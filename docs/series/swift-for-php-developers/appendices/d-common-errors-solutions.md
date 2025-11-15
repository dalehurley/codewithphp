---
title: "Appendix D: Common Errors and Solutions"
description: Troubleshooting guide for common errors PHP developers encounter when learning Swift.
series: swift-for-php-developers
appendix: D
tags: ["errors", "troubleshooting", "debugging", "solutions"]
---

# Appendix D: Common Errors and Solutions

A troubleshooting guide for PHP developers learning Swift.

## Compilation Errors

### "Cannot find 'variable' in scope"

**Error:**
```swift
func greet() {
    let name = "John"
}

print(name)  // Error: Cannot find 'name' in scope
```

**Cause:** Variable defined in function scope, not accessible outside

**Solution:**
```swift
let name = "John"  // Define in outer scope

func greet() {
    print(name)  // Now accessible
}
```

**PHP Comparison:**
PHP has similar scope issues but is more forgiving with globals

---

### "Value of optional type 'String?' must be unwrapped"

**Error:**
```swift
let name: String? = "John"
print(name.count)  // Error: must be unwrapped
```

**Cause:** Trying to use optional without unwrapping

**Solutions:**
```swift
// Option 1: Optional binding
if let name = name {
    print(name.count)
}

// Option 2: Optional chaining
print(name?.count ?? 0)

// Option 3: Guard statement
guard let name = name else {
    return
}
print(name.count)
```

**PHP Comparison:**
```php
// PHP: null check
$name = $user->getName();  // might be null
echo $name ? strlen($name) : 0;
```

---

### "Cannot convert value of type 'String' to expected argument type 'Int'"

**Error:**
```swift
let age: Int = "30"  // Error: cannot convert String to Int
```

**Cause:** Swift doesn't auto-convert types like PHP

**Solution:**
```swift
let ageString = "30"
let age = Int(ageString)  // Optional<Int>, might fail
let age = Int(ageString) ?? 0  // With default
```

**PHP Comparison:**
```php
// PHP: automatic type conversion
$age = "30";
$result = $age + 5;  // Works: 35
```

---

### "Immutable value 'x' may not be assigned"

**Error:**
```swift
let name = "John"
name = "Jane"  // Error: 'let' is immutable
```

**Cause:** Using `let` (constant) instead of `var` (variable)

**Solution:**
```swift
var name = "John"  // Use 'var' for mutable
name = "Jane"  // Now works
```

**PHP Comparison:**
```php
// PHP: variables are mutable by default
$name = "John";
$name = "Jane";  // Always works
```

---

### "Function declares an opaque return type, but has no return statements"

**Error:**
```swift
func getUser() -> User {
    // No return statement
}  // Error
```

**Solution:**
```swift
func getUser() -> User {
    return User(id: 1, name: "John")
}

// Or use Void if no return
func logMessage() {
    print("Logged")
}
```

---

## Runtime Errors

### "Fatal error: Unexpectedly found nil while unwrapping an Optional value"

**Error:**
```swift
let user: User? = nil
print(user!.name)  // Fatal error: force unwrap of nil
```

**Cause:** Force unwrapping (`!`) a nil optional

**Solution:**
```swift
// Don't use ! unless you're 100% sure it's not nil
if let user = user {
    print(user.name)
}
```

**This is the #1 crash in Swift. Never use `!` unless absolutely necessary.**

---

### "Index out of range"

**Error:**
```swift
let arr = [1, 2, 3]
let value = arr[5]  // Runtime error
```

**Solution:**
```swift
// Check bounds
if arr.indices.contains(5) {
    let value = arr[5]
}

// Or use safe subscript
let value = arr[safe: 5]  // Returns Optional
```

---

## Memory Management Errors

### Retain Cycle (Memory Leak)

**Problem:**
```swift
class ViewController {
    var closure: (() -> Void)?

    func setupClosure() {
        closure = {
            self.doSomething()  // Strong reference cycle!
        }
    }
}
```

**Solution:**
```swift
closure = { [weak self] in
    self?.doSomething()  // Break the cycle with weak self
}
```

**PHP Comparison:**
PHP has garbage collection, so this isn't an issue

---

## SwiftUI Errors

### "Cannot convert value of type 'SomeView' to closure result type 'some View'"

**Error:**
```swift
var body: some View {
    Text("Hello")
    Text("World")  // Error: multiple views
}
```

**Solution:**
```swift
var body: some View {
    VStack {  // Wrap in container
        Text("Hello")
        Text("World")
    }
}
```

---

### "Modifying state during view update"

**Error:** App crashes when updating @State during rendering

**Solution:**
```swift
// Don't do this:
var body: some View {
    count += 1  // Modifying state during render!
    return Text("\(count)")
}

// Do this:
var body: some View {
    Text("\(count)")
        .onAppear {
            count += 1  // Modify in lifecycle method
        }
}
```

---

## Async/Await Errors

### "Expression is 'async' but is not marked with 'await'"

**Error:**
```swift
func fetchData() async -> Data { ... }

let data = fetchData()  // Error: missing await
```

**Solution:**
```swift
let data = await fetchData()  // Add await

// Or in non-async context:
Task {
    let data = await fetchData()
}
```

---

## Common Beginner Mistakes (From PHP Background)

### 1. Forgetting Type Annotations When Needed

**PHP Habit:**
```php
$data = [];  // PHP infers empty array
```

**Swift:**
```swift
let data = []  // Error: type annotation needed
let data: [String] = []  // Correct
```

---

### 2. Using `==` Instead of `===` for Optionals

**PHP Habit:**
```php
if ($value === null) { }  // Strict comparison
```

**Swift:**
```swift
if value == nil { }  // Use ==, not ===
```

---

### 3. Expecting Auto Type Conversion

**PHP Habit:**
```php
echo "The count is: " . $count;  // Auto converts to string
```

**Swift:**
```swift
print("The count is: " + count)  // Error
print("The count is: \(count)")  // Correct: string interpolation
```

---

### 4. Modifying Collection While Iterating

**PHP:**
```php
foreach ($items as $key => $item) {
    unset($items[$key]);  // Works (sometimes)
}
```

**Swift:**
```swift
for item in items {
    items.remove(at: index)  // Runtime error or unexpected behavior
}

// Correct way:
items.removeAll(where: { $0.condition })
```

---

## Xcode/Build Errors

### "Command PhaseScriptExecution failed with a nonzero exit code"

**Cause:** Build script error

**Solutions:**
1. Clean build folder (⌘⇧K)
2. Delete derived data
3. Check Build Phases scripts
4. Update dependencies

---

### "Could not find Developer Disk Image"

**Cause:** iOS device version newer than Xcode

**Solution:**
Update Xcode to latest version

---

### "No signing identity found"

**Cause:** Missing code signing certificate

**Solution:**
1. Xcode → Preferences → Accounts
2. Download Manual Profiles
3. Or: Turn off "Automatically manage signing" for simulator

---

## Debugging Tips

### Print Debugging

```swift
// Swift
print("Debug: \(variable)")
dump(complexObject)  // Detailed output

// PHP equivalent
var_dump($variable);
```

### Breakpoint Logging

Instead of print statements:
1. Add breakpoint
2. Right-click → Edit Breakpoint
3. Add Action → Log Message
4. Automatically Continue: ✓

### Type Checking

```swift
if let value = value as? ExpectedType {
    print("Success: \(value)")
} else {
    print("Type mismatch")
}
```

---

## Error Messages Decoder

| Swift Error | What It Really Means |
|-------------|---------------------|
| "Type '...' has no member '...'" | Property/method doesn't exist |
| "Cannot infer type" | Need to specify type explicitly |
| "Ambiguous use of '...'" | Compiler can't decide which overload to use |
| "Instance member '...' cannot be used on type '...'" | Trying to use instance method as static |
| "Self' may not be used in property initializers" | Can't use self in property default value |

---

## Getting Help

When stuck:

1. **Read the error message** carefully (Xcode shows helpful Fix-it suggestions)
2. **Check documentation** (⌥ + Click on symbol)
3. **Clean and rebuild** (⌘⇧K)
4. **Search Stack Overflow** (include "Swift" + error message)
5. **Ask in forums** (Swift Forums, Apple Developer Forums)

**See also:**
- [Appendix A: PHP to Swift Quick Reference](/series/swift-for-php-developers/appendices/a-php-swift-quick-reference)
- [Appendix C: Xcode Tips](/series/swift-for-php-developers/appendices/c-xcode-tips-shortcuts)
