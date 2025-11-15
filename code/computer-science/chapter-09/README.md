# Chapter 09: Recursion - Code Examples

Complete, runnable code examples demonstrating recursion and recursive thinking from Chapter 9.

## Quick Start

```bash
# Run any example
php 01-recursion-basics.php
php 02-fibonacci-memoization.php
php 06-divide-conquer.php
# ... etc
```

## Examples Overview

### 01-recursion-basics.php
**Concepts**: Foundation of recursion - base case, recursive case, call stack

Demonstrates:
- Factorial with call stack visualization
- Countdown and count up patterns
- Sum of numbers
- Power function
- GCD (Euclidean algorithm)
- Print array forward and reverse
- Even number checker
- Anatomy of every recursive function

**Run time**: ~1 second
**Key insight**: Every recursive function needs base case, recursive case, and progress toward base case

---

### 02-fibonacci-memoization.php
**Concepts**: Memoization transforms O(2^n) to O(n)

Demonstrates:
- Naive recursive fibonacci (exponential time)
- Fibonacci with memoization (linear time)
- Performance comparison showing 1000x+ speedup
- Computing large fibonacci numbers (fib(50))

**Run time**: ~2 seconds (includes slow naive version)
**Key insight**: Memoization is CRITICAL for overlapping subproblems

---

### 03-array-string-recursion.php
**Concepts**: Recursion on linear data structures

Demonstrates:
- Sum array elements recursively
- Reverse string
- Palindrome checker
- Find maximum in array

**Run time**: < 1 second
**Key insight**: Reduce problem size by one element each recursion

---

### 04-tree-recursion.php
**Concepts**: Natural recursion for tree structures

Demonstrates:
- TreeNode class
- Sum all nodes in tree
- Calculate tree height
- Count nodes in tree

**Run time**: < 1 second
**Key insight**: Trees are inherently recursive - perfect fit for recursion

---

### 05-backtracking.php
**Concepts**: Generate all possible solutions

Demonstrates:
- Generate all permutations
- Generate all subsets (power set)
- Backtracking pattern

**Run time**: < 1 second
**Key insight**: Backtracking explores all possibilities recursively

---

### 06-divide-conquer.php
**Concepts**: Divide problem, conquer recursively, combine results

Demonstrates:
- Merge sort (O(n log n))
- Binary search (recursive implementation)
- Divide and conquer pattern

**Run time**: < 1 second
**Key insight**: Many O(n log n) algorithms use divide and conquer

---

### 07-tail-recursion.php
**Concepts**: Last operation is recursive call (no work after)

Demonstrates:
- Normal vs tail recursive factorial
- Tail recursive sum
- Benefits of tail recursion

**Run time**: < 1 second
**Key insight**: Tail recursion CAN be optimized to iteration (PHP doesn't do this)

---

### 08-recursion-vs-iteration.php
**Concepts**: When to use recursion vs iteration

Demonstrates:
- Recursive vs iterative factorial
- Performance comparison
- Decision criteria

**Run time**: ~1 second (includes benchmarks)
**Key insight**: Iteration is faster, recursion is often clearer

---

### 09-common-patterns.php
**Concepts**: Recursive patterns you'll encounter

Demonstrates:
- Linear recursion (one call)
- Binary recursion (two calls - fibonacci)
- Multiple recursion (many calls - grid paths)
- Mutual recursion (functions call each other)

**Run time**: < 1 second
**Key insight**: Recognize these patterns in problems

---

### 10-real-world-applications.php
**Concepts**: Recursion in real systems

Demonstrates:
- File system traversal
- JSON flattening (nested structures)
- Expression evaluation
- Real-world use cases

**Run time**: < 1 second
**Key insight**: Recursion is everywhere in real applications

---

## Running All Examples

```bash
# Run all examples in sequence
for file in 0*.php; do
    echo "=== Running $file ==="
    php $file
    echo ""
done
```

## Dependencies

- PHP 8.2+ (uses constructor property promotion, typed properties, match expressions)
- No external dependencies required

## Learning Path

**Recommended order:**

1. **Foundation (Start Here)**
   - `01-recursion-basics.php` - Understand base case, recursive case, call stack
   - `03-array-string-recursion.php` - Practice on simple linear structures

2. **Critical Optimization**
   - `02-fibonacci-memoization.php` - Learn why memoization is essential
   - See 1000x speedup from O(2^n) to O(n)

3. **Natural Recursion**
   - `04-tree-recursion.php` - See why recursion is perfect for trees
   - Trees are naturally recursive structures

4. **Advanced Patterns**
   - `05-backtracking.php` - Generate all solutions (permutations, subsets)
   - `06-divide-conquer.php` - Master merge sort and binary search
   - `09-common-patterns.php` - Recognize linear, binary, multiple recursion

5. **Practical Considerations**
   - `07-tail-recursion.php` - Understand tail call optimization
   - `08-recursion-vs-iteration.php` - Know when to use each
   - `10-real-world-applications.php` - See recursion in real systems

## Key Takeaways

After running these examples, you'll understand:

✅ **Recursion Fundamentals**
- **Base case**: Stopping condition (prevents infinite recursion)
- **Recursive case**: Function calls itself with simpler input
- **Progress**: Each call must get closer to base case
- **Call stack**: Builds up with calls, unwinds with returns

✅ **Critical Patterns**
- **Linear recursion**: One recursive call (factorial, sum)
- **Binary recursion**: Two calls (fibonacci, tree)
- **Multiple recursion**: Many calls (permutations, paths)
- **Tail recursion**: Last operation is recursive call

✅ **Optimization**
- **Memoization**: Cache results to avoid redundant computation
- **Transforms O(2^n) to O(n)** for overlapping subproblems
- **Essential for fibonacci, dynamic programming**

✅ **When to Use Recursion**
- Trees and graphs (natural fit)
- Divide and conquer (merge sort, binary search)
- Backtracking (generate all solutions)
- Problem naturally divides into subproblems
- Code readability matters

✅ **When NOT to Use Recursion**
- Simple sequential processing (use loops)
- Performance critical code (iteration is faster)
- Risk of stack overflow (deep recursion)
- Tail recursion not optimized (PHP limitation)

## Complexity Analysis

### Time Complexity Patterns

| Pattern | Example | Time | Note |
|---------|---------|------|------|
| Linear | Factorial | O(n) | n recursive calls |
| Binary | Naive Fibonacci | O(2^n) | Exponential! |
| Binary + Memo | Fibonacci | O(n) | Memoization saves us |
| Divide & Conquer | Merge Sort | O(n log n) | Balanced tree |
| Backtracking | Permutations | O(n!) | All possibilities |

### Space Complexity

**Call Stack Depth**:
- Each recursive call uses stack memory
- Maximum depth = O(height)
- Factorial: O(n) stack depth
- Merge sort: O(log n) stack depth (balanced)
- Risk: Stack overflow for deep recursion

**Memoization**:
- Extra O(n) space for memo table
- Trade space for time
- Worth it for overlapping subproblems

## Common Pitfalls

⚠️ **Missing base case**: Causes infinite recursion → stack overflow
```php
// WRONG - no base case!
function bad($n) {
    return $n + bad($n - 1); // Stack overflow!
}

// CORRECT
function good($n) {
    if ($n === 0) return 0; // Base case!
    return $n + good($n - 1);
}
```

⚠️ **Not making progress**: Each call must get closer to base case
```php
// WRONG - doesn't decrease $n
function bad($n) {
    if ($n === 0) return;
    bad($n); // Infinite loop!
}
```

⚠️ **Redundant computation**: Use memoization for overlapping subproblems
```php
// WRONG - recalculates same values
function slowFib($n) {
    if ($n <= 1) return $n;
    return slowFib($n-1) + slowFib($n-2); // O(2^n)!
}

// CORRECT - cache results
function fastFib($n, &$memo = []) {
    if ($n <= 1) return $n;
    if (isset($memo[$n])) return $memo[$n]; // Check cache
    $memo[$n] = fastFib($n-1, $memo) + fastFib($n-2, $memo);
    return $memo[$n]; // O(n)!
}
```

⚠️ **Stack overflow**: Recursion too deep (PHP limit ~1000-10000 calls)
```php
// Risk with large $n
factorial(100000); // May cause stack overflow

// Use iteration for deep recursion
function factorialIterative($n) {
    $result = 1;
    for ($i = 2; $i <= $n; $i++) {
        $result *= $i;
    }
    return $result;
}
```

## Interview Questions Covered

✅ **Factorial**
- Solution: `01-recursion-basics.php`
- Time: O(n), Space: O(n) stack

✅ **Fibonacci**
- Naive: `02-fibonacci-memoization.php` - O(2^n)
- Optimized: With memoization - O(n)

✅ **Reverse String/Array**
- Solution: `03-array-string-recursion.php`
- Time: O(n), Space: O(n)

✅ **Palindrome Check**
- Solution: `03-array-string-recursion.php`
- Time: O(n), Space: O(n) stack

✅ **Tree Traversals**
- Solution: `04-tree-recursion.php`
- Perfect use case for recursion

✅ **Permutations**
- Solution: `05-backtracking.php`
- Time: O(n!), Space: O(n)

✅ **Merge Sort**
- Solution: `06-divide-conquer.php`
- Time: O(n log n), Space: O(n)

✅ **Binary Search (Recursive)**
- Solution: `06-divide-conquer.php`
- Time: O(log n), Space: O(log n) stack

## Recursion Decision Tree

```
Need to solve problem?
│
├─ Working with trees/graphs?
│  └─ Use: Recursion (natural fit)
│
├─ Divide and conquer possible?
│  └─ Use: Recursion (merge sort, binary search)
│
├─ Generate all solutions?
│  └─ Use: Recursion + Backtracking (permutations)
│
├─ Overlapping subproblems?
│  └─ Use: Recursion + Memoization (fibonacci, DP)
│
├─ Simple sequential processing?
│  └─ Use: Iteration (faster, less memory)
│
└─ Risk of stack overflow?
   └─ Use: Iteration or tail recursion
```

## Performance Tips

1. **Use memoization** for overlapping subproblems (fibonacci)
2. **Consider iteration** for simple sequential problems (factorial)
3. **Watch stack depth** - PHP limit is typically 1000-10000 calls
4. **Tail recursion** doesn't help in PHP (not optimized)
5. **Profile before optimizing** - clarity often beats micro-optimization

## Next Steps

- Practice recursive problems on **LeetCode**
- Study **Dynamic Programming** (Chapter 18)
- Explore **Graph Algorithms** (Chapter 10) - heavily use recursion
- Master **Tree Traversals** (Chapter 5) - natural recursion
- Learn **Backtracking** patterns (N-Queens, Sudoku)

## Further Reading

- [Recursion (Wikipedia)](https://en.wikipedia.org/wiki/Recursion_(computer_science))
- [Master Theorem](https://en.wikipedia.org/wiki/Master_theorem_(analysis_of_algorithms))
- [Dynamic Programming](https://en.wikipedia.org/wiki/Dynamic_programming)
- [Tail Call Optimization](https://en.wikipedia.org/wiki/Tail_call)
- [LeetCode Recursion Problems](https://leetcode.com/tag/recursion/)

---

**Chapter 09 Complete!** 🎉

Master recursion and you'll unlock:
- Tree and graph algorithms (Chapter 10)
- Dynamic programming (Chapter 18)
- Backtracking problems
- Divide and conquer strategies

Ready to move on to [Chapter 10: Graph Algorithms](../../docs/series/computer-science/chapters/10-graph-algorithms.md).
