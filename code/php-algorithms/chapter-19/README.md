# Chapter 19: Tree Traversal Algorithms - Code Samples

Comprehensive implementations of all major tree traversal algorithms, both recursive and iterative approaches.

## Files Overview

### 1. `01-tree-traversals.php`
**Complete Tree Traversal Implementations**

Includes all major traversal algorithms:
- **Depth-First Traversals** (recursive & iterative)
  - In-order (Left-Root-Right)
  - Pre-order (Root-Left-Right)
  - Post-order (Left-Right-Root)
- **Breadth-First Traversals**
  - Level-order
  - Level-order grouped
  - Zigzag level-order
- **Space-Optimized**
  - Morris traversal (O(1) space)
- **Path Algorithms**
  - Find all root-to-leaf paths

**Run:** `php 01-tree-traversals.php`

## Traversal Types

### Depth-First Search (DFS)

#### 1. In-Order (Left-Root-Right)
```
For tree:     4
             / \
            2   6
           / \ / \
          1  3 5  7

Result: 1, 2, 3, 4, 5, 6, 7 (sorted for BST!)
```

**Use Cases:**
- Getting sorted output from BST
- Expression tree to infix notation
- Validating BST properties

#### 2. Pre-Order (Root-Left-Right)
```
Result: 4, 2, 1, 3, 6, 5, 7
```

**Use Cases:**
- Creating copy of tree
- Prefix notation (Polish notation)
- Serializing tree
- Directory listing

#### 3. Post-Order (Left-Right-Root)
```
Result: 1, 3, 2, 5, 7, 6, 4
```

**Use Cases:**
- Deleting tree (delete children before parent)
- Postfix notation (RPN)
- Calculating directory sizes
- Expression evaluation

### Breadth-First Search (BFS)

#### Level-Order
```
For tree:     4
             / \
            2   6
           / \ / \
          1  3 5  7

Result: 4, 2, 6, 1, 3, 5, 7 (level by level)
```

**Use Cases:**
- Finding shortest path
- Level-wise processing
- Serialization for complete trees
- Finding nodes at specific level

## Time & Space Complexity

| Traversal | Time | Space (Recursive) | Space (Iterative) | Space (Morris) |
|-----------|------|-------------------|-------------------|----------------|
| In-order | O(n) | O(h) | O(h) | O(1) |
| Pre-order | O(n) | O(h) | O(h) | O(1) |
| Post-order | O(n) | O(h) | O(h) | O(1) |
| Level-order | O(n) | O(w) | O(w) | N/A |

Where:
- **n** = number of nodes
- **h** = height of tree (O(log n) balanced, O(n) skewed)
- **w** = maximum width of tree (O(n) worst case)

## Choosing the Right Traversal

### Use In-Order When:
- Working with BST and need sorted output
- Validating BST properties
- Finding kth smallest/largest element

### Use Pre-Order When:
- Copying tree structure
- Creating prefix expressions
- Serializing tree
- Path finding from root

### Use Post-Order When:
- Deleting entire tree
- Calculating heights/sizes
- Postfix expression evaluation
- Processing children before parent

### Use Level-Order When:
- Finding shortest path
- Printing by levels
- Finding nodes at distance k
- Checking completeness

## Recursive vs Iterative

### Recursive
**Pros:**
- Cleaner, more intuitive code
- Matches tree structure naturally
- Less code to write

**Cons:**
- Stack overflow risk on deep trees
- Hidden space complexity (call stack)
- Harder to pause/resume

### Iterative
**Pros:**
- No stack overflow risk
- Explicit control over memory
- Can pause/resume easily
- Better for very deep trees

**Cons:**
- More code, less intuitive
- Manual stack management
- Slightly more complex logic

### Morris Traversal
**Pros:**
- O(1) space complexity
- No recursion or stack
- Still O(n) time

**Cons:**
- Temporarily modifies tree (creates threads)
- More complex to understand
- Only for in-order and pre-order

## Practical Applications

### 1. Expression Trees
```php
// Infix: ((3 + 5) * 2)
// Tree traversals give different notations:
In-order:   3 + 5 * 2     (Infix)
Pre-order:  * + 3 5 2     (Prefix/Polish)
Post-order: 3 5 + 2 *     (Postfix/RPN)
```

### 2. File System
```php
Pre-order: Directory listing (directories first)
Post-order: Calculate disk usage (children first)
Level-order: Show by directory depth
```

### 3. Syntax Trees
```php
In-order: Source code representation
Post-order: Code generation
Pre-order: Parsing/compilation
```

## Common Patterns

### Pattern 1: Two-Pointer Technique
Used in finding middle, palindrome checking
```php
$slow = $fast = $root;
while ($fast !== null && $fast->right !== null) {
    $slow = $slow->right;
    $fast = $fast->right->right;
}
```

### Pattern 2: Path Tracking
Used in root-to-leaf problems
```php
function findPath($node, $currentPath, &$allPaths) {
    $currentPath[] = $node->data;
    if (isLeaf($node)) {
        $allPaths[] = $currentPath;
    }
    findPath($node->left, $currentPath, $allPaths);
    findPath($node->right, $currentPath, $allPaths);
}
```

### Pattern 3: Level Processing
Used in zigzag, level-wise operations
```php
while (!empty($queue)) {
    $levelSize = count($queue);
    for ($i = 0; $i < $levelSize; $i++) {
        // Process level
    }
}
```

## Testing

The example file demonstrates:
- All traversal types with same tree
- Comparison of outputs
- Recursive vs iterative approaches
- Space optimization with Morris
- Path finding algorithms
- Complexity analysis

## Common Mistakes

1. **Forgetting base case** in recursion
2. **Wrong order** of recursive calls
3. **Not handling null** nodes properly
4. **Stack overflow** on deep trees
5. **Incorrect queue** operations in level-order

## Requirements

- PHP 8.0+
- Chapter 18 (BST) for TreeNode class
- No external dependencies
