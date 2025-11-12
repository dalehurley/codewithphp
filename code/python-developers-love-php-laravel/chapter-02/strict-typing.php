<?php

declare(strict_types=1);  // Enables strict type checking

// With strict_types enabled, PHP enforces type checking at runtime
function add(int $a, int $b): int
{
    return $a + $b;
}

// This would cause a TypeError with strict_types enabled
// add('1', '2');  // ❌ Type error: Argument #1 must be of type int, string given

// This works correctly
echo "Result: " . add(1, 2) . "\n";  // ✅ Output: Result: 3

// Without strict_types (default), PHP would coerce types
// add('1', '2') would work and return 3 (string '1' coerced to int 1)

// Strict typing also applies to return types
function divide(int $a, int $b): float
{
    return $a / $b;
}

echo "Division: " . divide(10, 3) . "\n";  // Output: Division: 3.3333333333333

// Strict typing prevents common type-related bugs
function processUserId(int $userId): string
{
    return "Processing user ID: {$userId}";
}

echo processUserId(123) . "\n";  // ✅ Works: Output: Processing user ID: 123
// echo processUserId('123');  // ❌ Would fail with strict_types: TypeError

