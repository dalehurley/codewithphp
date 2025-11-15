---
title: "Chapter 06: Classes and Structs - Reference vs Value Types"
description: Understand Swift's fundamental choice between value types (structs) and reference types (classes).
series: swift-for-php-developers
chapter: 6
difficulty: Intermediate
tags: ["classes", "structs", "value-types", "reference-types", "memory"]
---

# Chapter 06: Classes and Structs: Reference vs Value Types

This is one of the **most important concepts** in Swift and represents a fundamental difference from PHP. PHP has only reference types (objects). Swift has both value types (structs) and reference types (classes), and **Apple recommends using structs by default**.

Understanding this distinction is critical for writing correct, safe Swift code.

## What You'll Learn

- Value types vs reference types
- Structs (value semantics)
- Classes (reference semantics)
- When to use structs vs classes
- Mutability and copying behavior
- Memory management implications
- How this differs completely from PHP

## Prerequisites

- Completed [Chapter 05: Collections](/series/swift-for-php-developers/chapters/05-collections-arrays-dictionaries-sets)
- Understanding of PHP classes and objects
- Basic memory concepts

---

## The Fundamental Difference

### PHP: Everything Is a Reference

```php
<?php
class User {
    public string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }
}

$user1 = new User("John");
$user2 = $user1;  // Same reference!

$user2->name = "Jane";

echo $user1->name;  // "Jane" - Modified!
echo $user2->name;  // "Jane" - Same object

// To copy, must explicitly clone
$user3 = clone $user1;
$user3->name = "Bob";
echo $user1->name;  // "Jane" - Not modified
```

**In PHP:** Objects are always references. Assignment creates a new reference to the same object.

### Swift: Two Options

**Option 1: Struct (Value Type)**

```swift
struct User {
    var name: String
}

var user1 = User(name: "John")
var user2 = user1  // Copies the value!

user2.name = "Jane"

print(user1.name)  // "John" - Not modified!
print(user2.name)  // "Jane" - Independent copy
```

**Option 2: Class (Reference Type)**

```swift
class Person {
    var name: String
    init(name: String) {
        self.name = name
    }
}

var person1 = Person(name: "John")
var person2 = person1  // Same reference!

person2.name = "Jane"

print(person1.name)  // "Jane" - Modified!
print(person2.name)  // "Jane" - Same object
```

---

## Value Types: Structs

### Definition

A **value type** is copied when assigned or passed to a function. Each copy is independent.

### Creating Structs

```swift
struct Point {
    var x: Int
    var y: Int
}

// Automatic memberwise initializer
let origin = Point(x: 0, y: 0)
var point = Point(x: 10, y: 20)

// Copy on assignment
var anotherPoint = point  // Copies
anotherPoint.x = 30

print(point.x)         // 10 (unchanged)
print(anotherPoint.x)  // 30 (modified)
```

### Struct Characteristics

```swift
struct Rectangle {
    var width: Double
    var height: Double

    // Computed property
    var area: Double {
        return width * height
    }

    // Methods
    func describe() -> String {
        return "Rectangle: \(width)x\(height)"
    }

    // Mutating method (can modify properties)
    mutating func scale(by factor: Double) {
        width *= factor
        height *= factor
    }
}

var rect = Rectangle(width: 10, height: 20)
print(rect.area)  // 200.0

rect.scale(by: 2)
print(rect.width)  // 20.0
```

**PHP Comparison:**
PHP doesn't have structs. You'd use a class:

```php
<?php
class Rectangle {
    public function __construct(
        public float $width,
        public float $height
    ) {}

    public function area(): float {
        return $this->width * $this->height;
    }

    public function scale(float $factor): void {
        $this->width *= $factor;
        $this->height *= $factor;
    }
}
```

---

## Reference Types: Classes

### Definition

A **reference type** shares a single instance. Assignment creates a new reference to the same object.

### Creating Classes

```swift
class BankAccount {
    var balance: Double
    let accountNumber: String

    init(accountNumber: String, balance: Double = 0) {
        self.accountNumber = accountNumber
        self.balance = balance
    }

    func deposit(_ amount: Double) {
        balance += amount
    }

    func withdraw(_ amount: Double) {
        balance -= amount
    }
}

let account = BankAccount(accountNumber: "12345", balance: 1000)
account.deposit(500)
print(account.balance)  // 1500.0
```

### Reference Sharing

```swift
let account1 = BankAccount(accountNumber: "12345", balance: 1000)
let account2 = account1  // Same reference!

account2.deposit(500)

print(account1.balance)  // 1500 - Modified!
print(account2.balance)  // 1500 - Same object

// Check if same instance
account1 === account2  // true (same object)
```

**Note:** Use `===` to check if two references point to the same object. Use `==` for value equality (requires Equatable).

---

## Structs vs Classes: Key Differences

| Feature | Struct | Class |
|---------|--------|-------|
| **Type** | Value type | Reference type |
| **Assignment** | Copies | References |
| **Mutation** | Requires `mutating` keyword | Always mutable |
| **Inheritance** | ❌ No | ✅ Yes |
| **Deinitializers** | ❌ No | ✅ Yes (deinit) |
| **Reference counting** | ❌ No | ✅ Yes (ARC) |
| **Identity** | ❌ No | ✅ Yes (===) |
| **Default** | ✅ Recommended | Use when needed |
| **Memory** | Stack (usually) | Heap |
| **Thread safety** | ✅ Safe (copies) | ❌ Requires synchronization |

---

## When to Use Structs (Default Choice)

Use structs for:

✅ **Simple data models**
```swift
struct User {
    let id: Int
    var name: String
    var email: String
}
```

✅ **Value-like types** (things that should copy)
```swift
struct Point {
    var x: Int
    var y: Int
}

struct Color {
    var red: Double
    var green: Double
    var blue: Double
}
```

✅ **Independent state**
```swift
struct Temperature {
    var celsius: Double

    var fahrenheit: Double {
        return celsius * 9/5 + 32
    }
}
```

✅ **Thread safety matters**
```swift
struct Settings {
    var theme: String
    var fontSize: Int
}
// Each thread gets its own copy automatically
```

---

## When to Use Classes

Use classes for:

✅ **Shared mutable state**
```swift
class NetworkConnection {
    var isConnected: Bool = false

    func connect() {
        isConnected = true
    }
}

let connection = NetworkConnection()
// Multiple parts of app share same connection
```

✅ **Identity matters**
```swift
class Player {
    var name: String
    var score: Int

    init(name: String, score: Int = 0) {
        self.name = name
        self.score = score
    }
}

// Want same player object everywhere
let player1 = Player(name: "John")
let currentPlayer = player1  // Same player
currentPlayer.score = 100
print(player1.score)  // 100 - Same object
```

✅ **Inheritance needed**
```swift
class Vehicle {
    var speed: Double = 0
}

class Car: Vehicle {
    var brand: String = ""
}
```

✅ **Deinitialization needed**
```swift
class FileHandle {
    let filename: String

    init(filename: String) {
        self.filename = filename
        print("Opening \(filename)")
    }

    deinit {
        print("Closing \(filename)")
    }
}
```

✅ **Objective-C interoperability**
```swift
// Must use classes for UIKit/AppKit
class MyViewController: UIViewController {
    // ...
}
```

---

## Mutability

### Struct Mutability

```swift
struct Counter {
    var value: Int = 0

    // Must mark as mutating to modify properties
    mutating func increment() {
        value += 1
    }
}

var counter = Counter()
counter.increment()  // ✅ OK

let constCounter = Counter()
// constCounter.increment()  // ❌ Error: cannot mutate 'let' constant
```

**With let:** Entire struct is immutable.
**With var:** Can call mutating methods.

### Class Mutability

```swift
class Counter {
    var value: Int = 0

    func increment() {
        value += 1  // No 'mutating' needed
    }
}

let counter = Counter()  // 'let' only prevents reassignment
counter.increment()  // ✅ OK - can modify properties
// counter = Counter()  // ❌ Error: cannot reassign constant

counter.value = 10  // ✅ OK - properties are mutable
```

**With let:** Cannot reassign variable, but can modify properties.
**With var:** Can do both.

---

## Copy Behavior in Detail

### Struct Copying

```swift
struct Point {
    var x: Int
    var y: Int
}

var p1 = Point(x: 10, y: 20)
var p2 = p1  // Copies

// Modify copy
p2.x = 30

// Original unchanged
print(p1.x)  // 10
print(p2.x)  // 30
```

### Class Referencing

```swift
class Point {
    var x: Int
    var y: Int

    init(x: Int, y: Int) {
        self.x = x
        self.y = y
    }
}

let p1 = Point(x: 10, y: 20)
let p2 = p1  // Same reference

// Modify through reference
p2.x = 30

// Both point to same object
print(p1.x)  // 30 - Modified!
print(p2.x)  // 30
```

---

## Copying Classes (When Needed)

If you need to copy a class, implement custom copying:

```swift
class Person {
    var name: String
    var age: Int

    init(name: String, age: Int) {
        self.name = name
        self.age = age
    }

    // Custom copy method
    func copy() -> Person {
        return Person(name: self.name, age: self.age)
    }
}

let person1 = Person(name: "John", age: 30)
let person2 = person1.copy()  // Create new instance

person2.name = "Jane"

print(person1.name)  // "John" - Not modified
print(person2.name)  // "Jane" - Independent
```

**PHP Comparison:**
```php
<?php
$person1 = new Person("John", 30);
$person2 = clone $person1;  // Clone

$person2->name = "Jane";
echo $person1->name;  // "John"
```

---

## Nested Types

Structs can contain classes and vice versa:

```swift
struct Team {
    var name: String
    var members: [Player]  // Array of class instances
}

class Player {
    var name: String
    var position: Position  // Struct property

    init(name: String, position: Position) {
        self.name = name
        self.position = position
    }
}

struct Position {
    var x: Double
    var y: Double
}
```

**Behavior:**
- Copying `Team` creates new array but references same `Player` instances
- Copying `Position` creates completely independent copy

---

## Real-World Example: Shopping Cart

### Using Struct (Value Type)

```swift
struct Product {
    let id: Int
    let name: String
    let price: Double
}

struct CartItem {
    let product: Product
    var quantity: Int
}

struct ShoppingCart {
    private(set) var items: [CartItem] = []

    mutating func add(product: Product, quantity: Int = 1) {
        if let index = items.firstIndex(where: { $0.product.id == product.id }) {
            items[index].quantity += quantity
        } else {
            items.append(CartItem(product: product, quantity: quantity))
        }
    }

    func total() -> Double {
        items.reduce(0) { $0 + ($1.product.price * Double($1.quantity)) }
    }
}

var cart1 = ShoppingCart()
cart1.add(product: Product(id: 1, name: "Book", price: 29.99))

var cart2 = cart1  // Copy
cart2.add(product: Product(id: 2, name: "Pen", price: 4.99))

print(cart1.items.count)  // 1 (unchanged)
print(cart2.items.count)  // 2 (modified)
```

### Using Class (Reference Type)

```swift
class ShoppingCartManager {
    private var items: [CartItem] = []

    func add(product: Product, quantity: Int = 1) {
        if let index = items.firstIndex(where: { $0.product.id == product.id }) {
            items[index].quantity += quantity
        } else {
            items.append(CartItem(product: product, quantity: quantity))
        }
    }

    func total() -> Double {
        items.reduce(0) { $0 + ($1.product.price * Double($1.quantity)) }
    }
}

let cart1 = ShoppingCartManager()
cart1.add(product: Product(id: 1, name: "Book", price: 29.99))

let cart2 = cart1  // Same cart!
cart2.add(product: Product(id: 2, name: "Pen", price: 4.99))

print(cart1.total())  // 34.98 (both modified!)
print(cart2.total())  // 34.98 (same object)
```

**When to use which:**
- **Struct:** When each user should have their own independent cart
- **Class:** When you want a shared cart across the app

---

## Performance Considerations

### Struct Performance

```swift
// Small structs are fast (stack allocated)
struct Point {
    var x: Int
    var y: Int
}

// Large structs can be slower (more copying)
struct HugeData {
    var data: [Double] = Array(repeating: 0, count: 10000)
}

// But Swift optimizes with copy-on-write for many types
var array1 = [1, 2, 3]
var array2 = array1  // Shares storage
array2.append(4)     // NOW it copies
```

### Class Performance

```swift
// Classes always allocate on heap
class Node {
    var value: Int
    var next: Node?

    init(value: Int) {
        self.value = value
    }
}

// But no copying overhead
let node1 = Node(value: 1)
let node2 = node1  // Just copies reference (cheap)
```

**Rule of Thumb:** Prefer structs unless you specifically need reference semantics.

---

## Common Mistakes

### ❌ Mistake 1: Using Class When Struct Would Work

```swift
// ❌ Bad: Unnecessary reference type
class Point {
    var x: Int
    var y: Int
    init(x: Int, y: Int) {
        self.x = x
        self.y = y
    }
}

// ✅ Good: Value type is appropriate
struct Point {
    var x: Int
    var y: Int
}
```

### ❌ Mistake 2: Forgetting Mutating

```swift
struct Counter {
    var value = 0

    // ❌ Forgot 'mutating'
    func increment() {
        // value += 1  // Error!
    }

    // ✅ Correct
    mutating func increment() {
        value += 1
    }
}
```

### ❌ Mistake 3: Unexpected Sharing (Classes)

```swift
class Settings {
    var theme: String = "light"
}

let settings1 = Settings()
let settings2 = settings1  // Oops, same reference!

settings2.theme = "dark"
print(settings1.theme)  // "dark" - Unexpected!
```

---

## Summary

You've mastered value vs reference types:

✅ **Structs** are value types (copy on assignment)
✅ **Classes** are reference types (share on assignment)
✅ **Default to structs** unless you need reference semantics
✅ **Use classes** for shared state, identity, inheritance
✅ **Understand copying** behavior for correctness
✅ **Thread safety** comes naturally with value types

**Key Takeaway:** This is fundamentally different from PHP where everything is a reference. Swift gives you both options, and choosing correctly is essential for writing safe, predictable code.

---

## What's Next?

In [Chapter 07: Properties and Methods](/series/swift-for-php-developers/chapters/07-properties-methods-initializers), you'll learn about stored properties, computed properties, property observers, and how they differ from PHP's getters/setters.

---

**Next Chapter:** [07 — Properties, Methods, and Initializers](/series/swift-for-php-developers/chapters/07-properties-methods-initializers)
