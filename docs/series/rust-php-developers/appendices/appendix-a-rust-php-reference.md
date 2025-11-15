# Appendix A: Rust vs PHP Quick Reference

A side-by-side comparison of common patterns and syntax between Rust and PHP.

## Variables and Types

### Variable Declaration

**PHP**
```php
<?php
// Mutable by default
$name = "Alice";
$age = 30;

// Type hints (optional)
$count: int = 10;
```

**Rust**
```rust
// Immutable by default
let name = "Alice";
let age = 30;

// Mutable variables
let mut count = 10;
count += 1;

// Type annotations (optional with inference)
let score: i32 = 100;
```

### Primitive Types

| PHP Type | Rust Type | Notes |
|----------|-----------|-------|
| `int` | `i32`, `i64`, `u32`, `u64` | Rust has sized integers |
| `float` | `f32`, `f64` | Rust distinguishes precision |
| `bool` | `bool` | Same |
| `string` | `String`, `&str` | Rust has owned and borrowed |
| `array` | `Vec<T>`, `[T; N]` | Vec is dynamic, array is fixed |
| `null` | `Option<T>` | No null in Rust! |

### Type Casting

**PHP**
```php
<?php
$num = (int)"42";
$str = (string)42;
$float = (float)"3.14";
```

**Rust**
```rust
let num: i32 = "42".parse().unwrap();
let str = 42.to_string();
let float: f64 = "3.14".parse().unwrap();
```

## Functions

### Basic Functions

**PHP**
```php
<?php
function greet(string $name): string {
    return "Hello, $name!";
}

$result = greet("Alice");
```

**Rust**
```rust
fn greet(name: &str) -> String {
    format!("Hello, {}!", name)
}

let result = greet("Alice");
```

### Default Parameters

**PHP**
```php
<?php
function greet(string $name = "World"): string {
    return "Hello, $name!";
}
```

**Rust**
```rust
// No default parameters, use Option instead
fn greet(name: Option<&str>) -> String {
    let name = name.unwrap_or("World");
    format!("Hello, {}!", name)
}

// Or multiple functions
fn greet_default() -> String {
    greet("World")
}

fn greet(name: &str) -> String {
    format!("Hello, {}!", name)
}
```

### Variable Arguments

**PHP**
```php
<?php
function sum(int ...$numbers): int {
    return array_sum($numbers);
}

sum(1, 2, 3, 4);
```

**Rust**
```rust
fn sum(numbers: &[i32]) -> i32 {
    numbers.iter().sum()
}

// Call with slice
sum(&[1, 2, 3, 4]);

// Or with vec!
sum(&vec![1, 2, 3, 4]);
```

## Control Flow

### If/Else

**PHP**
```php
<?php
if ($age >= 18) {
    echo "Adult";
} elseif ($age >= 13) {
    echo "Teenager";
} else {
    echo "Child";
}
```

**Rust**
```rust
if age >= 18 {
    println!("Adult");
} else if age >= 13 {
    println!("Teenager");
} else {
    println!("Child");
}

// If as expression (returns value)
let status = if age >= 18 {
    "Adult"
} else if age >= 13 {
    "Teenager"
} else {
    "Child"
};
```

### Match/Switch

**PHP**
```php
<?php
match ($status) {
    'draft' => 'Draft',
    'published' => 'Published',
    'archived' => 'Archived',
    default => 'Unknown',
};

// Old switch
switch ($status) {
    case 'draft':
        $label = 'Draft';
        break;
    case 'published':
        $label = 'Published';
        break;
    default:
        $label = 'Unknown';
}
```

**Rust**
```rust
let label = match status {
    "draft" => "Draft",
    "published" => "Published",
    "archived" => "Archived",
    _ => "Unknown",
};

// Match with patterns
match age {
    0..=12 => "Child",
    13..=17 => "Teenager",
    18.. => "Adult",
}
```

### Loops

**PHP**
```php
<?php
// For loop
for ($i = 0; $i < 10; $i++) {
    echo $i;
}

// While loop
while ($i < 10) {
    echo $i;
    $i++;
}

// Foreach
foreach ($items as $item) {
    echo $item;
}

foreach ($items as $key => $value) {
    echo "$key: $value";
}
```

**Rust**
```rust
// For loop (range)
for i in 0..10 {
    println!("{}", i);
}

// While loop
while i < 10 {
    println!("{}", i);
    i += 1;
}

// Loop (infinite)
loop {
    println!("Forever!");
    break;
}

// Iterator
for item in items.iter() {
    println!("{}", item);
}

// With index
for (index, item) in items.iter().enumerate() {
    println!("{}: {}", index, item);
}
```

## Collections

### Arrays/Vectors

**PHP**
```php
<?php
// PHP arrays are dynamic
$numbers = [1, 2, 3, 4, 5];
$numbers[] = 6;  // Append

// Access
echo $numbers[0];

// Length
count($numbers);
```

**Rust**
```rust
// Fixed-size array
let numbers: [i32; 5] = [1, 2, 3, 4, 5];

// Dynamic vector
let mut numbers = vec![1, 2, 3, 4, 5];
numbers.push(6);  // Append

// Access
println!("{}", numbers[0]);

// Length
numbers.len();
```

### Associative Arrays/HashMaps

**PHP**
```php
<?php
$user = [
    'name' => 'Alice',
    'age' => 30,
    'email' => 'alice@example.com',
];

// Access
echo $user['name'];

// Add/update
$user['city'] = 'NYC';
```

**Rust**
```rust
use std::collections::HashMap;

let mut user = HashMap::new();
user.insert("name", "Alice");
user.insert("age", "30");
user.insert("email", "alice@example.com");

// Access
println!("{}", user.get("name").unwrap());

// Add/update
user.insert("city", "NYC");

// Or use a struct instead!
struct User {
    name: String,
    age: u32,
    email: String,
}
```

## Structs/Classes

### Basic Class/Struct

**PHP**
```php
<?php
class User {
    public function __construct(
        public string $name,
        public int $age,
    ) {}

    public function greet(): string {
        return "Hello, I'm {$this->name}";
    }
}

$user = new User("Alice", 30);
echo $user->greet();
```

**Rust**
```rust
struct User {
    name: String,
    age: u32,
}

impl User {
    // Constructor (associated function)
    fn new(name: String, age: u32) -> Self {
        User { name, age }
    }

    // Method (has &self)
    fn greet(&self) -> String {
        format!("Hello, I'm {}", self.name)
    }
}

let user = User::new("Alice".to_string(), 30);
println!("{}", user.greet());
```

### Inheritance vs Composition

**PHP**
```php
<?php
class Animal {
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

**Rust** (No inheritance, use traits)
```rust
trait Animal {
    fn speak(&self) -> String;
}

struct Dog;

impl Animal for Dog {
    fn speak(&self) -> String {
        "Woof!".to_string()
    }
}
```

## Error Handling

### Try/Catch vs Result

**PHP**
```php
<?php
try {
    $result = riskyOperation();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Or
function divide(int $a, int $b): ?int {
    if ($b === 0) {
        return null;
    }
    return $a / $b;
}
```

**Rust**
```rust
fn risky_operation() -> Result<String, std::io::Error> {
    // Return Ok(value) or Err(error)
    Ok("Success".to_string())
}

// Handle with match
match risky_operation() {
    Ok(result) => println!("{}", result),
    Err(e) => println!("Error: {}", e),
}

// Or with ?
fn caller() -> Result<(), std::io::Error> {
    let result = risky_operation()?;  // Propagates error
    Ok(())
}

// Division
fn divide(a: i32, b: i32) -> Option<i32> {
    if b == 0 {
        None
    } else {
        Some(a / b)
    }
}
```

## Null Handling

### Nullable Types

**PHP**
```php
<?php
function findUser(int $id): ?User {
    return $user ?? null;
}

$user = findUser(1);
if ($user !== null) {
    echo $user->name;
}

// Null coalescing
$name = $user?->name ?? 'Guest';
```

**Rust**
```rust
fn find_user(id: u32) -> Option<User> {
    // Return Some(user) or None
    Some(User::new("Alice".to_string(), 30))
}

let user = find_user(1);
match user {
    Some(u) => println!("{}", u.name),
    None => println!("Not found"),
}

// Or with if-let
if let Some(u) = user {
    println!("{}", u.name);
}

// Unwrap or default
let name = user.map(|u| u.name).unwrap_or_else(|| "Guest".to_string());
```

## Traits/Interfaces

**PHP**
```php
<?php
interface Drawable {
    public function draw(): void;
}

class Circle implements Drawable {
    public function draw(): void {
        echo "Drawing circle";
    }
}
```

**Rust**
```rust
trait Drawable {
    fn draw(&self);
}

struct Circle;

impl Drawable for Circle {
    fn draw(&self) {
        println!("Drawing circle");
    }
}
```

## Common Operations

### String Operations

**PHP**
```php
<?php
$str = "Hello, World!";
strlen($str);
strtoupper($str);
str_contains($str, "World");
explode(", ", $str);
implode(", ", $arr);
trim($str);
```

**Rust**
```rust
let str = "Hello, World!";
str.len();
str.to_uppercase();
str.contains("World");
str.split(", ").collect::<Vec<_>>();
arr.join(", ");
str.trim();
```

### Array Operations

**PHP**
```php
<?php
array_map(fn($x) => $x * 2, $numbers);
array_filter($numbers, fn($x) => $x > 5);
array_reduce($numbers, fn($acc, $x) => $acc + $x, 0);
in_array($needle, $haystack);
```

**Rust**
```rust
numbers.iter().map(|x| x * 2).collect::<Vec<_>>();
numbers.iter().filter(|x| **x > 5).collect::<Vec<_>>();
numbers.iter().fold(0, |acc, x| acc + x);
haystack.contains(&needle);
```

## File I/O

**PHP**
```php
<?php
// Read file
$content = file_get_contents('file.txt');

// Write file
file_put_contents('file.txt', $content);

// Read lines
$lines = file('file.txt');
```

**Rust**
```rust
use std::fs;

// Read file
let content = fs::read_to_string("file.txt").unwrap();

// Write file
fs::write("file.txt", content).unwrap();

// Read lines
use std::io::{BufRead, BufReader};
let file = fs::File::open("file.txt").unwrap();
let lines: Vec<_> = BufReader::new(file).lines().collect();
```

## JSON

**PHP**
```php
<?php
$data = ['name' => 'Alice', 'age' => 30];
$json = json_encode($data);
$decoded = json_decode($json, true);
```

**Rust**
```rust
use serde::{Serialize, Deserialize};

#[derive(Serialize, Deserialize)]
struct User {
    name: String,
    age: u32,
}

let user = User { name: "Alice".to_string(), age: 30 };
let json = serde_json::to_string(&user).unwrap();
let decoded: User = serde_json::from_str(&json).unwrap();
```

## HTTP Client

**PHP**
```php
<?php
// Guzzle
$client = new \GuzzleHttp\Client();
$response = $client->get('https://api.example.com/users');
$data = json_decode($response->getBody(), true);
```

**Rust**
```rust
// reqwest (async)
use reqwest;

#[tokio::main]
async fn main() {
    let response = reqwest::get("https://api.example.com/users")
        .await
        .unwrap();
    let data: Vec<User> = response.json().await.unwrap();
}
```

## Testing

**PHP (PHPUnit)**
```php
<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    public function testGreet(): void {
        $user = new User("Alice", 30);
        $this->assertEquals("Hello, I'm Alice", $user->greet());
    }
}
```

**Rust**
```rust
#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_greet() {
        let user = User::new("Alice".to_string(), 30);
        assert_eq!(user.greet(), "Hello, I'm Alice");
    }
}
```

## Key Differences Summary

| Concept | PHP | Rust |
|---------|-----|------|
| **Memory Management** | Garbage collection | Ownership system |
| **Null** | null exists | No null, use Option<T> |
| **Mutability** | Mutable by default | Immutable by default |
| **Type System** | Dynamic (with hints) | Static, inferred |
| **Errors** | Exceptions | Result<T, E> |
| **Inheritance** | Yes (extends) | No (use traits) |
| **Compilation** | Interpreted | Compiled |
| **Concurrency** | Multi-process | Multi-threaded |
| **Package Manager** | Composer | Cargo |

---

This reference covers the most common patterns. For deeper exploration, see the individual chapters in the series.
