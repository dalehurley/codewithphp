---
title: "03: Your First Claude Request in PHP"
description: "Master making Claude API calls with detailed examples using Guzzle HTTP client and the official SDK. Learn request structure, response parsing, error handling, and best practices."
series: "claude-php-developers"
chapter: 3
order: 3
difficulty: "Beginner"
prerequisites:
  - "PHP 8.2+ installed"
  - "Composer installed"
  - "Completion of Chapters 01-02"
  - "Anthropic API key configured"
---

![03: Your First Claude Request in PHP](/images/claude-php/chapter-03-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 03</span>
</div>

# Chapter 03: Your First Claude Request in PHP

## Overview

Making your first successful API request is a milestone in any integration project. This chapter provides a comprehensive guide to making Claude API calls using both raw HTTP requests with Guzzle and the official Anthropic PHP SDK.

You'll learn the complete anatomy of API requests and responses, how to properly structure your calls, parse responses effectively, handle errors gracefully, and follow best practices for production environments. By the end, you'll be confident making reliable, efficient Claude API calls.

**What You'll Learn:**
- Making API calls with Guzzle HTTP client
- Using the official Anthropic PHP SDK
- Request structure and parameters
- Response parsing and data extraction
- Comprehensive error handling
- Request/response debugging
- Performance optimization

**Estimated Time**: 35-45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 01-02**
- ✓ **PHP 8.2+** installed and working
- ✓ **Composer** for dependency management
- ✓ **Anthropic API key** configured in environment
- ✓ **Basic HTTP/REST API knowledge**

## Installation

### Installing the Anthropic SDK

The official SDK is the recommended way to interact with Claude:

```bash
composer require anthropic-ai/sdk
```

### Installing Guzzle (Optional)

For direct HTTP requests or if SDK doesn't meet your needs:

```bash
composer require guzzlehttp/guzzle
```

### Project Structure

Create a clean project structure:

```bash
mkdir claude-requests && cd claude-requests
composer init --no-interaction
composer require anthropic-ai/sdk vlucas/phpdotenv
mkdir -p src/{Services,Models,Exceptions} examples tests
```

Create `.env` file:

```bash
# .env
ANTHROPIC_API_KEY=sk-ant-your-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=2048
```

## Making Requests with the SDK

### Basic Request

The simplest possible request:

```php
<?php
# filename: examples/01-basic-request.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

// Initialize client
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Make request
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello, Claude! Tell me about PHP 8.3 features.'
        ]
    ]
]);

// Output response
echo $response->content[0]->text . "\n";
```

Run it:

```bash
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-basic-request.php
```

### Request with All Parameters

Complete example showing all available parameters:

```php
<?php
# filename: examples/02-complete-request.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    // === Required Parameters ===

    'model' => 'claude-sonnet-4-20250514',

    'max_tokens' => 2048,

    'messages' => [
        [
            'role' => 'user',
            'content' => 'Explain Laravel service providers in detail.'
        ]
    ],

    // === Optional Parameters ===

    // System prompt - sets behavior and context
    'system' => 'You are an expert Laravel developer. Provide detailed, practical explanations with code examples.',

    // Temperature: 0.0 (deterministic) to 1.0 (creative)
    // Lower = more focused, higher = more varied
    'temperature' => 0.7,

    // Top-p (nucleus sampling): 0.0 to 1.0
    // Lower = more focused, higher = more diverse
    'top_p' => 0.9,

    // Top-k sampling: limits token selection
    'top_k' => 40,

    // Stop sequences - halt generation when encountered
    'stop_sequences' => ['</answer>', 'STOP', '---END---'],

    // Metadata for tracking (user_id required if provided)
    'metadata' => [
        'user_id' => 'user-12345',
    ],
]);

echo "Response:\n";
echo $response->content[0]->text . "\n\n";

echo "Model used: {$response->model}\n";
echo "Stop reason: {$response->stop_reason}\n";
echo "Input tokens: {$response->usage->inputTokens}\n";
echo "Output tokens: {$response->usage->outputTokens}\n";
```

### Request Builder Pattern

Create a reusable request builder:

```php
<?php
# filename: src/Services/ClaudeRequestBuilder.php
declare(strict_types=1);

namespace App\Services;

class ClaudeRequestBuilder
{
    private string $model = 'claude-sonnet-4-20250514';
    private int $maxTokens = 2048;
    private array $messages = [];
    private ?string $system = null;
    private float $temperature = 1.0;
    private ?array $stopSequences = null;
    private ?array $metadata = null;

    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function maxTokens(int $tokens): self
    {
        $this->maxTokens = $tokens;
        return $this;
    }

    public function userMessage(string $content): self
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $content
        ];
        return $this;
    }

    public function assistantMessage(string $content): self
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content
        ];
        return $this;
    }

    public function system(string $prompt): self
    {
        $this->system = $prompt;
        return $this;
    }

    public function temperature(float $temp): self
    {
        if ($temp < 0 || $temp > 1) {
            throw new \InvalidArgumentException('Temperature must be between 0.0 and 1.0');
        }
        $this->temperature = $temp;
        return $this;
    }

    public function stopSequences(array $sequences): self
    {
        $this->stopSequences = $sequences;
        return $this;
    }

    public function metadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function build(): array
    {
        if (empty($this->messages)) {
            throw new \RuntimeException('At least one message is required');
        }

        $params = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->messages,
            'temperature' => $this->temperature,
        ];

        if ($this->system !== null) {
            $params['system'] = $this->system;
        }

        if ($this->stopSequences !== null) {
            $params['stop_sequences'] = $this->stopSequences;
        }

        if ($this->metadata !== null) {
            $params['metadata'] = $this->metadata;
        }

        return $params;
    }
}
```

**Usage:**

```php
<?php
# filename: examples/03-request-builder.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\ClaudeRequestBuilder;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Build request fluently
$builder = new ClaudeRequestBuilder();
$params = $builder
    ->model('claude-sonnet-4-20250514')
    ->maxTokens(1500)
    ->system('You are a PHP expert specializing in Laravel.')
    ->temperature(0.7)
    ->userMessage('How do I implement custom validation rules in Laravel?')
    ->stopSequences(['</answer>'])
    ->metadata(['user_id' => 'demo-user'])
    ->build();

// Make request
$response = $client->messages()->create($params);

echo $response->content[0]->text . "\n";
```

## Understanding Request Structure

### Messages Array

Messages must follow specific rules:

```php
<?php
# Valid: Single user message
$messages = [
    ['role' => 'user', 'content' => 'Hello!']
];

# Valid: Conversation with alternating roles
$messages = [
    ['role' => 'user', 'content' => 'What is dependency injection?'],
    ['role' => 'assistant', 'content' => 'Dependency injection is a design pattern...'],
    ['role' => 'user', 'content' => 'Can you show an example?'],
];

# Invalid: Consecutive user messages
$messages = [
    ['role' => 'user', 'content' => 'Hello!'],
    ['role' => 'user', 'content' => 'Are you there?'],  // ERROR
];

# Invalid: Starting with assistant
$messages = [
    ['role' => 'assistant', 'content' => 'Hello!'],  // ERROR
];

# Invalid: Empty messages array
$messages = [];  // ERROR
```

### System Prompts

System prompts set the context for Claude's behavior:

```php
<?php
# filename: examples/04-system-prompts.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Example 1: Code reviewer
$codeReviewResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2000,
    'system' => 'You are a senior PHP code reviewer. Focus on security, performance, and best practices. Be concise but thorough.',
    'messages' => [
        [
            'role' => 'user',
            'content' => "Review this code:\n\n" . $codeToReview
        ]
    ]
]);

// Example 2: Documentation writer
$docsResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 3000,
    'system' => 'You are a technical documentation writer. Write clear, comprehensive docs with examples. Follow PSR-5 PHPDoc standards.',
    'messages' => [
        [
            'role' => 'user',
            'content' => "Generate PHPDoc comments for:\n\n" . $classCode
        ]
    ]
]);

// Example 3: Data analyzer
$analysisResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1500,
    'system' => 'You are a data analyst. Provide statistical insights and actionable recommendations based on data. Always include confidence levels.',
    'messages' => [
        [
            'role' => 'user',
            'content' => "Analyze this sales data:\n\n" . json_encode($salesData)
        ]
    ]
]);
```

### Temperature and Sampling Parameters

Control randomness and creativity:

```php
<?php
# filename: examples/05-temperature-examples.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$prompt = 'Write a short story about a PHP developer.';

// Temperature 0.0 - Most deterministic
// Same input = same output (mostly)
// Best for: factual responses, code generation, data extraction
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'temperature' => 0.0,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);

echo "Temperature 0.0 (Deterministic):\n";
echo $response1->content[0]->text . "\n\n";

// Temperature 0.5 - Balanced
// Some variation, still focused
// Best for: general conversation, explanations
$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'temperature' => 0.5,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);

echo "Temperature 0.5 (Balanced):\n";
echo $response2->content[0]->text . "\n\n";

// Temperature 1.0 - Most creative (default)
// High variation and creativity
// Best for: creative writing, brainstorming, diverse outputs
$response3 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'temperature' => 1.0,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);

echo "Temperature 1.0 (Creative):\n";
echo $response3->content[0]->text . "\n\n";
```

**Parameter Recommendations:**

| Use Case | Temperature | Top-p | Top-k |
|----------|-------------|-------|-------|
| Code Generation | 0.0 - 0.3 | 0.1 | 10 |
| Data Extraction | 0.0 - 0.2 | 0.1 | 5 |
| Technical Documentation | 0.3 - 0.5 | 0.5 | 20 |
| General Conversation | 0.7 - 0.9 | 0.9 | 40 |
| Creative Writing | 0.9 - 1.0 | 0.95 | 50 |
| Brainstorming | 1.0 | 1.0 | 100 |

## Parsing Responses

### Basic Response Structure

```php
<?php
# filename: examples/06-response-parsing.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Explain PHP namespaces.']
    ]
]);

// Response object properties
echo "=== Response Structure ===\n";
echo "ID: {$response->id}\n";                          // msg_01ABC...
echo "Type: {$response->type}\n";                      // "message"
echo "Role: {$response->role}\n";                      // "assistant"
echo "Model: {$response->model}\n";                    // "claude-sonnet-4..."
echo "Stop Reason: {$response->stop_reason}\n";        // "end_turn"
echo "Stop Sequence: " . ($response->stop_sequence ?? 'null') . "\n";

// Content array (usually has one text content block)
echo "\n=== Content ===\n";
foreach ($response->content as $index => $block) {
    echo "Block {$index}:\n";
    echo "  Type: {$block->type}\n";                   // "text"
    echo "  Text: {$block->text}\n";                   // The actual response
}

// Usage statistics
echo "\n=== Usage ===\n";
echo "Input Tokens: {$response->usage->inputTokens}\n";
echo "Output Tokens: {$response->usage->outputTokens}\n";
echo "Total Tokens: " . ($response->usage->inputTokens + $response->usage->outputTokens) . "\n";
```

### Response Model Class

Create a type-safe response wrapper:

```php
<?php
# filename: src/Models/ClaudeResponse.php
declare(strict_types=1);

namespace App\Models;

class ClaudeResponse
{
    public function __construct(
        private readonly string $id,
        private readonly string $text,
        private readonly string $model,
        private readonly string $stopReason,
        private readonly int $inputTokens,
        private readonly int $outputTokens,
        private readonly ?string $stopSequence = null,
        private readonly ?array $rawResponse = null
    ) {}

    public static function fromSdkResponse($response): self
    {
        return new self(
            id: $response->id,
            text: $response->content[0]->text,
            model: $response->model,
            stopReason: $response->stop_reason,
            inputTokens: $response->usage->inputTokens,
            outputTokens: $response->usage->outputTokens,
            stopSequence: $response->stop_sequence,
            rawResponse: $response->toArray()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getStopReason(): string
    {
        return $this->stopReason;
    }

    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }

    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }

    public function wasStoppedBySequence(): bool
    {
        return $this->stopSequence !== null;
    }

    public function getStopSequence(): ?string
    {
        return $this->stopSequence;
    }

    public function estimateCost(): float
    {
        $pricing = match($this->model) {
            'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
            'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
            'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        $inputCost = ($this->inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($this->outputTokens / 1_000_000) * $pricing['output'];

        return $inputCost + $outputCost;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'model' => $this->model,
            'stop_reason' => $this->stopReason,
            'stop_sequence' => $this->stopSequence,
            'tokens' => [
                'input' => $this->inputTokens,
                'output' => $this->outputTokens,
                'total' => $this->getTotalTokens(),
            ],
            'cost' => $this->estimateCost(),
        ];
    }
}
```

**Usage:**

```php
<?php
# filename: examples/07-response-model.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Models\ClaudeResponse;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$sdkResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Explain PHP traits.']
    ]
]);

// Wrap in type-safe model
$response = ClaudeResponse::fromSdkResponse($sdkResponse);

// Use clean API
echo "Response:\n{$response->getText()}\n\n";
echo "Tokens used: {$response->getTotalTokens()}\n";
echo "Estimated cost: $" . number_format($response->estimateCost(), 6) . "\n";

// Export as array
$data = $response->toArray();
file_put_contents('response.json', json_encode($data, JSON_PRETTY_PRINT));
```

### Extracting JSON from Responses

Claude often returns JSON, but may wrap it in markdown:

```php
<?php
# filename: src/Services/JsonExtractor.php
declare(strict_types=1);

namespace App\Services;

class JsonExtractor
{
    /**
     * Extract and parse JSON from Claude's response
     * Handles markdown code blocks and plain JSON
     */
    public static function extract(string $text): ?array
    {
        // Try to extract from markdown code block
        if (preg_match('/```json\s*(\{.*?\}|\[.*?\])\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true);
        }

        // Try to extract from code block without language
        if (preg_match('/```\s*(\{.*?\}|\[.*?\])\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true);
        }

        // Try to find raw JSON
        if (preg_match('/(\{.*?\}|\[.*?\])/s', $text, $matches)) {
            $decoded = json_decode($matches[1], true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Extract and validate JSON with schema
     */
    public static function extractWithValidation(string $text, array $requiredKeys): ?array
    {
        $data = self::extract($text);

        if ($data === null) {
            return null;
        }

        // Validate required keys
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                throw new \RuntimeException("Missing required key: {$key}");
            }
        }

        return $data;
    }
}
```

**Usage:**

```php
<?php
# filename: examples/08-json-extraction.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\JsonExtractor;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages' => [[
        'role' => 'user',
        'content' => 'Return user data as JSON: name="John Doe", age=30, city="New York". Include only valid JSON, no explanation.'
    ]]
]);

$text = $response->content[0]->text;
echo "Raw response:\n{$text}\n\n";

// Extract JSON
$data = JsonExtractor::extract($text);

if ($data) {
    echo "Extracted data:\n";
    print_r($data);
} else {
    echo "Failed to extract JSON\n";
}

// Extract with validation
try {
    $validated = JsonExtractor::extractWithValidation($text, ['name', 'age', 'city']);
    echo "Validated data:\n";
    print_r($validated);
} catch (\RuntimeException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
}
```

## Error Handling

### Exception Types

The SDK throws several exception types:

```php
<?php
# filename: examples/09-error-handling.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Exceptions\{
    ErrorException,
    RateLimitException,
    ValidationException,
    AuthenticationException,
    PermissionDeniedException,
    NotFoundException,
    UnprocessableEntityException
};

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ]);

    echo $response->content[0]->text;

} catch (AuthenticationException $e) {
    // Invalid API key
    error_log("Authentication failed: " . $e->getMessage());
    echo "Error: Invalid API key\n";

} catch (RateLimitException $e) {
    // Rate limit exceeded
    error_log("Rate limit exceeded: " . $e->getMessage());
    echo "Error: Too many requests. Please try again later.\n";

} catch (ValidationException $e) {
    // Invalid request parameters
    error_log("Validation error: " . $e->getMessage());
    echo "Error: Invalid request parameters\n";

} catch (PermissionDeniedException $e) {
    // Insufficient permissions
    error_log("Permission denied: " . $e->getMessage());
    echo "Error: You don't have permission for this operation\n";

} catch (NotFoundException $e) {
    // Resource not found (e.g., invalid model)
    error_log("Not found: " . $e->getMessage());
    echo "Error: Requested resource not found\n";

} catch (UnprocessableEntityException $e) {
    // Request understood but cannot be processed
    error_log("Unprocessable: " . $e->getMessage());
    echo "Error: Request cannot be processed\n";

} catch (ErrorException $e) {
    // General API error
    error_log("API error: " . $e->getMessage());
    echo "Error: API request failed\n";

} catch (\Exception $e) {
    // Unexpected errors
    error_log("Unexpected error: " . $e->getMessage());
    echo "Error: An unexpected error occurred\n";
}
```

### Retry Logic with Exponential Backoff

```php
<?php
# filename: src/Services/RetryableClaudeClient.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;
use Anthropic\Exceptions\RateLimitException;
use Anthropic\Exceptions\ErrorException;

class RetryableClaudeClient
{
    private const MAX_RETRIES = 3;
    private const INITIAL_DELAY = 1; // seconds

    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function createMessage(array $params): mixed
    {
        $attempt = 0;
        $delay = self::INITIAL_DELAY;

        while ($attempt < self::MAX_RETRIES) {
            try {
                return $this->client->messages()->create($params);

            } catch (RateLimitException $e) {
                $attempt++;

                if ($attempt >= self::MAX_RETRIES) {
                    throw $e;
                }

                // Exponential backoff: 1s, 2s, 4s, 8s...
                error_log("Rate limited. Retrying in {$delay}s (attempt {$attempt}/" . self::MAX_RETRIES . ")");
                sleep($delay);
                $delay *= 2;

            } catch (ErrorException $e) {
                // Check if error is transient (5xx status codes)
                if ($this->isTransientError($e)) {
                    $attempt++;

                    if ($attempt >= self::MAX_RETRIES) {
                        throw $e;
                    }

                    error_log("Transient error. Retrying in {$delay}s");
                    sleep($delay);
                    $delay *= 2;
                } else {
                    // Non-transient error, don't retry
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Max retries exceeded');
    }

    private function isTransientError(ErrorException $e): bool
    {
        $message = $e->getMessage();

        // Check for 5xx errors (server errors)
        return str_contains($message, '500')
            || str_contains($message, '502')
            || str_contains($message, '503')
            || str_contains($message, '504');
    }
}
```

**Usage:**

```php
<?php
# filename: examples/10-retry-logic.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\RetryableClaudeClient;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$retryableClient = new RetryableClaudeClient($client);

try {
    $response = $retryableClient->createMessage([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ]);

    echo $response->content[0]->text;

} catch (\Exception $e) {
    echo "Request failed after retries: " . $e->getMessage() . "\n";
}
```

## Making Requests with Guzzle (Direct HTTP)

For advanced use cases or when SDK doesn't fit your needs:

```php
<?php
# filename: examples/11-guzzle-request.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$apiKey = getenv('ANTHROPIC_API_KEY');

$client = new Client([
    'base_uri' => 'https://api.anthropic.com',
    'timeout' => 30.0,
    'headers' => [
        'x-api-key' => $apiKey,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json',
    ]
]);

try {
    $response = $client->post('/v1/messages', [
        'json' => [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, Claude!'
                ]
            ]
        ]
    ]);

    $body = json_decode($response->getBody()->getContents(), true);

    echo "Response:\n";
    echo $body['content'][0]['text'] . "\n\n";

    echo "Usage:\n";
    echo "Input tokens: {$body['usage']['input_tokens']}\n";
    echo "Output tokens: {$body['usage']['output_tokens']}\n";

} catch (GuzzleException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Guzzle with Retry Middleware

```php
<?php
# filename: examples/12-guzzle-retry.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$apiKey = getenv('ANTHROPIC_API_KEY');

// Create handler with retry middleware
$handlerStack = HandlerStack::create();

$handlerStack->push(Middleware::retry(
    function (
        int $retries,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?RequestException $exception = null
    ) {
        // Don't retry after 3 attempts
        if ($retries >= 3) {
            return false;
        }

        // Retry on server errors (5xx) or rate limits (429)
        if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504])) {
            return true;
        }

        // Retry on connection errors
        if ($exception instanceof RequestException) {
            return true;
        }

        return false;
    },
    function (int $retries) {
        // Exponential backoff: 1s, 2s, 4s
        return 1000 * (2 ** ($retries - 1));
    }
));

$client = new Client([
    'handler' => $handlerStack,
    'base_uri' => 'https://api.anthropic.com',
    'timeout' => 30.0,
    'headers' => [
        'x-api-key' => $apiKey,
        'anthropic-version' => '2023-06-01',
        'Content-Type' => 'application/json',
    ]
]);

try {
    $response = $client->post('/v1/messages', [
        'json' => [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!']
            ]
        ]
    ]);

    $body = json_decode($response->getBody()->getContents(), true);
    echo $body['content'][0]['text'] . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Request Debugging

### Logging Requests and Responses

```php
<?php
# filename: src/Services/DebuggableClaudeClient.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DebuggableClaudeClient
{
    public function __construct(
        private readonly Anthropic $client,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly bool $debugMode = false
    ) {}

    public function createMessage(array $params): mixed
    {
        $requestId = uniqid('req_', true);
        $startTime = microtime(true);

        if ($this->debugMode) {
            $this->logger->debug("Claude API Request", [
                'request_id' => $requestId,
                'model' => $params['model'] ?? 'unknown',
                'params' => $this->sanitizeParams($params),
            ]);
        }

        try {
            $response = $this->client->messages()->create($params);

            $duration = microtime(true) - $startTime;

            if ($this->debugMode) {
                $this->logger->debug("Claude API Response", [
                    'request_id' => $requestId,
                    'duration' => round($duration, 3),
                    'model' => $response->model,
                    'stop_reason' => $response->stop_reason,
                    'input_tokens' => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
                    'response_preview' => substr($response->content[0]->text, 0, 100),
                ]);
            }

            return $response;

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            $this->logger->error("Claude API Error", [
                'request_id' => $requestId,
                'duration' => round($duration, 3),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    private function sanitizeParams(array $params): array
    {
        // Don't log full message content in production
        $sanitized = $params;

        if (isset($sanitized['messages'])) {
            $sanitized['messages'] = array_map(function($msg) {
                return [
                    'role' => $msg['role'],
                    'content_length' => strlen($msg['content']),
                    'content_preview' => substr($msg['content'], 0, 50) . '...',
                ];
            }, $sanitized['messages']);
        }

        return $sanitized;
    }
}
```

## Performance Optimization

### Request Timeout Configuration

```php
<?php
# filename: examples/13-timeout-config.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use GuzzleHttp\Client;

// Configure HTTP client with custom timeout
$httpClient = new Client([
    'timeout' => 60.0,           // Total request timeout
    'connect_timeout' => 10.0,   // Connection timeout
]);

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($httpClient)
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,  // Large response
    'messages' => [
        ['role' => 'user', 'content' => 'Write a comprehensive guide to PHP design patterns.']
    ]
]);

echo $response->content[0]->text;
```

### Connection Pooling

```php
<?php
# filename: src/Services/PooledClaudeClient.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlMultiHandler;

class PooledClaudeClient
{
    private static ?Anthropic $instance = null;

    public static function getInstance(): Anthropic
    {
        if (self::$instance === null) {
            // Create handler with connection pooling
            $handler = new CurlMultiHandler([
                'max_handles' => 100,  // Maximum concurrent connections
            ]);

            $stack = HandlerStack::create($handler);

            $httpClient = new Client([
                'handler' => $stack,
                'timeout' => 30.0,
            ]);

            self::$instance = Anthropic::factory()
                ->withApiKey(getenv('ANTHROPIC_API_KEY'))
                ->withHttpClient($httpClient)
                ->make();
        }

        return self::$instance;
    }
}
```

## Exercises

### Exercise 1: Request Validator

Build a request validator that checks parameters before sending:

```php
<?php
class RequestValidator
{
    public function validate(array $params): void
    {
        // TODO: Validate required fields (model, max_tokens, messages)
        // TODO: Validate model is valid Claude model
        // TODO: Validate max_tokens range (1-16384)
        // TODO: Validate messages array structure
        // TODO: Validate temperature range (0.0-1.0)
        // TODO: Throw exception if invalid
    }
}
```

### Exercise 2: Response Cache

Implement a caching layer to avoid duplicate requests:

```php
<?php
class CachedClaudeClient
{
    public function createMessage(array $params): mixed
    {
        // TODO: Generate cache key from params
        // TODO: Check if cached response exists
        // TODO: Return cached response if found
        // TODO: Make API request if not cached
        // TODO: Store response in cache
        // TODO: Return response
    }
}
```

### Exercise 3: Cost Tracker

Create a system to track and limit API costs:

```php
<?php
class CostTracker
{
    private float $dailyLimit = 10.00; // $10 per day

    public function trackAndLimit(array $params): void
    {
        // TODO: Estimate request cost
        // TODO: Get today's spending
        // TODO: Check if request would exceed limit
        // TODO: Throw exception if over limit
        // TODO: Record request for tracking
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use match expression for valid models, check isset() for required fields, validate ranges with conditional checks.

**Exercise 2**: Use md5(json_encode($params)) for cache key, store in Redis or file system with TTL, implement cache warming for common queries.

**Exercise 3**: Store daily costs in database with date grouping, calculate estimated cost before request, implement budget warnings at 80% threshold.

</details>

## Troubleshooting

**Request timeout errors?**
- Increase timeout configuration
- Reduce max_tokens for faster responses
- Check network connectivity
- Try different model (Haiku is faster)

**Invalid request parameters?**
- Validate messages array structure
- Ensure messages alternate user/assistant
- Check max_tokens is in valid range
- Verify model name spelling

**Authentication errors?**
- Check API key format (starts with sk-ant-)
- Verify environment variable is set
- Ensure API key is active in console
- Check payment method is valid

**Rate limit errors?**
- Implement exponential backoff
- Check current tier limits
- Reduce request frequency
- Consider request queuing

## Key Takeaways

- ✓ **SDK is recommended** for most use cases over direct HTTP
- ✓ **Always handle errors** with specific exception types
- ✓ **Implement retry logic** for transient failures
- ✓ **Validate parameters** before making requests
- ✓ **Parse responses carefully** - handle JSON extraction
- ✓ **Log requests** in debug mode for troubleshooting
- ✓ **Set timeouts** appropriately for your use case
- ✓ **Monitor costs** by tracking token usage
- ✓ **Use type-safe wrappers** for better code maintainability
- ✓ **Test error scenarios** to ensure robust error handling

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="03"
  label="You can make robust Claude API requests!"
/>

---

Continue to [Chapter 04: Understanding Messages and Conversations](/series/claude-php-developers/chapters/04-messages-conversations) to learn multi-turn conversation management.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 03 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-03)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-03
composer install
cp .env.example .env
# Add your API key to .env
php examples/01-basic-request.php
```
