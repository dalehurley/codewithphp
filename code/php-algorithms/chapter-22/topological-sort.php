<?php

declare(strict_types=1);

/**
 * Topological Sort using DFS
 *
 * Orders vertices in a Directed Acyclic Graph (DAG) such that for every
 * edge u→v, u comes before v in the ordering.
 *
 * Applications: Task scheduling, build systems, course prerequisites
 *
 * Time Complexity: O(V + E)
 * Space Complexity: O(V)
 *
 * PHP Version: 8.0+
 */

class TopologicalSort
{
    private array $visited = [];
    private array $stack = [];

    /**
     * Perform topological sort on a DAG
     *
     * @param array $graph Adjacency list of directed graph
     * @return array|null Topologically sorted vertices, or null if cycle detected
     */
    public function sort(array $graph): ?array
    {
        // First check for cycles
        if ($this->hasCycle($graph)) {
            return null; // Graph has cycle, cannot perform topological sort
        }

        $this->visited = [];
        $this->stack = [];

        // Perform DFS for all vertices
        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->topologicalSortUtil($graph, $vertex);
            }
        }

        // Stack contains vertices in reverse topological order
        return array_reverse($this->stack);
    }

    /**
     * DFS helper for topological sort
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     */
    private function topologicalSortUtil(array $graph, int $vertex): void
    {
        $this->visited[$vertex] = true;

        // Visit all neighbors
        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->topologicalSortUtil($graph, $neighbor);
            }
        }

        // Push to stack after visiting all descendants
        $this->stack[] = $vertex;
    }

    /**
     * Check if graph has a cycle
     *
     * @param array $graph Graph structure
     * @return bool True if cycle exists
     */
    private function hasCycle(array $graph): bool
    {
        $visited = [];
        $recStack = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($visited[$vertex])) {
                if ($this->hasCycleUtil($graph, $vertex, $visited, $recStack)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Cycle detection helper
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     * @param array &$visited Visited array
     * @param array &$recStack Recursion stack
     * @return bool True if cycle found
     */
    private function hasCycleUtil(array $graph, int $vertex, array &$visited, array &$recStack): bool
    {
        $visited[$vertex] = true;
        $recStack[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                if ($this->hasCycleUtil($graph, $neighbor, $visited, $recStack)) {
                    return true;
                }
            } elseif (isset($recStack[$neighbor])) {
                return true;
            }
        }

        unset($recStack[$vertex]);
        return false;
    }

    /**
     * Get all possible topological orderings
     *
     * @param array $graph Graph structure
     * @return array Array of all valid topological orderings
     */
    public function allTopologicalSorts(array $graph): array
    {
        $allSorts = [];
        $visited = [];
        $path = [];
        $inDegree = $this->calculateInDegree($graph);

        $this->allTopologicalSortsUtil($graph, $visited, $path, $allSorts, $inDegree);

        return $allSorts;
    }

    /**
     * Calculate in-degree for all vertices
     *
     * @param array $graph Graph structure
     * @return array In-degree array
     */
    private function calculateInDegree(array $graph): array
    {
        $inDegree = [];

        foreach (array_keys($graph) as $vertex) {
            $inDegree[$vertex] = 0;
        }

        foreach ($graph as $vertex => $neighbors) {
            foreach ($neighbors as $neighbor) {
                $inDegree[$neighbor]++;
            }
        }

        return $inDegree;
    }

    /**
     * Helper for finding all topological sorts
     *
     * @param array $graph Graph structure
     * @param array &$visited Visited vertices
     * @param array &$path Current path
     * @param array &$allSorts All found sorts
     * @param array &$inDegree In-degree array
     */
    private function allTopologicalSortsUtil(array $graph, array &$visited, array &$path, array &$allSorts, array &$inDegree): void
    {
        $flag = false;

        foreach (array_keys($graph) as $vertex) {
            if ($inDegree[$vertex] === 0 && !isset($visited[$vertex])) {
                // Include this vertex in path
                $visited[$vertex] = true;
                $path[] = $vertex;

                // Reduce in-degree of neighbors
                foreach ($graph[$vertex] ?? [] as $neighbor) {
                    $inDegree[$neighbor]--;
                }

                $this->allTopologicalSortsUtil($graph, $visited, $path, $allSorts, $inDegree);

                // Backtrack
                unset($visited[$vertex]);
                array_pop($path);
                foreach ($graph[$vertex] ?? [] as $neighbor) {
                    $inDegree[$neighbor]++;
                }

                $flag = true;
            }
        }

        // If no vertex with in-degree 0, we have a complete path
        if (!$flag) {
            $allSorts[] = $path;
        }
    }
}

// ============================================================================
// Example Usage and Demonstrations
// ============================================================================

echo "=== Topological Sort Examples ===\n\n";

$topo = new TopologicalSort();

// Example 1: Simple DAG
echo "=== Simple Task Dependencies ===\n";
$tasks = [
    0 => [1, 2],    // Task 0 must be done before 1 and 2
    1 => [3],       // Task 1 must be done before 3
    2 => [3],       // Task 2 must be done before 3
    3 => []         // Task 3 has no dependencies
];

echo "Dependencies:\n";
echo "  Task 0 → Tasks 1, 2\n";
echo "  Task 1 → Task 3\n";
echo "  Task 2 → Task 3\n\n";

$sorted = $topo->sort($tasks);
echo "Topological order: " . implode(' → ', $sorted) . "\n";
echo "Explanation: Task 0 must come first, then 1 and 2 (in any order), finally 3\n\n";

// Example 2: Course Prerequisites
echo "=== Course Prerequisites ===\n";
$courses = [
    0 => [1],       // Intro to CS → Data Structures
    1 => [2],       // Data Structures → Algorithms
    2 => [4],       // Algorithms → Advanced Algorithms
    3 => [2],       // Math → Algorithms
    4 => []         // Advanced Algorithms (final course)
];

echo "Prerequisites:\n";
echo "  Course 0 (Intro to CS) → Course 1 (Data Structures)\n";
echo "  Course 1 (Data Structures) → Course 2 (Algorithms)\n";
echo "  Course 3 (Math) → Course 2 (Algorithms)\n";
echo "  Course 2 (Algorithms) → Course 4 (Advanced Algorithms)\n\n";

$sorted = $topo->sort($courses);
echo "Valid course order: " . implode(' → ', $sorted) . "\n\n";

// Example 3: Build System
echo "=== Build System Dependencies ===\n";
$buildSteps = [
    0 => [2],       // Clean → Compile
    1 => [2],       // Generate → Compile
    2 => [3],       // Compile → Link
    3 => [4],       // Link → Package
    4 => []         // Package
];

echo "Build steps:\n";
echo "  Clean (0) → Compile (2)\n";
echo "  Generate (1) → Compile (2)\n";
echo "  Compile (2) → Link (3)\n";
echo "  Link (3) → Package (4)\n\n";

$sorted = $topo->sort($buildSteps);
echo "Build order: " . implode(' → ', $sorted) . "\n\n";

// Example 4: Graph with cycle (should fail)
echo "=== Graph with Cycle (Invalid) ===\n";
$cyclicGraph = [
    0 => [1],
    1 => [2],
    2 => [0]        // Creates cycle: 0 → 1 → 2 → 0
];

$sorted = $topo->sort($cyclicGraph);
if ($sorted === null) {
    echo "Cannot perform topological sort: Graph contains a cycle!\n";
    echo "Cycle detected: 0 → 1 → 2 → 0\n";
}

echo "\n=== Done ===\n";
