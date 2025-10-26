<?php

declare(strict_types=1);

/**
 * Arrow Functions and Closures
 * 
 * Arrow functions (PHP 7.4+) provide a shorter syntax for simple functions.
 * Closures (anonymous functions) are functions without a name that can
 * capture variables from their surrounding scope.
 */

echo "=== Arrow Functions and Closures ===" . PHP_EOL . PHP_EOL;

// Example 1: Traditional function vs arrow function
echo "1. Arrow Function Syntax:" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5];

// Traditional anonymous function
$doubled = array_map(function ($n) {
    return $n * 2;
}, $numbers);
echo "Doubled (traditional): " . implode(', ', $doubled) . PHP_EOL;

// Arrow function (shorter syntax)
$tripled = array_map(fn($n) => $n * 3, $numbers);
echo "Tripled (arrow function): " . implode(', ', $tripled) . PHP_EOL;
echo PHP_EOL;

// Example 2: Arrow functions with array_filter
echo "2. Arrow Functions with array_filter:" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Get even numbers
$evens = array_filter($numbers, fn($n) => $n % 2 === 0);
echo "Even numbers: " . implode(', ', $evens) . PHP_EOL;

// Get numbers greater than 5
$greaterThanFive = array_filter($numbers, fn($n) => $n > 5);
echo "Greater than 5: " . implode(', ', $greaterThanFive) . PHP_EOL;
echo PHP_EOL;

// Example 3: Variable capture in arrow functions (automatic)
echo "3. Automatic Variable Capture:" . PHP_EOL;

$multiplier = 10;

// Arrow functions automatically capture variables from parent scope
$scaled = array_map(fn($n) => $n * $multiplier, [1, 2, 3]);
echo "Scaled by $multiplier: " . implode(', ', $scaled) . PHP_EOL;
echo PHP_EOL;

// Example 4: Closures (anonymous functions)
echo "4. Anonymous Functions (Closures):" . PHP_EOL;

$greeting = "Hello";

// Closure with 'use' keyword to capture variables
$greet = function (string $name) use ($greeting): string {
    return "$greeting, $name!";
};

echo $greet("Alice") . PHP_EOL;
echo $greet("Bob") . PHP_EOL;
echo PHP_EOL;

// Example 5: Modifying captured variables (by reference)
echo "5. Capturing by Reference:" . PHP_EOL;

$counter = 0;

// Capture by value (won't modify original)
$incrementCopy = function () use ($counter): void {
    $counter++;
    echo "  Inside function (by value): $counter" . PHP_EOL;
};

$incrementCopy();
echo "  Outside function: $counter (unchanged)" . PHP_EOL;
echo PHP_EOL;

// Capture by reference (will modify original)
$counter = 0;
$incrementRef = function () use (&$counter): void {
    $counter++;
    echo "  Inside function (by reference): $counter" . PHP_EOL;
};

$incrementRef();
echo "  Outside function: $counter (modified)" . PHP_EOL;
$incrementRef();
echo "  Outside function: $counter (modified again)" . PHP_EOL;
echo PHP_EOL;

// Example 6: Returning closures from functions
echo "6. Returning Closures:" . PHP_EOL;

function makeMultiplier(int $factor): callable
{
    return fn($n) => $n * $factor;
}

$double = makeMultiplier(2);
$triple = makeMultiplier(3);

echo "5 doubled: " . $double(5) . PHP_EOL;
echo "5 tripled: " . $triple(5) . PHP_EOL;
echo PHP_EOL;

// Example 7: Closures with multiple captured variables
echo "7. Multiple Captured Variables:" . PHP_EOL;

$prefix = "Mr.";
$suffix = "Esq.";

$formatName = function (string $name) use ($prefix, $suffix): string {
    return "$prefix $name, $suffix";
};

echo $formatName("Smith") . PHP_EOL;
echo $formatName("Johnson") . PHP_EOL;
echo PHP_EOL;

// Example 8: Practical example - Custom sorting
echo "8. Custom Sorting with Closures:" . PHP_EOL;

$products = [
    ['name' => 'Laptop', 'price' => 999.99],
    ['name' => 'Mouse', 'price' => 29.99],
    ['name' => 'Keyboard', 'price' => 79.99],
    ['name' => 'Monitor', 'price' => 399.99]
];

// Sort by price (ascending)
usort($products, fn($a, $b) => $a['price'] <=> $b['price']);

echo "Sorted by price (ascending):" . PHP_EOL;
foreach ($products as $product) {
    echo "  {$product['name']}: \${$product['price']}" . PHP_EOL;
}
echo PHP_EOL;

// Example 9: Array operations with arrow functions
echo "9. Chaining Array Operations:" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// Get squares of even numbers greater than 2
$result = array_map(
    fn($n) => $n ** 2,                    // Square each number
    array_filter(
        $numbers,
        fn($n) => $n % 2 === 0 && $n > 2  // Only even numbers > 2
    )
);

echo "Squares of even numbers > 2: " . implode(', ', $result) . PHP_EOL;
echo PHP_EOL;

// Example 10: Practical example - Data transformation
echo "10. Data Transformation Pipeline:" . PHP_EOL;

$users = [
    ['id' => 1, 'name' => 'alice johnson', 'age' => 28],
    ['id' => 2, 'name' => 'bob smith', 'age' => 17],
    ['id' => 3, 'name' => 'charlie brown', 'age' => 35]
];

// Filter adults, capitalize names
$adults = array_filter($users, fn($user) => $user['age'] >= 18);

$formattedAdults = array_map(
    fn($user) => [
        'id' => $user['id'],
        'name' => ucwords($user['name']), // Capitalize
        'age' => $user['age']
    ],
    $adults
);

echo "Adult users:" . PHP_EOL;
foreach ($formattedAdults as $user) {
    echo "  [{$user['id']}] {$user['name']} (Age: {$user['age']})" . PHP_EOL;
}
echo PHP_EOL;

// Example 11: Practical example - Event handlers (callback pattern)
echo "11. Callback Pattern:" . PHP_EOL;

function processData(array $data, callable $validator, callable $transformer): array
{
    // Filter using validator
    $valid = array_filter($data, $validator);

    // Transform using transformer
    return array_map($transformer, $valid);
}

$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

$result = processData(
    $numbers,
    fn($n) => $n % 2 === 0,              // Validator: only evens
    fn($n) => $n * $n                    // Transformer: square them
);

echo "Squares of even numbers: " . implode(', ', $result) . PHP_EOL;
echo PHP_EOL;

// Example 12: First-class callable syntax (PHP 8.1+)
echo "12. First-Class Callables (PHP 8.1+):" . PHP_EOL;

function square(int $n): int
{
    return $n * $n;
}

// Old way
$squares1 = array_map('square', [1, 2, 3, 4]);

// New way (PHP 8.1+)
$squares2 = array_map(square(...), [1, 2, 3, 4]);

echo "Squares: " . implode(', ', $squares2) . PHP_EOL;
