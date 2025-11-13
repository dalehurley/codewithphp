---
title: "09: Comparing Sorting Algorithms"
description: "Benchmark all sorting algorithms against each other. Learn which to use in different scenarios."
series: "php-algorithms"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites:
  - "Completion of Chapters 05-08"
  - "Understanding of all sorting algorithms covered"
---

# Comparing Sorting Algorithms

We've learned six sorting algorithms: bubble sort, selection sort, insertion sort, merge sort, quick sort, and heap sort. In this chapter, we'll compare them comprehensively, benchmark their performance, and learn when to use each one.

## Quick Reference Table

| Algorithm | Best | Average | Worst | Space | Stable | In-Place |
|-----------|------|---------|-------|-------|--------|----------|
| **Bubble Sort** | O(n) | O(n²) | O(n²) | O(1) | Yes | Yes |
| **Selection Sort** | O(n²) | O(n²) | O(n²) | O(1) | No | Yes |
| **Insertion Sort** | O(n) | O(n²) | O(n²) | O(1) | Yes | Yes |
| **Merge Sort** | O(n log n) | O(n log n) | O(n log n) | O(n) | Yes | No |
| **Quick Sort** | O(n log n) | O(n log n) | O(n²)* | O(log n) | No | Yes |
| **Heap Sort** | O(n log n) | O(n log n) | O(n log n) | O(1) | No | Yes |

*With good pivot selection, worst case is extremely rare

## Detailed Comparison

### Bubble Sort

```php
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
function selectionSort(array $arr): array
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }
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
function insertionSort(array $arr): array
{
    $n = count($arr);
    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;
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
function mergeSort(array $arr): array
{
    if (count($arr) <= 1) return $arr;

    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

function merge(array $left, array $right): array
{
    $result = [];
    $i = $j = 0;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }

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
function quickSort(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pi = partition($arr, $low, $high);
        quickSort($arr, $low, $pi - 1);
        quickSort($arr, $pi + 1, $high);
    }
}

function partition(array &$arr, int $low, int $high): int
{
    $pivot = $arr[$high];
    $i = $low - 1;

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

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

### Heap Sort

```php
function heapSort(array $arr): array
{
    $n = count($arr);

    // Build heap
    for ($i = (int)(($n / 2) - 1); $i >= 0; $i--) {
        heapify($arr, $i, $n);
    }

    // Extract elements
    for ($i = $n - 1; $i > 0; $i--) {
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[$0]];
        heapify($arr, 0, $i);
    }

    return $arr;
}

function heapify(array &$arr, int $i, int $size): void
{
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    if ($left < $size && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    if ($right < $size && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

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

```php
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

**Winner: Insertion Sort**
- Low overhead
- O(n) on nearly sorted data
- Simple and fast for tiny arrays

### Medium Arrays (50-10,000)

**Winner: Quick Sort**
- Excellent cache locality
- Low overhead
- Fast partitioning

### Large Arrays (> 10,000)

**Winner: Quick Sort (with optimizations)**
- Remains fastest due to cache efficiency
- Merge Sort close second if stability needed

### Nearly Sorted Data

**Winner: Insertion Sort** (small) or **Quick Sort** (large)
- Insertion sort is O(n) on nearly sorted data
- Quick sort with good pivot still very fast

### Many Duplicates

**Winner: 3-Way Quick Sort**
- Handles duplicates efficiently
- O(n) when all elements equal

### Need Guaranteed O(n log n)

**Winner: Merge Sort** (if have memory) or **Heap Sort** (if don't)
- Both guarantee O(n log n)
- Merge sort faster in practice

## Hybrid Approaches

Real-world sorting often uses hybrid algorithms:

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

## Decision Tree: Which Sort to Use?

```
Need stable sort?
├─ Yes
│  ├─ Have extra memory?
│  │  ├─ Yes → Merge Sort
│  │  └─ No → Insertion Sort (if small)
│  └─ No (stability matters)
│     └─ Merge Sort
└─ No
   ├─ Need guaranteed O(n log n)?
   │  ├─ Yes
   │  │  ├─ Have extra memory?
   │  │  │  ├─ Yes → Merge Sort
   │  │  │  └─ No → Heap Sort
   │  └─ No
   │     ├─ Array size?
   │     │  ├─ Small (< 50) → Insertion Sort
   │     │  └─ Large → Quick Sort
   │     └─ Data pattern?
   │        ├─ Nearly sorted → Insertion Sort
   │        ├─ Many duplicates → 3-Way Quick Sort
   │        └─ Random → Quick Sort
```

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

## Practice Exercises

### Exercise 1: Adaptive Sort

Implement a sorting function that automatically chooses the best algorithm:

```php
function adaptiveSort(array $arr): array
{
    // Analyze array and choose algorithm
    // Your code here
}
```

### Exercise 2: Sort Visualization

Create a visual comparison showing how different sorts behave:

```php
function visualizeSort(string $algorithm, array $arr): void
{
    // Print step-by-step execution
    // Your code here
}
```

### Exercise 3: Custom Benchmark

Build a benchmarking suite that tests your own criteria:

```php
class CustomBenchmark
{
    public function testSortingAlgorithms(): void
    {
        // Your custom benchmark logic
    }
}
```

## Key Takeaways

- **No single "best" sorting algorithm** - depends on context
- **Quick sort** is usually fastest for general-purpose sorting
- **Insertion sort** excels for small or nearly sorted arrays
- **Merge sort** when you need stability and guaranteed O(n log n)
- **Heap sort** when memory is limited and need O(n log n)
- **Hybrid algorithms** combine strengths of multiple sorts
- **PHP's sort()** uses optimized hybrid approach (usually best choice)
- **Profile your specific use case** before optimizing

## What's Next

In the next chapter, we'll explore **PHP's Built-in Sorting Functions** and learn how to use them effectively with custom comparators and different data types.

---

Continue to [Chapter 10: PHP's Built-in Sorting Functions](/series/php-algorithms/chapters/10-php-built-in-sorting-functions).
