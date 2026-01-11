---
title: "09: Comparing Sorting Algorithms"
description: "Benchmark all sorting algorithms against each other. Learn which to use in different scenarios"
series: "php-algorithms"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites:
  - "/series/php-algorithms/chapters/05-bubble-sort-selection-sort"
  - "/series/php-algorithms/chapters/06-insertion-sort-merge-sort"
  - "/series/php-algorithms/chapters/07-quick-sort-pivot-strategies"
  - "/series/php-algorithms/chapters/08-heap-sort-priority-queues"
estimatedTime: "PT50M"
teaches:
  - 'Benchmark all six sorting algorithms across various dataset types and sizes'
  - 'Understand time/space complexity trade-offs for each sorting algorithm'
  - 'Learn which algorithm to choose based on data characteristics (size, order, duplicates)'
  - 'Master the concept of stable vs unstable sorting and when stability matters'
  - 'Create decision charts and selection guidelines for real-world sorting scenarios'
---
![09: Comparing Sorting Algorithms](/images/php-algorithms/chapter-09-comparing-sorts-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 09</span>
</div>

# Comparing Sorting Algorithms <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

We've learned six sorting algorithms: bubble sort, selection sort, insertion sort, merge sort, quick sort, and heap sort. Each has unique strengths and weaknesses that make it ideal for specific scenarios. In this chapter, we'll conduct comprehensive benchmarks, analyze performance across different data patterns, and build decision frameworks for algorithm selection.

You'll discover that there's no single "best" sorting algorithm—the optimal choice depends on your data size, characteristics (sorted, random, duplicates), memory constraints, and whether you need stability or guaranteed performance. By understanding these trade-offs, you'll make informed decisions that can dramatically improve your application's performance.

We'll benchmark all six algorithms against various datasets (random, sorted, nearly-sorted, and duplicate-heavy), compare their real-world performance, explore hybrid approaches used in production systems like Python's Timsort and C++'s Introsort, and create practical decision trees for algorithm selection.

## Objectives

**Estimated time:** 50 minutes

By the end of this chapter, you will:

- Benchmark all six sorting algorithms across various dataset types and sizes
- Understand time/space complexity trade-offs for each sorting algorithm
- Learn which algorithm to choose based on data characteristics (size, order, duplicates)
- Master the concept of stable vs unstable sorting and when stability matters
- Create decision charts and selection guidelines for real-world sorting scenarios

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and working
- Completion of [Chapter 05: Bubble Sort & Selection Sort](/series/php-algorithms/chapters/05-bubble-sort-selection-sort)
- Completion of [Chapter 06: Insertion Sort & Merge Sort](/series/php-algorithms/chapters/06-insertion-sort-merge-sort)
- Completion of [Chapter 07: Quick Sort & Pivot Strategies](/series/php-algorithms/chapters/07-quick-sort-pivot-strategies)
- Completion of [Chapter 08: Heap Sort & Priority Queues](/series/php-algorithms/chapters/08-heap-sort-priority-queues)
- Understanding of Big O notation from [Chapter 01](/series/php-algorithms/chapters/01-algorithm-analysis-big-o)
- **Estimated Time**: ~50 minutes

## Quick Checklist

Complete these hands-on tasks as you work through the chapter:

- [ ] Create comprehensive benchmark suite testing all six algorithms
- [ ] Test performance on: random, sorted, reverse-sorted, and nearly-sorted data
- [ ] Compare memory usage (in-place vs additional space requirements)
- [ ] Verify stability by sorting records with duplicate keys
- [ ] Create performance comparison charts for different input sizes
- [ ] Build a decision tree for algorithm selection based on constraints
- [ ] (Optional) Test algorithms with real-world data (database records, file lists)

## Quick Reference Table

::: info Understanding the Table
This reference table summarizes the performance characteristics of all six sorting algorithms. Use it as a quick guide when choosing an algorithm for your specific use case.
:::

| Algorithm | Best | Average | Worst | Space | Stable | In-Place | Cache | Adaptive |
|-----------|------|---------|-------|-------|--------|----------|-------|----------|
| **Bubble Sort** | O(n) | O(n²) | O(n²) | O(1) | ✅ Yes | ✅ Yes | ✅ Good | ✅ Yes |
| **Selection Sort** | O(n²) | O(n²) | O(n²) | O(1) | ❌ No | ✅ Yes | ✅ Good | ❌ No |
| **Insertion Sort** | O(n) | O(n²) | O(n²) | O(1) | ✅ Yes | ✅ Yes | ✅ Excellent | ✅ Yes |
| **Merge Sort** | O(n log n) | O(n log n) | O(n log n) | O(n) | ✅ Yes | ❌ No | ⚠️ Good | ❌ No |
| **Quick Sort** | O(n log n) | O(n log n) | O(n²)* | O(log n) | ❌ No | ✅ Yes | ✅ Excellent | ⚠️ Can be |
| **3-Way Quick Sort** | O(n)** | O(n log k)*** | O(n²)* | O(log n) | ❌ No | ✅ Yes | ✅ Excellent | ✅ Yes |
| **Heap Sort** | O(n log n) | O(n log n) | O(n log n) | O(1) | ❌ No | ✅ Yes | ❌ Poor | ❌ No |

*With good pivot selection, worst case is extremely rare  
**When all elements are equal  
***Where k = number of distinct elements

### Visual Comparison Chart

**Sorting 10,000 Random Elements (Time in milliseconds)**

```
Quick Sort    ████░░░░░░░░░░░░░░░░  8ms    (Fastest)
Quick (Opt)   ███░░░░░░░░░░░░░░░░░  5ms    (With optimizations)
Merge Sort    ███████░░░░░░░░░░░░░  15ms
Heap Sort     ████████░░░░░░░░░░░░  18ms
Insertion     █████████████████████ 2500ms (O(n²) - way too slow!)
Selection     █████████████████████ 3000ms (O(n²) - way too slow!)
Bubble Sort   █████████████████████ 3500ms (O(n²) - way too slow!)
```

**Sorting 10,000 Nearly Sorted Elements**

```
Insertion     █░░░░░░░░░░░░░░░░░░░  2ms    (O(n) - Fastest!)
Quick (Opt)   ███░░░░░░░░░░░░░░░░░  6ms
Quick Sort    █████░░░░░░░░░░░░░░░  10ms
Merge Sort    ███████░░░░░░░░░░░░░  15ms
Heap Sort     ████████░░░░░░░░░░░░  18ms
Selection     ████████████████░░░░  3000ms
Bubble Sort   ████████░░░░░░░░░░░░  1500ms (Early termination)
```

## Detailed Comparison

### Bubble Sort

```php
# filename: bubble-sort-recap.php
<?php

declare(strict_types=1);

function bubbleSort(array $arr): array
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swapped = true;
            }
        }
        // Early termination for nearly sorted arrays
        if (!$swapped) break;
    }
    return $arr;
}
```

**Pros:**
- Simple to implement
- O(n) best case for nearly sorted data
- Stable sorting
- In-place (O(1) space)

**Cons:**
- O(n²) average and worst case
- Slow for large datasets
- Many unnecessary comparisons

**Use when:**
- Data is nearly sorted
- Array is very small (< 10 elements)
- Simplicity is more important than speed
- Educational purposes

### Selection Sort

```php
# filename: selection-sort-recap.php
<?php

declare(strict_types=1);

function selectionSort(array $arr): array
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;
        // Find minimum element in unsorted portion
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }
        // Swap only if needed (minimizes writes)
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
        }
    }
    return $arr;
}
```

**Pros:**
- Simple to implement
- Minimizes number of swaps
- In-place (O(1) space)
- Good when writes are expensive

**Cons:**
- Always O(n²), even for sorted data
- Not stable
- Poor performance on large datasets

**Use when:**
- Write operations are expensive (flash memory)
- Finding smallest/largest K elements
- Memory writes are costly

### Insertion Sort

```php
# filename: insertion-sort-recap.php
<?php

declare(strict_types=1);

function insertionSort(array $arr): array
{
    $n = count($arr);
    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;
        // Shift elements greater than key to the right
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $key;
    }
    return $arr;
}
```

**Pros:**
- O(n) best case for nearly sorted data
- Stable sorting
- In-place (O(1) space)
- Excellent for small arrays
- Online algorithm (can sort as data arrives)

**Cons:**
- O(n²) average and worst case
- Slow for large datasets

**Use when:**
- Data is nearly sorted
- Array is small (< 50 elements)
- Sorting as data arrives
- Stability is required

### Merge Sort

```php
# filename: merge-sort-recap.php
<?php

declare(strict_types=1);

function mergeSort(array $arr): array
{
    if (count($arr) <= 1) return $arr;

    // Divide array into two halves
    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

function merge(array $left, array $right): array
{
    $result = [];
    $i = $j = 0;

    // Merge two sorted arrays
    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }

    // Append remaining elements
    return array_merge($result, array_slice($left, $i), array_slice($right, $j));
}
```

**Pros:**
- Guaranteed O(n log n)
- Stable sorting
- Predictable performance
- Good for external sorting
- Parallelizable

**Cons:**
- O(n) extra space
- Not in-place
- Slower than quick sort in practice

**Use when:**
- Need guaranteed O(n log n)
- Stability is required
- Sorting linked lists
- External sorting (data doesn't fit in memory)
- Parallel sorting

### Quick Sort

```php
# filename: quick-sort-recap.php
<?php

declare(strict_types=1);

function quickSort(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pi = partition($arr, $low, $high);
        // Recursively sort left and right partitions
        quickSort($arr, $low, $pi - 1);
        quickSort($arr, $pi + 1, $high);
    }
}

function partition(array &$arr, int $low, int $high): int
{
    $pivot = $arr[$high];
    $i = $low - 1;

    // Place elements smaller than pivot to the left
    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    // Place pivot in correct position
    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    return $i + 1;
}
```

**Pros:**
- Very fast in practice
- O(log n) space (in-place)
- Excellent cache locality
- Good average case O(n log n)

**Cons:**
- O(n²) worst case (rare with good pivot)
- Not stable
- Unpredictable performance

**Use when:**
- General-purpose sorting
- Average case performance matters
- Memory is limited
- In-place sorting needed
- **Most common choice for general sorting**

### 3-Way Quick Sort (For Many Duplicates)

::: tip Optimization for Duplicate Values
3-way partitioning is a powerful optimization when your data contains many duplicate values. Instead of partitioning into two groups (< pivot and ≥ pivot), it creates three groups: less than, equal to, and greater than the pivot. This means duplicate elements are already in their final position and don't need further sorting.
:::

**When to use**: Data with many duplicate values (categories, statuses, grades, enum values)

```php
# filename: three-way-quick-sort.php
<?php

declare(strict_types=1);

function threeWayQuickSort(array &$arr, int $low, int $high): void
{
    if ($low >= $high) return;
    
    // Partition into three parts: < pivot, = pivot, > pivot
    [$lt, $gt] = threeWayPartition($arr, $low, $high);
    
    // Recursively sort elements less than and greater than pivot
    // Elements equal to pivot are already in correct position!
    threeWayQuickSort($arr, $low, $lt - 1);
    threeWayQuickSort($arr, $gt + 1, $high);
}

function threeWayPartition(array &$arr, int $low, int $high): array
{
    $pivot = $arr[$low];
    $lt = $low;      // Elements < pivot are in [low, lt-1]
    $gt = $high;     // Elements > pivot are in [gt+1, high]
    $i = $low + 1;   // Elements = pivot are in [lt, i-1]
    
    while ($i <= $gt) {
        if ($arr[$i] < $pivot) {
            // Element smaller than pivot, swap to left section
            [$arr[$lt], $arr[$i]] = [$arr[$i], $arr[$lt]];
            $lt++;
            $i++;
        } elseif ($arr[$i] > $pivot) {
            // Element greater than pivot, swap to right section
            [$arr[$i], $arr[$gt]] = [$arr[$gt], $arr[$i]];
            $gt--;
            // Don't increment $i - need to check swapped element
        } else {
            // Element equals pivot, it's in correct position
            $i++;
        }
    }
    
    return [$lt, $gt];
}

// Example usage
$grades = ['B', 'A', 'C', 'A', 'B', 'C', 'A', 'B', 'C', 'A'];
threeWayQuickSort($grades, 0, count($grades) - 1);
print_r($grades);
// Output: ['A', 'A', 'A', 'A', 'B', 'B', 'B', 'C', 'C', 'C']
```

**Pros:**
- Extremely fast for data with many duplicates
- O(n) best case when all elements equal
- O(n log k) where k = distinct elements
- In-place sorting (O(log n) stack space)

**Cons:**
- More complex than standard quick sort
- Slightly slower than 2-way partition when all elements unique
- Still O(n²) worst case with bad pivot selection

**Performance with duplicates:**

```php
// Array with only 10 unique values among 10,000 elements
$data = [];
for ($i = 0; $i < 10000; $i++) {
    $data[] = rand(1, 10);
}

// Standard Quick Sort: O(n log n) - compares many duplicates unnecessarily
// 3-Way Quick Sort:   O(n log 10) ≈ O(n) - groups duplicates efficiently

// Benchmark result:
// Standard: ~8ms
// 3-Way:    ~3ms (2.6x faster!)
```

**Use when:**
- Sorting by category, status, or enum values
- Data with low cardinality (few unique values)
- User roles, priority levels, or grade distributions
- Any dataset where duplicates are common (> 20% duplicate rate)

### Heap Sort

```php
# filename: heap-sort-recap.php
<?php

declare(strict_types=1);

function heapSort(array $arr): array
{
    $n = count($arr);

    // Build max heap from array
    for ($i = (int)(($n / 2) - 1); $i >= 0; $i--) {
        heapify($arr, $i, $n);
    }

    // Extract elements from heap one by one
    for ($i = $n - 1; $i > 0; $i--) {
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];
        heapify($arr, 0, $i);
    }

    return $arr;
}

function heapify(array &$arr, int $i, int $size): void
{
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    // Find largest among root, left child, and right child
    if ($left < $size && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    if ($right < $size && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

    // If largest is not root, swap and continue heapifying
    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapify($arr, $largest, $size);
    }
}
```

**Pros:**
- Guaranteed O(n log n)
- In-place (O(1) space)
- Predictable performance
- Good for priority queues

**Cons:**
- Not stable
- Poor cache locality
- Slower than quick sort in practice

**Use when:**
- Need guaranteed O(n log n)
- Memory is very limited
- Finding top K elements
- Don't need stability

## Comprehensive Benchmark

Let's benchmark all algorithms on different data patterns:

::: tip Performance Testing
The following benchmark suite tests all six sorting algorithms against five different data patterns. This helps identify which algorithm performs best for your specific use case.
:::

```php
# filename: comprehensive-benchmark.php
<?php

declare(strict_types=1);

require_once 'Benchmark.php';

class SortingBenchmark
{
    private Benchmark $bench;

    public function __construct()
    {
        $this->bench = new Benchmark();
    }

    public function compareAll(int $size): void
    {
        echo "═══════════════════════════════════════════\n";
        echo "Array Size: $size\n";
        echo "═══════════════════════════════════════════\n\n";

        $this->testPattern('Random', $this->generateRandom($size));
        $this->testPattern('Sorted', $this->generateSorted($size));
        $this->testPattern('Reverse Sorted', $this->generateReverse($size));
        $this->testPattern('Nearly Sorted', $this->generateNearlySorted($size));
        $this->testPattern('Many Duplicates', $this->generateDuplicates($size));
    }

    private function testPattern(string $name, array $data): void
    {
        echo "Pattern: $name\n";
        echo "─────────────────────────────────────────\n";

        $this->bench->compare([
            'Bubble Sort' => fn($arr) => bubbleSort($arr),
            'Selection Sort' => fn($arr) => selectionSort($arr),
            'Insertion Sort' => fn($arr) => insertionSort($arr),
            'Merge Sort' => fn($arr) => mergeSort($arr),
            'Quick Sort' => function($arr) {
                quickSort($arr, 0, count($arr) - 1);
                return $arr;
            },
            'Heap Sort' => fn($arr) => heapSort($arr),
            'PHP sort()' => function($arr) {
                sort($arr);
                return $arr;
            },
        ], $data, iterations: 10);

        echo "\n";
    }

    private function generateRandom(int $size): array
    {
        $arr = range(1, $size);
        shuffle($arr);
        return $arr;
    }

    private function generateSorted(int $size): array
    {
        return range(1, $size);
    }

    private function generateReverse(int $size): array
    {
        return range($size, 1);
    }

    private function generateNearlySorted(int $size): array
    {
        $arr = range(1, $size);
        // Swap 5% of elements
        $swaps = (int)($size * 0.05);
        for ($i = 0; $i < $swaps; $i++) {
            $a = rand(0, $size - 1);
            $b = rand(0, $size - 1);
            [$arr[$a], $arr[$b]] = [$arr[$b], $arr[$a]];
        }
        return $arr;
    }

    private function generateDuplicates(int $size): array
    {
        $arr = [];
        for ($i = 0; $i < $size; $i++) {
            $arr[] = rand(1, 10); // Only 10 unique values
        }
        return $arr;
    }
}

// Run benchmarks
$benchmark = new SortingBenchmark();
$benchmark->compareAll(1000);
$benchmark->compareAll(5000);
```

## Expected Results Analysis

### Small Arrays (< 50 elements)

**Winner: Insertion Sort** 🏆
- **Why:** Low overhead, simple operations
- **Performance:** O(n) on nearly sorted, O(n²) worst case
- **Best for:** Arrays with 10-50 elements
- **Real timing:** 0.01-0.25ms for 50 elements

**Comparison:**
```php
// Array size: 20 elements
Insertion Sort:  0.05ms  ← Winner!
Quick Sort:      0.08ms  (overhead)
Merge Sort:      0.10ms  (recursion overhead)
PHP sort():      0.03ms  (highly optimized)
```

### Medium Arrays (50-10,000)

**Winner: Quick Sort** (with optimizations) 🏆
- **Why:** Excellent cache locality, fast partitioning
- **Performance:** O(n log n) average case
- **Best for:** General-purpose sorting
- **Real timing:** 0.5-10ms for 1,000-10,000 elements

**Comparison:**
```php
// Array size: 5,000 elements
Quick Sort (opt): 3ms      ← Winner!
Quick Sort:       5ms
Merge Sort:       9ms
Heap Sort:        12ms
Insertion Sort:   125ms    (O(n²) too slow)
```

### Large Arrays (> 10,000)

**Winner: Quick Sort (optimized)** 🏆
- **Why:** Cache efficiency, in-place, fewer allocations
- **Performance:** O(n log n) with low constants
- **Best for:** Large random datasets
- **Real timing:** 100ms for 100,000 elements

**Comparison:**
```php
// Array size: 100,000 elements
Quick Sort (opt): 62ms     ← Winner!
Quick Sort:       95ms
Merge Sort:       180ms    (memory allocations)
Heap Sort:        250ms    (cache misses)
Insertion Sort:   ~10 min  (Don't even try!)
```

### Nearly Sorted Data

**Winner: Insertion Sort** (small) or **Adaptive Quick Sort** (large) 🏆

**Small arrays (< 1,000):**
```php
// Array size: 500, 95% sorted
Insertion Sort:  0.15ms   ← Winner! O(n) performance
Quick Sort:      0.40ms
Merge Sort:      1.5ms
Heap Sort:       12ms
```

**Large arrays (> 1,000):**
```php
// Array size: 10,000, 95% sorted
Quick Sort (opt): 6ms     ← Winner! (with insertion for small subarrays)
Insertion Sort:   15ms    (still O(n) but worse constants)
Merge Sort:       15ms
Heap Sort:        18ms
```

### Many Duplicates

**Winner: 3-Way Quick Sort** 🏆
- **Why:** Groups equal elements efficiently
- **Performance:** O(n log k) where k = distinct elements
- **Best case:** O(n) when all elements equal

**Comparison:**
```php
// Array size: 10,000, only 10 unique values
3-Way Quick Sort: 3ms     ← Winner!
Quick Sort:       8ms     (wastes time on duplicates)
Merge Sort:       15ms
Heap Sort:        18ms
Insertion Sort:   2500ms
```

### Need Guaranteed O(n log n)

::: tip Critical Applications
For real-time systems, embedded devices, or safety-critical applications where worst-case performance matters more than average performance, always choose algorithms with guaranteed O(n log n) complexity.
:::

**Winner:**
- **Merge Sort** (if have O(n) memory) 🏆
- **Heap Sort** (if limited memory) 🏆

**Comparison:**
```php
// Array size: 10,000, worst-case scenario
Merge Sort:       15ms    ← Fastest O(n log n) guaranteed
Heap Sort:        18ms    ← Best if memory limited (O(1))
Quick Sort:       8ms     (usually) or 5000ms (worst case!) ⚠️
```

### Sorted or Reverse Sorted Data

**Winner: Insertion Sort** (small) or **Merge Sort / Heap Sort** (large) 🏆

**Why Quick Sort Fails:**
```php
// Already sorted [1,2,3,4,5] with bad pivot
Quick Sort (first pivot): 5000ms  ← O(n²) disaster!
Quick Sort (random):      8ms     ← Random pivot saves it
Quick Sort (median-3):    8ms     ← Median-of-three works

// Safe choices:
Insertion Sort (small):   0.5ms   ← O(n) for sorted!
Merge Sort:               15ms    ← Guaranteed
Heap Sort:                18ms    ← Guaranteed
```

### Stability Required

::: info What is Stability?
A sorting algorithm is **stable** if it preserves the relative order of elements with equal keys. This is critical when sorting by multiple fields (e.g., sort by date, then by priority) or when the original order has meaning.
:::

**Winner: Merge Sort** 🏆
- Only O(n log n) stable algorithm
- Essential for multi-field sorting
- Preserves order of equal elements

**When stability matters:**

Let's see a complete real-world example showing why stability is crucial:

```php
# filename: stability-real-world-example.php
<?php

declare(strict_types=1);

class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $rating,
        public int $originalPosition
    ) {}
    
    public function __toString(): string
    {
        return sprintf(
            "%-15s $%-6.2f %d★ (pos: %d)",
            $this->name,
            $this->price,
            $this->rating,
            $this->originalPosition
        );
    }
}

// Products already sorted by rating (5★ → 3★)
// Now we want to sort by price while keeping rating order
$products = [
    new Product('Phone A', 500, 5, 0),
    new Product('Phone B', 500, 5, 1),    // Same price as Phone A
    new Product('Laptop C', 1000, 4, 2),
    new Product('Tablet D', 500, 4, 3),   // Same price as Phone A/B
    new Product('Monitor E', 300, 3, 4),
];

echo "ORIGINAL (sorted by rating):\n";
foreach ($products as $p) echo "$p\n";

// Sort by price using STABLE sort (merge sort via usort)
echo "\n STABLE SORT by price (merge sort):\n";
$stable = $products;
usort($stable, fn($a, $b) => $a->price <=> $b->price);
foreach ($stable as $p) echo "$p\n";
// Result: Among $500 items, maintains rating order!
// Monitor E ($300, 3★, pos: 4)
// Phone A   ($500, 5★, pos: 0) ← Still before Phone B
// Phone B   ($500, 5★, pos: 1) ← Still before Tablet D
// Tablet D  ($500, 4★, pos: 3)
// Laptop C  ($1000, 4★, pos: 2)

// Simulate UNSTABLE sort (hypothetical - order unpredictable)
echo "\n UNSTABLE SORT by price (quick sort might do this):\n";
echo "Monitor E       $300.00 3★ (pos: 4)\n";
echo "Tablet D        $500.00 4★ (pos: 3) ← Lost rating order!\n";
echo "Phone B         $500.00 5★ (pos: 1) ← Swapped with Phone A\n";
echo "Phone A         $500.00 5★ (pos: 0) ← Original order lost\n";
echo "Laptop C        $1000.00 4★ (pos: 2)\n";

// Why this matters for e-commerce
echo "\n WHY STABILITY MATTERS:\n";
echo "- User sorted by rating, then by price\n";
echo "- STABLE: Higher-rated items appear first within same price\n";
echo "- UNSTABLE: Rating order is lost, worse items may appear first\n";
echo "- User experience: Stable sort = predictable results\n";
```

**Expected Output:**
```
ORIGINAL (sorted by rating):
Phone A         $500.00 5★ (pos: 0)
Phone B         $500.00 5★ (pos: 1)
Laptop C        $1000.00 4★ (pos: 2)
Tablet D        $500.00 4★ (pos: 3)
Monitor E       $300.00 3★ (pos: 4)

STABLE SORT by price (merge sort):
Monitor E       $300.00 3★ (pos: 4)
Phone A         $500.00 5★ (pos: 0) ← Maintains order!
Phone B         $500.00 5★ (pos: 1)
Tablet D        $500.00 4★ (pos: 3)
Laptop C        $1000.00 4★ (pos: 2)

WHY STABILITY MATTERS:
- Among $500 items, Phone A/B (5★) appear before Tablet D (4★)
- User gets best-rated items first within same price range
- Predictable, intuitive sorting behavior
```

**Real-world scenarios requiring stability:**

1. **E-commerce**: Sort by price after sorting by rating/popularity
2. **Task management**: Sort by priority, then by due date
3. **Leaderboards**: Sort by score, preserving registration order for ties
4. **Database records**: Maintain insertion order when sorting by secondary keys
5. **Log processing**: Sort by timestamp while preserving order of simultaneous events

## Hybrid Approaches

Real-world sorting often uses hybrid algorithms:

::: tip Production Sorting
Modern programming languages rarely use a single sorting algorithm. Instead, they combine multiple algorithms to leverage the strengths of each. Python uses Timsort, C++ uses Introsort, and PHP uses a variant similar to these hybrid approaches.
:::

### Timsort (Python's default)

Combines merge sort and insertion sort:

```php
function timSort(array &$arr): void
{
    $minRun = 32;
    $n = count($arr);

    // Sort individual runs using insertion sort
    for ($start = 0; $start < $n; $start += $minRun) {
        $end = min($start + $minRun - 1, $n - 1);
        insertionSortRange($arr, $start, $end);
    }

    // Merge sorted runs
    $size = $minRun;
    while ($size < $n) {
        for ($start = 0; $start < $n; $start += 2 * $size) {
            $mid = $start + $size - 1;
            $end = min($start + 2 * $size - 1, $n - 1);

            if ($mid < $end) {
                $left = array_slice($arr, $start, $mid - $start + 1);
                $right = array_slice($arr, $mid + 1, $end - $mid);
                $merged = merge($left, $right);

                array_splice($arr, $start, $end - $start + 1, $merged);
            }
        }
        $size *= 2;
    }
}
```

### Introsort (C++'s std::sort)

Combines quick sort, heap sort, and insertion sort:

```php
function introSort(array &$arr, int $low, int $high, int $maxDepth): void
{
    $size = $high - $low + 1;

    // Use insertion sort for small arrays
    if ($size < 16) {
        insertionSortRange($arr, $low, $high);
        return;
    }

    // Switch to heap sort if recursion too deep
    if ($maxDepth === 0) {
        heapSortRange($arr, $low, $high);
        return;
    }

    // Use quick sort
    $pi = partition($arr, $low, $high);
    introSort($arr, $low, $pi - 1, $maxDepth - 1);
    introSort($arr, $pi + 1, $high, $maxDepth - 1);
}

function introSortWrapper(array &$arr): void
{
    $maxDepth = (int)(2 * log(count($arr)));
    introSort($arr, 0, count($arr) - 1, $maxDepth);
}
```

## Enhanced Decision Tree: Which Sort to Use?

```
START: What is your array size?
│
├─ Very Small (< 20 elements)
│  └─ Use: Insertion Sort or PHP sort()
│     Reason: Simple, low overhead, fast enough
│
├─ Small (20-50 elements)
│  ├─ Nearly sorted?
│  │  ├─ Yes → Insertion Sort (O(n) performance!)
│  │  └─ No → Insertion Sort or Quick Sort
│  └─ Reason: Overhead of complex algorithms not worth it
│
├─ Medium (50-10,000 elements)
│  ├─ Need stability?
│  │  ├─ Yes → Merge Sort (only O(n log n) stable option)
│  │  └─ No → Continue...
│  ├─ Data characteristics?
│  │  ├─ Nearly sorted → Insertion Sort or Adaptive Quick Sort
│  │  ├─ Many duplicates (>20%) → **3-Way Quick Sort** (O(n log k)!)
│  │  ├─ Already/reverse sorted → Avoid Quick Sort with first/last pivot!
│  │  │                           Use random/median-of-three or Merge Sort
│  │  └─ Random → Quick Sort (fastest!)
│  └─ Need guaranteed O(n log n)?
│     ├─ Yes, have memory → Merge Sort
│     ├─ Yes, limited memory → Heap Sort
│     └─ No → Quick Sort or 3-Way Quick Sort (best average case)
│
└─ Large (> 10,000 elements)
   ├─ Need stability?
   │  └─ Yes → Merge Sort
   ├─ Need guaranteed O(n log n)?
   │  ├─ Yes, have memory (O(n)) → Merge Sort
   │  └─ Yes, limited memory → Heap Sort
   ├─ Data characteristics?
   │  ├─ Nearly sorted → Adaptive Quick Sort with insertion sort for small chunks
   │  ├─ Many duplicates (>20%) → **3-Way Quick Sort** (dramatically faster!)
   │  └─ Random → Optimized Quick Sort
   │     (median-of-three + insertion for small subarrays)
   └─ Performance critical?
      ├─ Unique values → Optimized Quick Sort (fastest in practice)
      └─ Many duplicates → 3-Way Quick Sort (can be 2-3x faster!)

SPECIAL CASES:
─────────────
• External sorting (data > memory): Merge Sort (natural for chunking)
• Linked lists: Merge Sort (no random access needed)
• Real-time systems: Merge or Heap Sort (predictable O(n log n))
• Embedded systems: Heap Sort (O(1) space, predictable)
• Unknown data patterns: Quick Sort with random pivot (safe bet)
• Educational purposes: Start with Insertion Sort (simplest)
```

### Quick Decision Cheat Sheet

| Scenario | Best Choice | Why |
|----------|-------------|-----|
| **Small array (< 50)** | Insertion Sort | Low overhead, simple |
| **Nearly sorted** | Insertion Sort | O(n) performance |
| **Random data** | Quick Sort | Fastest average case |
| **Need stability** | Merge Sort | Only O(n log n) stable |
| **Limited memory** | Heap Sort | O(1) space, guaranteed O(n log n) |
| **Many duplicates** | 3-Way Quick Sort | O(n log k) where k = unique |
| **Worst case matters** | Merge or Heap Sort | Guaranteed O(n log n) |
| **Don't know pattern** | Quick Sort (random pivot) | Safe for most cases |
| **Linked list** | Merge Sort | No random access needed |
| **Real-time system** | Heap or Merge Sort | Predictable timing |
| **Just use best** | PHP sort() | Optimized hybrid algorithm |

## Real-World Recommendations

### Web Development (PHP)

```php
class Sorter
{
    public static function smartSort(array $arr): array
    {
        $n = count($arr);

        // Tiny array: insertion sort
        if ($n < 50) {
            return insertionSort($arr);
        }

        // Check if nearly sorted
        if (self::isNearlySorted($arr)) {
            return insertionSort($arr);
        }

        // Default: use PHP's built-in (optimized Timsort-like)
        sort($arr);
        return $arr;
    }

    private static function isNearlySorted(array $arr): bool
    {
        $inversions = 0;
        $threshold = count($arr) * 0.1; // 10% inversions

        for ($i = 0; $i < count($arr) - 1; $i++) {
            if ($arr[$i] > $arr[$i + 1]) {
                $inversions++;
                if ($inversions > $threshold) {
                    return false;
                }
            }
        }

        return true;
    }
}
```

## Troubleshooting

### Performance Not as Expected

**Symptom**: Sorting takes much longer than benchmark results suggest

**Cause**: Several factors can affect performance:
- Array size much larger than tested
- PHP interpreter differences (version, configuration)
- Server load and available memory
- Data pattern not matching assumptions

**Solution**:
```php
// Always profile YOUR specific use case
$start = microtime(true);
$sorted = quickSort($data, 0, count($data) - 1);
$time = (microtime(true) - $start) * 1000;
echo "Actual time: {$time}ms\n";

// Compare with PHP's built-in
$start = microtime(true);
sort($data);
$time = (microtime(true) - $start) * 1000;
echo "PHP sort() time: {$time}ms\n";
```

### Quick Sort Running Slow on Sorted Data

**Symptom**: Quick sort performs poorly on already sorted arrays

**Cause**: Using first or last element as pivot creates O(n²) worst case

**Solution**: Use randomized pivot or median-of-three:
```php
// Bad: Always uses last element
$pivot = $arr[$high];

// Good: Randomized pivot
$pivotIndex = rand($low, $high);
[$arr[$pivotIndex], $arr[$high]] = [$arr[$high], $arr[$pivotIndex]];
$pivot = $arr[$high];

// Better: Median-of-three
$mid = (int)(($low + $high) / 2);
if ($arr[$mid] < $arr[$low]) {
    [$arr[$low], $arr[$mid]] = [$arr[$mid], $arr[$low]];
}
if ($arr[$high] < $arr[$low]) {
    [$arr[$low], $arr[$high]] = [$arr[$high], $arr[$low]];
}
if ($arr[$high] < $arr[$mid]) {
    [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];
}
$pivot = $arr[$high];
```

### Memory Exhausted with Merge Sort

**Symptom**: `Fatal error: Allowed memory size exhausted`

**Cause**: Merge sort requires O(n) extra space, and `array_slice()` creates copies

**Solution**: Either increase memory limit or use in-place algorithm:
```php
// If you need O(n log n) with O(1) space, use heap sort instead
$sorted = heapSort($largeArray);

// Or increase memory for merge sort (in php.ini or runtime)
ini_set('memory_limit', '512M');
$sorted = mergeSort($largeArray);
```

::: warning Common Pitfall
Never use O(n²) algorithms (bubble, selection, insertion) on arrays larger than 1,000 elements unless the data is nearly sorted. The performance degradation is dramatic and can bring your application to a halt.
:::

## Practice Exercises

### Exercise 1: Adaptive Sort

**Goal**: Create a smart sorting function that automatically selects the optimal algorithm based on array characteristics.

**Requirements**:
1. Detect array size (< 50 use insertion, >= 50 use quick sort)
2. Check if array is nearly sorted (< 10% inversions use insertion)
3. For large arrays (> 1000), detect if many duplicates exist (consider 3-way quicksort)
4. Return sorted array using the chosen algorithm

**Implementation**:

```php
# filename: adaptive-sort-exercise.php
<?php

declare(strict_types=1);

function adaptiveSort(array $arr): array
{
    $n = count($arr);
    
    // TODO: Implement size check
    // TODO: Implement inversion counting
    // TODO: Choose and apply appropriate algorithm
    // TODO: Return sorted array
}

// Helper function to count inversions (measure of sortedness)
function countInversions(array $arr, int $threshold): int
{
    // TODO: Count pairs where arr[i] > arr[j] and i < j
    // Stop counting if inversions > threshold
}
```

**Validation**: Test with these datasets:
```php
// Test 1: Small array (should use insertion sort)
$small = [5, 2, 8, 1, 9];
echo "Small: " . get_class(adaptiveSort($small)) . "\n";

// Test 2: Large random (should use quick sort)
$large = range(1, 5000);
shuffle($large);
$start = microtime(true);
adaptiveSort($large);
echo "Large random: " . ((microtime(true) - $start) * 1000) . "ms\n";

// Test 3: Nearly sorted (should use insertion sort)
$nearlySorted = range(1, 1000);
for ($i = 0; $i < 10; $i++) {
    $a = rand(0, 999);
    $b = rand(0, 999);
    [$nearlySorted[$a], $nearlySorted[$b]] = [$nearlySorted[$b], $nearlySorted[$a]];
}
$start = microtime(true);
adaptiveSort($nearlySorted);
echo "Nearly sorted: " . ((microtime(true) - $start) * 1000) . "ms (should be < 5ms)\n";
```

**Expected Output**:
```
Small: Insertion Sort selected
Large random: 25-50ms
Nearly sorted: 2-5ms (O(n) performance!)
```

### Exercise 2: Sort Stability Tester

**Goal**: Verify which sorting algorithms preserve the relative order of equal elements (stability).

**Requirements**:
1. Create an array of objects with `value` and `originalIndex` properties
2. Sort by `value` using different algorithms
3. For elements with equal `value`, check if `originalIndex` remains in order
4. Report which algorithms are stable

**Implementation**:

```php
# filename: stability-tester-exercise.php
<?php

declare(strict_types=1);

class Item
{
    public function __construct(
        public int $value,
        public int $originalIndex
    ) {}
}

function testStability(callable $sortFunc, string $algorithmName): bool
{
    // Create test data with duplicates
    $items = [
        new Item(5, 0),
        new Item(3, 1),
        new Item(5, 2),  // Duplicate
        new Item(1, 3),
        new Item(3, 4),  // Duplicate
        new Item(5, 5),  // Duplicate
    ];
    
    // TODO: Sort using provided algorithm
    // TODO: Check if items with same value maintain original order
    // TODO: Return true if stable, false if not
}

// Test all algorithms
$algorithms = [
    'Bubble Sort' => fn($arr) => bubbleSort($arr),
    'Selection Sort' => fn($arr) => selectionSort($arr),
    'Insertion Sort' => fn($arr) => insertionSort($arr),
    'Merge Sort' => fn($arr) => mergeSort($arr),
    'Quick Sort' => function($arr) {
        quickSort($arr, 0, count($arr) - 1);
        return $arr;
    },
    'Heap Sort' => fn($arr) => heapSort($arr),
];

foreach ($algorithms as $name => $func) {
    $stable = testStability($func, $name);
    echo "$name: " . ($stable ? "✓ STABLE" : "✗ UNSTABLE") . "\n";
}
```

**Expected Output**:
```
Bubble Sort: ✓ STABLE
Selection Sort: ✗ UNSTABLE
Insertion Sort: ✓ STABLE
Merge Sort: ✓ STABLE
Quick Sort: ✗ UNSTABLE
Heap Sort: ✗ UNSTABLE
```

### Exercise 3: Real-World Performance Benchmark

**Goal**: Test sorting algorithms with real-world data patterns from your actual applications.

**Requirements**:
1. Create datasets that mimic your real data (e.g., timestamps, user IDs, prices)
2. Test with different sizes (100, 1K, 10K, 100K elements)
3. Measure and compare: time, memory usage, and stability
4. Generate a recommendation report

**Implementation**:

```php
# filename: real-world-benchmark-exercise.php
<?php

declare(strict_types=1);

class RealWorldBenchmark
{
    public function generateRealisticData(int $size, string $type): array
    {
        // TODO: Generate realistic datasets
        // Types: 'timestamps', 'userids', 'prices', 'scores'
    }
    
    public function benchmarkWithMemory(callable $sortFunc, array $data): array
    {
        // TODO: Measure execution time
        // TODO: Measure memory usage with memory_get_usage()
        // TODO: Return ['time' => float, 'memory' => int]
    }
    
    public function generateReport(): void
    {
        // TODO: Test multiple algorithms on your data
        // TODO: Output formatted recommendation report
    }
}

// Run comprehensive benchmark
$bench = new RealWorldBenchmark();
$bench->generateReport();
```

**Expected Output**:
```
REAL-WORLD SORTING BENCHMARK
=============================

Dataset: E-commerce product prices (10,000 items)
Pattern: Many duplicates, some outliers

Algorithm        Time      Memory    Stable    Recommendation
----------------------------------------------------------------
Insertion Sort   2500ms    1KB       Yes       ✗ Too slow
Merge Sort       15ms      156KB     Yes       ✓ Good choice
Quick Sort       8ms       12KB      No        ✓ Best for speed
Heap Sort        18ms      1KB       No        ✓ Best for memory

RECOMMENDATION: Use Quick Sort for best performance,
                Merge Sort if stability required.
```

## Wrap-up

Congratulations! You've completed a comprehensive comparison of all major sorting algorithms. Let's review what you've accomplished:

**What You've Learned:**
- ✅ Benchmarked all six sorting algorithms across multiple data patterns
- ✅ Understood time and space complexity trade-offs for each algorithm
- ✅ Learned to identify which algorithm suits different scenarios (size, pattern, constraints)
- ✅ Mastered the concept of stable vs unstable sorting
- ✅ Created decision frameworks for real-world algorithm selection
- ✅ Explored hybrid approaches like Timsort and Introsort

**Real-World Impact:**

The knowledge you've gained in this chapter is immediately applicable to production code. You now understand why PHP's `sort()` function uses a hybrid algorithm, when to optimize sorting performance, and how to make informed decisions that can improve your application's speed by orders of magnitude.

Remember: **there's no single "best" sorting algorithm**. The optimal choice always depends on your specific use case. Quick sort dominates for general-purpose sorting, insertion sort excels for small or nearly-sorted data, merge sort guarantees O(n log n) with stability, and heap sort provides guaranteed performance with minimal memory. Choose wisely based on your constraints.

**Next Steps:**

In the next chapter, we'll explore **PHP's built-in sorting functions**—learning how to leverage PHP's optimized sorting capabilities, use custom comparators, and work with different data types efficiently.

## Key Takeaways

- **No single "best" sorting algorithm** - depends on context
- **Quick sort** is usually fastest for general-purpose sorting
- **Insertion sort** excels for small or nearly sorted arrays
- **Merge sort** when you need stability and guaranteed O(n log n)
- **Heap sort** when memory is limited and need O(n log n)
- **Hybrid algorithms** combine strengths of multiple sorts
- **PHP's sort()** uses optimized hybrid approach (usually best choice)
- **Profile your specific use case** before optimizing

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="09"
  label="Completed Comparing Sorting Algorithms!"
/>

## What's Next

In the next chapter, we'll explore **PHP's Built-in Sorting Functions** and learn how to use them effectively with custom comparators and different data types.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 09 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-09)**

Files included:
- `01-sorting-benchmark.php` - Side-by-side performance comparison of all sorting algorithms on different data patterns
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-09
php 01-sorting-benchmark.php
```

---

Continue to [Chapter 10: PHP's Built-in Sorting Functions](/series/php-algorithms/chapters/10-php-built-in-sorting-functions).
