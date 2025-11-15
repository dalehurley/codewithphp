<?php
declare(strict_types=1);

/**
 * Closures and Variable Capture
 * Key difference from JavaScript: PHP requires explicit 'use' clause
 */

echo "=== Variable Capture Basics ===" . PHP_EOL . PHP_EOL;

// JavaScript: Variables automatically captured
// PHP: Must use 'use' clause for traditional closures

$multiplier = 10;

// ❌ Without 'use' - does NOT work
try {
    $broken = function(int $x): int {
        return $x * $multiplier; // Undefined variable error
    };
    // This will cause an error
} catch (Error $e) {
    echo "❌ Error without 'use': Variable not captured" . PHP_EOL;
}

// ✅ With 'use' clause - works
$fixed = function(int $x) use ($multiplier): int {
    return $x * $multiplier;
};
echo "✅ With 'use': " . $fixed(5) . PHP_EOL;
// Output: 50

// ✅ Arrow functions - automatic capture
$arrow = fn(int $x): int => $x * $multiplier;
echo "✅ Arrow function: " . $arrow(5) . PHP_EOL;
// Output: 50

// Capture by value vs reference
echo PHP_EOL . "=== Capture by Value vs Reference ===" . PHP_EOL . PHP_EOL;

$counter = 0;

// By value (default) - does NOT modify outer variable
$incrementByValue = function() use ($counter): void {
    $counter++; // Only modifies local copy
    echo "Inside (by value): {$counter}" . PHP_EOL;
};

$incrementByValue();
echo "Outside (unchanged): {$counter}" . PHP_EOL . PHP_EOL;
// Output: Outside (unchanged): 0

// By reference - DOES modify outer variable
$incrementByRef = function() use (&$counter): void {
    $counter++; // Modifies outer variable
    echo "Inside (by reference): {$counter}" . PHP_EOL;
};

$incrementByRef();
echo "Outside (changed): {$counter}" . PHP_EOL;
// Output: Outside (changed): 1

// Multiple variable capture
echo PHP_EOL . "=== Multiple Variable Capture ===" . PHP_EOL . PHP_EOL;

$prefix = "Hello";
$suffix = "!";
$count = 0;

$greet = function(string $name) use ($prefix, $suffix, &$count): string {
    $count++; // Increment by reference
    return "{$prefix}, {$name}{$suffix} (Call #{$count})";
};

echo $greet("Alice") . PHP_EOL;
echo $greet("Bob") . PHP_EOL;
echo "Total calls: {$count}" . PHP_EOL;

// Arrow functions capture by value only
echo PHP_EOL . "=== Arrow Functions (Value Only) ===" . PHP_EOL . PHP_EOL;

$value = 10;
$arrowFn = fn() => $value++; // ❌ This does NOT modify outer $value

$result = $arrowFn();
echo "Arrow function result: {$result}" . PHP_EOL; // 10
echo "Outer value (unchanged): {$value}" . PHP_EOL; // 10

// For mutation, use traditional closure with reference
$traditionalFn = function() use (&$value): int {
    return $value++;
};

$result = $traditionalFn();
echo "Traditional closure result: {$result}" . PHP_EOL; // 10
echo "Outer value (changed): {$value}" . PHP_EOL; // 11

// Practical example: Counter factory
echo PHP_EOL . "=== Practical: Counter Factory ===" . PHP_EOL . PHP_EOL;

function createCounter(int $start = 0): callable {
    $count = $start;

    return function() use (&$count): int {
        return ++$count;
    };
}

$counter1 = createCounter(0);
$counter2 = createCounter(100);

echo "Counter 1: " . $counter1() . PHP_EOL; // 1
echo "Counter 1: " . $counter1() . PHP_EOL; // 2
echo "Counter 2: " . $counter2() . PHP_EOL; // 101
echo "Counter 1: " . $counter1() . PHP_EOL; // 3

// Practical example: Logger with context
echo PHP_EOL . "=== Practical: Logger with Context ===" . PHP_EOL . PHP_EOL;

function createLogger(string $prefix): callable {
    $logCount = 0;

    return function(string $message) use ($prefix, &$logCount): void {
        $logCount++;
        $timestamp = date('H:i:s');
        echo "[{$timestamp}] [{$prefix}] #{$logCount}: {$message}" . PHP_EOL;
    };
}

$errorLog = createLogger('ERROR');
$infoLog = createLogger('INFO');

$errorLog("Database connection failed");
$infoLog("User logged in");
$errorLog("Timeout occurred");
$infoLog("Request completed");

// Practical example: Memoization
echo PHP_EOL . "=== Practical: Memoization ===" . PHP_EOL . PHP_EOL;

function memoize(callable $fn): callable {
    $cache = [];

    return function(...$args) use ($fn, &$cache) {
        $key = serialize($args);

        if (!isset($cache[$key])) {
            echo "  → Computing result..." . PHP_EOL;
            $cache[$key] = $fn(...$args);
        } else {
            echo "  → Using cached result" . PHP_EOL;
        }

        return $cache[$key];
    };
}

$expensiveOperation = function(int $x): int {
    sleep(1); // Simulate expensive operation
    return $x ** 2;
};

$memoized = memoize($expensiveOperation);

echo "First call (5): " . $memoized(5) . PHP_EOL;
echo "Second call (5): " . $memoized(5) . PHP_EOL;
echo "Third call (10): " . $memoized(10) . PHP_EOL;
echo "Fourth call (5): " . $memoized(5) . PHP_EOL;
