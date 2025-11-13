<?php
/**
 * Algorithm Complexity Examples
 *
 * Demonstrates each major time complexity class with practical examples
 * and visualizations of how they scale.
 *
 * @package PHPAlgorithms
 * @chapter 01
 */

declare(strict_types=1);

// ============================================================================
// O(1) - CONSTANT TIME
// ============================================================================

/**
 * O(1) - Array access by index
 * Time stays constant regardless of array size
 */
function getElement(array $arr, int $index): mixed
{
    return $arr[$index] ?? null;
}

/**
 * O(1) - Hash table lookup
 * Constant time regardless of number of elements
 */
function hashLookup(array $hashTable, string $key): mixed
{
    return $hashTable[$key] ?? null;
}

/**
 * O(1) - Simple arithmetic
 * Fixed number of operations
 */
function calculateDiscount(float $price, float $rate): float
{
    return $price * (1 - $rate);
}

// ============================================================================
// O(log n) - LOGARITHMIC TIME
// ============================================================================

/**
 * O(log n) - Binary search
 * Halves search space each iteration
 *
 * @param array<int> $arr Sorted array
 * @param int $target Value to find
 * @return int Index of target, or -1 if not found
 */
function binarySearch(array $arr, int $target): int
{
    $left = 0;
    $right = count($arr) - 1;
    $iterations = 0;

    while ($left <= $right) {
        $iterations++;
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            echo "   Found in {$iterations} iterations (log₂ " . count($arr) . " ≈ " . log(count($arr), 2) . ")\n";
            return $mid;
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    echo "   Not found after {$iterations} iterations\n";
    return -1;
}

/**
 * O(log n) - Find power using exponentiation by squaring
 */
function power(int $base, int $exp): int
{
    if ($exp === 0) {
        return 1;
    }

    if ($exp % 2 === 0) {
        $half = power($base, $exp / 2);
        return $half * $half;
    }

    return $base * power($base, $exp - 1);
}

// ============================================================================
// O(n) - LINEAR TIME
// ============================================================================

/**
 * O(n) - Sum all elements
 * Must visit each element once
 */
function sumArray(array $nums): int
{
    $sum = 0;
    foreach ($nums as $num) {
        $sum += $num;
    }
    return $sum;
}

/**
 * O(n) - Find maximum
 * Single pass through array
 */
function findMax(array $nums): int
{
    if (empty($nums)) {
        throw new \InvalidArgumentException('Array cannot be empty');
    }

    $max = $nums[0];
    foreach ($nums as $num) {
        if ($num > $max) {
            $max = $num;
        }
    }
    return $max;
}

/**
 * O(n) - Linear search
 */
function linearSearch(array $arr, $target): int
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return -1;
}

// ============================================================================
// O(n log n) - LINEARITHMIC TIME
// ============================================================================

/**
 * O(n log n) - Merge sort
 * Divides array (log n) and merges (n) at each level
 */
function mergeSort(array $arr): array
{
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

function merge(array $left, array $right): array
{
    $result = [];
    $i = $j = 0;

    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }

    return array_merge($result, array_slice($left, $i), array_slice($right, $j));
}

/**
 * O(n log n) - Heap sort demonstration
 */
function heapSort(array $arr): array
{
    // For demonstration, use PHP's built-in heap
    $heap = new SplMinHeap();

    foreach ($arr as $value) {
        $heap->insert($value);
    }

    $sorted = [];
    while (!$heap->isEmpty()) {
        $sorted[] = $heap->extract();
    }

    return $sorted;
}

// ============================================================================
// O(n²) - QUADRATIC TIME
// ============================================================================

/**
 * O(n²) - Bubble sort
 * Nested loops over same data
 */
function bubbleSort(array $arr): array
{
    $n = count($arr);
    $comparisons = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            $comparisons++;
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }

    echo "   Made {$comparisons} comparisons (n² = " . ($n * $n) . ", actual ≈ n²/2)\n";
    return $arr;
}

/**
 * O(n²) - Find all pairs
 */
function findAllPairs(array $items): array
{
    $pairs = [];
    $n = count($items);

    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $pairs[] = [$items[$i], $items[$j]];
        }
    }

    return $pairs;
}

/**
 * O(n²) - Check for duplicates (naive approach)
 */
function hasDuplicates_Slow(array $arr): bool
{
    $n = count($arr);

    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$i] === $arr[$j]) {
                return true;
            }
        }
    }

    return false;
}

/**
 * O(n) - Check for duplicates (optimized with hash set)
 */
function hasDuplicates_Fast(array $arr): bool
{
    $seen = [];

    foreach ($arr as $value) {
        if (isset($seen[$value])) {
            return true;
        }
        $seen[$value] = true;
    }

    return false;
}

// ============================================================================
// O(2^n) - EXPONENTIAL TIME
// ============================================================================

/**
 * O(2^n) - Fibonacci (naive recursive)
 * Each call branches into two more calls
 */
function fibonacci_Slow(int $n): int
{
    if ($n <= 1) {
        return $n;
    }
    return fibonacci_Slow($n - 1) + fibonacci_Slow($n - 2);
}

/**
 * O(n) - Fibonacci (with memoization)
 */
function fibonacci_Fast(int $n, array &$memo = []): int
{
    if ($n <= 1) {
        return $n;
    }

    if (isset($memo[$n])) {
        return $memo[$n];
    }

    $memo[$n] = fibonacci_Fast($n - 1, $memo) + fibonacci_Fast($n - 2, $memo);
    return $memo[$n];
}

/**
 * O(2^n) - Generate all subsets
 */
function generateSubsets(array $set): array
{
    if (empty($set)) {
        return [[]];
    }

    $first = array_shift($set);
    $subsetsWithoutFirst = generateSubsets($set);
    $subsetsWithFirst = array_map(fn($subset) => array_merge([$first], $subset), $subsetsWithoutFirst);

    return array_merge($subsetsWithoutFirst, $subsetsWithFirst);
}

// ============================================================================
// O(n!) - FACTORIAL TIME
// ============================================================================

/**
 * O(n!) - Generate all permutations
 */
function generatePermutations(array $items): array
{
    if (count($items) <= 1) {
        return [$items];
    }

    $permutations = [];

    foreach ($items as $i => $item) {
        $remaining = array_merge(
            array_slice($items, 0, $i),
            array_slice($items, $i + 1)
        );

        foreach (generatePermutations($remaining) as $perm) {
            $permutations[] = array_merge([$item], $perm);
        }
    }

    return $permutations;
}

// ============================================================================
// COMPLEXITY GROWTH DEMONSTRATION
// ============================================================================

function demonstrateGrowth(): void
{
    echo "=== Complexity Growth Comparison ===\n\n";
    echo "n      | O(1) | O(log n) | O(n) | O(n log n) | O(n²)   | O(2^n)\n";
    echo str_repeat('-', 70) . "\n";

    $sizes = [1, 10, 100, 1000, 10000];

    foreach ($sizes as $n) {
        $o1 = 1;
        $olog = (int)log($n, 2);
        $on = $n;
        $onlogn = (int)($n * log($n, 2));
        $on2 = $n * $n;
        $o2n = $n <= 20 ? pow(2, $n) : '∞';

        printf(
            "%-6d | %-4d | %-8d | %-4d | %-10d | %-7s | %s\n",
            $n,
            $o1,
            $olog,
            $on,
            $onlogn,
            number_format($on2),
            is_numeric($o2n) ? number_format($o2n) : $o2n
        );
    }
    echo "\n";
}

// ============================================================================
// BENCHMARKING HELPER
// ============================================================================

function benchmarkComplexity(string $name, callable $fn, array $sizes): void
{
    echo "{$name}:\n";
    echo "Size   | Time (ms) | Growth\n";
    echo str_repeat('-', 35) . "\n";

    $prevTime = null;

    foreach ($sizes as $size) {
        $start = hrtime(true);
        $fn($size);
        $end = hrtime(true);

        $time = ($end - $start) / 1_000_000;
        $growth = $prevTime ? $time / $prevTime : 1.0;

        printf("%-6d | %9.3f | %.2fx\n", $size, $time, $growth);
        $prevTime = $time;
    }

    echo "\n";
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Algorithm Complexity Examples ===\n\n";

    // O(1) Demo
    echo "1. O(1) - Constant Time:\n";
    $arr = range(1, 1000000);
    $start = hrtime(true);
    getElement($arr, 50000);
    $time = (hrtime(true) - $start) / 1_000_000;
    echo "   Access element in 1M array: {$time} ms (always constant)\n\n";

    // O(log n) Demo
    echo "2. O(log n) - Logarithmic Time:\n";
    $sorted = range(1, 1000000);
    echo "   Searching in 1,000,000 elements:\n";
    binarySearch($sorted, 742518);
    echo "\n";

    // O(n) Demo
    echo "3. O(n) - Linear Time:\n";
    $nums = range(1, 1000);
    $start = hrtime(true);
    $sum = sumArray($nums);
    $time = (hrtime(true) - $start) / 1_000_000;
    echo "   Sum 1000 numbers: {$time} ms\n";
    echo "   Result: {$sum}\n\n";

    // O(n log n) Demo
    echo "4. O(n log n) - Linearithmic Time:\n";
    $unsorted = range(1, 100);
    shuffle($unsorted);
    $start = hrtime(true);
    $sorted = mergeSort($unsorted);
    $time = (hrtime(true) - $start) / 1_000_000;
    echo "   Merge sort 100 elements: {$time} ms\n\n";

    // O(n²) Demo
    echo "5. O(n²) - Quadratic Time:\n";
    $small = [64, 34, 25, 12, 22, 11, 90, 88, 45, 50];
    bubbleSort($small);
    echo "\n";

    // Duplicate detection comparison
    echo "6. O(n²) vs O(n) Comparison:\n";
    $testArray = array_merge(range(1, 100), [50]); // Has duplicate
    shuffle($testArray);

    $start = hrtime(true);
    $result1 = hasDuplicates_Slow($testArray);
    $time1 = (hrtime(true) - $start) / 1_000_000;

    $start = hrtime(true);
    $result2 = hasDuplicates_Fast($testArray);
    $time2 = (hrtime(true) - $start) / 1_000_000;

    echo "   O(n²) approach: {$time1} ms\n";
    echo "   O(n) approach:  {$time2} ms\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // O(2^n) Demo
    echo "7. O(2^n) - Exponential Time:\n";
    echo "   Fibonacci comparison:\n";

    for ($n = 5; $n <= 35; $n += 10) {
        $start = hrtime(true);
        $result1 = fibonacci_Slow($n);
        $time1 = (hrtime(true) - $start) / 1_000_000;

        $start = hrtime(true);
        $result2 = fibonacci_Fast($n);
        $time2 = (hrtime(true) - $start) / 1_000_000;

        echo "   fib({$n}) - Slow: " . number_format($time1, 3) . " ms, ";
        echo "Fast: " . number_format($time2, 3) . " ms ";
        echo "(Speedup: " . round($time1 / max($time2, 0.001), 0) . "x)\n";
    }
    echo "\n";

    // Growth comparison
    demonstrateGrowth();

    // Key takeaways
    echo "=== Key Takeaways ===\n";
    echo "• O(1): Best - always fast regardless of input size\n";
    echo "• O(log n): Excellent - scales well even for huge inputs\n";
    echo "• O(n): Good - acceptable for most applications\n";
    echo "• O(n log n): Acceptable - best we can do for comparison sorting\n";
    echo "• O(n²): Poor - avoid for large inputs (n > 10,000)\n";
    echo "• O(2^n): Very Poor - only works for tiny inputs (n < 25)\n";
    echo "• O(n!): Terrible - only works for very small inputs (n < 12)\n";
}
