<?php

/**
 * Hash Function Implementations
 *
 * Various hash functions with different properties.
 *
 * @package CodeWithPHP\Algorithms\Chapter13
 */

declare(strict_types=1);

function hashDivision(string $key, int $size): int
{
    $hash = 0;
    for ($i = 0; $i < strlen($key); $i++) {
        $hash += ord($key[$i]);
    }
    return $hash % $size;
}

function hashPolynomial(string $key, int $size): int
{
    $hash = 0;
    $prime = 31;

    for ($i = 0; $i < strlen($key); $i++) {
        $hash = ($hash * $prime + ord($key[$i])) % $size;
    }

    return abs($hash);
}

function hashFNV1a(string $key): int
{
    $hash = 2166136261;

    for ($i = 0; $i < strlen($key); $i++) {
        $hash ^= ord($key[$i]);
        $hash *= 16777619;
    }

    return abs($hash);
}

// DEMONSTRATIONS
echo "=== Hash Function Comparison ===\n\n";

$keys = ['apple', 'banana', 'orange', 'grape'];
$size = 100;

foreach ($keys as $key) {
    echo "Key: $key\n";
    echo "  Division:   " . hashDivision($key, $size) . "\n";
    echo "  Polynomial: " . hashPolynomial($key, $size) . "\n";
    echo "  FNV-1a:     " . (hashFNV1a($key) % $size) . "\n\n";
}
