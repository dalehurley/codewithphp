<?php

declare(strict_types=1);

/**
 * BST Range Queries
 *
 * Demonstrates:
 * - Finding values in a range
 * - Kth smallest element
 * - Kth largest element
 * - Count nodes in range
 */

require_once '01-tree-node-basics.php';
require_once '02-tree-traversals.php';

echo "=== BST Range Queries ===\n\n";

// Build test BST:
//        10
//       /  \
//      5    15
//     / \   / \
//    3   7 13  17
//   /   /     \
//  1   6      20

$root = new TreeNode(10);
$root->left = new TreeNode(5);
$root->right = new TreeNode(15);
$root->left->left = new TreeNode(3);
$root->left->right = new TreeNode(7);
$root->right->left = new TreeNode(13);
$root->right->right = new TreeNode(17);
$root->left->left->left = new TreeNode(1);
$root->left->right->left = new TreeNode(6);
$root->right->right->right = new TreeNode(20);

echo "BST values (inorder): " . json_encode(inorderTraversal($root)) . "\n\n";

/**
 * Find all values in range [low, high]
 * Time: O(k + log n) where k is number of results
 */
function rangeSumBST(?TreeNode $root, int $low, int $high): array
{
    $result = [];
    rangeSearch($root, $low, $high, $result);
    return $result;
}

function rangeSearch(?TreeNode $node, int $low, int $high, array &$result): void
{
    if ($node === null) {
        return;
    }

    // If current value is in range, add it
    if ($node->value >= $low && $node->value <= $high) {
        $result[] = $node->value;
    }

    // Search left subtree if there could be values in range
    if ($node->value > $low) {
        rangeSearch($node->left, $low, $high, $result);
    }

    // Search right subtree if there could be values in range
    if ($node->value < $high) {
        rangeSearch($node->right, $low, $high, $result);
    }
}

echo "1. Range Queries\n";
echo "   Values in range [5, 15]: " . json_encode(rangeSumBST($root, 5, 15)) . "\n";
echo "   Values in range [1, 7]: " . json_encode(rangeSumBST($root, 1, 7)) . "\n";
echo "   Values in range [12, 18]: " . json_encode(rangeSumBST($root, 12, 18)) . "\n\n";

/**
 * Find Kth smallest element (1-indexed)
 * Time: O(h + k) where h is height
 */
function kthSmallest(?TreeNode $root, int $k): ?int
{
    $count = 0;
    $result = null;

    function inorderWithCounter(?TreeNode $node, int $k, int &$count, &$result): void
    {
        if ($node === null || $result !== null) {
            return;
        }

        inorderWithCounter($node->left, $k, $count, $result);

        $count++;
        if ($count === $k) {
            $result = $node->value;
            return;
        }

        inorderWithCounter($node->right, $k, $count, $result);
    }

    inorderWithCounter($root, $k, $count, $result);
    return $result;
}

echo "2. Kth Smallest Element\n";
for ($k = 1; $k <= 3; $k++) {
    $value = kthSmallest($root, $k);
    echo "   {$k}th smallest: $value\n";
}
echo "\n";

/**
 * Find Kth largest element
 */
function kthLargest(?TreeNode $root, int $k): ?int
{
    $count = 0;
    $result = null;

    function reverseInorder(?TreeNode $node, int $k, int &$count, &$result): void
    {
        if ($node === null || $result !== null) {
            return;
        }

        reverseInorder($node->right, $k, $count, $result);

        $count++;
        if ($count === $k) {
            $result = $node->value;
            return;
        }

        reverseInorder($node->left, $k, $count, $result);
    }

    reverseInorder($root, $k, $count, $result);
    return $result;
}

echo "3. Kth Largest Element\n";
for ($k = 1; $k <= 3; $k++) {
    $value = kthLargest($root, $k);
    echo "   {$k}th largest: $value\n";
}
echo "\n";

/**
 * Count nodes in range
 */
function countInRange(?TreeNode $node, int $low, int $high): int
{
    if ($node === null) {
        return 0;
    }

    $count = 0;

    if ($node->value >= $low && $node->value <= $high) {
        $count = 1;
    }

    if ($node->value > $low) {
        $count += countInRange($node->left, $low, $high);
    }

    if ($node->value < $high) {
        $count += countInRange($node->right, $low, $high);
    }

    return $count;
}

echo "4. Count Nodes in Range\n";
echo "   Count in [5, 15]: " . countInRange($root, 5, 15) . "\n";
echo "   Count in [1, 20]: " . countInRange($root, 1, 20) . "\n";
echo "   Count in [11, 16]: " . countInRange($root, 11, 16) . "\n\n";

/**
 * Find closest value to target
 */
function closestValue(?TreeNode $root, float $target): ?int
{
    $closest = $root->value;
    $node = $root;

    while ($node !== null) {
        if (abs($node->value - $target) < abs($closest - $target)) {
            $closest = $node->value;
        }

        if ($target < $node->value) {
            $node = $node->left;
        } elseif ($target > $node->value) {
            $node = $node->right;
        } else {
            return $node->value;
        }
    }

    return $closest;
}

echo "5. Find Closest Value\n";
foreach ([8.5, 4.7, 18.2, 10.0] as $target) {
    $closest = closestValue($root, $target);
    echo "   Closest to $target: $closest\n";
}

echo "\n✅ Complete! Range query operations demonstrated.\n";
