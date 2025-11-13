---
title: "02: Control Flow & Functions"
description: "Master PHP's control structures and functions with detailed Java comparisons"
series: "php-for-java-developers"
chapter: 2
order: 2
difficulty: "Beginner"
prerequisites:
  - "/series/php-for-java-developers/chapters/01-types-variables-and-operators"
---

![Control Flow Hero](/images/php-for-java-developers/chapter-02-control-flow-hero-full.webp)

# Chapter 2: Control Flow & Functions

<Badge type="tip">Beginner</Badge> <Badge type="info">60-75 min</Badge>

## Overview

Control structures in PHP will feel very familiar to you as a Java developer—the syntax is nearly identical for most constructs. However, PHP adds some convenient features like the `foreach` loop for arrays and flexible function parameters. In this chapter, we'll explore control flow and functions, highlighting both similarities and PHP-specific enhancements.

By the end of this chapter, you'll be writing PHP control structures and functions confidently, knowing exactly how they differ from Java.

## Prerequisites

::: info Time Estimate
⏱️ **60-75 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 1: Types, Variables & Operators](/series/php-for-java-developers/chapters/01-types-variables-and-operators)
- Understanding of Java control structures and methods
- PHP 8.3 installed and configured

## What You'll Build

In this chapter, you'll create:
- A request router using switch statements
- A collection of utility functions with type hints
- A function library demonstrating closures
- A file processor using control structures

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Use PHP control structures** (if, switch, loops) effectively
- **Write type-safe functions** with proper declarations
- **Leverage PHP's flexible parameters** (default values, named arguments, variadic)
- **Create and use closures** (anonymous functions)
- **Understand include/require** for code organization

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

---

## Section 4: Closures and Anonymous Functions

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

## Section 5: Include and Require

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

## Section 6: Practical Example - Request Router

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

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Write if/else statements and understand PHP's `??` operator
- [ ] Use switch statements and PHP 8's match expressions
- [ ] Master all loop types, especially foreach
- [ ] Write functions with type hints and return types
- [ ] Use default parameters and named arguments
- [ ] Create variadic functions and unpack arrays
- [ ] Write arrow functions and regular closures
- [ ] Understand the difference between include and require
- [ ] Know when to use `require_once` vs autoloading

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
