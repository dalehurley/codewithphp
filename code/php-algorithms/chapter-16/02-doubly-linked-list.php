<?php

declare(strict_types=1);

/**
 * Doubly Linked List Implementation
 *
 * A doubly linked list where each node has pointers to both next and previous nodes.
 * Allows bidirectional traversal and O(1) insertion/deletion at both ends.
 *
 * Time Complexities:
 * - Prepend: O(1)
 * - Append: O(1)
 * - Delete: O(n) for search, O(1) if node reference known
 * - Search: O(n)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * Node class for doubly linked list with previous and next pointers
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
 * Doubly Linked List implementation with head and tail pointers
 */
class DoublyLinkedList
{
    private ?DoublyNode $head = null;
    private ?DoublyNode $tail = null;
    private int $size = 0;

    /**
     * Add element to the beginning - O(1)
     *
     * @param mixed $data The data to add
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
     * Add element to the end - O(1)
     *
     * @param mixed $data The data to add
     * @return void
     */
    public function append(mixed $data): void
    {
        $newNode = new DoublyNode($data);

        if ($this->tail === null) {
            $this->head = $this->tail = $newNode;
        } else {
            $this->tail->next = $newNode;
            $newNode->prev = $this->tail;
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
        $current = $this->head;

        while ($current !== null) {
            if ($current->data === $data) {
                // Update previous node's next pointer
                if ($current->prev !== null) {
                    $current->prev->next = $current->next;
                } else {
                    // Deleting head
                    $this->head = $current->next;
                }

                // Update next node's prev pointer
                if ($current->next !== null) {
                    $current->next->prev = $current->prev;
                } else {
                    // Deleting tail
                    $this->tail = $current->prev;
                }

                $this->size--;
                return true;
            }

            $current = $current->next;
        }

        return false;
    }

    /**
     * Delete node by reference - O(1)
     * Useful when you already have a reference to the node
     *
     * @param DoublyNode $node The node to delete
     * @return void
     */
    public function deleteNode(DoublyNode $node): void
    {
        if ($node->prev !== null) {
            $node->prev->next = $node->next;
        } else {
            $this->head = $node->next;
        }

        if ($node->next !== null) {
            $node->next->prev = $node->prev;
        } else {
            $this->tail = $node->prev;
        }

        $this->size--;
    }

    /**
     * Search for a value - O(n)
     *
     * @param mixed $data The value to search for
     * @return ?DoublyNode The node if found, null otherwise
     */
    public function search(mixed $data): ?DoublyNode
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
     * Display list forward (head to tail)
     *
     * @return void
     */
    public function displayForward(): void
    {
        if ($this->head === null) {
            echo "Empty list\n";
            return;
        }

        $current = $this->head;
        $values = [];

        while ($current !== null) {
            $values[] = $current->data;
            $current = $current->next;
        }

        echo 'null ← ' . implode(' ↔ ', $values) . ' → null' . "\n";
    }

    /**
     * Display list backward (tail to head)
     *
     * @return void
     */
    public function displayBackward(): void
    {
        if ($this->tail === null) {
            echo "Empty list\n";
            return;
        }

        $current = $this->tail;
        $values = [];

        while ($current !== null) {
            $values[] = $current->data;
            $current = $current->prev;
        }

        echo 'null ← ' . implode(' ↔ ', $values) . ' → null' . "\n";
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
     * Insert after a specific node - O(1)
     *
     * @param DoublyNode $node The node to insert after
     * @param mixed $data The data to insert
     * @return void
     */
    public function insertAfter(DoublyNode $node, mixed $data): void
    {
        $newNode = new DoublyNode($data);

        $newNode->next = $node->next;
        $newNode->prev = $node;

        if ($node->next !== null) {
            $node->next->prev = $newNode;
        } else {
            // Inserting after tail
            $this->tail = $newNode;
        }

        $node->next = $newNode;
        $this->size++;
    }

    /**
     * Insert before a specific node - O(1)
     *
     * @param DoublyNode $node The node to insert before
     * @param mixed $data The data to insert
     * @return void
     */
    public function insertBefore(DoublyNode $node, mixed $data): void
    {
        $newNode = new DoublyNode($data);

        $newNode->prev = $node->prev;
        $newNode->next = $node;

        if ($node->prev !== null) {
            $node->prev->next = $newNode;
        } else {
            // Inserting before head
            $this->head = $newNode;
        }

        $node->prev = $newNode;
        $this->size++;
    }

    /**
     * Get head node - O(1)
     *
     * @return ?DoublyNode The head node or null if empty
     */
    public function getHead(): ?DoublyNode
    {
        return $this->head;
    }

    /**
     * Get tail node - O(1)
     *
     * @return ?DoublyNode The tail node or null if empty
     */
    public function getTail(): ?DoublyNode
    {
        return $this->tail;
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Doubly Linked List Demo ===\n\n";

    $list = new DoublyLinkedList();

    // Test append and prepend
    echo "Building list: 10, 20, 30\n";
    $list->append(10);
    $list->append(20);
    $list->append(30);
    $list->displayForward(); // null ← 10 ↔ 20 ↔ 30 → null

    echo "\nPrepending 5...\n";
    $list->prepend(5);
    $list->displayForward(); // null ← 5 ↔ 10 ↔ 20 ↔ 30 → null

    // Test bidirectional traversal
    echo "\nBackward traversal:\n";
    $list->displayBackward(); // null ← 30 ↔ 20 ↔ 10 ↔ 5 → null

    // Test search and insertAfter
    echo "\n=== Testing Insert After ===\n";
    $node = $list->search(20);
    if ($node !== null) {
        echo "Found node with value 20, inserting 25 after it...\n";
        $list->insertAfter($node, 25);
        $list->displayForward();
    }

    // Test insertBefore
    echo "\n=== Testing Insert Before ===\n";
    $node = $list->search(10);
    if ($node !== null) {
        echo "Found node with value 10, inserting 7 before it...\n";
        $list->insertBefore($node, 7);
        $list->displayForward();
    }

    // Test deleteNode (O(1) deletion)
    echo "\n=== Testing O(1) Node Deletion ===\n";
    $node = $list->search(25);
    if ($node !== null) {
        echo "Deleting node with value 25 by reference...\n";
        $list->deleteNode($node);
        $list->displayForward();
    }

    // Test regular delete
    echo "\nDeleting value 20...\n";
    $list->delete(20);
    $list->displayForward();

    // Test size and array conversion
    echo "\nList size: " . $list->getSize() . "\n";
    echo "As array: ";
    print_r($list->toArray());

    // Test edge cases
    echo "\n=== Testing Edge Cases ===\n";

    $singleList = new DoublyLinkedList();
    $singleList->append(42);
    echo "Single element list:\n";
    $singleList->displayForward();
    $singleList->displayBackward();

    echo "\nDeleting single element...\n";
    $singleList->delete(42);
    echo "After deletion (should be empty): ";
    $singleList->displayForward();
    echo "Is empty: " . ($singleList->isEmpty() ? "yes" : "no") . "\n";
}
