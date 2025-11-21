# Chapter 24: Dijkstra's Shortest Path Algorithm - Code Samples

Comprehensive PHP code samples for Dijkstra's algorithm and weighted graph applications.

## Files Overview

### 1. `dijkstra-basic.php`
**Purpose**: Core Dijkstra's algorithm implementation.

**Key Features**:
- Priority queue-based implementation
- Single-source shortest paths
- Path reconstruction
- Efficient O((V + E) log V) time

**Run**: `php dijkstra-basic.php`

### 2. `weighted-graph-applications.php`
**Purpose**: Real-world application - GPS navigation system.

**Features**:
- Route finding between cities
- Distance calculation
- Turn-by-turn directions
- Named vertex support

**Run**: `php weighted-graph-applications.php`

## Key Concepts

**Algorithm Properties**:
- Greedy approach: always expand nearest unvisited vertex
- Requires non-negative edge weights
- Uses priority queue (min-heap) for efficiency
- Computes shortest path tree from source

**Time Complexity**:
- With binary heap: O((V + E) log V)
- With Fibonacci heap: O(E + V log V) (theoretical)
- Array-based: O(V²)

**Space Complexity**: O(V)

## When to Use Dijkstra

- Weighted graphs with non-negative weights
- Single-source shortest paths
- Network routing
- GPS navigation
- Game pathfinding

## Limitations

**Cannot handle**:
- Negative edge weights (use Bellman-Ford instead)
- Negative cycles

**Alternatives**:
- Unweighted graphs: Use BFS (simpler)
- Negative weights: Use Bellman-Ford
- All-pairs: Use Floyd-Warshall

## Applications

1. **GPS Navigation**: Find shortest route between locations
2. **Network Routing**: Determine optimal packet paths
3. **Flight Planning**: Find cheapest flights
4. **Game AI**: Pathfinding for NPCs
5. **Delivery Optimization**: Route planning for deliveries

## Requirements

- PHP 8.0+
- SplMinHeap (built-in)

## Next Chapter

**Chapter 25**: Dynamic Programming Fundamentals
