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

1. **Arrow functions** in PHP use `fn` keyword and support only single expressions
2. **Null coalescing** (`??`, `??=`) works identically to TypeScript
3. **Nullsafe operator** (`?->`) is PHP's version of optional chaining
4. **Match expressions** are superior to switch statements
5. **Named arguments** (PHP 8.0+) provide similar flexibility to object parameters in TS
6. **Property promotion** makes class constructors as concise as TypeScript
7. **String interpolation** uses double quotes instead of backticks
8. **Spread operator** works for arrays, limited support for associative arrays

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
