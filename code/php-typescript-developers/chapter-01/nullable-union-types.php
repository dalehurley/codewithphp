<?php
declare(strict_types=1);

/**
 * Nullable and Union Types in PHP
 * Compare with TypeScript: T | null → PHP: ?T
 */

// Nullable type (? prefix means "Type or null")
function findUser(int $id): ?string {
    $users = [
        1 => "Alice",
        2 => "Bob",
        3 => "Charlie"
    ];

    return $users[$id] ?? null;
}

// Union types (PHP 8.0+)
function format(string|int|float $value): string {
    if (is_int($value)) {
        return "Integer: {$value}";
    }
    if (is_float($value)) {
        return "Float: " . number_format($value, 2);
    }
    return "String: " . strtoupper($value);
}

// Combining nullable with union types
function processInput(string|int|null $input): string {
    if ($input === null) {
        return "No input provided";
    }
    if (is_int($input)) {
        return "Number: {$input}";
    }
    return "Text: {$input}";
}

// Complex union type
function calculate(int|float $a, int|float $b, string $operation): int|float {
    return match($operation) {
        'add' => $a + $b,
        'subtract' => $a - $b,
        'multiply' => $a * $b,
        'divide' => $b !== 0 ? $a / $b : 0,
        default => 0
    };
}

// Examples
echo "Finding users:" . PHP_EOL;
$user = findUser(1);
echo "User 1: " . ($user ?? "Not found") . PHP_EOL;
// Output: User 1: Alice

$user = findUser(999);
echo "User 999: " . ($user ?? "Not found") . PHP_EOL;
// Output: User 999: Not found

echo PHP_EOL . "Formatting values:" . PHP_EOL;
echo format(42) . PHP_EOL;
// Output: Integer: 42

echo format(3.14159) . PHP_EOL;
// Output: Float: 3.14

echo format("hello") . PHP_EOL;
// Output: String: HELLO

echo PHP_EOL . "Processing inputs:" . PHP_EOL;
echo processInput(null) . PHP_EOL;
// Output: No input provided

echo processInput(100) . PHP_EOL;
// Output: Number: 100

echo processInput("TypeScript") . PHP_EOL;
// Output: Text: TypeScript

echo PHP_EOL . "Calculations:" . PHP_EOL;
echo "5 + 3 = " . calculate(5, 3, 'add') . PHP_EOL;
// Output: 5 + 3 = 8

echo "10.5 * 2 = " . calculate(10.5, 2, 'multiply') . PHP_EOL;
// Output: 10.5 * 2 = 21

/**
 * Key Differences from TypeScript:
 *
 * TypeScript:
 *   function findUser(id: number): string | null
 *
 * PHP:
 *   function findUser(int $id): ?string
 *
 * TypeScript:
 *   function format(value: string | number): string
 *
 * PHP:
 *   function format(string|int|float $value): string
 *   // Note: PHP distinguishes int and float, no unified "number" type
 */
