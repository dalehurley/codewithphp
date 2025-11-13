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
- Compare 2 with 5: 2 < 5, so shift 5 right
- Insert 2 at position 0
- Result: `[2, 5] | 4, 6, 1, 3`

**Step 2:** Insert 4
- Compare 4 with 5: 4 < 5, so shift 5 right
- Compare 4 with 2: 4 > 2, so insert after 2
- Result: `[2, 4, 5] | 6, 1, 3`

**Step 3:** Insert 6
- Compare 6 with 5: 6 > 5, so no shifts needed
- Insert 6 at end
- Result: `[2, 4, 5, 6] | 1, 3`

**Step 4:** Insert 1
- Compare 1 with 6: shift 6 right
- Compare 1 with 5: shift 5 right
- Compare 1 with 4: shift 4 right
- Compare 1 with 2: shift 2 right
- Insert 1 at position 0
- Result: `[1, 2, 4, 5, 6] | 3`

**Step 5:** Insert 3
- Compare 3 with 6: shift 6 right
- Compare 3 with 5: shift 5 right
- Compare 3 with 4: shift 4 right
- Compare 3 with 2: 3 > 2, insert after 2
- Result: `[1, 2, 3, 4, 5, 6]` Done!

**Animation Concept:**
Imagine cards being picked one at a time from an unsorted pile and inserted into the correct position in your hand. Each new card slides into place by comparing with existing cards from right to left.

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

| Scenario | Time Complexity | Why? | Example |
|----------|-----------------|------|---------|
| **Best case** | O(n) | Array already sorted, no shifts needed | [1, 2, 3, 4, 5] |
| **Average case** | O(n²) | Elements randomly distributed | [3, 1, 4, 2, 5] |
| **Worst case** | O(n²) | Array reverse sorted, maximum shifts | [5, 4, 3, 2, 1] |
| **Space** | O(1) | In-place, only temporary variables | - |
| **Stable** | Yes | Equal elements maintain relative order | [3a, 1, 3b] → [1, 3a, 3b] |

**Why O(n²) in Average/Worst Case?**
- Outer loop runs **n** times (for each element)
- Inner loop runs up to **i** times (comparing with sorted portion)
- Total comparisons: 1 + 2 + 3 + ... + n = **n(n+1)/2 ≈ n²/2** → **O(n²)**

**Why O(n) in Best Case?**
- If array is already sorted, inner loop never executes
- Only n comparisons, no shifts
- Total: **n comparisons** → **O(n)**

**Memory Usage:**
- **In-place sorting:** Original array modified directly
- **Auxiliary space:** O(1) - only needs a few variables (key, i, j)
- **No extra arrays or recursion stack**

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

### Edge Cases and Special Scenarios

**1. Empty or Single Element Array**
```php
insertionSort([]);        // Returns: []
insertionSort([42]);      // Returns: [42]
// No comparisons needed!
```

**2. Already Sorted Array (Best Case)**
```php
$sorted = [1, 2, 3, 4, 5];
// Only n comparisons, no shifts
// Runs in O(n) time!
```

**3. Reverse Sorted Array (Worst Case)**
```php
$reversed = [5, 4, 3, 2, 1];
// Each element requires maximum shifts
// Total: n²/2 comparisons and shifts
```

**4. All Equal Elements**
```php
$equal = [5, 5, 5, 5, 5];
// n comparisons, no shifts
// O(n) time - very efficient!
```

**5. Array with Duplicates (Stability Test)**
```php
class Item {
    public function __construct(public int $value, public string $id) {}
}

$items = [
    new Item(3, 'a'),
    new Item(1, 'b'),
    new Item(3, 'c'),
];

insertionSort($items);
// Maintains order: Item(1,'b'), Item(3,'a'), Item(3,'c')
// 3a comes before 3c (stable sort)
```

**6. Nearly Sorted Array with Few Swaps**
```php
$nearlySorted = [1, 2, 3, 10, 5, 6, 7, 8, 9];
// Only element 10 out of place
// Very fast: ~O(n) performance
```

### Performance Characteristics Table

| Array Size | Random Data | Sorted Data | Reverse Sorted | Nearly Sorted |
|------------|-------------|-------------|----------------|---------------|
| 10 | 0.01ms | 0.005ms | 0.02ms | 0.006ms |
| 50 | 0.25ms | 0.02ms | 0.50ms | 0.03ms |
| 100 | 1.0ms | 0.05ms | 2.0ms | 0.06ms |
| 500 | 25ms | 0.25ms | 50ms | 0.30ms |
| 1000 | 100ms | 0.5ms | 200ms | 0.6ms |

**Key Observations:**
- **Sorted data:** Lightning fast, linear time
- **Nearly sorted:** Almost as fast as sorted
- **Random/Reverse:** Quadratic slowdown
- **Cutoff point:** ~50 elements for practical use

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

### Detailed Step-by-Step Walkthrough

Let's trace through merging `[27, 38]` and `[3, 43]`:

```
Left:  [27, 38]  (i=0)
Right: [3, 43]   (j=0)
Result: []

Step 1: Compare 27 vs 3
  → 3 < 27, take 3 from right
  → Result: [3]
  → Left: [27, 38] (i=0), Right: [43] (j=1)

Step 2: Compare 27 vs 43
  → 27 < 43, take 27 from left
  → Result: [3, 27]
  → Left: [38] (i=1), Right: [43] (j=1)

Step 3: Compare 38 vs 43
  → 38 < 43, take 38 from left
  → Result: [3, 27, 38]
  → Left: [] (i=2), Right: [43] (j=1)

Step 4: Left exhausted, append remaining right
  → Result: [3, 27, 38, 43]
```

**Animation Concept:**
Visualize two sorted stacks of cards. Always compare the top cards from both stacks and take the smaller one, building a new sorted stack. When one stack empties, append the remaining cards from the other stack.

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

| Scenario | Time Complexity | Space Complexity | Why? |
|----------|-----------------|------------------|------|
| **Best case** | O(n log n) | O(n) | Always divides and merges all elements |
| **Average case** | O(n log n) | O(n) | Same as best - no shortcuts |
| **Worst case** | O(n log n) | O(n) | Guaranteed performance! |
| **Stable** | Yes | - | Equal elements maintain order |

**Why O(n log n) for ALL Cases?**

1. **Divide Phase:**
   - Split array in half repeatedly
   - log₂(n) levels of recursion
   - Example: 8 elements → 3 levels (8→4→2→1)

2. **Merge Phase:**
   - At each level, merge processes all n elements
   - Level 1: 4 merges of 2 elements = 8 comparisons
   - Level 2: 2 merges of 4 elements = 8 comparisons
   - Level 3: 1 merge of 8 elements = 8 comparisons

3. **Total Work:**
   - log n levels × n work per level = **O(n log n)**

**Why O(n) Space?**
- Need temporary arrays for merging
- Maximum n/2 + n/2 = n elements stored simultaneously
- Cannot sort in-place like quick sort

**Visual Recursion Tree:**
```
                    [8 elements]           ← n work
                   /            \
            [4 elements]      [4 elements]  ← n work total
           /         \        /         \
        [2]  [2]    [2]  [2]             ← n work total
       / \   / \   / \   / \
      [1][1][1][1][1][1][1][1]            ← n work total

      Total levels: log₂(8) = 3
      Work per level: n
      Total work: 3n = O(n log n)
```

**Memory Layout During Merge:**
```php
// Merging [3, 27, 38, 43] and [9, 10, 82]
// Needs temporary space:
$left  = [3, 27, 38, 43];  // n/2 space
$right = [9, 10, 82];       // n/2 space
$result = [];               // n space (while building)

// Total auxiliary space: O(n)
```

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

### Edge Cases and Special Scenarios

**1. Empty or Single Element**
```php
mergeSort([]);       // Returns: []
mergeSort([42]);     // Returns: [42]
// Base case - no division or merging needed
```

**2. Two Elements**
```php
mergeSort([5, 2]);   // Returns: [2, 5]
// Minimal division: [5] | [2] → merge → [2, 5]
```

**3. Already Sorted Array**
```php
$sorted = [1, 2, 3, 4, 5];
// Still O(n log n) - no shortcuts!
// Always divides and merges, even if sorted
```

**4. Reverse Sorted Array**
```php
$reversed = [5, 4, 3, 2, 1];
// Also O(n log n) - same work as sorted
// Merge sort doesn't care about initial order
```

**5. All Equal Elements**
```php
$equal = [5, 5, 5, 5, 5];
// O(n log n) - still divides and merges
// No optimizations for duplicates
```

**6. Stability with Duplicates**
```php
class Item {
    public function __construct(public int $value, public string $id) {}
}

$items = [
    new Item(3, 'first'),
    new Item(1, 'middle'),
    new Item(3, 'last'),
];

mergeSort($items);
// Maintains order: Item(1,'middle'), Item(3,'first'), Item(3,'last')
// Stable: 'first' stays before 'last'
```

### Performance Characteristics Table

| Array Size | Random | Sorted | Reverse | Nearly Sorted |
|------------|--------|--------|---------|---------------|
| 10 | 0.02ms | 0.02ms | 0.02ms | 0.02ms |
| 50 | 0.12ms | 0.12ms | 0.12ms | 0.12ms |
| 100 | 0.25ms | 0.25ms | 0.25ms | 0.25ms |
| 500 | 1.5ms | 1.5ms | 1.5ms | 1.5ms |
| 1000 | 3.2ms | 3.2ms | 3.2ms | 3.2ms |
| 5000 | 18ms | 18ms | 18ms | 18ms |

**Key Observations:**
- **Predictable:** Same time for all input patterns
- **Guaranteed:** Always O(n log n), no worst case surprise
- **Consistent:** Performance doesn't vary with data distribution
- **Trade-off:** Uses O(n) extra space for predictability

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

### Comprehensive Comparison Table

| Feature | Insertion Sort | Merge Sort |
|---------|----------------|------------|
| **Best Time** | O(n) | O(n log n) |
| **Average Time** | O(n²) | O(n log n) |
| **Worst Time** | O(n²) | O(n log n) |
| **Space** | O(1) | O(n) |
| **Stable** | Yes | Yes |
| **In-Place** | Yes | No |
| **Adaptive** | Yes (fast on sorted) | No (always same) |
| **Online** | Yes (sort as data arrives) | No |
| **Recursion** | No | Yes |
| **Cache Friendly** | Very | Moderate |

### When to Use Each

**Use Insertion Sort When:**
- Array size < 50 elements
- Data is nearly sorted (huge advantage!)
- Memory writes are expensive
- Simplicity is important
- Sorting data as it arrives (online)
- Need stable sort with O(1) space

**Use Merge Sort When:**
- Need guaranteed O(n log n)
- Array size > 1000 elements
- Stability is required
- Don't care about extra O(n) space
- Sorting linked lists (natural fit)
- Need predictable performance

### Performance Benchmark Results

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

**Expected Results (Random Data):**

| Array Size | Insertion Sort | Merge Sort | PHP sort() | Winner |
|------------|----------------|------------|------------|--------|
| 10 | 0.01ms | 0.02ms | 0.005ms | PHP |
| 50 | 0.25ms | 0.12ms | 0.02ms | PHP |
| 100 | 1.0ms | 0.25ms | 0.05ms | Merge |
| 500 | 25ms | 1.5ms | 0.3ms | Merge |
| 1000 | 100ms | 3.2ms | 0.6ms | Merge |
| 5000 | 2500ms | 18ms | 3.5ms | Merge |

**Expected Results (Nearly Sorted Data):**

| Array Size | Insertion Sort | Merge Sort | Winner |
|------------|----------------|------------|--------|
| 100 | 0.05ms | 0.25ms | Insertion |
| 500 | 0.30ms | 1.5ms | Insertion |
| 1000 | 0.6ms | 3.2ms | Insertion |
| 5000 | 3.0ms | 18ms | Insertion |

**Key Insights:**
- **Small arrays (< 50):** Insertion sort or PHP built-in
- **Medium arrays (100-1000):** Merge sort wins
- **Large arrays (> 1000):** Merge sort dominates
- **Nearly sorted:** Insertion sort is king!
- **PHP sort():** Optimized hybrid, usually best choice

### Real-World Decision Matrix

```
Is array size < 50?
├─ Yes → Insertion Sort
└─ No
   ├─ Is data nearly sorted?
   │  ├─ Yes → Insertion Sort (O(n) performance!)
   │  └─ No → Merge Sort
   └─ Need guaranteed O(n log n)?
      ├─ Yes → Merge Sort
      └─ No → Consider Quick Sort (next chapter)
```

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

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 06 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-06)**

Files included:
- `01-insertion-sort.php` - Complete insertion sort implementation with visualization and analysis
- `02-merge-sort.php` - Complete merge sort implementation with divide-and-conquer visualization
- `03-insertion-sort-practical.php` - Real-world applications where insertion sort excels
- `04-merge-sort-optimized.php` - Optimized merge sort with hybrid approach
- `05-comparison-demo.php` - Direct head-to-head comparison of insertion vs merge sort
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-06
php 01-insertion-sort.php
```

---

Continue to [Chapter 07: Quick Sort & Pivot Strategies](/series/php-algorithms/chapters/07-quick-sort-pivot-strategies).
