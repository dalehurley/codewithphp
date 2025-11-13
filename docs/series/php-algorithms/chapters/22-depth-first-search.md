---
title: "Depth-First Search (DFS)"
description: "Master Depth-First Search for graph traversal, exploring applications like cycle detection, topological sorting, connected components, and path finding"
series: "php-algorithms"
chapter: 22
order: 22
difficulty: "intermediate"
prerequisites: ["Graph Representations", "Recursion Fundamentals", "Stacks & Queues"]
---

# Depth-First Search (DFS)

Depth-First Search is a fundamental graph traversal algorithm that explores as far as possible along each branch before backtracking. It's the foundation for many graph algorithms.

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

## Recursive DFS Implementation

```php
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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

## Strongly Connected Components (Kosaraju's Algorithm)

Finding strongly connected components in directed graphs.

```php
<?php

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

## Practical Applications

### 1. Maze Solver

```php
<?php

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
<?php

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
<?php

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

## Key Takeaways

- DFS explores as far as possible along each branch before backtracking
- Can be implemented recursively (using call stack) or iteratively (using explicit stack)
- Time complexity: O(V + E), Space complexity: O(V)
- Essential for many graph problems: cycles, paths, components, topological sort
- Maintains visited set to track explored vertices
- Works on both directed and undirected graphs
- Foundation for advanced algorithms like strongly connected components
- Natural choice for problems requiring exhaustive exploration or backtracking

## Next Steps

In the next chapter, we'll explore Breadth-First Search (BFS), which explores level by level and is optimal for finding shortest paths in unweighted graphs.
