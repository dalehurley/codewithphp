<?php

declare(strict_types=1);

/**
 * Exercise 3: Array Statistics Functions
 * 
 * Create functions to calculate statistics for an array of numbers:
 * - getAverage(array $numbers): float
 * - getMedian(array $numbers): float
 * - getMode(array $numbers): int|float (most frequent value)
 * - getRange(array $numbers): float (difference between max and min)
 * - getStats(array $numbers): array (return all stats)
 */

// Solution:

function getAverage(array $numbers): float
{
    if (count($numbers) === 0) {
        return 0.0;
    }

    return array_sum($numbers) / count($numbers);
}

function getMedian(array $numbers): float
{
    if (count($numbers) === 0) {
        return 0.0;
    }

    // Sort the array
    $sorted = $numbers;
    sort($sorted);

    $count = count($sorted);
    $middle = floor($count / 2);

    // If odd number of elements, return middle
    if ($count % 2 === 1) {
        return (float)$sorted[$middle];
    }

    // If even, return average of two middle elements
    return ($sorted[$middle - 1] + $sorted[$middle]) / 2;
}

function getMode(array $numbers): int|float|null
{
    if (count($numbers) === 0) {
        return null;
    }

    // Count frequencies
    $frequencies = array_count_values($numbers);

    // Find max frequency
    $maxFrequency = max($frequencies);

    // Get all values with max frequency
    $modes = array_keys($frequencies, $maxFrequency);

    // Return first mode (could return all modes in array)
    return $modes[0];
}

function getRange(array $numbers): float
{
    if (count($numbers) === 0) {
        return 0.0;
    }

    return max($numbers) - min($numbers);
}

function getStats(array $numbers): array
{
    return [
        'count' => count($numbers),
        'sum' => array_sum($numbers),
        'average' => getAverage($numbers),
        'median' => getMedian($numbers),
        'mode' => getMode($numbers),
        'min' => count($numbers) > 0 ? min($numbers) : 0,
        'max' => count($numbers) > 0 ? max($numbers) : 0,
        'range' => getRange($numbers)
    ];
}

// Testing the functions
echo "=== Array Statistics Functions ===" . PHP_EOL . PHP_EOL;

$numbers = [12, 15, 18, 12, 20, 25, 30, 12, 18];

echo "Dataset: " . implode(', ', $numbers) . PHP_EOL . PHP_EOL;

echo "Individual Statistics:" . PHP_EOL;
echo "  Average: " . round(getAverage($numbers), 2) . PHP_EOL;
echo "  Median: " . round(getMedian($numbers), 2) . PHP_EOL;
echo "  Mode (most frequent): " . getMode($numbers) . PHP_EOL;
echo "  Range: " . getRange($numbers) . PHP_EOL;
echo "  Min: " . min($numbers) . PHP_EOL;
echo "  Max: " . max($numbers) . PHP_EOL;
echo PHP_EOL;

echo "All Statistics Combined:" . PHP_EOL;
$stats = getStats($numbers);
foreach ($stats as $key => $value) {
    $displayValue = is_float($value) ? round($value, 2) : $value;
    echo "  " . ucfirst($key) . ": $displayValue" . PHP_EOL;
}
echo PHP_EOL;

// Test with different dataset
echo "=== Test with Grade Dataset ===" . PHP_EOL;
$grades = [85, 92, 78, 92, 88, 95, 82, 92, 90];
echo "Grades: " . implode(', ', $grades) . PHP_EOL . PHP_EOL;

$gradeStats = getStats($grades);
echo "Statistics:" . PHP_EOL;
foreach ($gradeStats as $key => $value) {
    $displayValue = is_float($value) ? round($value, 2) : $value;
    echo "  " . ucfirst($key) . ": $displayValue" . PHP_EOL;
}
echo PHP_EOL;

// Test median with even and odd counts
echo "=== Median Tests ===" . PHP_EOL;
$oddCount = [1, 3, 5, 7, 9];
echo "Odd count [1, 3, 5, 7, 9]: Median = " . getMedian($oddCount) . PHP_EOL;

$evenCount = [1, 3, 5, 7];
echo "Even count [1, 3, 5, 7]: Median = " . getMedian($evenCount) . PHP_EOL;
