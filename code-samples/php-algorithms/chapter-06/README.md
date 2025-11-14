# Chapter 06: Insertion Sort & Merge Sort

This directory contains runnable PHP code samples demonstrating insertion sort and merge sort algorithms.

## Code Files

### 01-insertion-sort.php
**Purpose:** Complete insertion sort implementation with visualization and analysis

**Features:**
- Basic insertion sort algorithm
- Step-by-step visualization
- Range-based sorting (for hybrid algorithms)
- Edge cases (empty, single element, sorted, reversed, duplicates)
- Performance analysis on different data patterns

**Key Concepts:**
- O(n) best case for sorted data
- O(n²) average/worst case
- Stable, in-place sorting
- Excellent for small arrays (< 50 elements)
- Adaptive algorithm (performs well on nearly sorted data)

**Run:** `php 01-insertion-sort.php`

### 02-merge-sort.php
**Purpose:** Complete merge sort implementation with divide-and-conquer visualization

**Features:**
- Basic merge sort algorithm
- Detailed merge operation walkthrough
- Step-by-step recursion visualization
- Edge cases and stability testing
- Predictable performance analysis

**Key Concepts:**
- O(n log n) for all cases (guaranteed)
- Stable sorting
- Divide-and-conquer strategy
- Requires O(n) extra space
- Predictable and consistent performance

**Run:** `php 02-merge-sort.php`

### 03-insertion-sort-practical.php
**Purpose:** Real-world applications where insertion sort excels

**Features:**
- Sorting blog posts by timestamp (nearly sorted scenario)
- Online sorting (inserting into sorted array)
- Task prioritization with custom comparisons
- Multi-criteria sorting
- Performance comparison: nearly sorted vs random

**Key Concepts:**
- Nearly sorted data: ~O(n) performance
- Online algorithm (sort as data arrives)
- Custom comparator functions
- Practical use cases

**Run:** `php 03-insertion-sort-practical.php`

### 04-merge-sort-optimized.php
**Purpose:** Optimized merge sort with hybrid approach

**Features:**
- Insertion sort for small subarrays (reduces overhead)
- Skip merge optimization (when already sorted)
- Finding optimal threshold for switching algorithms
- Hybrid sort (adaptive algorithm selection)
- Comprehensive performance comparison

**Key Concepts:**
- Hybrid algorithms combine strengths
- Insertion sort cutoff (typically 15-30 elements)
- Early termination optimization
- Real-world performance improvements (10-20%)

**Run:** `php 04-merge-sort-optimized.php`

### 05-comparison-demo.php
**Purpose:** Direct head-to-head comparison of insertion vs merge sort

**Features:**
- Side-by-side performance comparison
- Multiple data patterns (random, sorted, reversed, nearly sorted)
- Comprehensive size analysis
- Decision matrix for choosing algorithm
- Characteristics comparison table

**Key Concepts:**
- No single "best" algorithm
- Context-dependent performance
- Understanding trade-offs
- When to use each algorithm

**Run:** `php 05-comparison-demo.php`

## Quick Start

Run all examples:
```bash
cd /home/user/codewithphp/code-samples/php-algorithms/chapter-06
php 01-insertion-sort.php
php 02-merge-sort.php
php 03-insertion-sort-practical.php
php 04-merge-sort-optimized.php
php 05-comparison-demo.php
```

## Key Takeaways

### Insertion Sort
- **Best for:** Small arrays (< 50), nearly sorted data
- **Time Complexity:** O(n) best, O(n²) average/worst
- **Space Complexity:** O(1)
- **Stable:** Yes
- **Adaptive:** Yes (fast on nearly sorted data)

### Merge Sort
- **Best for:** Large arrays, guaranteed O(n log n), stability required
- **Time Complexity:** O(n log n) all cases
- **Space Complexity:** O(n)
- **Stable:** Yes
- **Predictable:** Same performance regardless of input

### When to Use Each

**Insertion Sort:**
- ✓ Array size < 50 elements
- ✓ Data is nearly sorted
- ✓ Online sorting (sort as data arrives)
- ✓ Need stable sort with O(1) space
- ✓ Simplicity important

**Merge Sort:**
- ✓ Array size > 1000 elements
- ✓ Need guaranteed O(n log n)
- ✓ Stability required
- ✓ Sorting linked lists
- ✓ External sorting (data > memory)
- ✓ Predictable performance critical

**Hybrid Approach (Best of Both):**
- Use insertion sort for small subarrays (< 20)
- Use merge sort for large subarrays
- Check if nearly sorted → use insertion sort
- Typical improvement: 10-20%

## Performance Summary

| Data Pattern | Size | Insertion Sort | Merge Sort | Winner |
|--------------|------|----------------|------------|--------|
| Random | 10 | ~0.01 ms | ~0.02 ms | Insertion |
| Random | 100 | ~1.0 ms | ~0.25 ms | Merge |
| Random | 1000 | ~100 ms | ~3.2 ms | Merge |
| Sorted | 1000 | ~0.5 ms | ~3.2 ms | Insertion |
| Nearly Sorted | 1000 | ~0.6 ms | ~3.2 ms | Insertion |
| Reversed | 1000 | ~200 ms | ~3.2 ms | Merge |

## Related Chapters

- **Chapter 05:** Bubble Sort & Selection Sort
- **Chapter 07:** Quick Sort & Pivot Strategies
- **Chapter 08:** Heap Sort & Priority Queues
- **Chapter 09:** Comparing Sorting Algorithms

## Additional Resources

- [Chapter 06 Documentation](/docs/series/php-algorithms/chapters/06-insertion-sort-merge-sort.md)
- Time Complexity Analysis
- Space Complexity Trade-offs
- Stability and When It Matters
