---
title: "07: Quick Sort & Pivot Strategies"
description: "Master one of the most popular sorting algorithms. Learn partitioning, pivot selection, and optimization techniques."
series: "php-algorithms"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "Understanding of recursion"
  - "Understanding of Big O notation"
  - "Completion of Chapters 05-06"
---

# Quick Sort & Pivot Strategies

Quick Sort is one of the most important and widely used sorting algorithms. Despite having O(n²) worst-case complexity, it's typically faster than other O(n log n) algorithms in practice due to excellent cache locality and low overhead. In this chapter, we'll master Quick Sort and learn how to optimize it with smart pivot selection.

## How Quick Sort Works

Quick Sort uses a **divide-and-conquer** strategy:

1. **Pick a pivot** element from the array
2. **Partition** the array so elements smaller than pivot are on the left, larger on the right
3. **Recursively sort** the left and right partitions

**Key insight:** After partitioning, the pivot is in its final sorted position!

### Example

Sort `[8, 3, 1, 7, 0, 10, 2]` using last element as pivot:

```
[8, 3, 1, 7, 0, 10, 2]  pivot=2
    ↓ partition
[1, 0, 2, 7, 8, 10, 3]  pivot now at index 2

Recursively sort left [1, 0] and right [7, 8, 10, 3]

[1, 0]  pivot=0
[0, 1]  done!

[7, 8, 10, 3]  pivot=3
[3, 7, 8, 10]  done!

Final: [0, 1, 2, 3, 7, 8, 10]
```

## Basic Implementation

```php
function quickSort(array $arr): array
{
    // Base case: arrays with 0 or 1 element are already sorted
    if (count($arr) < 2) {
        return $arr;
    }

    // Choose pivot (simple: use first element)
    $pivot = $arr[0];
    $left = [];
    $right = [];

    // Partition: elements < pivot go left, >= pivot go right
    for ($i = 1; $i < count($arr); $i++) {
        if ($arr[$i] < $pivot) {
            $left[] = $arr[$i];
        } else {
            $right[] = $arr[$i];
        }
    }

    // Recursively sort and combine
    return array_merge(
        quickSort($left),
        [$pivot],
        quickSort($right)
    );
}

$numbers = [8, 3, 1, 7, 0, 10, 2];
print_r(quickSort($numbers));
// Output: [0, 1, 2, 3, 7, 8, 10]
```

**Note:** This implementation is easy to understand but not space-efficient (creates new arrays). Let's build an in-place version.

## In-Place Quick Sort

In-place sorting uses O(1) extra space by partitioning within the original array:

```php
function quickSortInPlace(array &$arr, int $low = 0, int $high = null): void
{
    if ($high === null) {
        $high = count($arr) - 1;
    }

    if ($low < $high) {
        // Partition and get pivot index
        $pivotIndex = partition($arr, $low, $high);

        // Recursively sort left and right partitions
        quickSortInPlace($arr, $low, $pivotIndex - 1);
        quickSortInPlace($arr, $pivotIndex + 1, $high);
    }
}

function partition(array &$arr, int $low, int $high): int
{
    // Choose last element as pivot
    $pivot = $arr[$high];
    $i = $low - 1; // Index of smaller element

    for ($j = $low; $j < $high; $j++) {
        // If current element is smaller than pivot
        if ($arr[$j] < $pivot) {
            $i++;
            // Swap arr[i] and arr[j]
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    // Place pivot in correct position
    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];

    return $i + 1;
}

// Usage
$numbers = [8, 3, 1, 7, 0, 10, 2];
quickSortInPlace($numbers);
print_r($numbers);
// Output: [0, 1, 2, 3, 7, 8, 10]
```

### Understanding the Partition Algorithm

The partition process moves elements around the pivot:

```php
function partitionVisualized(array &$arr, int $low, int $high): int
{
    $pivot = $arr[$high];
    echo "Pivot: $pivot\n";
    echo "Initial: [" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";

    $i = $low - 1;

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            echo "  Swap {$arr[$i]} and {$arr[$j]}\n";
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    // Place pivot
    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    echo "After partition: [" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";
    echo "Pivot at index: " . ($i + 1) . "\n\n";

    return $i + 1;
}
```

## Complexity Analysis

- **Best case:** O(n log n) - pivot always divides array evenly
- **Average case:** O(n log n)
- **Worst case:** O(n²) - already sorted array with bad pivot choice
- **Space:** O(log n) - recursion stack (in-place version)

**Why O(n log n) average?**
- With good pivot selection, we divide array roughly in half
- log n levels of recursion
- Each level processes all n elements
- Total: n × log n

**Why O(n²) worst case?**
- If pivot is always smallest/largest element
- Array divided into 0 and n-1 elements
- n levels of recursion instead of log n
- Total: n × n = n²

## Pivot Selection Strategies

The pivot choice dramatically affects performance. Let's explore different strategies:

### Strategy 1: First Element (Poor)

```php
function quickSortFirstPivot(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pivotIndex = partitionFirst($arr, $low, $high);
        quickSortFirstPivot($arr, $low, $pivotIndex - 1);
        quickSortFirstPivot($arr, $pivotIndex + 1, $high);
    }
}

function partitionFirst(array &$arr, int $low, int $high): int
{
    // Swap first with last to use it as pivot
    [$arr[$low], $arr[$high]] = [$arr[$high], $arr[$low]];
    return partition($arr, $low, $high);
}
```

**Problem:** O(n²) on already sorted or reverse sorted arrays!

### Strategy 2: Random Pivot (Good)

```php
function quickSortRandom(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pivotIndex = partitionRandom($arr, $low, $high);
        quickSortRandom($arr, $low, $pivotIndex - 1);
        quickSortRandom($arr, $pivotIndex + 1, $high);
    }
}

function partitionRandom(array &$arr, int $low, int $high): int
{
    // Pick random index as pivot
    $randomIndex = rand($low, $high);

    // Swap with last position
    [$arr[$randomIndex], $arr[$high]] = [$arr[$high], $arr[$randomIndex]];

    return partition($arr, $low, $high);
}
```

**Advantage:** Average O(n log n) even on sorted data!

### Strategy 3: Median-of-Three (Best)

Choose median of first, middle, and last elements:

```php
function quickSortMedianOfThree(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pivotIndex = partitionMedianOfThree($arr, $low, $high);
        quickSortMedianOfThree($arr, $low, $pivotIndex - 1);
        quickSortMedianOfThree($arr, $pivotIndex + 1, $high);
    }
}

function partitionMedianOfThree(array &$arr, int $low, int $high): int
{
    $mid = (int)(($low + $high) / 2);

    // Order first, middle, last
    if ($arr[$mid] < $arr[$low]) {
        [$arr[$low], $arr[$mid]] = [$arr[$mid], $arr[$low]];
    }
    if ($arr[$high] < $arr[$low]) {
        [$arr[$low], $arr[$high]] = [$arr[$high], $arr[$low]];
    }
    if ($arr[$high] < $arr[$mid]) {
        [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];
    }

    // Now: arr[low] <= arr[mid] <= arr[high]
    // Use middle element as pivot
    [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];

    return partition($arr, $low, $high);
}
```

**Advantage:** Better pivot selection leads to more balanced partitions!

## Optimizations

### Optimization 1: Switch to Insertion Sort for Small Subarrays

```php
function quickSortOptimized(array &$arr, int $low, int $high): void
{
    // Use insertion sort for small subarrays
    if ($high - $low < 10) {
        insertionSortRange($arr, $low, $high);
        return;
    }

    if ($low < $high) {
        $pivotIndex = partitionMedianOfThree($arr, $low, $high);
        quickSortOptimized($arr, $low, $pivotIndex - 1);
        quickSortOptimized($arr, $pivotIndex + 1, $high);
    }
}

function insertionSortRange(array &$arr, int $low, int $high): void
{
    for ($i = $low + 1; $i <= $high; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        while ($j >= $low && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        $arr[$j + 1] = $key;
    }
}
```

### Optimization 2: Three-Way Partitioning (Dutch National Flag)

Handle duplicate elements efficiently:

```php
function quickSort3Way(array &$arr, int $low, int $high): void
{
    if ($low >= $high) return;

    [$lt, $gt] = partition3Way($arr, $low, $high);

    // Elements equal to pivot are in arr[lt..gt]
    quickSort3Way($arr, $low, $lt - 1);
    quickSort3Way($arr, $gt + 1, $high);
}

function partition3Way(array &$arr, int $low, int $high): array
{
    $pivot = $arr[$low];
    $lt = $low;      // arr[low..lt-1] < pivot
    $i = $low + 1;   // arr[lt..i-1] == pivot
    $gt = $high;     // arr[gt+1..high] > pivot

    while ($i <= $gt) {
        if ($arr[$i] < $pivot) {
            [$arr[$lt], $arr[$i]] = [$arr[$i], $arr[$lt]];
            $lt++;
            $i++;
        } elseif ($arr[$i] > $pivot) {
            [$arr[$i], $arr[$gt]] = [$arr[$gt], $arr[$i]];
            $gt--;
        } else {
            $i++;
        }
    }

    return [$lt, $gt];
}

// Excellent for arrays with many duplicates!
$numbers = [5, 2, 8, 2, 9, 1, 5, 5];
quickSort3Way($numbers, 0, count($numbers) - 1);
```

### Optimization 3: Tail Call Optimization

```php
function quickSortTailOptimized(array &$arr, int $low, int $high): void
{
    while ($low < $high) {
        // Use median-of-three
        $pivotIndex = partitionMedianOfThree($arr, $low, $high);

        // Recurse on smaller partition, iterate on larger
        if ($pivotIndex - $low < $high - $pivotIndex) {
            quickSortTailOptimized($arr, $low, $pivotIndex - 1);
            $low = $pivotIndex + 1; // Tail recursion eliminated
        } else {
            quickSortTailOptimized($arr, $pivotIndex + 1, $high);
            $high = $pivotIndex - 1; // Tail recursion eliminated
        }
    }
}
```

## Visualizing Quick Sort

```php
function quickSortVisualized(array &$arr, int $low, int $high, int $depth = 0): void
{
    $indent = str_repeat('  ', $depth);

    if ($low < $high) {
        echo $indent . "Sorting: [" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";

        $pivotIndex = partition($arr, $low, $high);

        echo $indent . "After partition (pivot={$arr[$pivotIndex]}): [" .
             implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n\n";

        quickSortVisualized($arr, $low, $pivotIndex - 1, $depth + 1);
        quickSortVisualized($arr, $pivotIndex + 1, $high, $depth + 1);
    }
}

$numbers = [8, 3, 1, 7, 0, 10, 2];
quickSortVisualized($numbers, 0, count($numbers) - 1);
print_r($numbers);
```

## Quick Sort vs Merge Sort

| Feature | Quick Sort | Merge Sort |
|---------|-----------|------------|
| **Average time** | O(n log n) | O(n log n) |
| **Worst time** | O(n²) | O(n log n) |
| **Space** | O(log n) | O(n) |
| **In-place** | Yes | No |
| **Stable** | No* | Yes |
| **Cache locality** | Excellent | Good |
| **Typical speed** | Faster | Slower |

*Quick sort can be made stable but with performance penalty

**When to use Quick Sort:**
- General-purpose sorting
- When average case performance matters
- When space is limited
- When in-place sorting is needed

**When to use Merge Sort:**
- Need guaranteed O(n log n)
- Stability is important
- Sorting linked lists
- External sorting (large datasets)

## Practical Applications

### 1. Finding Kth Smallest Element (QuickSelect)

```php
function quickSelect(array &$arr, int $k): mixed
{
    return quickSelectHelper($arr, 0, count($arr) - 1, $k - 1);
}

function quickSelectHelper(array &$arr, int $low, int $high, int $k): mixed
{
    if ($low === $high) {
        return $arr[$low];
    }

    $pivotIndex = partitionRandom($arr, $low, $high);

    if ($k === $pivotIndex) {
        return $arr[$k];
    } elseif ($k < $pivotIndex) {
        return quickSelectHelper($arr, $low, $pivotIndex - 1, $k);
    } else {
        return quickSelectHelper($arr, $pivotIndex + 1, $high, $k);
    }
}

$numbers = [7, 10, 4, 3, 20, 15];
echo "3rd smallest: " . quickSelect($numbers, 3); // Output: 7
// Average O(n) instead of O(n log n) for sorting!
```

### 2. Sorting Objects by Multiple Criteria

```php
class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $rating
    ) {}
}

function quickSortProducts(array &$products, int $low, int $high): void
{
    if ($low < $high) {
        $pivotIndex = partitionProducts($products, $low, $high);
        quickSortProducts($products, $low, $pivotIndex - 1);
        quickSortProducts($products, $pivotIndex + 1, $high);
    }
}

function partitionProducts(array &$products, int $low, int $high): int
{
    $pivot = $products[$high];
    $i = $low - 1;

    for ($j = $low; $j < $high; $j++) {
        // Sort by rating (descending), then price (ascending)
        if ($products[$j]->rating > $pivot->rating ||
            ($products[$j]->rating === $pivot->rating &&
             $products[$j]->price < $pivot->price)) {
            $i++;
            [$products[$i], $products[$j]] = [$products[$j], $products[$i]];
        }
    }

    [$products[$i + 1], $products[$high]] = [$products[$high], $products[$i + 1]];
    return $i + 1;
}

$products = [
    new Product('A', 10.99, 4),
    new Product('B', 15.99, 5),
    new Product('C', 12.99, 4),
];

quickSortProducts($products, 0, count($products) - 1);
```

## Benchmarking Pivot Strategies

```php
require_once 'Benchmark.php';

$bench = new Benchmark();
$sizes = [1000, 5000, 10000];

foreach ($sizes as $size) {
    echo "Array size: $size\n";

    // Random data
    $random = range(1, $size);
    shuffle($random);

    $bench->compare([
        'First Pivot' => function($arr) {
            quickSortFirstPivot($arr, 0, count($arr) - 1);
        },
        'Random Pivot' => function($arr) {
            quickSortRandom($arr, 0, count($arr) - 1);
        },
        'Median-of-Three' => function($arr) {
            quickSortMedianOfThree($arr, 0, count($arr) - 1);
        },
    ], $random, iterations: 10);

    echo "\n";
}
```

## Common Mistakes

### Mistake 1: Not Handling Single Element

```php
// Wrong: infinite recursion on single element
if (count($arr) == 0) return $arr;

// Correct
if (count($arr) < 2) return $arr;
```

### Mistake 2: Including Pivot in Both Partitions

```php
// Wrong: pivot included twice
quickSort($arr, $low, $pivotIndex);     // Includes pivot
quickSort($arr, $pivotIndex, $high);    // Includes pivot again

// Correct: exclude pivot
quickSort($arr, $low, $pivotIndex - 1);
quickSort($arr, $pivotIndex + 1, $high);
```

### Mistake 3: Poor Pivot for Sorted Data

Always use randomized or median-of-three pivots to avoid O(n²) on sorted data.

## Practice Exercises

### Exercise 1: Sort Colors (Dutch National Flag)

Sort an array of 0s, 1s, and 2s in one pass:

```php
function sortColors(array &$nums): void
{
    // Your code here (use 3-way partitioning)
}

$colors = [2, 0, 2, 1, 1, 0];
sortColors($colors);
// Result: [0, 0, 1, 1, 2, 2]
```

### Exercise 2: Nuts and Bolts Problem

Match nuts and bolts of different sizes:

```php
function matchNutsAndBolts(array &$nuts, array &$bolts): void
{
    // Your code here (modify quick sort)
}
```

### Exercise 3: Wiggle Sort

Rearrange array so arr[0] <= arr[1] >= arr[2] <= arr[3]...:

```php
function wiggleSort(array &$nums): void
{
    // Your code here
}
```

## Key Takeaways

- **Quick Sort** is O(n log n) average, O(n²) worst case
- **Pivot selection** is crucial for performance
- **Median-of-three** or **random pivot** avoids worst case
- **In-place** sorting with O(log n) space
- **Three-way partitioning** excellent for duplicates
- **Quick Select** finds kth element in O(n) average
- Generally **faster than merge sort** in practice
- Not stable, but cache-efficient

## What's Next

In the next chapter, we'll explore **Heap Sort & Priority Queues**, learning about the heap data structure and how to use it for efficient sorting and priority management.

---

Continue to [Chapter 08: Heap Sort & Priority Queues](/series/php-algorithms/chapters/08-heap-sort-priority-queues).
