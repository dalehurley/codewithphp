# Chapter 05: Trees and Binary Search Trees - Code Examples

Complete, runnable code examples demonstrating tree and BST concepts from Chapter 5.

## Quick Start

```bash
# Run any example
php 01-tree-node-basics.php
php 02-tree-traversals.php
php 03-bst-implementation.php
# ... etc
```

## Examples Overview

### 01-tree-node-basics.php
**Concepts**: TreeNode class, building trees, basic operations

Demonstrates:
- TreeNode implementation with constructor property promotion
- Building a simple binary tree manually
- Calculating height, counting nodes, counting leaves
- Finding values in a tree

**Run time**: ~1 second

---

### 02-tree-traversals.php
**Concepts**: All 4 tree traversal methods

Demonstrates:
- **Inorder** (Left → Root → Right) - Gives sorted sequence for BST
- **Preorder** (Root → Left → Right) - Root visited first
- **Postorder** (Left → Right → Root) - Root visited last
- **Level Order** (BFS) - Visits level by level
- Level-order with levels separated

**Run time**: ~1 second

---

### 03-bst-implementation.php
**Concepts**: Complete Binary Search Tree class

Demonstrates:
- Full BST implementation with insert, search, delete
- Finding min/max values
- Height calculation
- BST validation
- Inorder traversal for sorted output
- Handling all 3 delete cases (leaf, one child, two children)

**Run time**: ~1 second

---

### 04-tree-validation.php
**Concepts**: Validating tree properties

Demonstrates:
- Checking if a tree is a valid BST
- Checking if a tree is balanced
- Checking if a tree is symmetric
- Checking if a tree is complete
- Shows both valid and invalid examples

**Run time**: ~1 second

---

### 05-common-tree-problems.php
**Concepts**: Classic tree algorithm problems

Demonstrates:
- Maximum depth / Minimum depth
- Path sum (does a path with target sum exist?)
- Lowest common ancestor (LCA) for BST
- Invert a binary tree
- Diameter of tree (longest path)
- Sum of all nodes

**Run time**: ~1 second

---

### 06-build-bst-from-array.php
**Concepts**: Converting between arrays and balanced BSTs

Demonstrates:
- Building height-balanced BST from sorted array
- Comparing balanced vs unbalanced trees
- Height optimization (O(log n) vs O(n))
- Tree visualization
- Converting BST back to sorted array

**Run time**: ~1 second

---

### 07-bst-range-queries.php
**Concepts**: Range-based operations on BST

Demonstrates:
- Finding all values in range [low, high]
- Kth smallest element (1-indexed)
- Kth largest element
- Count nodes in range
- Find closest value to target
- Efficient pruning of search space

**Run time**: ~1 second

---

### 08-serialize-deserialize.php
**Concepts**: Converting trees to/from strings

Demonstrates:
- Preorder serialization format
- Level-order serialization (more compact)
- Deserializing strings back to trees
- Handling edge cases (null tree, single node)
- Alternative JSON format

**Run time**: ~1 second

---

### 09-tree-visualization.php
**Concepts**: Pretty-printing trees in console

Demonstrates:
- Vertical format with connecting lines
- Horizontal format (rotated 90°)
- Level-by-level display
- Simple ASCII art representation
- Works with trees of any size

**Run time**: ~1 second

---

### 10-performance-comparison.php
**Concepts**: BST vs Array vs Hash Table performance

Demonstrates:
- Time benchmarks for insert, search, delete
- Memory usage comparison
- Performance at different scales (100, 1000, 10000 elements)
- When to use each data structure
- Impact of tree balance on performance

**Run time**: ~5-10 seconds (runs benchmarks)

---

## Running All Examples

```bash
# Run all examples in sequence
for i in {01..10}; do
    echo "=== Running example $i ==="
    php $(ls ${i}*.php)
    echo ""
done
```

## Dependencies

- PHP 8.2+ (uses constructor property promotion, typed properties)
- No external dependencies required

## Learning Path

**Recommended order:**
1. Start with `01-tree-node-basics.php` to understand TreeNode structure
2. Learn traversals with `02-tree-traversals.php`
3. Study full BST implementation in `03-bst-implementation.php`
4. Practice validation techniques in `04-tree-validation.php`
5. Solve common problems with `05-common-tree-problems.php`
6. Understand balancing with `06-build-bst-from-array.php`
7. Master range queries with `07-bst-range-queries.php`
8. Learn serialization with `08-serialize-deserialize.php`
9. Visualize trees with `09-tree-visualization.php`
10. Compare data structures with `10-performance-comparison.php`

## Key Takeaways

After running these examples, you'll understand:

✅ **Tree Fundamentals**
- TreeNode structure and relationships
- Binary tree vs binary search tree
- Height, depth, and tree properties

✅ **Traversal Algorithms**
- All 4 traversal methods and their use cases
- Recursion vs iteration for tree traversal
- Level-order (BFS) vs depth-first (DFS)

✅ **BST Operations**
- Insert: O(h) average, maintains BST property
- Search: O(h) average, faster than linear
- Delete: O(h) average, 3 cases (leaf, one child, two children)

✅ **Advanced Techniques**
- Tree validation (BST property, balance, symmetry)
- Range queries and kth element problems
- Serialization/deserialization for storage
- Performance comparison vs other structures

✅ **Practical Insights**
- Balanced trees are critical for O(log n) performance
- BST best for sorted order + fast operations
- Hash tables faster for lookups but no ordering
- Tree visualization aids debugging

## Complexity Cheat Sheet

| Operation | BST (Balanced) | BST (Skewed) | Sorted Array | Hash Table |
|-----------|----------------|--------------|--------------|------------|
| Search | O(log n) | O(n) | O(log n) | O(1) avg |
| Insert | O(log n) | O(n) | O(n) | O(1) avg |
| Delete | O(log n) | O(n) | O(n) | O(1) avg |
| Min/Max | O(log n) | O(n) | O(1) | N/A |
| Range | O(k + log n) | O(n) | O(k + log n) | O(n) |
| Sorted | O(n) inorder | O(n) inorder | O(n) iterate | N/A |

## Common Pitfalls

⚠️ **Unbalanced trees**: Always check tree height in production. Use AVL or Red-Black trees for guaranteed O(log n).

⚠️ **Null checks**: Always validate node is not null before accessing properties.

⚠️ **Duplicate values**: Standard BST doesn't handle duplicates. Decide strategy (ignore, allow in one subtree, or use count field).

⚠️ **Memory leaks**: PHP handles garbage collection, but be aware of circular references in complex tree structures.

## Next Steps

- Study **AVL Trees** and **Red-Black Trees** for self-balancing
- Implement **B-Trees** for database-style operations
- Learn **Tries** for prefix-based searches
- Practice on **LeetCode** tree problems

## Further Reading

- [Binary Search Tree - Wikipedia](https://en.wikipedia.org/wiki/Binary_search_tree)
- [Tree Traversal - Wikipedia](https://en.wikipedia.org/wiki/Tree_traversal)
- [AVL Tree Tutorial](https://www.geeksforgeeks.org/avl-tree-set-1-insertion/)
- [Red-Black Tree Tutorial](https://www.geeksforgeeks.org/red-black-tree-set-1-introduction-2/)

---

**Chapter 05 Complete!** 🎉

Ready to move on to [Chapter 06: Hash Tables](../../docs/series/computer-science/chapters/06-hash-tables.md).
