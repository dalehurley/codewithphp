---
title: "Tree Traversal Algorithms"
description: "Master the fundamental tree traversal algorithms including in-order, pre-order, post-order, and level-order traversal with both recursive and iterative implementations"
series: "php-algorithms"
chapter: 19
order: 19
difficulty: "intermediate"
prerequisites: ["Trees & Binary Search Trees"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 19</span>
</div>

# Tree Traversal Algorithms <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## What You'll Learn

- Master in-order, pre-order, and post-order depth-first traversals
- Implement level-order (breadth-first) traversal with queues
- Build both recursive and iterative versions of each traversal
- Apply traversals to solve practical tree problems
- Understand when to use each traversal strategy

**Estimated Time**: ~50 minutes

## Prerequisites

Before starting this chapter, you should have:

- ✓ Strong understanding of binary search trees (Chapter 18)
- ✓ Familiarity with recursion (Chapter 3)
- ✓ Knowledge of stacks and queues (Chapter 17)
- ✓ Completion of Chapters 15-18

Tree traversal is the process of visiting each node in a tree data structure exactly once in a systematic way. Think of it as taking different "tours" through your tree—each traversal strategy visits the same nodes but in a different order, making each one useful for different problems. Understanding traversal algorithms is fundamental to working with trees and forms the basis for many tree-based algorithms.

## Understanding Tree Traversal

Tree traversal algorithms differ from linear data structure traversal because trees are hierarchical. We need strategies to visit nodes in a specific order.

### Key Concepts

- **Visiting**: Processing a node (reading data, printing, modifying)
- **Traversing**: Moving through the tree systematically
- **Depth-First**: Explore as far as possible along each branch before backtracking
- **Breadth-First**: Explore all nodes at the current depth before moving deeper

## Depth-First Traversals

Depth-first traversals use a stack (either explicitly or via recursion) to explore branches deeply before backtracking.

### In-Order Traversal (Left-Root-Right)

In-order traversal visits the left subtree, then the root, then the right subtree. For BSTs, this produces sorted output.

**Order**: Left → Root → Right

```php
<?php

class TreeNode
{
    public function __construct(
        public mixed $data,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

class TreeTraversal
{
    // Recursive in-order traversal
    public function inOrderRecursive(?TreeNode $node, array &$result = []): array
    {
        if ($node === null) {
            return $result;
        }

        $this->inOrderRecursive($node->left, $result);  // Left
        $result[] = $node->data;                         // Root
        $this->inOrderRecursive($node->right, $result);  // Right

        return $result;
    }

    // Iterative in-order traversal using explicit stack
    public function inOrderIterative(?TreeNode $root): array
    {
        $result = [];
        $stack = [];
        $current = $root;

        while ($current !== null || !empty($stack)) {
            // Go to the leftmost node
            while ($current !== null) {
                $stack[] = $current;
                $current = $current->left;
            }

            // Current is null, so we backtrack
            $current = array_pop($stack);
            $result[] = $current->data;

            // Visit the right subtree
            $current = $current->right;
        }

        return $result;
    }
}

// Example usage
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

$traversal = new TreeTraversal();
print_r($traversal->inOrderRecursive($tree));    // [1, 2, 3, 4, 5, 6, 7]
print_r($traversal->inOrderIterative($tree));    // [1, 2, 3, 4, 5, 6, 7]
```

### Pre-Order Traversal (Root-Left-Right)

Pre-order traversal visits the root first, then the left subtree, then the right subtree. Useful for creating a copy of the tree or prefix expression.

**Order**: Root → Left → Right

```php
<?php

class TreeTraversal
{
    // Recursive pre-order traversal
    public function preOrderRecursive(?TreeNode $node, array &$result = []): array
    {
        if ($node === null) {
            return $result;
        }

        $result[] = $node->data;                          // Root
        $this->preOrderRecursive($node->left, $result);   // Left
        $this->preOrderRecursive($node->right, $result);  // Right

        return $result;
    }

    // Iterative pre-order traversal
    public function preOrderIterative(?TreeNode $root): array
    {
        if ($root === null) {
            return [];
        }

        $result = [];
        $stack = [$root];

        while (!empty($stack)) {
            $node = array_pop($stack);
            $result[] = $node->data;

            // Push right first so left is processed first (LIFO)
            if ($node->right !== null) {
                $stack[] = $node->right;
            }
            if ($node->left !== null) {
                $stack[] = $node->left;
            }
        }

        return $result;
    }
}

// Example
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

$traversal = new TreeTraversal();
print_r($traversal->preOrderRecursive($tree));   // [4, 2, 1, 3, 6, 5, 7]
print_r($traversal->preOrderIterative($tree));   // [4, 2, 1, 3, 6, 5, 7]
```

### Post-Order Traversal (Left-Right-Root)

Post-order traversal visits the left subtree, then the right subtree, then the root. Useful for deleting a tree or postfix expression.

**Order**: Left → Right → Root

```php
<?php

class TreeTraversal
{
    // Recursive post-order traversal
    public function postOrderRecursive(?TreeNode $node, array &$result = []): array
    {
        if ($node === null) {
            return $result;
        }

        $this->postOrderRecursive($node->left, $result);   // Left
        $this->postOrderRecursive($node->right, $result);  // Right
        $result[] = $node->data;                           // Root

        return $result;
    }

    // Iterative post-order traversal (two-stack approach)
    public function postOrderIterative(?TreeNode $root): array
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

    // Iterative post-order traversal (single-stack approach)
    public function postOrderIterativeSingleStack(?TreeNode $root): array
    {
        if ($root === null) {
            return [];
        }

        $result = [];
        $stack = [];
        $lastVisited = null;
        $current = $root;

        while (!empty($stack) || $current !== null) {
            if ($current !== null) {
                $stack[] = $current;
                $current = $current->left;
            } else {
                $peekNode = end($stack);

                // If right child exists and traversing from left, go right
                if ($peekNode->right !== null && $lastVisited !== $peekNode->right) {
                    $current = $peekNode->right;
                } else {
                    // Visit the node
                    $result[] = $peekNode->data;
                    $lastVisited = array_pop($stack);
                }
            }
        }

        return $result;
    }
}

// Example
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

$traversal = new TreeTraversal();
print_r($traversal->postOrderRecursive($tree));  // [1, 3, 2, 5, 7, 6, 4]
print_r($traversal->postOrderIterative($tree));  // [1, 3, 2, 5, 7, 6, 4]
```

## Breadth-First Traversal

Breadth-first (level-order) traversal visits nodes level by level, from left to right.

### Level-Order Traversal

```php
<?php

class TreeTraversal
{
    // Level-order traversal using queue
    public function levelOrder(?TreeNode $root): array
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

    // Level-order traversal grouped by level
    public function levelOrderGrouped(?TreeNode $root): array
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

    // Zigzag level-order traversal (alternate left-to-right and right-to-left)
    public function zigzagLevelOrder(?TreeNode $root): array
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
}

// Example
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

$traversal = new TreeTraversal();
print_r($traversal->levelOrder($tree));
// [4, 2, 6, 1, 3, 5, 7]

print_r($traversal->levelOrderGrouped($tree));
// [[4], [2, 6], [1, 3, 5, 7]]

print_r($traversal->zigzagLevelOrder($tree));
// [[4], [6, 2], [1, 3, 5, 7]]
```

## Morris Traversal (Space-Optimized)

Morris traversal achieves O(1) space complexity by using threaded binary trees (temporarily modifying tree structure).

```php
<?php

class TreeTraversal
{
    // Morris in-order traversal (O(1) space)
    public function morrisInOrder(?TreeNode $root): array
    {
        $result = [];
        $current = $root;

        while ($current !== null) {
            if ($current->left === null) {
                // No left child, visit current and go right
                $result[] = $current->data;
                $current = $current->right;
            } else {
                // Find the inorder predecessor
                $predecessor = $current->left;
                while ($predecessor->right !== null && $predecessor->right !== $current) {
                    $predecessor = $predecessor->right;
                }

                if ($predecessor->right === null) {
                    // Create thread (temporary link)
                    $predecessor->right = $current;
                    $current = $current->left;
                } else {
                    // Thread already exists, remove it
                    $predecessor->right = null;
                    $result[] = $current->data;
                    $current = $current->right;
                }
            }
        }

        return $result;
    }

    // Morris pre-order traversal (O(1) space)
    public function morrisPreOrder(?TreeNode $root): array
    {
        $result = [];
        $current = $root;

        while ($current !== null) {
            if ($current->left === null) {
                $result[] = $current->data;
                $current = $current->right;
            } else {
                $predecessor = $current->left;
                while ($predecessor->right !== null && $predecessor->right !== $current) {
                    $predecessor = $predecessor->right;
                }

                if ($predecessor->right === null) {
                    $result[] = $current->data;  // Visit before going left
                    $predecessor->right = $current;
                    $current = $current->left;
                } else {
                    $predecessor->right = null;
                    $current = $current->right;
                }
            }
        }

        return $result;
    }
}
```

## Traversal Applications

### 1. Expression Tree Evaluation

```php
<?php

class ExpressionNode extends TreeNode
{
    public function __construct(
        public mixed $data,
        public ?ExpressionNode $left = null,
        public ?ExpressionNode $right = null
    ) {}
}

class ExpressionTreeEvaluator
{
    // Evaluate expression tree using post-order traversal
    public function evaluate(?ExpressionNode $node): float
    {
        if ($node === null) {
            return 0;
        }

        // Leaf node (operand)
        if ($node->left === null && $node->right === null) {
            return (float)$node->data;
        }

        // Evaluate left and right subtrees
        $leftValue = $this->evaluate($node->left);
        $rightValue = $this->evaluate($node->right);

        // Apply operator
        return match($node->data) {
            '+' => $leftValue + $rightValue,
            '-' => $leftValue - $rightValue,
            '*' => $leftValue * $rightValue,
            '/' => $leftValue / $rightValue,
            default => throw new InvalidArgumentException("Unknown operator: {$node->data}")
        };
    }

    // Build infix expression using in-order traversal
    public function toInfix(?ExpressionNode $node): string
    {
        if ($node === null) {
            return '';
        }

        if ($node->left === null && $node->right === null) {
            return (string)$node->data;
        }

        $left = $this->toInfix($node->left);
        $right = $this->toInfix($node->right);

        return "({$left} {$node->data} {$right})";
    }

    // Build prefix expression using pre-order traversal
    public function toPrefix(?ExpressionNode $node): string
    {
        if ($node === null) {
            return '';
        }

        if ($node->left === null && $node->right === null) {
            return (string)$node->data;
        }

        $left = $this->toPrefix($node->left);
        $right = $this->toPrefix($node->right);

        return "{$node->data} {$left} {$right}";
    }

    // Build postfix expression using post-order traversal
    public function toPostfix(?ExpressionNode $node): string
    {
        if ($node === null) {
            return '';
        }

        if ($node->left === null && $node->right === null) {
            return (string)$node->data;
        }

        $left = $this->toPostfix($node->left);
        $right = $this->toPostfix($node->right);

        return "{$left} {$right} {$node->data}";
    }
}

// Example: Expression tree for ((3 + 5) * 2)
$tree = new ExpressionNode('*',
    new ExpressionNode('+',
        new ExpressionNode(3),
        new ExpressionNode(5)
    ),
    new ExpressionNode(2)
);

$evaluator = new ExpressionTreeEvaluator();
echo $evaluator->evaluate($tree);      // 16
echo $evaluator->toInfix($tree);       // ((3 + 5) * 2)
echo $evaluator->toPrefix($tree);      // * + 3 5 2
echo $evaluator->toPostfix($tree);     // 3 5 + 2 *
```

### 2. Tree Serialization and Deserialization

```php
<?php

class TreeSerializer
{
    private const NULL_MARKER = '#';

    // Serialize using pre-order traversal
    public function serialize(?TreeNode $root): string
    {
        $result = [];
        $this->serializeHelper($root, $result);
        return implode(',', $result);
    }

    private function serializeHelper(?TreeNode $node, array &$result): void
    {
        if ($node === null) {
            $result[] = self::NULL_MARKER;
            return;
        }

        $result[] = $node->data;
        $this->serializeHelper($node->left, $result);
        $this->serializeHelper($node->right, $result);
    }

    // Deserialize
    public function deserialize(string $data): ?TreeNode
    {
        $values = explode(',', $data);
        $index = 0;
        return $this->deserializeHelper($values, $index);
    }

    private function deserializeHelper(array $values, int &$index): ?TreeNode
    {
        if ($index >= count($values) || $values[$index] === self::NULL_MARKER) {
            $index++;
            return null;
        }

        $node = new TreeNode($values[$index++]);
        $node->left = $this->deserializeHelper($values, $index);
        $node->right = $this->deserializeHelper($values, $index);

        return $node;
    }

    // Serialize using level-order traversal
    public function serializeLevelOrder(?TreeNode $root): string
    {
        if ($root === null) {
            return '';
        }

        $result = [];
        $queue = [$root];

        while (!empty($queue)) {
            $node = array_shift($queue);

            if ($node === null) {
                $result[] = self::NULL_MARKER;
            } else {
                $result[] = $node->data;
                $queue[] = $node->left;
                $queue[] = $node->right;
            }
        }

        return implode(',', $result);
    }
}

// Example
$tree = new TreeNode(1,
    new TreeNode(2),
    new TreeNode(3,
        new TreeNode(4),
        new TreeNode(5)
    )
);

$serializer = new TreeSerializer();
$serialized = $serializer->serialize($tree);
echo $serialized . "\n";  // 1,2,#,#,3,4,#,#,5,#,#

$deserialized = $serializer->deserialize($serialized);
$reserialized = $serializer->serialize($deserialized);
echo $reserialized . "\n";  // 1,2,#,#,3,4,#,#,5,#,#
```

### 3. Path Finding

```php
<?php

class TreePathFinder
{
    // Find all root-to-leaf paths
    public function findAllPaths(?TreeNode $root): array
    {
        $paths = [];
        $this->findPathsHelper($root, [], $paths);
        return $paths;
    }

    private function findPathsHelper(?TreeNode $node, array $currentPath, array &$paths): void
    {
        if ($node === null) {
            return;
        }

        $currentPath[] = $node->data;

        // Leaf node
        if ($node->left === null && $node->right === null) {
            $paths[] = $currentPath;
            return;
        }

        $this->findPathsHelper($node->left, $currentPath, $paths);
        $this->findPathsHelper($node->right, $currentPath, $paths);
    }

    // Find path to a specific node
    public function findPathToNode(?TreeNode $root, mixed $target): ?array
    {
        $path = [];
        if ($this->findPathHelper($root, $target, $path)) {
            return $path;
        }
        return null;
    }

    private function findPathHelper(?TreeNode $node, mixed $target, array &$path): bool
    {
        if ($node === null) {
            return false;
        }

        $path[] = $node->data;

        if ($node->data === $target) {
            return true;
        }

        if ($this->findPathHelper($node->left, $target, $path) ||
            $this->findPathHelper($node->right, $target, $path)) {
            return true;
        }

        array_pop($path);  // Backtrack
        return false;
    }

    // Find paths with given sum
    public function findPathsWithSum(?TreeNode $root, int $targetSum): array
    {
        $paths = [];
        $this->findSumPathsHelper($root, $targetSum, [], $paths);
        return $paths;
    }

    private function findSumPathsHelper(
        ?TreeNode $node,
        int $targetSum,
        array $currentPath,
        array &$paths
    ): void {
        if ($node === null) {
            return;
        }

        $currentPath[] = $node->data;
        $currentSum = array_sum($currentPath);

        // Leaf node with target sum
        if ($node->left === null && $node->right === null && $currentSum === $targetSum) {
            $paths[] = $currentPath;
            return;
        }

        $this->findSumPathsHelper($node->left, $targetSum, $currentPath, $paths);
        $this->findSumPathsHelper($node->right, $targetSum, $currentPath, $paths);
    }
}

// Example
$tree = new TreeNode(5,
    new TreeNode(4,
        new TreeNode(11,
            new TreeNode(7),
            new TreeNode(2)
        )
    ),
    new TreeNode(8,
        new TreeNode(13),
        new TreeNode(4,
            new TreeNode(5),
            new TreeNode(1)
        )
    )
);

$pathFinder = new TreePathFinder();
print_r($pathFinder->findAllPaths($tree));
print_r($pathFinder->findPathToNode($tree, 7));
print_r($pathFinder->findPathsWithSum($tree, 22));  // [[5,4,11,2], [5,8,4,5]]
```

## Complexity Analysis

| Traversal Type | Time Complexity | Space Complexity | Notes |
|---------------|-----------------|------------------|-------|
| In-Order (Recursive) | O(n) | O(h) | h = height, worst case O(n) for skewed tree |
| Pre-Order (Recursive) | O(n) | O(h) | Same as above |
| Post-Order (Recursive) | O(n) | O(h) | Same as above |
| Level-Order | O(n) | O(w) | w = max width, worst case O(n) |
| In-Order (Iterative) | O(n) | O(h) | Explicit stack |
| Morris Traversal | O(n) | O(1) | Temporarily modifies tree |

Where:
- **n** = number of nodes
- **h** = height of tree
- **w** = maximum width of tree

## Practical Applications

### 1. File System Directory Listing

```php
<?php

class FileNode
{
    public function __construct(
        public string $name,
        public bool $isDirectory,
        public array $children = []
    ) {}
}

class FileSystemTraversal
{
    // List all files (DFS pre-order)
    public function listAllFiles(FileNode $root, string $prefix = ''): array
    {
        $files = [];

        $currentPath = $prefix . $root->name;

        if (!$root->isDirectory) {
            $files[] = $currentPath;
        } else {
            foreach ($root->children as $child) {
                $files = array_merge(
                    $files,
                    $this->listAllFiles($child, $currentPath . '/')
                );
            }
        }

        return $files;
    }

    // List by directory level (BFS)
    public function listByLevel(FileNode $root): array
    {
        $levels = [];
        $queue = [['node' => $root, 'level' => 0]];

        while (!empty($queue)) {
            $item = array_shift($queue);
            $node = $item['node'];
            $level = $item['level'];

            if (!isset($levels[$level])) {
                $levels[$level] = [];
            }

            $levels[$level][] = $node->name;

            if ($node->isDirectory) {
                foreach ($node->children as $child) {
                    $queue[] = ['node' => $child, 'level' => $level + 1];
                }
            }
        }

        return $levels;
    }
}
```

### 2. HTML DOM Traversal

```php
<?php

class DOMNode
{
    public function __construct(
        public string $tag,
        public array $attributes = [],
        public array $children = [],
        public ?string $text = null
    ) {}
}

class DOMTraversal
{
    // Find all elements with specific tag (DFS)
    public function findByTag(DOMNode $root, string $tag): array
    {
        $elements = [];

        if ($root->tag === $tag) {
            $elements[] = $root;
        }

        foreach ($root->children as $child) {
            $elements = array_merge($elements, $this->findByTag($child, $tag));
        }

        return $elements;
    }

    // Find elements with specific attribute
    public function findByAttribute(DOMNode $root, string $attr, mixed $value = null): array
    {
        $elements = [];

        if (isset($root->attributes[$attr])) {
            if ($value === null || $root->attributes[$attr] === $value) {
                $elements[] = $root;
            }
        }

        foreach ($root->children as $child) {
            $elements = array_merge($elements, $this->findByAttribute($child, $attr, $value));
        }

        return $elements;
    }

    // Render HTML (pre-order traversal)
    public function render(DOMNode $node, int $indent = 0): string
    {
        $html = str_repeat('  ', $indent);
        $html .= '<' . $node->tag;

        foreach ($node->attributes as $key => $value) {
            $html .= " {$key}=\"{$value}\"";
        }

        if (empty($node->children) && $node->text === null) {
            $html .= " />\n";
        } else {
            $html .= '>';

            if ($node->text !== null) {
                $html .= $node->text;
            } else {
                $html .= "\n";
                foreach ($node->children as $child) {
                    $html .= $this->render($child, $indent + 1);
                }
                $html .= str_repeat('  ', $indent);
            }

            $html .= "</{$node->tag}>\n";
        }

        return $html;
    }
}
```

## Best Practices

1. **Choose the Right Traversal**
   - In-order: BST operations, sorted output
   - Pre-order: Tree copying, prefix expressions
   - Post-order: Tree deletion, postfix expressions
   - Level-order: Level-wise processing, shortest path

2. **Iterative vs Recursive**
   - Recursive: Cleaner code, easier to understand
   - Iterative: Better for deep trees (avoid stack overflow)

3. **Space Optimization**
   - Use Morris traversal when O(1) space is critical
   - Be aware it temporarily modifies the tree

4. **Error Handling**
   - Always check for null nodes
   - Handle empty trees gracefully

## Practice Exercises

1. **Basic Traversals**
   - Implement all four traversals (in/pre/post/level-order) both recursively and iteratively
   - Verify outputs match for the same tree

2. **Vertical Order Traversal**
   - Print tree nodes in vertical order (nodes in same column together)
   - Use horizontal distance from root

3. **Boundary Traversal**
   - Print left boundary, leaves, right boundary (counterclockwise)
   - Used in tree visualization

4. **Diagonal Traversal**
   - Print nodes in diagonal order
   - Nodes at same diagonal sum have same slope

5. **View Problems**
   - Right side view: Nodes visible from right side
   - Left side view: Nodes visible from left side
   - Top view: Nodes visible from top
   - Bottom view: Nodes visible from bottom

## Key Takeaways

- Tree traversal systematically visits every node exactly once
- Depth-first (in/pre/post-order) uses stack, breadth-first (level-order) uses queue
- Recursive implementations are simpler but use call stack space
- Iterative implementations have explicit control over memory usage
- Morris traversal achieves O(1) space by threading the tree
- Choose traversal based on the problem: sorted output (in-order), copying (pre-order), deletion (post-order), level processing (level-order)
- Understanding traversal is fundamental for tree algorithms

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 19 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-19)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-19
php 01-*.php
```

## Next Steps

In the next chapter, we'll explore balanced trees including AVL trees and Red-Black trees, which maintain logarithmic height for optimal operation performance.
