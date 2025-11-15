<?php
declare(strict_types=1);

/**
 * Arrow Functions in PHP (7.4+)
 * Similar to TypeScript arrow functions but with 'fn' keyword
 */

echo "=== Arrow Functions ===" . PHP_EOL . PHP_EOL;

// Simple arrow function
$double = fn(int $x): int => $x * 2;
echo "double(5) = " . $double(5) . PHP_EOL;
// Output: double(5) = 10

// Arrow function with type inference
$triple = fn($x) => $x * 3;
echo "triple(5) = " . $triple(5) . PHP_EOL;
// Output: triple(5) = 15

// Automatic variable capture (no 'use' needed)
$multiplier = 10;
$multiplyBy = fn(int $x): int => $x * $multiplier;
echo "multiplyBy(5) = " . $multiplyBy(5) . PHP_EOL;
// Output: multiplyBy(5) = 50

// Array methods with arrow functions
echo PHP_EOL . "=== Array Operations ===" . PHP_EOL . PHP_EOL;

$numbers = [1, 2, 3, 4, 5];

$doubled = array_map(fn($x) => $x * 2, $numbers);
echo "Doubled: " . implode(', ', $doubled) . PHP_EOL;
// Output: Doubled: 2, 4, 6, 8, 10

$evens = array_filter($numbers, fn($x) => $x % 2 === 0);
echo "Evens: " . implode(', ', $evens) . PHP_EOL;
// Output: Evens: 2, 4

$sum = array_reduce($numbers, fn($acc, $val) => $acc + $val, 0);
echo "Sum: {$sum}" . PHP_EOL;
// Output: Sum: 15

// Multi-line requires traditional closure
echo PHP_EOL . "=== Multi-line Closure ===" . PHP_EOL . PHP_EOL;

$complexOperation = function(int $x): int {
    $temp = $x * 2;
    $temp += 10;
    return $temp ** 2;
};

echo "complexOperation(5) = " . $complexOperation(5) . PHP_EOL;
// Output: complexOperation(5) = 400

// Comparison: Traditional vs Arrow
echo PHP_EOL . "=== Traditional vs Arrow ===" . PHP_EOL . PHP_EOL;

$factor = 5;

// Traditional closure (requires 'use')
$traditionalMultiply = function(int $x) use ($factor): int {
    return $x * $factor;
};

// Arrow function (automatic capture)
$arrowMultiply = fn(int $x): int => $x * $factor;

echo "Traditional: " . $traditionalMultiply(10) . PHP_EOL;
echo "Arrow: " . $arrowMultiply(10) . PHP_EOL;
// Both output: 50

// Higher-order functions
echo PHP_EOL . "=== Higher-Order Functions ===" . PHP_EOL . PHP_EOL;

$makeAdder = fn(int $n): callable => fn(int $x): int => $x + $n;

$add5 = $makeAdder(5);
$add10 = $makeAdder(10);

echo "add5(3) = " . $add5(3) . PHP_EOL;  // 8
echo "add10(3) = " . $add10(3) . PHP_EOL; // 13
