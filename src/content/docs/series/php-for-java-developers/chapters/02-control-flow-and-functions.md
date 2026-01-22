---
title: "02: Control Flow & Functions"
description: "Master PHP's control structures and functions with detailed Java comparisons"
sidebar:
  label: "02: Control Flow & Functions"
  order: 2
  badge:
    text: Beginner
    variant: success
---

![Control Flow Hero](/images/php-for-java-developers/chapter-02-control-flow-hero-full.webp)

# Chapter 2: Control Flow & Functions

<Badge type="tip">Beginner</Badge> <Badge type="info">60-75 min</Badge>

## Overview

Control structures in PHP will feel very familiar to you as a Java developer - the syntax is nearly identical for most constructs. However, PHP adds some convenient features like the `foreach` loop for arrays and flexible function parameters. In this chapter, we'll explore control flow and functions, highlighting both similarities and PHP-specific enhancements.

By the end of this chapter, you'll be writing PHP control structures and functions confidently, knowing exactly how they differ from Java.

## Prerequisites

::: info Time Estimate
⏱️ **60-75 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 1: Types, Variables & Operators](/series/php-for-java-developers/chapters/01-types-variables-and-operators)
- Understanding of Java control structures and methods
- PHP 8.4 installed and configured

## What You'll Build

In this chapter, you'll create:
- A request router using switch statements
- A collection of utility functions with type hints
- A function library demonstrating closures
- A file processor using control structures
- Advanced function patterns including recursion, overloading alternatives, and reflection

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Use PHP control structures** (if, switch, loops) effectively
- **Write type-safe functions** with proper declarations
- **Leverage PHP's flexible parameters** (default values, named arguments, variadic)
- **Create and use closures** (anonymous functions)
- **Understand include/require** for code organization
- **Master advanced patterns** including recursion, overloading alternatives, scope management, and reflection

---

## Section 1: Conditional Statements

### Goal

Master if/else and switch statements in PHP.

### If/Else Statements

The syntax is nearly identical to Java:

::: code-group

```php [PHP Conditionals]
<?php

declare(strict_types=1);

$age = 25;

// Standard if/else (identical to Java)
if ($age < 18) {
    echo "Minor";
} elseif ($age < 65) {
    echo "Adult";
} else {
    echo "Senior";
}

// Ternary operator (identical to Java)
$status = $age >= 18 ? "Adult" : "Minor";

// Null coalescing operator (PHP-specific, cleaner than ternary)
$name = $_GET['name'] ?? 'Guest';  // Use 'Guest' if 'name' is null/undefined

// Null coalescing assignment (PHP 7.4+)
$config['timeout'] ??= 30;  // Set to 30 if not already set

// Spaceship operator in conditionals (PHP 7+)
$result = $a <=> $b;  // Returns -1, 0, or 1
if ($result < 0) {
    echo "a is less than b";
}
```

```java [Java Conditionals]
int age = 25;

// Standard if/else (identical syntax)
if (age < 18) {
    System.out.println("Minor");
} else if (age < 65) {
    System.out.println("Adult");
} else {
    System.out.println("Senior");
}

// Ternary operator (identical)
String status = age >= 18 ? "Adult" : "Minor";

// No direct equivalent to null coalescing
// Closest: Optional.ofNullable(value).orElse("Guest")
String name = request.getParameter("name");
if (name == null) {
    name = "Guest";
}

// No spaceship operator
// Use Integer.compare(a, b)
int result = Integer.compare(a, b);
if (result < 0) {
    System.out.println("a is less than b");
}
```

:::

::: tip PHP-Specific Features
1. **`elseif`**: Can be written as one word (or `else if` as two words)
2. **`??` operator**: Much cleaner than ternary for default values
3. **`??=` operator**: Assign default value only if not set
4. **`<=>` operator**: Three-way comparison (useful in sorting callbacks)
:::

### Switch Statements

Switch statements are very similar, with some important differences:

::: code-group

```php [PHP Switch]
<?php

declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo "Fetching data";
        break;
    case 'POST':
        echo "Creating resource";
        break;
    case 'PUT':
    case 'PATCH':
        echo "Updating resource";
        break;
    case 'DELETE':
        echo "Deleting resource";
        break;
    default:
        http_response_code(405);
        echo "Method not allowed";
}

// PHP 8.0+: Match expression (better than switch!)
$message = match ($method) {
    'GET' => 'Fetching data',
    'POST' => 'Creating resource',
    'PUT', 'PATCH' => 'Updating resource',
    'DELETE' => 'Deleting resource',
    default => 'Method not allowed'
};

// Match is strict by default (uses ===)
$result = match ($value) {
    0 => 'Zero',
    1 => 'One',
    2 => 'Two',
    default => 'Other'
};
```

```java [Java Switch]
String method = request.getMethod();

switch (method) {
    case "GET":
        System.out.println("Fetching data");
        break;
    case "POST":
        System.out.println("Creating resource");
        break;
    case "PUT":
    case "PATCH":
        System.out.println("Updating resource");
        break;
    case "DELETE":
        System.out.println("Deleting resource");
        break;
    default:
        response.setStatus(405);
        System.out.println("Method not allowed");
}

// Java 14+: Switch expressions
String message = switch (method) {
    case "GET" -> "Fetching data";
    case "POST" -> "Creating resource";
    case "PUT", "PATCH" -> "Updating resource";
    case "DELETE" -> "Deleting resource";
    default -> "Method not allowed";
};
```

:::

::: warning Key Differences
1. **PHP match (8.0+)**: Strict comparison (===), no fall-through, returns value
2. **PHP switch**: Loose comparison (==) by default unless using strict_types
3. **No break in match**: Each arm is independent (like Java's arrow syntax)
4. **Match throws error**: If no case matches and no default (safer!)
:::

### Alternative Syntax for Templates

PHP offers alternative syntax for control structures, useful in template files:

::: code-group

```php [Standard Syntax]
<?php if ($user->isLoggedIn()): ?>
    <div class="welcome">
        Welcome, <?= $user->getName() ?>!
    </div>
<?php else: ?>
    <div class="login">
        Please log in
    </div>
<?php endif; ?>

<?php foreach ($items as $item): ?>
    <li><?= $item ?></li>
<?php endforeach; ?>

<?php while ($row = $result->fetch()): ?>
    <tr><td><?= $row['name'] ?></td></tr>
<?php endwhile; ?>

<?php switch ($status): ?>
    <?php case 'active': ?>
        <span class="badge-success">Active</span>
        <?php break; ?>
    <?php case 'inactive': ?>
        <span class="badge-warning">Inactive</span>
        <?php break; ?>
    <?php default: ?>
        <span class="badge-default">Unknown</span>
<?php endswitch; ?>
```

```php [With Braces (Less Clean in Templates)]
<?php if ($user->isLoggedIn()) { ?>
    <div class="welcome">
        Welcome, <?= $user->getName() ?>!
    </div>
<?php } else { ?>
    <div class="login">
        Please log in
    </div>
<?php } ?>
```

:::

::: tip Template Syntax Benefits
- **Cleaner HTML mixing**: No braces in template files
- **Better readability**: Clear start/end markers
- **Editor support**: Easier to match opening/closing tags
- **Common in frameworks**: Laravel Blade uses similar syntax
:::

### Match vs Switch: Detailed Comparison

| Feature | Switch | Match (PHP 8.0+) |
|---------|--------|------------------|
| **Comparison** | `==` (loose, unless strict_types) | `===` (always strict) |
| **Fall-through** | Yes (needs `break`) | No (each arm independent) |
| **Returns value** | No (needs assignment) | Yes (expression) |
| **Multiple conditions** | Multiple `case` statements | Comma-separated values |
| **Default required** | No (optional) | No, but throws if no match |
| **Complex expressions** | Only in conditions | In arms too |

```php
<?php

declare(strict_types=1);

// Switch issues:
$value = "1";
switch ($value) {
    case 1:
        $result = "Integer one";  // ⚠️ Matches with weak comparison
        break;
    case "1":
        $result = "String one";
        break;
}

// Match advantages:
$value = "1";
$result = match ($value) {
    1 => "Integer one",     // Doesn't match
    "1" => "String one",    // ✅ Matches (strict)
};

// Match with complex expressions
$statusCode = 404;
$message = match (true) {
    $statusCode >= 200 && $statusCode < 300 => "Success",
    $statusCode >= 300 && $statusCode < 400 => "Redirect",
    $statusCode >= 400 && $statusCode < 500 => "Client Error",
    $statusCode >= 500 => "Server Error",
};

// Match with multiple values
$day = 'Saturday';
$type = match ($day) {
    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' => 'Weekday',
    'Saturday', 'Sunday' => 'Weekend',
};
```

---

## Section 2: Loops

### Goal

Master PHP's loop constructs, especially the powerful foreach loop.

### For Loops

Nearly identical to Java:

::: code-group

```php [PHP For Loop]
<?php

declare(strict_types=1);

// Standard for loop (identical to Java)
for ($i = 0; $i < 10; $i++) {
    echo "$i\n";
}

// Multiple expressions
for ($i = 0, $j = 10; $i < $j; $i++, $j--) {
    echo "i=$i, j=$j\n";
}

// Infinite loop
for (;;) {
    // Use break to exit
    if ($condition) {
        break;
    }
}
```

```java [Java For Loop]
// Standard for loop (identical syntax)
for (int i = 0; i < 10; i++) {
    System.out.println(i);
}

// Multiple expressions
for (int i = 0, j = 10; i < j; i++, j--) {
    System.out.println("i=" + i + ", j=" + j);
}

// Infinite loop
for (;;) {
    // Use break to exit
    if (condition) {
        break;
    }
}
```

:::

### While and Do-While Loops

Again, identical syntax:

::: code-group

```php [PHP While Loops]
<?php

declare(strict_types=1);

// While loop
$i = 0;
while ($i < 10) {
    echo "$i\n";
    $i++;
}

// Do-while loop
$i = 0;
do {
    echo "$i\n";
    $i++;
} while ($i < 10);
```

```java [Java While Loops]
// While loop
int i = 0;
while (i < 10) {
    System.out.println(i);
    i++;
}

// Do-while loop
int i = 0;
do {
    System.out.println(i);
    i++;
} while (i < 10);
```

:::

### Foreach Loop (PHP's Superpower)

PHP's foreach is more powerful than Java's enhanced for loop:

::: code-group

```php [PHP Foreach]
<?php

declare(strict_types=1);

// Iterate over indexed array (like Java's for-each)
$fruits = ['apple', 'banana', 'cherry'];
foreach ($fruits as $fruit) {
    echo "$fruit\n";
}

// With index (key)
foreach ($fruits as $index => $fruit) {
    echo "$index: $fruit\n";
}

// Iterate over associative array (no direct Java equivalent)
$user = [
    'name' => 'Alice',
    'age' => 30,
    'email' => 'alice@example.com'
];

foreach ($user as $key => $value) {
    echo "$key: $value\n";
}

// Modify array values by reference
$numbers = [1, 2, 3, 4, 5];
foreach ($numbers as &$number) {
    $number *= 2;  // Modifies original array
}
unset($number);  // Important: unset reference after loop!

print_r($numbers);  // [2, 4, 6, 8, 10]

// Destructuring in foreach (PHP 7.1+)
$users = [
    ['Alice', 30],
    ['Bob', 25],
    ['Charlie', 35]
];

foreach ($users as [$name, $age]) {
    echo "$name is $age years old\n";
}

// Nested array destructuring
$data = [
    ['user' => ['name' => 'Alice', 'age' => 30]],
    ['user' => ['name' => 'Bob', 'age' => 25]]
];

foreach ($data as ['user' => ['name' => $name, 'age' => $age]]) {
    echo "$name: $age\n";
}
```

```java [Java Enhanced For Loop]
// Iterate over List
List<String> fruits = Arrays.asList("apple", "banana", "cherry");
for (String fruit : fruits) {
    System.out.println(fruit);
}

// With index (requires traditional for loop or IntStream)
for (int i = 0; i < fruits.size(); i++) {
    System.out.println(i + ": " + fruits.get(i));
}

// Iterate over Map
Map<String, Object> user = new HashMap<>();
user.put("name", "Alice");
user.put("age", 30);
user.put("email", "alice@example.com");

for (Map.Entry<String, Object> entry : user.entrySet()) {
    System.out.println(entry.getKey() + ": " + entry.getValue());
}

// Can't modify during iteration (ConcurrentModificationException)
// Need to use ListIterator or streams

// Java 8+ Streams for transformation
List<Integer> numbers = Arrays.asList(1, 2, 3, 4, 5);
List<Integer> doubled = numbers.stream()
    .map(n -> n * 2)
    .collect(Collectors.toList());
```

:::

::: tip Foreach Best Practices
1. **Use foreach** whenever you don't need the index
2. **Unset reference** after loops using `&$var`
3. **Destructuring** makes code cleaner with multi-dimensional arrays
4. **Key access** is free (no performance penalty)
:::

### Loop Control

Break and continue work identically:

```php
<?php

declare(strict_types=1);

// Break out of loop
for ($i = 0; $i < 10; $i++) {
    if ($i === 5) {
        break;  // Exit loop
    }
    echo "$i\n";
}

// Continue to next iteration
for ($i = 0; $i < 10; $i++) {
    if ($i % 2 === 0) {
        continue;  // Skip even numbers
    }
    echo "$i\n";
}

// Break/continue with levels (for nested loops)
for ($i = 0; $i < 3; $i++) {
    for ($j = 0; $j < 3; $j++) {
        if ($j === 1) {
            break 2;  // Break out of both loops
        }
        echo "i=$i, j=$j\n";
    }
}
```

### Loop Performance and Usage Guide

Choosing the right loop can impact both readability and performance:

| Loop Type | Best For | Performance | Java Equivalent |
|-----------|----------|-------------|-----------------|
| `foreach` | Arrays/iterables | Fast ⚡ | Enhanced for loop |
| `for` | Counter-based iteration | Fast ⚡ | Standard for loop |
| `while` | Unknown iteration count | Fast ⚡ | while loop |
| `do-while` | At least one iteration | Fast ⚡ | do-while loop |
| `array_map()` | Transforming arrays | Fast ⚡ | Stream.map() |
| `array_filter()` | Filtering arrays | Fast ⚡ | Stream.filter() |
| `array_walk()` | Side effects on arrays | Medium | forEach() |

```php
<?php

declare(strict_types=1);

$data = range(1, 1000000);

// ✅ Best: foreach for iteration
foreach ($data as $value) {
    // Process value
}

// ✅ Best: array functions for transformation
$doubled = array_map(fn($n) => $n * 2, $data);

// ❌ Avoid: for with count() in condition (recalculated each iteration)
for ($i = 0; $i < count($data); $i++) {
    // Process $data[$i]
}

// ✅ Better: Cache count
$length = count($data);
for ($i = 0; $i < $length; $i++) {
    // Process $data[$i]
}

// ✅ Best for index access: foreach with key
foreach ($data as $index => $value) {
    // Have both index and value
}
```

::: tip Loop Selection Guidelines
**Use `foreach` when:**
- Iterating over all elements
- Don't need counter manipulation
- Working with associative arrays

**Use `for` when:**
- Need precise control over counter
- Iterating with step > 1
- Need to modify counter inside loop

**Use `while` when:**
- Iteration count unknown
- Condition-based iteration
- Reading from file/stream

**Use array functions when:**
- Transforming/filtering data
- Want functional style
- Chaining multiple operations
:::

---

## Section 3: Functions

### Goal

Learn to write type-safe functions with modern PHP features.

### Basic Function Declaration

::: code-group

```php [PHP Functions]
<?php

declare(strict_types=1);

// Basic function with type hints
function greet(string $name): string
{
    return "Hello, $name!";
}

// Multiple parameters
function add(int $a, int $b): int
{
    return $a + $b;
}

// No return value (void)
function logMessage(string $message): void
{
    echo "[LOG] $message\n";
}

// Union types (PHP 8.0+)
function format(int|float $number): string
{
    return number_format($number, 2);
}

// Mixed type (accepts anything)
function debug(mixed $value): void
{
    var_dump($value);
}

// Nullable parameters and return types
function findUser(int $id): ?array
{
    // Returns array or null
    return $id > 0 ? ['id' => $id, 'name' => 'User'] : null;
}

function setName(?string $name): void
{
    // $name can be string or null
    echo $name ?? 'Anonymous';
}
```

```java [Java Methods]
// Basic method
public String greet(String name) {
    return "Hello, " + name + "!";
}

// Multiple parameters
public int add(int a, int b) {
    return a + b;
}

// Void return
public void logMessage(String message) {
    System.out.println("[LOG] " + message);
}

// No union types (need method overloading)
public String format(int number) {
    return String.format("%.2f", (double) number);
}

public String format(double number) {
    return String.format("%.2f", number);
}

// Optional with Java 8+
public Optional<Map<String, Object>> findUser(int id) {
    if (id > 0) {
        return Optional.of(Map.of("id", id, "name", "User"));
    }
    return Optional.empty();
}
```

:::

### Default Parameters

PHP makes default parameters much easier than Java:

::: code-group

```php [PHP Default Parameters]
<?php

declare(strict_types=1);

// Default parameter values
function createUser(
    string $name,
    int $age = 18,
    string $role = 'user'
): array {
    return [
        'name' => $name,
        'age' => $age,
        'role' => $role
    ];
}

// Usage
$user1 = createUser('Alice');  // Uses defaults: age=18, role='user'
$user2 = createUser('Bob', 25);  // age=25, role='user'
$user3 = createUser('Charlie', 30, 'admin');  // All specified

// Default parameters must come last
function buildUrl(
    string $path,
    string $host = 'localhost',
    int $port = 80
): string {
    return "$host:$port/$path";
}
```

```java [Java Default Parameters]
// Java doesn't have default parameters
// Must use method overloading

public Map<String, Object> createUser(String name) {
    return createUser(name, 18, "user");
}

public Map<String, Object> createUser(String name, int age) {
    return createUser(name, age, "user");
}

public Map<String, Object> createUser(String name, int age, String role) {
    Map<String, Object> user = new HashMap<>();
    user.put("name", name);
    user.put("age", age);
    user.put("role", role);
    return user;
}

// Or use Builder pattern
User user = User.builder()
    .name("Alice")
    .age(18)
    .role("user")
    .build();
```

:::

### Named Arguments (PHP 8.0+)

PHP 8 introduced named arguments, similar to Kotlin/Python:

```php
<?php

declare(strict_types=1);

function sendEmail(
    string $to,
    string $subject,
    string $body,
    string $from = 'noreply@example.com',
    bool $html = false,
    array $attachments = []
): void {
    // Send email logic
    echo "Sending email to $to\n";
    echo "Subject: $subject\n";
    echo "HTML: " . ($html ? 'Yes' : 'No') . "\n";
}

// Named arguments (order doesn't matter!)
sendEmail(
    to: 'alice@example.com',
    subject: 'Hello',
    body: 'Welcome!',
    html: true
);

// Skip optional parameters easily
sendEmail(
    subject: 'Test',
    to: 'bob@example.com',
    body: 'Test message',
    attachments: ['file.pdf']
);

// Mix positional and named arguments
sendEmail(
    'charlie@example.com',
    'Greetings',
    'Hi there!',
    html: true  // Named argument for clarity
);
```

::: tip Named Arguments Benefits
1. **Skip optional parameters** without providing all intermediate ones
2. **Self-documenting code** - clear what each argument represents
3. **Order independence** - reorder as needed for readability
4. **Great for functions with many parameters**
:::

### Variadic Functions

PHP supports variadic functions (like Java's varargs):

::: code-group

```php [PHP Variadic Functions]
<?php

declare(strict_types=1);

// Variadic function (... operator)
function sum(int ...$numbers): int
{
    return array_sum($numbers);
}

echo sum(1, 2, 3);  // 6
echo sum(1, 2, 3, 4, 5);  // 15

// Combine regular and variadic parameters
function formatList(string $separator, string ...$items): string
{
    return implode($separator, $items);
}

echo formatList(', ', 'apple', 'banana', 'cherry');
// Output: apple, banana, cherry

// Type hints work with variadics
function concatenateStrings(string ...$strings): string
{
    return implode('', $strings);
}

// Unpack arrays into function arguments
$numbers = [1, 2, 3, 4, 5];
echo sum(...$numbers);  // 15 (unpacks array)

$items = ['red', 'green', 'blue'];
echo formatList(' | ', ...$items);
// Output: red | green | blue
```

```java [Java Varargs]
// Java varargs (similar but less flexible)
public int sum(int... numbers) {
    return Arrays.stream(numbers).sum();
}

System.out.println(sum(1, 2, 3));  // 6
System.out.println(sum(1, 2, 3, 4, 5));  // 15

// Regular and varargs parameters
public String formatList(String separator, String... items) {
    return String.join(separator, items);
}

System.out.println(formatList(", ", "apple", "banana", "cherry"));

// Can pass array directly
int[] numbers = {1, 2, 3, 4, 5};
System.out.println(sum(numbers));  // Works directly

// But can't easily unpack List
List<String> items = Arrays.asList("red", "green", "blue");
// Must convert: items.toArray(new String[0])
```

:::

### First-Class Callable Syntax (PHP 8.1+)

PHP 8.1 introduced a cleaner syntax for creating callables from functions and methods:

::: code-group

```php [PHP 8.1+ First-Class Callables]
<?php

declare(strict_types=1);

class StringUtils
{
    public static function uppercase(string $str): string
    {
        return strtoupper($str);
    }

    public function lowercase(string $str): string
    {
        return strtolower($str);
    }
}

// Old way: string references (still works)
$upper1 = 'strtoupper';
$upper2 = 'StringUtils::uppercase';
$upper3 = [new StringUtils(), 'lowercase'];

// New way: First-class callable syntax (PHP 8.1+)
$upper1 = strtoupper(...);  // Built-in function
$upper2 = StringUtils::uppercase(...);  // Static method
$utils = new StringUtils();
$upper3 = $utils->lowercase(...);  // Instance method

// Usage
$strings = ['hello', 'world'];
$result = array_map($upper1, $strings);
// ['HELLO', 'WORLD']

// Passing to higher-order functions
$names = ['alice', 'bob', 'charlie'];
$uppercase = array_map(strtoupper(...), $names);
$trimmed = array_map(trim(...), $names);

// With type hints (cleaner than before)
function processStrings(array $strings, callable $processor): array
{
    return array_map($processor, $strings);
}

$result = processStrings($names, strtoupper(...));
```

```java [Java Method References]
// Java has method references (Java 8+)
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

List<String> strings = Arrays.asList("hello", "world");

// Method references
List<String> upper = strings.stream()
    .map(String::toUpperCase)
    .collect(Collectors.toList());

// Static method reference
List<String> trimmed = strings.stream()
    .map(String::trim)
    .collect(Collectors.toList());

// Instance method reference
class StringUtils {
    public String lowercase(String str) {
        return str.toLowerCase();
    }
}

StringUtils utils = new StringUtils();
List<String> lower = strings.stream()
    .map(utils::lowercase)
    .collect(Collectors.toList());
```

:::

::: tip First-Class Callables vs Old Syntax
**Old way:**
```php
array_map('strtoupper', $strings);
array_map([$obj, 'method'], $strings);
```

**New way (PHP 8.1+):**
```php
array_map(strtoupper(...), $strings);
array_map($obj->method(...), $strings);
```

**Benefits:**
- Type-checked at parse time
- IDE autocomplete support
- Clearer intent
- Closer to Java's method references
:::

---

## Section 4: Generator Functions

### Goal

Learn PHP's generator functions with `yield` - a powerful feature for memory-efficient iteration.

### Understanding Generators

Generators allow you to create iterators without loading all data into memory:

::: code-group

```php [PHP Generators]
<?php

declare(strict_types=1);

// Regular function - loads everything into memory
function getAllNumbers(): array
{
    $numbers = [];
    for ($i = 0; $i < 1000000; $i++) {
        $numbers[] = $i;
    }
    return $numbers;  // ⚠️ Uses ~50MB memory
}

// Generator - yields values one at a time
function generateNumbers(): Generator
{
    for ($i = 0; $i < 1000000; $i++) {
        yield $i;  // ✅ Uses minimal memory
    }
}

// Usage is identical
foreach (generateNumbers() as $number) {
    echo $number . "\n";
    if ($number >= 10) break;  // Can stop early
}

// Generator with keys
function generateKeyValue(): Generator
{
    yield 'name' => 'Alice';
    yield 'age' => 30;
    yield 'email' => 'alice@example.com';
}

foreach (generateKeyValue() as $key => $value) {
    echo "$key: $value\n";
}

// Generator that yields from another generator
function numbers(): Generator
{
    yield 1;
    yield 2;
    yield from otherNumbers();  // Delegate to another generator
    yield 5;
}

function otherNumbers(): Generator
{
    yield 3;
    yield 4;
}

// Output: 1, 2, 3, 4, 5
foreach (numbers() as $num) {
    echo "$num\n";
}
```

```java [Java - No Direct Equivalent]
// Java doesn't have generators
// Closest: Stream API or custom Iterator

// Stream approach (not the same - evaluates differently)
import java.util.stream.IntStream;

IntStream numbers = IntStream.range(0, 1000000);
numbers.limit(10).forEach(System.out::println);

// Custom Iterator (more verbose)
import java.util.Iterator;

class NumberIterator implements Iterator<Integer> {
    private int current = 0;
    private int max;

    public NumberIterator(int max) {
        this.max = max;
    }

    public boolean hasNext() {
        return current < max;
    }

    public Integer next() {
        return current++;
    }
}

// Usage
Iterator<Integer> iter = new NumberIterator(1000000);
while (iter.hasNext() && iter.next() < 10) {
    System.out.println(iter.next());
}
```

:::

### Practical Generator Examples

```php
<?php

declare(strict_types=1);

// Read large file line by line (memory efficient)
function readLargeFile(string $filename): Generator
{
    $handle = fopen($filename, 'r');
    if ($handle === false) {
        return;
    }

    try {
        while (($line = fgets($handle)) !== false) {
            yield trim($line);
        }
    } finally {
        fclose($handle);
    }
}

// Process without loading entire file into memory
foreach (readLargeFile('/var/log/access.log') as $line) {
    // Process each line
    if (str_contains($line, 'ERROR')) {
        echo $line . "\n";
    }
}

// Fibonacci sequence generator
function fibonacci(): Generator
{
    $a = 0;
    $b = 1;

    yield $a;
    yield $b;

    while (true) {
        $next = $a + $b;
        yield $next;
        $a = $b;
        $b = $next;
    }
}

// Get first 10 Fibonacci numbers
$count = 0;
foreach (fibonacci() as $fib) {
    echo "$fib\n";
    if (++$count >= 10) break;
}

// Paginated API calls generator
function fetchAllUsers(int $perPage = 100): Generator
{
    $page = 1;

    do {
        $response = fetchUsersPage($page, $perPage);
        $users = $response['users'];

        foreach ($users as $user) {
            yield $user;
        }

        $page++;
    } while (!empty($users));
}

// Process all users without loading everything
foreach (fetchAllUsers() as $user) {
    processUser($user);
}

// Generator with two-way communication
function counter(): Generator
{
    $count = 0;

    while (true) {
        // Receive value sent to generator
        $increment = yield $count;

        if ($increment !== null) {
            $count += $increment;
        } else {
            $count++;
        }
    }
}

$gen = counter();
echo $gen->current() . "\n";  // 0
$gen->next();
echo $gen->current() . "\n";  // 1
$gen->send(10);  // Send value to generator
echo $gen->current() . "\n";  // 11
```

::: tip When to Use Generators
**Use generators when:**
- Processing large datasets (files, database results)
- Creating infinite sequences (Fibonacci, primes, etc.)
- Memory is a concern
- Don't need random access to elements
- Want lazy evaluation

**Don't use generators when:**
- Need to access elements multiple times (generators are one-time use)
- Need random access by index
- Dataset is small enough to fit in memory
- Need to count elements before processing
:::

### Generator Methods

```php
<?php

declare(strict_types=1);

function myGenerator(): Generator
{
    yield 'a';
    yield 'b';
    yield 'c';
}

$gen = myGenerator();

// Get current value
echo $gen->current();  // 'a'

// Get current key
echo $gen->key();  // 0

// Move to next value
$gen->next();
echo $gen->current();  // 'b'

// Check if generator is still valid
var_dump($gen->valid());  // true

// Rewind (throws exception - generators can't rewind!)
// $gen->rewind();  // ❌ Exception

// Send value to generator
$gen->send('value');

// Throw exception into generator
// $gen->throw(new Exception('Error'));
```

---

## Section 5: Closures and Anonymous Functions

### Goal

Master PHP's closures and understand how they compare to Java's lambdas.

### Anonymous Functions (Closures)

::: code-group

```php [PHP Closures]
<?php

declare(strict_types=1);

// Anonymous function (closure)
$greet = function(string $name): string {
    return "Hello, $name!";
};

echo $greet('Alice');  // Hello, Alice!

// Short arrow functions (PHP 7.4+, like Java lambdas)
$double = fn($n) => $n * 2;
echo $double(5);  // 10

// Arrow functions with types
$add = fn(int $a, int $b): int => $a + $b;

// Closures in array functions
$numbers = [1, 2, 3, 4, 5];

$doubled = array_map(fn($n) => $n * 2, $numbers);
// [2, 4, 6, 8, 10]

$evens = array_filter($numbers, fn($n) => $n % 2 === 0);
// [2, 4]

$sum = array_reduce($numbers, fn($acc, $n) => $acc + $n, 0);
// 15

// Capturing variables from parent scope
$multiplier = 10;

// Old way: use() keyword
$multiply = function($n) use ($multiplier) {
    return $n * $multiplier;
};

// Arrow functions automatically capture
$multiply = fn($n) => $n * $multiplier;  // Cleaner!

echo $multiply(5);  // 50
```

```java [Java Lambdas]
// Java lambdas (Java 8+)
Function<String, String> greet = name -> "Hello, " + name + "!";
System.out.println(greet.apply("Alice"));

// With types (explicit)
BiFunction<Integer, Integer, Integer> add =
    (Integer a, Integer b) -> a + b;

// Lambdas with streams
List<Integer> numbers = Arrays.asList(1, 2, 3, 4, 5);

List<Integer> doubled = numbers.stream()
    .map(n -> n * 2)
    .collect(Collectors.toList());

List<Integer> evens = numbers.stream()
    .filter(n -> n % 2 == 0)
    .collect(Collectors.toList());

int sum = numbers.stream()
    .reduce(0, Integer::sum);

// Capturing variables (must be final or effectively final)
int multiplier = 10;

Function<Integer, Integer> multiply = n -> n * multiplier;
System.out.println(multiply.apply(5));  // 50
```

:::

### Practical Closure Examples

```php
<?php

declare(strict_types=1);

// Closure as callback
function processItems(array $items, callable $callback): array
{
    return array_map($callback, $items);
}

$uppercased = processItems(
    ['hello', 'world'],
    fn($s) => strtoupper($s)
);
// ['HELLO', 'WORLD']

// Closure returning closure (higher-order function)
function multiplier(int $factor): callable
{
    return fn(int $n): int => $n * $factor;
}

$double = multiplier(2);
$triple = multiplier(3);

echo $double(5);  // 10
echo $triple(5);  // 15

// Sorting with closures
$users = [
    ['name' => 'Charlie', 'age' => 35],
    ['name' => 'Alice', 'age' => 30],
    ['name' => 'Bob', 'age' => 25]
];

// Sort by age
usort($users, fn($a, $b) => $a['age'] <=> $b['age']);

// Sort by name
usort($users, fn($a, $b) => $a['name'] <=> $b['name']);

// Filter and transform
$adults = array_filter($users, fn($u) => $u['age'] >= 18);
$names = array_map(fn($u) => $u['name'], $adults);
```

::: tip Arrow Functions vs Regular Closures
**Use arrow functions (`fn`) when:**
- Single expression
- Need to capture variables from parent scope
- Want concise syntax

**Use regular closures (`function`) when:**
- Multiple statements
- Need to modify captured variables (with `&`)
- Want explicit `use()` clause for clarity
:::

---

## Section 6: Include and Require

### Goal

Understand PHP's code inclusion system vs Java's imports.

### Include vs Require

::: code-group

```php [PHP Include/Require]
<?php

declare(strict_types=1);

// include: Warns if file not found, continues execution
include 'config.php';

// require: Fatal error if file not found, stops execution
require 'database.php';

// include_once: Includes only once (prevents redeclaration)
include_once 'helpers.php';

// require_once: Most common for class files
require_once 'User.php';
require_once 'Database.php';

// Relative paths
require_once __DIR__ . '/includes/functions.php';
require_once dirname(__FILE__) . '/config/app.php';

// Modern approach: Use Composer's autoloader (Chapter 8)
require 'vendor/autoload.php';  // One-time setup
// Then just use classes - no manual includes!
```

```java [Java Imports]
// Java uses compile-time imports
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

// Wildcard imports
import java.util.*;

// Static imports
import static java.lang.Math.PI;
import static java.lang.Math.pow;

// No runtime inclusion
// Classes are loaded by ClassLoader as needed
```

:::

### Key Differences

| PHP include/require | Java import |
|---------------------|-------------|
| Runtime operation | Compile-time operation |
| Literally inserts file content | References class location |
| Can include multiple times | Import once per file |
| Can be conditional | Always at top of file |
| Manual dependency management | Handled by build tools |

::: warning Best Practice
Don't use `include`/`require` manually in modern PHP. Use Composer's autoloader (covered in Chapter 8) which automatically loads classes when needed, similar to Java's ClassLoader.
:::

---

## Section 7: Practical Example - Request Router

### Goal

Build a simple request router combining control structures and functions.

```php
<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    /**
     * Register a route
     */
    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Convenience methods
     */
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * Dispatch request
     */
    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $handler = $route['handler'];
                $handler();
                return;
            }
        }

        // No route found
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}

// Usage
$router = new Router();

// Register routes
$router->get('/users', function() {
    echo json_encode(['users' => ['Alice', 'Bob', 'Charlie']]);
});

$router->get('/about', function() {
    echo json_encode(['version' => '1.0', 'name' => 'My API']);
});

$router->post('/users', function() {
    // Create user logic
    http_response_code(201);
    echo json_encode(['message' => 'User created']);
});

// Dispatch (in real app, use $_SERVER['REQUEST_METHOD'] and $_SERVER['REQUEST_URI'])
header('Content-Type: application/json');
$router->dispatch('GET', '/users');
```

---

## Section 8: Advanced Function Patterns

### Goal

Master advanced function patterns including recursion, overloading alternatives, scope management, and reflection.

### Recursion and Performance

PHP supports recursion similarly to Java, but with important differences:

::: code-group

```php [PHP Recursion]
<?php

declare(strict_types=1);

// Basic recursion - factorial
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }
    return $n * factorial($n - 1);
}

echo factorial(5);  // 120

// Tree traversal recursion
class TreeNode
{
    public function __construct(
        public mixed $value,
        public ?TreeNode $left = null,
        public ?TreeNode $right = null
    ) {}
}

function traverseTree(?TreeNode $node): void
{
    if ($node === null) {
        return;
    }
    
    echo $node->value . "\n";
    traverseTree($node->left);
    traverseTree($node->right);
}

// Tail recursion (not optimized in PHP)
function tailRecursiveSum(int $n, int $acc = 0): int
{
    if ($n === 0) {
        return $acc;
    }
    return tailRecursiveSum($n - 1, $acc + $n);
}

// ⚠️ PHP doesn't optimize tail recursion - can cause stack overflow
// Use iterative approach for large inputs
function iterativeSum(int $n): int
{
    $sum = 0;
    for ($i = 1; $i <= $n; $i++) {
        $sum += $i;
    }
    return $sum;
}

// Check recursion limit
echo ini_get('xdebug.max_nesting_level');  // Default: 256
```

```java [Java Recursion]
// Basic recursion - factorial
public int factorial(int n) {
    if (n <= 1) {
        return 1;
    }
    return n * factorial(n - 1);
}

// Tree traversal recursion
class TreeNode {
    int value;
    TreeNode left;
    TreeNode right;
}

void traverseTree(TreeNode node) {
    if (node == null) {
        return;
    }
    
    System.out.println(node.value);
    traverseTree(node.left);
    traverseTree(node.right);
}

// Tail recursion (not optimized in Java either)
int tailRecursiveSum(int n, int acc) {
    if (n == 0) {
        return acc;
    }
    return tailRecursiveSum(n - 1, acc + n);
}

// Java also doesn't optimize tail recursion
// Use iterative approach for large inputs
int iterativeSum(int n) {
    int sum = 0;
    for (int i = 1; i <= n; i++) {
        sum += i;
    }
    return sum;
}
```

:::

::: warning Recursion Limits
- **PHP default stack limit**: ~256-512 calls (configurable)
- **Java default stack limit**: ~1000-2000 calls (JVM-dependent)
- **Best practice**: Use iterative solutions for deep recursion or large datasets
- **Check limit**: `ini_get('xdebug.max_nesting_level')` or `get_cfg_var('max_execution_depth')`
:::

### Function Overloading Patterns

PHP doesn't have native function overloading like Java. Here are the alternatives:

::: code-group

```php [PHP Overloading Alternatives]
<?php

declare(strict_types=1);

// Method 1: Variadic functions (most common)
function sum(int ...$numbers): int
{
    return array_sum($numbers);
}

echo sum(1, 2, 3);  // 6
echo sum(1, 2, 3, 4, 5);  // 15

// Method 2: Union types (PHP 8.0+)
function format(int|float $value): string
{
    return number_format((float)$value, 2);
}

echo format(100);  // "100.00"
echo format(99.5);  // "99.50"

// Method 3: Named arguments for optional parameters
function createUser(
    string $name,
    int $age = 18,
    string $role = 'user',
    bool $active = true
): array {
    return [
        'name' => $name,
        'age' => $age,
        'role' => $role,
        'active' => $active
    ];
}

// Different "overloads" via named arguments
$user1 = createUser('Alice');  // age=18, role='user', active=true
$user2 = createUser('Bob', role: 'admin');  // age=18, role='admin', active=true
$user3 = createUser('Charlie', active: false);  // age=18, role='user', active=false

// Method 4: func_get_args() for dynamic signatures (legacy)
function legacySum(): int
{
    $args = func_get_args();
    return array_sum($args);
}

echo legacySum(1, 2, 3);  // 6

// Method 5: Mixed type with type checking
function process(mixed $input): string
{
    if (is_int($input)) {
        return "Integer: $input";
    }
    if (is_string($input)) {
        return "String: $input";
    }
    if (is_array($input)) {
        return "Array with " . count($input) . " items";
    }
    return "Unknown type";
}
```

```java [Java Method Overloading]
// Java has native method overloading
public int sum(int a, int b) {
    return a + b;
}

public int sum(int a, int b, int c) {
    return a + b + c;
}

public int sum(int... numbers) {
    return Arrays.stream(numbers).sum();
}

// Different parameter types
public String format(int value) {
    return String.format("%d", value);
}

public String format(double value) {
    return String.format("%.2f", value);
}

// Different parameter counts
public User createUser(String name) {
    return createUser(name, 18, "user", true);
}

public User createUser(String name, int age) {
    return createUser(name, age, "user", true);
}

public User createUser(String name, int age, String role) {
    return createUser(name, age, role, true);
}

public User createUser(String name, int age, String role, boolean active) {
    return new User(name, age, role, active);
}
```

:::

::: tip PHP Overloading Best Practices
1. **Use variadic functions** for variable argument counts
2. **Use union types** for different input types (PHP 8.0+)
3. **Use named arguments** to skip optional parameters
4. **Avoid func_get_args()** in new code - use variadic instead
5. **Prefer explicit type checking** over mixed types when possible
:::

### Advanced Closures and Scope

Understanding variable scope and closure binding is crucial:

::: code-group

```php [PHP Advanced Closures]
<?php

declare(strict_types=1);

// Static variables in functions (persist between calls)
function counter(): int
{
    static $count = 0;
    return ++$count;
}

echo counter();  // 1
echo counter();  // 2
echo counter();  // 3

// Closure with use() - explicit variable capture
$multiplier = 10;
$multiply = function(int $n) use ($multiplier): int {
    return $n * $multiplier;
};

echo $multiply(5);  // 50

// Closure with reference capture
$counter = 0;
$increment = function() use (&$counter): void {
    $counter++;
};

$increment();
$increment();
echo $counter;  // 2

// Arrow functions auto-capture by value
$factor = 5;
$double = fn(int $n): int => $n * $factor;
echo $double(10);  // 50

// Arrow functions can't capture by reference
// $increment = fn() => $counter++;  // ❌ Error: can't modify $counter

// Closure binding - change $this context
class Calculator
{
    public function __construct(
        private int $base = 0
    ) {}

    public function add(int $value): int
    {
        return $this->base + $value;
    }
}

$calc = new Calculator(10);
$bound = Closure::bind(fn(int $x): int => $this->add($x), $calc, Calculator::class);
echo $bound(5);  // 15

// Closure scope resolution
$globalVar = 'global';

function outerFunction(): callable
{
    $outerVar = 'outer';
    
    return function() use ($outerVar) {
        $innerVar = 'inner';
        // Can access: $outerVar, $innerVar
        // Cannot access: $globalVar (unless global keyword used)
        return "$outerVar - $innerVar";
    };
}

$closure = outerFunction();
echo $closure();  // "outer - inner"
```

```java [Java Lambda Scope]
// Java lambdas capture effectively final variables
int multiplier = 10;
Function<Integer, Integer> multiply = n -> n * multiplier;
System.out.println(multiply.apply(5));  // 50

// Can't modify captured variables
int counter = 0;
// counter++;  // Would make it non-final, breaking lambda

// Use array or object wrapper for mutable state
int[] counterWrapper = {0};
Runnable increment = () -> counterWrapper[0]++;
increment.run();
increment.run();
System.out.println(counterWrapper[0]);  // 2

// Method references capture instance
class Calculator {
    private int base;
    
    Calculator(int base) {
        this.base = base;
    }
    
    int add(int value) {
        return base + value;
    }
}

Calculator calc = new Calculator(10);
Function<Integer, Integer> add = calc::add;
System.out.println(add.apply(5));  // 15
```

:::

### Function Reflection

PHP's Reflection API allows introspection of functions:

::: code-group

```php [PHP Function Reflection]
<?php

declare(strict_types=1);

function exampleFunction(
    string $name,
    int $age = 18,
    ?string $email = null
): array {
    return [
        'name' => $name,
        'age' => $age,
        'email' => $email
    ];
}

// Get function reflection
$reflection = new ReflectionFunction('exampleFunction');

// Function metadata
echo $reflection->getName();  // "exampleFunction"
echo $reflection->getFileName();  // Full path to file
echo $reflection->getStartLine();  // Line number where function starts
echo $reflection->getEndLine();  // Line number where function ends

// Check if function is closure
var_dump($reflection->isClosure());  // false

// Get return type
$returnType = $reflection->getReturnType();
if ($returnType) {
    echo $returnType->getName();  // "array"
    var_dump($returnType->allowsNull());  // false
}

// Get parameters
$parameters = $reflection->getParameters();
foreach ($parameters as $param) {
    echo $param->getName() . "\n";  // name, age, email
    
    // Parameter type
    $type = $param->getType();
    if ($type) {
        echo "  Type: " . $type->getName() . "\n";
    }
    
    // Default value
    if ($param->isDefaultValueAvailable()) {
        echo "  Default: ";
        var_dump($param->getDefaultValue());
    }
    
    // Nullable
    echo "  Nullable: " . ($param->allowsNull() ? 'yes' : 'no') . "\n";
}

// Invoke function dynamically
$result = $reflection->invoke('Alice', 30, 'alice@example.com');
print_r($result);

// Invoke with named arguments (PHP 8.0+)
$result = $reflection->invokeNamedArgs([
    'name' => 'Bob',
    'email' => 'bob@example.com'
    // age uses default: 18
]);
print_r($result);

// Check if function exists
if (function_exists('exampleFunction')) {
    echo "Function exists\n";
}

// Get all functions
$allFunctions = get_defined_functions();
print_r($allFunctions['user']);  // User-defined functions
```

```java [Java Method Reflection]
import java.lang.reflect.Method;
import java.lang.reflect.Parameter;

class Example {
    public static String[] exampleMethod(
        String name,
        int age,
        String email
    ) {
        return new String[]{name, String.valueOf(age), email};
    }
}

// Get method reflection
Method method = Example.class.getMethod(
    "exampleMethod",
    String.class,
    int.class,
    String.class
);

// Method metadata
System.out.println(method.getName());  // "exampleMethod"
System.out.println(method.getReturnType());  // class [Ljava.lang.String;

// Get parameters
Parameter[] parameters = method.getParameters();
for (Parameter param : parameters) {
    System.out.println(param.getName());
    System.out.println("  Type: " + param.getType());
}

// Invoke method dynamically
String[] result = (String[]) method.invoke(
    null,  // static method, no instance
    "Alice",
    30,
    "alice@example.com"
);

// Check if method exists
try {
    Method m = Example.class.getMethod("exampleMethod",
        String.class, int.class, String.class);
    System.out.println("Method exists");
} catch (NoSuchMethodException e) {
    System.out.println("Method not found");
}
```

:::

### Function Performance Considerations

Understanding performance implications of different function patterns:

```php
<?php

declare(strict_types=1);

// Named function (fastest)
function namedAdd(int $a, int $b): int
{
    return $a + $b;
}

// Anonymous function (slightly slower)
$anonymousAdd = function(int $a, int $b): int {
    return $a + $b;
};

// Arrow function (similar performance to anonymous)
$arrowAdd = fn(int $a, int $b): int => $a + $b;

// Closure with captured variable (slight overhead)
$base = 10;
$closureAdd = function(int $a, int $b) use ($base): int {
    return $a + $b + $base;
};

// Performance tips:
// 1. Named functions are fastest (no closure overhead)
// 2. Arrow functions are slightly faster than regular closures
// 3. Capturing many variables increases memory usage
// 4. Use static functions when possible (no $this overhead)

// Memoization pattern for expensive functions
function memoize(callable $fn): callable
{
    static $cache = [];
    
    return function(...$args) use ($fn, &$cache) {
        $key = serialize($args);
        
        if (!isset($cache[$key])) {
            $cache[$key] = $fn(...$args);
        }
        
        return $cache[$key];
    };
}

// Usage
$expensiveFunction = memoize(function(int $n): int {
    // Simulate expensive computation
    sleep(1);
    return $n * 2;
});

echo $expensiveFunction(5);  // Takes 1 second
echo $expensiveFunction(5);  // Instant (cached)
```

::: tip Performance Best Practices
1. **Named functions** are fastest - use when closure isn't needed
2. **Arrow functions** have minimal overhead - prefer over regular closures for simple cases
3. **Avoid capturing large objects** in closures - increases memory usage
4. **Use static functions** when possible - no instance overhead
5. **Memoize expensive functions** - cache results for repeated calls
6. **Profile before optimizing** - function call overhead is usually negligible
:::

### Function Composition Patterns

Advanced patterns for combining functions:

```php
<?php

declare(strict_types=1);

// Function composition
function compose(callable ...$functions): callable
{
    return function(mixed $value) use ($functions): mixed {
        return array_reduce(
            array_reverse($functions),
            fn($acc, $fn) => $fn($acc),
            $value
        );
    };
}

// Usage
$process = compose(
    fn($s) => trim($s),
    fn($s) => strtolower($s),
    fn($s) => str_replace(' ', '-', $s)
);

echo $process('  Hello World  ');  // "hello-world"

// Partial application
function partial(callable $fn, ...$partialArgs): callable
{
    return function(...$remainingArgs) use ($fn, $partialArgs) {
        return $fn(...$partialArgs, ...$remainingArgs);
    };
}

// Usage
function multiply(int $a, int $b, int $c): int
{
    return $a * $b * $c;
}

$double = partial('multiply', 2);
echo $double(5, 3);  // 30 (2 * 5 * 3)

// Currying (manual implementation)
function curry(callable $fn): callable
{
    $reflection = new ReflectionFunction($fn);
    $paramCount = $reflection->getNumberOfParameters();
    
    $curryImpl = function(...$args) use ($fn, $paramCount, &$curryImpl) {
        if (count($args) >= $paramCount) {
            return $fn(...$args);
        }
        
        return function(...$nextArgs) use ($fn, $args, $paramCount, &$curryImpl) {
            return $curryImpl(...$args, ...$nextArgs);
        };
    };
    
    return $curryImpl;
}

// Usage
$curriedMultiply = curry(fn(int $a, int $b, int $c): int => $a * $b * $c);
$step1 = $curriedMultiply(2);  // Returns function expecting 2 args
$step2 = $step1(5);  // Returns function expecting 1 arg
echo $step2(3);  // 30
```

::: tip When to Use Advanced Patterns
- **Composition**: Chaining transformations in functional style
- **Partial application**: Creating specialized functions from general ones
- **Currying**: Breaking multi-argument functions into single-argument chains
- **Memoization**: Caching expensive function results
- **Use sparingly**: These patterns add complexity - use when they genuinely improve readability
:::

---

## Exercises

### Exercise 1: FizzBuzz

Write the classic FizzBuzz using PHP control structures.

**Requirements:**
- Print numbers 1-100
- For multiples of 3, print "Fizz"
- For multiples of 5, print "Buzz"
- For multiples of both 3 and 5, print "FizzBuzz"

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

function fizzBuzz(int $n): void
{
    for ($i = 1; $i <= $n; $i++) {
        echo match (true) {
            $i % 15 === 0 => 'FizzBuzz',
            $i % 3 === 0 => 'Fizz',
            $i % 5 === 0 => 'Buzz',
            default => (string)$i
        } . "\n";
    }
}

fizzBuzz(100);
```

</details>

### Exercise 2: Array Utilities

Create utility functions for array operations.

**Requirements:**
- `pluck(array $array, string $key): array` - Extract specific key from array of arrays
- `groupBy(array $array, string $key): array` - Group array elements by key value
- `unique(array $array, string $key): array` - Get unique elements by key

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

class ArrayUtils
{
    /**
     * Extract a specific key from array of arrays
     *
     * @param array<int, array<string, mixed>> $array
     */
    public static function pluck(array $array, string $key): array
    {
        return array_map(fn($item) => $item[$key] ?? null, $array);
    }

    /**
     * Group array elements by key value
     *
     * @param array<int, array<string, mixed>> $array
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function groupBy(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $item) {
            $groupKey = $item[$key] ?? 'unknown';
            $result[$groupKey][] = $item;
        }
        return $result;
    }

    /**
     * Get unique elements by key
     *
     * @param array<int, array<string, mixed>> $array
     * @return array<int, array<string, mixed>>
     */
    public static function unique(array $array, string $key): array
    {
        $seen = [];
        $result = [];

        foreach ($array as $item) {
            $value = $item[$key] ?? null;
            if (!in_array($value, $seen, true)) {
                $seen[] = $value;
                $result[] = $item;
            }
        }

        return $result;
    }
}

// Test
$users = [
    ['name' => 'Alice', 'role' => 'admin', 'age' => 30],
    ['name' => 'Bob', 'role' => 'user', 'age' => 25],
    ['name' => 'Charlie', 'role' => 'admin', 'age' => 35],
    ['name' => 'David', 'role' => 'user', 'age' => 28]
];

// Pluck names
print_r(ArrayUtils::pluck($users, 'name'));
// ['Alice', 'Bob', 'Charlie', 'David']

// Group by role
print_r(ArrayUtils::groupBy($users, 'role'));
// ['admin' => [...], 'user' => [...]]

// Unique by role
print_r(ArrayUtils::unique($users, 'role'));
// [['name' => 'Alice', 'role' => 'admin', ...], ['name' => 'Bob', 'role' => 'user', ...]]
```

</details>

### Exercise 3: Pipeline Function

Create a pipeline function that chains operations (like Java streams).

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

function pipeline(mixed $value, callable ...$functions): mixed
{
    foreach ($functions as $function) {
        $value = $function($value);
    }
    return $value;
}

// Usage
$result = pipeline(
    "  Hello World  ",
    fn($s) => trim($s),
    fn($s) => strtolower($s),
    fn($s) => str_replace(' ', '-', $s)
);

echo $result;  // "hello-world"

// With arrays
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$result = pipeline(
    $numbers,
    fn($arr) => array_filter($arr, fn($n) => $n % 2 === 0),
    fn($arr) => array_map(fn($n) => $n ** 2, $arr),
    fn($arr) => array_sum($arr)
);

echo $result;  // 220 (4 + 16 + 36 + 64 + 100)
```

</details>

---

## Section 8: Function Recursion

### Goal

Learn PHP's function recursion with examples and Java comparisons.

### Basic Recursion

Here's a classic factorial example comparing PHP and Java implementations:

```php
<?php
declare(strict_types=1);

function factorial(int $n): int {
    if ($n <= 1) {
        return 1;
    }
    return $n * factorial($n - 1);
}

echo factorial(5);  // 120
```

**Java equivalent:**
```java
public static int factorial(int n) {
    if (n <= 1) {
        return 1;
    }
    return n * factorial(n - 1);
}
```

### Tail Recursion and Stack Limits

Unlike Java, **PHP does not optimize tail recursion**, which means deeply recursive functions can hit stack limits. Here's an example with proper handling:

```php
<?php
declare(strict_types=1);

function fibonacci(int $n): int {
    if ($n < 0) {
        throw new InvalidArgumentException("n must be non-negative");
    }
    
    // Base cases
    if ($n <= 1) {
        return $n;
    }
    
    // Recursive case - note this is NOT tail recursive
    return fibonacci($n - 1) + fibonacci($n - 2);
}

// For large numbers, use iteration instead
function fibonacciIterative(int $n): int {
    if ($n < 0) {
        throw new InvalidArgumentException("n must be non-negative");
    }
    
    if ($n <= 1) {
        return $n;
    }
    
    $a = 0;
    $b = 1;
    
    for ($i = 2; $i <= $n; $i++) {
        $temp = $a + $b;
        $a = $b;
        $b = $temp;
    }
    
    return $b;
}

echo fibonacci(10);  // 55
echo fibonacciIterative(50);  // 12586269025 (much faster)
```

### Stack Overflow Prevention

PHP has a default recursion limit (typically 100-200 calls). You can check and adjust this:

```php
<?php
declare(strict_types=1);

// Check current limit
$limit = ini_get('xdebug.max_nesting_level') ?: '100';
echo "Recursion limit: $limit\n";

function safeRecursion(int $depth, int $maxDepth = 50): string {
    if ($depth >= $maxDepth) {
        return "Max depth reached at $depth";
    }
    
    return "Depth: $depth\n" . safeRecursion($depth + 1, $maxDepth);
}

echo safeRecursion(0, 10);
```

### Mutual Recursion

PHP supports mutual recursion just like Java:

```php
<?php
declare(strict_types=1);

function isEven(int $n): bool {
    if ($n === 0) {
        return true;
    }
    return isOdd(abs($n) - 1);
}

function isOdd(int $n): bool {
    if ($n === 0) {
        return false;
    }
    return isEven(abs($n) - 1);
}

$number = 42;
echo "$number is " . (isEven($number) ? 'even' : 'odd');  // 42 is even
```

### Practical Example: Tree Traversal

Here's a practical example of recursion for traversing nested structures:

```php
<?php
declare(strict_types=1);

$companyStructure = [
    'name' => 'CEO',
    'children' => [
        [
            'name' => 'CTO',
            'children' => [
                ['name' => 'Lead Developer', 'children' => []],
                ['name' => 'DevOps Engineer', 'children' => []]
            ]
        ],
        [
            'name' => 'CFO',
            'children' => [
                ['name' => 'Accountant', 'children' => []]
            ]
        ]
    ]
];

function printOrganization(array $node, int $level = 0): void {
    $indent = str_repeat('  ', $level);
    echo $indent . $node['name'] . "\n";
    
    foreach ($node['children'] as $child) {
        printOrganization($child, $level + 1);
    }
}

printOrganization($companyStructure);
```

### Java vs PHP Recursion Comparison

| Aspect | PHP | Java |
|--------|-----|------|
| **Tail Call Optimization** | ❌ Not supported | ✅ Supported (limited) |
| **Stack Limit** | ~100-200 calls | ~1000-10000 calls |
| **Performance** | Slower for deep recursion | Generally faster |
| **Memory Usage** | Higher per call | Lower per call |
| **Best Practice** | Use iteration for deep recursion | Can use recursion more freely |

### Memoization with Recursive Functions

To optimize recursive functions, use memoization (caching previous results):

```php
<?php
declare(strict_types=1);

class FibonacciMemoized {
    private static array $cache = [];
    
    public static function calculate(int $n): int {
        if ($n < 0) {
            throw new InvalidArgumentException("n must be non-negative");
        }
        
        // Check cache first
        if (isset(self::$cache[$n])) {
            return self::$cache[$n];
        }
        
        // Base cases
        if ($n <= 1) {
            return self::$cache[$n] = $n;
        }
        
        // Recursive calculation with caching
        self::$cache[$n] = self::calculate($n - 1) + self::calculate($n - 2);
        return self::$cache[$n];
    }
    
    public static function clearCache(): void {
        self::$cache = [];
    }
}

// Much faster for repeated calculations
$start = microtime(true);
echo FibonacciMemoized::calculate(35);  // 9227465
$time = microtime(true) - $start;
echo "\nTime: {$time}s\n";
```

---

## Function Attributes and Reflection

PHP 8.0+ introduced attributes (similar to Java annotations), and PHP's reflection API allows runtime inspection of functions - both crucial concepts for Java developers.

### Function Attributes

Attributes in PHP are similar to Java annotations and can be applied to functions:

```php
<?php
declare(strict_types=1);

// Define custom attributes
#[Attribute(Attribute::TARGET_FUNCTION)]
class Deprecated {
    public function __construct(
        public string $message,
        public string $since
    ) {}
}

#[Attribute(Attribute::TARGET_FUNCTION)]
class Cacheable {
    public function __construct(
        public int $ttl = 3600
    ) {}
}

// Using attributes on functions
#[Deprecated('Use newCalculate() instead', '2.0')]
function oldCalculate(int $a, int $b): int {
    return $a + $b;
}

#[Cacheable(ttl: 1800)]
function expensiveCalculation(int $input): int {
    // Simulate expensive operation
    sleep(1);
    return $input * $input;
}

// Reading attributes via reflection
$reflector = new ReflectionFunction('expensiveCalculation');
$attributes = $reflector->getAttributes(Cacheable::class);

if (!empty($attributes)) {
    $cacheAttr = $attributes[0]->newInstance();
    echo "Cache TTL: {$cacheAttr->ttl} seconds\n";
}
```

**Java equivalent:**
```java
@Deprecated(since = "2.0", forRemoval = true)
@Cacheable(ttl = 1800)
public int expensiveCalculation(int input) {
    // Implementation
}
```

### Function Reflection

PHP's reflection API provides runtime inspection of functions, similar to Java's reflection:

```php
<?php
declare(strict_types=1);

function exampleFunction(
    string $name,
    int $age = 25,
    ?string $email = null
): string {
    return "Hello $name, age $age";
}

// Reflect on the function
$reflection = new ReflectionFunction('exampleFunction');

echo "Function: " . $reflection->getName() . "\n";
echo "File: " . $reflection->getFileName() . "\n";
echo "Start line: " . $reflection->getStartLine() . "\n";

// Get parameters
$parameters = $reflection->getParameters();
foreach ($parameters as $param) {
    echo "Parameter: {$param->getName()}\n";
    echo "Type: " . ($param->getType()?->getName() ?? 'mixed') . "\n";
    echo "Has default: " . ($param->isDefaultValueAvailable() ? 'Yes' : 'No') . "\n";
    
    if ($param->isDefaultValueAvailable()) {
        echo "Default: " . var_export($param->getDefaultValue(), true) . "\n";
    }
    echo "---\n";
}

// Get return type
$returnType = $reflection->getReturnType();
echo "Return type: " . ($returnType?->getName() ?? 'mixed') . "\n";
```

### Dynamic Function Invocation

Using reflection to dynamically invoke functions with type safety:

```php
<?php
declare(strict_types=1);

class Calculator {
    public function add(int $a, int $b): int {
        return $a + $b;
    }
    
    public function divide(float $a, float $b): float {
        if ($b === 0.0) {
            throw new InvalidArgumentException("Cannot divide by zero");
        }
        return $a / $b;
    }
}

// Dynamic method invocation
$calculator = new Calculator();
$reflection = new ReflectionMethod($calculator, 'add');

// Invoke with proper type checking
$result = $reflection->invoke($calculator, 5, 3);
echo "5 + 3 = $result\n";  // 5 + 3 = 8

// Safe parameter validation
$method = new ReflectionMethod($calculator, 'divide');
$parameters = $method->getParameters();

// Validate parameters before invocation
$args = [10.0, 2.0];
if (count($args) === count($parameters)) {
    $result = $method->invoke($calculator, ...$args);
    echo "10 / 2 = $result\n";  // 10 / 2 = 5
}
```

### Function Overloading Simulation

While PHP doesn't support method overloading like Java, you can simulate it using reflection and variadic parameters:

```php
<?php
declare(strict_types=1);

class OverloadedCalculator {
    public function __call(string $name, array $arguments) {
        $reflection = new ReflectionMethod($this, $name);
        
        // Simulate overloading based on argument count
        switch ($name) {
            case 'calculate':
                return $this->handleCalculate($arguments);
            default:
                throw new BadMethodCallException("Method $name not found");
        }
    }
    
    private function handleCalculate(array $args) {
        switch (count($args)) {
            case 1:
                return $args[0] * $args[0];  // Square
            case 2:
                return $args[0] + $args[1];  // Add
            case 3:
                return $args[0] + $args[1] + $args[2];  // Add three
            default:
                return array_sum($args);  // Sum all
        }
    }
}

$calc = new OverloadedCalculator();
echo $calc->calculate(5) . "\n";        // 25 (square)
echo $calc->calculate(5, 3) . "\n";     // 8 (add)
echo $calc->calculate(1, 2, 3) . "\n";  // 6 (add three)
```

### Java vs PHP Reflection Comparison

| Feature | PHP | Java |
|---------|-----|------|
| **Annotations/Attributes** | `#[Attribute]` (PHP 8.0+) | `@interface` |
| **Runtime Reflection** | `ReflectionFunction` | `java.lang.reflect.Method` |
| **Parameter Types** | `ReflectionParameter` | `java.lang.reflect.Parameter` |
| **Return Types** | `ReflectionType` | `java.lang.reflect.Type` |
| **Dynamic Invocation** | `ReflectionFunction::invoke()` | `Method.invoke()` |
| **Method Overloading** | ❌ Not supported | ✅ Supported |
| **Type Erasure** | ❌ Not applicable | ✅ Generic type erasure |

### Practical Use Case: Validation Framework

Here's a practical example using attributes for function validation:

```php
<?php
declare(strict_types=1);

#[Attribute(Attribute::TARGET_FUNCTION)]
class Validate {
    public function __construct(
        public array $rules
    ) {}
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class NotEmpty {
    public function __construct(
        public string $message = "Value cannot be empty"
    ) {}
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class Range {
    public function __construct(
        public int $min,
        public int $max,
        public string $message = "Value out of range"
    ) {}
}

#[Validate([
    'name' => [NotEmpty::class],
    'age' => [Range::class => ['min' => 0, 'max' => 150]]
])]
function createUser(
    #[NotEmpty] string $name,
    #[Range(0, 150)] int $age
): array {
    return ['name' => $name, 'age' => $age];
}

// Validation framework implementation
class ValidationFramework {
    public static function validateAndCall(callable $function, array $args): mixed {
        $reflection = is_array($function) 
            ? new ReflectionMethod($function[0], $function[1])
            : new ReflectionFunction($function);
            
        // Validate parameters based on attributes
        // ... validation logic ...
        
        return $reflection->invoke(...$args);
    }
}
```

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Write if/else statements and understand PHP's `??` operator
- [ ] Use switch statements and PHP 8's match expressions
- [ ] Master all loop types, especially foreach
- [ ] Understand alternative syntax for templates (endif, endforeach, etc.)
- [ ] Choose the right loop type for performance and readability
- [ ] Write functions with type hints and return types
- [ ] Use default parameters and named arguments
- [ ] Create variadic functions and unpack arrays
- [ ] Use first-class callable syntax (PHP 8.1+) for cleaner code
- [ ] Create and use generator functions with yield
- [ ] Understand when generators are more efficient than arrays
- [ ] Write arrow functions and regular closures
- [ ] Understand the difference between include and require
- [ ] Know when to use `require_once` vs autoloading
- [ ] Use recursion effectively and understand stack limits
- [ ] Implement function overloading patterns using variadics, union types, and named arguments
- [ ] Master advanced closure scope and binding
- [ ] Use Reflection API to introspect functions dynamically
- [ ] Apply performance optimization techniques (memoization, composition)

::: tip Ready for More?
In [Chapter 3: OOP Basics](/series/php-for-java-developers/chapters/03-oop-basics), we'll dive deep into object-oriented programming in PHP, exploring classes, inheritance, and more advanced OOP concepts.
:::


---

## Further Reading

**PHP Documentation:**
- [Control Structures](https://www.php.net/manual/en/language.control-structures.php)
- [Functions](https://www.php.net/manual/en/language.functions.php)
- [Arrow Functions](https://www.php.net/manual/en/functions.arrow.php)
- [Match Expression](https://www.php.net/manual/en/control-structures.match.php)

**For Java Developers:**
- [PHP vs Java: Control Flow](https://www.php.net/manual/en/langref.php)
- [Closures in PHP](https://www.php.net/manual/en/class.closure.php)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/01-types-variables-and-operators">← Chapter 1: Types, Variables & Operators</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/03-oop-basics">Chapter 3: OOP Basics →</a>
  </div>
</div>
