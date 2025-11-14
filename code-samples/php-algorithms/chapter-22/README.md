# Chapter 22: Depth-First Search (DFS) - Code Samples

Comprehensive, runnable PHP code samples demonstrating Depth-First Search algorithms and applications.

## Files Overview

### 1. `dfs-traversal.php`
**Purpose**: Basic DFS traversal implementations (recursive and iterative).

**Key Concepts**:
- Recursive DFS (using call stack)
- Iterative DFS (using explicit stack)
- Handling disconnected graphs
- Depth tracking

**Run**: `php dfs-traversal.php`

### 2. `cycle-detection.php`
**Purpose**: Detect cycles in directed and undirected graphs.

**Key Concepts**:
- Cycle detection in directed graphs (using recursion stack)
- Cycle detection in undirected graphs (using parent tracking)
- Finding and reconstructing cycles
- Back edge detection

**Applications**: Dependency validation, deadlock detection

**Run**: `php cycle-detection.php`

### 3. `topological-sort.php`
**Purpose**: Topological ordering of DAG vertices.

**Key Concepts**:
- DFS-based topological sort
- Cycle detection before sorting
- Task scheduling applications
- Build order determination

**Applications**: Task scheduling, course prerequisites, build systems

**Run**: `php topological-sort.php`

### 4. `path-finding.php`
**Purpose**: Find paths between vertices using DFS.

**Key Concepts**:
- Single path finding
- All paths finding (with backtracking)
- Path existence checking
- Longest path in acyclic graphs

**Applications**: Maze solving, route finding, connectivity

**Run**: `php path-finding.php`

## Key Algorithms Demonstrated

1. **DFS Traversal**: O(V + E) time, visits all vertices depth-first
2. **Cycle Detection**: O(V + E) time, identifies cycles in graphs
3. **Topological Sort**: O(V + E) time, orders DAG vertices
4. **Path Finding**: O(V + E) time, finds paths between vertices

## Complexity Summary

| Operation | Time | Space |
|-----------|------|-------|
| DFS Traversal | O(V + E) | O(V) |
| Cycle Detection | O(V + E) | O(V) |
| Topological Sort | O(V + E) | O(V) |
| Path Finding | O(V + E) | O(V) |
| All Paths | O(V!) worst | O(V) |

## When to Use DFS

- **Exploring all paths**: DFS is exhaustive
- **Detecting cycles**: Natural with recursion stack
- **Topological sorting**: Must process children first
- **Connected components**: Find all connected vertices
- **Maze solving**: Explore deeply before backtracking

## DFS vs BFS

**DFS**:
- Uses stack (LIFO) - goes deep first
- Better for: cycle detection, topological sort, exhaustive search
- Memory: O(height of tree)

**BFS**:
- Uses queue (FIFO) - goes wide first
- Better for: shortest path, level-order traversal
- Memory: O(width of tree)

## Running All Examples

```bash
for file in *.php; do php "$file"; echo ""; done
```

## Requirements

- PHP 8.0+
- No external dependencies

## Next Chapter

**Chapter 23**: Breadth-First Search (BFS) - Level-by-level traversal and shortest paths
