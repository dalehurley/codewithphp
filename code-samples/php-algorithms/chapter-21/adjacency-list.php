<?php

declare(strict_types=1);

/**
 * Adjacency List Graph Representation
 *
 * This file demonstrates both unweighted and weighted adjacency list implementations.
 * An adjacency list uses an array of lists where each vertex stores a list of its neighbors.
 *
 * Time Complexity:
 * - Add Edge: O(1)
 * - Remove Edge: O(V)
 * - Has Edge: O(V)
 * - Get Neighbors: O(1)
 *
 * Space Complexity: O(V + E)
 *
 * Best for: Sparse graphs with few edges, efficient neighbor iteration
 *
 * PHP Version: 8.0+
 */

/**
 * Unweighted Adjacency List Implementation
 *
 * Represents an unweighted graph using arrays to store neighbor lists.
 */
class AdjacencyList
{
    private array $list;
    private int $vertices;

    /**
     * Initialize adjacency list with given number of vertices
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
        $this->list = array_fill(0, $vertices, []);
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

        if (!in_array($to, $this->list[$from], true)) {
            $this->list[$from][] = $to;
        }
        if (!in_array($from, $this->list[$to], true)) {
            $this->list[$to][] = $from;
        }
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

        if (!in_array($to, $this->list[$from], true)) {
            $this->list[$from][] = $to;
        }
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

        $this->list[$from] = array_values(
            array_filter($this->list[$from], fn($v) => $v !== $to)
        );
        $this->list[$to] = array_values(
            array_filter($this->list[$to], fn($v) => $v !== $from)
        );
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

        return in_array($to, $this->list[$from], true);
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
        return $this->list[$vertex];
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
        return count($this->list[$vertex]);
    }

    /**
     * Get the total number of edges in the graph
     *
     * @return int Total edge count
     */
    public function getEdgeCount(): int
    {
        $count = 0;
        foreach ($this->list as $neighbors) {
            $count += count($neighbors);
        }
        return intdiv($count, 2); // Each undirected edge counted twice
    }

    /**
     * Print the adjacency list in a readable format
     */
    public function print(): void
    {
        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i: ";
            if (empty($this->list[$i])) {
                echo "(no neighbors)";
            } else {
                echo implode(' -> ', $this->list[$i]);
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
 * Weighted Adjacency List Implementation
 *
 * Represents a weighted graph where edges have associated weights.
 */
class WeightedAdjacencyList
{
    private array $list;
    private int $vertices;

    /**
     * Initialize weighted adjacency list
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
        $this->list = array_fill(0, $vertices, []);
    }

    /**
     * Add an undirected weighted edge
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @param int|float $weight Edge weight
     * @throws OutOfBoundsException if vertices are out of range
     * @throws InvalidArgumentException if weight is negative
     */
    public function addEdge(int $from, int $to, int|float $weight): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        if ($weight < 0) {
            throw new InvalidArgumentException("Weight cannot be negative");
        }

        // Add edge from -> to
        if (!$this->edgeExists($from, $to)) {
            $this->list[$from][] = ['vertex' => $to, 'weight' => $weight];
        }

        // Add edge to -> from (undirected)
        if (!$this->edgeExists($to, $from)) {
            $this->list[$to][] = ['vertex' => $from, 'weight' => $weight];
        }
    }

    /**
     * Add a directed weighted edge
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @param int|float $weight Edge weight
     * @throws OutOfBoundsException if vertices are out of range
     * @throws InvalidArgumentException if weight is negative
     */
    public function addDirectedEdge(int $from, int $to, int|float $weight): void
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        if ($weight < 0) {
            throw new InvalidArgumentException("Weight cannot be negative");
        }

        if (!$this->edgeExists($from, $to)) {
            $this->list[$from][] = ['vertex' => $to, 'weight' => $weight];
        }
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

        $this->list[$from] = array_values(
            array_filter($this->list[$from], fn($e) => $e['vertex'] !== $to)
        );
        $this->list[$to] = array_values(
            array_filter($this->list[$to], fn($e) => $e['vertex'] !== $from)
        );
    }

    /**
     * Get all neighbors with their weights
     *
     * @param int $vertex Vertex to get neighbors for
     * @return array Array of ['vertex' => int, 'weight' => int|float]
     * @throws OutOfBoundsException if vertex is out of range
     */
    public function getNeighbors(int $vertex): array
    {
        $this->validateVertex($vertex);
        return $this->list[$vertex];
    }

    /**
     * Get the weight of an edge
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @return int|float|null Edge weight or null if edge doesn't exist
     * @throws OutOfBoundsException if vertices are out of range
     */
    public function getWeight(int $from, int $to): int|float|null
    {
        $this->validateVertex($from);
        $this->validateVertex($to);

        foreach ($this->list[$from] as $edge) {
            if ($edge['vertex'] === $to) {
                return $edge['weight'];
            }
        }
        return null;
    }

    /**
     * Print the weighted adjacency list
     */
    public function print(): void
    {
        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i: ";
            if (empty($this->list[$i])) {
                echo "(no neighbors)";
            } else {
                $edges = array_map(
                    fn($e) => "{$e['vertex']}({$e['weight']})",
                    $this->list[$i]
                );
                echo implode(' -> ', $edges);
            }
            echo "\n";
        }
    }

    /**
     * Check if an edge exists
     *
     * @param int $from Source vertex
     * @param int $to Destination vertex
     * @return bool True if edge exists
     */
    private function edgeExists(int $from, int $to): bool
    {
        foreach ($this->list[$from] as $edge) {
            if ($edge['vertex'] === $to) {
                return true;
            }
        }
        return false;
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

echo "=== Unweighted Adjacency List Example ===\n\n";

try {
    // Create a social network graph
    $socialNetwork = new AdjacencyList(6);

    // Add friendships
    $socialNetwork->addEdge(0, 1); // Alice - Bob
    $socialNetwork->addEdge(0, 2); // Alice - Charlie
    $socialNetwork->addEdge(1, 2); // Bob - Charlie
    $socialNetwork->addEdge(1, 3); // Bob - David
    $socialNetwork->addEdge(2, 4); // Charlie - Eve
    $socialNetwork->addEdge(3, 4); // David - Eve
    $socialNetwork->addEdge(3, 5); // David - Frank

    echo "Social Network (friendships):\n";
    $socialNetwork->print();

    echo "\nNetwork statistics:\n";
    echo "Alice's friends (0): " . implode(', ', $socialNetwork->getNeighbors(0)) . "\n";
    echo "Bob's friend count: " . $socialNetwork->getDegree(1) . "\n";
    echo "Total friendships: " . $socialNetwork->getEdgeCount() . "\n";
    echo "Are Alice and David friends? " . ($socialNetwork->hasEdge(0, 3) ? 'Yes' : 'No') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Weighted Adjacency List Example ===\n\n";

try {
    // Create a flight network with costs
    $flightNetwork = new WeightedAdjacencyList(5);

    // Add flights with prices
    $flightNetwork->addEdge(0, 1, 250);  // NYC - Boston: $250
    $flightNetwork->addEdge(0, 2, 150);  // NYC - Philadelphia: $150
    $flightNetwork->addEdge(1, 3, 180);  // Boston - Chicago: $180
    $flightNetwork->addEdge(2, 3, 200);  // Philadelphia - Chicago: $200
    $flightNetwork->addEdge(2, 4, 120);  // Philadelphia - Miami: $120
    $flightNetwork->addEdge(3, 4, 300);  // Chicago - Miami: $300

    echo "Flight Network (prices in USD):\n";
    $flightNetwork->print();

    echo "\nFlight information:\n";
    echo "Flights from NYC (0):\n";
    foreach ($flightNetwork->getNeighbors(0) as $flight) {
        echo "  - To city {$flight['vertex']}: \${$flight['weight']}\n";
    }

    echo "\nFlight price NYC to Boston: $" . $flightNetwork->getWeight(0, 1) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Directed Graph Example ===\n\n";

try {
    // Create a task dependency graph
    $taskGraph = new AdjacencyList(5);

    // Add dependencies (directed edges)
    $taskGraph->addDirectedEdge(0, 1); // Task 0 must complete before Task 1
    $taskGraph->addDirectedEdge(0, 2); // Task 0 must complete before Task 2
    $taskGraph->addDirectedEdge(1, 3); // Task 1 must complete before Task 3
    $taskGraph->addDirectedEdge(2, 3); // Task 2 must complete before Task 3
    $taskGraph->addDirectedEdge(3, 4); // Task 3 must complete before Task 4

    echo "Task Dependencies:\n";
    $taskGraph->print();

    echo "\nTask 0 must complete before tasks: " . implode(', ', $taskGraph->getNeighbors(0)) . "\n";
    echo "Task 3 must complete before tasks: " . implode(', ', $taskGraph->getNeighbors(3)) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Edge Removal Example ===\n\n";

try {
    $graph = new AdjacencyList(4);
    $graph->addEdge(0, 1);
    $graph->addEdge(0, 2);
    $graph->addEdge(1, 2);
    $graph->addEdge(2, 3);

    echo "Original graph:\n";
    $graph->print();

    $graph->removeEdge(1, 2);

    echo "\nAfter removing edge 1-2:\n";
    $graph->print();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
