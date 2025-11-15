---
title: "Chapter 11: Error Handling - Throws, Try, and Catch"
description: Master Swift's type-safe error handling with throws, try, and catch—similar to PHP exceptions but with more compile-time safety.
series: swift-for-php-developers
chapter: 11
difficulty: Intermediate
tags: ["error-handling", "throws", "try-catch", "exceptions", "result-type"]
---

# Chapter 11: Error Handling with Throws, Try, and Catch

Swift's error handling is similar to PHP's exceptions, but with **stronger compile-time guarantees**. The compiler forces you to handle errors explicitly, preventing forgotten error cases.

This chapter shows you how to handle errors the Swift way.

## What You'll Learn

- The Error protocol
- Throwing functions (`throws`)
- Catching errors (`do-catch`)
- Different forms of `try` (`try`, `try?`, `try!`)
- Error propagation
- Converting errors to optionals
- `defer` for cleanup
- Comparing to PHP's exceptions
- Result type as an alternative

## Prerequisites

- Completed [Chapter 10: Generics](/series/swift-for-php-developers/chapters/10-generics-type-parameters)
- Understanding of PHP exceptions
- Knowledge of optionals

---

## PHP Exceptions: Quick Review

```php
<?php
class InvalidAgeException extends Exception {}

function setAge(int $age): void {
    if ($age < 0 || $age > 150) {
        throw new InvalidAgeException("Age must be between 0 and 150");
    }
    echo "Age set to: $age\n";
}

try {
    setAge(25);   // OK
    setAge(200);  // Throws exception
} catch (InvalidAgeException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    echo "Cleanup\n";
}
```

**PHP error handling:**
- Throw exceptions with `throw`
- Catch with `try-catch-finally`
- Exceptions can go uncaught (runtime error)
- No compile-time checking

---

## Swift Error Handling: Overview

```swift
enum ValidationError: Error {
    case invalidAge
    case invalidName
}

func setAge(_ age: Int) throws {
    if age < 0 || age > 150 {
        throw ValidationError.invalidAge
    }
    print("Age set to: \(age)")
}

do {
    try setAge(25)   // OK
    try setAge(200)  // Throws error
} catch ValidationError.invalidAge {
    print("Error: Invalid age")
} catch {
    print("Other error: \(error)")
}
```

**Swift error handling:**
- Functions that throw are marked with `throws`
- Must use `try` when calling throwing functions
- Compiler forces you to handle errors
- Type-safe error types

---

## Defining Errors

Errors conform to the `Error` protocol.

### Enum Errors (Recommended)

```swift
enum NetworkError: Error {
    case badURL
    case requestFailed
    case invalidResponse(Int)  // With associated value
    case decodingFailed
}

enum FileError: Error {
    case notFound
    case permissionDenied
    case corrupted
}
```

### Struct Errors

```swift
struct ValidationError: Error {
    let field: String
    let message: String
}
```

### Class Errors

```swift
class DatabaseError: Error {
    let query: String
    let reason: String

    init(query: String, reason: String) {
        self.query = query
        self.reason = reason
    }
}
```

**Best Practice:** Use enums for most errors.

---

## Throwing Functions

Mark functions that can throw errors with `throws`.

### Basic Throwing Function

```swift
enum MathError: Error {
    case divisionByZero
}

func divide(_ a: Double, by b: Double) throws -> Double {
    if b == 0 {
        throw MathError.divisionByZero
    }
    return a / b
}
```

### Throwing Initializer

```swift
struct Age {
    let value: Int

    init(_ value: Int) throws {
        if value < 0 || value > 150 {
            throw ValidationError.invalidAge
        }
        self.value = value
    }
}

// Usage
do {
    let age = try Age(25)  // OK
    print(age.value)
} catch {
    print("Invalid age")
}
```

### Throwing Method

```swift
struct User {
    let age: Int

    mutating func updateAge(_ newAge: Int) throws {
        if newAge < 0 || newAge > 150 {
            throw ValidationError.invalidAge
        }
        // Would update age here if var
    }
}
```

---

## Calling Throwing Functions

### do-catch

```swift
do {
    let result = try divide(10, by: 2)
    print("Result: \(result)")
} catch MathError.divisionByZero {
    print("Cannot divide by zero")
} catch {
    print("Unknown error: \(error)")
}
```

### Multiple Catches

```swift
enum NetworkError: Error {
    case badURL
    case timeout
    case serverError(Int)
}

func fetchData() throws {
    throw NetworkError.serverError(500)
}

do {
    try fetchData()
} catch NetworkError.badURL {
    print("Invalid URL")
} catch NetworkError.timeout {
    print("Request timed out")
} catch NetworkError.serverError(let code) {
    print("Server error: \(code)")
} catch {
    print("Unknown error: \(error)")
}
```

### Pattern Matching in Catch

```swift
do {
    try fetchData()
} catch let error as NetworkError {
    switch error {
    case .badURL:
        print("Bad URL")
    case .timeout:
        print("Timeout")
    case .serverError(let code):
        print("Server error: \(code)")
    }
} catch {
    print("Other error")
}
```

---

## try? - Convert to Optional

Convert throwing calls to optionals (returns `nil` on error).

```swift
let result = try? divide(10, by: 2)  // Double?

if let result = result {
    print("Result: \(result)")
} else {
    print("Division failed")
}

// Useful with guard
guard let result = try? divide(10, by: 2) else {
    print("Failed")
    return
}

// Chaining optionals
let value = try? divide(10, by: 2) ?? 0
```

**PHP Comparison:**
```php
<?php
// PHP doesn't have built-in try-to-optional
function divideOrNull(float $a, float $b): ?float {
    try {
        return divide($a, $b);
    } catch (Exception $e) {
        return null;
    }
}

$result = divideOrNull(10, 2) ?? 0;
```

---

## try! - Force Try (Dangerous)

Force unwrap the result. **Crashes if error is thrown.**

```swift
// Only use if you're 100% sure it won't throw
let result = try! divide(10, by: 2)  // OK

// ❌ Dangerous!
// let result = try! divide(10, by: 0)  // 💥 Runtime crash!
```

**When it's acceptable:**
```swift
// Loading a bundled file that must exist
let url = Bundle.main.url(forResource: "config", withExtension: "json")!
let data = try! Data(contentsOf: url)  // Safe if file is guaranteed to exist
```

**Generally avoid:** Use `try?` or `do-catch` instead.

---

## Error Propagation

Throwing functions can propagate errors to their callers.

```swift
func loadUser() throws -> User {
    let data = try fetchData()  // Propagates error
    let user = try parseUser(data)  // Propagates error
    return user
}

// Caller must handle
do {
    let user = try loadUser()
    print(user)
} catch {
    print("Failed to load user: \(error)")
}
```

**PHP Comparison:**
```php
<?php
function loadUser(): User {
    $data = fetchData();  // Throws
    $user = parseUser($data);  // Throws
    return $user;
    // Errors automatically propagate
}

try {
    $user = loadUser();
    echo $user;
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage();
}
```

---

## Defer: Cleanup Code

`defer` executes code when leaving a scope (like PHP's finally, but different).

```swift
func processFile(filename: String) throws {
    let file = try openFile(filename)
    defer {
        closeFile(file)  // Always runs when function exits
    }

    // Process file
    let data = try readFile(file)
    try parseData(data)

    // closeFile() runs here automatically
}
```

**Key Points:**
- `defer` runs when scope exits (return, throw, or end of function)
- Multiple `defer` blocks run in reverse order
- Useful for cleanup (closing files, releasing resources)

```swift
func example() {
    defer { print("1") }
    defer { print("2") }
    defer { print("3") }
    print("Start")
}
// Output: Start, 3, 2, 1
```

**PHP Comparison:**
```php
<?php
function processFile(string $filename): void {
    try {
        $file = openFile($filename);
        $data = readFile($file);
        parseData($data);
    } finally {
        closeFile($file);  // Always runs
    }
}
```

**Difference:** `defer` is per-scope, `finally` is per-try block.

---

## Rethrowing Functions

Functions that take throwing closures can rethrow errors.

```swift
func performOperation(_ operation: () throws -> Void) rethrows {
    try operation()
}

// Usage
func mightFail() throws {
    throw NetworkError.badURL
}

do {
    try performOperation {
        try mightFail()
    }
} catch {
    print("Operation failed: \(error)")
}
```

---

## LocalizedError: User-Friendly Messages

Provide human-readable error messages.

```swift
enum FileError: LocalizedError {
    case notFound(String)
    case permissionDenied

    var errorDescription: String? {
        switch self {
        case .notFound(let filename):
            return "File '\(filename)' not found"
        case .permissionDenied:
            return "Permission denied"
        }
    }

    var failureReason: String? {
        switch self {
        case .notFound:
            return "The file does not exist"
        case .permissionDenied:
            return "You don't have permission to access this file"
        }
    }
}

// Usage
do {
    throw FileError.notFound("config.json")
} catch let error as LocalizedError {
    print(error.errorDescription ?? "Unknown error")
    print(error.failureReason ?? "")
}
```

---

## Result Type Alternative

Use `Result<Success, Failure>` instead of throwing (covered in Chapter 10).

```swift
enum NetworkError: Error {
    case badURL, timeout
}

// Throwing version
func fetchDataThrowing() throws -> Data {
    throw NetworkError.badURL
}

// Result version
func fetchDataResult() -> Result<Data, NetworkError> {
    return .failure(.badURL)
}

// Usage
let result = fetchDataResult()

switch result {
case .success(let data):
    print("Got data: \(data)")
case .failure(let error):
    print("Error: \(error)")
}

// Or with try
do {
    let data = try result.get()  // Convert Result to throwing
    print(data)
} catch {
    print("Error: \(error)")
}
```

**When to use Result:**
- Asynchronous operations
- When error is part of return type
- Functional programming style

**When to use throws:**
- Synchronous operations
- When errors are exceptional
- Traditional imperative style

---

## Practical Example: User Validation

```swift
enum ValidationError: LocalizedError {
    case emptyName
    case invalidEmail
    case ageTooYoung(Int)
    case ageTooOld(Int)

    var errorDescription: String? {
        switch self {
        case .emptyName:
            return "Name cannot be empty"
        case .invalidEmail:
            return "Email is invalid"
        case .ageTooYoung(let age):
            return "Age \(age) is too young (minimum 18)"
        case .ageTooOld(let age):
            return "Age \(age) is too old (maximum 120)"
        }
    }
}

struct User {
    let name: String
    let email: String
    let age: Int

    init(name: String, email: String, age: Int) throws {
        // Validate name
        guard !name.isEmpty else {
            throw ValidationError.emptyName
        }

        // Validate email
        guard email.contains("@") else {
            throw ValidationError.invalidEmail
        }

        // Validate age
        guard age >= 18 else {
            throw ValidationError.ageTooYoung(age)
        }

        guard age <= 120 else {
            throw ValidationError.ageTooOld(age)
        }

        self.name = name
        self.email = email
        self.age = age
    }
}

// Usage
do {
    let user = try User(name: "John", email: "john@example.com", age: 25)
    print("Created user: \(user.name)")
} catch let error as LocalizedError {
    print("Validation failed: \(error.errorDescription ?? "")")
}

// Using try?
if let user = try? User(name: "", email: "bad", age: 15) {
    print("User: \(user)")
} else {
    print("Invalid user data")
}
```

**PHP Comparison:**
```php
<?php
class ValidationException extends Exception {}

class User {
    public function __construct(
        public string $name,
        public string $email,
        public int $age
    ) {
        if (empty($name)) {
            throw new ValidationException("Name cannot be empty");
        }

        if (!str_contains($email, '@')) {
            throw new ValidationException("Email is invalid");
        }

        if ($age < 18) {
            throw new ValidationException("Age $age is too young");
        }

        if ($age > 120) {
            throw new ValidationException("Age $age is too old");
        }
    }
}

try {
    $user = new User("John", "john@example.com", 25);
    echo "Created user: {$user->name}\n";
} catch (ValidationException $e) {
    echo "Validation failed: " . $e->getMessage() . "\n";
}
```

---

## Practical Example: File Operations

```swift
enum FileError: Error {
    case notFound
    case unreadable
    case encodingFailed
}

func readFile(at path: String) throws -> String {
    // Check if file exists
    guard FileManager.default.fileExists(atPath: path) else {
        throw FileError.notFound
    }

    // Try to read data
    guard let data = FileManager.default.contents(atPath: path) else {
        throw FileError.unreadable
    }

    // Try to convert to string
    guard let content = String(data: data, encoding: .utf8) else {
        throw FileError.encodingFailed
    }

    return content
}

// Usage
do {
    let content = try readFile(at: "/path/to/file.txt")
    print("File content: \(content)")
} catch FileError.notFound {
    print("File not found")
} catch FileError.unreadable {
    print("Cannot read file")
} catch FileError.encodingFailed {
    print("Failed to decode file")
} catch {
    print("Unknown error: \(error)")
}

// With cleanup
func processFile(at path: String) throws {
    let handle = try FileHandle(forReadingFrom: URL(fileURLWithPath: path))

    defer {
        try? handle.close()  // Always close, even if error
    }

    let data = handle.readDataToEndOfFile()
    // Process data...
}
```

---

## Error Handling Best Practices

### ✅ DO

```swift
// Use enums for error types
enum AppError: Error {
    case network(NetworkError)
    case validation(ValidationError)
    case database(DatabaseError)
}

// Provide context with associated values
enum NetworkError: Error {
    case requestFailed(statusCode: Int)
    case invalidURL(String)
}

// Use guard for early returns
func process(value: String?) throws {
    guard let value = value else {
        throw ValidationError.emptyValue
    }
    // Continue processing
}

// Use try? for optional results
let user = try? loadUser()

// Use defer for cleanup
func operation() throws {
    let resource = allocate()
    defer { release(resource) }
    try doWork(resource)
}
```

### ❌ DON'T

```swift
// Don't use try! unless absolutely certain
// let result = try! riskyOperation()  // Dangerous!

// Don't swallow errors silently
do {
    try operation()
} catch {
    // ❌ Empty catch
}

// Don't use errors for control flow
enum NotAnError: Error {
    case shouldStop  // ❌ Not really an error
}

// Don't over-catch
do {
    try operation()
} catch {
    // ❌ Too broad, can't differentiate errors
}
```

---

## Comparing to PHP

| Feature | Swift | PHP |
|---------|-------|-----|
| Mark throwing functions | `throws` keyword | No marking needed |
| Throw errors | `throw error` | `throw exception` |
| Must use try | Yes (`try` keyword) | No |
| Compile-time checking | ✅ Yes | ❌ No |
| Convert to optional | `try?` | Manual try-catch |
| Force try | `try!` | N/A |
| Cleanup | `defer` | `finally` |
| Type-safe errors | ✅ Yes (Error protocol) | ⚠️ Partial (can throw anything) |
| Rethrowing | `rethrows` | Automatic |

---

## Summary

You've mastered Swift error handling:

✅ **Error protocol** defines error types (enums, structs, classes)
✅ **throws** marks functions that can throw errors
✅ **try** required when calling throwing functions
✅ **do-catch** handles errors with pattern matching
✅ **try?** converts errors to optionals
✅ **try!** force-unwraps (dangerous, avoid)
✅ **defer** ensures cleanup code runs
✅ **Compile-time safety** forces error handling
✅ **Result type** alternative for functional style

**Key Takeaway:** Swift's error handling is similar to PHP's exceptions, but with stronger compile-time guarantees. The compiler ensures you handle errors explicitly, preventing forgotten error cases.

---

## Congratulations! 🎉

You've completed **Part 2: OOP and Protocols!**

You've learned:
- Classes vs structs (value vs reference types)
- Properties and methods
- Protocols (Swift's superpower)
- Enums with associated values
- Generics for type-safe reusable code
- Error handling with throws/try/catch

## What's Next?

In [Chapter 12: ARC and Memory Management](/series/swift-for-php-developers/chapters/12-arc-memory-management), you'll learn how Swift manages memory automatically with Automatic Reference Counting—and how to avoid memory leaks.

---

**Next Chapter:** [12 — ARC and Memory Management](/series/swift-for-php-developers/chapters/12-arc-memory-management)
