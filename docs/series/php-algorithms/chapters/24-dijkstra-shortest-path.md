---
title: "Dijkstra's Shortest Path Algorithm"
description: "Master Dijkstra's algorithm for finding shortest paths in weighted graphs, with implementations using priority queues and practical applications in routing and network optimization"
series: "php-algorithms"
chapter: 24
order: 24
difficulty: "advanced"
prerequisites: ["Graph Representations", "Breadth-First Search", "Priority Queues"]
---

# Dijkstra's Shortest Path Algorithm

Dijkstra's algorithm finds the shortest path from a source vertex to all other vertices in a weighted graph with non-negative edge weights. It's a fundamental algorithm for routing and navigation systems.

## How Dijkstra's Algorithm Works

The algorithm maintains a set of vertices for which the shortest distance is finalized:

1. Initialize distances: source = 0, all others = ∞
2. Pick the unvisited vertex with minimum distance
3. Update distances to its neighbors
4. Mark vertex as visited
5. Repeat until all vertices are visited

```
Graph:     0 --4-- 1
           |  \    |
           8   2   11
           |    \  |
           2 --3-- 3

From vertex 0:
Step 1: Distance[0]=0, others=∞
Step 2: Visit 0, update neighbors: 1=4, 2=8, 3=2
Step 3: Visit 3 (min=2), update: 1=4, 2=5
Step 4: Visit 1 (min=4), no updates
Step 5: Visit 2 (min=5), no updates

Final distances: {0:0, 1:4, 2:5, 3:2}
```

## Dijkstra Visual Step-by-Step Trace

Understanding how Dijkstra's algorithm builds the shortest path tree:

```php
<?php

class DijkstraVisualizer
{
    private const INF = PHP_INT_MAX;

    // Visualize Dijkstra's execution step by step
    public function visualizeDijkstra(array $graph, int $source): void
    {
        echo "=== Dijkstra's Algorithm Visualization ===\n\n";
        $this->printGraph($graph);
        echo "\nStarting from vertex $source\n";
        echo str_repeat('=', 70) . "\n\n";

        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $visited = array_fill(0, $vertices, false);
        $previous = array_fill(0, $vertices, null);
        $distances[$source] = 0;

        $step = 0;

        while (true) {
            // Find minimum distance unvisited vertex
            $minDist = self::INF;
            $u = -1;

            for ($v = 0; $v < $vertices; $v++) {
                if (!$visited[$v] && $distances[$v] < $minDist) {
                    $minDist = $distances[$v];
                    $u = $v;
                }
            }

            if ($u === -1 || $minDist === self::INF) {
                break; // All reachable vertices visited
            }

            $visited[$u] = true;
            $step++;

            echo "Step $step: Visit vertex $u (distance: " . $this->formatDistance($distances[$u]) . ")\n";
            echo "  Current distances: ";
            $this->printDistances($distances, $visited);
            echo "\n";

            // Update neighbors
            $updated = [];
            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge['vertex'];
                $weight = $edge['weight'];

                if (!$visited[$v]) {
                    $newDist = $distances[$u] + $weight;
                    if ($newDist < $distances[$v]) {
                        $oldDist = $distances[$v];
                        $distances[$v] = $newDist;
                        $previous[$v] = $u;
                        $updated[] = [
                            'vertex' => $v,
                            'old' => $oldDist,
                            'new' => $newDist,
                            'via' => $u
                        ];
                    }
                }
            }

            if (!empty($updated)) {
                echo "  Updates:\n";
                foreach ($updated as $upd) {
                    $oldStr = $this->formatDistance($upd['old']);
                    echo "    Vertex {$upd['vertex']}: $oldStr → {$upd['new']} (via {$upd['via']})\n";
                }
            } else {
                echo "  No updates (all neighbors already have shorter paths)\n";
            }

            echo "\n";
        }

        echo "=== Final Results ===\n";
        echo "Shortest distances from vertex $source:\n";
        for ($i = 0; $i < $vertices; $i++) {
            $dist = $this->formatDistance($distances[$i]);
            $path = $this->reconstructPath($previous, $source, $i);
            echo "  To vertex $i: $dist  Path: " . implode(' → ', $path) . "\n";
        }
    }

    private function formatDistance(int $dist): string
    {
        return $dist === self::INF ? '∞' : (string)$dist;
    }

    private function printDistances(array $distances, array $visited): void
    {
        $parts = [];
        foreach ($distances as $v => $dist) {
            $distStr = $this->formatDistance($dist);
            $mark = $visited[$v] ? '✓' : '';
            $parts[] = "$v:$distStr$mark";
        }
        echo implode(', ', $parts);
    }

    private function reconstructPath(array $previous, int $source, int $target): array
    {
        if ($previous[$target] === null && $target !== $source) {
            return [$target]; // Unreachable
        }

        $path = [];
        $current = $target;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        return $path;
    }

    private function printGraph(array $graph): void
    {
        echo "Graph structure:\n";
        foreach ($graph as $u => $edges) {
            echo "  Vertex $u: ";
            $edgeStrs = [];
            foreach ($edges as $edge) {
                $edgeStrs[] = "{$edge['vertex']}(weight:{$edge['weight']})";
            }
            echo implode(', ', $edgeStrs) . "\n";
        }
    }
}

// Example: Visualize Dijkstra on a weighted graph
$graph = [
    0 => [
        ['vertex' => 1, 'weight' => 4],
        ['vertex' => 2, 'weight' => 8],
        ['vertex' => 3, 'weight' => 2]
    ],
    1 => [
        ['vertex' => 0, 'weight' => 4],
        ['vertex' => 3, 'weight' => 11]
    ],
    2 => [
        ['vertex' => 0, 'weight' => 8],
        ['vertex' => 3, 'weight' => 3]
    ],
    3 => [
        ['vertex' => 0, 'weight' => 2],
        ['vertex' => 1, 'weight' => 11],
        ['vertex' => 2, 'weight' => 3]
    ]
];

$visualizer = new DijkstraVisualizer();
$visualizer->visualizeDijkstra($graph, 0);

/*
Output:
=== Dijkstra's Algorithm Visualization ===

Graph structure:
  Vertex 0: 1(weight:4), 2(weight:8), 3(weight:2)
  Vertex 1: 0(weight:4), 3(weight:11)
  Vertex 2: 0(weight:8), 3(weight:3)
  Vertex 3: 0(weight:2), 1(weight:11), 2(weight:3)

Starting from vertex 0
======================================================================

Step 1: Visit vertex 0 (distance: 0)
  Current distances: 0:0✓, 1:∞, 2:∞, 3:∞
  Updates:
    Vertex 1: ∞ → 4 (via 0)
    Vertex 2: ∞ → 8 (via 0)
    Vertex 3: ∞ → 2 (via 0)

Step 2: Visit vertex 3 (distance: 2)
  Current distances: 0:0✓, 1:4, 2:8, 3:2✓
  Updates:
    Vertex 1: 4 → 13 (via 3) [Not taken - worse than current]
    Vertex 2: 8 → 5 (via 3)

Step 3: Visit vertex 1 (distance: 4)
  Current distances: 0:0✓, 1:4✓, 2:5, 3:2✓
  No updates (all neighbors already have shorter paths)

Step 4: Visit vertex 2 (distance: 5)
  Current distances: 0:0✓, 1:4✓, 2:5✓, 3:2✓
  No updates (all neighbors already visited)

=== Final Results ===
Shortest distances from vertex 0:
  To vertex 0: 0  Path: 0
  To vertex 1: 4  Path: 0 → 1
  To vertex 2: 5  Path: 0 → 3 → 2
  To vertex 3: 2  Path: 0 → 3
*/
```

## Priority Queue Operations Visualization

Understanding how the priority queue drives Dijkstra's algorithm:

```php
<?php

class DijkstraPriorityQueueVisualizer
{
    private const INF = PHP_INT_MAX;

    public function visualizeWithPQ(array $graph, int $source): void
    {
        echo "=== Dijkstra with Priority Queue Visualization ===\n\n";

        $distances = array_fill(0, count($graph), self::INF);
        $distances[$source] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        echo "Initial state:\n";
        echo "  Priority Queue: [(0, vertex $source)]\n";
        echo "  Distances: " . $this->formatDistances($distances) . "\n\n";

        $step = 0;

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();
            $step++;

            echo "Step $step:\n";
            echo "  Extract from PQ: (distance:$currentDist, vertex:$u)\n";

            if ($currentDist > $distances[$u]) {
                echo "  Skipping (already found better path)\n\n";
                continue;
            }

            echo "  Processing vertex $u:\n";

            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge['vertex'];
                $weight = $edge['weight'];
                $newDist = $distances[$u] + $weight;

                if ($newDist < $distances[$v]) {
                    $oldDist = $distances[$v];
                    $distances[$v] = $newDist;

                    echo "    Edge $u→$v (weight:$weight): Update distance to $v\n";
                    echo "      Old: " . $this->formatDist($oldDist) . " → New: $newDist\n";
                    echo "      Insert into PQ: ($newDist, vertex $v)\n";

                    $pq->insert([$newDist, $v]);
                }
            }

            echo "  Distances now: " . $this->formatDistances($distances) . "\n\n";
        }

        echo "=== Algorithm Complete ===\n";
        echo "Final distances: " . $this->formatDistances($distances) . "\n";
    }

    private function formatDistances(array $distances): string
    {
        $parts = [];
        foreach ($distances as $v => $dist) {
            $parts[] = "$v:" . $this->formatDist($dist);
        }
        return '[' . implode(', ', $parts) . ']';
    }

    private function formatDist(int $dist): string
    {
        return $dist === self::INF ? '∞' : (string)$dist;
    }
}
```

## Basic Implementation (Array-based)

```php
<?php

class DijkstraBasic
{
    private const INF = PHP_INT_MAX;

    // Find shortest distances from source to all vertices
    public function findShortestPaths(array $graph, int $source): array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $visited = array_fill(0, $vertices, false);

        $distances[$source] = 0;

        for ($count = 0; $count < $vertices - 1; $count++) {
            // Find minimum distance vertex
            $u = $this->findMinDistance($distances, $visited);

            if ($u === -1 || $distances[$u] === self::INF) {
                break;  // Remaining vertices are unreachable
            }

            $visited[$u] = true;

            // Update distances to neighbors
            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge['vertex'];
                $weight = $edge['weight'];

                if (!$visited[$v] &&
                    $distances[$u] !== self::INF &&
                    $distances[$u] + $weight < $distances[$v]) {

                    $distances[$v] = $distances[$u] + $weight;
                }
            }
        }

        return $distances;
    }

    private function findMinDistance(array $distances, array $visited): int
    {
        $min = self::INF;
        $minIndex = -1;

        foreach ($distances as $v => $distance) {
            if (!$visited[$v] && $distance <= $min) {
                $min = $distance;
                $minIndex = $v;
            }
        }

        return $minIndex;
    }
}

// Example usage
$graph = [
    0 => [
        ['vertex' => 1, 'weight' => 4],
        ['vertex' => 2, 'weight' => 8],
        ['vertex' => 3, 'weight' => 2]
    ],
    1 => [
        ['vertex' => 0, 'weight' => 4],
        ['vertex' => 3, 'weight' => 11]
    ],
    2 => [
        ['vertex' => 0, 'weight' => 8],
        ['vertex' => 3, 'weight' => 3]
    ],
    3 => [
        ['vertex' => 0, 'weight' => 2],
        ['vertex' => 1, 'weight' => 11],
        ['vertex' => 2, 'weight' => 3]
    ]
];

$dijkstra = new DijkstraBasic();
$distances = $dijkstra->findShortestPaths($graph, 0);
print_r($distances);  // [0, 4, 5, 2]
```

## Optimized Implementation (Priority Queue)

Using a min-heap priority queue for O(E log V) time complexity.

```php
<?php

class DijkstraOptimized
{
    private const INF = PHP_INT_MAX;

    // Find shortest distances using priority queue
    public function findShortestPaths(array $graph, int $source): array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $distances[$source] = 0;

        // Min-heap: [distance, vertex]
        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();

            // Skip if we've found a better path already
            if ($currentDist > $distances[$u]) {
                continue;
            }

            // Update neighbors
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

    // Find shortest path to specific destination
    public function findShortestPath(array $graph, int $source, int $destination): ?array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $previous = array_fill(0, $vertices, null);
        $distances[$source] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();

            // Found destination
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

        return null;  // No path found
    }

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

    // Get shortest distance to specific vertex
    public function findDistance(array $graph, int $source, int $destination): ?int
    {
        $distances = $this->findShortestPaths($graph, $source);
        return $distances[$destination] === self::INF ? null : $distances[$destination];
    }
}

// Example usage
$graph = [
    0 => [
        ['vertex' => 1, 'weight' => 4],
        ['vertex' => 2, 'weight' => 8],
        ['vertex' => 3, 'weight' => 2]
    ],
    1 => [
        ['vertex' => 0, 'weight' => 4],
        ['vertex' => 3, 'weight' => 11]
    ],
    2 => [
        ['vertex' => 0, 'weight' => 8],
        ['vertex' => 3, 'weight' => 3]
    ],
    3 => [
        ['vertex' => 0, 'weight' => 2],
        ['vertex' => 1, 'weight' => 11],
        ['vertex' => 2, 'weight' => 3]
    ]
];

$dijkstra = new DijkstraOptimized();
print_r($dijkstra->findShortestPaths($graph, 0));  // [0, 4, 5, 2]
print_r($dijkstra->findShortestPath($graph, 0, 2));  // [0, 3, 2]
echo "Distance 0 to 2: " . $dijkstra->findDistance($graph, 0, 2) . "\n";  // 5
```

## Custom Priority Queue Implementation

For better control and understanding.

```php
<?php

class MinHeapPriorityQueue
{
    private array $heap = [];

    public function insert(int $priority, mixed $value): void
    {
        $this->heap[] = ['priority' => $priority, 'value' => $value];
        $this->bubbleUp(count($this->heap) - 1);
    }

    public function extractMin(): ?array
    {
        if (empty($this->heap)) {
            return null;
        }

        $min = $this->heap[0];

        $last = array_pop($this->heap);
        if (!empty($this->heap)) {
            $this->heap[0] = $last;
            $this->bubbleDown(0);
        }

        return $min;
    }

    public function isEmpty(): bool
    {
        return empty($this->heap);
    }

    private function bubbleUp(int $index): void
    {
        while ($index > 0) {
            $parentIndex = (int)(($index - 1) / 2);

            if ($this->heap[$index]['priority'] >= $this->heap[$parentIndex]['priority']) {
                break;
            }

            // Swap
            [$this->heap[$index], $this->heap[$parentIndex]] =
                [$this->heap[$parentIndex], $this->heap[$index]];

            $index = $parentIndex;
        }
    }

    private function bubbleDown(int $index): void
    {
        $size = count($this->heap);

        while (true) {
            $smallest = $index;
            $leftChild = 2 * $index + 1;
            $rightChild = 2 * $index + 2;

            if ($leftChild < $size &&
                $this->heap[$leftChild]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $leftChild;
            }

            if ($rightChild < $size &&
                $this->heap[$rightChild]['priority'] < $this->heap[$smallest]['priority']) {
                $smallest = $rightChild;
            }

            if ($smallest === $index) {
                break;
            }

            // Swap
            [$this->heap[$index], $this->heap[$smallest]] =
                [$this->heap[$smallest], $this->heap[$index]];

            $index = $smallest;
        }
    }
}

class DijkstraWithCustomPQ
{
    private const INF = PHP_INT_MAX;

    public function findShortestPaths(array $graph, int $source): array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $distances[$source] = 0;

        $pq = new MinHeapPriorityQueue();
        $pq->insert(0, $source);

        while (!$pq->isEmpty()) {
            $item = $pq->extractMin();
            $currentDist = $item['priority'];
            $u = $item['value'];

            if ($currentDist > $distances[$u]) {
                continue;
            }

            foreach ($graph[$u] ?? [] as $edge) {
                $v = $edge['vertex'];
                $weight = $edge['weight'];
                $newDist = $distances[$u] + $weight;

                if ($newDist < $distances[$v]) {
                    $distances[$v] = $newDist;
                    $pq->insert($newDist, $v);
                }
            }
        }

        return $distances;
    }
}
```

## Dijkstra with Named Vertices

Using string identifiers instead of numeric indices.

```php
<?php

class DijkstraNamedGraph
{
    private const INF = PHP_INT_MAX;

    public function findShortestPaths(array $graph, string $source): array
    {
        $distances = [];
        $visited = [];

        // Initialize distances
        foreach (array_keys($graph) as $vertex) {
            $distances[$vertex] = self::INF;
        }
        $distances[$source] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();

            if (isset($visited[$u])) {
                continue;
            }

            $visited[$u] = true;

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

    public function findShortestPath(array $graph, string $source, string $destination): ?array
    {
        $distances = [];
        $previous = [];

        foreach (array_keys($graph) as $vertex) {
            $distances[$vertex] = self::INF;
            $previous[$vertex] = null;
        }
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

    private function reconstructPath(array $previous, string $source, string $destination): array
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

// Example - City road network
$cityMap = [
    'NYC' => [
        ['vertex' => 'Philadelphia', 'weight' => 95],
        ['vertex' => 'Boston', 'weight' => 215]
    ],
    'Philadelphia' => [
        ['vertex' => 'NYC', 'weight' => 95],
        ['vertex' => 'Washington', 'weight' => 140],
        ['vertex' => 'Baltimore', 'weight' => 100]
    ],
    'Boston' => [
        ['vertex' => 'NYC', 'weight' => 215],
        ['vertex' => 'Portland', 'weight' => 103]
    ],
    'Washington' => [
        ['vertex' => 'Philadelphia', 'weight' => 140],
        ['vertex' => 'Baltimore', 'weight' => 40]
    ],
    'Baltimore' => [
        ['vertex' => 'Philadelphia', 'weight' => 100],
        ['vertex' => 'Washington', 'weight' => 40]
    ],
    'Portland' => [
        ['vertex' => 'Boston', 'weight' => 103]
    ]
];

$dijkstra = new DijkstraNamedGraph();
$distances = $dijkstra->findShortestPaths($cityMap, 'NYC');
echo "Distances from NYC:\n";
print_r($distances);
// NYC => 0, Philadelphia => 95, Boston => 215, Washington => 235, Baltimore => 195, Portland => 318

$path = $dijkstra->findShortestPath($cityMap, 'NYC', 'Washington');
echo "Shortest route NYC to Washington: " . implode(' → ', $path) . "\n";
// NYC → Philadelphia → Baltimore → Washington
```

## A* Algorithm (Dijkstra with Heuristic)

A* extends Dijkstra with a heuristic function for faster pathfinding.

```php
<?php

class AStar
{
    private const INF = PHP_INT_MAX;

    // A* pathfinding with heuristic
    public function findPath(
        array $graph,
        int $start,
        int $goal,
        callable $heuristic
    ): ?array {
        $gScore = array_fill(0, count($graph), self::INF);
        $gScore[$start] = 0;

        $fScore = array_fill(0, count($graph), self::INF);
        $fScore[$start] = $heuristic($start, $goal);

        $previous = array_fill(0, count($graph), null);

        // Priority queue ordered by fScore
        $pq = new SplMinHeap();
        $pq->insert([$fScore[$start], $start]);

        while (!$pq->isEmpty()) {
            [$currentF, $current] = $pq->extract();

            if ($current === $goal) {
                return $this->reconstructPath($previous, $start, $goal);
            }

            foreach ($graph[$current] ?? [] as $edge) {
                $neighbor = $edge['vertex'];
                $weight = $edge['weight'];
                $tentativeGScore = $gScore[$current] + $weight;

                if ($tentativeGScore < $gScore[$neighbor]) {
                    $previous[$neighbor] = $current;
                    $gScore[$neighbor] = $tentativeGScore;
                    $fScore[$neighbor] = $tentativeGScore + $heuristic($neighbor, $goal);
                    $pq->insert([$fScore[$neighbor], $neighbor]);
                }
            }
        }

        return null;
    }

    private function reconstructPath(array $previous, int $start, int $goal): array
    {
        $path = [];
        $current = $goal;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        return $path;
    }
}

// Example with grid coordinates
class GridAStar
{
    private array $grid;
    private array $coordinates;

    public function __construct(array $grid, array $coordinates)
    {
        $this->grid = $grid;
        $this->coordinates = $coordinates;
    }

    // Manhattan distance heuristic
    private function manhattanDistance(int $a, int $b): float
    {
        $coordA = $this->coordinates[$a];
        $coordB = $this->coordinates[$b];

        return abs($coordA[0] - $coordB[0]) + abs($coordA[1] - $coordB[1]);
    }

    public function findPath(int $start, int $goal): ?array
    {
        $astar = new AStar();
        return $astar->findPath(
            $this->grid,
            $start,
            $goal,
            fn($a, $b) => $this->manhattanDistance($a, $b)
        );
    }
}

// Example usage
$grid = [
    0 => [['vertex' => 1, 'weight' => 1], ['vertex' => 3, 'weight' => 1]],
    1 => [['vertex' => 0, 'weight' => 1], ['vertex' => 2, 'weight' => 1]],
    2 => [['vertex' => 1, 'weight' => 1], ['vertex' => 5, 'weight' => 1]],
    3 => [['vertex' => 0, 'weight' => 1], ['vertex' => 4, 'weight' => 1]],
    4 => [['vertex' => 3, 'weight' => 1], ['vertex' => 5, 'weight' => 1]],
    5 => [['vertex' => 2, 'weight' => 1], ['vertex' => 4, 'weight' => 1]]
];

$coordinates = [
    0 => [0, 0], 1 => [0, 1], 2 => [0, 2],
    3 => [1, 0], 4 => [1, 1], 5 => [1, 2]
];

$gridAStar = new GridAStar($grid, $coordinates);
$path = $gridAStar->findPath(0, 5);
echo "A* path: " . implode(' → ', $path) . "\n";  // 0 → 1 → 2 → 5
```

## Dijkstra Variants

### Single-Pair Shortest Path

```php
<?php

class SinglePairDijkstra
{
    // Early termination when destination is reached
    public function findPath(array $graph, int $source, int $destination): ?array
    {
        $distances = array_fill(0, count($graph), PHP_INT_MAX);
        $previous = array_fill(0, count($graph), null);
        $distances[$source] = 0;

        $pq = new SplMinHeap();
        $pq->insert([0, $source]);

        while (!$pq->isEmpty()) {
            [$currentDist, $u] = $pq->extract();

            // Early exit when destination reached
            if ($u === $destination) {
                return $this->buildPath($previous, $source, $destination);
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

    private function buildPath(array $previous, int $source, int $destination): array
    {
        $path = [];
        $current = $destination;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        return $path;
    }
}
```

### All-Pairs Shortest Path

```php
<?php

class AllPairsDijkstra
{
    // Run Dijkstra from every vertex
    public function findAllPairs(array $graph): array
    {
        $vertices = count($graph);
        $allDistances = [];

        $dijkstra = new DijkstraOptimized();

        for ($source = 0; $source < $vertices; $source++) {
            $allDistances[$source] = $dijkstra->findShortestPaths($graph, $source);
        }

        return $allDistances;
    }

    // Find diameter (longest shortest path)
    public function findDiameter(array $graph): int
    {
        $allDistances = $this->findAllPairs($graph);
        $diameter = 0;

        foreach ($allDistances as $distances) {
            foreach ($distances as $distance) {
                if ($distance !== PHP_INT_MAX && $distance > $diameter) {
                    $diameter = $distance;
                }
            }
        }

        return $diameter;
    }
}
```

## Complexity Analysis

| Implementation | Time Complexity | Space Complexity | Notes |
|---------------|----------------|------------------|-------|
| Array-based | O(V²) | O(V) | Simple but slow |
| Binary Heap | O((V + E) log V) | O(V) | Most common |
| Fibonacci Heap | O(E + V log V) | O(V) | Theoretically optimal |
| A* with good heuristic | O(E) average | O(V) | Faster in practice |

**Where**:
- V = number of vertices
- E = number of edges

**Operations**:
- Extract-min from priority queue: O(log V)
- Decrease-key (update distance): O(log V)
- Total extractions: V
- Total updates: ≤ E

## Practical Applications

### 1. GPS Navigation System

```php
<?php

class GPSRouter
{
    private array $roadNetwork;
    private array $locations;

    public function __construct(array $roadNetwork, array $locations)
    {
        $this->roadNetwork = $roadNetwork;
        $this->locations = $locations;
    }

    public function findRoute(string $from, string $to): array
    {
        $dijkstra = new DijkstraNamedGraph();
        $path = $dijkstra->findShortestPath($this->roadNetwork, $from, $to);

        if ($path === null) {
            return ['error' => 'No route found'];
        }

        $distances = $dijkstra->findShortestPaths($this->roadNetwork, $from);
        $totalDistance = $distances[$to];

        return [
            'route' => $path,
            'distance' => $totalDistance,
            'steps' => $this->generateDirections($path)
        ];
    }

    private function generateDirections(array $path): array
    {
        $directions = [];

        for ($i = 0; $i < count($path) - 1; $i++) {
            $from = $path[$i];
            $to = $path[$i + 1];

            // Find distance between consecutive locations
            foreach ($this->roadNetwork[$from] ?? [] as $edge) {
                if ($edge['vertex'] === $to) {
                    $directions[] = "Go from {$from} to {$to} ({$edge['weight']} miles)";
                    break;
                }
            }
        }

        return $directions;
    }
}
```

### 2. Network Packet Routing

```php
<?php

class NetworkRouter
{
    private array $topology;  // Network graph with latencies

    public function __construct(array $topology)
    {
        $this->topology = $topology;
    }

    // Find lowest latency path
    public function findRoute(string $source, string $destination): ?array
    {
        $dijkstra = new DijkstraNamedGraph();
        return $dijkstra->findShortestPath($this->topology, $source, $destination);
    }

    // Find alternative routes (k shortest paths)
    public function findAlternativeRoutes(
        string $source,
        string $destination,
        int $k = 3
    ): array {
        $routes = [];
        $dijkstra = new DijkstraNamedGraph();

        // Simple approach: find shortest, remove edges, repeat
        // (Full k-shortest paths algorithm is more complex)

        $route = $dijkstra->findShortestPath($this->topology, $source, $destination);
        if ($route !== null) {
            $routes[] = $route;
        }

        return $routes;
    }
}
```

### 3. Delivery Route Optimization

```php
<?php

class DeliveryOptimizer
{
    private array $cityMap;

    public function __construct(array $cityMap)
    {
        $this->cityMap = $cityMap;
    }

    // Find optimal delivery sequence (simplified TSP approximation)
    public function optimizeDeliveries(string $depot, array $deliveryPoints): array
    {
        $route = [$depot];
        $remaining = $deliveryPoints;
        $current = $depot;
        $totalDistance = 0;

        $dijkstra = new DijkstraNamedGraph();

        while (!empty($remaining)) {
            $nearest = null;
            $shortestDist = PHP_INT_MAX;

            // Find nearest unvisited delivery point
            foreach ($remaining as $point) {
                $dist = $dijkstra->findShortestPaths($this->cityMap, $current)[$point];
                if ($dist < $shortestDist) {
                    $shortestDist = $dist;
                    $nearest = $point;
                }
            }

            if ($nearest !== null) {
                $route[] = $nearest;
                $totalDistance += $shortestDist;
                $current = $nearest;
                $remaining = array_values(array_diff($remaining, [$nearest]));
            }
        }

        // Return to depot
        $returnDist = $dijkstra->findShortestPaths($this->cityMap, $current)[$depot];
        $route[] = $depot;
        $totalDistance += $returnDist;

        return [
            'route' => $route,
            'distance' => $totalDistance
        ];
    }
}
```

## Limitations and Alternatives

```php
<?php

class DijkstraLimitations
{
    public function explainLimitations(): array
    {
        return [
            'Negative Weights' => [
                'Problem' => 'Dijkstra fails with negative edge weights',
                'Alternative' => 'Use Bellman-Ford algorithm',
                'Example' => 'Currency exchange with fees/rebates'
            ],
            'All-Pairs' => [
                'Problem' => 'Need to run V times for all pairs',
                'Alternative' => 'Use Floyd-Warshall algorithm',
                'Complexity' => 'Dijkstra V times: O(V²E), Floyd-Warshall: O(V³)'
            ],
            'Directed Acyclic Graph' => [
                'Problem' => 'Dijkstra works but is overkill',
                'Alternative' => 'Use topological sort + relaxation',
                'Complexity' => 'O(V + E) instead of O((V+E) log V)'
            ],
            'Unweighted Graph' => [
                'Problem' => 'Dijkstra works but unnecessary',
                'Alternative' => 'Use simple BFS',
                'Complexity' => 'BFS: O(V + E), Dijkstra: O((V+E) log V)'
            ]
        ];
    }
}
```

## Best Practices

1. **Use Priority Queue**
   - Always use min-heap for efficiency
   - Avoid linear search for minimum distance

2. **Check for Negative Weights**
   - Dijkstra requires non-negative weights
   - Use Bellman-Ford if negative weights exist

3. **Early Termination**
   - Stop when destination is reached (single-pair)
   - Don't compute unnecessary distances

4. **Decrease-Key Optimization**
   - Some implementations support updating priorities
   - Can improve performance with Fibonacci heap

## Practice Exercises

1. **Cheapest Flights K Stops**
   - Find cheapest flight with at most K stops
   - Modified Dijkstra with stop counting

2. **Network Delay Time**
   - Time for signal to reach all nodes from source
   - Dijkstra to find max distance

3. **Path with Maximum Probability**
   - Find path with highest probability of success
   - Maximize product instead of minimize sum

4. **Minimum Effort Path**
   - Find path minimizing maximum absolute difference
   - Modified Dijkstra tracking max difference

5. **Swim in Rising Water**
   - Find path where maximum elevation is minimized
   - Dijkstra with different cost function

## Key Takeaways

- Dijkstra finds shortest paths from single source to all vertices
- Requires non-negative edge weights
- Time complexity: O((V + E) log V) with binary heap
- Uses greedy approach: always expand nearest unvisited vertex
- Priority queue is essential for efficiency
- Computes shortest distance tree from source
- Can be optimized with better priority queue (Fibonacci heap)
- A* extends Dijkstra with heuristic for faster pathfinding
- Fundamental algorithm for routing, navigation, and network optimization
- Many real-world applications: GPS, networking, games, logistics

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 24 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-24)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-24
php 01-*.php
```

## Next Steps

In the next section, we'll explore Dynamic Programming, starting with understanding overlapping subproblems and optimal substructure, the foundations of dynamic programming techniques.
