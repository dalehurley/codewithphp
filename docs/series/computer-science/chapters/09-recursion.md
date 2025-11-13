---
title: "09: Recursion and Recursive Thinking"
description: "Think recursively and solve problems with self-referential solutions. Understand base cases, recursive cases, call stacks, tail recursion, and when recursion is the right tool."
series: "computer-science"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites: ["Functions", "Algorithm analysis", "Call stack understanding"]
---

# Chapter 09: Recursion and Recursive Thinking

## Introduction

Recursion is a problem-solving technique where a function calls itself to solve smaller instances of the same problem. It's elegant, powerful, and essential for many algorithms and data structures.

In this chapter, you'll learn:

- How recursion works
- Base cases and recursive cases
- Call stack visualization
- Common recursive patterns
- When to use recursion vs. iteration

## What is Recursion?

A **recursive function** calls itself with a modified input until reaching a **base case**.

### Simple Example: Factorial

```php
<?php

// n! = n × (n-1) × (n-2) × ... × 1
function factorial(int $n): int {
    // Base case
    if ($n === 0 || $n === 1) {
        return 1;
    }

    // Recursive case
    return $n * factorial($n - 1);
}

echo factorial(5); // 120 (5 × 4 × 3 × 2 × 1)
```

**Call Stack Visualization**:

```mermaid
graph TB
    subgraph "Call Stack: factorial(5)"
        F5["factorial(5)<br/>5 × factorial(4)<br/>= 5 × 24 = 120"]
        F4["factorial(4)<br/>4 × factorial(3)<br/>= 4 × 6 = 24"]
        F3["factorial(3)<br/>3 × factorial(2)<br/>= 3 × 2 = 6"]
        F2["factorial(2)<br/>2 × factorial(1)<br/>= 2 × 1 = 2"]
        F1["factorial(1)<br/>Base case!<br/>= 1"]

        F5 -->|"Call"| F4
        F4 -->|"Call"| F3
        F3 -->|"Call"| F2
        F2 -->|"Call"| F1
        F1 -.->|"Return 1"| F2
        F2 -.->|"Return 2"| F3
        F3 -.->|"Return 6"| F4
        F4 -.->|"Return 24"| F5
    end

    style F5 fill:#2196F3,color:#fff
    style F4 fill:#42A5F5
    style F3 fill:#64B5F6
    style F2 fill:#90CAF9
    style F1 fill:#4CAF50
```

**How it works**: Stack builds up with calls (blue), then unwinds with returns (green arrows).

## Anatomy of Recursion

Every recursive function needs:

1. **Base case**: Condition to stop recursion
2. **Recursive case**: Function calls itself with modified input
3. **Progress toward base case**: Each call gets closer to termination

```php
<?php

function recursiveTemplate($input) {
    // 1. Base case
    if (/* stopping condition */) {
        return /* simple answer */;
    }

    // 2. Recursive case
    // - Do some work
    // - Call function with simpler input
    // - Combine results

    return recursiveTemplate(/* simpler input */);
}
```

## Classic Recursive Problems

### 1. Fibonacci Sequence

```php
<?php

// Naive recursive approach - O(2^n)
function fibonacci(int $n): int {
    if ($n <= 1) {
        return $n;
    }

    return fibonacci($n - 1) + fibonacci($n - 2);
}

echo fibonacci(6); // 8 (0, 1, 1, 2, 3, 5, 8)
```

**Problem**: Exponential time! fibonacci(5) calls fibonacci(3) twice.

```mermaid
graph TB
    subgraph "Fibonacci Tree - Redundant Calculations!"
        F5["fib(5)"]
        F4a["fib(4)"]
        F3a["fib(3)"]
        F4b["fib(3)"]
        F3b["fib(2)"]
        F2a["fib(2)"]
        F2b["fib(2)"]
        F1a["fib(1)"]
        F1b["fib(1)"]
        F1c["fib(1)"]
        F0a["fib(0)"]
        F0b["fib(0)"]
        F0c["fib(0)"]

        F5 --> F4a
        F5 --> F4b
        F4a --> F3a
        F4a --> F2a
        F3a --> F2b
        F3a --> F1a
        F4b --> F2b
        F4b --> F1b
        F2a --> F1c
        F2a --> F0a
        F2b --> F1a
        F2b --> F0b
    end

    style F5 fill:#FF6B6B,color:#fff
    style F4a fill:#FFA500
    style F4b fill:#FFA500
    style F3a fill:#FFD700
    style F3b fill:#FFD700
    style F2a fill:#90EE90
    style F2b fill:#90EE90
```

**Notice**: fib(3) computed twice, fib(2) computed three times! Exponential waste!

**Solution**: Memoization

```php
<?php

function fibonacciMemo(int $n, array &$memo = []): int {
    if ($n <= 1) {
        return $n;
    }

    if (isset($memo[$n])) {
        return $memo[$n];
    }

    $memo[$n] = fibonacciMemo($n - 1, $memo) + fibonacciMemo($n - 2, $memo);
    return $memo[$n];
}

echo fibonacciMemo(50); // Fast! O(n) time
```

```mermaid
graph LR
    subgraph "Memoization: Cache Results to Avoid Redundancy"
        M0["fib(5)"]
        M1["fib(4)<br/>Compute"]
        M2["fib(3)<br/>Compute"]
        M3["fib(3)<br/>✓ Cache hit!"]
        M4["fib(2)<br/>Compute"]
        M5["Memo table:<br/>{2→1, 3→2, 4→3, 5→5}"]

        M0 --> M1
        M0 --> M3
        M1 --> M2
        M1 --> M4
        M2 --> M5
        M3 --> M5
    end

    style M0 fill:#2196F3,color:#fff
    style M1 fill:#FFA500
    style M2 fill:#FFD700
    style M3 fill:#4CAF50
    style M4 fill:#90EE90
    style M5 fill:#9C27B0,color:#fff
```

**Result**: O(2^n) → O(n)! From exponential to linear by caching results.

### 2. Sum of Array

```php
<?php

function sumArray(array $arr): int {
    // Base case: empty array
    if (empty($arr)) {
        return 0;
    }

    // Recursive case: first element + sum of rest
    return $arr[0] + sumArray(array_slice($arr, 1));
}

echo sumArray([1, 2, 3, 4, 5]); // 15
```

### 3. Reverse a String

```php
<?php

function reverseString(string $str): string {
    // Base case
    if (strlen($str) <= 1) {
        return $str;
    }

    // Recursive case: last char + reverse of rest
    return $str[strlen($str) - 1] . reverseString(substr($str, 0, -1));
}

echo reverseString("hello"); // olleh
```

### 4. Power Function

```php
<?php

function power(int $base, int $exp): int {
    // Base case
    if ($exp === 0) {
        return 1;
    }

    // Recursive case
    return $base * power($base, $exp - 1);
}

// Optimized version - O(log n)
function powerFast(int $base, int $exp): int {
    if ($exp === 0) {
        return 1;
    }

    $half = powerFast($base, (int)($exp / 2));

    if ($exp % 2 === 0) {
        return $half * $half;
    } else {
        return $base * $half * $half;
    }
}

echo powerFast(2, 10); // 1024
```

## Recursion with Data Structures

### Tree Traversal

```php
<?php

class TreeNode {
    public function __construct(
        public mixed $value,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

function sumTree(?TreeNode $node): int {
    // Base case: null node
    if ($node === null) {
        return 0;
    }

    // Recursive case: node value + sum of subtrees
    return $node->value + sumTree($node->left) + sumTree($node->right);
}

function treeHeight(?TreeNode $node): int {
    if ($node === null) {
        return -1;
    }

    return 1 + max(
        treeHeight($node->left),
        treeHeight($node->right)
    );
}
```

### Linked List Reversal

```php
<?php

class ListNode {
    public function __construct(
        public mixed $value,
        public ?ListNode $next = null
    ) {}
}

function reverseList(?ListNode $head): ?ListNode {
    // Base case
    if ($head === null || $head->next === null) {
        return $head;
    }

    // Reverse rest of list
    $newHead = reverseList($head->next);

    // Fix pointers
    $head->next->next = $head;
    $head->next = null;

    return $newHead;
}
```

## Divide and Conquer

Recursion enables divide-and-conquer algorithms:

```mermaid
graph TB
    subgraph "Divide and Conquer Pattern"
        D0["Problem of size n"]
        D1["Divide into<br/>subproblems"]
        D2["Solve subproblems<br/>recursively"]
        D3["Combine solutions"]
        D4["Solution to<br/>original problem"]

        D0 --> D1
        D1 --> D2
        D2 --> D3
        D3 --> D4
    end

    style D0 fill:#2196F3,color:#fff
    style D1 fill:#FFA500
    style D2 fill:#FFD700
    style D3 fill:#90EE90
    style D4 fill:#4CAF50
```

**Pattern**: Divide → Conquer (recurse) → Combine

### Merge Sort

```php
<?php

function mergeSort(array $arr): array {
    // Base case
    if (count($arr) <= 1) {
        return $arr;
    }

    // Divide
    $mid = (int)(count($arr) / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    // Conquer
    return merge(mergeSort($left), mergeSort($right));
}

function merge(array $left, array $right): array {
    $result = [];
    $i = $j = 0;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }

    return array_merge($result, array_slice($left, $i), array_slice($right, $j));
}
```

## Tail Recursion

**Tail recursion**: Recursive call is the last operation

```php
<?php

// Not tail recursive
function factorial($n) {
    if ($n === 0) return 1;
    return $n * factorial($n - 1); // Multiplication after recursive call
}

// Tail recursive
function factorialTail($n, $accumulator = 1) {
    if ($n === 0) {
        return $accumulator;
    }
    return factorialTail($n - 1, $n * $accumulator); // No work after call
}
```

**Benefit**: Some compilers optimize tail recursion to avoid stack overflow (PHP doesn't do this automatically).

## Common Recursive Patterns

### 1. Linear Recursion

Process list/array one element at a time:

```php
<?php

function contains(array $arr, $value): bool {
    if (empty($arr)) {
        return false;
    }

    if ($arr[0] === $value) {
        return true;
    }

    return contains(array_slice($arr, 1), $value);
}
```

### 2. Binary Recursion

Two recursive calls (like binary tree):

```php
<?php

function fibonacci($n) {
    if ($n <= 1) return $n;
    return fibonacci($n - 1) + fibonacci($n - 2); // Two calls
}
```

### 3. Multiple Recursion

More than two recursive calls:

```php
<?php

function countPaths(int $x, int $y): int {
    // Base case: reached destination
    if ($x === 0 && $y === 0) {
        return 1;
    }

    $paths = 0;

    // Try all possible moves
    if ($x > 0) $paths += countPaths($x - 1, $y);
    if ($y > 0) $paths += countPaths($x, $y - 1);

    return $paths;
}
```

## Recursion vs. Iteration

```mermaid
graph TB
    START["Recursion or<br/>Iteration?"]
    Q1{"Working with<br/>trees/graphs?"}
    Q2{"Problem naturally<br/>divides into<br/>subproblems?"}
    Q3{"Deep recursion<br/>possible?<br/>(stack overflow risk)"}
    Q4{"Readability<br/>vs Performance?"}

    START --> Q1
    Q1 -->|"Yes"| REC1["✓ Use Recursion<br/>Trees/graphs natural"]
    Q1 -->|"No"| Q2
    Q2 -->|"Yes"| Q3
    Q2 -->|"No"| ITER1["✓ Use Iteration<br/>Simple sequential"]
    Q3 -->|"Risk"| ITER2["✓ Use Iteration<br/>Avoid stack overflow"]
    Q3 -->|"Safe"| Q4
    Q4 -->|"Readability"| REC2["✓ Use Recursion<br/>Cleaner code"]
    Q4 -->|"Performance"| ITER3["✓ Use Iteration<br/>Faster execution"]

    style START fill:#2196F3,color:#fff
    style REC1 fill:#4CAF50
    style REC2 fill:#4CAF50
    style ITER1 fill:#FF9800
    style ITER2 fill:#FF9800
    style ITER3 fill:#FF9800
```

### When to Use Recursion

**Use recursion when**:
- Problem naturally divides into subproblems
- Working with recursive data structures (trees, graphs)
- Code readability matters
- Problem involves backtracking

**Example**: Tree traversal is much cleaner with recursion

```php
<?php

// Recursive - elegant
function inorder($node) {
    if ($node === null) return [];
    return array_merge(
        inorder($node->left),
        [$node->value],
        inorder($node->right)
    );
}

// Iterative - complex
function inorderIterative($root) {
    $result = [];
    $stack = [];
    $current = $root;

    while ($current !== null || !empty($stack)) {
        while ($current !== null) {
            $stack[] = $current;
            $current = $current->left;
        }
        $current = array_pop($stack);
        $result[] = $current->value;
        $current = $current->right;
    }

    return $result;
}
```

### When to Use Iteration

**Use iteration when**:
- Simple sequential processing
- Stack overflow is a concern
- Performance is critical
- Tail recursion can't be optimized

```php
<?php

// Recursive factorial - risk of stack overflow
function factorialRecursive($n) {
    if ($n === 0) return 1;
    return $n * factorialRecursive($n - 1);
}

// Iterative factorial - safer
function factorialIterative($n) {
    $result = 1;
    for ($i = 2; $i <= $n; $i++) {
        $result *= $i;
    }
    return $result;
}
```

## Debugging Recursive Functions

### 1. Add Tracing

```php
<?php

function factorialTrace($n, $depth = 0) {
    $indent = str_repeat("  ", $depth);
    echo "{$indent}factorial($n) called\n";

    if ($n === 0) {
        echo "{$indent}→ returning 1\n";
        return 1;
    }

    $result = $n * factorialTrace($n - 1, $depth + 1);
    echo "{$indent}→ returning $result\n";
    return $result;
}
```

### 2. Verify Base Case

Always check:
- Is base case reached?
- Does it return correct value?
- Do all paths lead to base case?

## Common Recursion Pitfalls

```mermaid
graph LR
    subgraph "Common Pitfalls"
        P1["❌ Missing<br/>Base Case"]
        P2["❌ Not Making<br/>Progress"]
        P3["❌ Redundant<br/>Computation"]

        P1 -.->|"Result"| R1["Stack Overflow!"]
        P2 -.->|"Result"| R2["Infinite Loop!"]
        P3 -.->|"Result"| R3["O(2^n) Slow!"]
    end

    style P1 fill:#FF6B6B,color:#fff
    style P2 fill:#FF6B6B,color:#fff
    style P3 fill:#FF6B6B,color:#fff
    style R1 fill:#FFA500
    style R2 fill:#FFA500
    style R3 fill:#FFA500
```

### 1. Missing Base Case

```php
<?php

// WRONG - infinite recursion!
function badRecursion($n) {
    return $n + badRecursion($n - 1);
}

// CORRECT
function goodRecursion($n) {
    if ($n === 0) return 0; // Base case!
    return $n + goodRecursion($n - 1);
}
```

### 2. Not Making Progress

```php
<?php

// WRONG - doesn't get closer to base case
function badCountdown($n) {
    if ($n === 0) return;
    badCountdown($n); // Should be $n - 1!
}
```

### 3. Redundant Computation

```php
<?php

// WRONG - recalculates same values
function slowFib($n) {
    if ($n <= 1) return $n;
    return slowFib($n - 1) + slowFib($n - 2);
}

// CORRECT - use memoization
function fastFib($n, &$memo = []) {
    if ($n <= 1) return $n;
    if (isset($memo[$n])) return $memo[$n];
    $memo[$n] = fastFib($n - 1, $memo) + fastFib($n - 2, $memo);
    return $memo[$n];
}
```

## Key Takeaways

- **Recursion** solves problems by breaking them into smaller identical problems
- Every recursive function needs a **base case** and **recursive case**
- **Call stack** stores function calls - deep recursion can overflow
- **Memoization** caches results to avoid redundant computation
- **Tail recursion** can be optimized by compilers
- Use recursion for **tree/graph** problems, backtracking, divide-and-conquer
- Use iteration for **simple loops** and when performance is critical

## Exercises

1. **Greatest Common Divisor (GCD)**: Implement Euclidean algorithm recursively.

2. **Palindrome checker**: Check if string is palindrome using recursion.

3. **Tower of Hanoi**: Solve the classic puzzle recursively.

4. **Generate all permutations**: Generate all permutations of a string.

5. **Count ways to climb stairs**: Each step, you can climb 1 or 2 stairs. Count total ways.

## What's Next?

Recursion is foundational for many advanced algorithms. In Chapter 10, we'll explore **Graph Algorithms**, where recursion powers depth-first search and pathfinding.

---

**Further Reading**:
- [Recursion (Wikipedia)](https://en.wikipedia.org/wiki/Recursion_(computer_science))
- [Master Theorem](https://en.wikipedia.org/wiki/Master_theorem_(analysis_of_algorithms))
- [Dynamic Programming and Memoization](https://en.wikipedia.org/wiki/Dynamic_programming)
