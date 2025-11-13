<?php

declare(strict_types=1);

/**
 * AVL Tree Implementation (Self-Balancing BST)
 *
 * AVL trees maintain strict balance: height difference between left and right
 * subtrees is at most 1. Guarantees O(log n) operations.
 *
 * Time Complexities (guaranteed):
 * - Search: O(log n)
 * - Insert: O(log n)
 * - Delete: O(log n)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * AVL tree node with height tracking
 */
class AVLNode
{
    public function __construct(
        public mixed $data,
        public ?AVLNode $left = null,
        public ?AVLNode $right = null,
        public int $height = 1
    ) {}
}

/**
 * AVL Tree implementation
 */
class AVLTree
{
    private ?AVLNode $root = null;

    /**
     * Get height of node - O(1)
     *
     * @param AVLNode|null $node The node
     * @return int The height (0 for null)
     */
    private function height(?AVLNode $node): int
    {
        return $node?->height ?? 0;
    }

    /**
     * Update height of node - O(1)
     *
     * @param AVLNode $node The node
     * @return void
     */
    private function updateHeight(AVLNode $node): void
    {
        $node->height = 1 + max(
            $this->height($node->left),
            $this->height($node->right)
        );
    }

    /**
     * Get balance factor - O(1)
     * Positive: left-heavy, Negative: right-heavy
     *
     * @param AVLNode|null $node The node
     * @return int Balance factor
     */
    private function getBalance(?AVLNode $node): int
    {
        if ($node === null) {
            return 0;
        }

        return $this->height($node->left) - $this->height($node->right);
    }

    /**
     * Right rotation - O(1)
     *
     * Before:       y              After:        x
     *              / \                          / \
     *             x   C                        A   y
     *            / \              =>              / \
     *           A   B                            B   C
     *
     * @param AVLNode $y The node to rotate
     * @return AVLNode The new root of subtree
     */
    private function rotateRight(AVLNode $y): AVLNode
    {
        $x = $y->left;
        $B = $x->right;

        // Perform rotation
        $x->right = $y;
        $y->left = $B;

        // Update heights
        $this->updateHeight($y);
        $this->updateHeight($x);

        return $x;
    }

    /**
     * Left rotation - O(1)
     *
     * Before:     x                After:          y
     *            / \                              / \
     *           A   y                            x   C
     *              / \            =>            / \
     *             B   C                        A   B
     *
     * @param AVLNode $x The node to rotate
     * @return AVLNode The new root of subtree
     */
    private function rotateLeft(AVLNode $x): AVLNode
    {
        $y = $x->right;
        $B = $y->left;

        // Perform rotation
        $y->left = $x;
        $x->right = $B;

        // Update heights
        $this->updateHeight($x);
        $this->updateHeight($y);

        return $y;
    }

    /**
     * Insert a value - O(log n)
     *
     * @param mixed $value The value to insert
     * @return void
     */
    public function insert(mixed $value): void
    {
        $this->root = $this->insertNode($this->root, $value);
    }

    /**
     * Helper: Insert node and rebalance - O(log n)
     */
    private function insertNode(?AVLNode $node, mixed $value): AVLNode
    {
        // Standard BST insertion
        if ($node === null) {
            return new AVLNode($value);
        }

        if ($value < $node->data) {
            $node->left = $this->insertNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->insertNode($node->right, $value);
        } else {
            // Duplicates not allowed
            return $node;
        }

        // Update height
        $this->updateHeight($node);

        // Get balance factor
        $balance = $this->getBalance($node);

        // Four cases requiring rebalancing

        // Left-Left Case: Single right rotation
        if ($balance > 1 && $value < $node->left->data) {
            return $this->rotateRight($node);
        }

        // Right-Right Case: Single left rotation
        if ($balance < -1 && $value > $node->right->data) {
            return $this->rotateLeft($node);
        }

        // Left-Right Case: Left rotation then right rotation
        if ($balance > 1 && $value > $node->left->data) {
            $node->left = $this->rotateLeft($node->left);
            return $this->rotateRight($node);
        }

        // Right-Left Case: Right rotation then left rotation
        if ($balance < -1 && $value < $node->right->data) {
            $node->right = $this->rotateRight($node->right);
            return $this->rotateLeft($node);
        }

        return $node;
    }

    /**
     * Find minimum value node in subtree
     */
    private function minValueNode(AVLNode $node): AVLNode
    {
        $current = $node;
        while ($current->left !== null) {
            $current = $current->left;
        }
        return $current;
    }

    /**
     * Delete a value - O(log n)
     *
     * @param mixed $value The value to delete
     * @return void
     */
    public function delete(mixed $value): void
    {
        $this->root = $this->deleteNode($this->root, $value);
    }

    /**
     * Helper: Delete node and rebalance - O(log n)
     */
    private function deleteNode(?AVLNode $node, mixed $value): ?AVLNode
    {
        // Standard BST deletion
        if ($node === null) {
            return null;
        }

        if ($value < $node->data) {
            $node->left = $this->deleteNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->deleteNode($node->right, $value);
        } else {
            // Node found

            // One child or no child
            if ($node->left === null) {
                return $node->right;
            } elseif ($node->right === null) {
                return $node->left;
            }

            // Two children: get inorder successor
            $successor = $this->minValueNode($node->right);
            $node->data = $successor->data;
            $node->right = $this->deleteNode($node->right, $successor->data);
        }

        // Update height
        $this->updateHeight($node);

        // Get balance factor
        $balance = $this->getBalance($node);

        // Rebalance if needed

        // Left-Left Case
        if ($balance > 1 && $this->getBalance($node->left) >= 0) {
            return $this->rotateRight($node);
        }

        // Left-Right Case
        if ($balance > 1 && $this->getBalance($node->left) < 0) {
            $node->left = $this->rotateLeft($node->left);
            return $this->rotateRight($node);
        }

        // Right-Right Case
        if ($balance < -1 && $this->getBalance($node->right) <= 0) {
            return $this->rotateLeft($node);
        }

        // Right-Left Case
        if ($balance < -1 && $this->getBalance($node->right) > 0) {
            $node->right = $this->rotateRight($node->right);
            return $this->rotateLeft($node);
        }

        return $node;
    }

    /**
     * Search for a value - O(log n)
     *
     * @param mixed $value The value to search for
     * @return bool True if found
     */
    public function search(mixed $value): bool
    {
        return $this->searchNode($this->root, $value);
    }

    /**
     * Helper: Search recursively
     */
    private function searchNode(?AVLNode $node, mixed $value): bool
    {
        if ($node === null) {
            return false;
        }

        if ($value === $node->data) {
            return true;
        }

        if ($value < $node->data) {
            return $this->searchNode($node->left, $value);
        }

        return $this->searchNode($node->right, $value);
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
    private function inOrderTraversal(?AVLNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $this->inOrderTraversal($node->left, $result);
        $result[] = $node->data;
        $this->inOrderTraversal($node->right, $result);
    }

    /**
     * Visualize tree with heights and balance factors
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
    private function printTree(?AVLNode $node, string $prefix, bool $isRight): void
    {
        if ($node === null) {
            return;
        }

        echo $prefix;
        echo $isRight ? '└── ' : '├── ';
        $balance = $this->getBalance($node);
        echo "{$node->data} (h:{$node->height}, bf:{$balance})\n";

        $newPrefix = $prefix . ($isRight ? '    ' : '│   ');
        if ($node->left !== null || $node->right !== null) {
            $this->printTree($node->right, $newPrefix, false);
            $this->printTree($node->left, $newPrefix, true);
        }
    }

    /**
     * Get height of tree - O(1)
     *
     * @return int The height
     */
    public function getHeight(): int
    {
        return $this->height($this->root);
    }

    /**
     * Verify tree is balanced
     *
     * @return bool True if balanced
     */
    public function isBalanced(): bool
    {
        return $this->checkBalance($this->root) !== -1;
    }

    /**
     * Helper: Check balance recursively
     */
    private function checkBalance(?AVLNode $node): int
    {
        if ($node === null) {
            return 0;
        }

        $leftHeight = $this->checkBalance($node->left);
        if ($leftHeight === -1) {
            return -1;
        }

        $rightHeight = $this->checkBalance($node->right);
        if ($rightHeight === -1) {
            return -1;
        }

        if (abs($leftHeight - $rightHeight) > 1) {
            return -1; // Unbalanced
        }

        return 1 + max($leftHeight, $rightHeight);
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== AVL Tree Demo ===\n\n";

    $avl = new AVLTree();

    // Insert values that would create skewed BST
    echo "=== Inserting Sequential Values ===\n";
    echo "Inserting: 10, 20, 30, 40, 50, 25\n";
    foreach ([10, 20, 30, 40, 50, 25] as $value) {
        $avl->insert($value);
    }

    echo "\nAVL Tree structure (automatically balanced):\n";
    $avl->visualize();

    echo "\nHeight: " . $avl->getHeight() . " (log₂(6) ≈ 2.58)\n";
    echo "Is balanced: " . ($avl->isBalanced() ? "Yes" : "No") . "\n";

    // In-order traversal
    echo "\n=== In-order Traversal (Sorted) ===\n";
    echo implode(', ', $avl->inOrder()) . "\n";

    // Search operations
    echo "\n=== Search Operations ===\n";
    $searchValues = [25, 35, 50];
    foreach ($searchValues as $val) {
        $found = $avl->search($val);
        echo "Search {$val}: " . ($found ? "Found" : "Not found") . "\n";
    }

    // Delete operations
    echo "\n=== Delete Operations ===\n";

    echo "Deleting 30 (root with two children)...\n";
    $avl->delete(30);
    $avl->visualize();

    echo "\nDeleting 25...\n";
    $avl->delete(25);
    $avl->visualize();

    echo "\nHeight after deletions: " . $avl->getHeight() . "\n";
    echo "Is balanced: " . ($avl->isBalanced() ? "Yes" : "No") . "\n";

    // Comparison with unbalanced BST
    echo "\n=== AVL vs Unbalanced BST ===\n";

    // Create unbalanced BST from Chapter 18
    require_once __DIR__ . '/../chapter-18/01-binary-search-tree.php';
    $bst = new BinarySearchTree();

    echo "Inserting same sequential values into regular BST...\n";
    foreach ([10, 20, 30, 40, 50] as $value) {
        $bst->insert($value);
    }

    echo "\nUnbalanced BST height: " . $bst->height() . " (becomes linked list)\n";

    $avl2 = new AVLTree();
    foreach ([10, 20, 30, 40, 50] as $value) {
        $avl2->insert($value);
    }
    echo "AVL Tree height: " . $avl2->getHeight() . " (stays balanced)\n";

    // Performance implication
    echo "\nFor 1000 elements:\n";
    echo "- Unbalanced BST worst-case depth: 1000 (O(n))\n";
    echo "- AVL Tree guaranteed depth: ~10 (O(log n))\n";
    echo "- Search speedup: ~100x\n";
}
