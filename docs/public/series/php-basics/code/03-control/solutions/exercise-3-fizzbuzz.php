<?php

declare(strict_types=1);

/**
 * Exercise 3: FizzBuzz Challenge
 * 
 * Write a program that prints numbers 1-100, but:
 * - For multiples of 3, print "Fizz"
 * - For multiples of 5, print "Buzz"
 * - For multiples of both 3 and 5, print "FizzBuzz"
 * - Otherwise, print the number
 * 
 * This is a classic programming interview question!
 */

// Solution:

echo "=== FizzBuzz (1-100) ===" . PHP_EOL . PHP_EOL;

for ($i = 1; $i <= 100; $i++) {
    // Check divisibility by both 3 and 5 first
    if ($i % 3 === 0 && $i % 5 === 0) {
        echo "FizzBuzz";
    }
    // Check divisibility by 3
    elseif ($i % 3 === 0) {
        echo "Fizz";
    }
    // Check divisibility by 5
    elseif ($i % 5 === 0) {
        echo "Buzz";
    }
    // Not divisible by 3 or 5
    else {
        echo $i;
    }

    // Add comma except for last number
    if ($i < 100) {
        echo ", ";
    }

    // Add line break every 10 items for readability
    if ($i % 10 === 0) {
        echo PHP_EOL;
    }
}

echo PHP_EOL . PHP_EOL;

// Alternative solution using match (more concise)
echo "=== FizzBuzz using Match (1-50) ===" . PHP_EOL . PHP_EOL;

for ($i = 1; $i <= 50; $i++) {
    $output = match (true) {
        $i % 15 === 0 => 'FizzBuzz', // 15 is LCM of 3 and 5
        $i % 3 === 0 => 'Fizz',
        $i % 5 === 0 => 'Buzz',
        default => (string)$i
    };

    echo $output;

    if ($i < 50) {
        echo ", ";
    }

    if ($i % 10 === 0) {
        echo PHP_EOL;
    }
}

echo PHP_EOL;
