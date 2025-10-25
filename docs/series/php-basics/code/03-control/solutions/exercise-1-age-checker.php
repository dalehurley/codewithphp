<?php

declare(strict_types=1);

/**
 * Exercise 1: Age Category Checker
 * 
 * Write a program that determines age category:
 * - 0-12: Child
 * - 13-17: Teenager
 * - 18-64: Adult
 * - 65+: Senior
 */

// Solution:

$age = 25;

echo "=== Age Category Checker ===" . PHP_EOL;
echo "Age: $age" . PHP_EOL;

if ($age >= 0 && $age <= 12) {
    echo "Category: Child" . PHP_EOL;
} elseif ($age >= 13 && $age <= 17) {
    echo "Category: Teenager" . PHP_EOL;
} elseif ($age >= 18 && $age <= 64) {
    echo "Category: Adult" . PHP_EOL;
} elseif ($age >= 65) {
    echo "Category: Senior" . PHP_EOL;
} else {
    echo "Category: Invalid age" . PHP_EOL;
}

echo PHP_EOL;

// Alternative solution using match (PHP 8.0+)
echo "Alternative using match:" . PHP_EOL;
$category = match (true) {
    $age >= 0 && $age <= 12 => 'Child',
    $age >= 13 && $age <= 17 => 'Teenager',
    $age >= 18 && $age <= 64 => 'Adult',
    $age >= 65 => 'Senior',
    default => 'Invalid age'
};

echo "Age: $age - Category: $category" . PHP_EOL;
