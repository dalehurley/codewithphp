<?php

/**
 * Common Array Patterns
 *
 * Two pointers, sliding window, prefix sum, Kadane's algorithm.
 *
 * @package CodeWithPHP\Algorithms\Chapter15
 */

declare(strict_types=1);

function twoPointers(array $arr, int $target): ?array
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        $sum = $arr[$left] + $arr[$right];

        if ($sum === $target) {
            return [$left, $right];
        } elseif ($sum < $target) {
            $left++;
        } else {
            $right--;
        }
    }

    return null;
}

function maxSumSubarray(array $arr, int $k): int
{
    $n = count($arr);
    if ($n < $k) return 0;

    $windowSum = array_sum(array_slice($arr, 0, $k));
    $maxSum = $windowSum;

    for ($i = $k; $i < $n; $i++) {
        $windowSum = $windowSum - $arr[$i - $k] + $arr[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

class PrefixSum
{
    private array $prefix;

    public function __construct(array $arr)
    {
        $n = count($arr);
        $this->prefix = array_fill(0, $n + 1, 0);

        for ($i = 0; $i < $n; $i++) {
            $this->prefix[$i + 1] = $this->prefix[$i] + $arr[$i];
        }
    }

    public function rangeSum(int $left, int $right): int
    {
        return $this->prefix[$right + 1] - $this->prefix[$left];
    }
}

function kadaneMaxSubarray(array $arr): int
{
    $maxSoFar = $arr[0];
    $maxEndingHere = $arr[0];

    for ($i = 1; $i < count($arr); $i++) {
        $maxEndingHere = max($arr[$i], $maxEndingHere + $arr[$i]);
        $maxSoFar = max($maxSoFar, $maxEndingHere);
    }

    return $maxSoFar;
}

// DEMONSTRATIONS
echo "=== Array Patterns ===\n\n";

echo "Example 1: Two pointers (find pair with sum)\n";
echo str_repeat('-', 50) . "\n";
$arr = [1, 2, 3, 4, 5, 6];
$result = twoPointers($arr, 9);
echo "Pair with sum 9: [" . implode(', ', $result) . "]\n\n";

echo "Example 2: Sliding window (max sum of k elements)\n";
echo str_repeat('-', 50) . "\n";
$arr = [1, 4, 2, 10, 23, 3, 1, 0, 20];
echo "Max sum of 4 consecutive elements: " . maxSumSubarray($arr, 4) . "\n\n";

echo "Example 3: Prefix sum (range queries)\n";
echo str_repeat('-', 50) . "\n";
$arr = [1, 2, 3, 4, 5];
$ps = new PrefixSum($arr);
echo "Sum [1, 3]: " . $ps->rangeSum(1, 3) . "\n\n";

echo "Example 4: Kadane's algorithm (max subarray)\n";
echo str_repeat('-', 50) . "\n";
$arr = [-2, 1, -3, 4, -1, 2, 1, -5, 4];
echo "Max subarray sum: " . kadaneMaxSubarray($arr) . "\n";
