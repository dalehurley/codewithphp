<?php

/**
 * Basic Binary Search Implementation
 *
 * Demonstrates iterative and recursive binary search algorithms.
 * Time Complexity: O(log n)
 * Space Complexity: O(1) iterative, O(log n) recursive
 *
 * @package CodeWithPHP\Algorithms\Chapter12
 */

declare(strict_types=1);

/**
 * Iterative binary search
 *
 * @param array $arr Sorted array to search
 * @param int $target Value to find
 * @return int|false Index if found, false otherwise
 */
function binarySearch(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        // Calculate middle index (avoiding overflow)
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid; // Found!
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1; // Search right half
        } else {
            $right = $mid - 1; // Search left half
        }
    }

    return false; // Not found
}

/**
 * Recursive binary search
 *
 * @param array $arr Sorted array to search
 * @param int $target Value to find
 * @param int $left Left boundary
 * @param int|null $right Right boundary
 * @return int|false Index if found, false otherwise
 */
function binarySearchRecursive(
    array $arr,
    int $target,
    int $left = 0,
    int|null $right = null
): int|false {
    if ($right === null) {
        $right = count($arr) - 1;
    }

    // Base case: search space exhausted
    if ($left > $right) {
        return false;
    }

    $mid = $left + (int)(($right - $left) / 2);

    if ($arr[$mid] === $target) {
        return $mid;
    } elseif ($arr[$mid] < $target) {
        // Recursive case: search right half
        return binarySearchRecursive($arr, $target, $mid + 1, $right);
    } else {
        // Recursive case: search left half
        return binarySearchRecursive($arr, $target, $left, $mid - 1);
    }
}

/**
 * Visualized binary search for demonstration
 *
 * @param array $arr Sorted array to search
 * @param int $target Value to find
 * @return int|false Index if found, false otherwise
 */
function binarySearchVisualized(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $step = 1;

    echo "Searching for: $target\n";
    echo "Array: [" . implode(', ', $arr) . "]\n\n";

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        // Visual representation
        echo "Step $step:\n";
        echo "  Search space: [" . implode(', ', array_slice($arr, $left, $right - $left + 1)) . "]\n";
        echo "  Left: $left, Mid: $mid, Right: $right\n";
        echo "  Checking arr[$mid] = {$arr[$mid]}\n";

        if ($arr[$mid] === $target) {
            echo "  ✓ Found at index $mid!\n";
            echo "\nTotal steps: $step\n";
            return $mid;
        } elseif ($arr[$mid] < $target) {
            echo "  → Target is greater, search right half\n\n";
            $left = $mid + 1;
        } else {
            echo "  ← Target is smaller, search left half\n\n";
            $right = $mid - 1;
        }

        $step++;
    }

    echo "Not found after $step steps\n";
    return false;
}

/**
 * Binary search that returns insert position if not found
 *
 * @param array $arr Sorted array
 * @param int $target Value to find or insert
 * @return array ['found' => bool, 'index' => int]
 */
function binarySearchWithInsertPos(array $arr, int $target): array
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return ['found' => true, 'index' => $mid];
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    // $left is the insert position
    return ['found' => false, 'index' => $left];
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "=== Basic Binary Search ===\n\n";

// Example 1: Simple binary search
echo "Example 1: Basic binary search\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
$target = 13;
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "Searching for: $target\n";
$result = binarySearch($numbers, $target);
echo "Result: " . ($result !== false ? "Found at index $result" : "Not found") . "\n";
echo "\n";

// Example 2: Element not found
echo "Example 2: Search for non-existent element\n";
echo str_repeat('-', 50) . "\n";
$target = 8;
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "Searching for: $target\n";
$result = binarySearch($numbers, $target);
echo "Result: " . ($result !== false ? "Found at index $result" : "Not found") . "\n";
echo "\n";

// Example 3: Visualized search
echo "Example 3: Visualized binary search\n";
echo str_repeat('-', 50) . "\n";
binarySearchVisualized($numbers, 13);
echo "\n";

// Example 4: Recursive binary search
echo "Example 4: Recursive implementation\n";
echo str_repeat('-', 50) . "\n";
$numbers = [2, 4, 6, 8, 10, 12, 14, 16, 18, 20];
$target = 14;
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "Searching for: $target (recursive)\n";
$result = binarySearchRecursive($numbers, $target);
echo "Result: " . ($result !== false ? "Found at index $result" : "Not found") . "\n";
echo "\n";

// Example 5: First element
echo "Example 5: Best case - first element\n";
echo str_repeat('-', 50) . "\n";
$numbers = [10, 20, 30, 40, 50];
binarySearchVisualized($numbers, 10);
echo "\n";

// Example 6: Last element
echo "Example 6: Last element\n";
echo str_repeat('-', 50) . "\n";
$numbers = [10, 20, 30, 40, 50];
binarySearchVisualized($numbers, 50);
echo "\n";

// Example 7: Middle element
echo "Example 7: Middle element (ideal case)\n";
echo str_repeat('-', 50) . "\n";
$numbers = [10, 20, 30, 40, 50];
binarySearchVisualized($numbers, 30);
echo "\n";

// Example 8: Large array
echo "Example 8: Search in large sorted array\n";
echo str_repeat('-', 50) . "\n";
$largeArray = range(1, 10000);
$target = 7856;
echo "Array: 1 to 10,000 (10,000 elements)\n";
echo "Searching for: $target\n";

$steps = 0;
$left = 0;
$right = count($largeArray) - 1;

while ($left <= $right) {
    $steps++;
    $mid = $left + (int)(($right - $left) / 2);

    if ($largeArray[$mid] === $target) {
        echo "Found at index $mid after $steps steps\n";
        echo "Linear search would take ~$target steps\n";
        echo "Binary search speedup: ~" . round($target / $steps, 2) . "x faster\n";
        break;
    } elseif ($largeArray[$mid] < $target) {
        $left = $mid + 1;
    } else {
        $right = $mid - 1;
    }
}
echo "\n";

// Example 9: Binary search with insert position
echo "Example 9: Find insert position\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 3, 5, 7, 9];
$targets = [5, 6, 0, 10];

echo "Array: [" . implode(', ', $numbers) . "]\n\n";
foreach ($targets as $target) {
    $result = binarySearchWithInsertPos($numbers, $target);
    if ($result['found']) {
        echo "Target $target: Found at index {$result['index']}\n";
    } else {
        echo "Target $target: Not found, insert at index {$result['index']}\n";
    }
}
echo "\n";

// Example 10: Comparison - Iterative vs Recursive
echo "Example 10: Performance - Iterative vs Recursive\n";
echo str_repeat('-', 50) . "\n";
$testArray = range(1, 100000);
$target = 99999;
$iterations = 10000;

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    binarySearch($testArray, $target);
}
$iterativeTime = microtime(true) - $start;

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    binarySearchRecursive($testArray, $target);
}
$recursiveTime = microtime(true) - $start;

printf("Iterative: %.6f seconds\n", $iterativeTime);
printf("Recursive: %.6f seconds\n", $recursiveTime);
printf("Difference: %.2f%%\n", abs($iterativeTime - $recursiveTime) / min($iterativeTime, $recursiveTime) * 100);
echo "\nNote: Iterative is usually faster due to no function call overhead\n";
