---
title: "Chapter 08: Protocols - Swift's Answer to Interfaces"
description: Master protocols and protocol-oriented programming—Swift's most powerful feature that goes far beyond PHP interfaces.
series: swift-for-php-developers
chapter: 8
difficulty: Intermediate
tags: ["protocols", "interfaces", "protocol-oriented", "extensions", "composition"]
---

# Chapter 08: Protocols: Swift's Answer to Interfaces

Protocols are **Swift's superpower** and fundamentally different from PHP interfaces. While PHP interfaces simply define method contracts, Swift protocols can require properties, provide default implementations, use associated types, and enable protocol-oriented programming—a modern alternative to classical inheritance.

This is one of the most important chapters in the series. Understanding protocols transforms how you write Swift code.

## What You'll Learn

- Protocols vs PHP interfaces
- Protocol requirements (methods and properties)
- Protocol conformance
- Protocol extensions and default implementations
- Protocol composition
- Associated types
- Protocol-oriented programming paradigm
- When to use protocols vs inheritance

## Prerequisites

- Completed [Chapter 07: Properties and Methods](/series/swift-for-php-developers/chapters/07-properties-methods-initializers)
- Understanding of PHP interfaces
- Knowledge of structs and classes

---

## PHP Interfaces: The Basics

```php
<?php
interface Drawable {
    public function draw(): void;
}

interface Colorable {
    public function getColor(): string;
    public function setColor(string $color): void;
}

class Circle implements Drawable, Colorable {
    private string $color = 'black';

    public function draw(): void {
        echo "Drawing a {$this->color} circle\n";
    }

    public function getColor(): string {
        return $this->color;
    }

    public function setColor(string $color): void {
        $this->color = $color;
    }
}
```

**PHP interfaces:**
- Define method signatures only
- No implementation
- No property requirements
- Can implement multiple

---

## Swift Protocols: Much More Powerful

```swift
protocol Drawable {
    func draw()
}

protocol Colorable {
    var color: String { get set }  // Property requirement!
}

struct Circle: Drawable, Colorable {
    var color: String = "black"

    func draw() {
        print("Drawing a \(color) circle")
    }
}

let circle = Circle()
circle.draw()
```

**Swift protocols can:**
- Require methods ✅
- Require properties ✅
- Provide default implementations ✅ (via extensions)
- Use associated types (generics) ✅
- Be composed together ✅

---

## Defining Protocols

### Method Requirements

```swift
protocol Vehicle {
    func start()
    func stop()
    func accelerate(to speed: Double)
}

struct Car: Vehicle {
    func start() {
        print("Engine starting")
    }

    func stop() {
        print("Engine stopping")
    }

    func accelerate(to speed: Double) {
        print("Accelerating to \(speed) mph")
    }
}
```

**PHP Comparison:**
```php
<?php
interface Vehicle {
    public function start(): void;
    public function stop(): void;
    public function accelerate(float $speed): void;
}

class Car implements Vehicle {
    public function start(): void {
        echo "Engine starting\n";
    }

    public function stop(): void {
        echo "Engine stopping\n";
    }

    public function accelerate(float $speed): void {
        echo "Accelerating to $speed mph\n";
    }
}
```

### Property Requirements

**Swift can require properties!** PHP cannot.

```swift
protocol Named {
    var name: String { get }  // Read-only
}

protocol Identifiable {
    var id: Int { get set }  // Read-write
}

struct User: Named, Identifiable {
    let name: String  // Satisfies 'get' requirement
    var id: Int       // Satisfies 'get set' requirement
}
```

**PHP Workaround:**
```php
<?php
interface Named {
    public function getName(): string;
}

interface Identifiable {
    public function getId(): int;
    public function setId(int $id): void;
}

class User implements Named, Identifiable {
    public function __construct(
        private string $name,
        private int $id
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }
}
```

### Mutating Method Requirements

For value types (structs), mark methods that modify state as `mutating`.

```swift
protocol Incrementable {
    mutating func increment()
}

struct Counter: Incrementable {
    var count = 0

    mutating func increment() {
        count += 1
    }
}

var counter = Counter()
counter.increment()
print(counter.count)  // 1
```

### Initializer Requirements

```swift
protocol Parseable {
    init(from string: String)
}

struct User: Parseable {
    let name: String

    init(from string: String) {
        self.name = string
    }
}

let user = User(from: "John")
```

---

## Protocol Conformance

### Adopting Protocols

```swift
protocol Equatable {
    static func == (lhs: Self, rhs: Self) -> Bool
}

struct Point: Equatable {
    let x: Int
    let y: Int

    static func == (lhs: Point, rhs: Point) -> Bool {
        lhs.x == rhs.x && lhs.y == rhs.y
    }
}

let p1 = Point(x: 10, y: 20)
let p2 = Point(x: 10, y: 20)
print(p1 == p2)  // true
```

### Conditional Conformance

```swift
extension Array: Equatable where Element: Equatable {
    // Array is Equatable when its elements are Equatable
}

let arr1 = [1, 2, 3]
let arr2 = [1, 2, 3]
print(arr1 == arr2)  // true
```

**PHP doesn't have this concept.**

---

## Protocol Extensions: Default Implementations

**This is where Swift protocols become incredibly powerful!**

### Providing Default Methods

```swift
protocol Greetable {
    var name: String { get }
    func greet()
}

// Default implementation for all Greetable types
extension Greetable {
    func greet() {
        print("Hello, \(name)!")
    }
}

struct Person: Greetable {
    let name: String
    // Gets greet() for free!
}

let person = Person(name: "John")
person.greet()  // "Hello, John!"
```

**PHP Comparison (Traits):**
```php
<?php
interface Greetable {
    public function getName(): string;
    public function greet(): void;
}

trait GreetableTrait {
    public function greet(): void {
        echo "Hello, {$this->getName()}!\n";
    }
}

class Person implements Greetable {
    use GreetableTrait;

    public function __construct(private string $name) {}

    public function getName(): string {
        return $this->name;
    }
}
```

**Key Difference:** Swift protocol extensions apply automatically to ALL conforming types. PHP traits must be explicitly included.

### Overriding Default Implementation

```swift
protocol Greetable {
    var name: String { get }
    func greet()
}

extension Greetable {
    func greet() {
        print("Hello, \(name)!")
    }
}

struct FormalPerson: Greetable {
    let name: String

    // Override default
    func greet() {
        print("Good day, \(name).")
    }
}

let person = FormalPerson(name: "John")
person.greet()  // "Good day, John."
```

### Adding Computed Properties

```swift
protocol Named {
    var firstName: String { get }
    var lastName: String { get }
}

extension Named {
    var fullName: String {
        "\(firstName) \(lastName)"
    }
}

struct Person: Named {
    let firstName: String
    let lastName: String
    // Gets fullName for free!
}

let person = Person(firstName: "John", lastName: "Doe")
print(person.fullName)  // "John Doe"
```

---

## Protocol Composition

Combine multiple protocols into one requirement.

```swift
protocol Named {
    var name: String { get }
}

protocol Aged {
    var age: Int { get }
}

// Require both protocols
func celebrate(person: Named & Aged) {
    print("\(person.name) is turning \(person.age)!")
}

struct Person: Named, Aged {
    let name: String
    let age: Int
}

let person = Person(name: "John", age: 30)
celebrate(person: person)
```

**PHP doesn't support this—must create a new interface that extends both.**

```php
<?php
interface Named {
    public function getName(): string;
}

interface Aged {
    public function getAge(): int;
}

interface NamedAndAged extends Named, Aged {}

function celebrate(NamedAndAged $person): void {
    echo "{$person->getName()} is turning {$person->getAge()}!\n";
}
```

---

## Associated Types (Generics in Protocols)

Protocols can have placeholder types.

```swift
protocol Container {
    associatedtype Item  // Placeholder type

    var count: Int { get }
    mutating func append(_ item: Item)
    subscript(index: Int) -> Item { get }
}

struct IntStack: Container {
    private var items: [Int] = []

    // Item is inferred as Int
    var count: Int {
        items.count
    }

    mutating func append(_ item: Int) {
        items.append(item)
    }

    subscript(index: Int) -> Int {
        items[index]
    }
}

struct StringStack: Container {
    private var items: [String] = []

    // Item is inferred as String
    var count: Int {
        items.count
    }

    mutating func append(_ item: String) {
        items.append(item)
    }

    subscript(index: Int) -> String {
        items[index]
    }
}
```

**PHP has no equivalent—generics are limited to PHPDoc annotations.**

---

## Protocol-Oriented Programming

**Prefer protocols over class hierarchies.**

### Traditional OOP (Class Hierarchy)

```swift
// ❌ Old way: Deep class hierarchy
class Animal {
    func makeSound() {
        fatalError("Must override")
    }
}

class Bird: Animal {
    override func makeSound() {
        print("Chirp")
    }

    func fly() {
        print("Flying")
    }
}

class Penguin: Bird {
    override func fly() {
        fatalError("Penguins can't fly!")
    }
}
```

**Problem:** Penguins inherit `fly()` even though they can't fly!

### Protocol-Oriented Approach

```swift
// ✅ New way: Compose capabilities
protocol Animal {
    func makeSound()
}

protocol Flying {
    func fly()
}

protocol Swimming {
    func swim()
}

struct Sparrow: Animal, Flying {
    func makeSound() {
        print("Chirp")
    }

    func fly() {
        print("Flying")
    }
}

struct Penguin: Animal, Swimming {
    func makeSound() {
        print("Squawk")
    }

    func swim() {
        print("Swimming")
    }
    // No fly() - doesn't conform to Flying!
}

struct Duck: Animal, Flying, Swimming {
    func makeSound() {
        print("Quack")
    }

    func fly() {
        print("Flying")
    }

    func swim() {
        print("Swimming")
    }
}
```

**Benefits:**
- Compose only needed capabilities
- No inheritance issues
- Works with structs (value types)
- More flexible

---

## Standard Library Protocols

Swift's standard library uses protocols extensively.

### Equatable

```swift
struct Point: Equatable {
    let x: Int
    let y: Int
}

let p1 = Point(x: 10, y: 20)
let p2 = Point(x: 10, y: 20)
print(p1 == p2)  // true (automatic implementation!)
```

### Comparable

```swift
struct Person: Comparable {
    let name: String
    let age: Int

    static func < (lhs: Person, rhs: Person) -> Bool {
        lhs.age < rhs.age
    }
}

let people = [
    Person(name: "Bob", age: 30),
    Person(name: "Alice", age: 25),
    Person(name: "Charlie", age: 35)
]

let sorted = people.sorted()  // Sorted by age
```

### Codable (Encodable + Decodable)

```swift
struct User: Codable {
    let id: Int
    let name: String
    let email: String
}

// Automatic JSON encoding/decoding!
let user = User(id: 1, name: "John", email: "john@example.com")

let encoder = JSONEncoder()
let jsonData = try encoder.encode(user)

let decoder = JSONDecoder()
let decoded = try decoder.decode(User.self, from: jsonData)
```

**PHP Comparison:**
```php
<?php
class User implements JsonSerializable {
    public function __construct(
        public int $id,
        public string $name,
        public string $email
    ) {}

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email
        ];
    }
}

$user = new User(1, "John", "john@example.com");
$json = json_encode($user);
$decoded = json_decode($json);
```

---

## Practical Example: Payment Processing

```swift
protocol PaymentMethod {
    var name: String { get }
    func process(amount: Double) -> Bool
}

// Default implementation
extension PaymentMethod {
    func formatAmount(_ amount: Double) -> String {
        String(format: "$%.2f", amount)
    }
}

struct CreditCard: PaymentMethod {
    let name = "Credit Card"
    let cardNumber: String

    func process(amount: Double) -> Bool {
        print("Processing \(formatAmount(amount)) via credit card")
        return true
    }
}

struct PayPal: PaymentMethod {
    let name = "PayPal"
    let email: String

    func process(amount: Double) -> Bool {
        print("Processing \(formatAmount(amount)) via PayPal")
        return true
    }
}

struct Cash: PaymentMethod {
    let name = "Cash"

    func process(amount: Double) -> Bool {
        print("Received \(formatAmount(amount)) in cash")
        return true
    }
}

// Works with any PaymentMethod
func checkout(amount: Double, method: PaymentMethod) {
    print("Payment method: \(method.name)")
    if method.process(amount: amount) {
        print("Payment successful!")
    }
}

let card = CreditCard(cardNumber: "1234-5678")
let paypal = PayPal(email: "user@example.com")
let cash = Cash()

checkout(amount: 99.99, method: card)
checkout(amount: 49.99, method: paypal)
checkout(amount: 29.99, method: cash)
```

---

## Protocols vs Classes: When to Use What

| Use Case | Protocol | Class |
|----------|----------|-------|
| Define capabilities | ✅ | ❌ |
| Work with value types (structs) | ✅ | ❌ |
| Provide default behavior | ✅ (extensions) | ✅ (inheritance) |
| Compose multiple behaviors | ✅ | ❌ (single inheritance) |
| Share implementation | ✅ (extensions) | ✅ (inheritance) |
| Maintain identity | ❌ | ✅ |
| Polymorphism | ✅ | ✅ |

**Swift Recommendation:** Start with protocols and structs. Use classes only when you need reference semantics or inheritance.

---

## Summary

You've mastered Swift protocols:

✅ **Protocols** define requirements (methods and properties)
✅ **Protocol extensions** provide default implementations
✅ **Protocol composition** combines multiple protocols
✅ **Associated types** enable generic protocols
✅ **Protocol-oriented programming** is Swift's paradigm
✅ **Protocols work with structs** (unlike class inheritance)
✅ **More powerful** than PHP interfaces

**Key Takeaway:** Protocols are Swift's most powerful feature. They go far beyond PHP interfaces and enable a fundamentally different programming style. Master protocols to write idiomatic Swift code.

---

## What's Next?

In [Chapter 09: Enums and Pattern Matching](/series/swift-for-php-developers/chapters/09-enums-pattern-matching), you'll learn about Swift's incredibly powerful enums—far beyond PHP's simple backed enums.

---

**Next Chapter:** [09 — Enums and Pattern Matching](/series/swift-for-php-developers/chapters/09-enums-pattern-matching)
