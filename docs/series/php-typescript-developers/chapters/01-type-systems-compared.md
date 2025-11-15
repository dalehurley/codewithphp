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

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-01)**

This chapter includes working code examples:
- `basic-types.php` - Basic type annotations and demonstrations
- `nullable-union-types.php` - Nullable types, union types, and advanced patterns

Clone the repository and run the examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-typescript-developers/chapter-01
php basic-types.php
```

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

## Type Juggling and Coercion

One of the biggest differences between TypeScript and PHP is how they handle type coercion.

### TypeScript Type Coercion

```typescript
// JavaScript's loose typing (TypeScript allows this)
let value: any = "42";
let doubled = value * 2; // 84 (string coerced to number)

// TypeScript with proper types prevents this
let typedValue: string = "42";
let doubled2 = typedValue * 2; // ❌ Error: The left-hand side of an arithmetic operation must be of type 'any', 'number', 'bigint' or an enum type
```

### PHP Type Juggling (Without Strict Types)

```php
<?php
// Without strict_types, PHP aggressively coerces types
function add(int $a, int $b): int {
    return $a + $b;
}

echo add("10", "20");    // ✅ 30 (strings → ints)
echo add("10.5", "20");  // ✅ 30 (floats truncated to ints)
echo add("10", 2.5);     // ✅ 12 (float truncated to int)
echo add("hello", "5");  // ⚠️  5 (non-numeric string → 0)
```

### PHP with Strict Types

```php
<?php
declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}

echo add(10, 20);      // ✅ 30
echo add("10", "20");  // ❌ Fatal error: Argument must be of type int, string given
echo add(10, 2.5);     // ❌ Fatal error: Argument must be of type int, float given
```

**Key Insight:** Without `declare(strict_types=1)`, PHP's type system behaves more like JavaScript with implicit coercion. Always enable strict types for TypeScript-like behavior.

## Mixed and Never Types Explained

### The `mixed` Type

**TypeScript's `any`:**
```typescript
let value: any = "hello";
value = 42;           // ✅ OK
value = true;         // ✅ OK
value.anyMethod();    // ✅ TypeScript allows (runtime error possible)
```

**PHP's `mixed`:**
```php
<?php
declare(strict_types=1);

function process(mixed $value): mixed {
    // $value can be any type
    if (is_string($value)) {
        return strtoupper($value);
    }
    if (is_int($value)) {
        return $value * 2;
    }
    return $value;
}

echo process("hello"); // "HELLO"
echo process(42);      // 84
echo process(true);    // 1 (true converted to string)
```

**Differences from `any`:**
- PHP's `mixed` is type-safe at boundaries (function signatures)
- You must use type guards (`is_string()`, `is_int()`) to narrow types
- Unlike TypeScript's `any`, `mixed` doesn't disable type checking

### The `never` Type

**TypeScript:**
```typescript
// Function that never returns (throws or infinite loop)
function throwError(message: string): never {
  throw new Error(message);
}

function infiniteLoop(): never {
  while (true) {
    // Never exits
  }
}
```

**PHP (8.1+):**
```php
<?php
declare(strict_types=1);

// Function that always throws
function throwError(string $message): never {
    throw new Exception($message);
}

// Function that exits
function terminate(string $message): never {
    echo $message;
    exit(1);
}

// ❌ This would be a type error:
function invalid(): never {
    return 42; // Error: never type cannot return a value
}
```

**Use Cases:**
- Functions that always throw exceptions
- Functions that call `exit()` or `die()`
- Exhaustiveness checking in match expressions

## Literal Types

### TypeScript Literal Types

```typescript
type Status = "pending" | "approved" | "rejected";
type Port = 3000 | 8080 | 9000;

function setStatus(status: Status): void {
  console.log(`Status: ${status}`);
}

setStatus("approved");   // ✅ OK
setStatus("invalid");    // ❌ Error
```

### PHP Literal Types (Limited)

PHP doesn't have literal types for primitives, but you can achieve similar results with enums:

```php
<?php
declare(strict_types=1);

enum Status: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

function setStatus(Status $status): void {
    echo "Status: {$status->value}\n";
}

setStatus(Status::Approved);  // ✅ OK
setStatus('invalid');         // ❌ Fatal error: must be of type Status

// Enum with methods (PHP advantage!)
enum Status: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function isComplete(): bool {
        return $this === self::Approved || $this === self::Rejected;
    }

    public function color(): string {
        return match($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }
}

$status = Status::Approved;
echo $status->isComplete(); // true
echo $status->color();      // "green"
```

## Callable Types and Function Signatures

### TypeScript Function Types

```typescript
// Function type annotation
type MathOperation = (a: number, b: number) => number;

const add: MathOperation = (a, b) => a + b;
const multiply: MathOperation = (a, b) => a * b;

// Callback parameter
function calculate(a: number, b: number, operation: MathOperation): number {
  return operation(a, b);
}

calculate(5, 10, add);      // 15
calculate(5, 10, multiply); // 50
```

### PHP Callable Types

```php
<?php
declare(strict_types=1);

// PHP uses 'callable' type
function calculate(int $a, int $b, callable $operation): int {
    return $operation($a, $b);
}

$add = fn(int $a, int $b): int => $a + $b;
$multiply = fn(int $a, int $b): int => $a * $b;

echo calculate(5, 10, $add);      // 15
echo calculate(5, 10, $multiply); // 50

// More specific with PHPStan/Psalm annotations
/**
 * @param callable(int, int): int $operation
 */
function calculateTyped(int $a, int $b, callable $operation): int {
    return $operation($a, $b);
}
```

**PHP 8.2+ First-Class Callable Syntax:**
```php
<?php
class Calculator {
    public function add(int $a, int $b): int {
        return $a + $b;
    }
}

$calc = new Calculator();
$addFunction = $calc->add(...); // First-class callable
echo $addFunction(5, 10); // 15
```

## Type Guards and Narrowing

### TypeScript Type Guards

```typescript
function process(value: string | number): string {
  // Type guard with typeof
  if (typeof value === "number") {
    return value.toFixed(2); // TypeScript knows it's a number
  }
  return value.toUpperCase(); // TypeScript knows it's a string
}

// User-defined type guard
interface Dog {
  bark(): void;
}

interface Cat {
  meow(): void;
}

function isDog(animal: Dog | Cat): animal is Dog {
  return (animal as Dog).bark !== undefined;
}

function makeSound(animal: Dog | Cat): void {
  if (isDog(animal)) {
    animal.bark(); // TypeScript knows it's a Dog
  } else {
    animal.meow(); // TypeScript knows it's a Cat
  }
}
```

### PHP Type Guards

```php
<?php
declare(strict_types=1);

function process(string|int $value): string {
    // Type guard with is_*() functions
    if (is_int($value)) {
        return number_format($value, 2);
    }
    return strtoupper($value);
}

// Interface-based type checking
interface Dog {
    public function bark(): void;
}

interface Cat {
    public function meow(): void;
}

function makeSound(Dog|Cat $animal): void {
    // Use instanceof for object types
    if ($animal instanceof Dog) {
        $animal->bark();
    } else {
        $animal->meow();
    }
}
```

**PHP Type Check Functions:**
- `is_int()`, `is_float()`, `is_string()`, `is_bool()`, `is_array()`
- `is_null()`, `is_object()`, `is_resource()`, `is_callable()`
- `instanceof` for class/interface type checking

## Practical Comparison

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Type Checking** | Compile-time | Runtime |
| **Type Coercion** | Moderate | Aggressive (without strict types) |
| **Nullability** | `T \| null` | `?T` |
| **Union Types** | `string \| number` | `string\|int\|float` |
| **Intersection Types** | `A & B` | No native support |
| **Literal Types** | `"pending" \| "approved"` | Use enums instead |
| **Generics** | `Array<T>`, `Promise<T>` | Docblock only (`@param array<T>`) |
| **Interfaces** | Structural | Nominal (method contracts) |
| **Enums** | ✅ (ES3+) | ✅ (PHP 8.1+, more powerful) |
| **Readonly** | ✅ | ✅ (PHP 8.1+) |
| **Type Inference** | Strong | Limited |
| **Any Type** | `any` | `mixed` |
| **Never Type** | `never` | `never` (PHP 8.1+) |
| **Type Guards** | `typeof`, custom guards | `is_*()`, `instanceof` |
| **Callable Types** | `(a: T) => R` | `callable` + docblocks |
| **Strict Mode** | `tsconfig.json` | `declare(strict_types=1)` |

## Common Pitfalls and Gotchas

### Pitfall 1: Forgetting `declare(strict_types=1)`

```php
<?php
// ❌ BAD: No strict types
function greet(string $name): string {
    return "Hello, {$name}!";
}

greet(123); // Silently converts 123 to "123" 🐛
```

```php
<?php
// ✅ GOOD: Always use strict types
declare(strict_types=1);

function greet(string $name): string {
    return "Hello, {$name}!";
}

greet(123); // Fatal error: must be of type string
```

### Pitfall 2: Array Type Hints Are Not Generic

```typescript
// TypeScript: Type-safe arrays
function processStrings(items: string[]): void {
  items.forEach(item => console.log(item.toUpperCase()));
}

processStrings([1, 2, 3]); // ❌ Error: Type 'number' is not assignable to type 'string'
```

```php
<?php
declare(strict_types=1);

// PHP: array type hint accepts ANY array
function processStrings(array $items): void {
    foreach ($items as $item) {
        echo strtoupper($item); // Runtime error if $item is not a string!
    }
}

processStrings([1, 2, 3]); // ✅ Compiles, ❌ Runtime error
```

**Solution:** Use PHPStan annotations:
```php
<?php
/**
 * @param array<string> $items
 */
function processStrings(array $items): void {
    foreach ($items as $item) {
        echo strtoupper($item);
    }
}
// PHPStan will catch type errors during static analysis
```

### Pitfall 3: Float vs Int Distinction

```typescript
// TypeScript: One numeric type
function double(n: number): number {
  return n * 2;
}

double(5);    // ✅ 10
double(5.5);  // ✅ 11
```

```php
<?php
declare(strict_types=1);

function double(int $n): int {
    return $n * 2;
}

double(5);    // ✅ 10
double(5.5);  // ❌ Fatal error: must be of type int, float given
```

**Solution:** Use union types for numeric flexibility:
```php
<?php
function double(int|float $n): int|float {
    return $n * 2;
}

double(5);    // ✅ 10
double(5.5);  // ✅ 11.0
```

### Pitfall 4: Nullable Return Types Must Be Explicit

```typescript
// TypeScript: Return type inferred as User | undefined
function findUser(id: number) {
  return users.find(u => u.id === id);
}
```

```php
<?php
declare(strict_types=1);

// ❌ BAD: Missing nullable return type
function findUser(int $id): User {
    return $users[$id] ?? null; // Runtime error if null returned!
}

// ✅ GOOD: Explicit nullable return
function findUser(int $id): ?User {
    return $users[$id] ?? null;
}
```

### Pitfall 5: Property Type Must Match Constructor Assignment

```typescript
// TypeScript: No issue
class User {
  name: string;

  constructor(name: string | null) {
    this.name = name ?? "Anonymous";
  }
}
```

```php
<?php
// ❌ BAD: Type mismatch
class User {
    public string $name;

    public function __construct(?string $name) {
        $this->name = $name ?? "Anonymous"; // OK
        $this->name = null; // Fatal error: Cannot assign null to string
    }
}

// ✅ GOOD: Matching types
class User {
    public function __construct(
        public string $name = "Anonymous"
    ) {}
}
```

### Pitfall 6: Void Functions Can Return Null

```typescript
// TypeScript: void means no return value
function log(message: string): void {
  console.log(message);
  return null; // ❌ Error: Type 'null' is not assignable to type 'void'
}
```

```php
<?php
declare(strict_types=1);

// PHP: void allows explicit 'return;' or 'return null;'
function log(string $message): void {
    echo $message;
    return null; // ✅ OK (but unnecessary)
}

function logBetter(string $message): void {
    echo $message;
    return; // ✅ Better style
}

function logBest(string $message): void {
    echo $message;
    // ✅ No return statement needed
}
```

### Pitfall 7: Properties Without Defaults Must Be Initialized

```typescript
// TypeScript: Properties can be undefined
class User {
  name: string;
  email?: string; // Optional
}

let user = new User(); // OK, name is undefined
```

```php
<?php
// ❌ BAD: Uninitialized typed property
class User {
    public string $name;
    public string $email;
}

$user = new User(); // Fatal error: Typed property must not be accessed before initialization

// ✅ GOOD: Initialize in constructor or provide defaults
class User {
    public function __construct(
        public string $name = "",
        public ?string $email = null
    ) {}
}

$user = new User(); // ✅ OK
```

### Pitfall 8: Type Declarations Are Per-File

```php
<?php
// file1.php
declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}
```

```php
<?php
// file2.php
// ❌ No declare(strict_types=1)

require 'file1.php';

add("5", "10"); // ✅ Works! Coercion happens based on CALLER's strict_types
```

**Key Insight:** `declare(strict_types=1)` affects how the CURRENT file calls functions, not how functions in OTHER files behave. Always declare it in every file!

## Best Practices for TypeScript Developers

1. **Always use `declare(strict_types=1)`** - First line after `<?php` in every file
2. **Use PHPStan or Psalm** - Get compile-time-like type checking for arrays and generics
3. **Prefer explicit nullable types** - `?Type` over implicit null returns
4. **Use union types liberally** - `int|float` instead of just `float` when both should work
5. **Leverage enums for literal types** - More powerful than TypeScript enums
6. **Document array types** - Use `@param array<Type>` in docblocks
7. **Use readonly for immutability** - Equivalent to TypeScript's `readonly`
8. **Avoid `mixed` when possible** - Be specific with union types instead
9. **Use `never` for functions that throw** - Helps with exhaustiveness checks
10. **Initialize all typed properties** - No implicit `undefined` like TypeScript

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
2. **Always use `declare(strict_types=1)`** - Without it, PHP behaves like JavaScript with aggressive type coercion
3. **Type coercion without strict types is dangerous** - `add("10", "20")` works but leads to bugs
4. **Nullable types:** `?Type` in PHP = `Type | null` in TypeScript
5. **Union types:** `string|int` in PHP = `string | number` in TypeScript (but PHP distinguishes int/float)
6. **No native generics** in PHP; use PHPStan/Psalm `@param array<Type>` docblocks for type-safe arrays
7. **PHP interfaces are nominal** (name-based), TypeScript interfaces are structural (shape-based)
8. **PHP enums (8.1+)** can have methods, making them more powerful than TypeScript enums
9. **`mixed` vs `any`**: PHP's `mixed` is type-safe at boundaries, unlike TypeScript's `any`
10. **Type guards**: Use `is_int()`, `is_string()`, `instanceof` in PHP vs `typeof` in TypeScript
11. **Callable types**: PHP uses `callable` keyword; use docblocks for precise signatures
12. **All typed properties must be initialized** - No implicit `undefined` like TypeScript
13. **`strict_types` is per-file** - Must declare in every file, not just once per project
14. **Use PHPStan/Psalm** - Essential for catching type errors before runtime (like tsc for TypeScript)

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
