<?php

declare(strict_types=1);

require_once __DIR__ . '/LRUCache.php';

use ComputerScience\Capstone\LRUCache;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  LRU Cache - Capstone Project Demonstration              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// =============================================================================
// BASIC OPERATIONS
// =============================================================================

echo "📦 BASIC OPERATIONS\n";
echo str_repeat("─", 60) . "\n\n";

$cache = new LRUCache(capacity: 3);

echo "Created LRU Cache with capacity 3\n";
echo "$cache\n\n";

echo "1. Adding items:\n";
$cache->put(1, 'apple');
echo "   put(1, 'apple')  → $cache\n";

$cache->put(2, 'banana');
echo "   put(2, 'banana') → $cache\n";

$cache->put(3, 'cherry');
echo "   put(3, 'cherry') → $cache\n\n";

echo "2. Accessing items:\n";
echo "   get(1) → " . $cache->get(1) . "\n";
echo "   get(2) → " . $cache->get(2) . "\n";
echo "   get(3) → " . $cache->get(3) . "\n\n";

// =============================================================================
// EVICTION POLICY
// =============================================================================

echo "\n🗑️  EVICTION POLICY (Least Recently Used)\n";
echo str_repeat("─", 60) . "\n\n";

$cache = new LRUCache(3);

$cache->put(1, 'one');
$cache->put(2, 'two');
$cache->put(3, 'three');
echo "Initial state: $cache\n";
echo "Keys (MRU→LRU): " . implode(' → ', $cache->keys()) . "\n\n";

echo "Access key 1 (moves to front):\n";
$cache->get(1);
echo "After get(1): $cache\n";
echo "Keys (MRU→LRU): " . implode(' → ', $cache->keys()) . "\n\n";

echo "Add key 4 (cache is full, must evict LRU):\n";
$cache->put(4, 'four');
echo "After put(4, 'four'): $cache\n";
echo "Keys (MRU→LRU): " . implode(' → ', $cache->keys()) . "\n";
echo "Key 2 was evicted (it was LRU)\n\n";

echo "Verify key 2 is gone:\n";
echo "get(2) → " . ($cache->get(2) ?? 'null (evicted)') . "\n\n";

// =============================================================================
// UPDATE OPERATIONS
// =============================================================================

echo "\n♻️  UPDATE OPERATIONS\n";
echo str_repeat("─", 60) . "\n\n";

$cache = new LRUCache(3);

$cache->put(1, 'apple');
$cache->put(2, 'banana');
$cache->put(3, 'cherry');
echo "Initial: $cache\n\n";

echo "Update existing key (moves to front):\n";
$cache->put(2, 'BANANA');
echo "After put(2, 'BANANA'): $cache\n";
echo "Keys (MRU→LRU): " . implode(' → ', $cache->keys()) . "\n\n";

// =============================================================================
// CACHE STATISTICS
// =============================================================================

echo "\n📊 CACHE STATISTICS\n";
echo str_repeat("─", 60) . "\n\n";

$cache = new LRUCache(3);

// Populate cache
$cache->put(1, 'one');
$cache->put(2, 'two');
$cache->put(3, 'three');

echo "Simulating cache access pattern:\n";
$operations = [
    [1, 'Hit'],
    [2, 'Hit'],
    [999, 'Miss'],
    [1, 'Hit'],
    [888, 'Miss'],
    [3, 'Hit'],
];

foreach ($operations as [$key, $expected]) {
    $result = $cache->get($key);
    $actual = $result !== null ? 'Hit' : 'Miss';
    $icon = $actual === 'Hit' ? '✓' : '✗';
    echo "   $icon get($key) → " . ($result ?? 'null') . " [$actual]\n";
}

echo "\n";
$stats = $cache->getStats();
echo "Final Statistics:\n";
echo "   Hits:     {$stats['hits']}\n";
echo "   Misses:   {$stats['misses']}\n";
echo "   Hit Rate: " . number_format($stats['hitRate'] * 100, 1) . "%\n";
echo "   Size:     {$stats['size']}/{$stats['capacity']}\n\n";

// =============================================================================
// REAL-WORLD SIMULATION: DATABASE QUERY CACHE
// =============================================================================

echo "\n🌐 REAL-WORLD SIMULATION: Database Query Cache\n";
echo str_repeat("─", 60) . "\n\n";

class DatabaseCache
{
    private LRUCache $cache;
    private int $dbQueries = 0;

    public function __construct(int $capacity)
    {
        $this->cache = new LRUCache($capacity);
    }

    public function query(int $userId): array
    {
        // Check cache first
        $cached = $this->cache->get($userId);
        if ($cached !== null) {
            echo "   ✓ Cache HIT for user $userId (instant)\n";
            return $cached;
        }

        // Cache miss - simulate DB query
        echo "   ✗ Cache MISS for user $userId (querying database...)\n";
        usleep(1000);  // Simulate DB latency
        $this->dbQueries++;

        $userData = [
            'id' => $userId,
            'name' => "User $userId",
            'email' => "user{$userId}@example.com"
        ];

        $this->cache->put($userId, $userData);
        return $userData;
    }

    public function getStats(): array
    {
        $cacheStats = $this->cache->getStats();
        return [
            'dbQueries' => $this->dbQueries,
            'cacheHits' => $cacheStats['hits'],
            'cacheMisses' => $cacheStats['misses'],
            'hitRate' => $cacheStats['hitRate'],
        ];
    }
}

$dbCache = new DatabaseCache(capacity: 3);

echo "Simulating user lookup requests:\n\n";

$requests = [1, 2, 3, 1, 2, 4, 1, 5, 2];
foreach ($requests as $userId) {
    echo "Request: User $userId\n";
    $dbCache->query($userId);
}

echo "\n";
$stats = $dbCache->getStats();
echo "Performance Summary:\n";
echo "   Database Queries: {$stats['dbQueries']} (expensive!)\n";
echo "   Cache Hits:       {$stats['cacheHits']} (instant!)\n";
echo "   Cache Misses:     {$stats['cacheMisses']}\n";
echo "   Hit Rate:         " . number_format($stats['hitRate'] * 100, 1) . "%\n";
echo "   Cache saved " . ($stats['cacheHits']) . " database queries!\n\n";

// =============================================================================
// PERFORMANCE BENCHMARK
// =============================================================================

echo "\n⚡ PERFORMANCE BENCHMARK\n";
echo str_repeat("─", 60) . "\n\n";

$iterations = 10000;

// Benchmark get/put operations
$cache = new LRUCache(1000);

// Warm up cache
for ($i = 0; $i < 1000; $i++) {
    $cache->put($i, "value_$i");
}

// Benchmark gets (mostly hits)
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $cache->get($i % 1000);
}
$getTime = microtime(true) - $start;

// Benchmark puts (with evictions)
$start = microtime(true);
for ($i = 1000; $i < 1000 + $iterations; $i++) {
    $cache->put($i, "value_$i");
}
$putTime = microtime(true) - $start;

echo "Operations: " . number_format($iterations) . "\n\n";
echo "get() operations:  " . number_format($getTime * 1000, 2) . " ms\n";
echo "   Per operation:  " . number_format(($getTime / $iterations) * 1000000, 2) . " μs\n\n";

echo "put() operations:  " . number_format($putTime * 1000, 2) . " ms\n";
echo "   Per operation:  " . number_format(($putTime / $iterations) * 1000000, 2) . " μs\n\n";

echo "Both operations are O(1) - constant time! ✓\n\n";

// =============================================================================
// COMPARISON WITH ARRAY
// =============================================================================

echo "\n🔥 COMPARISON: LRU Cache vs Simple Array\n";
echo str_repeat("─", 60) . "\n\n";

// LRU Cache with automatic eviction
$lru = new LRUCache(100);
for ($i = 0; $i < 200; $i++) {
    $lru->put($i, "value_$i");
}
echo "LRU Cache (capacity 100):\n";
echo "   Added 200 items\n";
echo "   Current size: " . $lru->size() . " (automatically evicted 100 oldest)\n";
echo "   Memory efficient: maintains capacity limit ✓\n\n";

// Simple array keeps growing
$array = [];
for ($i = 0; $i < 200; $i++) {
    $array[$i] = "value_$i";
}
echo "Simple Array:\n";
echo "   Added 200 items\n";
echo "   Current size: " . count($array) . " (no eviction)\n";
echo "   Memory grows unbounded ✗\n\n";

// =============================================================================
// KEY TAKEAWAYS
// =============================================================================

echo "\n📖 KEY TAKEAWAYS\n";
echo str_repeat("─", 60) . "\n\n";
echo "✓ LRU Cache combines hash table + doubly linked list\n";
echo "✓ O(1) get() and put() operations (constant time!)\n";
echo "✓ Automatically evicts least recently used items\n";
echo "✓ Perfect for caching with memory constraints\n\n";

echo "Real-world applications:\n";
echo "  • Database query caching\n";
echo "  • Web browser page cache\n";
echo "  • CDN edge servers\n";
echo "  • Operating system page replacement\n";
echo "  • API response caching\n\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  Demo Complete! LRU Cache is production-ready.            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
