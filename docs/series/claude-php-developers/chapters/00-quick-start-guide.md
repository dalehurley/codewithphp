---
title: "00: Quick Start Guide"
description: "Get Claude running in PHP in 5 minutes. Practical examples for text generation, code analysis, and data extraction with immediate results."
series: "claude-php-developers"
chapter: 0
order: 0
difficulty: "Expert"
prerequisites:
  - "PHP 8.2+ installed"
  - "Composer installed"
  - "Anthropic API key"
---

![00: Quick Start Guide](/images/claude-php/chapter-00-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 00</span>
</div>

# Chapter 00: Quick Start Guide

## Overview

Want to get started with Claude and PHP right now? This chapter gets you from zero to making your first AI-powered API call in 5 minutes. You'll see practical examples of text generation, code analysis, and data extraction—the three most common use cases for Claude in PHP applications.

By the end of this quick start, you'll have Claude integrated into a PHP script, understand the basic API structure, and have working code you can immediately adapt for your projects.

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.2+** installed (`php --version`)
- ✓ **Composer** installed (`composer --version`)
- ✓ **Anthropic API key** (we'll show you how to get one)
- ✓ Basic command line familiarity

**Estimated Time**: 5-10 minutes

## Step 1: Get Your API Key (~2 min)

1. **Sign up** at [console.anthropic.com](https://console.anthropic.com)
2. **Add payment method** (required for API access)
3. **Generate API key** from Settings → API Keys
4. **Copy the key** (starts with `sk-ant-`)

::: warning
Keep your API key secret! Never commit it to version control.
:::

## Step 2: Install the PHP SDK (~1 min)

Create a new project directory and install the Anthropic PHP SDK:

```bash
# Create project directory
mkdir claude-quickstart && cd claude-quickstart

# Initialize composer
composer init --no-interaction

# Install Anthropic SDK
composer require anthropic-ai/sdk
```

## Step 3: Your First Claude Request (~2 min)

Create a file called `quickstart.php`:

```php
<?php
# filename: quickstart.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;

// Initialize Claude client
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Make your first API call
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Explain what PHP generators are in one paragraph.'
        ]
    ]
]);

// Output the response
echo $response->content[0]->text;
```

Run it with your API key:

```bash
# Set your API key (replace with your actual key)
export ANTHROPIC_API_KEY="sk-ant-your-key-here"

# Run the script
php quickstart.php
```

**Expected output:** Claude will explain PHP generators in a clear, concise paragraph.

## Common Use Cases

Now that you have the basics working, here are three common patterns you'll use in real applications:

### Use Case 1: Text Generation

Generate content, summaries, or creative text:

```php
<?php
# filename: examples/01-text-generation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

function generateProductDescription(string $productName, array $features): string
{
    global $client;

    $featuresList = implode("\n", array_map(fn($f) => "- $f", $features));

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'messages' => [[
            'role' => 'user',
            'content' => "Write a compelling product description for:\n\nProduct: {$productName}\n\nFeatures:\n{$featuresList}\n\nMake it engaging and benefit-focused."
        ]]
    ]);

    return $response->content[0]->text;
}

// Example usage
$description = generateProductDescription(
    productName: 'Laravel Cloud',
    features: [
        'One-click deployment',
        'Auto-scaling infrastructure',
        'Built-in monitoring',
        '99.99% uptime SLA'
    ]
);

echo "Generated Description:\n\n{$description}\n";
```

### Use Case 2: Code Analysis

Analyze, review, or explain code:

```php
<?php
# filename: examples/02-code-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

function analyzeCode(string $code): string
{
    global $client;

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 2000,
        'messages' => [[
            'role' => 'user',
            'content' => "Analyze this PHP code for potential issues, security vulnerabilities, and suggest improvements:\n\n```php\n{$code}\n```"
        ]]
    ]);

    return $response->content[0]->text;
}

// Example: Analyze a potentially problematic function
$codeToAnalyze = <<<'PHP'
function getUserData($userId) {
    $db = new PDO('mysql:host=localhost;dbname=app', 'root', '');
    $result = $db->query("SELECT * FROM users WHERE id = " . $userId);
    return $result->fetch();
}
PHP;

echo "Code Analysis:\n\n";
echo analyzeCode($codeToAnalyze);
```

### Use Case 3: Data Extraction

Extract structured data from unstructured text:

```php
<?php
# filename: examples/03-data-extraction.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

function extractContactInfo(string $text): array
{
    global $client;

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => "Extract contact information from this text and return as JSON with fields: name, email, phone, company. If a field is not found, use null.\n\nText: {$text}\n\nReturn only valid JSON, no explanation."
        ]]
    ]);

    $jsonText = $response->content[0]->text;

    // Extract JSON from response (may be wrapped in markdown code blocks)
    if (preg_match('/```json\s*(\{.*?\})\s*```/s', $jsonText, $matches)) {
        $jsonText = $matches[1];
    } elseif (preg_match('/(\{.*?\})/s', $jsonText, $matches)) {
        $jsonText = $matches[1];
    }

    return json_decode($jsonText, true) ?? [];
}

// Example: Extract from business card text
$businessCard = <<<TEXT
John Smith
Senior PHP Developer
Acme Corporation
Email: john.smith@acme.com
Mobile: +1 (555) 123-4567
TEXT;

$contact = extractContactInfo($businessCard);

echo "Extracted Contact Information:\n";
print_r($contact);
```

## Understanding the Response Structure

Every Claude API response follows this structure:

```php
$response = $client->messages()->create([...]);

// Access the text content
$text = $response->content[0]->text;

// Check usage (for cost tracking)
$inputTokens = $response->usage->inputTokens;
$outputTokens = $response->usage->outputTokens;

// Get the model used
$model = $response->model;

// Response metadata
$id = $response->id;
$role = $response->role; // Always 'assistant'
```

## Model Selection Quick Reference

Choose the right model for your use case:

| Model | Speed | Cost | Best For |
|-------|-------|------|----------|
| **claude-haiku-4-20250514** | Fastest | Lowest | Simple tasks, high volume |
| **claude-sonnet-4-20250514** | Balanced | Medium | Most use cases, best value |
| **claude-opus-4-20250514** | Slowest | Highest | Complex reasoning, critical tasks |

For most PHP applications, start with **Sonnet**—it provides excellent quality at reasonable cost.

## Error Handling Basics

Always handle errors in production:

```php
<?php
# filename: examples/04-error-handling.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Exceptions\ErrorException;
use Anthropic\Exceptions\RateLimitException;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => 'Hello, Claude!'
        ]]
    ]);

    echo $response->content[0]->text;

} catch (RateLimitException $e) {
    // Handle rate limiting
    echo "Rate limit exceeded. Please try again later.\n";
    error_log("Rate limit: " . $e->getMessage());

} catch (ErrorException $e) {
    // Handle API errors
    echo "API error occurred: " . $e->getMessage() . "\n";
    error_log("Claude API error: " . $e->getMessage());

} catch (\Exception $e) {
    // Handle unexpected errors
    echo "Unexpected error: " . $e->getMessage() . "\n";
    error_log("Unexpected error: " . $e->getMessage());
}
```

## Cost Tracking

Monitor your API usage and costs:

```php
<?php
# filename: examples/05-cost-tracking.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Explain dependency injection in PHP.'
    ]]
]);

// Calculate cost
$inputTokens = $response->usage->inputTokens;
$outputTokens = $response->usage->outputTokens;

// Sonnet pricing (as of 2025)
$inputCostPer1M = 3.00;  // $3 per million input tokens
$outputCostPer1M = 15.00; // $15 per million output tokens

$inputCost = ($inputTokens / 1_000_000) * $inputCostPer1M;
$outputCost = ($outputTokens / 1_000_000) * $outputCostPer1M;
$totalCost = $inputCost + $outputCost;

echo "Response:\n{$response->content[0]->text}\n\n";
echo "--- Usage Stats ---\n";
echo "Input tokens: {$inputTokens}\n";
echo "Output tokens: {$outputTokens}\n";
echo "Estimated cost: $" . number_format($totalCost, 6) . "\n";
```

## Environment Setup Best Practices

For production applications, use environment variables:

```bash
# .env file
ANTHROPIC_API_KEY=sk-ant-your-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=4096
ANTHROPIC_TEMPERATURE=1.0
```

Load them with vlucas/phpdotenv:

```bash
composer require vlucas/phpdotenv
```

```php
<?php
# filename: examples/06-environment-config.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create client with environment config
$client = Anthropic::factory()
    ->withApiKey($_ENV['ANTHROPIC_API_KEY'])
    ->make();

// Use environment-based configuration
$response = $client->messages()->create([
    'model' => $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-20250514',
    'max_tokens' => (int)($_ENV['ANTHROPIC_MAX_TOKENS'] ?? 1024),
    'temperature' => (float)($_ENV['ANTHROPIC_TEMPERATURE'] ?? 1.0),
    'messages' => [[
        'role' => 'user',
        'content' => 'What is Laravel Octane?'
    ]]
]);

echo $response->content[0]->text;
```

## Quick Reference: Common Parameters

```php
$response = $client->messages()->create([
    // Required
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Your prompt here']
    ],

    // Optional
    'system' => 'You are a helpful PHP expert.',  // System prompt
    'temperature' => 1.0,                          // 0.0-1.0, default 1.0
    'top_p' => 0.9,                                // Nucleus sampling
    'top_k' => 40,                                 // Top-k sampling
    'stop_sequences' => ['END'],                   // Stop generation
]);
```

## Next Steps

Now that you have Claude working in PHP, here's what to explore next:

1. **[Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction-to-claude-api)** — Understand models, capabilities, and architecture
2. **[Chapter 03: Your First Claude Request](/series/claude-php-developers/chapters/03-first-claude-request)** — Deep dive into request/response structure
3. **[Chapter 05: Prompt Engineering Basics](/series/claude-php-developers/chapters/05-prompt-engineering-basics)** — Learn to write effective prompts
4. **[Chapter 11: Tool Use Fundamentals](/series/claude-php-developers/chapters/11-tool-use-fundamentals)** — Extend Claude with function calling

## Exercises

Before moving on, try these exercises:

### Exercise 1: Custom Text Generator

Build a function that generates blog post outlines:

```php
function generateBlogOutline(string $topic, int $sections = 5): string
{
    // Your implementation here
    // Should return a structured outline with intro, sections, conclusion
}
```

### Exercise 2: Code Formatter

Create a function that takes messy code and returns formatted, documented code:

```php
function formatAndDocumentCode(string $code, string $language = 'php'): string
{
    // Your implementation here
    // Should return formatted code with PHPDoc comments
}
```

### Exercise 3: JSON Validator

Build a data extractor that always returns valid JSON:

```php
function extractToJSON(string $text, array $schema): array
{
    // Your implementation here
    // Should validate against schema and return structured data
}
```

<details>
<summary>Solution Hints</summary>

For Exercise 1, use a system prompt that defines Claude as a blog outline expert. For Exercise 2, include code formatting rules in the prompt. For Exercise 3, specify the JSON schema in the prompt and use `json_decode()` with error handling.

</details>

## Troubleshooting

**API key not working?**
- Ensure you've added a payment method to your Anthropic account
- Check the key starts with `sk-ant-`
- Verify the environment variable is set correctly

**Rate limit errors?**
- You've exceeded your API rate limit
- Implement exponential backoff retry logic (see Chapter 10)
- Consider upgrading your API tier

**Empty or unexpected responses?**
- Check your `max_tokens` value (might be too low)
- Review your prompt for clarity and specificity
- Try a different model (Sonnet vs Haiku)

**JSON parsing fails?**
- Claude may wrap JSON in markdown code blocks
- Use regex to extract JSON from response
- Add "Return only valid JSON, no explanation" to your prompt

## Key Takeaways

- ✓ Claude API is accessible via the official PHP SDK
- ✓ Basic usage requires: model, max_tokens, and messages array
- ✓ Responses contain text content and usage statistics
- ✓ Always implement error handling for production
- ✓ Track token usage to monitor costs
- ✓ Choose the right model for your use case (Sonnet for most tasks)

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="00"
  label="You've made your first Claude API call in PHP!"
/>

---

Continue to [Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction-to-claude-api) for comprehensive understanding.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 00 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-00)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-00
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php quickstart.php
```
