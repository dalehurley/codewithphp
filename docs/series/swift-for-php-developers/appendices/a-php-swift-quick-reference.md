---
title: "Appendix A: PHP to Swift Quick Reference"
description: Side-by-side syntax comparison and translation guide for PHP developers learning Swift.
series: swift-for-php-developers
appendix: A
tags: ["reference", "comparison", "syntax", "php-to-swift"]
---

# Appendix A: PHP to Swift Quick Reference

A comprehensive side-by-side reference for translating PHP concepts to Swift.

## Variables and Constants

| PHP | Swift | Notes |
|-----|-------|-------|
| `$name = "John";` | `var name = "John"` | Mutable variable |
| `$name = "John";` | `let name = "John"` | Immutable (prefer this) |
| `define('API_KEY', 'secret');` | `let apiKey = "secret"` | Constant |
| `const API_KEY = 'secret';` | `let apiKey = "secret"` | Class constant |

## Types

| PHP | Swift | Notes |
|-----|-------|-------|
| `int $age` | `var age: Int` | Integer |
| `float $price` | `var price: Double` | Floating point |
| `string $name` | `var name: String` | String |
| `bool $active` | `var active: Bool` | Boolean |
| `array $items` | `var items: [String]` | Typed array |
| `array $data` | `var data: [String: Any]` | Dictionary |
| `?string $name` | `var name: String?` | Optional/nullable |

## Functions

| PHP | Swift |
|-----|-------|
| `function greet(string $name): string` | `func greet(name: String) -> String` |
| `function process(): void` | `func process()` |
| `function add(int $a, int $b = 0): int` | `func add(a: Int, b: Int = 0) -> Int` |
| `function(...$args)` | `func method(_ args: Int...)` |

## Classes and Structs

| PHP | Swift |
|-----|-------|
| `class User { }` | `class User { }` (reference type) |
| `class User { }` | `struct User { }` (value type, prefer this) |
| `public string $name;` | `var name: String` |
| `private int $age;` | `private var age: Int` |
| `public function __construct(string $name)` | `init(name: String)` |

## Control Flow

| PHP | Swift |
|-----|-------|
| `if ($x > 0) { }` | `if x > 0 { }` |
| `if ($x === null) { }` | `if x == nil { }` |
| `$result = $x ?? 'default';` | `let result = x ?? "default"` |
| `foreach ($items as $item) { }` | `for item in items { }` |
| `for ($i = 0; $i < 10; $i++) { }` | `for i in 0..<10 { }` |
| `while ($x > 0) { }` | `while x > 0 { }` |
| `switch ($x) { case 1: break; }` | `switch x { case 1: break }` |

## Arrays and Collections

| PHP | Swift |
|-----|-------|
| `$arr = [1, 2, 3];` | `let arr = [1, 2, 3]` |
| `$arr[] = 4;` | `arr.append(4)` |
| `count($arr)` | `arr.count` |
| `array_map($fn, $arr)` | `arr.map(fn)` |
| `array_filter($arr, $fn)` | `arr.filter(fn)` |
| `array_reduce($arr, $fn, $init)` | `arr.reduce(init, fn)` |
| `in_array($val, $arr)` | `arr.contains(val)` |

## String Operations

| PHP | Swift |
|-----|-------|
| `"Hello $name"` | `"Hello \(name)"` |
| `strlen($str)` | `str.count` |
| `strtoupper($str)` | `str.uppercased()` |
| `strtolower($str)` | `str.lowercased()` |
| `trim($str)` | `str.trimmingCharacters(in: .whitespaces)` |
| `explode(',', $str)` | `str.split(separator: ",")` |
| `implode(',', $arr)` | `arr.joined(separator: ",")` |

## Error Handling

| PHP | Swift |
|-----|-------|
| `try { } catch (Exception $e) { }` | `do { } catch { }` |
| `throw new Exception('error');` | `throw MyError.someCase` |
| `function doWork() throws` | `func doWork() throws` |

## Common Patterns

### Null Safety

```php
// PHP
$name = $user?->getName() ?? 'Guest';
```

```swift
// Swift
let name = user?.getName() ?? "Guest"
```

### Optional Binding

```php
// PHP
if ($user !== null) {
    echo $user->name;
}
```

```swift
// Swift
if let user = user {
    print(user.name)
}
```

### Guard Statement

```php
// PHP
if ($user === null) {
    return;
}
// use $user
```

```swift
// Swift
guard let user = user else {
    return
}
// use user (unwrapped)
```

## Framework Comparison

| Laravel | Vapor | Notes |
|---------|-------|-------|
| Route::get('/users', ...) | app.get("users") { req in } | Routing |
| Controller | Controller | Same concept |
| Model::all() | Model.query(on: db).all() | Query all |
| Model::find($id) | Model.find(id, on: db) | Find by ID |
| Middleware | Middleware | Same concept |
| Migration | Migration | Database migrations |
| Eloquent | Fluent | ORM |
| Validation | Content.decode() | Request validation |

## More Examples Coming

This appendix will be expanded with:
- Complete syntax comparison tables
- Common design patterns translation
- Framework routing comparison
- Database query translation
- Authentication patterns
- And more...

**See also:**
- [Chapter 02: Swift Syntax for PHP Developers](/series/swift-for-php-developers/chapters/02-swift-syntax-for-php-developers)
- [Appendix B: Swift Standard Library](/series/swift-for-php-developers/appendices/b-swift-standard-library)
