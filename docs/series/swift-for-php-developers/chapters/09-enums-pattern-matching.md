---
title: "Chapter 09: Enums and Pattern Matching - Beyond PHP's Backed Enums"
description: Discover Swift's incredibly powerful enums with associated values and pattern matching—far beyond PHP's simple backed enums.
series: swift-for-php-developers
chapter: 9
difficulty: Intermediate
tags: ["enums", "pattern-matching", "switch", "associated-values", "algebraic-types"]
---

# Chapter 09: Enums and Pattern Matching: Beyond PHP's Backed Enums

Swift's enums are **wildly more powerful** than PHP's backed enums. They can store associated data, have methods, use pattern matching, and even be recursive. This chapter introduces you to one of Swift's most expressive features.

## What You'll Learn

- Enums vs PHP's backed enums (PHP 8.1+)
- Basic enum syntax
- Associated values (store data with cases)
- Raw values (like PHP's backed enums)
- Pattern matching with switch
- Enum methods and computed properties
- Recursive enums
- Real-world enum patterns
- Best practices

## Prerequisites

- Completed [Chapter 08: Protocols](/series/swift-for-php-developers/chapters/08-protocols-interfaces)
- Understanding of PHP enums (8.1+)
- Knowledge of switch statements

---

## PHP Enums: The Basics

PHP 8.1 introduced backed enums:

```php
<?php
// Pure enum (no backing value)
enum Status {
    case Pending;
    case Approved;
    case Rejected;
}

// Backed enum (with values)
enum Status: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

// Usage
$status = Status::Pending;

// Match expression (PHP 8.0+)
$message = match($status) {
    Status::Pending => 'Waiting for approval',
    Status::Approved => 'Request approved',
    Status::Rejected => 'Request rejected',
};

// Methods (PHP 8.1+)
enum Status: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string {
        return match($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
```

**PHP enums are limited to:**
- Simple case values
- One backing value per case (string or int)
- Methods via match expressions

---

## Swift Enums: Much More Powerful

### Basic Enums

```swift
enum Status {
    case pending
    case approved
    case rejected
}

// Shorthand when type is known
var status: Status = .pending

// Switch statement
switch status {
case .pending:
    print("Waiting for approval")
case .approved:
    print("Request approved")
case .rejected:
    print("Request rejected")
}
```

**Key difference:** Swift's switch must be exhaustive—you must handle all cases or use `default`.

---

## Associated Values: The Game Changer

**Swift enums can store data with each case!** This is impossible in PHP.

### Example: Server Response

```swift
enum ServerResponse {
    case success(String)           // Success with message
    case failure(Int, String)      // Failure with code and message
    case loading                   // Loading state
}

// Create instances with data
let response1 = ServerResponse.success("Data received")
let response2 = ServerResponse.failure(404, "Not found")
let response3 = ServerResponse.loading

// Extract data with pattern matching
switch response1 {
case .success(let message):
    print("Success: \(message)")
case .failure(let code, let message):
    print("Error \(code): \(message)")
case .loading:
    print("Loading...")
}
// Output: "Success: Data received"

switch response2 {
case .success(let message):
    print("Success: \(message)")
case .failure(let code, let message):
    print("Error \(code): \(message)")  // This executes
case .loading:
    print("Loading...")
}
// Output: "Error 404: Not found"
```

**PHP Cannot Do This!** You'd need separate classes or arrays.

**PHP Workaround:**
```php
<?php
// Would require classes or arrays
class SuccessResponse {
    public function __construct(public string $message) {}
}

class FailureResponse {
    public function __construct(
        public int $code,
        public string $message
    ) {}
}

// Not as elegant
$response = new SuccessResponse("Data received");
```

---

## Raw Values (Like PHP's Backed Enums)

Swift enums can have raw values similar to PHP's backed enums.

```swift
enum Status: String {
    case pending = "pending"
    case approved = "approved"
    case rejected = "rejected"
}

let status = Status.pending
print(status.rawValue)  // "pending"

// Create from raw value (returns optional!)
if let status = Status(rawValue: "approved") {
    print(status)  // approved
}

let invalid = Status(rawValue: "unknown")  // nil
```

### Auto-Incrementing Raw Values

```swift
enum Priority: Int {
    case low = 1
    case medium = 2
    case high = 3
    case critical = 4
}

// Or let Swift auto-increment
enum Priority: Int {
    case low       // 0
    case medium    // 1
    case high      // 2
    case critical  // 3
}

print(Priority.high.rawValue)  // 2
```

**PHP Comparison:**
```php
<?php
enum Priority: int {
    case Low = 1;
    case Medium = 2;
    case High = 3;
    case Critical = 4;
}

echo Priority::High->value;  // 3
```

---

## Pattern Matching with Switch

Swift's pattern matching is incredibly powerful.

### Basic Matching

```swift
enum Direction {
    case north, south, east, west
}

let direction = Direction.north

switch direction {
case .north:
    print("Going north")
case .south:
    print("Going south")
case .east:
    print("Going east")
case .west:
    print("Going west")
}
// Must handle all cases!
```

### Matching Associated Values

```swift
enum PaymentMethod {
    case cash
    case creditCard(String)        // Card number
    case paypal(String)            // Email
    case applePay
}

let payment = PaymentMethod.creditCard("1234-5678-9012-3456")

switch payment {
case .cash:
    print("Payment by cash")

case .creditCard(let cardNumber):
    print("Charging card: \(cardNumber)")

case .paypal(let email):
    print("Charging PayPal: \(email)")

case .applePay:
    print("Charging Apple Pay")
}
// Output: "Charging card: 1234-5678-9012-3456"
```

### Where Clauses

Add conditions to cases:

```swift
enum NetworkResponse {
    case success(Int, String)
    case failure(Int, String)
}

let response = NetworkResponse.success(200, "OK")

switch response {
case .success(let code, let message) where code == 200:
    print("Perfect: \(message)")

case .success(let code, let message) where code >= 200 && code < 300:
    print("Success \(code): \(message)")

case .failure(let code, let message) where code == 404:
    print("Not found: \(message)")

case .failure(let code, let message):
    print("Error \(code): \(message)")

default:
    print("Unknown response")
}
```

---

## Enum Methods and Properties

Enums can have methods and computed properties!

```swift
enum Status {
    case pending, approved, rejected

    // Computed property
    var description: String {
        switch self {
        case .pending:
            return "Waiting for approval"
        case .approved:
            return "Request approved"
        case .rejected:
            return "Request rejected"
        }
    }

    // Method
    func canTransition(to newStatus: Status) -> Bool {
        switch (self, newStatus) {
        case (.pending, .approved), (.pending, .rejected):
            return true
        case (.approved, .rejected):
            return true
        default:
            return false
        }
    }
}

let status = Status.pending
print(status.description)  // "Waiting for approval"
print(status.canTransition(to: .approved))  // true
print(status.canTransition(to: .pending))   // false
```

**PHP Comparison:**
```php
<?php
enum Status {
    case Pending;
    case Approved;
    case Rejected;

    public function description(): string {
        return match($this) {
            self::Pending => 'Waiting for approval',
            self::Approved => 'Request approved',
            self::Rejected => 'Request rejected',
        };
    }
}
```

---

## Recursive Enums

Enums can reference themselves (with `indirect`).

```swift
// Binary tree
indirect enum BinaryTree<T> {
    case empty
    case node(T, BinaryTree<T>, BinaryTree<T>)
}

// Build a tree
let tree = BinaryTree.node(
    5,
    .node(3, .empty, .empty),
    .node(7, .empty, .empty)
)

// Traverse
func traverse<T>(_ tree: BinaryTree<T>) {
    switch tree {
    case .empty:
        return
    case .node(let value, let left, let right):
        traverse(left)
        print(value)
        traverse(right)
    }
}

traverse(tree)  // Prints: 3 5 7
```

**PHP has no equivalent** for recursive enums.

---

## Practical Example: Result Type

Model success or failure (common pattern).

```swift
enum Result<T, E> {
    case success(T)
    case failure(E)
}

// Usage with a function
func fetchUser(id: Int) -> Result<User, NetworkError> {
    if id > 0 {
        let user = User(id: id, name: "John")
        return .success(user)
    } else {
        return .failure(.invalidID)
    }
}

// Handle result
let result = fetchUser(id: 1)

switch result {
case .success(let user):
    print("Fetched user: \(user.name)")
case .failure(let error):
    print("Error: \(error)")
}

enum NetworkError: Error {
    case invalidID
    case notFound
    case serverError
}

struct User {
    let id: Int
    let name: String
}
```

**Note:** Swift 5.0+ has a built-in `Result<Success, Failure>` type!

---

## Practical Example: Form Validation

```swift
enum ValidationError {
    case empty
    case tooShort(Int)      // Minimum length
    case tooLong(Int)       // Maximum length
    case invalidFormat(String)
}

func validateEmail(_ email: String) -> Result<String, ValidationError> {
    if email.isEmpty {
        return .failure(.empty)
    }

    if email.count < 5 {
        return .failure(.tooShort(5))
    }

    if !email.contains("@") {
        return .failure(.invalidFormat("Must contain @"))
    }

    return .success(email)
}

// Usage
let result = validateEmail("john")

switch result {
case .success(let email):
    print("Valid email: \(email)")

case .failure(.empty):
    print("Email is required")

case .failure(.tooShort(let min)):
    print("Email must be at least \(min) characters")

case .failure(.tooLong(let max)):
    print("Email must be at most \(max) characters")

case .failure(.invalidFormat(let reason)):
    print("Invalid format: \(reason)")
}
// Output: "Email must be at least 5 characters"
```

**PHP Approach:**
```php
<?php
class ValidationError {
    public function __construct(
        public string $type,
        public mixed $data = null
    ) {}
}

function validateEmail(string $email): string|ValidationError {
    if (empty($email)) {
        return new ValidationError('empty');
    }

    if (strlen($email) < 5) {
        return new ValidationError('too_short', 5);
    }

    if (!str_contains($email, '@')) {
        return new ValidationError('invalid_format', 'Must contain @');
    }

    return $email;
}
```

Swift's approach is more type-safe and expressive.

---

## If Case and Guard Case

Pattern match without full switch.

### If Case

```swift
enum Status {
    case pending
    case approved(String)  // Approval message
    case rejected(String)  // Rejection reason
}

let status = Status.approved("Looks good!")

// Extract only if approved
if case .approved(let message) = status {
    print("Approved: \(message)")
}
// Output: "Approved: Looks good!"

// Won't execute
if case .rejected(let reason) = status {
    print("Rejected: \(reason)")
}
```

### Guard Case

```swift
func process(status: Status) {
    guard case .approved(let message) = status else {
        print("Not approved yet")
        return
    }

    // Only runs if approved
    print("Processing approval: \(message)")
}

process(status: .approved("OK"))  // "Processing approval: OK"
process(status: .pending)         // "Not approved yet"
```

---

## CaseIterable Protocol

Get all cases of an enum.

```swift
enum Direction: CaseIterable {
    case north, south, east, west
}

// Automatically provides allCases
for direction in Direction.allCases {
    print(direction)
}
// Prints: north, south, east, west

print(Direction.allCases.count)  // 4
```

**PHP Comparison:**
```php
<?php
enum Direction {
    case North;
    case South;
    case East;
    case West;

    public static function cases(): array {
        return [
            self::North,
            self::South,
            self::East,
            self::West,
        ];
    }
}

// PHP 8.1+ provides this automatically
foreach (Direction::cases() as $direction) {
    echo $direction->name . "\n";
}
```

---

## Comparing Enums

```swift
enum Status {
    case pending, approved, rejected
}

let status1 = Status.pending
let status2 = Status.pending
let status3 = Status.approved

status1 == status2  // true
status1 == status3  // false
```

**With associated values:**
```swift
enum Response: Equatable {
    case success(String)
    case failure(Int)
}

let r1 = Response.success("OK")
let r2 = Response.success("OK")
let r3 = Response.success("Done")

r1 == r2  // true
r1 == r3  // false
```

---

## Practical Example: State Machine

```swift
enum AppState {
    case loading
    case loaded([String])
    case error(String)

    mutating func reload() {
        self = .loading
    }
}

struct AppViewModel {
    var state = AppState.loading

    mutating func loadData() {
        state = .loading

        // Simulate API call
        let success = true

        if success {
            state = .loaded(["Item 1", "Item 2", "Item 3"])
        } else {
            state = .error("Failed to load data")
        }
    }

    func render() {
        switch state {
        case .loading:
            print("Loading...")

        case .loaded(let items):
            print("Items: \(items.joined(separator: ", "))")

        case .error(let message):
            print("Error: \(message)")
        }
    }
}

var viewModel = AppViewModel()
viewModel.render()  // "Loading..."

viewModel.loadData()
viewModel.render()  // "Items: Item 1, Item 2, Item 3"
```

This pattern is extremely common in SwiftUI apps!

---

## Enum Best Practices

### ✅ DO

```swift
// Use enums for fixed sets of values
enum Status {
    case pending, approved, rejected
}

// Use associated values to store context
enum Result<T, E> {
    case success(T)
    case failure(E)
}

// Provide helper methods
enum Status {
    case pending, approved, rejected

    var isPending: Bool {
        self == .pending
    }
}

// Use CaseIterable for iteration
enum Priority: CaseIterable {
    case low, medium, high
}
```

### ❌ DON'T

```swift
// Don't use enums for open-ended data
// enum UserID { case user1, user2, ... }  // Bad!

// Don't force unwrap enum raw values
// let status = Status(rawValue: "unknown")!  // Crash if invalid

// Don't ignore exhaustive switching
switch status {
case .pending:
    print("Pending")
default:  // Avoid if possible
    print("Other")
}
```

---

## Summary

You've mastered Swift enums and pattern matching:

✅ **Enums** define fixed sets of related values
✅ **Associated values** store data with each case (impossible in PHP)
✅ **Raw values** work like PHP's backed enums
✅ **Pattern matching** with switch is exhaustive and powerful
✅ **Methods and properties** add behavior to enums
✅ **Recursive enums** model tree structures
✅ **CaseIterable** provides all cases automatically
✅ **State machines** and Result types are common patterns

**Key Takeaway:** Swift enums are far more powerful than PHP's backed enums. They're a fundamental building block for type-safe, expressive Swift code. Associated values and pattern matching make enums one of Swift's most distinctive features.

---

## What's Next?

In [Chapter 10: Generics](/series/swift-for-php-developers/chapters/10-generics-type-parameters), you'll learn about Swift's powerful generic system—type-safe code that works with any type.

---

**Next Chapter:** [10 — Generics and Type Parameters](/series/swift-for-php-developers/chapters/10-generics-type-parameters)
