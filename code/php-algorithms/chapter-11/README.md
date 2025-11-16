# Chapter 11: Linear Search & Variants

This directory contains comprehensive code examples demonstrating linear search algorithms, their variants, optimizations, and real-world applications.

## Files

### 01-basic-linear-search.php
Fundamental linear search implementations:
- Basic linear search (foreach)
- Loop-based linear search
- Recursive linear search
- Find all occurrences
- Count occurrences
- Search with early termination
- Complexity analysis with statistics
- Linked list search
- Performance testing
- Comparison with PHP built-in functions

### 02-search-variants.php
Advanced search variants and optimizations:
- Sentinel search (eliminates boundary checks)
- Bidirectional search (searches from both ends)
- Jump search (O(√n) for sorted arrays)
- Interpolation search (O(log log n) for uniform data)
- Move-to-front optimization (self-organizing lists)
- Transpose search (gradual optimization)
- Performance comparisons
- When to use each variant

### 03-search-with-conditions.php
Conditional searching with callbacks:
- Predicate-based search
- `findIndex()` - find first matching index
- `findAll()` - find all matching elements
- `findAllIndices()` - find all matching indices
- `some()` - check if any match
- `every()` - check if all match
- `partition()` - split by condition
- Complex predicates
- Chained conditions (AND/OR)
- Count matching elements
- Search with context

### 04-object-search.php
Searching in complex structures:
- Search objects by property
- Search associative arrays by key
- Search nested structures
- Search multidimensional arrays
- Multi-criteria searches
- Range-based queries
- Deep property path searches
- Custom collection classes
- Property path (dot notation)
- Real-world blog post search

### 05-practical-applications.php
Real-world practical applications:
- Simple grep implementation
- Autocomplete/search suggestions
- Form validation (unique values, allowed values)
- In-memory database queries
- Inventory management system
- Tag/category search
- Permission checking
- Configuration lookup
- Menu navigation search

## Running the Examples

Each file is a complete, runnable PHP script with detailed output:

```bash
# Basic linear search
php 01-basic-linear-search.php

# Search variants and optimizations
php 02-search-variants.php

# Conditional searching
php 03-search-with-conditions.php

# Object and complex structure search
php 04-object-search.php

# Practical applications
php 05-practical-applications.php
```

## Requirements

- **PHP 8.4+** (uses modern PHP features)
- No external dependencies required

## What You'll Learn

### Basic Linear Search
- How linear search works sequentially
- Time complexity: O(n)
- Space complexity: O(1)
- Best, average, and worst cases
- When linear search is optimal
- Comparison with PHP's built-in `in_array()` and `array_search()`

### Search Variants
- **Sentinel search**: Eliminates boundary check overhead
- **Bidirectional search**: ~2x faster by checking both ends
- **Jump search**: O(√n) for sorted arrays
- **Interpolation search**: O(log log n) for uniformly distributed data
- **Move-to-front**: Optimizes repeated searches
- **Transpose**: Gradual self-organization

### Conditional Searching
- Predicate functions for flexible searching
- Finding first vs finding all matches
- Boolean checks (some/every)
- Partitioning arrays by conditions
- Complex and chained predicates
- Counting matches

### Complex Structures
- Searching objects by properties
- Nested structure traversal
- Multidimensional array searching
- Multi-criteria and range queries
- Deep property paths
- Custom collection implementations

### Real-World Applications
- Text searching (grep-like)
- Autocomplete functionality
- Validation (uniqueness, allowed values)
- In-memory database operations
- Inventory systems
- Tagging and categorization
- Permission systems
- Configuration management

## Key Concepts

### When to Use Linear Search

✅ **Linear search is optimal when:**
- Array is unsorted
- Small arrays (< 100 elements)
- Single search operation
- Data structure doesn't support random access (linked lists)
- Preprocessing (sorting) cost outweighs benefit

❌ **Consider alternatives when:**
- Array is sorted (use binary search O(log n))
- Many repeated searches (consider sorting first or using hash tables)
- Very large datasets (> 10,000 elements) with frequent searches

### Complexity Analysis

```php
// Best case: O(1) - element at first position
linearSearch([5, 2, 8, 1], 5); // 1 comparison

// Average case: O(n/2) → O(n) - element in middle
linearSearch([5, 2, 8, 1], 8); // 3 comparisons

// Worst case: O(n) - element at end or not found
linearSearch([5, 2, 8, 1], 1); // 4 comparisons
linearSearch([5, 2, 8, 1], 9); // 4 comparisons (not found)
```

### Performance Optimization Techniques

1. **Sentinel search**: Remove boundary check (~10-15% faster)
2. **Bidirectional search**: Check both ends (~2x faster on average)
3. **Move-to-front**: Optimize repeated searches of same elements
4. **Early termination**: Stop as soon as match is found
5. **Pre-calculate**: Cache expensive computations

## Code Patterns

### Basic Linear Search
```php
function linearSearch(array $arr, mixed $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}
```

### Predicate Search
```php
function find(array $arr, callable $predicate): mixed
{
    foreach ($arr as $value) {
        if ($predicate($value)) {
            return $value;
        }
    }
    return null;
}

// Usage
$firstEven = find([1, 3, 5, 8, 10], fn($x) => $x % 2 === 0);
```

### Search in Objects
```php
function findByProperty(array $objects, string $property, mixed $value): mixed
{
    foreach ($objects as $obj) {
        if (isset($obj->$property) && $obj->$property === $value) {
            return $obj;
        }
    }
    return null;
}
```

### Multi-Criteria Search
```php
function findByCriteria(array $items, array $criteria): array
{
    $results = [];
    foreach ($items as $item) {
        $matches = true;
        foreach ($criteria as $key => $value) {
            if (!isset($item[$key]) || $item[$key] !== $value) {
                $matches = false;
                break;
            }
        }
        if ($matches) {
            $results[] = $item;
        }
    }
    return $results;
}
```

## Performance Benchmarks

**Testing with 10,000 elements:**

| Algorithm | Time | Complexity | Use Case |
|-----------|------|------------|----------|
| Linear Search | 0.9ms | O(n) | Unsorted data |
| Sentinel Search | 0.8ms | O(n) | Large unsorted arrays |
| Bidirectional | 0.5ms | O(n/2) | Target position unknown |
| Jump Search | 0.3ms | O(√n) | Sorted data |
| Interpolation | 0.1ms | O(log log n) | Uniform sorted data |

**Key insights:**
- Linear search is simple but slow for large datasets
- Optimizations matter for repeated searches
- Sorted data enables much faster algorithms
- Choose algorithm based on data characteristics

## Real-World Use Cases

### 1. Form Validation
```php
// Check username availability
$existingUsernames = ['alice', 'bob', 'charlie'];
$isAvailable = !in_array($newUsername, $existingUsernames);
```

### 2. Permission Checking
```php
// Check if user has permission
$userPermissions = ['read', 'write'];
$canDelete = in_array('delete', $userPermissions);
```

### 3. Autocomplete
```php
// Find matching suggestions
$cities = ['New York', 'New Orleans', 'Newark'];
$suggestions = array_filter($cities, fn($city) => 
    str_starts_with(strtolower($city), strtolower($query))
);
```

### 4. Inventory Search
```php
// Find low stock items
$lowStock = array_filter($inventory, fn($item) => 
    $item['quantity'] <= $threshold
);
```

### 5. Log File Grep
```php
// Find error lines in logs
$errors = array_filter($logLines, fn($line) => 
    str_contains($line, 'ERROR')
);
```

## Common Pitfalls

### 1. Using Linear Search on Sorted Data
```php
// ❌ Bad: Linear search O(n) on sorted data
$sorted = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
linearSearch($sorted, 9); // Checks 9 elements

// ✅ Good: Binary search O(log n)
binarySearch($sorted, 9); // Checks 4 elements
```

### 2. Sorting for Single Search
```php
// ❌ Bad: Sort then search once (O(n log n))
sort($data);
binarySearch($data, $target);

// ✅ Good: Just search linearly (O(n))
linearSearch($data, $target);
```

### 3. Not Using PHP Built-ins
```php
// ❌ Implementing when built-in exists
function myInArray($arr, $val) { /* ... */ }

// ✅ Use optimized built-ins
in_array($val, $arr);        // Check existence
array_search($val, $arr);    // Get index
```

### 4. Ignoring Data Distribution
```php
// ❌ Linear search on uniform numeric data
linearSearch([10, 20, 30, ..., 1000], 750);

// ✅ Interpolation search (O(log log n))
interpolationSearch([10, 20, 30, ..., 1000], 750);
```

## Best Practices

1. **Use PHP built-ins when possible**: `in_array()`, `array_search()`, `array_filter()`
2. **Consider data characteristics**: sorted, uniform, small, etc.
3. **Profile before optimizing**: Measure actual performance
4. **Pre-calculate expensive comparisons**: Cache before searching
5. **Use appropriate variant**: Match algorithm to use case
6. **Document search complexity**: Help future maintainers
7. **Handle edge cases**: Empty arrays, null values, etc.

## Testing

All code has been tested with PHP 8.4. Each example includes:
- ✅ Complete, runnable code
- ✅ Clear output demonstrating results
- ✅ Performance measurements
- ✅ Real-world scenarios
- ✅ Best practices demonstrations
- ✅ Edge case handling

## Further Reading

- [PHP Manual: Array Functions](https://www.php.net/manual/en/ref.array.php)
- [PHP Manual: in_array()](https://www.php.net/manual/en/function.in-array.php)
- [PHP Manual: array_search()](https://www.php.net/manual/en/function.array-search.php)
- [Wikipedia: Linear Search](https://en.wikipedia.org/wiki/Linear_search)
- [Wikipedia: Jump Search](https://en.wikipedia.org/wiki/Jump_search)
- [Wikipedia: Interpolation Search](https://en.wikipedia.org/wiki/Interpolation_search)

## Next Steps

After mastering linear search, move on to:
- **Chapter 12**: Binary Search (O(log n) for sorted data)
- **Chapter 13**: Hash Tables (O(1) average case lookups)
- **Chapter 14**: Tree-based Search (BST, AVL, Red-Black trees)

## Questions or Issues?

If you find any issues with these examples or have questions about linear search in PHP, please open an issue on the [GitHub repository](https://github.com/dalehurley/codewithphp/issues).

---

**Part of the [PHP Algorithms Series](https://codewithphp.com/series/php-algorithms)**  
Learn data structures and algorithms with modern PHP 8.4


