<?php

declare(strict_types=1);

/**
 * Selection Sort - O(n²) Sorting Algorithm
 *
 * Demonstrates:
 * - Selection sort implementation
 * - Finding minimum in unsorted portion
 * - Fewer swaps than bubble sort
 * - Unstable sorting behavior
 * - Performance characteristics
 */

echo "=== Selection Sort ===\n\n";

echo "Concept: Find minimum element from unsorted portion\n";
echo "         Swap it with the first unsorted element\n";
echo "         Repeat until array is sorted\n\n";

/**
 * Basic selection sort implementation
 */
function selectionSort(array $arr): array
{
    $n = count($arr);
    $comparisons = 0;
    $swaps = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;

        // Find minimum in remaining unsorted array
        for ($j = $i + 1; $j < $n; $j++) {
            $comparisons++;
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Swap with current position
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
            $swaps++;
        }
    }

    echo "  Comparisons: $comparisons\n";
    echo "  Swaps: $swaps\n";

    return $arr;
}

/**
 * Selection sort with visualization
 */
function selectionSortVisualized(array $arr): array
{
    $n = count($arr);

    echo "Initial: [" . implode(', ', $arr) . "]\n";
    echo str_repeat(' ', 9) . "↑ sorted | unsorted →\n\n";

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;
        $minValue = $arr[$i];

        echo "Step " . ($i + 1) . ":\n";

        // Find minimum
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $minValue) {
                $minIndex = $j;
                $minValue = $arr[$j];
            }
        }

        echo "  Find min in [" . implode(', ', array_slice($arr, $i)) . "]\n";
        echo "  Minimum: $minValue at index $minIndex\n";

        // Swap
        if ($minIndex !== $i) {
            echo "  Swap {$arr[$i]} ↔ {$arr[$minIndex]}\n";
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
        } else {
            echo "  Already in position\n";
        }

        // Show current state
        echo "  Result: [";
        for ($k = 0; $k < $n; $k++) {
            if ($k <= $i) {
                echo "\033[32m" . $arr[$k] . "\033[0m"; // Green for sorted
            } else {
                echo $arr[$k];
            }
            if ($k < $n - 1) echo ", ";
        }
        echo "]\n\n";
    }

    return $arr;
}

/**
 * Find kth smallest element using selection sort approach
 */
function findKthSmallest(array $arr, int $k): mixed
{
    $n = count($arr);

    for ($i = 0; $i < min($k, $n); $i++) {
        $minIndex = $i;

        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
    }

    return $k <= $n ? $arr[$k - 1] : null;
}

// Test 1: Basic Selection Sort
echo "1. Basic Selection Sort\n";
echo "========================\n";

$numbers = [64, 25, 12, 22, 11];
echo "Input: [" . implode(', ', $numbers) . "]\n";

$sorted = selectionSort($numbers);
echo "Output: [" . implode(', ', $sorted) . "]\n\n";

// Test 2: Visualization
echo "2. Step-by-Step Visualization\n";
echo "==============================\n";

$numbers2 = [29, 10, 14, 37, 13];
selectionSortVisualized($numbers2);

// Test 3: Swap Count Comparison
echo "3. Swap Count: Selection vs Bubble Sort\n";
echo "=========================================\n";

$testArr = [5, 2, 8, 1, 9, 3, 7];
echo "Test array: [" . implode(', ', $testArr) . "]\n\n";

$arr1 = $testArr;
$n = count($arr1);
$bubbleSwaps = 0;

for ($i = 0; $i < $n - 1; $i++) {
    for ($j = 0; $j < $n - $i - 1; $j++) {
        if ($arr1[$j] > $arr1[$j + 1]) {
            [$arr1[$j], $arr1[$j + 1]] = [$arr1[$j + 1], $arr1[$j]];
            $bubbleSwaps++;
        }
    }
}

$arr2 = $testArr;
$selectionSwaps = 0;

for ($i = 0; $i < $n - 1; $i++) {
    $minIndex = $i;
    for ($j = $i + 1; $j < $n; $j++) {
        if ($arr2[$j] < $arr2[$minIndex]) {
            $minIndex = $j;
        }
    }
    if ($minIndex !== $i) {
        [$arr2[$i], $arr2[$minIndex]] = [$arr2[$minIndex], $arr2[$i]];
        $selectionSwaps++;
    }
}

echo "Bubble Sort swaps:    $bubbleSwaps\n";
echo "Selection Sort swaps: $selectionSwaps\n";
echo "Selection Sort makes " . ($bubbleSwaps - $selectionSwaps) . " fewer swaps!\n\n";

// Test 4: Instability Demonstration
echo "4. Instability Test\n";
echo "====================\n";

class Card
{
    public function __construct(
        public int $value,
        public string $suit
    ) {}

    public function __toString(): string
    {
        return "{$this->value}{$this->suit}";
    }
}

$cards = [
    new Card(5, '♠'),
    new Card(2, '♥'),
    new Card(5, '♦'),
    new Card(3, '♣'),
    new Card(5, '♥'),
];

echo "Input: ";
foreach ($cards as $card) {
    echo "$card ";
}
echo "\n";

// Selection sort by value
for ($i = 0; $i < count($cards) - 1; $i++) {
    $minIndex = $i;
    for ($j = $i + 1; $j < count($cards); $j++) {
        if ($cards[$j]->value < $cards[$minIndex]->value) {
            $minIndex = $j;
        }
    }
    if ($minIndex !== $i) {
        [$cards[$i], $cards[$minIndex]] = [$cards[$minIndex], $cards[$i]];
    }
}

echo "Output: ";
foreach ($cards as $card) {
    echo "$card ";
}
echo "\n";
echo "Notice: Cards with value 5 may not maintain original order (unstable)\n\n";

// Test 5: Finding Kth Smallest
echo "5. Finding Kth Smallest Element\n";
echo "=================================\n";

$numbers5 = [7, 10, 4, 3, 20, 15];
echo "Array: [" . implode(', ', $numbers5) . "]\n\n";

for ($k = 1; $k <= 3; $k++) {
    $result = findKthSmallest($numbers5, $k);
    echo "$k-smallest: $result\n";
}
echo "\nNote: More efficient with QuickSelect, but selection sort works!\n\n";

// Test 6: Performance Analysis
echo "6. Performance Analysis\n";
echo "========================\n";

$sizes = [50, 100, 200, 500];

echo "Array Size | Comparisons | Swaps | Time (ms)\n";
echo "-----------|-------------|-------|----------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    shuffle($arr);

    $start = microtime(true);

    $n = count($arr);
    $comparisons = 0;
    $swaps = 0;

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            $comparisons++;
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
            $swaps++;
        }
    }

    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%10d | %11d | %5d | %8.3f\n",
        $size,
        $comparisons,
        $swaps,
        $time
    );
}

echo "\nObservation: Comparisons always n(n-1)/2, regardless of input!\n\n";

// Test 7: Best, Worst, Average Cases
echo "7. Input-Independent Performance\n";
echo "==================================\n";

$testSize = 100;

// Best case: already sorted
$best = range(1, $testSize);
$start = microtime(true);
$temp = selectionSort($best);
$bestTime = (microtime(true) - $start) * 1000;

// Worst case: reverse sorted
$worst = range($testSize, 1, -1);
$start = microtime(true);
$temp = selectionSort($worst);
$worstTime = (microtime(true) - $start) * 1000;

// Average case: random
$average = range(1, $testSize);
shuffle($average);
$start = microtime(true);
$temp = selectionSort($average);
$averageTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Best case (sorted):    " . number_format($bestTime, 3) . " ms\n";
echo "Average case (random): " . number_format($averageTime, 3) . " ms\n";
echo "Worst case (reversed): " . number_format($worstTime, 3) . " ms\n\n";

echo "All times are similar! Selection sort is INPUT-INDEPENDENT.\n";
echo "It always makes the same number of comparisons: n(n-1)/2\n\n";

// Test 8: Comparison with Bubble Sort
echo "8. Selection Sort vs Bubble Sort\n";
echo "==================================\n";

$testSize = 500;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$start = microtime(true);
selectionSort($arr1);
$selectionTime = (microtime(true) - $start) * 1000;

$arr2 = $testArr;
$start = microtime(true);
// Bubble sort
$n = count($arr2);
for ($i = 0; $i < $n - 1; $i++) {
    for ($j = 0; $j < $n - $i - 1; $j++) {
        if ($arr2[$j] > $arr2[$j + 1]) {
            [$arr2[$j], $arr2[$j + 1]] = [$arr2[$j + 1], $arr2[$j]];
        }
    }
}
$bubbleTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Selection Sort: " . number_format($selectionTime, 3) . " ms\n";
echo "Bubble Sort:    " . number_format($bubbleTime, 3) . " ms\n";
echo "Difference:     " . number_format(abs($selectionTime - $bubbleTime), 3) . " ms\n\n";

echo "Both are O(n²), but Selection Sort typically makes fewer swaps.\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(n²) - no optimization possible\n";
echo "  • Average case:  O(n²)\n";
echo "  • Worst case:    O(n²)\n";
echo "  → Input-independent! Always makes n(n-1)/2 comparisons\n\n";

echo "Space Complexity:\n";
echo "  • O(1) - in-place sorting\n\n";

echo "Properties:\n";
echo "  ✗ Stable:     No - long-distance swaps break stability\n";
echo "  ✓ In-place:   Yes - no extra array needed\n";
echo "  ✗ Adaptive:   No - always O(n²) even if sorted\n";
echo "  ✗ Online:     No - needs full array\n\n";

echo "Advantages:\n";
echo "  ✓ Simple to understand and implement\n";
echo "  ✓ Performs well on small lists\n";
echo "  ✓ Minimizes number of swaps: O(n) swaps max\n";
echo "  ✓ Good when swap operation is expensive\n\n";

echo "Disadvantages:\n";
echo "  ✗ O(n²) time complexity\n";
echo "  ✗ Not adaptive (doesn't benefit from partial sorting)\n";
echo "  ✗ Unstable (can change order of equal elements)\n\n";

echo "When to Use:\n";
echo "  ✓ Small datasets (< 20 elements)\n";
echo "  ✓ Swap operation is very expensive\n";
echo "  ✓ Memory writes are costly (embedded systems)\n";
echo "  ✓ Finding top k elements without full sort\n";
echo "  ✗ Large datasets (use O(n log n) sorts)\n";
echo "  ✗ Stability is required (use insertion/merge sort)\n\n";

echo "Comparison with Bubble Sort:\n";
echo "  • Both are O(n²)\n";
echo "  • Selection Sort: Fewer swaps, more comparisons\n";
echo "  • Bubble Sort: More swaps, adaptive with optimization\n";
echo "  • Selection Sort: Unstable\n";
echo "  • Bubble Sort: Stable\n";

echo "\n✅ Complete! Selection sort demonstrated.\n";
