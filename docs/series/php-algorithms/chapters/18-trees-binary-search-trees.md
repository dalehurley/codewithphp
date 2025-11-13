---
title: "18: Trees & Binary Search Trees"
description: "Learn tree terminology and implement a BST. Master insertion, deletion, and search operations."
series: "php-algorithms"
chapter: 18
order: 18
difficulty: "Advanced"
prerequisites:
  - "Understanding of recursion"
  - "Familiarity with linked structures"
  - "Completion of Chapters 15-17"
---

# Trees & Binary Search Trees

Trees are hierarchical data structures fundamental to computer science. From file systems to database indexes, trees power many core technologies. In this chapter, we'll explore tree concepts and implement Binary Search Trees (BSTs), one of the most important tree variants.

## What Is a Tree?

A **tree** is a hierarchical structure with nodes connected by edges, starting from a root node.

**Tree terminology:**

```
         A           ← Root
        / \
       B   C         ← Level 1 (children of A)
      / \   \
     D   E   F       ← Level 2 (leaves)
```

- **Root**: Top node (A)
- **Parent**: Node with children (A is parent of B and C)
- **Child**: Node below another (B and C are children of A)
- **Leaf**: Node with no children (D, E, F)
- **Edge**: Connection between nodes
- **Path**: Sequence of nodes (A → B → D)
- **Height**: Longest path from node to leaf
- **Depth**: Distance from root to node
- **Subtree**: Tree consisting of node and its descendants

## Binary Trees

A **binary tree** is a tree where each node has at most 2 children (left and right).

### Node Structure

```php
class TreeNode
{
    public function __construct(
        public mixed $data,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}
```

### Creating a Binary Tree

```php
// Manual construction
$root = new TreeNode(1);
$root->left = new TreeNode(2);
$root->right = new TreeNode(3);
$root->left->left = new TreeNode(4);
$root->left->right = new TreeNode(5);

/*
Tree structure:
       1
      / \
     2   3
    / \
   4   5
*/
```

### Types of Binary Trees

#### 1. Full Binary Tree
Every node has 0 or 2 children:

```
       1
      / \
     2   3
    / \
   4   5
```

#### 2. Complete Binary Tree
All levels filled except possibly the last, which fills left to right:

```
       1
      / \
     2   3
    / \  /
   4  5 6
```

#### 3. Perfect Binary Tree
All internal nodes have 2 children, all leaves at same level:

```
       1
      / \
     2   3
    / \ / \
   4  5 6  7
```

## Binary Search Tree (BST)

A **Binary Search Tree** is a binary tree where:
- Left subtree contains values < parent
- Right subtree contains values > parent
- Both subtrees are also BSTs

**Example BST:**
```
       8
      / \
     3   10
    / \    \
   1   6    14
      / \   /
     4   7 13
```

**BST Property:** In-order traversal yields sorted sequence!

## Implementing a BST

```php
class BinarySearchTree
{
    private ?TreeNode $root = null;

    // Insert value - O(log n) average, O(n) worst
    public function insert(mixed $value): void
    {
        $this->root = $this->insertNode($this->root, $value);
    }

    private function insertNode(?TreeNode $node, mixed $value): TreeNode
    {
        // Base case: found position
        if ($node === null) {
            return new TreeNode($value);
        }

        // Recursive case: traverse tree
        if ($value < $node->data) {
            $node->left = $this->insertNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->insertNode($node->right, $value);
        }
        // Duplicate values: do nothing (or handle as needed)

        return $node;
    }

    // Search for value - O(log n) average, O(n) worst
    public function search(mixed $value): bool
    {
        return $this->searchNode($this->root, $value);
    }

    private function searchNode(?TreeNode $node, mixed $value): bool
    {
        // Base case: not found
        if ($node === null) {
            return false;
        }

        // Found it!
        if ($value === $node->data) {
            return true;
        }

        // Search left or right subtree
        if ($value < $node->data) {
            return $this->searchNode($node->left, $value);
        } else {
            return $this->searchNode($node->right, $value);
        }
    }

    // Find minimum value - O(h) where h is height
    public function findMin(?TreeNode $node = null): mixed
    {
        if ($node === null) {
            $node = $this->root;
        }

        if ($node === null) {
            throw new UnderflowException("Tree is empty");
        }

        // Minimum is leftmost node
        while ($node->left !== null) {
            $node = $node->left;
        }

        return $node->data;
    }

    // Find maximum value - O(h)
    public function findMax(?TreeNode $node = null): mixed
    {
        if ($node === null) {
            $node = $this->root;
        }

        if ($node === null) {
            throw new UnderflowException("Tree is empty");
        }

        // Maximum is rightmost node
        while ($node->right !== null) {
            $node = $node->right;
        }

        return $node->data;
    }

    // Delete value - O(log n) average, O(n) worst
    public function delete(mixed $value): void
    {
        $this->root = $this->deleteNode($this->root, $value);
    }

    private function deleteNode(?TreeNode $node, mixed $value): ?TreeNode
    {
        if ($node === null) {
            return null;
        }

        // Find node to delete
        if ($value < $node->data) {
            $node->left = $this->deleteNode($node->left, $value);
        } elseif ($value > $node->data) {
            $node->right = $this->deleteNode($node->right, $value);
        } else {
            // Found node to delete

            // Case 1: No children (leaf)
            if ($node->left === null && $node->right === null) {
                return null;
            }

            // Case 2: One child
            if ($node->left === null) {
                return $node->right;
            }
            if ($node->right === null) {
                return $node->left;
            }

            // Case 3: Two children
            // Replace with inorder successor (min of right subtree)
            $successor = $this->findMin($node->right);
            $node->data = $successor;
            $node->right = $this->deleteNode($node->right, $successor);
        }

        return $node;
    }

    // Get root (for traversals)
    public function getRoot(): ?TreeNode
    {
        return $this->root;
    }

    // Check if tree is empty
    public function isEmpty(): bool
    {
        return $this->root === null;
    }

    // Get height of tree
    public function height(?TreeNode $node = null): int
    {
        if ($node === null) {
            $node = $this->root;
        }

        if ($node === null) {
            return -1; // Empty tree has height -1
        }

        $leftHeight = $this->height($node->left);
        $rightHeight = $this->height($node->right);

        return 1 + max($leftHeight, $rightHeight);
    }

    // Count nodes in tree
    public function size(?TreeNode $node = null): int
    {
        if ($node === null && $this->root !== null) {
            $node = $this->root;
        }

        if ($node === null) {
            return 0;
        }

        return 1 + $this->size($node->left) + $this->size($node->right);
    }
}

// Usage
$bst = new BinarySearchTree();
$bst->insert(8);
$bst->insert(3);
$bst->insert(10);
$bst->insert(1);
$bst->insert(6);
$bst->insert(14);

echo $bst->search(6) ? "Found" : "Not found"; // Found
echo $bst->findMin(); // 1
echo $bst->findMax(); // 14
echo $bst->height(); // 2
echo $bst->size(); // 6
```

## BST Operations Explained

### Insertion

Start at root, go left if smaller, right if larger:

```php
// Inserting 7 into BST
function insertVisualized(TreeNode $node, int $value, int $depth = 0): TreeNode
{
    $indent = str_repeat("  ", $depth);

    if ($node === null) {
        echo "{$indent}Insert $value here\n";
        return new TreeNode($value);
    }

    echo "{$indent}At {$node->data}: ";

    if ($value < $node->data) {
        echo "Go left\n";
        $node->left = insertVisualized($node->left, $value, $depth + 1);
    } elseif ($value > $node->data) {
        echo "Go right\n";
        $node->right = insertVisualized($node->right, $value, $depth + 1);
    }

    return $node;
}
```

### Deletion Cases

**Case 1: Leaf node (no children)**
```
Delete 1 from:       8
                    / \
                   3   10
                  /
                 1

Simply remove:       8
                    / \
                   3   10
```

**Case 2: One child**
```
Delete 10 from:      8
                    / \
                   3   10
                        \
                        14

Replace with child:  8
                    / \
                   3   14
```

**Case 3: Two children**
```
Delete 8 from:       8
                    / \
                   3   10
                  / \    \
                 1   6   14

Replace with successor (10):
                    10
                    / \
                   3   14
                  / \
                 1   6
```

## Validating a BST

Check if a tree is a valid BST:

```php
function isValidBST(?TreeNode $node, ?int $min = null, ?int $max = null): bool
{
    // Empty tree is valid
    if ($node === null) {
        return true;
    }

    // Check current node's value
    if ($min !== null && $node->data <= $min) {
        return false;
    }
    if ($max !== null && $node->data >= $max) {
        return false;
    }

    // Recursively validate subtrees
    return isValidBST($node->left, $min, $node->data) &&
           isValidBST($node->right, $node->data, $max);
}

// Valid BST
$valid = new TreeNode(8);
$valid->left = new TreeNode(3);
$valid->right = new TreeNode(10);
echo isValidBST($valid) ? "Valid" : "Invalid"; // Valid

// Invalid BST
$invalid = new TreeNode(8);
$invalid->left = new TreeNode(3);
$invalid->right = new TreeNode(10);
$invalid->left->right = new TreeNode(15); // Wrong! 15 > 8
echo isValidBST($invalid) ? "Valid" : "Invalid"; // Invalid
```

## Finding Successor and Predecessor

### Inorder Successor
Next larger value:

```php
function inorderSuccessor(TreeNode $root, int $value): ?int
{
    $successor = null;
    $current = $root;

    while ($current !== null) {
        if ($value < $current->data) {
            $successor = $current->data;
            $current = $current->left;
        } else {
            $current = $current->right;
        }
    }

    return $successor;
}
```

### Inorder Predecessor
Next smaller value:

```php
function inorderPredecessor(TreeNode $root, int $value): ?int
{
    $predecessor = null;
    $current = $root;

    while ($current !== null) {
        if ($value > $current->data) {
            $predecessor = $current->data;
            $current = $current->right;
        } else {
            $current = $current->left;
        }
    }

    return $predecessor;
}
```

## Lowest Common Ancestor (LCA)

Find the lowest common ancestor of two nodes:

```php
function findLCA(TreeNode $root, int $n1, int $n2): ?TreeNode
{
    if ($root === null) {
        return null;
    }

    // Both nodes in left subtree
    if ($n1 < $root->data && $n2 < $root->data) {
        return findLCA($root->left, $n1, $n2);
    }

    // Both nodes in right subtree
    if ($n1 > $root->data && $n2 > $root->data) {
        return findLCA($root->right, $n1, $n2);
    }

    // Split: root is LCA
    return $root;
}
```

## Range Queries

Find all values in a range:

```php
function rangeQuery(
    ?TreeNode $node,
    int $min,
    int $max,
    array &$result = []
): array {
    if ($node === null) {
        return $result;
    }

    // If node could have left children in range
    if ($min < $node->data) {
        rangeQuery($node->left, $min, $max, $result);
    }

    // If node itself is in range
    if ($min <= $node->data && $node->data <= $max) {
        $result[] = $node->data;
    }

    // If node could have right children in range
    if ($node->data < $max) {
        rangeQuery($node->right, $min, $max, $result);
    }

    return $result;
}

// Find all values between 5 and 12
$result = rangeQuery($root, 5, 12);
```

## Kth Smallest Element

Find the kth smallest element in BST:

```php
function kthSmallest(TreeNode $root, int $k): ?int
{
    $count = 0;
    $result = null;

    $inorder = function(?TreeNode $node) use (&$inorder, &$count, $k, &$result) {
        if ($node === null || $result !== null) {
            return;
        }

        // Process left subtree
        $inorder($node->left);

        // Process current node
        $count++;
        if ($count === $k) {
            $result = $node->data;
            return;
        }

        // Process right subtree
        $inorder($node->right);
    };

    $inorder($root);
    return $result;
}
```

## Converting Array to BST

Build a balanced BST from sorted array:

```php
function sortedArrayToBST(array $nums): ?TreeNode
{
    return buildBST($nums, 0, count($nums) - 1);
}

function buildBST(array $nums, int $left, int $right): ?TreeNode
{
    if ($left > $right) {
        return null;
    }

    // Choose middle element as root
    $mid = (int)(($left + $right) / 2);
    $node = new TreeNode($nums[$mid]);

    // Recursively build left and right subtrees
    $node->left = buildBST($nums, $left, $mid - 1);
    $node->right = buildBST($nums, $mid + 1, $right);

    return $node;
}

$sorted = [1, 2, 3, 4, 5, 6, 7];
$balanced = sortedArrayToBST($sorted);
// Creates balanced BST:
//        4
//       / \
//      2   6
//     / \ / \
//    1  3 5  7
```

## Real-World Applications

### 1. Dictionary/Spell Checker

```php
class Dictionary
{
    private BinarySearchTree $words;

    public function __construct()
    {
        $this->words = new BinarySearchTree();
    }

    public function addWord(string $word): void
    {
        $this->words->insert(strtolower($word));
    }

    public function isValidWord(string $word): bool
    {
        return $this->words->search(strtolower($word));
    }

    public function loadFromFile(string $filename): void
    {
        $handle = fopen($filename, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $this->addWord(trim($line));
            }
            fclose($handle);
        }
    }
}

$dict = new Dictionary();
$dict->addWord("hello");
$dict->addWord("world");
echo $dict->isValidWord("hello") ? "Valid" : "Invalid";
```

### 2. File System Hierarchy

```php
class FileNode
{
    public function __construct(
        public string $name,
        public bool $isDirectory,
        public ?FileNode $left = null,
        public ?FileNode $right = null
    ) {}
}

class FileSystem
{
    private ?FileNode $root = null;

    public function insert(string $name, bool $isDirectory): void
    {
        $this->root = $this->insertNode($this->root, $name, $isDirectory);
    }

    private function insertNode(
        ?FileNode $node,
        string $name,
        bool $isDirectory
    ): FileNode {
        if ($node === null) {
            return new FileNode($name, $isDirectory);
        }

        if ($name < $node->name) {
            $node->left = $this->insertNode($node->left, $name, $isDirectory);
        } else {
            $node->right = $this->insertNode($node->right, $name, $isDirectory);
        }

        return $node;
    }

    public function listFiles(?FileNode $node = null): void
    {
        if ($node === null) {
            $node = $this->root;
        }

        if ($node === null) {
            return;
        }

        $this->listFiles($node->left);
        $icon = $node->isDirectory ? "📁" : "📄";
        echo "$icon {$node->name}\n";
        $this->listFiles($node->right);
    }
}
```

## BST Complexity Analysis

| Operation | Average Case | Worst Case |
|-----------|-------------|------------|
| **Search** | O(log n) | O(n) |
| **Insert** | O(log n) | O(n) |
| **Delete** | O(log n) | O(n) |
| **Find Min/Max** | O(log n) | O(n) |
| **Space** | O(n) | O(n) |

**Worst case** happens when tree is skewed (essentially a linked list).

## Practice Exercises

### Exercise 1: Serialize and Deserialize BST

Convert BST to string and back:

```php
function serialize(?TreeNode $root): string
{
    // Your code here
}

function deserialize(string $data): ?TreeNode
{
    // Your code here
}
```

### Exercise 2: BST Iterator

Create an iterator that returns nodes in sorted order:

```php
class BSTIterator
{
    public function __construct(TreeNode $root) {}

    public function hasNext(): bool {}

    public function next(): int {}
}
```

### Exercise 3: Merge Two BSTs

Merge two BSTs into one balanced BST:

```php
function mergeBSTs(TreeNode $root1, TreeNode $root2): TreeNode
{
    // Your code here
}
```

## Key Takeaways

- **Trees** are hierarchical structures with nodes and edges
- **Binary trees** have at most 2 children per node
- **BSTs** maintain sorted order: left < parent < right
- **BST operations** are O(log n) average, O(n) worst
- **Deletion** has 3 cases: leaf, one child, two children
- **Inorder traversal** of BST yields sorted sequence
- **Balanced trees** needed to guarantee O(log n) performance
- Common applications: dictionaries, file systems, databases

## What's Next

In the next chapter, we'll explore **Tree Traversal Algorithms**, learning different ways to visit all nodes in a tree systematically.

---

Continue to [Chapter 19: Tree Traversal Algorithms](/series/php-algorithms/chapters/19-tree-traversal-algorithms).
