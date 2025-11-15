---
title: "01: Introduction to Claude API"
description: "Master Claude's capabilities, model variants (Opus, Sonnet, Haiku), pricing structure, Messages API architecture, and how conversations work with practical PHP examples."
series: "claude-php-developers"
chapter: 1
order: 1
difficulty: "Beginner"
prerequisites:
  - "PHP 8.2+ installed"
  - "Basic understanding of REST APIs"
  - "Completion of Chapter 00"
---

![01: Introduction to Claude API](/images/claude-php/chapter-01-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 01</span>
</div>

# Chapter 01: Introduction to Claude API

## Overview

Claude is Anthropic's family of large language models (LLMs) designed to be helpful, harmless, and honest. This chapter provides a comprehensive introduction to Claude's capabilities, architecture, and how to effectively integrate it into your PHP applications.

You'll learn about the three model variants (Opus, Sonnet, and Haiku), understand the Messages API structure that powers all Claude interactions, explore pricing and cost optimization strategies, and gain insight into how Claude processes conversations. By the end, you'll have a solid foundation for making informed decisions about which Claude model to use for your specific use cases.

**What You'll Learn:**
- Claude's core capabilities and strengths
- Differences between Opus, Sonnet, and Haiku models
- Messages API architecture and design
- Pricing structure and cost optimization
- How conversations and context work
- Best practices for PHP integration

**Estimated Time**: 30-45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 00** (Quick Start Guide)
- ✓ **PHP 8.2+** installed and working
- ✓ **Anthropic API key** configured
- ✓ **Basic REST API knowledge**

## What is Claude?

Claude is a family of large language models developed by Anthropic. Unlike traditional software that follows explicit instructions, Claude uses advanced machine learning to understand context, generate human-like text, analyze code, solve problems, and interact naturally with users.

### Core Capabilities

Claude excels at:

**1. Natural Language Understanding**
- Complex text comprehension
- Nuanced context interpretation
- Multi-language support
- Sentiment and intent analysis

**2. Content Generation**
- Technical documentation
- Creative writing
- Marketing copy
- Email drafts and responses

**3. Code Analysis and Generation**
- Code review and debugging
- Documentation generation
- Refactoring suggestions
- Multi-language code generation

**4. Data Processing**
- Information extraction
- Structured data conversion
- Summarization
- Classification and categorization

**5. Problem Solving**
- Logical reasoning
- Mathematical calculations
- Strategic planning
- Decision support

### Key Strengths

**Extended Context Window**
Claude supports up to 200,000 tokens (approximately 150,000 words) in a single conversation. This enables:
- Processing entire codebases
- Analyzing long documents
- Maintaining extensive conversation history
- Working with large datasets

**Accurate Instruction Following**
Claude reliably follows complex, multi-step instructions with high accuracy, making it ideal for:
- Structured data extraction
- Multi-stage workflows
- Consistent formatting
- Rule-based processing

**Reduced Hallucination**
Claude is trained to acknowledge uncertainty and avoid making up information when it doesn't know the answer.

**Constitutional AI Training**
Claude is trained using Constitutional AI, making it:
- More helpful and harmless
- Better at refusing inappropriate requests
- More aligned with user intentions
- Safer for production deployments

## Claude Model Variants

Anthropic offers three model variants, each optimized for different use cases:

### Claude Opus 4

**Overview**: The most intelligent and capable model in the Claude family.

**Specifications:**
- Model ID: `claude-opus-4-20250514`
- Context Window: 200,000 tokens
- Max Output: 16,384 tokens
- Training Data: Up to December 2024

**Best For:**
- Complex reasoning tasks
- Advanced code generation
- Research and analysis
- Creative projects requiring nuance
- Tasks where accuracy is critical

**Performance Characteristics:**
- Highest quality outputs
- Best at following complex instructions
- Superior at handling ambiguity
- Slowest response times
- Highest cost per token

**Example Use Cases:**
```php
// Complex architectural decisions
$response = $client->messages()->create([
    'model' => 'claude-opus-4-20250514',
    'max_tokens' => 4096,
    'messages' => [[
        'role' => 'user',
        'content' => 'Design a microservices architecture for a high-traffic e-commerce platform handling 1M+ daily users. Include service boundaries, data flow, caching strategy, and failure handling.'
    ]]
]);

// Advanced code refactoring
$response = $client->messages()->create([
    'model' => 'claude-opus-4-20250514',
    'max_tokens' => 8192,
    'messages' => [[
        'role' => 'user',
        'content' => "Refactor this legacy PHP codebase to modern Laravel with: dependency injection, service containers, event-driven architecture, and comprehensive tests.\n\n{$legacyCode}"
    ]]
]);
```

### Claude Sonnet 4

**Overview**: The balanced model offering excellent performance at reasonable cost.

**Specifications:**
- Model ID: `claude-sonnet-4-20250514`
- Context Window: 200,000 tokens
- Max Output: 16,384 tokens
- Training Data: Up to December 2024

**Best For:**
- General-purpose applications
- Most production workloads
- Balanced quality and speed needs
- Cost-effective scaling

**Performance Characteristics:**
- Excellent quality-to-cost ratio
- Fast response times
- Reliable and consistent
- Recommended starting point

**Example Use Cases:**
```php
// API documentation generation
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [[
        'role' => 'user',
        'content' => "Generate OpenAPI 3.0 documentation for this Laravel controller:\n\n{$controllerCode}"
    ]]
]);

// Code review and suggestions
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 3000,
    'messages' => [[
        'role' => 'user',
        'content' => "Review this pull request for: security issues, performance problems, code style, and best practices.\n\n{$pullRequestDiff}"
    ]]
]);
```

### Claude Haiku 4

**Overview**: The fastest and most cost-effective model for high-volume tasks.

**Specifications:**
- Model ID: `claude-haiku-4-20250514`
- Context Window: 200,000 tokens
- Max Output: 16,384 tokens
- Training Data: Up to December 2024

**Best For:**
- High-volume processing
- Simple classification tasks
- Quick responses needed
- Cost-sensitive applications

**Performance Characteristics:**
- Fastest response times
- Lowest cost per token
- Good for straightforward tasks
- May lack nuance for complex queries

**Example Use Cases:**
```php
// Email classification
$response = $client->messages()->create([
    'model' => 'claude-haiku-4-20250514',
    'max_tokens' => 100,
    'messages' => [[
        'role' => 'user',
        'content' => "Classify this email as: spam, sales, support, or general. Return only the category.\n\nEmail: {$emailContent}"
    ]]
]);

// Simple data extraction
$response = $client->messages()->create([
    'model' => 'claude-haiku-4-20250514',
    'max_tokens' => 256,
    'messages' => [[
        'role' => 'user',
        'content' => "Extract name, email, and phone from:\n\n{$text}\n\nReturn as JSON."
    ]]
]);
```

### Model Comparison Table

| Feature | Opus 4 | Sonnet 4 | Haiku 4 |
|---------|--------|----------|---------|
| **Intelligence** | Highest | High | Good |
| **Speed** | Slower | Fast | Fastest |
| **Cost** | Highest | Medium | Lowest |
| **Use Case** | Complex tasks | General purpose | High volume |
| **Best For** | Quality | Balance | Speed |
| **Context** | 200K tokens | 200K tokens | 200K tokens |
| **Max Output** | 16,384 tokens | 16,384 tokens | 16,384 tokens |

### Choosing the Right Model

Use this decision tree:

```php
<?php
# filename: examples/model-selector.php
declare(strict_types=1);

class ClaudeModelSelector
{
    public static function selectModel(
        string $taskComplexity,
        bool $volumeHigh,
        bool $costSensitive,
        bool $speedCritical
    ): string {
        // High volume, simple tasks → Haiku
        if ($volumeHigh && $taskComplexity === 'simple') {
            return 'claude-haiku-4-20250514';
        }

        // Cost sensitive and task is not complex → Haiku
        if ($costSensitive && $taskComplexity !== 'complex') {
            return 'claude-haiku-4-20250514';
        }

        // Speed critical and task is not complex → Haiku or Sonnet
        if ($speedCritical) {
            return $taskComplexity === 'simple'
                ? 'claude-haiku-4-20250514'
                : 'claude-sonnet-4-20250514';
        }

        // Complex reasoning required → Opus
        if ($taskComplexity === 'complex') {
            return 'claude-opus-4-20250514';
        }

        // Default: Sonnet (best balance)
        return 'claude-sonnet-4-20250514';
    }
}

// Usage examples
echo "Email classification: " .
    ClaudeModelSelector::selectModel('simple', true, true, true) . "\n";
// Output: claude-haiku-4-20250514

echo "Code review: " .
    ClaudeModelSelector::selectModel('moderate', false, false, false) . "\n";
// Output: claude-sonnet-4-20250514

echo "Architecture design: " .
    ClaudeModelSelector::selectModel('complex', false, false, false) . "\n";
// Output: claude-opus-4-20250514
```

## Messages API Architecture

The Messages API is the primary interface for interacting with Claude. Understanding its architecture is crucial for effective integration.

### API Structure Overview

Every Claude interaction follows this structure:

```
Request → Messages API → Claude Model → Response
```

### Request Anatomy

```php
<?php
# Complete request structure with all options
$response = $client->messages()->create([
    // === REQUIRED PARAMETERS ===

    // Model identifier
    'model' => 'claude-sonnet-4-20250514',

    // Maximum tokens to generate (must be specified)
    'max_tokens' => 1024,

    // Conversation messages
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Your prompt here'
        ]
    ],

    // === OPTIONAL PARAMETERS ===

    // System prompt (instructions for Claude's behavior)
    'system' => 'You are a PHP expert specializing in Laravel.',

    // Temperature: 0.0 (focused) to 1.0 (creative)
    'temperature' => 1.0,

    // Top-p sampling (nucleus sampling)
    'top_p' => 0.9,

    // Top-k sampling
    'top_k' => 40,

    // Stop sequences (halt generation when encountered)
    'stop_sequences' => ['</answer>', 'END'],

    // Metadata for tracking
    'metadata' => [
        'user_id' => 'user-123'
    ],

    // Streaming (receive response incrementally)
    'stream' => false,
]);
```

### Response Anatomy

```php
<?php
# Complete response structure
$response = $client->messages()->create([...]);

// Response object structure:
// {
//   "id": "msg_01XYZ...",
//   "type": "message",
//   "role": "assistant",
//   "content": [
//     {
//       "type": "text",
//       "text": "The actual response text..."
//     }
//   ],
//   "model": "claude-sonnet-4-20250514",
//   "stop_reason": "end_turn",
//   "stop_sequence": null,
//   "usage": {
//     "input_tokens": 45,
//     "output_tokens": 128
//   }
// }

// Accessing response data
$messageId = $response->id;                    // Unique message ID
$assistantRole = $response->role;              // Always "assistant"
$responseText = $response->content[0]->text;   // The generated text
$modelUsed = $response->model;                 // Model that generated response
$stopReason = $response->stop_reason;          // Why generation stopped
$inputTokens = $response->usage->inputTokens;  // Tokens in request
$outputTokens = $response->usage->outputTokens; // Tokens in response
```

### Complete Example with Response Handling

```php
<?php
# filename: examples/complete-api-usage.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class ClaudeResponseHandler
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function processRequest(
        string $prompt,
        string $model = 'claude-sonnet-4-20250514',
        ?string $systemPrompt = null,
        int $maxTokens = 2048
    ): array {
        $startTime = microtime(true);

        $requestParams = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        if ($systemPrompt) {
            $requestParams['system'] = $systemPrompt;
        }

        $response = $this->client->messages()->create($requestParams);

        $duration = microtime(true) - $startTime;

        return [
            'success' => true,
            'message_id' => $response->id,
            'text' => $response->content[0]->text,
            'model' => $response->model,
            'stop_reason' => $response->stop_reason,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
                'total_tokens' => $response->usage->inputTokens + $response->usage->outputTokens,
            ],
            'performance' => [
                'duration_seconds' => round($duration, 3),
                'tokens_per_second' => round($response->usage->outputTokens / $duration, 2),
            ],
            'cost' => $this->calculateCost($response, $model),
        ];
    }

    private function calculateCost($response, string $model): array
    {
        $pricing = $this->getPricing($model);

        $inputCost = ($response->usage->inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($response->usage->outputTokens / 1_000_000) * $pricing['output'];

        return [
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $inputCost + $outputCost,
            'formatted' => '$' . number_format($inputCost + $outputCost, 6),
        ];
    }

    private function getPricing(string $model): array
    {
        return match($model) {
            'claude-opus-4-20250514' => [
                'input' => 15.00,
                'output' => 75.00,
            ],
            'claude-sonnet-4-20250514' => [
                'input' => 3.00,
                'output' => 15.00,
            ],
            'claude-haiku-4-20250514' => [
                'input' => 0.25,
                'output' => 1.25,
            ],
            default => ['input' => 0, 'output' => 0],
        };
    }
}

// Usage
$handler = new ClaudeResponseHandler($client);

$result = $handler->processRequest(
    prompt: 'Explain Laravel service containers in 2 paragraphs.',
    model: 'claude-sonnet-4-20250514',
    systemPrompt: 'You are a Laravel expert. Be concise and accurate.',
    maxTokens: 1024
);

echo "Response:\n{$result['text']}\n\n";
echo "--- Analytics ---\n";
echo "Message ID: {$result['message_id']}\n";
echo "Model: {$result['model']}\n";
echo "Input tokens: {$result['usage']['input_tokens']}\n";
echo "Output tokens: {$result['usage']['output_tokens']}\n";
echo "Duration: {$result['performance']['duration_seconds']}s\n";
echo "Speed: {$result['performance']['tokens_per_second']} tokens/sec\n";
echo "Cost: {$result['cost']['formatted']}\n";
```

## Pricing and Cost Optimization

Understanding Claude's pricing model is essential for building cost-effective applications.

### Current Pricing (2025)

**Claude Opus 4**
- Input: $15.00 per million tokens
- Output: $75.00 per million tokens

**Claude Sonnet 4**
- Input: $3.00 per million tokens
- Output: $15.00 per million tokens

**Claude Haiku 4**
- Input: $0.25 per million tokens
- Output: $1.25 per million tokens

::: info
Pricing is subject to change. Check [anthropic.com/pricing](https://www.anthropic.com/pricing) for current rates.
:::

### Token Calculation

Tokens are pieces of words. Approximately:
- **1 token** ≈ 4 characters in English
- **1 token** ≈ ¾ of a word
- **100 tokens** ≈ 75 words
- **1,000 tokens** ≈ 750 words

```php
<?php
# filename: examples/token-estimator.php
declare(strict_types=1);

class TokenEstimator
{
    /**
     * Rough estimation of tokens in text
     * Not exact, but useful for cost estimation
     */
    public static function estimate(string $text): int
    {
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Count characters (approximate: 1 token ≈ 4 chars)
        $charCount = mb_strlen($text);

        return (int) ceil($charCount / 4);
    }

    public static function estimateCost(
        string $inputText,
        int $expectedOutputTokens,
        string $model
    ): array {
        $inputTokens = self::estimate($inputText);

        $pricing = match($model) {
            'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
            'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
            'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($expectedOutputTokens / 1_000_000) * $pricing['output'];

        return [
            'input_tokens' => $inputTokens,
            'output_tokens' => $expectedOutputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $inputCost + $outputCost,
        ];
    }
}

// Usage
$prompt = "Analyze this Laravel controller and suggest improvements: [large code block]";
$estimate = TokenEstimator::estimateCost($prompt, 1000, 'claude-sonnet-4-20250514');

echo "Estimated cost: $" . number_format($estimate['total_cost'], 6) . "\n";
```

### Cost Optimization Strategies

**1. Choose the Right Model**
```php
<?php
# filename: examples/model-cost-optimization.php
declare(strict_types=1);

class CostOptimizer
{
    public static function selectCostEffectiveModel(string $taskType): string
    {
        return match($taskType) {
            'classification',
            'extraction',
            'simple-generation' => 'claude-haiku-4-20250514',

            'code-review',
            'documentation',
            'analysis' => 'claude-sonnet-4-20250514',

            'architecture',
            'complex-reasoning',
            'creative-work' => 'claude-opus-4-20250514',

            default => 'claude-sonnet-4-20250514',
        };
    }
}
```

**2. Minimize Token Usage**
```php
<?php
# Inefficient: Verbose prompt
$prompt = "I would like you to please analyze the following PHP code that I'm going to provide below and tell me what you think about it and if there are any issues or problems that you can identify:";

# Efficient: Concise prompt
$prompt = "Analyze this PHP code for issues:";

// Both produce similar results, but efficient version saves ~30 tokens
```

**3. Limit max_tokens**
```php
<?php
# Set appropriate max_tokens based on expected response length
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 256,  // Short response expected
    'messages' => [[
        'role' => 'user',
        'content' => 'Classify this email as spam or not: ' . $email
    ]]
]);
```

**4. Cache System Prompts** (Advanced)
```php
<?php
# Reuse system prompts across requests to save tokens
# (Requires prompt caching feature - covered in Chapter 15)
$systemPrompt = "You are a PHP expert...";  // Reused across requests
```

**5. Batch Processing**
```php
<?php
# filename: examples/batch-processing.php
declare(strict_types=1);

class BatchProcessor
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    /**
     * Process multiple items in a single request
     * More cost-effective than individual requests
     */
    public function classifyMultiple(array $items): array
    {
        $itemsList = implode("\n", array_map(
            fn($i, $item) => ($i + 1) . ". {$item}",
            array_keys($items),
            $items
        ));

        $response = $this->client->messages()->create([
            'model' => 'claude-haiku-4-20250514',
            'max_tokens' => count($items) * 50,
            'messages' => [[
                'role' => 'user',
                'content' => "Classify each item as positive or negative. Return format: number. classification\n\n{$itemsList}"
            ]]
        ]);

        return $this->parseResults($response->content[0]->text, count($items));
    }

    private function parseResults(string $text, int $expectedCount): array
    {
        $lines = explode("\n", trim($text));
        $results = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\.\s*(\w+)/', $line, $matches)) {
                $results[(int)$matches[1]] = strtolower($matches[2]);
            }
        }

        return $results;
    }
}

// Example: Classify 10 items in one request instead of 10 requests
$processor = new BatchProcessor($client);
$items = [
    "Great product, love it!",
    "Terrible experience, would not recommend.",
    "It's okay, nothing special.",
    // ... 7 more items
];

$classifications = $processor->classifyMultiple($items);
// Cost: ~1 request instead of 10 requests
```

## How Conversations Work

Claude processes conversations using a stateless Messages API. Understanding this is crucial for building conversational applications.

### Stateless Architecture

Each API call is independent. Claude doesn't "remember" previous conversations unless you explicitly include them.

```php
<?php
# filename: examples/stateless-conversation.php
declare(strict_types=1);

// Request 1
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'My name is John.'
    ]]
]);

// Request 2 - Claude does NOT remember John
$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'What is my name?'
    ]]
]);

// Claude will say it doesn't know your name
echo $response2->content[0]->text;
```

### Maintaining Conversation History

To maintain context, include previous messages in each request:

```php
<?php
# filename: examples/conversation-history.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class ConversationManager
{
    private array $history = [];

    public function __construct(
        private readonly Anthropic $client,
        private readonly string $model = 'claude-sonnet-4-20250514'
    ) {}

    public function addUserMessage(string $content): void
    {
        $this->history[] = [
            'role' => 'user',
            'content' => $content
        ];
    }

    public function addAssistantMessage(string $content): void
    {
        $this->history[] = [
            'role' => 'assistant',
            'content' => $content
        ];
    }

    public function sendMessage(string $message): string
    {
        // Add user message to history
        $this->addUserMessage($message);

        // Send entire conversation history
        $response = $this->client->messages()->create([
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => $this->history
        ]);

        $assistantResponse = $response->content[0]->text;

        // Add assistant response to history
        $this->addAssistantMessage($assistantResponse);

        return $assistantResponse;
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function clearHistory(): void
    {
        $this->history = [];
    }
}

// Usage
$conversation = new ConversationManager($client);

echo "User: My name is Sarah.\n";
$response1 = $conversation->sendMessage("My name is Sarah.");
echo "Claude: {$response1}\n\n";

echo "User: I work as a PHP developer.\n";
$response2 = $conversation->sendMessage("I work as a PHP developer.");
echo "Claude: {$response2}\n\n";

echo "User: What do you know about me?\n";
$response3 = $conversation->sendMessage("What do you know about me?");
echo "Claude: {$response3}\n\n";

// Claude now remembers: name is Sarah, works as PHP developer
```

### Message Alternation

Messages must alternate between 'user' and 'assistant' roles:

```php
<?php
# ✓ Valid: Alternating roles
$messages = [
    ['role' => 'user', 'content' => 'Hello'],
    ['role' => 'assistant', 'content' => 'Hi there!'],
    ['role' => 'user', 'content' => 'How are you?'],
];

# ✗ Invalid: Consecutive user messages
$messages = [
    ['role' => 'user', 'content' => 'Hello'],
    ['role' => 'user', 'content' => 'How are you?'],  // Error!
];

# ✓ Fix: Combine into single user message
$messages = [
    ['role' => 'user', 'content' => "Hello\n\nHow are you?"],
];
```

### System Prompts

System prompts set the context for the entire conversation:

```php
<?php
# filename: examples/system-prompts.php
declare(strict_types=1);

// System prompt provides instructions that apply to all messages
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'system' => 'You are a senior PHP developer specializing in Laravel. Provide concise, practical answers with code examples. Always follow PSR-12 coding standards.',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'How do I create a custom artisan command?'
        ]
    ]
]);

// Claude responds as a Laravel expert with code examples
echo $response->content[0]->text;
```

### Context Window Management

Claude has a 200,000 token context window, but costs increase with conversation length.

```php
<?php
# filename: examples/context-management.php
declare(strict_types=1);

class ManagedConversation
{
    private array $history = [];
    private int $maxHistoryTokens = 10000;  // Keep last ~10K tokens

    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function sendMessage(string $message): string
    {
        $this->history[] = ['role' => 'user', 'content' => $message];

        // Trim history if too long
        $this->trimHistory();

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $this->history
        ]);

        $reply = $response->content[0]->text;
        $this->history[] = ['role' => 'assistant', 'content' => $reply];

        return $reply;
    }

    private function trimHistory(): void
    {
        $estimatedTokens = 0;

        foreach (array_reverse($this->history) as $message) {
            $estimatedTokens += strlen($message['content']) / 4;
        }

        // If over limit, remove oldest messages
        while ($estimatedTokens > $this->maxHistoryTokens && count($this->history) > 2) {
            $removed = array_shift($this->history);
            $estimatedTokens -= strlen($removed['content']) / 4;
        }
    }
}
```

## Practical Integration Patterns

### Pattern 1: Simple Request-Response

```php
<?php
# filename: examples/pattern-simple.php
declare(strict_types=1);

class SimpleClaudeService
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function ask(string $question): string
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $question]
            ]
        ]);

        return $response->content[0]->text;
    }
}

// Usage
$service = new SimpleClaudeService($client);
$answer = $service->ask('What is dependency injection?');
echo $answer;
```

### Pattern 2: Configured Service

```php
<?php
# filename: examples/pattern-configured.php
declare(strict_types=1);

class ConfiguredClaudeService
{
    public function __construct(
        private readonly Anthropic $client,
        private readonly string $model = 'claude-sonnet-4-20250514',
        private readonly ?string $systemPrompt = null,
        private readonly int $maxTokens = 2048,
        private readonly float $temperature = 1.0
    ) {}

    public function generate(string $prompt): string
    {
        $params = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        if ($this->systemPrompt) {
            $params['system'] = $this->systemPrompt;
        }

        $response = $this->client->messages()->create($params);

        return $response->content[0]->text;
    }
}

// Usage
$codeReviewer = new ConfiguredClaudeService(
    client: $client,
    model: 'claude-sonnet-4-20250514',
    systemPrompt: 'You are a code reviewer. Be thorough but concise.',
    maxTokens: 3000,
    temperature: 0.3  // More focused for code review
);

$review = $codeReviewer->generate("Review this code:\n\n{$code}");
```

### Pattern 3: Multi-Turn Conversation

```php
<?php
# filename: examples/pattern-conversation.php
declare(strict_types=1);

class ConversationalService
{
    private array $messages = [];

    public function __construct(
        private readonly Anthropic $client,
        private readonly string $systemPrompt = ''
    ) {}

    public function chat(string $userMessage): string
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $params = [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $this->messages
        ];

        if ($this->systemPrompt) {
            $params['system'] = $this->systemPrompt;
        }

        $response = $this->client->messages()->create($params);
        $reply = $response->content[0]->text;

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $reply
        ];

        return $reply;
    }

    public function reset(): void
    {
        $this->messages = [];
    }
}

// Usage
$assistant = new ConversationalService(
    $client,
    'You are a helpful PHP programming tutor.'
);

$reply1 = $assistant->chat("What are PHP traits?");
echo "Q: What are PHP traits?\nA: {$reply1}\n\n";

$reply2 = $assistant->chat("Can you show me an example?");
echo "Q: Can you show me an example?\nA: {$reply2}\n\n";

$reply3 = $assistant->chat("When should I use them instead of inheritance?");
echo "Q: When should I use them instead of inheritance?\nA: {$reply3}\n";
```

## Exercises

### Exercise 1: Model Comparison Tool

Build a tool that sends the same prompt to all three models and compares results:

```php
<?php
class ModelComparison
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function compareModels(string $prompt): array
    {
        // TODO: Send same prompt to Opus, Sonnet, and Haiku
        // Return array with results, timing, and cost for each
    }
}

// Test with:
$comparison = new ModelComparison($client);
$results = $comparison->compareModels("Explain PHP generators in one paragraph.");
```

### Exercise 2: Cost Calculator

Create a class that estimates and tracks API costs:

```php
<?php
class CostTracker
{
    private array $requests = [];

    public function trackRequest(string $model, int $inputTokens, int $outputTokens): void
    {
        // TODO: Calculate cost and store request data
    }

    public function getTotalCost(): float
    {
        // TODO: Return total cost of all tracked requests
    }

    public function getCostByModel(): array
    {
        // TODO: Return cost breakdown by model
    }
}
```

### Exercise 3: Smart Conversation Manager

Build a conversation manager that automatically selects the right model:

```php
<?php
class SmartConversation
{
    public function chat(string $message, string $taskType = 'general'): string
    {
        // TODO: Select appropriate model based on task type
        // TODO: Maintain conversation history
        // TODO: Automatically trim old messages
    }
}

// Should automatically use Haiku for simple, Sonnet for general, Opus for complex
$chat = new SmartConversation($client);
$chat->chat("Hello!", 'simple');           // → Uses Haiku
$chat->chat("Explain MVC", 'general');     // → Uses Sonnet
$chat->chat("Design a system", 'complex'); // → Uses Opus
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Loop through models array, call API for each, measure time with microtime(), calculate costs using pricing table.

**Exercise 2**: Create array to store request data, implement cost calculation method using pricing per model, aggregate costs in getter methods.

**Exercise 3**: Use match expression to select model based on taskType, maintain messages array, implement trimming when estimated tokens exceed threshold.

</details>

## Troubleshooting

**Model not found error?**
- Check model ID spelling: `claude-sonnet-4-20250514` (not `claude-4-sonnet`)
- Ensure you're using a valid, current model ID
- Check Anthropic documentation for latest model names

**Unexpected response format?**
- Always access text via `$response->content[0]->text`
- Check `stop_reason` to understand why generation stopped
- Verify you're not hitting max_tokens limit

**High costs?**
- Review which model you're using (Opus is 25x more expensive than Haiku)
- Check token usage in responses
- Implement request tracking and monitoring
- Consider batching requests where possible

**Rate limiting?**
- Default limits: 50 requests/minute for most tiers
- Implement exponential backoff
- Consider request queuing for high-volume apps
- Contact Anthropic for limit increases

## Key Takeaways

- ✓ **Three Models**: Opus (powerful), Sonnet (balanced), Haiku (fast/cheap)
- ✓ **Messages API**: Stateless architecture requires including conversation history
- ✓ **Token Pricing**: Output tokens cost 5x more than input tokens
- ✓ **Context Window**: 200K tokens for all models, but costs scale with usage
- ✓ **System Prompts**: Set behavioral context for the entire conversation
- ✓ **Message Alternation**: Must alternate between user and assistant roles
- ✓ **Model Selection**: Start with Sonnet for most use cases
- ✓ **Cost Optimization**: Choose right model, minimize tokens, batch requests

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="01"
  label="You understand Claude's capabilities and architecture!"
/>

---

Continue to [Chapter 02: Authentication and API Keys](/series/claude-php-developers/chapters/02-authentication-api-keys) to learn secure API key management.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 01 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-01)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-01
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/complete-api-usage.php
```
