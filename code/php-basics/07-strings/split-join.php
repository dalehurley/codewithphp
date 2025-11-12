<?php

declare(strict_types=1);

/**
 * String Splitting and Joining
 * 
 * Demonstrates breaking strings apart and combining them:
 * - explode() - Split string into array
 * - implode()/join() - Join array into string
 * - str_split() - Split into characters
 * - chunk_split() - Split with chunks
 */

echo "=== String Splitting and Joining ===" . PHP_EOL . PHP_EOL;

// Example 1: Explode - Split string into array
echo "1. Explode - Split by Delimiter:" . PHP_EOL;
$csv = "apple,banana,cherry,date";
$fruits = explode(',', $csv);

echo "CSV: '$csv'" . PHP_EOL;
echo "Array:" . PHP_EOL;
print_r($fruits);
echo PHP_EOL;

// Example 2: Explode with limit
echo "2. Explode with Limit:" . PHP_EOL;
$text = "one:two:three:four:five";
echo "Text: '$text'" . PHP_EOL;

$parts2 = explode(':', $text, 2);  // Split into max 2 parts
echo "Limit 2:" . PHP_EOL;
print_r($parts2);

$parts3 = explode(':', $text, 3);  // Split into max 3 parts
echo "Limit 3:" . PHP_EOL;
print_r($parts3);
echo PHP_EOL;

// Example 3: Implode - Join array into string
echo "3. Implode - Join Array Elements:" . PHP_EOL;
$words = ['PHP', 'is', 'awesome'];
$sentence = implode(' ', $words);

echo "Array: " . json_encode($words) . PHP_EOL;
echo "Joined: '$sentence'" . PHP_EOL;
echo PHP_EOL;

// Example 4: Different separators
echo "4. Different Separators:" . PHP_EOL;
$items = ['Apple', 'Banana', 'Cherry'];
echo "Comma: " . implode(', ', $items) . PHP_EOL;
echo "Pipe: " . implode(' | ', $items) . PHP_EOL;
echo "Newline:" . PHP_EOL . implode(PHP_EOL, $items) . PHP_EOL;
echo "HTML: " . implode('<br>', $items) . PHP_EOL;
echo PHP_EOL;

// Example 5: Split string into characters
echo "5. Split into Characters:" . PHP_EOL;
$word = "Hello";
$chars = str_split($word);
echo "Word: '$word'" . PHP_EOL;
echo "Characters:" . PHP_EOL;
print_r($chars);
echo PHP_EOL;

// Example 6: Split with chunk size
echo "6. Split with Custom Chunk Size:" . PHP_EOL;
$text = "ABCDEFGHIJ";
$chunks = str_split($text, 3);  // Split into chunks of 3
echo "Text: '$text'" . PHP_EOL;
echo "Chunks of 3:" . PHP_EOL;
print_r($chunks);
echo PHP_EOL;

// Example 7: Chunk split (add separator at intervals)
echo "7. Chunk Split (Add Separator):" . PHP_EOL;
$creditCard = "1234567890123456";
$formatted = chunk_split($creditCard, 4, ' ');
echo "Original: '$creditCard'" . PHP_EOL;
echo "Formatted: '$formatted'" . PHP_EOL;
echo PHP_EOL;

// Example 8: Splitting by multiple delimiters
echo "8. Split by Multiple Delimiters (using preg_split):" . PHP_EOL;
$text = "apple,banana;cherry:date orange";
$items = preg_split('/[,;: ]+/', $text);
echo "Text: '$text'" . PHP_EOL;
echo "Split by [,;: ]:" . PHP_EOL;
print_r($items);
echo PHP_EOL;

// Example 9: Practical example - CSV parsing
echo "9. Practical Example - CSV Parsing:" . PHP_EOL;

$csvLine = "John Doe,john@example.com,30,New York";
$userData = explode(',', $csvLine);

list($name, $email, $age, $city) = $userData;

echo "CSV: $csvLine" . PHP_EOL;
echo "Parsed:" . PHP_EOL;
echo "  Name: $name" . PHP_EOL;
echo "  Email: $email" . PHP_EOL;
echo "  Age: $age" . PHP_EOL;
echo "  City: $city" . PHP_EOL;
echo PHP_EOL;

// Example 10: Practical example - Tag processing
echo "10. Practical Example - Tag Processing:" . PHP_EOL;

function parseTags(string $tagString): array
{
    // Split by comma
    $tags = explode(',', $tagString);

    // Trim whitespace from each tag
    $tags = array_map('trim', $tags);

    // Remove empty tags
    $tags = array_filter($tags);

    // Convert to lowercase
    $tags = array_map('strtolower', $tags);

    return $tags;
}

$input = " PHP, Laravel  ,, Symfony, Database , ";
$cleanTags = parseTags($input);
echo "Input: '$input'" . PHP_EOL;
echo "Clean tags: " . implode(', ', $cleanTags) . PHP_EOL;
echo PHP_EOL;

// Example 11: Practical example - URL path parsing
echo "11. Practical Example - URL Path Parsing:" . PHP_EOL;

function parseUrlPath(string $path): array
{
    // Remove leading/trailing slashes
    $path = trim($path, '/');

    // Split by slash
    return explode('/', $path);
}

$paths = [
    '/users/123/profile',
    'products/category/electronics/',
    'api/v1/users'
];

foreach ($paths as $path) {
    $segments = parseUrlPath($path);
    echo "Path: '$path'" . PHP_EOL;
    echo "  Segments: " . implode(' → ', $segments) . PHP_EOL;
}
echo PHP_EOL;

// Example 12: Practical example - Building breadcrumbs
echo "12. Practical Example - Building Breadcrumbs:" . PHP_EOL;

function buildBreadcrumbs(string $path): string
{
    $segments = parseUrlPath($path);
    $breadcrumbs = [];

    foreach ($segments as $index => $segment) {
        $label = ucwords(str_replace(['-', '_'], ' ', $segment));
        $breadcrumbs[] = $label;
    }

    return implode(' > ', $breadcrumbs);
}

$url = '/blog/php-tutorials/string-manipulation';
echo "URL: $url" . PHP_EOL;
echo "Breadcrumbs: " . buildBreadcrumbs($url) . PHP_EOL;
echo PHP_EOL;

// Example 13: Practical example - Name formatting
echo "13. Practical Example - Name Formatting:" . PHP_EOL;

function formatFullName(string $firstName, string $middleName, string $lastName): string
{
    $parts = array_filter([$firstName, $middleName, $lastName]);
    return implode(' ', $parts);
}

echo formatFullName('John', 'Michael', 'Doe') . PHP_EOL;
echo formatFullName('Jane', '', 'Smith') . PHP_EOL;  // No middle name
echo formatFullName('Bob', 'A.', 'Wilson') . PHP_EOL;
echo PHP_EOL;

// Example 14: Practical example - SQL IN clause builder
echo "14. Practical Example - SQL IN Clause Builder:" . PHP_EOL;

function buildInClause(array $values): string
{
    // Quote each value
    $quoted = array_map(fn($v) => "'$v'", $values);

    // Join with comma
    return '(' . implode(', ', $quoted) . ')';
}

$ids = [1, 5, 10, 15, 20];
$inClause = buildInClause($ids);
echo "Array: " . json_encode($ids) . PHP_EOL;
echo "SQL: SELECT * FROM users WHERE id IN $inClause" . PHP_EOL;
echo PHP_EOL;

// Example 15: Practical example - Word wrapping
echo "15. Practical Example - Word Wrapping:" . PHP_EOL;

$longText = "This is a very long line of text that needs to be wrapped to fit within a certain width for better readability.";

// Wrap at 40 characters
$wrapped = wordwrap($longText, 40, "\n");
echo "Original:" . PHP_EOL;
echo $longText . PHP_EOL . PHP_EOL;
echo "Wrapped at 40 chars:" . PHP_EOL;
echo $wrapped . PHP_EOL;
echo PHP_EOL;

// Example 16: Practical example - Building query strings
echo "16. Practical Example - Building Query Strings:" . PHP_EOL;

function buildQueryString(array $params): string
{
    $pairs = [];

    foreach ($params as $key => $value) {
        $pairs[] = urlencode((string)$key) . '=' . urlencode((string)$value);
    }

    return implode('&', $pairs);
}

$params = [
    'search' => 'PHP tutorials',
    'page' => 2,
    'sort' => 'date'
];

$queryString = buildQueryString($params);
echo "Parameters: " . json_encode($params) . PHP_EOL;
echo "Query string: ?$queryString" . PHP_EOL;
echo "Full URL: https://example.com/search?$queryString" . PHP_EOL;
