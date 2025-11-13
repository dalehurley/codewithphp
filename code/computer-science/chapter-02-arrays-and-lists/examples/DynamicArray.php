<?php

declare(strict_types=1);

namespace ComputerScience\Chapter02;

use OutOfBoundsException;

/**
 * Dynamic Array Implementation
 *
 * Demonstrates how dynamic arrays (like ArrayList in Java or vector in C++)
 * work internally with automatic resizing.
 *
 * Key Features:
 * - Starts with initial capacity
 * - Doubles capacity when full
 * - Amortized O(1) append operation
 * - O(1) access by index
 *
 * Time Complexity:
 * - get(): O(1)
 * - add(): O(1) amortized, O(n) when resize needed
 * - remove(): O(n) due to shifting
 * - size(): O(1)
 *
 * Space Complexity: O(n) where n is capacity
 */
class DynamicArray
{
    /** @var array<int, mixed> Internal storage */
    private array $data = [];

    /** @var int Current number of elements */
    private int $size = 0;

    /** @var int Current capacity */
    private int $capacity;

    /** @var int Number of times array was resized */
    private int $resizeCount = 0;

    /**
     * Create a new dynamic array
     *
     * @param int $initialCapacity Starting capacity (default: 4)
     */
    public function __construct(int $initialCapacity = 4)
    {
        if ($initialCapacity < 1) {
            throw new \InvalidArgumentException("Capacity must be at least 1");
        }

        $this->capacity = $initialCapacity;
        $this->data = array_fill(0, $this->capacity, null);
    }

    /**
     * Add an element to the end of the array
     *
     * @param mixed $value The value to add
     * @return void
     */
    public function add(mixed $value): void
    {
        // Resize if at capacity
        if ($this->size === $this->capacity) {
            $this->resize();
        }

        $this->data[$this->size] = $value;
        $this->size++;
    }

    /**
     * Get element at specific index
     *
     * @param int $index The index to access
     * @return mixed The value at that index
     * @throws OutOfBoundsException If index is out of bounds
     */
    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        return $this->data[$index];
    }

    /**
     * Set element at specific index
     *
     * @param int $index The index to modify
     * @param mixed $value The new value
     * @return void
     * @throws OutOfBoundsException If index is out of bounds
     */
    public function set(int $index, mixed $value): void
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        $this->data[$index] = $value;
    }

    /**
     * Remove element at specific index
     *
     * Shifts all subsequent elements left by one position.
     * O(n) operation where n is the number of elements after the removed index.
     *
     * @param int $index The index to remove
     * @return mixed The removed value
     * @throws OutOfBoundsException If index is out of bounds
     */
    public function remove(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        $removed = $this->data[$index];

        // Shift elements left
        for ($i = $index; $i < $this->size - 1; $i++) {
            $this->data[$i] = $this->data[$i + 1];
        }

        $this->data[$this->size - 1] = null;
        $this->size--;

        return $removed;
    }

    /**
     * Insert element at specific index
     *
     * Shifts all subsequent elements right by one position.
     * O(n) operation.
     *
     * @param int $index The index to insert at
     * @param mixed $value The value to insert
     * @return void
     * @throws OutOfBoundsException If index is out of bounds
     */
    public function insert(int $index, mixed $value): void
    {
        if ($index < 0 || $index > $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        // Resize if needed
        if ($this->size === $this->capacity) {
            $this->resize();
        }

        // Shift elements right
        for ($i = $this->size; $i > $index; $i--) {
            $this->data[$i] = $this->data[$i - 1];
        }

        $this->data[$index] = $value;
        $this->size++;
    }

    /**
     * Find first occurrence of a value
     *
     * @param mixed $value The value to find
     * @return int|null The index if found, null otherwise
     */
    public function indexOf(mixed $value): ?int
    {
        for ($i = 0; $i < $this->size; $i++) {
            if ($this->data[$i] === $value) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Check if array contains a value
     *
     * @param mixed $value The value to check
     * @return bool True if found, false otherwise
     */
    public function contains(mixed $value): bool
    {
        return $this->indexOf($value) !== null;
    }

    /**
     * Get current size (number of elements)
     *
     * @return int The size
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Get current capacity
     *
     * @return int The capacity
     */
    public function capacity(): int
    {
        return $this->capacity;
    }

    /**
     * Check if array is empty
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Get number of times array was resized
     *
     * @return int The resize count
     */
    public function getResizeCount(): int
    {
        return $this->resizeCount;
    }

    /**
     * Clear all elements
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = array_fill(0, $this->capacity, null);
        $this->size = 0;
    }

    /**
     * Convert to standard PHP array
     *
     * @return array<mixed> Array of elements
     */
    public function toArray(): array
    {
        return array_slice($this->data, 0, $this->size);
    }

    /**
     * Resize internal storage by doubling capacity
     *
     * This is the key operation that makes dynamic arrays work!
     * Although it's O(n), it happens infrequently enough that
     * the amortized cost of add() remains O(1).
     *
     * @return void
     */
    private function resize(): void
    {
        // Double the capacity
        $this->capacity *= 2;
        $this->resizeCount++;

        // Allocate new array
        $newData = array_fill(0, $this->capacity, null);

        // Copy existing elements
        for ($i = 0; $i < $this->size; $i++) {
            $newData[$i] = $this->data[$i];
        }

        $this->data = $newData;
    }

    /**
     * Get string representation
     *
     * @return string String representation
     */
    public function __toString(): string
    {
        $elements = implode(', ', $this->toArray());
        return "[{$elements}] (size: {$this->size}, capacity: {$this->capacity})";
    }
}
