<?php

declare(strict_types=1);

/**
 * Basic String Operations
 * 
 * Demonstrates fundamental string operations including:
 * - String creation and concatenation
 * - String length and character access
 * - Case conversion
 * - Trimming and padding
 */

echo "=== Basic String Operations ===" . PHP_EOL . PHP_EOL;

// Example 1: String creation
echo "1. String Creation:" . PHP_EOL;
$single = 'Single quoted string';
$double = "Double quoted string";
$heredoc = <<<TEXT
Heredoc syntax allows
multi-line strings
without escaping quotes
TEXT;

echo "Single: $single" . PHP_EOL;
echo "Double: $double" . PHP_EOL;
echo "Heredoc: $heredoc" . PHP_EOL;
echo PHP_EOL;

// Example 2: String concatenation
echo "2. String Concatenation:" . PHP_EOL;
$first = "Hello";
$last = "World";

$full = $first . " " . $last;  // Concatenation operator
echo "Concatenation: $full" . PHP_EOL;

$greeting = "$first, $last!";   // Variable interpolation (only in double quotes)
echo "Interpolation: $greeting" . PHP_EOL;

$complex = "{$first} {$last}";  // Complex interpolation
echo "Complex: $complex" . PHP_EOL;
echo PHP_EOL;

// Example 3: String length
echo "3. String Length:" . PHP_EOL;
$text = "PHP is awesome!";
$length = strlen($text);
echo "Text: '$text'" . PHP_EOL;
echo "Length: $length characters" . PHP_EOL;

// Multibyte length (for UTF-8)
$emoji = "Hello 👋 World 🌍";
echo "String: '$emoji'" . PHP_EOL;
echo "strlen(): " . strlen($emoji) . " bytes" . PHP_EOL;
echo "mb_strlen(): " . mb_strlen($emoji) . " characters" . PHP_EOL;
echo PHP_EOL;

// Example 4: Accessing characters
echo "4. Accessing Characters:" . PHP_EOL;
$word = "Programming";
echo "Word: $word" . PHP_EOL;
echo "First character: {$word[0]}" . PHP_EOL;
echo "Third character: {$word[2]}" . PHP_EOL;
echo "Last character: {$word[strlen($word) - 1]}" . PHP_EOL;
echo PHP_EOL;

// Example 5: Case conversion
echo "5. Case Conversion:" . PHP_EOL;
$original = "Hello World";
echo "Original: $original" . PHP_EOL;
echo "Uppercase: " . strtoupper($original) . PHP_EOL;
echo "Lowercase: " . strtolower($original) . PHP_EOL;
echo "First char upper: " . ucfirst(strtolower($original)) . PHP_EOL;
echo "Each word upper: " . ucwords(strtolower($original)) . PHP_EOL;
echo PHP_EOL;

// Example 6: Trimming whitespace
echo "6. Trimming Whitespace:" . PHP_EOL;
$messy = "  lots of space  ";
echo "Original: '$messy'" . PHP_EOL;
echo "trim(): '" . trim($messy) . "'" . PHP_EOL;
echo "ltrim(): '" . ltrim($messy) . "'" . PHP_EOL;
echo "rtrim(): '" . rtrim($messy) . "'" . PHP_EOL;

// Custom character trim
$url = "https://example.com/";
echo "URL: '$url'" . PHP_EOL;
echo "Trimmed '/': '" . rtrim($url, '/') . "'" . PHP_EOL;
echo PHP_EOL;

// Example 7: String padding
echo "7. String Padding:" . PHP_EOL;
$number = "42";
echo "Original: '$number'" . PHP_EOL;
echo "Left pad (zeros): '" . str_pad($number, 5, '0', STR_PAD_LEFT) . "'" . PHP_EOL;
echo "Right pad (zeros): '" . str_pad($number, 5, '0', STR_PAD_RIGHT) . "'" . PHP_EOL;
echo "Both sides: '" . str_pad($number, 6, '-', STR_PAD_BOTH) . "'" . PHP_EOL;
echo PHP_EOL;

// Example 8: String reversal
echo "8. String Reversal:" . PHP_EOL;
$word = "PHP";
$reversed = strrev($word);
echo "Original: $word" . PHP_EOL;
echo "Reversed: $reversed" . PHP_EOL;
echo PHP_EOL;

// Example 9: String repeat
echo "9. String Repetition:" . PHP_EOL;
$char = "=";
$line = str_repeat($char, 20);
echo $line . PHP_EOL;
echo "Title" . PHP_EOL;
echo $line . PHP_EOL;
echo PHP_EOL;

// Example 10: Practical example - Formatting names
echo "10. Practical Example - Name Formatting:" . PHP_EOL;

function formatName(string $name): string
{
    // Trim whitespace
    $name = trim($name);

    // Convert to lowercase
    $name = strtolower($name);

    // Capitalize first letter of each word
    $name = ucwords($name);

    return $name;
}

$names = ["  JOHN DOE  ", "jane smith", "bob WILSON"];

foreach ($names as $name) {
    echo "Input: '$name' → Output: '" . formatName($name) . "'" . PHP_EOL;
}
echo PHP_EOL;

// Example 11: String comparison
echo "11. String Comparison:" . PHP_EOL;
$str1 = "apple";
$str2 = "Apple";

echo "Case-sensitive: " . ($str1 === $str2 ? "Equal" : "Not equal") . PHP_EOL;
echo "Case-insensitive: " . (strcasecmp($str1, $str2) === 0 ? "Equal" : "Not equal") . PHP_EOL;
echo PHP_EOL;

// Example 12: Practical example - Creating initials
echo "12. Practical Example - Initials:" . PHP_EOL;

function getInitials(string $name): string
{
    $parts = explode(' ', trim($name));
    $initials = '';

    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }

    return $initials;
}

$fullNames = ["John Doe", "Mary Jane Watson", "Bob"];
foreach ($fullNames as $fullName) {
    echo "$fullName → " . getInitials($fullName) . PHP_EOL;
}
