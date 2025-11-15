# Go for PHP Developers: Chapters 00-05 Summary

## Part 1: Go Foundations

Complete guide to getting started with Go as an experienced PHP developer. These first six chapters lay the foundation for everything else in the series.

## Overview

**Total Chapters**: 6 (Chapters 00-05)
**Code Files**: 25+ Go files
**Documentation**: 6 README files
**Learning Time**: 2-3 days for basics, 1-2 weeks to master
**Prerequisite**: Expert PHP knowledge

## What's Covered

### Chapter 00: Quick Start - From PHP to Go
**Goal**: Get writing Go code in minutes

**Files**:
- `01-hello-from-php-to-go.go` - Your first Go program
- `02-variables-and-types.go` - Type system introduction
- `03-functions-and-errors.go` - Functions and error handling
- `04-structs-vs-classes.go` - Structs instead of classes
- `05-web-server-comparison.go` - Built-in HTTP server

**Key Takeaways**:
- ✅ Go is compiled (fast binaries)
- ✅ Static typing catches errors early
- ✅ Errors are values, not exceptions
- ✅ Structs + methods instead of classes
- ✅ Built-in HTTP server - no Apache/Nginx needed

**PHP to Go Quick Reference**:
```php
// PHP
echo "Hello";
$name = "Alice";
try { } catch (Exception $e) { }
class User { }
```
```go
// Go
fmt.Println("Hello")
name := "Alice"
if err != nil { }
type User struct { }
```

---

### Chapter 01: Setup & Tooling
**Goal**: Professional Go development environment

**Topics**:
- Installing Go on Mac/Linux/Windows
- Go modules (like composer)
- Essential commands (run, build, test, fmt)
- Code formatting (gofmt - one true way!)
- Linting and static analysis

**Essential Commands**:
```bash
go mod init github.com/user/project  # Like composer init
go get package-name                   # Like composer require
go run main.go                        # Run without building
go build -o app                       # Compile to binary
go test ./...                         # Run tests
gofmt -w .                           # Format all code
go vet ./...                         # Static analysis
```

**Key Differences from PHP**:
- No central package repository (uses git directly)
- One official formatter (no style debates)
- Built-in testing (no PHPUnit to install)
- Built-in benchmarking
- Compilation step required

---

### Chapter 02: Basic Syntax & Types
**Goal**: Master Go's type system

**Topics**:
- Basic types (int, float64, string, bool)
- Type declarations (var, :=, const)
- Zero values (not null!)
- Type conversions (explicit only)
- Constants and enums (iota)
- Strings and Unicode (UTF-8)

**Type System**:
```go
// Explicit type
var name string = "Alice"

// Type inference
age := 30  // inferred as int

// Constants
const Pi = 3.14159

// Enums with iota
const (
    Sunday = iota  // 0
    Monday         // 1
    Tuesday        // 2
)

// No automatic conversion!
var i int = 42
var f float64 = float64(i)  // Must convert explicitly
```

**Zero Values**:
- `int` → 0
- `float64` → 0.0
- `string` → "" (empty string)
- `bool` → false
- `*T` → nil (pointers)

**vs PHP**:
- Static typing (compile-time checks)
- No type juggling
- No null for basic types
- Explicit conversions only

---

### Chapter 03: Control Structures & Functions
**Goal**: Master Go's control flow

**Topics**:
- if/else (no parentheses needed!)
- for loops (only loop keyword!)
- switch statements (no break needed)
- Functions with multiple returns
- defer statement (cleanup)

**Control Flow**:
```go
// If (no parentheses)
if age >= 18 {
    fmt.Println("Adult")
}

// Only "for" - multiple styles
for i := 0; i < 10; i++ { }     // Traditional
for condition { }                 // While-style
for { }                          // Infinite
for i, v := range slice { }      // Range (foreach)

// Switch (no fall-through)
switch status {
case "active":
    // No break needed!
case "pending", "waiting":
    // Multiple cases
}

// Multiple return values
func divide(a, b float64) (float64, error) {
    if b == 0 {
        return 0, errors.New("division by zero")
    }
    return a / b, nil
}

// defer runs when function exits
defer file.Close()
```

**vs PHP**:
- No while/do-while (use for)
- No ternary operator (use if/else)
- Switch doesn't fall through
- Multiple return values (common for errors)
- defer for cleanup (no finally)

---

### Chapter 04: Arrays, Slices & Maps
**Goal**: Master Go's data structures

**Topics**:
- Arrays (fixed-size, rarely used)
- Slices (dynamic, use these!)
- Maps (key-value pairs)
- Slice operations (append, copy, slicing)
- Map operations (access, delete, check)
- Common patterns

**Data Structures**:
```go
// Slice (dynamic array)
slice := []int{1, 2, 3}
slice = append(slice, 4)        // Add element
len(slice)                      // Length
sub := slice[1:3]              // Subslice

// Pre-allocate for performance
slice := make([]int, 0, 100)   // len=0, cap=100

// Map (associative array)
m := make(map[string]int)
m["apple"] = 5                 // Set
count := m["apple"]            // Get
delete(m, "apple")            // Delete

// Check existence
if count, ok := m["apple"]; ok {
    fmt.Println("Found:", count)
}

// Iterate
for key, value := range m {
    fmt.Println(key, value)
}
```

**vs PHP's Array**:
PHP has one array type that does everything:
```php
$arr = [1, 2, 3];              // Indexed
$arr[] = 4;                     // Append
$map = ['key' => 'value'];     // Associative
```

Go has three distinct types:
```go
arr := [3]int{1, 2, 3}        // Fixed array
slice := []int{1, 2, 3}        // Dynamic slice
m := map[string]int{}          // Map
```

**Common Patterns**:
- Pre-allocate slices when size is known
- Use maps for counting, grouping, sets
- Check map existence with comma-ok
- Initialize maps before use (or panic!)

---

### Chapter 05: Pointers & Memory
**Goal**: Understand pointers and memory management

**Topics**:
- What pointers are (&, *)
- Pass by value vs pointer
- Pointer receivers on methods
- new() vs make()
- When to use pointers
- Memory safety and GC

**Pointers**:
```go
// & = address of, * = dereference
x := 42
ptr := &x      // ptr points to x
*ptr = 100     // x is now 100

// Pass by value (copy)
func update(val int) {
    val = 100  // Doesn't affect original
}

// Pass by pointer (reference)
func update(ptr *int) {
    *ptr = 100  // Modifies original
}

// Pointer receivers
type User struct {
    Name string
}

func (u *User) SetName(name string) {
    u.Name = name  // Modifies original
}

// nil pointers
var ptr *int  // nil
if ptr == nil {
    // Check before dereferencing!
}
```

**When to Use Pointers**:
1. Need to modify parameter
2. Avoid copying large structs
3. Represent optional/missing values (nil)
4. Build linked structures

**vs PHP**:
```php
// PHP: References with &
function increment(&$value) {
    $value++;
}
```
```go
// Go: Pointers with *
func increment(value *int) {
    *value++
}
```

**Memory Management**:
- Automatic garbage collection (like PHP)
- No manual memory management
- No reference counting
- Stack vs heap handled automatically

---

## Learning Path

### Day 1: Quick Start
1. Chapter 00 - Get code running
2. Write a simple HTTP server
3. Understand basic syntax differences

### Day 2-3: Foundations
1. Chapter 01 - Set up environment
2. Chapter 02 - Learn type system
3. Chapter 03 - Control structures
4. Build simple CLI programs

### Week 1: Core Concepts
1. Chapter 04 - Data structures
2. Chapter 05 - Pointers
3. Build web API with database

### Week 2: Practice
- Rewrite PHP projects in Go
- Focus on idiomatic Go
- Learn from code reviews

## Common Mistakes for PHP Developers

### 1. Ignoring Errors
```go
// ❌ Don't ignore errors!
data, _ := readFile("config.json")

// ✅ Always check
data, err := readFile("config.json")
if err != nil {
    log.Fatal(err)
}
```

### 2. Expecting Type Coercion
```go
// ❌ Won't compile
var result = 5 + "10"

// ✅ Convert explicitly
var result = 5 + atoi("10")
```

### 3. Forgetting to Initialize Maps
```go
// ❌ Panic!
var m map[string]int
m["key"] = 123

// ✅ Initialize first
m := make(map[string]int)
m["key"] = 123
```

### 4. Using := Outside Functions
```go
// ❌ Syntax error
package main
name := "Alice"

// ✅ Use var at package level
package main
var name = "Alice"
```

### 5. Treating Slices Like PHP Arrays
```go
// ❌ Slices share backing arrays!
a := []int{1, 2, 3}
b := a
b[0] = 99  // a[0] is also 99!

// ✅ Copy explicitly
b := make([]int, len(a))
copy(b, a)
```

## PHP to Go Cheat Sheet

| Feature | PHP | Go |
|---------|-----|-----|
| **Run** | `php script.php` | `go run main.go` |
| **Variables** | `$name = "Alice"` | `name := "Alice"` |
| **Arrays** | `$arr = [1, 2, 3]` | `arr := []int{1, 2, 3}` |
| **Maps** | `$map = ['a' => 1]` | `m := map[string]int{"a": 1}` |
| **Functions** | `function add($a, $b)` | `func add(a, b int) int` |
| **Classes** | `class User { }` | `type User struct { }` |
| **Errors** | `try/catch` | `if err != nil { }` |
| **Null** | `$x = null` | `var x *int` (nil pointer) |
| **Concat** | `$str . " world"` | `str + " world"` |
| **Format** | `sprintf("Hi %s", $name)` | `fmt.Sprintf("Hi %s", name)` |
| **Length** | `count($arr)` | `len(slice)` |
| **Print** | `echo "Hi"` | `fmt.Println("Hi")` |
| **Include** | `require 'file.php'` | `import "package"` |
| **Web** | Apache + PHP-FPM | Built-in HTTP server |

## Performance Comparison

| Metric | PHP | Go | Winner |
|--------|-----|-----|--------|
| Startup | ~50ms | ~5ms | Go (10x) |
| Memory | ~30MB base | ~5MB base | Go (6x) |
| Requests/sec | ~10k | ~50k+ | Go (5x) |
| JSON encoding | 50k ops/s | 200k ops/s | Go (4x) |
| Compilation | Interpreted | Compiled | Go |
| Type safety | Runtime | Compile-time | Go |
| Deployment | PHP + files | Single binary | Go |

## What's Next

After mastering Part 1:

### Part 2: Go Language Features (Ch 06-10)
- Structs and methods
- Interfaces and polymorphism
- Error handling patterns
- Packages and modules
- Standard library tour

### Part 3: Concurrent Programming (Ch 11-15)
- Goroutines (lightweight threads)
- Channels (communication)
- Select statement
- Sync package
- Concurrent patterns

### Part 4: Web Development (Ch 16-20)
- HTTP servers
- Routing and middleware
- JSON APIs
- Templates
- Web frameworks (Gin, Echo, Fiber)

## Resources

**Official**:
- [Go Tour](https://go.dev/tour/) - Interactive tutorial
- [Effective Go](https://go.dev/doc/effective_go) - Best practices
- [Go by Example](https://gobyexample.com/) - Code examples

**Books**:
- "The Go Programming Language" (Donovan & Kernighan)
- "Learning Go" (Jon Bodner)
- "Concurrency in Go" (Katherine Cox-Buday)

**Practice**:
- [Exercism.org](https://exercism.org/tracks/go) - Exercises
- [Go Playground](https://go.dev/play/) - Online REPL
- [LeetCode](https://leetcode.com/) - Practice problems

## Summary

**Chapters 00-05 teach you**:
✅ Basic Go syntax and how it differs from PHP
✅ Go's type system and why it's safer
✅ Control structures and functions
✅ Data structures (slices, maps)
✅ Pointers and memory management
✅ Essential tooling and development workflow

**You can now**:
✅ Write basic Go programs
✅ Understand Go error messages
✅ Build simple web servers
✅ Work with data structures
✅ Read and understand Go code

**Next**: Part 2 will teach you Go's unique features like interfaces, composition over inheritance, and idiomatic error handling patterns.

---

**Total Time Investment**: 2-3 weeks from PHP expert to Go proficient
**ROI**: 5-10x better performance, simpler deployment, built-in concurrency

---

**Keep going!** The hardest part is shifting your mindset from dynamic to static typing and from exceptions to explicit errors. Once you embrace these, Go becomes very productive.
