# Chapter 10: PHP's Built-in Sorting Functions

This directory contains working code examples demonstrating PHP's powerful built-in sorting functions and custom comparison techniques.

## Files

### 01-basic-sorting-functions.php
Complete demonstration of all basic PHP sorting functions:
- `sort()`, `rsort()` - Sort by value, reset keys
- `asort()`, `arsort()` - Sort by value, maintain keys  
- `ksort()`, `krsort()` - Sort by keys
- `natsort()`, `natcasesort()` - Natural sorting
- Sort flags (SORT_NUMERIC, SORT_STRING, SORT_FLAG_CASE)
- Performance comparisons
- Real-world examples

### 02-custom-sorting-usort.php
Advanced custom sorting with comparison functions:
- `usort()`, `uasort()`, `uksort()` - User-defined sorting
- Spaceship operator (`<=>`) for clean comparisons
- Arrow functions for concise syntax
- Sorting objects by properties
- Multi-level sorting (multiple sort criteria)
- Handling null values
- Case-insensitive sorting
- Performance analysis
- Real-world e-commerce example

### 03-advanced-sorting-techniques.php
Advanced topics and professional techniques:
- PHP Intl extension (`Collator`) for proper internationalization
- `SplFixedArray` performance optimization
- `shuffle()` and Fisher-Yates randomization
- Sorting mixed-type arrays
- Pre-computing sort keys for performance
- Multi-key sorting with Elvis operator
- Stable sorting with tie-breaking
- Version number sorting with `version_compare()`
- Bucket sort pattern with `array_reduce()`
- Generators and sorting (important gotchas)

## Running the Examples

Each file is a complete, runnable PHP script:

```bash
# Basic sorting functions
php 01-basic-sorting-functions.php

# Custom sorting with usort()
php 02-custom-sorting-usort.php

# Advanced sorting techniques
php 03-advanced-sorting-techniques.php
```

## Requirements

- **PHP 8.4+** (uses modern PHP features)
- No external dependencies required

## What You'll Learn

### Basic Sorting Functions
- When to use each sorting function
- Understanding key preservation vs reset
- Sort flags for different comparison types
- Natural sorting for human-friendly ordering
- Performance characteristics

### Custom Sorting
- Writing comparison functions
- Using the spaceship operator (`<=>`)
- Multi-level sorting strategies
- Sorting complex data structures
- Best practices and performance considerations

## Key Concepts

### Function Selection
```php
// Need to keep array keys?
asort($array);  // Yes - use asort/arsort
sort($array);   // No - use sort/rsort

// Sort by keys instead of values?
ksort($array);  // Yes - use ksort/krsort

// Custom comparison logic?
usort($array, fn($a, $b) => $a <=> $b);  // Use usort/uasort/uksort
```

### Spaceship Operator
```php
// Returns: -1 if $a < $b, 0 if equal, 1 if $a > $b
$result = $a <=> $b;

// Ascending
usort($arr, fn($a, $b) => $a <=> $b);

// Descending
usort($arr, fn($a, $b) => $b <=> $a);
```

### Multi-Level Sorting
```php
usort($students, function($a, $b) {
    // Primary sort
    $gradeCompare = $b->grade <=> $a->grade;
    if ($gradeCompare !== 0) return $gradeCompare;
    
    // Secondary sort (tie-breaker)
    return $a->age <=> $b->age;
});
```

## Performance Notes

**Built-in functions are highly optimized:**
- `sort()`: ~3.5ms for 10,000 elements
- `asort()`: ~4.2ms (slight overhead for key preservation)
- `usort()`: ~15ms (3-4x slower due to callback overhead)
- `natsort()`: ~12ms (natural comparison overhead)

**Best practices:**
1. Use built-in functions when possible
2. Pre-calculate expensive values before sorting
3. Choose the right function for your key requirements
4. Use natural sort for human-friendly data

## Real-World Applications

### E-commerce Product Sorting
```php
// Sort by multiple criteria
usort($products, function($a, $b) {
    $price = $a['price'] <=> $b['price'];
    if ($price !== 0) return $price;
    return $b['rating'] <=> $a['rating'];
});
```

### Search Result Ranking
```php
// Primary: relevance, Secondary: popularity, Tertiary: date
usort($results, function($a, $b) {
    if ($a->score !== $b->score) return $b->score <=> $a->score;
    if ($a->views !== $b->views) return $b->views <=> $a->views;
    return $b->date <=> $a->date;
});
```

### File Listings
```php
// Natural sorting for file names
$files = ['file1.txt', 'file10.txt', 'file2.txt'];
natsort($files); // ['file1.txt', 'file2.txt', 'file10.txt']
```

## Common Patterns

### Sort by Array Column
```php
// Extract column, sort together
$ages = array_column($users, 'age');
array_multisort($ages, SORT_ASC, $users);
```

### Maintain Stable Order
```php
// Add secondary sort for stability
usort($data, function($a, $b) {
    $primary = $a['value'] <=> $b['value'];
    return $primary !== 0 ? $primary : $a['id'] <=> $b['id'];
});
```

### Handle Null Values
```php
usort($data, function($a, $b) {
    if ($a === null && $b === null) return 0;
    if ($a === null) return 1;  // Nulls at end
    if ($b === null) return -1;
    return $a <=> $b;
});
```

## Testing

All code has been tested with PHP 8.4. Each example includes:
- ✅ Complete, runnable code
- ✅ Clear output demonstrating results
- ✅ Performance measurements
- ✅ Real-world scenarios
- ✅ Best practices demonstrations

## Further Reading

- [PHP Manual: Array Sorting Functions](https://www.php.net/manual/en/array.sorting.php)
- [PHP Manual: usort()](https://www.php.net/manual/en/function.usort.php)
- [PHP RFC: Spaceship Operator](https://wiki.php.net/rfc/combined-comparison-operator)
- [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/)

## Questions or Issues?

If you find any issues with these examples or have questions about sorting in PHP, please open an issue on the [GitHub repository](https://github.com/dalehurley/codewithphp/issues).

---

**Part of the [PHP Algorithms Series](https://codewithphp.com/series/php-algorithms)**  
Learn data structures and algorithms with modern PHP 8.4

