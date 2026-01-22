---
title: "21: Graph Representations"
description: "Learn the fundamental ways to represent graphs in code including adjacency matrix, adjacency list, and edge list, with practical implementations in PHP"
sidebar:
  label: "21: Graph Representations"
  order: 21
  badge:
    text: Intermediate
    variant: caution
---
![Graph Representations](/images/php-algorithms/chapter-21-graph-representations-hero-full.webp)


# 21: Graph Representations <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Graphs are powerful data structures that model relationships between objects—from social networks to road maps, from web page links to dependency trees. Understanding how to represent graphs efficiently is fundamental to implementing graph algorithms. The representation you choose can mean the difference between an algorithm that runs in seconds versus one that takes hours!

In this chapter, you'll learn three fundamental ways to represent graphs in code: the adjacency matrix (perfect for dense graphs with frequent edge lookups), the adjacency list (space-efficient for sparse graphs), and the edge list (ideal for edge-focused algorithms). We'll implement each representation in PHP with both weighted and unweighted variants, explore real-world applications like social networks and package dependency resolution, and learn when to choose each representation based on your specific use case.

By the end of this chapter, you'll have a solid foundation in graph representations that will enable you to implement graph algorithms efficiently in the chapters that follow.

## What You'll Learn

- Understand graph terminology (vertices, edges, directed, weighted)
- Implement adjacency matrix representation for dense graphs
- Build adjacency list representation for sparse graphs
- Use edge list representation for simple graph operations
- Choose the right representation for your specific use case

**Estimated Time**: ~45 minutes

## Prerequisites

Before starting this chapter, you should have:

- ✓ Strong understanding of arrays (Chapter 15)
- ✓ Knowledge of hash tables (Chapter 13)
- ✓ Familiarity with object-oriented PHP
- ✓ Basic understanding of graph concepts

## What You'll Build

By the end of this chapter, you will have created:

- Complete implementations of three graph representations (adjacency matrix, adjacency list, edge list)
- Weighted and unweighted variants of each representation
- A hash map-based graph for named vertices
- Real-world applications including social network analysis and package dependency resolution
- A decision guide for choosing the right representation based on graph properties

## Quick Start

Let's create a simple graph to see how it works:

```php
# filename: quick-start.php
<?php

declare(strict_types=1);

// Simple adjacency list representation
$graph = [];
$graph[0] = [1, 2];  // Vertex 0 connects to vertices 1 and 2
$graph[1] = [0, 2];  // Vertex 1 connects to vertices 0 and 2
$graph[2] = [0, 1, 3];  // Vertex 2 connects to vertices 0, 1, and 3
$graph[3] = [2];  // Vertex 3 connects to vertex 2

// Check if edge exists
function hasEdge(array $graph, int $from, int $to): bool
{
    return in_array($to, $graph[$from] ?? [], true);
}

// Get neighbors
function getNeighbors(array $graph, int $vertex): array
{
    return $graph[$vertex] ?? [];
}

// Example usage
echo "Neighbors of vertex 2: " . implode(', ', getNeighbors($graph, 2)) . "\n";
echo "Edge from 0 to 1 exists: " . (hasEdge($graph, 0, 1) ? 'Yes' : 'No') . "\n";
```

Expected output:

```
Neighbors of vertex 2: 0, 1, 3
Edge from 0 to 1 exists: Yes
```

This simple example shows the basic concept: each vertex has a list of its neighbors. In the rest of this chapter, we'll explore more sophisticated representations and their trade-offs.

## What is a Graph?

A graph G = (V, E) consists of:
- **V**: Set of vertices (nodes)
- **E**: Set of edges (connections between vertices)

### Graph Types

```php
# filename: graph-types.php
<?php

declare(strict_types=1);

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
# filename: graph-properties.php
<?php

declare(strict_types=1);

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

The adjacency matrix is the most intuitive representation: a 2D array where `matrix[i][j]` represents the edge from vertex i to vertex j. This representation excels when you need fast edge lookups (O(1) time) and works best for dense graphs where most vertices are connected.

### Unweighted Adjacency Matrix

```php
# filename: adjacency-matrix.php
<?php

declare(strict_types=1);

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
# filename: weighted-adjacency-matrix.php
<?php

declare(strict_types=1);

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

The adjacency list representation stores each vertex's neighbors in a separate list, making it much more space-efficient for sparse graphs. Instead of allocating space for all possible edges (like the matrix), we only store edges that actually exist. This makes it the preferred choice for most real-world graphs, which are typically sparse.

### Unweighted Adjacency List

```php
# filename: adjacency-list.php
<?php

declare(strict_types=1);

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
# filename: weighted-adjacency-list.php
<?php

declare(strict_types=1);

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

The edge list is the simplest representation: just a list of all edges in the graph. While it's not efficient for most operations (edge lookups require scanning the entire list), it's perfect for algorithms that need to process all edges, such as Kruskal's algorithm for finding minimum spanning trees.

```php
# filename: edge-list.php
<?php

declare(strict_types=1);

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

## Adjacency Set Representation

An optimized variant of adjacency list using hash sets (associative arrays) instead of arrays for O(1) edge lookups while maintaining efficient neighbor iteration.

```php
# filename: adjacency-set.php
<?php

declare(strict_types=1);

class AdjacencySet
{
    private array $sets = [];
    private int $vertices;

    public function __construct(int $vertices)
    {
        $this->vertices = $vertices;
        $this->sets = array_fill(0, $vertices, []);
    }

    // Add edge (undirected graph) - O(1) lookup
    public function addEdge(int $from, int $to): void
    {
        $this->sets[$from][$to] = true;
        $this->sets[$to][$from] = true;
    }

    // Add directed edge
    public function addDirectedEdge(int $from, int $to): void
    {
        $this->sets[$from][$to] = true;
    }

    // Remove edge - O(1)
    public function removeEdge(int $from, int $to): void
    {
        unset($this->sets[$from][$to]);
        unset($this->sets[$to][$from]);
    }

    // Check if edge exists - O(1) instead of O(V)!
    public function hasEdge(int $from, int $to): bool
    {
        return isset($this->sets[$from][$to]);
    }

    // Get neighbors - O(degree)
    public function getNeighbors(int $vertex): array
    {
        return array_keys($this->sets[$vertex] ?? []);
    }

    // Get degree
    public function getDegree(int $vertex): int
    {
        return count($this->sets[$vertex] ?? []);
    }

    // Print set
    public function print(): void
    {
        for ($i = 0; $i < $this->vertices; $i++) {
            echo "$i: " . implode(' -> ', $this->getNeighbors($i)) . "\n";
        }
    }
}

// Example usage
$graph = new AdjacencySet(4);
$graph->addEdge(0, 1);
$graph->addEdge(0, 2);
$graph->addEdge(1, 2);
$graph->addEdge(2, 3);

echo "Has edge 0->1: " . ($graph->hasEdge(0, 1) ? 'Yes' : 'No') . "\n";  // Yes - O(1) lookup!
echo "Has edge 0->3: " . ($graph->hasEdge(0, 3) ? 'Yes' : 'No') . "\n";  // No
echo "Neighbors of 2: " . implode(', ', $graph->getNeighbors(2)) . "\n";  // 0, 1, 3
```

**When to use Adjacency Set:**
- Need fast edge existence checks (O(1) vs O(V) in regular adjacency list)
- Graph has many edge existence queries
- Memory overhead of hash sets is acceptable
- Still need efficient neighbor iteration

## Hash Map Representation

Using associative arrays for named vertices (non-integer identifiers).

```php
# filename: hashmap-graph.php
<?php

declare(strict_types=1);

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

## Dynamic Vertex Management

Most graph representations assume a fixed number of vertices. However, real-world applications often need to add or remove vertices dynamically. Here's how to handle dynamic graphs:

### Dynamic Adjacency List

```php
# filename: dynamic-adjacency-list.php
<?php

declare(strict_types=1);

class DynamicAdjacencyList
{
    private array $list = [];
    private int $nextVertexId = 0;

    // Add a new vertex and return its ID
    public function addVertex(): int
    {
        $vertexId = $this->nextVertexId++;
        $this->list[$vertexId] = [];
        return $vertexId;
    }

    // Remove vertex and all its edges
    public function removeVertex(int $vertex): void
    {
        if (!isset($this->list[$vertex])) {
            return;
        }

        // Remove all edges connected to this vertex
        foreach ($this->list[$vertex] as $neighbor) {
            if (isset($this->list[$neighbor])) {
                $this->list[$neighbor] = array_values(
                    array_filter($this->list[$neighbor], fn($v) => $v !== $vertex)
                );
            }
        }

        // Remove the vertex
        unset($this->list[$vertex]);
    }

    // Add edge
    public function addEdge(int $from, int $to): void
    {
        if (!isset($this->list[$from]) || !isset($this->list[$to])) {
            throw new InvalidArgumentException("Vertices must exist before adding edge");
        }

        if (!in_array($to, $this->list[$from], true)) {
            $this->list[$from][] = $to;
        }
        if (!in_array($from, $this->list[$to], true)) {
            $this->list[$to][] = $from;
        }
    }

    // Get neighbors
    public function getNeighbors(int $vertex): array
    {
        return $this->list[$vertex] ?? [];
    }

    // Get all vertices
    public function getVertices(): array
    {
        return array_keys($this->list);
    }

    // Get vertex count
    public function getVertexCount(): int
    {
        return count($this->list);
    }

    // Check if vertex exists
    public function hasVertex(int $vertex): bool
    {
        return isset($this->list[$vertex]);
    }
}

// Example usage
$graph = new DynamicAdjacencyList();

// Add vertices dynamically
$v0 = $graph->addVertex();
$v1 = $graph->addVertex();
$v2 = $graph->addVertex();

// Add edges
$graph->addEdge($v0, $v1);
$graph->addEdge($v1, $v2);

echo "Vertices: " . implode(', ', $graph->getVertices()) . "\n";  // 0, 1, 2
echo "Neighbors of $v1: " . implode(', ', $graph->getNeighbors($v1)) . "\n";  // 0, 2

// Remove a vertex
$graph->removeVertex($v0);
echo "Vertices after removal: " . implode(', ', $graph->getVertices()) . "\n";  // 1, 2
```

### Dynamic Adjacency Matrix

```php
# filename: dynamic-adjacency-matrix.php
<?php

declare(strict_types=1);

class DynamicAdjacencyMatrix
{
    private array $matrix = [];
    private array $vertexMap = [];  // Map internal IDs to user IDs
    private int $nextId = 0;

    // Add vertex
    public function addVertex(int $userId): void
    {
        if (isset($this->vertexMap[$userId])) {
            return;  // Already exists
        }

        $internalId = $this->nextId++;
        $this->vertexMap[$userId] = $internalId;

        // Expand matrix
        $size = count($this->matrix);
        for ($i = 0; $i < $size; $i++) {
            $this->matrix[$i][] = 0;  // Add column
        }
        $this->matrix[] = array_fill(0, $size + 1, 0);  // Add row
    }

    // Remove vertex (expensive - O(V²))
    public function removeVertex(int $userId): void
    {
        if (!isset($this->vertexMap[$userId])) {
            return;
        }

        $internalId = $this->vertexMap[$userId];

        // Remove row
        unset($this->matrix[$internalId]);
        $this->matrix = array_values($this->matrix);  // Reindex

        // Remove column
        foreach ($this->matrix as $i => $row) {
            unset($row[$internalId]);
            $this->matrix[$i] = array_values($row);
        }

        // Update vertex map
        unset($this->vertexMap[$userId]);
        // Rebuild map for remaining vertices
        $newMap = [];
        foreach ($this->vertexMap as $uid => $iid) {
            if ($iid > $internalId) {
                $newMap[$uid] = $iid - 1;
            } else {
                $newMap[$uid] = $iid;
            }
        }
        $this->vertexMap = $newMap;
    }

    // Add edge
    public function addEdge(int $from, int $to): void
    {
        if (!isset($this->vertexMap[$from]) || !isset($this->vertexMap[$to])) {
            throw new InvalidArgumentException("Vertices must exist");
        }

        $iFrom = $this->vertexMap[$from];
        $iTo = $this->vertexMap[$to];
        $this->matrix[$iFrom][$iTo] = 1;
        $this->matrix[$iTo][$iFrom] = 1;
    }

    // Check edge
    public function hasEdge(int $from, int $to): bool
    {
        if (!isset($this->vertexMap[$from]) || !isset($this->vertexMap[$to])) {
            return false;
        }

        $iFrom = $this->vertexMap[$from];
        $iTo = $this->vertexMap[$to];
        return $this->matrix[$iFrom][$iTo] === 1;
    }
}

// Example usage
$graph = new DynamicAdjacencyMatrix();
$graph->addVertex(100);  // User ID 100
$graph->addVertex(200);  // User ID 200
$graph->addVertex(300);  // User ID 300

$graph->addEdge(100, 200);
$graph->addEdge(200, 300);

echo "Edge 100->200: " . ($graph->hasEdge(100, 200) ? 'Yes' : 'No') . "\n";
```

**Note**: Dynamic adjacency matrix is expensive (O(V²) for vertex removal). Use dynamic adjacency list for better performance.

## Graph Statistics and Utilities

Every graph representation should provide basic statistics and utility methods. Here's a comprehensive utility class that works with any representation:

```php
# filename: graph-statistics.php
<?php

declare(strict_types=1);

class GraphStatistics
{
    private array $graph;
    private bool $directed;
    private int $vertexCount;
    private int $edgeCount;

    public function __construct(array $graph, bool $directed = false)
    {
        $this->graph = $graph;
        $this->directed = $directed;
        $this->vertexCount = count($graph);
        $this->edgeCount = $this->calculateEdgeCount();
    }

    // Calculate total edge count
    private function calculateEdgeCount(): int
    {
        $count = 0;
        foreach ($this->graph as $neighbors) {
            $count += count($neighbors);
        }
        return $this->directed ? $count : $count / 2;
    }

    // Get vertex count
    public function getVertexCount(): int
    {
        return $this->vertexCount;
    }

    // Get edge count
    public function getEdgeCount(): int
    {
        return $this->edgeCount;
    }

    // Calculate graph density
    // For undirected: density = 2E / (V(V-1))
    // For directed: density = E / (V(V-1))
    public function getDensity(): float
    {
        if ($this->vertexCount <= 1) {
            return 0.0;
        }

        $maxEdges = $this->directed
            ? $this->vertexCount * ($this->vertexCount - 1)
            : $this->vertexCount * ($this->vertexCount - 1) / 2;

        return $maxEdges > 0 ? $this->edgeCount / $maxEdges : 0.0;
    }

    // Check if graph is dense
    public function isDense(float $threshold = 0.5): bool
    {
        return $this->getDensity() > $threshold;
    }

    // Check if graph is sparse
    public function isSparse(float $threshold = 0.1): bool
    {
        return $this->getDensity() < $threshold;
    }

    // Get average degree
    public function getAverageDegree(): float
    {
        if ($this->vertexCount === 0) {
            return 0.0;
        }

        $totalDegree = 0;
        foreach ($this->graph as $vertex => $neighbors) {
            $totalDegree += count($neighbors);
        }

        return $this->directed
            ? $totalDegree / $this->vertexCount
            : (2 * $this->edgeCount) / $this->vertexCount;
    }

    // Get maximum degree
    public function getMaxDegree(): int
    {
        $maxDegree = 0;
        foreach ($this->graph as $neighbors) {
            $maxDegree = max($maxDegree, count($neighbors));
        }
        return $maxDegree;
    }

    // Get minimum degree
    public function getMinDegree(): int
    {
        if ($this->vertexCount === 0) {
            return 0;
        }

        $minDegree = PHP_INT_MAX;
        foreach ($this->graph as $neighbors) {
            $minDegree = min($minDegree, count($neighbors));
        }
        return $minDegree === PHP_INT_MAX ? 0 : $minDegree;
    }

    // Get degree distribution
    public function getDegreeDistribution(): array
    {
        $distribution = [];
        foreach ($this->graph as $neighbors) {
            $degree = count($neighbors);
            $distribution[$degree] = ($distribution[$degree] ?? 0) + 1;
        }
        ksort($distribution);
        return $distribution;
    }

    // Check if graph is complete (all possible edges exist)
    public function isComplete(): bool
    {
        $maxEdges = $this->directed
            ? $this->vertexCount * ($this->vertexCount - 1)
            : $this->vertexCount * ($this->vertexCount - 1) / 2;

        return $this->edgeCount === $maxEdges;
    }

    // Get all statistics as array
    public function getAllStatistics(): array
    {
        return [
            'vertices' => $this->vertexCount,
            'edges' => $this->edgeCount,
            'density' => round($this->getDensity(), 4),
            'average_degree' => round($this->getAverageDegree(), 2),
            'max_degree' => $this->getMaxDegree(),
            'min_degree' => $this->getMinDegree(),
            'is_dense' => $this->isDense(),
            'is_sparse' => $this->isSparse(),
            'is_complete' => $this->isComplete(),
            'degree_distribution' => $this->getDegreeDistribution()
        ];
    }
}

// Example usage
$graph = [
    0 => [1, 2],
    1 => [0, 2, 3],
    2 => [0, 1],
    3 => [1]
];

$stats = new GraphStatistics($graph, directed: false);
print_r($stats->getAllStatistics());
/*
Array
(
    [vertices] => 4
    [edges] => 4
    [density] => 0.6667
    [average_degree] => 2.0
    [max_degree] => 3
    [min_degree] => 1
    [is_dense] => 1
    [is_sparse] => 0
    [is_complete] => 0
    [degree_distribution] => Array
        (
            [1] => 1
            [2] => 2
            [3] => 1
        )
)
*/
```

## Graph Validation

Validating graph representations ensures data integrity and helps catch errors early:

```php
# filename: graph-validation.php
<?php

declare(strict_types=1);

class GraphValidator
{
    private array $graph;
    private bool $directed;
    private int $expectedVertices;

    public function __construct(array $graph, bool $directed = false, int $expectedVertices = 0)
    {
        $this->graph = $graph;
        $this->directed = $directed;
        $this->expectedVertices = $expectedVertices ?: count($graph);
    }

    // Validate graph structure
    public function validate(): array
    {
        $errors = [];

        // Check vertex count
        if ($this->expectedVertices > 0 && count($this->graph) !== $this->expectedVertices) {
            $errors[] = "Vertex count mismatch: expected {$this->expectedVertices}, got " . count($this->graph);
        }

        // Check for invalid vertex references
        foreach ($this->graph as $vertex => $neighbors) {
            // Validate vertex ID is within range
            if ($this->expectedVertices > 0 && $vertex >= $this->expectedVertices) {
                $errors[] = "Invalid vertex ID: $vertex (out of range)";
            }

            // Check neighbor references
            foreach ($neighbors as $neighbor) {
                if (is_array($neighbor)) {
                    // Weighted edge
                    $neighborId = $neighbor['vertex'] ?? null;
                    if ($neighborId === null) {
                        $errors[] = "Invalid edge structure from vertex $vertex";
                        continue;
                    }
                    $neighbor = $neighborId;
                }

                // Check if neighbor exists
                if ($this->expectedVertices > 0 && $neighbor >= $this->expectedVertices) {
                    $errors[] = "Invalid neighbor reference: vertex $vertex -> $neighbor (out of range)";
                }

                // Check for self-loops (if not allowed)
                if ($vertex === $neighbor) {
                    $errors[] = "Self-loop detected: vertex $vertex -> $vertex";
                }

                // For undirected graphs, check symmetry
                if (!$this->directed) {
                    if (!isset($this->graph[$neighbor]) || !in_array($vertex, $this->graph[$neighbor], true)) {
                        $errors[] = "Asymmetric edge in undirected graph: $vertex -> $neighbor exists but $neighbor -> $vertex missing";
                    }
                }
            }
        }

        return $errors;
    }

    // Check if graph is valid
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    // Validate and throw exception if invalid
    public function assertValid(): void
    {
        $errors = $this->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException("Graph validation failed:\n" . implode("\n", $errors));
        }
    }

    // Check for duplicate edges
    public function hasDuplicateEdges(): bool
    {
        foreach ($this->graph as $vertex => $neighbors) {
            $seen = [];
            foreach ($neighbors as $neighbor) {
                $neighborId = is_array($neighbor) ? $neighbor['vertex'] : $neighbor;
                if (isset($seen[$neighborId])) {
                    return true;
                }
                $seen[$neighborId] = true;
            }
        }
        return false;
    }

    // Check for isolated vertices (vertices with no edges)
    public function getIsolatedVertices(): array
    {
        $isolated = [];
        foreach ($this->graph as $vertex => $neighbors) {
            if (empty($neighbors)) {
                $isolated[] = $vertex;
            }
        }
        return $isolated;
    }
}

// Example usage
$graph = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1, 3],  // Invalid: vertex 3 doesn't exist
    3 => [2]
];

$validator = new GraphValidator($graph, directed: false, expectedVertices: 4);
$errors = $validator->validate();

if (!empty($errors)) {
    echo "Validation errors found:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

// Valid graph example
$validGraph = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1],
    3 => []
];

$validator2 = new GraphValidator($validGraph, directed: false, expectedVertices: 4);
if ($validator2->isValid()) {
    echo "✓ Graph is valid\n";
}

$isolated = $validator2->getIsolatedVertices();
echo "Isolated vertices: " . implode(', ', $isolated) . "\n";  // 3
```

## Graph Serialization

Saving and loading graphs is essential for persistence and data exchange. Here's how to serialize graphs to JSON:

```php
# filename: graph-serialization.php
<?php

declare(strict_types=1);

class SerializableGraph
{
    private array $graph = [];
    private bool $directed = false;
    private bool $weighted = false;

    public function __construct(bool $directed = false, bool $weighted = false)
    {
        $this->directed = $directed;
        $this->weighted = $weighted;
    }

    public function addEdge(string $from, string $to, int $weight = 1): void
    {
        if (!isset($this->graph[$from])) {
            $this->graph[$from] = [];
        }

        if ($this->weighted) {
            $this->graph[$from][$to] = $weight;
        } else {
            $this->graph[$from][$to] = true;
        }

        if (!$this->directed) {
            if (!isset($this->graph[$to])) {
                $this->graph[$to] = [];
            }
            if ($this->weighted) {
                $this->graph[$to][$from] = $weight;
            } else {
                $this->graph[$to][$from] = true;
            }
        }
    }

    // Serialize to JSON
    public function toJson(): string
    {
        $data = [
            'directed' => $this->directed,
            'weighted' => $this->weighted,
            'graph' => $this->graph
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    // Deserialize from JSON
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON: " . json_last_error_msg());
        }

        $graph = new self($data['directed'], $data['weighted']);
        $graph->graph = $data['graph'];
        return $graph;
    }

    // Save to file
    public function saveToFile(string $filename): void
    {
        file_put_contents($filename, $this->toJson());
    }

    // Load from file
    public static function loadFromFile(string $filename): self
    {
        $json = file_get_contents($filename);
        if ($json === false) {
            throw new RuntimeException("Could not read file: $filename");
        }
        return self::fromJson($json);
    }

    // Get graph structure
    public function getGraph(): array
    {
        return $this->graph;
    }
}

// Example usage
$graph = new SerializableGraph(directed: false, weighted: true);
$graph->addEdge('Alice', 'Bob', 5);
$graph->addEdge('Bob', 'Charlie', 3);
$graph->addEdge('Alice', 'Charlie', 7);

// Serialize
$json = $graph->toJson();
echo "Serialized graph:\n$json\n";

// Save to file
$graph->saveToFile('graph.json');

// Load from file
$loadedGraph = SerializableGraph::loadFromFile('graph.json');
print_r($loadedGraph->getGraph());

// Expected JSON output:
/*
{
    "directed": false,
    "weighted": true,
    "graph": {
        "Alice": {
            "Bob": 5,
            "Charlie": 7
        },
        "Bob": {
            "Alice": 5,
            "Charlie": 3
        },
        "Charlie": {
            "Bob": 3,
            "Alice": 7
        }
    }
}
*/
```

## Visual Graph Examples

Understanding graph structure through ASCII visualization:

```php
# filename: graph-visualizer.php
<?php

declare(strict_types=1);

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
# filename: graph-benchmark.php
<?php

declare(strict_types=1);

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
# filename: social-network-advanced.php
<?php

declare(strict_types=1);

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
# filename: package-manager.php
<?php

declare(strict_types=1);

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
# filename: representation-guide.php
<?php

declare(strict_types=1);

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
            ],
            'Adjacency Set' => [
                'Space' => 'O(V + E)',
                'Add Edge' => 'O(1)',
                'Remove Edge' => 'O(1)',
                'Has Edge' => 'O(1)',
                'Get Neighbors' => 'O(degree)',
                'Best For' => 'Frequent edge lookups with sparse graphs'
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
# filename: social-network.php
<?php

declare(strict_types=1);

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
# filename: web-graph.php
<?php

declare(strict_types=1);

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
# filename: dependency-graph.php
<?php

declare(strict_types=1);

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

## Troubleshooting

### Error: "Undefined array key"

**Symptom**: `Warning: Undefined array key X` when accessing graph vertices

**Cause**: Attempting to access a vertex that doesn't exist in the graph

**Solution**: Always check if vertex exists before accessing:

```php
// Wrong
$neighbors = $graph[$vertex];  // May fail if vertex doesn't exist

// Correct
$neighbors = $graph[$vertex] ?? [];
// Or use a method that handles missing vertices
$neighbors = $graph->getNeighbors($vertex);  // Method handles missing vertices
```

### Error: "Array to string conversion" in adjacency list

**Symptom**: `Warning: Array to string conversion` when printing or comparing adjacency lists

**Cause**: Trying to use array operations on weighted adjacency list entries (which are arrays)

**Solution**: Handle weighted edges properly:

```php
// Wrong - treating weighted edge as integer
if (in_array($to, $this->list[$from])) {  // Fails if edges are ['vertex' => X, 'weight' => Y]
    // ...
}

// Correct - check structure
foreach ($this->list[$from] as $edge) {
    if (is_array($edge) && $edge['vertex'] === $to) {
        // Found weighted edge
    } elseif ($edge === $to) {
        // Found unweighted edge
    }
}
```

### Problem: Adjacency matrix uses too much memory

**Symptom**: Memory exhaustion with large graphs (10,000+ vertices)

**Cause**: Adjacency matrix uses O(V²) space, which becomes prohibitive for large sparse graphs

**Solution**: Switch to adjacency list for sparse graphs:

```php
// For sparse graphs (few edges), use adjacency list
$isSparse = $edges < $vertices * 2;
if ($isSparse) {
    $graph = new AdjacencyList($vertices);  // O(V + E) space
} else {
    $graph = new AdjacencyMatrix($vertices);  // O(V²) space
}
```

### Problem: Edge lookup is slow in adjacency list

**Symptom**: `hasEdge()` takes too long for large graphs

**Cause**: Adjacency list requires O(V) time to check if edge exists (must scan neighbor list)

**Solution**: For frequent edge lookups, use adjacency matrix or optimize with hash sets:

```php
// Option 1: Use adjacency matrix for frequent lookups
$graph = new AdjacencyMatrix($vertices);  // O(1) lookup

// Option 2: Optimize adjacency list with hash sets
class FastAdjacencyList {
    private array $neighborSets = [];  // Use sets for O(1) lookup
    
    public function hasEdge(int $from, int $to): bool {
        return isset($this->neighborSets[$from][$to]);
    }
}
```

### Problem: Duplicate edges in adjacency list

**Symptom**: Same edge appears multiple times in neighbor list

**Cause**: Adding same edge multiple times without checking

**Solution**: Always check before adding:

```php
// Wrong - allows duplicates
public function addEdge(int $from, int $to): void {
    $this->list[$from][] = $to;  // May add duplicate
}

// Correct - check first
public function addEdge(int $from, int $to): void {
    if (!in_array($to, $this->list[$from], true)) {
        $this->list[$from][] = $to;
    }
}
```

### Problem: Wrong edge count in undirected graphs

**Symptom**: `getEdgeCount()` returns double the actual number of edges

**Cause**: Counting each edge twice (once for each direction) in undirected graph

**Solution**: Divide by 2 for undirected graphs:

```php
// Correct implementation
public function getEdgeCount(): int {
    $count = 0;
    foreach ($this->list as $neighbors) {
        $count += count($neighbors);
    }
    return $count / 2;  // Each edge counted twice in undirected graph
}
```

## Practice Exercises

### Exercise 1: Graph Conversion

**Goal**: Convert between adjacency matrix and adjacency list representations while preserving all edges and weights.

**Requirements**:
- Create a class `GraphConverter` with two methods:
  - `matrixToList(array $matrix): array` - Convert adjacency matrix to adjacency list
  - `listToMatrix(array $list, int $vertices): array` - Convert adjacency list to adjacency matrix
- Handle both weighted and unweighted graphs
- Preserve edge weights when converting weighted graphs
- Support both directed and undirected graphs

**Validation**: Test your implementation:

```php
# filename: test-converter.php
<?php

declare(strict_types=1);

// Create a test graph
$matrix = [
    [0, 1, 1, 0],
    [1, 0, 1, 0],
    [1, 1, 0, 1],
    [0, 0, 1, 0]
];

$converter = new GraphConverter();
$list = $converter->matrixToList($matrix);
$backToMatrix = $converter->listToMatrix($list, 4);

// Verify conversion is correct
assert($matrix === $backToMatrix, "Conversion failed");
echo "✓ Conversion test passed\n";
```

Expected output:

```
✓ Conversion test passed
```

### Exercise 2: Graph Transpose

**Goal**: Reverse all edges in a directed graph (transpose operation).

**Requirements**:
- Implement `transpose()` method for `AdjacencyMatrix`, `AdjacencyList`, and `EdgeList` classes
- For a directed graph with edge A→B, transpose should create edge B→A
- Preserve edge weights in weighted graphs
- Return a new graph instance (don't modify the original)

**Validation**: Test with a directed graph:

```php
# filename: test-transpose.php
<?php

declare(strict_types=1);

$graph = new AdjacencyList(4);
$graph->addDirectedEdge(0, 1);
$graph->addDirectedEdge(0, 2);
$graph->addDirectedEdge(1, 3);
$graph->addDirectedEdge(2, 3);

$transposed = $graph->transpose();

// Verify: original has 0→1, transposed should have 1→0
assert($transposed->hasEdge(1, 0), "Transpose failed");
assert($transposed->hasEdge(2, 0), "Transpose failed");
assert($transposed->hasEdge(3, 1), "Transpose failed");
assert($transposed->hasEdge(3, 2), "Transpose failed");
echo "✓ Transpose test passed\n";
```

### Exercise 3: Degree Sequence

**Goal**: Calculate and analyze vertex degrees in a graph.

**Requirements**:
- Implement `getDegreeSequence(): array` that returns degrees of all vertices
- Implement `getMaxDegree(): int` and `getMinDegree(): int`
- Implement `getAverageDegree(): float`
- Handle both directed graphs (in-degree and out-degree) and undirected graphs

**Validation**: Test with a known graph:

```php
# filename: test-degrees.php
<?php

declare(strict_types=1);

$graph = new AdjacencyList(5);
$graph->addEdge(0, 1);
$graph->addEdge(0, 2);
$graph->addEdge(1, 2);
$graph->addEdge(2, 3);
$graph->addEdge(2, 4);

$degrees = $graph->getDegreeSequence();
// Expected: [2, 2, 4, 1, 1] (degrees of vertices 0-4)
assert($degrees[2] === 4, "Max degree should be 4");
assert($graph->getMaxDegree() === 4, "Max degree incorrect");
assert($graph->getMinDegree() === 1, "Min degree incorrect");
echo "✓ Degree sequence test passed\n";
```

### Exercise 4: Complement Graph

**Goal**: Create the complement of a graph (edges that don't exist become edges, original edges are removed).

**Requirements**:
- Implement `complement(): AdjacencyMatrix` or `complement(): AdjacencyList`
- For an undirected graph with n vertices, complement should have all edges except self-loops and original edges
- Maximum number of edges in complement: n(n-1)/2 - original_edges
- Handle both weighted and unweighted graphs

**Validation**: Test with a simple graph:

```php
# filename: test-complement.php
<?php

declare(strict_types=1);

$graph = new AdjacencyMatrix(4);
$graph->addEdge(0, 1);
$graph->addEdge(0, 2);
// Original has 2 edges

$complement = $graph->complement();
// Complement should have 4 edges (total possible: 6, minus 2 original)
assert($complement->getEdgeCount() === 4, "Complement edge count incorrect");
assert(!$complement->hasEdge(0, 1), "Original edge should not be in complement");
assert($complement->hasEdge(1, 3), "Missing edge should be in complement");
echo "✓ Complement test passed\n";
```

### Exercise 5: Graph Union and Intersection

**Goal**: Combine two graphs and find common edges.

**Requirements**:
- Implement `union(Graph $other): Graph` - combines edges from both graphs
- Implement `intersection(Graph $other): Graph` - finds edges present in both graphs
- Handle edge weights: for union, keep weights from first graph; for intersection, keep weights only if they match
- Both graphs must have the same number of vertices

**Validation**: Test union and intersection:

```php
# filename: test-operations.php
<?php

declare(strict_types=1);

$graph1 = new AdjacencyList(4);
$graph1->addEdge(0, 1);
$graph1->addEdge(0, 2);
$graph1->addEdge(1, 2);

$graph2 = new AdjacencyList(4);
$graph2->addEdge(0, 2);
$graph2->addEdge(2, 3);
$graph2->addEdge(1, 3);

$union = $graph1->union($graph2);
// Union should have: 0-1, 0-2, 1-2, 2-3, 1-3 (5 edges)
assert($union->getEdgeCount() === 5, "Union edge count incorrect");

$intersection = $graph1->intersection($graph2);
// Intersection should have: 0-2 (1 edge)
assert($intersection->getEdgeCount() === 1, "Intersection edge count incorrect");
assert($intersection->hasEdge(0, 2), "Intersection missing common edge");
echo "✓ Union and intersection tests passed\n";
```

## Key Takeaways

- Graphs model relationships between objects (social networks, roads, dependencies)
- Four main representations: adjacency matrix, adjacency list, edge list, adjacency set
- Adjacency matrix: O(1) edge lookup, O(V²) space, good for dense graphs
- Adjacency list: O(V + E) space, good for sparse graphs, efficient neighbor iteration
- Adjacency set: O(1) edge lookup like matrix, O(V + E) space like list - best of both worlds
- Edge list: O(E) space, good for edge-focused algorithms
- Hash map graphs allow named vertices (strings) instead of indices
- Dynamic graphs can add/remove vertices - adjacency list is more efficient than matrix
- Graph statistics (density, degree distribution) help analyze graph structure
- Graph validation ensures data integrity and catches errors early
- Graph serialization enables persistence and data exchange (JSON format)
- Choose representation based on graph density, operations needed, and memory constraints
- Sparse graphs are most common in practice, making adjacency lists/sets popular

## Wrap-up

Congratulations! You've completed a comprehensive exploration of graph representations. Here's what you've accomplished:

- ✓ Understood graph terminology (vertices, edges, directed, weighted, dense, sparse)
- ✓ Implemented adjacency matrix representation with O(1) edge lookups
- ✓ Built adjacency list representation with O(V + E) space efficiency
- ✓ Created edge list representation for edge-focused algorithms
- ✓ Implemented weighted variants of all three representations
- ✓ Built hash map graphs for named vertices (real-world applications)
- ✓ Implemented adjacency set for O(1) edge lookups in sparse graphs
- ✓ Created dynamic graph implementations that support adding/removing vertices
- ✓ Added graph statistics utilities (density, degree distribution, completeness checks)
- ✓ Implemented graph validation methods to ensure data integrity
- ✓ Added graph serialization for persistence and data exchange
- ✓ Analyzed performance characteristics and complexity trade-offs
- ✓ Applied graph representations to social networks and dependency resolution
- ✓ Learned decision criteria for choosing the right representation

You now have the foundational knowledge needed to implement graph algorithms efficiently. The representation you choose will significantly impact the performance of algorithms like depth-first search, breadth-first search, shortest path algorithms, and minimum spanning trees that we'll explore in upcoming chapters.

## Further Reading

- [Graph Theory Fundamentals](https://en.wikipedia.org/wiki/Graph_theory) — Comprehensive overview of graph theory concepts
- [Adjacency Matrix vs Adjacency List](https://www.geeksforgeeks.org/comparison-between-adjacency-list-and-adjacency-matrix-representation-of-graph/) — Detailed comparison with examples
- [Graph Data Structures](https://www.programiz.com/dsa/graph) — Visual explanations of graph representations
- [Network Analysis](https://networkx.org/documentation/stable/) — Python library documentation (concepts apply to PHP implementations)


## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 21 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-21)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-21
php 01-*.php
```

## Next Steps

In the next chapter, we'll explore Depth-First Search (DFS), a fundamental graph traversal algorithm used for connectivity, cycle detection, topological sorting, and more.
