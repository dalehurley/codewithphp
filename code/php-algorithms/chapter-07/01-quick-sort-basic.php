<?php

declare(strict_types=1);

/**
 * Quick Sort - Basic Implementation
 *
 * Demonstrates basic quick sort with in-place partitioning.
 * Time Complexity: O(n log n) average, O(n²) worst case
 * Space Complexity: O(log n) recursion stack
 * Stable: No
 */

/**
 * Quick sort (simple version with new arrays)
 *
 * @param array<int> $arr Array to sort
 * @return array<int> Sorted array
 */
function quickSort(array $arr): array
{
    // Base case
    if (count($arr) < 2) {
        return $arr;
    }

    // Choose pivot (simple: first element)
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

/**
 * Quick sort in-place (more efficient, modifies original array)
 *
 * @param array<int> $arr Array to sort (modified in place)
 * @param int $low Starting index
 * @param int $high Ending index
 * @return void
 */
function quickSortInPlace(array &$arr, int $low = 0, int $high = -1): void
{
    if ($high === -1) {
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

/**
 * Partition array around pivot (last element)
 *
 * @param array<int> $arr Array to partition
 * @param int $low Starting index
 * @param int $high Ending index
 * @return int Final position of pivot
 */
function partition(array &$arr, int $low, int $high): int
{
    $pivot = $arr[$high];
    $i = $low - 1; // Index of smaller element

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    // Place pivot in correct position
    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];

    return $i + 1;
}

/**
 * Visualized partition operation
 *
 * @param array<int> $arr Array to partition
 * @param int $low Starting index
 * @param int $high Ending index
 * @return int Final position of pivot
 */
function partitionVisualized(array &$arr, int $low, int $high): int
{
    $pivot = $arr[$high];
    echo "\n  Partitioning around pivot: $pivot\n";
    echo "  Initial: [" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";

    $i = $low - 1;
    $swaps = 0;

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            if ($i !== $j) {
                echo "    Swap {$arr[$i]} and {$arr[$j]} (both < pivot)\n";
                [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
                $swaps++;
            }
        }
    }

    // Place pivot
    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    echo "  Place pivot at index " . ($i + 1) . "\n";
    echo "  Result:  [" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";
    echo "  Swaps: $swaps\n";

    return $i + 1;
}

/**
 * Quick sort with visualization
 *
 * @param array<int> $arr Array to sort
 * @param int $low Starting index
 * @param int $high Ending index
 * @param int $depth Recursion depth for indentation
 * @return void
 */
function quickSortVisualized(array &$arr, int $low, int $high, int $depth = 0): void
{
    if ($low < $high) {
        $indent = str_repeat('  ', $depth);
        echo $indent . "Sorting: [" . implode(', ', array_slice($arr, $low, $high - $low + 1)) . "]\n";

        $pivotIndex = partitionVisualized($arr, $low, $high);

        echo $indent . "Pivot {$arr[$pivotIndex]} is now at position $pivotIndex\n";
        echo $indent . "Left partition:  indices $low to " . ($pivotIndex - 1) . "\n";
        echo $indent . "Right partition: indices " . ($pivotIndex + 1) . " to $high\n\n";

        quickSortVisualized($arr, $low, $pivotIndex - 1, $depth + 1);
        quickSortVisualized($arr, $pivotIndex + 1, $high, $depth + 1);
    }
}

// ============================================================================
// Examples and Test Cases
// ============================================================================

echo "QUICK SORT - BASIC IMPLEMENTATION\n";
echo str_repeat('=', 70) . "\n\n";

// Example 1: Simple quick sort
echo "Example 1: Simple Quick Sort\n";
echo str_repeat('-', 70) . "\n";
$numbers = [8, 3, 1, 7, 0, 10, 2];
echo "Original: [" . implode(', ', $numbers) . "]\n";
$sorted = quickSort($numbers);
echo "Sorted:   [" . implode(', ', $sorted) . "]\n\n";

// Example 2: In-place quick sort
echo "Example 2: In-Place Quick Sort (More Efficient)\n";
echo str_repeat('-', 70) . "\n";
$numbers = [8, 3, 1, 7, 0, 10, 2];
echo "Before: [" . implode(', ', $numbers) . "]\n";
quickSortInPlace($numbers);
echo "After:  [" . implode(', ', $numbers) . "]\n\n";

// Example 3: Visualized sorting
echo "Example 3: Step-by-Step Visualization\n";
echo str_repeat('-', 70) . "\n";
$numbers = [8, 3, 1, 7, 0, 10, 2];
echo "Initial array: [" . implode(', ', $numbers) . "]\n";
quickSortVisualized($numbers, 0, count($numbers) - 1);
echo "Final sorted: [" . implode(', ', $numbers) . "]\n\n";

// Example 4: Best case (balanced partitions)
echo "Example 4: Best Case - Balanced Partitions\n";
echo str_repeat('-', 70) . "\n";
$balanced = [5, 2, 8, 1, 9, 3, 7, 4, 6];
echo "Input: [" . implode(', ', $balanced) . "]\n";
$start = microtime(true);
quickSortInPlace($balanced);
$time = (microtime(true) - $start) * 1000;
echo "Sorted: [" . implode(', ', $balanced) . "]\n";
echo "Time: " . number_format($time, 4) . " ms\n";
echo "Note: Pivot divides array roughly in half each time (O(n log n))\n\n";

// Example 5: Worst case (already sorted with first/last pivot)
echo "Example 5: Worst Case - Already Sorted (Poor Pivot Choice)\n";
echo str_repeat('-', 70) . "\n";
$sorted = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
echo "Input (already sorted): [" . implode(', ', $sorted) . "]\n";
echo "WARNING: Using last element as pivot on sorted data is O(n²)!\n";
$start = microtime(true);
quickSortInPlace($sorted);
$time = (microtime(true) - $start) * 1000;
echo "Time: " . number_format($time, 4) . " ms\n";
echo "Partitions: [pivot] | [all other elements] (unbalanced!)\n\n";

// Example 6: Edge cases
echo "Example 6: Edge Cases\n";
echo str_repeat('-', 70) . "\n";
$empty = [];
$single = [42];
$two = [5, 2];

quickSortInPlace($empty);
quickSortInPlace($single);
quickSortInPlace($two);

echo "Empty: " . json_encode($empty) . "\n";
echo "Single: " . json_encode($single) . "\n";
echo "Two elements: " . json_encode($two) . "\n\n";

// Example 7: All equal elements (worst case)
echo "Example 7: All Equal Elements (Worst Case)\n";
echo str_repeat('-', 70) . "\n";
$equal = [5, 5, 5, 5, 5, 5, 5, 5];
echo "Input: [" . implode(', ', $equal) . "]\n";
$start = microtime(true);
quickSortInPlace($equal);
$time = (microtime(true) - $start) * 1000;
echo "Time: " . number_format($time, 4) . " ms\n";
echo "Note: Standard quick sort is O(n²) on all equal elements!\n";
echo "Solution: Use 3-way partitioning (see optimized version)\n\n";

// Example 8: Performance analysis
echo "Example 8: Performance on Different Data Patterns\n";
echo str_repeat('-', 70) . "\n";
echo sprintf("%-12s | %-15s | %-15s | %-15s\n", "Size", "Random", "Sorted", "Reversed");
echo str_repeat('-', 70) . "\n";

foreach ([10, 50, 100, 500, 1000] as $size) {
    // Random
    $random = range(1, $size);
    shuffle($random);
    $start = microtime(true);
    quickSortInPlace($random);
    $randomTime = (microtime(true) - $start) * 1000;

    // Sorted (worst case with last pivot)
    $sorted = range(1, $size);
    $start = microtime(true);
    quickSortInPlace($sorted);
    $sortedTime = (microtime(true) - $start) * 1000;

    // Reversed (also worst case)
    $reversed = range($size, 1, -1);
    $start = microtime(true);
    quickSortInPlace($reversed);
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
echo "- Random data: Fast O(n log n)\n";
echo "- Sorted/Reversed: SLOW O(n²) with poor pivot!\n";
echo "- Problem: Last element pivot terrible for sorted data\n";
echo "- Solution: Use better pivot selection (Chapter 02)\n\n";

echo "Advantages:\n";
echo "✓ Very fast average case: O(n log n)\n";
echo "✓ In-place: O(log n) space for recursion\n";
echo "✓ Excellent cache locality\n";
echo "✓ Typically faster than merge sort in practice\n\n";

echo "Disadvantages:\n";
echo "✗ Worst case: O(n²) with poor pivot\n";
echo "✗ Not stable (equal elements may be reordered)\n";
echo "✗ Performance depends on pivot selection\n";
