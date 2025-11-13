---
title: "12: Binary Search"
description: "Master the efficient divide-and-conquer search algorithm. Implement iterative and recursive versions."
series: "php-algorithms"
chapter: 12
order: 12
difficulty: "Intermediate"
prerequisites:
  - "Understanding of Big O notation"
  - "Familiarity with recursion"
  - "Understanding of sorted arrays"
---

# Binary Search

Binary search is one of the most important algorithms every developer should know. It's a fast, elegant algorithm that searches sorted data by repeatedly dividing the search space in half. In this chapter, we'll master binary search and its many variations.

## The Problem with Linear Search

First, let's see why we need binary search:

```php
// Linear search: O(n)
function linearSearch(array $arr, int $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

// For 1,000,000 elements, might check 500,000 on average!
```

Linear search is simple but slow for large datasets. Binary search solves this.

## How Binary Search Works

**Key insight:** If the array is sorted, we can eliminate half the elements with each comparison!

**Algorithm:**
1. Start with the entire array
2. Check the middle element
3. If it's the target, done!
4. If target is smaller, search left half
5. If target is larger, search right half
6. Repeat until found or no elements left

**Example:** Find `37` in `[1, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47]`

```
[1, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47]
                      ↑
                     mid=19, target=37, go right

                        [23, 29, 31, 37, 41, 43, 47]
                              ↑
                            mid=37, FOUND!
```

Only 2 comparisons instead of potentially 15!

## Iterative Implementation

```php
function binarySearch(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        // Calculate middle index
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            return $mid; // Found!
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1; // Search right half
        } else {
            $right = $mid - 1; // Search left half
        }
    }

    return false; // Not found
}

$numbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
echo binarySearch($numbers, 13); // Output: 6
echo binarySearch($numbers, 8);  // Output: false
```

### Why Use (int)(($left + $right) / 2)?

```php
// Potential integer overflow for very large arrays
$mid = ($left + $right) / 2; // Could overflow if left + right > PHP_INT_MAX

// Better: avoid overflow
$mid = $left + (int)(($right - $left) / 2);

// Or in PHP (which handles big integers):
$mid = (int)(($left + $right) / 2); // Usually fine
```

## Recursive Implementation

```php
function binarySearchRecursive(
    array $arr,
    int $target,
    int $left = 0,
    int $right = null
): int|false {
    if ($right === null) {
        $right = count($arr) - 1;
    }

    // Base case: search space exhausted
    if ($left > $right) {
        return false;
    }

    $mid = $left + (int)(($right - $left) / 2);

    if ($arr[$mid] === $target) {
        return $mid;
    } elseif ($arr[$mid] < $target) {
        // Recursive case: search right half
        return binarySearchRecursive($arr, $target, $mid + 1, $right);
    } else {
        // Recursive case: search left half
        return binarySearchRecursive($arr, $target, $left, $mid - 1);
    }
}
```

**Note:** Iterative is generally preferred in PHP due to:
- No recursion overhead
- No stack space concerns
- Slightly faster

## Complexity Analysis

- **Time Complexity:** O(log n)
  - Each iteration halves the search space
  - log₂(1,000,000) ≈ 20 comparisons
  - Compare to linear search's 500,000!

- **Space Complexity:**
  - Iterative: O(1) - no extra space
  - Recursive: O(log n) - call stack depth

**Why log n?**
```
n elements → n/2 → n/4 → n/8 → ... → 1
How many halvings? log₂(n)
```

## Visualizing Binary Search

```php
function binarySearchVisualized(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $step = 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        // Visual representation
        echo "Step $step:\n";
        echo "  Search space: [" . implode(', ', array_slice($arr, $left, $right - $left + 1)) . "]\n";
        echo "  Checking index $mid: {$arr[$mid]}\n";

        if ($arr[$mid] === $target) {
            echo "  ✓ Found at index $mid!\n";
            return $mid;
        } elseif ($arr[$mid] < $target) {
            echo "  → Target is greater, search right half\n\n";
            $left = $mid + 1;
        } else {
            echo "  ← Target is smaller, search left half\n\n";
            $right = $mid - 1;
        }

        $step++;
    }

    echo "Not found\n";
    return false;
}

$numbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
binarySearchVisualized($numbers, 13);
```

**Output:**
```
Step 1:
  Search space: [1, 3, 5, 7, 9, 11, 13, 15, 17, 19]
  Checking index 4: 9
  → Target is greater, search right half

Step 2:
  Search space: [11, 13, 15, 17, 19]
  Checking index 7: 15
  ← Target is smaller, search left half

Step 3:
  Search space: [11, 13]
  Checking index 6: 13
  ✓ Found at index 6!
```

## Binary Search Variants

### 1. Find First Occurrence

```php
function findFirst(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $result = false;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $right = $mid - 1; // Continue searching left for first occurrence
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

$numbers = [1, 2, 2, 2, 3, 4, 5];
echo findFirst($numbers, 2); // Output: 1 (first occurrence)
```

### 2. Find Last Occurrence

```php
function findLast(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $result = false;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $left = $mid + 1; // Continue searching right for last occurrence
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

$numbers = [1, 2, 2, 2, 3, 4, 5];
echo findLast($numbers, 2); // Output: 3 (last occurrence)
```

### 3. Find Insertion Point

Find where to insert a value to maintain sorted order:

```php
function findInsertPosition(array $arr, int $target): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $left; // Insertion position
}

$numbers = [1, 3, 5, 7, 9];
echo findInsertPosition($numbers, 6); // Output: 3
// Insert 6 at index 3: [1, 3, 5, 6, 7, 9]
```

### 4. Count Occurrences

```php
function countOccurrences(array $arr, int $target): int
{
    $first = findFirst($arr, $target);

    if ($first === false) {
        return 0;
    }

    $last = findLast($arr, $target);
    return $last - $first + 1;
}

$numbers = [1, 2, 2, 2, 3, 4, 5];
echo countOccurrences($numbers, 2); // Output: 3
```

## Binary Search on Answer Space

Sometimes we binary search on possible answers rather than array indices:

### Square Root (Integer)

```php
function sqrtInteger(int $x): int
{
    if ($x < 2) return $x;

    $left = 1;
    $right = $x;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);
        $square = $mid * $mid;

        if ($square === $x) {
            return $mid;
        } elseif ($square < $x) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $right; // Return floor(sqrt(x))
}

echo sqrtInteger(16); // 4
echo sqrtInteger(20); // 4 (floor of 4.47)
```

### Find Peak Element

```php
function findPeakElement(array $nums): int
{
    $left = 0;
    $right = count($nums) - 1;

    while ($left < $right) {
        $mid = (int)(($left + $right) / 2);

        if ($nums[$mid] > $nums[$mid + 1]) {
            // Peak is on left side (including mid)
            $right = $mid;
        } else {
            // Peak is on right side
            $left = $mid + 1;
        }
    }

    return $left;
}

// Array with peak: [1, 2, 3, 1]
// Peak is at index 2 (value 3)
```

## Common Mistakes & Edge Cases

### Mistake 1: Infinite Loop

```php
// Wrong: infinite loop when target not found
while ($left < $right) { // Should be $left <= $right
    $mid = (int)(($left + $right) / 2);
    if ($arr[$mid] === $target) return $mid;
    elseif ($arr[$mid] < $target) $left = $mid; // Should be $mid + 1
    else $right = $mid; // Should be $mid - 1
}
```

### Mistake 2: Off-by-One Errors

```php
// Test edge cases:
$arr = [1];
binarySearch($arr, 1);  // Should find at index 0
binarySearch($arr, 2);  // Should return false

$arr = [1, 2];
binarySearch($arr, 1);  // Should find at index 0
binarySearch($arr, 2);  // Should find at index 1
```

### Mistake 3: Unsorted Array

```php
// Binary search ONLY works on sorted arrays!
$unsorted = [5, 2, 8, 1, 9];
binarySearch($unsorted, 8); // Wrong result! Must sort first!

sort($unsorted);
binarySearch($unsorted, 8); // Now correct
```

## Practical Applications

### 1. Autocomplete Search

```php
function autocomplete(array $sortedWords, string $prefix): array
{
    // Find first word with prefix
    $left = 0;
    $right = count($sortedWords) - 1;
    $start = -1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if (str_starts_with($sortedWords[$mid], $prefix)) {
            $start = $mid;
            $right = $mid - 1; // Find earliest match
        } elseif ($sortedWords[$mid] < $prefix) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    if ($start === -1) return [];

    // Collect all words with prefix
    $results = [];
    for ($i = $start; $i < count($sortedWords); $i++) {
        if (str_starts_with($sortedWords[$i], $prefix)) {
            $results[] = $sortedWords[$i];
        } else {
            break;
        }
    }

    return $results;
}

$words = ['apple', 'application', 'apply', 'banana', 'band'];
print_r(autocomplete($words, 'app'));
// Output: ['apple', 'application', 'apply']
```

### 2. Date Range Search

```php
class Event
{
    public function __construct(
        public string $name,
        public int $timestamp
    ) {}
}

function findEventsInRange(array $events, int $start, int $end): array
{
    // Find first event >= start
    $left = 0;
    $right = count($events) - 1;
    $startIdx = count($events);

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);
        if ($events[$mid]->timestamp >= $start) {
            $startIdx = $mid;
            $right = $mid - 1;
        } else {
            $left = $mid + 1;
        }
    }

    // Collect events until end
    $result = [];
    for ($i = $startIdx; $i < count($events) && $events[$i]->timestamp <= $end; $i++) {
        $result[] = $events[$i];
    }

    return $result;
}
```

## Benchmarking Binary vs Linear Search

```php
require_once 'Benchmark.php';

$bench = new Benchmark();
$sizes = [1000, 10000, 100000, 1000000];

foreach ($sizes as $size) {
    $data = range(1, $size);
    $target = $size - 100; // Near the end

    echo "Array size: $size\n";
    $bench->compare([
        'Linear Search' => fn($arr) => linearSearch($arr, $target),
        'Binary Search' => fn($arr) => binarySearch($arr, $target),
    ], $data, iterations: 100);
    echo "\n";
}
```

## Practice Exercises

### Exercise 1: Rotated Sorted Array

Search in a sorted array that has been rotated:

```php
function searchRotated(array $nums, int $target): int|false
{
    // Your code here
}

$nums = [4, 5, 6, 7, 0, 1, 2]; // Rotated [0,1,2,3,4,5,6,7]
echo searchRotated($nums, 0); // Should output: 4
```

<details>
<summary>Hint</summary>
One half is always sorted. Check which half is sorted, then decide which side to search.
</details>

### Exercise 2: Find Minimum in Rotated Array

```php
function findMin(array $nums): int
{
    // Your code here
}

echo findMin([4, 5, 6, 7, 0, 1, 2]); // Should output: 0
```

### Exercise 3: Search 2D Matrix

Search in a matrix where each row is sorted and first element of each row is greater than last element of previous row:

```php
function searchMatrix(array $matrix, int $target): bool
{
    // Your code here
}

$matrix = [
    [1, 3, 5, 7],
    [10, 11, 16, 20],
    [23, 30, 34, 60]
];
echo searchMatrix($matrix, 3) ? 'Found' : 'Not found';
```

## Key Takeaways

- **Binary search** is O(log n) - dramatically faster than linear search for sorted data
- **Requirements:** Array must be sorted
- **Iterative** implementation preferred in PHP over recursive
- Many **variants** exist: first/last occurrence, insertion point, etc.
- Can search on **answer space**, not just array indices
- Watch for **off-by-one errors** and **infinite loops**
- **Edge cases:** Empty array, single element, duplicates

## What's Next

In the next chapter, we'll explore **Hash Tables & Hash Functions**, learning about O(1) lookups and collision handling strategies.

---

Continue to [Chapter 13: Hash Tables & Hash Functions](/series/php-algorithms/chapters/13-hash-tables-hash-functions).
