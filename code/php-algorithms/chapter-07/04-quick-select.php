<?php

declare(strict_types=1);

/**
 * QuickSelect Algorithm - Finding Kth Smallest Element
 * Average: O(n), Worst: O(n²) with random pivot
 * Much faster than sorting entire array!
 */

function partitionRandom(array &$arr, int $low, int $high): int
{
    $randomIndex = rand($low, $high);
    [$arr[$randomIndex], $arr[$high]] = [$arr[$high], $arr[$randomIndex]];

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

function quickSelect(array &$arr, int $k): mixed
{
    return quickSelectHelper($arr, 0, count($arr) - 1, $k - 1);
}

function quickSelectHelper(array &$arr, int $low, int $high, int $k): mixed
{
    if ($low === $high) {
        return $arr[$low];
    }

    $pivotIndex = partitionRandom($arr, $low, $high);

    if ($k === $pivotIndex) {
        return $arr[$k];
    } elseif ($k < $pivotIndex) {
        return quickSelectHelper($arr, $low, $pivotIndex - 1, $k);
    } else {
        return quickSelectHelper($arr, $pivotIndex + 1, $high, $k);
    }
}

// ============================================================================
// Examples
// ============================================================================

echo "QUICKSELECT - FIND KTH ELEMENT\n";
echo str_repeat('=', 70) . "\n\n";

$numbers = [7, 10, 4, 3, 20, 15, 8, 2];
echo "Array: [" . implode(', ', $numbers) . "]\n\n";

echo "Finding elements:\n";
echo "3rd smallest: " . quickSelect($numbers, 3) . "\n";
echo "5th smallest: " . quickSelect($numbers, 5) . "\n";
echo "1st smallest (min): " . quickSelect($numbers, 1) . "\n";
echo "8th smallest (max): " . quickSelect($numbers, 8) . "\n\n";

// Performance comparison
echo "Performance: QuickSelect vs Full Sort\n";
echo str_repeat('-', 70) . "\n";

$large = range(1, 10000);
shuffle($large);

$test = $large;
$start = microtime(true);
$result = quickSelect($test, 5000);
$quickSelectTime = (microtime(true) - $start) * 1000;

$test = $large;
$start = microtime(true);
sort($test);
$result = $test[4999];
$sortTime = (microtime(true) - $start) * 1000;

echo sprintf("QuickSelect: %.4f ms (O(n) average)\n", $quickSelectTime);
echo sprintf("Full Sort:   %.4f ms (O(n log n))\n", $sortTime);
echo sprintf("Speed up:    %.1fx faster!\n\n", $sortTime / $quickSelectTime);

echo "Use Cases:\n";
echo "✓ Find median without full sort\n";
echo "✓ Find top K elements efficiently\n";
echo "✓ Order statistics problems\n";
