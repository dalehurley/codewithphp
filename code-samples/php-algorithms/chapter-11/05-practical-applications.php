<?php

/**
 * Practical Applications of Linear Search
 *
 * Real-world use cases including log file searching, filtering,
 * autocomplete, and data validation.
 *
 * @package CodeWithPHP\Algorithms\Chapter11
 */

declare(strict_types=1);

/**
 * Simple grep-like implementation
 */
class SimpleGrep
{
    /**
     * Search for pattern in file
     *
     * @param string $pattern Pattern to search for
     * @param array $lines Lines to search through
     * @param bool $caseInsensitive Case insensitive search
     * @return array Matching lines with line numbers
     */
    public function search(string $pattern, array $lines, bool $caseInsensitive = false): array
    {
        $results = [];
        $lineNumber = 0;

        foreach ($lines as $line) {
            $lineNumber++;

            $found = $caseInsensitive
                ? stripos($line, $pattern) !== false
                : strpos($line, $pattern) !== false;

            if ($found) {
                $results[] = [
                    'line' => $lineNumber,
                    'content' => trim($line)
                ];
            }
        }

        return $results;
    }
}

/**
 * Autocomplete engine
 */
class AutocompleteEngine
{
    private array $dictionary;

    public function __construct(array $words)
    {
        sort($words);
        $this->dictionary = $words;
    }

    /**
     * Get suggestions for prefix
     *
     * @param string $prefix Prefix to match
     * @param int $maxResults Maximum number of results
     * @return array Matching words
     */
    public function suggest(string $prefix, int $maxResults = 5): array
    {
        $prefix = strtolower($prefix);
        $results = [];

        foreach ($this->dictionary as $word) {
            if (str_starts_with(strtolower($word), $prefix)) {
                $results[] = $word;

                if (count($results) >= $maxResults) {
                    break;
                }
            }
        }

        return $results;
    }
}

/**
 * Data validator
 */
class DataValidator
{
    /**
     * Find duplicate values
     *
     * @param array $arr Array to check
     * @return array Duplicate values found
     */
    public function findDuplicates(array $arr): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($arr as $value) {
            if (in_array($value, $seen, true)) {
                if (!in_array($value, $duplicates, true)) {
                    $duplicates[] = $value;
                }
            } else {
                $seen[] = $value;
            }
        }

        return $duplicates;
    }

    /**
     * Find missing values in sequence
     *
     * @param array $arr Array to check
     * @param int $start Start of sequence
     * @param int $end End of sequence
     * @return array Missing values
     */
    public function findMissing(array $arr, int $start, int $end): array
    {
        $missing = [];

        for ($i = $start; $i <= $end; $i++) {
            if (!in_array($i, $arr, true)) {
                $missing[] = $i;
            }
        }

        return $missing;
    }

    /**
     * Validate required fields
     *
     * @param array $data Data to validate
     * @param array $required Required fields
     * @return array Missing fields
     */
    public function validateRequired(array $data, array $required): array
    {
        $missing = [];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }
}

/**
 * Text highlighter
 */
class TextHighlighter
{
    /**
     * Highlight all occurrences of pattern
     *
     * @param string $text Text to highlight in
     * @param string $pattern Pattern to highlight
     * @param string $highlightClass CSS class for highlighting
     * @return string HTML with highlighted text
     */
    public function highlight(string $text, string $pattern, string $highlightClass = 'highlight'): string
    {
        if (empty($pattern)) {
            return $text;
        }

        $matches = $this->findAllMatches($text, $pattern);

        if (empty($matches)) {
            return $text;
        }

        $highlighted = '';
        $lastPos = 0;
        $patternLen = strlen($pattern);

        foreach ($matches as $pos) {
            // Add text before match
            $highlighted .= substr($text, $lastPos, $pos - $lastPos);

            // Add highlighted match
            $highlighted .= '<span class="' . $highlightClass . '">' .
                          substr($text, $pos, $patternLen) .
                          '</span>';

            $lastPos = $pos + $patternLen;
        }

        // Add remaining text
        $highlighted .= substr($text, $lastPos);

        return $highlighted;
    }

    private function findAllMatches(string $text, string $pattern): array
    {
        $matches = [];
        $pos = 0;

        while (($pos = strpos($text, $pattern, $pos)) !== false) {
            $matches[] = $pos;
            $pos++;
        }

        return $matches;
    }
}

/**
 * Filter class for data filtering
 */
class DataFilter
{
    /**
     * Filter array by multiple conditions
     *
     * @param array $data Data to filter
     * @param array $filters Array of filter functions
     * @return array Filtered data
     */
    public function filter(array $data, array $filters): array
    {
        $results = $data;

        foreach ($filters as $filter) {
            $results = array_filter($results, $filter);
        }

        return array_values($results);
    }

    /**
     * Find items within range
     *
     * @param array $numbers Array of numbers
     * @param float $min Minimum value (inclusive)
     * @param float $max Maximum value (inclusive)
     * @return array Filtered numbers
     */
    public function inRange(array $numbers, float $min, float $max): array
    {
        $results = [];

        foreach ($numbers as $number) {
            if ($number >= $min && $number <= $max) {
                $results[] = $number;
            }
        }

        return $results;
    }
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "=== Practical Applications ===\n\n";

// Example 1: Grep-like search
echo "Example 1: Search in log file\n";
echo str_repeat('-', 50) . "\n";
$logLines = [
    '[2025-01-15 10:23:45] INFO: Application started',
    '[2025-01-15 10:23:46] DEBUG: Loading configuration',
    '[2025-01-15 10:23:47] ERROR: Database connection failed',
    '[2025-01-15 10:23:48] WARN: Retrying connection',
    '[2025-01-15 10:23:49] INFO: Connection established',
    '[2025-01-15 10:23:50] ERROR: Query execution failed',
];

$grep = new SimpleGrep();
$errors = $grep->search('ERROR', $logLines);

echo "Searching for 'ERROR' in logs:\n";
foreach ($errors as $error) {
    echo "Line {$error['line']}: {$error['content']}\n";
}
echo "\n";

// Example 2: Autocomplete
echo "Example 2: Autocomplete suggestions\n";
echo str_repeat('-', 50) . "\n";
$dictionary = ['apple', 'application', 'apply', 'banana', 'band', 'bandana', 'cat', 'catch'];
$autocomplete = new AutocompleteEngine($dictionary);

$prefixes = ['app', 'ban', 'ca'];
foreach ($prefixes as $prefix) {
    $suggestions = $autocomplete->suggest($prefix, 3);
    echo "Prefix '$prefix': " . implode(', ', $suggestions) . "\n";
}
echo "\n";

// Example 3: Find duplicates
echo "Example 3: Find duplicate values\n";
echo str_repeat('-', 50) . "\n";
$validator = new DataValidator();
$data = [1, 2, 3, 2, 4, 5, 3, 6, 1];
$duplicates = $validator->findDuplicates($data);
echo "Array: [" . implode(', ', $data) . "]\n";
echo "Duplicates: [" . implode(', ', $duplicates) . "]\n";
echo "\n";

// Example 4: Find missing values
echo "Example 4: Find missing values in sequence\n";
echo str_repeat('-', 50) . "\n";
$sequence = [1, 2, 4, 5, 7, 9, 10];
$missing = $validator->findMissing($sequence, 1, 10);
echo "Sequence: [" . implode(', ', $sequence) . "]\n";
echo "Missing: [" . implode(', ', $missing) . "]\n";
echo "\n";

// Example 5: Validate required fields
echo "Example 5: Validate required fields\n";
echo str_repeat('-', 50) . "\n";
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => null
];
$required = ['name', 'email', 'age', 'phone'];
$missingFields = $validator->validateRequired($userData, $required);
echo "Validating user data...\n";
if (empty($missingFields)) {
    echo "✓ All required fields present\n";
} else {
    echo "✗ Missing fields: " . implode(', ', $missingFields) . "\n";
}
echo "\n";

// Example 6: Text highlighting
echo "Example 6: Highlight search terms\n";
echo str_repeat('-', 50) . "\n";
$highlighter = new TextHighlighter();
$text = "The quick brown fox jumps over the lazy dog";
$highlighted = $highlighter->highlight($text, "the");
echo "Original: $text\n";
echo "Highlighted: $highlighted\n";
echo "\n";

// Example 7: Data filtering
echo "Example 7: Filter data with multiple conditions\n";
echo str_repeat('-', 50) . "\n";
$filter = new DataFilter();
$numbers = [5, 12, 18, 25, 30, 42, 55, 60, 75, 88];
$filtered = $filter->inRange($numbers, 20, 60);
echo "Original: [" . implode(', ', $numbers) . "]\n";
echo "Range [20-60]: [" . implode(', ', $filtered) . "]\n";
echo "\n";

// Example 8: Complex filtering
echo "Example 8: Filter products by multiple criteria\n";
echo str_repeat('-', 50) . "\n";
$products = [
    ['name' => 'Laptop', 'price' => 1200, 'inStock' => true],
    ['name' => 'Mouse', 'price' => 25, 'inStock' => true],
    ['name' => 'Keyboard', 'price' => 75, 'inStock' => false],
    ['name' => 'Monitor', 'price' => 300, 'inStock' => true],
    ['name' => 'Webcam', 'price' => 80, 'inStock' => true],
];

$filters = [
    fn($p) => $p['inStock'] === true,
    fn($p) => $p['price'] < 500
];

$filtered = $filter->filter($products, $filters);
echo "Available products under $500:\n";
foreach ($filtered as $product) {
    printf("  - %s: $%.2f\n", $product['name'], $product['price']);
}
echo "\n";

// Example 9: Email validation and extraction
echo "Example 9: Find valid emails in text\n";
echo str_repeat('-', 50) . "\n";
$text = "Contact us at support@example.com or sales@company.org for more information. Invalid: notanemail";
$emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
preg_match_all($emailPattern, $text, $matches);
echo "Text: $text\n";
echo "Valid emails found: " . implode(', ', $matches[0]) . "\n";
echo "\n";

// Example 10: Performance monitoring
echo "Example 10: Performance comparison - Different search scenarios\n";
echo str_repeat('-', 50) . "\n";

$testData = range(1, 10000);
$iterations = 1000;

// Best case: element at beginning
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    foreach ($testData as $val) {
        if ($val === 1) break;
    }
}
$bestCase = microtime(true) - $start;

// Average case: element in middle
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    foreach ($testData as $val) {
        if ($val === 5000) break;
    }
}
$avgCase = microtime(true) - $start;

// Worst case: element at end
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    foreach ($testData as $val) {
        if ($val === 10000) break;
    }
}
$worstCase = microtime(true) - $start;

printf("Best case (element at start):   %.6f sec\n", $bestCase);
printf("Average case (element in mid):  %.6f sec (%.2fx slower)\n",
       $avgCase, $avgCase / $bestCase);
printf("Worst case (element at end):    %.6f sec (%.2fx slower)\n",
       $worstCase, $worstCase / $bestCase);
