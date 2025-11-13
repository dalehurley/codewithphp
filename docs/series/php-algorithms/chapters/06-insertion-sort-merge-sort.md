---
title: "06: Insertion Sort & Merge Sort"
description: "Explore more efficient sorting techniques. Understand divide-and-conquer strategies and stable sorting."
series: "php-algorithms"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites:
  - "Understanding of Big O notation"
  - "Completion of Chapter 05 (Bubble & Selection Sort)"
  - "Familiarity with recursion"
---

# Insertion Sort & Merge Sort

In this chapter, we'll explore two more sorting algorithms: **Insertion Sort**, which is simple and efficient for small arrays, and **Merge Sort**, our first O(n log n) sorting algorithm using divide-and-conquer.

## Insertion Sort

**Insertion Sort** builds the final sorted array one item at a time by inserting each element into its correct position. It's like sorting playing cards in your hand—you pick up cards one by one and insert each into its proper place.

### How It Works

Imagine sorting `[5, 2, 4, 6, 1, 3]`:

**Initial:** `[5] | 2, 4, 6, 1, 3` (first element is "sorted")

**Step 1:** Insert 2
`[2, 5] | 4, 6, 1, 3`

**Step 2:** Insert 4
`[2, 4, 5] | 6, 1, 3`

**Step 3:** Insert 6
`[2, 4, 5, 6] | 1, 3`

**Step 4:** Insert 1
`[1, 2, 4, 5, 6] | 3`

**Step 5:** Insert 3
`[1, 2, 3, 4, 5, 6]` Done!

### Implementation

```php
function insertionSort(array $arr): array
{
    $n = count($arr);

    // Start from second element (first is already "sorted")
    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];  // Element to insert
        $j = $i - 1;      // Start of sorted portion

        // Shift elements right to make space
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        // Insert key at correct position
        $arr[$j + 1] = $key;
    }

    return $arr;
}

$numbers = [5, 2, 4, 6, 1, 3];
print_r(insertionSort($numbers));
// Output: [1, 2, 3, 4, 5, 6]
```

### Complexity Analysis

- **Best case:** O(n) - array already sorted
- **Average case:** O(n²)
- **Worst case:** O(n²) - reverse sorted
- **Space:** O(1) - sorts in place
- **Stable:** Yes - maintains relative order of equal elements

**Why O(n²)?**
- Outer loop runs n times
- Inner loop can run up to i times
- Total: 1 + 2 + 3 + ... + n = n(n+1)/2 ≈ n²/2 → O(n²)

### When Insertion Sort Shines

Despite O(n²) complexity, insertion sort is excellent for:

1. **Small arrays** (< 50 elements)
2. **Nearly sorted data** (O(n) in best case!)
3. **Online sorting** (sorting as data arrives)
4. **Stable sorting** requirement

```php
// Hybrid approach: Use insertion sort for small subarrays
function smartSort(array $arr): array
{
    if (count($arr) < 20) {
        return insertionSort($arr); // Fast for small arrays!
    }
    return quickSort($arr); // Better for large arrays
}
```

### Visualizing Insertion Sort

```php
function insertionSortVisualized(array $arr): array
{
    $n = count($arr);
    echo "Initial: " . implode(', ', $arr) . "\n\n";

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        echo "Step $i: Inserting $key\n";
        echo "Before: " . implode(', ', $arr) . "\n";

        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        $arr[$j + 1] = $key;
        echo "After:  " . implode(', ', $arr) . "\n\n";
    }

    return $arr;
}

insertionSortVisualized([5, 2, 4, 6, 1, 3]);
```

## Merge Sort

**Merge Sort** is a divide-and-conquer algorithm that recursively divides the array into halves, sorts them, and merges them back together. It's one of the most efficient general-purpose sorting algorithms.

### The Divide-and-Conquer Strategy

1. **Divide:** Split array into two halves
2. **Conquer:** Recursively sort each half
3. **Combine:** Merge the sorted halves

```
[38, 27, 43, 3, 9, 82, 10]
         ↓ (divide)
[38, 27, 43, 3] | [9, 82, 10]
         ↓ (divide)
[38, 27] | [43, 3] | [9, 82] | [10]
         ↓ (divide)
[38] | [27] | [43] | [3] | [9] | [82] | [10]
         ↓ (merge & sort)
[27, 38] | [3, 43] | [9, 82] | [10]
         ↓ (merge & sort)
[3, 27, 38, 43] | [9, 10, 82]
         ↓ (merge & sort)
[3, 9, 10, 27, 38, 43, 82]
```

### Implementation

```php
function mergeSort(array $arr): array
{
    // Base case: array of 0 or 1 element is already sorted
    if (count($arr) <= 1) {
        return $arr;
    }

    // Divide: split array in half
    $mid = (int)(count($arr) / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    // Conquer: recursively sort both halves
    $left = mergeSort($left);
    $right = mergeSort($right);

    // Combine: merge sorted halves
    return merge($left, $right);
}

function merge(array $left, array $right): array
{
    $result = [];
    $i = $j = 0;

    // Compare elements from both arrays and add smaller one
    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i];
            $i++;
        } else {
            $result[] = $right[$j];
            $j++;
        }
    }

    // Add remaining elements from left array
    while ($i < count($left)) {
        $result[] = $left[$i];
        $i++;
    }

    // Add remaining elements from right array
    while ($j < count($right)) {
        $result[] = $right[$j];
        $j++;
    }

    return $result;
}

$numbers = [38, 27, 43, 3, 9, 82, 10];
print_r(mergeSort($numbers));
// Output: [3, 9, 10, 27, 38, 43, 82]
```

### Complexity Analysis

- **Best case:** O(n log n)
- **Average case:** O(n log n)
- **Worst case:** O(n log n)
- **Space:** O(n) - needs extra space for merging
- **Stable:** Yes

**Why O(n log n)?**
- Dividing takes log n levels (halve array each time)
- Merging at each level processes all n elements
- Total: n × log n = O(n log n)

### Merge Sort Advantages

1. **Guaranteed O(n log n)** - no worst case degradation
2. **Stable** - preserves order of equal elements
3. **Predictable performance** - same speed regardless of input
4. **Good for large datasets**
5. **Parallelizable** - can sort halves independently

### Merge Sort Disadvantages

1. **Extra space** - needs O(n) additional memory
2. **Not in-place** - creates new arrays
3. **Slower for small arrays** - overhead from recursion

### Visualizing Merge Sort

```php
function mergeSortVisualized(array $arr, int $depth = 0): array
{
    $indent = str_repeat('  ', $depth);

    if (count($arr) <= 1) {
        echo "{$indent}Base: [" . implode(', ', $arr) . "]\n";
        return $arr;
    }

    echo "{$indent}Divide: [" . implode(', ', $arr) . "]\n";

    $mid = (int)(count($arr) / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    $left = mergeSortVisualized($left, $depth + 1);
    $right = mergeSortVisualized($right, $depth + 1);

    $result = merge($left, $right);
    echo "{$indent}Merge: [" . implode(', ', $result) . "]\n";

    return $result;
}

mergeSortVisualized([38, 27, 43, 3]);
```

## Comparing Insertion Sort vs Merge Sort

Let's benchmark them:

```php
require_once 'Benchmark.php';

$bench = new Benchmark();
$sizes = [100, 500, 1000, 5000];

foreach ($sizes as $size) {
    $data = range(1, $size);
    shuffle($data);

    echo "Array size: $size\n";
    $bench->compare([
        'Insertion Sort' => fn($arr) => insertionSort($arr),
        'Merge Sort' => fn($arr) => mergeSort($arr),
        'PHP sort()' => function($arr) {
            sort($arr);
            return $arr;
        },
    ], $data, iterations: 10);
    echo "\n";
}
```

**Expected results:**
- Small arrays (< 100): Insertion sort competitive
- Medium arrays (100-1000): Merge sort starts winning
- Large arrays (> 1000): Merge sort significantly faster

## Practical Applications

### Insertion Sort: Real-World Use Case

```php
// Sorting user posts by timestamp (nearly sorted data)
class Post
{
    public function __construct(
        public string $content,
        public int $timestamp
    ) {}
}

function sortRecentPosts(array $posts): array
{
    // Posts are nearly sorted (new posts added at end)
    // Insertion sort is O(n) for nearly sorted data!

    $n = count($posts);

    for ($i = 1; $i < $n; $i++) {
        $key = $posts[$i];
        $j = $i - 1;

        while ($j >= 0 && $posts[$j]->timestamp < $key->timestamp) {
            $posts[$j + 1] = $posts[$j];
            $j--;
        }

        $posts[$j + 1] = $key;
    }

    return $posts;
}
```

### Merge Sort: External Sorting

Merge sort is excellent for sorting data that doesn't fit in memory:

```php
// Simplified external sort for large files
function externalMergeSort(string $inputFile, string $outputFile): void
{
    // 1. Divide file into chunks that fit in memory
    $chunks = divideIntoChunks($inputFile, 1000);

    // 2. Sort each chunk using merge sort
    $sortedChunks = [];
    foreach ($chunks as $chunk) {
        $sorted = mergeSort($chunk);
        $sortedChunks[] = $sorted;
    }

    // 3. Merge all sorted chunks
    $final = mergeSortedChunks($sortedChunks);

    // 4. Write to output file
    file_put_contents($outputFile, implode("\n", $final));
}
```

## Optimizing Merge Sort

### Optimization 1: Use Insertion Sort for Small Subarrays

```php
function mergeSortOptimized(array $arr): array
{
    // Use insertion sort for small arrays
    if (count($arr) < 20) {
        return insertionSort($arr);
    }

    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = mergeSortOptimized(array_slice($arr, 0, $mid));
    $right = mergeSortOptimized(array_slice($arr, $mid));

    return merge($left, $right);
}
```

### Optimization 2: Skip Merge if Already Sorted

```php
function mergeSortSkipMerge(array $arr): array
{
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = mergeSortSkipMerge(array_slice($arr, 0, $mid));
    $right = mergeSortSkipMerge(array_slice($arr, $mid));

    // If last of left ≤ first of right, already sorted!
    if (end($left) <= reset($right)) {
        return array_merge($left, $right);
    }

    return merge($left, $right);
}
```

## Practice Exercises

### Exercise 1: Count Inversions

Use merge sort to count inversions (pairs where i < j but arr[i] > arr[j]):

```php
function countInversions(array $arr): int
{
    // Your code here
}

echo countInversions([2, 4, 1, 3, 5]); // Should output: 3
// Inversions: (2,1), (4,1), (4,3)
```

### Exercise 2: K-Way Merge

Merge k sorted arrays into one sorted array:

```php
function mergeKSortedArrays(array $arrays): array
{
    // Your code here
}

$arrays = [
    [1, 4, 7],
    [2, 5, 8],
    [3, 6, 9]
];
print_r(mergeKSortedArrays($arrays));
// Should output: [1, 2, 3, 4, 5, 6, 7, 8, 9]
```

### Exercise 3: Sort Linked List

Implement merge sort for a linked list (bonus challenge):

```php
class ListNode
{
    public function __construct(
        public int $val,
        public ?ListNode $next = null
    ) {}
}

function sortList(?ListNode $head): ?ListNode
{
    // Your code here
}
```

## Key Takeaways

- **Insertion Sort**: O(n²) but excellent for small or nearly sorted arrays, stable, in-place
- **Merge Sort**: O(n log n) always, stable, requires O(n) space, divide-and-conquer
- **Insertion sort** is fast for small datasets despite O(n²) complexity
- **Merge sort** guarantees O(n log n) regardless of input
- **Hybrid approaches** combining both can be very effective
- **Stability** matters when sorting objects with multiple fields

## What's Next

In the next chapter, we'll explore **Quick Sort**, one of the fastest sorting algorithms in practice, along with pivot selection strategies.

---

Continue to [Chapter 07: Quick Sort & Pivot Strategies](/series/php-algorithms/chapters/07-quick-sort-pivot-strategies).
