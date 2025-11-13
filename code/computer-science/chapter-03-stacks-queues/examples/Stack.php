<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03;

use UnderflowException;

/**
 * Stack Implementation (LIFO - Last In, First Out)
 *
 * A stack is a linear data structure that follows the LIFO principle.
 * Elements are added and removed from the same end (the "top").
 *
 * Time Complexity:
 * - push(): O(1)
 * - pop(): O(1)
 * - peek(): O(1)
 * - isEmpty(): O(1)
 * - size(): O(1)
 *
 * Space Complexity: O(n) where n is the number of elements
 */
class Stack
{
    /** @var array<mixed> Internal storage for stack elements */
    private array $items = [];

    /**
     * Add an element to the top of the stack
     *
     * @param mixed $item The item to push onto the stack
     * @return void
     */
    public function push(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Remove and return the top element from the stack
     *
     * @return mixed The top element
     * @throws UnderflowException if the stack is empty
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot pop from an empty stack");
        }

        return array_pop($this->items);
    }

    /**
     * View the top element without removing it
     *
     * @return mixed The top element
     * @throws UnderflowException if the stack is empty
     */
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot peek an empty stack");
        }

        return end($this->items);
    }

    /**
     * Check if the stack is empty
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get the number of elements in the stack
     *
     * @return int The size of the stack
     */
    public function size(): int
    {
        return count($this->items);
    }

    /**
     * Clear all elements from the stack
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
     * @return array<mixed> Array representation of the stack
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Create a string representation of the stack
     *
     * @return string String representation
     */
    public function __toString(): string
    {
        return 'Stack[' . implode(', ', $this->items) . '] ← top';
    }
}
