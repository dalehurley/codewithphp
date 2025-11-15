---
title: "33: String Algorithms Deep Dive"
description: "Advanced string algorithms including Aho-Corasick, suffix trees/arrays, pattern matching, and text processing for search engines and bioinformatics"
series: "php-algorithms"
chapter: 33
order: 33
difficulty: "Advanced"
prerequisites: []
---

![String Algorithms Deep Dive](/images/php-algorithms/chapter-33-string-algorithms-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 33</span>
</div>

# 33: String Algorithms Deep Dive <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

String algorithms are fundamental to text processing, search engines, bioinformatics, and data compression. This chapter explores advanced string matching and manipulation techniques beyond basic patterns. These algorithms power everything from spam filters to DNA sequencing, handling millions of pattern matches per second.

While basic string operations like `strpos()` and `substr()` work well for simple tasks, real-world applications require sophisticated algorithms that can handle multiple patterns simultaneously, process massive texts efficiently, and solve complex matching problems. You'll learn how search engines index billions of web pages, how antivirus software scans files for thousands of malware signatures, and how DNA sequencing tools find genetic patterns.

By the end of this chapter, you'll have implemented several advanced string algorithms including Aho-Corasick for multi-pattern matching, suffix arrays and trees for fast substring queries, and specialized algorithms for palindromes and sequence comparison. These techniques form the foundation of modern text processing systems.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Comfortable working with strings and character arrays
- Understanding of tree data structures (tries, binary trees, traversals)
- Knowledge of hash functions for string operations
- Familiarity with dynamic programming for string problems (LCS, edit distance)

**Estimated Time**: ~55 minutes

**Verify your setup:**

```bash
php --version
```

## What You'll Build

By the end of this chapter, you will have created:

- Aho-Corasick automaton for multi-pattern matching with failure links
- Trie (prefix tree) implementation for autocomplete and prefix matching
- Suffix array implementation with longest common prefix (LCP) array
- Suffix tree for linear-time pattern matching
- Z-algorithm implementation for fast string matching
- Manacher's algorithm for finding all palindromes
- Rabin-Karp rolling hash implementation
- Longest Common Subsequence (LCS) algorithm with diff generation
- Unicode-aware string processing utilities
- Text similarity and comparison tools

## Objectives

- Implement Aho-Corasick for searching multiple patterns simultaneously in linear time
- Build trie data structures for efficient autocomplete and prefix matching
- Construct suffix trees and arrays for advanced pattern matching and text analysis
- Apply string algorithms to real-world problems like content filtering and plagiarism detection
- Optimize text processing operations that traditionally take quadratic time down to linear
- Use advanced string matching for bioinformatics and computational biology applications
- Handle Unicode and multibyte strings correctly in PHP
- Compare and benchmark different string matching algorithms

## Aho-Corasick Algorithm

The Aho-Corasick algorithm efficiently searches for multiple patterns simultaneously in O(n + m + z) time, where n is text length, m is total pattern length, and z is number of matches.

### Trie-Based Implementation

```php
# filename: aho-corasick.php
<?php

declare(strict_types=1);

class AhoCorasickNode {
    public array $children = [];
    public ?AhoCorasickNode $failure = null;
    public array $output = [];
    public int $depth = 0;
}

class AhoCorasick {
    private AhoCorasickNode $root;
    private array $patterns = [];

    public function __construct() {
        $this->root = new AhoCorasickNode();
    }

    public function addPattern(string $pattern, $id = null): void {
        $node = $this->root;
        $id = $id ?? $pattern;
        $this->patterns[$id] = $pattern;

        // Build trie
        for ($i = 0; $i < strlen($pattern); $i++) {
            $char = $pattern[$i];

            if (!isset($node->children[$char])) {
                $node->children[$char] = new AhoCorasickNode();
                $node->children[$char]->depth = $node->depth + 1;
            }

            $node = $node->children[$char];
        }

        // Mark as output node
        $node->output[] = $id;
    }

    public function build(): void {
        // Build failure links using BFS
        $queue = new SplQueue();

        // All children of root fail to root
        foreach ($this->root->children as $child) {
            $child->failure = $this->root;
            $queue->enqueue($child);
        }

        while (!$queue->isEmpty()) {
            $current = $queue->dequeue();

            foreach ($current->children as $char => $child) {
                $queue->enqueue($child);

                // Find failure link
                $failure = $current->failure;

                while ($failure !== null && !isset($failure->children[$char])) {
                    $failure = $failure->failure;
                }

                $child->failure = $failure !== null && isset($failure->children[$char])
                    ? $failure->children[$char]
                    : $this->root;

                // Merge output from failure node
                $child->output = array_merge($child->output, $child->failure->output);
            }
        }
    }

    public function search(string $text): array {
        $results = [];
        $node = $this->root;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            // Follow failure links until we find a match or reach root
            while ($node !== $this->root && !isset($node->children[$char])) {
                $node = $node->failure;
            }

            if (isset($node->children[$char])) {
                $node = $node->children[$char];

                // Record all matches at this position
                foreach ($node->output as $patternId) {
                    $pattern = $this->patterns[$patternId];
                    $start = $i - strlen($pattern) + 1;

                    $results[] = [
                        'pattern' => $pattern,
                        'id' => $patternId,
                        'start' => $start,
                        'end' => $i + 1
                    ];
                }
            }
        }

        return $results;
    }

    public function replace(string $text, array $replacements): string {
        $matches = $this->search($text);

        // Sort by start position in reverse to avoid offset issues
        usort($matches, fn($a, $b) => $b['start'] - $a['start']);

        foreach ($matches as $match) {
            $replacement = $replacements[$match['id']] ?? $match['pattern'];
            $text = substr_replace($text, $replacement, $match['start'], strlen($match['pattern']));
        }

        return $text;
    }
}

// Usage
$ac = new AhoCorasick();

$ac->addPattern('he', 'word_he');
$ac->addPattern('she', 'word_she');
$ac->addPattern('his', 'word_his');
$ac->addPattern('hers', 'word_hers');

$ac->build();

$text = 'she sells his hers by the seashore';
$matches = $ac->search($text);

foreach ($matches as $match) {
    echo "Found '{$match['pattern']}' at position {$match['start']}\n";
}

// Replace all patterns
$replacements = [
    'word_he' => 'HE',
    'word_she' => 'SHE',
    'word_his' => 'HIS',
    'word_hers' => 'HERS'
];

$replaced = $ac->replace($text, $replacements);
echo "Replaced: $replaced\n";
```

**Time Complexity**:

- Build: O(m) where m is total pattern length
- Search: O(n + z) where n is text length, z is number of matches

**Space Complexity**: O(m × σ) where σ is alphabet size

### Real-World Example: Content Filtering

```php
# filename: content-filter.php
<?php

declare(strict_types=1);

class ContentFilter {
    private AhoCorasick $profanityFilter;
    private AhoCorasick $spamFilter;

    public function __construct() {
        $this->profanityFilter = new AhoCorasick();
        $this->spamFilter = new AhoCorasick();
    }

    public function loadProfanityList(array $words): void {
        foreach ($words as $word) {
            $this->profanityFilter->addPattern(strtolower($word));
        }
        $this->profanityFilter->build();
    }

    public function loadSpamPatterns(array $patterns): void {
        foreach ($patterns as $pattern) {
            $this->spamFilter->addPattern(strtolower($pattern));
        }
        $this->spamFilter->build();
    }

    public function filterContent(string $content): array {
        $lowerContent = strtolower($content);

        $profanity = $this->profanityFilter->search($lowerContent);
        $spam = $this->spamFilter->search($lowerContent);

        return [
            'is_clean' => empty($profanity) && empty($spam),
            'profanity_count' => count($profanity),
            'spam_count' => count($spam),
            'violations' => array_merge($profanity, $spam)
        ];
    }

    public function censorContent(string $content): string {
        $replacements = [];
        $matches = $this->profanityFilter->search(strtolower($content));

        foreach ($matches as $match) {
            $replacements[$match['id']] = str_repeat('*', strlen($match['pattern']));
        }

        return $this->profanityFilter->replace($content, $replacements);
    }
}

// Usage
$filter = new ContentFilter();

$filter->loadProfanityList(['bad', 'worse', 'worst']);
$filter->loadSpamPatterns(['click here', 'buy now', 'limited offer']);

$content = 'Click here to buy now! This is a bad deal with limited offer.';

$analysis = $filter->filterContent($content);
print_r($analysis);

$censored = $filter->censorContent($content);
echo "Censored: $censored\n";
```

## Trie (Prefix Tree)

A trie (pronounced "try") is a tree-like data structure that stores strings where common prefixes share paths. It's ideal for autocomplete systems, spell checkers, and prefix-based searches. Unlike hash tables, tries allow efficient prefix matching and can find all strings with a given prefix in O(m + k) time, where m is prefix length and k is number of matches.

### Basic Trie Implementation

```php
# filename: trie.php
<?php

declare(strict_types=1);

class TrieNode {
    public array $children = [];
    public bool $isEndOfWord = false;
    public int $frequency = 0;  // For ranking autocomplete results
}

class Trie {
    private TrieNode $root;

    public function __construct() {
        $this->root = new TrieNode();
    }

    public function insert(string $word, int $frequency = 1): void {
        $node = $this->root;

        for ($i = 0; $i < strlen($word); $i++) {
            $char = $word[$i];

            if (!isset($node->children[$char])) {
                $node->children[$char] = new TrieNode();
            }

            $node = $node->children[$char];
        }

        $node->isEndOfWord = true;
        $node->frequency += $frequency;
    }

    public function search(string $word): bool {
        $node = $this->findNode($word);
        return $node !== null && $node->isEndOfWord;
    }

    public function startsWith(string $prefix): bool {
        return $this->findNode($prefix) !== null;
    }

    private function findNode(string $prefix): ?TrieNode {
        $node = $this->root;

        for ($i = 0; $i < strlen($prefix); $i++) {
            $char = $prefix[$i];

            if (!isset($node->children[$char])) {
                return null;
            }

            $node = $node->children[$char];
        }

        return $node;
    }

    public function getAllWordsWithPrefix(string $prefix, int $limit = 10): array {
        $node = $this->findNode($prefix);

        if ($node === null) {
            return [];
        }

        $results = [];
        $this->collectWords($node, $prefix, $results, $limit);

        // Sort by frequency (most common first)
        usort($results, fn($a, $b) => $b['frequency'] - $a['frequency']);

        return $results;
    }

    private function collectWords(TrieNode $node, string $prefix, array &$results, int $limit): void {
        if (count($results) >= $limit) {
            return;
        }

        if ($node->isEndOfWord) {
            $results[] = [
                'word' => $prefix,
                'frequency' => $node->frequency
            ];
        }

        foreach ($node->children as $char => $child) {
            $this->collectWords($child, $prefix . $char, $results, $limit);
        }
    }

    public function delete(string $word): bool {
        return $this->deleteRecursive($this->root, $word, 0);
    }

    private function deleteRecursive(TrieNode $node, string $word, int $index): bool {
        if ($index === strlen($word)) {
            if (!$node->isEndOfWord) {
                return false;  // Word doesn't exist
            }

            $node->isEndOfWord = false;
            return empty($node->children);
        }

        $char = $word[$index];

        if (!isset($node->children[$char])) {
            return false;  // Word doesn't exist
        }

        $shouldDeleteChild = $this->deleteRecursive($node->children[$char], $word, $index + 1);

        if ($shouldDeleteChild) {
            unset($node->children[$char]);
            return !$node->isEndOfWord && empty($node->children);
        }

        return false;
    }

    public function countWords(): int {
        return $this->countWordsRecursive($this->root);
    }

    private function countWordsRecursive(TrieNode $node): int {
        $count = $node->isEndOfWord ? 1 : 0;

        foreach ($node->children as $child) {
            $count += $this->countWordsRecursive($child);
        }

        return $count;
    }
}

// Usage
$trie = new Trie();

// Insert words with frequencies (for autocomplete ranking)
$trie->insert('hello', 100);
$trie->insert('help', 50);
$trie->insert('helmet', 30);
$trie->insert('world', 80);
$trie->insert('word', 40);

// Search for exact word
var_dump($trie->search('hello'));  // true
var_dump($trie->search('help'));   // true
var_dump($trie->search('helpful')); // false

// Check prefix
var_dump($trie->startsWith('hel'));  // true
var_dump($trie->startsWith('wor'));  // true

// Autocomplete
$suggestions = $trie->getAllWordsWithPrefix('hel', 5);
foreach ($suggestions as $suggestion) {
    echo "{$suggestion['word']} (frequency: {$suggestion['frequency']})\n";
}
// Output:
// hello (frequency: 100)
// help (frequency: 50)
// helmet (frequency: 30)

// Count total words
echo "Total words: " . $trie->countWords() . "\n";  // 5
```

**Time Complexity**:

- Insert: O(m) where m is word length
- Search: O(m)
- Prefix search: O(m + k) where k is number of matches
- Delete: O(m)

**Space Complexity**: O(ALPHABET_SIZE × N × M) where N is number of words, M is average word length

### Real-World Example: Autocomplete System

```php
# filename: autocomplete-system.php
<?php

declare(strict_types=1);

class AutocompleteSystem {
    private Trie $trie;
    private string $currentQuery = '';

    public function __construct() {
        $this->trie = new Trie();
    }

    public function train(array $queries): void {
        // Train with historical queries (frequency = number of times searched)
        foreach ($queries as $query => $frequency) {
            $this->trie->insert(strtolower($query), $frequency);
        }
    }

    public function input(string $char): array {
        if ($char === '#') {
            // Save current query
            if (!empty($this->currentQuery)) {
                $this->trie->insert(strtolower($this->currentQuery), 1);
                $this->currentQuery = '';
            }
            return [];
        }

        $this->currentQuery .= $char;
        return $this->trie->getAllWordsWithPrefix(strtolower($this->currentQuery), 3);
    }

    public function getTopSuggestions(string $prefix, int $topN = 5): array {
        $suggestions = $this->trie->getAllWordsWithPrefix(strtolower($prefix), $topN);
        return array_map(fn($s) => $s['word'], $suggestions);
    }
}

// Usage: Build autocomplete from search history
$autocomplete = new AutocompleteSystem();

// Train with historical search data
$searchHistory = [
    'php tutorial' => 150,
    'php arrays' => 80,
    'php functions' => 120,
    'python tutorial' => 90,
    'python lists' => 60,
    'javascript tutorial' => 200,
    'javascript arrays' => 100,
];

$autocomplete->train($searchHistory);

// Simulate user typing
echo "User types 'p':\n";
$suggestions = $autocomplete->input('p');
foreach ($suggestions as $suggestion) {
    echo "  - {$suggestion['word']}\n";
}

echo "\nUser types 'h':\n";
$suggestions = $autocomplete->input('h');
foreach ($suggestions as $suggestion) {
    echo "  - {$suggestion['word']}\n";
}

// Get top suggestions for a prefix
echo "\nTop suggestions for 'php':\n";
$top = $autocomplete->getTopSuggestions('php', 3);
foreach ($top as $suggestion) {
    echo "  - $suggestion\n";
}
```

### Advanced: Compressed Trie (Radix Tree)

For better space efficiency, we can compress nodes with single children:

```php
# filename: radix-trie.php
<?php

declare(strict_types=1);

class RadixNode {
    public string $label = '';
    public array $children = [];
    public bool $isEndOfWord = false;
}

class RadixTrie {
    private RadixNode $root;

    public function __construct() {
        $this->root = new RadixNode();
    }

    public function insert(string $word): void {
        $this->insertRecursive($this->root, $word);
    }

    private function insertRecursive(RadixNode $node, string $word): void {
        if (empty($word)) {
            $node->isEndOfWord = true;
            return;
        }

        // Find matching child
        foreach ($node->children as $child) {
            $commonPrefix = $this->getCommonPrefix($child->label, $word);

            if (!empty($commonPrefix)) {
                if ($commonPrefix === $child->label) {
                    // Full match, recurse
                    $this->insertRecursive($child, substr($word, strlen($commonPrefix)));
                } else {
                    // Partial match, split node
                    $newNode = new RadixNode();
                    $newNode->label = substr($child->label, strlen($commonPrefix));
                    $newNode->children = $child->children;
                    $newNode->isEndOfWord = $child->isEndOfWord;

                    $child->label = $commonPrefix;
                    $child->children = [$newNode->label[0] => $newNode];
                    $child->isEndOfWord = false;

                    $remaining = substr($word, strlen($commonPrefix));
                    if (!empty($remaining)) {
                        $leaf = new RadixNode();
                        $leaf->label = $remaining;
                        $leaf->isEndOfWord = true;
                        $child->children[$remaining[0]] = $leaf;
                    } else {
                        $child->isEndOfWord = true;
                    }
                }
                return;
            }
        }

        // No match, create new child
        $newNode = new RadixNode();
        $newNode->label = $word;
        $newNode->isEndOfWord = true;
        $node->children[$word[0]] = $newNode;
    }

    private function getCommonPrefix(string $s1, string $s2): string {
        $minLen = min(strlen($s1), strlen($s2));
        $prefix = '';

        for ($i = 0; $i < $minLen; $i++) {
            if ($s1[$i] === $s2[$i]) {
                $prefix .= $s1[$i];
            } else {
                break;
            }
        }

        return $prefix;
    }

    public function search(string $word): bool {
        return $this->searchRecursive($this->root, $word);
    }

    private function searchRecursive(RadixNode $node, string $word): bool {
        if (empty($word)) {
            return $node->isEndOfWord;
        }

        foreach ($node->children as $child) {
            if (str_starts_with($word, $child->label)) {
                return $this->searchRecursive($child, substr($word, strlen($child->label)));
            }
        }

        return false;
    }
}

// Usage
$radixTrie = new RadixTrie();

$radixTrie->insert('hello');
$radixTrie->insert('help');
$radixTrie->insert('helmet');
$radixTrie->insert('world');

var_dump($radixTrie->search('hello'));  // true
var_dump($radixTrie->search('help'));   // true
var_dump($radixTrie->search('helmet'));  // true
var_dump($radixTrie->search('world'));   // true
var_dump($radixTrie->search('helpful')); // false
```

**Benefits of Radix Trie**:

- Reduced space complexity (compresses single-child nodes)
- Faster traversal (fewer nodes to visit)
- Better cache locality

**Trade-offs**:

- More complex implementation
- Slower insertion (needs node splitting)
- Still O(m) for search operations

## Suffix Array

A suffix array is a sorted array of all suffixes of a string, enabling fast pattern matching and other string operations.

### Construction and Search

```php
# filename: suffix-array.php
<?php

declare(strict_types=1);

class SuffixArray {
    private string $text;
    private array $suffixArray;
    private array $lcp;  // Longest Common Prefix

    public function __construct(string $text) {
        $this->text = $text . '$';  // Add sentinel
        $this->build();
        $this->buildLCP();
    }

    private function build(): void {
        $n = strlen($this->text);
        $suffixes = [];

        // Create suffix indices
        for ($i = 0; $i < $n; $i++) {
            $suffixes[] = $i;
        }

        // Sort suffixes
        usort($suffixes, function ($a, $b) {
            return strcmp(
                substr($this->text, $a),
                substr($this->text, $b)
            );
        });

        $this->suffixArray = $suffixes;
    }

    private function buildLCP(): void {
        $n = strlen($this->text);
        $this->lcp = array_fill(0, $n, 0);
        $rank = array_flip($this->suffixArray);

        $h = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($rank[$i] > 0) {
                $j = $this->suffixArray[$rank[$i] - 1];

                while ($i + $h < $n && $j + $h < $n && $this->text[$i + $h] === $this->text[$j + $h]) {
                    $h++;
                }

                $this->lcp[$rank[$i]] = $h;

                if ($h > 0) {
                    $h--;
                }
            }
        }
    }

    public function search(string $pattern): array {
        $positions = [];
        $n = strlen($this->text);
        $m = strlen($pattern);

        // Binary search for first occurrence
        $left = 0;
        $right = $n - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $suffix = substr($this->text, $this->suffixArray[$mid]);
            $cmp = strncmp($suffix, $pattern, $m);

            if ($cmp < 0) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        $start = $left;

        // Binary search for last occurrence
        $right = $n - 1;
        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            $suffix = substr($this->text, $this->suffixArray[$mid]);
            $cmp = strncmp($suffix, $pattern, $m);

            if ($cmp <= 0) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        $end = $right;

        // Collect all occurrences
        for ($i = $start; $i <= $end; $i++) {
            $suffix = substr($this->text, $this->suffixArray[$i], $m);
            if ($suffix === $pattern) {
                $positions[] = $this->suffixArray[$i];
            }
        }

        return $positions;
    }

    public function longestRepeatedSubstring(): string {
        $maxLen = 0;
        $maxPos = 0;

        for ($i = 1; $i < count($this->lcp); $i++) {
            if ($this->lcp[$i] > $maxLen) {
                $maxLen = $this->lcp[$i];
                $maxPos = $this->suffixArray[$i];
            }
        }

        return substr($this->text, $maxPos, $maxLen);
    }

    public function longestCommonSubstring(string $other): string {
        $combined = $this->text . '#' . $other . '$';
        $sa = new SuffixArray(substr($combined, 0, -1));  // Remove our sentinel

        $n1 = strlen($this->text);
        $maxLen = 0;
        $maxPos = 0;

        for ($i = 1; $i < count($sa->lcp); $i++) {
            $pos1 = $sa->suffixArray[$i];
            $pos2 = $sa->suffixArray[$i - 1];

            // Check if suffixes come from different strings
            if (($pos1 < $n1) !== ($pos2 < $n1)) {
                if ($sa->lcp[$i] > $maxLen) {
                    $maxLen = $sa->lcp[$i];
                    $maxPos = $pos1;
                }
            }
        }

        return substr($combined, $maxPos, $maxLen);
    }

    public function countDistinctSubstrings(): int {
        $n = strlen($this->text);
        $total = $n * ($n - 1) / 2;  // Total possible substrings
        $duplicates = array_sum($this->lcp);

        return $total - $duplicates;
    }
}

// Usage
$sa = new SuffixArray('banana');

// Search for pattern
$positions = $sa->search('ana');
print_r($positions);  // [1, 3]

// Find longest repeated substring
echo $sa->longestRepeatedSubstring() . "\n";  // 'ana'

// Find longest common substring
$sa2 = new SuffixArray('ananas');
echo $sa->longestCommonSubstring('ananas') . "\n";  // 'anana'

// Count distinct substrings
echo $sa->countDistinctSubstrings() . "\n";
```

**Time Complexity**:

- Build: O(n log n) with comparison sort, O(n) with specialized algorithms
- Search: O(m log n + occ) where occ is number of occurrences

**Space Complexity**: O(n)

## Suffix Tree

A suffix tree is a compressed trie of all suffixes, enabling linear-time pattern matching and substring queries.

### Simplified Suffix Tree

```php
# filename: suffix-tree.php
<?php

declare(strict_types=1);

class SuffixTreeNode {
    public array $children = [];
    public ?int $start = null;
    public ?int $end = null;
    public ?int $suffixIndex = null;
}

class SuffixTree {
    private string $text;
    private SuffixTreeNode $root;

    public function __construct(string $text) {
        $this->text = $text . '$';
        $this->root = new SuffixTreeNode();
        $this->buildNaive();
    }

    private function buildNaive(): void {
        $n = strlen($this->text);

        // Add all suffixes
        for ($i = 0; $i < $n; $i++) {
            $this->addSuffix($i);
        }
    }

    private function addSuffix(int $suffixStart): void {
        $node = $this->root;
        $pos = $suffixStart;
        $n = strlen($this->text);

        while ($pos < $n) {
            $char = $this->text[$pos];

            if (!isset($node->children[$char])) {
                // Create new leaf
                $leaf = new SuffixTreeNode();
                $leaf->start = $pos;
                $leaf->end = $n - 1;
                $leaf->suffixIndex = $suffixStart;

                $node->children[$char] = $leaf;
                return;
            }

            // Follow existing edge
            $child = $node->children[$char];
            $edgeLen = $child->end - $child->start + 1;
            $matched = 0;

            // Match along edge
            while ($matched < $edgeLen && $pos < $n && $this->text[$child->start + $matched] === $this->text[$pos]) {
                $matched++;
                $pos++;
            }

            if ($matched === $edgeLen) {
                // Fully matched edge, continue to child
                $node = $child;
            } else {
                // Partial match, split edge
                $split = new SuffixTreeNode();
                $split->start = $child->start;
                $split->end = $child->start + $matched - 1;

                $child->start += $matched;
                $split->children[$this->text[$child->start]] = $child;

                // Add new leaf for remaining suffix
                $leaf = new SuffixTreeNode();
                $leaf->start = $pos;
                $leaf->end = $n - 1;
                $leaf->suffixIndex = $suffixStart;

                $split->children[$this->text[$pos]] = $leaf;
                $node->children[$char] = $split;

                return;
            }
        }
    }

    public function search(string $pattern): bool {
        $node = $this->root;
        $pos = 0;
        $patternLen = strlen($pattern);

        while ($pos < $patternLen) {
            $char = $pattern[$pos];

            if (!isset($node->children[$char])) {
                return false;
            }

            $child = $node->children[$char];
            $edgeStart = $child->start;
            $edgeEnd = $child->end;

            // Match along edge
            for ($i = $edgeStart; $i <= $edgeEnd && $pos < $patternLen; $i++, $pos++) {
                if ($this->text[$i] !== $pattern[$pos]) {
                    return false;
                }
            }

            $node = $child;
        }

        return true;
    }

    public function getAllOccurrences(string $pattern): array {
        $positions = [];
        $node = $this->findNode($pattern);

        if ($node !== null) {
            $this->collectLeaves($node, $positions);
        }

        return $positions;
    }

    private function findNode(string $pattern): ?SuffixTreeNode {
        $node = $this->root;
        $pos = 0;
        $patternLen = strlen($pattern);

        while ($pos < $patternLen) {
            $char = $pattern[$pos];

            if (!isset($node->children[$char])) {
                return null;
            }

            $child = $node->children[$char];
            $edgeStart = $child->start;
            $edgeEnd = $child->end;

            for ($i = $edgeStart; $i <= $edgeEnd && $pos < $patternLen; $i++, $pos++) {
                if ($this->text[$i] !== $pattern[$pos]) {
                    return null;
                }
            }

            $node = $child;
        }

        return $node;
    }

    private function collectLeaves(SuffixTreeNode $node, array &$positions): void {
        if (empty($node->children)) {
            // Leaf node
            $positions[] = $node->suffixIndex;
            return;
        }

        foreach ($node->children as $child) {
            $this->collectLeaves($child, $positions);
        }
    }

    public function longestRepeatedSubstring(): string {
        $maxLen = 0;
        $maxStr = '';

        $this->dfsLongestRepeat($this->root, '', $maxLen, $maxStr);

        return $maxStr;
    }

    private function dfsLongestRepeat(SuffixTreeNode $node, string $path, int &$maxLen, string &$maxStr): int {
        if (empty($node->children)) {
            return 1;  // Leaf node
        }

        $totalChildren = 0;

        foreach ($node->children as $child) {
            $edgeLabel = substr($this->text, $child->start, $child->end - $child->start + 1);
            $childPath = $path . $edgeLabel;

            $childCount = $this->dfsLongestRepeat($child, $childPath, $maxLen, $maxStr);
            $totalChildren += $childCount;
        }

        // Internal node with multiple children = repeated substring
        if ($totalChildren > 1 && strlen($path) > $maxLen) {
            $maxLen = strlen($path);
            $maxStr = $path;
        }

        return $totalChildren;
    }
}

// Usage
$st = new SuffixTree('banana');

var_dump($st->search('ana'));  // true
var_dump($st->search('xyz'));  // false

$occurrences = $st->getAllOccurrences('ana');
print_r($occurrences);  // [1, 3]

echo $st->longestRepeatedSubstring() . "\n";  // 'ana'
```

**Time Complexity**:

- Ukkonen's algorithm: O(n) construction
- Search: O(m) where m is pattern length

**Space Complexity**: O(n)

## Z-Algorithm

The Z-algorithm preprocesses a string to create a Z-array, where each element represents the length of the longest substring starting from that position that matches the prefix. This enables fast pattern matching in linear time.

```php
# filename: z-algorithm.php
<?php

declare(strict_types=1);

class ZAlgorithm {
    public static function computeZ(string $s): array {
        $n = strlen($s);
        $z = array_fill(0, $n, 0);
        $z[0] = $n;

        $left = 0;
        $right = 0;

        for ($i = 1; $i < $n; $i++) {
            if ($i > $right) {
                $left = $right = $i;

                while ($right < $n && $s[$right - $left] === $s[$right]) {
                    $right++;
                }

                $z[$i] = $right - $left;
                $right--;
            } else {
                $k = $i - $left;

                if ($z[$k] < $right - $i + 1) {
                    $z[$i] = $z[$k];
                } else {
                    $left = $i;

                    while ($right < $n && $s[$right - $left] === $s[$right]) {
                        $right++;
                    }

                    $z[$i] = $right - $left;
                    $right--;
                }
            }
        }

        return $z;
    }

    public static function search(string $text, string $pattern): array {
        $combined = $pattern . '$' . $text;
        $z = self::computeZ($combined);

        $positions = [];
        $patternLen = strlen($pattern);

        for ($i = $patternLen + 1; $i < strlen($combined); $i++) {
            if ($z[$i] === $patternLen) {
                $positions[] = $i - $patternLen - 1;
            }
        }

        return $positions;
    }
}

// Usage
$positions = ZAlgorithm::search('abababab', 'abab');
print_r($positions);  // [0, 2, 4]
```

**Time Complexity**: O(n + m)
**Space Complexity**: O(n + m)

## Manacher's Algorithm

Manacher's algorithm finds the longest palindromic substring in a string in linear time O(n). It uses a clever technique of maintaining a "center" and "right boundary" to avoid redundant comparisons, making it much faster than naive approaches.

```php
# filename: manacher.php
<?php

declare(strict_types=1);

class ManacherAlgorithm {
    public static function longestPalindrome(string $s): string {
        // Transform string: "abc" -> "^#a#b#c#$"
        $t = '^#' . implode('#', str_split($s)) . '#$';
        $n = strlen($t);
        $p = array_fill(0, $n, 0);

        $center = 0;
        $right = 0;

        for ($i = 1; $i < $n - 1; $i++) {
            $mirror = 2 * $center - $i;

            if ($i < $right) {
                $p[$i] = min($right - $i, $p[$mirror]);
            }

            // Expand around center
            while ($t[$i + $p[$i] + 1] === $t[$i - $p[$i] - 1]) {
                $p[$i]++;
            }

            // Update center and right boundary
            if ($i + $p[$i] > $right) {
                $center = $i;
                $right = $i + $p[$i];
            }
        }

        // Find longest palindrome
        $maxLen = 0;
        $centerIndex = 0;

        for ($i = 1; $i < $n - 1; $i++) {
            if ($p[$i] > $maxLen) {
                $maxLen = $p[$i];
                $centerIndex = $i;
            }
        }

        $start = (int)(($centerIndex - $maxLen) / 2);
        return substr($s, $start, $maxLen);
    }

    public static function allPalindromes(string $s): array {
        $t = '^#' . implode('#', str_split($s)) . '#$';
        $n = strlen($t);
        $p = array_fill(0, $n, 0);

        $center = 0;
        $right = 0;

        for ($i = 1; $i < $n - 1; $i++) {
            $mirror = 2 * $center - $i;

            if ($i < $right) {
                $p[$i] = min($right - $i, $p[$mirror]);
            }

            while ($t[$i + $p[$i] + 1] === $t[$i - $p[$i] - 1]) {
                $p[$i]++;
            }

            if ($i + $p[$i] > $right) {
                $center = $i;
                $right = $i + $p[$i];
            }
        }

        $palindromes = [];

        for ($i = 1; $i < $n - 1; $i++) {
            if ($p[$i] > 0) {
                $start = (int)(($i - $p[$i]) / 2);
                $len = $p[$i];
                $palindromes[] = substr($s, $start, $len);
            }
        }

        return array_unique($palindromes);
    }
}

// Usage
$longest = ManacherAlgorithm::longestPalindrome('babad');
echo "Longest palindrome: $longest\n";  // 'bab' or 'aba'

$all = ManacherAlgorithm::allPalindromes('ababa');
print_r($all);  // ['a', 'b', 'aba', 'bab', 'ababa']
```

**Time Complexity**: O(n)
**Space Complexity**: O(n)

## Rabin-Karp with Rolling Hash

The Rabin-Karp algorithm uses rolling hash to efficiently search for patterns in text. Instead of comparing characters directly, it compares hash values, only verifying matches when hashes match. This provides O(n + m) average-case performance.

```php
# filename: rabin-karp.php
<?php

declare(strict_types=1);

class RabinKarp {
    private const BASE = 256;
    private const MOD = 1000000007;

    public static function search(string $text, string $pattern): array {
        $n = strlen($text);
        $m = strlen($pattern);

        if ($m > $n) {
            return [];
        }

        $positions = [];

        // Calculate hash values
        $patternHash = self::hash($pattern);
        $textHash = self::hash(substr($text, 0, $m));

        // Precompute BASE^(m-1) % MOD
        $h = 1;
        for ($i = 0; $i < $m - 1; $i++) {
            $h = ($h * self::BASE) % self::MOD;
        }

        // Slide pattern over text
        for ($i = 0; $i <= $n - $m; $i++) {
            // Check hash match
            if ($patternHash === $textHash) {
                // Verify actual string match (handle collisions)
                if (substr($text, $i, $m) === $pattern) {
                    $positions[] = $i;
                }
            }

            // Calculate rolling hash for next window
            if ($i < $n - $m) {
                $textHash = (
                    self::BASE * ($textHash - ord($text[$i]) * $h) + ord($text[$i + $m])
                ) % self::MOD;

                // Handle negative values
                if ($textHash < 0) {
                    $textHash += self::MOD;
                }
            }
        }

        return $positions;
    }

    private static function hash(string $s): int {
        $hash = 0;
        $n = strlen($s);

        for ($i = 0; $i < $n; $i++) {
            $hash = ($hash * self::BASE + ord($s[$i])) % self::MOD;
        }

        return $hash;
    }

    public static function searchMultiple(string $text, array $patterns): array {
        $results = [];

        foreach ($patterns as $pattern) {
            $results[$pattern] = self::search($text, $pattern);
        }

        return $results;
    }
}

// Usage
$text = 'abracadabra';
$positions = RabinKarp::search($text, 'abra');
print_r($positions);  // [0, 7]

$multiple = RabinKarp::searchMultiple($text, ['abra', 'cad', 'bra']);
print_r($multiple);
```

**Time Complexity**: O(n + m) average, O(nm) worst case
**Space Complexity**: O(1)

## Longest Common Subsequence (LCS)

The Longest Common Subsequence algorithm finds the longest sequence of characters that appear in the same order in two strings (not necessarily contiguous). It's widely used in version control systems (like Git's diff), DNA sequence alignment, and text comparison tools.

```php
# filename: lcs.php
<?php

declare(strict_types=1);

class LongestCommonSubsequence {
    public static function compute(string $s1, string $s2): string {
        $m = strlen($s1);
        $n = strlen($s2);

        // Build DP table
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($s1[$i - 1] === $s2[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Reconstruct LCS
        $lcs = '';
        $i = $m;
        $j = $n;

        while ($i > 0 && $j > 0) {
            if ($s1[$i - 1] === $s2[$j - 1]) {
                $lcs = $s1[$i - 1] . $lcs;
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return $lcs;
    }

    public static function length(string $s1, string $s2): int {
        return strlen(self::compute($s1, $s2));
    }

    public static function diff(string $s1, string $s2): array {
        $lcs = self::compute($s1, $s2);
        $diff = [];

        $i = 0;
        $j = 0;
        $k = 0;

        while ($i < strlen($s1) || $j < strlen($s2)) {
            if ($k < strlen($lcs) && $i < strlen($s1) && $s1[$i] === $lcs[$k]) {
                $diff[] = ['type' => 'equal', 'char' => $s1[$i]];
                $i++;
                $j++;
                $k++;
            } elseif ($i < strlen($s1) && ($k >= strlen($lcs) || $s1[$i] !== $lcs[$k])) {
                $diff[] = ['type' => 'delete', 'char' => $s1[$i]];
                $i++;
            } else {
                $diff[] = ['type' => 'insert', 'char' => $s2[$j]];
                $j++;
            }
        }

        return $diff;
    }
}

// Usage
$lcs = LongestCommonSubsequence::compute('ABCDGH', 'AEDFHR');
echo "LCS: $lcs\n";  // 'ADH'

$diff = LongestCommonSubsequence::diff('ABCD', 'ACBD');
print_r($diff);
```

**Time Complexity**: O(mn)
**Space Complexity**: O(mn)

## Unicode and Multibyte String Handling

When working with international text, proper Unicode handling is essential. PHP's default string functions operate on bytes, not characters, which can cause issues with multibyte characters like emojis, Chinese characters, or accented letters. This section covers Unicode-aware string processing techniques.

### UTF-8 String Processing

```php
# filename: unicode-processor.php
<?php

declare(strict_types=1);

class UnicodeStringProcessor {
    public static function length(string $str): int {
        return mb_strlen($str, 'UTF-8');
    }

    public static function substring(string $str, int $start, ?int $length = null): string {
        return mb_substr($str, $start, $length, 'UTF-8');
    }

    public static function position(string $haystack, string $needle, int $offset = 0): int|false {
        return mb_strpos($haystack, $needle, $offset, 'UTF-8');
    }

    public static function split(string $str): array {
        return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public static function toLowerCase(string $str): string {
        return mb_strtolower($str, 'UTF-8');
    }

    public static function toUpperCase(string $str): string {
        return mb_strtoupper($str, 'UTF-8');
    }

    // Normalize Unicode (NFC, NFD, NFKC, NFKD)
    public static function normalize(string $str, int $form = Normalizer::FORM_C): string {
        return normalizer_normalize($str, $form);
    }

    // Count grapheme clusters (visual characters)
    public static function graphemeLength(string $str): int {
        return grapheme_strlen($str);
    }

    // Extract graphemes
    public static function graphemeSubstring(string $str, int $start, ?int $length = null): string {
        return grapheme_substr($str, $start, $length);
    }
}

// Usage
$text = "Hello, 世界! 👋🌍";

echo "Byte length: " . strlen($text) . "\n";                    // 23
echo "Character length: " . UnicodeStringProcessor::length($text) . "\n";  // 15
echo "Grapheme length: " . UnicodeStringProcessor::graphemeLength($text) . "\n";  // 13

// Extract emoji
$chars = UnicodeStringProcessor::split($text);
foreach ($chars as $char) {
    echo "[$char] ";
}
```

### International Pattern Matching

```php
# filename: international-pattern-matcher.php
<?php

declare(strict_types=1);

class InternationalPatternMatcher {
    public static function caseInsensitiveSearch(string $text, string $pattern): array {
        $pattern = mb_strtolower($pattern, 'UTF-8');
        $text = mb_strtolower($text, 'UTF-8');

        $positions = [];
        $offset = 0;

        while (($pos = mb_strpos($text, $pattern, $offset, 'UTF-8')) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }

        return $positions;
    }

    public static function accentInsensitiveSearch(string $text, string $pattern): array {
        // Remove accents by decomposing and removing combining marks
        $removeAccents = function($str) {
            $str = normalizer_normalize($str, Normalizer::FORM_D);
            return preg_replace('/\p{Mn}/u', '', $str);
        };

        $normalizedText = $removeAccents(mb_strtolower($text, 'UTF-8'));
        $normalizedPattern = $removeAccents(mb_strtolower($pattern, 'UTF-8'));

        $positions = [];
        $offset = 0;

        while (($pos = mb_strpos($normalizedText, $normalizedPattern, $offset, 'UTF-8')) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }

        return $positions;
    }

    public static function fuzzyMatch(string $text, string $pattern, int $maxDistance = 2): array {
        // Simplified fuzzy matching with Levenshtein distance
        $results = [];
        $patternLen = mb_strlen($pattern, 'UTF-8');
        $textLen = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i <= $textLen - $patternLen; $i++) {
            $substring = mb_substr($text, $i, $patternLen, 'UTF-8');
            $distance = levenshtein($pattern, $substring);

            if ($distance <= $maxDistance) {
                $results[] = [
                    'position' => $i,
                    'match' => $substring,
                    'distance' => $distance
                ];
            }
        }

        return $results;
    }
}

// Usage
$text = "Café résumé naïve";

// Case-insensitive
$positions = InternationalPatternMatcher::caseInsensitiveSearch($text, "café");
print_r($positions);

// Accent-insensitive (finds "Café" when searching for "cafe")
$positions = InternationalPatternMatcher::accentInsensitiveSearch($text, "cafe");
print_r($positions);

// Fuzzy matching
$fuzzy = InternationalPatternMatcher::fuzzyMatch($text, "resume", 2);
print_r($fuzzy);  // Finds "résumé"
```

## Advanced Text Processing Examples

### 1. Sentence Segmentation

```php
# filename: sentence-segmenter.php
<?php

declare(strict_types=1);

class SentenceSegmenter {
    private array $abbreviations = ['Dr', 'Mr', 'Mrs', 'Ms', 'Prof', 'Inc', 'Ltd', 'etc'];

    public function segment(string $text): array {
        $sentences = [];
        $current = '';

        $tokens = preg_split('/([.!?]+\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 0; $i < count($tokens); $i += 2) {
            $current .= $tokens[$i];

            if (isset($tokens[$i + 1])) {
                $delimiter = $tokens[$i + 1];

                // Check if it's an abbreviation
                if ($this->isAbbreviation($current)) {
                    $current .= $delimiter;
                } else {
                    $sentences[] = trim($current);
                    $current = '';
                }
            }
        }

        if (!empty($current)) {
            $sentences[] = trim($current);
        }

        return $sentences;
    }

    private function isAbbreviation(string $text): bool {
        $words = preg_split('/\s+/', trim($text));
        $lastWord = end($words);

        return in_array(rtrim($lastWord, '.'), $this->abbreviations);
    }
}

// Usage
$text = "Dr. Smith works at Inc. Corp. He is very professional. Isn't he?";
$segmenter = new SentenceSegmenter();
$sentences = $segmenter->segment($text);

foreach ($sentences as $i => $sentence) {
    echo ($i + 1) . ". $sentence\n";
}
```

### 2. Word Tokenization with Stemming

```php
# filename: text-tokenizer.php
<?php

declare(strict_types=1);

class TextTokenizer {
    public function tokenize(string $text, bool $lowercase = true, bool $removeStopWords = true): array {
        // Split on whitespace and punctuation
        $tokens = preg_split('/[\s\p{P}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if ($lowercase) {
            $tokens = array_map(fn($t) => mb_strtolower($t, 'UTF-8'), $tokens);
        }

        if ($removeStopWords) {
            $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but'];
            $tokens = array_filter($tokens, fn($t) => !in_array($t, $stopWords));
        }

        return array_values($tokens);
    }

    public function stem(string $word): string {
        // Simplified Porter stemmer
        $word = mb_strtolower($word, 'UTF-8');

        // Remove common suffixes
        $suffixes = ['ing', 'ed', 'ly', 'ness', 'ment', 'ful', 'less', 's'];

        foreach ($suffixes as $suffix) {
            if (mb_substr($word, -mb_strlen($suffix)) === $suffix) {
                return mb_substr($word, 0, -mb_strlen($suffix));
            }
        }

        return $word;
    }

    public function tokenizeAndStem(string $text): array {
        $tokens = $this->tokenize($text);
        return array_map([$this, 'stem'], $tokens);
    }

    public function getWordFrequency(string $text): array {
        $tokens = $this->tokenizeAndStem($text);
        $frequency = array_count_values($tokens);
        arsort($frequency);

        return $frequency;
    }
}

// Usage
$text = "The running dogs were running quickly. They ran and ran.";
$tokenizer = new TextTokenizer();

$tokens = $tokenizer->tokenize($text);
echo "Tokens: " . implode(', ', $tokens) . "\n";

$stemmed = $tokenizer->tokenizeAndStem($text);
echo "Stemmed: " . implode(', ', $stemmed) . "\n";

$frequency = $tokenizer->getWordFrequency($text);
print_r($frequency);
```

### 3. Text Similarity and Comparison

```php
# filename: text-similarity.php
<?php

declare(strict_types=1);

class TextSimilarity {
    public static function jaccardSimilarity(string $text1, string $text2): float {
        $tokenizer = new TextTokenizer();

        $tokens1 = array_unique($tokenizer->tokenize($text1));
        $tokens2 = array_unique($tokenizer->tokenize($text2));

        $intersection = count(array_intersect($tokens1, $tokens2));
        $union = count(array_unique(array_merge($tokens1, $tokens2)));

        return $union > 0 ? $intersection / $union : 0;
    }

    public static function cosineSimilarity(string $text1, string $text2): float {
        $tokenizer = new TextTokenizer();

        $freq1 = $tokenizer->getWordFrequency($text1);
        $freq2 = $tokenizer->getWordFrequency($text2);

        $allWords = array_unique(array_merge(array_keys($freq1), array_keys($freq2)));

        $vec1 = [];
        $vec2 = [];

        foreach ($allWords as $word) {
            $vec1[] = $freq1[$word] ?? 0;
            $vec2[] = $freq2[$word] ?? 0;
        }

        $dotProduct = array_sum(array_map(fn($a, $b) => $a * $b, $vec1, $vec2));
        $magnitude1 = sqrt(array_sum(array_map(fn($x) => $x * $x, $vec1)));
        $magnitude2 = sqrt(array_sum(array_map(fn($x) => $x * $x, $vec2)));

        return ($magnitude1 * $magnitude2) > 0 ? $dotProduct / ($magnitude1 * $magnitude2) : 0;
    }

    public static function levenshteinSimilarity(string $text1, string $text2): float {
        $distance = levenshtein($text1, $text2);
        $maxLength = max(strlen($text1), strlen($text2));

        return $maxLength > 0 ? 1 - ($distance / $maxLength) : 1;
    }
}

// Usage
$text1 = "The quick brown fox jumps over the lazy dog";
$text2 = "A quick brown dog jumps over a lazy fox";

echo "Jaccard: " . TextSimilarity::jaccardSimilarity($text1, $text2) . "\n";
echo "Cosine: " . TextSimilarity::cosineSimilarity($text1, $text2) . "\n";
echo "Levenshtein: " . TextSimilarity::levenshteinSimilarity($text1, $text2) . "\n";
```

## Performance Benchmarks

Understanding the performance characteristics of different string algorithms helps you choose the right tool for each job. Here are benchmarks comparing various approaches:

```php
# filename: benchmarks.php
<?php

declare(strict_types=1);

class StringAlgorithmBenchmarks {
    public static function benchmarkPatternMatching(): void {
        $text = str_repeat("abcdefghijklmnopqrstuvwxyz", 10000);  // 260K characters
        $pattern = "xyz";

        // Native strpos
        $start = microtime(true);
        $pos = strpos($text, $pattern);
        $native = microtime(true) - $start;

        // KMP
        $start = microtime(true);
        $kmp = KMPAlgorithm::search($text, $pattern);
        $kmpTime = microtime(true) - $start;

        // Z-Algorithm
        $start = microtime(true);
        $z = ZAlgorithm::search($text, $pattern);
        $zTime = microtime(true) - $start;

        echo "Pattern Matching Benchmarks (text: 260K chars):\n";
        printf("strpos: %.6fs\n", $native);
        printf("KMP: %.6fs (%.2fx slower)\n", $kmpTime, $kmpTime / $native);
        printf("Z-Algorithm: %.6fs (%.2fx slower)\n", $zTime, $zTime / $native);
    }

    public static function benchmarkMultipattern(): void {
        $text = file_get_contents('large_document.txt');  // Assume large file
        $patterns = ['error', 'warning', 'critical', 'debug', 'info'];

        // Sequential search
        $start = microtime(true);
        foreach ($patterns as $pattern) {
            strpos($text, $pattern);
        }
        $sequential = microtime(true) - $start;

        // Aho-Corasick
        $ac = new AhoCorasick();
        foreach ($patterns as $pattern) {
            $ac->addPattern($pattern);
        }
        $ac->build();

        $start = microtime(true);
        $ac->search($text);
        $acTime = microtime(true) - $start;

        echo "\nMulti-pattern Matching:\n";
        printf("Sequential: %.6fs\n", $sequential);
        printf("Aho-Corasick: %.6fs (%.2fx faster)\n", $acTime, $sequential / $acTime);
    }
}

// Run benchmarks
StringAlgorithmBenchmarks::benchmarkPatternMatching();
StringAlgorithmBenchmarks::benchmarkMultipattern();
```

## Common Pitfalls

When working with string algorithms, especially in PHP, there are several common mistakes that can lead to bugs or performance issues. Here are the most important ones to avoid:

### 1. Byte vs Character Indexing

```php
// ❌ BAD: Using byte-based functions with UTF-8
$text = "Hello, 世界!";
$char = $text[7];  // Gets byte, not character!
echo $char;  // Garbled output

// ✅ GOOD: Use multibyte functions
$char = mb_substr($text, 7, 1, 'UTF-8');
echo $char;  // Correct: '世'
```

### 2. Case-Insensitive Comparison Issues

```php
// ❌ BAD: ASCII-only case conversion
$text = "CAFÉ";
$lower = strtolower($text);  // "cafÉ" - 'É' not converted!

// ✅ GOOD: Multibyte case conversion
$lower = mb_strtolower($text, 'UTF-8');  // "café"
```

### 3. Regular Expression Performance

```php
// ❌ BAD: Catastrophic backtracking
$pattern = '/^(a+)+$/';
$text = str_repeat('a', 20) . 'b';
preg_match($pattern, $text);  // Hangs!

// ✅ GOOD: Atomic grouping or possessive quantifiers
$pattern = '/^(?>a+)+$/';
preg_match($pattern, $text);  // Fast failure
```

## Wrap-up

Congratulations! You've mastered advanced string algorithms that power modern text processing systems. Here's what you've accomplished:

- ✓ Implemented Aho-Corasick automaton for efficient multi-pattern matching
- ✓ Built trie data structures for autocomplete and prefix matching
- ✓ Constructed suffix arrays and trees for fast substring queries
- ✓ Created specialized algorithms for palindromes and sequence comparison
- ✓ Handled Unicode and multibyte strings correctly
- ✓ Applied string algorithms to real-world problems like content filtering

**Key Takeaways**:

- Aho-Corasick excels at multiple pattern search in linear time
- Tries provide efficient prefix matching and autocomplete functionality
- Suffix structures enable complex substring queries efficiently
- Rolling hash provides fast average-case matching
- Manacher's algorithm finds palindromes in linear time
- Proper Unicode handling is critical for international text processing

These algorithms form the foundation of search engines, content filters, bioinformatics tools, and many other text processing applications. Understanding them gives you the tools to build efficient, scalable text processing systems.

## Further Reading

- [PHP Manual: Multibyte String Functions](https://www.php.net/manual/en/book.mbstring.php) — Comprehensive guide to Unicode string handling in PHP
- [Aho-Corasick Algorithm](https://en.wikipedia.org/wiki/Aho%E2%80%93Corasick_algorithm) — Wikipedia article with detailed explanation
- [Suffix Array Construction](https://en.wikipedia.org/wiki/Suffix_array) — Theory and applications of suffix arrays
- [Ukkonen's Algorithm](https://en.wikipedia.org/wiki/Ukkonen%27s_algorithm) — Linear-time suffix tree construction
- [Manacher's Algorithm](https://en.wikipedia.org/wiki/Longest_palindromic_substring) — Finding longest palindromic substring
- [Rabin-Karp Algorithm](https://en.wikipedia.org/wiki/Rabin%E2%80%93Karp_algorithm) — Rolling hash for string matching

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="33"
  label="String Algorithms Deep Dive complete!"
/>

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 33 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-33)**

Clone the repository to run examples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-33
php 01-*.php
```

## Practice Exercises

1. **Plagiarism Detector**: Implement a system using suffix arrays to detect similar text passages
2. **Autocomplete Engine**: Build an autocomplete system using suffix trees or tries with ranking
3. **DNA Sequence Matcher**: Create a DNA sequence matcher with Aho-Corasick for multiple patterns
4. **Diff Tool**: Implement a diff tool using LCS algorithm with contextual display
5. **Palindrome Finder**: Build a palindrome detector for large texts using Manacher's algorithm
6. **Spell Checker**: Create a spell checker with fuzzy matching and suggestions
7. **Text Summarizer**: Implement extractive text summarization using sentence ranking
8. **Language Detector**: Build a language detection system using n-grams and statistical analysis
9. **Code Syntax Highlighter**: Create a syntax highlighter using pattern matching
10. **Search Engine Indexer**: Build an inverted index for full-text search with ranking
