<?php

declare(strict_types=1);

/**
 * Exercise 3: Array Manipulation Challenge
 * 
 * Practice various array operations in a single script.
 * 
 * Requirements:
 * - Start with array: [15, 8, 23, 4, 42, 16]
 * - Add 50 to the end
 * - Remove the first element
 * - Sort in descending order
 * - Use spread operator to add [100, 200]
 * - Print final array and count
 */

// Start with the initial array
$numbers = [15, 8, 23, 4, 42, 16];

echo "=== Array Manipulation Challenge ===" . PHP_EOL . PHP_EOL;
echo "Initial array: " . implode(', ', $numbers) . PHP_EOL;

// Add 50 to the end
$numbers[] = 50;
echo "After adding 50: " . implode(', ', $numbers) . PHP_EOL;

// Remove the first element
array_shift($numbers);
echo "After removing first element: " . implode(', ', $numbers) . PHP_EOL;

// Sort in descending order
rsort($numbers);
echo "After sorting (descending): " . implode(', ', $numbers) . PHP_EOL;

// Use spread operator to combine with [100, 200]
$numbers = [...$numbers, 100, 200];
echo PHP_EOL . "=== Final Result ===" . PHP_EOL;
echo "Final array: " . implode(', ', $numbers) . PHP_EOL;
echo "Total numbers: " . count($numbers) . PHP_EOL;
