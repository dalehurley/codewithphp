<?php

declare(strict_types=1);

/**
 * Multi-Level Cache Implementation
 *
 * Implements a production-ready multi-level caching strategy:
 * - L1: In-memory (request-scoped, fastest)
 * - L2: APCu (shared across processes, fast)
 * - L3: Redis (distributed, persistent)
 *
 * Time Complexity: O(1) for all operations
 * Space Complexity: Depends on cache size and levels
 */
class MultiLevelCache
{
    private array $l1Cache = [];  // Request-level memory
    private ?object $l2Cache = null;  // APCu (simulated)
    private ?object $l3Cache = null;  // Redis (simulated)

    private array $stats = [
        'l1_hits' => 0,
        'l2_hits' => 0,
        'l3_hits' => 0,
        'misses' => 0,
        'sets' => 0
    ];

    public function __construct(?object $redis = null, bool $useAPCu = true)
    {
        $this->l3Cache = $redis;

        // Check if APCu is available
        if ($useAPCu && function_exists('apcu_enabled') && apcu_enabled()) {
            $this->l2Cache = (object)['enabled' => true];
        }
    }

    /**
     * Get value from cache, checking all levels
     *
     * @param string $key Cache key
     * @param callable|null $fallback Function to call on cache miss
     * @return mixed Value or null if not found
     */
    public function get(string $key, ?callable $fallback = null): mixed
    {
        // L1: In-memory cache (fastest - 0.01ms)
        if (isset($this->l1Cache[$key])) {
            $this->stats['l1_hits']++;
            return $this->l1Cache[$key];
        }

        // L2: APCu cache (fast - 0.1ms)
        if ($this->l2Cache) {
            $value = $this->getFromL2($key);
            if ($value !== null) {
                $this->stats['l2_hits']++;
                $this->l1Cache[$key] = $value;  // Populate L1
                return $value;
            }
        }

        // L3: Redis cache (moderate - 2ms)
        if ($this->l3Cache) {
            $value = $this->getFromL3($key);
            if ($value !== null) {
                $this->stats['l3_hits']++;
                $this->populateUpperLevels($key, $value);
                return $value;
            }
        }

        // Cache miss - use fallback
        if ($fallback) {
            $value = $fallback();
            if ($value !== null) {
                $this->set($key, $value);
            }
            return $value;
        }

        $this->stats['misses']++;
        return null;
    }

    /**
     * Set value in all cache levels
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return void
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->stats['sets']++;

        // Set in all levels
        $this->l1Cache[$key] = $value;

        if ($this->l2Cache) {
            $this->setInL2($key, $value, $ttl);
        }

        if ($this->l3Cache) {
            $this->setInL3($key, $value, $ttl);
        }
    }

    /**
     * Delete from all cache levels
     *
     * @param string $key Cache key
     * @return void
     */
    public function delete(string $key): void
    {
        unset($this->l1Cache[$key]);

        if ($this->l2Cache) {
            $this->deleteFromL2($key);
        }

        if ($this->l3Cache) {
            $this->deleteFromL3($key);
        }
    }

    /**
     * Clear all cache levels
     *
     * @return void
     */
    public function clear(): void
    {
        $this->l1Cache = [];

        if ($this->l2Cache) {
            $this->clearL2();
        }

        if ($this->l3Cache) {
            $this->clearL3();
        }

        $this->stats = [
            'l1_hits' => 0,
            'l2_hits' => 0,
            'l3_hits' => 0,
            'misses' => 0,
            'sets' => 0
        ];
    }

    /**
     * Get cache statistics
     *
     * @return array Statistics including hit rates
     */
    public function getStats(): array
    {
        $total = $this->stats['l1_hits']
               + $this->stats['l2_hits']
               + $this->stats['l3_hits']
               + $this->stats['misses'];

        return [
            'l1_hits' => $this->stats['l1_hits'],
            'l2_hits' => $this->stats['l2_hits'],
            'l3_hits' => $this->stats['l3_hits'],
            'misses' => $this->stats['misses'],
            'sets' => $this->stats['sets'],
            'total_requests' => $total,
            'l1_hit_rate' => $total > 0 ? $this->stats['l1_hits'] / $total : 0,
            'l2_hit_rate' => $total > 0 ? $this->stats['l2_hits'] / $total : 0,
            'l3_hit_rate' => $total > 0 ? $this->stats['l3_hits'] / $total : 0,
            'overall_hit_rate' => $total > 0 ? 1 - ($this->stats['misses'] / $total) : 0,
            'l1_size' => count($this->l1Cache)
        ];
    }

    // L2 (APCu) operations
    private function getFromL2(string $key): mixed
    {
        if (function_exists('apcu_fetch')) {
            $value = apcu_fetch($key, $success);
            return $success ? $value : null;
        }
        return null;
    }

    private function setInL2(string $key, mixed $value, int $ttl): void
    {
        if (function_exists('apcu_store')) {
            apcu_store($key, $value, $ttl);
        }
    }

    private function deleteFromL2(string $key): void
    {
        if (function_exists('apcu_delete')) {
            apcu_delete($key);
        }
    }

    private function clearL2(): void
    {
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
    }

    // L3 (Redis) operations
    private function getFromL3(string $key): mixed
    {
        if (!$this->l3Cache) {
            return null;
        }

        // Simulated Redis get
        if (method_exists($this->l3Cache, 'get')) {
            $value = $this->l3Cache->get($key);
            return $value !== false ? unserialize($value) : null;
        }

        return null;
    }

    private function setInL3(string $key, mixed $value, int $ttl): void
    {
        if (!$this->l3Cache) {
            return;
        }

        // Simulated Redis setex
        if (method_exists($this->l3Cache, 'setex')) {
            $this->l3Cache->setex($key, $ttl, serialize($value));
        }
    }

    private function deleteFromL3(string $key): void
    {
        if (!$this->l3Cache) {
            return;
        }

        if (method_exists($this->l3Cache, 'del')) {
            $this->l3Cache->del($key);
        }
    }

    private function clearL3(): void
    {
        if (!$this->l3Cache) {
            return;
        }

        if (method_exists($this->l3Cache, 'flushDB')) {
            $this->l3Cache->flushDB();
        }
    }

    private function populateUpperLevels(string $key, mixed $value): void
    {
        $this->l1Cache[$key] = $value;

        if ($this->l2Cache) {
            $this->setInL2($key, $value, 300);
        }
    }
}

// Mock Redis class for demonstration
class MockRedis
{
    private array $data = [];

    public function get(string $key): string|false
    {
        return $this->data[$key] ?? false;
    }

    public function setex(string $key, int $ttl, string $value): bool
    {
        $this->data[$key] = $value;
        return true;
    }

    public function del(string $key): int
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            return 1;
        }
        return 0;
    }

    public function flushDB(): bool
    {
        $this->data = [];
        return true;
    }
}

// =============================================================================
// EXAMPLES AND USAGE
// =============================================================================

echo "Multi-Level Cache Implementation\n";
echo str_repeat("=", 70) . "\n\n";

// Example 1: Basic multi-level caching
echo "Example 1: Multi-Level Cache Behavior\n";
echo "-" . str_repeat("-", 69) . "\n";

$redis = new MockRedis();
$cache = new MultiLevelCache($redis, false);  // Disable APCu for demo

// First access: Cache miss, fetches from database
echo "Request 1 (cache miss):\n";
$user1 = $cache->get('user:123', function() {
    echo "  → Fetching from database...\n";
    return ['id' => 123, 'name' => 'John Doe', 'email' => 'john@example.com'];
});
echo "  Result: {$user1['name']}\n\n";

// Second access: L1 hit (in-memory)
echo "Request 2 (L1 cache hit):\n";
$user2 = $cache->get('user:123');
echo "  Result: {$user2['name']} (from L1)\n\n";

// Clear L1, third access: L3 hit (Redis)
echo "Request 3 (after clearing L1):\n";
$cache->l1Cache = [];  // Simulate new request
$user3 = $cache->get('user:123');
echo "  Result: {$user3['name']} (from L3, populated L1)\n\n";

$stats1 = $cache->getStats();
echo "Cache Statistics:\n";
echo "  L1 hits: {$stats1['l1_hits']} (" . number_format($stats1['l1_hit_rate'] * 100, 1) . "%)\n";
echo "  L3 hits: {$stats1['l3_hits']} (" . number_format($stats1['l3_hit_rate'] * 100, 1) . "%)\n";
echo "  Misses:  {$stats1['misses']}\n";
echo "  Overall hit rate: " . number_format($stats1['overall_hit_rate'] * 100, 1) . "%\n\n";

// Example 2: Performance comparison
echo "Example 2: Performance by Cache Level\n";
echo "-" . str_repeat("-", 69) . "\n";

$redis = new MockRedis();
$cache = new MultiLevelCache($redis, false);

// Warm up cache
$cache->set('test:data', ['value' => 'test data']);

// Benchmark L1 (in-memory)
$iterations = 10000;
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $cache->get('test:data');
}
$l1Time = (microtime(true) - $start) * 1000;

echo "10,000 operations:\n";
echo "  L1 (in-memory): " . number_format($l1Time, 2) . " ms";
echo " (" . number_format($iterations / ($l1Time / 1000), 0) . " ops/sec)\n";

// Simulate L3 access (Redis)
$cache->l1Cache = [];  // Clear L1
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {  // Fewer iterations for L3
    $cache->get('test:data');
}
$l3Time = (microtime(true) - $start) * 1000;

echo "  L3 (Redis):     " . number_format($l3Time, 2) . " ms for 1,000 ops";
echo " (" . number_format(1000 / ($l3Time / 1000), 0) . " ops/sec)\n";
echo "\nL1 is ~" . number_format(($l3Time / 1000) / ($l1Time / 10000), 1) . "x faster than L3\n\n";

// Example 3: Real-world simulation
echo "Example 3: Simulate Production Access Pattern\n";
echo "-" . str_repeat("-", 69) . "\n";

$cache = new MultiLevelCache(new MockRedis(), false);

// Simulate 100 requests with realistic access pattern
$requests = [];
for ($i = 0; $i < 100; $i++) {
    // 80% requests hit popular items (good L1 hit rate)
    // 15% requests hit less common items (L3 hit)
    // 5% requests are unique (cache miss)
    $rand = rand(1, 100);

    if ($rand <= 80) {
        $requests[] = 'user:' . rand(1, 5);  // Popular items
    } elseif ($rand <= 95) {
        $requests[] = 'user:' . rand(6, 20);  // Less common
    } else {
        $requests[] = 'user:' . rand(100, 200);  // Rare
    }
}

foreach ($requests as $key) {
    $cache->get($key, function() use ($key) {
        return ['id' => $key, 'data' => "Data for $key"];
    });
}

$stats = $cache->getStats();
echo "100 simulated requests:\n";
echo "  L1 hits: {$stats['l1_hits']} (" . number_format($stats['l1_hit_rate'] * 100, 1) . "%)\n";
echo "  L3 hits: {$stats['l3_hits']} (" . number_format($stats['l3_hit_rate'] * 100, 1) . "%)\n";
echo "  Misses:  {$stats['misses']} (" . number_format(($stats['misses'] / $stats['total_requests']) * 100, 1) . "%)\n";
echo "  Overall hit rate: " . number_format($stats['overall_hit_rate'] * 100, 1) . "%\n\n";

// Example 4: Cost savings analysis
echo "Example 4: Performance & Cost Impact\n";
echo "-" . str_repeat("-", 69) . "\n";

echo "Average latencies:\n";
echo "  L1 (in-memory): 0.01 ms\n";
echo "  L2 (APCu):      0.1 ms\n";
echo "  L3 (Redis):     2 ms\n";
echo "  Database:       50 ms\n\n";

$l1Rate = $stats['l1_hit_rate'];
$l3Rate = $stats['l3_hit_rate'];
$missRate = $stats['misses'] / $stats['total_requests'];

$avgLatency = ($l1Rate * 0.01) + ($l3Rate * 2) + ($missRate * 50);
$dbOnlyLatency = 50;

echo "For 1,000,000 requests/hour:\n";
echo "  Without caching: " . number_format($dbOnlyLatency * 1000000 / 1000) . " seconds of DB time\n";
echo "  With multi-level: " . number_format($avgLatency * 1000000 / 1000) . " seconds total\n";
echo "  Time saved: " . number_format(($dbOnlyLatency - $avgLatency) * 1000000 / 1000) . " seconds\n";
echo "  Improvement: " . number_format(($dbOnlyLatency / $avgLatency), 1) . "x faster\n";

echo "\n✓ All examples completed successfully!\n";
