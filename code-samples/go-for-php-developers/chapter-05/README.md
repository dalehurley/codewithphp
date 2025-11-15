# Chapter 05: Pointers & Memory Management

Understanding pointers is crucial in Go. Learn what pointers are, when to use them, and how Go's memory management differs from PHP's reference counting.

## Overview

PHP hides memory management from you. Go exposes it through pointers. While this might seem scary at first, it gives you control over performance and helps you understand what's happening in memory.

## Files in This Chapter

### 1. `01-pointer-basics.go`
**Topics**: What pointers are, & and * operators, nil pointers
### 2. `02-value-vs-pointer.go`
**Topics**: Pass by value vs pointer, when to use each
### 3. `03-pointer-receivers.go`
**Topics**: Value vs pointer receivers on methods
### 4. `04-new-and-make.go`
**Topics**: new() vs make(), when to use each
### 5. `05-common-patterns.go`
**Topics**: Optional values, building graphs, linked structures
### 6. `06-memory-safety.go`
**Topics**: Go's safety guarantees, garbage collection

## Quick Reference

### PHP References

```php
// PHP: References with &
$a = 10;
$b = &$a;  // $b is reference to $a
$b = 20;   // $a is now also 20

// Function by reference
function increment(&$value) {
    $value++;
}

$x = 5;
increment($x);  // $x is now 6
```

### Go Pointers

```go
// Go: Pointers with * and &
a := 10
b := &a   // b is pointer to a
*b = 20   // a is now 20

// Function with pointer parameter
func increment(value *int) {
    *value++
}

x := 5
increment(&x)  // x is now 6
```

## Key Concepts

### 1. Pointer Operators

```go
var x int = 42

// & = "address of"
ptr := &x  // ptr is pointer to x
fmt.Println(ptr)   // Prints memory address: 0x...

// * = "dereference" (get value at address)
value := *ptr  // value is 42
*ptr = 100     // x is now 100

// Type
var ptr *int  // Pointer to int
```

### 2. Zero Value is nil

```go
var ptr *int  // nil pointer

if ptr == nil {
    fmt.Println("Pointer is nil")
}

// ❌ Dereferencing nil panics!
value := *ptr  // Runtime panic!

// ✅ Check first
if ptr != nil {
    value := *ptr
}
```

### 3. Pass by Value vs Pointer

```go
// Pass by value (copy)
func updateValue(x int) {
    x = 100  // Only changes local copy
}

a := 10
updateValue(a)
// a is still 10

// Pass by pointer (reference)
func updatePointer(x *int) {
    *x = 100  // Changes original
}

a := 10
updatePointer(&a)
// a is now 100
```

### 4. Pointer Receivers

```go
type User struct {
    Name string
    Age  int
}

// Value receiver (gets copy)
func (u User) PrintName() {
    fmt.Println(u.Name)
}

// Pointer receiver (can modify)
func (u *User) HaveBirthday() {
    u.Age++  // Modifies original
}

user := User{Name: "Alice", Age: 30}
user.PrintName()    // Works
user.HaveBirthday() // user.Age is now 31
```

### 5. Structs and Pointers

```go
// Create struct
user := User{Name: "Alice", Age: 30}

// Get pointer to struct
ptr := &user
ptr.Name = "Bob"  // Go auto-dereferences!
// Equivalent to: (*ptr).Name = "Bob"

// Create pointer to new struct
user := &User{Name: "Alice", Age: 30}
```

## Common Patterns

### 1. Optional Values (nil = not set)

```go
type Config struct {
    Host    string
    Port    *int     // Optional - can be nil
    Timeout *int     // Optional
}

config := Config{
    Host: "localhost",
    // Port is nil (not set)
}

if config.Port != nil {
    fmt.Printf("Port: %d\n", *config.Port)
} else {
    fmt.Println("Using default port")
}
```

### 2. Factory Functions

```go
func NewUser(name string, age int) *User {
    return &User{
        Name: name,
        Age:  age,
    }
}

// Usage
user := NewUser("Alice", 30)
// Returns pointer to new User
```

### 3. Linked Structures

```go
type Node struct {
    Value int
    Next  *Node  // Pointer to next node
}

// Build linked list
head := &Node{Value: 1}
head.Next = &Node{Value: 2}
head.Next.Next = &Node{Value: 3}

// Traverse
for node := head; node != nil; node = node.Next {
    fmt.Println(node.Value)
}
```

### 4. Modifying Receiver

```go
type Counter struct {
    count int
}

// Must use pointer receiver to modify
func (c *Counter) Increment() {
    c.count++
}

func (c *Counter) Value() int {
    return c.count  // Pointer receiver works for reading too
}

counter := &Counter{}
counter.Increment()
counter.Increment()
fmt.Println(counter.Value())  // 2
```

## new() vs make()

### new()
```go
// Allocates memory, returns pointer
ptr := new(int)  // *int, initialized to 0
*ptr = 42

user := new(User)  // *User, zero values
user.Name = "Alice"
```

### make()
```go
// For slices, maps, channels only
slice := make([]int, 10)      // Slice of length 10
m := make(map[string]int)     // Initialized map
ch := make(chan int, 5)       // Buffered channel

// ❌ Can't use make() with structs
user := make(User)  // Compile error!

// ✅ Use new() or literal
user := new(User)
user := &User{}
```

## When to Use Pointers

### Use Pointers When:

1. **Function needs to modify parameter**
```go
func increment(x *int) {
    *x++
}
```

2. **Avoid copying large structs**
```go
type LargeStruct struct {
    // Many fields...
}

func process(data *LargeStruct) {
    // Efficient - no copy
}
```

3. **Need nil to represent "not set"**
```go
type Config struct {
    Timeout *int  // nil = use default
}
```

4. **Building linked structures**
```go
type Node struct {
    Next *Node
}
```

### Use Values When:

1. **Small, immutable data**
```go
type Point struct {
    X, Y int
}
```

2. **Maps, slices, interfaces** (already reference types)
```go
func addItem(items []string) []string {
    // Already a reference, no need for *[]string
}
```

3. **Default is usually right** - start with values, use pointers when needed

## Memory Management

### Garbage Collection

```go
// Go has automatic garbage collection
user := &User{Name: "Alice"}
// No need to free memory!

// GC runs automatically
// No reference counting like PHP
// No manual memory management like C
```

### Stack vs Heap

```go
func getValue() int {
    x := 42  // On stack (fast)
    return x
}

func getPointer() *int {
    x := 42      // Escapes to heap (slower but safe)
    return &x    // Pointer outlives function
}

// Go's compiler does escape analysis
// You don't need to worry about stack vs heap
```

## Common Mistakes

### 1. Dereferencing nil
```go
var ptr *int
value := *ptr  // ❌ Panic!

// ✅ Check first
if ptr != nil {
    value := *ptr
}
```

### 2. Forgetting & in Function Calls
```go
func update(x *int) {
    *x = 100
}

a := 10
update(a)   // ❌ Type error!
update(&a)  // ✅ Correct
```

### 3. Taking Address of Map Element
```go
m := map[string]int{"age": 30}
ptr := &m["age"]  // ❌ Can't take address!

// ✅ Use temporary variable
age := m["age"]
ptr := &age
```

### 4. Pointer to Loop Variable
```go
var pointers []*int
for i := 0; i < 3; i++ {
    pointers = append(pointers, &i)  // ❌ All point to same i!
}

// ✅ Create new variable
for i := 0; i < 3; i++ {
    i := i  // New variable
    pointers = append(pointers, &i)
}
```

## Best Practices

1. **Accept interfaces, return structs**
```go
// ✅ Good
func NewUser(name string) *User {
    return &User{Name: name}
}

// ❌ Less flexible
func ProcessUser(user *User) { }

// ✅ Better
func ProcessUser(user UserInterface) { }
```

2. **Consistent receiver types**
```go
// ✅ All pointer receivers
func (u *User) GetName() string { }
func (u *User) SetName(name string) { }

// ❌ Mixed - confusing
func (u User) GetName() string { }
func (u *User) SetName(name string) { }
```

3. **Don't over-use pointers**
```go
// ❌ Unnecessary
func add(a *int, b *int) *int {
    result := *a + *b
    return &result
}

// ✅ Simple and clear
func add(a, b int) int {
    return a + b
}
```

## Next Steps

- **Chapter 06**: Structs and methods
- **Chapter 07**: Interfaces and polymorphism
- **Chapter 08**: Error handling patterns

---

**Key Takeaway**: Pointers in Go are simpler than C/C++ but more explicit than PHP. Use them for modifying parameters, avoiding copies, and representing optional values.
