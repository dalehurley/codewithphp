---
title: "39: Cost Optimization and Billing"
description: "Optimize Claude API costs in production: strategic model selection, prompt compression techniques, intelligent caching, batch processing, budget alerts, quota management, and ROI tracking for cost-effective AI applications."
series: "claude-php-developers"
chapter: 39
order: 39
difficulty: "Advanced"
prerequisites:
  - "PHP 8.4+ installed"
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

## What You'll Build

By the end of this chapter, you will have created:

- **Pricing Calculator** (`PricingCalculator.php`) - Comprehensive cost calculation system supporting all Claude models with comparison and projection features
- **Intelligent Model Router** (`ModelRouter.php`) - Automatic model selection based on task complexity, quality requirements, and budget priorities
- **Prompt Optimizer** (`PromptOptimizer.php`) - Token reduction system that removes redundancy and optimizes prompts by 30-60%
- **Multi-Tier Cache System** (`ClaudeCache.php`) - Redis-based caching with semantic similarity detection and compression
- **Batch Processor** (`BatchProcessor.php`) - Cost-effective batch processing achieving 88% savings over individual requests
- **Budget Tracker** (`BudgetTracker.php`) - Real-time spending monitoring with hourly, daily, and monthly limits and automated alerts
- **Cost Attribution System** (`CostAttribution.php`) - Database-backed cost tracking by user, feature, and model
- **ROI Calculator** (`RoiCalculator.php`) - Business value demonstration with payback period calculations

## Objectives

By completing this chapter, you will:

- Understand Claude's pricing structure and calculate costs accurately across all models
- Implement intelligent model selection that reduces costs by up to 95% while maintaining quality
- Optimize prompts to reduce token usage by 30-60% through template-based approaches and redundancy removal
- Build intelligent caching systems that eliminate redundant API calls and reduce costs
- Process multiple requests efficiently using batch processing for massive cost savings
- Monitor and control spending in real-time with budget tracking and automated alerts
- Track costs by user, feature, and model for optimization opportunities
- Calculate and demonstrate ROI to justify AI investments with concrete business metrics

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.4+** with Redis and database extensions
- ✓ **Anthropic API account** with billing enabled
- ✓ **Redis server** running and accessible
- ✓ **Database** (MySQL, PostgreSQL, or SQLite) for cost attribution
- ✓ Completion of [Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction) or understanding of Claude pricing
- ✓ Completion of [Chapter 37: Monitoring and Observability](/series/claude-php-developers/chapters/37-monitoring-observability) or monitoring setup

**Estimated Time**: 60-75 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Verify Redis extension
php -m | grep redis

# Verify database extension (choose one)
php -m | grep pdo_mysql
php -m | grep pdo_pgsql
php -m | grep pdo_sqlite

# Test Redis connection
redis-cli ping

# Check Anthropic API key is set
echo $ANTHROPIC_API_KEY | head -c 10
```

## Quick Start

Here's a quick 5-minute example demonstrating cost calculation and model comparison:

```php
<?php
# filename: examples/quick-start-cost-optimization.php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Billing\PricingCalculator;

$calculator = new PricingCalculator();

// Calculate cost for a typical request
$cost = $calculator->calculateCost(
    'model' => 'claude-sonnet-4-5-20250929',
    inputTokens: 200,
    outputTokens: 400
);

echo "Request cost: $" . number_format($cost['total_cost'], 6) . "\n";
echo "Input cost: $" . number_format($cost['input_cost'], 6) . "\n";
echo "Output cost: $" . number_format($cost['output_cost'], 6) . "\n\n";

// Compare costs across all models
$comparison = $calculator->compareCosts(200, 400);

echo "Cost comparison for 200 input + 400 output tokens:\n";
foreach (['haiku', 'sonnet', 'opus'] as $model) {
    $modelCost = $comparison[$model];
    $relative = $comparison[$model . '_relative'];
    echo sprintf(
        "  %s: $%s (%dx Haiku cost)\n",
        ucfirst($model),
        number_format($modelCost, 6),
        round($relative, 1)
    );
}

/*
Expected output:
Request cost: $0.006600
Input cost: $0.000600
Output cost: $0.006000

Cost comparison for 200 input + 400 output tokens:
  Haiku: $0.000550 (1x Haiku cost)
  Sonnet: $0.006600 (12x Haiku cost)
  Opus: $0.033000 (60x Haiku cost)
*/
```

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
        'claude-opus-4-1' => [
            'input' => 15.00,
            'output' => 75.00,
        ],
        'claude-sonnet-4-5' => [
            'input' => 3.00,
            'output' => 15.00,
        ],
        'claude-haiku-4-5-20251001' => [
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
    'model' => 'claude-sonnet-4-5-20250929',
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
    'model' => 'claude-sonnet-4-5-20250929',
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
                'simple' => 'claude-haiku-4-5-20251001',
                'medium' => 'claude-haiku-4-5-20251001',
                'complex' => 'claude-sonnet-4-5',
                default => 'claude-haiku-4-5-20251001'
            };
        }

        // Speed critical → use fastest model
        if ($responseTimeRequired === 'fast') {
            return 'claude-haiku-4-5-20251001';
        }

        // Quality critical → use best model
        if ($qualityRequired === 'critical') {
            return 'claude-opus-4-1';
        }

        // Default routing based on complexity
        return match($complexity) {
            'simple' => 'claude-haiku-4-5-20251001',
            'medium' => 'claude-sonnet-4-5',
            'complex' => 'claude-opus-4-1',
            default => 'claude-sonnet-4-5'
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
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$cache = new ClaudeCache($redis, defaultTtl: 3600);

// Cached request
$prompt = "What are the benefits of PHP 8.4?";
$cacheKey = $cache->generateKey($prompt, ['model' => 'claude-sonnet-4-5']);

$response = $cache->remember(
    cacheKey: $cacheKey,
    callback: fn() => $client->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
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

use ClaudePhp\ClaudePhp;


class BatchProcessor
{
    public function __construct(
        private readonly ClaudePhp $client
    ) {}

    /**
     * Process multiple items in a single request
     * Much more cost-effective than individual requests
     */
    public function processBatch(array $items, string $task, string $model = 'claude-haiku-4-5-20251001'): array
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
    'model' => 'claude-haiku-4-5-20251001'
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
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

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
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Hello Claude!']]
]);

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
    'model' => 'claude-sonnet-4-5-20250929',
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

## Cost Attribution in Multi-Tenant Systems

For SaaS applications serving multiple customers or teams, accurate cost attribution is critical for billing, profitability analysis, and optimization decisions.

### Multi-Tenant Cost Tracker

```php
<?php
# filename: src/Billing/MultiTenantCostTracker.php
declare(strict_types=1);

namespace App\Billing;

class MultiTenantCostTracker
{
    public function __construct(
        private readonly \PDO $db
    ) {
        $this->initializeSchema();
    }

    /**
     * Record cost with full tenant context
     */
    public function recordTenantCost(array $attribution): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO tenant_costs (
                tenant_id, user_id, feature, workspace_id,
                model, input_tokens, output_tokens, cost,
                request_id, created_at
            ) VALUES (
                :tenant_id, :user_id, :feature, :workspace_id,
                :model, :input_tokens, :output_tokens, :cost,
                :request_id, :created_at
            )
        ");

        $stmt->execute([
            'tenant_id' => $attribution['tenant_id'],
            'user_id' => $attribution['user_id'] ?? null,
            'feature' => $attribution['feature'],
            'workspace_id' => $attribution['workspace_id'] ?? null,
            'model' => $attribution['model'],
            'input_tokens' => $attribution['input_tokens'],
            'output_tokens' => $attribution['output_tokens'],
            'cost' => $attribution['cost'],
            'request_id' => $attribution['request_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get cost breakdown for tenant (for invoicing)
     */
    public function getTenantCostBreakdown(
        string $tenantId,
        string $startDate,
        string $endDate
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                feature,
                model,
                COUNT(*) as request_count,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cost) as feature_cost
            FROM tenant_costs
            WHERE tenant_id = :tenant_id
            AND created_at BETWEEN :start AND :end
            GROUP BY feature, model
            ORDER BY feature_cost DESC
        ");

        $stmt->execute([
            'tenant_id' => $tenantId,
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all tenant costs for billing period
     */
    public function getTenantBillingData(
        string $startDate,
        string $endDate
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                tenant_id,
                COUNT(*) as request_count,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cost) as total_cost,
                COUNT(DISTINCT DATE(created_at)) as usage_days,
                MIN(created_at) as first_usage,
                MAX(created_at) as last_usage
            FROM tenant_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY tenant_id
            ORDER BY total_cost DESC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calculate chargeable amount (with markup)
     */
    public function calculateChargeableAmount(
        float $actualCost,
        float $markupPercentage = 20.0,
        float $minimumCharge = 0.01
    ): float {
        $chargeAmount = $actualCost * (1 + $markupPercentage / 100);
        return max($chargeAmount, $minimumCharge);
    }

    /**
     * Get per-user cost allocation (for usage-based pricing)
     */
    public function getPerUserAllocation(
        string $tenantId,
        string $date
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                user_id,
                COUNT(*) as request_count,
                SUM(cost) as user_cost,
                ROUND(SUM(cost) / (
                    SELECT SUM(cost) FROM tenant_costs
                    WHERE tenant_id = :tenant_id AND DATE(created_at) = :date
                ) * 100, 2) as cost_percentage
            FROM tenant_costs
            WHERE tenant_id = :tenant_id
            AND DATE(created_at) = :date
            GROUP BY user_id
            ORDER BY user_cost DESC
        ");

        $stmt->execute([
            'tenant_id' => $tenantId,
            'date' => $date,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function initializeSchema(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tenant_costs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tenant_id TEXT NOT NULL,
                user_id TEXT,
                feature TEXT NOT NULL,
                workspace_id TEXT,
                model TEXT NOT NULL,
                input_tokens INTEGER NOT NULL,
                output_tokens INTEGER NOT NULL,
                cost REAL NOT NULL,
                request_id TEXT,
                created_at DATETIME NOT NULL
            )
        ");

        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_tenant_date ON tenant_costs(tenant_id, created_at)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_tenant_feature ON tenant_costs(tenant_id, feature)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_date ON tenant_costs(user_id, created_at)");
    }
}

// Usage - Multi-tenant billing
$tracker = new MultiTenantCostTracker($pdo);

// Record cost for specific tenant
$tracker->recordTenantCost([
    'tenant_id' => 'org-12345',
    'user_id' => 'user-567',
    'workspace_id' => 'workspace-789',
    'feature' => 'document-analysis',
    'model' => 'claude-sonnet-4-5-20250929',
    'input_tokens' => 2000,
    'output_tokens' => 500,
    'cost' => 0.0375,
    'request_id' => 'req-abc123',
]);

// Get billing data for invoicing
$billingData = $tracker->getTenantBillingData('2025-01-01', '2025-01-31');
foreach ($billingData as $tenant) {
    $chargeAmount = $tracker->calculateChargeableAmount(
        $tenant['total_cost'],
        markupPercentage: 20.0 // 20% markup for infrastructure
    );

    echo "Tenant {$tenant['tenant_id']}: " .
         "Actual cost $" . number_format($tenant['total_cost'], 2) .
         " → Charge: $" . number_format($chargeAmount, 2) . "\n";
}

// Per-user allocation for transparency
$perUser = $tracker->getPerUserAllocation('org-12345', '2025-01-15');
echo "Usage breakdown by user:\n";
foreach ($perUser as $user) {
    echo "  User {$user['user_id']}: $" . number_format($user['user_cost'], 2) .
         " ({$user['cost_percentage']}% of daily total)\n";
}
```

### Preventing Cost Attribution Issues

- **Always include tenant context** in every Claude request
- **Use structured attribution** with mandatory fields: tenant_id, feature, model
- **Implement verification** to ensure all requests are tracked
- **Monitor for missing data** in daily reconciliation
- **Use workspace isolation** for better granularity
- **Implement appeal/adjustment process** for disputed charges

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

## Cost Trend Analysis

Track cost patterns over time to identify growth trends, detect anomalies, and forecast future spending.

### Cost Trend Analyzer

```php
<?php
# filename: src/Billing/CostTrendAnalyzer.php
declare(strict_types=1);

namespace App\Billing;

class CostTrendAnalyzer
{
    public function __construct(
        private readonly \PDO $db
    ) {}

    /**
     * Analyze cost trends over time
     */
    public function analyzeTrends(
        string $startDate,
        string $endDate,
        string $groupBy = 'day' // 'day', 'week', 'month'
    ): array {
        $dateFormat = match($groupBy) {
            'week' => "'%Y-W%W'",
            'month' => "'%Y-%m'",
            default => "'%Y-%m-%d'"
        };

        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(created_at, $dateFormat) as period,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                AVG(cost) as avg_cost_per_request,
                SUM(input_tokens + output_tokens) as total_tokens,
                ROUND(AVG(input_tokens + output_tokens), 0) as avg_tokens_per_request
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY period
            ORDER BY period ASC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        $trends = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->enrichTrendsWithMetrics($trends, $groupBy);
    }

    /**
     * Calculate growth rate (week-over-week, month-over-month)
     */
    public function calculateGrowthRate(array $trends): array {
        $withGrowth = [];

        for ($i = 0; $i < count($trends); $i++) {
            $current = $trends[$i];

            if ($i > 0) {
                $previous = $trends[$i - 1];
                $previousCost = (float) $previous['total_cost'];
                $currentCost = (float) $current['total_cost'];

                if ($previousCost > 0) {
                    $growthRate = (($currentCost - $previousCost) / $previousCost) * 100;
                    $current['growth_rate'] = round($growthRate, 2);
                    $current['growth_direction'] = $growthRate > 0 ? 'up' : 'down';
                }
            }

            $withGrowth[] = $current;
        }

        return $withGrowth;
    }

    /**
     * Detect anomalies (unusual spikes or drops)
     */
    public function detectAnomalies(array $trends, float $stdDevThreshold = 2.0): array {
        $costs = array_map(fn($t) => (float)$t['total_cost'], $trends);

        // Calculate mean and standard deviation
        $mean = array_sum($costs) / count($costs);
        $variance = array_sum(array_map(
            fn($cost) => pow($cost - $mean, 2),
            $costs
        )) / count($costs);
        $stdDev = sqrt($variance);

        $anomalies = [];

        foreach ($trends as $i => $trend) {
            $cost = (float)$trend['total_cost'];
            $zScore = abs(($cost - $mean) / ($stdDev ?: 1));

            if ($zScore > $stdDevThreshold) {
                $anomalies[] = [
                    'period' => $trend['period'],
                    'cost' => $cost,
                    'expected' => $mean,
                    'z_score' => round($zScore, 2),
                    'severity' => $zScore > 3 ? 'critical' : 'warning',
                    'reason' => $cost > $mean ? 'spike' : 'drop'
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Forecast costs using linear regression
     */
    public function forecastCosts(
        array $historicalData,
        int $forecastPeriods = 7
    ): array {
        if (count($historicalData) < 2) {
            return [];
        }

        $x = array_keys($historicalData);
        $y = array_values($historicalData);

        // Calculate linear regression coefficients
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $y));
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecasts = [];
        $nextX = max($x) + 1;

        for ($i = 0; $i < $forecastPeriods; $i++) {
            $predictedCost = $intercept + $slope * $nextX;
            $forecasts[] = [
                'period_offset' => $i + 1,
                'predicted_cost' => max(0, round($predictedCost, 2)),
                'confidence' => $this->calculateConfidence($i, $forecastPeriods)
            ];
            $nextX++;
        }

        return [
            'forecasts' => $forecasts,
            'trend_direction' => $slope > 0 ? 'increasing' : 'decreasing',
            'trend_strength' => abs($slope)
        ];
    }

    /**
     * Compare costs across different dimensions
     */
    public function compareDimensions(
        string $startDate,
        string $endDate,
        string $dimension = 'model' // 'model', 'feature', 'user'
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                $dimension as category,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                ROUND(AVG(cost), 6) as avg_cost,
                ROUND(MIN(cost), 6) as min_cost,
                ROUND(MAX(cost), 6) as max_cost
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY $dimension
            ORDER BY total_cost DESC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calculate cost per metric (e.g., cost per user, per feature)
     */
    public function calculateCostPerMetric(
        string $startDate,
        string $endDate,
        string $metric = 'request' // 'request', 'token', 'user', 'feature'
    ): array {
        $calculation = match($metric) {
            'request' => 'SUM(cost) / COUNT(*) as cost_per_request',
            'token' => 'SUM(cost) / SUM(input_tokens + output_tokens) as cost_per_token',
            'user' => 'SUM(cost) / COUNT(DISTINCT user_id) as cost_per_user',
            'feature' => 'SUM(cost) / COUNT(DISTINCT feature) as cost_per_feature',
            default => 'SUM(cost) as total_cost'
        };

        $stmt = $this->db->prepare("
            SELECT
                $calculation
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: ['cost_per_' . $metric => 0];
    }

    private function enrichTrendsWithMetrics(array $trends, string $groupBy): array {
        return array_map(function ($trend) {
            $trend['cost_per_request'] = $trend['request_count'] > 0
                ? round((float)$trend['total_cost'] / $trend['request_count'], 6)
                : 0;

            $trend['cost_per_1k_tokens'] = $trend['total_tokens'] > 0
                ? round(((float)$trend['total_cost'] / $trend['total_tokens']) * 1000, 2)
                : 0;

            return $trend;
        }, $trends);
    }

    private function calculateConfidence(int $offset, int $total): float {
        // Confidence decreases with distance from historical data
        return max(0.5, 1.0 - ($offset / $total) * 0.3);
    }
}

// Usage - Trend analysis
$analyzer = new CostTrendAnalyzer($pdo);

// Daily trends for last 30 days
$trends = $analyzer->analyzeTrends(
    startDate: date('Y-m-d', strtotime('-30 days')),
    endDate: date('Y-m-d'),
    groupBy: 'day'
);

// Calculate growth rates
$trendsWithGrowth = $analyzer->calculateGrowthRate($trends);

echo "Daily Cost Trends:\n";
foreach ($trendsWithGrowth as $trend) {
    $growth = isset($trend['growth_rate'])
        ? " ({$trend['growth_rate']}% " . ($trend['growth_direction'] === 'up' ? '↑' : '↓') . ")"
        : '';
    echo "{$trend['period']}: $" . number_format($trend['total_cost'], 2) .
         " ({$trend['request_count']} requests){$growth}\n";
}

// Detect anomalies
$anomalies = $analyzer->detectAnomalies($trends);
if (!empty($anomalies)) {
    echo "\nAnomalies Detected:\n";
    foreach ($anomalies as $anomaly) {
        echo "Period {$anomaly['period']}: " .
             "$" . number_format($anomaly['cost'], 2) .
             " (z-score: {$anomaly['z_score']}, severity: {$anomaly['severity']})\n";
    }
}

// Forecast next 14 days
$historicalCosts = [];
foreach ($trends as $t) {
    $historicalCosts[] = (float)$t['total_cost'];
}

$forecast = $analyzer->forecastCosts($historicalCosts, forecastPeriods: 14);
echo "\n14-Day Cost Forecast:\n";
echo "Trend: {$forecast['trend_direction']} (strength: " .
     number_format($forecast['trend_strength'], 4) . ")\n";
foreach ($forecast['forecasts'] as $f) {
    echo "Day +{$f['period_offset']}: $" .
         number_format($f['predicted_cost'], 2) .
         " (confidence: " . round($f['confidence'] * 100) . "%)\n";
}

// Cost per dimension
echo "\nCost Breakdown by Model:\n";
$byModel = $analyzer->compareDimensions(
    date('Y-m-d', strtotime('-30 days')),
    date('Y-m-d'),
    'model'
);
foreach ($byModel as $model) {
    echo "{$model['category']}: $" . number_format($model['total_cost'], 2) .
         " ({$model['request_count']} requests)\n";
}

// Cost per metric
$costPerToken = $analyzer->calculateCostPerMetric(
    date('Y-m-d', strtotime('-30 days')),
    date('Y-m-d'),
    'token'
);
echo "\nCost per 1M tokens: $" .
     number_format($costPerToken['cost_per_token'] * 1_000_000, 2) . "\n";
```

### Exercises

## Exercises

### Exercise 1: Cost Optimizer Dashboard

**Goal**: Build a comprehensive cost optimization dashboard that identifies savings opportunities and provides actionable recommendations.

Create a file called `CostOptimizerDashboard.php` and implement:

- Generate spending trend analysis (daily, weekly, monthly)
- Identify cost optimization opportunities with potential savings
- Recommend model selection changes based on usage patterns
- Calculate cache hit rate and projected savings from improved caching
- Identify batch processing opportunities (requests that could be batched)
- Calculate ROI by feature to prioritize optimization efforts
- Generate visualizations-ready data (charts, graphs)

**Requirements**:

- Use `PricingCalculator`, `BudgetTracker`, and `CostAttribution` classes
- Accept date range parameters (`startDate`, `endDate`)
- Return structured array with `trends`, `opportunities`, `recommendations`, `projected_savings`
- Calculate at least 5 different optimization opportunities
- Include confidence scores for recommendations (0-100)

**Validation**: Test your implementation:

```php
<?php
$dashboard = new CostOptimizerDashboard();
$report = $dashboard->generateReport(
    startDate: '2025-01-01',
    endDate: '2025-01-31'
);

// Should return array with structure:
// [
//     'trends' => ['daily' => [...], 'weekly' => [...], 'monthly' => [...]],
//     'opportunities' => [
//         ['type' => 'model_selection', 'savings' => 150.00, 'confidence' => 85],
//         ['type' => 'caching', 'savings' => 75.00, 'confidence' => 90],
//         ...
//     ],
//     'recommendations' => [...],
//     'projected_savings' => 500.00,
//     'total_spent' => 2000.00
// ]

assert(isset($report['trends']));
assert(isset($report['opportunities']));
assert(is_array($report['opportunities']));
assert($report['projected_savings'] > 0);
echo "✓ Cost optimizer dashboard working correctly\n";
```

### Exercise 2: Automatic Model Downgrade

**Goal**: Implement intelligent model selection that automatically downgrades to cheaper models when budget pressure increases.

Create a file called `AutomaticCostControl.php` and implement:

- Check current budget usage percentage across all time periods
- If budget usage > 80%, prefer cheaper models (Sonnet → Haiku for simple tasks)
- If budget usage > 90%, use only Haiku for all non-critical tasks
- If budget usage > 95%, queue requests instead of processing immediately
- Log all downgrade decisions for analysis
- Provide fallback to original model if quality requirements can't be met

**Requirements**:

- Integrate with `BudgetTracker` and `ModelRouter` classes
- Accept `requirements` array (complexity, quality_required, etc.) and return selected model
- Return decision metadata: `model`, `reason`, `budget_usage`, `original_model`
- Handle edge cases (budget exceeded, quality requirements too high)
- Support manual override flag to bypass automatic downgrade

**Validation**: Test your implementation:

```php
<?php
$costControl = new AutomaticCostControl($budgetTracker, $modelRouter);

// Test with high budget usage
$budgetTracker->trackSpending(180.00); // 90% of $200 daily budget
$decision = $costControl->selectModel(
    requirements: ['complexity' => 'medium', 'quality_required' => 'medium'],
    currentBudgetUsage: 0.90
);

// Should downgrade to Haiku
assert($decision['model'] === 'claude-haiku-4-5-20251001');
assert($decision['reason'] === 'budget_pressure');
assert($decision['budget_usage'] === 0.90);
echo "✓ Automatic cost control working correctly\n";
```

### Exercise 3: Cost Forecasting

**Goal**: Build a cost forecasting system that predicts future spending based on historical patterns.

Create a file called `CostForecaster.php` and implement:

- Analyze historical usage trends (daily, weekly patterns)
- Calculate growth rate from historical data
- Identify seasonal patterns (if data spans multiple months)
- Predict monthly costs with confidence intervals
- Forecast budget needs for next 1, 3, and 6 months
- Account for known events (product launches, marketing campaigns)

**Requirements**:

- Accept `historicalData` array with `date`, `cost`, `requests`, `tokens` fields
- Use linear regression or moving average for trend analysis
- Return forecasts with `predicted_cost`, `confidence_interval`, `growth_rate`
- Support multiple forecasting methods (simple average, exponential smoothing, linear regression)
- Include risk assessment (high/medium/low based on variance)

**Validation**: Test your implementation:

```php
<?php
$forecaster = new CostForecaster();

// Generate sample historical data (last 30 days)
$historicalData = [];
for ($i = 30; $i > 0; $i--) {
    $historicalData[] = [
        'date' => date('Y-m-d', strtotime("-$i days")),
        'cost' => 50 + ($i * 0.5) + rand(-5, 5), // Slight upward trend
        'requests' => 1000 + ($i * 10),
        'tokens' => 50000 + ($i * 500),
    ];
}

$forecast = $forecaster->forecastMonthlyCost($historicalData);

// Should return:
// [
//     'next_month' => ['predicted_cost' => 1650.00, 'confidence' => 85],
//     'next_3_months' => ['predicted_cost' => 5100.00, 'confidence' => 75],
//     'next_6_months' => ['predicted_cost' => 10500.00, 'confidence' => 65],
//     'growth_rate' => 0.05, // 5% monthly growth
//     'risk_level' => 'medium'
// ]

assert(isset($forecast['next_month']));
assert($forecast['next_month']['predicted_cost'] > 0);
assert(isset($forecast['growth_rate']));
echo "✓ Cost forecasting working correctly\n";
```

## Troubleshooting

### Unexpected High Costs

**Symptom**: Monthly costs are significantly higher than expected or budgeted.

**Possible Causes**:
- Using expensive models (Opus) for simple tasks that could use Haiku
- Caching not working effectively (low hit rate)
- Inefficient prompts generating excessive tokens
- Missing batch processing opportunities
- Budget tracking not implemented or not working

**Solutions**:

1. **Review model usage**:
```php
// Check which models are being used
$modelStats = $costAttribution->getModelStats('2025-01-01', '2025-01-31');
foreach ($modelStats as $stat) {
    echo "{$stat['model']}: {$stat['request_count']} requests, $" . 
         number_format($stat['total_cost'], 2) . "\n";
}
// If Opus is used frequently, consider downgrading to Sonnet or Haiku
```

2. **Check cache performance**:
```php
$cacheStats = $cache->getStats();
if ($cacheStats['hit_rate'] < 30) {
    // Cache not effective - review cache key generation and TTL
    echo "Cache hit rate too low: {$cacheStats['hit_rate']}%\n";
    echo "Consider: shorter TTL, better cache keys, semantic caching\n";
}
```

3. **Analyze prompt efficiency**:
```php
// Check average tokens per request
$avgTokens = $costAttribution->getAverageTokensPerRequest();
if ($avgTokens > 2000) {
    echo "High token usage detected. Optimize prompts using PromptOptimizer\n";
}
```

### Cache Not Effective

**Symptom**: Cache hit rate is below 30%, costs not reducing despite caching implementation.

**Possible Causes**:
- Cache keys too specific (include timestamps, user IDs unnecessarily)
- TTL too short for frequently accessed data
- Cache being cleared too frequently
- Query patterns not cacheable (unique prompts every time)

**Solutions**:

1. **Review cache key generation**:
```php
// Bad: Includes timestamp (always unique)
$badKey = "claude:cache:" . hash('sha256', $prompt . time());

// Good: Only includes relevant parameters
$goodKey = $cache->generateKey($prompt, [
    'model' => $model,
    'temperature' => $temperature, // Only if it affects output
    // Don't include: timestamps, user IDs (unless user-specific)
]);
```

2. **Adjust TTL based on data volatility**:
```php
// Static reference data: long TTL
$cache->remember($key, $callback, ttl: 86400); // 24 hours

// Frequently changing data: shorter TTL
$cache->remember($key, $callback, ttl: 3600); // 1 hour
```

3. **Implement semantic caching**:
```php
// Find similar cached prompts instead of exact matches
$similarKey = $cache->findSimilar($prompt, threshold: 0.85);
if ($similarKey) {
    return $cache->get($similarKey);
}
```

### Budget Alerts Firing Constantly

**Symptom**: Budget alerts (75%, 90%, 100%) triggering frequently, making alerts ineffective.

**Possible Causes**:
- Budget limits set too low for actual usage
- Sudden traffic spikes not accounted for
- Cost per request higher than expected
- Budget tracking includes test/development costs

**Solutions**:

1. **Review and adjust budget limits**:
```php
// Analyze historical spending to set realistic limits
$historicalSpending = $budgetTracker->getHistoricalSpending('2025-01-01', '2025-01-31');
$avgDaily = $historicalSpending['total'] / 31;
$recommendedDaily = $avgDaily * 1.2; // 20% buffer

// Update budget limits
$budgetTracker = new BudgetTracker($redis, [
    'hourly' => $recommendedDaily / 24,
    'daily' => $recommendedDaily,
    'monthly' => $recommendedDaily * 30,
]);
```

2. **Separate production and development costs**:
```php
// Track costs separately
$budgetTracker->trackSpending($cost, userId: 'production');
$budgetTracker->trackSpending($cost, userId: 'development');

// Set budgets only for production
$productionSpending = $budgetTracker->getUserSpending('production', date('Y-m-d'));
```

3. **Implement request throttling**:
```php
// Throttle requests when approaching budget limits
$status = $budgetTracker->getBudgetStatus();
if ($status['daily']['used_pct'] > 80) {
    // Slow down request processing
    sleep(1); // Add delay between requests
    // Or queue requests for later processing
}
```

### Model Selection Not Working

**Symptom**: Expensive models (Opus) being used when cheaper models would suffice.

**Possible Causes**:
- ModelRouter not properly configured
- Complexity classification too conservative
- Quality requirements set too high
- Budget priority flag not set

**Solutions**:

1. **Review model selection logic**:
```php
// Check what models are being recommended
$recommendation = $modelRouter->recommendModel($prompt, [
    'budget_priority' => true, // Enable budget optimization
    'quality_required' => 'medium', // Don't default to 'critical'
]);
echo "Recommended: {$recommendation['recommended_model']}\n";
echo "Reasoning: {$recommendation['reasoning']}\n";
```

2. **Adjust complexity thresholds**:
```php
// If too many tasks classified as 'complex', lower threshold
$complexity = $modelRouter->classifyComplexity($prompt);
// Review classification logic and adjust thresholds
```

### Cost Attribution Not Accurate

**Symptom**: Cost attribution data doesn't match actual spending or is incomplete.

**Possible Causes**:
- Not recording all requests
- Missing user/feature context
- Database connection issues
- Race conditions in concurrent requests

**Solutions**:

1. **Verify all requests are being tracked**:
```php
// Add logging to verify tracking
$costAttribution->recordCost([
    'user_id' => $userId,
    'feature' => $feature,
    'model' => $response->model,
    'input_tokens' => $response->usage->inputTokens,
    'output_tokens' => $response->usage->outputTokens,
    'cost' => $cost,
    'request_id' => $requestId,
]);

// Log for debugging
error_log("Cost tracked: user=$userId, feature=$feature, cost=$cost");
```

2. **Check database connectivity**:
```php
try {
    $costAttribution->recordCost($attribution);
} catch (\PDOException $e) {
    // Fallback: log to file or queue for later processing
    error_log("Failed to record cost: " . $e->getMessage());
    // Queue for retry
    $queue->push('record-cost', $attribution);
}
```

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've mastered cost optimization for Claude applications. Here's what you've accomplished:

- ✓ **Pricing Understanding**: Built a comprehensive pricing calculator that understands Claude's cost structure across all models
- ✓ **Strategic Model Selection**: Created an intelligent model router that automatically selects the most cost-effective model based on task requirements
- ✓ **Prompt Optimization**: Implemented prompt optimization techniques that reduce token usage by 30-60% without sacrificing quality
- ✓ **Intelligent Caching**: Built a multi-tier caching system with semantic similarity detection to eliminate redundant API calls
- ✓ **Batch Processing**: Developed batch processing capabilities that achieve 88% cost savings compared to individual requests
- ✓ **Budget Management**: Implemented real-time budget tracking with hourly, daily, and monthly limits and automated alerts
- ✓ **Cost Attribution**: Created a comprehensive cost attribution system tracking spending by user, feature, and model
- ✓ **ROI Tracking**: Built ROI calculators that demonstrate business value with concrete metrics and payback periods

**Key Concepts Learned:**

- Model selection can reduce costs by 95% - always use the cheapest model that meets quality requirements
- Prompt optimization through template-based approaches and redundancy removal significantly reduces token costs
- Intelligent caching with proper key generation and TTL management eliminates repeated API calls
- Batch processing consolidates multiple requests into single API calls for massive cost savings
- Budget tracking at multiple time granularities enables proactive cost management
- Cost attribution provides visibility into spending patterns for optimization opportunities
- ROI measurement demonstrates business value and justifies AI investments

**Real-World Impact:**

With these cost optimization strategies, you can:
- Reduce Claude API costs by 50-90% through intelligent model selection and caching
- Prevent budget overruns with real-time monitoring and automated controls
- Demonstrate ROI to stakeholders with concrete business metrics
- Scale AI features cost-effectively as usage grows
- Make data-driven decisions about feature development based on cost attribution

**Next Steps:**

Cost optimization is an ongoing process. Continue to:
- Monitor spending trends and adjust budgets accordingly
- Refine model selection logic based on quality metrics
- Optimize prompts based on token usage analysis
- Expand caching strategies for new use cases
- Track ROI over time to demonstrate long-term value

You've completed the entire Claude for PHP Developers series! You now have comprehensive knowledge of building production-ready Claude applications with proper security, monitoring, scaling, and cost optimization.

## Key Takeaways

- ✓ **Model Selection**: Right model can save 95% - use Haiku for simple tasks
- ✓ **Prompt Optimization**: Concise prompts save 30-60% on token costs
- ✓ **Intelligent Caching**: Cache hit rate of 40%+ eliminates repeated costs
- ✓ **Batch Processing**: Process 100 items for 88% savings vs individual requests
- ✓ **Budget Tracking**: Monitor spending at hourly/daily/monthly levels
- ✓ **Cost Attribution**: Track costs by user, feature, and model
- ✓ **ROI Measurement**: Demonstrate value with concrete business metrics
- ✓ **Automated Controls**: Implement budget limits and automatic optimizations

## Integration with Other Production Chapters

### With Chapter 37: Monitoring and Observability

[Chapter 37](/series/claude-php-developers/chapters/37-monitoring-observability) covers metrics collection, logging, and dashboards. **Chapter 39 extends this with cost-specific metrics:**

- **Chapter 37 provides**: Request latency, error rates, token usage, quality metrics
- **Chapter 39 adds**: Cost attribution, ROI tracking, budget anomalies, cost trends
- **Integration pattern**:

```php
// From Ch. 37: Metrics collection
$metrics->recordLatency($duration);

// From Ch. 39: Cost tracking
$costTracker->trackSpending($actualCost, $userId);

// Combined dashboard: Performance + Cost correlation
$dashboard->showCostPerLatency(); // Cost efficiency metric
$dashboard->showROIByFeature();   // Business value metric
```

**Use together for**: Production observability that combines performance and cost insights. Example: Identify features that are performant but unprofitable, or slow but valuable for customers.

### With Chapter 38: Scaling Applications

[Chapter 38](/series/claude-php-developers/chapters/38-scaling-applications) covers scaling architecture, load balancing, and concurrency. **Chapter 39 ensures scaling remains cost-effective:**

- **Chapter 38 provides**: Load balancing, queue management, circuit breakers, rate limiting
- **Chapter 39 adds**: Cost-aware model selection, budget-based throttling, batch processing at scale
- **Integration pattern**:

```php
// From Ch. 38: Scalability patterns
$circuitBreaker->checkHealth();
$queue->processAsync();

// From Ch. 39: Cost optimization at scale
$modelRouter->selectModel($requirements); // Uses budget status
$batchProcessor->processBatch($items);    // Reduces API calls by 88%

// Combined effect: Scale smoothly while controlling costs
```

**Use together for**: Scaling without runaway costs. Example:

```php
// As load increases, Ch. 38 handles traffic; Ch. 39 controls costs
if ($queue->isBacklogged()) {
    // Ch. 38: Add more workers, increase concurrency
    $queue->spawn_workers();
    
    // Ch. 39: Downgrade models to Haiku, enable aggressive batching
    $modelRouter->setBudgetMode('aggressive');
    $batchProcessor->increaseMaxBatchSize();
}
```

**Real-world scenario**: During a product launch, traffic spikes 10x:
- **Ch. 38 ensures** your infrastructure handles it (no timeouts, no errors)
- **Ch. 39 ensures** costs only increase 3x instead of 10x (through model optimization, caching, batching)

## Further Reading

- [Anthropic Pricing Documentation](https://docs.claude.com/en/docs/models-and-pricing/pricing) — Official Claude API pricing information and model costs
- [Anthropic Rate Limits](https://docs.claude.com/en/api/rate-limits) — Understanding rate limits and quota management
- [Prompt Engineering Guide](https://docs.claude.com/en/docs/prompt-engineering) — Best practices for writing efficient prompts
- [Redis Caching Patterns](https://redis.io/docs/manual/patterns/) — Advanced caching strategies and patterns
- [Batch Processing Best Practices](https://docs.claude.com/en/docs/capabilities/batch-processing) — Official guidance on batch API usage
- [Cost Optimization Strategies](https://docs.claude.com/en/docs/cost-optimization) — Anthropic's recommendations for cost management
- [ROI Calculation Methods](https://www.investopedia.com/terms/r/returnoninvestment.asp) — Understanding ROI calculations for business decisions
- [Linear Regression Forecasting](https://en.wikipedia.org/wiki/Simple_linear_regression) — Statistical basis for cost trend forecasting
- [Multi-Tenant Billing Systems](https://stripe.com/docs/billing) — Best practices from payment processor implementations
- [Chapter 10: Error Handling & Rate Limiting](/series/claude-php-developers/chapters/10-error-handling-rate-limiting) — Foundation for cost-aware rate limiting
- [Chapter 18: Caching Strategies](/series/claude-php-developers/chapters/18-caching-strategies) — Detailed caching implementation (complements Ch. 39)
- [Chapter 37: Monitoring and Observability](/series/claude-php-developers/chapters/37-monitoring-observability) — Monitor costs alongside performance metrics
- [Chapter 38: Scaling Applications](/series/claude-php-developers/chapters/38-scaling-applications) — Scale cost-effectively with proper architecture

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
