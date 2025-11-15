---
title: "Chapter 02: Swift Syntax for PHP Developers"
description: Learn Swift syntax by mapping it directly to PHP concepts you already know.
series: swift-for-php-developers
chapter: 2
difficulty: Beginner
tags: ["syntax", "basics", "php-comparison", "fundamentals"]
---

# Chapter 02: Swift Syntax for PHP Developers

Welcome to your first deep dive into Swift syntax! As a PHP developer, you already understand programming fundamentals. This chapter maps Swift syntax directly to PHP concepts you know, highlighting similarities and differences.

## What You'll Learn

- Variables and constants (let vs var vs PHP's $)
- Basic data types and type annotations
- Control flow (if, switch, loops)
- Functions and parameters
- String operations and interpolation
- Comments and documentation
- Naming conventions

## Prerequisites

- Completed [Chapter 01: Setting Up Environment](/series/swift-for-php-developers/chapters/01-setting-up-environment)
- Expert-level PHP knowledge
- Understanding of basic programming concepts

---

## Variables and Constants

### PHP Approach

```php
<?php
// PHP: All variables use $, mutable by default
$name = "John";
$age = 30;

// Can reassign anytime
$name = "Jane";
$age = 31;

// Constants (immutable)
define('API_KEY', 'secret');
const MAX_USERS = 100;
```

### Swift Approach

```swift
// Swift: Explicit mutability with let vs var

// var = mutable (can be reassigned)
var name = "John"
var age = 30

name = "Jane"  // ✅ Allowed
age = 31       // ✅ Allowed

// let = immutable (cannot be reassigned)
let apiKey = "secret"
let maxUsers = 100

// apiKey = "new"  // ❌ Error: Cannot assign to value: 'apiKey' is a 'let' constant
```

**Key Differences:**

| PHP | Swift | Notes |
|-----|-------|-------|
| `$variable = value;` | `var variable = value` | Mutable |
| `$variable = value;` | `let variable = value` | Immutable (prefer this!) |
| `define('NAME', value)` | `let NAME = value` | Constant |
| `const NAME = value` | `let NAME = value` | Class constant |

**Swift Best Practice:** Use `let` by default, only use `var` when you know the value will change. The compiler will suggest this.

---

## Type System

### PHP: Dynamic Typing (Optional Type Hints)

```php
<?php
// PHP 7.4+: Type hints are optional
$name = "John";              // No type specified
$age = 30;                   // Type inferred at runtime

// With type hints (recommended but optional)
function greet(string $name): string {
    return "Hello, $name";
}

// Type juggling (automatic conversion)
$x = "10";
$y = 5;
echo $x + $y;  // Outputs: 15 (string becomes int)
```

### Swift: Static Typing (Compile-Time)

```swift
// Swift: Types are enforced at compile time

// Type inference (Swift figures out the type)
let name = "John"  // Inferred as String
let age = 30       // Inferred as Int

// Explicit type annotation
let name: String = "John"
let age: Int = 30

// Functions must specify types
func greet(name: String) -> String {
    return "Hello, \(name)"
}

// No automatic type conversion
let x = "10"
let y = 5
// let result = x + y  // ❌ Error: Cannot convert String to Int

// Must explicitly convert
let result = Int(x)! + y  // Converts "10" to 10
```

**Key Differences:**

| Aspect | PHP | Swift |
|--------|-----|-------|
| **Type checking** | Runtime | Compile-time |
| **Type hints** | Optional | Required (or inferred) |
| **Type conversion** | Automatic | Explicit |
| **Type safety** | Weak | Strong |

---

## Basic Data Types

### Comparison Table

| PHP Type | Swift Type | Example |
|----------|------------|---------|
| `int` | `Int` | `let age: Int = 30` |
| `float` | `Double` | `let price: Double = 19.99` |
| `string` | `String` | `let name: String = "John"` |
| `bool` | `Bool` | `let active: Bool = true` |
| `array` | `Array<Type>` or `[Type]` | `let numbers: [Int] = [1, 2, 3]` |
| `array` (assoc) | `Dictionary<Key, Value>` | `let ages: [String: Int] = ["John": 30]` |
| `null` | `nil` (with Optionals) | `let name: String? = nil` |

### Examples

```php
<?php
// PHP
$age = 30;                    // int
$price = 19.99;              // float
$name = "John";              // string
$active = true;              // bool
$numbers = [1, 2, 3];        // array
$person = ['name' => 'John', 'age' => 30];  // associative array
```

```swift
// Swift
let age: Int = 30
let price: Double = 19.99
let name: String = "John"
let active: Bool = true
let numbers: [Int] = [1, 2, 3]
let person: [String: Any] = ["name": "John", "age": 30]

// Better: Use a struct instead of dictionary
struct Person {
    let name: String
    let age: Int
}
let person = Person(name: "John", age: 30)
```

---

## Control Flow: If Statements

### PHP

```php
<?php
// PHP: Parentheses required, braces optional for single line
if ($age >= 18) {
    echo "Adult";
} elseif ($age >= 13) {
    echo "Teen";
} else {
    echo "Child";
}

// Ternary
$status = $age >= 18 ? "Adult" : "Minor";
```

### Swift

```swift
// Swift: No parentheses needed, braces always required
if age >= 18 {
    print("Adult")
} else if age >= 13 {
    print("Teen")
} else {
    print("Child")
}

// Ternary (same as PHP)
let status = age >= 18 ? "Adult" : "Minor"
```

**Key Differences:**
- Swift doesn't require `()` around conditions
- Swift always requires `{}` braces (no single-line shortcuts)
- `else if` instead of `elseif`

---

## Control Flow: Switch Statements

### PHP

```php
<?php
// PHP: Requires break, fall-through by default
switch ($day) {
    case 1:
        echo "Monday";
        break;
    case 2:
        echo "Tuesday";
        break;
    default:
        echo "Other";
        break;
}
```

### Swift

```swift
// Swift: No break needed, no fall-through
switch day {
case 1:
    print("Monday")
case 2:
    print("Tuesday")
default:
    print("Other")
}

// Multiple values
switch day {
case 1, 2, 3, 4, 5:
    print("Weekday")
case 6, 7:
    print("Weekend")
default:
    print("Invalid")
}

// Range matching
switch age {
case 0..<13:
    print("Child")
case 13..<18:
    print("Teen")
case 18...120:
    print("Adult")
default:
    print("Invalid age")
}
```

**Swift switch is much more powerful:**
- No break needed (doesn't fall through)
- Can match ranges
- Can match multiple values
- Must be exhaustive (cover all cases)

---

## Loops: For

### PHP

```php
<?php
// Traditional for loop
for ($i = 0; $i < 5; $i++) {
    echo $i . "\n";
}

// Foreach
$names = ["John", "Jane", "Bob"];
foreach ($names as $name) {
    echo $name . "\n";
}

// Foreach with keys
$ages = ["John" => 30, "Jane" => 25];
foreach ($ages as $name => $age) {
    echo "$name is $age\n";
}
```

### Swift

```swift
// For-in loop (most common)
for i in 0..<5 {
    print(i)
}

// Foreach equivalent
let names = ["John", "Jane", "Bob"]
for name in names {
    print(name)
}

// With enumeration (index + value)
for (index, name) in names.enumerated() {
    print("\(index): \(name)")
}

// Dictionary iteration
let ages = ["John": 30, "Jane": 25]
for (name, age) in ages {
    print("\(name) is \(age)")
}

// Range variations
for i in 0..<5 { }    // 0, 1, 2, 3, 4 (excludes 5)
for i in 0...5 { }    // 0, 1, 2, 3, 4, 5 (includes 5)
for i in (0..<5).reversed() { }  // 4, 3, 2, 1, 0
```

**Key Differences:**
- Swift uses ranges (`0..<5`, `0...5`)
- `for-in` instead of `foreach`
- No traditional C-style `for (init; condition; increment)`

---

## Loops: While

### PHP and Swift (Very Similar!)

```php
<?php
// PHP
$count = 0;
while ($count < 5) {
    echo $count . "\n";
    $count++;
}

// Do-while
$count = 0;
do {
    echo $count . "\n";
    $count++;
} while ($count < 5);
```

```swift
// Swift
var count = 0
while count < 5 {
    print(count)
    count += 1
}

// Repeat-while (same as do-while)
var count = 0
repeat {
    print(count)
    count += 1
} while count < 5
```

**Differences:**
- Swift uses `repeat` instead of `do` (do is used for error handling)
- Same logic otherwise

---

## Functions

### PHP

```php
<?php
// Basic function
function greet($name) {
    return "Hello, $name";
}

// With type hints
function greet(string $name): string {
    return "Hello, $name";
}

// Default parameters
function greet(string $name = "Guest"): string {
    return "Hello, $name";
}

// Multiple parameters
function add(int $a, int $b): int {
    return $a + $b;
}

// Calling
echo greet("John");
echo add(5, 3);
```

### Swift

```swift
// Basic function
func greet(name: String) -> String {
    return "Hello, \(name)"
}

// Default parameters
func greet(name: String = "Guest") -> String {
    return "Hello, \(name)"
}

// Multiple parameters
func add(a: Int, b: Int) -> Int {
    return a + b
}

// Calling (with parameter labels!)
print(greet(name: "John"))
print(add(a: 5, b: 3))

// External parameter names
func greet(person name: String) -> String {
    return "Hello, \(name)"
}
greet(person: "John")  // 'person' externally, 'name' internally

// Omit external name with underscore
func add(_ a: Int, _ b: Int) -> Int {
    return a + b
}
add(5, 3)  // No labels needed
```

**Key Differences:**

| Aspect | PHP | Swift |
|--------|-----|-------|
| Declaration | `function name()` | `func name()` |
| Return type | `: type` after params | `-> Type` after params |
| Parameter labels | Not used | Required at call site |
| No return | `void` or omit | Omit `->` or `-> Void` |

---

## Strings

### String Interpolation

```php
<?php
// PHP
$name = "John";
$age = 30;

// Concatenation
echo "Name: " . $name . ", Age: " . $age;

// Double quotes (variable interpolation)
echo "Name: $name, Age: $age";

// Curly braces for complex expressions
echo "Name: {$user->name}";
```

```swift
// Swift: String interpolation with \()
let name = "John"
let age = 30

// String interpolation (works anywhere)
print("Name: \(name), Age: \(age)")

// Can include expressions
print("Next year: \(age + 1)")
print("Uppercase: \(name.uppercased())")

// Concatenation (also works)
print("Name: " + name + ", Age: " + String(age))
```

**Swift Best Practice:** Always use `\()` for interpolation, it's cleaner and type-safe.

### String Operations

```php
<?php
// PHP
$str = "Hello, World!";

strlen($str);                // Length
strtoupper($str);           // Uppercase
strtolower($str);           // Lowercase
trim($str);                 // Trim whitespace
str_replace("World", "Swift", $str);  // Replace
explode(",", $str);         // Split
implode(",", $array);       // Join
```

```swift
// Swift
let str = "Hello, World!"

str.count                               // Length
str.uppercased()                        // Uppercase
str.lowercased()                        // Lowercase
str.trimmingCharacters(in: .whitespaces)  // Trim
str.replacingOccurrences(of: "World", with: "Swift")  // Replace
str.split(separator: ",")               // Split
array.joined(separator: ",")            // Join

// Additional Swift string methods
str.contains("World")                   // true
str.hasPrefix("Hello")                  // true
str.hasSuffix("!")                      // true
str.isEmpty                             // false
```

---

## Comments and Documentation

### PHP

```php
<?php
// Single line comment

/*
 * Multi-line comment
 */

/**
 * PHPDoc comment
 *
 * @param string $name The user's name
 * @param int $age The user's age
 * @return string The greeting message
 */
function greet(string $name, int $age): string {
    return "Hello, $name ($age)";
}
```

### Swift

```swift
// Single line comment

/*
 Multi-line comment
 */

/// Documentation comment (single line)
/// This function greets a user

/**
 Documentation comment (multi-line)

 Greets a user with their name and age.

 - Parameter name: The user's name
 - Parameter age: The user's age
 - Returns: The greeting message
 */
func greet(name: String, age: Int) -> String {
    return "Hello, \(name) (\(age))"
}
```

**Swift uses `///` or `/** */` for documentation that appears in Xcode.**

---

## Naming Conventions

### PHP

```php
<?php
// PHP: snake_case for functions/variables, PascalCase for classes

// Variables and functions
$user_name = "John";
$user_age = 30;

function get_user_name() { }
function calculate_total_price() { }

// Classes
class UserProfile { }
class DatabaseConnection { }

// Constants
define('MAX_UPLOAD_SIZE', 1024);
const API_BASE_URL = 'https://api.example.com';
```

### Swift

```swift
// Swift: camelCase for variables/functions, PascalCase for types

// Variables and functions
let userName = "John"
let userAge = 30

func getUserName() { }
func calculateTotalPrice() { }

// Types (classes, structs, enums, protocols)
class UserProfile { }
struct DatabaseConnection { }
enum UserRole { }
protocol Drawable { }

// Constants (same as variables)
let maxUploadSize = 1024
let apiBaseURL = "https://api.example.com"
```

**Convention Comparison:**

| Element | PHP | Swift |
|---------|-----|-------|
| Variables | `$snake_case` | `camelCase` |
| Functions | `snake_case()` | `camelCase()` |
| Classes | `PascalCase` | `PascalCase` |
| Constants | `SCREAMING_SNAKE_CASE` | `camelCase` |

---

## Operators

### Arithmetic (Same!)

| Operator | PHP | Swift | Description |
|----------|-----|-------|-------------|
| `+` | ✅ | ✅ | Addition |
| `-` | ✅ | ✅ | Subtraction |
| `*` | ✅ | ✅ | Multiplication |
| `/` | ✅ | ✅ | Division |
| `%` | ✅ | ✅ | Modulo |
| `**` | ✅ | ❌ | Power (use `pow()` in Swift) |

### Comparison

| Operator | PHP | Swift | Notes |
|----------|-----|-------|-------|
| `==` | Loose equality | Equality | Swift only has strict |
| `===` | Strict equality | N/A | Swift `==` is always strict |
| `!=` | ✅ | ✅ | Not equal |
| `!==` | ✅ | ❌ | Not identical |
| `<`, `>`, `<=`, `>=` | ✅ | ✅ | Comparison |

### Logical (Same!)

| Operator | PHP | Swift |
|----------|-----|-------|
| `&&` | ✅ | ✅ |
| `\|\|` | ✅ | ✅ |
| `!` | ✅ | ✅ |

### Increment/Decrement

```php
<?php
// PHP
$x++;  // Post-increment
++$x;  // Pre-increment
$x--;  // Post-decrement
--$x;  // Pre-decrement
```

```swift
// Swift (Swift 3+ removed ++ and --)
var x = 5
x += 1  // Increment
x -= 1  // Decrement

// No more ++ or --
```

---

## Nil Coalescing

### PHP (7.0+)

```php
<?php
$name = $user->getName() ?? 'Guest';
$config = $_GET['config'] ?? 'default';
```

### Swift

```swift
let name = user?.getName() ?? "Guest"
let config = queryParams["config"] ?? "default"
```

**Exactly the same concept!** The `??` operator works identically.

---

## Hands-On Exercise

Translate this PHP function to Swift:

```php
<?php
function calculateDiscount(float $price, int $quantity, ?string $coupon = null): float {
    $subtotal = $price * $quantity;

    if ($quantity >= 10) {
        $subtotal *= 0.9;  // 10% bulk discount
    }

    if ($coupon === 'SAVE20') {
        $subtotal *= 0.8;  // 20% coupon discount
    }

    return $subtotal;
}

echo calculateDiscount(10.0, 5);  // 50.0
echo calculateDiscount(10.0, 12);  // 108.0
echo calculateDiscount(10.0, 12, 'SAVE20');  // 86.4
```

### Solution

```swift
func calculateDiscount(price: Double, quantity: Int, coupon: String? = nil) -> Double {
    var subtotal = price * Double(quantity)

    if quantity >= 10 {
        subtotal *= 0.9  // 10% bulk discount
    }

    if coupon == "SAVE20" {
        subtotal *= 0.8  // 20% coupon discount
    }

    return subtotal
}

print(calculateDiscount(price: 10.0, quantity: 5))  // 50.0
print(calculateDiscount(price: 10.0, quantity: 12))  // 108.0
print(calculateDiscount(price: 10.0, quantity: 12, coupon: "SAVE20"))  // 86.4
```

**Key Differences:**
1. Parameter labels at call site
2. `String?` for optional parameter
3. `Double(quantity)` explicit conversion
4. `==` for comparison (no `===` needed)

---

## Quick Reference Table

| Concept | PHP | Swift |
|---------|-----|-------|
| Variable | `$name = "John";` | `var name = "John"` |
| Constant | `const NAME = "John";` | `let name = "John"` |
| If statement | `if ($x > 0) { }` | `if x > 0 { }` |
| For loop | `for ($i=0; $i<5; $i++)` | `for i in 0..<5` |
| Foreach | `foreach ($arr as $item)` | `for item in arr` |
| Function | `function name() { }` | `func name() { }` |
| String interpolation | `"Hello $name"` | `"Hello \(name)"` |
| Null | `null` | `nil` |
| Null coalescing | `$x ?? 'default'` | `x ?? "default"` |
| Comment | `// Comment` | `// Comment` |
| Doc comment | `/** */` | `/// ` or `/** */` |

---

## Summary

You've now learned Swift syntax by mapping it to PHP:

✅ Variables (`var`) and constants (`let`) vs PHP's `$variable`
✅ Static typing vs dynamic typing
✅ Control flow (if, switch, loops)
✅ Functions with parameter labels
✅ String interpolation with `\()`
✅ Naming conventions (camelCase)
✅ Swift is stricter but catches more errors at compile time

**Key Takeaway:** Swift syntax is cleaner and more explicit than PHP. The compiler is your friend—it catches bugs before runtime.

---

## What's Next?

In [Chapter 03: Types, Constants, and Variables](/series/swift-for-php-developers/chapters/03-types-constants-variables), you'll dive deeper into Swift's type system and understand value types vs reference types.

---

## Practice Challenges

1. Convert your favorite PHP function to Swift
2. Write a Swift function that takes a name and age, returns a greeting
3. Create a switch statement that categorizes ages into groups
4. Practice string interpolation with complex expressions

---

**Next Chapter:** [03 — Types, Constants, and Variables](/series/swift-for-php-developers/chapters/03-types-constants-variables)
