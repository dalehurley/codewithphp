---
title: "30: Data Extraction and Analysis"
description: "Build intelligent ETL pipelines that extract structured data from unstructured sources, validate quality, transform formats, and generate comprehensive analytics with Claude."
series: "claude-php-developers"
chapter: 30
order: 30
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 11-15"
  - "Understanding of ETL processes"
  - "Experience with data formats (JSON, CSV, XML)"
  - "Knowledge of data validation and quality"
---

![30: Data Extraction and Analysis](/images/claude-php/chapter-30-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 30</span>
</div>

# Chapter 30: Data Extraction and Analysis

## Overview

Extracting meaningful insights from unstructured data is a common challenge. In this final chapter of Real-World Applications, you'll build intelligent ETL pipelines that extract structured data from documents, emails, PDFs, web pages, and other unstructured sources.

Claude excels at understanding context, recognizing patterns, and transforming messy real-world data into clean, structured formats. Your system will handle extraction, validation, transformation, quality assurance, and generate comprehensive analytics—all while maintaining data integrity and accuracy.

**What You'll Build**: A production-ready data extraction and analysis platform that processes multiple formats, validates quality, transforms data, generates insights, and produces detailed reports.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use and structured outputs)
- ✓ **ETL process knowledge** for data pipelines
- ✓ **Data format experience** (JSON, CSV, XML, etc.)
- ✓ **Quality assurance understanding** for validation

**Estimated Time**: 90-120 minutes

## Architecture Overview

```php
<?php
# filename: src/DataExtraction/ExtractionPipeline.php
declare(strict_types=1);

namespace App\DataExtraction;

use Anthropic\Anthropic;

class ExtractionPipeline
{
    public function __construct(
        private Anthropic $claude,
        private DocumentParser $parser,
        private DataValidator $validator,
        private DataTransformer $transformer,
        private QualityAnalyzer $qualityAnalyzer,
        private StorageManager $storage
    ) {}

    /**
     * Process document through complete pipeline
     */
    public function process(
        string $source,
        string $sourceType,
        array $schema,
        array $options = []
    ): ExtractionResult {
        // Step 1: Parse source document
        $parsed = $this->parser->parse($source, $sourceType);

        // Step 2: Extract structured data
        $extracted = $this->extractData($parsed, $schema);

        // Step 3: Validate data quality
        $validationResult = $this->validator->validate($extracted, $schema);

        // Step 4: Transform data (if needed)
        $transformed = $this->transformer->transform(
            $extracted,
            $options['output_format'] ?? 'json'
        );

        // Step 5: Analyze quality
        $qualityReport = $this->qualityAnalyzer->analyze($transformed, $validationResult);

        // Step 6: Store results
        if ($options['auto_store'] ?? false) {
            $this->storage->store($transformed, $qualityReport);
        }

        return new ExtractionResult(
            data: $transformed,
            validation: $validationResult,
            quality: $qualityReport,
            metadata: $this->buildMetadata($parsed, $extracted)
        );
    }

    /**
     * Extract structured data using Claude
     */
    private function extractData(ParsedDocument $document, array $schema): array
    {
        $prompt = $this->buildExtractionPrompt($document, $schema);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 8192,
            'temperature' => 0.2,
            'system' => $this->getExtractionSystemPrompt(),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $this->parseExtractedData($response->content[0]->text, $schema);
    }

    private function buildExtractionPrompt(ParsedDocument $document, array $schema): string
    {
        $schemaText = $this->formatSchema($schema);

        return <<<PROMPT
Extract structured data from this document according to the schema provided.

Document Type: {$document->type}
Content:
{$document->content}

Schema:
{$schemaText}

Instructions:
1. Extract all fields defined in the schema
2. Maintain data types (strings, numbers, dates, booleans, arrays)
3. Handle missing fields gracefully (use null)
4. Parse dates into ISO 8601 format
5. Clean and normalize data
6. For arrays, extract all matching items
7. Preserve relationships between fields

Return ONLY valid JSON matching the schema structure. No explanations.
PROMPT;
    }

    private function getExtractionSystemPrompt(): string
    {
        return <<<SYSTEM
You are a data extraction specialist with expertise in:
- Understanding document structure and context
- Extracting precise information from unstructured text
- Recognizing patterns and relationships
- Handling ambiguous or incomplete data
- Maintaining data integrity and accuracy

Guidelines:
1. Be precise - extract exact values, don't infer
2. Be consistent - use the same format throughout
3. Be thorough - don't skip fields
4. Be accurate - verify extracted data makes sense
5. Be explicit - use null for missing data, not empty strings

When extracting:
- Dates: Convert to ISO 8601 (YYYY-MM-DD or YYYY-MM-DDTHH:MM:SS)
- Numbers: Remove formatting (commas, currency symbols)
- Text: Trim whitespace, normalize quotes
- Arrays: Extract all matching items, maintain order
- Nested objects: Preserve structure

Return only valid JSON with no additional text.
SYSTEM;
    }

    private function formatSchema(array $schema): string
    {
        return json_encode($schema, JSON_PRETTY_PRINT);
    }

    private function parseExtractedData(string $jsonText, array $schema): array
    {
        // Extract JSON from response
        if (preg_match('/```json\s*(\{.*?\}|\[.*?\])\s*```/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/(\{.*\}|\[.*\])/s', $jsonText, $matches)) {
            $jsonText = $matches[0];
        }

        $data = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse extracted data: ' . json_last_error_msg());
        }

        return $data;
    }

    private function buildMetadata(ParsedDocument $document, array $extracted): array
    {
        return [
            'source_type' => $document->type,
            'extraction_timestamp' => date('c'),
            'fields_extracted' => count($extracted),
            'source_length' => strlen($document->content)
        ];
    }
}
```

## Document Parser

```php
<?php
# filename: src/DataExtraction/DocumentParser.php
declare(strict_types=1);

namespace App\DataExtraction;

class DocumentParser
{
    /**
     * Parse document based on type
     */
    public function parse(string $source, string $type): ParsedDocument
    {
        return match($type) {
            'text' => $this->parseText($source),
            'html' => $this->parseHTML($source),
            'pdf' => $this->parsePDF($source),
            'email' => $this->parseEmail($source),
            'csv' => $this->parseCSV($source),
            'xml' => $this->parseXML($source),
            'json' => $this->parseJSON($source),
            default => throw new \InvalidArgumentException("Unsupported type: {$type}")
        };
    }

    private function parseText(string $content): ParsedDocument
    {
        return new ParsedDocument(
            type: 'text',
            content: $content,
            metadata: [
                'length' => strlen($content),
                'lines' => substr_count($content, "\n") + 1
            ]
        );
    }

    private function parseHTML(string $html): ParsedDocument
    {
        // Remove scripts and styles
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $clean);

        // Extract text
        $text = strip_tags($clean);
        $text = html_entity_decode($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return new ParsedDocument(
            type: 'html',
            content: trim($text),
            metadata: [
                'original_length' => strlen($html),
                'cleaned_length' => strlen($text)
            ]
        );
    }

    private function parsePDF(string $filepath): ParsedDocument
    {
        // Use PDF parser library (e.g., smalot/pdfparser)
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filepath);
        $text = $pdf->getText();

        return new ParsedDocument(
            type: 'pdf',
            content: $text,
            metadata: [
                'pages' => count($pdf->getPages()),
                'title' => $pdf->getDetails()['Title'] ?? null
            ]
        );
    }

    private function parseEmail(string $rawEmail): ParsedDocument
    {
        // Parse email headers and body
        $lines = explode("\n", $rawEmail);
        $headers = [];
        $body = '';
        $inBody = false;

        foreach ($lines as $line) {
            if (!$inBody && trim($line) === '') {
                $inBody = true;
                continue;
            }

            if (!$inBody) {
                if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                    $headers[strtolower($matches[1])] = $matches[2];
                }
            } else {
                $body .= $line . "\n";
            }
        }

        return new ParsedDocument(
            type: 'email',
            content: trim($body),
            metadata: $headers
        );
    }

    private function parseCSV(string $filepath): ParsedDocument
    {
        $rows = [];
        if (($handle = fopen($filepath, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return new ParsedDocument(
            type: 'csv',
            content: json_encode($rows),
            metadata: [
                'rows' => count($rows),
                'columns' => !empty($rows) ? count($rows[0]) : 0
            ]
        );
    }

    private function parseXML(string $xml): ParsedDocument
    {
        $doc = simplexml_load_string($xml);
        $json = json_encode($doc);

        return new ParsedDocument(
            type: 'xml',
            content: $json,
            metadata: [
                'root' => $doc->getName()
            ]
        );
    }

    private function parseJSON(string $json): ParsedDocument
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        return new ParsedDocument(
            type: 'json',
            content: json_encode($data),
            metadata: [
                'keys' => array_keys($data)
            ]
        );
    }
}
```

## Invoice Data Extractor

```php
<?php
# filename: src/DataExtraction/Extractors/InvoiceExtractor.php
declare(strict_types=1);

namespace App\DataExtraction\Extractors;

use Anthropic\Anthropic;
use App\DataExtraction\ParsedDocument;

class InvoiceExtractor
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Extract invoice data
     */
    public function extract(ParsedDocument $document): array
    {
        $prompt = <<<PROMPT
Extract invoice information from this document.

Document:
{$document->content}

Extract and return JSON with this structure:
{
  "invoice_number": "INV-12345",
  "invoice_date": "2024-01-15",
  "due_date": "2024-02-15",
  "vendor": {
    "name": "Vendor Name",
    "address": "123 Main St",
    "city": "City",
    "state": "ST",
    "zip": "12345",
    "phone": "555-1234",
    "email": "vendor@example.com",
    "tax_id": "12-3456789"
  },
  "customer": {
    "name": "Customer Name",
    "address": "456 Oak Ave",
    "city": "City",
    "state": "ST",
    "zip": "67890",
    "phone": "555-5678",
    "email": "customer@example.com"
  },
  "line_items": [
    {
      "description": "Product or service",
      "quantity": 10,
      "unit_price": 99.99,
      "total": 999.90,
      "tax_rate": 0.08,
      "tax_amount": 79.99
    }
  ],
  "subtotal": 999.90,
  "tax": 79.99,
  "shipping": 15.00,
  "total": 1094.89,
  "currency": "USD",
  "payment_terms": "Net 30",
  "notes": "Any special notes or instructions"
}

Return ONLY valid JSON with all available fields. Use null for missing values.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.1,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        throw new \RuntimeException('Failed to extract invoice data');
    }

    /**
     * Validate extracted invoice
     */
    public function validate(array $invoice): array
    {
        $errors = [];

        // Required fields
        if (empty($invoice['invoice_number'])) {
            $errors[] = 'Missing invoice number';
        }

        if (empty($invoice['total'])) {
            $errors[] = 'Missing total amount';
        }

        // Validate totals
        if (isset($invoice['line_items'])) {
            $calculatedSubtotal = array_sum(array_column($invoice['line_items'], 'total'));
            $reportedSubtotal = $invoice['subtotal'] ?? 0;

            if (abs($calculatedSubtotal - $reportedSubtotal) > 0.01) {
                $errors[] = "Subtotal mismatch: calculated {$calculatedSubtotal} vs reported {$reportedSubtotal}";
            }
        }

        // Validate dates
        if (isset($invoice['invoice_date'], $invoice['due_date'])) {
            $invoiceDate = strtotime($invoice['invoice_date']);
            $dueDate = strtotime($invoice['due_date']);

            if ($dueDate < $invoiceDate) {
                $errors[] = 'Due date is before invoice date';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $this->checkWarnings($invoice)
        ];
    }

    private function checkWarnings(array $invoice): array
    {
        $warnings = [];

        // Check for unusually high amounts
        if (isset($invoice['total']) && $invoice['total'] > 100000) {
            $warnings[] = 'Unusually high invoice total';
        }

        // Check for missing optional but common fields
        if (empty($invoice['vendor']['tax_id'])) {
            $warnings[] = 'Missing vendor tax ID';
        }

        return $warnings;
    }
}
```

## Resume/CV Data Extractor

```php
<?php
# filename: src/DataExtraction/Extractors/ResumeExtractor.php
declare(strict_types=1);

namespace App\DataExtraction\Extractors;

use Anthropic\Anthropic;
use App\DataExtraction\ParsedDocument;

class ResumeExtractor
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Extract resume/CV data
     */
    public function extract(ParsedDocument $document): array
    {
        $prompt = <<<PROMPT
Extract structured information from this resume/CV.

Resume:
{$document->content}

Extract and return JSON:
{
  "personal_info": {
    "name": "Full Name",
    "email": "email@example.com",
    "phone": "555-1234",
    "location": "City, State",
    "linkedin": "linkedin.com/in/username",
    "website": "example.com",
    "github": "github.com/username"
  },
  "summary": "Professional summary or objective",
  "experience": [
    {
      "title": "Job Title",
      "company": "Company Name",
      "location": "City, State",
      "start_date": "2020-01",
      "end_date": "2023-12",
      "current": false,
      "description": "Job description and responsibilities",
      "achievements": [
        "Key achievement 1",
        "Key achievement 2"
      ]
    }
  ],
  "education": [
    {
      "degree": "Bachelor of Science",
      "field": "Computer Science",
      "institution": "University Name",
      "location": "City, State",
      "graduation_date": "2020-05",
      "gpa": "3.8",
      "honors": "Cum Laude"
    }
  ],
  "skills": {
    "technical": ["PHP", "JavaScript", "Python"],
    "languages": ["English (Native)", "Spanish (Fluent)"],
    "soft_skills": ["Leadership", "Communication"]
  },
  "certifications": [
    {
      "name": "Certification Name",
      "issuer": "Issuing Organization",
      "date": "2023-06",
      "credential_id": "ABC123"
    }
  ],
  "projects": [
    {
      "name": "Project Name",
      "description": "Project description",
      "technologies": ["PHP", "Laravel"],
      "url": "github.com/user/project"
    }
  ],
  "awards": [
    {
      "title": "Award Name",
      "issuer": "Organization",
      "date": "2022-11"
    }
  ]
}

Return ONLY valid JSON with all available information.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 6144,
            'temperature' => 0.1,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        throw new \RuntimeException('Failed to extract resume data');
    }

    /**
     * Analyze resume quality and completeness
     */
    public function analyzeQuality(array $resume): array
    {
        $prompt = <<<PROMPT
Analyze the quality and completeness of this resume data.

Resume Data:
{$this->formatResumeForAnalysis($resume)}

Provide analysis:
{
  "completeness_score": 0.0 to 1.0,
  "quality_score": 0.0 to 1.0,
  "strengths": ["strength 1", "strength 2"],
  "weaknesses": ["weakness 1", "weakness 2"],
  "missing_sections": ["section 1", "section 2"],
  "recommendations": ["recommendation 1", "recommendation 2"],
  "experience_years": 5.5,
  "career_level": "junior|mid|senior|lead|executive",
  "top_skills": ["skill1", "skill2", "skill3"]
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.3,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    private function formatResumeForAnalysis(array $resume): string
    {
        return json_encode($resume, JSON_PRETTY_PRINT);
    }
}
```

## Data Validator

```php
<?php
# filename: src/DataExtraction/DataValidator.php
declare(strict_types=1);

namespace App\DataExtraction;

class DataValidator
{
    /**
     * Validate extracted data against schema
     */
    public function validate(array $data, array $schema): ValidationResult
    {
        $errors = [];
        $warnings = [];

        $this->validateRecursive($data, $schema, '', $errors, $warnings);

        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors,
            warnings: $warnings,
            fieldCount: $this->countFields($data),
            completeness: $this->calculateCompleteness($data, $schema)
        );
    }

    private function validateRecursive(
        mixed $data,
        mixed $schema,
        string $path,
        array &$errors,
        array &$warnings
    ): void {
        if (!is_array($schema)) {
            return;
        }

        // Check required fields
        if (isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $field) {
                if (!isset($data[$field]) || $data[$field] === null) {
                    $errors[] = "{$path}.{$field} is required but missing";
                }
            }
        }

        // Validate each field
        if (isset($schema['properties']) && is_array($data)) {
            foreach ($schema['properties'] as $field => $fieldSchema) {
                $fieldPath = $path ? "{$path}.{$field}" : $field;

                if (!isset($data[$field])) {
                    continue;
                }

                $value = $data[$field];

                // Type validation
                if (isset($fieldSchema['type'])) {
                    $valid = $this->validateType($value, $fieldSchema['type']);
                    if (!$valid) {
                        $errors[] = "{$fieldPath} has invalid type. Expected {$fieldSchema['type']}";
                    }
                }

                // Format validation
                if (isset($fieldSchema['format'])) {
                    $valid = $this->validateFormat($value, $fieldSchema['format']);
                    if (!$valid) {
                        $warnings[] = "{$fieldPath} doesn't match expected format {$fieldSchema['format']}";
                    }
                }

                // Range validation
                if (isset($fieldSchema['minimum']) && $value < $fieldSchema['minimum']) {
                    $errors[] = "{$fieldPath} is below minimum value {$fieldSchema['minimum']}";
                }
                if (isset($fieldSchema['maximum']) && $value > $fieldSchema['maximum']) {
                    $errors[] = "{$fieldPath} exceeds maximum value {$fieldSchema['maximum']}";
                }

                // Pattern validation
                if (isset($fieldSchema['pattern']) && is_string($value)) {
                    if (!preg_match($fieldSchema['pattern'], $value)) {
                        $errors[] = "{$fieldPath} doesn't match required pattern";
                    }
                }

                // Nested validation
                if (isset($fieldSchema['properties'])) {
                    $this->validateRecursive($value, $fieldSchema, $fieldPath, $errors, $warnings);
                }

                // Array validation
                if (isset($fieldSchema['items']) && is_array($value)) {
                    foreach ($value as $index => $item) {
                        $this->validateRecursive($item, $fieldSchema['items'], "{$fieldPath}[{$index}]", $errors, $warnings);
                    }
                }
            }
        }
    }

    private function validateType(mixed $value, string $type): bool
    {
        return match($type) {
            'string' => is_string($value),
            'number', 'integer' => is_numeric($value),
            'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_array($value) && !array_is_list($value),
            'null' => $value === null,
            default => true
        };
    }

    private function validateFormat(mixed $value, string $format): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return match($format) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'date' => (bool)strtotime($value),
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'phone' => preg_match('/^\+?[\d\s\-\(\)]+$/', $value),
            'zip' => preg_match('/^\d{5}(-\d{4})?$/', $value),
            default => true
        };
    }

    private function countFields(array $data): int
    {
        $count = 0;
        foreach ($data as $value) {
            if (is_array($value)) {
                $count += $this->countFields($value);
            } else {
                $count++;
            }
        }
        return $count;
    }

    private function calculateCompleteness(array $data, array $schema): float
    {
        if (!isset($schema['properties'])) {
            return 1.0;
        }

        $total = count($schema['properties']);
        $filled = 0;

        foreach ($schema['properties'] as $field => $fieldSchema) {
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                $filled++;
            }
        }

        return $total > 0 ? $filled / $total : 1.0;
    }
}
```

## Quality Analyzer

```php
<?php
# filename: src/DataExtraction/QualityAnalyzer.php
declare(strict_types=1);

namespace App\DataExtraction;

use Anthropic\Anthropic;

class QualityAnalyzer
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Analyze data quality
     */
    public function analyze(array $data, ValidationResult $validation): QualityReport
    {
        $metrics = [
            'accuracy' => $this->assessAccuracy($data, $validation),
            'completeness' => $validation->completeness,
            'consistency' => $this->assessConsistency($data),
            'validity' => $validation->isValid ? 1.0 : 0.0
        ];

        $overallScore = array_sum($metrics) / count($metrics);

        // AI-powered anomaly detection
        $anomalies = $this->detectAnomalies($data);

        return new QualityReport(
            overallScore: $overallScore,
            metrics: $metrics,
            anomalies: $anomalies,
            recommendations: $this->generateRecommendations($metrics, $anomalies, $validation)
        );
    }

    private function assessAccuracy(array $data, ValidationResult $validation): float
    {
        $totalChecks = $validation->fieldCount;
        $failedChecks = count($validation->errors) + count($validation->warnings) * 0.5;

        if ($totalChecks === 0) {
            return 1.0;
        }

        return max(0, 1 - ($failedChecks / $totalChecks));
    }

    private function assessConsistency(array $data): float
    {
        // Check for consistency in formatting, types, etc.
        $score = 1.0;

        // Check date formats
        $dates = $this->extractDates($data);
        if (count($dates) > 1 && !$this->haveSameFormat($dates)) {
            $score -= 0.1;
        }

        // Check number formats
        $numbers = $this->extractNumbers($data);
        if (count($numbers) > 1 && !$this->haveSamePrecision($numbers)) {
            $score -= 0.1;
        }

        return max(0, $score);
    }

    private function detectAnomalies(array $data): array
    {
        $prompt = <<<PROMPT
Analyze this data for anomalies, inconsistencies, or suspicious values.

Data:
{$this->formatDataForAnalysis($data)}

Look for:
1. Unusual or unrealistic values
2. Inconsistent formats
3. Missing patterns
4. Duplicate entries
5. Data that doesn't make logical sense
6. Outliers

Return JSON array of anomalies:
[
  {
    "field": "field.path",
    "type": "outlier|inconsistent|suspicious|duplicate",
    "severity": "low|medium|high",
    "description": "What's wrong",
    "suggestion": "How to fix"
  }
]

Return ONLY valid JSON array.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\[.*\]/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    private function generateRecommendations(
        array $metrics,
        array $anomalies,
        ValidationResult $validation
    ): array {
        $recommendations = [];

        if ($metrics['completeness'] < 0.8) {
            $recommendations[] = 'Improve data completeness - many fields are missing';
        }

        if ($metrics['accuracy'] < 0.9) {
            $recommendations[] = 'Review data accuracy - validation errors detected';
        }

        if ($metrics['consistency'] < 0.9) {
            $recommendations[] = 'Standardize data formats for consistency';
        }

        if (count($anomalies) > 0) {
            $recommendations[] = 'Review and resolve detected anomalies';
        }

        return $recommendations;
    }

    private function extractDates(array $data, array &$dates = []): array
    {
        foreach ($data as $value) {
            if (is_string($value) && strtotime($value) !== false) {
                $dates[] = $value;
            } elseif (is_array($value)) {
                $this->extractDates($value, $dates);
            }
        }
        return $dates;
    }

    private function extractNumbers(array $data, array &$numbers = []): array
    {
        foreach ($data as $value) {
            if (is_numeric($value)) {
                $numbers[] = $value;
            } elseif (is_array($value)) {
                $this->extractNumbers($value, $numbers);
            }
        }
        return $numbers;
    }

    private function haveSameFormat(array $dates): bool
    {
        if (empty($dates)) {
            return true;
        }

        $formats = array_map(fn($d) => $this->detectDateFormat($d), $dates);
        return count(array_unique($formats)) === 1;
    }

    private function detectDateFormat(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return 'Y-m-d';
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) return 'm/d/Y';
        return 'unknown';
    }

    private function haveSamePrecision(array $numbers): bool
    {
        $decimals = array_map(function($n) {
            $parts = explode('.', (string)$n);
            return isset($parts[1]) ? strlen($parts[1]) : 0;
        }, $numbers);

        return count(array_unique($decimals)) === 1;
    }

    private function formatDataForAnalysis(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
```

## Analytics Generator

```php
<?php
# filename: src/DataExtraction/AnalyticsGenerator.php
declare(strict_types=1);

namespace App\DataExtraction;

use Anthropic\Anthropic;

class AnalyticsGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate comprehensive analytics from extracted data
     */
    public function generate(array $data, string $dataType): array
    {
        $prompt = <<<PROMPT
Generate comprehensive analytics and insights from this data.

Data Type: {$dataType}

Data:
{$this->formatData($data)}

Provide analytics including:
1. Summary statistics
2. Key patterns and trends
3. Interesting insights
4. Anomalies or outliers
5. Correlations (if applicable)
6. Recommendations based on the data

Return JSON:
{
  "summary": {
    "total_records": 0,
    "key_metrics": {},
    "time_period": "if applicable"
  },
  "insights": [
    {
      "title": "Insight title",
      "description": "Detailed description",
      "significance": "high|medium|low",
      "data_points": []
    }
  ],
  "trends": [
    {
      "name": "Trend name",
      "direction": "increasing|decreasing|stable",
      "rate": "percentage if applicable",
      "description": "Explanation"
    }
  ],
  "recommendations": [
    {
      "action": "What to do",
      "rationale": "Why",
      "priority": "high|medium|low"
    }
  ],
  "charts": [
    {
      "type": "bar|line|pie",
      "title": "Chart title",
      "data": {}
    }
  ]
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 6144,
            'temperature' => 0.4,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    private function formatData(array $data): string
    {
        // Limit data size for prompt
        $formatted = json_encode($data, JSON_PRETTY_PRINT);

        if (strlen($formatted) > 10000) {
            // Sample the data if too large
            $sample = array_slice($data, 0, 50);
            $formatted = json_encode($sample, JSON_PRETTY_PRINT);
            $formatted .= "\n... (showing first 50 items of " . count($data) . " total)";
        }

        return $formatted;
    }
}
```

## Complete CLI Tool

```php
<?php
# filename: bin/extract-data.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\DataExtraction\ExtractionPipeline;
use App\DataExtraction\DocumentParser;
use App\DataExtraction\DataValidator;
use App\DataExtraction\DataTransformer;
use App\DataExtraction\QualityAnalyzer;
use App\DataExtraction\StorageManager;
use App\DataExtraction\AnalyticsGenerator;

// Parse arguments
$options = getopt('', [
    'source:',
    'type:',
    'schema:',
    'output:',
    'format:',
    'analytics',
    'help'
]);

if (isset($options['help']) || !isset($options['source'])) {
    echo <<<HELP
Data Extraction CLI

Usage:
  php bin/extract-data.php --source=file.pdf --type=pdf --schema=invoice.json [options]

Options:
  --source      Source file or content (required)
  --type        Source type: text|html|pdf|email|csv|xml|json (required)
  --schema      Path to JSON schema file (required)
  --output      Output file path (default: stdout)
  --format      Output format: json|csv|xml (default: json)
  --analytics   Generate analytics report
  --help        Show this help

Examples:
  php bin/extract-data.php --source=invoice.pdf --type=pdf --schema=schemas/invoice.json
  php bin/extract-data.php --source=data.html --type=html --schema=schemas/product.json --analytics

HELP;
    exit(0);
}

// Validate required options
if (!isset($options['type']) || !isset($options['schema'])) {
    echo "Error: --type and --schema are required\n";
    exit(1);
}

// Initialize
$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$parser = new DocumentParser();
$validator = new DataValidator();
$transformer = new DataTransformer();
$qualityAnalyzer = new QualityAnalyzer($claude);
$storage = new StorageManager();

$pipeline = new ExtractionPipeline(
    claude: $claude,
    parser: $parser,
    validator: $validator,
    transformer: $transformer,
    qualityAnalyzer: $qualityAnalyzer,
    storage: $storage
);

// Load schema
$schema = json_decode(file_get_contents($options['schema']), true);

echo "🔍 Extracting data from {$options['source']}...\n";

try {
    // Process
    $result = $pipeline->process(
        source: $options['source'],
        sourceType: $options['type'],
        schema: $schema,
        options: [
            'output_format' => $options['format'] ?? 'json',
            'auto_store' => false
        ]
    );

    echo "✅ Extraction complete!\n\n";

    // Output results
    $output = [
        'success' => true,
        'data' => $result->data,
        'validation' => [
            'valid' => $result->validation->isValid,
            'errors' => $result->validation->errors,
            'warnings' => $result->validation->warnings,
            'completeness' => $result->validation->completeness
        ],
        'quality' => [
            'overall_score' => $result->quality->overallScore,
            'metrics' => $result->quality->metrics,
            'anomalies' => $result->quality->anomalies,
            'recommendations' => $result->quality->recommendations
        ],
        'metadata' => $result->metadata
    ];

    // Generate analytics if requested
    if (isset($options['analytics'])) {
        echo "📊 Generating analytics...\n";
        $analyticsGen = new AnalyticsGenerator($claude);
        $output['analytics'] = $analyticsGen->generate($result->data, $options['type']);
    }

    // Output to file or stdout
    $json = json_encode($output, JSON_PRETTY_PRINT);

    if (isset($options['output'])) {
        file_put_contents($options['output'], $json);
        echo "💾 Results saved to {$options['output']}\n";
    } else {
        echo $json . "\n";
    }

    // Summary
    echo "\n📈 Summary:\n";
    echo "  Fields extracted: {$result->validation->fieldCount}\n";
    echo "  Validation: " . ($result->validation->isValid ? '✓ Passed' : '✗ Failed') . "\n";
    echo "  Quality score: " . number_format($result->quality->overallScore * 100, 1) . "%\n";
    echo "  Completeness: " . number_format($result->validation->completeness * 100, 1) . "%\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

## Data Structures

```php
<?php
# filename: src/DataExtraction/DataStructures.php
declare(strict_types=1);

namespace App\DataExtraction;

readonly class ParsedDocument
{
    public function __construct(
        public string $type,
        public string $content,
        public array $metadata = []
    ) {}
}

readonly class ExtractionResult
{
    public function __construct(
        public array $data,
        public ValidationResult $validation,
        public QualityReport $quality,
        public array $metadata
    ) {}
}

readonly class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public array $errors,
        public array $warnings,
        public int $fieldCount,
        public float $completeness
    ) {}
}

readonly class QualityReport
{
    public function __construct(
        public float $overallScore,
        public array $metrics,
        public array $anomalies,
        public array $recommendations
    ) {}
}
```

## Key Takeaways

- ✓ Claude excels at extracting structured data from unstructured sources
- ✓ Multi-stage pipelines (parse → extract → validate → transform) ensure quality
- ✓ Schema-driven extraction provides consistency and validation
- ✓ AI-powered quality analysis detects anomalies humans might miss
- ✓ Context awareness helps handle ambiguous or incomplete data
- ✓ Validation against schemas ensures data integrity
- ✓ Analytics generation transforms raw data into actionable insights
- ✓ Multiple format support (PDF, HTML, email, etc.) enables flexibility
- ✓ Quality scoring helps identify data that needs review
- ✓ Automated pipelines scale to handle large volumes efficiently

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="30"
  label="You've built an intelligent data extraction and analysis system!"
/>

---

You've completed the Real-World Applications section! These chapters demonstrated how to build production-ready systems that solve real business problems. Continue to the next section to explore advanced topics and optimization techniques.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 30 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-30)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-30
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php bin/extract-data.php --source=sample.pdf --type=pdf --schema=schemas/invoice.json
```
