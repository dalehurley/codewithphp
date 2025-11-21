<?php

declare(strict_types=1);

/**
 * Cycle Detection using DFS
 *
 * Detects cycles in both directed and undirected graphs using DFS.
 * Essential for dependency validation, deadlock detection, and graph analysis.
 *
 * Time Complexity: O(V + E)
 * Space Complexity: O(V)
 *
 * PHP Version: 8.0+
 */

class CycleDetector
{
    private array $visited = [];
    private array $recursionStack = [];

    /**
     * Detect cycle in a directed graph
     *
     * @param array $graph Adjacency list of directed graph
     * @return bool True if cycle exists
     */
    public function hasCycleDirected(array $graph): bool
    {
        $this->visited = [];
        $this->recursionStack = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                if ($this->hasCycleDirectedUtil($graph, $vertex)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper method for directed graph cycle detection
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     * @return bool True if cycle found
     */
    private function hasCycleDirectedUtil(array $graph, int $vertex): bool
    {
        $this->visited[$vertex] = true;
        $this->recursionStack[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                if ($this->hasCycleDirectedUtil($graph, $neighbor)) {
                    return true;
                }
            } elseif (isset($this->recursionStack[$neighbor])) {
                // Back edge found - cycle exists
                return true;
            }
        }

        unset($this->recursionStack[$vertex]);
        return false;
    }

    /**
     * Detect cycle in an undirected graph
     *
     * @param array $graph Adjacency list of undirected graph
     * @return bool True if cycle exists
     */
    public function hasCycleUndirected(array $graph): bool
    {
        $this->visited = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                if ($this->hasCycleUndirectedUtil($graph, $vertex, -1)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper method for undirected graph cycle detection
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     * @param int $parent Parent vertex in DFS tree
     * @return bool True if cycle found
     */
    private function hasCycleUndirectedUtil(array $graph, int $vertex, int $parent): bool
    {
        $this->visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                if ($this->hasCycleUndirectedUtil($graph, $neighbor, $vertex)) {
                    return true;
                }
            } elseif ($neighbor !== $parent) {
                // Visited neighbor that's not parent - cycle found
                return true;
            }
        }

        return false;
    }

    /**
     * Find a cycle in directed graph (returns the cycle if exists)
     *
     * @param array $graph Adjacency list
     * @return array|null Array of vertices in cycle, or null if no cycle
     */
    public function findCycleDirected(array $graph): ?array
    {
        $this->visited = [];
        $this->recursionStack = [];
        $parent = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $cycle = $this->findCycleDirectedUtil($graph, $vertex, $parent);
                if ($cycle !== null) {
                    return $cycle;
                }
            }
        }

        return null;
    }

    /**
     * Helper to find cycle in directed graph
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     * @param array &$parent Parent tracking array
     * @return array|null Cycle vertices or null
     */
    private function findCycleDirectedUtil(array $graph, int $vertex, array &$parent): ?array
    {
        $this->visited[$vertex] = true;
        $this->recursionStack[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            $parent[$neighbor] = $vertex;

            if (!isset($this->visited[$neighbor])) {
                $cycle = $this->findCycleDirectedUtil($graph, $neighbor, $parent);
                if ($cycle !== null) {
                    return $cycle;
                }
            } elseif (isset($this->recursionStack[$neighbor])) {
                // Found cycle, reconstruct it
                return $this->reconstructCycle($parent, $neighbor, $vertex);
            }
        }

        unset($this->recursionStack[$vertex]);
        return null;
    }

    /**
     * Reconstruct cycle from parent array
     *
     * @param array $parent Parent array
     * @param int $start Start of cycle
     * @param int $end End of cycle
     * @return array Cycle vertices
     */
    private function reconstructCycle(array $parent, int $start, int $end): array
    {
        $cycle = [$start];
        $current = $end;

        while ($current !== $start && isset($parent[$current])) {
            array_unshift($cycle, $current);
            $current = $parent[$current];
        }

        array_unshift($cycle, $start);
        return $cycle;
    }
}

// ============================================================================
// Example Usage and Demonstrations
// ============================================================================

echo "=== Cycle Detection Examples ===\n\n";

$detector = new CycleDetector();

// Example 1: Directed graph with cycle
echo "=== Directed Graph with Cycle ===\n";
$directedCycle = [
    0 => [1],
    1 => [2],
    2 => [0]  // Cycle: 0 → 1 → 2 → 0
];

echo "Graph: 0 → 1 → 2 → 0\n";
echo "Has cycle: " . ($detector->hasCycleDirected($directedCycle) ? "Yes" : "No") . "\n";

$cycle = $detector->findCycleDirected($directedCycle);
if ($cycle) {
    echo "Cycle found: " . implode(' → ', $cycle) . "\n";
}
echo "\n";

// Example 2: Directed graph without cycle (DAG)
echo "=== Directed Acyclic Graph (DAG) ===\n";
$dag = [
    0 => [1, 2],
    1 => [3],
    2 => [3],
    3 => []
];

echo "Graph: 0 → {1, 2}, 1 → 3, 2 → 3\n";
echo "Has cycle: " . ($detector->hasCycleDirected($dag) ? "Yes" : "No") . "\n\n";

// Example 3: Undirected graph with cycle
echo "=== Undirected Graph with Cycle ===\n";
$undirectedCycle = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1]  // Triangle: 0-1-2-0
];

echo "Graph: Triangle (0-1-2)\n";
echo "Has cycle: " . ($detector->hasCycleUndirected($undirectedCycle) ? "Yes" : "No") . "\n\n";

// Example 4: Undirected graph without cycle (Tree)
echo "=== Undirected Graph without Cycle (Tree) ===\n";
$tree = [
    0 => [1, 2],
    1 => [0, 3],
    2 => [0],
    3 => [1]
];

echo "Graph: Tree structure\n";
echo "Has cycle: " . ($detector->hasCycleUndirected($tree) ? "Yes" : "No") . "\n\n";

// Example 5: Complex directed graph
echo "=== Complex Directed Graph ===\n";
$complex = [
    0 => [1],
    1 => [2],
    2 => [3],
    3 => [4],
    4 => [1]  // Cycle: 1 → 2 → 3 → 4 → 1
];

echo "Has cycle: " . ($detector->hasCycleDirected($complex) ? "Yes" : "No") . "\n";
$cycle = $detector->findCycleDirected($complex);
if ($cycle) {
    echo "Cycle found: " . implode(' → ', $cycle) . "\n";
}

echo "\n=== Done ===\n";
