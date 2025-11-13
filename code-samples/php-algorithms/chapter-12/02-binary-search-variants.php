<?php

/**
 * Binary Search Variants
 *
 * Find first/last occurrence, insertion point, and count occurrences.
 *
 * @package CodeWithPHP\Algorithms\Chapter12
 */

declare(strict_types=1);

function findFirst(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $result = false;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $right = $mid - 1; // Continue searching left
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

function findLast(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;
    $result = false;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            $result = $mid;
            $left = $mid + 1; // Continue searching right
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $result;
}

function findInsertPosition(array $arr, int $target): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $left;
}

function countOccurrences(array $arr, int $target): int
{
    $first = findFirst($arr, $target);

    if ($first === false) {
        return 0;
    }

    $last = findLast($arr, $target);
    return $last - $first + 1;
}

// DEMONSTRATIONS
echo "=== Binary Search Variants ===\n\n";

echo "Example 1: Find first occurrence\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 2, 2, 2, 3, 4, 5];
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "First occurrence of 2: " . findFirst($numbers, 2) . "\n\n";

echo "Example 2: Find last occurrence\n";
echo str_repeat('-', 50) . "\n";
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "Last occurrence of 2: " . findLast($numbers, 2) . "\n\n";

echo "Example 3: Find insertion position\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 3, 5, 7, 9];
foreach ([0, 4, 6, 10] as $target) {
    echo "Insert position for $target: " . findInsertPosition($numbers, $target) . "\n";
}
echo "\n";

echo "Example 4: Count occurrences\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 2, 2, 2, 3, 4, 5];
echo "Count of 2: " . countOccurrences($numbers, 2) . "\n";
