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

## Algorithm Selection Guide

| Use Case | Best Algorithm | Reason |
|----------|---------------|--------|
| **General search** | Boyer-Moore or PHP strpos() | Fast average case |
| **Short pattern, long text** | Boyer-Moore | Can skip many characters |
| **Multiple patterns** | Rabin-Karp or Aho-Corasick | Efficient for multiple searches |
| **Guaranteed linear time** | KMP | Never backtracks |
| **Simple implementation** | Naive or strpos() | Easy to understand/use |
| **Pattern with wildcards** | Regular expressions | Built-in support |

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

---

Continue to [Chapter 15: Arrays & Dynamic Arrays](/series/php-algorithms/chapters/15-arrays-dynamic-arrays).
