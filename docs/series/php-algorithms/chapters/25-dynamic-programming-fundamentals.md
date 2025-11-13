---
title: "Dynamic Programming Fundamentals"
description: "Master dynamic programming concepts including overlapping subproblems, optimal substructure, memoization, and tabulation with classic problems like Fibonacci, knapsack, and longest common subsequence"
series: "php-algorithms"
chapter: 25
order: 25
difficulty: "advanced"
prerequisites: ["Recursion Fundamentals", "Arrays & Dynamic Arrays"]
---

# Dynamic Programming Fundamentals

Dynamic Programming (DP) is an optimization technique that solves complex problems by breaking them down into simpler overlapping subproblems and storing their solutions to avoid redundant computation.

## Core Concepts

### 1. Overlapping Subproblems

A problem has overlapping subproblems if the same subproblems are solved multiple times.

```php
<?php

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
<?php

// Example: Shortest path has optimal substructure
// If A→B→C is shortest path from A to C,
// then A→B must be shortest path from A to B
```

## Memoization (Top-Down DP)

Store results of expensive function calls and return cached result when same inputs occur.

### Fibonacci with Memoization

```php
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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

### 3. Longest Common Subsequence (LCS)

Find longest subsequence common to two sequences.

```php
<?php

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

### 4. 0/1 Knapsack Problem

Maximize value of items in knapsack with weight limit.

```php
<?php

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

### 5. Longest Increasing Subsequence (LIS)

Find longest subsequence where elements are in increasing order.

```php
<?php

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

## DP Pattern Recognition

```php
<?php

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
                'Examples' => 'Subset sum, partition equal',
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

| Problem | Naive | DP Time | DP Space | Optimized Space |
|---------|-------|---------|----------|-----------------|
| Fibonacci | O(2ⁿ) | O(n) | O(n) | O(1) |
| Climbing Stairs | O(2ⁿ) | O(n) | O(n) | O(1) |
| Coin Change | O(2ⁿ) | O(n×m) | O(n) | O(n) |
| LCS | O(2^(m+n)) | O(m×n) | O(m×n) | O(min(m,n)) |
| Knapsack 0/1 | O(2ⁿ) | O(n×W) | O(n×W) | O(W) |
| LIS | O(2ⁿ) | O(n²) | O(n) | O(n) with O(n log n) time |

Where:
- n = problem size
- m = secondary dimension
- W = knapsack capacity

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

## Practice Exercises

1. **House Robber**
   - Rob houses with max money without robbing adjacent
   - dp[i] = max money robbing up to house i

2. **Edit Distance**
   - Minimum operations to convert string A to B
   - Insert, delete, replace operations

3. **Maximum Subarray Sum**
   - Find contiguous subarray with largest sum
   - Kadane's algorithm

4. **Unique Paths**
   - Count paths in m×n grid (only right/down moves)
   - dp[i][j] = paths to reach cell (i,j)

5. **Word Break**
   - Can string be segmented into dictionary words?
   - Boolean DP with string matching

## Key Takeaways

- Dynamic Programming optimizes by storing solutions to subproblems
- Two key properties: overlapping subproblems and optimal substructure
- Two approaches: memoization (top-down) and tabulation (bottom-up)
- Memoization: Add caching to recursive solution
- Tabulation: Build table iteratively from base cases
- Often can optimize space by keeping only necessary previous states
- Pattern recognition helps identify DP problems
- Start simple, optimize later (recursion → memoization → tabulation → space optimization)

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 25 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-25)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-25
php 01-*.php
```

## Next Steps

In the next chapter, we'll explore advanced dynamic programming techniques including state compression, bitmask DP, digit DP, and DP on trees and graphs.
