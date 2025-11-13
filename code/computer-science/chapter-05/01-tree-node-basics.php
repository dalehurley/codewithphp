<?php

declare(strict_types=1);

/**
 * Tree Node Basics
 *
 * Demonstrates:
 * - TreeNode class implementation
 * - Building a simple binary tree
 * - Accessing tree nodes and values
 */

class TreeNode
{
    public function __construct(
        public mixed $value,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

echo "=== Tree Node Basics ===\n\n";

// Build a simple tree:
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

echo "Tree Structure:\n";
echo "       {$root->value}\n";
echo "      / \\\n";
echo "     {$root->left->value}   {$root->right->value}\n";
echo "    / \\   \\\n";
echo "   {$root->left->left->value}   {$root->left->right->value}   {$root->right->right->value}\n\n";

// Calculate tree height
function getHeight(?TreeNode $node): int
{
    if ($node === null) {
        return -1;
    }

    return 1 + max(
        getHeight($node->left),
        getHeight($node->right)
    );
}

// Count total nodes
function countNodes(?TreeNode $node): int
{
    if ($node === null) {
        return 0;
    }

    return 1 + countNodes($node->left) + countNodes($node->right);
}

// Count leaf nodes
function countLeaves(?TreeNode $node): int
{
    if ($node === null) {
        return 0;
    }

    if ($node->left === null && $node->right === null) {
        return 1;
    }

    return countLeaves($node->left) + countLeaves($node->right);
}

echo "Tree Properties:\n";
echo "Height: " . getHeight($root) . "\n";
echo "Total nodes: " . countNodes($root) . "\n";
echo "Leaf nodes: " . countLeaves($root) . "\n\n";

// Find a value in the tree
function findValue(?TreeNode $node, mixed $target): bool
{
    if ($node === null) {
        return false;
    }

    if ($node->value === $target) {
        return true;
    }

    return findValue($node->left, $target) || findValue($node->right, $target);
}

$searchValues = [5, 4, 10, 2];
echo "Search Results:\n";
foreach ($searchValues as $val) {
    $found = findValue($root, $val) ? "found" : "not found";
    echo "Value $val: $found\n";
}

echo "\n✅ Complete! Binary tree basics demonstrated.\n";
