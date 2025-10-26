<?php

declare(strict_types=1);

/**
 * Exercise 1: String Utility Library
 * 
 * Create a library of useful string functions
 */

echo "=== String Utility Library ===" . PHP_EOL . PHP_EOL;

/**
 * Check if a string is a palindrome
 */
function isPalindrome(string $str): bool
{
    $clean = strtolower(preg_replace('/[^a-z0-9]/i', '', $str));
    return $clean === strrev($clean);
}

/**
 * Generate URL-friendly slug from string
 */
function generateSlug(string $str): string
{
    $slug = strtolower($str);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Truncate string to specified length with ellipsis
 */
function truncate(string $str, int $length, string $suffix = '...'): string
{
    if (mb_strlen($str) <= $length) {
        return $str;
    }

    return mb_substr($str, 0, $length) . $suffix;
}

/**
 * Extract initials from full name
 */
function getInitials(string $name): string
{
    $words = explode(' ', $name);
    $initials = '';

    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }

    return $initials;
}

/**
 * Count words in a string
 */
function countWords(string $str): int
{
    return str_word_count($str);
}

/**
 * Mask sensitive data (e.g., credit card numbers)
 */
function maskString(string $str, int $visibleStart = 4, int $visibleEnd = 4, string $mask = '*'): string
{
    $length = strlen($str);

    if ($length <= $visibleStart + $visibleEnd) {
        return $str;
    }

    $start = substr($str, 0, $visibleStart);
    $end = substr($str, -$visibleEnd);
    $maskLength = $length - $visibleStart - $visibleEnd;

    return $start . str_repeat($mask, $maskLength) . $end;
}

/**
 * Convert string to title case
 */
function toTitleCase(string $str): string
{
    return ucwords(strtolower($str));
}

/**
 * Remove extra whitespace
 */
function normalizeWhitespace(string $str): string
{
    $str = trim($str);
    $str = preg_replace('/\s+/', ' ', $str);
    return $str;
}

/**
 * Check if string contains substring (case-insensitive)
 */
function containsString(string $haystack, string $needle): bool
{
    return stripos($haystack, $needle) !== false;
}

/**
 * Replace last occurrence of substring
 */
function replaceLast(string $search, string $replace, string $subject): string
{
    $pos = strrpos($subject, $search);

    if ($pos === false) {
        return $subject;
    }

    return substr_replace($subject, $replace, $pos, strlen($search));
}

// Test the functions
echo "1. Palindrome Check:" . PHP_EOL;
$tests = ['racecar', 'A man a plan a canal Panama', 'hello'];
foreach ($tests as $test) {
    $result = isPalindrome($test) ? 'Yes' : 'No';
    echo "  '$test' is palindrome: $result" . PHP_EOL;
}
echo PHP_EOL;

echo "2. Generate Slugs:" . PHP_EOL;
$titles = [
    'Hello World!',
    'PHP 8.4 Features',
    'Learn to Code: Complete Guide'
];
foreach ($titles as $title) {
    echo "  '$title' → '" . generateSlug($title) . "'" . PHP_EOL;
}
echo PHP_EOL;

echo "3. Truncate Text:" . PHP_EOL;
$long = 'This is a very long string that needs to be truncated for display purposes';
echo "  Original: $long" . PHP_EOL;
echo "  Truncated (30): " . truncate($long, 30) . PHP_EOL;
echo "  Truncated (50, '...'): " . truncate($long, 50, '...') . PHP_EOL;
echo PHP_EOL;

echo "4. Extract Initials:" . PHP_EOL;
$names = ['John Doe', 'Mary Jane Smith', 'Bob'];
foreach ($names as $name) {
    echo "  $name → " . getInitials($name) . PHP_EOL;
}
echo PHP_EOL;

echo "5. Word Count:" . PHP_EOL;
$text = 'The quick brown fox jumps over the lazy dog';
echo "  Text: $text" . PHP_EOL;
echo "  Word count: " . countWords($text) . PHP_EOL;
echo PHP_EOL;

echo "6. Mask Sensitive Data:" . PHP_EOL;
$creditCard = '4532123456789012';
$email = 'john.doe@example.com';
echo "  Credit card: $creditCard → " . maskString($creditCard, 4, 4) . PHP_EOL;
echo "  Email: $email → " . maskString($email, 3, 8) . PHP_EOL;
echo PHP_EOL;

echo "7. Title Case:" . PHP_EOL;
$text = 'the quick BROWN fox';
echo "  Original: $text" . PHP_EOL;
echo "  Title case: " . toTitleCase($text) . PHP_EOL;
echo PHP_EOL;

echo "8. Normalize Whitespace:" . PHP_EOL;
$messy = '  Hello    World  \n  With   Extra    Spaces  ';
echo "  Original: '$messy'" . PHP_EOL;
echo "  Normalized: '" . normalizeWhitespace($messy) . "'" . PHP_EOL;
echo PHP_EOL;

echo "9. Contains String:" . PHP_EOL;
$text = 'The quick brown fox';
$searches = ['quick', 'BROWN', 'dog'];
foreach ($searches as $search) {
    $result = containsString($text, $search) ? 'Yes' : 'No';
    echo "  Does '$text' contain '$search'? $result" . PHP_EOL;
}
echo PHP_EOL;

echo "10. Replace Last Occurrence:" . PHP_EOL;
$text = 'foo bar foo bar foo';
$result = replaceLast('foo', 'baz', $text);
echo "  Original: $text" . PHP_EOL;
echo "  After replace last 'foo': $result" . PHP_EOL;
