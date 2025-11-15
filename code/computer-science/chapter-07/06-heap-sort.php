<?php

declare(strict_types=1);

/**
 * Heap Sort - O(n log n) In-Place Sorting Algorithm
 *
 * Demonstrates:
 * - Heap data structure (max heap)
 * - Heapify operation
 * - Building a heap from array
 * - Heap sort algorithm
 * - In-place sorting with guaranteed O(n log n)
 * - Not stable, but no extra space needed
 */

echo "=== Heap Sort ===\n\n";

echo "Concept: Use max heap data structure\n";
echo "         1. Build max heap from array\n";
echo "         2. Repeatedly extract maximum (root) to end\n";
echo "         3. Heapify remaining elements\n\n";

$heapifyCalls = 0;

/**
 * Heap sort implementation
 */
function heapSort(array &$arr): void
{
    $n = count($arr);

    // Build max heap
    for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
        heapify($arr, $n, $i);
    }

    // Extract elements from heap one by one
    for ($i = $n - 1; $i > 0; $i--) {
        // Move current root to end
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];

        // Heapify reduced heap
        heapify($arr, $i, 0);
    }
}

/**
 * Heapify a subtree rooted at index i
 */
function heapify(array &$arr, int $n, int $i): void
{
    global $heapifyCalls;
    $heapifyCalls++;

    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    // If left child is larger than root
    if ($left < $n && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    // If right child is larger than largest so far
    if ($right < $n && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

    // If largest is not root
    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];

        // Recursively heapify affected sub-tree
        heapify($arr, $n, $largest);
    }
}

/**
 * Visualize heap structure
 */
function visualizeHeap(array $arr, int $n): void
{
    echo "Heap as tree:\n";

    $height = (int)floor(log($n, 2)) + 1;

    for ($level = 0; $level < $height; $level++) {
        $startIdx = (1 << $level) - 1; // 2^level - 1
        $endIdx = min((1 << ($level + 1)) - 1, $n);

        $spaces = str_repeat('  ', $height - $level - 1);

        echo $spaces;

        for ($i = $startIdx; $i < $endIdx; $i++) {
            echo str_pad((string)$arr[$i], 4);
        }

        echo "\n";
    }

    echo "\n";
}

/**
 * Build heap visualization
 */
function heapSortVisualized(array &$arr): void
{
    $n = count($arr);

    echo "Initial array: [" . implode(', ', $arr) . "]\n\n";

    // Build max heap
    echo "Step 1: Build Max Heap\n";
    echo "=======================\n";

    for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
        echo "Heapify at index $i:\n";
        heapify($arr, $n, $i);
        visualizeHeap($arr, $n);
    }

    echo "Max heap built: [" . implode(', ', $arr) . "]\n\n";

    // Extract elements
    echo "Step 2: Extract Elements\n";
    echo "=========================\n";

    for ($i = $n - 1; $i > 0; $i--) {
        echo "Extract max ({$arr[0]}), swap with position $i:\n";

        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];

        echo "  Array: [";
        for ($j = 0; $j < $n; $j++) {
            if ($j >= $i) {
                echo "\033[32m" . $arr[$j] . "\033[0m "; // Green for sorted
            } else {
                echo $arr[$j] . " ";
            }
        }
        echo "]\n";

        heapify($arr, $i, 0);

        echo "  After heapify: [";
        for ($j = 0; $j < $n; $j++) {
            if ($j >= $i) {
                echo "\033[32m" . $arr[$j] . "\033[0m ";
            } else {
                echo $arr[$j] . " ";
            }
        }
        echo "]\n\n";
    }

    echo "Final sorted: [" . implode(', ', $arr) . "]\n";
}

// Test 1: Basic Heap Sort
echo "1. Basic Heap Sort\n";
echo "===================\n";

$numbers = [12, 11, 13, 5, 6, 7];
echo "Input: [" . implode(', ', $numbers) . "]\n\n";

$heapifyCalls = 0;
heapSort($numbers);

echo "Output: [" . implode(', ', $numbers) . "]\n";
echo "Heapify calls: $heapifyCalls\n\n";

// Test 2: Visualization
echo "2. Heap Sort Visualization\n";
echo "===========================\n";

$numbers2 = [4, 10, 3, 5, 1];
heapSortVisualized($numbers2);
echo "\n";

// Test 3: Max Heap Property
echo "3. Max Heap Property\n";
echo "=====================\n";

$arr = [4, 10, 3, 5, 1];
$n = count($arr);

echo "Array: [" . implode(', ', $arr) . "]\n\n";

// Build max heap
for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
    heapify($arr, $n, $i);
}

echo "Max heap: [" . implode(', ', $arr) . "]\n";
visualizeHeap($arr, $n);

echo "Max heap property: For any node i,\n";
echo "  arr[i] >= arr[2*i + 1] (left child)\n";
echo "  arr[i] >= arr[2*i + 2] (right child)\n\n";

// Verify
for ($i = 0; $i < (int)($n / 2); $i++) {
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    echo "Node $i ({$arr[$i]}): ";

    if ($left < $n) {
        $leftOk = $arr[$i] >= $arr[$left];
        echo "left={$arr[$left]} " . ($leftOk ? "✓" : "✗") . " ";
    }

    if ($right < $n) {
        $rightOk = $arr[$i] >= $arr[$right];
        echo "right={$arr[$right]} " . ($rightOk ? "✓" : "✗");
    }

    echo "\n";
}

echo "\n";

// Test 4: Performance Analysis
echo "4. Performance Analysis\n";
echo "========================\n";

$sizes = [100, 500, 1000, 5000];

echo "Array Size | Heapify Calls | Time (ms)\n";
echo "-----------|---------------|----------\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    shuffle($arr);

    $heapifyCalls = 0;

    $start = microtime(true);
    heapSort($arr);
    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "%10d | %13d | %8.3f\n",
        $size,
        $heapifyCalls,
        $time
    );
}

echo "\nObservation: Time grows as n log n (predictable)\n\n";

// Test 5: Best, Average, Worst Cases
echo "5. Consistent Performance (All Cases)\n";
echo "=======================================\n";

$testSize = 1000;

// Already sorted
$best = range(1, $testSize);
$start = microtime(true);
heapSort($best);
$bestTime = (microtime(true) - $start) * 1000;

// Reverse sorted
$worst = range($testSize, 1, -1);
$start = microtime(true);
heapSort($worst);
$worstTime = (microtime(true) - $start) * 1000;

// Random
$average = range(1, $testSize);
shuffle($average);
$start = microtime(true);
heapSort($average);
$averageTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Best case (sorted):    " . number_format($bestTime, 3) . " ms\n";
echo "Average case (random): " . number_format($averageTime, 3) . " ms\n";
echo "Worst case (reversed): " . number_format($worstTime, 3) . " ms\n\n";

echo "All cases have similar performance: O(n log n) guaranteed!\n";
echo "Unlike quick sort, no worst-case degradation.\n\n";

// Test 6: Heap Sort vs Quick Sort vs Merge Sort
echo "6. Heap Sort vs Other O(n log n) Sorts\n";
echo "=======================================\n";

$testSize = 5000;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$start = microtime(true);
heapSort($arr1);
$heapTime = (microtime(true) - $start) * 1000;

$arr2 = $testArr;
$start = microtime(true);
sort($arr2); // PHP's internal sort (usually quicksort/introsort)
$phpTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Heap Sort:    " . number_format($heapTime, 3) . " ms\n";
echo "PHP's sort(): " . number_format($phpTime, 3) . " ms\n\n";

echo "Heap sort is typically slower than quick sort in practice\n";
echo "due to poor cache locality (random memory access).\n\n";

// Test 7: Finding Top K Elements
echo "7. Finding Top K Elements (Heap Application)\n";
echo "==============================================\n";

$arr = [7, 10, 4, 3, 20, 15, 1, 9, 6];
$k = 3;

echo "Array: [" . implode(', ', $arr) . "]\n";
echo "Find top $k elements\n\n";

// Build max heap
$n = count($arr);
for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
    heapify($arr, $n, $i);
}

// Extract top k
$topK = [];
$heapSize = $n;

for ($i = 0; $i < $k; $i++) {
    $topK[] = $arr[0];

    [$arr[0], $arr[$heapSize - 1]] = [$arr[$heapSize - 1], $arr[0]];
    $heapSize--;

    heapify($arr, $heapSize, 0);
}

echo "Top $k elements: [" . implode(', ', $topK) . "]\n";
echo "Time complexity: O(n + k log n)\n\n";

// Test 8: Heap Sort Stability
echo "8. Stability Test (Heap Sort is Unstable)\n";
echo "===========================================\n";

class Record
{
    public function __construct(
        public int $key,
        public string $id
    ) {}

    public function __toString(): string
    {
        return "{$this->key}({$this->id})";
    }
}

$records = [
    new Record(3, 'A'),
    new Record(1, 'B'),
    new Record(3, 'C'),
    new Record(2, 'D'),
    new Record(3, 'E'),
];

echo "Input: ";
foreach ($records as $r) {
    echo "$r ";
}
echo "\n";

// Custom heapify for objects
function heapifyObjects(array &$arr, int $n, int $i): void
{
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    if ($left < $n && $arr[$left]->key > $arr[$largest]->key) {
        $largest = $left;
    }

    if ($right < $n && $arr[$right]->key > $arr[$largest]->key) {
        $largest = $right;
    }

    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapifyObjects($arr, $n, $largest);
    }
}

// Heap sort
$n = count($records);
for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
    heapifyObjects($records, $n, $i);
}

for ($i = $n - 1; $i > 0; $i--) {
    [$records[0], $records[$i]] = [$records[$i], $records[0]];
    heapifyObjects($records, $i, 0);
}

echo "Output: ";
foreach ($records as $r) {
    echo "$r ";
}
echo "\n";
echo "Notice: Records with key 3 may not maintain order A→C→E (unstable)\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(n log n)\n";
echo "  • Average case:  O(n log n)\n";
echo "  • Worst case:    O(n log n)\n";
echo "  → Guaranteed O(n log n) regardless of input!\n\n";

echo "Space Complexity:\n";
echo "  • O(1) - in-place sorting\n";
echo "  • No recursion stack needed (iterative heapify possible)\n\n";

echo "Properties:\n";
echo "  ✗ Stable:     No - heap operations break stability\n";
echo "  ✓ In-place:   Yes - no extra array needed\n";
echo "  ✗ Adaptive:   No - always O(n log n)\n";
echo "  ✗ Cache-friendly: No - poor locality of reference\n\n";

echo "Advantages:\n";
echo "  ✓ Guaranteed O(n log n) performance (no worst case degradation)\n";
echo "  ✓ In-place sorting (O(1) extra space)\n";
echo "  ✓ No recursion depth issues (can be made iterative)\n";
echo "  ✓ Useful for priority queue applications\n";
echo "  ✓ Good for finding top k elements\n\n";

echo "Disadvantages:\n";
echo "  ✗ Slower than quick sort in practice (poor cache locality)\n";
echo "  ✗ Unstable (changes order of equal elements)\n";
echo "  ✗ Not adaptive (doesn't benefit from partial sorting)\n";
echo "  ✗ Complex implementation compared to simpler sorts\n\n";

echo "When to Use:\n";
echo "  ✓ Guaranteed O(n log n) required (no worst case acceptable)\n";
echo "  ✓ Memory is limited (in-place sorting needed)\n";
echo "  ✓ Finding top/bottom k elements\n";
echo "  ✓ Priority queue implementation\n";
echo "  ✓ Embedded systems (predictable performance, no recursion)\n\n";

echo "Heap Data Structure:\n";
echo "  • Complete binary tree represented as array\n";
echo "  • Parent at index i, children at 2i+1 and 2i+2\n";
echo "  • Max heap: parent >= children\n";
echo "  • Min heap: parent <= children\n";
echo "  • Heapify: O(log n) to restore heap property\n";
echo "  • Build heap: O(n) from arbitrary array\n\n";

echo "Comparison:\n";
echo "  • vs Quick Sort: Slower but guaranteed O(n log n)\n";
echo "  • vs Merge Sort: In-place but unstable\n";
echo "  • vs Insertion Sort: Better for large data\n\n";

echo "Real-World Usage:\n";
echo "  • C++ std::make_heap, std::sort_heap\n";
echo "  • Priority queues (job scheduling)\n";
echo "  • Intro Sort (quick sort fallback when depth exceeds log n)\n";
echo "  • Finding median in streaming data\n";
echo "  • K-way merge in external sorting\n";

echo "\n✅ Complete! Heap sort demonstrated.\n";
