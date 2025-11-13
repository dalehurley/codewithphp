---
title: "Graph Representations"
description: "Learn the fundamental ways to represent graphs in code including adjacency matrix, adjacency list, and edge list, with practical implementations in PHP"
series: "php-algorithms"
chapter: 21
order: 21
difficulty: "intermediate"
prerequisites: ["Arrays & Dynamic Arrays", "Hash Tables & Hash Functions"]
---

# Graph Representations

Graphs are powerful data structures that model relationships between objects. Understanding how to represent graphs efficiently is fundamental to implementing graph algorithms.

## What is a Graph?

A graph G = (V, E) consists of:
- **V**: Set of vertices (nodes)
- **E**: Set of edges (connections between vertices)

### Graph Types

```php
<?php

// Undirected Graph: edges have no direction
// Example: Facebook friendships (if A is friends with B, B is friends with A)
//     A --- B
//     |     |
//     C --- D

// Directed Graph (Digraph): edges have direction
// Example: Twitter follows (A follows B doesn't mean B follows A)
//     A --> B
//     ↑     ↓
//     C <-- D

// Weighted Graph: edges have weights/costs
// Example: Road network (distances between cities)
//     A --5-- B
//     |       |
//     3       2
//     |       |
//     C --7-- D

// Unweighted Graph: all edges have equal weight
//     A --- B
//     |     |
//     C --- D
```

### Graph Properties

```php
<?php

class GraphProperties
{
    // Dense graph: many edges (|E| ≈ |V|²)
    // Example: Social network where most people know each other
    public function isDense(int $vertices, int $edges): bool
    {
        return $edges > ($vertices * ($vertices - 1)) / 4;
    }

    // Sparse graph: few edges (|E| ≈ |V|)
    // Example: Road network (each city connects to few others)
    public function isSparse(int $vertices, int $edges): bool
    {
        return $edges < $vertices * 2;
    }

    // Connected graph: path exists between every pair of vertices
    // Cyclic graph: contains at least one cycle
    // Acyclic graph: no cycles (DAG = Directed Acyclic Graph)
}
```

## Adjacency Matrix

A 2D array where `matrix[i][j]` represents the edge from vertex i to vertex j.

### Unweighted Adjacency Matrix

```php
<?php

class AdjacencyMatrix
{
    private array $matrix;
    private int $vertices;

    public function __construct(int $vertices)
    {
        $this->vertices = $vertices;
        $this->matrix = array_fill(0, $vertices, array_fill(0, $vertices, 0));
    }

    // Add edge (undirected graph)
    public function addEdge(int $from, int $to): void
    {
        $this->matrix[$from][$to] = 1;
        $this->matrix[$to][$from] = 1;  // For undirected graph
    }

    // Add directed edge
    public function addDirectedEdge(int $from, int $to): void
    {
        $this->matrix[$from][$to] = 1;
    }

    // Remove edge
    public function removeEdge(int $from, int $to): void
    {
        $this->matrix[$from][$to] = 0;
        $this->matrix[$to][$from] = 0;
    }

    // Check if edge exists
    public function hasEdge(int $from, int $to): bool
    {
        return $this->matrix[$from][$to] === 1;
    }

    // Get all neighbors of a vertex
    public function getNeighbors(int $vertex): array
    {
        $neighbors = [];
        for ($i = 0; $i < $this->vertices; $i++) {
            if ($this->matrix[$vertex][$i] === 1) {
                $neighbors[] = $i;
            }
        }
        return $neighbors;
    }

    // Get degree of a vertex (number of edges)
    public function getDegree(int $vertex): int
    {
        return array_sum($this->matrix[$vertex]);
    }

    // Print matrix
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
}

// Example usage
$graph = new AdjacencyMatrix(4);
$graph->addEdge(0, 1);
$graph->addEdge(0, 2);
$graph->addEdge(1, 2);
$graph->addEdge(2, 3);

$graph->print();
//    0 1 2 3
// 0: 0 1 1 0
// 1: 1 0 1 0
// 2: 1 1 0 1
// 3: 0 0 1 0

echo "Neighbors of 2: " . implode(', ', $graph->getNeighbors(2)) . "\n";  // 0, 1, 3
echo "Degree of 2: " . $graph->getDegree(2) . "\n";  // 3
```

### Weighted Adjacency Matrix

```php
<?php

class WeightedAdjacencyMatrix
{
    private array $matrix;
    private int $vertices;
    private const INF = PHP_INT_MAX;

    public function __construct(int $vertices)
    {
        $this->vertices = $vertices;
        // Initialize with infinity (no edge)
        $this->matrix = array_fill(0, $vertices, array_fill(0, $vertices, self::INF));

        // Distance from vertex to itself is 0
        for ($i = 0; $i < $vertices; $i++) {
            $this->matrix[$i][$i] = 0;
        }
    }

    // Add weighted edge
    public function addEdge(int $from, int $to, int $weight): void
    {
        $this->matrix[$from][$to] = $weight;
        $this->matrix[$to][$from] = $weight;  // For undirected graph
    }

    // Add directed weighted edge
    public function addDirectedEdge(int $from, int $to, int $weight): void
    {
        $this->matrix[$from][$to] = $weight;
    }

    // Get edge weight
    public function getWeight(int $from, int $to): int
    {
        return $this->matrix[$from][$to];
    }

    // Get neighbors with weights
    public function getNeighborsWithWeights(int $vertex): array
    {
        $neighbors = [];
        for ($i = 0; $i < $this->vertices; $i++) {
            if ($this->matrix[$vertex][$i] !== self::INF && $this->matrix[$vertex][$i] !== 0) {
                $neighbors[] = ['vertex' => $i, 'weight' => $this->matrix[$vertex][$i]];
            }
        }
        return $neighbors;
    }

    // Print matrix
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
                    echo "  ∞ ";
                } else {
                    printf("%4d", $this->matrix[$i][$j]);
                }
            }
            echo "\n";
        }
    }
}

// Example usage
$graph = new WeightedAdjacencyMatrix(4);
$graph->addEdge(0, 1, 5);
$graph->addEdge(0, 2, 3);
$graph->addEdge(1, 2, 2);
$graph->addEdge(2, 3, 7);

$graph->print();
//       0   1   2   3
//    0:   0   5   3  ∞
//    1:   5   0   2  ∞
//    2:   3   2   0   7
//    3:  ∞  ∞   7   0

print_r($graph->getNeighborsWithWeights(2));
// [['vertex' => 0, 'weight' => 3],
//  ['vertex' => 1, 'weight' => 2],
//  ['vertex' => 3, 'weight' => 7]]
```

## Adjacency List

An array of lists where each vertex has a list of its neighbors. More space-efficient for sparse graphs.

### Unweighted Adjacency List

```php
<?php

class AdjacencyList
{
    private array $list;
    private int $vertices;

    public function __construct(int $vertices)
    {
        $this->vertices = $vertices;
        $this->list = array_fill(0, $vertices, []);
    }

    // Add edge (undirected graph)
    public function addEdge(int $from, int $to): void
    {
        if (!in_array($to, $this->list[$from])) {
            $this->list[$from][] = $to;
        }
        if (!in_array($from, $this->list[$to])) {
            $this->list[$to][] = $from;
        }
    }

    // Add directed edge
    public function addDirectedEdge(int $from, int $to): void
    {
        if (!in_array($to, $this->list[$from])) {
            $this->list[$from][] = $to;
        }
    }

    // Remove edge
    public function removeEdge(int $from, int $to): void
    {
        $this->list[$from] = array_values(
            array_filter($this->list[$from], fn($v) => $v !== $to)
        );
        $this->list[$to] = array_values(
            array_filter($this->list[$to], fn($v) => $v !== $from)
        );
    }

    // Check if edge exists
    public function hasEdge(int $from, int $to): bool
    {
        return in_array($to, $this->list[$from]);
    }

    // Get neighbors
    public function getNeighbors(int $vertex): array
    {
        return $this->list[$vertex];
    }

    // Get degree
    public function getDegree(int $vertex): int
    {
        return count($this->list[$vertex]);
    }

    // Get total edges
    public function getEdgeCount(): int
    {
        $count = 0;
        foreach ($this->list as $neighbors) {
            $count += count($neighbors);
        }
        return $count / 2;  // Each edge counted twice in undirected graph
    }

    // Print list
    public function print(): void
    {
        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i: " . implode(' -> ', $this->list[$i]) . "\n";
        }
    }
}

// Example usage
$graph = new AdjacencyList(4);
$graph->addEdge(0, 1);
$graph->addEdge(0, 2);
$graph->addEdge(1, 2);
$graph->addEdge(2, 3);

$graph->print();
// 0: 1 -> 2
// 1: 0 -> 2
// 2: 0 -> 1 -> 3
// 3: 2

echo "Neighbors of 2: " . implode(', ', $graph->getNeighbors(2)) . "\n";  // 0, 1, 3
```

### Weighted Adjacency List

```php
<?php

class WeightedAdjacencyList
{
    private array $list;
    private int $vertices;

    public function __construct(int $vertices)
    {
        $this->vertices = $vertices;
        $this->list = array_fill(0, $vertices, []);
    }

    // Add weighted edge
    public function addEdge(int $from, int $to, int $weight): void
    {
        $this->list[$from][] = ['vertex' => $to, 'weight' => $weight];
        $this->list[$to][] = ['vertex' => $from, 'weight' => $weight];
    }

    // Add directed weighted edge
    public function addDirectedEdge(int $from, int $to, int $weight): void
    {
        $this->list[$from][] = ['vertex' => $to, 'weight' => $weight];
    }

    // Remove edge
    public function removeEdge(int $from, int $to): void
    {
        $this->list[$from] = array_values(
            array_filter($this->list[$from], fn($e) => $e['vertex'] !== $to)
        );
        $this->list[$to] = array_values(
            array_filter($this->list[$to], fn($e) => $e['vertex'] !== $from)
        );
    }

    // Get neighbors with weights
    public function getNeighbors(int $vertex): array
    {
        return $this->list[$vertex];
    }

    // Get edge weight
    public function getWeight(int $from, int $to): ?int
    {
        foreach ($this->list[$from] as $edge) {
            if ($edge['vertex'] === $to) {
                return $edge['weight'];
            }
        }
        return null;
    }

    // Print list
    public function print(): void
    {
        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i: ";
            $edges = array_map(
                fn($e) => "{$e['vertex']}({$e['weight']})",
                $this->list[$i]
            );
            echo implode(' -> ', $edges) . "\n";
        }
    }
}

// Example usage
$graph = new WeightedAdjacencyList(4);
$graph->addEdge(0, 1, 5);
$graph->addEdge(0, 2, 3);
$graph->addEdge(1, 2, 2);
$graph->addEdge(2, 3, 7);

$graph->print();
// 0: 1(5) -> 2(3)
// 1: 0(5) -> 2(2)
// 2: 0(3) -> 1(2) -> 3(7)
// 3: 2(7)

echo "Weight from 0 to 1: " . $graph->getWeight(0, 1) . "\n";  // 5
```

## Edge List

A simple list of all edges in the graph. Efficient for algorithms that process all edges.

```php
<?php

class Edge
{
    public function __construct(
        public int $from,
        public int $to,
        public int $weight = 1
    ) {}
}

class EdgeList
{
    private array $edges = [];
    private int $vertices;

    public function __construct(int $vertices)
    {
        $this->vertices = $vertices;
    }

    // Add edge
    public function addEdge(int $from, int $to, int $weight = 1): void
    {
        $this->edges[] = new Edge($from, $to, $weight);
    }

    // Add undirected edge
    public function addUndirectedEdge(int $from, int $to, int $weight = 1): void
    {
        $this->edges[] = new Edge($from, $to, $weight);
        $this->edges[] = new Edge($to, $from, $weight);
    }

    // Get all edges
    public function getEdges(): array
    {
        return $this->edges;
    }

    // Get edges from a vertex
    public function getEdgesFrom(int $vertex): array
    {
        return array_filter($this->edges, fn($e) => $e->from === $vertex);
    }

    // Sort edges by weight (useful for Kruskal's algorithm)
    public function sortByWeight(): void
    {
        usort($this->edges, fn($a, $b) => $a->weight <=> $b->weight);
    }

    // Get edge count
    public function getEdgeCount(): int
    {
        return count($this->edges);
    }

    // Print edges
    public function print(): void
    {
        foreach ($this->edges as $i => $edge) {
            echo "Edge $i: {$edge->from} -> {$edge->to} (weight: {$edge->weight})\n";
        }
    }
}

// Example usage
$graph = new EdgeList(4);
$graph->addUndirectedEdge(0, 1, 5);
$graph->addUndirectedEdge(0, 2, 3);
$graph->addUndirectedEdge(1, 2, 2);
$graph->addUndirectedEdge(2, 3, 7);

$graph->print();
// Edge 0: 0 -> 1 (weight: 5)
// Edge 1: 1 -> 0 (weight: 5)
// Edge 2: 0 -> 2 (weight: 3)
// Edge 3: 2 -> 0 (weight: 3)
// Edge 4: 1 -> 2 (weight: 2)
// Edge 5: 2 -> 1 (weight: 2)
// Edge 6: 2 -> 3 (weight: 7)
// Edge 7: 3 -> 2 (weight: 7)

$graph->sortByWeight();
echo "\nAfter sorting by weight:\n";
$graph->print();
```

## Hash Map Representation

Using associative arrays for named vertices (non-integer identifiers).

```php
<?php

class HashMapGraph
{
    private array $graph = [];

    // Add vertex
    public function addVertex(string $name): void
    {
        if (!isset($this->graph[$name])) {
            $this->graph[$name] = [];
        }
    }

    // Add edge
    public function addEdge(string $from, string $to, int $weight = 1): void
    {
        $this->addVertex($from);
        $this->addVertex($to);

        $this->graph[$from][$to] = $weight;
        $this->graph[$to][$from] = $weight;  // For undirected
    }

    // Add directed edge
    public function addDirectedEdge(string $from, string $to, int $weight = 1): void
    {
        $this->addVertex($from);
        $this->addVertex($to);

        $this->graph[$from][$to] = $weight;
    }

    // Get neighbors
    public function getNeighbors(string $vertex): array
    {
        return array_keys($this->graph[$vertex] ?? []);
    }

    // Get weight
    public function getWeight(string $from, string $to): ?int
    {
        return $this->graph[$from][$to] ?? null;
    }

    // Has vertex
    public function hasVertex(string $name): bool
    {
        return isset($this->graph[$name]);
    }

    // Has edge
    public function hasEdge(string $from, string $to): bool
    {
        return isset($this->graph[$from][$to]);
    }

    // Get all vertices
    public function getVertices(): array
    {
        return array_keys($this->graph);
    }

    // Print graph
    public function print(): void
    {
        foreach ($this->graph as $vertex => $neighbors) {
            echo "$vertex: ";
            $edges = [];
            foreach ($neighbors as $neighbor => $weight) {
                $edges[] = "$neighbor($weight)";
            }
            echo implode(', ', $edges) . "\n";
        }
    }
}

// Example usage - Social network
$social = new HashMapGraph();
$social->addEdge('Alice', 'Bob', 1);
$social->addEdge('Alice', 'Charlie', 1);
$social->addEdge('Bob', 'David', 1);
$social->addEdge('Charlie', 'David', 1);

$social->print();
// Alice: Bob(1), Charlie(1)
// Bob: Alice(1), David(1)
// Charlie: Alice(1), David(1)
// David: Bob(1), Charlie(1)

echo "Alice's friends: " . implode(', ', $social->getNeighbors('Alice')) . "\n";
// Alice's friends: Bob, Charlie

// Example usage - City road network
$roads = new HashMapGraph();
$roads->addEdge('NYC', 'Boston', 215);
$roads->addEdge('NYC', 'Philadelphia', 95);
$roads->addEdge('Boston', 'Portland', 103);
$roads->addEdge('Philadelphia', 'Washington', 140);

$roads->print();
// NYC: Boston(215), Philadelphia(95)
// Boston: NYC(215), Portland(103)
// Philadelphia: NYC(95), Washington(140)
// Washington: Philadelphia(140)
```

## Visual Graph Examples

Understanding graph structure through ASCII visualization:

```php
<?php

class GraphVisualizer
{
    // Visualize small graphs with ASCII art
    public function visualizeUndirected(): void
    {
        echo "Undirected Graph Example:\n";
        echo "\n";
        echo "     A --- B\n";
        echo "     |  \\  |\n";
        echo "     |   \\ |\n";
        echo "     C --- D\n";
        echo "\n";
        echo "Edges: A-B, A-C, A-D, B-D, C-D\n";
        echo "This forms a complete graph K4 (all vertices connected)\n\n";
    }

    public function visualizeDirected(): void
    {
        echo "Directed Graph Example (Dependency Graph):\n";
        echo "\n";
        echo "     A --> B --> D\n";
        echo "     |     |\n";
        echo "     v     v\n";
        echo "     C --> E\n";
        echo "\n";
        echo "Edges: A→B, A→C, B→D, B→E, C→E\n";
        echo "A depends on nothing, E depends on B and C\n\n";
    }

    public function visualizeWeighted(): void
    {
        echo "Weighted Graph Example (Road Network):\n";
        echo "\n";
        echo "         NYC\n";
        echo "       95/   \\215\n";
        echo "       /       \\\n";
        echo "     Philly   Boston\n";
        echo "       |  \\     |\n";
        echo "    140|   100  |103\n";
        echo "       |     \\  |\n";
        echo "       DC    Baltimore\n";
        echo "         \\     /\n";
        echo "          \\ 40/\n";
        echo "\n";
        echo "Edge weights represent distances in miles\n\n";
    }

    public function visualizeTree(): void
    {
        echo "Tree (Special Graph - No Cycles):\n";
        echo "\n";
        echo "           1\n";
        echo "         /   \\\n";
        echo "        2     3\n";
        echo "       / \\   / \\\n";
        echo "      4   5 6   7\n";
        echo "\n";
        echo "Properties: |V| = 7, |E| = 6 (always |E| = |V| - 1 for trees)\n";
        echo "Connected and acyclic\n\n";
    }

    public function visualizeBipartite(): void
    {
        echo "Bipartite Graph Example (Students-Courses):\n";
        echo "\n";
        echo "   Students        Courses\n";
        echo "     Alice -------- Math\n";
        echo "       |       X   /\n";
        echo "       |      / \\ /\n";
        echo "     Bob  --------  Physics\n";
        echo "       |  \\      X\n";
        echo "       |   \\    / \\\n";
        echo "   Charlie ------- Chemistry\n";
        echo "\n";
        echo "Two disjoint sets: no edges within same set\n\n";
    }
}

$viz = new GraphVisualizer();
$viz->visualizeUndirected();
$viz->visualizeDirected();
$viz->visualizeWeighted();
$viz->visualizeTree();
$viz->visualizeBipartite();
```

## Performance Benchmarks

Comparing representation performance across different operations:

```php
<?php

class GraphBenchmark
{
    // Benchmark different representations
    public function benchmarkOperations(int $vertices, int $edges): array
    {
        $results = [];

        // Generate random graph data
        $edgeList = $this->generateRandomEdges($vertices, $edges);

        // Adjacency Matrix
        $start = microtime(true);
        $matrix = $this->buildMatrix($vertices, $edgeList);
        $matrixBuildTime = microtime(true) - $start;

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $this->matrixHasEdge($matrix, rand(0, $vertices-1), rand(0, $vertices-1));
        }
        $matrixLookupTime = (microtime(true) - $start) / 1000;

        // Adjacency List
        $start = microtime(true);
        $list = $this->buildList($vertices, $edgeList);
        $listBuildTime = microtime(true) - $start;

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $this->listHasEdge($list, rand(0, $vertices-1), rand(0, $vertices-1));
        }
        $listLookupTime = (microtime(true) - $start) / 1000;

        return [
            'vertices' => $vertices,
            'edges' => $edges,
            'density' => round($edges / ($vertices * $vertices), 4),
            'matrix' => [
                'build_time' => round($matrixBuildTime * 1000, 2) . 'ms',
                'lookup_time' => round($matrixLookupTime * 1000000, 2) . 'µs',
                'memory' => $this->formatBytes($vertices * $vertices * 8)
            ],
            'list' => [
                'build_time' => round($listBuildTime * 1000, 2) . 'ms',
                'lookup_time' => round($listLookupTime * 1000000, 2) . 'µs',
                'memory' => $this->formatBytes(($vertices + $edges * 2) * 8)
            ]
        ];
    }

    private function generateRandomEdges(int $vertices, int $edges): array
    {
        $edgeList = [];
        $edgeSet = [];

        while (count($edgeList) < $edges) {
            $from = rand(0, $vertices - 1);
            $to = rand(0, $vertices - 1);

            if ($from !== $to) {
                $key = min($from, $to) . '-' . max($from, $to);
                if (!isset($edgeSet[$key])) {
                    $edgeSet[$key] = true;
                    $edgeList[] = [$from, $to];
                }
            }
        }

        return $edgeList;
    }

    private function buildMatrix(int $vertices, array $edges): array
    {
        $matrix = array_fill(0, $vertices, array_fill(0, $vertices, 0));
        foreach ($edges as [$from, $to]) {
            $matrix[$from][$to] = 1;
            $matrix[$to][$from] = 1;
        }
        return $matrix;
    }

    private function buildList(int $vertices, array $edges): array
    {
        $list = array_fill(0, $vertices, []);
        foreach ($edges as [$from, $to]) {
            $list[$from][] = $to;
            $list[$to][] = $from;
        }
        return $list;
    }

    private function matrixHasEdge(array $matrix, int $from, int $to): bool
    {
        return $matrix[$from][$to] === 1;
    }

    private function listHasEdge(array $list, int $from, int $to): bool
    {
        return in_array($to, $list[$from] ?? []);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}

// Run benchmarks
$benchmark = new GraphBenchmark();

echo "=== Sparse Graph (Social Network-like) ===\n";
$sparse = $benchmark->benchmarkOperations(1000, 5000);
print_r($sparse);
/*
 * Sparse: 1000 vertices, 5000 edges (density: 0.005)
 * Matrix: Build ~8ms, Lookup ~0.01µs, Memory ~7.63MB
 * List: Build ~2ms, Lookup ~5µs, Memory ~117KB
 * Winner: List (much less memory, fast enough lookups)
 */

echo "\n=== Dense Graph (Complete Graph-like) ===\n";
$dense = $benchmark->benchmarkOperations(200, 15000);
print_r($dense);
/*
 * Dense: 200 vertices, 15000 edges (density: 0.375)
 * Matrix: Build ~3ms, Lookup ~0.01µs, Memory ~312KB
 * List: Build ~6ms, Lookup ~20µs, Memory ~469KB
 * Winner: Matrix (constant time lookups, reasonable memory)
 */
```

## Real-World Graph Problems

### 1. Social Network Analysis

Complete implementation with friend recommendations and influence analysis:

```php
<?php

class SocialNetworkAdvanced
{
    private array $graph = [];
    private array $userProfiles = [];

    public function addUser(string $userId, array $profile): void
    {
        $this->graph[$userId] = [];
        $this->userProfiles[$userId] = $profile;
    }

    public function addFriendship(string $user1, string $user2): void
    {
        $this->graph[$user1][] = $user2;
        $this->graph[$user2][] = $user1;
    }

    // Find mutual friends
    public function getMutualFriends(string $user1, string $user2): array
    {
        $friends1 = array_flip($this->graph[$user1] ?? []);
        $friends2 = array_flip($this->graph[$user2] ?? []);
        return array_keys(array_intersect_key($friends1, $friends2));
    }

    // Friend recommendations (friends of friends)
    public function recommendFriends(string $userId, int $limit = 5): array
    {
        $recommendations = [];
        $friends = array_flip($this->graph[$userId] ?? []);

        // Look at friends of friends
        foreach ($this->graph[$userId] ?? [] as $friend) {
            foreach ($this->graph[$friend] ?? [] as $fof) {
                // Skip if already friends or self
                if ($fof === $userId || isset($friends[$fof])) {
                    continue;
                }

                // Count mutual connections
                $mutualCount = count($this->getMutualFriends($userId, $fof));
                $recommendations[$fof] = [
                    'user' => $fof,
                    'mutual_friends' => $mutualCount,
                    'connection' => $friend
                ];
            }
        }

        // Sort by mutual friends count
        usort($recommendations, fn($a, $b) => $b['mutual_friends'] <=> $a['mutual_friends']);

        return array_slice($recommendations, 0, $limit);
    }

    // Calculate network centrality (influence)
    public function calculateCentrality(string $userId): array
    {
        // Degree centrality
        $degreeCentrality = count($this->graph[$userId] ?? []);

        // Closeness centrality (average distance to others)
        $distances = $this->bfsDistances($userId);
        $totalDistance = array_sum($distances);
        $reachable = count($distances) - 1; // Exclude self
        $closenessCentrality = $reachable > 0 ? $reachable / $totalDistance : 0;

        return [
            'user' => $userId,
            'degree_centrality' => $degreeCentrality,
            'closeness_centrality' => round($closenessCentrality, 4),
            'friend_count' => $degreeCentrality,
            'influence_score' => round($degreeCentrality * $closenessCentrality, 2)
        ];
    }

    private function bfsDistances(string $start): array
    {
        $distances = [$start => 0];
        $queue = [$start];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $currentDist = $distances[$current];

            foreach ($this->graph[$current] ?? [] as $neighbor) {
                if (!isset($distances[$neighbor])) {
                    $distances[$neighbor] = $currentDist + 1;
                    $queue[] = $neighbor;
                }
            }
        }

        return $distances;
    }

    // Find communities (connected components)
    public function findCommunities(): array
    {
        $visited = [];
        $communities = [];

        foreach (array_keys($this->graph) as $user) {
            if (!isset($visited[$user])) {
                $community = [];
                $this->dfsCommunity($user, $visited, $community);
                $communities[] = $community;
            }
        }

        return $communities;
    }

    private function dfsCommunity(string $user, array &$visited, array &$community): void
    {
        $visited[$user] = true;
        $community[] = $user;

        foreach ($this->graph[$user] ?? [] as $friend) {
            if (!isset($visited[$friend])) {
                $this->dfsCommunity($friend, $visited, $community);
            }
        }
    }
}

// Example usage
$network = new SocialNetworkAdvanced();

// Build network
$users = ['Alice', 'Bob', 'Charlie', 'David', 'Eve', 'Frank', 'Grace'];
foreach ($users as $user) {
    $network->addUser($user, ['name' => $user]);
}

// Add friendships
$friendships = [
    ['Alice', 'Bob'], ['Alice', 'Charlie'], ['Bob', 'David'],
    ['Charlie', 'David'], ['David', 'Eve'], ['Frank', 'Grace']
];

foreach ($friendships as [$u1, $u2]) {
    $network->addFriendship($u1, $u2);
}

// Analyze network
echo "Friend Recommendations for Alice:\n";
print_r($network->recommendFriends('Alice', 3));
/*
 * [
 *   ['user' => 'David', 'mutual_friends' => 2, 'connection' => 'Bob'],
 *   ['user' => 'Eve', 'mutual_friends' => 1, 'connection' => 'David'],
 * ]
 */

echo "\nCentrality Analysis for David:\n";
print_r($network->calculateCentrality('David'));
/*
 * [
 *   'user' => 'David',
 *   'degree_centrality' => 3,  // Most connected
 *   'closeness_centrality' => 0.8333,
 *   'influence_score' => 2.5
 * ]
 */

echo "\nCommunities:\n";
print_r($network->findCommunities());
/*
 * [
 *   ['Alice', 'Bob', 'Charlie', 'David', 'Eve'],  // Main community
 *   ['Frank', 'Grace']  // Separate community
 * ]
 */
```

### 2. Package Dependency Resolution

Advanced dependency manager with cycle detection and build order:

```php
<?php

class PackageManager
{
    private array $dependencies = [];  // package => [dependencies]
    private array $versions = [];

    public function addPackage(string $package, string $version): void
    {
        $this->dependencies[$package] = [];
        $this->versions[$package] = $version;
    }

    public function addDependency(string $package, string $dependency, string $versionConstraint = '*'): void
    {
        $this->dependencies[$package][] = [
            'package' => $dependency,
            'version' => $versionConstraint
        ];
    }

    // Get installation order (topological sort)
    public function getInstallOrder(string $package): ?array
    {
        // Check for cycles first
        if ($this->hasCycle($package)) {
            throw new RuntimeException("Circular dependency detected involving: $package");
        }

        $order = [];
        $visited = [];
        $this->topologicalSort($package, $visited, $order);

        return array_reverse($order);
    }

    private function topologicalSort(string $package, array &$visited, array &$order): void
    {
        if (isset($visited[$package])) {
            return;
        }

        $visited[$package] = true;

        // Visit dependencies first
        foreach ($this->dependencies[$package] ?? [] as $dep) {
            $depPackage = $dep['package'];
            if (!isset($visited[$depPackage])) {
                $this->topologicalSort($depPackage, $visited, $order);
            }
        }

        // Add current package after dependencies
        $order[] = $package;
    }

    private function hasCycle(string $start): bool
    {
        $visited = [];
        $recursionStack = [];
        return $this->detectCycle($start, $visited, $recursionStack);
    }

    private function detectCycle(string $package, array &$visited, array &$recStack): bool
    {
        $visited[$package] = true;
        $recStack[$package] = true;

        foreach ($this->dependencies[$package] ?? [] as $dep) {
            $depPackage = $dep['package'];

            if (!isset($visited[$depPackage])) {
                if ($this->detectCycle($depPackage, $visited, $recStack)) {
                    return true;
                }
            } elseif (isset($recStack[$depPackage])) {
                return true;  // Cycle detected
            }
        }

        unset($recStack[$package]);
        return false;
    }

    // Get all transitive dependencies
    public function getAllDependencies(string $package): array
    {
        $allDeps = [];
        $visited = [];
        $this->collectDependencies($package, $visited, $allDeps);
        return array_values(array_unique($allDeps));
    }

    private function collectDependencies(string $package, array &$visited, array &$allDeps): void
    {
        if (isset($visited[$package])) {
            return;
        }

        $visited[$package] = true;

        foreach ($this->dependencies[$package] ?? [] as $dep) {
            $depPackage = $dep['package'];
            $allDeps[] = $depPackage;
            $this->collectDependencies($depPackage, $visited, $allDeps);
        }
    }

    // Visualize dependency tree
    public function visualizeDependencyTree(string $package, int $indent = 0): string
    {
        $output = str_repeat('  ', $indent) . '├─ ' . $package;
        if (isset($this->versions[$package])) {
            $output .= ' (' . $this->versions[$package] . ')';
        }
        $output .= "\n";

        foreach ($this->dependencies[$package] ?? [] as $dep) {
            $depPackage = $dep['package'];
            $output .= $this->visualizeDependencyTree($depPackage, $indent + 1);
        }

        return $output;
    }
}

// Example: PHP Composer-like dependencies
$manager = new PackageManager();

// Add packages
$manager->addPackage('myapp', '1.0.0');
$manager->addPackage('symfony/http-kernel', '5.4');
$manager->addPackage('symfony/event-dispatcher', '5.4');
$manager->addPackage('symfony/service-container', '5.4');
$manager->addPackage('psr/log', '1.0');
$manager->addPackage('doctrine/orm', '2.11');
$manager->addPackage('doctrine/dbal', '3.3');
$manager->addPackage('doctrine/common', '3.3');

// Add dependencies
$manager->addDependency('myapp', 'symfony/http-kernel');
$manager->addDependency('myapp', 'doctrine/orm');
$manager->addDependency('symfony/http-kernel', 'symfony/event-dispatcher');
$manager->addDependency('symfony/http-kernel', 'psr/log');
$manager->addDependency('symfony/event-dispatcher', 'symfony/service-container');
$manager->addDependency('doctrine/orm', 'doctrine/dbal');
$manager->addDependency('doctrine/dbal', 'doctrine/common');

// Get install order
echo "Installation order for 'myapp':\n";
$order = $manager->getInstallOrder('myapp');
foreach ($order as $i => $package) {
    echo ($i + 1) . ". $package\n";
}
/*
 * 1. symfony/service-container
 * 2. symfony/event-dispatcher
 * 3. psr/log
 * 4. symfony/http-kernel
 * 5. doctrine/common
 * 6. doctrine/dbal
 * 7. doctrine/orm
 * 8. myapp
 */

echo "\nDependency tree:\n";
echo $manager->visualizeDependencyTree('myapp');
/*
 * ├─ myapp (1.0.0)
 *   ├─ symfony/http-kernel (5.4)
 *     ├─ symfony/event-dispatcher (5.4)
 *       ├─ symfony/service-container (5.4)
 *     ├─ psr/log (1.0)
 *   ├─ doctrine/orm (2.11)
 *     ├─ doctrine/dbal (3.3)
 *       ├─ doctrine/common (3.3)
 */
```

## Choosing the Right Representation

```php
<?php

class GraphRepresentationGuide
{
    public function chooseRepresentation(
        int $vertices,
        int $edges,
        bool $weighted,
        bool $directed
    ): string {
        $isDense = $edges > ($vertices * ($vertices - 1)) / 4;

        // Decision matrix
        if ($isDense) {
            return "Adjacency Matrix - Dense graph benefits from O(1) edge lookup";
        }

        if (!$weighted && !$directed) {
            return "Adjacency List - Space efficient for sparse graphs, easy neighbor iteration";
        }

        if ($weighted) {
            return "Weighted Adjacency List - Stores weights efficiently, good for Dijkstra's";
        }

        return "Adjacency List - Default choice for most graph algorithms";
    }

    public function getComplexities(): array
    {
        return [
            'Adjacency Matrix' => [
                'Space' => 'O(V²)',
                'Add Edge' => 'O(1)',
                'Remove Edge' => 'O(1)',
                'Has Edge' => 'O(1)',
                'Get Neighbors' => 'O(V)',
                'Best For' => 'Dense graphs, frequent edge lookups'
            ],
            'Adjacency List' => [
                'Space' => 'O(V + E)',
                'Add Edge' => 'O(1)',
                'Remove Edge' => 'O(V)',
                'Has Edge' => 'O(V)',
                'Get Neighbors' => 'O(1)',
                'Best For' => 'Sparse graphs, traversal algorithms'
            ],
            'Edge List' => [
                'Space' => 'O(E)',
                'Add Edge' => 'O(1)',
                'Remove Edge' => 'O(E)',
                'Has Edge' => 'O(E)',
                'Get Neighbors' => 'O(E)',
                'Best For' => 'Edge-focused algorithms (Kruskal\'s MST)'
            ]
        ];
    }
}

$guide = new GraphRepresentationGuide();

// Example: Social network with 10,000 users, 50,000 friendships
echo $guide->chooseRepresentation(10000, 50000, false, false) . "\n";
// Adjacency List - Space efficient for sparse graphs, easy neighbor iteration

// Example: Complete graph with 100 vertices
echo $guide->chooseRepresentation(100, 4950, false, false) . "\n";
// Adjacency Matrix - Dense graph benefits from O(1) edge lookup

print_r($guide->getComplexities());
```

## Practical Applications

### 1. Social Network Graph

```php
<?php

class SocialNetwork
{
    private HashMapGraph $graph;

    public function __construct()
    {
        $this->graph = new HashMapGraph();
    }

    public function addUser(string $name): void
    {
        $this->graph->addVertex($name);
    }

    public function addFriendship(string $user1, string $user2): void
    {
        $this->graph->addEdge($user1, $user2);
    }

    public function getFriends(string $user): array
    {
        return $this->graph->getNeighbors($user);
    }

    public function areFriends(string $user1, string $user2): bool
    {
        return $this->graph->hasEdge($user1, $user2);
    }

    public function getMutualFriends(string $user1, string $user2): array
    {
        $friends1 = $this->graph->getNeighbors($user1);
        $friends2 = $this->graph->getNeighbors($user2);
        return array_values(array_intersect($friends1, $friends2));
    }

    public function suggestFriends(string $user): array
    {
        $suggestions = [];
        $friends = $this->graph->getNeighbors($user);

        foreach ($friends as $friend) {
            foreach ($this->graph->getNeighbors($friend) as $friendOfFriend) {
                if ($friendOfFriend !== $user && !$this->areFriends($user, $friendOfFriend)) {
                    $suggestions[$friendOfFriend] = ($suggestions[$friendOfFriend] ?? 0) + 1;
                }
            }
        }

        arsort($suggestions);
        return array_keys($suggestions);
    }
}

// Usage
$network = new SocialNetwork();
$network->addFriendship('Alice', 'Bob');
$network->addFriendship('Alice', 'Charlie');
$network->addFriendship('Bob', 'David');
$network->addFriendship('Charlie', 'David');
$network->addFriendship('David', 'Eve');

echo "Alice's friends: " . implode(', ', $network->getFriends('Alice')) . "\n";
// Alice's friends: Bob, Charlie

echo "Mutual friends (Alice & David): " . implode(', ', $network->getMutualFriends('Alice', 'David')) . "\n";
// Mutual friends (Alice & David): Bob, Charlie

echo "Friend suggestions for Alice: " . implode(', ', $network->suggestFriends('Alice')) . "\n";
// Friend suggestions for Alice: David, Eve
```

### 2. Web Page Link Graph

```php
<?php

class WebGraph
{
    private HashMapGraph $graph;

    public function __construct()
    {
        $this->graph = new HashMapGraph();
    }

    public function addPage(string $url): void
    {
        $this->graph->addVertex($url);
    }

    public function addLink(string $fromUrl, string $toUrl): void
    {
        $this->graph->addDirectedEdge($fromUrl, $toUrl);
    }

    public function getOutgoingLinks(string $url): array
    {
        return $this->graph->getNeighbors($url);
    }

    public function getIncomingLinks(string $url): array
    {
        $incoming = [];
        foreach ($this->graph->getVertices() as $vertex) {
            if ($this->graph->hasEdge($vertex, $url)) {
                $incoming[] = $vertex;
            }
        }
        return $incoming;
    }

    public function getPageRank(string $url): int
    {
        // Simplified PageRank: count incoming links
        return count($this->getIncomingLinks($url));
    }
}

// Usage
$web = new WebGraph();
$web->addLink('index.html', 'about.html');
$web->addLink('index.html', 'contact.html');
$web->addLink('about.html', 'contact.html');
$web->addLink('blog.html', 'index.html');

echo "Outgoing from index.html: " . implode(', ', $web->getOutgoingLinks('index.html')) . "\n";
// Outgoing from index.html: about.html, contact.html

echo "Incoming to index.html: " . implode(', ', $web->getIncomingLinks('index.html')) . "\n";
// Incoming to index.html: blog.html

echo "PageRank of contact.html: " . $web->getPageRank('contact.html') . "\n";
// PageRank of contact.html: 2
```

### 3. Dependency Graph

```php
<?php

class DependencyGraph
{
    private HashMapGraph $graph;

    public function __construct()
    {
        $this->graph = new HashMapGraph();
    }

    public function addPackage(string $package): void
    {
        $this->graph->addVertex($package);
    }

    // packageA depends on packageB
    public function addDependency(string $packageA, string $packageB): void
    {
        $this->graph->addDirectedEdge($packageA, $packageB);
    }

    public function getDependencies(string $package): array
    {
        return $this->graph->getNeighbors($package);
    }

    public function getDependents(string $package): array
    {
        $dependents = [];
        foreach ($this->graph->getVertices() as $vertex) {
            if ($this->graph->hasEdge($vertex, $package)) {
                $dependents[] = $vertex;
            }
        }
        return $dependents;
    }
}

// Usage - PHP package dependencies
$deps = new DependencyGraph();
$deps->addDependency('myapp', 'symfony/http-kernel');
$deps->addDependency('myapp', 'doctrine/orm');
$deps->addDependency('symfony/http-kernel', 'symfony/event-dispatcher');
$deps->addDependency('doctrine/orm', 'doctrine/dbal');

echo "myapp depends on: " . implode(', ', $deps->getDependencies('myapp')) . "\n";
// myapp depends on: symfony/http-kernel, doctrine/orm

echo "symfony/http-kernel is depended on by: " .
     implode(', ', $deps->getDependents('symfony/http-kernel')) . "\n";
// symfony/http-kernel is depended on by: myapp
```

## Complexity Comparison

| Operation | Adjacency Matrix | Adjacency List | Edge List |
|-----------|-----------------|----------------|-----------|
| Space | O(V²) | O(V + E) | O(E) |
| Add Vertex | O(V²) | O(1) | O(1) |
| Add Edge | O(1) | O(1) | O(1) |
| Remove Edge | O(1) | O(V) | O(E) |
| Has Edge | O(1) | O(V) | O(E) |
| Get Neighbors | O(V) | O(1) | O(E) |

**Where**:
- V = number of vertices
- E = number of edges

## Best Practices

1. **Choose Based on Graph Density**
   - Sparse graphs (E ≈ V): Use adjacency list
   - Dense graphs (E ≈ V²): Use adjacency matrix

2. **Consider Operations**
   - Frequent edge lookups: Adjacency matrix
   - Frequent neighbor iteration: Adjacency list
   - Edge-focused algorithms: Edge list

3. **Memory Constraints**
   - Limited memory + sparse graph: Adjacency list
   - Need fast access + dense graph: Adjacency matrix

4. **Use Named Vertices**
   - Real-world applications: Hash map graph
   - Better readability and maintainability

## Practice Exercises

1. **Graph Conversion**
   - Convert between adjacency matrix and adjacency list
   - Preserve all edges and weights

2. **Graph Transpose**
   - Reverse all edges in a directed graph
   - Implement for all three representations

3. **Degree Sequence**
   - Calculate degree of all vertices
   - Find vertices with maximum/minimum degree

4. **Complement Graph**
   - Create complement (edges that don't exist become edges)
   - Original edges are removed

5. **Graph Union/Intersection**
   - Combine two graphs (union of edges)
   - Find common edges (intersection)

## Key Takeaways

- Graphs model relationships between objects (social networks, roads, dependencies)
- Three main representations: adjacency matrix, adjacency list, edge list
- Adjacency matrix: O(1) edge lookup, O(V²) space, good for dense graphs
- Adjacency list: O(V + E) space, good for sparse graphs, efficient neighbor iteration
- Edge list: O(E) space, good for edge-focused algorithms
- Hash map graphs allow named vertices (strings) instead of indices
- Choose representation based on graph density and required operations
- Sparse graphs are most common in practice, making adjacency lists popular

## Next Steps

In the next chapter, we'll explore Depth-First Search (DFS), a fundamental graph traversal algorithm used for connectivity, cycle detection, topological sorting, and more.
