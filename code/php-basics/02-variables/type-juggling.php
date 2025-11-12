<?php

/**
 * Type Juggling (Type Coercion) Demonstration
 * 
 * PHP automatically converts types in certain situations.
 * This is called "type juggling" or "type coercion".
 * 
 * Note: This file intentionally does NOT use strict_types
 * to demonstrate automatic type conversion.
 */

echo "=== Type Juggling Examples ===" . PHP_EOL . PHP_EOL;

// Automatic conversion in arithmetic
echo "1. Arithmetic Operations:" . PHP_EOL;
$string = "5";
$number = 10;
$result = $string + $number; // "5" becomes 5
echo "\"5\" + 10 = $result (type: " . gettype($result) . ")" . PHP_EOL;
echo PHP_EOL;

// String concatenation
echo "2. String Concatenation:" . PHP_EOL;
$age = 25;
$message = "I am " . $age . " years old"; // 25 becomes "25"
echo $message . " (type: " . gettype($message) . ")" . PHP_EOL;
echo PHP_EOL;

// Boolean context
echo "3. Boolean Context:" . PHP_EOL;
$value1 = "hello";
$value2 = "";
$value3 = 0;
$value4 = "0";

echo "if (\"hello\"): " . ($value1 ? "true" : "false") . PHP_EOL;
echo "if (\"\"): " . ($value2 ? "true" : "false") . PHP_EOL;
echo "if (0): " . ($value3 ? "true" : "false") . PHP_EOL;
echo "if (\"0\"): " . ($value4 ? "true" : "false") . PHP_EOL;
echo PHP_EOL;

// Explicit type casting
echo "4. Explicit Type Casting:" . PHP_EOL;
$price = "19.99";
$intPrice = (int)$price;
$floatPrice = (float)$price;
$boolPrice = (bool)$price;

echo "Original: \"$price\" (type: " . gettype($price) . ")" . PHP_EOL;
echo "Cast to int: $intPrice (type: " . gettype($intPrice) . ")" . PHP_EOL;
echo "Cast to float: $floatPrice (type: " . gettype($floatPrice) . ")" . PHP_EOL;
echo "Cast to bool: " . ($boolPrice ? "true" : "false") . " (type: " . gettype($boolPrice) . ")" . PHP_EOL;
echo PHP_EOL;

// Comparison with loose vs strict
echo "5. Loose vs Strict Comparison:" . PHP_EOL;
$a = 5;
$b = "5";

echo "5 == \"5\" (loose): " . ($a == $b ? "true" : "false") . " (compares values)" . PHP_EOL;
echo "5 === \"5\" (strict): " . ($a === $b ? "true" : "false") . " (compares values AND types)" . PHP_EOL;
echo PHP_EOL;

// Falsy values in PHP
echo "6. Falsy Values:" . PHP_EOL;
echo "The following values are considered false:" . PHP_EOL;
echo "  - false: " . (false ? "truthy" : "falsy") . PHP_EOL;
echo "  - 0: " . (0 ? "truthy" : "falsy") . PHP_EOL;
echo "  - 0.0: " . (0.0 ? "truthy" : "falsy") . PHP_EOL;
echo "  - \"\" (empty string): " . ("" ? "truthy" : "falsy") . PHP_EOL;
echo "  - \"0\" (string zero): " . ("0" ? "truthy" : "falsy") . PHP_EOL;
echo "  - null: " . (null ? "truthy" : "falsy") . PHP_EOL;
echo "  - [] (empty array): " . ([] ? "truthy" : "falsy") . PHP_EOL;
