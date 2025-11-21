<?php

declare(strict_types=1);

/**
 * Hash Map Graph Representation
 *
 * This file demonstrates a graph implementation using associative arrays (hash maps)
 * with string identifiers instead of numeric indices. Perfect for real-world applications
 * where vertices have meaningful names (users, cities, web pages, etc.)
 *
 * Time Complexity:
 * - Add Vertex: O(1)
 * - Add Edge: O(1)
 * - Has Edge: O(1)
 * - Get Neighbors: O(1)
 *
 * Space Complexity: O(V + E)
 *
 * Best for: Graphs with named vertices, real-world applications
 *
 * PHP Version: 8.0+
 */

/**
 * Hash Map Graph Implementation
 *
 * Uses associative arrays to represent graphs with string vertex identifiers.
 */
class HashMapGraph
{
    private array $graph = [];

    /**
     * Add a vertex to the graph
     *
     * @param string $name Vertex name/identifier
     */
    public function addVertex(string $name): void
    {
        if (!isset($this->graph[$name])) {
            $this->graph[$name] = [];
        }
    }

    /**
     * Add an undirected edge with optional weight
     *
     * @param string $from Source vertex
     * @param string $to Destination vertex
     * @param int|float $weight Edge weight (default: 1)
     */
    public function addEdge(string $from, string $to, int|float $weight = 1): void
    {
        $this->addVertex($from);
        $this->addVertex($to);

        $this->graph[$from][$to] = $weight;
        $this->graph[$to][$from] = $weight;
    }

    /**
     * Add a directed edge with optional weight
     *
     * @param string $from Source vertex
     * @param string $to Destination vertex
     * @param int|float $weight Edge weight (default: 1)
     */
    public function addDirectedEdge(string $from, string $to, int|float $weight = 1): void
    {
        $this->addVertex($from);
        $this->addVertex($to);

        $this->graph[$from][$to] = $weight;
    }

    /**
     * Remove an edge between two vertices
     *
     * @param string $from Source vertex
     * @param string $to Destination vertex
     */
    public function removeEdge(string $from, string $to): void
    {
        if (isset($this->graph[$from][$to])) {
            unset($this->graph[$from][$to]);
        }
        if (isset($this->graph[$to][$from])) {
            unset($this->graph[$to][$from]);
        }
    }

    /**
     * Remove a vertex and all its edges
     *
     * @param string $name Vertex to remove
     */
    public function removeVertex(string $name): void
    {
        if (!isset($this->graph[$name])) {
            return;
        }

        // Remove all edges to this vertex
        foreach ($this->graph as $vertex => $neighbors) {
            if (isset($neighbors[$name])) {
                unset($this->graph[$vertex][$name]);
            }
        }

        // Remove the vertex itself
        unset($this->graph[$name]);
    }

    /**
     * Get all neighbors of a vertex
     *
     * @param string $vertex Vertex to get neighbors for
     * @return array Array of neighbor names
     */
    public function getNeighbors(string $vertex): array
    {
        return array_keys($this->graph[$vertex] ?? []);
    }

    /**
     * Get neighbors with their weights
     *
     * @param string $vertex Vertex to get neighbors for
     * @return array Associative array of [neighbor => weight]
     */
    public function getNeighborsWithWeights(string $vertex): array
    {
        return $this->graph[$vertex] ?? [];
    }

    /**
     * Get the weight of an edge
     *
     * @param string $from Source vertex
     * @param string $to Destination vertex
     * @return int|float|null Edge weight or null if edge doesn't exist
     */
    public function getWeight(string $from, string $to): int|float|null
    {
        return $this->graph[$from][$to] ?? null;
    }

    /**
     * Check if a vertex exists in the graph
     *
     * @param string $name Vertex name
     * @return bool True if vertex exists
     */
    public function hasVertex(string $name): bool
    {
        return isset($this->graph[$name]);
    }

    /**
     * Check if an edge exists between two vertices
     *
     * @param string $from Source vertex
     * @param string $to Destination vertex
     * @return bool True if edge exists
     */
    public function hasEdge(string $from, string $to): bool
    {
        return isset($this->graph[$from][$to]);
    }

    /**
     * Get all vertices in the graph
     *
     * @return array Array of all vertex names
     */
    public function getVertices(): array
    {
        return array_keys($this->graph);
    }

    /**
     * Get the number of vertices
     *
     * @return int Number of vertices
     */
    public function getVertexCount(): int
    {
        return count($this->graph);
    }

    /**
     * Get the degree of a vertex
     *
     * @param string $vertex Vertex name
     * @return int Number of edges connected to the vertex
     */
    public function getDegree(string $vertex): int
    {
        return count($this->graph[$vertex] ?? []);
    }

    /**
     * Print the graph in a readable format
     */
    public function print(): void
    {
        foreach ($this->graph as $vertex => $neighbors) {
            echo "$vertex: ";
            if (empty($neighbors)) {
                echo "(no connections)";
            } else {
                $edges = [];
                foreach ($neighbors as $neighbor => $weight) {
                    $edges[] = $weight === 1 ? $neighbor : "$neighbor($weight)";
                }
                echo implode(', ', $edges);
            }
            echo "\n";
        }
    }

    /**
     * Export graph as JSON
     *
     * @return string JSON representation of the graph
     */
    public function toJson(): string
    {
        return json_encode($this->graph, JSON_PRETTY_PRINT);
    }

    /**
     * Import graph from JSON
     *
     * @param string $json JSON string
     * @return bool True if successful
     */
    public function fromJson(string $json): bool
    {
        $data = json_decode($json, true);
        if ($data === null) {
            return false;
        }

        $this->graph = $data;
        return true;
    }
}

// ============================================================================
// Example Usage and Demonstrations
// ============================================================================

echo "=== Social Network Example ===\n\n";

try {
    $socialNetwork = new HashMapGraph();

    // Add users and their friendships
    $socialNetwork->addEdge('Alice', 'Bob');
    $socialNetwork->addEdge('Alice', 'Charlie');
    $socialNetwork->addEdge('Bob', 'David');
    $socialNetwork->addEdge('Charlie', 'David');
    $socialNetwork->addEdge('David', 'Eve');
    $socialNetwork->addEdge('Eve', 'Frank');

    echo "Social Network Structure:\n";
    $socialNetwork->print();

    echo "\nSocial Network Analysis:\n";
    echo "Total users: " . $socialNetwork->getVertexCount() . "\n";
    echo "Alice's friends: " . implode(', ', $socialNetwork->getNeighbors('Alice')) . "\n";
    echo "David's friend count: " . $socialNetwork->getDegree('David') . "\n";
    echo "Are Bob and Eve friends? " . ($socialNetwork->hasEdge('Bob', 'Eve') ? 'Yes' : 'No') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== City Road Network Example ===\n\n";

try {
    $roadNetwork = new HashMapGraph();

    // Add cities with distances
    $roadNetwork->addEdge('New York', 'Boston', 215);
    $roadNetwork->addEdge('New York', 'Philadelphia', 95);
    $roadNetwork->addEdge('Boston', 'Portland', 103);
    $roadNetwork->addEdge('Philadelphia', 'Washington DC', 140);
    $roadNetwork->addEdge('Philadelphia', 'Baltimore', 100);
    $roadNetwork->addEdge('Washington DC', 'Baltimore', 40);

    echo "Road Network (distances in miles):\n";
    $roadNetwork->print();

    echo "\nRoute Information:\n";
    echo "Distance New York to Philadelphia: " .
        $roadNetwork->getWeight('New York', 'Philadelphia') . " miles\n";

    echo "\nCities connected to Philadelphia:\n";
    $connections = $roadNetwork->getNeighborsWithWeights('Philadelphia');
    foreach ($connections as $city => $distance) {
        echo "  - $city: $distance miles\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Web Page Link Graph Example ===\n\n";

try {
    $webGraph = new HashMapGraph();

    // Add web pages and links (directed)
    $webGraph->addDirectedEdge('index.html', 'about.html');
    $webGraph->addDirectedEdge('index.html', 'contact.html');
    $webGraph->addDirectedEdge('index.html', 'products.html');
    $webGraph->addDirectedEdge('about.html', 'team.html');
    $webGraph->addDirectedEdge('products.html', 'contact.html');
    $webGraph->addDirectedEdge('team.html', 'contact.html');

    echo "Web Site Structure:\n";
    $webGraph->print();

    echo "\nLinks from index.html: " .
        implode(', ', $webGraph->getNeighbors('index.html')) . "\n";

    // Count incoming links (pages that link to contact.html)
    $incomingToContact = 0;
    foreach ($webGraph->getVertices() as $page) {
        if ($webGraph->hasEdge($page, 'contact.html')) {
            $incomingToContact++;
        }
    }
    echo "Pages linking to contact.html: $incomingToContact\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Package Dependency Graph Example ===\n\n";

try {
    $packages = new HashMapGraph();

    // Add package dependencies (directed)
    $packages->addDirectedEdge('myapp', 'symfony/http-kernel');
    $packages->addDirectedEdge('myapp', 'doctrine/orm');
    $packages->addDirectedEdge('symfony/http-kernel', 'symfony/event-dispatcher');
    $packages->addDirectedEdge('symfony/http-kernel', 'psr/log');
    $packages->addDirectedEdge('doctrine/orm', 'doctrine/dbal');
    $packages->addDirectedEdge('doctrine/dbal', 'doctrine/common');

    echo "Package Dependencies:\n";
    $packages->print();

    echo "\nDirect dependencies of 'myapp': " .
        implode(', ', $packages->getNeighbors('myapp')) . "\n";

    echo "Direct dependencies of 'symfony/http-kernel': " .
        implode(', ', $packages->getNeighbors('symfony/http-kernel')) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== JSON Export/Import Example ===\n\n";

try {
    $graph = new HashMapGraph();
    $graph->addEdge('A', 'B', 5);
    $graph->addEdge('B', 'C', 3);
    $graph->addEdge('A', 'C', 7);

    echo "Original graph:\n";
    $graph->print();

    $json = $graph->toJson();
    echo "\nExported to JSON:\n$json\n";

    // Import into new graph
    $importedGraph = new HashMapGraph();
    $importedGraph->fromJson($json);

    echo "\nImported graph:\n";
    $importedGraph->print();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "=== Vertex Removal Example ===\n\n";

try {
    $graph = new HashMapGraph();
    $graph->addEdge('A', 'B');
    $graph->addEdge('B', 'C');
    $graph->addEdge('C', 'D');
    $graph->addEdge('B', 'D');

    echo "Original graph:\n";
    $graph->print();

    $graph->removeVertex('B');

    echo "\nAfter removing vertex 'B':\n";
    $graph->print();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
