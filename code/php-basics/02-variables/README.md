# Chapter 02: Variables, Data Types, and Constants - Code Examples

This directory contains comprehensive code examples demonstrating PHP's data types, type system, and constants.

## Files Overview

### 1. `data-types-demo.php`

Complete demonstration of all major PHP data types.

**What it demonstrates:**

- String type and interpolation
- Integer type (including PHP 7.4+ numeric separators)
- Float/double type and scientific notation
- Boolean type
- Array type (indexed and associative)
- NULL type
- Type checking functions (`is_string()`, `is_int()`, etc.)

**Run it:**

```bash
php data-types-demo.php
```

### 2. `type-juggling.php`

Shows PHP's automatic type conversion (type coercion).

**What it demonstrates:**

- Automatic type conversion in arithmetic
- String concatenation with numbers
- Boolean context and falsy values
- Explicit type casting
- Loose (`==`) vs strict (`===`) comparison
- All PHP falsy values

**Run it:**

```bash
php type-juggling.php
```

**Important:** This file intentionally does NOT use `declare(strict_types=1)` to demonstrate automatic type conversion.

### 3. `strict-types-demo.php`

Demonstrates PHP's strict types mode (modern best practice).

**What it demonstrates:**

- `declare(strict_types=1)` directive
- Type declarations for function parameters
- Return type declarations
- Union types (PHP 8.0+)
- Nullable types with `?`
- Mixed type (PHP 8.0+)
- Why strict types prevent bugs

**Run it:**

```bash
php strict-types-demo.php
```

### 4. `constants-demo.php`

Complete guide to constants in PHP.

**What it demonstrates:**

- Defining constants with `define()`
- Defining constants with `const`
- Magic constants (`__FILE__`, `__DIR__`, `__LINE__`, etc.)
- Checking if constants exist with `defined()`
- Naming conventions for constants
- Practical configuration example

**Run it:**

```bash
php constants-demo.php
```

## Exercise Solutions

### Exercise 1: Simple Calculator

**File:** `solutions/exercise-1-calculator.php`

Create a calculator that performs basic arithmetic operations on two floating-point numbers.

**Requirements:**

- Use float variables for two numbers
- Calculate sum, difference, product, quotient, and remainder
- Display results clearly formatted

**Run it:**

```bash
php solutions/exercise-1-calculator.php
```

### Exercise 2: User Profile

**File:** `solutions/exercise-2-user-profile.php`

Create a user profile using appropriate data types for each piece of information.

**Requirements:**

- String for name and email
- Integer for age
- Float for account balance
- Boolean for premium status
- Display all information and their types

**Run it:**

```bash
php solutions/exercise-2-user-profile.php
```

## Key Concepts

### PHP Data Types

**Scalar Types:**

- `string` - Text data
- `int` - Whole numbers
- `float` - Decimal numbers
- `bool` - true or false

**Compound Types:**

- `array` - Collections of values
- `object` - Instances of classes

**Special Types:**

- `null` - Absence of value
- `resource` - External resources (files, database connections)

### Type Declarations (PHP 8.4)

```php
// Scalar types
function process(string $name, int $age, float $salary, bool $active): void

// Union types (multiple acceptable types)
function format(int|float $number): string

// Nullable types (can be NULL)
function find(int $id): ?User

// Mixed type (any type)
function handle(mixed $data): void
```

### Strict Types vs Type Juggling

**Without strict types:**

```php
function add(int $a, int $b) {
    return $a + $b;
}

add(5, "10"); // Works! "10" becomes 10
```

**With strict types:**

```php
declare(strict_types=1);

function add(int $a, int $b) {
    return $a + $b;
}

add(5, "10"); // TypeError! Must be exactly int
```

### Constants Best Practices

1. **Use `const` for class constants and top-level constants:**

   ```php
   const MAX_SIZE = 100;
   ```

2. **Use `define()` for runtime-defined constants:**

   ```php
   define('UPLOAD_PATH', __DIR__ . '/uploads');
   ```

3. **Naming convention:** `UPPERCASE_WITH_UNDERSCORES`

4. **Never try to change a constant** - it will cause a fatal error

## Common Mistakes

### 1. Loose vs Strict Comparison

```php
// Loose comparison (type juggling)
5 == "5"   // true (converts "5" to 5)
0 == false // true (0 is falsy)

// Strict comparison (no conversion)
5 === "5"   // false (different types)
0 === false // false (different types)

// Always use === for comparisons!
```

### 2. Falsy Values Surprise

```php
$value = "0"; // String containing zero

if ($value) {
    // This won't execute! "0" is falsy
}

// Better: be explicit
if ($value !== "") {
    // This will execute
}
```

### 3. Float Precision Issues

```php
$a = 0.1 + 0.2;
$b = 0.3;

var_dump($a == $b);  // false (float precision issue)

// For currency, use integers (cents) or bcmath
$cents = 10 + 20; // 30 cents = $0.30
```

## Quick Reference

### Type Checking Functions

- `is_string($var)` - Check if string
- `is_int($var)` - Check if integer
- `is_float($var)` - Check if float
- `is_bool($var)` - Check if boolean
- `is_array($var)` - Check if array
- `is_null($var)` - Check if NULL
- `gettype($var)` - Get the type name

### Type Casting

```php
$str = "123";
$int = (int)$str;     // 123
$float = (float)$str; // 123.0
$bool = (bool)$str;   // true
$arr = (array)$str;   // ["123"]
```

## Next Steps

Once you're comfortable with PHP's type system, move on to Chapter 03: Control Structures to learn how to make decisions and create loops!

## Related Chapter

[Chapter 02: Variables, Data Types, and Constants](../../chapters/02-variables-data-types-and-constants.md)
