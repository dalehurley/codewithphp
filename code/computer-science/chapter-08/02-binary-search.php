<?php

declare(strict_types=1);

/**
 * Binary Search - O(log n) Divide-and-Conquer Search
 *
 * Demonstrates:
 * - Iterative binary search implementation
 * - Recursive binary search implementation
 * - Binary search on sorted arrays
 * - Comparison with linear search
 * - Edge cases and boundary conditions
 * - Performance analysis
 */

echo "=== Binary Search ===\n\n";

echo "Concept: Repeatedly divide search space in half\n";
echo "         REQUIRES sorted data\n";
echo "         O(log n) time complexity\n\n";

/**
 * Iterative binary search
 */
function binarySearchIterative(array $arr, mixed $target): ?int
{
    $left = 0;
    $right = count($arr) - 1;

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
 * Recursive binary search
 */
function binarySearchRecursive(array $arr, mixed $target, int $left, int $right): ?int
{
    if ($left > $right) {
        return null;
    }

    $mid = $left + (int)(($right - $left) / 2);

    if ($arr[$mid] === $target) {
        return $mid;
    }

    if ($arr[$mid] < $target) {
        return binarySearchRecursive($arr, $target, $mid + 1, $right);
    } else {
        return binarySearchRecursive($arr, $target, $left, $mid - 1);
    }
}

/**
 * Binary search with comparison count
 */
function binarySearchWithStats(array $arr, mixed $target): array
{
    $comparisons = 0;
    $left = 0;
    $right = count($arr) - 1;
    $found = null;

    while ($left <= $right) {
        $comparisons++;
        $mid = $left + (int)(($right - $left) / 2);

        if ($arr[$mid] === $target) {
            $found = $mid;
            break;
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return [
        'index' => $found,
        'comparisons' => $comparisons,
    ];
}

/**
 * Linear search for comparison
 */
function linearSearch(array $arr, mixed $target): array
{
    $comparisons = 0;

    foreach ($arr as $index => $value) {
        $comparisons++;
        if ($value === $target) {
            return ['index' => $index, 'comparisons' => $comparisons];
        }
    }

    return ['index' => null, 'comparisons' => $comparisons];
}

// Test 1: Basic Binary Search
echo "1. Basic Binary Search\n";
echo "========================\n";

$numbers = [11, 22, 25, 34, 64, 90];
echo "Sorted array: [" . implode(', ', $numbers) . "]\n\n";

$searches = [64, 11, 90, 25, 99];

foreach ($searches as $target) {
    $result = binarySearchIterative($numbers, $target);

    if ($result !== null) {
        echo "Search for $target: Found at index $result\n";
    } else {
        echo "Search for $target: Not found\n";
    }
}
echo "\n";

// Test 2: Iterative vs Recursive
echo "2. Iterative vs Recursive Binary Search\n";
echo "=========================================\n";

$arr = range(1, 100);
$target = 67;

echo "Array: [1, 2, 3, ..., 100]\n";
echo "Search for: $target\n\n";

$resultIterative = binarySearchIterative($arr, $target);
$resultRecursive = binarySearchRecursive($arr, $target, 0, count($arr) - 1);

echo "Iterative: Found at index $resultIterative\n";
echo "Recursive: Found at index $resultRecursive\n";
echo "Both produce same result!\n\n";

// Test 3: Comparison Count Analysis
echo "3. Binary vs Linear Search Comparisons\n";
echo "========================================\n";

$sizes = [100, 1000, 10000, 100000];

echo "Array Size | Binary Comparisons | Linear Comparisons | Speedup\n";
echo "-----------|--------------------|--------------------|--------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    $target = $size; // Worst case for both

    $binaryResult = binarySearchWithStats($arr, $target);
    $linearResult = linearSearch($arr, $target);

    $speedup = $linearResult['comparisons'] / $binaryResult['comparisons'];

    echo sprintf(
        "%10d | %18d | %18d | %6.1fx\n",
        $size,
        $binaryResult['comparisons'],
        $linearResult['comparisons'],
        $speedup
    );
}

echo "\nObservation: Speedup increases with array size!\n\n";

// Test 4: Best, Average, Worst Cases
echo "4. Best, Average, Worst Cases\n";
echo "===============================\n";

$arr = range(1, 100);
$mid = (int)(count($arr) / 2);

// Best case: middle element
$result = binarySearchWithStats($arr, $arr[$mid]);
echo "Best case (middle element):  {$result['comparisons']} comparisons\n";

// Worst case: not found or at boundaries
$result = binarySearchWithStats($arr, 100);
echo "Worst case (last element):   {$result['comparisons']} comparisons\n";

$result = binarySearchWithStats($arr, 999);
echo "Not found:                   {$result['comparisons']} comparisons\n";

// Average case simulation
$totalComparisons = 0;
$trials = 100;

for ($i = 0; $i < $trials; $i++) {
    $target = random_int(1, 100);
    $result = binarySearchWithStats($arr, $target);
    $totalComparisons += $result['comparisons'];
}

echo "Average case:                " . round($totalComparisons / $trials) . " comparisons\n\n";

echo "Note: All cases are O(log n) - very efficient!\n\n";

// Test 5: Step-by-Step Visualization
echo "5. Step-by-Step Binary Search Visualization\n";
echo "=============================================\n";

$arr = [10, 20, 30, 40, 50, 60, 70, 80, 90];
$target = 70;

echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Target: $target\n\n";

$left = 0;
$right = count($arr) - 1;
$step = 1;

while ($left <= $right) {
    $mid = $left + (int)(($right - $left) / 2);

    echo "Step $step:\n";
    echo "  Range: [$left..$right] (indices)\n";
    echo "  Mid: $mid (value: {$arr[$mid]})\n";

    if ($arr[$mid] === $target) {
        echo "  Result: FOUND at index $mid!\n";
        break;
    }

    if ($arr[$mid] < $target) {
        echo "  Action: {$arr[$mid]} < $target, search right half\n";
        $left = $mid + 1;
    } else {
        echo "  Action: {$arr[$mid]} > $target, search left half\n";
        $right = $mid - 1;
    }

    echo "\n";
    $step++;
}

echo "\n";

// Test 6: Edge Cases
echo "6. Edge Cases and Boundary Conditions\n";
echo "=======================================\n";

// Empty array
$empty = [];
$result = binarySearchIterative($empty, 5);
echo "Empty array: " . ($result === null ? "Not found (correct)" : "ERROR") . "\n";

// Single element - found
$single = [42];
$result = binarySearchIterative($single, 42);
echo "Single element (found): " . ($result === 0 ? "Found at index 0 (correct)" : "ERROR") . "\n";

// Single element - not found
$result = binarySearchIterative($single, 99);
echo "Single element (not found): " . ($result === null ? "Not found (correct)" : "ERROR") . "\n";

// Two elements
$two = [10, 20];
$result = binarySearchIterative($two, 10);
echo "Two elements (first): Found at index $result\n";

$result = binarySearchIterative($two, 20);
echo "Two elements (second): Found at index $result\n";

// First element
$arr = range(1, 100);
$result = binarySearchIterative($arr, 1);
echo "First element: Found at index $result\n";

// Last element
$result = binarySearchIterative($arr, 100);
echo "Last element: Found at index $result\n";

echo "\nBinary search handles all edge cases correctly!\n\n";

// Test 7: Performance Comparison
echo "7. Performance Comparison: Binary vs Linear\n";
echo "============================================\n";

$testSizes = [1000, 10000, 100000];

echo "Size    | Binary Time | Linear Time | Speedup\n";
echo "--------|-------------|-------------|--------\n";

foreach ($testSizes as $size) {
    $arr = range(1, $size);
    $target = $size; // Worst case

    // Binary search
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        binarySearchIterative($arr, $target);
    }
    $binaryTime = (microtime(true) - $start) * 1000;

    // Linear search
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        linearSearch($arr, $target);
    }
    $linearTime = (microtime(true) - $start) * 1000;

    $speedup = $linearTime / $binaryTime;

    echo sprintf(
        "%7d | %9.3f ms | %9.3f ms | %6.1fx\n",
        $size,
        $binaryTime,
        $linearTime,
        $speedup
    );
}

echo "\nBinary search dominates for large sorted datasets!\n\n";

// Test 8: Binary Search on Different Data Types
echo "8. Binary Search on Different Data Types\n";
echo "==========================================\n";

// Strings (alphabetically sorted)
$words = ['apple', 'banana', 'cherry', 'date', 'elderberry', 'fig', 'grape'];
echo "Sorted words: [" . implode(', ', $words) . "]\n";

$target = 'date';
$result = binarySearchIterative($words, $target);
echo "Search for '$target': Found at index $result\n\n";

// Floats
$floats = [1.1, 2.5, 3.7, 4.9, 5.2, 6.8, 7.3];
echo "Sorted floats: [" . implode(', ', $floats) . "]\n";

$target = 4.9;
$result = binarySearchIterative($floats, $target);
echo "Search for $target: Found at index $result\n\n";

// Test 9: Why Binary Search is Logarithmic
echo "9. Why Binary Search is O(log n)\n";
echo "==================================\n";

echo "Binary search halves the search space each iteration:\n\n";

$n = 1000000;
$steps = 0;

echo "Array size: $n\n";
echo "Steps to find element:\n";

while ($n > 1) {
    $steps++;
    echo "  Step $steps: Search space = $n\n";
    $n = (int)($n / 2);

    if ($steps >= 20) {
        echo "  ... (found after ~20 steps max)\n";
        break;
    }
}

echo "\nlog₂(1,000,000) ≈ " . round(log(1000000, 2)) . " steps\n";
echo "With 1 million elements, binary search needs ~20 comparisons!\n\n";

// Test 10: Practical Applications
echo "10. Practical Applications\n";
echo "===========================\n";

echo "Binary search is used in:\n\n";

echo "1. Database Indexing\n";
echo "   • B-tree indexes use binary search principle\n";
echo "   • Log-time lookups in sorted indexes\n\n";

echo "2. Dictionary/Phone Book Lookup\n";
$dictionary = ['aardvark', 'bear', 'cat', 'dog', 'elephant', 'fox', 'giraffe'];
$word = 'elephant';
$result = binarySearchIterative($dictionary, $word);
echo "   • Find '$word': Index $result\n";
echo "   • Much faster than reading sequentially\n\n";

echo "3. Finding Version Introduction\n";
$versions = ['v1.0', 'v1.1', 'v1.2', 'v2.0', 'v2.1', 'v3.0'];
echo "   • Find when bug was introduced\n";
echo "   • Binary search through version history\n\n";

echo "4. Searching Rotated Arrays (with modification)\n";
echo "   • Used in scheduling algorithms\n";
echo "   • Finding peaks and valleys\n\n";

echo "5. Auto-complete Systems\n";
echo "   • Quick prefix matching in sorted lists\n";
echo "   • Type 'el' → suggest 'elephant'\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "Time Complexity:\n";
echo "  • Best case:     O(1)   - target at middle\n";
echo "  • Average case:  O(log n)\n";
echo "  • Worst case:    O(log n) - target at boundary or not found\n\n";

echo "Space Complexity:\n";
echo "  • Iterative: O(1) - only uses loop variables\n";
echo "  • Recursive: O(log n) - recursion stack depth\n\n";

echo "Requirements:\n";
echo "  ✓ Array MUST be sorted\n";
echo "  ✓ Random access required (arrays, not linked lists)\n";
echo "  ✓ Elements must be comparable\n\n";

echo "Advantages:\n";
echo "  • Extremely fast for large datasets (log n growth)\n";
echo "  • Predictable performance\n";
echo "  • Simple to implement\n";
echo "  • Works on any sorted data\n\n";

echo "Disadvantages:\n";
echo "  • Requires sorted data (O(n log n) sorting cost)\n";
echo "  • Requires random access (no linked lists)\n";
echo "  • Slower than hash tables for single lookups (O(1) vs O(log n))\n";
echo "  • Not adaptive (doesn't benefit from data patterns)\n\n";

echo "When to Use Binary Search:\n";
echo "  ✓ Data is already sorted or will be searched multiple times\n";
echo "  ✓ Large datasets (1000+ elements)\n";
echo "  ✓ Memory-efficient search needed (vs hash tables)\n";
echo "  ✓ Need to find boundaries (first/last occurrence)\n\n";

echo "When NOT to Use Binary Search:\n";
echo "  ✗ Unsorted data (sorting cost > search benefit)\n";
echo "  ✗ Single search operation on unsorted data\n";
echo "  ✗ Linked lists (no random access)\n";
echo "  ✗ Very small datasets (< 10 elements, linear search is fine)\n\n";

echo "Real-World Performance:\n";
echo "  • 1,000 elements:     ~10 comparisons (vs 500 average for linear)\n";
echo "  • 1,000,000 elements: ~20 comparisons (vs 500,000 average for linear)\n";
echo "  • 1,000,000,000:      ~30 comparisons (vs 500,000,000 average for linear)\n\n";

echo "Comparison with Other Searches:\n";
echo "  • Linear search:       O(n)     - any data, slow\n";
echo "  • Binary search:       O(log n) - sorted data, fast\n";
echo "  • Hash table:          O(1)     - extra memory, fastest\n";
echo "  • Interpolation search: O(log log n) - uniform distribution\n\n";

echo "✅ Complete! Binary search demonstrated.\n";
