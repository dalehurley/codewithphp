---
title: "25: Dynamic Programming Fundamentals"
description: "Master dynamic programming concepts including overlapping subproblems, optimal substructure, memoization, and tabulation with classic problems like Fibonacci, knapsack, and longest common subsequence"
series: "php-algorithms"
chapter: 25
order: 25
difficulty: "Advanced"
prerequisites:
  - "/series/php-algorithms/chapters/03-recursion-fundamentals"
  - "/series/php-algorithms/chapters/15-arrays-dynamic-arrays"
estimatedTime: "PT45M"
teaches:
  - 'Understand when and why dynamic programming is the right tool for the job'
  - 'Master both fundamental approaches: top-down memoization and bottom-up tabulation'
  - 'Recognize and solve classic DP problems like Fibonacci, coin change, and knapsack'
  - 'Apply DP to eliminate redundant calculations and optimize recursive solutions'
  - 'Build real-world applications for inventory optimization and resource allocation'
---
![Dynamic Programming Fundamentals](/images/php-algorithms/chapter-25-dynamic-programming-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 25</span>
</div>

# 25: Dynamic Programming Fundamentals <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Dynamic Programming is one of the most powerful problem-solving techniques in computer science, transforming exponential-time solutions into polynomial or even linear-time algorithms. While it might seem intimidating at first, DP follows clear patterns that you can master with practice.

This technique solves complex problems by breaking them down into simpler overlapping subproblems and storing their solutions to avoid redundant computation. Think of it as "smart recursion" - we remember what we've already computed so we never waste time calculating the same thing twice.

In this chapter, you'll master both fundamental DP approaches: top-down memoization (adding caching to recursive solutions) and bottom-up tabulation (building solutions iteratively from base cases). You'll solve classic DP problems like Fibonacci, coin change, knapsack, and longest common subsequence, learning to recognize when DP is the right tool and how to optimize your solutions.

By the end of this chapter, you'll understand how DP can transform solutions from O(2ⁿ) exponential time to O(n) or O(n²) polynomial time, making previously intractable problems solvable in milliseconds.

## Prerequisites

Before starting this chapter, you should have:

- ✓ Complete understanding of recursion fundamentals ([Chapter 3](/series/php-algorithms/chapters/03-recursion-fundamentals))
- ✓ Strong knowledge of arrays and dynamic arrays ([Chapter 15](/series/php-algorithms/chapters/15-arrays-dynamic-arrays))
- ✓ Basic understanding of Big O notation and algorithm complexity
- ✓ Experience breaking down problems into smaller subproblems

**Estimated Time**: ~45 minutes

## What You'll Build

By the end of this chapter, you will have created:

- Memoization and tabulation implementations for Fibonacci and other classic problems
- A coin change solver that finds minimum coins and counts combinations
- A subset sum solver that determines if a target sum can be achieved
- Both 0/1 knapsack (bounded) and unbounded knapsack solvers with rod cutting optimization
- Longest common subsequence (LCS) and longest increasing subsequence (LIS) algorithms
- Grid DP solvers for counting paths and finding minimum cost paths
- DP pattern recognition system for identifying when to use dynamic programming
- Space-optimized versions of classic DP problems with understanding of forward vs backward iteration

## Objectives

- Understand when and why dynamic programming is the right tool for the job
- Master both fundamental approaches: top-down memoization and bottom-up tabulation
- Recognize and solve classic DP problems like Fibonacci, coin change, and knapsack
- Apply DP to eliminate redundant calculations and optimize recursive solutions
- Build real-world applications for inventory optimization and resource allocation
- Identify overlapping subproblems and optimal substructure in new problems

## Core Concepts

Dynamic Programming relies on two fundamental properties that determine whether a problem can be solved efficiently using DP. Understanding these concepts is crucial for recognizing when DP is the right approach.

### 1. Overlapping Subproblems

A problem has overlapping subproblems if the same subproblems are solved multiple times.

```php
# filename: fibonacci-naive.php
<?php

declare(strict_types=1);

// Example: Fibonacci without DP (exponential time)
function fibonacciNaive(int $n): int
{
    if ($n <= 1) {
        return $n;
    }
    return fibonacciNaive($n - 1) + fibonacciNaive($n - 2);
}

// fib(5) calls fib(3) twice, fib(2) three times
// Overlapping subproblems!
```

### 2. Optimal Substructure

A problem has optimal substructure if an optimal solution can be constructed from optimal solutions of its subproblems.

```php
# filename: optimal-substructure-example.php
<?php

declare(strict_types=1);

// Example: Shortest path has optimal substructure
// If A→B→C is shortest path from A to C,
// then A→B must be shortest path from A to B
```

## Memoization (Top-Down DP)

Store results of expensive function calls and return cached result when same inputs occur.

### Fibonacci with Memoization

```php
# filename: fibonacci-memoization.php
<?php

declare(strict_types=1);

class FibonacciMemoization
{
    private array $memo = [];

    public function fib(int $n): int
    {
        // Base cases
        if ($n <= 1) {
            return $n;
        }

        // Check if already computed
        if (isset($this->memo[$n])) {
            return $this->memo[$n];
        }

        // Compute and store
        $this->memo[$n] = $this->fib($n - 1) + $this->fib($n - 2);
        return $this->memo[$n];
    }

    public function getMemo(): array
    {
        return $this->memo;
    }
}

// Example usage
$fib = new FibonacciMemoization();
echo $fib->fib(10) . "\n";  // 55
print_r($fib->getMemo());
// [2 => 1, 3 => 2, 4 => 3, 5 => 5, 6 => 8, 7 => 13, 8 => 21, 9 => 34, 10 => 55]

// Time: O(n), Space: O(n)
```

## DP State Transition Visualization

Understanding how dynamic programming builds solutions:

```php
# filename: dp-state-visualizer.php
<?php

declare(strict_types=1);

class DPStateVisualizer
{
    // Visualize Fibonacci DP state transitions
    public function visualizeFibonacci(int $n): void
    {
        echo "=== Fibonacci DP State Transitions ===\n\n";
        echo "Computing fib($n)\n\n";

        // Show naive recursive approach
        echo "Without DP (Exponential - many repeated calculations):\n";
        echo "fib($n)\n";
        if ($n >= 2) {
            echo "├─ fib(" . ($n-1) . ")\n";
            echo "│  ├─ fib(" . ($n-2) . ") [REPEATED]\n";
            echo "│  └─ fib(" . ($n-3) . ")\n";
            echo "└─ fib(" . ($n-2) . ") [REPEATED]\n";
            echo "   ├─ fib(" . ($n-3) . ") [REPEATED]\n";
            echo "   └─ fib(" . ($n-4) . ")\n";
        }
        echo "\nWith DP (Linear - each value computed once):\n";

        $dp = [];
        for ($i = 0; $i <= $n; $i++) {
            if ($i <= 1) {
                $dp[$i] = $i;
                echo "dp[$i] = $i (base case)\n";
            } else {
                $dp[$i] = $dp[$i-1] + $dp[$i-2];
                echo "dp[$i] = dp[" . ($i-1) . "] + dp[" . ($i-2) . "] = {$dp[$i-1]} + {$dp[$i-2]} = {$dp[$i]}\n";
            }
        }

        echo "\nFinal answer: fib($n) = {$dp[$n]}\n";
        echo "DP table: [" . implode(', ', $dp) . "]\n";
    }

    // Visualize coin change DP table construction
    public function visualizeCoinChange(array $coins, int $amount): void
    {
        echo "\n=== Coin Change DP Table Construction ===\n\n";
        echo "Coins: [" . implode(', ', $coins) . "], Amount: $amount\n\n";

        $dp = array_fill(0, $amount + 1, PHP_INT_MAX);
        $dp[0] = 0;

        echo "Initial: dp[0] = 0 (0 coins needed for amount 0)\n";
        echo "All others: ∞ (impossible initially)\n\n";

        for ($i = 1; $i <= $amount; $i++) {
            echo "Computing dp[$i] (minimum coins for amount $i):\n";

            foreach ($coins as $coin) {
                if ($coin <= $i && $dp[$i - $coin] !== PHP_INT_MAX) {
                    $newValue = $dp[$i - $coin] + 1;
                    echo "  Try coin $coin: dp[$i - $coin] + 1 = {$dp[$i - $coin]} + 1 = $newValue\n";

                    if ($newValue < $dp[$i]) {
                        $dp[$i] = $newValue;
                        echo "    → Update dp[$i] = $newValue\n";
                    }
                }
            }

            $result = $dp[$i] === PHP_INT_MAX ? '∞' : $dp[$i];
            echo "  Final: dp[$i] = $result\n\n";
        }

        echo "Complete DP table:\n";
        echo "Amount: ";
        for ($i = 0; $i <= $amount; $i++) {
            printf("%4d", $i);
        }
        echo "\nCoins:  ";
        for ($i = 0; $i <= $amount; $i++) {
            $val = $dp[$i] === PHP_INT_MAX ? '∞' : $dp[$i];
            printf("%4s", $val);
        }
        echo "\n";
    }

    // Visualize LCS DP table
    public function visualizeLCS(string $text1, string $text2): void
    {
        echo "\n=== Longest Common Subsequence DP Table ===\n\n";
        echo "Text1: \"$text1\"\n";
        echo "Text2: \"$text2\"\n\n";

        $m = strlen($text1);
        $n = strlen($text2);
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        // Build DP table
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($text1[$i - 1] === $text2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Print DP table
        echo "DP Table (dp[i][j] = LCS length of text1[0..i-1] and text2[0..j-1]):\n\n";
        echo "      ";
        for ($j = 0; $j < $n; $j++) {
            echo "  " . $text2[$j];
        }
        echo "\n";

        for ($i = 0; $i <= $m; $i++) {
            if ($i === 0) {
                echo "  ";
            } else {
                echo $text1[$i - 1] . " ";
            }

            for ($j = 0; $j <= $n; $j++) {
                printf("%3d", $dp[$i][$j]);
            }
            echo "\n";
        }

        echo "\nLCS Length: {$dp[$m][$n]}\n";

        // Show how to trace back
        echo "\nTraceback (how we got the answer):\n";
        $i = $m;
        $j = $n;
        $lcs = '';

        while ($i > 0 && $j > 0) {
            if ($text1[$i - 1] === $text2[$j - 1]) {
                $lcs = $text1[$i - 1] . $lcs;
                echo "  Match '{$text1[$i-1]}' at ($i,$j) → move diagonal\n";
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                echo "  At ($i,$j): up ({$dp[$i-1][$j]}) > left ({$dp[$i][$j-1]}) → move up\n";
                $i--;
            } else {
                echo "  At ($i,$j): left ({$dp[$i][$j-1]}) >= up ({$dp[$i-1][$j]}) → move left\n";
                $j--;
            }
        }

        echo "\nLCS: \"$lcs\"\n";
    }
}

// Example visualizations
$visualizer = new DPStateVisualizer();

$visualizer->visualizeFibonacci(7);
/*
=== Fibonacci DP State Transitions ===

Computing fib(7)

Without DP (Exponential - many repeated calculations):
fib(7)
├─ fib(6)
│  ├─ fib(5) [REPEATED]
│  └─ fib(4)
└─ fib(5) [REPEATED]
   ├─ fib(4) [REPEATED]
   └─ fib(3)

With DP (Linear - each value computed once):
dp[0] = 0 (base case)
dp[1] = 1 (base case)
dp[2] = dp[1] + dp[0] = 1 + 0 = 1
dp[3] = dp[2] + dp[1] = 1 + 1 = 2
dp[4] = dp[3] + dp[2] = 2 + 1 = 3
dp[5] = dp[4] + dp[3] = 3 + 2 = 5
dp[6] = dp[5] + dp[4] = 5 + 3 = 8
dp[7] = dp[6] + dp[5] = 8 + 5 = 13

Final answer: fib(7) = 13
DP table: [0, 1, 1, 2, 3, 5, 8, 13]
*/

$visualizer->visualizeCoinChange([1, 5, 10], 12);

$visualizer->visualizeLCS("AGGTAB", "GXTXAYB");
```

## Memoization vs Tabulation Detailed Comparison

Understanding the tradeoffs between top-down and bottom-up DP:

```php
# filename: memoization-vs-tabulation.php
<?php

declare(strict_types=1);

class MemoizationVsTabulation
{
    // Memoization (Top-Down) - with call tracking
    public function fibMemo(int $n, array &$callCount = []): int
    {
        static $memo = [];
        static $calls = 0;

        $calls++;
        $callCount['total'] = ($callCount['total'] ?? 0) + 1;

        if ($n <= 1) {
            return $n;
        }

        if (!isset($memo[$n])) {
            $callCount['computed'] = ($callCount['computed'] ?? 0) + 1;
            $memo[$n] = $this->fibMemo($n - 1, $callCount) + $this->fibMemo($n - 2, $callCount);
        } else {
            $callCount['cached'] = ($callCount['cached'] ?? 0) + 1;
        }

        return $memo[$n];
    }

    // Tabulation (Bottom-Up)
    public function fibTab(int $n, array &$stats = []): int
    {
        if ($n <= 1) {
            return $n;
        }

        $dp = [0, 1];
        $stats['iterations'] = 0;

        for ($i = 2; $i <= $n; $i++) {
            $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
            $stats['iterations']++;
        }

        $stats['space_used'] = count($dp);

        return $dp[$n];
    }

    // Comparison
    public function compare(int $n): void
    {
        echo "=== Memoization vs Tabulation Comparison (n=$n) ===\n\n";

        // Memoization
        $memoCallCount = [];
        $memoStart = microtime(true);
        $memoResult = $this->fibMemo($n, $memoCallCount);
        $memoTime = microtime(true) - $memoStart;

        echo "MEMOIZATION (Top-Down):\n";
        echo "  Result: $memoResult\n";
        echo "  Time: " . round($memoTime * 1000, 4) . " ms\n";
        echo "  Function calls: {$memoCallCount['total']}\n";
        echo "  Computed: " . ($memoCallCount['computed'] ?? 0) . "\n";
        echo "  From cache: " . ($memoCallCount['cached'] ?? 0) . "\n";
        echo "  Approach: Recursive (uses call stack)\n";
        echo "  When to use: When you don't need all subproblems\n\n";

        // Tabulation
        $tabStats = [];
        $tabStart = microtime(true);
        $tabResult = $this->fibTab($n, $tabStats);
        $tabTime = microtime(true) - $tabStart;

        echo "TABULATION (Bottom-Up):\n";
        echo "  Result: $tabResult\n";
        echo "  Time: " . round($tabTime * 1000, 4) . " ms\n";
        echo "  Iterations: {$tabStats['iterations']}\n";
        echo "  Array size: {$tabStats['space_used']} elements\n";
        echo "  Approach: Iterative (no recursion)\n";
        echo "  When to use: When you need all subproblems\n\n";

        echo "COMPARISON:\n";
        echo "  Speed: Tabulation is typically faster (no function call overhead)\n";
        echo "  Space: Memoization may use less (only computes needed values)\n";
        echo "  Clarity: Memoization often more intuitive (follows recursive logic)\n";
        echo "  Stack safety: Tabulation avoids stack overflow for large n\n";
    }

    // Show execution patterns
    public function visualizeExecutionPatterns(): void
    {
        echo "\n=== Execution Pattern Visualization ===\n\n";

        echo "MEMOIZATION (Fibonacci n=5):\n";
        echo "Call tree:\n";
        echo "fib(5)\n";
        echo "├─ fib(4)       [compute]\n";
        echo "│  ├─ fib(3)    [compute]\n";
        echo "│  │  ├─ fib(2) [compute]\n";
        echo "│  │  │  ├─ fib(1) = 1\n";
        echo "│  │  │  └─ fib(0) = 0\n";
        echo "│  │  └─ fib(1) = 1\n";
        echo "│  └─ fib(2)    [from cache!]\n";
        echo "└─ fib(3)       [from cache!]\n\n";

        echo "TABULATION (Fibonacci n=5):\n";
        echo "Builds table iteratively:\n";
        echo "dp[0] = 0\n";
        echo "dp[1] = 1\n";
        echo "dp[2] = dp[1] + dp[0] = 1\n";
        echo "dp[3] = dp[2] + dp[1] = 2\n";
        echo "dp[4] = dp[3] + dp[2] = 3\n";
        echo "dp[5] = dp[4] + dp[3] = 5\n";
        echo "Direct access to result: dp[5] = 5\n\n";

        echo "KEY DIFFERENCES:\n";
        echo "1. Direction:\n";
        echo "   Memoization: Top-down (starts from problem, breaks down)\n";
        echo "   Tabulation:  Bottom-up (starts from base cases, builds up)\n\n";

        echo "2. Order of computation:\n";
        echo "   Memoization: Computes as needed (lazy evaluation)\n";
        echo "   Tabulation:  Computes all states in order (eager evaluation)\n\n";

        echo "3. Implementation style:\n";
        echo "   Memoization: Recursive + cache (natural for many problems)\n";
        echo "   Tabulation:  Iterative loops (better performance)\n\n";
    }
}

$comparator = new MemoizationVsTabulation();
$comparator->compare(30);
$comparator->visualizeExecutionPatterns();
```

### Generic Memoization Wrapper

```php
# filename: generic-memoizer.php
<?php

declare(strict_types=1);

class Memoizer
{
    private array $cache = [];

    public function memoize(callable $fn): callable
    {
        return function (...$args) use ($fn) {
            $key = serialize($args);

            if (!isset($this->cache[$key])) {
                $this->cache[$key] = $fn(...$args);
            }

            return $this->cache[$key];
        };
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }

    public function getCacheSize(): int
    {
        return count($this->cache);
    }
}

// Example usage
$memoizer = new Memoizer();

$factorial = function(int $n) use (&$factorial, $memoizer) {
    if ($n <= 1) return 1;
    $memoizedFactorial = $memoizer->memoize($factorial);
    return $n * $memoizedFactorial($n - 1);
};

echo $factorial(5) . "\n";  // 120
```

## Tabulation (Bottom-Up DP)

Build solution iteratively from smallest subproblems to larger ones, filling a table.

### Fibonacci with Tabulation

```php
# filename: fibonacci-tabulation.php
<?php

declare(strict_types=1);

class FibonacciTabulation
{
    public function fib(int $n): int
    {
        if ($n <= 1) {
            return $n;
        }

        // Create table to store results
        $dp = array_fill(0, $n + 1, 0);
        $dp[0] = 0;
        $dp[1] = 1;

        // Fill table bottom-up
        for ($i = 2; $i <= $n; $i++) {
            $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
        }

        return $dp[$n];
    }

    // Space-optimized version (only keep last 2 values)
    public function fibOptimized(int $n): int
    {
        if ($n <= 1) {
            return $n;
        }

        $prev2 = 0;
        $prev1 = 1;

        for ($i = 2; $i <= $n; $i++) {
            $current = $prev1 + $prev2;
            $prev2 = $prev1;
            $prev1 = $current;
        }

        return $prev1;
    }
}

// Example
$fib = new FibonacciTabulation();
echo $fib->fib(10) . "\n";           // 55
echo $fib->fibOptimized(10) . "\n";  // 55

// Time: O(n), Space: O(n) or O(1) optimized
```

## Classic DP Problems

### 1. Climbing Stairs

You can climb 1 or 2 steps at a time. How many ways to reach the top?

```php
# filename: climbing-stairs.php
<?php

declare(strict_types=1);

class ClimbingStairs
{
    // Memoization approach
    private array $memo = [];

    public function climbStairsMemo(int $n): int
    {
        if ($n <= 2) {
            return $n;
        }

        if (isset($this->memo[$n])) {
            return $this->memo[$n];
        }

        $this->memo[$n] = $this->climbStairsMemo($n - 1) + $this->climbStairsMemo($n - 2);
        return $this->memo[$n];
    }

    // Tabulation approach
    public function climbStairsTab(int $n): int
    {
        if ($n <= 2) {
            return $n;
        }

        $dp = array_fill(0, $n + 1, 0);
        $dp[1] = 1;
        $dp[2] = 2;

        for ($i = 3; $i <= $n; $i++) {
            $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
        }

        return $dp[$n];
    }

    // Space-optimized
    public function climbStairsOptimized(int $n): int
    {
        if ($n <= 2) {
            return $n;
        }

        $prev2 = 1;
        $prev1 = 2;

        for ($i = 3; $i <= $n; $i++) {
            $current = $prev1 + $prev2;
            $prev2 = $prev1;
            $prev1 = $current;
        }

        return $prev1;
    }
}

// Example
$stairs = new ClimbingStairs();
echo $stairs->climbStairsTab(5) . "\n";  // 8 ways
// 1+1+1+1+1, 1+1+1+2, 1+1+2+1, 1+2+1+1, 2+1+1+1, 1+2+2, 2+1+2, 2+2+1
```

### 2. Coin Change Problem

Find minimum coins needed to make a given amount.

```php
# filename: coin-change.php
<?php

declare(strict_types=1);

class CoinChange
{
    // Minimum coins to make amount
    public function minCoins(array $coins, int $amount): int
    {
        $dp = array_fill(0, $amount + 1, PHP_INT_MAX);
        $dp[0] = 0;  // 0 coins needed for amount 0

        for ($i = 1; $i <= $amount; $i++) {
            foreach ($coins as $coin) {
                if ($coin <= $i && $dp[$i - $coin] !== PHP_INT_MAX) {
                    $dp[$i] = min($dp[$i], $dp[$i - $coin] + 1);
                }
            }
        }

        return $dp[$amount] === PHP_INT_MAX ? -1 : $dp[$amount];
    }

    // Number of ways to make amount
    public function coinCombinations(array $coins, int $amount): int
    {
        $dp = array_fill(0, $amount + 1, 0);
        $dp[0] = 1;  // One way to make 0: use no coins

        foreach ($coins as $coin) {
            for ($i = $coin; $i <= $amount; $i++) {
                $dp[$i] += $dp[$i - $coin];
            }
        }

        return $dp[$amount];
    }

    // Get actual coins used
    public function minCoinsWithPath(array $coins, int $amount): ?array
    {
        $dp = array_fill(0, $amount + 1, PHP_INT_MAX);
        $usedCoin = array_fill(0, $amount + 1, -1);
        $dp[0] = 0;

        for ($i = 1; $i <= $amount; $i++) {
            foreach ($coins as $coin) {
                if ($coin <= $i && $dp[$i - $coin] !== PHP_INT_MAX) {
                    if ($dp[$i - $coin] + 1 < $dp[$i]) {
                        $dp[$i] = $dp[$i - $coin] + 1;
                        $usedCoin[$i] = $coin;
                    }
                }
            }
        }

        if ($dp[$amount] === PHP_INT_MAX) {
            return null;
        }

        // Reconstruct coins used
        $result = [];
        $current = $amount;
        while ($current > 0) {
            $coin = $usedCoin[$current];
            $result[] = $coin;
            $current -= $coin;
        }

        return $result;
    }
}

// Example
$coinChange = new CoinChange();

$coins = [1, 5, 10, 25];
echo "Min coins for 63 cents: " . $coinChange->minCoins($coins, 63) . "\n";  // 6 (25+25+10+1+1+1)
echo "Ways to make 10 cents: " . $coinChange->coinCombinations($coins, 10) . "\n";  // 4

print_r($coinChange->minCoinsWithPath($coins, 63));
// [25, 25, 10, 1, 1, 1]
```

### 3. Subset Sum Problem

Determine if there exists a subset of numbers that sums to a target value.

```php
# filename: subset-sum.php
<?php

declare(strict_types=1);

class SubsetSum
{
    // Check if subset exists that sums to target
    public function subsetExists(array $nums, int $target): bool
    {
        $n = count($nums);

        // dp[i][j] = true if sum j can be achieved using first i elements
        $dp = array_fill(0, $n + 1, array_fill(0, $target + 1, false));

        // Base case: sum 0 can always be achieved (empty subset)
        for ($i = 0; $i <= $n; $i++) {
            $dp[$i][0] = true;
        }

        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $target; $j++) {
                // Don't include current element
                $dp[$i][$j] = $dp[$i - 1][$j];

                // Include current element if it fits
                if ($nums[$i - 1] <= $j) {
                    $dp[$i][$j] = $dp[$i][$j] || $dp[$i - 1][$j - $nums[$i - 1]];
                }
            }
        }

        return $dp[$n][$target];
    }

    // Space-optimized version (1D array)
    public function subsetExistsOptimized(array $nums, int $target): bool
    {
        $dp = array_fill(0, $target + 1, false);
        $dp[0] = true;  // Sum 0 always possible

        foreach ($nums as $num) {
            // Traverse backwards to avoid using same element twice
            for ($j = $target; $j >= $num; $j--) {
                $dp[$j] = $dp[$j] || $dp[$j - $num];
            }
        }

        return $dp[$target];
    }

    // Count number of subsets that sum to target
    public function countSubsets(array $nums, int $target): int
    {
        $dp = array_fill(0, $target + 1, 0);
        $dp[0] = 1;  // One way to achieve sum 0 (empty subset)

        foreach ($nums as $num) {
            for ($j = $target; $j >= $num; $j--) {
                $dp[$j] += $dp[$j - $num];
            }
        }

        return $dp[$target];
    }

    // Get actual subset that sums to target
    public function findSubset(array $nums, int $target): ?array
    {
        $n = count($nums);
        $dp = array_fill(0, $n + 1, array_fill(0, $target + 1, false));

        for ($i = 0; $i <= $n; $i++) {
            $dp[$i][0] = true;
        }

        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $target; $j++) {
                $dp[$i][$j] = $dp[$i - 1][$j];
                if ($nums[$i - 1] <= $j) {
                    $dp[$i][$j] = $dp[$i][$j] || $dp[$i - 1][$j - $nums[$i - 1]];
                }
            }
        }

        if (!$dp[$n][$target]) {
            return null;
        }

        // Reconstruct subset
        $subset = [];
        $j = $target;
        for ($i = $n; $i > 0 && $j > 0; $i--) {
            if (!$dp[$i - 1][$j]) {
                // Element i-1 was included
                $subset[] = $nums[$i - 1];
                $j -= $nums[$i - 1];
            }
        }

        return array_reverse($subset);
    }
}

// Example
$subsetSum = new SubsetSum();

$nums = [3, 34, 4, 12, 5, 2];
echo $subsetSum->subsetExists($nums, 9) ? "true" : "false";  // true (4 + 5)
echo "\n";

echo "Count subsets summing to 9: " . $subsetSum->countSubsets($nums, 9) . "\n";  // 2
print_r($subsetSum->findSubset($nums, 9));  // [4, 5]

// Time: O(n × target), Space: O(target) optimized
```

**Why It Works**: This is a classic boolean DP problem. `dp[i][j]` represents whether sum `j` can be achieved using the first `i` elements. For each element, we have two choices: include it or exclude it. The space-optimized version uses a 1D array and traverses backwards (like knapsack) to ensure each element is used at most once per subset.

### 4. Longest Common Subsequence (LCS)

Find longest subsequence common to two sequences.

```php
# filename: longest-common-subsequence.php
<?php

declare(strict_types=1);

class LongestCommonSubsequence
{
    // Length of LCS
    public function lcsLength(string $text1, string $text2): int
    {
        $m = strlen($text1);
        $n = strlen($text2);

        // dp[i][j] = LCS length of text1[0...i-1] and text2[0...j-1]
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($text1[$i - 1] === $text2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        return $dp[$m][$n];
    }

    // Get actual LCS string
    public function lcs(string $text1, string $text2): string
    {
        $m = strlen($text1);
        $n = strlen($text2);
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        // Fill DP table
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($text1[$i - 1] === $text2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Reconstruct LCS
        $lcs = '';
        $i = $m;
        $j = $n;

        while ($i > 0 && $j > 0) {
            if ($text1[$i - 1] === $text2[$j - 1]) {
                $lcs = $text1[$i - 1] . $lcs;
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return $lcs;
    }

    // Space-optimized (only need 2 rows)
    public function lcsLengthOptimized(string $text1, string $text2): int
    {
        $m = strlen($text1);
        $n = strlen($text2);

        $prev = array_fill(0, $n + 1, 0);
        $curr = array_fill(0, $n + 1, 0);

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($text1[$i - 1] === $text2[$j - 1]) {
                    $curr[$j] = $prev[$j - 1] + 1;
                } else {
                    $curr[$j] = max($prev[$j], $curr[$j - 1]);
                }
            }
            [$prev, $curr] = [$curr, $prev];
        }

        return $prev[$n];
    }
}

// Example
$lcs = new LongestCommonSubsequence();
echo $lcs->lcsLength('ABCDGH', 'AEDFHR') . "\n";  // 3 (ADH)
echo $lcs->lcs('ABCDGH', 'AEDFHR') . "\n";        // ADH
echo $lcs->lcs('AGGTAB', 'GXTXAYB') . "\n";       // GTAB
```

### 5. 0/1 Knapsack Problem

Maximize value of items in knapsack with weight limit.

```php
# filename: knapsack-01.php
<?php

declare(strict_types=1);

class Knapsack01
{
    // Maximum value achievable
    public function maxValue(array $weights, array $values, int $capacity): int
    {
        $n = count($weights);

        // dp[i][w] = max value using first i items with capacity w
        $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));

        for ($i = 1; $i <= $n; $i++) {
            for ($w = 0; $w <= $capacity; $w++) {
                // Don't include item i-1
                $dp[$i][$w] = $dp[$i - 1][$w];

                // Include item i-1 if it fits
                if ($weights[$i - 1] <= $w) {
                    $includeValue = $values[$i - 1] + $dp[$i - 1][$w - $weights[$i - 1]];
                    $dp[$i][$w] = max($dp[$i][$w], $includeValue);
                }
            }
        }

        return $dp[$n][$capacity];
    }

    // Get items included
    public function maxValueWithItems(array $weights, array $values, int $capacity): array
    {
        $n = count($weights);
        $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));

        // Fill DP table
        for ($i = 1; $i <= $n; $i++) {
            for ($w = 0; $w <= $capacity; $w++) {
                $dp[$i][$w] = $dp[$i - 1][$w];

                if ($weights[$i - 1] <= $w) {
                    $includeValue = $values[$i - 1] + $dp[$i - 1][$w - $weights[$i - 1]];
                    $dp[$i][$w] = max($dp[$i][$w], $includeValue);
                }
            }
        }

        // Reconstruct solution
        $items = [];
        $w = $capacity;
        for ($i = $n; $i > 0; $i--) {
            if ($dp[$i][$w] !== $dp[$i - 1][$w]) {
                $items[] = $i - 1;  // Item index
                $w -= $weights[$i - 1];
            }
        }

        return [
            'maxValue' => $dp[$n][$capacity],
            'items' => array_reverse($items)
        ];
    }

    // Space-optimized (1D array)
    public function maxValueOptimized(array $weights, array $values, int $capacity): int
    {
        $dp = array_fill(0, $capacity + 1, 0);

        for ($i = 0; $i < count($weights); $i++) {
            // Traverse backwards to avoid using same item multiple times
            for ($w = $capacity; $w >= $weights[$i]; $w--) {
                $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
            }
        }

        return $dp[$capacity];
    }
}

// Example
$knapsack = new Knapsack01();

$weights = [1, 3, 4, 5];
$values = [1, 4, 5, 7];
$capacity = 7;

echo "Max value: " . $knapsack->maxValue($weights, $values, $capacity) . "\n";  // 9

$result = $knapsack->maxValueWithItems($weights, $values, $capacity);
echo "Max value: {$result['maxValue']}\n";
echo "Items: " . implode(', ', $result['items']) . "\n";  // Items 1, 2 (weights 3+4=7, values 4+5=9)
```

### 5b. Unbounded Knapsack & Rod Cutting

In the **0/1 Knapsack** problem, each item can be used **at most once**. The **Unbounded Knapsack** problem allows items to be used **multiple times**. This fundamental difference changes how we iterate in space-optimized solutions.

**Key Difference:**

- **0/1 Knapsack**: Traverse **backwards** to prevent using same item twice
- **Unbounded Knapsack**: Traverse **forwards** to allow reusing items

#### Rod Cutting Problem

A classic unbounded knapsack problem: Given a rod of length `n` and prices for pieces of different lengths, maximize revenue by cutting the rod optimally.

```php
# filename: rod-cutting.php
<?php

declare(strict_types=1);

class RodCutting
{
    // Maximum revenue from cutting rod of length n
    public function maxRevenue(array $prices, int $length): int
    {
        // prices[i] = price for piece of length i+1
        // dp[i] = max revenue for rod of length i
        $dp = array_fill(0, $length + 1, 0);

        for ($i = 1; $i <= $length; $i++) {
            for ($j = 0; $j < $i && $j < count($prices); $j++) {
                // Try cutting piece of length j+1
                $dp[$i] = max($dp[$i], $prices[$j] + $dp[$i - ($j + 1)]);
            }
        }

        return $dp[$length];
    }

    // Get actual cuts that maximize revenue
    public function findCuts(array $prices, int $length): array
    {
        $dp = array_fill(0, $length + 1, 0);
        $cuts = array_fill(0, $length + 1, []);

        for ($i = 1; $i <= $length; $i++) {
            for ($j = 0; $j < $i && $j < count($prices); $j++) {
                $pieceLength = $j + 1;
                $revenue = $prices[$j] + $dp[$i - $pieceLength];

                if ($revenue > $dp[$i]) {
                    $dp[$i] = $revenue;
                    $cuts[$i] = array_merge([$pieceLength], $cuts[$i - $pieceLength]);
                }
            }
        }

        return $cuts[$length];
    }
}

// Example
$rod = new RodCutting();

$prices = [1, 5, 8, 9, 10, 17, 17, 20];  // Prices for lengths 1-8
echo "Max revenue for length 8: " . $rod->maxRevenue($prices, 8) . "\n";  // 22
print_r($rod->findCuts($prices, 8));  // [2, 6] or [1, 2, 5] etc.

// Time: O(n²), Space: O(n)
```

**Why It Works**: For each rod length `i`, we try all possible first cuts `j+1`. Since pieces can be reused (unbounded), we can use `dp[i - (j+1)]` which may already include the same piece. This is why forward iteration works - we're building up solutions that allow reuse.

#### Unbounded Knapsack

Similar to 0/1 knapsack, but items can be used multiple times.

```php
# filename: unbounded-knapsack.php
<?php

declare(strict_types=1);

class UnboundedKnapsack
{
    // Maximum value with unlimited items
    public function maxValue(array $weights, array $values, int $capacity): int
    {
        // dp[w] = max value with capacity w
        $dp = array_fill(0, $capacity + 1, 0);

        // Forward iteration allows reusing items
        for ($w = 0; $w <= $capacity; $w++) {
            for ($i = 0; $i < count($weights); $i++) {
                if ($weights[$i] <= $w) {
                    $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
                }
            }
        }

        return $dp[$capacity];
    }

    // Get items used (may include duplicates)
    public function maxValueWithItems(array $weights, array $values, int $capacity): array
    {
        $dp = array_fill(0, $capacity + 1, 0);
        $items = array_fill(0, $capacity + 1, []);

        for ($w = 0; $w <= $capacity; $w++) {
            for ($i = 0; $i < count($weights); $i++) {
                if ($weights[$i] <= $w) {
                    $newValue = $values[$i] + $dp[$w - $weights[$i]];
                    if ($newValue > $dp[$w]) {
                        $dp[$w] = $newValue;
                        $items[$w] = array_merge([$i], $items[$w - $weights[$i]]);
                    }
                }
            }
        }

        return [
            'maxValue' => $dp[$capacity],
            'items' => $items[$capacity]
        ];
    }
}

// Example
$unbounded = new UnboundedKnapsack();

$weights = [1, 3, 4];
$values = [15, 20, 30];
$capacity = 7;

echo "Max value (unbounded): " . $unbounded->maxValue($weights, $values, $capacity) . "\n";  // 105
// Uses item 0 (weight 1, value 15) seven times: 7 × 15 = 105

$result = $unbounded->maxValueWithItems($weights, $values, $capacity);
echo "Max value: {$result['maxValue']}\n";
echo "Items used: " . implode(', ', $result['items']) . "\n";  // [0, 0, 0, 0, 0, 0, 0]

// Time: O(n × W), Space: O(W)
```

#### Bounded vs Unbounded: Key Differences

Understanding when to use forward vs backward iteration is crucial:

| Aspect                  | 0/1 Knapsack (Bounded)         | Unbounded Knapsack               |
| ----------------------- | ------------------------------ | -------------------------------- |
| **Item Usage**          | At most once                   | Unlimited times                  |
| **Iteration Direction** | Backward (`w--`)               | Forward (`w++`)                  |
| **Why**                 | Prevents using same item twice | Allows reusing items             |
| **DP State**            | `dp[w]` before including item  | `dp[w]` may already include item |
| **Example**             | Each item is unique            | Coins, rod pieces                |

**Critical Insight**:

- **Backward iteration** ensures when computing `dp[w]`, we use `dp[w - weight]` which was computed **before** considering the current item
- **Forward iteration** allows `dp[w - weight]` to already include the current item, enabling reuse

```php
// 0/1 Knapsack: Backward iteration
for ($w = $capacity; $w >= $weights[$i]; $w--) {
    $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
    // dp[w - weights[i]] computed BEFORE considering item i
}

// Unbounded Knapsack: Forward iteration
for ($w = $weights[$i]; $w <= $capacity; $w++) {
    $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
    // dp[w - weights[i]] may already include item i (reuse allowed)
}
```

### 6. Longest Increasing Subsequence (LIS)

Find longest subsequence where elements are in increasing order.

```php
# filename: longest-increasing-subsequence.php
<?php

declare(strict_types=1);

class LongestIncreasingSubsequence
{
    // O(n²) DP solution
    public function lisLength(array $nums): int
    {
        $n = count($nums);
        if ($n === 0) return 0;

        // dp[i] = length of LIS ending at index i
        $dp = array_fill(0, $n, 1);

        for ($i = 1; $i < $n; $i++) {
            for ($j = 0; $j < $i; $j++) {
                if ($nums[$j] < $nums[$i]) {
                    $dp[$i] = max($dp[$i], $dp[$j] + 1);
                }
            }
        }

        return max($dp);
    }

    // Get actual LIS
    public function lis(array $nums): array
    {
        $n = count($nums);
        if ($n === 0) return [];

        $dp = array_fill(0, $n, 1);
        $prev = array_fill(0, $n, -1);

        for ($i = 1; $i < $n; $i++) {
            for ($j = 0; $j < $i; $j++) {
                if ($nums[$j] < $nums[$i] && $dp[$j] + 1 > $dp[$i]) {
                    $dp[$i] = $dp[$j] + 1;
                    $prev[$i] = $j;
                }
            }
        }

        // Find index with max LIS length
        $maxLength = max($dp);
        $maxIndex = array_search($maxLength, $dp);

        // Reconstruct LIS
        $lis = [];
        $current = $maxIndex;
        while ($current !== -1) {
            array_unshift($lis, $nums[$current]);
            $current = $prev[$current];
        }

        return $lis;
    }

    // O(n log n) solution using binary search
    public function lisLengthOptimized(array $nums): int
    {
        $tails = [];  // tails[i] = smallest tail of all LIS of length i+1

        foreach ($nums as $num) {
            // Binary search for position
            $left = 0;
            $right = count($tails);

            while ($left < $right) {
                $mid = (int)(($left + $right) / 2);
                if ($tails[$mid] < $num) {
                    $left = $mid + 1;
                } else {
                    $right = $mid;
                }
            }

            // Update or append
            if ($left === count($tails)) {
                $tails[] = $num;
            } else {
                $tails[$left] = $num;
            }
        }

        return count($tails);
    }
}

// Example
$lis = new LongestIncreasingSubsequence();

$nums = [10, 9, 2, 5, 3, 7, 101, 18];
echo "LIS length: " . $lis->lisLength($nums) . "\n";  // 4
print_r($lis->lis($nums));  // [2, 3, 7, 18] or [2, 5, 7, 18]
echo "LIS length (optimized): " . $lis->lisLengthOptimized($nums) . "\n";  // 4
```

### 7. Minimum Path Sum (Grid DP)

Find the minimum cost path from top-left to bottom-right in a grid, moving only right or down. This demonstrates grid-based DP with optimization (not just counting).

```php
# filename: minimum-path-sum.php
<?php

declare(strict_types=1);

class MinimumPathSum
{
    // Minimum cost path from (0,0) to (m-1, n-1)
    public function minPathSum(array $grid): int
    {
        $m = count($grid);
        $n = count($grid[0]);

        // dp[i][j] = min cost to reach (i, j)
        $dp = array_fill(0, $m, array_fill(0, $n, 0));
        $dp[0][0] = $grid[0][0];

        // Fill first row (can only come from left)
        for ($j = 1; $j < $n; $j++) {
            $dp[0][$j] = $dp[0][$j - 1] + $grid[0][$j];
        }

        // Fill first column (can only come from top)
        for ($i = 1; $i < $m; $i++) {
            $dp[$i][0] = $dp[$i - 1][0] + $grid[$i][0];
        }

        // Fill rest: min of coming from top or left
        for ($i = 1; $i < $m; $i++) {
            for ($j = 1; $j < $n; $j++) {
                $dp[$i][$j] = min($dp[$i - 1][$j], $dp[$i][$j - 1]) + $grid[$i][$j];
            }
        }

        return $dp[$m - 1][$n - 1];
    }

    // Space-optimized (only need previous row)
    public function minPathSumOptimized(array $grid): int
    {
        $m = count($grid);
        $n = count($grid[0]);

        $prev = array_fill(0, $n, 0);
        $prev[0] = $grid[0][0];

        // First row
        for ($j = 1; $j < $n; $j++) {
            $prev[$j] = $prev[$j - 1] + $grid[0][$j];
        }

        // Process remaining rows
        for ($i = 1; $i < $m; $i++) {
            $curr = [];
            $curr[0] = $prev[0] + $grid[$i][0];  // First column

            for ($j = 1; $j < $n; $j++) {
                $curr[$j] = min($prev[$j], $curr[$j - 1]) + $grid[$i][$j];
            }

            $prev = $curr;
        }

        return $prev[$n - 1];
    }

    // Get actual path
    public function minPathSumWithPath(array $grid): array
    {
        $m = count($grid);
        $n = count($grid[0]);
        $dp = array_fill(0, $m, array_fill(0, $n, 0));
        $dp[0][0] = $grid[0][0];

        // Fill DP table
        for ($j = 1; $j < $n; $j++) {
            $dp[0][$j] = $dp[0][$j - 1] + $grid[0][$j];
        }
        for ($i = 1; $i < $m; $i++) {
            $dp[$i][0] = $dp[$i - 1][0] + $grid[$i][0];
        }
        for ($i = 1; $i < $m; $i++) {
            for ($j = 1; $j < $n; $j++) {
                $dp[$i][$j] = min($dp[$i - 1][$j], $dp[$i][$j - 1]) + $grid[$i][$j];
            }
        }

        // Reconstruct path
        $path = [];
        $i = $m - 1;
        $j = $n - 1;

        while ($i > 0 || $j > 0) {
            $path[] = [$i, $j];

            if ($i === 0) {
                $j--;
            } elseif ($j === 0) {
                $i--;
            } elseif ($dp[$i - 1][$j] < $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }
        $path[] = [0, 0];

        return [
            'minCost' => $dp[$m - 1][$n - 1],
            'path' => array_reverse($path)
        ];
    }
}

// Example
$mps = new MinimumPathSum();

$grid = [
    [1, 3, 1],
    [1, 5, 1],
    [4, 2, 1]
];

echo "Min path sum: " . $mps->minPathSum($grid) . "\n";  // 7
// Path: (0,0) → (0,1) → (0,2) → (1,2) → (2,2)
// Cost: 1 + 3 + 1 + 1 + 1 = 7

$result = $mps->minPathSumWithPath($grid);
echo "Min cost: {$result['minCost']}\n";
echo "Path: " . json_encode($result['path']) . "\n";

// Time: O(m × n), Space: O(m × n) or O(n) optimized
```

**Why It Works**: For each cell `(i, j)`, the minimum cost to reach it is the minimum of:

- Coming from top: `dp[i-1][j] + grid[i][j]`
- Coming from left: `dp[i][j-1] + grid[i][j]`

This is a classic grid DP pattern where we build solutions cell by cell, always choosing the optimal previous state.

### 8. Unique Paths

Count the number of unique paths from top-left to bottom-right in a grid, moving only right or down.

```php
# filename: unique-paths.php
<?php

declare(strict_types=1);

class UniquePaths
{
    // Count paths in m×n grid (2D DP)
    public function uniquePaths(int $m, int $n): int
    {
        // dp[i][j] = number of paths to reach cell (i, j)
        $dp = array_fill(0, $m, array_fill(0, $n, 0));

        // Base case: first row and column have only 1 path
        for ($i = 0; $i < $m; $i++) {
            $dp[$i][0] = 1;
        }
        for ($j = 0; $j < $n; $j++) {
            $dp[0][$j] = 1;
        }

        // Fill DP table
        for ($i = 1; $i < $m; $i++) {
            for ($j = 1; $j < $n; $j++) {
                // Can reach (i,j) from above or left
                $dp[$i][$j] = $dp[$i - 1][$j] + $dp[$i][$j - 1];
            }
        }

        return $dp[$m - 1][$n - 1];
    }

    // Space-optimized version (1D array)
    public function uniquePathsOptimized(int $m, int $n): int
    {
        // Only need previous row, so use 1D array
        $prev = array_fill(0, $n, 1);

        for ($i = 1; $i < $m; $i++) {
            $curr = array_fill(0, $n, 0);
            $curr[0] = 1;  // First column always 1

            for ($j = 1; $j < $n; $j++) {
                $curr[$j] = $prev[$j] + $curr[$j - 1];
            }

            $prev = $curr;
        }

        return $prev[$n - 1];
    }

    // Unique paths with obstacles (0 = obstacle, 1 = free)
    public function uniquePathsWithObstacles(array $obstacleGrid): int
    {
        $m = count($obstacleGrid);
        $n = count($obstacleGrid[0]);

        // If start or end is blocked, no paths
        if ($obstacleGrid[0][0] === 1 || $obstacleGrid[$m - 1][$n - 1] === 1) {
            return 0;
        }

        $dp = array_fill(0, $m, array_fill(0, $n, 0));
        $dp[0][0] = 1;

        // Fill first row
        for ($j = 1; $j < $n; $j++) {
            $dp[0][$j] = ($obstacleGrid[0][$j] === 0 && $dp[0][$j - 1] > 0) ? 1 : 0;
        }

        // Fill first column
        for ($i = 1; $i < $m; $i++) {
            $dp[$i][0] = ($obstacleGrid[$i][0] === 0 && $dp[$i - 1][0] > 0) ? 1 : 0;
        }

        // Fill rest of grid
        for ($i = 1; $i < $m; $i++) {
            for ($j = 1; $j < $n; $j++) {
                if ($obstacleGrid[$i][$j] === 0) {
                    $dp[$i][$j] = $dp[$i - 1][$j] + $dp[$i][$j - 1];
                } else {
                    $dp[$i][$j] = 0;  // Obstacle blocks path
                }
            }
        }

        return $dp[$m - 1][$n - 1];
    }

    // Get actual paths (for small grids)
    public function getAllPaths(int $m, int $n): array
    {
        $paths = [];
        $currentPath = [];

        $this->dfs(0, 0, $m, $n, $currentPath, $paths);

        return $paths;
    }

    private function dfs(int $i, int $j, int $m, int $n, array &$path, array &$paths): void
    {
        // Reached destination
        if ($i === $m - 1 && $j === $n - 1) {
            $paths[] = $path;
            return;
        }

        // Move right
        if ($j < $n - 1) {
            $path[] = 'R';
            $this->dfs($i, $j + 1, $m, $n, $path, $paths);
            array_pop($path);
        }

        // Move down
        if ($i < $m - 1) {
            $path[] = 'D';
            $this->dfs($i + 1, $j, $m, $n, $path, $paths);
            array_pop($path);
        }
    }
}

// Example
$paths = new UniquePaths();

echo "Paths in 3×7 grid: " . $paths->uniquePaths(3, 7) . "\n";  // 28
echo "Paths in 3×2 grid: " . $paths->uniquePaths(3, 2) . "\n";  // 3
echo "Paths (optimized): " . $paths->uniquePathsOptimized(3, 7) . "\n";  // 28

// With obstacles
$obstacles = [
    [0, 0, 0],
    [0, 1, 0],
    [0, 0, 0]
];
echo "Paths with obstacles: " . $paths->uniquePathsWithObstacles($obstacles) . "\n";  // 2

// Get all paths for small grid
print_r($paths->getAllPaths(2, 2));  // [['R', 'D'], ['D', 'R']]

// Time: O(m × n), Space: O(min(m, n)) optimized
```

**Why It Works**: This is a classic 2D DP problem. `dp[i][j]` represents the number of ways to reach cell `(i, j)`. Since we can only move right or down, we can reach `(i, j)` from `(i-1, j)` (above) or `(i, j-1)` (left). The base case is that the first row and column have only 1 path each. The space-optimized version uses only one row at a time since we only need the previous row to compute the current row.

## DP Pattern Recognition

```php
# filename: dp-pattern-recognition.php
<?php

declare(strict_types=1);

class DPPatterns
{
    public function identifyPattern(string $problemType): array
    {
        $patterns = [
            'Optimization' => [
                'Questions' => 'Maximum/minimum, best way, optimal',
                'Examples' => 'Knapsack, coin change, LIS',
                'Approach' => 'Find max/min at each step'
            ],
            'Counting' => [
                'Questions' => 'How many ways, number of solutions',
                'Examples' => 'Climbing stairs, coin combinations',
                'Approach' => 'Sum up ways to reach current state'
            ],
            'Decision' => [
                'Questions' => 'Is it possible, can we achieve',
                'Examples' => 'Subset sum, partition equal, word break',
                'Approach' => 'Boolean DP, track possibility'
            ],
            'Sequence' => [
                'Questions' => 'Longest/shortest sequence',
                'Examples' => 'LCS, LIS, edit distance',
                'Approach' => 'Build sequence incrementally'
            ]
        ];

        return $patterns[$problemType] ?? [];
    }

    public function dpSteps(): array
    {
        return [
            '1. Identify' => 'Does problem have optimal substructure and overlapping subproblems?',
            '2. Define State' => 'What does dp[i] or dp[i][j] represent?',
            '3. Find Recurrence' => 'How to compute dp[i] from smaller subproblems?',
            '4. Base Cases' => 'What are the initial/trivial cases?',
            '5. Order' => 'What order to fill the DP table?',
            '6. Answer' => 'Where is the final answer in the table?',
            '7. Optimize' => 'Can we reduce space complexity?'
        ];
    }
}
```

## Complexity Analysis

| Problem            | Naive      | DP Time | DP Space | Optimized Space           |
| ------------------ | ---------- | ------- | -------- | ------------------------- |
| Fibonacci          | O(2ⁿ)      | O(n)    | O(n)     | O(1)                      |
| Climbing Stairs    | O(2ⁿ)      | O(n)    | O(n)     | O(1)                      |
| Coin Change        | O(2ⁿ)      | O(n×m)  | O(n)     | O(n)                      |
| Subset Sum         | O(2ⁿ)      | O(n×S)  | O(n×S)   | O(S)                      |
| LCS                | O(2^(m+n)) | O(m×n)  | O(m×n)   | O(min(m,n))               |
| Knapsack 0/1       | O(2ⁿ)      | O(n×W)  | O(n×W)   | O(W)                      |
| Unbounded Knapsack | O(2ⁿ)      | O(n×W)  | O(W)     | O(W)                      |
| Rod Cutting        | O(2ⁿ)      | O(n²)   | O(n)     | O(n)                      |
| Minimum Path Sum   | O(2^(m+n)) | O(m×n)  | O(m×n)   | O(n)                      |
| LIS                | O(2ⁿ)      | O(n²)   | O(n)     | O(n) with O(n log n) time |
| Unique Paths       | O(2^(m+n)) | O(m×n)  | O(m×n)   | O(min(m,n))               |

Where:

- n = problem size
- m = secondary dimension (rows for grids, length for strings)
- W = knapsack capacity
- S = target sum for subset sum

## Best Practices

1. **Start with Recursion**

   - Write recursive solution first
   - Identify overlapping subproblems
   - Add memoization

2. **Choose Memoization vs Tabulation**

   - Memoization: Easier to code, only computes needed subproblems
   - Tabulation: Better space optimization, avoids recursion overhead

3. **Define DP State Carefully**

   - State should capture all necessary information
   - Keep state simple for efficiency

4. **Optimize Space**

   - Often can reduce from 2D to 1D
   - Only keep necessary rows/columns

5. **Trace Small Examples**
   - Work through small examples by hand
   - Helps identify recurrence relation

## Exercises

### Exercise 1: House Robber Problem

**Goal**: Implement a solution to maximize money robbed without robbing adjacent houses.

Create a file called `house-robber.php` and implement:

- `HouseRobber` class with `rob(array $houses): int` method
- Use DP to find maximum money that can be robbed
- Constraint: Cannot rob two adjacent houses
- Return the maximum amount possible
- Implement both memoization and tabulation approaches

**Validation**: Test your implementation:

```php
# filename: test-house-robber.php
<?php

declare(strict_types=1);

require_once 'house-robber.php';

$robber = new HouseRobber();

// Test case 1: [2, 7, 9, 3, 1]
// Optimal: rob houses 0, 2, 4 = 2 + 9 + 1 = 12
echo $robber->rob([2, 7, 9, 3, 1]) . "\n";  // Expected: 12

// Test case 2: [1, 2, 3, 1]
// Optimal: rob houses 0, 2 = 1 + 3 = 4
echo $robber->rob([1, 2, 3, 1]) . "\n";  // Expected: 4

// Test case 3: [2, 1, 1, 2]
// Optimal: rob houses 0, 3 = 2 + 2 = 4
echo $robber->rob([2, 1, 1, 2]) . "\n";  // Expected: 4
```

**Hint**: `dp[i] = max(dp[i-1], dp[i-2] + houses[i])` - either skip current house or rob it.

### Exercise 2: Edit Distance (Levenshtein Distance)

**Goal**: Find minimum operations to convert one string into another.

Create a file called `edit-distance.php` and implement:

- `EditDistance` class with `minDistance(string $word1, string $word2): int` method
- Calculate minimum operations (insert, delete, replace) needed
- Use 2D DP table: `dp[i][j]` = min operations to convert `word1[0..i-1]` to `word2[0..j-1]`
- Return the minimum edit distance
- Optionally implement path reconstruction to show actual operations

**Validation**: Test your implementation:

```php
# filename: test-edit-distance.php
<?php

declare(strict_types=1);

require_once 'edit-distance.php';

$edit = new EditDistance();

echo $edit->minDistance("horse", "ros") . "\n";  // Expected: 3
// Operations: horse -> rorse (replace h with r)
//             rorse -> rose (remove r)
//             rose -> ros (remove e)

echo $edit->minDistance("intention", "execution") . "\n";  // Expected: 5
echo $edit->minDistance("", "abc") . "\n";  // Expected: 3 (insert a, b, c)
```

**Hint**: `dp[i][j] = min(dp[i-1][j] + 1, dp[i][j-1] + 1, dp[i-1][j-1] + cost)` where cost is 0 if characters match, 1 otherwise.

### Exercise 3: Maximum Subarray Sum (Kadane's Algorithm)

**Goal**: Find contiguous subarray with the largest sum using DP.

Create a file called `maximum-subarray.php` and implement:

- `MaximumSubarray` class with `maxSubArray(array $nums): int` method
- Use DP to track maximum sum ending at each position
- Return the maximum sum
- Optionally return the subarray indices that produce maximum sum

**Validation**: Test your implementation:

```php
# filename: test-maximum-subarray.php
<?php

declare(strict_types=1);

require_once 'maximum-subarray.php';

$maxSub = new MaximumSubarray();

echo $maxSub->maxSubArray([-2, 1, -3, 4, -1, 2, 1, -5, 4]) . "\n";  // Expected: 6
// Subarray: [4, -1, 2, 1]

echo $maxSub->maxSubArray([1]) . "\n";  // Expected: 1
echo $maxSub->maxSubArray([5, 4, -1, 7, 8]) . "\n";  // Expected: 23
```

**Hint**: `dp[i] = max(nums[i], dp[i-1] + nums[i])` - either start new subarray or extend previous.

### Exercise 4: Word Break Problem

**Goal**: Determine if a string can be segmented into dictionary words.

Create a file called `word-break.php` and implement:

- `WordBreak` class with `wordBreak(string $s, array $wordDict): bool` method
- Use boolean DP: `dp[i]` = true if `s[0..i-1]` can be segmented
- Return true if entire string can be segmented, false otherwise
- Optionally return the actual segmentation

**Validation**: Test your implementation:

```php
# filename: test-word-break.php
<?php

declare(strict_types=1);

require_once 'word-break.php';

$wb = new WordBreak();

echo $wb->wordBreak("leetcode", ["leet", "code"]) ? "true" : "false";  // Expected: true
echo "\n";

echo $wb->wordBreak("applepenapple", ["apple", "pen"]) ? "true" : "false";  // Expected: true
echo "\n";

echo $wb->wordBreak("catsandog", ["cats", "dog", "sand", "and", "cat"]) ? "true" : "false";  // Expected: false
echo "\n";
```

**Hint**: `dp[i] = true` if there exists `j < i` such that `dp[j] = true` and `s[j..i-1]` is in dictionary.

## Troubleshooting

### Error: "Maximum recursion depth exceeded"

**Symptom**: Stack overflow when using memoization with large inputs

**Cause**: Recursive memoization can still hit stack limits for very large problems

**Solution**: Convert to tabulation (iterative) approach:

```php
// ❌ Memoization (can overflow)
public function fibMemo(int $n): int
{
    if ($n <= 1) return $n;
    if (isset($this->memo[$n])) return $this->memo[$n];
    return $this->memo[$n] = $this->fibMemo($n - 1) + $this->fibMemo($n - 2);
}

// ✅ Tabulation (no recursion)
public function fibTab(int $n): int
{
    if ($n <= 1) return $n;
    $dp = [0, 1];
    for ($i = 2; $i <= $n; $i++) {
        $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
    }
    return $dp[$n];
}
```

### Problem: Wrong answer for coin change

**Symptom**: Getting incorrect minimum coins or -1 when solution exists

**Cause**: Common mistakes:

- Not initializing `dp[0] = 0` correctly
- Using wrong comparison in min calculation
- Not handling impossible cases properly

**Solution**: Verify base case and recurrence:

```php
// ✅ Correct initialization
$dp = array_fill(0, $amount + 1, PHP_INT_MAX);
$dp[0] = 0;  // Critical: 0 coins for amount 0

// ✅ Correct recurrence
for ($i = 1; $i <= $amount; $i++) {
    foreach ($coins as $coin) {
        if ($coin <= $i && $dp[$i - $coin] !== PHP_INT_MAX) {
            $dp[$i] = min($dp[$i], $dp[$i - $coin] + 1);
        }
    }
}

// ✅ Check for impossible case
return $dp[$amount] === PHP_INT_MAX ? -1 : $dp[$amount];
```

### Problem: LCS returns wrong length

**Symptom**: LCS length doesn't match expected value

**Cause**: Common issues:

- Off-by-one errors in string indexing
- Wrong base case initialization
- Incorrect recurrence relation

**Solution**: Double-check indexing and recurrence:

```php
// ✅ Correct: dp[i][j] represents LCS of text1[0..i-1] and text2[0..j-1]
$m = strlen($text1);
$n = strlen($text2);
$dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

for ($i = 1; $i <= $m; $i++) {
    for ($j = 1; $j <= $n; $j++) {
        // Note: text1[$i-1] because dp[i][j] uses 1-indexed, string uses 0-indexed
        if ($text1[$i - 1] === $text2[$j - 1]) {
            $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
        } else {
            $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
        }
    }
}

return $dp[$m][$n];  // Answer is at dp[m][n]
```

### Problem: Knapsack space optimization gives wrong result

**Symptom**: 1D array version produces incorrect maximum value

**Cause**: Iterating forward instead of backward, causing items to be used multiple times

**Solution**: Always iterate backwards in space-optimized knapsack:

```php
// ❌ Wrong: Forward iteration (allows using same item multiple times)
for ($w = $weights[$i]; $w <= $capacity; $w++) {
    $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
}

// ✅ Correct: Backward iteration (ensures each item used at most once)
for ($w = $capacity; $w >= $weights[$i]; $w--) {
    $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
}
```

### Problem: DP state not capturing all necessary information

**Symptom**: Solution works for some cases but fails for others

**Cause**: DP state definition is incomplete - missing important constraints or dimensions

**Solution**: Review problem constraints and ensure state captures all relevant information:

```php
// Example: If problem has multiple constraints, state needs multiple dimensions
// ❌ Insufficient: dp[i] = max value using first i items
// ✅ Complete: dp[i][w] = max value using first i items with capacity w

// For problems with additional constraints (like "at most k items"):
// ✅ Complete: dp[i][w][k] = max value using first i items, capacity w, at most k items
```

### Problem: Time limit exceeded in competitive programming

**Symptom**: Solution is correct but too slow

**Cause**: Not optimizing space or using inefficient state transitions

**Solution**:

1. Optimize space complexity (2D → 1D when possible)
2. Reduce state dimensions if some are redundant
3. Use tabulation instead of memoization (faster in practice)
4. Consider if problem can be solved with greedy instead of DP

## Wrap-up

Congratulations! You've mastered the fundamentals of Dynamic Programming. Here's what you've accomplished:

- ✓ Understood the two key properties: overlapping subproblems and optimal substructure
- ✓ Implemented both memoization (top-down) and tabulation (bottom-up) approaches
- ✓ Solved classic DP problems: Fibonacci, climbing stairs, coin change, subset sum, LCS, knapsack (0/1 and unbounded), rod cutting, minimum path sum, and LIS
- ✓ Learned the critical difference between bounded (0/1) and unbounded problems, and when to use forward vs backward iteration
- ✓ Recognized DP patterns and when to apply this technique
- ✓ Optimized space complexity by keeping only necessary previous states
- ✓ Built visualization tools to understand DP state transitions

Dynamic Programming is a powerful optimization technique that transforms exponential-time solutions into polynomial or linear-time algorithms. The key is recognizing when a problem has overlapping subproblems and optimal substructure, then choosing the right approach (memoization or tabulation) based on your needs.

Remember the progression: start with recursion → add memoization → convert to tabulation → optimize space. This systematic approach will help you solve increasingly complex DP problems.

## Key Takeaways

- Dynamic Programming optimizes by storing solutions to subproblems
- Two key properties: overlapping subproblems and optimal substructure
- Two approaches: memoization (top-down) and tabulation (bottom-up)
- Memoization: Add caching to recursive solution
- Tabulation: Build table iteratively from base cases
- Often can optimize space by keeping only necessary previous states
- Pattern recognition helps identify DP problems
- Start simple, optimize later (recursion → memoization → tabulation → space optimization)

## Further Reading

- [Dynamic Programming on GeeksforGeeks](https://www.geeksforgeeks.org/dynamic-programming/) — Comprehensive DP tutorials and problems
- [LeetCode Dynamic Programming Problems](https://leetcode.com/tag/dynamic-programming/) — Practice problems with solutions
- [Introduction to Algorithms (CLRS)](https://mitpress.mit.edu/9780262046305/introduction-to-algorithms/) — Chapter 15 covers dynamic programming in depth
- [Chapter 26: Advanced Dynamic Programming](/series/php-algorithms/chapters/26-advanced-dynamic-programming) — Next chapter covering advanced DP techniques

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="25"
  label="Dynamic Programming fundamentals mastered!"
/>

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 25 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-25)**

Clone the repository to run examples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-25
php *.php
```

## Next Steps

In the next chapter, we'll explore advanced dynamic programming techniques including state compression, bitmask DP, digit DP, and DP on trees and graphs.
