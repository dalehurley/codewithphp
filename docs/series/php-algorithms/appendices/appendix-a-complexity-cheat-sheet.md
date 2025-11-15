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

### Visual Complexity Growth Chart

```
Operations performed (y-axis) vs Input Size (x-axis)

n=10          n=100         n=1000        n=10000
│             │             │             │
│             │             │             │         O(n!)
│             │             │             │xxxxxxxxx
│             │             │ xxxxxxxxxxxx│
│             │ xxxxxxxxxxxx│             │
│ xxxxxxxxxxxx│             │             │         O(2^n)
│             │             │             │xxxxxxx
│             │             │ xxxxxxxxxxx│
│             │ xxxxxxxxxxx│             │
│ xxxxxxxxxxx│             │             │         O(n³)
│             │             │             │xxxxx
│             │             │ xxxxxxxxx  │
│             │ xxxxxxxxx  │             │
│ xxxxxxxxx  │             │             │         O(n²)
│             │             │      xxxx  │
│             │      xxx   │             │
│      xx    │             │             │         O(n log n)
│             │       xx   │        xx   │
│        x   │             │             │         O(n)
│         x  │          x  │           x │
│          x │             │             x         O(log n)
│xxxxxxxxxx ─┼─────────x ─┼────────────x┼─        O(1)
└────────────┴─────────────┴─────────────┴─────────

Legend: Lower = Better Performance
```

### Complexity Comparison for n=100

```
Algorithm     Operations  Visualization
O(1)          1          █
O(log n)      7          ███
O(n)          100        ██████████
O(n log n)    664        ████████████████████████████████████
O(n²)         10,000     ████████████████████████████████████████████████████... (entire screen!)
O(2^n)        1.27×10³⁰  (larger than atoms in universe)
```

**Key Insight**: Even small differences in complexity class have MASSIVE impacts as input grows!

### Practical PHP Examples by Complexity

**O(1) - Constant Time**:
```php
<?php
# filename: constant-time-example.php

declare(strict_types=1);

// Direct array access
$value = $array[$key];  // Always same time

// Hash table operations
isset($cache[$key]);    // Instant lookup
unset($map[$key]);      // Instant delete
```
*See: Chapter 4 (Arrays), Chapter 6 (Hash Tables)*

**O(log n) - Logarithmic**:
```php
<?php
# filename: logarithmic-example.php

declare(strict_types=1);

// Binary search on sorted array
$index = binarySearch($sortedArray, $target);

// Operations on balanced BST
$tree->search($value);  // Height = log n
```
*See: Chapter 8 (Binary Search), Chapter 16 (Binary Search Trees)*

**O(n) - Linear**:
```php
<?php
# filename: linear-example.php

declare(strict_types=1);

// Single loop through array
foreach ($array as $item) {
    process($item);
}

// Linear search
$found = in_array($needle, $haystack);
```
*See: Chapter 4 (Array Traversal), Chapter 7 (Linear Search)*

**O(n log n) - Linearithmic**:
```php
<?php
# filename: linearithmic-example.php

declare(strict_types=1);

// Efficient sorting
sort($array);           // PHP's built-in
$merged = mergeSort($array);  // Merge sort
```
*See: Chapter 10-11 (Sorting Algorithms)*

**O(n²) - Quadratic**:
```php
<?php
# filename: quadratic-example.php

declare(strict_types=1);

// Nested loops
$count = count($arr);
for ($i = 0; $i < $count; $i++) {
    for ($j = $i + 1; $j < $count; $j++) {
        comparePair($arr[$i], $arr[$j]);
    }
}
```
*See: Chapter 9 (Bubble Sort), Chapter 11 (Selection Sort)*

**O(2^n) - Exponential**:
```php
<?php
# filename: exponential-example.php

declare(strict_types=1);

// Naive recursive Fibonacci
function fib(int $n): int {
    if ($n <= 1) return $n;
    return fib($n-1) + fib($n-2);  // Two recursive calls
}
```
*See: Chapter 25 (Dynamic Programming - shows optimization)*

**O(n!) - Factorial**:
```php
<?php
# filename: factorial-example.php

declare(strict_types=1);

// Generate all permutations
function permutations(array $items): array {
    if (count($items) <= 1) return [$items];
    
    $result = [];
    foreach ($items as $i => $item) {
        $rest = $items;
        unset($rest[$i]);
        foreach (permutations(array_values($rest)) as $perm) {
            $result[] = array_merge([$item], $perm);
        }
    }
    return $result;
}
```
*See: Chapter 25 (Dynamic Programming - shows optimization)*

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

### AVL Tree

| Operation | Time | Space | Notes |
|-----------|------|-------|-------|
| Search | O(log n) | O(1) | Height bounded by 1.44 × log(n) |
| Insert | O(log n) | O(1) | May require rotations |
| Delete | O(log n) | O(1) | May require rotations |
| Min/Max | O(log n) | O(1) | Height field per node |
| Space | O(n) | O(n) | Strict balance maintained |

### Red-Black Tree

| Operation | Time | Space | Notes |
|-----------|------|-------|-------|
| Search | O(log n) | O(1) | Height bounded by 2 × log(n) |
| Insert | O(log n) | O(1) | Max 2 rotations |
| Delete | O(log n) | O(1) | Max 3 rotations |
| Min/Max | O(log n) | O(1) | Color bit per node |
| Space | O(n) | O(n) | Looser balance than AVL |

**Comparison**:
- **AVL**: Stricter balance, faster searches, more rotations on insert/delete
- **Red-Black**: Looser balance, faster insertions/deletions, slightly slower searches
- Both guarantee O(log n) worst-case performance

*See: Chapter 20 (Balanced Trees: AVL & Red-Black Trees)*

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
| Rabin-Karp | O(n + m) avg, O(nm) worst | O(1) | Multiple patterns |
| Aho-Corasick | O(n + m + z) | O(m × σ) | Multi-pattern search |
| Z-Algorithm | O(n + m) | O(n + m) | Fast pattern matching |
| Suffix Array | O(n log n) build, O(m log n + occ) search | O(n) | Substring queries |
| Suffix Tree | O(n) build (Ukkonen's), O(m) search | O(n) | Linear-time pattern matching |
| Longest Common Subsequence | O(nm) | O(nm) | Sequence comparison |
| Edit Distance | O(nm) | O(nm) | String similarity |
| Manacher's Algorithm | O(n) | O(n) | Palindrome finding |

**Notes**:
- **Aho-Corasick**: n = text length, m = total pattern length, z = number of matches, σ = alphabet size
- **Suffix Array**: occ = number of occurrences, specialized algorithms can build in O(n)
- **Suffix Tree**: Uses Ukkonen's algorithm for linear construction

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

## Stream Processing Algorithms

Stream processing algorithms handle continuous, potentially infinite data streams with constant or logarithmic space complexity.

| Algorithm | Time per Element | Space | Notes |
|-----------|-----------------|-------|-------|
| Fixed-Size Sliding Window | O(1) add/sum/avg, O(n) min/max | O(n) | Window size n |
| Optimized Sliding Window | O(1) amortized all ops | O(n) | Uses monotonic queues |
| Time-Based Sliding Window | O(1) add, O(n) cleanup | O(n) | n = events in window |
| Token Bucket Rate Limiter | O(1) | O(1) | Constant space |
| Leaky Bucket Rate Limiter | O(1) | O(1) | Constant space |
| Sliding Window Counter | O(1) | O(k) | k = time buckets |
| Moving Average (SMA) | O(1) | O(n) | n = window size |
| Exponential Moving Average (EMA) | O(1) | O(1) | Constant space |
| Top-K Elements | O(1) add, O(n log n) query | O(n) | n = unique elements |
| Reservoir Sampling | O(1) | O(k) | k = sample size |
| Stream Aggregator | O(1) per record | O(n) | n = stored values |

*See: Chapter 36 (Stream Processing Algorithms)*

## Probabilistic Algorithms

Probabilistic algorithms trade perfect accuracy for dramatic space/time improvements, essential for big data processing.

| Algorithm | Time | Space | Accuracy | Use Case |
|-----------|------|-------|----------|----------|
| Bloom Filter | O(k) add/contains | O(m) | No false negatives | Membership testing |
| HyperLogLog | O(1) add | O(2^p) | ±0.81% (p=14) | Cardinality estimation |
| Count-Min Sketch | O(d) add/estimate | O(w×d) | ±εN | Frequency estimation |
| Reservoir Sampling | O(1) add | O(k) | Exact distribution | Random sampling |
| Morris Counter | O(1) increment | O(1) | ±√n | Approximate counting |
| Skip List | O(log n) avg | O(n log n) | Exact | Sorted set operations |

**Notes**:
- **Bloom Filter**: k = number of hash functions, m = bit array size
- **HyperLogLog**: p = precision parameter (typically 14)
- **Count-Min Sketch**: w = width, d = depth (hash functions)
- **Reservoir Sampling**: k = sample size

*See: Chapter 32 (Probabilistic Algorithms)*

## Concurrent Algorithms

Concurrent algorithms enable parallel execution, dramatically improving performance for I/O-bound and CPU-intensive tasks.

| Pattern | Time Complexity | Space | Notes |
|---------|----------------|-------|-------|
| Async I/O (ReactPHP) | O(1) scheduling | O(n) | n = concurrent operations |
| Parallel Processing | O(n/p) | O(n) | p = processors |
| Coroutines (Swoole) | O(1) yield/resume | O(k) | k = coroutine stack |
| Worker Pool | O(n/p) | O(p) | p = workers, n = tasks |
| Pipeline Processing | O(n) | O(k) | k = pipeline stages |
| Fork-Join | O(n/p + log p) | O(n) | Divide and conquer |

**Notes**:
- Concurrent algorithms reduce wall-clock time but may increase total CPU time
- Space complexity often increases due to multiple execution contexts
- Actual speedup depends on I/O wait time and CPU cores available

*See: Chapter 31 (Concurrent Algorithms)*

## Common PHP Function Complexities

### Array Functions

| Function | Time | Space | Notes |
|----------|------|-------|-------|
| `in_array()` | O(n) | O(1) | Linear search - avoid in loops |
| `array_search()` | O(n) | O(1) | Linear search - returns key |
| `isset()` | O(1) | O(1) | Hash lookup - use for membership |
| `array_key_exists()` | O(1) | O(1) | Hash lookup - checks null values too |
| `count()` | O(1) | O(1) | Stored value - cache outside loops |
| `array_push()` | O(1) | O(1) | Append - same as `$arr[] = $val` |
| `array_pop()` | O(1) | O(1) | Remove last |
| `array_shift()` | O(n) | O(1) | Remove first, reindex - use SplQueue |
| `array_unshift()` | O(n) | O(1) | Prepend, reindex - expensive |
| `array_merge()` | O(n + m) | O(n + m) | Combine arrays |
| `array_merge_recursive()` | O(n + m) | O(n + m) | Deep merge |
| `array_combine()` | O(n) | O(n) | Keys + values to array |
| `array_fill()` | O(n) | O(n) | Fill with value |
| `array_pad()` | O(n) | O(n) | Pad to length |
| `array_chunk()` | O(n) | O(n) | Split into chunks |
| `array_column()` | O(n) | O(n) | Extract column from 2D array |
| `array_flip()` | O(n) | O(n) | Swap keys and values |
| `array_intersect()` | O(n * m) | O(n) | Common values |
| `array_diff()` | O(n * m) | O(n) | Difference |
| `array_values()` | O(n) | O(n) | Reindex numerically |
| `array_keys()` | O(n) | O(n) | Extract keys |
| `sort()` | O(n log n) | O(log n) | Quicksort variant |
| `rsort()` | O(n log n) | O(log n) | Reverse sort |
| `asort()` | O(n log n) | O(log n) | Sort maintaining keys |
| `ksort()` | O(n log n) | O(log n) | Sort by keys |
| `usort()` | O(n log n) | O(log n) | Custom comparison sort |
| `array_unique()` | O(n) | O(n) | Hash-based deduplication |
| `array_filter()` | O(n) | O(n) | Iterate + callback |
| `array_map()` | O(n) | O(n) | Iterate + transform |
| `array_reduce()` | O(n) | O(1) | Iterate + accumulate |
| `array_walk()` | O(n) | O(1) | In-place modification |
| `array_reverse()` | O(n) | O(n) | Reverse order |
| `array_slice()` | O(k) | O(k) | Extract k elements |
| `array_splice()` | O(n) | O(k) | Remove and replace |
| `array_sum()` | O(n) | O(1) | Sum numeric array |
| `array_product()` | O(n) | O(1) | Product of array |
| `min()` / `max()` | O(n) | O(1) | Find min/max in array |

### String Functions

| Function | Time | Space | Notes |
|----------|------|-------|-------|
| `strlen()` | O(1) | O(1) | Cached length |
| `substr()` | O(k) | O(k) | Extract k characters |
| `strpos()` | O(n) | O(1) | Find substring - fast |
| `str_contains()` | O(n) | O(1) | PHP 8.0+ - check substring |
| `str_starts_with()` | O(m) | O(1) | PHP 8.0+ - check prefix |
| `str_ends_with()` | O(m) | O(1) | PHP 8.0+ - check suffix |
| `str_replace()` | O(n) | O(n) | Simple replacement |
| `str_ireplace()` | O(n) | O(n) | Case-insensitive replace |
| `preg_match()` | O(n) avg | O(1) | Regex - can be O(2^n) worst |
| `preg_replace()` | O(n) avg | O(n) | Regex replacement |
| `explode()` | O(n) | O(n) | Split string |
| `implode()` | O(n) | O(n) | Join strings |
| `trim()` / `ltrim()` / `rtrim()` | O(n) | O(n) | Remove whitespace |
| `strtoupper()` / `strtolower()` | O(n) | O(n) | Case conversion |
| `strcmp()` | O(n) | O(1) | String comparison |
| `levenshtein()` | O(n * m) | O(n * m) | Edit distance |

### PHP 8.0+ Modern Functions

| Function | Time | Space | Notes |
|----------|------|-------|-------|
| `str_contains()` | O(n) | O(1) | Better than `strpos() !== false` |
| `str_starts_with()` | O(m) | O(1) | Better than `substr($str, 0, m)` |
| `str_ends_with()` | O(m) | O(1) | Better than substring comparison |
| `array_is_list()` | O(n) | O(1) | Check if sequential numeric keys |
| `fdiv()` | O(1) | O(1) | Division by zero returns INF |
| Match expression | O(1) | O(1) | Strict comparison switch |

### PHP 8.4+ Features

| Feature | Time | Space | Notes |
|---------|------|-------|-------|
| Property hooks | O(1) | O(1) | Overhead per property access |
| Asymmetric visibility | O(1) | O(1) | No performance impact |
| Typed class constants | O(1) | O(1) | Compile-time check |
| Override attribute | O(1) | O(1) | Compile-time validation |

### SPL Data Structure Operations

| Operation | SplStack | SplQueue | SplHeap | SplFixedArray |
|-----------|----------|----------|---------|---------------|
| Access | O(1) top | O(1) front | O(1) top | O(1) by index |
| Insert | O(1) push | O(1) enqueue | O(log n) | O(1) at index |
| Delete | O(1) pop | O(1) dequeue | O(log n) | O(1) at index |
| Search | O(n) | O(n) | O(n) | O(n) |
| Space | O(n) | O(n) | O(n) | O(n) fixed |

**Example: When to use each**:

```php
<?php
# filename: queue-comparison.php

declare(strict_types=1);

// ❌ BAD: Using array_shift in loop (O(n²) total)
while (count($arr) > 0) {
    $item = array_shift($arr);  // O(n) each time
    process($item);
}

// ✅ GOOD: Using SplQueue (O(n) total)
$queue = new SplQueue();
foreach ($arr as $item) {
    $queue->enqueue($item);
}
while (!$queue->isEmpty()) {
    $item = $queue->dequeue();  // O(1) each time
    process($item);
}
```

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
| Stream processing | O(1) or O(log n) | Sliding windows, aggregations |
| Probabilistic structures | O(1) to O(k) | Bloom filters, sketches |
| Concurrent contexts | O(p) | p = parallel workers/threads |

### PHP-Specific Memory Considerations

```php
<?php
# filename: memory-overhead.php

declare(strict_types=1);

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

### Need rate limiting?
→ Token Bucket O(1) time, O(1) space
→ Sliding Window Counter O(1) time, O(k) space

### Need to track unique items in stream?
→ HyperLogLog O(1) add, O(2^p) space
→ Bloom Filter O(k) add/check, O(m) space

### Need frequency counts in stream?
→ Count-Min Sketch O(d) add/estimate, O(w×d) space

### Need parallel processing?
→ Worker Pool O(n/p) time, O(p) space
→ Async I/O O(1) scheduling, O(n) space

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

6. ❌ Using `array_shift()` in sliding window → O(n²) total
   ✅ Use deque or circular buffer → O(1) per operation

7. ❌ Storing entire stream in memory → O(n) space
   ✅ Use stream processing algorithms → O(1) or O(log n) space

8. ❌ Exact counting for billions of items → O(n) space
   ✅ Use probabilistic algorithms (Bloom filter, HyperLogLog) → O(1) or O(log n) space

## Performance Testing Template

```php
<?php
# filename: performance-testing.php

declare(strict_types=1);

function measureTime(callable $fn, mixed ...$args): float {
    $start = microtime(true);
    $fn(...$args);
    return microtime(true) - $start;
}

function measureMemory(callable $fn, mixed ...$args): int {
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

## Real-World Optimization Decisions

### When to Optimize?

**Don't optimize** if:
- Data size is small (n < 100)
- Code runs rarely (batch jobs, migrations)
- Code is clearer without optimization
- Time saved is negligible

**Do optimize** if:
- Data size is large (n > 10,000)
- Code runs frequently (API endpoints, real-time systems)
- User experience is affected (page load time)
- Resource costs are high (server costs, memory)

### Optimization Priority Matrix

```
Impact vs Effort

High Impact, Low Effort  ← START HERE
  ├─ Replace in_array() with isset()
  ├─ Cache count() outside loops
  ├─ Use native sort() instead of custom O(n²)
  └─ Enable OPcache

High Impact, High Effort  ← DO AFTER QUICK WINS
  ├─ Implement caching layer (Redis/Memcached)
  ├─ Database query optimization
  ├─ Algorithm redesign (e.g., O(n²) → O(n))
  └─ Data structure refactoring

Low Impact, Low Effort  ← DO IF TIME PERMITS
  ├─ Code style improvements
  ├─ Minor refactoring
  └─ Comment additions

Low Impact, High Effort  ← AVOID
  ├─ Micro-optimizations
  ├─ Premature optimization
  └─ Over-engineering
```

### Real Production Examples

**Example 1: E-commerce Product Search**
```php
<?php
# filename: product-search-optimization.php

declare(strict_types=1);

// ❌ BAD: O(n²) - checking duplicates in loop
$filtered = [];
foreach ($products as $product) {
    if (!in_array($product->id, $filtered)) {  // O(n) each time
        $filtered[] = $product->id;
    }
}
// With 10,000 products: 10,000² = 100M operations!

// ✅ GOOD: O(n) - using hash table
$seen = [];
$filtered = [];
foreach ($products as $product) {
    if (!isset($seen[$product->id])) {  // O(1) each time
        $filtered[] = $product->id;
        $seen[$product->id] = true;
    }
}
// With 10,000 products: 10,000 operations (10,000x faster!)
```
*Impact: Page load reduced from 5s to 50ms*

**Example 2: User Activity Feed**
```php
<?php
# filename: activity-feed-optimization.php

declare(strict_types=1);

// ❌ BAD: N+1 query problem
$posts = $db->query("SELECT * FROM posts LIMIT 50");
foreach ($posts as $post) {
    $user = $db->query("SELECT * FROM users WHERE id = {$post['user_id']}");
    $post['author'] = $user;
}
// 51 database queries!

// ✅ GOOD: Single JOIN query
$posts = $db->query("
    SELECT posts.*, users.name, users.avatar
    FROM posts
    JOIN users ON posts.user_id = users.id
    LIMIT 50
");
// 1 database query (51x fewer queries!)
```
*Impact: API response time reduced from 800ms to 15ms*

**Example 3: Log Processing**
```php
<?php
# filename: log-processing-optimization.php

declare(strict_types=1);

// ❌ BAD: String concatenation in loop
$log = '';
foreach ($entries as $entry) {
    $log .= $entry . "\n";  // Creates new string each time
}
// O(n²) time, O(n²) memory with 100,000 entries = 10B operations!

// ✅ GOOD: Array + implode
$lines = [];
foreach ($entries as $entry) {
    $lines[] = $entry;
}
$log = implode("\n", $lines);
// O(n) time, O(n) memory with 100,000 entries = 100K operations
```
*Impact: Memory usage reduced from 5GB to 50MB*

## Cross-Reference Guide

### By Chapter

**Fundamentals**:
- **Chapter 1**: Introduction to Algorithms → Big O basics
- **Chapter 2**: Time Complexity Analysis → Detailed complexity analysis
- **Chapter 3**: Space Complexity → Memory usage patterns

**Data Structures**:
- **Chapter 4**: Arrays and Lists → Array operations (this appendix)
- **Chapter 5**: Stacks and Queues → Stack/Queue complexity
- **Chapter 6**: Hash Tables → O(1) lookup patterns
- **Chapter 14**: Heaps → Heap operations and Priority Queues
- **Chapter 16**: Binary Search Trees → Tree operations
- **Chapter 17**: Balanced Trees → AVL/Red-Black tree guarantees
- **Chapter 18**: Tree Traversals → DFS/BFS complexity
- **Chapter 20**: Balanced Trees (AVL & Red-Black) → Self-balancing tree operations

**Algorithms**:
- **Chapter 7**: Linear Search → O(n) search
- **Chapter 8**: Binary Search → O(log n) search
- **Chapter 9-13**: Sorting Algorithms → Sorting comparison table
- **Chapter 19**: Graph Representations → Graph storage complexity
- **Chapter 20-22**: Graph Algorithms → DFS, BFS, Dijkstra complexity
- **Chapter 23**: String Algorithms → Pattern matching complexity
- **Chapter 33**: String Algorithms Deep Dive → Advanced string algorithms (Z-algorithm, Suffix Tree)
- **Chapter 25**: Dynamic Programming → Memoization patterns
- **Chapter 31**: Concurrent Algorithms → Parallel processing patterns
- **Chapter 32**: Probabilistic Algorithms → Space-efficient data structures
- **Chapter 36**: Stream Processing → Real-time algorithms

**Optimization**:
- **Chapter 29**: Performance Optimization → Practical optimization
- **Appendix B**: PHP Performance Tips → PHP-specific optimizations

### By Problem Type

**Need O(1) lookup?**
→ Chapter 6: Hash Tables
→ This appendix: Hash Table section

**Need to sort efficiently?**
→ Chapter 10: Merge Sort
→ Chapter 12: Quick Sort
→ This appendix: Sorting Algorithms table

**Need shortest path?**
→ Chapter 20: BFS (unweighted)
→ Chapter 22: Dijkstra (weighted)
→ This appendix: Graph Algorithms table

**Need to optimize recursive algorithm?**
→ Chapter 25: Dynamic Programming
→ This appendix: DP Problems table

**Working with strings?**
→ Chapter 23: String Algorithms
→ Chapter 33: String Algorithms Deep Dive (advanced)
→ This appendix: String Algorithms table

**Processing data streams?**
→ Chapter 36: Stream Processing Algorithms
→ This appendix: Stream Processing Algorithms table

**Need space-efficient solutions?**
→ Chapter 32: Probabilistic Algorithms
→ This appendix: Probabilistic Algorithms table

**Need parallel processing?**
→ Chapter 31: Concurrent Algorithms
→ This appendix: Concurrent Algorithms table

## Quick Decision Tree

```
Start: I need to process data
│
├─ Known operation on standard data?
│  └─ Use this appendix to find complexity
│
├─ Custom algorithm needed?
│  ├─ Search problem?
│  │  ├─ Sorted? → Chapter 8: Binary Search
│  │  └─ Unsorted? → Chapter 7: Linear Search
│  │
│  ├─ Sorting problem?
│  │  └─ Chapter 9-13: Sorting Algorithms
│  │
│  ├─ Graph problem?
│  │  └─ Chapter 19-22: Graph Algorithms
│  │
│  ├─ Optimization problem?
│  │  └─ Chapter 25: Dynamic Programming
│  │
│  ├─ Stream processing?
│  │  └─ Chapter 36: Stream Processing Algorithms
│  │
│  ├─ Big data / space constraints?
│  │  └─ Chapter 32: Probabilistic Algorithms
│  │
│  └─ Need parallel/concurrent?
│     └─ Chapter 31: Concurrent Algorithms
│
└─ Performance issue?
   ├─ Algorithm complexity? → This appendix
   ├─ PHP-specific? → Appendix B
   └─ Profiling needed? → Chapter 29
```

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="appendix-a"
  label="Complexity Cheat Sheet mastered!"
/>

## Resources

### External References
- [Big-O Cheat Sheet](https://www.bigocheatsheet.com/) - Interactive complexity visualization
- [PHP Manual: Data Structures](https://www.php.net/manual/en/book.ds.php) - Official SPL documentation
- [PHP Manual: Array Functions](https://www.php.net/manual/en/ref.array.php) - Complete function reference

### This Series
- **Chapter 1**: Introduction to Algorithms
- **Chapter 2**: Time Complexity Analysis - Deep dive into Big O
- **Chapter 3**: Space Complexity - Memory usage analysis
- **Chapter 4**: Arrays and Lists - Array operations
- **Chapter 6**: Hash Tables - O(1) data structures
- **Chapter 8**: Binary Search - O(log n) searching
- **Chapter 10-13**: Sorting Algorithms - Complete sorting guide
- **Chapter 25**: Dynamic Programming - Optimization techniques
- **Chapter 29**: Performance Optimization - Real-world optimization
- **Chapter 31**: Concurrent Algorithms - Parallel processing patterns
- **Chapter 30**: Real-World Case Studies - Practical algorithm applications
- **Chapter 32**: Probabilistic Algorithms - Space-efficient data structures
- **Chapter 33**: String Algorithms Deep Dive - Advanced string matching
- **Chapter 36**: Stream Processing Algorithms - Real-time data processing
- **Appendix B**: PHP Performance Tips - PHP-specific optimizations
- **Appendix C**: Glossary - Algorithm terminology
