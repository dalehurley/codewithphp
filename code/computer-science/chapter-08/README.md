# Chapter 08: Searching Algorithms - Code Examples

Complete, runnable code examples demonstrating searching algorithms from Chapter 8.

## Quick Start

```bash
# Run any example
php 01-linear-search.php
php 02-binary-search.php
php 08-search-comparison.php
# ... etc
```

## Examples Overview

### 01-linear-search.php
**Concepts**: O(n) sequential search

Demonstrates:
- Basic linear search implementation
- Search with comparison statistics
- Finding all occurrences
- Sentinel linear search optimization
- Performance scaling tests
- Best/average/worst case analysis
- Early termination benefits

**Run time**: ~1 second

---

### 02-binary-search.php
**Concepts**: O(log n) divide-and-conquer search

Demonstrates:
- Iterative binary search
- Recursive binary search
- Comparison counting
- Performance vs linear search
- Step-by-step visualization
- Edge cases and boundary conditions
- Why binary search is logarithmic

**Run time**: ~1 second

---

### 03-binary-search-variations.php
**Concepts**: Binary search variants for specific problems

Demonstrates:
- Find first occurrence
- Find last occurrence
- Count occurrences in O(log n)
- Find insertion position
- Find closest element
- Find peak element
- Floor and ceiling operations
- Search range boundaries

**Run time**: ~1 second

---

### 04-search-rotated-array.php
**Concepts**: Modified binary search for rotated arrays

Demonstrates:
- Search in rotated sorted array
- Find minimum in rotated array
- Find rotation count/pivot
- Handle duplicates
- Step-by-step visualization
- Circular buffer applications
- Real-world rotation patterns

**Run time**: ~1 second

---

### 05-interpolation-jump-search.php
**Concepts**: O(log log n) and O(√n) search algorithms

Demonstrates:
- Interpolation search for uniform data
- Jump search (block search)
- Exponential search
- Performance comparison
- Optimal step size analysis
- When each algorithm is best

**Run time**: ~2 seconds

---

### 06-search-2d-matrix.php
**Concepts**: Searching in 2D matrices

Demonstrates:
- Staircase search (O(m+n))
- Binary search on fully sorted matrix
- Count elements smaller than target
- Find kth smallest element
- Different matrix sorting patterns
- Practical spreadsheet searches

**Run time**: ~1 second

---

### 07-search-in-data-structures.php
**Concepts**: Search across different data structures

Demonstrates:
- Binary Search Tree (BST) search
- Hash table O(1) lookup
- Trie prefix search and auto-complete
- Linked list search
- Performance comparison
- When to use each structure

**Run time**: ~2 seconds

---

### 08-search-comparison.php
**Concepts**: Comprehensive algorithm comparison

Demonstrates:
- Performance benchmarks
- Small vs large dataset tests
- Scaling behavior analysis
- Uniform vs non-uniform distribution
- Memory usage comparison
- Algorithm selection guide

**Run time**: ~3 seconds (benchmarks)

---

### 09-search-applications.php
**Concepts**: Real-world search use cases

Demonstrates:
- Database query optimization
- Text search and inverted index
- Spell checking (edit distance)
- Finding duplicates
- Product recommendation (similarity)
- IP address lookup (CIDR)
- Log file analysis
- LRU cache implementation

**Run time**: ~1 second

---

### 10-advanced-search-techniques.php
**Concepts**: Advanced patterns and optimizations

Demonstrates:
- Ternary search for unimodal functions
- Fibonacci search
- Two pointers technique
- Sliding window search
- Binary search on answer space
- Meet in the middle
- Search in infinite arrays
- Advanced problem patterns

**Run time**: ~1 second

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

1. **Basic Search (Foundation)**
   - Start with `01-linear-search.php` - understand sequential search
   - Then `02-binary-search.php` - learn divide-and-conquer

2. **Binary Search Mastery**
   - Study `03-binary-search-variations.php` - master binary search patterns
   - Practice `04-search-rotated-array.php` - handle tricky cases

3. **Alternative Algorithms**
   - Explore `05-interpolation-jump-search.php` - learn when to use alternatives
   - Review `06-search-2d-matrix.php` - extend to 2D problems

4. **Data Structure Integration**
   - Run `07-search-in-data-structures.php` - see search in context
   - Compare `08-search-comparison.php` - understand tradeoffs

5. **Practical Application**
   - Apply `09-search-applications.php` - real-world scenarios
   - Master `10-advanced-search-techniques.php` - interview patterns

## Key Takeaways

After running these examples, you'll understand:

✅ **Algorithm Classification**
- Linear search: O(n) - works on any data
- Binary search: O(log n) - requires sorted data
- Interpolation: O(log log n) - requires uniform distribution
- Jump search: O(√n) - good for sequential access
- Hash table: O(1) - fastest but extra space

✅ **Critical Insights**
- **Sorted data requirement**: Binary, interpolation, jump require sorted arrays
- **Uniform distribution**: Interpolation excels on uniform data, degrades otherwise
- **Space-time tradeoffs**: Hash tables trade space for O(1) speed
- **Problem patterns**: Recognize when to use two pointers, sliding window, etc.

✅ **When to Use Each**
- **Linear Search**: Unsorted data, small arrays (< 100), single search
- **Binary Search**: Sorted data, general purpose, guaranteed O(log n)
- **Interpolation Search**: Large uniform numeric data, can be O(log log n)
- **Jump Search**: Sequential access systems, between linear and binary
- **Hash Table**: Key-value lookups, need O(1), extra memory available
- **Trie**: Prefix searches, auto-complete, dictionary operations

✅ **Real-World Performance**
For 1,000,000 elements:
- Linear: ~1,000,000 comparisons
- Binary: ~20 comparisons (50,000x fewer!)
- Interpolation: ~6 comparisons (on uniform data)
- Hash table: 1 comparison (O(1))

✅ **Advanced Patterns**
- Binary search on answer space (not just arrays)
- Two pointers for sorted array problems
- Sliding window for subarray/substring
- Meet in the middle for exponential reduction

## Complexity Cheat Sheet

| Algorithm | Time (Best) | Time (Avg) | Time (Worst) | Space | Requirements |
|-----------|-------------|------------|--------------|-------|--------------|
| Linear | O(1) | O(n) | O(n) | O(1) | None |
| Binary | O(1) | O(log n) | O(log n) | O(1) | Sorted |
| Interpolation | O(1) | O(log log n) | O(n) | O(1) | Sorted + Uniform |
| Jump | O(1) | O(√n) | O(√n) | O(1) | Sorted |
| Exponential | O(1) | O(log n) | O(log n) | O(1) | Sorted |
| Hash Table | O(1) | O(1) | O(n) | O(n) | Hash function |
| BST | O(log n) | O(log n) | O(n) | O(n) | BST structure |
| Trie | O(m) | O(m) | O(m) | O(ALPHABET_SIZE * N * M) | Trie structure |

*m = word/key length, n = number of elements*

## Common Pitfalls

⚠️ **Using linear search on sorted data**: Always use binary search for sorted data (1000x+ speedup on large datasets).

⚠️ **Forgetting data must be sorted**: Binary search and variants REQUIRE sorted data. Always verify!

⚠️ **Using interpolation on non-uniform data**: Can degrade to O(n). Stick with binary search unless distribution is truly uniform.

⚠️ **Integer overflow in mid calculation**: Use `left + (right - left) / 2` instead of `(left + right) / 2`.

⚠️ **Wrong loop condition**: `left <= right` vs `left < right` matters! Know when to use each.

⚠️ **Not considering hash tables**: For frequent lookups, hash table's O(1) beats even O(log n).

## Performance Benchmarks

Based on examples in this chapter (random data):

**Small Dataset (100 elements):**
- All algorithms comparable (< 1 ms)
- Differences minimal for small n
- Choose based on code simplicity

**Medium Dataset (10,000 elements):**
- Linear search: ~50 ms
- Binary search: ~0.5 ms (100x faster)
- Interpolation: ~0.3 ms (on uniform data)
- Hash table: ~0.1 ms (500x faster)

**Large Dataset (1,000,000 elements):**
- Linear search: ~5000 ms
- Binary search: ~0.5 ms (10,000x faster!)
- Interpolation: ~0.2 ms (25,000x faster!)
- Hash table: ~0.1 ms (50,000x faster!)

**Key Insight**: The speedup grows with data size! Binary search's advantage increases logarithmically.

## Interview Questions Covered

✅ **Binary Search**
- Solution: `02-binary-search.php`
- Time: O(log n)
- Space: O(1) iterative, O(log n) recursive

✅ **Search in Rotated Sorted Array**
- Solution: `04-search-rotated-array.php`
- Time: O(log n)
- Key: Determine which half is sorted

✅ **Find First and Last Position**
- Solution: `03-binary-search-variations.php`
- Time: O(log n)
- Pattern: Modified binary search

✅ **Search a 2D Matrix**
- Solution: `06-search-2d-matrix.php`
- Time: O(log(m*n)) or O(m+n)
- Depends on matrix sorting

✅ **Two Sum (Sorted)**
- Solution: `10-advanced-search-techniques.php`
- Time: O(n) with two pointers
- Space: O(1)

✅ **Maximum Sliding Window**
- Pattern: Sliding window technique
- Solution: `10-advanced-search-techniques.php`

✅ **Kth Smallest in Sorted Matrix**
- Solution: `06-search-2d-matrix.php`
- Time: O(n * log(max-min))
- Pattern: Binary search on values

## Search Algorithm Decision Tree

```
Need to search for element?
│
├─ Data unsorted?
│  ├─ Small dataset (< 100)?
│  │  └─ Use: Linear Search O(n)
│  └─ Frequent searches?
│     └─ Use: Hash Table O(1) (build once, search many)
│
├─ Data sorted?
│  ├─ Array rotated?
│  │  └─ Use: Modified Binary Search O(log n)
│  │
│  ├─ Need first/last occurrence?
│  │  └─ Use: Binary Search Variations O(log n)
│  │
│  ├─ Data uniformly distributed?
│  │  └─ Use: Interpolation Search O(log log n)
│  │
│  ├─ Sequential access costly?
│  │  └─ Use: Jump Search O(√n)
│  │
│  └─ General case?
│     └─ Use: Binary Search O(log n)
│
└─ Searching for pattern/substring?
   ├─ Prefix matching?
   │  └─ Use: Trie O(m)
   └─ Substring?
      └─ Use: KMP/Boyer-Moore
```

## Next Steps

- Practice binary search problems on **LeetCode**
- Study **KMP** and **Boyer-Moore** for string searching
- Learn **Rabin-Karp** for multiple pattern matching
- Explore **Aho-Corasick** for dictionary matching
- Master **Z-algorithm** for pattern matching

## Further Reading

- [Binary Search Visualizations](https://www.cs.usfca.edu/~galles/visualization/Search.html)
- [Interpolation Search Analysis](https://en.wikipedia.org/wiki/Interpolation_search)
- [Advanced Binary Search Patterns](https://leetcode.com/discuss/study-guide/786126/python-powerful-ultimate-binary-search-template-solved-many-problems)
- [Two Pointers Technique](https://leetcode.com/articles/two-pointer-technique/)
- [Sliding Window Patterns](https://medium.com/leetcode-patterns/leetcode-pattern-2-sliding-windows-for-strings-e19af105316b)

---

**Chapter 08 Complete!** 🎉

Ready to move on to [Chapter 09: Recursion](../../docs/series/computer-science/chapters/09-recursion.md).
