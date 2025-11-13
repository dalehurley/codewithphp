---
title: "05: Bubble Sort & Selection Sort"
description: "Implement and understand simple sorting algorithms. Learn their time complexity and when (not) to use them."
series: "php-algorithms"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "Understanding of Big O notation"
  - "Familiarity with arrays and loops"
  - "Completion of Chapters 0-2"
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 05</span>
</div>

# Bubble Sort & Selection Sort <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

Now that we understand algorithm complexity and how to benchmark performance, let's dive into our first sorting algorithms. We'll start with two simple but inefficient sorting algorithms: **Bubble Sort** and **Selection Sort**.

While these algorithms aren't practical for large datasets, they're excellent learning tools that introduce fundamental sorting concepts.

## What You'll Learn

**Estimated time:** 55 minutes

By the end of this chapter, you will:

- Implement Bubble Sort and Selection Sort from scratch in PHP
- Understand O(n²) time complexity and why it matters for sorting
- Learn optimization techniques like early termination for nearly-sorted data
- Benchmark these algorithms against various dataset sizes to validate complexity analysis
- Recognize when simple sorting algorithms are appropriate vs when to use advanced alternatives

## Prerequisites

Before starting this chapter, ensure you have:

- ✓ Understanding of Big O notation *(60 mins from Chapter 1 if not done)*
- ✓ Familiarity with arrays and loops *(10 mins review if needed)*
- ✓ Completion of Chapters 0-2 *(180 mins if not done)*

## Quick Checklist

Complete these hands-on tasks as you work through the chapter:

- [ ] Implement basic Bubble Sort with nested loops
- [ ] Add optimization flag to detect if array is already sorted (early termination)
- [ ] Implement Selection Sort by finding minimum element in each pass
- [ ] Benchmark both algorithms with different array sizes (100, 500, 1000 items)
- [ ] Compare performance: random, sorted, and reverse-sorted arrays
- [ ] (Optional) Implement bidirectional Bubble Sort (Cocktail Shaker Sort)

## Why Learn "Slow" Algorithms?

You might wonder: "Why learn algorithms that are inefficient?"

Here's why:

1. **Foundation**: They teach core sorting concepts used in advanced algorithms
2. **Interviews**: Simple algorithms are common in technical interviews
3. **Small datasets**: They're perfectly fine for tiny arrays (< 100 items)
4. **Understanding**: You'll appreciate faster algorithms more after seeing slow ones
5. **Comparison**: They serve as benchmarks for better algorithms

## Bubble Sort

**Bubble Sort** repeatedly steps through the array, compares adjacent elements, and swaps them if they're in the wrong order. Larger values "bubble up" to the end.

### How It Works

Imagine sorting `[5, 2, 8, 1, 9]`:

**Pass 1:**
- Compare 5 and 2 → swap → `[2, 5, 8, 1, 9]`
- Compare 5 and 8 → no swap → `[2, 5, 8, 1, 9]`
- Compare 8 and 1 → swap → `[2, 5, 1, 8, 9]`
- Compare 8 and 9 → no swap → `[2, 5, 1, 8, 9]`
- ✅ Largest element (9) is now in place

**Pass 2:**
- Compare 2 and 5 → no swap
- Compare 5 and 1 → swap → `[2, 1, 5, 8, 9]`
- Compare 5 and 8 → no swap
- ✅ Second largest (8) is in place

Continue until the array is sorted.

### Basic Implementation

```php
function bubbleSort(array $arr): array
{
    $n = count($arr);

    // Outer loop: number of passes
    for ($i = 0; $i < $n - 1; $i++) {
        // Inner loop: comparisons in this pass
        for ($j = 0; $j < $n - $i - 1; $j++) {
            // If current element is greater than next, swap them
            if ($arr[$j] > $arr[$j + 1]) {
                // Swap using array destructuring
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }

    return $arr;
}

// Test it
$numbers = [64, 34, 25, 12, 22, 11, 90];
print_r(bubbleSort($numbers));
// Output: [11, 12, 22, 25, 34, 64, 90]
```

### Complexity Analysis

- **Time Complexity:**
  - Best case: O(n) - already sorted, with optimization
  - Average case: O(n²) - random order
  - Worst case: O(n²) - reverse sorted

- **Space Complexity:** O(1) - sorts in place

**Why O(n²)?**
- Outer loop runs n-1 times
- Inner loop runs (n-1), (n-2), (n-3), ..., 1 times
- Total comparisons: (n-1) + (n-2) + ... + 1 = n(n-1)/2 ≈ n²/2 → O(n²)

### Optimized Bubble Sort

We can optimize by stopping early if no swaps occur (array is sorted):

```php
function bubbleSortOptimized(array $arr): array
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

        // If no swaps occurred, array is sorted
        if (!$swapped) {
            break;
        }
    }

    return $arr;
}

// Best case: already sorted array
$sorted = [1, 2, 3, 4, 5];
bubbleSortOptimized($sorted); // Only one pass needed!
```

This optimization improves best-case complexity to **O(n)** when the array is already sorted.

### Visualizing Bubble Sort

```php
function bubbleSortWithVisualization(array $arr): array
{
    $n = count($arr);
    echo "Starting array: " . implode(', ', $arr) . "\n\n";

    for ($i = 0; $i < $n - 1; $i++) {
        echo "Pass " . ($i + 1) . ":\n";

        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                echo "  Swap {$arr[$j]} and {$arr[$j + 1]}\n";
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }

        echo "  Result: " . implode(', ', $arr) . "\n\n";
    }

    return $arr;
}

bubbleSortWithVisualization([5, 2, 8, 1, 9]);
```

**Output:**
```
Starting array: 5, 2, 8, 1, 9

Pass 1:
  Swap 5 and 2
  Swap 8 and 1
  Result: 2, 5, 1, 8, 9

Pass 2:
  Swap 5 and 1
  Result: 2, 1, 5, 8, 9

Pass 3:
  Swap 2 and 1
  Result: 1, 2, 5, 8, 9

Pass 4:
  Result: 1, 2, 5, 8, 9
```

## Selection Sort

**Selection Sort** works by repeatedly finding the minimum element and placing it at the beginning of the unsorted portion.

### How It Works

Sorting `[64, 25, 12, 22, 11]`:

**Pass 1:**
- Find minimum in `[64, 25, 12, 22, 11]` → **11**
- Swap with first element → `[11, 25, 12, 22, 64]`

**Pass 2:**
- Find minimum in `[25, 12, 22, 64]` → **12**
- Swap with first unsorted element → `[11, 12, 25, 22, 64]`

**Pass 3:**
- Find minimum in `[25, 22, 64]` → **22**
- Swap → `[11, 12, 22, 25, 64]`

**Pass 4:**
- Find minimum in `[25, 64]` → **25**
- Already in place → `[11, 12, 22, 25, 64]`

Done!

### Implementation

```php
function selectionSort(array $arr): array
{
    $n = count($arr);

    // Move boundary of unsorted portion
    for ($i = 0; $i < $n - 1; $i++) {
        // Find minimum element in unsorted portion
        $minIndex = $i;

        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Swap found minimum with first element of unsorted portion
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
        }
    }

    return $arr;
}

// Test
$numbers = [64, 25, 12, 22, 11];
print_r(selectionSort($numbers));
// Output: [11, 12, 22, 25, 64]
```

### Complexity Analysis

- **Time Complexity:**
  - Best case: O(n²) - even if sorted
  - Average case: O(n²)
  - Worst case: O(n²)

- **Space Complexity:** O(1) - sorts in place

**Why always O(n²)?**
- Always makes the same number of comparisons regardless of input
- No early exit optimization possible
- Comparisons: (n-1) + (n-2) + ... + 1 = n(n-1)/2 → O(n²)

### Selection Sort with Visualization

```php
function selectionSortWithVisualization(array $arr): array
{
    $n = count($arr);
    echo "Starting array: " . implode(', ', $arr) . "\n\n";

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;

        // Find minimum
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Swap if needed
        if ($minIndex !== $i) {
            echo "Pass " . ($i + 1) . ": ";
            echo "Swap {$arr[$i]} (position $i) with {$arr[$minIndex]} (position $minIndex)\n";
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
            echo "  Result: " . implode(', ', $arr) . "\n\n";
        }
    }

    return $arr;
}

selectionSortWithVisualization([64, 25, 12, 22, 11]);
```

## Comparing Bubble Sort vs Selection Sort

Let's benchmark them:

```php
require_once 'Benchmark.php'; // From Chapter 2

$bench = new Benchmark();

// Test with different sizes
$sizes = [100, 500, 1000, 2000];

foreach ($sizes as $size) {
    $data = range(1, $size);
    shuffle($data);

    echo "Array size: $size\n";
    $bench->compare([
        'Bubble Sort' => fn($arr) => bubbleSort($arr),
        'Bubble Sort (Optimized)' => fn($arr) => bubbleSortOptimized($arr),
        'Selection Sort' => fn($arr) => selectionSort($arr),
    ], $data, iterations: 10);
    echo "\n";
}
```

### Key Differences

| Feature | Bubble Sort | Selection Sort |
|---------|-------------|----------------|
| **Comparisons** | O(n²) | O(n²) |
| **Swaps** | O(n²) worst case | O(n) always |
| **Best case** | O(n) with optimization | O(n²) always |
| **Stable?** | Yes | No |
| **When to use** | Nearly sorted data | Minimize writes |

**Stability** means equal elements maintain their relative order. This matters when sorting objects:

```php
$students = [
    ['name' => 'Alice', 'score' => 85],
    ['name' => 'Bob', 'score' => 90],
    ['name' => 'Charlie', 'score' => 85],
];

// Stable sort: Alice stays before Charlie (both scored 85)
// Unstable sort: Charlie might come before Alice
```

## Practical Applications

### When Bubble Sort Is Okay

```php
// Tiny array - bubble sort is fine
function sortThreeNumbers(int $a, int $b, int $c): array
{
    $arr = [$a, $b, $c];
    return bubbleSort($arr); // Only 3 elements!
}

// Nearly sorted data
$almostSorted = [1, 2, 3, 5, 4, 6, 7, 8];
bubbleSortOptimized($almostSorted); // Very fast with optimization
```

### When Selection Sort Is Okay

```php
// When write operations are expensive (e.g., flash memory, network)
function sortExpensiveWrites(array $arr): array
{
    // Selection sort minimizes swaps
    return selectionSort($arr);
}

// Finding top K elements
function findTopK(array $arr, int $k): array
{
    // Use k passes of selection sort
    $n = count($arr);

    for ($i = 0; $i < min($k, $n); $i++) {
        $maxIndex = $i;

        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] > $arr[$maxIndex]) {
                $maxIndex = $j;
            }
        }

        if ($maxIndex !== $i) {
            [$arr[$i], $arr[$maxIndex]] = [$arr[$maxIndex], $arr[$i]];
        }
    }

    return array_slice($arr, 0, $k);
}

$scores = [45, 92, 67, 88, 71, 95, 53];
print_r(findTopK($scores, 3)); // [95, 92, 88]
```

## Real-World Example: Sorting User Data

```php
class User
{
    public function __construct(
        public string $name,
        public int $age
    ) {}
}

function sortUsersByAge(array $users): array
{
    $n = count($users);

    // Using bubble sort for small user arrays
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($users[$j]->age > $users[$j + 1]->age) {
                [$users[$j], $users[$j + 1]] = [$users[$j + 1], $users[$j]];
            }
        }
    }

    return $users;
}

$users = [
    new User('Alice', 30),
    new User('Bob', 25),
    new User('Charlie', 35),
    new User('David', 25),
];

$sorted = sortUsersByAge($users);

foreach ($sorted as $user) {
    echo "{$user->name}: {$user->age}\n";
}
```

## Common Mistakes to Avoid

### Mistake 1: Off-by-One Errors

```php
// Wrong: goes out of bounds
for ($j = 0; $j < $n; $j++) { // Should be $n - 1
    if ($arr[$j] > $arr[$j + 1]) { // $j + 1 can exceed bounds!
```

### Mistake 2: Forgetting to Return

```php
// Wrong: modifies in place but doesn't return
function bubbleSort(array $arr): void
{
    // ... sorting logic ...
    // Forgot to return $arr!
}

// Correct:
function bubbleSort(array $arr): array
{
    // ... sorting logic ...
    return $arr;
}
```

### Mistake 3: Inefficient Swapping

```php
// Inefficient
$temp = $arr[$i];
$arr[$i] = $arr[$j];
$arr[$j] = $temp;

// Better: PHP's array destructuring
[$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
```

## Cocktail Shaker Sort (Bidirectional Bubble Sort)

An optimization of bubble sort that sorts in both directions:

```php
function cocktailSort(array $arr): array
{
    $n = count($arr);
    $swapped = true;
    $start = 0;
    $end = $n - 1;

    while ($swapped) {
        $swapped = false;

        // Forward pass (left to right)
        for ($i = $start; $i < $end; $i++) {
            if ($arr[$i] > $arr[$i + 1]) {
                [$arr[$i], $arr[$i + 1]] = [$arr[$i + 1], $arr[$i]];
                $swapped = true;
            }
        }

        if (!$swapped) break;

        $swapped = false;
        $end--;

        // Backward pass (right to left)
        for ($i = $end; $i > $start; $i--) {
            if ($arr[$i] < $arr[$i - 1]) {
                [$arr[$i], $arr[$i - 1]] = [$arr[$i - 1], $arr[$i]];
                $swapped = true;
            }
        }

        $start++;
    }

    return $arr;
}

// Test
$numbers = [5, 1, 4, 2, 8, 0, 2];
print_r(cocktailSort($numbers));
// Output: [0, 1, 2, 2, 4, 5, 8]
```

**Advantages over regular bubble sort:**
- Slightly faster on some inputs
- Better handles "turtles" (small values at the end)
- Still O(n²) worst case but can be faster in practice

### Performance Comparison

```php
require_once 'Benchmark.php';

$bench = new Benchmark();

// Test on different data patterns
$patterns = [
    'Random' => function($n) { $arr = range(1, $n); shuffle($arr); return $arr; },
    'Nearly Sorted' => function($n) {
        $arr = range(1, $n);
        // Swap a few elements
        for ($i = 0; $i < $n / 10; $i++) {
            $j = rand(0, $n - 1);
            $k = rand(0, $n - 1);
            [$arr[$j], $arr[$k]] = [$arr[$k], $arr[$j]];
        }
        return $arr;
    },
    'Reversed' => function($n) { return range($n, 1); },
];

foreach ($patterns as $patternName => $generator) {
    echo "\n{$patternName} data (n=100):\n";
    $data = $generator(100);

    $bench->compare([
        'Bubble Sort' => fn($arr) => bubbleSort($arr),
        'Bubble Sort (Optimized)' => fn($arr) => bubbleSortOptimized($arr),
        'Cocktail Sort' => fn($arr) => cocktailSort($arr),
        'Selection Sort' => fn($arr) => selectionSort($arr),
    ], $data, iterations: 100);
}
```

## Adaptive Bubble Sort

Adaptive sort: performs better on partially sorted data.

```php
function adaptiveBubbleSort(array $arr): array
{
    $n = count($arr);
    $swapped = true;
    $passes = 0;

    while ($swapped) {
        $swapped = false;
        $lastSwap = 0;

        for ($i = 0; $i < $n - $passes - 1; $i++) {
            if ($arr[$i] > $arr[$i + 1]) {
                [$arr[$i], $arr[$i + 1]] = [$arr[$i + 1], $arr[$i]];
                $swapped = true;
                $lastSwap = $i;
            }
        }

        // Optimize: elements after last swap are already sorted
        $passes = $n - $lastSwap - 1;

        if (!$swapped) break;
    }

    return $arr;
}

// Best for nearly sorted data
$nearlySorted = [1, 2, 3, 5, 4, 6, 7, 8, 9, 10];
$start = hrtime(true);
adaptiveBubbleSort($nearlySorted);
$time = (hrtime(true) - $start) / 1_000_000;
echo "Time: {$time}ms\n"; // Very fast!
```

## Comb Sort (Improved Bubble Sort)

Uses a gap larger than 1 and shrinks it:

```php
function combSort(array $arr): array
{
    $n = count($arr);
    $gap = $n;
    $shrink = 1.3;
    $swapped = true;

    while ($gap > 1 || $swapped) {
        // Update gap
        $gap = (int)($gap / $shrink);
        if ($gap < 1) $gap = 1;

        $swapped = false;

        // Compare elements with current gap
        for ($i = 0; $i + $gap < $n; $i++) {
            if ($arr[$i] > $arr[$i + $gap]) {
                [$arr[$i], $arr[$i + $gap]] = [$arr[$i + $gap], $arr[$i]];
                $swapped = true;
            }
        }
    }

    return $arr;
}

// Significantly faster than bubble sort on large arrays
$large = range(1, 1000);
shuffle($large);

$start = hrtime(true);
bubbleSort($large);
$bubbleTime = (hrtime(true) - $start) / 1_000_000;

$start = hrtime(true);
combSort($large);
$combTime = (hrtime(true) - $start) / 1_000_000;

echo "Bubble Sort: {$bubbleTime}ms\n";
echo "Comb Sort: {$combTime}ms\n";
echo "Speedup: " . round($bubbleTime / $combTime, 2) . "x\n";
```

## Interview Questions & Answers

### Q1: When would you use bubble sort in production?

**Answer:**
- Very small datasets (< 10 elements) where code simplicity matters
- Nearly sorted data with optimization enabled
- Educational purposes or as a fallback
- When memory is extremely limited (in-place, no recursion)
- **Realistically:** Almost never. Use `sort()` or better algorithms.

### Q2: What's the space complexity and why?

**Answer:**
O(1) space complexity (constant). Both bubble sort and selection sort are in-place algorithms:
- Only use a few variables for swapping and loop counters
- Don't create additional arrays proportional to input size
- Compare to merge sort which needs O(n) extra space

### Q3: How do you detect if an array is already sorted efficiently?

**Answer:**
```php
function isSorted(array $arr): bool
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        if ($arr[$i] > $arr[$i + 1]) {
            return false;
        }
    }
    return true;
}

// Or using optimized bubble sort:
function isSortedViaBubble(array $arr): bool
{
    $n = count($arr);
    $swapped = false;

    for ($i = 0; $i < $n - 1; $i++) {
        if ($arr[$i] > $arr[$i + 1]) {
            return false; // Early exit on first inversion
        }
    }

    return true; // No swaps needed
}
```

### Q4: Implement stable selection sort

**Answer:**
Regular selection sort is unstable. Make it stable by shifting instead of swapping:

```php
function stableSelectionSort(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;

        // Find minimum
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Instead of swapping, shift elements
        if ($minIndex !== $i) {
            $minValue = $arr[$minIndex];

            // Shift elements right
            for ($j = $minIndex; $j > $i; $j--) {
                $arr[$j] = $arr[$j - 1];
            }

            $arr[$i] = $minValue;
        }
    }

    return $arr;
}

// Test stability
$items = [
    ['name' => 'Alice', 'age' => 30],
    ['name' => 'Bob', 'age' => 25],
    ['name' => 'Charlie', 'age' => 30],
];

// Sort by age (stable)
$sorted = stableSelectionSort($items);
// Alice should still come before Charlie (both age 30)
```

### Q5: Optimize bubble sort for a linked list

**Answer:**
```php
class ListNode
{
    public function __construct(
        public int $value,
        public ?ListNode $next = null
    ) {}
}

function bubbleSortLinkedList(?ListNode $head): ?ListNode
{
    if ($head === null) return null;

    $swapped = true;

    while ($swapped) {
        $swapped = false;
        $current = $head;

        while ($current->next !== null) {
            if ($current->value > $current->next->value) {
                // Swap values (easier than rewiring pointers)
                $temp = $current->value;
                $current->value = $current->next->value;
                $current->next->value = $temp;
                $swapped = true;
            }

            $current = $current->next;
        }
    }

    return $head;
}

// Create list: 4 -> 2 -> 1 -> 3
$head = new ListNode(4, new ListNode(2, new ListNode(1, new ListNode(3))));
$sorted = bubbleSortLinkedList($head);

// Print: 1 -> 2 -> 3 -> 4
$current = $sorted;
while ($current !== null) {
    echo $current->value . " ";
    $current = $current->next;
}
```

### Q6: Count minimum swaps needed to sort array

**Answer:**
```php
function minSwapsToSort(array $arr): int
{
    $n = count($arr);
    $arrPos = [];

    // Store value => position
    for ($i = 0; $i < $n; $i++) {
        $arrPos[] = [$arr[$i], $i];
    }

    // Sort by value
    usort($arrPos, fn($a, $b) => $a[0] <=> $b[0]);

    $visited = array_fill(0, $n, false);
    $swaps = 0;

    for ($i = 0; $i < $n; $i++) {
        // Skip if already visited or in correct position
        if ($visited[$i] || $arrPos[$i][1] === $i) {
            continue;
        }

        // Count cycle size
        $cycleSize = 0;
        $j = $i;

        while (!$visited[$j]) {
            $visited[$j] = true;
            $j = $arrPos[$j][1];
            $cycleSize++;
        }

        // Add cycle size - 1 swaps
        if ($cycleSize > 0) {
            $swaps += $cycleSize - 1;
        }
    }

    return $swaps;
}

echo minSwapsToSort([4, 3, 2, 1]); // 2
echo minSwapsToSort([1, 5, 4, 3, 2]); // 2
```

## Comprehensive Benchmark Suite

```php
class SortingBenchmark
{
    private Benchmark $bench;
    private array $results = [];

    public function __construct()
    {
        $this->bench = new Benchmark();
    }

    public function runComprehensiveTests(): void
    {
        $sizes = [10, 50, 100, 500, 1000];
        $dataTypes = [
            'Random' => fn($n) => $this->generateRandom($n),
            'Sorted' => fn($n) => range(1, $n),
            'Reversed' => fn($n) => range($n, 1),
            'Nearly Sorted' => fn($n) => $this->generateNearlySorted($n),
            'Many Duplicates' => fn($n) => $this->generateDuplicates($n),
        ];

        foreach ($sizes as $size) {
            echo "\n" . str_repeat('=', 60) . "\n";
            echo "Array Size: {$size}\n";
            echo str_repeat('=', 60) . "\n";

            foreach ($dataTypes as $typeName => $generator) {
                echo "\n{$typeName}:\n";
                $data = $generator($size);

                $this->bench->compare([
                    'Bubble' => fn($arr) => bubbleSort($arr),
                    'Bubble Optimized' => fn($arr) => bubbleSortOptimized($arr),
                    'Cocktail' => fn($arr) => cocktailSort($arr),
                    'Selection' => fn($arr) => selectionSort($arr),
                    'Comb' => fn($arr) => combSort($arr),
                    'PHP sort()' => function($arr) {
                        sort($arr);
                        return $arr;
                    },
                ], $data, iterations: $size > 500 ? 10 : 100);
            }
        }
    }

    private function generateRandom(int $n): array
    {
        $arr = range(1, $n);
        shuffle($arr);
        return $arr;
    }

    private function generateNearlySorted(int $n): array
    {
        $arr = range(1, $n);
        $swaps = max(1, (int)($n / 10));

        for ($i = 0; $i < $swaps; $i++) {
            $j = rand(0, $n - 1);
            $k = rand(0, $n - 1);
            [$arr[$j], $arr[$k]] = [$arr[$k], $arr[$j]];
        }

        return $arr;
    }

    private function generateDuplicates(int $n): array
    {
        $arr = [];
        $uniqueValues = max(1, (int)($n / 5));

        for ($i = 0; $i < $n; $i++) {
            $arr[] = rand(1, $uniqueValues);
        }

        return $arr;
    }
}

// Run comprehensive benchmarks
$benchmark = new SortingBenchmark();
$benchmark->runComprehensiveTests();
```

## Practice Exercises

### Exercise 1: Descending Bubble Sort

Modify bubble sort to sort in **descending** order:

```php
function bubbleSortDesc(array $arr): array
{
    // Your code here
}

echo implode(', ', bubbleSortDesc([5, 2, 8, 1, 9]));
// Should output: 9, 8, 5, 2, 1
```

<details>
<summary>Solution</summary>

```php
function bubbleSortDesc(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            // Change > to <
            if ($arr[$j] < $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }

    return $arr;
}
```
</details>

### Exercise 2: Count Swaps

Modify bubble sort to count the number of swaps performed:

```php
function bubbleSortCountSwaps(array $arr): array
{
    $swaps = 0;
    // Your code here
    echo "Swaps performed: $swaps\n";
    return $arr;
}
```

### Exercise 3: Find Kth Smallest

Use selection sort to find the Kth smallest element:

```php
function findKthSmallest(array $arr, int $k): int
{
    // Hint: You only need k passes of selection sort
}

echo findKthSmallest([7, 10, 4, 3, 20, 15], 3); // Should output: 7
```

<details>
<summary>Solution</summary>

```php
function findKthSmallest(array $arr, int $k): int
{
    $n = count($arr);

    for ($i = 0; $i < $k; $i++) {
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

    return $arr[$k - 1];
}
```
</details>

## Key Takeaways

- **Bubble Sort**: Simple but O(n²), good for nearly sorted data with optimization
- **Selection Sort**: Always O(n²), but minimizes swaps
- Both are **in-place** algorithms (O(1) space)
- **Bubble sort is stable**, selection sort is not
- Use only for **small datasets** (< 100 elements)
- Understanding these helps you appreciate better algorithms

## What's Next

These simple sorts taught us the basics, but they're too slow for real-world use. In the next chapter, we'll learn **Insertion Sort and Merge Sort**—algorithms that are actually practical for larger datasets.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 05 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-05)**

Files included:
- `01-sorting-algorithms.php` - Complete sorting implementations including Bubble Sort (basic and optimized), Selection Sort, Cocktail Shaker Sort, visualizations, and performance benchmarks
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-05
php 01-sorting-algorithms.php
```

---

Continue to [Chapter 06: Insertion Sort & Merge Sort](/series/php-algorithms/chapters/06-insertion-sort-merge-sort).
