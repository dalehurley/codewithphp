<?php

declare(strict_types=1);

/**
 * Algorithm Complexity Examples
 * 
 * Demonstrates all major time complexity classes with working PHP code.
 * Run: php 01-complexity-examples.php
 * 
 * Chapter 01: Algorithm Complexity & Big O Notation
 * Series: Algorithms for PHP Developers
 */

echo "=== Algorithm Complexity Examples ===\n\n";

// =============================================================================
// O(1) - Constant Time
// =============================================================================

echo "--- O(1) - Constant Time ---\n";

function getFirstElement(array $arr): mixed
{
    return $arr[0];
}

function getUserById(array $users, int $id): ?array
{
    return $users[$id] ?? null;
}

function calculateDiscount(float $price): float
{
    return $price * 0.1;
}

$numbers = [10, 20, 30, 40, 50];
echo "First element: " . getFirstElement($numbers) . "\n";

$users = [
    1 => ['name' => 'Alice', 'email' => 'alice@example.com'],
    2 => ['name' => 'Bob', 'email' => 'bob@example.com'],
];
$user = getUserById($users, 1);
echo "User by ID: {$user['name']}\n";

echo "Discount on $100: $" . calculateDiscount(100.0) . "\n\n";

// =============================================================================
// O(log n) - Logarithmic Time
// =============================================================================

echo "--- O(log n) - Logarithmic Time ---\n";

function binarySearch(array $sorted, int $target): int|false
{
    $left = 0;
    $right = count($sorted) - 1;
    $steps = 0;

    while ($left <= $right) {
        $steps++;
        $mid = (int)(($left + $right) / 2);

        if ($sorted[$mid] === $target) {
            echo "Found $target in $steps steps\n";
            return $mid;
        } elseif ($sorted[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    echo "Not found after $steps steps\n";
    return false;
}

$sortedNumbers = range(1, 1000);
$index = binarySearch($sortedNumbers, 742);
echo "Index of 742: " . ($index !== false ? $index : 'not found') . "\n\n";

// =============================================================================
// O(n) - Linear Time
// =============================================================================

echo "--- O(n) - Linear Time ---\n";

function sum(array $numbers): int|float
{
    $total = 0;
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

function findMax(array $numbers): int|float
{
    $max = $numbers[0];
    foreach ($numbers as $number) {
        if ($number > $max) {
            $max = $number;
        }
    }
    return $max;
}

function filterEven(array $numbers): array
{
    $result = [];
    foreach ($numbers as $number) {
        if ($number % 2 === 0) {
            $result[] = $number;
        }
    }
    return $result;
}

$testNumbers = [3, 7, 2, 9, 1, 5, 8, 4];
echo "Sum: " . sum($testNumbers) . "\n";
echo "Max: " . findMax($testNumbers) . "\n";
echo "Even numbers: " . implode(', ', filterEven($testNumbers)) . "\n\n";

// =============================================================================
// O(n log n) - Linearithmic Time
// =============================================================================

echo "--- O(n log n) - Linearithmic Time ---\n";

function mergeSort(array $arr): array
{
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

function merge(array $left, array $right): array
{
    $result = [];
    $i = $j = 0;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }

    return array_merge($result, array_slice($left, $i), array_slice($right, $j));
}

$unsorted = [64, 34, 25, 12, 22, 11, 90];
$sorted = mergeSort($unsorted);
echo "Merge sort result: " . implode(', ', $sorted) . "\n\n";

// =============================================================================
// O(n²) - Quadratic Time
// =============================================================================

echo "--- O(n²) - Quadratic Time ---\n";

function bubbleSort(array $arr): array
{
    $n = count($arr);
    $swaps = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swaps++;
            }
        }
    }

    echo "Bubble sort swaps: $swaps\n";
    return $arr;
}

function findAllPairs(array $items): array
{
    $pairs = [];

    for ($i = 0; $i < count($items); $i++) {
        for ($j = $i + 1; $j < count($items); $j++) {
            $pairs[] = [$items[$i], $items[$j]];
        }
    }

    return $pairs;
}

$toSort = [5, 2, 8, 1, 9];
$sorted = bubbleSort($toSort);
echo "Bubble sort result: " . implode(', ', $sorted) . "\n";

$items = ['A', 'B', 'C', 'D'];
$pairs = findAllPairs($items);
echo "Pairs from 4 items: " . count($pairs) . " pairs\n";
echo "First 3 pairs: ";
for ($i = 0; $i < min(3, count($pairs)); $i++) {
    echo "[{$pairs[$i][0]}, {$pairs[$i][1]}] ";
}
echo "\n\n";

// =============================================================================
// O(2ⁿ) - Exponential Time
// =============================================================================

echo "--- O(2ⁿ) - Exponential Time (WARNING: SLOW!) ---\n";

$fibCalls = 0;

function fibonacci(int $n): int
{
    global $fibCalls;
    $fibCalls++;

    if ($n <= 1) {
        return $n;
    }

    return fibonacci($n - 1) + fibonacci($n - 2);
}

echo "Computing fibonacci(10)...\n";
$fibCalls = 0;
$result = fibonacci(10);
echo "Result: $result (function calls: $fibCalls)\n";

echo "\nComputing fibonacci(20)...\n";
$fibCalls = 0;
$result = fibonacci(20);
echo "Result: $result (function calls: $fibCalls)\n";

echo "\n⚠️  Notice: fibonacci(20) requires " . number_format($fibCalls) . " function calls!\n";
echo "fibonacci(40) would take millions of calls and several seconds.\n\n";

// =============================================================================
// Practical Comparison: Hash Lookup vs Linear Search
// =============================================================================

echo "--- Practical Comparison: O(1) vs O(n) ---\n";

// Linear search - O(n)
function findUserLinear(array $users, string $email): ?array
{
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

// Hash lookup - O(1)
function findUserHash(array $usersByEmail, string $email): ?array
{
    return $usersByEmail[$email] ?? null;
}

// Create test data
$usersArray = [];
$usersHash = [];
for ($i = 1; $i <= 1000; $i++) {
    $user = ['id' => $i, 'name' => "User$i", 'email' => "user$i@example.com"];
    $usersArray[] = $user;
    $usersHash["user$i@example.com"] = $user;
}

$targetEmail = 'user750@example.com';

// Benchmark linear search
$start = microtime(true);
$result = findUserLinear($usersArray, $targetEmail);
$linearTime = microtime(true) - $start;

// Benchmark hash lookup
$start = microtime(true);
$result = findUserHash($usersHash, $targetEmail);
$hashTime = microtime(true) - $start;

echo "Searching for '$targetEmail' in 1,000 users:\n";
echo "Linear search (O(n)): " . number_format($linearTime * 1000000, 2) . " μs\n";
echo "Hash lookup (O(1)): " . number_format($hashTime * 1000000, 2) . " μs\n";
echo "Speedup: " . number_format($linearTime / $hashTime, 1) . "x faster\n\n";

// =============================================================================
// Optimizing O(n²) to O(n) using Hash Sets
// =============================================================================

echo "--- Optimizing O(n²) to O(n) with Hash Sets ---\n";

// O(n²) - Nested loops
function findCommonFriends_Slow(array $friends1, array $friends2): array
{
    $common = [];
    foreach ($friends1 as $friend1) {
        foreach ($friends2 as $friend2) {
            if ($friend1 === $friend2) {
                $common[] = $friend1;
            }
        }
    }
    return $common;
}

// O(n) - Using hash set
function findCommonFriends_Fast(array $friends1, array $friends2): array
{
    $friendSet = array_flip($friends1);
    $common = [];
    foreach ($friends2 as $friend) {
        if (isset($friendSet[$friend])) {
            $common[] = $friend;
        }
    }
    return $common;
}

$friends1 = ['Alice', 'Bob', 'Charlie', 'David', 'Eve'];
$friends2 = ['Bob', 'Frank', 'Charlie', 'Grace', 'David'];

$start = microtime(true);
$commonSlow = findCommonFriends_Slow($friends1, $friends2);
$slowTime = microtime(true) - $start;

$start = microtime(true);
$commonFast = findCommonFriends_Fast($friends1, $friends2);
$fastTime = microtime(true) - $start;

echo "Common friends: " . implode(', ', $commonFast) . "\n";
echo "O(n²) approach: " . number_format($slowTime * 1000000, 2) . " μs\n";
echo "O(n) approach: " . number_format($fastTime * 1000000, 2) . " μs\n";
echo "Speedup: " . number_format($slowTime / $fastTime, 1) . "x faster\n\n";

// =============================================================================
// Summary
// =============================================================================

echo "=== Summary ===\n";
echo "✓ O(1) - Constant time: Same speed regardless of input size\n";
echo "✓ O(log n) - Logarithmic: Doubles input → adds one step\n";
echo "✓ O(n) - Linear: Doubles input → doubles time\n";
echo "✓ O(n log n) - Linearithmic: Efficient sorting algorithms\n";
echo "✓ O(n²) - Quadratic: Gets slow quickly, avoid for large data\n";
echo "✓ O(2ⁿ) - Exponential: Only usable for tiny inputs\n\n";

echo "Key takeaway: Choose the right algorithm for your data size!\n";
