<?php
declare(strict_types=1);

echo "=== Tree Recursion ===\n\n";

class TreeNode {
    public function __construct(
        public mixed $value,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

function sumTree(?TreeNode $node): int {
    if ($node === null) return 0;
    return $node->value + sumTree($node->left) + sumTree($node->right);
}

function treeHeight(?TreeNode $node): int {
    if ($node === null) return -1;
    return 1 + max(treeHeight($node->left), treeHeight($node->right));
}

function countNodes(?TreeNode $node): int {
    if ($node === null) return 0;
    return 1 + countNodes($node->left) + countNodes($node->right);
}

// Build sample tree
$root = new TreeNode(1,
    new TreeNode(2, new TreeNode(4), new TreeNode(5)),
    new TreeNode(3)
);

echo "Sum of tree: " . sumTree($root) . "\n";
echo "Height: " . treeHeight($root) . "\n";
echo "Node count: " . countNodes($root) . "\n";

echo "\n✅ Complete!\n";
