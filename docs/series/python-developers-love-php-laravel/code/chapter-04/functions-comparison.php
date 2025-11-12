<?php

declare(strict_types=1);

/**
 * Function Syntax - PHP vs Python
 * 
 * This file demonstrates PHP function definition syntax compared to Python.
 * Key differences:
 * - PHP: function name(type $param): returnType
 * - Python: def name(param: type) -> returnType
 * - PHP requires {} braces and ; semicolons
 * - PHP variadic: ...$args (similar to Python *args)
 * - PHP arrow functions: fn($x) => $x * 2
 */

// ============================================================================
// 1. Basic Function Definitions
// ============================================================================

// Python: def greet(name: str, age: int = 0) -> str:
// PHP:    function greet(string $name, int $age = 0): string

function greet(string $name, int $age = 0): string
{
    return "Hello, {$name}! Age: {$age}";
}

echo greet("Alice", 30) . "\n";
echo greet("Bob") . "\n";  // Uses default age

// ============================================================================
// 2. Default Parameters
// ============================================================================

// Python: def create_user(name: str, email: str, is_active: bool = True) -> dict:
// PHP:    function createUser(string $name, string $email, bool $isActive = true): array

function createUser(string $name, string $email, bool $isActive = true): array
{
    return [
        "name" => $name,
        "email" => $email,
        "active" => $isActive
    ];
}

$user = createUser("Alice", "alice@example.com");
print_r($user);

// ============================================================================
// 3. Variadic Functions
// ============================================================================

// Python: def sum_numbers(*args: int) -> int:
// PHP:    function sumNumbers(int ...$args): int

function sumNumbers(int ...$args): int
{
    return array_sum($args);
}

echo "Sum: " . sumNumbers(1, 2, 3, 4) . "\n";  // Output: 10

// Variadic with other parameters (must come last)
function greetMany(string $greeting, string ...$names): string
{
    $nameList = implode(", ", $names);
    return "{$greeting}: {$nameList}";
}

echo greetMany("Hello", "Alice", "Bob", "Charlie") . "\n";

// ============================================================================
// 4. Arrow Functions (PHP 7.4+)
// ============================================================================

// Python: doubled = list(map(lambda x: x * 2, numbers))
// PHP:    $doubled = array_map(fn($x) => $x * 2, $numbers);

$numbers = [1, 2, 3, 4, 5];
$doubled = array_map(fn($x) => $x * 2, $numbers);
print_r($doubled);

// Arrow functions automatically capture variables from parent scope
$multiplier = 3;
$tripled = array_map(fn($x) => $x * $multiplier, $numbers);
print_r($tripled);

// ============================================================================
// 5. Anonymous Functions (Closures)
// ============================================================================

// Python: lambda x: x * 2
// PHP:    function($x) { return $x * 2; }

$numbers = [1, 2, 3, 4, 5];

// Traditional anonymous function
$doubled = array_map(function($x) {
    return $x * 2;
}, $numbers);

// Arrow function (shorter syntax)
$doubled2 = array_map(fn($x) => $x * 2, $numbers);

// ============================================================================
// 6. Named Arguments (PHP 8.0+)
// ============================================================================

// Python: create_user(name="Alice", email="alice@example.com", is_active=True)
// PHP:    createUser(name: "Alice", email: "alice@example.com", isActive: true)

function createUserNamed(
    string $name,
    string $email,
    bool $isActive = true,
    ?int $age = null
): array {
    return [
        "name" => $name,
        "email" => $email,
        "active" => $isActive,
        "age" => $age
    ];
}

// Can skip optional parameters by name
$user = createUserNamed(
    email: "alice@example.com",
    name: "Alice",
    age: 30
);
print_r($user);

// ============================================================================
// 7. Return Type Declarations
// ============================================================================

// Void return type
function logMessage(string $message): void
{
    echo "[LOG] $message\n";
}

logMessage("Application started");

// Never return type (PHP 8.1+) - function never returns (throws exception, exits, etc.)
function throwError(string $message): never
{
    throw new Exception($message);
}

// ============================================================================
// 8. Type Hints for Callables
// ============================================================================

// Python: def process(func: Callable[[int], int], value: int) -> int:
// PHP:    function process(callable $func, int $value): int

function process(callable $func, int $value): int
{
    return $func($value);
}

$result = process(fn($x) => $x * 2, 5);
echo "Result: $result\n";  // Output: 10

// More specific: Closure type
function processClosure(Closure $func, int $value): int
{
    return $func($value);
}

