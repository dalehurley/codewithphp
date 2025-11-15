<?php

declare(strict_types=1);

/**
 * Interpolation Search and Jump Search
 *
 * Demonstrates:
 * - Interpolation search for uniformly distributed data
 * - Jump search (block search)
 * - Exponential search
 * - Comparison with binary search
 * - When to use each algorithm
 */

echo "=== Interpolation Search and Jump Search ===\n\n";

/**
 * Interpolation Search - O(log log n) for uniform data
 */
function interpolationSearch(array $arr, int $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right && $target >= $arr[$left] && $target <= $arr[$right]) {
        // Handle single element
        if ($left === $right) {
            return $arr[$left] === $target ? $left : null;
        }

        // Interpolation formula
        $pos = $left + (int)(
            (($target - $arr[$left]) / ($arr[$right] - $arr[$left])) *
            ($right - $left)
        );

        if ($arr[$pos] === $target) {
            return $pos;
        }

        if ($arr[$pos] < $target) {
            $left = $pos + 1;
        } else {
            $right = $pos - 1;
        }
    }

    return null;
}

/**
 * Interpolation search with stats
 */
function interpolationSearchWithStats(array $arr, int $target): array
{
    $comparisons = 0;
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right && $target >= $arr[$left] && $target <= $arr[$right]) {
        $comparisons++;

        if ($left === $right) {
            if ($arr[$left] === $target) {
                return ['index' => $left, 'comparisons' => $comparisons];
            }
            break;
        }

        $pos = $left + (int)(
            (($target - $arr[$left]) / ($arr[$right] - $arr[$left])) *
            ($right - $left)
        );

        if ($arr[$pos] === $target) {
            return ['index' => $pos, 'comparisons' => $comparisons];
        }

        if ($arr[$pos] < $target) {
            $left = $pos + 1;
        } else {
            $right = $pos - 1;
        }
    }

    return ['index' => null, 'comparisons' => $comparisons];
}

/**
 * Jump Search - O(√n)
 */
function jumpSearch(array $arr, mixed $target): ?int
{
    $n = count($arr);
    $step = (int)sqrt($n);
    $prev = 0;

    // Jump to find block
    while ($arr[min($step, $n) - 1] < $target) {
        $prev = $step;
        $step += (int)sqrt($n);

        if ($prev >= $n) {
            return null;
        }
    }

    // Linear search in block
    while ($arr[$prev] < $target) {
        $prev++;

        if ($prev === min($step, $n)) {
            return null;
        }
    }

    if ($arr[$prev] === $target) {
        return $prev;
    }

    return null;
}

/**
 * Jump search with stats
 */
function jumpSearchWithStats(array $arr, mixed $target): array
{
    $n = count($arr);
    $step = (int)sqrt($n);
    $prev = 0;
    $comparisons = 0;

    // Jump phase
    while ($arr[min($step, $n) - 1] < $target) {
        $comparisons++;
        $prev = $step;
        $step += (int)sqrt($n);

        if ($prev >= $n) {
            return ['index' => null, 'comparisons' => $comparisons, 'jumps' => $prev / (int)sqrt($n)];
        }
    }

    // Linear search phase
    $linearComparisons = 0;
    while ($arr[$prev] < $target) {
        $comparisons++;
        $linearComparisons++;
        $prev++;

        if ($prev === min($step, $n)) {
            return ['index' => null, 'comparisons' => $comparisons, 'jumps' => 0];
        }
    }

    $comparisons++;
    if ($arr[$prev] === $target) {
        return ['index' => $prev, 'comparisons' => $comparisons, 'linear_comparisons' => $linearComparisons];
    }

    return ['index' => null, 'comparisons' => $comparisons, 'linear_comparisons' => $linearComparisons];
}

/**
 * Exponential Search - O(log n)
 */
function exponentialSearch(array $arr, mixed $target): ?int
{
    $n = count($arr);

    if ($n === 0) return null;
    if ($arr[0] === $target) return 0;

    // Find range for binary search
    $i = 1;
    while ($i < $n && $arr[$i] <= $target) {
        $i *= 2;
    }

    // Binary search in found range
    return binarySearchRange($arr, $target, (int)($i / 2), min($i, $n - 1));
}

/**
 * Binary search in a range
 */
function binarySearchRange(array $arr, mixed $target, int $left, int $right): ?int
{
    while ($left <= $right) {
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return null;
}

/**
 * Binary search with stats (for comparison)
 */
function binarySearchWithStats(array $arr, mixed $target): array
{
    $comparisons = 0;
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $comparisons++;
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            return ['index' => $mid, 'comparisons' => $comparisons];
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return ['index' => null, 'comparisons' => $comparisons];
}

// Test 1: Interpolation Search on Uniform Data
echo "1. Interpolation Search (Uniform Distribution)\n";
echo "================================================\n";

$uniform = range(10, 1000, 10); // [10, 20, 30, ..., 1000]
echo "Array: [10, 20, 30, ..., 1000] (100 elements, uniform)\n\n";

$searches = [100, 500, 900, 1000];

foreach ($searches as $target) {
    $result = interpolationSearchWithStats($uniform, $target);

    if ($result['index'] !== null) {
        echo "Search for $target: Found at index {$result['index']} ({$result['comparisons']} comparisons)\n";
    } else {
        echo "Search for $target: Not found ({$result['comparisons']} comparisons)\n";
    }
}

echo "\nInterpolation search probes directly near target!\n\n";

// Test 2: Comparison - Interpolation vs Binary
echo "2. Interpolation vs Binary Search\n";
echo "===================================\n";

$sizes = [100, 1000, 10000];

echo "Size    | Interpolation | Binary  | Winner\n";
echo "--------|---------------|---------|--------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    $target = $size - 10; // Near end

    $interpResult = interpolationSearchWithStats($arr, $target);
    $binaryResult = binarySearchWithStats($arr, $target);

    $winner = $interpResult['comparisons'] < $binaryResult['comparisons'] ? 'Interp' : 'Binary';

    echo sprintf(
        "%7d | %13d | %7d | %s\n",
        $size,
        $interpResult['comparisons'],
        $binaryResult['comparisons'],
        $winner
    );
}

echo "\nInterpolation wins on uniform data!\n\n";

// Test 3: Jump Search
echo "3. Jump Search (Block Search)\n";
echo "===============================\n";

$arr = range(0, 100);
echo "Array: [0, 1, 2, ..., 100]\n";
echo "Block size (step): √n = √101 ≈ 10\n\n";

$searches = [55, 100, 0, 75];

foreach ($searches as $target) {
    $result = jumpSearchWithStats($arr, $target);

    if ($result['index'] !== null) {
        echo "Search for $target: Found at index {$result['index']}\n";
        echo "  Total comparisons: {$result['comparisons']}\n";
        if (isset($result['linear_comparisons'])) {
            echo "  Linear comparisons in block: {$result['linear_comparisons']}\n";
        }
    } else {
        echo "Search for $target: Not found\n";
    }
    echo "\n";
}

// Test 4: Jump Search Visualization
echo "4. Jump Search Step-by-Step\n";
echo "=============================\n";

$arr = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
$target = 11;
$step = (int)sqrt(count($arr)); // 4

echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Target: $target\n";
echo "Step size: √16 = 4\n\n";

echo "Jump phase:\n";
$prev = 0;
$jumpCount = 0;

while ($arr[min($step, count($arr)) - 1] < $target) {
    $jumpCount++;
    echo "  Jump $jumpCount: Check arr[" . (min($step, count($arr)) - 1) . "] = {$arr[min($step, count($arr)) - 1]} < $target\n";
    $prev = $step;
    $step += 4;
}

echo "\nLinear search phase in block [$prev.." . (min($step, count($arr)) - 1) . "]:\n";

for ($i = $prev; $i < min($step, count($arr)); $i++) {
    echo "  Check arr[$i] = {$arr[$i]}\n";
    if ($arr[$i] === $target) {
        echo "  Found!\n";
        break;
    }
}

echo "\n";

// Test 5: Exponential Search
echo "5. Exponential Search\n";
echo "======================\n";

$arr = range(1, 1000);
echo "Array: [1, 2, 3, ..., 1000]\n\n";

$searches = [1, 10, 100, 500, 1000];

echo "Target | Found at | Range Found\n";
echo "-------|----------|-------------\n";

foreach ($searches as $target) {
    $result = exponentialSearch($arr, $target);

    // Determine range
    $i = 1;
    while ($i < count($arr) && $arr[$i] <= $target) {
        $i *= 2;
    }
    $rangeStart = (int)($i / 2);
    $rangeEnd = min($i, count($arr) - 1);

    echo sprintf(
        "%6d | %8d | [%d..%d]\n",
        $target,
        $result,
        $rangeStart,
        $rangeEnd
    );
}

echo "\nExponential search finds range, then uses binary search\n\n";

// Test 6: Performance Comparison
echo "6. Performance Comparison: All Algorithms\n";
echo "===========================================\n";

$size = 10000;
$arr = range(1, $size);
$target = $size - 100;

$algorithms = [
    'Binary' => fn() => array_search($target, $arr, true),
    'Interpolation' => fn() => interpolationSearch($arr, $target),
    'Jump' => fn() => jumpSearch($arr, $target),
    'Exponential' => fn() => exponentialSearch($arr, $target),
];

echo "Array size: $size (uniform distribution)\n";
echo "Target: $target\n\n";

echo "Algorithm      | Time (1000 runs)\n";
echo "---------------|------------------\n";

foreach ($algorithms as $name => $func) {
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $func();
    }
    $time = (microtime(true) - $start) * 1000;

    echo sprintf("%-14s | %12.3f ms\n", $name, $time);
}

echo "\n";

// Test 7: Non-Uniform Data (Where Interpolation Fails)
echo "7. Interpolation on Non-Uniform Data\n";
echo "======================================\n";

$nonUniform = [1, 2, 3, 4, 5, 100, 200, 300, 400, 500, 10000];
echo "Non-uniform: [" . implode(', ', $nonUniform) . "]\n\n";

$target = 100;

$interpResult = interpolationSearchWithStats($nonUniform, $target);
$binaryResult = binarySearchWithStats($nonUniform, $target);

echo "Search for $target:\n";
echo "  Interpolation: {$interpResult['comparisons']} comparisons\n";
echo "  Binary:        {$binaryResult['comparisons']} comparisons\n\n";

echo "Binary search wins on non-uniform data!\n\n";

// Test 8: Optimal Step Size for Jump Search
echo "8. Jump Search: Optimal Step Size\n";
echo "===================================\n";

$arr = range(1, 10000);
$target = 9999;

echo "Testing different step sizes:\n\n";

echo "Step Size | Comparisons\n";
echo "----------|------------\n";

$stepSizes = [10, 50, 100, (int)sqrt(10000), 500, 1000];

foreach ($stepSizes as $stepSize) {
    $n = count($arr);
    $step = $stepSize;
    $prev = 0;
    $comparisons = 0;

    // Jump phase
    while ($arr[min($step, $n) - 1] < $target) {
        $comparisons++;
        $prev = $step;
        $step += $stepSize;
        if ($prev >= $n) break;
    }

    // Linear phase
    while ($prev < min($step, $n) && $arr[$prev] < $target) {
        $comparisons++;
        $prev++;
    }

    echo sprintf("%9d | %11d\n", $stepSize, $comparisons);
}

echo "\nOptimal: √n = " . (int)sqrt(10000) . " (minimizes total comparisons)\n\n";

// Test 9: Practical Application - Phone Book
echo "9. Practical Application: Phone Book Search\n";
echo "=============================================\n";

$phoneBook = [];
for ($i = 0; $i < 1000; $i++) {
    $phoneBook[] = sprintf("Person%04d", $i);
}

echo "Phone book with 1000 entries (sorted alphabetically)\n";
echo "Search for: 'Person0500'\n\n";

$target = 'Person0500';

// Interpolation doesn't work well with strings, use binary
$left = 0;
$right = count($phoneBook) - 1;
$comparisons = 0;

while ($left <= $right) {
    $comparisons++;
    $mid = $left + (int)(($right - $left) / 2);

    if ($phoneBook[$mid] === $target) {
        echo "Found 'Person0500' at index $mid\n";
        echo "Comparisons: $comparisons\n";
        break;
    }

    if ($phoneBook[$mid] < $target) {
        $left = $mid + 1;
    } else {
        $right = $mid - 1;
    }
}

echo "\nFor strings, binary search is most reliable\n\n";

// Test 10: When to Use Each Algorithm
echo "10. Algorithm Selection Guide\n";
echo "===============================\n\n";

echo "Binary Search:\n";
echo "  ✓ General-purpose, works on any sorted data\n";
echo "  ✓ O(log n) guaranteed\n";
echo "  ✓ Best for: Most use cases\n";
echo "  Example: Search in general sorted array\n\n";

echo "Interpolation Search:\n";
echo "  ✓ O(log log n) for uniformly distributed numeric data\n";
echo "  ✓ Best for: Large arrays with uniform distribution\n";
echo "  ✗ Worst case O(n) on skewed data\n";
echo "  Example: Search in sequential IDs (1,2,3...)\n\n";

echo "Jump Search:\n";
echo "  ✓ O(√n) time\n";
echo "  ✓ Better than linear, simpler than binary\n";
echo "  ✓ Good for systems where jumping backward is costly\n";
echo "  ✓ Best for: Sequential access data structures\n";
echo "  Example: Search in linked list, tape storage\n\n";

echo "Exponential Search:\n";
echo "  ✓ O(log n) time\n";
echo "  ✓ Best for: Unbounded/infinite arrays\n";
echo "  ✓ Good when target is near beginning\n";
echo "  Example: Search in infinite stream\n\n";

echo "Performance Summary:\n";
echo "  Algorithm      | Time        | Best For\n";
echo "  ---------------|-------------|---------------------------\n";
echo "  Linear         | O(n)        | Unsorted, small arrays\n";
echo "  Binary         | O(log n)    | General sorted data\n";
echo "  Interpolation  | O(log log n)| Uniform numeric data\n";
echo "  Jump           | O(√n)       | Sequential access\n";
echo "  Exponential    | O(log n)    | Unbounded arrays\n";

echo "\n✅ Complete! Interpolation, Jump, and Exponential search demonstrated.\n";
