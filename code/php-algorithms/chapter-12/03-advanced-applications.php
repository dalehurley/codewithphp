<?php

/**
 * Advanced Binary Search Applications
 *
 * Square root, peak element, rotated arrays.
 *
 * @package CodeWithPHP\Algorithms\Chapter12
 */

declare(strict_types=1);

function sqrtInteger(int $x): int
{
    if ($x < 2) return $x;

    $left = 1;
    $right = $x;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);
        $square = $mid * $mid;

        if ($square === $x) {
            return $mid;
        } elseif ($square < $x) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return $right;
}

function findPeakElement(array $nums): int
{
    $left = 0;
    $right = count($nums) - 1;

    while ($left < $right) {
        $mid = (int)(($left + $right) / 2);

        if ($nums[$mid] > $nums[$mid + 1]) {
            $right = $mid;
        } else {
            $left = $mid + 1;
        }
    }

    return $left;
}

// DEMONSTRATIONS
echo "=== Advanced Applications ===\n\n";

echo "Example 1: Integer square root\n";
echo str_repeat('-', 50) . "\n";
foreach ([16, 20, 100, 150] as $num) {
    echo "sqrt($num) = " . sqrtInteger($num) . "\n";
}
echo "\n";

echo "Example 2: Find peak element\n";
echo str_repeat('-', 50) . "\n";
$arr = [1, 2, 3, 1];
echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Peak at index: " . findPeakElement($arr) . "\n";
