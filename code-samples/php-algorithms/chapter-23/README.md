# Chapter 23: Breadth-First Search (BFS) - Code Samples

Comprehensive PHP code samples for BFS algorithms and applications.

## Files Overview

### 1. `bfs-traversal.php`
**Purpose**: Basic BFS implementations and shortest path finding.

**Key Features**:
- Basic BFS traversal
- Level-by-level traversal
- Shortest path finding
- Distance calculation

**Run**: `php bfs-traversal.php`

### 2. `grid-bfs.php`
**Purpose**: BFS on 2D grids for pathfinding and island counting.

**Applications**:
- Maze solving
- Game pathfinding
- Island counting
- Flood fill

**Run**: `php grid-bfs.php`

### 3. `bipartite-graph.php`
**Purpose**: Detect if graph is bipartite (2-colorable).

**Applications**:
- Matching problems
- Scheduling
- Conflict detection

**Run**: `php bipartite-graph.php`

## Key Concepts

**BFS Properties**:
- Explores level by level (breadth-first)
- Uses queue (FIFO)
- Guarantees shortest path in unweighted graphs
- Time: O(V + E), Space: O(V)

**When to Use BFS**:
- Finding shortest path
- Level-order traversal
- Finding nodes within K distance
- Testing bipartiteness

## BFS vs DFS

| Feature | BFS | DFS |
|---------|-----|-----|
| Data Structure | Queue (FIFO) | Stack (LIFO) |
| Exploration | Level by level | Depth first |
| Shortest Path | Yes (unweighted) | No |
| Memory | O(width) | O(height) |
| Applications | Shortest path, levels | Cycles, topological sort |

## Requirements

- PHP 8.0+
- No dependencies

## Next Chapter

**Chapter 24**: Dijkstra's Shortest Path Algorithm
