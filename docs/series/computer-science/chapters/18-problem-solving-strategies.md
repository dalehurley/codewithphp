---
title: "18: Problem Solving Strategies"
description: "Develop a systematic approach to coding challenges. Learn how to break down problems, recognize patterns, choose data structures, and write clean, efficient solutions."
series: "computer-science"
chapter: 18
order: 18
difficulty: "Intermediate"
prerequisites: ["Data structures", "Algorithms", "Computational thinking"]
---

# Chapter 18: Problem Solving Strategies

## Introduction

Problem solving is a skill that improves with practice and strategy. This chapter teaches systematic approaches to tackle any coding challenge.

In this chapter, you'll learn:

- Systematic problem-solving framework
- Pattern recognition techniques
- How to choose the right approach
- Common problem-solving patterns

## The Problem-Solving Framework

### Step 1: Understand the Problem

**Questions to ask**:
- What are the inputs and outputs?
- What are the constraints?
- What are edge cases?
- Can I restate the problem in my own words?

```php
<?php

// Problem: Find duplicate in array

// Clarifying questions:
// - What type of elements? (integers, strings, objects?)
// - Can there be multiple duplicates? (return first, all, or any?)
// - What if no duplicates? (return null, false, -1?)
// - Are elements sorted? (affects approach)
// - Space constraints? (can I use extra memory?)
```

### Step 2: Explore Examples

```php
<?php

// Problem: Find duplicate
// Input: [1, 3, 4, 2, 2]
// Output: 2

// Edge cases:
// - Empty array: []
// - No duplicates: [1, 2, 3]
// - All same: [1, 1, 1, 1]
// - Two elements: [1, 1]
```

### Step 3: Break It Down

Pseudocode before coding:

```
function findDuplicate(array):
    1. Create a set to track seen elements
    2. Loop through array:
        a. If element in set, return it
        b. Add element to set
    3. Return null if no duplicate found
```

### Step 4: Solve Simpler Version First

```php
<?php

// Simplification: Assume array is sorted
function findDuplicateSorted(array $nums): ?int {
    for ($i = 1; $i < count($nums); $i++) {
        if ($nums[$i] === $nums[$i - 1]) {
            return $nums[$i];
        }
    }
    return null;
}

// Then generalize for unsorted
function findDuplicate(array $nums): ?int {
    $seen = [];
    foreach ($nums as $num) {
        if (isset($seen[$num])) {
            return $num;
        }
        $seen[$num] = true;
    }
    return null;
}
```

### Step 5: Optimize

Consider time and space:

```php
<?php

// O(n²) time, O(1) space - brute force
function findDuplicateBrute(array $nums): ?int {
    for ($i = 0; $i < count($nums); $i++) {
        for ($j = $i + 1; $j < count($nums); $j++) {
            if ($nums[$i] === $nums[$j]) {
                return $nums[$i];
            }
        }
    }
    return null;
}

// O(n) time, O(n) space - hash set
function findDuplicateHash(array $nums): ?int {
    $seen = [];
    foreach ($nums as $num) {
        if (isset($seen[$num])) return $num;
        $seen[$num] = true;
    }
    return null;
}

// O(n) time, O(1) space - if values are 1 to n
function findDuplicateCycle(array $nums): ?int {
    $slow = $fast = $nums[0];

    do {
        $slow = $nums[$slow];
        $fast = $nums[$nums[$fast]];
    } while ($slow !== $fast);

    $slow = $nums[0];
    while ($slow !== $fast) {
        $slow = $nums[$slow];
        $fast = $nums[$fast];
    }

    return $fast;
}
```

## Common Problem Patterns

### 1. Two Pointers

**Use**: Arrays, strings, linked lists

```php
<?php

// Remove duplicates from sorted array
function removeDuplicates(array &$nums): int {
    if (empty($nums)) return 0;

    $writeIndex = 1;
    for ($i = 1; $i < count($nums); $i++) {
        if ($nums[$i] !== $nums[$i - 1]) {
            $nums[$writeIndex++] = $nums[$i];
        }
    }
    return $writeIndex;
}

// Reverse string
function reverseString(string $s): string {
    $chars = str_split($s);
    $left = 0;
    $right = strlen($s) - 1;

    while ($left < $right) {
        [$chars[$left], $chars[$right]] = [$chars[$right], $chars[$left]];
        $left++;
        $right--;
    }

    return implode('', $chars);
}
```

### 2. Sliding Window

**Use**: Subarrays, substrings

```php
<?php

// Maximum sum of k consecutive elements
function maxSumSubarray(array $nums, int $k): int {
    $maxSum = $windowSum = array_sum(array_slice($nums, 0, $k));

    for ($i = $k; $i < count($nums); $i++) {
        $windowSum += $nums[$i] - $nums[$i - $k];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

// Longest substring without repeating characters
function lengthOfLongestSubstring(string $s): int {
    $seen = [];
    $maxLen = $left = 0;

    for ($right = 0; $right < strlen($s); $right++) {
        $char = $s[$right];

        if (isset($seen[$char]) && $seen[$char] >= $left) {
            $left = $seen[$char] + 1;
        }

        $seen[$char] = $right;
        $maxLen = max($maxLen, $right - $left + 1);
    }

    return $maxLen;
}
```

### 3. Fast & Slow Pointers

**Use**: Linked lists, cycle detection

```php
<?php

// Detect cycle in linked list
function hasCycle(?ListNode $head): bool {
    $slow = $fast = $head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;

        if ($slow === $fast) {
            return true;
        }
    }

    return false;
}

// Find middle of linked list
function findMiddle(?ListNode $head): ?ListNode {
    $slow = $fast = $head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;
    }

    return $slow;
}
```

### 4. Divide and Conquer

**Use**: Sorting, searching

```php
<?php

// Merge sort
function mergeSort(array $arr): array {
    if (count($arr) <= 1) return $arr;

    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

// Binary search
function binarySearch(array $arr, $target): ?int {
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) return $mid;

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return null;
}
```

### 5. Breadth-First Search (BFS)

**Use**: Trees, graphs, shortest path

```php
<?php

// Level order traversal
function levelOrder(?TreeNode $root): array {
    if ($root === null) return [];

    $result = [];
    $queue = [$root];

    while (!empty($queue)) {
        $levelSize = count($queue);
        $currentLevel = [];

        for ($i = 0; $i < $levelSize; $i++) {
            $node = array_shift($queue);
            $currentLevel[] = $node->value;

            if ($node->left) $queue[] = $node->left;
            if ($node->right) $queue[] = $node->right;
        }

        $result[] = $currentLevel;
    }

    return $result;
}
```

### 6. Depth-First Search (DFS)

**Use**: Trees, graphs, backtracking

```php
<?php

// All paths from root to leaves
function allPaths(?TreeNode $root): array {
    if ($root === null) return [];

    $paths = [];
    dfsHelper($root, [], $paths);
    return $paths;
}

function dfsHelper(?TreeNode $node, array $current, array &$paths): void {
    if ($node === null) return;

    $current[] = $node->value;

    if ($node->left === null && $node->right === null) {
        $paths[] = $current;
        return;
    }

    dfsHelper($node->left, $current, $paths);
    dfsHelper($node->right, $current, $paths);
}
```

## Choosing the Right Data Structure

| Problem Type | Consider |
|-------------|----------|
| Frequent lookups | Hash table |
| Ordered data + range queries | BST, array |
| LIFO operations | Stack |
| FIFO operations | Queue |
| Hierarchical data | Tree |
| Relationships | Graph |
| Fixed-size collection | Array |
| Dynamic-size collection | Linked list, dynamic array |

## Common Mistakes

### 1. Not Clarifying Requirements

```php
<?php

// Unclear: "Find maximum in array"
// - What if array is empty?
// - What if multiple maximums?
// - Integer or float values?
```

### 2. Jumping to Code Too Fast

Write pseudocode and test logic first.

### 3. Not Testing Edge Cases

Always test:
- Empty input
- Single element
- All same elements
- Maximum/minimum values
- Negative numbers

### 4. Ignoring Time/Space Complexity

Analyze Big O before submitting.

## Practice Strategy

1. **Start easy**: Build confidence
2. **Focus on patterns**: Recognize similar problems
3. **Time yourself**: Simulate interviews
4. **Review solutions**: Learn from others
5. **Consistency**: Practice daily

## Key Takeaways

- **Understand** problem before coding
- **Break down** into steps
- **Recognize patterns** (two pointers, sliding window, etc.)
- **Choose** right data structure
- **Optimize** after working solution
- **Test** edge cases
- **Practice** regularly

## Exercises

1. **Two Sum**: Find two numbers that add to target.

2. **Valid Parentheses**: Check if brackets are balanced.

3. **Merge Intervals**: Merge overlapping intervals.

4. **Top K Frequent Elements**: Find k most frequent elements.

5. **Longest Palindromic Substring**: Find longest palindrome in string.

## What's Next?

Problem-solving skills prepare you for **Technical Interviews** (Chapter 19)—applying these strategies under pressure.

---

**Further Reading**:
- [LeetCode Patterns](https://seanprashad.com/leetcode-patterns/)
- [Problem Solving Techniques](https://www.geeksforgeeks.org/problem-solving-techniques/)
