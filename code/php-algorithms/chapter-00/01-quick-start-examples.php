<?php
/**
 * Quick Start Examples - Common Algorithm Patterns
 *
 * This file contains practical, copy-paste ready solutions for common programming tasks.
 * Based on Chapter 00: Quick Start Guide
 *
 * @package PHPAlgorithms
 * @chapter 00
 */

declare(strict_types=1);

// ============================================================================
// SEARCHING EXAMPLES
// ============================================================================

/**
 * Binary Search - O(log n)
 * Find element in sorted array
 *
 * @param array<int> $arr Sorted array
 * @param int $target Value to find
 * @return int Index of target, or -1 if not found
 */
function binarySearch(array $arr, int $target): int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return -1; // Not found
}

/**
 * Linear Search - O(n)
 * Find element in unsorted array
 *
 * @param array<mixed> $arr Array to search
 * @param mixed $target Value to find
 * @return int|false Index of target, or false if not found
 */
function linearSearch(array $arr, $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

// ============================================================================
// CACHING EXAMPLES
// ============================================================================

/**
 * Simple in-memory cache with TTL support
 */
class SimpleCache
{
    private array $store = [];

    /**
     * Remember/memoize the result of an expensive operation
     *
     * @param string $key Cache key
     * @param callable $callback Function to call if cache miss
     * @param int $ttl Time to live in seconds (0 = forever)
     * @return mixed Cached or computed value
     */
    public function remember(string $key, callable $callback, int $ttl = 0): mixed
    {
        // Check if key exists and hasn't expired
        if (isset($this->store[$key])) {
            $entry = $this->store[$key];

            if ($entry['expires'] === 0 || $entry['expires'] > time()) {
                return $entry['value'];
            }
        }

        // Cache miss - compute value
        $value = $callback();

        // Store with expiration
        $this->store[$key] = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0
        ];

        return $value;
    }

    /**
     * Clear all cached values
     */
    public function clear(): void
    {
        $this->store = [];
    }

    /**
     * Remove specific key from cache
     */
    public function forget(string $key): void
    {
        unset($this->store[$key]);
    }
}

// ============================================================================
// SORTING EXAMPLES
// ============================================================================

/**
 * Quick sort implementation - O(n log n) average case
 *
 * @param array<int> $arr Array to sort
 * @return array<int> Sorted array
 */
function quickSort(array $arr): array
{
    if (count($arr) < 2) {
        return $arr;
    }

    $pivot = $arr[0];
    $left = [];
    $right = [];

    for ($i = 1; $i < count($arr); $i++) {
        if ($arr[$i] < $pivot) {
            $left[] = $arr[$i];
        } else {
            $right[] = $arr[$i];
        }
    }

    return array_merge(quickSort($left), [$pivot], quickSort($right));
}

// ============================================================================
// FILE PROCESSING EXAMPLES
// ============================================================================

/**
 * Stream large file using generator - O(1) memory
 *
 * @param string $filename Path to file
 * @return \Generator<string> Lines from file
 */
function readLargeFile(string $filename): \Generator
{
    if (!file_exists($filename)) {
        throw new \RuntimeException("File not found: {$filename}");
    }

    $handle = fopen($filename, 'r');

    if ($handle === false) {
        throw new \RuntimeException("Could not open file: {$filename}");
    }

    try {
        while (($line = fgets($handle)) !== false) {
            yield $line;
        }
    } finally {
        fclose($handle);
    }
}

// ============================================================================
// SHORTEST PATH (BFS)
// ============================================================================

/**
 * Find shortest path in unweighted graph using BFS
 *
 * @param array<int, array<int>> $graph Adjacency list
 * @param int $start Start node
 * @param int $end End node
 * @return array<int>|null Path from start to end, or null if no path exists
 */
function shortestPath(array $graph, int $start, int $end): ?array
{
    $queue = [[$start]];
    $visited = [$start => true];

    while (!empty($queue)) {
        $path = array_shift($queue);
        $node = end($path);

        if ($node === $end) {
            return $path;
        }

        foreach ($graph[$node] ?? [] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $visited[$neighbor] = true;
                $newPath = array_merge($path, [$neighbor]);
                $queue[] = $newPath;
            }
        }
    }

    return null; // No path found
}

// ============================================================================
// COMMON PATTERNS
// ============================================================================

/**
 * Two Sum - Find pair that sums to target
 *
 * @param array<int> $nums Array of integers
 * @param int $target Target sum
 * @return array{int, int}|null Indices of two numbers, or null
 */
function twoSum(array $nums, int $target): ?array
{
    $seen = [];

    foreach ($nums as $i => $num) {
        $complement = $target - $num;

        if (isset($seen[$complement])) {
            return [$seen[$complement], $i];
        }

        $seen[$num] = $i;
    }

    return null;
}

/**
 * Maximum sum of k consecutive elements (Sliding Window)
 *
 * @param array<int> $nums Array of integers
 * @param int $k Window size
 * @return int Maximum sum
 */
function maxSumSubarray(array $nums, int $k): int
{
    if (count($nums) < $k) {
        throw new \InvalidArgumentException('Array too small for window size');
    }

    // Calculate first window
    $windowSum = array_sum(array_slice($nums, 0, $k));
    $maxSum = $windowSum;

    // Slide window
    for ($i = $k; $i < count($nums); $i++) {
        $windowSum = $windowSum - $nums[$i - $k] + $nums[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

// ============================================================================
// PERFORMANCE UTILITIES
// ============================================================================

/**
 * Simple benchmark function
 *
 * @param callable $fn Function to benchmark
 * @param array<mixed> $args Arguments to pass
 * @return float Execution time in milliseconds
 */
function benchmark(callable $fn, array $args = []): float
{
    $start = hrtime(true);
    $fn(...$args);
    $end = hrtime(true);

    return ($end - $start) / 1_000_000; // Convert to milliseconds
}

/**
 * Check current memory usage
 *
 * @return string Formatted memory usage
 */
function memoryUsage(): string
{
    $bytes = memory_get_usage(true);
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;

    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Quick Start Examples ===\n\n";

    // Binary Search Demo
    echo "1. Binary Search:\n";
    $sortedArray = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];
    $target = 13;
    $index = binarySearch($sortedArray, $target);
    echo "   Finding {$target} in array: Index = {$index}\n";
    echo "   Time: " . benchmark(fn() => binarySearch($sortedArray, $target)) . " ms\n\n";

    // Cache Demo
    echo "2. Simple Cache:\n";
    $cache = new SimpleCache();

    $expensiveOperation = function() {
        usleep(100000); // Simulate 100ms operation
        return random_int(1, 100);
    };

    $time1 = benchmark(fn() => $cache->remember('expensive', $expensiveOperation));
    echo "   First call (cache miss): {$time1} ms\n";

    $time2 = benchmark(fn() => $cache->remember('expensive', $expensiveOperation));
    echo "   Second call (cache hit): {$time2} ms\n";
    echo "   Speedup: " . round($time1 / max($time2, 0.001), 2) . "x\n\n";

    // Sorting Demo
    echo "3. Quick Sort:\n";
    $unsorted = [64, 34, 25, 12, 22, 11, 90];
    echo "   Unsorted: " . implode(', ', $unsorted) . "\n";
    $sorted = quickSort($unsorted);
    echo "   Sorted: " . implode(', ', $sorted) . "\n";
    echo "   Time: " . benchmark(fn() => quickSort($unsorted)) . " ms\n\n";

    // Two Sum Demo
    echo "4. Two Sum Pattern:\n";
    $nums = [2, 7, 11, 15];
    $target = 9;
    $result = twoSum($nums, $target);
    if ($result) {
        echo "   Numbers at indices {$result[0]} and {$result[1]} sum to {$target}\n";
        echo "   Values: {$nums[$result[0]]} + {$nums[$result[1]]} = {$target}\n\n";
    }

    // Sliding Window Demo
    echo "5. Sliding Window (Max Sum of K elements):\n";
    $numbers = [2, 1, 5, 1, 3, 2];
    $k = 3;
    $maxSum = maxSumSubarray($numbers, $k);
    echo "   Array: " . implode(', ', $numbers) . "\n";
    echo "   K = {$k}\n";
    echo "   Max sum: {$maxSum}\n\n";

    // Shortest Path Demo
    echo "6. Shortest Path (BFS):\n";
    $graph = [
        0 => [1, 2],
        1 => [0, 3, 4],
        2 => [0, 5],
        3 => [1],
        4 => [1, 5],
        5 => [2, 4]
    ];
    $path = shortestPath($graph, 0, 5);
    echo "   Path from 0 to 5: " . implode(' -> ', $path ?? []) . "\n";
    echo "   Length: " . (count($path ?? []) - 1) . " edges\n\n";

    // Memory Usage
    echo "7. Memory Usage:\n";
    echo "   Current: " . memoryUsage() . "\n";
    echo "   Peak: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
}
