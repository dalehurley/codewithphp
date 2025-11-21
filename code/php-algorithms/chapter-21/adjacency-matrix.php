<?php

declare(strict_types=1);

/**
 * Adjacency Matrix Graph Representation
 *
 * This file demonstrates both unweighted and weighted adjacency matrix implementations.
 * An adjacency matrix uses a 2D array where matrix[i][j] represents the edge from vertex i to vertex j.
 *
 * Time Complexity:
 * - Add Edge: O(1)
 * - Remove Edge: O(1)
 * - Has Edge: O(1)
 * - Get Neighbors: O(V)
 *
 * Space Complexity: O(V²)
 *
 * Best for: Dense graphs with many edges, frequent edge lookup operations
 *
 * PHP Version: 8.0+
 */

/**
 * Unweighted Adjacency Matrix Implementation
 *
 * Represents an unweighted graph where edges are either present (1) or absent (0).
 */
class AdjacencyMatrix
{
    private array $matrix;
    private int $vertices;

    /**
     * Initialize adjacency matrix with given number of vertices
     *
     * @param int $vertices Number of vertices in the graph
     * @throws InvalidArgumentException if vertices is less than 1
     */
    public function __construct(int $vertices)
    {
        if ($vertices < 1) {
            throw new InvalidArgumentException("Number of vertices must be at least 1");
        }

        $this->vertices = $vertices;
        $this->matrix = array_fill(0, $vertices, array_fill(0, $vertices, 0));
    }

    /**
     * Add an undirected edge between two vertices
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @throws OutOfBoundsException if vertices are out of range
     */
    public function addEdge(int $from, int $to): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        $this->matrix[$from][$to] = 1;
        $this->matrix[$to][$from] = 1;
    }

    /**
     * Add a directed edge from one vertex to another
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @throws OutOfBoundsException if vertices are out of range
     */
    public function addDirectedEdge(int $from, int $to): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        $this->matrix[$from][$to] = 1;
    }

    /**
     * Remove an edge between two vertices
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @throws OutOfBoundsException if vertices are out of range
     */
    public function removeEdge(int $from, int $to): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        $this->matrix[$from][$to] = 0;
        $this->matrix[$to][$from] = 0;
    }

    /**
     * Check if an edge exists between two vertices
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @return bool True if edge exists
     * @throws OutOfBoundsException if vertices are out of range
     */
    public function hasEdge(int $from, int $to): bool
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        return $this->matrix[$from][$to] === 1;
    }

    /**
     * Get all neighbors of a vertex
     *
     * @param int $vertex Vertex to get neighbors for
     * @return array List of neighboring vertices
     * @throws OutOfBoundsException if vertex is out of range
     */
    public function getNeighbors(int $vertex): array
    {
        $this->validateVertex($vertex);

        $neighbors = [];
        for ($i = 0; $i < $this->vertices; $i++) {
            if ($this->matrix[$vertex][$i] === 1) {
                $neighbors[] = $i;
            }
        }
        return $neighbors;
    }

    /**
     * Get the degree (number of edges) of a vertex
     *
     * @param int $vertex Vertex to get degree for
     * @return int Number of edges connected to the vertex
     * @throws OutOfBoundsException if vertex is out of range
     */
    public function getDegree(int $vertex): int
    {
        $this->validateVertex($vertex);
        return array_sum($this->matrix[$vertex]);
    }

    /**
     * Get the adjacency matrix
     *
     * @return array The adjacency matrix
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * Print the adjacency matrix in a readable format
     */
    public function print(): void
    {
        echo "   ";
        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i ";
        }
        echo "\n";

        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i: ";
            for ($j = 0; $j < $this->vertices; $j++) {
                echo $this->matrix[$i][$j] . " ";
            }
            echo "\n";
        }
    }

    /**
     * Validate that a vertex is within valid range
     *
     * @param int $vertex Vertex to validate
     * @throws OutOfBoundsException if vertex is out of range
     */
    private function validateVertex(int $vertex): void
    {
        if ($vertex < 0 || $vertex >= $this->vertices) {
            throw new OutOfBoundsException("Vertex $vertex is out of bounds [0, {$this->vertices})");
        }
    }
}

/**
 * Weighted Adjacency Matrix Implementation
 *
 * Represents a weighted graph where edges have associated weights (costs/distances).
 */
class WeightedAdjacencyMatrix
{
    private array $matrix;
    private int $vertices;
    private const INF = PHP_INT_MAX;

    /**
     * Initialize weighted adjacency matrix
     *
     * @param int $vertices Number of vertices in the graph
     * @throws InvalidArgumentException if vertices is less than 1
     */
    public function __construct(int $vertices)
    {
        if ($vertices < 1) {
            throw new InvalidArgumentException("Number of vertices must be at least 1");
        }

        $this->vertices = $vertices;

        // Initialize with infinity (no edge)
        $this->matrix = array_fill(0, $vertices, array_fill(0, $vertices, self::INF));

        // Distance from vertex to itself is 0
        for ($i = 0; $i < $vertices; $i++) {
            $this->matrix[$i][$i] = 0;
        }
    }

    /**
     * Add an undirected weighted edge
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @param int $weight Edge weight
     * @throws OutOfBoundsException if vertices are out of range
     * @throws InvalidArgumentException if weight is negative
     */
    public function addEdge(int $from, int $to, int $weight): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        if ($weight < 0) {
            throw new InvalidArgumentException("Weight cannot be negative");
        }

        $this->matrix[$from][$to] = $weight;
        $this->matrix[$to][$from] = $weight;
    }

    /**
     * Add a directed weighted edge
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @param int $weight Edge weight
     * @throws OutOfBoundsException if vertices are out of range
     * @throws InvalidArgumentException if weight is negative
     */
    public function addDirectedEdge(int $from, int $to, int $weight): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        if ($weight < 0) {
            throw new InvalidArgumentException("Weight cannot be negative");
        }

        $this->matrix[$from][$to] = $weight;
    }

    /**
     * Get the weight of an edge
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @return int Edge weight (INF if no edge exists)
     * @throws OutOfBoundsException if vertices are out of range
     */
    public function getWeight(int $from, int $to): int
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        return $this->matrix[$from][$to];
    }

    /**
     * Get neighbors with their weights
     *
     * @param int $vertex Vertex to get neighbors for
     * @return array Array of ['vertex' => int, 'weight' => int]
     * @throws OutOfBoundsException if vertex is out of range
     */
    public function getNeighborsWithWeights(int $vertex): array
    {
        $this->validateVertex($vertex);

        $neighbors = [];
        for ($i = 0; $i < $this->vertices; $i++) {
            if ($this->matrix[$vertex][$i] !== self::INF && $this->matrix[$vertex][$i] !== 0) {
                $neighbors[] = ['vertex' => $i, 'weight' => $this->matrix[$vertex][$i]];
            }
        }
        return $neighbors;
    }

    /**
     * Print the weighted adjacency matrix
     */
    public function print(): void
    {
        echo "     ";
        for ($i = 0; $i < $this->vertices; $i++) {
            printf("%4d", $i);
        }
        echo "\n";

        for ($i = 0; $i < $this->vertices; $i++) {
            printf("%4d:", $i);
            for ($j = 0; $j < $this->vertices; $j++) {
                if ($this->matrix[$i][$j] === self::INF) {
                    echo "   ∞";
                } else {
                    printf("%4d", $this->matrix[$i][$j]);
                }
            }
            echo "\n";
        }
    }

    /**
     * Validate that a vertex is within valid range
     *
     * @param int $vertex Vertex to validate
     * @throws OutOfBoundsException if vertex is out of range
     */
    private function validateVertex(int $vertex): void
    {
        if ($vertex < 0 || $vertex >= $this->vertices) {
            throw new OutOfBoundsException("Vertex $vertex is out of bounds [0, {$this->vertices})");
        }
    }
}

// ============================================================================
// Example Usage and Demonstrations
// ============================================================================

echo "=== Unweighted Adjacency Matrix Example ===\n\n";

try {
    // Create a graph with 5 vertices
    $graph = new AdjacencyMatrix(5);

    // Add edges to create a simple graph
    $graph->addEdge(0, 1);
    $graph->addEdge(0, 2);
    $graph->addEdge(1, 2);
    $graph->addEdge(1, 3);
    $graph->addEdge(2, 4);
    $graph->addEdge(3, 4);

    echo "Graph structure:\n";
    $graph->print();

    echo "\nGraph properties:\n";
    echo "Neighbors of vertex 1: " . implode(', ', $graph->getNeighbors(1)) . "\n";
    echo "Degree of vertex 1: " . $graph->getDegree(1) . "\n";
    echo "Edge exists 0->1: " . ($graph->hasEdge(0, 1) ? 'Yes' : 'No') . "\n";
    echo "Edge exists 0->3: " . ($graph->hasEdge(0, 3) ? 'Yes' : 'No') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Weighted Adjacency Matrix Example ===\n\n";

try {
    // Create a weighted graph representing a road network
    $roadNetwork = new WeightedAdjacencyMatrix(4);

    // Add weighted edges (distances between cities)
    $roadNetwork->addEdge(0, 1, 5);  // City 0 to City 1: 5 km
    $roadNetwork->addEdge(0, 2, 3);  // City 0 to City 2: 3 km
    $roadNetwork->addEdge(1, 2, 2);  // City 1 to City 2: 2 km
    $roadNetwork->addEdge(2, 3, 7);  // City 2 to City 3: 7 km

    echo "Road network (distances in km):\n";
    $roadNetwork->print();

    echo "\nRoute information:\n";
    echo "Distance from City 0 to City 1: " . $roadNetwork->getWeight(0, 1) . " km\n";

    $neighborsOfCity2 = $roadNetwork->getNeighborsWithWeights(2);
    echo "Cities connected to City 2:\n";
    foreach ($neighborsOfCity2 as $connection) {
        echo "  - City {$connection['vertex']}: {$connection['weight']} km\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Directed Graph Example ===\n\n";

try {
    // Create a directed graph (e.g., web page links)
    $webGraph = new AdjacencyMatrix(4);

    $webGraph->addDirectedEdge(0, 1);  // Page 0 links to Page 1
    $webGraph->addDirectedEdge(0, 2);  // Page 0 links to Page 2
    $webGraph->addDirectedEdge(1, 2);  // Page 1 links to Page 2
    $webGraph->addDirectedEdge(2, 0);  // Page 2 links to Page 0
    $webGraph->addDirectedEdge(2, 3);  // Page 2 links to Page 3

    echo "Web page link structure:\n";
    $webGraph->print();

    echo "\nPage 0 links to: " . implode(', ', $webGraph->getNeighbors(0)) . "\n";
    echo "Page 2 links to: " . implode(', ', $webGraph->getNeighbors(2)) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
