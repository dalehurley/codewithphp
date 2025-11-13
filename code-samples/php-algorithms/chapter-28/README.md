# Chapter 28: Algorithm Selection Guide

Decision-making tools for choosing the right algorithm based on problem characteristics.

## Code Samples

### algorithm-selector.php
**Comprehensive Algorithm Selection Guide**

Interactive tool to recommend algorithms based on:
- Data size and characteristics
- Time/space constraints
- Stability requirements
- Access patterns

**Features:**
- Sorting algorithm selector
- Search algorithm selector
- Performance analyzer
- Runtime estimator
- Constraint-based selection

**Run:** `php algorithm-selector.php`

## Key Concepts

### Selection Criteria

**Sorting:**
- n < 10: Insertion Sort
- Nearly sorted: Insertion Sort (O(n))
- Stable needed: Merge Sort
- Memory limited: Heap Sort
- General purpose: Quick Sort / PHP sort()

**Searching:**
- Sorted data: Binary Search (O(log n))
- Frequent lookups: Hash Table (O(1))
- Small/unsorted: Linear Search (O(n))

### Complexity Guidelines

| Input Size | Max Complexity | Example |
|------------|----------------|---------|
| n ≤ 10 | O(n!) | Permutations |
| n ≤ 20 | O(2^n) | Subset generation |
| n ≤ 10,000 | O(n²) | Nested loops |
| n ≤ 1M | O(n log n) | Sorting |
| n > 1M | O(n) or O(log n) | Linear/Binary |

## Quick Reference

```php
// Choose sorting algorithm
$selector = new SortingSelector();
$rec = $selector->recommend([
    'size' => 10000,
    'needs_stable' => true
]);
// Returns: Merge Sort

// Estimate runtime
$analyzer = new PerformanceAnalyzer();
$time = $analyzer->estimateRuntime(10000, 'O(n log n)');
// Returns: ~0.13 ms
```

## Decision Tree

1. **Is data sorted?**
   - Yes → Binary Search (O(log n))
   - No → Continue

2. **How many searches?**
   - Many → Build hash table (O(n) prep, O(1) lookup)
   - Few → Linear search (O(n))

3. **Need to maintain order?**
   - Yes → BST or sorted array
   - No → Hash table

## Requirements

- PHP 8.0+
- No external dependencies

**Next:** [Chapter 29: Performance Optimization](../chapter-29/)
