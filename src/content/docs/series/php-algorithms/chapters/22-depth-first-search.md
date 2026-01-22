---
title: "22: Depth-First Search (DFS)"
description: "Master Depth-First Search for graph traversal, exploring applications like cycle detection, topological sorting, connected components, and path finding"
sidebar:
  label: "22: Depth-First Search (DFS)"
  order: 22
  badge:
    text: Intermediate
    variant: caution
---
![Depth-First Search (DFS)](/images/php-algorithms/chapter-22-depth-first-search-hero-full.webp)


# 22: Depth-First Search (DFS)

## Overview

Depth-First Search is a fundamental graph traversal algorithm that explores as far as possible along each branch before backtracking—like exploring a maze by always taking the left-most unexplored path until you hit a dead end, then backtracking to try the next option. It's the foundation for many powerful graph algorithms and surprisingly elegant in its simplicity.

In this chapter, you'll master both recursive and iterative implementations of DFS, understand how it explores graphs through backtracking, and apply it to solve real-world problems like cycle detection, path finding, topological sorting, and finding connected components. You'll build practical applications including a maze solver and a dependency resolver, gaining deep insight into one of computer science's most versatile algorithms.

By the end of this chapter, you'll understand when DFS is the right choice over breadth-first search, how to optimize it for different graph structures, and how it forms the basis for advanced algorithms like strongly connected components detection.

## Prerequisites

Before starting this chapter, you should have:

- Complete understanding of [graph representations](/series/php-algorithms/chapters/21-graph-representations) (Chapter 21)
- Strong [recursion skills](/series/php-algorithms/chapters/03-recursion-fundamentals) (Chapter 3)
- Knowledge of [stacks and queues](/series/php-algorithms/chapters/17-stacks-queues) (Chapter 17)
- Familiarity with visited/seen tracking patterns

**Estimated Time**: ~55 minutes

## What You'll Build

By the end of this chapter, you will have created:

- A complete DFS implementation class with both recursive and iterative versions
- A path finder that discovers routes between vertices in graphs
- A cycle detector for both directed and undirected graphs
- A topological sort implementation for dependency resolution
- A connected components finder for analyzing graph structure
- A bridge detector for finding critical edges in networks
- An articulation points detector for identifying critical nodes
- A maze solver using depth-first exploration
- A dependency resolver for package management scenarios

## Objectives

- Master the depth-first search algorithm for graph traversal
- Implement both recursive and iterative DFS versions
- Apply DFS to solve cycle detection, path finding, and connected components
- Understand topological sorting with DFS
- Build a maze solver using depth-first exploration

## How DFS Works

DFS explores a graph by:
1. Starting at a source vertex
2. Visiting an unvisited neighbor
3. Recursively exploring from that neighbor
4. Backtracking when no unvisited neighbors remain
5. Continuing until all reachable vertices are visited

```
Graph:        0---1---2
              |   |
              3---4

DFS from 0: 0 → 1 → 2 (backtrack) → 4 (backtrack) → 3 (backtrack)
Order: [0, 1, 2, 4, 3]
```

## DFS Visual Step-by-Step Trace

Understanding DFS execution through detailed visualization:

```php
# filename: dfs-visualizer.php
<?php

declare(strict_types=1);

class DFSVisualizer
{
    private array $graph;
    private array $visited = [];
    private array $steps = [];

    public function __construct(array $graph)
    {
        $this->graph = $graph;
    }

    // Visualize DFS execution step by step
    public function visualizeDFS(int $start): void
    {
        echo "=== DFS Traversal Visualization ===\n\n";
        echo "Graph Structure:\n";
        $this->printGraph();
        echo "\n";

        echo "Starting DFS from vertex $start\n";
        echo str_repeat('=', 50) . "\n\n";

        $this->visited = [];
        $this->steps = [];
        $this->dfsWithVisualization($start, 0);

        echo "\n=== Summary ===\n";
        echo "Visit order: " . implode(' → ', array_column($this->steps, 'vertex')) . "\n";
        echo "Total vertices visited: " . count($this->steps) . "\n";
    }

    private function dfsWithVisualization(int $vertex, int $depth): void
    {
        $step = count($this->steps) + 1;

        // Mark as visited
        $this->visited[$vertex] = true;
        $this->steps[] = ['vertex' => $vertex, 'depth' => $depth];

        // Print current step
        echo "Step $step: Visit vertex $vertex (depth $depth)\n";
        echo "  " . str_repeat('  ', $depth) . "├─ $vertex\n";
        echo "  Visited so far: " . implode(', ', array_keys($this->visited)) . "\n";

        $neighbors = $this->graph[$vertex] ?? [];
        $unvisitedNeighbors = array_filter($neighbors, fn($n) => !isset($this->visited[$n]));

        if (empty($unvisitedNeighbors)) {
            echo "  No unvisited neighbors, backtracking...\n";
        } else {
            echo "  Unvisited neighbors: " . implode(', ', $unvisitedNeighbors) . "\n";
        }
        echo "\n";

        // Explore neighbors
        foreach ($neighbors as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                echo "  Exploring edge $vertex → $neighbor\n\n";
                $this->dfsWithVisualization($neighbor, $depth + 1);
                echo "  Backtracked to $vertex\n\n";
            }
        }
    }

    private function printGraph(): void
    {
        foreach ($this->graph as $vertex => $neighbors) {
            echo "  $vertex: [" . implode(', ', $neighbors) . "]\n";
        }
    }
}

// Example: Visualize DFS on a simple graph
$graph = [
    0 => [1, 3],
    1 => [0, 2, 4],
    2 => [1],
    3 => [0, 4],
    4 => [1, 3]
];

$visualizer = new DFSVisualizer($graph);
$visualizer->visualizeDFS(0);

/*
Output:
=== DFS Traversal Visualization ===

Graph Structure:
  0: [1, 3]
  1: [0, 2, 4]
  2: [1]
  3: [0, 4]
  4: [1, 3]

Starting DFS from vertex 0
==================================================

Step 1: Visit vertex 0 (depth 0)
  ├─ 0
  Visited so far: 0
  Unvisited neighbors: 1, 3

  Exploring edge 0 → 1

Step 2: Visit vertex 1 (depth 1)
    ├─ 1
  Visited so far: 0, 1
  Unvisited neighbors: 2, 4

  Exploring edge 1 → 2

Step 3: Visit vertex 2 (depth 2)
      ├─ 2
  Visited so far: 0, 1, 2
  No unvisited neighbors, backtracking...

  Backtracked to 1

  Exploring edge 1 → 4

Step 4: Visit vertex 4 (depth 2)
      ├─ 4
  Visited so far: 0, 1, 2, 4
  Unvisited neighbors: 3

  Exploring edge 4 → 3

Step 5: Visit vertex 3 (depth 3)
        ├─ 3
  Visited so far: 0, 1, 2, 4, 3
  No unvisited neighbors, backtracking...

  Backtracked to 4

  Backtracked to 1

  Backtracked to 0

=== Summary ===
Visit order: 0 → 1 → 2 → 4 → 3
Total vertices visited: 5
*/
```

## DFS Call Stack Visualization

Understanding recursion and backtracking:

```php
# filename: dfs-call-stack-visualizer.php
<?php

declare(strict_types=1);

class DFSCallStackVisualizer
{
    private array $graph;
    private array $visited = [];
    private array $callStack = [];

    public function __construct(array $graph)
    {
        $this->graph = $graph;
    }

    public function visualizeCallStack(int $start): void
    {
        echo "=== DFS Call Stack Visualization ===\n\n";
        $this->visited = [];
        $this->callStack = [];
        $this->dfs($start);
    }

    private function dfs(int $vertex): void
    {
        // Enter function call
        $this->callStack[] = $vertex;
        $this->printCallStack("ENTER dfs($vertex)");

        $this->visited[$vertex] = true;

        foreach ($this->graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->dfs($neighbor);
            }
        }

        // Exit function call
        $this->printCallStack("EXIT dfs($vertex)");
        array_pop($this->callStack);
    }

    private function printCallStack(string $action): void
    {
        echo "$action\n";
        echo "Call Stack: ";

        if (empty($this->callStack)) {
            echo "[empty]\n";
        } else {
            echo "[" . implode(' → ', $this->callStack) . "]\n";
        }

        echo "Visited: {" . implode(', ', array_keys($this->visited)) . "}\n";
        echo str_repeat('-', 40) . "\n";
    }
}

// Example
$graph = [
    0 => [1, 2],
    1 => [0, 3],
    2 => [0],
    3 => [1]
];

$stackViz = new DFSCallStackVisualizer($graph);
$stackViz->visualizeCallStack(0);

/*
Output shows how the call stack grows and shrinks:

=== DFS Call Stack Visualization ===

ENTER dfs(0)
Call Stack: [0]
Visited: {0}
----------------------------------------
ENTER dfs(1)
Call Stack: [0 → 1]
Visited: {0, 1}
----------------------------------------
ENTER dfs(3)
Call Stack: [0 → 1 → 3]
Visited: {0, 1, 3}
----------------------------------------
EXIT dfs(3)
Call Stack: [0 → 1]
Visited: {0, 1, 3}
----------------------------------------
EXIT dfs(1)
Call Stack: [0]
Visited: {0, 1, 3}
----------------------------------------
ENTER dfs(2)
Call Stack: [0 → 2]
Visited: {0, 1, 3, 2}
----------------------------------------
EXIT dfs(2)
Call Stack: [0]
Visited: {0, 1, 3, 2}
----------------------------------------
EXIT dfs(0)
Call Stack: [empty]
Visited: {0, 1, 3, 2}
----------------------------------------
*/
```

## Recursive DFS Implementation

```php
# filename: recursive-dfs.php
<?php

declare(strict_types=1);

class DFS
{
    private array $visited = [];
    private array $result = [];

    // DFS traversal (recursive)
    public function traverse(array $graph, int $start): array
    {
        $this->visited = [];
        $this->result = [];

        $this->dfsRecursive($graph, $start);

        return $this->result;
    }

    private function dfsRecursive(array $graph, int $vertex): void
    {
        // Mark current vertex as visited
        $this->visited[$vertex] = true;
        $this->result[] = $vertex;

        // Visit all unvisited neighbors
        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->dfsRecursive($graph, $neighbor);
            }
        }
    }

    // DFS for all components (handles disconnected graphs)
    public function traverseAll(array $graph): array
    {
        $this->visited = [];
        $this->result = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->dfsRecursive($graph, $vertex);
            }
        }

        return $this->result;
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

$dfs = new DFS();
print_r($dfs->traverse($graph, 0));  // [0, 1, 2, 4, 3]

// Disconnected graph
$disconnected = [
    0 => [1],
    1 => [0],
    2 => [3],
    3 => [2]
];

print_r($dfs->traverseAll($disconnected));  // [0, 1, 2, 3]
```

## Iterative DFS Implementation

Using an explicit stack instead of recursion.

```php
# filename: iterative-dfs.php
<?php

declare(strict_types=1);

class IterativeDFS
{
    // DFS traversal (iterative using stack)
    public function traverse(array $graph, int $start): array
    {
        $visited = [];
        $result = [];
        $stack = [$start];

        while (!empty($stack)) {
            $vertex = array_pop($stack);

            if (!isset($visited[$vertex])) {
                $visited[$vertex] = true;
                $result[] = $vertex;

                // Add neighbors to stack in reverse order
                // to maintain same order as recursive DFS
                $neighbors = array_reverse($graph[$vertex] ?? []);
                foreach ($neighbors as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $stack[] = $neighbor;
                    }
                }
            }
        }

        return $result;
    }

    // Alternative: Visit immediately when pushed to stack
    public function traverseEager(array $graph, int $start): array
    {
        $visited = [$start => true];
        $result = [$start];
        $stack = [$start];

        while (!empty($stack)) {
            $vertex = array_pop($stack);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $result[] = $neighbor;
                    $stack[] = $neighbor;
                }
            }
        }

        return $result;
    }
}

// Example
$graph = [
    0 => [1, 3],
    1 => [0, 2, 4],
    2 => [1],
    3 => [0, 4],
    4 => [1, 3]
];

$dfs = new IterativeDFS();
print_r($dfs->traverse($graph, 0));       // [0, 1, 2, 4, 3]
print_r($dfs->traverseEager($graph, 0));  // [0, 1, 2, 4, 3]
```

## Path Finding with DFS

Finding a path between two vertices.

```php
# filename: dfs-path-finder.php
<?php

declare(strict_types=1);

class DFSPathFinder
{
    private array $visited = [];
    private array $path = [];

    // Find a path from start to end
    public function findPath(array $graph, int $start, int $end): ?array
    {
        $this->visited = [];
        $this->path = [];

        if ($this->dfsPath($graph, $start, $end)) {
            return $this->path;
        }

        return null;  // No path found
    }

    private function dfsPath(array $graph, int $current, int $end): bool
    {
        $this->visited[$current] = true;
        $this->path[] = $current;

        // Found the destination
        if ($current === $end) {
            return true;
        }

        // Explore neighbors
        foreach ($graph[$current] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                if ($this->dfsPath($graph, $neighbor, $end)) {
                    return true;
                }
            }
        }

        // Dead end - backtrack
        array_pop($this->path);
        return false;
    }

    // Find all paths from start to end
    public function findAllPaths(array $graph, int $start, int $end): array
    {
        $this->visited = [];
        $allPaths = [];
        $this->findAllPathsHelper($graph, $start, $end, [], $allPaths);
        return $allPaths;
    }

    private function findAllPathsHelper(
        array $graph,
        int $current,
        int $end,
        array $currentPath,
        array &$allPaths
    ): void {
        $this->visited[$current] = true;
        $currentPath[] = $current;

        if ($current === $end) {
            $allPaths[] = $currentPath;
        } else {
            foreach ($graph[$current] ?? [] as $neighbor) {
                if (!isset($this->visited[$neighbor])) {
                    $this->findAllPathsHelper($graph, $neighbor, $end, $currentPath, $allPaths);
                }
            }
        }

        // Backtrack: unmark for other paths
        unset($this->visited[$current]);
    }
}

// Example
$graph = [
    0 => [1, 2],
    1 => [3],
    2 => [1, 3],
    3 => []
];

$pathFinder = new DFSPathFinder();
print_r($pathFinder->findPath($graph, 0, 3));
// [0, 1, 3] or [0, 2, 3]

print_r($pathFinder->findAllPaths($graph, 0, 3));
// [[0, 1, 3], [0, 2, 1, 3], [0, 2, 3]]
```

## Cycle Detection

Detecting cycles in directed and undirected graphs.

```php
# filename: cycle-detector.php
<?php

declare(strict_types=1);

class CycleDetector
{
    private array $visited = [];
    private array $recursionStack = [];

    // Detect cycle in directed graph
    public function hasCycleDirected(array $graph): bool
    {
        $this->visited = [];
        $this->recursionStack = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                if ($this->hasCycleDirectedUtil($graph, $vertex)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasCycleDirectedUtil(array $graph, int $vertex): bool
    {
        $this->visited[$vertex] = true;
        $this->recursionStack[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                if ($this->hasCycleDirectedUtil($graph, $neighbor)) {
                    return true;
                }
            } elseif (isset($this->recursionStack[$neighbor])) {
                // Back edge found - cycle exists
                return true;
            }
        }

        unset($this->recursionStack[$vertex]);
        return false;
    }

    // Detect cycle in undirected graph
    public function hasCycleUndirected(array $graph): bool
    {
        $this->visited = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                if ($this->hasCycleUndirectedUtil($graph, $vertex, -1)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasCycleUndirectedUtil(array $graph, int $vertex, int $parent): bool
    {
        $this->visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                if ($this->hasCycleUndirectedUtil($graph, $neighbor, $vertex)) {
                    return true;
                }
            } elseif ($neighbor !== $parent) {
                // Visited neighbor that's not parent - cycle found
                return true;
            }
        }

        return false;
    }
}

// Example - Directed graph with cycle
$directedCycle = [
    0 => [1],
    1 => [2],
    2 => [0]  // Cycle: 0 → 1 → 2 → 0
];

$detector = new CycleDetector();
echo $detector->hasCycleDirected($directedCycle) ? "Cycle found\n" : "No cycle\n";
// Cycle found

// Example - Undirected graph with cycle
$undirectedCycle = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1]  // Cycle: 0-1-2-0
];

echo $detector->hasCycleUndirected($undirectedCycle) ? "Cycle found\n" : "No cycle\n";
// Cycle found
```

## Topological Sort

Ordering vertices in a Directed Acyclic Graph (DAG) such that for every edge u→v, u comes before v.

```php
# filename: topological-sort.php
<?php

declare(strict_types=1);

class TopologicalSort
{
    private array $visited = [];
    private array $stack = [];

    // Topological sort using DFS
    public function sort(array $graph): ?array
    {
        $this->visited = [];
        $this->stack = [];

        // Check for cycles first (topological sort only works on DAGs)
        $cycleDetector = new CycleDetector();
        if ($cycleDetector->hasCycleDirected($graph)) {
            return null;  // Graph has cycle, cannot perform topological sort
        }

        // Perform DFS for all vertices
        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->topologicalSortUtil($graph, $vertex);
            }
        }

        // Stack contains vertices in reverse topological order
        return array_reverse($this->stack);
    }

    private function topologicalSortUtil(array $graph, int $vertex): void
    {
        $this->visited[$vertex] = true;

        // Visit all neighbors
        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->topologicalSortUtil($graph, $neighbor);
            }
        }

        // Push to stack after visiting all descendants
        $this->stack[] = $vertex;
    }
}

// Example - Course prerequisites
$courses = [
    'Intro to CS' => ['Data Structures'],
    'Data Structures' => ['Algorithms'],
    'Algorithms' => ['Advanced Algorithms'],
    'Advanced Algorithms' => [],
    'Math' => ['Algorithms']
];

// Convert to numeric indices
$courseNames = array_keys($courses);
$graph = [];
foreach ($courses as $course => $prereqs) {
    $courseIdx = array_search($course, $courseNames);
    $graph[$courseIdx] = [];
    foreach ($prereqs as $prereq) {
        $prereqIdx = array_search($prereq, $courseNames);
        $graph[$courseIdx][] = $prereqIdx;
    }
}

$topo = new TopologicalSort();
$order = $topo->sort($graph);

if ($order !== null) {
    echo "Course order:\n";
    foreach ($order as $idx) {
        echo $courseNames[$idx] . "\n";
    }
}
// Output:
// Math
// Intro to CS
// Data Structures
// Algorithms
// Advanced Algorithms
```

## Connected Components

Finding all connected components in an undirected graph.

```php
# filename: connected-components.php
<?php

declare(strict_types=1);

class ConnectedComponents
{
    private array $visited = [];
    private array $component = [];

    // Find all connected components
    public function findComponents(array $graph): array
    {
        $this->visited = [];
        $components = [];

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->component = [];
                $this->dfs($graph, $vertex);
                $components[] = $this->component;
            }
        }

        return $components;
    }

    private function dfs(array $graph, int $vertex): void
    {
        $this->visited[$vertex] = true;
        $this->component[] = $vertex;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->dfs($graph, $neighbor);
            }
        }
    }

    // Count connected components
    public function countComponents(array $graph): int
    {
        return count($this->findComponents($graph));
    }

    // Check if two vertices are in same component
    public function areConnected(array $graph, int $v1, int $v2): bool
    {
        $components = $this->findComponents($graph);

        foreach ($components as $component) {
            if (in_array($v1, $component) && in_array($v2, $component)) {
                return true;
            }
        }

        return false;
    }
}

// Example - Social network with separate groups
$network = [
    0 => [1, 2],      // Group 1
    1 => [0, 2],
    2 => [0, 1],
    3 => [4],         // Group 2
    4 => [3],
    5 => []           // Group 3 (isolated)
];

$cc = new ConnectedComponents();
print_r($cc->findComponents($network));
// [[0, 1, 2], [3, 4], [5]]

echo "Number of components: " . $cc->countComponents($network) . "\n";
// Number of components: 3

echo "Are 0 and 2 connected? " . ($cc->areConnected($network, 0, 2) ? "Yes" : "No") . "\n";
// Are 0 and 2 connected? Yes

echo "Are 0 and 3 connected? " . ($cc->areConnected($network, 0, 3) ? "Yes" : "No") . "\n";
// Are 0 and 3 connected? No
```

## DFS with Timestamps

Recording discovery and finish times for each vertex.

```php
# filename: dfs-timestamps.php
<?php

declare(strict_types=1);

class DFSWithTimestamps
{
    private array $visited = [];
    private array $discoveryTime = [];
    private array $finishTime = [];
    private int $time = 0;

    public function traverse(array $graph): array
    {
        $this->visited = [];
        $this->discoveryTime = [];
        $this->finishTime = [];
        $this->time = 0;

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->dfs($graph, $vertex);
            }
        }

        return [
            'discovery' => $this->discoveryTime,
            'finish' => $this->finishTime
        ];
    }

    private function dfs(array $graph, int $vertex): void
    {
        $this->time++;
        $this->discoveryTime[$vertex] = $this->time;
        $this->visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->dfs($graph, $neighbor);
            }
        }

        $this->time++;
        $this->finishTime[$vertex] = $this->time;
    }

    // Classify edges based on timestamps
    public function classifyEdges(array $graph): array
    {
        $this->traverse($graph);

        $edges = [
            'tree' => [],      // Discovery edges
            'back' => [],      // Point to ancestor (cycle indicator)
            'forward' => [],   // Point to descendant
            'cross' => []      // All others
        ];

        foreach ($graph as $u => $neighbors) {
            foreach ($neighbors as $v) {
                if ($this->discoveryTime[$u] < $this->discoveryTime[$v] &&
                    $this->finishTime[$u] > $this->finishTime[$v]) {
                    $edges['tree'][] = [$u, $v];
                } elseif ($this->discoveryTime[$u] > $this->discoveryTime[$v] &&
                          $this->finishTime[$u] < $this->finishTime[$v]) {
                    $edges['back'][] = [$u, $v];
                } elseif ($this->discoveryTime[$u] < $this->discoveryTime[$v]) {
                    $edges['forward'][] = [$u, $v];
                } else {
                    $edges['cross'][] = [$u, $v];
                }
            }
        }

        return $edges;
    }
}

// Example
$graph = [
    0 => [1, 2],
    1 => [3],
    2 => [3],
    3 => []
];

$dfs = new DFSWithTimestamps();
$times = $dfs->traverse($graph);

echo "Discovery times:\n";
print_r($times['discovery']);
// [0 => 1, 1 => 2, 3 => 3, 2 => 5]

echo "Finish times:\n";
print_r($times['finish']);
// [0 => 8, 1 => 4, 3 => 4, 2 => 6]
```

## Bridge Detection (Cut Edges)

Finding edges whose removal disconnects the graph. Bridges are critical edges in network analysis.

```php
# filename: bridge-detector.php
<?php

declare(strict_types=1);

class BridgeDetector
{
    private array $visited = [];
    private array $discoveryTime = [];
    private array $lowTime = [];
    private array $bridges = [];
    private int $time = 0;

    // Find all bridges in undirected graph
    public function findBridges(array $graph): array
    {
        $this->visited = [];
        $this->discoveryTime = [];
        $this->lowTime = [];
        $this->bridges = [];
        $this->time = 0;

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->dfs($graph, $vertex, -1);
            }
        }

        return $this->bridges;
    }

    private function dfs(array $graph, int $vertex, int $parent): void
    {
        $this->time++;
        $this->discoveryTime[$vertex] = $this->time;
        $this->lowTime[$vertex] = $this->time;
        $this->visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if ($neighbor === $parent) {
                continue;  // Skip parent edge
            }

            if (!isset($this->visited[$neighbor])) {
                // Tree edge - explore child
                $this->dfs($graph, $neighbor, $vertex);

                // Update low time: can we reach ancestor through child?
                $this->lowTime[$vertex] = min(
                    $this->lowTime[$vertex],
                    $this->lowTime[$neighbor]
                );

                // Bridge found: if low time of child > discovery time of parent
                // No back edge from subtree to ancestor
                if ($this->lowTime[$neighbor] > $this->discoveryTime[$vertex]) {
                    $this->bridges[] = [$vertex, $neighbor];
                }
            } else {
                // Back edge - update low time
                $this->lowTime[$vertex] = min(
                    $this->lowTime[$vertex],
                    $this->discoveryTime[$neighbor]
                );
            }
        }
    }
}

// Example - Network with bridges
$network = [
    0 => [1, 2],
    1 => [0, 2],
    2 => [0, 1, 3],
    3 => [2, 4],
    4 => [3]
];

$detector = new BridgeDetector();
$bridges = $detector->findBridges($network);
print_r($bridges);
// [[2, 3], [3, 4]]
// Edge 2-3 is a bridge (removing it disconnects 3,4 from rest)
// Edge 3-4 is a bridge (removing it isolates vertex 4)
```

## Articulation Points (Cut Vertices)

Finding vertices whose removal disconnects the graph. Critical nodes in network analysis.

```php
# filename: articulation-points.php
<?php

declare(strict_types=1);

class ArticulationPointsDetector
{
    private array $visited = [];
    private array $discoveryTime = [];
    private array $lowTime = [];
    private array $articulationPoints = [];
    private int $time = 0;

    // Find all articulation points in undirected graph
    public function findArticulationPoints(array $graph): array
    {
        $this->visited = [];
        $this->discoveryTime = [];
        $this->lowTime = [];
        $this->articulationPoints = [];
        $this->time = 0;

        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $children = 0;
                $this->dfs($graph, $vertex, -1, $children);

                // Root is articulation point if it has > 1 child
                if ($children > 1) {
                    $this->articulationPoints[] = $vertex;
                }
            }
        }

        return array_unique($this->articulationPoints);
    }

    private function dfs(
        array $graph,
        int $vertex,
        int $parent,
        int &$children
    ): void {
        $this->time++;
        $this->discoveryTime[$vertex] = $this->time;
        $this->lowTime[$vertex] = $this->time;
        $this->visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if ($neighbor === $parent) {
                continue;
            }

            if (!isset($this->visited[$neighbor])) {
                $children++;
                $this->dfs($graph, $neighbor, $vertex, $children);

                // Update low time
                $this->lowTime[$vertex] = min(
                    $this->lowTime[$vertex],
                    $this->lowTime[$neighbor]
                );

                // Articulation point: if low time of child >= discovery time of parent
                // No back edge from subtree to ancestor
                if ($parent !== -1 && 
                    $this->lowTime[$neighbor] >= $this->discoveryTime[$vertex]) {
                    $this->articulationPoints[] = $vertex;
                }
            } else {
                // Back edge
                $this->lowTime[$vertex] = min(
                    $this->lowTime[$vertex],
                    $this->discoveryTime[$neighbor]
                );
            }
        }
    }
}

// Example - Social network with critical nodes
$network = [
    0 => [1],
    1 => [0, 2, 3],
    2 => [1],
    3 => [1, 4],
    4 => [3]
];

$detector = new ArticulationPointsDetector();
$points = $detector->findArticulationPoints($network);
print_r($points);
// [1, 3]
// Vertex 1 is critical (removing it disconnects 0 from rest)
// Vertex 3 is critical (removing it disconnects 4 from rest)
```

## Strongly Connected Components (Kosaraju's Algorithm)

Finding strongly connected components in directed graphs.

```php
# filename: strongly-connected-components.php
<?php

declare(strict_types=1);

class StronglyConnectedComponents
{
    private array $visited = [];
    private array $stack = [];

    // Find all strongly connected components
    public function findSCC(array $graph): array
    {
        $this->visited = [];
        $this->stack = [];

        // Step 1: Fill stack with vertices in finish time order
        foreach (array_keys($graph) as $vertex) {
            if (!isset($this->visited[$vertex])) {
                $this->fillOrder($graph, $vertex);
            }
        }

        // Step 2: Create transpose graph (reverse all edges)
        $transpose = $this->getTranspose($graph);

        // Step 3: DFS on transpose in order from stack
        $this->visited = [];
        $sccs = [];

        while (!empty($this->stack)) {
            $vertex = array_pop($this->stack);
            if (!isset($this->visited[$vertex])) {
                $component = [];
                $this->dfsCollect($transpose, $vertex, $component);
                $sccs[] = $component;
            }
        }

        return $sccs;
    }

    private function fillOrder(array $graph, int $vertex): void
    {
        $this->visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->fillOrder($graph, $neighbor);
            }
        }

        $this->stack[] = $vertex;
    }

    private function getTranspose(array $graph): array
    {
        $transpose = [];

        foreach ($graph as $u => $neighbors) {
            foreach ($neighbors as $v) {
                $transpose[$v][] = $u;
            }
        }

        return $transpose;
    }

    private function dfsCollect(array $graph, int $vertex, array &$component): void
    {
        $this->visited[$vertex] = true;
        $component[] = $vertex;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($this->visited[$neighbor])) {
                $this->dfsCollect($graph, $neighbor, $component);
            }
        }
    }
}

// Example - Web pages with links
$web = [
    0 => [1],
    1 => [2],
    2 => [0, 3],
    3 => [4],
    4 => [5],
    5 => [3]
];

$scc = new StronglyConnectedComponents();
print_r($scc->findSCC($web));
// [[0, 2, 1], [3, 5, 4]]
// Two SCCs: pages 0,1,2 form a cycle, pages 3,4,5 form another cycle
```

## Complexity Analysis

| Operation | Time Complexity | Space Complexity |
|-----------|----------------|------------------|
| DFS Traversal | O(V + E) | O(V) |
| Path Finding | O(V + E) | O(V) |
| Cycle Detection | O(V + E) | O(V) |
| Topological Sort | O(V + E) | O(V) |
| Connected Components | O(V + E) | O(V) |
| Strongly Connected Components | O(V + E) | O(V) |

**Where**:
- V = number of vertices
- E = number of edges

**Space breakdown**:
- Visited array: O(V)
- Recursion stack (recursive): O(V) worst case
- Explicit stack (iterative): O(V)

## Performance Analysis and Benchmarks

Comparing DFS performance across different graph types:

```php
# filename: dfs-performance-analyzer.php
<?php

declare(strict_types=1);

class DFSPerformanceAnalyzer
{
    // Benchmark DFS on different graph types
    public function benchmarkDFS(string $graphType, int $vertices): array
    {
        $graph = $this->generateGraph($graphType, $vertices);
        $results = [];

        // Recursive DFS
        $start = microtime(true);
        $visited = [];
        $this->dfsRecursive($graph, 0, $visited);
        $recursiveTime = microtime(true) - $start;
        $results['recursive'] = [
            'time' => round($recursiveTime * 1000, 3) . 'ms',
            'visited' => count($visited)
        ];

        // Iterative DFS
        $start = microtime(true);
        $visited = $this->dfsIterative($graph, 0);
        $iterativeTime = microtime(true) - $start;
        $results['iterative'] = [
            'time' => round($iterativeTime * 1000, 3) . 'ms',
            'visited' => count($visited)
        ];

        $results['graph_type'] = $graphType;
        $results['vertices'] = $vertices;
        $results['edges'] = $this->countEdges($graph);

        return $results;
    }

    private function generateGraph(string $type, int $vertices): array
    {
        $graph = array_fill(0, $vertices, []);

        switch ($type) {
            case 'linear':
                // Linear chain: 0-1-2-3-...
                for ($i = 0; $i < $vertices - 1; $i++) {
                    $graph[$i][] = $i + 1;
                    $graph[$i + 1][] = $i;
                }
                break;

            case 'complete':
                // Complete graph: all vertices connected
                for ($i = 0; $i < $vertices; $i++) {
                    for ($j = $i + 1; $j < $vertices; $j++) {
                        $graph[$i][] = $j;
                        $graph[$j][] = $i;
                    }
                }
                break;

            case 'tree':
                // Binary tree structure
                for ($i = 0; $i < $vertices; $i++) {
                    $left = 2 * $i + 1;
                    $right = 2 * $i + 2;

                    if ($left < $vertices) {
                        $graph[$i][] = $left;
                        $graph[$left][] = $i;
                    }
                    if ($right < $vertices) {
                        $graph[$i][] = $right;
                        $graph[$right][] = $i;
                    }
                }
                break;

            case 'sparse':
                // Sparse random graph (each vertex connects to 2-3 others)
                for ($i = 0; $i < $vertices; $i++) {
                    $connections = rand(2, 3);
                    for ($j = 0; $j < $connections; $j++) {
                        $target = rand(0, $vertices - 1);
                        if ($target !== $i && !in_array($target, $graph[$i])) {
                            $graph[$i][] = $target;
                            $graph[$target][] = $i;
                        }
                    }
                }
                break;
        }

        return $graph;
    }

    private function dfsRecursive(array $graph, int $vertex, array &$visited): void
    {
        $visited[$vertex] = true;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $this->dfsRecursive($graph, $neighbor, $visited);
            }
        }
    }

    private function dfsIterative(array $graph, int $start): array
    {
        $visited = [$start => true];
        $stack = [$start];

        while (!empty($stack)) {
            $vertex = array_pop($stack);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $stack[] = $neighbor;
                }
            }
        }

        return $visited;
    }

    private function countEdges(array $graph): int
    {
        $count = 0;
        foreach ($graph as $neighbors) {
            $count += count($neighbors);
        }
        return $count / 2;  // Each edge counted twice
    }
}

// Run benchmarks
$analyzer = new DFSPerformanceAnalyzer();

echo "=== DFS Performance Analysis ===\n\n";

$graphTypes = ['linear', 'tree', 'sparse', 'complete'];
$sizes = [100, 500, 1000];

foreach ($graphTypes as $type) {
    foreach ($sizes as $size) {
        echo "Graph Type: $type, Vertices: $size\n";
        $results = $analyzer->benchmarkDFS($type, $size);
        echo "  Edges: {$results['edges']}\n";
        echo "  Recursive DFS: {$results['recursive']['time']} ({$results['recursive']['visited']} visited)\n";
        echo "  Iterative DFS: {$results['iterative']['time']} ({$results['iterative']['visited']} visited)\n";
        echo "\n";
    }
}

/*
Example Output:

=== DFS Performance Analysis ===

Graph Type: linear, Vertices: 100
  Edges: 99
  Recursive DFS: 0.234ms (100 visited)
  Iterative DFS: 0.198ms (100 visited)

Graph Type: linear, Vertices: 1000
  Edges: 999
  Recursive DFS: 2.456ms (1000 visited)
  Iterative DFS: 2.102ms (1000 visited)

Graph Type: complete, Vertices: 100
  Edges: 4950
  Recursive DFS: 1.845ms (100 visited)
  Iterative DFS: 1.632ms (100 visited)

Graph Type: tree, Vertices: 100
  Edges: 99
  Recursive DFS: 0.312ms (100 visited)
  Iterative DFS: 0.267ms (100 visited)

Key Findings:
- Iterative DFS is slightly faster (no function call overhead)
- Performance is O(V+E) regardless of graph structure
- Deep graphs may cause recursion stack overflow
- Complete graphs have more edge checks but same vertex visits
*/
```

## DFS vs BFS Comparison with Visualization

Understanding when to use DFS vs BFS:

```php
# filename: dfs-vs-bfs-comparison.php
<?php

declare(strict_types=1);

class DFSvsBFSComparison
{
    // Compare DFS and BFS on same graph
    public function compareTraversals(array $graph, int $start): array
    {
        echo "=== DFS vs BFS Comparison ===\n\n";
        echo "Graph: " . $this->graphToString($graph) . "\n\n";

        // DFS traversal
        $dfsOrder = [];
        $visited = [];
        $this->dfs($graph, $start, $visited, $dfsOrder);

        // BFS traversal
        $bfsOrder = $this->bfs($graph, $start);

        // Visualize differences
        echo "DFS Order: " . implode(' → ', $dfsOrder) . "\n";
        echo "  (Goes deep first: explores entire branch before moving to next)\n\n";

        echo "BFS Order: " . implode(' → ', $bfsOrder) . "\n";
        echo "  (Goes wide first: explores all neighbors before going deeper)\n\n";

        return [
            'dfs' => $dfsOrder,
            'bfs' => $bfsOrder,
            'same_order' => $dfsOrder === $bfsOrder
        ];
    }

    private function dfs(array $graph, int $vertex, array &$visited, array &$order): void
    {
        $visited[$vertex] = true;
        $order[] = $vertex;

        foreach ($graph[$vertex] ?? [] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $this->dfs($graph, $neighbor, $visited, $order);
            }
        }
    }

    private function bfs(array $graph, int $start): array
    {
        $visited = [$start => true];
        $order = [$start];
        $queue = [$start];

        while (!empty($queue)) {
            $vertex = array_shift($queue);

            foreach ($graph[$vertex] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $order[] = $neighbor;
                    $queue[] = $neighbor;
                }
            }
        }

        return $order;
    }

    private function graphToString(array $graph): string
    {
        $parts = [];
        foreach ($graph as $v => $neighbors) {
            $parts[] = "$v:[" . implode(',', $neighbors) . "]";
        }
        return implode(', ', $parts);
    }

    // Visualize tree structure exploration
    public function visualizeTreeExploration(): void
    {
        echo "\n=== Tree Exploration Pattern ===\n\n";

        echo "Tree Structure:\n";
        echo "        1\n";
        echo "      /   \\\n";
        echo "     2     3\n";
        echo "    / \\   / \\\n";
        echo "   4   5 6   7\n\n";

        $tree = [
            1 => [2, 3],
            2 => [1, 4, 5],
            3 => [1, 6, 7],
            4 => [2],
            5 => [2],
            6 => [3],
            7 => [3]
        ];

        echo "DFS (Recursive): Explores left subtree completely first\n";
        echo "  Path: 1 → 2 → 4 (backtrack) → 5 (backtrack) → 3 → 6 (backtrack) → 7\n";
        echo "  Like: Going deep into a maze before exploring alternatives\n\n";

        echo "BFS (Queue): Explores level by level\n";
        echo "  Path: 1 (level 0) → 2, 3 (level 1) → 4, 5, 6, 7 (level 2)\n";
        echo "  Like: Ripples spreading from a stone dropped in water\n\n";

        $comparison = $this->compareTraversals($tree, 1);
    }
}

$comparer = new DFSvsBFSComparison();
$comparer->visualizeTreeExploration();

/*
Output:
=== Tree Exploration Pattern ===

Tree Structure:
        1
      /   \
     2     3
    / \   / \
   4   5 6   7

DFS (Recursive): Explores left subtree completely first
  Path: 1 → 2 → 4 (backtrack) → 5 (backtrack) → 3 → 6 (backtrack) → 7
  Like: Going deep into a maze before exploring alternatives

BFS (Queue): Explores level by level
  Path: 1 (level 0) → 2, 3 (level 1) → 4, 5, 6, 7 (level 2)
  Like: Ripples spreading from a stone dropped in water

=== DFS vs BFS Comparison ===

Graph: 1:[2,3], 2:[1,4,5], 3:[1,6,7], 4:[2], 5:[2], 6:[3], 7:[3]

DFS Order: 1 → 2 → 4 → 5 → 3 → 6 → 7
  (Goes deep first: explores entire branch before moving to next)

BFS Order: 1 → 2 → 3 → 4 → 5 → 6 → 7
  (Goes wide first: explores all neighbors before going deeper)
*/
```

## Practical Applications

### 1. Maze Solver

```php
# filename: maze-solver.php
<?php

declare(strict_types=1);

class MazeSolver
{
    private array $visited = [];
    private const WALL = '#';
    private const PATH = '.';
    private const START = 'S';
    private const END = 'E';

    public function solve(array $maze): ?array
    {
        // Find start position
        $start = $this->findPosition($maze, self::START);
        if ($start === null) {
            return null;
        }

        $this->visited = [];
        $path = [];

        if ($this->dfs($maze, $start[0], $start[1], $path)) {
            return $path;
        }

        return null;  // No solution
    }

    private function dfs(array $maze, int $row, int $col, array &$path): bool
    {
        // Check bounds
        if ($row < 0 || $row >= count($maze) ||
            $col < 0 || $col >= count($maze[0])) {
            return false;
        }

        // Check if wall or visited
        $key = "$row,$col";
        if ($maze[$row][$col] === self::WALL || isset($this->visited[$key])) {
            return false;
        }

        $this->visited[$key] = true;
        $path[] = [$row, $col];

        // Check if reached end
        if ($maze[$row][$col] === self::END) {
            return true;
        }

        // Try all 4 directions: up, right, down, left
        $directions = [[-1, 0], [0, 1], [1, 0], [0, -1]];
        foreach ($directions as [$dr, $dc]) {
            if ($this->dfs($maze, $row + $dr, $col + $dc, $path)) {
                return true;
            }
        }

        // Backtrack
        array_pop($path);
        return false;
    }

    private function findPosition(array $maze, string $target): ?array
    {
        for ($row = 0; $row < count($maze); $row++) {
            for ($col = 0; $col < count($maze[0]); $col++) {
                if ($maze[$row][$col] === $target) {
                    return [$row, $col];
                }
            }
        }
        return null;
    }
}

// Example
$maze = [
    ['S', '.', '#', '.'],
    ['.', '.', '#', '.'],
    ['#', '.', '.', '.'],
    ['#', '#', '#', 'E']
];

$solver = new MazeSolver();
$path = $solver->solve($maze);
print_r($path);
// [[0,0], [1,0], [1,1], [2,1], [2,2], [2,3], [3,3]]
```

### 2. File System Directory Walker

```php
# filename: directory-walker.php
<?php

declare(strict_types=1);

class DirectoryWalker
{
    private array $files = [];

    public function walkDFS(string $dir): array
    {
        $this->files = [];
        $this->dfs($dir);
        return $this->files;
    }

    private function dfs(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . '/' . $item;

            if (is_file($fullPath)) {
                $this->files[] = $fullPath;
            } elseif (is_dir($fullPath)) {
                $this->dfs($fullPath);  // Recursively explore subdirectory
            }
        }
    }
}
```

### 3. Dependency Resolver

```php
# filename: dependency-resolver.php
<?php

declare(strict_types=1);

class DependencyResolver
{
    private array $visited = [];
    private array $resolved = [];
    private array $unresolved = [];

    public function resolve(array $dependencies, string $package): array
    {
        $this->visited = [];
        $this->resolved = [];
        $this->unresolved = [];

        $this->resolveDependencies($dependencies, $package);

        return $this->resolved;
    }

    private function resolveDependencies(array $dependencies, string $package): void
    {
        $this->unresolved[$package] = true;

        foreach ($dependencies[$package] ?? [] as $dependency) {
            if (!isset($this->visited[$dependency])) {
                if (isset($this->unresolved[$dependency])) {
                    throw new RuntimeException("Circular dependency detected: $package -> $dependency");
                }
                $this->resolveDependencies($dependencies, $dependency);
            }
        }

        $this->visited[$package] = true;
        unset($this->unresolved[$package]);
        $this->resolved[] = $package;
    }
}

// Example
$deps = [
    'app' => ['database', 'logger'],
    'database' => ['logger', 'config'],
    'logger' => ['config'],
    'config' => []
];

$resolver = new DependencyResolver();
$order = $resolver->resolve($deps, 'app');
print_r($order);
// ['config', 'logger', 'database', 'app']
```

## Best Practices

1. **Choose Recursive vs Iterative**
   - Recursive: Cleaner, easier to understand
   - Iterative: Avoids stack overflow for deep graphs

2. **Track Visited Vertices**
   - Always maintain visited set to avoid infinite loops
   - Reset visited set between independent DFS calls

3. **Handle Disconnected Graphs**
   - Iterate through all vertices
   - Start DFS from unvisited vertices

4. **Use Appropriate Data Structures**
   - Array/hash map for visited tracking
   - Stack for iterative DFS
   - Recursion stack for recursive DFS

## Limitations

While DFS is powerful, it's not always the best choice:

1. **Doesn't Guarantee Shortest Path**
   - DFS finds a path, but not necessarily the shortest
   - Use BFS for shortest paths in unweighted graphs
   - Use Dijkstra's algorithm for weighted graphs

2. **Stack Overflow Risk**
   - Deep graphs can cause recursion stack overflow
   - Use iterative DFS for graphs with depth > 10,000
   - Consider iterative deepening DFS (IDDFS) for very deep graphs

3. **May Explore Unnecessary Paths**
   - DFS explores entire branches even if solution is nearby
   - Can be inefficient in wide, shallow search spaces
   - BFS may be better when solutions are near the start

4. **Not Optimal for Level-Order Problems**
   - Problems requiring level-by-level processing favor BFS
   - Examples: level-order tree traversal, shortest path problems

5. **Memory Usage in Deep Graphs**
   - Recursive DFS uses O(V) stack space in worst case
   - Very deep graphs may exhaust available memory
   - Iterative DFS with explicit stack has same space complexity

6. **Requires Careful Visited Tracking**
   - Missing visited checks cause infinite loops
   - Must reset visited set between independent DFS calls
   - Different visited strategies needed for different problems (e.g., all paths vs single path)

## Practice Exercises

1. **Island Counting**
   - Count number of islands in a 2D grid (1 = land, 0 = water)
   - Use DFS to explore connected land cells

2. **Detect Bridges**
   - Find bridges (edges whose removal disconnects graph)
   - Use DFS with timestamps

3. **Word Ladder**
   - Transform one word to another by changing one letter at a time
   - Build graph of valid transformations, use DFS

4. **Surrounded Regions**
   - Find regions completely surrounded by another value
   - DFS from borders to mark reachable cells

5. **Course Schedule**
   - Check if courses can be completed given prerequisites
   - Cycle detection in dependency graph

## Troubleshooting

### Error: "Maximum function nesting level reached"

**Symptom**: `Fatal error: Maximum function nesting level of '256' reached`

**Cause**: Deep recursion in recursive DFS implementation exceeds PHP's recursion limit

**Solution**: 
- Use iterative DFS instead of recursive DFS
- Increase recursion limit (not recommended): `ini_set('xdebug.max_nesting_level', 1000);`
- For very deep graphs, always prefer iterative implementation:

```php
// Use iterative DFS for deep graphs
$dfs = new IterativeDFS();
$result = $dfs->traverse($graph, $start);
```

### Problem: Infinite Loop in DFS

**Symptom**: Algorithm runs forever, never terminates

**Cause**: Missing visited check or not resetting visited set between calls

**Solution**: Always check visited before recursing:

```php
// Correct
foreach ($graph[$vertex] ?? [] as $neighbor) {
    if (!isset($visited[$neighbor])) {  // Check visited
        $this->dfs($graph, $neighbor, $visited);
    }
}

// Wrong - causes infinite loop
foreach ($graph[$vertex] ?? [] as $neighbor) {
    $this->dfs($graph, $neighbor, $visited);  // No visited check!
}
```

### Problem: DFS Doesn't Find Shortest Path

**Symptom**: Path found by DFS is longer than expected

**Cause**: DFS doesn't guarantee shortest paths - it finds any path

**Solution**: 
- Use BFS for shortest paths in unweighted graphs
- Use Dijkstra's algorithm for weighted graphs
- DFS is correct for problems that don't require shortest path (e.g., cycle detection, topological sort)

### Problem: Stack Overflow in Recursive DFS

**Symptom**: `Fatal error: Allowed memory size exhausted` or segmentation fault

**Cause**: Very deep graph causes recursion stack to exceed available memory

**Solution**:
- Switch to iterative DFS implementation
- For graphs with depth > 10,000, always use iterative DFS
- Monitor graph depth before choosing recursive vs iterative

```php
// Check graph depth first
$maxDepth = $this->estimateMaxDepth($graph, $start);
if ($maxDepth > 10000) {
    // Use iterative DFS
    $dfs = new IterativeDFS();
} else {
    // Recursive DFS is fine
    $dfs = new DFS();
}
```

### Problem: Visited Set Not Reset Between Calls

**Symptom**: Second DFS call doesn't visit vertices that should be visited

**Cause**: Visited array persists between multiple DFS calls

**Solution**: Always reset visited set at start of each traversal:

```php
public function traverse(array $graph, int $start): array
{
    $this->visited = [];  // Reset visited
    $this->result = [];
    $this->dfsRecursive($graph, $start);
    return $this->result;
}
```

### Problem: Wrong Order in Iterative DFS

**Symptom**: Iterative DFS produces different order than recursive DFS

**Cause**: Stack order differs from recursion order

**Solution**: Reverse neighbors when pushing to stack to match recursive order:

```php
// To match recursive DFS order
$neighbors = array_reverse($graph[$vertex] ?? []);
foreach ($neighbors as $neighbor) {
    $stack[] = $neighbor;
}
```

### Problem: Cycle Detection Returns False Positives

**Symptom**: Algorithm reports cycles in acyclic graphs

**Cause**: Not distinguishing between directed and undirected graphs

**Solution**: Use correct cycle detection method:

```php
// For directed graphs
$detector = new CycleDetector();
$hasCycle = $detector->hasCycleDirected($graph);

// For undirected graphs
$hasCycle = $detector->hasCycleUndirected($graph);

// Don't mix them!
```

## Wrap-up

Congratulations! You've mastered Depth-First Search, one of the most fundamental graph traversal algorithms. Here's what you've accomplished:

- ✓ Implemented both recursive and iterative DFS versions
- ✓ Built visualization tools to understand DFS execution and call stack behavior
- ✓ Created a path finder that discovers routes between vertices
- ✓ Developed cycle detection for both directed and undirected graphs
- ✓ Implemented topological sorting for dependency resolution
- ✓ Built a connected components finder for analyzing graph structure
- ✓ Learned DFS with timestamps for edge classification
- ✓ Implemented bridge detection to find critical edges in networks
- ✓ Built articulation points detector to identify critical nodes
- ✓ Implemented Kosaraju's algorithm for strongly connected components
- ✓ Analyzed performance characteristics across different graph types
- ✓ Compared DFS vs BFS to understand when to use each algorithm
- ✓ Understood limitations and when DFS is not the optimal choice
- ✓ Built practical applications: maze solver, directory walker, and dependency resolver
- ✓ Understood complexity analysis: O(V + E) time, O(V) space

You now have a deep understanding of how DFS explores graphs by going deep before backtracking, making it perfect for problems requiring exhaustive exploration, backtracking, or topological ordering. This foundation will serve you well as you tackle more advanced graph algorithms.

## Further Reading

- [Depth-First Search on GeeksforGeeks](https://www.geeksforgeeks.org/depth-first-search-or-dfs-for-a-graph/) — Comprehensive DFS tutorial with examples
- [Topological Sorting Algorithm](https://www.geeksforgeeks.org/topological-sorting/) — Detailed explanation of topological sort using DFS
- [Bridge Detection Algorithm](https://www.geeksforgeeks.org/bridge-in-a-graph/) — Finding critical edges using DFS
- [Articulation Points Algorithm](https://www.geeksforgeeks.org/articulation-points-or-cut-vertices-in-a-graph/) — Finding critical vertices using DFS
- [Strongly Connected Components](https://www.geeksforgeeks.org/strongly-connected-components/) — Kosaraju's algorithm explained
- [Graph Traversal Visualization](https://visualgo.net/en/dfsbfs) — Interactive visualization of DFS and BFS
- [Chapter 21: Graph Representations](/series/php-algorithms/chapters/21-graph-representations) — Review graph data structures
- [Chapter 23: Breadth-First Search](/series/php-algorithms/chapters/23-breadth-first-search) — Next chapter covering BFS traversal
- [Introduction to Algorithms (CLRS)](https://mitpress.mit.edu/9780262046305/introduction-to-algorithms/) — Chapter 22 covers depth-first search in depth


## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 22 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-22)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-22
php 01-*.php
```

## Next Steps

In the next chapter, we'll explore Breadth-First Search (BFS), which explores level by level and is optimal for finding shortest paths in unweighted graphs.
