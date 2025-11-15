# Go for PHP Developers: Chapters 06-10 Summary

## Part 2: Go Language Features

Master Go's unique language features that differentiate it from PHP: structs instead of classes, implicit interfaces, explicit error handling, and the powerful standard library.

## Overview

**Total Chapters**: 5 (Chapters 06-10)
**Code Files**: 15+ Go files
**Learning Time**: 2-3 weeks
**Prerequisite**: Completion of Part 1 (Chapters 00-05)

## What's Covered

### Chapter 06: Structs & Methods
**Goal**: Master structs and composition over inheritance

**Files**:
- `01-structs-basics.go` - Struct definitions, initialization, embedded fields
- `02-methods-receivers.go` - Value vs pointer receivers, when to use each
- `03-composition-not-inheritance.go` - Building complex types through composition

**Key Concepts**:
```go
// Struct (like a simple PHP class)
type User struct {
    Name  string
    Email string
}

// Method with receiver
func (u *User) SendEmail() error {
    // Implementation
}

// Composition (not inheritance)
type Admin struct {
    User  // Embedded struct
    Role string
}
```

**vs PHP**:
```php
class User {
    public string $name;
    public string $email;

    public function sendEmail(): void {
        // Implementation
    }
}

class Admin extends User {
    public string $role;
}
```

**Key Differences**:
- No classes → Use structs
- No constructors → Use factory functions
- No inheritance → Use composition
- Methods defined outside struct
- Capital = public, lowercase = private

---

### Chapter 07: Interfaces & Polymorphism
**Goal**: Understand Go's implicit interfaces

**Key Concepts**:
```go
// Interface defines behavior
type Writer interface {
    Write(data []byte) error
}

// Any type with Write method implements Writer
// No "implements" keyword needed!
type FileWriter struct { }

func (f *FileWriter) Write(data []byte) error {
    // Implementation
}

// Polymorphism through interfaces
func Save(w Writer, data []byte) error {
    return w.Write(data)
}
```

**vs PHP**:
```php
interface Writer {
    public function write(array $data): void;
}

class FileWriter implements Writer {  // Explicit
    public function write(array $data): void {
        // Implementation
    }
}
```

**Key Differences**:
- Implicit implementation (no "implements")
- Small interfaces (1-3 methods ideal)
- Prefer interfaces over concrete types
- Empty interface{} accepts anything
- Type assertions for concrete types

**Common Patterns**:
- `io.Reader` and `io.Writer` - everywhere!
- `error` interface - standard error handling
- Type switches - check concrete type
- Accept interfaces, return structs

---

### Chapter 08: Error Handling
**Goal**: Master Go's explicit error handling

**The Go Way**:
```go
// Errors are values, not exceptions
func ReadFile(path string) ([]byte, error) {
    data, err := os.ReadFile(path)
    if err != nil {
        return nil, fmt.Errorf("failed to read %s: %w", path, err)
    }
    return data, nil
}

// Usage
data, err := ReadFile("config.json")
if err != nil {
    log.Fatal(err)
}
// Use data
```

**vs PHP**:
```php
function readFile(string $path): string {
    if (!file_exists($path)) {
        throw new Exception("File not found: $path");
    }
    return file_get_contents($path);
}

// Usage
try {
    $data = readFile("config.json");
    // Use data
} catch (Exception $e) {
    die($e->getMessage());
}
```

**Error Handling Patterns**:

1. **Check immediately**:
```go
if err != nil {
    return err
}
```

2. **Wrap errors** (Go 1.13+):
```go
return fmt.Errorf("operation failed: %w", err)
```

3. **Custom errors**:
```go
type ValidationError struct {
    Field string
    Message string
}

func (e *ValidationError) Error() string {
    return fmt.Sprintf("%s: %s", e.Field, e.Message)
}
```

4. **Sentinel errors**:
```go
var ErrNotFound = errors.New("not found")

if errors.Is(err, ErrNotFound) {
    // Handle not found
}
```

**When to panic**:
- Only for unrecoverable errors
- Programming bugs (nil pointer, array out of bounds)
- Initialization failures
- NOT for normal error handling!

---

### Chapter 09: Packages & Modules
**Goal**: Organize code into packages

**Package Structure**:
```
myproject/
├── go.mod                    # Module definition
├── main.go                   # Entry point
├── internal/                 # Private packages
│   ├── handlers/
│   │   └── user.go
│   ├── models/
│   │   └── user.go
│   └── database/
│       └── db.go
└── pkg/                      # Public packages
    └── utils/
        └── helpers.go
```

**vs PHP**:
```
myproject/
├── composer.json
├── index.php
├── src/                      # PSR-4 autoload
│   ├── Controllers/
│   ├── Models/
│   └── Services/
└── vendor/
```

**Key Concepts**:

1. **Package declaration**:
```go
package handlers  // One package per directory
```

2. **Imports**:
```go
import (
    "fmt"                           // Standard library
    "github.com/gin-gonic/gin"      // External
    "myproject/internal/models"     // Internal
)
```

3. **Visibility**:
```go
type User struct {       // Exported (public)
    Name string          // Exported field
    age  int            // Unexported (private)
}

func NewUser() *User { } // Exported function
func validate() error { } // Unexported function
```

4. **Circular imports NOT allowed**:
   - PHP allows, Go doesn't
   - Forces better architecture
   - Use interfaces to break cycles

---

### Chapter 10: Standard Library Tour
**Goal**: Master essential standard library packages

**Essential Packages**:

**strings** - String manipulation:
```go
import "strings"

strings.Contains("hello", "lo")      // true
strings.Split("a,b,c", ",")          // ["a", "b", "c"]
strings.ToUpper("hello")             // "HELLO"
strings.TrimSpace("  text  ")        // "text"
```

**fmt** - Formatting:
```go
import "fmt"

fmt.Printf("%s is %d years old\n", "Alice", 30)
fmt.Sprintf("Hi %s", name)  // Return string
fmt.Println("Hello")        // Print with newline
```

**time** - Time operations:
```go
import "time"

now := time.Now()
future := now.Add(24 * time.Hour)
duration := future.Sub(now)
time.Sleep(1 * time.Second)
```

**io & os** - File operations:
```go
import "os"

// Read file
data, err := os.ReadFile("file.txt")

// Write file
err := os.WriteFile("file.txt", data, 0644)

// Open file
f, err := os.Open("file.txt")
defer f.Close()
```

**encoding/json** - JSON:
```go
import "encoding/json"

// Encode (like json_encode)
data, err := json.Marshal(struct{Name string}{"Alice"})

// Decode (like json_decode)
var user User
err := json.Unmarshal(data, &user)
```

**net/http** - HTTP:
```go
import "net/http"

// HTTP server
http.HandleFunc("/", handler)
http.ListenAndServe(":8080", nil)

// HTTP client
resp, err := http.Get("https://api.example.com")
```

---

## PHP to Go Quick Reference

| Task | PHP | Go |
|------|-----|-----|
| **Define class/struct** | `class User { }` | `type User struct { }` |
| **Constructor** | `__construct()` | `func NewUser()` (convention) |
| **Method** | `public function greet()` | `func (u *User) Greet()` |
| **Interface** | `implements Interface` | Implicit (just have methods) |
| **Inheritance** | `extends BaseClass` | Composition with embedding |
| **Try/catch** | `try { } catch() { }` | `if err != nil { }` |
| **Throw error** | `throw new Exception()` | `return errors.New()` |
| **Import** | `use Namespace\Class;` | `import "package"` |
| **Public** | `public` keyword | Capital letter |
| **Private** | `private` keyword | Lowercase letter |
| **JSON encode** | `json_encode($data)` | `json.Marshal(data)` |
| **JSON decode** | `json_decode($json)` | `json.Unmarshal(data, &v)` |
| **Sleep** | `sleep(1)` | `time.Sleep(1 * time.Second)` |
| **String concat** | `$a . $b` | `a + b` |
| **String format** | `sprintf("%s", $x)` | `fmt.Sprintf("%s", x)` |

## Common Patterns

### 1. Constructor Pattern
```go
type User struct {
    name  string
    email string
}

func NewUser(name, email string) *User {
    return &User{
        name:  name,
        email: email,
    }
}
```

### 2. Interface for Mocking
```go
type UserRepository interface {
    Find(id int) (*User, error)
    Save(user *User) error
}

// Real implementation
type SQLUserRepository struct { }

// Test mock
type MockUserRepository struct { }
```

### 3. Error Wrapping
```go
if err != nil {
    return fmt.Errorf("failed to save user: %w", err)
}
```

### 4. Options Pattern
```go
type Server struct {
    port int
    host string
}

type Option func(*Server)

func WithPort(port int) Option {
    return func(s *Server) {
        s.port = port
    }
}

server := NewServer(
    WithPort(8080),
    WithHost("localhost"),
)
```

## Best Practices

**Structs & Methods**:
1. ✅ Use pointer receivers for modification
2. ✅ Use value receivers for small, immutable types
3. ✅ Be consistent within a type
4. ✅ Favor composition over inheritance
5. ✅ Keep structs focused (single responsibility)

**Interfaces**:
1. ✅ Small interfaces (1-3 methods)
2. ✅ Accept interfaces, return structs
3. ✅ Define interfaces where they're used
4. ✅ Name with -er suffix (Reader, Writer, Handler)
5. ✅ Depend on interfaces, not concrete types

**Error Handling**:
1. ✅ Always check errors
2. ✅ Wrap errors with context
3. ✅ Return errors, don't panic
4. ✅ Use sentinel errors for comparison
5. ✅ Check error types with errors.Is/As

**Packages**:
1. ✅ One package per directory
2. ✅ Use `internal/` for private code
3. ✅ Avoid circular dependencies
4. ✅ Keep package scope focused
5. ✅ Document exported items

## Common Mistakes

### 1. Trying to Use Inheritance
```go
// ❌ No inheritance in Go
type Admin struct {
    User  // This is composition, not inheritance!
}

// ✅ Use composition explicitly
type Admin struct {
    user *User
    role string
}
```

### 2. Forgetting to Return Errors
```go
// ❌ Swallowing errors
data, _ := readFile("config.json")

// ✅ Always handle errors
data, err := readFile("config.json")
if err != nil {
    return fmt.Errorf("failed to read config: %w", err)
}
```

### 3. Making Interfaces Too Large
```go
// ❌ Too many methods
type Repository interface {
    Find()
    Save()
    Delete()
    Update()
    List()
    Count()
}

// ✅ Split into focused interfaces
type Finder interface {
    Find(id int) (*Entity, error)
}

type Saver interface {
    Save(entity *Entity) error
}
```

## What's Next

After mastering Part 2:

### Part 3: Concurrent Programming (Ch 11-15)
- Goroutines and parallelism
- Channels for communication
- Select statement
- Sync primitives
- Concurrent patterns

### Part 4: Web Development (Ch 16-20)
- HTTP servers
- Routing and middleware
- JSON APIs
- Templates
- Web frameworks

---

**Key Takeaway**: Part 2 teaches you to think in Go. Structs replace classes, composition replaces inheritance, interfaces are implicit, and errors are values. Master these concepts and you'll write idiomatic Go code.

---

*Continue to Part 3 to learn Go's killer feature: built-in concurrency!*
