# Appendix A: Complexity Cheat Sheet

A comprehensive reference for algorithm complexity analysis and common algorithm time/space complexities.

## Big O Notation Hierarchy

From fastest to slowest:

| Notation | Name | Example | n=10 | n=100 | n=1000 |
|----------|------|---------|------|-------|--------|
| O(1) | Constant | Array access, hash lookup | 1 | 1 | 1 |
| O(log n) | Logarithmic | Binary search | 3 | 7 | 10 |
| O(n) | Linear | Linear search, array iteration | 10 | 100 | 1,000 |
| O(n log n) | Linearithmic | Merge sort, quick sort (avg) | 30 | 664 | 9,966 |
| O(n²) | Quadratic | Bubble sort, nested loops | 100 | 10,000 | 1,000,000 |
| O(n³) | Cubic | Matrix multiplication | 1,000 | 1,000,000 | 1,000,000,000 |
| O(2ⁿ) | Exponential | Recursive Fibonacci | 1,024 | 1.27×10³⁰ | ∞ |
| O(n!) | Factorial | Traveling salesman (brute force) | 3,628,800 | ∞ | ∞ |

### Practical Limits (1 second execution)

| Complexity | Maximum n |
|------------|-----------|
| O(1) | ∞ |
| O(log n) | ∞ |
| O(n) | ~100,000,000 |
| O(n log n) | ~10,000,000 |
| O(n²) | ~10,000 |
| O(n³) | ~500 |
| O(2ⁿ) | ~25 |
| O(n!) | ~11 |

## Common Data Structure Operations

### Array (PHP array with numeric keys)

| Operation | Average | Worst | Notes |
|-----------|---------|-------|-------|
| Access by index | O(1) | O(1) | Direct access |
| Search (unsorted) | O(n) | O(n) | Linear scan |
| Search (sorted) | O(log n) | O(log n) | Binary search |
| Insert at end | O(1) | O(1) | `$arr[] = $value` |
| Insert at beginning | O(n) | O(n) | `array_unshift()` |
| Insert at middle | O(n) | O(n) | Shift elements |
| Delete at end | O(1) | O(1) | `array_pop()` |
| Delete at beginning | O(n) | O(n) | `array_shift()` |
| Delete at middle | O(n) | O(n) | Shift elements |

### Hash Table (PHP associative array)

| Operation | Average | Worst | Notes |
|-----------|---------|-------|-------|
| Access | O(1) | O(n) | Key lookup |
| Search | O(1) | O(n) | `isset()`, `array_key_exists()` |
| Insert | O(1) | O(n) | `$arr[$key] = $value` |
| Delete | O(1) | O(n) | `unset($arr[$key])` |
| Iteration | O(n) | O(n) | `foreach` |

### Stack (using SplStack or array)

| Operation | Time | Notes |
|-----------|------|-------|
| Push | O(1) | `$stack->push()` or `array_push()` |
| Pop | O(1) | `$stack->pop()` or `array_pop()` |
| Peek | O(1) | `$stack->top()` or `end()` |
| Search | O(n) | Linear scan |

### Queue (using SplQueue or array)

| Operation | Time | Notes |
|-----------|------|-------|
| Enqueue | O(1) | `$queue->enqueue()` |
| Dequeue | O(1) | `$queue->dequeue()` |
| Peek | O(1) | Front element |
| Search | O(n) | Linear scan |

### Binary Search Tree (Balanced)

| Operation | Average | Worst (unbalanced) |
|-----------|---------|-------------------|
| Search | O(log n) | O(n) |
| Insert | O(log n) | O(n) |
| Delete | O(log n) | O(n) |
| Min/Max | O(log n) | O(n) |

### Heap (SplPriorityQueue, SplMinHeap, SplMaxHeap)

| Operation | Time |
|-----------|------|
| Insert | O(log n) |
| Extract min/max | O(log n) |
| Peek min/max | O(1) |
| Build heap | O(n) |
| Heapify | O(log n) |

### Graph (Adjacency List)

| Operation | Time | Space |
|-----------|------|-------|
| Add vertex | O(1) | O(1) |
| Add edge | O(1) | O(1) |
| Remove vertex | O(V + E) | - |
| Remove edge | O(E) | - |
| Query edge | O(V) | - |

## Sorting Algorithms

| Algorithm | Best | Average | Worst | Space | Stable | Notes |
|-----------|------|---------|-------|-------|--------|-------|
| Bubble Sort | O(n) | O(n²) | O(n²) | O(1) | Yes | Simple, slow |
| Selection Sort | O(n²) | O(n²) | O(n²) | O(1) | No | Always O(n²) |
| Insertion Sort | O(n) | O(n²) | O(n²) | O(1) | Yes | Good for small/nearly sorted |
| Merge Sort | O(n log n) | O(n log n) | O(n log n) | O(n) | Yes | Predictable, needs space |
| Quick Sort | O(n log n) | O(n log n) | O(n²) | O(log n) | No | Fast average, in-place |
| Heap Sort | O(n log n) | O(n log n) | O(n log n) | O(1) | No | In-place, not stable |
| Counting Sort | O(n + k) | O(n + k) | O(n + k) | O(k) | Yes | Integer keys only |
| Radix Sort | O(nk) | O(nk) | O(nk) | O(n + k) | Yes | Integer keys |
| Bucket Sort | O(n + k) | O(n + k) | O(n²) | O(n) | Yes | Uniform distribution |

**PHP's `sort()` and `usort()`**: O(n log n) average (uses quicksort variant)

## Searching Algorithms

| Algorithm | Time | Space | Notes |
|-----------|------|-------|-------|
| Linear Search | O(n) | O(1) | Unsorted data |
| Binary Search | O(log n) | O(1) | Sorted data required |
| Jump Search | O(√n) | O(1) | Sorted data |
| Interpolation Search | O(log log n) avg | O(1) | Uniformly distributed |
| Hash Table Lookup | O(1) avg | O(n) | PHP arrays |
| Binary Search Tree | O(log n) avg | O(n) | Balanced tree |
| Depth-First Search (DFS) | O(V + E) | O(V) | Graph traversal |
| Breadth-First Search (BFS) | O(V + E) | O(V) | Graph traversal |

## Graph Algorithms

| Algorithm | Time Complexity | Space | Use Case |
|-----------|----------------|-------|----------|
| DFS | O(V + E) | O(V) | Traversal, cycles |
| BFS | O(V + E) | O(V) | Shortest path (unweighted) |
| Dijkstra | O((V + E) log V) | O(V) | Shortest path (weighted) |
| Bellman-Ford | O(VE) | O(V) | Negative weights allowed |
| Floyd-Warshall | O(V³) | O(V²) | All-pairs shortest paths |
| Prim's | O((V + E) log V) | O(V) | Minimum spanning tree |
| Kruskal's | O(E log E) | O(V) | Minimum spanning tree |
| Topological Sort | O(V + E) | O(V) | DAG ordering |

## String Algorithms

| Algorithm | Time | Space | Use Case |
|-----------|------|-------|----------|
| Naive String Match | O(nm) | O(1) | Simple pattern search |
| KMP | O(n + m) | O(m) | Pattern matching |
| Boyer-Moore | O(n/m) best | O(m) | Fast pattern matching |
| Rabin-Karp | O(n + m) avg | O(1) | Multiple patterns |
| Aho-Corasick | O(n + m + z) | O(m) | Multi-pattern search |
| Suffix Array | O(n log n) | O(n) | Substring queries |
| Longest Common Subsequence | O(nm) | O(nm) | Sequence comparison |
| Edit Distance | O(nm) | O(nm) | String similarity |
| Manacher's Algorithm | O(n) | O(n) | Palindrome finding |

## Dynamic Programming Problems

| Problem | Time | Space |
|---------|------|-------|
| Fibonacci (naive) | O(2ⁿ) | O(n) |
| Fibonacci (DP) | O(n) | O(n) |
| Fibonacci (optimized) | O(n) | O(1) |
| 0/1 Knapsack | O(nW) | O(nW) |
| Longest Common Subsequence | O(nm) | O(nm) |
| Longest Increasing Subsequence | O(n²) or O(n log n) | O(n) |
| Matrix Chain Multiplication | O(n³) | O(n²) |
| Edit Distance | O(nm) | O(nm) |
| Coin Change | O(nA) | O(n) |

## Common PHP Function Complexities

| Function | Time | Notes |
|----------|------|-------|
| `in_array()` | O(n) | Linear search |
| `array_search()` | O(n) | Linear search |
| `isset()` | O(1) | Hash lookup |
| `array_key_exists()` | O(1) | Hash lookup |
| `count()` | O(1) | Stored value |
| `array_push()` | O(1) | Append |
| `array_pop()` | O(1) | Remove last |
| `array_shift()` | O(n) | Remove first, reindex |
| `array_unshift()` | O(n) | Prepend, reindex |
| `array_merge()` | O(n + m) | Combine arrays |
| `sort()` | O(n log n) | Quicksort |
| `usort()` | O(n log n) | Custom sort |
| `array_unique()` | O(n) | Hash-based |
| `array_filter()` | O(n) | Iterate + callback |
| `array_map()` | O(n) | Iterate + callback |
| `array_reduce()` | O(n) | Iterate + callback |
| `array_reverse()` | O(n) | Reverse |
| `array_slice()` | O(k) | Extract k elements |
| `str_replace()` | O(n) | String scan |
| `preg_match()` | O(n) avg | Regex matching |
| `explode()` | O(n) | Split string |
| `implode()` | O(n) | Join strings |

## Space Complexity Guide

### Common Patterns

| Pattern | Space | Example |
|---------|-------|---------|
| In-place algorithm | O(1) | Bubble sort, two pointers |
| Single array/variable | O(n) | Hash table, visited set |
| Two arrays | O(n) | Merge sort temp array |
| Matrix | O(n²) | Floyd-Warshall, DP table |
| Recursion depth | O(n) | DFS call stack |
| Complete binary tree | O(2ⁿ) | All subsets generation |

### PHP-Specific Memory Considerations

```php
// Array overhead
$arr = [];  // ~56 bytes base
$arr[] = 1; // ~72 bytes per element (with overhead)

// String memory
$str = '';  // ~24 bytes base
$str = 'hello';  // 24 + 5 bytes

// Object overhead
class Foo {}
$obj = new Foo();  // ~40 bytes minimum
```

## Algorithm Selection Guide

### Sorting

**Small dataset (n < 100)**:
- Insertion Sort O(n²) - Simple, efficient for small data

**Medium dataset (100 < n < 1M)**:
- Quick Sort O(n log n) - Fast average case
- PHP's `sort()` - Optimized built-in

**Large dataset (n > 1M)**:
- Merge Sort O(n log n) - Predictable performance
- Timsort (Python, Java) - Hybrid approach

**Special cases**:
- Nearly sorted: Insertion Sort O(n)
- Integer keys, small range: Counting Sort O(n + k)
- Need stability: Merge Sort
- Memory constrained: Heap Sort

### Searching

**Unsorted data**:
- Linear Search O(n) - Only option

**Sorted data**:
- Binary Search O(log n) - Optimal

**Frequent lookups**:
- Hash Table O(1) - Best average case
- PHP associative array

**Range queries**:
- Binary Search Tree O(log n)
- Segment Tree O(log n)

### Graph Traversal

**Shortest path (unweighted)**:
- BFS O(V + E)

**Shortest path (weighted, non-negative)**:
- Dijkstra O((V + E) log V)

**Shortest path (negative weights)**:
- Bellman-Ford O(VE)

**All-pairs shortest path**:
- Floyd-Warshall O(V³)

**Minimum spanning tree**:
- Prim's O((V + E) log V) - Dense graphs
- Kruskal's O(E log E) - Sparse graphs

## Optimization Techniques

### Time Optimization

1. **Use appropriate data structure**
   - Hash table for O(1) lookup
   - Heap for O(log n) min/max

2. **Avoid nested loops**
   - O(n²) → O(n) using hash table

3. **Use binary search**
   - O(n) → O(log n) for sorted data

4. **Memoization**
   - O(2ⁿ) → O(n) for recursive problems

5. **Early termination**
   - Break loops when answer found

### Space Optimization

1. **In-place algorithms**
   - Modify input instead of creating new array

2. **Two pointers**
   - O(n) space → O(1) space

3. **Iterative vs recursive**
   - O(n) stack space → O(1) space

4. **Sliding window**
   - O(n) → O(k) where k is window size

5. **Rolling hash**
   - O(m) → O(1) for pattern matching

## Quick Reference: When to Use What

### Need O(1) lookup?
→ Hash table (PHP array)

### Need sorted data?
→ Binary Search Tree or sorted array

### Need min/max quickly?
→ Heap (SplPriorityQueue)

### Need FIFO?
→ Queue (SplQueue)

### Need LIFO?
→ Stack (SplStack)

### Need to find duplicates?
→ Hash table O(n)

### Need to reverse?
→ Two pointers O(n), O(1) space

### Need substring search?
→ KMP O(n + m) or PHP `strpos()` O(n)

### Need shortest path?
→ BFS (unweighted) or Dijkstra (weighted)

### Need to sort?
→ PHP `sort()` O(n log n)

## Common Mistakes to Avoid

1. ❌ Using `in_array()` in loop → O(n²)
   ✅ Use hash table → O(n)

2. ❌ Multiple `array_shift()` → O(n²)
   ✅ Use SplQueue → O(n)

3. ❌ String concatenation in loop → O(n²)
   ✅ Use array + `implode()` → O(n)

4. ❌ Nested `foreach` without thinking → O(n²)
   ✅ Consider hash table approach → O(n)

5. ❌ Sorting already sorted data repeatedly
   ✅ Check if sorted first or maintain sorted order

## Performance Testing Template

```php
function measureTime(callable $fn, ...$args): float {
    $start = microtime(true);
    $fn(...$args);
    return microtime(true) - $start;
}

function measureMemory(callable $fn, ...$args): int {
    $before = memory_get_usage();
    $fn(...$args);
    return memory_get_usage() - $before;
}

// Usage
$time = measureTime(fn() => myAlgorithm($data));
$memory = measureMemory(fn() => myAlgorithm($data));

echo "Time: " . number_format($time * 1000, 2) . " ms\n";
echo "Memory: " . number_format($memory / 1024, 2) . " KB\n";
```

## Resources

- [Big-O Cheat Sheet](https://www.bigocheatsheet.com/)
- [PHP Manual: Time Complexity](https://www.php.net/manual/en/intro.ds.php)
- Chapter 02: Time Complexity Analysis
- Chapter 29: Performance Optimization
