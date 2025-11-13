---
title: "01: Algorithm Complexity & Big O Notation"
description: "Learn to analyze algorithm efficiency using Big O notation. Understand time and space complexity with practical PHP examples."
series: "php-algorithms"
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites:
  - "Understanding of basic PHP syntax"
  - "Familiarity with loops and functions"
  - "Completion of Chapter 0"
---

# Algorithm Complexity & Big O Notation

In the previous chapter, we saw that some algorithms are faster than others. But how do we measure and compare algorithm efficiency? Enter **Big O notation**—the language we use to describe how algorithms scale.

## Why Algorithm Complexity Matters

Imagine you're building a user search feature for your PHP application:

```php
// Version 1: Linear search - O(n)
function findUserLinear(array $users, string $email): ?array
{
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

// Version 2: Hash lookup - O(1)
function findUserHash(array $usersByEmail, string $email): ?array
{
    return $usersByEmail[$email] ?? null;
}
```

With 10 users, both versions feel instant. But with 1,000,000 users:
- **Linear search**: Might check 500,000 users on average
- **Hash lookup**: Always checks ~1 user

This is why complexity analysis matters—**it predicts how your code performs as data grows**.

## What Is Big O Notation?

**Big O notation** describes how an algorithm's runtime or memory usage grows relative to input size. It answers: "As my input gets larger, how much slower does my code get?"

### The Basics

Big O uses this format: **O(expression)**

- **O(1)**: Constant time—stays the same regardless of input size
- **O(n)**: Linear time—grows proportionally with input size
- **O(n²)**: Quadratic time—grows with the square of input size

The **n** represents input size (number of elements, string length, etc.).

### An Analogy

Think of Big O like describing how long it takes to find a book:

- **O(1)**: You know exactly where it is—grab it instantly
- **O(log n)**: Use the library catalog to narrow down the section
- **O(n)**: Check every shelf one by one
- **O(n²)**: Check every shelf, and for each book, flip through every page

## Common Time Complexities

Let's explore the most common complexities you'll encounter:

### O(1) - Constant Time

Operations that take the same time regardless of input size:

```php
// Array access by key - O(1)
function getFirstElement(array $arr): mixed
{
    return $arr[0];
}

// Hash table lookup - O(1)
function getUserById(array $users, int $id): ?array
{
    return $users[$id] ?? null;
}

// Simple arithmetic - O(1)
function calculateDiscount(float $price): float
{
    return $price * 0.1;
}
```

**Key insight**: The operation takes the same amount of time whether you have 10 items or 10 million.

### O(log n) - Logarithmic Time

Algorithms that halve the problem size with each step:

```php
// Binary search - O(log n)
function binarySearch(array $sorted, int $target): int|false
{
    $left = 0;
    $right = count($sorted) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($sorted[$mid] === $target) {
            return $mid;
        } elseif ($sorted[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return false;
}

// Searching 1,000,000 items takes only ~20 steps!
$numbers = range(1, 1000000);
$index = binarySearch($numbers, 742518);
```

**Key insight**: Doubling the input size only adds one more step. Very efficient!

### O(n) - Linear Time

Algorithms that process each element once:

```php
// Sum array - O(n)
function sum(array $numbers): int|float
{
    $total = 0;
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

// Find maximum - O(n)
function findMax(array $numbers): int|float
{
    $max = $numbers[0];
    foreach ($numbers as $number) {
        if ($number > $max) {
            $max = $number;
        }
    }
    return $max;
}

// Filter array - O(n)
function filterEven(array $numbers): array
{
    $result = [];
    foreach ($numbers as $number) {
        if ($number % 2 === 0) {
            $result[] = $number;
        }
    }
    return $result;
}
```

**Key insight**: Doubling the input size doubles the runtime.

### O(n log n) - Linearithmic Time

Efficient sorting algorithms fall into this category:

```php
// Merge sort - O(n log n)
function mergeSort(array $arr): array
{
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

function merge(array $left, array $right): array
{
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

**Key insight**: Much faster than O(n²) for sorting, but slower than O(n).

### O(n²) - Quadratic Time

Nested loops over the same data:

```php
// Bubble sort - O(n²)
function bubbleSort(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }

    return $arr;
}

// Find all pairs - O(n²)
function findAllPairs(array $items): array
{
    $pairs = [];

    for ($i = 0; $i < count($items); $i++) {
        for ($j = $i + 1; $j < count($items); $j++) {
            $pairs[] = [$items[$i], $items[$j]];
        }
    }

    return $pairs;
}
```

**Key insight**: Doubling the input size quadruples the runtime. Gets slow quickly!

### O(2ⁿ) - Exponential Time

Algorithms that double in runtime with each additional input:

```php
// Fibonacci (naive recursive) - O(2ⁿ)
function fibonacci(int $n): int
{
    if ($n <= 1) {
        return $n;
    }

    return fibonacci($n - 1) + fibonacci($n - 2);
}

// This is VERY slow for large n!
// fibonacci(40) might take seconds
// fibonacci(50) might take hours!
```

**Key insight**: Avoid exponential algorithms for anything but tiny inputs.

## Complexity Comparison Chart

Here's how these complexities compare with different input sizes:

| n (input) | O(1) | O(log n) | O(n) | O(n log n) | O(n²) | O(2ⁿ) |
|-----------|------|----------|------|------------|-------|-------|
| 10        | 1    | 3        | 10   | 33         | 100   | 1,024 |
| 100       | 1    | 7        | 100  | 664        | 10,000| ~10³⁰ |
| 1,000     | 1    | 10       | 1,000| 9,966      | 1M    | ∞     |
| 10,000    | 1    | 13       | 10K  | 130K       | 100M  | ∞     |

Notice how O(2ⁿ) becomes unusable very quickly!

## Space Complexity

Big O also describes memory usage:

```php
// O(1) space - only uses a fixed amount of extra memory
function sumArray(array $numbers): int
{
    $total = 0; // Only one variable
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

// O(n) space - creates a new array proportional to input
function doubleValues(array $numbers): array
{
    $doubled = []; // New array grows with input
    foreach ($numbers as $number) {
        $doubled[] = $number * 2;
    }
    return $doubled;
}

// O(n) space - recursive call stack
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }
    return $n * factorial($n - 1); // Stack grows with n
}
```

## Analyzing Real PHP Code

Let's analyze complexity for a practical example:

```php
class UserRepository
{
    private array $users = [];

    // O(1) - direct array access
    public function findById(int $id): ?array
    {
        return $this->users[$id] ?? null;
    }

    // O(n) - must check each user
    public function findByEmail(string $email): ?array
    {
        foreach ($this->users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    // O(n) - must process each user
    public function findActive(): array
    {
        $active = [];
        foreach ($this->users as $user) {
            if ($user['active']) {
                $active[] = $user;
            }
        }
        return $active;
    }

    // O(n²) - nested loops!
    public function findCommonFriends(int $userId1, int $userId2): array
    {
        $user1Friends = $this->users[$userId1]['friends'];
        $user2Friends = $this->users[$userId2]['friends'];
        $common = [];

        foreach ($user1Friends as $friend1) {
            foreach ($user2Friends as $friend2) {
                if ($friend1 === $friend2) {
                    $common[] = $friend1;
                }
            }
        }

        return $common;
    }

    // Better: O(n) using hash lookup
    public function findCommonFriendsOptimized(int $userId1, int $userId2): array
    {
        $user1Friends = $this->users[$userId1]['friends'];
        $user2Friends = $this->users[$userId2]['friends'];

        // Create hash set - O(n)
        $friendSet = array_flip($user1Friends);

        // Check membership - O(n)
        $common = [];
        foreach ($user2Friends as $friend) {
            if (isset($friendSet[$friend])) {
                $common[] = $friend;
            }
        }

        return $common;
    }
}
```

## Rules for Calculating Big O

### Rule 1: Drop Constants

O(2n) → O(n)
O(500) → O(1)

```php
// Both are O(n), even though one does twice the work
function example1(array $arr): void {
    foreach ($arr as $item) { }
}

function example2(array $arr): void {
    foreach ($arr as $item) { }
    foreach ($arr as $item) { }
}
```

### Rule 2: Drop Non-Dominant Terms

O(n² + n) → O(n²)
O(n + log n) → O(n)

```php
// This is O(n²), not O(n² + n)
function example(array $arr): void {
    foreach ($arr as $item) { } // O(n)

    foreach ($arr as $item1) {  // O(n²)
        foreach ($arr as $item2) { }
    }
}
```

### Rule 3: Different Inputs = Different Variables

```php
// This is O(a + b), not O(n)
function mergeTwoArrays(array $a, array $b): array {
    $result = [];

    foreach ($a as $item) {
        $result[] = $item;
    }

    foreach ($b as $item) {
        $result[] = $item;
    }

    return $result;
}

// This is O(a * b), not O(n²)
function findCommonElements(array $a, array $b): array {
    $common = [];

    foreach ($a as $itemA) {
        foreach ($b as $itemB) {
            if ($itemA === $itemB) {
                $common[] = $itemA;
            }
        }
    }

    return $common;
}
```

## Best, Worst, and Average Case

Some algorithms have different complexities depending on input:

```php
function linearSearch(array $arr, $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

// Best case: O(1) - target is first element
// Worst case: O(n) - target is last or not present
// Average case: O(n/2) → O(n)
```

We typically focus on **worst-case complexity** because it guarantees performance.

## Practical Tips for PHP Developers

### 1. Know Your Built-in Functions

```php
// in_array() - O(n)
if (in_array($needle, $haystack)) { }

// isset() - O(1) for arrays
if (isset($array[$key])) { }

// array_search() - O(n)
$key = array_search($value, $array);

// sort() - O(n log n)
sort($array);
```

### 2. Use Hash Lookups When Possible

```php
// Bad: O(n) for each check
$validEmails = ['user1@example.com', 'user2@example.com'];
if (in_array($email, $validEmails)) { }

// Good: O(1) for each check
$validEmails = [
    'user1@example.com' => true,
    'user2@example.com' => true
];
if (isset($validEmails[$email])) { }
```

### 3. Watch for Hidden Loops

```php
// This looks like O(n) but is actually O(n²)!
function joinWithCommas(array $items): string
{
    $result = '';
    foreach ($items as $item) {
        $result .= $item . ','; // String concatenation is O(n)
    }
    return rtrim($result, ',');
}

// Better: O(n)
function joinWithCommas(array $items): string
{
    return implode(',', $items);
}
```

## Practice Problems

### Problem 1: Analyze This Code

What's the time complexity?

```php
function mystery(array $arr): int
{
    $count = 0;

    for ($i = 0; $i < count($arr); $i++) {
        if ($arr[$i] % 2 === 0) {
            $count++;
        }
    }

    for ($i = 0; $i < count($arr); $i++) {
        if ($arr[$i] % 3 === 0) {
            $count++;
        }
    }

    return $count;
}
```

<details>
<summary>Answer</summary>
O(n) - Two sequential loops, each O(n), so O(n + n) = O(n)
</details>

### Problem 2: Optimize This

Improve the complexity:

```php
// Current: O(n²)
function hasDuplicate(array $arr): bool
{
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

<details>
<summary>Solution</summary>

```php
// Optimized: O(n)
function hasDuplicate(array $arr): bool
{
    $seen = [];
    foreach ($arr as $value) {
        if (isset($seen[$value])) {
            return true;
        }
        $seen[$value] = true;
    }
    return false;
}
```
</details>

## Key Takeaways

- **Big O notation** describes how algorithms scale with input size
- Focus on **worst-case complexity** for reliable performance guarantees
- **Common complexities**: O(1) < O(log n) < O(n) < O(n log n) < O(n²) < O(2ⁿ)
- **Space complexity** is just as important as time complexity
- **Optimize smartly**: Profile first, then optimize bottlenecks
- **Know your tools**: Understand PHP's built-in function complexities

## What's Next

In the next chapter, we'll build a **benchmarking framework** to actually measure algorithm performance in PHP. You'll learn to validate your complexity analysis with real data.

---

Continue to [Chapter 02: Benchmarking & Performance Testing](/series/php-algorithms/chapters/02-benchmarking-performance-testing).
