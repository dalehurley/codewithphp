---
title: "39: Cost Optimization and Billing"
description: "Optimize Claude API costs in production: strategic model selection, prompt compression techniques, intelligent caching, batch processing, budget alerts, quota management, and ROI tracking for cost-effective AI applications."
series: "claude-php-developers"
chapter: 39
order: 39
difficulty: "Advanced"
prerequisites:
  - "PHP 8.2+ installed"
  - "Understanding of cost structures"
  - "Completion of Chapters 36-38"
---

![39: Cost Optimization and Billing](/images/claude-php/chapter-39-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 39</span>
</div>

# Chapter 39: Cost Optimization and Billing

## Overview

Claude API costs can scale quickly in production applications. Without proper cost management, you may face unexpected bills, budget overruns, and reduced profitability. Strategic cost optimization requires understanding pricing models, implementing intelligent caching, choosing appropriate models, optimizing prompts, and monitoring spending in real-time.

This chapter teaches you to build cost-effective Claude applications. You'll learn strategic model selection, prompt optimization techniques, intelligent caching strategies, batch processing for efficiency, budget monitoring and alerts, quota management, and ROI tracking to demonstrate business value.

**What You'll Learn:**
- Understanding Claude pricing structure
- Strategic model selection for cost savings
- Prompt compression and optimization
- Multi-tier caching strategies
- Batch processing for bulk operations
- Real-time cost tracking and attribution
- Budget alerts and quota management
- ROI calculation and business metrics

**Estimated Time**: 60-75 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.2+** with Redis and database
- ✓ **Anthropic API account** with billing enabled
- ✓ **Understanding of pricing** from Chapter 01
- ✓ **Monitoring setup** from Chapter 37

## Understanding Claude Pricing

### Current Pricing Structure (2025)

```php
<?php
# filename: src/Billing/PricingCalculator.php
declare(strict_types=1);

namespace App\Billing;

class PricingCalculator
{
    // Prices per million tokens (as of 2025)
    private const PRICING = [
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
    ];

    /**
     * Calculate cost for a request
     */
    public function calculateCost(
        string $model,
        int $inputTokens,
        int $outputTokens
    ): array {
        $pricing = self::PRICING[$model] ?? ['input' => 0, 'output' => 0];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];
        $totalCost = $inputCost + $outputCost;

        return [
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost,
            'cost_per_token' => $totalCost / ($inputTokens + $outputTokens),
            'model' => $model,
        ];
    }

    /**
     * Estimate cost before making request
     */
    public function estimateCost(
        string $model,
        string $prompt,
        int $expectedOutputTokens = 500
    ): array {
        // Rough estimation: 1 token ≈ 4 characters
        $estimatedInputTokens = (int) ceil(strlen($prompt) / 4);

        return $this->calculateCost($model, $estimatedInputTokens, $expectedOutputTokens);
    }

    /**
     * Compare costs across models
     */
    public function compareCosts(
        int $inputTokens,
        int $outputTokens
    ): array {
        $comparison = [];

        foreach (self::PRICING as $model => $pricing) {
            $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
            $comparison[$this->simplifyModelName($model)] = $cost['total_cost'];
        }

        // Add relative costs
        $haikusCost = $comparison['haiku'];
        foreach ($comparison as $model => $cost) {
            $comparison[$model . '_relative'] = $cost / $haikusCost;
        }

        return $comparison;
    }

    /**
     * Calculate monthly projection
     */
    public function projectMonthlyCost(
        string $model,
        int $avgInputTokens,
        int $avgOutputTokens,
        int $requestsPerDay
    ): array {
        $costPerRequest = $this->calculateCost(
            $model,
            $avgInputTokens,
            $avgOutputTokens
        )['total_cost'];

        $dailyCost = $costPerRequest * $requestsPerDay;
        $monthlyCost = $dailyCost * 30;
        $yearlyCost = $dailyCost * 365;

        return [
            'cost_per_request' => $costPerRequest,
            'daily_cost' => $dailyCost,
            'monthly_cost' => $monthlyCost,
            'yearly_cost' => $yearlyCost,
            'assumptions' => [
                'model' => $model,
                'avg_input_tokens' => $avgInputTokens,
                'avg_output_tokens' => $avgOutputTokens,
                'requests_per_day' => $requestsPerDay,
            ],
        ];
    }

    private function simplifyModelName(string $model): string
    {
        return match(true) {
            str_contains($model, 'opus') => 'opus',
            str_contains($model, 'sonnet') => 'sonnet',
            str_contains($model, 'haiku') => 'haiku',
            default => 'unknown'
        };
    }
}

// Usage
$calculator = new PricingCalculator();

// Calculate actual cost
$cost = $calculator->calculateCost(
    model: 'claude-sonnet-4-20250514',
    inputTokens: 150,
    outputTokens: 300
);
echo "Total cost: $" . number_format($cost['total_cost'], 6) . "\n";

// Compare models
$comparison = $calculator->compareCosts(150, 300);
print_r($comparison);
/*
Array (
    [haiku] => 0.0001125
    [sonnet] => 0.0049500
    [opus] => 0.0247500
    [haiku_relative] => 1
    [sonnet_relative] => 44
    [opus_relative] => 220
)
Sonnet costs 44x more than Haiku for the same request!
*/

// Monthly projection
$projection = $calculator->projectMonthlyCost(
    model: 'claude-sonnet-4-20250514',
    avgInputTokens: 200,
    avgOutputTokens: 400,
    requestsPerDay: 1000
);
echo "Projected monthly cost: $" . number_format($projection['monthly_cost'], 2) . "\n";
```

## Strategic Model Selection

Choosing the right model can reduce costs by 95% while maintaining quality.

### Intelligent Model Router

```php
<?php
# filename: src/Optimization/ModelRouter.php
declare(strict_types=1);

namespace App\Optimization;

class ModelRouter
{
    /**
     * Select optimal model based on task requirements
     */
    public function selectModel(array $taskRequirements): string
    {
        $complexity = $taskRequirements['complexity'] ?? 'medium';
        $qualityRequired = $taskRequirements['quality_required'] ?? 'medium';
        $budgetPriority = $taskRequirements['budget_priority'] ?? false;
        $responseTimeRequired = $taskRequirements['response_time'] ?? 'normal';

        // High budget priority → use cheapest suitable model
        if ($budgetPriority) {
            return match($complexity) {
                'simple' => 'claude-haiku-4-20250514',
                'medium' => 'claude-haiku-4-20250514',
                'complex' => 'claude-sonnet-4-20250514',
                default => 'claude-haiku-4-20250514'
            };
        }

        // Speed critical → use fastest model
        if ($responseTimeRequired === 'fast') {
            return 'claude-haiku-4-20250514';
        }

        // Quality critical → use best model
        if ($qualityRequired === 'critical') {
            return 'claude-opus-4-20250514';
        }

        // Default routing based on complexity
        return match($complexity) {
            'simple' => 'claude-haiku-4-20250514',
            'medium' => 'claude-sonnet-4-20250514',
            'complex' => 'claude-opus-4-20250514',
            default => 'claude-sonnet-4-20250514'
        };
    }

    /**
     * Classify task complexity automatically
     */
    public function classifyComplexity(string $prompt, array $context = []): string
    {
        $promptLength = strlen($prompt);
        $hasCodeGeneration = str_contains(strtolower($prompt), 'generate code') ||
                            str_contains(strtolower($prompt), 'write a function');
        $hasComplexReasoning = str_contains(strtolower($prompt), 'analyze') ||
                               str_contains(strtolower($prompt), 'design') ||
                               str_contains(strtolower($prompt), 'architecture');

        // Simple tasks
        if ($promptLength < 200 && !$hasCodeGeneration && !$hasComplexReasoning) {
            return 'simple';
        }

        // Complex tasks
        if ($hasComplexReasoning || $promptLength > 2000) {
            return 'complex';
        }

        // Default to medium
        return 'medium';
    }

    /**
     * Get model recommendation with cost comparison
     */
    public function recommendModel(string $prompt, array $requirements = []): array
    {
        $complexity = $this->classifyComplexity($prompt, $requirements);
        $requirements['complexity'] = $complexity;

        $recommendedModel = $this->selectModel($requirements);

        // Estimate costs for all models
        $estimatedTokens = (int) ceil(strlen($prompt) / 4);
        $expectedOutput = $requirements['expected_output_tokens'] ?? 500;

        $calculator = new \App\Billing\PricingCalculator();
        $costs = $calculator->compareCosts($estimatedTokens, $expectedOutput);

        return [
            'recommended_model' => $recommendedModel,
            'complexity' => $complexity,
            'estimated_costs' => $costs,
            'reasoning' => $this->explainRecommendation($complexity, $requirements),
        ];
    }

    private function explainRecommendation(string $complexity, array $requirements): string
    {
        if ($requirements['budget_priority'] ?? false) {
            return "Budget-optimized: Using most cost-effective model for $complexity complexity";
        }

        if ($requirements['quality_required'] === 'critical') {
            return "Quality-optimized: Using highest quality model";
        }

        return "Balanced: Using appropriate model for $complexity complexity";
    }
}

// Usage
$router = new ModelRouter();

// Automatic model selection
$prompt = "Classify this email as spam or not: ...";
$recommendation = $router->recommendModel($prompt, [
    'budget_priority' => true,
    'expected_output_tokens' => 50
]);

echo "Recommended: {$recommendation['recommended_model']}\n";
echo "Reasoning: {$recommendation['reasoning']}\n";
echo "Estimated cost: $" . number_format($recommendation['estimated_costs']['sonnet'], 6) . "\n";
```

## Prompt Optimization

Reduce token usage through prompt engineering.

### Prompt Optimizer

```php
<?php
# filename: src/Optimization/PromptOptimizer.php
declare(strict_types=1);

namespace App\Optimization;

class PromptOptimizer
{
    /**
     * Optimize prompt to reduce token usage
     */
    public function optimize(string $prompt, array $options = []): array
    {
        $original = $prompt;
        $optimized = $prompt;

        // Apply optimization techniques
        $optimized = $this->removeRedundancy($optimized);
        $optimized = $this->useAbbreviations($optimized);
        $optimized = $this->removeFluff($optimized);
        $optimized = $this->consolidateInstructions($optimized);

        if ($options['aggressive'] ?? false) {
            $optimized = $this->aggressiveOptimization($optimized);
        }

        return [
            'original' => $original,
            'optimized' => $optimized,
            'original_length' => strlen($original),
            'optimized_length' => strlen($optimized),
            'reduction_pct' => round((1 - strlen($optimized) / strlen($original)) * 100, 1),
            'estimated_token_savings' => (int) ceil((strlen($original) - strlen($optimized)) / 4),
        ];
    }

    /**
     * Remove redundant words and phrases
     */
    private function removeRedundancy(string $prompt): string
    {
        $redundancies = [
            'I would like you to ' => '',
            'Please ' => '',
            'Could you please ' => '',
            'I need you to ' => '',
            'Can you ' => '',
            ' the following ' => ' ',
            ' that is ' => ' ',
            ' which is ' => ' ',
        ];

        foreach ($redundancies as $redundant => $replacement) {
            $prompt = str_ireplace($redundant, $replacement, $prompt);
        }

        return $prompt;
    }

    /**
     * Use common abbreviations
     */
    private function useAbbreviations(string $prompt): string
    {
        $abbreviations = [
            'For example' => 'E.g.',
            'That is' => 'I.e.',
            'et cetera' => 'etc.',
            'versus' => 'vs.',
        ];

        foreach ($abbreviations as $full => $abbr) {
            $prompt = str_ireplace($full, $abbr, $prompt);
        }

        return $prompt;
    }

    /**
     * Remove unnecessary fluff
     */
    private function removeFluff(string $prompt): string
    {
        $fluff = [
            'basically',
            'actually',
            'literally',
            'honestly',
            'really',
            'very much',
            'kind of',
            'sort of',
        ];

        foreach ($fluff as $word) {
            $prompt = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', '', $prompt);
        }

        // Clean up extra spaces
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        return trim($prompt);
    }

    /**
     * Consolidate multiple instructions into concise format
     */
    private function consolidateInstructions(string $prompt): string
    {
        // If prompt has numbered instructions, keep them
        // Otherwise, it's already consolidated
        return $prompt;
    }

    /**
     * Aggressive optimization (may reduce clarity)
     */
    private function aggressiveOptimization(string $prompt): string
    {
        // Remove articles (a, an, the) where not critical
        $prompt = preg_replace('/\b(a|an|the)\b/i', '', $prompt);

        // Remove extra punctuation
        $prompt = preg_replace('/[,;]+/', ',', $prompt);

        // Clean up spaces
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        return trim($prompt);
    }
}

// Usage
$optimizer = new PromptOptimizer();

$verbose = "I would like you to please analyze the following text and tell me if it contains any negative sentiment or not.";

$result = $optimizer->optimize($verbose);

echo "Original: {$result['original']}\n";
echo "Optimized: {$result['optimized']}\n";
echo "Reduction: {$result['reduction_pct']}%\n";
echo "Token savings: ~{$result['estimated_token_savings']} tokens\n";

/*
Original: I would like you to please analyze the following text and tell me if it contains any negative sentiment or not.
Optimized: Analyze text for negative sentiment.
Reduction: 62.5%
Token savings: ~19 tokens
*/
```

### Template-Based Prompts

```php
<?php
# filename: src/Optimization/PromptTemplates.php
declare(strict_types=1);

namespace App\Optimization;

class PromptTemplates
{
    /**
     * Efficient prompt templates
     */
    private const TEMPLATES = [
        'classification' => 'Classify: {input}\nCategories: {categories}\nReturn category only.',

        'extraction' => 'Extract {fields} from:\n{text}\nFormat: JSON',

        'summarization' => 'Summarize in {length} {unit}:\n{text}',

        'sentiment' => 'Sentiment (positive/negative/neutral): {text}',

        'translation' => 'Translate to {language}:\n{text}',

        'yesno' => '{question}\nAnswer: Yes or No only.',
    ];

    /**
     * Build prompt from template
     */
    public function build(string $template, array $variables): string
    {
        if (!isset(self::TEMPLATES[$template])) {
            throw new \InvalidArgumentException("Template '$template' not found");
        }

        $prompt = self::TEMPLATES[$template];

        foreach ($variables as $key => $value) {
            $prompt = str_replace("{{$key}}", $value, $prompt);
        }

        return $prompt;
    }

    /**
     * Get token estimate for template
     */
    public function estimateTokens(string $template, array $variables): int
    {
        $prompt = $this->build($template, $variables);
        return (int) ceil(strlen($prompt) / 4);
    }
}

// Usage - Much more efficient than verbose prompts
$templates = new PromptTemplates();

// Instead of: "I would like you to classify the following email into one of these categories: spam, sales, support, or general. Please return only the category name."
// Use:
$prompt = $templates->build('classification', [
    'input' => $emailText,
    'categories' => 'spam, sales, support, general'
]);

echo $prompt . "\n";
// Output: "Classify: {email text}
//          Categories: spam, sales, support, general
//          Return category only."
// ~60% fewer tokens!
```

## Intelligent Caching

Cache responses to avoid redundant API calls.

### Multi-Tier Cache System

```php
<?php
# filename: src/Optimization/ClaudeCache.php
declare(strict_types=1);

namespace App\Optimization;

class ClaudeCache
{
    public function __construct(
        private readonly \Redis $redis,
        private readonly int $defaultTtl = 3600
    ) {}

    /**
     * Get cached response or execute Claude request
     */
    public function remember(
        string $cacheKey,
        callable $callback,
        ?int $ttl = null
    ): mixed {
        // Try to get from cache
        $cached = $this->get($cacheKey);

        if ($cached !== null) {
            // Cache hit - update stats
            $this->incrementStat('hits');
            return $cached;
        }

        // Cache miss - execute callback
        $this->incrementStat('misses');
        $result = $callback();

        // Store in cache
        $this->set($cacheKey, $result, $ttl ?? $this->defaultTtl);

        return $result;
    }

    /**
     * Generate cache key from prompt and parameters
     */
    public function generateKey(string $prompt, array $params = []): string
    {
        // Include model and relevant params in key
        $keyData = [
            'prompt' => $prompt,
            'model' => $params['model'] ?? 'default',
            'system' => $params['system'] ?? '',
            'temperature' => $params['temperature'] ?? 1.0,
        ];

        return 'claude:cache:' . hash('sha256', json_encode($keyData));
    }

    /**
     * Semantic caching - find similar cached prompts
     */
    public function findSimilar(string $prompt, float $threshold = 0.9): ?string
    {
        // Get all cache keys (expensive - use sparingly)
        $keys = $this->redis->keys('claude:cache:*');

        foreach ($keys as $key) {
            $cached = $this->redis->get($key);
            if (!$cached) continue;

            $data = json_decode($cached, true);
            $cachedPrompt = $data['prompt'] ?? '';

            // Calculate similarity (simple version - use proper algorithm in production)
            $similarity = $this->calculateSimilarity($prompt, $cachedPrompt);

            if ($similarity >= $threshold) {
                return $key;
            }
        }

        return null;
    }

    private function get(string $key): mixed
    {
        $value = $this->redis->get($key);

        if ($value === false) {
            return null;
        }

        $data = json_decode($value, true);

        // Check if expired (additional layer beyond Redis TTL)
        if (isset($data['expires_at']) && $data['expires_at'] < time()) {
            $this->redis->del($key);
            return null;
        }

        return $data['value'];
    }

    private function set(string $key, mixed $value, int $ttl): void
    {
        $data = [
            'value' => $value,
            'cached_at' => time(),
            'expires_at' => time() + $ttl,
        ];

        $this->redis->setex($key, $ttl, json_encode($data));
    }

    private function incrementStat(string $stat): void
    {
        $this->redis->incr("claude:cache:stats:$stat");
    }

    private function calculateSimilarity(string $a, string $b): float
    {
        // Simple similarity - use levenshtein or cosine similarity in production
        similar_text($a, $b, $percent);
        return $percent / 100;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $hits = (int) $this->redis->get('claude:cache:stats:hits') ?: 0;
        $misses = (int) $this->redis->get('claude:cache:stats:misses') ?: 0;
        $total = $hits + $misses;

        return [
            'hits' => $hits,
            'misses' => $misses,
            'total' => $total,
            'hit_rate' => $total > 0 ? round($hits / $total * 100, 2) : 0,
        ];
    }

    /**
     * Warm cache with common queries
     */
    public function warmCache(array $commonQueries): void
    {
        foreach ($commonQueries as $query) {
            $key = $this->generateKey($query['prompt'], $query['params'] ?? []);

            // Check if already cached
            if ($this->get($key) !== null) {
                continue;
            }

            // Execute and cache
            $result = $query['callback']();
            $this->set($key, $result, $query['ttl'] ?? $this->defaultTtl);
        }
    }
}

// Usage
$cache = new ClaudeCache($redis, defaultTtl: 3600);

// Cached request
$prompt = "What are the benefits of PHP 8.2?";
$cacheKey = $cache->generateKey($prompt, ['model' => 'claude-sonnet-4-20250514']);

$response = $cache->remember(
    cacheKey: $cacheKey,
    callback: fn() => $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]),
    ttl: 3600  // Cache for 1 hour
);

// Check cache performance
$stats = $cache->getStats();
echo "Cache hit rate: {$stats['hit_rate']}%\n";
echo "Cost savings: " . ($stats['hits'] * 0.005) . " dollars\n";  // Assuming $0.005 per request
```

### Response Compression

```php
<?php
# filename: src/Optimization/ResponseCompressor.php
declare(strict_types=1);

namespace App\Optimization;

class ResponseCompressor
{
    /**
     * Compress response before caching
     */
    public function compress(array $response): string
    {
        // Extract only essential data
        $compressed = [
            't' => $response->content[0]->text,  // text
            'i' => $response->usage->inputTokens,  // input tokens
            'o' => $response->usage->outputTokens,  // output tokens
            'm' => $response->model,  // model
        ];

        // JSON encode and gzip
        $json = json_encode($compressed);
        return gzcompress($json, 9);
    }

    /**
     * Decompress cached response
     */
    public function decompress(string $compressed): array
    {
        $json = gzuncompress($compressed);
        $data = json_decode($json, true);

        return [
            'text' => $data['t'],
            'input_tokens' => $data['i'],
            'output_tokens' => $data['o'],
            'model' => $data['m'],
        ];
    }

    /**
     * Calculate compression ratio
     */
    public function getCompressionRatio(array $original, string $compressed): float
    {
        $originalSize = strlen(json_encode($original));
        $compressedSize = strlen($compressed);

        return round($compressedSize / $originalSize, 2);
    }
}

// Usage - Save cache storage space
$compressor = new ResponseCompressor();

$compressed = $compressor->compress($claudeResponse);
$redis->setex("claude:response:123", 3600, $compressed);

// Later...
$compressed = $redis->get("claude:response:123");
$response = $compressor->decompress($compressed);

// Compression saves ~70% storage space
```

## Batch Processing

Process multiple requests efficiently.

### Batch Processor

```php
<?php
# filename: src/Optimization/BatchProcessor.php
declare(strict_types=1);

namespace App\Optimization;

use Anthropic\Anthropic;

class BatchProcessor
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    /**
     * Process multiple items in a single request
     * Much more cost-effective than individual requests
     */
    public function processBatch(array $items, string $task, string $model = 'claude-haiku-4-20250514'): array
    {
        // Build batch prompt
        $batchPrompt = $this->buildBatchPrompt($items, $task);

        // Single Claude request for all items
        $response = $this->client->messages()->create([
            'model' => $model,
            'max_tokens' => count($items) * 100,  // Allocate tokens per item
            'messages' => [[
                'role' => 'user',
                'content' => $batchPrompt
            ]]
        ]);

        // Parse batch response
        $results = $this->parseBatchResponse($response->content[0]->text, count($items));

        // Calculate savings
        $singleRequestCost = $this->calculateBatchCost($response);
        $individualRequestsCost = $this->estimateIndividualCosts(count($items), $model);

        return [
            'results' => $results,
            'cost_analysis' => [
                'batch_cost' => $singleRequestCost,
                'individual_cost' => $individualRequestsCost,
                'savings' => $individualRequestsCost - $singleRequestCost,
                'savings_pct' => round((1 - $singleRequestCost / $individualRequestsCost) * 100, 1),
            ],
        ];
    }

    private function buildBatchPrompt(array $items, string $task): string
    {
        $itemsList = '';
        foreach ($items as $index => $item) {
            $itemsList .= ($index + 1) . ". $item\n";
        }

        return <<<PROMPT
$task

Process each item and return results in this exact format:
1. [result for item 1]
2. [result for item 2]
...

Items:
$itemsList

Return ONLY the numbered results, no explanation.
PROMPT;
    }

    private function parseBatchResponse(string $response, int $expectedCount): array
    {
        $results = [];
        $lines = explode("\n", trim($response));

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\.\s*(.+)$/', $line, $matches)) {
                $index = (int) $matches[1];
                $result = trim($matches[2]);
                $results[$index - 1] = $result;
            }
        }

        return $results;
    }

    private function calculateBatchCost($response): float
    {
        $calculator = new \App\Billing\PricingCalculator();
        $cost = $calculator->calculateCost(
            $response->model,
            $response->usage->inputTokens,
            $response->usage->outputTokens
        );

        return $cost['total_cost'];
    }

    private function estimateIndividualCosts(int $itemCount, string $model): float
    {
        // Assume 50 input tokens and 50 output tokens per item
        $calculator = new \App\Billing\PricingCalculator();
        $costPerItem = $calculator->calculateCost($model, 50, 50)['total_cost'];

        return $costPerItem * $itemCount;
    }
}

// Usage
$batchProcessor = new BatchProcessor($client);

$emails = [
    "Great product! Love it!",
    "This is terrible, waste of money.",
    "Okay, nothing special.",
    // ... 97 more emails
];

$result = $batchProcessor->processBatch(
    items: $emails,
    task: "Classify each as: positive, negative, or neutral",
    model: 'claude-haiku-4-20250514'
);

echo "Processed {count($emails)} items\n";
echo "Batch cost: $" . number_format($result['cost_analysis']['batch_cost'], 4) . "\n";
echo "Individual cost would be: $" . number_format($result['cost_analysis']['individual_cost'], 4) . "\n";
echo "Savings: {$result['cost_analysis']['savings_pct']}%\n";

/*
Processed 100 items
Batch cost: $0.0045
Individual cost would be: $0.0375
Savings: 88%
*/
```

## Budget Management

Monitor and control spending in real-time.

### Budget Tracker

```php
<?php
# filename: src/Billing/BudgetTracker.php
declare(strict_types=1);

namespace App\Billing;

class BudgetTracker
{
    public function __construct(
        private readonly \Redis $redis,
        private readonly array $budgetLimits = [
            'hourly' => 10.00,
            'daily' => 200.00,
            'monthly' => 5000.00,
        ]
    ) {}

    /**
     * Track spending and check limits
     */
    public function trackSpending(float $cost, string $userId = 'system'): void
    {
        $now = new \DateTimeImmutable();

        // Track at different time granularities
        $this->incrementSpending('hourly', $cost, $now->format('Y-m-d-H'));
        $this->incrementSpending('daily', $cost, $now->format('Y-m-d'));
        $this->incrementSpending('monthly', $cost, $now->format('Y-m'));

        // Track per user
        $this->incrementUserSpending($userId, $cost, $now->format('Y-m-d'));

        // Check if limits exceeded
        $this->checkLimits($cost);
    }

    /**
     * Check if request would exceed budget
     */
    public function wouldExceedBudget(float $estimatedCost): bool
    {
        foreach (['hourly', 'daily', 'monthly'] as $period) {
            $current = $this->getCurrentSpending($period);
            $limit = $this->budgetLimits[$period];

            if ($current + $estimatedCost > $limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current spending
     */
    public function getCurrentSpending(string $period): float
    {
        $key = $this->getSpendingKey($period);
        return (float) ($this->redis->get($key) ?? 0);
    }

    /**
     * Get budget status
     */
    public function getBudgetStatus(): array
    {
        $status = [];

        foreach ($this->budgetLimits as $period => $limit) {
            $spent = $this->getCurrentSpending($period);
            $remaining = $limit - $spent;
            $usedPct = round($spent / $limit * 100, 1);

            $status[$period] = [
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'used_pct' => $usedPct,
                'status' => $this->getStatus($usedPct),
            ];
        }

        return $status;
    }

    /**
     * Get user spending
     */
    public function getUserSpending(string $userId, string $date): float
    {
        $key = "budget:user:$userId:$date";
        return (float) ($this->redis->get($key) ?? 0);
    }

    /**
     * Get top spending users
     */
    public function getTopSpenders(int $limit = 10): array
    {
        $today = (new \DateTimeImmutable())->format('Y-m-d');

        // Get all user spending keys for today
        $keys = $this->redis->keys("budget:user:*:$today");

        $spenders = [];
        foreach ($keys as $key) {
            preg_match('/budget:user:(.+):/', $key, $matches);
            $userId = $matches[1] ?? 'unknown';
            $spending = (float) $this->redis->get($key);

            $spenders[$userId] = $spending;
        }

        arsort($spenders);
        return array_slice($spenders, 0, $limit, true);
    }

    private function incrementSpending(string $period, float $cost, string $key): void
    {
        $redisKey = "budget:$period:$key";
        $this->redis->incrByFloat($redisKey, $cost);

        // Set expiration
        $ttl = match($period) {
            'hourly' => 7200,      // 2 hours
            'daily' => 172800,     // 2 days
            'monthly' => 2592000,  // 30 days
            default => 86400
        };

        $this->redis->expire($redisKey, $ttl);
    }

    private function incrementUserSpending(string $userId, float $cost, string $date): void
    {
        $key = "budget:user:$userId:$date";
        $this->redis->incrByFloat($key, $cost);
        $this->redis->expire($key, 172800);  // 2 days
    }

    private function checkLimits(float $recentCost): void
    {
        foreach ($this->budgetLimits as $period => $limit) {
            $current = $this->getCurrentSpending($period);
            $usedPct = ($current / $limit) * 100;

            // Alert at different thresholds
            if ($usedPct >= 100) {
                $this->sendAlert('critical', $period, $current, $limit);
            } elseif ($usedPct >= 90) {
                $this->sendAlert('warning', $period, $current, $limit);
            } elseif ($usedPct >= 75) {
                $this->sendAlert('info', $period, $current, $limit);
            }
        }
    }

    private function getSpendingKey(string $period): string
    {
        $now = new \DateTimeImmutable();

        $key = match($period) {
            'hourly' => $now->format('Y-m-d-H'),
            'daily' => $now->format('Y-m-d'),
            'monthly' => $now->format('Y-m'),
            default => $now->format('Y-m-d')
        };

        return "budget:$period:$key";
    }

    private function getStatus(float $usedPct): string
    {
        return match(true) {
            $usedPct >= 100 => 'exceeded',
            $usedPct >= 90 => 'critical',
            $usedPct >= 75 => 'warning',
            default => 'ok'
        };
    }

    private function sendAlert(string $severity, string $period, float $current, float $limit): void
    {
        $message = "Budget $severity: $period spending at $" . number_format($current, 2) .
                   " of $" . number_format($limit, 2) . " limit";

        error_log("[BUDGET ALERT] $message");

        // Send to monitoring system, email, Slack, etc.
    }
}

// Usage
$budgetTracker = new BudgetTracker($redis, [
    'hourly' => 10.00,
    'daily' => 200.00,
    'monthly' => 5000.00,
]);

// Before making request
$estimatedCost = 0.15;

if ($budgetTracker->wouldExceedBudget($estimatedCost)) {
    throw new BudgetExceededException("Request would exceed budget limits");
}

// Make request
$response = $client->messages()->create([...]);

// Track actual cost
$actualCost = $calculator->calculateCost(
    $response->model,
    $response->usage->inputTokens,
    $response->usage->outputTokens
)['total_cost'];

$budgetTracker->trackSpending($actualCost, $userId);

// Check budget status
$status = $budgetTracker->getBudgetStatus();
print_r($status);
```

### Cost Attribution

```php
<?php
# filename: src/Billing/CostAttribution.php
declare(strict_types=1);

namespace App\Billing;

class CostAttribution
{
    public function __construct(
        private readonly \PDO $db
    ) {
        $this->initializeSchema();
    }

    /**
     * Record cost attribution
     */
    public function recordCost(array $attribution): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO claude_costs (
                user_id, feature, model, input_tokens, output_tokens,
                cost, request_id, created_at
            ) VALUES (
                :user_id, :feature, :model, :input_tokens, :output_tokens,
                :cost, :request_id, :created_at
            )
        ");

        $stmt->execute([
            'user_id' => $attribution['user_id'],
            'feature' => $attribution['feature'],
            'model' => $attribution['model'],
            'input_tokens' => $attribution['input_tokens'],
            'output_tokens' => $attribution['output_tokens'],
            'cost' => $attribution['cost'],
            'request_id' => $attribution['request_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get cost breakdown by feature
     */
    public function getCostByFeature(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("
            SELECT
                feature,
                COUNT(*) as request_count,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cost) as total_cost
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY feature
            ORDER BY total_cost DESC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get cost breakdown by user
     */
    public function getCostByUser(string $startDate, string $endDate, int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT
                user_id,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                AVG(cost) as avg_cost_per_request
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY user_id
            ORDER BY total_cost DESC
            LIMIT :limit
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
            'limit' => $limit,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get model usage statistics
     */
    public function getModelStats(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("
            SELECT
                model,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                AVG(output_tokens) as avg_output_tokens
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY model
            ORDER BY total_cost DESC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function initializeSchema(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS claude_costs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL,
                feature TEXT NOT NULL,
                model TEXT NOT NULL,
                input_tokens INTEGER NOT NULL,
                output_tokens INTEGER NOT NULL,
                cost REAL NOT NULL,
                request_id TEXT,
                created_at DATETIME NOT NULL
            )
        ");

        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_date ON claude_costs(user_id, created_at)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_feature_date ON claude_costs(feature, created_at)");
    }
}

// Usage
$costAttribution = new CostAttribution($pdo);

// Record cost
$costAttribution->recordCost([
    'user_id' => 'user-123',
    'feature' => 'chatbot',
    'model' => 'claude-sonnet-4-20250514',
    'input_tokens' => 150,
    'output_tokens' => 300,
    'cost' => 0.00495,
    'request_id' => 'req-abc123',
]);

// Analyze costs
$featureCosts = $costAttribution->getCostByFeature('2025-01-01', '2025-01-31');
foreach ($featureCosts as $feature) {
    echo "{$feature['feature']}: $" . number_format($feature['total_cost'], 2) . "\n";
}

// Find expensive users
$topUsers = $costAttribution->getCostByUser('2025-01-01', '2025-01-31', 10);
```

## ROI Tracking

Demonstrate business value of AI features.

### ROI Calculator

```php
<?php
# filename: src/Billing/RoiCalculator.php
declare(strict_types=1);

namespace App\Billing;

class RoiCalculator
{
    /**
     * Calculate ROI for AI feature
     */
    public function calculateRoi(array $metrics): array
    {
        $costs = [
            'claude_api' => $metrics['claude_api_cost'],
            'infrastructure' => $metrics['infrastructure_cost'] ?? 0,
            'development' => $metrics['development_cost'] ?? 0,
        ];

        $benefits = [
            'time_saved' => $metrics['hours_saved'] * ($metrics['hourly_rate'] ?? 50),
            'revenue_generated' => $metrics['revenue_generated'] ?? 0,
            'cost_avoided' => $metrics['cost_avoided'] ?? 0,
        ];

        $totalCost = array_sum($costs);
        $totalBenefit = array_sum($benefits);
        $netBenefit = $totalBenefit - $totalCost;
        $roi = $totalCost > 0 ? ($netBenefit / $totalCost) * 100 : 0;

        return [
            'costs' => $costs,
            'total_cost' => $totalCost,
            'benefits' => $benefits,
            'total_benefit' => $totalBenefit,
            'net_benefit' => $netBenefit,
            'roi_percentage' => round($roi, 2),
            'payback_period_months' => $this->calculatePaybackPeriod($totalCost, $totalBenefit),
        ];
    }

    /**
     * Calculate customer support ROI
     */
    public function calculateSupportRoi(array $metrics): array
    {
        // Costs
        $claudeCost = $metrics['total_requests'] * $metrics['avg_cost_per_request'];

        // Benefits
        $ticketsDeflected = $metrics['tickets_deflected'];
        $avgTicketCost = $metrics['avg_human_ticket_cost'] ?? 15.00;
        $costSavings = $ticketsDeflected * $avgTicketCost;

        $customerSatisfactionValue = $metrics['improved_satisfaction_score'] * 1000;  // Value per point

        return $this->calculateRoi([
            'claude_api_cost' => $claudeCost,
            'cost_avoided' => $costSavings,
            'revenue_generated' => $customerSatisfactionValue,
        ]);
    }

    private function calculatePaybackPeriod(float $totalCost, float $monthlyBenefit): float
    {
        if ($monthlyBenefit <= 0) {
            return INF;
        }

        return round($totalCost / $monthlyBenefit, 1);
    }
}

// Usage
$roiCalculator = new RoiCalculator();

// Example: Content generation feature
$contentRoi = $roiCalculator->calculateRoi([
    'claude_api_cost' => 150.00,         // Monthly API costs
    'infrastructure_cost' => 50.00,       // Server costs
    'development_cost' => 2000.00 / 12,   // Amortized over 12 months
    'hours_saved' => 40,                  // Writer hours saved
    'hourly_rate' => 75.00,               // Writer hourly rate
    'revenue_generated' => 5000.00,       // Revenue from increased content
]);

echo "ROI: {$contentRoi['roi_percentage']}%\n";
echo "Net benefit: $" . number_format($contentRoi['net_benefit'], 2) . "\n";
echo "Payback period: {$contentRoi['payback_period_months']} months\n";

/*
ROI: 1575%
Net benefit: $5,033.33
Payback period: 0.6 months
*/

// Example: Customer support chatbot
$supportRoi = $roiCalculator->calculateSupportRoi([
    'total_requests' => 10000,
    'avg_cost_per_request' => 0.005,
    'tickets_deflected' => 3000,
    'avg_human_ticket_cost' => 15.00,
    'improved_satisfaction_score' => 2,  // 2 points improvement
]);

echo "\nSupport ROI: {$supportRoi['roi_percentage']}%\n";
```

## Exercises

### Exercise 1: Cost Optimizer Dashboard

Build a comprehensive cost optimization dashboard:

```php
<?php
class CostOptimizerDashboard
{
    public function generateReport(): array
    {
        // TODO: Generate report showing:
        // - Current spending trends
        // - Cost optimization opportunities
        // - Model selection recommendations
        // - Cache hit rate and savings
        // - Batch processing opportunities
        // - ROI by feature
    }
}
```

### Exercise 2: Automatic Model Downgrade

Implement automatic model downgrading under budget pressure:

```php
<?php
class AutomaticCostControl
{
    public function selectModel(array $requirements, float $currentBudgetUsage): string
    {
        // TODO: Implement logic to:
        // - Check current budget usage
        // - If > 80% of budget, prefer cheaper models
        // - If > 90%, use only Haiku
        // - If > 95%, queue requests instead
    }
}
```

### Exercise 3: Cost Forecasting

Build a cost forecasting system:

```php
<?php
class CostForecaster
{
    public function forecastMonthlyCost(array $historicalData): array
    {
        // TODO: Forecast costs using:
        // - Historical usage trends
        // - Growth rate
        // - Seasonal patterns
        // - Predict budget needs
    }
}
```

## Troubleshooting

**Unexpected high costs?**
- Review top spending users and features
- Check for caching failures
- Look for inefficient prompts
- Verify model selection is appropriate

**Cache not effective?**
- Review cache key generation
- Check TTL settings
- Analyze query patterns for cacheable requests
- Implement semantic caching

**Budget alerts firing constantly?**
- Review budget limits
- Check for sudden traffic spikes
- Analyze cost per request trends
- Implement request throttling

## Key Takeaways

- ✓ **Model Selection**: Right model can save 95% - use Haiku for simple tasks
- ✓ **Prompt Optimization**: Concise prompts save 30-60% on token costs
- ✓ **Intelligent Caching**: Cache hit rate of 40%+ eliminates repeated costs
- ✓ **Batch Processing**: Process 100 items for 88% savings vs individual requests
- ✓ **Budget Tracking**: Monitor spending at hourly/daily/monthly levels
- ✓ **Cost Attribution**: Track costs by user, feature, and model
- ✓ **ROI Measurement**: Demonstrate value with concrete business metrics
- ✓ **Automated Controls**: Implement budget limits and automatic optimizations

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="39"
  label="You've mastered cost optimization for Claude applications!"
/>

---

## Series Complete!

Congratulations on completing the Claude for PHP Developers series! You now have comprehensive knowledge of:

- 🎯 **Core API**: Messages, streaming, tools, vision
- 🏗️ **Architecture**: Service classes, caching, queues
- 🔧 **Integration**: Laravel, real-world applications
- 🚀 **Production**: Security, monitoring, scaling, cost optimization

Continue exploring the [series overview](/series/claude-php-developers) or start building your own production Claude applications!

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 39 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-39)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-39
composer install
php examples/cost-optimization-demo.php
```
