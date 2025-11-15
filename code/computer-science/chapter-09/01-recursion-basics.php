<?php

declare(strict_types=1);

/**
 * Basic Recursion Fundamentals
 *
 * Demonstrates:
 * - What is recursion
 * - Base case and recursive case
 * - Call stack visualization
 * - Simple recursive examples
 * - Factorial, countdown, sum
 * - How recursion builds and unwinds
 */

echo "=== Basic Recursion Fundamentals ===\n\n";

echo "Recursion: A function that calls itself to solve smaller versions\n";
echo "           of the same problem until reaching a base case.\n\n";

/**
 * Example 1: Factorial - Classic recursion example
 */
function factorial(int $n): int
{
    // Base case: stop recursion
    if ($n === 0 || $n === 1) {
        return 1;
    }

    // Recursive case: n! = n × (n-1)!
    return $n * factorial($n - 1);
}

// Test 1: Factorial
echo "1. Factorial (n! = n × (n-1) × ... × 1)\n";
echo "========================================\n\n";

$test = 5;
echo "Calculate $test!:\n\n";

// Show the recursion visually
echo "Recursion breakdown:\n";
echo "  factorial(5)\n";
echo "  = 5 × factorial(4)\n";
echo "  = 5 × (4 × factorial(3))\n";
echo "  = 5 × (4 × (3 × factorial(2)))\n";
echo "  = 5 × (4 × (3 × (2 × factorial(1))))\n";
echo "  = 5 × (4 × (3 × (2 × 1)))\n";
echo "  = 5 × (4 × (3 × 2))\n";
echo "  = 5 × (4 × 6)\n";
echo "  = 5 × 24\n";
echo "  = 120\n\n";

$result = factorial($test);
echo "Result: $test! = $result\n\n";

// More examples
echo "More factorials:\n";
for ($i = 0; $i <= 10; $i++) {
    echo "  $i! = " . factorial($i) . "\n";
}
echo "\n";

/**
 * Example 2: Factorial with tracing to visualize call stack
 */
function factorialTrace(int $n, int $depth = 0): int
{
    $indent = str_repeat("  ", $depth);

    echo "{$indent}→ factorial($n) called\n";

    if ($n === 0 || $n === 1) {
        echo "{$indent}← Base case! Returning 1\n";
        return 1;
    }

    echo "{$indent}  Computing $n × factorial(" . ($n - 1) . ")\n";
    $result = $n * factorialTrace($n - 1, $depth + 1);

    echo "{$indent}← Returning $result\n";
    return $result;
}

// Test 2: Factorial with call stack trace
echo "2. Factorial with Call Stack Trace\n";
echo "====================================\n\n";

echo "Computing factorial(4) with trace:\n\n";
$result = factorialTrace(4);
echo "\nFinal result: $result\n\n";

/**
 * Example 3: Countdown - Simple recursion
 */
function countdown(int $n): void
{
    // Base case
    if ($n <= 0) {
        echo "Blastoff! 🚀\n";
        return;
    }

    // Recursive case
    echo "$n... ";
    countdown($n - 1);
}

// Test 3: Countdown
echo "3. Countdown\n";
echo "=============\n\n";

echo "Countdown from 10:\n";
countdown(10);
echo "\n";

/**
 * Example 4: Count up (shows recursion can happen before or after recursive call)
 */
function countUp(int $start, int $end): void
{
    // Base case
    if ($start > $end) {
        return;
    }

    echo "$start ";

    // Recursive call
    countUp($start + 1, $end);
}

function countDown(int $start, int $end): void
{
    // Base case
    if ($start > $end) {
        return;
    }

    // Recursive call FIRST
    countDown($start + 1, $end);

    // Then print (after returning)
    echo "$start ";
}

// Test 4: Count up vs count down
echo "4. Count Up vs Count Down\n";
echo "==========================\n\n";

echo "Count up (print before recursive call): ";
countUp(1, 10);
echo "\n\n";

echo "Count down (print after recursive call): ";
countDown(1, 10);
echo "\n\n";

/**
 * Example 5: Sum of numbers 1 to n
 */
function sumToN(int $n): int
{
    // Base case
    if ($n === 0) {
        return 0;
    }

    // Recursive case: n + sum(n-1)
    return $n + sumToN($n - 1);
}

// Test 5: Sum to N
echo "5. Sum of Numbers 1 to N\n";
echo "=========================\n\n";

for ($n = 1; $n <= 10; $n++) {
    $sum = sumToN($n);
    echo "Sum(1..$n) = $sum\n";
}

// Verify with formula: n(n+1)/2
echo "\nVerification with formula n(n+1)/2:\n";
$n = 100;
$recursive = sumToN($n);
$formula = ($n * ($n + 1)) / 2;
echo "  Recursive: sum(1..100) = $recursive\n";
echo "  Formula:   100×101/2 = $formula\n";
echo "  Match: " . ($recursive === $formula ? "✓" : "✗") . "\n\n";

/**
 * Example 6: Power function (base^exponent)
 */
function power(int $base, int $exp): int
{
    // Base case
    if ($exp === 0) {
        return 1;
    }

    // Recursive case
    return $base * power($base, $exp - 1);
}

// Test 6: Power function
echo "6. Power Function (base^exponent)\n";
echo "===================================\n\n";

$tests = [
    [2, 0],
    [2, 1],
    [2, 5],
    [3, 3],
    [5, 4],
];

foreach ($tests as [$base, $exp]) {
    $result = power($base, $exp);
    echo "$base^$exp = $result\n";
}
echo "\n";

/**
 * Example 7: GCD (Greatest Common Divisor) - Euclidean algorithm
 */
function gcd(int $a, int $b): int
{
    // Base case
    if ($b === 0) {
        return $a;
    }

    // Recursive case: gcd(a,b) = gcd(b, a mod b)
    return gcd($b, $a % $b);
}

// Test 7: GCD
echo "7. GCD (Greatest Common Divisor)\n";
echo "==================================\n\n";

$pairs = [
    [48, 18],
    [100, 35],
    [56, 98],
    [17, 19],
];

echo "Using Euclidean algorithm:\n\n";

foreach ($pairs as [$a, $b]) {
    $result = gcd($a, $b);
    echo "gcd($a, $b) = $result\n";
}
echo "\n";

/**
 * Example 8: Print array elements recursively
 */
function printArray(array $arr, int $index = 0): void
{
    // Base case: reached end
    if ($index >= count($arr)) {
        return;
    }

    // Print current element
    echo $arr[$index] . " ";

    // Recurse for rest
    printArray($arr, $index + 1);
}

function printArrayReverse(array $arr, int $index = 0): void
{
    // Base case
    if ($index >= count($arr)) {
        return;
    }

    // Recurse FIRST
    printArrayReverse($arr, $index + 1);

    // Then print (after returning)
    echo $arr[$index] . " ";
}

// Test 8: Print array
echo "8. Print Array Elements\n";
echo "========================\n\n";

$numbers = [1, 2, 3, 4, 5];

echo "Forward (print before recursive call): ";
printArray($numbers);
echo "\n\n";

echo "Reverse (print after recursive call): ";
printArrayReverse($numbers);
echo "\n\n";

/**
 * Example 9: Check if number is even (using recursion - impractical but educational!)
 */
function isEven(int $n): bool
{
    // Make positive
    $n = abs($n);

    // Base cases
    if ($n === 0) {
        return true;
    }

    if ($n === 1) {
        return false;
    }

    // Recursive case: n is even if n-2 is even
    return isEven($n - 2);
}

// Test 9: Even checker
echo "9. Even Number Checker (Recursive)\n";
echo "====================================\n\n";

echo "Check if numbers are even:\n";
for ($i = 0; $i <= 10; $i++) {
    $even = isEven($i);
    $status = $even ? "even" : "odd";
    echo "  $i is $status\n";
}
echo "\n";

/**
 * Example 10: The anatomy of recursion
 */
echo "10. Anatomy of Every Recursive Function\n";
echo "=========================================\n\n";

echo "Every recursive function has three parts:\n\n";

echo "1. BASE CASE (stopping condition)\n";
echo "   → Prevents infinite recursion\n";
echo "   → Returns simple answer without recursion\n";
echo "   → Example: if (n === 0) return 1;\n\n";

echo "2. RECURSIVE CASE (recursive call)\n";
echo "   → Calls itself with simpler/smaller input\n";
echo "   → Must make progress toward base case\n";
echo "   → Example: return n * factorial(n - 1);\n\n";

echo "3. PROGRESS TOWARD BASE CASE\n";
echo "   → Each call must get closer to base case\n";
echo "   → Usually: decrease number, shrink array, etc.\n";
echo "   → Example: n - 1, array_slice($arr, 1)\n\n";

echo "Template:\n";
echo "```php\n";
echo "function recursive(\$input) {\n";
echo "    // 1. Base case\n";
echo "    if (/* stopping condition */) {\n";
echo "        return /* simple answer */;\n";
echo "    }\n\n";
echo "    // 2. Recursive case\n";
echo "    // Do some work\n";
echo "    \$result = recursive(/* simpler input */);\n\n";
echo "    // 3. Combine and return\n";
echo "    return /* combine result */;\n";
echo "}\n";
echo "```\n\n";

echo "Common Mistakes:\n";
echo "  ❌ No base case → Infinite recursion → Stack overflow!\n";
echo "  ❌ Base case never reached → Infinite loop!\n";
echo "  ❌ Not making progress → Stuck in recursion!\n\n";

echo "How Recursion Works:\n";
echo "  1. Call stack BUILDS UP with each recursive call\n";
echo "  2. When base case reached, stack UNWINDS\n";
echo "  3. Each return pops one call off the stack\n";
echo "  4. Results combine as stack unwinds\n\n";

echo "Example with factorial(3):\n";
echo "  Call stack builds:         Stack unwinds:\n";
echo "  factorial(3)               factorial(3) = 6\n";
echo "    factorial(2)               factorial(2) = 2\n";
echo "      factorial(1)               factorial(1) = 1 ✓\n";
echo "        return 1\n\n";

echo "Key Insights:\n";
echo "===============\n\n";

echo "Time Complexity:\n";
echo "  • Usually O(n) for n recursive calls\n";
echo "  • Can be O(2^n) for binary recursion (fibonacci)\n";
echo "  • Can be O(log n) for divide-and-conquer (binary search)\n\n";

echo "Space Complexity:\n";
echo "  • O(n) for call stack depth\n";
echo "  • Each recursive call uses stack memory\n";
echo "  • Risk of stack overflow for deep recursion\n\n";

echo "When to Use Recursion:\n";
echo "  ✓ Problem naturally divides into subproblems\n";
echo "  ✓ Working with trees or graphs\n";
echo "  ✓ Backtracking problems\n";
echo "  ✓ Divide and conquer algorithms\n";
echo "  ✓ Code readability matters\n\n";

echo "When NOT to Use Recursion:\n";
echo "  ✗ Simple sequential processing (use loops)\n";
echo "  ✗ Performance critical (iteration faster)\n";
echo "  ✗ Risk of stack overflow (use iteration)\n";
echo "  ✗ Tail recursion not optimized (PHP doesn't optimize)\n\n";

echo "Recursion vs Iteration:\n";
echo "  Recursion: Elegant, expressive, uses more memory\n";
echo "  Iteration: Faster, more memory efficient, sometimes less readable\n";

echo "\n✅ Complete! Basic recursion fundamentals demonstrated.\n";
