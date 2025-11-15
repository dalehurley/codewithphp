<?php

declare(strict_types=1);

/**
 * Advanced Sorting Techniques
 *
 * Demonstrates:
 * - Hybrid sorting algorithms (Tim Sort, Intro Sort)
 * - Custom comparison functions
 * - Stable vs unstable sorting
 * - Partially sorted data optimization
 * - External sorting concepts
 * - Parallel sorting ideas
 */

echo "=== Advanced Sorting Techniques ===\n\n";

// Hybrid Sort: Intro Sort (Quick Sort + Heap Sort + Insertion Sort)
echo "1. Intro Sort (Introspective Sort)\n";
echo "====================================\n";

function introSort(array &$arr): void
{
    $maxDepth = (int)(2 * log(count($arr), 2));
    introSortHelper($arr, 0, count($arr) - 1, $maxDepth);
}

function introSortHelper(array &$arr, int $low, int $high, int $maxDepth): void
{
    $size = $high - $low + 1;

    // Use insertion sort for small arrays
    if ($size < 16) {
        insertionSortRange($arr, $low, $high);
        return;
    }

    // Switch to heap sort if recursion depth exceeds limit
    if ($maxDepth === 0) {
        heapSortRange($arr, $low, $high);
        return;
    }

    // Use quick sort for general case
    $pivot = partitionMedianOfThree($arr, $low, $high);
    introSortHelper($arr, $low, $pivot - 1, $maxDepth - 1);
    introSortHelper($arr, $pivot + 1, $high, $maxDepth - 1);
}

function partitionMedianOfThree(array &$arr, int $low, int $high): int
{
    $mid = (int)(($low + $high) / 2);

    // Median of three as pivot
    if ($arr[$mid] < $arr[$low]) [$arr[$low], $arr[$mid]] = [$arr[$mid], $arr[$low]];
    if ($arr[$high] < $arr[$low]) [$arr[$low], $arr[$high]] = [$arr[$high], $arr[$low]];
    if ($arr[$mid] < $arr[$high]) [$arr[$mid], $arr[$high]] = [$arr[$high], $arr[$mid]];

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

function insertionSortRange(array &$arr, int $low, int $high): void
{
    for ($i = $low + 1; $i <= $high; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        while ($j >= $low && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        $arr[$j + 1] = $key;
    }
}

function heapSortRange(array &$arr, int $low, int $high): void
{
    $size = $high - $low + 1;

    // Build heap
    for ($i = (int)($size / 2) - 1; $i >= 0; $i--) {
        heapifyRange($arr, $low, $size, $i);
    }

    // Extract elements
    for ($i = $size - 1; $i > 0; $i--) {
        [$arr[$low], $arr[$low + $i]] = [$arr[$low + $i], $arr[$low]];
        heapifyRange($arr, $low, $i, 0);
    }
}

function heapifyRange(array &$arr, int $offset, int $n, int $i): void
{
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    if ($left < $n && $arr[$offset + $left] > $arr[$offset + $largest]) {
        $largest = $left;
    }

    if ($right < $n && $arr[$offset + $right] > $arr[$offset + $largest]) {
        $largest = $right;
    }

    if ($largest !== $i) {
        [$arr[$offset + $i], $arr[$offset + $largest]] = [$arr[$offset + $largest], $arr[$offset + $i]];
        heapifyRange($arr, $offset, $n, $largest);
    }
}

echo "Intro Sort combines:\n";
echo "  • Quick sort for general cases (fast average)\n";
echo "  • Heap sort when recursion depth exceeds 2*log(n) (prevents O(n²))\n";
echo "  • Insertion sort for small partitions (< 16 elements)\n\n";

$numbers = range(1, 1000);
shuffle($numbers);

$start = microtime(true);
introSort($numbers);
$time = (microtime(true) - $start) * 1000;

echo "Sorted 1000 elements in " . number_format($time, 3) . " ms\n";
echo "Used by: C++ std::sort()\n\n";

// Custom Comparison Functions
echo "2. Custom Comparison Functions\n";
echo "================================\n";

class Person
{
    public function __construct(
        public string $name,
        public int $age,
        public string $city
    ) {}

    public function __toString(): string
    {
        return sprintf("%-10s (age %2d, %s)", $this->name, $this->age, $this->city);
    }
}

$people = [
    new Person('Alice', 30, 'NYC'),
    new Person('Bob', 25, 'LA'),
    new Person('Charlie', 30, 'NYC'),
    new Person('Diana', 25, 'Chicago'),
    new Person('Eve', 28, 'NYC'),
];

echo "Original:\n";
foreach ($people as $person) {
    echo "  $person\n";
}
echo "\n";

// Sort by age (ascending)
$byAge = $people;
usort($byAge, fn($a, $b) => $a->age <=> $b->age);

echo "Sorted by age:\n";
foreach ($byAge as $person) {
    echo "  $person\n";
}
echo "\n";

// Sort by city, then age (descending)
$byCity = $people;
usort($byCity, function($a, $b) {
    if ($a->city !== $b->city) {
        return $a->city <=> $b->city;
    }
    return $b->age <=> $a->age; // Descending age
});

echo "Sorted by city, then age (desc):\n";
foreach ($byCity as $person) {
    echo "  $person\n";
}
echo "\n";

// Tim Sort (Simplified Concept)
echo "3. Tim Sort Concept (Python's Default)\n";
echo "========================================\n";

echo "Tim Sort combines:\n";
echo "  1. Identify natural 'runs' (already sorted sequences)\n";
echo "  2. Extend runs to minimum size using insertion sort\n";
echo "  3. Merge runs using merge sort\n\n";

function timSortSimplified(array $arr): array
{
    $minRun = 32; // Minimum run size
    $n = count($arr);

    echo "Step 1: Create runs of size $minRun\n";

    // Create runs using insertion sort
    for ($start = 0; $start < $n; $start += $minRun) {
        $end = min($start + $minRun - 1, $n - 1);

        // Insertion sort this run
        for ($i = $start + 1; $i <= $end; $i++) {
            $key = $arr[$i];
            $j = $i - 1;

            while ($j >= $start && $arr[$j] > $key) {
                $arr[$j + 1] = $arr[$j];
                $j--;
            }

            $arr[$j + 1] = $key;
        }
    }

    echo "  Created " . (int)ceil($n / $minRun) . " sorted runs\n\n";

    echo "Step 2: Merge runs\n";

    // Merge runs
    $size = $minRun;

    while ($size < $n) {
        for ($start = 0; $start < $n; $start += 2 * $size) {
            $mid = min($start + $size - 1, $n - 1);
            $end = min($start + 2 * $size - 1, $n - 1);

            if ($mid < $end) {
                $left = array_slice($arr, $start, $mid - $start + 1);
                $right = array_slice($arr, $mid + 1, $end - $mid);

                $merged = merge($left, $right);

                for ($i = 0; $i < count($merged); $i++) {
                    $arr[$start + $i] = $merged[$i];
                }
            }
        }

        $size *= 2;
    }

    echo "  Merged all runs\n\n";

    return $arr;
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

$data = range(1, 100);
shuffle($data);

echo "Sorting 100 elements with Tim Sort:\n";
$sorted = timSortSimplified($data);
echo "Result: [" . implode(', ', array_slice($sorted, 0, 10)) . "...]\n\n";

// Stability Demonstration
echo "4. Stability in Sorting\n";
echo "========================\n";

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
foreach ($records as $r) echo "$r ";
echo "\n\n";

// Stable sort (PHP's usort with stable comparison)
$stable = $records;
usort($stable, fn($a, $b) => $a->key <=> $b->key);

echo "Stable sort (merge sort based):\n  ";
foreach ($stable as $r) echo "$r ";
echo "\n  Records with key 3: A→C→E (order preserved)\n\n";

// Unstable sort simulation (quick sort might reorder)
echo "Unstable sort can produce:\n  ";
echo "3(C) 3(A) 3(E) (order may change)\n\n";

echo "Why stability matters:\n";
echo "  • Multi-key sorting (sort by secondary key, then primary)\n";
echo "  • Database ORDER BY with multiple columns\n";
echo "  • Maintaining chronological order in tied records\n\n";

// Partially Sorted Data Optimization
echo "5. Optimization for Partially Sorted Data\n";
echo "===========================================\n";

// Adaptive insertion sort
function adaptiveInsertionSort(array $arr): array
{
    $inversions = 0;

    // Count inversions (measure of sortedness)
    for ($i = 0; $i < count($arr) - 1; $i++) {
        for ($j = $i + 1; $j < count($arr); $j++) {
            if ($arr[$i] > $arr[$j]) {
                $inversions++;
            }
        }
    }

    echo "  Inversions: $inversions (lower = more sorted)\n";

    // Use insertion sort (optimal for nearly sorted)
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

$nearlySorted = [1, 2, 3, 5, 4, 6, 7, 9, 8, 10];
echo "Nearly sorted: [" . implode(', ', $nearlySorted) . "]\n";

$sorted = adaptiveInsertionSort($nearlySorted);
echo "  Sorted: [" . implode(', ', $sorted) . "]\n";
echo "  Insertion sort is O(n) when nearly sorted!\n\n";

// External Sorting Concept
echo "6. External Sorting (Large Datasets)\n";
echo "======================================\n";

echo "Problem: Sort 1TB of data with 1GB RAM\n\n";

echo "Approach (External Merge Sort):\n";
echo "  1. Split data into 1GB chunks\n";
echo "  2. Sort each chunk in memory\n";
echo "  3. Write sorted chunks to disk\n";
echo "  4. Merge chunks using k-way merge\n\n";

echo "Example: Sort 10,000 records with buffer of 100\n";

// Simulate external sort
$totalRecords = 10000;
$bufferSize = 100;
$chunkSize = 1000;

echo "  Total records: $totalRecords\n";
echo "  Buffer size: $bufferSize\n";
echo "  Chunk size: $chunkSize\n\n";

echo "  Step 1: Create sorted chunks\n";
$numChunks = (int)ceil($totalRecords / $chunkSize);
echo "    Created $numChunks sorted chunks on disk\n\n";

echo "  Step 2: K-way merge\n";
echo "    Using min-heap to merge $numChunks chunks\n";
echo "    Time: O(n log k) where k = $numChunks\n\n";

// Parallel Sorting Concept
echo "7. Parallel Sorting (Multi-Core)\n";
echo "==================================\n";

echo "Parallel Merge Sort:\n";
echo "  1. Divide array into p parts (p = number of cores)\n";
echo "  2. Sort each part in parallel\n";
echo "  3. Merge sorted parts\n\n";

echo "Speedup: ~p times faster (with p cores)\n";
echo "Example: 8-core CPU can sort ~8x faster\n\n";

echo "Parallel Quick Sort:\n";
echo "  1. Partition around pivot\n";
echo "  2. Sort left and right partitions in parallel\n";
echo "  3. Recursively parallelize until small enough\n\n";

echo "Best for: Large datasets (millions+ elements)\n\n";

echo "Key Insights:\n";
echo "==============\n\n";

echo "Hybrid Algorithms (Production-Ready):\n";
echo "  • Intro Sort: Quick + Heap + Insertion\n";
echo "  • Tim Sort: Merge + Insertion + Run Detection\n";
echo "  • PDQ Sort: Quick + Heap + Insertion + Pattern Detection\n";
echo "  → Combine best aspects of multiple algorithms!\n\n";

echo "When to Use Custom Comparators:\n";
echo "  ✓ Multi-key sorting\n";
echo "  ✓ Complex object comparison\n";
echo "  ✓ Domain-specific ordering (e.g., suit in cards)\n";
echo "  ✓ Locale-aware string sorting\n\n";

echo "Stability Considerations:\n";
echo "  • Stable: Merge, Insertion, Tim, Counting, Radix\n";
echo "  • Unstable: Quick, Heap, Selection\n";
echo "  • Use stable sort when order of equal elements matters\n\n";

echo "Optimization Strategies:\n";
echo "  1. Detect nearly sorted data → use insertion sort\n";
echo "  2. Switch to heap sort if quick sort depth exceeds limit\n";
echo "  3. Use insertion sort for small partitions (< 16)\n";
echo "  4. Median-of-three pivot selection for quick sort\n";
echo "  5. Parallelize for large datasets\n\n";

echo "Real-World Implementations:\n";
echo "  • Python: Tim Sort (adaptive, stable)\n";
echo "  • C++: Intro Sort (fast, in-place)\n";
echo "  • Java: Dual-Pivot Quick Sort + Tim Sort\n";
echo "  • Rust: PDQ Sort (pattern-defeating quick sort)\n";
echo "  • Go: PDQ Sort variant\n\n";

echo "Trade-offs:\n";
echo "  • Simplicity vs Performance\n";
echo "  • Memory vs Speed\n";
echo "  • Stability vs In-place\n";
echo "  • Worst-case vs Average-case\n";
echo "  • Choose algorithm based on your constraints!\n";

echo "\n✅ Complete! Advanced sorting techniques demonstrated.\n";
