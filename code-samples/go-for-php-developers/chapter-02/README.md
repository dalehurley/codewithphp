# Chapter 02: Basic Syntax & Types

Master Go's type system, variable declarations, and basic syntax. Learn how Go's static typing differs from PHP's dynamic types and why it catches errors earlier.

## Overview

Go is statically typed - types are checked at compile time, not runtime. This is the biggest shift from PHP. While it requires more explicit code, it catches many bugs before your code even runs.

## Files in This Chapter

### 1. `01-type-system.go`
**Topics**: Basic types, type inference, zero values, type conversions
### 2. `02-constants-and-enums.go`
**Topics**: const declarations, iota, enum patterns
### 3. `03-strings-and-runes.go`
**Topics**: String manipulation, UTF-8, runes vs bytes
### 4. `04-type-conversions.go`
**Topics**: Explicit conversions, type assertions, type switches
### 5. `05-operators.go`
**Topics**: Arithmetic, comparison, logical, bitwise operators
### 6. `06-practical-examples.go`
**Topics**: Real-world type usage patterns

## Quick Reference

### Type Declarations

**PHP**:
```php
$name = "Alice";           // Dynamic
$age = 30;                 // Inferred at runtime
$price = (float)$age;      // Cast
```

**Go**:
```go
var name string = "Alice"  // Explicit
age := 30                  // Inferred at compile time
price := float64(age)      // Convert (not cast)
```

### Basic Types

| Go Type | PHP Equivalent | Zero Value | Notes |
|---------|---------------|------------|-------|
| `bool` | `bool` | `false` | true/false |
| `string` | `string` | `""` | UTF-8 by default |
| `int` | `int` | `0` | Platform dependent (32 or 64-bit) |
| `int8` | - | `0` | -128 to 127 |
| `int16` | - | `0` | -32768 to 32767 |
| `int32` | `int` | `0` | -2B to 2B |
| `int64` | `int` | `0` | Large numbers |
| `uint` | - | `0` | Unsigned int |
| `uint8` | - | `0` | 0 to 255 (byte) |
| `float32` | `float` | `0.0` | 32-bit floating point |
| `float64` | `float` | `0.0` | 64-bit (use this) |
| `byte` | - | `0` | Alias for uint8 |
| `rune` | - | `0` | Alias for int32 (Unicode) |

### Constants

**PHP**:
```php
define('PI', 3.14159);
const MAX_SIZE = 100;
```

**Go**:
```go
const Pi = 3.14159
const MaxSize = 100

// Typed constants
const (
    Active   = 1
    Inactive = 0
)

// Enums with iota
const (
    Sunday = iota  // 0
    Monday         // 1
    Tuesday        // 2
)
```

### String Operations

**PHP**:
```php
$str = "Hello";
$len = strlen($str);
$upper = strtoupper($str);
$concat = $str . " World";
$sub = substr($str, 0, 3);
```

**Go**:
```go
str := "Hello"
length := len(str)                    // Bytes, not chars!
upper := strings.ToUpper(str)
concat := str + " World"
sub := str[0:3]                       // Slice syntax

// For Unicode:
runeCount := utf8.RuneCountInString(str)
```

## Key Concepts

### 1. Zero Values (Not Null!)

Every type has a zero value:
```go
var i int       // 0
var f float64   // 0.0
var b bool      // false
var s string    // ""
var p *int      // nil (pointers can be nil)
```

**PHP Comparison**:
```php
$i;  // null (and a warning)
$i = 0;  // Must initialize
```

### 2. Type Inference with :=

```go
name := "Alice"        // inferred as string
age := 30              // inferred as int
price := 19.99         // inferred as float64
active := true         // inferred as bool

// Can only use := inside functions!
```

### 3. No Automatic Type Conversion

```go
var i int = 42
var f float64 = i      // ❌ Compile error!
var f float64 = float64(i)  // ✅ Explicit conversion
```

### 4. Strings Are UTF-8

```go
s := "Hello, 世界"
fmt.Println(len(s))                    // 13 (bytes)
fmt.Println(utf8.RuneCountInString(s)) // 9 (characters)

// Iterate by runes
for i, r := range s {
    fmt.Printf("%d: %c\n", i, r)
}
```

## Common Patterns

### 1. Multiple Variable Declaration
```go
var (
    name  string = "Alice"
    age   int    = 30
    active bool  = true
)
```

### 2. Short Variable Declaration
```go
x, y := 10, 20
name, age := "Alice", 30
```

### 3. Type Aliases
```go
type UserID int
type Email string

var id UserID = 123
var email Email = "user@example.com"
```

### 4. Enum Pattern with iota
```go
type Status int

const (
    Pending Status = iota  // 0
    Active                 // 1
    Inactive               // 2
    Deleted                // 3
)
```

## Best Practices

1. **Use `int` for integers** (not int32/int64 unless needed)
2. **Use `float64` for decimals** (not float32)
3. **Use `:=` inside functions** for brevity
4. **Use `var` at package level** or when you need zero value
5. **Be explicit with conversions** - never assume

## Next Steps

- **Chapter 03**: Control structures and functions
- **Chapter 04**: Arrays, slices, and maps
- **Chapter 05**: Pointers and memory management

---

**Key Takeaway**: Go's type system is your friend. It catches errors at compile time that PHP would only catch at runtime (or never!).
