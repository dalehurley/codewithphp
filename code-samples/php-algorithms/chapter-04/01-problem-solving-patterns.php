<?php
/**
 * Problem-Solving Strategies
 *
 * Systematic approaches to algorithm problems
 *
 * @package PHPAlgorithms
 * @chapter 04
 */

declare(strict_types=1);

// ============================================================================
// TWO POINTERS
// ============================================================================

/**
 * Two Sum (sorted array)
 */
function twoSumSorted(array $nums, int $target): ?array
{
    $left = 0;
    $right = count($nums) - 1;

    while ($left < $right) {
        $sum = $nums[$left] + $nums[$right];

        if ($sum === $target) {
            return [$nums[$left], $nums[$right]];
        } elseif ($sum < $target) {
            $left++;
        } else {
            $right--;
        }
    }

    return null;
}

/**
 * Container with most water
 */
function maxArea(array $heights): int
{
    $left = 0;
    $right = count($heights) - 1;
    $maxArea = 0;

    while ($left < $right) {
        $width = $right - $left;
        $height = min($heights[$left], $heights[$right]);
        $area = $width * $height;
        $maxArea = max($maxArea, $area);

        if ($heights[$left] < $heights[$right]) {
            $left++;
        } else {
            $right--;
        }
    }

    return $maxArea;
}

// ============================================================================
// SLIDING WINDOW
// ============================================================================

/**
 * Longest substring without repeating characters
 */
function lengthOfLongestSubstring(string $str): int
{
    $charMap = [];
    $maxLength = 0;
    $start = 0;

    for ($end = 0; $end < strlen($str); $end++) {
        $char = $str[$end];

        if (isset($charMap[$char]) && $charMap[$char] >= $start) {
            $start = $charMap[$char] + 1;
        }

        $charMap[$char] = $end;
        $maxLength = max($maxLength, $end - $start + 1);
    }

    return $maxLength;
}

/**
 * Max sum of k consecutive elements
 */
function maxSumKElements(array $nums, int $k): int
{
    $windowSum = array_sum(array_slice($nums, 0, $k));
    $maxSum = $windowSum;

    for ($i = $k; $i < count($nums); $i++) {
        $windowSum = $windowSum - $nums[$i - $k] + $nums[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

// ============================================================================
// HASH MAP
// ============================================================================

/**
 * Two Sum (unsorted)
 */
function twoSum(array $nums, int $target): ?array
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
 * Group anagrams
 */
function groupAnagrams(array $words): array
{
    $groups = [];

    foreach ($words as $word) {
        $chars = str_split($word);
        sort($chars);
        $key = implode('', $chars);

        if (!isset($groups[$key])) {
            $groups[$key] = [];
        }
        $groups[$key][] = $word;
    }

    return array_values($groups);
}

// ============================================================================
// DIVIDE AND CONQUER
// ============================================================================

/**
 * Find maximum using divide and conquer
 */
function findMaxDivideConquer(array $nums, int $left, int $right): int
{
    if ($left === $right) {
        return $nums[$left];
    }

    $mid = (int)(($left + $right) / 2);
    $leftMax = findMaxDivideConquer($nums, $left, $mid);
    $rightMax = findMaxDivideConquer($nums, $mid + 1, $right);

    return max($leftMax, $rightMax);
}

// ============================================================================
// GREEDY ALGORITHMS
// ============================================================================

/**
 * Coin change (greedy - doesn't always work!)
 */
function coinChangeGreedy(array $coins, int $amount): int
{
    rsort($coins);
    $count = 0;

    foreach ($coins as $coin) {
        while ($amount >= $coin) {
            $amount -= $coin;
            $count++;
        }
    }

    return $amount === 0 ? $count : -1;
}

// ============================================================================
// BACKTRACKING
// ============================================================================

/**
 * Generate all combinations of size k
 */
function combinations(int $n, int $k): array
{
    $result = [];
    backtrackCombinations([], 1, $n, $k, $result);
    return $result;
}

function backtrackCombinations(array $current, int $start, int $n, int $k, array &$result): void
{
    if (count($current) === $k) {
        $result[] = $current;
        return;
    }

    for ($i = $start; $i <= $n; $i++) {
        $current[] = $i;
        backtrackCombinations($current, $i + 1, $n, $k, $result);
        array_pop($current);
    }
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Problem-Solving Strategies ===\n\n";

    // Two Pointers
    echo "1. Two Pointers:\n";
    $sorted = [1, 2, 3, 4, 5, 6, 7];
    $target = 9;
    $result = twoSumSorted($sorted, $target);
    echo "   Two sum ({$target}): [" . implode(', ', $result ?? []) . "]\n";

    $heights = [1, 8, 6, 2, 5, 4, 8, 3, 7];
    echo "   Max area: " . maxArea($heights) . "\n";

    // Sliding Window
    echo "\n2. Sliding Window:\n";
    $str = "abcabcbb";
    echo "   Longest substring '{$str}': " . lengthOfLongestSubstring($str) . "\n";

    $nums = [2, 1, 5, 1, 3, 2];
    echo "   Max sum (k=3): " . maxSumKElements($nums, 3) . "\n";

    // Hash Map
    echo "\n3. Hash Map:\n";
    $nums = [2, 7, 11, 15];
    $result = twoSum($nums, 9);
    echo "   Two sum indices: [" . implode(', ', $result ?? []) . "]\n";

    $words = ['eat', 'tea', 'tan', 'ate', 'nat', 'bat'];
    $groups = groupAnagrams($words);
    echo "   Anagram groups: " . count($groups) . "\n";

    // Divide and Conquer
    echo "\n4. Divide and Conquer:\n";
    $nums = [3, 7, 2, 9, 1, 5];
    $max = findMaxDivideConquer($nums, 0, count($nums) - 1);
    echo "   Max element: {$max}\n";

    // Greedy
    echo "\n5. Greedy:\n";
    $coins = [1, 5, 10, 25];
    $count = coinChangeGreedy($coins, 63);
    echo "   Coin change (63¢): {$count} coins\n";

    // Backtracking
    echo "\n6. Backtracking:\n";
    $combs = combinations(4, 2);
    echo "   Combinations C(4,2): " . count($combs) . " total\n";
    echo "   First 3: " . json_encode(array_slice($combs, 0, 3)) . "\n";
}
