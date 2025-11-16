---
title: "03: Your First Claude Request in PHP"
description: "Master making Claude API calls with detailed examples using Guzzle HTTP client, the beta official SDK, and the community client. Learn request structure, response parsing, error handling, and best practices."
series: "claude-php-developers"
chapter: 3
order: 3
difficulty: "Beginner"
prerequisites:
  - "PHP 8.4+ installed"
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

Making your first successful API request is a milestone in any integration project. This chapter provides a comprehensive guide to making Claude API calls using raw HTTP requests with Guzzle, the beta official Anthropic PHP SDK, and the battle-tested community client most production teams ship today.

You'll learn the complete anatomy of API requests and responses, how to properly structure your calls, parse responses effectively, handle errors gracefully, and follow best practices for production environments. By the end, you'll be confident making reliable, efficient Claude API calls.

**What You'll Learn:**
- Making API calls with Guzzle HTTP client
- Using the official Anthropic PHP SDK (beta) and understanding its limits
- Installing and calling the community client for production readiness
- Request structure and parameters
- Response parsing and data extraction
- Comprehensive error handling
- Request/response debugging
- Performance optimization

**Estimated Time**: 35-45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 01-02**
- ✓ **PHP 8.4+** installed and working
- ✓ **Composer** for dependency management
- ✓ **Anthropic API key** configured in environment
- ✓ **Basic HTTP/REST API knowledge**

## What You'll Build

By the end of this chapter, you will have created:

- A working PHP application that makes Claude API requests using both the beta SDK and the community client
- A reusable `ClaudeRequestBuilder` class for fluent request construction
- A type-safe `ClaudeResponse` model wrapper with cost estimation
- A `JsonExtractor` service for parsing structured data from responses
- A `RetryableClaudeClient` wrapper with exponential backoff retry logic
- A `DebuggableClaudeClient` with request/response logging
- Direct HTTP integration examples using Guzzle
- Production-ready error handling for all API exception types

You'll have a complete understanding of request structure, response parsing, error handling, and best practices for making reliable Claude API calls in PHP.

## Objectives

By completing this chapter, you will:

- **Understand** the complete structure of Claude API requests and responses
- **Create** working examples using the beta SDK, the community client, and direct HTTP calls
- **Implement** robust error handling with specific exception types
- **Build** reusable service classes for common API patterns
- **Master** response parsing including JSON extraction from markdown
- **Apply** retry logic and exponential backoff for transient failures
- **Optimize** API calls with proper timeout configuration and connection pooling

## Installation

### Installing the Official SDK (Beta)

Anthropic's SDK for PHP is still labeled **beta**. Install it to align with the first-party API surface and to verify breaking changes quickly:

```bash
# Install the official Anthropic PHP SDK
composer require anthropics/anthropic-sdk-php
```

**Verify installation:**

```bash
# Check that the package was installed correctly
composer show anthropics/anthropic-sdk-php
```

You should see package details including version, description, and dependencies along with a `beta` stability flag. Stick with the latest tagged version; nightly builds change frequently.

### Installing the Community Client (Production Default)

Most production teams currently rely on the community-maintained client for richer helpers (streaming ergonomics, tool payload builders, retries, etc.). Install it alongside the official SDK:

```bash
composer require anthropic-php/client
```

The community package exports the same request payloads, so you can swap between clients simply by changing which factory you instantiate.

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
composer require anthropics/anthropic-sdk-php anthropic-php/client vlucas/phpdotenv
mkdir -p src/{Services,Models,Exceptions} examples tests
```

Create `.env` file:

```bash
# .env
ANTHROPIC_API_KEY=sk-ant-your-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=2048
```

::: warning Security Best Practice
Never commit your `.env` file to version control. Add it to `.gitignore`:

```bash
echo ".env" >> .gitignore
```

Always use environment variables or secure secret management in production.
:::

## Making Requests with the SDK

### Basic Request

The simplest possible request demonstrates the core API call pattern:

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
            'content' => 'Hello, Claude! Tell me about PHP 8.4 features.'
        ]
    ]
]);

// Output response
echo $response->content[0]->text . "\n";
```

::: tip Switching Clients
Swap `Anthropic::factory()` with the factory class exported by `anthropic-php/client` if you're using the community client. The payload structure shown here works for both packages.
:::

**How It Works:**

1. **Client Initialization**: `Anthropic::factory()` creates a client builder, `withApiKey()` sets your API key, and `make()` builds the client instance.
2. **Request Structure**: The `messages()->create()` method accepts an array with required parameters (`model`, `max_tokens`, `messages`) and optional parameters.
3. **Response Access**: The response object contains a `content` array where each element has a `text` property containing Claude's response.

Run it:

```bash
# Set your API key as an environment variable
export ANTHROPIC_API_KEY="sk-ant-your-key-here"

# Execute the script
php examples/01-basic-request.php
```

**Expected Result:**

```
PHP 8.4 introduces several exciting features including property hooks, asymmetric visibility, 
new array functions, and improved type system capabilities. Property hooks allow you to 
intercept property access and modification...
```

::: tip Environment Variables
For production applications, use a `.env` file with `vlucas/phpdotenv` instead of exporting environment variables directly. This keeps your API keys secure and out of version control.
:::

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

**Expected Result:**

```
Response:
Laravel service providers are the central place of all Laravel application bootstrapping...

Model used: claude-sonnet-4-20250514
Stop reason: end_turn
Input tokens: 45
Output tokens: 523
```

**Understanding the Parameters:**

- **`model`**: Specifies which Claude model to use (required)
- **`max_tokens`**: Maximum tokens Claude can generate in response (required, 1-16384)
- **`messages`**: Array of conversation messages (required, must start with user message)
- **`system`**: Optional system prompt that sets Claude's behavior and context
- **`temperature`**: Controls randomness (0.0 = deterministic, 1.0 = creative)
- **`top_p`**: Nucleus sampling parameter (0.0-1.0)
- **`top_k`**: Limits token selection pool
- **`stop_sequences`**: Array of strings that stop generation when encountered
- **`metadata`**: Optional tracking data (requires `user_id` if provided)

### Request Builder Pattern

The builder pattern provides a fluent interface for constructing API requests. This approach offers several benefits:

- **Type safety**: Method chaining ensures correct parameter types
- **Readability**: Code reads like natural language
- **Validation**: Built-in validation prevents invalid requests
- **Reusability**: Single builder instance can create multiple requests

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

**How It Works:**

The builder pattern uses method chaining where each method returns `$this`, allowing you to call multiple methods in sequence. The `build()` method validates the configuration and returns a properly formatted array ready for the API call. This pattern is especially useful when building requests dynamically based on user input or application state.

## Understanding Request Structure

### Messages Array

Messages must follow specific rules to ensure valid API requests:

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

**Key Rules:**

1. **Must start with user message**: The first message in the array must have `role => 'user'`
2. **Alternating roles**: Messages must alternate between `user` and `assistant` roles
3. **No consecutive same roles**: Two user messages or two assistant messages cannot be adjacent
4. **Non-empty array**: At least one message is required
5. **Content required**: Each message must have a non-empty `content` string

::: tip Message Validation
Always validate your messages array before sending. The SDK will throw a `ValidationException` if these rules are violated, but validating early prevents unnecessary API calls.
:::

### System Prompts

System prompts set the context for Claude's behavior and are particularly powerful for:

- **Defining roles**: Tell Claude to act as a specific type of expert
- **Setting constraints**: Establish boundaries and guidelines
- **Providing context**: Share background information that applies to all messages
- **Controlling tone**: Specify the style and format of responses

::: info System Prompt Best Practices
- Keep system prompts concise but specific
- Use clear, direct language
- Include examples when helpful
- System prompts apply to the entire conversation, so set them once at the start
:::

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
$codeToReview = <<<'PHP'
function processUser($data) {
    return mysql_query("SELECT * FROM users WHERE id = " . $data['id']);
}
PHP;

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
$classCode = <<<'PHP'
class UserService {
    public function createUser(string $name, string $email): int {
        // Implementation here
    }
}
PHP;

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
$salesData = [
    ['month' => 'January', 'revenue' => 50000],
    ['month' => 'February', 'revenue' => 55000],
    ['month' => 'March', 'revenue' => 60000],
];

$analysisResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1500,
    'system' => 'You are a data analyst. Provide statistical insights and actionable recommendations based on data. Always include confidence levels.',
    'messages' => [
        [
            'role' => 'user',
            'content' => "Analyze this sales data:\n\n" . json_encode($salesData, JSON_PRETTY_PRINT)
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

| Use Case | Temperature | Top-p | Top-k | Why |
|----------|-------------|-------|-------|-----|
| Code Generation | 0.0 - 0.3 | 0.1 | 10 | Low randomness ensures consistent, correct code |
| Data Extraction | 0.0 - 0.2 | 0.1 | 5 | Deterministic output for accurate data parsing |
| Technical Documentation | 0.3 - 0.5 | 0.5 | 20 | Balanced for clarity with some variation |
| General Conversation | 0.7 - 0.9 | 0.9 | 40 | Natural variation for engaging dialogue |
| Creative Writing | 0.9 - 1.0 | 0.95 | 50 | High creativity for diverse outputs |
| Brainstorming | 1.0 | 1.0 | 100 | Maximum diversity for idea generation |

::: tip Temperature Guidelines
- **Start with defaults** (temperature 1.0) and adjust based on your needs
- **Lower temperature** for tasks requiring consistency (code, data extraction)
- **Higher temperature** for creative tasks (writing, brainstorming)
- **Test different values** to find what works best for your specific use case
:::

## Parsing Responses

### Basic Response Structure

Every Claude API response contains structured data that you can access programmatically. Understanding the response structure is crucial for building robust applications.

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
if (empty($response->content)) {
    echo "Warning: Response has no content blocks\n";
} else {
    foreach ($response->content as $index => $block) {
        echo "Block {$index}:\n";
        echo "  Type: {$block->type}\n";                   // "text"
        
        // Handle different content types
        if ($block->type === 'text') {
            echo "  Text: {$block->text}\n";               // The actual response
        } else {
            echo "  Content: " . json_encode($block) . "\n"; // Other content types
        }
    }
}

// Usage statistics
echo "\n=== Usage ===\n";
echo "Input Tokens: {$response->usage->inputTokens}\n";
echo "Output Tokens: {$response->usage->outputTokens}\n";
echo "Total Tokens: " . ($response->usage->inputTokens + $response->usage->outputTokens) . "\n";
```

**Understanding Response Properties:**

- **`id`**: Unique identifier for this message (useful for logging and tracking)
- **`type`**: Always `"message"` for message responses
- **`role`**: Always `"assistant"` for Claude's responses
- **`model`**: The model that generated this response
- **`stop_reason`**: Why generation stopped (`"end_turn"`, `"max_tokens"`, `"stop_sequence"`)
- **`stop_sequence`**: The stop sequence that triggered termination (if applicable)
- **`content`**: Array of content blocks (usually one text block, but can have multiple)
- **`usage`**: Token usage statistics for cost tracking

**Using Response ID for Correlation:**

The response `id` field (format: `msg_01ABC...`) is useful for:
- **Logging**: Include in logs to correlate requests and responses
- **Debugging**: Reference specific API calls when troubleshooting
- **Support**: Share with Anthropic support for issue investigation
- **Auditing**: Track API usage and costs per request

```php
// Example: Logging with request ID
$response = $client->messages()->create([...]);
error_log("Claude API Response [{$response->id}]: {$response->usage->inputTokens} input, {$response->usage->outputTokens} output tokens");
```

**Content Array Safety:**

Always check if the content array is empty before accessing elements:

```php
// Safe content access
if (empty($response->content)) {
    throw new \RuntimeException('Response has no content');
}

$text = $response->content[0]->text ?? '';

// Or handle multiple content blocks
foreach ($response->content as $block) {
    if ($block->type === 'text') {
        echo $block->text;
    }
}
```

::: tip Response Validation
Always check `stop_reason` to ensure the response completed successfully. If `stop_reason` is `"max_tokens"`, the response was truncated and you may need to increase `max_tokens` or request a continuation.
:::

### Response Model Class

Wrapping the SDK response in a custom model class provides several advantages:

- **Type safety**: Explicit types prevent runtime errors
- **Encapsulation**: Hide SDK implementation details
- **Additional methods**: Add helper methods like cost estimation
- **Easier testing**: Mock responses without SDK dependencies
- **Future-proofing**: Adapt to SDK changes without changing application code

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

Claude often returns JSON, but may wrap it in markdown code blocks. This is common when:
- Requesting structured data extraction
- Asking for formatted output
- Using system prompts that specify JSON format

The `JsonExtractor` class handles multiple formats automatically:

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

## Understanding Rate Limits

Before diving into error handling, it's important to understand rate limits - a common source of API errors.

### What Are Rate Limits?

Rate limits control how many API requests you can make within a specific time period. They exist to:
- **Prevent abuse**: Protect the API from being overwhelmed
- **Ensure fairness**: Distribute resources fairly among users
- **Maintain stability**: Keep the service responsive for all users

### Typical Rate Limits

Anthropic enforces rate limits based on your account tier:

- **Free/Trial Tier**: ~50 requests per minute
- **Paid Tier**: Varies by usage and account level (typically 50-100+ requests/minute)
- **Enterprise Tier**: Custom limits based on agreement

Rate limits apply to:
- **Requests per minute**: How many API calls you can make
- **Tokens per minute**: Total token throughput (input + output)
- **Requests per day**: Daily quota limits (for some tiers)

### Rate Limit Headers

When you hit a rate limit, the API returns HTTP 429 with helpful headers:

```
HTTP/1.1 429 Too Many Requests
Retry-After: 60
X-RateLimit-Limit: 50
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1640995200
```

- **`Retry-After`**: Seconds to wait before retrying
- **`X-RateLimit-Limit`**: Your rate limit threshold
- **`X-RateLimit-Remaining`**: Requests remaining in current window
- **`X-RateLimit-Reset`**: Unix timestamp when limit resets

::: tip Rate Limit Best Practices
- **Monitor your usage**: Track requests per minute to stay under limits
- **Implement backoff**: Use exponential backoff when hitting limits
- **Batch requests**: Combine multiple operations when possible
- **Cache responses**: Avoid duplicate requests for same content
- **Upgrade tier**: Consider higher tier for production workloads
:::

## Error Handling

Robust error handling is essential for production applications. The Anthropic SDK provides specific exception types for different error scenarios, allowing you to handle each appropriately.

### Exception Types

The SDK throws several exception types, each representing a different error condition:

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

**Exception Handling Best Practices:**

1. **Catch specific exceptions first**: Handle `RateLimitException` and `AuthenticationException` before generic `Exception`
2. **Log errors appropriately**: Use appropriate log levels (error for failures, warning for retries)
3. **Provide user-friendly messages**: Don't expose internal error details to end users
4. **Implement retry logic**: Automatically retry transient failures (rate limits, server errors)
5. **Fail fast on permanent errors**: Don't retry authentication or validation errors

::: warning Error Handling in Production
Never expose API keys, internal error messages, or stack traces to end users. Always sanitize error messages and log detailed information server-side for debugging.
:::

### Retry Logic with Exponential Backoff

Transient failures (rate limits, server errors) should be retried automatically. Exponential backoff increases wait time between retries, preventing overwhelming the API and improving success rates.

**Why Exponential Backoff?**

- **Rate limits**: Gives the API time to reset rate limit counters
- **Server errors**: Temporary issues often resolve quickly
- **Network issues**: Brief connectivity problems may resolve
- **Prevents cascading failures**: Avoids hammering a struggling service

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

## Understanding the Claude API Structure

Before diving into direct HTTP requests, it's important to understand the Claude API's structure and conventions.

### API Base URL and Endpoint

The Claude API uses a single base URL with versioned endpoints:

- **Base URL**: `https://api.anthropic.com`
- **Messages Endpoint**: `/v1/messages`
- **Full URL**: `https://api.anthropic.com/v1/messages`

The `/v1/` prefix indicates the API version. Anthropic maintains backward compatibility within major versions, so `/v1/` endpoints remain stable. Future major versions (like `/v2/`) would introduce breaking changes.

::: info API Environments
Currently, Anthropic provides a single production API endpoint. There are no separate staging or sandbox environments. Always use `https://api.anthropic.com` for all requests.
:::

### Required HTTP Headers

When making direct HTTP requests, you must include these headers:

```php
$headers = [
    'x-api-key' => 'sk-ant-your-key-here',           // Your API key
    'anthropic-version' => '2023-06-01',             // API version (required)
    'Content-Type' => 'application/json',            // Request body format
];
```

**Understanding the Headers:**

- **`x-api-key`**: Your Anthropic API key (starts with `sk-ant-`)
- **`anthropic-version`**: API version date string. This ensures your code works with the expected API behavior. The SDK handles this automatically, but you must set it manually for direct HTTP requests.
- **`Content-Type`**: Always `application/json` for request bodies

::: tip API Version Header
The `anthropic-version` header tells Anthropic which API version to use. Using `2023-06-01` ensures compatibility with the current API. If Anthropic releases breaking changes in the future, they'll use a new date (e.g., `2024-01-01`), and you can continue using `2023-06-01` until you're ready to migrate.
:::

### HTTP Status Codes

The Claude API uses standard HTTP status codes to indicate request results:

| Status Code | Meaning | When It Occurs | Action |
|-------------|---------|----------------|--------|
| `200` | Success | Request completed successfully | Process response normally |
| `400` | Bad Request | Invalid request parameters | Fix request parameters, don't retry |
| `401` | Unauthorized | Invalid or missing API key | Check API key, don't retry |
| `403` | Forbidden | API key lacks permissions | Check account permissions, don't retry |
| `404` | Not Found | Invalid endpoint or model | Check endpoint/model name, don't retry |
| `422` | Unprocessable Entity | Request valid but cannot be processed | Review request format, don't retry |
| `429` | Too Many Requests | Rate limit exceeded | Implement retry with backoff |
| `500` | Internal Server Error | Anthropic server error | Retry with exponential backoff |
| `502` | Bad Gateway | Network/gateway error | Retry with exponential backoff |
| `503` | Service Unavailable | Service temporarily unavailable | Retry with exponential backoff |

**Status Code Categories:**

- **2xx (Success)**: Request succeeded
- **4xx (Client Error)**: Your request was invalid - don't retry without fixing
- **5xx (Server Error)**: Anthropic's servers had an issue - safe to retry

::: tip Retry Strategy
Only retry on 5xx errors and 429 (rate limit). Never retry 4xx errors (except 429) as they indicate problems with your request that won't be fixed by retrying.
:::

## Making Requests with Guzzle (Direct HTTP)

While the SDK is recommended for most use cases, direct HTTP requests with Guzzle offer more control and flexibility. Use direct HTTP when:

- **Custom HTTP behavior**: Need specific timeout, retry, or middleware behavior
- **SDK limitations**: SDK doesn't support a feature you need
- **Performance**: Want to optimize HTTP client configuration
- **Integration**: Need to integrate with existing Guzzle-based infrastructure

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
    'base_uri' => 'https://api.anthropic.com',  // Claude API base URL
    'timeout' => 30.0,
    'headers' => [
        'x-api-key' => $apiKey,                    // Your API key
        'anthropic-version' => '2023-06-01',       // API version (required!)
        'Content-Type' => 'application/json',       // Request format
    ]
]);

try {
    // POST to /v1/messages endpoint
    // The /v1/ prefix indicates API version 1
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

## Wrap-up

Congratulations! You've completed a comprehensive guide to making Claude API requests in PHP. Here's what you've accomplished:

- ✓ **Made your first API call** using both the beta SDK and the community client
- ✓ **Built reusable components** including request builders, response models, and service wrappers
- ✓ **Implemented robust error handling** with specific exception types and retry logic
- ✓ **Mastered response parsing** including JSON extraction from markdown-formatted responses
- ✓ **Created production-ready patterns** for debugging, logging, and performance optimization
- ✓ **Learned both SDK and HTTP approaches** giving you flexibility for different use cases

### Key Concepts Learned

- **SDK vs HTTP**: The beta SDK mirrors the REST contract; the community client adds ergonomics, while direct HTTP gives you full control
- **Request Structure**: Messages must alternate between user and assistant roles, with optional system prompts
- **Error Handling**: Different exception types require different handling strategies (retry vs fail-fast)
- **Response Parsing**: Claude responses are structured objects with metadata, usage stats, and content blocks
- **Production Patterns**: Retry logic, logging, timeout configuration, and connection pooling are essential

### Next Steps

You now have the foundation to build sophisticated Claude integrations. In the next chapter, you'll learn how to manage multi-turn conversations, maintain context across requests, and implement conversation state management.

## Key Takeaways

- ✓ **Pair the beta SDK with the community client** for most use cases, falling back to direct HTTP when you need total control
- ✓ **Always handle errors** with specific exception types
- ✓ **Implement retry logic** for transient failures
- ✓ **Validate parameters** before making requests
- ✓ **Parse responses carefully** - handle JSON extraction
- ✓ **Log requests** in debug mode for troubleshooting
- ✓ **Set timeouts** appropriately for your use case
- ✓ **Monitor costs** by tracking token usage
- ✓ **Use type-safe wrappers** for better code maintainability
- ✓ **Test error scenarios** to ensure robust error handling

## Further Reading

- [Anthropic Messages API Documentation](https://docs.claude.com/en/api/messages) — Official API reference with all parameters and response formats
- [Anthropic PHP SDK GitHub Repository](https://github.com/anthropics/anthropic-sdk-php) — Source code, examples, and issue tracking
- [Guzzle HTTP Client Documentation](https://docs.guzzlephp.org/) — Complete guide to Guzzle for direct HTTP requests
- [PSR-18 HTTP Client Standard](https://www.php-fig.org/psr/psr-18/) — PHP standard for HTTP client interfaces
- [Chapter 04: Understanding Messages and Conversations](/series/claude-php-developers/chapters/04-messages-conversations) — Learn multi-turn conversation management
- [Chapter 05: Prompt Engineering Basics](/series/claude-php-developers/chapters/05-prompt-engineering-basics) — Master effective prompt design

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
