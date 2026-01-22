---
title: "03: Functions & Closures - From JS to PHP"
description: "Master PHP functions and closures by leveraging your JavaScript knowledge. Learn variable capture, higher-order functions, callbacks, and generators."
sidebar:
  label: "03: Functions & Closures - From JS to PHP"
  order: 3
  badge:
    text: Intermediate
    variant: caution
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

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-03)**

This chapter includes comprehensive function and closure examples:
- Closure variable capture patterns
- Higher-order functions
- Generators and lazy evaluation
- Currying and memoization techniques

Run the examples:
```bash
cd code/php-typescript-developers/chapter-03
php closures.php
php generators.php
```

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

## Currying and Partial Application

### TypeScript Currying

```typescript
// Curried function
const add = (a: number) => (b: number) => a + b;

const add5 = add(5);
console.log(add5(10)); // 15

// Generic curry helper
function curry<A, B, C>(fn: (a: A, b: B) => C) {
  return (a: A) => (b: B) => fn(a, b);
}

const multiply = (a: number, b: number) => a * b;
const curriedMultiply = curry(multiply);
const double = curriedMultiply(2);
console.log(double(10)); // 20
```

### PHP Currying

```php
<?php
declare(strict_types=1);

// Curried function
$add = fn(int $a): callable => fn(int $b): int => $a + $b;

$add5 = $add(5);
echo $add5(10); // 15

// Generic curry helper
function curry(callable $fn): callable {
    return function(...$args) use ($fn): callable {
        return function(...$moreArgs) use ($fn, $args): mixed {
            return $fn(...array_merge($args, $moreArgs));
        };
    };
}

$multiply = fn(int $a, int $b): int => $a * $b;
$curriedMultiply = curry($multiply);
$double = $curriedMultiply(2);
echo $double(10); // 20
```

### Partial Application

**TypeScript:**
```typescript
const greet = (greeting: string, name: string) => `${greeting}, ${name}!`;

const sayHello = (name: string) => greet("Hello", name);
const sayHi = (name: string) => greet("Hi", name);

console.log(sayHello("Alice")); // "Hello, Alice!"
console.log(sayHi("Bob"));      // "Hi, Bob!"
```

**PHP:**
```php
<?php
declare(strict_types=1);

$greet = fn(string $greeting, string $name): string => "{$greeting}, {$name}!";

// Partial application with arrow functions
$sayHello = fn(string $name): string => $greet("Hello", $name);
$sayHi = fn(string $name): string => $greet("Hi", $name);

echo $sayHello("Alice"); // "Hello, Alice!"
echo $sayHi("Bob");      // "Hi, Bob!"

// Or with traditional closures for clarity
$sayHello = function(string $name) use ($greet): string {
    return $greet("Hello", $name);
};
```

## Memoization Pattern

### TypeScript Memoization

```typescript
function memoize<T extends (...args: any[]) => any>(fn: T): T {
  const cache = new Map();

  return ((...args: any[]) => {
    const key = JSON.stringify(args);
    if (cache.has(key)) {
      return cache.get(key);
    }
    const result = fn(...args);
    cache.set(key, result);
    return result;
  }) as T;
}

const expensiveOperation = (n: number): number => {
  console.log(`Computing ${n}...`);
  return n * n;
};

const memoized = memoize(expensiveOperation);
console.log(memoized(5)); // "Computing 5..." -> 25
console.log(memoized(5)); // 25 (cached, no log)
```

### PHP Memoization

```php
<?php
declare(strict_types=1);

function memoize(callable $fn): callable {
    $cache = [];

    return function(...$args) use ($fn, &$cache): mixed {
        $key = json_encode($args);

        if (!isset($cache[$key])) {
            $cache[$key] = $fn(...$args);
        }

        return $cache[$key];
    };
}

$expensiveOperation = function(int $n): int {
    echo "Computing {$n}...\n";
    return $n * $n;
};

$memoized = memoize($expensiveOperation);
echo $memoized(5) . PHP_EOL; // "Computing 5..." -> 25
echo $memoized(5) . PHP_EOL; // 25 (cached, no log)
echo $memoized(10) . PHP_EOL; // "Computing 10..." -> 100
```

**Key Point:** PHP requires `&$cache` to modify the cache array across calls.

## Common Closure Pitfalls

### Pitfall 1: Forgetting to Capture Variables

```php
<?php
$multiplier = 10;

// ❌ BAD: Closure doesn't capture $multiplier
$double = function(int $x): int {
    return $x * $multiplier; // Error: Undefined variable
};

// ✅ GOOD: Explicit capture
$double = function(int $x) use ($multiplier): int {
    return $x * $multiplier;
};

// ✅ BETTER: Use arrow function for auto-capture
$double = fn(int $x): int => $x * $multiplier;
```

### Pitfall 2: Capturing by Value vs Reference

```php
<?php
$counter = 0;

// ❌ WRONG: Captures by value (doesn't mutate outer)
$increment = function() use ($counter): void {
    $counter++; // Only modifies local copy
};

$increment();
echo $counter; // Still 0

// ✅ CORRECT: Capture by reference
$counter = 0;
$increment = function() use (&$counter): void {
    $counter++; // Modifies outer variable
};

$increment();
echo $counter; // 1
```

### Pitfall 3: Late Binding in Loops

**TypeScript:**
```typescript
const callbacks: (() => void)[] = [];

for (let i = 0; i < 3; i++) {
  callbacks.push(() => console.log(i)); // ✅ Each closure captures its own i
}

callbacks.forEach(cb => cb()); // 0, 1, 2
```

**PHP (Problematic):**
```php
<?php
$callbacks = [];

// ❌ WRONG: All closures share same $i reference
for ($i = 0; $i < 3; $i++) {
    $callbacks[] = function() use ($i) {
        echo $i . PHP_EOL;
    };
}

foreach ($callbacks as $cb) {
    $cb(); // 2, 2, 2 (all capture final value)
}

// ✅ CORRECT: Capture by value explicitly
$callbacks = [];
for ($i = 0; $i < 3; $i++) {
    $callbacks[] = function() use ($i) { // Captures current value
        echo $i . PHP_EOL;
    };
}

foreach ($callbacks as $cb) {
    $cb(); // 0, 1, 2
}

// ✅ ALTERNATIVE: Use arrow function
$callbacks = [];
for ($i = 0; $i < 3; $i++) {
    $callbacks[] = fn() => print($i . PHP_EOL);
}
```

**Explanation:** In the first example, if you use `use (&$i)` (reference), all closures point to the same variable, which ends at 2. Using `use ($i)` (value) captures the value at creation time.

### Pitfall 4: Arrow Functions Can't Modify Captured Variables

```php
<?php
$count = 0;

// ❌ Arrow functions can't modify captured variables
$increment = fn() => $count++; // Modifies local copy, not outer

$increment();
echo $count; // Still 0

// ✅ Use traditional closure with reference
$increment = function() use (&$count): void {
    $count++;
};

$increment();
echo $count; // 1
```

### Pitfall 5: Closure Serialization

```php
<?php
// ❌ Closures cannot be serialized
$fn = fn($x) => $x * 2;
$serialized = serialize($fn); // Error: Serialization of 'Closure' is not allowed

// ✅ Use named functions or Opis/Closure library
function double(int $x): int {
    return $x * 2;
}

$serialized = serialize('double'); // OK
$fn = unserialize($serialized);
echo $fn(5); // 10
```

## Function Composition

### TypeScript Compose

```typescript
const compose = <T>(...fns: Array<(arg: T) => T>) => {
  return (value: T): T => {
    return fns.reduceRight((acc, fn) => fn(acc), value);
  };
};

const add2 = (x: number) => x + 2;
const multiply3 = (x: number) => x * 3;
const square = (x: number) => x ** 2;

const compute = compose(square, multiply3, add2);
console.log(compute(5)); // square(multiply3(add2(5))) = square(21) = 441
```

### PHP Compose

```php
<?php
declare(strict_types=1);

function compose(callable ...$functions): callable {
    return function(mixed $value) use ($functions): mixed {
        return array_reduce(
            array_reverse($functions),
            fn($carry, $fn) => $fn($carry),
            $value
        );
    };
}

$add2 = fn(int $x): int => $x + 2;
$multiply3 = fn(int $x): int => $x * 3;
$square = fn(int $x): int => $x ** 2;

$compute = compose($square, $multiply3, $add2);
echo $compute(5); // 441
```

## Closure Binding and `$this` Context

### TypeScript `this` Binding

```typescript
class Counter {
  private count = 0;

  // Arrow function preserves this
  increment = () => {
    this.count++;
  };

  // Regular method needs binding
  incrementMethod() {
    this.count++;
  }

  getCount() {
    return this.count;
  }
}

const counter = new Counter();
const inc = counter.increment;
inc(); // ✅ Works (arrow function bound to instance)

const incMethod = counter.incrementMethod;
incMethod(); // ❌ Error: this is undefined
```

### PHP Closure Binding

```php
<?php
declare(strict_types=1);

class Counter {
    private int $count = 0;

    public function increment(): void {
        $this->count++;
    }

    public function getCount(): int {
        return $this->count;
    }

    public function getIncrementer(): callable {
        // Closure has access to $this automatically
        return function(): void {
            $this->count++; // ✅ Works
        };
    }

    public function getArrowIncrementer(): callable {
        // Arrow function also has $this access
        return fn() => $this->count++;
    }
}

$counter = new Counter();
$inc = $counter->getIncrementer();
$inc();
echo $counter->getCount(); // 1

// Rebinding closure to different object
$counter2 = new Counter();
$boundInc = $inc->bindTo($counter2);
$boundInc();
echo $counter2->getCount(); // 1
```

**Key Difference:** PHP closures defined inside methods automatically have access to `$this`, unlike JavaScript where you need arrow functions or explicit binding.

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
3. **Variable capture by reference** requires `&` in `use (&$var)` for mutation
4. **Arrow functions can't modify** captured variables—use traditional closures with `&` for that
5. **Callable type** accepts functions, closures, method references, and string function names
6. **First-class callable syntax** (`fn(...)`) available in PHP 8.1+ for cleaner references
7. **Generators** work identically to TypeScript/JavaScript for lazy evaluation
8. **No tail-call optimization** in PHP—use iteration for deep recursion
9. **Currying and partial application** possible but require manual implementation
10. **Memoization requires** `&$cache` reference to persist cache across invocations
11. **Loop variable capture** behaves differently—use by-value `use ($i)` to capture current iteration
12. **Closures cannot be serialized**—use named functions or external libraries
13. **Function composition** can be implemented with `array_reduce` and `array_reverse`
14. **PHP closures in methods** automatically have access to `$this` (no need for binding like JS)
15. **`bindTo()` method** allows rebinding closures to different object instances

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
