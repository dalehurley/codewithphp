---
title: "01: TypeScript to PHP - Type Systems Compared"
description: "Understand how PHP's type system compares to TypeScript's. Learn the similarities, differences, and how to leverage your TypeScript knowledge in PHP."
series: "php-typescript-developers"
chapter: 1
order: 1
difficulty: "Intermediate"
prerequisites:
  - "TypeScript experience"
  - "Understanding of static typing"
---

# TypeScript to PHP: Type Systems Compared

## Overview

Both TypeScript and modern PHP (8.0+) offer robust type systems, but they approach typing from different philosophies. TypeScript provides **compile-time static typing** that gets erased at runtime, while PHP offers **runtime-checked static typing** that validates types during execution.

In this chapter, we'll map TypeScript's type features to their PHP equivalents and explore the practical differences.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Compare TypeScript's compile-time types to PHP's runtime types
- ✅ Translate TypeScript type annotations to PHP
- ✅ Understand union types, intersection types, and nullable types in both languages
- ✅ Use strict typing modes in PHP
- ✅ Recognize when PHP's type system is more permissive or restrictive than TypeScript's

## Key Concepts

### The Fundamental Difference

**TypeScript:**
```typescript
// Type checking happens at compile-time
function greet(name: string): string {
  return `Hello, ${name}!`;
}

greet(123); // ❌ TypeScript error: Argument of type 'number' is not assignable to parameter of type 'string'
```

After compilation, this becomes:
```javascript
// All types are erased
function greet(name) {
  return `Hello, ${name}!`;
}

greet(123); // ✅ Runs in JavaScript (no runtime checks)
```

**PHP:**
```php
<?php
declare(strict_types=1); // Enable strict type checking

function greet(string $name): string {
    return "Hello, {$name}!";
}

greet(123); // ❌ PHP fatal error: Argument #1 must be of type string, int given
```

PHP's types are **checked at runtime**. No compilation step needed, but type violations cause runtime errors.

## Basic Type Annotations

### Primitive Types

| TypeScript | PHP | Notes |
|------------|-----|-------|
| `string` | `string` | Identical |
| `number` | `int` or `float` | PHP distinguishes integers and floats |
| `boolean` | `bool` | PHP uses `bool`, not `boolean` |
| `null` | `null` | Identical |
| `void` | `void` | Function returns nothing |
| `any` | `mixed` | Accepts any type (PHP 8.0+) |
| `never` | `never` | Function never returns (PHP 8.1+) |
| `unknown` | N/A | No direct equivalent |

### Examples Side-by-Side

**TypeScript:**
```typescript
function add(a: number, b: number): number {
  return a + b;
}

let result: number = add(5, 10);
let message: string = "The result is " + result;
let isValid: boolean = result > 0;
```

**PHP:**
```php
<?php
declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}

$result = add(5, 10); // Type inference
$message = "The result is " . $result;
$isValid = $result > 0;
```

**Key Differences:**
- PHP requires `$` prefix for variables
- PHP uses `.` for string concatenation instead of `+`
- PHP's type inference is limited (no explicit type annotation for variables in most cases)

## Nullable Types

### TypeScript Union with null

```typescript
function findUser(id: number): User | null {
  // Return User or null if not found
}

let user: User | null = findUser(123);
```

### PHP Nullable Type

```php
<?php
function findUser(int $id): ?User {
    // Return User or null if not found
}

$user = findUser(123); // $user is User or null
```

**PHP Shorthand:**
- `?string` = `string | null`
- `?int` = `int | null`
- `?User` = `User | null`

## Union Types

### TypeScript

```typescript
function format(value: string | number): string {
  if (typeof value === 'number') {
    return value.toFixed(2);
  }
  return value.toUpperCase();
}

format(42);      // "42.00"
format("hello"); // "HELLO"
```

### PHP (8.0+)

```php
<?php
declare(strict_types=1);

function format(string|int $value): string {
    if (is_int($value)) {
        return number_format($value, 2);
    }
    return strtoupper($value);
}

format(42);      // "42.00"
format("hello"); // "HELLO"
```

**Differences:**
- PHP uses `|` without spaces (style convention)
- PHP has `is_int()`, `is_string()`, etc. instead of `typeof`
- PHP doesn't have a unified `number` type (use `int|float`)

## Array Types

### TypeScript

```typescript
// Array of strings
let names: string[] = ["Alice", "Bob"];
let namesAlt: Array<string> = ["Alice", "Bob"]; // Generic syntax

// Tuple (fixed length, specific types)
let person: [string, number] = ["Alice", 30];

// Object type
let user: { name: string; age: number } = {
  name: "Alice",
  age: 30
};
```

### PHP

```php
<?php
declare(strict_types=1);

// Array (no generic typing in native PHP)
$names = ["Alice", "Bob"];

// Type hint: array of any type
function processNames(array $names): void {
    foreach ($names as $name) {
        echo $name;
    }
}

// No native tuple support, use array
$person = ["Alice", 30]; // Not type-safe

// Associative array (like object)
$user = [
    'name' => 'Alice',
    'age' => 30
];
```

**PHP Limitations:**
- No built-in generic types (e.g., `array<string>`)
- Use PHPStan or Psalm for generic annotations via docblocks:
  ```php
  /**
   * @param array<string> $names
   * @return array<int>
   */
  function processNames(array $names): array {
      // ...
  }
  ```

## Interfaces

### TypeScript

```typescript
interface User {
  id: number;
  name: string;
  email: string;
  isActive?: boolean; // Optional property
}

function getUser(id: number): User {
  return {
    id: 1,
    name: "Alice",
    email: "alice@example.com"
  };
}
```

### PHP

```php
<?php
declare(strict_types=1);

interface User {
    public function getId(): int;
    public function getName(): string;
    public function getEmail(): string;
    public function isActive(): bool;
}

class UserModel implements User {
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
        private bool $isActive = false
    ) {}

    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function isActive(): bool {
        return $this->isActive;
    }
}
```

**Key Differences:**
- TypeScript interfaces describe object shapes (structural typing)
- PHP interfaces define method contracts (nominal typing)
- PHP interfaces cannot have properties, only methods
- TypeScript interfaces can represent object literals; PHP requires classes

**PHP Alternative (Object Shape):**
Use classes with public properties:
```php
<?php
class User {
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isActive = false
    ) {}
}

$user = new User(1, "Alice", "alice@example.com");
echo $user->name; // "Alice"
```

## Enums

### TypeScript

```typescript
enum Status {
  Pending = "pending",
  Approved = "approved",
  Rejected = "rejected"
}

function updateStatus(status: Status): void {
  console.log(`Status: ${status}`);
}

updateStatus(Status.Approved); // "Status: approved"
```

### PHP (8.1+)

```php
<?php
declare(strict_types=1);

enum Status: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

function updateStatus(Status $status): void {
    echo "Status: {$status->value}";
}

updateStatus(Status::Approved); // "Status: approved"
```

**Similarities:**
- Both support string and numeric backing values
- Both are strongly typed
- Both prevent invalid values

**Differences:**
- PHP uses `case` keyword instead of just property names
- PHP accesses enum values with `->value` instead of direct access
- PHP enums can have methods (TypeScript enums cannot)

## Type Assertions and Casting

### TypeScript

```typescript
let value: unknown = "hello";
let length: number = (value as string).length; // Type assertion
```

### PHP

```php
<?php
$value = "hello"; // mixed type
$length = strlen((string) $value); // Type casting
```

**PHP Type Casting:**
- `(int)`, `(float)`, `(string)`, `(bool)`, `(array)`, `(object)`
- More permissive than TypeScript (may lose data)

## Strict Mode

### TypeScript

```json
// tsconfig.json
{
  "compilerOptions": {
    "strict": true,
    "strictNullChecks": true,
    "strictFunctionTypes": true
  }
}
```

### PHP

```php
<?php
declare(strict_types=1); // Must be first line of file

function add(int $a, int $b): int {
    return $a + $b;
}

// Without strict_types:
add("5", "10"); // ✅ Works, strings coerced to ints

// With strict_types:
add("5", "10"); // ❌ Fatal error: must be of type int, string given
```

**Best Practice:**
Always use `declare(strict_types=1)` at the top of every PHP file. It's the closest equivalent to TypeScript's strict mode.

## Readonly Properties

### TypeScript

```typescript
interface User {
  readonly id: number;
  name: string;
}

let user: User = { id: 1, name: "Alice" };
user.name = "Bob"; // ✅ OK
user.id = 2;       // ❌ Error: Cannot assign to 'id' because it is a read-only property
```

### PHP (8.1+)

```php
<?php
class User {
    public function __construct(
        public readonly int $id,
        public string $name
    ) {}
}

$user = new User(1, "Alice");
$user->name = "Bob"; // ✅ OK
$user->id = 2;       // ❌ Fatal error: Cannot modify readonly property
```

## Practical Comparison

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Type Checking** | Compile-time | Runtime |
| **Nullability** | `T \| null` | `?T` |
| **Union Types** | `string \| number` | `string\|int\|float` |
| **Generics** | `Array<T>` | Docblock only (`@param array<T>`) |
| **Interfaces** | Structural | Nominal (method contracts) |
| **Enums** | ✅ (ES3+) | ✅ (PHP 8.1+) |
| **Readonly** | ✅ | ✅ (PHP 8.1+) |
| **Type Inference** | Strong | Limited |
| **Any Type** | `any` | `mixed` |
| **Never Type** | `never` | `never` (PHP 8.1+) |
| **Strict Mode** | `tsconfig.json` | `declare(strict_types=1)` |

## Hands-On Exercise

### Task 1: Convert TypeScript to PHP

Given this TypeScript code:

```typescript
interface Product {
  id: number;
  name: string;
  price: number;
  inStock: boolean;
}

function calculateTotal(products: Product[]): number {
  return products.reduce((sum, p) => sum + p.price, 0);
}

let products: Product[] = [
  { id: 1, name: "Laptop", price: 999.99, inStock: true },
  { id: 2, name: "Mouse", price: 29.99, inStock: true }
];

console.log(calculateTotal(products)); // 1029.98
```

**Convert it to PHP.** Try it yourself before checking the solution!

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

class Product {
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public bool $inStock
    ) {}
}

/**
 * @param array<Product> $products
 */
function calculateTotal(array $products): float {
    return array_reduce(
        $products,
        fn($sum, $p) => $sum + $p->price,
        0.0
    );
}

$products = [
    new Product(1, "Laptop", 999.99, true),
    new Product(2, "Mouse", 29.99, true)
];

echo calculateTotal($products); // 1029.98
```

**Key Changes:**
- Interface → Class with public properties
- Arrow function `(sum, p) => sum + p.price` → `fn($sum, $p) => $sum + $p->price`
- `reduce()` → `array_reduce()`
- `console.log()` → `echo`
- Added PHPStan docblock for array type safety
</details>

### Task 2: Strict Types Challenge

What happens in PHP with and without `declare(strict_types=1)`?

```php
<?php
// Without strict_types (default)
function double(int $n): int {
    return $n * 2;
}

echo double("5"); // What gets printed?
```

<details>
<summary>Answer</summary>

**Without `strict_types`:**
```php
echo double("5"); // Prints: 10
// PHP coerces "5" (string) to 5 (int) automatically
```

**With `strict_types`:**
```php
<?php
declare(strict_types=1);

echo double("5"); // Fatal error: Argument #1 must be of type int, string given
```

**Lesson:** Always use `declare(strict_types=1)` to avoid unexpected type coercion.
</details>

## Key Takeaways

1. **PHP's type system is runtime-checked**, unlike TypeScript's compile-time checks
2. Use `declare(strict_types=1)` to enable strict type checking (recommended)
3. **Nullable types:** `?Type` in PHP = `Type | null` in TypeScript
4. **Union types:** `string|int` in PHP = `string | number` in TypeScript
5. **No native generics** in PHP; use PHPStan/Psalm docblocks for static analysis
6. PHP interfaces are nominal (name-based), TypeScript interfaces are structural (shape-based)
7. PHP enums (8.1+) are similar to TypeScript enums but more powerful
8. PHP has **limited type inference** compared to TypeScript

## Next Steps

Now that you understand the type systems, let's explore modern PHP syntax that will feel familiar to you as a TypeScript developer.

**Next Chapter:** [02: Modern PHP Syntax for TS Developers](/series/php-typescript-developers/chapters/02-modern-php-syntax)

## Resources

- [PHP Type System Documentation](https://www.php.net/manual/en/language.types.php)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/handbook/intro.html)
- [PHPStan - PHP Static Analysis Tool](https://phpstan.org/)
- [Psalm - Static Analysis for PHP](https://psalm.dev/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
