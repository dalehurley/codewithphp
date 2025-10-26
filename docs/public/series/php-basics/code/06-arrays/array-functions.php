<?php

declare(strict_types=1);

/**
 * Common Array Functions
 */

echo "=== Array Functions ===" . PHP_EOL . PHP_EOL;

// 1. array_map - Transform each element
echo "1. array_map() - Transform Elements:" . PHP_EOL;

$numbers = [1, 2, 3, 4, 5];
$squared = array_map(fn($n) => $n * $n, $numbers);
$doubled = array_map(fn($n) => $n * 2, $numbers);

echo "Original: " . implode(", ", $numbers) . PHP_EOL;
echo "Squared: " . implode(", ", $squared) . PHP_EOL;
echo "Doubled: " . implode(", ", $doubled) . PHP_EOL;
echo PHP_EOL;

// 2. array_filter - Filter elements
echo "2. array_filter() - Filter Elements:" . PHP_EOL;

$values = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$even = array_filter($values, fn($n) => $n % 2 === 0);
$greaterThanFive = array_filter($values, fn($n) => $n > 5);

echo "Even numbers: " . implode(", ", $even) . PHP_EOL;
echo "Greater than 5: " . implode(", ", $greaterThanFive) . PHP_EOL;
echo PHP_EOL;

// 3. array_reduce - Reduce to single value
echo "3. array_reduce() - Reduce to Single Value:" . PHP_EOL;

$sum = array_reduce($numbers, fn($carry, $n) => $carry + $n, 0);
$product = array_reduce($numbers, fn($carry, $n) => $carry * $n, 1);

echo "Sum: $sum" . PHP_EOL;
echo "Product: $product" . PHP_EOL;
echo PHP_EOL;

// 4. array_merge - Combine arrays
echo "4. array_merge() - Combine Arrays:" . PHP_EOL;

$array1 = ["a", "b", "c"];
$array2 = ["d", "e", "f"];
$merged = array_merge($array1, $array2);

echo "Merged: " . implode(", ", $merged) . PHP_EOL;

// Merging associative arrays
$defaults = ["color" => "blue", "size" => "medium"];
$custom = ["color" => "red", "weight" => "light"];
$config = array_merge($defaults, $custom);

print_r($config);
echo PHP_EOL;

// 5. array_slice - Extract portion
echo "5. array_slice() - Extract Portion:" . PHP_EOL;

$letters = ["a", "b", "c", "d", "e", "f", "g"];
$slice1 = array_slice($letters, 2, 3); // Start at index 2, take 3
$slice2 = array_slice($letters, -3); // Last 3 elements

echo "Original: " . implode(", ", $letters) . PHP_EOL;
echo "Slice (2, 3): " . implode(", ", $slice1) . PHP_EOL;
echo "Last 3: " . implode(", ", $slice2) . PHP_EOL;
echo PHP_EOL;

// 6. array_splice - Remove/replace portion
echo "6. array_splice() - Remove/Replace:" . PHP_EOL;

$items = ["a", "b", "c", "d", "e"];
$removed = array_splice($items, 1, 2, ["x", "y"]); // Remove 2 from index 1, insert x,y

echo "After splice: " . implode(", ", $items) . PHP_EOL;
echo "Removed: " . implode(", ", $removed) . PHP_EOL;
echo PHP_EOL;

// 7. array_unique - Remove duplicates
echo "7. array_unique() - Remove Duplicates:" . PHP_EOL;

$duplicates = [1, 2, 2, 3, 3, 3, 4, 5, 5];
$unique = array_unique($duplicates);

echo "With duplicates: " . implode(", ", $duplicates) . PHP_EOL;
echo "Unique: " . implode(", ", $unique) . PHP_EOL;
echo PHP_EOL;

// 8. array_reverse - Reverse order
echo "8. array_reverse() - Reverse Order:" . PHP_EOL;

$original = [1, 2, 3, 4, 5];
$reversed = array_reverse($original);

echo "Original: " . implode(", ", $original) . PHP_EOL;
echo "Reversed: " . implode(", ", $reversed) . PHP_EOL;
echo PHP_EOL;

// 9. array_keys and array_values
echo "9. array_keys() and array_values():" . PHP_EOL;

$person = ["name" => "Alice", "age" => 25, "city" => "NYC"];
$keys = array_keys($person);
$values = array_values($person);

echo "Keys: " . implode(", ", $keys) . PHP_EOL;
echo "Values: " . implode(", ", $values) . PHP_EOL;
echo PHP_EOL;

// 10. array_flip - Swap keys and values
echo "10. array_flip() - Swap Keys/Values:" . PHP_EOL;

$original = ["a" => 1, "b" => 2, "c" => 3];
$flipped = array_flip($original);

print_r($original);
print_r($flipped);
echo PHP_EOL;

// 11. array_column - Extract column from multidimensional
echo "11. array_column() - Extract Column:" . PHP_EOL;

$users = [
    ["id" => 1, "name" => "Alice", "age" => 25],
    ["id" => 2, "name" => "Bob", "age" => 30],
    ["id" => 3, "name" => "Charlie", "age" => 35]
];

$names = array_column($users, "name");
$ages = array_column($users, "age", "name"); // Key by name

echo "Names: " . implode(", ", $names) . PHP_EOL;
print_r($ages);
echo PHP_EOL;

// 12. array_chunk - Split into chunks
echo "12. array_chunk() - Split Into Chunks:" . PHP_EOL;

$data = range(1, 10);
$chunks = array_chunk($data, 3);

foreach ($chunks as $index => $chunk) {
    echo "Chunk $index: " . implode(", ", $chunk) . PHP_EOL;
}
echo PHP_EOL;

// 13. array_combine - Create array using keys and values
echo "13. array_combine() - Combine Keys/Values:" . PHP_EOL;

$keys = ["name", "age", "city"];
$vals = ["Alice", 25, "NYC"];
$combined = array_combine($keys, $vals);

print_r($combined);
echo PHP_EOL;

// 14. implode and explode
echo "14. implode() and explode():" . PHP_EOL;

$words = ["Hello", "World", "PHP"];
$sentence = implode(" ", $words);
echo "Joined: $sentence" . PHP_EOL;

$parts = explode(" ", $sentence);
echo "Split: " . implode(", ", $parts) . PHP_EOL;
