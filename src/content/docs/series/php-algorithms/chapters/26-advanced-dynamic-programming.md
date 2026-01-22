---
title: "26: Advanced Dynamic Programming"
description: "Explore advanced DP techniques including matrix chain multiplication, palindrome partitioning, state compression, bitmask DP, and multi-dimensional optimization problems"
sidebar:
  label: "26: Advanced Dynamic Programming"
  order: 26
  badge:
    text: Advanced
    variant: danger
---
![Advanced Dynamic Programming](/images/php-algorithms/chapter-26-advanced-dp-hero-full.webp)


# 26: Advanced Dynamic Programming <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Building on the Dynamic Programming fundamentals from Chapter 25, this chapter explores sophisticated DP patterns used in competitive programming, complex system optimization, and real-world applications. These advanced techniques transform how you approach problems that initially appear computationally intractable.

While basic DP solves problems with simple state transitions, advanced DP handles complex scenarios: processing intervals and ranges, managing exponential state spaces with bit manipulation, optimizing multi-dimensional problems, and applying specialized techniques like digit DP and profile DP. These patterns unlock solutions to problems ranging from matrix chain multiplication to inventory optimization.

In this chapter, you'll master interval DP for range-based problems, bitmask DP for subset optimization, multi-dimensional DP for complex constraints, and specialized techniques like digit DP, probability DP, and convex hull optimization. These skills are essential for tackling advanced algorithmic challenges and optimizing real-world systems.

## Prerequisites

Before starting this chapter, you should have:

- ✓ **Dynamic Programming fundamentals** - Comfortable with memoization and tabulation from [Chapter 25: Dynamic Programming Fundamentals](/series/php-algorithms/chapters/25-dynamic-programming-fundamentals)
- ✓ **Bit manipulation** - Understanding bitwise operations (`&`, `|`, `^`, `<<`, `>>`) and their applications
- ✓ **Recursion expertise** - Proficient with recursive problem-solving and state transitions
- ✓ **Algorithm analysis** - Able to analyze time and space complexity of multi-dimensional algorithms

**Estimated Time**: ~50 minutes

## What You'll Build

By the end of this chapter, you will have created:

- Matrix chain multiplication optimizer with optimal parenthesization
- Palindrome partitioning solver with minimum cuts
- Longest palindromic subsequence finder
- Traveling Salesman Problem (TSP) solver using bitmask DP
- Edit distance calculator with operation reconstruction
- Egg drop problem solver with binary search optimization
- Matrix exponentiation solver for linear recurrences (Fibonacci, etc.)
- State machine DP for regex matching and pattern problems
- Tree diameter, coloring, and matching using DP on trees
- Longest path and critical path solver for DAGs
- Digit DP counter for numbers with specific properties
- Probability DP solver for expected value problems
- Inventory optimization system using advanced DP patterns

## Objectives

- Master interval DP for processing ranges and subarrays efficiently
- Apply bitmask DP to handle exponential state spaces with bit manipulation
- Implement multi-dimensional DP for problems with multiple constraints
- Use advanced state compression to optimize memory usage dramatically
- Recognize complex DP patterns like matrix chain multiplication and palindrome partitioning
- Apply specialized techniques: digit DP, probability DP, convex hull optimization, Knuth optimization, and matrix exponentiation
- Solve DP problems on trees and DAGs using topological ordering
- Handle advanced string DP problems like longest palindromic subsequence
- Model problems as state machines for pattern matching and complex transitions

## Interval DP

Interval DP solves problems where you need to process intervals or ranges of elements. The key insight is that optimal solutions for larger intervals depend on optimal solutions for smaller intervals within them. This pattern is common in problems involving sequences, strings, and arrays where you need to find optimal ways to partition or process contiguous segments.

The general approach:

1. Define state as `dp[i][j]` representing the optimal solution for interval `[i, j]`
2. Fill the DP table by increasing interval length
3. For each interval, try all possible split points and choose the optimal one

### Knuth Optimization

Knuth Optimization (also called Optimal Binary Search Tree optimization) reduces the time complexity of certain interval DP problems from O(n³) to O(n²) when the cost function satisfies the quadrangle inequality. This optimization applies when the optimal split point `k` for interval `[i, j]` is monotonic.

**When to Use**: When solving interval DP problems where:

- The recurrence has form: `dp[i][j] = min(dp[i][k] + dp[k+1][j] + cost(i, j, k))`
- The cost function satisfies quadrangle inequality
- Optimal split points are monotonic: `opt[i][j-1] ≤ opt[i][j] ≤ opt[i+1][j]`

```php
# filename: knuth-optimization.php
<?php

declare(strict_types=1);

class KnuthOptimization
{
    // Optimal Binary Search Tree with Knuth optimization
    // O(n²) instead of O(n³)
    public function optimalBST(array $keys, array $frequencies): int
    {
        $n = count($keys);

        // Prefix sum for frequencies
        $prefix = [0];
        for ($i = 0; $i < $n; $i++) {
            $prefix[] = $prefix[$i] + $frequencies[$i];
        }

        // dp[i][j] = min cost for keys[i...j]
        $dp = array_fill(0, $n, array_fill(0, $n, PHP_INT_MAX));
        $opt = array_fill(0, $n, array_fill(0, $n, 0));

        // Base case: single key
        for ($i = 0; $i < $n; $i++) {
            $dp[$i][$i] = $frequencies[$i];
            $opt[$i][$i] = $i;
        }

        // Fill by increasing interval length
        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;

                // Knuth optimization: narrow search range
                $left = ($i < $j - 1) ? $opt[$i][$j - 1] : $i;
                $right = ($i + 1 < $j) ? $opt[$i + 1][$j] : $j;

                for ($k = $left; $k <= $right; $k++) {
                    $cost = ($prefix[$j + 1] - $prefix[$i]);
                    if ($k > $i) {
                        $cost += $dp[$i][$k - 1];
                    }
                    if ($k < $j) {
                        $cost += $dp[$k + 1][$j];
                    }

                    if ($cost < $dp[$i][$j]) {
                        $dp[$i][$j] = $cost;
                        $opt[$i][$j] = $k;
                    }
                }
            }
        }

        return $dp[0][$n - 1];
    }
}

// Example
$ko = new KnuthOptimization();
$keys = [10, 12, 20];
$frequencies = [34, 8, 50];

echo "Optimal BST cost: " . $ko->optimalBST($keys, $frequencies) . "\n";
```

**Why It Works**: Instead of trying all split points `k` from `i` to `j` (O(n) per interval), Knuth optimization uses the monotonicity property to narrow the search range. We only check `k` between `opt[i][j-1]` and `opt[i+1][j]`, reducing the search space. The cost function `cost(i, j) = sum of frequencies in [i, j]` satisfies the quadrangle inequality, making this optimization valid. This reduces time from O(n³) to O(n²).

### Matrix Chain Multiplication

Find the optimal order to multiply a chain of matrices to minimize the number of scalar multiplications. Given matrices with dimensions that must be compatible for multiplication, we need to determine the best way to parenthesize the expression.

**Problem**: Given matrices A₁, A₂, ..., Aₙ with dimensions p₀×p₁, p₁×p₂, ..., pₙ₋₁×pₙ, find the minimum number of scalar multiplications needed.

```php
# filename: matrix-chain-multiplication.php
<?php

declare(strict_types=1);

class MatrixChainMultiplication
{
    // Minimum scalar multiplications needed
    public function minMultiplications(array $dimensions): int
    {
        $n = count($dimensions) - 1;  // Number of matrices

        // dp[i][j] = min cost to multiply matrices from i to j
        $dp = array_fill(0, $n, array_fill(0, $n, 0));

        // Length of chain
        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                $dp[$i][$j] = PHP_INT_MAX;

                // Try all possible split points
                for ($k = $i; $k < $j; $k++) {
                    $cost = $dp[$i][$k] + $dp[$k + 1][$j] +
                            $dimensions[$i] * $dimensions[$k + 1] * $dimensions[$j + 1];

                    $dp[$i][$j] = min($dp[$i][$j], $cost);
                }
            }
        }

        return $dp[0][$n - 1];
    }

    // Get optimal parenthesization
    public function optimalParenthesization(array $dimensions): string
    {
        $n = count($dimensions) - 1;
        $dp = array_fill(0, $n, array_fill(0, $n, 0));
        $split = array_fill(0, $n, array_fill(0, $n, 0));

        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                $dp[$i][$j] = PHP_INT_MAX;

                for ($k = $i; $k < $j; $k++) {
                    $cost = $dp[$i][$k] + $dp[$k + 1][$j] +
                            $dimensions[$i] * $dimensions[$k + 1] * $dimensions[$j + 1];

                    if ($cost < $dp[$i][$j]) {
                        $dp[$i][$j] = $cost;
                        $split[$i][$j] = $k;
                    }
                }
            }
        }

        return $this->buildParenthesization($split, 0, $n - 1);
    }

    private function buildParenthesization(array $split, int $i, int $j): string
    {
        if ($i === $j) {
            return "M" . ($i + 1);
        }

        $k = $split[$i][$j];
        $left = $this->buildParenthesization($split, $i, $k);
        $right = $this->buildParenthesization($split, $k + 1, $j);

        return "({$left} × {$right})";
    }
}

// Example: Matrices A1(10×20), A2(20×30), A3(30×40), A4(40×30)
$mcm = new MatrixChainMultiplication();
$dimensions = [10, 20, 30, 40, 30];

echo "Min multiplications: " . $mcm->minMultiplications($dimensions) . "\n";  // 30000
echo "Optimal order: " . $mcm->optimalParenthesization($dimensions) . "\n";
// Output: ((M1 × M2) × (M3 × M4))
```

**Why It Works**: The recurrence relation `dp[i][j] = min(dp[i][k] + dp[k+1][j] + cost)` tries all possible split points `k` where we can parenthesize the chain. The cost of multiplying matrices from `i` to `k` and `k+1` to `j`, then multiplying the results, is `dimensions[i] × dimensions[k+1] × dimensions[j+1]`. By filling the table bottom-up (increasing interval length), we ensure all subproblems are solved before computing larger intervals.

### Palindrome Partitioning

Find the minimum number of cuts needed to partition a string into palindromic substrings. This is a classic interval DP problem where we first identify all palindromic substrings, then find the optimal way to partition the string.

**Problem**: Given a string `s`, partition it into the minimum number of palindromic substrings.

```php
# filename: palindrome-partitioning.php
<?php

declare(strict_types=1);

class PalindromePartitioning
{
    // Minimum cuts needed
    public function minCuts(string $s): int
    {
        $n = strlen($s);

        // isPalindrome[i][j] = true if s[i...j] is palindrome
        $isPalindrome = array_fill(0, $n, array_fill(0, $n, false));

        // Fill palindrome table
        for ($i = 0; $i < $n; $i++) {
            $isPalindrome[$i][$i] = true;
        }

        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;

                if ($len === 2) {
                    $isPalindrome[$i][$j] = ($s[$i] === $s[$j]);
                } else {
                    $isPalindrome[$i][$j] = ($s[$i] === $s[$j] && $isPalindrome[$i + 1][$j - 1]);
                }
            }
        }

        // dp[i] = min cuts for s[0...i]
        $dp = array_fill(0, $n, PHP_INT_MAX);

        for ($i = 0; $i < $n; $i++) {
            if ($isPalindrome[0][$i]) {
                $dp[$i] = 0;
            } else {
                for ($j = 0; $j < $i; $j++) {
                    if ($isPalindrome[$j + 1][$i]) {
                        $dp[$i] = min($dp[$i], $dp[$j] + 1);
                    }
                }
            }
        }

        return $dp[$n - 1];
    }

    // Get actual partition
    public function partition(string $s): array
    {
        $n = strlen($s);
        $isPalindrome = array_fill(0, $n, array_fill(0, $n, false));

        for ($i = 0; $i < $n; $i++) {
            $isPalindrome[$i][$i] = true;
        }

        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                if ($len === 2) {
                    $isPalindrome[$i][$j] = ($s[$i] === $s[$j]);
                } else {
                    $isPalindrome[$i][$j] = ($s[$i] === $s[$j] && $isPalindrome[$i + 1][$j - 1]);
                }
            }
        }

        $dp = array_fill(0, $n, PHP_INT_MAX);
        $cutPosition = array_fill(0, $n, -1);

        for ($i = 0; $i < $n; $i++) {
            if ($isPalindrome[0][$i]) {
                $dp[$i] = 0;
            } else {
                for ($j = 0; $j < $i; $j++) {
                    if ($isPalindrome[$j + 1][$i] && $dp[$j] + 1 < $dp[$i]) {
                        $dp[$i] = $dp[$j] + 1;
                        $cutPosition[$i] = $j;
                    }
                }
            }
        }

        // Reconstruct partition
        $result = [];
        $end = $n - 1;
        while ($end >= 0) {
            $start = $cutPosition[$end] + 1;
            $result[] = substr($s, $start, $end - $start + 1);
            $end = $cutPosition[$end];
        }

        return array_reverse($result);
    }
}

// Example
$pp = new PalindromePartitioning();
echo $pp->minCuts("aab") . "\n";  // 1 (aa | b)
echo $pp->minCuts("ababbbabbababa") . "\n";  // 3
print_r($pp->partition("aab"));  // ["aa", "b"]
```

**Why It Works**: We first build a palindrome table `isPalindrome[i][j]` using interval DP - a substring is palindromic if its ends match and the inner substring is palindromic. Then `dp[i]` represents minimum cuts for prefix `s[0...i]`. If `s[0...i]` is already a palindrome, no cuts needed. Otherwise, we try all positions `j` where `s[j+1...i]` is a palindrome and take the minimum cuts.

## Bitmask DP

Bitmask DP uses binary representations to efficiently encode subsets and states. Each bit represents whether an element is included in the subset, allowing us to represent 2ⁿ states with just n bits. This technique is powerful for problems involving subsets, permutations, and state transitions where the state space would otherwise be exponential.

**Key Operations**:

- `mask | (1 << i)` - Add element `i` to subset
- `mask & (1 << i)` - Check if element `i` is in subset
- `mask ^ (1 << i)` - Remove element `i` from subset
- `(1 << n) - 1` - All elements included (all bits set)

### Traveling Salesman Problem (TSP)

Find the shortest route visiting all cities exactly once and returning to the starting city. This classic NP-hard problem becomes tractable for small n (≤ 20) using bitmask DP.

**Problem**: Given distances between n cities, find the minimum cost tour that visits each city exactly once and returns to the start.

```php
# filename: traveling-salesman.php
<?php

declare(strict_types=1);

class TravelingSalesman
{
    private const INF = PHP_INT_MAX;

    // Minimum cost to visit all cities
    public function minCost(array $distances): int
    {
        $n = count($distances);
        $allVisited = (1 << $n) - 1;  // All bits set

        // dp[mask][i] = min cost to reach city i with visited cities in mask
        $dp = array_fill(0, 1 << $n, array_fill(0, $n, self::INF));

        // Start from city 0
        $dp[1][0] = 0;

        for ($mask = 1; $mask <= $allVisited; $mask++) {
            for ($u = 0; $u < $n; $u++) {
                if (!($mask & (1 << $u))) continue;  // City u not in mask
                if ($dp[$mask][$u] === self::INF) continue;

                // Try visiting each unvisited city
                for ($v = 0; $v < $n; $v++) {
                    if ($mask & (1 << $v)) continue;  // Already visited

                    $newMask = $mask | (1 << $v);
                    $dp[$newMask][$v] = min(
                        $dp[$newMask][$v],
                        $dp[$mask][$u] + $distances[$u][$v]
                    );
                }
            }
        }

        // Find minimum cost to visit all cities and return to start
        $minCost = self::INF;
        for ($i = 0; $i < $n; $i++) {
            if ($dp[$allVisited][$i] !== self::INF) {
                $minCost = min($minCost, $dp[$allVisited][$i] + $distances[$i][0]);
            }
        }

        return $minCost === self::INF ? -1 : $minCost;
    }

    // Get actual tour
    public function findTour(array $distances): ?array
    {
        $n = count($distances);
        $allVisited = (1 << $n) - 1;

        $dp = array_fill(0, 1 << $n, array_fill(0, $n, self::INF));
        $parent = array_fill(0, 1 << $n, array_fill(0, $n, -1));

        $dp[1][0] = 0;

        for ($mask = 1; $mask <= $allVisited; $mask++) {
            for ($u = 0; $u < $n; $u++) {
                if (!($mask & (1 << $u))) continue;
                if ($dp[$mask][$u] === self::INF) continue;

                for ($v = 0; $v < $n; $v++) {
                    if ($mask & (1 << $v)) continue;

                    $newMask = $mask | (1 << $v);
                    $newCost = $dp[$mask][$u] + $distances[$u][$v];

                    if ($newCost < $dp[$newMask][$v]) {
                        $dp[$newMask][$v] = $newCost;
                        $parent[$newMask][$v] = $u;
                    }
                }
            }
        }

        // Find best ending city
        $minCost = self::INF;
        $lastCity = -1;
        for ($i = 0; $i < $n; $i++) {
            $totalCost = $dp[$allVisited][$i] + $distances[$i][0];
            if ($totalCost < $minCost) {
                $minCost = $totalCost;
                $lastCity = $i;
            }
        }

        if ($lastCity === -1) return null;

        // Reconstruct tour
        $tour = [$lastCity];
        $mask = $allVisited;
        $current = $lastCity;

        while ($parent[$mask][$current] !== -1) {
            $prev = $parent[$mask][$current];
            array_unshift($tour, $prev);
            $mask ^= (1 << $current);
            $current = $prev;
        }

        $tour[] = 0;  // Return to start
        return $tour;
    }
}

// Example - 4 cities
$tsp = new TravelingSalesman();
$distances = [
    [0, 10, 15, 20],
    [10, 0, 35, 25],
    [15, 35, 0, 30],
    [20, 25, 30, 0]
];

echo "Min cost: " . $tsp->minCost($distances) . "\n";  // 80
$tour = $tsp->findTour($distances);
echo "Tour: " . implode(' → ', $tour) . "\n";  // 0 → 1 → 3 → 2 → 0
```

**Why It Works**: `dp[mask][i]` represents the minimum cost to reach city `i` having visited all cities in `mask`. We iterate through all masks in increasing order, ensuring that when we compute `dp[newMask][v]`, all subproblems for `mask` are already solved. The bitmask efficiently encodes which cities have been visited, reducing state space from factorial to exponential.

### Assignment Problem

Assign n tasks to n workers such that each task is assigned to exactly one worker and each worker gets exactly one task, minimizing the total cost. This is a classic assignment problem solved efficiently with bitmask DP.

**Problem**: Given an n×n cost matrix where `costs[i][j]` is the cost of assigning task `i` to worker `j`, find the minimum cost assignment.

```php
# filename: assignment-problem.php
<?php

declare(strict_types=1);

class AssignmentProblem
{
    // Minimum cost using bitmask DP
    public function minCost(array $costs): int
    {
        $n = count($costs);

        // dp[mask] = min cost to assign first bitcount(mask) tasks
        $dp = array_fill(0, 1 << $n, PHP_INT_MAX);
        $dp[0] = 0;

        for ($mask = 0; $mask < (1 << $n); $mask++) {
            if ($dp[$mask] === PHP_INT_MAX) continue;

            $task = $this->countBits($mask);  // Next task to assign

            for ($worker = 0; $worker < $n; $worker++) {
                if ($mask & (1 << $worker)) continue;  // Worker already assigned

                $newMask = $mask | (1 << $worker);
                $dp[$newMask] = min($dp[$newMask], $dp[$mask] + $costs[$task][$worker]);
            }
        }

        return $dp[(1 << $n) - 1];
    }

    private function countBits(int $mask): int
    {
        $count = 0;
        while ($mask) {
            $count += $mask & 1;
            $mask >>= 1;
        }
        return $count;
    }

    // Get actual assignment
    public function findAssignment(array $costs): array
    {
        $n = count($costs);
        $dp = array_fill(0, 1 << $n, PHP_INT_MAX);
        $choice = array_fill(0, 1 << $n, -1);
        $dp[0] = 0;

        for ($mask = 0; $mask < (1 << $n); $mask++) {
            if ($dp[$mask] === PHP_INT_MAX) continue;

            $task = $this->countBits($mask);

            for ($worker = 0; $worker < $n; $worker++) {
                if ($mask & (1 << $worker)) continue;

                $newMask = $mask | (1 << $worker);
                $newCost = $dp[$mask] + $costs[$task][$worker];

                if ($newCost < $dp[$newMask]) {
                    $dp[$newMask] = $newCost;
                    $choice[$newMask] = $worker;
                }
            }
        }

        // Reconstruct assignment
        $assignment = [];
        $mask = (1 << $n) - 1;

        for ($task = $n - 1; $task >= 0; $task--) {
            $worker = $choice[$mask];
            $assignment[$task] = $worker;
            $mask ^= (1 << $worker);
        }

        return $assignment;
    }
}

// Example - 3 tasks, 3 workers
$ap = new AssignmentProblem();
$costs = [
    [9, 2, 7],   // Task 0 costs for workers 0, 1, 2
    [6, 4, 3],   // Task 1 costs
    [5, 8, 1]    // Task 2 costs
];

echo "Min cost: " . $ap->minCost($costs) . "\n";  // 8 (task0→worker1, task1→worker2, task2→worker0)
$assignment = $ap->findAssignment($costs);
echo "Assignment:\n";
foreach ($assignment as $task => $worker) {
    echo "Task $task → Worker $worker (cost: {$costs[$task][$worker]})\n";
}
```

**Why It Works**: `dp[mask]` represents the minimum cost to assign the first `bitcount(mask)` tasks to workers in `mask`. The number of set bits in `mask` tells us which task we're assigning next. We try assigning this task to each unassigned worker and take the minimum. This reduces the problem from O(n!) brute force to O(2ⁿ × n²) using bitmask DP.

## Multi-Dimensional DP

Multi-dimensional DP extends the basic DP concept to problems with multiple independent dimensions or constraints. The state space becomes multi-dimensional, with each dimension representing a different aspect of the problem. Common applications include string alignment, resource allocation with multiple constraints, and optimization problems with multiple parameters.

### Edit Distance (Levenshtein Distance)

Find the minimum number of operations (insert, delete, replace) needed to convert one string into another. This is a fundamental problem in text processing, bioinformatics, and spell checking.

**Problem**: Given two strings `word1` and `word2`, find the minimum edit distance.

```php
# filename: edit-distance.php
<?php

declare(strict_types=1);

class EditDistance
{
    // Minimum edit distance (insert, delete, replace)
    public function minDistance(string $word1, string $word2): int
    {
        $m = strlen($word1);
        $n = strlen($word2);

        // dp[i][j] = min operations to convert word1[0...i-1] to word2[0...j-1]
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        // Base cases
        for ($i = 0; $i <= $m; $i++) {
            $dp[$i][0] = $i;  // Delete all characters
        }
        for ($j = 0; $j <= $n; $j++) {
            $dp[0][$j] = $j;  // Insert all characters
        }

        // Fill DP table
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($word1[$i - 1] === $word2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1];  // No operation needed
                } else {
                    $dp[$i][$j] = 1 + min(
                        $dp[$i - 1][$j],      // Delete from word1
                        $dp[$i][$j - 1],      // Insert into word1
                        $dp[$i - 1][$j - 1]   // Replace in word1
                    );
                }
            }
        }

        return $dp[$m][$n];
    }

    // Get actual edit operations
    public function getOperations(string $word1, string $word2): array
    {
        $m = strlen($word1);
        $n = strlen($word2);
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 0; $i <= $m; $i++) $dp[$i][0] = $i;
        for ($j = 0; $j <= $n; $j++) $dp[0][$j] = $j;

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($word1[$i - 1] === $word2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1];
                } else {
                    $dp[$i][$j] = 1 + min(
                        $dp[$i - 1][$j],
                        $dp[$i][$j - 1],
                        $dp[$i - 1][$j - 1]
                    );
                }
            }
        }

        // Reconstruct operations
        $operations = [];
        $i = $m;
        $j = $n;

        while ($i > 0 || $j > 0) {
            if ($i === 0) {
                $operations[] = "Insert '{$word2[$j - 1]}'";
                $j--;
            } elseif ($j === 0) {
                $operations[] = "Delete '{$word1[$i - 1]}'";
                $i--;
            } elseif ($word1[$i - 1] === $word2[$j - 1]) {
                $i--;
                $j--;
            } else {
                $delete = $dp[$i - 1][$j];
                $insert = $dp[$i][$j - 1];
                $replace = $dp[$i - 1][$j - 1];

                if ($replace <= $delete && $replace <= $insert) {
                    $operations[] = "Replace '{$word1[$i - 1]}' with '{$word2[$j - 1]}'";
                    $i--;
                    $j--;
                } elseif ($delete <= $insert) {
                    $operations[] = "Delete '{$word1[$i - 1]}'";
                    $i--;
                } else {
                    $operations[] = "Insert '{$word2[$j - 1]}'";
                    $j--;
                }
            }
        }

        return array_reverse($operations);
    }
}

// Example
$ed = new EditDistance();
echo $ed->minDistance("horse", "ros") . "\n";  // 3
print_r($ed->getOperations("horse", "ros"));
// Output: ["Replace 'h' with 'r'", "Delete 'r'", "Delete 'e'"]
```

**Why It Works**: `dp[i][j]` represents the minimum operations to convert `word1[0...i-1]` to `word2[0...j-1]`. If characters match, no operation needed. Otherwise, we take the minimum of: delete from word1, insert into word1, or replace in word1. The base cases handle empty strings (all deletions or all insertions). Space can be optimized to O(min(m,n)) using a rolling array since we only need the previous row.

### Longest Palindromic Subsequence

Find the length of the longest palindromic subsequence in a string. Unlike substring, subsequence doesn't require contiguous characters. This is a classic interval DP problem that demonstrates how to work with subsequences.

**Problem**: Given a string `s`, find the length of the longest palindromic subsequence.

```php
# filename: longest-palindromic-subsequence.php
<?php

declare(strict_types=1);

class LongestPalindromicSubsequence
{
    // Length of longest palindromic subsequence
    public function longestPalindromeSubseq(string $s): int
    {
        $n = strlen($s);

        // dp[i][j] = length of longest palindromic subsequence in s[i...j]
        $dp = array_fill(0, $n, array_fill(0, $n, 0));

        // Base case: single character is palindrome of length 1
        for ($i = 0; $i < $n; $i++) {
            $dp[$i][$i] = 1;
        }

        // Fill table by increasing interval length
        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;

                if ($s[$i] === $s[$j]) {
                    // Characters match: add 2 to inner subsequence
                    if ($len === 2) {
                        $dp[$i][$j] = 2;
                    } else {
                        $dp[$i][$j] = 2 + $dp[$i + 1][$j - 1];
                    }
                } else {
                    // Characters don't match: take max of excluding either end
                    $dp[$i][$j] = max($dp[$i + 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        return $dp[0][$n - 1];
    }

    // Get actual palindromic subsequence
    public function getPalindromeSubseq(string $s): string
    {
        $n = strlen($s);
        $dp = array_fill(0, $n, array_fill(0, $n, 0));

        for ($i = 0; $i < $n; $i++) {
            $dp[$i][$i] = 1;
        }

        for ($len = 2; $len <= $n; $len++) {
            for ($i = 0; $i < $n - $len + 1; $i++) {
                $j = $i + $len - 1;
                if ($s[$i] === $s[$j]) {
                    $dp[$i][$j] = ($len === 2) ? 2 : 2 + $dp[$i + 1][$j - 1];
                } else {
                    $dp[$i][$j] = max($dp[$i + 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Reconstruct subsequence
        $result = '';
        $i = 0;
        $j = $n - 1;

        while ($i <= $j) {
            if ($i === $j) {
                $result .= $s[$i];
                break;
            }

            if ($s[$i] === $s[$j]) {
                $result .= $s[$i];
                $i++;
                $j--;
            } elseif ($dp[$i + 1][$j] > $dp[$i][$j - 1]) {
                $i++;
            } else {
                $j--;
            }
        }

        // Build palindrome: left + reverse(left) or left + middle + reverse(left)
        $left = $result;
        $right = strrev($left);

        if ($i > $j) {
            // Even length: left + reverse(left)
            return $left . $right;
        } else {
            // Odd length: left + middle + reverse(left)
            return $left . $s[$i] . $right;
        }
    }
}

// Example
$lps = new LongestPalindromicSubsequence();
echo $lps->longestPalindromeSubseq("bbbab") . "\n";  // 4 (bbbb or babb)
echo $lps->longestPalindromeSubseq("cbbd") . "\n";   // 2 (bb)
echo $lps->getPalindromeSubseq("bbbab") . "\n";      // bbbb
```

**Why It Works**: `dp[i][j]` represents the length of the longest palindromic subsequence in `s[i...j]`. If `s[i] === s[j]`, we can include both characters and add 2 to the inner subsequence. Otherwise, we take the maximum of excluding either character. This uses interval DP, filling the table by increasing interval length. Time complexity is O(n²) and space can be optimized to O(n) using a rolling array.

### Egg Drop Problem

A classic optimization problem: find the minimum number of trials needed in the worst case to determine the critical floor from which an egg breaks. This problem demonstrates multi-dimensional DP with two constraints: number of eggs and number of floors.

**Problem**: Given `e` eggs and `f` floors, find the minimum trials needed to determine the critical floor in the worst case.

```php
# filename: egg-drop.php
<?php

declare(strict_types=1);

class EggDrop
{
    // Minimum trials in worst case
    public function minTrials(int $eggs, int $floors): int
    {
        // dp[e][f] = min trials with e eggs and f floors
        $dp = array_fill(0, $eggs + 1, array_fill(0, $floors + 1, 0));

        // Base cases
        for ($f = 1; $f <= $floors; $f++) {
            $dp[1][$f] = $f;  // With 1 egg, must try each floor
        }
        for ($e = 1; $e <= $eggs; $e++) {
            $dp[$e][1] = 1;  // With 1 floor, need 1 trial
        }

        // Fill DP table
        for ($e = 2; $e <= $eggs; $e++) {
            for ($f = 2; $f <= $floors; $f++) {
                $dp[$e][$f] = PHP_INT_MAX;

                // Try dropping from each floor
                for ($x = 1; $x <= $f; $x++) {
                    // If egg breaks: try lower floors with e-1 eggs
                    // If egg doesn't break: try higher floors with e eggs
                    $worstCase = 1 + max(
                        $dp[$e - 1][$x - 1],    // Breaks
                        $dp[$e][$f - $x]        // Doesn't break
                    );

                    $dp[$e][$f] = min($dp[$e][$f], $worstCase);
                }
            }
        }

        return $dp[$eggs][$floors];
    }

    // Optimized with binary search
    public function minTrialsOptimized(int $eggs, int $floors): int
    {
        $dp = array_fill(0, $eggs + 1, array_fill(0, $floors + 1, 0));

        for ($f = 1; $f <= $floors; $f++) {
            $dp[1][$f] = $f;
        }
        for ($e = 1; $e <= $eggs; $e++) {
            $dp[$e][1] = 1;
        }

        for ($e = 2; $e <= $eggs; $e++) {
            for ($f = 2; $f <= $floors; $f++) {
                $low = 1;
                $high = $f;
                $minTrials = PHP_INT_MAX;

                // Binary search for optimal floor
                while ($low <= $high) {
                    $mid = (int)(($low + $high) / 2);

                    $breaks = $dp[$e - 1][$mid - 1];
                    $doesntBreak = $dp[$e][$f - $mid];

                    $worstCase = 1 + max($breaks, $doesntBreak);
                    $minTrials = min($minTrials, $worstCase);

                    if ($breaks > $doesntBreak) {
                        $high = $mid - 1;
                    } else {
                        $low = $mid + 1;
                    }
                }

                $dp[$e][$f] = $minTrials;
            }
        }

        return $dp[$eggs][$floors];
    }
}

// Example
$eggDrop = new EggDrop();
echo "Min trials (2 eggs, 10 floors): " . $eggDrop->minTrials(2, 10) . "\n";  // 4
echo "Min trials (2 eggs, 100 floors): " . $eggDrop->minTrialsOptimized(2, 100) . "\n";  // 14
```

**Why It Works**: `dp[e][f]` represents minimum trials with `e` eggs and `f` floors. For each floor `x`, we consider two cases: if the egg breaks, we need `dp[e-1][x-1]` trials for lower floors; if it doesn't break, we need `dp[e][f-x]` trials for upper floors. We take the worst case (maximum) and minimize over all floors. The optimized version uses binary search to find the optimal floor, reducing time from O(e×f²) to O(e×f log f).

## DP on Trees

Dynamic Programming on trees leverages the recursive structure of trees to solve problems efficiently. Since trees have no cycles, we can use DFS to process nodes bottom-up, computing optimal solutions for subtrees before combining them. Common problems include finding diameter, maximum independent set, and maximum path sum.

### Tree Diameter

Find the longest path between any two nodes in a tree. The diameter is the maximum distance between any pair of nodes, which may or may not pass through the root.

**Problem**: Given a tree as an adjacency list, find its diameter.

```php
# filename: tree-dp.php
<?php

declare(strict_types=1);

class TreeDP
{
    private array $dp;
    private int $diameter = 0;

    // Find diameter of tree
    public function findDiameter(array $tree, int $root = 0): int
    {
        $this->dp = [];
        $this->diameter = 0;

        $this->dfs($tree, $root, -1);

        return $this->diameter;
    }

    private function dfs(array $tree, int $node, int $parent): int
    {
        $maxDepth1 = 0;
        $maxDepth2 = 0;

        foreach ($tree[$node] ?? [] as $child) {
            if ($child === $parent) continue;

            $depth = $this->dfs($tree, $child, $node);

            if ($depth > $maxDepth1) {
                $maxDepth2 = $maxDepth1;
                $maxDepth1 = $depth;
            } elseif ($depth > $maxDepth2) {
                $maxDepth2 = $depth;
            }
        }

        // Update diameter (longest path through this node)
        $this->diameter = max($this->diameter, $maxDepth1 + $maxDepth2);

        // Return longest path going down from this node
        return $maxDepth1 + 1;
    }

    // Maximum independent set in tree
    public function maxIndependentSet(array $tree, array $values, int $root = 0): int
    {
        return $this->maxIndependentSetDFS($tree, $values, $root, -1);
    }

    private function maxIndependentSetDFS(
        array $tree,
        array $values,
        int $node,
        int $parent
    ): int {
        // include[node] = max value including this node
        // exclude[node] = max value excluding this node

        $include = $values[$node];
        $exclude = 0;

        foreach ($tree[$node] ?? [] as $child) {
            if ($child === $parent) continue;

            $childValue = $this->maxIndependentSetDFS($tree, $values, $child, $node);

            // If we include current node, can't include children
            // If we exclude current node, can include or exclude children
            $exclude += $childValue;

            // For include, we need to exclude direct children
            // This requires tracking both include/exclude states
        }

        return max($include, $exclude);
    }
}

// Example - Tree as adjacency list
$treeDP = new TreeDP();
$tree = [
    0 => [1, 2],
    1 => [0, 3, 4],
    2 => [0, 5],
    3 => [1],
    4 => [1],
    5 => [2]
];

echo "Tree diameter: " . $treeDP->findDiameter($tree, 0) . "\n";  // 4 (path: 3→1→0→2→5)
```

**Why It Works**: For diameter, we use DFS to compute the longest path going down from each node. The diameter through a node is the sum of the two longest paths from its children. We track `maxDepth1` and `maxDepth2` to find these two longest paths. The global diameter is updated as we traverse. This runs in O(n) time with a single DFS pass.

### Tree Coloring

Color tree nodes with minimum colors such that no two adjacent nodes have the same color. This demonstrates DP on trees with state tracking.

**Problem**: Given a tree, find minimum colors needed such that adjacent nodes have different colors.

```php
# filename: tree-coloring.php
<?php

declare(strict_types=1);

class TreeColoring
{
    private array $memo;

    // Minimum colors needed to color tree
    public function minColors(array $tree, int $root = 0): int
    {
        $this->memo = [];
        return $this->colorTree($tree, $root, -1, -1);
    }

    private function colorTree(array $tree, int $node, int $parent, int $parentColor): int
    {
        $key = "$node:$parentColor";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $minColors = PHP_INT_MAX;

        // Try each possible color for current node
        for ($color = 0; $color < 3; $color++) {
            if ($color === $parentColor) continue;  // Can't use parent's color

            $maxChildColors = 0;
            foreach ($tree[$node] ?? [] as $child) {
                if ($child === $parent) continue;

                $childColors = $this->colorTree($tree, $child, $node, $color);
                $maxChildColors = max($maxChildColors, $childColors);
            }

            // Total colors needed: current color + max from children
            $totalColors = 1 + $maxChildColors;
            $minColors = min($minColors, $totalColors);
        }

        $this->memo[$key] = $minColors;
        return $minColors;
    }

    // Get actual coloring
    public function getColoring(array $tree, int $root = 0): array
    {
        $coloring = [];
        $this->assignColors($tree, $root, -1, -1, $coloring);
        return $coloring;
    }

    private function assignColors(
        array $tree,
        int $node,
        int $parent,
        int $parentColor,
        array &$coloring
    ): void {
        // Find best color for this node
        $bestColor = -1;
        $minMaxChildColors = PHP_INT_MAX;

        for ($color = 0; $color < 3; $color++) {
            if ($color === $parentColor) continue;

            $maxChildColors = 0;
            foreach ($tree[$node] ?? [] as $child) {
                if ($child === $parent) continue;
                $childColors = $this->colorTree($tree, $child, $node, $color);
                $maxChildColors = max($maxChildColors, $childColors);
            }

            if ($maxChildColors < $minMaxChildColors) {
                $minMaxChildColors = $maxChildColors;
                $bestColor = $color;
            }
        }

        $coloring[$node] = $bestColor;

        // Recursively color children
        foreach ($tree[$node] ?? [] as $child) {
            if ($child !== $parent) {
                $this->assignColors($tree, $child, $node, $bestColor, $coloring);
            }
        }
    }
}

// Example
$tc = new TreeColoring();
$tree = [
    0 => [1, 2],
    1 => [0, 3],
    2 => [0],
    3 => [1]
];

echo "Min colors: " . $tc->minColors($tree) . "\n";  // 2
print_r($tc->getColoring($tree));
```

**Why It Works**: For each node, we try all possible colors (excluding parent's color) and recursively compute minimum colors needed for subtrees. We track the maximum colors needed from all children and add 1 for the current node. The memoization key includes the node and parent's color to avoid recomputation. This runs in O(n × c²) where c is the number of colors.

### Tree Matching

Find maximum matching in a tree - a set of edges such that no two edges share a vertex. This demonstrates DP on trees with inclusion/exclusion states.

**Problem**: Given a tree, find the maximum number of edges in a matching (no two edges share a vertex).

```php
# filename: tree-matching.php
<?php

declare(strict_types=1);

class TreeMatching
{
    private array $memo;

    // Maximum matching in tree
    public function maxMatching(array $tree, int $root = 0): int
    {
        $this->memo = [];
        $result = $this->matchTree($tree, $root, -1, false);
        return $result['matched'];
    }

    private function matchTree(
        array $tree,
        int $node,
        int $parent,
        bool $parentMatched
    ): array {
        $key = "$node:$parentMatched";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        // Option 1: Don't match current node with parent
        $notMatched = 0;
        foreach ($tree[$node] ?? [] as $child) {
            if ($child === $parent) continue;
            $childResult = $this->matchTree($tree, $child, $node, false);
            $notMatched += $childResult['matched'];
        }

        // Option 2: Match current node with parent (if parent not already matched)
        $matched = 0;
        if (!$parentMatched && $parent !== -1) {
            $matched = 1;  // Count edge to parent
            foreach ($tree[$node] ?? [] as $child) {
                if ($child === $parent) continue;
                $childResult = $this->matchTree($tree, $child, $node, true);
                $matched += $childResult['matched'];
            }
        }

        $result = ['matched' => max($notMatched, $matched)];
        $this->memo[$key] = $result;
        return $result;
    }
}

// Example
$tm = new TreeMatching();
$tree = [
    0 => [1, 2],
    1 => [0, 3, 4],
    2 => [0],
    3 => [1],
    4 => [1]
];

echo "Max matching: " . $tm->maxMatching($tree) . "\n";  // 2 (edges: 0-1, 3-4)
```

**Why It Works**: For each node, we have two options: match with parent (if parent not matched) or don't match. If we match with parent, we count that edge and children cannot match with current node. If we don't match, children can match with current node. We take the maximum of both options. Memoization key includes whether parent is matched to avoid recomputation.

## DP on DAGs (Directed Acyclic Graphs)

Dynamic Programming on Directed Acyclic Graphs leverages the topological ordering property of DAGs. Since DAGs have no cycles, we can process vertices in topological order, ensuring that when we compute `dp[v]`, all vertices that can reach `v` have already been processed. This makes DP on DAGs more efficient than general graph DP.

**Key Insight**: In a DAG, there exists a topological ordering where all edges point forward. We can process vertices in this order, computing DP values for each vertex based only on its predecessors.

### Longest Path in DAG

Find the longest path from a source vertex to all other vertices in a DAG. This is simpler than general longest path (which is NP-hard) because DAGs have no cycles.

**Problem**: Given a DAG with edge weights, find the longest path from source to each vertex.

```php
# filename: dag-dp.php
<?php

declare(strict_types=1);

class DAGDP
{
    // Longest path from source to all vertices in DAG
    public function longestPath(array $graph, array $weights, int $source): array
    {
        $n = count($graph);

        // Topological sort
        $topoOrder = $this->topologicalSort($graph);

        // dp[v] = longest path from source to v
        $dp = array_fill(0, $n, PHP_INT_MIN);
        $dp[$source] = 0;

        // Process vertices in topological order
        foreach ($topoOrder as $u) {
            if ($dp[$u] === PHP_INT_MIN) continue;  // Unreachable from source

            foreach ($graph[$u] ?? [] as $v) {
                $edgeWeight = $weights["$u:$v"] ?? 0;
                $dp[$v] = max($dp[$v], $dp[$u] + $edgeWeight);
            }
        }

        return $dp;
    }

    // Shortest path from source to all vertices in DAG
    public function shortestPath(array $graph, array $weights, int $source): array
    {
        $n = count($graph);
        $topoOrder = $this->topologicalSort($graph);

        $dp = array_fill(0, $n, PHP_INT_MAX);
        $dp[$source] = 0;

        foreach ($topoOrder as $u) {
            if ($dp[$u] === PHP_INT_MAX) continue;

            foreach ($graph[$u] ?? [] as $v) {
                $edgeWeight = $weights["$u:$v"] ?? 0;
                $dp[$v] = min($dp[$v], $dp[$u] + $edgeWeight);
            }
        }

        return $dp;
    }

    // Count paths from source to target in DAG
    public function countPaths(array $graph, int $source, int $target): int
    {
        $topoOrder = $this->topologicalSort($graph);

        // dp[v] = number of paths from source to v
        $dp = array_fill(0, count($graph), 0);
        $dp[$source] = 1;

        foreach ($topoOrder as $u) {
            foreach ($graph[$u] ?? [] as $v) {
                $dp[$v] += $dp[$u];
            }
        }

        return $dp[$target];
    }

    private function topologicalSort(array $graph): array
    {
        $n = count($graph);
        $inDegree = array_fill(0, $n, 0);

        // Calculate in-degrees
        foreach ($graph as $neighbors) {
            foreach ($neighbors as $v) {
                $inDegree[$v]++;
            }
        }

        // Start with vertices having in-degree 0
        $queue = [];
        for ($i = 0; $i < $n; $i++) {
            if ($inDegree[$i] === 0) {
                $queue[] = $i;
            }
        }

        $result = [];
        while (!empty($queue)) {
            $u = array_shift($queue);
            $result[] = $u;

            foreach ($graph[$u] ?? [] as $v) {
                $inDegree[$v]--;
                if ($inDegree[$v] === 0) {
                    $queue[] = $v;
                }
            }
        }

        return $result;
    }
}

// Example - DAG representing project tasks with dependencies
$dagDP = new DAGDP();
$graph = [
    0 => [1, 2],  // Task 0 depends on tasks 1 and 2
    1 => [3],
    2 => [3],
    3 => [4],
    4 => []
];

$weights = [
    "0:1" => 5,
    "0:2" => 3,
    "1:3" => 2,
    "2:3" => 4,
    "3:4" => 1
];

$longestPaths = $dagDP->longestPath($graph, $weights, 0);
echo "Longest paths from 0:\n";
foreach ($longestPaths as $v => $dist) {
    if ($dist !== PHP_INT_MIN) {
        echo "  To $v: $dist\n";
    }
}

$pathCount = $dagDP->countPaths($graph, 0, 4);
echo "Paths from 0 to 4: $pathCount\n";  // 2 paths
```

**Why It Works**: By processing vertices in topological order, we ensure that when computing `dp[v]`, all predecessors of `v` have already been processed. This allows us to compute longest/shortest paths and count paths efficiently. For longest path, we initialize with `PHP_INT_MIN` and maximize; for shortest path, we initialize with `PHP_INT_MAX` and minimize. Time complexity is O(V + E) for topological sort plus O(E) for DP transitions.

### Critical Path Method (Project Scheduling)

Find the critical path in a project DAG - the longest path that determines minimum project completion time. This is a practical application of longest path in DAGs.

**Problem**: Given tasks with durations and dependencies, find the critical path and minimum completion time.

```php
# filename: critical-path.php
<?php

declare(strict_types=1);

class CriticalPath
{
    // Find critical path and minimum completion time
    public function findCriticalPath(array $tasks, array $durations, array $dependencies): array
    {
        $n = count($tasks);

        // Build graph from dependencies
        $graph = array_fill(0, $n, []);
        foreach ($dependencies as $task => $deps) {
            foreach ($deps as $dep) {
                $graph[$dep][] = $task;
            }
        }

        // Topological sort
        $topoOrder = $this->topologicalSort($graph);

        // dp[v] = earliest start time for task v
        $earliestStart = array_fill(0, $n, 0);

        foreach ($topoOrder as $u) {
            foreach ($graph[$u] ?? [] as $v) {
                $earliestStart[$v] = max(
                    $earliestStart[$v],
                    $earliestStart[$u] + $durations[$u]
                );
            }
        }

        // Latest start times (backward pass)
        $latestStart = array_fill(0, $n, PHP_INT_MAX);
        $maxTime = 0;
        foreach ($earliestStart as $i => $time) {
            $maxTime = max($maxTime, $time + $durations[$i]);
        }

        // Process in reverse topological order
        $reverseOrder = array_reverse($topoOrder);
        foreach ($reverseOrder as $v) {
            if (empty($graph[$v])) {
                $latestStart[$v] = $maxTime - $durations[$v];
            } else {
                $latestStart[$v] = PHP_INT_MAX;
                foreach ($graph[$v] as $u) {
                    $latestStart[$v] = min($latestStart[$v], $latestStart[$u] - $durations[$v]);
                }
            }
        }

        // Critical path: tasks where earliest == latest
        $criticalPath = [];
        foreach ($tasks as $i => $task) {
            if ($earliestStart[$i] === $latestStart[$i]) {
                $criticalPath[] = $task;
            }
        }

        return [
            'min_completion_time' => $maxTime,
            'critical_path' => $criticalPath,
            'earliest_start' => $earliestStart,
            'latest_start' => $latestStart
        ];
    }

    private function topologicalSort(array $graph): array
    {
        $n = count($graph);
        $inDegree = array_fill(0, $n, 0);

        foreach ($graph as $neighbors) {
            foreach ($neighbors as $v) {
                $inDegree[$v]++;
            }
        }

        $queue = [];
        for ($i = 0; $i < $n; $i++) {
            if ($inDegree[$i] === 0) {
                $queue[] = $i;
            }
        }

        $result = [];
        while (!empty($queue)) {
            $u = array_shift($queue);
            $result[] = $u;

            foreach ($graph[$u] ?? [] as $v) {
                $inDegree[$v]--;
                if ($inDegree[$v] === 0) {
                    $queue[] = $v;
                }
            }
        }

        return $result;
    }
}

// Example - Project with tasks
$cp = new CriticalPath();
$tasks = ['Design', 'Code', 'Test', 'Deploy'];
$durations = [5, 10, 3, 2];  // Days
$dependencies = [
    0 => [],           // Design has no dependencies
    1 => [0],          // Code depends on Design
    2 => [1],          // Test depends on Code
    3 => [2]           // Deploy depends on Test
];

$result = $cp->findCriticalPath($tasks, $durations, $dependencies);
echo "Min completion time: {$result['min_completion_time']} days\n";
echo "Critical path: " . implode(' → ', $result['critical_path']) . "\n";
```

**Why It Works**: We compute earliest start times with a forward pass (topological order) and latest start times with a backward pass (reverse topological order). Tasks on the critical path have `earliest_start == latest_start`, meaning any delay will delay the entire project. This is the classic Critical Path Method (CPM) used in project management.

## State Compression Techniques

State compression reduces memory usage by representing states more efficiently. Instead of storing full arrays or complex structures, we use bitmasks, rolling arrays, or other compact representations. This is crucial when dealing with large state spaces or memory constraints.

### Subset Sum with Limited Items

Determine if we can make a target sum using a limited number of each item. This extends the classic subset sum problem by adding quantity constraints.

**Problem**: Given numbers `nums`, counts `counts`, and target `target`, can we make the target sum?

```php
# filename: subset-sum-limited.php
<?php

declare(strict_types=1);

class SubsetSumLimited
{
    // Can we make target sum using given counts of each number?
    public function canMakeSum(array $nums, array $counts, int $target): bool
    {
        $dp = array_fill(0, $target + 1, false);
        $dp[0] = true;  // Base case: sum 0 is always achievable

        for ($i = 0; $i < count($nums); $i++) {
            $num = $nums[$i];
            $count = $counts[$i];

            // Process from right to left to avoid using same item twice
            for ($sum = $target; $sum >= $num; $sum--) {
                // Try using 1, 2, ..., count items
                for ($k = 1; $k <= $count && $k * $num <= $sum; $k++) {
                    if ($dp[$sum - $k * $num]) {
                        $dp[$sum] = true;
                        break;  // Found a way, no need to try more
                    }
                }
            }
        }

        return $dp[$target];
    }

    // Get actual combination that makes the target sum
    public function findCombination(array $nums, array $counts, int $target): ?array
    {
        $n = count($nums);
        $dp = array_fill(0, $target + 1, false);
        $parent = array_fill(0, $target + 1, null);
        $dp[0] = true;

        for ($i = 0; $i < $n; $i++) {
            $num = $nums[$i];
            $count = $counts[$i];

            for ($sum = $target; $sum >= $num; $sum--) {
                for ($k = 1; $k <= $count && $k * $num <= $sum; $k++) {
                    if ($dp[$sum - $k * $num] && !$dp[$sum]) {
                        $dp[$sum] = true;
                        $parent[$sum] = ['num' => $num, 'count' => $k, 'prev' => $sum - $k * $num];
                        break;
                    }
                }
            }
        }

        if (!$dp[$target]) {
            return null;
        }

        // Reconstruct combination
        $combination = [];
        $current = $target;
        while ($current > 0 && $parent[$current] !== null) {
            $info = $parent[$current];
            $combination[] = ['num' => $info['num'], 'count' => $info['count']];
            $current = $info['prev'];
        }

        return $combination;
    }
}

// Example
$ssl = new SubsetSumLimited();
$nums = [3, 5, 7];
$counts = [2, 1, 3];

echo $ssl->canMakeSum($nums, $counts, 11) ? "Yes\n" : "No\n";  // Yes (3+3+5)
$combo = $ssl->findCombination($nums, $counts, 11);
if ($combo) {
    echo "Combination: ";
    foreach ($combo as $item) {
        echo "{$item['count']}×{$item['num']} ";
    }
    echo "\n";  // Output: Combination: 1×5 2×3
}
```

**Why It Works**: We use a 1D DP array `dp[sum]` indicating if sum `sum` is achievable. For each number with its count, we process from right to left to avoid using the same item multiple times in one iteration. This is more efficient than the naive approach of trying all combinations. The right-to-left iteration ensures that when we check `dp[sum - k*num]`, we're using values from previous items, not the current item being processed.

## Complexity Analysis

| Problem              | States  | Transitions      | Time                    | Space       |
| -------------------- | ------- | ---------------- | ----------------------- | ----------- |
| Matrix Chain         | O(n²)   | O(n)             | O(n³)                   | O(n²)       |
| Palindrome Partition | O(n²)   | O(n)             | O(n²)                   | O(n²)       |
| TSP                  | O(2ⁿ×n) | O(n)             | O(2ⁿ×n²)                | O(2ⁿ×n)     |
| Edit Distance        | O(m×n)  | O(1)             | O(m×n)                  | O(min(m,n)) |
| Egg Drop             | O(e×f)  | O(f) or O(log f) | O(e×f²) or O(e×f log f) | O(e×f)      |

## Best Practices

1. **Identify State Carefully**

   - State should contain all necessary information
   - Minimize state dimensions for efficiency

2. **Use Bitmasks for Subsets**

   - Efficient for problems with small n (≤ 20)
   - Fast bitwise operations

3. **Optimize Space**

   - Use rolling arrays when only previous row/column needed
   - State compression techniques

4. **Memoization for Complex Recurrence**
   - Easier to code for complex state transitions
   - Only computes needed states

## Practice Exercises

### Exercise 1: Burst Balloons

**Goal**: Apply interval DP to maximize coins from bursting balloons

**Problem**: Given balloons with values, burst them optimally to maximize coins. When you burst balloon `i`, you get `nums[left] × nums[i] × nums[right]` coins.

**Requirements**:

- Use interval DP with state `dp[i][j]` = max coins from bursting balloons in range `[i, j]`
- Handle boundary cases (balloons at edges)
- Return maximum coins achievable

**Validation**: Test with `[3, 1, 5, 8]` - expected result is 167

### Exercise 2: Boolean Parenthesization

**Goal**: Count ways to parenthesize boolean expression to get true

**Problem**: Given boolean expression with operators (`&`, `|`, `^`), count ways to parenthesize to get `true`.

**Requirements**:

- Use interval DP to process subexpressions
- Track both true and false counts for each subexpression
- Handle all three operators correctly

**Validation**: Test with `"T|T&F^T"` - verify correct count of true evaluations

### Exercise 3: Optimal Binary Search Tree

**Goal**: Build BST with minimum expected search cost

**Problem**: Given keys and their search frequencies, build BST to minimize total search cost.

**Requirements**:

- Use interval DP to try all possible roots
- Calculate cost correctly: `frequency × depth`
- Return minimum total cost

**Validation**: Test with keys `[10, 12, 20]` and frequencies `[34, 8, 50]` - verify optimal cost

### Exercise 4: Maximum Profit Job Scheduling

**Goal**: Select non-overlapping jobs for maximum profit

**Problem**: Given jobs with start time, end time, and profit, select jobs to maximize total profit.

**Requirements**:

- Use DP with binary search to find last non-conflicting job
- Sort jobs by end time
- Return maximum profit achievable

**Validation**: Test with overlapping jobs - verify optimal selection

### Exercise 5: Minimum Cost Tree from Leaves

**Goal**: Build tree with given leaf values, minimize sum

**Problem**: Given array of leaf values, build binary tree where each internal node equals product of its children. Minimize sum of all node values.

**Requirements**:

- Use interval DP to try all possible ways to combine leaves
- Track minimum cost for each interval
- Return minimum total cost

**Validation**: Test with `[6, 2, 4]` - verify correct tree structure and cost

## Digit DP

Digit DP solves counting problems involving numbers with specific digit properties. Instead of iterating through all numbers (which can be exponential), we process digits one by one, tracking constraints like digit sum, digit patterns, or other properties. This technique is essential for problems like "count numbers in range [L, R] with property X".

**Key Concepts**:

- **Tight constraint**: Whether we're still matching the upper bound digit-by-digit
- **Started flag**: Whether we've started building the number (handles leading zeros)
- **State**: Additional constraints (sum, last digit, etc.)

### Count Numbers with Digit Sum

Count numbers from 1 to n where the sum of digits equals a target value. This demonstrates the core digit DP pattern.

```php
# filename: digit-dp.php
<?php

declare(strict_types=1);

class DigitDP
{
    private array $memo;

    // Count numbers from 1 to n with digit sum equal to target
    public function countWithDigitSum(int $n, int $targetSum): int
    {
        $this->memo = [];
        $digits = str_split((string)$n);
        return $this->solve($digits, 0, 0, $targetSum, true, false);
    }

    private function solve(
        array $digits,
        int $pos,
        int $currentSum,
        int $targetSum,
        bool $tight,
        bool $started
    ): int {
        // Base case
        if ($pos === count($digits)) {
            return $started && $currentSum === $targetSum ? 1 : 0;
        }

        // Memoization
        $key = "$pos:$currentSum:$tight:$started";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $limit = $tight ? (int)$digits[$pos] : 9;
        $result = 0;

        for ($digit = 0; $digit <= $limit; $digit++) {
            if (!$started && $digit === 0) {
                // Leading zeros
                $result += $this->solve(
                    $digits,
                    $pos + 1,
                    0,
                    $targetSum,
                    false,
                    false
                );
            } else {
                $result += $this->solve(
                    $digits,
                    $pos + 1,
                    $currentSum + $digit,
                    $targetSum,
                    $tight && ($digit === $limit),
                    true
                );
            }
        }

        $this->memo[$key] = $result;
        return $result;
    }

    // Count numbers without consecutive repeating digits
    public function countWithoutConsecutiveDigits(int $n): int
    {
        $this->memo = [];
        $digits = str_split((string)$n);
        return $this->solveConsecutive($digits, 0, -1, true, false);
    }

    private function solveConsecutive(
        array $digits,
        int $pos,
        int $lastDigit,
        bool $tight,
        bool $started
    ): int {
        if ($pos === count($digits)) {
            return $started ? 1 : 0;
        }

        $key = "$pos:$lastDigit:$tight:$started";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $limit = $tight ? (int)$digits[$pos] : 9;
        $result = 0;

        for ($digit = 0; $digit <= $limit; $digit++) {
            if (!$started && $digit === 0) {
                $result += $this->solveConsecutive(
                    $digits,
                    $pos + 1,
                    -1,
                    false,
                    false
                );
            } elseif ($digit !== $lastDigit) {
                $result += $this->solveConsecutive(
                    $digits,
                    $pos + 1,
                    $digit,
                    $tight && ($digit === $limit),
                    true
                );
            }
        }

        $this->memo[$key] = $result;
        return $result;
    }
}

// Example
$digitDP = new DigitDP();
echo $digitDP->countWithDigitSum(100, 10) . "\n";  // Numbers 1-100 with digit sum = 10
echo $digitDP->countWithoutConsecutiveDigits(1000) . "\n";  // Numbers without consecutive same digits
```

**Why It Works**: We process digits from left to right, maintaining state: position, current sum, tight constraint (whether matching upper bound), and started flag. The tight constraint ensures we don't exceed the upper bound. Memoization caches results for `(position, sum, tight, started)` states, avoiding recomputation. This reduces complexity from exponential to polynomial in the number of digits.

## Probability DP

Probability DP handles problems involving probabilistic states, expected values, and stochastic processes. Instead of deterministic transitions, we compute expected values by averaging over all possible outcomes weighted by their probabilities. Common applications include game theory, random walks, and stochastic optimization.

### Expected Steps Random Walk

Calculate the expected number of steps to reach a target position in a random walk. Each step has a probability distribution, and we need to compute the expected value.

**Problem**: Starting at position 0, what's the expected steps to reach position `n` if each step has 50% chance to move right and 50% to move left (bounded by 0 and n)?

```php
# filename: probability-dp.php
<?php

declare(strict_types=1);

class ProbabilityDP
{
    private array $memo;

    // Expected steps to reach position n starting from position 0
    // Each step: 50% chance move right, 50% chance move left (bounded by 0 and n)
    public function expectedSteps(int $target): float
    {
        $this->memo = [];
        return $this->solve(0, $target);
    }

    private function solve(int $current, int $target): float
    {
        if ($current === $target) {
            return 0.0;
        }

        if (isset($this->memo[$current])) {
            return $this->memo[$current];
        }

        $expected = 1.0;  // Current step

        if ($current === 0) {
            // Can only move right
            $expected += $this->solve($current + 1, $target);
        } elseif ($current === $target - 1) {
            // Can move left or right
            $expected += 0.5 * $this->solve($current - 1, $target);
            $expected += 0.5 * $this->solve($current + 1, $target);
        } else {
            // Can move left or right
            $expected += 0.5 * $this->solve($current - 1, $target);
            $expected += 0.5 * $this->solve($current + 1, $target);
        }

        $this->memo[$current] = $expected;
        return $expected;
    }

    // Dice game: Expected score rolling n dice, can stop anytime
    public function expectedDiceScore(int $remainingRolls, float $currentScore = 0): float
    {
        if ($remainingRolls === 0) {
            return $currentScore;
        }

        $key = "$remainingRolls:$currentScore";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        // Expected value if we roll
        $expectedRoll = 0.0;
        for ($face = 1; $face <= 6; $face++) {
            $expectedRoll += (1.0 / 6.0) * $this->expectedDiceScore(
                $remainingRolls - 1,
                $currentScore + $face
            );
        }

        // Best choice: stop now or continue rolling
        $result = max($currentScore, $expectedRoll);

        $this->memo[$key] = $result;
        return $result;
    }
}

// Example
$probDP = new ProbabilityDP();
echo "Expected steps to reach position 10: " . $probDP->expectedSteps(10) . "\n";
echo "Expected dice score with 5 rolls: " . $probDP->expectedDiceScore(5) . "\n";
```

**Why It Works**: For expected values, we use the linearity of expectation: `E[X] = Σ P(outcome) × E[outcome]`. Each state's expected value is computed as 1 (current step) plus the weighted average of expected values from reachable states. Memoization ensures we don't recompute the same states. The dice game demonstrates optimal stopping - we choose between stopping now or continuing based on expected future value.

## Matrix Exponentiation for Linear Recurrences

Matrix exponentiation allows us to compute linear recurrences in O(log n) time instead of O(n). This technique is crucial for problems like computing the nth Fibonacci number, solving linear recurrence relations, and counting paths in graphs.

**Key Insight**: Linear recurrences can be expressed as matrix multiplication. By computing the nth power of a transformation matrix using fast exponentiation, we can compute the nth term in logarithmic time.

**When to Use**:

- Computing nth term of linear recurrence (Fibonacci, Tribonacci, etc.)
- Counting paths in graphs
- Problems with recurrence relations that can be matrix-formulated

### Fibonacci with Matrix Exponentiation

Compute the nth Fibonacci number in O(log n) time using matrix exponentiation.

```php
# filename: matrix-exponentiation.php
<?php

declare(strict_types=1);

class MatrixExponentiation
{
    // Multiply two 2x2 matrices
    private function multiply(array $a, array $b): array
    {
        return [
            [
                $a[0][0] * $b[0][0] + $a[0][1] * $b[1][0],
                $a[0][0] * $b[0][1] + $a[0][1] * $b[1][1]
            ],
            [
                $a[1][0] * $b[0][0] + $a[1][1] * $b[1][0],
                $a[1][0] * $b[0][1] + $a[1][1] * $b[1][1]
            ]
        ];
    }

    // Fast matrix exponentiation
    private function power(array $matrix, int $n): array
    {
        if ($n === 1) {
            return $matrix;
        }

        if ($n % 2 === 0) {
            $half = $this->power($matrix, $n / 2);
            return $this->multiply($half, $half);
        } else {
            return $this->multiply($matrix, $this->power($matrix, $n - 1));
        }
    }

    // Fibonacci using matrix exponentiation: O(log n)
    public function fibonacci(int $n): int
    {
        if ($n <= 1) {
            return $n;
        }

        // Transformation matrix: [F(n+1), F(n)] = [F(n), F(n-1)] × [[1,1],[1,0]]
        $transform = [
            [1, 1],
            [1, 0]
        ];

        // Compute transform^(n-1)
        $result = $this->power($transform, $n - 1);

        // [F(n), F(n-1)] = [1, 0] × transform^(n-1)
        return $result[0][0];
    }

    // General linear recurrence: a(n) = c1*a(n-1) + c2*a(n-2) + ... + ck*a(n-k)
    public function linearRecurrence(array $coefficients, array $initial, int $n): int
    {
        $k = count($coefficients);
        if ($n < $k) {
            return $initial[$n];
        }

        // Build transformation matrix
        $transform = array_fill(0, $k, array_fill(0, $k, 0));
        $transform[0] = $coefficients;  // First row is coefficients

        for ($i = 1; $i < $k; $i++) {
            $transform[$i][$i - 1] = 1;  // Identity-like structure
        }

        // Compute transform^(n-k+1)
        $power = $this->power($transform, $n - $k + 1);

        // Result = initial values × power matrix
        $result = 0;
        for ($i = 0; $i < $k; $i++) {
            $result += $initial[$k - 1 - $i] * $power[0][$i];
        }

        return $result;
    }

    // Count paths of length k in graph using adjacency matrix exponentiation
    public function countPaths(array $graph, int $from, int $to, int $length): int
    {
        $n = count($graph);
        $matrix = $graph;

        // Compute graph^length
        $result = $matrix;
        for ($i = 1; $i < $length; $i++) {
            $result = $this->multiplyGraphs($result, $matrix, $n);
        }

        return $result[$from][$to];
    }

    private function multiplyGraphs(array $a, array $b, int $n): array
    {
        $result = array_fill(0, $n, array_fill(0, $n, 0));
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                for ($k = 0; $k < $n; $k++) {
                    $result[$i][$j] += $a[$i][$k] * $b[$k][$j];
                }
            }
        }
        return $result;
    }
}

// Example
$me = new MatrixExponentiation();
echo "Fibonacci(10): " . $me->fibonacci(10) . "\n";  // 55
echo "Fibonacci(50): " . $me->fibonacci(50) . "\n";  // 12586269025

// Tribonacci: T(n) = T(n-1) + T(n-2) + T(n-3)
$tribonacci = $me->linearRecurrence([1, 1, 1], [0, 0, 1], 10);
echo "Tribonacci(10): $tribonacci\n";
```

**Why It Works**: The Fibonacci recurrence `F(n) = F(n-1) + F(n-2)` can be written as `[F(n), F(n-1)] = [F(n-1), F(n-2)] × [[1,1],[1,0]]`. By computing the nth power of the transformation matrix using fast exponentiation (divide and conquer), we get O(log n) time complexity instead of O(n). This technique generalizes to any linear recurrence relation.

## Convex Hull Optimization

Convex Hull Trick (CHT) optimizes certain DP recurrence relations from O(n²) to O(n log n) by maintaining a convex hull of linear functions. This advanced technique applies when the recurrence has the form `dp[i] = min/max(a[j] × x[i] + b[j] + dp[j])` for some linear functions.

**When to Use**: When transitions involve linear functions and you can maintain a convex hull of these functions to quickly find the optimal transition.

### Building Factories Problem

Place k factories at optimal positions to minimize total cost, where cost depends linearly on distance and production rates. This demonstrates convex hull optimization.

**Problem**: Given positions and production rates, place k factories to minimize total cost (distance × rate).

```php
# filename: convex-hull-optimization.php
<?php

declare(strict_types=1);

class ConvexHullOptimization
{
    // Build n factories at positions, minimize total cost
    // Cost = distance * production_rate
    public function minCost(array $positions, array $rates, int $k): int
    {
        $n = count($positions);

        // dp[i][j] = min cost to build i factories using first j positions
        $dp = array_fill(0, $k + 1, array_fill(0, $n + 1, PHP_INT_MAX));
        $dp[0][0] = 0;

        for ($i = 1; $i <= $k; $i++) {
            for ($j = $i; $j <= $n; $j++) {
                // Try placing i-th factory at position j
                for ($m = $i - 1; $m < $j; $m++) {
                    if ($dp[$i - 1][$m] === PHP_INT_MAX) continue;

                    $cost = $dp[$i - 1][$m];
                    // Add cost of factory at position j serving positions m+1 to j
                    for ($p = $m + 1; $p <= $j; $p++) {
                        $cost += abs($positions[$p - 1] - $positions[$j - 1]) * $rates[$p - 1];
                    }

                    $dp[$i][$j] = min($dp[$i][$j], $cost);
                }
            }
        }

        return $dp[$k][$n];
    }
}

// Note: Full convex hull optimization implementation would use a deque
// to maintain the convex hull of linear functions for O(n log n) transitions
```

**Why It Works**: The naive approach tries all previous positions for each factory placement, giving O(n²) transitions. Convex hull optimization recognizes that the cost function forms linear functions, and we can maintain a convex hull to find the optimal transition point in O(log n) time using binary search, reducing overall complexity to O(n log n).

## Profile DP (Broken Profile)

Profile DP solves grid-based problems by processing row-by-row and maintaining a "profile" (bitmask) representing the state of the current row's boundary with the next row. This technique is powerful for tiling problems, connectivity problems, and grid-based constraints.

**Key Concept**: The profile encodes which cells in the current row are "filled" or "connected" to the next row, allowing us to ensure valid transitions between rows.

### Domino Tiling

Count the number of ways to tile an n×m grid with 1×2 dominoes. This classic problem demonstrates profile DP where the profile represents which cells in the current row are covered by vertical dominoes extending to the next row.

**Problem**: Count ways to tile n×m grid with 1×2 dominoes (can be placed horizontally or vertically).

```php
# filename: profile-dp.php
<?php

declare(strict_types=1);

class ProfileDP
{
    private array $memo;
    private int $cols;

    // Count ways to tile n×m grid with 1×2 dominoes
    public function countTilings(int $rows, int $cols): int
    {
        $this->cols = $cols;
        $this->memo = [];
        return $this->solve(0, 0, $rows);
    }

    private function solve(int $row, int $mask, int $rows): int
    {
        if ($row === $rows) {
            return $mask === 0 ? 1 : 0;
        }

        $key = "$row:$mask";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $nextMask = $mask;
        $result = $this->fillRow(0, $row, $mask, $nextMask, $rows);

        $this->memo[$key] = $result;
        return $result;
    }

    private function fillRow(int $col, int $row, int $curMask, int $nextMask, int $rows): int
    {
        if ($col === $this->cols) {
            return $this->solve($row + 1, $nextMask, $rows);
        }

        $result = 0;

        // Current cell filled by previous row
        if ($curMask & (1 << $col)) {
            $result += $this->fillRow(
                $col + 1,
                $row,
                $curMask,
                $nextMask,
                $rows
            );
        } else {
            // Place vertical domino
            if ($row + 1 < $rows) {
                $result += $this->fillRow(
                    $col + 1,
                    $row,
                    $curMask | (1 << $col),
                    $nextMask | (1 << $col),
                    $rows
                );
            }

            // Place horizontal domino
            if ($col + 1 < $this->cols && !($curMask & (1 << ($col + 1)))) {
                $result += $this->fillRow(
                    $col + 2,
                    $row,
                    $curMask | (1 << $col) | (1 << ($col + 1)),
                    $nextMask,
                    $rows
                );
            }
        }

        return $result;
    }
}

// Example
$profileDP = new ProfileDP();
echo "Tilings of 3×2 grid: " . $profileDP->countTilings(3, 2) . "\n";  // 3
echo "Tilings of 4×3 grid: " . $profileDP->countTilings(4, 3) . "\n";  // 11
```

**Why It Works**: We process the grid row by row. The mask represents which columns in the current row are covered by vertical dominoes extending to the next row. For each cell, we can: skip if already covered, place a vertical domino (if not last row), or place a horizontal domino (if next cell available). The profile ensures valid transitions - all cells must be covered and no invalid overlaps occur. Complexity is O(n × 4^m) which is feasible for small m.

## DP with State Machine

State machine DP models problems as a finite state machine where transitions between states represent decisions or events. This technique is powerful for problems involving pattern matching, string processing, and problems with complex state transitions.

**Key Concept**: Define states representing different conditions or positions in a process, then use DP to count paths or compute optimal values through the state machine.

**When to Use**:

- Pattern matching problems (regex, string matching)
- Problems with multiple states or modes
- Counting problems with state-dependent transitions

### Regular Expression Matching

Match a string against a pattern with `.` (any character) and `*` (zero or more of preceding element) using state machine DP.

**Problem**: Given string `s` and pattern `p`, determine if `s` matches `p` where `.` matches any character and `*` matches zero or more of the preceding element.

```php
# filename: state-machine-dp.php
<?php

declare(strict_types=1);

class StateMachineDP
{
    // Regular expression matching with . and *
    public function isMatch(string $s, string $p): bool
    {
        $m = strlen($s);
        $n = strlen($p);

        // dp[i][j] = does s[0...i-1] match p[0...j-1]
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, false));
        $dp[0][0] = true;  // Empty string matches empty pattern

        // Handle patterns like a*, a*b*, etc. matching empty string
        for ($j = 1; $j < $n; $j++) {
            if ($p[$j] === '*') {
                $dp[0][$j + 1] = $dp[0][$j - 1];
            }
        }

        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($p[$j] === '.' || $s[$i] === $p[$j]) {
                    // Match single character
                    $dp[$i + 1][$j + 1] = $dp[$i][$j];
                } elseif ($p[$j] === '*') {
                    // * can match zero or more of preceding character
                    $dp[$i + 1][$j + 1] = $dp[$i + 1][$j - 1];  // Zero matches

                    if ($p[$j - 1] === '.' || $p[$j - 1] === $s[$i]) {
                        // One or more matches
                        $dp[$i + 1][$j + 1] = $dp[$i + 1][$j + 1] || $dp[$i][$j + 1];
                    }
                }
            }
        }

        return $dp[$m][$n];
    }

    // Count ways to decode string where A=1, B=2, ..., Z=26
    public function numDecodings(string $s): int
    {
        $n = strlen($s);
        if ($n === 0 || $s[0] === '0') {
            return 0;
        }

        // dp[i] = ways to decode s[0...i-1]
        $dp = array_fill(0, $n + 1, 0);
        $dp[0] = 1;  // Empty string has 1 way
        $dp[1] = 1;  // Single digit (non-zero) has 1 way

        for ($i = 2; $i <= $n; $i++) {
            $oneDigit = (int)$s[$i - 1];
            $twoDigit = (int)substr($s, $i - 2, 2);

            // Can decode as single digit
            if ($oneDigit >= 1 && $oneDigit <= 9) {
                $dp[$i] += $dp[$i - 1];
            }

            // Can decode as two digits
            if ($twoDigit >= 10 && $twoDigit <= 26) {
                $dp[$i] += $dp[$i - 2];
            }
        }

        return $dp[$n];
    }

    // Buy and sell stock with state machine (hold, sold, cooldown)
    public function maxProfitWithCooldown(array $prices): int
    {
        $n = count($prices);
        if ($n <= 1) {
            return 0;
        }

        // States: 0 = hold, 1 = sold (just sold), 2 = cooldown (can buy)
        $hold = -$prices[0];  // Buy first stock
        $sold = 0;
        $cooldown = 0;

        for ($i = 1; $i < $n; $i++) {
            $newHold = max($hold, $cooldown - $prices[$i]);  // Keep holding or buy
            $newSold = $hold + $prices[$i];  // Sell
            $newCooldown = max($cooldown, $sold);  // Stay in cooldown or enter from sold

            $hold = $newHold;
            $sold = $newSold;
            $cooldown = $newCooldown;
        }

        return max($sold, $cooldown);
    }
}

// Example
$sm = new StateMachineDP();
echo $sm->isMatch("aa", "a") ? "Match\n" : "No match\n";  // false
echo $sm->isMatch("aa", "a*") ? "Match\n" : "No match\n";  // true
echo $sm->isMatch("ab", ".*") ? "Match\n" : "No match\n";  // true

echo "Decodings of '226': " . $sm->numDecodings("226") . "\n";  // 3 (BZ, VF, BBF)

$prices = [1, 2, 3, 0, 2];
echo "Max profit with cooldown: " . $sm->maxProfitWithCooldown($prices) . "\n";  // 3
```

**Why It Works**: State machine DP tracks the current state (position in pattern, holding stock, etc.) and uses DP to compute optimal values or counts for each state. For regex matching, states represent positions in both string and pattern. Transitions handle character matches, wildcards, and repetition. The state machine approach makes complex transition logic clear and efficient.

## DP with Deque Optimization

Deque optimization uses a double-ended queue to maintain optimal candidates for DP transitions in sliding window problems. Instead of checking all previous states, we maintain a deque of candidates sorted by their values, allowing O(1) amortized updates and queries.

**When to Use**: When DP transitions involve finding min/max over a sliding window of previous states, and the window moves monotonically.

### Sliding Window Maximum Sum

Find the maximum sum of a subarray with length at least k, or with operations allowed. This demonstrates deque optimization for maintaining optimal prefix sums.

**Problem**: Find maximum sum subarray with length ≥ k.

```php
# filename: deque-optimization-dp.php
<?php

declare(strict_types=1);

class DequeOptimizationDP
{
    // Maximum sum of k consecutive elements with at most m operations
    public function maxSumWithOperations(array $arr, int $k, int $operations): int
    {
        $n = count($arr);

        // dp[i][j] = max sum ending at position i with j operations used
        $dp = array_fill(0, $n, array_fill(0, $operations + 1, PHP_INT_MIN));
        $dp[0][0] = $arr[0];
        $dp[0][1] = $arr[0] * 2;  // One operation: double the value

        for ($i = 1; $i < $n; $i++) {
            for ($j = 0; $j <= $operations; $j++) {
                // Don't use operation on current element
                $dp[$i][$j] = $arr[$i];
                if ($i >= 1 && $dp[$i - 1][$j] !== PHP_INT_MIN) {
                    $dp[$i][$j] = max($dp[$i][$j], $dp[$i - 1][$j] + $arr[$i]);
                }

                // Use operation on current element
                if ($j > 0) {
                    $dp[$i][$j] = max($dp[$i][$j], $arr[$i] * 2);
                    if ($i >= 1 && $dp[$i - 1][$j - 1] !== PHP_INT_MIN) {
                        $dp[$i][$j] = max($dp[$i][$j], $dp[$i - 1][$j - 1] + $arr[$i] * 2);
                    }
                }
            }
        }

        $maxSum = PHP_INT_MIN;
        for ($i = $k - 1; $i < $n; $i++) {
            for ($j = 0; $j <= $operations; $j++) {
                $maxSum = max($maxSum, $dp[$i][$j]);
            }
        }

        return $maxSum;
    }

    // Maximum sum subarray with length at least k using deque
    public function maxSumAtLeastK(array $arr, int $k): int
    {
        $n = count($arr);

        // Prefix sum
        $prefix = [0];
        for ($i = 0; $i < $n; $i++) {
            $prefix[] = $prefix[$i] + $arr[$i];
        }

        $maxSum = PHP_INT_MIN;
        $deque = new \SplDoublyLinkedList();

        for ($i = $k; $i <= $n; $i++) {
            // Add previous position to deque
            $prevPos = $i - $k;

            // Remove elements from back that are greater than current
            while (!$deque->isEmpty() && $prefix[$deque->top()] >= $prefix[$prevPos]) {
                $deque->pop();
            }
            $deque->push($prevPos);

            // Remove elements from front that are out of range
            while (!$deque->isEmpty() && $deque->bottom() < $i - $n) {
                $deque->shift();
            }

            // Maximum sum ending at position i with length >= k
            if (!$deque->isEmpty()) {
                $maxSum = max($maxSum, $prefix[$i] - $prefix[$deque->bottom()]);
            }
        }

        return $maxSum;
    }
}

// Example
$dequeDP = new DequeOptimizationDP();
$arr = [1, -2, 3, 4, -5, 8];
echo "Max sum with 2 ops: " . $dequeDP->maxSumWithOperations($arr, 3, 2) . "\n";
echo "Max sum length >= 3: " . $dequeDP->maxSumAtLeastK($arr, 3) . "\n";
```

**Why It Works**: For the sliding window problem, we use prefix sums. To maximize `prefix[i] - prefix[j]` for `j` in a valid range, we want to minimize `prefix[j]`. The deque maintains indices of prefix sums in increasing order. We remove indices that are out of range from the front, and remove indices with larger prefix sums from the back (since they're worse candidates). This gives O(n) time instead of O(n²).

## Advanced DP Optimizations

Advanced optimization techniques reduce both time and space complexity of DP solutions. Space optimization is crucial when dealing with large inputs, while algorithmic optimizations like binary search can reduce time complexity significantly.

### Space Optimization Techniques

Reduce space complexity by recognizing that we don't need to store all previous states. Common techniques include rolling arrays (for 2D DP where only previous row/column needed) and binary search optimization (for problems with monotonic properties).

```php
# filename: space-optimization.php
<?php

declare(strict_types=1);

class SpaceOptimization
{
    // Standard DP: O(n²) space
    public function longestIncreasingSubsequenceStandard(array $arr): int
    {
        $n = count($arr);
        $dp = array_fill(0, $n, 1);

        for ($i = 1; $i < $n; $i++) {
            for ($j = 0; $j < $i; $j++) {
                if ($arr[$j] < $arr[$i]) {
                    $dp[$i] = max($dp[$i], $dp[$j] + 1);
                }
            }
        }

        return max($dp);
    }

    // Optimized: O(n log n) using binary search
    public function longestIncreasingSubsequenceOptimized(array $arr): int
    {
        $tails = [];

        foreach ($arr as $num) {
            $left = 0;
            $right = count($tails);

            // Binary search for position
            while ($left < $right) {
                $mid = (int)(($left + $right) / 2);
                if ($tails[$mid] < $num) {
                    $left = $mid + 1;
                } else {
                    $right = $mid;
                }
            }

            if ($left === count($tails)) {
                $tails[] = $num;
            } else {
                $tails[$left] = $num;
            }
        }

        return count($tails);
    }

    // 2D DP with rolling array
    public function uniquePathsOptimized(int $m, int $n): int
    {
        // Instead of m×n array, use 1×n array
        $dp = array_fill(0, $n, 1);

        for ($i = 1; $i < $m; $i++) {
            for ($j = 1; $j < $n; $j++) {
                $dp[$j] += $dp[$j - 1];
            }
        }

        return $dp[$n - 1];
    }
}

// Example
$spaceOpt = new SpaceOptimization();
$arr = [10, 9, 2, 5, 3, 7, 101, 18];
echo "LIS (standard): " . $spaceOpt->longestIncreasingSubsequenceStandard($arr) . "\n";  // 4
echo "LIS (optimized): " . $spaceOpt->longestIncreasingSubsequenceOptimized($arr) . "\n";  // 4
echo "Unique paths 3×7: " . $spaceOpt->uniquePathsOptimized(3, 7) . "\n";  // 28
```

**Why It Works**:

- **Rolling Array**: For 2D DP where `dp[i][j]` only depends on `dp[i-1][...]`, we can use a 1D array and update it in-place, reducing space from O(m×n) to O(n).
- **LIS Binary Search**: Instead of O(n²) DP, we maintain `tails[i]` = smallest tail of all increasing subsequences of length `i+1`. This array is always sorted, allowing binary search for O(n log n) time and O(n) space.

## Production DP Patterns

Real-world applications of advanced DP often involve multiple constraints, complex state spaces, and practical considerations like capacity limits, ordering costs, and holding costs. These problems demonstrate how advanced DP techniques solve actual business optimization challenges.

### Real-World Inventory Optimization

Minimize inventory costs by optimizing when and how much to order, considering fixed ordering costs, holding costs, demand forecasts, and warehouse capacity. This is a practical application of DP with multiple constraints.

**Problem**: Given daily demand, ordering cost, holding cost, and capacity, find the optimal ordering schedule to minimize total cost.

```php
# filename: inventory-optimization.php
<?php

declare(strict_types=1);

class InventoryOptimization
{
    private array $memo;

    // Minimize inventory cost with ordering constraints
    public function minimizeCost(
        array $demand,  // Daily demand
        int $orderCost,  // Fixed cost per order
        int $holdingCost,  // Cost per unit per day
        int $capacity  // Warehouse capacity
    ): array {
        $n = count($demand);
        $this->memo = [];

        $minCost = $this->solve($demand, 0, 0, $orderCost, $holdingCost, $capacity);
        $orders = $this->reconstructOrders($demand, $orderCost, $holdingCost, $capacity);

        return [
            'min_cost' => $minCost,
            'orders' => $orders
        ];
    }

    private function solve(
        array $demand,
        int $day,
        int $inventory,
        int $orderCost,
        int $holdingCost,
        int $capacity
    ): int {
        $n = count($demand);

        if ($day === $n) {
            return 0;
        }

        $key = "$day:$inventory";
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $minCost = PHP_INT_MAX;

        // Try not ordering today (if we have enough inventory)
        if ($inventory >= $demand[$day]) {
            $cost = $holdingCost * ($inventory - $demand[$day]);
            $cost += $this->solve(
                $demand,
                $day + 1,
                $inventory - $demand[$day],
                $orderCost,
                $holdingCost,
                $capacity
            );
            $minCost = min($minCost, $cost);
        }

        // Try ordering different amounts
        for ($order = max(0, $demand[$day] - $inventory);
             $order <= $capacity && $inventory + $order <= $capacity;
             $order += 10) {

            $cost = $orderCost;  // Fixed ordering cost
            $newInventory = $inventory + $order - $demand[$day];

            if ($newInventory >= 0) {
                $cost += $holdingCost * $newInventory;
                $cost += $this->solve(
                    $demand,
                    $day + 1,
                    $newInventory,
                    $orderCost,
                    $holdingCost,
                    $capacity
                );
                $minCost = min($minCost, $cost);
            }
        }

        $this->memo[$key] = $minCost;
        return $minCost;
    }

    private function reconstructOrders(
        array $demand,
        int $orderCost,
        int $holdingCost,
        int $capacity
    ): array {
        $orders = [];
        $inventory = 0;
        $n = count($demand);

        for ($day = 0; $day < $n; $day++) {
            $currentCost = $this->solve(
                $demand,
                $day,
                $inventory,
                $orderCost,
                $holdingCost,
                $capacity
            );

            // Try each ordering option
            $bestOrder = 0;
            $bestCost = PHP_INT_MAX;

            for ($order = 0; $order <= $capacity - $inventory; $order += 10) {
                $newInventory = $inventory + $order - $demand[$day];

                if ($newInventory >= 0) {
                    $cost = ($order > 0 ? $orderCost : 0) + $holdingCost * $newInventory;
                    $cost += $this->solve(
                        $demand,
                        $day + 1,
                        $newInventory,
                        $orderCost,
                        $holdingCost,
                        $capacity
                    );

                    if ($cost < $bestCost) {
                        $bestCost = $cost;
                        $bestOrder = $order;
                    }
                }
            }

            if ($bestOrder > 0) {
                $orders[] = ['day' => $day, 'amount' => $bestOrder];
            }

            $inventory = $inventory + $bestOrder - $demand[$day];
        }

        return $orders;
    }
}

// Example
$inventory = new InventoryOptimization();
$demand = [50, 30, 40, 70, 20, 60, 45];
$result = $inventory->minimizeCost($demand, 100, 2, 200);

echo "Minimum cost: \${$result['min_cost']}\n";
echo "Order schedule:\n";
foreach ($result['orders'] as $order) {
    echo "Day {$order['day']}: Order {$order['amount']} units\n";
}
```

**Why It Works**: State `(day, inventory)` represents the minimum cost to satisfy demand from day `day` onward with current inventory. For each day, we can either not order (if inventory sufficient) or order various amounts. The recurrence considers ordering costs, holding costs, and future optimal costs. Memoization avoids recomputing the same states. This solves the classic economic order quantity (EOQ) problem with capacity constraints.

## Troubleshooting

Advanced DP problems can be challenging to debug. Here are common issues and solutions:

### Problem: Bitmask DP gives wrong result for TSP

**Symptom**: TSP solution doesn't find optimal tour or returns incorrect cost

**Cause**: Common issues include:

- Not initializing base case correctly (`dp[1][0] = 0`)
- Incorrect mask updates when visiting cities
- Wrong order of mask iteration (must process smaller masks first)

**Solution**: Verify initialization and mask operations:

```php
// ✅ Correct initialization
$dp[1][0] = 0;  // Start at city 0 with only city 0 visited

// ✅ Correct mask iteration (increasing order)
for ($mask = 1; $mask <= $allVisited; $mask++) {
    // Process all cities in current mask
    for ($u = 0; $u < $n; $u++) {
        if (!($mask & (1 << $u))) continue;  // City not in mask

        // Visit unvisited cities
        for ($v = 0; $v < $n; $v++) {
            if ($mask & (1 << $v)) continue;  // Already visited

            $newMask = $mask | (1 << $v);  // Add city v to mask
            $dp[$newMask][$v] = min($dp[$newMask][$v], $dp[$mask][$u] + $distances[$u][$v]);
        }
    }
}
```

### Problem: Interval DP index out of bounds

**Symptom**: Array index errors in matrix chain multiplication or palindrome partitioning

**Cause**: Off-by-one errors in interval length calculations or incorrect bounds checking

**Solution**: Carefully verify interval calculations:

```php
// ✅ Correct: For interval length len starting at i
for ($len = 2; $len <= $n; $len++) {
    for ($i = 0; $i < $n - $len + 1; $i++) {
        $j = $i + $len - 1;  // End of interval

        // Verify: j < n
        if ($j >= $n) continue;

        // Process interval [i, j]
    }
}
```

### Problem: Edit distance reconstruction gives wrong operations

**Symptom**: Reconstructed edit operations don't match the minimum distance

**Cause**: Incorrect tie-breaking when multiple operations have same cost, or wrong order of operations

**Solution**: Use consistent tie-breaking and reverse operations:

```php
// ✅ Correct: Prefer replace > delete > insert when costs are equal
if ($replace <= $delete && $replace <= $insert) {
    $operations[] = "Replace '{$word1[$i-1]}' with '{$word2[$j-1]}'";
    $i--; $j--;
} elseif ($delete <= $insert) {
    $operations[] = "Delete '{$word1[$i-1]}'";
    $i--;
} else {
    $operations[] = "Insert '{$word2[$j-1]}'";
    $j--;
}

// Don't forget to reverse at the end
return array_reverse($operations);
```

### Problem: Digit DP counts wrong numbers

**Symptom**: Digit DP solution counts incorrect number of numbers with property

**Cause**: Incorrect handling of tight constraint or started flag, leading to double-counting or missing numbers

**Solution**: Carefully manage tight and started flags:

```php
// ✅ Correct: Handle leading zeros properly
if (!$started && $digit === 0) {
    // Leading zero - don't count sum yet
    $result += $this->solve($digits, $pos + 1, 0, $targetSum, false, false);
} else {
    // Non-zero digit - update sum and mark as started
    $result += $this->solve(
        $digits,
        $pos + 1,
        $currentSum + $digit,
        $targetSum,
        $tight && ($digit === $limit),  // Tight only if matching limit
        true  // Number has started
    );
}
```

### Problem: Profile DP runs out of memory

**Symptom**: Memory limit exceeded for large grids

**Cause**: Profile DP has exponential state space in number of columns (O(2^m) states per row)

**Solution**:

- Use memoization with efficient key generation
- Consider iterative approach for small m
- For large m, use alternative algorithms or optimizations

```php
// ✅ Efficient memoization key
$key = "$row:$mask";  // Compact string representation

// ✅ Consider iterative DP for small m (m <= 12)
if ($cols <= 12) {
    // Use tabulation instead of memoization
    // More memory efficient for small state spaces
}
```

### Problem: Probability DP gives infinite recursion

**Symptom**: Stack overflow or infinite loop in probability DP

**Cause**: Missing base case or incorrect boundary handling in random walk

**Solution**: Ensure all base cases are handled:

```php
// ✅ Correct: Handle all boundary cases
if ($current === $target) {
    return 0.0;  // Base case: reached target
}

if ($current === 0) {
    // At left boundary - can only move right
    $expected += $this->solve($current + 1, $target);
} elseif ($current === $target - 1) {
    // One step from target - can move either direction
    $expected += 0.5 * $this->solve($current - 1, $target);
    $expected += 0.5 * $this->solve($current + 1, $target);
} else {
    // Normal case - can move either direction
    $expected += 0.5 * $this->solve($current - 1, $target);
    $expected += 0.5 * $this->solve($current + 1, $target);
}
```

### Problem: Space optimization gives wrong result

**Symptom**: Rolling array version produces different result than 2D version

**Cause**: Incorrect order of iteration or overwriting values needed later

**Solution**: Iterate in correct direction and order:

```php
// ✅ For 2D DP where dp[i][j] depends on dp[i-1][...]
// Use 1D array and iterate forward, but be careful about dependencies

// ✅ For knapsack: iterate backwards to avoid using item twice
for ($w = $capacity; $w >= $weights[$i]; $w--) {
    $dp[$w] = max($dp[$w], $values[$i] + $dp[$w - $weights[$i]]);
}

// ✅ For edit distance: can iterate forward since we only need previous row
for ($j = 1; $j <= $n; $j++) {
    $dp[$j] = min($dp[$j], $dp[$j - 1] + 1);  // Update in place
}
```

## Wrap-up

Congratulations! You've mastered advanced Dynamic Programming techniques that unlock solutions to complex optimization problems. Here's what you've accomplished:

✓ **Interval DP** - Solved matrix chain multiplication and palindrome partitioning by processing ranges efficiently  
✓ **Knuth Optimization** - Reduced interval DP complexity from O(n³) to O(n²) using monotonicity  
✓ **Bitmask DP** - Handled exponential state spaces using bit manipulation for TSP and assignment problems  
✓ **Multi-dimensional DP** - Solved edit distance, longest palindromic subsequence, and egg drop problems  
✓ **DP on Trees** - Applied DP to tree structures for diameter, coloring, and matching problems  
✓ **DP on DAGs** - Solved longest path, shortest path, and critical path problems using topological ordering  
✓ **Specialized Techniques** - Mastered digit DP, probability DP, profile DP, convex hull optimization, matrix exponentiation, and state machine DP  
✓ **Space Optimization** - Reduced memory usage from O(n²) to O(n) using rolling arrays and binary search  
✓ **Real-World Applications** - Built inventory optimization and project scheduling systems using advanced DP patterns

These advanced DP techniques are essential for competitive programming, system optimization, and solving complex real-world problems. The patterns you've learned—interval DP, bitmask DP, and specialized optimizations—will help you recognize when and how to apply DP to seemingly intractable problems.

## Key Takeaways

- Advanced DP extends fundamentals with complex state management
- Interval DP processes ranges/intervals of elements
- Knuth optimization reduces O(n³) to O(n²) for certain interval DP problems
- Bitmask DP efficiently handles subset problems (n ≤ 20)
- Digit DP solves counting problems with digit constraints
- Probability DP handles expected values and probabilistic states
- Profile DP (broken profile) solves grid tiling problems
- Convex hull optimization reduces O(n²) to O(n log n) for certain recurrences
- Matrix exponentiation computes linear recurrences in O(log n) time
- State machine DP models problems with complex state transitions (regex, pattern matching)
- Multi-dimensional DP solves problems requiring multiple parameters
- DP on trees uses recursive structure of trees (diameter, coloring, matching)
- DP on DAGs leverages topological ordering for efficient processing
- Longest palindromic subsequence demonstrates advanced string DP
- State compression reduces memory usage
- Deque optimization improves sliding window DP problems
- Space optimization techniques reduce memory from O(n²) to O(n)
- Pattern recognition crucial for identifying DP approach
- Trade-offs between time and space complexity
- Advanced techniques enable solving NP-hard problems optimally for small inputs
- Real-world applications include inventory optimization, resource allocation, project scheduling

## Complexity Summary (Extended)

| Problem               | States    | Transitions      | Time                    | Space       | Optimization        |
| --------------------- | --------- | ---------------- | ----------------------- | ----------- | ------------------- |
| Matrix Chain          | O(n²)     | O(n)             | O(n³)                   | O(n²)       | -                   |
| Optimal BST (Knuth)   | O(n²)     | O(1)             | O(n²)                   | O(n²)       | Knuth optimization  |
| Palindrome Partition  | O(n²)     | O(n)             | O(n²)                   | O(n²)       | -                   |
| Longest Palindromic   | O(n²)     | O(1)             | O(n²)                   | O(n)        | Rolling array       |
| TSP                   | O(2ⁿ×n)   | O(n)             | O(2ⁿ×n²)                | O(2ⁿ×n)     | Bitmask             |
| Edit Distance         | O(m×n)    | O(1)             | O(m×n)                  | O(min(m,n)) | Rolling array       |
| Egg Drop              | O(e×f)    | O(f) or O(log f) | O(e×f²) or O(e×f log f) | O(e×f)      | Binary search       |
| Tree DP               | O(n)      | O(deg)           | O(n)                    | O(n)        | DFS                 |
| DAG Longest Path      | O(V)      | O(E)             | O(V + E)                | O(V)        | Topological sort    |
| Digit DP              | O(d×s×2²) | O(10)            | O(d×s×20)               | O(d×s×4)    | State compression   |
| Profile DP            | O(n×2^m)  | O(2^m)           | O(n×4^m)                | O(2^m)      | Bitmask             |
| LIS Optimized         | O(n)      | O(log n)         | O(n log n)              | O(n)        | Binary search       |
| Convex Hull           | O(n)      | O(log n)         | O(n log n)              | O(n)        | Deque/CHT           |
| Matrix Exponentiation | O(1)      | O(log n)         | O(log n)                | O(1)        | Fast exponentiation |
| State Machine DP      | O(n×m)    | O(1)             | O(n×m)                  | O(n×m)      | State transitions   |


## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 26 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-26)**

Clone the repository to run examples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-26
php matrix-chain-multiplication.php
```

## Further Reading

- [Dynamic Programming on CP-Algorithms](https://cp-algorithms.com/dynamic_programming/intro-to-dp.html) — Comprehensive guide to DP techniques and optimizations
- [Bitmask DP Tutorial](https://www.hackerearth.com/practice/algorithms/dynamic-programming/bit-masking/tutorial/) — Deep dive into bitmask dynamic programming
- [Interval DP Patterns](https://usaco.guide/adv/dp-more?lang=cpp) — Common interval DP problems and solutions
- [Chapter 25: Dynamic Programming Fundamentals](/series/php-algorithms/chapters/25-dynamic-programming-fundamentals) — Review the fundamentals before tackling advanced techniques
- [LeetCode Dynamic Programming Problems](https://leetcode.com/tag/dynamic-programming/) — Practice problems sorted by difficulty

## Next Steps

In the next chapter, we'll explore practical caching and memoization strategies for PHP applications, including Redis integration, APCu comparison, query result caching, and computed property caching with production benchmarks.
