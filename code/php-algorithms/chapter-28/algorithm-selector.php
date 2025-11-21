<?php

declare(strict_types=1);

/**
 * Algorithm Selection Guide
 *
 * Helps choose the right algorithm based on problem characteristics,
 * data size, and performance requirements.
 */

/**
 * Sorting Algorithm Selector
 */
class SortingSelector
{
    public function recommend(array $options): array
    {
        $size = $options['size'] ?? 0;
        $type = $options['type'] ?? 'general';
        $memoryLimited = $options['memory_limited'] ?? false;
        $stable = $options['needs_stable'] ?? false;

        if ($size < 10) {
            return [
                'algorithm' => 'Insertion Sort',
                'reason' => 'Best for tiny arrays (n < 10)',
                'complexity' => 'O(n²) but fast for small n',
                'code' => 'sort($arr);  // PHP handles this optimally'
            ];
        }

        if ($type === 'nearly_sorted') {
            return [
                'algorithm' => 'Insertion Sort',
                'reason' => 'O(n) for nearly sorted data',
                'complexity' => 'O(n) best case',
                'code' => '// Custom insertion sort'
            ];
        }

        if ($type === 'integers' && isset($options['max_value'])) {
            $range = $options['max_value'];
            if ($range < $size * 10) {
                return [
                    'algorithm' => 'Counting Sort',
                    'reason' => 'Linear time for limited range integers',
                    'complexity' => 'O(n + k) where k = range',
                    'code' => '// Counting sort implementation'
                ];
            }
        }

        if ($memoryLimited) {
            return [
                'algorithm' => 'Heap Sort',
                'reason' => 'O(n log n) with O(1) extra space',
                'complexity' => 'O(n log n) time, O(1) space',
                'code' => '// In-place heap sort'
            ];
        }

        if ($stable) {
            return [
                'algorithm' => 'Merge Sort',
                'reason' => 'Stable with guaranteed O(n log n)',
                'complexity' => 'O(n log n) time, O(n) space',
                'code' => '// Stable merge sort'
            ];
        }

        if ($size > 1000000) {
            return [
                'algorithm' => 'Quick Sort',
                'reason' => 'Best average case, excellent cache performance',
                'complexity' => 'O(n log n) average',
                'code' => 'sort($arr);  // PHP uses optimized quicksort'
            ];
        }

        return [
            'algorithm' => 'PHP sort()',
            'reason' => 'Optimized hybrid algorithm for general use',
            'complexity' => 'O(n log n)',
            'code' => 'sort($arr);'
        ];
    }

    public function getComparison(): array
    {
        return [
            'Bubble Sort' => [
                'time_avg' => 'O(n²)',
                'space' => 'O(1)',
                'stable' => true,
                'use_when' => 'Never (educational only)'
            ],
            'Insertion Sort' => [
                'time_avg' => 'O(n²)',
                'space' => 'O(1)',
                'stable' => true,
                'use_when' => 'Small arrays (n < 10) or nearly sorted'
            ],
            'Merge Sort' => [
                'time_avg' => 'O(n log n)',
                'space' => 'O(n)',
                'stable' => true,
                'use_when' => 'Need stable sort, guaranteed performance'
            ],
            'Quick Sort' => [
                'time_avg' => 'O(n log n)',
                'space' => 'O(log n)',
                'stable' => false,
                'use_when' => 'General purpose, large arrays'
            ],
            'Heap Sort' => [
                'time_avg' => 'O(n log n)',
                'space' => 'O(1)',
                'stable' => false,
                'use_when' => 'Memory constrained, guaranteed O(n log n)'
            ]
        ];
    }
}

/**
 * Search Algorithm Selector
 */
class SearchSelector
{
    public function recommend(array $options): array
    {
        $sorted = $options['is_sorted'] ?? false;
        $size = $options['size'] ?? 0;
        $structure = $options['structure'] ?? 'array';
        $frequent = $options['frequent_searches'] ?? false;

        if ($structure === 'graph') {
            $weighted = $options['weighted'] ?? false;

            if ($weighted) {
                $negative = $options['negative_weights'] ?? false;
                return [
                    'algorithm' => $negative ? 'Bellman-Ford' : 'Dijkstra',
                    'complexity' => $negative ? 'O(VE)' : 'O(E + V log V)',
                    'reason' => $negative ? 'Handles negative weights' : 'Optimal for positive weights'
                ];
            }

            return [
                'algorithm' => 'BFS',
                'complexity' => 'O(V + E)',
                'reason' => 'Shortest path in unweighted graph'
            ];
        }

        if ($frequent && $size > 100) {
            return [
                'algorithm' => 'Hash Table',
                'complexity' => 'O(1) average lookup',
                'reason' => 'Frequent lookups warrant preprocessing'
            ];
        }

        if ($sorted && $size > 100) {
            return [
                'algorithm' => 'Binary Search',
                'complexity' => 'O(log n)',
                'reason' => 'Logarithmic search in sorted data'
            ];
        }

        return [
            'algorithm' => 'Linear Search',
            'complexity' => 'O(n)',
            'reason' => 'Simple and efficient for small/unsorted data'
        ];
    }
}

/**
 * Performance Analyzer
 */
class PerformanceAnalyzer
{
    public function estimateRuntime(int $n, string $complexity): string
    {
        $opsPerSecond = 1_000_000_000;  // 1 billion ops/sec

        $operations = match($complexity) {
            'O(1)' => 1,
            'O(log n)' => log($n, 2),
            'O(n)' => $n,
            'O(n log n)' => $n * log($n, 2),
            'O(n²)' => $n * $n,
            'O(n³)' => $n * $n * $n,
            'O(2^n)' => pow(2, min($n, 30)),  // Cap to prevent overflow
            default => $n
        };

        $seconds = $operations / $opsPerSecond;

        if ($seconds < 0.001) {
            return number_format($seconds * 1_000_000, 2) . ' μs';
        } elseif ($seconds < 1) {
            return number_format($seconds * 1000, 2) . ' ms';
        } elseif ($seconds < 60) {
            return number_format($seconds, 2) . ' seconds';
        } elseif ($seconds < 3600) {
            return number_format($seconds / 60, 2) . ' minutes';
        } else {
            return number_format($seconds / 3600, 2) . ' hours';
        }
    }

    public function selectByConstraints(array $constraints): string
    {
        $n = $constraints['input_size'] ?? 1000;
        $timeLimit = $constraints['time_limit_ms'] ?? 1000;

        $maxOps = $timeLimit * 1_000_000;  // ops per ms

        if ($n <= 10) return "O(n!) possible - Brute force OK";
        if ($n <= 20) return "O(2^n) possible - Backtracking/DP";
        if ($n * $n <= $maxOps) return "O(n²) acceptable - Simple loops";
        if ($n * log($n, 2) <= $maxOps) return "O(n log n) recommended - Sorting/divide-and-conquer";
        if ($n <= $maxOps) return "O(n) required - Linear algorithms only";

        return "O(log n) or O(1) required - Preprocess or direct computation";
    }
}

// =============================================================================
// EXAMPLES
// =============================================================================

echo "Algorithm Selection Guide\n";
echo str_repeat("=", 70) . "\n\n";

// Example 1: Sorting Selection
echo "Example 1: Choose Sorting Algorithm\n";
echo "-" . str_repeat("-", 69) . "\n";

$sortingSelector = new SortingSelector();

$scenarios = [
    ['size' => 5, 'type' => 'general'],
    ['size' => 10000, 'type' => 'general', 'needs_stable' => true],
    ['size' => 1000000, 'type' => 'general'],
    ['size' => 5000, 'type' => 'integers', 'max_value' => 10000],
    ['size' => 1000, 'type' => 'general', 'memory_limited' => true]
];

foreach ($scenarios as $scenario) {
    $recommendation = $sortingSelector->recommend($scenario);
    echo "Scenario: " . json_encode($scenario) . "\n";
    echo "  → {$recommendation['algorithm']}: {$recommendation['reason']}\n";
    echo "  → Complexity: {$recommendation['complexity']}\n\n";
}

// Example 2: Search Selection
echo "Example 2: Choose Search Algorithm\n";
echo "-" . str_repeat("-", 69) . "\n";

$searchSelector = new SearchSelector();

$searchScenarios = [
    ['size' => 1000, 'is_sorted' => true],
    ['size' => 10000, 'frequent_searches' => true],
    ['structure' => 'graph', 'weighted' => true, 'negative_weights' => false],
    ['size' => 50, 'is_sorted' => false]
];

foreach ($searchScenarios as $scenario) {
    $rec = $searchSelector->recommend($scenario);
    echo "Scenario: " . json_encode($scenario) . "\n";
    echo "  → {$rec['algorithm']}: {$rec['complexity']}\n";
    echo "  → {$rec['reason']}\n\n";
}

// Example 3: Performance Estimation
echo "Example 3: Estimated Runtime\n";
echo "-" . str_repeat("-", 69) . "\n";

$analyzer = new PerformanceAnalyzer();

$testCases = [
    ['n' => 100, 'complexity' => 'O(n)'],
    ['n' => 100, 'complexity' => 'O(n²)'],
    ['n' => 1000, 'complexity' => 'O(n log n)'],
    ['n' => 10000, 'complexity' => 'O(n log n)'],
    ['n' => 1000000, 'complexity' => 'O(n)'],
    ['n' => 20, 'complexity' => 'O(2^n)']
];

foreach ($testCases as $test) {
    $runtime = $analyzer->estimateRuntime($test['n'], $test['complexity']);
    echo sprintf(
        "n = %7s, %s: ~%s\n",
        number_format($test['n']),
        str_pad($test['complexity'], 12),
        $runtime
    );
}

// Example 4: Constraint-based Selection
echo "\n" . str_repeat("=", 70) . "\n";
echo "Example 4: Select by Constraints\n";
echo str_repeat("=", 70) . "\n";

$constraints = [
    ['input_size' => 100, 'time_limit_ms' => 1000],
    ['input_size' => 10000, 'time_limit_ms' => 1000],
    ['input_size' => 1000000, 'time_limit_ms' => 1000],
    ['input_size' => 15, 'time_limit_ms' => 5000]
];

foreach ($constraints as $constraint) {
    $selection = $analyzer->selectByConstraints($constraint);
    echo "n = " . number_format($constraint['input_size']);
    echo ", time = {$constraint['time_limit_ms']}ms\n";
    echo "  → $selection\n\n";
}

// Example 5: Algorithm Comparison Table
echo "Example 5: Quick Reference Table\n";
echo "-" . str_repeat("-", 69) . "\n";

$comparison = $sortingSelector->getComparison();

echo str_pad("Algorithm", 15) . " | ";
echo str_pad("Time", 12) . " | ";
echo str_pad("Space", 8) . " | ";
echo str_pad("Stable", 7) . "\n";
echo str_repeat("-", 70) . "\n";

foreach ($comparison as $name => $info) {
    echo str_pad($name, 15) . " | ";
    echo str_pad($info['time_avg'], 12) . " | ";
    echo str_pad($info['space'], 8) . " | ";
    echo str_pad($info['stable'] ? 'Yes' : 'No', 7) . "\n";
}

echo "\n✓ All examples completed successfully!\n";
echo "\nKey Takeaway: Choose algorithms based on:\n";
echo "  • Data size and characteristics\n";
echo "  • Time and space constraints\n";
echo "  • Stability requirements\n";
echo "  • Access patterns (one-time vs frequent)\n";
