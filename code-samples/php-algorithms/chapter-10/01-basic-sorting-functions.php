<?php

declare(strict_types=1);

/**
 * PHP Built-in Sorting Functions - Basic Usage
 * Demonstrates sort(), rsort(), asort(), arsort(), ksort(), krsort()
 */

echo "PHP BUILT-IN SORTING FUNCTIONS\n";
echo str_repeat('=', 70) . "\n\n";

// sort() - Sort values, reset keys
echo "1. sort() - Sort by Values, Reset Keys\n";
echo str_repeat('-', 70) . "\n";
$numbers = [3, 1, 4, 1, 5, 9, 2, 6];
echo "Before: " . json_encode($numbers) . "\n";
sort($numbers);
echo "After:  " . json_encode($numbers) . "\n";
echo "Use: Indexed arrays\n\n";

// rsort() - Reverse sort
echo "2. rsort() - Reverse Sort\n";
echo str_repeat('-', 70) . "\n";
$numbers = [3, 1, 4, 1, 5, 9];
echo "Before: " . json_encode($numbers) . "\n";
rsort($numbers);
echo "After:  " . json_encode($numbers) . "\n\n";

// asort() - Sort values, maintain keys
echo "3. asort() - Sort by Values, Maintain Keys\n";
echo str_repeat('-', 70) . "\n";
$ages = [
    'Alice' => 30,
    'Bob' => 25,
    'Charlie' => 35
];
echo "Before: " . json_encode($ages) . "\n";
asort($ages);
echo "After:  " . json_encode($ages) . "\n";
echo "Use: Associative arrays\n\n";

// arsort() - Reverse sort, maintain keys
echo "4. arsort() - Reverse Sort, Maintain Keys\n";
echo str_repeat('-', 70) . "\n";
$scores = [
    'Player1' => 100,
    'Player2' => 150,
    'Player3' => 75
];
echo "Before: " . json_encode($scores) . "\n";
arsort($scores);
echo "After:  " . json_encode($scores) . "\n\n";

// ksort() - Sort by keys
echo "5. ksort() - Sort by Keys\n";
echo str_repeat('-', 70) . "\n";
$data = [
    'z' => 1,
    'a' => 2,
    'm' => 3
];
echo "Before: " . json_encode($data) . "\n";
ksort($data);
echo "After:  " . json_encode($data) . "\n\n";

// krsort() - Reverse sort by keys
echo "6. krsort() - Reverse Sort by Keys\n";
echo str_repeat('-', 70) . "\n";
$months = [
    'March' => 3,
    'January' => 1,
    'February' => 2
];
echo "Before: " . json_encode($months) . "\n";
krsort($months);
echo "After:  " . json_encode($months) . "\n\n";

// Sort flags
echo "7. Sort Flags - SORT_NUMERIC vs SORT_STRING\n";
echo str_repeat('-', 70) . "\n";
$numbers = ['10', '2', '1', '20'];

$test = $numbers;
sort($test);
echo "String sort:  " . json_encode($test) . "\n";

$test = $numbers;
sort($test, SORT_NUMERIC);
echo "Numeric sort: " . json_encode($test) . "\n\n";

// Natural sorting
echo "8. natsort() - Natural Order Sorting\n";
echo str_repeat('-', 70) . "\n";
$files = ['file1.txt', 'file10.txt', 'file2.txt', 'file20.txt'];

$test = $files;
sort($test);
echo "Regular sort: " . json_encode($test) . "\n";

$test = $files;
natsort($test);
echo "Natural sort: " . json_encode(array_values($test)) . "\n\n";

echo "DECISION TREE:\n";
echo str_repeat('-', 70) . "\n";
echo "Sort by VALUES:\n";
echo "  Keep keys? → YES: asort() or arsort()\n";
echo "            → NO:  sort() or rsort()\n\n";
echo "Sort by KEYS:\n";
echo "  Ascending  → ksort()\n";
echo "  Descending → krsort()\n\n";
echo "Natural order (file1, file2, file10):\n";
echo "  Use natsort() or natcasesort()\n";
