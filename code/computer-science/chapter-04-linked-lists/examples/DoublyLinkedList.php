<?php

declare(strict_types=1);

namespace ComputerScience\Chapter04;

use UnderflowException;
use OutOfBoundsException;

/**
 * Doubly Linked List Node
 */
class DoublyNode
{
    public function __construct(
        public mixed $data,
        public ?DoublyNode $next = null,
        public ?DoublyNode $prev = null
    ) {}
}

/**
 * Doubly Linked List Implementation
 *
 * Each node has pointers to both next AND previous nodes,
 * allowing bidirectional traversal.
 *
 * Time Complexity:
 * - prepend(): O(1)
 * - append(): O(1) with tail pointer
 * - insertAt(): O(n)
 * - deleteFirst(): O(1)
 * - deleteLast(): O(1) with tail pointer
 * - delete(): O(n)
 * - search(): O(n)
 *
 * Space Complexity: O(n) - twice the pointers of singly linked list
 *
 * Advantages over Singly Linked List:
 * - Can traverse backwards
 * - Easier deletion (don't need to track previous)
 * - O(1) append and deleteLast with tail pointer
 *
 * Use Cases:
 * - LRU Cache implementation
 * - Browser history (back/forward)
 * - Undo/Redo functionality
 * - Music player playlist
 */
class DoublyLinkedList
{
    private ?DoublyNode $head = null;
    private ?DoublyNode $tail = null;
    private int $size = 0;

    /**
     * Insert at the beginning - O(1)
     *
     * @param mixed $data Data to insert
     * @return void
     */
    public function prepend(mixed $data): void
    {
        $newNode = new DoublyNode($data);

        if ($this->head === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $newNode->next = $this->head;
            $this->head->prev = $newNode;
            $this->head = $newNode;
        }

        $this->size++;
    }

    /**
     * Insert at the end - O(1)
     *
     * Much faster than singly linked list due to tail pointer!
     *
     * @param mixed $data Data to insert
     * @return void
     */
    public function append(mixed $data): void
    {
        $newNode = new DoublyNode($data);

        if ($this->tail === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $newNode->prev = $this->tail;
            $this->tail->next = $newNode;
            $this->tail = $newNode;
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

        if ($index === $this->size) {
            $this->append($data);
            return;
        }

        $newNode = new DoublyNode($data);
        $current = $this->head;

        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $newNode->next = $current->next;
        $newNode->prev = $current;
        $current->next->prev = $newNode;
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

        if ($this->head === $this->tail) {
            // Only one element
            $this->head = $this->tail = null;
        } else {
            $this->head = $this->head->next;
            $this->head->prev = null;
        }

        $this->size--;
        return $data;
    }

    /**
     * Delete from end - O(1)
     *
     * This is O(1) in doubly linked list (vs O(n) in singly)!
     *
     * @return mixed The deleted data
     * @throws UnderflowException If list is empty
     */
    public function deleteLast(): mixed
    {
        if ($this->tail === null) {
            throw new UnderflowException("Cannot delete from empty list");
        }

        $data = $this->tail->data;

        if ($this->head === $this->tail) {
            // Only one element
            $this->head = $this->tail = null;
        } else {
            $this->tail = $this->tail->prev;
            $this->tail->next = null;
        }

        $this->size--;
        return $data;
    }

    /**
     * Delete specific node - O(1) if you have the node reference
     *
     * This is the key advantage of doubly linked lists!
     *
     * @param DoublyNode $node Node to delete
     * @return void
     */
    public function deleteNode(DoublyNode $node): void
    {
        // Update previous node
        if ($node->prev !== null) {
            $node->prev->next = $node->next;
        } else {
            $this->head = $node->next;
        }

        // Update next node
        if ($node->next !== null) {
            $node->next->prev = $node->prev;
        } else {
            $this->tail = $node->prev;
        }

        $this->size--;
    }

    /**
     * Delete by value - O(n)
     *
     * @param mixed $value Value to delete
     * @return bool True if deleted, false if not found
     */
    public function delete(mixed $value): bool
    {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $value) {
                $this->deleteNode($current);
                return true;
            }
            $current = $current->next;
        }

        return false;
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
     * Optimized to traverse from closest end (head or tail).
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

        // Optimization: traverse from closest end
        if ($index < $this->size / 2) {
            // Traverse from head
            $current = $this->head;
            for ($i = 0; $i < $index; $i++) {
                $current = $current->next;
            }
        } else {
            // Traverse from tail (backwards)
            $current = $this->tail;
            for ($i = $this->size - 1; $i > $index; $i--) {
                $current = $current->prev;
            }
        }

        return $current->data;
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
     * Convert to array (forward) - O(n)
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
     * Convert to array (reverse) - O(n)
     *
     * Demonstrates backwards traversal capability.
     *
     * @return array<mixed> Array of elements in reverse order
     */
    public function toArrayReverse(): array
    {
        $result = [];
        $current = $this->tail;

        while ($current !== null) {
            $result[] = $current->data;
            $current = $current->prev;
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

        return "[" . implode(" ⇄ ", $this->toArray()) . "]";
    }

    /**
     * Clear all elements
     *
     * @return void
     */
    public function clear(): void
    {
        $this->head = $this->tail = null;
        $this->size = 0;
    }

    /**
     * Get head node (for advanced operations)
     *
     * @return DoublyNode|null The head node
     */
    public function getHead(): ?DoublyNode
    {
        return $this->head;
    }

    /**
     * Get tail node (for advanced operations)
     *
     * @return DoublyNode|null The tail node
     */
    public function getTail(): ?DoublyNode
    {
        return $this->tail;
    }
}
