<?php

declare(strict_types=1);

/**
 * Insertion Sort Implementation
 *
 * Demonstrates insertion sort algorithm with visualization and edge cases.
 * Time Complexity: O(n) best case, O(n²) average/worst case
 * Space Complexity: O(1)
 * Stable: Yes
 */

/**
 * Performs insertion sort on an array
 *
 * @param array<int> $arr Array to sort
 * @return array<int> Sorted array
 */
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

/**
 * Insertion sort with step-by-step visualization
 *
 * @param array<int> $arr Array to sort
 * @return array<int> Sorted array
 */
function insertionSortVisualized(array $arr): array
{
    $n = count($arr);
    echo "Initial array: [" . implode(', ', $arr) . "]\n";
    echo str_repeat('=', 50) . "\n\n";

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        echo "Step $i: Inserting $key\n";
        echo "  Before: [" . implode(', ', $arr) . "]\n";

        $shifts = 0;
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
            $shifts++;
        }

        $arr[$j + 1] = $key;
        echo "  After:  [" . implode(', ', $arr) . "] ($shifts shifts)\n\n";
    }

    echo str_repeat('=', 50) . "\n";
    echo "Final sorted: [" . implode(', ', $arr) . "]\n";

    return $arr;
}

/**
 * Insertion sort for a range within an array (useful for hybrid algorithms)
 *
 * @param array<int> $arr Array to sort (modified in place)
 * @param int $low Starting index
 * @param int $high Ending index
 * @return void
 */
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

// ============================================================================
// Examples and Test Cases
// ============================================================================

echo "INSERTION SORT EXAMPLES\n";
echo str_repeat('=', 70) . "\n\n";

// Example 1: Basic sorting
echo "Example 1: Basic Sorting\n";
echo str_repeat('-', 70) . "\n";
$numbers = [5, 2, 4, 6, 1, 3];
$sorted = insertionSort($numbers);
echo "Original: [5, 2, 4, 6, 1, 3]\n";
echo "Sorted:   [" . implode(', ', $sorted) . "]\n\n";

// Example 2: Visualized sorting
echo "Example 2: Step-by-Step Visualization\n";
echo str_repeat('-', 70) . "\n";
$numbers = [5, 2, 4, 6, 1, 3];
insertionSortVisualized($numbers);
echo "\n";

// Example 3: Best case (already sorted)
echo "Example 3: Best Case - Already Sorted (O(n))\n";
echo str_repeat('-', 70) . "\n";
$sorted = [1, 2, 3, 4, 5];
echo "Input (already sorted): [" . implode(', ', $sorted) . "]\n";
$start = microtime(true);
$result = insertionSort($sorted);
$time = (microtime(true) - $start) * 1000;
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Time: " . number_format($time, 4) . " ms (minimal comparisons, no shifts)\n\n";

// Example 4: Worst case (reverse sorted)
echo "Example 4: Worst Case - Reverse Sorted (O(n²))\n";
echo str_repeat('-', 70) . "\n";
$reversed = [5, 4, 3, 2, 1];
echo "Input (reverse sorted): [" . implode(', ', $reversed) . "]\n";
$start = microtime(true);
$result = insertionSort($reversed);
$time = (microtime(true) - $start) * 1000;
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Time: " . number_format($time, 4) . " ms (maximum shifts)\n\n";

// Example 5: Nearly sorted array
echo "Example 5: Nearly Sorted Array (Excellent Performance)\n";
echo str_repeat('-', 70) . "\n";
$nearlySorted = [1, 2, 3, 10, 5, 6, 7, 8, 9];
echo "Input: [" . implode(', ', $nearlySorted) . "]\n";
$start = microtime(true);
$result = insertionSort($nearlySorted);
$time = (microtime(true) - $start) * 1000;
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Time: " . number_format($time, 4) . " ms (~O(n) performance)\n\n";

// Example 6: All equal elements
echo "Example 6: All Equal Elements\n";
echo str_repeat('-', 70) . "\n";
$equal = [5, 5, 5, 5, 5];
echo "Input: [" . implode(', ', $equal) . "]\n";
$result = insertionSort($equal);
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Note: O(n) time - no shifts needed\n\n";

// Example 7: Empty and single element arrays
echo "Example 7: Edge Cases\n";
echo str_repeat('-', 70) . "\n";
$empty = [];
$single = [42];
echo "Empty array: " . json_encode(insertionSort($empty)) . "\n";
echo "Single element: " . json_encode(insertionSort($single)) . "\n\n";

// Example 8: Array with duplicates (stability test)
echo "Example 8: Duplicates - Stable Sort Test\n";
echo str_repeat('-', 70) . "\n";
$withDuplicates = [3, 1, 4, 1, 5, 9, 2, 6, 5, 3];
echo "Input: [" . implode(', ', $withDuplicates) . "]\n";
$result = insertionSort($withDuplicates);
echo "Result: [" . implode(', ', $result) . "]\n";
echo "Note: Insertion sort is stable - equal elements maintain relative order\n\n";

// Example 9: Performance comparison (different sizes)
echo "Example 9: Performance Analysis\n";
echo str_repeat('-', 70) . "\n";
echo sprintf("%-12s | %-15s | %-15s | %-15s\n", "Array Size", "Random", "Sorted", "Reversed");
echo str_repeat('-', 70) . "\n";

foreach ([10, 50, 100, 500] as $size) {
    // Random
    $random = range(1, $size);
    shuffle($random);
    $start = microtime(true);
    insertionSort($random);
    $randomTime = (microtime(true) - $start) * 1000;

    // Sorted
    $sorted = range(1, $size);
    $start = microtime(true);
    insertionSort($sorted);
    $sortedTime = (microtime(true) - $start) * 1000;

    // Reversed
    $reversed = range($size, 1, -1);
    $start = microtime(true);
    insertionSort($reversed);
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
echo "- Sorted data: O(n) - very fast!\n";
echo "- Random/Reversed: O(n²) - grows quadratically\n";
echo "- Best for: small arrays (< 50) or nearly sorted data\n";
