<?php

declare(strict_types=1);

/**
 * Binary Search Tree (BST) Implementation
 *
 * A complete implementation of Binary Search Tree with all major operations.
 * BST Property: Left subtree < Node < Right subtree
 *
 * Time Complexities:
 * - Search: O(log n) average, O(n) worst
 * - Insert: O(log n) average, O(n) worst
 * - Delete: O(log n) average, O(n) worst
 * - Min/Max: O(h) where h is height
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * Tree node class
 */
class TreeNode
{
    public function __construct(
        public mixed $data,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

/**
 * Binary Search Tree implementation
 */
class BinarySearchTree
{
    private ?TreeNode $root = null;

    /**
     * Insert a value - O(log n) average, O(n) worst
     *
     * @param mixed $value The value to insert
     * @return void
     */
    public function insert(mixed $value): void
    {
        $this->root = $this->insertNode($this->root, $value);
    }

    /**
     * Helper: Insert node recursively
     */
    private function insertNode(?TreeNode $node, mixed $value): TreeNode
    {
        if ($node === null) {
            return new TreeNode($value);
        }

        if ($value < $node->data) {
            $node->left = $this->insertNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->insertNode($node->right, $value);
        }
        // Duplicates: do nothing (or handle as needed)

        return $node;
    }

    /**
     * Search for a value - O(log n) average
     *
     * @param mixed $value The value to search for
     * @return bool True if found, false otherwise
     */
    public function search(mixed $value): bool
    {
        return $this->searchNode($this->root, $value);
    }

    /**
     * Helper: Search recursively
     */
    private function searchNode(?TreeNode $node, mixed $value): bool
    {
        if ($node === null) {
            return false;
        }

        if ($value === $node->data) {
            return true;
        }

        if ($value < $node->data) {
            return $this->searchNode($node->left, $value);
        } else {
            return $this->searchNode($node->right, $value);
        }
    }

    /**
     * Find minimum value - O(h)
     *
     * @return mixed|null The minimum value or null if empty
     */
    public function findMin(): mixed
    {
        if ($this->root === null) {
            return null;
        }

        $node = $this->root;
        while ($node->left !== null) {
            $node = $node->left;
        }

        return $node->data;
    }

    /**
     * Find maximum value - O(h)
     *
     * @return mixed|null The maximum value or null if empty
     */
    public function findMax(): mixed
    {
        if ($this->root === null) {
            return null;
        }

        $node = $this->root;
        while ($node->right !== null) {
            $node = $node->right;
        }

        return $node->data;
    }

    /**
     * Delete a value - O(log n) average
     *
     * @param mixed $value The value to delete
     * @return void
     */
    public function delete(mixed $value): void
    {
        $this->root = $this->deleteNode($this->root, $value);
    }

    /**
     * Helper: Delete node recursively
     */
    private function deleteNode(?TreeNode $node, mixed $value): ?TreeNode
    {
        if ($node === null) {
            return null;
        }

        if ($value < $node->data) {
            $node->left = $this->deleteNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->deleteNode($node->right, $value);
        } else {
            // Node found - handle 3 cases

            // Case 1: No children (leaf)
            if ($node->left === null && $node->right === null) {
                return null;
            }

            // Case 2: One child
            if ($node->left === null) {
                return $node->right;
            }
            if ($node->right === null) {
                return $node->left;
            }

            // Case 3: Two children
            // Find inorder successor (min in right subtree)
            $successor = $this->findMinNode($node->right);
            $node->data = $successor->data;
            $node->right = $this->deleteNode($node->right, $successor->data);
        }

        return $node;
    }

    /**
     * Find minimum node in subtree
     */
    private function findMinNode(TreeNode $node): TreeNode
    {
        while ($node->left !== null) {
            $node = $node->left;
        }
        return $node;
    }

    /**
     * Get height of tree - O(n)
     *
     * @return int The height (-1 for empty tree)
     */
    public function height(): int
    {
        return $this->getHeight($this->root);
    }

    /**
     * Helper: Calculate height recursively
     */
    private function getHeight(?TreeNode $node): int
    {
        if ($node === null) {
            return -1;
        }

        $leftHeight = $this->getHeight($node->left);
        $rightHeight = $this->getHeight($node->right);

        return 1 + max($leftHeight, $rightHeight);
    }

    /**
     * Count nodes - O(n)
     *
     * @return int The number of nodes
     */
    public function size(): int
    {
        return $this->countNodes($this->root);
    }

    /**
     * Helper: Count nodes recursively
     */
    private function countNodes(?TreeNode $node): int
    {
        if ($node === null) {
            return 0;
        }

        return 1 + $this->countNodes($node->left) + $this->countNodes($node->right);
    }

    /**
     * In-order traversal (sorted output) - O(n)
     *
     * @return array The values in sorted order
     */
    public function inOrder(): array
    {
        $result = [];
        $this->inOrderTraversal($this->root, $result);
        return $result;
    }

    /**
     * Helper: In-order traversal
     */
    private function inOrderTraversal(?TreeNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $this->inOrderTraversal($node->left, $result);
        $result[] = $node->data;
        $this->inOrderTraversal($node->right, $result);
    }

    /**
     * Validate if tree is a valid BST
     *
     * @return bool True if valid BST
     */
    public function isValidBST(): bool
    {
        return $this->validateBST($this->root, null, null);
    }

    /**
     * Helper: Validate BST with min/max bounds
     */
    private function validateBST(?TreeNode $node, $min, $max): bool
    {
        if ($node === null) {
            return true;
        }

        if ($min !== null && $node->data <= $min) {
            return false;
        }
        if ($max !== null && $node->data >= $max) {
            return false;
        }

        return $this->validateBST($node->left, $min, $node->data) &&
               $this->validateBST($node->right, $node->data, $max);
    }

    /**
     * Range query: find all values in [min, max]
     *
     * @param mixed $min Minimum value
     * @param mixed $max Maximum value
     * @return array Values in range
     */
    public function rangeQuery(mixed $min, mixed $max): array
    {
        $result = [];
        $this->rangeSearch($this->root, $min, $max, $result);
        return $result;
    }

    /**
     * Helper: Range search
     */
    private function rangeSearch(?TreeNode $node, $min, $max, array &$result): void
    {
        if ($node === null) {
            return;
        }

        if ($min < $node->data) {
            $this->rangeSearch($node->left, $min, $max, $result);
        }

        if ($min <= $node->data && $node->data <= $max) {
            $result[] = $node->data;
        }

        if ($node->data < $max) {
            $this->rangeSearch($node->right, $min, $max, $result);
        }
    }

    /**
     * Visualize tree structure
     *
     * @return void
     */
    public function visualize(): void
    {
        $this->printTree($this->root, '', true);
    }

    /**
     * Helper: Print tree structure
     */
    private function printTree(?TreeNode $node, string $prefix, bool $isRight): void
    {
        if ($node === null) {
            return;
        }

        echo $prefix;
        echo $isRight ? '└── ' : '├── ';
        echo $node->data . "\n";

        if ($node->left !== null || $node->right !== null) {
            if ($node->left !== null) {
                $this->printTree($node->left, $prefix . ($isRight ? '    ' : '│   '), false);
            } else {
                echo $prefix . ($isRight ? '    ' : '│   ') . "├── null\n";
            }

            if ($node->right !== null) {
                $this->printTree($node->right, $prefix . ($isRight ? '    ' : '│   '), true);
            } else {
                echo $prefix . ($isRight ? '    ' : '│   ') . "└── null\n";
            }
        }
    }

    /**
     * Get root node (for advanced operations)
     *
     * @return ?TreeNode The root node
     */
    public function getRoot(): ?TreeNode
    {
        return $this->root;
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Binary Search Tree Demo ===\n\n";

    $bst = new BinarySearchTree();

    // Build tree
    echo "=== Building BST ===\n";
    $values = [8, 3, 10, 1, 6, 14, 4, 7, 13];
    foreach ($values as $val) {
        echo "Inserting: {$val}\n";
        $bst->insert($val);
    }

    echo "\nTree structure:\n";
    $bst->visualize();

    // Search operations
    echo "\n=== Search Operations ===\n";
    $searchValues = [6, 15, 1];
    foreach ($searchValues as $val) {
        $found = $bst->search($val);
        echo "Search {$val}: " . ($found ? "Found" : "Not found") . "\n";
    }

    // Min/Max
    echo "\n=== Min/Max ===\n";
    echo "Minimum: " . $bst->findMin() . "\n";
    echo "Maximum: " . $bst->findMax() . "\n";

    // Tree properties
    echo "\n=== Tree Properties ===\n";
    echo "Height: " . $bst->height() . "\n";
    echo "Size: " . $bst->size() . "\n";
    echo "Is valid BST: " . ($bst->isValidBST() ? "Yes" : "No") . "\n";

    // Traversal
    echo "\n=== In-order Traversal (sorted) ===\n";
    $sorted = $bst->inOrder();
    echo implode(', ', $sorted) . "\n";

    // Range query
    echo "\n=== Range Query [5, 10] ===\n";
    $range = $bst->rangeQuery(5, 10);
    echo "Values in range: " . implode(', ', $range) . "\n";

    // Delete operations
    echo "\n=== Delete Operations ===\n";

    echo "Deleting leaf node (1)...\n";
    $bst->delete(1);
    $bst->visualize();

    echo "\nDeleting node with one child (14)...\n";
    $bst->delete(14);
    $bst->visualize();

    echo "\nDeleting node with two children (3)...\n";
    $bst->delete(3);
    $bst->visualize();

    echo "\nFinal in-order: " . implode(', ', $bst->inOrder()) . "\n";
}
