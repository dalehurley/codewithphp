# PHP Algorithms - Code Samples

Comprehensive, production-ready PHP code samples for the **[PHP Algorithms Series](https://github.com/dalehurley/codewithphp/tree/main/docs/series/php-algorithms)**.

## 📊 Statistics

- **Total Code Files**: 99 PHP files
- **Total Documentation**: 37 README files
- **Total Size**: 1.2MB
- **Chapters Covered**: 37 (Chapters 00-36)
- **PHP Version**: 8.0+ with modern syntax
- **Lines of Code**: ~30,000+

## 🎯 What's Included

Every code sample in this repository features:

✅ **Complete, Runnable Code** - Execute directly with `php filename.php`
✅ **PHP 8.0+ Modern Syntax** - Typed properties, constructor promotion, match expressions
✅ **Comprehensive PHPDoc** - Full documentation for all classes and methods
✅ **Proper Error Handling** - Validation, exceptions, edge case coverage
✅ **Real-World Examples** - Practical applications with performance benchmarks
✅ **Educational Value** - Clear explanations, algorithm visualizations, complexity analysis

## 📚 Quick Start

### Run a Sample

```bash
# Navigate to code samples directory
cd /home/user/codewithphp/code-samples/php-algorithms

# Run any example
php chapter-00/01-quick-start-examples.php
php chapter-12/01-basic-binary-search.php
php chapter-25/01-fibonacci-dp.php
```

### Explore a Chapter

```bash
# View chapter README
cat chapter-18/README.md

# Run all examples in a chapter
cd chapter-18
for file in *.php; do php "$file"; echo ""; done
```

### Use in Your Project

```php
<?php
// Include any implementation
require_once '/path/to/code-samples/php-algorithms/chapter-18/01-binary-search-tree.php';

$bst = new BinarySearchTree();
$bst->insert(50);
$bst->insert(30);
$bst->insert(70);

echo $bst->search(30) ? "Found!" : "Not found";
```

## 🗂️ Chapter Index

### Part 1: Foundations (Chapters 00-04)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [00](chapter-00/) | Quick Start Guide | 3 | Binary search, caching, common patterns |
| [01](chapter-01/) | Big O Notation | 2 | Complexity examples (O(1) to O(n!)) |
| [02](chapter-02/) | Benchmarking | 1 | Performance testing framework |
| [03](chapter-03/) | Recursion | 1 | Fibonacci, factorial, backtracking |
| [04](chapter-04/) | Problem Solving | 1 | Two pointers, sliding window, divide & conquer |

### Part 2: Sorting (Chapters 05-10)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [05](chapter-05/) | Bubble & Selection Sort | 1 | Basic O(n²) sorts with visualizations |
| [06](chapter-06/) | Insertion & Merge Sort | 6 | Adaptive sorting, divide & conquer |
| [07](chapter-07/) | Quick Sort | 5 | Pivot strategies, 3-way partitioning, QuickSelect |
| [08](chapter-08/) | Heap Sort | 3 | Heapify, priority queues, task scheduling |
| [09](chapter-09/) | Comparing Sorts | 2 | Performance benchmarks, decision matrix |
| [10](chapter-10/) | PHP Built-in Sorts | 3 | sort(), usort(), custom comparators |

### Part 3: Searching (Chapters 11-14)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [11](chapter-11/) | Linear Search | 5 | Basic search, sentinel, jump search, predicates |
| [12](chapter-12/) | Binary Search | 3 | Iterative, recursive, find first/last occurrence |
| [13](chapter-13/) | Hash Tables | 4 | Chaining, open addressing, hash functions |
| [14](chapter-14/) | String Search | 3 | Naive, KMP, Boyer-Moore, Rabin-Karp |

### Part 4: Data Structures (Chapters 15-20)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [15](chapter-15/) | Arrays | 3 | Dynamic arrays, circular buffer, sliding window |
| [16](chapter-16/) | Linked Lists | 4 | Singly/doubly linked, cycle detection, palindrome |
| [17](chapter-17/) | Stacks & Queues | 2 | Expression evaluation, task scheduling |
| [18](chapter-18/) | Binary Search Trees | 1 | Insert, delete, search, range queries |
| [19](chapter-19/) | Tree Traversals | 1 | In-order, pre-order, post-order, level-order, Morris |
| [20](chapter-20/) | Balanced Trees | 1 | AVL rotations, self-balancing |

### Part 5: Graphs (Chapters 21-24)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [21](chapter-21/) | Graph Representations | 4 | Adjacency matrix/list, social networks |
| [22](chapter-22/) | Depth-First Search | 4 | DFS traversal, cycle detection, topological sort |
| [23](chapter-23/) | Breadth-First Search | 3 | BFS traversal, shortest paths, bipartite graphs |
| [24](chapter-24/) | Dijkstra's Algorithm | 2 | Weighted shortest paths, GPS navigation |

### Part 6: Dynamic Programming (Chapters 25-26)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [25](chapter-25/) | DP Fundamentals | 4 | Fibonacci, coin change, knapsack, LCS |
| [26](chapter-26/) | Advanced DP | 5 | Matrix chain, TSP, edit distance, digit DP |

### Part 7: Optimization (Chapters 27-30)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [27](chapter-27/) | Caching & Memoization | 3 | LRU cache, multi-level caching |
| [28](chapter-28/) | Algorithm Selection | 2 | Decision frameworks, performance estimation |
| [29](chapter-29/) | Performance Optimization | 2 | Benchmarking, memory optimization, PHP 8+ |
| [30](chapter-30/) | Real-World Case Studies | 2 | API optimization (850ms → 45ms) |

### Part 8: Advanced Topics (Chapters 31-36)

| Chapter | Topics | Files | Key Algorithms |
|---------|--------|-------|----------------|
| [31](chapter-31/) | Concurrent Algorithms | 5 | ReactPHP, Swoole, worker pools, circuit breaker |
| [32](chapter-32/) | Probabilistic Algorithms | 4 | Bloom filter, HyperLogLog, Count-Min Sketch |
| [33](chapter-33/) | String Algorithms | 3 | Aho-Corasick, suffix arrays, Z-algorithm |
| [34](chapter-34/) | Geometric Algorithms | 6 | Convex hull, line intersection, collision detection |
| [35](chapter-35/) | Cryptographic Algorithms | 5 | Hashing, encryption, password security, TOTP |
| [36](chapter-36/) | Stream Processing | 3 | Sliding windows, rate limiting, token bucket |

## 🎓 Learning Paths

### Beginner (Start Here!)
1. [Chapter 00](chapter-00/) - Quick Start Guide
2. [Chapter 01](chapter-01/) - Big O Notation
3. [Chapter 05](chapter-05/) - Basic Sorting
4. [Chapter 11](chapter-11/) - Linear Search
5. [Chapter 12](chapter-12/) - Binary Search

### Interview Preparation
1. [Chapter 12](chapter-12/) - Binary Search
2. [Chapter 15](chapter-15/) - Arrays & Patterns
3. [Chapter 16](chapter-16/) - Linked Lists
4. [Chapter 18-19](chapter-18/) - Trees & Traversals
5. [Chapter 22-23](chapter-22/) - DFS & BFS
6. [Chapter 25](chapter-25/) - Dynamic Programming

### Production Optimization
1. [Chapter 02](chapter-02/) - Benchmarking
2. [Chapter 13](chapter-13/) - Hash Tables
3. [Chapter 27](chapter-27/) - Caching Strategies
4. [Chapter 29](chapter-29/) - Performance Optimization
5. [Chapter 30](chapter-30/) - Real-World Case Studies
6. [Chapter 32](chapter-32/) - Probabilistic Algorithms

### Advanced Topics
1. [Chapter 26](chapter-26/) - Advanced DP
2. [Chapter 31](chapter-31/) - Concurrent Algorithms
3. [Chapter 33](chapter-33/) - String Algorithms
4. [Chapter 35](chapter-35/) - Cryptographic Algorithms
5. [Chapter 36](chapter-36/) - Stream Processing

## 🚀 Featured Examples

### High-Performance Caching (Chapter 00)
```php
php chapter-00/01-quick-start-examples.php
```
Demonstrates **16,000x speedup** with proper caching.

### AVL Tree Self-Balancing (Chapter 20)
```php
php chapter-20/01-avl-tree.php
```
Shows **50,000x speedup** over unbalanced trees.

### API Optimization Case Study (Chapter 30)
```php
php chapter-30/01-api-optimization-case-study.php
```
Real optimization: **850ms → 45ms** (18.9x faster), **77% cost savings**.

### Bloom Filter Space Efficiency (Chapter 32)
```php
php chapter-32/01-bloom-filter.php
```
Demonstrates **650x memory reduction** over exact sets.

### Multi-Pattern String Matching (Chapter 33)
```php
php chapter-33/01-aho-corasick.php
```
Aho-Corasick algorithm **10x faster** than naive approach.

## 🛠️ Requirements

- **PHP**: 8.0 or higher
- **Extensions**:
  - Standard library (included)
  - Optional: ext-swoole (Chapter 31)
  - Optional: ext-redis (Chapter 27)
  - Optional: ext-sodium (Chapter 35)
- **Dependencies**: None (all code is self-contained)

## 📖 Code Quality

All code samples follow these standards:

### Modern PHP
```php
declare(strict_types=1);

class Example {
    public function __construct(
        private int $value,  // Property promotion (PHP 8.0)
        private ?string $name = null,  // Nullsafe operator
    ) {}

    public function getType(): string {
        return match($this->value) {  // Match expression (PHP 8.0)
            0 => 'zero',
            1 => 'one',
            default => 'many',
        };
    }
}
```

### Comprehensive Documentation
```php
/**
 * Performs binary search on a sorted array
 *
 * @param array<int> $arr Sorted array of integers
 * @param int $target Value to search for
 * @return int|null Index of target, or null if not found
 *
 * @complexity Time: O(log n), Space: O(1)
 */
function binarySearch(array $arr, int $target): ?int {
    // Implementation...
}
```

### Production-Ready Error Handling
```php
class HashTable {
    public function get(string $key): mixed {
        if (!$this->has($key)) {
            throw new OutOfBoundsException("Key not found: $key");
        }
        return $this->data[$key];
    }
}
```

## 🧪 Testing

Run all tests for a specific chapter:

```bash
# Test Chapter 18 (Binary Search Trees)
cd chapter-18
php 01-binary-search-tree.php

# Expected output includes:
# - Test results
# - Performance metrics
# - Visual tree representations
# - Complexity analysis
```

Run all examples in sequence:

```bash
# Run every code sample (may take several minutes)
cd /home/user/codewithphp/code-samples/php-algorithms

for i in {00..36}; do
    chapter="chapter-$(printf "%02d" $i)"
    if [ -d "$chapter" ]; then
        echo "=== Testing $chapter ==="
        for file in "$chapter"/*.php 2>/dev/null; do
            [ -f "$file" ] && php "$file"
        done
    fi
done
```

## 📈 Performance Highlights

| Algorithm | Before | After | Improvement |
|-----------|--------|-------|-------------|
| Cache Hit (Ch 00) | 1600ms | 0.1ms | **16,000x faster** |
| AVL vs Unbalanced Tree (Ch 20) | O(n) | O(log n) | **50,000x faster** |
| API Response Time (Ch 30) | 850ms | 45ms | **18.9x faster** |
| Fibonacci with Memoization (Ch 03) | 182s | 0.0001s | **1,820,000x faster** |
| Manacher's Palindrome (Ch 33) | O(n³) | O(n) | **200x faster** (n=1000) |

## 🔗 Related Resources

- **Documentation**: [PHP Algorithms Series](https://github.com/dalehurley/codewithphp/tree/main/docs/series/php-algorithms)
- **GitHub Repository**: [dalehurley/codewithphp](https://github.com/dalehurley/codewithphp)
- **Appendix A**: [Complexity Cheat Sheet](../../docs/series/php-algorithms/appendices/appendix-a-complexity-cheat-sheet.md)
- **Appendix B**: [PHP Performance Tips](../../docs/series/php-algorithms/appendices/appendix-b-php-performance-tips.md)

## 📝 License

This code is part of the PHP Algorithms educational series. See the main repository for license information.

## 🤝 Contributing

Found an issue or have an improvement? Please open an issue or pull request in the [main repository](https://github.com/dalehurley/codewithphp).

## 📧 Support

For questions or feedback about these code samples, please refer to the main documentation or open an issue on GitHub.

---

**Happy Coding!** 🚀

*Last Updated: 2025*
