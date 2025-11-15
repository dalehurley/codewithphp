<?php

declare(strict_types=1);

/**
 * Two Sum Problem - Classic Hash Table Application
 *
 * Demonstrates:
 * - Using hash tables to solve the two-sum problem
 * - O(n) solution vs O(n²) brute force
 * - Complement pattern
 * - Multiple solutions handling
 */

echo "=== Two Sum Problem ===\n\n";

echo "Problem: Find two numbers in an array that add up to a target sum.\n";
echo "Example: nums = [2, 7, 11, 15], target = 9\n";
echo "Answer: indices [0, 1] because nums[0] + nums[1] = 2 + 7 = 9\n\n";

/**
 * Brute Force Solution - O(n²)
 */
function twoSumBruteForce(array $nums, int $target): ?array
{
    $n = count($nums);

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($nums[$i] + $nums[$j] === $target) {
                return [$i, $j];
            }
        }
    }

    return null;
}

/**
 * Hash Table Solution - O(n)
 * Key insight: For each number, check if (target - number) exists
 */
function twoSumHashTable(array $nums, int $target): ?array
{
    $map = []; // value => index

    foreach ($nums as $i => $num) {
        $complement = $target - $num;

        if (isset($map[$complement])) {
            return [$map[$complement], $i];
        }

        $map[$num] = $i;
    }

    return null;
}

/**
 * Find all pairs that sum to target
 */
function twoSumAllPairs(array $nums, int $target): array
{
    $map = [];
    $pairs = [];
    $seen = [];

    foreach ($nums as $i => $num) {
        $complement = $target - $num;

        if (isset($map[$complement])) {
            // Create sorted pair key to avoid duplicates
            $pairKey = $complement < $num
                ? "$complement,$num"
                : "$num,$complement";

            if (!isset($seen[$pairKey])) {
                $pairs[] = [$map[$complement], $i];
                $seen[$pairKey] = true;
            }
        }

        $map[$num] = $i;
    }

    return $pairs;
}

/**
 * Count pairs that sum to target
 */
function countPairs(array $nums, int $target): int
{
    $map = [];
    $count = 0;

    foreach ($nums as $num) {
        $complement = $target - $num;

        if (isset($map[$complement])) {
            $count += $map[$complement];
        }

        if (!isset($map[$num])) {
            $map[$num] = 0;
        }
        $map[$num]++;
    }

    return $count;
}

// Test Case 1: Basic example
echo "Test 1: Basic Example\n";
echo "======================\n";
$nums1 = [2, 7, 11, 15];
$target1 = 9;

echo "Array: [" . implode(', ', $nums1) . "]\n";
echo "Target: $target1\n\n";

$start = microtime(true);
$result1Brute = twoSumBruteForce($nums1, $target1);
$timeBrute = (microtime(true) - $start) * 1000000;

$start = microtime(true);
$result1Hash = twoSumHashTable($nums1, $target1);
$timeHash = (microtime(true) - $start) * 1000000;

echo "Brute Force: ";
if ($result1Brute) {
    echo "indices [{$result1Brute[0]}, {$result1Brute[1]}] ";
    echo "({$nums1[$result1Brute[0]]} + {$nums1[$result1Brute[1]]} = $target1)";
} else {
    echo "No solution";
}
echo " [" . number_format($timeBrute, 2) . " μs]\n";

echo "Hash Table:  ";
if ($result1Hash) {
    echo "indices [{$result1Hash[0]}, {$result1Hash[1]}] ";
    echo "({$nums1[$result1Hash[0]]} + {$nums1[$result1Hash[1]]} = $target1)";
} else {
    echo "No solution";
}
echo " [" . number_format($timeHash, 2) . " μs]\n\n";

// Test Case 2: Multiple pairs
echo "Test 2: Multiple Pairs\n";
echo "=======================\n";
$nums2 = [1, 3, 2, 4, 5, 3, 6];
$target2 = 6;

echo "Array: [" . implode(', ', $nums2) . "]\n";
echo "Target: $target2\n\n";

$allPairs = twoSumAllPairs($nums2, $target2);
echo "All pairs that sum to $target2:\n";
foreach ($allPairs as $pair) {
    echo "  indices [{$pair[0]}, {$pair[1]}]: ";
    echo "{$nums2[$pair[0]]} + {$nums2[$pair[1]]} = $target2\n";
}

$pairCount = countPairs($nums2, $target2);
echo "Total pair count: $pairCount\n\n";

// Test Case 3: No solution
echo "Test 3: No Solution\n";
echo "====================\n";
$nums3 = [1, 2, 3, 4];
$target3 = 10;

echo "Array: [" . implode(', ', $nums3) . "]\n";
echo "Target: $target3\n\n";

$result3 = twoSumHashTable($nums3, $target3);
echo "Result: " . ($result3 ? "Found" : "No solution") . "\n\n";

// Test Case 4: Large array performance comparison
echo "Test 4: Performance Comparison (Large Array)\n";
echo "=============================================\n";
$nums4 = range(1, 1000);
shuffle($nums4);
$target4 = 1500;

echo "Array size: " . count($nums4) . " elements\n";
echo "Target: $target4\n\n";

$start = microtime(true);
$result4Brute = twoSumBruteForce($nums4, $target4);
$timeBrute = (microtime(true) - $start) * 1000;

$start = microtime(true);
$result4Hash = twoSumHashTable($nums4, $target4);
$timeHash = (microtime(true) - $start) * 1000;

echo "Brute Force (O(n²)): " . number_format($timeBrute, 3) . " ms\n";
echo "Hash Table  (O(n)):  " . number_format($timeHash, 3) . " ms\n";
echo "Speedup: " . number_format($timeBrute / $timeHash, 1) . "x faster\n\n";

echo "Key Insights:\n";
echo "  • Hash table stores complements: O(1) lookup\n";
echo "  • Space-time tradeoff: O(n) space for O(n) time\n";
echo "  • Complement pattern: target - num = what we need\n";
echo "  • One pass through array vs nested loops\n";

echo "\n✅ Complete! Two-sum problem solutions demonstrated.\n";
