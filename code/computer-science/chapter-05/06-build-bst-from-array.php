<?php

declare(strict_types=1);

/**
 * Build Balanced BST from Sorted Array
 *
 * Demonstrates:
 * - Converting sorted array to balanced BST
 * - Converting BST to sorted array
 * - Comparing balanced vs unbalanced trees
 */

require_once '01-tree-node-basics.php';
require_once '02-tree-traversals.php';

echo "=== Build Balanced BST from Sorted Array ===\n\n";

/**
 * Build a height-balanced BST from sorted array
 * Time: O(n), Space: O(log n) for recursion
 */
function sortedArrayToBST(array $nums): ?TreeNode
{
    if (empty($nums)) {
        return null;
    }

    return buildBST($nums, 0, count($nums) - 1);
}

function buildBST(array $nums, int $left, int $right): ?TreeNode
{
    if ($left > $right) {
        return null;
    }

    // Use middle element as root to ensure balance
    $mid = intval(($left + $right) / 2);
    $root = new TreeNode($nums[$mid]);

    // Recursively build left and right subtrees
    $root->left = buildBST($nums, $left, $mid - 1);
    $root->right = buildBST($nums, $mid + 1, $right);

    return $root;
}

// Test with sorted array
$sortedArray = [1, 2, 3, 4, 5, 6, 7, 8, 9];
echo "Sorted array: " . json_encode($sortedArray) . "\n\n";

$balancedBST = sortedArrayToBST($sortedArray);

echo "Balanced BST created!\n";
echo "Height: " . getHeight($balancedBST) . "\n";
echo "Inorder traversal: " . json_encode(inorderTraversal($balancedBST)) . "\n";
echo "Level order: " . json_encode(levelOrderTraversal($balancedBST)) . "\n\n";

// Compare with unbalanced (skewed) tree
echo "Comparison: Balanced vs Unbalanced\n";
echo "=====================================\n\n";

// Build unbalanced tree (insert in sorted order)
echo "Unbalanced tree (inserted in sorted order):\n";
$unbalancedRoot = null;
foreach ($sortedArray as $val) {
    if ($unbalancedRoot === null) {
        $unbalancedRoot = new TreeNode($val);
    } else {
        $current = $unbalancedRoot;
        while ($current->right !== null) {
            $current = $current->right;
        }
        $current->right = new TreeNode($val);
    }
}

echo "Height: " . getHeight($unbalancedRoot) . "\n";
echo "Structure: Linear (like a linked list)\n\n";

echo "Balanced tree:\n";
echo "Height: " . getHeight($balancedBST) . "\n";
echo "Structure: Logarithmic height\n\n";

echo "Performance Impact:\n";
echo "  Array size: " . count($sortedArray) . " elements\n";
echo "  Unbalanced height: " . getHeight($unbalancedRoot) . " (worst case: O(n) search)\n";
echo "  Balanced height: " . getHeight($balancedBST) . " (best case: O(log n) search)\n\n";

// Visualize balanced tree structure
echo "Balanced Tree Visualization:\n";
visualizeTree($balancedBST);

function visualizeTree(?TreeNode $node, string $prefix = "", bool $isLeft = true): void
{
    if ($node === null) {
        return;
    }

    echo $prefix . ($isLeft ? "├── " : "└── ") . $node->value . "\n";

    $newPrefix = $prefix . ($isLeft ? "│   " : "    ");

    if ($node->left !== null || $node->right !== null) {
        visualizeTree($node->left, $newPrefix, true);
        visualizeTree($node->right, $newPrefix, false);
    }
}

// Reverse: BST to sorted array
function bstToSortedArray(?TreeNode $root): array
{
    return inorderTraversal($root);
}

echo "\nReverse operation (BST → Sorted Array):\n";
echo json_encode(bstToSortedArray($balancedBST)) . "\n";

echo "\n✅ Complete! Balanced BST construction demonstrated.\n";
