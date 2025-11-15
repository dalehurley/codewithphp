<?php
declare(strict_types=1);

/**
 * Basic Type Annotations in PHP
 * Compare with TypeScript examples in typescript-examples/basic-types.ts
 */

// Function with typed parameters and return type
function greet(string $name): string {
    return "Hello, {$name}!";
}

// Integer and float distinction (unlike TypeScript's unified 'number')
function add(int $a, int $b): int {
    return $a + $b;
}

function addFloats(float $a, float $b): float {
    return $a + $b;
}

// Boolean type
function isAdult(int $age): bool {
    return $age >= 18;
}

// Void return type
function logMessage(string $message): void {
    echo $message . PHP_EOL;
}

// Mixed type (like TypeScript's 'any')
function processValue(mixed $value): mixed {
    if (is_string($value)) {
        return strtoupper($value);
    }
    if (is_int($value)) {
        return $value * 2;
    }
    return $value;
}

// Examples
echo greet("TypeScript Developer") . PHP_EOL;
// Output: Hello, TypeScript Developer!

echo add(5, 10) . PHP_EOL;
// Output: 15

echo addFloats(3.14, 2.86) . PHP_EOL;
// Output: 6

echo (isAdult(25) ? "Adult" : "Minor") . PHP_EOL;
// Output: Adult

logMessage("This is a log message");
// Output: This is a log message

echo processValue("hello") . PHP_EOL;
// Output: HELLO

echo processValue(21) . PHP_EOL;
// Output: 42

// Try uncommenting this to see strict type error:
// echo add("5", "10"); // Fatal error: Argument #1 must be of type int, string given
