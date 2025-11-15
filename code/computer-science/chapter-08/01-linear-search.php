<?php

declare(strict_types=1);

/**
 * Linear Search - O(n) Sequential Search
 *
 * Demonstrates:
 * - Basic linear search implementation
 * - Linear search variants (first, last, all occurrences)
 * - Sentinel linear search optimization
 * - Search in unsorted data
 * - When linear search is appropriate
 */

echo "=== Linear Search ===\n\n";

echo "Concept: Check each element sequentially until found\n";
echo "         Works on both sorted and unsorted data\n";
echo "         O(n) time complexity\n\n";

/**
 * Basic linear search
 */
function linearSearch(array $arr, mixed $target): ?int
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return null;
}

/**
 * Linear search with comparison count
 */
function linearSearchWithStats(array $arr, mixed $target): array
{
    $comparisons = 0;
    $found = null;

    foreach ($arr as $index => $value) {
        $comparisons++;
        if ($value === $target) {
            $found = $index;
            break;
        }
    }

    return [
        'index' => $found,
        'comparisons' => $comparisons,
    ];
}

/**
 * Find all occurrences
 */
function linearSearchAll(array $arr, mixed $target): array
{
    $indices = [];

    foreach ($arr as $index => $value) {
        if ($value === $target) {
            $indices[] = $index;
        }
    }

    return $indices;
}

/**
 * Sentinel linear search (optimization)
 */
function sentinelLinearSearch(array $arr, mixed $target): ?int
{
    $n = count($arr);
    if ($n === 0) return null;

    $last = $arr[$n - 1];

    // Place sentinel at end
    $arr[$n - 1] = $target;

    $i = 0;
    while ($arr[$i] !== $target) {
        $i++;
    }

    // Restore last element
    $arr[$n - 1] = $last;

    // Check if we found target or hit sentinel
    if ($i < $n - 1 || $last === $target) {
        return $i;
    }

    return null;
}

// Test 1: Basic Linear Search
echo "1. Basic Linear Search\n";
echo "=======================\n";

$numbers = [64, 34, 25, 12, 22, 11, 90];
echo "Array: [" . implode(', ', $numbers) . "]\n\n";

$searches = [22, 90, 11, 99];

foreach ($searches as $target) {
    $result = linearSearchWithStats($numbers, $target);

    if ($result['index'] !== null) {
        echo "Search for $target: Found at index {$result['index']} ({$result['comparisons']} comparisons)\n";
    } else {
        echo "Search for $target: Not found ({$result['comparisons']} comparisons)\n";
    }
}
echo "\n";

// Test 2: Best, Average, Worst Cases
echo "2. Best, Average, Worst Cases\n";
echo "===============================\n";

$arr = range(1, 100);

// Best case: first element
$result = linearSearchWithStats($arr, 1);
echo "Best case (first element):  {$result['comparisons']} comparisons\n";

// Worst case: last element or not found
$result = linearSearchWithStats($arr, 100);
echo "Worst case (last element):  {$result['comparisons']} comparisons\n";

$result = linearSearchWithStats($arr, 999);
echo "Not found (full scan):      {$result['comparisons']} comparisons\n";

// Average case simulation
$totalComparisons = 0;
$trials = 100;

for ($i = 0; $i < $trials; $i++) {
    $target = random_int(1, 100);
    $result = linearSearchWithStats($arr, $target);
    $totalComparisons += $result['comparisons'];
}

echo "Average case:               " . round($totalComparisons / $trials) . " comparisons\n\n";

// Test 3: Find All Occurrences
echo "3. Find All Occurrences\n";
echo "========================\n";

$numbers = [3, 1, 4, 1, 5, 9, 2, 6, 5, 3, 5];
echo "Array: [" . implode(', ', $numbers) . "]\n\n";

$target = 5;
$indices = linearSearchAll($numbers, $target);

echo "All occurrences of $target: [" . implode(', ', $indices) . "]\n";
echo "Found " . count($indices) . " occurrences\n\n";

// Test 4: Sentinel Linear Search
echo "4. Sentinel Linear Search (Optimization)\n";
echo "==========================================\n";

echo "Sentinel search eliminates boundary checking in loop\n";
echo "Slightly faster in practice (fewer operations per iteration)\n\n";

$testSize = 10000;
$arr = range(1, $testSize);
$target = $testSize; // Worst case

// Regular linear search
$start = microtime(true);
$result = linearSearch($arr, $target);
$regularTime = (microtime(true) - $start) * 1000;

// Sentinel linear search
$start = microtime(true);
$result = sentinelLinearSearch($arr, $target);
$sentinelTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n";
echo "Regular linear search:  " . number_format($regularTime, 3) . " ms\n";
echo "Sentinel linear search: " . number_format($sentinelTime, 3) . " ms\n";
echo "Improvement: " . number_format(($regularTime - $sentinelTime) / $regularTime * 100, 1) . "%\n\n";

// Test 5: Performance on Different Data Sizes
echo "5. Performance Scaling\n";
echo "=======================\n";

$sizes = [100, 1000, 10000, 100000];

echo "Array Size | Target | Comparisons | Time (ms)\n";
echo "-----------|--------|-------------|----------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);

    // Worst case: not found
    $target = $size + 1;

    $start = microtime(true);
    $result = linearSearchWithStats($arr, $target);
    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%10d | %6d | %11d | %8.3f\n",
        $size,
        $target,
        $result['comparisons'],
        $time
    );
}

echo "\nObservation: Time scales linearly with n (O(n))\n\n";

// Test 6: Search Objects
echo "6. Search in Object Arrays\n";
echo "===========================\n";

class Person
{
    public function __construct(
        public string $name,
        public int $age
    ) {}

    public function __toString(): string
    {
        return "{$this->name} ({$this->age})";
    }
}

$people = [
    new Person('Alice', 30),
    new Person('Bob', 25),
    new Person('Charlie', 35),
    new Person('Diana', 28),
];

echo "People: ";
foreach ($people as $p) {
    echo "$p ";
}
echo "\n\n";

// Search by name
$targetName = 'Charlie';
$found = null;

foreach ($people as $index => $person) {
    if ($person->name === $targetName) {
        $found = $index;
        break;
    }
}

if ($found !== null) {
    echo "Found '$targetName' at index $found: {$people[$found]}\n\n";
}

// Test 7: Linear Search on Unsorted Data
echo "7. Linear Search: Only Option for Unsorted Data\n";
echo "=================================================\n";

$unsorted = [42, 17, 93, 8, 64, 23, 51];
echo "Unsorted array: [" . implode(', ', $unsorted) . "]\n";

$target = 23;
$result = linearSearchWithStats($unsorted, $target);

echo "Search for $target: Found at index {$result['index']}\n";
echo "Note: Binary search REQUIRES sorted data!\n\n";

// Test 8: Early Termination Benefit
echo "8. Early Termination Benefit\n";
echo "==============================\n";

$arr = range(1, 1000);

echo "Searching for elements at different positions:\n\n";

$positions = [0, 250, 500, 750, 999];

foreach ($positions as $pos) {
    $target = $arr[$pos];
    $result = linearSearchWithStats($arr, $target);

    echo sprintf(
        "Position %4d: %4d comparisons\n",
        $pos,
        $result['comparisons']
    );
}

echo "\nEarly termination allows O(1) best case!\n\n";

// Test 9: Comparison with Other Approaches
echo "9. When Linear Search is Best\n";
echo "===============================\n";

echo "Use Linear Search when:\n";
echo "  ✓ Data is unsorted (sorting cost > search benefit)\n";
echo "  ✓ Small dataset (< 100 elements)\n";
echo "  ✓ Single search operation\n";
echo "  ✓ Searching non-comparable data\n";
echo "  ✓ Finding all occurrences\n\n";

echo "Avoid Linear Search when:\n";
echo "  ✗ Data is sorted (use binary search O(log n))\n";
echo "  ✗ Large dataset with multiple searches\n";
echo "  ✗ Can use hash table for O(1) lookup\n\n";

// Test 10: Practical Example - Word Search
echo "10. Practical Example: Search in Text\n";
echo "=======================================\n";

$words = ['apple', 'banana', 'cherry', 'date', 'elderberry', 'fig', 'grape'];
echo "Dictionary: [" . implode(', ', $words) . "]\n\n";

$searchTerms = ['cherry', 'mango', 'fig'];

foreach ($searchTerms as $term) {
    $result = linearSearchWithStats($words, $term);

    if ($result['index'] !== null) {
        echo "'$term': Found (scanned {$result['comparisons']} words)\n";
    } else {
        echo "'$term': Not found (scanned {$result['comparisons']} words)\n";
    }
}

echo "\nKey Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(1)   - target at first position\n";
echo "  • Average case:  O(n/2) ≈ O(n)\n";
echo "  • Worst case:    O(n)   - target at end or not found\n\n";

echo "Space Complexity:\n";
echo "  • O(1) - only uses constant extra space\n\n";

echo "Properties:\n";
echo "  ✓ Simple to implement\n";
echo "  ✓ Works on unsorted data\n";
echo "  ✓ Works on any comparable data type\n";
echo "  ✓ Can find all occurrences efficiently\n";
echo "  ✓ Cache-friendly (sequential access)\n\n";

echo "Advantages:\n";
echo "  • Simple and intuitive\n";
echo "  • No preprocessing required (no sorting)\n";
echo "  • Works on unsorted data\n";
echo "  • Early termination possible\n";
echo "  • Good for small datasets\n\n";

echo "Disadvantages:\n";
echo "  • Slow for large datasets (O(n))\n";
echo "  • Not suitable for frequent searches\n";
echo "  • Outperformed by binary search on sorted data\n";
echo "  • No optimization possible without data structure change\n\n";

echo "Real-World Applications:\n";
echo "  • Finding item in small list\n";
echo "  • Searching unsorted data\n";
echo "  • One-time searches where sorting overhead isn't worth it\n";
echo "  • Finding all matching elements\n";
echo "  • Sequential file processing\n";

echo "\n✅ Complete! Linear search demonstrated.\n";
