---
title: "Chapter 14: Extensions - Adding Functionality to Existing Types"
description: Master Swift extensions to add functionality to any type—even types you don't own like String, Int, or Array.
series: swift-for-php-developers
chapter: 14
difficulty: Intermediate
tags: ["extensions", "protocols", "computed-properties", "methods", "retroactive-modeling"]
---

# Chapter 14: Extensions: Adding Functionality to Existing Types

Extensions let you **add new functionality to existing types**—even types you don't own, like `String`, `Int`, or `Array`. This powerful feature goes far beyond PHP's traits and enables elegant, organized code.

This chapter shows you how to extend any type in Swift.

## What You'll Learn

- What extensions are
- Adding methods to existing types
- Adding computed properties
- Adding initializers
- Protocol conformance via extensions
- Extensions with constraints
- Organizing code with extensions
- Comparing to PHP traits
- Best practices

## Prerequisites

- Completed [Chapter 13: Closures](/series/swift-for-php-developers/chapters/13-closures-functional-programming)
- Understanding of protocols
- Knowledge of structs and classes

---

## PHP Traits: Quick Review

PHP uses traits to add functionality to classes:

```php
<?php
trait Loggable {
    public function log(string $message): void {
        echo "[LOG] $message\n";
    }
}

class User {
    use Loggable;

    public function save(): void {
        $this->log("Saving user");
    }
}

$user = new User();
$user->save();  // [LOG] Saving user
```

**PHP Traits:**
- Add methods and properties to classes
- Must be explicitly `use`d
- Can only extend classes you define
- Cannot extend built-in types

---

## Swift Extensions: More Powerful

Extensions add functionality to **any** type:

```swift
extension String {
    func shout() -> String {
        return self.uppercased() + "!"
    }
}

let message = "hello"
print(message.shout())  // "HELLO!"

// Works on all strings!
"swift".shout()  // "SWIFT!"
```

**Swift Extensions:**
- Add functionality to any type (even built-in types!)
- Automatic (no need to "use" them)
- Can extend types you don't own
- More flexible than PHP traits

---

## Extending Built-In Types

### Adding Methods to String

```swift
extension String {
    func isPalindrome() -> Bool {
        let cleaned = self.lowercased().filter { $0.isLetter }
        return cleaned == String(cleaned.reversed())
    }

    func truncate(length: Int, trailing: String = "...") -> String {
        if self.count > length {
            return String(self.prefix(length)) + trailing
        }
        return self
    }
}

print("racecar".isPalindrome())  // true
print("hello".isPalindrome())     // false

print("This is a long string".truncate(length: 10))
// "This is a ..."
```

**PHP Cannot Do This!** Cannot extend built-in string functions.

### Adding Methods to Int

```swift
extension Int {
    func squared() -> Int {
        return self * self
    }

    func times(_ block: () -> Void) {
        for _ in 0..<self {
            block()
        }
    }

    var isEven: Bool {
        return self % 2 == 0
    }

    var isOdd: Bool {
        return self % 2 != 0
    }
}

print(5.squared())  // 25

3.times {
    print("Hello")
}
// Prints "Hello" 3 times

print(4.isEven)  // true
print(5.isOdd)   // true
```

### Adding Methods to Array

```swift
extension Array where Element: Numeric {
    func sum() -> Element {
        return reduce(0, +)
    }

    func average() -> Double where Element == Int {
        return Double(sum()) / Double(count)
    }
}

let numbers = [1, 2, 3, 4, 5]
print(numbers.sum())      // 15
print(numbers.average())  // 3.0
```

---

## Adding Computed Properties

Extensions can add computed properties (not stored properties).

```swift
extension Double {
    var km: Double { return self * 1_000.0 }
    var m: Double { return self }
    var cm: Double { return self / 100.0 }
    var mm: Double { return self / 1_000.0 }
    var ft: Double { return self / 3.28084 }
}

let distance = 5.km  // 5000.0
let height = 6.ft    // ~1.83 meters

print(distance)  // 5000.0
print(height)    // 1.8288
```

**Cannot add stored properties:**
```swift
extension String {
    // ❌ Error: Extensions may not contain stored properties
    // var storedValue: Int = 0
}
```

---

## Adding Initializers

Extensions can add convenience initializers.

```swift
extension String {
    init(repeating character: Character, count: Int) {
        self = String(repeating: String(character), count: count)
    }
}

let stars = String(repeating: "*", count: 5)
print(stars)  // "*****"
```

---

## Protocol Conformance via Extensions

Add protocol conformance to existing types.

```swift
protocol Describable {
    var description: String { get }
}

extension Int: Describable {
    var description: String {
        return "The number \(self)"
    }
}

let number = 42
print(number.description)  // "The number 42"
```

**Retroactive Modeling:** Add protocol conformance to types you don't own!

```swift
extension Array: Describable {
    var description: String {
        return "Array with \(count) elements"
    }
}

let items = [1, 2, 3]
print(items.description)  // "Array with 3 elements"
```

---

## Extending Your Own Types

Organize code by functionality using extensions.

```swift
struct User {
    let name: String
    let email: String
    var age: Int
}

// Validation logic in separate extension
extension User {
    func isValid() -> Bool {
        return !name.isEmpty && email.contains("@") && age >= 0
    }
}

// Formatting logic in separate extension
extension User {
    var displayName: String {
        return name.capitalized
    }

    func formattedAge() -> String {
        return "\(age) years old"
    }
}

// Comparison logic
extension User: Equatable {
    static func == (lhs: User, rhs: User) -> Bool {
        return lhs.email == rhs.email
    }
}

let user = User(name: "john", email: "john@example.com", age: 30)
print(user.isValid())         // true
print(user.displayName)       // "John"
print(user.formattedAge())    // "30 years old"
```

---

## Extensions with Constraints

Add functionality only when type meets certain conditions.

```swift
// Only for arrays of Equatable elements
extension Array where Element: Equatable {
    func removingDuplicates() -> [Element] {
        var result: [Element] = []
        for item in self {
            if !result.contains(item) {
                result.append(item)
            }
        }
        return result
    }
}

let numbers = [1, 2, 2, 3, 3, 3, 4, 5, 5]
print(numbers.removingDuplicates())  // [1, 2, 3, 4, 5]

// Only for arrays of Comparable elements
extension Array where Element: Comparable {
    func isSorted() -> Bool {
        for i in 1..<count {
            if self[i] < self[i - 1] {
                return false
            }
        }
        return true
    }
}

print([1, 2, 3, 4].isSorted())  // true
print([1, 3, 2, 4].isSorted())  // false
```

---

## Protocol Extensions

Provide default implementations for protocols.

```swift
protocol Greetable {
    var name: String { get }
    func greet()
}

// Default implementation
extension Greetable {
    func greet() {
        print("Hello, \(name)!")
    }

    func greetFormal() {
        print("Good day, \(name).")
    }
}

struct Person: Greetable {
    let name: String
    // Gets greet() and greetFormal() for free!
}

let person = Person(name: "John")
person.greet()        // "Hello, John!"
person.greetFormal()  // "Good day, John."
```

---

## Practical Examples

### 1. String Validation

```swift
extension String {
    var isValidEmail: Bool {
        let emailRegex = "[A-Z0-9a-z._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,64}"
        let emailPredicate = NSPredicate(format:"SELF MATCHES %@", emailRegex)
        return emailPredicate.evaluate(with: self)
    }

    var isNumeric: Bool {
        return Double(self) != nil
    }

    func trimmed() -> String {
        return self.trimmingCharacters(in: .whitespacesAndNewlines)
    }
}

print("john@example.com".isValidEmail)  // true
print("invalid".isValidEmail)           // false
print("123".isNumeric)                  // true
print("  hello  ".trimmed())            // "hello"
```

### 2. Date Formatting

```swift
extension Date {
    func formatted(as format: String) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = format
        return formatter.string(from: self)
    }

    var isToday: Bool {
        return Calendar.current.isDateInToday(self)
    }

    var isYesterday: Bool {
        return Calendar.current.isDateInYesterday(self)
    }
}

let now = Date()
print(now.formatted(as: "yyyy-MM-dd"))  // "2025-11-15"
print(now.isToday)                      // true
```

### 3. Collection Utilities

```swift
extension Collection {
    var isNotEmpty: Bool {
        return !isEmpty
    }

    func chunked(into size: Int) -> [[Element]] {
        var chunks: [[Element]] = []
        var currentChunk: [Element] = []

        for element in self {
            currentChunk.append(element)
            if currentChunk.count == size {
                chunks.append(currentChunk)
                currentChunk = []
            }
        }

        if !currentChunk.isEmpty {
            chunks.append(currentChunk)
        }

        return chunks
    }
}

let numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9]
print(numbers.chunked(into: 3))
// [[1, 2, 3], [4, 5, 6], [7, 8, 9]]

print([].isNotEmpty)      // false
print([1, 2].isNotEmpty)  // true
```

### 4. Optional Extensions

```swift
extension Optional {
    var isNil: Bool {
        return self == nil
    }

    var isNotNil: Bool {
        return self != nil
    }
}

let name: String? = "John"
let age: Int? = nil

print(name.isNotNil)  // true
print(age.isNil)      // true
```

---

## Organizing Code with Extensions

### Separate Files by Functionality

```swift
// User.swift - Core definition
struct User {
    let id: Int
    let name: String
    let email: String
}

// User+Validation.swift
extension User {
    func isValid() -> Bool {
        return !name.isEmpty && email.contains("@")
    }
}

// User+Networking.swift
extension User {
    static func fetch(id: Int, completion: @escaping (User?) -> Void) {
        // Network logic
    }
}

// User+Equatable.swift
extension User: Equatable {
    static func == (lhs: User, rhs: User) -> Bool {
        return lhs.id == rhs.id
    }
}
```

### Mark: Organize in Same File

```swift
struct User {
    let name: String
}

// MARK: - Validation
extension User {
    func isValid() -> Bool {
        return !name.isEmpty
    }
}

// MARK: - Formatting
extension User {
    var displayName: String {
        return name.capitalized
    }
}

// MARK: - Equatable
extension User: Equatable {}
```

---

## Extensions vs Inheritance

| Feature | Extension | Inheritance |
|---------|-----------|-------------|
| Add to any type | ✅ Yes | ❌ No (only your classes) |
| Add to structs/enums | ✅ Yes | ❌ No |
| Add stored properties | ❌ No | ✅ Yes |
| Add computed properties | ✅ Yes | ✅ Yes |
| Add methods | ✅ Yes | ✅ Yes |
| Override methods | ❌ No | ✅ Yes |
| Multiple inheritance | ✅ Yes (via protocols) | ❌ No |

---

## Best Practices

### ✅ DO

```swift
// Extend built-in types for convenience
extension String {
    func trimmed() -> String {
        return trimmingCharacters(in: .whitespacesAndNewlines)
    }
}

// Use extensions to organize code
extension User {
    // Validation logic
}

// Add protocol conformance via extension
extension User: Codable {}

// Use constraints when appropriate
extension Array where Element: Equatable {
    func removeDuplicates() -> [Element] { ... }
}

// Provide default protocol implementations
extension Collection {
    var isNotEmpty: Bool { !isEmpty }
}
```

### ❌ DON'T

```swift
// Don't add too much functionality
extension Int {
    // ❌ Too many methods makes Int confusing
    func method1() {}
    func method2() {}
    // ... 20 more methods
}

// Don't replace proper inheritance
// ❌ Use inheritance for this
extension UIViewController {
    // Complex base functionality
}

// Don't add unrelated functionality
extension String {
    // ❌ String shouldn't have networking logic
    func fetchFromServer() {}
}

// Don't try to add stored properties
extension User {
    // ❌ Error: Cannot add stored properties
    // var storedValue: Int = 0
}
```

---

## PHP vs Swift Comparison

| Feature | PHP Traits | Swift Extensions |
|---------|-----------|------------------|
| Extend own classes | ✅ Yes | ✅ Yes |
| Extend built-in types | ❌ No | ✅ Yes |
| Explicit usage | ✅ `use Trait` | ❌ Automatic |
| Add stored properties | ✅ Yes | ❌ No |
| Add methods | ✅ Yes | ✅ Yes |
| Add to structs/enums | ❌ N/A | ✅ Yes |
| Protocol conformance | ❌ No | ✅ Yes |
| Constrained extensions | ❌ No | ✅ Yes |

---

## Summary

You've mastered Swift extensions:

✅ **Extensions** add functionality to any type
✅ **Extend built-in types** like String, Int, Array
✅ **Computed properties** can be added
✅ **Protocol conformance** via extensions (retroactive modeling)
✅ **Constrained extensions** add functionality conditionally
✅ **Protocol extensions** provide default implementations
✅ **Organize code** by separating concerns
✅ **More powerful** than PHP traits

**Key Takeaway:** Extensions are one of Swift's most powerful features. They let you extend any type—even types you don't own—making code more expressive and organized. This goes far beyond what PHP traits can do.

---

## Congratulations! 🎉

You've completed **Part 3: Memory and Advanced!**

You've learned:
- ARC and memory management (weak, unowned, retain cycles)
- Closures and functional programming (map, filter, reduce)
- Extensions (adding functionality to any type)

## What's Next?

In [Chapter 15: Introduction to SwiftUI](/series/swift-for-php-developers/chapters/15-swiftui-introduction), you'll begin **Part 4: iOS Development** and learn Swift's modern declarative UI framework.

---

**Next Chapter:** [15 — Introduction to SwiftUI](/series/swift-for-php-developers/chapters/15-swiftui-introduction)
