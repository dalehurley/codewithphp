<?php

declare(strict_types=1);

/**
 * Bubble Sort - O(n²) Sorting Algorithm
 *
 * Demonstrates:
 * - Basic bubble sort implementation
 * - Optimization with early termination
 * - Visualization of bubble-up process
 * - Swap counting and comparisons
 * - Stable sorting property
 */

echo "=== Bubble Sort ===\n\n";

echo "Concept: Repeatedly swap adjacent elements if they're in wrong order\n";
echo "         Largest elements 'bubble up' to the end in each pass\n\n";

/**
 * Basic bubble sort implementation
 */
function bubbleSort(array $arr): array
{
    $n = count($arr);
    $comparisons = 0;
    $swaps = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            $comparisons++;

            if ($arr[$j] > $arr[$j + 1]) {
                // Swap adjacent elements
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swaps++;
            }
        }
    }

    echo "  Comparisons: $comparisons\n";
    echo "  Swaps: $swaps\n";

    return $arr;
}

/**
 * Optimized bubble sort with early termination
 */
function bubbleSortOptimized(array $arr): array
{
    $n = count($arr);
    $comparisons = 0;
    $swaps = 0;
    $passes = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;
        $passes++;

        for ($j = 0; $j < $n - $i - 1; $j++) {
            $comparisons++;

            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swaps++;
                $swapped = true;
            }
        }

        // Early termination: if no swaps, array is sorted
        if (!$swapped) {
            echo "  Passes: $passes (early termination)\n";
            echo "  Comparisons: $comparisons\n";
            echo "  Swaps: $swaps\n";
            return $arr;
        }
    }

    echo "  Passes: $passes\n";
    echo "  Comparisons: $comparisons\n";
    echo "  Swaps: $swaps\n";

    return $arr;
}

/**
 * Bubble sort with step-by-step visualization
 */
function bubbleSortVisualized(array $arr): array
{
    $n = count($arr);

    echo "Initial: [" . implode(', ', $arr) . "]\n\n";

    for ($i = 0; $i < $n - 1; $i++) {
        echo "Pass " . ($i + 1) . ":\n";
        $swapped = false;

        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                echo "  Swap {$arr[$j]} and {$arr[$j + 1]}: ";
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                echo "[" . implode(', ', $arr) . "]\n";
                $swapped = true;
            }
        }

        if (!$swapped) {
            echo "  No swaps - array is sorted!\n";
            break;
        }

        echo "  End of pass: [" . implode(', ', $arr) . "]\n\n";
    }

    return $arr;
}

// Test 1: Basic Bubble Sort
echo "1. Basic Bubble Sort\n";
echo "=====================\n";

$numbers = [64, 34, 25, 12, 22, 11, 90];
echo "Input: [" . implode(', ', $numbers) . "]\n";

$sorted = bubbleSort($numbers);
echo "Output: [" . implode(', ', $sorted) . "]\n\n";

// Test 2: Optimized Bubble Sort
echo "2. Optimized Bubble Sort (Early Termination)\n";
echo "=============================================\n";

$numbers2 = [5, 1, 4, 2, 8];
echo "Input: [" . implode(', ', $numbers2) . "]\n";

$sorted2 = bubbleSortOptimized($numbers2);
echo "Output: [" . implode(', ', $sorted2) . "]\n\n";

// Test 3: Already Sorted (Shows Optimization Benefit)
echo "3. Already Sorted Array (Optimization Benefit)\n";
echo "================================================\n";

$numbers3 = [1, 2, 3, 4, 5];
echo "Input: [" . implode(', ', $numbers3) . "] (already sorted)\n";

$sorted3 = bubbleSortOptimized($numbers3);
echo "Output: [" . implode(', ', $sorted3) . "]\n";
echo "Note: Only 1 pass needed with optimization!\n\n";

// Test 4: Visualization
echo "4. Step-by-Step Visualization\n";
echo "==============================\n";

$numbers4 = [5, 2, 8, 1, 9];
$sorted4 = bubbleSortVisualized($numbers4);
echo "Final: [" . implode(', ', $sorted4) . "]\n\n";

// Test 5: Stability Test
echo "5. Stability Test (Equal Elements)\n";
echo "====================================\n";

// Using objects to track original order
class Item
{
    public function __construct(
        public int $value,
        public string $id
    ) {}

    public function __toString(): string
    {
        return "{$this->value}({$this->id})";
    }
}

$items = [
    new Item(3, 'A'),
    new Item(1, 'B'),
    new Item(3, 'C'),
    new Item(2, 'D'),
    new Item(3, 'E'),
];

echo "Input: ";
foreach ($items as $item) {
    echo "$item ";
}
echo "\n";

// Sort by value
for ($i = 0; $i < count($items) - 1; $i++) {
    for ($j = 0; $j < count($items) - $i - 1; $j++) {
        if ($items[$j]->value > $items[$j + 1]->value) {
            [$items[$j], $items[$j + 1]] = [$items[$j + 1], $items[$j]];
        }
    }
}

echo "Output: ";
foreach ($items as $item) {
    echo "$item ";
}
echo "\n";
echo "Notice: Items with value 3 maintain order A→C→E (stable!)\n\n";

// Test 6: Performance on Different Sizes
echo "6. Performance Analysis\n";
echo "========================\n";

$sizes = [10, 50, 100, 500];

echo "Array Size | Comparisons | Swaps    | Time (ms)\n";
echo "-----------|-------------|----------|----------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    shuffle($arr);

    $start = microtime(true);

    $n = count($arr);
    $comparisons = 0;
    $swaps = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;
        for ($j = 0; $j < $n - $i - 1; $j++) {
            $comparisons++;
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swaps++;
                $swapped = true;
            }
        }
        if (!$swapped) break;
    }

    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%10d | %11d | %8d | %8.3f\n",
        $size,
        $comparisons,
        $swaps,
        $time
    );
}

echo "\nObservation: Time grows quadratically (O(n²))\n\n";

// Test 7: Best, Average, Worst Cases
echo "7. Best, Average, and Worst Cases\n";
echo "===================================\n";

$testSize = 100;

// Best case: already sorted
$best = range(1, $testSize);
$start = microtime(true);
$temp = bubbleSortOptimized($best);
$bestTime = (microtime(true) - $start) * 1000;

// Worst case: reverse sorted
$worst = range($testSize, 1, -1);
$start = microtime(true);
$temp = bubbleSortOptimized($worst);
$worstTime = (microtime(true) - $start) * 1000;

// Average case: random
$average = range(1, $testSize);
shuffle($average);
$start = microtime(true);
$temp = bubbleSortOptimized($average);
$averageTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Best case (sorted):     " . number_format($bestTime, 3) . " ms\n";
echo "Average case (random):  " . number_format($averageTime, 3) . " ms\n";
echo "Worst case (reversed):  " . number_format($worstTime, 3) . " ms\n";
echo "Worst/Best ratio:       " . number_format($worstTime / $bestTime, 1) . "x\n\n";

// Test 8: Comparing with PHP's built-in sort
echo "8. Comparison with PHP's Built-in Sort\n";
echo "========================================\n";

$testSize = 1000;
$arr1 = range(1, $testSize);
shuffle($arr1);
$arr2 = $arr1; // Copy for fair comparison

// Bubble sort
$start = microtime(true);
bubbleSortOptimized($arr1);
$bubbleTime = (microtime(true) - $start) * 1000;

// PHP's built-in sort (Tim Sort - O(n log n))
$start = microtime(true);
sort($arr2);
$phpTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Bubble Sort:       " . number_format($bubbleTime, 3) . " ms\n";
echo "PHP's sort():      " . number_format($phpTime, 3) . " ms\n";
echo "Bubble is ~" . number_format($bubbleTime / $phpTime, 1) . "x slower\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(n)   - already sorted with optimization\n";
echo "  • Average case:  O(n²)  - random data\n";
echo "  • Worst case:    O(n²)  - reverse sorted\n\n";

echo "Space Complexity:\n";
echo "  • O(1) - in-place sorting (only uses swap variables)\n\n";

echo "Properties:\n";
echo "  ✓ Stable:     Yes - equal elements maintain order\n";
echo "  ✓ In-place:   Yes - no extra array needed\n";
echo "  ✓ Adaptive:   Yes - O(n) when already sorted\n";
echo "  ✓ Online:     No - needs full array\n\n";

echo "When to Use:\n";
echo "  ✓ Educational purposes (easy to understand)\n";
echo "  ✓ Very small datasets (< 10 elements)\n";
echo "  ✓ Nearly sorted data with optimization\n";
echo "  ✗ Production code (use merge/quick/tim sort)\n";
echo "  ✗ Large datasets (O(n²) is prohibitive)\n\n";

echo "Optimization Techniques:\n";
echo "  1. Early termination (no swaps → sorted)\n";
echo "  2. Reduce comparisons on each pass (bubbled elements at end)\n";
echo "  3. Track last swap position (skip sorted tail)\n";

echo "\n✅ Complete! Bubble sort demonstrated.\n";
