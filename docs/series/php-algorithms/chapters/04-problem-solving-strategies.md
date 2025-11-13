---
title: "04: Problem-Solving Strategies"
description: "Develop systematic approaches to algorithm problems. Learn to break down complex problems into manageable steps."
series: "php-algorithms"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "Understanding of Big O notation"
  - "Familiarity with recursion"
  - "Completion of Chapters 0-3"
---

# Problem-Solving Strategies

Now that you understand algorithms, complexity analysis, benchmarking, and recursion, it's time to develop systematic problem-solving strategies. This chapter teaches you how to approach any algorithmic problem with confidence.

## The Problem-Solving Framework

When faced with an algorithm challenge, follow this systematic approach:

### 1. Understand the Problem

Before writing any code, make sure you fully understand what's being asked:

**Ask these questions:**
- What are the inputs? (Types, ranges, constraints)
- What is the expected output?
- Are there edge cases?
- What are the performance requirements?
- Can I solve this by hand with a small example?

**Example Problem:** "Find all pairs in an array that sum to a target value."

**Understanding phase:**
- Input: Array of integers, target sum (integer)
- Output: Array of pairs (arrays with 2 elements)
- Edge cases: Empty array, no pairs found, duplicate numbers
- Should pairs be unique? Can we use the same element twice?

### 2. Devise a Plan

Choose an appropriate problem-solving strategy:

```php
// Problem: Find pairs that sum to target

// Plan A: Brute force - check all pairs O(n²)
// Plan B: Use hash map for O(n) lookup
// Plan C: Sort array and use two pointers O(n log n)

// Choose Plan B for best time complexity
```

### 3. Implement and Test

Start with a working solution, even if inefficient:

```php
// Step 1: Brute force solution (works but slow)
function findPairsBrute(array $nums, int $target): array
{
    $pairs = [];
    $n = count($nums);

    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($nums[$i] + $nums[$j] === $target) {
                $pairs[] = [$nums[$i], $nums[$j]];
            }
        }
    }

    return $pairs;
}

// Step 2: Optimize with hash map
function findPairsOptimized(array $nums, int $target): array
{
    $pairs = [];
    $seen = [];

    foreach ($nums as $num) {
        $complement = $target - $num;

        if (isset($seen[$complement])) {
            $pairs[] = [$complement, $num];
        }

        $seen[$num] = true;
    }

    return $pairs;
}
```

### 4. Optimize and Refine

Analyze complexity and look for improvements:

```php
// Complexity analysis:
// Brute force: O(n²) time, O(1) space
// Optimized: O(n) time, O(n) space

// Trade-off: Better time complexity for more space
```

## Common Problem-Solving Patterns

### Pattern 1: Two Pointers

Use two pointers to traverse data from different positions.

**When to use:** Sorted arrays, palindromes, pairs/triplets

```php
// Problem: Check if string is palindrome
function isPalindrome(string $s): bool
{
    $left = 0;
    $right = strlen($s) - 1;

    while ($left < $right) {
        if ($s[$left] !== $s[$right]) {
            return false;
        }
        $left++;
        $right--;
    }

    return true;
}

// Problem: Find pair in sorted array that sums to target
function findPairSorted(array $nums, int $target): ?array
{
    $left = 0;
    $right = count($nums) - 1;

    while ($left < $right) {
        $sum = $nums[$left] + $nums[$right];

        if ($sum === $target) {
            return [$nums[$left], $nums[$right]];
        } elseif ($sum < $target) {
            $left++; // Need larger sum
        } else {
            $right--; // Need smaller sum
        }
    }

    return null;
}
```

### Pattern 2: Sliding Window

Maintain a "window" of elements and slide it through the array.

**When to use:** Subarrays, substrings, consecutive elements

```php
// Problem: Maximum sum of k consecutive elements
function maxSumSubarray(array $nums, int $k): int
{
    if (count($nums) < $k) return 0;

    // Calculate first window sum
    $windowSum = array_sum(array_slice($nums, 0, $k));
    $maxSum = $windowSum;

    // Slide the window
    for ($i = $k; $i < count($nums); $i++) {
        $windowSum = $windowSum - $nums[$i - $k] + $nums[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

echo maxSumSubarray([2, 1, 5, 1, 3, 2], 3); // 9 (5+1+3)
```

**Visual representation:**
```
[2, 1, 5, 1, 3, 2]  k=3
 └──┴──┘            sum=8
    └──┴──┘         sum=7
       └──┴──┘      sum=9  ← maximum
          └──┴──┘   sum=6
```

### Pattern 3: Hash Map Lookups

Use hash maps for O(1) lookups.

**When to use:** Counting, frequency, finding complements

```php
// Problem: Find first non-repeating character
function firstUnique(string $s): ?string
{
    // Count frequency
    $freq = [];
    for ($i = 0; $i < strlen($s); $i++) {
        $char = $s[$i];
        $freq[$char] = ($freq[$char] ?? 0) + 1;
    }

    // Find first with frequency 1
    for ($i = 0; $i < strlen($s); $i++) {
        if ($freq[$s[$i]] === 1) {
            return $s[$i];
        }
    }

    return null;
}

echo firstUnique('leetcode'); // 'l'
```

### Pattern 4: Fast & Slow Pointers

Use two pointers moving at different speeds.

**When to use:** Cycle detection, finding middle element

```php
// Problem: Detect cycle in array (values are indices)
function hasCycle(array $nums): bool
{
    if (empty($nums)) return false;

    $slow = 0;
    $fast = 0;

    do {
        // Move slow by 1 step
        $slow = $nums[$slow];

        // Move fast by 2 steps
        $fast = $nums[$nums[$fast]];

        // If they meet, there's a cycle
        if ($slow === $fast) {
            return true;
        }

    } while ($fast !== 0 && $nums[$fast] !== 0);

    return false;
}
```

### Pattern 5: Divide and Conquer

Break problem into smaller subproblems, solve recursively, combine results.

**When to use:** Sorting, searching, tree problems

```php
// Problem: Find maximum element using divide and conquer
function findMaxDivideConquer(array $nums, int $left, int $right): int
{
    // Base case: single element
    if ($left === $right) {
        return $nums[$left];
    }

    // Divide: split array in half
    $mid = (int)(($left + $right) / 2);

    // Conquer: find max in each half
    $leftMax = findMaxDivideConquer($nums, $left, $mid);
    $rightMax = findMaxDivideConquer($nums, $mid + 1, $right);

    // Combine: return larger of the two
    return max($leftMax, $rightMax);
}

$nums = [3, 7, 2, 9, 1, 5];
echo findMaxDivideConquer($nums, 0, count($nums) - 1); // 9
```

### Pattern 6: Greedy Algorithms

Make locally optimal choices at each step.

**When to use:** Optimization problems where local optimum leads to global optimum

```php
// Problem: Coin change (fewest coins)
function minCoins(array $coins, int $amount): int
{
    rsort($coins); // Sort descending
    $count = 0;

    foreach ($coins as $coin) {
        while ($amount >= $coin) {
            $amount -= $coin;
            $count++;
        }
    }

    return $amount === 0 ? $count : -1; // -1 if not possible
}

echo minCoins([1, 5, 10, 25], 63); // 6 (25+25+10+1+1+1)
```

**Warning:** Greedy doesn't always work! For coin change with arbitrary denominations, use dynamic programming.

### Pattern 7: Backtracking

Try all possibilities, backtrack when you hit a dead end.

**When to use:** Permutations, combinations, puzzles, constraint satisfaction

```php
// Problem: Generate all permutations
function permute(array $nums): array
{
    $result = [];
    permuteHelper($nums, [], $result);
    return $result;
}

function permuteHelper(array $nums, array $current, array &$result): void
{
    // Base case: no more numbers to add
    if (empty($nums)) {
        $result[] = $current;
        return;
    }

    // Try each remaining number
    for ($i = 0; $i < count($nums); $i++) {
        // Choose
        $chosen = $nums[$i];
        $remaining = array_merge(
            array_slice($nums, 0, $i),
            array_slice($nums, $i + 1)
        );

        // Explore
        permuteHelper($remaining, array_merge($current, [$chosen]), $result);

        // Backtrack (implicit - function returns)
    }
}

print_r(permute([1, 2, 3]));
// Output: [[1,2,3], [1,3,2], [2,1,3], [2,3,1], [3,1,2], [3,2,1]]
```

## Problem Classification

Learn to recognize problem types:

### Searching & Sorting
- Binary search variants
- Custom sorting criteria
- Finding kth element

### Array Manipulation
- Subarrays, subsequences
- Rotation, reversal
- In-place modifications

### String Processing
- Pattern matching
- Anagrams, palindromes
- Parsing and validation

### Graph & Tree Problems
- Traversal (DFS, BFS)
- Shortest path
- Cycle detection

### Dynamic Programming
- Overlapping subproblems
- Optimal substructure
- Memoization opportunities

### Mathematical
- Number theory (GCD, primes)
- Combinatorics
- Bit manipulation

## Step-by-Step Example

Let's solve a complete problem using our framework:

**Problem:** Given an array of integers, find the longest consecutive sequence.

**Example:** `[100, 4, 200, 1, 3, 2]` → `4` (sequence: 1, 2, 3, 4)

### Step 1: Understand

```php
// Input: array of integers (can be unsorted, may have duplicates)
// Output: length of longest consecutive sequence
// Edge cases: empty array (return 0), single element (return 1)
// Consecutive means n, n+1, n+2, etc.
```

### Step 2: Plan

```php
// Approach 1: Sort array, count consecutive - O(n log n)
// Approach 2: Use hash set for O(1) lookups - O(n)
// Choose Approach 2 for better complexity
```

### Step 3: Implement

```php
function longestConsecutive(array $nums): int
{
    if (empty($nums)) return 0;

    // Build hash set for O(1) lookups
    $numSet = array_flip($nums);
    $longest = 0;

    foreach ($numSet as $num => $_) {
        // Only start counting from sequence start
        if (!isset($numSet[$num - 1])) {
            $currentNum = $num;
            $currentStreak = 1;

            // Count consecutive numbers
            while (isset($numSet[$currentNum + 1])) {
                $currentNum++;
                $currentStreak++;
            }

            $longest = max($longest, $currentStreak);
        }
    }

    return $longest;
}

// Test
echo longestConsecutive([100, 4, 200, 1, 3, 2]); // 4
echo longestConsecutive([0, 3, 7, 2, 5, 8, 4, 6, 0, 1]); // 9
```

### Step 4: Analyze

```php
// Time: O(n) - each number visited at most twice
// Space: O(n) - hash set storage
// This is optimal - can't do better than O(n) time
```

## Debugging Strategies

When your solution doesn't work:

### 1. Test with Simple Cases

```php
function buggyFunction(array $nums): int
{
    // Test with small inputs first
    // [] - edge case
    // [1] - single element
    // [1, 2] - two elements
    // [1, 1, 1] - duplicates
}
```

### 2. Add Debug Output

```php
function debugSum(array $nums): int
{
    $sum = 0;
    foreach ($nums as $i => $num) {
        $sum += $num;
        echo "Step $i: sum=$sum, added=$num\n"; // Debug
    }
    return $sum;
}
```

### 3. Check Edge Cases

```php
function safeFunction($input)
{
    // Handle null/empty
    if ($input === null || empty($input)) {
        return defaultValue();
    }

    // Handle single element
    if (count($input) === 1) {
        return $input[0];
    }

    // Main logic
    // ...
}
```

## Practice Problems

### Problem 1: Product of Array Except Self

Given an array, return array where each element is the product of all others (without using division).

```php
function productExceptSelf(array $nums): array
{
    // Your solution here
}

// Test: [1, 2, 3, 4] → [24, 12, 8, 6]
```

<details>
<summary>Hint</summary>
Use two passes: one for products to the left, one for products to the right.
</details>

### Problem 2: Valid Parentheses

Check if a string of parentheses is valid: `"()[]{ }"` → true, `"([)]"` → false

```php
function isValidParentheses(string $s): bool
{
    // Your solution here
}
```

<details>
<summary>Hint</summary>
Use a stack to track opening brackets.
</details>

### Problem 3: Container With Most Water

Given heights array, find two lines that form container with maximum area.

```php
function maxArea(array $heights): int
{
    // Your solution here
}

// Test: [1, 8, 6, 2, 5, 4, 8, 3, 7] → 49
```

<details>
<summary>Hint</summary>
Use two pointers from both ends, move the pointer with smaller height.
</details>

## Key Takeaways

- **Understand** the problem completely before coding
- **Choose the right strategy**: two pointers, sliding window, hash maps, divide and conquer, etc.
- **Start simple**: Get a working solution first, then optimize
- **Recognize patterns**: Many problems fit common templates
- **Test thoroughly**: Include edge cases in your testing
- **Analyze complexity**: Know the Big O of your solution

## What's Next

Now that you have problem-solving strategies, we'll apply them to sorting algorithms, starting with **Bubble Sort and Selection Sort** in the next chapter.

---

Continue to [Chapter 05: Bubble Sort & Selection Sort](/series/php-algorithms/chapters/05-bubble-sort-selection-sort).
