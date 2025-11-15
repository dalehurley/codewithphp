---
title: "Chapter 12: ARC and Memory Management - Avoiding Retain Cycles"
description: Master Swift's Automatic Reference Counting and learn to prevent memory leaks with weak and unowned references.
series: swift-for-php-developers
chapter: 12
difficulty: Intermediate to Advanced
tags: ["arc", "memory-management", "retain-cycles", "weak", "unowned", "memory-leaks"]
---

# Chapter 12: ARC and Memory Management

Swift uses **Automatic Reference Counting (ARC)** to manage memory automatically—different from PHP's garbage collection. While ARC handles most memory management for you, understanding how it works is critical to **avoiding memory leaks** caused by retain cycles.

This chapter teaches you how Swift manages memory and how to write leak-free code.

## What You'll Learn

- How ARC works
- Reference counting basics
- Strong, weak, and unowned references
- Retain cycles and how to detect them
- Breaking retain cycles
- Closures and capture lists
- Memory management best practices
- Comparing ARC to PHP's garbage collection

## Prerequisites

- Completed [Chapter 11: Error Handling](/series/swift-for-php-developers/chapters/11-error-handling)
- Understanding of classes and closures
- Knowledge of reference types vs value types

---

## PHP's Garbage Collection

PHP uses **garbage collection** to automatically free memory:

```php
<?php
class Person {
    public function __construct(public string $name) {
        echo "Creating {$this->name}\n";
    }

    public function __destruct() {
        echo "Destroying {$this->name}\n";
    }
}

function example() {
    $person = new Person("John");
    // Person is automatically freed when function ends
}

example();
// Output: Creating John, Destroying John
```

**PHP garbage collection:**
- Automatic (runs periodically)
- Reference counting + cycle detection
- Non-deterministic (you don't know exactly when objects are freed)
- Handles circular references automatically

---

## Swift's ARC (Automatic Reference Counting)

Swift uses **ARC** to manage memory:

```swift
class Person {
    let name: String

    init(name: String) {
        self.name = name
        print("Creating \(name)")
    }

    deinit {
        print("Destroying \(name)")
    }
}

func example() {
    let person = Person(name: "John")
    // Person is freed immediately when function ends
}

example()
// Output: Creating John, Destroying John
```

**Swift ARC:**
- Automatic (no manual memory management)
- Reference counting only (no cycle detection!)
- Deterministic (objects freed immediately when count reaches 0)
- **Cannot handle circular references automatically** (you must break them)

---

## How ARC Works

ARC tracks how many strong references point to each class instance.

### Reference Counting

```swift
class Person {
    let name: String

    init(name: String) {
        self.name = name
        print("Creating \(name)")
    }

    deinit {
        print("Destroying \(name)")
    }
}

var ref1: Person? = Person(name: "John")  // Reference count: 1
var ref2 = ref1                            // Reference count: 2
var ref3 = ref1                            // Reference count: 3

ref1 = nil  // Reference count: 2
ref2 = nil  // Reference count: 1
ref3 = nil  // Reference count: 0 -> deinit called
// Output: Destroying John
```

**Key Points:**
- Each strong reference increases the count
- Object is deallocated when count reaches 0
- `deinit` is called immediately

---

## Strong References (Default)

By default, all references are **strong**.

```swift
class Person {
    let name: String
    var apartment: Apartment?

    init(name: String) {
        self.name = name
    }

    deinit {
        print("\(name) is being deinitialized")
    }
}

class Apartment {
    let unit: String
    var tenant: Person?

    init(unit: String) {
        self.unit = unit
    }

    deinit {
        print("Apartment \(unit) is being deinitialized")
    }
}

var john: Person? = Person(name: "John")
var unit4A: Apartment? = Apartment(unit: "4A")

john?.apartment = unit4A
unit4A?.tenant = john

// Both still exist because references are strong
```

---

## Retain Cycles: The Problem

**Retain cycle** = Two objects hold strong references to each other, preventing deallocation.

```swift
class Person {
    let name: String
    var apartment: Apartment?

    init(name: String) {
        self.name = name
        print("Creating \(name)")
    }

    deinit {
        print("Destroying \(name)")
    }
}

class Apartment {
    let unit: String
    var tenant: Person?  // Strong reference to Person

    init(unit: String) {
        self.unit = unit
        print("Creating apartment \(unit)")
    }

    deinit {
        print("Destroying apartment \(unit)")
    }
}

var john: Person? = Person(name: "John")
var unit4A: Apartment? = Apartment(unit: "4A")

// Create retain cycle
john?.apartment = unit4A  // Person -> Apartment (strong)
unit4A?.tenant = john     // Apartment -> Person (strong)

// Try to free them
john = nil
unit4A = nil

// ❌ Memory leak! Neither deinit is called
// They're keeping each other alive
```

**Problem:** Person and Apartment reference each other, so reference count never reaches 0.

**PHP Comparison:**
```php
<?php
class Person {
    public ?Apartment $apartment = null;
}

class Apartment {
    public ?Person $tenant = null;
}

$john = new Person();
$unit4A = new Apartment();

$john->apartment = $unit4A;
$unit4A->tenant = $john;

unset($john);
unset($unit4A);

// ✅ PHP's garbage collector handles this automatically
```

---

## Weak References: Breaking Retain Cycles

Use `weak` for references that should **not** keep the object alive.

```swift
class Person {
    let name: String
    var apartment: Apartment?

    init(name: String) {
        self.name = name
        print("Creating \(name)")
    }

    deinit {
        print("Destroying \(name)")
    }
}

class Apartment {
    let unit: String
    weak var tenant: Person?  // ✅ Weak reference!

    init(unit: String) {
        self.unit = unit
        print("Creating apartment \(unit)")
    }

    deinit {
        print("Destroying apartment \(unit)")
    }
}

var john: Person? = Person(name: "John")
var unit4A: Apartment? = Apartment(unit: "4A")

john?.apartment = unit4A  // Person -> Apartment (strong)
unit4A?.tenant = john     // Apartment -> Person (weak!)

john = nil      // Person is deallocated (weak ref doesn't count)
                // Output: Destroying John

unit4A = nil    // Apartment is deallocated
                // Output: Destroying apartment 4A
```

**Weak references:**
- Do NOT increase reference count
- Automatically set to `nil` when object is deallocated
- Must be `var` (not `let`)
- Must be optional (`?`)

---

## Unowned References: Non-Optional Alternative

Use `unowned` when reference should **never** be `nil`.

```swift
class Customer {
    let name: String
    var card: CreditCard?

    init(name: String) {
        self.name = name
    }

    deinit {
        print("Destroying \(name)")
    }
}

class CreditCard {
    let number: String
    unowned let customer: Customer  // ✅ Unowned reference

    init(number: String, customer: Customer) {
        self.number = number
        self.customer = customer
    }

    deinit {
        print("Destroying card \(number)")
    }
}

var john: Customer? = Customer(name: "John")
john?.card = CreditCard(number: "1234-5678-9012-3456", customer: john!)

john = nil  // Both Customer and CreditCard are deallocated
// Output: Destroying John, Destroying card 1234-5678-9012-3456
```

**Unowned references:**
- Do NOT increase reference count
- NOT automatically set to `nil` (assumes object exists)
- Can be `let` (constant)
- NOT optional (no `?`)
- **Crashes if accessed after object is deallocated** (use carefully!)

---

## Weak vs Unowned: When to Use Which

| Use Case | Reference Type | Example |
|----------|---------------|---------|
| Reference may become `nil` | `weak var` | Delegates, optional parent references |
| Reference will never be `nil` during lifetime | `unowned let` | Child always has parent (CreditCard -> Customer) |
| Don't want to keep object alive | `weak` or `unowned` | Both work |
| Object might outlive referenced object | `weak` (safer) | Callbacks, closures |

**General Rule:** Use `weak` unless you're certain the reference will never be `nil`. `weak` is safer.

---

## Closures and Capture Lists

Closures capture references, which can create retain cycles.

### The Problem

```swift
class ViewController {
    var name = "MainViewController"

    func setupHandler() {
        // ❌ Retain cycle!
        // self captures ViewController, ViewController has closure
        someAsyncOperation {
            print(self.name)  // Strong capture of self
        }
    }

    deinit {
        print("ViewController deallocated")
    }
}

var controller: ViewController? = ViewController()
controller?.setupHandler()
controller = nil  // ❌ Not deallocated! Retain cycle
```

### Solution: Capture Lists

```swift
class ViewController {
    var name = "MainViewController"

    func setupHandler() {
        // ✅ Weak capture breaks retain cycle
        someAsyncOperation { [weak self] in
            guard let self = self else { return }
            print(self.name)
        }
    }

    deinit {
        print("ViewController deallocated")
    }
}

var controller: ViewController? = ViewController()
controller?.setupHandler()
controller = nil  // ✅ Deallocated! Output: ViewController deallocated
```

### Capture List Syntax

```swift
// Weak capture (most common)
{ [weak self] in
    guard let self = self else { return }
    print(self.name)
}

// Unowned capture (if you're certain object exists)
{ [unowned self] in
    print(self.name)
}

// Multiple captures
{ [weak self, weak delegate, unowned parent] in
    // ...
}

// Capture with renaming
{ [weak weakSelf = self] in
    guard let strongSelf = weakSelf else { return }
    print(strongSelf.name)
}
```

---

## Common Retain Cycle Patterns

### 1. Delegates

```swift
protocol TaskDelegate: AnyObject {
    func taskDidFinish()
}

class Task {
    weak var delegate: TaskDelegate?  // ✅ Weak!

    func start() {
        // Do work...
        delegate?.taskDidFinish()
    }
}

class Manager: TaskDelegate {
    var task: Task?

    init() {
        task = Task()
        task?.delegate = self  // No retain cycle
    }

    func taskDidFinish() {
        print("Task finished")
    }
}
```

**Rule:** Delegates should almost always be `weak`.

### 2. Parent-Child Relationships

```swift
class Parent {
    var children: [Child] = []

    deinit {
        print("Parent deallocated")
    }
}

class Child {
    weak var parent: Parent?  // ✅ Weak reference to parent

    deinit {
        print("Child deallocated")
    }
}

var parent: Parent? = Parent()
let child = Child()
child.parent = parent
parent?.children.append(child)

parent = nil  // ✅ Both deallocated
```

**Rule:** Child-to-parent references should be `weak`.

### 3. Closures in Classes

```swift
class DataLoader {
    var onComplete: (() -> Void)?

    func load() {
        // Simulate async operation
        DispatchQueue.main.asyncAfter(deadline: .now() + 1) { [weak self] in
            guard let self = self else { return }
            self.onComplete?()
        }
    }

    deinit {
        print("DataLoader deallocated")
    }
}

class ViewController {
    var loader: DataLoader?

    func loadData() {
        loader = DataLoader()

        // ✅ Weak capture prevents retain cycle
        loader?.onComplete = { [weak self] in
            guard let self = self else { return }
            print("Data loaded in \(self)")
        }

        loader?.load()
    }

    deinit {
        print("ViewController deallocated")
    }
}
```

---

## Debugging Memory Leaks

### 1. Instruments (Xcode Tool)

Use Xcode's Instruments to detect leaks:
- Product → Profile (⌘I)
- Select "Leaks" template
- Run app and perform actions
- Instruments shows leaked objects

### 2. Print Statements

Add `deinit` with print statements:

```swift
class MyClass {
    deinit {
        print("MyClass deallocated")  // Should see this
    }
}
```

If you don't see the message, you have a retain cycle.

### 3. Debug Memory Graph (Xcode)

- Run app
- Click Debug Memory Graph button in debug bar
- Shows all live objects and their references
- Purple exclamation marks = retain cycles

---

## Value Types Don't Have Retain Cycles

Structs and enums don't use reference counting.

```swift
struct Point {
    var x: Int
    var y: Int
}

// No retain cycles possible with value types
var p1 = Point(x: 10, y: 20)
var p2 = p1  // Copy, not reference

// No ARC needed for structs
```

**This is another reason to prefer structs over classes when possible!**

---

## Best Practices

### ✅ DO

```swift
// Use weak for delegates
weak var delegate: SomeDelegate?

// Use weak in closures that capture self
someOperation { [weak self] in
    guard let self = self else { return }
    // ...
}

// Use weak for parent references
weak var parent: Parent?

// Add deinit for debugging
deinit {
    print("Deallocated")
}

// Prefer structs (no ARC needed)
struct User {
    let name: String
}
```

### ❌ DON'T

```swift
// Don't create retain cycles
class Person {
    var bestFriend: Person?  // ❌ Can cause cycle
}

// Don't forget weak in closures
someOperation {
    print(self.name)  // ❌ Retain cycle if self owns closure
}

// Don't use unowned unless certain
unowned var parent: Parent?  // ❌ Crashes if parent deallocated

// Don't ignore memory warnings
// Always investigate if deinit isn't called
```

---

## PHP vs Swift Memory Management

| Aspect | PHP | Swift |
|--------|-----|-------|
| Memory management | Garbage collection | ARC (reference counting) |
| Circular references | Handled automatically | ❌ Manual weak/unowned needed |
| When objects freed | Non-deterministic | Deterministic (immediate) |
| Manual management | Not needed | weak/unowned needed |
| Value types | No distinction | No ARC needed |
| Memory leaks | Rare | Possible (retain cycles) |

---

## Practical Example: View Controller with Closure

```swift
class PhotoViewController {
    var imageURL: URL?
    var imageView: UIImageView?

    func loadImage() {
        guard let url = imageURL else { return }

        // ❌ BAD: Retain cycle
        /*
        ImageLoader.load(url: url) { image in
            self.imageView?.image = image  // Strong capture of self
        }
        */

        // ✅ GOOD: Weak capture
        ImageLoader.load(url: url) { [weak self] image in
            guard let self = self else { return }
            self.imageView?.image = image
        }
    }

    deinit {
        print("PhotoViewController deallocated")
    }
}

class ImageLoader {
    static func load(url: URL, completion: @escaping (UIImage?) -> Void) {
        // Simulate async loading
        DispatchQueue.global().async {
            // Load image...
            let image: UIImage? = nil  // Placeholder
            DispatchQueue.main.async {
                completion(image)
            }
        }
    }
}
```

---

## Summary

You've mastered Swift's memory management:

✅ **ARC** manages memory automatically via reference counting
✅ **Strong references** (default) increase reference count
✅ **Weak references** don't increase count, become `nil` when deallocated
✅ **Unowned references** don't increase count, assume object exists
✅ **Retain cycles** occur when objects reference each other
✅ **Capture lists** (`[weak self]`) prevent closure retain cycles
✅ **Delegates** should almost always be `weak`
✅ **Value types** don't need ARC
✅ **deinit** helps debug memory leaks

**Key Takeaway:** Unlike PHP's garbage collection, Swift's ARC cannot handle circular references automatically. You must use `weak` or `unowned` to break retain cycles. When in doubt, use `weak` for safety.

---

## What's Next?

In [Chapter 13: Closures and Functional Programming](/series/swift-for-php-developers/chapters/13-closures-functional-programming), you'll learn about closures in depth, including capturing values, escaping vs non-escaping closures, and functional programming patterns.

---

**Next Chapter:** [13 — Closures and Functional Programming](/series/swift-for-php-developers/chapters/13-closures-functional-programming)
