<?php

declare(strict_types=1);

// PHP 7.0+ type declarations for function parameters and return types
function calculateTotal(float $items, float $tax): float
{
    return $items * (1 + $tax);
}

function getUserName(int $userId): string
{
    return "User {$userId}";
}

function getUserAge(int $userId): ?int  // Nullable return type
{
    // May return null if user not found
    return null;
}

// Example usage
$total = calculateTotal(100.0, 0.1);
echo "Total: {$total}\n";  // Output: Total: 110

$name = getUserName(123);
echo "Name: {$name}\n";  // Output: Name: User 123

$age = getUserAge(123);
var_dump($age);  // Output: NULL

