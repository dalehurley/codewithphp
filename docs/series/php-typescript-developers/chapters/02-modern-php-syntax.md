---
title: "02: Modern PHP Syntax for TS Developers"
description: "Discover modern PHP syntax features that will feel familiar to TypeScript developers—arrow functions, spread operators, null coalescing, and more."
series: "php-typescript-developers"
chapter: 2
order: 2
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/01-type-systems-compared"
---

# Modern PHP Syntax for TS Developers

## Overview

Modern PHP (8.0+) has adopted many syntax features that TypeScript/JavaScript developers will recognize. This chapter explores the syntactic similarities and differences, helping you write idiomatic PHP using familiar patterns.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Use arrow functions in PHP (like TypeScript's arrow syntax)
- ✅ Apply null coalescing and nullish coalescing operators
- ✅ Use spread operators for arrays
- ✅ Write match expressions (better than switch)
- ✅ Leverage string interpolation
- ✅ Use named arguments (like object parameters in TS)
- ✅ Destructure arrays (PHP's equivalent to array destructuring)

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-02)**

This chapter includes working examples of modern PHP syntax:
- Arrow functions and closures
- Match expressions
- Spread operators and named arguments
- Array methods and TypeScript-style helpers

Run the examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-typescript-developers/chapter-02
php arrow-functions.php
```

## Arrow Functions

### TypeScript

```typescript
// Arrow function syntax
const double = (x: number): number => x * 2;

// Multi-line arrow function
const greet = (name: string): string => {
  const message = `Hello, ${name}!`;
  return message;
};

// Array methods with arrow functions
const numbers = [1, 2, 3, 4, 5];
const doubled = numbers.map(x => x * 2);
const evens = numbers.filter(x => x % 2 === 0);
```

### PHP (7.4+)

```php
<?php
declare(strict_types=1);

// Arrow function (short syntax - single expression only)
$double = fn(int $x): int => $x * 2;

// Multi-line requires traditional closure
$greet = function(string $name): string {
    $message = "Hello, {$name}!";
    return $message;
};

// Array methods with arrow functions
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($x) => $x * 2, $numbers);
$evens = array_filter($numbers, fn($x) => $x % 2 === 0);
```

**Key Differences:**

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Keyword** | None | `fn` |
| **Single Expression** | ✅ | ✅ |
| **Multi-line Body** | ✅ | ❌ (use `function`) |
| **Implicit Return** | ✅ | ✅ |
| **Auto-capture Variables** | ✅ | ✅ |

**Important:** PHP arrow functions (`fn`) can only have a **single expression**. For multi-line functions, use traditional closures.

## Null Coalescing Operators

### The `??` Operator (Null Coalescing)

**TypeScript:**
```typescript
// Nullish coalescing (??)
const name = user.name ?? "Guest";
const port = config.port ?? 3000;
```

**PHP:**
```php
<?php
// Null coalescing (??)
$name = $user['name'] ?? "Guest";
$port = $config['port'] ?? 3000;

// Chain multiple
$value = $a ?? $b ?? $c ?? "default";
```

**Identical behavior!** Returns the right operand if the left is `null` or undefined.

### The `??=` Operator (Null Coalescing Assignment)

**TypeScript:**
```typescript
// Assign only if null/undefined
port ??= 3000;
```

**PHP (7.4+):**
```php
<?php
$port ??= 3000; // Only assigns if $port is null or undefined
```

### The `?->` Operator (Optional Chaining)

**TypeScript:**
```typescript
// Optional chaining
const city = user?.address?.city;
const firstItem = array?.[0];
```

**PHP (8.0+):**
```php
<?php
// Nullsafe operator
$city = $user?->address?->city;
$firstItem = $array[0] ?? null; // No ?. for arrays
```

**PHP Limitation:** No optional chaining for arrays—use `??` instead.

## Spread Operator

### TypeScript

```typescript
// Array spread
const arr1 = [1, 2, 3];
const arr2 = [4, 5, 6];
const combined = [...arr1, ...arr2]; // [1, 2, 3, 4, 5, 6]

// Object spread
const user = { name: "Alice", age: 30 };
const updatedUser = { ...user, age: 31 };

// Function arguments
function sum(...numbers: number[]): number {
  return numbers.reduce((a, b) => a + b, 0);
}
```

### PHP (7.4+ for arrays, 8.1+ for named args)

```php
<?php
// Array spread (7.4+)
$arr1 = [1, 2, 3];
$arr2 = [4, 5, 6];
$combined = [...$arr1, ...$arr2]; // [1, 2, 3, 4, 5, 6]

// Associative array spread (8.1+)
$user = ['name' => 'Alice', 'age' => 30];
$updatedUser = [...$user, 'age' => 31];

// Function arguments (variadic)
function sum(int ...$numbers): int {
    return array_sum($numbers);
}

sum(1, 2, 3, 4); // 10
```

**PHP Limitation:** Spread in associative arrays requires PHP 8.1+.

## Match Expression (Better Switch)

### TypeScript Switch

```typescript
function getStatus(code: number): string {
  switch (code) {
    case 200:
      return "OK";
    case 404:
      return "Not Found";
    case 500:
      return "Server Error";
    default:
      return "Unknown";
  }
}
```

### PHP Match (8.0+)

```php
<?php
declare(strict_types=1);

function getStatus(int $code): string {
    return match($code) {
        200 => "OK",
        404 => "Not Found",
        500 => "Server Error",
        default => "Unknown"
    };
}
```

**Benefits of Match over Switch:**
- ✅ Returns a value (expression, not statement)
- ✅ Strict comparison (`===` by default)
- ✅ No fall-through (no `break` needed)
- ✅ Exhaustiveness checking

**Multiple conditions:**
```php
<?php
$result = match($status) {
    200, 201, 202 => "Success",
    400, 404 => "Client Error",
    500, 502, 503 => "Server Error",
    default => "Unknown"
};
```

## String Interpolation

### TypeScript

```typescript
const name = "Alice";
const age = 30;

// Template literals
const message = `Hello, ${name}! You are ${age} years old.`;
const multiline = `
  Line 1
  Line 2
  Line 3
`;
```

### PHP

```php
<?php
$name = "Alice";
$age = 30;

// Double quotes with variable interpolation
$message = "Hello, {$name}! You are {$age} years old.";

// Heredoc (multiline)
$multiline = <<<EOT
Line 1
Line 2
Line 3
EOT;

// Nowdoc (no interpolation, like single quotes)
$nowdoc = <<<'EOT'
Variables like {$name} are not parsed here.
EOT;
```

**Key Differences:**

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Syntax** | `` `${var}` `` | `"{$var}"` or `"$var"` |
| **Quotes** | Backticks | Double quotes |
| **Multiline** | Native in backticks | Heredoc (`<<<EOT`) |
| **Expression** | `` `${1 + 1}` `` | Not directly (use concatenation) |

**Simple variables** can omit braces in PHP:
```php
<?php
$name = "Alice";
echo "Hello, $name!"; // Works
echo "Hello, {$name}!"; // More explicit (recommended)
```

## Named Arguments

### TypeScript (Object Parameters)

```typescript
function createUser({
  name,
  email,
  age = 18,
  isActive = true
}: {
  name: string;
  email: string;
  age?: number;
  isActive?: boolean;
}): User {
  return { name, email, age, isActive };
}

// Call with named parameters
const user = createUser({
  name: "Alice",
  email: "alice@example.com",
  age: 30
});
```

### PHP (8.0+)

```php
<?php
declare(strict_types=1);

function createUser(
    string $name,
    string $email,
    int $age = 18,
    bool $isActive = true
): array {
    return compact('name', 'email', 'age', 'isActive');
}

// Call with named arguments
$user = createUser(
    name: "Alice",
    email: "alice@example.com",
    age: 30
);

// Skip optional parameters
$user2 = createUser(
    name: "Bob",
    email: "bob@example.com",
    isActive: false // Skip $age, use default
);
```

**PHP Named Arguments Benefits:**
- ✅ Skip optional parameters
- ✅ Reorder arguments
- ✅ Self-documenting code

## Array Destructuring

### TypeScript

```typescript
// Array destructuring
const [a, b, c] = [1, 2, 3];

// Object destructuring
const { name, age } = { name: "Alice", age: 30 };

// Nested destructuring
const { user: { name } } = data;
```

### PHP (7.1+)

```php
<?php
// Array destructuring
[$a, $b, $c] = [1, 2, 3];

// Associative array destructuring
['name' => $name, 'age' => $age] = ['name' => 'Alice', 'age' => 30];

// Nested destructuring
['user' => ['name' => $name]] = $data;

// Skip elements
[, , $third] = [1, 2, 3]; // $third = 3
```

**PHP Limitation:** No object destructuring—only arrays.

## Property Promotion (Constructor Shorthand)

### TypeScript

```typescript
class User {
  constructor(
    public name: string,
    public email: string,
    private age: number
  ) {}
}

const user = new User("Alice", "alice@example.com", 30);
```

### PHP (8.0+)

```php
<?php
declare(strict_types=1);

class User {
    public function __construct(
        public string $name,
        public string $email,
        private int $age
    ) {}
}

$user = new User("Alice", "alice@example.com", 30);
echo $user->name; // "Alice"
```

**Identical concept!** Properties are automatically declared and assigned.

## Trailing Comma Support

### TypeScript

```typescript
const arr = [
  1,
  2,
  3, // ✅ Trailing comma allowed
];

function foo(
  a: number,
  b: number, // ✅ Trailing comma allowed
) {}
```

### PHP (7.3+ for arrays, 8.0+ for parameters)

```php
<?php
$arr = [
    1,
    2,
    3, // ✅ Trailing comma allowed
];

function foo(
    int $a,
    int $b, // ✅ Trailing comma allowed (PHP 8.0+)
): void {}
```

## Nullsafe Operator Chain

### TypeScript

```typescript
// Optional chaining
const city = user?.profile?.address?.city;
```

### PHP (8.0+)

```php
<?php
// Nullsafe operator
$city = $user?->profile?->address?->city;
```

**Identical behavior!** Returns `null` if any property in the chain is `null`.

## First-Class Callable Syntax (PHP 8.1+)

### TypeScript

```typescript
class Calculator {
  add(a: number, b: number): number {
    return a + b;
  }
}

const calc = new Calculator();
const addFn = calc.add.bind(calc);
const result = addFn(5, 10); // 15

// Or with arrow function
const addFn2 = (a: number, b: number) => calc.add(a, b);
```

### PHP (8.1+)

```php
<?php
declare(strict_types=1);

class Calculator {
    public function add(int $a, int $b): int {
        return $a + $b;
    }
}

$calc = new Calculator();

// OLD way (verbose)
$addFn = fn($a, $b) => $calc->add($a, $b);

// NEW way (PHP 8.1+): First-class callable
$addFn = $calc->add(...);
$result = $addFn(5, 10); // 15

// Also works with built-in functions
$upperFn = strtoupper(...);
echo $upperFn('hello'); // "HELLO"

// Static methods
$jsonDecodeFn = json_decode(...);
```

**Benefits:**
- ✅ Cleaner syntax
- ✅ Works with methods, functions, and static methods
- ✅ Proper type checking

## Array Functions Comparison

TypeScript developers coming to PHP often miss familiar array methods. Here's how they translate:

### TypeScript Array Methods

```typescript
const numbers = [1, 2, 3, 4, 5];

// map
const doubled = numbers.map(x => x * 2);

// filter
const evens = numbers.filter(x => x % 2 === 0);

// reduce
const sum = numbers.reduce((acc, x) => acc + x, 0);

// find
const firstEven = numbers.find(x => x % 2 === 0);

// some
const hasEven = numbers.some(x => x % 2 === 0);

// every
const allPositive = numbers.every(x => x > 0);

// includes
const hasThree = numbers.includes(3);

// forEach
numbers.forEach(x => console.log(x));
```

### PHP Array Functions

```php
<?php
$numbers = [1, 2, 3, 4, 5];

// map
$doubled = array_map(fn($x) => $x * 2, $numbers);

// filter
$evens = array_filter($numbers, fn($x) => $x % 2 === 0);

// reduce
$sum = array_reduce($numbers, fn($acc, $x) => $acc + $x, 0);

// find (first match) - no direct equivalent, use loop or array_filter
$firstEven = current(array_filter($numbers, fn($x) => $x % 2 === 0)) ?: null;

// some - no direct equivalent, use loop
$hasEven = count(array_filter($numbers, fn($x) => $x % 2 === 0)) > 0;

// every - no direct equivalent
$allPositive = count(array_filter($numbers, fn($x) => $x > 0)) === count($numbers);

// includes
$hasThree = in_array(3, $numbers, true); // Third param = strict comparison

// forEach
array_walk($numbers, fn($x) => print($x . "\n"));
// Or use regular foreach loop (more idiomatic)
foreach ($numbers as $x) {
    echo $x . "\n";
}
```

**Important Differences:**
- PHP's `array_map` has parameters reversed: `array_map(callback, array)`
- Use `in_array()` for checking existence (with `strict: true` for type-safe)
- No direct `find()`, `some()`, `every()` equivalents—use `array_filter()` with additional logic
- PHP prefers `foreach` loops over `forEach()` method

### Modern PHP Array Helper (Custom)

You can create TypeScript-like helpers:

```php
<?php
declare(strict_types=1);

class Arr {
    /**
     * @template T
     * @param array<T> $array
     * @param callable(T): bool $callback
     * @return T|null
     */
    public static function find(array $array, callable $callback): mixed {
        foreach ($array as $item) {
            if ($callback($item)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @template T
     * @param array<T> $array
     * @param callable(T): bool $callback
     */
    public static function some(array $array, callable $callback): bool {
        foreach ($array as $item) {
            if ($callback($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @template T
     * @param array<T> $array
     * @param callable(T): bool $callback
     */
    public static function every(array $array, callable $callback): bool {
        foreach ($array as $item) {
            if (!$callback($item)) {
                return false;
            }
        }
        return true;
    }
}

// Usage
$numbers = [1, 2, 3, 4, 5];
$firstEven = Arr::find($numbers, fn($x) => $x % 2 === 0); // 2
$hasEven = Arr::some($numbers, fn($x) => $x % 2 === 0);   // true
$allPositive = Arr::every($numbers, fn($x) => $x > 0);    // true
```

## Attributes vs Decorators

### TypeScript Decorators

```typescript
// TypeScript decorators (experimental)
function log(target: any, propertyKey: string, descriptor: PropertyDescriptor) {
  const original = descriptor.value;
  descriptor.value = function(...args: any[]) {
    console.log(`Calling ${propertyKey} with`, args);
    return original.apply(this, args);
  };
}

class Calculator {
  @log
  add(a: number, b: number): number {
    return a + b;
  }
}
```

### PHP Attributes (8.0+)

```php
<?php
declare(strict_types=1);

// Define attribute
#[Attribute]
class Route {
    public function __construct(
        public string $path,
        public string $method = 'GET'
    ) {}
}

// Use attribute
class UserController {
    #[Route('/users', 'GET')]
    public function index(): array {
        return ['users' => []];
    }

    #[Route('/users', 'POST')]
    public function store(): array {
        return ['created' => true];
    }
}

// Read attributes via reflection
$reflection = new ReflectionClass(UserController::class);
foreach ($reflection->getMethods() as $method) {
    $attributes = $method->getAttributes(Route::class);
    foreach ($attributes as $attribute) {
        $route = $attribute->newInstance();
        echo "{$route->method} {$route->path} -> {$method->getName()}\n";
    }
}
```

**Differences:**
- TypeScript decorators modify behavior at runtime (when supported)
- PHP attributes are **metadata only**—they don't change behavior automatically
- PHP requires reflection to read attributes
- Common PHP use cases: routing, validation, ORM mapping

## Looping Constructs

### TypeScript Loops

```typescript
const items = ['a', 'b', 'c'];

// for...of (values)
for (const item of items) {
  console.log(item);
}

// for...in (keys)
for (const key in items) {
  console.log(key); // "0", "1", "2"
}

// forEach
items.forEach((item, index) => {
  console.log(index, item);
});

// Classic for
for (let i = 0; i < items.length; i++) {
  console.log(items[i]);
}
```

### PHP Loops

```php
<?php
$items = ['a', 'b', 'c'];

// foreach (most common)
foreach ($items as $item) {
    echo $item . "\n";
}

// foreach with index
foreach ($items as $index => $item) {
    echo "{$index}: {$item}\n";
}

// Classic for
for ($i = 0; $i < count($items); $i++) {
    echo $items[$i] . "\n";
}

// while
$i = 0;
while ($i < count($items)) {
    echo $items[$i] . "\n";
    $i++;
}
```

**Associative Arrays (like Objects):**

```typescript
// TypeScript
const user = { name: "Alice", age: 30 };
for (const [key, value] of Object.entries(user)) {
  console.log(`${key}: ${value}`);
}
```

```php
<?php
// PHP
$user = ['name' => 'Alice', 'age' => 30];
foreach ($user as $key => $value) {
    echo "{$key}: {$value}\n";
}
```

## Union Types in Practice

### TypeScript

```typescript
function format(value: string | number | boolean): string {
  if (typeof value === "string") {
    return value.toUpperCase();
  }
  if (typeof value === "number") {
    return value.toFixed(2);
  }
  return value ? "Yes" : "No";
}
```

### PHP (8.0+)

```php
<?php
declare(strict_types=1);

function format(string|int|float|bool $value): string {
    return match(true) {
        is_string($value) => strtoupper($value),
        is_int($value) || is_float($value) => number_format($value, 2),
        is_bool($value) => $value ? "Yes" : "No",
    };
}

// Or with if/elseif
function formatAlt(string|int|float|bool $value): string {
    if (is_string($value)) {
        return strtoupper($value);
    }
    if (is_int($value) || is_float($value)) {
        return number_format($value, 2);
    }
    return $value ? "Yes" : "No";
}
```

**Match with Truthiness:**
- `match(true)` allows conditional expressions as cases
- More elegant than long if/elseif chains
- Exhaustive by default (must handle all cases)

## Practical Comparison Example

Let's build a simple user validator in both languages:

### TypeScript

```typescript
interface User {
  name: string;
  email: string;
  age?: number;
  isActive?: boolean;
}

const validateUser = (user: User): string[] => {
  const errors: string[] = [];

  if (!user.name?.trim()) {
    errors.push("Name is required");
  }

  if (!user.email?.includes("@")) {
    errors.push("Invalid email");
  }

  if ((user.age ?? 0) < 18) {
    errors.push("Must be 18 or older");
  }

  return errors;
};

// Usage
const user = { name: "Alice", email: "alice@example.com", age: 30 };
const errors = validateUser(user);
```

### PHP

```php
<?php
declare(strict_types=1);

class User {
    public function __construct(
        public string $name,
        public string $email,
        public ?int $age = null,
        public bool $isActive = true
    ) {}
}

/**
 * @return array<string>
 */
function validateUser(User $user): array {
    $errors = [];

    if (empty(trim($user->name))) {
        $errors[] = "Name is required";
    }

    if (!str_contains($user->email, "@")) {
        $errors[] = "Invalid email";
    }

    if (($user->age ?? 0) < 18) {
        $errors[] = "Must be 18 or older";
    }

    return $errors;
}

// Usage
$user = new User("Alice", "alice@example.com", 30);
$errors = validateUser($user);
```

## Hands-On Exercise

### Task 1: Refactor to Modern PHP

Given this old-style PHP code, refactor it using modern syntax:

```php
<?php
// Old style
function calculateDiscount($price, $discountPercent = 10, $isMember = false) {
    if (!isset($price)) {
        return 0;
    }

    $discount = $isMember ? $discountPercent * 1.5 : $discountPercent;

    switch ($discount) {
        case 10:
            $label = "Standard";
            break;
        case 15:
            $label = "Member";
            break;
        default:
            $label = "Custom";
            break;
    }

    return array(
        'original' => $price,
        'discount' => $discount,
        'final' => $price - ($price * $discount / 100),
        'label' => $label
    );
}
```

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

function calculateDiscount(
    ?float $price,
    float $discountPercent = 10,
    bool $isMember = false
): array {
    $price ??= 0; // Null coalescing assignment

    // Ternary with clear intent
    $discount = $isMember ? $discountPercent * 1.5 : $discountPercent;

    // Match expression (better than switch)
    $label = match($discount) {
        10 => "Standard",
        15 => "Member",
        default => "Custom"
    };

    // Named array keys
    return [
        'original' => $price,
        'discount' => $discount,
        'final' => $price - ($price * $discount / 100),
        'label' => $label
    ];
}
```

**Modern features used:**
- Type declarations
- Null coalescing assignment (`??=`)
- Match expression
- Short array syntax `[]`
</details>

### Task 2: Convert TypeScript to PHP

Convert this TypeScript function to modern PHP:

```typescript
const processItems = (items: number[]): { sum: number; avg: number } => {
  const sum = items.reduce((acc, val) => acc + val, 0);
  const avg = sum / items.length;
  return { sum, avg };
};

const numbers = [1, 2, 3, 4, 5];
const result = processItems(numbers);
console.log(`Sum: ${result.sum}, Avg: ${result.avg}`);
```

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

/**
 * @param array<int> $items
 */
function processItems(array $items): array {
    $sum = array_reduce(
        $items,
        fn($acc, $val) => $acc + $val,
        0
    );
    $avg = $sum / count($items);

    return compact('sum', 'avg');
    // Or: return ['sum' => $sum, 'avg' => $avg];
}

$numbers = [1, 2, 3, 4, 5];
$result = processItems($numbers);
echo "Sum: {$result['sum']}, Avg: {$result['avg']}" . PHP_EOL;
```

**Key translations:**
- Arrow function → `fn($acc, $val) => $acc + $val`
- `reduce()` → `array_reduce()`
- `length` → `count()`
- Template literal → String interpolation with `{}`
</details>

## Key Takeaways

1. **Arrow functions** (`fn`) support only single expressions; use traditional closures for multi-line
2. **Null coalescing** (`??`, `??=`) works identically to TypeScript's nullish coalescing
3. **Nullsafe operator** (`?->`) is PHP's version of optional chaining (objects only, not arrays)
4. **Match expressions** are superior to switch: return values, strict comparison, no fall-through
5. **Named arguments** (PHP 8.0+) allow skipping optional parameters and reordering
6. **Property promotion** (PHP 8.0+) makes constructors as concise as TypeScript
7. **String interpolation** uses double quotes `"{$var}"` instead of backticks `` `${var}` ``
8. **Spread operator** works for arrays; associative array spread requires PHP 8.1+
9. **First-class callables** (PHP 8.1+): Use `fn(...)` syntax for cleaner function references
10. **Array functions** differ: `array_map(callback, array)` has reversed parameter order vs JS
11. **PHP lacks** `array.find()`, `array.some()`, `array.every()`—use `array_filter()` or custom helpers
12. **Attributes** (PHP 8.0+) are metadata-only, unlike TypeScript decorators that modify behavior
13. **`foreach` is idiomatic** in PHP; prefer it over `array_walk()` for most iterations
14. **Union types** work great with `match(true)` for elegant type-based logic

## Syntax Cheat Sheet

| Feature | TypeScript | PHP |
|---------|------------|-----|
| Arrow function | `(x) => x * 2` | `fn($x) => $x * 2` |
| Null coalescing | `a ?? b` | `$a ?? $b` |
| Optional chain | `obj?.prop` | `$obj?->prop` |
| Spread | `...arr` | `...$arr` |
| Match/Switch | `switch (x) {}` | `match($x) {}` |
| String template | `` `Hello ${x}` `` | `"Hello {$x}"` |
| Named args | `foo({ x: 1 })` | `foo(x: 1)` |
| Destructure | `[a, b] = arr` | `[$a, $b] = $arr` |

## Next Steps

Now that you're familiar with modern PHP syntax, let's dive deeper into functions and closures.

**Next Chapter:** [03: Functions & Closures: From JS to PHP](/series/php-typescript-developers/chapters/03-functions-and-closures)

## Resources

- [PHP 8.0 Features](https://www.php.net/releases/8.0/en.php)
- [PHP 8.1 Features](https://www.php.net/releases/8.1/en.php)
- [PHP Arrow Functions RFC](https://wiki.php.net/rfc/arrow_functions_v2)
- [PHP Match Expression RFC](https://wiki.php.net/rfc/match_expression_v2)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
