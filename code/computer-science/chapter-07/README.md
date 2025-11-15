# Chapter 07: Sorting Algorithms - Code Examples

Complete, runnable code examples demonstrating sorting algorithms from Chapter 7.

## Quick Start

```bash
# Run any example
php 01-bubble-sort.php
php 04-merge-sort.php
php 08-sorting-comparison.php
# ... etc
```

## Examples Overview

### 01-bubble-sort.php
**Concepts**: O(n²) simple sorting with adjacent swaps

Demonstrates:
- Basic bubble sort implementation
- Early termination optimization
- Step-by-step visualization
- Stability verification
- Best/average/worst case performance
- Comparison with other sorts

**Run time**: ~1 second

---

### 02-selection-sort.php
**Concepts**: O(n²) sorting by selecting minimum

Demonstrates:
- Selection sort implementation
- Finding minimum in unsorted portion
- Swap count comparison with bubble sort
- Instability demonstration
- Input-independent performance
- Finding kth smallest element

**Run time**: ~1 second

---

### 03-insertion-sort.php
**Concepts**: O(n²) adaptive sorting, building sorted array

Demonstrates:
- Insertion sort implementation
- Binary insertion sort variant
- Adaptive behavior on nearly-sorted data
- Online sorting (sorting as data arrives)
- Stability verification
- Comparison with other O(n²) sorts

**Run time**: ~1 second

---

### 04-merge-sort.php
**Concepts**: O(n log n) divide-and-conquer sorting

Demonstrates:
- Recursive merge sort implementation
- Merging two sorted arrays
- Divide-and-conquer visualization
- Stable sorting guarantee
- External sorting concept
- Consistent performance across all inputs

**Run time**: ~2 seconds

---

### 05-quick-sort.php
**Concepts**: O(n log n) average, in-place sorting

Demonstrates:
- Lomuto and Hoare partition schemes
- Randomized quick sort
- Three-way partitioning for duplicates
- Pivot selection strategies
- Best/worst case scenarios
- Comparison with merge sort

**Run time**: ~2 seconds

---

### 06-heap-sort.php
**Concepts**: O(n log n) guaranteed, heap data structure

Demonstrates:
- Max heap construction
- Heapify operation
- Heap sort algorithm
- Finding top k elements
- Visualization of heap structure
- Unstable sorting example

**Run time**: ~2 seconds

---

### 07-counting-radix-bucket-sort.php
**Concepts**: Non-comparison sorts O(n+k), O(nk), O(n)

Demonstrates:
- Counting sort for small integer ranges
- Radix sort for multi-digit numbers
- Bucket sort for uniformly distributed data
- Stability in counting sort
- Performance comparison with O(n log n) sorts
- When to use non-comparison sorts

**Run time**: ~2 seconds

---

### 08-sorting-comparison.php
**Concepts**: Comprehensive performance comparison

Demonstrates:
- Algorithm summary table
- Small vs large dataset performance
- Different input types (sorted, random, reversed)
- Scaling behavior analysis
- Memory usage comparison
- Algorithm selection guide
- Real-world implementations

**Run time**: ~5 seconds (benchmarks)

---

### 09-sorting-applications.php
**Concepts**: Real-world sorting use cases

Demonstrates:
- Multi-key sorting (database-style)
- Top k elements (heap-based)
- Finding median
- Interval scheduling (greedy algorithm)
- Duplicate detection
- Merge sorted lists (like JOINs)
- Closest pair problem
- Search query optimization

**Run time**: ~1 second

---

### 10-advanced-sorting.php
**Concepts**: Hybrid algorithms and advanced techniques

Demonstrates:
- Intro Sort (Quick + Heap + Insertion)
- Tim Sort concept (Python's default)
- Custom comparison functions
- Stability considerations
- Partially sorted data optimization
- External sorting concept
- Parallel sorting ideas

**Run time**: ~2 seconds

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

1. **O(n²) Sorts (Simple but Educational)**
   - Start with `01-bubble-sort.php` - understand swapping concept
   - Then `02-selection-sort.php` - understand selection concept
   - Then `03-insertion-sort.php` - understand adaptive sorting

2. **O(n log n) Sorts (Efficient General-Purpose)**
   - Start with `04-merge-sort.php` - divide and conquer
   - Then `05-quick-sort.php` - partitioning and in-place sorting
   - Then `06-heap-sort.php` - heap data structure

3. **Specialized Sorts**
   - Study `07-counting-radix-bucket-sort.php` - when comparison isn't needed

4. **Practical Knowledge**
   - Run `08-sorting-comparison.php` - see all algorithms compared
   - Explore `09-sorting-applications.php` - real-world use cases
   - Master `10-advanced-sorting.php` - production-ready techniques

## Key Takeaways

After running these examples, you'll understand:

✅ **Algorithm Classification**
- Simple O(n²): Bubble, Selection, Insertion
- Efficient O(n log n): Merge, Quick, Heap
- Non-comparison O(n+k): Counting, Radix, Bucket

✅ **Critical Properties**
- **Stable**: Maintains order of equal elements (Merge, Insertion, Bubble, Counting, Radix)
- **In-place**: Minimal extra memory (Quick, Heap, Bubble, Selection, Insertion)
- **Adaptive**: Performs better on nearly-sorted data (Insertion, Bubble with optimization)

✅ **When to Use Each**
- **Bubble Sort**: Never in production (educational only)
- **Selection Sort**: Small datasets with expensive swaps
- **Insertion Sort**: Small datasets, nearly-sorted data, online sorting
- **Merge Sort**: Stability required, guaranteed O(n log n), linked lists
- **Quick Sort**: General purpose, in-place, average-case performance matters
- **Heap Sort**: Guaranteed O(n log n), in-place, memory limited
- **Counting Sort**: Small integer range (k ≈ n)
- **Radix Sort**: Fixed-length integers
- **Bucket Sort**: Uniformly distributed data

✅ **Real-World Insights**
- Production implementations use hybrid algorithms
- C++ `std::sort()`: Intro Sort (Quick + Heap + Insertion)
- Python `sorted()`: Tim Sort (Merge + Insertion + Run Detection)
- Java `Arrays.sort()`: Dual-Pivot Quick Sort + Tim Sort
- Choose algorithm based on data characteristics!

## Complexity Cheat Sheet

| Algorithm | Best | Average | Worst | Space | Stable | In-Place |
|-----------|------|---------|-------|-------|--------|----------|
| Bubble | O(n) | O(n²) | O(n²) | O(1) | Yes | Yes |
| Selection | O(n²) | O(n²) | O(n²) | O(1) | No | Yes |
| Insertion | O(n) | O(n²) | O(n²) | O(1) | Yes | Yes |
| Merge | O(n log n) | O(n log n) | O(n log n) | O(n) | Yes | No |
| Quick | O(n log n) | O(n log n) | O(n²) | O(log n) | No | Yes |
| Heap | O(n log n) | O(n log n) | O(n log n) | O(1) | No | Yes |
| Counting | O(n+k) | O(n+k) | O(n+k) | O(k) | Yes | No |
| Radix | O(nk) | O(nk) | O(nk) | O(n+k) | Yes | No |
| Bucket | O(n) | O(n) | O(n²) | O(n) | Yes* | No |

*Can be stable depending on bucket sort implementation

## Common Pitfalls

⚠️ **Using bubble/selection sort in production**: Always use O(n log n) sorts for large data. O(n²) algorithms are only acceptable for very small datasets (< 50 elements).

⚠️ **Ignoring stability**: When sorting on multiple keys, stability matters! First sort by secondary key, then by primary key (works only with stable sorts).

⚠️ **Quick sort on sorted data**: Without randomization, quick sort degrades to O(n²) on already-sorted data. Always use randomized pivot selection.

⚠️ **Merge sort memory usage**: Merge sort requires O(n) extra space. Use quick sort or heap sort when memory is limited.

⚠️ **Choosing wrong algorithm**: Small integer ranges? Use counting sort. Large datasets? Use quick/merge/heap sort. Nearly sorted? Use insertion sort.

## Performance Benchmarks

Based on examples in this chapter (random data):

**Small Dataset (100 elements):**
- All algorithms comparable (< 1 ms)
- Insertion sort often fastest
- O(n²) vs O(n log n) difference negligible

**Medium Dataset (1,000 elements):**
- Insertion sort: ~10-50 ms
- Quick sort: ~1-3 ms
- Merge sort: ~2-4 ms
- Heap sort: ~3-5 ms
- **Quick sort wins (in-place advantage)**

**Large Dataset (10,000 elements):**
- Insertion sort: ~1000+ ms
- Quick sort: ~10-20 ms
- Merge sort: ~20-30 ms
- Heap sort: ~30-40 ms
- **O(n log n) sorts dominate by 50-100x**

**Speedup from O(n²) to O(n log n):**
- 100 elements: ~2x
- 1,000 elements: ~10x
- 10,000 elements: ~100x
- **Grows with data size!**

## Interview Questions Covered

✅ **Implement Quick Sort**
- Solution: `05-quick-sort.php`
- Time: O(n log n) average, O(n²) worst
- Space: O(log n) recursion

✅ **Implement Merge Sort**
- Solution: `04-merge-sort.php`
- Time: O(n log n) guaranteed
- Space: O(n) for merging

✅ **Sort Colors** (Dutch National Flag)
- Pattern: Three-way partitioning
- Related: `05-quick-sort.php` (3-way partition)

✅ **Find Kth Largest Element**
- Pattern: Heap or Quick Select
- Solution: `06-heap-sort.php` (top k elements)

✅ **Merge Sorted Arrays**
- Pattern: Two-pointer merge
- Solution: `04-merge-sort.php` (merge function)

✅ **Sort List** (Linked List)
- Best: Merge sort (no random access needed)
- Solution: `04-merge-sort.php`

## Next Steps

- Practice sorting problems on **LeetCode**
- Implement **AVL trees** and **Red-Black trees** (self-balancing)
- Study **external sorting** for massive datasets
- Learn **parallel sorting** algorithms
- Explore **GPU-accelerated sorting**

## Further Reading

- [Sorting Algorithm Visualizations](https://www.toptal.com/developers/sorting-algorithms)
- [Tim Sort Explained](https://github.com/python/cpython/blob/main/Objects/listsort.txt)
- [Intro Sort - Musser's Paper](https://citeseerx.ist.psu.edu/viewdoc/summary?doi=10.1.1.14.5196)
- [Quick Sort Analysis - Sedgewick](https://algs4.cs.princeton.edu/23quicksort/)
- [Sorting in Practice](https://queue.acm.org/detail.cfm?id=2984631)

---

**Chapter 07 Complete!** 🎉

Ready to move on to [Chapter 08: Searching Algorithms](../../docs/series/computer-science/chapters/08-searching-algorithms.md).
