---
title: "Chapter 13: Closures and Functional Programming"
description: Master Swift closures, capture semantics, and functional programming patterns like map, filter, and reduce.
series: swift-for-php-developers
chapter: 13
difficulty: Intermediate to Advanced
tags: ["closures", "functional-programming", "map", "filter", "reduce", "higher-order-functions", "escaping"]
---

# Chapter 13: Closures and Functional Programming

Closures are self-contained blocks of functionality—similar to PHP's anonymous functions and arrow functions, but more powerful. Combined with Swift's functional programming features, closures enable elegant, expressive code.

This chapter teaches you to think functionally and write cleaner code with closures.

## What You'll Learn

- What closures are
- Closure syntax and shorthand
- Capturing values
- Escaping vs non-escaping closures
- Trailing closure syntax
- Functional programming patterns (map, filter, reduce)
- Higher-order functions
- Autoclosures
- Comparing to PHP's anonymous functions

## Prerequisites

- Completed [Chapter 12: ARC and Memory Management](/series/swift-for-php-developers/chapters/12-arc-memory-management)
- Understanding of functions
- Knowledge of arrays and collections

---

## PHP Anonymous Functions: Quick Review

```php
<?php
// Anonymous function (closure)
$greet = function(string $name): string {
    return "Hello, $name!";
};

echo $greet("John");  // "Hello, John!"

// Arrow function (PHP 7.4+)
$double = fn($x) => $x * 2;

echo $double(5);  // 10

// Capturing variables with 'use'
$multiplier = 3;
$multiply = function(int $x) use ($multiplier): int {
    return $x * $multiplier;
};

echo $multiply(4);  // 12

// Array functions
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($x) => $x * 2, $numbers);
// [2, 4, 6, 8, 10]
```

---

## Swift Closures: Overview

Closures are self-contained blocks of functionality.

```swift
// Closure assigned to variable
let greet = { (name: String) -> String in
    return "Hello, \(name)!"
}

print(greet("John"))  // "Hello, John!"

// Shorthand (type inferred)
let double = { (x: Int) -> Int in
    return x * 2
}

print(double(5))  // 10

// Capturing variables (automatic)
let multiplier = 3
let multiply = { (x: Int) -> Int in
    return x * multiplier  // Captures multiplier
}

print(multiply(4))  // 12

// Array functions
let numbers = [1, 2, 3, 4, 5]
let doubled = numbers.map { $0 * 2 }
// [2, 4, 6, 8, 10]
```

**Key Differences from PHP:**
- No `use` keyword needed (automatic capture)
- More concise shorthand syntax
- Strong typing with type inference

---

## Closure Syntax

### Full Syntax

```swift
let closure: (Int, Int) -> Int = { (a: Int, b: Int) -> Int in
    return a + b
}
```

**Parts:**
1. Parameters: `(a: Int, b: Int)`
2. Return type: `-> Int`
3. `in` keyword separates declaration from body
4. Body: `return a + b`

### Type Inference

```swift
// Swift infers types from context
let add: (Int, Int) -> Int = { (a, b) in
    return a + b
}

// Or infer from usage
let numbers = [1, 2, 3]
let doubled = numbers.map { (number) in
    return number * 2
}
```

### Implicit Returns

Single-expression closures can omit `return`:

```swift
let add: (Int, Int) -> Int = { (a, b) in
    a + b  // Implicit return
}

let doubled = numbers.map { (number) in
    number * 2  // Implicit return
}
```

### Shorthand Argument Names

Use `$0`, `$1`, `$2`... for arguments:

```swift
let add: (Int, Int) -> Int = { $0 + $1 }

let doubled = numbers.map { $0 * 2 }

let sum = numbers.reduce(0) { $0 + $1 }
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3];

// PHP arrow function
$doubled = array_map(fn($x) => $x * 2, $numbers);

// Swift is more concise
$doubled = numbers.map { $0 * 2 }
```

---

## Closures as Function Parameters

Functions can accept closures as parameters (higher-order functions).

```swift
func performOperation(_ a: Int, _ b: Int, operation: (Int, Int) -> Int) -> Int {
    return operation(a, b)
}

// Pass closure
let result = performOperation(5, 3, operation: { $0 + $1 })
print(result)  // 8

// Different operation
let result2 = performOperation(5, 3, operation: { $0 * $1 })
print(result2)  // 15
```

**PHP Comparison:**
```php
<?php
function performOperation(int $a, int $b, callable $operation): int {
    return $operation($a, $b);
}

$result = performOperation(5, 3, fn($x, $y) => $x + $y);
echo $result;  // 8
```

---

## Trailing Closure Syntax

If closure is the last parameter, you can write it outside parentheses.

```swift
// Standard syntax
let sorted = numbers.sorted(by: { $0 < $1 })

// Trailing closure (preferred)
let sorted = numbers.sorted { $0 < $1 }

// Multiple lines
let filtered = numbers.filter { number in
    number % 2 == 0
}

// No other parameters? Omit parentheses entirely
UIView.animate(withDuration: 1.0) {
    // Animation code
}
```

**PHP has no equivalent** (closures must be inside parentheses).

---

## Capturing Values

Closures automatically capture variables from their surrounding context.

```swift
func makeIncrementer(increment: Int) -> () -> Int {
    var total = 0

    let incrementer = {
        total += increment  // Captures total and increment
        return total
    }

    return incrementer
}

let incrementByTwo = makeIncrementer(increment: 2)
print(incrementByTwo())  // 2
print(incrementByTwo())  // 4
print(incrementByTwo())  // 6

let incrementByFive = makeIncrementer(increment: 5)
print(incrementByFive())  // 5
print(incrementByFive())  // 10
```

**Each closure has its own captured copy!**

**PHP Comparison:**
```php
<?php
function makeIncrementer(int $increment): Closure {
    $total = 0;

    return function() use ($increment, &$total): int {
        $total += $increment;  // Must use &$total for by-reference
        return $total;
    };
}

$incrementByTwo = makeIncrementer(2);
echo $incrementByTwo();  // 2
echo $incrementByTwo();  // 4
```

**Difference:** PHP requires `use` and `&` for mutable captures. Swift does it automatically.

---

## Escaping vs Non-Escaping Closures

### Non-Escaping (Default)

Closure is called before function returns:

```swift
func performOperation(numbers: [Int], operation: (Int) -> Int) -> [Int] {
    return numbers.map(operation)
}

let doubled = performOperation(numbers: [1, 2, 3]) { $0 * 2 }
```

### Escaping (@escaping)

Closure outlives the function (stored or called later):

```swift
var completionHandlers: [() -> Void] = []

func doSomethingAsync(completion: @escaping () -> Void) {
    // Store closure for later
    completionHandlers.append(completion)
}

doSomethingAsync {
    print("Completed!")
}

// Call later
completionHandlers.first?()  // "Completed!"
```

**@escaping is required when:**
- Storing closure in a property
- Passing closure to async function
- Closure called after function returns

```swift
class ViewController {
    var onComplete: (() -> Void)?  // Stored property

    func loadData(completion: @escaping () -> Void) {
        self.onComplete = completion  // Requires @escaping

        DispatchQueue.main.asyncAfter(deadline: .now() + 1) {
            completion()  // Called later (requires @escaping)
        }
    }
}
```

---

## Map, Filter, Reduce: Functional Patterns

### Map: Transform Each Element

```swift
let numbers = [1, 2, 3, 4, 5]

// Double each number
let doubled = numbers.map { $0 * 2 }
// [2, 4, 6, 8, 10]

// Convert to strings
let strings = numbers.map { String($0) }
// ["1", "2", "3", "4", "5"]

// Complex transformation
struct Person {
    let name: String
    let age: Int
}

let people = [
    Person(name: "John", age: 30),
    Person(name: "Jane", age: 25)
]

let names = people.map { $0.name }
// ["John", "Jane"]
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($x) => $x * 2, $numbers);
```

### Filter: Keep Elements Matching Condition

```swift
let numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

// Even numbers only
let evens = numbers.filter { $0 % 2 == 0 }
// [2, 4, 6, 8, 10]

// Numbers > 5
let large = numbers.filter { $0 > 5 }
// [6, 7, 8, 9, 10]

// Adults only
let adults = people.filter { $0.age >= 18 }
```

**PHP Comparison:**
```php
<?php
$evens = array_filter($numbers, fn($x) => $x % 2 == 0);
```

### Reduce: Combine Into Single Value

```swift
let numbers = [1, 2, 3, 4, 5]

// Sum
let sum = numbers.reduce(0) { $0 + $1 }
// 15

// Or more concisely
let sum = numbers.reduce(0, +)
// 15

// Product
let product = numbers.reduce(1, *)
// 120

// Concatenate strings
let words = ["Hello", "World", "Swift"]
let sentence = words.reduce("") { $0 + " " + $1 }
// " Hello World Swift"

// Custom reduction
let ages = people.reduce(0) { $0 + $1.age }
// Sum of ages
```

**PHP Comparison:**
```php
<?php
$sum = array_reduce($numbers, fn($carry, $item) => $carry + $item, 0);
```

---

## Chaining Functional Methods

Combine map, filter, and reduce:

```swift
let numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]

// Get sum of squares of even numbers
let result = numbers
    .filter { $0 % 2 == 0 }      // [2, 4, 6, 8, 10]
    .map { $0 * $0 }             // [4, 16, 36, 64, 100]
    .reduce(0, +)                // 220

print(result)  // 220

// Get names of adults
let adultNames = people
    .filter { $0.age >= 18 }
    .map { $0.name }
```

**PHP Comparison:**
```php
<?php
$result = array_reduce(
    array_map(
        fn($x) => $x * $x,
        array_filter($numbers, fn($x) => $x % 2 == 0)
    ),
    fn($carry, $item) => $carry + $item,
    0
);

// PHP nested calls are less readable than Swift's chaining
```

---

## Other Useful Higher-Order Functions

### compactMap: Map and Remove Nils

```swift
let strings = ["1", "2", "foo", "3", "bar", "4"]

let numbers = strings.compactMap { Int($0) }
// [1, 2, 3, 4] (non-nil values only)
```

### flatMap: Flatten Nested Arrays

```swift
let arrays = [[1, 2], [3, 4], [5, 6]]

let flattened = arrays.flatMap { $0 }
// [1, 2, 3, 4, 5, 6]
```

### forEach: Perform Action on Each

```swift
let numbers = [1, 2, 3, 4, 5]

numbers.forEach { print($0) }
// Prints each number

// vs for-in loop
for number in numbers {
    print(number)
}
```

### contains: Check if Any Match

```swift
let numbers = [1, 2, 3, 4, 5]

let hasEven = numbers.contains { $0 % 2 == 0 }
// true

let hasNegative = numbers.contains { $0 < 0 }
// false
```

### allSatisfy: Check if All Match

```swift
let allPositive = numbers.allSatisfy { $0 > 0 }
// true

let allEven = numbers.allSatisfy { $0 % 2 == 0 }
// false
```

### sorted: Sort with Custom Logic

```swift
let names = ["John", "Alice", "Bob", "Charlie"]

let sorted = names.sorted { $0 < $1 }
// ["Alice", "Bob", "Charlie", "John"]

let sortedByLength = names.sorted { $0.count < $1.count }
// ["Bob", "John", "Alice", "Charlie"]
```

---

## Autoclosures

`@autoclosure` automatically wraps expression in closure.

```swift
func assert(_ condition: @autoclosure () -> Bool, message: String) {
    if !condition() {
        print("Assertion failed: \(message)")
    }
}

// Call without explicit closure
assert(1 + 1 == 2, message: "Math is broken")

// Without @autoclosure, would need:
// assert({ 1 + 1 == 2 }, message: "Math is broken")
```

**Use case:** Lazy evaluation (condition only evaluated if needed).

```swift
func logIfDebug(_ message: @autoclosure () -> String) {
    #if DEBUG
    print(message())
    #endif
}

// Expression only evaluated in debug builds
logIfDebug("Expensive computation: \(veryExpensiveFunction())")
```

---

## Practical Example: Async Operation with Closures

```swift
class DataLoader {
    func fetchData(completion: @escaping (Result<Data, Error>) -> Void) {
        DispatchQueue.global().async {
            // Simulate network request
            let success = Bool.random()

            DispatchQueue.main.async {
                if success {
                    let data = Data()
                    completion(.success(data))
                } else {
                    completion(.failure(NSError(domain: "Network", code: -1)))
                }
            }
        }
    }
}

// Usage
let loader = DataLoader()
loader.fetchData { result in
    switch result {
    case .success(let data):
        print("Got data: \(data)")
    case .failure(let error):
        print("Error: \(error)")
    }
}
```

---

## Practical Example: Custom Operators with Closures

```swift
struct User {
    let name: String
    let age: Int
}

let users = [
    User(name: "John", age: 30),
    User(name: "Jane", age: 25),
    User(name: "Bob", age: 35),
    User(name: "Alice", age: 28)
]

// Get names of users over 25, sorted alphabetically
let result = users
    .filter { $0.age > 25 }
    .map { $0.name }
    .sorted()

print(result)  // ["Alice", "Bob", "John"]

// Average age
let averageAge = users
    .map { $0.age }
    .reduce(0, +) / users.count

print(averageAge)  // 29
```

---

## Best Practices

### ✅ DO

```swift
// Use trailing closure syntax
numbers.map { $0 * 2 }

// Use shorthand when clear
let doubled = numbers.map { $0 * 2 }

// Chain functional methods
let result = numbers
    .filter { $0 > 5 }
    .map { $0 * 2 }

// Use [weak self] in escaping closures
fetchData { [weak self] data in
    guard let self = self else { return }
    self.process(data)
}

// Break complex closures into named functions
func isEven(_ number: Int) -> Bool {
    number % 2 == 0
}
let evens = numbers.filter(isEven)
```

### ❌ DON'T

```swift
// Don't over-shorten when unclear
let x = numbers.map { $0 }  // What does this do?

// Don't create retain cycles
// ❌ BAD
fetchData { data in
    self.data = data  // Strong capture
}

// Don't over-chain (hard to read)
let result = numbers.filter { $0 > 5 }.map { $0 * 2 }.reduce(0, +).description.count
// Split into multiple lines!

// Don't forget @escaping
// ❌ Won't compile
func doAsync(completion: () -> Void) {
    DispatchQueue.main.async {
        completion()  // Error: escaping closure
    }
}
```

---

## Summary

You've mastered closures and functional programming:

✅ **Closures** are self-contained blocks of functionality
✅ **Shorthand syntax** uses `$0`, `$1`, implicit returns
✅ **Trailing closures** improve readability
✅ **Capturing** happens automatically (no `use` needed)
✅ **@escaping** required for closures that outlive function
✅ **map** transforms elements
✅ **filter** keeps matching elements
✅ **reduce** combines into single value
✅ **Chaining** creates expressive pipelines
✅ **[weak self]** prevents retain cycles

**Key Takeaway:** Closures and functional programming patterns make Swift code more concise and expressive. Combined with type inference and trailing closure syntax, they're more elegant than PHP's array functions.

---

## What's Next?

In [Chapter 14: Extensions](/series/swift-for-php-developers/chapters/14-extensions), you'll learn how to add functionality to existing types—even types you don't own, like String or Int.

---

**Next Chapter:** [14 — Extensions: Adding Functionality to Existing Types](/series/swift-for-php-developers/chapters/14-extensions)
