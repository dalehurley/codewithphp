<?php

declare(strict_types=1);

/**
 * Basic Functions in PHP
 * 
 * Functions are reusable blocks of code that perform specific tasks.
 * They help organize code, reduce repetition, and make programs more maintainable.
 */

echo "=== Basic Functions ===" . PHP_EOL . PHP_EOL;

// Example 1: Simple function with no parameters
function sayHello(): void
{
    echo "Hello, World!" . PHP_EOL;
}

echo "1. Simple Function:" . PHP_EOL;
sayHello();
sayHello(); // Can call multiple times
echo PHP_EOL;

// Example 2: Function with parameters
function greet(string $name): void
{
    echo "Hello, $name!" . PHP_EOL;
}

echo "2. Function with Parameters:" . PHP_EOL;
greet("Alice");
greet("Bob");
echo PHP_EOL;

// Example 3: Function with return value
function add(int $a, int $b): int
{
    return $a + $b;
}

echo "3. Function with Return Value:" . PHP_EOL;
$result = add(5, 10);
echo "5 + 10 = $result" . PHP_EOL;

// Use return value in expression
$total = add(20, 30) + add(10, 5);
echo "Total: $total" . PHP_EOL;
echo PHP_EOL;

// Example 4: Function with multiple parameters and types
function calculateRectangleArea(float $width, float $height): float
{
    return $width * $height;
}

echo "4. Multiple Parameters:" . PHP_EOL;
$area = calculateRectangleArea(5.5, 10.2);
echo "Rectangle area: " . round($area, 2) . PHP_EOL;
echo PHP_EOL;

// Example 5: Function with default parameters
function greetWithTitle(string $name, string $title = "Mr."): string
{
    return "Hello, $title $name";
}

echo "5. Default Parameters:" . PHP_EOL;
echo greetWithTitle("Smith") . PHP_EOL;        // Uses default "Mr."
echo greetWithTitle("Johnson", "Dr.") . PHP_EOL; // Overrides default
echo PHP_EOL;

// Example 6: Multiple default parameters
function createUser(
    string $username,
    string $email,
    bool $isActive = true,
    string $role = "user"
): array {
    return [
        'username' => $username,
        'email' => $email,
        'isActive' => $isActive,
        'role' => $role
    ];
}

echo "6. Multiple Default Parameters:" . PHP_EOL;
$user1 = createUser("alice", "alice@example.com");
print_r($user1);

$user2 = createUser("bob", "bob@example.com", false, "admin");
print_r($user2);
echo PHP_EOL;

// Example 7: Function returning different types with union types (PHP 8.0+)
function divide(float $a, float $b): float|string
{
    if ($b === 0.0) {
        return "Cannot divide by zero";
    }
    return $a / $b;
}

echo "7. Union Return Types:" . PHP_EOL;
$result1 = divide(10, 2);
echo "10 / 2 = $result1" . PHP_EOL;

$result2 = divide(10, 0);
echo "10 / 0 = $result2" . PHP_EOL;
echo PHP_EOL;

// Example 8: Function with nullable return type
function findUser(int $id): ?array
{
    // Simulating database lookup
    $users = [
        1 => ['name' => 'Alice', 'email' => 'alice@example.com'],
        2 => ['name' => 'Bob', 'email' => 'bob@example.com']
    ];

    return $users[$id] ?? null;
}

echo "8. Nullable Return Type:" . PHP_EOL;
$user = findUser(1);
if ($user !== null) {
    echo "Found: {$user['name']}" . PHP_EOL;
}

$user = findUser(999);
if ($user === null) {
    echo "User 999 not found" . PHP_EOL;
}
echo PHP_EOL;

// Example 9: Early return pattern
function validateAge(int $age): string
{
    // Early returns for invalid cases
    if ($age < 0) {
        return "Age cannot be negative";
    }

    if ($age > 150) {
        return "Age seems unrealistic";
    }

    // Main logic
    if ($age < 18) {
        return "Minor";
    }

    return "Adult";
}

echo "9. Early Return Pattern:" . PHP_EOL;
echo "Age -5: " . validateAge(-5) . PHP_EOL;
echo "Age 16: " . validateAge(16) . PHP_EOL;
echo "Age 25: " . validateAge(25) . PHP_EOL;
echo PHP_EOL;

// Example 10: Function documentation with PHPDoc
/**
 * Calculate the total price including tax
 * 
 * @param float $price The base price before tax
 * @param float $taxRate The tax rate as a decimal (e.g., 0.08 for 8%)
 * @return float The total price including tax
 */
function calculateTotalWithTax(float $price, float $taxRate): float
{
    return $price * (1 + $taxRate);
}

echo "10. Well-Documented Function:" . PHP_EOL;
$basePrice = 99.99;
$taxRate = 0.08;
$total = calculateTotalWithTax($basePrice, $taxRate);
echo "Base: \$$basePrice + Tax (8%) = \$" . number_format($total, 2) . PHP_EOL;
