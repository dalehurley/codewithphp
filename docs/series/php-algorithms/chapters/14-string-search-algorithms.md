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
![14: String Search Algorithms](/images/php-algorithms/chapter-14-string-search-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 14</span>
</div>

# String Search Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

String searching (or pattern matching) is fundamental to text processing. Whether you're implementing search in a text editor, filtering log files, or building a search engine, you need efficient string search algorithms. Every time you use `Ctrl+F` in your editor, search through logs, or filter data, you're relying on string search algorithms working behind the scenes.

In this chapter, we'll explore multiple approaches from simple to sophisticated, discovering how algorithms like KMP and Boyer-Moore achieve remarkable performance gains. We'll start with the naive approach to understand the problem, then progressively build more efficient solutions. You'll learn how the Knuth-Morris-Pratt algorithm never backtracks in the text, how Boyer-Moore can skip entire sections, and how Rabin-Karp uses hashing for efficient multi-pattern matching.

By the end of this chapter, you'll have implemented four major string search algorithms and built a practical grep-like tool. You'll understand when to use each algorithm and how PHP's built-in functions leverage these techniques. This knowledge will help you optimize text processing in your applications and make informed decisions about algorithm selection.

## What You'll Learn

- Master the naive string search algorithm and understand its limitations
- Implement the Knuth-Morris-Pratt (KMP) algorithm for linear-time searching
- Build the Boyer-Moore algorithm that powers real-world text editors
- Use Rabin-Karp hashing for efficient multi-pattern matching
- Create a practical grep-like tool for text processing

## Prerequisites

Before starting this chapter, you should have:

- ✓ Understanding of strings and arrays
- ✓ Completion of Chapters 11-13 (search algorithms and hash tables)
- ✓ Familiarity with pattern matching concepts
- ✓ Basic knowledge of time complexity analysis

**Estimated Time**: ~55 minutes

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

The prefix table (also called LPS - Longest Proper Prefix which is also Suffix) tells us where to continue matching after a mismatch. It stores the length of the longest proper prefix that is also a suffix for each position in the pattern.

**Understanding the Prefix Table:**

For pattern `"AABAAB"`:
- Position 0: `""` → No prefix/suffix → `lps[0] = 0`
- Position 1: `"A"` → Prefix: `""`, Suffix: `""` → `lps[1] = 1` (length of "A")
- Position 2: `"AA"` → Prefixes: `"", "A"`, Suffixes: `"", "A"` → `lps[2] = 0` (no proper prefix = suffix)
- Position 3: `"AAB"` → Prefixes: `"", "A", "AA"`, Suffixes: `"", "B", "AB"` → `lps[3] = 1` ("A")
- Position 4: `"AABA"` → Prefixes: `"", "A", "AA", "AAB"`, Suffixes: `"", "A", "BA", "ABA"` → `lps[4] = 1` ("A")
- Position 5: `"AABAAB"` → Prefixes: `"", "A", "AA", "AAB", "AABA"`, Suffixes: `"", "B", "AB", "AAB", "BAAB"` → `lps[5] = 2` ("AA")

**Step-by-Step Construction:**

Let's build the prefix table for `"AABAAB"`:

```
Pattern: A A B A A B
Index:   0 1 2 3 4 5
LPS:     0 1 0 1 2 3
```

**Step-by-step walkthrough:**

1. **i=0, len=0**: `lps[0] = 0` (always starts at 0)
2. **i=1, len=0**: Compare `pattern[1]='A'` with `pattern[0]='A'` → Match! `len=1`, `lps[1]=1`, `i=2`
3. **i=2, len=1**: Compare `pattern[2]='B'` with `pattern[1]='A'` → Mismatch! `len != 0`, so `len = lps[0] = 0`
4. **i=2, len=0**: Compare `pattern[2]='B'` with `pattern[0]='A'` → Mismatch! `len == 0`, so `lps[2]=0`, `i=3`
5. **i=3, len=0**: Compare `pattern[3]='A'` with `pattern[0]='A'` → Match! `len=1`, `lps[3]=1`, `i=4`
6. **i=4, len=1**: Compare `pattern[4]='A'` with `pattern[1]='A'` → Match! `len=2`, `lps[4]=2`, `i=5`
7. **i=5, len=2**: Compare `pattern[5]='B'` with `pattern[2]='B'` → Match! `len=3`, `lps[5]=3`, done

```php
function computePrefixTable(string $pattern): array
{
    $m = strlen($pattern);
    $lps = array_fill(0, $m, 0);
    $len = 0; // Length of previous longest prefix suffix
    $i = 1;

    while ($i < $m) {
        if ($pattern[$i] === $pattern[$len]) {
            // Characters match - extend the prefix
            $len++;
            $lps[$i] = $len;
            $i++;
        } else {
            // Mismatch - try shorter prefix
            if ($len !== 0) {
                // Don't reset len to 0, use previous LPS value
                $len = $lps[$len - 1];
            } else {
                // No prefix found, move to next character
                $lps[$i] = 0;
                $i++;
            }
        }
    }

    return $lps;
}

// Example with detailed output
function computePrefixTableVisualized(string $pattern): array
{
    $m = strlen($pattern);
    $lps = array_fill(0, $m, 0);
    $len = 0;
    $i = 1;

    echo "Building prefix table for: $pattern\n";
    echo "Index: " . implode(' ', range(0, $m - 1)) . "\n";
    echo "Char:  " . implode(' ', str_split($pattern)) . "\n\n";

    while ($i < $m) {
        echo "Step $i: Comparing pattern[$i]='{$pattern[$i]}' with pattern[$len]='{$pattern[$len]}'\n";
        
        if ($pattern[$i] === $pattern[$len]) {
            $len++;
            $lps[$i] = $len;
            echo "  ✓ Match! len=$len, lps[$i]=$len\n";
            $i++;
        } else {
            if ($len !== 0) {
                echo "  ✗ Mismatch, len=$len, trying lps[$len-1]={$lps[$len - 1]}\n";
                $len = $lps[$len - 1];
            } else {
                $lps[$i] = 0;
                echo "  ✗ Mismatch, len=0, lps[$i]=0\n";
                $i++;
            }
        }
    }

    echo "\nFinal LPS: " . implode(' ', $lps) . "\n";
    return $lps;
}

// Example
$pattern = "AABAAB";
print_r(computePrefixTable($pattern));
// [0, 1, 0, 1, 2, 3]

// Visualized version
computePrefixTableVisualized($pattern);
```

**Key Insight:** When characters don't match, we don't start from the beginning. Instead, we use the LPS value to know how much of the pattern we've already matched, allowing us to skip unnecessary comparisons.

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

Test your understanding with these progressively challenging exercises.

### Exercise 1: Wildcard Matching (~20 minutes)

**Goal**: Implement wildcard pattern matching using dynamic programming or two-pointer technique

**Requirements:**
- Create a file called `exercise-wildcard-match.php`
- Implement `wildcardMatch(string $text, string $pattern): bool`
- `*` matches any sequence of characters (including empty)
- `?` matches any single character
- Must handle edge cases: empty strings, multiple `*`, `*` at start/end

**Starter code:**
```php
# filename: exercise-wildcard-match.php
<?php

function wildcardMatch(string $text, string $pattern): bool
{
    // Your code here
    // Hint: Use dynamic programming or two-pointer approach
    // Consider: What happens when you encounter '*'?
    // Consider: How to handle multiple '*' in a row?
}

// Test cases
echo wildcardMatch("hello", "h*o") ? "Match" : "No match"; // Expected: Match
echo "\n";
echo wildcardMatch("hello", "h?llo") ? "Match" : "No match"; // Expected: Match
echo "\n";
echo wildcardMatch("hello", "h*ll?") ? "Match" : "No match"; // Expected: Match
echo "\n";
echo wildcardMatch("hello", "h*ll??") ? "Match" : "No match"; // Expected: No match
echo "\n";
echo wildcardMatch("", "*") ? "Match" : "No match"; // Expected: Match
```

**Validation**: Run your code. It should output:
```
Match
Match
Match
No match
Match
```

<details>
<summary>💡 Solution Approach</summary>

**Strategy**: Use dynamic programming with memoization or two-pointer technique.

**Two-Pointer Approach:**
```php
function wildcardMatch(string $text, string $pattern): bool
{
    $textLen = strlen($text);
    $patternLen = strlen($pattern);
    $textIdx = 0;
    $patternIdx = 0;
    $starIdx = -1;
    $matchIdx = 0;

    while ($textIdx < $textLen) {
        // If characters match or pattern has '?', advance both pointers
        if ($patternIdx < $patternLen && 
            ($pattern[$patternIdx] === '?' || $pattern[$patternIdx] === $text[$textIdx])) {
            $textIdx++;
            $patternIdx++;
        }
        // If pattern has '*', record position and try matching empty string
        elseif ($patternIdx < $patternLen && $pattern[$patternIdx] === '*') {
            $starIdx = $patternIdx;
            $matchIdx = $textIdx;
            $patternIdx++;
        }
        // If we have a '*' recorded, backtrack and try matching one more character
        elseif ($starIdx !== -1) {
            $patternIdx = $starIdx + 1;
            $matchIdx++;
            $textIdx = $matchIdx;
        }
        // No match possible
        else {
            return false;
        }
    }

    // Skip remaining '*' in pattern
    while ($patternIdx < $patternLen && $pattern[$patternIdx] === '*') {
        $patternIdx++;
    }

    return $patternIdx === $patternLen;
}
```

**Key Insight**: When you encounter `*`, you can match zero or more characters. Use backtracking to try different lengths.
</details>

---

### Exercise 2: Longest Common Substring (~25 minutes)

**Goal**: Find the longest substring common to two strings using dynamic programming

**Requirements:**
- Create a file called `exercise-longest-substring.php`
- Implement `longestCommonSubstring(string $s1, string $s2): string`
- Return the longest common substring (not subsequence)
- O(n×m) time complexity is acceptable
- Handle edge cases: no common substring, empty strings

**Starter code:**
```php
# filename: exercise-longest-substring.php
<?php

function longestCommonSubstring(string $s1, string $s2): string
{
    // Your code here
    // Hint: Use dynamic programming table
    // Consider: What does dp[i][j] represent?
    // Consider: How do you track the maximum length and position?
}

// Test cases
echo longestCommonSubstring("abcdefgh", "cdefijk"); // Expected: "cdef"
echo "\n";
echo longestCommonSubstring("programming", "program"); // Expected: "program"
echo "\n";
echo longestCommonSubstring("hello", "world"); // Expected: "" (or empty string)
echo "\n";
echo longestCommonSubstring("abc", "abc"); // Expected: "abc"
```

**Validation**: Run your code. It should output:
```
cdef
program

abc
```

<details>
<summary>💡 Solution Approach</summary>

**Strategy**: Dynamic programming where `dp[i][j]` represents the length of common substring ending at positions `i` and `j`.

```php
function longestCommonSubstring(string $s1, string $s2): string
{
    $n = strlen($s1);
    $m = strlen($s2);
    
    if ($n === 0 || $m === 0) {
        return '';
    }

    $dp = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
    $maxLen = 0;
    $endPos = 0;

    for ($i = 1; $i <= $n; $i++) {
        for ($j = 1; $j <= $m; $j++) {
            if ($s1[$i - 1] === $s2[$j - 1]) {
                $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                
                if ($dp[$i][$j] > $maxLen) {
                    $maxLen = $dp[$i][$j];
                    $endPos = $i - 1;
                }
            } else {
                $dp[$i][$j] = 0;
            }
        }
    }

    if ($maxLen === 0) {
        return '';
    }

    return substr($s1, $endPos - $maxLen + 1, $maxLen);
}
```

**Key Insight**: Unlike longest common subsequence, substring requires consecutive characters. Reset to 0 when characters don't match.
</details>

---

### Exercise 3: Anagram Search (~30 minutes)

**Goal**: Find all starting indices of anagrams of a pattern in text using sliding window technique

**Requirements:**
- Create a file called `exercise-anagram-search.php`
- Implement `findAnagrams(string $text, string $pattern): array`
- Return array of starting indices where anagram of pattern occurs
- An anagram has same characters with same frequency
- O(n) time complexity where n = text length
- Handle edge cases: pattern longer than text, no anagrams found

**Starter code:**
```php
# filename: exercise-anagram-search.php
<?php

function findAnagrams(string $text, string $pattern): array
{
    // Your code here
    // Hint: Use sliding window with character frequency counting
    // Consider: How to check if two strings are anagrams?
    // Consider: How to efficiently update the window?
}

// Test cases
print_r(findAnagrams("cbaebabacd", "abc"));
// Expected: [0, 6] - "cba" and "bac" are anagrams of "abc"
echo "\n";

print_r(findAnagrams("abab", "ab"));
// Expected: [0, 1, 2] - "ab", "ba", "ab" are anagrams
echo "\n";

print_r(findAnagrams("hello", "xyz"));
// Expected: [] - no anagrams found
```

**Validation**: Run your code. It should output:
```
Array
(
    [0] => 0
    [1] => 6
)
Array
(
    [0] => 0
    [1] => 1
    [2] => 2
)
Array
(
)
```

<details>
<summary>💡 Solution Approach</summary>

**Strategy**: Sliding window with character frequency counting.

```php
function findAnagrams(string $text, string $pattern): array
{
    $textLen = strlen($text);
    $patternLen = strlen($pattern);
    $result = [];

    if ($patternLen > $textLen) {
        return $result;
    }

    // Count character frequencies in pattern
    $patternCount = array_count_values(str_split($pattern));
    
    // Count character frequencies in first window
    $windowCount = [];
    for ($i = 0; $i < $patternLen; $i++) {
        $char = $text[$i];
        $windowCount[$char] = ($windowCount[$char] ?? 0) + 1;
    }

    // Check if first window is anagram
    if ($windowCount === $patternCount) {
        $result[] = 0;
    }

    // Slide the window
    for ($i = $patternLen; $i < $textLen; $i++) {
        // Remove leftmost character
        $leftChar = $text[$i - $patternLen];
        $windowCount[$leftChar]--;
        if ($windowCount[$leftChar] === 0) {
            unset($windowCount[$leftChar]);
        }

        // Add rightmost character
        $rightChar = $text[$i];
        $windowCount[$rightChar] = ($windowCount[$rightChar] ?? 0) + 1;

        // Check if current window is anagram
        if ($windowCount === $patternCount) {
            $result[] = $i - $patternLen + 1;
        }
    }

    return $result;
}
```

**Key Insight**: Two strings are anagrams if they have the same character frequencies. Use sliding window to efficiently check each substring.
</details>

---

### Bonus Exercise 4: Implement KMP from Scratch (~40 minutes)

**Goal**: Implement the complete KMP algorithm including prefix table construction

**Requirements:**
- Create a file called `exercise-kmp-implementation.php`
- Implement both `computePrefixTable()` and `kmpSearch()` functions
- Test with various patterns and texts
- Add visualization to show how the algorithm works step-by-step
- Compare performance with naive search

**Starter code:**
```php
# filename: exercise-kmp-implementation.php
<?php

function computePrefixTable(string $pattern): array
{
    // Your implementation here
}

function kmpSearch(string $text, string $pattern): array
{
    // Your implementation here
}

// Test cases
$text = "ABABDABACDABABCABCAB";
$pattern = "ABABCABAB";
$matches = kmpSearch($text, $pattern);
print_r($matches); // Expected: [10]
```

::: tip Exercise Tips
- Start with Exercise 1 to build confidence with pattern matching
- Draw diagrams to visualize how each algorithm works
- Test edge cases: empty strings, pattern longer than text, no matches
- For Exercise 3, verify your frequency counting logic carefully
- Compare your solutions' performance with simpler approaches
- Review the algorithm explanations if stuck on implementation details
- Use the visualization functions provided in the chapter to debug
:::

## Wrap-up

Congratulations! You've mastered string search algorithms—essential tools for efficient text processing. Let's review what you've accomplished:

**✓ Core Algorithms Implemented:**
- ✅ **Naive search** - Simple O(n×m) approach, easy to understand and implement
- ✅ **KMP algorithm** - O(n+m) linear-time search that never backtracks in text
- ✅ **Boyer-Moore** - Often fastest in practice, can skip many characters
- ✅ **Rabin-Karp** - Uses rolling hash for efficient multi-pattern matching

**✓ Advanced Techniques:**
- ✅ Built prefix tables (LPS) for KMP preprocessing
- ✅ Implemented bad character rule for Boyer-Moore
- ✅ Created rolling hash functions for Rabin-Karp
- ✅ Handled edge cases: empty strings, Unicode, overlapping matches
- ✅ Security considerations: timing attacks, ReDoS protection

**✓ Real-World Applications:**
- ✅ Built a grep-like tool for file searching
- ✅ Created text highlighting functionality
- ✅ Implemented URL extraction and word counting
- ✅ Framework integration examples (Laravel, Symfony)
- ✅ Performance benchmarking and optimization strategies

**✓ Algorithm Selection:**
- ✅ Understand when to use each algorithm based on use case
- ✅ Know PHP's built-in functions and when they're optimal
- ✅ Recognize trade-offs between simplicity and performance
- ✅ Choose appropriate algorithm for text size and pattern length

**✓ Common Pitfalls Avoided:**
- ✅ **Off-by-one errors** in pattern matching loops and index calculations
- ✅ **Incorrect prefix table construction** in KMP (forgetting to backtrack `len`)
- ✅ **Hash collision issues** in Rabin-Karp (not verifying actual string match)
- ✅ **Unicode handling mistakes** (using `strlen()` instead of `mb_strlen()`)
- ✅ **Performance pitfalls** (using complex algorithms for small texts where naive is faster)
- ✅ **Edge case failures** (empty strings, pattern longer than text, overlapping matches)
- ✅ **Infinite loops** in KMP when not properly handling `j` reset after match
- ✅ **Memory issues** with large texts (loading entire file into memory)

**Real-World Impact:**

String search algorithms power:
- **Text editors**: Find/replace functionality (Boyer-Moore)
- **Search engines**: Indexing and query matching (KMP, Rabin-Karp)
- **Log analysis**: Filtering and pattern matching (grep tools)
- **Security systems**: Intrusion detection, pattern matching
- **Database systems**: Full-text search capabilities
- **Web applications**: Search functionality, autocomplete

You now understand not just *how* these algorithms work, but *when* and *why* to use each one. This knowledge will help you optimize text processing in your applications and make informed decisions about algorithm selection.

## Key Takeaways

### Core Algorithms
- ✅ **Naive search** is O(n×m) but simple and easy to understand
- ✅ **KMP** is O(n+m) and never backtracks in text - guaranteed linear time
- ✅ **Boyer-Moore** is often fastest in practice, can skip many characters
- ✅ **Rabin-Karp** uses hashing, excellent for multiple patterns

### Performance Characteristics
- ✅ **Preprocessing time**: KMP and Boyer-Moore require O(m) preprocessing
- ✅ **Search time**: KMP guarantees O(n), Boyer-Moore averages O(n) but can be O(n×m) worst case
- ✅ **Space complexity**: KMP uses O(m) for prefix table, Boyer-Moore uses O(σ) for bad character table
- ✅ **PHP's strpos()** is highly optimized - use it when possible for simple searches

### When to Use Each Algorithm
- ✅ **Naive**: Small texts, simple patterns, educational purposes
- ✅ **KMP**: Guaranteed linear time needed, patterns with many repeated substrings
- ✅ **Boyer-Moore**: Long patterns, large alphabets, general-purpose search
- ✅ **Rabin-Karp**: Multiple pattern searches, rolling hash benefits
- ✅ **Regular expressions**: Complex patterns, but slower for simple patterns

### Best Practices
- ✅ **Choose algorithm** based on text size, pattern length, and use case
- ✅ **Handle edge cases**: Empty strings, Unicode, special characters
- ✅ **Consider security**: Timing attacks, ReDoS protection
- ✅ **Benchmark** different approaches for your specific use case
- ✅ **Use PHP built-ins** when they meet your needs - they're highly optimized

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="14"
  label="String Search Algorithms understood!"
/>

## Further Reading

### Official Documentation & Standards
- [PHP String Functions](https://www.php.net/manual/en/ref.strings.php) — Complete reference for PHP string operations
- [strpos() function](https://www.php.net/manual/en/function.strpos.php) — PHP's optimized string search
- [preg_match() function](https://www.php.net/manual/en/function.preg-match.php) — Regular expression matching
- [PCRE Patterns](https://www.php.net/manual/en/book.pcre.php) — Regular expression syntax and optimization

### Algorithm Resources
- **Introduction to Algorithms (CLRS)** — Chapter 32 covers string matching algorithms in detail
- **Algorithm Design Manual** — Section 18.3 covers pattern matching algorithms
- [KMP Algorithm Visualization](https://www.cs.usfca.edu/~galles/visualization/StringMatching.html) — Interactive algorithm animation
- [Boyer-Moore Algorithm Paper](https://dl.acm.org/doi/10.1145/359842.359859) — Original 1977 paper by Boyer and Moore

### Advanced Topics
- **Aho-Corasick Algorithm** — Multi-pattern matching using finite automata
- **Suffix Trees & Suffix Arrays** — Advanced data structures for string problems
- **Z-Algorithm** — Linear-time pattern matching using Z-array
- **Finite Automata** — State machine approach to string matching
- **Regular Expression Engines** — How regex engines work internally

### Security & Performance
- [ReDoS (Regular Expression Denial of Service)](https://owasp.org/www-community/attacks/Regular_expression_Denial_of_Service_-_ReDoS) — OWASP guide to preventing ReDoS attacks
- [Timing Attacks](https://en.wikipedia.org/wiki/Timing_attack) — Understanding timing-based vulnerabilities
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php) — PHP security documentation

### Related Chapters
- [Chapter 11: Linear Search & Variants](/series/php-algorithms/chapters/11-linear-search-variants) — Compare with linear search approaches
- [Chapter 13: Hash Tables & Hash Functions](/series/php-algorithms/chapters/13-hash-tables-hash-functions) — Understanding hash functions used in Rabin-Karp
- [Chapter 33: String Algorithms Deep Dive](/series/php-algorithms/chapters/33-string-algorithms-deep-dive) — Advanced string algorithms and techniques
- [Chapter 29: Performance Optimization](/series/php-algorithms/chapters/29-performance-optimization) — Choosing the right algorithm for your use case

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
