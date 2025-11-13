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

# Bubble Sort & Selection Sort

Now that we understand algorithm complexity and how to benchmark performance, let's dive into our first sorting algorithms. We'll start with two simple but inefficient sorting algorithms: **Bubble Sort** and **Selection Sort**.

While these algorithms aren't practical for large datasets, they're excellent learning tools that introduce fundamental sorting concepts.

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

---

Continue to [Chapter 06: Insertion Sort & Merge Sort](/series/php-algorithms/chapters/06-insertion-sort-merge-sort).
