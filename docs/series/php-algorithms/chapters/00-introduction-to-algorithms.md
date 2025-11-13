---
title: "00: Introduction to Algorithms"
description: "Understand what algorithms are, why they matter for PHP developers, and how to think algorithmically."
series: "php-algorithms"
chapter: 0
order: 0
difficulty: "Intermediate"
prerequisites:
  - "Solid understanding of PHP basics"
  - "Familiarity with arrays and loops"
  - "Basic understanding of functions"
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 00</span>
</div>

# Introduction to Algorithms <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

Welcome to **Algorithms for PHP Developers**! In this chapter, we'll explore what algorithms are, why they're essential for writing better PHP applications, and how to start thinking algorithmically.

## What You'll Learn

**Estimated time:** 45 minutes

By the end of this chapter, you will:

- Understand what algorithms are and why they're crucial for PHP developers
- Learn to think algorithmically using a systematic problem-solving framework
- Explore common algorithm categories including searching, sorting, and data structures
- Develop skills to analyze algorithm efficiency and make informed design choices
- Set up your development environment for algorithm practice and testing

## Prerequisites

Before starting this chapter, you should have:

- ✓ Solid understanding of PHP basics *(15 mins review if needed)*
- ✓ Familiarity with arrays and loops *(10 mins review if needed)*
- ✓ Basic understanding of functions *(5 mins review if needed)*

## What Is an Algorithm?

An **algorithm** is simply a step-by-step procedure for solving a problem or accomplishing a task. You use algorithms every day without realizing it:

- Following a recipe to bake a cake
- Getting directions from point A to point B
- Sorting your email by date

In programming, an algorithm is a precise sequence of instructions that transforms input into output:

```php
// A simple algorithm to find the maximum value in an array
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

$numbers = [3, 7, 2, 9, 1, 5];
echo findMax($numbers); // Output: 9
```

This `findMax` algorithm:
1. Starts by assuming the first number is the maximum
2. Compares each subsequent number to the current maximum
3. Updates the maximum when a larger number is found
4. Returns the final maximum value

## Why Should PHP Developers Care About Algorithms?

You might be thinking: "I build web applications, not search engines. Why do I need to know algorithms?"

Here's why algorithms matter for PHP developers:

### 1. **Performance Optimization**

Poor algorithm choices can make your application painfully slow:

```php
// Bad: O(n²) - checking for duplicates
function hasDuplicates(array $items): bool
{
    for ($i = 0; $i < count($items); $i++) {
        for ($j = $i + 1; $j < count($items); $j++) {
            if ($items[$i] === $items[$j]) {
                return true;
            }
        }
    }
    return false;
}

// Good: O(n) - using a hash set
function hasDuplicates(array $items): bool
{
    $seen = [];
    foreach ($items as $item) {
        if (isset($seen[$item])) {
            return true;
        }
        $seen[$item] = true;
    }
    return false;
}
```

With 1,000 items, the first version does ~500,000 comparisons. The second? Just 1,000. That's a 500x speedup!

### 2. **Better Technical Interviews**

Many companies test algorithm knowledge in technical interviews. Understanding algorithms in PHP gives you an edge.

### 3. **Framework Understanding**

Laravel, Symfony, and other frameworks use sophisticated algorithms internally:

- **Laravel Collections** use various sorting and filtering algorithms
- **Routing systems** use tree-based data structures for fast lookups
- **Database ORMs** optimize queries using algorithmic strategies

Understanding these algorithms helps you use frameworks more effectively.

### 4. **Problem-Solving Skills**

Learning algorithms trains you to:
- Break down complex problems into smaller steps
- Think about edge cases and error handling
- Reason about efficiency and resource usage

These skills make you a better developer overall.

## Types of Problems Algorithms Solve

Algorithms help us solve different categories of problems:

### Searching

Finding specific data in a collection:

```php
// Linear search - checks each item
function linearSearch(array $items, $target): int|false
{
    foreach ($items as $index => $item) {
        if ($item === $target) {
            return $index;
        }
    }
    return false;
}

$users = ['Alice', 'Bob', 'Charlie', 'David'];
echo linearSearch($users, 'Charlie'); // Output: 2
```

### Sorting

Arranging data in a specific order:

```php
// Bubble sort - simple but slow for large datasets
function bubbleSort(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                // Swap elements
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }

    return $arr;
}

$numbers = [64, 34, 25, 12, 22, 11, 90];
print_r(bubbleSort($numbers)); // [11, 12, 22, 25, 34, 64, 90]
```

### Data Structure Operations

Managing collections of data efficiently:

```php
// Stack - Last In, First Out (LIFO)
class Stack
{
    private array $items = [];

    public function push($item): void
    {
        $this->items[] = $item;
    }

    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException('Stack is empty');
        }
        return array_pop($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

$stack = new Stack();
$stack->push('First');
$stack->push('Second');
$stack->push('Third');
echo $stack->pop(); // Output: Third (last in, first out)
```

### Graph and Tree Traversal

Navigating hierarchical or networked data:

```php
// Tree structure representing a file system
$fileSystem = [
    'name' => 'root',
    'children' => [
        ['name' => 'var', 'children' => [
            ['name' => 'log', 'children' => []],
            ['name' => 'www', 'children' => []]
        ]],
        ['name' => 'etc', 'children' => []],
        ['name' => 'home', 'children' => []]
    ]
];

// Depth-first traversal
function printTree(array $node, int $depth = 0): void
{
    echo str_repeat('  ', $depth) . $node['name'] . "\n";

    foreach ($node['children'] as $child) {
        printTree($child, $depth + 1);
    }
}

printTree($fileSystem);
```

### String Processing

Manipulating and analyzing text:

```php
// Check if a string is a palindrome
function isPalindrome(string $str): bool
{
    $str = strtolower(preg_replace('/[^a-z0-9]/', '', $str));
    return $str === strrev($str);
}

echo isPalindrome('A man, a plan, a canal: Panama'); // true
echo isPalindrome('hello'); // false
```

## Algorithmic Thinking: A Framework

When faced with a problem, use this systematic approach:

### 1. **Understand the Problem**

- What are the inputs?
- What should the output be?
- Are there constraints or edge cases?

### 2. **Plan Your Approach**

- Can you break the problem into smaller steps?
- Have you solved a similar problem before?
- Can you solve a simpler version first?

### 3. **Consider Efficiency**

- How does performance change with input size?
- What resources (time, memory) do you have?
- Is there a trade-off between speed and simplicity?

### 4. **Implement and Test**

- Start with a working solution (even if inefficient)
- Test with various inputs (normal, edge cases, large datasets)
- Refine and optimize based on results

### 5. **Analyze and Improve**

- Measure actual performance
- Compare to alternative approaches
- Document your decisions

## Example: Algorithmic Thinking in Action

**Problem**: Find duplicate email addresses in a user database.

### Understand
- **Input**: Array of user objects with email addresses
- **Output**: Array of duplicate email addresses
- **Constraints**: Could have 100,000+ users

### Plan
Three approaches:
1. Compare each email to every other email (simple but slow)
2. Sort emails and check adjacent entries (moderate complexity)
3. Use a hash map to track seen emails (fast, uses more memory)

### Consider Efficiency
- Approach 1: O(n²) - too slow for large datasets
- Approach 2: O(n log n) - decent
- Approach 3: O(n) - best time, acceptable memory usage

### Implement

```php
function findDuplicateEmails(array $users): array
{
    $seen = [];
    $duplicates = [];

    foreach ($users as $user) {
        $email = strtolower($user['email']);

        if (isset($seen[$email])) {
            if (!in_array($email, $duplicates)) {
                $duplicates[] = $email;
            }
        } else {
            $seen[$email] = true;
        }
    }

    return $duplicates;
}

$users = [
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com'],
    ['name' => 'Charlie', 'email' => 'alice@example.com'], // Duplicate!
    ['name' => 'David', 'email' => 'david@example.com'],
];

print_r(findDuplicateEmails($users)); // ['alice@example.com']
```

### Analyze
- Time complexity: O(n) - single pass through users
- Space complexity: O(n) - stores emails in hash map
- Works efficiently even with millions of users

## Setting Up Your Development Environment

To follow along with this series, ensure you have:

### PHP Installation

You need PHP 8.1 or higher for modern features:

```bash
php --version
```

If you need to install PHP, visit [php.net](https://www.php.net/downloads.php) or use your system's package manager.

### Code Editor

We recommend **Visual Studio Code** with the PHP Intelephense extension for:
- Syntax highlighting
- Code completion
- Error detection
- Debugging support

### Testing Framework

Install **PHPUnit** for testing your algorithms:

```bash
composer require --dev phpunit/phpunit
```

### Performance Profiling

For benchmarking, you can use PHP's built-in `microtime()`:

```php
$start = microtime(true);

// Your algorithm here
bubbleSort($largeArray);

$end = microtime(true);
$duration = ($end - $start) * 1000; // Convert to milliseconds

echo "Execution time: {$duration}ms\n";
```

## What's Coming Next

In the next chapter, we'll dive deep into **Algorithm Complexity and Big O Notation**. You'll learn to:

- Analyze algorithm efficiency mathematically
- Understand time and space complexity
- Recognize common complexity patterns
- Make informed decisions about algorithm choices

This foundation will help you evaluate every algorithm we study in this series.

## Practice Exercises

Before moving on, try these exercises to reinforce your understanding:

### Exercise 1: Reverse a String

Write a function that reverses a string without using `strrev()`:

```php
function reverseString(string $str): string
{
    // Your code here
}

echo reverseString('hello'); // Should output: olleh
```

<details>
<summary>Solution</summary>

```php
function reverseString(string $str): string
{
    $reversed = '';
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        $reversed .= $str[$i];
    }
    return $reversed;
}
```
</details>

### Exercise 2: Find the Second Largest Number

Write a function that finds the second-largest number in an array:

```php
function findSecondLargest(array $numbers): int|float|null
{
    // Your code here
}

echo findSecondLargest([3, 7, 2, 9, 1, 5]); // Should output: 7
```

<details>
<summary>Solution</summary>

```php
function findSecondLargest(array $numbers): int|float|null
{
    if (count($numbers) < 2) {
        return null;
    }

    $first = $second = PHP_INT_MIN;

    foreach ($numbers as $number) {
        if ($number > $first) {
            $second = $first;
            $first = $number;
        } elseif ($number > $second && $number !== $first) {
            $second = $number;
        }
    }

    return $second === PHP_INT_MIN ? null : $second;
}
```
</details>

### Exercise 3: Count Vowels

Count the number of vowels in a string:

```php
function countVowels(string $str): int
{
    // Your code here
}

echo countVowels('Hello World'); // Should output: 3
```

<details>
<summary>Solution</summary>

```php
function countVowels(string $str): int
{
    $vowels = ['a', 'e', 'i', 'o', 'u'];
    $count = 0;
    $str = strtolower($str);

    for ($i = 0; $i < strlen($str); $i++) {
        if (in_array($str[$i], $vowels)) {
            $count++;
        }
    }

    return $count;
}
```
</details>

## Key Takeaways

- **Algorithms** are step-by-step procedures for solving problems
- **Good algorithms** make your PHP applications faster and more scalable
- **Algorithmic thinking** is a systematic approach to problem-solving
- **Practice** is essential—you'll improve with each algorithm you implement
- **Efficiency matters**, especially as your data grows

## Further Reading

- [PHP Manual: Arrays](https://www.php.net/manual/en/book.array.php)
- [Introduction to Algorithms](https://mitpress.mit.edu/books/introduction-algorithms) by CLRS
- [Algorithm Complexity Cheat Sheet](https://www.bigocheatsheet.com/)

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 00 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-00)**

Files included:
- `01-quick-start-examples.php` - Collection of essential algorithm patterns ready to use
- `02-common-patterns.php` - Fundamental algorithm patterns (two pointers, sliding window, hash maps)
- `03-performance-tips.php` - Practical optimization techniques with benchmarks
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-00
php 01-quick-start-examples.php
```

---

Ready to analyze algorithm efficiency? Continue to [Chapter 01: Algorithm Complexity & Big O Notation](/series/php-algorithms/chapters/01-algorithm-complexity-big-o-notation).
