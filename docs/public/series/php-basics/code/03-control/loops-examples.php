<?php

declare(strict_types=1);

/**
 * Loop Control Structures
 * 
 * Demonstrates different types of loops in PHP:
 * - for loop: when you know iteration count
 * - while loop: condition-based iteration
 * - do-while loop: execute at least once
 * - foreach loop: iterate over arrays
 * - break and continue statements
 */

echo "=== Loop Examples ===" . PHP_EOL . PHP_EOL;

// Example 1: Basic for loop
echo "1. Basic For Loop:" . PHP_EOL;
for ($i = 1; $i <= 5; $i++) {
    echo "Iteration $i" . PHP_EOL;
}
echo PHP_EOL;

// Example 2: For loop counting down
echo "2. For Loop Counting Down:" . PHP_EOL;
for ($i = 10; $i >= 1; $i--) {
    echo "$i ";
}
echo PHP_EOL . "Blast off! 🚀" . PHP_EOL . PHP_EOL;

// Example 3: For loop with step
echo "3. For Loop with Custom Step:" . PHP_EOL;
echo "Even numbers 0-20: ";
for ($i = 0; $i <= 20; $i += 2) {
    echo "$i ";
}
echo PHP_EOL . PHP_EOL;

// Example 4: While loop
echo "4. While Loop:" . PHP_EOL;
$count = 1;
while ($count <= 5) {
    echo "Count: $count" . PHP_EOL;
    $count++;
}
echo PHP_EOL;

// Example 5: Do-while loop (executes at least once)
echo "5. Do-While Loop:" . PHP_EOL;
$number = 10;
do {
    echo "Number: $number" . PHP_EOL;
    $number++;
} while ($number <= 5); // Condition is false, but executes once
echo "Loop executed even though condition was false!" . PHP_EOL . PHP_EOL;

// Example 6: Foreach with indexed array
echo "6. Foreach with Indexed Array:" . PHP_EOL;
$fruits = ['Apple', 'Banana', 'Cherry', 'Date'];

foreach ($fruits as $fruit) {
    echo "• $fruit" . PHP_EOL;
}
echo PHP_EOL;

// Example 7: Foreach with array keys and values
echo "7. Foreach with Keys and Values:" . PHP_EOL;
$fruits = ['Apple', 'Banana', 'Cherry', 'Date'];

foreach ($fruits as $index => $fruit) {
    echo "[$index] $fruit" . PHP_EOL;
}
echo PHP_EOL;

// Example 8: Foreach with associative array
echo "8. Foreach with Associative Array:" . PHP_EOL;
$person = [
    'name' => 'Alice',
    'age' => 28,
    'city' => 'New York',
    'occupation' => 'Developer'
];

foreach ($person as $key => $value) {
    echo "$key: $value" . PHP_EOL;
}
echo PHP_EOL;

// Example 9: Break statement (exit loop early)
echo "9. Break Statement:" . PHP_EOL;
echo "Finding first number divisible by 7:" . PHP_EOL;
for ($i = 1; $i <= 100; $i++) {
    if ($i % 7 === 0) {
        echo "Found: $i" . PHP_EOL;
        break; // Exit the loop
    }
}
echo PHP_EOL;

// Example 10: Continue statement (skip to next iteration)
echo "10. Continue Statement:" . PHP_EOL;
echo "Odd numbers 1-20:" . PHP_EOL;
for ($i = 1; $i <= 20; $i++) {
    if ($i % 2 === 0) {
        continue; // Skip even numbers
    }
    echo "$i ";
}
echo PHP_EOL . PHP_EOL;

// Example 11: Nested loops (multiplication table)
echo "11. Nested Loops - Multiplication Table:" . PHP_EOL;
for ($row = 1; $row <= 5; $row++) {
    for ($col = 1; $col <= 5; $col++) {
        $product = $row * $col;
        echo str_pad((string)$product, 4, ' ', STR_PAD_LEFT);
    }
    echo PHP_EOL;
}
echo PHP_EOL;

// Example 12: Infinite loop with break (useful pattern)
echo "12. Controlled Infinite Loop:" . PHP_EOL;
$attempts = 0;
$maxAttempts = 5;

while (true) {
    $attempts++;
    echo "Attempt $attempts..." . PHP_EOL;

    if ($attempts >= $maxAttempts) {
        echo "Max attempts reached!" . PHP_EOL;
        break;
    }
}
echo PHP_EOL;

// Example 13: Loop with multiple conditions
echo "13. Loop with Multiple Exit Conditions:" . PHP_EOL;
$sum = 0;
$num = 1;

while ($num <= 10 && $sum < 30) {
    $sum += $num;
    echo "Added $num, sum is now $sum" . PHP_EOL;
    $num++;
}
echo "Loop ended: sum = $sum" . PHP_EOL . PHP_EOL;

// Example 14: Practical example - Menu system
echo "14. Practical Example - Menu Processing:" . PHP_EOL;
$menuItems = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'About', 'url' => '/about'],
    ['name' => 'Services', 'url' => '/services'],
    ['name' => 'Contact', 'url' => '/contact']
];

echo "Navigation Menu:" . PHP_EOL;
foreach ($menuItems as $item) {
    echo "  • {$item['name']} → {$item['url']}" . PHP_EOL;
}
echo PHP_EOL;

// Example 15: Practical example - Shopping cart total
echo "15. Practical Example - Shopping Cart:" . PHP_EOL;
$cart = [
    ['item' => 'Book', 'price' => 19.99, 'quantity' => 2],
    ['item' => 'Pen', 'price' => 2.50, 'quantity' => 5],
    ['item' => 'Notebook', 'price' => 7.99, 'quantity' => 3]
];

$total = 0;
echo "Cart items:" . PHP_EOL;

foreach ($cart as $product) {
    $subtotal = $product['price'] * $product['quantity'];
    $total += $subtotal;

    echo "  {$product['item']}: \${$product['price']} × {$product['quantity']} = \${$subtotal}" . PHP_EOL;
}

echo "─────────────────────────" . PHP_EOL;
echo "Total: \$" . number_format($total, 2) . PHP_EOL;
echo PHP_EOL;

// Example 16: Performance tip - caching array length
echo "16. Performance Tip - Cache Array Length:" . PHP_EOL;

// Less efficient (calculates count() every iteration)
$numbers = range(1, 10);
echo "Less efficient:" . PHP_EOL;
for ($i = 0; $i < count($numbers); $i++) {
    echo $numbers[$i] . " ";
}
echo PHP_EOL;

// More efficient (calculates once)
echo "More efficient:" . PHP_EOL;
$length = count($numbers);
for ($i = 0; $i < $length; $i++) {
    echo $numbers[$i] . " ";
}
echo PHP_EOL . PHP_EOL;

// Best: Use foreach for arrays when possible
echo "Best practice (foreach):" . PHP_EOL;
foreach ($numbers as $number) {
    echo "$number ";
}
echo PHP_EOL;
