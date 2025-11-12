<?php

/**
 * Variables and Output Demonstration
 * 
 * This script shows how to:
 * - Declare and use variables
 * - Output variables with echo
 * - Use string interpolation
 * - Concatenate strings and variables
 */

// Declaring variables
$name = "Alice";
$age = 25;
$city = "New York";

// Output variables with echo
echo "Name: " . $name . PHP_EOL;
echo "Age: " . $age . PHP_EOL;
echo "City: " . $city . PHP_EOL;

echo PHP_EOL; // Blank line

// String interpolation (variables inside double quotes)
echo "My name is $name and I am $age years old." . PHP_EOL;
echo "I live in $city." . PHP_EOL;

echo PHP_EOL;

// Variables can be reassigned
$name = "Bob";
echo "Now my name is $name." . PHP_EOL;

// Variables can store different types
$score = 95.5;
$isPassing = true;

echo "Score: $score" . PHP_EOL;
echo "Passing: " . ($isPassing ? "Yes" : "No") . PHP_EOL;
