<?php

declare(strict_types=1);

/**
 * Exercise 1: Simple Calculator
 * 
 * Create a calculator that performs basic operations on two numbers.
 * Use appropriate data types and display results.
 */

// Solution:

$num1 = 15.5;
$num2 = 4.2;

echo "=== Simple Calculator ===" . PHP_EOL;
echo "Number 1: $num1" . PHP_EOL;
echo "Number 2: $num2" . PHP_EOL;
echo PHP_EOL;

$sum = $num1 + $num2;
$difference = $num1 - $num2;
$product = $num1 * $num2;
$quotient = $num1 / $num2;
$remainder = $num1 % $num2;

echo "Addition: $num1 + $num2 = $sum" . PHP_EOL;
echo "Subtraction: $num1 - $num2 = $difference" . PHP_EOL;
echo "Multiplication: $num1 × $num2 = $product" . PHP_EOL;
echo "Division: $num1 ÷ $num2 = " . round($quotient, 2) . PHP_EOL;
echo "Modulus (remainder): $num1 % $num2 = $remainder" . PHP_EOL;
