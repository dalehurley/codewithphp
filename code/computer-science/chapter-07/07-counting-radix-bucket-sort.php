<?php

declare(strict_types=1);

/**
 * Non-Comparison Sorts: Counting, Radix, and Bucket Sort
 *
 * Demonstrates:
 * - Counting sort for integers O(n+k)
 * - Radix sort for multi-digit numbers O(d*(n+k))
 * - Bucket sort for uniformly distributed data O(n)
 * - When non-comparison sorts outperform O(n log n)
 * - Trade-offs: speed vs space vs applicability
 */

echo "=== Non-Comparison Sorting Algorithms ===\n\n";

/**
 * Counting Sort - O(n + k) where k is range of input
 */
function countingSort(array $arr): array
{
    if (empty($arr)) return [];

    $min = min($arr);
    $max = max($arr);
    $range = $max - $min + 1;

    // Count occurrences
    $count = array_fill(0, $range, 0);

    foreach ($arr as $num) {
        $count[$num - $min]++;
    }

    // Build output array
    $output = [];

    for ($i = 0; $i < $range; $i++) {
        for ($j = 0; $j < $count[$i]; $j++) {
            $output[] = $i + $min;
        }
    }

    return $output;
}

/**
 * Counting Sort (stable version for radix sort)
 */
function countingSortStable(array &$arr, int $exp): void
{
    $n = count($arr);
    $output = array_fill(0, $n, 0);
    $count = array_fill(0, 10, 0);

    // Count occurrences of digits
    for ($i = 0; $i < $n; $i++) {
        $digit = (int)(($arr[$i] / $exp) % 10);
        $count[$digit]++;
    }

    // Change count[i] to actual position in output
    for ($i = 1; $i < 10; $i++) {
        $count[$i] += $count[$i - 1];
    }

    // Build output array (stable)
    for ($i = $n - 1; $i >= 0; $i--) {
        $digit = (int)(($arr[$i] / $exp) % 10);
        $output[$count[$digit] - 1] = $arr[$i];
        $count[$digit]--;
    }

    // Copy output to arr
    for ($i = 0; $i < $n; $i++) {
        $arr[$i] = $output[$i];
    }
}

/**
 * Radix Sort - O(d * (n + k)) where d is number of digits
 */
function radixSort(array $arr): array
{
    if (empty($arr)) return [];

    $max = max($arr);

    // Do counting sort for every digit
    for ($exp = 1; $max / $exp >= 1; $exp *= 10) {
        countingSortStable($arr, $exp);
    }

    return $arr;
}

/**
 * Bucket Sort - O(n) average for uniformly distributed data
 */
function bucketSort(array $arr): array
{
    if (empty($arr)) return [];

    $n = count($arr);
    $min = min($arr);
    $max = max($arr);

    $bucketCount = (int)sqrt($n);
    $buckets = array_fill(0, $bucketCount, []);

    // Distribute elements into buckets
    $range = $max - $min;

    foreach ($arr as $num) {
        if ($range == 0) {
            $bucketIndex = 0;
        } else {
            $bucketIndex = (int)(($num - $min) / ($range / $bucketCount + 1));
        }

        $buckets[min($bucketIndex, $bucketCount - 1)][] = $num;
    }

    // Sort individual buckets
    foreach ($buckets as &$bucket) {
        sort($bucket);
    }

    // Concatenate buckets
    $result = [];

    foreach ($buckets as $bucket) {
        foreach ($bucket as $num) {
            $result[] = $num;
        }
    }

    return $result;
}

// Test 1: Counting Sort
echo "1. Counting Sort - O(n + k)\n";
echo "============================\n";

$numbers = [4, 2, 2, 8, 3, 3, 1];
echo "Input: [" . implode(', ', $numbers) . "]\n";

$min = min($numbers);
$max = max($numbers);
echo "Range: $min to $max (k = " . ($max - $min + 1) . ")\n\n";

$sorted = countingSort($numbers);
echo "Output: [" . implode(', ', $sorted) . "]\n\n";

echo "How it works:\n";
echo "1. Count occurrences of each value\n";
echo "2. Build output array from counts\n\n";

// Test 2: Counting Sort Visualization
echo "2. Counting Sort Step-by-Step\n";
echo "===============================\n";

$arr = [3, 1, 4, 1, 5, 9, 2, 6];
echo "Input: [" . implode(', ', $arr) . "]\n\n";

$min = min($arr);
$max = max($arr);
$range = $max - $min + 1;

echo "Step 1: Count occurrences\n";
$count = array_fill(0, $range, 0);

foreach ($arr as $num) {
    $count[$num - $min]++;
}

echo "  ";
for ($i = 0; $i < $range; $i++) {
    echo "Value " . ($i + $min) . ": " . str_repeat('█', $count[$i]) . " ({$count[$i]})\n  ";
}
echo "\n";

echo "Step 2: Build sorted array\n";
$output = [];

for ($i = 0; $i < $range; $i++) {
    for ($j = 0; $j < $count[$i]; $j++) {
        $output[] = $i + $min;
    }
}

echo "  Output: [" . implode(', ', $output) . "]\n\n";

// Test 3: Radix Sort
echo "3. Radix Sort - O(d * (n + k))\n";
echo "===============================\n";

$numbers = [170, 45, 75, 90, 802, 24, 2, 66];
echo "Input: [" . implode(', ', $numbers) . "]\n\n";

echo "Sorting by digit (least significant first):\n\n";

$arr = $numbers;
$max = max($arr);

$exp = 1;
$round = 1;

while ($max / $exp >= 1) {
    echo "Round $round (digit at 10^" . (int)log10($exp) . "):\n";

    countingSortStable($arr, $exp);

    echo "  After sorting: [" . implode(', ', $arr) . "]\n\n";

    $exp *= 10;
    $round++;
}

echo "Final sorted: [" . implode(', ', $arr) . "]\n\n";

// Test 4: Bucket Sort
echo "4. Bucket Sort - O(n) average\n";
echo "==============================\n";

$numbers = [0.42, 0.32, 0.33, 0.52, 0.37, 0.47, 0.51];
echo "Input: [" . implode(', ', $numbers) . "]\n\n";

// Scale to integers for demonstration
$scaled = array_map(fn($x) => (int)($x * 100), $numbers);
echo "Scaled: [" . implode(', ', $scaled) . "]\n\n";

$sorted = bucketSort($scaled);
$result = array_map(fn($x) => $x / 100, $sorted);

echo "Output: [" . implode(', ', $result) . "]\n\n";

echo "How bucket sort works:\n";
echo "1. Create buckets for different ranges\n";
echo "2. Distribute elements into buckets\n";
echo "3. Sort each bucket individually\n";
echo "4. Concatenate buckets\n\n";

// Test 5: Performance Comparison
echo "5. Performance Comparison\n";
echo "==========================\n";

$testSize = 10000;
$testArr = [];

for ($i = 0; $i < $testSize; $i++) {
    $testArr[] = random_int(0, 999);
}

// Counting sort
$arr1 = $testArr;
$start = microtime(true);
$sorted1 = countingSort($arr1);
$countingTime = (microtime(true) - $start) * 1000;

// Radix sort
$arr2 = $testArr;
$start = microtime(true);
$sorted2 = radixSort($arr2);
$radixTime = (microtime(true) - $start) * 1000;

// Quick sort (PHP's built-in)
$arr3 = $testArr;
$start = microtime(true);
sort($arr3);
$quickTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements (0-999 range)\n\n";
echo "Counting Sort: " . number_format($countingTime, 3) . " ms\n";
echo "Radix Sort:    " . number_format($radixTime, 3) . " ms\n";
echo "Quick Sort:    " . number_format($quickTime, 3) . " ms\n\n";

$fastest = min($countingTime, $radixTime, $quickTime);
if ($fastest === $countingTime) {
    echo "Counting sort wins! (small range)\n";
} elseif ($fastest === $radixTime) {
    echo "Radix sort wins! (integer data)\n";
} else {
    echo "Quick sort wins! (general purpose)\n";
}

echo "\n";

// Test 6: When to Use Each Sort
echo "6. When to Use Non-Comparison Sorts\n";
echo "=====================================\n\n";

// Small range integers
echo "Scenario 1: Small Range Integers\n";
$arr = array_fill(0, 1000, 0);
for ($i = 0; $i < 1000; $i++) {
    $arr[$i] = random_int(0, 10);
}

$start = microtime(true);
countingSort($arr);
$countTime = (microtime(true) - $start) * 1000;

$arr2 = $arr;
$start = microtime(true);
sort($arr2);
$sortTime = (microtime(true) - $start) * 1000;

echo "  1000 elements, range 0-10\n";
echo "  Counting: " . number_format($countTime, 3) . " ms\n";
echo "  Quick:    " . number_format($sortTime, 3) . " ms\n";
echo "  → Counting sort is " . number_format($sortTime / $countTime, 1) . "x faster!\n\n";

// Large integers
echo "Scenario 2: Large Multi-Digit Integers\n";
$arr = array_fill(0, 1000, 0);
for ($i = 0; $i < 1000; $i++) {
    $arr[$i] = random_int(1000000, 9999999);
}

$start = microtime(true);
radixSort($arr);
$radixTime = (microtime(true) - $start) * 1000;

$arr2 = $arr;
$start = microtime(true);
sort($arr2);
$sortTime = (microtime(true) - $start) * 1000;

echo "  1000 elements, 7-digit integers\n";
echo "  Radix: " . number_format($radixTime, 3) . " ms\n";
echo "  Quick: " . number_format($sortTime, 3) . " ms\n";
echo "  → Radix sort competitive with quick sort!\n\n";

// Test 7: Stability
echo "7. Stability of Non-Comparison Sorts\n";
echo "======================================\n";

class Item
{
    public function __construct(
        public int $key,
        public string $id
    ) {}

    public function __toString(): string
    {
        return "{$this->key}({$this->id})";
    }
}

// Custom counting sort for objects
function countingSortObjects(array $items): array
{
    if (empty($items)) return [];

    $min = min(array_map(fn($item) => $item->key, $items));
    $max = max(array_map(fn($item) => $item->key, $items));
    $range = $max - $min + 1;

    $buckets = array_fill(0, $range, []);

    foreach ($items as $item) {
        $buckets[$item->key - $min][] = $item;
    }

    $result = [];

    foreach ($buckets as $bucket) {
        foreach ($bucket as $item) {
            $result[] = $item;
        }
    }

    return $result;
}

$items = [
    new Item(3, 'A'),
    new Item(1, 'B'),
    new Item(3, 'C'),
    new Item(2, 'D'),
    new Item(3, 'E'),
];

echo "Input: ";
foreach ($items as $item) {
    echo "$item ";
}
echo "\n";

$sorted = countingSortObjects($items);

echo "Output: ";
foreach ($sorted as $item) {
    echo "$item ";
}
echo "\n";
echo "Notice: Items with key 3 maintain order A→C→E (stable!)\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "Counting Sort:\n";
echo "  Time: O(n + k) where k is range\n";
echo "  Space: O(k)\n";
echo "  ✓ Stable\n";
echo "  ✓ Fast when k is small\n";
echo "  ✗ Requires integer keys in known range\n";
echo "  ✗ Inefficient when k >> n\n\n";

echo "Radix Sort:\n";
echo "  Time: O(d * (n + k)) where d is digits\n";
echo "  Space: O(n + k)\n";
echo "  ✓ Stable\n";
echo "  ✓ Excellent for fixed-length integer keys\n";
echo "  ✓ Can handle large ranges efficiently\n";
echo "  ✗ Only works for integers/strings\n";
echo "  ✗ Constant factors higher than quick sort\n\n";

echo "Bucket Sort:\n";
echo "  Time: O(n) average when uniformly distributed\n";
echo "  Space: O(n)\n";
echo "  ✓ Can be stable (depends on bucket sort)\n";
echo "  ✓ Excellent for uniformly distributed data\n";
echo "  ✗ Degrades to O(n²) if all in one bucket\n";
echo "  ✗ Requires knowledge of distribution\n\n";

echo "When to Use:\n\n";

echo "Counting Sort:\n";
echo "  ✓ Integer keys with small range (k ≈ n)\n";
echo "  ✓ Need stable sort\n";
echo "  ✓ Histogram/frequency counting\n";
echo "  Example: Sorting ages (0-120), grades (0-100)\n\n";

echo "Radix Sort:\n";
echo "  ✓ Fixed-length integers (phone numbers, IDs)\n";
echo "  ✓ Large integers (millions+)\n";
echo "  ✓ String sorting (lexicographic)\n";
echo "  Example: Sorting credit card numbers, zip codes\n\n";

echo "Bucket Sort:\n";
echo "  ✓ Uniformly distributed floating-point data\n";
echo "  ✓ Data with known distribution\n";
echo "  ✓ External sorting (large datasets)\n";
echo "  Example: Sorting scores 0.0-1.0, timestamps\n\n";

echo "Comparison with O(n log n) Sorts:\n";
echo "  • Non-comparison sorts can be faster than O(n log n)\n";
echo "  • But they have restrictions on input type/range\n";
echo "  • Quick/merge/heap sort work on any comparable data\n";
echo "  • Choose based on your data characteristics!\n\n";

echo "Real-World Applications:\n";
echo "  • Counting sort: Histogram equalization, voting systems\n";
echo "  • Radix sort: Sorting IP addresses, database indexes\n";
echo "  • Bucket sort: Sorting timestamps, distributed systems\n";
echo "  • Used in hybrid algorithms (Tim Sort uses counting for small ranges)\n";

echo "\n✅ Complete! Non-comparison sorts demonstrated.\n";
