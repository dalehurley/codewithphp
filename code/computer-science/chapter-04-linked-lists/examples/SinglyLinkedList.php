<?php

declare(strict_types=1);

namespace ComputerScience\Chapter04;

use UnderflowException;
use OutOfBoundsException;

/**
 * Singly Linked List Node
 */
class Node
{
    public function __construct(
        public mixed $data,
        public ?Node $next = null
    ) {}
}

/**
 * Singly Linked List Implementation
 *
 * A linear data structure where elements are stored in nodes,
 * and each node points to the next node in the sequence.
 *
 * Time Complexity:
 * - prepend(): O(1)
 * - append(): O(n) without tail, O(1) with tail
 * - insertAt(): O(n)
 * - deleteFirst(): O(1)
 * - delete(): O(n)
 * - search(): O(n)
 * - get(): O(n)
 * - reverse(): O(n)
 *
 * Space Complexity: O(n)
 *
 * Advantages:
 * - Dynamic size
 * - Efficient insertion/deletion at beginning
 * - No wasted space
 *
 * Disadvantages:
 * - No random access (must traverse)
 * - Extra memory for pointers
 * - Cache inefficient
 */
class SinglyLinkedList
{
    private ?Node $head = null;
    private int $size = 0;

    /**
     * Insert at the beginning - O(1)
     *
     * @param mixed $data Data to insert
     * @return void
     */
    public function prepend(mixed $data): void
    {
        $newNode = new Node($data, $this->head);
        $this->head = $newNode;
        $this->size++;
    }

    /**
     * Insert at the end - O(n)
     *
     * Must traverse entire list to find tail.
     * For O(1) append, use DoublyLinkedList with tail pointer.
     *
     * @param mixed $data Data to insert
     * @return void
     */
    public function append(mixed $data): void
    {
        $newNode = new Node($data);

        if ($this->head === null) {
            $this->head = $newNode;
        } else {
            $current = $this->head;
            while ($current->next !== null) {
                $current = $current->next;
            }
            $current->next = $newNode;
        }

        $this->size++;
    }

    /**
     * Insert at specific position - O(n)
     *
     * @param int $index Position to insert at (0-based)
     * @param mixed $data Data to insert
     * @return void
     * @throws OutOfBoundsException If index out of bounds
     */
    public function insertAt(int $index, mixed $data): void
    {
        if ($index < 0 || $index > $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        if ($index === 0) {
            $this->prepend($data);
            return;
        }

        $newNode = new Node($data);
        $current = $this->head;

        // Traverse to node before insertion point
        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $newNode->next = $current->next;
        $current->next = $newNode;
        $this->size++;
    }

    /**
     * Delete from beginning - O(1)
     *
     * @return mixed The deleted data
     * @throws UnderflowException If list is empty
     */
    public function deleteFirst(): mixed
    {
        if ($this->head === null) {
            throw new UnderflowException("Cannot delete from empty list");
        }

        $data = $this->head->data;
        $this->head = $this->head->next;
        $this->size--;

        return $data;
    }

    /**
     * Delete specific value - O(n)
     *
     * Removes first occurrence of value.
     *
     * @param mixed $value Value to delete
     * @return bool True if deleted, false if not found
     */
    public function delete(mixed $value): bool
    {
        if ($this->head === null) {
            return false;
        }

        // If head node has the value
        if ($this->head->data === $value) {
            $this->head = $this->head->next;
            $this->size--;
            return true;
        }

        $current = $this->head;
        while ($current->next !== null) {
            if ($current->next->data === $value) {
                $current->next = $current->next->next;
                $this->size--;
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    /**
     * Delete at specific index - O(n)
     *
     * @param int $index Position to delete (0-based)
     * @return mixed The deleted data
     * @throws OutOfBoundsException If index out of bounds
     */
    public function deleteAt(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        if ($index === 0) {
            return $this->deleteFirst();
        }

        $current = $this->head;

        // Traverse to node before deletion point
        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $data = $current->next->data;
        $current->next = $current->next->next;
        $this->size--;

        return $data;
    }

    /**
     * Search for a value - O(n)
     *
     * @param mixed $value Value to search for
     * @return bool True if found, false otherwise
     */
    public function search(mixed $value): bool
    {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $value) {
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    /**
     * Get element at index - O(n)
     *
     * @param int $index Position to access (0-based)
     * @return mixed The data at that index
     * @throws OutOfBoundsException If index out of bounds
     */
    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfBoundsException("Index $index out of bounds (size: $this->size)");
        }

        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $current = $current->next;
        }

        return $current->data;
    }

    /**
     * Reverse the list - O(n)
     *
     * Reverses the list in-place by changing pointers.
     *
     * @return void
     */
    public function reverse(): void
    {
        $prev = null;
        $current = $this->head;

        while ($current !== null) {
            $next = $current->next;
            $current->next = $prev;
            $prev = $current;
            $current = $next;
        }

        $this->head = $prev;
    }

    /**
     * Find middle element - O(n)
     *
     * Uses slow/fast pointer technique (Floyd's algorithm).
     *
     * @return mixed|null The middle element or null if empty
     */
    public function findMiddle(): mixed
    {
        if ($this->head === null) {
            return null;
        }

        $slow = $this->head;
        $fast = $this->head;

        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;
        }

        return $slow->data;
    }

    /**
     * Detect cycle in list - O(n)
     *
     * Uses Floyd's Cycle Detection Algorithm.
     *
     * @return bool True if cycle exists, false otherwise
     */
    public function hasCycle(): bool
    {
        if ($this->head === null) {
            return false;
        }

        $slow = $this->head;
        $fast = $this->head;

        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;

            if ($slow === $fast) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current size
     *
     * @return int The number of elements
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Check if list is empty
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->head === null;
    }

    /**
     * Convert to array - O(n)
     *
     * @return array<mixed> Array of elements
     */
    public function toArray(): array
    {
        $result = [];
        $current = $this->head;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->next;
        }

        return $result;
    }

    /**
     * Get string representation
     *
     * @return string String representation
     */
    public function __toString(): string
    {
        if ($this->isEmpty()) {
            return "[]";
        }

        return "[" . implode(" -> ", $this->toArray()) . "]";
    }

    /**
     * Clear all elements
     *
     * @return void
     */
    public function clear(): void
    {
        $this->head = null;
        $this->size = 0;
    }
}
