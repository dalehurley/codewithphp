<?php

declare(strict_types=1);

/**
 * Path Finding using DFS
 *
 * Find paths between vertices using Depth-First Search.
 * DFS explores deeply before backtracking, useful for finding any path.
 *
 * Note: DFS does not guarantee shortest path (use BFS for that).
 *
 * Time Complexity: O(V + E)
 * Space Complexity: O(V)
 *
 * PHP Version: 8.0+
 */

class DFSPathFinder
{
    private array $visited = [];
    private array $path = [];

    /**
     * Find a path from start to end
     *
     * @param array $graph Adjacency list
     * @param int $start Start vertex
     * @param int $end End vertex
     * @return array|null Path as array of vertices, or null if no path
     */
    public function findPath(array $graph, int $start, int $end): ?array
    {
        $this->visited = [];
        $this->path = [];

        if ($this->dfsPath($graph, $start, $end)) {
            return $this->path;
        }

        return null;
    }

    /**
     * DFS helper for path finding
     *
     * @param array $graph Graph structure
     * @param int $current Current vertex
     * @param int $end Target vertex
     * @return bool True if path found
     */
    private function dfsPath(array $graph, int $current, int $end): bool
    {
        $this->visited[$current] = true;
        $this->path[] = $current;

        // Found the destination
        if ($current === $end) {
            return true;
        }

        // Explore neighbors
        foreach ($graph[$current] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                if ($this->dfsPath($graph, $neighbor, $end)) {
                    return true;
                }
            }
        }

        // Dead end - backtrack
        array_pop($this->path);
        return false;
    }

    /**
     * Find all paths from start to end
     *
     * @param array $graph Adjacency list
     * @param int $start Start vertex
     * @param int $end End vertex
     * @return array Array of all paths
     */
    public function findAllPaths(array $graph, int $start, int $end): array
    {
        $allPaths = [];
        $this->visited = [];
        $this->findAllPathsHelper($graph, $start, $end, [], $allPaths);
        return $allPaths;
    }

    /**
     * Helper for finding all paths
     *
     * @param array $graph Graph structure
     * @param int $current Current vertex
     * @param int $end Target vertex
     * @param array $currentPath Current path being explored
     * @param array &$allPaths All found paths
     */
    private function findAllPathsHelper(array $graph, int $current, int $end, array $currentPath, array &$allPaths): void
    {
        $this->visited[$current] = true;
        $currentPath[] = $current;

        if ($current === $end) {
            $allPaths[] = $currentPath;
        } else {
            foreach ($graph[$current] ?? [] as $neighbor) {
                if (!isset($this->visited[$neighbor])) {
                    $this->findAllPathsHelper($graph, $neighbor, $end, $currentPath, $allPaths);
                }
            }
        }

        // Backtrack: unmark for other paths
        unset($this->visited[$current]);
    }

    /**
     * Check if path exists between two vertices
     *
     * @param array $graph Adjacency list
     * @param int $start Start vertex
     * @param int $end End vertex
     * @return bool True if path exists
     */
    public function hasPath(array $graph, int $start, int $end): bool
    {
        $visited = [];
        return $this->hasPathHelper($graph, $start, $end, $visited);
    }

    /**
     * Helper for path existence check
     *
     * @param array $graph Graph structure
     * @param int $current Current vertex
     * @param int $end Target vertex
     * @param array &$visited Visited array
     * @return bool True if path exists
     */
    private function hasPathHelper(array $graph, int $current, int $end, array &$visited): bool
    {
        if ($current === $end) {
            return true;
        }

        $visited[$current] = true;

        foreach ($graph[$current] ?? [] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                if ($this->hasPathHelper($graph, $neighbor, $end, $visited)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find longest path from start to end (in acyclic graph)
     *
     * @param array $graph Adjacency list
     * @param int $start Start vertex
     * @param int $end End vertex
     * @return array|null Longest path or null
     */
    public function findLongestPath(array $graph, int $start, int $end): ?array
    {
        $allPaths = $this->findAllPaths($graph, $start, $end);

        if (empty($allPaths)) {
            return null;
        }

        $longest = $allPaths[0];
        foreach ($allPaths as $path) {
            if (count($path) > count($longest)) {
                $longest = $path;
            }
        }

        return $longest;
    }
}

// ============================================================================
// Example Usage and Demonstrations
// ============================================================================

echo "=== DFS Path Finding Examples ===\n\n";

$pathFinder = new DFSPathFinder();

// Example 1: Simple graph path finding
echo "=== Simple Graph ===\n";
$graph = [
    0 => [1, 2],
    1 => [3],
    2 => [1, 3],
    3 => []
];

echo "Graph structure:\n";
foreach ($graph as $v => $neighbors) {
    echo "  $v → [" . implode(', ', $neighbors) . "]\n";
}
echo "\n";

$path = $pathFinder->findPath($graph, 0, 3);
echo "Path from 0 to 3: " . implode(' → ', $path) . "\n\n";

// Example 2: Find all paths
echo "=== All Paths from 0 to 3 ===\n";
$allPaths = $pathFinder->findAllPaths($graph, 0, 3);
foreach ($allPaths as $i => $path) {
    echo "Path " . ($i + 1) . ": " . implode(' → ', $path) . "\n";
}
echo "\n";

// Example 3: Check path existence
echo "=== Path Existence Check ===\n";
echo "Path exists from 0 to 3? " . ($pathFinder->hasPath($graph, 0, 3) ? "Yes" : "No") . "\n";
echo "Path exists from 3 to 0? " . ($pathFinder->hasPath($graph, 3, 0) ? "Yes" : "No") . "\n\n";

// Example 4: Maze solving
echo "=== Maze Solving ===\n";
$maze = [
    0 => [1],       // Start
    1 => [2, 4],
    2 => [3],
    3 => [6],
    4 => [5],
    5 => [6],
    6 => []         // Exit
];

echo "Maze structure (0 = start, 6 = exit):\n";
$exitPath = $pathFinder->findPath($maze, 0, 6);
if ($exitPath) {
    echo "Solution: " . implode(' → ', $exitPath) . "\n";
}

echo "\nAll possible solutions:\n";
$allMazePaths = $pathFinder->findAllPaths($maze, 0, 6);
foreach ($allMazePaths as $i => $path) {
    echo "Solution " . ($i + 1) . ": " . implode(' → ', $path) . "\n";
}
echo "\n";

// Example 5: Longest path
echo "=== Longest Path ===\n";
$longest = $pathFinder->findLongestPath($maze, 0, 6);
echo "Longest path from 0 to 6: " . implode(' → ', $longest) . "\n";
echo "Length: " . count($longest) . " vertices\n";

echo "\n=== Done ===\n";
