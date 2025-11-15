<?php
/**
 * Performance Optimization Tips
 *
 * Practical examples of common performance optimizations in PHP algorithms.
 * Shows good vs bad practices with benchmarks.
 *
 * @package PHPAlgorithms
 * @chapter 00
 */

declare(strict_types=1);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function benchmark(callable $fn, int $iterations = 1000): float
{
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }
    $end = hrtime(true);
    return ($end - $start) / 1_000_000 / $iterations; // avg ms
}

function formatTime(float $ms): string
{
    if ($ms < 0.001) {
        return number_format($ms * 1000, 3) . ' μs';
    }
    return number_format($ms, 3) . ' ms';
}

// ============================================================================
// TIP 1: PRE-CALCULATE count() IN LOOPS
// ============================================================================

/**
 * Bad: count() called every iteration - O(n²) for some implementations
 */
function sumArray_BAD(array $arr): int
{
    $sum = 0;
    for ($i = 0; $i < count($arr); $i++) { // count() called n times!
        $sum += $arr[$i];
    }
    return $sum;
}

/**
 * Good: count() called once - O(n)
 */
function sumArray_GOOD(array $arr): int
{
    $sum = 0;
    $n = count($arr); // Calculate once
    for ($i = 0; $i < $n; $i++) {
        $sum += $arr[$i];
    }
    return $sum;
}

// ============================================================================
// TIP 2: USE isset() INSTEAD OF in_array()
// ============================================================================

/**
 * Bad: in_array() is O(n) per lookup
 */
function checkMembership_BAD(array $validIds, int $id): bool
{
    return in_array($id, $validIds, true); // O(n)
}

/**
 * Good: isset() with array keys is O(1)
 */
function checkMembership_GOOD(array $validIds, int $id): bool
{
    return isset($validIds[$id]); // O(1)
}

// ============================================================================
// TIP 3: STRING CONCATENATION
// ============================================================================

/**
 * Bad: String concatenation in loop - O(n²)
 * Each concatenation creates a new string and copies everything
 */
function buildString_BAD(array $items): string
{
    $result = '';
    foreach ($items as $item) {
        $result .= $item . ','; // Creates new string each time!
    }
    return rtrim($result, ',');
}

/**
 * Good: Build array then implode - O(n)
 */
function buildString_GOOD(array $items): string
{
    return implode(',', $items);
}

// ============================================================================
// TIP 4: EARLY RETURN
// ============================================================================

/**
 * Bad: Checks all elements even after finding match
 */
function hasValue_BAD(array $arr, $target): bool
{
    $found = false;
    foreach ($arr as $value) {
        if ($value === $target) {
            $found = true;
        }
    }
    return $found;
}

/**
 * Good: Returns immediately when found
 */
function hasValue_GOOD(array $arr, $target): bool
{
    foreach ($arr as $value) {
        if ($value === $target) {
            return true; // Early return!
        }
    }
    return false;
}

// ============================================================================
// TIP 5: AVOID NESTED LOOPS WHEN POSSIBLE
// ============================================================================

/**
 * Bad: Nested loops - O(n²)
 */
function findCommon_BAD(array $arr1, array $arr2): array
{
    $common = [];
    foreach ($arr1 as $val1) {
        foreach ($arr2 as $val2) { // Nested!
            if ($val1 === $val2) {
                $common[] = $val1;
            }
        }
    }
    return $common;
}

/**
 * Good: Use hash set - O(n + m)
 */
function findCommon_GOOD(array $arr1, array $arr2): array
{
    $set = array_flip($arr1); // O(n)
    $common = [];

    foreach ($arr2 as $val) { // O(m)
        if (isset($set[$val])) {
            $common[] = $val;
        }
    }

    return $common;
}

// ============================================================================
// TIP 6: USE BUILT-IN FUNCTIONS
// ============================================================================

/**
 * Bad: Manual array filtering
 */
function filterEven_BAD(array $nums): array
{
    $result = [];
    foreach ($nums as $num) {
        if ($num % 2 === 0) {
            $result[] = $num;
        }
    }
    return $result;
}

/**
 * Good: Use array_filter (implemented in C, faster)
 */
function filterEven_GOOD(array $nums): array
{
    return array_filter($nums, fn($n) => $n % 2 === 0);
}

// ============================================================================
// TIP 7: GENERATORS FOR LARGE DATASETS
// ============================================================================

/**
 * Bad: Loads entire dataset into memory - O(n) space
 */
function processLargeFile_BAD(string $filename): int
{
    $lines = file($filename); // Loads entire file!
    $count = 0;

    foreach ($lines as $line) {
        if (str_contains($line, 'ERROR')) {
            $count++;
        }
    }

    return $count;
}

/**
 * Good: Streams file line by line - O(1) space
 */
function processLargeFile_GOOD(string $filename): int
{
    $count = 0;
    $handle = fopen($filename, 'r');

    while (($line = fgets($handle)) !== false) {
        if (str_contains($line, 'ERROR')) {
            $count++;
        }
    }

    fclose($handle);
    return $count;
}

// ============================================================================
// TIP 8: CACHING EXPENSIVE OPERATIONS
// ============================================================================

/**
 * Bad: Recalculates expensive operation every time
 */
class DataProcessor_BAD
{
    public function getStats(int $userId): array
    {
        // Simulate expensive database query
        usleep(10000); // 10ms
        return [
            'userId' => $userId,
            'posts' => random_int(0, 100),
            'followers' => random_int(0, 1000)
        ];
    }
}

/**
 * Good: Caches results
 */
class DataProcessor_GOOD
{
    private array $cache = [];

    public function getStats(int $userId): array
    {
        if (isset($this->cache[$userId])) {
            return $this->cache[$userId];
        }

        // Simulate expensive database query
        usleep(10000); // 10ms
        $stats = [
            'userId' => $userId,
            'posts' => random_int(0, 100),
            'followers' => random_int(0, 1000)
        ];

        $this->cache[$userId] = $stats;
        return $stats;
    }
}

// ============================================================================
// TIP 9: BATCH OPERATIONS
// ============================================================================

/**
 * Bad: N database queries
 */
function getUserNames_BAD(array $userIds, $db): array
{
    $names = [];
    foreach ($userIds as $id) {
        // Simulate 1 query per user
        usleep(100); // 0.1ms per query
        $names[] = "User{$id}";
    }
    return $names;
}

/**
 * Good: Single batch query
 */
function getUserNames_GOOD(array $userIds, $db): array
{
    // Simulate single query for all users
    usleep(500); // 0.5ms total (faster than N × 0.1ms)
    return array_map(fn($id) => "User{$id}", $userIds);
}

// ============================================================================
// TIP 10: AVOID UNNECESSARY ARRAY COPIES
// ============================================================================

/**
 * Bad: array_slice creates copy
 */
function processFirst_BAD(array $items): void
{
    $first = array_slice($items, 0, 1)[0] ?? null; // Creates copy!
    if ($first !== null) {
        // process $first
    }
}

/**
 * Good: Direct access
 */
function processFirst_GOOD(array $items): void
{
    $first = $items[0] ?? null; // No copy
    if ($first !== null) {
        // process $first
    }
}

// ============================================================================
// DEMONSTRATIONS WITH BENCHMARKS
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Performance Optimization Tips ===\n\n";

    $testData = range(1, 1000);
    $largeTestData = range(1, 10000);

    // Tip 1: Pre-calculate count()
    echo "1. Pre-calculate count() in loops:\n";
    $time1 = benchmark(fn() => sumArray_BAD($testData), 100);
    $time2 = benchmark(fn() => sumArray_GOOD($testData), 100);
    echo "   Bad:  " . formatTime($time1) . "\n";
    echo "   Good: " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Tip 2: isset() vs in_array()
    echo "2. isset() vs in_array():\n";
    $validArray = $testData;
    $validHash = array_flip($validArray);
    $time1 = benchmark(fn() => checkMembership_BAD($validArray, 500), 1000);
    $time2 = benchmark(fn() => checkMembership_GOOD($validHash, 500), 1000);
    echo "   in_array(): " . formatTime($time1) . "\n";
    echo "   isset():    " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Tip 3: String concatenation
    echo "3. String concatenation:\n";
    $items = array_map(fn($i) => "item{$i}", range(1, 100));
    $time1 = benchmark(fn() => buildString_BAD($items), 100);
    $time2 = benchmark(fn() => buildString_GOOD($items), 100);
    echo "   Concatenation: " . formatTime($time1) . "\n";
    echo "   implode():     " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Tip 4: Early return
    echo "4. Early return:\n";
    $haystack = array_merge([1, 2, 3], range(4, 1000));
    $time1 = benchmark(fn() => hasValue_BAD($haystack, 3), 1000);
    $time2 = benchmark(fn() => hasValue_GOOD($haystack, 3), 1000);
    echo "   No early return: " . formatTime($time1) . "\n";
    echo "   Early return:    " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Tip 5: Avoid nested loops
    echo "5. Avoid nested loops:\n";
    $arr1 = range(1, 100);
    $arr2 = range(50, 150);
    $time1 = benchmark(fn() => findCommon_BAD($arr1, $arr2), 100);
    $time2 = benchmark(fn() => findCommon_GOOD($arr1, $arr2), 100);
    echo "   Nested loops: " . formatTime($time1) . "\n";
    echo "   Hash set:     " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Tip 6: Built-in functions
    echo "6. Use built-in functions:\n";
    $nums = range(1, 1000);
    $time1 = benchmark(fn() => filterEven_BAD($nums), 1000);
    $time2 = benchmark(fn() => filterEven_GOOD($nums), 1000);
    echo "   Manual loop:    " . formatTime($time1) . "\n";
    echo "   array_filter(): " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Tip 8: Caching
    echo "8. Caching expensive operations:\n";
    $processorBad = new DataProcessor_BAD();
    $processorGood = new DataProcessor_GOOD();

    $start = hrtime(true);
    for ($i = 0; $i < 10; $i++) {
        $processorBad->getStats(1); // Same user, no cache
    }
    $timeBad = (hrtime(true) - $start) / 1_000_000;

    $start = hrtime(true);
    for ($i = 0; $i < 10; $i++) {
        $processorGood->getStats(1); // Same user, cached
    }
    $timeGood = (hrtime(true) - $start) / 1_000_000;

    echo "   No cache: " . formatTime($timeBad) . "\n";
    echo "   Cached:   " . formatTime($timeGood) . "\n";
    echo "   Speedup: " . round($timeBad / max($timeGood, 0.001), 2) . "x\n\n";

    // Tip 9: Batch operations
    echo "9. Batch operations:\n";
    $userIds = range(1, 10);
    $db = null; // Mock
    $time1 = benchmark(fn() => getUserNames_BAD($userIds, $db), 100);
    $time2 = benchmark(fn() => getUserNames_GOOD($userIds, $db), 100);
    echo "   N queries:    " . formatTime($time1) . "\n";
    echo "   Batch query:  " . formatTime($time2) . "\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Summary
    echo "=== Key Takeaways ===\n";
    echo "1. Pre-calculate expensive operations outside loops\n";
    echo "2. Use hash lookups (isset) instead of linear search (in_array)\n";
    echo "3. Use implode() instead of string concatenation in loops\n";
    echo "4. Return early when possible\n";
    echo "5. Avoid nested loops - use hash sets\n";
    echo "6. Prefer built-in functions (they're in C)\n";
    echo "7. Use generators for large datasets\n";
    echo "8. Cache expensive operations\n";
    echo "9. Batch database operations\n";
    echo "10. Avoid unnecessary array copies\n";
}
