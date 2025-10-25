<?php

declare(strict_types=1);

/**
 * String Searching and Replacement
 * 
 * Demonstrates finding substrings and replacing text:
 * - PHP 8.0+ str_contains(), str_starts_with(), str_ends_with()
 * - strpos(), strstr() for searching
 * - str_replace(), str_ireplace() for replacement
 * - substr() for extracting substrings
 */

echo "=== String Searching and Replacement ===" . PHP_EOL . PHP_EOL;

// Example 1: Modern PHP 8.0+ string checking
echo "1. PHP 8.0+ String Checking Functions:" . PHP_EOL;
$text = "Hello, welcome to PHP programming!";
echo "Text: '$text'" . PHP_EOL . PHP_EOL;

// str_contains() - Check if string contains substring
echo "Contains 'PHP': " . (str_contains($text, 'PHP') ? 'Yes' : 'No') . PHP_EOL;
echo "Contains 'Python': " . (str_contains($text, 'Python') ? 'Yes' : 'No') . PHP_EOL;

// str_starts_with() - Check if string starts with substring
echo "Starts with 'Hello': " . (str_starts_with($text, 'Hello') ? 'Yes' : 'No') . PHP_EOL;
echo "Starts with 'Hi': " . (str_starts_with($text, 'Hi') ? 'Yes' : 'No') . PHP_EOL;

// str_ends_with() - Check if string ends with substring
echo "Ends with 'programming!': " . (str_ends_with($text, 'programming!') ? 'Yes' : 'No') . PHP_EOL;
echo "Ends with '?': " . (str_ends_with($text, '?') ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 2: Finding substring position
echo "2. Finding Substring Position:" . PHP_EOL;
$sentence = "The quick brown fox jumps over the lazy dog";
echo "Sentence: '$sentence'" . PHP_EOL . PHP_EOL;

// strpos() - Find first occurrence
$pos = strpos($sentence, 'fox');
echo "Position of 'fox': $pos" . PHP_EOL;

$pos2 = strpos($sentence, 'the');
echo "Position of 'the': $pos2 (first occurrence)" . PHP_EOL;

// strrpos() - Find last occurrence
$lastPos = strrpos($sentence, 'the');
echo "Last position of 'the': $lastPos" . PHP_EOL;

// Not found returns false
$notFound = strpos($sentence, 'cat');
echo "Position of 'cat': " . ($notFound === false ? 'Not found' : $notFound) . PHP_EOL;
echo PHP_EOL;

// Example 3: Case-insensitive search
echo "3. Case-Insensitive Search:" . PHP_EOL;
$text = "Hello World";
echo "Text: '$text'" . PHP_EOL;

echo "strpos('WORLD'): " . (strpos($text, 'WORLD') === false ? 'Not found' : 'Found') . PHP_EOL;
echo "stripos('WORLD'): " . (stripos($text, 'WORLD') === false ? 'Not found' : 'Found at ' . stripos($text, 'WORLD')) . PHP_EOL;
echo PHP_EOL;

// Example 4: Extracting substrings
echo "4. Extracting Substrings with substr():" . PHP_EOL;
$text = "Programming";
echo "Original: '$text'" . PHP_EOL;

echo "substr(0, 4): '" . substr($text, 0, 4) . "' (first 4 chars)" . PHP_EOL;
echo "substr(3): '" . substr($text, 3) . "' (from position 3 to end)" . PHP_EOL;
echo "substr(-4): '" . substr($text, -4) . "' (last 4 chars)" . PHP_EOL;
echo "substr(3, -4): '" . substr($text, 3, -4) . "' (from pos 3, stop 4 from end)" . PHP_EOL;
echo PHP_EOL;

// Example 5: Simple replacement
echo "5. Simple String Replacement:" . PHP_EOL;
$original = "Hello World, World is beautiful";
echo "Original: '$original'" . PHP_EOL;

$replaced = str_replace('World', 'PHP', $original);
echo "Replace 'World' with 'PHP': '$replaced'" . PHP_EOL;
echo PHP_EOL;

// Example 6: Multiple replacements
echo "6. Multiple Replacements:" . PHP_EOL;
$text = "I love cats and dogs. Cats are cute and dogs are loyal.";
echo "Original: '$text'" . PHP_EOL;

$find = ['cats', 'dogs'];
$replace = ['birds', 'fish'];
$result = str_replace($find, $replace, $text);
echo "After replacement: '$result'" . PHP_EOL;
echo PHP_EOL;

// Example 7: Case-insensitive replacement
echo "7. Case-Insensitive Replacement:" . PHP_EOL;
$text = "PHP is great. php is powerful. Php is amazing.";
echo "Original: '$text'" . PHP_EOL;

$result = str_ireplace('php', 'Python', $text);
echo "Replace all 'PHP' (case-insensitive): '$result'" . PHP_EOL;
echo PHP_EOL;

// Example 8: Counting replacements
echo "8. Counting Replacements:" . PHP_EOL;
$text = "foo bar foo baz foo";
$count = 0;
$result = str_replace('foo', 'qux', $text, $count);
echo "Original: '$text'" . PHP_EOL;
echo "Result: '$result'" . PHP_EOL;
echo "Replacements made: $count" . PHP_EOL;
echo PHP_EOL;

// Example 9: Get substring after/before a delimiter
echo "9. Extracting Parts:" . PHP_EOL;
$email = "user@example.com";
echo "Email: '$email'" . PHP_EOL;

// Get part before @
$username = strstr($email, '@', true);
echo "Username: '$username'" . PHP_EOL;

// Get part after @
$domain = substr(strstr($email, '@'), 1);
echo "Domain: '$domain'" . PHP_EOL;
echo PHP_EOL;

// Example 10: Practical example - Censoring profanity
echo "10. Practical Example - Content Filtering:" . PHP_EOL;

function censorText(string $text, array $badWords): string
{
    $replacement = '***';
    return str_ireplace($badWords, $replacement, $text);
}

$comment = "This is a badword and another badword in text";
$badWords = ['badword'];
$cleaned = censorText($comment, $badWords);
echo "Original: '$comment'" . PHP_EOL;
echo "Censored: '$cleaned'" . PHP_EOL;
echo PHP_EOL;

// Example 11: Practical example - Highlighting search terms
echo "11. Practical Example - Highlighting Search Terms:" . PHP_EOL;

function highlightSearchTerm(string $text, string $term): string
{
    // Case-insensitive replacement with HTML mark tags
    return str_ireplace($term, "<mark>$term</mark>", $text);
}

$article = "PHP is a popular programming language. Many developers use PHP.";
$searchTerm = "PHP";
$highlighted = highlightSearchTerm($article, $searchTerm);
echo "Original: $article" . PHP_EOL;
echo "Highlighted: $highlighted" . PHP_EOL;
echo PHP_EOL;

// Example 12: Practical example - URL slug creation
echo "12. Practical Example - Creating URL Slugs:" . PHP_EOL;

function createSlug(string $text): string
{
    // Convert to lowercase
    $slug = strtolower($text);

    // Replace spaces and special chars with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');

    return $slug;
}

$titles = [
    "Hello World!",
    "PHP From Scratch: A Beginner's Guide",
    "10 Tips for Better Code"
];

foreach ($titles as $title) {
    echo "'$title' → '" . createSlug($title) . "'" . PHP_EOL;
}
echo PHP_EOL;

// Example 13: Substring replacement
echo "13. Substring Replacement:" . PHP_EOL;

function replaceSubstr(string $str, string $replacement, int $start, int $length): string
{
    return substr_replace($str, $replacement, $start, $length);
}

$text = "Hello World";
echo "Original: '$text'" . PHP_EOL;
echo "Replace chars 6-11 with 'PHP': '" . replaceSubstr($text, 'PHP', 6, 5) . "'" . PHP_EOL;
echo PHP_EOL;

// Example 14: Practical example - Truncating with ellipsis
echo "14. Practical Example - Truncate Long Text:" . PHP_EOL;

function truncate(string $text, int $maxLength, string $suffix = '...'): string
{
    if (strlen($text) <= $maxLength) {
        return $text;
    }

    return substr($text, 0, $maxLength - strlen($suffix)) . $suffix;
}

$longText = "This is a very long piece of text that needs to be truncated for display purposes.";
echo "Original length: " . strlen($longText) . " chars" . PHP_EOL;
echo "Truncated (30): '" . truncate($longText, 30) . "'" . PHP_EOL;
echo "Truncated (50): '" . truncate($longText, 50) . "'" . PHP_EOL;
