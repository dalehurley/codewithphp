<?php

declare(strict_types=1);

/**
 * Frequency Counter Pattern with Hash Tables
 *
 * Demonstrates:
 * - Counting character/element frequencies
 * - Anagram detection
 * - Most frequent element
 * - Frequency-based problems
 */

echo "=== Frequency Counter Pattern ===\n\n";

/**
 * Count character frequencies in a string
 */
function charFrequency(string $str): array
{
    $freq = [];
    $len = strlen($str);

    for ($i = 0; $i < $len; $i++) {
        $char = $str[$i];
        if (!isset($freq[$char])) {
            $freq[$char] = 0;
        }
        $freq[$char]++;
    }

    return $freq;
}

/**
 * Check if two strings are anagrams - O(n)
 */
function areAnagrams(string $str1, string $str2): bool
{
    if (strlen($str1) !== strlen($str2)) {
        return false;
    }

    $freq1 = charFrequency($str1);
    $freq2 = charFrequency($str2);

    return $freq1 === $freq2;
}

/**
 * Find first non-repeating character - O(n)
 */
function firstNonRepeating(string $str): ?string
{
    $freq = charFrequency($str);

    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        if ($freq[$str[$i]] === 1) {
            return $str[$i];
        }
    }

    return null;
}

/**
 * Find most frequent element in array - O(n)
 */
function mostFrequent(array $arr): mixed
{
    $freq = [];
    $maxCount = 0;
    $mostFreq = null;

    foreach ($arr as $item) {
        if (!isset($freq[$item])) {
            $freq[$item] = 0;
        }
        $freq[$item]++;

        if ($freq[$item] > $maxCount) {
            $maxCount = $freq[$item];
            $mostFreq = $item;
        }
    }

    return $mostFreq;
}

/**
 * Check if array contains duplicates - O(n)
 */
function hasDuplicates(array $arr): bool
{
    $seen = [];

    foreach ($arr as $item) {
        if (isset($seen[$item])) {
            return true;
        }
        $seen[$item] = true;
    }

    return false;
}

/**
 * Find all duplicates in array - O(n)
 */
function findDuplicates(array $arr): array
{
    $freq = [];
    $duplicates = [];

    foreach ($arr as $item) {
        if (!isset($freq[$item])) {
            $freq[$item] = 0;
        }
        $freq[$item]++;
    }

    foreach ($freq as $item => $count) {
        if ($count > 1) {
            $duplicates[] = $item;
        }
    }

    return $duplicates;
}

/**
 * Check if frequency of all characters is same
 */
function sameFrequency(string $str): bool
{
    $freq = charFrequency($str);

    if (empty($freq)) {
        return true;
    }

    $firstFreq = reset($freq);

    foreach ($freq as $count) {
        if ($count !== $firstFreq) {
            return false;
        }
    }

    return true;
}

// Test 1: Character Frequency
echo "1. Character Frequency Count\n";
echo "==============================\n";
$text = "hello world";
$freq = charFrequency($text);

echo "Text: '$text'\n";
echo "Frequencies:\n";
foreach ($freq as $char => $count) {
    $displayChar = $char === ' ' ? '[space]' : $char;
    echo "  '$displayChar': $count\n";
}
echo "\n";

// Test 2: Anagram Detection
echo "2. Anagram Detection\n";
echo "=====================\n";
$pairs = [
    ['listen', 'silent'],
    ['hello', 'world'],
    ['evil', 'vile'],
    ['a gentleman', 'elegant man'],
];

foreach ($pairs as [$str1, $str2]) {
    $isAnagram = areAnagrams($str1, $str2);
    $result = $isAnagram ? "✓ Anagrams" : "✗ Not anagrams";
    echo "  '$str1' & '$str2': $result\n";
}
echo "\n";

// Test 3: First Non-Repeating Character
echo "3. First Non-Repeating Character\n";
echo "=================================\n";
$strings = ['swiss', 'aabbccde', 'aabbcc'];

foreach ($strings as $str) {
    $char = firstNonRepeating($str);
    $result = $char !== null ? "'$char'" : "none";
    echo "  '$str' → $result\n";
}
echo "\n";

// Test 4: Most Frequent Element
echo "4. Most Frequent Element\n";
echo "=========================\n";
$arrays = [
    [1, 2, 3, 2, 2, 4],
    ['apple', 'banana', 'apple', 'cherry', 'apple'],
    [5, 5, 3, 3, 3, 1, 1, 1, 1],
];

foreach ($arrays as $arr) {
    $mostFreq = mostFrequent($arr);
    echo "  [" . implode(', ', $arr) . "]\n";
    echo "  → Most frequent: $mostFreq\n\n";
}

// Test 5: Duplicate Detection
echo "5. Duplicate Detection\n";
echo "=======================\n";
$testArrays = [
    [1, 2, 3, 4, 5],
    [1, 2, 3, 2, 4],
    ['a', 'b', 'c', 'd'],
    ['a', 'b', 'a', 'c'],
];

foreach ($testArrays as $arr) {
    $hasDups = hasDuplicates($arr);
    $dups = findDuplicates($arr);

    echo "  [" . implode(', ', $arr) . "]\n";
    echo "  → Has duplicates: " . ($hasDups ? "Yes" : "No") . "\n";

    if (!empty($dups)) {
        echo "  → Duplicates: [" . implode(', ', $dups) . "]\n";
    }
    echo "\n";
}

// Test 6: Same Frequency Check
echo "6. Same Frequency Check\n";
echo "========================\n";
$testStrings = ['aabbcc', 'aabbccc', 'xyz'];

foreach ($testStrings as $str) {
    $same = sameFrequency($str);
    $result = $same ? "✓ All chars have same frequency" : "✗ Different frequencies";
    echo "  '$str': $result\n";
}

echo "\n7. Performance Insights\n";
echo "========================\n";
echo "All operations use hash tables for O(n) time complexity:\n";
echo "  • Character frequency: Single pass through string\n";
echo "  • Anagram check: Compare two frequency maps\n";
echo "  • Duplicates: Track seen elements in hash set\n";
echo "  • Most frequent: Update max while building frequency map\n\n";

echo "Without hash tables, many would be O(n²) or require sorting O(n log n).\n";

echo "\n✅ Complete! Frequency counter patterns demonstrated.\n";
