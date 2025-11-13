---
title: "14: String Search Algorithms"
description: "Implement pattern matching with naive search, KMP algorithm, and Boyer-Moore. Build a simple grep-like tool."
series: "php-algorithms"
chapter: 14
order: 14
difficulty: "Advanced"
prerequisites:
  - "Understanding of strings and arrays"
  - "Completion of Chapters 11-13"
  - "Familiarity with pattern matching"
---

# String Search Algorithms

String searching (or pattern matching) is fundamental to text processing. Whether you're implementing search in a text editor, filtering log files, or building a search engine, you need efficient string search algorithms. In this chapter, we'll explore multiple approaches from simple to sophisticated.

## The String Search Problem

**Given:**
- A **text** string (the haystack)
- A **pattern** string (the needle)

**Find:** All occurrences of pattern in text

**Example:**
```
Text:    "hello world hello"
Pattern: "hello"
Matches: positions 0 and 12
```

## Naive String Search

The simplest approach: check every position in the text.

### Implementation

```php
function naiveSearch(string $text, string $pattern): array
{
    $matches = [];
    $textLen = strlen($text);
    $patternLen = strlen($pattern);

    // Check each position in text
    for ($i = 0; $i <= $textLen - $patternLen; $i++) {
        $j = 0;

        // Check if pattern matches starting at position i
        while ($j < $patternLen && $text[$i + $j] === $pattern[$j]) {
            $j++;
        }

        // If we matched entire pattern
        if ($j === $patternLen) {
            $matches[] = $i;
        }
    }

    return $matches;
}

$text = "AABAACAADAABAABA";
$pattern = "AABA";
print_r(naiveSearch($text, $pattern));
// Output: [0, 9, 12]
```

### Visualization

```php
function naiveSearchVisualized(string $text, string $pattern): array
{
    $matches = [];
    $textLen = strlen($text);
    $patternLen = strlen($pattern);

    for ($i = 0; $i <= $textLen - $patternLen; $i++) {
        echo "Position $i:\n";
        echo "  Text:    " . substr($text, $i, $patternLen) . "\n";
        echo "  Pattern: $pattern\n";

        $j = 0;
        while ($j < $patternLen && $text[$i + $j] === $pattern[$j]) {
            $j++;
        }

        if ($j === $patternLen) {
            echo "  ✓ Match found!\n\n";
            $matches[] = $i;
        } else {
            echo "  ✗ No match\n\n";
        }
    }

    return $matches;
}
```

### Complexity

- **Time:** O(n×m) where n = text length, m = pattern length
- **Space:** O(1)
- **Problem:** Inefficient for long texts or patterns

## Knuth-Morris-Pratt (KMP) Algorithm

KMP improves on naive search by **never re-examining text characters**. It uses a **prefix table** to skip unnecessary comparisons.

### The Prefix Table

The prefix table (also called LPS - Longest Proper Prefix which is also Suffix) tells us where to continue matching after a mismatch.

```php
function computePrefixTable(string $pattern): array
{
    $m = strlen($pattern);
    $lps = array_fill(0, $m, 0);
    $len = 0; // Length of previous longest prefix suffix
    $i = 1;

    while ($i < $m) {
        if ($pattern[$i] === $pattern[$len]) {
            $len++;
            $lps[$i] = $len;
            $i++;
        } else {
            if ($len !== 0) {
                $len = $lps[$len - 1];
            } else {
                $lps[$i] = 0;
                $i++;
            }
        }
    }

    return $lps;
}

// Example
$pattern = "AABAAB";
print_r(computePrefixTable($pattern));
// [0, 1, 0, 1, 2, 3]
```

### KMP Implementation

```php
function kmpSearch(string $text, string $pattern): array
{
    $matches = [];
    $n = strlen($text);
    $m = strlen($pattern);

    if ($m === 0) return $matches;

    // Compute prefix table
    $lps = computePrefixTable($pattern);

    $i = 0; // Index for text
    $j = 0; // Index for pattern

    while ($i < $n) {
        if ($pattern[$j] === $text[$i]) {
            $i++;
            $j++;
        }

        if ($j === $m) {
            // Found match
            $matches[] = $i - $j;
            $j = $lps[$j - 1];
        } elseif ($i < $n && $pattern[$j] !== $text[$i]) {
            // Mismatch after j matches
            if ($j !== 0) {
                $j = $lps[$j - 1];
            } else {
                $i++;
            }
        }
    }

    return $matches;
}

$text = "AABAACAADAABAABA";
$pattern = "AABA";
print_r(kmpSearch($text, $pattern));
// Output: [0, 9, 12]
```

### Complexity

- **Preprocessing:** O(m) to build prefix table
- **Searching:** O(n)
- **Total:** O(n + m)
- **Space:** O(m) for prefix table

**Advantage:** Never backtracks in the text!

## Boyer-Moore Algorithm

Boyer-Moore scans the pattern **right-to-left** and uses two heuristics to skip sections of text.

### Bad Character Rule

```php
function computeBadCharTable(string $pattern): array
{
    $m = strlen($pattern);
    $badChar = [];

    // Initialize all characters to -1
    for ($i = 0; $i < 256; $i++) {
        $badChar[chr($i)] = -1;
    }

    // Fill with last occurrence of each character
    for ($i = 0; $i < $m; $i++) {
        $badChar[$pattern[$i]] = $i;
    }

    return $badChar;
}
```

### Boyer-Moore Implementation (Simplified)

```php
function boyerMooreSearch(string $text, string $pattern): array
{
    $matches = [];
    $n = strlen($text);
    $m = strlen($pattern);

    if ($m === 0) return $matches;

    $badChar = computeBadCharTable($pattern);

    $s = 0; // Shift of pattern relative to text

    while ($s <= $n - $m) {
        $j = $m - 1;

        // Keep reducing index j while characters match
        while ($j >= 0 && $pattern[$j] === $text[$s + $j]) {
            $j--;
        }

        if ($j < 0) {
            // Pattern found
            $matches[] = $s;

            // Shift pattern to align next character in text with its last occurrence in pattern
            $s += ($s + $m < $n) ? $m - ($badChar[$text[$s + $m]] ?? -1) : 1;
        } else {
            // Shift pattern to align bad character in text with its last occurrence in pattern
            $s += max(1, $j - ($badChar[$text[$s + $j]] ?? -1));
        }
    }

    return $matches;
}

$text = "AABAACAADAABAABA";
$pattern = "AABA";
print_r(boyerMooreSearch($text, $pattern));
// Output: [0, 9, 12]
```

### Complexity

- **Preprocessing:** O(m + σ) where σ is alphabet size
- **Searching:** O(n/m) best case, O(n×m) worst case
- **Average:** O(n) and often faster than KMP

**Advantage:** Can skip many characters, especially with large alphabets!

## Rabin-Karp Algorithm

Uses **hashing** to find pattern matches efficiently.

### Rolling Hash

```php
class RabinKarp
{
    private const PRIME = 101;
    private const BASE = 256;

    public function search(string $text, string $pattern): array
    {
        $matches = [];
        $n = strlen($text);
        $m = strlen($pattern);

        if ($m > $n) return $matches;

        // Calculate hash values
        $patternHash = $this->hash($pattern, $m);
        $textHash = $this->hash($text, $m);

        // Calculate h = BASE^(m-1) % PRIME for removing leading digit
        $h = 1;
        for ($i = 0; $i < $m - 1; $i++) {
            $h = ($h * self::BASE) % self::PRIME;
        }

        // Slide pattern over text
        for ($i = 0; $i <= $n - $m; $i++) {
            // Check hash values
            if ($patternHash === $textHash) {
                // Hash match - verify actual string
                if (substr($text, $i, $m) === $pattern) {
                    $matches[] = $i;
                }
            }

            // Calculate hash for next window
            if ($i < $n - $m) {
                $textHash = $this->recalculateHash(
                    $text,
                    $i,
                    $i + $m,
                    $textHash,
                    $h
                );
            }
        }

        return $matches;
    }

    private function hash(string $str, int $length): int
    {
        $hash = 0;

        for ($i = 0; $i < $length; $i++) {
            $hash = (self::BASE * $hash + ord($str[$i])) % self::PRIME;
        }

        return $hash;
    }

    private function recalculateHash(
        string $text,
        int $oldIndex,
        int $newIndex,
        int $oldHash,
        int $h
    ): int {
        // Remove leading character
        $newHash = $oldHash - ord($text[$oldIndex]) * $h;

        // Add trailing character
        $newHash = ($newHash * self::BASE + ord($text[$newIndex])) % self::PRIME;

        // Make sure hash is positive
        if ($newHash < 0) {
            $newHash += self::PRIME;
        }

        return $newHash;
    }
}

// Usage
$rk = new RabinKarp();
$matches = $rk->search("AABAACAADAABAABA", "AABA");
print_r($matches); // [0, 9, 12]
```

### Complexity

- **Average case:** O(n + m)
- **Worst case:** O(n×m) (many hash collisions)
- **Best for:** Multiple pattern searches

## PHP Built-in Functions

PHP provides optimized string search functions:

### strpos() - Find First Occurrence

```php
$text = "hello world hello";
$pos = strpos($text, "world");
echo $pos; // 6

// Case-insensitive
$pos = stripos($text, "WORLD");
echo $pos; // 6
```

### str_contains() - Check if Contains (PHP 8+)

```php
if (str_contains($text, "world")) {
    echo "Found!";
}
```

### preg_match() - Regular Expression

```php
$text = "My email is john@example.com";

if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $text, $matches)) {
    echo "Email found: " . $matches[0];
}
```

### preg_match_all() - Find All Matches

```php
$text = "Contact us at info@example.com or support@example.com";
$pattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';

preg_match_all($pattern, $text, $matches);
print_r($matches[0]);
// ['info@example.com', 'support@example.com']
```

## Real-World Applications

### 1. Simple Grep Implementation

```php
class SimpleGrep
{
    public function search(string $filename, string $pattern, bool $caseInsensitive = false): array
    {
        $results = [];
        $handle = fopen($filename, 'r');

        if (!$handle) {
            throw new RuntimeException("Cannot open file: $filename");
        }

        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
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

        fclose($handle);
        return $results;
    }

    public function searchRegex(string $filename, string $pattern): array
    {
        $results = [];
        $handle = fopen($filename, 'r');

        if (!$handle) {
            throw new RuntimeException("Cannot open file: $filename");
        }

        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            if (preg_match($pattern, $line, $matches)) {
                $results[] = [
                    'line' => $lineNumber,
                    'content' => trim($line),
                    'matches' => $matches
                ];
            }
        }

        fclose($handle);
        return $results;
    }
}

// Usage
$grep = new SimpleGrep();
$results = $grep->search('access.log', 'ERROR');
$emailResults = $grep->searchRegex('users.txt', '/[a-z]+@[a-z]+\.[a-z]+/i');
```

### 2. Text Highlighter

```php
function highlightPattern(string $text, string $pattern): string
{
    $matches = naiveSearch($text, $pattern);

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
        $highlighted .= '<mark>' . substr($text, $pos, $patternLen) . '</mark>';

        $lastPos = $pos + $patternLen;
    }

    // Add remaining text
    $highlighted .= substr($text, $lastPos);

    return $highlighted;
}

$text = "The quick brown fox jumps over the lazy dog";
echo highlightPattern($text, "the");
// The quick brown fox jumps over <mark>the</mark> lazy dog
```

### 3. URL Extractor

```php
function extractURLs(string $text): array
{
    $pattern = '/https?:\/\/[^\s<>"{}|\\^`\[\]]+/i';
    preg_match_all($pattern, $text, $matches);
    return $matches[0];
}

$text = "Visit https://example.com and http://test.org for more info";
print_r(extractURLs($text));
// ['https://example.com', 'http://test.org']
```

### 4. Word Counter

```php
function countWordOccurrences(string $text, string $word): int
{
    $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
    preg_match_all($pattern, $text, $matches);
    return count($matches[0]);
}

$text = "The cat in the hat sat on the mat";
echo countWordOccurrences($text, "the"); // 3
```

### 5. Plagiarism Detector (Simplified)

```php
class PlagiarismDetector
{
    public function findCommonPhrases(string $text1, string $text2, int $minLength = 5): array
    {
        $common = [];
        $words1 = explode(' ', $text1);
        $words2 = explode(' ', $text2);

        for ($i = 0; $i < count($words1) - $minLength + 1; $i++) {
            $phrase = implode(' ', array_slice($words1, $i, $minLength));

            if (str_contains($text2, $phrase)) {
                $common[] = $phrase;
            }
        }

        return array_unique($common);
    }

    public function calculateSimilarity(string $text1, string $text2): float
    {
        $common = $this->findCommonPhrases($text1, $text2, 3);
        $totalPhrases = max(str_word_count($text1), str_word_count($text2));

        return count($common) / $totalPhrases;
    }
}
```

## Comparing String Search Algorithms

```php
require_once 'Benchmark.php';

$bench = new Benchmark();

// Generate large text
$text = str_repeat("AABAACAADAABAABAABA", 1000);
$pattern = "AABA";

$bench->compare([
    'Naive Search' => fn() => naiveSearch($text, $pattern),
    'KMP Search' => fn() => kmpSearch($text, $pattern),
    'Boyer-Moore' => fn() => boyerMooreSearch($text, $pattern),
    'Rabin-Karp' => fn() => (new RabinKarp())->search($text, $pattern),
    'PHP strpos()' => function() use ($text, $pattern) {
        $matches = [];
        $pos = 0;
        while (($pos = strpos($text, $pattern, $pos)) !== false) {
            $matches[] = $pos;
            $pos++;
        }
        return $matches;
    },
], null, iterations: 100);
```

## Edge Cases and Special Scenarios

### Handling Edge Cases

```php
class StringSearchEdgeCases
{
    /**
     * Handle empty strings
     */
    public function handleEmptyStrings(string $text, string $pattern): array
    {
        // Empty pattern - returns all positions
        if ($pattern === '') {
            return range(0, strlen($text));
        }

        // Empty text - no matches
        if ($text === '') {
            return [];
        }

        // Pattern longer than text - no matches
        if (strlen($pattern) > strlen($text)) {
            return [];
        }

        return naiveSearch($text, $pattern);
    }

    /**
     * Case-insensitive search
     */
    public function caseInsensitiveSearch(string $text, string $pattern): array
    {
        $lowerText = strtolower($text);
        $lowerPattern = strtolower($pattern);

        return naiveSearch($lowerText, $lowerPattern);
    }

    /**
     * Unicode-aware search
     */
    public function unicodeSearch(string $text, string $pattern): array
    {
        $matches = [];
        $textLen = mb_strlen($text);
        $patternLen = mb_strlen($pattern);

        for ($i = 0; $i <= $textLen - $patternLen; $i++) {
            $substring = mb_substr($text, $i, $patternLen);

            if ($substring === $pattern) {
                $matches[] = $i;
            }
        }

        return $matches;
    }

    /**
     * Search with special characters
     */
    public function searchWithSpecialChars(string $text, string $pattern): array
    {
        // Escape special regex characters if using regex
        $escapedPattern = preg_quote($pattern, '/');

        preg_match_all('/' . $escapedPattern . '/', $text, $matches, PREG_OFFSET_CAPTURE);

        return array_column($matches[0], 1);
    }

    /**
     * Overlapping matches
     */
    public function findOverlapping(string $text, string $pattern): array
    {
        $matches = [];
        $textLen = strlen($text);
        $patternLen = strlen($pattern);

        for ($i = 0; $i <= $textLen - $patternLen; $i++) {
            if (substr($text, $i, $patternLen) === $pattern) {
                $matches[] = $i;
                // Don't skip - allow overlapping
            }
        }

        return $matches;
    }

    /**
     * Whitespace handling
     */
    public function searchIgnoringWhitespace(string $text, string $pattern): array
    {
        // Remove all whitespace
        $cleanText = preg_replace('/\s+/', '', $text);
        $cleanPattern = preg_replace('/\s+/', '', $pattern);

        $matches = naiveSearch($cleanText, $cleanPattern);

        // Map back to original positions (approximation)
        return $matches;
    }

    /**
     * Null byte handling
     */
    public function binarySafeSearch(string $text, string $pattern): array
    {
        $matches = [];
        $textLen = strlen($text);
        $patternLen = strlen($pattern);

        for ($i = 0; $i <= $textLen - $patternLen; $i++) {
            // Use binary-safe comparison
            if (substr_compare($text, $pattern, $i, $patternLen, false) === 0) {
                $matches[] = $i;
            }
        }

        return $matches;
    }
}

// Test edge cases
$edgeCases = new StringSearchEdgeCases();

// Empty strings
print_r($edgeCases->handleEmptyStrings("hello", "")); // All positions
print_r($edgeCases->handleEmptyStrings("", "hello")); // Empty array

// Unicode
print_r($edgeCases->unicodeSearch("Hello 世界", "世界")); // [6]

// Overlapping
print_r($edgeCases->findOverlapping("AAAA", "AA")); // [0, 1, 2]
```

### Performance Optimization for Edge Cases

```php
class OptimizedStringSearch
{
    /**
     * Quick fail for impossible matches
     */
    public function optimizedSearch(string $text, string $pattern): array
    {
        $textLen = strlen($text);
        $patternLen = strlen($pattern);

        // Quick return for edge cases
        if ($patternLen === 0 || $patternLen > $textLen) {
            return [];
        }

        // Single character optimization
        if ($patternLen === 1) {
            return $this->searchSingleChar($text, $pattern[0]);
        }

        // Use appropriate algorithm based on lengths
        if ($patternLen <= 3) {
            return naiveSearch($text, $pattern);
        } elseif ($patternLen < 100) {
            return boyerMooreSearch($text, $pattern);
        } else {
            return kmpSearch($text, $pattern);
        }
    }

    private function searchSingleChar(string $text, string $char): array
    {
        $matches = [];
        $pos = 0;

        while (($pos = strpos($text, $char, $pos)) !== false) {
            $matches[] = $pos;
            $pos++;
        }

        return $matches;
    }
}
```

## Performance Benchmarks with Edge Cases

```php
class StringSearchBenchmark
{
    public function comprehensiveBenchmark(): void
    {
        echo "=== String Search Performance Comparison ===\n\n";

        $testCases = [
            'Small text, small pattern' => [
                'text' => str_repeat('abc', 100),
                'pattern' => 'abc'
            ],
            'Large text, small pattern' => [
                'text' => str_repeat('lorem ipsum dolor sit amet ', 10000),
                'pattern' => 'dolor'
            ],
            'Large text, large pattern' => [
                'text' => file_get_contents('/usr/share/dict/words'),
                'pattern' => 'internationalization'
            ],
            'Worst case (many mismatches)' => [
                'text' => str_repeat('a', 10000) . 'b',
                'pattern' => str_repeat('a', 10) . 'b'
            ],
            'Best case (early match)' => [
                'text' => 'target' . str_repeat('x', 10000),
                'pattern' => 'target'
            ],
            'Unicode text' => [
                'text' => str_repeat('Hello 世界 ', 1000),
                'pattern' => '世界'
            ]
        ];

        foreach ($testCases as $name => $data) {
            echo "\n$name:\n";
            echo str_repeat('-', 60) . "\n";

            $text = $data['text'];
            $pattern = $data['pattern'];
            $iterations = 1000;

            // Naive
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                naiveSearch($text, $pattern);
            }
            $naiveTime = microtime(true) - $start;

            // KMP
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                kmpSearch($text, $pattern);
            }
            $kmpTime = microtime(true) - $start;

            // Boyer-Moore
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                boyerMooreSearch($text, $pattern);
            }
            $bmTime = microtime(true) - $start;

            // Rabin-Karp
            $rk = new RabinKarp();
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                $rk->search($text, $pattern);
            }
            $rkTime = microtime(true) - $start;

            // PHP native
            $start = microtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                $pos = 0;
                $matches = [];
                while (($pos = strpos($text, $pattern, $pos)) !== false) {
                    $matches[] = $pos;
                    $pos++;
                }
            }
            $nativeTime = microtime(true) - $start;

            printf("Naive:       %.6f sec\n", $naiveTime);
            printf("KMP:         %.6f sec (%.2fx vs Naive)\n",
                $kmpTime, $naiveTime / $kmpTime);
            printf("Boyer-Moore: %.6f sec (%.2fx vs Naive)\n",
                $bmTime, $naiveTime / $bmTime);
            printf("Rabin-Karp:  %.6f sec (%.2fx vs Naive)\n",
                $rkTime, $naiveTime / $rkTime);
            printf("PHP Native:  %.6f sec (%.2fx vs Naive)\n",
                $nativeTime, $naiveTime / $nativeTime);
        }
    }

    public function memoryBenchmark(): void
    {
        echo "\n\n=== Memory Usage Comparison ===\n\n";

        $text = str_repeat('lorem ipsum ', 100000);
        $pattern = 'ipsum';

        $algorithms = [
            'Naive' => fn() => naiveSearch($text, $pattern),
            'KMP' => fn() => kmpSearch($text, $pattern),
            'Boyer-Moore' => fn() => boyerMooreSearch($text, $pattern),
            'Rabin-Karp' => fn() => (new RabinKarp())->search($text, $pattern),
        ];

        foreach ($algorithms as $name => $algorithm) {
            $memBefore = memory_get_usage();
            $algorithm();
            $memAfter = memory_get_usage();

            $memUsed = $memAfter - $memBefore;
            printf("%s: %s\n", str_pad($name, 15), $this->formatBytes($memUsed));
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return "$bytes B";
        if ($bytes < 1048576) return round($bytes / 1024, 2) . " KB";
        return round($bytes / 1048576, 2) . " MB";
    }
}

$benchmark = new StringSearchBenchmark();
$benchmark->comprehensiveBenchmark();
$benchmark->memoryBenchmark();
```

## Security Considerations

### Timing Attacks on String Matching

```php
class SecureStringSearch
{
    /**
     * VULNERABLE: Early termination leaks information
     */
    public function insecurePasswordMatch(string $input, string $stored): bool
    {
        return $input === $stored; // Timing attack possible!
    }

    /**
     * SECURE: Constant-time string comparison
     */
    public function securePasswordMatch(string $input, string $stored): bool
    {
        return hash_equals($stored, $input);
    }

    /**
     * VULNERABLE: Pattern search reveals information via timing
     */
    public function insecureTokenSearch(array $validTokens, string $userToken): bool
    {
        foreach ($validTokens as $token) {
            if (strpos($token, $userToken) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * SECURE: Constant-time token validation
     */
    public function secureTokenSearch(array $validTokens, string $userToken): bool
    {
        $found = 0;

        foreach ($validTokens as $token) {
            if (hash_equals($token, $userToken)) {
                $found = 1;
            }
        }

        // Add jitter to obscure timing
        usleep(rand(10, 50));

        return $found === 1;
    }

    /**
     * Protect against ReDoS (Regular Expression Denial of Service)
     */
    public function safeRegexSearch(string $text, string $pattern, int $maxTime = 1000): ?array
    {
        // Validate pattern first
        if (!$this->isPatternSafe($pattern)) {
            throw new Exception("Unsafe regex pattern detected");
        }

        // Set PCRE limits
        ini_set('pcre.backtrack_limit', '100000');
        ini_set('pcre.recursion_limit', '100000');

        $start = microtime(true);

        // Use timeout wrapper
        $matches = [];
        try {
            if (preg_match_all($pattern, $text, $matches) === false) {
                throw new Exception("Regex execution failed");
            }

            $duration = (microtime(true) - $start) * 1000;
            if ($duration > $maxTime) {
                throw new Exception("Regex execution timeout");
            }

            return $matches[0];
        } catch (Exception $e) {
            error_log("Regex error: " . $e->getMessage());
            return null;
        }
    }

    private function isPatternSafe(string $pattern): bool
    {
        // Check for catastrophic backtracking patterns
        $dangerous = [
            '/(\w+\s*)+/', // Nested quantifiers
            '/(a+)+b/',    // Exponential backtracking
            '/(a|a)*b/',   // Overlapping alternation
        ];

        foreach ($dangerous as $danger) {
            if (strpos($pattern, trim($danger, '/')) !== false) {
                return false;
            }
        }

        return true;
    }
}
```

### Input Validation and Sanitization

```php
class SafeStringOperations
{
    /**
     * Sanitize search input
     */
    public function sanitizeSearchInput(string $input): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Limit length
        $input = mb_substr($input, 0, 1000);

        // Remove control characters
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        return $input;
    }

    /**
     * Safe SQL LIKE search
     */
    public function safeLikeSearch(string $userInput): string
    {
        // Escape LIKE wildcards
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $userInput);

        // Also escape for SQL
        $escaped = addslashes($escaped);

        return "%$escaped%";
    }

    /**
     * Rate limit search operations
     */
    private array $searchAttempts = [];

    public function rateLimitedSearch(
        string $clientId,
        string $text,
        string $pattern,
        int $maxSearches = 100
    ): ?array {
        $now = time();

        if (!isset($this->searchAttempts[$clientId])) {
            $this->searchAttempts[$clientId] = ['count' => 0, 'time' => $now];
        }

        $client = &$this->searchAttempts[$clientId];

        if ($now - $client['time'] > 60) {
            $client = ['count' => 0, 'time' => $now];
        }

        if ($client['count'] >= $maxSearches) {
            throw new Exception("Rate limit exceeded");
        }

        $client['count']++;

        return naiveSearch($text, $pattern);
    }
}
```

## Framework Integration Examples

### Laravel Integration

```php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StringSearchService
{
    /**
     * Search with caching
     */
    public function cachedSearch(string $text, string $pattern): array
    {
        $cacheKey = 'search:' . md5($text . $pattern);

        return Cache::remember($cacheKey, 3600, function () use ($text, $pattern) {
            return kmpSearch($text, $pattern);
        });
    }

    /**
     * Full-text search in database
     */
    public function fullTextSearch(string $query, string $table = 'posts'): array
    {
        // Use database full-text search
        $results = DB::table($table)
            ->whereRaw("MATCH(title, content) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->get();

        // Highlight matches
        return $results->map(function ($item) use ($query) {
            $item->highlighted_content = $this->highlight($item->content, $query);
            return $item;
        })->toArray();
    }

    /**
     * Highlight search terms
     */
    public function highlight(string $text, string $pattern): string
    {
        $matches = $this->cachedSearch($text, $pattern);

        if (empty($matches)) {
            return $text;
        }

        $result = '';
        $lastPos = 0;

        foreach ($matches as $pos) {
            $result .= substr($text, $lastPos, $pos - $lastPos);
            $result .= '<mark>' . substr($text, $pos, strlen($pattern)) . '</mark>';
            $lastPos = $pos + strlen($pattern);
        }

        $result .= substr($text, $lastPos);

        return $result;
    }

    /**
     * Autocomplete search
     */
    public function autocomplete(string $prefix, int $limit = 10): array
    {
        return Cache::remember("autocomplete:$prefix", 600, function () use ($prefix, $limit) {
            return DB::table('search_terms')
                ->where('term', 'LIKE', "$prefix%")
                ->orderBy('popularity', 'desc')
                ->limit($limit)
                ->pluck('term')
                ->toArray();
        });
    }

    /**
     * Fuzzy search
     */
    public function fuzzySearch(string $query, int $maxDistance = 2): array
    {
        $terms = DB::table('search_terms')->pluck('term');

        return $terms->filter(function ($term) use ($query, $maxDistance) {
            return levenshtein($term, $query) <= $maxDistance;
        })->values()->toArray();
    }
}

// Usage in Laravel Controller
namespace App\Http\Controllers;

class SearchController extends Controller
{
    public function search(Request $request, StringSearchService $searchService)
    {
        $query = $request->input('q');

        // Validate and sanitize
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|max:100|regex:/^[a-zA-Z0-9\s]+$/'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid search query'], 400);
        }

        // Perform search
        $results = $searchService->fullTextSearch($query);

        // Log search
        Log::info('Search performed', [
            'query' => $query,
            'results_count' => count($results),
            'user_id' => auth()->id()
        ]);

        return response()->json($results);
    }

    public function autocomplete(Request $request, StringSearchService $searchService)
    {
        $prefix = $request->input('q');

        if (strlen($prefix) < 2) {
            return response()->json([]);
        }

        return response()->json(
            $searchService->autocomplete($prefix)
        );
    }
}
```

### Symfony Integration

```php
namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class StringSearchService
{
    private CacheInterface $cache;
    private LoggerInterface $logger;

    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Cached pattern matching
     */
    public function search(string $text, string $pattern, string $algorithm = 'kmp'): array
    {
        $cacheKey = sprintf('search_%s_%s_%s',
            $algorithm,
            md5($text),
            md5($pattern)
        );

        return $this->cache->get($cacheKey, function (ItemInterface $item) use (
            $text,
            $pattern,
            $algorithm
        ) {
            $item->expiresAfter(3600);

            $startTime = microtime(true);

            $result = match ($algorithm) {
                'naive' => naiveSearch($text, $pattern),
                'kmp' => kmpSearch($text, $pattern),
                'bm' => boyerMooreSearch($text, $pattern),
                'rk' => (new RabinKarp())->search($text, $pattern),
                default => kmpSearch($text, $pattern)
            };

            $duration = microtime(true) - $startTime;

            $this->logger->info('String search performed', [
                'algorithm' => $algorithm,
                'pattern_length' => strlen($pattern),
                'text_length' => strlen($text),
                'matches' => count($result),
                'duration' => $duration
            ]);

            return $result;
        });
    }

    /**
     * Search in uploaded files
     */
    public function searchInFile(string $filepath, string $pattern): array
    {
        if (!file_exists($filepath)) {
            throw new \Exception("File not found: $filepath");
        }

        // Check file size
        $maxSize = 10 * 1024 * 1024; // 10 MB
        if (filesize($filepath) > $maxSize) {
            throw new \Exception("File too large for in-memory search");
        }

        $content = file_get_contents($filepath);
        return $this->search($content, $pattern);
    }

    /**
     * Multi-pattern search (Aho-Corasick style)
     */
    public function multiPatternSearch(string $text, array $patterns): array
    {
        $results = [];

        foreach ($patterns as $pattern) {
            $matches = $this->search($text, $pattern);
            if (!empty($matches)) {
                $results[$pattern] = $matches;
            }
        }

        return $results;
    }
}

// Usage in Symfony Controller
namespace App\Controller;

use App\Service\StringSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['POST'])]
    public function search(
        Request $request,
        StringSearchService $searchService
    ): Response {
        $query = $request->request->get('query');
        $text = $request->request->get('text');

        // Validate input
        if (empty($query) || empty($text)) {
            return $this->json(['error' => 'Missing parameters'], 400);
        }

        if (strlen($query) > 1000 || strlen($text) > 1000000) {
            return $this->json(['error' => 'Input too large'], 400);
        }

        try {
            $matches = $searchService->search($text, $query, 'kmp');

            return $this->json([
                'query' => $query,
                'matches' => $matches,
                'count' => count($matches)
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

## Algorithm Selection Guide

| Use Case | Best Algorithm | Reason |
|----------|---------------|--------|
| **General search** | Boyer-Moore or PHP strpos() | Fast average case |
| **Short pattern, long text** | Boyer-Moore | Can skip many characters |
| **Multiple patterns** | Rabin-Karp or Aho-Corasick | Efficient for multiple searches |
| **Guaranteed linear time** | KMP | Never backtracks |
| **Simple implementation** | Naive or strpos() | Easy to understand/use |
| **Pattern with wildcards** | Regular expressions | Built-in support |
| **Unicode text** | MB functions or Regex | Proper character handling |
| **Security-sensitive** | Constant-time comparison | Prevent timing attacks |

## Practice Exercises

### Exercise 1: Wildcard Matching

Implement wildcard pattern matching (* and ?):

```php
function wildcardMatch(string $text, string $pattern): bool
{
    // * matches any sequence
    // ? matches any single character
    // Your code here
}

echo wildcardMatch("hello", "h*o") ? "Match" : "No match"; // Match
echo wildcardMatch("hello", "h?llo") ? "Match" : "No match"; // Match
```

### Exercise 2: Longest Common Substring

Find the longest substring common to two strings:

```php
function longestCommonSubstring(string $s1, string $s2): string
{
    // Your code here
}

echo longestCommonSubstring("abcdefgh", "cdefijk"); // "cdef"
```

### Exercise 3: Anagram Search

Find all anagrams of a pattern in text:

```php
function findAnagrams(string $text, string $pattern): array
{
    // Find all substrings that are anagrams of pattern
    // Your code here
}

print_r(findAnagrams("cbaebabacd", "abc"));
// [0, 6] - "cba" and "bac" are anagrams of "abc"
```

## Key Takeaways

- **Naive search** is O(n×m) but simple
- **KMP** is O(n+m) and never backtracks in text
- **Boyer-Moore** is often fastest in practice, can skip characters
- **Rabin-Karp** uses hashing, good for multiple patterns
- **PHP's strpos()** is highly optimized - use it when possible
- **Regular expressions** are powerful but slower for simple patterns
- **Choose algorithm** based on text size, pattern length, and use case

## What's Next

Congratulations on completing the Searching Algorithms section! In the next chapter, we'll begin exploring **Data Structures**, starting with **Arrays & Dynamic Arrays**.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 14 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-14)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-14
php 01-*.php
```

---

Continue to [Chapter 15: Arrays & Dynamic Arrays](/series/php-algorithms/chapters/15-arrays-dynamic-arrays).
