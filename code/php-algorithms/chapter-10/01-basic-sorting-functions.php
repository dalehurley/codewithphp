<?php

declare(strict_types=1);

/**
 * Chapter 10: PHP's Built-in Sorting Functions
 * 
 * Demonstrates all basic PHP sorting functions including:
 * - sort(), rsort() - Sort by value, reset keys
 * - asort(), arsort() - Sort by value, maintain keys
 * - ksort(), krsort() - Sort by keys
 * - natsort(), natcasesort() - Natural sorting
 * - Sort flags and their effects
 */

echo "=== PHP Built-in Sorting Functions Demo ===\n\n";

// ============================================================================
// 1. sort() - Sort by Value, Reset Keys (Ascending)
// ============================================================================

echo "1. sort() - Sort by value, reset keys\n";
echo str_repeat("-", 50) . "\n";

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
echo "Original: " . json_encode($numbers) . "\n";
sort($numbers);
echo "After sort(): " . json_encode($numbers) . "\n";

$fruits = ['orange', 'apple', 'banana', 'cherry'];
echo "Original: " . json_encode($fruits) . "\n";
sort($fruits);
echo "After sort(): " . json_encode($fruits) . "\n\n";

// ============================================================================
// 2. rsort() - Reverse Sort by Value
// ============================================================================

echo "2. rsort() - Reverse sort\n";
echo str_repeat("-", 50) . "\n";

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
echo "Original: " . json_encode($numbers) . "\n";
rsort($numbers);
echo "After rsort(): " . json_encode($numbers) . "\n\n";

// ============================================================================
// 3. asort() - Sort by Value, Maintain Keys (Ascending)
// ============================================================================

echo "3. asort() - Sort by value, maintain keys\n";
echo str_repeat("-", 50) . "\n";

$ages = [
    'Alice' => 30,
    'Bob' => 25,
    'Charlie' => 35,
    'Diana' => 28
];

echo "Original:\n";
print_r($ages);

asort($ages);
echo "After asort():\n";
print_r($ages);

// ============================================================================
// 4. arsort() - Reverse Sort by Value, Maintain Keys
// ============================================================================

echo "4. arsort() - Reverse sort, maintain keys\n";
echo str_repeat("-", 50) . "\n";

$scores = [
    'Player1' => 100,
    'Player2' => 150,
    'Player3' => 75,
    'Player4' => 120
];

echo "Original:\n";
print_r($scores);

arsort($scores);
echo "After arsort():\n";
print_r($scores);

// ============================================================================
// 5. ksort() - Sort by Key (Ascending)
// ============================================================================

echo "5. ksort() - Sort by key (ascending)\n";
echo str_repeat("-", 50) . "\n";

$data = [
    'z' => 1,
    'a' => 2,
    'm' => 3,
    'b' => 4
];

echo "Original:\n";
print_r($data);

ksort($data);
echo "After ksort():\n";
print_r($data);

// ============================================================================
// 6. krsort() - Sort by Key (Descending)
// ============================================================================

echo "6. krsort() - Sort by key (descending)\n";
echo str_repeat("-", 50) . "\n";

$months = [
    'March' => 3,
    'January' => 1,
    'February' => 2,
    'December' => 12
];

echo "Original:\n";
print_r($months);

krsort($months);
echo "After krsort():\n";
print_r($months);

// ============================================================================
// 7. Sort Flags - Different Comparison Methods
// ============================================================================

echo "7. Sort Flags - Different comparison methods\n";
echo str_repeat("-", 50) . "\n";

// Numeric strings
$numbers = ['10', '2', '1', '20', '3'];
echo "Numeric strings: " . json_encode($numbers) . "\n";

$test1 = $numbers;
sort($test1);
echo "sort() (default): " . json_encode($test1) . " [string comparison]\n";

$test2 = $numbers;
sort($test2, SORT_NUMERIC);
echo "sort(SORT_NUMERIC): " . json_encode($test2) . " [numeric comparison]\n\n";

// Case sensitivity
$words = ['Banana', 'apple', 'Cherry', 'date'];
echo "Mixed case: " . json_encode($words) . "\n";

$test3 = $words;
sort($test3);
echo "sort() (default): " . json_encode($test3) . " [case-sensitive]\n";

$test4 = $words;
sort($test4, SORT_STRING | SORT_FLAG_CASE);
echo "sort(SORT_STRING | SORT_FLAG_CASE): " . json_encode($test4) . " [case-insensitive]\n\n";

// ============================================================================
// 8. natsort() - Natural Order Sorting
// ============================================================================

echo "8. natsort() - Natural order sorting\n";
echo str_repeat("-", 50) . "\n";

$files = ['file1.txt', 'file10.txt', 'file2.txt', 'file20.txt', 'file3.txt'];
echo "Original: " . json_encode($files) . "\n";

$test5 = $files;
sort($test5);
echo "sort(): " . json_encode(array_values($test5)) . " [wrong order]\n";

$test6 = $files;
natsort($test6);
echo "natsort(): " . json_encode(array_values($test6)) . " [correct human order]\n\n";

// ============================================================================
// 9. natcasesort() - Case-Insensitive Natural Order
// ============================================================================

echo "9. natcasesort() - Case-insensitive natural order\n";
echo str_repeat("-", 50) . "\n";

$files2 = ['File1.txt', 'file10.txt', 'File2.txt', 'file20.txt'];
echo "Original: " . json_encode($files2) . "\n";

natcasesort($files2);
echo "natcasesort(): " . json_encode(array_values($files2)) . "\n\n";

// ============================================================================
// 10. Real-World Example: Sorting User Data
// ============================================================================

echo "10. Real-World Example: Sorting User Data\n";
echo str_repeat("-", 50) . "\n";

$users = [
    'user_3' => ['name' => 'Charlie', 'age' => 30, 'score' => 85],
    'user_1' => ['name' => 'Alice', 'age' => 25, 'score' => 92],
    'user_2' => ['name' => 'Bob', 'age' => 35, 'score' => 78],
];

echo "Original (by user ID):\n";
foreach ($users as $id => $user) {
    echo "  $id: {$user['name']} (age {$user['age']}, score {$user['score']})\n";
}

// Sort by user ID (key)
ksort($users);
echo "\nSorted by user ID (ksort):\n";
foreach ($users as $id => $user) {
    echo "  $id: {$user['name']} (age {$user['age']}, score {$user['score']})\n";
}

// ============================================================================
// 11. Performance Comparison
// ============================================================================

echo "\n11. Performance Comparison\n";
echo str_repeat("-", 50) . "\n";

$testSize = 10000;
$testData = range(1, $testSize);
shuffle($testData);

// Test sort()
$data1 = $testData;
$start = microtime(true);
sort($data1);
$time1 = (microtime(true) - $start) * 1000;

// Test asort()
$data2 = $testData;
$start = microtime(true);
asort($data2);
$time2 = (microtime(true) - $start) * 1000;

// Test rsort()
$data3 = $testData;
$start = microtime(true);
rsort($data3);
$time3 = (microtime(true) - $start) * 1000;

echo "Sorting {$testSize} elements:\n";
echo sprintf("  sort():  %.3f ms\n", $time1);
echo sprintf("  asort(): %.3f ms (%.1fx slower - key preservation overhead)\n", $time2, $time2 / $time1);
echo sprintf("  rsort(): %.3f ms (%.1fx)\n", $time3, $time3 / $time1);

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- sort()/rsort(): Fast, resets keys\n";
echo "- asort()/arsort(): Maintains keys (associative arrays)\n";
echo "- ksort()/krsort(): Sort by keys\n";
echo "- natsort()/natcasesort(): Human-friendly number sorting\n";
echo "- All functions modify arrays in place\n";
echo "- Use sort flags for different comparison types\n";
echo str_repeat("=", 50) . "\n";


