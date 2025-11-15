---
title: "14: Document Processing and PDF Analysis"
description: "Process complex documents with Claude in PHP. Extract structured data from PDFs, analyze contracts, process invoices, perform document intelligence, and build automated document workflows."
series: "claude-php-developers"
chapter: 14
order: 14
difficulty: "Expert"
prerequisites:
  - "Understanding of PDF structure"
  - "Completed Chapter 13 (Vision)"
  - "Experience with document processing"
---

![14: Document Processing and PDF Analysis](/images/claude-php/chapter-14-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 14</span>
</div>

# Chapter 14: Document Processing and PDF Analysis

## Overview

Claude excels at understanding and extracting information from complex documents—contracts, invoices, resumes, reports, legal documents, and more. Combined with PHP's document processing capabilities, you can build powerful automation systems that understand document structure, extract key data, and generate insights.

In this chapter, you'll learn to process PDFs, extract structured data, analyze contracts, automate invoice processing, and build intelligent document workflows that save hours of manual work.

**What You'll Build**: An automated document processing system that handles invoices, contracts, and resumes with intelligent extraction and validation.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 13** (Vision capabilities)
- ✓ **PDF processing knowledge** (basic understanding)
- ✓ **Document structure understanding** (headers, tables, sections)
- ✓ **Composer installed** for PDF libraries

**Estimated Time**: 60-75 minutes

## Required Libraries

Install necessary PHP libraries for PDF processing:

```bash
composer require smalot/pdfparser
composer require setasign/fpdf
composer require tecnickcom/tcpdf
```

## Step 1: PDF to Image Conversion

Claude works best with PDFs converted to images:

```php
<?php
# filename: src/Document/PDFProcessor.php
declare(strict_types=1);

namespace App\Document;

use Smalot\PdfParser\Parser as PdfParser;

class PDFProcessor
{
    /**
     * Convert PDF to images using Imagick
     */
    public static function convertToImages(string $pdfPath, string $outputDir = null): array
    {
        if (!extension_loaded('imagick')) {
            throw new \RuntimeException('Imagick extension required for PDF conversion');
        }

        $outputDir = $outputDir ?? sys_get_temp_dir();
        $baseFilename = pathinfo($pdfPath, PATHINFO_FILENAME);

        $imagick = new \Imagick();
        $imagick->setResolution(150, 150); // DPI for quality
        $imagick->readImage($pdfPath);

        $imagick->setImageFormat('png');
        $imagick->setImageCompressionQuality(90);

        $images = [];
        $pageCount = $imagick->getNumberImages();

        foreach ($imagick as $pageIndex => $page) {
            $pageNumber = $pageIndex + 1;
            $imagePath = "{$outputDir}/{$baseFilename}_page_{$pageNumber}.png";

            $page->setImageFormat('png');
            $page->writeImage($imagePath);

            $images[] = [
                'page' => $pageNumber,
                'path' => $imagePath,
                'size' => filesize($imagePath)
            ];
        }

        $imagick->clear();
        $imagick->destroy();

        return $images;
    }

    /**
     * Extract text from PDF (fallback method)
     */
    public static function extractText(string $pdfPath): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($pdfPath);

        $pages = [];
        foreach ($pdf->getPages() as $pageNumber => $page) {
            $pages[$pageNumber + 1] = $page->getText();
        }

        return [
            'total_pages' => count($pages),
            'pages' => $pages,
            'full_text' => implode("\n\n", $pages),
            'metadata' => $pdf->getDetails()
        ];
    }

    /**
     * Get PDF metadata
     */
    public static function getMetadata(string $pdfPath): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($pdfPath);
        $details = $pdf->getDetails();

        return [
            'title' => $details['Title'] ?? null,
            'author' => $details['Author'] ?? null,
            'subject' => $details['Subject'] ?? null,
            'keywords' => $details['Keywords'] ?? null,
            'creator' => $details['Creator'] ?? null,
            'producer' => $details['Producer'] ?? null,
            'creation_date' => $details['CreationDate'] ?? null,
            'modification_date' => $details['ModDate'] ?? null,
            'page_count' => count($pdf->getPages())
        ];
    }
}
```

## Step 2: Invoice Processing

Extract structured data from invoices:

```php
<?php
# filename: src/Document/InvoiceProcessor.php
declare(strict_types=1);

namespace App\Document;

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

class InvoiceProcessor
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function processInvoice(string $pdfPath): array
    {
        // Convert PDF to images
        $images = PDFProcessor::convertToImages($pdfPath);

        // Process each page
        $allData = [];
        foreach ($images as $imageInfo) {
            $pageData = $this->processInvoicePage($imageInfo['path']);
            $allData[] = $pageData;
        }

        // Combine multi-page data
        return $this->combineInvoiceData($allData);
    }

    private function processInvoicePage(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Extract all information from this invoice and return as JSON:

{
  "invoice_number": "string",
  "invoice_date": "YYYY-MM-DD",
  "due_date": "YYYY-MM-DD",
  "vendor": {
    "name": "string",
    "address": "string",
    "tax_id": "string",
    "contact": "string"
  },
  "customer": {
    "name": "string",
    "address": "string",
    "tax_id": "string"
  },
  "line_items": [
    {
      "description": "string",
      "quantity": number,
      "unit_price": number,
      "amount": number,
      "tax_rate": number
    }
  ],
  "subtotal": number,
  "tax": number,
  "shipping": number,
  "total": number,
  "currency": "string",
  "payment_terms": "string",
  "notes": "string"
}

Extract all visible data. Use null for missing fields. Ensure numbers are numeric, not strings.
Return only valid JSON, no explanation.
PROMPT
                        ]
                    ]
                ]
            ]
        ]);

        $jsonText = $response->content[0]->text;

        // Extract JSON from response
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        }

        $data = json_decode($jsonText, true);

        if (!$data) {
            throw new \RuntimeException('Failed to parse invoice JSON');
        }

        return $data;
    }

    private function combineInvoiceData(array $pageData): array
    {
        // If single page, return as-is
        if (count($pageData) === 1) {
            return $pageData[0];
        }

        // Multi-page: merge line items
        $combined = $pageData[0];
        for ($i = 1; $i < count($pageData); $i++) {
            if (isset($pageData[$i]['line_items'])) {
                $combined['line_items'] = array_merge(
                    $combined['line_items'] ?? [],
                    $pageData[$i]['line_items']
                );
            }
        }

        return $combined;
    }

    public function validateInvoice(array $invoiceData): array
    {
        $errors = [];

        // Required field validation
        $required = ['invoice_number', 'invoice_date', 'vendor', 'total'];
        foreach ($required as $field) {
            if (empty($invoiceData[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Numeric validation
        if (isset($invoiceData['total']) && !is_numeric($invoiceData['total'])) {
            $errors[] = "Total must be numeric";
        }

        // Date validation
        if (isset($invoiceData['invoice_date'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $invoiceData['invoice_date']);
            if (!$date) {
                $errors[] = "Invalid invoice date format";
            }
        }

        // Line items total check
        if (isset($invoiceData['line_items']) && isset($invoiceData['subtotal'])) {
            $calculatedSubtotal = array_sum(array_column($invoiceData['line_items'], 'amount'));
            $difference = abs($calculatedSubtotal - $invoiceData['subtotal']);

            if ($difference > 0.01) { // Allow 1 cent rounding difference
                $errors[] = "Line items total ({$calculatedSubtotal}) doesn't match subtotal ({$invoiceData['subtotal']})";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'invoice_number' => $invoiceData['invoice_number'] ?? 'UNKNOWN'
        ];
    }
}
```

## Step 3: Contract Analysis

Analyze legal contracts and extract key terms:

```php
<?php
# filename: src/Document/ContractAnalyzer.php
declare(strict_types=1);

namespace App\Document;

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

class ContractAnalyzer
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function analyzeContract(string $pdfPath): array
    {
        // Convert PDF pages to images
        $images = PDFProcessor::convertToImages($pdfPath);

        // First pass: Get overview
        $overview = $this->getContractOverview($images[0]['path']);

        // Second pass: Extract specific clauses
        $clauses = $this->extractClauses($images);

        // Third pass: Risk assessment
        $risks = $this->assessRisks($images);

        return [
            'overview' => $overview,
            'clauses' => $clauses,
            'risk_assessment' => $risks,
            'page_count' => count($images),
            'processed_at' => date('Y-m-d H:i:s')
        ];
    }

    private function getContractOverview(string $firstPageImage): array
    {
        $imageContent = ImageHelper::prepareImage($firstPageImage);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Analyze this contract (first page) and provide:

{
  "contract_type": "string (e.g., Employment, NDA, Service Agreement)",
  "parties": [
    {"name": "string", "role": "string"}
  ],
  "effective_date": "YYYY-MM-DD or null",
  "expiration_date": "YYYY-MM-DD or null",
  "governing_law": "string (jurisdiction)",
  "contract_value": "string (if mentioned)",
  "key_obligations": ["array of main obligations"]
}

Return only JSON.
PROMPT
                        ]
                    ]
                ]
            ]
        ]);

        return json_decode($this->extractJSON($response->content[0]->text), true) ?? [];
    }

    private function extractClauses(array $images): array
    {
        // Prepare all pages
        $content = [];
        foreach ($images as $imageInfo) {
            $content[] = ImageHelper::prepareImage($imageInfo['path']);
        }

        $content[] = [
            'type' => 'text',
            'text' => <<<PROMPT
Extract these key clauses from the contract:

{
  "termination_clause": {
    "notice_period": "string",
    "conditions": ["array"],
    "penalties": "string"
  },
  "payment_terms": {
    "amount": "string",
    "frequency": "string",
    "payment_method": "string",
    "late_fees": "string"
  },
  "confidentiality": {
    "scope": "string",
    "duration": "string",
    "exceptions": ["array"]
  },
  "liability": {
    "limitations": "string",
    "indemnification": "string",
    "insurance_required": "boolean"
  },
  "intellectual_property": {
    "ownership": "string",
    "license_grants": ["array"]
  },
  "dispute_resolution": {
    "method": "string (litigation/arbitration/mediation)",
    "venue": "string"
  }
}

Return only JSON. Use null for clauses not found.
PROMPT
        ];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

        return json_decode($this->extractJSON($response->content[0]->text), true) ?? [];
    }

    private function assessRisks(array $images): array
    {
        $content = [];
        foreach ($images as $imageInfo) {
            $content[] = ImageHelper::prepareImage($imageInfo['path']);
        }

        $content[] = [
            'type' => 'text',
            'text' => <<<PROMPT
Assess the risks in this contract and provide:

{
  "risk_level": "low/medium/high",
  "red_flags": [
    {
      "category": "string",
      "issue": "string",
      "severity": "low/medium/high",
      "recommendation": "string"
    }
  ],
  "missing_clauses": ["array of important missing protections"],
  "unfavorable_terms": ["array of potentially unfavorable terms"],
  "recommendations": ["array of suggested changes"],
  "overall_assessment": "string (2-3 sentences)"
}

Return only JSON.
PROMPT
        ];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

        return json_decode($this->extractJSON($response->content[0]->text), true) ?? [];
    }

    private function extractJSON(string $text): string
    {
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\{.*?\})/s', $text, $matches)) {
            return $matches[1];
        }
        return $text;
    }

    public function compareContracts(string $pdfPath1, string $pdfPath2): string
    {
        $images1 = PDFProcessor::convertToImages($pdfPath1);
        $images2 = PDFProcessor::convertToImages($pdfPath2);

        $content = [
            ['type' => 'text', 'text' => "Contract 1:\n"],
            ImageHelper::prepareImage($images1[0]['path']),
            ['type' => 'text', 'text' => "\nContract 2:\n"],
            ImageHelper::prepareImage($images2[0]['path']),
            [
                'type' => 'text',
                'text' => 'Compare these contracts. Identify key differences in terms, obligations, and risk. Which is more favorable and why?'
            ]
        ];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

        return $response->content[0]->text;
    }
}
```

## Step 4: Resume/CV Processing

Extract structured data from resumes:

```php
<?php
# filename: src/Document/ResumeProcessor.php
declare(strict_types=1);

namespace App\Document;

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

class ResumeProcessor
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function processResume(string $pdfPath): array
    {
        $images = PDFProcessor::convertToImages($pdfPath);

        // Process all pages together for context
        $content = [];
        foreach ($images as $imageInfo) {
            $content[] = ImageHelper::prepareImage($imageInfo['path']);
        }

        $content[] = [
            'type' => 'text',
            'text' => <<<PROMPT
Extract comprehensive information from this resume/CV:

{
  "personal_info": {
    "name": "string",
    "email": "string",
    "phone": "string",
    "location": "string",
    "linkedin": "string",
    "website": "string"
  },
  "summary": "string (professional summary/objective)",
  "work_experience": [
    {
      "company": "string",
      "title": "string",
      "start_date": "YYYY-MM or string",
      "end_date": "YYYY-MM or 'Present'",
      "duration": "string",
      "responsibilities": ["array of key responsibilities"],
      "achievements": ["array of quantifiable achievements"]
    }
  ],
  "education": [
    {
      "institution": "string",
      "degree": "string",
      "field": "string",
      "graduation_date": "string",
      "gpa": "string or null",
      "honors": "string or null"
    }
  ],
  "skills": {
    "technical": ["array"],
    "languages": ["array"],
    "soft_skills": ["array"]
  },
  "certifications": [
    {
      "name": "string",
      "issuer": "string",
      "date": "string"
    }
  ],
  "projects": [
    {
      "name": "string",
      "description": "string",
      "technologies": ["array"],
      "url": "string or null"
    }
  ],
  "years_of_experience": number,
  "seniority_level": "entry/mid/senior/lead/executive"
}

Return only valid JSON.
PROMPT
        ];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

        $jsonText = $this->extractJSON($response->content[0]->text);
        return json_decode($jsonText, true) ?? [];
    }

    public function matchJobDescription(array $resumeData, string $jobDescription): array
    {
        $resumeJson = json_encode($resumeData, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => <<<PROMPT
Resume Data:
{$resumeJson}

Job Description:
{$jobDescription}

Analyze the match between this resume and job description:

{
  "match_score": number (0-100),
  "matching_skills": ["array of skills that match"],
  "missing_skills": ["array of required skills not in resume"],
  "relevant_experience": ["array of relevant work experiences"],
  "strengths": ["why this candidate is a good fit"],
  "gaps": ["potential concerns or missing qualifications"],
  "recommendation": "strong_match/good_match/partial_match/poor_match",
  "summary": "string (2-3 sentence assessment)"
}

Return only JSON.
PROMPT
                ]
            ]
        ]);

        return json_decode($this->extractJSON($response->content[0]->text), true) ?? [];
    }

    public function generateInterviewQuestions(array $resumeData): array
    {
        $resumeJson = json_encode($resumeData, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => <<<PROMPT
Based on this resume, generate targeted interview questions:

{$resumeJson}

Provide:
{
  "technical_questions": ["5-7 technical questions based on their skills"],
  "experience_questions": ["5-7 behavioral questions about their work history"],
  "project_deep_dives": ["3-5 questions about specific projects"],
  "cultural_fit": ["3-5 questions to assess team fit"],
  "areas_to_probe": ["topics that need clarification"]
}

Return only JSON.
PROMPT
                ]
            ]
        ]);

        return json_decode($this->extractJSON($response->content[0]->text), true) ?? [];
    }

    private function extractJSON(string $text): string
    {
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return $matches[1];
        }
        if (preg_match('/(\{.*?\})/s', $text, $matches)) {
            return $matches[1];
        }
        return $text;
    }
}
```

## Step 5: Complete Document Processing Pipeline

```php
<?php
# filename: examples/01-document-pipeline.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Document\InvoiceProcessor;
use App\Document\ContractAnalyzer;
use App\Document\ResumeProcessor;
use App\Document\PDFProcessor;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class DocumentPipeline
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function processDocument(string $pdfPath): array
    {
        // Detect document type
        $documentType = $this->detectDocumentType($pdfPath);

        echo "Detected document type: {$documentType}\n\n";

        // Process based on type
        return match($documentType) {
            'invoice' => $this->processAsInvoice($pdfPath),
            'contract' => $this->processAsContract($pdfPath),
            'resume' => $this->processAsResume($pdfPath),
            default => $this->processAsGeneric($pdfPath)
        };
    }

    private function detectDocumentType(string $pdfPath): string
    {
        $images = PDFProcessor::convertToImages($pdfPath);
        $firstPage = $images[0]['path'];

        $imageContent = \App\Vision\ImageHelper::prepareImage($firstPage);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 200,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => 'What type of document is this? Reply with one word: invoice, contract, resume, report, letter, or other.'
                        ]
                    ]
                ]
            ]
        ]);

        return strtolower(trim($response->content[0]->text));
    }

    private function processAsInvoice(string $pdfPath): array
    {
        $processor = new InvoiceProcessor($this->client);
        $data = $processor->processInvoice($pdfPath);
        $validation = $processor->validateInvoice($data);

        return [
            'type' => 'invoice',
            'data' => $data,
            'validation' => $validation
        ];
    }

    private function processAsContract(string $pdfPath): array
    {
        $analyzer = new ContractAnalyzer($this->client);
        $analysis = $analyzer->analyzeContract($pdfPath);

        return [
            'type' => 'contract',
            'analysis' => $analysis
        ];
    }

    private function processAsResume(string $pdfPath): array
    {
        $processor = new ResumeProcessor($this->client);
        $data = $processor->processResume($pdfPath);

        return [
            'type' => 'resume',
            'data' => $data
        ];
    }

    private function processAsGeneric(string $pdfPath): array
    {
        $textData = PDFProcessor::extractText($pdfPath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Summarize this document and extract key information:\n\n" . $textData['full_text']
                ]
            ]
        ]);

        return [
            'type' => 'generic',
            'summary' => $response->content[0]->text,
            'metadata' => PDFProcessor::getMetadata($pdfPath)
        ];
    }
}

// Example usage
$pipeline = new DocumentPipeline($client);

$documents = [
    __DIR__ . '/documents/invoice-001.pdf',
    __DIR__ . '/documents/employment-contract.pdf',
    __DIR__ . '/documents/resume-john-doe.pdf'
];

foreach ($documents as $docPath) {
    if (!file_exists($docPath)) {
        echo "Skipping missing file: {$docPath}\n";
        continue;
    }

    echo "Processing: " . basename($docPath) . "\n";
    echo str_repeat('=', 50) . "\n";

    $result = $pipeline->processDocument($docPath);

    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
}
```

## Performance Optimization

```php
<?php
# filename: src/Document/DocumentCache.php
declare(strict_types=1);

namespace App\Document;

class DocumentCache
{
    private string $cacheDir;

    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/document_cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $pdfPath): ?array
    {
        $cacheKey = $this->getCacheKey($pdfPath);
        $cachePath = "{$this->cacheDir}/{$cacheKey}.json";

        if (!file_exists($cachePath)) {
            return null;
        }

        // Check if PDF has been modified since cache
        if (filemtime($pdfPath) > filemtime($cachePath)) {
            unlink($cachePath);
            return null;
        }

        $data = file_get_contents($cachePath);
        return json_decode($data, true);
    }

    public function set(string $pdfPath, array $data): void
    {
        $cacheKey = $this->getCacheKey($pdfPath);
        $cachePath = "{$this->cacheDir}/{$cacheKey}.json";

        file_put_contents($cachePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function getCacheKey(string $pdfPath): string
    {
        return md5($pdfPath . filesize($pdfPath) . filemtime($pdfPath));
    }

    public function clear(): void
    {
        $files = glob("{$this->cacheDir}/*.json");
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
```

## Key Takeaways

- ✓ Convert PDFs to images for best results with Claude
- ✓ Invoice processing extracts structured financial data automatically
- ✓ Contract analysis identifies risks, terms, and missing clauses
- ✓ Resume processing enables automated candidate screening
- ✓ Multi-page documents require combining data across pages
- ✓ Always validate extracted data for accuracy
- ✓ Cache processed results to avoid redundant API calls
- ✓ Combine text extraction with vision for comprehensive analysis
- ✓ Use specific prompts and schemas for reliable structured output
- ✓ Document classification enables smart routing to specialized processors

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="14"
  label="You've mastered document processing with Claude!"
/>

---

Continue to [Chapter 15: Structured Outputs with JSON](/series/claude-php-developers/chapters/15-structured-outputs) to master reliable data extraction.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 14 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-14)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-14
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-document-pipeline.php
```
