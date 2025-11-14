<?php
/**
 * Bubble Sort & Selection Sort
 *
 * Implementation and comparison of basic sorting algorithms
 *
 * @package PHPAlgorithms
 * @chapter 05
 */

declare(strict_types=1);

// ============================================================================
// BUBBLE SORT
// ============================================================================

/**
 * Basic bubble sort - O(n²)
 */
function bubbleSort(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }

    return $arr;
}

/**
 * Optimized bubble sort with early exit
 */
function bubbleSortOptimized(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;

        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swapped = true;
            }
        }

        if (!$swapped) break; // Already sorted
    }

    return $arr;
}

/**
 * Bubble sort with visualization
 */
function bubbleSortVisualized(array $arr): array
{
    $n = count($arr);
    echo "Starting array: " . implode(', ', $arr) . "\n\n";

    for ($i = 0; $i < $n - 1; $i++) {
        echo "Pass " . ($i + 1) . ":\n";
        $swaps = 0;

        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                echo "  Swap {$arr[$j]} and {$arr[$j + 1]}\n";
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swaps++;
            }
        }

        echo "  Result: " . implode(', ', $arr) . " ({$swaps} swaps)\n\n";

        if ($swaps === 0) {
            echo "  Already sorted!\n";
            break;
        }
    }

    return $arr;
}

// ============================================================================
// SELECTION SORT
// ============================================================================

/**
 * Basic selection sort - O(n²)
 */
function selectionSort(array $arr): array
{
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;

        // Find minimum in unsorted portion
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Swap if needed
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
        }
    }

    return $arr;
}

/**
 * Selection sort with visualization
 */
function selectionSortVisualized(array $arr): array
{
    $n = count($arr);
    echo "Starting array: " . implode(', ', $arr) . "\n\n";

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;

        // Find minimum
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Swap if needed
        if ($minIndex !== $i) {
            echo "Pass " . ($i + 1) . ": ";
            echo "Swap {$arr[$i]} (pos $i) with {$arr[$minIndex]} (pos $minIndex)\n";
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
            echo "  Result: " . implode(', ', $arr) . "\n\n";
        }
    }

    return $arr;
}

// ============================================================================
// VARIANTS
// ============================================================================

/**
 * Cocktail shaker sort (bidirectional bubble sort)
 */
function cocktailSort(array $arr): array
{
    $n = count($arr);
    $swapped = true;
    $start = 0;
    $end = $n - 1;

    while ($swapped) {
        $swapped = false;

        // Forward pass
        for ($i = $start; $i < $end; $i++) {
            if ($arr[$i] > $arr[$i + 1]) {
                [$arr[$i], $arr[$i + 1]] = [$arr[$i + 1], $arr[$i]];
                $swapped = true;
            }
        }

        if (!$swapped) break;

        $swapped = false;
        $end--;

        // Backward pass
        for ($i = $end; $i > $start; $i--) {
            if ($arr[$i] < $arr[$i - 1]) {
                [$arr[$i], $arr[$i - 1]] = [$arr[$i - 1], $arr[$i]];
                $swapped = true;
            }
        }

        $start++;
    }

    return $arr;
}

// ============================================================================
// BENCHMARKING
// ============================================================================

function benchmarkSort(string $name, callable $sortFn, array $data, int $iterations = 10): float
{
    $start = hrtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $sortFn($data);
    }

    $end = hrtime(true);
    return ($end - $start) / 1_000_000 / $iterations; // avg ms
}

function compareAlgorithms(array $sizes): void
{
    echo "=== Sorting Algorithm Comparison ===\n\n";

    foreach ($sizes as $size) {
        echo "Array size: {$size}\n";
        echo str_repeat('-', 50) . "\n";

        $data = range(1, $size);
        shuffle($data);

        $results = [
            'Bubble Sort' => benchmarkSort('Bubble', 'bubbleSort', $data),
            'Bubble (Opt)' => benchmarkSort('BubbleOpt', 'bubbleSortOptimized', $data),
            'Selection Sort' => benchmarkSort('Selection', 'selectionSort', $data),
            'Cocktail Sort' => benchmarkSort('Cocktail', 'cocktailSort', $data),
            'PHP sort()' => benchmarkSort('PHP', function($arr) {
                sort($arr);
                return $arr;
            }, $data),
        ];

        asort($results);
        $fastest = reset($results);

        foreach ($results as $name => $time) {
            $ratio = $time / max($fastest, 0.001);
            printf("%-20s: %8.3f ms (%.2fx)\n", $name, $time, $ratio);
        }

        echo "\n";
    }
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Bubble Sort & Selection Sort ===\n\n";

    // Visualized bubble sort
    echo "1. Bubble Sort (Visualized):\n";
    bubbleSortVisualized([5, 2, 8, 1, 9]);

    echo "\n";

    // Visualized selection sort
    echo "2. Selection Sort (Visualized):\n";
    selectionSortVisualized([64, 25, 12, 22, 11]);

    echo "\n";

    // Performance comparison
    echo "3. Performance Comparison:\n";
    compareAlgorithms([10, 50, 100, 500]);

    // Key differences
    echo "=== Key Differences ===\n";
    echo "Bubble Sort:\n";
    echo "  • Compares adjacent elements\n";
    echo "  • Swaps frequently: O(n²) swaps\n";
    echo "  • Best case: O(n) with optimization\n";
    echo "  • Stable: maintains relative order\n\n";

    echo "Selection Sort:\n";
    echo "  • Finds minimum each pass\n";
    echo "  • Minimal swaps: O(n) swaps\n";
    echo "  • Always O(n²) time\n";
    echo "  • Not stable (can change order)\n\n";

    echo "When to use:\n";
    echo "  • Small arrays (< 100 elements)\n";
    echo "  • Nearly sorted data (bubble with optimization)\n";
    echo "  • Expensive write operations (selection)\n";
    echo "  • Educational purposes\n";
    echo "  • In production: Use sort() instead!\n";
}
