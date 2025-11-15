<?php

declare(strict_types=1);

/**
 * Comprehensive Search Algorithms Comparison
 *
 * Demonstrates:
 * - Performance benchmarks across all search algorithms
 * - Best/average/worst case scenarios
 * - Different data sizes and types
 * - Algorithm selection guide
 * - Real-world performance metrics
 */

echo "=== Search Algorithms: Comprehensive Comparison ===\n\n";

// Implement all search algorithms

function linearSearch(array $arr, mixed $target): ?int
{
    foreach ($arr as $index => $value) {
        if ($value === $target) return $index;
    }
    return null;
}

function binarySearch(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) return $mid;

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return null;
}

function jumpSearch(array $arr, mixed $target): ?int
{
    $n = count($arr);
    $step = (int)sqrt($n);
    $prev = 0;

    while ($arr[min($step, $n) - 1] < $target) {
        $prev = $step;
        $step += (int)sqrt($n);
        if ($prev >= $n) return null;
    }

    while ($arr[$prev] < $target) {
        $prev++;
        if ($prev === min($step, $n)) return null;
    }

    return $arr[$prev] === $target ? $prev : null;
}

function interpolationSearch(array $arr, int $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right && $target >= $arr[$left] && $target <= $arr[$right]) {
        if ($left === $right) {
            return $arr[$left] === $target ? $left : null;
        }

        $pos = $left + (int)(
            (($target - $arr[$left]) / ($arr[$right] - $arr[$left])) *
            ($right - $left)
        );

        if ($arr[$pos] === $target) return $pos;

        if ($arr[$pos] < $target) {
            $left = $pos + 1;
        } else {
            $right = $pos - 1;
        }
    }

    return null;
}

function exponentialSearch(array $arr, mixed $target): ?int
{
    $n = count($arr);
    if ($n === 0) return null;
    if ($arr[0] === $target) return 0;

    $i = 1;
    while ($i < $n && $arr[$i] <= $target) {
        $i *= 2;
    }

    $left = (int)($i / 2);
    $right = min($i, $n - 1);

    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) return $mid;

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return null;
}

// Test 1: Algorithm Summary
echo "1. Search Algorithm Summary\n";
echo "============================\n\n";

echo "Algorithm      | Time (Best) | Time (Avg) | Time (Worst) | Space | Requirements\n";
echo "---------------|-------------|------------|--------------|-------|--------------\n";
echo "Linear         | O(1)        | O(n)       | O(n)         | O(1)  | None\n";
echo "Binary         | O(1)        | O(log n)   | O(log n)     | O(1)  | Sorted array\n";
echo "Jump           | O(1)        | O(√n)      | O(√n)        | O(1)  | Sorted array\n";
echo "Interpolation  | O(1)        | O(log log n)| O(n)        | O(1)  | Sorted + uniform\n";
echo "Exponential    | O(1)        | O(log n)   | O(log n)     | O(1)  | Sorted array\n";
echo "Hash Table     | O(1)        | O(1)       | O(n)         | O(n)  | Hash function\n";

echo "\n\n";

// Test 2: Small Dataset Performance
echo "2. Small Dataset Performance (100 elements)\n";
echo "============================================\n";

$size = 100;
$arr = range(1, $size);
$target = 75;

$results = [];

// Linear search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    linearSearch($arr, $target);
}
$results['Linear'] = (microtime(true) - $start) * 1000;

// Binary search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    binarySearch($arr, $target);
}
$results['Binary'] = (microtime(true) - $start) * 1000;

// Jump search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    jumpSearch($arr, $target);
}
$results['Jump'] = (microtime(true) - $start) * 1000;

// Interpolation search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    interpolationSearch($arr, $target);
}
$results['Interpolation'] = (microtime(true) - $start) * 1000;

asort($results);

echo "Algorithm      | Time (10000 searches)\n";
echo "---------------|----------------------\n";

foreach ($results as $name => $time) {
    echo sprintf("%-14s | %17.3f ms\n", $name, $time);
}

echo "\nFor small datasets, differences are minimal\n\n";

// Test 3: Large Dataset Performance
echo "3. Large Dataset Performance (100,000 elements)\n";
echo "================================================\n";

$size = 100000;
$arr = range(1, $size);
$target = 75000;

$results = [];

// Linear search (reduce iterations)
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    linearSearch($arr, $target);
}
$results['Linear'] = (microtime(true) - $start) * 1000;

// Binary search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    binarySearch($arr, $target);
}
$results['Binary'] = (microtime(true) - $start) * 1000 / 100; // Normalize

// Jump search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    jumpSearch($arr, $target);
}
$results['Jump'] = (microtime(true) - $start) * 1000 / 100;

// Interpolation search
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    interpolationSearch($arr, $target);
}
$results['Interpolation'] = (microtime(true) - $start) * 1000 / 100;

asort($results);

echo "Algorithm      | Time (normalized)\n";
echo "---------------|------------------\n";

foreach ($results as $name => $time) {
    echo sprintf("%-14s | %13.3f ms\n", $name, $time);
}

echo "\nInterpolation and Binary dominate for large datasets!\n\n";

// Test 4: Different Search Positions
echo "4. Performance by Target Position\n";
echo "===================================\n";

$arr = range(1, 10000);

$positions = [
    'First (index 0)' => 1,
    'Quarter (2500)' => 2500,
    'Middle (5000)' => 5000,
    'Three-quarter (7500)' => 7500,
    'Last (10000)' => 10000,
];

echo str_pad('Position', 20) . " | Linear | Binary | Jump\n";
echo str_repeat('-', 20) . " | ------ | ------ | ----\n";

foreach ($positions as $desc => $target) {
    // Linear
    $comparisons = $target; // Linear always checks target's index comparisons

    // Binary (approximate)
    $binaryComp = (int)ceil(log($target, 2));

    // Jump (approximate)
    $jumpComp = (int)ceil(sqrt(10000));

    echo sprintf("%-20s | %6d | %6d | %4d\n", $desc, $comparisons, $binaryComp, $jumpComp);
}

echo "\nBinary search consistent regardless of position!\n\n";

// Test 5: Scaling Behavior
echo "5. Scaling Behavior (How time grows with n)\n";
echo "=============================================\n";

$sizes = [100, 1000, 10000, 100000];

echo "Size     | Linear   | Binary  | Jump    | Interpolation\n";
echo "---------|----------|---------|---------|---------------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    $target = $size - 10;

    // Linear
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        linearSearch($arr, $target);
    }
    $linearTime = (microtime(true) - $start) * 1000;

    // Binary
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        binarySearch($arr, $target);
    }
    $binaryTime = (microtime(true) - $start) * 1000;

    // Jump
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        jumpSearch($arr, $target);
    }
    $jumpTime = (microtime(true) - $start) * 1000;

    // Interpolation
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        interpolationSearch($arr, $target);
    }
    $interpTime = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%8d | %6.2f ms | %5.2f ms | %5.2f ms | %11.2f ms\n",
        $size,
        $linearTime,
        $binaryTime,
        $jumpTime,
        $interpTime
    );
}

echo "\nObservation: Linear grows linearly, others grow logarithmically\n\n";

// Test 6: Uniform vs Non-Uniform Data
echo "6. Uniform vs Non-Uniform Distribution\n";
echo "========================================\n";

$uniform = range(1, 10000);
$nonUniform = array_merge(
    range(1, 10),
    range(100, 110),
    range(1000, 1010),
    range(10000, 10990)
);

$target = 10000;

echo "Testing on 10,000 element arrays\n\n";

// Uniform
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    interpolationSearch($uniform, $target);
}
$uniformTime = (microtime(true) - $start) * 1000;

// Binary on uniform
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    binarySearch($uniform, $target);
}
$uniformBinaryTime = (microtime(true) - $start) * 1000;

// Non-uniform
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    interpolationSearch($nonUniform, $target);
}
$nonUniformTime = (microtime(true) - $start) * 1000;

// Binary on non-uniform
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    binarySearch($nonUniform, $target);
}
$nonUniformBinaryTime = (microtime(true) - $start) * 1000;

echo "Uniform Data:\n";
echo "  Interpolation: " . number_format($uniformTime, 3) . " ms\n";
echo "  Binary:        " . number_format($uniformBinaryTime, 3) . " ms\n\n";

echo "Non-Uniform Data:\n";
echo "  Interpolation: " . number_format($nonUniformTime, 3) . " ms\n";
echo "  Binary:        " . number_format($nonUniformBinaryTime, 3) . " ms\n\n";

echo "Binary search is consistent; Interpolation varies with distribution\n\n";

// Test 7: Memory Usage Comparison
echo "7. Memory Usage Comparison\n";
echo "===========================\n\n";

echo "Algorithm      | Extra Space | Notes\n";
echo "---------------|-------------|--------------------------------\n";
echo "Linear         | O(1)        | Only loop variable\n";
echo "Binary (iter)  | O(1)        | Only left, right, mid variables\n";
echo "Binary (rec)   | O(log n)    | Recursion stack\n";
echo "Jump           | O(1)        | Step and prev variables\n";
echo "Interpolation  | O(1)        | Position calculation\n";
echo "Exponential    | O(1)        | Range finding + binary\n";
echo "Hash Table     | O(n)        | Separate storage structure\n";

echo "\n";

// Test 8: Best Use Cases
echo "8. Algorithm Selection Guide\n";
echo "==============================\n\n";

echo "Linear Search:\n";
echo "  ✓ Unsorted data\n";
echo "  ✓ Small datasets (< 100)\n";
echo "  ✓ Single search operation\n";
echo "  Example: Search in unsorted user input\n\n";

echo "Binary Search:\n";
echo "  ✓ Sorted data\n";
echo "  ✓ Large datasets (1000+)\n";
echo "  ✓ General-purpose solution\n";
echo "  Example: Dictionary lookup, database index\n\n";

echo "Jump Search:\n";
echo "  ✓ Sorted data\n";
echo "  ✓ Sequential access costly\n";
echo "  ✓ Backward jumps expensive\n";
echo "  Example: Linked list search, tape storage\n\n";

echo "Interpolation Search:\n";
echo "  ✓ Sorted + uniformly distributed\n";
echo "  ✓ Numeric data\n";
echo "  ✓ Very large datasets\n";
echo "  Example: Sequential IDs, timestamps\n\n";

echo "Exponential Search:\n";
echo "  ✓ Unbounded/infinite arrays\n";
echo "  ✓ Target near beginning likely\n";
echo "  ✓ Unknown array size\n";
echo "  Example: Log files, streaming data\n\n";

echo "Hash Table:\n";
echo "  ✓ Key-value lookups\n";
echo "  ✓ Need O(1) performance\n";
echo "  ✓ Extra memory available\n";
echo "  Example: Caching, counting, indexing\n\n";

// Test 9: Worst Case Scenarios
echo "9. Worst Case Scenarios\n";
echo "========================\n\n";

echo "Linear Search Worst Case:\n";
echo "  • Target at end or not found\n";
echo "  • Must check all n elements\n";
echo "  • O(n) comparisons\n\n";

echo "Binary Search Worst Case:\n";
echo "  • Target at edge or not found\n";
echo "  • Still O(log n) comparisons\n";
echo "  • Very predictable\n\n";

echo "Jump Search Worst Case:\n";
echo "  • Target just after a jump\n";
echo "  • O(√n) comparisons\n";
echo "  • Between linear and binary\n\n";

echo "Interpolation Worst Case:\n";
echo "  • Non-uniform distribution\n";
echo "  • Can degrade to O(n)\n";
echo "  • Unpredictable on skewed data\n\n";

echo "Hash Table Worst Case:\n";
echo "  • All keys hash to same bucket\n";
echo "  • Degrades to O(n)\n";
echo "  • Rare with good hash function\n\n";

// Test 10: Performance Summary
echo "10. Performance Summary\n";
echo "========================\n\n";

echo "Speedup Comparison (vs Linear Search):\n";
echo "Dataset size: 1,000,000 elements\n\n";

$size = 1000000;
$arr = range(1, $size);
$target = 999999;

// Linear (sample)
$start = microtime(true);
for ($i = 0; $i < 10; $i++) {
    linearSearch($arr, $target);
}
$linearTime = (microtime(true) - $start) * 1000;

// Binary
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    binarySearch($arr, $target);
}
$binaryTime = (microtime(true) - $start) * 1000 / 100;

// Interpolation
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    interpolationSearch($arr, $target);
}
$interpTime = (microtime(true) - $start) * 1000 / 100;

echo "Algorithm      | Time      | Speedup vs Linear\n";
echo "---------------|-----------|-------------------\n";
echo sprintf("Linear         | %7.2f ms | 1.0x (baseline)\n", $linearTime);
echo sprintf("Binary         | %7.2f ms | %.1fx faster\n", $binaryTime, $linearTime / $binaryTime);
echo sprintf("Interpolation  | %7.2f ms | %.1fx faster\n", $interpTime, $linearTime / $interpTime);

echo "\nFor 1 million elements:\n";
echo "  • Linear needs ~1,000,000 comparisons\n";
echo "  • Binary needs ~20 comparisons (50,000x fewer!)\n";
echo "  • Interpolation needs ~6 comparisons (166,000x fewer!)\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "Time Complexity Comparison:\n";
echo "  n=100     | Linear: ~100  | Binary: ~7   | Jump: ~10\n";
echo "  n=10,000  | Linear: ~10k  | Binary: ~13  | Jump: ~100\n";
echo "  n=1M      | Linear: ~1M   | Binary: ~20  | Jump: ~1000\n\n";

echo "Choosing the Right Algorithm:\n";
echo "  1. Data sorted? → Binary, Jump, or Interpolation\n";
echo "  2. Data unsorted? → Linear or Hash Table\n";
echo "  3. Need O(1)? → Hash Table\n";
echo "  4. Uniform distribution? → Interpolation\n";
echo "  5. Memory constrained? → Binary (iterative)\n";
echo "  6. Unknown size? → Exponential\n\n";

echo "Real-World Performance:\n";
echo "  • Small data (< 100): Use simple linear search\n";
echo "  • Medium data (100-10K): Binary search is best\n";
echo "  • Large uniform data (10K+): Interpolation search\n";
echo "  • Need fastest possible: Hash table (O(1))\n\n";

echo "Common Mistakes:\n";
echo "  ⚠ Using linear search on sorted data\n";
echo "  ⚠ Using interpolation on non-uniform data\n";
echo "  ⚠ Forgetting data must be sorted for binary search\n";
echo "  ⚠ Not considering hash tables for frequent lookups\n";

echo "\n✅ Complete! Search algorithm comparison demonstrated.\n";
