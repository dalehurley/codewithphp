# Appendix C: Glossary

Comprehensive definitions of algorithm and data structure terminology used throughout this series. Terms include pronunciation guides where helpful and cross-references to relevant chapters.

## A

**Abstract Data Type (ADT)**: A mathematical model for data types defined by behavior (operations) rather than implementation. Examples: Stack, Queue, List.

```php
# filename: adt-stack-example.php
<?php

declare(strict_types=1);

// ADT: Stack (behavior defined, implementation hidden)
interface StackInterface {
    public function push(mixed $item): void;
    public function pop(): mixed;
    public function top(): mixed;
    public function isEmpty(): bool;
}

// Implementation can vary (array, linked list, etc.)
class ArrayStack implements StackInterface {
    private array $items = [];

    public function push(mixed $item): void {
        $this->items[] = $item;
    }

    public function pop(): mixed {
        return array_pop($this->items);
    }

    public function top(): mixed {
        return end($this->items);
    }

    public function isEmpty(): bool {
        return empty($this->items);
    }
}
```
*See: Chapter 17 (Stacks and Queues)*

**Adjacency List** (pronunciation: ad-JAY-sen-see): Graph representation using arrays/lists to store neighbors of each vertex. Space: O(V + E).

```php
# filename: adjacency-list-example.php
<?php

declare(strict_types=1);

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
$hasEdge = in_array('B', $graph['A'], true);
```
*See: Chapter 21 (Graph Representations)*

**Adjacency Matrix** (pronunciation: ad-JAY-sen-see MAY-triks): Graph representation using 2D array where matrix[i][j] indicates edge from vertex i to j. Space: O(V²).

```php
# filename: adjacency-matrix-example.php
<?php

declare(strict_types=1);

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
*See: Chapter 21 (Graph Representations)*

**Aho-Corasick Algorithm** (pronunciation: AH-ho KOR-uh-sik): Efficient multi-pattern string matching algorithm that searches for multiple patterns simultaneously in O(n + m + z) time, where n is text length, m is total pattern length, and z is number of matches. Uses a trie with failure links.

```php
# filename: aho-corasick-example.php
<?php

declare(strict_types=1);

class AhoCorasickNode {
    public array $children = [];
    public ?AhoCorasickNode $failure = null;
    public array $output = [];
}

class AhoCorasick {
    private AhoCorasickNode $root;

    public function __construct() {
        $this->root = new AhoCorasickNode();
    }

    public function search(string $text): array {
        $matches = [];
        $current = $this->root;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            // Follow failure links if needed
            while ($current !== $this->root && !isset($current->children[$char])) {
                $current = $current->failure ?? $this->root;
            }

            if (isset($current->children[$char])) {
                $current = $current->children[$char];
            }

            // Collect matches
            foreach ($current->output as $patternId) {
                $matches[] = ['position' => $i, 'pattern' => $patternId];
            }
        }

        return $matches;
    }
}
```
*See: Chapter 33 (String Algorithms Deep Dive)*

**A* Algorithm** (pronunciation: A-star): Informed search algorithm that finds shortest path using both actual distance from start and estimated distance to goal (heuristic). More efficient than Dijkstra's for pathfinding. Time: O(b^d) where b is branching factor, d is depth.

```php
# filename: a-star-example.php
<?php

declare(strict_types=1);

class AStarNode {
    public int $x;
    public int $y;
    public float $g = 0.0;  // Cost from start
    public float $h = 0.0;  // Heuristic to goal
    public float $f = 0.0;  // Total: g + h
    public ?AStarNode $parent = null;

    public function __construct(int $x, int $y) {
        $this->x = $x;
        $this->y = $y;
    }
}

function heuristic(AStarNode $a, AStarNode $b): float {
    // Manhattan distance
    return abs($a->x - $b->x) + abs($a->y - $b->y);
}

function aStar(array $grid, AStarNode $start, AStarNode $goal): ?array {
    $openSet = [$start];
    $closedSet = [];
    
    while (!empty($openSet)) {
        // Find node with lowest f score
        $current = $openSet[0];
        foreach ($openSet as $node) {
            if ($node->f < $current->f) {
                $current = $node;
            }
        }
        
        if ($current->x === $goal->x && $current->y === $goal->y) {
            // Reconstruct path
            $path = [];
            while ($current !== null) {
                $path[] = $current;
                $current = $current->parent;
            }
            return array_reverse($path);
        }
        
        // Move current from open to closed
        $openSet = array_filter($openSet, fn($n) => $n !== $current);
        $closedSet[] = $current;
        
        // Check neighbors...
        // (Implementation continues with neighbor evaluation)
    }
    
    return null;  // No path found
}
```
*See: Chapter 24 (Dijkstra's Shortest Path - comparison)*

**Algorithm** (pronunciation: AL-go-rith-em): A finite sequence of well-defined instructions to solve a problem or perform a computation.

*See: Chapter 1 (Introduction to Algorithms)*

**Amortized Time Complexity** (pronunciation: AM-or-tized): Average time per operation over a sequence of operations, accounting for expensive occasional operations.

```php
# filename: amortized-complexity-example.php
<?php

declare(strict_types=1);

// Dynamic array (PHP array): amortized O(1) append
$arr = [];
for ($i = 0; $i < 1000; $i++) {
    $arr[] = $i;  // Usually O(1), occasionally O(n) when resizing
                  // Amortized: O(1) average over all operations
}
```
*See: Chapter 2 (Time Complexity Analysis)*

**AES (Advanced Encryption Standard)**: Symmetric encryption algorithm adopted as U.S. government standard. Key sizes: 128, 192, or 256 bits. Block size: 128 bits. Modes: CBC, GCM (authenticated), CTR. Fast and secure for bulk data encryption.

```php
# filename: aes-example.php
<?php

declare(strict_types=1);

// AES-256-GCM (authenticated encryption)
$key = random_bytes(32);  // 256-bit key
$iv = random_bytes(12);    // 96-bit IV for GCM
$plaintext = "Secret message";

// Encrypt
$ciphertext = openssl_encrypt(
    $plaintext,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag  // Authentication tag
);

// Decrypt (verifies authenticity)
$decrypted = openssl_decrypt(
    $ciphertext,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag
);

// AES-256-CBC (unauthenticated - use GCM instead)
$ciphertext = openssl_encrypt(
    $plaintext,
    'aes-256-cbc',
    $key,
    OPENSSL_RAW_DATA,
    $iv
);
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Argon2**: Modern password hashing algorithm designed to resist GPU attacks and side-channel attacks. Winner of Password Hashing Competition. Variants: Argon2i (data-independent), Argon2d (data-dependent), Argon2id (hybrid, recommended).

```php
# filename: argon2-example.php
<?php

declare(strict_types=1);

// Hash password with Argon2id (recommended)
$password = 'MySecurePassword123!';
$hash = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,  // 64 MB
    'time_cost' => 4,         // 4 iterations
    'threads' => 3            // 3 threads
]);

// Verify password
$isValid = password_verify($password, $hash);

// Check if rehashing needed (for security upgrades)
if (password_needs_rehash($hash, PASSWORD_ARGON2ID, [
    'memory_cost' => 131072,  // Upgraded to 128 MB
    'time_cost' => 4,
    'threads' => 3
])) {
    $newHash = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 131072,
        'time_cost' => 4,
        'threads' => 3
    ]);
}
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Array**: Contiguous block of memory storing elements of the same type, accessible by index in O(1) time.

```php
# filename: array-operations-example.php
<?php

declare(strict_types=1);

// PHP array (numeric keys)
$numbers = [10, 20, 30, 40, 50];

// O(1) random access
$value = $numbers[2];  // 30

// O(1) append
$numbers[] = 60;

// O(n) insert at beginning
array_unshift($numbers, 0);
```
*See: Chapter 15 (Arrays and Dynamic Arrays)*

**Asymmetric Encryption**: Encryption using different keys for encryption (public key) and decryption (private key). Solves key distribution problem. Examples: RSA, ECC. Slower than symmetric encryption.

```php
# filename: asymmetric-encryption-example.php
<?php

declare(strict_types=1);

// Generate RSA key pair
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$keyPair = openssl_pkey_new($config);
openssl_pkey_export($keyPair, $privateKey);
$publicKey = openssl_pkey_get_details($keyPair)["key"];

// Encrypt with public key (anyone can encrypt)
$data = "Secret message";
openssl_public_encrypt($data, $encrypted, $publicKey);

// Decrypt with private key (only holder can decrypt)
openssl_private_decrypt($encrypted, $decrypted, $privateKey);
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Asymptotic Analysis** (pronunciation: ace-im-TOT-ik): Study of algorithm behavior as input size approaches infinity using Big O, Omega, and Theta notation.

*See: Chapter 2 (Time Complexity Analysis), Appendix A (Complexity Cheat Sheet)*

**AVL Tree** (pronunciation: A-V-L tree, named after inventors Adelson-Velsky and Landis): Self-balancing binary search tree where height difference between left and right subtrees is at most 1.

```php
# filename: avl-tree-example.php
<?php

declare(strict_types=1);

// AVL Tree: maintains balance factor of -1, 0, or 1
class AVLNode {
    public mixed $value;
    public ?AVLNode $left = null;
    public ?AVLNode $right = null;
    public int $height = 1;

    public function __construct(mixed $value) {
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
*See: Chapter 20 (Balanced Trees - AVL and Red-Black)*

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

**Boyer-Moore Algorithm** (pronunciation: BOY-er moor): Efficient string matching algorithm that skips characters by comparing pattern from right to left and using bad character and good suffix rules. Often faster than KMP in practice. Average time: O(n/m), worst: O(nm).

```php
# filename: boyer-moore-example.php
<?php

declare(strict_types=1);

function boyerMoore(string $text, string $pattern): array {
    $matches = [];
    $n = strlen($text);
    $m = strlen($pattern);
    
    // Build bad character table
    $badChar = [];
    for ($i = 0; $i < $m; $i++) {
        $badChar[$pattern[$i]] = $i;
    }
    
    $s = 0;  // Shift of pattern with respect to text
    while ($s <= ($n - $m)) {
        $j = $m - 1;
        
        // Match pattern from right to left
        while ($j >= 0 && $pattern[$j] === $text[$s + $j]) {
            $j--;
        }
        
        if ($j < 0) {
            // Pattern found
            $matches[] = $s;
            $s += ($s + $m < $n) ? $m - ($badChar[$text[$s + $m]] ?? -1) : 1;
        } else {
            // Shift pattern based on bad character rule
            $s += max(1, $j - ($badChar[$text[$s + $j]] ?? -1));
        }
    }
    
    return $matches;
}
```
*See: Chapter 14 (String Search Algorithms)*

**Blackfire**: PHP profiling tool that provides detailed performance analysis, memory usage, and optimization recommendations for production applications.

*See: Chapter 29 (Performance Optimization)*

## C

**Cache**: Fast storage layer that stores frequently accessed data to improve access speed.

**Caching**: Strategy of storing computed results to avoid redundant calculations.

**Circular Queue**: Queue implementation where end connects to beginning, forming a circle.

**Collision**: Situation in hash tables where two different keys map to the same hash value.

**Concurrency**: Ability of a system to handle multiple tasks simultaneously, improving performance for I/O-bound and CPU-intensive operations.

**Coroutine** (pronunciation: KOR-oh-teen): Lightweight concurrent execution unit that can be paused and resumed, allowing cooperative multitasking without threads.

```php
# filename: coroutine-example.php
<?php

declare(strict_types=1);

// Generator-based coroutine pattern
function asyncTask(): Generator {
    yield 'Task 1 started';
    yield 'Task 1 processing';
    yield 'Task 1 completed';
}

$coroutine = asyncTask();
foreach ($coroutine as $value) {
    echo $value . "\n";
}
```
*See: Chapter 31 (Concurrent Algorithms)*

**Count-Min Sketch**: Probabilistic data structure for estimating frequency of elements in a stream using minimal memory. Provides approximate counts with controlled error bounds.

```php
# filename: count-min-sketch-example.php
<?php

declare(strict_types=1);

class CountMinSketch {
    private array $table;
    private int $width;
    private int $depth;
    private array $hashFunctions;

    public function __construct(int $width, int $depth) {
        $this->width = $width;
        $this->depth = $depth;
        $this->table = array_fill(0, $depth, array_fill(0, $width, 0));
        $this->hashFunctions = $this->generateHashFunctions();
    }

    public function increment(string $item): void {
        for ($i = 0; $i < $this->depth; $i++) {
            $hash = $this->hashFunctions[$i]($item) % $this->width;
            $this->table[$i][$hash]++;
        }
    }

    public function estimate(string $item): int {
        $min = PHP_INT_MAX;
        for ($i = 0; $i < $this->depth; $i++) {
            $hash = $this->hashFunctions[$i]($item) % $this->width;
            $min = min($min, $this->table[$i][$hash]);
        }
        return $min;
    }

    private function generateHashFunctions(): array {
        // Simplified hash function generation
        return array_map(fn($seed) => fn($x) => crc32($x . $seed), range(1, $this->depth));
    }
}
```
*See: Chapter 32 (Probabilistic Algorithms)*

**Cocktail Shaker Sort**: Bidirectional variant of bubble sort that sorts in both directions, improving performance on nearly-sorted arrays. Time: O(n²) worst case, O(n) best case.

```php
# filename: cocktail-shaker-sort-example.php
<?php

declare(strict_types=1);

function cocktailShakerSort(array $arr): array {
    $n = count($arr);
    $left = 0;
    $right = $n - 1;
    $swapped = true;

    while ($swapped) {
        $swapped = false;

        // Forward pass
        for ($i = $left; $i < $right; $i++) {
            if ($arr[$i] > $arr[$i + 1]) {
                [$arr[$i], $arr[$i + 1]] = [$arr[$i + 1], $arr[$i]];
                $swapped = true;
            }
        }
        $right--;

        if (!$swapped) break;

        // Backward pass
        for ($i = $right; $i > $left; $i--) {
            if ($arr[$i] < $arr[$i - 1]) {
                [$arr[$i], $arr[$i - 1]] = [$arr[$i - 1], $arr[$i]];
                $swapped = true;
            }
        }
        $left++;
    }

    return $arr;
}
```
*See: Chapter 5 (Bubble Sort & Selection Sort)*

**Collaborative Filtering**: Recommendation algorithm that predicts user preferences by finding similar users or items. Used in e-commerce, streaming services, and social platforms.

```php
# filename: collaborative-filtering-example.php
<?php

declare(strict_types=1);

class CollaborativeFilter {
    private array $userRatings;

    public function __construct(array $userRatings) {
        $this->userRatings = $userRatings;
    }

    // User-based collaborative filtering
    public function findSimilarUsers(int $userId): array {
        $similarities = [];
        $userRatings = $this->userRatings[$userId] ?? [];

        foreach ($this->userRatings as $otherId => $otherRatings) {
            if ($otherId === $userId) continue;
            $similarities[$otherId] = $this->cosineSimilarity($userRatings, $otherRatings);
        }

        arsort($similarities);
        return $similarities;
    }

    private function cosineSimilarity(array $a, array $b): float {
        $common = array_intersect_key($a, $b);
        if (empty($common)) return 0.0;

        $dotProduct = array_sum(array_map(fn($k) => $a[$k] * $b[$k], array_keys($common)));
        $normA = sqrt(array_sum(array_map(fn($v) => $v ** 2, $a)));
        $normB = sqrt(array_sum(array_map(fn($v) => $v ** 2, $b)));

        return $dotProduct / ($normA * $normB);
    }
}
```
*See: Chapter 30 (Real-World Case Studies)*

**Comb Sort**: Improved bubble sort variant that uses gap-based comparisons, reducing the "turtle problem" of bubble sort. Time: O(n²) worst case, O(n log n) average.

```php
# filename: comb-sort-example.php
<?php

declare(strict_types=1);

function combSort(array $arr): array {
    $n = count($arr);
    $gap = $n;
    $shrink = 1.3;
    $swapped = true;

    while ($gap > 1 || $swapped) {
        $gap = max(1, (int)($gap / $shrink));
        $swapped = false;

        for ($i = 0; $i + $gap < $n; $i++) {
            if ($arr[$i] > $arr[$i + $gap]) {
                [$arr[$i], $arr[$i + $gap]] = [$arr[$i + $gap], $arr[$i]];
                $swapped = true;
            }
        }
    }

    return $arr;
}
```
*See: Chapter 5 (Bubble Sort & Selection Sort)*

**Comparison Sort**: Sorting algorithm that works by comparing elements. Lower bound: O(n log n).

**Complete Binary Tree**: Binary tree where all levels are fully filled except possibly the last, which is filled left to right.

**Complexity**: Measure of resources (time, space) required by an algorithm as a function of input size.

**Connected Component**: Maximal set of vertices in a graph where each vertex is reachable from every other vertex.

**Constant Time**: O(1) complexity - execution time doesn't depend on input size.

**Convex Hull**: Smallest convex polygon containing all points in a set.

**Coroutine** (pronunciation: KOR-oh-teen): Lightweight concurrent execution unit that can be paused and resumed, allowing cooperative multitasking without threads.

```php
# filename: coroutine-example.php
<?php

declare(strict_types=1);

// Generator-based coroutine pattern
function asyncTask(): Generator {
    yield 'Task 1 started';
    yield 'Task 1 processing';
    yield 'Task 1 completed';
}

$coroutine = asyncTask();
foreach ($coroutine as $value) {
    echo $value . "\n";
}
```
*See: Chapter 31 (Concurrent Algorithms)*

**Cycle**: Path in a graph that starts and ends at the same vertex.

## D

**DAG (Directed Acyclic Graph)**: Directed graph with no cycles, used in dependency resolution and topological sorting.

**Diffie-Hellman Key Exchange**: Cryptographic protocol allowing two parties to establish shared secret over insecure channel without pre-sharing keys. Security based on discrete logarithm problem.

```php
# filename: diffie-hellman-example.php
<?php

declare(strict_types=1);

// Generate parameters (in practice, use well-known parameters)
$params = [
    'p' => '0xFFFFFFFFFFFFFFFFC90FDAA22168C234C4C6628B80DC1CD129024E088A67CC74020BBEA63B139B22514A08798E3404DDEF9519B3CD3A431B302B0A6DF25F14374FE1356D6D51C245E485B576625E7EC6F44C42E9A637ED6B0BFF5CB6F406B7EDEE386BFB5A899FA5AE9F24117C4B1FE649286651ECE45B3DC2007CB8A163BF0598DA48361C55D39A69163FA8FD24CF5F83655D23DCA3AD961C62F356208552BB9ED529077096966D670C354E4ABC9804F1746C08CA18217C32905E462E36CE3BE39E772C180E86039B2783A2EC07A28FB5C55DF06F4C52C9DE2BCBF6955817183995497CEA956AE515D2261898FA051015728E5A8AAAC42DAD33170D04507A33A85521ABDF1CBA64ECFB850458DBEF0A8AEA71575D060C7DB3970F85A6E1E4C7ABF5AE8CDB0933D71E8C94E04A25619DCEE3D2261AD2EE6BF12FFA06D98A0864D87602733EC86A64521F2B18177B200CBBE117577A615D6C770988C0BAD946E208E24FA074E5AB3143DB5BFCE0FD108E4B82D120A93AD2CAFFFFFFFFFFFFFFFF',
    'g' => '2',
];

// Alice generates key pair
$aliceKey = openssl_pkey_new([
    'p' => $params['p'],
    'g' => $params['g'],
    'private_key_bits' => 2048,
]);

// Bob generates key pair
$bobKey = openssl_pkey_new([
    'p' => $params['p'],
    'g' => $params['g'],
    'private_key_bits' => 2048,
]);

// Exchange public keys and compute shared secret
// (Simplified - actual implementation uses proper DH functions)
```
*See: Chapter 35 (Cryptographic Algorithms)*

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

**ETL (Extract, Transform, Load)**: Data pipeline process for extracting data from sources, transforming it (cleaning, aggregating, enriching), and loading into target systems. Common in data warehousing and analytics.

```php
# filename: etl-example.php
<?php

declare(strict_types=1);

class ETLPipeline {
    public function extract(array $sources): array {
        $data = [];
        foreach ($sources as $source) {
            $data[] = $this->readFromSource($source);
        }
        return $data;
    }

    public function transform(array $rawData): array {
        return array_map(function($record) {
            // Clean, validate, enrich
            return [
                'id' => (int)$record['id'],
                'name' => trim($record['name']),
                'email' => strtolower($record['email']),
                'created_at' => date('Y-m-d H:i:s', strtotime($record['date']))
            ];
        }, $rawData);
    }

    public function load(array $transformedData, string $destination): void {
        // Load into database, file, or API
        foreach ($transformedData as $record) {
            $this->writeToDestination($destination, $record);
        }
    }

    private function readFromSource(string $source): array { /* ... */ }
    private function writeToDestination(string $dest, array $data): void { /* ... */ }
}
```
*See: Chapter 30 (Real-World Case Studies), Chapter 36 (Stream Processing Algorithms)*

## F

**Factorial Time**: O(n!) complexity - extremely slow, usually only viable for n < 12.

**FIFO (First In, First Out)**: Queue ordering where first element added is first removed.

**Fibonacci Heap**: Advanced heap data structure with excellent amortized time for decrease-key operation.

**Floyd-Warshall Algorithm** (pronunciation: FLOYD WAR-shall): Dynamic programming algorithm that finds shortest paths between all pairs of vertices in weighted graph. Handles negative weights (but not negative cycles). Time: O(V³), Space: O(V²).

```php
# filename: floyd-warshall-example.php
<?php

declare(strict_types=1);

function floydWarshall(array $graph, int $V): array {
    $dist = $graph;  // Initialize with graph weights
    
    // Try all intermediate vertices
    for ($k = 0; $k < $V; $k++) {
        for ($i = 0; $i < $V; $i++) {
            for ($j = 0; $j < $V; $j++) {
                if ($dist[$i][$k] !== PHP_INT_MAX && 
                    $dist[$k][$j] !== PHP_INT_MAX &&
                    $dist[$i][$k] + $dist[$k][$j] < $dist[$i][$j]) {
                    $dist[$i][$j] = $dist[$i][$k] + $dist[$k][$j];
                }
            }
        }
    }
    
    return $dist;
}
```
*See: Chapter 24 (Dijkstra's Shortest Path - comparison for all-pairs)*

**Full Binary Tree**: Binary tree where every node has either 0 or 2 children.

## G

**Generator**: PHP feature that yields values one at a time instead of building full array in memory.

**Graph**: Data structure consisting of vertices (nodes) and edges (connections).

**Greedy Algorithm**: Makes locally optimal choice at each step, hoping to find global optimum.

**Greatest Common Divisor (GCD)**: Largest positive integer dividing two or more integers.

**Euclidean Algorithm**: Efficient algorithm for computing GCD using property that GCD(a,b) = GCD(b, a mod b). Time: O(log min(a,b)).

```php
# filename: euclidean-algorithm-example.php
<?php

declare(strict_types=1);

function gcd(int $a, int $b): int {
    while ($b !== 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return abs($a);
}

// Recursive version
function gcdRecursive(int $a, int $b): int {
    if ($b === 0) return abs($a);
    return gcdRecursive($b, $a % $b);
}
```
*See: Chapter 4 (Problem-Solving Strategies)*

## H

**Hash Collision**: When two different keys produce the same hash value.

**Hash Function**: Function mapping keys to hash values (array indices) for quick access.

**Hash Table**: Data structure providing O(1) average-case insertion, deletion, and lookup using hashing.

**HMAC (Hash-based Message Authentication Code)**: Cryptographic authentication mechanism that uses a secret key combined with a hash function to verify both data integrity and authenticity.

```php
# filename: hmac-example.php
<?php

declare(strict_types=1);

// Generate HMAC
$message = "Important data";
$secretKey = "my-secret-key";
$hmac = hash_hmac('sha256', $message, $secretKey);

// Verify HMAC
$receivedHmac = "received-hmac-value";
$isValid = hash_equals($hmac, $receivedHmac);
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Heap**: Complete binary tree satisfying heap property (min-heap: parent ≤ children, max-heap: parent ≥ children).

**Heapify**: Process of converting array into valid heap structure.

**Height (Tree)**: Maximum distance from root to any leaf node.

**Heuristic**: Rule of thumb or educated guess to find good (not necessarily optimal) solutions quickly.

**HyperLogLog**: Probabilistic algorithm for counting distinct elements using minimal memory.

## I

**In-Place Algorithm**: Algorithm that modifies input directly using O(1) extra space.

**Insertion Sort**: Simple sorting algorithm building sorted array one element at a time. Good for small/nearly sorted arrays.

**In-Order Traversal**: BST traversal visiting left subtree, then root, then right subtree (produces sorted order).

**Initialization Vector (IV)**: Random value used with encryption keys to ensure same plaintext produces different ciphertext. Must be unique for each encryption with the same key.

```php
# filename: iv-example.php
<?php

declare(strict_types=1);

// Generate random IV for encryption
$iv = random_bytes(16);  // 16 bytes for AES-128

// Use IV in encryption (must be unique!)
$ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, 0, $iv);

// IV must be stored/transmitted with ciphertext (not secret)
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Invariant**: Property that remains true throughout algorithm execution, used to prove correctness. Common invariants: loop invariants, class invariants, data structure invariants.

```php
# filename: invariant-example.php
<?php

declare(strict_types=1);

// Loop invariant: elements [0..i-1] are sorted
function insertionSort(array $arr): array {
    for ($i = 1; $i < count($arr); $i++) {
        $key = $arr[$i];
        $j = $i - 1;
        
        // INVARIANT: arr[0..j] is sorted
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $key;
        // INVARIANT: arr[0..i] is now sorted
    }
    return $arr;
}
```
*See: Chapter 8 (Heap Sort - heap invariants), Chapter 20 (Balanced Trees - tree invariants)*

**Interval DP**: Dynamic programming technique for problems involving intervals or ranges, where subproblems are defined over contiguous intervals. Common in problems like matrix chain multiplication, optimal binary search tree construction.

```php
# filename: interval-dp-example.php
<?php

declare(strict_types=1);

// Example: Matrix Chain Multiplication
function matrixChainOrder(array $dims): int {
    $n = count($dims) - 1;
    $dp = array_fill(0, $n, array_fill(0, $n, 0));

    // Length of chain
    for ($len = 2; $len <= $n; $len++) {
        for ($i = 0; $i < $n - $len + 1; $i++) {
            $j = $i + $len - 1;
            $dp[$i][$j] = PHP_INT_MAX;

            // Try all possible splits
            for ($k = $i; $k < $j; $k++) {
                $cost = $dp[$i][$k] + $dp[$k + 1][$j] + 
                        $dims[$i] * $dims[$k + 1] * $dims[$j + 1];
                $dp[$i][$j] = min($dp[$i][$j], $cost);
            }
        }
    }

    return $dp[0][$n - 1];
}
```
*See: Chapter 26 (Advanced Dynamic Programming)*

## J

**JIT (Just-In-Time Compilation)**: Compilation technique that translates code to machine code at runtime rather than ahead of time. PHP 8+ includes JIT compiler for improved performance of CPU-intensive code.

*See: Chapter 29 (Performance Optimization)*

## K

**K-way Merge**: Merging K sorted arrays/lists into one sorted result.

**KMP (Knuth-Morris-Pratt)**: Efficient string matching algorithm using preprocessing to avoid re-scanning. Time: O(n + m).

**Kruskal's Algorithm**: Greedy algorithm for finding Minimum Spanning Tree (MST) by sorting edges and adding them if they don't form cycles. Uses Union-Find for cycle detection. Time: O(E log E).

```php
# filename: kruskal-example.php
<?php

declare(strict_types=1);

function kruskalMST(array $edges, int $V): array {
    // Sort edges by weight
    usort($edges, fn($a, $b) => $a[2] <=> $b[2]);
    
    $mst = [];
    $uf = new UnionFind($V);
    
    foreach ($edges as [$u, $v, $weight]) {
        if ($uf->find($u) !== $uf->find($v)) {
            $mst[] = [$u, $v, $weight];
            $uf->union($u, $v);
        }
    }
    
    return $mst;
}
```
*See: Chapter 21 (Graph Representations - edge list usage)*

**Leaky Bucket**: Rate limiting algorithm that processes requests at a fixed rate, discarding excess requests when bucket is full. Provides smooth, constant output rate.

```php
# filename: leaky-bucket-example.php
<?php

declare(strict_types=1);

class LeakyBucket {
    private float $capacity;
    private float $leakRate;
    private float $currentLevel = 0.0;
    private float $lastLeakTime;

    public function __construct(float $capacity, float $leakRate) {
        $this->capacity = $capacity;
        $this->leakRate = $leakRate;
        $this->lastLeakTime = microtime(true);
    }

    public function tryConsume(float $amount = 1.0): bool {
        $this->leak();
        
        if ($this->currentLevel + $amount <= $this->capacity) {
            $this->currentLevel += $amount;
            return true;
        }
        
        return false;  // Bucket full, request rejected
    }

    private function leak(): void {
        $now = microtime(true);
        $elapsed = $now - $this->lastLeakTime;
        $this->currentLevel = max(0, $this->currentLevel - ($this->leakRate * $elapsed));
        $this->lastLeakTime = $now;
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

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

**LCP Array (Longest Common Prefix)**: Array storing lengths of longest common prefixes between consecutive suffixes in suffix array. Enables efficient substring queries and pattern matching.

```php
# filename: lcp-array-example.php
<?php

declare(strict_types=1);

function buildLCP(string $text, array $suffixArray): array {
    $n = strlen($text);
    $lcp = array_fill(0, $n, 0);
    $rank = array_fill(0, $n, 0);
    
    // Build rank array
    for ($i = 0; $i < $n; $i++) {
        $rank[$suffixArray[$i]] = $i;
    }
    
    // Build LCP array
    $k = 0;
    for ($i = 0; $i < $n; $i++) {
        if ($rank[$i] === $n - 1) {
            $k = 0;
            continue;
        }
        
        $j = $suffixArray[$rank[$i] + 1];
        while ($i + $k < $n && $j + $k < $n && $text[$i + $k] === $text[$j + $k]) {
            $k++;
        }
        
        $lcp[$rank[$i]] = $k;
        if ($k > 0) $k--;
    }
    
    return $lcp;
}

// Usage: Find longest repeated substring
function longestRepeatedSubstring(string $text): string {
    $suffixArray = buildSuffixArray($text);
    $lcp = buildLCP($text, $suffixArray);
    
    $maxLen = 0;
    $maxIndex = 0;
    for ($i = 0; $i < count($lcp); $i++) {
        if ($lcp[$i] > $maxLen) {
            $maxLen = $lcp[$i];
            $maxIndex = $suffixArray[$i];
        }
    }
    
    return substr($text, $maxIndex, $maxLen);
}
```
*See: Chapter 33 (String Algorithms Deep Dive)*

**Manacher's Algorithm** (pronunciation: muh-NAH-cher): Linear-time algorithm for finding longest palindromic substring by maintaining center and right boundary to avoid redundant comparisons. Time: O(n).

```php
# filename: manacher-example.php
<?php

declare(strict_types=1);

function manacher(string $s): string {
    $n = strlen($s);
    $s2 = '#' . implode('#', str_split($s)) . '#';
    $n2 = strlen($s2);
    $p = array_fill(0, $n2, 0);
    $center = 0;
    $right = 0;
    $maxLen = 0;
    $centerIndex = 0;

    for ($i = 0; $i < $n2; $i++) {
        if ($i < $right) {
            $p[$i] = min($right - $i, $p[2 * $center - $i]);
        }

        while ($i + $p[$i] + 1 < $n2 && 
               $i - $p[$i] - 1 >= 0 &&
               $s2[$i + $p[$i] + 1] === $s2[$i - $p[$i] - 1]) {
            $p[$i]++;
        }

        if ($i + $p[$i] > $right) {
            $center = $i;
            $right = $i + $p[$i];
        }

        if ($p[$i] > $maxLen) {
            $maxLen = $p[$i];
            $centerIndex = $i;
        }
    }

    $start = ($centerIndex - $maxLen) / 2;
    return substr($s, (int)$start, $maxLen);
}
```
*See: Chapter 33 (String Algorithms Deep Dive)*

**Levenshtein Distance** (pronunciation: LEV-en-shtine): Minimum number of single-character edits (insertions, deletions, substitutions) needed to transform one string into another. Used in spell checkers, DNA analysis, and fuzzy string matching.

```php
# filename: levenshtein-distance-example.php
<?php

declare(strict_types=1);

function levenshteinDistance(string $s1, string $s2): int {
    $m = strlen($s1);
    $n = strlen($s2);
    $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));

    // Base cases
    for ($i = 0; $i <= $m; $i++) $dp[$i][0] = $i;
    for ($j = 0; $j <= $n; $j++) $dp[0][$j] = $j;

    // Fill DP table
    for ($i = 1; $i <= $m; $i++) {
        for ($j = 1; $j <= $n; $j++) {
            if ($s1[$i - 1] === $s2[$j - 1]) {
                $dp[$i][$j] = $dp[$i - 1][$j - 1];
            } else {
                $dp[$i][$j] = 1 + min(
                    $dp[$i - 1][$j],      // deletion
                    $dp[$i][$j - 1],      // insertion
                    $dp[$i - 1][$j - 1]   // substitution
                );
            }
        }
    }

    return $dp[$m][$n];
}
```
*See: Chapter 26 (Advanced Dynamic Programming)*

**LRU Cache (Least Recently Used)**: Cache eviction policy that removes least recently accessed items when cache is full. Maintains O(1) access time using hash map and doubly linked list.

```php
# filename: lru-cache-example.php
<?php

declare(strict_types=1);

class LRUCache {
    private int $capacity;
    private array $cache = [];
    private array $accessOrder = [];

    public function __construct(int $capacity) {
        $this->capacity = $capacity;
    }

    public function get(string $key): mixed {
        if (!isset($this->cache[$key])) {
            return null;
        }

        // Move to end (most recently used)
        unset($this->accessOrder[array_search($key, $this->accessOrder, true)]);
        $this->accessOrder[] = $key;

        return $this->cache[$key];
    }

    public function put(string $key, mixed $value): void {
        if (isset($this->cache[$key])) {
            $this->cache[$key] = $value;
            unset($this->accessOrder[array_search($key, $this->accessOrder, true)]);
        } else {
            if (count($this->cache) >= $this->capacity) {
                // Remove least recently used
                $lru = array_shift($this->accessOrder);
                unset($this->cache[$lru]);
            }
            $this->cache[$key] = $value;
        }
        $this->accessOrder[] = $key;
    }
}
```
*See: Chapter 27 (Caching & Memoization Strategies)*

## M

**Memoization**: Optimization technique storing function results to avoid recalculation with same inputs.

**Median-of-Three**: Pivot selection strategy for QuickSort that chooses median of first, middle, and last elements, reducing worst-case probability. Improves performance on sorted/reverse-sorted arrays.

```php
# filename: median-of-three-example.php
<?php

declare(strict_types=1);

function medianOfThree(array $arr, int $left, int $right): int {
    $mid = (int)(($left + $right) / 2);
    
    // Sort the three values
    $values = [$arr[$left], $arr[$mid], $arr[$right]];
    sort($values);
    
    // Return index of median value
    if ($values[1] === $arr[$left]) return $left;
    if ($values[1] === $arr[$mid]) return $mid;
    return $right;
}
```
*See: Chapter 7 (Quick Sort & Pivot Strategies)*

**Merge Sort**: Divide-and-conquer sorting algorithm with O(n log n) time complexity. Stable and predictable.

**Min Heap**: Heap where parent node value ≤ child node values (minimum at root).

**Max Heap**: Heap where parent node value ≥ child node values (maximum at root).

**Modular Arithmetic**: Arithmetic system where numbers wrap around after reaching modulus. Essential for hashing, cryptography, and avoiding integer overflow. Properties: (a + b) mod m = ((a mod m) + (b mod m)) mod m.

```php
# filename: modular-arithmetic-example.php
<?php

declare(strict_types=1);

// Used in Rabin-Karp hashing
function modularHash(string $str, int $mod = 1000000007): int {
    $hash = 0;
    $base = 256;
    
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ($hash * $base + ord($str[$i])) % $mod;
    }
    
    return $hash;
}

// Modular exponentiation: (a^b) mod m
function modPow(int $a, int $b, int $m): int {
    $result = 1;
    $a = $a % $m;
    
    while ($b > 0) {
        if ($b % 2 === 1) {
            $result = ($result * $a) % $m;
        }
        $b = (int)($b / 2);
        $a = ($a * $a) % $m;
    }
    
    return $result;
}
```
*See: Chapter 14 (String Search Algorithms - Rabin-Karp), Chapter 35 (Cryptographic Algorithms)*

**Monotonic Stack/Queue**: Data structure maintaining elements in monotonic (non-decreasing or non-increasing) order. Used for problems requiring next/previous greater/smaller element queries. Enables O(n) solutions for sliding window maximum/minimum.

```php
# filename: monotonic-stack-example.php
<?php

declare(strict_types=1);

// Find next greater element for each element
function nextGreaterElements(array $arr): array {
    $result = array_fill(0, count($arr), -1);
    $stack = [];  // Monotonic decreasing stack
    
    for ($i = 0; $i < count($arr); $i++) {
        while (!empty($stack) && $arr[$stack[count($stack) - 1]] < $arr[$i]) {
            $idx = array_pop($stack);
            $result[$idx] = $arr[$i];
        }
        $stack[] = $i;
    }
    
    return $result;
}
```
*See: Chapter 4 (Problem-Solving Strategies), Chapter 17 (Stacks & Queues), Chapter 26 (Advanced Dynamic Programming - sliding window DP)*

**Moving Average**: Average of elements in a sliding window, used for smoothing time-series data and trend analysis.

```php
# filename: moving-average-example.php
<?php

declare(strict_types=1);

class MovingAverage {
    private array $window = [];
    private int $windowSize;
    private float $sum = 0.0;

    public function __construct(int $windowSize) {
        $this->windowSize = $windowSize;
    }

    public function add(float $value): float {
        $this->window[] = $value;
        $this->sum += $value;

        if (count($this->window) > $this->windowSize) {
            $removed = array_shift($this->window);
            $this->sum -= $removed;
        }

        return $this->getAverage();
    }

    public function getAverage(): float {
        return count($this->window) > 0 ? $this->sum / count($this->window) : 0.0;
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**MST (Minimum Spanning Tree)**: Subset of edges connecting all vertices in weighted graph with minimum total weight.

## N

**NP-Complete**: Class of computational problems with no known polynomial-time solution.

**N-ary Tree**: Tree where each node can have at most N children.

**Nonce (Number Used Once)**: Unique value used once in cryptographic operations to prevent replay attacks. Must never be reused with the same key.

```php
# filename: nonce-example.php
<?php

declare(strict_types=1);

// Generate cryptographically secure nonce
$nonce = random_bytes(24);  // 24 bytes for XChaCha20

// Use nonce in encryption (must be unique!)
$ciphertext = sodium_crypto_secretbox($message, $nonce, $key);

// CRITICAL: Never reuse nonce with same key!
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Node**: Basic unit of data structures containing data and references/pointers.

**Null**: Reference that points to nothing, indicating absence of value or end of structure.

**B-tree**: Self-balancing tree data structure maintaining sorted data with multiple keys per node. Optimized for disk storage with high branching factor. Used in databases and file systems. Time: O(log n) for all operations.

```php
# filename: b-tree-example.php
<?php

declare(strict_types=1);

class BTreeNode {
    public array $keys = [];
    public array $children = [];
    public bool $isLeaf = false;
    public int $minDegree;  // Minimum degree (t)

    public function __construct(int $minDegree, bool $isLeaf) {
        $this->minDegree = $minDegree;
        $this->isLeaf = $isLeaf;
    }
}

class BTree {
    private ?BTreeNode $root = null;
    private int $minDegree;

    public function __construct(int $minDegree) {
        $this->minDegree = $minDegree;
        $this->root = new BTreeNode($minDegree, true);
    }

    public function search(int $key): ?BTreeNode {
        return $this->searchRecursive($this->root, $key);
    }

    private function searchRecursive(?BTreeNode $node, int $key): ?BTreeNode {
        $i = 0;
        while ($i < count($node->keys) && $key > $node->keys[$i]) {
            $i++;
        }

        if ($i < count($node->keys) && $node->keys[$i] === $key) {
            return $node;
        }

        if ($node->isLeaf) {
            return null;
        }

        return $this->searchRecursive($node->children[$i], $key);
    }
}
```
*See: Chapter 12 (Binary Search - database indexing), Chapter 20 (Balanced Trees)*

## O

**O(1)**: Constant time complexity - operations take fixed time regardless of input size.

**O(log n)**: Logarithmic complexity - doubles input, adds constant operations.

**O(n)**: Linear complexity - doubles input, doubles operations.

**O(n log n)**: Linearithmic complexity - optimal for comparison-based sorting.

**O(n²)**: Quadratic complexity - doubles input, quadruples operations.

**OPcache**: PHP opcode cache that stores precompiled script bytecode in memory, eliminating need to load and parse PHP files on each request. Significantly improves performance.

*See: Chapter 29 (Performance Optimization)*

**Open Addressing**: Hash table collision resolution method that stores colliding elements in alternative slots within the table (linear probing, quadratic probing, double hashing) rather than chaining.

```php
# filename: open-addressing-example.php
<?php

declare(strict_types=1);

class OpenAddressingHashTable {
    private array $table;
    private int $size = 0;
    private int $capacity;

    public function __construct(int $capacity) {
        $this->capacity = $capacity;
        $this->table = array_fill(0, $capacity, null);
    }

    private function hash(string $key): int {
        return crc32($key) % $this->capacity;
    }

    public function insert(string $key, mixed $value): void {
        $index = $this->hash($key);
        
        // Linear probing: find next available slot
        while ($this->table[$index] !== null && $this->table[$index]['key'] !== $key) {
            $index = ($index + 1) % $this->capacity;
        }
        
        $this->table[$index] = ['key' => $key, 'value' => $value];
        $this->size++;
    }

    public function get(string $key): mixed {
        $index = $this->hash($key);
        
        while ($this->table[$index] !== null) {
            if ($this->table[$index]['key'] === $key) {
                return $this->table[$index]['value'];
            }
            $index = ($index + 1) % $this->capacity;
        }
        
        return null;
    }
}
```
*See: Chapter 13 (Hash Tables & Hash Functions)*

## P

**Parent Node**: Node that has references to child nodes in tree structure.

**Partition**: Dividing array into segments based on pivot value (used in QuickSort).

**Path**: Sequence of edges connecting two vertices in graph.

**Perfect Binary Tree**: Binary tree where all interior nodes have two children and all leaves are at same level.

**Pivot**: Element used to partition array in QuickSort algorithm.

**Point in Polygon**: Computational geometry problem determining if a point lies inside, outside, or on boundary of polygon. Solved using ray casting or winding number algorithms.

```php
# filename: point-in-polygon-example.php
<?php

declare(strict_types=1);

function pointInPolygon(Point $point, array $polygon): bool {
    $n = count($polygon);
    $inside = false;
    
    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $xi = $polygon[$i]->x;
        $yi = $polygon[$i]->y;
        $xj = $polygon[$j]->x;
        $yj = $polygon[$j]->y;
        
        $intersect = (($yi > $point->y) !== ($yj > $point->y)) &&
                     ($point->x < ($xj - $xi) * ($point->y - $yi) / ($yj - $yi) + $xi);
        
        if ($intersect) {
            $inside = !$inside;
        }
    }
    
    return $inside;
}
```
*See: Chapter 34 (Geometric Algorithms)*

**Polynomial Time**: Algorithm with time complexity O(n^k) for some constant k.

**Post-Order Traversal**: Tree traversal visiting left subtree, right subtree, then root.

**Pre-Order Traversal**: Tree traversal visiting root, then left subtree, then right subtree.

**Priority Queue**: Queue where elements have priority values determining order of removal.

**Race Condition**: Situation where concurrent operations access shared resources without proper synchronization, leading to unpredictable results.

```php
# filename: race-condition-example.php
<?php

declare(strict_types=1);

// Example of race condition (BAD - don't do this)
class UnsafeCounter {
    private int $count = 0;

    public function increment(): void {
        // Race condition: multiple threads/processes can read same value
        $this->count = $this->count + 1;  // Not atomic!
    }

    public function getCount(): int {
        return $this->count;
    }
}

// Thread-safe version using synchronization
class SafeCounter {
    private int $count = 0;
    private $lock;

    public function __construct() {
        $this->lock = new \SplSemaphore(1);
    }

    public function increment(): void {
        $this->lock->acquire();
        $this->count++;
        $this->lock->release();
    }

    public function getCount(): int {
        return $this->count;
    }
}
```
*See: Chapter 31 (Concurrent Algorithms)*

**Rate Limiting**: Technique for controlling the rate of requests or operations to prevent system overload and ensure fair resource usage.

```php
# filename: rate-limiting-example.php
<?php

declare(strict_types=1);

class RateLimiter {
    private array $requests = [];
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(int $maxRequests, int $windowSeconds) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    public function isAllowed(string $identifier): bool {
        $now = time();
        $this->cleanOldRequests($now);

        if (count($this->requests[$identifier] ?? []) < $this->maxRequests) {
            $this->requests[$identifier][] = $now;
            return true;
        }

        return false;
    }

    private function cleanOldRequests(int $now): void {
        foreach ($this->requests as $id => &$times) {
            $this->requests[$id] = array_filter($times, fn($t) => $now - $t < $this->windowSeconds);
        }
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**Rabin-Karp Algorithm**: String matching algorithm using rolling hash to find pattern in text. Average time: O(n + m), worst: O(nm).

**RSA (Rivest-Shamir-Adleman)**: Asymmetric encryption algorithm based on difficulty of factoring large numbers. Used for key exchange and digital signatures. Key sizes: 2048+ bits recommended.

```php
# filename: rsa-example.php
<?php

declare(strict_types=1);

// Generate RSA key pair
$config = [
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$keyPair = openssl_pkey_new($config);
openssl_pkey_export($keyPair, $privateKey);
$publicKey = openssl_pkey_get_details($keyPair)["key"];

// Encrypt with public key
$data = "Secret message";
openssl_public_encrypt($data, $encrypted, $publicKey);

// Decrypt with private key
openssl_private_decrypt($encrypted, $decrypted, $privateKey);

// Digital signature
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$isValid = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);
```
*See: Chapter 35 (Cryptographic Algorithms)*

## S

**Shortest Path**: Path between two vertices with minimum total edge weight.

**Singly Linked List**: Linked list where each node has reference to next node only.

**Sliding Window**: Technique maintaining subset of array/string by adding/removing elements at ends.

**Sliding Window Counter**: Rate limiting algorithm that tracks requests in overlapping time windows, providing precise rate limiting with smooth transitions.

```php
# filename: sliding-window-counter-example.php
<?php

declare(strict_types=1);

class SlidingWindowCounter {
    private array $windows = [];
    private int $maxRequests;
    private int $windowSizeSeconds;
    private int $numWindows;

    public function __construct(int $maxRequests, int $windowSizeSeconds, int $numWindows = 10) {
        $this->maxRequests = $maxRequests;
        $this->windowSizeSeconds = $windowSizeSeconds;
        $this->numWindows = $numWindows;
    }

    public function isAllowed(string $identifier): bool {
        $now = time();
        $currentWindow = (int)($now / ($this->windowSizeSeconds / $this->numWindows));

        // Clean old windows
        $this->windows[$identifier] = array_filter(
            $this->windows[$identifier] ?? [],
            fn($w) => $w >= $currentWindow - $this->numWindows
        );

        // Count requests in current windows
        $count = count($this->windows[$identifier] ?? []);

        if ($count < $this->maxRequests) {
            $this->windows[$identifier][] = $currentWindow;
            return true;
        }

        return false;
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**Space Complexity**: Amount of memory used by algorithm as function of input size.

**Stable Sort**: Sorting algorithm preserving relative order of equal elements.

**Stack**: LIFO data structure supporting push (add to top) and pop (remove from top) operations.

**Suffix Array**: Sorted array of all suffixes of string, enabling efficient substring queries.

**Suffix Tree**: Compressed trie of all suffixes, supporting linear-time pattern matching.

**Symmetric Encryption**: Encryption where same key encrypts and decrypts data. Fast and efficient for large data. Examples: AES, ChaCha20.

```php
# filename: symmetric-encryption-example.php
<?php

declare(strict_types=1);

// AES-256-GCM symmetric encryption
$key = random_bytes(32);  // 256-bit key
$iv = random_bytes(12);    // 96-bit IV for GCM
$plaintext = "Secret message";

$ciphertext = openssl_encrypt(
    $plaintext,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag
);

// Decrypt
$decrypted = openssl_decrypt(
    $ciphertext,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag
);

// Using Libsodium (recommended)
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
$ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);
$decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
```
*See: Chapter 35 (Cryptographic Algorithms)*

**Sweep Line Algorithm**: Geometric algorithm technique that processes events in sorted order (typically by x-coordinate), maintaining active set of objects intersecting sweep line. Used for line segment intersection, Voronoi diagrams, and other geometric problems.

```php
# filename: sweep-line-example.php
<?php

declare(strict_types=1);

class SweepLine {
    public function findIntersections(array $segments): array {
        $events = [];
        $active = [];
        $intersections = [];
        
        // Create events: segment start and end points
        foreach ($segments as $seg) {
            $events[] = ['type' => 'start', 'segment' => $seg, 'x' => $seg->start->x];
            $events[] = ['type' => 'end', 'segment' => $seg, 'x' => $seg->end->x];
        }
        
        // Sort events by x-coordinate
        usort($events, fn($a, $b) => $a['x'] <=> $b['x']);
        
        // Process events
        foreach ($events as $event) {
            if ($event['type'] === 'start') {
                // Check against active segments
                foreach ($active as $activeSeg) {
                    $intersection = $this->intersect($event['segment'], $activeSeg);
                    if ($intersection) {
                        $intersections[] = $intersection;
                    }
                }
                $active[] = $event['segment'];
            } else {
                // Remove from active set
                $active = array_filter($active, fn($s) => $s !== $event['segment']);
            }
        }
        
        return $intersections;
    }
}
```
*See: Chapter 34 (Geometric Algorithms)*

## T

**Tail Recursion**: Recursion where recursive call is last operation in function.

**Time Complexity**: Number of operations algorithm performs as function of input size.

**Top-K Elements**: Problem of finding K largest or smallest elements in a dataset, often solved efficiently with heaps or quickselect.

```php
# filename: top-k-example.php
<?php

declare(strict_types=1);

function findTopK(array $arr, int $k): array {
    // Using min heap to find top K largest
    $heap = new SplMinHeap();

    foreach ($arr as $value) {
        if ($heap->count() < $k) {
            $heap->insert($value);
        } elseif ($value > $heap->top()) {
            $heap->extract();
            $heap->insert($value);
        }
    }

    $result = [];
    while (!$heap->isEmpty()) {
        $result[] = $heap->extract();
    }

    return array_reverse($result);  // Return largest to smallest
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**Topological Sort**: Linear ordering of DAG vertices such that for every edge (u,v), u comes before v.

**Tree**: Connected acyclic graph with hierarchical structure.

**Trie (Prefix Tree)**: Tree-like data structure for storing strings where common prefixes share paths.

**Token Bucket**: Rate limiting algorithm that allows bursts of requests up to bucket capacity, refilling tokens at fixed rate.

```php
# filename: token-bucket-example.php
<?php

declare(strict_types=1);

class TokenBucket {
    private float $capacity;
    private float $tokens;
    private float $refillRate;
    private float $lastRefill;

    public function __construct(float $capacity, float $refillRate) {
        $this->capacity = $capacity;
        $this->tokens = $capacity;
        $this->refillRate = $refillRate;
        $this->lastRefill = microtime(true);
    }

    public function tryConsume(float $tokens = 1.0): bool {
        $this->refill();

        if ($this->tokens >= $tokens) {
            $this->tokens -= $tokens;
            return true;
        }

        return false;  // Not enough tokens
    }

    private function refill(): void {
        $now = microtime(true);
        $elapsed = $now - $this->lastRefill;
        $this->tokens = min($this->capacity, $this->tokens + ($this->refillRate * $elapsed));
        $this->lastRefill = $now;
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**Two Pointers**: Technique using two array indices (pointers) moving toward each other or in same direction.

## U

**Undirected Graph**: Graph where edges have no direction (bidirectional).

**Union-Find (Disjoint Set)**: Data structure tracking partitions of set, supporting union and find operations efficiently.

**Unstable Sort**: Sorting algorithm that may change relative order of equal elements.

## V

**Vertex (Node)**: Fundamental unit of graph representing entity or location.

**Visited Set**: Data structure tracking which nodes have been explored in graph traversal.

**Voronoi Diagram**: Partition of plane into regions based on distance to set of points. Each region contains all points closer to one site than any other.

## W

**Weighted Graph**: Graph where edges have associated weights/costs.

**Welzl's Algorithm**: Randomized algorithm for finding minimum enclosing circle (smallest circle containing all points). Expected time: O(n), worst case: O(n²).

```php
# filename: welzl-example.php
<?php

declare(strict_types=1);

function welzl(array $points, array $boundary = []): Circle {
    if (empty($points) || count($boundary) === 3) {
        return makeCircle($boundary);
    }
    
    // Pick random point
    $p = $points[array_rand($points)];
    $remaining = array_filter($points, fn($pt) => $pt !== $p);
    
    $circle = welzl($remaining, $boundary);
    
    if (!isInside($p, $circle)) {
        $boundary[] = $p;
        $circle = welzl($remaining, $boundary);
    }
    
    return $circle;
}
```
*See: Chapter 34 (Geometric Algorithms)*

**Worst-Case Complexity**: Maximum resources algorithm requires over all possible inputs of given size.

## Z

**Z-Algorithm**: Linear-time string matching algorithm that preprocesses pattern to find all occurrences in text. Time: O(n + m).

```php
# filename: z-algorithm-example.php
<?php

declare(strict_types=1);

function zAlgorithm(string $text, string $pattern): array {
    $combined = $pattern . '$' . $text;
    $n = strlen($combined);
    $z = array_fill(0, $n, 0);
    $left = 0;
    $right = 0;
    $matches = [];

    for ($i = 1; $i < $n; $i++) {
        if ($i <= $right) {
            $z[$i] = min($right - $i + 1, $z[$i - $left]);
        }

        while ($i + $z[$i] < $n && $combined[$z[$i]] === $combined[$i + $z[$i]]) {
            $z[$i]++;
        }

        if ($i + $z[$i] - 1 > $right) {
            $left = $i;
            $right = $i + $z[$i] - 1;
        }

        // Check if match found (pattern length)
        if ($z[$i] === strlen($pattern)) {
            $matches[] = $i - strlen($pattern) - 1;
        }
    }

    return $matches;
}
```
*See: Chapter 33 (String Algorithms Deep Dive)*

## Example Usage

```php
# filename: rabin-karp-example.php
<?php

declare(strict_types=1);

function rabinKarp(string $text, string $pattern): array {
    $matches = [];
    $n = strlen($text);
    $m = strlen($pattern);
    $base = 256;
    $mod = 1000000007;

    // Calculate hash of pattern and first window
    $patternHash = 0;
    $textHash = 0;
    $h = 1;

    for ($i = 0; $i < $m - 1; $i++) {
        $h = ($h * $base) % $mod;
    }

    for ($i = 0; $i < $m; $i++) {
        $patternHash = ($base * $patternHash + ord($pattern[$i])) % $mod;
        $textHash = ($base * $textHash + ord($text[$i])) % $mod;
    }

    // Slide pattern over text
    for ($i = 0; $i <= $n - $m; $i++) {
        if ($patternHash === $textHash) {
            // Verify match (hash collision possible)
            if (substr($text, $i, $m) === $pattern) {
                $matches[] = $i;
            }
        }

        if ($i < $n - $m) {
            // Rolling hash: remove leftmost, add rightmost
            $textHash = ($base * ($textHash - ord($text[$i]) * $h) + ord($text[$i + $m])) % $mod;
            if ($textHash < 0) {
                $textHash += $mod;
            }
        }
    }

    return $matches;
}
```
*See: Chapter 33 (String Algorithms Deep Dive)*

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

**Sliding Window Counter**: Rate limiting algorithm that tracks requests in overlapping time windows, providing precise rate limiting with smooth transitions.

```php
# filename: sliding-window-counter-example.php
<?php

declare(strict_types=1);

class SlidingWindowCounter {
    private array $windows = [];
    private int $maxRequests;
    private int $windowSizeSeconds;
    private int $numWindows;

    public function __construct(int $maxRequests, int $windowSizeSeconds, int $numWindows = 10) {
        $this->maxRequests = $maxRequests;
        $this->windowSizeSeconds = $windowSizeSeconds;
        $this->numWindows = $numWindows;
    }

    public function isAllowed(string $identifier): bool {
        $now = time();
        $currentWindow = (int)($now / ($this->windowSizeSeconds / $this->numWindows));

        // Clean old windows
        $this->windows[$identifier] = array_filter(
            $this->windows[$identifier] ?? [],
            fn($w) => $w >= $currentWindow - $this->numWindows
        );

        // Count requests in current windows
        $count = count($this->windows[$identifier] ?? []);

        if ($count < $this->maxRequests) {
            $this->windows[$identifier][] = $currentWindow;
            return true;
        }

        return false;
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**Space Complexity**: Amount of memory used by algorithm as function of input size.

**Stable Sort**: Sorting algorithm preserving relative order of equal elements.

**Stack**: LIFO data structure supporting push (add to top) and pop (remove from top) operations.

**Suffix Array**: Sorted array of all suffixes of string, enabling efficient substring queries.

**Suffix Tree**: Compressed trie of all suffixes, supporting linear-time pattern matching.

## T

**Tail Recursion**: Recursion where recursive call is last operation in function.

**Time Complexity**: Number of operations algorithm performs as function of input size.

**Top-K Elements**: Problem of finding K largest or smallest elements in a dataset, often solved efficiently with heaps or quickselect.

```php
# filename: top-k-example.php
<?php

declare(strict_types=1);

function findTopK(array $arr, int $k): array {
    // Using min heap to find top K largest
    $heap = new SplMinHeap();

    foreach ($arr as $value) {
        if ($heap->count() < $k) {
            $heap->insert($value);
        } elseif ($value > $heap->top()) {
            $heap->extract();
            $heap->insert($value);
        }
    }

    $result = [];
    while (!$heap->isEmpty()) {
        $result[] = $heap->extract();
    }

    return array_reverse($result);  // Return largest to smallest
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**Tabulation**: Bottom-up dynamic programming approach that builds solution iteratively from base cases, filling table/array systematically. Contrasts with memoization (top-down).

```php
# filename: tabulation-example.php
<?php

declare(strict_types=1);

// Tabulation: bottom-up approach
function fibonacciTabulation(int $n): int {
    $dp = [0, 1];  // Base cases
    
    // Build up from base cases
    for ($i = 2; $i <= $n; $i++) {
        $dp[$i] = $dp[$i - 1] + $dp[$i - 2];
    }
    
    return $dp[$n];
}

// Memoization: top-down approach (for comparison)
function fibonacciMemoization(int $n, array &$memo = []): int {
    if ($n <= 1) return $n;
    
    if (!isset($memo[$n])) {
        $memo[$n] = fibonacciMemoization($n - 1, $memo) + 
                     fibonacciMemoization($n - 2, $memo);
    }
    
    return $memo[$n];
}
```
*See: Chapter 25 (Dynamic Programming Fundamentals)*

**Topological Sort**: Linear ordering of DAG vertices such that for every edge (u,v), u comes before v.

**Tree**: Connected acyclic graph with hierarchical structure.

**Trie (Prefix Tree)**: Tree-like data structure for storing strings where common prefixes share paths.

**Token Bucket**: Rate limiting algorithm that allows bursts of requests up to bucket capacity, refilling tokens at fixed rate.

```php
# filename: token-bucket-example.php
<?php

declare(strict_types=1);

class TokenBucket {
    private float $capacity;
    private float $tokens;
    private float $refillRate;
    private float $lastRefill;

    public function __construct(float $capacity, float $refillRate) {
        $this->capacity = $capacity;
        $this->tokens = $capacity;
        $this->refillRate = $refillRate;
        $this->lastRefill = microtime(true);
    }

    public function tryConsume(float $tokens = 1.0): bool {
        $this->refill();

        if ($this->tokens >= $tokens) {
            $this->tokens -= $tokens;
            return true;
        }

        return false;  // Not enough tokens
    }

    private function refill(): void {
        $now = microtime(true);
        $elapsed = $now - $this->lastRefill;
        $this->tokens = min($this->capacity, $this->tokens + ($this->refillRate * $elapsed));
        $this->lastRefill = $now;
    }
}
```
*See: Chapter 36 (Stream Processing Algorithms)*

**TSP (Traveling Salesman Problem)**: Optimization problem of finding shortest route visiting each city exactly once and returning to origin. NP-hard problem solved with dynamic programming, heuristics, or approximation algorithms.

```php
# filename: tsp-example.php
<?php

declare(strict_types=1);

// TSP using dynamic programming (bitmask DP)
function tsp(array $dist, int $n): float {
    $dp = array_fill(0, 1 << $n, array_fill(0, $n, PHP_INT_MAX));
    $dp[1][0] = 0;  // Start at city 0

    // Try all subsets
    for ($mask = 1; $mask < (1 << $n); $mask++) {
        for ($i = 0; $i < $n; $i++) {
            if (!($mask & (1 << $i))) continue;
            
            for ($j = 0; $j < $n; $j++) {
                if ($mask & (1 << $j)) continue;
                $newMask = $mask | (1 << $j);
                $dp[$newMask][$j] = min(
                    $dp[$newMask][$j],
                    $dp[$mask][$i] + $dist[$i][$j]
                );
            }
        }
    }

    // Return to start
    $finalMask = (1 << $n) - 1;
    $minCost = PHP_INT_MAX;
    for ($i = 1; $i < $n; $i++) {
        $minCost = min($minCost, $dp[$finalMask][$i] + $dist[$i][0]);
    }

    return $minCost;
}
```
*See: Chapter 26 (Advanced Dynamic Programming)*

**TTL (Time To Live)**: Cache expiration mechanism that automatically invalidates cached items after specified time period. Prevents stale data while allowing efficient caching.

```php
# filename: ttl-cache-example.php
<?php

declare(strict_types=1);

class TTLCache {
    private array $cache = [];
    private int $ttl;

    public function __construct(int $ttlSeconds) {
        $this->ttl = $ttlSeconds;
    }

    public function get(string $key): mixed {
        if (!isset($this->cache[$key])) {
            return null;
        }

        $entry = $this->cache[$key];
        if (time() - $entry['timestamp'] > $this->ttl) {
            unset($this->cache[$key]);
            return null;  // Expired
        }

        return $entry['value'];
    }

    public function set(string $key, mixed $value): void {
        $this->cache[$key] = [
            'value' => $value,
            'timestamp' => time()
        ];
    }
}
```
*See: Chapter 27 (Caching & Memoization Strategies)*

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

## X

**Xdebug**: PHP debugging and profiling extension that provides detailed code coverage, function traces, and performance profiling data. Essential tool for identifying bottlenecks.

*See: Chapter 29 (Performance Optimization)*

## Z

**Z-Algorithm**: Linear-time string matching algorithm that preprocesses pattern to find all occurrences in text. Time: O(n + m).

```php
# filename: z-algorithm-example.php
<?php

declare(strict_types=1);

function zAlgorithm(string $text, string $pattern): array {
    $combined = $pattern . '$' . $text;
    $n = strlen($combined);
    $z = array_fill(0, $n, 0);
    $left = 0;
    $right = 0;
    $matches = [];

    for ($i = 1; $i < $n; $i++) {
        if ($i <= $right) {
            $z[$i] = min($right - $i + 1, $z[$i - $left]);
        }

        while ($i + $z[$i] < $n && $combined[$z[$i]] === $combined[$i + $z[$i]]) {
            $z[$i]++;
        }

        if ($i + $z[$i] - 1 > $right) {
            $left = $i;
            $right = $i + $z[$i] - 1;
        }

        // Check if match found (pattern length)
        if ($z[$i] === strlen($pattern)) {
            $matches[] = $i - strlen($pattern) - 1;
        }
    }

    return $matches;
}
```
*See: Chapter 33 (String Algorithms Deep Dive)*

## Example Usage

```php
# filename: example-usage.php
<?php

declare(strict_types=1);

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
function binarySearch(array $arr, int $target): int {
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
function fibonacci(int $n, array &$cache): int {
    if ($n <= 1) return $n;

    if (!isset($cache[$n])) {
        $cache[$n] = fibonacci($n - 1, $cache) + fibonacci($n - 2, $cache);
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
function bellmanFord(array $graph, int $V, int $start): array {
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
# filename: hashing-example.php
<?php

declare(strict_types=1);

// Hash function example
function simpleHash(string $key, int $tableSize): int {
    $hash = 0;
    for ($i = 0; $i < strlen($key); $i++) {
        $hash = ($hash * 31 + ord($key[$i])) % $tableSize;
    }
    return $hash;
}

// PHP built-in hash functions
$hash1 = crc32($data);              // Fast, 32-bit
$hash2 = md5($data);                // 128-bit
$hash3 = hash('sha256', $data);     // 256-bit (cryptographic)

// Collision resolution: chaining
$hashTable = array_fill(0, 10, []);
$index = simpleHash('key', 10);
$hashTable[$index][] = ['key' => 'key', 'value' => 'value'];
```
*See: Chapter 13 (Hash Tables and Hash Functions)*

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
# filename: load-factor-example.php
<?php

declare(strict_types=1);

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

    public function insert(mixed $key, mixed $value): void {
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
*See: Chapter 13 (Hash Tables and Hash Functions)*

**Memoization** (pronunciation: mem-oh-ize-AY-shun, note: NOT "memorization"): Storing results of expensive function calls to avoid recalculation.

```php
// Without memoization: O(2^n) - recalculates many times
function fibSlow(int $n): int {
    if ($n <= 1) return $n;
    return fibSlow($n - 1) + fibSlow($n - 2);
}

// With memoization: O(n) - calculates each value once
function fibMemo(int $n, array &$memo = []): int {
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
*See: Chapter 6 (Merge Sort), Chapter 9 (Comparing Sorting Algorithms)*

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
- **Sorting**: Chapters 5-10, Appendix A
- **Searching**: Chapters 11-12
- **Graph Algorithms**: Chapters 21-24
- **String Algorithms**: Chapters 14, 33
- **Dynamic Programming**: Chapters 25-26
- **Greedy Algorithms**: Chapter 26
- **Backtracking**: Chapter 3
- **Probabilistic Algorithms**: Chapter 32
- **Concurrent Algorithms**: Chapter 31
- **Stream Processing**: Chapter 36
- **Cryptographic Algorithms**: Chapter 35

### By Performance Topic
- **Time Complexity**: Chapter 2, Appendix A
- **Space Complexity**: Chapter 3
- **Optimization**: Chapter 29, Appendix B
- **Profiling**: Appendix B

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="appendix-c"
  label="Glossary reviewed!"
/>

## See Also

- **Appendix A**: Complexity Cheat Sheet - Detailed complexity tables and practical examples
- **Appendix B**: PHP Performance Tips - Optimization techniques and best practices
- **Appendix D**: Further Reading - Books and resources for deeper understanding
- **Chapter 1**: Introduction to Algorithms - Algorithm fundamentals
- **Chapter 2**: Time Complexity Analysis - In-depth Big O analysis
- **Chapter 3**: Space Complexity - Memory usage patterns
- **Chapter 29**: Performance Optimization - Real-world optimization strategies

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
function fibSlow(int $n): int {
    if ($n <= 1) return $n;
    return fibSlow($n - 1) + fibSlow($n - 2);
}

// With memoization: O(n) - calculates each value once
function fibMemo(int $n, array &$memo = []): int {
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
*See: Chapter 6 (Merge Sort), Chapter 9 (Comparing Sorting Algorithms)*

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
- **Sorting**: Chapters 5-10, Appendix A
- **Searching**: Chapters 11-12
- **Graph Algorithms**: Chapters 21-24
- **String Algorithms**: Chapters 14, 33
- **Dynamic Programming**: Chapters 25-26
- **Greedy Algorithms**: Chapter 26
- **Backtracking**: Chapter 3
- **Probabilistic Algorithms**: Chapter 32
- **Concurrent Algorithms**: Chapter 31
- **Stream Processing**: Chapter 36
- **Cryptographic Algorithms**: Chapter 35

### By Performance Topic
- **Time Complexity**: Chapter 2, Appendix A
- **Space Complexity**: Chapter 3
- **Optimization**: Chapter 29, Appendix B
- **Profiling**: Appendix B

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="appendix-c"
  label="Glossary reviewed!"
/>

## See Also

- **Appendix A**: Complexity Cheat Sheet - Detailed complexity tables and practical examples
- **Appendix B**: PHP Performance Tips - Optimization techniques and best practices
- **Appendix D**: Further Reading - Books and resources for deeper understanding
- **Chapter 1**: Introduction to Algorithms - Algorithm fundamentals
- **Chapter 2**: Time Complexity Analysis - In-depth Big O analysis
- **Chapter 3**: Space Complexity - Memory usage patterns
- **Chapter 29**: Performance Optimization - Real-world optimization strategies
