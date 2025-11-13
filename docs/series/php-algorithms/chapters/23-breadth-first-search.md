---
title: "Breadth-First Search (BFS)"
description: "Learn Breadth-First Search for level-order graph traversal, finding shortest paths in unweighted graphs, and solving problems like connected components and bipartite detection"
series: "php-algorithms"
chapter: 23
order: 23
difficulty: "intermediate"
prerequisites: ["Graph Representations", "Stacks & Queues"]
---

# Breadth-First Search (BFS)

Breadth-First Search explores a graph level by level, visiting all neighbors of a vertex before moving to the next level. It's optimal for finding shortest paths in unweighted graphs.

## How BFS Works

BFS explores a graph by:
1. Starting at a source vertex
2. Visiting all immediate neighbors (level 1)
3. Visiting all neighbors of those neighbors (level 2)
4. Continuing level by level until all reachable vertices are visited

```
Graph:        0---1---2
              |   |
              3---4

BFS from 0: Level 0: [0]
            Level 1: [1, 3]
            Level 2: [2, 4]

Order: [0, 1, 3, 2, 4]
```

## BFS Visual Step-by-Step Trace

Understanding BFS execution through detailed visualization:

```php
<?php

class BFSVisualizer
{
    private array $graph;

    public function __construct(array $graph)
    {
        $this->graph = $graph;
    }

    // Visualize BFS execution step by step
    public function visualizeBFS(int $start): void
    {
        echo "=== BFS Traversal Visualization ===\n\n";
        echo "Graph Structure:\n";
        $this->printGraph();
        echo "\n";

        echo "Starting BFS from vertex $start\n";
        echo str_repeat('=', 60) . "\n\n";

        $visited = [$start => true];
        $queue = [$start];
        $level = 0;
        $stepCount = 0;

        while (!empty($queue)) {
            $levelSize = count($queue);
            echo "Level $level:\n";

            for ($i = 0; $i < $levelSize; $i++) {
                $vertex = array_shift($queue);
                $stepCount++;

                echo "  Step $stepCount: Process vertex $vertex\n";
                echo "    Current queue: [" . implode(', ', $queue) . "]\n";
                echo "    Visited: {" . implode(', ', array_keys($visited)) . "}\n";

                $neighbors = $this->graph[$vertex] ?? [];
                $newNeighbors = [];

                foreach ($neighbors as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $visited[$neighbor] = true;
                        $queue[] = $neighbor;
                        $newNeighbors[] = $neighbor;
                    }
                }

                if (!empty($newNeighbors)) {
                    echo "    Added to queue: [" . implode(', ', $newNeighbors) . "]\n";
                } else {
                    echo "    No new neighbors to add\n";
                }

                echo "    Queue after: [" . implode(', ', $queue) . "]\n";
                echo "\n";
            }

            $level++;
        }

        echo "=== Summary ===\n";
        echo "Total vertices visited: " . count($visited) . "\n";
        echo "Maximum level reached: " . ($level - 1) . "\n";
    }

    private function printGraph(): void
    {
        foreach ($this->graph as $vertex => $neighbors) {
            echo "  $vertex: [" . implode(', ', $neighbors) . "]\n";
        }
    }

    // Visualize level-by-level exploration
    public function visualizeLevels(int $start): void
    {
        echo "\n=== BFS Level Visualization ===\n\n";

        echo "Starting from vertex $start:\n\n";

        $visited = [$start => true];
        $currentLevel = [$start];
        $level = 0;

        while (!empty($currentLevel)) {
            echo "Level $level: ";
            $this->drawLevel($currentLevel);
            echo "\n";

            $nextLevel = [];
            foreach ($currentLevel as $vertex) {
                foreach ($this->graph[$vertex] ?? [] as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $visited[$neighbor] = true;
                        $nextLevel[] = $neighbor;
                    }
                }
            }

            $currentLevel = $nextLevel;
            $level++;
        }

        echo "\nLike ripples in water, BFS expands outward level by level\n";
    }

    private function drawLevel(array $vertices): void
    {
        $visual = implode('---', array_map(fn($v) => "[$v]", $vertices));
        echo $visual;
    }
}

// Example: Visualize BFS on a simple graph
$graph = [
    0 => [1, 3],
    1 => [0, 2, 4],
    2 => [1],
    3 => [0, 4],
    4 => [1, 3]
];

$visualizer = new BFSVisualizer($graph);
$visualizer->visualizeBFS(0);
$visualizer->visualizeLevels(0);

/*
Output:
=== BFS Traversal Visualization ===

Graph Structure:
  0: [1, 3]
  1: [0, 2, 4]
  2: [1]
  3: [0, 4]
  4: [1, 3]

Starting BFS from vertex 0
============================================================

Level 0:
  Step 1: Process vertex 0
    Current queue: []
    Visited: {0}
    Added to queue: [1, 3]
    Queue after: [1, 3]

Level 1:
  Step 2: Process vertex 1
    Current queue: [3]
    Visited: {0, 1}
    Added to queue: [2, 4]
    Queue after: [3, 2, 4]

  Step 3: Process vertex 3
    Current queue: [2, 4]
    Visited: {0, 1, 3}
    No new neighbors to add (0 already visited, 4 already in queue)
    Queue after: [2, 4]

Level 2:
  Step 4: Process vertex 2
    Current queue: [4]
    Visited: {0, 1, 3, 2}
    No new neighbors to add
    Queue after: [4]

  Step 5: Process vertex 4
    Current queue: []
    Visited: {0, 1, 3, 2, 4}
    No new neighbors to add
    Queue after: []

=== Summary ===
Total vertices visited: 5
Maximum level reached: 2

=== BFS Level Visualization ===

Starting from vertex 0:

Level 0: [0]
Level 1: [1]---[3]
Level 2: [2]---[4]

Like ripples in water, BFS expands outward level by level
*/
```

## BFS Queue State Visualization

Understanding how the queue changes during BFS:

```php
<?php

class BFSQueueVisualizer
{
    // Visualize queue operations in detail
    public function visualizeQueue(array $graph, int $start): void
    {
        echo "=== BFS Queue Operations ===\n\n";

        $visited = [$start => true];
        $queue = [$start];
        $step = 0;

        $this->printQueueState($step++, $queue, "INIT", $start, "Start vertex");

        while (!empty($queue)) {
            $vertex = array_shift($queue);
            $this->printQueueState($step++, $queue, "DEQUEUE", $vertex, "Processing");

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $queue[] = $neighbor;
                    $this->printQueueState($step++, $queue, "ENQUEUE", $neighbor,
                                         "From vertex $vertex");
                }
            }
        }

        echo "\nQueue Operations Summary:\n";
        echo "- Queue follows FIFO (First-In-First-Out) principle\n";
        echo "- Enqueue: Add to back of queue (array_push)\n";
        echo "- Dequeue: Remove from front of queue (array_shift)\n";
        echo "- This ensures level-by-level exploration\n";
    }

    private function printQueueState(int $step, array $queue, string $op, int $vertex, string $note): void
    {
        echo "Step $step: $op vertex $vertex ($note)\n";
        echo "  Queue: [";

        if (empty($queue)) {
            echo "empty";
        } else {
            foreach ($queue as $i => $v) {
                echo $i === 0 ? "← $v" : " $v";
                if ($i === count($queue) - 1 && count($queue) > 1) {
                    echo " ←";
                }
            }
        }

        echo "]\n";
        echo "         " . ($op === 'DEQUEUE' ? "^front" : str_repeat(' ', count($queue) * 3) . "back^") . "\n\n";
    }
}

$graph = [
    0 => [1, 2],
    1 => [0, 3],
    2 => [0, 4],
    3 => [1],
    4 => [2]
];

$queueViz = new BFSQueueVisualizer();
$queueViz->visualizeQueue($graph, 0);

/*
Output shows FIFO queue behavior:

=== BFS Queue Operations ===

Step 0: INIT vertex 0 (Start vertex)
  Queue: [← 0 ←]
            back^

Step 1: DEQUEUE vertex 0 (Processing)
  Queue: [empty]
         ^front

Step 2: ENQUEUE vertex 1 (From vertex 0)
  Queue: [← 1 ←]
            back^

Step 3: ENQUEUE vertex 2 (From vertex 0)
  Queue: [← 1 2 ←]
               back^

Step 4: DEQUEUE vertex 1 (Processing)
  Queue: [← 2]
         ^front

Step 5: ENQUEUE vertex 3 (From vertex 1)
  Queue: [← 2 3 ←]
               back^

... and so on ...
*/
```

## Basic BFS Implementation

```php
<?php

class BFS
{
    // BFS traversal
    public function traverse(array $graph, int $start): array
    {
        $visited = [$start => true];
        $result = [$start];
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $result[] = $neighbor;
                    $queue[] = $neighbor;
                }
            }
        }

        return $result;
    }

    // BFS with level tracking
    public function traverseByLevel(array $graph, int $start): array
    {
        $visited = [$start => true];
        $levels = [[$start]];
        $queue = [[$start, 0]];  // [vertex, level]

        while (!empty($queue)) {
            [$vertex, $level] = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;

                    if (!isset($levels[$level + 1])) {
                        $levels[$level + 1] = [];
                    }
                    $levels[$level + 1][] = $neighbor;

                    $queue[] = [$neighbor, $level + 1];
                }
            }
        }

        return $levels;
    }

    // BFS for all components (disconnected graphs)
    public function traverseAll(array $graph): array
    {
        $visited = [];
        $components = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($visited[$vertex])) {
                $component = $this->bfsComponent($graph, $vertex, $visited);
                $components[] = $component;
            }
        }

        return $components;
    }

    private function bfsComponent(array $graph, int $start, array &$visited): array
    {
        $component = [$start];
        $visited[$start] = true;
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $component[] = $neighbor;
                    $queue[] = $neighbor;
                }
            }
        }

        return $component;
    }
}

// Example usage
$graph = [
    0 => [1, 3],
    1 => [0, 2, 4],
    2 => [1],
    3 => [0, 4],
    4 => [1, 3]
];

$bfs = new BFS();
print_r($bfs->traverse($graph, 0));  // [0, 1, 3, 2, 4]

print_r($bfs->traverseByLevel($graph, 0));
// [[0], [1, 3], [2, 4]]

// Disconnected graph
$disconnected = [
    0 => [1],
    1 => [0],
    2 => [3],
    3 => [2]
];

print_r($bfs->traverseAll($disconnected));  // [[0, 1], [2, 3]]
```

## Shortest Path in Unweighted Graph

BFS finds the shortest path in graphs where all edges have equal weight.

```php
<?php

class BFSShortestPath
{
    // Find shortest path from start to end
    public function findPath(array $graph, int $start, int $end): ?array
    {
        if ($start === $end) {
            return [$start];
        }

        $visited = [$start => true];
        $parent = [$start => null];
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $parent[$neighbor] = $vertex;
                    $queue[] = $neighbor;

                    // Found the destination
                    if ($neighbor === $end) {
                        return $this->reconstructPath($parent, $start, $end);
                    }
                }
            }
        }

        return null;  // No path found
    }

    private function reconstructPath(array $parent, int $start, int $end): array
    {
        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $parent[$current];
        }

        return $path;
    }

    // Find shortest distance from start to all vertices
    public function findAllDistances(array $graph, int $start): array
    {
        $distances = [$start => 0];
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);
            $currentDistance = $distances[$vertex];

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($distances[$neighbor])) {
                    $distances[$neighbor] = $currentDistance + 1;
                    $queue[] = $neighbor;
                }
            }
        }

        return $distances;
    }

    // Find shortest path distance
    public function findDistance(array $graph, int $start, int $end): ?int
    {
        $distances = $this->findAllDistances($graph, $start);
        return $distances[$end] ?? null;
    }
}

// Example
$graph = [
    0 => [1, 2],
    1 => [0, 3],
    2 => [0, 3, 4],
    3 => [1, 2, 4],
    4 => [2, 3]
];

$pathFinder = new BFSShortestPath();
print_r($pathFinder->findPath($graph, 0, 4));  // [0, 2, 4]

echo "Distance from 0 to 4: " . $pathFinder->findDistance($graph, 0, 4) . "\n";  // 2

print_r($pathFinder->findAllDistances($graph, 0));
// [0 => 0, 1 => 1, 2 => 1, 3 => 2, 4 => 2]
```

## Bipartite Graph Detection

Checking if a graph can be colored with two colors such that no adjacent vertices have the same color.

```php
<?php

class BipartiteDetector
{
    private const COLOR_A = 0;
    private const COLOR_B = 1;

    // Check if graph is bipartite
    public function isBipartite(array $graph): bool
    {
        $colors = [];

        // Check all components (for disconnected graphs)
        foreach (array_keys($graph) as $vertex) {
            if (!isset($colors[$vertex])) {
                if (!$this->bfsColorCheck($graph, $vertex, $colors)) {
                    return false;
                }
            }
        }

        return true;
    }

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
                    // Neighbor has same color - not bipartite
                    return false;
                }
            }
        }

        return true;
    }

    // Get the two sets if bipartite
    public function getTwoSets(array $graph): ?array
    {
        $colors = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($colors[$vertex])) {
                if (!$this->bfsColorCheck($graph, $vertex, $colors)) {
                    return null;  // Not bipartite
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

// Example - Bipartite graph (e.g., students and courses)
$bipartite = [
    'Alice' => ['Math', 'Physics'],
    'Bob' => ['Math', 'Chemistry'],
    'Charlie' => ['Physics', 'Chemistry'],
    'Math' => ['Alice', 'Bob'],
    'Physics' => ['Alice', 'Charlie'],
    'Chemistry' => ['Bob', 'Charlie']
];

$detector = new BipartiteDetector();
echo $detector->isBipartite($bipartite) ? "Bipartite\n" : "Not bipartite\n";
// Bipartite

$sets = $detector->getTwoSets($bipartite);
echo "Set A (students): " . implode(', ', $sets[0]) . "\n";
echo "Set B (courses): " . implode(', ', $sets[1]) . "\n";

// Example - Not bipartite (contains odd cycle)
$notBipartite = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1]  // Triangle - odd cycle
];

echo $detector->isBipartite($notBipartite) ? "Bipartite\n" : "Not bipartite\n";
// Not bipartite
```

## 0-1 BFS

Optimized BFS for graphs with edge weights of only 0 and 1.

```php
<?php

class ZeroOneBFS
{
    // Shortest path in 0-1 weighted graph using deque
    public function shortestPath(array $graph, int $start, int $end): ?int
    {
        $distances = array_fill(0, count($graph), PHP_INT_MAX);
        $distances[$start] = 0;

        $deque = [$start];

        while (!empty($deque)) {
            $vertex = array_shift($deque);

            if ($vertex === $end) {
                return $distances[$end];
            }

            foreach ($graph[$vertex] ?? [] as $edge) {
                $neighbor = $edge['vertex'];
                $weight = $edge['weight'];
                $newDistance = $distances[$vertex] + $weight;

                if ($newDistance < $distances[$neighbor]) {
                    $distances[$neighbor] = $newDistance;

                    if ($weight === 0) {
                        // Add to front for 0-weight edges
                        array_unshift($deque, $neighbor);
                    } else {
                        // Add to back for 1-weight edges
                        $deque[] = $neighbor;
                    }
                }
            }
        }

        return $distances[$end] === PHP_INT_MAX ? null : $distances[$end];
    }
}

// Example - Grid where you can move freely or climb (cost 1)
$grid = [
    0 => [['vertex' => 1, 'weight' => 0], ['vertex' => 3, 'weight' => 1]],
    1 => [['vertex' => 0, 'weight' => 0], ['vertex' => 2, 'weight' => 0]],
    2 => [['vertex' => 1, 'weight' => 0], ['vertex' => 4, 'weight' => 1]],
    3 => [['vertex' => 0, 'weight' => 1], ['vertex' => 4, 'weight' => 0]],
    4 => [['vertex' => 2, 'weight' => 1], ['vertex' => 3, 'weight' => 0]]
];

$bfs01 = new ZeroOneBFS();
echo "Shortest distance: " . $bfs01->shortestPath($grid, 0, 4) . "\n";  // 1
```

## Multi-Source BFS

Starting BFS from multiple source vertices simultaneously.

```php
<?php

class MultiSourceBFS
{
    // Find shortest distance from any source to all vertices
    public function findDistances(array $graph, array $sources): array
    {
        $distances = [];
        $queue = [];

        // Initialize all sources with distance 0
        foreach ($sources as $source) {
            $distances[$source] = 0;
            $queue[] = $source;
        }

        while (!empty($queue)) {
            $vertex = array_shift($queue);
            $currentDistance = $distances[$vertex];

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($distances[$neighbor])) {
                    $distances[$neighbor] = $currentDistance + 1;
                    $queue[] = $neighbor;
                }
            }
        }

        return $distances;
    }

    // Find nearest source for each vertex
    public function findNearestSource(array $graph, array $sources): array
    {
        $nearest = [];
        $queue = [];

        // Initialize all sources
        foreach ($sources as $source) {
            $nearest[$source] = $source;
            $queue[] = $source;
        }

        while (!empty($queue)) {
            $vertex = array_shift($queue);
            $sourceVertex = $nearest[$vertex];

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($nearest[$neighbor])) {
                    $nearest[$neighbor] = $sourceVertex;
                    $queue[] = $neighbor;
                }
            }
        }

        return $nearest;
    }
}

// Example - Multiple hospitals serving areas
$cityMap = [
    0 => [1, 2],
    1 => [0, 3],
    2 => [0, 3, 4],
    3 => [1, 2, 4, 5],
    4 => [2, 3, 6],
    5 => [3, 6],
    6 => [4, 5]
];

$hospitals = [0, 5];  // Hospitals at vertices 0 and 5

$multiBFS = new MultiSourceBFS();
$distances = $multiBFS->findDistances($cityMap, $hospitals);
echo "Distance to nearest hospital:\n";
print_r($distances);
// [0 => 0, 1 => 1, 2 => 1, 3 => 2, 5 => 0, 6 => 1, 4 => 2]

$nearest = $multiBFS->findNearestSource($cityMap, $hospitals);
echo "Nearest hospital for each location:\n";
print_r($nearest);
// [0 => 0, 1 => 0, 2 => 0, 3 => 0, 5 => 5, 6 => 5, 4 => 0]
```

## BFS on 2D Grid

Common application of BFS for grid-based problems.

```php
<?php

class GridBFS
{
    private const DIRECTIONS = [
        [-1, 0],  // Up
        [0, 1],   // Right
        [1, 0],   // Down
        [0, -1]   // Left
    ];

    // Find shortest path in grid (1 = walkable, 0 = obstacle)
    public function shortestPath(array $grid, array $start, array $end): ?int
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $visited = [];

        $queue = [[$start[0], $start[1], 0]];  // [row, col, distance]
        $visited["{$start[0]},{$start[1]}"] = true;

        while (!empty($queue)) {
            [$row, $col, $distance] = array_shift($queue);

            // Check if reached end
            if ($row === $end[0] && $col === $end[1]) {
                return $distance;
            }

            // Try all 4 directions
            foreach (self::DIRECTIONS as [$dr, $dc]) {
                $newRow = $row + $dr;
                $newCol = $col + $dc;
                $key = "$newRow,$newCol";

                // Check bounds
                if ($newRow >= 0 && $newRow < $rows &&
                    $newCol >= 0 && $newCol < $cols &&
                    $grid[$newRow][$newCol] === 1 &&
                    !isset($visited[$key])) {

                    $visited[$key] = true;
                    $queue[] = [$newRow, $newCol, $distance + 1];
                }
            }
        }

        return null;  // No path found
    }

    // Flood fill (paint all connected cells)
    public function floodFill(array $grid, int $row, int $col, int $newColor): array
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $originalColor = $grid[$row][$col];

        if ($originalColor === $newColor) {
            return $grid;
        }

        $queue = [[$row, $col]];
        $grid[$row][$col] = $newColor;

        while (!empty($queue)) {
            [$r, $c] = array_shift($queue);

            foreach (self::DIRECTIONS as [$dr, $dc]) {
                $newRow = $r + $dr;
                $newCol = $c + $dc;

                if ($newRow >= 0 && $newRow < $rows &&
                    $newCol >= 0 && $newCol < $cols &&
                    $grid[$newRow][$newCol] === $originalColor) {

                    $grid[$newRow][$newCol] = $newColor;
                    $queue[] = [$newRow, $newCol];
                }
            }
        }

        return $grid;
    }

    // Count islands (connected 1s)
    public function countIslands(array $grid): int
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $visited = [];
        $count = 0;

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $key = "$row,$col";

                if ($grid[$row][$col] === 1 && !isset($visited[$key])) {
                    $this->bfsIsland($grid, $row, $col, $visited);
                    $count++;
                }
            }
        }

        return $count;
    }

    private function bfsIsland(array $grid, int $startRow, int $startCol, array &$visited): void
    {
        $rows = count($grid);
        $cols = count($grid[0]);
        $queue = [[$startRow, $startCol]];
        $visited["$startRow,$startCol"] = true;

        while (!empty($queue)) {
            [$row, $col] = array_shift($queue);

            foreach (self::DIRECTIONS as [$dr, $dc]) {
                $newRow = $row + $dr;
                $newCol = $col + $dc;
                $key = "$newRow,$newCol";

                if ($newRow >= 0 && $newRow < $rows &&
                    $newCol >= 0 && $newCol < $cols &&
                    $grid[$newRow][$newCol] === 1 &&
                    !isset($visited[$key])) {

                    $visited[$key] = true;
                    $queue[] = [$newRow, $newCol];
                }
            }
        }
    }
}

// Example - Grid pathfinding
$grid = [
    [1, 1, 0, 1],
    [1, 0, 1, 1],
    [1, 1, 1, 0],
    [0, 1, 1, 1]
];

$gridBFS = new GridBFS();
$distance = $gridBFS->shortestPath($grid, [0, 0], [3, 3]);
echo "Shortest path length: $distance\n";  // 6

// Example - Island counting
$islandGrid = [
    [1, 1, 0, 0, 0],
    [1, 1, 0, 0, 1],
    [0, 0, 0, 1, 1],
    [0, 0, 0, 0, 0],
    [1, 0, 1, 0, 1]
];

echo "Number of islands: " . $gridBFS->countIslands($islandGrid) . "\n";  // 5
```

## Word Ladder Problem

Transform one word to another changing one letter at a time.

```php
<?php

class WordLadder
{
    // Find shortest transformation sequence
    public function ladderLength(string $beginWord, string $endWord, array $wordList): ?int
    {
        $wordSet = array_flip($wordList);

        if (!isset($wordSet[$endWord])) {
            return null;  // End word not in dictionary
        }

        $queue = [[$beginWord, 1]];  // [word, steps]
        $visited = [$beginWord => true];

        while (!empty($queue)) {
            [$word, $steps] = array_shift($queue);

            if ($word === $endWord) {
                return $steps;
            }

            // Try changing each letter
            for ($i = 0; $i < strlen($word); $i++) {
                for ($c = ord('a'); $c <= ord('z'); $c++) {
                    $newWord = substr($word, 0, $i) . chr($c) . substr($word, $i + 1);

                    if (isset($wordSet[$newWord]) && !isset($visited[$newWord])) {
                        $visited[$newWord] = true;
                        $queue[] = [$newWord, $steps + 1];
                    }
                }
            }
        }

        return null;  // No transformation sequence found
    }

    // Find the actual transformation sequence
    public function findLadder(string $beginWord, string $endWord, array $wordList): ?array
    {
        $wordSet = array_flip($wordList);

        if (!isset($wordSet[$endWord])) {
            return null;
        }

        $queue = [[$beginWord, [$beginWord]]];
        $visited = [$beginWord => true];

        while (!empty($queue)) {
            [$word, $path] = array_shift($queue);

            if ($word === $endWord) {
                return $path;
            }

            for ($i = 0; $i < strlen($word); $i++) {
                for ($c = ord('a'); $c <= ord('z'); $c++) {
                    $newWord = substr($word, 0, $i) . chr($c) . substr($word, $i + 1);

                    if (isset($wordSet[$newWord]) && !isset($visited[$newWord])) {
                        $visited[$newWord] = true;
                        $newPath = array_merge($path, [$newWord]);
                        $queue[] = [$newWord, $newPath];
                    }
                }
            }
        }

        return null;
    }
}

// Example
$ladder = new WordLadder();
$wordList = ['hot', 'dot', 'dog', 'lot', 'log', 'cog'];

$length = $ladder->ladderLength('hit', 'cog', $wordList);
echo "Ladder length: $length\n";  // 5

$sequence = $ladder->findLadder('hit', 'cog', $wordList);
echo "Transformation: " . implode(' -> ', $sequence) . "\n";
// hit -> hot -> dot -> dog -> cog
```

## Complexity Analysis

| Operation | Time Complexity | Space Complexity |
|-----------|----------------|------------------|
| BFS Traversal | O(V + E) | O(V) |
| Shortest Path | O(V + E) | O(V) |
| Bipartite Check | O(V + E) | O(V) |
| 0-1 BFS | O(V + E) | O(V) |
| Multi-source BFS | O(V + E) | O(V) |
| Grid BFS (m×n grid) | O(m × n) | O(m × n) |

**Where**:
- V = number of vertices
- E = number of edges
- m, n = grid dimensions

## BFS vs DFS Comparison

```php
<?php

class BFSvsDFS
{
    public function compare(): array
    {
        return [
            'Data Structure' => [
                'BFS' => 'Queue (FIFO)',
                'DFS' => 'Stack (LIFO) or recursion'
            ],
            'Exploration' => [
                'BFS' => 'Level by level (breadth)',
                'DFS' => 'Branch by branch (depth)'
            ],
            'Shortest Path' => [
                'BFS' => 'Guaranteed shortest (unweighted)',
                'DFS' => 'Not guaranteed shortest'
            ],
            'Memory' => [
                'BFS' => 'O(width) - can be large',
                'DFS' => 'O(height) - usually smaller'
            ],
            'Best For' => [
                'BFS' => 'Shortest path, level-order, nearby solutions',
                'DFS' => 'Topological sort, cycles, exhaustive search'
            ],
            'Complete' => [
                'BFS' => 'Yes (finds solution if exists)',
                'DFS' => 'No (can get stuck in infinite path)'
            ]
        ];
    }
}
```

## Framework Integration Examples

### Laravel Graph Service with BFS

Real-world integration of BFS in a Laravel application:

```php
<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

/**
 * Graph-based service using BFS for social features
 */
class SocialGraphService
{
    // Find shortest connection path between two users
    public function findConnectionPath(int $userId1, int $userId2): ?array
    {
        // Cache key for this connection path
        $cacheKey = "connection_path:{$userId1}:{$userId2}";

        return Cache::remember($cacheKey, now()->addHours(24), function() use ($userId1, $userId2) {
            return $this->bfsConnectionPath($userId1, $userId2);
        });
    }

    private function bfsConnectionPath(int $startId, int $endId): ?array
    {
        if ($startId === $endId) {
            return [User::find($startId)];
        }

        $visited = [$startId => true];
        $parent = [$startId => null];
        $queue = [$startId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);

            // Get friends of current user
            $friends = User::find($currentId)
                ->friends()
                ->pluck('id')
                ->toArray();

            foreach ($friends as $friendId) {
                if (!isset($visited[$friendId])) {
                    $visited[$friendId] = true;
                    $parent[$friendId] = $currentId;
                    $queue[] = $friendId;

                    // Found the target!
                    if ($friendId === $endId) {
                        return $this->reconstructPath($parent, $startId, $endId);
                    }
                }
            }
        }

        return null; // No connection found
    }

    private function reconstructPath(array $parent, int $start, int $end): array
    {
        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, User::find($current));
            $current = $parent[$current];
        }

        return $path;
    }

    // Calculate degrees of separation
    public function getDegreesOfSeparation(int $userId1, int $userId2): int
    {
        $path = $this->findConnectionPath($userId1, $userId2);

        if ($path === null) {
            return -1; // Not connected
        }

        return count($path) - 1; // Number of hops
    }

    // Find all users within N degrees
    public function findUsersWithinDistance(int $userId, int $maxDistance): Collection
    {
        $visited = [$userId => 0]; // user_id => distance
        $queue = [[$userId, 0]]; // [user_id, distance]
        $result = collect();

        while (!empty($queue)) {
            [$currentId, $distance] = array_shift($queue);

            if ($distance <= $maxDistance && $currentId !== $userId) {
                $result->push([
                    'user' => User::find($currentId),
                    'distance' => $distance
                ]);
            }

            if ($distance < $maxDistance) {
                $friends = User::find($currentId)
                    ->friends()
                    ->pluck('id')
                    ->toArray();

                foreach ($friends as $friendId) {
                    if (!isset($visited[$friendId])) {
                        $visited[$friendId] = $distance + 1;
                        $queue[] = [$friendId, $distance + 1];
                    }
                }
            }
        }

        return $result;
    }

    // Recommend friends (friends of friends not yet connected)
    public function recommendFriends(int $userId, int $limit = 10): Collection
    {
        $user = User::find($userId);
        $directFriends = $user->friends()->pluck('id')->toArray();
        $recommendations = [];

        // BFS to explore 2 levels deep
        $visited = array_flip(array_merge([$userId], $directFriends));
        $queue = [];

        // Start from direct friends
        foreach ($directFriends as $friendId) {
            $queue[] = [$friendId, 1];
        }

        while (!empty($queue)) {
            [$currentId, $level] = array_shift($queue);

            if ($level === 2) {
                // This is a friend-of-friend
                if (!isset($visited[$currentId])) {
                    $mutualFriends = $this->countMutualFriends($userId, $currentId);

                    $recommendations[] = [
                        'user' => User::find($currentId),
                        'mutual_friends' => $mutualFriends,
                        'score' => $mutualFriends // Can be enhanced with more factors
                    ];

                    $visited[$currentId] = true;

                    if (count($recommendations) >= $limit * 2) {
                        break; // Early termination
                    }
                }
            }

            if ($level < 2) {
                $friends = User::find($currentId)
                    ->friends()
                    ->pluck('id')
                    ->toArray();

                foreach ($friends as $friendId) {
                    if (!isset($visited[$friendId])) {
                        $queue[] = [$friendId, $level + 1];
                    }
                }
            }
        }

        // Sort by score and return top N
        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        return collect(array_slice($recommendations, 0, $limit));
    }

    private function countMutualFriends(int $userId1, int $userId2): int
    {
        $friends1 = User::find($userId1)->friends()->pluck('id')->toArray();
        $friends2 = User::find($userId2)->friends()->pluck('id')->toArray();

        return count(array_intersect($friends1, $friends2));
    }

    // Find network influencers (users with high closeness centrality)
    public function findInfluencers(int $limit = 10): Collection
    {
        $users = User::all();
        $centrality = [];

        foreach ($users as $user) {
            $distances = $this->bfsDistances($user->id);
            $totalDistance = array_sum($distances);
            $reachable = count($distances) - 1; // Exclude self

            $centrality[$user->id] = [
                'user' => $user,
                'centrality' => $reachable > 0 ? $reachable / $totalDistance : 0,
                'reach' => $reachable
            ];
        }

        // Sort by centrality
        usort($centrality, fn($a, $b) => $b['centrality'] <=> $a['centrality']);

        return collect(array_slice($centrality, 0, $limit));
    }

    private function bfsDistances(int $startId): array
    {
        $distances = [$startId => 0];
        $queue = [$startId];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $currentDist = $distances[$currentId];

            $friends = User::find($currentId)
                ->friends()
                ->pluck('id')
                ->toArray();

            foreach ($friends as $friendId) {
                if (!isset($distances[$friendId])) {
                    $distances[$friendId] = $currentDist + 1;
                    $queue[] = $friendId;
                }
            }
        }

        return $distances;
    }
}

// Usage in Laravel Controller
namespace App\Http\Controllers;

use App\Services\SocialGraphService;
use Illuminate\Http\Request;

class SocialGraphController extends Controller
{
    public function __construct(
        private SocialGraphService $graphService
    ) {}

    public function connectionPath(Request $request)
    {
        $userId1 = $request->user()->id;
        $userId2 = $request->input('user_id');

        $path = $this->graphService->findConnectionPath($userId1, $userId2);

        if ($path === null) {
            return response()->json([
                'connected' => false,
                'message' => 'No connection found'
            ]);
        }

        return response()->json([
            'connected' => true,
            'degrees' => count($path) - 1,
            'path' => array_map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name
            ], $path)
        ]);
    }

    public function recommendations(Request $request)
    {
        $userId = $request->user()->id;
        $limit = $request->input('limit', 10);

        $recommendations = $this->graphService->recommendFriends($userId, $limit);

        return response()->json([
            'recommendations' => $recommendations->map(fn($rec) => [
                'user' => [
                    'id' => $rec['user']->id,
                    'name' => $rec['user']->name,
                    'avatar' => $rec['user']->avatar_url
                ],
                'mutual_friends' => $rec['mutual_friends'],
                'score' => $rec['score']
            ])
        ]);
    }

    public function nearby(Request $request)
    {
        $userId = $request->user()->id;
        $distance = $request->input('distance', 2);

        $users = $this->graphService->findUsersWithinDistance($userId, $distance);

        return response()->json([
            'users' => $users->map(fn($item) => [
                'user' => [
                    'id' => $item['user']->id,
                    'name' => $item['user']->name
                ],
                'distance' => $item['distance']
            ])
        ]);
    }

    public function influencers()
    {
        $influencers = $this->graphService->findInfluencers(20);

        return response()->json([
            'influencers' => $influencers->map(fn($item) => [
                'user' => [
                    'id' => $item['user']->id,
                    'name' => $item['user']->name
                ],
                'centrality_score' => round($item['centrality'], 4),
                'reach' => $item['reach']
            ])
        ]);
    }
}

// Routes (routes/api.php)
/*
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/social/connection/{user_id}', [SocialGraphController::class, 'connectionPath']);
    Route::get('/social/recommendations', [SocialGraphController::class, 'recommendations']);
    Route::get('/social/nearby', [SocialGraphController::class, 'nearby']);
    Route::get('/social/influencers', [SocialGraphController::class, 'influencers']);
});
*/
```

### Symfony Content Recommendation System

```php
<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Content;
use App\Entity\User;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * BFS-based content recommendation system
 */
class ContentRecommendationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CacheInterface $cache
    ) {}

    // Find related content through user interaction graph
    public function findRelatedContent(int $contentId, int $limit = 10): array
    {
        return $this->cache->get(
            "related_content_{$contentId}_{$limit}",
            function (ItemInterface $item) use ($contentId, $limit) {
                $item->expiresAfter(3600); // 1 hour cache

                return $this->bfsRelatedContent($contentId, $limit);
            }
        );
    }

    private function bfsRelatedContent(int $startContentId, int $limit): array
    {
        $visited = [$startContentId => true];
        $queue = [[$startContentId, 0]]; // [content_id, distance]
        $related = [];

        // Build graph: content -> users who viewed it -> other content they viewed
        while (!empty($queue) && count($related) < $limit) {
            [$contentId, $distance] = array_shift($queue);

            if ($distance > 0 && $distance <= 2) {
                $content = $this->em->find(Content::class, $contentId);
                if ($content) {
                    $related[] = [
                        'content' => $content,
                        'distance' => $distance,
                        'score' => $this->calculateRelevanceScore($startContentId, $contentId)
                    ];
                }
            }

            if ($distance < 2) {
                // Find users who viewed this content
                $viewers = $this->em->getRepository(User::class)
                    ->createQueryBuilder('u')
                    ->innerJoin('u.viewedContent', 'c')
                    ->where('c.id = :contentId')
                    ->setParameter('contentId', $contentId)
                    ->getQuery()
                    ->getResult();

                // Find other content these users viewed
                foreach ($viewers as $viewer) {
                    $otherContent = $viewer->getViewedContent();

                    foreach ($otherContent as $content) {
                        if (!isset($visited[$content->getId()])) {
                            $visited[$content->getId()] = true;
                            $queue[] = [$content->getId(), $distance + 1];
                        }
                    }
                }
            }
        }

        // Sort by relevance score
        usort($related, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($related, 0, $limit);
    }

    private function calculateRelevanceScore(int $sourceId, int $targetId): float
    {
        // Calculate based on multiple factors
        $sharedViewers = $this->countSharedViewers($sourceId, $targetId);
        $categoryMatch = $this->haveSameCategory($sourceId, $targetId) ? 1.5 : 1.0;
        $recency = $this->getRecencyBoost($targetId);

        return $sharedViewers * $categoryMatch * $recency;
    }

    private function countSharedViewers(int $contentId1, int $contentId2): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(DISTINCT u.id)')
            ->from(User::class, 'u')
            ->innerJoin('u.viewedContent', 'c1')
            ->innerJoin('u.viewedContent', 'c2')
            ->where('c1.id = :id1 AND c2.id = :id2')
            ->setParameter('id1', $contentId1)
            ->setParameter('id2', $contentId2)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function haveSameCategory(int $contentId1, int $contentId2): bool
    {
        $content1 = $this->em->find(Content::class, $contentId1);
        $content2 = $this->em->find(Content::class, $contentId2);

        return $content1 && $content2 &&
               $content1->getCategory() === $content2->getCategory();
    }

    private function getRecencyBoost(int $contentId): float
    {
        $content = $this->em->find(Content::class, $contentId);
        if (!$content) return 1.0;

        $daysSincePublished = (new \DateTime())->diff($content->getPublishedAt())->days;

        // Boost recent content
        if ($daysSincePublished < 7) return 1.5;
        if ($daysSincePublished < 30) return 1.2;
        return 1.0;
    }
}
```

## Practical Applications

### 1. Social Network Friend Suggestions

```php
<?php

class FriendSuggestions
{
    // Find friends of friends (2 hops away)
    public function suggestFriends(array $network, string $user): array
    {
        $suggestions = [];
        $visited = [$user => true];
        $queue = [[$user, 0]];  // [user, distance]

        while (!empty($queue)) {
            [$current, $distance] = array_shift($queue);

            if ($distance === 2) {
                // Friends of friends (not already friends)
                $suggestions[] = $current;
                continue;  // Don't explore further
            }

            foreach ($network[$current] ?? [] as $friend) {
                if (!isset($visited[$friend])) {
                    $visited[$friend] = true;
                    $queue[] = [$friend, $distance + 1];
                }
            }
        }

        return $suggestions;
    }
}
```

### 2. Shortest Route in City Map

```php
<?php

class CityRouter
{
    public function findShortestRoute(
        array $cityMap,
        string $start,
        string $destination
    ): ?array {
        if ($start === $destination) {
            return [$start];
        }

        $visited = [$start => true];
        $parent = [$start => null];
        $queue = [$start];

        while (!empty($queue)) {
            $location = array_shift($queue);

            foreach ($cityMap[$location] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $parent[$neighbor] = $location;
                    $queue[] = $neighbor;

                    if ($neighbor === $destination) {
                        return $this->buildPath($parent, $start, $destination);
                    }
                }
            }
        }

        return null;
    }

    private function buildPath(array $parent, string $start, string $end): array
    {
        $path = [];
        $current = $end;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $parent[$current];
        }

        return $path;
    }
}
```

## Best Practices

1. **Use Queue for BFS**
   - Always use FIFO queue (array_shift + array_push)
   - Don't use stack (would be DFS)

2. **Mark as Visited When Enqueued**
   - Prevents duplicates in queue
   - More efficient than checking during dequeue

3. **Track Parent/Distance**
   - For path reconstruction, maintain parent array
   - For distances, maintain distance array

4. **Handle Disconnected Graphs**
   - Loop through all vertices
   - Start BFS from unvisited vertices

## Practice Exercises

1. **Shortest Bridge**
   - Find shortest bridge connecting two islands
   - Multi-source BFS from one island to find other

2. **Rotting Oranges**
   - Fresh oranges rot if adjacent to rotten (multi-source)
   - Find minimum time for all oranges to rot

3. **Snakes and Ladders**
   - Shortest path to reach end of board
   - BFS treating snakes/ladders as edges

4. **Binary Tree Level Order Traversal**
   - Return nodes level by level
   - BFS on tree structure

5. **Open the Lock**
   - 4-digit lock, rotate wheels, avoid deadends
   - BFS through possible combinations

## Key Takeaways

- BFS explores graphs level by level using a queue
- Guarantees shortest path in unweighted graphs
- Time complexity: O(V + E), Space complexity: O(V)
- Optimal for finding shortest paths, level-order traversal, nearby solutions
- Uses more memory than DFS (stores entire level)
- Essential for problems requiring minimum steps/distance
- Natural choice for grid-based pathfinding
- Can start from multiple sources simultaneously
- Mark vertices as visited when enqueued, not when dequeued

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 23 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-23)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-23
php 01-*.php
```

## Next Steps

In the next chapter, we'll explore Dijkstra's algorithm, which extends BFS to find shortest paths in weighted graphs where edges have different costs.
