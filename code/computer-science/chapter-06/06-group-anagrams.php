<?php

declare(strict_types=1);

/**
 * Group Anagrams - Advanced Hash Table Problem
 *
 * Demonstrates:
 * - Using sorted strings as hash keys
 * - Grouping elements by pattern
 * - Hash table for pattern matching
 * - Time: O(n * k log k) where n = words, k = avg length
 */

echo "=== Group Anagrams ===\n\n";

echo "Problem: Group words that are anagrams of each other\n";
echo "Example: ['eat', 'tea', 'tan', 'ate', 'nat', 'bat']\n";
echo "Output: [['eat', 'tea', 'ate'], ['tan', 'nat'], ['bat']]\n\n";

/**
 * Group anagrams using sorted string as key
 */
function groupAnagrams(array $words): array
{
    $groups = [];

    foreach ($words as $word) {
        // Sort characters to create a key
        $chars = str_split($word);
        sort($chars);
        $key = implode('', $chars);

        if (!isset($groups[$key])) {
            $groups[$key] = [];
        }

        $groups[$key][] = $word;
    }

    return array_values($groups);
}

/**
 * Group anagrams using character frequency as key (faster)
 */
function groupAnagramsFrequency(array $words): array
{
    $groups = [];

    foreach ($words as $word) {
        // Create frequency signature
        $freq = array_fill(0, 26, 0);
        $len = strlen($word);

        for ($i = 0; $i < $len; $i++) {
            $freq[ord($word[$i]) - ord('a')]++;
        }

        $key = implode(',', $freq);

        if (!isset($groups[$key])) {
            $groups[$key] = [];
        }

        $groups[$key][] = $word;
    }

    return array_values($groups);
}

/**
 * Find all anagrams of a word in a list
 */
function findAnagrams(string $word, array $words): array
{
    $anagrams = [];
    $chars = str_split($word);
    sort($chars);
    $pattern = implode('', $chars);

    foreach ($words as $candidate) {
        $candChars = str_split($candidate);
        sort($candChars);
        $candPattern = implode('', $candChars);

        if ($pattern === $candPattern && $candidate !== $word) {
            $anagrams[] = $candidate;
        }
    }

    return $anagrams;
}

/**
 * Count anagram groups
 */
function countAnagramGroups(array $words): int
{
    return count(groupAnagrams($words));
}

// Test 1: Basic Grouping
echo "1. Basic Anagram Grouping\n";
echo "===========================\n";
$words1 = ['eat', 'tea', 'tan', 'ate', 'nat', 'bat'];

echo "Input: [" . implode(', ', $words1) . "]\n\n";

$groups = groupAnagrams($words1);
echo "Grouped anagrams:\n";
foreach ($groups as $i => $group) {
    echo "  Group " . ($i + 1) . ": [" . implode(', ', $group) . "]\n";
}
echo "\n";

// Test 2: Larger Example
echo "2. Larger Example\n";
echo "==================\n";
$words2 = [
    'listen', 'silent', 'enlist', 'inlets', 'tinsel',
    'hello', 'world', 'olleh',
    'abc', 'bca', 'cab', 'xyz'
];

echo "Input words: " . count($words2) . " total\n\n";

$groups2 = groupAnagrams($words2);
echo "Found " . count($groups2) . " anagram groups:\n";
foreach ($groups2 as $i => $group) {
    echo "  Group " . ($i + 1) . " (" . count($group) . " words): [" . implode(', ', $group) . "]\n";
}
echo "\n";

// Test 3: Find Specific Anagrams
echo "3. Find Anagrams of Specific Word\n";
echo "===================================\n";
$target = 'listen';
$dictionary = ['silent', 'hello', 'enlist', 'world', 'inlets', 'xyz'];

echo "Find anagrams of '$target' in dictionary\n";
echo "Dictionary: [" . implode(', ', $dictionary) . "]\n\n";

$found = findAnagrams($target, $dictionary);
echo "Anagrams found: [" . implode(', ', $found) . "]\n\n";

// Test 4: Performance Comparison
echo "4. Performance Comparison\n";
echo "==========================\n";

$words3 = [];
for ($i = 0; $i < 1000; $i++) {
    // Generate random 5-letter strings
    $word = '';
    for ($j = 0; $j < 5; $j++) {
        $word .= chr(ord('a') + random_int(0, 25));
    }
    $words3[] = $word;
}

echo "Testing with " . count($words3) . " random words\n\n";

$start = microtime(true);
$groups3a = groupAnagrams($words3);
$time1 = (microtime(true) - $start) * 1000;

$start = microtime(true);
$groups3b = groupAnagramsFrequency($words3);
$time2 = (microtime(true) - $start) * 1000;

echo "Sorting method:   " . number_format($time1, 3) . " ms (" . count($groups3a) . " groups)\n";
echo "Frequency method: " . number_format($time2, 3) . " ms (" . count($groups3b) . " groups)\n";
echo "Speedup: " . number_format($time1 / $time2, 2) . "x\n\n";

// Test 5: Edge Cases
echo "5. Edge Cases\n";
echo "==============\n";

$edgeCases = [
    'Empty array' => [],
    'Single word' => ['hello'],
    'No anagrams' => ['abc', 'def', 'ghi'],
    'All anagrams' => ['abc', 'bca', 'cab', 'acb'],
    'Single char' => ['a', 'b', 'a', 'c', 'b'],
];

foreach ($edgeCases as $name => $words) {
    $groups = groupAnagrams($words);
    echo "  $name:\n";
    echo "    Input: [" . implode(', ', $words) . "]\n";
    echo "    Groups: " . count($groups) . "\n";
}

echo "\n6. Key Insights\n";
echo "================\n";
echo "Anagram grouping uses hash tables for O(n * k log k) performance:\n";
echo "  • Sorted string as key: O(k log k) per word for sorting\n";
echo "  • Frequency signature: O(k) per word (faster)\n";
echo "  • Hash lookup: O(1) per word\n";
echo "  • Without hash table: O(n²) comparisons needed\n\n";

echo "Pattern: Transform elements to canonical form (sorted/frequency)\n";
echo "           → Use as hash key → Group by key\n";

echo "\n✅ Complete! Anagram grouping demonstrated.\n";
