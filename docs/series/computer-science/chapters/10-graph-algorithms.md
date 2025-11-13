---
title: "10: Graph Algorithms"
description: "Model and traverse graphs with BFS and DFS. Understand graph representations (adjacency matrix, adjacency list), shortest path algorithms (Dijkstra's, Bellman-Ford), and minimum spanning trees."
series: "computer-science"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites: ["Recursion", "Queues", "Trees"]
---

# Chapter 10: Graph Algorithms

## Introduction

Graphs are versatile data structures that model relationships between entities. They power social networks, navigation systems, recommendation engines, and countless other applications.

In this chapter, you'll learn:

- Graph terminology and representations
- Graph traversal algorithms (BFS, DFS)
- Shortest path algorithms
- Cycle detection
- Topological sorting

## What is a Graph?

A **graph** is a collection of **nodes** (vertices) connected by **edges**.

```
    A ---  B
    |  \   |
    |   \  |
    C --- D
```

### Graph Terminology

- **Vertex/Node**: Individual point
- **Edge**: Connection between vertices
- **Degree**: Number of edges connected to a vertex
- **Path**: Sequence of vertices connected by edges
- **Cycle**: Path that starts and ends at same vertex
- **Connected**: Graph where all vertices are reachable
- **Weighted**: Edges have associated costs
- **Directed**: Edges have direction (one-way)
- **Undirected**: Edges are bidirectional

## Graph Representations

### 1. Adjacency Matrix

2D array where `matrix[i][j]` = 1 if edge exists:

```php
<?php

class GraphMatrix {
    private array $matrix;
    private int $vertices;

    public function __construct(int $vertices) {
        $this->vertices = $vertices;
        $this->matrix = array_fill(0, $vertices, array_fill(0, $vertices, 0));
    }

    public function addEdge(int $from, int $to, bool $directed = false): void {
        $this->matrix[$from][$to] = 1;
        if (!$directed) {
            $this->matrix[$to][$from] = 1;
        }
    }

    public function hasEdge(int $from, int $to): bool {
        return $this->matrix[$from][$to] === 1;
    }

    public function getNeighbors(int $vertex): array {
        $neighbors = [];
        for ($i = 0; $i < $this->vertices; $i++) {
            if ($this->matrix[$vertex][$i] === 1) {
                $neighbors[] = $i;
            }
        }
        return $neighbors;
    }
}
```

**Space**: O(V²) where V = number of vertices
**Use**: Dense graphs, fast edge lookups

### 2. Adjacency List

Array of lists, each list contains neighbors:

```php
<?php

class GraphList {
    private array $adjacencyList = [];

    public function addVertex(string $vertex): void {
        if (!isset($this->adjacencyList[$vertex])) {
            $this->adjacencyList[$vertex] = [];
        }
    }

    public function addEdge(string $from, string $to, bool $directed = false): void {
        $this->addVertex($from);
        $this->addVertex($to);

        $this->adjacencyList[$from][] = $to;
        if (!$directed) {
            $this->adjacencyList[$to][] = $from;
        }
    }

    public function getNeighbors(string $vertex): array {
        return $this->adjacencyList[$vertex] ?? [];
    }

    public function getVertices(): array {
        return array_keys($this->adjacencyList);
    }

    public function display(): void {
        foreach ($this->adjacencyList as $vertex => $neighbors) {
            echo "$vertex → " . implode(", ", $neighbors) . "\n";
        }
    }
}

// Usage
$graph = new GraphList();
$graph->addEdge('A', 'B');
$graph->addEdge('A', 'C');
$graph->addEdge('B', 'D');
$graph->addEdge('C', 'D');
$graph->display();
// A → B, C
// B → A, D
// C → A, D
// D → B, C
```

**Space**: O(V + E) where E = number of edges
**Use**: Sparse graphs (most real-world graphs)

## Graph Traversal

### Breadth-First Search (BFS)

Visit all neighbors before going deeper. Uses a queue.

```php
<?php

function bfs(GraphList $graph, string $start): array {
    $visited = [];
    $queue = [$start];
    $visited[$start] = true;
    $result = [];

    while (!empty($queue)) {
        $vertex = array_shift($queue);
        $result[] = $vertex;

        foreach ($graph->getNeighbors($vertex) as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $visited[$neighbor] = true;
                $queue[] = $neighbor;
            }
        }
    }

    return $result;
}

// Build graph
$graph = new GraphList();
$graph->addEdge('A', 'B');
$graph->addEdge('A', 'C');
$graph->addEdge('B', 'D');
$graph->addEdge('C', 'D');

print_r(bfs($graph, 'A')); // [A, B, C, D]
```

**Complexity**: O(V + E) time, O(V) space
**Use**: Shortest path in unweighted graphs, level-order traversal

### Depth-First Search (DFS)

Explore as deep as possible before backtracking. Uses a stack (or recursion).

```php
<?php

// Recursive DFS
function dfs(GraphList $graph, string $vertex, array &$visited = []): array {
    $visited[$vertex] = true;
    $result = [$vertex];

    foreach ($graph->getNeighbors($vertex) as $neighbor) {
        if (!isset($visited[$neighbor])) {
            $result = array_merge($result, dfs($graph, $neighbor, $visited));
        }
    }

    return $result;
}

// Iterative DFS
function dfsIterative(GraphList $graph, string $start): array {
    $visited = [];
    $stack = [$start];
    $result = [];

    while (!empty($stack)) {
        $vertex = array_pop($stack);

        if (!isset($visited[$vertex])) {
            $visited[$vertex] = true;
            $result[] = $vertex;

            foreach ($graph->getNeighbors($vertex) as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $stack[] = $neighbor;
                }
            }
        }
    }

    return $result;
}

print_r(dfs($graph, 'A')); // [A, B, D, C]
```

**Complexity**: O(V + E) time, O(V) space
**Use**: Cycle detection, topological sort, pathfinding

## Shortest Path Algorithms

### Dijkstra's Algorithm

Find shortest path from source to all other vertices (non-negative weights).

```php
<?php

function dijkstra(array $graph, string $start): array {
    $distances = [];
    $previous = [];
    $unvisited = [];

    // Initialize
    foreach ($graph as $vertex => $edges) {
        $distances[$vertex] = PHP_INT_MAX;
        $previous[$vertex] = null;
        $unvisited[$vertex] = true;
    }
    $distances[$start] = 0;

    while (!empty($unvisited)) {
        // Find vertex with minimum distance
        $minVertex = null;
        $minDistance = PHP_INT_MAX;

        foreach ($unvisited as $vertex => $unused) {
            if ($distances[$vertex] < $minDistance) {
                $minDistance = $distances[$vertex];
                $minVertex = $vertex;
            }
        }

        if ($minVertex === null) break;

        unset($unvisited[$minVertex]);

        // Update neighbors
        foreach ($graph[$minVertex] as $neighbor => $weight) {
            $alt = $distances[$minVertex] + $weight;

            if ($alt < $distances[$neighbor]) {
                $distances[$neighbor] = $alt;
                $previous[$neighbor] = $minVertex;
            }
        }
    }

    return ['distances' => $distances, 'previous' => $previous];
}

// Weighted graph
$graph = [
    'A' => ['B' => 4, 'C' => 2],
    'B' => ['C' => 1, 'D' => 5],
    'C' => ['D' => 8, 'E' => 10],
    'D' => ['E' => 2],
    'E' => []
];

$result = dijkstra($graph, 'A');
print_r($result['distances']);
// A: 0, B: 4, C: 2, D: 9, E: 11
```

**Complexity**: O((V + E) log V) with priority queue
**Use**: GPS navigation, network routing

## Cycle Detection

### Undirected Graph

```php
<?php

function hasCycleUndirected(GraphList $graph): bool {
    $visited = [];

    foreach ($graph->getVertices() as $vertex) {
        if (!isset($visited[$vertex])) {
            if (hasCycleDFS($graph, $vertex, null, $visited)) {
                return true;
            }
        }
    }

    return false;
}

function hasCycleDFS(
    GraphList $graph,
    string $vertex,
    ?string $parent,
    array &$visited
): bool {
    $visited[$vertex] = true;

    foreach ($graph->getNeighbors($vertex) as $neighbor) {
        if (!isset($visited[$neighbor])) {
            if (hasCycleDFS($graph, $neighbor, $vertex, $visited)) {
                return true;
            }
        } elseif ($neighbor !== $parent) {
            return true; // Cycle found
        }
    }

    return false;
}
```

### Directed Graph (Using Colors)

```php
<?php

function hasCycleDirected(GraphList $graph): bool {
    $color = [];

    foreach ($graph->getVertices() as $vertex) {
        $color[$vertex] = 'WHITE'; // Unvisited
    }

    foreach ($graph->getVertices() as $vertex) {
        if ($color[$vertex] === 'WHITE') {
            if (hasCycleDirectedDFS($graph, $vertex, $color)) {
                return true;
            }
        }
    }

    return false;
}

function hasCycleDirectedDFS(
    GraphList $graph,
    string $vertex,
    array &$color
): bool {
    $color[$vertex] = 'GRAY'; // In progress

    foreach ($graph->getNeighbors($vertex) as $neighbor) {
        if ($color[$neighbor] === 'GRAY') {
            return true; // Back edge = cycle
        }

        if ($color[$neighbor] === 'WHITE') {
            if (hasCycleDirectedDFS($graph, $neighbor, $color)) {
                return true;
            }
        }
    }

    $color[$vertex] = 'BLACK'; // Finished
    return false;
}
```

## Topological Sort

Order vertices in directed acyclic graph (DAG) so all edges point forward.

```php
<?php

function topologicalSort(GraphList $graph): array {
    $visited = [];
    $stack = [];

    foreach ($graph->getVertices() as $vertex) {
        if (!isset($visited[$vertex])) {
            topologicalSortDFS($graph, $vertex, $visited, $stack);
        }
    }

    return array_reverse($stack);
}

function topologicalSortDFS(
    GraphList $graph,
    string $vertex,
    array &$visited,
    array &$stack
): void {
    $visited[$vertex] = true;

    foreach ($graph->getNeighbors($vertex) as $neighbor) {
        if (!isset($visited[$neighbor])) {
            topologicalSortDFS($graph, $neighbor, $visited, $stack);
        }
    }

    $stack[] = $vertex; // Add after visiting all descendants
}

// Example: Course prerequisites
$courses = new GraphList();
$courses->addEdge('Data Structures', 'Algorithms', true);
$courses->addEdge('Intro to CS', 'Data Structures', true);
$courses->addEdge('Intro to CS', 'Discrete Math', true);
$courses->addEdge('Discrete Math', 'Algorithms', true);

print_r(topologicalSort($courses));
// [Intro to CS, Discrete Math, Data Structures, Algorithms]
```

**Complexity**: O(V + E)
**Use**: Task scheduling, build systems, course planning

## Common Graph Problems

### 1. Connected Components

```php
<?php

function countConnectedComponents(GraphList $graph): int {
    $visited = [];
    $count = 0;

    foreach ($graph->getVertices() as $vertex) {
        if (!isset($visited[$vertex])) {
            dfs($graph, $vertex, $visited);
            $count++;
        }
    }

    return $count;
}
```

### 2. Is Bipartite (Two-Coloring)

```php
<?php

function isBipartite(GraphList $graph): bool {
    $color = [];

    foreach ($graph->getVertices() as $vertex) {
        if (!isset($color[$vertex])) {
            if (!isBipartiteBFS($graph, $vertex, $color)) {
                return false;
            }
        }
    }

    return true;
}

function isBipartiteBFS(GraphList $graph, string $start, array &$color): bool {
    $queue = [$start];
    $color[$start] = 0;

    while (!empty($queue)) {
        $vertex = array_shift($queue);

        foreach ($graph->getNeighbors($vertex) as $neighbor) {
            if (!isset($color[$neighbor])) {
                $color[$neighbor] = 1 - $color[$vertex];
                $queue[] = $neighbor;
            } elseif ($color[$neighbor] === $color[$vertex]) {
                return false; // Same color = not bipartite
            }
        }
    }

    return true;
}
```

## Key Takeaways

- Graphs model **relationships** between entities
- **Adjacency list** is best for sparse graphs (most real-world)
- **BFS** finds shortest paths in unweighted graphs
- **DFS** is used for cycle detection and topological sort
- **Dijkstra's** algorithm finds shortest paths with non-negative weights
- Graphs power social networks, navigation, recommendations

## Exercises

1. **Clone a graph**: Deep copy a graph.

2. **Number of islands**: Count connected components in a 2D grid.

3. **Course schedule**: Determine if courses can be completed given prerequisites (cycle detection).

4. **Word ladder**: Transform one word to another, changing one letter at a time.

5. **Minimum spanning tree**: Implement Kruskal's or Prim's algorithm.

## What's Next?

Graphs often require clever optimization strategies. In Chapter 11, we'll explore **Greedy Algorithms**—making locally optimal choices to find global solutions.

---

**Further Reading**:
- [Graph Theory (Wikipedia)](https://en.wikipedia.org/wiki/Graph_theory)
- [Dijkstra's Algorithm Visualization](https://www.cs.usfca.edu/~galles/visualization/Dijkstra.html)
- [Introduction to Graph Algorithms](https://www.geeksforgeeks.org/graph-data-structure-and-algorithms/)
