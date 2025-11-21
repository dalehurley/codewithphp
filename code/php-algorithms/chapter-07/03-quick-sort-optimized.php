<?php

declare(strict_types=1);

/**
 * Optimized Quick Sort - 3-Way Partitioning & Hybrid Approach
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

// 3-Way Partitioning (Dutch National Flag)
function partition3Way(array &$arr, int $low, int $high): array
{
    $pivot = $arr[$low];
    $lt = $low;      // arr[low..lt-1] < pivot
    $i = $low + 1;   // arr[lt..i-1] == pivot
    $gt = $high;     // arr[gt+1..high] > pivot

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

    return [$lt, $gt];
}

function quickSort3Way(array &$arr, int $low, int $high): void
{
    if ($low >= $high) return;

    [$lt, $gt] = partition3Way($arr, $low, $high);

    quickSort3Way($arr, $low, $lt - 1);
    quickSort3Way($arr, $gt + 1, $high);
}

// Hybrid: Insertion for small + Median-of-3 + 3-Way
function quickSortHybrid(array &$arr, int $low, int $high): void
{
    if ($high - $low < 10) {
        insertionSortRange($arr, $low, $high);
        return;
    }

    if ($low < $high) {
        $mid = (int)(($low + $high) / 2);
        if ($arr[$mid] < $arr[$low]) [$arr[$low], $arr[$mid]] = [$arr[$mid], $arr[$low]];
        if ($arr[$high] < $arr[$low]) [$arr[$low], $arr[$high]] = [$arr[$high], $arr[$low]];
        if ($arr[$high] < $arr[$mid]) [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];
        [$arr[$mid], $arr[$low]] = [$arr[$low], $arr[$mid]];

        [$lt, $gt] = partition3Way($arr, $low, $high);
        quickSortHybrid($arr, $low, $lt - 1);
        quickSortHybrid($arr, $gt + 1, $high);
    }
}

// ============================================================================
// Benchmarks
// ============================================================================

echo "OPTIMIZED QUICK SORT\n";
echo str_repeat('=', 70) . "\n\n";

echo "Test: Array with Many Duplicates (1000 elements, only 10 unique)\n";
echo str_repeat('-', 70) . "\n";
$duplicates = array_map(fn() => rand(1, 10), range(1, 1000));

$test = $duplicates;
$start = microtime(true);
sort($test); // Standard sort
$standardTime = (microtime(true) - $start) * 1000;

$test = $duplicates;
$start = microtime(true);
quickSort3Way($test, 0, count($test) - 1);
$threeWayTime = (microtime(true) - $start) * 1000;

$test = $duplicates;
$start = microtime(true);
quickSortHybrid($test, 0, count($test) - 1);
$hybridTime = (microtime(true) - $start) * 1000;

echo sprintf("Standard Sort:   %.4f ms\n", $standardTime);
echo sprintf("3-Way Quick:     %.4f ms\n", $threeWayTime);
echo sprintf("Hybrid (Best):   %.4f ms\n\n", $hybridTime);

echo "Benefits:\n";
echo "✓ 3-Way: O(n log k) where k = unique values\n";
echo "✓ Hybrid: Combines best of insertion + quick sort\n";
echo "✓ Production-ready performance\n";
