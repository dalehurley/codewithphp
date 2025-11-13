---
title: "Balanced Trees: AVL & Red-Black Trees"
description: "Learn about self-balancing binary search trees including AVL trees and Red-Black trees, understanding their balancing mechanisms and guaranteed logarithmic performance"
series: "php-algorithms"
chapter: 20
order: 20
difficulty: "advanced"
prerequisites: ["Trees & Binary Search Trees", "Tree Traversal Algorithms"]
---

# Balanced Trees: AVL & Red-Black Trees

Regular binary search trees can become unbalanced, degrading to O(n) time complexity in the worst case. Self-balancing trees maintain logarithmic height automatically, ensuring O(log n) performance for all operations.

## The Problem with Unbalanced Trees

```php
<?php

// Inserting sorted data into a BST creates a linked list
$bst = new BinarySearchTree();
foreach ([1, 2, 3, 4, 5, 6, 7] as $value) {
    $bst->insert($value);
}

// Tree structure becomes:
//     1
//      \
//       2
//        \
//         3
//          \
//           4
//            \
//             5
//              \
//               6
//                \
//                 7

// Height: 7, Search time: O(n) instead of O(log n)
```

Self-balancing trees solve this by maintaining balance through rotations and color properties.

## AVL Trees

AVL (Adelson-Velsky and Landis) trees maintain strict balance: the height difference between left and right subtrees (balance factor) of any node is at most 1.

### Balance Factor

```
Balance Factor = Height(Left Subtree) - Height(Right Subtree)
Valid values: -1, 0, 1
```

### AVL Node Implementation

```php
<?php

class AVLNode
{
    public function __construct(
        public mixed $data,
        public ?AVLNode $left = null,
        public ?AVLNode $right = null,
        public int $height = 1
    ) {}
}

class AVLTree
{
    private ?AVLNode $root = null;

    // Get height of node
    private function height(?AVLNode $node): int
    {
        return $node?->height ?? 0;
    }

    // Update height of node
    private function updateHeight(AVLNode $node): void
    {
        $node->height = 1 + max(
            $this->height($node->left),
            $this->height($node->right)
        );
    }

    // Get balance factor
    private function getBalance(?AVLNode $node): int
    {
        if ($node === null) {
            return 0;
        }

        return $this->height($node->left) - $this->height($node->right);
    }

    // Right rotation
    //       y                    x
    //      / \                  / \
    //     x   C    =>          A   y
    //    / \                      / \
    //   A   B                    B   C
    private function rotateRight(AVLNode $y): AVLNode
    {
        $x = $y->left;
        $B = $x->right;

        // Perform rotation
        $x->right = $y;
        $y->left = $B;

        // Update heights
        $this->updateHeight($y);
        $this->updateHeight($x);

        return $x;
    }

    // Left rotation
    //     x                      y
    //    / \                    / \
    //   A   y      =>          x   C
    //      / \                / \
    //     B   C              A   B
    private function rotateLeft(AVLNode $x): AVLNode
    {
        $y = $x->right;
        $B = $y->left;

        // Perform rotation
        $y->left = $x;
        $x->right = $B;

        // Update heights
        $this->updateHeight($x);
        $this->updateHeight($y);

        return $y;
    }

    // Insert a value
    public function insert(mixed $value): void
    {
        $this->root = $this->insertNode($this->root, $value);
    }

    private function insertNode(?AVLNode $node, mixed $value): AVLNode
    {
        // Normal BST insertion
        if ($node === null) {
            return new AVLNode($value);
        }

        if ($value < $node->data) {
            $node->left = $this->insertNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->insertNode($node->right, $value);
        } else {
            // Duplicate values not allowed
            return $node;
        }

        // Update height
        $this->updateHeight($node);

        // Get balance factor
        $balance = $this->getBalance($node);

        // Left-Left Case (Right rotation)
        if ($balance > 1 && $value < $node->left->data) {
            return $this->rotateRight($node);
        }

        // Right-Right Case (Left rotation)
        if ($balance < -1 && $value > $node->right->data) {
            return $this->rotateLeft($node);
        }

        // Left-Right Case (Left rotation then Right rotation)
        if ($balance > 1 && $value > $node->left->data) {
            $node->left = $this->rotateLeft($node->left);
            return $this->rotateRight($node);
        }

        // Right-Left Case (Right rotation then Left rotation)
        if ($balance < -1 && $value < $node->right->data) {
            $node->right = $this->rotateRight($node->right);
            return $this->rotateLeft($node);
        }

        return $node;
    }

    // Find minimum value node
    private function minValueNode(AVLNode $node): AVLNode
    {
        $current = $node;
        while ($current->left !== null) {
            $current = $current->left;
        }
        return $current;
    }

    // Delete a value
    public function delete(mixed $value): void
    {
        $this->root = $this->deleteNode($this->root, $value);
    }

    private function deleteNode(?AVLNode $node, mixed $value): ?AVLNode
    {
        // Normal BST deletion
        if ($node === null) {
            return null;
        }

        if ($value < $node->data) {
            $node->left = $this->deleteNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->deleteNode($node->right, $value);
        } else {
            // Node to be deleted found

            // Node with one child or no child
            if ($node->left === null) {
                return $node->right;
            } elseif ($node->right === null) {
                return $node->left;
            }

            // Node with two children: get inorder successor
            $successor = $this->minValueNode($node->right);
            $node->data = $successor->data;
            $node->right = $this->deleteNode($node->right, $successor->data);
        }

        // Update height
        $this->updateHeight($node);

        // Get balance factor
        $balance = $this->getBalance($node);

        // Left-Left Case
        if ($balance > 1 && $this->getBalance($node->left) >= 0) {
            return $this->rotateRight($node);
        }

        // Left-Right Case
        if ($balance > 1 && $this->getBalance($node->left) < 0) {
            $node->left = $this->rotateLeft($node->left);
            return $this->rotateRight($node);
        }

        // Right-Right Case
        if ($balance < -1 && $this->getBalance($node->right) <= 0) {
            return $this->rotateLeft($node);
        }

        // Right-Left Case
        if ($balance < -1 && $this->getBalance($node->right) > 0) {
            $node->right = $this->rotateRight($node->right);
            return $this->rotateLeft($node);
        }

        return $node;
    }

    // Search for a value
    public function search(mixed $value): bool
    {
        return $this->searchNode($this->root, $value);
    }

    private function searchNode(?AVLNode $node, mixed $value): bool
    {
        if ($node === null) {
            return false;
        }

        if ($value === $node->data) {
            return true;
        }

        if ($value < $node->data) {
            return $this->searchNode($node->left, $value);
        }

        return $this->searchNode($node->right, $value);
    }

    // In-order traversal
    public function inOrder(): array
    {
        $result = [];
        $this->inOrderTraversal($this->root, $result);
        return $result;
    }

    private function inOrderTraversal(?AVLNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $this->inOrderTraversal($node->left, $result);
        $result[] = $node->data;
        $this->inOrderTraversal($node->right, $result);
    }

    // Visualize tree structure
    public function visualize(): void
    {
        $this->printTree($this->root, '', true);
    }

    private function printTree(?AVLNode $node, string $prefix, bool $isRight): void
    {
        if ($node === null) {
            return;
        }

        echo $prefix;
        echo $isRight ? '└── ' : '├── ';
        echo "{$node->data} (h:{$node->height}, bf:{$this->getBalance($node)})\n";

        $newPrefix = $prefix . ($isRight ? '    ' : '│   ');
        if ($node->left !== null || $node->right !== null) {
            $this->printTree($node->right, $newPrefix, false);
            $this->printTree($node->left, $newPrefix, true);
        }
    }
}

// Example usage
$avl = new AVLTree();
foreach ([10, 20, 30, 40, 50, 25] as $value) {
    $avl->insert($value);
}

$avl->visualize();
// Output shows balanced tree:
//         30 (h:3, bf:0)
//         ├── 40 (h:2, bf:-1)
//         │   └── 50 (h:1, bf:0)
//         └── 20 (h:2, bf:0)
//             ├── 25 (h:1, bf:0)
//             └── 10 (h:1, bf:0)

print_r($avl->inOrder());  // [10, 20, 25, 30, 40, 50]
echo $avl->search(25) ? 'Found' : 'Not found';  // Found

$avl->delete(30);
$avl->visualize();
```

## AVL Tree Rotations

### Four Cases Requiring Rebalancing

```php
<?php

class AVLTreeRotations
{
    // Case 1: Left-Left (Single Right Rotation)
    //       z                      y
    //      / \                    / \
    //     y   D    =>            x   z
    //    / \                    /   / \
    //   x   C                  A   C   D
    //  /
    // A
    public function leftLeftCase(): void
    {
        // Insert into left subtree of left child
        // Solution: Single right rotation at z
        echo "Left-Left Case: Single right rotation\n";
    }

    // Case 2: Right-Right (Single Left Rotation)
    //   z                          y
    //  / \                        / \
    // A   y        =>            z   x
    //    / \                    / \   \
    //   B   x                  A   B   D
    //        \
    //         D
    public function rightRightCase(): void
    {
        // Insert into right subtree of right child
        // Solution: Single left rotation at z
        echo "Right-Right Case: Single left rotation\n";
    }

    // Case 3: Left-Right (Double Rotation: Left then Right)
    //     z                    z                    x
    //    / \                  / \                  / \
    //   y   D    =>          x   D    =>          y   z
    //  / \                  / \                  /   / \
    // A   x                y   C                A   C   D
    //    /                /
    //   C                A
    public function leftRightCase(): void
    {
        // Insert into right subtree of left child
        // Solution: Left rotation at y, then right rotation at z
        echo "Left-Right Case: Double rotation (L-R)\n";
    }

    // Case 4: Right-Left (Double Rotation: Right then Left)
    //   z                    z                      x
    //  / \                  / \                    / \
    // A   y    =>          A   x        =>        z   y
    //    / \                  / \                / \   \
    //   x   D                B   y              A   B   D
    //    \                      /
    //     B                    D
    public function rightLeftCase(): void
    {
        // Insert into left subtree of right child
        // Solution: Right rotation at y, then left rotation at z
        echo "Right-Left Case: Double rotation (R-L)\n";
    }
}
```

## Red-Black Trees

Red-Black trees are self-balancing binary search trees with less strict balancing than AVL trees, using node colors to maintain balance.

### Red-Black Tree Properties

1. Every node is either red or black
2. Root is always black
3. All leaves (NULL) are black
4. Red nodes have black children (no two red nodes in a row)
5. All paths from root to leaves have the same number of black nodes

### Red-Black Node Implementation

```php
<?php

enum NodeColor: string
{
    case RED = 'red';
    case BLACK = 'black';
}

class RBNode
{
    public function __construct(
        public mixed $data,
        public NodeColor $color = NodeColor::RED,
        public ?RBNode $left = null,
        public ?RBNode $right = null,
        public ?RBNode $parent = null
    ) {}
}

class RedBlackTree
{
    private ?RBNode $root = null;
    private RBNode $nil;  // Sentinel node for leaves

    public function __construct()
    {
        // NIL node is always black
        $this->nil = new RBNode(null, NodeColor::BLACK);
        $this->root = $this->nil;
    }

    // Left rotation
    private function rotateLeft(RBNode $x): void
    {
        $y = $x->right;
        $x->right = $y->left;

        if ($y->left !== $this->nil) {
            $y->left->parent = $x;
        }

        $y->parent = $x->parent;

        if ($x->parent === null) {
            $this->root = $y;
        } elseif ($x === $x->parent->left) {
            $x->parent->left = $y;
        } else {
            $x->parent->right = $y;
        }

        $y->left = $x;
        $x->parent = $y;
    }

    // Right rotation
    private function rotateRight(RBNode $y): void
    {
        $x = $y->left;
        $y->left = $x->right;

        if ($x->right !== $this->nil) {
            $x->right->parent = $y;
        }

        $x->parent = $y->parent;

        if ($y->parent === null) {
            $this->root = $x;
        } elseif ($y === $y->parent->right) {
            $y->parent->right = $x;
        } else {
            $y->parent->left = $x;
        }

        $x->right = $y;
        $y->parent = $x;
    }

    // Insert a value
    public function insert(mixed $value): void
    {
        $node = new RBNode($value, NodeColor::RED, $this->nil, $this->nil);

        $parent = null;
        $current = $this->root;

        // Find position for new node
        while ($current !== $this->nil) {
            $parent = $current;
            if ($value < $current->data) {
                $current = $current->left;
            } else {
                $current = $current->right;
            }
        }

        $node->parent = $parent;

        // Insert node
        if ($parent === null) {
            $this->root = $node;
        } elseif ($value < $parent->data) {
            $parent->left = $node;
        } else {
            $parent->right = $node;
        }

        // Fix Red-Black properties
        $this->insertFixup($node);
    }

    // Fix Red-Black tree properties after insertion
    private function insertFixup(RBNode $z): void
    {
        while ($z->parent !== null && $z->parent->color === NodeColor::RED) {
            if ($z->parent === $z->parent->parent?->left) {
                $y = $z->parent->parent->right;  // Uncle

                if ($y->color === NodeColor::RED) {
                    // Case 1: Uncle is red
                    $z->parent->color = NodeColor::BLACK;
                    $y->color = NodeColor::BLACK;
                    $z->parent->parent->color = NodeColor::RED;
                    $z = $z->parent->parent;
                } else {
                    if ($z === $z->parent->right) {
                        // Case 2: Uncle is black, z is right child
                        $z = $z->parent;
                        $this->rotateLeft($z);
                    }
                    // Case 3: Uncle is black, z is left child
                    $z->parent->color = NodeColor::BLACK;
                    $z->parent->parent->color = NodeColor::RED;
                    $this->rotateRight($z->parent->parent);
                }
            } else {
                // Mirror cases
                $y = $z->parent->parent?->left;  // Uncle

                if ($y !== null && $y->color === NodeColor::RED) {
                    $z->parent->color = NodeColor::BLACK;
                    $y->color = NodeColor::BLACK;
                    $z->parent->parent->color = NodeColor::RED;
                    $z = $z->parent->parent;
                } else {
                    if ($z === $z->parent->left) {
                        $z = $z->parent;
                        $this->rotateRight($z);
                    }
                    $z->parent->color = NodeColor::BLACK;
                    if ($z->parent->parent !== null) {
                        $z->parent->parent->color = NodeColor::RED;
                        $this->rotateLeft($z->parent->parent);
                    }
                }
            }
        }

        $this->root->color = NodeColor::BLACK;
    }

    // Search for a value
    public function search(mixed $value): bool
    {
        return $this->searchNode($this->root, $value);
    }

    private function searchNode(?RBNode $node, mixed $value): bool
    {
        if ($node === null || $node === $this->nil) {
            return false;
        }

        if ($value === $node->data) {
            return true;
        }

        if ($value < $node->data) {
            return $this->searchNode($node->left, $value);
        }

        return $this->searchNode($node->right, $value);
    }

    // Find minimum node
    private function minimum(RBNode $node): RBNode
    {
        while ($node->left !== $this->nil) {
            $node = $node->left;
        }
        return $node;
    }

    // Transplant subtree
    private function transplant(RBNode $u, RBNode $v): void
    {
        if ($u->parent === null) {
            $this->root = $v;
        } elseif ($u === $u->parent->left) {
            $u->parent->left = $v;
        } else {
            $u->parent->right = $v;
        }
        $v->parent = $u->parent;
    }

    // Delete a value
    public function delete(mixed $value): void
    {
        $z = $this->findNode($this->root, $value);
        if ($z === $this->nil) {
            return;  // Value not found
        }

        $y = $z;
        $yOriginalColor = $y->color;

        if ($z->left === $this->nil) {
            $x = $z->right;
            $this->transplant($z, $z->right);
        } elseif ($z->right === $this->nil) {
            $x = $z->left;
            $this->transplant($z, $z->left);
        } else {
            $y = $this->minimum($z->right);
            $yOriginalColor = $y->color;
            $x = $y->right;

            if ($y->parent === $z) {
                $x->parent = $y;
            } else {
                $this->transplant($y, $y->right);
                $y->right = $z->right;
                $y->right->parent = $y;
            }

            $this->transplant($z, $y);
            $y->left = $z->left;
            $y->left->parent = $y;
            $y->color = $z->color;
        }

        if ($yOriginalColor === NodeColor::BLACK) {
            $this->deleteFixup($x);
        }
    }

    // Find node with given value
    private function findNode(RBNode $node, mixed $value): RBNode
    {
        while ($node !== $this->nil && $value !== $node->data) {
            if ($value < $node->data) {
                $node = $node->left;
            } else {
                $node = $node->right;
            }
        }
        return $node;
    }

    // Fix Red-Black tree properties after deletion
    private function deleteFixup(RBNode $x): void
    {
        while ($x !== $this->root && $x->color === NodeColor::BLACK) {
            if ($x === $x->parent?->left) {
                $w = $x->parent->right;

                if ($w->color === NodeColor::RED) {
                    $w->color = NodeColor::BLACK;
                    $x->parent->color = NodeColor::RED;
                    $this->rotateLeft($x->parent);
                    $w = $x->parent->right;
                }

                if ($w->left->color === NodeColor::BLACK &&
                    $w->right->color === NodeColor::BLACK) {
                    $w->color = NodeColor::RED;
                    $x = $x->parent;
                } else {
                    if ($w->right->color === NodeColor::BLACK) {
                        $w->left->color = NodeColor::BLACK;
                        $w->color = NodeColor::RED;
                        $this->rotateRight($w);
                        $w = $x->parent->right;
                    }
                    $w->color = $x->parent->color;
                    $x->parent->color = NodeColor::BLACK;
                    $w->right->color = NodeColor::BLACK;
                    $this->rotateLeft($x->parent);
                    $x = $this->root;
                }
            } else {
                // Mirror cases
                $w = $x->parent?->left;

                if ($w !== null && $w->color === NodeColor::RED) {
                    $w->color = NodeColor::BLACK;
                    $x->parent->color = NodeColor::RED;
                    $this->rotateRight($x->parent);
                    $w = $x->parent->left;
                }

                if ($w !== null &&
                    $w->right->color === NodeColor::BLACK &&
                    $w->left->color === NodeColor::BLACK) {
                    $w->color = NodeColor::RED;
                    $x = $x->parent;
                } else {
                    if ($w !== null && $w->left->color === NodeColor::BLACK) {
                        $w->right->color = NodeColor::BLACK;
                        $w->color = NodeColor::RED;
                        $this->rotateLeft($w);
                        $w = $x->parent->left;
                    }
                    if ($w !== null) {
                        $w->color = $x->parent->color;
                        $x->parent->color = NodeColor::BLACK;
                        $w->left->color = NodeColor::BLACK;
                        $this->rotateRight($x->parent);
                    }
                    $x = $this->root;
                }
            }
        }
        $x->color = NodeColor::BLACK;
    }

    // In-order traversal
    public function inOrder(): array
    {
        $result = [];
        $this->inOrderTraversal($this->root, $result);
        return $result;
    }

    private function inOrderTraversal(RBNode $node, array &$result): void
    {
        if ($node === $this->nil) {
            return;
        }

        $this->inOrderTraversal($node->left, $result);
        $result[] = [
            'value' => $node->data,
            'color' => $node->color->value
        ];
        $this->inOrderTraversal($node->right, $result);
    }
}

// Example usage
$rbt = new RedBlackTree();
foreach ([10, 20, 30, 15, 25, 5] as $value) {
    $rbt->insert($value);
}

print_r($rbt->inOrder());
echo $rbt->search(15) ? 'Found' : 'Not found';  // Found

$rbt->delete(15);
print_r($rbt->inOrder());
```

## AVL vs Red-Black Trees Comparison

```php
<?php

class TreeComparison
{
    public function compareCharacteristics(): array
    {
        return [
            'Balance' => [
                'AVL' => 'Strictly balanced (height diff ≤ 1)',
                'Red-Black' => 'Loosely balanced (black-height equality)'
            ],
            'Height' => [
                'AVL' => '~1.44 * log(n) (tighter)',
                'Red-Black' => '~2 * log(n) (looser)'
            ],
            'Insertion' => [
                'AVL' => 'Slower (more rotations)',
                'Red-Black' => 'Faster (fewer rotations, max 2)'
            ],
            'Deletion' => [
                'AVL' => 'Slower (more rotations)',
                'Red-Black' => 'Faster (fewer rotations, max 3)'
            ],
            'Search' => [
                'AVL' => 'Faster (better balanced)',
                'Red-Black' => 'Slightly slower'
            ],
            'Memory' => [
                'AVL' => 'Height field per node',
                'Red-Black' => 'Color bit per node'
            ],
            'Use Case' => [
                'AVL' => 'Read-heavy workloads',
                'Red-Black' => 'Write-heavy workloads'
            ]
        ];
    }

    public function benchmark(): void
    {
        $operations = 10000;

        // AVL Tree benchmark
        $avl = new AVLTree();
        $avlInsertTime = microtime(true);
        for ($i = 0; $i < $operations; $i++) {
            $avl->insert(random_int(1, $operations * 10));
        }
        $avlInsertTime = microtime(true) - $avlInsertTime;

        // Red-Black Tree benchmark
        $rbt = new RedBlackTree();
        $rbtInsertTime = microtime(true);
        for ($i = 0; $i < $operations; $i++) {
            $rbt->insert(random_int(1, $operations * 10));
        }
        $rbtInsertTime = microtime(true) - $rbtInsertTime;

        echo "Performance Comparison ($operations operations):\n";
        echo "AVL Insert: " . round($avlInsertTime * 1000, 2) . " ms\n";
        echo "RBT Insert: " . round($rbtInsertTime * 1000, 2) . " ms\n";
        echo "Winner: " . ($rbtInsertTime < $avlInsertTime ? 'Red-Black' : 'AVL') . "\n";
    }
}
```

## Complexity Analysis

### AVL Trees

| Operation | Average | Worst Case | Notes |
|-----------|---------|------------|-------|
| Search | O(log n) | O(log n) | Height bounded by 1.44 * log(n) |
| Insert | O(log n) | O(log n) | May require rotations |
| Delete | O(log n) | O(log n) | May require rotations |
| Space | O(n) | O(n) | Height field per node |

### Red-Black Trees

| Operation | Average | Worst Case | Notes |
|-----------|---------|------------|-------|
| Search | O(log n) | O(log n) | Height bounded by 2 * log(n) |
| Insert | O(log n) | O(log n) | Max 2 rotations |
| Delete | O(log n) | O(log n) | Max 3 rotations |
| Space | O(n) | O(n) | Color bit per node |

## Practical Applications

### 1. In-Memory Databases

```php
<?php

class OrderedIndex
{
    private AVLTree $index;

    public function __construct()
    {
        $this->index = new AVLTree();
    }

    public function addRecord(int $id, array $data): void
    {
        $this->index->insert($id);
        // Store actual data elsewhere, indexed by id
    }

    public function findRecord(int $id): bool
    {
        return $this->index->search($id);
    }

    public function rangeQuery(int $start, int $end): array
    {
        // Get all records in sorted order, filter by range
        $allRecords = $this->index->inOrder();
        return array_filter($allRecords, fn($id) => $id >= $start && $id <= $end);
    }
}
```

### 2. PHP's Internal Implementation

```php
<?php

// PHP's SPL (Standard PHP Library) doesn't expose AVL/RB tree directly,
// but you can use SplHeap or implement custom sorted structures

class SortedSet
{
    private AVLTree $tree;

    public function __construct()
    {
        $this->tree = new AVLTree();
    }

    public function add(mixed $value): void
    {
        $this->tree->insert($value);
    }

    public function contains(mixed $value): bool
    {
        return $this->tree->search($value);
    }

    public function toArray(): array
    {
        return $this->tree->inOrder();
    }

    public function first(): mixed
    {
        $values = $this->tree->inOrder();
        return $values[0] ?? null;
    }

    public function last(): mixed
    {
        $values = $this->tree->inOrder();
        return end($values) ?: null;
    }
}

// Usage
$set = new SortedSet();
$set->add(5);
$set->add(2);
$set->add(8);
$set->add(1);

print_r($set->toArray());  // [1, 2, 5, 8] - always sorted
echo $set->first();        // 1
echo $set->last();         // 8
```

### 3. Priority Queue with Updates

```php
<?php

class UpdatablePriorityQueue
{
    private RedBlackTree $tree;
    private array $positions = [];  // Track node positions for updates

    public function __construct()
    {
        $this->tree = new RedBlackTree();
    }

    public function insert(string $id, int $priority): void
    {
        $this->tree->insert($priority);
        $this->positions[$id] = $priority;
    }

    public function updatePriority(string $id, int $newPriority): void
    {
        if (isset($this->positions[$id])) {
            $oldPriority = $this->positions[$id];
            $this->tree->delete($oldPriority);
            $this->tree->insert($newPriority);
            $this->positions[$id] = $newPriority;
        }
    }

    public function extractMin(): ?string
    {
        $values = $this->tree->inOrder();
        if (empty($values)) {
            return null;
        }

        $minPriority = $values[0]['value'];
        $minId = array_search($minPriority, $this->positions);

        $this->tree->delete($minPriority);
        unset($this->positions[$minId]);

        return $minId;
    }
}
```

## Best Practices

1. **Choose the Right Tree**
   - Use AVL for read-heavy workloads (databases, dictionaries)
   - Use Red-Black for write-heavy workloads (system libraries)
   - Consider memory constraints (color bit vs height field)

2. **Implementation Tips**
   - Always maintain tree properties during modifications
   - Use sentinel nodes (NIL) in Red-Black trees to simplify logic
   - Test thoroughly with edge cases (single node, deletions, duplicates)

3. **When to Use**
   - Need guaranteed O(log n) operations
   - Cannot tolerate worst-case O(n) of regular BST
   - Require sorted data with frequent updates

4. **When Not to Use**
   - Small datasets (overhead not worth it)
   - Hash table would work (O(1) average, no ordering needed)
   - Data is append-only (B-tree might be better)

## Practice Exercises

1. **Validate Balance**
   - Write a function to verify AVL tree balance factors
   - Check Red-Black tree properties are maintained

2. **Merge Two Trees**
   - Merge two AVL/Red-Black trees into one balanced tree
   - Maintain balance throughout

3. **Range Count**
   - Count nodes with values in range [a, b]
   - Optimize better than O(n)

4. **Kth Smallest with Updates**
   - Augment tree to find kth smallest in O(log n)
   - Handle insertions and deletions

5. **Interval Tree**
   - Extend balanced tree to store intervals
   - Support overlapping interval queries

## Key Takeaways

- Balanced trees maintain O(log n) height automatically through rotations
- AVL trees are strictly balanced (height diff ≤ 1), better for searches
- Red-Black trees are loosely balanced (black-height equality), better for updates
- AVL requires more rotations (slower updates) but has tighter height bounds
- Red-Black requires fewer rotations (max 2-3) but slightly taller trees
- Choose based on workload: AVL for read-heavy, Red-Black for write-heavy
- Both guarantee O(log n) worst-case for search, insert, delete
- PHP doesn't have built-in balanced trees, but concepts apply to many data structures
- Understanding balanced trees is essential for database internals and system programming

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 20 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-20)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-20
php 01-*.php
```

## Next Steps

In the next section, we'll explore graph algorithms, starting with graph representations and fundamental traversal algorithms like BFS and DFS applied to graphs.
