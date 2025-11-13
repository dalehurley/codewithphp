<?php

declare(strict_types=1);

/**
 * Tree Validation
 *
 * Demonstrates:
 * - Checking if a tree is a valid BST
 * - Checking if a tree is balanced
 * - Checking if a tree is symmetric
 */

require_once '01-tree-node-basics.php';

echo "=== Tree Validation ===\n\n";

// Test 1: Valid BST
echo "Test 1: Valid BST\n";
$validBST = new TreeNode(5);
$validBST->left = new TreeNode(3);
$validBST->right = new TreeNode(7);
$validBST->left->left = new TreeNode(2);
$validBST->left->right = new TreeNode(4);

function isValidBST(?TreeNode $node, $min = null, $max = null): bool
{
    if ($node === null) {
        return true;
    }

    if (($min !== null && $node->value <= $min) ||
        ($max !== null && $node->value >= $max)) {
        return false;
    }

    return isValidBST($node->left, $min, $node->value) &&
           isValidBST($node->right, $node->value, $max);
}

echo "  Tree: 5 → (3, 7) → (2, 4, null, null)\n";
echo "  Is valid BST: " . (isValidBST($validBST) ? "✓ Yes" : "✗ No") . "\n\n";

// Test 2: Invalid BST
echo "Test 2: Invalid BST\n";
$invalidBST = new TreeNode(5);
$invalidBST->left = new TreeNode(3);
$invalidBST->right = new TreeNode(7);
$invalidBST->left->left = new TreeNode(6); // INVALID! 6 > 5 but in left subtree

echo "  Tree: 5 → (3, 7) → (6, null, null, null)\n";
echo "  Is valid BST: " . (isValidBST($invalidBST) ? "✓ Yes" : "✗ No") . "\n";
echo "  (6 is greater than root 5, shouldn't be in left subtree)\n\n";

// Test 3: Balanced tree
echo "Test 3: Balanced Tree\n";
$balancedTree = new TreeNode(5);
$balancedTree->left = new TreeNode(3);
$balancedTree->right = new TreeNode(7);
$balancedTree->left->left = new TreeNode(2);
$balancedTree->left->right = new TreeNode(4);

function isBalanced(?TreeNode $node): bool
{
    return checkBalance($node) !== -1;
}

function checkBalance(?TreeNode $node): int
{
    if ($node === null) {
        return 0;
    }

    $leftHeight = checkBalance($node->left);
    if ($leftHeight === -1) return -1;

    $rightHeight = checkBalance($node->right);
    if ($rightHeight === -1) return -1;

    if (abs($leftHeight - $rightHeight) > 1) {
        return -1;
    }

    return 1 + max($leftHeight, $rightHeight);
}

echo "  Tree: 5 → (3, 7) → (2, 4, null, null)\n";
echo "  Is balanced: " . (isBalanced($balancedTree) ? "✓ Yes" : "✗ No") . "\n\n";

// Test 4: Unbalanced tree
echo "Test 4: Unbalanced Tree (skewed)\n";
$unbalancedTree = new TreeNode(1);
$unbalancedTree->right = new TreeNode(2);
$unbalancedTree->right->right = new TreeNode(3);
$unbalancedTree->right->right->right = new TreeNode(4);

echo "  Tree: 1 → (null, 2) → (null, 3) → (null, 4)\n";
echo "  Is balanced: " . (isBalanced($unbalancedTree) ? "✓ Yes" : "✗ No") . "\n";
echo "  (Height difference too large)\n\n";

// Test 5: Symmetric tree
echo "Test 5: Symmetric Tree\n";
$symmetricTree = new TreeNode(1);
$symmetricTree->left = new TreeNode(2);
$symmetricTree->right = new TreeNode(2);
$symmetricTree->left->left = new TreeNode(3);
$symmetricTree->left->right = new TreeNode(4);
$symmetricTree->right->left = new TreeNode(4);
$symmetricTree->right->right = new TreeNode(3);

function isSymmetric(?TreeNode $root): bool
{
    if ($root === null) {
        return true;
    }
    return isMirror($root->left, $root->right);
}

function isMirror(?TreeNode $left, ?TreeNode $right): bool
{
    if ($left === null && $right === null) {
        return true;
    }
    if ($left === null || $right === null) {
        return false;
    }
    return ($left->value === $right->value) &&
           isMirror($left->left, $right->right) &&
           isMirror($left->right, $right->left);
}

echo "  Tree:     1\n";
echo "           / \\\n";
echo "          2   2\n";
echo "         / \\ / \\\n";
echo "        3  4 4  3\n";
echo "  Is symmetric: " . (isSymmetric($symmetricTree) ? "✓ Yes" : "✗ No") . "\n\n";

// Test 6: Complete binary tree
echo "Test 6: Complete Binary Tree Check\n";
$completeTree = new TreeNode(1);
$completeTree->left = new TreeNode(2);
$completeTree->right = new TreeNode(3);
$completeTree->left->left = new TreeNode(4);
$completeTree->left->right = new TreeNode(5);

function isComplete(?TreeNode $root): bool
{
    if ($root === null) {
        return true;
    }

    $queue = [$root];
    $encounteredNull = false;

    while (!empty($queue)) {
        $node = array_shift($queue);

        if ($node === null) {
            $encounteredNull = true;
        } else {
            if ($encounteredNull) {
                return false; // Found a node after null
            }
            $queue[] = $node->left;
            $queue[] = $node->right;
        }
    }

    return true;
}

echo "  Tree: 1 → (2, 3) → (4, 5, null, null)\n";
echo "  Is complete: " . (isComplete($completeTree) ? "✓ Yes" : "✗ No") . "\n";

echo "\n✅ Complete! Tree validation techniques demonstrated.\n";
