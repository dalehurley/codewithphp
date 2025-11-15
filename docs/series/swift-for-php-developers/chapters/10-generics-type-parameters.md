---
title: "Chapter 10: Generics - Type-Safe Code That Works With Any Type"
description: Master Swift's powerful generic system for writing flexible, reusable, type-safe code—far beyond PHP's PHPDoc annotations.
series: swift-for-php-developers
chapter: 10
difficulty: Intermediate to Advanced
tags: ["generics", "type-parameters", "constraints", "associated-types", "type-safety"]
---

# Chapter 10: Generics: Type-Safe Code That Works With Any Type

Generics let you write **flexible, reusable code** that works with any type while maintaining **full type safety**. While PHP relies on PHPDoc annotations for generic hints (with no runtime enforcement), Swift provides true compile-time generic support.

This is one of Swift's most powerful features for building robust, reusable APIs.

## What You'll Learn

- What generics are and why they matter
- Generic functions
- Generic types (structs, classes, enums)
- Type constraints
- Associated types in protocols (revisited)
- Generic where clauses
- Comparing to PHP's limited generic support
- Real-world generic patterns

## Prerequisites

- Completed [Chapter 09: Enums and Pattern Matching](/series/swift-for-php-developers/chapters/09-enums-pattern-matching)
- Understanding of protocols
- Knowledge of Swift's type system

---

## The Problem: Code Duplication

### Without Generics

Imagine you need to swap two variables:

```swift
// Swap two Ints
func swapInts(_ a: inout Int, _ b: inout Int) {
    let temp = a
    a = b
    b = temp
}

// Swap two Strings
func swapStrings(_ a: inout String, _ b: inout String) {
    let temp = a
    a = b
    b = temp
}

// Swap two Doubles... you get the idea
func swapDoubles(_ a: inout Double, _ b: inout Double) {
    let temp = a
    a = b
    b = temp
}
```

**Problem:** Same logic, different types. Code duplication!

### With Generics

```swift
func swap<T>(_ a: inout T, _ b: inout T) {
    let temp = a
    a = b
    b = temp
}

// Works with any type!
var x = 10
var y = 20
swap(&x, &y)  // x = 20, y = 10

var str1 = "hello"
var str2 = "world"
swap(&str1, &str2)  // str1 = "world", str2 = "hello"
```

**Solution:** One function works with all types. `<T>` is a type parameter (placeholder).

---

## PHP's Limited Generic Support

PHP doesn't have true generics (as of PHP 8.3), only PHPDoc annotations:

```php
<?php
/**
 * @template T
 * @param T $a
 * @param T $b
 */
function swap(&$a, &$b): void {
    $temp = $a;
    $a = $b;
    $b = $temp;
}

// Works but NO type safety
$x = 10;
$y = "hello";
swap($x, $y);  // Allowed! $x is now "hello", $y is 10
// No compile-time error!

/**
 * @template T
 */
class Box {
    /** @var T */
    private mixed $value;

    /** @param T $value */
    public function __construct(mixed $value) {
        $this->value = $value;
    }

    /** @return T */
    public function getValue(): mixed {
        return $this->value;
    }
}

$box = new Box(42);
$value = $box->getValue();  // Static analyzers know it's int, runtime doesn't
```

**Limitations:**
- PHPDoc annotations are for **static analysis only**
- No runtime enforcement
- No compile-time type checking
- Can't prevent type mismatches

---

## Generic Functions

### Basic Syntax

```swift
// <T> declares a type parameter
func printValue<T>(_ value: T) {
    print(value)
}

printValue(42)        // T is Int
printValue("hello")   // T is String
printValue([1, 2, 3]) // T is [Int]
```

### Multiple Type Parameters

```swift
func combine<T, U>(_ first: T, _ second: U) -> (T, U) {
    return (first, second)
}

let result = combine(42, "hello")  // (Int, String)
print(result.0)  // 42
print(result.1)  // "hello"
```

### Generic Return Types

```swift
func firstElement<T>(_ array: [T]) -> T? {
    return array.first
}

let numbers = [1, 2, 3]
let first = firstElement(numbers)  // Int?

let words = ["hello", "world"]
let firstWord = firstElement(words)  // String?
```

---

## Generic Types

### Generic Struct

```swift
struct Stack<Element> {
    private var items: [Element] = []

    mutating func push(_ item: Element) {
        items.append(item)
    }

    mutating func pop() -> Element? {
        return items.isEmpty ? nil : items.removeLast()
    }

    func peek() -> Element? {
        return items.last
    }

    var isEmpty: Bool {
        return items.isEmpty
    }

    var count: Int {
        return items.count
    }
}

// Create a stack of Ints
var intStack = Stack<Int>()
intStack.push(10)
intStack.push(20)
print(intStack.pop())  // Optional(20)

// Create a stack of Strings
var stringStack = Stack<String>()
stringStack.push("hello")
stringStack.push("world")
print(stringStack.pop())  // Optional("world")
```

**PHP Comparison:**
```php
<?php
/**
 * @template T
 */
class Stack {
    /** @var array<T> */
    private array $items = [];

    /** @param T $item */
    public function push(mixed $item): void {
        $this->items[] = $item;
    }

    /** @return T|null */
    public function pop(): mixed {
        return array_pop($this->items);
    }
}

$stack = new Stack();
$stack->push(10);
$stack->push("hello");  // No error! Type safety broken at runtime
```

### Generic Class

```swift
class Box<T> {
    var value: T

    init(value: T) {
        self.value = value
    }

    func getValue() -> T {
        return value
    }
}

let intBox = Box(value: 42)
let stringBox = Box(value: "hello")

print(intBox.getValue())     // 42
print(stringBox.getValue())  // "hello"
```

### Generic Enum

We've already seen this with `Optional` and `Result`:

```swift
enum Optional<Wrapped> {
    case none
    case some(Wrapped)
}

enum Result<Success, Failure: Error> {
    case success(Success)
    case failure(Failure)
}
```

---

## Type Constraints

Limit what types can be used with generics.

### Conformance Constraints

```swift
// T must conform to Equatable
func findIndex<T: Equatable>(of value: T, in array: [T]) -> Int? {
    for (index, element) in array.enumerated() {
        if element == value {  // Requires Equatable
            return index
        }
    }
    return nil
}

let numbers = [1, 2, 3, 4, 5]
let index = findIndex(of: 3, in: numbers)  // Optional(2)

// Won't compile with non-Equatable types
struct Person {
    let name: String
}
let people = [Person(name: "John")]
// findIndex(of: Person(name: "John"), in: people)  // ❌ Error: Person doesn't conform to Equatable
```

### Multiple Constraints

```swift
// T must be both Comparable and Hashable
func findMax<T: Comparable & Hashable>(_ a: T, _ b: T) -> T {
    return a > b ? a : b
}

print(findMax(10, 20))      // 20
print(findMax("a", "b"))    // "b"
```

### Class Constraints

```swift
// T must be a class (reference type)
func processClass<T: AnyObject>(_ object: T) {
    // Only works with classes
}

class MyClass {}
struct MyStruct {}

processClass(MyClass())   // ✅ Works
// processClass(MyStruct())  // ❌ Error: MyStruct is not a class
```

---

## Where Clauses

Add complex constraints with `where`.

### Basic Where Clause

```swift
func allEqual<T: Equatable>(_ a: T, _ b: T, _ c: T) -> Bool where T: Comparable {
    return a == b && b == c && a >= b
}
```

### Where with Associated Types

```swift
// Container with items that are Equatable
func allItemsMatch<C1: Container, C2: Container>(_ container1: C1, _ container2: C2) -> Bool
    where C1.Item == C2.Item, C1.Item: Equatable {

    if container1.count != container2.count {
        return false
    }

    for i in 0..<container1.count {
        if container1[i] != container2[i] {
            return false
        }
    }

    return true
}

protocol Container {
    associatedtype Item
    var count: Int { get }
    subscript(i: Int) -> Item { get }
}
```

### Extension with Where

```swift
extension Array where Element: Numeric {
    func sum() -> Element {
        return reduce(0, +)
    }
}

let numbers = [1, 2, 3, 4, 5]
print(numbers.sum())  // 15

let doubles = [1.5, 2.5, 3.0]
print(doubles.sum())  // 7.0

let strings = ["a", "b", "c"]
// strings.sum()  // ❌ Error: String is not Numeric
```

---

## Associated Types Revisited

Protocols can have generic requirements via associated types:

```swift
protocol Container {
    associatedtype Item  // Placeholder type

    var count: Int { get }
    mutating func append(_ item: Item)
    subscript(i: Int) -> Item { get }
}

struct IntStack: Container {
    // Item is inferred as Int
    private var items: [Int] = []

    var count: Int {
        return items.count
    }

    mutating func append(_ item: Int) {
        items.append(item)
    }

    subscript(i: Int) -> Int {
        return items[i]
    }
}

struct StringStack: Container {
    // Item is inferred as String
    private var items: [String] = []

    var count: Int {
        return items.count
    }

    mutating func append(_ item: String) {
        items.append(item)
    }

    subscript(i: Int) -> String {
        return items[i]
    }
}
```

### Generic Type Conforming to Protocol

```swift
struct GenericStack<Element>: Container {
    // Item is explicitly Element
    typealias Item = Element

    private var items: [Element] = []

    var count: Int {
        return items.count
    }

    mutating func append(_ item: Element) {
        items.append(item)
    }

    subscript(i: Int) -> Element {
        return items[i]
    }
}
```

---

## Practical Example: Result Type

Swift's built-in `Result` type is a generic enum:

```swift
enum Result<Success, Failure: Error> {
    case success(Success)
    case failure(Failure)
}

enum NetworkError: Error {
    case badURL
    case requestFailed
    case invalidResponse
}

struct User {
    let id: Int
    let name: String
}

func fetchUser(id: Int) -> Result<User, NetworkError> {
    if id > 0 {
        let user = User(id: id, name: "John")
        return .success(user)
    } else {
        return .failure(.badURL)
    }
}

// Usage
let result = fetchUser(id: 1)

switch result {
case .success(let user):
    print("Fetched user: \(user.name)")
case .failure(let error):
    print("Error: \(error)")
}
```

**PHP Comparison:**
```php
<?php
// Would require separate classes or arrays
class Success {
    public function __construct(public mixed $value) {}
}

class Failure {
    public function __construct(public Exception $error) {}
}

function fetchUser(int $id): Success|Failure {
    if ($id > 0) {
        return new Success(['id' => $id, 'name' => 'John']);
    } else {
        return new Failure(new Exception('Bad URL'));
    }
}

$result = fetchUser(1);

if ($result instanceof Success) {
    echo "User: " . $result->value['name'];
} elseif ($result instanceof Failure) {
    echo "Error: " . $result->error->getMessage();
}
```

---

## Practical Example: Generic Cache

```swift
class Cache<Key: Hashable, Value> {
    private var storage: [Key: Value] = [:]

    func set(_ value: Value, forKey key: Key) {
        storage[key] = value
    }

    func get(_ key: Key) -> Value? {
        return storage[key]
    }

    func remove(_ key: Key) {
        storage.removeValue(forKey: key)
    }

    func clear() {
        storage.removeAll()
    }
}

// String keys, User values
let userCache = Cache<String, User>()
userCache.set(User(id: 1, name: "John"), forKey: "user1")
if let user = userCache.get("user1") {
    print(user.name)  // "John"
}

// Int keys, String values
let stringCache = Cache<Int, String>()
stringCache.set("Hello", forKey: 1)
print(stringCache.get(1) ?? "Not found")  // "Hello"
```

---

## Practical Example: Generic Pair

```swift
struct Pair<T, U> {
    let first: T
    let second: U

    init(_ first: T, _ second: U) {
        self.first = first
        self.second = second
    }

    func swapped() -> Pair<U, T> {
        return Pair<U, T>(second, first)
    }
}

let pair = Pair(42, "hello")
print(pair.first)   // 42
print(pair.second)  // "hello"

let swapped = pair.swapped()
print(swapped.first)   // "hello"
print(swapped.second)  // 42
```

---

## Generic Subscripts

```swift
extension Container {
    subscript<Indices: Sequence>(indices: Indices) -> [Item]
        where Indices.Element == Int {

        var result: [Item] = []
        for index in indices {
            result.append(self[index])
        }
        return result
    }
}

let numbers = [10, 20, 30, 40, 50]
let subset = numbers[[0, 2, 4]]  // [10, 30, 50]
```

---

## Type Inference with Generics

Swift often infers generic types:

```swift
// Explicit
let box1: Box<Int> = Box(value: 42)

// Inferred (recommended)
let box2 = Box(value: 42)  // Swift infers Box<Int>

// Array literal
let numbers = [1, 2, 3]  // [Int] inferred

// Function call
func identity<T>(_ value: T) -> T {
    return value
}

let result = identity(42)  // T inferred as Int
```

---

## Common Generic Patterns

### 1. Optional Wrapping

```swift
func wrapInOptional<T>(_ value: T) -> T? {
    return value
}
```

### 2. Generic Factory

```swift
protocol Creatable {
    init()
}

func createInstance<T: Creatable>(_ type: T.Type) -> T {
    return T()
}
```

### 3. Generic Mapper

```swift
func map<T, U>(_ array: [T], transform: (T) -> U) -> [U] {
    var result: [U] = []
    for item in array {
        result.append(transform(item))
    }
    return result
}

let numbers = [1, 2, 3]
let doubled = map(numbers) { $0 * 2 }  // [2, 4, 6]
```

---

## Best Practices

### ✅ DO

```swift
// Use descriptive type parameter names for clarity
struct Pair<First, Second> {
    let first: First
    let second: Second
}

// Use T, U, V for simple cases
func swap<T>(_ a: inout T, _ b: inout T) { ... }

// Add constraints when needed
func sorted<T: Comparable>(_ array: [T]) -> [T] { ... }

// Let Swift infer types
let box = Box(value: 42)  // Not Box<Int>(value: 42)
```

### ❌ DON'T

```swift
// Don't over-constrain
func process<T: Equatable & Comparable & Hashable>(_ value: T) {
    // Only use constraints you actually need
}

// Don't use generics when a protocol suffices
protocol Drawable {
    func draw()
}

// ❌ Overkill
func drawAll<T: Drawable>(_ items: [T]) { ... }

// ✅ Better
func drawAll(_ items: [Drawable]) { ... }
```

---

## Debugging Generic Code

```swift
func printType<T>(_ value: T) {
    print("Type: \(type(of: value))")
    print("Value: \(value)")
}

printType(42)           // Type: Int
printType("hello")      // Type: String
printType([1, 2, 3])    // Type: Array<Int>
```

---

## Summary

You've mastered Swift generics:

✅ **Generic functions** write reusable logic for any type
✅ **Generic types** (structs, classes, enums) provide type-safe containers
✅ **Type constraints** limit generics to specific protocols or types
✅ **Where clauses** add complex constraints
✅ **Associated types** enable generic protocols
✅ **Type inference** reduces boilerplate
✅ **Compile-time safety** prevents type errors (unlike PHP)

**Key Takeaway:** Generics enable you to write flexible, reusable code without sacrificing type safety. Swift's generics are fully checked at compile time, unlike PHP's PHPDoc annotations which are only for static analysis.

---

## What's Next?

In [Chapter 11: Error Handling](/series/swift-for-php-developers/chapters/11-error-handling), you'll learn Swift's powerful error handling system with `throws`, `try`, and `catch`—a type-safe alternative to PHP's exceptions.

---

**Next Chapter:** [11 — Error Handling with Throws, Try, and Catch](/series/swift-for-php-developers/chapters/11-error-handling)
