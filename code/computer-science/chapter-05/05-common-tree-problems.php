<?php

declare(strict_types=1);

/**
 * Common Tree Problems
 *
 * Demonstrates solutions to classic tree problems:
 * - Maximum depth
 * - Minimum depth
 * - Path sum
 * - Lowest common ancestor
 * - Invert a tree
 * - Diameter of tree
 */

require_once '01-tree-node-basics.php';

echo "=== Common Tree Problems ===\n\n";

// Build test tree:
//        5
//       / \
//      3   7
//     / \   \
//    2   4   8

$root = new TreeNode(5);
$root->left = new TreeNode(3);
$root->right = new TreeNode(7);
$root->left->left = new TreeNode(2);
$root->left->right = new TreeNode(4);
$root->right->right = new TreeNode(8);

// Problem 1: Maximum Depth
function maxDepth(?TreeNode $node): int
{
    if ($node === null) {
        return 0;
    }

    return 1 + max(
        maxDepth($node->left),
        maxDepth($node->right)
    );
}

echo "1. Maximum Depth\n";
echo "   Result: " . maxDepth($root) . "\n\n";

// Problem 2: Minimum Depth
function minDepth(?TreeNode $node): int
{
    if ($node === null) {
        return 0;
    }

    // If only one subtree exists, go down that path
    if ($node->left === null) {
        return 1 + minDepth($node->right);
    }
    if ($node->right === null) {
        return 1 + minDepth($node->left);
    }

    return 1 + min(
        minDepth($node->left),
        minDepth($node->right)
    );
}

echo "2. Minimum Depth\n";
echo "   Result: " . minDepth($root) . "\n\n";

// Problem 3: Path Sum
// Does a root-to-leaf path with given sum exist?
function hasPathSum(?TreeNode $node, int $targetSum): bool
{
    if ($node === null) {
        return false;
    }

    // Leaf node check
    if ($node->left === null && $node->right === null) {
        return $targetSum === $node->value;
    }

    $remainingSum = $targetSum - $node->value;
    return hasPathSum($node->left, $remainingSum) ||
           hasPathSum($node->right, $remainingSum);
}

echo "3. Path Sum\n";
echo "   Target sum 10: " . (hasPathSum($root, 10) ? "✓ Path exists" : "✗ No path") . "\n";
echo "   Target sum 15: " . (hasPathSum($root, 15) ? "✓ Path exists" : "✗ No path") . "\n";
echo "   Target sum 20: " . (hasPathSum($root, 20) ? "✓ Path exists" : "✗ No path") . "\n\n";

// Problem 4: Lowest Common Ancestor (BST version)
function lowestCommonAncestorBST(
    TreeNode $root,
    int $p,
    int $q
): TreeNode {
    if ($p < $root->value && $q < $root->value) {
        return lowestCommonAncestorBST($root->left, $p, $q);
    }

    if ($p > $root->value && $q > $root->value) {
        return lowestCommonAncestorBST($root->right, $p, $q);
    }

    return $root; // Split point
}

echo "4. Lowest Common Ancestor (LCA)\n";
$lca = lowestCommonAncestorBST($root, 2, 4);
echo "   LCA of 2 and 4: {$lca->value}\n";
$lca = lowestCommonAncestorBST($root, 2, 8);
echo "   LCA of 2 and 8: {$lca->value}\n\n";

// Problem 5: Invert Tree
function invertTree(?TreeNode $node): ?TreeNode
{
    if ($node === null) {
        return null;
    }

    // Swap left and right children
    [$node->left, $node->right] = [$node->right, $node->left];

    invertTree($node->left);
    invertTree($node->right);

    return $node;
}

// Create a copy to invert
$invertRoot = new TreeNode(5);
$invertRoot->left = new TreeNode(3);
$invertRoot->right = new TreeNode(7);
$invertRoot->left->left = new TreeNode(2);
$invertRoot->left->right = new TreeNode(4);

require_once '02-tree-traversals.php';

echo "5. Invert Tree\n";
echo "   Before: " . json_encode(inorderTraversal($invertRoot)) . "\n";
invertTree($invertRoot);
echo "   After:  " . json_encode(inorderTraversal($invertRoot)) . "\n\n";

// Problem 6: Diameter of Tree
// Longest path between any two nodes
$diameter = 0;

function diameterOfTree(?TreeNode $node): int
{
    global $diameter;
    $diameter = 0;
    calculateDiameter($node);
    return $diameter;
}

function calculateDiameter(?TreeNode $node): int
{
    global $diameter;

    if ($node === null) {
        return 0;
    }

    $leftDepth = calculateDiameter($node->left);
    $rightDepth = calculateDiameter($node->right);

    // Update diameter if path through this node is longer
    $diameter = max($diameter, $leftDepth + $rightDepth);

    return 1 + max($leftDepth, $rightDepth);
}

echo "6. Diameter of Tree\n";
echo "   Longest path: " . diameterOfTree($root) . " edges\n\n";

// Problem 7: Sum of all values
function sumOfAllNodes(?TreeNode $node): int
{
    if ($node === null) {
        return 0;
    }

    return $node->value +
           sumOfAllNodes($node->left) +
           sumOfAllNodes($node->right);
}

echo "7. Sum of All Nodes\n";
echo "   Total sum: " . sumOfAllNodes($root) . "\n\n";

echo "✅ Complete! Common tree problems solved.\n";
