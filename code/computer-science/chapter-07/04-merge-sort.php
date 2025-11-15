<?php

declare(strict_types=1);

/**
 * Merge Sort - O(n log n) Divide-and-Conquer Algorithm
 *
 * Demonstrates:
 * - Recursive merge sort implementation
 * - Divide and conquer strategy
 * - Merging two sorted arrays
 * - Guaranteed O(n log n) performance
 * - Stable sorting
 * - External sorting capability
 */

echo "=== Merge Sort ===\n\n";

echo "Concept: Divide and Conquer\n";
echo "         1. Divide array into two halves\n";
echo "         2. Recursively sort each half\n";
echo "         3. Merge the sorted halves\n\n";

$mergeCalls = 0;
$comparisons = 0;

/**
 * Merge sort implementation
 */
function mergeSort(array $arr): array
{
    $n = count($arr);

    if ($n <= 1) {
        return $arr;
    }

    // Divide
    $mid = (int)($n / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    // Conquer (recursive sort)
    $left = mergeSort($left);
    $right = mergeSort($right);

    // Combine (merge)
    return merge($left, $right);
}

/**
 * Merge two sorted arrays
 */
function merge(array $left, array $right): array
{
    global $mergeCalls, $comparisons;
    $mergeCalls++;

    $result = [];
    $i = 0;
    $j = 0;

    // Merge while both arrays have elements
    while ($i < count($left) && $j < count($right)) {
        $comparisons++;

        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i];
            $i++;
        } else {
            $result[] = $right[$j];
            $j++;
        }
    }

    // Append remaining elements
    while ($i < count($left)) {
        $result[] = $left[$i];
        $i++;
    }

    while ($j < count($right)) {
        $result[] = $right[$j];
        $j++;
    }

    return $result;
}

/**
 * Merge sort with visualization
 */
function mergeSortVisualized(array $arr, int $depth = 0): array
{
    $n = count($arr);

    $indent = str_repeat('  ', $depth);

    if ($n <= 1) {
        echo $indent . "Base case: [" . implode(', ', $arr) . "]\n";
        return $arr;
    }

    echo $indent . "Split: [" . implode(', ', $arr) . "]\n";

    $mid = (int)($n / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    echo $indent . "  Left:  [" . implode(', ', $left) . "]\n";
    echo $indent . "  Right: [" . implode(', ', $right) . "]\n\n";

    $left = mergeSortVisualized($left, $depth + 1);
    $right = mergeSortVisualized($right, $depth + 1);

    $merged = merge($left, $right);

    echo $indent . "Merge: [" . implode(', ', $merged) . "]\n\n";

    return $merged;
}

/**
 * In-place merge sort (reduces space complexity)
 */
function mergeSortInPlace(array &$arr, int $left, int $right): void
{
    if ($left < $right) {
        $mid = (int)(($left + $right) / 2);

        mergeSortInPlace($arr, $left, $mid);
        mergeSortInPlace($arr, $mid + 1, $right);

        mergeInPlace($arr, $left, $mid, $right);
    }
}

function mergeInPlace(array &$arr, int $left, int $mid, int $right): void
{
    $leftArr = array_slice($arr, $left, $mid - $left + 1);
    $rightArr = array_slice($arr, $mid + 1, $right - $mid);

    $i = 0;
    $j = 0;
    $k = $left;

    while ($i < count($leftArr) && $j < count($rightArr)) {
        if ($leftArr[$i] <= $rightArr[$j]) {
            $arr[$k] = $leftArr[$i];
            $i++;
        } else {
            $arr[$k] = $rightArr[$j];
            $j++;
        }
        $k++;
    }

    while ($i < count($leftArr)) {
        $arr[$k] = $leftArr[$i];
        $i++;
        $k++;
    }

    while ($j < count($rightArr)) {
        $arr[$k] = $rightArr[$j];
        $j++;
        $k++;
    }
}

// Test 1: Basic Merge Sort
echo "1. Basic Merge Sort\n";
echo "====================\n";

$numbers = [38, 27, 43, 3, 9, 82, 10];
echo "Input: [" . implode(', ', $numbers) . "]\n\n";

$mergeCalls = 0;
$comparisons = 0;
$sorted = mergeSort($numbers);

echo "Output: [" . implode(', ', $sorted) . "]\n";
echo "Merge operations: $mergeCalls\n";
echo "Comparisons: $comparisons\n\n";

// Test 2: Visualization of Divide & Conquer
echo "2. Divide-and-Conquer Visualization\n";
echo "=====================================\n";

$numbers2 = [5, 2, 8, 1, 9];
echo "Sorting: [" . implode(', ', $numbers2) . "]\n\n";

$mergeCalls = 0;
$comparisons = 0;
$sorted2 = mergeSortVisualized($numbers2);

echo "Final: [" . implode(', ', $sorted2) . "]\n\n";

// Test 3: Merging Two Sorted Arrays
echo "3. Merging Two Sorted Arrays\n";
echo "==============================\n";

$left = [1, 3, 5, 7, 9];
$right = [2, 4, 6, 8, 10];

echo "Left:  [" . implode(', ', $left) . "]\n";
echo "Right: [" . implode(', ', $right) . "]\n";

$comparisons = 0;
$merged = merge($left, $right);

echo "Merged: [" . implode(', ', $merged) . "]\n";
echo "Comparisons: $comparisons\n\n";

// Test 4: Stability Test
echo "4. Stability Test\n";
echo "==================\n";

class Student
{
    public function __construct(
        public string $name,
        public int $score
    ) {}

    public function __toString(): string
    {
        return "{$this->name}({$this->score})";
    }
}

$students = [
    new Student('Alice', 85),
    new Student('Bob', 92),
    new Student('Charlie', 85),
    new Student('Diana', 78),
    new Student('Eve', 85),
];

echo "Input: ";
foreach ($students as $s) {
    echo "$s ";
}
echo "\n";

// Custom merge sort for objects
function mergeSortObjects(array $arr): array
{
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = mergeSortObjects(array_slice($arr, 0, $mid));
    $right = mergeSortObjects(array_slice($arr, $mid));

    return mergeObjects($left, $right);
}

function mergeObjects(array $left, array $right): array
{
    $result = [];
    $i = 0;
    $j = 0;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i]->score <= $right[$j]->score) {
            $result[] = $left[$i];
            $i++;
        } else {
            $result[] = $right[$j];
            $j++;
        }
    }

    while ($i < count($left)) {
        $result[] = $left[$i];
        $i++;
    }

    while ($j < count($right)) {
        $result[] = $right[$j];
        $j++;
    }

    return $result;
}

$sorted4 = mergeSortObjects($students);

echo "Output: ";
foreach ($sorted4 as $s) {
    echo "$s ";
}
echo "\n";
echo "Notice: Students with score 85 maintain order: Alice→Charlie→Eve (stable!)\n\n";

// Test 5: Performance Analysis
echo "5. Performance Analysis\n";
echo "========================\n";

$sizes = [100, 500, 1000, 5000];

echo "Array Size | Merge Ops | Comparisons | Time (ms)\n";
echo "-----------|-----------|-------------|----------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    shuffle($arr);

    $mergeCalls = 0;
    $comparisons = 0;

    $start = microtime(true);
    $sorted = mergeSort($arr);
    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%10d | %9d | %11d | %8.3f\n",
        $size,
        $mergeCalls,
        $comparisons,
        $time
    );
}

echo "\nObservation: Time grows as n log n (very predictable)\n\n";

// Test 6: Best, Average, Worst Cases
echo "6. Consistent Performance (All Cases)\n";
echo "=======================================\n";

$testSize = 1000;

// Best case: already sorted
$best = range(1, $testSize);
$start = microtime(true);
$temp = mergeSort($best);
$bestTime = (microtime(true) - $start) * 1000;

// Worst case: reverse sorted
$worst = range($testSize, 1, -1);
$start = microtime(true);
$temp = mergeSort($worst);
$worstTime = (microtime(true) - $start) * 1000;

// Average case: random
$average = range(1, $testSize);
shuffle($average);
$start = microtime(true);
$temp = mergeSort($average);
$averageTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Best case (sorted):    " . number_format($bestTime, 3) . " ms\n";
echo "Average case (random): " . number_format($averageTime, 3) . " ms\n";
echo "Worst case (reversed): " . number_format($worstTime, 3) . " ms\n\n";

echo "All cases have similar performance: O(n log n) guaranteed!\n\n";

// Test 7: Comparison with O(n²) Sorts
echo "7. Merge Sort vs O(n²) Sorts\n";
echo "=============================\n";

$testSize = 5000;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$start = microtime(true);
$temp = mergeSort($arr1);
$mergeTime = (microtime(true) - $start) * 1000;

$arr2 = $testArr;
$start = microtime(true);
// Insertion sort
$n = count($arr2);
for ($i = 1; $i < $n; $i++) {
    $key = $arr2[$i];
    $j = $i - 1;
    while ($j >= 0 && $arr2[$j] > $key) {
        $arr2[$j + 1] = $arr2[$j];
        $j--;
    }
    $arr2[$j + 1] = $key;
}
$insertionTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Merge Sort:     " . number_format($mergeTime, 3) . " ms\n";
echo "Insertion Sort: " . number_format($insertionTime, 3) . " ms\n";
echo "Speedup:        " . number_format($insertionTime / $mergeTime, 1) . "x\n\n";

echo "As data grows, O(n log n) dominates O(n²)!\n\n";

// Test 8: External Sorting Simulation
echo "8. External Sorting Concept\n";
echo "============================\n";

echo "Merge sort is ideal for sorting data that doesn't fit in memory\n";
echo "(e.g., sorting 100GB file with 4GB RAM)\n\n";

echo "Approach:\n";
echo "1. Split large file into sorted chunks that fit in memory\n";
echo "2. Sort each chunk individually (using any algorithm)\n";
echo "3. Merge sorted chunks (using external merge)\n\n";

// Simulate with small chunks
$largeArray = range(1, 100);
shuffle($largeArray);

echo "Simulating external sort with chunks of size 10:\n\n";

$chunkSize = 10;
$chunks = array_chunk($largeArray, $chunkSize);

echo "Step 1: Sort " . count($chunks) . " chunks in memory\n";
foreach ($chunks as $i => $chunk) {
    sort($chunk);
    $chunks[$i] = $chunk;
    echo "  Chunk " . ($i + 1) . ": [" . implode(', ', array_slice($chunk, 0, 5)) . "...]\n";
}

echo "\nStep 2: Merge sorted chunks\n";

// Merge chunks pairwise
while (count($chunks) > 1) {
    $newChunks = [];
    for ($i = 0; $i < count($chunks); $i += 2) {
        if ($i + 1 < count($chunks)) {
            $newChunks[] = merge($chunks[$i], $chunks[$i + 1]);
        } else {
            $newChunks[] = $chunks[$i];
        }
    }
    $chunks = $newChunks;
    echo "  Merged into " . count($chunks) . " chunk(s)\n";
}

$finalSorted = $chunks[0];
echo "\nFinal sorted: [" . implode(', ', array_slice($finalSorted, 0, 10)) . "...]\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(n log n)\n";
echo "  • Average case:  O(n log n)\n";
echo "  • Worst case:    O(n log n)\n";
echo "  → Guaranteed O(n log n) regardless of input!\n\n";

echo "Space Complexity:\n";
echo "  • O(n) - requires additional array for merging\n";
echo "  • Can be optimized to O(log n) with in-place variant\n\n";

echo "Properties:\n";
echo "  ✓ Stable:       Yes - maintains order of equal elements\n";
echo "  ✗ In-place:     No - requires O(n) extra space\n";
echo "  ✗ Adaptive:     No - always O(n log n)\n";
echo "  ✓ Parallelizable: Yes - can sort chunks in parallel\n\n";

echo "Advantages:\n";
echo "  ✓ Guaranteed O(n log n) performance\n";
echo "  ✓ Stable sort (preserves order)\n";
echo "  ✓ Predictable performance (no worst case degradation)\n";
echo "  ✓ Excellent for external sorting (large datasets)\n";
echo "  ✓ Parallelizes well (divide-and-conquer)\n";
echo "  ✓ Good cache performance (sequential access)\n\n";

echo "Disadvantages:\n";
echo "  ✗ Requires O(n) extra space\n";
echo "  ✗ Not in-place (memory overhead)\n";
echo "  ✗ Slower than quicksort in practice (constant factors)\n\n";

echo "When to Use:\n";
echo "  ✓ Stability is required\n";
echo "  ✓ Worst-case O(n log n) guarantee needed\n";
echo "  ✓ External sorting (data doesn't fit in memory)\n";
echo "  ✓ Linked lists (no random access penalty)\n";
echo "  ✓ Parallel processing available\n\n";

echo "Real-World Usage:\n";
echo "  • External sorting in databases\n";
echo "  • Sorting linked lists\n";
echo "  • Tim Sort (Python) uses merge sort for large runs\n";
echo "  • Used in tape drive sorting (sequential access)\n";
echo "  • Foundation for parallel sorting algorithms\n";

echo "\n✅ Complete! Merge sort demonstrated.\n";
