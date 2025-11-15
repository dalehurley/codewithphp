<?php

declare(strict_types=1);

/**
 * Hash Function Basics
 *
 * Demonstrates:
 * - Simple hash functions
 * - Hash function properties
 * - Collision examples
 * - Distribution testing
 */

echo "=== Hash Function Basics ===\n\n";

// Simple hash function for strings (djb2 algorithm)
function hashString(string $str, int $tableSize = 10): int
{
    $hash = 5381;
    $len = strlen($str);

    for ($i = 0; $i < $len; $i++) {
        $hash = (($hash << 5) + $hash) + ord($str[$i]);
    }

    return abs($hash) % $tableSize;
}

// Simple modulo hash for integers
function hashInt(int $value, int $tableSize = 10): int
{
    return abs($value) % $tableSize;
}

// Division method
function hashDivision(int $key, int $tableSize): int
{
    return $key % $tableSize;
}

// Multiplication method
function hashMultiplication(int $key, int $tableSize): int
{
    $A = 0.6180339887; // (sqrt(5) - 1) / 2
    $fractional = ($key * $A) - floor($key * $A);
    return (int)floor($tableSize * $fractional);
}

echo "1. String Hashing (djb2)\n";
echo "=========================\n";
$words = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
$tableSize = 10;

foreach ($words as $word) {
    $hash = hashString($word, $tableSize);
    echo "  '$word' → hash($word) = $hash\n";
}
echo "\n";

echo "2. Integer Hashing\n";
echo "===================\n";
$numbers = [42, 123, 7, 999, 1024];

foreach ($numbers as $num) {
    $hashMod = hashInt($num, $tableSize);
    $hashDiv = hashDivision($num, $tableSize);
    $hashMult = hashMultiplication($num, $tableSize);

    echo "  $num:\n";
    echo "    Modulo:         $hashMod\n";
    echo "    Division:       $hashDiv\n";
    echo "    Multiplication: $hashMult\n";
}
echo "\n";

echo "3. Collision Detection\n";
echo "=======================\n";
$testWords = ['listen', 'silent', 'enlist', 'inlets', 'tinsel'];

echo "Testing words: " . implode(', ', $testWords) . "\n";
echo "Hash table size: $tableSize\n\n";

$hashCounts = [];
foreach ($testWords as $word) {
    $hash = hashString($word, $tableSize);
    if (!isset($hashCounts[$hash])) {
        $hashCounts[$hash] = [];
    }
    $hashCounts[$hash][] = $word;
}

echo "Results:\n";
foreach ($hashCounts as $hash => $words) {
    $collisionMarker = count($words) > 1 ? " ⚠️ COLLISION!" : "";
    echo "  Bucket $hash: [" . implode(', ', $words) . "]$collisionMarker\n";
}
echo "\n";

echo "4. Distribution Quality Test\n";
echo "=============================\n";

// Test distribution with 100 random strings
$buckets = array_fill(0, $tableSize, 0);
for ($i = 0; $i < 100; $i++) {
    $randomStr = 'str_' . $i;
    $hash = hashString($randomStr, $tableSize);
    $buckets[$hash]++;
}

echo "Distribution of 100 random strings across $tableSize buckets:\n";
for ($i = 0; $i < $tableSize; $i++) {
    $bar = str_repeat('█', $buckets[$i]);
    echo "  Bucket $i: $bar ({$buckets[$i]})\n";
}

$avg = array_sum($buckets) / count($buckets);
$variance = 0;
foreach ($buckets as $count) {
    $variance += pow($count - $avg, 2);
}
$variance /= count($buckets);

echo "\n  Average per bucket: " . number_format($avg, 2) . "\n";
echo "  Variance: " . number_format($variance, 2) . "\n";
echo "  Good distribution has low variance (close to 0)\n";

echo "\n5. Hash Function Properties\n";
echo "============================\n";
echo "A good hash function should:\n";
echo "  ✓ Be deterministic (same input → same output)\n";
echo "  ✓ Distribute keys uniformly\n";
echo "  ✓ Be fast to compute (O(1) or O(k) where k = key length)\n";
echo "  ✓ Minimize collisions\n\n";

// Test determinism
$testStr = "testing";
$hash1 = hashString($testStr, $tableSize);
$hash2 = hashString($testStr, $tableSize);
$hash3 = hashString($testStr, $tableSize);

echo "Determinism test for '$testStr':\n";
echo "  Hash 1: $hash1\n";
echo "  Hash 2: $hash2\n";
echo "  Hash 3: $hash3\n";
echo "  " . ($hash1 === $hash2 && $hash2 === $hash3 ? "✓ Deterministic" : "✗ Not deterministic") . "\n";

echo "\n✅ Complete! Hash function basics demonstrated.\n";
