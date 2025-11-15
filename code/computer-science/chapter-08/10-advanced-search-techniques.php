<?php

declare(strict_types=1);

/**
 * Advanced Search Techniques
 *
 * Demonstrates:
 * - Ternary search for unimodal functions
 * - Fibonacci search
 * - Sublist search
 * - Pattern matching (KMP algorithm intro)
 * - Two-pointer technique
 * - Sliding window search
 * - Binary search on answer
 * - Advanced optimization techniques
 */

echo "=== Advanced Search Techniques ===\n\n";

/**
 * Ternary Search - Find maximum in unimodal function
 */
function ternarySearch(callable $func, float $left, float $right, float $precision = 0.0001): float
{
    while ($right - $left > $precision) {
        $mid1 = $left + ($right - $left) / 3;
        $mid2 = $right - ($right - $left) / 3;

        if ($func($mid1) < $func($mid2)) {
            $left = $mid1;
        } else {
            $right = $mid2;
        }
    }

    return ($left + $right) / 2;
}

/**
 * Fibonacci Search
 */
function fibonacciSearch(array $arr, mixed $target): ?int
{
    $n = count($arr);

    // Generate Fibonacci numbers
    $fib2 = 0;  // (m-2)'th Fibonacci number
    $fib1 = 1;  // (m-1)'th Fibonacci number
    $fib = $fib1 + $fib2; // m'th Fibonacci

    // Find smallest Fibonacci number >= n
    while ($fib < $n) {
        $fib2 = $fib1;
        $fib1 = $fib;
        $fib = $fib1 + $fib2;
    }

    $offset = -1;

    while ($fib > 1) {
        $i = min($offset + $fib2, $n - 1);

        if ($arr[$i] < $target) {
            $fib = $fib1;
            $fib1 = $fib2;
            $fib2 = $fib - $fib1;
            $offset = $i;
        } elseif ($arr[$i] > $target) {
            $fib = $fib2;
            $fib1 = $fib1 - $fib2;
            $fib2 = $fib - $fib1;
        } else {
            return $i;
        }
    }

    // Check last element
    if ($fib1 && $offset + 1 < $n && $arr[$offset + 1] === $target) {
        return $offset + 1;
    }

    return null;
}

/**
 * Sublist Search - Find if list2 is sublist of list1
 */
function isSublist(array $list1, array $list2): bool
{
    $n1 = count($list1);
    $n2 = count($list2);

    if ($n2 > $n1) return false;
    if ($n2 === 0) return true;

    for ($i = 0; $i <= $n1 - $n2; $i++) {
        $match = true;

        for ($j = 0; $j < $n2; $j++) {
            if ($list1[$i + $j] !== $list2[$j]) {
                $match = false;
                break;
            }
        }

        if ($match) return true;
    }

    return false;
}

/**
 * Two Pointers - Find pair with target sum
 */
function twoSum(array $arr, int $target): ?array
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

/**
 * Sliding Window - Find maximum sum subarray of size k
 */
function maxSumSubarray(array $arr, int $k): int
{
    $n = count($arr);
    if ($n < $k) return 0;

    // Compute sum of first window
    $maxSum = 0;
    for ($i = 0; $i < $k; $i++) {
        $maxSum += $arr[$i];
    }

    $windowSum = $maxSum;

    // Slide window
    for ($i = $k; $i < $n; $i++) {
        $windowSum = $windowSum - $arr[$i - $k] + $arr[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

/**
 * Binary Search on Answer - Find minimum capacity
 */
function canShipWithinDays(array $weights, int $days, int $capacity): bool
{
    $daysNeeded = 1;
    $currentLoad = 0;

    foreach ($weights as $weight) {
        if ($currentLoad + $weight > $capacity) {
            $daysNeeded++;
            $currentLoad = $weight;
        } else {
            $currentLoad += $weight;
        }
    }

    return $daysNeeded <= $days;
}

function shipWithinDays(array $weights, int $days): int
{
    $left = max($weights);  // Min capacity is max single weight
    $right = array_sum($weights); // Max capacity is sum of all

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if (canShipWithinDays($weights, $days, $mid)) {
            $right = $mid;
        } else {
            $left = $mid + 1;
        }
    }

    return $left;
}

/**
 * Find first bad version (binary search on unknown function)
 */
function findFirstBad(callable $isBad, int $n): int
{
    $left = 1;
    $right = $n;

    while ($left < $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($isBad($mid)) {
            $right = $mid;
        } else {
            $left = $mid + 1;
        }
    }

    return $left;
}

// Test 1: Ternary Search
echo "1. Ternary Search (Find Maximum)\n";
echo "==================================\n";

// Unimodal function: has single peak
$parabola = fn($x) => -($x - 5) * ($x - 5) + 25;

echo "Function: f(x) = -(x-5)² + 25\n";
echo "This is a downward parabola with peak at x=5\n\n";

$maxX = ternarySearch($parabola, 0, 10);
$maxY = $parabola($maxX);

echo "Ternary search result:\n";
echo "  Maximum at x ≈ " . number_format($maxX, 4) . "\n";
echo "  f(x) ≈ " . number_format($maxY, 4) . "\n\n";

echo "Use case: Optimization problems with single peak/valley\n";
echo "Time: O(log n) like binary search\n\n";

// Test 2: Fibonacci Search
echo "2. Fibonacci Search\n";
echo "====================\n";

$arr = [10, 22, 35, 40, 45, 50, 80, 82, 85, 90, 100];
echo "Array: [" . implode(', ', $arr) . "]\n\n";

$searches = [85, 100, 99];

foreach ($searches as $target) {
    $result = fibonacciSearch($arr, $target);

    if ($result !== null) {
        echo "Search for $target: Found at index $result\n";
    } else {
        echo "Search for $target: Not found\n";
    }
}

echo "\nFibonacci search:\n";
echo "  • Similar to binary search\n";
echo "  • Uses Fibonacci numbers for division\n";
echo "  • Good for sequential access (tape storage)\n";
echo "  • O(log n) time\n\n";

// Test 3: Sublist Search
echo "3. Sublist Search (Pattern in Sequence)\n";
echo "=========================================\n";

$main = [1, 2, 3, 4, 5, 6, 7, 8, 9];
$patterns = [
    [3, 4, 5],
    [5, 6, 7],
    [1, 3, 5],
    [7, 8, 9],
];

echo "Main list: [" . implode(', ', $main) . "]\n\n";

foreach ($patterns as $pattern) {
    $found = isSublist($main, $pattern);
    echo "Pattern [" . implode(', ', $pattern) . "]: " . ($found ? "Found" : "Not found") . "\n";
}

echo "\nApplications:\n";
echo "  • DNA sequence matching\n";
echo "  • Network packet inspection\n";
echo "  • Log pattern detection\n\n";

// Test 4: Two Pointers Technique
echo "4. Two Pointers Technique\n";
echo "==========================\n";

$sortedArr = [1, 2, 3, 4, 6, 8, 9, 14, 15];
$target = 13;

echo "Sorted array: [" . implode(', ', $sortedArr) . "]\n";
echo "Find two numbers that sum to: $target\n\n";

$result = twoSum($sortedArr, $target);

if ($result !== null) {
    [$i, $j] = $result;
    echo "Found: {$sortedArr[$i]} + {$sortedArr[$j]} = $target\n";
    echo "Indices: [$i, $j]\n";
} else {
    echo "No pair found\n";
}

echo "\nTwo pointers technique:\n";
echo "  • O(n) solution for sorted arrays\n";
echo "  • Move pointers based on comparison\n";
echo "  • Alternative to O(n²) brute force\n\n";

// Test 5: Sliding Window
echo "5. Sliding Window Search\n";
echo "=========================\n";

$arr = [2, 1, 5, 1, 3, 2];
$k = 3;

echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Window size k = $k\n\n";

$maxSum = maxSumSubarray($arr, $k);

echo "Maximum sum of subarray of size $k: $maxSum\n";

// Show the window
echo "\nAll windows of size $k:\n";
for ($i = 0; $i <= count($arr) - $k; $i++) {
    $window = array_slice($arr, $i, $k);
    $sum = array_sum($window);
    $marker = $sum === $maxSum ? " ← MAX" : "";
    echo "  [" . implode(', ', $window) . "] → sum = $sum$marker\n";
}

echo "\nSliding window:\n";
echo "  • O(n) instead of O(n*k)\n";
echo "  • Add new element, remove old element\n";
echo "  • Applications: Moving averages, substring problems\n\n";

// Test 6: Binary Search on Answer
echo "6. Binary Search on Answer\n";
echo "===========================\n";

$weights = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$days = 5;

echo "Ship packages with weights: [" . implode(', ', $weights) . "]\n";
echo "Must ship within $days days\n";
echo "Find minimum ship capacity\n\n";

$minCapacity = shipWithinDays($weights, $days);

echo "Minimum capacity: $minCapacity\n\n";

// Show how it works
echo "Verification:\n";
$currentDay = 1;
$currentLoad = 0;

foreach ($weights as $i => $weight) {
    if ($currentLoad + $weight > $minCapacity) {
        echo "  Day $currentDay: Load = $currentLoad\n";
        $currentDay++;
        $currentLoad = $weight;
    } else {
        $currentLoad += $weight;
    }
}
echo "  Day $currentDay: Load = $currentLoad\n";
echo "  Total days: $currentDay\n\n";

echo "Binary search on answer:\n";
echo "  • Search space is possible answers, not array indices\n";
echo "  • Need monotonic property (larger capacity → always works)\n";
echo "  • O(log(max-min) * O(verification))\n\n";

// Test 7: Find First Bad Version
echo "7. Find First Bad Version\n";
echo "==========================\n";

$firstBadVersion = 4;
$isBad = fn($version) => $version >= $firstBadVersion;

$totalVersions = 10;

echo "Versions: 1 to $totalVersions\n";
echo "First bad version: $firstBadVersion (unknown to algorithm)\n\n";

$result = findFirstBad($isBad, $totalVersions);

echo "Found first bad version: $result\n";
echo "API calls: ~" . (int)ceil(log($totalVersions, 2)) . " (O(log n))\n\n";

echo "Instead of checking all versions (O(n)), use binary search\n";
echo "Real-world: Finding first failing commit in version control\n\n";

// Test 8: Search in Infinite Array
echo "8. Search in Unbounded/Infinite Array\n";
echo "=======================================\n";

// Simulate infinite array with function
$infiniteArray = fn($i) => $i * 2; // [0, 2, 4, 6, 8, ...]
$target = 100;

echo "Infinite array: [0, 2, 4, 6, 8, ...] (generated on demand)\n";
echo "Target: $target\n\n";

// Exponential search to find range
$i = 1;
while ($infiniteArray($i) < $target) {
    $i *= 2;
}

echo "Exponential search found range: [" . ($i / 2) . "..$i]\n";

// Binary search in range
$left = (int)($i / 2);
$right = $i;

while ($left <= $right) {
    $mid = $left + (int)(($right - $left) / 2);
    $value = $infiniteArray($mid);

    if ($value === $target) {
        echo "Found at index: $mid\n";
        break;
    }

    if ($value < $target) {
        $left = $mid + 1;
    } else {
        $right = $mid - 1;
    }
}

echo "Time: O(log k) where k is position of target\n\n";

// Test 9: Advanced Pattern: Meet in the Middle
echo "9. Meet in the Middle\n";
echo "======================\n";

echo "Problem: Find if subset sums to target\n";
echo "Array: [3, 34, 4, 12, 5, 2]\n";
echo "Target: 9\n\n";

$arr = [3, 34, 4, 12, 5, 2];
$target = 9;

// Split array in half
$mid = (int)(count($arr) / 2);
$left = array_slice($arr, 0, $mid);
$right = array_slice($arr, $mid);

// Generate all subset sums for left half
function getSubsetSums(array $arr): array
{
    $sums = [0];

    foreach ($arr as $num) {
        $newSums = [];
        foreach ($sums as $sum) {
            $newSums[] = $sum + $num;
        }
        $sums = array_merge($sums, $newSums);
    }

    return $sums;
}

$leftSums = getSubsetSums($left);
$rightSums = getSubsetSums($right);

sort($leftSums);
sort($rightSums);

echo "Left subset sums: [" . implode(', ', array_slice($leftSums, 0, 8)) . "...]\n";
echo "Right subset sums: [" . implode(', ', array_slice($rightSums, 0, 8)) . "...]\n\n";

$found = false;
foreach ($leftSums as $leftSum) {
    $need = $target - $leftSum;
    if (in_array($need, $rightSums)) {
        echo "Found! $leftSum (from left) + $need (from right) = $target\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "No subset sums to $target\n";
}

echo "\nMeet in the middle:\n";
echo "  • Reduce O(2^n) to O(2^(n/2))\n";
echo "  • Split problem, solve halves, combine\n\n";

// Test 10: Summary and Best Practices
echo "10. Advanced Search Patterns Summary\n";
echo "======================================\n\n";

echo "Ternary Search:\n";
echo "  • Find max/min in unimodal function\n";
echo "  • O(log n) time\n";
echo "  • Applications: Optimization, finding peaks\n\n";

echo "Fibonacci Search:\n";
echo "  • Like binary search with Fibonacci division\n";
echo "  • Better for sequential access\n";
echo "  • O(log n) time\n\n";

echo "Two Pointers:\n";
echo "  • O(n) for sorted array problems\n";
echo "  • Two sum, three sum, container with most water\n";
echo "  • Move pointers based on comparison\n\n";

echo "Sliding Window:\n";
echo "  • O(n) for subarray/substring problems\n";
echo "  • Maximum sum, longest substring, etc.\n";
echo "  • Maintain window invariant\n\n";

echo "Binary Search on Answer:\n";
echo "  • Search space is answers, not indices\n";
echo "  • Need monotonic property\n";
echo "  • Capacity problems, optimization\n\n";

echo "Meet in the Middle:\n";
echo "  • Reduce exponential to sqrt of exponential\n";
echo "  • Subset sum, closest pair sum\n";
echo "  • O(2^(n/2)) vs O(2^n)\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "When to Use Each:\n";
echo "  1. Unimodal function → Ternary search\n";
echo "  2. Sorted array pairs → Two pointers\n";
echo "  3. Subarray/window → Sliding window\n";
echo "  4. Optimization range → Binary search on answer\n";
echo "  5. Exponential space → Meet in the middle\n";
echo "  6. Unbounded data → Exponential search\n\n";

echo "Advanced Techniques:\n";
echo "  • Binary search doesn't always search arrays\n";
echo "  • Can search answer space, function values\n";
echo "  • Think creatively about search domain\n";
echo "  • Combine techniques for complex problems\n\n";

echo "Interview Problems:\n";
echo "  ✓ Find Peak Element → Ternary/Binary\n";
echo "  ✓ Two Sum → Two Pointers/Hash\n";
echo "  ✓ Max Sliding Window → Sliding Window + Deque\n";
echo "  ✓ Koko Eating Bananas → Binary Search on Answer\n";
echo "  ✓ Partition Equal Subset Sum → Meet in the Middle\n\n";

echo "Common Patterns:\n";
echo "  1. If monotonic → Binary search variant\n";
echo "  2. If pairs/triplets → Two/Three pointers\n";
echo "  3. If subarray → Sliding window\n";
echo "  4. If optimization → Binary search on answer\n";
echo "  5. Always check: Can I eliminate half?\n";

echo "\n✅ Complete! Advanced search techniques demonstrated.\n";
