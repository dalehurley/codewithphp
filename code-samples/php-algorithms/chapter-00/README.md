# Chapter 00: Quick Start Guide - Code Samples

Practical, runnable PHP code examples for getting started with algorithms quickly.

## Files in This Chapter

### 1. `01-quick-start-examples.php`
**Purpose**: Collection of essential algorithm patterns ready to use
**Key Concepts**:
- Binary search and linear search
- Simple caching with TTL
- Quick sort implementation
- File streaming with generators
- Shortest path (BFS)
- Two sum pattern
- Sliding window
- Performance benchmarking

**Run it**:
```bash
php 01-quick-start-examples.php
```

**What you'll learn**:
- How to implement common algorithm patterns
- Basic performance measurement
- Memory-efficient file processing
- Graph traversal basics

---

### 2. `02-common-patterns.php`
**Purpose**: Fundamental algorithm patterns used across problem-solving
**Key Concepts**:
- **Two Pointers**: Palindrome check, two sum, remove duplicates
- **Sliding Window**: Max sum subarray, longest substring, minimum window
- **Fast & Slow Pointers**: Cycle detection, finding middle, nth from end
- **Hash Maps**: Frequency counting, anagram grouping, isomorphic strings

**Run it**:
```bash
php 02-common-patterns.php
```

**What you'll learn**:
- When to use two pointers vs sliding window
- How Floyd's cycle detection algorithm works
- Practical uses of hash maps for O(1) lookups
- Pattern recognition for interview problems

---

### 3. `03-performance-tips.php`
**Purpose**: Practical optimization techniques with benchmarks
**Key Concepts**:
- Pre-calculating count() in loops
- Using isset() instead of in_array()
- String concatenation optimization
- Early returns
- Avoiding nested loops with hash sets
- Using built-in functions
- Generators for memory efficiency
- Caching expensive operations
- Batch operations
- Avoiding unnecessary array copies

**Run it**:
```bash
php 03-performance-tips.php
```

**What you'll learn**:
- Common performance pitfalls in PHP
- Measurable impact of optimizations
- When O(n) becomes O(n²) by accident
- Best practices for production code

---

## Quick Reference

### Complexity Cheat Sheet

| Pattern | Time Complexity | Space Complexity | Use Case |
|---------|----------------|------------------|----------|
| Binary Search | O(log n) | O(1) | Sorted array search |
| Hash Lookup | O(1) | O(n) | Fast membership checks |
| Two Pointers | O(n) | O(1) | Sorted arrays, palindromes |
| Sliding Window | O(n) | O(1) | Subarray/substring problems |
| BFS | O(V + E) | O(V) | Shortest path (unweighted) |

### When to Use Each Pattern

**Use Two Pointers when:**
- Array is sorted
- Looking for pairs/triplets
- Need to process from both ends
- Example: Palindrome check, container with most water

**Use Sliding Window when:**
- Need subarray/substring with property
- Looking for consecutive elements
- Can expand/contract a range
- Example: Max sum of k elements, longest substring

**Use Hash Map when:**
- Need O(1) lookups
- Counting frequencies
- Finding pairs/complements
- Example: Two sum, anagram detection

**Use Fast & Slow Pointers when:**
- Linked list problems
- Cycle detection needed
- Finding middle element
- Example: Linked list cycle, find duplicate

## Common Mistakes to Avoid

1. **Calling count() in loop condition**
   ```php
   // ❌ Bad: count() called every iteration
   for ($i = 0; $i < count($arr); $i++) { }

   // ✅ Good: calculate once
   $n = count($arr);
   for ($i = 0; $i < $n; $i++) { }
   ```

2. **Using in_array() for repeated lookups**
   ```php
   // ❌ Bad: O(n) per lookup
   if (in_array($id, $validIds)) { }

   // ✅ Good: O(1) per lookup
   $valid = array_flip($validIds);
   if (isset($valid[$id])) { }
   ```

3. **String concatenation in loops**
   ```php
   // ❌ Bad: O(n²) - creates new string each time
   $result = '';
   foreach ($items as $item) {
       $result .= $item;
   }

   // ✅ Good: O(n)
   $result = implode('', $items);
   ```

## Testing the Examples

All files can be run directly from command line:

```bash
# Run individual files
php 01-quick-start-examples.php
php 02-common-patterns.php
php 03-performance-tips.php

# Or run all examples
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo "---"
done
```

## Next Steps

After mastering these quick start patterns:

1. **Chapter 01**: Learn Big O notation and complexity analysis
2. **Chapter 02**: Build a benchmarking framework
3. **Chapter 03**: Master recursion fundamentals
4. **Chapter 04**: Develop systematic problem-solving strategies
5. **Chapter 05**: Understand sorting algorithms

## Tips for Learning

1. **Run the code**: Don't just read - execute and modify
2. **Experiment**: Change inputs and see what happens
3. **Benchmark**: Compare different approaches
4. **Practice**: Implement variations of each pattern
5. **Debug**: Add print statements to visualize execution

## Requirements

- PHP 8.0 or higher
- CLI access (command line)
- Basic understanding of PHP syntax

## Resources

- [PHP Documentation](https://www.php.net/manual/en/)
- [Algorithm Visualizations](https://visualgo.net/)
- Practice problems: LeetCode, HackerRank, CodeWars

---

**Pro Tip**: Keep these files as a reference. When you encounter a similar problem in the future, you can quickly copy and adapt the patterns shown here.
