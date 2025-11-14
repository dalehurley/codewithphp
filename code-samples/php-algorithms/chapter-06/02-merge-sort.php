<?php

declare(strict_types=1);

/**
 * Merge Sort Implementation
 *
 * Demonstrates merge sort algorithm with divide-and-conquer strategy.
 * Time Complexity: O(n log n) for all cases
 * Space Complexity: O(n)
 * Stable: Yes
 */

/**
 * Performs merge sort on an array
 *
 * @param array<int> $arr Array to sort
 * @return array<int> Sorted array
 */
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

/**
 * Merges two sorted arrays into one sorted array
 *
 * @param array<int> $left First sorted array
 * @param array<int> $right Second sorted array
 * @return array<int> Merged sorted array
 */
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

/**
 * Merge sort with step-by-step visualization
 *
 * @param array<int> $arr Array to sort
 * @param int $depth Recursion depth (for indentation)
 * @return array<int> Sorted array
 */
function mergeSortVisualized(array $arr, int $depth = 0): array
{
    $indent = str_repeat('  ', $depth);

    if (count($arr) <= 1) {
        echo "{$indent}Base case: [" . implode(', ', $arr) . "]\n";
        return $arr;
    }

    echo "{$indent}Divide: [" . implode(', ', $arr) . "]\n";

    $mid = (int)(count($arr) / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    echo "{$indent}  → Left:  [" . implode(', ', $left) . "]\n";
    echo "{$indent}  → Right: [" . implode(', ', $right) . "]\n";

    $left = mergeSortVisualized($left, $depth + 1);
    $right = mergeSortVisualized($right, $depth + 1);

    $result = merge($left, $right);
    echo "{$indent}Merge: [" . implode(', ', $result) . "]\n";

    return $result;
}

/**
 * Detailed merge operation with step-by-step output
 *
 * @param array<int> $left First sorted array
 * @param array<int> $right Second sorted array
 * @return array<int> Merged sorted array
 */
function mergeDetailed(array $left, array $right): array
{
    echo "\n  Merging:\n";
    echo "    Left:  [" . implode(', ', $left) . "]\n";
    echo "    Right: [" . implode(', ', $right) . "]\n";
    echo "  Steps:\n";

    $result = [];
    $i = $j = 0;
    $step = 1;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i];
            echo "    $step. Compare {$left[$i]} vs {$right[$j]} → take {$left[$i]} from left\n";
            $i++;
        } else {
            $result[] = $right[$j];
            echo "    $step. Compare {$left[$i]} vs {$right[$j]} → take {$right[$j]} from right\n";
            $j++;
        }
        $step++;
    }

    // Add remaining elements
    while ($i < count($left)) {
        $result[] = $left[$i];
        echo "    $step. Left exhausted right, append {$left[$i]}\n";
        $i++;
        $step++;
    }

    while ($j < count($right)) {
        $result[] = $right[$j];
        echo "    $step. Right exhausted left, append {$right[$j]}\n";
        $j++;
        $step++;
    }

    echo "    Result: [" . implode(', ', $result) . "]\n";

    return $result;
}

// ============================================================================
// Examples and Test Cases
// ============================================================================

echo "MERGE SORT EXAMPLES\n";
echo str_repeat('=', 70) . "\n\n";

// Example 1: Basic sorting
echo "Example 1: Basic Sorting\n";
echo str_repeat('-', 70) . "\n";
$numbers = [38, 27, 43, 3, 9, 82, 10];
echo "Original: [" . implode(', ', $numbers) . "]\n";
$sorted = mergeSort($numbers);
echo "Sorted:   [" . implode(', ', $sorted) . "]\n\n";

// Example 2: Visualized sorting (small array)
echo "Example 2: Divide-and-Conquer Visualization\n";
echo str_repeat('-', 70) . "\n";
$numbers = [38, 27, 43, 3];
mergeSortVisualized($numbers);
echo "\n";

// Example 3: Detailed merge operation
echo "Example 3: Detailed Merge Operation\n";
echo str_repeat('-', 70) . "\n";
$left = [3, 27, 38, 43];
$right = [9, 10, 82];
mergeDetailed($left, $right);
echo "\n";

// Example 4: Already sorted (still O(n log n))
echo "Example 4: Already Sorted Array\n";
echo str_repeat('-', 70) . "\n";
$sorted = [1, 2, 3, 4, 5];
echo "Input (already sorted): [" . implode(', ', $sorted) . "]\n";
$start = microtime(true);
$result = mergeSort($sorted);
$time = (microtime(true) - $start) * 1000;
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Time: " . number_format($time, 4) . " ms\n";
echo "Note: Still O(n log n) - no shortcuts!\n\n";

// Example 5: Reverse sorted (still O(n log n))
echo "Example 5: Reverse Sorted Array\n";
echo str_repeat('-', 70) . "\n";
$reversed = [5, 4, 3, 2, 1];
echo "Input (reverse sorted): [" . implode(', ', $reversed) . "]\n";
$start = microtime(true);
$result = mergeSort($reversed);
$time = (microtime(true) - $start) * 1000;
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Time: " . number_format($time, 4) . " ms\n";
echo "Note: Same time as sorted - predictable performance!\n\n";

// Example 6: All equal elements
echo "Example 6: All Equal Elements\n";
echo str_repeat('-', 70) . "\n";
$equal = [5, 5, 5, 5, 5];
echo "Input: [" . implode(', ', $equal) . "]\n";
$result = mergeSort($equal);
echo "Result: [" . implode(', ', $result) . "]\n\n";

// Example 7: Edge cases
echo "Example 7: Edge Cases\n";
echo str_repeat('-', 70) . "\n";
$empty = [];
$single = [42];
$two = [5, 2];
echo "Empty array: " . json_encode(mergeSort($empty)) . "\n";
echo "Single element: " . json_encode(mergeSort($single)) . "\n";
echo "Two elements: " . json_encode(mergeSort($two)) . "\n\n";

// Example 8: Stability test with duplicates
echo "Example 8: Stability Test\n";
echo str_repeat('-', 70) . "\n";
$withDuplicates = [3, 1, 4, 1, 5, 9, 2, 6, 5, 3];
echo "Input: [" . implode(', ', $withDuplicates) . "]\n";
$result = mergeSort($withDuplicates);
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Note: Merge sort is stable - maintains relative order of equal elements\n\n";

// Example 9: Performance analysis
echo "Example 9: Predictable Performance (All Cases O(n log n))\n";
echo str_repeat('-', 70) . "\n";
echo sprintf("%-12s | %-15s | %-15s | %-15s\n", "Array Size", "Random", "Sorted", "Reversed");
echo str_repeat('-', 70) . "\n";

foreach ([10, 50, 100, 500, 1000] as $size) {
    // Random
    $random = range(1, $size);
    shuffle($random);
    $start = microtime(true);
    mergeSort($random);
    $randomTime = (microtime(true) - $start) * 1000;

    // Sorted
    $sorted = range(1, $size);
    $start = microtime(true);
    mergeSort($sorted);
    $sortedTime = (microtime(true) - $start) * 1000;

    // Reversed
    $reversed = range($size, 1, -1);
    $start = microtime(true);
    mergeSort($reversed);
    $reversedTime = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%-12d | %10.4f ms | %10.4f ms | %10.4f ms\n",
        $size,
        $randomTime,
        $sortedTime,
        $reversedTime
    );
}

echo "\n";
echo "Key Observations:\n";
echo "- All patterns take similar time\n";
echo "- Guaranteed O(n log n) - no worst case surprise\n";
echo "- Predictable and consistent performance\n";
echo "- Trade-off: Uses O(n) extra space\n";
