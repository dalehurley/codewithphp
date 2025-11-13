---
title: "Algorithm Selection Guide"
description: "Learn how to choose the right algorithm for your problem based on data characteristics, constraints, and performance requirements"
series: "php-algorithms"
chapter: 28
order: 28
difficulty: "intermediate"
prerequisites: ["All previous chapters"]
---

# Algorithm Selection Guide

Choosing the right algorithm is crucial for application performance. This chapter provides decision trees and guidelines for selecting appropriate algorithms based on problem characteristics.

## Sorting Algorithm Selection

```php
<?php

class SortingSelector
{
    public function recommendSorting(array $options): string
    {
        $size = $options['size'] ?? 0;
        $dataType = $options['type'] ?? 'general';  // general, integers, nearly_sorted
        $memoryConstrained = $options['memory_limited'] ?? false;
        $stable = $options['needs_stable'] ?? false;

        // Decision tree
        if ($size < 10) {
            return 'Insertion Sort - Best for tiny arrays';
        }

        if ($dataType === 'nearly_sorted') {
            return 'Insertion Sort - O(n) for nearly sorted data';
        }

        if ($dataType === 'integers' && $size > 1000) {
            $range = $options['max_value'] ?? PHP_INT_MAX;
            if ($range < $size * 10) {
                return 'Counting Sort - O(n + k) for limited range integers';
            }
        }

        if ($memoryConstrained) {
            return 'Heap Sort - O(n log n) worst case, O(1) extra space';
        }

        if ($stable) {
            if ($size < 1000) {
                return 'Insertion Sort - Simple and stable';
            }
            return 'Merge Sort - O(n log n) guaranteed, stable';
        }

        if ($size > 1000000) {
            return 'Quick Sort - Average O(n log n), best in practice for large data';
        }

        return 'PHP sort() - Optimized hybrid algorithm';
    }

    public function sortingComparison(): array
    {
        return [
            'Bubble Sort' => [
                'Time Best' => 'O(n)',
                'Time Average' => 'O(n²)',
                'Time Worst' => 'O(n²)',
                'Space' => 'O(1)',
                'Stable' => 'Yes',
                'Use When' => 'Educational purposes only'
            ],
            'Insertion Sort' => [
                'Time Best' => 'O(n)',
                'Time Average' => 'O(n²)',
                'Time Worst' => 'O(n²)',
                'Space' => 'O(1)',
                'Stable' => 'Yes',
                'Use When' => 'Small or nearly sorted arrays'
            ],
            'Merge Sort' => [
                'Time Best' => 'O(n log n)',
                'Time Average' => 'O(n log n)',
                'Time Worst' => 'O(n log n)',
                'Space' => 'O(n)',
                'Stable' => 'Yes',
                'Use When' => 'Need guaranteed O(n log n), stability required'
            ],
            'Quick Sort' => [
                'Time Best' => 'O(n log n)',
                'Time Average' => 'O(n log n)',
                'Time Worst' => 'O(n²)',
                'Space' => 'O(log n)',
                'Stable' => 'No',
                'Use When' => 'General purpose, large arrays, average case matters'
            ],
            'Heap Sort' => [
                'Time Best' => 'O(n log n)',
                'Time Average' => 'O(n log n)',
                'Time Worst' => 'O(n log n)',
                'Space' => 'O(1)',
                'Stable' => 'No',
                'Use When' => 'Memory constrained, need O(n log n) guarantee'
            ],
            'Counting Sort' => [
                'Time Best' => 'O(n + k)',
                'Time Average' => 'O(n + k)',
                'Time Worst' => 'O(n + k)',
                'Space' => 'O(k)',
                'Stable' => 'Yes',
                'Use When' => 'Limited integer range, k ≈ n'
            ]
        ];
    }
}

// Usage
$selector = new SortingSelector();

echo $selector->recommendSorting([
    'size' => 100000,
    'type' => 'general',
    'needs_stable' => false
]) . "\n";
// Quick Sort - Average O(n log n), best in practice for large data

echo $selector->recommendSorting([
    'size' => 5000,
    'type' => 'integers',
    'max_value' => 10000,
    'needs_stable' => true
]) . "\n";
// Counting Sort - O(n + k) for limited range integers
```

## Search Algorithm Selection

```php
<?php

class SearchSelector
{
    public function recommendSearch(array $options): string
    {
        $sorted = $options['is_sorted'] ?? false;
        $size = $options['size'] ?? 0;
        $dataStructure = $options['structure'] ?? 'array';  // array, tree, graph
        $frequentSearches = $options['frequent'] ?? false;

        // Search in graph/tree
        if ($dataStructure === 'graph' || $dataStructure === 'tree') {
            $weighted = $options['weighted'] ?? false;

            if ($dataStructure === 'tree') {
                if ($sorted) {
                    return 'Binary Search Tree traversal - O(log n) average';
                }
                return 'DFS or BFS tree traversal - O(n)';
            }

            if ($weighted) {
                $negativeCost = $options['negative_weights'] ?? false;
                if ($negativeCost) {
                    return 'Bellman-Ford - O(VE) handles negative weights';
                }
                return 'Dijkstra - O(E + V log V) for weighted graphs';
            }

            return 'BFS - O(V + E) for unweighted shortest path';
        }

        // Search in array
        if ($frequentSearches && $size > 100) {
            return 'Build hash table - O(1) lookup after O(n) preprocessing';
        }

        if ($sorted) {
            if ($size > 100) {
                return 'Binary Search - O(log n)';
            }
            return 'Linear Search - O(n) but simple for small arrays';
        }

        if ($size < 50) {
            return 'Linear Search - O(n) simple and efficient for small data';
        }

        return 'Consider sorting first, then Binary Search - Overall O(n log n + k log n) for k searches';
    }

    public function searchComparison(): array
    {
        return [
            'Linear Search' => [
                'Time' => 'O(n)',
                'Space' => 'O(1)',
                'Requires Sorted' => 'No',
                'Use When' => 'Unsorted data, small datasets, single search'
            ],
            'Binary Search' => [
                'Time' => 'O(log n)',
                'Space' => 'O(1)',
                'Requires Sorted' => 'Yes',
                'Use When' => 'Sorted data, large datasets, multiple searches'
            ],
            'Hash Table' => [
                'Time' => 'O(1) average',
                'Space' => 'O(n)',
                'Requires Sorted' => 'No',
                'Use When' => 'Many lookups, have extra memory'
            ],
            'BST Search' => [
                'Time' => 'O(log n) average',
                'Space' => 'O(n)',
                'Requires Sorted' => 'No',
                'Use When' => 'Dynamic data, need sorted order, range queries'
            ]
        ];
    }
}

// Usage
$selector = new SearchSelector();

echo $selector->recommendSearch([
    'is_sorted' => true,
    'size' => 10000,
    'frequent' => false
]) . "\n";
// Binary Search - O(log n)

echo $selector->recommendSearch([
    'structure' => 'graph',
    'weighted' => true,
    'negative_weights' => false
]) . "\n";
// Dijkstra - O(E + V log V) for weighted graphs
```

## Data Structure Selection

```php
<?php

class DataStructureSelector
{
    public function recommendStructure(array $requirements): string
    {
        $operations = $requirements['operations'] ?? [];
        $priority = $requirements['priority'] ?? 'balanced';  // read, write, balanced
        $ordered = $requirements['need_order'] ?? false;
        $unique = $requirements['unique_keys'] ?? false;

        // Determine primary operations
        $needsFIFO = in_array('queue', $operations);
        $needsLIFO = in_array('stack', $operations);
        $needsPriority = in_array('priority', $operations);
        $needsKeyValue = in_array('key_value', $operations);
        $needsRangeQuery = in_array('range_query', $operations);

        if ($needsLIFO) {
            return 'Stack (SplStack or array) - O(1) push/pop';
        }

        if ($needsFIFO) {
            return 'Queue (SplQueue or array) - O(1) enqueue/dequeue';
        }

        if ($needsPriority) {
            return 'Priority Queue (SplPriorityQueue or heap) - O(log n) insert, O(1) peek';
        }

        if ($needsKeyValue) {
            if ($ordered || $needsRangeQuery) {
                return 'Balanced BST (AVL/Red-Black) - O(log n) ops, maintains order';
            }

            if ($priority === 'read') {
                return 'Hash Table (PHP array) - O(1) average lookup, insertion';
            }

            return 'Hash Table (PHP array) - Best all-around key-value store';
        }

        if ($ordered) {
            if ($unique) {
                return 'Balanced BST or SplHeap - O(log n) ops, maintains sorted order';
            }
            return 'Dynamic Array (PHP array) - Maintains insertion order';
        }

        if ($unique) {
            return 'Hash Set (array with keys) - O(1) membership test';
        }

        return 'Dynamic Array (PHP array) - Default choice for sequential data';
    }

    public function dataStructureComparison(): array
    {
        return [
            'Array' => [
                'Access' => 'O(1)',
                'Search' => 'O(n)',
                'Insert End' => 'O(1) amortized',
                'Insert Middle' => 'O(n)',
                'Delete' => 'O(n)',
                'Best For' => 'Random access, append operations'
            ],
            'Linked List' => [
                'Access' => 'O(n)',
                'Search' => 'O(n)',
                'Insert End' => 'O(1)',
                'Insert Middle' => 'O(1) with pointer',
                'Delete' => 'O(1) with pointer',
                'Best For' => 'Frequent insertions/deletions in middle'
            ],
            'Hash Table' => [
                'Access' => 'O(1) average',
                'Search' => 'O(1) average',
                'Insert End' => 'O(1) average',
                'Insert Middle' => 'O(1) average',
                'Delete' => 'O(1) average',
                'Best For' => 'Fast lookups, key-value storage'
            ],
            'BST (Balanced)' => [
                'Access' => 'O(log n)',
                'Search' => 'O(log n)',
                'Insert End' => 'O(log n)',
                'Insert Middle' => 'O(log n)',
                'Delete' => 'O(log n)',
                'Best For' => 'Sorted data, range queries'
            ],
            'Heap' => [
                'Access' => 'O(n)',
                'Search' => 'O(n)',
                'Insert End' => 'O(log n)',
                'Insert Middle' => 'O(log n)',
                'Delete' => 'O(log n)',
                'Best For' => 'Priority queue, finding min/max'
            ]
        ];
    }
}

// Usage
$selector = new DataStructureSelector();

echo $selector->recommendStructure([
    'operations' => ['key_value', 'range_query'],
    'need_order' => true
]) . "\n";
// Balanced BST (AVL/Red-Black) - O(log n) ops, maintains order

echo $selector->recommendStructure([
    'operations' => ['priority'],
    'need_order' => false
]) . "\n";
// Priority Queue (SplPriorityQueue or heap) - O(log n) insert, O(1) peek
```

## Algorithm Pattern Recognition

```php
<?php

class PatternRecognizer
{
    public function identifyPattern(string $problemDescription): array
    {
        $patterns = [
            'Two Pointers' => [
                'Keywords' => ['sorted array', 'pairs', 'triplets', 'palindrome'],
                'Examples' => 'Two sum in sorted array, container with most water',
                'Time' => 'O(n)',
                'When' => 'Array/string problems with pairs or subsequences'
            ],
            'Sliding Window' => [
                'Keywords' => ['substring', 'subarray', 'consecutive', 'window'],
                'Examples' => 'Longest substring, maximum subarray sum',
                'Time' => 'O(n)',
                'When' => 'Find optimal continuous sequence'
            ],
            'Binary Search' => [
                'Keywords' => ['sorted', 'find', 'search space', 'monotonic'],
                'Examples' => 'Search in rotated array, find peak element',
                'Time' => 'O(log n)',
                'When' => 'Sorted data or search space can be halved'
            ],
            'Backtracking' => [
                'Keywords' => ['all combinations', 'permutations', 'subsets'],
                'Examples' => 'N-Queens, Sudoku solver, combinations',
                'Time' => 'O(2^n) or O(n!)',
                'When' => 'Generate all possible solutions'
            ],
            'Dynamic Programming' => [
                'Keywords' => ['optimal', 'maximum', 'minimum', 'count ways'],
                'Examples' => 'Knapsack, LCS, coin change',
                'Time' => 'O(n²) or O(n×m) typically',
                'When' => 'Overlapping subproblems, optimal substructure'
            ],
            'Graph Traversal' => [
                'Keywords' => ['connected', 'path', 'cycle', 'island'],
                'Examples' => 'Number of islands, course schedule',
                'Time' => 'O(V + E)',
                'When' => 'Relationships between entities'
            ],
            'Greedy' => [
                'Keywords' => ['minimum', 'maximum', 'earliest', 'latest'],
                'Examples' => 'Activity selection, Huffman coding',
                'Time' => 'O(n log n) typically',
                'When' => 'Local optimal choices lead to global optimum'
            ],
            'Divide and Conquer' => [
                'Keywords' => ['divide', 'merge', 'half'],
                'Examples' => 'Merge sort, quick sort, closest pair',
                'Time' => 'O(n log n)',
                'When' => 'Problem can be split into independent subproblems'
            ]
        ];

        $matches = [];
        $description = strtolower($problemDescription);

        foreach ($patterns as $patternName => $info) {
            foreach ($info['Keywords'] as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $matches[] = [
                        'pattern' => $patternName,
                        'match' => $keyword,
                        'info' => $info
                    ];
                    break;
                }
            }
        }

        return $matches;
    }

    public function suggestApproach(string $problem): string
    {
        $matches = $this->identifyPattern($problem);

        if (empty($matches)) {
            return "No clear pattern detected. Start with brute force, then optimize.";
        }

        $suggestions = "Detected patterns:\n\n";

        foreach ($matches as $match) {
            $suggestions .= "**{$match['pattern']}**\n";
            $suggestions .= "- Matched keyword: '{$match['match']}'\n";
            $suggestions .= "- Time complexity: {$match['info']['Time']}\n";
            $suggestions .= "- When to use: {$match['info']['When']}\n";
            $suggestions .= "- Example problems: {$match['info']['Examples']}\n\n";
        }

        return $suggestions;
    }
}

// Usage
$recognizer = new PatternRecognizer();

$problem1 = "Find the longest substring without repeating characters";
echo $recognizer->suggestApproach($problem1);
// Detected: Sliding Window

$problem2 = "Find minimum coins needed to make a target amount";
echo $recognizer->suggestApproach($problem2);
// Detected: Dynamic Programming

$problem3 = "Check if graph has a cycle";
echo $recognizer->suggestApproach($problem3);
// Detected: Graph Traversal
```

## Performance Characteristics Decision Matrix

```php
<?php

class PerformanceMatrix
{
    public function selectByConstraints(array $constraints): string
    {
        $n = $constraints['input_size'] ?? 1000;
        $timeLimit = $constraints['time_limit_ms'] ?? 1000;
        $memoryLimit = $constraints['memory_limit_mb'] ?? 128;

        // Estimate acceptable complexity
        $operationsPerMs = 1000000;  // Rough estimate: 1M ops per ms
        $maxOperations = $timeLimit * $operationsPerMs;

        if ($n <= 10) {
            return "O(n!) possible - Can try brute force";
        }

        if ($n <= 20) {
            return "O(2^n) possible - Backtracking/DP with memoization";
        }

        if ($n <= 100 && $n * $n <= $maxOperations) {
            return "O(n²) acceptable - Simple nested loops";
        }

        if ($n <= 10000 && $n * log($n) <= $maxOperations) {
            return "O(n log n) recommended - Sorting-based or divide-and-conquer";
        }

        if ($n * log($n) > $maxOperations) {
            if ($n <= $maxOperations) {
                return "O(n) required - Linear scan, hash table, or amortized analysis";
            }

            return "O(log n) or O(1) required - Binary search or direct computation";
        }

        return "O(n log n) recommended - Optimal for general sorting/searching";
    }

    public function complexityGuide(): array
    {
        return [
            'O(1)' => [
                'Name' => 'Constant',
                'Max n' => 'Any',
                'Examples' => 'Array access, hash table lookup',
                'Notes' => 'Best possible, not always achievable'
            ],
            'O(log n)' => [
                'Name' => 'Logarithmic',
                'Max n' => '> 1 billion',
                'Examples' => 'Binary search, balanced tree ops',
                'Notes' => 'Very scalable, look for halving'
            ],
            'O(n)' => [
                'Name' => 'Linear',
                'Max n' => '~ 100 million',
                'Examples' => 'Array traversal, counting',
                'Notes' => 'Usually acceptable, single pass'
            ],
            'O(n log n)' => [
                'Name' => 'Linearithmic',
                'Max n' => '~ 10 million',
                'Examples' => 'Merge sort, quick sort',
                'Notes' => 'Optimal for comparison sorting'
            ],
            'O(n²)' => [
                'Name' => 'Quadratic',
                'Max n' => '~ 10,000',
                'Examples' => 'Bubble sort, nested loops',
                'Notes' => 'Avoid for large data'
            ],
            'O(2^n)' => [
                'Name' => 'Exponential',
                'Max n' => '~ 20',
                'Examples' => 'Subset generation, backtracking',
                'Notes' => 'Only for tiny inputs'
            ],
            'O(n!)' => [
                'Name' => 'Factorial',
                'Max n' => '~ 10',
                'Examples' => 'Permutations, TSP brute force',
                'Notes' => 'Impractical beyond small n'
            ]
        ];
    }
}

// Usage
$matrix = new PerformanceMatrix();

echo $matrix->selectByConstraints([
    'input_size' => 100000,
    'time_limit_ms' => 1000
]) . "\n";
// O(n log n) recommended - Sorting-based or divide-and-conquer

echo $matrix->selectByConstraints([
    'input_size' => 15,
    'time_limit_ms' => 1000
]) . "\n";
// O(2^n) possible - Backtracking/DP with memoization
```

## Decision Tree for Common Problems

```php
<?php

class ProblemDecisionTree
{
    public function solve(string $problemType, array $details = []): string
    {
        return match($problemType) {
            'find_element' => $this->findElementStrategy($details),
            'sort_data' => $this->sortStrategy($details),
            'optimize' => $this->optimizationStrategy($details),
            'count_ways' => $this->countingStrategy($details),
            'path_finding' => $this->pathStrategy($details),
            default => "Unknown problem type"
        };
    }

    private function findElementStrategy(array $details): string
    {
        if ($details['sorted'] ?? false) {
            return "Binary Search - O(log n)";
        }

        if ($details['frequency'] === 'multiple') {
            return "Build hash table first - O(n) preprocessing, O(1) lookups";
        }

        return "Linear Search - O(n)";
    }

    private function sortStrategy(array $details): string
    {
        $size = $details['size'] ?? 0;
        $stable = $details['stable'] ?? false;

        if ($stable) {
            return "Merge Sort - O(n log n), stable";
        }

        if ($size > 1000000) {
            return "Quick Sort - O(n log n) average, fast in practice";
        }

        return "PHP sort() - Optimized built-in";
    }

    private function optimizationStrategy(array $details): string
    {
        if ($details['greedy_works'] ?? false) {
            return "Greedy Algorithm - O(n log n) typical";
        }

        if ($details['overlapping_subproblems'] ?? false) {
            return "Dynamic Programming - Various complexities";
        }

        return "Try greedy first, verify correctness, fall back to DP if needed";
    }

    private function countingStrategy(array $details): string
    {
        return "Dynamic Programming - Build count table bottom-up";
    }

    private function pathStrategy(array $details): string
    {
        if ($details['weighted'] ?? false) {
            if ($details['negative_weights'] ?? false) {
                return "Bellman-Ford - O(VE)";
            }
            return "Dijkstra - O(E + V log V)";
        }

        return "BFS - O(V + E) for unweighted shortest path";
    }
}
```

## Best Practices

1. **Start Simple**
   - Begin with brute force to understand the problem
   - Optimize only after correctness is verified

2. **Consider Trade-offs**
   - Time vs space
   - Simplicity vs performance
   - Preprocessing vs query time

3. **Know Your Data**
   - Size and growth rate
   - Distribution (random, sorted, nearly sorted)
   - Update frequency

4. **Benchmark in Practice**
   - Theoretical complexity doesn't always match reality
   - PHP array operations are highly optimized
   - Network/IO often dominates algorithm cost

5. **Premature Optimization**
   - Profile before optimizing
   - 80/20 rule: optimize bottlenecks only
   - Readable code > clever code

## Key Takeaways

- Algorithm selection depends on data size, structure, and operations
- No single "best" algorithm - context matters
- Start with simple solutions, optimize bottlenecks
- Consider both time and space complexity
- PHP's built-in functions are highly optimized
- Pattern recognition helps identify approach quickly
- Understand trade-offs between different approaches
- Benchmark with realistic data
- Complexity guide helps estimate feasibility
- Know when brute force is acceptable

## Next Steps

In the next chapter, we'll explore performance optimization techniques including profiling, benchmarking, and optimization strategies specific to PHP.
