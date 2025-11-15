<?php

declare(strict_types=1);

/**
 * Chapter 10: PHP's Built-in Sorting Functions
 * Part 3: Advanced Sorting Techniques
 * 
 * Demonstrates advanced sorting topics:
 * - PHP Intl extension (Collator) for proper internationalization
 * - SplFixedArray sorting performance
 * - shuffle() and array randomization (Fisher-Yates)
 * - Sorting arrays with mixed types
 * - Pre-computing sort keys for performance
 * - Advanced multi-key sorting patterns
 */

echo "=== Advanced Sorting Techniques ===\n\n";

// ============================================================================
// 1. PHP Intl Collator for Internationalization
// ============================================================================

echo "1. PHP Intl Collator (Proper Internationalization)\n";
echo str_repeat("-", 50) . "\n";

if (extension_loaded('intl')) {
    $names = ['Ömer', 'Alice', 'Åsa', 'Bob', 'Äpple'];
    echo "Original: " . implode(', ', $names) . "\n";
    
    // Method 1: Basic setlocale (less reliable)
    $test1 = $names;
    setlocale(LC_COLLATE, 'sv_SE.UTF-8');
    usort($test1, fn($a, $b) => strcoll($a, $b));
    echo "setlocale + strcoll: " . implode(', ', $test1) . "\n";
    
    // Method 2: Collator class (more robust)
    $test2 = $names;
    $collator = new Collator('sv_SE');
    $collator->sort($test2);
    echo "Collator::sort(): " . implode(', ', $test2) . "\n";
    
    // Method 3: Collator with usort (custom logic)
    $test3 = $names;
    usort($test3, fn($a, $b) => $collator->compare($a, $b));
    echo "Collator::compare(): " . implode(', ', $test3) . "\n";
    
    // Chinese/Japanese example
    $chinese = ['李', '王', '张', '刘'];
    $collatorCN = new Collator('zh_CN');
    $collatorCN->sort($chinese);
    echo "\nChinese names (sorted): " . implode(', ', $chinese) . "\n";
    
    echo "\nBenefit: Collator is platform-independent and handles complex Unicode rules\n";
} else {
    echo "Intl extension not loaded. Install with: pecl install intl\n";
}

echo "\n";

// ============================================================================
// 2. SplFixedArray Performance
// ============================================================================

echo "2. SplFixedArray Sorting Performance\n";
echo str_repeat("-", 50) . "\n";

$size = 10000;
$data = range(1, $size);
shuffle($data);

// Standard PHP array
$phpArray = $data;
$start = microtime(true);
sort($phpArray);
$time1 = (microtime(true) - $start) * 1000;

// SplFixedArray (faster for large arrays)
$fixedArray = SplFixedArray::fromArray($data);
$start = microtime(true);
// Convert to array, sort, convert back
$temp = $fixedArray->toArray();
sort($temp);
$fixedArray = SplFixedArray::fromArray($temp);
$time2 = (microtime(true) - $start) * 1000;

echo "Sorting {$size} elements:\n";
echo sprintf("  PHP array:      %.3f ms\n", $time1);
echo sprintf("  SplFixedArray:  %.3f ms", $time2);
if ($time1 > $time2) {
    $speedup = ($time1 - $time2) / $time1 * 100;
    echo sprintf(" (%.1f%% faster)\n", $speedup);
} else {
    echo "\n";
}

echo "\nBenefit: SplFixedArray uses less memory and can be 20-30% faster\n";
echo "Use when: Sorting large arrays (>5000 elements), memory is constrained\n\n";

// ============================================================================
// 3. shuffle() and Randomization
// ============================================================================

echo "3. shuffle() and Array Randomization\n";
echo str_repeat("-", 50) . "\n";

$deck = range(1, 10);
echo "Original: " . implode(', ', $deck) . "\n";

// Built-in shuffle
$test1 = $deck;
shuffle($test1);
echo "shuffle():  " . implode(', ', $test1) . "\n";

// Fisher-Yates shuffle (unbiased)
function fisherYatesShuffle(array &$arr): void
{
    $n = count($arr);
    for ($i = $n - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
    }
}

$test2 = $deck;
fisherYatesShuffle($test2);
echo "Fisher-Yates: " . implode(', ', $test2) . "\n";

echo "\nUse cases: Card games, random sampling, testing sorting algorithms\n\n";

// ============================================================================
// 4. Sorting Arrays with Mixed Types
// ============================================================================

echo "4. Sorting Mixed-Type Arrays\n";
echo str_repeat("-", 50) . "\n";

$mixed = [1, '10', 2.5, '2', null, true, false, '1.5'];
echo "Mixed types: " . json_encode($mixed) . "\n";

// Default sort (type juggling)
$test1 = $mixed;
sort($test1);
echo "Default sort: " . json_encode($test1) . "\n";

// Numeric sort
$test2 = $mixed;
sort($test2, SORT_NUMERIC);
echo "SORT_NUMERIC: " . json_encode($test2) . "\n";

// Custom handling with explicit type conversion
$test3 = $mixed;
usort($test3, function($a, $b) {
    // Convert everything to comparable numeric format
    $a = match(true) {
        $a === null => PHP_INT_MIN,
        is_bool($a) => (int)$a,
        is_numeric($a) => +$a,
        default => 0
    };
    $b = match(true) {
        $b === null => PHP_INT_MIN,
        is_bool($b) => (int)$b,
        is_numeric($b) => +$b,
        default => 0
    };
    return $a <=> $b;
});
echo "Custom convert: " . json_encode($test3) . "\n";

echo "\nBest practice: Validate and sanitize data before sorting\n\n";

// ============================================================================
// 5. Pre-computing Sort Keys (Performance Optimization)
// ============================================================================

echo "5. Pre-computing Sort Keys for Performance\n";
echo str_repeat("-", 50) . "\n";

class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $sales
    ) {}
    
    public function calculateComplexScore(): float
    {
        // Simulate expensive calculation
        usleep(100); // 0.1ms delay
        return $this->price * 0.3 + $this->sales * 0.7;
    }
}

$products = [
    new Product('Laptop', 1200, 50),
    new Product('Mouse', 25, 200),
    new Product('Keyboard', 80, 150),
    new Product('Monitor', 400, 100),
];

echo "Sorting 4 products by complex score...\n\n";

// Method 1: Calculate in comparison (SLOW)
$test1 = $products;
$start = microtime(true);
usort($test1, fn($a, $b) => 
    $b->calculateComplexScore() <=> $a->calculateComplexScore()
);
$time1 = (microtime(true) - $start) * 1000;

// Method 2: Pre-compute scores (FAST)
$test2 = $products;
$start = microtime(true);
$scores = array_map(fn($p) => $p->calculateComplexScore(), $test2);
array_multisort($scores, SORT_DESC, $test2);
$time2 = (microtime(true) - $start) * 1000;

echo sprintf("Method 1 (in comparison): %.1f ms\n", $time1);
echo sprintf("Method 2 (pre-compute):   %.1f ms", $time2);
if ($time1 > $time2) {
    $speedup = $time1 / $time2;
    echo sprintf(" (%.1fx faster!)\n", $speedup);
} else {
    echo "\n";
}

echo "\nRule: Pre-calculate if the comparison function is called O(n log n) times\n\n";

// ============================================================================
// 6. Advanced Multi-Key Sorting with Elvis Operator
// ============================================================================

echo "6. Advanced Multi-Key Sorting Patterns\n";
echo str_repeat("-", 50) . "\n";

$employees = [
    ['name' => 'Alice', 'dept' => 'IT', 'salary' => 50000, 'years' => 5],
    ['name' => 'Bob', 'dept' => 'HR', 'salary' => 60000, 'years' => 3],
    ['name' => 'Charlie', 'dept' => 'IT', 'salary' => 55000, 'years' => 5],
    ['name' => 'Diana', 'dept' => 'IT', 'salary' => 50000, 'years' => 7],
];

echo "Original employees:\n";
foreach ($employees as $e) {
    echo "  {$e['name']}: {$e['dept']}, \${$e['salary']}, {$e['years']} years\n";
}

// Sort by: dept (asc), salary (desc), years (desc), name (asc)
usort($employees, function($a, $b) {
    return $a['dept'] <=> $b['dept']
        ?: $b['salary'] <=> $a['salary']
        ?: $b['years'] <=> $a['years']
        ?: $a['name'] <=> $b['name'];
});

echo "\nSorted (dept asc, salary desc, years desc, name asc):\n";
foreach ($employees as $e) {
    echo "  {$e['name']}: {$e['dept']}, \${$e['salary']}, {$e['years']} years\n";
}

echo "\nTip: Use ?: (Elvis operator) for clean multi-level comparisons\n\n";

// ============================================================================
// 7. Stable Sorting with Tie-Breaking
// ============================================================================

echo "7. Achieving Stable Sort with Tie-Breaking\n";
echo str_repeat("-", 50) . "\n";

$items = [
    ['value' => 5, 'id' => 'A', 'index' => 0],
    ['value' => 3, 'id' => 'B', 'index' => 1],
    ['value' => 5, 'id' => 'C', 'index' => 2],
    ['value' => 3, 'id' => 'D', 'index' => 3],
];

echo "Original order:\n";
foreach ($items as $item) {
    echo "  {$item['id']}: value={$item['value']}, index={$item['index']}\n";
}

// Unstable sort (order of equal values unpredictable)
$test1 = $items;
usort($test1, fn($a, $b) => $a['value'] <=> $b['value']);

echo "\nUnstable sort (by value only):\n";
foreach ($test1 as $item) {
    echo "  {$item['id']}: value={$item['value']}, index={$item['index']}\n";
}

// Stable sort (preserve original order for equal values)
$test2 = $items;
usort($test2, function($a, $b) {
    $cmp = $a['value'] <=> $b['value'];
    return $cmp !== 0 ? $cmp : $a['index'] <=> $b['index'];
});

echo "\nStable sort (with index tie-breaker):\n";
foreach ($test2 as $item) {
    echo "  {$item['id']}: value={$item['value']}, index={$item['index']}\n";
}

echo "\nUse case: Pagination, maintaining relative order\n\n";

// ============================================================================
// 8. Sorting with Generators (What NOT to Do)
// ============================================================================

echo "8. Sorting with Generators (Important Gotcha)\n";
echo str_repeat("-", 50) . "\n";

function numbersGenerator(): Generator
{
    yield 3;
    yield 1;
    yield 4;
    yield 2;
}

echo "Generator cannot be sorted directly!\n\n";

// ❌ Won't work:
// sort(numbersGenerator()); // TypeError!

// ✅ Must convert to array first:
$numbers = iterator_to_array(numbersGenerator());
sort($numbers);
echo "Converted to array, then sorted: " . implode(', ', $numbers) . "\n";

echo "\nWhy: Sorting requires random access; generators are forward-only\n";
echo "Memory trade-off: Must load entire generator into memory to sort\n\n";

// ============================================================================
// 9. Version Number Sorting
// ============================================================================

echo "9. Sorting Version Numbers\n";
echo str_repeat("-", 50) . "\n";

$versions = ['1.10.0', '1.2.0', '1.2.1', '2.0.0', '1.1.5'];
echo "Original: " . implode(', ', $versions) . "\n";

// ❌ Wrong: String sort
$test1 = $versions;
sort($test1);
echo "String sort (wrong): " . implode(', ', $test1) . "\n";

// ✅ Correct: version_compare
$test2 = $versions;
usort($test2, fn($a, $b) => version_compare($a, $b));
echo "version_compare(): " . implode(', ', $test2) . "\n";

echo "\nAlways use version_compare() for semantic versioning\n\n";

// ============================================================================
// 10. Bucket Sort Pattern with array_reduce
// ============================================================================

echo "10. Bucket Sort Pattern (Advanced)\n";
echo str_repeat("-", 50) . "\n";

$numbers = [23, 45, 12, 78, 34, 56, 90, 11, 67, 89];
echo "Numbers: " . implode(', ', $numbers) . "\n";

// Bucket sort using array_reduce
$buckets = array_reduce($numbers, function($carry, $num) {
    $bucket = (int)($num / 10); // Bucket 0-9
    $carry[$bucket][] = $num;
    return $carry;
}, []);

// Sort buckets and items within buckets
ksort($buckets);
array_walk($buckets, fn(&$b) => sort($b));

// Flatten
$sorted = array_merge(...$buckets);
echo "Bucket sorted: " . implode(', ', $sorted) . "\n";

echo "\nUse case: Efficient for uniformly distributed data\n";
echo "Complexity: O(n + k) where k is number of buckets\n";

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary - Advanced Techniques:\n";
echo "- Collator (intl): Proper internationalization\n";
echo "- SplFixedArray: 20-30% faster for large arrays\n";
echo "- shuffle(): Fisher-Yates for unbiased randomization\n";
echo "- Pre-compute: Avoid expensive calculations in comparisons\n";
echo "- Elvis operator (?):: Clean multi-level sorting\n";
echo "- Stable sort: Use index as tie-breaker\n";
echo "- Generators: Convert to array before sorting\n";
echo "- version_compare(): For semantic versioning\n";
echo "- Bucket sort: O(n + k) for uniform distributions\n";
echo str_repeat("=", 50) . "\n";

