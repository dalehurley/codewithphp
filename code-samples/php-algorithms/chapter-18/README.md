# Chapter 18: Trees & Binary Search Trees - Code Samples

Complete implementation of Binary Search Trees (BST) with all major operations and practical applications.

## Files Overview

### 1. `01-binary-search-tree.php`
**Complete Binary Search Tree Implementation**

Full-featured BST implementation demonstrating:
- Insert, Search, Delete operations
- Find Min/Max
- Tree height and size calculations
- Range queries
- BST validation
- Tree visualization

**Key Features:**
- All operations with proper error handling
- Three deletion cases (leaf, one child, two children)
- Visual tree structure display
- In-order traversal (sorted output)

**Run:** `php 01-binary-search-tree.php`

## BST Properties

### Core Property
For any node N:
- All values in left subtree < N
- All values in right subtree > N
- Both subtrees are also BSTs

### Time Complexity

| Operation | Average | Worst Case | Notes |
|-----------|---------|------------|-------|
| Search | O(log n) | O(n) | Worst when tree becomes skewed (linked list) |
| Insert | O(log n) | O(n) | Same as search |
| Delete | O(log n) | O(n) | Find node + restructure |
| Min/Max | O(log n) | O(n) | Go to leftmost/rightmost |
| In-order | O(n) | O(n) | Visit all nodes |

### Space Complexity
- Tree storage: O(n)
- Recursive operations: O(h) for call stack (h = height)
- Iterative operations: O(1) extra space

## Key Concepts

### 1. Insertion
```
Start at root, compare value:
  - If smaller: go left
  - If larger: go right
  - If null: insert here
```

### 2. Deletion (Three Cases)
```
Case 1: No children (leaf)
  → Simply remove node

Case 2: One child
  → Replace node with its child

Case 3: Two children
  → Replace with inorder successor (min in right subtree)
  → Delete successor from its original position
```

### 3. Inorder Traversal
**Left → Root → Right**
- For BST, produces sorted output
- Most common traversal for BSTs

## When to Use BSTs

### ✅ Good For:
- Maintaining sorted data
- Range queries
- Finding min/max quickly
- Predecessor/successor queries
- Dynamic datasets with frequent insertions/deletions

### ❌ Not Good For:
- Random access by index (use arrays)
- Hash-based lookups (use hash tables)
- Guaranteed O(log n) (use balanced trees like AVL)
- Sequential data insertion (creates skewed tree)

## Common Pitfalls

### 1. Skewed Trees
Sequential insertion (1, 2, 3, 4, 5) creates linked list:
```
1
 \
  2
   \
    3
     \
      4
       \
        5
```
**Height:** O(n)
**Solution:** Use balanced trees (AVL, Red-Black) or shuffle before insertion

### 2. Incorrect Deletion
Forgetting to handle all three cases:
- Missing subtree assignment
- Not finding inorder successor correctly
- Not deleting successor after copying value

### 3. Stack Overflow
Deep recursion on skewed trees
**Solution:** Use iterative approach or limit tree height

## BST Applications

1. **Databases**: Index structures
2. **File Systems**: Directory hierarchies
3. **Autocomplete**: Word suggestions
4. **Expression Trees**: Mathematical expressions
5. **Decision Trees**: Machine learning
6. **Priority Queues**: With modifications

## Performance Tips

1. **Randomize insertion** order to avoid skewing
2. **Use balanced variants** (AVL, Red-Black) for guaranteed performance
3. **Consider iterative** approaches for very deep trees
4. **Cache frequently** accessed nodes
5. **Monitor tree height** - if approaching O(n), rebalance

## Testing

The example file includes comprehensive testing:
- Basic operations (insert, search, delete)
- All deletion cases
- Edge cases (empty tree, single node)
- Tree properties (height, size, validity)
- Range queries
- Visualization

## Further Reading

- Chapter 19: Tree Traversal Algorithms
- Chapter 20: Balanced Trees (AVL & Red-Black)
- See also: Binary heaps, B-trees

## Requirements

- PHP 8.0+
- No external dependencies
