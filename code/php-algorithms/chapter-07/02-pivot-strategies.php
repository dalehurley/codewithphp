<?php

declare(strict_types=1);

/**
 * Quick Sort - Pivot Selection Strategies
 *
 * Demonstrates different pivot selection strategies and their impact on performance.
 */

function partition(array &$arr, int $low, int $high): int
{
    $pivot = $arr[$high];
    $i = $low - 1;

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    return $i + 1;
}

// Strategy 1: Last Element (Poor for sorted data)
function quickSortLast(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pi = partition($arr, $low, $high);
        quickSortLast($arr, $low, $pi - 1);
        quickSortLast($arr, $pi + 1, $high);
    }
}

// Strategy 2: Random Pivot (Good average case)
function partitionRandom(array &$arr, int $low, int $high): int
{
    $randomIndex = rand($low, $high);
    [$arr[$randomIndex], $arr[$high]] = [$arr[$high], $arr[$randomIndex]];
    return partition($arr, $low, $high);
}

function quickSortRandom(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pi = partitionRandom($arr, $low, $high);
        quickSortRandom($arr, $low, $pi - 1);
        quickSortRandom($arr, $pi + 1, $high);
    }
}

// Strategy 3: Median-of-Three (Best overall)
function partitionMedianOfThree(array &$arr, int $low, int $high): int
{
    $mid = (int)(($low + $high) / 2);

    // Order low, mid, high
    if ($arr[$mid] < $arr[$low]) {
        [$arr[$low], $arr[$mid]] = [$arr[$mid], $arr[$low]];
    }
    if ($arr[$high] < $arr[$low]) {
        [$arr[$low], $arr[$high]] = [$arr[$high], $arr[$low]];
    }
    if ($arr[$high] < $arr[$mid]) {
        [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];
    }

    // Use middle as pivot
    [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];
    return partition($arr, $low, $high);
}

function quickSortMedianOfThree(array &$arr, int $low, int $high): void
{
    if ($low < $high) {
        $pi = partitionMedianOfThree($arr, $low, $high);
        quickSortMedianOfThree($arr, $low, $pi - 1);
        quickSortMedianOfThree($arr, $pi + 1, $high);
    }
}

// ============================================================================
// Comparison Tests
// ============================================================================

echo "QUICK SORT - PIVOT STRATEGIES COMPARISON\n";
echo str_repeat('=', 70) . "\n\n";

echo "Test 1: Random Data (1000 elements)\n";
echo str_repeat('-', 70) . "\n";
$data = range(1, 1000);
shuffle($data);

$test = $data;
$start = microtime(true);
quickSortLast($test, 0, count($test) - 1);
echo sprintf("Last Element:      %.4f ms\n", (microtime(true) - $start) * 1000);

$test = $data;
$start = microtime(true);
quickSortRandom($test, 0, count($test) - 1);
echo sprintf("Random Pivot:      %.4f ms\n", (microtime(true) - $start) * 1000);

$test = $data;
$start = microtime(true);
quickSortMedianOfThree($test, 0, count($test) - 1);
echo sprintf("Median-of-Three:   %.4f ms (Best!)\n\n", (microtime(true) - $start) * 1000);

echo "Test 2: Already Sorted (WORST CASE for Last Element)\n";
echo str_repeat('-', 70) . "\n";
$sorted = range(1, 1000);

$test = $sorted;
$start = microtime(true);
quickSortLast($test, 0, count($test) - 1);
echo sprintf("Last Element:      %.4f ms (O(n²) disaster!)\n", (microtime(true) - $start) * 1000);

$test = $sorted;
$start = microtime(true);
quickSortRandom($test, 0, count($test) - 1);
echo sprintf("Random Pivot:      %.4f ms (saved by randomization!)\n", (microtime(true) - $start) * 1000);

$test = $sorted;
$start = microtime(true);
quickSortMedianOfThree($test, 0, count($test) - 1);
echo sprintf("Median-of-Three:   %.4f ms (handles sorted data well!)\n\n", (microtime(true) - $start) * 1000);

echo "Recommendation: Always use Random or Median-of-Three pivot!\n";
echo "Never use first/last element pivot in production code.\n";
