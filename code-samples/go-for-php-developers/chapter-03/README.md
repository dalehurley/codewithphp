# Chapter 03: Control Structures & Functions

Learn Go's control flow: if/else, loops, switch statements, and function declarations. Discover why Go has only one loop keyword and how defer changes error handling.

## Overview

Go's control structures are simpler than PHP's - there's only one loop keyword (`for`), no ternary operator, and switch statements don't fall through by default. Functions can return multiple values, which enables Go's idiomatic error handling.

## Files in This Chapter

### 1. `01-if-else-statements.go`
**Topics**: if/else, initialization in if, no parentheses required
### 2. `02-for-loops.go`
**Topics**: for loop variations, range, infinite loops, break/continue
### 3. `03-switch-statements.go`
**Topics**: switch, type switches, no break needed
### 4. `04-function-basics.go`
**Topics**: Function declaration, multiple returns, named returns
### 5. `05-defer-panic-recover.go`
**Topics**: defer, panic, recover (Go's error handling)

## Quick Reference

### If Statements

**PHP**:
```php
if ($age >= 18) {
    echo "Adult";
} elseif ($age >= 13) {
    echo "Teen";
} else {
    echo "Child";
}

// Ternary
$status = $age >= 18 ? "Adult" : "Minor";
```

**Go**:
```go
if age >= 18 {
    fmt.Println("Adult")
} else if age >= 13 {
    fmt.Println("Teen")
} else {
    fmt.Println("Child")
}

// No ternary! Use if/else
status := "Minor"
if age >= 18 {
    status = "Adult"
}

// Or inline if:
if age := getAge(); age >= 18 {
    fmt.Println("Adult")
}
```

### Loops

**PHP**:
```php
// For loop
for ($i = 0; $i < 10; $i++) {
    echo $i;
}

// While loop
while ($condition) {
    // ...
}

// Do-while loop
do {
    // ...
} while ($condition);

// Foreach
foreach ($array as $value) {
    echo $value;
}

foreach ($array as $key => $value) {
    echo "$key: $value";
}
```

**Go**:
```go
// Only "for" - but very flexible!

// Traditional for loop
for i := 0; i < 10; i++ {
    fmt.Println(i)
}

// While-style loop
for condition {
    // ...
}

// Infinite loop
for {
    // ... (use break to exit)
}

// Range over slice (like foreach)
for index, value := range slice {
    fmt.Printf("%d: %v\n", index, value)
}

// Just values
for _, value := range slice {
    fmt.Println(value)
}

// Just indexes
for index := range slice {
    fmt.Println(index)
}
```

### Switch Statements

**PHP**:
```php
switch ($status) {
    case 'active':
        echo "Active";
        break;  // Required!
    case 'pending':
        echo "Pending";
        break;
    default:
        echo "Unknown";
}
```

**Go**:
```go
switch status {
case "active":
    fmt.Println("Active")
    // No break needed! (doesn't fall through)
case "pending":
    fmt.Println("Pending")
default:
    fmt.Println("Unknown")
}

// Multiple cases
switch status {
case "active", "enabled":
    fmt.Println("Active")
case "pending", "waiting":
    fmt.Println("Waiting")
}

// Switch with initialization
switch s := getStatus(); s {
case "active":
    // ...
}

// Switch without expression (like if/else)
switch {
case age < 13:
    fmt.Println("Child")
case age < 18:
    fmt.Println("Teen")
default:
    fmt.Println("Adult")
}
```

### Functions

**PHP**:
```php
function add(int $a, int $b): int {
    return $a + $b;
}

function divide(float $a, float $b): ?float {
    if ($b == 0) {
        return null;  // Or throw exception
    }
    return $a / $b;
}
```

**Go**:
```go
func add(a int, b int) int {
    return a + b
}

// Group same types
func add(a, b int) int {
    return a + b
}

// Multiple return values
func divide(a, b float64) (float64, error) {
    if b == 0 {
        return 0, errors.New("division by zero")
    }
    return a / b, nil
}

// Named return values
func divide(a, b float64) (result float64, err error) {
    if b == 0 {
        err = errors.New("division by zero")
        return  // Returns named values
    }
    result = a / b
    return
}
```

## Key Concepts

### 1. No Parentheses in Conditions
```go
// ✅ Go style
if x > 0 {
    // ...
}

// ❌ Not required (but allowed)
if (x > 0) {
    // ...
}
```

### 2. Braces Are Required
```go
// ❌ Won't compile
if x > 0
    fmt.Println("positive")

// ✅ Braces required
if x > 0 {
    fmt.Println("positive")
}
```

### 3. Only One Loop: for
```go
// Traditional
for i := 0; i < 10; i++ { }

// While-style
for i < 10 { }

// Infinite
for { }

// Range
for i, v := range slice { }
```

### 4. defer Statement
```go
func readFile() error {
    file, err := os.Open("data.txt")
    if err != nil {
        return err
    }
    defer file.Close()  // Runs when function exits

    // Read file...
    // No need to close in every return path!
    return nil
}
```

### 5. Multiple Return Values
```go
func getUser(id int) (*User, error) {
    user, err := database.FindUser(id)
    if err != nil {
        return nil, err
    }
    return user, nil
}

// Usage
user, err := getUser(123)
if err != nil {
    log.Fatal(err)
}
fmt.Println(user.Name)
```

## Common Patterns

### 1. Error Checking Pattern
```go
result, err := someFunction()
if err != nil {
    return err  // Or handle error
}
// Use result
```

### 2. Inline Initialization
```go
if err := doSomething(); err != nil {
    return err
}

if user, err := getUser(id); err == nil {
    fmt.Println(user.Name)
}
```

### 3. Range with Index
```go
fruits := []string{"apple", "banana", "cherry"}

for i, fruit := range fruits {
    fmt.Printf("%d: %s\n", i, fruit)
}

// Ignore index
for _, fruit := range fruits {
    fmt.Println(fruit)
}

// Just index
for i := range fruits {
    fmt.Println(i)
}
```

### 4. Switch for Type Checking
```go
func printType(v interface{}) {
    switch v := v.(type) {
    case int:
        fmt.Printf("Integer: %d\n", v)
    case string:
        fmt.Printf("String: %s\n", v)
    case bool:
        fmt.Printf("Boolean: %t\n", v)
    default:
        fmt.Printf("Unknown type: %T\n", v)
    }
}
```

### 5. defer for Cleanup
```go
func copyFile(src, dst string) error {
    source, err := os.Open(src)
    if err != nil {
        return err
    }
    defer source.Close()

    destination, err := os.Create(dst)
    if err != nil {
        return err
    }
    defer destination.Close()

    _, err = io.Copy(destination, source)
    return err
}
```

## Best Practices

1. **Use defer for cleanup** - file closing, unlocking mutexes
2. **Check errors immediately** - don't ignore them
3. **Use range for iteration** - cleaner than traditional for
4. **Keep functions small** - single responsibility
5. **Return errors, don't panic** - except for unrecoverable errors

## Common Mistakes

### 1. Forgetting := Creates New Variable
```go
var err error
if err := doSomething(); err != nil {  // ❌ New err in this scope!
    return err
}
// Original err is still nil here

// ✅ Correct
var err error
if err = doSomething(); err != nil {
    return err
}
```

### 2. Range Loop Variable Reuse
```go
var funcs []func()
for _, v := range []int{1, 2, 3} {
    funcs = append(funcs, func() {
        fmt.Println(v)  // ❌ Always prints 3!
    })
}

// ✅ Correct
for _, v := range []int{1, 2, 3} {
    v := v  // Create new variable
    funcs = append(funcs, func() {
        fmt.Println(v)
    })
}
```

### 3. Defer in Loops
```go
for _, file := range files {
    f, _ := os.Open(file)
    defer f.Close()  // ❌ Won't close until function ends!
}

// ✅ Use a function
for _, file := range files {
    func() {
        f, _ := os.Open(file)
        defer f.Close()
        // Process file
    }()
}
```

## Next Steps

- **Chapter 04**: Arrays, slices, and maps
- **Chapter 05**: Pointers and memory management
- **Chapter 06**: Structs and methods

---

**Key Takeaway**: Go's control structures are simpler than PHP's, but combined with multiple return values and defer, they enable very clean error handling.
