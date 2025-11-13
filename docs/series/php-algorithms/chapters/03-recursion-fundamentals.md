---
title: "03: Recursion Fundamentals"
description: "Master recursive thinking and implementation. Learn base cases, recursive cases, and when recursion is the right tool."
series: "php-algorithms"
chapter: 3
order: 3
difficulty: "Intermediate"
prerequisites:
  - "Understanding of functions"
  - "Familiarity with the call stack"
  - "Completion of Chapters 0-2"
---

# Recursion Fundamentals

Recursion is one of the most powerful and elegant problem-solving techniques in computer science. A **recursive function** is one that calls itself to solve smaller instances of the same problem. In this chapter, we'll master recursive thinking and learn when (and when not) to use recursion in PHP.

## What Is Recursion?

Recursion occurs when a function calls itself. It's like looking into two mirrors facing each other—you see infinite reflections, each slightly smaller.

### A Simple Example

```php
function countdown(int $n): void
{
    if ($n <= 0) {
        echo "Blast off!\n";
        return;
    }

    echo "$n...\n";
    countdown($n - 1); // Function calls itself
}

countdown(5);
```

**Output:**
```
5...
4...
3...
2...
1...
Blast off!
```

## The Two Essential Parts of Recursion

Every recursive function must have:

### 1. Base Case (Stopping Condition)

The condition that stops the recursion. Without it, you get infinite recursion and a stack overflow!

```php
// Bad: No base case = infinite recursion
function badRecursion(int $n): int
{
    return badRecursion($n - 1); // Never stops!
}

// Good: Has base case
function goodRecursion(int $n): int
{
    if ($n <= 0) { // BASE CASE
        return 0;
    }
    return goodRecursion($n - 1); // RECURSIVE CASE
}
```

### 2. Recursive Case (Progress Toward Base Case)

The part where the function calls itself with a "smaller" problem, moving toward the base case.

```php
function factorial(int $n): int
{
    // Base case: factorial of 0 or 1 is 1
    if ($n <= 1) {
        return 1;
    }

    // Recursive case: n! = n × (n-1)!
    return $n * factorial($n - 1);
}

echo factorial(5); // 5 × 4 × 3 × 2 × 1 = 120
```

## How Recursion Works: The Call Stack

PHP uses a **call stack** to track function calls:

```php
factorial(3)
│
├─ 3 * factorial(2)
│      │
│      ├─ 2 * factorial(1)
│      │      │
│      │      └─ return 1 (base case)
│      │
│      └─ return 2 * 1 = 2
│
└─ return 3 * 2 = 6
```

Each function call is pushed onto the stack. When a base case is reached, calls start returning (popping off the stack).

### Visualizing the Stack

```php
function visualizeFactorial(int $n, int $depth = 0): int
{
    $indent = str_repeat('  ', $depth);
    echo "{$indent}factorial({$n}) called\n";

    if ($n <= 1) {
        echo "{$indent}Base case reached: returning 1\n";
        return 1;
    }

    $result = $n * visualizeFactorial($n - 1, $depth + 1);
    echo "{$indent}factorial({$n}) returning {$result}\n";
    return $result;
}

visualizeFactorial(4);
```

**Output:**
```
factorial(4) called
  factorial(3) called
    factorial(2) called
      factorial(1) called
      Base case reached: returning 1
    factorial(2) returning 2
  factorial(3) returning 6
factorial(4) returning 24
```

## Classic Recursive Problems

### Fibonacci Sequence

Each number is the sum of the two preceding ones: 0, 1, 1, 2, 3, 5, 8, 13...

```php
function fibonacci(int $n): int
{
    // Base cases
    if ($n === 0) return 0;
    if ($n === 1) return 1;

    // Recursive case
    return fibonacci($n - 1) + fibonacci($n - 2);
}

echo fibonacci(6); // 8
```

**Warning:** This is inefficient (O(2ⁿ))! We'll optimize it later with memoization.

### Sum of Array

```php
function sumArray(array $arr): int
{
    // Base case: empty array
    if (empty($arr)) {
        return 0;
    }

    // Recursive case: first element + sum of rest
    $first = array_shift($arr);
    return $first + sumArray($arr);
}

echo sumArray([1, 2, 3, 4, 5]); // 15
```

### Power Function

```php
function power(int $base, int $exponent): int
{
    // Base case: anything to the power of 0 is 1
    if ($exponent === 0) {
        return 1;
    }

    // Recursive case: base × base^(exponent-1)
    return $base * power($base, $exponent - 1);
}

echo power(2, 5); // 2^5 = 32
```

### Reverse a String

```php
function reverseString(string $str): string
{
    // Base case: empty or single character
    if (strlen($str) <= 1) {
        return $str;
    }

    // Recursive case: last char + reverse of remaining string
    return substr($str, -1) . reverseString(substr($str, 0, -1));
}

echo reverseString('hello'); // 'olleh'
```

## Recursion vs Iteration

Many recursive problems can be solved iteratively. Let's compare:

### Factorial: Recursive vs Iterative

```php
// Recursive
function factorialRecursive(int $n): int
{
    if ($n <= 1) return 1;
    return $n * factorialRecursive($n - 1);
}

// Iterative
function factorialIterative(int $n): int
{
    $result = 1;
    for ($i = 2; $i <= $n; $i++) {
        $result *= $i;
    }
    return $result;
}
```

**Comparison:**
- **Recursive**: More elegant, easier to understand for some problems
- **Iterative**: Usually faster, uses less memory (no stack overhead)

### When to Use Recursion

✅ **Use recursion when:**
- Problem naturally divides into smaller subproblems (tree traversal, divide-and-conquer)
- Code is clearer and more maintainable
- Stack depth won't be excessive

❌ **Avoid recursion when:**
- Simple iteration is clearer
- Stack depth could be very deep (risk of stack overflow)
- Performance is critical and iteration is faster

## Optimizing Recursion: Tail Recursion

**Tail recursion** occurs when the recursive call is the last operation in the function. Some languages optimize this, but PHP doesn't automatically.

```php
// Not tail recursive: operation after recursive call
function factorial(int $n): int
{
    if ($n <= 1) return 1;
    return $n * factorial($n - 1); // Multiplication happens AFTER return
}

// Tail recursive: no operation after recursive call
function factorialTail(int $n, int $accumulator = 1): int
{
    if ($n <= 1) {
        return $accumulator;
    }
    return factorialTail($n - 1, $n * $accumulator); // Nothing after this
}
```

While PHP doesn't optimize tail recursion automatically, it's still a good pattern to know.

## Common Recursive Patterns

### 1. Linear Recursion

Each function call makes one recursive call:

```php
function countDown(int $n): void
{
    if ($n <= 0) return;
    echo "$n ";
    countDown($n - 1); // One recursive call
}
```

### 2. Binary Recursion

Each function call makes two recursive calls:

```php
function fibonacci(int $n): int
{
    if ($n <= 1) return $n;
    return fibonacci($n - 1) + fibonacci($n - 2); // Two calls!
}
```

### 3. Multiple Recursion

Function makes many recursive calls:

```php
function printCombinations(array $items, int $k, array $current = []): void
{
    if ($k === 0) {
        echo implode(', ', $current) . "\n";
        return;
    }

    foreach ($items as $item) {
        printCombinations($items, $k - 1, array_merge($current, [$item]));
    }
}
```

## Recursive Data Structures

Some data structures are inherently recursive:

### Directory Tree Traversal

```php
function listFiles(string $directory, int $depth = 0): void
{
    $indent = str_repeat('  ', $depth);

    $items = scandir($directory);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $directory . '/' . $item;
        echo $indent . $item . "\n";

        // Recursive case: if directory, traverse it
        if (is_dir($path)) {
            listFiles($path, $depth + 1);
        }
    }
}

listFiles('./docs');
```

### Nested Array Sum

```php
function sumNested(array $arr): int
{
    $sum = 0;

    foreach ($arr as $item) {
        if (is_array($item)) {
            // Recursive case: nested array
            $sum += sumNested($item);
        } else {
            // Base case: single value
            $sum += $item;
        }
    }

    return $sum;
}

$nested = [1, [2, 3, [4, 5]], 6, [7, [8, 9]]];
echo sumNested($nested); // 45
```

## Avoiding Stack Overflow

PHP has a limited call stack. Deep recursion can cause stack overflow:

```php
// This will likely crash with large n
function deepRecursion(int $n): int
{
    if ($n <= 0) return 0;
    return 1 + deepRecursion($n - 1);
}

// Stack overflow!
// deepRecursion(100000);
```

### Solution 1: Use Iteration

```php
function iterativeCount(int $n): int
{
    $count = 0;
    for ($i = 0; $i < $n; $i++) {
        $count++;
    }
    return $count;
}

echo iterativeCount(100000); // Works fine!
```

### Solution 2: Increase Stack Size (Not Recommended)

```php
ini_set('xdebug.max_nesting_level', 10000); // Requires Xdebug
```

Better to redesign with iteration or tail recursion where possible.

## Memoization: Optimizing Recursive Functions

**Memoization** caches results to avoid redundant calculations:

```php
// Slow: O(2ⁿ) - recalculates same values
function fibonacciSlow(int $n): int
{
    if ($n <= 1) return $n;
    return fibonacciSlow($n - 1) + fibonacciSlow($n - 2);
}

// Fast: O(n) - caches results
function fibonacciFast(int $n, array &$memo = []): int
{
    if ($n <= 1) return $n;

    if (isset($memo[$n])) {
        return $memo[$n]; // Return cached result
    }

    $memo[$n] = fibonacciFast($n - 1, $memo) + fibonacciFast($n - 2, $memo);
    return $memo[$n];
}

// Compare performance
$start = microtime(true);
echo fibonacciSlow(35) . "\n"; // Takes several seconds
echo "Time: " . (microtime(true) - $start) . "s\n";

$start = microtime(true);
echo fibonacciFast(35) . "\n"; // Nearly instant!
echo "Time: " . (microtime(true) - $start) . "s\n";
```

We'll explore memoization deeply in the Dynamic Programming section.

## Real-World PHP Examples

### JSON Validation

```php
function validateJSON($data): bool
{
    if (is_scalar($data) || $data === null) {
        return true; // Base case: primitive types are valid
    }

    if (is_array($data)) {
        foreach ($data as $item) {
            if (!validateJSON($item)) { // Recursive validation
                return false;
            }
        }
        return true;
    }

    if (is_object($data)) {
        foreach ($data as $value) {
            if (!validateJSON($value)) { // Recursive validation
                return false;
            }
        }
        return true;
    }

    return false;
}

$data = ['name' => 'John', 'nested' => ['age' => 30, 'tags' => ['php', 'dev']]];
echo validateJSON($data) ? 'Valid' : 'Invalid';
```

### Menu Hierarchy Rendering

```php
function renderMenu(array $items, int $depth = 0): string
{
    $html = str_repeat('  ', $depth) . "<ul>\n";

    foreach ($items as $item) {
        $html .= str_repeat('  ', $depth + 1) . "<li>{$item['title']}";

        // Recursive case: render children
        if (!empty($item['children'])) {
            $html .= "\n" . renderMenu($item['children'], $depth + 2);
            $html .= str_repeat('  ', $depth + 1);
        }

        $html .= "</li>\n";
    }

    $html .= str_repeat('  ', $depth) . "</ul>\n";
    return $html;
}

$menu = [
    ['title' => 'Home', 'children' => []],
    ['title' => 'Products', 'children' => [
        ['title' => 'Electronics', 'children' => []],
        ['title' => 'Clothing', 'children' => []]
    ]],
    ['title' => 'About', 'children' => []]
];

echo renderMenu($menu);
```

## Practice Exercises

### Exercise 1: Greatest Common Divisor (GCD)

Use Euclid's algorithm recursively:

```php
function gcd(int $a, int $b): int
{
    // Your code here
}

echo gcd(48, 18); // Should output: 6
```

<details>
<summary>Solution</summary>

```php
function gcd(int $a, int $b): int
{
    // Base case: if b is 0, GCD is a
    if ($b === 0) {
        return $a;
    }

    // Recursive case: GCD(a, b) = GCD(b, a % b)
    return gcd($b, $a % $b);
}
```
</details>

### Exercise 2: Palindrome Checker

Check if a string is a palindrome recursively:

```php
function isPalindrome(string $str): bool
{
    // Your code here
}

echo isPalindrome('racecar') ? 'Yes' : 'No'; // Should output: Yes
```

<details>
<summary>Solution</summary>

```php
function isPalindrome(string $str): bool
{
    // Remove non-alphanumeric and convert to lowercase
    $str = strtolower(preg_replace('/[^a-z0-9]/', '', $str));

    // Base case: empty or single character
    if (strlen($str) <= 1) {
        return true;
    }

    // Check if first and last characters match
    if ($str[0] !== $str[strlen($str) - 1]) {
        return false;
    }

    // Recursive case: check middle substring
    return isPalindrome(substr($str, 1, -1));
}
```
</details>

### Exercise 3: Flatten Nested Array

Flatten a multi-dimensional array into a single-level array:

```php
function flatten(array $arr): array
{
    // Your code here
}

$nested = [1, [2, [3, 4], 5], 6];
print_r(flatten($nested)); // Should output: [1, 2, 3, 4, 5, 6]
```

<details>
<summary>Solution</summary>

```php
function flatten(array $arr): array
{
    $result = [];

    foreach ($arr as $item) {
        if (is_array($item)) {
            // Recursive case: merge flattened nested array
            $result = array_merge($result, flatten($item));
        } else {
            // Base case: add single item
            $result[] = $item;
        }
    }

    return $result;
}
```
</details>

## Key Takeaways

- **Recursion** is when a function calls itself to solve smaller subproblems
- Every recursive function needs a **base case** (stopping condition) and **recursive case** (progress toward base)
- The **call stack** tracks recursive calls; too many can cause stack overflow
- **Memoization** can dramatically improve recursive performance
- Many recursive problems can be solved iteratively—choose based on clarity and performance
- Recursion shines with naturally recursive data structures (trees, nested data)

## What's Next

In the next chapter, we'll explore **Problem-Solving Strategies** that combine recursion with other techniques like divide-and-conquer, backtracking, and dynamic programming.

---

Continue to [Chapter 04: Problem-Solving Strategies](/series/php-algorithms/chapters/04-problem-solving-strategies).
