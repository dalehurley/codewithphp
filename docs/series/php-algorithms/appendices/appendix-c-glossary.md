# Appendix C: Glossary

Comprehensive definitions of algorithm and data structure terminology used throughout this series.

## A

**Abstract Data Type (ADT)**: A mathematical model for data types defined by behavior (operations) rather than implementation. Examples: Stack, Queue, List.

**Adjacency List**: Graph representation using arrays/lists to store neighbors of each vertex. Space: O(V + E).

**Adjacency Matrix**: Graph representation using 2D array where matrix[i][j] indicates edge from vertex i to j. Space: O(V²).

**Algorithm**: A finite sequence of well-defined instructions to solve a problem or perform a computation.

**Amortized Time Complexity**: Average time per operation over a sequence of operations, accounting for expensive occasional operations.

**Array**: Contiguous block of memory storing elements of the same type, accessible by index in O(1) time.

**Asymptotic Analysis**: Study of algorithm behavior as input size approaches infinity using Big O, Omega, and Theta notation.

**AVL Tree**: Self-balancing binary search tree where height difference between left and right subtrees is at most 1.

## B

**Backtracking**: Algorithmic technique that builds solutions incrementally and abandons solutions that fail to satisfy constraints.

**Balanced Tree**: Binary tree where height of left and right subtrees differ by at most a constant factor, ensuring O(log n) operations.

**Base Case**: Terminating condition in recursive algorithms that stops further recursion.

**BFS (Breadth-First Search)**: Graph traversal algorithm that explores vertices level by level, using a queue.

**Big O Notation**: Upper bound on algorithm growth rate, describing worst-case time or space complexity.

**Big Omega (Ω)**: Lower bound on algorithm growth rate, describing best-case complexity.

**Big Theta (Θ)**: Tight bound on algorithm growth rate when upper and lower bounds are the same.

**Binary Search**: Efficient search algorithm on sorted arrays that repeatedly divides search space in half. Time: O(log n).

**Binary Search Tree (BST)**: Binary tree where left subtree contains smaller values and right subtree contains larger values.

**Binary Tree**: Tree data structure where each node has at most two children (left and right).

**Bit Manipulation**: Operations on individual bits of data (AND, OR, XOR, shifts) for space-efficient algorithms.

**Bloom Filter**: Probabilistic data structure for testing set membership with possible false positives but no false negatives.

**Brute Force**: Straightforward approach that tries all possible solutions, often inefficient but simple.

## C

**Cache**: Fast storage layer that stores frequently accessed data to improve access speed.

**Caching**: Strategy of storing computed results to avoid redundant calculations.

**Circular Queue**: Queue implementation where end connects to beginning, forming a circle.

**Collision**: Situation in hash tables where two different keys map to the same hash value.

**Comparison Sort**: Sorting algorithm that works by comparing elements. Lower bound: O(n log n).

**Complete Binary Tree**: Binary tree where all levels are fully filled except possibly the last, which is filled left to right.

**Complexity**: Measure of resources (time, space) required by an algorithm as a function of input size.

**Connected Component**: Maximal set of vertices in a graph where each vertex is reachable from every other vertex.

**Constant Time**: O(1) complexity - execution time doesn't depend on input size.

**Convex Hull**: Smallest convex polygon containing all points in a set.

**Cycle**: Path in a graph that starts and ends at the same vertex.

## D

**DAG (Directed Acyclic Graph)**: Directed graph with no cycles, used in dependency resolution and topological sorting.

**Data Structure**: Organized way to store and organize data for efficient access and modification.

**Degree (Graph)**: Number of edges connected to a vertex. In-degree (incoming), out-degree (outgoing).

**Deque (Double-Ended Queue)**: Queue allowing insertion and deletion at both ends.

**DFS (Depth-First Search)**: Graph traversal that explores as far as possible along each branch before backtracking.

**Dijkstra's Algorithm**: Finds shortest paths from source vertex to all other vertices in weighted graph with non-negative edges.

**Directed Graph**: Graph where edges have direction (from one vertex to another).

**Divide and Conquer**: Algorithm design paradigm that breaks problem into smaller subproblems, solves them recursively, and combines results.

**Dynamic Programming**: Optimization technique that stores solutions to subproblems to avoid redundant calculations.

## E

**Edge**: Connection between two vertices in a graph. Can be directed or undirected, weighted or unweighted.

**Exponential Time**: O(2^n) or worse complexity - grows extremely rapidly with input size.

**Edge List**: Graph representation storing all edges as list of (vertex1, vertex2) pairs.

## F

**Factorial Time**: O(n!) complexity - extremely slow, usually only viable for n < 12.

**FIFO (First In, First Out)**: Queue ordering where first element added is first removed.

**Fibonacci Heap**: Advanced heap data structure with excellent amortized time for decrease-key operation.

**Full Binary Tree**: Binary tree where every node has either 0 or 2 children.

## G

**Generator**: PHP feature that yields values one at a time instead of building full array in memory.

**Graph**: Data structure consisting of vertices (nodes) and edges (connections).

**Greedy Algorithm**: Makes locally optimal choice at each step, hoping to find global optimum.

**Greatest Common Divisor (GCD)**: Largest positive integer dividing two or more integers.

## H

**Hash Collision**: When two different keys produce the same hash value.

**Hash Function**: Function mapping keys to hash values (array indices) for quick access.

**Hash Table**: Data structure providing O(1) average-case insertion, deletion, and lookup using hashing.

**Heap**: Complete binary tree satisfying heap property (min-heap: parent ≤ children, max-heap: parent ≥ children).

**Heapify**: Process of converting array into valid heap structure.

**Height (Tree)**: Maximum distance from root to any leaf node.

**Heuristic**: Rule of thumb or educated guess to find good (not necessarily optimal) solutions quickly.

**HyperLogLog**: Probabilistic algorithm for counting distinct elements using minimal memory.

## I

**In-Place Algorithm**: Algorithm that modifies input directly using O(1) extra space.

**Insertion Sort**: Simple sorting algorithm building sorted array one element at a time. Good for small/nearly sorted arrays.

**In-Order Traversal**: BST traversal visiting left subtree, then root, then right subtree (produces sorted order).

## K

**K-way Merge**: Merging K sorted arrays/lists into one sorted result.

**KMP (Knuth-Morris-Pratt)**: Efficient string matching algorithm using preprocessing to avoid re-scanning. Time: O(n + m).

**Knapsack Problem**: Optimization problem of selecting items with weights and values to maximize total value within weight constraint.

## L

**Leaf Node**: Tree node with no children.

**Level-Order Traversal**: Tree traversal visiting nodes level by level (BFS).

**LIFO (Last In, First Out)**: Stack ordering where last element added is first removed.

**Linear Time**: O(n) complexity - execution time proportional to input size.

**Linked List**: Data structure where elements (nodes) contain data and reference to next node.

**Logarithmic Time**: O(log n) complexity - very efficient, grows slowly as input increases.

**Longest Common Subsequence (LCS)**: Longest sequence appearing in same order in two sequences.

**Lookup Table**: Precomputed array of values used to replace runtime computations with simple array access.

## M

**Memoization**: Optimization technique storing function results to avoid recalculation with same inputs.

**Merge Sort**: Divide-and-conquer sorting algorithm with O(n log n) time complexity. Stable and predictable.

**Min Heap**: Heap where parent node value ≤ child node values (minimum at root).

**Max Heap**: Heap where parent node value ≥ child node values (maximum at root).

**MST (Minimum Spanning Tree)**: Subset of edges connecting all vertices in weighted graph with minimum total weight.

## N

**NP-Complete**: Class of computational problems with no known polynomial-time solution.

**N-ary Tree**: Tree where each node can have at most N children.

**Node**: Basic unit of data structures containing data and references/pointers.

**Null**: Reference that points to nothing, indicating absence of value or end of structure.

## O

**O(1)**: Constant time complexity - operations take fixed time regardless of input size.

**O(log n)**: Logarithmic complexity - doubles input, adds constant operations.

**O(n)**: Linear complexity - doubles input, doubles operations.

**O(n log n)**: Linearithmic complexity - optimal for comparison-based sorting.

**O(n²)**: Quadratic complexity - doubles input, quadruples operations.

## P

**Parent Node**: Node that has references to child nodes in tree structure.

**Partition**: Dividing array into segments based on pivot value (used in QuickSort).

**Path**: Sequence of edges connecting two vertices in graph.

**Perfect Binary Tree**: Binary tree where all interior nodes have two children and all leaves are at same level.

**Pivot**: Element used to partition array in QuickSort algorithm.

**Polynomial Time**: Algorithm with time complexity O(n^k) for some constant k.

**Post-Order Traversal**: Tree traversal visiting left subtree, right subtree, then root.

**Pre-Order Traversal**: Tree traversal visiting root, then left subtree, then right subtree.

**Priority Queue**: Queue where elements have priority values determining order of removal.

**Probabilistic Algorithm**: Algorithm using randomness and accepting small probability of error for efficiency.

## Q

**Quadratic Time**: O(n²) complexity - often from nested loops.

**Queue**: FIFO data structure supporting enqueue (add to rear) and dequeue (remove from front) operations.

**Quick Sort**: Efficient divide-and-conquer sorting algorithm. Average: O(n log n), Worst: O(n²).

## R

**Radix Sort**: Non-comparison sorting algorithm sorting numbers digit by digit. Time: O(nk).

**Recursion**: Function calling itself with simpler inputs until reaching base case.

**Red-Black Tree**: Self-balancing BST with nodes colored red or black, ensuring O(log n) operations.

**Root**: Top node of tree with no parent.

**Running Time**: Time taken by algorithm to complete as function of input size.

## S

**Shortest Path**: Path between two vertices with minimum total edge weight.

**Singly Linked List**: Linked list where each node has reference to next node only.

**Sliding Window**: Technique maintaining subset of array/string by adding/removing elements at ends.

**Space Complexity**: Amount of memory used by algorithm as function of input size.

**Stable Sort**: Sorting algorithm preserving relative order of equal elements.

**Stack**: LIFO data structure supporting push (add to top) and pop (remove from top) operations.

**Suffix Array**: Sorted array of all suffixes of string, enabling efficient substring queries.

**Suffix Tree**: Compressed trie of all suffixes, supporting linear-time pattern matching.

## T

**Tail Recursion**: Recursion where recursive call is last operation in function.

**Time Complexity**: Number of operations algorithm performs as function of input size.

**Topological Sort**: Linear ordering of DAG vertices such that for every edge (u,v), u comes before v.

**Tree**: Connected acyclic graph with hierarchical structure.

**Trie (Prefix Tree)**: Tree-like data structure for storing strings where common prefixes share paths.

**Two Pointers**: Technique using two array indices (pointers) moving toward each other or in same direction.

## U

**Undirected Graph**: Graph where edges have no direction (bidirectional).

**Union-Find (Disjoint Set)**: Data structure tracking partitions of set, supporting union and find operations efficiently.

**Unstable Sort**: Sorting algorithm that may change relative order of equal elements.

## V

**Vertex (Node)**: Fundamental unit of graph representing entity or location.

**Visited Set**: Data structure tracking which nodes have been explored in graph traversal.

## W

**Weighted Graph**: Graph where edges have associated weights/costs.

**Worst-Case Complexity**: Maximum resources algorithm requires over all possible inputs of given size.

## Example Usage

```php
// Algorithm: QuickSort (Divide and Conquer)
function quickSort(array $arr): array {
    // Base Case
    if (count($arr) <= 1) {
        return $arr;
    }

    // Pivot Selection
    $pivot = $arr[0];

    // Partition
    $left = array_filter($arr, fn($x) => $x < $pivot);
    $middle = array_filter($arr, fn($x) => $x === $pivot);
    $right = array_filter($arr, fn($x) => $x > $pivot);

    // Recursion & Merge
    return array_merge(
        quickSort($left),
        $middle,
        quickSort($right)
    );
}

// Data Structure: Stack (LIFO)
$stack = new SplStack();
$stack->push(1);  // [1]
$stack->push(2);  // [1, 2]
$stack->push(3);  // [1, 2, 3]
echo $stack->pop();  // 3 (last in, first out)

// Data Structure: Queue (FIFO)
$queue = new SplQueue();
$queue->enqueue(1);  // [1]
$queue->enqueue(2);  // [1, 2]
$queue->enqueue(3);  // [1, 2, 3]
echo $queue->dequeue();  // 1 (first in, first out)

// Complexity: O(n) Linear Time
foreach ($array as $item) {  // Visits each element once
    process($item);
}

// Complexity: O(log n) Logarithmic Time
function binarySearch($arr, $target) {
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);
        if ($arr[$mid] === $target) return $mid;
        if ($arr[$mid] < $target) $left = $mid + 1;
        else $right = $mid - 1;
    }

    return -1;
}

// Complexity: O(n²) Quadratic Time
for ($i = 0; $i < count($arr); $i++) {
    for ($j = 0; $j < count($arr); $j++) {  // Nested loops
        // ...
    }
}

// Memoization (Dynamic Programming)
$cache = [];
function fibonacci($n) use (&$cache) {
    if ($n <= 1) return $n;

    if (!isset($cache[$n])) {
        $cache[$n] = fibonacci($n - 1) + fibonacci($n - 2);
    }

    return $cache[$n];
}

// Graph: Adjacency List
$graph = [
    'A' => ['B', 'C'],
    'B' => ['A', 'D'],
    'C' => ['A', 'D'],
    'D' => ['B', 'C']
];

// Hash Table (PHP Array)
$hashTable = [];
$hashTable['key'] = 'value';  // O(1) insertion
$value = $hashTable['key'];   // O(1) lookup
isset($hashTable['key']);     // O(1) existence check
```

## Quick Reference Cards

### Common Patterns

**Two Pointers**: Used for array problems involving pairs or subarrays
```php
$left = 0; $right = count($arr) - 1;
while ($left < $right) {
    // Process
    $left++;
    $right--;
}
```

**Sliding Window**: Used for contiguous subarray problems
```php
$windowStart = 0;
for ($windowEnd = 0; $windowEnd < count($arr); $windowEnd++) {
    // Expand window
    while (/* window invalid */) {
        $windowStart++;  // Shrink window
    }
}
```

**Fast & Slow Pointers**: Used for cycle detection
```php
$slow = $head;
$fast = $head;
while ($fast && $fast->next) {
    $slow = $slow->next;
    $fast = $fast->next->next;
    if ($slow === $fast) {
        // Cycle detected
    }
}
```

## See Also

- **Appendix A**: Complexity Cheat Sheet - Detailed complexity tables
- **Chapter 2**: Time Complexity Analysis - In-depth complexity analysis
- **Chapter 3**: Space Complexity - Memory usage patterns
- **Appendix D**: Further Reading - Books and resources for deeper understanding
