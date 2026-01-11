---
title: "24: Dijkstra's Shortest Path Algorithm"
description: "Master Dijkstra's algorithm for finding shortest paths in weighted graphs, with implementations using priority queues and practical applications in routing and network optimization"
series: "php-algorithms"
chapter: 24
order: 24
difficulty: "Advanced"
prerequisites:
  - "/series/php-algorithms/chapters/21-graph-representations"
  - "/series/php-algorithms/chapters/23-breadth-first-search"
estimatedTime: "PT65M"
teaches:
  - 'Master Dijkstra''s algorithm for shortest path finding in weighted graphs'
  - 'Implement efficient priority queue-based solutions'
  - 'Understand greedy algorithm design and optimality proofs'
  - 'Build practical applications: GPS routing, network optimization'
  - 'Analyze time complexity with different data structure choices'
---
![Dijkstra's Shortest Path Algorithm](/images/php-algorithms/chapter-24-dijkstra-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 24</span>
</div>

# 24: Dijkstra's Shortest Path Algorithm <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Dijkstra's algorithm finds the shortest path from a source vertex to all other vertices in a weighted graph with non-negative edge weights. Think of it as BFS's smarter cousin that handles weighted edges—it powers GPS navigation, network routing protocols, and game pathfinding. It's a fundamental algorithm for routing and navigation systems that every developer should know.

Unlike Breadth-First Search which finds shortest paths in unweighted graphs, Dijkstra's algorithm handles weighted edges by using a greedy approach: it always expands the vertex with the minimum distance first. This guarantees optimal shortest paths when all edge weights are non-negative.

In this chapter, you'll master Dijkstra's algorithm implementation using priority queues, learn how to reconstruct shortest paths, understand the algorithm's time complexity trade-offs, and build practical applications like GPS routing systems and network optimization tools.

## Prerequisites

Before starting this chapter, you should have:

- ✓ Complete understanding of graph representations ([Chapter 21](/series/php-algorithms/chapters/21-graph-representations))
- ✓ Mastery of BFS for unweighted shortest paths ([Chapter 23](/series/php-algorithms/chapters/23-breadth-first-search))
- ✓ Knowledge of priority queues and heaps (covered in this chapter)
- ✓ Understanding of greedy algorithm principles

**Estimated Time**: ~65 minutes

## Quick Start

Let's find the shortest path in a weighted graph using Dijkstra's algorithm:

```php
# filename: quick-start.php
<?php

declare(strict_types=1);

// Simple weighted graph: [vertex => [['vertex' => neighbor, 'weight' => cost], ...]]
$graph = [
    0 => [['vertex' => 1, 'weight' => 4], ['vertex' => 2, 'weight' => 1]],
    1 => [['vertex' => 3, 'weight' => 1]],
    2 => [['vertex' => 1, 'weight' => 2], ['vertex' => 3, 'weight' => 5]],
    3 => []
];

// Simple Dijkstra implementation
function dijkstra(array $graph, int $source): array
{
    $distances = [];
    $visited = [];
    
    // Initialize distances
    foreach (array_keys($graph) as $vertex) {
        $distances[$vertex] = PHP_INT_MAX;
    }
    $distances[$source] = 0;
    
    // Priority queue: [distance, vertex]
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

// Find shortest distances from vertex 0
$distances = dijkstra($graph, 0);

echo "Shortest distances from vertex 0:\n";
foreach ($distances as $vertex => $distance) {
    $dist = $distance === PHP_INT_MAX ? '∞' : (string)$distance;
    echo "  To vertex $vertex: $dist\n";
}
```

Expected output:

```
Shortest distances from vertex 0:
  To vertex 0: 0
  To vertex 1: 3
  To vertex 2: 1
  To vertex 3: 4
```

This shows the shortest path from vertex 0: directly to vertex 2 (cost 1), then to vertex 1 (cost 3 total), and finally to vertex 3 (cost 4 total).

## What You'll Build

By the end of this chapter, you will have created:

- A complete Dijkstra's algorithm implementation using priority queues
- Shortest path finder for weighted graphs with path reconstruction
- GPS routing system for finding optimal routes between locations
- Network packet routing system with latency optimization
- Delivery route optimizer using Dijkstra's algorithm
- A* pathfinding algorithm implementation with heuristics

## Objectives

- Master Dijkstra's algorithm for shortest path finding in weighted graphs
- Implement efficient priority queue-based solutions
- Understand greedy algorithm design and optimality proofs
- Build practical applications: GPS routing, network optimization
- Analyze time complexity with different data structure choices

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
# filename: dijkstra-visualizer.php
<?php

declare(strict_types=1);

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
# filename: dijkstra-pq-visualizer.php
<?php

declare(strict_types=1);

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
# filename: dijkstra-basic.php
<?php

declare(strict_types=1);

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
# filename: dijkstra-optimized.php
<?php

declare(strict_types=1);

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
# filename: custom-priority-queue.php
<?php

declare(strict_types=1);

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
# filename: dijkstra-named-graph.php
<?php

declare(strict_types=1);

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
# filename: astar-pathfinding.php
<?php

declare(strict_types=1);

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
# filename: single-pair-dijkstra.php
<?php

declare(strict_types=1);

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
# filename: all-pairs-dijkstra.php
<?php

declare(strict_types=1);

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

### Bidirectional Dijkstra

Searching from both source and destination simultaneously can significantly speed up single-pair shortest path queries, especially in large graphs. The algorithm stops when the two search frontiers meet.

```php
# filename: bidirectional-dijkstra.php
<?php

class BidirectionalDijkstra
{
    private const INF = PHP_INT_MAX;

    public function findPath(array $graph, int $source, int $destination): ?array
    {
        if ($source === $destination) {
            return [$source];
        }

        $vertices = count($graph);
        
        // Forward search (from source)
        $distForward = array_fill(0, $vertices, self::INF);
        $prevForward = array_fill(0, $vertices, null);
        $distForward[$source] = 0;
        $pqForward = new SplMinHeap();
        $pqForward->insert([0, $source]);
        $visitedForward = [];

        // Backward search (from destination)
        $distBackward = array_fill(0, $vertices, self::INF);
        $prevBackward = array_fill(0, $vertices, null);
        $distBackward[$destination] = 0;
        $pqBackward = new SplMinHeap();
        $pqBackward->insert([0, $destination]);
        $visitedBackward = [];

        $bestDistance = self::INF;
        $meetingVertex = -1;

        while (!$pqForward->isEmpty() || !$pqBackward->isEmpty()) {
            // Forward step
            if (!$pqForward->isEmpty()) {
                [$currentDist, $u] = $pqForward->extract();

                if (isset($visitedForward[$u]) || $currentDist > $distForward[$u]) {
                    continue;
                }

                $visitedForward[$u] = true;

                // Check if we've met backward search
                if (isset($visitedBackward[$u])) {
                    $totalDist = $distForward[$u] + $distBackward[$u];
                    if ($totalDist < $bestDistance) {
                        $bestDistance = $totalDist;
                        $meetingVertex = $u;
                    }
                }

                foreach ($graph[$u] ?? [] as $edge) {
                    $v = $edge['vertex'];
                    $weight = $edge['weight'];
                    $newDist = $distForward[$u] + $weight;

                    if ($newDist < $distForward[$v]) {
                        $distForward[$v] = $newDist;
                        $prevForward[$v] = $u;
                        $pqForward->insert([$newDist, $v]);
                    }
                }
            }

            // Backward step
            if (!$pqBackward->isEmpty()) {
                [$currentDist, $u] = $pqBackward->extract();

                if (isset($visitedBackward[$u]) || $currentDist > $distBackward[$u]) {
                    continue;
                }

                $visitedBackward[$u] = true;

                // Check if we've met forward search
                if (isset($visitedForward[$u])) {
                    $totalDist = $distForward[$u] + $distBackward[$u];
                    if ($totalDist < $bestDistance) {
                        $bestDistance = $totalDist;
                        $meetingVertex = $u;
                    }
                }

                foreach ($graph[$u] ?? [] as $edge) {
                    $v = $edge['vertex'];
                    $weight = $edge['weight'];
                    $newDist = $distBackward[$u] + $weight;

                    if ($newDist < $distBackward[$v]) {
                        $distBackward[$v] = $newDist;
                        $prevBackward[$v] = $u;
                        $pqBackward->insert([$newDist, $v]);
                    }
                }
            }

            // Early termination if best path found
            if ($meetingVertex !== -1) {
                break;
            }
        }

        if ($meetingVertex === -1) {
            return null; // No path found
        }

        // Reconstruct path: source → meeting → destination
        $pathForward = $this->reconstructPath($prevForward, $source, $meetingVertex);
        $pathBackward = $this->reconstructPath($prevBackward, $destination, $meetingVertex);
        array_reverse($pathBackward);
        array_pop($pathBackward); // Remove duplicate meeting vertex

        return array_merge($pathForward, $pathBackward);
    }

    private function reconstructPath(array $previous, int $start, int $end): array
    {
        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $previous[$current];
        }

        return $path;
    }
}

// Example usage
$graph = [
    0 => [['vertex' => 1, 'weight' => 4], ['vertex' => 2, 'weight' => 8]],
    1 => [['vertex' => 0, 'weight' => 4], ['vertex' => 3, 'weight' => 11]],
    2 => [['vertex' => 0, 'weight' => 8], ['vertex' => 3, 'weight' => 3]],
    3 => [['vertex' => 1, 'weight' => 11], ['vertex' => 2, 'weight' => 3]]
];

$bidijkstra = new BidirectionalDijkstra();
$path = $bidijkstra->findPath($graph, 0, 3);
echo "Bidirectional path: " . implode(' → ', $path) . "\n";  // 0 → 2 → 3
```

### Multi-Source Dijkstra

Starting Dijkstra from multiple source vertices simultaneously. Useful for finding the nearest source to each vertex, such as finding the nearest hospital or server.

```php
# filename: multi-source-dijkstra.php
<?php

class MultiSourceDijkstra
{
    private const INF = PHP_INT_MAX;

    // Find shortest distances from any source to all vertices
    public function findDistances(array $graph, array $sources): array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $pq = new SplMinHeap();

        // Initialize all sources with distance 0
        foreach ($sources as $source) {
            $distances[$source] = 0;
            $pq->insert([0, $source]);
        }

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

    // Find nearest source for each vertex
    public function findNearestSource(array $graph, array $sources): array
    {
        $vertices = count($graph);
        $distances = array_fill(0, $vertices, self::INF);
        $nearestSource = array_fill(0, $vertices, null);
        $pq = new SplMinHeap();

        // Initialize all sources
        foreach ($sources as $source) {
            $distances[$source] = 0;
            $nearestSource[$source] = $source;
            $pq->insert([0, $source]);
        }

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
                    $nearestSource[$v] = $nearestSource[$u];
                    $pq->insert([$newDist, $v]);
                }
            }
        }

        return $nearestSource;
    }
}

// Example - Multiple hospitals serving areas
$cityMap = [
    0 => [['vertex' => 1, 'weight' => 2], ['vertex' => 2, 'weight' => 3]],
    1 => [['vertex' => 0, 'weight' => 2], ['vertex' => 3, 'weight' => 1]],
    2 => [['vertex' => 0, 'weight' => 3], ['vertex' => 3, 'weight' => 2], ['vertex' => 4, 'weight' => 1]],
    3 => [['vertex' => 1, 'weight' => 1], ['vertex' => 2, 'weight' => 2], ['vertex' => 4, 'weight' => 1], ['vertex' => 5, 'weight' => 3]],
    4 => [['vertex' => 2, 'weight' => 1], ['vertex' => 3, 'weight' => 1], ['vertex' => 6, 'weight' => 2]],
    5 => [['vertex' => 3, 'weight' => 3], ['vertex' => 6, 'weight' => 1]],
    6 => [['vertex' => 4, 'weight' => 2], ['vertex' => 5, 'weight' => 1]]
];

$hospitals = [0, 5];  // Hospitals at vertices 0 and 5

$multiDijkstra = new MultiSourceDijkstra();
$distances = $multiDijkstra->findDistances($cityMap, $hospitals);
echo "Distance to nearest hospital:\n";
print_r($distances);
// [0 => 0, 1 => 2, 2 => 3, 3 => 3, 4 => 4, 5 => 0, 6 => 1]

$nearest = $multiDijkstra->findNearestSource($cityMap, $hospitals);
echo "Nearest hospital for each location:\n";
print_r($nearest);
// [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 5, 6 => 5]
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
# filename: gps-router.php
<?php

declare(strict_types=1);

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
# filename: network-router.php
<?php

declare(strict_types=1);

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
# filename: delivery-optimizer.php
<?php

declare(strict_types=1);

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
# filename: dijkstra-limitations.php
<?php

declare(strict_types=1);

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

## Edge Cases and Graph Considerations

### Handling Self-Loops

Self-loops (edges from a vertex to itself) are handled naturally by Dijkstra:

```php
# filename: dijkstra-self-loops.php
<?php

// Graph with self-loop
$graph = [
    0 => [
        ['vertex' => 0, 'weight' => 5],  // Self-loop
        ['vertex' => 1, 'weight' => 3]
    ],
    1 => [['vertex' => 0, 'weight' => 3]]
];

// Self-loops are processed but won't improve distance
// (since distance to self is already 0)
```

### Multiple Edges Between Same Vertices

When multiple edges exist between the same pair of vertices, Dijkstra naturally selects the shortest:

```php
# filename: dijkstra-multiple-edges.php
<?php

// Graph with multiple edges
$graph = [
    0 => [
        ['vertex' => 1, 'weight' => 5],
        ['vertex' => 1, 'weight' => 3],  // Shorter edge
        ['vertex' => 1, 'weight' => 7]
    ],
    1 => []
];

// Dijkstra will use the edge with weight 3 (shortest)
```

### Disconnected Graphs

Dijkstra handles disconnected graphs by leaving unreachable vertices with distance INF:

```php
# filename: dijkstra-disconnected.php
<?php

$graph = [
    0 => [['vertex' => 1, 'weight' => 2]],
    1 => [['vertex' => 0, 'weight' => 2]],
    2 => [['vertex' => 3, 'weight' => 1]],  // Separate component
    3 => [['vertex' => 2, 'weight' => 1]]
];

$dijkstra = new DijkstraOptimized();
$distances = $dijkstra->findShortestPaths($graph, 0);
// [0 => 0, 1 => 2, 2 => INF, 3 => INF]
```

### Dense vs Sparse Graphs

Choose implementation based on graph density:

- **Dense graphs** (E ≈ V²): Array-based O(V²) may be competitive
- **Sparse graphs** (E << V²): Priority queue O((V+E) log V) is much better

```php
# filename: dijkstra-graph-density.php
<?php

class AdaptiveDijkstra
{
    public function findShortestPaths(array $graph, int $source): array
    {
        $vertices = count($graph);
        $edges = 0;
        
        foreach ($graph as $edges) {
            $edges += count($edges);
        }
        
        // Dense graph: E > V * log(V)
        if ($edges > $vertices * log($vertices + 1)) {
            return (new DijkstraBasic())->findShortestPaths($graph, $source);
        } else {
            return (new DijkstraOptimized())->findShortestPaths($graph, $source);
        }
    }
}
```

## Dijkstra vs BFS Comparison

Understanding when to use Dijkstra vs BFS for shortest path problems:

```php
# filename: dijkstra-vs-bfs-comparison.php
<?php

declare(strict_types=1);

class DijkstraVsBFSComparison
{
    public function compare(): array
    {
        return [
            'Graph Type' => [
                'Dijkstra' => 'Weighted graphs (edges have costs)',
                'BFS' => 'Unweighted graphs (all edges equal)'
            ],
            'Data Structure' => [
                'Dijkstra' => 'Priority Queue (min-heap)',
                'BFS' => 'Queue (FIFO)'
            ],
            'Shortest Path' => [
                'Dijkstra' => 'Guaranteed shortest (weighted)',
                'BFS' => 'Guaranteed shortest (unweighted, minimum edges)'
            ],
            'Time Complexity' => [
                'Dijkstra' => 'O((V + E) log V) with binary heap',
                'BFS' => 'O(V + E) - faster!'
            ],
            'Edge Weights' => [
                'Dijkstra' => 'Requires non-negative weights',
                'BFS' => 'No weights needed (treats all as 1)'
            ],
            'Use Cases' => [
                'Dijkstra' => 'GPS routing, network latency, cost optimization',
                'BFS' => 'Social networks, unweighted grids, level-order traversal'
            ],
            'Memory' => [
                'Dijkstra' => 'O(V) for distances + priority queue',
                'BFS' => 'O(V) for visited + queue'
            ],
            'When to Choose' => [
                'Dijkstra' => 'Edges have different costs/weights',
                'BFS' => 'All edges are equal or unweighted'
            ]
        ];
    }

    // Demonstrate difference on same graph structure
    public function demonstrateDifference(): void
    {
        echo "=== Dijkstra vs BFS on Same Graph ===\n\n";
        
        // Unweighted graph (BFS)
        $unweightedGraph = [
            0 => [1, 2],
            1 => [0, 3],
            2 => [0, 3],
            3 => [1, 2]
        ];
        
        // Weighted graph (Dijkstra) - same structure, different weights
        $weightedGraph = [
            0 => [['vertex' => 1, 'weight' => 4], ['vertex' => 2, 'weight' => 1]],
            1 => [['vertex' => 0, 'weight' => 4], ['vertex' => 3, 'weight' => 1]],
            2 => [['vertex' => 0, 'weight' => 1], ['vertex' => 3, 'weight' => 5]],
            3 => [['vertex' => 1, 'weight' => 1], ['vertex' => 2, 'weight' => 5]]
        ];
        
        echo "BFS from 0 to 3 (unweighted):\n";
        echo "  Path: 0 → 1 → 3 (2 edges, cost = 2)\n";
        echo "  OR: 0 → 2 → 3 (2 edges, cost = 2)\n";
        echo "  Both paths have same cost in unweighted graph\n\n";
        
        echo "Dijkstra from 0 to 3 (weighted):\n";
        echo "  Path 0 → 1 → 3: cost = 4 + 1 = 5\n";
        echo "  Path 0 → 2 → 3: cost = 1 + 5 = 6\n";
        echo "  Shortest path: 0 → 1 → 3 (cost = 5)\n";
        echo "  Dijkstra finds optimal path considering weights!\n";
    }
}

$comparison = new DijkstraVsBFSComparison();
print_r($comparison->compare());
$comparison->demonstrateDifference();
```

**Key Insight**: If all edges have the same weight (or no weights), BFS is simpler and faster. Use Dijkstra when edges have different costs, distances, or weights that matter for finding the optimal path.

## Best Practices

1. **Use Priority Queue**
   - Always use min-heap for efficiency
   - Avoid linear search for minimum distance
   - Exception: Very dense graphs (E ≈ V²) where array-based may be faster

2. **Check for Negative Weights**
   - Dijkstra requires non-negative weights
   - Use Bellman-Ford if negative weights exist
   - Validate graph before running algorithm

3. **Early Termination**
   - Stop when destination is reached (single-pair)
   - Don't compute unnecessary distances
   - Consider bidirectional Dijkstra for single-pair queries

4. **Decrease-Key Optimization**
   - Some implementations support updating priorities
   - Can improve performance with Fibonacci heap
   - PHP's SplMinHeap doesn't support decrease-key (insert duplicates instead)

5. **Choose BFS for Unweighted Graphs**
   - If all edges are equal, BFS is simpler and faster (O(V+E) vs O((V+E) log V))
   - Use Dijkstra only when edge weights matter for optimality

6. **Handle Edge Cases**
   - Self-loops are handled automatically (won't improve distance)
   - Multiple edges: algorithm naturally selects shortest
   - Disconnected graphs: unreachable vertices remain INF
   - Empty graphs: return empty distance array

7. **Choose Right Variant**
   - Single-pair: Use early termination or bidirectional Dijkstra
   - All-pairs: Consider Floyd-Warshall for dense graphs
   - Multi-source: Use multi-source Dijkstra instead of running V times

## Practice Exercises

### Exercise 1: Cheapest Flights K Stops

**Goal**: Implement Dijkstra's algorithm with stop constraints

Create a function that finds the cheapest flight path with at most K stops between two cities.

**Requirements**:
- Use modified Dijkstra's algorithm that tracks number of stops
- Handle cases where no path exists within K stops
- Return both the cost and the path

**Validation**: Test with a flight network graph:
```php
$flights = [
    'NYC' => [['vertex' => 'LA', 'weight' => 500, 'stops' => 0]],
    'NYC' => [['vertex' => 'Chicago', 'weight' => 200, 'stops' => 0]],
    'Chicago' => [['vertex' => 'LA', 'weight' => 300, 'stops' => 1]]
];
// Find cheapest NYC → LA with max 1 stop
```

### Exercise 2: Network Delay Time

**Goal**: Find the time for a signal to reach all nodes from a source

**Requirements**:
- Use Dijkstra's algorithm to find shortest distances to all nodes
- Return the maximum distance (time for signal to reach farthest node)
- Handle disconnected graphs (return -1 if some nodes unreachable)

**Validation**: 
```php
$network = [
    0 => [['vertex' => 1, 'weight' => 1], ['vertex' => 2, 'weight' => 2]],
    1 => [['vertex' => 2, 'weight' => 1]]
];
// Signal from node 0: reaches node 1 in 1, node 2 in 2
// Maximum delay = 2
```

### Exercise 3: Path with Maximum Probability

**Goal**: Find path with highest probability of success (maximize product)

**Requirements**:
- Modify Dijkstra to maximize product instead of minimize sum
- Use max-heap instead of min-heap
- Convert probabilities to logarithms for numerical stability (optional)

**Validation**: Test with probability graph where edges have success probabilities (0-1).

### Exercise 4: Minimum Effort Path

**Goal**: Find path minimizing maximum absolute difference between consecutive edges

**Requirements**:
- Track maximum difference encountered so far
- Use Dijkstra with modified cost function
- Priority queue ordered by maximum difference, not total distance

**Validation**: Test with grid graph where each edge has a weight representing elevation.

### Exercise 5: Swim in Rising Water

**Goal**: Find path where maximum elevation along path is minimized

**Requirements**:
- Similar to minimum effort path but simpler
- Track maximum weight encountered on path
- Use Dijkstra with max(previous_max, current_edge_weight) as distance

**Validation**: Test with grid representing water levels at each cell.

## Troubleshooting

### Error: "Negative edge weights detected"

**Symptom**: Algorithm produces incorrect shortest paths or infinite loops

**Cause**: Dijkstra's algorithm requires non-negative edge weights. Negative weights can cause the algorithm to revisit vertices and produce incorrect results.

**Solution**: 
- Validate graph before running Dijkstra: check all edge weights are ≥ 0
- If negative weights exist, use Bellman-Ford algorithm instead
- Example validation:
```php
foreach ($graph as $edges) {
    foreach ($edges as $edge) {
        if ($edge['weight'] < 0) {
            throw new InvalidArgumentException("Negative weights not allowed");
        }
    }
}
```

### Problem: Priority Queue Returns Wrong Order

**Symptom**: `SplMinHeap` doesn't order tuples correctly

**Cause**: `SplMinHeap` compares arrays lexicographically, which works for `[distance, vertex]` tuples when distance is first element.

**Solution**: Ensure tuple format is `[priority, value]` where priority is numeric:
```php
// Correct
$pq->insert([5, 2]);  // distance=5, vertex=2

// Wrong - will compare incorrectly
$pq->insert([2, 5]);  // vertex=2, distance=5
```

### Problem: Algorithm Too Slow for Large Graphs

**Symptom**: Dijkstra takes too long on graphs with many vertices

**Cause**: Using array-based implementation (O(V²)) instead of priority queue (O((V+E) log V))

**Solution**:
- Always use priority queue implementation for graphs with V > 100
- Consider early termination if only need path to specific destination
- For very large graphs, consider A* with good heuristic

### Problem: Path Reconstruction Returns Empty Array

**Symptom**: `reconstructPath()` returns empty array or incorrect path

**Cause**: `$previous` array not properly maintained, or source/destination mismatch

**Solution**:
- Ensure `$previous[$v] = $u` is set when updating distance
- Check that `$previous[$source]` is null (source has no predecessor)
- Verify destination is reachable: check `$distances[$destination] !== INF`

## Wrap-up

Congratulations! You've mastered Dijkstra's shortest path algorithm, one of the most important algorithms in computer science. Here's what you've accomplished:

- ✓ Understood how Dijkstra's algorithm finds shortest paths using a greedy approach
- ✓ Implemented both array-based (O(V²)) and priority queue-based (O((V+E) log V)) solutions
- ✓ Built custom priority queue implementation for better understanding
- ✓ Learned to reconstruct shortest paths from the `previous` array
- ✓ Implemented Dijkstra with named vertices for real-world applications
- ✓ Extended Dijkstra to A* algorithm with heuristic functions
- ✓ Built practical applications: GPS routing, network optimization, delivery routing
- ✓ Analyzed time complexity trade-offs between different implementations
- ✓ Understood limitations and when to use alternative algorithms

Dijkstra's algorithm is the foundation for many routing and navigation systems you use every day. The key insight is using a priority queue to always expand the vertex with minimum distance first, which guarantees optimal shortest paths when edge weights are non-negative. This greedy approach, combined with proper data structures, makes Dijkstra both elegant and efficient.

Remember: Dijkstra requires non-negative weights, uses O((V+E) log V) time with binary heap, and can be optimized further with Fibonacci heaps or extended to A* for faster pathfinding with good heuristics.

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

## Further Reading

- [Breadth-First Search (BFS)](/series/php-algorithms/chapters/23-breadth-first-search) — Compare Dijkstra with BFS for unweighted graphs
- [Graph Representations](/series/php-algorithms/chapters/21-graph-representations) — Review graph data structures used with Dijkstra
- [Dijkstra's Algorithm - Wikipedia](https://en.wikipedia.org/wiki/Dijkstra%27s_algorithm) — Comprehensive overview with history and variants
- [A* Search Algorithm - Wikipedia](https://en.wikipedia.org/wiki/A*_search_algorithm) — Learn more about A* pathfinding
- [Shortest Path Algorithms - GeeksforGeeks](https://www.geeksforgeeks.org/shortest-path-algorithms/) — Visual explanations and comparisons
- [Network Routing Algorithms](https://www.cisco.com/c/en/us/support/docs/ip/enhanced-interior-gateway-routing-protocol-eigrp/16419-eigrp-toc.html) — Real-world applications in networking


<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="24"
  label="Dijkstra's Shortest Path understood!"
/>
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
