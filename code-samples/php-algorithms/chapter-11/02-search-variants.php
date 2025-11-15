<?php

/**
 * Advanced Linear Search Variants
 *
 * Demonstrates sentinel search, jump search, and interpolation search.
 * These variants optimize linear search for specific use cases.
 *
 * @package CodeWithPHP\Algorithms\Chapter11
 */

declare(strict_types=1);

/**
 * Sentinel Linear Search
 *
 * Eliminates boundary check in the loop by placing target at the end
 * Time Complexity: O(n)
 * Space Complexity: O(1)
 *
 * @param array $arr The array to search
 * @param mixed $target The value to find
 * @return int|false The index if found, false otherwise
 */
function sentinelLinearSearch(array $arr, mixed $target): int|false
{
    $n = count($arr);

    if ($n === 0) {
        return false;
    }

    // Save last element
    $last = $arr[$n - 1];

    // Place sentinel at end
    $arr[$n - 1] = $target;

    $i = 0;

    // No need to check $i < $n because sentinel guarantees we'll find target
    while ($arr[$i] !== $target) {
        $i++;
    }

    // Restore last element
    $arr[$n - 1] = $last;

    // Check if we found actual target or just the sentinel
    if ($i < $n - 1 || $last === $target) {
        return $i;
    }

    return false;
}

/**
 * Jump Search
 *
 * Works on sorted arrays by jumping ahead by fixed steps
 * Time Complexity: O(√n)
 * Space Complexity: O(1)
 *
 * @param array $arr Sorted array to search
 * @param mixed $target The value to find
 * @return int|false The index if found, false otherwise
 */
function jumpSearch(array $arr, mixed $target): int|false
{
    $n = count($arr);
    $step = (int)sqrt($n);
    $prev = 0;

    // Jump ahead by step size
    while ($arr[min($step, $n) - 1] < $target) {
        $prev = $step;
        $step += (int)sqrt($n);

        if ($prev >= $n) {
            return false;
        }
    }

    // Linear search in the block
    while ($arr[$prev] < $target) {
        $prev++;

        if ($prev === min($step, $n)) {
            return false;
        }
    }

    // Check if element found
    if ($arr[$prev] === $target) {
        return $prev;
    }

    return false;
}

/**
 * Interpolation Search
 *
 * Works best on uniformly distributed sorted arrays
 * Time Complexity: O(log log n) average, O(n) worst case
 * Space Complexity: O(1)
 *
 * @param array $arr Sorted array with uniform distribution
 * @param int $target The value to find
 * @return int|false The index if found, false otherwise
 */
function interpolationSearch(array $arr, int $target): int|false
{
    $low = 0;
    $high = count($arr) - 1;

    while ($low <= $high && $target >= $arr[$low] && $target <= $arr[$high]) {
        if ($low === $high) {
            if ($arr[$low] === $target) {
                return $low;
            }
            return false;
        }

        // Estimate position using interpolation formula
        $pos = $low + (int)((($high - $low) / ($arr[$high] - $arr[$low])) *
                            ($target - $arr[$low]));

        if ($arr[$pos] === $target) {
            return $pos;
        }

        if ($arr[$pos] < $target) {
            $low = $pos + 1;
        } else {
            $high = $pos - 1;
        }
    }

    return false;
}

/**
 * Find all occurrences of a value
 *
 * @param array $arr The array to search
 * @param mixed $target The value to find
 * @return array Array of indices where target was found
 */
function findAllOccurrences(array $arr, mixed $target): array
{
    $indices = [];

    foreach ($arr as $index => $value) {
        if ($value === $target) {
            $indices[] = $index;
        }
    }

    return $indices;
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "=== Advanced Search Variants ===\n\n";

// Example 1: Sentinel Search
echo "Example 1: Sentinel Linear Search\n";
echo str_repeat('-', 50) . "\n";
$numbers = [4, 2, 7, 1, 9, 5];
$target = 7;
$result = sentinelLinearSearch($numbers, $target);
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "Searching for: $target\n";
echo "Result: " . ($result !== false ? "Found at index $result" : "Not found") . "\n";
echo "\n";

// Example 2: Jump Search on sorted array
echo "Example 2: Jump Search (√n jumps)\n";
echo str_repeat('-', 50) . "\n";
$sorted = [0, 1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144, 233, 377, 610];
$target = 55;
echo "Array: [" . implode(', ', $sorted) . "]\n";
echo "Searching for: $target\n";
echo "Step size: " . (int)sqrt(count($sorted)) . " (√" . count($sorted) . ")\n";
$result = jumpSearch($sorted, $target);
echo "Result: " . ($result !== false ? "Found at index $result" : "Not found") . "\n";
echo "\n";

// Example 3: Jump Search visualization
echo "Example 3: Jump Search with detailed steps\n";
echo str_repeat('-', 50) . "\n";
function jumpSearchVisualized(array $arr, $target): int|false
{
    $n = count($arr);
    $step = (int)sqrt($n);
    $prev = 0;
    $jumps = 0;

    echo "Array size: $n, Jump size: $step\n";
    echo "Array: [" . implode(', ', $arr) . "]\n\n";

    // Jump ahead
    while ($arr[min($step, $n) - 1] < $target) {
        $jumps++;
        echo "Jump $jumps: Checking position " . (min($step, $n) - 1) .
             " (value: {$arr[min($step, $n) - 1]})\n";
        $prev = $step;
        $step += (int)sqrt($n);

        if ($prev >= $n) {
            echo "Jumped beyond array bounds\n";
            return false;
        }
    }

    echo "\nLinear search in block [$prev, " . min($step, $n) . ")\n";

    // Linear search in block
    $comparisons = 0;
    while ($arr[$prev] < $target) {
        $comparisons++;
        echo "  Checking position $prev (value: {$arr[$prev]})\n";
        $prev++;

        if ($prev === min($step, $n)) {
            echo "Reached end of block without finding target\n";
            return false;
        }
    }

    if ($arr[$prev] === $target) {
        echo "  ✓ Found at position $prev!\n";
        echo "\nTotal operations: $jumps jumps + $comparisons comparisons = " .
             ($jumps + $comparisons) . "\n";
        return $prev;
    }

    return false;
}

$sorted = range(0, 20);
jumpSearchVisualized($sorted, 15);
echo "\n";

// Example 4: Interpolation Search
echo "Example 4: Interpolation Search\n";
echo str_repeat('-', 50) . "\n";
$uniform = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
$target = 70;
echo "Uniformly distributed array: [" . implode(', ', $uniform) . "]\n";
echo "Searching for: $target\n";
$result = interpolationSearch($uniform, $target);
echo "Result: " . ($result !== false ? "Found at index $result" : "Not found") . "\n";
echo "\n";

// Example 5: Find all occurrences
echo "Example 5: Find All Occurrences\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 3, 5, 3, 7, 3, 9];
$target = 3;
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "Searching for all occurrences of: $target\n";
$indices = findAllOccurrences($numbers, $target);
echo "Found at indices: [" . implode(', ', $indices) . "]\n";
echo "Total occurrences: " . count($indices) . "\n";
echo "\n";

// Example 6: Performance comparison
echo "Example 6: Performance Comparison\n";
echo str_repeat('-', 50) . "\n";
$testArray = range(1, 10000);
$target = 9999;
$iterations = 1000;

// Linear search
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    foreach ($testArray as $index => $value) {
        if ($value === $target) break;
    }
}
$linearTime = microtime(true) - $start;

// Sentinel search
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    sentinelLinearSearch($testArray, $target);
}
$sentinelTime = microtime(true) - $start;

// Jump search
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    jumpSearch($testArray, $target);
}
$jumpTime = microtime(true) - $start;

// Interpolation search
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    interpolationSearch($testArray, $target);
}
$interpTime = microtime(true) - $start;

printf("Linear Search:        %.6f seconds (baseline)\n", $linearTime);
printf("Sentinel Search:      %.6f seconds (%.2fx)\n",
       $sentinelTime, $linearTime / $sentinelTime);
printf("Jump Search:          %.6f seconds (%.2fx)\n",
       $jumpTime, $linearTime / $jumpTime);
printf("Interpolation Search: %.6f seconds (%.2fx)\n",
       $interpTime, $linearTime / $interpTime);
