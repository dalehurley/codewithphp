---
title: "03: Functions & Closures - From JS to PHP"
description: "Master PHP functions and closures by leveraging your JavaScript knowledge. Learn variable capture, higher-order functions, callbacks, and generators."
series: "php-typescript-developers"
chapter: 3
order: 3
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/02-modern-php-syntax"
---

# Functions & Closures: From JS to PHP

## Overview

Functions in PHP and TypeScript/JavaScript share many concepts, but PHP's approach to closures and variable scope has important differences. This chapter bridges your JavaScript knowledge to PHP's function system.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Write functions with proper type hints in PHP
- ✅ Understand PHP's closure scope and variable capture
- ✅ Create higher-order functions
- ✅ Use first-class functions and callbacks
- ✅ Work with callable types
- ✅ Implement generator functions (yield)
- ✅ Apply functional programming patterns

## Function Declarations

### TypeScript

```typescript
// Function declaration
function add(a: number, b: number): number {
  return a + b;
}

// Arrow function
const multiply = (a: number, b: number): number => a * b;

// Optional parameters
function greet(name: string, greeting?: string): string {
  return `${greeting ?? "Hello"}, ${name}!`;
}

// Default parameters
function createUser(name: string, age: number = 18): User {
  return { name, age };
}

// Rest parameters
function sum(...numbers: number[]): number {
  return numbers.reduce((a, b) => a + b, 0);
}
```

### PHP

```php
<?php
declare(strict_types=1);

// Function declaration
function add(int $a, int $b): int {
    return $a + $b;
}

// Arrow function (single expression only)
$multiply = fn(int $a, int $b): int => $a * $b;

// Optional parameters (use nullable type + null default)
function greet(string $name, ?string $greeting = null): string {
    return ($greeting ?? "Hello") . ", {$name}!";
}

// Default parameters
function createUser(string $name, int $age = 18): array {
    return compact('name', 'age');
}

// Variadic parameters (rest parameters)
function sum(int ...$numbers): int {
    return array_sum($numbers);
}
```

**Key Differences:**
- PHP uses `...` before the parameter name (`...$numbers`)
- PHP arrow functions use `fn` keyword
- Optional parameters typically use nullable types with `null` defaults

## Closures and Variable Scope

### The Critical Difference: Lexical Scope

**TypeScript/JavaScript:**
```typescript
// Variables are automatically captured
const multiplier = 2;

const double = (x: number): number => {
  return x * multiplier; // ✅ Automatically accesses outer scope
};

console.log(double(5)); // 10
```

**PHP:**
```php
<?php
$multiplier = 2;

// ❌ Does NOT automatically capture outer variables
$double = function(int $x): int {
    return $x * $multiplier; // ❌ Error: Undefined variable $multiplier
};

// ✅ Must explicitly use 'use' clause
$double = function(int $x) use ($multiplier): int {
    return $x * $multiplier; // ✅ Works
};

echo $double(5); // 10
```

**PHP Arrow Functions (PHP 7.4+):**
```php
<?php
$multiplier = 2;

// ✅ Arrow functions automatically capture variables
$double = fn(int $x): int => $x * $multiplier;

echo $double(5); // 10
```

### Closure Variable Capture

**TypeScript:**
```typescript
let counter = 0;

const increment = (): void => {
  counter++; // ✅ Mutates outer variable
};

increment();
console.log(counter); // 1
```

**PHP (Reference Capture):**
```php
<?php
$counter = 0;

// By value (default) - does NOT mutate outer variable
$increment = function() use ($counter): void {
    $counter++; // ❌ Only modifies local copy
};

$increment();
echo $counter; // 0 (unchanged)

// By reference - DOES mutate outer variable
$increment = function() use (&$counter): void {
    $counter++; // ✅ Modifies outer variable
};

$increment();
echo $counter; // 1 (changed)
```

**PHP Arrow Functions:**
```php
<?php
$counter = 0;

// Arrow functions capture by value (cannot modify outer variables)
$increment = fn() => $counter++; // ❌ Does not modify outer $counter

// For mutation, use traditional closure with reference
$increment = function() use (&$counter): void {
    $counter++;
};
```

## Higher-Order Functions

### Functions Returning Functions

**TypeScript:**
```typescript
const makeMultiplier = (factor: number) => {
  return (value: number): number => value * factor;
};

const double = makeMultiplier(2);
const triple = makeMultiplier(3);

console.log(double(5)); // 10
console.log(triple(5)); // 15
```

**PHP:**
```php
<?php
declare(strict_types=1);

function makeMultiplier(int $factor): callable {
    return fn(int $value): int => $value * $factor;
}

$double = makeMultiplier(2);
$triple = makeMultiplier(3);

echo $double(5) . PHP_EOL; // 10
echo $triple(5) . PHP_EOL; // 15
```

### Functions as Arguments (Callbacks)

**TypeScript:**
```typescript
const numbers = [1, 2, 3, 4, 5];

// map, filter, reduce
const doubled = numbers.map(x => x * 2);
const evens = numbers.filter(x => x % 2 === 0);
const sum = numbers.reduce((acc, val) => acc + val, 0);
```

**PHP:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];

// array_map, array_filter, array_reduce
$doubled = array_map(fn($x) => $x * 2, $numbers);
$evens = array_filter($numbers, fn($x) => $x % 2 === 0);
$sum = array_reduce($numbers, fn($acc, $val) => $acc + $val, 0);
```

**Custom Higher-Order Function:**

**TypeScript:**
```typescript
function applyOperation<T>(
  items: T[],
  operation: (item: T) => T
): T[] {
  return items.map(operation);
}

const numbers = [1, 2, 3];
const doubled = applyOperation(numbers, x => x * 2);
```

**PHP:**
```php
<?php
declare(strict_types=1);

/**
 * @template T
 * @param array<T> $items
 * @param callable(T): T $operation
 * @return array<T>
 */
function applyOperation(array $items, callable $operation): array {
    return array_map($operation, $items);
}

$numbers = [1, 2, 3];
$doubled = applyOperation($numbers, fn($x) => $x * 2);
```

## Callable Type Hints

### TypeScript Function Types

```typescript
type MathOperation = (a: number, b: number) => number;

const add: MathOperation = (a, b) => a + b;
const multiply: MathOperation = (a, b) => a * b;

function calculate(
  a: number,
  b: number,
  operation: MathOperation
): number {
  return operation(a, b);
}
```

### PHP Callable Types

```php
<?php
declare(strict_types=1);

// Simple callable type hint
function calculate(int $a, int $b, callable $operation): int {
    return $operation($a, $b);
}

// Using PHPStan/Psalm docblock for precise typing
/**
 * @param callable(int, int): int $operation
 */
function calculateWithDoc(int $a, int $b, callable $operation): int {
    return $operation($a, $b);
}

// Usage
$add = fn($a, $b) => $a + $b;
$multiply = fn($a, $b) => $a * $b;

echo calculate(5, 3, $add); // 8
echo calculate(5, 3, $multiply); // 15
```

**PHP Callable Formats:**
```php
<?php
// 1. Anonymous function
$callback = function() { return "hello"; };

// 2. Arrow function
$callback = fn() => "hello";

// 3. Function name as string
$callback = 'strlen';

// 4. Static method (array format)
$callback = [MyClass::class, 'staticMethod'];

// 5. Object method (array format)
$callback = [$object, 'method'];

// 6. First-class callable (PHP 8.1+)
$callback = strlen(...);
```

## First-Class Callable Syntax (PHP 8.1+)

### TypeScript

```typescript
// Functions are first-class citizens
const fn = Math.max;
console.log(fn(1, 2, 3)); // 3
```

### PHP (8.1+)

```php
<?php
// Old way (string or array)
$fn = 'max';
echo $fn(1, 2, 3); // 3

// First-class callable syntax (8.1+)
$fn = max(...);
echo $fn(1, 2, 3); // 3

// Object methods
$fn = $object->method(...);
$result = $fn($arg);

// Static methods
$fn = MyClass::staticMethod(...);
$result = $fn($arg);
```

## Generators (yield)

### TypeScript Generators

```typescript
function* generateNumbers(max: number): Generator<number> {
  for (let i = 0; i < max; i++) {
    yield i;
  }
}

for (const num of generateNumbers(5)) {
  console.log(num); // 0, 1, 2, 3, 4
}
```

### PHP Generators

```php
<?php
function generateNumbers(int $max): Generator {
    for ($i = 0; $i < $max; $i++) {
        yield $i;
    }
}

foreach (generateNumbers(5) as $num) {
    echo $num . PHP_EOL; // 0, 1, 2, 3, 4
}
```

**Generator with Keys:**
```php
<?php
function generateKeyValue(): Generator {
    yield 'a' => 1;
    yield 'b' => 2;
    yield 'c' => 3;
}

foreach (generateKeyValue() as $key => $value) {
    echo "{$key}: {$value}" . PHP_EOL;
}
```

**Practical Example - Lazy Loading:**

**TypeScript:**
```typescript
function* readLargeFile(filename: string): Generator<string> {
  const lines = fs.readFileSync(filename, 'utf-8').split('\n');
  for (const line of lines) {
    yield line;
  }
}
```

**PHP:**
```php
<?php
function readLargeFile(string $filename): Generator {
    $handle = fopen($filename, 'r');
    while (($line = fgets($handle)) !== false) {
        yield trim($line);
    }
    fclose($handle);
}

// Memory-efficient: processes one line at a time
foreach (readLargeFile('large.txt') as $line) {
    // Process line
}
```

## Recursion

### TypeScript

```typescript
const factorial = (n: number): number => {
  if (n <= 1) return 1;
  return n * factorial(n - 1);
};

console.log(factorial(5)); // 120
```

### PHP

```php
<?php
declare(strict_types=1);

function factorial(int $n): int {
    if ($n <= 1) return 1;
    return $n * factorial($n - 1);
}

echo factorial(5); // 120
```

**Tail-Call Optimization (Not in PHP):**

PHP does **not** optimize tail calls, so deep recursion can cause stack overflow. Use iteration or trampolining for deep recursion.

## Practical Example: Event Emitter

Let's build a simple event emitter in both languages:

### TypeScript

```typescript
class EventEmitter {
  private listeners: Map<string, Array<(...args: any[]) => void>> = new Map();

  on(event: string, callback: (...args: any[]) => void): void {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event)!.push(callback);
  }

  emit(event: string, ...args: any[]): void {
    const callbacks = this.listeners.get(event);
    if (callbacks) {
      callbacks.forEach(callback => callback(...args));
    }
  }
}

// Usage
const emitter = new EventEmitter();
emitter.on('message', (msg: string) => console.log(`Received: ${msg}`));
emitter.emit('message', 'Hello!'); // "Received: Hello!"
```

### PHP

```php
<?php
declare(strict_types=1);

class EventEmitter {
    /** @var array<string, array<callable>> */
    private array $listeners = [];

    public function on(string $event, callable $callback): void {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $callback;
    }

    public function emit(string $event, mixed ...$args): void {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $callback) {
                $callback(...$args);
            }
        }
    }
}

// Usage
$emitter = new EventEmitter();
$emitter->on('message', fn($msg) => echo "Received: {$msg}" . PHP_EOL);
$emitter->emit('message', 'Hello!'); // "Received: Hello!"
```

## Hands-On Exercise

### Task 1: Implement Array Methods

Implement `myMap`, `myFilter`, and `myReduce` functions in PHP:

**Goal:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$doubled = myMap($numbers, fn($x) => $x * 2);
$evens = myFilter($numbers, fn($x) => $x % 2 === 0);
$sum = myReduce($numbers, fn($acc, $val) => $acc + $val, 0);
```

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

/**
 * @template T
 * @template U
 * @param array<T> $array
 * @param callable(T): U $callback
 * @return array<U>
 */
function myMap(array $array, callable $callback): array {
    $result = [];
    foreach ($array as $item) {
        $result[] = $callback($item);
    }
    return $result;
}

/**
 * @template T
 * @param array<T> $array
 * @param callable(T): bool $callback
 * @return array<T>
 */
function myFilter(array $array, callable $callback): array {
    $result = [];
    foreach ($array as $item) {
        if ($callback($item)) {
            $result[] = $item;
        }
    }
    return $result;
}

/**
 * @template T
 * @template U
 * @param array<T> $array
 * @param callable(U, T): U $callback
 * @param U $initial
 * @return U
 */
function myReduce(array $array, callable $callback, mixed $initial): mixed {
    $accumulator = $initial;
    foreach ($array as $item) {
        $accumulator = $callback($accumulator, $item);
    }
    return $accumulator;
}

// Test
$numbers = [1, 2, 3, 4, 5];
$doubled = myMap($numbers, fn($x) => $x * 2);
$evens = myFilter($numbers, fn($x) => $x % 2 === 0);
$sum = myReduce($numbers, fn($acc, $val) => $acc + $val, 0);

print_r($doubled); // [2, 4, 6, 8, 10]
print_r($evens);   // [2, 4]
echo $sum;         // 15
```
</details>

### Task 2: Pipeline Function

Create a `pipe` function that chains multiple operations:

**Goal:**
```php
<?php
$result = pipe(
    5,
    fn($x) => $x * 2,  // 10
    fn($x) => $x + 3,  // 13
    fn($x) => $x ** 2  // 169
);
echo $result; // 169
```

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

function pipe(mixed $value, callable ...$functions): mixed {
    foreach ($functions as $fn) {
        $value = $fn($value);
    }
    return $value;
}

// Test
$result = pipe(
    5,
    fn($x) => $x * 2,  // 10
    fn($x) => $x + 3,  // 13
    fn($x) => $x ** 2  // 169
);

echo $result; // 169
```

**Alternative with array_reduce:**
```php
<?php
function pipe(mixed $value, callable ...$functions): mixed {
    return array_reduce(
        $functions,
        fn($carry, $fn) => $fn($carry),
        $value
    );
}
```
</details>

### Task 3: Debounce Function

Implement a simple debounce function:

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

function debounce(callable $callback, int $delayMs): callable {
    $lastCall = 0;

    return function(...$args) use ($callback, $delayMs, &$lastCall) {
        $now = (int)(microtime(true) * 1000);

        if ($now - $lastCall >= $delayMs) {
            $lastCall = $now;
            return $callback(...$args);
        }

        return null;
    };
}

// Usage
$logMessage = debounce(
    fn($msg) => echo "Log: {$msg}" . PHP_EOL,
    1000 // 1 second debounce
);

$logMessage("Hello"); // Prints
$logMessage("World"); // Ignored (within 1s)
sleep(2);
$logMessage("Again"); // Prints (after 1s)
```
</details>

## Key Takeaways

1. **Closures require explicit variable capture** with `use` clause (except arrow functions)
2. **Arrow functions** (`fn`) automatically capture variables but only support single expressions
3. **Variable capture by reference** requires `&` in `use (&$var)`
4. **Callable type** accepts functions, closures, and method references
5. **First-class callable syntax** (`fn(...)`) available in PHP 8.1+
6. **Generators** work identically to TypeScript/JavaScript
7. **No tail-call optimization** in PHP—use iteration for deep recursion

## Comparison Table

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Arrow Function** | `(x) => x * 2` | `fn($x) => $x * 2` |
| **Multi-line Arrow** | ✅ | ❌ (use `function`) |
| **Auto Capture Vars** | ✅ | ✅ (arrow), ❌ (function) |
| **Explicit Capture** | N/A | `use ($var)` |
| **Reference Capture** | Default | `use (&$var)` |
| **Generators** | `function*` | `function` + `yield` |
| **Callable Type** | `(a: T) => U` | `callable` |
| **First-Class Callable** | Native | `fn(...)` (PHP 8.1+) |
| **Tail-Call Optimization** | Engine-dependent | ❌ |

## Next Steps

Now that you understand functions and closures, let's explore object-oriented programming in PHP.

**Next Chapter:** [04: OOP: Classes, Interfaces & Generics](/series/php-typescript-developers/chapters/04-oop-classes-interfaces)

## Resources

- [PHP Closures Documentation](https://www.php.net/manual/en/class.closure.php)
- [PHP Arrow Functions RFC](https://wiki.php.net/rfc/arrow_functions_v2)
- [PHP Generators Documentation](https://www.php.net/manual/en/language.generators.php)
- [PHP First-Class Callable Syntax](https://wiki.php.net/rfc/first_class_callable_syntax)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
