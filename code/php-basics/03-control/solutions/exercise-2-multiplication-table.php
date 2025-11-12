<?php

declare(strict_types=1);

/**
 * Exercise 2: Multiplication Table Generator
 * 
 * Create a program that prints a multiplication table for a given number.
 * Print the table from 1 to 10.
 */

// Solution:

$number = 7;

echo "=== Multiplication Table for $number ===" . PHP_EOL . PHP_EOL;

for ($i = 1; $i <= 10; $i++) {
    $result = $number * $i;
    echo "$number × $i = $result" . PHP_EOL;
}

echo PHP_EOL;

// Bonus: Create a full multiplication table (1-12 x 1-12)
echo "=== Full Multiplication Table (1-12) ===" . PHP_EOL . PHP_EOL;

// Print header
echo "   |";
for ($col = 1; $col <= 12; $col++) {
    echo str_pad((string)$col, 4, ' ', STR_PAD_LEFT);
}
echo PHP_EOL;
echo "---+" . str_repeat("----", 12) . PHP_EOL;

// Print rows
for ($row = 1; $row <= 12; $row++) {
    echo str_pad((string)$row, 3, ' ', STR_PAD_LEFT) . "|";

    for ($col = 1; $col <= 12; $col++) {
        $product = $row * $col;
        echo str_pad((string)$product, 4, ' ', STR_PAD_LEFT);
    }
    echo PHP_EOL;
}
