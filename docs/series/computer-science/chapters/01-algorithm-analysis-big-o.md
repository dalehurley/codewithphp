---
title: "01: Algorithm Analysis and Big O Notation"
description: "Master Big O notation and algorithmic complexity. Learn to analyze time and space complexity, compare algorithm efficiency, and understand O(1), O(n), O(log n), O(n²), and beyond."
series: "computer-science"
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites: ["Computational thinking", "Basic understanding of loops and functions"]
---

# Chapter 01: Algorithm Analysis and Big O Notation

## Introduction

You've written code that works. But is it **efficient**? Will it still work when your dataset grows from 100 items to 1 million? How do you compare two different solutions objectively?

This is where **algorithm analysis** comes in. It's the science of measuring how algorithms perform as input size grows. And the universal language for describing this performance is **Big O notation**.

In this chapter, you'll learn:

- Why algorithm efficiency matters
- How to analyze time and space complexity
- What Big O notation means and how to use it
- Common complexity classes (O(1), O(n), O(log n), O(n²))
- How to compare algorithms objectively

## Why Algorithm Efficiency Matters

Consider two ways to search for a name in a list:

**Approach 1**: Check every name until you find a match
**Approach 2**: If the list is sorted, use binary search

For 10 names, both feel instant. But what about 1 million names?

- **Approach 1**: Checks up to 1,000,000 names (worst case)
- **Approach 2**: Checks up to 20 names (worst case)

This 50,000x difference is why algorithm analysis matters. As data grows, inefficient algorithms become unusable.

## What is Big O Notation?

**Big O notation** describes how an algorithm's runtime or space requirements grow relative to its input size. It answers the question: "How does performance scale?"

The notation looks like this: **O(n)**, **O(log n)**, **O(n²)**, etc.

- **O** stands for "Order of"
- **n** represents the input size
- The expression describes growth rate

### Key Insight

Big O focuses on **worst-case growth rate**, not exact performance. It ignores:

- Constant factors (2n and n are both O(n))
- Lower-order terms (n² + n is just O(n²))
- Specific hardware or language

This makes it a universal, hardware-independent measure of efficiency.

## Common Complexity Classes

Let's explore the most common Big O complexities, from best to worst:

### 1. O(1) — Constant Time

**Definition**: Runtime doesn't change with input size.

**Example**: Accessing an array element by index

```php
<?php

function getFirstElement(array $arr): mixed {
    return $arr[0]; // Always takes the same time
}

$small = [1, 2, 3];
$large = range(1, 1000000);

// Both take the same time:
getFirstElement($small);  // O(1)
getFirstElement($large);  // O(1)
```

**Real-world applications**:
- Hash table lookups
- Array access by index
- Stack push/pop operations

### 2. O(log n) — Logarithmic Time

**Definition**: Runtime grows slowly as input size grows exponentially.

**Example**: Binary search on a sorted array

```php
<?php

function binarySearch(array $arr, int $target): ?int {
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            return $mid; // Found
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1; // Search right half
        } else {
            $right = $mid - 1; // Search left half
        }
    }

    return null; // Not found
}

$numbers = range(1, 1000000); // 1 million sorted numbers
$result = binarySearch($numbers, 987654);
```

**Why it's logarithmic**:
- Each iteration cuts the search space in half
- 1,000,000 items → ~20 comparisons
- 1,000,000,000 items → ~30 comparisons

**Real-world applications**:
- Binary search trees
- Divide-and-conquer algorithms
- Balanced tree operations

### 3. O(n) — Linear Time

**Definition**: Runtime grows proportionally with input size.

**Example**: Linear search

```php
<?php

function linearSearch(array $arr, int $target): ?int {
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return null;
}

$numbers = range(1, 1000);
linearSearch($numbers, 500); // May check up to 1000 items
```

**Real-world applications**:
- Iterating through arrays
- Traversing linked lists
- Finding min/max in unsorted data

### 4. O(n log n) — Linearithmic Time

**Definition**: A combination of linear and logarithmic growth.

**Example**: Efficient sorting algorithms (merge sort, quicksort)

```php
<?php

function mergeSort(array $arr): array {
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

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

$unsorted = [64, 34, 25, 12, 22, 11, 90];
$sorted = mergeSort($unsorted); // O(n log n)
```

**Real-world applications**:
- Efficient sorting (merge sort, quicksort, heapsort)
- Many divide-and-conquer algorithms

### 5. O(n²) — Quadratic Time

**Definition**: Runtime grows with the square of input size.

**Example**: Nested loops (bubble sort, selection sort)

```php
<?php

function bubbleSort(array $arr): array {
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                // Swap
                $temp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $temp;
            }
        }
    }

    return $arr;
}

$numbers = [64, 34, 25, 12, 22, 11, 90];
bubbleSort($numbers); // O(n²)
```

**Why it's quadratic**:
- Outer loop runs n times
- Inner loop runs n times for each outer iteration
- Total: n × n = n²

**Real-world applications**:
- Simple sorting algorithms (bubble, selection, insertion)
- Comparing all pairs in a dataset
- Naive string matching

### 6. O(2ⁿ) — Exponential Time

**Definition**: Runtime doubles with each additional input.

**Example**: Recursive Fibonacci (naive implementation)

```php
<?php

function fibonacci(int $n): int {
    if ($n <= 1) {
        return $n;
    }
    return fibonacci($n - 1) + fibonacci($n - 2);
}

fibonacci(5);  // 15 function calls
fibonacci(10); // 177 function calls
fibonacci(20); // 21,891 function calls
fibonacci(30); // 2,692,537 function calls (very slow!)
```

**Real-world applications**:
- Brute-force password cracking
- Generating all subsets of a set
- Some recursive algorithms (before optimization)

## Comparing Complexities Visually

Here's how different complexities compare:

| Input Size (n) | O(1) | O(log n) | O(n) | O(n log n) | O(n²) | O(2ⁿ) |
|----------------|------|----------|------|------------|-------|-------|
| 10             | 1    | 3        | 10   | 33         | 100   | 1,024 |
| 100            | 1    | 7        | 100  | 664        | 10,000| 1.27×10³⁰ |
| 1,000          | 1    | 10       | 1,000| 9,966      | 1,000,000 | ∞ |
| 1,000,000      | 1    | 20       | 1,000,000 | 20,000,000 | 1,000,000,000,000 | ∞ |

**Key takeaway**: As n grows, differences between complexities become dramatic.

## How to Analyze Algorithm Complexity

Follow these steps to determine Big O:

### Step 1: Identify the input size

What variable represents the size of the data? Usually `n`.

### Step 2: Count operations relative to input size

Look for:
- **Loops**: Each loop level multiplies complexity
- **Recursive calls**: Count how many times the function calls itself
- **Built-in operations**: Know their complexity (e.g., `array_search` is O(n))

### Step 3: Keep the dominant term

Drop constants and lower-order terms:
- `3n + 5` → O(n)
- `n² + n` → O(n²)
- `n log n + n` → O(n log n)

### Example Analysis

```php
<?php

function example1(array $arr): int {
    $sum = 0;                    // O(1)
    foreach ($arr as $num) {     // O(n)
        $sum += $num;            // O(1)
    }
    return $sum;                 // O(1)
}
// Total: O(1) + O(n) + O(1) + O(1) = O(n)

function example2(array $arr): array {
    $result = [];
    foreach ($arr as $i) {       // O(n)
        foreach ($arr as $j) {   // O(n)
            $result[] = $i + $j; // O(1)
        }
    }
    return $result;
}
// Total: O(n) × O(n) = O(n²)

function example3(int $n): int {
    if ($n <= 1) return 1;
    return example3($n - 1) + example3($n - 1);
}
// Each call makes 2 recursive calls: O(2ⁿ)
```

## Space Complexity

Big O also applies to **memory usage**.

```php
<?php

// O(1) space - constant memory
function sumArray(array $arr): int {
    $sum = 0;
    foreach ($arr as $num) {
        $sum += $num;
    }
    return $sum; // Only stores $sum variable
}

// O(n) space - memory grows with input
function doubleArray(array $arr): array {
    $result = [];
    foreach ($arr as $num) {
        $result[] = $num * 2;
    }
    return $result; // Stores copy of entire array
}

// O(n) space - recursive call stack
function factorial(int $n): int {
    if ($n <= 1) return 1;
    return $n * factorial($n - 1); // n recursive calls on stack
}
```

## Practical Example: Choosing the Right Algorithm

**Problem**: Find duplicate values in an array.

### Approach 1: Nested Loop (Brute Force)

```php
<?php

function findDuplicatesV1(array $arr): array {
    $duplicates = [];

    for ($i = 0; $i < count($arr); $i++) {
        for ($j = $i + 1; $j < count($arr); $j++) {
            if ($arr[$i] === $arr[$j] && !in_array($arr[$i], $duplicates)) {
                $duplicates[] = $arr[$i];
            }
        }
    }

    return $duplicates;
}

// Time complexity: O(n²)
// Space complexity: O(n)
```

### Approach 2: Hash Map

```php
<?php

function findDuplicatesV2(array $arr): array {
    $seen = [];
    $duplicates = [];

    foreach ($arr as $value) {
        if (isset($seen[$value]) && !in_array($value, $duplicates)) {
            $duplicates[] = $value;
        }
        $seen[$value] = true;
    }

    return $duplicates;
}

// Time complexity: O(n)
// Space complexity: O(n)
```

**Verdict**: Approach 2 is much faster (O(n) vs O(n²)) with the same space usage!

## Common Mistakes in Big O Analysis

### Mistake 1: Counting exact operations

```php
// WRONG: "This has 5 operations, so it's O(5)"
$sum = $a + $b + $c + $d + $e;

// CORRECT: O(1) — constant number of operations
```

### Mistake 2: Adding complexities incorrectly

```php
// Two sequential O(n) loops
foreach ($arr1 as $val) { /* ... */ } // O(n)
foreach ($arr2 as $val) { /* ... */ } // O(n)

// WRONG: O(n²)
// CORRECT: O(n + n) = O(n)
```

### Mistake 3: Ignoring built-in function complexity

```php
foreach ($arr as $val) {
    if (in_array($val, $other)) { // in_array is O(n)!
        // ...
    }
}

// WRONG: O(n)
// CORRECT: O(n²) — nested O(n) operations
```

## Key Takeaways

- **Big O notation** measures how algorithms scale with input size
- Focus on **worst-case** and **dominant terms**
- **Common complexities**: O(1) < O(log n) < O(n) < O(n log n) < O(n²) < O(2ⁿ)
- **Space complexity** matters too
- Analyze loops, recursion, and built-in functions carefully

## Exercises

1. **Determine the complexity**:
```php
function mystery(array $arr, int $target): bool {
    foreach ($arr as $value) {
        if ($value === $target) {
            return true;
        }
    }
    return false;
}
```

2. **Compare two approaches**:
   - Approach A: O(n log n)
   - Approach B: O(n²)

   For n = 1,000, which is faster and by how much?

3. **Optimize this function**:
```php
function hasDuplicate(array $arr): bool {
    for ($i = 0; $i < count($arr); $i++) {
        for ($j = $i + 1; $j < count($arr); $j++) {
            if ($arr[$i] === $arr[$j]) {
                return true;
            }
        }
    }
    return false;
}
```

4. **Analyze this recursive function**:
```php
function power(int $base, int $exp): int {
    if ($exp === 0) return 1;
    return $base * power($base, $exp - 1);
}
```

## What's Next?

Now that you understand how to analyze algorithms, we'll apply this knowledge to specific **data structures** starting with arrays and lists in Chapter 02. You'll see how different data structures have different performance characteristics for various operations.

---

**Further Reading**:
- [Big-O Cheat Sheet](https://www.bigocheatsheet.com/)
- [A Beginner's Guide to Big O Notation](https://rob-bell.net/2009/06/a-beginners-guide-to-big-o-notation/)
- [Introduction to Algorithms (CLRS)](https://mitpress.mit.edu/books/introduction-algorithms-third-edition)
