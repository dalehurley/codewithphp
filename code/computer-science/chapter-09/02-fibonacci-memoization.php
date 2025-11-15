<?php

declare(strict_types=1);

/**
 * Fibonacci and Memoization
 *
 * Demonstrates the critical importance of memoization in recursion
 * Shows how naive recursion can be O(2^n) and memoization makes it O(n)
 */

echo "=== Fibonacci and Memoization ===\n\n";

// Naive recursive fibonacci - VERY SLOW!
function fibonacciNaive(int $n): int
{
    if ($n <= 1) {
        return $n;
    }
    
    return fibonacciNaive($n - 1) + fibonacciNaive($n - 2);
}

// Fibonacci with memoization - FAST!
function fibonacciMemo(int $n, array &$memo = []): int
{
    if ($n <= 1) {
        return $n;
    }
    
    if (isset($memo[$n])) {
        return $memo[$n];
    }
    
    $memo[$n] = fibonacciMemo($n - 1, $memo) + fibonacciMemo($n - 2, $memo);
    return $memo[$n];
}

// Test 1: Show exponential growth
echo "1. Naive Recursion - Exponential Time O(2^n)\n";
echo "=============================================\n\n";

echo "Fibonacci sequence: 0, 1, 1, 2, 3, 5, 8, 13, 21, 34...\n\n";

for ($i = 0; $i <= 10; $i++) {
    $result = fibonacciNaive($i);
    echo "fib($i) = $result\n";
}
echo "\n";

// Test 2: Performance comparison
echo "2. Performance Comparison\n";
echo "==========================\n\n";

echo "Computing fibonacci(30)...\n\n";

$start = microtime(true);
$result = fibonacciNaive(30);
$naiveTime = (microtime(true) - $start) * 1000;

echo "Naive recursion:\n";
echo "  Result: $result\n";
echo "  Time: " . number_format($naiveTime, 2) . " ms\n\n";

$start = microtime(true);
$result = fibonacciMemo(30);
$memoTime = (microtime(true) - $start) * 1000;

echo "With memoization:\n";
echo "  Result: $result\n";
echo "  Time: " . number_format($memoTime, 4) . " ms\n\n";

$speedup = $naiveTime / $memoTime;
echo "Speedup: " . number_format($speedup, 0) . "x faster!\n\n";

// Test 3: Large fibonacci numbers
echo "3. Computing Large Fibonacci Numbers\n";
echo "======================================\n\n";

echo "Naive recursion can't compute fib(50) in reasonable time!\n";
echo "With memoization:\n\n";

for ($i = 40; $i <= 50; $i += 2) {
    $result = fibonacciMemo($i);
    echo "fib($i) = $result\n";
}

echo "\n✅ Complete! Memoization demonstrated.\n";
