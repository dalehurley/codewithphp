<?php

declare(strict_types=1);

/**
 * PHP 8.4 New Array Functions
 * 
 * Demonstrates array_find(), array_find_key(), array_any(), and array_all()
 */

echo "=== PHP 8.4 Array Functions ===" . PHP_EOL . PHP_EOL;

// Sample data
$users = [
    ["id" => 1, "name" => "Alice", "age" => 25, "active" => true],
    ["id" => 2, "name" => "Bob", "age" => 30, "active" => false],
    ["id" => 3, "name" => "Charlie", "age" => 35, "active" => true],
    ["id" => 4, "name" => "Diana", "age" => 28, "active" => true]
];

$numbers = [1, 3, 5, 7, 9, 11, 13];

// 1. array_find() - Find first matching element
echo "1. array_find() - Find First Match:" . PHP_EOL;

// Traditional way (before PHP 8.4)
$filtered = array_filter($users, fn($u) => $u["age"] > 30);
$firstOld = reset($filtered) ?: null;
echo "Traditional way: " . ($firstOld ? $firstOld["name"] : "Not found") . PHP_EOL;

// PHP 8.4 way - much cleaner!
$firstOver30 = array_find($users, fn($u) => $u["age"] > 30);
echo "PHP 8.4 way: " . ($firstOver30 ? $firstOver30["name"] : "Not found") . PHP_EOL;

// Find first inactive user
$inactiveUser = array_find($users, fn($u) => !$u["active"]);
echo "First inactive: " . ($inactiveUser ? $inactiveUser["name"] : "Not found") . PHP_EOL;

// Find user by name
$bob = array_find($users, fn($u) => $u["name"] === "Bob");
echo "Found Bob: " . ($bob ? "Yes (age {$bob['age']})" : "No") . PHP_EOL;
echo PHP_EOL;

// 2. array_find_key() - Find key of first matching element
echo "2. array_find_key() - Find Key of Match:" . PHP_EOL;

// Find index of first user over 30
$keyOver30 = array_find_key($users, fn($u) => $u["age"] > 30);
echo "Index of first user over 30: " . ($keyOver30 !== null ? $keyOver30 : "Not found") . PHP_EOL;

// Find index of inactive user
$inactiveKey = array_find_key($users, fn($u) => !$u["active"]);
echo "Index of inactive user: " . ($inactiveKey !== null ? $inactiveKey : "Not found") . PHP_EOL;

// Find index of specific number
$key = array_find_key($numbers, fn($n) => $n > 10);
echo "Index of first number > 10: " . ($key !== null ? $key : "Not found") . PHP_EOL;
echo PHP_EOL;

// 3. array_any() - Check if ANY element matches
echo "3. array_any() - Check if Any Match:" . PHP_EOL;

// Check if any user is inactive
$hasInactive = array_any($users, fn($u) => !$u["active"]);
echo "Has inactive users? " . ($hasInactive ? "Yes" : "No") . PHP_EOL;

// Check if any user is over 40
$hasOver40 = array_any($users, fn($u) => $u["age"] > 40);
echo "Has users over 40? " . ($hasOver40 ? "Yes" : "No") . PHP_EOL;

// Check if any number is even
$hasEven = array_any($numbers, fn($n) => $n % 2 === 0);
echo "Has even numbers? " . ($hasEven ? "Yes" : "No") . PHP_EOL;

// Check if any user named "Eve"
$hasEve = array_any($users, fn($u) => $u["name"] === "Eve");
echo "Has user named Eve? " . ($hasEve ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

// 4. array_all() - Check if ALL elements match
echo "4. array_all() - Check if All Match:" . PHP_EOL;

// Check if all users are active
$allActive = array_all($users, fn($u) => $u["active"]);
echo "All users active? " . ($allActive ? "Yes" : "No") . PHP_EOL;

// Check if all users are over 20
$allOver20 = array_all($users, fn($u) => $u["age"] > 20);
echo "All users over 20? " . ($allOver20 ? "Yes" : "No") . PHP_EOL;

// Check if all numbers are odd
$allOdd = array_all($numbers, fn($n) => $n % 2 !== 0);
echo "All numbers odd? " . ($allOdd ? "Yes" : "No") . PHP_EOL;

// Check if all users have names
$allHaveNames = array_all($users, fn($u) => isset($u["name"]) && !empty($u["name"]));
echo "All users have names? " . ($allHaveNames ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

// 5. Practical Examples
echo "5. Practical Examples:" . PHP_EOL . PHP_EOL;

// Example: Form validation
echo "Example A: Form Validation" . PHP_EOL;
$fields = [
    ["name" => "email", "value" => "test@example.com", "required" => true],
    ["name" => "age", "value" => "", "required" => true],
    ["name" => "bio", "value" => "Hello", "required" => false]
];

$allRequiredFilled = array_all(
    array_filter($fields, fn($f) => $f["required"]),
    fn($f) => !empty($f["value"])
);

echo "All required fields filled? " . ($allRequiredFilled ? "Yes" : "No") . PHP_EOL;

$hasEmptyRequired = array_any(
    array_filter($fields, fn($f) => $f["required"]),
    fn($f) => empty($f["value"])
);

echo "Has empty required fields? " . ($hasEmptyRequired ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

// Example: Product inventory
echo "Example B: Product Inventory" . PHP_EOL;
$products = [
    ["sku" => "A001", "name" => "Widget", "stock" => 15, "price" => 19.99],
    ["sku" => "A002", "name" => "Gadget", "stock" => 0, "price" => 29.99],
    ["sku" => "A003", "name" => "Gizmo", "stock" => 5, "price" => 39.99]
];

$allInStock = array_all($products, fn($p) => $p["stock"] > 0);
echo "All products in stock? " . ($allInStock ? "Yes" : "No") . PHP_EOL;

$anyOutOfStock = array_any($products, fn($p) => $p["stock"] === 0);
echo "Any products out of stock? " . ($anyOutOfStock ? "Yes" : "No") . PHP_EOL;

$firstOutOfStock = array_find($products, fn($p) => $p["stock"] === 0);
if ($firstOutOfStock) {
    echo "First out of stock: {$firstOutOfStock['name']} (SKU: {$firstOutOfStock['sku']})" . PHP_EOL;
}

$firstExpensive = array_find($products, fn($p) => $p["price"] > 35);
if ($firstExpensive) {
    echo "First expensive product: {$firstExpensive['name']} (\${$firstExpensive['price']})" . PHP_EOL;
}
echo PHP_EOL;

// 6. Comparison with Old Methods
echo "6. Before vs After PHP 8.4:" . PHP_EOL . PHP_EOL;

echo "Finding first even number:" . PHP_EOL;

// OLD WAY (verbose)
$filtered = array_filter($numbers, fn($n) => $n % 2 === 0);
$firstEvenOld = reset($filtered) ?: null;
echo "OLD: " . ($firstEvenOld ?? "Not found") . PHP_EOL;

// NEW WAY (clean)
$firstEvenNew = array_find($numbers, fn($n) => $n % 2 === 0);
echo "NEW: " . ($firstEvenNew ?? "Not found") . PHP_EOL;
echo PHP_EOL;

echo "Checking if any number is > 100:" . PHP_EOL;

// OLD WAY
$hasLarge = count(array_filter($numbers, fn($n) => $n > 100)) > 0;
echo "OLD: " . ($hasLarge ? "Yes" : "No") . PHP_EOL;

// NEW WAY
$hasLargeNew = array_any($numbers, fn($n) => $n > 100);
echo "NEW: " . ($hasLargeNew ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

// 7. Performance Benefits
echo "7. Why These Functions Matter:" . PHP_EOL;
echo "✓ More expressive and readable code" . PHP_EOL;
echo "✓ Stop iterating once condition is met (array_find, array_any)" . PHP_EOL;
echo "✓ No need for reset() or array_values() workarounds" . PHP_EOL;
echo "✓ Consistent return types (null for not found)" . PHP_EOL;
echo "✓ Better intent communication" . PHP_EOL;
