<?php

declare(strict_types=1);

/**
 * Tree Visualization
 *
 * Demonstrates:
 * - Pretty printing trees in console
 * - Multiple visualization formats
 * - Tree structure display
 */

require_once '01-tree-node-basics.php';
require_once '02-tree-traversals.php';

echo "=== Tree Visualization ===\n\n";

class TreeVisualizer
{
    /**
     * Print tree in vertical format with connecting lines
     */
    public function printTree(?TreeNode $node, string $prefix = "", bool $isLeft = true): void
    {
        if ($node === null) {
            return;
        }

        echo $prefix . ($isLeft ? "├── " : "└── ") . $node->value . "\n";

        $newPrefix = $prefix . ($isLeft ? "│   " : "    ");

        if ($node->left !== null || $node->right !== null) {
            if ($node->left !== null) {
                $this->printTree($node->left, $newPrefix, true);
            } else {
                echo $newPrefix . "├── (null)\n";
            }

            if ($node->right !== null) {
                $this->printTree($node->right, $newPrefix, false);
            } else {
                echo $newPrefix . "└── (null)\n";
            }
        }
    }

    /**
     * Print tree horizontally (rotated 90°)
     */
    public function printHorizontal(?TreeNode $node, int $space = 0, int $gap = 5): void
    {
        if ($node === null) {
            return;
        }

        $space += $gap;

        $this->printHorizontal($node->right, $space, $gap);

        echo str_repeat(' ', $space - $gap) . $node->value . "\n";

        $this->printHorizontal($node->left, $space, $gap);
    }

    /**
     * Print tree level by level
     */
    public function printByLevel(?TreeNode $root): void
    {
        if ($root === null) {
            echo "(empty tree)\n";
            return;
        }

        $queue = [$root];
        $level = 0;

        while (!empty($queue)) {
            $levelSize = count($queue);
            $levelValues = [];

            for ($i = 0; $i < $levelSize; $i++) {
                $node = array_shift($queue);
                $levelValues[] = $node->value;

                if ($node->left !== null) {
                    $queue[] = $node->left;
                }
                if ($node->right !== null) {
                    $queue[] = $node->right;
                }
            }

            echo "Level $level: " . implode(', ', $levelValues) . "\n";
            $level++;
        }
    }

    /**
     * Generate ASCII art representation
     */
    public function printASCII(?TreeNode $root): void
    {
        if ($root === null) {
            echo "(empty tree)\n";
            return;
        }

        $lines = $this->buildASCII($root);
        foreach ($lines as $line) {
            echo $line . "\n";
        }
    }

    private function buildASCII(?TreeNode $node): array
    {
        if ($node === null) {
            return [];
        }

        $value = (string)$node->value;
        $leftLines = $this->buildASCII($node->left);
        $rightLines = $this->buildASCII($node->right);

        // Simple version for demonstration
        if (empty($leftLines) && empty($rightLines)) {
            return [$value];
        }

        $result = [];
        $result[] = str_repeat(' ', max(0, count($leftLines) * 2)) . $value;

        if (!empty($leftLines) || !empty($rightLines)) {
            $branches = str_repeat(' ', max(0, count($leftLines) * 2 - 1)) . '/';
            if (!empty($rightLines)) {
                $branches .= ' \\';
            }
            $result[] = $branches;

            // Add child lines
            $maxLines = max(count($leftLines), count($rightLines));
            for ($i = 0; $i < $maxLines; $i++) {
                $left = $i < count($leftLines) ? $leftLines[$i] : '';
                $right = $i < count($rightLines) ? $rightLines[$i] : '';
                $result[] = $left . '  ' . $right;
            }
        }

        return $result;
    }
}

// Build sample tree:
//        10
//       /  \
//      5    15
//     / \   / \
//    3   7 12  17

$root = new TreeNode(10);
$root->left = new TreeNode(5);
$root->right = new TreeNode(15);
$root->left->left = new TreeNode(3);
$root->left->right = new TreeNode(7);
$root->right->left = new TreeNode(12);
$root->right->right = new TreeNode(17);

$viz = new TreeVisualizer();

echo "1. Vertical Format (with connectors)\n";
echo "=====================================\n";
$viz->printTree($root);
echo "\n";

echo "2. Horizontal Format (rotated 90°)\n";
echo "====================================\n";
$viz->printHorizontal($root);
echo "\n";

echo "3. Level-by-Level\n";
echo "==================\n";
$viz->printByLevel($root);
echo "\n";

echo "4. ASCII Art (simple)\n";
echo "======================\n";
$viz->printASCII($root);
echo "\n";

// Build larger tree for better visualization
echo "5. Larger Tree Example\n";
echo "=======================\n";

$largeTree = new TreeNode(50);
$largeTree->left = new TreeNode(30);
$largeTree->right = new TreeNode(70);
$largeTree->left->left = new TreeNode(20);
$largeTree->left->right = new TreeNode(40);
$largeTree->right->left = new TreeNode(60);
$largeTree->right->right = new TreeNode(80);
$largeTree->left->left->left = new TreeNode(10);
$largeTree->left->right->left = new TreeNode(35);
$largeTree->right->left->right = new TreeNode(65);
$largeTree->right->right->right = new TreeNode(90);

$viz->printTree($largeTree);
echo "\n";

echo "Same tree (horizontal):\n";
$viz->printHorizontal($largeTree);

echo "\n✅ Complete! Tree visualization techniques demonstrated.\n";
