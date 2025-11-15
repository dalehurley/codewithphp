---
title: "00: Introduction to Algorithms"
description: "Understand what algorithms are, why they matter for PHP developers, and how to think algorithmically"
series: "php-algorithms"
chapter: 0
order: 0
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/02-variables-data-types-and-constants"
  - "/series/php-basics/chapters/03-control-structures"
  - "/series/php-basics/chapters/04-understanding-and-using-functions"
  - "/series/php-basics/chapters/06-deep-dive-into-arrays"
---
![00: Introduction to Algorithms](/images/php-algorithms/chapter-00-introduction-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 00</span>
</div>

# Chapter 00: Introduction to Algorithms

## Overview

Welcome to **Algorithms for PHP Developers**! This chapter introduces algorithms and why they matter for PHP developers. You'll learn what algorithms are, explore common problem types they solve, and develop a systematic framework for thinking algorithmically.

Understanding algorithms helps you write faster, more efficient PHP applications. Whether you're optimizing database queries, building search features, or preparing for technical interviews, algorithmic thinking is an essential skill.

By the end of this chapter, you'll have a solid foundation in algorithmic thinking and be ready to dive deep into algorithm complexity analysis in the next chapter.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Completion of [Chapter 02: Variables, Data Types, and Constants](/series/php-basics/chapters/02-variables-data-types-and-constants) or equivalent understanding
- Completion of [Chapter 03: Control Structures](/series/php-basics/chapters/03-control-structures) or familiarity with loops and conditionals
- Completion of [Chapter 04: Understanding and Using Functions](/series/php-basics/chapters/04-understanding-and-using-functions) or basic understanding of functions and type hints
- Completion of [Chapter 06: Deep Dive into Arrays](/series/php-basics/chapters/06-deep-dive-into-arrays) or familiarity with array operations

**Estimated Time**: ~55 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version
```

## What You'll Build

By the end of this chapter, you will have:

- Understanding of what algorithms are and why they matter for PHP developers
- A systematic problem-solving framework for approaching algorithmic challenges
- Knowledge of common algorithm categories (searching, sorting, data structures)
- Working examples of algorithms including finding maximum values, checking duplicates, and processing strings
- A development environment set up for algorithm practice and testing
- Experience implementing three practice exercises with solutions

## Objectives

- Understand what algorithms are and why they're crucial for PHP developers
- Learn to think algorithmically using a systematic problem-solving framework
- Explore common algorithm categories including searching, sorting, and data structures
- Develop skills to analyze algorithm efficiency and make informed design choices
- Set up your development environment for algorithm practice and testing

## Step 1: Understanding What Algorithms Are (~5 min)

### Goal

Learn what algorithms are and see your first algorithm in action.

### Actions

1. **Understand the concept**: An algorithm is a step-by-step procedure for solving a problem. You use algorithms every day:
   - Following a recipe to bake a cake
   - Getting directions from point A to point B
   - Sorting your email by date

2. **See a simple algorithm**: In programming, an algorithm transforms input into output. Here's a complete example:

```php
# filename: find-max.php
<?php

declare(strict_types=1);

// A simple algorithm to find the maximum value in an array
function findMax(array $numbers): int|float
{
    if (empty($numbers)) {
        throw new InvalidArgumentException('Array cannot be empty');
    }

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

3. **Run the example**: Save this code to `find-max.php` and run it:

```bash
# Run the algorithm example
php find-max.php
```

### Expected Result

```
9
```

### Why It Works

The `findMax` algorithm works by:
1. Starting with the first number as the initial maximum
2. Comparing each subsequent number to the current maximum
3. Updating the maximum when a larger number is found
4. Returning the final maximum value after checking all numbers

This is a **linear search** pattern—we examine each element once, making it an O(n) algorithm where n is the array size.

### Troubleshooting

- **Error: "Array is empty"** — The function now includes error handling; ensure you pass a non-empty array
- **Error: "InvalidArgumentException"** — This means the array was empty; check your input data
- **Wrong result** — Verify the array contains numeric values; strings will be compared lexicographically
- **Type errors** — Ensure all array elements are numeric (int or float) for accurate comparison

## Step 2: Understanding Why Algorithms Matter for PHP Developers (~10 min)

### Goal

Learn why algorithms are essential for PHP developers, even when building web applications.

### Actions

1. **Consider the performance impact**: You might think "I build web applications, not search engines. Why do I need algorithms?" Here's why they matter:

2. **Compare algorithm performance**: See how algorithm choice dramatically affects performance:

```php
# filename: duplicate-check-comparison.php
<?php

declare(strict_types=1);

// Bad: O(n²) - checking for duplicates
// Note: We cache count() to avoid calling it repeatedly in the loop
function hasDuplicatesSlow(array $items): bool
{
    $n = count($items);
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($items[$i] === $items[$j]) {
                return true;
            }
        }
    }
    return false;
}

// Good: O(n) - using a hash set
function hasDuplicatesFast(array $items): bool
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

// Test with 1,000 items
$largeArray = range(1, 1000);
$largeArray[] = 500; // Add duplicate

$start = microtime(true);
hasDuplicatesSlow($largeArray);
$slowTime = (microtime(true) - $start) * 1000;

$start = microtime(true);
hasDuplicatesFast($largeArray);
$fastTime = (microtime(true) - $start) * 1000;

echo "Slow version: {$slowTime}ms\n";
echo "Fast version: {$fastTime}ms\n";
```

### Expected Result

```
Slow version: ~500ms (approximately)
Fast version: ~0.1ms (approximately)
```

### Why It Works

**Performance Optimization**: With 1,000 items, the slow version does ~500,000 comparisons (n²/2). The fast version? Just 1,000 (n). That's a 500x speedup!

**Better Technical Interviews**: Many companies test algorithm knowledge. Understanding algorithms in PHP gives you an edge.

**Framework Understanding**: Laravel, Symfony, and other frameworks use sophisticated algorithms:
- **Laravel Collections** use various sorting and filtering algorithms
- **Routing systems** use tree-based data structures for fast lookups
- **Database ORMs** optimize queries using algorithmic strategies

**Problem-Solving Skills**: Learning algorithms trains you to break down complex problems, think about edge cases, and reason about efficiency—skills that make you a better developer overall.

### Troubleshooting

- **Performance difference not noticeable** — Try with larger arrays (10,000+ items) to see the difference
- **Memory error with large arrays** — The fast version uses more memory (O(n) space) but is much faster
- **Type errors** — Ensure array items are comparable (same type) for accurate duplicate detection

## Step 3: Understanding Algorithms vs Data Structures (~5 min)

### Goal

Distinguish between algorithms and data structures, and understand how they work together.

### Actions

1. **Understand the difference**:
   - **Algorithm**: A step-by-step procedure for solving a problem (the "how")
   - **Data Structure**: A way to organize and store data (the "what")
   - They work together: algorithms operate on data structures

2. **See examples**:

```php
# filename: algorithm-vs-datastructure.php
<?php

declare(strict_types=1);

// DATA STRUCTURE: Stack (how data is organized)
class Stack
{
    private array $items = [];

    public function push(mixed $item): void
    {
        $this->items[] = $item;
    }

    public function pop(): mixed
    {
        return array_pop($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

// ALGORITHM: Reverse a string using a stack (the procedure)
function reverseWithStack(string $str): string
{
    $stack = new Stack();
    
    // Push all characters onto stack
    for ($i = 0; $i < strlen($str); $i++) {
        $stack->push($str[$i]);
    }
    
    // Pop all characters (reverses order)
    $reversed = '';
    while (!$stack->isEmpty()) {
        $reversed .= $stack->pop();
    }
    
    return $reversed;
}

echo reverseWithStack('hello'); // Output: olleh
```

### Expected Result

You'll understand that:
- **Data structures** organize data (arrays, stacks, trees, hash maps)
- **Algorithms** process data (sorting, searching, traversing)
- They're complementary: good algorithms need appropriate data structures

### Why It Works

Think of it like cooking:
- **Data structure** = ingredients organized in containers (bowls, pans)
- **Algorithm** = recipe steps (mix, bake, serve)

You need both: the right containers (data structures) and the right steps (algorithms) to solve problems efficiently.

### Troubleshooting

- **Confused about the difference** — Remember: data structure = storage, algorithm = process
- **Not sure which to learn first** — Start with algorithms on simple arrays, then learn data structures as needed
- **Overthinking it** — For now, just know they're different but work together

## Step 4: Exploring Types of Problems Algorithms Solve (~15 min)

### Goal

Explore common algorithm categories and see examples of each type.

### Actions

1. **Understand algorithm categories**: Algorithms solve different types of problems. Let's explore the main categories:

2. **Searching algorithms**: Finding specific data in a collection:

```php
# filename: linear-search.php
<?php

declare(strict_types=1);

// Linear search - checks each item
function linearSearch(array $items, mixed $target): int|false
{
    foreach ($items as $index => $item) {
        if ($item === $target) {
            return $index;
        }
    }
    return false;
}

$users = ['Alice', 'Bob', 'Charlie', 'David'];
$result = linearSearch($users, 'Charlie');
echo $result !== false ? "Found at index: $result\n" : "Not found\n";
```

3. **Sorting algorithms**: Arranging data in a specific order:

```php
# filename: bubble-sort.php
<?php

declare(strict_types=1);

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
$sorted = bubbleSort($numbers);
print_r($sorted); // [11, 12, 22, 25, 34, 64, 90]
```

4. **Data structure operations**: Managing collections of data efficiently:

```php
# filename: stack.php
<?php

declare(strict_types=1);

// Stack - Last In, First Out (LIFO)
class Stack
{
    private array $items = [];

    public function push(mixed $item): void
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
echo $stack->pop() . "\n"; // Output: Third (last in, first out)
```

5. **Graph and tree traversal**: Navigating hierarchical or networked data:

```php
# filename: tree-traversal.php
<?php

declare(strict_types=1);

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

6. **String processing**: Manipulating and analyzing text:

```php
# filename: palindrome.php
<?php

declare(strict_types=1);

// Check if a string is a palindrome
function isPalindrome(string $str): bool
{
    $str = strtolower(preg_replace('/[^a-z0-9]/', '', $str));
    return $str === strrev($str);
}

echo isPalindrome('A man, a plan, a canal: Panama') ? "true\n" : "false\n"; // true
echo isPalindrome('hello') ? "true\n" : "false\n"; // false
```

### Expected Result

For the tree traversal example:

```
root
  var
    log
    www
  etc
  home
```

### Why It Works

Each algorithm category solves different problems:
- **Searching**: Finds items quickly (linear search is O(n), binary search is O(log n) for sorted arrays)
- **Sorting**: Organizes data for efficient access (bubble sort is O(n²), merge sort is O(n log n))
- **Data structures**: Provides efficient ways to store and access data (stacks, queues, hash maps)
- **Tree traversal**: Navigates hierarchical structures (file systems, organization charts, DOM trees)
- **String processing**: Manipulates text data (palindromes, pattern matching, parsing)

### Troubleshooting

- **Search returns wrong index** — Ensure you're using strict comparison (`===`) to match exact values
- **Sort doesn't work** — Verify array elements are comparable (same type)
- **Stack underflow error** — Always check `isEmpty()` before calling `pop()`
- **Tree traversal infinite loop** — Ensure tree structure doesn't have circular references

## Step 5: Learning Algorithmic Thinking Framework (~10 min)

### Goal

Develop a systematic approach to solving algorithmic problems.

### Actions

1. **Understand the problem**: Ask yourself:
   - What are the inputs?
   - What should the output be?
   - Are there constraints or edge cases?

2. **Plan your approach**: Consider:
   - Can you break the problem into smaller steps?
   - Have you solved a similar problem before?
   - Can you solve a simpler version first?

3. **Consider efficiency**: Think about:
   - How does performance change with input size?
   - What resources (time, memory) do you have?
   - Is there a trade-off between speed and simplicity?

4. **Implement and test**: Follow these steps:
   - Start with a working solution (even if inefficient)
   - Test with various inputs (normal, edge cases, large datasets)
   - Refine and optimize based on results

5. **Analyze and improve**: After implementation:
   - Measure actual performance
   - Compare to alternative approaches
   - Document your decisions

### Expected Result

You'll have a five-step framework you can apply to any algorithmic problem.

### Why It Works

This systematic approach prevents common mistakes:
- **Understanding first** avoids solving the wrong problem
- **Planning** helps you see the big picture before coding
- **Considering efficiency** ensures your solution scales
- **Testing** catches bugs early
- **Analyzing** helps you learn and improve

### Troubleshooting

- **Stuck on understanding** — Try restating the problem in your own words
- **Can't break it down** — Start with the simplest possible case (1-2 elements)
- **Performance unclear** — Implement first, then measure and optimize
- **Too many edge cases** — List them explicitly before coding

## Step 6: Applying Algorithmic Thinking to a Real Problem (~10 min)

### Goal

Apply the algorithmic thinking framework to solve a real-world problem: finding duplicate email addresses.

### Actions

1. **Understand the problem**:
   - **Input**: Array of user objects with email addresses
   - **Output**: Array of duplicate email addresses
   - **Constraints**: Could have 100,000+ users

2. **Plan your approach**: Consider three options:
   - Compare each email to every other email (simple but slow)
   - Sort emails and check adjacent entries (moderate complexity)
   - Use a hash map to track seen emails (fast, uses more memory)

3. **Consider efficiency**:
   - Approach 1: O(n²) - too slow for large datasets
   - Approach 2: O(n log n) - decent
   - Approach 3: O(n) - best time, acceptable memory usage

4. **Implement the best solution**:

```php
# filename: find-duplicate-emails.php
<?php

declare(strict_types=1);

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

$duplicates = findDuplicateEmails($users);
print_r($duplicates); // ['alice@example.com']
```

5. **Run the example**:

```bash
# Test the duplicate finder
php find-duplicate-emails.php
```

### Expected Result

```
Array
(
    [0] => alice@example.com
)
```

### Why It Works

**Time complexity**: O(n) - single pass through users
**Space complexity**: O(n) - stores emails in hash map

The algorithm:
1. Normalizes emails to lowercase for case-insensitive comparison
2. Uses a hash map (`$seen`) for O(1) lookup time
3. Tracks duplicates separately to avoid duplicates in the result array
4. Works efficiently even with millions of users

### Troubleshooting

- **No duplicates found** — Check that emails are normalized (lowercase) before comparison
- **Memory issues** — For very large datasets, consider streaming or batch processing
- **Case sensitivity** — Always normalize emails to lowercase before comparison
- **Empty result** — Verify the input array structure matches expected format

## Step 7: Understanding When NOT to Optimize (~5 min)

### Goal

Learn when optimization is premature and when it's necessary.

### Actions

1. **Understand premature optimization**: Not every piece of code needs optimization:

```php
# filename: premature-optimization.php
<?php

declare(strict_types=1);

// ❌ PREMATURE: Over-optimizing a function that runs once
function getUserName(int $userId): string
{
    // Don't need hash map for single lookup!
    $users = [
        1 => 'Alice',
        2 => 'Bob',
        3 => 'Charlie'
    ];
    return $users[$userId] ?? 'Unknown';
}

// ✅ APPROPRIATE: Optimizing code that runs millions of times
function findDuplicatesInLargeDataset(array $items): array
{
    // This runs on 100,000+ items - optimization matters!
    $seen = [];
    $duplicates = [];
    
    foreach ($items as $item) {
        if (isset($seen[$item])) {
            $duplicates[] = $item;
        } else {
            $seen[$item] = true;
        }
    }
    
    return $duplicates;
}
```

2. **Follow the optimization rule**: Optimize when:
   - Code runs frequently (loops, API endpoints, batch processing)
   - Performance is actually a problem (measure first!)
   - Data size is large (thousands+ items)
   - Users report slowness

3. **Don't optimize when**:
   - Code runs rarely (one-time scripts, admin tools)
   - Performance is already acceptable
   - You're guessing without measuring
   - It makes code harder to read/maintain

### Expected Result

You'll understand that optimization should be:
- **Measured**: Profile first, optimize second
- **Targeted**: Focus on bottlenecks, not everything
- **Balanced**: Consider readability and maintainability

### Why It Works

**Premature optimization** wastes time and makes code complex. **Measured optimization** solves real problems efficiently.

The best approach:
1. Write clear, working code first
2. Measure performance
3. Optimize only what's slow
4. Verify the optimization helped

### Troubleshooting

- **Not sure if optimization is needed** — Measure first with `microtime()` or profiling tools
- **Code is slow but don't know why** — Use profiling tools (Xdebug, Blackfire) to find bottlenecks
- **Optimization made code worse** — Sometimes simpler is better; measure before and after

## Step 8: Setting Up Your Development Environment (~5 min)

### Goal

Configure your development environment for algorithm practice and testing.

### Actions

1. **Verify PHP installation**: You need PHP 8.4+ for modern features:

```bash
# Check PHP version
php --version
```

If you need to install PHP, visit [php.net](https://www.php.net/downloads.php) or use your system's package manager.

2. **Set up your code editor**: We recommend **Visual Studio Code** with the PHP Intelephense extension for:
   - Syntax highlighting
   - Code completion
   - Error detection
   - Debugging support

3. **Install testing framework** (optional): Install **PHPUnit** for testing your algorithms:

```bash
# Create composer.json if needed
composer init --no-interaction

# Install PHPUnit
composer require --dev phpunit/phpunit
```

4. **Set up performance profiling**: Use PHP's built-in `microtime()` for benchmarking:

```php
# filename: benchmark-template.php
<?php

declare(strict_types=1);

$start = microtime(true);

// Your algorithm here
// bubbleSort($largeArray);

$end = microtime(true);
$duration = ($end - $start) * 1000; // Convert to milliseconds

echo "Execution time: {$duration}ms\n";
```

### Expected Result

You'll have PHP 8.4+ installed, a code editor configured, and tools ready for algorithm development.

### Why It Works

- **PHP 8.4+** provides modern type system and performance improvements
- **Code editor** with PHP support helps catch errors early
- **PHPUnit** enables automated testing of algorithm correctness
- **Benchmarking** helps you measure and compare algorithm performance

### Troubleshooting

- **PHP version too old** — Update to PHP 8.4+ using your system's package manager
- **Composer not found** — Install Composer from [getcomposer.org](https://getcomposer.org/)
- **Editor extensions not working** — Restart your editor after installing PHP extensions
- **Benchmark shows 0ms** — Use larger datasets or run multiple iterations for accurate timing

## Exercises

Before moving on, try these exercises to reinforce your understanding:

### Exercise 1: Reverse a String

**Goal**: Practice string manipulation and array/loop operations.

Write a function that reverses a string without using `strrev()`:

```php
# filename: exercise-01-reverse-string.php
<?php

declare(strict_types=1);

function reverseString(string $str): string
{
    // Your code here
}

echo reverseString('hello'); // Should output: olleh
```

**Validation**: Test your implementation:

```php
echo reverseString('hello'); // Expected: olleh
echo reverseString('PHP'); // Expected: PHP
echo reverseString(''); // Expected: (empty string)
```

<details>
<summary>Solution</summary>

```php
# filename: exercise-01-reverse-string.php
<?php

declare(strict_types=1);

function reverseString(string $str): string
{
    $reversed = '';
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        $reversed .= $str[$i];
    }
    return $reversed;
}

// Test cases
echo reverseString('hello') . "\n"; // olleh
echo reverseString('PHP') . "\n"; // PHP
echo reverseString('') . "\n"; // (empty)
```
</details>

### Exercise 2: Find the Second Largest Number

**Goal**: Practice array traversal and comparison logic.

Write a function that finds the second-largest number in an array:

```php
# filename: exercise-02-second-largest.php
<?php

declare(strict_types=1);

function findSecondLargest(array $numbers): int|float|null
{
    // Your code here
}

echo findSecondLargest([3, 7, 2, 9, 1, 5]); // Should output: 7
```

**Validation**: Test your implementation:

```php
echo findSecondLargest([3, 7, 2, 9, 1, 5]); // Expected: 7
echo findSecondLargest([1, 1, 1]); // Expected: null (no second largest)
echo findSecondLargest([5]); // Expected: null (need at least 2 numbers)
```

<details>
<summary>Solution</summary>

```php
# filename: exercise-02-second-largest.php
<?php

declare(strict_types=1);

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

// Test cases
echo findSecondLargest([3, 7, 2, 9, 1, 5]) . "\n"; // 7
var_dump(findSecondLargest([1, 1, 1])); // null
var_dump(findSecondLargest([5])); // null
```
</details>

### Exercise 3: Count Vowels

**Goal**: Practice string iteration and character matching.

Count the number of vowels in a string:

```php
# filename: exercise-03-count-vowels.php
<?php

declare(strict_types=1);

function countVowels(string $str): int
{
    // Your code here
}

echo countVowels('Hello World'); // Should output: 3
```

**Validation**: Test your implementation:

```php
echo countVowels('Hello World'); // Expected: 3
echo countVowels('PHP'); // Expected: 0
echo countVowels('aeiou'); // Expected: 5
```

<details>
<summary>Solution</summary>

```php
# filename: exercise-03-count-vowels.php
<?php

declare(strict_types=1);

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

// Test cases
echo countVowels('Hello World') . "\n"; // 3
echo countVowels('PHP') . "\n"; // 0
echo countVowels('aeiou') . "\n"; // 5
```
</details>

## Wrap-up

Congratulations! You've completed the introduction to algorithms. Here's what you've accomplished:

- ✓ **Learned what algorithms are** and why they matter for PHP developers
- ✓ **Explored common algorithm categories** including searching, sorting, data structures, tree traversal, and string processing
- ✓ **Understood the difference** between algorithms and data structures
- ✓ **Developed a systematic problem-solving framework** with five clear steps
- ✓ **Learned when optimization is appropriate** and when it's premature
- ✓ **Applied algorithmic thinking** to solve a real-world duplicate detection problem
- ✓ **Set up your development environment** for algorithm practice and testing
- ✓ **Completed three practice exercises** to reinforce your understanding

### Key Concepts Learned

- **Algorithms** are step-by-step procedures for solving problems
- **Good algorithms** make your PHP applications faster and more scalable
- **Algorithmic thinking** is a systematic approach to problem-solving
- **Efficiency matters**, especially as your data grows
- **Practice** is essential—you'll improve with each algorithm you implement

### What's Next

In the next chapter, we'll dive deep into **Algorithm Complexity and Big O Notation**. You'll learn to:

- Analyze algorithm efficiency mathematically
- Understand time and space complexity
- Recognize common complexity patterns
- Make informed decisions about algorithm choices

This foundation will help you evaluate every algorithm we study in this series.

## Further Reading

- [PHP Manual: Arrays](https://www.php.net/manual/en/book.array.php) — Official PHP array documentation
- [PHP Manual: Functions](https://www.php.net/manual/en/functions.user-defined.php) — User-defined functions guide
- [Introduction to Algorithms](https://mitpress.mit.edu/books/introduction-algorithms) by CLRS — Comprehensive algorithms textbook
- [Algorithm Complexity Cheat Sheet](https://www.bigocheatsheet.com/) — Quick reference for Big O notation
- [Chapter 01: Algorithm Complexity & Big O Notation](/series/php-algorithms/chapters/01-algorithm-complexity-big-o-notation) — Next chapter in this series

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 00 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-00)**

Files included:
- `01-quick-start-examples.php` - Collection of essential algorithm patterns ready to use
- `02-common-patterns.php` - Fundamental algorithm patterns (two pointers, sliding window, hash maps)
- `03-performance-tips.php` - Practical optimization techniques with benchmarks
- `README.md` - Complete documentation and usage guide

Clone the repository to run the examples locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-00
php 01-quick-start-examples.php
```

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="00"
  label="You've mastered the fundamentals of algorithms and algorithmic thinking!"
/>

---

Ready to analyze algorithm efficiency? Continue to [Chapter 01: Algorithm Complexity & Big O Notation](/series/php-algorithms/chapters/01-algorithm-complexity-big-o-notation).
