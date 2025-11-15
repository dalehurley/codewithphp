---
title: "03: Ownership and Borrowing"
description: "Master Rust's unique ownership system - the concept that makes Rust memory-safe without garbage collection"
series: "rust-php-developers"
chapter: 3
order: 3
difficulty: "Intermediate"
prerequisites:
  - "/series/rust-php-developers/chapters/00-quick-start-guide"
  - "/series/rust-php-developers/chapters/01-why-rust-for-php-developers"
  - "/series/rust-php-developers/chapters/02-variables-and-types"
---

![03: Ownership and Borrowing](/images/rust-php-developers/chapter-03-ownership-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rust-php-developers">Rust for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 03</span>
</div>

# Chapter 03: Ownership and Borrowing

## Overview

Ownership is Rust's most unique and important feature. It's what enables Rust to guarantee memory safety without a garbage collector. For PHP developers who are used to automatic memory management, ownership represents a fundamentally different way of thinking about how programs manage memory.

This chapter will transform your understanding of how memory works. By the end, you'll understand why Rust's ownership system prevents entire categories of bugs that plague PHP and other languages, and you'll be able to write memory-safe code that's faster than garbage-collected languages.

**Warning:** This chapter is challenging. It introduces concepts that don't exist in PHP. Take your time, run the examples, and don't worry if it takes multiple reads to click. Every Rust developer has struggled with ownership initially—it's normal!

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 02: Variables and Types](/series/rust-php-developers/chapters/02-variables-and-types)
- Understanding of String vs &str
- Understanding of mutability (`mut`)
- Basic comfort with Rust syntax

**Estimated Time**: ~90 minutes (this is a critical chapter - don't rush!)

## What You'll Learn

By the end of this chapter, you will:

- **Understand the three ownership rules** — Rust's memory safety guarantees
- **Master move semantics** — Why values move instead of copy
- **Use borrowing effectively** — &T and &mut T
- **Work with the borrow checker** — Rust's compile-time safety net
- **Fix ownership errors** — Common patterns and solutions
- **Compare to PHP's memory model** — Reference counting vs ownership

## The Big Picture: Memory Management

### PHP: Garbage Collection

**PHP (automatic memory management):**
```php
<?php
class User {
    public function __construct(public string $name) {}
}

function process() {
    $user = new User("Alice");
    // PHP automatically:
    // 1. Allocates memory for User
    // 2. Increments reference count
    // 3. Decrements when out of scope
    // 4. Garbage collector frees memory later
}

// Memory cleaned up sometime in the future
```

**How PHP manages memory:**
1. **Reference counting**: Tracks how many variables point to each object
2. **Garbage collection**: Periodically scans and frees unreferenced memory
3. **Automatic**: You don't think about it
4. **Cost**: GC pauses, memory overhead, unpredictable timing

### Rust: Ownership

**Rust (compile-time memory management):**
```rust
struct User {
    name: String,
}

fn process() {
    let user = User { name: String::from("Alice") };
    // Rust automatically:
    // 1. Allocates memory for User
    // 2. Frees memory when user goes out of scope
    // 3. No garbage collector needed!
} // user dropped here - memory freed immediately

// Memory cleaned up deterministically
```

**How Rust manages memory:**
1. **Ownership**: Each value has exactly one owner
2. **Scope-based**: Memory freed when owner goes out of scope
3. **Compile-time**: All checked at compile time
4. **Cost**: Zero runtime overhead, no GC pauses

## Step 1: The Three Ownership Rules (~10 min)

### Goal

Understand Rust's fundamental ownership rules.

### Actions

1. **Learn the three rules**:

**The Ownership Rules:**

1. **Each value has exactly one owner**
2. **There can only be one owner at a time**
3. **When the owner goes out of scope, the value is dropped**

2. **See it in action**:

```rust
fn main() {
    // Rule 1: Each value has an owner
    let s = String::from("hello");  // s owns the String

    // Rule 3: Value dropped when owner goes out of scope
    {
        let s2 = String::from("world");  // s2 owns this String
    }  // s2 goes out of scope, String is dropped (freed)

    println!("{}", s);   // s is still valid
    // println!("{}", s2);  // ❌ Error: s2 not in scope
}  // s goes out of scope, its String is dropped
```

3. **Compare to PHP**:

**PHP:**
```php
<?php
function demo() {
    $s = "hello";      // Reference count: 1
    $s2 = $s;          // Reference count: 2 (both point to same string)
    // Both $s and $s2 can access the string

    unset($s);         // Reference count: 1
    echo $s2;          // Still works
}  // Reference count: 0, GC will clean up eventually
```

**Rust:**
```rust
fn demo() {
    let s = String::from("hello");  // s owns the String
    let s2 = s;                     // Ownership moved to s2!
    // println!("{}", s);           // ❌ Error: s no longer owns the value
    println!("{}", s2);             // ✅ s2 owns it now
}  // s2 goes out of scope, String is dropped
```

### Expected Result

Understanding that:
- Rust has ONE owner per value (not multiple references like PHP)
- Ownership can be transferred (moved)
- Memory is freed automatically when owner goes out of scope

### Why It Works

**Benefits of ownership:**
- **No memory leaks**: Can't forget to free memory
- **No dangling pointers**: Can't access freed memory
- **No double frees**: Can't free the same memory twice
- **Zero-cost**: No runtime overhead
- **Deterministic**: Know exactly when memory is freed

**PHP comparison:**
- PHP uses reference counting + garbage collection
- Multiple variables can reference the same object
- GC runs periodically (unpredictable timing)
- Circular references can cause leaks

## Step 2: Stack vs Heap (~10 min)

### Goal

Understand where different types of data are stored.

### Actions

1. **Stack data**:

```rust
fn main() {
    // Stack: Fixed-size data
    let x = 5;              // i32 on stack
    let y = 3.14;           // f64 on stack
    let b = true;           // bool on stack

    // These types implement Copy trait
    let a = x;              // a gets a COPY of x's value
    println!("{}, {}", x, a);  // Both work! (x not moved)
}
```

2. **Heap data**:

```rust
fn main() {
    // Heap: Variable-size data
    let s1 = String::from("hello");  // String data on heap

    // String does NOT implement Copy
    let s2 = s1;            // s1 MOVED to s2 (not copied)
    // println!("{}", s1);  // ❌ Error: s1 was moved
    println!("{}", s2);     // ✅ Only s2 works
}
```

3. **Visualize the difference**:

**Stack types (implement Copy):**
```rust
let x = 5;
let y = x;  // Copy

// Memory:
// x: 5
// y: 5
// Both independent copies
```

**Heap types (don't implement Copy):**
```rust
let s1 = String::from("hello");
let s2 = s1;  // Move

// Memory:
// s1: [invalid]
// s2: -> heap("hello")
// Only one owner
```

### Expected Result

Understanding:
- Stack: Fast, fixed-size, implements Copy
- Heap: Flexible, variable-size, moves by default
- Copy types can be used after assignment
- Move types cannot be used after being moved

**Types that implement Copy:**
- All integers (i32, u64, etc.)
- All floats (f32, f64)
- Boolean (bool)
- Character (char)
- Tuples (if all elements implement Copy)

**Types that move:**
- String
- Vec<T>
- Custom structs (by default)
- Most complex types

### Why It Works

**Stack allocation:**
```
┌─────────┐
│    5    │ ← x
├─────────┤
│    5    │ ← y (copy)
└─────────┘
```

**Heap allocation:**
```
Stack:              Heap:
┌──────────┐      ┌───────────┐
│ pointer  │ ──→  │  "hello"  │
└──────────┘      └───────────┘
     s2

s1: [invalid]
```

**PHP comparison:**
```php
<?php
// PHP: Everything is copy-on-write
$x = 5;
$y = $x;       // Both reference same value until modified

$s1 = "hello";
$s2 = $s1;     // Both reference same string until modified

// Objects are always by reference
$obj1 = new stdClass();
$obj2 = $obj1;  // Both reference same object
```

## Step 3: Move Semantics (~15 min)

### Goal

Master how Rust moves ownership between variables.

### Actions

1. **Basic move**:

```rust
fn main() {
    let s1 = String::from("hello");
    let s2 = s1;  // s1 moved to s2

    // println!("{}", s1);  // ❌ Error: value borrowed after move
    println!("{}", s2);     // ✅ Works
}
```

2. **Move in function calls**:

```rust
fn take_ownership(s: String) {
    println!("{}", s);
}  // s dropped here

fn main() {
    let s = String::from("hello");
    take_ownership(s);  // s moved into function

    // println!("{}", s);  // ❌ Error: s was moved
}
```

3. **Returning ownership**:

```rust
fn give_ownership() -> String {
    String::from("hello")  // Ownership transferred to caller
}

fn take_and_give(s: String) -> String {
    s  // Ownership passed through
}

fn main() {
    let s1 = give_ownership();  // s1 receives ownership

    let s2 = String::from("world");
    let s3 = take_and_give(s2);  // s2 moved in, ownership returned as s3

    // println!("{}", s2);  // ❌ Error: s2 was moved
    println!("{}, {}", s1, s3);  // ✅ Works
}
```

4. **Clone to keep original**:

```rust
fn main() {
    let s1 = String::from("hello");
    let s2 = s1.clone();  // Deep copy - expensive!

    println!("s1: {}, s2: {}", s1, s2);  // ✅ Both work
}
```

### Expected Result

```
hello
hello, world
s1: hello, s2: hello
```

### Why It Works

**Move prevents:**
- Double frees (two owners trying to free same memory)
- Use-after-free (accessing freed memory)
- Data races (concurrent access to same data)

**PHP comparison:**
```php
<?php
function takeOwnership(string $s): void {
    echo $s;
}

$s = "hello";
takeOwnership($s);
echo $s;            // ✅ Still works (copy for primitives)

class User {
    public string $name;
}

function modifyUser(User $user): void {
    $user->name = "Modified";
}

$user = new User();
$user->name = "Alice";
modifyUser($user);
echo $user->name;   // "Modified" (objects passed by reference)
```

### Troubleshooting

- **Error: value borrowed after move** — Variable was moved, use clone() or borrowing
- **Want to use after move** — Clone the value or pass a reference
- **Too many clones** — Learn borrowing (next section)!

## Step 4: Borrowing with & (~15 min)

### Goal

Use references to borrow values without taking ownership.

### Actions

1. **Immutable borrowing**:

```rust
fn calculate_length(s: &String) -> usize {
    s.len()  // Can read, but can't modify
}  // s goes out of scope, but doesn't drop the String (doesn't own it)

fn main() {
    let s = String::from("hello");
    let len = calculate_length(&s);  // Borrow s with &

    println!("'{}' has length {}", s, len);  // ✅ s still valid!
}
```

2. **Multiple immutable borrows**:

```rust
fn main() {
    let s = String::from("hello");

    let r1 = &s;  // First immutable borrow
    let r2 = &s;  // Second immutable borrow
    let r3 = &s;  // Third immutable borrow

    println!("{}, {}, {}", r1, r2, r3);  // ✅ All work!
}
```

3. **Visualize borrowing**:

```
Stack:              Heap:
┌──────────┐      ┌───────────┐
│    s     │ ──→  │  "hello"  │
├──────────┤      └───────────┘
│   &s     │ ──┘       ↑
│  (r1)    │          (owner: s, borrower: r1)
└──────────┘
```

### Expected Result

```
'hello' has length 5
hello, hello, hello
```

### Why It Works

**Borrowing rules:**
- ✅ Can have any number of immutable borrows (&T)
- ✅ Borrows don't take ownership
- ✅ Original owner still valid
- ✅ Data can't be modified through immutable borrow

**PHP comparison:**
```php
<?php
function calculateLength(string $s): int {
    return strlen($s);
}

$s = "hello";
$len = calculateLength($s);
echo "$s has length $len";  // Works

// PHP strings are immutable anyway
// Objects behave differently (passed by reference)
```

## Step 5: Mutable Borrowing with &mut (~15 min)

### Goal

Borrow values mutably to modify them without taking ownership.

### Actions

1. **Basic mutable borrow**:

```rust
fn add_world(s: &mut String) {
    s.push_str(", world");
}

fn main() {
    let mut s = String::from("hello");
    add_world(&mut s);  // Mutable borrow

    println!("{}", s);  // "hello, world"
}
```

2. **The one mutable borrow rule**:

```rust
fn main() {
    let mut s = String::from("hello");

    let r1 = &mut s;  // First mutable borrow
    // let r2 = &mut s;  // ❌ Error: cannot borrow as mutable more than once

    println!("{}", r1);
}
```

3. **Can't mix immutable and mutable borrows**:

```rust
fn main() {
    let mut s = String::from("hello");

    let r1 = &s;      // Immutable borrow
    let r2 = &s;      // Another immutable borrow
    // let r3 = &mut s;  // ❌ Error: cannot borrow as mutable

    println!("{}, {}", r1, r2);
}
```

4. **Borrowing scopes**:

```rust
fn main() {
    let mut s = String::from("hello");

    {
        let r1 = &mut s;  // Mutable borrow in inner scope
        r1.push_str(", world");
    }  // r1 goes out of scope

    let r2 = &mut s;  // ✅ New mutable borrow OK now
    r2.push_str("!");

    println!("{}", s);
}
```

### Expected Result

```
hello, world
hello, world!
```

### Why It Works

**Mutable borrowing rules:**
- ✅ Can have ONE mutable borrow (&mut T)
- ❌ Can't have other borrows while mutable borrow exists
- ✅ Prevents data races at compile time
- ✅ Original owner regains access when borrow ends

**This prevents:**
```rust
let mut s = String::from("hello");
let r1 = &s;          // Immutable borrow
let r2 = &mut s;      // ❌ Can't have mutable while immutable exists
r2.push_str("!");     // Would invalidate r1!
println!("{}", r1);   // Would be reading stale data
```

**PHP comparison:**
```php
<?php
function addWorld(string &$s): void {  // Pass by reference
    $s .= ", world";
}

$s = "hello";
addWorld($s);
echo $s;  // "hello, world"

// PHP doesn't prevent data races
$obj = new stdClass();
$obj->value = 0;

// Multiple references to same object - potential race condition
$ref1 = &$obj;
$ref2 = &$obj;
// Both can modify simultaneously in multi-threaded PHP (pthreads)
```

## Step 6: The Borrow Checker (~10 min)

### Goal

Understand how Rust prevents dangling references at compile time.

### Actions

1. **Dangling reference prevention**:

```rust
fn dangle() -> &String {  // ❌ Won't compile!
    let s = String::from("hello");
    &s  // Trying to return reference to s
}  // s dropped here - reference would be invalid!

// Error: missing lifetime specifier
```

2. **The correct way**:

```rust
fn no_dangle() -> String {
    let s = String::from("hello");
    s  // ✅ Return ownership
}

fn main() {
    let s = no_dangle();
    println!("{}", s);
}
```

3. **Borrow checker in action**:

```rust
fn main() {
    let r;

    {
        let x = 5;
        r = &x;  // ❌ Error: x doesn't live long enough
    }  // x dropped here

    // println!("{}", r);  // Would be dangling reference!
}
```

4. **Working version**:

```rust
fn main() {
    let x = 5;
    let r = &x;  // ✅ OK: x lives longer than r

    println!("{}", r);
}  // r dropped, then x dropped
```

### Expected Result

Understanding that Rust prevents:
- Dangling references (pointers to freed memory)
- Use-after-free bugs
- All at compile time (zero runtime cost)

### Why It Works

**Borrow checker ensures:**
```
Reference lifetime ≤ Owner lifetime
```

**PHP comparison:**
```php
<?php
function dangle(): string {
    $s = "hello";
    return $s;  // ✅ Returns copy (strings are immutable)
}

function dangleObject(): stdClass {
    $obj = new stdClass();
    $obj->value = 42;
    return $obj;  // ✅ Returns reference (reference counted)
}

// PHP garbage collector prevents dangling references
// But at runtime cost
```

## Step 7: Common Ownership Patterns (~10 min)

### Goal

Learn practical patterns for working with ownership.

### Actions

1. **Pattern: Read-only access**:

```rust
fn print_user(user: &User) {  // Borrow, don't take ownership
    println!("{}", user.name);
}

struct User {
    name: String,
}

fn main() {
    let user = User { name: String::from("Alice") };
    print_user(&user);  // Borrow
    print_user(&user);  // Can borrow multiple times
}
```

2. **Pattern: Modify without owning**:

```rust
fn update_name(user: &mut User, new_name: String) {
    user.name = new_name;
}

fn main() {
    let mut user = User { name: String::from("Alice") };
    update_name(&mut user, String::from("Bob"));
    println!("{}", user.name);
}
```

3. **Pattern: Transfer ownership**:

```rust
fn consume_user(user: User) {
    println!("Consumed: {}", user.name);
}  // user dropped here

fn main() {
    let user = User { name: String::from("Alice") };
    consume_user(user);  // Ownership transferred
    // Can't use user here
}
```

4. **Pattern: Return ownership**:

```rust
fn create_user(name: String) -> User {
    User { name }  // Transfer ownership to caller
}

fn transform_user(mut user: User) -> User {
    user.name = format!("Modified: {}", user.name);
    user  // Return ownership
}

fn main() {
    let user = create_user(String::from("Alice"));
    let user = transform_user(user);  // Rebind to take ownership back
    println!("{}", user.name);
}
```

### Expected Result

```
Alice
Alice
Bob
Consumed: Alice
Modified: Alice
```

### Why It Works

**Guidelines:**
- **Default to borrowing** (&T or &mut T)
- **Take ownership** when consuming the value
- **Return ownership** to give caller control
- **Use &str** for string parameters (more flexible than &String)

**PHP comparison:**
```php
<?php
class User {
    public function __construct(public string $name) {}
}

// PHP: Always pass objects by reference
function printUser(User $user): void {
    echo $user->name;
}

function updateName(User $user, string $newName): void {
    $user->name = $newName;  // Modifies original
}

function consumeUser(User $user): void {
    echo "Consumed: {$user->name}";
    // Object still exists (reference counting)
}

$user = new User("Alice");
printUser($user);
updateName($user, "Bob");
printUser($user);
consumeUser($user);
printUser($user);  // Still works!
```

## Step 8: Practical Examples (~15 min)

### Goal

Apply ownership concepts to real scenarios.

### Actions

1. **Example: Vector ownership**:

```rust
fn main() {
    let mut v = vec![1, 2, 3];

    // Borrow to read
    fn sum(numbers: &Vec<i32>) -> i32 {
        numbers.iter().sum()
    }
    println!("Sum: {}", sum(&v));

    // Borrow mutably to modify
    fn add_one(numbers: &mut Vec<i32>) {
        for num in numbers.iter_mut() {
            *num += 1;
        }
    }
    add_one(&mut v);
    println!("{:?}", v);

    // Take ownership to consume
    fn consume(numbers: Vec<i32>) {
        println!("Consumed: {:?}", numbers);
    }
    consume(v);
    // Can't use v here
}
```

2. **Example: String building**:

```rust
fn build_message(parts: &[&str]) -> String {
    let mut message = String::new();
    for part in parts {
        message.push_str(part);
        message.push(' ');
    }
    message
}

fn main() {
    let parts = ["Hello", "from", "Rust"];
    let msg = build_message(&parts);
    println!("{}", msg);

    // parts still valid
    println!("{:?}", parts);
}
```

3. **Example: Struct ownership**:

```rust
struct User {
    name: String,
    email: String,
}

impl User {
    // Takes ownership
    fn new(name: String, email: String) -> User {
        User { name, email }
    }

    // Borrows self
    fn display(&self) {
        println!("{} <{}>", self.name, self.email);
    }

    // Borrows self mutably
    fn update_email(&mut self, new_email: String) {
        self.email = new_email;
    }

    // Consumes self
    fn into_name(self) -> String {
        self.name
    }
}

fn main() {
    let mut user = User::new(
        String::from("Alice"),
        String::from("alice@example.com")
    );

    user.display();
    user.update_email(String::from("alice@newdomain.com"));
    user.display();

    let name = user.into_name();
    // user can't be used here (moved)
    println!("Name: {}", name);
}
```

### Expected Result

```
Sum: 6
[2, 3, 4]
Consumed: [2, 3, 4]
Hello from Rust
["Hello", "from", "Rust"]
Alice <alice@example.com>
Alice <alice@newdomain.com>
Name: Alice
```

### Why It Works

**Method borrowing conventions:**
- `&self` — Borrow immutably (read-only)
- `&mut self` — Borrow mutably (modify)
- `self` — Take ownership (consume)

**PHP comparison:**
```php
<?php
class User {
    public function __construct(
        private string $name,
        private string $email
    ) {}

    // Always operates on same object
    public function display(): void {
        echo "{$this->name} <{$this->email}>";
    }

    public function updateEmail(string $newEmail): void {
        $this->email = $newEmail;
    }

    public function getName(): string {
        return $this->name;  // Return copy
    }
}
```

## Exercises

### Exercise 1: Fix the Ownership Error

Fix this code without using `.clone()`:

```rust
fn main() {
    let s = String::from("hello");
    take_and_print(s);
    println!("{}", s);  // Want to use s here!
}

fn take_and_print(s: String) {
    println!("{}", s);
}
```

<details>
<summary>Solution</summary>

```rust
fn main() {
    let s = String::from("hello");
    take_and_print(&s);  // Borrow instead of move
    println!("{}", s);
}

fn take_and_print(s: &String) {  // Take a reference
    println!("{}", s);
}
```
</details>

### Exercise 2: Modify Through Reference

Write a function that appends "!" to a string without taking ownership:

```rust
fn add_excitement(s: /* your parameter here */) {
    // Your code here
}

fn main() {
    let mut message = String::from("Hello");
    add_excitement(/* your argument here */);
    println!("{}", message);  // Should print "Hello!"
}
```

<details>
<summary>Solution</summary>

```rust
fn add_excitement(s: &mut String) {
    s.push('!');
}

fn main() {
    let mut message = String::from("Hello");
    add_excitement(&mut message);
    println!("{}", message);  // Prints "Hello!"
}
```
</details>

### Exercise 3: Return Ownership

Write a function that creates and returns a User:

```rust
struct User {
    name: String,
    age: u32,
}

fn create_user(/* parameters */) -> /* return type */ {
    // Your code here
}

fn main() {
    let user = create_user("Alice", 30);
    println!("{} is {}", user.name, user.age);
}
```

<details>
<summary>Solution</summary>

```rust
struct User {
    name: String,
    age: u32,
}

fn create_user(name: &str, age: u32) -> User {
    User {
        name: name.to_string(),
        age,
    }
}

fn main() {
    let user = create_user("Alice", 30);
    println!("{} is {}", user.name, user.age);
}
```
</details>

## Wrap-up

Congratulations! You've completed the most challenging chapter in the series. Here's what you've mastered:

- ✓ **The three ownership rules** — Each value has one owner, only one at a time, dropped when owner goes out of scope
- ✓ **Move semantics** — Values move by default (unless they implement Copy)
- ✓ **Borrowing** — Use &T for immutable borrows, &mut T for mutable borrows
- ✓ **The borrow checker** — Prevents dangling references and data races at compile time
- ✓ **Common patterns** — When to borrow, when to take ownership, when to return ownership

### The Mental Model

**Ownership:**
```
One owner → Clear responsibility → Automatic cleanup
```

**Borrowing:**
```
Temporary access → No ownership transfer → Original owner still responsible
```

### Key Rules to Remember

1. **Default to borrowing** — Use &T or &mut T unless you need ownership
2. **One mutable borrow OR many immutable borrows** — Never both at once
3. **References can't outlive their owner** — Borrow checker ensures this
4. **Move by default for heap types** — Use .clone() only when necessary

### PHP vs Rust Memory Management

| Concept | PHP | Rust |
|---------|-----|------|
| **Memory management** | Garbage collection | Ownership |
| **Multiple references** | Yes (reference counting) | No (one owner) |
| **Cleanup timing** | Unpredictable (GC) | Deterministic (scope-based) |
| **Runtime cost** | GC pauses | Zero |
| **Compile-time safety** | No | Yes |
| **Data races** | Possible | Prevented |

### What's Next

In the next chapter, **Functions and Control Flow**, you'll learn:
- Function syntax and parameters
- Return values and expressions
- Control flow (if, loops, match)
- Pattern matching (Rust's supercharged switch)
- How ownership works with function calls (you've got the foundation now!)

The hardest part is behind you. Ownership is Rust's unique feature, and now that you understand it, everything else will be much easier!

## Further Reading

- [The Rust Book: Understanding Ownership](https://doc.rust-lang.org/book/ch04-00-understanding-ownership.html)
- [Rust by Example: Ownership](https://doc.rust-lang.org/rust-by-example/scope/move.html)
- [Rust by Example: Borrowing](https://doc.rust-lang.org/rust-by-example/scope/borrow.html)
- [Appendix C: Common Errors](/series/rust-php-developers/appendices/appendix-c-common-errors) — Ownership error solutions

## Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 03 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/rust-php-developers/chapter-03)**

<ChapterCheckbox
  seriesId="rust-php-developers"
  chapterId="03"
  label="You've mastered Rust's ownership system - the hardest part is done!"
/>

---

Ready to use your ownership knowledge? Continue to [Chapter 04: Functions and Control Flow](/series/rust-php-developers/chapters/04-functions-and-control-flow).
