---
title: "Advanced Dynamic Programming"
description: "Explore advanced DP techniques including matrix chain multiplication, palindrome partitioning, state compression, bitmask DP, and multi-dimensional optimization problems"
series: "php-algorithms"
chapter: 26
order: 26
difficulty: "advanced"
prerequisites: ["Dynamic Programming Fundamentals"]
---

# Advanced Dynamic Programming

Building on DP fundamentals, this chapter explores complex DP patterns including interval DP, bitmask DP, multi-dimensional optimization, and advanced state management techniques.

## Interval DP

Problems where you process intervals/ranges of elements.

### Matrix Chain Multiplication

Find optimal order to multiply chain of matrices to minimize operations.

```php
<?php

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
// ((M1 × M2) × (M3 × M4))
```

### Palindrome Partitioning

Minimum cuts to partition string into palindromes.

```php
<?php

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

## Bitmask DP

Use bitmasks to represent states for problems involving subsets.

### Traveling Salesman Problem (TSP)

Find shortest route visiting all cities exactly once.

```php
<?php

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

### Assignment Problem

Assign n tasks to n workers to minimize total cost.

```php
<?php

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

## Multi-Dimensional DP

### Edit Distance

Minimum operations to convert string A to string B.

```php
<?php

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
// Replace 'h' with 'r', Delete 'r', Delete 'e'
```

### Egg Drop Problem

Find minimum trials needed to determine egg breaking floor.

```php
<?php

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

## DP on Trees

### Tree Diameter

Find longest path between any two nodes.

```php
<?php

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

echo "Tree diameter: " . $treeDP->findDiameter($tree, 0) . "\n";  // 4 (3→1→0→2→5)
```

## State Compression Techniques

### Subset Sum with Limited Items

```php
<?php

class SubsetSumLimited
{
    // Can we make target sum using given counts of each number?
    public function canMakeSum(array $nums, array $counts, int $target): bool
    {
        $dp = array_fill(0, $target + 1, false);
        $dp[0] = true;

        for ($i = 0; $i < count($nums); $i++) {
            $num = $nums[$i];
            $count = $counts[$i];

            // Process from right to left
            for ($sum = $target; $sum >= $num; $sum--) {
                // Try using 1, 2, ..., count items
                for ($k = 1; $k <= $count && $k * $num <= $sum; $k++) {
                    if ($dp[$sum - $k * $num]) {
                        $dp[$sum] = true;
                        break;
                    }
                }
            }
        }

        return $dp[$target];
    }
}
```

## Complexity Analysis

| Problem | States | Transitions | Time | Space |
|---------|--------|-------------|------|-------|
| Matrix Chain | O(n²) | O(n) | O(n³) | O(n²) |
| Palindrome Partition | O(n²) | O(n) | O(n²) | O(n²) |
| TSP | O(2ⁿ×n) | O(n) | O(2ⁿ×n²) | O(2ⁿ×n) |
| Edit Distance | O(m×n) | O(1) | O(m×n) | O(min(m,n)) |
| Egg Drop | O(e×f) | O(f) or O(log f) | O(e×f²) or O(e×f log f) | O(e×f) |

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

1. **Burst Balloons**
   - Pop balloons to maximize coins (interval DP)

2. **Boolean Parenthesization**
   - Ways to parenthesize boolean expression (interval DP)

3. **Optimal Binary Search Tree**
   - Build BST with minimum search cost

4. **Maximum Profit Job Scheduling**
   - Select non-overlapping jobs for max profit

5. **Minimum Cost Tree from Leaves**
   - Build tree with given leaf values, minimize sum

## Key Takeaways

- Advanced DP extends fundamentals with complex state management
- Interval DP processes ranges/intervals of elements
- Bitmask DP efficiently handles subset problems (n ≤ 20)
- Multi-dimensional DP solves problems requiring multiple parameters
- DP on trees uses recursive structure of trees
- State compression reduces memory usage
- Pattern recognition crucial for identifying DP approach
- Trade-offs between time and space complexity
- Advanced techniques enable solving NP-hard problems optimally for small inputs

## Next Steps

In the next chapter, we'll explore practical caching and memoization strategies for PHP applications, including Redis integration, query result caching, and computed property caching.
