<?php

declare(strict_types=1);

/**
 * Chapter 11: Linear Search & Variants
 * Part 3: Search with Conditions and Callbacks
 * 
 * Demonstrates conditional searching:
 * - Search with callback predicates
 * - findIndex() - find first matching index
 * - findAll() - find all matching elements
 * - filter() - create filtered array
 * - some() and every() - boolean checks
 * - partition() - split by condition
 */

echo "=== Search with Conditions and Callbacks ===\n\n";

// ============================================================================
// 1. Basic Predicate Search
// ============================================================================

echo "1. Basic Predicate Search\n";
echo str_repeat("-", 50) . "\n";

function find(array $arr, callable $predicate): mixed
{
    foreach ($arr as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }
    return null;
}

$numbers = [1, 3, 5, 8, 10, 12, 15];
echo "Array: " . json_encode($numbers) . "\n";

$firstEven = find($numbers, fn($x) => $x % 2 === 0);
echo "First even number: " . ($firstEven ?? 'None') . "\n";

$firstGreaterThan10 = find($numbers, fn($x) => $x > 10);
echo "First number > 10: " . ($firstGreaterThan10 ?? 'None') . "\n\n";

// ============================================================================
// 2. findIndex() - Return Index of First Match
// ============================================================================

echo "2. findIndex() - Find Index\n";
echo str_repeat("-", 50) . "\n";

function findIndex(array $arr, callable $predicate): int|false
{
    foreach ($arr as $index => $value) {
        if ($predicate($value)) {
            return $index;
        }
    }
    return false;
}

$fruits = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
echo "Array: " . json_encode($fruits) . "\n";

$index = findIndex($fruits, fn($fruit) => strlen($fruit) > 6);
echo "First fruit with length > 6: index " . ($index !== false ? $index : 'Not found') . "\n";

$index = findIndex($fruits, fn($fruit) => str_starts_with($fruit, 'c'));
echo "First fruit starting with 'c': index " . ($index !== false ? $index : 'Not found') . "\n\n";

// ============================================================================
// 3. findAll() - Find All Matching Elements
// ============================================================================

echo "3. findAll() - Find All Matches\n";
echo str_repeat("-", 50) . "\n";

function findAll(array $arr, callable $predicate): array
{
    $results = [];
    
    foreach ($arr as $value) {
        if ($predicate($value)) {
            $results[] = $value;
        }
    }
    
    return $results;
}

$numbers2 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
echo "Array: " . json_encode($numbers2) . "\n";

$evens = findAll($numbers2, fn($x) => $x % 2 === 0);
echo "All even numbers: " . json_encode($evens) . "\n";

$divisibleBy3 = findAll($numbers2, fn($x) => $x % 3 === 0);
echo "All divisible by 3: " . json_encode($divisibleBy3) . "\n\n";

// ============================================================================
// 4. findAllIndices() - Find All Matching Indices
// ============================================================================

echo "4. findAllIndices() - Find All Indices\n";
echo str_repeat("-", 50) . "\n";

function findAllIndices(array $arr, callable $predicate): array
{
    $indices = [];
    
    foreach ($arr as $index => $value) {
        if ($predicate($value)) {
            $indices[] = $index;
        }
    }
    
    return $indices;
}

$data = [10, 25, 30, 15, 40, 5, 50];
echo "Array: " . json_encode($data) . "\n";

$indices = findAllIndices($data, fn($x) => $x >= 30);
echo "Indices where value >= 30: " . json_encode($indices) . "\n";

$indices = findAllIndices($data, fn($x) => $x < 20);
echo "Indices where value < 20: " . json_encode($indices) . "\n\n";

// ============================================================================
// 5. some() - Check if Any Element Matches
// ============================================================================

echo "5. some() - Boolean Check (Any)\n";
echo str_repeat("-", 50) . "\n";

function some(array $arr, callable $predicate): bool
{
    foreach ($arr as $value) {
        if ($predicate($value)) {
            return true;
        }
    }
    return false;
}

$scores = [45, 67, 89, 72, 91];
echo "Scores: " . json_encode($scores) . "\n";

$hasExcellent = some($scores, fn($score) => $score >= 90);
echo "Has any score >= 90? " . ($hasExcellent ? 'Yes' : 'No') . "\n";

$hasFailing = some($scores, fn($score) => $score < 60);
echo "Has any score < 60? " . ($hasFailing ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// 6. every() - Check if All Elements Match
// ============================================================================

echo "6. every() - Boolean Check (All)\n";
echo str_repeat("-", 50) . "\n";

function every(array $arr, callable $predicate): bool
{
    foreach ($arr as $value) {
        if (!$predicate($value)) {
            return false;
        }
    }
    return true;
}

$ages = [18, 21, 25, 30, 19];
echo "Ages: " . json_encode($ages) . "\n";

$allAdults = every($ages, fn($age) => $age >= 18);
echo "All adults (>= 18)? " . ($allAdults ? 'Yes' : 'No') . "\n";

$allSeniors = every($ages, fn($age) => $age >= 65);
echo "All seniors (>= 65)? " . ($allSeniors ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// 7. partition() - Split Array by Condition
// ============================================================================

echo "7. partition() - Split by Condition\n";
echo str_repeat("-", 50) . "\n";

function partition(array $arr, callable $predicate): array
{
    $pass = [];
    $fail = [];
    
    foreach ($arr as $value) {
        if ($predicate($value)) {
            $pass[] = $value;
        } else {
            $fail[] = $value;
        }
    }
    
    return [$pass, $fail];
}

$numbers3 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
echo "Array: " . json_encode($numbers3) . "\n";

[$evens, $odds] = partition($numbers3, fn($x) => $x % 2 === 0);
echo "Evens: " . json_encode($evens) . "\n";
echo "Odds: " . json_encode($odds) . "\n\n";

// ============================================================================
// 8. Complex Predicates
// ============================================================================

echo "8. Complex Predicates\n";
echo str_repeat("-", 50) . "\n";

$products = [
    ['name' => 'Laptop', 'price' => 1200, 'inStock' => true],
    ['name' => 'Mouse', 'price' => 25, 'inStock' => true],
    ['name' => 'Keyboard', 'price' => 80, 'inStock' => false],
    ['name' => 'Monitor', 'price' => 400, 'inStock' => true],
];

echo "Products:\n";
foreach ($products as $p) {
    echo "  {$p['name']}: \${$p['price']} (in stock: " . ($p['inStock'] ? 'yes' : 'no') . ")\n";
}
echo "\n";

// Find expensive, available products
$expensiveAvailable = findAll($products, function($p) {
    return $p['price'] > 100 && $p['inStock'];
});

echo "Expensive (>$100) and in stock:\n";
foreach ($expensiveAvailable as $p) {
    echo "  {$p['name']}: \${$p['price']}\n";
}
echo "\n";

// ============================================================================
// 9. Chained Conditions
// ============================================================================

echo "9. Chained Conditions\n";
echo str_repeat("-", 50) . "\n";

function andPredicate(callable ...$predicates): callable
{
    return function($value) use ($predicates) {
        foreach ($predicates as $predicate) {
            if (!$predicate($value)) {
                return false;
            }
        }
        return true;
    };
}

function orPredicate(callable ...$predicates): callable
{
    return function($value) use ($predicates) {
        foreach ($predicates as $predicate) {
            if ($predicate($value)) {
                return true;
            }
        }
        return false;
    };
}

$numbers4 = range(1, 20);
echo "Array: 1-20\n";

// Find numbers divisible by both 2 AND 3
$divisibleBy2And3 = andPredicate(
    fn($x) => $x % 2 === 0,
    fn($x) => $x % 3 === 0
);

$results = findAll($numbers4, $divisibleBy2And3);
echo "Divisible by 2 AND 3: " . json_encode($results) . "\n";

// Find numbers divisible by 5 OR 7
$divisibleBy5Or7 = orPredicate(
    fn($x) => $x % 5 === 0,
    fn($x) => $x % 7 === 0
);

$results = findAll($numbers4, $divisibleBy5Or7);
echo "Divisible by 5 OR 7: " . json_encode($results) . "\n\n";

// ============================================================================
// 10. Count Matching Elements
// ============================================================================

echo "10. Count Matching Elements\n";
echo str_repeat("-", 50) . "\n";

function count_matching(array $arr, callable $predicate): int
{
    $count = 0;
    
    foreach ($arr as $value) {
        if ($predicate($value)) {
            $count++;
        }
    }
    
    return $count;
}

$grades = [85, 92, 78, 65, 90, 88, 95, 70, 82];
echo "Grades: " . json_encode($grades) . "\n";

$excellentCount = count_matching($grades, fn($g) => $g >= 90);
echo "Excellent (>= 90): {$excellentCount}\n";

$passingCount = count_matching($grades, fn($g) => $g >= 70);
echo "Passing (>= 70): {$passingCount}\n";

$failingCount = count_matching($grades, fn($g) => $g < 70);
echo "Failing (< 70): {$failingCount}\n\n";

// ============================================================================
// 11. Search with Context
// ============================================================================

echo "11. Search with Context (Index Access)\n";
echo str_repeat("-", 50) . "\n";

function findWithIndex(array $arr, callable $predicate): mixed
{
    foreach ($arr as $index => $value) {
        if ($predicate($value, $index, $arr)) {
            return $value;
        }
    }
    return null;
}

$sequence = [1, 2, 4, 8, 16, 32, 64];
echo "Array: " . json_encode($sequence) . "\n";

// Find first element where value > index * 5
$result = findWithIndex($sequence, fn($val, $idx) => $val > $idx * 5);
echo "First where value > index * 5: " . ($result ?? 'None') . "\n\n";

// ============================================================================
// 12. Real-World Example: Filter Users
// ============================================================================

echo "12. Real-World Example: Filter Users\n";
echo str_repeat("-", 50) . "\n";

$users = [
    ['name' => 'Alice', 'age' => 25, 'role' => 'admin', 'active' => true],
    ['name' => 'Bob', 'age' => 30, 'role' => 'user', 'active' => true],
    ['name' => 'Charlie', 'age' => 22, 'role' => 'user', 'active' => false],
    ['name' => 'Diana', 'age' => 28, 'role' => 'admin', 'active' => true],
];

echo "All users:\n";
foreach ($users as $u) {
    echo "  {$u['name']} ({$u['age']}) - {$u['role']} - " . ($u['active'] ? 'active' : 'inactive') . "\n";
}
echo "\n";

// Find active admins
$activeAdmins = findAll($users, fn($u) => $u['role'] === 'admin' && $u['active']);
echo "Active admins:\n";
foreach ($activeAdmins as $u) {
    echo "  {$u['name']}\n";
}
echo "\n";

// Check if any inactive users
$hasInactive = some($users, fn($u) => !$u['active']);
echo "Has inactive users? " . ($hasInactive ? 'Yes' : 'No') . "\n";

// Check if all are adults
$allAdults = every($users, fn($u) => $u['age'] >= 18);
echo "All adults? " . ($allAdults ? 'Yes' : 'No') . "\n";

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- find(): First matching element\n";
echo "- findIndex(): Index of first match\n";
echo "- findAll(): All matching elements\n";
echo "- some(): True if any match\n";
echo "- every(): True if all match\n";
echo "- partition(): Split by condition\n";
echo "- Predicates: Flexible condition functions\n";
echo "- Use callbacks for complex search logic\n";
echo str_repeat("=", 50) . "\n";




