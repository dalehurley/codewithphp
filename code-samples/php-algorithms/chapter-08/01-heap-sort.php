<?php

declare(strict_types=1);

/**
 * Heap Sort Implementation
 * Time: O(n log n) all cases
 * Space: O(1) - in-place
 * Stable: No
 */

function heapify(array &$arr, int $i, int $heapSize): void
{
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;
    $largest = $i;

    if ($left < $heapSize && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    if ($right < $heapSize && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapify($arr, $largest, $heapSize);
    }
}

function heapSort(array $arr): array
{
    $n = count($arr);

    // Build max heap - O(n)
    for ($i = (int)(($n / 2) - 1); $i >= 0; $i--) {
        heapify($arr, $i, $n);
    }

    // Extract elements one by one - O(n log n)
    for ($i = $n - 1; $i > 0; $i--) {
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];
        heapify($arr, 0, $i);
    }

    return $arr;
}

function heapSortVisualized(array $arr): array
{
    $n = count($arr);
    echo "Initial: [" . implode(', ', $arr) . "]\n\n";

    echo "Phase 1: Building Max Heap\n";
    echo str_repeat('-', 50) . "\n";
    for ($i = (int)(($n / 2) - 1); $i >= 0; $i--) {
        heapify($arr, $i, $n);
        echo "After heapify($i): [" . implode(', ', $arr) . "]\n";
    }

    echo "\nPhase 2: Extracting Elements\n";
    echo str_repeat('-', 50) . "\n";
    for ($i = $n - 1; $i > 0; $i--) {
        echo "Extract max {$arr[0]}, move to position $i\n";
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];
        heapify($arr, 0, $i);
        echo "  Heap: [" . implode(', ', array_slice($arr, 0, $i)) . "] | Sorted: [" . implode(', ', array_slice($arr, $i)) . "]\n";
    }

    return $arr;
}

// ============================================================================
// Examples
// ============================================================================

echo "HEAP SORT\n";
echo str_repeat('=', 70) . "\n\n";

echo "Example 1: Basic Heap Sort\n";
echo str_repeat('-', 70) . "\n";
$numbers = [12, 11, 13, 5, 6, 7];
echo "Original: [" . implode(', ', $numbers) . "]\n";
$sorted = heapSort($numbers);
echo "Sorted:   [" . implode(', ', $sorted) . "]\n\n";

echo "Example 2: Visualized Heap Sort\n";
echo str_repeat('-', 70) . "\n";
$numbers = [4, 10, 3, 5, 1];
$result = heapSortVisualized($numbers);
echo "\nFinal: [" . implode(', ', $result) . "]\n\n";

echo "Example 3: Performance Comparison\n";
echo str_repeat('-', 70) . "\n";
echo sprintf("%-12s | %-15s | %-15s | %-15s\n", "Size", "Random", "Sorted", "Reversed");
echo str_repeat('-', 70) . "\n";

foreach ([100, 500, 1000, 5000] as $size) {
    $random = range(1, $size);
    shuffle($random);
    $start = microtime(true);
    heapSort($random);
    $randomTime = (microtime(true) - $start) * 1000;

    $sorted = range(1, $size);
    $start = microtime(true);
    heapSort($sorted);
    $sortedTime = (microtime(true) - $start) * 1000;

    $reversed = range($size, 1, -1);
    $start = microtime(true);
    heapSort($reversed);
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
echo "Key Features:\n";
echo "✓ Guaranteed O(n log n) - no worst case surprise\n";
echo "✓ In-place: O(1) extra space\n";
echo "✓ Predictable performance\n";
echo "✗ Not stable\n";
echo "✗ Poor cache locality (slower than quick sort in practice)\n";
