---
title: "01: Types, Variables & Operators"
description: "Master PHP's type system, variable handling, and operators with Java comparisons"
series: "php-for-java-developers"
chapter: 1
order: 1
difficulty: "Beginner"
prerequisites:
  - "/series/php-for-java-developers/chapters/00-setup-and-first-comparison"
---

![Types and Variables Hero](/images/php-for-java-developers/chapter-01-types-hero-full.webp)

# Chapter 1: Types, Variables & Operators

<Badge type="tip">Beginner</Badge> <Badge type="info">60-75 min</Badge>

## Overview

Coming from Java's strongly-typed world, PHP's dynamic typing might seem foreign at first. However, modern PHP (7.4+) offers optional static typing that brings it closer to Java's type safety. In this chapter, we'll explore PHP's type system, understand how variables work, and learn operators—all while comparing to Java equivalents.

By the end of this chapter, you'll understand how to write type-safe PHP code that feels familiar to your Java background.

## Prerequisites

::: info Time Estimate
⏱️ **60-75 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 0: Setup & First Comparison](/series/php-for-java-developers/chapters/00-setup-and-first-comparison)
- PHP 8.3 installed and working
- IDE configured for PHP development
- Understanding of Java's primitive and object types

## What You'll Build

In this chapter, you'll create:
- A type-safe data validation class
- A utility library for type conversions
- A demonstration of PHP's type juggling vs strict types
- Operator examples comparing PHP and Java

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Understand PHP's type system** and how it differs from Java
- **Use type declarations** to write safer PHP code
- **Work with PHP's scalar types** (int, float, string, bool)
- **Leverage compound types** (arrays, objects, callables)
- **Apply strict typing** to catch type errors early
- **Use operators effectively** including PHP-specific ones

---

## Section 1: PHP's Type System Overview

### Goal

Understand PHP's dynamic typing and how strict typing brings it closer to Java.

### Comparison: Java vs PHP Type Systems

::: code-group

```java [Java - Static Typing (Compile-time)]
// Java requires type declarations
String name = "Alice";
int age = 30;
double price = 99.99;

// Type checking happens at compile time
name = 123;  // ❌ Compile error: incompatible types
```

```php [PHP - Dynamic Typing (Default)]
<?php

// PHP allows dynamic types
$name = "Alice";
$age = 30;
$price = 99.99;

// Type can change (type juggling)
$name = 123;  // ✅ Allowed by default
var_dump($name);  // int(123)
```

```php [PHP - Strict Typing (Modern PHP)]
<?php

declare(strict_types=1);  // Enable strict type checking

// With type declarations
$name = "Alice";  // string
$age = 30;        // int
$price = 99.99;   // float

function greet(string $name): void {
    echo "Hello, $name!";
}

greet(123);  // ❌ TypeError: must be of type string
```

:::

### Key Differences

| Aspect | Java | PHP (Weak Mode) | PHP (Strict Mode) |
|--------|------|-----------------|-------------------|
| **Type Declaration** | Required | Optional | Optional |
| **Type Checking** | Compile-time | Runtime | Runtime |
| **Type Coercion** | Limited (widening) | Automatic (juggling) | Minimal |
| **Error Detection** | Compile error | Type error at runtime | Type error at runtime |
| **Safety** | High | Low | High |

::: tip Recommendation for Java Developers
Always use `declare(strict_types=1);` at the top of your PHP files. This gives you type safety similar to Java and catches errors early.
:::

### Why It Matters

PHP's dynamic nature is powerful for scripting, but strict typing prevents bugs like:
- Accidentally passing wrong types to functions
- Type confusion in calculations
- Silent type conversions leading to unexpected behavior

---

## Section 2: Scalar Types

### Goal

Master PHP's scalar types and their Java equivalents.

### PHP Scalar Types

PHP has 4 scalar types that map to Java primitives:

::: code-group

```php [PHP Scalar Types]
<?php

declare(strict_types=1);

// int - Integer numbers
$count = 42;
$negative = -10;
$hex = 0xFF;        // 255
$binary = 0b1010;   // 10
$octal = 0755;      // 493

// float (aka double) - Floating point numbers
$price = 99.99;
$scientific = 1.23e4;  // 12300.0
$pi = 3.14159;

// string - Text data
$name = "Alice";
$message = 'Hello, World!';
$multiline = "Line 1\nLine 2";

// bool - Boolean values
$isActive = true;
$hasError = false;
```

```java [Java Primitives (Comparison)]
// int - Integer numbers
int count = 42;
int negative = -10;
int hex = 0xFF;         // 255
int binary = 0b1010;    // 10
int octal = 0755;       // 493

// double - Floating point numbers
double price = 99.99;
double scientific = 1.23e4;  // 12300.0
double pi = 3.14159;

// String - Text data (not a primitive!)
String name = "Alice";
String message = "Hello, World!";
String multiline = "Line 1\nLine 2";

// boolean - Boolean values
boolean isActive = true;
boolean hasError = false;
```

:::

### Type Declarations in Functions

::: code-group

```php [PHP with Type Hints]
<?php

declare(strict_types=1);

function calculateTotal(float $price, int $quantity): float
{
    return $price * $quantity;
}

function formatMessage(string $template, string $name): string
{
    return str_replace('{name}', $name, $template);
}

function isEligible(int $age, bool $hasLicense): bool
{
    return $age >= 18 && $hasLicense;
}

// Usage
$total = calculateTotal(19.99, 3);     // float(59.97)
$msg = formatMessage("Hello, {name}!", "Alice");
$eligible = isEligible(25, true);      // bool(true)
```

```java [Java with Types]
public class Example {

    public static double calculateTotal(double price, int quantity) {
        return price * quantity;
    }

    public static String formatMessage(String template, String name) {
        return template.replace("{name}", name);
    }

    public static boolean isEligible(int age, boolean hasLicense) {
        return age >= 18 && hasLicense;
    }

    // Usage
    public static void main(String[] args) {
        double total = calculateTotal(19.99, 3);
        String msg = formatMessage("Hello, {name}!", "Alice");
        boolean eligible = isEligible(25, true);
    }
}
```

:::

### String Handling: Major Differences

PHP's string handling is more powerful and flexible than Java's:

::: code-group

```php [PHP Strings]
<?php

// Double quotes: variable interpolation
$name = "Alice";
$greeting = "Hello, $name!";           // "Hello, Alice!"
$complex = "Age: {$user->getAge()}";   // Can call methods

// Single quotes: literal strings (like Java)
$literal = 'Hello, $name!';            // "Hello, $name!" (no interpolation)

// String concatenation
$full = $firstName . " " . $lastName;  // Use dot operator

// Heredoc (like Java text blocks)
$html = <<<HTML
<div class="user">
    <h1>$name</h1>
    <p>Welcome back!</p>
</div>
HTML;

// Nowdoc (no interpolation, like Java text blocks)
$raw = <<<'TEXT'
Raw text with $variables not interpreted
TEXT;
```

```java [Java Strings]
// String concatenation (verbose)
String name = "Alice";
String greeting = "Hello, " + name + "!";

// String.format() for interpolation
String formatted = String.format("Hello, %s!", name);

// Text blocks (Java 15+)
String html = """
    <div class="user">
        <h1>%s</h1>
        <p>Welcome back!</p>
    </div>
    """.formatted(name);

// No equivalent to PHP's variable interpolation
// No equivalent to calling methods in strings
```

:::

::: warning Key String Differences
1. **PHP**: Double quotes interpolate variables, single quotes don't
2. **PHP**: Use `.` for concatenation (not `+`)
3. **PHP**: Can call methods inside strings: `"{$obj->method()}"`
4. **Java**: No built-in variable interpolation
5. **Java**: Use `+` for concatenation
:::

---

## Section 3: Type Juggling vs Strict Typing

### Goal

Understand PHP's type juggling and why strict typing is better.

### Type Juggling (Weak Mode)

Without `declare(strict_types=1)`, PHP automatically converts types:

```php
<?php

// NO strict_types declaration

function add($a, $b) {
    return $a + $b;
}

// Type juggling in action
echo add(5, 3);        // 8 (int + int)
echo add(5, "3");      // 8 (int + string → int)
echo add("5", "3");    // 8 (string + string → int)
echo add(5, "3 apples");  // 8 ⚠️ (converts "3 apples" to 3!)

// Comparison type juggling
var_dump(0 == "");     // bool(true) ⚠️
var_dump(0 == "0");    // bool(true) ⚠️
var_dump(1 == "1");    // bool(true)
var_dump(1 == true);   // bool(true) ⚠️
var_dump("10" == 10);  // bool(true)
```

::: danger Type Juggling Dangers
Type juggling can cause unexpected bugs:
- `"5" + "3"` = `8` (not `"53"`)
- `"10 apples" + 5` = `15` (silently drops "apples")
- `0 == false` = `true` (confusing comparisons)
- `"" == 0` = `true` (empty string equals zero!)
:::

### Strict Typing (Recommended)

With `declare(strict_types=1)`, PHP behaves more like Java:

```php
<?php

declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}

echo add(5, 3);        // ✅ 8
echo add(5, "3");      // ❌ TypeError: Argument #2 must be of type int, string given
echo add("5", "3");    // ❌ TypeError
echo add(5, 3.5);      // ❌ TypeError: Argument #2 must be of type int, float given

// Strict comparison (always use ===)
var_dump(0 === "");    // bool(false) ✅
var_dump(1 === true);  // bool(false) ✅
var_dump("10" === 10); // bool(false) ✅
```

::: tip Best Practice for Java Developers
**Always use:**
1. `declare(strict_types=1);` at the top of every PHP file
2. Type hints for all function parameters and return values
3. `===` for comparisons (strict equality, like Java's `==`)
4. `!==` instead of `!=` (strict inequality)
:::

### Type Coercion Reference Table

Here's a quick reference for how PHP converts types in weak mode (avoid these scenarios by using strict types):

| Expression | Weak Mode Result | Strict Mode Result | Explanation |
|-----------|-----------------|-------------------|-------------|
| `"5" + 3` | `8` (int) | TypeError | String "5" coerced to int 5 |
| `"10 apples" + 5` | `15` (int) | TypeError | String parsed to 10, rest ignored |
| `true + true` | `2` (int) | TypeError | true = 1, false = 0 |
| `"hello" + 5` | `5` (int) | TypeError | Non-numeric string = 0 |
| `null + 5` | `5` (int) | TypeError | null = 0 in arithmetic |
| `[] + []` | `[]` (array) | `[]` (array) | Array union (empty result) |
| `0 == ""` | `true` | `false` with `===` | Both are falsy |
| `"0" == 0` | `true` | `false` with `===` | String coerced to int |
| `"00" == 0` | `true` | `false` with `===` | String coerced to int |
| `false == ""` | `true` | `false` with `===` | Both are falsy |

::: danger Common Pitfall
```php
// Without strict types
function calculateDiscount($price, $percent) {
    return $price - ($price * $percent / 100);
}

calculateDiscount("100", "10");  // Works but wrong!
calculateDiscount("100 dollars", "10%");  // Returns 90 (silently ignores text!)
```

**Solution:** Always use strict types!
```php
declare(strict_types=1);

function calculateDiscount(float $price, float $percent): float {
    return $price - ($price * $percent / 100);
}

calculateDiscount("100", "10");  // TypeError - caught immediately! ✅
```
:::

### Type Demonstration Script

Here's a script to see type behavior in action:

```php
<?php

echo "=== WITHOUT strict_types ===\n\n";

function addWeak($a, $b) {
    return $a + $b;
}

echo "addWeak(5, 3): " . addWeak(5, 3) . "\n";
echo "addWeak(5, '3'): " . addWeak(5, "3") . "\n";
echo "addWeak('5', '3'): " . addWeak("5", "3") . "\n";
echo "addWeak(5, '3 apples'): " . addWeak(5, "3 apples") . " ⚠️\n\n";

echo "=== WITH strict_types ===\n\n";

// In a separate file with declare(strict_types=1);
declare(strict_types=1);

function addStrict(int $a, int $b): int {
    return $a + $b;
}

try {
    echo "addStrict(5, 3): " . addStrict(5, 3) . " ✅\n";
} catch (TypeError $e) {
    echo "Error: {$e->getMessage()}\n";
}

try {
    echo "addStrict(5, '3'): ";
    echo addStrict(5, "3");  // This will throw TypeError
} catch (TypeError $e) {
    echo "TypeError - Caught as expected! ✅\n";
}

echo "\nConclusion: Always use strict_types!\n";
```

---

## Section 4: Compound Types

### Goal

Learn PHP's compound types: arrays, objects, callables, and iterables.

### Arrays in PHP vs Java Collections

PHP arrays are incredibly flexible—they're arrays, lists, and maps all in one:

::: code-group

```php [PHP Arrays]
<?php

declare(strict_types=1);

// Indexed array (like Java ArrayList)
$fruits = ["apple", "banana", "cherry"];
echo $fruits[0];  // "apple"

// Associative array (like Java HashMap)
$user = [
    "name" => "Alice",
    "age" => 30,
    "email" => "alice@example.com"
];
echo $user["name"];  // "Alice"

// Mixed array (no Java equivalent)
$mixed = [1, "two", 3.0, true];

// Multidimensional array
$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
];
echo $matrix[1][2];  // 6

// Array functions (similar to Java streams)
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($n) => $n * 2, $numbers);  // [2, 4, 6, 8, 10]
$evens = array_filter($numbers, fn($n) => $n % 2 === 0);  // [2, 4]
$sum = array_reduce($numbers, fn($acc, $n) => $acc + $n, 0);  // 15
```

```java [Java Collections]
// ArrayList (indexed)
List<String> fruits = Arrays.asList("apple", "banana", "cherry");
System.out.println(fruits.get(0));  // "apple"

// HashMap (key-value)
Map<String, Object> user = new HashMap<>();
user.put("name", "Alice");
user.put("age", 30);
user.put("email", "alice@example.com");
System.out.println(user.get("name"));  // "Alice"

// No direct equivalent to PHP's mixed array
// Need separate types or Object[]

// 2D array
int[][] matrix = {
    {1, 2, 3},
    {4, 5, 6},
    {7, 8, 9}
};
System.out.println(matrix[1][2]);  // 6

// Streams (Java 8+)
List<Integer> numbers = Arrays.asList(1, 2, 3, 4, 5);
List<Integer> doubled = numbers.stream()
    .map(n -> n * 2)
    .collect(Collectors.toList());
List<Integer> evens = numbers.stream()
    .filter(n -> n % 2 == 0)
    .collect(Collectors.toList());
int sum = numbers.stream()
    .reduce(0, Integer::sum);
```

:::

### Array Functions vs Java Streams Comparison

For Java developers familiar with Stream API, here's how PHP array functions map:

| Java Stream Operation | PHP Array Function | Example |
|----------------------|-------------------|---------|
| `.map(x -> x * 2)` | `array_map(fn($x) => $x * 2, $arr)` | Transform each element |
| `.filter(x -> x > 0)` | `array_filter($arr, fn($x) => $x > 0)` | Keep matching elements |
| `.reduce(0, (a,b) -> a+b)` | `array_reduce($arr, fn($a,$b) => $a+$b, 0)` | Combine into single value |
| `.forEach(x -> print(x))` | `array_walk($arr, fn($x) => print($x))` | Side effects on each element |
| `.anyMatch(x -> x > 0)` | `array_any($arr, fn($x) => $x > 0)` | Check if any match (PHP 8.4+) |
| `.allMatch(x -> x > 0)` | `array_all($arr, fn($x) => $x > 0)` | Check if all match (PHP 8.4+) |
| `.findFirst()` | `array_find($arr, fn($x) => condition)` | Find first matching (PHP 8.4+) |
| `.collect(toList())` | Return value (already array) | No collection needed |
| `.count()` | `count($arr)` | Get size |
| `.sorted()` | `sort($arr)` or `usort()` | Sort elements |
| `.distinct()` | `array_unique($arr)` | Remove duplicates |
| `.skip(n)` | `array_slice($arr, $n)` | Skip first n elements |
| `.limit(n)` | `array_slice($arr, 0, $n)` | Take first n elements |
| `.flatMap()` | `array_merge(...array_map())` | Flatten and map |

**Example comparison:**

::: code-group

```php [PHP]
<?php

declare(strict_types=1);

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Chain operations
$result = array_reduce(
    array_map(
        fn($n) => $n ** 2,
        array_filter($numbers, fn($n) => $n % 2 === 0)
    ),
    fn($acc, $n) => $acc + $n,
    0
);

echo "Sum of squares of evens: $result\n";  // 220
```

```java [Java]
List<Integer> numbers = Arrays.asList(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

int result = numbers.stream()
    .filter(n -> n % 2 == 0)
    .map(n -> n * n)
    .reduce(0, Integer::sum);

System.out.println("Sum of squares of evens: " + result);  // 220
```

:::

### Type Hints for Arrays

```php
<?php

declare(strict_types=1);

// Array type hint (no specific element type)
function processItems(array $items): array
{
    return array_map('strtoupper', $items);
}

// Better: Use PHPDoc for specific types (like Java generics)
/**
 * @param array<int, string> $names  Array of strings
 * @return array<int, string>
 */
function uppercaseNames(array $names): array
{
    return array_map('strtoupper', $names);
}

/**
 * @param array<string, mixed> $data  Associative array
 * @return array<string, mixed>
 */
function validateData(array $data): array
{
    // Validation logic
    return $data;
}
```

::: tip PHPDoc Annotations
PHP doesn't have generics like Java, but PHPDoc annotations provide type information to IDEs and static analyzers:
- `@param array<int, string>` = `List<String>` in Java
- `@param array<string, int>` = `Map<String, Integer>` in Java
- `@return array<string, mixed>` = `Map<String, Object>` in Java
:::

### Objects and Callables

```php
<?php

declare(strict_types=1);

class User
{
    public function __construct(
        public string $name,
        public int $age
    ) {}
}

// Object type hint
function greetUser(object $user): string
{
    // Works with any object
    return "Hello!";
}

// Specific class type hint (like Java)
function processUser(User $user): void
{
    echo "Processing {$user->name}";
}

// Callable type (like Java's functional interfaces)
function execute(callable $callback, int $value): int
{
    return $callback($value);
}

// Usage
$double = fn($n) => $n * 2;
echo execute($double, 5);  // 10

// Method reference (like Java::method)
function process(array $data, callable $processor): array
{
    return array_map($processor, $data);
}

echo process([1, 2, 3], fn($n) => $n * 2);  // [2, 4, 6]
```

---

## Section 5: Operators

### Goal

Master PHP operators and understand differences from Java.

### Arithmetic Operators

::: code-group

```php [PHP Arithmetic]
<?php

declare(strict_types=1);

$a = 10;
$b = 3;

echo $a + $b;   // 13 (addition)
echo $a - $b;   // 7  (subtraction)
echo $a * $b;   // 30 (multiplication)
echo $a / $b;   // 3.333... (division, always returns float!)
echo $a % $b;   // 1  (modulus)
echo $a ** $b;  // 1000 (exponentiation, PHP 5.6+)

// Integer division (PHP 7.0+)
echo intdiv($a, $b);  // 3

// Increment/decrement
$c = 5;
echo ++$c;  // 6 (pre-increment)
echo $c++;  // 6 (post-increment, then $c = 7)
echo --$c;  // 6 (pre-decrement)
echo $c--;  // 6 (post-decrement, then $c = 5)
```

```java [Java Arithmetic]
int a = 10;
int b = 3;

System.out.println(a + b);   // 13
System.out.println(a - b);   // 7
System.out.println(a * b);   // 30
System.out.println(a / b);   // 3 (integer division!)
System.out.println(a % b);   // 1
System.out.println(Math.pow(a, b));  // 1000.0

// For float division in Java:
System.out.println((double)a / b);  // 3.333...

// Increment/decrement (same as PHP)
int c = 5;
System.out.println(++c);  // 6
System.out.println(c++);  // 6
System.out.println(--c);  // 6
System.out.println(c--);  // 6
```

:::

::: warning Division Behavior
**Key Difference:**
- **Java**: `10 / 3` = `3` (integer division)
- **PHP**: `10 / 3` = `3.333...` (always float division)
- **PHP**: Use `intdiv(10, 3)` for integer division (returns `3`)
:::

### Comparison Operators

```php
<?php

declare(strict_types=1);

// Equality comparisons
var_dump(5 == 5);      // true (equal, with type juggling)
var_dump(5 === 5);     // true (identical, same type and value)
var_dump(5 == "5");    // true ⚠️ (type juggling)
var_dump(5 === "5");   // false ✅ (different types)

// Inequality
var_dump(5 != 6);      // true (not equal, with type juggling)
var_dump(5 !== "5");   // true ✅ (not identical)

// Relational
var_dump(5 < 10);      // true
var_dump(5 > 10);      // false
var_dump(5 <= 5);      // true
var_dump(5 >= 10);     // false

// Spaceship operator (PHP 7+, no Java equivalent)
echo 5 <=> 10;   // -1 (5 is less than 10)
echo 10 <=> 10;  // 0  (equal)
echo 15 <=> 10;  // 1  (15 is greater than 10)

// Null coalescing (PHP 7+, like Java's Optional.orElse())
$name = $user["name"] ?? "Guest";  // Use "Guest" if $user["name"] is null/undefined
$age = $data["age"] ?? $default ?? 0;  // Chain multiple ??
```

::: tip Comparison Best Practices
**For Java developers:**
1. Always use `===` and `!==` (like Java's `==` and `!=`)
2. Never use `==` or `!=` unless you specifically need type juggling
3. Use spaceship operator `<=>` for comparisons (returns -1, 0, or 1)
4. Use null coalescing `??` instead of ternary for default values
:::

### String Operators

```php
<?php

declare(strict_types=1);

// Concatenation (dot operator, NOT plus!)
$firstName = "Alice";
$lastName = "Smith";
$fullName = $firstName . " " . $lastName;  // "Alice Smith"

// Concatenation assignment
$message = "Hello";
$message .= ", World!";  // "Hello, World!" (like += in Java for strings)

// String comparison
var_dump("apple" < "banana");  // true (lexicographic)
var_dump("a" <=> "b");         // -1
```

**Java equivalent:**
```java
String firstName = "Alice";
String lastName = "Smith";
String fullName = firstName + " " + lastName;

String message = "Hello";
message += ", World!";

System.out.println("apple".compareTo("banana"));  // Negative number
```

### Logical Operators

```php
<?php

declare(strict_types=1);

$a = true;
$b = false;

// Logical AND
var_dump($a && $b);   // false (short-circuit, like Java)
var_dump($a and $b);  // false (lower precedence)

// Logical OR
var_dump($a || $b);   // true (short-circuit)
var_dump($a or $b);   // true (lower precedence)

// Logical NOT
var_dump(!$a);        // false
var_dump(!$b);        // true

// Logical XOR
var_dump($a xor $b);  // true (true if exactly one is true)
```

::: warning PHP Has Two Sets of Logical Operators
- `&&`, `||`, `!` - Higher precedence (like Java)
- `and`, `or`, `not` - Lower precedence (avoid these, use symbolic versions)

**Recommendation:** Always use `&&`, `||`, `!` for consistency with Java.
:::

### Operator Precedence

Like Java, PHP has operator precedence rules (higher precedence = evaluated first):

| Precedence | Operator | Description | Example |
|-----------|----------|-------------|---------|
| Highest | `()` | Parentheses | `(5 + 3) * 2` |
| | `**` | Exponentiation | `2 ** 3` = 8 |
| | `++`, `--`, `!` | Increment, decrement, not | `!$flag`, `$i++` |
| | `*`, `/`, `%` | Multiplication, division, modulo | `5 * 3 / 2` |
| | `+`, `-`, `.` | Addition, subtraction, concatenation | `5 + 3`, `"a" . "b"` |
| | `<`, `<=`, `>`, `>=` | Comparison | `5 < 10` |
| | `==`, `===`, `!=`, `!==` | Equality | `5 === 5` |
| | `&&` | Logical AND | `$a && $b` |
| | `\|\|` | Logical OR | `$a \|\| $b` |
| | `??` | Null coalescing | `$a ?? $b` |
| | `? :` | Ternary | `$x ? $y : $z` |
| | `=`, `+=`, `-=`, etc. | Assignment | `$a = 5` |
| Lowest | `and`, `or`, `xor` | Low-precedence logical | `$a and $b` |

**Important differences from Java:**

```php
<?php

declare(strict_types=1);

// String concatenation has higher precedence than addition
echo "Total: " . 5 + 3;  // ⚠️ "Total: 5" + 3 = 3 (confusing!)
echo "Total: " . (5 + 3);  // ✅ "Total: 8"

// Null coalescing vs ternary
$value = $data['key'] ?? 'default';  // Checks if key exists and not null
$value = $data['key'] ?: 'default';  // Checks if value is truthy (avoid!)

// Logical operator precedence trap
$result = true or false && false;  // true (because 'or' is low precedence)
$result = true || false && false;  // true (&& evaluated first)

echo $result = 5 + 3;  // ⚠️ Assignment has low precedence, echoes 8
echo ($result = 5 + 3);  // ✅ Explicit with parentheses
```

::: tip Best Practice
**Always use parentheses** when mixing operators to make precedence explicit:
```php
// Unclear
$result = $a + $b * $c;

// Clear
$result = $a + ($b * $c);

// Unclear
if ($isValid && $isActive || $isAdmin)

// Clear
if (($isValid && $isActive) || $isAdmin)
```
:::

---

## Section 6: Type Casting and Conversion

### Goal

Learn how to explicitly convert types in PHP.

### Type Casting

::: code-group

```php [PHP Type Casting]
<?php

declare(strict_types=1);

// Cast to int
$floatValue = 10.7;
$intValue = (int)$floatValue;     // 10 (truncates)
$intValue2 = intval($floatValue); // 10

// Cast to float
$intValue = 10;
$floatValue = (float)$intValue;     // 10.0
$floatValue2 = floatval($intValue); // 10.0

// Cast to string
$number = 123;
$text = (string)$number;      // "123"
$text2 = strval($number);     // "123"

// Cast to bool
$value = 1;
$bool = (bool)$value;         // true
$bool2 = boolval($value);     // true

// Cast to array
$object = (object)["name" => "Alice"];
$array = (array)$object;      // ["name" => "Alice"]

// Type checking
var_dump(is_int(10));         // true
var_dump(is_float(10.5));     // true
var_dump(is_string("hello")); // true
var_dump(is_bool(true));      // true
var_dump(is_array([1, 2]));   // true
var_dump(is_object(new stdClass())); // true
var_dump(is_null(null));      // true
```

```java [Java Type Casting]
// Cast to int
double floatValue = 10.7;
int intValue = (int)floatValue;  // 10 (truncates)

// Cast to double
int intValue = 10;
double doubleValue = (double)intValue;  // 10.0

// Convert to String
int number = 123;
String text = String.valueOf(number);  // "123"
String text2 = Integer.toString(number);

// Parse from String
int parsed = Integer.parseInt("123");
double parsedFloat = Double.parseDouble("10.5");

// Type checking with instanceof
Object obj = "Hello";
if (obj instanceof String) {
    String str = (String)obj;
}
```

:::

### Type Juggling Rules (Without Strict Types)

```php
<?php

// String to number
$str = "123";
$num = $str + 0;      // 123 (int)
$num = $str * 1;      // 123 (int)

// Truthy/Falsy values (like JavaScript)
// Falsy: false, 0, 0.0, "", "0", null, []
// Everything else is truthy

if ("") echo "truthy";        // Doesn't print (empty string is falsy)
if ("0") echo "truthy";       // Doesn't print ("0" is falsy!)
if (0) echo "truthy";         // Doesn't print
if ([]) echo "truthy";        // Doesn't print (empty array is falsy)
if ("hello") echo "truthy";   // Prints (non-empty string is truthy)
if (1) echo "truthy";         // Prints
if ([1]) echo "truthy";       // Prints (non-empty array is truthy)
```

::: danger Falsy Value Gotcha
In PHP, `"0"` (string zero) is falsy, unlike most languages:
```php
if ("0") {
    // This doesn't execute! ⚠️
}

// Solution: Use strict comparison
if ("0" !== "") {
    // This executes ✅
}
```
:::

---

## Section 7: Nullable Types and Modern Type Features

### Goal

Understand PHP 8+ nullable types, union types, and special types.

### Nullable Types (Like Java Optional)

::: code-group

```php [PHP Nullable Types]
<?php

declare(strict_types=1);

// Nullable type with ?
function findUser(int $id): ?User
{
    // Returns User or null
    $user = $this->repository->find($id);
    return $user;  // Can be null
}

// Using nullable parameters
function greet(?string $name): string
{
    if ($name === null) {
        return "Hello, Guest!";
    }
    return "Hello, $name!";
}

// Nullable with null coalescing
$user = findUser(123);
$userName = $user?->getName() ?? "Unknown";  // Null-safe operator

// Old style (still valid)
function oldStyleNullable($value = null): void
{
    // $value can be any type or null
}
```

```java [Java Optional]
// Java Optional pattern
public Optional<User> findUser(int id) {
    User user = repository.find(id);
    return Optional.ofNullable(user);
}

// Using Optional
Optional<User> userOpt = findUser(123);
String userName = userOpt
    .map(User::getName)
    .orElse("Unknown");

// Java 8+ method reference
public String greet(String name) {
    return Optional.ofNullable(name)
        .map(n -> "Hello, " + n + "!")
        .orElse("Hello, Guest!");
}
```

:::

::: tip Null-safe Operator (PHP 8.0+)
The `?->` operator is like Java's Optional.map():
```php
// PHP
$email = $user?->getProfile()?->getEmail() ?? 'no-email@example.com';

// Java equivalent
String email = Optional.ofNullable(user)
    .flatMap(u -> Optional.ofNullable(u.getProfile()))
    .map(Profile::getEmail)
    .orElse("no-email@example.com");
```
:::

### Union Types (PHP 8.0+)

Union types allow a value to be one of several types:

::: code-group

```php [PHP Union Types]
<?php

declare(strict_types=1);

// Union type: int OR float
function processNumber(int|float $value): int|float
{
    return $value * 2;
}

echo processNumber(5);      // 10
echo processNumber(5.5);    // 11.0

// Union with null (alternative to ?)
function findUser(int $id): User|null
{
    // Same as: ?User
    return $this->repository->find($id);
}

// Multiple types in union
function formatValue(int|float|string|bool $value): string
{
    return match (true) {
        is_int($value) => "Integer: $value",
        is_float($value) => "Float: $value",
        is_string($value) => "String: $value",
        is_bool($value) => "Boolean: " . ($value ? 'true' : 'false'),
    };
}

// Union types in properties (PHP 8.0+)
class Payment
{
    public function __construct(
        public int|float $amount,
        public string|null $currency = null
    ) {}
}
```

```java [Java (No Direct Equivalent)]
// Java doesn't have union types
// You'd use method overloading or common interface

public String formatValue(int value) {
    return "Integer: " + value;
}

public String formatValue(double value) {
    return "Float: " + value;
}

public String formatValue(String value) {
    return "String: " + value;
}

public String formatValue(boolean value) {
    return "Boolean: " + value;
}

// Or use generics with bounded types
public <T extends Number> T processNumber(T value) {
    // Limited compared to PHP union types
    return value;
}
```

:::

### Mixed Type (PHP 8.0+)

The `mixed` type accepts any type (like Java's `Object`):

```php
<?php

declare(strict_types=1);

// Mixed type - accepts anything
function processData(mixed $data): mixed
{
    return match (true) {
        is_array($data) => count($data),
        is_string($data) => strlen($data),
        is_int($data) => $data * 2,
        default => null
    };
}

echo processData([1, 2, 3]);    // 3
echo processData("hello");       // 5
echo processData(10);            // 20
```

**Java equivalent:**
```java
public Object processData(Object data) {
    if (data instanceof int[]) {
        return ((int[])data).length;
    } else if (data instanceof String) {
        return ((String)data).length();
    } else if (data instanceof Integer) {
        return (Integer)data * 2;
    }
    return null;
}
```

### Intersection Types (PHP 8.1+)

Intersection types require a value to implement multiple interfaces:

```php
<?php

declare(strict_types=1);

interface Loggable {
    public function log(): void;
}

interface Cacheable {
    public function cache(): void;
}

// Must implement BOTH interfaces
function process(Loggable&Cacheable $object): void
{
    $object->log();
    $object->cache();
}

class User implements Loggable, Cacheable
{
    public function log(): void { /* ... */ }
    public function cache(): void { /* ... */ }
}

$user = new User();
process($user);  // ✅ Implements both interfaces
```

**Java equivalent:**
```java
// Java uses extends for multiple interfaces
public <T extends Loggable & Cacheable> void process(T object) {
    object.log();
    object.cache();
}
```

### Never Type (PHP 8.1+)

The `never` type indicates a function never returns (like Java's void for exceptions):

```php
<?php

declare(strict_types=1);

// Never returns normally (always throws or exits)
function fail(string $message): never
{
    throw new Exception($message);
}

function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

// Usage
function processUser(int $id): User
{
    $user = findUser($id);

    if ($user === null) {
        fail("User not found");  // Never returns
    }

    return $user;  // Type checker knows $user is not null here
}
```

### Void Type (PHP 7.1+)

Like Java's `void`, indicates no return value:

```php
<?php

declare(strict_types=1);

function logMessage(string $message): void
{
    echo $message . "\n";
    // No return statement, or return with no value
}

function saveUser(User $user): void
{
    $this->repository->save($user);
    return;  // Optional
}
```

### Type System Summary

| PHP Type | Java Equivalent | Description |
|----------|----------------|-------------|
| `int` | `int` | Integer number |
| `float` | `double` | Floating point |
| `string` | `String` | Text data |
| `bool` | `boolean` | True/false |
| `array` | `List`, `Map` | Array/associative array |
| `object` | `Object` | Any object |
| `callable` | Functional interface | Function reference |
| `iterable` | `Iterable` | Array or Traversable |
| `mixed` | `Object` | Any type (PHP 8.0+) |
| `never` | Void (for exceptions) | Never returns (PHP 8.1+) |
| `void` | `void` | No return value |
| `?Type` | `Optional<Type>` | Nullable type |
| `Type1\|Type2` | No equivalent | Union type (PHP 8.0+) |
| `Type1&Type2` | `<T extends A & B>` | Intersection (PHP 8.1+) |
| `null` | `null` | Null value |

---

## Section 8: Building a Type-Safe Validator

### Goal

Create a practical validation class using strict types.

### Implementation

```php
<?php

declare(strict_types=1);

class Validator
{
    /**
     * Validate that a value is a non-empty string
     */
    public function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && $value !== "";
    }

    /**
     * Validate that a value is a positive integer
     */
    public function isPositiveInt(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    /**
     * Validate that a value is within a range
     */
    public function isInRange(int|float $value, int|float $min, int|float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Validate email format
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate array has required keys
     *
     * @param array<string, mixed> $data
     * @param array<int, string> $requiredKeys
     */
    public function hasRequiredKeys(array $data, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate and sanitize user data
     *
     * @param array<string, mixed> $data
     * @return array{valid: bool, errors: array<string, string>, data: array<string, mixed>}
     */
    public function validateUser(array $data): array
    {
        $errors = [];

        // Check required fields
        if (!$this->hasRequiredKeys($data, ['name', 'email', 'age'])) {
            $errors['missing'] = 'Missing required fields';
            return ['valid' => false, 'errors' => $errors, 'data' => []];
        }

        // Validate name
        if (!$this->isNonEmptyString($data['name'])) {
            $errors['name'] = 'Name must be a non-empty string';
        }

        // Validate email
        if (!$this->isValidEmail($data['email'])) {
            $errors['email'] = 'Invalid email format';
        }

        // Validate age
        if (!$this->isPositiveInt($data['age'])) {
            $errors['age'] = 'Age must be a positive integer';
        } elseif (!$this->isInRange($data['age'], 1, 120)) {
            $errors['age'] = 'Age must be between 1 and 120';
        }

        $valid = empty($errors);

        return [
            'valid' => $valid,
            'errors' => $errors,
            'data' => $valid ? $data : []
        ];
    }
}

// Usage
$validator = new Validator();

$userData = [
    'name' => 'Alice',
    'email' => 'alice@example.com',
    'age' => 30
];

$result = $validator->validateUser($userData);

if ($result['valid']) {
    echo "User data is valid!\n";
    print_r($result['data']);
} else {
    echo "Validation errors:\n";
    print_r($result['errors']);
}

// Test with invalid data
$invalidData = [
    'name' => '',
    'email' => 'invalid-email',
    'age' => -5
];

$invalidResult = $validator->validateUser($invalidData);
print_r($invalidResult['errors']);
```

### Expected Output

```
User data is valid!
Array
(
    [name] => Alice
    [email] => alice@example.com
    [age] => 30
)
Array
(
    [name] => Name must be a non-empty string
    [email] => Invalid email format
    [age] => Age must be a positive integer
)
```

::: tip PHP 8+ Features Used
- `mixed` type (accepts any type)
- Union types: `int|float`
- Typed properties
- Return type declarations
- Array shape documentation in PHPDoc
:::

---

## Exercises

### Exercise 1: Temperature Converter

Create a class that converts temperatures between Celsius and Fahrenheit.

**Requirements:**
- Use strict types
- Type hint all parameters and return values
- Handle invalid inputs (throw exceptions)
- Write tests for edge cases

**Starter code:**
```php
<?php

declare(strict_types=1);

class TemperatureConverter
{
    // TODO: Implement celsiusToFahrenheit
    // Formula: F = C * 9/5 + 32

    // TODO: Implement fahrenheitToCelsius
    // Formula: C = (F - 32) * 5/9
}
```

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

class TemperatureConverter
{
    public function celsiusToFahrenheit(float $celsius): float
    {
        return ($celsius * 9/5) + 32;
    }

    public function fahrenheitToCelsius(float $fahrenheit): float
    {
        return ($fahrenheit - 32) * 5/9;
    }

    public function isAbsoluteZero(float $celsius): bool
    {
        return $celsius <= -273.15;
    }
}

// Test
$converter = new TemperatureConverter();

echo $converter->celsiusToFahrenheit(0) . "°F\n";    // 32°F
echo $converter->celsiusToFahrenheit(100) . "°F\n";  // 212°F
echo $converter->fahrenheitToCelsius(32) . "°C\n";   // 0°C
echo $converter->fahrenheitToCelsius(212) . "°C\n";  // 100°C

var_dump($converter->isAbsoluteZero(-300));  // true
```

</details>

### Exercise 2: Type Juggling vs Strict Comparison

Demonstrate the differences between loose and strict comparisons.

**Task:** Create a script that shows surprising behavior with `==` and expected behavior with `===`.

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

function compareValues($a, $b): void
{
    echo "Comparing: ";
    var_export($a);
    echo " and ";
    var_export($b);
    echo "\n";

    echo "  == (loose):  ";
    var_dump($a == $b);

    echo "  === (strict): ";
    var_dump($a === $b);

    echo "\n";
}

echo "Demonstration of type juggling:\n\n";

compareValues(0, "");          // Surprising with ==
compareValues(0, "0");         // Surprising with ==
compareValues(1, "1");
compareValues(1, true);        // Surprising with ==
compareValues(false, "");      // Surprising with ==
compareValues(false, null);    // Surprising with ==
compareValues("10", 10);
compareValues([],  false);     // Surprising with ==

echo "Recommendation: Always use === for comparisons!\n";
```

</details>

### Exercise 3: Array Transformer

Create a utility class that transforms arrays similar to Java streams.

**Requirements:**
- Method to map (transform each element)
- Method to filter (keep elements matching condition)
- Method to reduce (combine elements into single value)
- Use type hints and PHPDoc

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

class ArrayTransformer
{
    /**
     * @param array<int, mixed> $array
     * @param callable(mixed): mixed $callback
     * @return array<int, mixed>
     */
    public function map(array $array, callable $callback): array
    {
        return array_map($callback, $array);
    }

    /**
     * @param array<int, mixed> $array
     * @param callable(mixed): bool $callback
     * @return array<int, mixed>
     */
    public function filter(array $array, callable $callback): array
    {
        return array_filter($array, $callback);
    }

    /**
     * @param array<int, mixed> $array
     * @param callable(mixed, mixed): mixed $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(array $array, callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($array, $callback, $initial);
    }

    /**
     * Fluent interface example
     *
     * @param array<int, int> $numbers
     * @return int
     */
    public function sumOfSquaresOfEvens(array $numbers): int
    {
        $evens = $this->filter($numbers, fn($n) => $n % 2 === 0);
        $squares = $this->map($evens, fn($n) => $n ** 2);
        return $this->reduce($squares, fn($acc, $n) => $acc + $n, 0);
    }
}

// Test
$transformer = new ArrayTransformer();
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Map: double each number
$doubled = $transformer->map($numbers, fn($n) => $n * 2);
print_r($doubled);  // [2, 4, 6, ..., 20]

// Filter: get even numbers
$evens = $transformer->filter($numbers, fn($n) => $n % 2 === 0);
print_r($evens);  // [2, 4, 6, 8, 10]

// Reduce: sum all numbers
$sum = $transformer->reduce($numbers, fn($acc, $n) => $acc + $n, 0);
echo "Sum: $sum\n";  // 55

// Chain operations
$result = $transformer->sumOfSquaresOfEvens($numbers);
echo "Sum of squares of evens: $result\n";  // 220 (4 + 16 + 36 + 64 + 100)
```

</details>

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Explain the difference between weak and strict typing in PHP
- [ ] Use `declare(strict_types=1)` in all your PHP files
- [ ] Apply type hints to function parameters and return values
- [ ] Understand PHP's scalar types: int, float, string, bool
- [ ] Work with arrays as both indexed and associative
- [ ] Use `===` for comparisons (never `==`)
- [ ] Cast between types explicitly when needed
- [ ] Leverage PHPDoc annotations for complex types
- [ ] Understand string interpolation with double quotes
- [ ] Use the null coalescing operator `??`
- [ ] Use nullable types (`?Type`) and understand null-safe operator (`?->`)
- [ ] Apply union types (`Type1|Type2`) in PHP 8.0+
- [ ] Understand operator precedence and use parentheses for clarity
- [ ] Know the type coercion rules and avoid them with strict types
- [ ] Map Java Stream operations to PHP array functions

::: tip Ready for More?
In [Chapter 2: Control Flow & Functions](/series/php-for-java-developers/chapters/02-control-flow-and-functions), we'll dive into control structures, functions, and closures, comparing them to Java equivalents.
:::

---

## Further Reading

**PHP Documentation:**
- [Type Declarations](https://www.php.net/manual/en/language.types.declarations.php)
- [Type Juggling](https://www.php.net/manual/en/language.types.type-juggling.php)
- [Operators](https://www.php.net/manual/en/language.operators.php)
- [Arrays](https://www.php.net/manual/en/language.types.array.php)

**For Java Developers:**
- [PHP's Type System](https://php.watch/versions/8.0/type-system-improvements)
- [Strict Types RFC](https://wiki.php.net/rfc/scalar_type_hints_v5)
- [PHPStan](https://phpstan.org/) - Static analysis tool (like Java's compiler checks)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/00-setup-and-first-comparison">← Chapter 0: Setup & First Comparison</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/02-control-flow-and-functions">Chapter 2: Control Flow & Functions →</a>
  </div>
</div>
