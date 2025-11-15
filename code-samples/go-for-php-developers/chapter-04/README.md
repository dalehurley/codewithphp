# Chapter 04: Arrays, Slices & Maps

Master Go's data structures. Learn how slices differ from PHP arrays, why arrays are fixed-size, and how maps provide key-value storage.

## Overview

This is where Go differs most from PHP. PHP has one array type that does everything. Go has three distinct types:
- **Arrays**: Fixed-size, rarely used directly
- **Slices**: Dynamic-size, what you'll use most
- **Maps**: Key-value pairs (like PHP associative arrays)

## Files in This Chapter

### 1. `01-arrays-vs-slices.go`
**Topics**: Fixed arrays, dynamic slices, make(), append()
### 2. `02-slice-operations.go`
**Topics**: Slicing, copying, capacity, growth
### 3. `03-maps-basics.go`
**Topics**: Creating maps, accessing, deleting, checking existence
### 4. `04-map-patterns.go`
**Topics**: Maps as sets, counting, grouping
### 5. `05-slices-of-structs.go`
**Topics**: Common patterns with complex data
### 6. `06-multidimensional.go`
**Topics**: 2D slices, nested maps
### 7. `07-practical-examples.go`
**Topics**: Real-world data manipulation

## Quick Reference

### PHP Array (Does Everything)

```php
// Indexed array
$numbers = [1, 2, 3, 4, 5];
$numbers[] = 6;  // Append
count($numbers);  // 6

// Associative array
$user = [
    'name' => 'Alice',
    'age' => 30,
    'email' => 'alice@example.com'
];

// Mixed (both!)
$mixed = [
    0 => 'first',
    'key' => 'value',
    1 => 'second'
];
```

### Go: Three Separate Types

#### Slices (Like PHP Indexed Arrays)
```go
// Dynamic array-like structure
numbers := []int{1, 2, 3, 4, 5}
numbers = append(numbers, 6)  // Append
len(numbers)  // 6

// Make slice with capacity
numbers := make([]int, 0, 10)  // length 0, capacity 10
```

#### Maps (Like PHP Associative Arrays)
```go
// Key-value pairs
user := map[string]interface{}{
    "name":  "Alice",
    "age":   30,
    "email": "alice@example.com",
}

// Typed map
user := map[string]string{
    "name":  "Alice",
    "email": "alice@example.com",
}

// Check if key exists
if value, ok := user["name"]; ok {
    fmt.Println(value)
}

// Delete key
delete(user, "email")
```

#### Arrays (Fixed Size - Rarely Used)
```go
// Fixed size, cannot grow
var numbers [5]int
numbers[0] = 1

// Initialize
numbers := [5]int{1, 2, 3, 4, 5}

// Compiler counts size
numbers := [...]int{1, 2, 3, 4, 5}
```

## Key Concepts

### 1. Slices vs Arrays

**Arrays** (fixed size):
```go
var arr [5]int  // Fixed at 5 elements
arr[0] = 1
arr[1] = 2
// Cannot append or resize!
```

**Slices** (dynamic):
```go
var slice []int  // Can grow
slice = append(slice, 1)
slice = append(slice, 2)
// Can keep appending!
```

### 2. Slice Operations

```go
// Create
slice := []int{1, 2, 3, 4, 5}

// Append
slice = append(slice, 6)
slice = append(slice, 7, 8, 9)

// Append another slice
more := []int{10, 11}
slice = append(slice, more...)

// Slice (get sub-slice)
sub := slice[1:4]  // Elements at index 1, 2, 3

// Length and capacity
len(slice)  // Number of elements
cap(slice)  // Underlying array size

// Make with capacity
slice := make([]int, 0, 100)  // len=0, cap=100
```

### 3. Map Operations

```go
// Create
m := make(map[string]int)
m["apple"] = 5
m["banana"] = 3

// Literal
m := map[string]int{
    "apple":  5,
    "banana": 3,
}

// Access
count := m["apple"]  // 5
count := m["missing"]  // 0 (zero value)

// Check existence
if count, exists := m["apple"]; exists {
    fmt.Println("Found:", count)
}

// Delete
delete(m, "apple")

// Iterate
for key, value := range m {
    fmt.Printf("%s: %d\n", key, value)
}

// Length
len(m)  // Number of keys
```

### 4. Nil Slices and Maps

```go
var slice []int    // nil slice
var m map[string]int  // nil map

// Can read from nil slice
len(slice)  // 0

// Can append to nil slice
slice = append(slice, 1)  // Works!

// CANNOT write to nil map!
m["key"] = "value"  // ❌ Panic! Must initialize
m = make(map[string]int)  // ✅ Initialize first
m["key"] = 123  // Now OK
```

## Common Patterns

### 1. Building Slices
```go
// Pre-allocate if size known
slice := make([]int, 0, 100)
for i := 0; i < 100; i++ {
    slice = append(slice, i)
}

// With initial values
slice := []int{1, 2, 3}
slice = append(slice, 4, 5, 6)
```

### 2. Maps as Sets
```go
// Set of unique values
seen := make(map[string]bool)
seen["apple"] = true
seen["banana"] = true

if seen["apple"] {
    fmt.Println("Already seen")
}

// Or use empty struct (no memory)
seen := make(map[string]struct{})
seen["apple"] = struct{}{}

if _, exists := seen["apple"]; exists {
    fmt.Println("Already seen")
}
```

### 3. Counting with Maps
```go
// Count occurrences
counts := make(map[string]int)
words := []string{"apple", "banana", "apple", "cherry"}

for _, word := range words {
    counts[word]++  // Zero value is 0, so this works!
}
// counts["apple"] == 2
```

### 4. Grouping with Maps
```go
// Group by category
groups := make(map[string][]string)
groups["fruit"] = append(groups["fruit"], "apple")
groups["fruit"] = append(groups["fruit"], "banana")
groups["vegetable"] = append(groups["vegetable"], "carrot")
```

### 5. Removing from Slice
```go
// Remove element at index
func remove(slice []int, index int) []int {
    return append(slice[:index], slice[index+1:]...)
}

slice := []int{1, 2, 3, 4, 5}
slice = remove(slice, 2)  // Remove index 2
// slice is now [1, 2, 4, 5]
```

### 6. Filtering Slices
```go
func filter(numbers []int, predicate func(int) bool) []int {
    result := make([]int, 0)
    for _, num := range numbers {
        if predicate(num) {
            result = append(result, num)
        }
    }
    return result
}

// Usage
numbers := []int{1, 2, 3, 4, 5, 6}
evens := filter(numbers, func(n int) bool {
    return n%2 == 0
})
// evens is [2, 4, 6]
```

## Best Practices

### 1. Pre-allocate Slices When Size is Known
```go
// ❌ Inefficient - many reallocations
slice := []int{}
for i := 0; i < 1000; i++ {
    slice = append(slice, i)
}

// ✅ Efficient - one allocation
slice := make([]int, 0, 1000)
for i := 0; i < 1000; i++ {
    slice = append(slice, i)
}
```

### 2. Initialize Maps Before Use
```go
// ❌ Panic!
var m map[string]int
m["key"] = 123  // Runtime panic!

// ✅ Initialize
m := make(map[string]int)
m["key"] = 123
```

### 3. Check Map Existence
```go
// ❌ Can't distinguish zero value from missing
count := m["key"]  // Could be 0 (zero value) or missing

// ✅ Check with comma-ok idiom
if count, ok := m["key"]; ok {
    fmt.Println("Found:", count)
} else {
    fmt.Println("Not found")
}
```

### 4. Copy Slices Properly
```go
// ❌ Shallow copy - shares backing array
a := []int{1, 2, 3}
b := a  // Same underlying array!
b[0] = 99  // a[0] is also 99!

// ✅ Deep copy
a := []int{1, 2, 3}
b := make([]int, len(a))
copy(b, a)  // Now independent
b[0] = 99  // a[0] is still 1
```

## Common Mistakes

### 1. Assuming Slices Copy
```go
original := []int{1, 2, 3}
modified := original
modified[0] = 99
// original[0] is also 99! Same backing array
```

### 2. Range Loop Reuses Variables
```go
var pointers []*int
for _, v := range []int{1, 2, 3} {
    pointers = append(pointers, &v)  // ❌ All point to same v!
}
// All pointers point to 3!

// ✅ Create new variable
for _, v := range []int{1, 2, 3} {
    v := v  // New variable each iteration
    pointers = append(pointers, &v)
}
```

### 3. Map Iteration Order
```go
m := map[string]int{"a": 1, "b": 2, "c": 3}
for k, v := range m {
    fmt.Println(k, v)
}
// ❌ Order is RANDOM! Not insertion order
```

## Performance Tips

### 1. Slice Capacity
```go
// Many reallocations
slice := []int{}
for i := 0; i < 10000; i++ {
    slice = append(slice, i)
}

// One allocation
slice := make([]int, 0, 10000)
for i := 0; i < 10000; i++ {
    slice = append(slice, i)
}
```

### 2. Map Pre-sizing
```go
// Will resize
m := make(map[string]int)
for i := 0; i < 10000; i++ {
    m[fmt.Sprintf("key%d", i)] = i
}

// No resizing
m := make(map[string]int, 10000)
for i := 0; i < 10000; i++ {
    m[fmt.Sprintf("key%d", i)] = i
}
```

## Next Steps

- **Chapter 05**: Pointers and memory management
- **Chapter 06**: Structs and methods
- **Chapter 07**: Interfaces and polymorphism

---

**Key Takeaway**: Go's arrays, slices, and maps are three distinct types. Slices are what you'll use most. Maps must be initialized before use. Both are reference types.
