<?php

declare(strict_types=1);

namespace ComputerScience\Chapter02;

/**
 * Common Array Algorithms
 *
 * Collection of frequently-used array manipulation algorithms
 * that appear in technical interviews and real-world applications.
 */
class ArrayAlgorithms
{
    /**
     * Linear Search - Find index of target value
     *
     * Time: O(n), Space: O(1)
     *
     * @param array<int|float> $arr Array to search
     * @param int|float $target Value to find
     * @return int|null Index if found, null otherwise
     */
    public static function linearSearch(array $arr, int|float $target): ?int
    {
        foreach ($arr as $index => $value) {
            if ($value === $target) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Binary Search - Find index of target in sorted array
     *
     * Time: O(log n), Space: O(1)
     * REQUIREMENT: Array must be sorted!
     *
     * @param array<int|float> $arr Sorted array to search
     * @param int|float $target Value to find
     * @return int|null Index if found, null otherwise
     */
    public static function binarySearch(array $arr, int|float $target): ?int
    {
        $left = 0;
        $right = count($arr) - 1;

        while ($left <= $right) {
            $mid = $left + (int)(($right - $left) / 2);

            if ($arr[$mid] === $target) {
                return $mid;
            }

            if ($arr[$mid] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        return null;
    }

    /**
     * Reverse array in-place
     *
     * Time: O(n), Space: O(1)
     *
     * @param array<mixed> $arr Array to reverse
     * @return array<mixed> Reversed array
     */
    public static function reverse(array $arr): array
    {
        $left = 0;
        $right = count($arr) - 1;

        while ($left < $right) {
            // Swap elements
            $temp = $arr[$left];
            $arr[$left] = $arr[$right];
            $arr[$right] = $temp;

            $left++;
            $right--;
        }

        return $arr;
    }

    /**
     * Rotate array to the right by k positions
     *
     * Example: [1,2,3,4,5] rotated by 2 → [4,5,1,2,3]
     * Time: O(n), Space: O(1)
     *
     * @param array<mixed> $arr Array to rotate
     * @param int $k Number of positions to rotate
     * @return array<mixed> Rotated array
     */
    public static function rotateRight(array $arr, int $k): array
    {
        $n = count($arr);
        if ($n === 0) return $arr;

        $k = $k % $n; // Handle k > n
        if ($k === 0) return $arr;

        // Reverse entire array
        $arr = self::reverse($arr);

        // Reverse first k elements
        $part1 = self::reverse(array_slice($arr, 0, $k));

        // Reverse remaining elements
        $part2 = self::reverse(array_slice($arr, $k));

        return array_merge($part1, $part2);
    }

    /**
     * Find missing number in array containing 1 to n
     *
     * Time: O(n), Space: O(1)
     *
     * @param array<int> $arr Array with one missing number
     * @return int The missing number
     */
    public static function findMissingNumber(array $arr): int
    {
        $n = count($arr) + 1; // Should have n elements
        $expectedSum = ($n * ($n + 1)) / 2;
        $actualSum = array_sum($arr);

        return (int)($expectedSum - $actualSum);
    }

    /**
     * Remove duplicates from sorted array in-place
     *
     * Time: O(n), Space: O(1)
     * Returns new length and modifies array
     *
     * @param array<int|float|string> $arr Sorted array with duplicates
     * @return int New length after removing duplicates
     */
    public static function removeDuplicates(array &$arr): int
    {
        if (empty($arr)) {
            return 0;
        }

        $writeIndex = 1;

        for ($i = 1; $i < count($arr); $i++) {
            if ($arr[$i] !== $arr[$i - 1]) {
                $arr[$writeIndex] = $arr[$i];
                $writeIndex++;
            }
        }

        // Truncate array
        $arr = array_slice($arr, 0, $writeIndex);

        return $writeIndex;
    }

    /**
     * Two Sum - Find two indices that sum to target
     *
     * Time: O(n), Space: O(n)
     *
     * @param array<int|float> $nums Array of numbers
     * @param int|float $target Target sum
     * @return array{int, int}|null Pair of indices or null
     */
    public static function twoSum(array $nums, int|float $target): ?array
    {
        $seen = [];

        foreach ($nums as $i => $num) {
            $complement = $target - $num;

            if (isset($seen[$complement])) {
                return [$seen[$complement], $i];
            }

            $seen[$num] = $i;
        }

        return null;
    }

    /**
     * Maximum Subarray Sum (Kadane's Algorithm)
     *
     * Find contiguous subarray with largest sum.
     * Time: O(n), Space: O(1)
     *
     * @param array<int|float> $arr Array of numbers
     * @return int|float Maximum sum
     */
    public static function maxSubarraySum(array $arr): int|float
    {
        if (empty($arr)) {
            return 0;
        }

        $maxSoFar = $maxEndingHere = $arr[0];

        for ($i = 1; $i < count($arr); $i++) {
            $maxEndingHere = max($arr[$i], $maxEndingHere + $arr[$i]);
            $maxSoFar = max($maxSoFar, $maxEndingHere);
        }

        return $maxSoFar;
    }

    /**
     * Merge two sorted arrays
     *
     * Time: O(m + n), Space: O(m + n)
     *
     * @param array<int|float> $arr1 First sorted array
     * @param array<int|float> $arr2 Second sorted array
     * @return array<int|float> Merged sorted array
     */
    public static function mergeSorted(array $arr1, array $arr2): array
    {
        $result = [];
        $i = $j = 0;
        $m = count($arr1);
        $n = count($arr2);

        while ($i < $m && $j < $n) {
            if ($arr1[$i] <= $arr2[$j]) {
                $result[] = $arr1[$i++];
            } else {
                $result[] = $arr2[$j++];
            }
        }

        // Add remaining elements
        while ($i < $m) {
            $result[] = $arr1[$i++];
        }

        while ($j < $n) {
            $result[] = $arr2[$j++];
        }

        return $result;
    }

    /**
     * Find all pairs that sum to target
     *
     * Time: O(n), Space: O(n)
     *
     * @param array<int|float> $arr Array of numbers
     * @param int|float $target Target sum
     * @return array<array{int|float, int|float}> Array of pairs
     */
    public static function findPairs(array $arr, int|float $target): array
    {
        $seen = [];
        $pairs = [];

        foreach ($arr as $num) {
            $complement = $target - $num;

            if (isset($seen[$complement])) {
                $pairs[] = [$complement, $num];
            }

            $seen[$num] = true;
        }

        return $pairs;
    }

    /**
     * Move all zeros to end of array
     *
     * Time: O(n), Space: O(1)
     *
     * @param array<int|float> $arr Array with zeros
     * @return array<int|float> Array with zeros at end
     */
    public static function moveZerosToEnd(array $arr): array
    {
        $writeIndex = 0;

        // Move non-zero elements forward
        for ($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] !== 0) {
                $arr[$writeIndex++] = $arr[$i];
            }
        }

        // Fill remaining with zeros
        for ($i = $writeIndex; $i < count($arr); $i++) {
            $arr[$i] = 0;
        }

        return $arr;
    }

    /**
     * Find intersection of two arrays
     *
     * Time: O(m + n), Space: O(min(m, n))
     *
     * @param array<int|float|string> $arr1 First array
     * @param array<int|float|string> $arr2 Second array
     * @return array<int|float|string> Common elements
     */
    public static function intersection(array $arr1, array $arr2): array
    {
        $set1 = array_flip($arr1);
        $result = [];

        foreach ($arr2 as $value) {
            if (isset($set1[$value])) {
                $result[] = $value;
                unset($set1[$value]); // Avoid duplicates
            }
        }

        return $result;
    }

    /**
     * Check if array is sorted
     *
     * Time: O(n), Space: O(1)
     *
     * @param array<int|float> $arr Array to check
     * @param bool $ascending Check for ascending order (default: true)
     * @return bool True if sorted, false otherwise
     */
    public static function isSorted(array $arr, bool $ascending = true): bool
    {
        if (count($arr) <= 1) {
            return true;
        }

        for ($i = 1; $i < count($arr); $i++) {
            if ($ascending) {
                if ($arr[$i] < $arr[$i - 1]) {
                    return false;
                }
            } else {
                if ($arr[$i] > $arr[$i - 1]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Find peak element (greater than its neighbors)
     *
     * Time: O(n), Space: O(1)
     *
     * @param array<int|float> $arr Array of numbers
     * @return int|null Index of peak element or null
     */
    public static function findPeak(array $arr): ?int
    {
        $n = count($arr);

        if ($n === 0) {
            return null;
        }

        if ($n === 1) {
            return 0;
        }

        // Check first element
        if ($arr[0] > $arr[1]) {
            return 0;
        }

        // Check last element
        if ($arr[$n - 1] > $arr[$n - 2]) {
            return $n - 1;
        }

        // Check middle elements
        for ($i = 1; $i < $n - 1; $i++) {
            if ($arr[$i] > $arr[$i - 1] && $arr[$i] > $arr[$i + 1]) {
                return $i;
            }
        }

        return null;
    }
}
