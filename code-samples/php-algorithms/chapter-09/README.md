# Chapter 09: Comparing Sorting Algorithms

Comprehensive benchmark suite comparing all sorting algorithms.

## Code Files

### 01-sorting-benchmark.php
Side-by-side performance comparison of all sorting algorithms on different data patterns.

**Run:** `php 01-sorting-benchmark.php`

## Key Insights

- **Small arrays (< 50):** Insertion sort or PHP sort()
- **Medium/Large random:** Quick sort (fastest average case)
- **Need guaranteed O(n log n):** Merge sort or Heap sort
- **Nearly sorted:** Insertion sort (O(n) performance)
- **Production code:** PHP sort() (optimized hybrid algorithm)

## Quick Start
```bash
cd /home/user/codewithphp/code-samples/php-algorithms/chapter-09
php 01-sorting-benchmark.php
```
