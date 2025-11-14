<?php

declare(strict_types=1);

/**
 * Bipartite Graph Detection using BFS
 *
 * Check if a graph can be colored with two colors such that
 * no adjacent vertices have the same color.
 *
 * Applications: Matching problems, scheduling, conflict resolution
 *
 * Time Complexity: O(V + E)
 * Space Complexity: O(V)
 *
 * PHP Version: 8.0+
 */

class BipartiteDetector
{
    private const COLOR_A = 0;
    private const COLOR_B = 1;

    /**
     * Check if graph is bipartite
     *
     * @param array $graph Adjacency list
     * @return bool True if bipartite
     */
    public function isBipartite(array $graph): bool
    {
        $colors = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($colors[$vertex])) {
                if (!$this->bfsColorCheck($graph, $vertex, $colors)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * BFS to check coloring
     */
    private function bfsColorCheck(array $graph, int $start, array &$colors): bool
    {
        $colors[$start] = self::COLOR_A;
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);
            $currentColor = $colors[$vertex];
            $neighborColor = $currentColor === self::COLOR_A ? self::COLOR_B : self::COLOR_A;

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($colors[$neighbor])) {
                    $colors[$neighbor] = $neighborColor;
                    $queue[] = $neighbor;
                } elseif ($colors[$neighbor] !== $neighborColor) {
                    return false; // Conflict found
                }
            }
        }

        return true;
    }

    /**
     * Get the two sets if bipartite
     *
     * @param array $graph Adjacency list
     * @return array|null Two sets or null if not bipartite
     */
    public function getTwoSets(array $graph): ?array
    {
        $colors = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($colors[$vertex])) {
                if (!$this->bfsColorCheck($graph, $vertex, $colors)) {
                    return null;
                }
            }
        }

        $setA = [];
        $setB = [];

        foreach ($colors as $vertex => $color) {
            if ($color === self::COLOR_A) {
                $setA[] = $vertex;
            } else {
                $setB[] = $vertex;
            }
        }

        return [$setA, $setB];
    }
}

// ============================================================================
// Example Usage
// ============================================================================

echo "=== Bipartite Graph Detection ===\n\n";

$detector = new BipartiteDetector();

// Bipartite graph example
echo "=== Bipartite Graph (Students and Courses) ===\n";
$bipartite = [
    0 => [3, 4],  // Student 0 takes courses 3, 4
    1 => [3, 5],  // Student 1 takes courses 3, 5
    2 => [4, 5],  // Student 2 takes courses 4, 5
    3 => [0, 1],  // Course 3 has students 0, 1
    4 => [0, 2],  // Course 4 has students 0, 2
    5 => [1, 2]   // Course 5 has students 1, 2
];

echo "Is bipartite: " . ($detector->isBipartite($bipartite) ? "Yes" : "No") . "\n";

$sets = $detector->getTwoSets($bipartite);
if ($sets) {
    echo "Set A (students): [" . implode(', ', $sets[0]) . "]\n";
    echo "Set B (courses): [" . implode(', ', $sets[1]) . "]\n";
}
echo "\n";

// Non-bipartite graph example
echo "=== Non-Bipartite Graph (Triangle) ===\n";
$triangle = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1]
];

echo "Is bipartite: " . ($detector->isBipartite($triangle) ? "Yes" : "No") . "\n";
echo "Explanation: Triangle creates odd cycle, cannot be 2-colored\n";

echo "\n=== Done ===\n";
