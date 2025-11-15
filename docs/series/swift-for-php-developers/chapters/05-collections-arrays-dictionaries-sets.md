---
title: "Chapter 05: Collections - Arrays, Dictionaries, and Sets"
description: Master Swift's strongly-typed collections and understand how they differ from PHP's flexible arrays.
series: swift-for-php-developers
chapter: 5
difficulty: Beginner
tags: ["collections", "arrays", "dictionaries", "sets", "generics"]
---

# Chapter 05: Collections: Arrays, Dictionaries, and Sets

PHP has one incredibly flexible data structure: the array. It can be indexed, associative, or mixed. Swift takes a different approach with three distinct, strongly-typed collection types. This chapter teaches you to think in Swift collections.

## What You'll Learn

- Swift's three collection types vs PHP arrays
- Arrays: Ordered collections
- Dictionaries: Key-value pairs
- Sets: Unique unordered values
- Collection operations (map, filter, reduce)
- Value semantics and copy-on-write
- When to use each collection type

## Prerequisites

- Completed [Chapter 04: Optionals](/series/swift-for-php-developers/chapters/04-optionals-null-safety)
- Understanding of PHP arrays
- Basic knowledge of generics

---

## PHP Arrays: One Size Fits All

PHP has a single `array` type that can be anything:

```php
<?php
// Indexed array (list)
$numbers = [1, 2, 3, 4, 5];

// Associative array (map)
$ages = ['John' => 30, 'Jane' => 25];

// Mixed (both!)
$mixed = [
    0 => 'first',
    'key' => 'second',
    1 => 'third'
];

// Nested arrays
$users = [
    ['id' => 1, 'name' => 'John'],
    ['id' => 2, 'name' => 'Jane']
];
```

**Problem:** Type safety issues. You don't know what's in an array without checking at runtime.

---

## Swift Collections: Three Distinct Types

Swift provides three specialized collection types:

1. **Array** - Ordered collection of values
2. **Dictionary** - Unordered key-value pairs
3. **Set** - Unordered collection of unique values

All are **strongly typed** and **generic**.

---

## Arrays: Ordered Collections

### Creating Arrays

```swift
// Type inference
let numbers = [1, 2, 3, 4, 5]  // [Int]
let names = ["John", "Jane", "Bob"]  // [String]

// Explicit type
let numbers: [Int] = [1, 2, 3]
let names: Array<String> = ["John", "Jane"]

// Empty array (needs type)
var items: [String] = []
var items = [String]()

// Array with default values
let zeros = Array(repeating: 0, count: 5)  // [0, 0, 0, 0, 0]
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];
$names = ['John', 'Jane', 'Bob'];
$items = [];
```

### Accessing Elements

```swift
let names = ["John", "Jane", "Bob"]

// By index
let first = names[0]  // "John"
let last = names[2]   // "Bob"

// Safe access (returns Optional)
let first = names.first  // Optional("John")
let last = names.last    // Optional("Bob")

// Out of bounds crashes!
// let invalid = names[10]  // 💥 Runtime error

// Count
let count = names.count  // 3

// Check if empty
names.isEmpty  // false
```

**PHP Comparison:**
```php
<?php
$names = ['John', 'Jane', 'Bob'];

$first = $names[0];  // "John"
$last = $names[2];   // "Bob"

// Out of bounds gives warning, returns null
$invalid = $names[10] ?? 'default';

$count = count($names);
$isEmpty = empty($names);
```

### Modifying Arrays

```swift
var numbers = [1, 2, 3]

// Append
numbers.append(4)        // [1, 2, 3, 4]
numbers += [5, 6]        // [1, 2, 3, 4, 5, 6]

// Insert at index
numbers.insert(0, at: 0)  // [0, 1, 2, 3, 4, 5, 6]

// Remove
numbers.remove(at: 0)     // [1, 2, 3, 4, 5, 6]
numbers.removeLast()      // [1, 2, 3, 4, 5]
numbers.removeFirst()     // [2, 3, 4, 5]
numbers.removeAll()       // []

// Replace
numbers = [1, 2, 3]
numbers[1] = 99          // [1, 99, 3]
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3];

// Append
$numbers[] = 4;
array_push($numbers, 5, 6);

// Insert at index
array_splice($numbers, 0, 0, [0]);

// Remove
array_shift($numbers);    // Remove first
array_pop($numbers);      // Remove last
unset($numbers[1]);       // Remove at index

// Replace
$numbers[1] = 99;
```

### Iterating Arrays

```swift
let names = ["John", "Jane", "Bob"]

// For-in loop
for name in names {
    print(name)
}

// With index
for (index, name) in names.enumerated() {
    print("\(index): \(name)")
}

// Iterate indices
for i in 0..<names.count {
    print(names[i])
}

// Iterate with indices
for i in names.indices {
    print(names[i])
}
```

**PHP Comparison:**
```php
<?php
$names = ['John', 'Jane', 'Bob'];

// Foreach
foreach ($names as $name) {
    echo $name;
}

// With index
foreach ($names as $index => $name) {
    echo "$index: $name";
}

// For loop
for ($i = 0; $i < count($names); $i++) {
    echo $names[$i];
}
```

---

## Dictionaries: Key-Value Pairs

### Creating Dictionaries

```swift
// Type inference
let ages = ["John": 30, "Jane": 25, "Bob": 35]  // [String: Int]

// Explicit type
let ages: [String: Int] = ["John": 30, "Jane": 25]
let ages: Dictionary<String, Int> = ["John": 30]

// Empty dictionary
var scores: [String: Int] = [:]
var scores = [String: Int]()
```

**PHP Comparison:**
```php
<?php
// Associative array
$ages = ['John' => 30, 'Jane' => 25, 'Bob' => 35];
```

### Accessing Values

```swift
let ages = ["John": 30, "Jane": 25]

// By key (returns Optional!)
let johnAge = ages["John"]  // Optional(30)
let bobAge = ages["Bob"]    // nil (doesn't exist)

// With default
let age = ages["Bob"] ?? 0  // 0

// Unwrap safely
if let age = ages["John"] {
    print("John is \(age)")
}

// Count
ages.count  // 2

// Keys and values
let keys = Array(ages.keys)      // ["John", "Jane"]
let values = Array(ages.values)  // [30, 25]
```

**PHP Comparison:**
```php
<?php
$ages = ['John' => 30, 'Jane' => 25];

$johnAge = $ages['John'];  // 30
$bobAge = $ages['Bob'] ?? 0;  // 0 (doesn't exist)

$count = count($ages);
$keys = array_keys($ages);
$values = array_values($ages);
```

### Modifying Dictionaries

```swift
var ages = ["John": 30, "Jane": 25]

// Add/Update
ages["Bob"] = 35       // Add new
ages["John"] = 31      // Update existing

// Update with old value
let oldAge = ages.updateValue(32, forKey: "John")  // Optional(31)

// Remove
ages["Jane"] = nil     // Remove by setting to nil
ages.removeValue(forKey: "Bob")  // Returns Optional old value

// Remove all
ages.removeAll()
```

**PHP Comparison:**
```php
<?php
$ages = ['John' => 30, 'Jane' => 25];

// Add/Update
$ages['Bob'] = 35;
$ages['John'] = 31;

// Remove
unset($ages['Jane']);

// Remove all
$ages = [];
```

### Iterating Dictionaries

```swift
let ages = ["John": 30, "Jane": 25, "Bob": 35]

// For-in with tuple
for (name, age) in ages {
    print("\(name) is \(age)")
}

// Keys only
for name in ages.keys {
    print(name)
}

// Values only
for age in ages.values {
    print(age)
}
```

**PHP Comparison:**
```php
<?php
$ages = ['John' => 30, 'Jane' => 25, 'Bob' => 35];

foreach ($ages as $name => $age) {
    echo "$name is $age";
}

foreach (array_keys($ages) as $name) {
    echo $name;
}

foreach (array_values($ages) as $age) {
    echo $age;
}
```

---

## Sets: Unique Unordered Values

Sets store unique values in no particular order. PHP doesn't have a native Set type.

### Creating Sets

```swift
// From array literal
let numbers: Set<Int> = [1, 2, 3, 4, 5]
let names: Set = ["John", "Jane", "Bob"]  // Type inferred

// Duplicates automatically removed
let unique: Set = [1, 2, 2, 3, 3, 3]  // {1, 2, 3}

// Empty set
var items = Set<String>()
```

**PHP Comparison:**
```php
<?php
// Use array_unique() for uniqueness
$numbers = [1, 2, 3, 4, 5];
$unique = array_unique([1, 2, 2, 3, 3, 3]);  // [1, 2, 3]
```

### Set Operations

```swift
let evens: Set = [2, 4, 6, 8]
let odds: Set = [1, 3, 5, 7]
let primes: Set = [2, 3, 5, 7]

// Union (combine all)
evens.union(odds)  // {1, 2, 3, 4, 5, 6, 7, 8}

// Intersection (common elements)
evens.intersection(primes)  // {2}

// Subtraction (remove elements)
evens.subtracting(primes)  // {4, 6, 8}

// Symmetric difference (unique to each)
evens.symmetricDifference(primes)  // {3, 4, 5, 6, 7, 8}

// Check membership
primes.contains(2)  // true
primes.contains(4)  // false

// Subset/Superset
let smallPrimes: Set = [2, 3]
smallPrimes.isSubset(of: primes)     // true
primes.isSuperset(of: smallPrimes)   // true
```

**PHP Comparison:**
```php
<?php
$evens = [2, 4, 6, 8];
$odds = [1, 3, 5, 7];
$primes = [2, 3, 5, 7];

// Union
$union = array_unique(array_merge($evens, $odds));

// Intersection
$intersection = array_intersect($evens, $primes);

// Difference
$difference = array_diff($evens, $primes);

// Check membership
in_array(2, $primes);  // true
```

### Modifying Sets

```swift
var fruits: Set = ["Apple", "Banana", "Orange"]

// Insert
fruits.insert("Mango")  // Returns (inserted: true, memberAfterInsert: "Mango")
fruits.insert("Apple")  // Returns (inserted: false, ...) - already exists

// Remove
fruits.remove("Banana")  // Returns Optional("Banana")
fruits.remove("Grape")   // Returns nil (doesn't exist)

// Remove all
fruits.removeAll()
```

---

## Higher-Order Functions

These work on all collection types and are incredibly powerful!

### Map: Transform Elements

```swift
let numbers = [1, 2, 3, 4, 5]

// Double each number
let doubled = numbers.map { $0 * 2 }  // [2, 4, 6, 8, 10]

// Convert to strings
let strings = numbers.map { String($0) }  // ["1", "2", "3", "4", "5"]

// Transform objects
struct User {
    let name: String
}
let users = [User(name: "John"), User(name: "Jane")]
let names = users.map { $0.name }  // ["John", "Jane"]
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];

$doubled = array_map(fn($n) => $n * 2, $numbers);
$strings = array_map(fn($n) => (string)$n, $numbers);
```

### Filter: Select Elements

```swift
let numbers = [1, 2, 3, 4, 5, 6]

// Even numbers only
let evens = numbers.filter { $0 % 2 == 0 }  // [2, 4, 6]

// Numbers > 3
let large = numbers.filter { $0 > 3 }  // [4, 5, 6]

// Filter objects
let users = [
    User(name: "John", age: 30),
    User(name: "Jane", age: 25)
]
let adults = users.filter { $0.age >= 18 }
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3, 4, 5, 6];

$evens = array_filter($numbers, fn($n) => $n % 2 === 0);
$large = array_filter($numbers, fn($n) => $n > 3);
```

### Reduce: Combine Elements

```swift
let numbers = [1, 2, 3, 4, 5]

// Sum
let sum = numbers.reduce(0, +)  // 15
let sum = numbers.reduce(0) { $0 + $1 }  // Same

// Product
let product = numbers.reduce(1, *)  // 120

// Concatenate strings
let words = ["Hello", "World", "Swift"]
let sentence = words.reduce("") { $0 + " " + $1 }  // " Hello World Swift"
let sentence = words.joined(separator: " ")  // Better: "Hello World Swift"
```

**PHP Comparison:**
```php
<?php
$numbers = [1, 2, 3, 4, 5];

$sum = array_reduce($numbers, fn($carry, $n) => $carry + $n, 0);
$product = array_reduce($numbers, fn($carry, $n) => $carry * $n, 1);

$words = ['Hello', 'World', 'PHP'];
$sentence = implode(' ', $words);
```

### CompactMap: Map and Remove Nils

```swift
let strings = ["1", "2", "three", "4", "five"]

// Convert to Int (fails for non-numbers)
let numbers = strings.compactMap { Int($0) }  // [1, 2, 4]

// Regular map would give [Optional(1), Optional(2), nil, Optional(4), nil]
```

**PHP Comparison:**
```php
<?php
$strings = ['1', '2', 'three', '4', 'five'];

$numbers = array_filter(
    array_map(fn($s) => is_numeric($s) ? (int)$s : null, $strings),
    fn($n) => $n !== null
);
```

### FlatMap: Flatten Nested Collections

```swift
let nested = [[1, 2], [3, 4], [5]]
let flat = nested.flatMap { $0 }  // [1, 2, 3, 4, 5]

// With transformation
let numbers = [1, 2, 3]
let duplicated = numbers.flatMap { [$0, $0] }  // [1, 1, 2, 2, 3, 3]
```

**PHP Comparison:**
```php
<?php
$nested = [[1, 2], [3, 4], [5]];
$flat = array_merge(...$nested);  // [1, 2, 3, 4, 5]
```

---

## Value Semantics and Copy-on-Write

**Critical concept:** Swift collections are value types!

```swift
// Arrays copy on assignment
var original = [1, 2, 3]
var copy = original  // Copies the array

copy.append(4)

print(original)  // [1, 2, 3] - Not modified!
print(copy)      // [1, 2, 3, 4] - Modified
```

**But** Swift optimizes with copy-on-write:

```swift
var array1 = [1, 2, 3]  // Allocates memory
var array2 = array1     // Shares memory (no copy yet)

// Only copies when modified
array2.append(4)        // NOW it copies
```

**PHP Comparison:**
```php
<?php
// Arrays copy on assignment too
$original = [1, 2, 3];
$copy = $original;

$copy[] = 4;

print_r($original);  // [1, 2, 3] - Not modified
print_r($copy);      // [1, 2, 3, 4] - Modified

// For references, use &
$ref = &$original;
$ref[] = 5;
print_r($original);  // [1, 2, 3, 5] - Modified!
```

---

## Choosing the Right Collection

| Use Case | Collection | Why |
|----------|------------|-----|
| Ordered list of items | `Array` | Maintains order, allows duplicates |
| Key-value lookup | `Dictionary` | Fast O(1) lookup by key |
| Unique items, no order | `Set` | Guarantees uniqueness, fast membership test |
| Mathematical set operations | `Set` | Union, intersection, etc. |
| Frequent insertions at beginning | `Array` (consider deque) | Or use collections from Foundation |

---

## Practical Example: Shopping Cart

```swift
struct Product {
    let id: Int
    let name: String
    let price: Double
}

struct ShoppingCart {
    private var items: [Int: Product] = [:]  // [ProductID: Product]
    private var quantities: [Int: Int] = [:]  // [ProductID: Quantity]

    mutating func add(_ product: Product, quantity: Int = 1) {
        items[product.id] = product
        quantities[product.id, default: 0] += quantity
    }

    mutating func remove(_ productID: Int) {
        items.removeValue(forKey: productID)
        quantities.removeValue(forKey: productID)
    }

    func total() -> Double {
        items.reduce(0) { sum, item in
            let (id, product) = item
            let quantity = quantities[id] ?? 0
            return sum + (product.price * Double(quantity))
        }
    }

    var itemCount: Int {
        quantities.values.reduce(0, +)
    }
}

// Usage
var cart = ShoppingCart()
cart.add(Product(id: 1, name: "Swift Book", price: 49.99), quantity: 2)
cart.add(Product(id: 2, name: "iPhone", price: 999.99))

print("Total: $\(cart.total())")  // Total: $1099.97
print("Items: \(cart.itemCount)")  // Items: 3
```

---

## Hands-On Exercise

Create a function that:
1. Takes an array of integers
2. Filters out negative numbers
3. Doubles the remaining numbers
4. Returns the sum

**Solution:**

```swift
func processNumbers(_ numbers: [Int]) -> Int {
    return numbers
        .filter { $0 >= 0 }        // Keep positive
        .map { $0 * 2 }            // Double them
        .reduce(0, +)              // Sum
}

// Usage
let numbers = [-1, 2, -3, 4, 5, -6]
let result = processNumbers(numbers)  // 22 (2*2 + 4*2 + 5*2)

// Or in one line
let result = numbers.filter { $0 >= 0 }.map { $0 * 2 }.reduce(0, +)
```

**PHP Comparison:**
```php
<?php
function processNumbers(array $numbers): int {
    return array_reduce(
        array_map(
            fn($n) => $n * 2,
            array_filter($numbers, fn($n) => $n >= 0)
        ),
        fn($carry, $n) => $carry + $n,
        0
    );
}

$numbers = [-1, 2, -3, 4, 5, -6];
$result = processNumbers($numbers);  // 22
```

---

## Summary

You've mastered Swift collections:

✅ **Array** for ordered lists
✅ **Dictionary** for key-value pairs
✅ **Set** for unique values
✅ **map, filter, reduce** for functional transformations
✅ **Value semantics** with copy-on-write optimization
✅ **Type safety** throughout

**Key Takeaway:** Swift's three distinct collection types replace PHP's single array type. Choose the right collection for your use case, and leverage the type system for safety.

---

## What's Next?

In [Chapter 06: Classes and Structs](/series/swift-for-php-developers/chapters/06-classes-structs-value-reference-types), you'll learn the fundamental difference between value types (structs) and reference types (classes)—one of the most important concepts in Swift.

---

**Next Chapter:** [06 — Classes and Structs: Reference vs Value Types](/series/swift-for-php-developers/chapters/06-classes-structs-value-reference-types)
