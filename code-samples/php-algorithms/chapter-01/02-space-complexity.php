<?php
/**
 * Space Complexity Examples
 *
 * Demonstrates different space complexity classes and their memory usage.
 *
 * @package PHPAlgorithms
 * @chapter 01
 */

declare(strict_types=1);

// ============================================================================
// O(1) SPACE - CONSTANT SPACE
// ============================================================================

/**
 * O(1) space - Only uses fixed variables
 */
function sumArray_O1_Space(array $nums): int
{
    $sum = 0; // Single variable
    foreach ($nums as $num) {
        $sum += $num;
    }
    return $sum;
}

/**
 * O(1) space - In-place array reversal
 */
function reverseInPlace(array &$arr): void
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        [$arr[$left], $arr[$right]] = [$arr[$right], $arr[$left]];
        $left++;
        $right--;
    }
}

// ============================================================================
// O(n) SPACE - LINEAR SPACE
// ============================================================================

/**
 * O(n) space - Creates new array
 */
function doubleValues(array $nums): array
{
    $doubled = []; // New array grows with input
    foreach ($nums as $num) {
        $doubled[] = $num * 2;
    }
    return $doubled;
}

/**
 * O(n) space - Recursive call stack
 */
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }
    return $n * factorial($n - 1); // Stack depth = n
}

/**
 * O(n) space - Hash table for frequency counting
 */
function countFrequencies(array $items): array
{
    $freq = []; // Size depends on unique items
    foreach ($items as $item) {
        $freq[$item] = ($freq[$item] ?? 0) + 1;
    }
    return $freq;
}

// ============================================================================
// O(log n) SPACE - LOGARITHMIC SPACE
// ============================================================================

/**
 * O(log n) space - Binary search (iterative, minimal space)
 */
function binarySearch_Iterative(array $arr, int $target): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);
        if ($arr[$mid] === $target) return $mid;
        if ($arr[$mid] < $target) $left = $mid + 1;
        else $right = $mid - 1;
    }

    return -1;
}

/**
 * O(log n) space - Recursive binary search (call stack)
 */
function binarySearch_Recursive(array $arr, int $target, int $left = 0, int $right = null): int
{
    if ($right === null) $right = count($arr) - 1;

    if ($left > $right) return -1;

    $mid = (int)(($left + $right) / 2);

    if ($arr[$mid] === $target) return $mid;

    if ($arr[$mid] < $target) {
        return binarySearch_Recursive($arr, $target, $mid + 1, $right);
    }

    return binarySearch_Recursive($arr, $target, $left, $mid - 1);
}

// ============================================================================
// COMPARING SPACE USAGE
// ============================================================================

/**
 * Memory profiler helper
 */
class MemoryProfiler
{
    private int $startMemory;

    public function start(): void
    {
        gc_collect_cycles();
        $this->startMemory = memory_get_usage(true);
    }

    public function end(): int
    {
        return memory_get_usage(true) - $this->startMemory;
    }

    public function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Space Complexity Examples ===\n\n";

    $profiler = new MemoryProfiler();

    // O(1) Space
    echo "1. O(1) Space - Constant:\n";
    $nums = range(1, 100000);
    $profiler->start();
    $sum = sumArray_O1_Space($nums);
    $memory = $profiler->end();
    echo "   Sum 100K numbers: " . $profiler->formatBytes($memory) . " additional memory\n\n";

    // O(n) Space
    echo "2. O(n) Space - Linear:\n";
    $nums = range(1, 10000);
    $profiler->start();
    $doubled = doubleValues($nums);
    $memory = $profiler->end();
    echo "   Double 10K numbers: " . $profiler->formatBytes($memory) . " additional memory\n\n";

    // Recursive vs Iterative
    echo "3. Recursive vs Iterative Space:\n";
    echo "   Factorial(1000) - Recursive stack depth: 1000\n";
    echo "   Factorial(1000) - Iterative uses: O(1) space\n\n";

    echo "=== Key Takeaways ===\n";
    echo "• In-place algorithms use O(1) space\n";
    echo "• Creating new data structures usually requires O(n) space\n";
    echo "• Recursive solutions use O(depth) space for call stack\n";
    echo "• Trade-offs: Time vs Space complexity\n";
}
