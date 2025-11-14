<?php
/**
 * Recursion Fundamentals
 *
 * Learn recursion through practical examples
 *
 * @package PHPAlgorithms
 * @chapter 03
 */

declare(strict_types=1);

// ============================================================================
// BASIC RECURSION
// ============================================================================

/**
 * Countdown - Simple recursion example
 */
function countdown(int $n): void
{
    if ($n <= 0) {
        echo "Blast off!\n";
        return;
    }
    echo "$n...\n";
    countdown($n - 1);
}

/**
 * Factorial - Classic recursion
 */
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1; // Base case
    }
    return $n * factorial($n - 1); // Recursive case
}

/**
 * Fibonacci - Naive implementation O(2^n)
 */
function fibonacci_Slow(int $n): int
{
    if ($n <= 1) return $n;
    return fibonacci_Slow($n - 1) + fibonacci_Slow($n - 2);
}

/**
 * Fibonacci - With memoization O(n)
 */
function fibonacci_Fast(int $n, array &$memo = []): int
{
    if ($n <= 1) return $n;

    if (isset($memo[$n])) {
        return $memo[$n];
    }

    $memo[$n] = fibonacci_Fast($n - 1, $memo) + fibonacci_Fast($n - 2, $memo);
    return $memo[$n];
}

/**
 * Fibonacci - Iterative (best for this problem)
 */
function fibonacci_Iterative(int $n): int
{
    if ($n <= 1) return $n;

    $prev = 0;
    $curr = 1;

    for ($i = 2; $i <= $n; $i++) {
        $next = $prev + $curr;
        $prev = $curr;
        $curr = $next;
    }

    return $curr;
}

// ============================================================================
// RECURSIVE DATA STRUCTURES
// ============================================================================

/**
 * Sum nested array recursively
 */
function sumNested(array $arr): int
{
    $sum = 0;

    foreach ($arr as $item) {
        if (is_array($item)) {
            $sum += sumNested($item); // Recursive case
        } else {
            $sum += $item; // Base case
        }
    }

    return $sum;
}

/**
 * Flatten nested array
 */
function flatten(array $arr): array
{
    $result = [];

    foreach ($arr as $item) {
        if (is_array($item)) {
            $result = array_merge($result, flatten($item));
        } else {
            $result[] = $item;
        }
    }

    return $result;
}

/**
 * Directory tree traversal
 */
function listFiles(string $directory, int $depth = 0): void
{
    if (!is_dir($directory)) return;

    $indent = str_repeat('  ', $depth);
    $items = scandir($directory);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $directory . '/' . $item;
        echo $indent . $item . "\n";

        if (is_dir($path)) {
            listFiles($path, $depth + 1); // Recursive
        }
    }
}

// ============================================================================
// DIVIDE AND CONQUER
// ============================================================================

/**
 * Binary search - recursive
 */
function binarySearchRecursive(array $arr, int $target, int $left = 0, int $right = null): int
{
    if ($right === null) $right = count($arr) - 1;

    if ($left > $right) return -1; // Base case: not found

    $mid = (int)(($left + $right) / 2);

    if ($arr[$mid] === $target) {
        return $mid; // Base case: found
    }

    if ($arr[$mid] < $target) {
        return binarySearchRecursive($arr, $target, $mid + 1, $right);
    }

    return binarySearchRecursive($arr, $target, $left, $mid - 1);
}

/**
 * Power function - recursive
 */
function power(int $base, int $exp): int
{
    if ($exp === 0) return 1; // Base case

    // Optimization: exponentiation by squaring
    if ($exp % 2 === 0) {
        $half = power($base, $exp / 2);
        return $half * $half;
    }

    return $base * power($base, $exp - 1);
}

// ============================================================================
// BACKTRACKING
// ============================================================================

/**
 * Generate all permutations
 */
function permutations(array $items): array
{
    if (count($items) <= 1) {
        return [$items]; // Base case
    }

    $result = [];

    foreach ($items as $i => $item) {
        $remaining = array_merge(
            array_slice($items, 0, $i),
            array_slice($items, $i + 1)
        );

        foreach (permutations($remaining) as $perm) {
            $result[] = array_merge([$item], $perm);
        }
    }

    return $result;
}

/**
 * Generate all subsets
 */
function subsets(array $set): array
{
    if (empty($set)) {
        return [[]]; // Base case
    }

    $first = array_shift($set);
    $subsetsWithoutFirst = subsets($set);
    $subsetsWithFirst = array_map(
        fn($subset) => array_merge([$first], $subset),
        $subsetsWithoutFirst
    );

    return array_merge($subsetsWithoutFirst, $subsetsWithFirst);
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Recursion Fundamentals ===\n\n";

    // Countdown
    echo "1. Countdown:\n";
    countdown(5);

    // Factorial
    echo "\n2. Factorial:\n";
    for ($n = 0; $n <= 5; $n++) {
        echo "   {$n}! = " . factorial($n) . "\n";
    }

    // Fibonacci comparison
    echo "\n3. Fibonacci Comparison:\n";
    foreach ([10, 20, 30] as $n) {
        $start = hrtime(true);
        $result = fibonacci_Fast($n);
        $time1 = (hrtime(true) - $start) / 1_000_000;

        $start = hrtime(true);
        $result = fibonacci_Iterative($n);
        $time2 = (hrtime(true) - $start) / 1_000_000;

        printf("   fib(%d) = %d (Recursive: %.3fms, Iterative: %.3fms)\n",
            $n, $result, $time1, $time2);
    }

    // Nested array
    echo "\n4. Nested Array Operations:\n";
    $nested = [1, [2, 3, [4, 5]], 6, [7, [8, 9]]];
    echo "   Nested: " . json_encode($nested) . "\n";
    echo "   Sum: " . sumNested($nested) . "\n";
    echo "   Flattened: " . json_encode(flatten($nested)) . "\n";

    // Binary search
    echo "\n5. Recursive Binary Search:\n";
    $sorted = [1, 3, 5, 7, 9, 11, 13, 15];
    $target = 7;
    $index = binarySearchRecursive($sorted, $target);
    echo "   Finding {$target} in [" . implode(', ', $sorted) . "]\n";
    echo "   Found at index: {$index}\n";

    // Permutations
    echo "\n6. Permutations:\n";
    $items = [1, 2, 3];
    $perms = permutations($items);
    echo "   Items: [" . implode(', ', $items) . "]\n";
    echo "   Total permutations: " . count($perms) . "\n";
    echo "   First 3: \n";
    foreach (array_slice($perms, 0, 3) as $perm) {
        echo "     [" . implode(', ', $perm) . "]\n";
    }

    echo "\n=== Key Takeaways ===\n";
    echo "• Every recursion needs a base case (stopping condition)\n";
    echo "• Recursive case must make progress toward base case\n";
    echo "• Memoization can dramatically improve performance\n";
    echo "• Sometimes iteration is better than recursion\n";
    echo "• Recursion excels with tree/nested structures\n";
}
