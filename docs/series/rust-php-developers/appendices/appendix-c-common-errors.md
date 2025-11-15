# Appendix C: Common Errors and Solutions

A guide to decoding Rust compiler errors for PHP developers, with solutions and explanations.

## Borrow Checker Errors

These are the most common errors for PHP developers learning Rust.

### Error: Cannot borrow as mutable

```rust
error[E0502]: cannot borrow `x` as mutable because it is also borrowed as immutable
```

**Example:**
```rust
let mut vec = vec![1, 2, 3];
let first = &vec[0];      // Immutable borrow
vec.push(4);              // ❌ Error: mutable borrow while immutable borrow exists
println!("{}", first);
```

**Why it happens:**
Rust prevents data races by not allowing mutable and immutable borrows simultaneously.

**Solution:**
```rust
let mut vec = vec![1, 2, 3];
let first = vec[0];       // Copy the value instead of borrowing
vec.push(4);              // ✅ OK
println!("{}", first);

// Or: limit the scope of the borrow
let mut vec = vec![1, 2, 3];
{
    let first = &vec[0];
    println!("{}", first);
}  // Borrow ends here
vec.push(4);              // ✅ OK
```

**PHP comparison:**
PHP doesn't have this concept—variables are either copied or reference-counted.

### Error: Cannot move out of borrowed content

```rust
error[E0507]: cannot move out of `*some_string` which is behind a shared reference
```

**Example:**
```rust
fn print_string(s: &String) {
    let owned = *s;       // ❌ Error: cannot move out of borrowed content
    println!("{}", owned);
}
```

**Solution:**
```rust
// Solution 1: Clone the value
fn print_string(s: &String) {
    let owned = s.clone(); // ✅ OK: creates a new copy
    println!("{}", owned);
}

// Solution 2: Just borrow it
fn print_string(s: &String) {
    println!("{}", s);     // ✅ OK: no need to own it
}

// Solution 3: Take ownership
fn print_string(s: String) {  // Takes ownership
    println!("{}", s);
}
```

### Error: Value used after move

```rust
error[E0382]: borrow of moved value: `s`
```

**Example:**
```rust
let s = String::from("hello");
let s2 = s;               // s moved to s2
println!("{}", s);        // ❌ Error: s was moved
```

**Why it happens:**
Rust transfers ownership by default (unlike PHP's copy-on-write).

**Solution:**
```rust
// Solution 1: Clone
let s = String::from("hello");
let s2 = s.clone();       // ✅ OK: creates a copy
println!("{} {}", s, s2);

// Solution 2: Borrow instead
let s = String::from("hello");
let s2 = &s;              // ✅ OK: borrows instead of moves
println!("{} {}", s, s2);

// Solution 3: Use types that implement Copy
let x = 5;
let y = x;                // ✅ OK: i32 implements Copy
println!("{} {}", x, y);
```

**PHP comparison:**
```php
$s = "hello";
$s2 = $s;                 // Copied in PHP
echo "$s $s2";            // Works fine
```

### Error: Cannot borrow as mutable more than once

```rust
error[E0499]: cannot borrow `x` as mutable more than once at a time
```

**Example:**
```rust
let mut vec = vec![1, 2, 3];
let ref1 = &mut vec;
let ref2 = &mut vec;      // ❌ Error: second mutable borrow
ref1.push(4);
ref2.push(5);
```

**Solution:**
```rust
// Solution: Use only one mutable borrow at a time
let mut vec = vec![1, 2, 3];
{
    let ref1 = &mut vec;
    ref1.push(4);
}  // ref1 goes out of scope
let ref2 = &mut vec;      // ✅ OK: ref1 no longer exists
ref2.push(5);
```

## Lifetime Errors

### Error: Lifetime mismatch

```rust
error[E0623]: lifetime mismatch
```

**Example:**
```rust
fn longest(x: &str, y: &str) -> &str {
    if x.len() > y.len() { x } else { y }
}  // ❌ Error: missing lifetime specifier
```

**Why it happens:**
Rust can't determine which input lifetime the output should have.

**Solution:**
```rust
// Annotate lifetimes
fn longest<'a>(x: &'a str, y: &'a str) -> &'a str {
    if x.len() > y.len() { x } else { y }
}  // ✅ OK: explicit lifetime
```

### Error: Borrowed value does not live long enough

```rust
error[E0597]: `x` does not live long enough
```

**Example:**
```rust
fn dangle() -> &String {
    let s = String::from("hello");
    &s
}  // ❌ Error: s goes out of scope
```

**Solution:**
```rust
// Solution 1: Return owned value
fn no_dangle() -> String {
    let s = String::from("hello");
    s  // ✅ OK: ownership transferred
}

// Solution 2: Use 'static lifetime
fn get_static() -> &'static str {
    "hello"  // ✅ OK: string literals are 'static
}
```

## Type Errors

### Error: Mismatched types

```rust
error[E0308]: mismatched types
```

**Example:**
```rust
fn add(a: i32, b: i32) -> i32 {
    a + b;  // ❌ Error: expected i32, found ()
}
```

**Why it happens:**
The semicolon makes it a statement (returns `()`), not an expression.

**Solution:**
```rust
fn add(a: i32, b: i32) -> i32 {
    a + b  // ✅ OK: expression (no semicolon)
}

// Or explicit return
fn add(a: i32, b: i32) -> i32 {
    return a + b;  // ✅ OK: explicit return
}
```

### Error: Cannot convert type

**Example:**
```rust
let x: u32 = 5;
let y: i32 = x;  // ❌ Error: expected i32, found u32
```

**Solution:**
```rust
let x: u32 = 5;
let y: i32 = x as i32;  // ✅ OK: explicit cast
```

## Option/Result Errors

### Error: No method named `unwrap` on type `T`

**Example:**
```rust
fn get_value() -> Option<i32> { Some(5) }

let value: i32 = get_value();  // ❌ Error: expected i32, found Option<i32>
```

**Solution:**
```rust
// Solution 1: Unwrap (can panic!)
let value = get_value().unwrap();  // ⚠️ Panics if None

// Solution 2: Match
let value = match get_value() {
    Some(v) => v,
    None => 0,  // default value
};

// Solution 3: unwrap_or
let value = get_value().unwrap_or(0);

// Solution 4: if let
if let Some(value) = get_value() {
    println!("{}", value);
}

// Solution 5: ? operator (in Result-returning function)
fn process() -> Result<(), Box<dyn std::error::Error>> {
    let value = get_value().ok_or("No value")?;
    Ok(())
}
```

**PHP comparison:**
```php
function getValue(): ?int { return 5; }

$value = getValue();
if ($value !== null) {
    echo $value;
}

// Or
$value = getValue() ?? 0;
```

### Error: `?` couldn't convert the error

```rust
error[E0277]: `?` couldn't convert the error to `MyError`
```

**Example:**
```rust
use std::fs;

fn read_file() -> Result<String, MyError> {
    let content = fs::read_to_string("file.txt")?;  // ❌ Error: io::Error != MyError
    Ok(content)
}
```

**Solution:**
```rust
// Solution 1: Use compatible error type
fn read_file() -> Result<String, std::io::Error> {
    let content = fs::read_to_string("file.txt")?;  // ✅ OK
    Ok(content)
}

// Solution 2: Convert error types
use std::io;

#[derive(Debug)]
struct MyError(String);

impl From<io::Error> for MyError {
    fn from(error: io::Error) -> Self {
        MyError(error.to_string())
    }
}

fn read_file() -> Result<String, MyError> {
    let content = fs::read_to_string("file.txt")?;  // ✅ OK: auto-converts
    Ok(content)
}

// Solution 3: Use anyhow
use anyhow::Result;

fn read_file() -> Result<String> {
    let content = fs::read_to_string("file.txt")?;  // ✅ OK: works with any error
    Ok(content)
}
```

## Trait Errors

### Error: Trait not implemented

```rust
error[E0277]: the trait `Display` is not implemented for `MyStruct`
```

**Example:**
```rust
struct MyStruct { value: i32 }

fn print_it<T: std::fmt::Display>(item: T) {
    println!("{}", item);
}

let s = MyStruct { value: 5 };
print_it(s);  // ❌ Error: Display not implemented
```

**Solution:**
```rust
use std::fmt;

struct MyStruct { value: i32 }

impl fmt::Display for MyStruct {
    fn fmt(&self, f: &mut fmt::Formatter) -> fmt::Result {
        write!(f, "MyStruct({})", self.value)
    }
}

// Or use Debug (derive)
#[derive(Debug)]
struct MyStruct { value: i32 }

let s = MyStruct { value: 5 };
println!("{:?}", s);  // ✅ OK
```

## Async Errors

### Error: `await` is only allowed inside `async` functions

**Example:**
```rust
fn main() {
    let result = some_async_function().await;  // ❌ Error: not in async context
}
```

**Solution:**
```rust
// Solution 1: Make function async
async fn main() {
    let result = some_async_function().await;  // Still won't work for main()
}

// Solution 2: Use tokio::main
#[tokio::main]
async fn main() {
    let result = some_async_function().await;  // ✅ OK
}

// Solution 3: Block on the future
fn main() {
    let rt = tokio::runtime::Runtime::new().unwrap();
    let result = rt.block_on(some_async_function());  // ✅ OK
}
```

### Error: Future not executed

**Example:**
```rust
async fn do_something() {}

fn main() {
    do_something();  // ⚠️ Warning: future not awaited
}
```

**Solution:**
```rust
#[tokio::main]
async fn main() {
    do_something().await;  // ✅ OK: future executed
}
```

## Macro Errors

### Error: Cannot find macro

```rust
error: cannot find macro `println` in this scope
```

**Solution:**
```rust
// Add the std prelude (usually automatic)
use std::println;

// Or check for typos
println!("Hello");  // Not println("Hello")
```

### Error: No rules expected this token

**Example:**
```rust
println!("{} {}", value);  // ❌ Error: format string has 2 args but only 1 provided
```

**Solution:**
```rust
println!("{} {}", value, other_value);  // ✅ OK
```

## Common Pitfalls for PHP Developers

### 1. Trying to mutate immutable variables

**PHP:**
```php
$count = 0;
$count++;  // OK
```

**Rust (wrong):**
```rust
let count = 0;
count += 1;  // ❌ Error: cannot assign twice to immutable variable
```

**Rust (correct):**
```rust
let mut count = 0;
count += 1;  // ✅ OK
```

### 2. Expecting null

**PHP:**
```php
$user = findUser(1);
if ($user === null) {
    echo "Not found";
}
```

**Rust:**
```rust
let user: Option<User> = find_user(1);
if user.is_none() {
    println!("Not found");
}

// Or with pattern matching
match user {
    Some(u) => println!("Found: {}", u.name),
    None => println!("Not found"),
}
```

### 3. Trying to use exceptions

**PHP:**
```php
try {
    $result = riskyOperation();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

**Rust:**
```rust
// Use Result instead
match risky_operation() {
    Ok(result) => println!("Success: {}", result),
    Err(e) => println!("Error: {}", e),
}

// Or with ?
fn caller() -> Result<(), Error> {
    let result = risky_operation()?;
    Ok(())
}
```

### 4. String confusion

**PHP:**
```php
$str = "hello";  // Always mutable
$str = $str . " world";  // Creates new string
```

**Rust:**
```rust
// &str (borrowed string slice)
let str: &str = "hello";  // Immutable

// String (owned string)
let mut s = String::from("hello");
s.push_str(" world");  // ✅ OK: String is mutable

// Common error
let str: &str = "hello";
str.push_str(" world");  // ❌ Error: &str is immutable
```

### 5. Array/Vec confusion

**PHP:**
```php
$arr = [1, 2, 3];
$arr[] = 4;  // Always works
```

**Rust:**
```rust
// Fixed array
let arr = [1, 2, 3];  // Can't grow

// Dynamic vector
let mut vec = vec![1, 2, 3];
vec.push(4);  // ✅ OK
```

## Debugging Tips

### 1. Read the error message carefully

Rust's errors are very helpful:
```
error[E0382]: borrow of moved value: `s`
  --> src/main.rs:4:20
   |
2  |     let s = String::from("hello");
   |         - move occurs because `s` has type `String`, which does not implement the `Copy` trait
3  |     let s2 = s;
   |              - value moved here
4  |     println!("{}", s);
   |                    ^ value borrowed here after move
```

The error shows:
- What went wrong (borrow of moved value)
- Where it happened (line 4)
- Why it happened (String doesn't implement Copy)
- How to fix it (usually suggests solutions)

### 2. Use `dbg!` macro

```rust
let x = 5;
dbg!(x);  // Prints: [src/main.rs:2] x = 5

let result = dbg!(some_function());  // Prints value and returns it
```

### 3. Check types with compiler errors

```rust
let x = 5;
let () = x;  // ❌ Intentional error shows type: expected (), found i32
```

### 4. Use cargo clippy

```bash
cargo clippy
```

Clippy catches common mistakes and suggests improvements.

### 5. Use rust-analyzer

Install rust-analyzer in your editor for:
- Inline error messages
- Type hints
- Quick fixes

## Getting Help

When stuck:

1. **Read the full error message** — Rust errors are detailed
2. **Check the Rust book** — [doc.rust-lang.org/book](https://doc.rust-lang.org/book/)
3. **Search the error code** — Google "rust E0382" (error code)
4. **Ask on forums** — [users.rust-lang.org](https://users.rust-lang.org/)
5. **Use `cargo clippy`** — Catches common mistakes
6. **Check this appendix** — Common PHP developer issues

## Error Code Quick Reference

| Error Code | Meaning | Common Cause |
|------------|---------|--------------|
| E0382 | Borrow of moved value | Used value after move |
| E0499 | Multiple mutable borrows | Two &mut borrows |
| E0502 | Mutable and immutable borrow | &mut while & exists |
| E0507 | Move out of borrowed content | Tried to move borrowed value |
| E0597 | Value doesn't live long enough | Lifetime too short |
| E0308 | Mismatched types | Wrong type returned/assigned |
| E0277 | Trait not implemented | Missing trait impl |
| E0425 | Cannot find value | Typo or not in scope |

---

Remember: Rust's compiler is your friend. It prevents bugs that would only show up in production in PHP!
