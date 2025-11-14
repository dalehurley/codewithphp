<?php

declare(strict_types=1);

/**
 * Queue Implementation (FIFO - First In, First Out)
 *
 * Complete implementation of a queue data structure.
 * Includes optimized circular queue for better performance.
 *
 * Time Complexities:
 * - Enqueue: O(1)
 * - Dequeue: O(1) for circular queue, O(n) for array queue
 * - Peek: O(1)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * Simple queue using SplQueue (PHP's built-in efficient queue)
 */
class Queue
{
    private SplQueue $queue;
    private int $maxSize;

    /**
     * Initialize queue
     *
     * @param int $maxSize Maximum queue size
     */
    public function __construct(int $maxSize = PHP_INT_MAX)
    {
        $this->queue = new SplQueue();
        $this->maxSize = $maxSize;
    }

    /**
     * Add element to rear - O(1)
     *
     * @param mixed $value The value to enqueue
     * @return void
     * @throws OverflowException if queue is full
     */
    public function enqueue(mixed $value): void
    {
        if ($this->isFull()) {
            throw new OverflowException("Queue is full");
        }

        $this->queue->enqueue($value);
    }

    /**
     * Remove element from front - O(1)
     *
     * @return mixed The dequeued value
     * @throws UnderflowException if queue is empty
     */
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->queue->dequeue();
    }

    /**
     * Peek at front element - O(1)
     *
     * @return mixed The front value
     * @throws UnderflowException if queue is empty
     */
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->queue->bottom();
    }

    /**
     * Check if queue is empty - O(1)
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }

    /**
     * Check if queue is full - O(1)
     *
     * @return bool True if full, false otherwise
     */
    public function isFull(): bool
    {
        return $this->queue->count() >= $this->maxSize;
    }

    /**
     * Get queue size - O(1)
     *
     * @return int Number of elements
     */
    public function size(): int
    {
        return $this->queue->count();
    }

    /**
     * Display queue contents
     *
     * @return void
     */
    public function display(): void
    {
        if ($this->isEmpty()) {
            echo "Queue is empty\n";
            return;
        }

        $elements = [];
        foreach ($this->queue as $item) {
            $elements[] = $item;
        }

        echo "Queue (front to rear): " . implode(' <- ', $elements) . "\n";
    }
}

/**
 * Circular Queue implementation for O(1) operations
 */
class CircularQueue
{
    private array $items;
    private int $front = 0;
    private int $rear = -1;
    private int $size = 0;
    private int $capacity;

    /**
     * Initialize circular queue
     *
     * @param int $capacity Maximum capacity
     */
    public function __construct(int $capacity)
    {
        $this->capacity = $capacity;
        $this->items = array_fill(0, $capacity, null);
    }

    /**
     * Enqueue element - O(1)
     *
     * @param mixed $value The value to add
     * @return void
     * @throws OverflowException if queue is full
     */
    public function enqueue(mixed $value): void
    {
        if ($this->isFull()) {
            throw new OverflowException("Queue is full");
        }

        $this->rear = ($this->rear + 1) % $this->capacity;
        $this->items[$this->rear] = $value;
        $this->size++;
    }

    /**
     * Dequeue element - O(1)
     *
     * @return mixed The dequeued value
     * @throws UnderflowException if queue is empty
     */
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        $value = $this->items[$this->front];
        $this->items[$this->front] = null;
        $this->front = ($this->front + 1) % $this->capacity;
        $this->size--;

        return $value;
    }

    /**
     * Peek at front element - O(1)
     *
     * @return mixed The front value
     * @throws UnderflowException if queue is empty
     */
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->items[$this->front];
    }

    /**
     * Check if empty - O(1)
     *
     * @return bool True if empty
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Check if full - O(1)
     *
     * @return bool True if full
     */
    public function isFull(): bool
    {
        return $this->size === $this->capacity;
    }

    /**
     * Get size - O(1)
     *
     * @return int Number of elements
     */
    public function size(): int
    {
        return $this->size;
    }
}

/**
 * Priority Queue implementation
 */
class PriorityQueue
{
    private SplPriorityQueue $queue;

    public function __construct()
    {
        $this->queue = new SplPriorityQueue();
    }

    /**
     * Insert with priority - O(log n)
     *
     * @param mixed $value The value
     * @param int $priority The priority (higher = more important)
     * @return void
     */
    public function insert(mixed $value, int $priority): void
    {
        $this->queue->insert($value, $priority);
    }

    /**
     * Extract highest priority element - O(log n)
     *
     * @return mixed The value
     * @throws UnderflowException if empty
     */
    public function extract(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Priority queue is empty");
        }

        return $this->queue->extract();
    }

    /**
     * Check if empty - O(1)
     *
     * @return bool True if empty
     */
    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Queue Implementation Demo ===\n\n";

    // Basic queue operations
    echo "=== Basic Queue Operations ===\n";
    $queue = new Queue();

    echo "Enqueuing: 10, 20, 30\n";
    $queue->enqueue(10);
    $queue->enqueue(20);
    $queue->enqueue(30);
    $queue->display(); // 10 <- 20 <- 30

    echo "Peek: " . $queue->peek() . "\n"; // 10
    echo "Dequeue: " . $queue->dequeue() . "\n"; // 10
    $queue->display(); // 20 <- 30

    echo "Size: " . $queue->size() . "\n"; // 2

    // Circular queue
    echo "\n=== Circular Queue (Efficient) ===\n";
    $circularQueue = new CircularQueue(5);

    echo "Enqueuing: 1, 2, 3\n";
    $circularQueue->enqueue(1);
    $circularQueue->enqueue(2);
    $circularQueue->enqueue(3);

    echo "Dequeue: " . $circularQueue->dequeue() . "\n"; // 1
    echo "Dequeue: " . $circularQueue->dequeue() . "\n"; // 2

    echo "Enqueuing: 4, 5, 6, 7\n";
    $circularQueue->enqueue(4);
    $circularQueue->enqueue(5);
    $circularQueue->enqueue(6);
    $circularQueue->enqueue(7);

    echo "Size: " . $circularQueue->size() . "\n"; // 5
    echo "Is full: " . ($circularQueue->isFull() ? "yes" : "no") . "\n"; // yes

    // Priority queue
    echo "\n=== Priority Queue ===\n";
    $pq = new PriorityQueue();

    echo "Inserting tasks with priorities...\n";
    $pq->insert("Low priority task", 1);
    $pq->insert("High priority task", 10);
    $pq->insert("Medium priority task", 5);
    $pq->insert("Urgent task", 20);

    echo "Processing tasks by priority:\n";
    while (!$pq->isEmpty()) {
        echo "- " . $pq->extract() . "\n";
    }

    // Edge cases
    echo "\n=== Testing Edge Cases ===\n";
    $emptyQueue = new Queue();
    echo "Empty queue: ";
    $emptyQueue->display();

    try {
        echo "Attempting to dequeue from empty queue...\n";
        $emptyQueue->dequeue();
    } catch (UnderflowException $e) {
        echo "Caught: " . $e->getMessage() . "\n";
    }

    // Test queue overflow
    echo "\nTesting queue overflow...\n";
    $limitedQueue = new Queue(maxSize: 2);
    try {
        $limitedQueue->enqueue(1);
        $limitedQueue->enqueue(2);
        echo "Queue full. Attempting to enqueue 3...\n";
        $limitedQueue->enqueue(3); // This will throw
    } catch (OverflowException $e) {
        echo "Caught: " . $e->getMessage() . "\n";
    }
}
