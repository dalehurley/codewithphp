<?php

declare(strict_types=1);

/**
 * Tree Traversal Algorithms
 *
 * Complete implementation of all major tree traversal algorithms:
 * - Depth-First: In-order, Pre-order, Post-order (recursive & iterative)
 * - Breadth-First: Level-order
 * - Morris Traversal (space-optimized)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

require_once __DIR__ . '/../chapter-18/01-binary-search-tree.php';

/**
 * Tree traversal algorithms collection
 */
class TreeTraversal
{
    /**
     * In-order traversal (Left-Root-Right) - Recursive
     * For BST, gives sorted output
     *
     * @param TreeNode|null $node The root node
     * @return array The traversal result
     */
    public static function inOrderRecursive(?TreeNode $node): array
    {
        $result = [];
        self::inOrderHelper($node, $result);
        return $result;
    }

    private static function inOrderHelper(?TreeNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        self::inOrderHelper($node->left, $result);
        $result[] = $node->data;
        self::inOrderHelper($node->right, $result);
    }

    /**
     * In-order traversal - Iterative
     * Uses explicit stack
     *
     * @param TreeNode|null $root The root node
     * @return array The traversal result
     */
    public static function inOrderIterative(?TreeNode $root): array
    {
        $result = [];
        $stack = [];
        $current = $root;

        while ($current !== null || !empty($stack)) {
            // Go to leftmost node
            while ($current !== null) {
                $stack[] = $current;
                $current = $current->left;
            }

            // Process node
            $current = array_pop($stack);
            $result[] = $current->data;

            // Move to right subtree
            $current = $current->right;
        }

        return $result;
    }

    /**
     * Pre-order traversal (Root-Left-Right) - Recursive
     * Used for creating tree copies
     *
     * @param TreeNode|null $node The root node
     * @return array The traversal result
     */
    public static function preOrderRecursive(?TreeNode $node): array
    {
        $result = [];
        self::preOrderHelper($node, $result);
        return $result;
    }

    private static function preOrderHelper(?TreeNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $result[] = $node->data;
        self::preOrderHelper($node->left, $result);
        self::preOrderHelper($node->right, $result);
    }

    /**
     * Pre-order traversal - Iterative
     *
     * @param TreeNode|null $root The root node
     * @return array The traversal result
     */
    public static function preOrderIterative(?TreeNode $root): array
    {
        if ($root === null) {
            return [];
        }

        $result = [];
        $stack = [$root];

        while (!empty($stack)) {
            $node = array_pop($stack);
            $result[] = $node->data;

            // Push right first (LIFO), so left is processed first
            if ($node->right !== null) {
                $stack[] = $node->right;
            }
            if ($node->left !== null) {
                $stack[] = $node->left;
            }
        }

        return $result;
    }

    /**
     * Post-order traversal (Left-Right-Root) - Recursive
     * Used for deleting trees
     *
     * @param TreeNode|null $node The root node
     * @return array The traversal result
     */
    public static function postOrderRecursive(?TreeNode $node): array
    {
        $result = [];
        self::postOrderHelper($node, $result);
        return $result;
    }

    private static function postOrderHelper(?TreeNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        self::postOrderHelper($node->left, $result);
        self::postOrderHelper($node->right, $result);
        $result[] = $node->data;
    }

    /**
     * Post-order traversal - Iterative (two-stack approach)
     *
     * @param TreeNode|null $root The root node
     * @return array The traversal result
     */
    public static function postOrderIterative(?TreeNode $root): array
    {
        if ($root === null) {
            return [];
        }

        $result = [];
        $stack1 = [$root];
        $stack2 = [];

        // Fill second stack with reverse post-order
        while (!empty($stack1)) {
            $node = array_pop($stack1);
            $stack2[] = $node;

            if ($node->left !== null) {
                $stack1[] = $node->left;
            }
            if ($node->right !== null) {
                $stack1[] = $node->right;
            }
        }

        // Pop from second stack to get post-order
        while (!empty($stack2)) {
            $result[] = array_pop($stack2)->data;
        }

        return $result;
    }

    /**
     * Level-order traversal (Breadth-First)
     * Visits nodes level by level
     *
     * @param TreeNode|null $root The root node
     * @return array The traversal result
     */
    public static function levelOrder(?TreeNode $root): array
    {
        if ($root === null) {
            return [];
        }

        $result = [];
        $queue = [$root];

        while (!empty($queue)) {
            $node = array_shift($queue);
            $result[] = $node->data;

            if ($node->left !== null) {
                $queue[] = $node->left;
            }
            if ($node->right !== null) {
                $queue[] = $node->right;
            }
        }

        return $result;
    }

    /**
     * Level-order traversal grouped by level
     *
     * @param TreeNode|null $root The root node
     * @return array Array of levels, each containing node values
     */
    public static function levelOrderGrouped(?TreeNode $root): array
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
                $currentLevel[] = $node->data;

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

    /**
     * Zigzag level-order traversal
     * Alternates left-to-right and right-to-left at each level
     *
     * @param TreeNode|null $root The root node
     * @return array Array of levels in zigzag order
     */
    public static function zigzagLevelOrder(?TreeNode $root): array
    {
        if ($root === null) {
            return [];
        }

        $result = [];
        $queue = [$root];
        $leftToRight = true;

        while (!empty($queue)) {
            $levelSize = count($queue);
            $currentLevel = [];

            for ($i = 0; $i < $levelSize; $i++) {
                $node = array_shift($queue);

                if ($leftToRight) {
                    $currentLevel[] = $node->data;
                } else {
                    array_unshift($currentLevel, $node->data);
                }

                if ($node->left !== null) {
                    $queue[] = $node->left;
                }
                if ($node->right !== null) {
                    $queue[] = $node->right;
                }
            }

            $result[] = $currentLevel;
            $leftToRight = !$leftToRight;
        }

        return $result;
    }

    /**
     * Morris in-order traversal (O(1) space)
     * Achieves O(1) space by threading the tree temporarily
     *
     * @param TreeNode|null $root The root node
     * @return array The traversal result
     */
    public static function morrisInOrder(?TreeNode $root): array
    {
        $result = [];
        $current = $root;

        while ($current !== null) {
            if ($current->left === null) {
                // No left child, visit and go right
                $result[] = $current->data;
                $current = $current->right;
            } else {
                // Find inorder predecessor
                $predecessor = $current->left;
                while ($predecessor->right !== null && $predecessor->right !== $current) {
                    $predecessor = $predecessor->right;
                }

                if ($predecessor->right === null) {
                    // Create thread
                    $predecessor->right = $current;
                    $current = $current->left;
                } else {
                    // Remove thread
                    $predecessor->right = null;
                    $result[] = $current->data;
                    $current = $current->right;
                }
            }
        }

        return $result;
    }

    /**
     * Find all root-to-leaf paths
     *
     * @param TreeNode|null $root The root node
     * @return array Array of paths
     */
    public static function findAllPaths(?TreeNode $root): array
    {
        $paths = [];
        self::findPathsHelper($root, [], $paths);
        return $paths;
    }

    private static function findPathsHelper(?TreeNode $node, array $current, array &$paths): void
    {
        if ($node === null) {
            return;
        }

        $current[] = $node->data;

        // Leaf node
        if ($node->left === null && $node->right === null) {
            $paths[] = $current;
            return;
        }

        self::findPathsHelper($node->left, $current, $paths);
        self::findPathsHelper($node->right, $current, $paths);
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Tree Traversal Algorithms Demo ===\n\n";

    // Build sample tree:
    //        4
    //       / \
    //      2   6
    //     / \ / \
    //    1  3 5  7
    $tree = new TreeNode(4,
        new TreeNode(2,
            new TreeNode(1),
            new TreeNode(3)
        ),
        new TreeNode(6,
            new TreeNode(5),
            new TreeNode(7)
        )
    );

    echo "Tree structure:\n";
    echo "       4\n";
    echo "      / \\\n";
    echo "     2   6\n";
    echo "    / \\ / \\\n";
    echo "   1  3 5  7\n\n";

    // Depth-First Traversals
    echo "=== Depth-First Traversals ===\n";

    echo "In-order (Recursive):  " . implode(', ', TreeTraversal::inOrderRecursive($tree)) . "\n";
    echo "In-order (Iterative):  " . implode(', ', TreeTraversal::inOrderIterative($tree)) . "\n";
    echo "In-order (Morris):     " . implode(', ', TreeTraversal::morrisInOrder($tree)) . "\n\n";

    echo "Pre-order (Recursive): " . implode(', ', TreeTraversal::preOrderRecursive($tree)) . "\n";
    echo "Pre-order (Iterative): " . implode(', ', TreeTraversal::preOrderIterative($tree)) . "\n\n";

    echo "Post-order (Recursive):" . implode(', ', TreeTraversal::postOrderRecursive($tree)) . "\n";
    echo "Post-order (Iterative):" . implode(', ', TreeTraversal::postOrderIterative($tree)) . "\n\n";

    // Breadth-First Traversals
    echo "=== Breadth-First Traversals ===\n";

    echo "Level-order: " . implode(', ', TreeTraversal::levelOrder($tree)) . "\n\n";

    echo "Level-order (grouped by level):\n";
    $levels = TreeTraversal::levelOrderGrouped($tree);
    foreach ($levels as $i => $level) {
        echo "  Level $i: " . implode(', ', $level) . "\n";
    }

    echo "\nZigzag level-order:\n";
    $zigzag = TreeTraversal::zigzagLevelOrder($tree);
    foreach ($zigzag as $i => $level) {
        $direction = $i % 2 === 0 ? 'L→R' : 'R→L';
        echo "  Level $i ($direction): " . implode(', ', $level) . "\n";
    }

    // Path finding
    echo "\n=== All Root-to-Leaf Paths ===\n";
    $paths = TreeTraversal::findAllPaths($tree);
    foreach ($paths as $i => $path) {
        echo "Path " . ($i + 1) . ": " . implode(' → ', $path) . "\n";
    }

    // Comparison table
    echo "\n=== Complexity Comparison ===\n";
    echo "Traversal          | Time  | Space   | Notes\n";
    echo "-------------------|-------|---------|---------------------------\n";
    echo "In-order (Rec)     | O(n)  | O(h)    | h = height, call stack\n";
    echo "In-order (Iter)    | O(n)  | O(h)    | Explicit stack\n";
    echo "In-order (Morris)  | O(n)  | O(1)    | Threads tree temporarily\n";
    echo "Pre-order          | O(n)  | O(h)    | For tree copying\n";
    echo "Post-order         | O(n)  | O(h)    | For tree deletion\n";
    echo "Level-order        | O(n)  | O(w)    | w = max width\n";
}
