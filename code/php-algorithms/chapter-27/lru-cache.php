<?php

declare(strict_types=1);

/**
 * LRU (Least Recently Used) Cache Implementation
 *
 * Efficient cache that automatically evicts the least recently used items
 * when capacity is reached.
 *
 * Time Complexity: O(1) for get and set operations
 * Space Complexity: O(capacity)
 */
class LRUCache
{
    private array $cache = [];
    private array $keys = [];  // Track access order
    private int $capacity;
    private int $hits = 0;
    private int $misses = 0;

    /**
     * @param int $capacity Maximum number of items to store
     */
    public function __construct(int $capacity)
    {
        if ($capacity <= 0) {
            throw new InvalidArgumentException("Capacity must be positive");
        }

        $this->capacity = $capacity;
    }

    /**
     * Get value from cache
     *
     * @param string $key Cache key
     * @return mixed|null Value or null if not found
     */
    public function get(string $key): mixed
    {
        if (!isset($this->cache[$key])) {
            $this->misses++;
            return null;
        }

        // Move to end (most recently used)
        $this->updateAccessOrder($key);
        $this->hits++;

        return $this->cache[$key];
    }

    /**
     * Set value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to store
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        if (isset($this->cache[$key])) {
            // Update existing
            $this->cache[$key] = $value;
            $this->updateAccessOrder($key);
        } else {
            // Add new
            if (count($this->cache) >= $this->capacity) {
                // Evict least recently used
                $lruKey = array_shift($this->keys);
                unset($this->cache[$lruKey]);
            }

            $this->cache[$key] = $value;
            $this->keys[] = $key;
        }
    }

    /**
     * Check if key exists in cache
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * Remove item from cache
     *
     * @param string $key Cache key
     * @return bool True if removed, false if not found
     */
    public function delete(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        unset($this->cache[$key]);
        $this->keys = array_values(array_filter($this->keys, fn($k) => $k !== $key));

        return true;
    }

    /**
     * Clear all cache entries
     *
     * @return void
     */
    public function clear(): void
    {
        $this->cache = [];
        $this->keys = [];
        $this->hits = 0;
        $this->misses = 0;
    }

    /**
     * Update access order (move key to end)
     *
     * @param string $key Cache key
     * @return void
     */
    private function updateAccessOrder(string $key): void
    {
        // Remove from current position
        $this->keys = array_values(array_filter($this->keys, fn($k) => $k !== $key));

        // Add to end (most recent)
        $this->keys[] = $key;
    }

    /**
     * Get cache statistics
     *
     * @return array{size: int, capacity: int, hits: int, misses: int, hit_rate: float, access_order: array}
     */
    public function getStats(): array
    {
        $total = $this->hits + $this->misses;

        return [
            'size' => count($this->cache),
            'capacity' => $this->capacity,
            'hits' => $this->hits,
            'misses' => $this->misses,
            'hit_rate' => $total > 0 ? $this->hits / $total : 0,
            'access_order' => $this->keys
        ];
    }

    /**
     * Get current cache size
     *
     * @return int Number of items in cache
     */
    public function size(): int
    {
        return count($this->cache);
    }

    /**
     * Get capacity
     *
     * @return int Maximum capacity
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * Get all keys in access order
     *
     * @return array<string> Keys from LRU to MRU
     */
    public function getAccessOrder(): array
    {
        return $this->keys;
    }

    /**
     * Get items that would be evicted next
     *
     * @param int $count Number of items
     * @return array<string> Keys that would be evicted
     */
    public function getEvictionCandidates(int $count = 1): array
    {
        return array_slice($this->keys, 0, min($count, count($this->keys)));
    }
}

// =============================================================================
// EXAMPLES AND USAGE
// =============================================================================

echo "LRU (Least Recently Used) Cache\n";
echo str_repeat("=", 70) . "\n\n";

// Example 1: Basic usage
echo "Example 1: Basic LRU Cache Operations\n";
echo "-" . str_repeat("-", 69) . "\n";

$cache = new LRUCache(3);

// Add items
$cache->set('a', 'Value A');
$cache->set('b', 'Value B');
$cache->set('c', 'Value C');

echo "Added 3 items (capacity = 3)\n";
echo "Cache size: " . $cache->size() . "\n";
echo "Access order: " . implode(', ', $cache->getAccessOrder()) . "\n\n";

// Access 'a' (moves it to end)
echo "Accessing 'a': " . $cache->get('a') . "\n";
echo "Access order: " . implode(', ', $cache->getAccessOrder()) . "\n\n";

// Add new item (should evict 'b')
$cache->set('d', 'Value D');
echo "Added 'd' (evicted least recently used)\n";
echo "Has 'b': " . ($cache->has('b') ? 'yes' : 'no') . " (evicted!)\n";
echo "Access order: " . implode(', ', $cache->getAccessOrder()) . "\n\n";

// Example 2: Cache hit rate tracking
echo "Example 2: Cache Hit Rate\n";
echo "-" . str_repeat("-", 69) . "\n";

$cache2 = new LRUCache(5);

// Simulate database queries
$queries = ['user:1', 'user:2', 'user:1', 'user:3', 'user:1', 'user:2', 'user:4'];

foreach ($queries as $query) {
    $result = $cache2->get($query);

    if ($result === null) {
        // Cache miss - simulate database query
        $result = "Data for $query from DB";
        $cache2->set($query, $result);
        echo "MISS: $query (fetched from DB)\n";
    } else {
        echo "HIT : $query (from cache)\n";
    }
}

$stats = $cache2->getStats();
echo "\nCache Statistics:\n";
echo "  Hits: {$stats['hits']}\n";
echo "  Misses: {$stats['misses']}\n";
echo "  Hit Rate: " . number_format($stats['hit_rate'] * 100, 1) . "%\n";
echo "  Size: {$stats['size']}/{$stats['capacity']}\n\n";

// Example 3: Eviction tracking
echo "Example 3: Track Evictions\n";
echo "-" . str_repeat("-", 69) . "\n";

$cache3 = new LRUCache(3);

$cache3->set('session:1', ['user_id' => 1, 'name' => 'Alice']);
$cache3->set('session:2', ['user_id' => 2, 'name' => 'Bob']);
$cache3->set('session:3', ['user_id' => 3, 'name' => 'Charlie']);

echo "Current cache (capacity 3):\n";
foreach ($cache3->getAccessOrder() as $key) {
    echo "  $key\n";
}

echo "\nNext eviction candidate: " . $cache3->getEvictionCandidates(1)[0] . "\n";

$cache3->set('session:4', ['user_id' => 4, 'name' => 'Diana']);

echo "\nAfter adding session:4:\n";
foreach ($cache3->getAccessOrder() as $key) {
    echo "  $key\n";
}
echo "\n";

// Example 4: Performance benchmark
echo "Example 4: Performance Benchmark\n";
echo "-" . str_repeat("-", 69) . "\n";

$sizes = [10, 100, 1000, 10000];

foreach ($sizes as $size) {
    $cache = new LRUCache($size);

    // Fill cache
    $start = microtime(true);
    for ($i = 0; $i < $size; $i++) {
        $cache->set("key:$i", "value:$i");
    }
    $fillTime = (microtime(true) - $start) * 1000;

    // Test access pattern (80% hits)
    $start = microtime(true);
    for ($i = 0; $i < $size * 2; $i++) {
        $key = rand(0, 100) < 80 ? "key:" . rand(0, $size - 1) : "key:" . rand($size, $size * 2);
        $cache->get($key);
    }
    $accessTime = (microtime(true) - $start) * 1000;

    $stats = $cache->getStats();

    echo sprintf(
        "Capacity %5d: Fill = %6.2f ms, Access = %6.2f ms, Hit Rate = %5.1f%%\n",
        $size,
        $fillTime,
        $accessTime,
        $stats['hit_rate'] * 100
    );
}
echo "\n";

// Example 5: Real-world use case - Memoized function
echo "Example 5: Function Memoization with LRU\n";
echo "-" . str_repeat("-", 69) . "\n";

$fibCache = new LRUCache(50);

function fibonacci(int $n, LRUCache $cache): int
{
    $key = "fib:$n";

    $cached = $cache->get($key);
    if ($cached !== null) {
        return $cached;
    }

    if ($n <= 1) {
        $result = $n;
    } else {
        $result = fibonacci($n - 1, $cache) + fibonacci($n - 2, $cache);
    }

    $cache->set($key, $result);
    return $result;
}

echo "Calculating Fibonacci numbers with LRU cache:\n";

for ($n = 30; $n <= 35; $n++) {
    $start = microtime(true);
    $result = fibonacci($n, $fibCache);
    $time = (microtime(true) - $start) * 1000;

    echo "fib($n) = " . number_format($result) . " (time: " . number_format($time, 3) . " ms)\n";
}

$stats = $fibCache->getStats();
echo "\nFibonacci cache stats:\n";
echo "  Size: {$stats['size']}/{$stats['capacity']}\n";
echo "  Hit rate: " . number_format($stats['hit_rate'] * 100, 1) . "%\n";

echo "\n✓ All examples completed successfully!\n";
