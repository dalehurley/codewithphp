<?php

declare(strict_types=1);

/**
 * Quick Sort - O(n log n) Divide-and-Conquer Algorithm
 *
 * Demonstrates:
 * - Quick sort with different pivot strategies
 * - Partitioning algorithm (Lomuto and Hoare)
 * - In-place sorting
 * - Average O(n log n), worst O(n²)
 * - Randomized quick sort
 * - Tail recursion optimization
 */

echo "=== Quick Sort ===\n\n";

echo "Concept: Divide and Conquer with Partitioning\n";
echo "         1. Pick a 'pivot' element\n";
echo "         2. Partition: elements < pivot left, >= pivot right\n";
echo "         3. Recursively sort left and right partitions\n\n";

$comparisons = 0;
$swaps = 0;

/**
 * Quick sort (Lomuto partition scheme)
 */
function quickSort(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pivotIndex = partition($arr, $low, $high);

        quickSort($arr, $low, $pivotIndex - 1);
        quickSort($arr, $pivotIndex + 1, $high);
    }
}

/**
 * Lomuto partition scheme (last element as pivot)
 */
function partition(array &$arr, int $low, int $high): int
{
    global $comparisons, $swaps;

    $pivot = $arr[$high];
    $i = $low - 1;

    for ($j = $low; $j < $high; $j++) {
        $comparisons++;

        if ($arr[$j] <= $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
            $swaps++;
        }
    }

    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    $swaps++;

    return $i + 1;
}

/**
 * Quick sort with visualization
 */
function quickSortVisualized(array &$arr, int $low, int $high, int $depth = 0): void
{
    $indent = str_repeat('  ', $depth);

    if ($low < $high) {
        $pivot = $arr[$high];

        echo $indent . "Partition around pivot=$pivot: ";
        echo "[" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";

        $pivotIndex = partition($arr, $low, $high);

        echo $indent . "After partition:  ";
        for ($i = $low; $i <= $high; $i++) {
            if ($i == $pivotIndex) {
                echo "[$i=" . $arr[$i] . "] ";
            } else {
                echo $arr[$i] . " ";
            }
        }
        echo "\n\n";

        quickSortVisualized($arr, $low, $pivotIndex - 1, $depth + 1);
        quickSortVisualized($arr, $pivotIndex + 1, $high, $depth + 1);
    }
}

/**
 * Randomized quick sort (random pivot selection)
 */
function randomizedQuickSort(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        // Random pivot selection
        $randomIndex = random_int($low, $high);
        [$arr[$randomIndex], $arr[$high]] = [$arr[$high], $arr[$randomIndex]];

        $pivotIndex = partition($arr, $low, $high);

        randomizedQuickSort($arr, $low, $pivotIndex - 1);
        randomizedQuickSort($arr, $pivotIndex + 1, $high);
    }
}

/**
 * Hoare partition scheme (more efficient)
 */
function hoarePartition(array &$arr, int $low, int $high): int
{
    global $comparisons, $swaps;

    $pivot = $arr[($low + $high) / 2];
    $i = $low - 1;
    $j = $high + 1;

    while (true) {
        do {
            $i++;
            $comparisons++;
        } while ($arr[$i] < $pivot);

        do {
            $j--;
            $comparisons++;
        } while ($arr[$j] > $pivot);

        if ($i >= $j) {
            return $j;
        }

        [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        $swaps++;
    }
}

function quickSortHoare(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pivotIndex = hoarePartition($arr, $low, $high);

        quickSortHoare($arr, $low, $pivotIndex);
        quickSortHoare($arr, $pivotIndex + 1, $high);
    }
}

/**
 * Three-way partitioning (handles duplicates efficiently)
 */
function quickSort3Way(array &$arr, int $low, int $high): void
{
    if ($low >= $high) return;

    $pivot = $arr[$low];
    $lt = $low;      // arr[low..lt-1] < pivot
    $gt = $high;     // arr[gt+1..high] > pivot
    $i = $low + 1;   // arr[lt..i-1] == pivot

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

    quickSort3Way($arr, $low, $lt - 1);
    quickSort3Way($arr, $gt + 1, $high);
}

// Test 1: Basic Quick Sort
echo "1. Basic Quick Sort (Lomuto Partition)\n";
echo "========================================\n";

$numbers = [10, 7, 8, 9, 1, 5];
echo "Input: [" . implode(', ', $numbers) . "]\n\n";

$comparisons = 0;
$swaps = 0;
quickSort($numbers, 0, count($numbers) - 1);

echo "Output: [" . implode(', ', $numbers) . "]\n";
echo "Comparisons: $comparisons\n";
echo "Swaps: $swaps\n\n";

// Test 2: Visualization
echo "2. Quick Sort Visualization\n";
echo "============================\n";

$numbers2 = [5, 2, 8, 1, 9, 3];
echo "Sorting: [" . implode(', ', $numbers2) . "]\n\n";

quickSortVisualized($numbers2, 0, count($numbers2) - 1);

echo "Final: [" . implode(', ', $numbers2) . "]\n\n";

// Test 3: Partition Demonstration
echo "3. Partition Process\n";
echo "=====================\n";

$arr = [8, 3, 1, 7, 0, 10, 2];
echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Pivot (last element): {$arr[count($arr) - 1]}\n\n";

$comparisons = 0;
$swaps = 0;
$pivotIndex = partition($arr, 0, count($arr) - 1);

echo "After partition:\n";
echo "  Left (< pivot):  [" . implode(', ', array_slice($arr, 0, $pivotIndex)) . "]\n";
echo "  Pivot:           [{$arr[$pivotIndex]}]\n";
echo "  Right (>= pivot): [" . implode(', ', array_slice($arr, $pivotIndex + 1)) . "]\n";
echo "  Comparisons: $comparisons, Swaps: $swaps\n\n";

// Test 4: Randomized Quick Sort
echo "4. Randomized Quick Sort\n";
echo "=========================\n";

$numbers4 = [5, 2, 9, 1, 7, 6, 3];
echo "Input: [" . implode(', ', $numbers4) . "]\n";

randomizedQuickSort($numbers4, 0, count($numbers4) - 1);

echo "Output: [" . implode(', ', $numbers4) . "]\n";
echo "Note: Random pivot selection avoids worst-case O(n²) on sorted data\n\n";

// Test 5: Lomuto vs Hoare Partition
echo "5. Lomuto vs Hoare Partition Scheme\n";
echo "=====================================\n";

$testSize = 1000;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$comparisons = 0;
$swaps = 0;
$start = microtime(true);
quickSort($arr1, 0, count($arr1) - 1);
$lomutoTime = (microtime(true) - $start) * 1000;
$lomutoComparisons = $comparisons;
$lomutoSwaps = $swaps;

$arr2 = $testArr;
$comparisons = 0;
$swaps = 0;
$start = microtime(true);
quickSortHoare($arr2, 0, count($arr2) - 1);
$hoareTime = (microtime(true) - $start) * 1000;
$hoareComparisons = $comparisons;
$hoareSwaps = $swaps;

echo "Array size: $testSize elements\n\n";
echo "Lomuto Partition:\n";
echo "  Time:        " . number_format($lomutoTime, 3) . " ms\n";
echo "  Comparisons: $lomutoComparisons\n";
echo "  Swaps:       $lomutoSwaps\n\n";

echo "Hoare Partition:\n";
echo "  Time:        " . number_format($hoareTime, 3) . " ms\n";
echo "  Comparisons: $hoareComparisons\n";
echo "  Swaps:       $hoareSwaps\n\n";

echo "Hoare is typically ~2x faster (fewer swaps)\n\n";

// Test 6: Three-Way Partitioning (Duplicates)
echo "6. Three-Way Partitioning (Many Duplicates)\n";
echo "============================================\n";

$duplicates = [3, 1, 3, 3, 2, 3, 1, 3, 3];
echo "Input with duplicates: [" . implode(', ', $duplicates) . "]\n";

$arr1 = $duplicates;
$start = microtime(true);
quickSort($arr1, 0, count($arr1) - 1);
$standardTime = (microtime(true) - $start) * 1000;

$arr2 = $duplicates;
$start = microtime(true);
quickSort3Way($arr2, 0, count($arr2) - 1);
$threeWayTime = (microtime(true) - $start) * 1000;

echo "\nStandard quick sort: " . number_format($standardTime, 3) . " ms\n";
echo "3-way quick sort:    " . number_format($threeWayTime, 3) . " ms\n";
echo "3-way is faster when many duplicates exist!\n\n";

// Test 7: Best, Average, Worst Cases
echo "7. Performance: Best, Average, Worst Cases\n";
echo "============================================\n";

$testSize = 2000;

// Average case: random
$average = range(1, $testSize);
shuffle($average);
$start = microtime(true);
quickSort($average, 0, count($average) - 1);
$averageTime = (microtime(true) - $start) * 1000;

// Best case: random pivot usually good
$best = range(1, $testSize);
shuffle($best);
$start = microtime(true);
randomizedQuickSort($best, 0, count($best) - 1);
$bestTime = (microtime(true) - $start) * 1000;

// Worst case: already sorted with last element pivot
$worst = range(1, $testSize);
$start = microtime(true);
quickSort($worst, 0, count($worst) - 1);
$worstTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Best/Average (random):     " . number_format($averageTime, 3) . " ms\n";
echo "Worst case (sorted, last pivot): " . number_format($worstTime, 3) . " ms\n";
echo "Randomized (always good):  " . number_format($bestTime, 3) . " ms\n\n";

echo "Worst/Average ratio: " . number_format($worstTime / $averageTime, 1) . "x\n";
echo "Always use randomized quick sort in production!\n\n";

// Test 8: Quick Sort vs Merge Sort
echo "8. Quick Sort vs Merge Sort\n";
echo "============================\n";

$testSize = 5000;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$start = microtime(true);
randomizedQuickSort($arr1, 0, count($arr1) - 1);
$quickTime = (microtime(true) - $start) * 1000;

$arr2 = $testArr;
$start = microtime(true);
// Merge sort
function mergeSort(array $arr): array {
    if (count($arr) <= 1) return $arr;
    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));
    return merge($left, $right);
}
function merge($left, $right) {
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
$arr2 = mergeSort($arr2);
$mergeTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Quick Sort (in-place):   " . number_format($quickTime, 3) . " ms\n";
echo "Merge Sort (extra space): " . number_format($mergeTime, 3) . " ms\n\n";

echo "Quick sort is typically faster in practice due to:\n";
echo "  • Better cache locality (in-place)\n";
echo "  • Fewer data movements\n";
echo "  • Lower constant factors\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(n log n) - balanced partitions\n";
echo "  • Average case:  O(n log n) - random data\n";
echo "  • Worst case:    O(n²) - poor pivot selection (already sorted)\n";
echo "  → Use randomized quick sort to avoid worst case!\n\n";

echo "Space Complexity:\n";
echo "  • O(log n) - recursion stack space\n";
echo "  • O(n) worst case - unbalanced partitions\n\n";

echo "Properties:\n";
echo "  ✗ Stable:     No - long-distance swaps\n";
echo "  ✓ In-place:   Yes - O(log n) extra space\n";
echo "  ✗ Adaptive:   No - doesn't benefit from partial sorting\n";
echo "  ✓ Cache-friendly: Yes - good locality of reference\n\n";

echo "Pivot Selection Strategies:\n";
echo "  1. Last element (Lomuto): Simple but O(n²) on sorted data\n";
echo "  2. Random element: Avoids worst case, expected O(n log n)\n";
echo "  3. Median-of-three: First, middle, last → better pivots\n";
echo "  4. Median-of-medians: Guaranteed O(n log n) but slow\n\n";

echo "Advantages:\n";
echo "  ✓ Fast in practice (typically fastest sorting algorithm)\n";
echo "  ✓ In-place sorting (low memory overhead)\n";
echo "  ✓ Good cache performance\n";
echo "  ✓ Simple to implement\n";
echo "  ✓ Average O(n log n) performance\n\n";

echo "Disadvantages:\n";
echo "  ✗ Worst case O(n²) (mitigated with randomization)\n";
echo "  ✗ Unstable (changes order of equal elements)\n";
echo "  ✗ Poor performance on nearly sorted data (without randomization)\n";
echo "  ✗ Recursive (stack overflow on deep recursion)\n\n";

echo "When to Use:\n";
echo "  ✓ General-purpose sorting (default choice)\n";
echo "  ✓ Large datasets (better than O(n²) sorts)\n";
echo "  ✓ When in-place sorting is needed\n";
echo "  ✓ When average-case performance matters more than worst-case\n";
echo "  ✓ Arrays (better than merge sort due to cache locality)\n\n";

echo "Optimizations:\n";
echo "  • Use randomized pivot selection\n";
echo "  • Switch to insertion sort for small partitions (< 10 elements)\n";
echo "  • Use 3-way partitioning for many duplicates\n";
echo "  • Median-of-three pivot selection\n";
echo "  • Tail recursion optimization\n\n";

echo "Real-World Usage:\n";
echo "  • C++ std::sort() uses Intro Sort (quick sort + heap sort fallback)\n";
echo "  • Java Arrays.sort() uses dual-pivot quick sort for primitives\n";
echo "  • Most system qsort() implementations\n";
echo "  • Database query result sorting\n";
echo "  • Common default sorting algorithm\n";

echo "\n✅ Complete! Quick sort demonstrated.\n";
