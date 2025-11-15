<?php

declare(strict_types=1);

/**
 * Insertion Sort - O(n²) Sorting Algorithm
 *
 * Demonstrates:
 * - Insertion sort implementation
 * - Building sorted array one element at a time
 * - Adaptive performance on nearly-sorted data
 * - Stable sorting
 * - Optimal for small arrays and online sorting
 */

echo "=== Insertion Sort ===\n\n";

echo "Concept: Build sorted array one element at a time\n";
echo "         Insert each element into its correct position in sorted portion\n";
echo "         Like sorting playing cards in your hand\n\n";

/**
 * Basic insertion sort implementation
 */
function insertionSort(array $arr): array
{
    $n = count($arr);
    $comparisons = 0;
    $shifts = 0;

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        // Move elements greater than key one position ahead
        while ($j >= 0 && $arr[$j] > $key) {
            $comparisons++;
            $arr[$j + 1] = $arr[$j];
            $shifts++;
            $j--;
        }

        if ($j >= 0) $comparisons++;

        $arr[$j + 1] = $key;
    }

    echo "  Comparisons: $comparisons\n";
    echo "  Shifts: $shifts\n";

    return $arr;
}

/**
 * Insertion sort with visualization
 */
function insertionSortVisualized(array $arr): array
{
    $n = count($arr);

    echo "Initial: [" . implode(', ', $arr) . "]\n";
    echo "         sorted | unsorted →\n\n";

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        echo "Step $i: Insert $key into sorted portion\n";
        echo "  Before: [";
        for ($k = 0; $k < $n; $k++) {
            if ($k < $i) {
                echo "\033[32m" . $arr[$k] . "\033[0m"; // Green for sorted
            } else if ($k == $i) {
                echo "\033[33m" . $arr[$k] . "\033[0m"; // Yellow for current
            } else {
                echo $arr[$k];
            }
            if ($k < $n - 1) echo ", ";
        }
        echo "]\n";

        while ($j >= 0 && $arr[$j] > $key) {
            echo "  Shift {$arr[$j]} right\n";
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        $arr[$j + 1] = $key;

        echo "  After:  [";
        for ($k = 0; $k < $n; $k++) {
            if ($k <= $i) {
                echo "\033[32m" . $arr[$k] . "\033[0m";
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
 * Binary insertion sort (uses binary search for position)
 */
function binaryInsertionSort(array $arr): array
{
    $n = count($arr);

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];

        // Binary search for insertion position
        $left = 0;
        $right = $i - 1;

        while ($left <= $right) {
            $mid = (int)(($left + $right) / 2);
            if ($arr[$mid] > $key) {
                $right = $mid - 1;
            } else {
                $left = $mid + 1;
            }
        }

        // Shift elements
        for ($j = $i - 1; $j >= $left; $j--) {
            $arr[$j + 1] = $arr[$j];
        }

        $arr[$left] = $key;
    }

    return $arr;
}

// Test 1: Basic Insertion Sort
echo "1. Basic Insertion Sort\n";
echo "========================\n";

$numbers = [12, 11, 13, 5, 6];
echo "Input: [" . implode(', ', $numbers) . "]\n";

$sorted = insertionSort($numbers);
echo "Output: [" . implode(', ', $sorted) . "]\n\n";

// Test 2: Visualization
echo "2. Step-by-Step Visualization\n";
echo "==============================\n";

$numbers2 = [5, 2, 4, 6, 1, 3];
insertionSortVisualized($numbers2);

// Test 3: Nearly Sorted Array (Shows Adaptive Behavior)
echo "3. Nearly Sorted Array (Adaptive Behavior)\n";
echo "============================================\n";

$nearlySorted = [1, 2, 3, 5, 4, 6, 7, 8, 10, 9];
echo "Input: [" . implode(', ', $nearlySorted) . "] (nearly sorted)\n";

$sorted3 = insertionSort($nearlySorted);
echo "Output: [" . implode(', ', $sorted3) . "]\n";
echo "Notice: Very few comparisons and shifts!\n\n";

// Test 4: Stability Test
echo "4. Stability Test\n";
echo "==================\n";

class Person
{
    public function __construct(
        public string $name,
        public int $age
    ) {}

    public function __toString(): string
    {
        return "{$this->name}({$this->age})";
    }
}

$people = [
    new Person('Alice', 25),
    new Person('Bob', 30),
    new Person('Charlie', 25),
    new Person('Diana', 28),
    new Person('Eve', 25),
];

echo "Input: ";
foreach ($people as $p) {
    echo "$p ";
}
echo "\n";

// Sort by age
for ($i = 1; $i < count($people); $i++) {
    $key = $people[$i];
    $j = $i - 1;

    while ($j >= 0 && $people[$j]->age > $key->age) {
        $people[$j + 1] = $people[$j];
        $j--;
    }

    $people[$j + 1] = $key;
}

echo "Output: ";
foreach ($people as $p) {
    echo "$p ";
}
echo "\n";
echo "Notice: People with age 25 maintain order: Alice→Charlie→Eve (stable!)\n\n";

// Test 5: Performance on Different Input Types
echo "5. Performance on Different Inputs\n";
echo "====================================\n";

$testSize = 100;

// Already sorted (best case)
$sorted = range(1, $testSize);
$start = microtime(true);
$temp = insertionSort($sorted);
$sortedTime = (microtime(true) - $start) * 1000;

// Reverse sorted (worst case)
$reversed = range($testSize, 1, -1);
$start = microtime(true);
$temp = insertionSort($reversed);
$reversedTime = (microtime(true) - $start) * 1000;

// Random (average case)
$random = range(1, $testSize);
shuffle($random);
$start = microtime(true);
$temp = insertionSort($random);
$randomTime = (microtime(true) - $start) * 1000;

// Nearly sorted (few inversions)
$nearlySorted = range(1, $testSize);
for ($i = 0; $i < 5; $i++) {
    $idx1 = random_int(0, $testSize - 1);
    $idx2 = random_int(0, $testSize - 1);
    [$nearlySorted[$idx1], $nearlySorted[$idx2]] = [$nearlySorted[$idx2], $nearlySorted[$idx1]];
}
$start = microtime(true);
$temp = insertionSort($nearlySorted);
$nearlyTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Sorted (best):       " . number_format($sortedTime, 3) . " ms\n";
echo "Nearly sorted:       " . number_format($nearlyTime, 3) . " ms\n";
echo "Random (average):    " . number_format($randomTime, 3) . " ms\n";
echo "Reversed (worst):    " . number_format($reversedTime, 3) . " ms\n\n";

echo "Worst/Best ratio:    " . number_format($reversedTime / $sortedTime, 1) . "x\n";
echo "Nearly sorted is close to best case! (Adaptive)\n\n";

// Test 6: Binary vs Linear Insertion
echo "6. Binary Insertion Sort Comparison\n";
echo "=====================================\n";

$testSize = 500;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$start = microtime(true);
insertionSort($arr1);
$linearTime = (microtime(true) - $start) * 1000;

$arr2 = $testArr;
$start = microtime(true);
binaryInsertionSort($arr2);
$binaryTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements\n\n";
echo "Linear search:  " . number_format($linearTime, 3) . " ms\n";
echo "Binary search:  " . number_format($binaryTime, 3) . " ms\n\n";

echo "Note: Binary search reduces comparisons but not shifts.\n";
echo "      Overall improvement is modest for typical data.\n\n";

// Test 7: Online Sorting (Sorting as data arrives)
echo "7. Online Sorting Example\n";
echo "==========================\n";

echo "Simulating data arriving in real-time...\n\n";

$sortedArray = [];

for ($i = 1; $i <= 10; $i++) {
    $newValue = random_int(1, 50);

    echo "Incoming: $newValue → ";

    // Insert into sorted array
    $sortedArray[] = $newValue;

    // Sort using insertion sort (efficient for adding one element)
    $j = count($sortedArray) - 2;
    $key = $sortedArray[count($sortedArray) - 1];

    while ($j >= 0 && $sortedArray[$j] > $key) {
        $sortedArray[$j + 1] = $sortedArray[$j];
        $j--;
    }

    $sortedArray[$j + 1] = $key;

    echo "[" . implode(', ', $sortedArray) . "]\n";
}

echo "\nInsertion sort is perfect for online sorting!\n";
echo "New elements are inserted in O(n) time.\n\n";

// Test 8: Comparison with Other O(n²) Sorts
echo "8. Comparison with Bubble and Selection Sort\n";
echo "==============================================\n";

$testSize = 500;
$testArr = range(1, $testSize);
shuffle($testArr);

$arr1 = $testArr;
$start = microtime(true);
// Bubble sort
$n = count($arr1);
for ($i = 0; $i < $n - 1; $i++) {
    for ($j = 0; $j < $n - $i - 1; $j++) {
        if ($arr1[$j] > $arr1[$j + 1]) {
            [$arr1[$j], $arr1[$j + 1]] = [$arr1[$j + 1], $arr1[$j]];
        }
    }
}
$bubbleTime = (microtime(true) - $start) * 1000;

$arr2 = $testArr;
$start = microtime(true);
// Selection sort
for ($i = 0; $i < $n - 1; $i++) {
    $minIndex = $i;
    for ($j = $i + 1; $j < $n; $j++) {
        if ($arr2[$j] < $arr2[$minIndex]) {
            $minIndex = $j;
        }
    }
    if ($minIndex !== $i) {
        [$arr2[$i], $arr2[$minIndex]] = [$arr2[$minIndex], $arr2[$i]];
    }
}
$selectionTime = (microtime(true) - $start) * 1000;

$arr3 = $testArr;
$start = microtime(true);
insertionSort($arr3);
$insertionTime = (microtime(true) - $start) * 1000;

echo "Array size: $testSize elements (random)\n\n";
echo "Bubble Sort:    " . number_format($bubbleTime, 3) . " ms\n";
echo "Selection Sort: " . number_format($selectionTime, 3) . " ms\n";
echo "Insertion Sort: " . number_format($insertionTime, 3) . " ms\n\n";

$fastest = min($bubbleTime, $selectionTime, $insertionTime);
echo "Insertion Sort is typically fastest for random data.\n\n";

echo "Key Insights:\n";
echo "==============\n";
echo "Time Complexity:\n";
echo "  • Best case:     O(n)   - already sorted\n";
echo "  • Average case:  O(n²)  - random data\n";
echo "  • Worst case:    O(n²)  - reverse sorted\n\n";

echo "Space Complexity:\n";
echo "  • O(1) - in-place sorting\n\n";

echo "Properties:\n";
echo "  ✓ Stable:     Yes - equal elements maintain order\n";
echo "  ✓ In-place:   Yes - no extra array needed\n";
echo "  ✓ Adaptive:   Yes - O(n) when nearly sorted\n";
echo "  ✓ Online:     Yes - can sort data as it arrives\n\n";

echo "Advantages:\n";
echo "  ✓ Simple and intuitive (like sorting cards)\n";
echo "  ✓ Efficient for small datasets (< 50 elements)\n";
echo "  ✓ Efficient for nearly sorted data\n";
echo "  ✓ Stable sort (preserves order of equal elements)\n";
echo "  ✓ Online algorithm (adds new elements efficiently)\n";
echo "  ✓ Low overhead (minimal constant factors)\n\n";

echo "Disadvantages:\n";
echo "  ✗ O(n²) time complexity on random data\n";
echo "  ✗ Not suitable for large datasets\n\n";

echo "When to Use:\n";
echo "  ✓ Small datasets (< 50 elements)\n";
echo "  ✓ Nearly sorted data\n";
echo "  ✓ Online sorting (data arrives over time)\n";
echo "  ✓ As part of hybrid sorts (Tim Sort, Intro Sort)\n";
echo "  ✓ When stability is required\n";
echo "  ✓ When simplicity is important\n\n";

echo "Real-World Usage:\n";
echo "  • Tim Sort (Python's default) uses insertion sort for small runs\n";
echo "  • Intro Sort (C++ STL) uses insertion sort for small partitions\n";
echo "  • Databases use insertion sort for very small result sets\n";
echo "  • Often used as final polish in hybrid algorithms\n";

echo "\n✅ Complete! Insertion sort demonstrated.\n";
