---
title: "Appendix B: Swift Standard Library Reference"
description: Essential Swift types, protocols, and functions with PHP equivalents.
series: swift-for-php-developers
appendix: B
tags: ["reference", "standard-library", "protocols", "types"]
---

# Appendix B: Swift Standard Library Reference

A comprehensive guide to Swift's standard library with PHP comparisons.

## Core Protocols

### Equatable
Enables `==` comparison

```swift
struct User: Equatable {
    let id: Int
    let name: String
}

let user1 = User(id: 1, name: "John")
let user2 = User(id: 1, name: "John")
print(user1 == user2)  // true
```

**PHP Equivalent:** Implementing `__equals()` or manual comparison

### Hashable
Enables use in Sets and Dictionary keys

```swift
struct User: Hashable {
    let id: Int
    let name: String
}

let users: Set<User> = [user1, user2]
let userDict: [User: String] = [user1: "Admin"]
```

**PHP Equivalent:** Using objects as array keys (with spl_object_hash)

### Comparable
Enables `<`, `>`, `<=`, `>=` comparison

```swift
struct Version: Comparable {
    let major: Int
    let minor: Int

    static func < (lhs: Version, rhs: Version) -> Bool {
        if lhs.major != rhs.major {
            return lhs.major < rhs.major
        }
        return lhs.minor < rhs.minor
    }
}
```

**PHP Equivalent:** Implementing comparison methods or using `<=>` operator

### Codable (Encodable + Decodable)
JSON serialization/deserialization

```swift
struct User: Codable {
    let id: Int
    let name: String
}

// Encode to JSON
let user = User(id: 1, name: "John")
let jsonData = try JSONEncoder().encode(user)

// Decode from JSON
let decoded = try JSONDecoder().decode(User.self, from: jsonData)
```

**PHP Equivalent:** `json_encode()` and `json_decode()`

## Collection Types

### Array
Ordered collection

```swift
var numbers = [1, 2, 3, 4, 5]

// Common operations
numbers.append(6)
numbers.insert(0, at: 0)
numbers.remove(at: 0)
let first = numbers.first  // Optional
let last = numbers.last    // Optional
let count = numbers.count
```

**PHP Equivalent:** Indexed arrays

### Dictionary
Key-value pairs

```swift
var ages: [String: Int] = ["John": 30, "Jane": 25]

// Common operations
ages["Bob"] = 35
ages["John"] = 31
let johnAge = ages["John"]  // Optional<Int>
let keys = ages.keys
let values = ages.values
```

**PHP Equivalent:** Associative arrays

### Set
Unordered unique values

```swift
var uniqueNumbers: Set<Int> = [1, 2, 3, 4, 5]

uniqueNumbers.insert(6)
uniqueNumbers.remove(1)
let contains = uniqueNumbers.contains(3)

// Set operations
let odds: Set = [1, 3, 5]
let evens: Set = [2, 4, 6]
let union = odds.union(evens)
```

**PHP Equivalent:** Using array_unique() and array operations

## String

```swift
let str = "Hello, World!"

// Common operations
str.count                                    // 13
str.uppercased()                            // "HELLO, WORLD!"
str.lowercased()                            // "hello, world!"
str.contains("World")                       // true
str.hasPrefix("Hello")                      // true
str.hasSuffix("!")                          // true
str.replacingOccurrences(of: ",", with: "") // "Hello World!"

// String interpolation
let name = "John"
let greeting = "Hello, \(name)!"  // "Hello, John!"
```

**PHP Equivalents:**
- `strlen()` → `str.count`
- `strtoupper()` → `str.uppercased()`
- `strtolower()` → `str.lowercased()`
- `strpos()` → `str.contains()`
- `str_replace()` → `str.replacingOccurrences()`

## Optionals

```swift
let optional: String? = "value"

// Optional binding
if let value = optional {
    print(value)
}

// Optional chaining
let length = optional?.count

// Nil coalescing
let result = optional ?? "default"

// Force unwrap (avoid!)
let forced = optional!
```

**PHP Equivalent:** Nullable types and null coalescing operator

## Result Type

```swift
enum NetworkError: Error {
    case invalidURL
    case noData
}

func fetchData() -> Result<Data, NetworkError> {
    // Returns either .success(data) or .failure(error)
}

let result = fetchData()
switch result {
case .success(let data):
    print("Got data: \(data)")
case .failure(let error):
    print("Error: \(error)")
}
```

**PHP Equivalent:** Exceptions, but Result type avoids throwing

## Higher-Order Functions

### map
Transform each element

```swift
let numbers = [1, 2, 3, 4, 5]
let doubled = numbers.map { $0 * 2 }  // [2, 4, 6, 8, 10]
```

**PHP:** `array_map(fn($n) => $n * 2, $numbers)`

### filter
Keep elements matching condition

```swift
let numbers = [1, 2, 3, 4, 5]
let evens = numbers.filter { $0 % 2 == 0 }  // [2, 4]
```

**PHP:** `array_filter($numbers, fn($n) => $n % 2 === 0)`

### reduce
Combine elements into single value

```swift
let numbers = [1, 2, 3, 4, 5]
let sum = numbers.reduce(0) { $0 + $1 }  // 15
// or: let sum = numbers.reduce(0, +)
```

**PHP:** `array_reduce($numbers, fn($carry, $n) => $carry + $n, 0)`

### compactMap
Map and remove nils

```swift
let strings = ["1", "2", "three", "4"]
let numbers = strings.compactMap { Int($0) }  // [1, 2, 4]
```

**PHP:** `array_filter(array_map(...))`

### flatMap
Map and flatten

```swift
let nested = [[1, 2], [3, 4], [5]]
let flat = nested.flatMap { $0 }  // [1, 2, 3, 4, 5]
```

**PHP:** `array_merge(...$nested)`

## Date and Time

```swift
import Foundation

let now = Date()
let calendar = Calendar.current

// Components
let year = calendar.component(.year, from: now)
let month = calendar.component(.month, from: now)

// Date formatting
let formatter = DateFormatter()
formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
let dateString = formatter.string(from: now)

// Date arithmetic
let tomorrow = calendar.date(byAdding: .day, value: 1, to: now)
```

**PHP Equivalents:**
- `Date()` → `new DateTime()`
- `Calendar` → `DateTime` methods
- `DateFormatter` → `DateTime::format()`

## Math Operations

```swift
import Foundation

let x = 4.0
let y = 2.0

// Basic
let sum = x + y
let difference = x - y
let product = x * y
let quotient = x / y

// Math functions
let power = pow(x, y)      // x^y
let squareRoot = sqrt(x)   // √x
let absolute = abs(-5)     // 5
let rounded = round(4.6)   // 5.0
let ceiling = ceil(4.2)    // 5.0
let floor = floor(4.8)     // 4.0

// Min/Max
let minimum = min(x, y)
let maximum = max(x, y)

// Random
let randomInt = Int.random(in: 1...100)
let randomDouble = Double.random(in: 0.0...1.0)
```

**PHP Equivalents:** Same function names mostly (`pow`, `sqrt`, `abs`, `round`, `ceil`, `floor`, `min`, `max`, `rand`)

## More to Come

This appendix will be expanded with:
- Complete protocol reference
- All collection types and methods
- File I/O operations
- Network types
- Concurrency primitives (Task, Actor)
- And more...

**See also:**
- [Swift Standard Library Documentation](https://developer.apple.com/documentation/swift/swift_standard_library)
- [Appendix A: PHP to Swift Quick Reference](/series/swift-for-php-developers/appendices/a-php-swift-quick-reference)
