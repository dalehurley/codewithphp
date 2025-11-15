# Chapter 06: Structs & Methods

Master Go's approach to object-oriented programming through structs and methods. Learn how Go achieves OOP concepts without classes or inheritance.

## Overview

Go doesn't have classes. Instead, it uses structs (data) and methods (behavior). This is simpler than PHP's class system but just as powerful. You can achieve encapsulation, composition, and polymorphism without the complexity of inheritance hierarchies.

## Files in This Chapter

### 1. `01-struct-basics.go`
**Topics**: Struct declaration, initialization, literal syntax, zero values

### 2. `02-methods.go`
**Topics**: Method declaration, value vs pointer receivers, method sets

### 3. `03-constructors.go`
**Topics**: Constructor functions, factory patterns, validation

### 4. `04-composition.go`
**Topics**: Embedding structs, composition over inheritance, promoted fields

### 5. `05-encapsulation.go`
**Topics**: Exported vs unexported fields, getter/setter patterns, package visibility

### 6. `06-json-tags.go`
**Topics**: Struct tags, JSON marshaling/unmarshaling, custom tags

## Quick Reference

### Defining Types

**PHP**:
```php
class User {
    private string $name;
    private int $age;
    private string $email;

    public function __construct(string $name, int $age, string $email) {
        $this->name = $name;
        $this->age = $age;
        $this->email = $email;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }
}

$user = new User("Alice", 30, "alice@example.com");
```

**Go**:
```go
type User struct {
    name  string  // unexported (private)
    age   int     // unexported (private)
    Email string  // exported (public)
}

// Constructor function
func NewUser(name string, age int, email string) *User {
    return &User{
        name:  name,
        age:   age,
        Email: email,
    }
}

// Method (getter)
func (u *User) Name() string {
    return u.name
}

// Method (setter)
func (u *User) SetName(name string) {
    u.name = name
}

user := NewUser("Alice", 30, "alice@example.com")
```

### Methods

**PHP**:
```php
class Counter {
    private int $count = 0;

    public function increment(): void {
        $this->count++;
    }

    public function value(): int {
        return $this->count;
    }
}
```

**Go**:
```go
type Counter struct {
    count int
}

// Pointer receiver - can modify
func (c *Counter) Increment() {
    c.count++
}

// Pointer receiver - read only
func (c *Counter) Value() int {
    return c.count
}
```

### Inheritance vs Composition

**PHP**:
```php
class Animal {
    protected string $name;

    public function speak(): string {
        return "Some sound";
    }
}

class Dog extends Animal {
    public function speak(): string {
        return "Woof!";
    }
}
```

**Go**:
```go
// No inheritance! Use composition
type Animal struct {
    Name string
}

func (a *Animal) Speak() string {
    return "Some sound"
}

type Dog struct {
    Animal  // Embedded field (composition)
}

// Override by defining same method
func (d *Dog) Speak() string {
    return "Woof!"
}

dog := Dog{Animal: Animal{Name: "Buddy"}}
dog.Speak()     // "Woof!"
dog.Name        // "Buddy" (promoted field)
```

## Key Concepts

### 1. Struct Declaration

```go
// Basic struct
type Person struct {
    Name string
    Age  int
}

// Anonymous struct (one-time use)
person := struct {
    Name string
    Age  int
}{
    Name: "Alice",
    Age:  30,
}

// Empty struct (no fields)
type Signal struct{}
```

### 2. Struct Initialization

```go
type User struct {
    Name  string
    Age   int
    Email string
}

// Named fields (recommended)
u1 := User{
    Name:  "Alice",
    Age:   30,
    Email: "alice@example.com",
}

// Positional (not recommended)
u2 := User{"Bob", 25, "bob@example.com"}

// Partial initialization
u3 := User{Name: "Charlie"}  // Age: 0, Email: ""

// Zero value
var u4 User  // All fields are zero values

// Pointer to struct
u5 := &User{Name: "Diana"}
```

### 3. Methods

```go
type Rectangle struct {
    Width  float64
    Height float64
}

// Value receiver (read-only)
func (r Rectangle) Area() float64 {
    return r.Width * r.Height
}

// Pointer receiver (can modify)
func (r *Rectangle) Scale(factor float64) {
    r.Width *= factor
    r.Height *= factor
}

// Usage
rect := Rectangle{Width: 10, Height: 5}
area := rect.Area()          // 50
rect.Scale(2)                // Width: 20, Height: 10
area = rect.Area()           // 200
```

### 4. Value vs Pointer Receivers

```go
type Counter struct {
    count int
}

// Value receiver - operates on copy
func (c Counter) IncrementValue() {
    c.count++  // Only modifies copy!
}

// Pointer receiver - operates on original
func (c *Counter) IncrementPointer() {
    c.count++  // Modifies original
}

counter := Counter{}
counter.IncrementValue()    // count is still 0
counter.IncrementPointer()  // count is now 1
```

### 5. Embedding (Composition)

```go
type Address struct {
    Street string
    City   string
    State  string
}

type Person struct {
    Name    string
    Address Address  // Regular field
}

type Employee struct {
    Name    string
    Address  // Embedded field (no name)
    Salary  int
}

// Usage
person := Person{
    Name: "Alice",
    Address: Address{
        Street: "123 Main St",
        City:   "Boston",
    },
}
person.Address.City  // "Boston"

employee := Employee{
    Name: "Bob",
    Address: Address{
        Street: "456 Oak Ave",
        City:   "NYC",
    },
    Salary: 75000,
}
employee.City  // "NYC" (promoted field!)
employee.Address.City  // Also works
```

### 6. Method Promotion

```go
type Engine struct {
    Power int
}

func (e *Engine) Start() {
    fmt.Println("Engine started")
}

type Car struct {
    Engine  // Embedded
    Model  string
}

car := Car{
    Engine: Engine{Power: 200},
    Model:  "Tesla",
}

// Promoted method - can call on Car
car.Start()  // "Engine started"

// Same as
car.Engine.Start()
```

## Common Patterns

### 1. Constructor Functions

```go
type User struct {
    id    int
    name  string
    email string
}

// Simple constructor
func NewUser(name, email string) *User {
    return &User{
        name:  name,
        email: email,
    }
}

// Constructor with validation
func NewUserValidated(name, email string) (*User, error) {
    if name == "" {
        return nil, errors.New("name required")
    }
    if !strings.Contains(email, "@") {
        return nil, errors.New("invalid email")
    }
    return &User{
        name:  name,
        email: email,
    }, nil
}

// Constructor with options
type UserOptions struct {
    Name  string
    Email string
    Age   int
}

func NewUserWithOptions(opts UserOptions) *User {
    return &User{
        name:  opts.Name,
        email: opts.Email,
    }
}
```

### 2. Functional Options Pattern

```go
type Server struct {
    host    string
    port    int
    timeout time.Duration
}

type ServerOption func(*Server)

func WithHost(host string) ServerOption {
    return func(s *Server) {
        s.host = host
    }
}

func WithPort(port int) ServerOption {
    return func(s *Server) {
        s.port = port
    }
}

func WithTimeout(timeout time.Duration) ServerOption {
    return func(s *Server) {
        s.timeout = timeout
    }
}

func NewServer(opts ...ServerOption) *Server {
    // Defaults
    s := &Server{
        host:    "localhost",
        port:    8080,
        timeout: 30 * time.Second,
    }

    // Apply options
    for _, opt := range opts {
        opt(s)
    }

    return s
}

// Usage
server := NewServer(
    WithHost("0.0.0.0"),
    WithPort(3000),
)
```

### 3. Builder Pattern

```go
type Query struct {
    table  string
    fields []string
    where  string
    limit  int
}

type QueryBuilder struct {
    query Query
}

func NewQueryBuilder(table string) *QueryBuilder {
    return &QueryBuilder{
        query: Query{table: table},
    }
}

func (qb *QueryBuilder) Select(fields ...string) *QueryBuilder {
    qb.query.fields = fields
    return qb
}

func (qb *QueryBuilder) Where(condition string) *QueryBuilder {
    qb.query.where = condition
    return qb
}

func (qb *QueryBuilder) Limit(n int) *QueryBuilder {
    qb.query.limit = n
    return qb
}

func (qb *QueryBuilder) Build() Query {
    return qb.query
}

// Usage (fluent interface)
query := NewQueryBuilder("users").
    Select("id", "name", "email").
    Where("age > 18").
    Limit(10).
    Build()
```

### 4. Encapsulation with Getters/Setters

```go
type Account struct {
    balance float64  // unexported (private)
}

func NewAccount(initial float64) *Account {
    return &Account{balance: initial}
}

// Getter
func (a *Account) Balance() float64 {
    return a.balance
}

// Setter with validation
func (a *Account) Deposit(amount float64) error {
    if amount <= 0 {
        return errors.New("deposit must be positive")
    }
    a.balance += amount
    return nil
}

func (a *Account) Withdraw(amount float64) error {
    if amount <= 0 {
        return errors.New("withdrawal must be positive")
    }
    if amount > a.balance {
        return errors.New("insufficient funds")
    }
    a.balance -= amount
    return nil
}
```

### 5. Struct Tags for JSON

```go
type User struct {
    ID        int       `json:"id"`
    Name      string    `json:"name"`
    Email     string    `json:"email"`
    Password  string    `json:"-"`              // Never serialize
    CreatedAt time.Time `json:"created_at"`
    UpdatedAt time.Time `json:"updated_at,omitempty"`  // Omit if zero
}

user := User{
    ID:    1,
    Name:  "Alice",
    Email: "alice@example.com",
}

// Marshal to JSON
data, err := json.Marshal(user)
// {"id":1,"name":"Alice","email":"alice@example.com","created_at":"..."}

// Unmarshal from JSON
var user2 User
err = json.Unmarshal(data, &user2)
```

### 6. Anonymous Fields for Extension

```go
type BaseModel struct {
    ID        int
    CreatedAt time.Time
    UpdatedAt time.Time
}

type User struct {
    BaseModel  // Embedded - all fields promoted
    Name      string
    Email     string
}

type Post struct {
    BaseModel  // Same base fields
    Title     string
    Content   string
    AuthorID  int
}

user := User{
    BaseModel: BaseModel{ID: 1},
    Name:      "Alice",
}
user.ID        // Promoted from BaseModel
user.CreatedAt // Promoted from BaseModel
```

## Best Practices

### 1. Use Pointer Receivers for Methods

```go
type User struct {
    Name string
    Age  int
}

// ✅ Pointer receiver (can modify, no copy)
func (u *User) SetName(name string) {
    u.Name = name
}

// ✅ Pointer receiver (even for read - consistency)
func (u *User) GetName() string {
    return u.Name
}

// ❌ Mixed receivers (confusing)
func (u User) GetAge() int {
    return u.Age
}
```

**Rule**: If any method needs a pointer receiver, use pointer receivers for ALL methods on that type.

### 2. Return Pointers from Constructors

```go
// ✅ Return pointer
func NewUser(name string) *User {
    return &User{Name: name}
}

// ❌ Return value (less flexible)
func NewUser(name string) User {
    return User{Name: name}
}
```

### 3. Use Named Fields in Literals

```go
type Config struct {
    Host    string
    Port    int
    Timeout time.Duration
}

// ✅ Named fields (clear and maintainable)
cfg := Config{
    Host:    "localhost",
    Port:    8080,
    Timeout: 30 * time.Second,
}

// ❌ Positional (fragile)
cfg := Config{"localhost", 8080, 30 * time.Second}
```

### 4. Keep Structs Simple

```go
// ✅ Simple, focused struct
type User struct {
    ID    int
    Name  string
    Email string
}

// ❌ Too many responsibilities
type User struct {
    ID       int
    Name     string
    Email    string
    DB       *Database
    Cache    *Cache
    Logger   *Logger
    Validator *Validator
}
```

### 5. Use Composition Over Inheritance

```go
// ✅ Composition
type Logger struct {
    prefix string
}

type Service struct {
    logger Logger
    db     Database
}

// ❌ Don't try to simulate inheritance
type BaseService struct {
    logger Logger
}

type UserService struct {
    BaseService  // This works but isn't idiomatic
}
```

### 6. Validate in Constructors

```go
func NewUser(name, email string) (*User, error) {
    if name == "" {
        return nil, errors.New("name required")
    }
    if !isValidEmail(email) {
        return nil, errors.New("invalid email")
    }
    return &User{
        Name:  name,
        Email: email,
    }, nil
}
```

## Common Mistakes

### 1. Forgetting Pointer Receivers

```go
type Counter struct {
    count int
}

// ❌ Value receiver - doesn't modify original
func (c Counter) Increment() {
    c.count++  // Only modifies copy!
}

// ✅ Pointer receiver
func (c *Counter) Increment() {
    c.count++
}
```

### 2. Not Exporting Constructor

```go
type user struct {  // unexported
    Name string
}

// ❌ Can't create from other packages!
func newUser(name string) *user {
    return &user{Name: name}
}

// ✅ Export constructor for unexported type
func NewUser(name string) *user {
    return &user{Name: name}
}
```

### 3. Mixing Pointer and Value Receivers

```go
type User struct {
    Name string
}

// ❌ Inconsistent
func (u *User) SetName(name string) {
    u.Name = name
}

func (u User) GetName() string {  // Value receiver
    return u.Name
}

// ✅ Consistent - all pointer receivers
func (u *User) SetName(name string) {
    u.Name = name
}

func (u *User) GetName() string {
    return u.Name
}
```

### 4. Not Checking Nil Pointers

```go
type User struct {
    Name string
}

func (u *User) PrintName() {
    fmt.Println(u.Name)  // ❌ Panics if u is nil!
}

// ✅ Check for nil
func (u *User) PrintName() {
    if u == nil {
        return
    }
    fmt.Println(u.Name)
}
```

### 5. Overusing Getters/Setters

```go
type Point struct {
    x, y int  // Private for no reason
}

func (p *Point) X() int { return p.x }
func (p *Point) Y() int { return p.y }
func (p *Point) SetX(x int) { p.x = x }
func (p *Point) SetY(y int) { p.y = y }

// ✅ Just export the fields!
type Point struct {
    X, Y int
}
```

### 6. Embedding When You Should Compose

```go
// ❌ Too much promoted
type Service struct {
    *Database  // All Database methods promoted!
}

// ✅ Explicit composition
type Service struct {
    db *Database
}

func (s *Service) GetUser(id int) (*User, error) {
    return s.db.FindUser(id)
}
```

## Advanced Patterns

### 1. Method Chaining

```go
type Builder struct {
    data map[string]interface{}
}

func NewBuilder() *Builder {
    return &Builder{data: make(map[string]interface{})}
}

func (b *Builder) Set(key string, value interface{}) *Builder {
    b.data[key] = value
    return b
}

func (b *Builder) Build() map[string]interface{} {
    return b.data
}

// Usage
result := NewBuilder().
    Set("name", "Alice").
    Set("age", 30).
    Set("email", "alice@example.com").
    Build()
```

### 2. Type Embedding for Mixins

```go
type Timestamped struct {
    CreatedAt time.Time
    UpdatedAt time.Time
}

func (t *Timestamped) Touch() {
    now := time.Now()
    if t.CreatedAt.IsZero() {
        t.CreatedAt = now
    }
    t.UpdatedAt = now
}

type User struct {
    Timestamped  // Mixin
    ID    int
    Name  string
}

type Post struct {
    Timestamped  // Same mixin
    ID      int
    Title   string
}

user := &User{Name: "Alice"}
user.Touch()  // Sets timestamps

post := &Post{Title: "Hello"}
post.Touch()  // Same method
```

### 3. Struct Composition for Testing

```go
type UserStore interface {
    GetUser(id int) (*User, error)
    SaveUser(*User) error
}

type Service struct {
    users UserStore  // Interface, not concrete type
}

func NewService(users UserStore) *Service {
    return &Service{users: users}
}

// Real implementation
type DBUserStore struct {
    db *sql.DB
}

func (s *DBUserStore) GetUser(id int) (*User, error) {
    // Database logic
}

// Mock for testing
type MockUserStore struct {
    users map[int]*User
}

func (s *MockUserStore) GetUser(id int) (*User, error) {
    return s.users[id], nil
}
```

### 4. Struct with Private State

```go
type Cache struct {
    mu    sync.RWMutex
    items map[string]interface{}
}

func NewCache() *Cache {
    return &Cache{
        items: make(map[string]interface{}),
    }
}

func (c *Cache) Get(key string) (interface{}, bool) {
    c.mu.RLock()
    defer c.mu.RUnlock()
    val, ok := c.items[key]
    return val, ok
}

func (c *Cache) Set(key string, value interface{}) {
    c.mu.Lock()
    defer c.mu.Unlock()
    c.items[key] = value
}
```

## Comparison with PHP

### PHP Classes vs Go Structs

| Feature | PHP | Go |
|---------|-----|-----|
| Definition | `class User { }` | `type User struct { }` |
| Instantiation | `new User()` | `User{}` or `&User{}` |
| Constructor | `__construct()` | `NewUser()` function |
| Properties | `private $name` | `name string` (unexported) |
| Methods | `public function getName()` | `func (u *User) Name()` |
| Inheritance | `extends` | No inheritance (use composition) |
| Interfaces | `implements` | Implicit (no declaration needed) |
| Visibility | `public/private/protected` | Capitalization (exported/unexported) |
| Static | `static` keyword | Package-level functions |
| `this` | `$this` | Receiver name (e.g., `u`) |

### Key Differences

1. **No Inheritance**: Go has no `extends`. Use composition with embedding.

2. **No Constructors**: Use factory functions like `NewUser()`.

3. **No `$this`**: Use receiver variable (any name, typically first letter).

4. **Visibility by Capitalization**:
   - `Name` (exported = public)
   - `name` (unexported = private)

5. **Methods Defined Outside**: Methods are defined outside struct, not inside.

6. **Value vs Pointer**: You control whether receivers are values or pointers.

## Next Steps

- **Chapter 07**: Interfaces & Polymorphism - How Go achieves polymorphism without inheritance
- **Chapter 08**: Error Handling - Idiomatic error handling with multiple returns
- **Chapter 09**: Packages & Modules - Organizing code and managing dependencies

---

**Key Takeaway**: Go's structs and methods are simpler than PHP classes but achieve the same goals. Focus on composition over inheritance, and use pointer receivers consistently. The lack of inheritance is a feature, not a limitation.
