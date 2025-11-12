<?php

declare(strict_types=1);

// PHP 8.0+ union types
// Similar to Python's Union[str, int] from typing module

function processId(string|int $id): string
{
    return (string) $id;
}

function getValue(): string|int|null
{
    // May return string, int, or null
    return 42;
}

function formatValue(string|int|float $value): string
{
    return "Value: {$value}";
}

// Example usage
echo processId(123) . "\n";  // Output: 123
echo processId('abc') . "\n";  // Output: abc

$value = getValue();
var_dump($value);  // Output: int(42)

echo formatValue(42) . "\n";  // Output: Value: 42
echo formatValue(3.14) . "\n";  // Output: Value: 3.14
echo formatValue('hello') . "\n";  // Output: Value: hello

// Union types with arrays
function processItems(array|string $items): string
{
    if (is_array($items)) {
        return implode(', ', $items);
    }
    return $items;
}

echo processItems(['apple', 'banana', 'cherry']) . "\n";  // Output: apple, banana, cherry
echo processItems('single item') . "\n";  // Output: single item

