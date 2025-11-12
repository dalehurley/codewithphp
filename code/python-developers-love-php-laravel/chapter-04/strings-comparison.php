<?php

declare(strict_types=1);

/**
 * String Syntax - PHP vs Python
 * 
 * This file demonstrates PHP string syntax differences from Python.
 * Key differences:
 * - PHP single quotes: literal text (no interpolation)
 * - PHP double quotes: allow {$variable} interpolation
 * - PHP uses . for concatenation (not +)
 * - PHP heredoc/nowdoc similar to Python triple-quoted strings
 */

// ============================================================================
// 1. Single vs Double Quotes
// ============================================================================

$name = "Alice";

// Single quotes: literal text (no interpolation)
$message1 = 'Hello, $name';  // Output: Hello, $name (literal)
echo $message1 . "\n";

// Double quotes: interpolation works
$message2 = "Hello, {$name}";  // Output: Hello, Alice
echo $message2 . "\n";

// Concatenation with . operator
$message3 = "Hello, " . $name;  // Output: Hello, Alice
echo $message3 . "\n";

// ============================================================================
// 2. String Interpolation
// ============================================================================

// Python: f"Hello, {name}! You are {age} years old."
// PHP:    "Hello, {$name}! You are {$age} years old."

$name = "Alice";
$age = 30;
$message = "Hello, {$name}! You are {$age} years old.";
echo $message . "\n";

// Simple variables can omit braces (but braces are clearer)
$message2 = "Hello, $name! You are $age years old.";
echo $message2 . "\n";

// Complex expressions require braces
$items = ["apple", "banana"];
$message3 = "Count: " . count($items) . ", First: {$items[0]}";
echo $message3 . "\n";

// ============================================================================
// 3. String Concatenation
// ============================================================================

// Python: first + " " + second
// PHP:    $first . " " . $second

$first = "Hello";
$second = "World";
$result = $first . " " . $second;
echo $result . "\n";  // Output: Hello World

// Important: PHP's + operator does math, not string concatenation
$num1 = "5";
$num2 = "3";
$math_result = $num1 + $num2;      // Output: 8 (coerced to numbers)
$concat_result = $num1 . $num2;   // Output: "53" (string concatenation)

echo "Math: $math_result\n";
echo "Concat: $concat_result\n";

// ============================================================================
// 4. Heredoc (Double-quoted style)
// ============================================================================

// Python: """Multi-line string with {variable}"""
// PHP:    <<<EOT ... EOT;

$name = "Alice";
$heredoc = <<<EOT
This is a multi-line string.
It preserves line breaks.
Variables: {$name}
You can use "quotes" and 'single quotes' freely.
EOT;

echo $heredoc . "\n";

// ============================================================================
// 5. Nowdoc (Single-quoted style)
// ============================================================================

// Python: '''Literal multi-line string'''
// PHP:    <<<'EOT' ... EOT;

$nowdoc = <<<'EOT'
This is literal text.
No variable interpolation: {$name}
Useful for code examples or templates.
EOT;

echo $nowdoc . "\n";

// ============================================================================
// 6. String Functions vs Methods
// ============================================================================

// Python: name.upper(), name.lower(), name.strip()
// PHP:    strtoupper($name), strtolower($name), trim($name)

$text = "  Hello World  ";

echo "Original: '$text'\n";
echo "Upper: " . strtoupper($text) . "\n";
echo "Lower: " . strtolower($text) . "\n";
echo "Trimmed: '" . trim($text) . "'\n";

// String length
// Python: len(text)
// PHP:    strlen($text)
echo "Length: " . strlen($text) . "\n";

// String contains
// Python: "hello" in text
// PHP:    str_contains($text, "hello")
$text2 = "Hello World";
echo "Contains 'World': " . (str_contains($text2, "World") ? "yes" : "no") . "\n";

// ============================================================================
// 7. String Formatting
// ============================================================================

// Python: f"Price: ${price:.2f}"
// PHP:    sprintf() or number_format()

$price = 99.999;
$formatted = sprintf("Price: $%.2f", $price);
echo $formatted . "\n";

// Or using number_format()
$formatted2 = "Price: $" . number_format($price, 2);
echo $formatted2 . "\n";

