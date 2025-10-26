<?php

declare(strict_types=1);

/**
 * Variable Scope and Variadic Functions
 * 
 * Covers variable scope (local, global, static) and functions that
 * accept a variable number of arguments (variadic functions).
 */

echo "=== Variable Scope and Variadic Functions ===" . PHP_EOL . PHP_EOL;

// Example 1: Local scope
echo "1. Local Scope:" . PHP_EOL;

$globalVar = "I'm global";

function testLocalScope(): void
{
    $localVar = "I'm local";
    echo "  Inside function: $localVar" . PHP_EOL;

    // Cannot access $globalVar directly
    // echo $globalVar; // This would cause an error
}

testLocalScope();
echo "  Outside function: $globalVar" . PHP_EOL;
// echo $localVar; // This would cause an error - $localVar doesn't exist here
echo PHP_EOL;

// Example 2: Global scope (accessing global variables)
echo "2. Global Keyword:" . PHP_EOL;

$counter = 0;

function increment(): void
{
    global $counter; // Declare we're using the global variable
    $counter++;
    echo "  Counter inside function: $counter" . PHP_EOL;
}

echo "  Counter before: $counter" . PHP_EOL;
increment();
increment();
echo "  Counter after: $counter" . PHP_EOL;
echo PHP_EOL;

// Example 3: Alternative to global keyword - $GLOBALS superglobal
echo "3. \$GLOBALS Superglobal:" . PHP_EOL;

$total = 0;

function addToTotal(int $amount): void
{
    $GLOBALS['total'] += $amount;
    echo "  Added $amount, total is now {$GLOBALS['total']}" . PHP_EOL;
}

addToTotal(10);
addToTotal(25);
echo "  Final total: $total" . PHP_EOL;
echo PHP_EOL;

// Example 4: Static variables (persist between function calls)
echo "4. Static Variables:" . PHP_EOL;

function countCalls(): int
{
    static $count = 0; // Initialized only once
    $count++;
    return $count;
}

echo "  Call 1: " . countCalls() . PHP_EOL;
echo "  Call 2: " . countCalls() . PHP_EOL;
echo "  Call 3: " . countCalls() . PHP_EOL;
echo "  Call 4: " . countCalls() . PHP_EOL;
echo PHP_EOL;

// Example 5: Variadic functions - variable number of arguments
echo "5. Variadic Functions (...):" . PHP_EOL;

function sum(int ...$numbers): int
{
    $total = 0;
    foreach ($numbers as $number) {
        $total += $number;
    }
    return $total;
}

echo "  sum(1, 2, 3) = " . sum(1, 2, 3) . PHP_EOL;
echo "  sum(1, 2, 3, 4, 5) = " . sum(1, 2, 3, 4, 5) . PHP_EOL;
echo "  sum(10, 20) = " . sum(10, 20) . PHP_EOL;
echo PHP_EOL;

// Example 6: Variadic with type declarations
echo "6. Typed Variadic Functions:" . PHP_EOL;

function concatenate(string $separator, string ...$words): string
{
    return implode($separator, $words);
}

echo "  " . concatenate(", ", "apple", "banana", "cherry") . PHP_EOL;
echo "  " . concatenate(" - ", "PHP", "is", "awesome") . PHP_EOL;
echo PHP_EOL;

// Example 7: Named arguments (PHP 8.0+)
echo "7. Named Arguments:" . PHP_EOL;

function createProduct(
    string $name,
    float $price,
    int $quantity = 1,
    bool $inStock = true
): array {
    return [
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'inStock' => $inStock
    ];
}

// Can call with arguments in any order using names
$product1 = createProduct(name: "Laptop", price: 999.99);
print_r($product1);

$product2 = createProduct(
    price: 29.99,
    name: "Mouse",
    inStock: false,
    quantity: 5
);
print_r($product2);
echo PHP_EOL;

// Example 8: Combining regular and variadic parameters
echo "8. Mixed Parameters:" . PHP_EOL;

function logMessage(string $level, string $message, string ...$context): void
{
    echo "  [$level] $message";

    if (count($context) > 0) {
        echo " | Context: " . implode(', ', $context);
    }

    echo PHP_EOL;
}

logMessage("INFO", "User logged in");
logMessage("ERROR", "Database connection failed", "host: localhost", "port: 3306");
logMessage("WARNING", "Slow query", "duration: 2.5s", "table: users");
echo PHP_EOL;

// Example 9: Unpacking arrays into function arguments
echo "9. Argument Unpacking:" . PHP_EOL;

function multiply(int $a, int $b, int $c): int
{
    return $a * $b * $c;
}

$values = [2, 3, 4];
$result = multiply(...$values); // Unpack array into arguments
echo "  multiply(2, 3, 4) = $result" . PHP_EOL;
echo PHP_EOL;

// Example 10: Practical example - Flexible formatting function
echo "10. Practical Example - Format Money:" . PHP_EOL;

function formatMoney(
    float $amount,
    string $currency = 'USD',
    int $decimals = 2,
    string $decimalSeparator = '.',
    string $thousandsSeparator = ','
): string {
    $formatted = number_format($amount, $decimals, $decimalSeparator, $thousandsSeparator);

    return match ($currency) {
        'USD' => "\$$formatted",
        'EUR' => "€$formatted",
        'GBP' => "£$formatted",
        default => "$formatted $currency"
    };
}

echo "  " . formatMoney(1234.56) . PHP_EOL;
echo "  " . formatMoney(1234.56, currency: 'EUR') . PHP_EOL;
echo "  " . formatMoney(1234567.89, 'EUR', thousandsSeparator: ' ') . PHP_EOL;
echo PHP_EOL;

// Example 11: Practical example - Logging with static counter
echo "11. Request Logger with Static Counter:" . PHP_EOL;

function logRequest(string $method, string $url): void
{
    static $requestCount = 0;
    $requestCount++;

    $timestamp = date('Y-m-d H:i:s');
    echo "  [$requestCount] $timestamp - $method $url" . PHP_EOL;
}

logRequest('GET', '/home');
logRequest('POST', '/api/users');
logRequest('GET', '/api/products');
logRequest('DELETE', '/api/users/123');
echo PHP_EOL;

// Example 12: Practical example - Calculate average of any number of grades
echo "12. Calculate Average (Variadic):" . PHP_EOL;

function calculateAverage(float ...$grades): float
{
    if (count($grades) === 0) {
        return 0.0;
    }

    return array_sum($grades) / count($grades);
}

$avg1 = calculateAverage(85, 90, 78, 92);
echo "  Average of 4 grades: " . round($avg1, 2) . PHP_EOL;

$avg2 = calculateAverage(75, 80, 85, 90, 88, 92, 79);
echo "  Average of 7 grades: " . round($avg2, 2) . PHP_EOL;
echo PHP_EOL;

// Example 13: Best practices note
echo "13. Best Practices:" . PHP_EOL;
echo "  ✓ Avoid global variables - pass parameters instead" . PHP_EOL;
echo "  ✓ Use static variables sparingly - can make testing harder" . PHP_EOL;
echo "  ✓ Use named arguments for clarity with many parameters" . PHP_EOL;
echo "  ✓ Use variadic functions for flexible APIs" . PHP_EOL;
echo "  ✓ Type hint variadic parameters for safety" . PHP_EOL;
