<?php

declare(strict_types=1);

/**
 * LRU Cache - Practical Hash Table Application
 *
 * Demonstrates:
 * - Combining hash table with doubly linked list
 * - O(1) get and put operations
 * - Least Recently Used eviction policy
 * - Real-world caching implementation
 */

/**
 * Node for doubly linked list
 */
class CacheNode
{
    public function __construct(
        public int $key,
        public mixed $value,
        public ?CacheNode $prev = null,
        public ?CacheNode $next = null
    ) {}
}

/**
 * LRU Cache Implementation
 *
 * Uses hash table for O(1) lookups + doubly linked list for O(1) reordering
 */
class LRUCache
{
    private array $cache = [];
    private ?CacheNode $head = null;
    private ?CacheNode $tail = null;
    private int $count = 0;

    public function __construct(
        private int $capacity
    ) {}

    /**
     * Get value from cache - O(1)
     * Moves accessed node to front (most recently used)
     */
    public function get(int $key): mixed
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        $node = $this->cache[$key];

        // Move to front (most recently used)
        $this->moveToFront($node);

        return $node->value;
    }

    /**
     * Put key-value pair in cache - O(1)
     * Evicts least recently used if at capacity
     */
    public function put(int $key, mixed $value): void
    {
        // Update existing key
        if (isset($this->cache[$key])) {
            $node = $this->cache[$key];
            $node->value = $value;
            $this->moveToFront($node);
            return;
        }

        // Create new node
        $node = new CacheNode($key, $value);
        $this->cache[$key] = $node;

        // Add to front
        if ($this->head === null) {
            $this->head = $this->tail = $node;
        } else {
            $node->next = $this->head;
            $this->head->prev = $node;
            $this->head = $node;
        }

        $this->count++;

        // Evict if over capacity
        if ($this->count > $this->capacity) {
            $this->evictLRU();
        }
    }

    /**
     * Move node to front (mark as most recently used)
     */
    private function moveToFront(CacheNode $node): void
    {
        // Already at front
        if ($node === $this->head) {
            return;
        }

        // Remove from current position
        if ($node->prev !== null) {
            $node->prev->next = $node->next;
        }

        if ($node->next !== null) {
            $node->next->prev = $node->prev;
        }

        // Update tail if needed
        if ($node === $this->tail) {
            $this->tail = $node->prev;
        }

        // Move to front
        $node->prev = null;
        $node->next = $this->head;
        $this->head->prev = $node;
        $this->head = $node;
    }

    /**
     * Evict least recently used item (tail)
     */
    private function evictLRU(): void
    {
        if ($this->tail === null) {
            return;
        }

        $evictedKey = $this->tail->key;

        // Remove from hash table
        unset($this->cache[$evictedKey]);

        // Remove from list
        if ($this->tail->prev !== null) {
            $this->tail->prev->next = null;
            $this->tail = $this->tail->prev;
        } else {
            // Only one node
            $this->head = $this->tail = null;
        }

        $this->count--;
    }

    /**
     * Get current cache state (MRU to LRU)
     */
    public function getState(): array
    {
        $state = [];
        $current = $this->head;

        while ($current !== null) {
            $state[] = [$current->key => $current->value];
            $current = $current->next;
        }

        return $state;
    }

    /**
     * Get cache size
     */
    public function size(): int
    {
        return $this->count;
    }

    /**
     * Clear cache
     */
    public function clear(): void
    {
        $this->cache = [];
        $this->head = $this->tail = null;
        $this->count = 0;
    }
}

// Demo
echo "=== LRU Cache Implementation ===\n\n";

echo "LRU Cache combines:\n";
echo "  • Hash Table: O(1) key lookups\n";
echo "  • Doubly Linked List: O(1) reordering\n";
echo "  → Result: O(1) get and put operations!\n\n";

// Test 1: Basic Operations
echo "1. Basic Cache Operations\n";
echo "==========================\n";

$cache = new LRUCache(3);
echo "Created cache with capacity: 3\n\n";

echo "Put: (1, 'one')\n";
$cache->put(1, 'one');
echo "Cache state: " . json_encode($cache->getState()) . "\n\n";

echo "Put: (2, 'two')\n";
$cache->put(2, 'two');
echo "Cache state: " . json_encode($cache->getState()) . "\n\n";

echo "Put: (3, 'three')\n";
$cache->put(3, 'three');
echo "Cache state: " . json_encode($cache->getState()) . "\n";
echo "Size: " . $cache->size() . "/3 (at capacity)\n\n";

// Test 2: LRU Eviction
echo "2. LRU Eviction\n";
echo "================\n";

echo "Put: (4, 'four') - This will evict LRU item\n";
$cache->put(4, 'four');
echo "Cache state: " . json_encode($cache->getState()) . "\n";
echo "Key 1 evicted (least recently used)\n\n";

echo "Get: 1 (evicted)\n";
$result = $cache->get(1);
echo "Result: " . ($result ?? 'null') . "\n\n";

// Test 3: Access Pattern
echo "3. Access Pattern Affects Order\n";
echo "================================\n";

echo "Current state: " . json_encode($cache->getState()) . "\n";
echo "Order: [4, 3, 2] (MRU → LRU)\n\n";

echo "Get: 2 (moves to front)\n";
$cache->get(2);
echo "New state: " . json_encode($cache->getState()) . "\n";
echo "Order: [2, 4, 3] (2 is now MRU)\n\n";

echo "Get: 3 (moves to front)\n";
$cache->get(3);
echo "New state: " . json_encode($cache->getState()) . "\n";
echo "Order: [3, 2, 4] (3 is now MRU)\n\n";

echo "Put: (5, 'five') - Evicts 4 (LRU)\n";
$cache->put(5, 'five');
echo "New state: " . json_encode($cache->getState()) . "\n\n";

// Test 4: Update Existing Key
echo "4. Updating Existing Keys\n";
echo "==========================\n";

echo "Current state: " . json_encode($cache->getState()) . "\n\n";

echo "Put: (3, 'THREE') - Update value and move to front\n";
$cache->put(3, 'THREE');
echo "New state: " . json_encode($cache->getState()) . "\n";
echo "Value updated and 3 moved to MRU position\n\n";

// Test 5: Real-World Simulation
echo "5. Real-World Page Cache Simulation\n";
echo "=====================================\n";

$pageCache = new LRUCache(5);

$pageAccesses = [
    'home', 'about', 'contact', 'blog', 'products',  // Fill cache
    'home',      // Access home (moves to front)
    'pricing',   // New page (evicts 'about')
    'blog',      // Access blog (moves to front)
    'careers',   // New page (evicts 'contact')
];

echo "Page cache capacity: 5\n";
echo "Simulating page accesses:\n\n";

foreach ($pageAccesses as $i => $page) {
    $pageCache->put($page, "Content for $page");

    echo "Access " . ($i + 1) . ": '$page'\n";

    $state = $pageCache->getState();
    $pages = array_map(fn($item) => array_key_first($item), $state);
    echo "  Cache: [" . implode(', ', $pages) . "]\n";

    if ($pageCache->size() > 5) {
        echo "  Evicted least recently used page\n";
    }
    echo "\n";
}

// Test 6: Performance Benchmark
echo "6. Performance Benchmark\n";
echo "=========================\n";

$perfCache = new LRUCache(1000);

// Fill cache
echo "Filling cache with 1000 items...\n";
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $perfCache->put($i, "value_$i");
}
$fillTime = (microtime(true) - $start) * 1000;

// Random access pattern
echo "Performing 10,000 random accesses...\n";
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $key = random_int(0, 1499); // Some hits, some misses
    $perfCache->get($key);
}
$accessTime = (microtime(true) - $start) * 1000;

// Random updates
echo "Performing 5,000 random updates...\n";
$start = microtime(true);
for ($i = 0; $i < 5000; $i++) {
    $key = random_int(0, 1999);
    $perfCache->put($key, "new_value_$key");
}
$updateTime = (microtime(true) - $start) * 1000;

echo "\nResults:\n";
echo "  Fill 1000 items:    " . number_format($fillTime, 3) . " ms\n";
echo "  10,000 gets:        " . number_format($accessTime, 3) . " ms\n";
echo "  5,000 puts:         " . number_format($updateTime, 3) . " ms\n";
echo "  Average get time:   " . number_format($accessTime / 10000, 6) . " ms\n";
echo "  Average put time:   " . number_format($updateTime / 5000, 6) . " ms\n\n";

// Test 7: Edge Cases
echo "7. Edge Cases\n";
echo "==============\n";

$edgeCache = new LRUCache(1);
echo "Cache with capacity 1:\n";

$edgeCache->put(1, 'first');
echo "  Put (1, 'first'): " . json_encode($edgeCache->getState()) . "\n";

$edgeCache->put(2, 'second');
echo "  Put (2, 'second'): " . json_encode($edgeCache->getState()) . "\n";
echo "  (1 evicted immediately)\n\n";

$edgeCache->clear();
echo "After clear(): " . json_encode($edgeCache->getState()) . "\n";
echo "Size: " . $edgeCache->size() . "\n\n";

echo "8. Key Insights\n";
echo "================\n";
echo "LRU Cache achieves O(1) operations by combining:\n";
echo "  1. Hash Table: O(1) lookups by key\n";
echo "  2. Doubly Linked List: O(1) reordering\n\n";

echo "Data Structure Choice:\n";
echo "  • Hash Table alone: Can't track access order\n";
echo "  • Linked List alone: O(n) lookups\n";
echo "  • Combined: O(1) for both!\n\n";

echo "Real-World Applications:\n";
echo "  ✓ Database query result caching\n";
echo "  ✓ Web page caching (CDNs)\n";
echo "  ✓ CPU cache algorithms\n";
echo "  ✓ Browser cache management\n";
echo "  ✓ API response caching\n\n";

echo "Complexity:\n";
echo "  • Get:   O(1)\n";
echo "  • Put:   O(1)\n";
echo "  • Space: O(capacity)\n\n";

echo "Advantages:\n";
echo "  ✓ Predictable memory usage (bounded by capacity)\n";
echo "  ✓ Automatic eviction of stale data\n";
echo "  ✓ Optimal for temporal locality (recent items likely reused)\n";
echo "  ✓ Simple to implement and understand\n";

echo "\n✅ Complete! LRU Cache implementation demonstrated.\n";
