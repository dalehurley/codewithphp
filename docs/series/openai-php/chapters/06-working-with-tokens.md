---
title: "06: Working with Tokens"
description: "Master token counting, context management, and cost optimization strategies for OpenAI applications"
series: "openai-php"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/05-error-handling-resilience"
  - "Understanding of text encoding"
---

![Working with Tokens](/images/openai-php/chapter-06-tokens-hero-full.webp)

[Home](/series/openai-php) > [Chapter 05](/series/openai-php/chapters/05-error-handling-resilience) > Working with Tokens

# Chapter 06: Working with Tokens

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">50-60 minutes</span>

## Overview

Tokens are the fundamental unit of OpenAI's API—they determine costs, context limits, and response lengths. Understanding tokens deeply is essential for building cost-effective, performant applications. Many developers stumble by treating tokens as "words," leading to unexpected costs and context window issues.

In this chapter, you'll learn exactly how tokenization works, how to count tokens accurately, and how to manage context windows efficiently. You'll build tools to estimate costs before making requests, optimize prompts to reduce token usage, and handle conversation history without hitting limits.

Whether you're building a chatbot that needs to maintain long conversations or a content generator processing thousands of articles, these token management techniques will save you money and prevent errors.

## What You'll Learn

- 🔢 **Tokenization Basics**: Understand how text becomes tokens
- 📏 **Accurate Counting**: Count tokens precisely for cost estimation
- 🎯 **Context Management**: Work within model token limits
- 💰 **Cost Optimization**: Reduce token usage without sacrificing quality
- 🔄 **Conversation Handling**: Manage multi-turn chat history
- 📊 **Token Analysis**: Analyze and visualize token usage
- ⚡ **Optimization Strategies**: Practical techniques to minimize tokens

## Prerequisites

- ✅ Completed Chapters 01-05
- ✅ Understanding of strings and character encoding
- ✅ Basic math for cost calculations
- ✅ Familiarity with JSON structures

---

## What Are Tokens?

### Tokenization Explained

Tokens are not words—they're chunks of text that the model processes. The tokenization algorithm breaks text into these pieces:

```
"Hello, world!" → ["Hello", ",", " world", "!"]
(4 tokens)

"OpenAI is amazing" → ["Open", "AI", " is", " amazing"]
(4 tokens)

"PHP" → ["PHP"]
(1 token)

"ChatGPT" → ["Chat", "G", "PT"]
(3 tokens)
```

**Key Rules:**
- Common words: Usually 1 token
- Spaces: Part of the following token
- Punctuation: Often separate tokens
- Special characters: May be multiple tokens
- Numbers: Vary widely

### Token Statistics

```
English text: ~4 characters = 1 token
Code: ~3-4 characters = 1 token
Other languages: Varies significantly
```

---

## Token Counting in PHP

### Using tiktoken (Official Tokenizer)

```bash
# Install tiktoken PHP wrapper
composer require yethee/tiktoken
```

```php
<?php

use Yethee\Tiktoken\Tiktoken;

class TokenCounter
{
    private Tiktoken $tiktoken;

    public function __construct(string $model = 'gpt-3.5-turbo')
    {
        $this->tiktoken = Tiktoken::encodingForModel($model);
    }

    public function count(string $text): int
    {
        $tokens = $this->tiktoken->encode($text);
        return count($tokens);
    }

    public function countMessages(array $messages): int
    {
        $count = 0;

        foreach ($messages as $message) {
            // Every message has overhead: role + content + formatting
            $count += 4; // Message overhead

            $count += $this->count($message['role']);
            $count += $this->count($message['content']);

            if (isset($message['name'])) {
                $count += $this->count($message['name']);
                $count -= 1; // Role is omitted if name is present
            }
        }

        $count += 2; // Every conversation starts with priming tokens

        return $count;
    }

    public function estimateCost(
        int $inputTokens,
        int $outputTokens,
        string $model = 'gpt-3.5-turbo'
    ): float {
        $pricing = [
            'gpt-4-turbo-preview' => ['input' => 10.00, 'output' => 30.00],
            'gpt-4' => ['input' => 30.00, 'output' => 60.00],
            'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
        ];

        $rates = $pricing[$model] ?? $pricing['gpt-3.5-turbo'];

        $inputCost = ($inputTokens / 1_000_000) * $rates['input'];
        $outputCost = ($outputTokens / 1_000_000) * $rates['output'];

        return $inputCost + $outputCost;
    }
}

// Usage
$counter = new TokenCounter('gpt-3.5-turbo');

$text = "Hello, world! This is a test message.";
$tokens = $counter->count($text);
echo "Tokens: $tokens\n";  // ~9 tokens

$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'What is PHP?'],
];
$messageTokens = $counter->countMessages($messages);
echo "Message tokens: $messageTokens\n";  // ~24 tokens

$cost = $counter->estimateCost($messageTokens, 50, 'gpt-3.5-turbo');
echo "Estimated cost: $" . number_format($cost, 6) . "\n";
```

### Approximation Without tiktoken

```php
<?php

/**
 * Approximate token counting (less accurate but no dependencies)
 */

class ApproximateTokenCounter
{
    public function count(string $text): int
    {
        // Very rough approximation: 4 characters per token
        return (int) ceil(strlen($text) / 4);
    }

    public function countWords(string $text): int
    {
        // Words-based approximation (0.75 tokens per word average)
        $words = str_word_count($text);
        return (int) ceil($words * 0.75);
    }

    public function countBetter(string $text): int
    {
        // Improved approximation
        $chars = strlen($text);
        $spaces = substr_count($text, ' ');
        $punctuation = preg_match_all('/[.,!?;:]/', $text);

        // Base: 4 chars per token
        $base = $chars / 4;

        // Adjustment: spaces and punctuation often create token boundaries
        $adjustment = ($spaces + $punctuation) * 0.25;

        return (int) ceil($base + $adjustment);
    }
}
```

---

## Context Window Management

### Context Window Sizes

```php
<?php

class ContextManager
{
    private const MODEL_LIMITS = [
        'gpt-4-turbo-preview' => 128000,
        'gpt-4-32k' => 32768,
        'gpt-4' => 8192,
        'gpt-3.5-turbo' => 16384,
    ];

    public function __construct(
        private string $model = 'gpt-3.5-turbo',
        private int $maxOutputTokens = 1000
    ) {}

    public function getMaxInputTokens(): int
    {
        $limit = self::MODEL_LIMITS[$this->model] ?? 4096;
        return $limit - $this->maxOutputTokens;
    }

    public function canFit(int $tokenCount): bool
    {
        return $tokenCount <= $this->getMaxInputTokens();
    }

    public function getRemainingTokens(int $usedTokens): int
    {
        return max(0, $this->getMaxInputTokens() - $usedTokens);
    }

    public function getUsagePercentage(int $usedTokens): float
    {
        return ($usedTokens / $this->getMaxInputTokens()) * 100;
    }
}

// Usage
$context = new ContextManager('gpt-3.5-turbo', maxOutputTokens: 500);

echo "Max input tokens: " . $context->getMaxInputTokens() . "\n";
// Output: 15884

$currentTokens = 10000;
echo "Can fit: " . ($context->canFit($currentTokens) ? 'Yes' : 'No') . "\n";
// Output: Yes

echo "Remaining: " . $context->getRemainingTokens($currentTokens) . "\n";
// Output: 5884

echo "Usage: " . round($context->getUsagePercentage($currentTokens), 1) . "%\n";
// Output: Usage: 62.9%
```

### Automatic Context Trimming

```php
<?php

/**
 * Intelligent conversation history management
 */

class ConversationHistory
{
    private array $messages = [];
    private TokenCounter $counter;
    private int $maxTokens;

    public function __construct(
        string $model = 'gpt-3.5-turbo',
        int $maxTokens = 4000
    ) {
        $this->counter = new TokenCounter($model);
        $this->maxTokens = $maxTokens;
    }

    public function addMessage(string $role, string $content): void
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => time(),
        ];

        $this->trimToFit();
    }

    private function trimToFit(): void
    {
        while (count($this->messages) > 1) {
            $currentTokens = $this->counter->countMessages($this->messages);

            if ($currentTokens <= $this->maxTokens) {
                break;
            }

            // Remove oldest message (but keep system message)
            if ($this->messages[0]['role'] === 'system') {
                // Keep system message, remove second message
                array_splice($this->messages, 1, 1);
            } else {
                // Remove first message
                array_shift($this->messages);
            }
        }
    }

    public function getMessages(): array
    {
        return array_map(
            fn($m) => ['role' => $m['role'], 'content' => $m['content']],
            $this->messages
        );
    }

    public function getCurrentTokenCount(): int
    {
        return $this->counter->countMessages($this->messages);
    }

    public function getSummary(): array
    {
        return [
            'message_count' => count($this->messages),
            'token_count' => $this->getCurrentTokenCount(),
            'token_limit' => $this->maxTokens,
            'usage_percentage' => round(
                ($this->getCurrentTokenCount() / $this->maxTokens) * 100,
                1
            ),
        ];
    }
}

// Usage
$history = new ConversationHistory('gpt-3.5-turbo', maxTokens: 4000);

$history->addMessage('system', 'You are a helpful assistant.');
$history->addMessage('user', 'What is PHP?');
$history->addMessage('assistant', 'PHP is a server-side scripting language...');
$history->addMessage('user', 'Tell me more about its history.');

print_r($history->getSummary());
/*
Array (
    [message_count] => 4
    [token_count] => 156
    [token_limit] => 4000
    [usage_percentage] => 3.9
)
*/
```

---

## Token Optimization Strategies

### 1. Prompt Compression

```php
<?php

class PromptOptimizer
{
    public function compress(string $prompt): string
    {
        $optimized = $prompt;

        // Remove extra whitespace
        $optimized = preg_replace('/\s+/', ' ', $optimized);

        // Remove redundant phrases
        $redundant = [
            'please ' => '',
            'I would like you to ' => '',
            'Can you please ' => '',
            'I need you to ' => '',
        ];

        $optimized = str_ireplace(
            array_keys($redundant),
            array_values($redundant),
            $optimized
        );

        // Trim
        $optimized = trim($optimized);

        return $optimized;
    }

    public function compareTokens(string $original, string $optimized): array
    {
        $counter = new TokenCounter();

        $originalTokens = $counter->count($original);
        $optimizedTokens = $counter->count($optimized);

        return [
            'original_tokens' => $originalTokens,
            'optimized_tokens' => $optimizedTokens,
            'saved_tokens' => $originalTokens - $optimizedTokens,
            'reduction_percentage' => round(
                (($originalTokens - $optimizedTokens) / $originalTokens) * 100,
                1
            ),
        ];
    }
}

// Usage
$optimizer = new PromptOptimizer();

$original = "Please can you help me write a detailed blog post about PHP best practices. I would like you to include examples.";

$optimized = $optimizer->compress($original);
echo "Optimized: $optimized\n";
// "Write a detailed blog post about PHP best practices. Include examples."

$stats = $optimizer->compareTokens($original, $optimized);
print_r($stats);
/*
Array (
    [original_tokens] => 28
    [optimized_tokens] => 16
    [saved_tokens] => 12
    [reduction_percentage] => 42.9
)
*/
```

### 2. Response Length Control

```php
<?php

class ResponseController
{
    public function calculateMaxTokens(
        int $promptTokens,
        string $model = 'gpt-3.5-turbo',
        float $safetyMargin = 0.9
    ): int {
        $contextManager = new ContextManager($model);
        $maxInput = $contextManager->getMaxInputTokens();

        // Available tokens for response
        $available = $maxInput - $promptTokens;

        // Apply safety margin
        $safe = (int) ($available * $safetyMargin);

        return max($safe, 1);
    }

    public function optimizeForLength(
        array $messages,
        int $desiredOutputTokens,
        string $model = 'gpt-3.5-turbo'
    ): array {
        $counter = new TokenCounter($model);
        $contextManager = new ContextManager($model, $desiredOutputTokens);

        $currentTokens = $counter->countMessages($messages);
        $maxAllowed = $contextManager->getMaxInputTokens();

        if ($currentTokens <= $maxAllowed) {
            return $messages;
        }

        // Trim messages to fit
        $trimmed = $messages;
        $systemMessage = null;

        // Preserve system message
        if ($trimmed[0]['role'] === 'system') {
            $systemMessage = array_shift($trimmed);
        }

        // Remove oldest messages until it fits
        while ($trimmed && $counter->countMessages($trimmed) > $maxAllowed) {
            array_shift($trimmed);
        }

        // Re-add system message
        if ($systemMessage) {
            array_unshift($trimmed, $systemMessage);
        }

        return $trimmed;
    }
}
```

---

## Cost Estimation Tools

```php
<?php

/**
 * Comprehensive cost calculator and tracker
 */

class CostEstimator
{
    private array $history = [];

    public function estimate(
        string $prompt,
        int $maxTokens,
        string $model = 'gpt-3.5-turbo'
    ): array {
        $counter = new TokenCounter($model);

        $inputTokens = $counter->count($prompt);
        $outputTokens = $maxTokens;

        $cost = $counter->estimateCost($inputTokens, $outputTokens, $model);

        return [
            'input_tokens' => $inputTokens,
            'estimated_output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'estimated_cost' => $cost,
            'formatted_cost' => '$' . number_format($cost, 6),
        ];
    }

    public function trackActual(
        int $promptTokens,
        int $completionTokens,
        string $model = 'gpt-3.5-turbo'
    ): void {
        $counter = new TokenCounter($model);
        $cost = $counter->estimateCost($promptTokens, $completionTokens, $model);

        $this->history[] = [
            'timestamp' => time(),
            'model' => $model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost' => $cost,
        ];
    }

    public function getTotalCost(): float
    {
        return array_sum(array_column($this->history, 'cost'));
    }

    public function getTotalTokens(): int
    {
        return array_sum(array_column($this->history, 'total_tokens'));
    }

    public function getAverageCostPerRequest(): float
    {
        $count = count($this->history);
        return $count > 0 ? $this->getTotalCost() / $count : 0;
    }

    public function getStatistics(): array
    {
        return [
            'total_requests' => count($this->history),
            'total_tokens' => $this->getTotalTokens(),
            'total_cost' => $this->getTotalCost(),
            'average_cost_per_request' => $this->getAverageCostPerRequest(),
            'formatted_total' => '$' . number_format($this->getTotalCost(), 2),
        ];
    }
}

// Usage
$estimator = new CostEstimator();

// Estimate before request
$estimate = $estimator->estimate(
    "Write a blog post about PHP",
    maxTokens: 500,
    model: 'gpt-3.5-turbo'
);

echo "Estimated cost: {$estimate['formatted_cost']}\n";

// Track actual after request
$estimator->trackActual(
    promptTokens: 15,
    completionTokens: 450,
    model: 'gpt-3.5-turbo'
);

print_r($estimator->getStatistics());
```

---

## Exercises

### Exercise 1: Token Visualization

Create a tool that:
1. Shows token breakdown of text
2. Highlights each token boundary
3. Calculates cost per token
4. Suggests optimizations

### Exercise 2: Smart Context Manager

Build a manager that:
1. Prioritizes important messages
2. Summarizes old context when trimming
3. Tracks conversation topics
4. Optimizes for token efficiency

### Exercise 3: Budget Controller

Implement:
1. Daily/monthly budget limits
2. Alerts when approaching limits
3. Automatic model downgrade to stay in budget
4. Cost projections based on usage

### Exercise 4: Batch Optimizer

Create a system that:
1. Batches multiple prompts together
2. Shares common context
3. Minimizes total tokens
4. Calculates bulk savings

---

## Key Takeaways

- ✅ Tokens are not words—understand the tokenization algorithm
- ✅ Use tiktoken library for accurate token counting
- ✅ Context windows include both input and output tokens
- ✅ Always leave buffer space for responses
- ✅ Trim conversation history proactively to avoid errors
- ✅ Estimate costs before making requests
- ✅ Optimize prompts to reduce token usage
- ✅ Different languages have different token densities

---

## Next Steps

With token mastery achieved, you're ready to dive into the Chat Completions API!

👉 **[Chapter 07: Chat Completions API Fundamentals](/series/openai-php/chapters/07-chat-completions-api-fundamentals)**

In the next chapter, you'll learn:
- Message roles and their purposes
- Building effective system prompts
- Managing multi-turn conversations
- Response formatting techniques

---

[← Previous: Chapter 05](/series/openai-php/chapters/05-error-handling-resilience) | [Next: Chapter 07 →](/series/openai-php/chapters/07-chat-completions-api-fundamentals)
