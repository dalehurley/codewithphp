<?php

declare(strict_types=1);

namespace ComputerScience\Capstone;

/**
 * LRU Cache Node
 *
 * Represents a key-value pair in the doubly linked list.
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
 * LRU (Least Recently Used) Cache
 *
 * A cache that removes the least recently used item when capacity is reached.
 * Implements O(1) get() and put() operations using a combination of:
 * - Hash table for O(1) lookups
 * - Doubly linked list for O(1) removal and reordering
 *
 * This is a classic interview question and real-world data structure used in:
 * - Operating system page replacement
 * - Database query caching
 * - Web browser cache
 * - CDN edge servers
 * - CPU cache simulation
 *
 * Time Complexity:
 * - get(): O(1)
 * - put(): O(1)
 *
 * Space Complexity: O(capacity)
 *
 * Example Usage:
 * ```php
 * $cache = new LRUCache(2);  // capacity of 2
 * $cache->put(1, 'a');       // cache: {1='a'}
 * $cache->put(2, 'b');       // cache: {1='a', 2='b'}
 * $cache->get(1);            // returns 'a', cache: {2='b', 1='a'}
 * $cache->put(3, 'c');       // evicts key 2, cache: {1='a', 3='c'}
 * $cache->get(2);            // returns null (evicted)
 * ```
 */
class LRUCache
{
    /** @var array<int, CacheNode> Hash map for O(1) access */
    private array $cache = [];

    /** @var CacheNode Dummy head node */
    private CacheNode $head;

    /** @var CacheNode Dummy tail node */
    private CacheNode $tail;

    /** @var int Current number of items */
    private int $size = 0;

    /** @var int Hit count for statistics */
    private int $hits = 0;

    /** @var int Miss count for statistics */
    private int $misses = 0;

    /**
     * Initialize LRU Cache with given capacity
     *
     * @param int $capacity Maximum number of items to store
     * @throws \InvalidArgumentException If capacity < 1
     */
    public function __construct(
        private int $capacity
    ) {
        if ($capacity < 1) {
            throw new \InvalidArgumentException("Capacity must be at least 1");
        }

        // Create dummy head and tail nodes to simplify edge cases
        $this->head = new CacheNode(0, 0);
        $this->tail = new CacheNode(0, 0);
        $this->head->next = $this->tail;
        $this->tail->prev = $this->head;
    }

    /**
     * Get value by key - O(1)
     *
     * If key exists, move it to front (most recently used).
     *
     * @param int $key The key to look up
     * @return mixed|null The value if found, null otherwise
     */
    public function get(int $key): mixed
    {
        if (!isset($this->cache[$key])) {
            $this->misses++;
            return null;
        }

        $node = $this->cache[$key];

        // Move to front (most recently used)
        $this->removeNode($node);
        $this->addToFront($node);

        $this->hits++;
        return $node->value;
    }

    /**
     * Put key-value pair into cache - O(1)
     *
     * If key exists, update value and move to front.
     * If at capacity, evict least recently used item.
     *
     * @param int $key The key
     * @param mixed $value The value to store
     * @return void
     */
    public function put(int $key, mixed $value): void
    {
        // If key exists, update and move to front
        if (isset($this->cache[$key])) {
            $node = $this->cache[$key];
            $node->value = $value;

            $this->removeNode($node);
            $this->addToFront($node);
            return;
        }

        // Create new node
        $newNode = new CacheNode($key, $value);
        $this->cache[$key] = $newNode;
        $this->addToFront($newNode);
        $this->size++;

        // Evict LRU if over capacity
        if ($this->size > $this->capacity) {
            $lru = $this->tail->prev;  // Least recently used is before tail
            $this->removeNode($lru);
            unset($this->cache[$lru->key]);
            $this->size--;
        }
    }

    /**
     * Check if key exists in cache - O(1)
     *
     * Does NOT update access order (unlike get).
     *
     * @param int $key The key to check
     * @return bool True if key exists
     */
    public function has(int $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * Delete key from cache - O(1)
     *
     * @param int $key The key to delete
     * @return bool True if deleted, false if not found
     */
    public function delete(int $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $node = $this->cache[$key];
        $this->removeNode($node);
        unset($this->cache[$key]);
        $this->size--;

        return true;
    }

    /**
     * Clear all items from cache - O(1)
     *
     * @return void
     */
    public function clear(): void
    {
        $this->cache = [];
        $this->head->next = $this->tail;
        $this->tail->prev = $this->head;
        $this->size = 0;
    }

    /**
     * Get current size
     *
     * @return int Number of items in cache
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Get capacity
     *
     * @return int Maximum capacity
     */
    public function capacity(): int
    {
        return $this->capacity;
    }

    /**
     * Check if cache is empty
     *
     * @return bool True if empty
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Check if cache is full
     *
     * @return bool True if at capacity
     */
    public function isFull(): bool
    {
        return $this->size === $this->capacity;
    }

    /**
     * Get all keys in MRU to LRU order
     *
     * Most recently used first, least recently used last.
     *
     * @return array<int> Array of keys
     */
    public function keys(): array
    {
        $keys = [];
        $current = $this->head->next;

        while ($current !== $this->tail) {
            $keys[] = $current->key;
            $current = $current->next;
        }

        return $keys;
    }

    /**
     * Get all values in MRU to LRU order
     *
     * @return array<mixed> Array of values
     */
    public function values(): array
    {
        $values = [];
        $current = $this->head->next;

        while ($current !== $this->tail) {
            $values[] = $current->value;
            $current = $current->next;
        }

        return $values;
    }

    /**
     * Get cache statistics
     *
     * @return array{hits: int, misses: int, hitRate: float, size: int, capacity: int}
     */
    public function getStats(): array
    {
        $total = $this->hits + $this->misses;
        $hitRate = $total > 0 ? $this->hits / $total : 0.0;

        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'hitRate' => $hitRate,
            'size' => $this->size,
            'capacity' => $this->capacity,
        ];
    }

    /**
     * Reset statistics
     *
     * @return void
     */
    public function resetStats(): void
    {
        $this->hits = 0;
        $this->misses = 0;
    }

    /**
     * Get string representation showing MRU to LRU order
     *
     * @return string
     */
    public function __toString(): string
    {
        $items = [];
        $current = $this->head->next;

        while ($current !== $this->tail) {
            $items[] = "{$current->key}={$current->value}";
            $current = $current->next;
        }

        $order = empty($items) ? "empty" : implode(" -> ", $items);
        return "LRUCache({$this->size}/{$this->capacity}): MRU[$order]LRU";
    }

    /**
     * Remove node from doubly linked list - O(1)
     *
     * @param CacheNode $node Node to remove
     * @return void
     */
    private function removeNode(CacheNode $node): void
    {
        $node->prev->next = $node->next;
        $node->next->prev = $node->prev;
    }

    /**
     * Add node to front (after head) - O(1)
     *
     * Front = most recently used position
     *
     * @param CacheNode $node Node to add
     * @return void
     */
    private function addToFront(CacheNode $node): void
    {
        $node->next = $this->head->next;
        $node->prev = $this->head;

        $this->head->next->prev = $node;
        $this->head->next = $node;
    }
}
