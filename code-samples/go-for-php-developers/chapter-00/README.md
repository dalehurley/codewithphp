# Chapter 00: Quick Start - From PHP to Go

Welcome! This chapter gets you started with Go in the fastest way possible, showing you the key differences from PHP and getting you writing Go code immediately.

## Files in This Chapter

### 1. `01-hello-from-php-to-go.go`
**Purpose**: Your first Go program with PHP comparison
**Key Concepts**:
- Basic program structure
- package main and func main()
- Compilation vs interpretation
- Strong static typing
- No semicolons (usually)

**Run it**:
```bash
go run 01-hello-from-php-to-go.go

# Or compile and run
go build 01-hello-from-php-to-go.go
./01-hello-from-php-to-go
```

**What you'll learn**:
- Go programs start with `package main` and `func main()`
- Go is compiled to a native binary
- Type declarations come after variable names
- Import statements vs PHP require/include

---

### 2. `02-variables-and-types.go`
**Purpose**: Understanding Go's type system vs PHP's dynamic types
**Key Concepts**:
- Variable declarations (var, :=, const)
- Type inference
- Zero values vs PHP null
- Strong static typing at compile time
- Type conversions (no automatic coercion)

**Run it**:
```bash
go run 02-variables-and-types.go
```

**What you'll learn**:
- `var name string = "Go"` - explicit type
- `name := "Go"` - short declaration with type inference
- Types are checked at compile time (no runtime type juggling)
- Every type has a zero value (not null/undefined)

---

### 3. `03-functions-and-errors.go`
**Purpose**: Functions and Go's error handling (no exceptions!)
**Key Concepts**:
- Function declarations
- Multiple return values
- Error handling pattern (no try/catch)
- defer statement
- Named return values

**Run it**:
```bash
go run 03-functions-and-errors.go
```

**What you'll learn**:
- Functions can return multiple values
- Errors are values, not exceptions
- `if err != nil` is the idiomatic error check
- `defer` runs code when function exits

---

### 4. `04-structs-vs-classes.go`
**Purpose**: Understanding Go's struct approach vs PHP classes
**Key Concepts**:
- Struct definitions
- Methods on structs
- No classes, no inheritance
- Composition over inheritance
- Exported vs unexported (public vs private)

**Run it**:
```bash
go run 04-structs-vs-classes.go
```

**What you'll learn**:
- Structs hold data (like PHP classes but simpler)
- Methods are functions with receivers
- Capital letter = exported (public), lowercase = unexported (private)
- Embed structs instead of extending classes

---

### 5. `05-web-server-comparison.go`
**Purpose**: Building a simple web server - PHP vs Go
**Key Concepts**:
- Built-in HTTP server (no Apache/Nginx/PHP-FPM needed)
- Handlers and HTTP routing
- Concurrent request handling
- Native binary deployment

**Run it**:
```bash
go run 05-web-server-comparison.go
# Visit http://localhost:8080
```

**What you'll learn**:
- Go has a built-in HTTP server
- Handlers implement ServeHTTP method
- Each request runs in its own goroutine (concurrent)
- Deploy as a single binary - no PHP runtime needed

---

## Quick Reference: PHP to Go

### Hello World Comparison

**PHP**:
```php
<?php
echo "Hello, World!\n";
```

**Go**:
```go
package main

import "fmt"

func main() {
    fmt.Println("Hello, World!")
}
```

---

### Variable Declarations

**PHP**:
```php
$name = "Alice";        // Dynamic typing
$age = 30;              // Type inferred at runtime
$price = 19.99;
$active = true;
```

**Go**:
```go
var name string = "Alice"  // Explicit type
age := 30                  // Type inferred at compile time
var price float64 = 19.99
active := true
```

---

### Functions

**PHP**:
```php
function add(int $a, int $b): int {
    return $a + $b;
}

$result = add(5, 3);
```

**Go**:
```go
func add(a int, b int) int {
    return a + b
}

result := add(5, 3)
```

---

### Error Handling

**PHP**:
```php
try {
    $data = readFile("config.json");
    echo $data;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

**Go**:
```go
data, err := readFile("config.json")
if err != nil {
    fmt.Printf("Error: %v\n", err)
    return
}
fmt.Println(data)
```

---

### Classes vs Structs

**PHP**:
```php
class User {
    public string $name;
    public int $age;

    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }

    public function greet(): string {
        return "Hello, I'm " . $this->name;
    }
}

$user = new User("Alice", 30);
echo $user->greet();
```

**Go**:
```go
type User struct {
    Name string
    Age  int
}

func NewUser(name string, age int) *User {
    return &User{Name: name, Age: age}
}

func (u *User) Greet() string {
    return fmt.Sprintf("Hello, I'm %s", u.Name)
}

user := NewUser("Alice", 30)
fmt.Println(user.Greet())
```

---

### Arrays/Slices

**PHP**:
```php
$numbers = [1, 2, 3, 4, 5];
$numbers[] = 6;  // Add element
echo count($numbers);

$assoc = ["name" => "Alice", "age" => 30];
echo $assoc["name"];
```

**Go**:
```go
// Slice (dynamic)
numbers := []int{1, 2, 3, 4, 5}
numbers = append(numbers, 6)  // Add element
fmt.Println(len(numbers))

// Map (like PHP associative array)
assoc := map[string]interface{}{
    "name": "Alice",
    "age":  30,
}
fmt.Println(assoc["name"])
```

---

## Key Differences to Remember

### 1. Compilation
- **PHP**: Interpreted (or JIT compiled in PHP 8+)
- **Go**: Compiled to native binary before execution
- **Impact**: Go is much faster at runtime, but requires compilation step

### 2. Type System
- **PHP**: Dynamic, types checked at runtime
- **Go**: Static, types checked at compile time
- **Impact**: Go catches more errors before running, but requires explicit types

### 3. Error Handling
- **PHP**: Exceptions with try/catch
- **Go**: Errors as return values with if err != nil
- **Impact**: Go is more explicit but can be more verbose

### 4. OOP Approach
- **PHP**: Classes with inheritance
- **Go**: Structs with composition and interfaces
- **Impact**: Go is simpler but requires different thinking

### 5. Concurrency
- **PHP**: Requires external tools (ReactPHP, Swoole)
- **Go**: Built-in with goroutines and channels
- **Impact**: Go makes concurrent programming much easier

### 6. Web Deployment
- **PHP**: Needs web server (Apache, Nginx) + PHP-FPM
- **Go**: Standalone HTTP server, deploy single binary
- **Impact**: Go deployment is much simpler

---

## Common Mistakes for PHP Developers

### 1. Forgetting Type Declarations
```go
// ❌ Won't compile
var name = "Alice"
name = 42  // Error: cannot use 42 (type int) as string

// ✅ Correct - stick to one type
var name string = "Alice"
name = "Bob"  // OK
```

### 2. Ignoring Errors
```go
// ❌ Bad practice
data, _ := readFile("config.json")  // Ignoring error!

// ✅ Always check errors
data, err := readFile("config.json")
if err != nil {
    log.Fatal(err)
}
```

### 3. Using := in Wrong Place
```go
// ❌ Can't use := outside functions
package main
name := "Alice"  // Error!

// ✅ Use var outside functions
package main
var name = "Alice"  // OK

func main() {
    age := 30  // OK inside functions
}
```

### 4. Expecting Null
```go
// ❌ No null in Go (there's nil for pointers/interfaces)
var name string
// name is "" (empty string), not null

// ✅ Use pointers if you need nil
var name *string
// name is nil
```

### 5. Trying to Use Classes
```go
// ❌ No classes in Go
class User {  // Error!

// ✅ Use structs
type User struct {
```

---

## Running the Examples

All files can be run directly:

```bash
# Run without compiling
go run 01-hello-from-php-to-go.go

# Compile then run
go build 01-hello-from-php-to-go.go
./01-hello-from-php-to-go

# Run all examples
for file in *.go; do
    echo "Running $file..."
    go run "$file"
    echo "---"
done
```

---

## Next Steps

After completing this chapter, you should:

1. **Understand**: Go's basic syntax and how it differs from PHP
2. **Know**: How to compile and run Go programs
3. **Recognize**: Structs vs classes, errors vs exceptions
4. **Be ready for**: Chapter 01 - Setup & Tooling

---

## Quick Tips for Success

1. **Think Simple**: Go intentionally has fewer features than PHP
2. **Embrace Verbosity**: Explicit error handling is more code, but clearer
3. **Forget OOP Patterns**: Don't try to recreate PHP class hierarchies in Go
4. **Use the Compiler**: Let it catch errors early - it's your friend
5. **Read Error Messages**: Go's compiler errors are very helpful

---

## Resources

- [A Tour of Go](https://go.dev/tour/) - Interactive introduction
- [Go by Example](https://gobyexample.com/) - Code examples
- [Effective Go](https://go.dev/doc/effective_go) - Best practices
- [Go vs PHP](https://go.dev/doc/) - Official documentation

---

**Ready to dive deeper?** Move on to Chapter 01 to set up your Go development environment!
