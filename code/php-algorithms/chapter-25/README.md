# Chapter 25: Dynamic Programming Fundamentals - Code Samples

Comprehensive PHP code samples for fundamental dynamic programming concepts and classic problems.

## Files Overview

### 1. `fibonacci-dp.php`
**Purpose**: Demonstrate memoization vs tabulation approaches.

**Key Concepts**:
- Memoization (top-down with caching)
- Tabulation (bottom-up iterative)
- Space optimization
- Comparison of approaches

**Run**: `php fibonacci-dp.php`

### 2. `coin-change.php`
**Purpose**: Classic coin change problem with multiple variations.

**Variations**:
- Minimum coins needed
- Track which coins used
- Count number of ways

**Run**: `php coin-change.php`

### 3. `knapsack.php`
**Purpose**: 0/1 knapsack problem for resource optimization.

**Features**:
- Maximum value calculation
- Item selection reconstruction
- Weight constraint handling

**Applications**: Resource allocation, portfolio optimization

**Run**: `php knapsack.php`

### 4. `longest-common-subsequence.php`
**Purpose**: Find longest common subsequence between two strings.

**Features**:
- LCS length calculation
- Actual LCS reconstruction
- DP table visualization

**Applications**: Diff algorithms, DNA analysis, version control

**Run**: `php longest-common-subsequence.php`

## Core DP Concepts

**Two Key Properties**:
1. **Overlapping Subproblems**: Same subproblems solved multiple times
2. **Optimal Substructure**: Optimal solution contains optimal subsolutions

**Two Approaches**:
1. **Memoization (Top-Down)**:
   - Start with original problem
   - Recursively break down
   - Cache results
   - Natural for many problems

2. **Tabulation (Bottom-Up)**:
   - Start with base cases
   - Build up iteratively
   - Fill DP table
   - Better performance (no recursion overhead)

## Problem Patterns

| Pattern | Examples | Key |
|---------|----------|-----|
| Optimization | Knapsack, coin change (min) | Find max/min |
| Counting | Coin change (ways), climbing stairs | Count possibilities |
| Decision | Subset sum, partition | Yes/no answer |
| Sequence | LCS, LIS, edit distance | Build sequence |

## Complexity Summary

| Problem | Time | Space | Optimized Space |
|---------|------|-------|-----------------|
| Fibonacci | O(n) | O(n) | O(1) |
| Coin Change | O(n×m) | O(n) | O(n) |
| Knapsack 0/1 | O(n×W) | O(n×W) | O(W) |
| LCS | O(m×n) | O(m×n) | O(min(m,n)) |

## DP Problem-Solving Steps

1. **Identify**: Does it have overlapping subproblems and optimal substructure?
2. **Define State**: What does dp[i] or dp[i][j] represent?
3. **Find Recurrence**: How to compute dp[i] from smaller subproblems?
4. **Base Cases**: What are the initial/trivial cases?
5. **Order**: What order to fill the DP table?
6. **Answer**: Where is the final answer in the table?
7. **Optimize**: Can we reduce space complexity?

## When to Use DP

- Problem has overlapping subproblems
- Need optimal solution (not just any solution)
- Can break into smaller subproblems
- Subproblem solutions can be reused
- Recursive solution too slow

## Memoization vs Tabulation

**Memoization**:
- Pros: Easy to code, computes only needed values
- Cons: Recursion overhead, stack depth limit
- Use when: Not all subproblems needed

**Tabulation**:
- Pros: No recursion, easier to optimize space
- Cons: May compute unneeded values
- Use when: All subproblems needed

## Requirements

- PHP 8.0+
- No external dependencies

## Practice Tips

1. Start with recursive solution
2. Identify repeated subproblems
3. Add memoization
4. Convert to tabulation if needed
5. Optimize space last

## Next Steps

After mastering these fundamentals:
- **Advanced DP**: State compression, digit DP, DP on trees
- **Greedy Algorithms**: Related optimization technique
- **Divide and Conquer**: Alternative problem-solving paradigm
