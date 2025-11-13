<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03;

use UnderflowException;

/**
 * Queue Implementation (FIFO - First In, First Out)
 *
 * A queue is a linear data structure that follows the FIFO principle.
 * Elements are added at the rear and removed from the front.
 *
 * ⚠️  WARNING: This array-based implementation has O(n) dequeue operation
 * due to array_shift(). For production code, use CircularQueue or SplQueue.
 *
 * Time Complexity:
 * - enqueue(): O(1)
 * - dequeue(): O(n) ⚠️  SLOW - use CircularQueue for O(1)
 * - peek(): O(1)
 * - isEmpty(): O(1)
 * - size(): O(1)
 *
 * Space Complexity: O(n) where n is the number of elements
 */
class Queue
{
    /** @var array<mixed> Internal storage for queue elements */
    private array $items = [];

    /**
     * Add an element to the rear of the queue
     *
     * @param mixed $item The item to enqueue
     * @return void
     */
    public function enqueue(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Remove and return the front element from the queue
     *
     * ⚠️  WARNING: O(n) operation due to array reindexing
     *
     * @return mixed The front element
     * @throws UnderflowException if the queue is empty
     */
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot dequeue from an empty queue");
        }

        return array_shift($this->items); // O(n) - reindexes array
    }

    /**
     * View the front element without removing it
     *
     * @return mixed The front element
     * @throws UnderflowException if the queue is empty
     */
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot peek an empty queue");
        }

        return $this->items[0];
    }

    /**
     * Check if the queue is empty
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get the number of elements in the queue
     *
     * @return int The size of the queue
     */
    public function size(): int
    {
        return count($this->items);
    }

    /**
     * Clear all elements from the queue
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Get all elements as an array (for debugging/testing)
     *
     * @return array<mixed> Array representation of the queue
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Create a string representation of the queue
     *
     * @return string String representation
     */
    public function __toString(): string
    {
        return 'front → [' . implode(', ', $this->items) . '] ← rear';
    }
}
