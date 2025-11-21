<?php

declare(strict_types=1);

/**
 * Dijkstra's Shortest Path Algorithm
 *
 * Finds shortest paths from source vertex to all others
 * in a weighted graph with non-negative edge weights.
 *
 * Time Complexity: O((V + E) log V) with priority queue
 * Space Complexity: O(V)
 *
 * PHP Version: 8.0+
 */

class DijkstraShortestPath
{
    private const INF = PHP_INT_MAX;

    /**
     * Find shortest paths using priority queue
     *
     * @param array $graph Weighted adjacency list
     * @param int $source Source vertex
     * @return array Distances array
     */
    public function findShortestPaths(array $graph, int $source): array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $distances[$source] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();

            if ($currentDist > $distances[$u]) {
                continue;
            }

            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge['vertex'];
                $weight = $edge['weight'];
                $newDist = $distances[$u] + $weight;

                if ($newDist < $distances[$v]) {
                    $distances[$v] = $newDist;
                    $pq->insert([$newDist, $v]);
                }
            }
        }

        return $distances;
    }

    /**
     * Find shortest path to specific destination
     *
     * @param array $graph Weighted adjacency list
     * @param int $source Source vertex
     * @param int $destination Destination vertex
     * @return array|null Path or null if unreachable
     */
    public function findPath(array $graph, int $source, int $destination): ?array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $previous = array_fill(0, $vertices, null);
        $distances[$source] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();

            if ($u === $destination) {
                return $this->reconstructPath($previous, $source, $destination);
            }

            if ($currentDist > $distances[$u]) {
                continue;
            }

            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge['vertex'];
                $weight = $edge['weight'];
                $newDist = $distances[$u] + $weight;

                if ($newDist < $distances[$v]) {
                    $distances[$v] = $newDist;
                    $previous[$v] = $u;
                    $pq->insert([$newDist, $v]);
                }
            }
        }

        return null;
    }

    /**
     * Reconstruct path from previous array
     */
    private function reconstructPath(array $previous, int $source, int $destination): array
    {
        $path = [];
        $current = $destination;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        return $path[0] === $source ? $path : [];
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== Dijkstra's Algorithm Examples ===\n\n";

$dijkstra = new DijkstraShortestPath();

// Example graph
$graph = [
    0 => [
        ['vertex' => 1, 'weight' => 4],
        ['vertex' => 2, 'weight' => 1]
    ],
    1 => [
        ['vertex' => 3, 'weight' => 1]
    ],
    2 => [
        ['vertex' => 1, 'weight' => 2],
        ['vertex' => 3, 'weight' => 5]
    ],
    3 => []
];

echo "=== Shortest Distances from Vertex 0 ===\n";
$distances = $dijkstra->findShortestPaths($graph, 0);
foreach ($distances as $vertex => $distance) {
    $dist = $distance === PHP_INT_MAX ? '∞' : $distance;
    echo "To vertex $vertex: $dist\n";
}
echo "\n";

echo "=== Shortest Path from 0 to 3 ===\n";
$path = $dijkstra->findPath($graph, 0, 3);
if ($path) {
    echo "Path: " . implode(' → ', $path) . "\n";
    echo "Distance: " . $distances[3] . "\n";
}

echo "\n=== Done ===\n";
