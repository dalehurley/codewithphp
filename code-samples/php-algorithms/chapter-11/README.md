# Chapter 11: Linear Search & Variants - Code Samples

This directory contains comprehensive, runnable PHP code samples for Chapter 11 of the PHP Algorithms series.

## Files Overview

### 01-basic-linear-search.php
**Basic Linear Search Implementation**

Demonstrates the fundamental linear search algorithm with multiple implementations and use cases.

**Key Concepts:**
- Basic linear search using `foreach`
- Linear search using `for` loop
- Early termination for sorted arrays
- Visualized search process
- Best/worst case scenarios
- Performance comparisons

**Run:**
```bash
php 01-basic-linear-search.php
```

---

### 02-search-variants.php
**Advanced Search Variants**

Implements optimized variants of linear search for specific use cases.

**Key Concepts:**
- Sentinel linear search (eliminates boundary checks)
- Jump search (O(√n) for sorted arrays)
- Interpolation search (O(log log n) for uniform data)
- Finding all occurrences
- Detailed visualizations
- Performance comparisons

**Run:**
```bash
php 02-search-variants.php
```

---

### 03-search-with-conditions.php
**Search with Conditions and Callbacks**

Demonstrates flexible searching using callbacks and predicates.

**Key Concepts:**
- Search with custom conditions
- `findIndex()` - Find index of first match
- `findAll()` - Find all matching elements
- `findAllIndices()` - Find all matching indices
- `countMatching()` - Count matches
- `any()` / `all()` - Boolean checks
- Complex predicates (prime numbers, string operations)
- Performance vs. PHP built-ins

**Run:**
```bash
php 03-search-with-conditions.php
```

---

### 04-object-search.php
**Searching in Objects and Complex Structures**

Shows how to search in objects, associative arrays, and nested structures.

**Key Concepts:**
- Search in arrays of objects
- Find by property value
- Search in multidimensional arrays
- Deep recursive search in nested structures
- Multiple criteria matching
- Object vs Array performance comparison
- Real-world examples (Users, Products)

**Run:**
```bash
php 04-object-search.php
```

---

### 05-practical-applications.php
**Practical Real-World Applications**

Demonstrates real-world use cases for linear search.

**Key Concepts:**
- Simple grep implementation
- Autocomplete engine
- Data validation (duplicates, missing values, required fields)
- Text highlighting
- Data filtering with multiple conditions
- Email extraction and validation
- Performance monitoring across scenarios

**Run:**
```bash
php 05-practical-applications.php
```

---

## Running All Examples

To run all examples in sequence:

```bash
for file in *.php; do
    echo "=== Running $file ==="
    php "$file"
    echo ""
done
```

## Key Takeaways

1. **Linear search is O(n)** - Simple but potentially slow for large datasets
2. **Use linear search when:**
   - Array is unsorted
   - Array is small (< 100 elements)
   - Only searching once
   - Can't use binary search (linked lists, etc.)
3. **Variants offer improvements:**
   - Sentinel search: Fewer comparisons per iteration
   - Jump search: O(√n) for sorted arrays
   - Interpolation search: O(log log n) for uniform data
4. **PHP provides built-in alternatives:**
   - `in_array()` - Check if value exists
   - `array_search()` - Find key of value
   - `array_filter()` - Filter with callback
5. **Practical applications:**
   - Log file searching
   - Autocomplete
   - Data validation
   - Text highlighting

## Requirements

- PHP 8.0 or higher
- No external dependencies

## Additional Resources

- Chapter 11: Linear Search & Variants (documentation)
- [PHP Manual: Array Functions](https://www.php.net/manual/en/ref.array.php)
- [Big O Notation Guide](https://www.bigocheatsheet.com/)

## Next Steps

Continue to Chapter 12 samples to learn about Binary Search and its variants.
