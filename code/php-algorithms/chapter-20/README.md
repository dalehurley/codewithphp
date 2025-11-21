# Chapter 20: Balanced Trees (AVL & Red-Black) - Code Samples

Self-balancing binary search trees that guarantee O(log n) operations through automatic rebalancing.

## Files Overview

### 1. `01-avl-tree.php`
**Complete AVL Tree Implementation**

AVL trees maintain strict balance through rotations:
- Height difference ≤ 1 for all nodes
- Four rotation cases (LL, RR, LR, RL)
- Automatic rebalancing after insert/delete
- Height and balance factor tracking

**Features:**
- Insert with automatic rebalancing
- Delete with rebalancing
- Search (guaranteed O(log n))
- Visual display with heights and balance factors
- Balance verification

**Run:** `php 01-avl-tree.php`

## Why Balanced Trees?

### The Problem
Regular BST can degrade to O(n) with sequential insertion:
```
Insert 1,2,3,4,5 into BST:

1              Height: 5
 \
  2            Search: O(5)
   \
    3
     \
      4
       \
        5
```

### The Solution
Self-balancing trees maintain O(log n) height:
```
Insert 1,2,3,4,5 into AVL:

    2           Height: 3
   / \
  1   4         Search: O(log 5) ≈ O(3)
     / \
    3   5
```

## AVL Trees

### Properties
1. **Balance Factor** = height(left) - height(right)
2. Valid balance factors: **-1, 0, 1**
3. Rebalance when |BF| > 1
4. Height bounded by **1.44 * log(n)**

### Four Rotation Cases

#### 1. Left-Left (LL) - Single Right Rotation
```
      z                   y
     /                   / \
    y        =>         x   z
   /
  x

Trigger: Insert in left subtree of left child
Solution: Rotate right at z
```

#### 2. Right-Right (RR) - Single Left Rotation
```
  z                       y
   \                     / \
    y      =>           z   x
     \
      x

Trigger: Insert in right subtree of right child
Solution: Rotate left at z
```

#### 3. Left-Right (LR) - Double Rotation
```
    z               z              x
   /               /              / \
  y       =>      x      =>      y   z
   \             /
    x           y

Trigger: Insert in right subtree of left child
Solution: Rotate left at y, then right at z
```

#### 4. Right-Left (RL) - Double Rotation
```
  z               z                x
   \               \              / \
    y      =>       x    =>      z   y
   /                 \
  x                   y

Trigger: Insert in left subtree of right child
Solution: Rotate right at y, then left at z
```

## Time Complexity Guarantees

### AVL Tree

| Operation | Time | Notes |
|-----------|------|-------|
| Search | **O(log n)** | Guaranteed, height ≤ 1.44 log(n) |
| Insert | **O(log n)** | Search + O(1) rotations (max 1) |
| Delete | **O(log n)** | Search + O(log n) rotations (rare) |
| Space | **O(n)** | Plus height integer per node |

### Comparison with Regular BST

| Scenario | BST | AVL |
|----------|-----|-----|
| Random inserts | O(log n) avg | O(log n) guaranteed |
| Sequential inserts | **O(n) worst** | O(log n) guaranteed |
| 1000 elements (worst) | Depth: 1000 | Depth: ~10 |
| Search speedup | - | ~100x faster |

## AVL vs Red-Black Trees

| Property | AVL | Red-Black |
|----------|-----|-----------|
| **Balance** | Strict (height diff ≤ 1) | Loose (black-height equality) |
| **Height** | ~1.44 log(n) | ~2 log(n) |
| **Insertions** | Slower (more rotations) | Faster (max 2 rotations) |
| **Deletions** | Slower | Faster (max 3 rotations) |
| **Searches** | Faster (better balanced) | Slightly slower |
| **Memory** | Height per node | Color bit per node |
| **Best for** | Read-heavy workloads | Write-heavy workloads |

## When to Use

### Use AVL When:
- Read-heavy workload (many searches, few updates)
- Need fastest possible searches
- Database indexes with mostly reads
- In-memory dictionaries
- Autocomplete systems

### Use Red-Black When:
- Write-heavy workload (many inserts/deletes)
- Need balanced inserts and searches
- System libraries (Linux kernel, C++ std::map)
- Memory allocators
- Associative arrays

### Use Regular BST When:
- Data arrives randomly (natural balance)
- Simplicity preferred over guarantees
- Small datasets where O(n) acceptable
- Quick prototyping

### Use Hash Table Instead When:
- Only need exact lookups (no range queries)
- Order doesn't matter
- O(1) average more important than O(log n) worst
- Memory abundant

## Performance Characteristics

### For 1,000,000 Elements:

**Sequential Insertion:**
- Regular BST: Depth 1,000,000 (becomes linked list)
- AVL Tree: Depth ~20 (guaranteed balanced)
- **Speedup**: 50,000x faster searches!

**Random Insertion:**
- Regular BST: Depth ~20-30 (naturally balanced)
- AVL Tree: Depth ~20 (still balanced)
- **Speedup**: Minimal, but guaranteed

### Operations Count:

**10,000 Inserts (sequential):**
- Regular BST: O(n²) = 50,000,000 comparisons
- AVL Tree: O(n log n) = 133,000 comparisons
- **Improvement**: 375x faster!

## Implementation Considerations

### Height Storage
AVL needs height per node:
```php
class AVLNode {
    public int $height = 1;  // Extra 4 bytes
}
```

### Rotation Overhead
- Insert: Maximum 1 rotation
- Delete: Maximum log(n) rotations (rare in practice)
- Each rotation: O(1) pointer updates

### Space Overhead
```
Regular BST: ~40 bytes/node (data + 2 pointers)
AVL Tree:    ~44 bytes/node (+ height integer)
Overhead:    10% more memory
```

## Common Pitfalls

### 1. Forgetting to Update Heights
```php
// BAD: Height not updated
private function rotateRight($y) {
    $x = $y->left;
    $y->left = $x->right;
    $x->right = $y;
    return $x;  // Heights are wrong!
}

// GOOD: Update heights
private function rotateRight($y) {
    $x = $y->left;
    $y->left = $x->right;
    $x->right = $y;
    $this->updateHeight($y);  // Fix y first
    $this->updateHeight($x);  // Then x
    return $x;
}
```

### 2. Wrong Rotation Selection
Must check:
- Which side is heavy (left or right)
- Which side of heavy child was inserted into

### 3. Not Rebalancing After Delete
Deletion can also cause imbalance - must check!

## Real-World Applications

### 1. Database Indexes
```php
// B+ trees (evolved from AVL concept)
- MySQL InnoDB uses variants
- PostgreSQL uses B-trees
- In-memory databases use AVL directly
```

### 2. In-Memory Structures
```php
// C++ std::map uses Red-Black trees
// Java TreeMap uses Red-Black trees
// Linux kernel scheduler uses Red-Black trees
```

### 3. Computational Geometry
```php
// Sweep line algorithms
// Range trees
// Interval trees
```

## Testing

The example file demonstrates:
- Sequential insertion (worst case for regular BST)
- Automatic rebalancing visualization
- Height comparison with regular BST
- All four rotation cases
- Delete with rebalancing
- Performance implications

## Further Optimizations

### 1. Cache Root Balance
Store balance factor instead of computing:
```php
class AVLNode {
    public int $balanceFactor = 0;
}
```

### 2. Parent Pointers
Makes some operations easier:
```php
class AVLNode {
    public ?AVLNode $parent = null;
}
```

### 3. Iterative Implementation
Avoid recursion for very deep trees:
- More complex code
- Explicit stack management
- No stack overflow risk

## Key Takeaways

1. **AVL guarantees O(log n)** for all operations
2. **Self-balancing** through rotations
3. **Four rotation cases**: LL, RR, LR, RL
4. **Height ≤ 1.44 log(n)** always
5. **Better than BST** for guaranteed performance
6. **Read-heavy workloads** benefit most
7. **10% memory overhead** is worth it
8. **Critical for databases** and system software

## Requirements

- PHP 8.0+
- Chapter 18 (for comparison with regular BST)
- No external dependencies

## Next Steps

- Study Red-Black trees for write-heavy scenarios
- Explore B-trees for disk-based storage
- Learn Splay trees for locality of reference
- Investigate 2-3 trees as teaching tool
