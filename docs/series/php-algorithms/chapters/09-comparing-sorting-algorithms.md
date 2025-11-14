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

We've learned six sorting algorithms: bubble sort, selection sort, insertion sort, merge sort, quick sort, and heap sort. In this chapter, we'll compare them comprehensively, benchmark their performance, and learn when to use each one.

## What You'll Learn

**Estimated time:** 50 minutes

By the end of this chapter, you will:

- Benchmark all six sorting algorithms across various dataset types and sizes
- Understand time/space complexity trade-offs for each sorting algorithm
- Learn which algorithm to choose based on data characteristics (size, order, duplicates)
- Master the concept of stable vs unstable sorting and when stability matters
- Create decision charts and selection guidelines for real-world sorting scenarios

## Prerequisites

Before starting this chapter, ensure you have:

- ✓ Completion of Chapters 05-08 *(255 mins if not done)*
- ✓ Understanding of all sorting algorithms covered *(review if needed)*

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

| Algorithm | Best | Average | Worst | Space | Stable | In-Place | Cache | Adaptive |
|-----------|------|---------|-------|-------|--------|----------|-------|----------|
| **Bubble Sort** | O(n) | O(n²) | O(n²) | O(1) | ✅ Yes | ✅ Yes | ✅ Good | ✅ Yes |
| **Selection Sort** | O(n²) | O(n²) | O(n²) | O(1) | ❌ No | ✅ Yes | ✅ Good | ❌ No |
| **Insertion Sort** | O(n) | O(n²) | O(n²) | O(1) | ✅ Yes | ✅ Yes | ✅ Excellent | ✅ Yes |
| **Merge Sort** | O(n log n) | O(n log n) | O(n log n) | O(n) | ✅ Yes | ❌ No | ⚠️ Good | ❌ No |
| **Quick Sort** | O(n log n) | O(n log n) | O(n²)* | O(log n) | ❌ No | ✅ Yes | ✅ Excellent | ⚠️ Can be |
| **Heap Sort** | O(n log n) | O(n log n) | O(n log n) | O(1) | ❌ No | ✅ Yes | ❌ Poor | ❌ No |

*With good pivot selection, worst case is extremely rare

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

**Winner: Merge Sort** 🏆
- Only O(n log n) stable algorithm
- Essential for multi-field sorting
- Preserves order of equal elements

**When stability matters:**
```php
// Sorting by grade, preserving registration order
$students = [
    ['name' => 'Alice', 'grade' => 85, 'registered' => 1],
    ['name' => 'Bob', 'grade' => 85, 'registered' => 2],
];

// Merge Sort: Alice before Bob (stable) ✓
// Quick Sort: Bob before Alice (unstable) ✗
// Heap Sort: Bob before Alice (unstable) ✗
```

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
│  │  ├─ Many duplicates → 3-Way Quick Sort
│  │  ├─ Already/reverse sorted → Avoid Quick Sort with first/last pivot!
│  │  │                           Use random/median-of-three or Merge Sort
│  │  └─ Random → Quick Sort (fastest!)
│  └─ Need guaranteed O(n log n)?
│     ├─ Yes, have memory → Merge Sort
│     ├─ Yes, limited memory → Heap Sort
│     └─ No → Quick Sort (best average case)
│
└─ Large (> 10,000 elements)
   ├─ Need stability?
   │  └─ Yes → Merge Sort
   ├─ Need guaranteed O(n log n)?
   │  ├─ Yes, have memory (O(n)) → Merge Sort
   │  └─ Yes, limited memory → Heap Sort
   ├─ Data characteristics?
   │  ├─ Nearly sorted → Adaptive Quick Sort with insertion sort for small chunks
   │  ├─ Many duplicates → 3-Way Quick Sort
   │  └─ Random → Optimized Quick Sort
   │     (median-of-three + insertion for small subarrays)
   └─ Performance critical?
      └─ Yes → Optimized Quick Sort (fastest in practice)

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

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 09 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-09)**

Files included:
- `01-sorting-benchmark.php` - Side-by-side performance comparison of all sorting algorithms on different data patterns
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-09
php 01-sorting-benchmark.php
```

---

Continue to [Chapter 10: PHP's Built-in Sorting Functions](/series/php-algorithms/chapters/10-php-built-in-sorting-functions).
