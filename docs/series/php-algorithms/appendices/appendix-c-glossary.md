# Appendix C: Glossary

Comprehensive definitions of algorithm and data structure terminology used throughout this series. Terms include pronunciation guides where helpful and cross-references to relevant chapters.

## A

**Abstract Data Type (ADT)**: A mathematical model for data types defined by behavior (operations) rather than implementation. Examples: Stack, Queue, List.

```php
// ADT: Stack (behavior defined, implementation hidden)
interface StackInterface {
    public function push($item): void;
    public function pop();
    public function top();
    public function isEmpty(): bool;
}

// Implementation can vary (array, linked list, etc.)
class ArrayStack implements StackInterface {
    private array $items = [];

    public function push($item): void {
        $this->items[] = $item;
    }

    public function pop() {
        return array_pop($this->items);
    }

    public function top() {
        return end($this->items);
    }

    public function isEmpty(): bool {
        return empty($this->items);
    }
}
```
*See: Chapter 5 (Stacks and Queues)*

**Adjacency List** (pronunciation: ad-JAY-sen-see): Graph representation using arrays/lists to store neighbors of each vertex. Space: O(V + E).

```php
// Adjacency list representation
$graph = [
    'A' => ['B', 'C'],        // A connects to B and C
    'B' => ['A', 'D', 'E'],   // B connects to A, D, E
    'C' => ['A', 'F'],
    'D' => ['B'],
    'E' => ['B', 'F'],
    'F' => ['C', 'E']
];

// Check neighbors: O(1) average
$neighbors = $graph['A'];  // ['B', 'C']

// Check if edge exists: O(degree(v))
$hasEdge = in_array('B', $graph['A']);
```
*See: Chapter 19 (Graph Representations), Chapter 20 (Graph Algorithms)*

**Adjacency Matrix** (pronunciation: ad-JAY-sen-see MAY-triks): Graph representation using 2D array where matrix[i][j] indicates edge from vertex i to j. Space: O(V²).

```php
// Adjacency matrix representation (V = 4 vertices)
$matrix = [
    [0, 1, 1, 0],  // Vertex 0 connects to 1, 2
    [1, 0, 0, 1],  // Vertex 1 connects to 0, 3
    [1, 0, 0, 1],  // Vertex 2 connects to 0, 3
    [0, 1, 1, 0]   // Vertex 3 connects to 1, 2
];

// Check if edge exists: O(1)
$hasEdge = $matrix[0][1] === 1;  // true
```
*See: Chapter 19 (Graph Representations)*

**Algorithm** (pronunciation: AL-go-rith-em): A finite sequence of well-defined instructions to solve a problem or perform a computation.

*See: Chapter 1 (Introduction to Algorithms)*

**Amortized Time Complexity** (pronunciation: AM-or-tized): Average time per operation over a sequence of operations, accounting for expensive occasional operations.

```php
// Dynamic array (PHP array): amortized O(1) append
$arr = [];
for ($i = 0; $i < 1000; $i++) {
    $arr[] = $i;  // Usually O(1), occasionally O(n) when resizing
                  // Amortized: O(1) average over all operations
}
```
*See: Chapter 2 (Time Complexity Analysis)*

**Array**: Contiguous block of memory storing elements of the same type, accessible by index in O(1) time.

```php
// PHP array (numeric keys)
$numbers = [10, 20, 30, 40, 50];

// O(1) random access
$value = $numbers[2];  // 30

// O(1) append
$numbers[] = 60;

// O(n) insert at beginning
array_unshift($numbers, 0);
```
*See: Chapter 4 (Arrays and Lists)*

**Asymptotic Analysis** (pronunciation: ace-im-TOT-ik): Study of algorithm behavior as input size approaches infinity using Big O, Omega, and Theta notation.

*See: Chapter 2 (Time Complexity Analysis), Appendix A (Complexity Cheat Sheet)*

**AVL Tree** (pronunciation: A-V-L tree, named after inventors Adelson-Velsky and Landis): Self-balancing binary search tree where height difference between left and right subtrees is at most 1.

```php
// AVL Tree: maintains balance factor of -1, 0, or 1
class AVLNode {
    public $value;
    public $left = null;
    public $right = null;
    public $height = 1;

    public function __construct($value) {
        $this->value = $value;
    }
}

class AVLTree {
    private ?AVLNode $root = null;

    private function height(?AVLNode $node): int {
        return $node ? $node->height : 0;
    }

    private function balanceFactor(AVLNode $node): int {
        return $this->height($node->left) - $this->height($node->right);
    }

    // Rotations maintain O(log n) operations
    private function rotateRight(AVLNode $y): AVLNode {
        $x = $y->left;
        $T2 = $x->right;

        $x->right = $y;
        $y->left = $T2;

        $y->height = max($this->height($y->left), $this->height($y->right)) + 1;
        $x->height = max($this->height($x->left), $this->height($x->right)) + 1;

        return $x;
    }
}
```
*See: Chapter 17 (Balanced Trees)*

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

## Additional Terms

**Bellman-Ford Algorithm** (pronunciation: BELL-man ford): Single-source shortest path algorithm that can handle negative edge weights. Time: O(VE).

```php
function bellmanFord($graph, $V, $start) {
    $distance = array_fill(0, $V, PHP_INT_MAX);
    $distance[$start] = 0;

    // Relax all edges V-1 times
    for ($i = 0; $i < $V - 1; $i++) {
        foreach ($graph as [$u, $v, $weight]) {
            if ($distance[$u] !== PHP_INT_MAX &&
                $distance[$u] + $weight < $distance[$v]) {
                $distance[$v] = $distance[$u] + $weight;
            }
        }
    }

    // Check for negative cycles
    foreach ($graph as [$u, $v, $weight]) {
        if ($distance[$u] !== PHP_INT_MAX &&
            $distance[$u] + $weight < $distance[$v]) {
            throw new Exception("Negative cycle detected");
        }
    }

    return $distance;
}
```
*See: Chapter 22 (Dijkstra's Algorithm - comparison)*

**Cache Hit/Miss**: When requested data is (hit) or isn't (miss) found in cache. High hit rate improves performance.

```php
class SimpleCache {
    private array $cache = [];
    private int $hits = 0;
    private int $misses = 0;

    public function get(string $key, callable $loader) {
        if (isset($this->cache[$key])) {
            $this->hits++;
            return $this->cache[$key];  // Cache hit
        }

        $this->misses++;
        $value = $loader();  // Cache miss - load data
        $this->cache[$key] = $value;
        return $value;
    }

    public function getHitRate(): float {
        $total = $this->hits + $this->misses;
        return $total > 0 ? $this->hits / $total : 0;
    }
}
```
*See: Chapter 29 (Performance Optimization), Appendix B (PHP Performance Tips)*

**Fibonacci Sequence** (pronunciation: fib-oh-NAH-chee): Sequence where each number is sum of two preceding: 0, 1, 1, 2, 3, 5, 8, 13...

```php
// Naive recursive: O(2^n) - exponential!
function fibNaive($n) {
    if ($n <= 1) return $n;
    return fibNaive($n - 1) + fibNaive($n - 2);
}

// Dynamic programming: O(n) - linear
function fibDP($n) {
    if ($n <= 1) return $n;

    $dp = [0, 1];
    for ($i = 2; $i <= $n; $i++) {
        $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
    }
    return $dp[$n];
}

// Space-optimized: O(n) time, O(1) space
function fibOptimized($n) {
    if ($n <= 1) return $n;

    $prev2 = 0;
    $prev1 = 1;

    for ($i = 2; $i <= $n; $i++) {
        $current = $prev1 + $prev2;
        $prev2 = $prev1;
        $prev1 = $current;
    }

    return $prev1;
}
```
*See: Chapter 25 (Dynamic Programming)*

**Hashing**: Process of mapping data to fixed-size values (hash codes) for fast lookup.

```php
// Hash function example
function simpleHash(string $key, int $tableSize): int {
    $hash = 0;
    for ($i = 0; $i < strlen($key); $i++) {
        $hash = ($hash * 31 + ord($key[$i])) % $tableSize;
    }
    return $hash;
}

// PHP built-in hash functions
$hash1 = crc32($data);           // Fast, 32-bit
$hash2 = md5($data);             // 128-bit
$hash3 = sha256($data);          // 256-bit (cryptographic)

// Collision resolution: chaining
$hashTable = array_fill(0, 10, []);
$index = simpleHash('key', 10);
$hashTable[$index][] = ['key' => 'key', 'value' => 'value'];
```
*See: Chapter 6 (Hash Tables)*

**Iteration vs Recursion**: Two approaches to repetition. Iteration uses loops; recursion uses function calls.

```php
// Iteration: explicit loop
function sumIterative(array $arr): int {
    $sum = 0;
    foreach ($arr as $value) {
        $sum += $value;
    }
    return $sum;
}

// Recursion: function calls itself
function sumRecursive(array $arr): int {
    if (empty($arr)) {
        return 0;  // Base case
    }
    return array_shift($arr) + sumRecursive($arr);  // Recursive case
}

// Tail recursion (can be optimized)
function sumTailRecursive(array $arr, int $acc = 0): int {
    if (empty($arr)) {
        return $acc;
    }
    return sumTailRecursive(array_slice($arr, 1), $acc + $arr[0]);
}
```
*See: Chapter 24 (Recursion and Backtracking)*

**Load Factor**: Ratio of elements to capacity in hash table. Affects performance of hash operations.

```php
class HashTable {
    private array $buckets;
    private int $size = 0;
    private int $capacity;
    private float $maxLoadFactor = 0.75;

    public function __construct(int $capacity = 16) {
        $this->capacity = $capacity;
        $this->buckets = array_fill(0, $capacity, []);
    }

    public function getLoadFactor(): float {
        return $this->size / $this->capacity;
    }

    public function insert($key, $value): void {
        // ... insert logic ...
        $this->size++;

        // Resize if load factor too high
        if ($this->getLoadFactor() > $this->maxLoadFactor) {
            $this->resize();
        }
    }

    private function resize(): void {
        $oldBuckets = $this->buckets;
        $this->capacity *= 2;
        $this->buckets = array_fill(0, $this->capacity, []);
        $this->size = 0;

        // Rehash all entries
        foreach ($oldBuckets as $bucket) {
            foreach ($bucket as $entry) {
                $this->insert($entry['key'], $entry['value']);
            }
        }
    }
}
```
*See: Chapter 6 (Hash Tables)*

**Memoization** (pronunciation: mem-oh-ize-AY-shun, note: NOT "memorization"): Storing results of expensive function calls to avoid recalculation.

```php
// Without memoization: O(2^n) - recalculates many times
function fibSlow($n) {
    if ($n <= 1) return $n;
    return fibSlow($n - 1) + fibSlow($n - 2);
}

// With memoization: O(n) - calculates each value once
function fibMemo($n, &$memo = []) {
    if ($n <= 1) return $n;

    if (!isset($memo[$n])) {
        $memo[$n] = fibMemo($n - 1, $memo) + fibMemo($n - 2, $memo);
    }

    return $memo[$n];
}

// Generic memoization wrapper
function memoize(callable $fn): callable {
    $cache = [];

    return function(...$args) use ($fn, &$cache) {
        $key = serialize($args);

        if (!isset($cache[$key])) {
            $cache[$key] = $fn(...$args);
        }

        return $cache[$key];
    };
}

// Usage
$fibMemoized = memoize(fn($n) => $n <= 1 ? $n : fibMemoized($n-1) + fibMemoized($n-2));
```
*See: Chapter 25 (Dynamic Programming)*

**Pruning** (pronounced PROO-ning): Eliminating branches in search tree that cannot lead to solution, improving efficiency.

```php
// Backtracking with pruning
function solveSudoku(&$board) {
    for ($row = 0; $row < 9; $row++) {
        for ($col = 0; $col < 9; $col++) {
            if ($board[$row][$col] === 0) {
                for ($num = 1; $num <= 9; $num++) {
                    // PRUNING: Skip numbers that violate constraints
                    if (!isValid($board, $row, $col, $num)) {
                        continue;  // Prune this branch
                    }

                    $board[$row][$col] = $num;

                    if (solveSudoku($board)) {
                        return true;
                    }

                    $board[$row][$col] = 0;  // Backtrack
                }
                return false;
            }
        }
    }
    return true;
}

function isValid($board, $row, $col, $num): bool {
    // Check row, column, and 3x3 box
    // Returns false to prune invalid branches
    // ... validation logic ...
}
```
*See: Chapter 24 (Recursion and Backtracking)*

**Sentinel Value**: Special value marking end of data structure or signaling special condition.

```php
// Sentinel in linked list (simplifies edge cases)
class LinkedList {
    private Node $sentinel;  // Dummy head node

    public function __construct() {
        $this->sentinel = new Node(null);  // Sentinel has no value
    }

    public function insert($value): void {
        $newNode = new Node($value);
        $newNode->next = $this->sentinel->next;
        $this->sentinel->next = $newNode;
        // No need to check if list is empty!
    }

    public function search($value): ?Node {
        $current = $this->sentinel->next;  // Start after sentinel

        while ($current !== null) {
            if ($current->value === $value) {
                return $current;
            }
            $current = $current->next;
        }

        return null;
    }
}

// Sentinel in array processing
function processUntilSentinel(array $arr): void {
    $sentinel = -1;
    $arr[] = $sentinel;  // Add sentinel at end

    $i = 0;
    while ($arr[$i] !== $sentinel) {
        process($arr[$i]);
        $i++;
        // No need to check $i < count($arr) each iteration!
    }
}
```
*See: Chapter 15 (Linked Lists)*

**Stability (Sorting)**: Sorting algorithm is stable if equal elements maintain their relative order.

```php
// Example: Sorting students by grade
$students = [
    ['name' => 'Alice', 'grade' => 85],
    ['name' => 'Bob', 'grade' => 90],
    ['name' => 'Charlie', 'grade' => 85],  // Same grade as Alice
    ['name' => 'David', 'grade' => 90]     // Same grade as Bob
];

// STABLE sort: Alice stays before Charlie, Bob before David
usort($students, fn($a, $b) => $a['grade'] <=> $b['grade']);
// Result: Alice(85), Charlie(85), Bob(90), David(90)

// UNSTABLE sort might produce: Charlie(85), Alice(85), David(90), Bob(90)

// Stable sorts: Merge Sort, Insertion Sort, Bubble Sort
// Unstable sorts: Quick Sort, Heap Sort, Selection Sort
```
*See: Chapter 10 (Merge Sort), Chapter 13 (Advanced Sorting)*

**Time-Space Tradeoff**: Using more memory to save time, or vice versa.

```php
// More time, less space: Compute each time
function isPrime($n): bool {
    if ($n < 2) return false;
    for ($i = 2; $i <= sqrt($n); $i++) {
        if ($n % $i === 0) return false;
    }
    return true;
}

// More space, less time: Precompute and cache
class PrimeChecker {
    private static array $cache = [];

    public static function isPrime($n): bool {
        if (!isset(self::$cache[$n])) {
            self::$cache[$n] = self::computeIsPrime($n);
        }
        return self::$cache[$n];
    }

    private static function computeIsPrime($n): bool {
        if ($n < 2) return false;
        for ($i = 2; $i <= sqrt($n); $i++) {
            if ($n % $i === 0) return false;
        }
        return true;
    }
}

// Extreme tradeoff: Sieve of Eratosthenes
// Uses O(n) space to find all primes up to n in O(n log log n) time
function sieveOfEratosthenes($n): array {
    $isPrime = array_fill(0, $n + 1, true);
    $isPrime[0] = $isPrime[1] = false;

    for ($i = 2; $i * $i <= $n; $i++) {
        if ($isPrime[$i]) {
            for ($j = $i * $i; $j <= $n; $j += $i) {
                $isPrime[$j] = false;
            }
        }
    }

    return array_keys(array_filter($isPrime));
}
```
*See: Chapter 3 (Space Complexity), Chapter 29 (Performance Optimization)*

## Cross-Reference Index

### By Complexity Class
- **O(1) operations**: Hash Table, Array Access, Stack Push/Pop
- **O(log n) operations**: Binary Search, Balanced Tree Operations, Heap Operations
- **O(n) operations**: Linear Search, Array Traversal, Hash Table Build
- **O(n log n) operations**: Merge Sort, Quick Sort (avg), Heap Sort
- **O(n²) operations**: Bubble Sort, Selection Sort, Insertion Sort (worst)

### By Data Structure
- **Arrays**: Chapter 4, Appendix A
- **Linked Lists**: Chapter 15
- **Stacks**: Chapter 5
- **Queues**: Chapter 5
- **Hash Tables**: Chapter 6
- **Trees**: Chapters 16-18
- **Graphs**: Chapters 19-22
- **Heaps**: Chapter 14

### By Algorithm Type
- **Sorting**: Chapters 9-13, Appendix A
- **Searching**: Chapters 7-8
- **Graph Algorithms**: Chapters 19-22
- **String Algorithms**: Chapter 23
- **Dynamic Programming**: Chapter 25
- **Greedy Algorithms**: Chapter 26
- **Backtracking**: Chapter 24

### By Performance Topic
- **Time Complexity**: Chapter 2, Appendix A
- **Space Complexity**: Chapter 3
- **Optimization**: Chapter 29, Appendix B
- **Profiling**: Appendix B

## See Also

- **Appendix A**: Complexity Cheat Sheet - Detailed complexity tables and practical examples
- **Appendix B**: PHP Performance Tips - Optimization techniques and best practices
- **Appendix D**: Further Reading - Books and resources for deeper understanding
- **Chapter 1**: Introduction to Algorithms - Algorithm fundamentals
- **Chapter 2**: Time Complexity Analysis - In-depth Big O analysis
- **Chapter 3**: Space Complexity - Memory usage patterns
- **Chapter 29**: Performance Optimization - Real-world optimization strategies
