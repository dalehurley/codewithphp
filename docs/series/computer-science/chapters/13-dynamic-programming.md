---
title: "13: Dynamic Programming"
description: "Optimize recursive solutions with memoization and tabulation. Solve classic DP problems like Fibonacci, knapsack, longest common subsequence, and edit distance."
series: "computer-science"
chapter: 13
order: 13
difficulty: "Advanced"
prerequisites: ["Recursion", "Algorithm analysis"]
---

# Chapter 13: Dynamic Programming

## Introduction

Dynamic Programming (DP) is an optimization technique that solves complex problems by breaking them down into simpler overlapping subproblems and storing their results to avoid redundant computation.

In this chapter, you'll learn:

- When to use dynamic programming
- Memoization vs. tabulation
- Classic DP problems
- DP optimization techniques

## What is Dynamic Programming?

DP optimizes problems with:

1. **Overlapping subproblems**: Same subproblems solved multiple times
2. **Optimal substructure**: Optimal solution contains optimal solutions to subproblems

### Fibonacci: The Classic Example

```php
<?php

// Naive recursion - O(2^n)
function fibRecursive(int $n): int {
    if ($n <= 1) return $n;
    return fibRecursive($n - 1) + fibRecursive($n - 2);
}

// Memoization (Top-Down DP) - O(n)
function fibMemo(int $n, array &$memo = []): int {
    if ($n <= 1) return $n;

    if (!isset($memo[$n])) {
        $memo[$n] = fibMemo($n - 1, $memo) + fibMemo($n - 2, $memo);
    }

    return $memo[$n];
}

// Tabulation (Bottom-Up DP) - O(n)
function fibTabulation(int $n): int {
    if ($n <= 1) return $n;

    $dp = [0, 1];

    for ($i = 2; $i <= $n; $i++) {
        $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
    }

    return $dp[$n];
}

// Space-optimized - O(n) time, O(1) space
function fibOptimized(int $n): int {
    if ($n <= 1) return $n;

    $prev = 0;
    $curr = 1;

    for ($i = 2; $i <= $n; $i++) {
        $next = $prev + $curr;
        $prev = $curr;
        $curr = $next;
    }

    return $curr;
}
```

## Memoization vs. Tabulation

| Approach | Direction | Implementation | Space | When to Use |
|----------|-----------|----------------|-------|-------------|
| **Memoization** | Top-down | Recursive + cache | O(n) + recursion stack | Natural recursion, not all subproblems needed |
| **Tabulation** | Bottom-up | Iterative + table | O(n) | All subproblems needed, avoid recursion |

## Classic DP Problems

### 1. Climbing Stairs

```php
<?php

// Ways to climb n stairs (1 or 2 steps at a time)
function climbStairs(int $n): int {
    if ($n <= 2) return $n;

    $dp = [0, 1, 2];

    for ($i = 3; $i <= $n; $i++) {
        $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
    }

    return $dp[$n];
}

echo climbStairs(5); // 8 ways
```

### 2. 0/1 Knapsack

```php
<?php

function knapsack(array $weights, array $values, int $capacity): int {
    $n = count($weights);
    $dp = array_fill(0, $n + 1, array_fill(0, $capacity + 1, 0));

    for ($i = 1; $i <= $n; $i++) {
        for ($w = 0; $w <= $capacity; $w++) {
            if ($weights[$i - 1] <= $w) {
                // Include or exclude item
                $dp[$i][$w] = max(
                    $values[$i - 1] + $dp[$i - 1][$w - $weights[$i - 1]],
                    $dp[$i - 1][$w]
                );
            } else {
                $dp[$i][$w] = $dp[$i - 1][$w];
            }
        }
    }

    return $dp[$n][$capacity];
}

$weights = [1, 2, 3];
$values = [6, 10, 12];
echo knapsack($weights, $values, 5); // 22
```

### 3. Longest Common Subsequence (LCS)

```php
<?php

function longestCommonSubsequence(string $text1, string $text2): int {
    $m = strlen($text1);
    $n = strlen($text2);

    $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

    for ($i = 1; $i <= $m; $i++) {
        for ($j = 1; $j <= $n; $j++) {
            if ($text1[$i - 1] === $text2[$j - 1]) {
                $dp[$i][$j] = 1 + $dp[$i - 1][$j - 1];
            } else {
                $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
            }
        }
    }

    return $dp[$m][$n];
}

echo longestCommonSubsequence("abcde", "ace"); // 3 ("ace")
```

### 4. Edit Distance (Levenshtein Distance)

```php
<?php

function minDistance(string $word1, string $word2): int {
    $m = strlen($word1);
    $n = strlen($word2);

    $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

    // Initialize base cases
    for ($i = 0; $i <= $m; $i++) {
        $dp[$i][0] = $i;
    }
    for ($j = 0; $j <= $n; $j++) {
        $dp[0][$j] = $j;
    }

    for ($i = 1; $i <= $m; $i++) {
        for ($j = 1; $j <= $n; $j++) {
            if ($word1[$i - 1] === $word2[$j - 1]) {
                $dp[$i][$j] = $dp[$i - 1][$j - 1];
            } else {
                $dp[$i][$j] = 1 + min(
                    $dp[$i - 1][$j],     // Delete
                    $dp[$i][$j - 1],     // Insert
                    $dp[$i - 1][$j - 1]  // Replace
                );
            }
        }
    }

    return $dp[$m][$n];
}

echo minDistance("horse", "ros"); // 3
```

### 5. Coin Change

```php
<?php

function coinChange(array $coins, int $amount): int {
    $dp = array_fill(0, $amount + 1, PHP_INT_MAX);
    $dp[0] = 0;

    for ($i = 1; $i <= $amount; $i++) {
        foreach ($coins as $coin) {
            if ($i >= $coin && $dp[$i - $coin] !== PHP_INT_MAX) {
                $dp[$i] = min($dp[$i], 1 + $dp[$i - $coin]);
            }
        }
    }

    return $dp[$amount] === PHP_INT_MAX ? -1 : $dp[$amount];
}

$coins = [1, 2, 5];
echo coinChange($coins, 11); // 3 (5+5+1)
```

## DP Optimization Patterns

### Space Optimization

```php
<?php

// 2D DP → 1D DP
function knapsackOptimized(array $weights, array $values, int $capacity): int {
    $dp = array_fill(0, $capacity + 1, 0);

    foreach ($weights as $i => $weight) {
        for ($w = $capacity; $w >= $weight; $w--) {
            $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weight]);
        }
    }

    return $dp[$capacity];
}
```

## When to Use DP

**Use DP when**:
- Overlapping subproblems exist
- Problem has optimal substructure
- Problem asks for optimization (min/max/count)
- Greedy doesn't work

**DP vs. Other Approaches**:
- **Divide & Conquer**: No overlapping subproblems
- **Greedy**: Can't guarantee optimal solution
- **Backtracking**: Need all solutions, not just optimal

## Key Takeaways

- **DP** optimizes problems with overlapping subproblems
- **Memoization**: Top-down, cache results
- **Tabulation**: Bottom-up, fill table
- **Space optimization**: Often possible
- **Time**: Usually O(n²) or O(n × m)

## Exercises

1. **Longest Increasing Subsequence**: Find length of LIS.

2. **Partition Equal Subset Sum**: Can array be partitioned into equal sums?

3. **Maximum Subarray Sum**: Find contiguous subarray with maximum sum (Kadane's algorithm).

4. **Unique Paths**: Count paths in grid from top-left to bottom-right.

## What's Next?

DP is powerful for optimization. In Chapter 14, we'll explore **Design Patterns**—reusable solutions to common software design problems.

---

**Further Reading**:
- [Dynamic Programming (Wikipedia)](https://en.wikipedia.org/wiki/Dynamic_programming)
- [DP Patterns](https://www.geeksforgeeks.org/dynamic-programming/)
