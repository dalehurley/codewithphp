---
title: "00: Quick Start Guide"
description: "Get Claude running in PHP in 5 minutes. Practical examples for text generation, code analysis, and data extraction with immediate results."
series: "claude-php-developers"
chapter: 0
order: 0
difficulty: "Beginner"
prerequisites: []
---

![00: Quick Start Guide](/images/claude-php/chapter-00-hero-full.webp)

# Chapter 00: Quick Start Guide

**Got 5 minutes?** This guide gets you from zero to making your first Claude API call in PHP. You'll see practical examples of text generation, code analysis, and data extraction—the three most common use cases for Claude in PHP applications.

::: info
This is a reference guide, not a traditional tutorial. For step-by-step learning and deeper understanding, start with [Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction-to-claude-api).
:::

## Prerequisites

**No prerequisites required** - dive right in! But you'll need:

- **PHP 8.4+** installed (`php --version`)
- **Composer** installed (`composer --version`)
- **Anthropic API key** (we'll show you how to get one below)

**Estimated Time**: 5-10 minutes

## Quick Setup

### Get Your API Key

1. **Sign up** at [console.anthropic.com](https://console.anthropic.com)
2. **Add payment method** (required for API access)
3. **Generate API key** from Settings → API Keys
4. **Copy the key** (starts with `sk-ant-`)

::: warning
Keep your API key secret! Never commit it to version control.
:::

### Install the PHP SDK

Create a new project directory and install the Anthropic PHP SDK:

**Unix/Mac/Linux:**
```bash
# Create project directory
mkdir claude-quickstart && cd claude-quickstart

# Initialize composer
composer init --no-interaction

# Install Anthropic SDK
composer require claude-php/claude-php-sdk
```

**Windows (PowerShell):**
```powershell
# Create project directory
New-Item -ItemType Directory -Path claude-quickstart
Set-Location claude-quickstart

# Initialize composer
composer init --no-interaction

# Install Anthropic SDK
composer require claude-php/claude-php-sdk
```

### Your First Claude Request

Create a file called `quickstart.php`:

```php
<?php
# filename: quickstart.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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
foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'];
    }
}
```

Run it with your API key:

**Unix/Mac/Linux:**
```bash
# Set your API key (replace with your actual key)
export ANTHROPIC_API_KEY="sk-ant-your-key-here"

# Run the script
php quickstart.php
```

**Windows (PowerShell):**
```powershell
# Set your API key (replace with your actual key)
$env:ANTHROPIC_API_KEY="sk-ant-your-key-here"

# Run the script
php quickstart.php
```

**Expected output:** Claude will explain PHP generators in a clear, concise paragraph.

### Verify Your Setup

Test that everything works:

```bash
# Verify PHP version (should be 8.4+)
php --version

# Verify Composer is installed
composer --version

# Verify API key is set
echo $ANTHROPIC_API_KEY | head -c 20  # Shows first 20 chars (Unix/Mac)
# Or on Windows PowerShell:
# $env:ANTHROPIC_API_KEY.Substring(0, 20)
```

If all checks pass, you're ready to go!

## 🚦 Which Use Case Do You Need?

```
Need Claude in PHP?
├─ Generate text content?
│  └─ → Use Case 1: Text Generation
├─ Analyze or review code?
│  └─ → Use Case 2: Code Analysis
├─ Extract structured data?
│  └─ → Use Case 3: Data Extraction
├─ Need specialized AI assistant?
│  └─ → Use System Prompts
├─ Need real-time responses?
│  └─ → Use Streaming
└─ Not sure? → Start with Use Case 1
```

## 🎯 "I Need To..."

### Generate Text Content

Need to generate product descriptions, summaries, or creative text? Here's the pattern:

**→ [Full Guide: Text Generation](/series/claude-php-developers/chapters/05-prompt-engineering-basics)**

```php
<?php
# filename: examples/01-text-generation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

function generateProductDescription(Anthropic $client, string $productName, array $features): string
{
    $featuresList = implode("\n", array_map(fn($f) => "- $f", $features));

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'messages' => [[
            'role' => 'user',
            'content' => "Write a compelling product description for:\n\nProduct: {$productName}\n\nFeatures:\n{$featuresList}\n\nMake it engaging and benefit-focused."
        ]]
    ]);

    return $response->content[0]['text'];
}

// Example usage
$description = generateProductDescription(
    client: $client,
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

### Analyze Code

Need to review code for security issues, bugs, or improvements?

**→ [Full Guide: Code Analysis](/series/claude-php-developers/chapters/06-code-analysis-and-review)**

```php
<?php
# filename: examples/02-code-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

function analyzeCode(Anthropic $client, string $code): string
{
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 2000,
        'messages' => [[
            'role' => 'user',
            'content' => "Analyze this PHP code for potential issues, security vulnerabilities, and suggest improvements:\n\n```php\n{$code}\n```"
        ]]
    ]);

    return $response->content[0]['text'];
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
echo analyzeCode($client, $codeToAnalyze);
```

### Extract Structured Data

Need to extract contact info, product details, or other structured data from text?

**→ [Full Guide: Data Extraction](/series/claude-php-developers/chapters/07-structured-data-extraction)**

```php
<?php
# filename: examples/03-data-extraction.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

function extractContactInfo(Anthropic $client, string $text): array
{
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => "Extract contact information from this text and return as JSON with fields: name, email, phone, company. If a field is not found, use null.\n\nText: {$text}\n\nReturn only valid JSON, no explanation."
        ]]
    ]);

    $jsonText = $response->content[0]['text'];

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

$contact = extractContactInfo($client, $businessCard);

echo "Extracted Contact Information:\n";
print_r($contact);
```

### Use System Prompts

Define Claude's role and expertise for specialized responses:

**→ [Full Guide: System Prompts](/series/claude-php-developers/chapters/07-system-prompts-roles)**

```php
<?php
# filename: examples/04-system-prompt.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// System prompt defines Claude's role and expertise
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'system' => 'You are a senior PHP code reviewer. Analyze code for type safety, security issues, and best practices. Always suggest improvements using PHP 8.4+ features.',
    'messages' => [[
        'role' => 'user',
        'content' => 'Review this function: function getUser($id) { return $db->query("SELECT * FROM users WHERE id = " . $id); }'
    ]]
]);

echo $response->content[0]['text'];
```

### Stream Responses

Get real-time responses for better UX:

**→ [Full Guide: Streaming](/series/claude-php-developers/chapters/06-streaming-responses)**

```php
<?php
# filename: examples/05-streaming.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Stream responses for real-time output
$stream = $client->messages()->createStreamed([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Explain PHP generators in detail.'
    ]]
]);

foreach ($stream as $event) {
    if (isset($event->type) && $event->type === 'content_block_delta') {
        echo $event->delta->text;
        flush(); // Send output immediately
    }
}
```

## Quick Reference: Response Structure

Every Claude API response follows this structure:

```php
$response = $client->messages()->create([...]);

// Access the text content (most common)
$text = $response->content[0]['text'];

// Handle multiple content blocks (if any)
foreach ($response->content as $block) {
    if ($block->type === 'text') {
        echo $block->text;
    }
}

// Check usage (for cost tracking)
$inputTokens = $response->usage->input_tokens;
$outputTokens = $response->usage->output_tokens;

// Get the model used
$model = $response->model;

// Response metadata
$id = $response->id;
$role = $response->role; // Always 'assistant'
$stopReason = $response->stopReason; // 'end_turn', 'max_tokens', etc.
```

## 🚦 Model Selection Guide

Choose the right model for your use case:

| Model | Speed | Cost | Best For |
|-------|-------|------|----------|
| **claude-haiku-4-20250514** | Fastest | Lowest | Simple tasks, high volume |
| **claude-sonnet-4-20250514** | Balanced | Medium | Most use cases, best value |
| **claude-opus-4-20250514** | Slowest | Highest | Complex reasoning, critical tasks |

For most PHP applications, start with **Sonnet**—it provides excellent quality at reasonable cost.

**→ [Full Guide: Model Selection](/series/claude-php-developers/chapters/02-model-selection-and-capabilities)**

## 💡 Quick Wins

### Error Handling

Always handle errors in production:

```php
<?php
# filename: examples/04-error-handling.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Anthropic\Exceptions\ErrorException;
use Anthropic\Exceptions\RateLimitException;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => 'Hello, Claude!'
        ]]
    ]);

    echo $response->content[0]['text'];

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

**→ [Full Guide: Error Handling](/series/claude-php-developers/chapters/10-error-handling-and-retries)**

### Cost Tracking

Monitor your API usage and costs:

```php
<?php
# filename: examples/05-cost-tracking.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Explain dependency injection in PHP.'
    ]]
]);

// Calculate cost
$inputTokens = $response->usage->input_tokens;
$outputTokens = $response->usage->output_tokens;

// Sonnet pricing (as of 2025)
$inputCostPer1M = 3.00;  // $3 per million input tokens
$outputCostPer1M = 15.00; // $15 per million output tokens

$inputCost = ($inputTokens / 1_000_000) * $inputCostPer1M;
$outputCost = ($outputTokens / 1_000_000) * $outputCostPer1M;
$totalCost = $inputCost + $outputCost;

echo "Response:\n{$response->content[0]['text']}\n\n";
echo "--- Usage Stats ---\n";
echo "Input tokens: {$inputTokens}\n";
echo "Output tokens: {$outputTokens}\n";
echo "Estimated cost: $" . number_format($totalCost, 6) . "\n";
```

**→ [Full Guide: Cost Management](/series/claude-php-developers/chapters/09-cost-optimization)**

### Environment Configuration

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

use ClaudePhp\ClaudePhp;
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

echo $response->content[0]['text'];
```

**→ [Full Guide: Production Setup](/series/claude-php-developers/chapters/08-production-best-practices)**

## 📖 Quick Reference: Common Parameters

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

## 📖 Learning Paths

Now that you have Claude working in PHP, here's what to explore next:

**If you have 30 minutes:**
1. Read this guide
2. [Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction-to-claude-api) — Understand models and capabilities
3. [Chapter 05: Prompt Engineering Basics](/series/claude-php-developers/chapters/05-prompt-engineering-basics) — Write effective prompts

**For deep learning:**
- Start with [Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction-to-claude-api) for comprehensive understanding
- [Chapter 03: Your First Claude Request](/series/claude-php-developers/chapters/03-first-claude-request) — Deep dive into request/response structure
- [Chapter 11: Tool Use Fundamentals](/series/claude-php-developers/chapters/11-tool-use-fundamentals) — Extend Claude with function calling

## ❓ FAQs

**Q: API key not working?**
- Ensure you've added a payment method to your Anthropic account
- Check the key starts with `sk-ant-`
- Verify the environment variable is set correctly

**Q: Rate limit errors?**
- You've exceeded your API rate limit
- Implement exponential backoff retry logic → [Chapter 10: Error Handling](/series/claude-php-developers/chapters/10-error-handling-and-retries)
- Consider upgrading your API tier

**Q: Empty or unexpected responses?**
- Check your `max_tokens` value (might be too low)
- Review your prompt for clarity and specificity → [Chapter 05: Prompt Engineering](/series/claude-php-developers/chapters/05-prompt-engineering-basics)
- Try a different model (Sonnet vs Haiku)

**Q: JSON parsing fails?**
- Claude may wrap JSON in markdown code blocks
- Use regex to extract JSON from response (see data extraction example above)
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
  label="I've reviewed the Quick Start Guide and made my first Claude API call!"
/>

---

<div class="series-cta">
  <h2>Ready for More?</h2>
  <p>Continue with the full series for comprehensive understanding.</p>
  <a href="/series/claude-php-developers/chapters/01-introduction-to-claude-api" class="cta-button">Start Course →</a>
</div>

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
