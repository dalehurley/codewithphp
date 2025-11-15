<?php

/**
 * Basic Linear Search Implementation
 *
 * Demonstrates the fundamental linear search algorithm and its variations.
 * Time Complexity: O(n)
 * Space Complexity: O(1)
 *
 * @package CodeWithPHP\Algorithms\Chapter11
 */

declare(strict_types=1);

/**
 * Performs linear search on an array
 *
 * @param array $arr The array to search
 * @param mixed $target The value to find
 * @return int|false The index if found, false otherwise
 */
function linearSearch(array $arr, mixed $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

/**
 * Linear search using for loop (alternative implementation)
 *
 * @param array $arr The array to search
 * @param mixed $target The value to find
 * @return int|false The index if found, false otherwise
 */
function linearSearchLoop(array $arr, mixed $target): int|false
{
    $n = count($arr);

    for ($i = 0; $i < $n; $i++) {
        if ($arr[$i] === $target) {
            return $i;
        }
    }

    return false;
}

/**
 * Linear search with early termination for sorted arrays
 *
 * @param array $arr Sorted array to search
 * @param int $target The value to find
 * @return int|false The index if found, false otherwise
 */
function linearSearchSortedWithTermination(array $arr, int $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }

        // Early termination: target can't be in remaining elements
        if ($value > $target) {
            return false;
        }
    }

    return false;
}

/**
 * Visualized linear search for demonstration
 *
 * @param array $arr The array to search
 * @param mixed $target The value to find
 * @return int|false The index if found, false otherwise
 */
function linearSearchVisualized(array $arr, mixed $target): int|false
{
    $comparisons = 0;

    echo "Searching for: $target\n";
    echo "Array: [" . implode(', ', $arr) . "]\n\n";

    foreach ($arr as $index => $value) {
        $comparisons++;
        echo "Step $comparisons: Checking index $index => $value ";

        if ($value === $target) {
            echo "✓ FOUND!\n";
            echo "Total comparisons: $comparisons\n";
            return $index;
        }

        echo "✗\n";
    }

    echo "\nNot found after $comparisons comparisons\n";
    return false;
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "=== Basic Linear Search Examples ===\n\n";

// Example 1: Simple array search
echo "Example 1: Search in integer array\n";
echo str_repeat('-', 50) . "\n";
$numbers = [4, 2, 7, 1, 9, 5];
$target = 7;
$result = linearSearch($numbers, $target);

if ($result !== false) {
    echo "Found $target at index $result\n";
} else {
    echo "$target not found\n";
}
echo "\n";

// Example 2: Element not found
echo "Example 2: Search for non-existent element\n";
echo str_repeat('-', 50) . "\n";
$target = 8;
$result = linearSearch($numbers, $target);

if ($result !== false) {
    echo "Found $target at index $result\n";
} else {
    echo "$target not found in array\n";
}
echo "\n";

// Example 3: Search in string array
echo "Example 3: Search in string array\n";
echo str_repeat('-', 50) . "\n";
$fruits = ['apple', 'banana', 'orange', 'grape', 'mango'];
$target = 'grape';
$result = linearSearch($fruits, $target);

if ($result !== false) {
    echo "Found '$target' at index $result\n";
} else {
    echo "'$target' not found\n";
}
echo "\n";

// Example 4: Visualized search
echo "Example 4: Visualized search process\n";
echo str_repeat('-', 50) . "\n";
$numbers = [10, 23, 45, 70, 11, 15];
linearSearchVisualized($numbers, 11);
echo "\n";

// Example 5: Early termination on sorted array
echo "Example 5: Early termination on sorted array\n";
echo str_repeat('-', 50) . "\n";
$sorted = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
echo "Searching for 6 in sorted array\n";
echo "Array: [" . implode(', ', $sorted) . "]\n";
$result = linearSearchSortedWithTermination($sorted, 6);

if ($result !== false) {
    echo "Found at index $result\n";
} else {
    echo "Not found (terminated early when value exceeded target)\n";
}
echo "\n";

// Example 6: First element
echo "Example 6: Best case - element at beginning\n";
echo str_repeat('-', 50) . "\n";
$numbers = [42, 17, 8, 3, 11];
linearSearchVisualized($numbers, 42);
echo "\n";

// Example 7: Last element
echo "Example 7: Worst case - element at end\n";
echo str_repeat('-', 50) . "\n";
$numbers = [42, 17, 8, 3, 11];
linearSearchVisualized($numbers, 11);
echo "\n";

// Example 8: Comparing both implementations
echo "Example 8: Comparing implementations\n";
echo str_repeat('-', 50) . "\n";
$testArray = range(1, 1000);
$target = 999;

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    linearSearch($testArray, $target);
}
$time1 = microtime(true) - $start;

$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    linearSearchLoop($testArray, $target);
}
$time2 = microtime(true) - $start;

printf("foreach implementation: %.6f seconds\n", $time1);
printf("for loop implementation: %.6f seconds\n", $time2);
printf("Difference: %.2f%%\n", abs($time1 - $time2) / min($time1, $time2) * 100);
