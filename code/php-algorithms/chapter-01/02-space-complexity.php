<?php

declare(strict_types=1);

/**
 * Space Complexity Examples
 * 
 * Demonstrates memory usage patterns and space complexity analysis.
 * Run: php 02-space-complexity.php
 * 
 * Chapter 01: Algorithm Complexity & Big O Notation
 * Series: Algorithms for PHP Developers
 */

echo "=== Space Complexity Examples ===\n\n";

// =============================================================================
// O(1) Space - Constant Memory
// =============================================================================

echo "--- O(1) Space - Constant Memory ---\n";

function sumArray(array $numbers): int
{
    $total = 0; // Only one variable - O(1) space
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

function findMaxInPlace(array $numbers): int|float
{
    $max = $numbers[0]; // O(1) space
    foreach ($numbers as $number) {
        if ($number > $max) {
            $max = $number;
        }
    }
    return $max;
}

$numbers = [3, 7, 2, 9, 1, 5, 8, 4];
echo "Sum (O(1) space): " . sumArray($numbers) . "\n";
echo "Max (O(1) space): " . findMaxInPlace($numbers) . "\n";
echo "Memory: Uses fixed amount of variables regardless of input size\n\n";

// =============================================================================
// O(n) Space - Linear Memory
// =============================================================================

echo "--- O(n) Space - Linear Memory ---\n";

function doubleValues(array $numbers): array
{
    $doubled = []; // New array grows with input - O(n) space
    foreach ($numbers as $number) {
        $doubled[] = $number * 2;
    }
    return $doubled;
}

function copyAndReverse(array $arr): array
{
    $reversed = []; // O(n) space
    for ($i = count($arr) - 1; $i >= 0; $i--) {
        $reversed[] = $arr[$i];
    }
    return $reversed;
}

$original = [1, 2, 3, 4, 5];
$doubled = doubleValues($original);
$reversed = copyAndReverse($original);

echo "Original: " . implode(', ', $original) . "\n";
echo "Doubled (O(n) space): " . implode(', ', $doubled) . "\n";
echo "Reversed (O(n) space): " . implode(', ', $reversed) . "\n";
echo "Memory: Each result array uses n additional slots\n\n";

// =============================================================================
// Recursive Call Stack - O(n) Space
// =============================================================================

echo "--- Recursive Call Stack - O(n) Space ---\n";

function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }
    return $n * factorial($n - 1); // Stack grows with n - O(n) space
}

function factorialIterative(int $n): int
{
    $result = 1; // O(1) space
    for ($i = 2; $i <= $n; $i++) {
        $result *= $i;
    }
    return $result;
}

echo "Factorial of 5 (recursive, O(n) space): " . factorial(5) . "\n";
echo "Factorial of 5 (iterative, O(1) space): " . factorialIterative(5) . "\n";
echo "Recursive version uses O(n) call stack memory\n";
echo "Iterative version uses O(1) memory\n\n";

// =============================================================================
// Comparing In-Place vs Copy Algorithms
// =============================================================================

echo "--- In-Place vs Copy Algorithms ---\n";

// In-place modification - O(1) space
function reverseInPlace(array &$arr): void
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        [$arr[$left], $arr[$right]] = [$arr[$right], $arr[$left]];
        $left++;
        $right--;
    }
}

// Copy approach - O(n) space
function reverseCopy(array $arr): array
{
    return array_reverse($arr);
}

$data1 = [1, 2, 3, 4, 5, 6];
$data2 = [1, 2, 3, 4, 5, 6];

echo "Before: " . implode(', ', $data1) . "\n";

reverseInPlace($data1);
echo "After in-place (O(1) space): " . implode(', ', $data1) . "\n";

$data2Copy = reverseCopy($data2);
echo "After copy (O(n) space): " . implode(', ', $data2Copy) . "\n";
echo "Original unchanged: " . implode(', ', $data2) . "\n\n";

// =============================================================================
// Space Complexity with Data Structures
// =============================================================================

echo "--- Space Complexity with Data Structures ---\n";

// O(n) space - storing unique elements
function removeDuplicates(array $arr): array
{
    $seen = []; // O(n) space in worst case
    $result = [];

    foreach ($arr as $value) {
        if (!isset($seen[$value])) {
            $seen[$value] = true;
            $result[] = $value;
        }
    }

    return $result;
}

// O(n²) space - storing all pairs
function generateAllPairs(array $items): array
{
    $pairs = []; // O(n²) space

    for ($i = 0; $i < count($items); $i++) {
        for ($j = $i + 1; $j < count($items); $j++) {
            $pairs[] = [$items[$i], $items[$j]];
        }
    }

    return $pairs;
}

$withDupes = [1, 2, 2, 3, 3, 3, 4, 4, 4, 4];
$unique = removeDuplicates($withDupes);
echo "Remove duplicates (O(n) space): " . implode(', ', $unique) . "\n";

$items = ['A', 'B', 'C', 'D'];
$pairs = generateAllPairs($items);
echo "All pairs (O(n²) space): " . count($pairs) . " pairs from " . count($items) . " items\n";
echo "Pairs: ";
foreach ($pairs as $pair) {
    echo "[{$pair[0]}, {$pair[1]}] ";
}
echo "\n\n";

// =============================================================================
// Memory Usage Visualization
// =============================================================================

echo "--- Memory Usage Visualization ---\n";

function measureMemoryUsage(callable $fn, ...$args): array
{
    $memBefore = memory_get_usage();
    $result = $fn(...$args);
    $memAfter = memory_get_usage();
    $memUsed = $memAfter - $memBefore;

    return [
        'result' => $result,
        'memory_bytes' => $memUsed,
        'memory_kb' => round($memUsed / 1024, 2),
    ];
}

// Test with different sized inputs
$sizes = [100, 1000, 10000];

echo "Testing memory usage for doubleValues() function:\n";
foreach ($sizes as $size) {
    $testData = range(1, $size);
    $usage = measureMemoryUsage('doubleValues', $testData);
    echo "  n=$size: {$usage['memory_kb']} KB\n";
}

echo "\nNotice: Memory usage grows linearly with input size (O(n) space)\n\n";

// =============================================================================
// Generator for Space Optimization
// =============================================================================

echo "--- Generator for Space Optimization ---\n";

// Memory-intensive: O(n) space
function rangeArray(int $start, int $end): array
{
    $result = [];
    for ($i = $start; $i <= $end; $i++) {
        $result[] = $i;
    }
    return $result;
}

// Memory-efficient: O(1) space
function rangeGenerator(int $start, int $end): Generator
{
    for ($i = $start; $i <= $end; $i++) {
        yield $i;
    }
}

// Memory comparison
$n = 10000;

$memBefore = memory_get_usage();
$arr = rangeArray(1, $n);
$arrayMemory = memory_get_usage() - $memBefore;

$memBefore = memory_get_usage();
$gen = rangeGenerator(1, $n);
// Generator doesn't allocate until iterated
$count = 0;
foreach ($gen as $num) {
    $count++;
    if ($count >= $n) break;
}
$genMemory = memory_get_usage() - $memBefore;

echo "Generating 1 to $n:\n";
echo "Array approach (O(n) space): " . round($arrayMemory / 1024, 2) . " KB\n";
echo "Generator approach (O(1) space): " . round($genMemory / 1024, 2) . " KB\n";
echo "Memory saved: " . round(($arrayMemory - $genMemory) / 1024, 2) . " KB\n\n";

// =============================================================================
// Memoization: Trading Space for Time
// =============================================================================

echo "--- Memoization: Trading Space for Time ---\n";

// Without memoization - O(2ⁿ) time, O(n) space (call stack)
function fibSlow(int $n): int
{
    if ($n <= 1) return $n;
    return fibSlow($n - 1) + fibSlow($n - 2);
}

// With memoization - O(n) time, O(n) space (memo array)
function fibFast(int $n, array &$memo = []): int
{
    if ($n <= 1) return $n;
    if (isset($memo[$n])) return $memo[$n];

    $memo[$n] = fibFast($n - 1, $memo) + fibFast($n - 2, $memo);
    return $memo[$n];
}

$testN = 30;

$start = microtime(true);
$resultSlow = fibSlow($testN);
$slowTime = microtime(true) - $start;

$memo = [];
$start = microtime(true);
$resultFast = fibFast($testN, $memo);
$fastTime = microtime(true) - $start;

echo "Fibonacci($testN):\n";
echo "Without memoization: $resultSlow (" . number_format($slowTime * 1000, 2) . " ms)\n";
echo "With memoization: $resultFast (" . number_format($fastTime * 1000, 2) . " ms)\n";
echo "Speedup: " . number_format($slowTime / $fastTime, 0) . "x faster\n";
echo "Trade-off: Used " . count($memo) . " memo slots (O(n) space) for massive time savings\n\n";

// =============================================================================
// Summary
// =============================================================================

echo "=== Summary ===\n";
echo "✓ O(1) space - Fixed memory, doesn't grow with input\n";
echo "✓ O(n) space - Memory grows linearly with input size\n";
echo "✓ O(n²) space - Memory grows quadratically (avoid when possible)\n";
echo "✓ Call stack - Recursive functions use O(n) stack space\n";
echo "✓ Generators - Process large datasets with O(1) memory\n";
echo "✓ Memoization - Trade space for time (often worth it)\n\n";

echo "Key takeaway: Space complexity matters for large datasets!\n";
echo "Consider memory constraints when choosing algorithms.\n";
