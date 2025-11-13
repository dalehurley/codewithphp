<?php

declare(strict_types=1);

/**
 * Tree Traversal Algorithms
 *
 * Demonstrates all 4 tree traversal methods:
 * - Inorder (Left → Root → Right)
 * - Preorder (Root → Left → Right)
 * - Postorder (Left → Right → Root)
 * - Level Order (Breadth-First)
 */

require_once '01-tree-node-basics.php';

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

echo "=== Tree Traversal Algorithms ===\n\n";

echo "Tree Structure:\n";
echo "       {$root->value}\n";
echo "      / \\\n";
echo "     {$root->left->value}   {$root->right->value}\n";
echo "    / \\   \\\n";
echo "   {$root->left->left->value}   {$root->left->right->value}   {$root->right->right->value}\n\n";

// 1. Inorder Traversal (Left → Root → Right)
// Produces sorted output for BST
function inorderTraversal(?TreeNode $node): array
{
    if ($node === null) {
        return [];
    }

    return array_merge(
        inorderTraversal($node->left),
        [$node->value],
        inorderTraversal($node->right)
    );
}

// 2. Preorder Traversal (Root → Left → Right)
// Useful for copying trees
function preorderTraversal(?TreeNode $node): array
{
    if ($node === null) {
        return [];
    }

    return array_merge(
        [$node->value],
        preorderTraversal($node->left),
        preorderTraversal($node->right)
    );
}

// 3. Postorder Traversal (Left → Right → Root)
// Useful for deleting trees
function postorderTraversal(?TreeNode $node): array
{
    if ($node === null) {
        return [];
    }

    return array_merge(
        postorderTraversal($node->left),
        postorderTraversal($node->right),
        [$node->value]
    );
}

// 4. Level Order Traversal (Breadth-First)
// Visits nodes level by level
function levelOrderTraversal(?TreeNode $root): array
{
    if ($root === null) {
        return [];
    }

    $result = [];
    $queue = [$root];

    while (!empty($queue)) {
        $node = array_shift($queue);
        $result[] = $node->value;

        if ($node->left !== null) {
            $queue[] = $node->left;
        }
        if ($node->right !== null) {
            $queue[] = $node->right;
        }
    }

    return $result;
}

// Level order with levels separated
function levelOrderByLevel(?TreeNode $root): array
{
    if ($root === null) {
        return [];
    }

    $result = [];
    $queue = [$root];

    while (!empty($queue)) {
        $levelSize = count($queue);
        $currentLevel = [];

        for ($i = 0; $i < $levelSize; $i++) {
            $node = array_shift($queue);
            $currentLevel[] = $node->value;

            if ($node->left !== null) {
                $queue[] = $node->left;
            }
            if ($node->right !== null) {
                $queue[] = $node->right;
            }
        }

        $result[] = $currentLevel;
    }

    return $result;
}

echo "Traversal Results:\n";
echo "Inorder (Left → Root → Right):   " . json_encode(inorderTraversal($root)) . "\n";
echo "  → Gives sorted sequence for BST!\n\n";

echo "Preorder (Root → Left → Right):  " . json_encode(preorderTraversal($root)) . "\n";
echo "  → Root visited first\n\n";

echo "Postorder (Left → Right → Root): " . json_encode(postorderTraversal($root)) . "\n";
echo "  → Root visited last\n\n";

echo "Level Order (BFS):                " . json_encode(levelOrderTraversal($root)) . "\n";
echo "  → Visits level by level\n\n";

echo "Level Order by Level:\n";
$levels = levelOrderByLevel($root);
foreach ($levels as $depth => $level) {
    echo "  Level $depth: " . json_encode($level) . "\n";
}

echo "\n✅ Complete! All 4 traversal methods demonstrated.\n";
