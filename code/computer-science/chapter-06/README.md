# Chapter 06: Hash Tables - Code Examples

Complete, runnable code examples demonstrating hash table concepts from Chapter 6.

## Quick Start

```bash
# Run any example
php 01-hash-function-basics.php
php 02-hash-table-chaining.php
php 03-hash-table-open-addressing.php
# ... etc
```

## Examples Overview

### 01-hash-function-basics.php
**Concepts**: Hash functions, collision detection, distribution quality

Demonstrates:
- djb2 hash algorithm for strings
- Division and multiplication methods
- Collision detection and handling
- Distribution quality testing with variance analysis
- Determinism verification

**Run time**: ~1 second

---

### 02-hash-table-chaining.php
**Concepts**: Separate chaining collision resolution

Demonstrates:
- Complete hash table implementation using linked lists
- Insert, search, delete operations (all O(1) average)
- Load factor calculation and monitoring
- Chain length statistics
- Visualization of bucket distribution
- Performance benchmarks

**Run time**: ~1 second

---

### 03-hash-table-open-addressing.php
**Concepts**: Linear probing, open addressing

Demonstrates:
- Hash table with linear probing
- Tombstone markers for deletion
- Clustering effects visualization
- Probe sequence tracking
- Comparison with chaining approach
- Load factor impact on performance

**Run time**: ~1 second

---

### 04-two-sum-problem.php
**Concepts**: Classic hash table interview problem

Demonstrates:
- O(n) solution using hash table vs O(n²) brute force
- Complement pattern for pair finding
- Performance benchmarks showing 50-100x speedup
- Edge cases (no solution, multiple pairs)
- Practical time-space tradeoff

**Run time**: ~2 seconds (includes benchmarks)

---

### 05-frequency-counter.php
**Concepts**: Frequency counting patterns

Demonstrates:
- Character frequency counting O(n)
- Anagram detection using frequency maps
- First non-repeating character
- Most frequent element
- Duplicate detection and finding
- Word frequency analysis

**Run time**: ~1 second

---

### 06-group-anagrams.php
**Concepts**: Advanced hash table grouping

Demonstrates:
- Grouping anagrams using sorted string as key
- Alternative frequency signature approach
- Performance comparison: sorting O(n*k log k) vs frequency O(n*k)
- Pattern matching with canonical forms
- Handling large datasets efficiently

**Run time**: ~2 seconds (includes performance tests)

---

### 07-hash-set-implementation.php
**Concepts**: Set data structure using hash table

Demonstrates:
- Complete HashSet implementation
- Set operations: add, remove, contains (all O(1))
- Set theory: union, intersection, difference
- Subset, superset, and disjoint checks
- Duplicate removal use case
- String and numeric sets

**Run time**: ~1 second

---

### 08-lru-cache.php
**Concepts**: LRU Cache - combining hash table + doubly linked list

Demonstrates:
- O(1) get and put operations
- Least Recently Used eviction policy
- Doubly linked list for order tracking
- Hash table for O(1) lookups
- Page cache simulation
- Performance benchmarks on large caches

**Run time**: ~2 seconds

---

### 09-hash-table-resizing.php
**Concepts**: Dynamic resizing and load factor management

Demonstrates:
- Automatic resizing when load factor exceeds threshold
- Rehashing all elements during resize
- Growth pattern visualization (2 → 4 → 8 → 16...)
- Amortized O(1) analysis
- Load factor impact on performance
- Optimal threshold selection (0.75)

**Run time**: ~3 seconds (includes benchmarks)

---

### 10-practical-applications.php
**Concepts**: Real-world hash table applications

Demonstrates:
- Word frequency analyzer (text analysis)
- Database index simulation
- Spell checker with suggestions
- URL shortener service
- Simple cache implementation
- When to use hash tables vs other structures
- Performance comparison across data structures

**Run time**: ~2 seconds

---

## Running All Examples

```bash
# Run all examples in sequence
for i in {01..10}; do
    echo "=== Running example $i ==="
    php $(ls ${i}*.php)
    echo ""
done
```

## Dependencies

- PHP 8.2+ (uses constructor property promotion, typed properties)
- No external dependencies required

## Learning Path

**Recommended order:**
1. Start with `01-hash-function-basics.php` to understand hashing
2. Learn chaining with `02-hash-table-chaining.php`
3. Study open addressing in `03-hash-table-open-addressing.php`
4. Practice classic problem with `04-two-sum-problem.php`
5. Master frequency patterns in `05-frequency-counter.php`
6. Solve grouping problem with `06-group-anagrams.php`
7. Implement sets with `07-hash-set-implementation.php`
8. Build LRU cache with `08-lru-cache.php`
9. Understand resizing with `09-hash-table-resizing.php`
10. See real applications in `10-practical-applications.php`

## Key Takeaways

After running these examples, you'll understand:

✅ **Hash Function Fundamentals**
- How hash functions distribute keys uniformly
- Collision detection and handling
- Importance of determinism and speed
- Impact of hash quality on performance

✅ **Collision Resolution Strategies**
- Separate chaining: Simple, handles high load factors
- Open addressing: Cache-friendly, less memory overhead
- Linear probing and clustering effects
- Tombstone markers for deletion

✅ **Hash Table Operations**
- Insert: O(1) average, critical for fast data ingestion
- Search: O(1) average, enables instant lookups
- Delete: O(1) average with proper collision handling
- All degrade to O(n) worst case with poor hash function

✅ **Advanced Applications**
- LRU Cache: O(1) operations combining hash + linked list
- Dynamic resizing: Amortized O(1) despite occasional O(n) resize
- Set operations: Union, intersection, difference
- Pattern matching: Anagrams, frequency analysis

✅ **Practical Insights**
- Load factor sweet spot: ~0.75 for balanced performance
- Hash tables excel at lookups but don't preserve order
- Complement pattern solves many "find pair" problems
- Canonical forms (sorted, frequency) enable grouping

## Complexity Cheat Sheet

| Operation | Hash Table (avg) | Hash Table (worst) | When Worst Case? |
|-----------|------------------|-------------------|------------------|
| Search | O(1) | O(n) | All keys collide |
| Insert | O(1) | O(n) | Resize + collisions |
| Delete | O(1) | O(n) | All keys collide |
| Iterate All | O(n) | O(n) | Always |
| Find Min/Max | O(n) | O(n) | No ordering |
| Range Query | O(n) | O(n) | No ordering |

**Load Factor Impact:**
- < 0.5: Wasted space, very fast lookups
- ~0.75: Optimal balance (industry standard)
- \> 1.0: Long chains, degraded performance
- \> 2.0: Approaching O(n) search time

**Collision Resolution Comparison:**

| Aspect | Chaining | Open Addressing |
|--------|----------|-----------------|
| Implementation | Linked lists | Array with probing |
| Cache Performance | Worse (pointer chasing) | Better (contiguous) |
| Load Factor | Can exceed 1.0 | Must stay < 1.0 |
| Deletion | Simple (unlink) | Requires tombstones |
| Memory | Extra pointers | More compact |

## Common Pitfalls

⚠️ **Poor hash function**: Using modulo on sequential keys (1, 2, 3...) with power-of-2 table size causes clustering. Always use a good hash algorithm (djb2, FNV, MurmurHash).

⚠️ **Ignoring load factor**: Letting load factor grow unchecked degrades performance to O(n). Monitor and resize when threshold exceeded.

⚠️ **Mutable keys**: Changing a key after insertion breaks the hash table. Always use immutable keys (strings, numbers, frozen objects).

⚠️ **Assuming order**: Hash tables don't preserve insertion order (unless using LinkedHashMap/OrderedDict). Don't rely on iteration order.

⚠️ **Hash flooding attacks**: Malicious inputs can cause all keys to collide, creating O(n²) DoS. Use randomized hashing or cryptographic hashes for user input.

⚠️ **Confusing hash table with sorted structures**: Need min/max or range queries? Use BST or sorted array instead.

## Performance Benchmarks

Based on examples in this chapter (1000 elements):

**Two-Sum Problem:**
- Brute force O(n²): ~50-100 ms
- Hash table O(n): ~1-2 ms
- **Speedup: 50-100x**

**LRU Cache:**
- 10,000 random gets: ~1-5 ms
- Average get time: ~0.0001-0.0005 ms
- Confirms O(1) performance

**Dynamic Resizing:**
- 10,000 inserts with resizing: ~5-10 ms
- Amortized cost per insert: ~0.001 ms
- Confirms amortized O(1)

**Hash Table vs Array Search:**
- 100 elements: 2-3x faster
- 1,000 elements: 10-20x faster
- 10,000 elements: 100-200x faster
- **Advantage grows with data size!**

## Real-World Examples

**Programming Languages:**
- Python: `dict`, `set`
- JavaScript: `Object`, `Map`, `Set`
- Java: `HashMap`, `HashSet`
- PHP: Associative arrays
- Ruby: `Hash`
- C++: `unordered_map`, `unordered_set`

**Databases:**
- MySQL: Hash indexes
- PostgreSQL: Hash indexes
- Redis: Hash data type, sets
- MongoDB: Hash-based indexing
- Elasticsearch: Term queries

**Systems:**
- DNS lookup tables
- Compiler symbol tables
- File system inode tables
- Process ID (PID) mapping
- Network routing tables

**Applications:**
- Caching (browser, CDN, database)
- Session management
- Rate limiting (IP tracking)
- Duplicate detection
- Autocomplete dictionaries

## Interview Questions Covered

✅ **Two-Sum Problem** (LeetCode #1)
- Pattern: Complement lookup
- Solution: `04-two-sum-problem.php`

✅ **Group Anagrams** (LeetCode #49)
- Pattern: Canonical form as key
- Solution: `06-group-anagrams.php`

✅ **LRU Cache** (LeetCode #146)
- Pattern: Hash table + doubly linked list
- Solution: `08-lru-cache.php`

✅ **First Unique Character** (LeetCode #387)
- Pattern: Frequency counter
- Solution: `05-frequency-counter.php`

✅ **Design HashMap** (LeetCode #706)
- Pattern: Chaining or open addressing
- Solution: `02-hash-table-chaining.php` or `03-hash-table-open-addressing.php`

## Next Steps

- Study **Bloom Filters** for space-efficient membership testing
- Learn **Consistent Hashing** for distributed systems
- Implement **Perfect Hashing** for static datasets
- Practice **Hash-based algorithms** on LeetCode
- Explore **Cryptographic hashing** (SHA-256, bcrypt)

## Further Reading

- [Hash Table - Wikipedia](https://en.wikipedia.org/wiki/Hash_table)
- [Hash Functions - Thomas Wang](https://gist.github.com/badboy/6267743)
- [Load Factor Analysis - CLRS Chapter 11](https://mitpress.mit.edu/books/introduction-algorithms-third-edition)
- [Consistent Hashing](https://en.wikipedia.org/wiki/Consistent_hashing)
- [Hash Flooding DoS](https://www.kb.cert.org/vuls/id/903934/)

---

**Chapter 06 Complete!** 🎉

Ready to move on to [Chapter 07: Sorting Algorithms](../../docs/series/computer-science/chapters/07-sorting-algorithms.md).
