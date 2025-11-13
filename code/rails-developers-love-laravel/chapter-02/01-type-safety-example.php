<?php
/**
 * Chapter 02: Modern PHP - Type Safety
 * 
 * This example demonstrates PHP 8.4's strict type system
 * and how it catches errors that old PHP would silently convert.
 */

declare(strict_types=1);

// ============ STRICT TYPES EXAMPLE ============

function getUser(int $id): string|null
{
    // Simulate database lookup
    if ($id === 1) {
        return 'John Doe';
    }
    return null;
}

// Valid calls - these work
echo "Valid calls:\n";
echo getUser(1) ?? 'Not found' . "\n";  // "John Doe"
echo getUser(999) ?? 'Not found' . "\n"; // "Not found"

// Invalid calls - these would throw TypeError
// Uncomment to test:
// getUser("hello");     // TypeError: getUser(): Argument #1 must be of type int
// getUser([1, 2, 3]);   // TypeError
// getUser(null);        // TypeError

// ============ UNION TYPES ============

function calculateTotal(int|float $price, float $taxRate = 0.1): float
{
    return $price * (1 + $taxRate);
}

echo "\nUnion types:\n";
echo "calculateTotal(100, 0.1) = " . calculateTotal(100, 0.1) . "\n";      // 110.0
echo "calculateTotal(99.99, 0.1) = " . calculateTotal(99.99, 0.1) . "\n"; // 109.989

// ============ NULLABLE TYPES ============

function getName(?string $firstName, ?string $lastName): ?string
{
    if ($firstName === null || $lastName === null) {
        return null;
    }
    return "$firstName $lastName";
}

echo "\nNullable types:\n";
echo getName('John', 'Doe') ?? 'No name' . "\n";     // "John Doe"
echo getName('John', null) ?? 'No name' . "\n";      // "No name"
echo getName(null, null) ?? 'No name' . "\n";        // "No name"

// ============ TYPE CASTING ============

function processValue(mixed $value): string
{
    return match (gettype($value)) {
        'integer' => "Integer: $value",
        'double' => "Float: $value",
        'string' => "String: $value",
        'array' => "Array with " . count($value) . " items",
        'NULL' => "Is null",
        default => "Unknown type",
    };
}

echo "\nType checking:\n";
echo processValue(42) . "\n";           // "Integer: 42"
echo processValue(3.14) . "\n";         // "Float: 3.14"
echo processValue("hello") . "\n";      // "String: hello"
echo processValue([1, 2, 3]) . "\n";    // "Array with 3 items"
echo processValue(null) . "\n";         // "Is null"

// ============ RETURN TYPE DECLARATIONS ============

class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function divide(int $dividend, int $divisor): float
    {
        if ($divisor === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return $dividend / $divisor;
    }

    public function describe(): string
    {
        return 'I am a calculator';
    }
}

echo "\nReturn types:\n";
$calc = new Calculator();
echo "5 + 3 = " . $calc->add(5, 3) . "\n";           // 5 + 3 = 8
echo "10 / 3 = " . $calc->divide(10, 3) . "\n";      // 10 / 3 = 3.333...
echo $calc->describe() . "\n";                        // "I am a calculator"

echo "\n✓ All type safety examples completed successfully!\n";

