---
title: "02: Variables and Types"
description: "Learn Rust's type system, immutability, type inference, and how it differs from PHP's dynamic typing"
series: "rust-php-developers"
chapter: 2
order: 2
difficulty: "Beginner"
prerequisites:
  - "/series/rust-php-developers/chapters/00-quick-start-guide"
  - "/series/rust-php-developers/chapters/01-why-rust-for-php-developers"
---

![02: Variables and Types](/images/rust-php-developers/chapter-02-variables-types-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rust-php-developers">Rust for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 02</span>
</div>

# Chapter 02: Variables and Types

## Overview

Rust's type system is fundamentally different from PHP's. While PHP is dynamically typed (types checked at runtime), Rust is statically typed (types checked at compile time). This chapter explores Rust's type system from a PHP developer's perspective, showing you how to work with variables, understand immutability, and leverage type inference.

By the end of this chapter, you'll understand Rust's type system, know when to use different numeric types, work with strings confidently, and appreciate how compile-time type checking prevents bugs that would only show up in production PHP code.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 00: Quick Start Guide](/series/rust-php-developers/chapters/00-quick-start-guide)
- Completed [Chapter 01: Why Rust for PHP Developers](/series/rust-php-developers/chapters/01-why-rust-for-php-developers)
- Rust toolchain installed and working
- Basic understanding of PHP types (int, float, string, bool)

**Estimated Time**: ~60 minutes

## What You'll Learn

By the end of this chapter, you will:

- **Understand immutability by default** — Why Rust variables are immutable and when to use `mut`
- **Master type inference** — How Rust infers types without explicit annotations
- **Work with primitive types** — Integers, floats, booleans, and characters
- **Use compound types** — Tuples and arrays
- **Handle strings properly** — String vs &str and when to use each
- **Define constants** — const vs static
- **Cast between types** — Type conversions and coercion

## The Big Difference: Static vs Dynamic

### PHP: Dynamic Typing

**PHP (runtime type checking):**
```php
<?php
declare(strict_types=1);

$count = 10;           // int
$count = "hello";      // now a string - no error!
$count = 3.14;         // now a float - still no error!

function add(int $a, int $b): int {
    return $a + $b;
}

// Type errors only caught at runtime
add(5, "10");          // TypeError at runtime (strict_types=1)
```

### Rust: Static Typing

**Rust (compile-time type checking):**
```rust
let count = 10;        // i32 (inferred)
// count = "hello";    // ❌ Compile error: mismatched types

fn add(a: i32, b: i32) -> i32 {
    a + b
}

// Type errors caught at compile time
// add(5, "10");       // ❌ Won't compile!
```

**Key benefit:** Rust catches type errors before your code ever runs!

## Step 1: Immutability by Default (~10 min)

### Goal

Understand why Rust variables are immutable by default and how to make them mutable.

### Actions

1. **Understand immutability**:

**PHP (always mutable):**
```php
<?php
$count = 0;
$count = 1;            // ✅ Always works
$count += 1;           // ✅ Always works
```

**Rust (immutable by default):**
```rust
// Immutable binding
let count = 0;
// count = 1;          // ❌ Error: cannot assign twice to immutable variable

// Mutable binding
let mut count = 0;
count = 1;             // ✅ OK
count += 1;            // ✅ OK
```

2. **Try it yourself**:

```rust
// src/main.rs
fn main() {
    // Immutable
    let x = 5;
    println!("x = {}", x);
    // x = 6;           // Uncomment to see error

    // Mutable
    let mut y = 5;
    println!("y = {}", y);
    y = 6;
    println!("y = {}", y);
}
```

### Expected Result

```
x = 5
y = 5
y = 6
```

### Why It Works

**Immutability by default benefits:**

1. **Prevents bugs**: Can't accidentally change values
2. **Easier reasoning**: Values don't change unexpectedly
3. **Thread safety**: Immutable data is inherently thread-safe
4. **Compiler optimizations**: Enables better optimizations

**When to use `mut`:**
- Counters and accumulators
- Building data structures incrementally
- State that needs to change

**PHP comparison:**
```php
<?php
// PHP has no immutability built-in
$count = 0;            // Always mutable

// Workaround: use final for class properties
class User {
    public function __construct(
        public readonly string $name,  // PHP 8.1+ readonly
    ) {}
}
```

### Troubleshooting

- **Error: cannot assign twice** — Add `mut`: `let mut x = 5;`
- **Warning: unused mut** — Remove `mut` if you're not modifying the variable
- **Confused when to use mut** — Start with immutable, add `mut` when compiler complains

## Step 2: Type Inference (~5 min)

### Goal

Learn how Rust infers types without explicit annotations.

### Actions

1. **Type inference basics**:

**PHP:**
```php
<?php
$count = 10;           // Runtime determines it's an int
$price = 19.99;        // Runtime determines it's a float
```

**Rust:**
```rust
let count = 10;        // Compiler infers i32
let price = 19.99;     // Compiler infers f64

// Type annotations (optional but sometimes needed)
let count: i32 = 10;
let price: f64 = 19.99;
```

2. **When type annotation is required**:

```rust
// Example: parse needs to know target type
let guess = "42".parse().expect("Not a number!");  // ❌ Error: type annotations needed

let guess: i32 = "42".parse().expect("Not a number!");  // ✅ OK
// Or using turbofish syntax
let guess = "42".parse::<i32>().expect("Not a number!");  // ✅ OK
```

### Expected Result

Understanding that Rust infers types from:
- Literal values (42 → i32, 3.14 → f64)
- Function return types
- Variable usage

### Why It Works

Rust's type inference:
- Analyzes your code at compile time
- Infers types based on usage
- Requires explicit types when ambiguous
- Ensures type safety without verbosity

**PHP comparison:**
PHP's type hints are optional and checked at runtime:
```php
<?php
function add($a, $b) {         // No types
    return $a + $b;
}

function add(int $a, int $b): int {  // With types (PHP 7+)
    return $a + $b;
}
```

## Step 3: Integer Types (~10 min)

### Goal

Understand Rust's various integer types and when to use each.

### Actions

1. **Integer types overview**:

**PHP:**
```php
<?php
$count = 10;           // Just "int" (platform-dependent size)
$big = 9223372036854775807;  // Max int on 64-bit
```

**Rust:**
```rust
// Signed integers (can be negative)
let a: i8 = -128;      // -128 to 127
let b: i16 = -32768;   // -32,768 to 32,767
let c: i32 = -2147483648;  // -2,147,483,648 to 2,147,483,647 (default)
let d: i64 = -9223372036854775808;
let e: i128 = 0;       // Very large range

// Unsigned integers (only positive)
let a: u8 = 255;       // 0 to 255
let b: u16 = 65535;    // 0 to 65,535
let c: u32 = 4294967295;
let d: u64 = 18446744073709551615;
let e: u128 = 0;       // Very large range

// Architecture-dependent
let ptr_size: isize = 0;  // Pointer-sized signed integer
let ptr_size: usize = 0;  // Pointer-sized unsigned (common for indexing)
```

2. **Common usage**:

```rust
fn main() {
    // i32 is the default (good balance of range and performance)
    let count = 42;             // i32

    // u8 for bytes
    let byte: u8 = 255;

    // usize for array indices
    let index: usize = 0;
    let arr = [1, 2, 3, 4, 5];
    println!("{}", arr[index]);

    // Number literals can use underscores for readability
    let million = 1_000_000;
    let hex = 0xFF;
    let octal = 0o77;
    let binary = 0b1111_0000;
}
```

3. **Integer overflow**:

```rust
fn main() {
    let mut num: u8 = 255;
    // num = num + 1;          // ❌ Panic in debug mode!

    // Safe ways to handle overflow
    num = num.wrapping_add(1);     // Wraps to 0
    num = num.saturating_add(1);   // Stays at 255
    num = num.checked_add(1).unwrap_or(0);  // Returns Option
}
```

### Expected Result

Understanding:
- Default integer type is `i32`
- Use `u8` for bytes
- Use `usize` for array indices
- Rust prevents integer overflow in debug builds

### Why It Works

**Rust's precise integer types:**
- Explicit size prevents platform-specific bugs
- Overflow protection catches errors early
- Unsigned types prevent negative values where they don't make sense

**PHP comparison:**
```php
<?php
$num = 255;
$num = $num + 1;       // 256 (no overflow protection)

// PHP integers are platform-dependent
echo PHP_INT_MAX;      // 9223372036854775807 on 64-bit
```

## Step 4: Floating-Point Types (~5 min)

### Goal

Work with decimal numbers in Rust.

### Actions

1. **Float types**:

**PHP:**
```php
<?php
$price = 19.99;        // Just "float"
```

**Rust:**
```rust
let price: f32 = 19.99;   // 32-bit float (single precision)
let price: f64 = 19.99;   // 64-bit float (double precision) - default
let price = 19.99;        // f64 inferred
```

2. **Float operations**:

```rust
fn main() {
    let x = 2.5;
    let y = 10.0;

    let sum = x + y;
    let difference = y - x;
    let product = x * y;
    let quotient = y / x;
    let remainder = y % x;

    println!("sum: {}", sum);
    println!("difference: {}", difference);
    println!("product: {}", product);
    println!("quotient: {}", quotient);
    println!("remainder: {}", remainder);
}
```

### Expected Result

```
sum: 12.5
difference: 7.5
product: 25
quotient: 4
remainder: 0
```

### Why It Works

- `f64` is default (more precision, modern CPUs handle it efficiently)
- `f32` when you specifically need to save space
- All standard math operations work as expected

**PHP comparison:**
```php
<?php
$x = 2.5;
$y = 10.0;

echo $x + $y;          // 12.5
echo $y / $x;          // 4
```

## Step 5: Boolean Type (~3 min)

### Goal

Work with boolean values in Rust.

### Actions

**PHP:**
```php
<?php
$is_active = true;
$is_deleted = false;

if ($is_active) {
    echo "Active";
}
```

**Rust:**
```rust
fn main() {
    let is_active = true;
    let is_deleted: bool = false;  // Type annotation optional

    if is_active {
        println!("Active");
    }

    // Boolean operations
    let both = is_active && !is_deleted;
    let either = is_active || is_deleted;
    let not_active = !is_active;
}
```

### Expected Result

Booleans work the same as in PHP, but must be explicitly `true` or `false`.

**Key difference:**
```rust
let x = 5;

// ❌ This won't work in Rust (unlike PHP)
// if x {  // Error: expected bool, found integer
//     println!("x is truthy");
// }

// ✅ Must be explicit
if x != 0 {
    println!("x is not zero");
}
```

**PHP comparison:**
```php
<?php
$x = 5;

if ($x) {              // ✅ Works (truthy)
    echo "x is truthy";
}
```

## Step 6: Character Type (~5 min)

### Goal

Understand Rust's character type and Unicode support.

### Actions

**PHP:**
```php
<?php
$letter = 'A';         // Just a string
$emoji = '😀';         // Also a string
```

**Rust:**
```rust
fn main() {
    let letter = 'A';            // char (single quotes!)
    let emoji = '😀';             // char (Unicode scalar value)
    let chinese = '中';

    println!("letter: {}", letter);
    println!("emoji: {}", emoji);
    println!("chinese: {}", chinese);

    // char is 4 bytes (Unicode scalar value)
    println!("Size of char: {} bytes", std::mem::size_of::<char>());
}
```

### Expected Result

```
letter: A
emoji: 😀
chinese: 中
Size of char: 4 bytes
```

### Why It Works

**Important differences:**

| PHP | Rust |
|-----|------|
| No separate char type | `char` type (4 bytes) |
| Strings are byte arrays | Strings are UTF-8 |
| 'A' is a string | 'A' is a char (single quotes) |
| "A" is a string | "A" is a string slice (double quotes) |

```rust
let letter: char = 'A';      // ✅ char
let word: &str = "A";        // ✅ string slice
// let wrong: char = "A";    // ❌ Error: expected char, found &str
```

## Step 7: Compound Types - Tuples (~8 min)

### Goal

Learn to use tuples to group multiple values together.

### Actions

1. **Basic tuples**:

**PHP:**
```php
<?php
// PHP has no built-in tuples, use arrays
$user = ['Alice', 30, 'alice@example.com'];

// Access by index
echo $user[0];         // Alice

// Destructuring (PHP 7.1+)
[$name, $age, $email] = $user;
```

**Rust:**
```rust
fn main() {
    // Tuple with mixed types
    let user: (&str, u32, &str) = ("Alice", 30, "alice@example.com");

    // Access by index with dot notation
    println!("Name: {}", user.0);
    println!("Age: {}", user.1);
    println!("Email: {}", user.2);

    // Destructuring
    let (name, age, email) = user;
    println!("{} is {} years old", name, age);
}
```

2. **Returning multiple values**:

**PHP:**
```php
<?php
function get_user(): array {
    return ['Alice', 30];
}

[$name, $age] = get_user();
```

**Rust:**
```rust
fn get_user() -> (&'static str, u32) {
    ("Alice", 30)
}

fn main() {
    let (name, age) = get_user();
    println!("{} is {}", name, age);
}
```

3. **Unit type (empty tuple)**:

```rust
fn main() {
    // The unit type () represents "no value"
    let nothing: () = ();

    // Functions that don't return anything return ()
    fn do_something() {
        println!("Doing something");
        // Implicitly returns ()
    }

    let result = do_something();  // result is ()
}
```

### Expected Result

```
Name: Alice
Age: 30
Email: alice@example.com
Alice is 30 years old
Doing something
```

### Why It Works

Tuples are:
- Fixed-size collections
- Can hold different types
- Accessed by index with `.0`, `.1`, etc.
- Useful for returning multiple values

## Step 8: Compound Types - Arrays (~10 min)

### Goal

Work with fixed-size arrays in Rust.

### Actions

1. **Fixed-size arrays**:

**PHP:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$numbers[] = 6;        // Can grow dynamically
```

**Rust:**
```rust
fn main() {
    // Fixed-size array [type; length]
    let numbers: [i32; 5] = [1, 2, 3, 4, 5];

    // Access elements
    println!("First: {}", numbers[0]);
    println!("Length: {}", numbers.len());

    // Initialize with same value
    let zeros = [0; 5];  // [0, 0, 0, 0, 0]

    // ❌ Can't grow or shrink!
    // numbers.push(6);  // Error: no method push on arrays
}
```

2. **Iterating over arrays**:

```rust
fn main() {
    let numbers = [1, 2, 3, 4, 5];

    // Iterate with for loop
    for num in numbers.iter() {
        println!("{}", num);
    }

    // With index
    for (index, num) in numbers.iter().enumerate() {
        println!("numbers[{}] = {}", index, num);
    }
}
```

3. **Array bounds checking**:

```rust
fn main() {
    let numbers = [1, 2, 3, 4, 5];

    // ✅ Safe access
    if let Some(value) = numbers.get(10) {
        println!("Value: {}", value);
    } else {
        println!("Index out of bounds");
    }

    // ❌ Panics at runtime if out of bounds
    // let value = numbers[10];  // Panic!
}
```

### Expected Result

```
First: 1
Length: 5
1
2
3
4
5
numbers[0] = 1
numbers[1] = 2
...
Index out of bounds
```

### Why It Works

**Arrays in Rust:**
- Fixed size known at compile time
- All elements must be same type
- Allocated on the stack
- Very fast, no heap allocation

**For dynamic arrays, use Vec<T>** (covered in Chapter 07).

**PHP comparison:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$numbers[] = 6;        // Dynamic growth
echo count($numbers);  // 6
```

## Step 9: String vs &str (~10 min)

### Goal

Understand the crucial difference between String and &str in Rust.

### Actions

1. **String types**:

**PHP:**
```php
<?php
$name = "Alice";       // Always a string
$greeting = 'Hello, ' . $name;
```

**Rust:**
```rust
fn main() {
    // &str - string slice (borrowed, immutable)
    let name: &str = "Alice";          // String literal

    // String - owned, growable string
    let mut greeting = String::from("Hello, ");
    greeting.push_str(name);
    println!("{}", greeting);

    // Convert between types
    let str_slice: &str = "hello";
    let string: String = str_slice.to_string();
    let back_to_slice: &str = &string;
}
```

2. **When to use each**:

```rust
fn main() {
    // Use &str for:
    // - String literals
    // - Function parameters (more flexible)
    // - When you don't need to own the string

    fn greet(name: &str) {  // Takes both &str and &String
        println!("Hello, {}!", name);
    }

    greet("Alice");  // &str
    let owned = String::from("Bob");
    greet(&owned);   // &String coerces to &str

    // Use String for:
    // - Building strings dynamically
    // - Owning string data
    // - Returning strings from functions

    fn make_greeting(name: &str) -> String {
        format!("Hello, {}!", name)  // Returns owned String
    }
}
```

3. **String operations**:

```rust
fn main() {
    let mut s = String::from("Hello");

    // Append
    s.push_str(", world");
    s.push('!');

    // Concatenation
    let s1 = String::from("Hello, ");
    let s2 = String::from("world!");
    let s3 = s1 + &s2;  // s1 moved, s2 borrowed

    // Format (doesn't take ownership)
    let s1 = String::from("Hello");
    let s2 = String::from("world");
    let s3 = format!("{}, {}!", s1, s2);  // s1 and s2 still valid

    println!("{}", s);
    println!("{}", s3);
}
```

### Expected Result

```
Hello, world!
Hello, world!
```

### Why It Works

**Key differences:**

| &str | String |
|------|--------|
| Borrowed | Owned |
| Immutable | Mutable (if mut) |
| Fixed size | Growable |
| Stack or static | Heap allocated |
| Lightweight | Heavier |

**Rule of thumb:**
- Use `&str` for function parameters
- Use `String` when you need to own or modify strings
- String literals are `&str`

**PHP comparison:**
```php
<?php
// PHP has just one string type
$str = "hello";
$str .= " world";      // Always mutable, always owned
```

## Step 10: Constants and Statics (~5 min)

### Goal

Define compile-time constants and static variables.

### Actions

1. **Constants**:

**PHP:**
```php
<?php
define('MAX_POINTS', 100000);
const TAX_RATE = 0.08;  // Class constant

echo MAX_POINTS;
```

**Rust:**
```rust
// Constants - must be type annotated
const MAX_POINTS: u32 = 100_000;
const TAX_RATE: f64 = 0.08;

fn main() {
    println!("Max points: {}", MAX_POINTS);
    println!("Tax rate: {}", TAX_RATE);
}
```

2. **Constants vs statics**:

```rust
// const - value inlined at compile time
const MAX_SIZE: usize = 1024;

// static - has a fixed memory address
static LANGUAGE: &str = "Rust";

// static mut (rarely used, unsafe to access)
static mut COUNTER: u32 = 0;

fn main() {
    println!("Language: {}", LANGUAGE);

    // Accessing mutable static requires unsafe
    unsafe {
        COUNTER += 1;
        println!("Counter: {}", COUNTER);
    }
}
```

### Expected Result

```
Max points: 100000
Tax rate: 0.08
Language: Rust
Counter: 1
```

### Why It Works

**const:**
- Always immutable
- Must be known at compile time
- Value inlined wherever used
- Must have type annotation

**static:**
- Has a fixed memory location
- Lives for entire program
- Can be mutable (requires unsafe)

**PHP comparison:**
```php
<?php
define('MAX_SIZE', 1024);     // Runtime constant
const TAX_RATE = 0.08;        // Compile-time constant (PHP 5.3+)
```

## Step 11: Type Casting and Conversions (~5 min)

### Goal

Convert between different types safely.

### Actions

1. **Casting primitives**:

**PHP:**
```php
<?php
$num = (int)"42";
$str = (string)42;
$float = (float)"3.14";
```

**Rust:**
```rust
fn main() {
    // Numeric casting with 'as'
    let x = 5u8;
    let y = x as i32;
    let z = y as f64;

    println!("x: {}, y: {}, z: {}", x, y, z);

    // Lossy conversions
    let a = 300i32;
    let b = a as u8;    // Truncates! b = 44
    println!("a: {}, b: {}", a, b);

    // String to number (fallible)
    let num_str = "42";
    let num: i32 = num_str.parse().expect("Not a number");
    println!("Parsed: {}", num);

    // Number to string
    let num = 42;
    let str = num.to_string();
    let str2 = format!("{}", num);
}
```

### Expected Result

```
x: 5, y: 5, z: 5
a: 300, b: 44
Parsed: 42
```

### Why It Works

**Rust casting:**
- Use `as` for numeric types
- Use `.to_string()` or `format!()` for string conversion
- Use `.parse()` for string → number (returns Result)

**Beware:**
- `as` can truncate or lose precision
- Always handle parse errors properly

**PHP comparison:**
```php
<?php
$num = (int)"42";      // Always succeeds, returns 0 on failure
$str = (string)42;     // Always succeeds
```

## Exercises

### Exercise 1: Temperature Converter

Create a program that converts Fahrenheit to Celsius.

```rust
// src/main.rs
fn fahrenheit_to_celsius(f: f64) -> f64 {
    // Your code here
}

fn main() {
    let temp_f = 98.6;
    let temp_c = fahrenheit_to_celsius(temp_f);
    println!("{}°F is {:.1}°C", temp_f, temp_c);
}
```

<details>
<summary>Solution</summary>

```rust
fn fahrenheit_to_celsius(f: f64) -> f64 {
    (f - 32.0) * 5.0 / 9.0
}

fn main() {
    let temp_f = 98.6;
    let temp_c = fahrenheit_to_celsius(temp_f);
    println!("{}°F is {:.1}°C", temp_f, temp_c);
    // Output: 98.6°F is 37.0°C
}
```
</details>

### Exercise 2: Tuple Calculator

Write functions that take two numbers as a tuple and return various calculations.

```rust
fn calculate(numbers: (i32, i32)) -> (i32, i32, i32, f64) {
    // Return (sum, difference, product, quotient)
    // Your code here
}

fn main() {
    let nums = (10, 3);
    let (sum, diff, prod, quot) = calculate(nums);
    println!("Sum: {}, Diff: {}, Prod: {}, Quot: {:.2}", sum, diff, prod, quot);
}
```

<details>
<summary>Solution</summary>

```rust
fn calculate(numbers: (i32, i32)) -> (i32, i32, i32, f64) {
    let (a, b) = numbers;
    (a + b, a - b, a * b, a as f64 / b as f64)
}

fn main() {
    let nums = (10, 3);
    let (sum, diff, prod, quot) = calculate(nums);
    println!("Sum: {}, Diff: {}, Prod: {}, Quot: {:.2}", sum, diff, prod, quot);
    // Output: Sum: 13, Diff: 7, Prod: 30, Quot: 3.33
}
```
</details>

### Exercise 3: String Builder

Build a greeting string dynamically.

```rust
fn build_greeting(name: &str, age: u32) -> String {
    // Build and return a greeting string
    // Your code here
}

fn main() {
    let greeting = build_greeting("Alice", 30);
    println!("{}", greeting);
}
```

<details>
<summary>Solution</summary>

```rust
fn build_greeting(name: &str, age: u32) -> String {
    format!("Hello, {}! You are {} years old.", name, age)
}

fn main() {
    let greeting = build_greeting("Alice", 30);
    println!("{}", greeting);
    // Output: Hello, Alice! You are 30 years old.
}
```
</details>

## Wrap-up

Congratulations! You've completed Chapter 02. Here's what you've learned:

- ✓ **Immutability by default** — Variables are immutable unless marked `mut`
- ✓ **Type inference** — Rust infers types but ensures safety
- ✓ **Integer types** — Sized integers (i8-i128, u8-u128) for precise control
- ✓ **Floats and bools** — f32/f64 and explicit boolean values
- ✓ **Characters** — Unicode scalar values with `char`
- ✓ **Tuples** — Group multiple values of different types
- ✓ **Arrays** — Fixed-size, stack-allocated collections
- ✓ **String vs &str** — Owned vs borrowed strings
- ✓ **Constants** — const and static for compile-time values
- ✓ **Type casting** — Converting between types with `as` and `.parse()`

### Key Differences from PHP

| Concept | PHP | Rust |
|---------|-----|------|
| **Typing** | Dynamic | Static |
| **Mutability** | Always mutable | Immutable by default |
| **Integers** | One int type | Many sized types |
| **Strings** | One type | String and &str |
| **Arrays** | Dynamic | Fixed-size |
| **Type checking** | Runtime | Compile-time |

### What's Next

In the next chapter, **Ownership and Borrowing**, you'll learn Rust's most unique feature—the ownership system. This is what makes Rust memory-safe without garbage collection.

You'll learn:
- The three ownership rules
- Move semantics vs copy semantics
- Borrowing with & and &mut
- The borrow checker
- How this prevents entire categories of bugs

## Further Reading

- [The Rust Book: Variables and Mutability](https://doc.rust-lang.org/book/ch03-01-variables-and-mutability.html)
- [The Rust Book: Data Types](https://doc.rust-lang.org/book/ch03-02-data-types.html)
- [Rust by Example: Types](https://doc.rust-lang.org/rust-by-example/types.html)
- [Appendix A: Rust vs PHP Quick Reference](/series/rust-php-developers/appendices/appendix-a-rust-php-reference)

## Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 02 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/rust-php-developers/chapter-02)**

<ChapterCheckbox
  seriesId="rust-php-developers"
  chapterId="02"
  label="You've mastered Rust's type system and variables!"
/>

---

Ready to learn ownership? Continue to [Chapter 03: Ownership and Borrowing](/series/rust-php-developers/chapters/03-ownership-and-borrowing).
