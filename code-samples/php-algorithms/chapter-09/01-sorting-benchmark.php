<?php

declare(strict_types=1);

/**
 * Comprehensive Sorting Algorithm Comparison
 * Benchmarks all sorting algorithms on different data patterns
 */

// Import all sorting implementations
function insertionSort(array $arr): array
{
    $n = count($arr);
    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $key;
    }
    return $arr;
}

function mergeSort(array $arr): array
{
    if (count($arr) <= 1) return $arr;
    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

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

function quickSort(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pivot = $arr[$high];
        $i = $low - 1;
        for ($j = $low; $j < $high; $j++) {
            if ($arr[$j] < $pivot) {
                $i++;
                [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
            }
        }
        [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
        $pi = $i + 1;
        quickSort($arr, $low, $pi - 1);
        quickSort($arr, $pi + 1, $high);
    }
}

function heapSort(array $arr): array
{
    $n = count($arr);

    $heapify = function(&$arr, $i, $size) use (&$heapify) {
        $largest = $i;
        $left = 2 * $i + 1;
        $right = 2 * $i + 2;

        if ($left < $size && $arr[$left] > $arr[$largest]) $largest = $left;
        if ($right < $size && $arr[$right] > $arr[$largest]) $largest = $right;

        if ($largest !== $i) {
            [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
            $heapify($arr, $largest, $size);
        }
    };

    for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
        $heapify($arr, $i, $n);
    }

    for ($i = $n - 1; $i > 0; $i--) {
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];
        $heapify($arr, 0, $i);
    }

    return $arr;
}

function benchmark(string $name, callable $sortFunc, array $data): float
{
    $start = microtime(true);
    $sortFunc($data);
    return (microtime(true) - $start) * 1000;
}

// ============================================================================
// Benchmarks
// ============================================================================

echo "COMPREHENSIVE SORTING ALGORITHM COMPARISON\n";
echo str_repeat('=', 80) . "\n\n";

$algorithms = [
    'Insertion Sort' => fn($arr) => insertionSort($arr),
    'Merge Sort' => fn($arr) => mergeSort($arr),
    'Quick Sort' => function($arr) {
        quickSort($arr, 0, count($arr) - 1);
        return $arr;
    },
    'Heap Sort' => fn($arr) => heapSort($arr),
    'PHP sort()' => function($arr) {
        sort($arr);
        return $arr;
    },
];

// Test 1: Small Random
echo "Test 1: Small Array (50 elements) - Random\n";
echo str_repeat('-', 80) . "\n";
$data = range(1, 50);
shuffle($data);

foreach ($algorithms as $name => $func) {
    $time = benchmark($name, $func, $data);
    echo sprintf("%-20s: %10.4f ms\n", $name, $time);
}
echo "\n";

// Test 2: Medium Random
echo "Test 2: Medium Array (500 elements) - Random\n";
echo str_repeat('-', 80) . "\n";
$data = range(1, 500);
shuffle($data);

foreach ($algorithms as $name => $func) {
    $time = benchmark($name, $func, $data);
    echo sprintf("%-20s: %10.4f ms\n", $name, $time);
}
echo "\n";

// Test 3: Large Random
echo "Test 3: Large Array (2000 elements) - Random\n";
echo str_repeat('-', 80) . "\n";
$data = range(1, 2000);
shuffle($data);

$efficientAlgorithms = [
    'Merge Sort' => $algorithms['Merge Sort'],
    'Quick Sort' => $algorithms['Quick Sort'],
    'Heap Sort' => $algorithms['Heap Sort'],
    'PHP sort()' => $algorithms['PHP sort()'],
];

foreach ($efficientAlgorithms as $name => $func) {
    $time = benchmark($name, $func, $data);
    echo sprintf("%-20s: %10.4f ms\n", $name, $time);
}
echo "Note: Skipped O(n²) algorithms for large datasets\n\n";

// Test 4: Nearly Sorted
echo "Test 4: Nearly Sorted (1000 elements, 5% unsorted)\n";
echo str_repeat('-', 80) . "\n";
$data = range(1, 1000);
for ($i = 0; $i < 50; $i++) {
    $a = rand(0, 999);
    $b = rand(0, 999);
    [$data[$a], $data[$b]] = [$data[$b], $data[$a]];
}

foreach ($efficientAlgorithms as $name => $func) {
    $time = benchmark($name, $func, $data);
    echo sprintf("%-20s: %10.4f ms\n", $name, $time);
}
echo "\n";

// Summary
echo "SUMMARY & RECOMMENDATIONS\n";
echo str_repeat('=', 80) . "\n\n";

echo "When to use each algorithm:\n\n";

echo "Insertion Sort:\n";
echo "  ✓ Small arrays (< 50 elements)\n";
echo "  ✓ Nearly sorted data (O(n) performance!)\n";
echo "  ✓ Online sorting (sort as data arrives)\n";
echo "  Best: O(n) | Average: O(n²) | Worst: O(n²) | Space: O(1)\n\n";

echo "Merge Sort:\n";
echo "  ✓ Need guaranteed O(n log n)\n";
echo "  ✓ Stability required\n";
echo "  ✓ External sorting\n";
echo "  Best: O(n log n) | Average: O(n log n) | Worst: O(n log n) | Space: O(n)\n\n";

echo "Quick Sort:\n";
echo "  ✓ General-purpose sorting (most common choice)\n";
echo "  ✓ Average case performance critical\n";
echo "  ✓ In-place sorting needed\n";
echo "  Best: O(n log n) | Average: O(n log n) | Worst: O(n²) | Space: O(log n)\n\n";

echo "Heap Sort:\n";
echo "  ✓ Guaranteed O(n log n) with O(1) space\n";
echo "  ✓ Memory constrained environments\n";
echo "  ✓ Priority queue applications\n";
echo "  Best: O(n log n) | Average: O(n log n) | Worst: O(n log n) | Space: O(1)\n\n";

echo "PHP sort():\n";
echo "  ✓ Production code (highly optimized)\n";
echo "  ✓ Don't know data pattern\n";
echo "  ✓ Want best average performance\n";
echo "  Uses hybrid algorithm (similar to Timsort)\n";
