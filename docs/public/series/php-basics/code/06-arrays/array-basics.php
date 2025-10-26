<?php

declare(strict_types=1);

/**
 * Array Basics - Creating and Accessing Arrays
 */

echo "=== Array Basics ===" . PHP_EOL . PHP_EOL;

// 1. Creating indexed arrays
echo "1. Indexed Arrays:" . PHP_EOL;

$fruits = ["apple", "banana", "cherry"];
$numbers = [1, 2, 3, 4, 5];

echo "Fruits: " . implode(", ", $fruits) . PHP_EOL;
echo "First fruit: " . $fruits[0] . PHP_EOL;
echo "Last fruit: " . $fruits[count($fruits) - 1] . PHP_EOL;
echo PHP_EOL;

// 2. Creating associative arrays
echo "2. Associative Arrays:" . PHP_EOL;

$person = [
    "name" => "John Doe",
    "age" => 30,
    "email" => "john@example.com",
    "city" => "New York"
];

echo "Name: " . $person["name"] . PHP_EOL;
echo "Age: " . $person["age"] . PHP_EOL;
echo "Email: " . $person["email"] . PHP_EOL;
echo PHP_EOL;

// 3. Array with mixed keys
echo "3. Mixed Key Arrays:" . PHP_EOL;

$mixed = [
    0 => "first",
    "key" => "value",
    1 => "second",
    "another" => "data"
];

print_r($mixed);
echo PHP_EOL;

// 4. Adding elements to arrays
echo "4. Adding Elements:" . PHP_EOL;

$colors = ["red", "green"];
$colors[] = "blue"; // Append
$colors[10] = "yellow"; // Specific index
array_push($colors, "purple", "orange"); // Add multiple

echo "Colors: " . implode(", ", $colors) . PHP_EOL;
echo "Count: " . count($colors) . PHP_EOL;
echo PHP_EOL;

// 5. Checking if element exists
echo "5. Checking Elements:" . PHP_EOL;

if (in_array("red", $colors)) {
    echo "✓ 'red' found in colors" . PHP_EOL;
}

if (isset($person["email"])) {
    echo "✓ 'email' key exists" . PHP_EOL;
}

if (array_key_exists("age", $person)) {
    echo "✓ 'age' key exists" . PHP_EOL;
}
echo PHP_EOL;

// 6. Removing elements
echo "6. Removing Elements:" . PHP_EOL;

$items = ["a", "b", "c", "d", "e"];
unset($items[2]); // Remove 'c'
echo "After unset: ";
print_r($items);

$last = array_pop($items); // Remove last
echo "Popped: $last" . PHP_EOL;

$first = array_shift($items); // Remove first
echo "Shifted: $first" . PHP_EOL;
echo "Remaining: " . implode(", ", $items) . PHP_EOL;
echo PHP_EOL;

// 7. Array length/size
echo "7. Array Size:" . PHP_EOL;

echo "Count of fruits: " . count($fruits) . PHP_EOL;
echo "Size of person: " . sizeof($person) . PHP_EOL; // sizeof is alias of count
echo "Empty check: " . (empty([]) ? "Empty" : "Not empty") . PHP_EOL;
echo PHP_EOL;

// 8. Iterating arrays
echo "8. Iterating Arrays:" . PHP_EOL;

echo "Fruits (foreach):" . PHP_EOL;
foreach ($fruits as $fruit) {
    echo "  - $fruit" . PHP_EOL;
}

echo "Person (foreach with keys):" . PHP_EOL;
foreach ($person as $key => $value) {
    echo "  $key: $value" . PHP_EOL;
}
echo PHP_EOL;

// 9. Array destructuring (PHP 7.1+)
echo "9. Array Destructuring:" . PHP_EOL;

[$first, $second, $third] = $fruits;
echo "First: $first, Second: $second, Third: $third" . PHP_EOL;

// With associative arrays (PHP 7.1+)
["name" => $personName, "age" => $personAge] = $person;
echo "Name: $personName, Age: $personAge" . PHP_EOL;
echo PHP_EOL;

// 10. Spread operator (PHP 7.4+)
echo "10. Spread Operator:" . PHP_EOL;

$array1 = [1, 2, 3];
$array2 = [4, 5, 6];
$combined = [...$array1, ...$array2];
echo "Combined: " . implode(", ", $combined) . PHP_EOL;

// Spread in function calls
function sum(int ...$numbers): int
{
    return array_sum($numbers);
}

echo "Sum: " . sum(...$array1) . PHP_EOL;
