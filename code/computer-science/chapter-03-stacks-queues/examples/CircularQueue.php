<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03;

use UnderflowException;
use OverflowException;

/**
 * Circular Queue Implementation (Efficient FIFO)
 *
 * A circular queue uses a fixed-size array with front and rear pointers
 * that wrap around. This provides O(1) enqueue and dequeue operations.
 *
 * Time Complexity:
 * - enqueue(): O(1)
 * - dequeue(): O(1)  ← Much faster than regular Queue!
 * - peek(): O(1)
 * - isEmpty(): O(1)
 * - isFull(): O(1)
 * - size(): O(1)
 *
 * Space Complexity: O(capacity)
 */
class CircularQueue
{
    /** @var array<mixed> Fixed-size array for storing elements */
    private array $items;

    /** @var int Index of the front element */
    private int $front = 0;

    /** @var int Index where next element will be added */
    private int $rear = -1;

    /** @var int Current number of elements */
    private int $size = 0;

    /** @var int Maximum capacity of the queue */
    private int $capacity;

    /**
     * Create a new circular queue with fixed capacity
     *
     * @param int $capacity Maximum number of elements (default: 10)
     * @throws \InvalidArgumentException if capacity is less than 1
     */
    public function __construct(int $capacity = 10)
    {
        if ($capacity < 1) {
            throw new \InvalidArgumentException("Capacity must be at least 1");
        }

        $this->capacity = $capacity;
        $this->items = array_fill(0, $capacity, null);
    }

    /**
     * Add an element to the rear of the queue
     *
     * @param mixed $item The item to enqueue
     * @return void
     * @throws OverflowException if the queue is full
     */
    public function enqueue(mixed $item): void
    {
        if ($this->isFull()) {
            throw new OverflowException("Queue is full");
        }

        // Move rear forward (with wraparound)
        $this->rear = ($this->rear + 1) % $this->capacity;
        $this->items[$this->rear] = $item;
        $this->size++;
    }

    /**
     * Remove and return the front element from the queue
     *
     * @return mixed The front element
     * @throws UnderflowException if the queue is empty
     */
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        $item = $this->items[$this->front];

        // Move front forward (with wraparound)
        $this->front = ($this->front + 1) % $this->capacity;
        $this->size--;

        return $item;
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
            throw new UnderflowException("Queue is empty");
        }

        return $this->items[$this->front];
    }

    /**
     * Check if the queue is empty
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Check if the queue is full
     *
     * @return bool True if full, false otherwise
     */
    public function isFull(): bool
    {
        return $this->size === $this->capacity;
    }

    /**
     * Get the number of elements in the queue
     *
     * @return int The current size
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Get the maximum capacity of the queue
     *
     * @return int The capacity
     */
    public function capacity(): int
    {
        return $this->capacity;
    }

    /**
     * Clear all elements from the queue
     *
     * @return void
     */
    public function clear(): void
    {
        $this->front = 0;
        $this->rear = -1;
        $this->size = 0;
        $this->items = array_fill(0, $this->capacity, null);
    }

    /**
     * Get all elements as an array in order (for debugging/testing)
     *
     * @return array<mixed> Array representation of the queue
     */
    public function toArray(): array
    {
        if ($this->isEmpty()) {
            return [];
        }

        $result = [];
        $index = $this->front;

        for ($i = 0; $i < $this->size; $i++) {
            $result[] = $this->items[$index];
            $index = ($index + 1) % $this->capacity;
        }

        return $result;
    }

    /**
     * Create a string representation of the queue
     *
     * @return string String representation
     */
    public function __toString(): string
    {
        $elements = $this->toArray();
        return 'front → [' . implode(', ', $elements) . '] ← rear ' .
               "({$this->size}/{$this->capacity})";
    }
}
