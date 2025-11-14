<?php

/**
 * Search with Conditions and Callbacks
 *
 * Demonstrates flexible search algorithms using callbacks and predicates.
 * Useful for complex search criteria and filtering operations.
 *
 * @package CodeWithPHP\Algorithms\Chapter11
 */

declare(strict_types=1);

/**
 * Search with custom condition (callback)
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function that returns true for match
 * @return mixed The first matching value or null
 */
function searchWithCondition(array $arr, callable $condition): mixed
{
    foreach ($arr as $value) {
        if ($condition($value)) {
            return $value;
        }
    }

    return null;
}

/**
 * Find index of first element matching condition
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @return int|false The index if found, false otherwise
 */
function findIndex(array $arr, callable $condition): int|false
{
    foreach ($arr as $index => $value) {
        if ($condition($value)) {
            return $index;
        }
    }

    return false;
}

/**
 * Find all elements matching condition
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @return array Array of matching elements
 */
function findAll(array $arr, callable $condition): array
{
    $results = [];

    foreach ($arr as $value) {
        if ($condition($value)) {
            $results[] = $value;
        }
    }

    return $results;
}

/**
 * Find all indices matching condition
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @return array Array of matching indices
 */
function findAllIndices(array $arr, callable $condition): array
{
    $indices = [];

    foreach ($arr as $index => $value) {
        if ($condition($value)) {
            $indices[] = $index;
        }
    }

    return $indices;
}

/**
 * Count elements matching condition
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @return int Count of matching elements
 */
function countMatching(array $arr, callable $condition): int
{
    $count = 0;

    foreach ($arr as $value) {
        if ($condition($value)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Check if any element matches condition
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @return bool True if at least one element matches
 */
function any(array $arr, callable $condition): bool
{
    foreach ($arr as $value) {
        if ($condition($value)) {
            return true;
        }
    }

    return false;
}

/**
 * Check if all elements match condition
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @return bool True if all elements match
 */
function all(array $arr, callable $condition): bool
{
    foreach ($arr as $value) {
        if (!$condition($value)) {
            return false;
        }
    }

    return true;
}

/**
 * Find first element or return default
 *
 * @param array $arr The array to search
 * @param callable $condition Predicate function
 * @param mixed $default Default value if not found
 * @return mixed The found element or default
 */
function findOrDefault(array $arr, callable $condition, mixed $default = null): mixed
{
    foreach ($arr as $value) {
        if ($condition($value)) {
            return $value;
        }
    }

    return $default;
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "=== Search with Conditions ===\n\n";

// Example 1: Find first even number
echo "Example 1: Find first even number\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 3, 5, 8, 7, 9, 10];
echo "Array: [" . implode(', ', $numbers) . "]\n";
$firstEven = searchWithCondition($numbers, fn($x) => $x % 2 === 0);
echo "First even number: " . ($firstEven ?? 'not found') . "\n";
echo "\n";

// Example 2: Find first number greater than 100
echo "Example 2: Find first number > 100\n";
echo str_repeat('-', 50) . "\n";
$values = [45, 67, 89, 112, 134];
echo "Array: [" . implode(', ', $values) . "]\n";
$firstLarge = searchWithCondition($values, fn($x) => $x > 100);
echo "First number > 100: $firstLarge\n";
echo "\n";

// Example 3: Find index of first negative number
echo "Example 3: Find index of first negative number\n";
echo str_repeat('-', 50) . "\n";
$numbers = [5, 3, 8, -2, 7, -1];
echo "Array: [" . implode(', ', $numbers) . "]\n";
$index = findIndex($numbers, fn($x) => $x < 0);
echo "Index of first negative: " . ($index !== false ? $index : 'not found') . "\n";
echo "\n";

// Example 4: Find all multiples of 3
echo "Example 4: Find all multiples of 3\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
echo "Array: [" . implode(', ', $numbers) . "]\n";
$multiples = findAll($numbers, fn($x) => $x % 3 === 0);
echo "Multiples of 3: [" . implode(', ', $multiples) . "]\n";
echo "\n";

// Example 5: Find all indices where value > 50
echo "Example 5: Find all indices where value > 50\n";
echo str_repeat('-', 50) . "\n";
$scores = [45, 67, 55, 32, 78, 90, 43];
echo "Scores: [" . implode(', ', $scores) . "]\n";
$indices = findAllIndices($scores, fn($x) => $x > 50);
echo "Indices of scores > 50: [" . implode(', ', $indices) . "]\n";
echo "\n";

// Example 6: Count elements in range
echo "Example 6: Count elements in range [20, 80]\n";
echo str_repeat('-', 50) . "\n";
$numbers = [15, 25, 35, 85, 45, 95, 55, 10];
echo "Array: [" . implode(', ', $numbers) . "]\n";
$count = countMatching($numbers, fn($x) => $x >= 20 && $x <= 80);
echo "Count in range [20, 80]: $count\n";
echo "\n";

// Example 7: Check if any element is negative
echo "Example 7: Check if any element is negative\n";
echo str_repeat('-', 50) . "\n";
$numbers1 = [5, 3, 8, 2, 7];
$numbers2 = [5, 3, -1, 2, 7];
echo "Array 1: [" . implode(', ', $numbers1) . "]\n";
echo "Has negative? " . (any($numbers1, fn($x) => $x < 0) ? 'Yes' : 'No') . "\n";
echo "Array 2: [" . implode(', ', $numbers2) . "]\n";
echo "Has negative? " . (any($numbers2, fn($x) => $x < 0) ? 'Yes' : 'No') . "\n";
echo "\n";

// Example 8: Check if all elements are positive
echo "Example 8: Check if all elements are positive\n";
echo str_repeat('-', 50) . "\n";
$numbers = [5, 3, 8, 2, 7];
echo "Array: [" . implode(', ', $numbers) . "]\n";
echo "All positive? " . (all($numbers, fn($x) => $x > 0) ? 'Yes' : 'No') . "\n";
echo "\n";

// Example 9: Find with default value
echo "Example 9: Find with default value\n";
echo str_repeat('-', 50) . "\n";
$numbers = [1, 3, 5, 7, 9];
echo "Array: [" . implode(', ', $numbers) . "]\n";
$even = findOrDefault($numbers, fn($x) => $x % 2 === 0, -1);
echo "First even (default -1): $even\n";
echo "\n";

// Example 10: Complex condition (prime numbers)
echo "Example 10: Find all prime numbers\n";
echo str_repeat('-', 50) . "\n";

function isPrime(int $n): bool
{
    if ($n < 2) return false;
    if ($n === 2) return true;
    if ($n % 2 === 0) return false;

    for ($i = 3; $i <= sqrt($n); $i += 2) {
        if ($n % $i === 0) return false;
    }

    return true;
}

$numbers = range(1, 30);
$primes = findAll($numbers, fn($x) => isPrime($x));
echo "Numbers 1-30: Prime numbers found\n";
echo "Primes: [" . implode(', ', $primes) . "]\n";
echo "Count: " . count($primes) . "\n";
echo "\n";

// Example 11: String operations
echo "Example 11: String searches with conditions\n";
echo str_repeat('-', 50) . "\n";
$words = ['apple', 'banana', 'apricot', 'cherry', 'avocado'];
echo "Words: [" . implode(', ', $words) . "]\n";

// Words starting with 'a'
$aWords = findAll($words, fn($w) => str_starts_with($w, 'a'));
echo "Words starting with 'a': [" . implode(', ', $aWords) . "]\n";

// Words longer than 6 characters
$longWords = findAll($words, fn($w) => strlen($w) > 6);
echo "Words longer than 6 chars: [" . implode(', ', $longWords) . "]\n";

// Words containing 'an'
$containsAn = findAll($words, fn($w) => str_contains($w, 'an'));
echo "Words containing 'an': [" . implode(', ', $containsAn) . "]\n";
echo "\n";

// Example 12: Performance comparison
echo "Example 12: Performance - Condition vs PHP built-in\n";
echo str_repeat('-', 50) . "\n";
$testArray = range(1, 10000);
$iterations = 1000;

// Using findAll
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    findAll($testArray, fn($x) => $x % 2 === 0);
}
$customTime = microtime(true) - $start;

// Using array_filter
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    array_filter($testArray, fn($x) => $x % 2 === 0);
}
$filterTime = microtime(true) - $start;

printf("Custom findAll():    %.6f seconds\n", $customTime);
printf("array_filter():      %.6f seconds\n", $filterTime);
printf("Performance ratio:   %.2fx\n", $customTime / $filterTime);
