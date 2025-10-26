<?php

declare(strict_types=1);

/**
 * Strict Types Demonstration
 * 
 * With strict_types=1, PHP will not automatically convert types.
 * This helps catch bugs and makes your code more predictable.
 * 
 * Compare this with type-juggling.php to see the difference.
 */

echo "=== Strict Types Mode ===" . PHP_EOL . PHP_EOL;

// Function with type declarations
function addNumbers(int $a, int $b): int
{
    return $a + $b;
}

// This works fine
$result1 = addNumbers(5, 10);
echo "addNumbers(5, 10) = $result1" . PHP_EOL;

// This will throw a TypeError because "5" is a string, not an int
// Uncomment to see the error:
// $result2 = addNumbers(5, "10"); // TypeError!

// Function with multiple type options using union types (PHP 8.0+)
function formatValue(int|float $value): string
{
    return number_format($value, 2);
}

echo "formatValue(19.99) = " . formatValue(19.99) . PHP_EOL;
echo "formatValue(20) = " . formatValue(20) . PHP_EOL;
echo PHP_EOL;

// Return type declarations
function getUsername(): string
{
    return "JohnDoe";
}

function getUserAge(): int
{
    return 25;
}

function isUserActive(): bool
{
    return true;
}

echo "Username: " . getUsername() . PHP_EOL;
echo "Age: " . getUserAge() . PHP_EOL;
echo "Active: " . (isUserActive() ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

// Nullable types (PHP 8.0+)
function findUser(int $id): ?string
{
    // Returns null if user not found
    if ($id === 1) {
        return "Alice";
    }
    return null;
}

$user1 = findUser(1);
$user2 = findUser(999);

echo "User 1: " . ($user1 ?? "Not found") . PHP_EOL;
echo "User 2: " . ($user2 ?? "Not found") . PHP_EOL;
echo PHP_EOL;

// Mixed type (PHP 8.0+) - accepts any type
function processData(mixed $data): string
{
    return "Processing: " . gettype($data);
}

echo processData("hello") . PHP_EOL;
echo processData(42) . PHP_EOL;
echo processData([1, 2, 3]) . PHP_EOL;
echo PHP_EOL;

echo "Why use strict types?" . PHP_EOL;
echo "✓ Catches type errors early" . PHP_EOL;
echo "✓ Makes code more predictable" . PHP_EOL;
echo "✓ Improves IDE autocomplete and static analysis" . PHP_EOL;
echo "✓ Standard practice in modern PHP" . PHP_EOL;
