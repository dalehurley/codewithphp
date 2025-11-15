# Chapter 07: Interfaces & Polymorphism

Discover Go's powerful implicit interfaces and how they enable polymorphism without inheritance. Learn why "accept interfaces, return structs" is a core Go principle.

## Overview

Interfaces in Go are fundamentally different from PHP interfaces. They're implicit (no `implements` keyword), can be satisfied by any type, and are the key to writing flexible, testable code. Go proves you don't need inheritance to achieve polymorphism.

## Files in This Chapter

### 1. `01-interface-basics.go`
**Topics**: Interface declaration, implicit satisfaction, empty interface

### 2. `02-common-interfaces.go`
**Topics**: io.Reader, io.Writer, fmt.Stringer, error interface

### 3. `03-type-assertions.go`
**Topics**: Type assertions, type switches, checking interface satisfaction

### 4. `04-interface-composition.go`
**Topics**: Embedding interfaces, io.ReadWriter, composing behaviors

### 5. `05-polymorphism.go`
**Topics**: Polymorphic functions, duck typing, interface-based design

### 6. `06-testing-with-interfaces.go`
**Topics**: Mocking, dependency injection, testable code

## Quick Reference

### Interface Declaration

**PHP**:
```php
interface Logger {
    public function log(string $message): void;
    public function error(string $message): void;
}

class FileLogger implements Logger {
    public function log(string $message): void {
        file_put_contents('app.log', $message, FILE_APPEND);
    }

    public function error(string $message): void {
        file_put_contents('error.log', $message, FILE_APPEND);
    }
}

function writeLog(Logger $logger, string $msg): void {
    $logger->log($msg);
}

$logger = new FileLogger();
writeLog($logger, "Hello");
```

**Go**:
```go
// Interface declaration
type Logger interface {
    Log(message string)
    Error(message string)
}

// Implicit implementation (no "implements" keyword!)
type FileLogger struct {
    path string
}

func (f *FileLogger) Log(message string) {
    // Write to file
}

func (f *FileLogger) Error(message string) {
    // Write to error file
}

// Function accepts interface
func writeLog(logger Logger, msg string) {
    logger.Log(msg)
}

logger := &FileLogger{path: "app.log"}
writeLog(logger, "Hello")
```

### Empty Interface

**PHP**:
```php
// No direct equivalent
// Use "mixed" type in PHP 8+
function process(mixed $value): void {
    // ...
}
```

**Go**:
```go
// interface{} accepts any type
func process(value interface{}) {
    // ...
}

// Go 1.18+ - use "any" (alias for interface{})
func process(value any) {
    // ...
}

// Examples
process(42)
process("hello")
process([]int{1, 2, 3})
```

## Key Concepts

### 1. Implicit Interface Satisfaction

```go
// Interface
type Writer interface {
    Write(data []byte) (int, error)
}

// Type satisfies interface implicitly
type FileWriter struct {
    path string
}

func (f *FileWriter) Write(data []byte) (int, error) {
    // Write to file
    return len(data), nil
}

// No "implements" keyword needed!
// FileWriter automatically satisfies Writer interface
var w Writer = &FileWriter{path: "output.txt"}
```

### 2. Interface Definition

```go
// Single method
type Stringer interface {
    String() string
}

// Multiple methods
type ReadWriter interface {
    Read(p []byte) (n int, err error)
    Write(p []byte) (n int, err error)
}

// Empty interface (any type)
type Any interface{}

// Go 1.18+ alias
type Any = any
```

### 3. Interface Satisfaction Rules

```go
type Speaker interface {
    Speak() string
}

// ✅ Pointer receiver satisfies interface
type Dog struct {
    name string
}

func (d *Dog) Speak() string {
    return "Woof!"
}

var s Speaker = &Dog{name: "Buddy"}  // ✅ Works
// var s Speaker = Dog{name: "Buddy"}   // ❌ Won't compile!

// ✅ Value receiver satisfies for both
type Cat struct {
    name string
}

func (c Cat) Speak() string {
    return "Meow!"
}

var s1 Speaker = Cat{name: "Whiskers"}   // ✅ Works
var s2 Speaker = &Cat{name: "Whiskers"}  // ✅ Also works
```

### 4. Type Assertions

```go
var i interface{} = "hello"

// Type assertion
s := i.(string)  // s = "hello"
fmt.Println(s)

// Type assertion with check
s, ok := i.(string)
if ok {
    fmt.Println(s)  // Safe
}

// Will panic if wrong type
n := i.(int)  // ❌ Panic: interface is string, not int

// Safe version
n, ok := i.(int)
if !ok {
    fmt.Println("Not an int")
}
```

### 5. Type Switches

```go
func describe(i interface{}) string {
    switch v := i.(type) {
    case int:
        return fmt.Sprintf("Integer: %d", v)
    case string:
        return fmt.Sprintf("String: %s", v)
    case bool:
        return fmt.Sprintf("Boolean: %t", v)
    case nil:
        return "Nil"
    default:
        return fmt.Sprintf("Unknown type: %T", v)
    }
}

describe(42)      // "Integer: 42"
describe("hello") // "String: hello"
describe(true)    // "Boolean: true"
```

### 6. Common Standard Interfaces

```go
// Stringer - for printing
type Stringer interface {
    String() string
}

type Person struct {
    Name string
    Age  int
}

func (p Person) String() string {
    return fmt.Sprintf("%s (%d years old)", p.Name, p.Age)
}

p := Person{Name: "Alice", Age: 30}
fmt.Println(p)  // "Alice (30 years old)"

// Error - for errors
type error interface {
    Error() string
}

// Reader - for reading
type Reader interface {
    Read(p []byte) (n int, err error)
}

// Writer - for writing
type Writer interface {
    Write(p []byte) (n int, err error)
}
```

## Common Patterns

### 1. Accept Interfaces, Return Structs

```go
// ✅ Good - accept interface
func ProcessData(r io.Reader) error {
    data, err := io.ReadAll(r)
    if err != nil {
        return err
    }
    // Process data
    return nil
}

// ✅ Good - return concrete type
func NewFileReader(path string) (*FileReader, error) {
    f, err := os.Open(path)
    if err != nil {
        return nil, err
    }
    return &FileReader{file: f}, nil
}

// ❌ Less flexible - accept concrete type
func ProcessData(f *FileReader) error {
    // Can only use FileReader!
}

// ❌ Less flexible - return interface
func NewFileReader(path string) (io.Reader, error) {
    // Can't add methods to returned value
}
```

### 2. Small Interfaces

```go
// ✅ Good - single method
type Validator interface {
    Validate() error
}

// ✅ Good - single responsibility
type Closer interface {
    Close() error
}

// ❌ Too big - hard to implement
type Repository interface {
    Create(item interface{}) error
    Read(id int) (interface{}, error)
    Update(id int, item interface{}) error
    Delete(id int) error
    List() ([]interface{}, error)
    Count() (int, error)
    Search(query string) ([]interface{}, error)
}

// ✅ Better - compose small interfaces
type Creator interface {
    Create(item interface{}) error
}

type Reader interface {
    Read(id int) (interface{}, error)
}

type Repository interface {
    Creator
    Reader
    Updater
    Deleter
}
```

### 3. Interface Composition

```go
// Compose from smaller interfaces
type Reader interface {
    Read(p []byte) (n int, err error)
}

type Writer interface {
    Write(p []byte) (n int, err error)
}

type Closer interface {
    Close() error
}

// Composed interfaces
type ReadWriter interface {
    Reader
    Writer
}

type ReadCloser interface {
    Reader
    Closer
}

type WriteCloser interface {
    Writer
    Closer
}

type ReadWriteCloser interface {
    Reader
    Writer
    Closer
}
```

### 4. Dependency Injection

```go
// Define interfaces for dependencies
type UserStore interface {
    GetUser(id int) (*User, error)
    SaveUser(user *User) error
}

type EmailService interface {
    SendEmail(to, subject, body string) error
}

// Service depends on interfaces
type UserService struct {
    store UserStore
    email EmailService
}

func NewUserService(store UserStore, email EmailService) *UserService {
    return &UserService{
        store: store,
        email: email,
    }
}

func (s *UserService) RegisterUser(user *User) error {
    if err := s.store.SaveUser(user); err != nil {
        return err
    }
    return s.email.SendEmail(user.Email, "Welcome!", "Thanks for registering")
}

// Easy to test with mocks
type MockUserStore struct{}
func (m *MockUserStore) GetUser(id int) (*User, error) { return nil, nil }
func (m *MockUserStore) SaveUser(user *User) error { return nil }

type MockEmailService struct{}
func (m *MockEmailService) SendEmail(to, subject, body string) error { return nil }
```

### 5. Strategy Pattern

```go
type PaymentProcessor interface {
    ProcessPayment(amount float64) error
}

type CreditCardProcessor struct{}
func (c *CreditCardProcessor) ProcessPayment(amount float64) error {
    fmt.Printf("Processing $%.2f via credit card\n", amount)
    return nil
}

type PayPalProcessor struct{}
func (p *PayPalProcessor) ProcessPayment(amount float64) error {
    fmt.Printf("Processing $%.2f via PayPal\n", amount)
    return nil
}

type Checkout struct {
    processor PaymentProcessor
}

func (c *Checkout) Pay(amount float64) error {
    return c.processor.ProcessPayment(amount)
}

// Usage
checkout := &Checkout{processor: &CreditCardProcessor{}}
checkout.Pay(99.99)

// Change strategy
checkout.processor = &PayPalProcessor{}
checkout.Pay(49.99)
```

### 6. Polymorphic Collections

```go
type Shape interface {
    Area() float64
    Perimeter() float64
}

type Rectangle struct {
    Width, Height float64
}

func (r Rectangle) Area() float64 {
    return r.Width * r.Height
}

func (r Rectangle) Perimeter() float64 {
    return 2 * (r.Width + r.Height)
}

type Circle struct {
    Radius float64
}

func (c Circle) Area() float64 {
    return math.Pi * c.Radius * c.Radius
}

func (c Circle) Perimeter() float64 {
    return 2 * math.Pi * c.Radius
}

// Polymorphic slice
shapes := []Shape{
    Rectangle{Width: 10, Height: 5},
    Circle{Radius: 7},
    Rectangle{Width: 3, Height: 4},
}

totalArea := 0.0
for _, shape := range shapes {
    totalArea += shape.Area()
}
```

## Best Practices

### 1. Keep Interfaces Small

```go
// ✅ Small, focused
type Reader interface {
    Read(p []byte) (n int, err error)
}

// ✅ Single method
type Stringer interface {
    String() string
}

// ❌ Too many methods
type DataManager interface {
    Create(item interface{}) error
    Read(id int) (interface{}, error)
    Update(id int, item interface{}) error
    Delete(id int) error
    List() ([]interface{}, error)
    Validate(item interface{}) error
    Transform(item interface{}) interface{}
}
```

### 2. Define Interfaces Where You Use Them

```go
// ❌ Don't define in the implementation package
package database

type UserRepository interface {
    GetUser(id int) (*User, error)
}

type MySQLUserRepository struct{}
func (r *MySQLUserRepository) GetUser(id int) (*User, error) { ... }

// ✅ Define in the consumer package
package service

type UserStore interface {  // Define what YOU need
    GetUser(id int) (*User, error)
}

type UserService struct {
    store UserStore
}
```

### 3. Accept Interfaces, Return Concrete Types

```go
// ✅ Accept interface (flexible)
func ProcessData(r io.Reader) (*Result, error) {
    // Can accept any Reader
}

// ✅ Return concrete type (clear)
func NewService() *Service {
    return &Service{}
}

// ❌ Return interface (less clear)
func NewService() ServiceInterface {
    return &Service{}
}
```

### 4. Use Empty Interface Sparingly

```go
// ❌ Overuse
func Process(data interface{}) interface{} {
    // Type checking needed everywhere
}

// ✅ Use specific types
func Process(data *Data) *Result {
    // Type-safe
}

// ✅ Use generics (Go 1.18+)
func Process[T any](data T) T {
    // Generic but type-safe
}
```

### 5. Check Interface Satisfaction at Compile Time

```go
type Writer interface {
    Write(p []byte) (n int, err error)
}

type MyWriter struct{}

func (m *MyWriter) Write(p []byte) (n int, err error) {
    return len(p), nil
}

// Compile-time check
var _ Writer = (*MyWriter)(nil)  // ✅ Verifies MyWriter implements Writer

// If MyWriter doesn't satisfy Writer, this line won't compile
```

## Common Mistakes

### 1. Pointer vs Value Receivers

```go
type Printer interface {
    Print() string
}

type Document struct {
    content string
}

// Pointer receiver
func (d *Document) Print() string {
    return d.content
}

// ❌ Won't work with value
var p Printer = Document{content: "hello"}  // Compile error!

// ✅ Works with pointer
var p Printer = &Document{content: "hello"}
```

### 2. Interface Nil vs Value Nil

```go
type Printer interface {
    Print() string
}

type MyPrinter struct{}
func (m *MyPrinter) Print() string { return "hello" }

var p *MyPrinter = nil

var i Printer = p
fmt.Println(i == nil)  // ❌ False! (interface is not nil)
fmt.Println(p == nil)  // ✅ True (pointer is nil)

// ✅ Check properly
if i == nil || reflect.ValueOf(i).IsNil() {
    // Handle nil
}
```

### 3. Type Assertion Without Check

```go
var i interface{} = "hello"

// ❌ Panics if wrong type
n := i.(int)  // Panic!

// ✅ Check first
n, ok := i.(int)
if !ok {
    fmt.Println("Not an int")
    return
}
```

### 4. Too Large Interfaces

```go
// ❌ Too big - hard to implement and test
type Service interface {
    CreateUser(user User) error
    GetUser(id int) (User, error)
    UpdateUser(user User) error
    DeleteUser(id int) error
    SendEmail(to, subject, body string) error
    ProcessPayment(amount float64) error
    GenerateReport() (Report, error)
}

// ✅ Split into smaller interfaces
type UserStore interface {
    CreateUser(user User) error
    GetUser(id int) (User, error)
}

type EmailSender interface {
    SendEmail(to, subject, body string) error
}

type PaymentProcessor interface {
    ProcessPayment(amount float64) error
}
```

### 5. Returning Interface from Constructor

```go
// ❌ Less flexible
func NewService() ServiceInterface {
    return &Service{}
}

// Caller can't access concrete methods
service := NewService()
// Can only use ServiceInterface methods

// ✅ Return concrete type
func NewService() *Service {
    return &Service{}
}

// Caller can use all methods
service := NewService()
service.ConcreteMethod()  // Can access

// Can still use as interface
var i ServiceInterface = service
```

## Advanced Patterns

### 1. Adapter Pattern

```go
// Third-party interface
type LegacyPrinter interface {
    PrintDocument(doc string)
}

// Your interface
type Printer interface {
    Print(content string) error
}

// Adapter
type PrinterAdapter struct {
    legacy LegacyPrinter
}

func (a *PrinterAdapter) Print(content string) error {
    a.legacy.PrintDocument(content)
    return nil
}

// Usage
legacy := &OldPrinter{}
adapter := &PrinterAdapter{legacy: legacy}
var printer Printer = adapter
printer.Print("Hello")
```

### 2. Decorator Pattern

```go
type Handler interface {
    Handle(req Request) Response
}

type LoggingHandler struct {
    next Handler
}

func (h *LoggingHandler) Handle(req Request) Response {
    log.Printf("Request: %v", req)
    resp := h.next.Handle(req)
    log.Printf("Response: %v", resp)
    return resp
}

type CachingHandler struct {
    next  Handler
    cache map[string]Response
}

func (h *CachingHandler) Handle(req Request) Response {
    if cached, ok := h.cache[req.Key]; ok {
        return cached
    }
    resp := h.next.Handle(req)
    h.cache[req.Key] = resp
    return resp
}

// Usage - wrap handlers
handler := &LoggingHandler{
    next: &CachingHandler{
        next:  &ActualHandler{},
        cache: make(map[string]Response),
    },
}
```

### 3. Interface Segregation

```go
// Large interface
type Repository interface {
    Create(item interface{}) error
    Read(id int) (interface{}, error)
    Update(id int, item interface{}) error
    Delete(id int) error
}

// ✅ Segregate into smaller interfaces
type Creator interface {
    Create(item interface{}) error
}

type Reader interface {
    Read(id int) (interface{}, error)
}

type Updater interface {
    Update(id int, item interface{}) error
}

type Deleter interface {
    Delete(id int) error
}

// Compose as needed
type ReadOnlyRepository interface {
    Reader
}

type FullRepository interface {
    Creator
    Reader
    Updater
    Deleter
}
```

## Testing with Interfaces

### 1. Mock Implementation

```go
// Production interface
type EmailSender interface {
    Send(to, subject, body string) error
}

// Production implementation
type SMTPSender struct {
    host string
}

func (s *SMTPSender) Send(to, subject, body string) error {
    // Actually send email
    return nil
}

// Mock for testing
type MockEmailSender struct {
    SentEmails []Email
}

func (m *MockEmailSender) Send(to, subject, body string) error {
    m.SentEmails = append(m.SentEmails, Email{
        To:      to,
        Subject: subject,
        Body:    body,
    })
    return nil
}

// Test
func TestUserRegistration(t *testing.T) {
    mock := &MockEmailSender{}
    service := NewUserService(mock)

    service.RegisterUser(&User{Email: "test@example.com"})

    if len(mock.SentEmails) != 1 {
        t.Error("Expected 1 email to be sent")
    }
}
```

### 2. Table-Driven Tests with Interfaces

```go
func TestShapeArea(t *testing.T) {
    tests := []struct {
        name     string
        shape    Shape
        expected float64
    }{
        {
            name:     "rectangle",
            shape:    Rectangle{Width: 10, Height: 5},
            expected: 50,
        },
        {
            name:     "circle",
            shape:    Circle{Radius: 5},
            expected: math.Pi * 25,
        },
    }

    for _, tt := range tests {
        t.Run(tt.name, func(t *testing.T) {
            area := tt.shape.Area()
            if area != tt.expected {
                t.Errorf("got %f, want %f", area, tt.expected)
            }
        })
    }
}
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Declaration | `interface Logger { }` | `type Logger interface { }` |
| Implementation | `class X implements Logger` | Implicit (just add methods) |
| Keyword | `implements` required | No keyword needed |
| Multiple interfaces | `implements A, B, C` | Automatic if methods match |
| Type checking | Runtime | Compile-time |
| Empty interface | No equivalent | `interface{}` or `any` |
| Interface composition | Limited | Built-in with embedding |
| Duck typing | No (strict types) | Yes (implicit satisfaction) |

## Next Steps

- **Chapter 08**: Error Handling - Using interfaces for custom errors
- **Chapter 09**: Packages & Modules - Designing package interfaces
- **Chapter 11**: Goroutines - Interfaces for concurrent code

---

**Key Takeaway**: Go interfaces are implicit, small, and powerful. They enable polymorphism without inheritance and are the foundation of testable, flexible code. Remember: "Accept interfaces, return concrete types."
