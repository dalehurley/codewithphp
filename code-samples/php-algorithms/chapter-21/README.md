# Chapter 21: Graph Representations - Code Samples

This directory contains comprehensive, runnable PHP code samples demonstrating different graph representation techniques and their practical applications.

## Files Overview

### 1. `adjacency-matrix.php`
**Purpose**: Demonstrates adjacency matrix representation for both unweighted and weighted graphs.

**Key Concepts**:
- Unweighted adjacency matrix (0/1 values)
- Weighted adjacency matrix (actual weights with infinity for missing edges)
- O(1) edge lookup and modification
- O(V²) space complexity

**When to Use**:
- Dense graphs with many edges
- Frequent edge existence checks
- Need constant-time edge operations

**Run**:
```bash
php adjacency-matrix.php
```

### 2. `adjacency-list.php`
**Purpose**: Demonstrates adjacency list representation using arrays for neighbor storage.

**Key Concepts**:
- Unweighted adjacency list (array of arrays)
- Weighted adjacency list (array of edge objects)
- O(V + E) space complexity
- Efficient neighbor iteration

**When to Use**:
- Sparse graphs with few edges
- Need to iterate through neighbors frequently
- Memory-constrained environments

**Run**:
```bash
php adjacency-list.php
```

### 3. `hashmap-graph.php`
**Purpose**: Hash map-based graph with string vertex identifiers for real-world applications.

**Key Concepts**:
- Named vertices (strings instead of integers)
- Dynamic vertex addition/removal
- JSON export/import functionality
- Perfect for real-world modeling

**When to Use**:
- Modeling real entities (users, cities, web pages)
- Need meaningful vertex names
- Dynamic graph structure

**Run**:
```bash
php hashmap-graph.php
```

### 4. `social-network-analyzer.php`
**Purpose**: Complete production-ready social network analysis application.

**Key Features**:
- Friend recommendations (friends of friends)
- Mutual friends calculation
- Network centrality metrics
- Path finding between users
- Degrees of separation
- Network statistics and density
- Influence scoring

**Demonstrates**:
- Practical graph applications
- BFS for shortest paths
- Real-world social network analysis
- Error handling and validation

**Run**:
```bash
php social-network-analyzer.php
```

## Complexity Comparison

| Operation | Adjacency Matrix | Adjacency List | Hash Map Graph |
|-----------|-----------------|----------------|----------------|
| Space | O(V²) | O(V + E) | O(V + E) |
| Add Edge | O(1) | O(1) | O(1) |
| Remove Edge | O(1) | O(V) | O(1) |
| Has Edge | O(1) | O(V) | O(1) |
| Get Neighbors | O(V) | O(1) | O(1) |
| Add Vertex | N/A (fixed size) | O(1) | O(1) |

## Graph Types Demonstrated

### Undirected Graphs
- Social networks (friendships are bidirectional)
- Road networks (roads go both ways)
- Edges have no direction

### Directed Graphs
- Web page links (one-way connections)
- Task dependencies (A must come before B)
- Twitter follows (following is not mutual)

### Weighted Graphs
- Road networks (distances between cities)
- Flight networks (ticket prices)
- Edges have associated costs/weights

## Practical Applications Shown

1. **Social Networks** (`social-network-analyzer.php`)
   - Friend recommendations
   - Mutual connections
   - Network influence
   - Degrees of separation

2. **Road Networks** (`hashmap-graph.php`)
   - City connections with distances
   - Route information

3. **Web Graphs** (`hashmap-graph.php`)
   - Page link structure
   - Incoming/outgoing links

4. **Package Dependencies** (`hashmap-graph.php`)
   - Software package dependencies
   - Build order determination

## Key Takeaways

1. **Choose representation based on graph density**:
   - Sparse (few edges): Use adjacency list
   - Dense (many edges): Use adjacency matrix

2. **Consider your operations**:
   - Frequent edge lookups: Matrix
   - Frequent neighbor iteration: List
   - Named vertices: Hash map

3. **Space vs Time tradeoffs**:
   - Matrix: More space, faster edge lookup
   - List: Less space, slower edge lookup (for sparse graphs)

4. **Real-world modeling**:
   - Use hash map graphs for meaningful names
   - Add domain-specific methods
   - Include validation and error handling

## Running All Examples

To run all examples in sequence:

```bash
php adjacency-matrix.php && \
php adjacency-list.php && \
php hashmap-graph.php && \
php social-network-analyzer.php
```

## Requirements

- PHP 8.0 or higher
- No external dependencies required
- All code is self-contained and runnable

## Learning Path

1. Start with `adjacency-matrix.php` to understand basic concepts
2. Move to `adjacency-list.php` to see space-efficient representation
3. Explore `hashmap-graph.php` for real-world modeling
4. Study `social-network-analyzer.php` for complete application example

## Next Steps

After mastering graph representations, proceed to:
- **Chapter 22**: Depth-First Search (DFS)
- **Chapter 23**: Breadth-First Search (BFS)
- **Chapter 24**: Dijkstra's Shortest Path Algorithm
- **Chapter 25**: Dynamic Programming Fundamentals

These algorithms will use the graph representations you've learned here!
