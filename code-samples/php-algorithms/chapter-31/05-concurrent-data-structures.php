<?php
/**
 * Concurrent Data Structures
 *
 * Demonstrates thread-safe and lock-free data structures for concurrent
 * programming. These structures handle concurrent access safely.
 *
 * Note: This simulates concurrent behavior for demonstration. For true
 * concurrent data structures, use Swoole's Atomic, Table, or Channel.
 *
 * @category  Algorithms
 * @package   ConcurrentAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter31;

/**
 * Thread-safe counter using optimistic locking
 */
class ConcurrentCounter
{
    private int $value = 0;
    private object $lock;

    public function __construct(int $initial = 0)
    {
        $this->value = $initial;
        $this->lock = new \stdClass(); // Simulated lock object
    }

    /**
     * Increment counter atomically
     *
     * @return int New value after increment
     */
    public function increment(): int
    {
        // In production with Swoole: use Swoole\Atomic
        $this->value++;
        return $this->value;
    }

    /**
     * Decrement counter atomically
     *
     * @return int New value after decrement
     */
    public function decrement(): int
    {
        $this->value--;
        return $this->value;
    }

    /**
     * Add value atomically
     *
     * @param int $delta Value to add
     * @return int New value
     */
    public function add(int $delta): int
    {
        $this->value += $delta;
        return $this->value;
    }

    /**
     * Get current value
     *
     * @return int Current value
     */
    public function get(): int
    {
        return $this->value;
    }

    /**
     * Compare and swap operation
     *
     * @param int $expected Expected current value
     * @param int $new New value to set
     * @return bool True if swap successful
     */
    public function compareAndSwap(int $expected, int $new): bool
    {
        if ($this->value === $expected) {
            $this->value = $new;
            return true;
        }
        return false;
    }
}

/**
 * Lock-free queue using linked list
 */
class LockFreeQueue
{
    private array $items = [];
    private int $head = 0;
    private int $tail = 0;

    /**
     * Enqueue an item
     *
     * @param mixed $item Item to add
     */
    public function enqueue(mixed $item): void
    {
        $this->items[$this->tail++] = $item;
    }

    /**
     * Dequeue an item
     *
     * @return mixed|null Item or null if empty
     */
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }

        $item = $this->items[$this->head];
        unset($this->items[$this->head]);
        $this->head++;

        // Compact array periodically
        if ($this->head > 1000 && $this->head > $this->tail / 2) {
            $this->items = array_values($this->items);
            $this->tail -= $this->head;
            $this->head = 0;
        }

        return $item;
    }

    /**
     * Check if queue is empty
     *
     * @return bool True if empty
     */
    public function isEmpty(): bool
    {
        return $this->head >= $this->tail;
    }

    /**
     * Get queue size
     *
     * @return int Number of items
     */
    public function size(): int
    {
        return $this->tail - $this->head;
    }
}

/**
 * Concurrent HashMap (simulated thread-safe map)
 */
class ConcurrentHashMap
{
    private array $buckets = [];
    private int $size = 0;

    /**
     * Put key-value pair
     *
     * @param string $key Key
     * @param mixed $value Value
     */
    public function put(string $key, mixed $value): void
    {
        $hash = $this->hash($key);

        if (!isset($this->buckets[$hash])) {
            $this->buckets[$hash] = [];
        }

        if (!isset($this->buckets[$hash][$key])) {
            $this->size++;
        }

        $this->buckets[$hash][$key] = $value;
    }

    /**
     * Get value by key
     *
     * @param string $key Key
     * @return mixed|null Value or null if not found
     */
    public function get(string $key): mixed
    {
        $hash = $this->hash($key);

        if (!isset($this->buckets[$hash][$key])) {
            return null;
        }

        return $this->buckets[$hash][$key];
    }

    /**
     * Remove key-value pair
     *
     * @param string $key Key to remove
     * @return bool True if removed
     */
    public function remove(string $key): bool
    {
        $hash = $this->hash($key);

        if (!isset($this->buckets[$hash][$key])) {
            return false;
        }

        unset($this->buckets[$hash][$key]);
        $this->size--;

        return true;
    }

    /**
     * Check if key exists
     *
     * @param string $key Key to check
     * @return bool True if exists
     */
    public function containsKey(string $key): bool
    {
        $hash = $this->hash($key);
        return isset($this->buckets[$hash][$key]);
    }

    /**
     * Get number of entries
     *
     * @return int Size
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Get all keys
     *
     * @return array Keys
     */
    public function keys(): array
    {
        $keys = [];
        foreach ($this->buckets as $bucket) {
            $keys = array_merge($keys, array_keys($bucket));
        }
        return $keys;
    }

    /**
     * Hash function
     *
     * @param string $key Key to hash
     * @return int Hash value
     */
    private function hash(string $key): int
    {
        return abs(crc32($key)) % 16; // 16 buckets
    }
}

/**
 * Bounded blocking queue
 */
class BlockingQueue
{
    private array $items = [];
    private int $capacity;
    private int $head = 0;
    private int $tail = 0;

    public function __construct(int $capacity = 100)
    {
        $this->capacity = $capacity;
    }

    /**
     * Add item, blocks if full
     *
     * @param mixed $item Item to add
     * @return bool Success
     * @throws \RuntimeException If queue is full
     */
    public function put(mixed $item): bool
    {
        if ($this->isFull()) {
            throw new \RuntimeException("Queue is full");
        }

        $this->items[$this->tail % $this->capacity] = $item;
        $this->tail++;

        return true;
    }

    /**
     * Remove and return item, blocks if empty
     *
     * @return mixed Item
     * @throws \RuntimeException If queue is empty
     */
    public function take(): mixed
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException("Queue is empty");
        }

        $item = $this->items[$this->head % $this->capacity];
        unset($this->items[$this->head % $this->capacity]);
        $this->head++;

        return $item;
    }

    /**
     * Check if queue is full
     *
     * @return bool True if full
     */
    public function isFull(): bool
    {
        return ($this->tail - $this->head) >= $this->capacity;
    }

    /**
     * Check if queue is empty
     *
     * @return bool True if empty
     */
    public function isEmpty(): bool
    {
        return $this->head >= $this->tail;
    }

    /**
     * Get current size
     *
     * @return int Size
     */
    public function size(): int
    {
        return $this->tail - $this->head;
    }

    /**
     * Get remaining capacity
     *
     * @return int Remaining space
     */
    public function remainingCapacity(): int
    {
        return $this->capacity - $this->size();
    }
}

/**
 * Read-Write Lock (simulated)
 */
class ReadWriteLock
{
    private int $readers = 0;
    private bool $writer = false;

    /**
     * Acquire read lock
     */
    public function acquireRead(): void
    {
        while ($this->writer) {
            usleep(1000); // Wait for writer
        }
        $this->readers++;
    }

    /**
     * Release read lock
     */
    public function releaseRead(): void
    {
        $this->readers--;
    }

    /**
     * Acquire write lock
     */
    public function acquireWrite(): void
    {
        while ($this->writer || $this->readers > 0) {
            usleep(1000); // Wait for all readers and writers
        }
        $this->writer = true;
    }

    /**
     * Release write lock
     */
    public function releaseWrite(): void
    {
        $this->writer = false;
    }

    /**
     * Execute with read lock
     *
     * @param callable $operation Operation to execute
     * @return mixed Operation result
     */
    public function readLocked(callable $operation): mixed
    {
        $this->acquireRead();
        try {
            return $operation();
        } finally {
            $this->releaseRead();
        }
    }

    /**
     * Execute with write lock
     *
     * @param callable $operation Operation to execute
     * @return mixed Operation result
     */
    public function writeLocked(callable $operation): mixed
    {
        $this->acquireWrite();
        try {
            return $operation();
        } finally {
            $this->releaseWrite();
        }
    }
}

/**
 * Example usage demonstrating concurrent data structures
 */
function demonstrateConcurrentDataStructures(): void
{
    echo "=== Concurrent Data Structures Demo ===\n\n";

    // Test 1: Concurrent counter
    echo "1. Concurrent Counter\n";
    $counter = new ConcurrentCounter(0);

    // Simulate concurrent increments
    for ($i = 0; $i < 100; $i++) {
        $counter->increment();
    }

    echo "   Counter value: " . $counter->get() . " (expected: 100)\n";

    // Compare and swap
    $success = $counter->compareAndSwap(100, 200);
    echo "   CAS(100 -> 200): " . ($success ? 'success' : 'failed') . "\n";
    echo "   New value: " . $counter->get() . "\n";

    // Test 2: Lock-free queue
    echo "\n2. Lock-Free Queue\n";
    $queue = new LockFreeQueue();

    for ($i = 1; $i <= 10; $i++) {
        $queue->enqueue("Item {$i}");
    }

    echo "   Queue size: " . $queue->size() . "\n";
    echo "   Dequeued: ";

    for ($i = 0; $i < 5; $i++) {
        echo $queue->dequeue() . " ";
    }
    echo "\n   Remaining: " . $queue->size() . "\n";

    // Test 3: Concurrent HashMap
    echo "\n3. Concurrent HashMap\n";
    $map = new ConcurrentHashMap();

    // Add entries
    $map->put('user:1', ['name' => 'Alice', 'age' => 30]);
    $map->put('user:2', ['name' => 'Bob', 'age' => 25]);
    $map->put('user:3', ['name' => 'Charlie', 'age' => 35]);

    echo "   Map size: " . $map->size() . "\n";
    echo "   Keys: " . implode(', ', $map->keys()) . "\n";

    $user = $map->get('user:2');
    echo "   user:2 = {$user['name']}, age {$user['age']}\n";

    $map->remove('user:1');
    echo "   After removal: " . $map->size() . " entries\n";

    // Test 4: Blocking queue
    echo "\n4. Blocking Queue\n";
    $blockingQueue = new BlockingQueue(5);

    // Fill queue
    for ($i = 1; $i <= 5; $i++) {
        $blockingQueue->put("Task {$i}");
    }

    echo "   Queue size: " . $blockingQueue->size() . " (capacity: 5)\n";
    echo "   Is full: " . ($blockingQueue->isFull() ? 'yes' : 'no') . "\n";

    // Try to add when full
    try {
        $blockingQueue->put("Task 6");
    } catch (\RuntimeException $e) {
        echo "   ✗ Cannot add: " . $e->getMessage() . "\n";
    }

    // Take items
    echo "   Taking items: ";
    for ($i = 0; $i < 3; $i++) {
        echo $blockingQueue->take() . " ";
    }
    echo "\n   Remaining capacity: " . $blockingQueue->remainingCapacity() . "\n";

    // Test 5: Read-Write Lock
    echo "\n5. Read-Write Lock\n";
    $rwLock = new ReadWriteLock();
    $sharedData = ['counter' => 0];

    // Simulate concurrent reads
    echo "   Performing concurrent reads...\n";
    for ($i = 0; $i < 5; $i++) {
        $value = $rwLock->readLocked(function () use ($sharedData) {
            return $sharedData['counter'];
        });
        echo "   Read {$i}: counter = {$value}\n";
    }

    // Perform write
    echo "   Performing write...\n";
    $rwLock->writeLocked(function () use (&$sharedData) {
        $sharedData['counter'] = 42;
        echo "   Write: counter = 42\n";
    });

    // Read after write
    $value = $rwLock->readLocked(function () use ($sharedData) {
        return $sharedData['counter'];
    });
    echo "   Read after write: counter = {$value}\n";

    // Test 6: Producer-Consumer with blocking queue
    echo "\n6. Producer-Consumer Pattern\n";
    $buffer = new BlockingQueue(10);
    $produced = 0;
    $consumed = 0;

    // Producer
    echo "   Producer: Adding 10 items\n";
    for ($i = 1; $i <= 10; $i++) {
        $buffer->put("Product {$i}");
        $produced++;
    }

    // Consumer
    echo "   Consumer: Processing items\n";
    while (!$buffer->isEmpty()) {
        $item = $buffer->take();
        $consumed++;
        if ($consumed <= 3) {
            echo "   - Consumed: {$item}\n";
        } elseif ($consumed === 4) {
            echo "   - ... (remaining items)\n";
        }
    }

    echo "   Total produced: {$produced}, consumed: {$consumed}\n";
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateConcurrentDataStructures();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
