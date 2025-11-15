---
title: "Chapter 03: Types, Constants, and Variables"
description: Deep dive into Swift's type system, value types vs reference types, and why type safety matters.
series: swift-for-php-developers
chapter: 3
difficulty: Beginner
tags: ["types", "type-safety", "constants", "variables", "value-types"]
---

# Chapter 03: Types, Constants, and Variables

Now that you understand basic Swift syntax, let's dive deeper into Swift's type system. As a PHP developer accustomed to dynamic typing, understanding Swift's static type system is crucial for writing safe, performant code.

## What You'll Learn

- Swift's type system philosophy
- Type inference vs explicit type annotations
- Value types vs reference types (introduction)
- Constants (let) vs variables (var) in depth
- Type safety and compile-time checking
- Type conversion and casting
- Common Swift types

## Prerequisites

- Completed [Chapter 02: Swift Syntax](/series/swift-for-php-developers/chapters/02-swift-syntax-for-php-developers)
- Understanding of PHP type hints and strict types

---

## The Type System Philosophy

### PHP: Dynamic Typing

```php
<?php
// PHP: Types determined at runtime
$value = "Hello";      // String
$value = 42;           // Now an Int
$value = 3.14;         // Now a Float
$value = [];           // Now an Array

// Type hints help but don't prevent reassignment
function greet(string $name): string {
    return "Hello, $name";
}

// Runtime type checking
// greet(123);  // TypeError at runtime
```

**PHP Philosophy:** Flexible and forgiving. Types can change. Errors appear when code runs.

### Swift: Static Typing

```swift
// Swift: Types determined at compile time
var value = "Hello"  // Type is String forever
value = "World"      // ✅ OK: Still a String
// value = 42        // ❌ Error: Cannot assign Int to String

func greet(name: String) -> String {
    return "Hello, \(name)"
}

// Compile-time type checking
// greet(name: 123)  // ❌ Compile error: Cannot convert Int to String
```

**Swift Philosophy:** Safe and strict. Types are fixed. Errors caught before code runs.

---

## Type Inference

Swift can infer types from context, so you don't always need to write them explicitly.

### Inference in Action

```swift
// Swift infers the type
let name = "John"              // Inferred as String
let age = 30                   // Inferred as Int
let price = 19.99             // Inferred as Double
let active = true              // Inferred as Bool
let numbers = [1, 2, 3]        // Inferred as [Int]
let person = ["name": "John"]  // Inferred as [String: String]

// Hover over a variable in Xcode to see its inferred type
```

**PHP Comparison:**
```php
<?php
// PHP infers types but they can change
$name = "John";      // String (until reassigned)
$age = 30;           // Int (until reassigned)
```

### When to Use Explicit Types

```swift
// ✅ Good: Inference is clear
let name = "John"  // Obviously a String

// ✅ Good: Explicit when needed for clarity
let temperature: Double = 72  // Could be Int, but we want Double
let users: [User] = []        // Empty array needs type

// ❌ Redundant: Type is obvious
let name: String = "John"  // String is obvious from "John"

// ✅ Required: Cannot infer from context
var userOptional: User?    // Optional requires type
var items: [Item] = []     // Empty collection needs type
```

---

## Basic Types in Detail

### Int: Integer Numbers

```swift
// Integer (whole numbers)
let age: Int = 30
let year = 2024         // Inferred as Int

// Size variants (usually just use Int)
let small: Int8 = 127        // -128 to 127
let medium: Int16 = 32767    // -32,768 to 32,767
let large: Int32 = 2147483647
let huge: Int64 = 9223372036854775807

// Unsigned (positive only)
let count: UInt = 100  // 0 to 4,294,967,295 (on 64-bit)

// Min/Max values
Int.min  // Minimum value
Int.max  // Maximum value
```

**PHP Comparison:**
```php
<?php
$age = 30;  // int (size determined by platform)
```

### Double and Float: Decimal Numbers

```swift
// Double (preferred, 64-bit precision)
let price: Double = 19.99
let pi = 3.14159265359  // Inferred as Double

// Float (32-bit, less common)
let temperature: Float = 72.5

// Swift always infers floating-point as Double
let implicitDouble = 3.14  // Double, not Float
```

**PHP Comparison:**
```php
<?php
$price = 19.99;  // float
```

### String: Text

```swift
// String literals
let name = "John"
let empty = ""
let multiline = """
    This is a
    multi-line
    string.
    """

// String properties
name.count         // Length: 4
name.isEmpty       // false
empty.isEmpty      // true

// String methods
name.uppercased()  // "JOHN"
name.lowercased()  // "john"
```

**PHP Comparison:**
```php
<?php
$name = "John";
strlen($name);      // 4
strtoupper($name);  // "JOHN"
```

### Bool: Boolean

```swift
// Boolean values
let isActive = true
let isAdmin = false

// Boolean operators
let canAccess = isActive && isAdmin  // AND
let hasPermission = isActive || isAdmin  // OR
let denied = !isActive  // NOT

// Used in conditionals
if isActive {
    print("Active user")
}
```

**PHP Comparison:**
```php
<?php
$isActive = true;
if ($isActive) {
    echo "Active user";
}
```

---

## Constants vs Variables: Deep Dive

### Let: Immutable (Constants)

```swift
// Value cannot be changed after initialization
let name = "John"
// name = "Jane"  // ❌ Error: Cannot assign to value: 'name' is a 'let' constant

// But properties of a reference type CAN change
class Person {
    var name: String
    init(name: String) { self.name = name }
}

let person = Person(name: "John")
// person = Person(name: "Jane")  // ❌ Cannot reassign
person.name = "Jane"  // ✅ Can modify properties
```

**PHP Comparison:**
```php
<?php
define('NAME', 'John');
// NAME = 'Jane';  // Error

const NAME = 'John';
// self::NAME = 'Jane';  // Error
```

### Var: Mutable (Variables)

```swift
// Value can be changed
var count = 0
count = 1     // ✅ OK
count += 5    // ✅ OK
```

### Best Practice: Use `let` by Default

```swift
// ❌ Bad: Using var when value doesn't change
var name = "John"  // Never reassigned

// ✅ Good: Using let for immutable values
let name = "John"

// ✅ Good: Using var only when needed
var score = 0
for _ in 1...10 {
    score += 1  // Score changes, so var is appropriate
}
```

**Why prefer `let`?**
1. **Intent**: Shows this value won't change
2. **Safety**: Compiler prevents accidental changes
3. **Performance**: Compiler can optimize better
4. **Thread safety**: Immutable values are inherently thread-safe

---

## Type Safety and Compile-Time Checking

### Type Mismatch Errors

```swift
// ❌ Cannot mix types without explicit conversion
let age = 30
let message = "Age: " + age  // Error: Cannot convert Int to String

// ✅ Must explicitly convert
let message = "Age: " + String(age)  // OK
let message = "Age: \(age)"          // OK: String interpolation
```

```php
<?php
// PHP: Automatic type coercion
$age = 30;
$message = "Age: " . $age;  // ✅ Works, converts to string
```

### Integer Division

```swift
// Integer division truncates
let result = 5 / 2  // 2, not 2.5

// For decimal result, use Double
let result = 5.0 / 2.0  // 2.5
let result = Double(5) / Double(2)  // 2.5
```

```php
<?php
// PHP: Returns float for division
$result = 5 / 2;  // 2.5

// Use intdiv() for integer division
$result = intdiv(5, 2);  // 2
```

---

## Type Conversion

### Explicit Conversion

```swift
// String to Int
let str = "123"
let num = Int(str)  // Optional<Int>, might fail!

if let num = num {
    print("Converted: \(num)")
} else {
    print("Conversion failed")
}

// Int to String
let age = 30
let str = String(age)  // "30"

// Double to Int (truncates)
let price = 19.99
let rounded = Int(price)  // 19

// Int to Double
let count = 5
let decimal = Double(count)  // 5.0
```

**PHP Comparison:**
```php
<?php
// PHP: Type casting
$str = "123";
$num = (int)$str;     // 123
$num = intval($str);  // 123

$age = 30;
$str = (string)$age;  // "30"
$str = strval($age);  // "30"
```

### Failed Conversions

```swift
// Converting invalid string returns nil
let invalid = Int("abc")  // nil (Optional)

// Must handle the optional
if let number = Int("abc") {
    print("Number: \(number)")
} else {
    print("Invalid number")  // This executes
}
```

---

## Value Types vs Reference Types (Introduction)

This is **critical** to understand. PHP uses reference semantics for objects; Swift has both.

### Value Types (Copy on Assignment)

```swift
// Structs are value types
struct Point {
    var x: Int
    var y: Int
}

var point1 = Point(x: 0, y: 0)
var point2 = point1  // Copies the value

point2.x = 10

print(point1.x)  // 0 (not modified!)
print(point2.x)  // 10 (modified)
```

**Key:** Changing `point2` doesn't affect `point1`. They're independent copies.

### Reference Types (Share on Assignment)

```swift
// Classes are reference types
class Person {
    var name: String
    init(name: String) { self.name = name }
}

let person1 = Person(name: "John")
let person2 = person1  // Same reference!

person2.name = "Jane"

print(person1.name)  // "Jane" (modified!)
print(person2.name)  // "Jane" (same object)
```

**Key:** Changing `person2` DOES affect `person1`. They reference the same object.

### PHP Comparison (Everything is Reference)

```php
<?php
// PHP objects are always references
class Person {
    public string $name;
    public function __construct(string $name) {
        $this->name = $name;
    }
}

$person1 = new Person("John");
$person2 = $person1;  // Same reference

$person2->name = "Jane";

echo $person1->name;  // "Jane" (modified!)
echo $person2->name;  // "Jane"

// To copy, use clone
$person3 = clone $person1;
$person3->name = "Bob";
echo $person1->name;  // "Jane" (not modified)
```

**We'll cover this in depth in Chapter 06.**

---

## Type Aliases

Create custom names for existing types.

```swift
// Create an alias
typealias UserID = Int
typealias Coordinate = (Double, Double)

// Use the alias
let userID: UserID = 12345
let location: Coordinate = (37.7749, -122.4194)

// Still compatible with original type
let id: Int = userID  // OK
```

**PHP Comparison:**
PHP doesn't have type aliases, but you might use:
```php
<?php
// PHPDoc pseudo-type
/** @var int $userID */
$userID = 12345;
```

---

## Tuples

Swift has tuples (PHP doesn't have a direct equivalent).

```swift
// Tuple: Group multiple values
let person = ("John", 30)
let name = person.0  // "John"
let age = person.1   // 30

// Named tuple elements
let person = (name: "John", age: 30)
print(person.name)  // "John"
print(person.age)   // 30

// Tuple return from function
func getUser() -> (name: String, age: Int) {
    return ("John", 30)
}

let user = getUser()
print(user.name)
```

**PHP Comparison:**
```php
<?php
// PHP: Use array or object
function getUser() {
    return ['name' => 'John', 'age' => 30];
}

$user = getUser();
echo $user['name'];
```

---

## Type Annotation Syntax

```swift
// Basic syntax
let variableName: Type = value

// Examples
let name: String = "John"
let age: Int = 30
let price: Double = 19.99
let active: Bool = true

// Arrays
let numbers: [Int] = [1, 2, 3]
let names: [String] = ["John", "Jane"]

// Dictionaries
let ages: [String: Int] = ["John": 30, "Jane": 25]

// Optionals (next chapter!)
let optional: String? = nil

// Functions
let greet: (String) -> String = { name in
    return "Hello, \(name)"
}
```

---

## Practical Example: Type-Safe User Model

### PHP Approach

```php
<?php
class User {
    public int $id;
    public string $name;
    public string $email;
    public bool $isAdmin;
    public ?string $phone;  // Nullable

    public function __construct(
        int $id,
        string $name,
        string $email,
        bool $isAdmin = false,
        ?string $phone = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->isAdmin = $isAdmin;
        $this->phone = $phone;
    }
}

$user = new User(1, "John", "john@example.com");
```

### Swift Approach (with Struct)

```swift
struct User {
    let id: Int
    let name: String
    let email: String
    var isAdmin: Bool = false
    var phone: String? = nil
}

// Create instance
let user = User(
    id: 1,
    name: "John",
    email: "john@example.com"
)

// Access properties (type-safe!)
print(user.name)  // String
print(user.id)    // Int

// Cannot do this:
// user.id = 2  // Error: 'id' is a 'let' constant
```

---

## Hands-On Exercise

Create a type-safe product model:

**Requirements:**
- Product ID (integer, immutable)
- Name (string, immutable)
- Price (decimal, immutable)
- Quantity (integer, mutable)
- Discount percentage (decimal, mutable, 0-100)
- Calculate discounted price method

### Solution

```swift
struct Product {
    let id: Int
    let name: String
    let price: Double
    var quantity: Int
    var discountPercent: Double = 0.0  // 0-100

    // Computed property for discounted price
    var discountedPrice: Double {
        let discount = price * (discountPercent / 100)
        return price - discount
    }

    // Computed property for total
    var total: Double {
        return discountedPrice * Double(quantity)
    }
}

// Usage
var product = Product(
    id: 1,
    name: "Swift Book",
    price: 49.99,
    quantity: 2
)

product.discountPercent = 20  // 20% off
print("Price: $\(product.price)")
print("Discounted: $\(product.discountedPrice)")
print("Total: $\(product.total)")

// Output:
// Price: $49.99
// Discounted: $39.992
// Total: $79.984
```

---

## Common Type-Related Errors

### 1. Type Mismatch

```swift
// ❌ Error
let age: Int = "30"  // Cannot convert String to Int

// ✅ Fix
let age: Int = 30
```

### 2. Cannot Infer Type

```swift
// ❌ Error
var items = []  // Type of 'items' cannot be inferred

// ✅ Fix
var items: [String] = []
var items = [String]()
```

### 3. Reassigning Constant

```swift
// ❌ Error
let name = "John"
name = "Jane"  // Cannot assign to value: 'name' is a 'let' constant

// ✅ Fix
var name = "John"
name = "Jane"
```

---

## Summary

You've learned Swift's type system:

✅ **Static typing** catches errors at compile time (vs PHP's runtime)
✅ **Type inference** reduces verbosity while maintaining safety
✅ **let** for constants, **var** for variables
✅ **Value types** (structs) vs **reference types** (classes)
✅ **Explicit type conversion** (no automatic coercion)
✅ **Type safety** prevents entire classes of bugs

**Key Takeaway:** Swift's type system is stricter than PHP, but this strictness prevents bugs before your code ever runs. Embrace it!

---

## What's Next?

In [Chapter 04: Optionals](/series/swift-for-php-developers/chapters/04-optionals-null-safety), you'll learn Swift's powerful approach to handling null values—one of the biggest differences from PHP.

---

**Next Chapter:** [04 — Optionals: Swift's Approach to Null Safety](/series/swift-for-php-developers/chapters/04-optionals-null-safety)
