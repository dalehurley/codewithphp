<?php

declare(strict_types=1);

/**
 * Depth-First Search (DFS) Traversal
 *
 * Demonstrates both recursive and iterative DFS implementations.
 * DFS explores as far as possible along each branch before backtracking.
 *
 * Time Complexity: O(V + E)
 * Space Complexity: O(V) for visited array + O(V) for recursion/stack
 *
 * PHP Version: 8.0+
 */

/**
 * DFS Implementation with Multiple Approaches
 */
class DFS
{
    private array $visited = [];
    private array $result = [];

    /**
     * Recursive DFS traversal
     *
     * @param array $graph Adjacency list representation
     * @param int $start Starting vertex
     * @return array Vertices in DFS order
     */
    public function traverseRecursive(array $graph, int $start): array
    {
        $this->visited = [];
        $this->result = [];
        $this->dfsRecursive($graph, $start);
        return $this->result;
    }

    /**
     * Helper method for recursive DFS
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     */
    private function dfsRecursive(array $graph, int $vertex): void
    {
        $this->visited[$vertex] = true;
        $this->result[] = $vertex;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->dfsRecursive($graph, $neighbor);
            }
        }
    }

    /**
     * Iterative DFS using explicit stack
     *
     * @param array $graph Adjacency list representation
     * @param int $start Starting vertex
     * @return array Vertices in DFS order
     */
    public function traverseIterative(array $graph, int $start): array
    {
        $visited = [];
        $result = [];
        $stack = [$start];

        while (!empty($stack)) {
            $vertex = array_pop($stack);

            if (!isset($visited[$vertex])) {
                $visited[$vertex] = true;
                $result[] = $vertex;

                // Add neighbors in reverse order to maintain same order as recursive
                $neighbors = array_reverse($graph[$vertex] ?? []);
                foreach ($neighbors as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $stack[] = $neighbor;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * DFS for all components (handles disconnected graphs)
     *
     * @param array $graph Graph structure
     * @return array Array of components, each containing vertex IDs
     */
    public function traverseAllComponents(array $graph): array
    {
        $this->visited = [];
        $components = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->result = [];
                $this->dfsRecursive($graph, $vertex);
                $components[] = $this->result;
            }
        }

        return $components;
    }

    /**
     * DFS with level/depth tracking
     *
     * @param array $graph Graph structure
     * @param int $start Starting vertex
     * @return array Associative array [vertex => depth]
     */
    public function traverseWithDepth(array $graph, int $start): array
    {
        $depths = [];
        $this->dfsWithDepth($graph, $start, 0, $depths);
        return $depths;
    }

    /**
     * Helper for DFS with depth tracking
     *
     * @param array $graph Graph structure
     * @param int $vertex Current vertex
     * @param int $depth Current depth
     * @param array &$depths Depth array (passed by reference)
     */
    private function dfsWithDepth(array $graph, int $vertex, int $depth, array &$depths): void
    {
        if (isset($depths[$vertex])) {
            return;
        }

        $depths[$vertex] = $depth;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($depths[$neighbor])) {
                $this->dfsWithDepth($graph, $neighbor, $depth + 1, $depths);
            }
        }
    }
}

// ============================================================================
// Example Usage and Demonstrations
// ============================================================================

echo "=== DFS Traversal Examples ===\n\n";

// Create a sample graph
$graph = [
    0 => [1, 3],
    1 => [0, 2, 4],
    2 => [1],
    3 => [0, 4],
    4 => [1, 3]
];

echo "Graph structure:\n";
foreach ($graph as $vertex => $neighbors) {
    echo "$vertex: [" . implode(', ', $neighbors) . "]\n";
}
echo "\n";

$dfs = new DFS();

// Recursive traversal
echo "=== Recursive DFS from vertex 0 ===\n";
$recursiveResult = $dfs->traverseRecursive($graph, 0);
echo "Traversal order: " . implode(' → ', $recursiveResult) . "\n\n";

// Iterative traversal
echo "=== Iterative DFS from vertex 0 ===\n";
$iterativeResult = $dfs->traverseIterative($graph, 0);
echo "Traversal order: " . implode(' → ', $iterativeResult) . "\n\n";

// Disconnected graph
echo "=== Disconnected Graph Example ===\n";
$disconnectedGraph = [
    0 => [1],
    1 => [0],
    2 => [3],
    3 => [2],
    4 => []
];

$components = $dfs->traverseAllComponents($disconnectedGraph);
echo "Found " . count($components) . " components:\n";
foreach ($components as $i => $component) {
    echo "Component " . ($i + 1) . ": [" . implode(', ', $component) . "]\n";
}
echo "\n";

// DFS with depth tracking
echo "=== DFS with Depth Tracking ===\n";
$depths = $dfs->traverseWithDepth($graph, 0);
echo "Vertex depths from vertex 0:\n";
foreach ($depths as $vertex => $depth) {
    echo "Vertex $vertex: depth $depth\n";
}

echo "\n=== Done ===\n";
