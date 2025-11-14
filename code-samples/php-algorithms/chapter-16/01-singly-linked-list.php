<?php

declare(strict_types=1);

/**
 * Singly Linked List Implementation
 *
 * A singly linked list where each node points to the next node.
 * Demonstrates basic operations: prepend, append, delete, search.
 *
 * Time Complexities:
 * - Prepend: O(1)
 * - Append: O(n) without tail pointer, O(1) with tail
 * - Delete: O(n)
 * - Search: O(n)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * Node class representing a single element in the linked list
 */
class Node
{
    public function __construct(
        public mixed $data,
        public ?Node $next = null
    ) {}
}

/**
 * Singly Linked List implementation with tail pointer for O(1) append
 */
class LinkedList
{
    private ?Node $head = null;
    private ?Node $tail = null;
    private int $size = 0;

    /**
     * Add element to the beginning of the list - O(1)
     *
     * @param mixed $data The data to add
     * @return void
     */
    public function prepend(mixed $data): void
    {
        $newNode = new Node($data);
        $newNode->next = $this->head;
        $this->head = $newNode;

        // Update tail if list was empty
        if ($this->tail === null) {
            $this->tail = $newNode;
        }

        $this->size++;
    }

    /**
     * Add element to the end of the list - O(1) with tail pointer
     *
     * @param mixed $data The data to add
     * @return void
     */
    public function append(mixed $data): void
    {
        $newNode = new Node($data);

        if ($this->head === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $this->tail->next = $newNode;
            $this->tail = $newNode;
        }

        $this->size++;
    }

    /**
     * Delete first occurrence of a value - O(n)
     *
     * @param mixed $data The value to delete
     * @return bool True if deleted, false if not found
     */
    public function delete(mixed $data): bool
    {
        if ($this->head === null) {
            return false;
        }

        // Special case: delete head
        if ($this->head->data === $data) {
            $this->head = $this->head->next;

            // Update tail if list is now empty
            if ($this->head === null) {
                $this->tail = null;
            }

            $this->size--;
            return true;
        }

        // Find node before the one to delete
        $current = $this->head;
        while ($current->next !== null) {
            if ($current->next->data === $data) {
                // Update tail if deleting last node
                if ($current->next === $this->tail) {
                    $this->tail = $current;
                }

                $current->next = $current->next->next;
                $this->size--;
                return true;
            }
            $current = $current->next;
        }

        return false;
    }

    /**
     * Search for a value in the list - O(n)
     *
     * @param mixed $data The value to search for
     * @return ?Node The node if found, null otherwise
     */
    public function search(mixed $data): ?Node
    {
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $data) {
                return $current;
            }
            $current = $current->next;
        }

        return null;
    }

    /**
     * Get the size of the list - O(1)
     *
     * @return int The number of elements
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Check if list is empty - O(1)
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->head === null;
    }

    /**
     * Display the list as a string
     *
     * @return void
     */
    public function display(): void
    {
        if ($this->isEmpty()) {
            echo "Empty list\n";
            return;
        }

        $current = $this->head;
        $values = [];

        while ($current !== null) {
            $values[] = $current->data;
            $current = $current->next;
        }

        echo implode(' → ', $values) . ' → null' . "\n";
    }

    /**
     * Convert list to array - O(n)
     *
     * @return array The list elements as an array
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
     * Insert at specific position - O(n)
     *
     * @param int $index Position to insert at (0-indexed)
     * @param mixed $data The data to insert
     * @return void
     * @throws OutOfRangeException if index is invalid
     */
    public function insertAt(int $index, mixed $data): void
    {
        if ($index < 0 || $index > $this->size) {
            throw new OutOfRangeException("Index {$index} out of range [0, {$this->size}]");
        }

        if ($index === 0) {
            $this->prepend($data);
            return;
        }

        if ($index === $this->size) {
            $this->append($data);
            return;
        }

        $newNode = new Node($data);
        $current = $this->head;

        for ($i = 0; $i < $index - 1; $i++) {
            $current = $current->next;
        }

        $newNode->next = $current->next;
        $current->next = $newNode;
        $this->size++;
    }

    /**
     * Get element at specific index - O(n)
     *
     * @param int $index The index to get
     * @return mixed The data at that index
     * @throws OutOfRangeException if index is invalid
     */
    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= $this->size) {
            throw new OutOfRangeException("Index {$index} out of range [0, " . ($this->size - 1) . "]");
        }

        $current = $this->head;
        for ($i = 0; $i < $index; $i++) {
            $current = $current->next;
        }

        return $current->data;
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Singly Linked List Demo ===\n\n";

    $list = new LinkedList();

    // Test append
    echo "Appending values 10, 20, 30...\n";
    $list->append(10);
    $list->append(20);
    $list->append(30);
    $list->display(); // 10 → 20 → 30 → null

    // Test prepend
    echo "\nPrepending value 5...\n";
    $list->prepend(5);
    $list->display(); // 5 → 10 → 20 → 30 → null

    // Test search
    echo "\nSearching for 20...\n";
    $found = $list->search(20);
    echo ($found !== null ? "Found: {$found->data}" : "Not found") . "\n";

    echo "\nSearching for 99...\n";
    $found = $list->search(99);
    echo ($found !== null ? "Found: {$found->data}" : "Not found") . "\n";

    // Test delete
    echo "\nDeleting 20...\n";
    $deleted = $list->delete(20);
    echo ($deleted ? "Successfully deleted" : "Not found") . "\n";
    $list->display(); // 5 → 10 → 30 → null

    // Test insertAt
    echo "\nInserting 15 at position 2...\n";
    try {
        $list->insertAt(2, 15);
        $list->display(); // 5 → 10 → 15 → 30 → null
    } catch (OutOfRangeException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    // Test get
    echo "\nGetting element at index 1...\n";
    try {
        $value = $list->get(1);
        echo "Value at index 1: {$value}\n";
    } catch (OutOfRangeException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    // Test size
    echo "\nList size: " . $list->getSize() . "\n";

    // Test toArray
    echo "\nConverting to array...\n";
    print_r($list->toArray());

    // Test edge cases
    echo "\n=== Testing Edge Cases ===\n";

    $emptyList = new LinkedList();
    echo "Empty list: ";
    $emptyList->display();

    echo "Is empty: " . ($emptyList->isEmpty() ? "yes" : "no") . "\n";

    echo "Deleting from empty list: ";
    echo ($emptyList->delete(1) ? "success" : "failed") . "\n";
}
