---
title: "15: Structured Outputs with JSON"
description: "Master reliable structured data extraction with Claude in PHP. Define JSON schemas, validate outputs, handle edge cases, and build robust data extraction pipelines for production applications."
series: "claude-php-developers"
chapter: 15
order: 15
difficulty: "Expert"
prerequisites:
  - "Understanding of JSON and schemas"
  - "Completed Chapters 00-05"
  - "Experience with data validation"
---

![15: Structured Outputs with JSON](/images/claude-php/chapter-15-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 15</span>
</div>

# Chapter 15: Structured Outputs with JSON

## Overview

Getting reliable, structured data from AI is critical for production applications. In this chapter, you'll master techniques for extracting consistent JSON responses from Claude, validating outputs, handling edge cases, and building robust data extraction pipelines.

You'll learn schema definition strategies, validation patterns, error recovery techniques, and how to build data extraction systems that work reliably at scale.

**What You'll Build**: A production-ready data extraction system with schema validation, error handling, retry logic, and comprehensive testing.

## Prerequisites

Before starting, ensure you have:

- ✓ **JSON and schema knowledge** (JSON Schema standard)
- ✓ **Data validation experience** in PHP
- ✓ **Completed Chapters 00-05** of this series
- ✓ **Understanding of type systems** and validation

**Estimated Time**: 45-60 minutes

## Install Validation Library

```bash
composer require justinrainbow/json-schema
composer require symfony/validator
```

## Step 1: Basic Structured Output

Start with a simple structured output example:

```php
<?php
# filename: examples/01-basic-structured-output.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

function extractContactInfo(string $text): array
{
    global $client;

    $schema = [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'email' => ['type' => 'string', 'format' => 'email'],
            'phone' => ['type' => 'string'],
            'company' => ['type' => 'string'],
            'title' => ['type' => 'string']
        ],
        'required' => ['name']
    ];

    $prompt = <<<PROMPT
Extract contact information from this text and return as JSON matching this schema:

Schema: {$schema_json}

Text: {$text}

Return ONLY valid JSON matching the schema. Use null for missing fields.
PROMPT;

    $schema_json = json_encode($schema, JSON_PRETTY_PRINT);
    $prompt = str_replace('{$schema_json}', $schema_json, $prompt);
    $prompt = str_replace('{$text}', $text, $prompt);

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]
    ]);

    $responseText = $response->content[0]->text;

    // Extract JSON from response
    if (preg_match('/```json\s*(\{.*?\})\s*```/s', $responseText, $matches)) {
        $responseText = $matches[1];
    } elseif (preg_match('/(\{.*?\})/s', $responseText, $matches)) {
        $responseText = $matches[1];
    }

    $data = json_decode($responseText, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
    }

    return $data;
}

// Example usage
$businessCard = <<<TEXT
Dr. Sarah Johnson
Chief Technology Officer
TechCorp Industries
sarah.johnson@techcorp.com
+1 (555) 123-4567
TEXT;

$contact = extractContactInfo($businessCard);
echo json_encode($contact, JSON_PRETTY_PRINT) . "\n";
```

## Step 2: Schema-Based Extraction Class

Create a reusable class for schema-based extraction:

```php
<?php
# filename: src/Extraction/SchemaExtractor.php
declare(strict_types=1);

namespace App\Extraction;

use Anthropic\Anthropic;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

class SchemaExtractor
{
    public function __construct(
        private Anthropic $client,
        private int $maxRetries = 3
    ) {}

    /**
     * Extract structured data matching a JSON schema
     */
    public function extract(string $input, array $schema, string $context = ''): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                $data = $this->attemptExtraction($input, $schema, $context, $lastError);

                // Validate against schema
                $validation = $this->validateSchema($data, $schema);

                if ($validation['valid']) {
                    return $data;
                }

                // Store validation errors for next attempt
                $lastError = implode(', ', $validation['errors']);

                if ($attempt >= $this->maxRetries) {
                    throw new \RuntimeException(
                        "Schema validation failed after {$this->maxRetries} attempts: {$lastError}"
                    );
                }

            } catch (\Exception $e) {
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                $lastError = $e->getMessage();
            }
        }

        throw new \RuntimeException('Extraction failed after maximum retries');
    }

    private function attemptExtraction(
        string $input,
        array $schema,
        string $context,
        ?string $previousError
    ): array {
        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT);

        $prompt = "Extract structured data from the input and return as JSON.\n\n";

        if ($context) {
            $prompt .= "Context: {$context}\n\n";
        }

        $prompt .= "Required JSON Schema:\n{$schemaJson}\n\n";

        if ($previousError) {
            $prompt .= "Previous attempt had errors: {$previousError}\n";
            $prompt .= "Please fix these issues and try again.\n\n";
        }

        $prompt .= "Input:\n{$input}\n\n";
        $prompt .= "Return ONLY valid JSON matching the schema exactly. No explanation or markdown.";

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.3, // Lower temperature for more consistent output
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        return $this->parseJSON($response->content[0]->text);
    }

    private function parseJSON(string $text): array
    {
        // Try to extract JSON from markdown code blocks
        if (preg_match('/```json\s*(\{.*?\}|\[.*?\])\s*```/s', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/(\{.*?\}|\[.*?\])/s', $text, $matches)) {
            $text = $matches[1];
        }

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    private function validateSchema(array $data, array $schema): array
    {
        $validator = new Validator();
        $dataObj = json_decode(json_encode($data));
        $schemaObj = json_decode(json_encode($schema));

        $validator->validate($dataObj, $schemaObj, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        $errors = [];
        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                $errors[] = sprintf("[%s] %s", $error['property'], $error['message']);
            }
        }

        return [
            'valid' => $validator->isValid(),
            'errors' => $errors
        ];
    }

    /**
     * Extract array of items
     */
    public function extractList(string $input, array $itemSchema, string $context = ''): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'items' => $itemSchema
                ]
            ],
            'required' => ['items']
        ];

        $result = $this->extract($input, $schema, $context);
        return $result['items'] ?? [];
    }
}
```

## Step 3: Common Data Extraction Schemas

Pre-built schemas for common use cases:

```php
<?php
# filename: src/Extraction/Schemas.php
declare(strict_types=1);

namespace App\Extraction;

class Schemas
{
    public static function person(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'first_name' => ['type' => 'string'],
                'last_name' => ['type' => 'string'],
                'email' => [
                    'type' => 'string',
                    'format' => 'email'
                ],
                'phone' => ['type' => 'string'],
                'company' => ['type' => 'string'],
                'title' => ['type' => 'string'],
                'address' => [
                    'type' => 'object',
                    'properties' => [
                        'street' => ['type' => 'string'],
                        'city' => ['type' => 'string'],
                        'state' => ['type' => 'string'],
                        'zip' => ['type' => 'string'],
                        'country' => ['type' => 'string']
                    ]
                ]
            ],
            'required' => ['first_name', 'last_name']
        ];
    }

    public static function product(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'sku' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'price' => [
                    'type' => 'number',
                    'minimum' => 0
                ],
                'currency' => [
                    'type' => 'string',
                    'default' => 'USD'
                ],
                'category' => ['type' => 'string'],
                'brand' => ['type' => 'string'],
                'in_stock' => ['type' => 'boolean'],
                'specifications' => [
                    'type' => 'object',
                    'additionalProperties' => ['type' => 'string']
                ],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string']
                ]
            ],
            'required' => ['name', 'price']
        ];
    }

    public static function event(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'start_date' => [
                    'type' => 'string',
                    'format' => 'date-time'
                ],
                'end_date' => [
                    'type' => 'string',
                    'format' => 'date-time'
                ],
                'location' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'address' => ['type' => 'string'],
                        'city' => ['type' => 'string'],
                        'virtual' => ['type' => 'boolean']
                    ]
                ],
                'attendees' => [
                    'type' => 'array',
                    'items' => ['type' => 'string']
                ],
                'organizer' => ['type' => 'string']
            ],
            'required' => ['title', 'start_date']
        ];
    }

    public static function article(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'author' => ['type' => 'string'],
                'published_date' => [
                    'type' => 'string',
                    'format' => 'date'
                ],
                'summary' => [
                    'type' => 'string',
                    'maxLength' => 500
                ],
                'content' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string']
                ],
                'reading_time_minutes' => [
                    'type' => 'integer',
                    'minimum' => 1
                ],
                'url' => [
                    'type' => 'string',
                    'format' => 'uri'
                ]
            ],
            'required' => ['title', 'content']
        ];
    }

    public static function transaction(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'transaction_id' => ['type' => 'string'],
                'date' => [
                    'type' => 'string',
                    'format' => 'date'
                ],
                'amount' => [
                    'type' => 'number',
                    'minimum' => 0
                ],
                'currency' => ['type' => 'string'],
                'type' => [
                    'type' => 'string',
                    'enum' => ['debit', 'credit', 'transfer']
                ],
                'description' => ['type' => 'string'],
                'merchant' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'status' => [
                    'type' => 'string',
                    'enum' => ['pending', 'completed', 'failed', 'cancelled']
                ]
            ],
            'required' => ['transaction_id', 'date', 'amount', 'type']
        ];
    }
}
```

## Step 4: Advanced Extraction Pipeline

Build a complete extraction pipeline with validation and error handling:

```php
<?php
# filename: examples/02-extraction-pipeline.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Extraction\SchemaExtractor;
use App\Extraction\Schemas;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$extractor = new SchemaExtractor($client);

// Example 1: Extract products from description
echo "=== Extracting Products ===\n";

$productText = <<<TEXT
We have several great items in stock:

1. MacBook Pro 16" with M3 chip - \$2499
   Professional laptop with incredible performance
   In stock, free shipping

2. iPhone 15 Pro (256GB) - \$1099
   Latest flagship phone, available in all colors
   Category: Smartphones

3. AirPods Pro (2nd gen) - \$249
   Premium wireless earbuds with ANC
   Brand: Apple, Currently in stock
TEXT;

$products = $extractor->extractList(
    $productText,
    Schemas::product(),
    'Extract product information from this product listing'
);

echo json_encode($products, JSON_PRETTY_PRINT) . "\n\n";

// Example 2: Extract events from email
echo "=== Extracting Events ===\n";

$emailText = <<<TEXT
Hi team,

Quick reminder about our upcoming meetings:

Tech Review Meeting
March 15, 2025 at 2:00 PM - 3:30 PM
Conference Room A
Organizer: Sarah Johnson

Client Presentation
March 18, 2025 at 10:00 AM - 11:00 AM
Virtual (Zoom link to follow)
Organizer: Mike Chen
TEXT;

$events = $extractor->extractList(
    $emailText,
    Schemas::event(),
    'Extract all meeting/event information from this email'
);

echo json_encode($events, JSON_PRETTY_PRINT) . "\n\n";

// Example 3: Extract transactions from bank statement
echo "=== Extracting Transactions ===\n";

$statementText = <<<TEXT
Recent Transactions:

03/10/2025 - Amazon.com - \$87.43 - Online Shopping - Completed
03/11/2025 - Starbucks - \$5.67 - Food & Dining - Completed
03/12/2025 - Salary Deposit - \$3,500.00 - Income - Completed
03/13/2025 - Rent Payment - \$1,800.00 - Housing - Pending
TEXT;

$transactions = $extractor->extractList(
    $statementText,
    Schemas::transaction(),
    'Extract transaction information from this bank statement'
);

echo json_encode($transactions, JSON_PRETTY_PRINT) . "\n\n";
```

## Step 5: Custom Validators

Add custom validation logic beyond JSON schema:

```php
<?php
# filename: src/Extraction/CustomValidator.php
declare(strict_types=1);

namespace App\Extraction;

class CustomValidator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule => $params) {
                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $params);
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateEmail(string $field, $value, $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "Invalid email format";
        }
    }

    private function validatePhone(string $field, $value, $params): void
    {
        if ($value && !preg_match('/^\+?[\d\s\-\(\)]+$/', $value)) {
            $this->errors[$field][] = "Invalid phone format";
        }
    }

    private function validateUrl(string $field, $value, $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = "Invalid URL format";
        }
    }

    private function validateDate(string $field, $value, $params): void
    {
        if ($value) {
            $date = \DateTime::createFromFormat($params['format'] ?? 'Y-m-d', $value);
            if (!$date || $date->format($params['format'] ?? 'Y-m-d') !== $value) {
                $this->errors[$field][] = "Invalid date format";
            }
        }
    }

    private function validateRange(string $field, $value, $params): void
    {
        if ($value !== null) {
            if (isset($params['min']) && $value < $params['min']) {
                $this->errors[$field][] = "Value below minimum {$params['min']}";
            }
            if (isset($params['max']) && $value > $params['max']) {
                $this->errors[$field][] = "Value above maximum {$params['max']}";
            }
        }
    }

    private function validateEnum(string $field, $value, $params): void
    {
        if ($value && !in_array($value, $params['values'])) {
            $this->errors[$field][] = "Value not in allowed list: " . implode(', ', $params['values']);
        }
    }

    private function validatePattern(string $field, $value, $params): void
    {
        if ($value && !preg_match($params['regex'], $value)) {
            $this->errors[$field][] = $params['message'] ?? "Value doesn't match required pattern";
        }
    }

    private function validateLength(string $field, $value, $params): void
    {
        if ($value) {
            $length = strlen($value);
            if (isset($params['min']) && $length < $params['min']) {
                $this->errors[$field][] = "Length below minimum {$params['min']}";
            }
            if (isset($params['max']) && $length > $params['max']) {
                $this->errors[$field][] = "Length above maximum {$params['max']}";
            }
        }
    }

    private function validateRequired(string $field, $value, $params): void
    {
        if ($params && ($value === null || $value === '')) {
            $this->errors[$field][] = "Field is required";
        }
    }
}
```

## Step 6: Batch Extraction

Process multiple items efficiently:

```php
<?php
# filename: examples/03-batch-extraction.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Extraction\SchemaExtractor;
use App\Extraction\Schemas;

class BatchExtractor
{
    public function __construct(
        private SchemaExtractor $extractor
    ) {}

    public function extractBatch(array $inputs, array $schema, string $context = ''): array
    {
        $results = [];
        $errors = [];

        foreach ($inputs as $index => $input) {
            try {
                $results[$index] = $this->extractor->extract($input, $schema, $context);
            } catch (\Exception $e) {
                $errors[$index] = [
                    'error' => $e->getMessage(),
                    'input' => $input
                ];
            }
        }

        return [
            'results' => $results,
            'errors' => $errors,
            'success_count' => count($results),
            'error_count' => count($errors),
            'total' => count($inputs)
        ];
    }

    public function extractFromMultipleTexts(string $combinedText, array $itemSchema): array
    {
        // Let Claude extract all items in one go
        return $this->extractor->extractList($combinedText, $itemSchema);
    }
}

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$extractor = new SchemaExtractor($client);
$batchExtractor = new BatchExtractor($extractor);

// Example: Extract contacts from multiple business cards
$businessCards = [
    "John Smith\nCEO, TechCorp\njohn@techcorp.com\n555-1234",
    "Jane Doe\nCTO, StartupXYZ\njane.doe@startupxyz.com\n555-5678",
    "Bob Johnson\nVP Engineering, MegaSoft\nbob.j@megasoft.com\n555-9999"
];

$result = $batchExtractor->extractBatch(
    $businessCards,
    Schemas::person(),
    'Extract contact information from business card'
);

echo "Batch Extraction Results:\n";
echo "Success: {$result['success_count']}/{$result['total']}\n";
echo "Errors: {$result['error_count']}\n\n";

echo "Extracted Data:\n";
echo json_encode($result['results'], JSON_PRETTY_PRINT) . "\n";
```

## Step 7: Streaming Structured Output

For large outputs, process data as it streams:

```php
<?php
# filename: examples/04-streaming-structured.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class StreamingExtractor
{
    private string $buffer = '';

    public function extractWithStreaming(string $input, array $schema): array
    {
        global $client;

        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Extract data from this input as JSON matching this schema:

{$schemaJson}

Input:
{$input}

Return only valid JSON.
PROMPT;

        $stream = $client->messages()->createStreamed([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $this->buffer = '';

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta') {
                if (isset($event->delta->text)) {
                    $this->buffer .= $event->delta->text;
                    echo "."; // Progress indicator
                }
            }
        }

        echo "\n";

        return $this->parseJSON($this->buffer);
    }

    private function parseJSON(string $text): array
    {
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            $text = $matches[1];
        }

        return json_decode($text, true) ?? [];
    }
}
```

## Best Practices

### 1. Schema Design

```php
// ✓ Good: Clear, specific schema
$schema = [
    'type' => 'object',
    'properties' => [
        'price' => [
            'type' => 'number',
            'minimum' => 0,
            'description' => 'Price in USD'
        ],
        'date' => [
            'type' => 'string',
            'pattern' => '^\d{4}-\d{2}-\d{2}$',
            'description' => 'Date in YYYY-MM-DD format'
        ]
    ],
    'required' => ['price', 'date']
];

// ❌ Bad: Vague, loose schema
$schema = [
    'type' => 'object',
    'properties' => [
        'data' => ['type' => 'string']
    ]
];
```

### 2. Error Recovery

```php
// Always implement retry logic
try {
    $data = $extractor->extract($input, $schema);
} catch (\Exception $e) {
    // Log error
    error_log("Extraction failed: " . $e->getMessage());

    // Fallback strategy
    $data = $this->manualExtraction($input);
}
```

### 3. Validation Layers

```php
// Layer 1: JSON Schema validation
$schemaValidation = $validator->validateSchema($data, $schema);

// Layer 2: Custom business logic
$customValidation = $customValidator->validate($data, $rules);

// Layer 3: Data sanitization
$sanitized = $this->sanitizeData($data);
```

## Key Takeaways

- ✓ Use explicit JSON schemas to define expected output structure
- ✓ Implement retry logic with error feedback for robust extraction
- ✓ Validate outputs using both JSON Schema and custom validators
- ✓ Extract JSON from markdown code blocks automatically
- ✓ Lower temperature (0.3-0.5) produces more consistent structured outputs
- ✓ Pre-built schemas accelerate common extraction tasks
- ✓ Batch processing improves efficiency for multiple items
- ✓ Always handle JSON parsing errors gracefully
- ✓ Include descriptions in schemas to improve extraction accuracy
- ✓ Test extraction with edge cases and malformed inputs

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="15"
  label="You've mastered structured outputs with Claude!"
/>

---

Congratulations! You've completed the Advanced Features section. Continue to explore more advanced topics or revisit earlier chapters to deepen your understanding.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 15 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-15)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-15
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-basic-structured-output.php
```
