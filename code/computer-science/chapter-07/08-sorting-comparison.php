<?php

declare(strict_types=1);

/**
 * Sorting Algorithms Comprehensive Comparison
 *
 * Demonstrates:
 * - Performance benchmarks across all sorting algorithms
 * - Best/average/worst case scenarios
 * - Small vs large dataset performance
 * - Different input types (sorted, random, reversed)
 * - Memory usage comparison
 * - Stability and in-place comparison
 */

echo "=== Sorting Algorithms: Comprehensive Comparison ===\n\n";

// Include all sorting implementations
function bubbleSort(array $arr): array {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swapped = true;
            }
        }
        if (!$swapped) break;
    }
    return $arr;
}

function selectionSort(array $arr): array {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
        }
    }
    return $arr;
}

function insertionSort(array $arr): array {
    $n = count($arr);
    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $key;
    }
    return $arr;
}

function mergeSort(array $arr): array {
    if (count($arr) <= 1) return $arr;
    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));
    return merge($left, $right);
}

function merge($left, $right) {
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

function quickSort(array &$arr, int $low, int $high): void {
    if ($low < $high) {
        $pivotIndex = partition($arr, $low, $high);
        quickSort($arr, $low, $pivotIndex - 1);
        quickSort($arr, $pivotIndex + 1, $high);
    }
}

function partition(array &$arr, int $low, int $high): int {
    $pivot = $arr[$high];
    $i = $low - 1;
    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] <= $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }
    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    return $i + 1;
}

function heapSort(array &$arr): void {
    $n = count($arr);
    for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
        heapify($arr, $n, $i);
    }
    for ($i = $n - 1; $i > 0; $i--) {
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];
        heapify($arr, $i, 0);
    }
}

function heapify(array &$arr, int $n, int $i): void {
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;
    if ($left < $n && $arr[$left] > $arr[$largest]) $largest = $left;
    if ($right < $n && $arr[$right] > $arr[$largest]) $largest = $right;
    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapify($arr, $n, $largest);
    }
}

// Test 1: Algorithm Summary Table
echo "1. Algorithm Summary\n";
echo "=====================\n\n";

echo "Algorithm      | Best      | Average   | Worst     | Space | Stable | In-Place\n";
echo "---------------|-----------|-----------|-----------|-------|--------|----------\n";
echo "Bubble Sort    | O(n)      | O(n²)     | O(n²)     | O(1)  | Yes    | Yes\n";
echo "Selection Sort | O(n²)     | O(n²)     | O(n²)     | O(1)  | No     | Yes\n";
echo "Insertion Sort | O(n)      | O(n²)     | O(n²)     | O(1)  | Yes    | Yes\n";
echo "Merge Sort     | O(n log n)| O(n log n)| O(n log n)| O(n)  | Yes    | No\n";
echo "Quick Sort     | O(n log n)| O(n log n)| O(n²)     | O(log n)| No  | Yes\n";
echo "Heap Sort      | O(n log n)| O(n log n)| O(n log n)| O(1)  | No     | Yes\n";
echo "Counting Sort  | O(n+k)    | O(n+k)    | O(n+k)    | O(k)  | Yes    | No\n";
echo "Radix Sort     | O(nk)     | O(nk)     | O(nk)     | O(n+k)| Yes    | No\n";

echo "\n";

// Test 2: Small Dataset Performance
echo "2. Small Dataset Performance (100 elements)\n";
echo "============================================\n";

$size = 100;
$testData = range(1, $size);
shuffle($testData);

$results = [];

$algorithms = [
    'Bubble Sort' => function($arr) { return bubbleSort($arr); },
    'Selection Sort' => function($arr) { return selectionSort($arr); },
    'Insertion Sort' => function($arr) { return insertionSort($arr); },
    'Merge Sort' => function($arr) { return mergeSort($arr); },
    'Quick Sort' => function($arr) { quickSort($arr, 0, count($arr) - 1); return $arr; },
    'Heap Sort' => function($arr) { heapSort($arr); return $arr; },
    'PHP sort()' => function($arr) { sort($arr); return $arr; },
];

foreach ($algorithms as $name => $func) {
    $arr = $testData;
    $start = microtime(true);
    $sorted = $func($arr);
    $time = (microtime(true) - $start) * 1000;
    $results[$name] = $time;
}

asort($results);

echo "Algorithm        | Time (ms)\n";
echo "-----------------|----------\n";

foreach ($results as $name => $time) {
    echo sprintf("%-16s | %8.3f\n", $name, $time);
}

echo "\nFor small datasets, simpler algorithms can be competitive!\n\n";

// Test 3: Large Dataset Performance
echo "3. Large Dataset Performance (10,000 elements)\n";
echo "===============================================\n";

$size = 10000;
$testData = range(1, $size);
shuffle($testData);

$results = [];

$algorithmsLarge = [
    'Insertion Sort' => function($arr) { return insertionSort($arr); },
    'Merge Sort' => function($arr) { return mergeSort($arr); },
    'Quick Sort' => function($arr) { quickSort($arr, 0, count($arr) - 1); return $arr; },
    'Heap Sort' => function($arr) { heapSort($arr); return $arr; },
    'PHP sort()' => function($arr) { sort($arr); return $arr; },
];

foreach ($algorithmsLarge as $name => $func) {
    $arr = $testData;
    $start = microtime(true);
    $sorted = $func($arr);
    $time = (microtime(true) - $start) * 1000;
    $results[$name] = $time;
}

asort($results);

echo "Algorithm        | Time (ms)\n";
echo "-----------------|----------\n";

foreach ($results as $name => $time) {
    echo sprintf("%-16s | %8.3f\n", $name, $time);
}

echo "\nO(n log n) algorithms dominate for large datasets!\n\n";

// Test 4: Different Input Types
echo "4. Performance on Different Input Types (1,000 elements)\n";
echo "==========================================================\n";

$size = 1000;

$inputs = [
    'Random' => function() use ($size) {
        $arr = range(1, $size);
        shuffle($arr);
        return $arr;
    },
    'Sorted' => function() use ($size) {
        return range(1, $size);
    },
    'Reversed' => function() use ($size) {
        return range($size, 1, -1);
    },
    'Nearly Sorted' => function() use ($size) {
        $arr = range(1, $size);
        for ($i = 0; $i < 10; $i++) {
            $idx1 = random_int(0, $size - 1);
            $idx2 = random_int(0, $size - 1);
            [$arr[$idx1], $arr[$idx2]] = [$arr[$idx2], $arr[$idx1]];
        }
        return $arr;
    },
];

$testAlgorithms = [
    'Insertion Sort',
    'Quick Sort',
    'Merge Sort',
];

echo str_pad('Input Type', 15) . " | ";
foreach ($testAlgorithms as $algo) {
    echo str_pad($algo, 15) . " | ";
}
echo "\n";

echo str_repeat('-', 15) . " | ";
foreach ($testAlgorithms as $algo) {
    echo str_repeat('-', 15) . " | ";
}
echo "\n";

foreach ($inputs as $inputName => $inputGen) {
    echo str_pad($inputName, 15) . " | ";

    foreach ($testAlgorithms as $algoName) {
        $arr = $inputGen();

        $start = microtime(true);

        if ($algoName === 'Insertion Sort') {
            insertionSort($arr);
        } elseif ($algoName === 'Quick Sort') {
            quickSort($arr, 0, count($arr) - 1);
        } elseif ($algoName === 'Merge Sort') {
            mergeSort($arr);
        }

        $time = (microtime(true) - $start) * 1000;

        echo sprintf("%12.3f ms | ", $time);
    }

    echo "\n";
}

echo "\nObservations:\n";
echo "  • Insertion sort excels on nearly sorted data (adaptive)\n";
echo "  • Quick sort can degrade on sorted data (O(n²) worst case)\n";
echo "  • Merge sort consistent across all inputs\n\n";

// Test 5: Scaling Behavior
echo "5. Scaling Behavior (How time grows with n)\n";
echo "=============================================\n";

$sizes = [100, 500, 1000, 2000, 5000];

echo str_pad('Size', 8) . " | ";
echo str_pad('Insertion', 12) . " | ";
echo str_pad('Quick', 12) . " | ";
echo str_pad('Merge', 12) . "\n";

echo str_repeat('-', 8) . " | ";
echo str_repeat('-', 12) . " | ";
echo str_repeat('-', 12) . " | ";
echo str_repeat('-', 12) . "\n";

foreach ($sizes as $size) {
    $arr = range(1, $size);
    shuffle($arr);

    echo sprintf("%7d | ", $size);

    // Insertion sort
    $test = $arr;
    $start = microtime(true);
    insertionSort($test);
    $insertionTime = (microtime(true) - $start) * 1000;
    echo sprintf("%9.3f ms | ", $insertionTime);

    // Quick sort
    $test = $arr;
    $start = microtime(true);
    quickSort($test, 0, count($test) - 1);
    $quickTime = (microtime(true) - $start) * 1000;
    echo sprintf("%9.3f ms | ", $quickTime);

    // Merge sort
    $test = $arr;
    $start = microtime(true);
    mergeSort($test);
    $mergeTime = (microtime(true) - $start) * 1000;
    echo sprintf("%9.3f ms\n", $mergeTime);
}

echo "\nObservations:\n";
echo "  • Insertion sort: ~4x slower when size doubles (O(n²))\n";
echo "  • Quick/Merge: ~2x slower when size doubles (O(n log n))\n\n";

// Test 6: Memory Usage Estimation
echo "6. Memory Usage Comparison\n";
echo "===========================\n";

echo "Algorithm      | Extra Space | Notes\n";
echo "---------------|-------------|----------------------------------------\n";
echo "Bubble Sort    | O(1)        | In-place, only swap variables\n";
echo "Selection Sort | O(1)        | In-place, minimal extra space\n";
echo "Insertion Sort | O(1)        | In-place, one temp variable\n";
echo "Merge Sort     | O(n)        | Requires temp arrays for merging\n";
echo "Quick Sort     | O(log n)    | Recursion stack (balanced)\n";
echo "               | O(n)        | Recursion stack (worst case)\n";
echo "Heap Sort      | O(1)        | In-place, no extra arrays\n";

echo "\n";

// Test 7: Recommendations
echo "7. Algorithm Selection Guide\n";
echo "=============================\n\n";

echo "Choose Based on Your Needs:\n\n";

echo "Small datasets (< 50 elements):\n";
echo "  → Insertion Sort\n";
echo "  Why: Simple, fast for small n, adaptive\n\n";

echo "Nearly sorted data:\n";
echo "  → Insertion Sort\n";
echo "  Why: O(n) on sorted data, adaptive\n\n";

echo "General purpose (large datasets):\n";
echo "  → Quick Sort (with randomization)\n";
echo "  Why: Fastest in practice, in-place\n\n";

echo "Guaranteed O(n log n) required:\n";
echo "  → Heap Sort or Merge Sort\n";
echo "  Why: No worst-case degradation\n\n";

echo "Stability required:\n";
echo "  → Merge Sort or Tim Sort\n";
echo "  Why: Preserves order of equal elements\n\n";

echo "Limited memory:\n";
echo "  → Heap Sort or Quick Sort\n";
echo "  Why: In-place sorting\n\n";

echo "Integer keys, small range:\n";
echo "  → Counting Sort\n";
echo "  Why: O(n+k) when k is small\n\n";

echo "Multi-digit integers:\n";
echo "  → Radix Sort\n";
echo "  Why: O(d*n) for d-digit numbers\n\n";

echo "8. Real-World Implementations\n";
echo "===============================\n\n";

echo "Language/Library | Algorithm Used\n";
echo "-----------------|--------------------------------------------------\n";
echo "Python (sorted)  | Tim Sort (hybrid: merge + insertion)\n";
echo "Java (Arrays)    | Dual-Pivot Quick Sort (primitives), Tim Sort (objects)\n";
echo "C++ (std::sort)  | Intro Sort (quick + heap + insertion)\n";
echo "PHP (sort)       | Quick Sort variant\n";
echo "JavaScript       | Tim Sort (V8), Merge Sort (others)\n";
echo "Go (sort)        | Intro Sort variant\n";
echo "Rust (sort)      | Pattern-defeating Quick Sort\n";

echo "\n";

echo "Key Insight:\n";
echo "  Production implementations use HYBRID algorithms:\n";
echo "  • Start with quick sort for large partitions\n";
echo "  • Switch to heap sort if recursion depth exceeds log n\n";
echo "  • Use insertion sort for small partitions (< 10-20 elements)\n";
echo "  • This combines best aspects of multiple algorithms!\n";

echo "\n✅ Complete! Sorting comparison demonstrated.\n";
