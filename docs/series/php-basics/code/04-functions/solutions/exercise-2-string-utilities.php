<?php

declare(strict_types=1);

/**
 * Exercise 2: String Utility Functions
 * 
 * Create a collection of useful string utility functions:
 * - isPalindrome(string $text): bool - Check if text reads same backwards
 * - wordCount(string $text): int - Count words in text
 * - reverseWords(string $text): string - Reverse word order
 * - truncate(string $text, int $length, string $suffix = '...'): string
 */

// Solution:

function isPalindrome(string $text): bool
{
    // Remove spaces and convert to lowercase
    $clean = strtolower(str_replace(' ', '', $text));

    // Compare with reversed version
    return $clean === strrev($clean);
}

function wordCount(string $text): int
{
    // Trim whitespace and split by spaces
    $words = array_filter(explode(' ', trim($text)));
    return count($words);
}

function reverseWords(string $text): string
{
    $words = explode(' ', trim($text));
    return implode(' ', array_reverse($words));
}

function truncate(string $text, int $length, string $suffix = '...'): string
{
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

// Testing the functions
echo "=== String Utility Functions ===" . PHP_EOL . PHP_EOL;

// Test isPalindrome
echo "1. Palindrome Checker:" . PHP_EOL;
$testWords = ['racecar', 'hello', 'A man a plan a canal Panama', 'level'];

foreach ($testWords as $word) {
    $result = isPalindrome($word) ? 'YES' : 'NO';
    echo "  '$word' is palindrome: $result" . PHP_EOL;
}
echo PHP_EOL;

// Test wordCount
echo "2. Word Counter:" . PHP_EOL;
$sentences = [
    'Hello world',
    'PHP is a great programming language',
    'One'
];

foreach ($sentences as $sentence) {
    $count = wordCount($sentence);
    echo "  '$sentence' has $count words" . PHP_EOL;
}
echo PHP_EOL;

// Test reverseWords
echo "3. Reverse Words:" . PHP_EOL;
$phrase = "The quick brown fox jumps";
echo "  Original: $phrase" . PHP_EOL;
echo "  Reversed: " . reverseWords($phrase) . PHP_EOL;
echo PHP_EOL;

// Test truncate
echo "4. Truncate Text:" . PHP_EOL;
$longText = "This is a very long string that needs to be truncated";
echo "  Original: $longText" . PHP_EOL;
echo "  Truncated (20): " . truncate($longText, 20) . PHP_EOL;
echo "  Truncated (30, custom): " . truncate($longText, 30, '... [more]') . PHP_EOL;
echo PHP_EOL;

// Bonus: Slug generator
function createSlug(string $text): string
{
    // Convert to lowercase
    $slug = strtolower($text);

    // Replace non-alphanumeric with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');

    return $slug;
}

echo "5. Bonus - Slug Generator:" . PHP_EOL;
$titles = [
    'Hello World',
    'PHP From Scratch',
    'Understanding Functions in PHP 8.4!'
];

foreach ($titles as $title) {
    echo "  '$title' → '" . createSlug($title) . "'" . PHP_EOL;
}
