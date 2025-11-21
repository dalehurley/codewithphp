<?php

declare(strict_types=1);

/**
 * Breadth-First Search (BFS) Traversal
 *
 * BFS explores graph level by level using a queue.
 * Guarantees shortest path in unweighted graphs.
 *
 * Time Complexity: O(V + E)
 * Space Complexity: O(V)
 *
 * PHP Version: 8.0+
 */

class BFS
{
    /**
     * Basic BFS traversal
     *
     * @param array $graph Adjacency list
     * @param int $start Starting vertex
     * @return array Vertices in BFS order
     */
    public function traverse(array $graph, int $start): array
    {
        $visited = [$start => true];
        $result = [$start];
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $result[] = $neighbor;
                    $queue[] = $neighbor;
                }
            }
        }

        return $result;
    }

    /**
     * BFS with level tracking
     *
     * @param array $graph Adjacency list
     * @param int $start Starting vertex
     * @return array Vertices grouped by level
     */
    public function traverseByLevel(array $graph, int $start): array
    {
        $visited = [$start => true];
        $levels = [[$start]];
        $queue = [[$start, 0]];

        while (!empty($queue)) {
            [$vertex, $level] = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;

                    if (!isset($levels[$level + 1])) {
                        $levels[$level + 1] = [];
                    }
                    $levels[$level + 1][] = $neighbor;
                    $queue[] = [$neighbor, $level + 1];
                }
            }
        }

        return $levels;
    }

    /**
     * Find shortest path distance
     *
     * @param array $graph Adjacency list
     * @param int $start Start vertex
     * @param int $end End vertex
     * @return int|null Distance or null if no path
     */
    public function shortestDistance(array $graph, int $start, int $end): ?int
    {
        if ($start === $end) return 0;

        $visited = [$start => true];
        $queue = [[$start, 0]];

        while (!empty($queue)) {
            [$vertex, $distance] = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if ($neighbor === $end) {
                    return $distance + 1;
                }

                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $queue[] = [$neighbor, $distance + 1];
                }
            }
        }

        return null;
    }

    /**
     * Find shortest path
     *
     * @param array $graph Adjacency list
     * @param int $start Start vertex
     * @param int $end End vertex
     * @return array|null Path or null if no path
     */
    public function shortestPath(array $graph, int $start, int $end): ?array
    {
        if ($start === $end) return [$start];

        $visited = [$start => true];
        $parent = [$start => null];
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $parent[$neighbor] = $vertex;
                    $queue[] = $neighbor;

                    if ($neighbor === $end) {
                        return $this->reconstructPath($parent, $start, $end);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Reconstruct path from parent array
     */
    private function reconstructPath(array $parent, int $start, int $end): array
    {
        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $parent[$current];
        }

        return $path;
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== BFS Traversal Examples ===\n\n";

$bfs = new BFS();

$graph = [
    0 => [1, 3],
    1 => [0, 2, 4],
    2 => [1],
    3 => [0, 4],
    4 => [1, 3]
];

echo "=== Basic BFS Traversal ===\n";
$result = $bfs->traverse($graph, 0);
echo "BFS from vertex 0: " . implode(' → ', $result) . "\n\n";

echo "=== Level-by-Level Traversal ===\n";
$levels = $bfs->traverseByLevel($graph, 0);
foreach ($levels as $level => $vertices) {
    echo "Level $level: [" . implode(', ', $vertices) . "]\n";
}
echo "\n";

echo "=== Shortest Path ===\n";
$path = $bfs->shortestPath($graph, 0, 2);
echo "Shortest path from 0 to 2: " . implode(' → ', $path) . "\n";
echo "Distance: " . $bfs->shortestDistance($graph, 0, 2) . "\n";

echo "\n=== Done ===\n";
