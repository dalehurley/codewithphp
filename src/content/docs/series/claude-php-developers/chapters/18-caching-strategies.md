---
title: "18: Caching Strategies for API Calls"
description: "Optimize Claude API costs and performance with prompt caching, response caching using Redis, cache invalidation strategies, and semantic similarity caching."
sidebar:
  label: "18: Caching Strategies for API Calls"
  order: 18
  badge:
    text: Expert
    variant: danger
---
![18: Caching Strategies for API Calls](/images/claude-php/chapter-18-hero-full.webp)


# Chapter 18: Caching Strategies for API Calls

## Overview

Caching is essential for production Claude applications—it reduces costs, improves response times, and provides resilience against API outages. This chapter covers multiple caching strategies: Anthropic's native prompt caching, response caching with Redis, intelligent cache invalidation, and semantic similarity caching for fuzzy matching.

You'll learn to implement sophisticated caching layers that can reduce API costs by 90% while maintaining fresh, relevant responses.

## Prerequisites

Before diving in, ensure you have:

- ✓ **Chapter 17** completed (Service class knowledge)
- ✓ **Redis** installed and running
- ✓ **PSR-6 or PSR-16** cache interface understanding
- ✓ **Cache strategies** basic knowledge

**Estimated Time**: 60-75 minutes

## What You'll Build

By the end of this chapter, you will have created:

- A `CachedClaudeService` class implementing response caching with Redis
- A `TieredCacheService` combining in-memory and persistent caching layers
- A `SemanticCacheService` for similarity-based cache matching
- Cache invalidation strategies for managing stale data
- Cache warming and monitoring utilities
- Complete working examples demonstrating 90% cost reduction through caching

## Objectives

- Understand Anthropic's native prompt caching and how to use it effectively
- Implement response caching with Redis using PSR-16 interfaces
- Build tiered caching systems combining memory and persistent storage
- Create semantic similarity caching for fuzzy prompt matching
- Design cache invalidation strategies for production applications
- Monitor cache performance and optimize hit rates
- Implement cache warming for frequently used queries

## Cache Layer Architecture

A comprehensive caching strategy uses multiple layers, each checked in sequence from fastest to slowest:

**Cache Request Flow:**

1. **In-Memory Cache Check** (~0.1ms)
   - First line of defense for hot data
   - If hit: Return response immediately
   - If miss: Continue to next layer

2. **Redis Cache Check** (~1-5ms)
   - Persistent storage shared across instances
   - If hit: Store in memory cache and return response
   - If miss: Continue to next layer

3. **Semantic Cache Check** (~5-10ms)
   - Fuzzy matching for similar prompts
   - If similar match found: Store in Redis and memory, return response
   - If no match: Continue to API call

4. **Claude API Call** (~500-2000ms)
   - Most expensive operation
   - Store response in all cache layers
   - Return response to user

**Layer Benefits:**

- **In-Memory**: Fastest access (~0.1ms), perfect for repeated requests within the same request lifecycle
- **Redis**: Persistent across requests (~1-5ms), shared across application instances
- **Semantic**: Fuzzy matching for similar prompts, reduces redundant API calls
- **API**: Most expensive but always fresh, used only when cache misses occur

## Anthropic's Prompt Caching

Anthropic offers native prompt caching to reduce costs for repeated context. This is different from response caching—it caches the **input context** (like documentation or system prompts) rather than the complete response.

::: tip When to Use Prompt Caching
Use Anthropic's prompt caching when:

- You have large, static context (documentation, knowledge bases, system instructions)
- The same context is used across multiple requests
- You want to reduce input token costs (up to 90% discount on cached tokens)
- Context changes infrequently (cache lasts ~5 minutes)
  :::

::: warning Cache Duration
Anthropic's prompt cache expires after approximately 5 minutes. For longer-lived caching, use response caching with Redis instead.
:::

```php
<?php
# filename: examples/01-prompt-caching.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Large context that will be cached
$documentationContext = file_get_contents(__DIR__ . '/large-documentation.txt');

// First request - full cost
$response1 = $client->messages()->create(
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'system' => [
        [
            'type' => 'text',
            'text' => 'You are a helpful assistant that answers questions about PHP documentation.',
        ],
        [
            'type' => 'text',
            'text' => $documentationContext,
            'cache_control' => ['type' => 'ephemeral'] // Cache this block
        ]
    ],
    'messages' => [
        ['role' => 'user', 'content' => 'What are PHP attributes?']
    ]
);

echo "First Request:\n";
echo "Response: " . $response1->content[0]->text . "\n";
echo "Input tokens: " . ($response1->usage->inputTokens ?? 0) . "\n";
echo "Cache creation tokens: " . ($response1->usage->cacheCreationInputTokens ?? 0) . "\n";
echo "Cache read tokens: " . ($response1->usage->cacheReadInputTokens ?? 0) . "\n\n";

// Second request within 5 minutes - uses cached context
sleep(2);

$response2 = $client->messages()->create(
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'system' => [
        [
            'type' => 'text',
            'text' => 'You are a helpful assistant that answers questions about PHP documentation.',
        ],
        [
            'type' => 'text',
            'text' => $documentationContext,
            'cache_control' => ['type' => 'ephemeral']
        ]
    ],
    'messages' => [
        ['role' => 'user', 'content' => 'How do enums work in PHP?']
    ]
);

echo "Second Request (with cache hit):\n";
echo "Response: " . $response2->content[0]->text . "\n";
echo "Input tokens: " . ($response2->usage->inputTokens ?? 0) . "\n";
echo "Cache creation tokens: " . ($response2->usage->cacheCreationInputTokens ?? 0) . "\n";
echo "Cache read tokens: " . ($response2->usage->cacheReadInputTokens ?? 0) . "\n\n";

// Calculate savings
$costSavings = ($response2->usage->cacheReadInputTokens ?? 0) * 0.90; // 90% discount
echo "Cache read tokens at 90% discount: significant cost savings!\n";
```

### Why Prompt Caching Works

Anthropic's prompt caching identifies repeated context blocks in your system messages. When you mark a block with `cache_control: ['type' => 'ephemeral']`, Anthropic:

1. **First request**: Processes and caches the context block (full cost)
2. **Subsequent requests**: Reuses the cached block (90% discount on cached tokens)
3. **Cache expires**: After ~5 minutes, the next request rebuilds the cache

This is ideal for applications with large, static documentation or knowledge bases that don't change frequently. The cache is ephemeral (temporary) and tied to your API key, so it's automatically managed by Anthropic.

## Response Caching with Redis

Cache complete API responses for identical requests. Unlike prompt caching, this caches the **entire response**, allowing you to serve identical requests instantly without any API call.

::: tip When to Use Response Caching
Use Redis response caching when:

- Users frequently ask identical questions
- Responses don't need to be real-time fresh
- You want to reduce API costs and improve response times
- You need cache persistence across application restarts
- You want fine-grained control over cache invalidation
  :::

::: warning Cache Key Collisions
Ensure your cache key generation includes all parameters that affect the response (prompt, model, temperature, max_tokens). Missing parameters can cause incorrect cache hits.
:::

```php
<?php
# filename: src/Services/CachedClaudeService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class CachedClaudeService implements ClaudeServiceInterface
{
    public function __construct(
        private ClaudePhp $client,
        private CacheInterface $cache,
        private ?LoggerInterface $logger = null,
        private int $defaultTtl = 3600,
        private string $defaultModel = 'claude-sonnet-4-5-20250929'
    ) {}

    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string {
        $cacheKey = $this->generateCacheKey([
            'prompt' => $prompt,
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'model' => $model ?? $this->defaultModel,
        ]);

        // Check cache first
        if ($this->cache->has($cacheKey)) {
            $this->logger?->info('Cache HIT', ['key' => $cacheKey]);
            return $this->cache->get($cacheKey);
        }

        $this->logger?->info('Cache MISS', ['key' => $cacheKey]);

        // Make API call
        $response = $this->client->messages()->create(
            'model' => $model ?? $this->defaultModel,
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $text = $response->content[0]->text;

        // Cache the response
        $this->cache->set($cacheKey, $text, $this->defaultTtl);

        return $text;
    }

    public function generateWithMetadata(
        string $prompt,
        array $options = []
    ): array {
        $cacheKey = $this->generateCacheKey(array_merge(
            ['prompt' => $prompt],
            $options
        ));

        if ($this->cache->has($cacheKey)) {
            $this->logger?->info('Cache HIT (with metadata)', ['key' => $cacheKey]);
            return $this->cache->get($cacheKey);
        }

        $this->logger?->info('Cache MISS (with metadata)', ['key' => $cacheKey]);

        $response = $this->client->messages()->create(
            'model' => $options['model'] ?? $this->defaultModel,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $result = [
            'text' => $response->content[0]->text,
            'metadata' => [
                'id' => $response->id,
                'model' => $response->model,
                'stop_reason' => $response->stopReason,
                'usage' => [
                    'input_tokens' => $response->usage->inputTokens ?? 0,
                    'output_tokens' => $response->usage->outputTokens ?? 0,
                ],
            ]
        ];

        $this->cache->set($cacheKey, $result, $this->defaultTtl);

        return $result;
    }

    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void {
        // Streaming responses are typically not cached
        // Note: Claude-PHP-SDK streaming implementation might differ
        // This is a conceptual example
        throw new \RuntimeException('Streaming implementation pending SDK support');
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    public function healthCheck(): bool
    {
        try {
            $response = $this->client->messages()->create(
                'model' => $this->defaultModel,
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping']
                ]
            );

            return !empty($response->content[0]->text);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear cache for a specific prompt
     */
    public function clearCache(string $prompt, array $options = []): bool
    {
        $cacheKey = $this->generateCacheKey(array_merge(
            ['prompt' => $prompt],
            $options
        ));

        return $this->cache->delete($cacheKey);
    }

    /**
     * Generate deterministic cache key from parameters
     *
     * Uses MD5 hash to ensure:
     * - Same parameters = same key (deterministic)
     * - Keys are fixed length (Redis-friendly)
     * - Includes all parameters that affect response
     */
    private function generateCacheKey(array $params): string
    {
        ksort($params); // Ensure consistent ordering
        return 'claude:' . md5(json_encode($params, JSON_UNESCAPED_UNICODE));
    }
}
```

### Why Response Caching Works

Response caching stores the complete API response in Redis, allowing you to:

1. **Check cache first**: Before making any API call, check if an identical request was made recently
2. **Serve instantly**: Return cached response in milliseconds instead of seconds
3. **Reduce costs**: Eliminate API calls entirely for cached requests
4. **Improve UX**: Users get instant responses for common queries

The cache key includes all parameters that affect the response (prompt, model, temperature, max_tokens), ensuring that different configurations don't collide. The MD5 hash creates a fixed-length key that's Redis-friendly and prevents key collisions.

## Redis Caching Example

Here's a complete example showing Redis caching in action:

```php
<?php
# filename: examples/02-redis-caching.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Connect to Redis
$redisConnection = RedisAdapter::createConnection('redis://localhost');
$redisAdapter = new RedisAdapter($redisConnection);
$cache = new Psr16Cache($redisAdapter);

// Create cached service
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$claudeService = new \App\Services\CachedClaudeService(
    client: $client,
    cache: $cache,
    logger: new \Monolog\Logger('claude'),
    defaultTtl: 3600
);

// First request - cache miss
echo "First request (cache miss):\n";
$start = microtime(true);
$response1 = $claudeService->generate('What is dependency injection?');
$duration1 = microtime(true) - $start;
echo "Response: " . substr($response1, 0, 100) . "...\n";
echo "Duration: " . number_format($duration1, 3) . "s\n\n";

// Second identical request - cache hit
echo "Second request (cache hit):\n";
$start = microtime(true);
$response2 = $claudeService->generate('What is dependency injection?');
$duration2 = microtime(true) - $start;
echo "Response: " . substr($response2, 0, 100) . "...\n";
echo "Duration: " . number_format($duration2, 3) . "s\n\n";

echo "Speed improvement: " . number_format($duration1 / $duration2, 1) . "x faster\n";
```

## Tiered Cache Strategy

Combine in-memory and Redis for optimal performance. This multi-layer approach provides the best of both worlds: lightning-fast in-memory access for hot data, and persistent Redis storage for warm data.

::: tip Performance Benefits

- **Memory cache**: ~0.1ms access time, perfect for repeated requests
- **Redis cache**: ~1-5ms access time, persists across requests
- **Combined**: Most requests hit memory cache, reducing Redis load
  :::

::: warning Memory Management
Monitor memory cache size to prevent excessive memory usage. The example uses LRU (Least Recently Used) eviction when the cache exceeds 100 entries.
:::

```php
<?php
# filename: src/Services/TieredCacheService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class TieredCacheService implements ClaudeServiceInterface
{
    private array $memoryCache = [];
    private int $memoryCacheSize = 100;

    public function __construct(
        private ClaudePhp $client,
        private CacheInterface $persistentCache,
        private ?LoggerInterface $logger = null,
        private int $memoryTtl = 300,      // 5 minutes
        private int $persistentTtl = 3600  // 1 hour
    ) {}

    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string {
        $cacheKey = $this->generateCacheKey([
            'prompt' => $prompt,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'model' => $model,
        ]);

        // Layer 1: Check in-memory cache
        if ($this->hasInMemory($cacheKey)) {
            $this->logger?->info('Memory cache HIT', ['key' => $cacheKey]);
            return $this->getFromMemory($cacheKey);
        }

        // Layer 2: Check Redis cache
        if ($this->persistentCache->has($cacheKey)) {
            $this->logger?->info('Redis cache HIT', ['key' => $cacheKey]);
            $value = $this->persistentCache->get($cacheKey);
            $this->setInMemory($cacheKey, $value);
            return $value;
        }

        // Layer 3: API call
        $this->logger?->info('Cache MISS - API call', ['key' => $cacheKey]);

        $response = $this->client->messages()->create(
            'model' => $model ?? 'claude-sonnet-4-5-20250929',
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $text = $response->content[0]->text;

        // Store in both cache layers
        $this->setInMemory($cacheKey, $text);
        $this->persistentCache->set($cacheKey, $text, $this->persistentTtl);

        return $text;
    }

    public function generateWithMetadata(string $prompt, array $options = []): array
    {
        // Similar implementation with metadata
        throw new \RuntimeException('Not implemented in example');
    }

    public function stream(string $prompt, callable $callback, array $options = []): void
    {
        // Streaming not cached
        throw new \RuntimeException('Not implemented in example');
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    public function healthCheck(): bool
    {
        return true;
    }

    private function hasInMemory(string $key): bool
    {
        if (!isset($this->memoryCache[$key])) {
            return false;
        }

        $entry = $this->memoryCache[$key];
        if (time() > $entry['expires']) {
            unset($this->memoryCache[$key]);
            return false;
        }

        return true;
    }

    private function getFromMemory(string $key): string
    {
        return $this->memoryCache[$key]['value'];
    }

    private function setInMemory(string $key, string $value): void
    {
        // Implement simple LRU by removing oldest if size limit reached
        if (count($this->memoryCache) >= $this->memoryCacheSize) {
            $oldest = array_key_first($this->memoryCache);
            unset($this->memoryCache[$oldest]);
        }

        $this->memoryCache[$key] = [
            'value' => $value,
            'expires' => time() + $this->memoryTtl,
        ];
    }

    private function generateCacheKey(array $params): string
    {
        $filtered = array_filter($params, fn($v) => $v !== null);
        ksort($filtered);
        return 'claude:' . md5(json_encode($filtered, JSON_UNESCAPED_UNICODE));
    }
}
```

## Semantic Similarity Caching

Cache responses based on prompt similarity, not just exact matches. This advanced strategy uses string similarity algorithms to find semantically similar prompts and reuse their cached responses.

::: tip When to Use Semantic Caching
Use semantic caching when:

- Users ask similar questions with different wording
- You want to reduce API calls for semantically equivalent queries
- Responses are general enough that slight prompt variations don't matter
- You have many cached prompts to compare against
  :::

::: warning Similarity Threshold
The default threshold of 0.85 (85% similarity) balances cache hits with accuracy. Lower thresholds increase hits but may return less relevant responses. Adjust based on your use case.
:::

```php
<?php
# filename: src/Services/SemanticCacheService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use ClaudePhp\ClaudePhp;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

class SemanticCacheService implements ClaudeServiceInterface
{
    /**
     * Similarity threshold: 0.0 (no match) to 1.0 (exact match)
     * 0.85 means prompts must be 85% similar to reuse cached response
     */
    private const SIMILARITY_THRESHOLD = 0.85;

    public function __construct(
        private ClaudePhp $client,
        private CacheInterface $cache,
        private ?LoggerInterface $logger = null,
        private float $similarityThreshold = self::SIMILARITY_THRESHOLD
    ) {}

    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string {
        // Check for semantically similar cached prompts
        $similarCacheKey = $this->findSimilarCachedPrompt($prompt);

        if ($similarCacheKey) {
            $this->logger?->info('Semantic cache HIT', [
                'original_prompt' => substr($prompt, 0, 50),
                'cache_key' => $similarCacheKey
            ]);

            return $this->cache->get($similarCacheKey);
        }

        $this->logger?->info('Semantic cache MISS', [
            'prompt' => substr($prompt, 0, 50)
        ]);

        // Make API call
        $response = $this->client->messages()->create(
            'model' => $model ?? 'claude-sonnet-4-5-20250929',
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $text = $response->content[0]->text;

        // Cache with prompt mapping
        $cacheKey = 'claude:semantic:' . md5($prompt);
        $this->cache->set($cacheKey, $text, 3600);

        // Store prompt for similarity matching
        $promptMapKey = 'claude:prompts:map';
        $promptMap = $this->cache->get($promptMapKey, []);
        $promptMap[$cacheKey] = $prompt;
        $this->cache->set($promptMapKey, $promptMap, 3600);

        return $text;
    }

    public function generateWithMetadata(string $prompt, array $options = []): array
    {
        throw new \RuntimeException('Not implemented in example');
    }

    public function stream(string $prompt, callable $callback, array $options = []): void
    {
        throw new \RuntimeException('Not implemented in example');
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    public function healthCheck(): bool
    {
        return true;
    }

    /**
     * Find cached prompt similar to the given prompt
     */
    private function findSimilarCachedPrompt(string $prompt): ?string
    {
        $promptMapKey = 'claude:prompts:map';
        $promptMap = $this->cache->get($promptMapKey, []);

        $bestMatch = null;
        $bestSimilarity = 0.0;

        foreach ($promptMap as $cacheKey => $cachedPrompt) {
            $similarity = $this->calculateSimilarity($prompt, $cachedPrompt);

            if ($similarity > $bestSimilarity && $similarity >= $this->similarityThreshold) {
                $bestSimilarity = $similarity;
                $bestMatch = $cacheKey;
            }
        }

        return $bestMatch;
    }

    /**
     * Calculate similarity between two strings
     * Returns value between 0 and 1
     *
     * Uses different algorithms based on string length:
     * - Levenshtein distance: Fast for short strings (<255 chars)
     * - similar_text: Better for longer strings, more accurate
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        // Normalize strings (lowercase, trimmed) for consistent comparison
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Use Levenshtein distance for short strings (faster)
        if (strlen($str1) < 255 && strlen($str2) < 255) {
            $distance = levenshtein($str1, $str2);
            $maxLength = max(strlen($str1), strlen($str2));
            // Convert distance to similarity (0 = identical, 1 = completely different)
            return $maxLength > 0 ? 1 - ($distance / $maxLength) : 1.0;
        }

        // Use similar_text for longer strings (more accurate)
        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }
}
```

### Why Semantic Caching Works

Semantic caching compares new prompts against cached prompts using string similarity algorithms:

1. **Normalization**: Converts prompts to lowercase and trims whitespace for consistent comparison
2. **Similarity calculation**: Uses Levenshtein distance (short strings) or `similar_text()` (long strings)
3. **Threshold matching**: Returns cached response if similarity ≥ threshold (default 0.85)
4. **Prompt mapping**: Stores prompt-to-cache-key mappings for efficient lookup

This enables cache hits for semantically equivalent queries like:

- "What is dependency injection?" vs "Explain dependency injection"
- "How do Laravel queues work?" vs "Tell me about Laravel queue system"

The similarity threshold balances cache hit rate with response relevance—higher thresholds (0.9+) ensure more accurate matches but fewer cache hits.

## Cache Invalidation Strategies

Proper cache invalidation prevents stale data from being served. Different strategies suit different use cases:

::: tip Invalidation Strategies

- **Time-based**: TTL expiration (simplest, automatic)
- **Tag-based**: Invalidate related caches together
- **Pattern-based**: Invalidate caches matching a pattern
- **Manual**: Explicit invalidation for specific keys
  :::

::: warning Stale Data Risk
Without proper invalidation, users may receive outdated responses. Always invalidate caches when underlying data changes or when responses become stale.
:::

```php
<?php
# filename: src/Services/CacheInvalidationService.php
declare(strict_types=1);

namespace App\Services;

use Psr\SimpleCache\CacheInterface;

class CacheInvalidationService
{
    public function __construct(
        private CacheInterface $cache
    ) {}

    /**
     * Invalidate all Claude caches
     */
    public function invalidateAll(): int
    {
        // This requires a cache implementation that supports pattern deletion
        // For Redis, you can use SCAN and DELETE
        $count = 0;

        if ($this->cache instanceof \Symfony\Component\Cache\Psr16Cache) {
            $adapter = $this->cache->getAdapter();
            if (method_exists($adapter, 'clear')) {
                // Clear with prefix
                $count = $adapter->deleteItems(['claude']);
            }
        }

        return $count;
    }

    /**
     * Invalidate caches older than specified age
     */
    public function invalidateOlderThan(int $seconds): int
    {
        // Implementation depends on cache backend
        // For demonstration, we'll track creation times
        return 0;
    }

    /**
     * Invalidate caches by tag
     */
    public function invalidateByTag(string $tag): int
    {
        $tagKey = "claude:tag:{$tag}";
        $cacheKeys = $this->cache->get($tagKey, []);

        foreach ($cacheKeys as $key) {
            $this->cache->delete($key);
        }

        $this->cache->delete($tagKey);

        return count($cacheKeys);
    }

    /**
     * Invalidate caches matching pattern
     */
    public function invalidateByPattern(string $pattern): int
    {
        // This requires Redis or similar with pattern support
        // For demonstration purposes only
        return 0;
    }
}
```

## Cache Warming Strategy

Pre-populate cache with common queries to improve initial performance. Cache warming runs during application startup or scheduled maintenance windows to ensure frequently accessed data is cached.

::: tip When to Warm Cache

- Application startup (pre-populate common queries)
- Scheduled maintenance (refresh stale caches)
- After cache invalidation (rebuild critical caches)
- Before high-traffic periods (preload expected queries)
  :::

```php
<?php
# filename: examples/03-cache-warming.php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Services\CachedClaudeService;
use ClaudePhp\ClaudePhp;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Setup
$redisConnection = RedisAdapter::createConnection('redis://localhost');
$cache = new Psr16Cache(new RedisAdapter($redisConnection));

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$claudeService = new CachedClaudeService($client, $cache);

// Common queries to warm the cache
$commonQueries = [
    'What is PHP?',
    'Explain dependency injection',
    'How do Laravel queues work?',
    'What are PHP attributes?',
    'Explain PSR-7 and PSR-15',
];

echo "Warming cache with " . count($commonQueries) . " common queries...\n\n";

foreach ($commonQueries as $index => $query) {
    echo ($index + 1) . ". {$query}\n";

    try {
        $response = $claudeService->generate($query, 'max_tokens' => 500);
        echo "   Cached: " . substr($response, 0, 60) . "...\n\n";
    } catch (\Exception $e) {
        echo "   Error: " . $e->getMessage() . "\n\n";
    }

    // Rate limiting
    if ($index < count($commonQueries) - 1) {
        sleep(1);
    }
}

echo "Cache warming completed!\n";
```

## Monitoring Cache Performance

Track cache metrics to optimize your caching strategy. Monitoring helps identify:

- Cache hit rates (higher is better)
- Cost savings from cache hits
- Cache size and memory usage
- Access patterns for optimization

::: tip Key Metrics to Track

- **Hit rate**: Percentage of requests served from cache (target: >70%)
- **Cost savings**: Estimated API cost reduction from caching
- **Average response time**: Compare cached vs uncached requests
- **Cache size**: Monitor memory usage and eviction rates
  :::

```php
<?php
# filename: src/Services/CacheMetricsService.php
declare(strict_types=1);

namespace App\Services;

use Psr\SimpleCache\CacheInterface;

class CacheMetricsService
{
    private const METRICS_KEY = 'claude:cache:metrics';

    public function __construct(
        private CacheInterface $cache
    ) {}

    public function recordHit(string $cacheKey): void
    {
        $this->incrementMetric('hits');
        $this->recordAccess($cacheKey, 'hit');
    }

    public function recordMiss(string $cacheKey): void
    {
        $this->incrementMetric('misses');
        $this->recordAccess($cacheKey, 'miss');
    }

    public function getMetrics(): array
    {
        $metrics = $this->cache->get(self::METRICS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'total_requests' => 0,
        ]);

        $metrics['hit_rate'] = $metrics['total_requests'] > 0
            ? $metrics['hits'] / $metrics['total_requests']
            : 0;

        return $metrics;
    }

    public function resetMetrics(): void
    {
        $this->cache->delete(self::METRICS_KEY);
    }

    private function incrementMetric(string $metric): void
    {
        $metrics = $this->cache->get(self::METRICS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'total_requests' => 0,
        ]);

        $metrics[$metric]++;
        $metrics['total_requests']++;

        $this->cache->set(self::METRICS_KEY, $metrics);
    }

    private function recordAccess(string $cacheKey, string $type): void
    {
        $accessLog = $this->cache->get('claude:cache:access_log', []);

        $accessLog[] = [
            'key' => $cacheKey,
            'type' => $type,
            'timestamp' => time(),
        ];

        // Keep only last 1000 entries
        if (count($accessLog) > 1000) {
            $accessLog = array_slice($accessLog, -1000);
        }

        $this->cache->set('claude:cache:access_log', $accessLog, 3600);
    }
}
```

## Complete Example: Multi-Strategy Caching

This example demonstrates a production-ready caching setup combining tiered caching with logging and monitoring:

```php
<?php
# filename: examples/04-complete-caching-example.php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Services\TieredCacheService;
use ClaudePhp\ClaudePhp;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Setup logging
$logger = new Logger('claude');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Setup Redis cache
$redisConnection = RedisAdapter::createConnection('redis://localhost');
$cache = new Psr16Cache(new RedisAdapter($redisConnection));

// Setup Claude client
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Create tiered cache service
$claudeService = new TieredCacheService(
    client: $client,
    persistentCache: $cache,
    logger: $logger,
    memoryTtl: 300,      // 5 minutes
    persistentTtl: 3600  // 1 hour
);

// Test the caching layers
$prompts = [
    'What is PHP?',
    'What is PHP?',           // Should hit memory cache
    'Explain Laravel',
];

foreach ($prompts as $i => $prompt) {
    echo "\n--- Request " . ($i + 1) . " ---\n";
    echo "Prompt: {$prompt}\n";

    $start = microtime(true);
    $response = $claudeService->generate($prompt, 'max_tokens' => 100);
    $duration = microtime(true) - $start;

    echo "Response: " . substr($response, 0, 80) . "...\n";
    echo "Duration: " . number_format($duration, 3) . "s\n";
}
```

## Cache Persistence and Fallback Strategies

When Redis becomes unavailable, applications need fallback strategies to maintain functionality:

```php
<?php
# filename: src/Services/FallbackCacheService.php
declare(strict_types=1);

namespace App\Services;

use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

class FallbackCacheService implements CacheInterface
{
    private bool $primaryAvailable = true;
    private array $fallbackMemory = [];

    public function __construct(
        private CacheInterface $primary,
        private ?LoggerInterface $logger = null
    ) {
        $this->checkPrimary();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        try {
            if ($this->primaryAvailable) {
                return $this->primary->get($key, $default);
            }
        } catch (\Exception $e) {
            $this->logger?->warning('Primary cache unavailable, using fallback', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            $this->primaryAvailable = false;
        }

        // Fall back to memory cache
        return $this->fallbackMemory[$key] ?? $default;
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        // Always try primary
        try {
            if ($this->primaryAvailable) {
                $this->primary->set($key, $value, $ttl);
            }
        } catch (\Exception $e) {
            $this->logger?->warning('Failed to write to primary cache', [
                'error' => $e->getMessage()
            ]);
            $this->primaryAvailable = false;
        }

        // Always update fallback
        $this->fallbackMemory[$key] = $value;

        // Limit fallback memory to prevent overflow
        if (count($this->fallbackMemory) > 1000) {
            $this->fallbackMemory = array_slice($this->fallbackMemory, -500);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        try {
            if ($this->primaryAvailable) {
                $this->primary->delete($key);
            }
        } catch (\Exception) {
            $this->primaryAvailable = false;
        }

        unset($this->fallbackMemory[$key]);
        return true;
    }

    public function clear(): bool
    {
        try {
            if ($this->primaryAvailable) {
                $this->primary->clear();
            }
        } catch (\Exception $e) {
            $this->primaryAvailable = false;
        }

        $this->fallbackMemory = [];
        return true;
    }

    public function has(string $key): bool
    {
        if (isset($this->fallbackMemory[$key])) {
            return true;
        }

        try {
            if ($this->primaryAvailable) {
                return $this->primary->has($key);
            }
        } catch (\Exception) {
            $this->primaryAvailable = false;
        }

        return false;
    }

    private function checkPrimary(): void
    {
        try {
            $this->primary->set('health_check', true, 60);
            $this->primaryAvailable = true;
        } catch (\Exception $e) {
            $this->logger?->warning('Primary cache health check failed', [
                'error' => $e->getMessage()
            ]);
            $this->primaryAvailable = false;
        }
    }
}
```

## Cache Compression for Storage Efficiency

Reduce Redis memory usage by compressing large cached responses:

```php
<?php
# filename: src/Services/CompressedCacheService.php
declare(strict_types=1);

namespace App\Services;

use Psr\SimpleCache\CacheInterface;

class CompressedCacheService
{
    private const COMPRESSION_THRESHOLD = 1024; // Compress if > 1KB

    public function __construct(
        private CacheInterface $cache,
        private int $compressionLevel = 6
    ) {}

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $serialized = json_encode($value);
        $size = strlen($serialized);

        // Only compress if larger than threshold
        if ($size > self::COMPRESSION_THRESHOLD) {
            $compressed = gzcompress($serialized, $this->compressionLevel);

            // Use compression if it actually saves space
            if (strlen($compressed) < $size) {
                $this->cache->set($key, [
                    'compressed' => true,
                    'data' => base64_encode($compressed),
                    'original_size' => $size,
                    'compressed_size' => strlen($compressed)
                ], $ttl);
                return true;
            }
        }

        // Store uncompressed
        $this->cache->set($key, [
            'compressed' => false,
            'data' => $serialized
        ], $ttl);

        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cached = $this->cache->get($key);

        if ($cached === null) {
            return $default;
        }

        if (isset($cached['compressed']) && $cached['compressed']) {
            $decompressed = gzuncompress(base64_decode($cached['data']));
            return json_decode($decompressed, true);
        }

        return json_decode($cached['data'], true);
    }
}
```

## Troubleshooting

**Redis connection fails?**

- Verify Redis is running: `redis-cli ping`
- Check connection string format: `redis://localhost:6379`
- Ensure Redis PHP extension is installed: `php -m | grep redis`
- Implement fallback cache strategies (see example above)

**Cache not persisting?**

- Check TTL values are not too short
- Verify Redis memory limits in `redis.conf`
- Ensure cache keys are deterministic (same input = same key)
- Monitor Redis memory: `redis-cli INFO memory`

**Semantic caching too slow?**

- Limit the number of cached prompts to compare against
- Use more efficient similarity algorithms (e.g., MinHash, SimHash)
- Consider using vector embeddings for better semantic matching
- Add sampling to compare only a subset of cached prompts

**Memory cache growing too large?**

- Implement proper LRU eviction
- Set reasonable memory cache size limits
- Monitor memory usage with `memory_get_usage()`
- Use compression for large cached values (see example above)

**Cache inconsistency across servers?**

- Use a centralized Redis instance (not local caches on each server)
- Implement cache invalidation events for distributed systems
- Use consistent cache key generation across all servers
- Consider using Redis Pub/Sub for cache invalidation events

## Distributed Cache Invalidation

For applications running on multiple servers, use Redis Pub/Sub to invalidate caches across all instances:

```php
<?php
# filename: src/Services/DistributedCacheInvalidation.php
declare(strict_types=1);

namespace App\Services;

use Redis;
use Psr\Log\LoggerInterface;

class DistributedCacheInvalidation
{
    private Redis $pubsub;
    private array $subscribers = [];

    public function __construct(
        private Redis $redis,
        private ?LoggerInterface $logger = null
    ) {
        $this->pubsub = new Redis();
        $this->pubsub->connect('localhost', 6379);
    }

    /**
     * Invalidate cache key across all application instances
     */
    public function invalidateKey(string $key): void
    {
        // Publish invalidation event
        $this->redis->publish('cache:invalidate', json_encode([
            'key' => $key,
            'timestamp' => time(),
            'server' => gethostname()
        ]));

        $this->logger?->info('Cache invalidation published', ['key' => $key]);
    }

    /**
     * Listen for invalidation events (run in background worker)
     */
    public function listen(callable $callback): void
    {
        $this->pubsub->subscribe(['cache:invalidate'], function($redis, $chan, $msg) use ($callback) {
            $data = json_decode($msg, true);

            $this->logger?->info('Cache invalidation received', [
                'key' => $data['key'] ?? null,
                'from_server' => $data['server'] ?? null
            ]);

            call_user_func($callback, $data);
        });
    }

    /**
     * Invalidate by pattern across cluster
     */
    public function invalidatePattern(string $pattern): void
    {
        // Publish pattern invalidation
        $this->redis->publish('cache:invalidate:pattern', json_encode([
            'pattern' => $pattern,
            'timestamp' => time()
        ]));

        $this->logger?->info('Pattern invalidation published', ['pattern' => $pattern]);
    }
}
```

## Exercises

### Exercise 1: Implement Cache Tags

**Goal**: Add tag-based cache invalidation to your `CachedClaudeService`

Create an enhanced version that supports cache tagging:

- Add a `generateWithTags()` method that accepts an array of tags
- Store tag-to-key mappings in Redis
- Implement `invalidateByTag()` method to clear all caches with a specific tag
- Test by caching multiple prompts with tags and invalidating by tag

**Validation**: Verify that invalidating a tag clears all related caches:

```php
$service->generateWithTags('What is PHP?', tags: ['php', 'basics']);
$service->generateWithTags('What is Laravel?', tags: ['laravel', 'php']);
$service->invalidateByTag('php'); // Should clear both caches
```

### Exercise 2: Cache Hit Rate Dashboard

**Goal**: Build a simple monitoring dashboard for cache performance

Create a `CacheDashboard` class that:

- Tracks hit/miss rates over time windows (last hour, day, week)
- Calculates cost savings based on cache hits
- Provides cache size statistics
- Exports metrics as JSON for API endpoints

**Validation**: Run multiple requests and verify metrics are tracked correctly:

```php
$dashboard = new CacheDashboard($cache);
// Make requests...
$metrics = $dashboard->getMetrics();
assert($metrics['hit_rate'] > 0);
```

### Exercise 3: Adaptive TTL Strategy

**Goal**: Implement dynamic TTL based on prompt frequency

Create a caching service that:

- Tracks how often each prompt is requested
- Increases TTL for frequently accessed prompts
- Decreases TTL for rarely accessed prompts
- Maintains a minimum and maximum TTL range

**Validation**: Verify that frequently accessed prompts have longer TTLs:

```php
$service->generate('Popular query'); // Request 1
$service->generate('Popular query'); // Request 2
$service->generate('Popular query'); // Request 3
// TTL should increase after multiple accesses
```

## Further Reading

- **[Claude-PHP-SDK on GitHub](https://github.com/claude-php/Claude-PHP-SDK)** — The community-maintained PHP SDK
- **[Anthropic Prompt Caching Documentation](https://docs.claude.com/en/docs/capabilities/prompt-caching)** — Official guide to prompt caching
- **[PSR-16: Simple Cache](https://www.php-fig.org/psr/psr-16/)** — PHP-FIG cache interface standard
- **[Redis Caching Patterns](https://redis.io/docs/manual/patterns/)** — Best practices for Redis caching
- **[Symfony Cache Component](https://symfony.com/doc/current/components/cache.html)** — PSR-6/PSR-16 implementation
- **[See Chapter 37: Monitoring and Observability](/series/claude-php-developers/chapters/37-monitoring-observability)** — Track cache performance metrics
- **[See Chapter 39: Cost Optimization](/series/claude-php-developers/chapters/39-cost-optimization)** — Cache as cost reduction strategy


---

Continue to [Chapter 19: Queue-Based Processing with Laravel](/series/claude-php-developers/chapters/19-queue-processing-laravel) to handle long-running AI tasks asynchronously.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 18 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-18)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-18
composer install
# Ensure Redis is running
redis-cli ping
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/prompt-cache.php
```
