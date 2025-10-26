<?php

declare(strict_types=1);

/**
 * Multidimensional Arrays
 */

echo "=== Multidimensional Arrays ===" . PHP_EOL . PHP_EOL;

// 1. Two-dimensional arrays
echo "1. Two-Dimensional Arrays:" . PHP_EOL;

$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
];

echo "Matrix:" . PHP_EOL;
foreach ($matrix as $row) {
    echo "  " . implode(" ", $row) . PHP_EOL;
}

echo "Element at [1][2]: " . $matrix[1][2] . PHP_EOL; // 6
echo PHP_EOL;

// 2. Associative multidimensional arrays
echo "2. Associative Multidimensional:" . PHP_EOL;

$users = [
    [
        "id" => 1,
        "name" => "Alice",
        "email" => "alice@example.com",
        "address" => [
            "street" => "123 Main St",
            "city" => "NYC",
            "zip" => "10001"
        ]
    ],
    [
        "id" => 2,
        "name" => "Bob",
        "email" => "bob@example.com",
        "address" => [
            "street" => "456 Oak Ave",
            "city" => "LA",
            "zip" => "90001"
        ]
    ]
];

foreach ($users as $user) {
    echo "{$user['name']} ({$user['email']})" . PHP_EOL;
    echo "  Lives in: {$user['address']['city']}" . PHP_EOL;
}
echo PHP_EOL;

// 3. Adding to multidimensional arrays
echo "3. Adding Elements:" . PHP_EOL;

$products = [];

$products[] = [
    "id" => 1,
    "name" => "Laptop",
    "price" => 999,
    "specs" => ["RAM" => "16GB", "Storage" => "512GB SSD"]
];

$products[] = [
    "id" => 2,
    "name" => "Mouse",
    "price" => 25,
    "specs" => ["Type" => "Wireless", "DPI" => "1600"]
];

foreach ($products as $product) {
    echo "{$product['name']}: \${$product['price']}" . PHP_EOL;
    echo "  Specs: " . implode(", ", array_map(
        fn($k, $v) => "$k: $v",
        array_keys($product['specs']),
        $product['specs']
    )) . PHP_EOL;
}
echo PHP_EOL;

// 4. Searching multidimensional arrays
echo "4. Searching Multidimensional Arrays:" . PHP_EOL;

function findByKey(array $array, string $key, mixed $value): ?array
{
    foreach ($array as $item) {
        if (isset($item[$key]) && $item[$key] === $value) {
            return $item;
        }
    }
    return null;
}

$foundUser = findByKey($users, "id", 2);
if ($foundUser) {
    echo "Found user by ID 2: {$foundUser['name']}" . PHP_EOL;
}

// PHP 8.4 way
$foundUserNew = array_find($users, fn($u) => $u["id"] === 2);
if ($foundUserNew) {
    echo "Found with array_find: {$foundUserNew['name']}" . PHP_EOL;
}
echo PHP_EOL;

// 5. Filtering multidimensional arrays
echo "5. Filtering Multidimensional Arrays:" . PHP_EOL;

$tasks = [
    ["id" => 1, "title" => "Task 1", "completed" => true],
    ["id" => 2, "title" => "Task 2", "completed" => false],
    ["id" => 3, "title" => "Task 3", "completed" => true],
    ["id" => 4, "title" => "Task 4", "completed" => false]
];

$completed = array_filter($tasks, fn($task) => $task["completed"]);
$pending = array_filter($tasks, fn($task) => !$task["completed"]);

echo "Completed tasks:" . PHP_EOL;
foreach ($completed as $task) {
    echo "  - {$task['title']}" . PHP_EOL;
}

echo "Pending tasks:" . PHP_EOL;
foreach ($pending as $task) {
    echo "  - {$task['title']}" . PHP_EOL;
}
echo PHP_EOL;

// 6. Transforming multidimensional arrays
echo "6. Transforming Multidimensional Arrays:" . PHP_EOL;

$numbers = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
];

// Double all values
$doubled = array_map(
    fn($row) => array_map(fn($n) => $n * 2, $row),
    $numbers
);

echo "Doubled matrix:" . PHP_EOL;
foreach ($doubled as $row) {
    echo "  " . implode(" ", $row) . PHP_EOL;
}
echo PHP_EOL;

// 7. Flattening arrays
echo "7. Flattening Arrays:" . PHP_EOL;

$nested = [[1, 2], [3, 4], [5, 6]];

// Using array_merge with spread
$flat = array_merge(...$nested);
echo "Flattened: " . implode(", ", $flat) . PHP_EOL;

// Deep flatten function
function arrayFlatten(array $array): array
{
    $result = [];
    array_walk_recursive($array, function ($value) use (&$result) {
        $result[] = $value;
    });
    return $result;
}

$deepNested = [[1, [2, 3]], [4, [5, [6, 7]]]];
$deepFlat = arrayFlatten($deepNested);
echo "Deep flattened: " . implode(", ", $deepFlat) . PHP_EOL;
echo PHP_EOL;

// 8. Grouping data
echo "8. Grouping Data:" . PHP_EOL;

$orders = [
    ["id" => 1, "customer" => "Alice", "total" => 50],
    ["id" => 2, "customer" => "Bob", "total" => 75],
    ["id" => 3, "customer" => "Alice", "total" => 30],
    ["id" => 4, "customer" => "Charlie", "total" => 100],
    ["id" => 5, "customer" => "Bob", "total" => 45]
];

function groupBy(array $array, string $key): array
{
    $grouped = [];
    foreach ($array as $item) {
        $grouped[$item[$key]][] = $item;
    }
    return $grouped;
}

$byCustomer = groupBy($orders, "customer");

foreach ($byCustomer as $customer => $customerOrders) {
    $total = array_sum(array_column($customerOrders, "total"));
    echo "$customer: " . count($customerOrders) . " orders, \$$total total" . PHP_EOL;
}
echo PHP_EOL;

// 9. Practical example: Shopping cart
echo "9. Practical Example: Shopping Cart:" . PHP_EOL;

$cart = [
    [
        "product_id" => 101,
        "name" => "Laptop",
        "price" => 999,
        "quantity" => 1
    ],
    [
        "product_id" => 102,
        "name" => "Mouse",
        "price" => 25,
        "quantity" => 2
    ],
    [
        "product_id" => 103,
        "name" => "Keyboard",
        "price" => 79,
        "quantity" => 1
    ]
];

// Calculate totals
$subtotals = array_map(fn($item) => $item["price"] * $item["quantity"], $cart);
$total = array_sum($subtotals);
$itemCount = array_sum(array_column($cart, "quantity"));

echo "Cart contents:" . PHP_EOL;
foreach ($cart as $index => $item) {
    $itemTotal = $item["price"] * $item["quantity"];
    echo "  {$item['name']} x{$item['quantity']}: \${$itemTotal}" . PHP_EOL;
}
echo "Items: $itemCount" . PHP_EOL;
echo "Total: \$$total" . PHP_EOL;
