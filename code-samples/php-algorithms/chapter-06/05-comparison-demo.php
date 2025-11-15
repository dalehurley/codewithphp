<?php

declare(strict_types=1);

/**
 * Insertion Sort vs Merge Sort - Direct Comparison
 *
 * This file demonstrates when to use each algorithm through
 * side-by-side comparisons on different data patterns.
 */

require_once '01-insertion-sort.php';
require_once '02-merge-sort.php';

/**
 * Benchmark a sorting function
 *
 * @param callable $sortFunc Sorting function
 * @param array<int> $arr Array to sort
 * @return array{time: float, result: array<int>}
 */
function benchmark(callable $sortFunc, array $arr): array
{
    $start = microtime(true);
    $result = $sortFunc($arr);
    $time = (microtime(true) - $start) * 1000;

    return ['time' => $time, 'result' => $result];
}

/**
 * Compare algorithms on different data patterns
 *
 * @param string $name Pattern name
 * @param array<int> $data Test data
 * @return void
 */
function compareAlgorithms(string $name, array $data): void
{
    echo "\n$name\n";
    echo str_repeat('-', 70) . "\n";
    echo "Array size: " . count($data) . "\n";

    $insertionResult = benchmark('insertionSort', $data);
    $mergeResult = benchmark('mergeSort', $data);

    echo sprintf("Insertion Sort: %10.4f ms\n", $insertionResult['time']);
    echo sprintf("Merge Sort:     %10.4f ms\n", $mergeResult['time']);

    if ($insertionResult['time'] < $mergeResult['time']) {
        $ratio = $mergeResult['time'] / $insertionResult['time'];
        echo sprintf("Winner: Insertion Sort (%.1fx faster)\n", $ratio);
    } else {
        $ratio = $insertionResult['time'] / $mergeResult['time'];
        echo sprintf("Winner: Merge Sort (%.1fx faster)\n", $ratio);
    }
}

// ============================================================================
// Comparison Tests
// ============================================================================

echo "INSERTION SORT vs MERGE SORT - HEAD-TO-HEAD COMPARISON\n";
echo str_repeat('=', 70) . "\n";

// Test 1: Very small array
echo "\nTest 1: Very Small Array (10 elements)\n";
echo str_repeat('-', 70) . "\n";
$small = [9, 3, 7, 1, 5, 2, 8, 4, 6, 0];
compareAlgorithms("Random data", $small);
echo "Analysis: Insertion sort wins due to lower overhead\n";

// Test 2: Medium array - random
echo "\nTest 2: Medium Array - Random (100 elements)\n";
echo str_repeat('-', 70) . "\n";
$medium = range(1, 100);
shuffle($medium);
compareAlgorithms("Random data", $medium);
echo "Analysis: Starting to favor merge sort\n";

// Test 3: Large array - random
echo "\nTest 3: Large Array - Random (1000 elements)\n";
echo str_repeat('-', 70) . "\n";
$large = range(1, 1000);
shuffle($large);
compareAlgorithms("Random data", $large);
echo "Analysis: Merge sort dominates at O(n log n) vs O(n²)\n";

// Test 4: Already sorted
echo "\nTest 4: Already Sorted Data (1000 elements)\n";
echo str_repeat('-', 70) . "\n";
$sorted = range(1, 1000);
compareAlgorithms("Already sorted", $sorted);
echo "Analysis: Insertion sort wins with O(n) best case!\n";

// Test 5: Reverse sorted
echo "\nTest 5: Reverse Sorted Data (1000 elements)\n";
echo str_repeat('-', 70) . "\n";
$reversed = range(1000, 1, -1);
compareAlgorithms("Reverse sorted", $reversed);
echo "Analysis: Merge sort wins - worst case for insertion sort\n";

// Test 6: Nearly sorted
echo "\nTest 6: Nearly Sorted Data (1000 elements, 5% swaps)\n";
echo str_repeat('-', 70) . "\n";
$nearlySorted = range(1, 1000);
$swaps = 50;
for ($i = 0; $i < $swaps; $i++) {
    $a = rand(0, 999);
    $b = rand(0, 999);
    [$nearlySorted[$a], $nearlySorted[$b]] = [$nearlySorted[$b], $nearlySorted[$a]];
}
compareAlgorithms("Nearly sorted (95%)", $nearlySorted);
echo "Analysis: Insertion sort excellent for nearly sorted data!\n";

// Test 7: Many duplicates
echo "\nTest 7: Many Duplicates (1000 elements, only 10 unique values)\n";
echo str_repeat('-', 70) . "\n";
$duplicates = [];
for ($i = 0; $i < 1000; $i++) {
    $duplicates[] = rand(1, 10);
}
compareAlgorithms("Many duplicates", $duplicates);

// Test 8: All equal
echo "\nTest 8: All Equal Elements (1000 elements)\n";
echo str_repeat('-', 70) . "\n";
$equal = array_fill(0, 1000, 5);
compareAlgorithms("All equal", $equal);
echo "Analysis: Insertion sort fastest - immediate termination\n";

// Comprehensive size comparison
echo "\n\nCOMPREHENSIVE SIZE COMPARISON - RANDOM DATA\n";
echo str_repeat('=', 70) . "\n";
echo sprintf("%-12s | %-15s | %-15s | %-12s\n", "Size", "Insertion", "Merge", "Winner");
echo str_repeat('-', 70) . "\n";

foreach ([10, 20, 50, 100, 200, 500, 1000, 2000] as $size) {
    $data = range(1, $size);
    shuffle($data);

    $insertionResult = benchmark('insertionSort', $data);
    $mergeResult = benchmark('mergeSort', $data);

    $winner = $insertionResult['time'] < $mergeResult['time'] ? 'Insertion' : 'Merge';

    echo sprintf(
        "%-12d | %11.4f ms | %11.4f ms | %-12s\n",
        $size,
        $insertionResult['time'],
        $mergeResult['time'],
        $winner
    );
}

// Decision matrix
echo "\n\nDECISION MATRIX: WHEN TO USE EACH ALGORITHM\n";
echo str_repeat('=', 70) . "\n\n";

echo "Use INSERTION SORT when:\n";
echo "  ✓ Array size < 50 elements\n";
echo "  ✓ Data is nearly sorted (huge advantage!)\n";
echo "  ✓ Data arrives online (can sort as it comes)\n";
echo "  ✓ Need stable sort with O(1) space\n";
echo "  ✓ Simplicity and code clarity important\n";
echo "  ✓ Memory writes are expensive\n\n";

echo "Use MERGE SORT when:\n";
echo "  ✓ Array size > 1000 elements\n";
echo "  ✓ Need guaranteed O(n log n) performance\n";
echo "  ✓ Stability is required (preserves order of equal elements)\n";
echo "  ✓ Data distribution unknown\n";
echo "  ✓ Sorting linked lists (natural fit)\n";
echo "  ✓ External sorting (data doesn't fit in memory)\n";
echo "  ✓ Predictable performance is critical\n\n";

echo "SWEET SPOTS:\n";
echo "  • Insertion: 10-50 elements OR nearly sorted data\n";
echo "  • Merge: 100+ elements with random/unknown patterns\n";
echo "  • Hybrid: Use insertion for small, merge for large (best of both!)\n\n";

echo "CHARACTERISTICS COMPARISON:\n";
echo str_repeat('-', 70) . "\n";
$characteristics = [
    ['Characteristic', 'Insertion Sort', 'Merge Sort'],
    ['Best case', 'O(n)', 'O(n log n)'],
    ['Average case', 'O(n²)', 'O(n log n)'],
    ['Worst case', 'O(n²)', 'O(n log n)'],
    ['Space', 'O(1)', 'O(n)'],
    ['Stable', 'Yes', 'Yes'],
    ['In-place', 'Yes', 'No'],
    ['Adaptive', 'Yes', 'No'],
    ['Online', 'Yes', 'No'],
    ['Cache-friendly', 'Excellent', 'Good'],
];

foreach ($characteristics as $row) {
    echo sprintf("%-20s | %-20s | %-20s\n", ...$row);
}

echo "\n";
echo "KEY INSIGHT: There's no single \"best\" sorting algorithm!\n";
echo "The best choice depends on:\n";
echo "  - Data size\n";
echo "  - Data patterns (sorted, random, etc.)\n";
echo "  - Memory constraints\n";
echo "  - Stability requirements\n";
echo "  - Performance predictability needs\n";
