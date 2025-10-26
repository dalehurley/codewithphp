<?php

declare(strict_types=1);

/**
 * Array Sorting Functions
 */

echo "=== Array Sorting ===" . PHP_EOL . PHP_EOL;

// 1. sort() - Sort indexed array ascending
echo "1. sort() - Sort Indexed Array:" . PHP_EOL;

$numbers = [5, 2, 8, 1, 9, 3];
sort($numbers);
echo "Sorted: " . implode(", ", $numbers) . PHP_EOL;

$fruits = ["banana", "apple", "cherry"];
sort($fruits);
echo "Sorted fruits: " . implode(", ", $fruits) . PHP_EOL;
echo PHP_EOL;

// 2. rsort() - Sort descending
echo "2. rsort() - Reverse Sort:" . PHP_EOL;

$numbers = [5, 2, 8, 1, 9, 3];
rsort($numbers);
echo "Reverse sorted: " . implode(", ", $numbers) . PHP_EOL;
echo PHP_EOL;

// 3. asort() - Sort associative array by value
echo "3. asort() - Sort Associative by Value:" . PHP_EOL;

$ages = ["Alice" => 25, "Bob" => 30, "Charlie" => 22];
asort($ages);
echo "Sorted by age:" . PHP_EOL;
foreach ($ages as $name => $age) {
    echo "  $name: $age" . PHP_EOL;
}
echo PHP_EOL;

// 4. arsort() - Reverse sort by value
echo "4. arsort() - Reverse Sort by Value:" . PHP_EOL;

$scores = ["Alice" => 85, "Bob" => 92, "Charlie" => 78];
arsort($scores);
echo "Sorted by score (desc):" . PHP_EOL;
foreach ($scores as $name => $score) {
    echo "  $name: $score" . PHP_EOL;
}
echo PHP_EOL;

// 5. ksort() - Sort by key
echo "5. ksort() - Sort by Key:" . PHP_EOL;

$data = ["z" => 1, "a" => 2, "m" => 3];
ksort($data);
print_r($data);
echo PHP_EOL;

// 6. krsort() - Reverse sort by key
echo "6. krsort() - Reverse Sort by Key:" . PHP_EOL;

$data = ["z" => 1, "a" => 2, "m" => 3];
krsort($data);
print_r($data);
echo PHP_EOL;

// 7. usort() - Custom sort with callback
echo "7. usort() - Custom Sort:" . PHP_EOL;

$people = [
    ["name" => "Alice", "age" => 25],
    ["name" => "Bob", "age" => 30],
    ["name" => "Charlie", "age" => 22]
];

// Sort by age
usort($people, fn($a, $b) => $a["age"] <=> $b["age"]);

echo "Sorted by age:" . PHP_EOL;
foreach ($people as $person) {
    echo "  {$person['name']}: {$person['age']}" . PHP_EOL;
}
echo PHP_EOL;

// Sort by name
usort($people, fn($a, $b) => $a["name"] <=> $b["name"]);

echo "Sorted by name:" . PHP_EOL;
foreach ($people as $person) {
    echo "  {$person['name']}: {$person['age']}" . PHP_EOL;
}
echo PHP_EOL;

// 8. uasort() - Custom sort preserving keys
echo "8. uasort() - Custom Sort with Keys:" . PHP_EOL;

$products = [
    "laptop" => ["price" => 999, "rating" => 4.5],
    "mouse" => ["price" => 25, "rating" => 4.8],
    "keyboard" => ["price" => 79, "rating" => 4.2]
];

uasort($products, fn($a, $b) => $b["rating"] <=> $a["rating"]);

echo "Sorted by rating (desc):" . PHP_EOL;
foreach ($products as $name => $product) {
    echo "  $name: {$product['rating']} stars" . PHP_EOL;
}
echo PHP_EOL;

// 9. uksort() - Custom sort by keys
echo "9. uksort() - Custom Sort Keys:" . PHP_EOL;

$items = ["item3" => "c", "item1" => "a", "item2" => "b"];
uksort($items, fn($a, $b) => $a <=> $b);

print_r($items);
echo PHP_EOL;

// 10. natsort() - Natural order sorting
echo "10. natsort() - Natural Order:" . PHP_EOL;

$files = ["file10.txt", "file2.txt", "file1.txt", "file20.txt"];

// Regular sort
$regularSort = $files;
sort($regularSort);
echo "Regular sort: " . implode(", ", $regularSort) . PHP_EOL;

// Natural sort (human-friendly)
$naturalSort = $files;
natsort($naturalSort);
echo "Natural sort: " . implode(", ", $naturalSort) . PHP_EOL;
echo PHP_EOL;

// 11. array_multisort() - Sort multiple arrays
echo "11. array_multisort() - Multiple Arrays:" . PHP_EOL;

$names = ["Alice", "Bob", "Charlie"];
$ages = [25, 30, 22];
$cities = ["NYC", "LA", "Chicago"];

// Sort all arrays by ages
array_multisort($ages, SORT_ASC, $names, $cities);

for ($i = 0; $i < count($names); $i++) {
    echo "  {$names[$i]}, {$ages[$i]}, {$cities[$i]}" . PHP_EOL;
}
echo PHP_EOL;

// 12. Spaceship operator in sorting
echo "12. Spaceship Operator (<=>):" . PHP_EOL;

echo "Comparing values:" . PHP_EOL;
echo "1 <=> 2 = " . (1 <=> 2) . " (less than)" . PHP_EOL;
echo "2 <=> 2 = " . (2 <=> 2) . " (equal)" . PHP_EOL;
echo "3 <=> 2 = " . (3 <=> 2) . " (greater than)" . PHP_EOL;
echo PHP_EOL;

// 13. Sorting multidimensional arrays
echo "13. Sorting Multidimensional Arrays:" . PHP_EOL;

$users = [
    ["name" => "Alice", "age" => 25, "score" => 85],
    ["name" => "Bob", "age" => 30, "score" => 92],
    ["name" => "Charlie", "age" => 22, "score" => 78],
    ["name" => "Diana", "age" => 28, "score" => 92]
];

// Sort by score (desc), then by name (asc)
usort($users, function ($a, $b) {
    $scoreComparison = $b["score"] <=> $a["score"];
    if ($scoreComparison !== 0) {
        return $scoreComparison;
    }
    return $a["name"] <=> $b["name"];
});

echo "Sorted by score (desc), then name (asc):" . PHP_EOL;
foreach ($users as $user) {
    echo "  {$user['name']}: {$user['score']} (age {$user['age']})" . PHP_EOL;
}
