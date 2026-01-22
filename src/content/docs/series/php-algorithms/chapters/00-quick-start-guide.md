---
title: "Quick Start Guide"
description: "Get started with algorithms in 5 minutes. Common scenarios, algorithm mapping, and copy-paste ready solutions for PHP developers."
sidebar:
  label: "00: Quick Start Guide"
  order: 0
  badge:
    text: beginner
    variant: danger
---
![Quick Start Guide](/images/php-algorithms/chapter-00-quick-start-guide-hero-full.webp)


# Quick Start Guide <span class="difficulty-badge difficulty-beginner">Beginner</span>

**Got 5 minutes?** This guide gets you from zero to productive fast. Skip the theory and jump straight to practical solutions.

::: info
This is a reference guide, not a traditional tutorial. For step-by-step learning, start with [Chapter 1: Introduction to Algorithms](/series/php-algorithms/chapters/00-introduction-to-algorithms/).
:::

## What You'll Learn

**Estimated time:** 15 minutes

By the end of this quick start guide, you will:

- Get productive with algorithms immediately using copy-paste ready solutions
- Learn which algorithm to use for common scenarios (sorting, searching, caching, path-finding)
- Master the decision tree for algorithm selection based on your data and use case
- Discover quick performance wins that can 10x your application speed
- Access framework-specific optimizations for Laravel and Symfony

## Prerequisites

**No prerequisites required** - dive right in! This guide is designed for PHP developers of all levels who want practical solutions now.

## 🎯 "I Need To..."

### Sort Data

```php
<?php
# filename: quick-sort-example.php

// Small array (<100 items) or nearly sorted
$data = [64, 34, 25, 12, 22, 11, 90];
sort($data);  // PHP's built-in - BEST choice 99% of the time

// Custom sorting
$users = [
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25],
];
usort($users, fn($a, $b) => $a['age'] <=> $b['age']);

// When to use custom algorithms:
// - Learning/interview prep: See Chapter 5-10
// - Specific constraints: See Chapter 28 (Selection Guide)
```

**→ [Full Sorting Guide](/series/php-algorithms/chapters/05-bubble-selection-sort/)**

### Search for Something

```php
<?php
# filename: search-examples.php

declare(strict_types=1);

// In unsorted array
$needle = 'value';
$found = in_array($needle, $haystack, true);  // Linear search O(n)

// In sorted array - use binary search
function binarySearch(array $arr, mixed $target): int {
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);
        if ($arr[$mid] === $target) return $mid;
        if ($arr[$mid] < $target) $left = $mid + 1;
        else $right = $mid - 1;
    }

    return -1;  // Not found
}

// Frequent lookups - use hash table
$lookup = array_flip($haystack);  // O(1) search after O(n) setup
$found = isset($lookup[$needle]);
```

::: tip Performance Tip
Binary search is **100x faster** than linear search on large sorted arrays. Hash tables give **instant** O(1) lookups but require O(n) setup time.
:::

**→ [Binary Search](/series/php-algorithms/chapters/12-binary-search/)** | **[Hash Tables](/series/php-algorithms/chapters/13-hash-tables-hash-functions/)**

### Cache Results

```php
<?php
# filename: simple-cache.php

declare(strict_types=1);

// Simple in-memory cache
class SimpleCache {
    private array $cache = [];

    public function remember(string $key, callable $callback): mixed {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $callback();
        }
        return $this->cache[$key];
    }

    public function forget(string $key): void {
        unset($this->cache[$key]);
    }

    public function clear(): void {
        $this->cache = [];
    }
}

// Usage
$cache = new SimpleCache();
$result = $cache->remember('expensive_op', function(): array {
    // Expensive database query or calculation
    return expensiveOperation();
});

// Production: Use Redis
$redis = new Redis();
$redis->connect('127.0.0.1');
$result = $redis->get('key') ?: $redis->setex('key', 3600, expensiveOperation());
```

::: tip Cache Everything
Caching is the **#1 performance win**. A simple cache can reduce response times from 500ms to 5ms (100x faster)!
:::

**→ [Caching Strategies](/series/php-algorithms/chapters/27-caching-memoization-strategies/)** | **[Performance Optimization](/series/php-algorithms/chapters/29-performance-optimization/)**

### Find Shortest Path

```php
<?php
# filename: shortest-path-bfs.php

declare(strict_types=1);

// Unweighted graph (all edges equal) - Use BFS
function shortestPath(array $graph, int $start, int $end): ?array {
    if ($start === $end) return [$start];
    if (!isset($graph[$start])) return null;

    $queue = [[$start]];
    $visited = [$start => true];

    while (!empty($queue)) {
        $path = array_shift($queue);
        $node = end($path);

        if ($node === $end) return $path;

        foreach ($graph[$node] ?? [] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $visited[$neighbor] = true;
                $newPath = [...$path, $neighbor];  // PHP 8.4 spread operator
                $queue[] = $newPath;
            }
        }
    }

    return null;  // No path found
}

// Weighted graph - See Dijkstra's algorithm
```

**→ [BFS](/series/php-algorithms/chapters/23-breadth-first-search/)** | **[Dijkstra](/series/php-algorithms/chapters/24-dijkstra-shortest-path/)**

### Process Large Files

```php
<?php
# filename: stream-large-file.php

declare(strict_types=1);

// ❌ BAD: Loads entire file into memory
$lines = file('huge-file.csv');  // OOM for large files

// ✅ GOOD: Stream with generator
function readLargeFile(string $filename): Generator {
    if (!file_exists($filename)) {
        throw new RuntimeException("File not found: $filename");
    }

    $handle = fopen($filename, 'r');
    if ($handle === false) {
        throw new RuntimeException("Cannot open file: $filename");
    }

    try {
        while (($line = fgets($handle)) !== false) {
            yield $line;
        }
    } finally {
        fclose($handle);
    }
}

// Usage - constant memory regardless of file size
foreach (readLargeFile('huge-file.csv') as $lineNumber => $line) {
    processLine($line);
}
```

::: warning Memory Management
Generators use **constant memory** regardless of file size. A 10GB file uses the same ~100KB memory whether you're processing 1 line or 1 billion lines!
:::

**→ [Performance Optimization](/series/php-algorithms/chapters/29-performance-optimization/)** | **[Stream Processing](/series/php-algorithms/chapters/36-stream-processing-algorithms/)**

### Optimize Slow Code

```php
<?php
# filename: optimization-examples.php

// 1. Profile first
$start = microtime(true);
slowFunction();
echo (microtime(true) - $start) * 1000 . "ms\n";

// 2. Common fixes:

// ❌ N+1 database queries
foreach ($users as $user) {
    $posts = $db->query("SELECT * FROM posts WHERE user_id = ?", [$user['id']]);
}

// ✅ Single query with JOIN
$results = $db->query("
    SELECT u.*, p.*
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
");

// ❌ Repeated calculations in loop
for ($i = 0; $i < count($array); $i++) {  // count() called every iteration
    process($array[$i]);
}

// ✅ Calculate once
$n = count($array);
for ($i = 0; $i < $n; $i++) {
    process($array[$i]);
}

// ❌ String concatenation in loop
$result = '';
foreach ($items as $item) {
    $result .= $item;  // Creates new string each time
}

// ✅ Use array and implode
$parts = [];
foreach ($items as $item) {
    $parts[] = $item;
}
$result = implode('', $parts);
```

**→ [Performance Guide](/series/php-algorithms/chapters/29-performance-optimization/)**

---

## 🚦 Decision Tree

### "Which Algorithm Should I Use?"

```
Need to process data?
├─ Search for specific item?
│  ├─ Data sorted? → Binary Search (O(log n))
│  ├─ Many searches? → Hash Table (O(1))
│  └─ Unsorted, one search? → Linear Search (O(n))
│
├─ Sort data?
│  ├─ Use PHP sort() → DONE (best choice 99% of time)
│  ├─ Need custom order? → usort() with comparator
│  └─ Learning? → See Chapters 5-10
│
├─ Find path between nodes?
│  ├─ Unweighted graph? → BFS (shortest path)
│  ├─ Weighted graph? → Dijkstra's Algorithm
│  └─ Just any path? → DFS
│
├─ Optimize repeated calculations?
│  ├─ Overlapping subproblems? → Dynamic Programming
│  ├─ Expensive function calls? → Memoization/Caching
│  └─ Database queries? → Query result caching
│
└─ Process large dataset?
   ├─ Fits in memory? → Regular arrays
   ├─ Too large for memory? → Generators/Streaming
   └─ Need fast lookups? → Hash table/Redis
```

---

## 📊 Complexity Cheat Sheet

| Complexity | Max Input Size | Examples |
|------------|---------------|----------|
| O(1) | Unlimited | Array access, hash table lookup |
| O(log n) | Billions | Binary search, balanced tree ops |
| O(n) | 100 million | Linear search, array traversal |
| O(n log n) | 10 million | Merge sort, quick sort, heap sort |
| O(n²) | 10,000 | Bubble sort, selection sort, nested loops |
| O(2^n) | ~20 | Subset generation, backtracking |
| O(n!) | ~10 | Permutations, TSP brute force |

**Rule of thumb**: If n > 10,000 and you have O(n²), you need a better algorithm.

### 📏 Real-World Input Sizes

Understanding typical data scales helps you choose the right algorithm for your use case:

| Use Case | Typical Size | Algorithm Needed | Why |
|----------|-------------|------------------|-----|
| User dropdown options | 10-100 | Any (even O(n²)) | Too small to matter |
| Search autocomplete | 1,000-10,000 | Hash table/Trie | Need instant O(1) lookups |
| E-commerce catalog | 10,000-100,000 | Indexed DB search | Database handles it |
| Social media feed | 100-1,000/page | Pagination + O(n) | Per-page processing |
| Analytics dashboard | 10,000-1M rows | O(n log n) max | Must complete in seconds |
| Log file processing | 1M-1B entries | Streaming/generators | Can't fit in memory |
| ML training data | 1M-100M records | Specialized + distributed | Requires infrastructure |

**Quick Size Check**:

```php
<?php
# filename: size-check-helper.php

declare(strict_types=1);

function chooseApproach(int $n): string {
    return match(true) {
        $n < 100 => "✅ Use any algorithm, even O(n²)",
        $n < 10_000 => "⚠️  O(n²) acceptable, O(n log n) better",
        $n < 1_000_000 => "⚡ Need O(n log n), prefer O(n)",
        default => "🔥 Must use O(n) or streaming"
    };
}

// Examples
echo chooseApproach(50) . "\n";        // Any algorithm
echo chooseApproach(5_000) . "\n";     // O(n²) acceptable
echo chooseApproach(500_000) . "\n";   // Need O(n log n)
echo chooseApproach(10_000_000) . "\n"; // Must stream
```

::: tip Pro Tip
If your "large" dataset fits comfortably in a PHP array, it's probably not that large. True big data requires databases, message queues, or streaming processors.
:::

::: info Quick Reference
**Most Common**: O(1) hash lookup, O(n) array scan, O(n log n) sort, O(log n) binary search.  
**Avoid in Production**: O(n²) nested loops on large data, O(2^n) recursive solutions without memoization.
:::

**→ [Full Complexity Guide](/series/php-algorithms/chapters/00-introduction-to-algorithms#big-o-notation)** | **[Appendix A](/series/php-algorithms/appendices/a-complexity-cheat-sheet/)**

---

## 🛠️ Common Patterns

### Two Pointers

```php
<?php
# filename: two-pointers-pattern.php

declare(strict_types=1);

// Find pair that sums to target (sorted array)
function twoSum(array $arr, int $target): ?array {
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        $sum = $arr[$left] + $arr[$right];
        if ($sum === $target) return [$left, $right];
        if ($sum < $target) $left++;
        else $right--;
    }

    return null;
}

// Usage example
$numbers = [1, 2, 3, 4, 6, 8, 9];
$result = twoSum($numbers, 10);
// Returns: [1, 5] because $numbers[1] + $numbers[5] = 2 + 8 = 10
```

### Sliding Window

```php
<?php
# filename: sliding-window-pattern.php

declare(strict_types=1);

// Maximum sum of k consecutive elements
function maxSumSubarray(array $arr, int $k): int {
    if (count($arr) < $k) {
        throw new InvalidArgumentException("Array too small for window size");
    }

    $maxSum = $currentSum = array_sum(array_slice($arr, 0, $k));

    for ($i = $k; $i < count($arr); $i++) {
        $currentSum = $currentSum - $arr[$i - $k] + $arr[$i];
        $maxSum = max($maxSum, $currentSum);
    }

    return $maxSum;
}

// Usage example
$sales = [100, 200, 300, 400, 100, 200];
$maxThreeDaySales = maxSumSubarray($sales, 3);
// Returns: 900 (days 2-4: 200 + 300 + 400)
```

### Fast & Slow Pointers

```php
<?php
# filename: fast-slow-pointers-pattern.php

declare(strict_types=1);

// Detect cycle in linked list (Floyd's Cycle Detection)
function hasCycle(?ListNode $head): bool {
    if ($head === null) return false;

    $slow = $fast = $head;

    while ($fast !== null && $fast->next !== null) {
        $slow = $slow->next;
        $fast = $fast->next->next;

        if ($slow === $fast) return true;  // Cycle detected
    }

    return false;  // No cycle
}

// Also known as "Floyd's Tortoise and Hare" algorithm
// Time: O(n), Space: O(1)
```

::: tip Pattern Recognition
These three patterns solve **80% of array/linked list interview questions**. Master them and you'll recognize when to apply them instantly.
:::

---

## 🔍 Recognize Algorithms in Code

Learn to identify algorithms by their code patterns. This helps you understand existing code and spot optimization opportunities.

### Common Algorithm Signatures

**Two Nested Loops = O(n²) Brute Force**:
```php
<?php
# Pattern: Comparing all pairs

foreach ($arr as $i => $val1) {
    foreach ($arr as $j => $val2) {
        if ($val1 + $val2 === $target) {
            // O(n²) - check all combinations
        }
    }
}
```

**Two Pointers = O(n) Optimized**:
```php
<?php
# Pattern: Left and right pointers moving toward each other

$left = 0;
$right = count($arr) - 1;
while ($left < $right) {
    // O(n) - single pass through sorted array
}
```

**Divide in Half = O(log n) Binary Search**:
```php
<?php
# Pattern: Repeatedly dividing search space

$mid = (int)(($left + $right) / 2);
if ($arr[$mid] === $target) {
    return $mid;
}
// O(log n) - eliminates half each iteration
```

**Hash Table Lookup = O(n) Space-Time Tradeoff**:
```php
<?php
# Pattern: Build lookup table, then query

$seen = [];
foreach ($arr as $val) {
    if (isset($seen[$val])) {
        return true;  // Found duplicate
    }
    $seen[$val] = true;
}
// O(n) time, O(n) space - trade memory for speed
```

**Memoization Cache = Dynamic Programming**:
```php
<?php
# Pattern: Cache to avoid recomputation

static $cache = [];
if (isset($cache[$key])) {
    return $cache[$key];  // Return cached result
}
$result = expensiveCalculation($key);
$cache[$key] = $result;
// Overlapping subproblems → DP
```

**Sort Then Process = O(n log n) Foundation**:
```php
<?php
# Pattern: Sort enables faster algorithms

sort($arr);  // O(n log n)
// Now can use binary search O(log n)
// Or two pointers O(n)
// Total: O(n log n)
```

### Algorithm Spotting Cheat Sheet

| Code Pattern | Algorithm Type | Complexity | Use Case |
|--------------|----------------|------------|----------|
| Two nested loops | Brute force | O(n²) | Small data or first solution |
| `while ($left < $right)` | Two pointers | O(n) | Sorted array problems |
| Divide by 2 repeatedly | Binary search | O(log n) | Find in sorted data |
| Build hash table first | Hash table | O(n) | Fast lookups needed |
| Recursive + cache | Dynamic programming | Varies | Overlapping subproblems |
| Queue for nodes | BFS | O(V+E) | Shortest path (unweighted) |
| Stack/recursion for nodes | DFS | O(V+E) | Path finding, traversal |
| `sort()` then iterate | Sort-based | O(n log n) | Need ordering first |

::: info Quick Diagnosis
See nested loops? → Probably O(n²), look for optimization.  
See hash table? → Trading space for speed.  
See recursion? → Check for duplicate work (DP candidate).  
See sort first? → Likely O(n log n) is the best you can do.
:::

---

## 🎯 By Use Case

### E-Commerce

```php
<?php
# filename: ecommerce-examples.php

// Product recommendations - collaborative filtering
$similarUsers = findSimilarUsers($userId);  // Graph/clustering
$productScores = calculateScores($similarUsers);  // Aggregation
arsort($productScores);  // Sort by score
$recommendations = array_slice(array_keys($productScores), 0, 10);

// Inventory sorting - priority queue
$orders = new SplPriorityQueue();
$orders->insert($order1, $order1->priority);
$topOrder = $orders->extract();
```

### APIs

```php
<?php
# filename: api-examples.php

// Rate limiting - token bucket
class RateLimiter {
    public function checkLimit(string $userId): bool {
        $key = "rate:$userId";
        $redis = new Redis();
        $redis->connect('127.0.0.1');

        $current = $redis->incr($key);
        if ($current === 1) {
            $redis->expire($key, 60);  // 1 minute window
        }

        return $current <= 100;  // 100 requests per minute
    }
}

// Response caching - LRU cache
$cache = new LRUCache(1000);
$response = $cache->get($cacheKey) ?? $cache->set($cacheKey, generateResponse());
```

### Search

```php
<?php
# filename: search-use-case.php

// Autocomplete - Trie data structure
class AutocompleteNode {
    public array $children = [];
    public bool $isEnd = false;
    public array $suggestions = [];
}

// Full-text search - inverted index + TF-IDF
$index = buildInvertedIndex($documents);  // term → document IDs
$scores = calculateTFIDF($queryTerms, $index);  // Relevance scores
arsort($scores);
```

---

## 💡 Quick Wins

### 1. Use PHP's Built-in Functions (Don't Reinvent the Wheel)

```php
<?php
# filename: use-built-ins.php

declare(strict_types=1);

// ❌ DON'T: Implement basic algorithms yourself
function mySort(array $arr): array {
    // 50 lines of sorting code...
    return $arr;
}

// ✅ DO: Use PHP's optimized built-ins
sort($array);  // Implemented in C, handles edge cases
$found = in_array($item, $array, true);  // Strict comparison
$sum = array_sum($numbers);
$filtered = array_filter($array, $callback);
$unique = array_unique($array);
$reversed = array_reverse($array);
$keys = array_keys($array);
$values = array_values($array);
```

**When to use custom algorithms**:
- ✅ Specific business logic not in built-ins
- ✅ Performance-critical (after profiling proves it!)
- ✅ Unique constraints PHP doesn't handle
- ✅ Learning/interview preparation

**When to use built-ins**:
- ✅ 99% of the time (seriously!)
- ✅ Standard operations (sort, search, filter, map)
- ✅ Production code (battle-tested, optimized)
- ✅ When you want maintainable code

### 2. Cache Expensive Operations

```php
<?php
# filename: cache-expensive-ops.php

// ❌ Recalculates every time
function getStats($userId) {
    return calculateExpensiveStats($userId);  // 500ms
}

// ✅ Cache for 5 minutes
function getStats($userId) {
    $key = "stats:$userId";
    return $cache->remember($key, 300, fn() => calculateExpensiveStats($userId));
}
```

### 3. Use Generators for Large Data

```php
<?php
# filename: use-generators.php

// ❌ 1GB memory for 1M records
$users = User::all();
foreach ($users as $user) {
    process($user);
}

// ✅ Constant memory
$users = User::cursor();  // Returns generator
foreach ($users as $user) {
    process($user);
}
```

### 4. Batch Database Operations

```php
<?php
# filename: batch-operations.php

// ❌ 1000 queries
foreach ($users as $user) {
    DB::insert('INSERT INTO logs (user_id) VALUES (?)', [$user->id]);
}

// ✅ 1 query
$values = array_map(fn($u) => "({$u->id})", $users);
DB::insert('INSERT INTO logs (user_id) VALUES ' . implode(',', $values));
```

### 5. Enable OPcache in Production

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0  ; Production only
opcache.jit=tracing  ; PHP 8.0+ JIT compiler
opcache.jit_buffer_size=128M
```

::: warning Production Only
**Never** set `opcache.validate_timestamps=0` in development! This disables file change detection. Your code changes won't take effect without restarting PHP-FPM.
:::

---

## 🎓 Learning Paths

### "I Have 1 Hour"

1. Read this page ✓
2. [Introduction & Big O](/series/php-algorithms/chapters/00-introduction-to-algorithms/) (15 min)
3. [Hash Tables](/series/php-algorithms/chapters/13-hash-tables-hash-functions/) (20 min)
4. [Caching](/series/php-algorithms/chapters/27-caching-memoization-strategies/) (25 min)

### "I'm Preparing for Interviews"

1. All Sorting (Chapters 5-10)
2. Binary Search (Chapter 12)
3. Trees & Traversals (Chapters 18-19)
4. DFS & BFS (Chapters 22-23)
5. Dynamic Programming (Chapters 25-26)

→ [Full Interview Prep Path](/series/php-algorithms/#path-2-interview-preparation)

### "I Want to Optimize My App"

1. [Benchmarking](/series/php-algorithms/chapters/02-benchmarking-performance-testing/)
2. [Algorithm Selection](/series/php-algorithms/chapters/28-algorithm-selection-guide/)
3. [Caching](/series/php-algorithms/chapters/27-caching-memoization-strategies/)
4. [Performance](/series/php-algorithms/chapters/29-performance-optimization/)
5. [Case Studies](/series/php-algorithms/chapters/30-real-world-case-studies/)

→ [Full Optimization Path](/series/php-algorithms/#path-3-production-optimization)

---

## 📖 Essential References

**Quick Lookups**:
- [Complexity Cheat Sheet](/series/php-algorithms/appendices/a-complexity-cheat-sheet/) - Big O reference
- [PHP Performance Tips](/series/php-algorithms/appendices/b-php-performance-tips/) - Optimization guide
- [Glossary](/series/php-algorithms/appendices/c-glossary/) - Term definitions

**Common Tasks**:
- Sorting: [Chapters 5-10](/series/php-algorithms/chapters/05-bubble-selection-sort/)
- Searching: [Chapters 11-14](/series/php-algorithms/chapters/11-linear-search/)
- Graphs: [Chapters 21-24](/series/php-algorithms/chapters/21-graph-representations/)
- Optimization: [Chapters 27-30](/series/php-algorithms/chapters/27-caching-memoization-strategies/)

---

## ⚠️ Common Pitfalls to Avoid

### 1. Premature Optimization

```php
<?php
# filename: avoid-premature-optimization.php

// ❌ DON'T: Optimize before measuring
// "I'll use a complex algorithm because it's O(n log n)"

// ✅ DO: Profile first, then optimize
$start = microtime(true);
simpleAlgorithm();  // Start simple
$time = microtime(true) - $start;

if ($time > 0.1) {  // Only if it's actually slow
    optimizedAlgorithm();
}
```

**Rule**: Make it work, make it right, make it fast - **in that order**.

::: danger Premature Optimization Kills Projects
"Premature optimization is the root of all evil" — Donald Knuth. **Profile first**, optimize second. 97% of optimizations are wasted effort if applied to the wrong place.
:::

### 2. Wrong Data Structure

```php
<?php
# filename: choose-right-data-structure.php

// ❌ BAD: Using array with in_array() for lookups
$validUsers = [1, 2, 3, 4, 5, /* ...1000 more */];
if (in_array($userId, $validUsers)) {  // O(n) - SLOW!
    // ...
}

// ✅ GOOD: Use associative array for O(1) lookups
$validUsers = [1 => true, 2 => true, 3 => true, /* ... */];
if (isset($validUsers[$userId])) {  // O(1) - FAST!
    // ...
}
```

### 3. Hidden N+1 Queries (Laravel)

```php
<?php
# filename: avoid-n-plus-one.php

// ❌ BAD: 1 + N queries
$users = User::all();  // 1 query
foreach ($users as $user) {
    echo $user->posts->count();  // N queries - ONE PER USER!
}
// Result: 1 + 1000 users = 1001 queries! 🔥

// ✅ GOOD: 2 queries total
$users = User::withCount('posts')->get();  // 2 queries with JOIN
foreach ($users as $user) {
    echo $user->posts_count;  // No query!
}
// Result: 2 queries total (500x faster) ⚡
```

::: warning N+1 Query Detector
Enable Laravel Debugbar or Telescope in development to catch N+1 queries. This is the **#1 performance killer** in Laravel apps.
:::

### 4. Memory Leaks in Loops

```php
<?php
# filename: avoid-memory-leaks.php

// ❌ BAD: Memory accumulates
$results = [];
foreach ($hugeDataset as $item) {
    $results[] = process($item);  // Grows without bounds
}

// ✅ GOOD: Process and discard
foreach ($hugeDataset as $item) {
    $result = process($item);
    sendToOutput($result);  // Process one at a time
    unset($result);  // Free memory
}
```

### 5. Inefficient String Building

```php
<?php
# filename: efficient-string-building.php

// ❌ BAD: O(n²) - Creates new string each time
$html = '';
foreach ($items as $item) {
    $html .= "<li>$item</li>";  // SLOW for large arrays
}

// ✅ GOOD: O(n) - Build array then join
$parts = [];
foreach ($items as $item) {
    $parts[] = "<li>$item</li>";
}
$html = implode('', $parts);
```

---

## 🔒 Quick Security Tips

### 1. Always Use Parameterized Queries

```php
<?php
# filename: secure-queries.php

// ❌ DANGER: SQL injection
$result = $db->query("SELECT * FROM users WHERE id = {$_GET['id']}");

// ✅ SAFE: Prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_GET['id']]);
```

### 2. Avoid Timing Attacks

```php
<?php
# filename: avoid-timing-attacks.php

// ❌ BAD: Vulnerable to timing attacks
if ($userHash === $expectedHash) {  // Character-by-character comparison
    return true;
}

// ✅ GOOD: Constant-time comparison
if (hash_equals($expectedHash, $userHash)) {
    return true;
}
```

### 3. Use Secure Random for Tokens

```php
<?php
# filename: secure-random.php

// ❌ BAD: Predictable
$token = md5(time() . rand());

// ✅ GOOD: Cryptographically secure
$token = bin2hex(random_bytes(32));
```

---

## 🧰 Essential Tools

### Profiling & Debugging

```php
<?php
# filename: profiling-tools.php

// 1. Quick memory check
echo "Memory: " . memory_get_usage() / 1024 / 1024 . " MB\n";
echo "Peak: " . memory_get_peak_usage() / 1024 / 1024 . " MB\n";

// 2. Query logging (Laravel)
DB::enableQueryLog();
// ... run queries ...
dd(DB::getQueryLog());

// 3. Execution time breakdown
$times = [];
$start = microtime(true);
step1();
$times['step1'] = microtime(true) - $start;

$start = microtime(true);
step2();
$times['step2'] = microtime(true) - $start;

print_r($times);
```

### Recommended Tools

- **Xdebug**: Development profiling (slow, detailed)
- **Blackfire**: Production profiling (fast, safe)
- **Telescope** (Laravel): Request/query monitoring
- **Clockwork**: Browser-based profiling
- **New Relic/DataDog**: APM for production

---

## 🎯 Framework-Specific Quick Wins

### Laravel

```php
<?php
# filename: laravel-optimizations.php

// 1. Eager loading (prevents N+1)
$users = User::with('posts')->get();  // 2 queries instead of N+1

// 2. Chunk large datasets
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        // Process in batches
    }
});

// 3. Cache query results
$users = Cache::remember('users', 3600, function () {
    return User::all();
});

// 4. Use whereIn instead of loop
$userIds = [1, 2, 3, 4, 5];
$users = User::whereIn('id', $userIds)->get();  // 1 query, not 5

// 5. Select only needed columns
$users = User::select(['id', 'name'])->get();  // Not SELECT *
```

### Symfony

```php
<?php
# filename: symfony-optimizations.php

// 1. Query builder for complex queries
$qb = $entityManager->createQueryBuilder();
$users = $qb->select('u')
    ->from('App\Entity\User', 'u')
    ->where('u.active = :active')
    ->setParameter('active', true)
    ->getQuery()
    ->getResult();

// 2. Batch processing
$batchSize = 1000;
$i = 0;
foreach ($users as $user) {
    $entityManager->persist($user);
    if (($i % $batchSize) === 0) {
        $entityManager->flush();
        $entityManager->clear();
    }
    $i++;
}

// 3. HTTP cache
$response->setCache([
    'public' => true,
    'max_age' => 3600,
]);
```

---

## ❓ FAQs

**Q: Should I always use the most efficient algorithm?**
A: No! Use the simplest algorithm that meets your requirements. `sort()` is fine for 99% of cases. **Optimize only when you have measured performance issues.**

**Q: When should I NOT implement a custom algorithm?**
A: When PHP has a built-in that does it (`sort()`, `array_filter()`, `array_map()`), when you haven't profiled yet, when the built-in is "good enough," or when maintainability matters more than micro-optimizations.

**Q: When do I really need to know this stuff?**
A: Job interviews, optimizing slow code, working with large datasets, understanding framework internals, debugging production issues.

**Q: Can't PHP frameworks handle this for me?**
A: Frameworks help, but you need to understand when to use `whereIn()` vs `where()`, when to cache, when to eager load, etc. Frameworks give you tools, but you need to know when to use them.

**Q: What's the #1 optimization I should know?**
A: **Caching**. It's the biggest bang for your buck. Cache database queries, API responses, expensive calculations. A simple cache can turn a 500ms request into a 5ms request.

**Q: How do I know if my code is slow?**
A: **Measure**! Use `microtime()`, Xdebug, or Blackfire. Never optimize without profiling first. "Premature optimization is the root of all evil."

**Q: Should I learn all the algorithms in this series?**
A: Start with the essentials (sorting, searching, hash tables, caching). Learn advanced topics (dynamic programming, graph algorithms) when you need them or for interview prep.

**Q: Where do I start if I'm completely new?**
A: [Chapter 1: Introduction](/series/php-algorithms/chapters/00-introduction-to-algorithms/) for comprehensive learning, or continue with this guide for copy-paste solutions.

**Q: What about async/await in PHP?**
A: PHP 8.1+ supports fibers, but for practical async use ReactPHP, Swoole, or Amp. See [Chapter 31: Concurrent Algorithms](/series/php-algorithms/chapters/31-concurrent-algorithms/).

**Q: How do I know if my data is "large" enough to worry about?**
A: If it's < 10,000 items and fits in a PHP array comfortably, don't overthink it. Use simple algorithms. If it's > 100,000 or causes memory issues, then optimize. Profile first!

**Q: How do I recognize what algorithm is being used in code I'm reading?**
A: Look for patterns: nested loops = O(n²), binary divide = O(log n), hash table = space/time tradeoff, recursion + cache = dynamic programming. See the "Recognize Algorithms in Code" section above.

---

## 🚀 Next Steps

**Ready to dive deeper?**
- [Start Chapter 1](/series/php-algorithms/chapters/00-introduction-to-algorithms/) - Full course
- [See all paths](/series/php-algorithms/#learning-paths) - Choose your journey
- [Use case guide](/series/php-algorithms/#navigation-by-use-case) - Find what you need

**Need help?**
- [Glossary](/series/php-algorithms/appendices/c-glossary/) - Look up terms
- [Cheat Sheet](/series/php-algorithms/appendices/a-complexity-cheat-sheet/) - Quick reference
- Review this guide - Come back anytime

---

## 💾 Copy-Paste Snippets

### Benchmark Function

```php
<?php
# filename: benchmark-helper.php

declare(strict_types=1);

function benchmark(callable $fn, array $args = [], int $iterations = 1): float {
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $fn(...$args);
        $times[] = (microtime(true) - $start) * 1000;
    }

    return $iterations === 1 ? $times[0] : array_sum($times) / count($times);
}

// Single run
echo "Execution time: " . benchmark(fn() => expensiveFunction()) . "ms\n";

// Average of 100 runs for accuracy
echo "Average time: " . benchmark(fn() => quickFunction(), [], 100) . "ms\n";
```

### Simple Cache Class

```php
<?php
# filename: cache-class.php

declare(strict_types=1);

class Cache {
    private static array $store = [];

    public static function remember(string $key, int $ttl, callable $callback): mixed {
        // Check if cached and not expired
        if (isset(self::$store[$key]) && self::$store[$key]['expires'] > time()) {
            return self::$store[$key]['value'];
        }

        // Generate and cache
        $value = $callback();
        self::$store[$key] = [
            'value' => $value,
            'expires' => time() + $ttl,
            'hits' => 0
        ];
        return $value;
    }

    public static function forget(string $key): void {
        unset(self::$store[$key]);
    }

    public static function flush(): void {
        self::$store = [];
    }

    public static function stats(): array {
        return [
            'keys' => count(self::$store),
            'memory' => memory_get_usage()
        ];
    }
}
```

### Binary Search

```php
<?php
# filename: binary-search-helper.php

declare(strict_types=1);

function binarySearch(array $arr, mixed $target): int {
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) return $mid;
        if ($arr[$mid] < $target) $left = $mid + 1;
        else $right = $mid - 1;
    }

    return -1;  // Not found
}

// Usage
$numbers = [1, 3, 5, 7, 9, 11, 13, 15];
$index = binarySearch($numbers, 7);  // Returns: 3
$missing = binarySearch($numbers, 6);  // Returns: -1
```

### File Stream Generator

```php
<?php
# filename: file-stream-generator.php

declare(strict_types=1);

function streamFile(string $filename): Generator {
    if (!file_exists($filename)) {
        throw new RuntimeException("File not found: $filename");
    }

    $handle = fopen($filename, 'r');
    if ($handle === false) {
        throw new RuntimeException("Cannot open file: $filename");
    }

    try {
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false) {
                yield $line;
            }
        }
    } finally {
        fclose($handle);
    }
}

// Usage: Process 10GB file with constant memory
foreach (streamFile('huge.log') as $lineNumber => $line) {
    if (str_contains($line, 'ERROR')) {
        echo "Line $lineNumber: $line";
    }
}
```

---


---

<div class="series-cta">
  <h2>Ready for More?</h2>
  <p>This guide covers the essentials. For deep understanding, continue with the full series.</p>
  <a href="/series/php-algorithms/chapters/00-introduction-to-algorithms" class="cta-button">Start Full Course →</a>
</div>
