---
title: "18: Caching Strategies for API Calls"
description: "Optimize Claude API costs and performance with prompt caching, response caching using Redis, cache invalidation strategies, and semantic similarity caching."
series: "claude-php-developers"
chapter: 18
order: 18
difficulty: "Expert"
prerequisites:
  - "Chapter 17: Building a Claude Service Class"
  - "Redis familiarity"
  - "Cache invalidation concepts"
  - "Understanding of cache TTL patterns"
---

![18: Caching Strategies for API Calls](/images/claude-php/chapter-18-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 18</span>
</div>

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

## Cache Layer Architecture

A comprehensive caching strategy uses multiple layers:

```
Request
  ↓
1. In-Memory Cache (fastest, short-lived)
  ↓ (miss)
2. Redis Cache (fast, persistent)
  ↓ (miss)
3. Semantic Cache (similarity-based)
  ↓ (miss)
4. Claude API (slowest, most expensive)
  ↓
Response → Cache at all layers
```

## Anthropic's Prompt Caching

Anthropic offers native prompt caching to reduce costs for repeated context:

```php
<?php
# filename: examples/01-prompt-caching.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Large context that will be cached
$documentationContext = file_get_contents(__DIR__ . '/large-documentation.txt');

// First request - full cost
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
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
]);

echo "First Request:\n";
echo "Response: " . $response1->content[0]->text . "\n";
echo "Input tokens: {$response1->usage->inputTokens}\n";
echo "Cache creation tokens: " . ($response1->usage->cacheCreationInputTokens ?? 0) . "\n";
echo "Cache read tokens: " . ($response1->usage->cacheReadInputTokens ?? 0) . "\n\n";

// Second request within 5 minutes - uses cached context
sleep(2);

$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
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
]);

echo "Second Request (with cache hit):\n";
echo "Response: " . $response2->content[0]->text . "\n";
echo "Input tokens: {$response2->usage->inputTokens}\n";
echo "Cache creation tokens: " . ($response2->usage->cacheCreationInputTokens ?? 0) . "\n";
echo "Cache read tokens: " . ($response2->usage->cacheReadInputTokens ?? 0) . "\n\n";

// Calculate savings
$costSavings = ($response2->usage->cacheReadInputTokens ?? 0) * 0.90; // 90% discount
echo "Cache read tokens at 90% discount: significant cost savings!\n";
```

## Response Caching with Redis

Cache complete API responses for identical requests:

```php
<?php
# filename: src/Services/CachedClaudeService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use Anthropic\Contracts\ClientContract;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class CachedClaudeService implements ClaudeServiceInterface
{
    public function __construct(
        private ClientContract $client,
        private CacheInterface $cache,
        private ?LoggerInterface $logger = null,
        private int $defaultTtl = 3600,
        private string $defaultModel = 'claude-sonnet-4-20250514'
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
        $response = $this->client->messages()->create([
            'model' => $model ?? $this->defaultModel,
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

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

        $response = $this->client->messages()->create([
            'model' => $options['model'] ?? $this->defaultModel,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $result = [
            'text' => $response->content[0]->text,
            'metadata' => [
                'id' => $response->id,
                'model' => $response->model,
                'stop_reason' => $response->stopReason,
                'usage' => [
                    'input_tokens' => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
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
        $stream = $this->client->messages()->createStreamed([
            'model' => $options['model'] ?? $this->defaultModel,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta') {
                $callback($event->delta->text ?? '');
            }
        }
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    public function healthCheck(): bool
    {
        try {
            $response = $this->client->messages()->create([
                'model' => $this->defaultModel,
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping']
                ]
            ]);

            return $response->content[0]->text !== null;
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
     */
    private function generateCacheKey(array $params): string
    {
        ksort($params);
        return 'claude:' . md5(json_encode($params));
    }
}
```

## Advanced Redis Caching Implementation

```php
<?php
# filename: examples/02-redis-caching.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Connect to Redis
$redisConnection = RedisAdapter::createConnection('redis://localhost');
$redisAdapter = new RedisAdapter($redisConnection);
$cache = new Psr16Cache($redisAdapter);

// Create cached service
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

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

Combine in-memory and Redis for optimal performance:

```php
<?php
# filename: src/Services/TieredCacheService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use Anthropic\Contracts\ClientContract;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class TieredCacheService implements ClaudeServiceInterface
{
    private array $memoryCache = [];
    private int $memoryCacheSize = 100;

    public function __construct(
        private ClientContract $client,
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

        $response = $this->client->messages()->create([
            'model' => $model ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

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
        return 'claude:' . md5(json_encode($filtered));
    }
}
```

## Semantic Similarity Caching

Cache responses based on prompt similarity, not just exact matches:

```php
<?php
# filename: src/Services/SemanticCacheService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use Anthropic\Contracts\ClientContract;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

class SemanticCacheService implements ClaudeServiceInterface
{
    private const SIMILARITY_THRESHOLD = 0.85;

    public function __construct(
        private ClientContract $client,
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
        $response = $this->client->messages()->create([
            'model' => $model ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $maxTokens ?? 4096,
            'temperature' => $temperature ?? 1.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

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
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        // Normalize strings
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Use Levenshtein distance for short strings
        if (strlen($str1) < 255 && strlen($str2) < 255) {
            $distance = levenshtein($str1, $str2);
            $maxLength = max(strlen($str1), strlen($str2));
            return 1 - ($distance / $maxLength);
        }

        // Use similar_text for longer strings
        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }
}
```

## Cache Invalidation Strategies

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

Pre-populate cache with common queries:

```php
<?php
# filename: examples/03-cache-warming.php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Services\CachedClaudeService;
use Anthropic\Anthropic;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

// Setup
$redisConnection = RedisAdapter::createConnection('redis://localhost');
$cache = new Psr16Cache(new RedisAdapter($redisConnection));

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

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
        $response = $claudeService->generate($query, maxTokens: 500);
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

```php
<?php
# filename: examples/04-complete-caching-example.php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Services\TieredCacheService;
use Anthropic\Anthropic;
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
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

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
    $response = $claudeService->generate($prompt, maxTokens: 100);
    $duration = microtime(true) - $start;

    echo "Response: " . substr($response, 0, 80) . "...\n";
    echo "Duration: " . number_format($duration, 3) . "s\n";
}
```

## Troubleshooting

**Redis connection fails?**
- Verify Redis is running: `redis-cli ping`
- Check connection string format: `redis://localhost:6379`
- Ensure Redis PHP extension is installed: `php -m | grep redis`

**Cache not persisting?**
- Check TTL values are not too short
- Verify Redis memory limits in `redis.conf`
- Ensure cache keys are deterministic (same input = same key)

**Semantic caching too slow?**
- Limit the number of cached prompts to compare against
- Use more efficient similarity algorithms (e.g., MinHash, SimHash)
- Consider using vector embeddings for better semantic matching

**Memory cache growing too large?**
- Implement proper LRU eviction
- Set reasonable memory cache size limits
- Monitor memory usage with `memory_get_usage()`

## Key Takeaways

- ✓ Anthropic's prompt caching reduces costs by 90% for repeated context
- ✓ Response caching eliminates redundant API calls entirely
- ✓ Tiered caching (memory + Redis) provides optimal performance
- ✓ Semantic caching enables fuzzy matching for similar prompts
- ✓ Cache invalidation strategies prevent stale data
- ✓ Monitoring cache hit rates helps optimize strategy
- ✓ Cache warming pre-populates frequently used queries

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="18"
  label="You've mastered Claude API caching strategies!"
/>

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
php examples/01-prompt-caching.php
```
