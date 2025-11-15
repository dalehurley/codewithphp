---
title: "09: Token Management and Counting"
description: "Master tokenization, cost calculation, and context window optimization. Track usage, manage budgets, and maximize Claude's 200K token context efficiently."
series: "claude-php-developers"
chapter: 9
order: 9
difficulty: "Expert"
prerequisites:
  - "Chapter 00-08 completed"
  - "Understanding of API pricing models"
  - "Basic cost-benefit analysis skills"
---

![09: Token Management and Counting](/images/claude-php/chapter-09-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 09</span>
</div>

# Chapter 09: Token Management and Counting

## Overview

Tokens are the currency of Claude API—they determine both your costs and what you can accomplish. Understanding how tokenization works, accurately counting tokens, optimizing context window usage, and implementing effective budget controls transforms Claude from an unpredictable expense into a cost-effective tool.

This chapter teaches you what tokens are and how they're calculated, how to count tokens before making API calls, strategies for staying within context limits, cost optimization techniques, and budget management systems.

By the end, you'll build production-ready token management systems that prevent cost overruns while maximizing Claude's capabilities.

## Prerequisites

Before starting, ensure you understand:

- ✓ Basic Claude API usage (Chapters 00-03)
- ✓ Message structure and conversation flow
- ✓ System prompts and role definition (Chapter 07)
- ✓ Basic mathematics and cost calculation

**Estimated Time**: 45-60 minutes

## Understanding Tokenization

### What Are Tokens?

Tokens are not words—they're chunks of text that language models process.

```php
<?php
# filename: examples/01-token-basics.php
declare(strict_types=1);

/**
 * Token examples - approximate tokenization
 *
 * Claude uses a tokenizer similar to other LLMs.
 * Rough rules of thumb:
 * - 1 token ≈ 4 characters of English text
 * - 1 token ≈ ¾ of a word on average
 * - Common words = 1 token
 * - Uncommon words = 2-3 tokens
 * - Code is typically more tokens per character
 */

$examples = [
    'Hello' => 1,                    // Common word = 1 token
    'Hello world' => 2,              // Two common words = 2 tokens
    'PHP' => 1,                      // Acronym = 1 token
    'PHP developer' => 2,            // 2 tokens
    'tokenization' => 2,             // Long word = multiple tokens
    'antidisestablishmentarianism' => 6,  // Very long = many tokens
    'function getName() {}' => 6,    // Code, roughly 6 tokens
    '$user->getName()' => 5,         // PHP code with symbols
];

echo "Token Estimation Examples:\n\n";

foreach ($examples as $text => $estimatedTokens) {
    $charCount = strlen($text);
    $wordCount = str_word_count($text);
    $tokensPerChar = $charCount > 0 ? $estimatedTokens / $charCount : 0;

    echo "Text: \"{$text}\"\n";
    echo "  Characters: {$charCount}\n";
    echo "  Words: {$wordCount}\n";
    echo "  Estimated tokens: {$estimatedTokens}\n";
    echo "  Tokens per character: " . round($tokensPerChar, 2) . "\n\n";
}

// General estimation formula
function estimateTokens(string $text): int
{
    // Very rough estimate: 1 token per 4 characters
    return (int) ceil(strlen($text) / 4);
}

$sampleText = <<<'PHP'
function authenticateUser(string $email, string $password): ?User
{
    $user = User::where('email', $email)->first();

    if (!$user || !password_verify($password, $user->password_hash)) {
        return null;
    }

    return $user;
}
PHP;

echo "Sample PHP Code:\n";
echo $sampleText . "\n\n";
echo "Estimated tokens: " . estimateTokens($sampleText) . "\n";
echo "Actual tokens would need precise counting...\n";
```

### Claude's Token Limits

```php
<?php
# filename: examples/02-model-limits.php
declare(strict_types=1);

class ClaudeModelLimits
{
    public const MODELS = [
        'claude-opus-4-20250514' => [
            'context_window' => 200_000,
            'max_output' => 16_384,
            'input_price_per_1m' => 15.00,   // USD
            'output_price_per_1m' => 75.00,  // USD
        ],
        'claude-sonnet-4-20250514' => [
            'context_window' => 200_000,
            'max_output' => 16_384,
            'input_price_per_1m' => 3.00,
            'output_price_per_1m' => 15.00,
        ],
        'claude-haiku-4-20250514' => [
            'context_window' => 200_000,
            'max_output' => 16_384,
            'input_price_per_1m' => 0.80,
            'output_price_per_1m' => 4.00,
        ],
    ];

    public static function getLimit(string $model, string $type): int
    {
        return self::MODELS[$model][$type] ?? 0;
    }

    public static function getPrice(string $model, string $type): float
    {
        $key = $type . '_price_per_1m';
        return self::MODELS[$model][$key] ?? 0.0;
    }

    public static function calculateCost(
        string $model,
        int $inputTokens,
        int $outputTokens
    ): float {
        $inputCost = ($inputTokens / 1_000_000) * self::getPrice($model, 'input');
        $outputCost = ($outputTokens / 1_000_000) * self::getPrice($model, 'output');

        return $inputCost + $outputCost;
    }

    public static function estimateMaxTokenCost(string $model): float
    {
        $contextWindow = self::getLimit($model, 'context_window');
        $maxOutput = self::getLimit($model, 'max_output');

        // Worst case: full context window of input + max output
        return self::calculateCost($model, $contextWindow, $maxOutput);
    }
}

// Display model information
echo "Claude Model Comparison:\n\n";

foreach (ClaudeModelLimits::MODELS as $model => $specs) {
    echo str_pad($model, 30) . "\n";
    echo "  Context window: " . number_format($specs['context_window']) . " tokens\n";
    echo "  Max output: " . number_format($specs['max_output']) . " tokens\n";
    echo "  Input cost: $" . $specs['input_price_per_1m'] . " per 1M tokens\n";
    echo "  Output cost: $" . $specs['output_price_per_1m'] . " per 1M tokens\n";
    echo "  Max theoretical cost: $" . number_format(
        ClaudeModelLimits::estimateMaxTokenCost($model),
        2
    ) . " per request\n\n";
}
```

## Accurate Token Counting

### Token Counter Implementation

```php
<?php
# filename: src/TokenCounter.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

/**
 * Token counter for Claude API
 *
 * Uses approximate counting since exact tokenization requires
 * the same tokenizer Claude uses. This provides good estimates
 * for planning and budgeting.
 */
class TokenCounter
{
    private const CHARS_PER_TOKEN = 4;
    private const CODE_MULTIPLIER = 1.3;  // Code uses ~30% more tokens

    /**
     * Estimate token count for text
     */
    public function count(string $text): int
    {
        // Basic estimation
        $baseCount = strlen($text) / self::CHARS_PER_TOKEN;

        // Adjust for special characters and structure
        $adjustedCount = $baseCount;

        // Code detection (has common code markers)
        if ($this->looksLikeCode($text)) {
            $adjustedCount *= self::CODE_MULTIPLIER;
        }

        return (int) ceil($adjustedCount);
    }

    /**
     * Count tokens in messages array
     */
    public function countMessages(array $messages): int
    {
        $total = 0;

        foreach ($messages as $message) {
            // Message role overhead (~4 tokens per message)
            $total += 4;

            // Message content
            if (is_string($message['content'])) {
                $total += $this->count($message['content']);
            } elseif (is_array($message['content'])) {
                // Multi-part content
                foreach ($message['content'] as $part) {
                    if (isset($part['text'])) {
                        $total += $this->count($part['text']);
                    }
                    if (isset($part['image'])) {
                        $total += 1000; // Images ~1000 tokens
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Count tokens in entire API request
     */
    public function countRequest(array $request): array
    {
        $counts = [
            'system' => 0,
            'messages' => 0,
            'overhead' => 10,  // API overhead
            'total' => 0,
        ];

        // System prompt
        if (isset($request['system'])) {
            $counts['system'] = $this->count($request['system']);
        }

        // Messages
        if (isset($request['messages'])) {
            $counts['messages'] = $this->countMessages($request['messages']);
        }

        $counts['total'] = $counts['system'] + $counts['messages'] + $counts['overhead'];

        return $counts;
    }

    /**
     * Detect if text looks like code
     */
    private function looksLikeCode(string $text): bool
    {
        $codeIndicators = [
            'function ', 'class ', 'public ', 'private ', 'protected ',
            'return ', 'if (', 'for (', 'while (', 'foreach (',
            '{', '}', '=>', '->', '::', '<?php'
        ];

        $indicatorCount = 0;
        foreach ($codeIndicators as $indicator) {
            if (str_contains($text, $indicator)) {
                $indicatorCount++;
            }
        }

        // If 3+ code indicators, likely code
        return $indicatorCount >= 3;
    }

    /**
     * Estimate response tokens based on max_tokens
     */
    public function estimateResponse(int $maxTokens, float $utilizationRate = 0.8): int
    {
        return (int) ($maxTokens * $utilizationRate);
    }
}

// Usage
$counter = new TokenCounter();

$systemPrompt = 'You are a PHP expert who reviews code for security issues.';
$userMessage = 'Review this code: function login($user, $pass) { /* ... */ }';

echo "Token Estimates:\n";
echo "System prompt: " . $counter->count($systemPrompt) . " tokens\n";
echo "User message: " . $counter->count($userMessage) . " tokens\n\n";

$request = [
    'system' => $systemPrompt,
    'messages' => [
        ['role' => 'user', 'content' => $userMessage]
    ]
];

$breakdown = $counter->countRequest($request);
print_r($breakdown);
```

### Real-Time Token Tracking

```php
<?php
# filename: src/TokenTracker.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use Anthropic\Contracts\ClientContract;

class TokenTracker
{
    private array $history = [];

    public function __construct(
        private ClientContract $client,
        private TokenCounter $counter
    ) {}

    public function track(array $request): object
    {
        // Count input tokens
        $estimatedInput = $this->counter->countRequest($request);

        // Make API call
        $response = $this->client->messages()->create($request);

        // Get actual token counts from response
        $actualInput = $response->usage->inputTokens;
        $actualOutput = $response->usage->outputTokens;

        // Calculate costs
        $model = $request['model'];
        $cost = ClaudeModelLimits::calculateCost($model, $actualInput, $actualOutput);

        // Track
        $record = [
            'timestamp' => time(),
            'model' => $model,
            'estimated_input' => $estimatedInput['total'],
            'actual_input' => $actualInput,
            'actual_output' => $actualOutput,
            'total_tokens' => $actualInput + $actualOutput,
            'cost' => $cost,
            'accuracy' => $this->calculateAccuracy($estimatedInput['total'], $actualInput),
        ];

        $this->history[] = $record;

        return $response;
    }

    private function calculateAccuracy(int $estimated, int $actual): float
    {
        if ($actual === 0) return 0.0;
        return 100 - (abs($estimated - $actual) / $actual * 100);
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function getStats(): array
    {
        if (empty($this->history)) {
            return [];
        }

        return [
            'total_requests' => count($this->history),
            'total_input_tokens' => array_sum(array_column($this->history, 'actual_input')),
            'total_output_tokens' => array_sum(array_column($this->history, 'actual_output')),
            'total_tokens' => array_sum(array_column($this->history, 'total_tokens')),
            'total_cost' => array_sum(array_column($this->history, 'cost')),
            'average_accuracy' => array_sum(array_column($this->history, 'accuracy')) / count($this->history),
        ];
    }

    public function exportCSV(string $filename): void
    {
        $fp = fopen($filename, 'w');

        // Header
        fputcsv($fp, ['Timestamp', 'Model', 'Input', 'Output', 'Total', 'Cost', 'Accuracy']);

        // Data
        foreach ($this->history as $record) {
            fputcsv($fp, [
                date('Y-m-d H:i:s', $record['timestamp']),
                $record['model'],
                $record['actual_input'],
                $record['actual_output'],
                $record['total_tokens'],
                number_format($record['cost'], 6),
                round($record['accuracy'], 2) . '%',
            ]);
        }

        fclose($fp);
    }
}

// Usage
$tracker = new TokenTracker($client, new TokenCounter());

$response = $tracker->track([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Explain PHP generators'
    ]]
]);

echo $response->content[0]->text . "\n\n";

$stats = $tracker->getStats();
echo "Token Usage Stats:\n";
print_r($stats);

// Export to CSV for analysis
$tracker->exportCSV('token_usage.csv');
```

## Context Window Management

### Conversation Context Manager

```php
<?php
# filename: src/ConversationContextManager.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class ConversationContextManager
{
    private array $messages = [];
    private int $maxContextTokens;

    public function __construct(
        private TokenCounter $counter,
        int $maxContextTokens = 180_000  // Leave room for response
    ) {
        $this->maxContextTokens = $maxContextTokens;
    }

    public function addMessage(string $role, string $content): void
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content
        ];

        $this->pruneIfNeeded();
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getCurrentTokenCount(): int
    {
        return $this->counter->countMessages($this->messages);
    }

    public function getRemainingTokens(): int
    {
        return max(0, $this->maxContextTokens - $this->getCurrentTokenCount());
    }

    public function canFit(string $content): bool
    {
        $additionalTokens = $this->counter->count($content);
        return ($this->getCurrentTokenCount() + $additionalTokens) <= $this->maxContextTokens;
    }

    /**
     * Prune old messages if context is too large
     */
    private function pruneIfNeeded(): void
    {
        while ($this->getCurrentTokenCount() > $this->maxContextTokens && count($this->messages) > 1) {
            // Remove oldest message (but keep at least 1)
            array_shift($this->messages);
        }
    }

    /**
     * Prune strategically - keep important messages
     */
    public function pruneStrategic(array $importantIndices = []): void
    {
        $newMessages = [];
        $tokenCount = 0;

        // Always keep important messages
        foreach ($importantIndices as $idx) {
            if (isset($this->messages[$idx])) {
                $newMessages[] = $this->messages[$idx];
                $tokenCount += $this->counter->countMessages([$this->messages[$idx]]);
            }
        }

        // Add most recent messages until we hit limit
        $reversed = array_reverse($this->messages, true);
        foreach ($reversed as $idx => $message) {
            if (in_array($idx, $importantIndices)) {
                continue; // Already added
            }

            $messageTokens = $this->counter->countMessages([$message]);

            if ($tokenCount + $messageTokens <= $this->maxContextTokens) {
                array_unshift($newMessages, $message);
                $tokenCount += $messageTokens;
            } else {
                break;
            }
        }

        $this->messages = $newMessages;
    }

    /**
     * Summarize old messages to save tokens
     */
    public function summarizeOldMessages(
        ClientContract $client,
        int $keepRecentCount = 5
    ): void {
        if (count($this->messages) <= $keepRecentCount) {
            return;
        }

        // Messages to summarize
        $toSummarize = array_slice($this->messages, 0, -$keepRecentCount);

        // Keep recent messages
        $recent = array_slice($this->messages, -$keepRecentCount);

        // Create summary
        $conversationText = '';
        foreach ($toSummarize as $msg) {
            $conversationText .= "{$msg['role']}: {$msg['content']}\n\n";
        }

        $response = $client->messages()->create([
            'model' => 'claude-haiku-4-20250514',  // Use fast model for summary
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => "Summarize this conversation concisely:\n\n{$conversationText}"
            ]]
        ]);

        $summary = $response->content[0]->text;

        // Replace old messages with summary
        $this->messages = [
            ['role' => 'assistant', 'content' => "[Previous conversation summary: {$summary}]"],
            ...$recent
        ];
    }

    public function clear(): void
    {
        $this->messages = [];
    }
}

// Usage
$contextManager = new ConversationContextManager(new TokenCounter());

// Add messages
$contextManager->addMessage('user', 'What is PHP?');
$contextManager->addMessage('assistant', 'PHP is a server-side scripting language...');
$contextManager->addMessage('user', 'How do I use arrays?');
$contextManager->addMessage('assistant', 'PHP arrays are versatile...');

echo "Current tokens: " . $contextManager->getCurrentTokenCount() . "\n";
echo "Remaining tokens: " . $contextManager->getRemainingTokens() . "\n";

// Check if new content fits
$newQuestion = 'Can you explain object-oriented programming in PHP?';
if ($contextManager->canFit($newQuestion)) {
    $contextManager->addMessage('user', $newQuestion);
    echo "Added new message\n";
} else {
    echo "Not enough context space, pruning...\n";
    $contextManager->pruneIfNeeded();
    $contextManager->addMessage('user', $newQuestion);
}
```

### Smart Context Pruning

```php
<?php
# filename: src/SmartContextPruner.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class SmartContextPruner
{
    public function __construct(
        private TokenCounter $counter
    ) {}

    /**
     * Prune conversation using different strategies
     */
    public function prune(
        array $messages,
        int $targetTokens,
        string $strategy = 'recent'
    ): array {
        return match($strategy) {
            'recent' => $this->pruneKeepRecent($messages, $targetTokens),
            'important' => $this->pruneKeepImportant($messages, $targetTokens),
            'balanced' => $this->pruneBalanced($messages, $targetTokens),
            'summarize' => $this->pruneBySummarizing($messages, $targetTokens),
            default => $messages,
        };
    }

    /**
     * Keep most recent messages
     */
    private function pruneKeepRecent(array $messages, int $targetTokens): array
    {
        $kept = [];
        $tokenCount = 0;

        // Work backwards from most recent
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            $msgTokens = $this->counter->countMessages([$messages[$i]]);

            if ($tokenCount + $msgTokens <= $targetTokens) {
                array_unshift($kept, $messages[$i]);
                $tokenCount += $msgTokens;
            } else {
                break;
            }
        }

        return $kept;
    }

    /**
     * Keep important messages (first and recent)
     */
    private function pruneKeepImportant(array $messages, int $targetTokens): array
    {
        if (empty($messages)) return [];

        $kept = [];
        $tokenCount = 0;

        // Always keep first message (context/instructions)
        $first = $messages[0];
        $firstTokens = $this->counter->countMessages([$first]);

        if ($firstTokens <= $targetTokens) {
            $kept[] = $first;
            $tokenCount += $firstTokens;
        }

        // Add recent messages
        for ($i = count($messages) - 1; $i > 0; $i--) {
            $msgTokens = $this->counter->countMessages([$messages[$i]]);

            if ($tokenCount + $msgTokens <= $targetTokens) {
                array_splice($kept, 1, 0, [$messages[$i]]);
                $tokenCount += $msgTokens;
            } else {
                break;
            }
        }

        return $kept;
    }

    /**
     * Balance between old and new
     */
    private function pruneBalanced(array $messages, int $targetTokens): array
    {
        $halfTarget = (int) ($targetTokens / 2);

        // Get first half from beginning
        $beginning = [];
        $beginTokens = 0;
        for ($i = 0; $i < count($messages); $i++) {
            $msgTokens = $this->counter->countMessages([$messages[$i]]);
            if ($beginTokens + $msgTokens <= $halfTarget) {
                $beginning[] = $messages[$i];
                $beginTokens += $msgTokens;
            } else {
                break;
            }
        }

        // Get second half from end
        $end = [];
        $endTokens = 0;
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            $msgTokens = $this->counter->countMessages([$messages[$i]]);
            if ($endTokens + $msgTokens <= $halfTarget) {
                array_unshift($end, $messages[$i]);
                $endTokens += $msgTokens;
            } else {
                break;
            }
        }

        // Add placeholder for omitted middle
        if (count($beginning) + count($end) < count($messages)) {
            $beginning[] = [
                'role' => 'assistant',
                'content' => '[... middle of conversation omitted to save tokens ...]'
            ];
        }

        return array_merge($beginning, $end);
    }

    /**
     * Replace old messages with summary
     */
    private function pruneBySummarizing(array $messages, int $targetTokens): array
    {
        // This is a placeholder - actual implementation would use Claude
        // to summarize old messages (see ConversationContextManager)

        $summary = [
            'role' => 'system',
            'content' => '[Summarized earlier conversation]'
        ];

        return array_merge(
            [$summary],
            $this->pruneKeepRecent($messages, $targetTokens - 100)
        );
    }
}

// Usage
$pruner = new SmartContextPruner(new TokenCounter());

$messages = [
    ['role' => 'user', 'content' => 'Long message 1...'],
    ['role' => 'assistant', 'content' => 'Long response 1...'],
    ['role' => 'user', 'content' => 'Long message 2...'],
    ['role' => 'assistant', 'content' => 'Long response 2...'],
    ['role' => 'user', 'content' => 'Long message 3...'],
];

$pruned = $pruner->prune($messages, targetTokens: 1000, strategy: 'balanced');

echo "Original messages: " . count($messages) . "\n";
echo "Pruned messages: " . count($pruned) . "\n";
```

## Cost Management

### Budget Manager

```php
<?php
# filename: src/BudgetManager.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use Anthropic\Contracts\ClientContract;

class BudgetManager
{
    private float $spent = 0.0;
    private array $transactions = [];

    public function __construct(
        private ClientContract $client,
        private TokenCounter $counter,
        private float $budgetUSD,
        private ?string $period = 'monthly'
    ) {}

    public function query(array $request): object
    {
        // Estimate cost before making request
        $estimatedCost = $this->estimateRequestCost($request);

        if ($this->spent + $estimatedCost > $this->budgetUSD) {
            throw new \RuntimeException(
                "Budget exceeded: \${$this->budgetUSD} limit. " .
                "Spent: \${$this->spent}, Estimated: \${$estimatedCost}"
            );
        }

        // Make request
        $response = $this->client->messages()->create($request);

        // Calculate actual cost
        $actualCost = ClaudeModelLimits::calculateCost(
            $request['model'],
            $response->usage->inputTokens,
            $response->usage->outputTokens
        );

        // Track
        $this->spent += $actualCost;
        $this->transactions[] = [
            'timestamp' => time(),
            'model' => $request['model'],
            'input_tokens' => $response->usage->inputTokens,
            'output_tokens' => $response->usage->outputTokens,
            'cost' => $actualCost,
            'estimated_cost' => $estimatedCost,
        ];

        return $response;
    }

    private function estimateRequestCost(array $request): float
    {
        $inputTokens = $this->counter->countRequest($request)['total'];
        $outputTokens = $request['max_tokens'] ?? 1024;

        return ClaudeModelLimits::calculateCost(
            $request['model'],
            $inputTokens,
            $outputTokens
        );
    }

    public function getRemaining(): float
    {
        return max(0, $this->budgetUSD - $this->spent);
    }

    public function getSpent(): float
    {
        return $this->spent;
    }

    public function getUtilization(): float
    {
        return ($this->spent / $this->budgetUSD) * 100;
    }

    public function canAfford(array $request): bool
    {
        $estimatedCost = $this->estimateRequestCost($request);
        return ($this->spent + $estimatedCost) <= $this->budgetUSD;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getSummary(): array
    {
        return [
            'budget' => $this->budgetUSD,
            'spent' => $this->spent,
            'remaining' => $this->getRemaining(),
            'utilization' => round($this->getUtilization(), 2) . '%',
            'transaction_count' => count($this->transactions),
            'average_cost_per_request' => count($this->transactions) > 0
                ? $this->spent / count($this->transactions)
                : 0,
        ];
    }

    public function reset(): void
    {
        $this->spent = 0.0;
        $this->transactions = [];
    }
}

// Usage
$budget = new BudgetManager(
    client: $client,
    counter: new TokenCounter(),
    budgetUSD: 10.00,
    period: 'daily'
);

try {
    $response = $budget->query([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => 'Explain PHP namespaces'
        ]]
    ]);

    echo $response->content[0]->text . "\n\n";

    $summary = $budget->getSummary();
    echo "Budget Summary:\n";
    print_r($summary);

} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Cost Optimizer

```php
<?php
# filename: src/CostOptimizer.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class CostOptimizer
{
    /**
     * Choose the most cost-effective model for a task
     */
    public function chooseModel(
        string $task,
        int $estimatedInputTokens,
        int $maxOutputTokens,
        ?string $quality = 'balanced'
    ): string {
        $taskLower = strtolower($task);

        // Simple tasks -> Haiku
        if (str_contains($taskLower, 'extract') ||
            str_contains($taskLower, 'classify') ||
            str_contains($taskLower, 'simple') ||
            $estimatedInputTokens < 1000
        ) {
            return 'claude-haiku-4-20250514';
        }

        // Complex reasoning -> Opus
        if ($quality === 'best' ||
            str_contains($taskLower, 'complex') ||
            str_contains($taskLower, 'analyze deeply') ||
            str_contains($taskLower, 'comprehensive')
        ) {
            return 'claude-opus-4-20250514';
        }

        // Default: Sonnet (best value)
        return 'claude-sonnet-4-20250514';
    }

    /**
     * Optimize request to reduce costs
     */
    public function optimizeRequest(array $request): array
    {
        // 1. Trim whitespace
        if (isset($request['system'])) {
            $request['system'] = $this->trimExcessWhitespace($request['system']);
        }

        foreach ($request['messages'] as &$message) {
            if (is_string($message['content'])) {
                $message['content'] = $this->trimExcessWhitespace($message['content']);
            }
        }

        // 2. Reduce max_tokens if possible
        if (isset($request['max_tokens']) && $request['max_tokens'] > 4096) {
            // Most responses don't need 16K tokens
            // Consider reducing unless explicitly needed
        }

        // 3. Use lower temperature for deterministic tasks (faster, same quality)
        if (!isset($request['temperature'])) {
            $request['temperature'] = 0.5;  // Lower = faster
        }

        return $request;
    }

    private function trimExcessWhitespace(string $text): string
    {
        // Remove extra newlines (more than 2 consecutive)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Remove trailing whitespace
        $text = preg_replace('/[ \t]+$/m', '', $text);

        return trim($text);
    }

    /**
     * Calculate potential savings
     */
    public function calculateSavings(
        string $originalModel,
        string $optimizedModel,
        int $inputTokens,
        int $outputTokens
    ): array {
        $originalCost = ClaudeModelLimits::calculateCost(
            $originalModel,
            $inputTokens,
            $outputTokens
        );

        $optimizedCost = ClaudeModelLimits::calculateCost(
            $optimizedModel,
            $inputTokens,
            $outputTokens
        );

        $savings = $originalCost - $optimizedCost;
        $savingsPercent = $originalCost > 0
            ? ($savings / $originalCost) * 100
            : 0;

        return [
            'original_model' => $originalModel,
            'original_cost' => $originalCost,
            'optimized_model' => $optimizedModel,
            'optimized_cost' => $optimizedCost,
            'savings' => $savings,
            'savings_percent' => round($savingsPercent, 2),
        ];
    }
}

// Usage
$optimizer = new CostOptimizer();

// Choose cost-effective model
$model = $optimizer->chooseModel(
    task: 'Extract email addresses from text',
    estimatedInputTokens: 500,
    maxOutputTokens: 100
);

echo "Recommended model: {$model}\n";

// Calculate savings
$savings = $optimizer->calculateSavings(
    originalModel: 'claude-opus-4-20250514',
    optimizedModel: 'claude-haiku-4-20250514',
    inputTokens: 1000,
    outputTokens: 500
);

echo "Potential savings:\n";
print_r($savings);
```

## Production Token Management System

### Complete Token Management Service

```php
<?php
# filename: src/TokenManagementService.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use Anthropic\Contracts\ClientContract;

class TokenManagementService
{
    private TokenCounter $counter;
    private BudgetManager $budget;
    private CostOptimizer $optimizer;
    private array $stats = [];

    public function __construct(
        private ClientContract $client,
        float $dailyBudget = 50.00
    ) {
        $this->counter = new TokenCounter();
        $this->budget = new BudgetManager($client, $this->counter, $dailyBudget);
        $this->optimizer = new CostOptimizer();
    }

    public function query(
        string $task,
        array $messages,
        ?string $system = null,
        ?int $maxTokens = null,
        array $options = []
    ): object {
        // Build request
        $request = [
            'messages' => $messages,
        ];

        if ($system) {
            $request['system'] = $system;
        }

        // Auto-select model if not specified
        $inputTokens = $this->counter->countRequest($request)['total'];
        $outputTokens = $maxTokens ?? 2048;

        $request['model'] = $options['model'] ?? $this->optimizer->chooseModel(
            task: $task,
            estimatedInputTokens: $inputTokens,
            maxOutputTokens: $outputTokens,
            quality: $options['quality'] ?? 'balanced'
        );

        $request['max_tokens'] = $outputTokens;

        // Optimize request
        $request = $this->optimizer->optimizeRequest($request);

        // Check budget
        if (!$this->budget->canAfford($request)) {
            throw new \RuntimeException(
                'Insufficient budget for this request. ' .
                'Remaining: $' . number_format($this->budget->getRemaining(), 4)
            );
        }

        // Execute
        $startTime = microtime(true);
        $response = $this->budget->query($request);
        $duration = microtime(true) - $startTime;

        // Track stats
        $this->trackStats($request, $response, $duration);

        return $response;
    }

    private function trackStats(array $request, object $response, float $duration): void
    {
        $this->stats[] = [
            'timestamp' => time(),
            'model' => $request['model'],
            'input_tokens' => $response->usage->inputTokens,
            'output_tokens' => $response->usage->outputTokens,
            'duration' => $duration,
            'cost' => ClaudeModelLimits::calculateCost(
                $request['model'],
                $response->usage->inputTokens,
                $response->usage->outputTokens
            ),
        ];
    }

    public function getStats(): array
    {
        if (empty($this->stats)) {
            return [];
        }

        return [
            'total_requests' => count($this->stats),
            'total_tokens' => array_sum(array_map(
                fn($s) => $s['input_tokens'] + $s['output_tokens'],
                $this->stats
            )),
            'total_cost' => array_sum(array_column($this->stats, 'cost')),
            'average_duration' => array_sum(array_column($this->stats, 'duration')) / count($this->stats),
            'budget_summary' => $this->budget->getSummary(),
        ];
    }

    public function exportReport(string $filename): void
    {
        $report = [
            'generated_at' => date('Y-m-d H:i:s'),
            'stats' => $this->getStats(),
            'transactions' => $this->budget->getTransactions(),
        ];

        file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT));
    }
}

// Usage
$service = new TokenManagementService($client, dailyBudget: 25.00);

$response = $service->query(
    task: 'Extract data from text',
    messages: [[
        'role' => 'user',
        'content' => 'Extract the email from: Contact us at support@example.com'
    ]],
    maxTokens: 100
);

echo $response->content[0]->text . "\n\n";

$stats = $service->getStats();
echo "Token Management Stats:\n";
print_r($stats);

$service->exportReport('token_report.json');
```

## Exercises

### Exercise 1: Token Budget Dashboard

Build a web dashboard that displays real-time token usage and budget status.

**Requirements:**
- Show current budget utilization
- Display token usage trends
- Alert when approaching budget limits
- Export usage reports

### Exercise 2: Adaptive Context Window

Create a system that automatically adjusts context window usage based on conversation importance.

**Requirements:**
- Identify important vs filler messages
- Summarize or prune strategically
- Maintain conversation coherence
- Maximize context efficiency

### Exercise 3: Cost Prediction Engine

Build a tool that predicts costs before making requests.

**Requirements:**
- Estimate token counts accurately
- Calculate cost ranges (min/max)
- Suggest optimizations
- Compare model costs

<details>
<summary>Solution Hints</summary>

For Exercise 1, create a class that stores usage data in a database and provides endpoints for fetching stats. For Exercise 2, implement a scoring system for message importance and use strategic pruning. For Exercise 3, build on the TokenCounter and add confidence intervals for estimates.

</details>

## Key Takeaways

- ✓ Tokens are chunks of text (~4 chars each), not words
- ✓ Claude has a 200K token context window across all models
- ✓ Count tokens before requests to estimate costs accurately
- ✓ Implement budget management to prevent cost overruns
- ✓ Prune conversation history strategically to stay within limits
- ✓ Choose the right model for each task to optimize costs
- ✓ Track token usage to understand patterns and optimize
- ✓ Use Haiku for simple tasks, Sonnet for most, Opus for complex

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="09"
  label="You've mastered token management and cost optimization!"
/>

---

Continue to [Chapter 10: Error Handling and Rate Limiting](/series/claude-php-developers/chapters/10-error-handling-rate-limiting) to learn about building resilient applications with proper error handling.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 09 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-09)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-09
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/02-model-limits.php
```
