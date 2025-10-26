<?php

declare(strict_types=1);

/**
 * PHP Data Types Demonstration
 * 
 * This script demonstrates all major PHP data types:
 * - String
 * - Integer
 * - Float (double)
 * - Boolean
 * - Array
 * - NULL
 * 
 * PHP 8.4 compatible with strict types enabled
 */

echo "=== PHP Data Types ===" . PHP_EOL . PHP_EOL;

// STRING - text data
$name = "Alice";
$greeting = 'Hello, World!';
$multiline = "This is a
multi-line string";

echo "STRING Examples:" . PHP_EOL;
echo "Name: $name" . PHP_EOL;
echo "Greeting: $greeting" . PHP_EOL;
echo "Type: " . gettype($name) . PHP_EOL;
echo PHP_EOL;

// INTEGER - whole numbers
$age = 25;
$negative = -100;
$large = 1_000_000; // PHP 7.4+ allows underscores for readability

echo "INTEGER Examples:" . PHP_EOL;
echo "Age: $age" . PHP_EOL;
echo "Negative: $negative" . PHP_EOL;
echo "Large number: $large" . PHP_EOL;
echo "Type: " . gettype($age) . PHP_EOL;
echo PHP_EOL;

// FLOAT - decimal numbers
$price = 19.99;
$pi = 3.14159;
$scientific = 1.5e3; // 1500 in scientific notation

echo "FLOAT Examples:" . PHP_EOL;
echo "Price: $price" . PHP_EOL;
echo "Pi: $pi" . PHP_EOL;
echo "Scientific notation (1.5e3): $scientific" . PHP_EOL;
echo "Type: " . gettype($price) . PHP_EOL;
echo PHP_EOL;

// BOOLEAN - true or false
$isLoggedIn = true;
$hasError = false;

echo "BOOLEAN Examples:" . PHP_EOL;
echo "Is logged in: " . ($isLoggedIn ? "true" : "false") . PHP_EOL;
echo "Has error: " . ($hasError ? "true" : "false") . PHP_EOL;
echo "Type: " . gettype($isLoggedIn) . PHP_EOL;
echo PHP_EOL;

// ARRAY - collections of values
$colors = ["red", "green", "blue"];
$person = [
    "name" => "Bob",
    "age" => 30,
    "city" => "New York"
];

echo "ARRAY Examples:" . PHP_EOL;
echo "Colors: " . implode(", ", $colors) . PHP_EOL;
echo "Person name: " . $person["name"] . PHP_EOL;
echo "Person age: " . $person["age"] . PHP_EOL;
echo "Type: " . gettype($colors) . PHP_EOL;
echo PHP_EOL;

// NULL - absence of value
$notSet = null;

echo "NULL Example:" . PHP_EOL;
echo "Not set value: " . ($notSet === null ? "NULL" : $notSet) . PHP_EOL;
echo "Type: " . gettype($notSet) . PHP_EOL;
echo PHP_EOL;

// Type checking functions
echo "=== Type Checking ===" . PHP_EOL;
echo "is_string(\$name): " . (is_string($name) ? "true" : "false") . PHP_EOL;
echo "is_int(\$age): " . (is_int($age) ? "true" : "false") . PHP_EOL;
echo "is_float(\$price): " . (is_float($price) ? "true" : "false") . PHP_EOL;
echo "is_bool(\$isLoggedIn): " . (is_bool($isLoggedIn) ? "true" : "false") . PHP_EOL;
echo "is_array(\$colors): " . (is_array($colors) ? "true" : "false") . PHP_EOL;
echo "is_null(\$notSet): " . (is_null($notSet) ? "true" : "false") . PHP_EOL;
