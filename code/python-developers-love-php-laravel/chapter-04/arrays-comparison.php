<?php

declare(strict_types=1);

/**
 * Array Syntax - PHP vs Python
 * 
 * This file demonstrates PHP array syntax differences from Python.
 * Key differences:
 * - PHP arrays can be indexed or associative (unified type)
 * - PHP uses => for key-value pairs (Python uses :)
 * - PHP uses functions (array_map, array_filter) vs Python methods
 * - PHP array destructuring: [$a, $b] = $array
 */

// ============================================================================
// 1. Indexed Arrays (like Python lists)
// ============================================================================

// Python: fruits = ["apple", "banana", "cherry"]
// PHP:    $fruits = ["apple", "banana", "cherry"];

$fruits = ["apple", "banana", "cherry"];
echo "First fruit: {$fruits[0]}\n";  // Output: apple

// Or using array() syntax (older, still valid)
$fruits2 = array("apple", "banana", "cherry");

// ============================================================================
// 2. Associative Arrays (like Python dictionaries)
// ============================================================================

// Python: person = {"name": "Alice", "age": 30}
// PHP:    $person = ["name" => "Alice", "age" => 30];

$person = ["name" => "Alice", "age" => 30];
echo "Name: {$person['name']}\n";  // Output: Alice
echo "Age: {$person['age']}\n";     // Output: 30

// ============================================================================
// 3. Array Operations
// ============================================================================

// Python: fruits.append("cherry")
// PHP:    $fruits[] = "cherry";  or  array_push($fruits, "cherry");

$fruits = ["apple", "banana"];
$fruits[] = "cherry";  // Append
print_r($fruits);

// Count
// Python: len(fruits)
// PHP:    count($fruits)
echo "Count: " . count($fruits) . "\n";

// Check if value exists
// Python: "apple" in fruits
// PHP:    in_array("apple", $fruits)
echo "Has apple: " . (in_array("apple", $fruits) ? "yes" : "no") . "\n";

// Check if key exists (for associative arrays)
// Python: "name" in person
// PHP:    isset($person["name"])  or  array_key_exists("name", $person)
echo "Has name key: " . (isset($person["name"]) ? "yes" : "no") . "\n";

// ============================================================================
// 4. Array Destructuring (PHP 7.1+)
// ============================================================================

// Python: name, age, email = data
// PHP:    [$name, $age, $email] = $data;

$data = ["Alice", 30, "alice@example.com"];
[$name, $age, $email] = $data;
echo "Name: $name, Age: $age, Email: $email\n";

// Associative array destructuring (PHP 7.1+)
// Python: name = person["name"], age = person["age"]
// PHP:    ["name" => $name, "age" => $age] = $person;

$person = ["name" => "Alice", "age" => 30];
["name" => $name, "age" => $age] = $person;
echo "Name: $name, Age: $age\n";

// ============================================================================
// 5. Array Functions vs Python Methods
// ============================================================================

// Python: doubled = [x * 2 for x in numbers]
// PHP:    $doubled = array_map(fn($x) => $x * 2, $numbers);

$numbers = [1, 2, 3, 4, 5];

// Map (transform)
$doubled = array_map(fn($x) => $x * 2, $numbers);
print_r($doubled);  // [2, 4, 6, 8, 10]

// Filter
// Python: filtered = [x for x in numbers if x > 2]
// PHP:    $filtered = array_filter($numbers, fn($x) => $x > 2);
$filtered = array_filter($numbers, fn($x) => $x > 2);
print_r($filtered);

// Reduce
// Python: from functools import reduce; reduce(lambda x, y: x + y, numbers)
// PHP:    array_reduce($numbers, fn($carry, $item) => $carry + $item, 0);
$sum = array_reduce($numbers, fn($carry, $item) => $carry + $item, 0);
echo "Sum: $sum\n";

// ============================================================================
// 6. Array Merging and Slicing
// ============================================================================

// Merge arrays
// Python: combined = list1 + list2
// PHP:    $combined = array_merge($list1, $list2);

$list1 = [1, 2, 3];
$list2 = [4, 5, 6];
$combined = array_merge($list1, $list2);
print_r($combined);

// Slice
// Python: sliced = numbers[1:3]
// PHP:    $sliced = array_slice($numbers, 1, 2);
$sliced = array_slice($numbers, 1, 2);
print_r($sliced);  // [2, 3]

// ============================================================================
// 7. Array Sorting
// ============================================================================

// Python: numbers.sort()  or  sorted(numbers)
// PHP:    sort($numbers)  (modifies original)  or  (PHP 8+) array is sorted by reference

$unsorted = [3, 1, 4, 1, 5, 9, 2, 6];
$sorted = $unsorted;
sort($sorted);
print_r($sorted);

// Associative array sorting
$people = [
    ["name" => "Alice", "age" => 30],
    ["name" => "Bob", "age" => 25],
    ["name" => "Charlie", "age" => 35],
];

// Sort by age
usort($people, fn($a, $b) => $a["age"] <=> $b["age"]);
print_r($people);

// ============================================================================
// 8. Array Spread Operator (PHP 7.4+)
// ============================================================================

// Python: combined = [*list1, *list2]
// PHP:    $combined = [...$list1, ...$list2];

$list1 = [1, 2, 3];
$list2 = [4, 5, 6];
$combined = [...$list1, ...$list2];
print_r($combined);

