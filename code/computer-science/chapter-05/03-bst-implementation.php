<?php

declare(strict_types=1);

/**
 * Binary Search Tree (BST) Complete Implementation
 *
 * Demonstrates:
 * - BST class with insert, search, delete operations
 * - Finding min/max values
 * - Height calculation
 * - BST validation
 * - Sorted output via inorder traversal
 */

require_once '01-tree-node-basics.php';
require_once '02-tree-traversals.php';

class BinarySearchTree
{
    private ?TreeNode $root = null;

    /**
     * Insert a value into the BST
     * Time: O(h) where h is height
     */
    public function insert(mixed $value): void
    {
        $this->root = $this->insertNode($this->root, $value);
    }

    private function insertNode(?TreeNode $node, mixed $value): TreeNode
    {
        if ($node === null) {
            return new TreeNode($value);
        }

        if ($value < $node->value) {
            $node->left = $this->insertNode($node->left, $value);
        } elseif ($value > $node->value) {
            $node->right = $this->insertNode($node->right, $value);
        }
        // If equal, don't insert (no duplicates)

        return $node;
    }

    /**
     * Search for a value in the BST
     * Time: O(h)
     */
    public function search(mixed $value): bool
    {
        return $this->searchNode($this->root, $value);
    }

    private function searchNode(?TreeNode $node, mixed $value): bool
    {
        if ($node === null) {
            return false;
        }

        if ($value === $node->value) {
            return true;
        }

        if ($value < $node->value) {
            return $this->searchNode($node->left, $value);
        }

        return $this->searchNode($node->right, $value);
    }

    /**
     * Delete a value from the BST
     * Time: O(h)
     */
    public function delete(mixed $value): void
    {
        $this->root = $this->deleteNode($this->root, $value);
    }

    private function deleteNode(?TreeNode $node, mixed $value): ?TreeNode
    {
        if ($node === null) {
            return null;
        }

        if ($value < $node->value) {
            $node->left = $this->deleteNode($node->left, $value);
        } elseif ($value > $node->value) {
            $node->right = $this->deleteNode($node->right, $value);
        } else {
            // Node found - delete it

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
            // Find inorder successor (smallest in right subtree)
            $successor = $this->findMin($node->right);
            $node->value = $successor->value;
            $node->right = $this->deleteNode($node->right, $successor->value);
        }

        return $node;
    }

    /**
     * Find minimum value in tree
     */
    public function findMinimum(): ?int
    {
        if ($this->root === null) {
            return null;
        }
        return $this->findMin($this->root)->value;
    }

    private function findMin(TreeNode $node): TreeNode
    {
        while ($node->left !== null) {
            $node = $node->left;
        }
        return $node;
    }

    /**
     * Find maximum value in tree
     */
    public function findMaximum(): ?int
    {
        if ($this->root === null) {
            return null;
        }
        return $this->findMax($this->root)->value;
    }

    private function findMax(TreeNode $node): TreeNode
    {
        while ($node->right !== null) {
            $node = $node->right;
        }
        return $node;
    }

    /**
     * Get tree height
     */
    public function height(): int
    {
        return $this->getHeight($this->root);
    }

    private function getHeight(?TreeNode $node): int
    {
        if ($node === null) {
            return -1;
        }

        return 1 + max(
            $this->getHeight($node->left),
            $this->getHeight($node->right)
        );
    }

    /**
     * Check if tree is a valid BST
     */
    public function isValidBST(): bool
    {
        return $this->isValidBSTNode($this->root, null, null);
    }

    private function isValidBSTNode(?TreeNode $node, $min, $max): bool
    {
        if ($node === null) {
            return true;
        }

        if (($min !== null && $node->value <= $min) ||
            ($max !== null && $node->value >= $max)) {
            return false;
        }

        return $this->isValidBSTNode($node->left, $min, $node->value) &&
               $this->isValidBSTNode($node->right, $node->value, $max);
    }

    /**
     * Get all values in sorted order
     */
    public function inorder(): array
    {
        return inorderTraversal($this->root);
    }

    /**
     * Get the root node (for testing)
     */
    public function getRoot(): ?TreeNode
    {
        return $this->root;
    }
}

// Demo
echo "=== Binary Search Tree Implementation ===\n\n";

$bst = new BinarySearchTree();

echo "Inserting values: 5, 3, 7, 2, 4, 8, 6\n";
foreach ([5, 3, 7, 2, 4, 8, 6] as $value) {
    $bst->insert($value);
}

echo "\nTree structure (inorder - sorted):\n";
echo json_encode($bst->inorder()) . "\n\n";

echo "Tree properties:\n";
echo "  Min value: " . $bst->findMinimum() . "\n";
echo "  Max value: " . $bst->findMaximum() . "\n";
echo "  Height: " . $bst->height() . "\n";
echo "  Is valid BST: " . ($bst->isValidBST() ? 'Yes' : 'No') . "\n\n";

echo "Search operations:\n";
foreach ([4, 10, 7, 1] as $value) {
    $found = $bst->search($value) ? "✓ Found" : "✗ Not found";
    echo "  Search for $value: $found\n";
}

echo "\nDeleting 3 (node with 2 children):\n";
$bst->delete(3);
echo "After deletion: " . json_encode($bst->inorder()) . "\n";

echo "\nDeleting 7:\n";
$bst->delete(7);
echo "After deletion: " . json_encode($bst->inorder()) . "\n";

echo "\n✅ Complete! BST implementation demonstrated.\n";
