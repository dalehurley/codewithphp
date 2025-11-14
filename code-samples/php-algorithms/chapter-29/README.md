# Chapter 29: Performance Optimization

Practical optimization techniques with benchmarking and profiling tools.

## Code Samples

### benchmark-framework.php
**Complete Performance Optimization Suite**

Demonstrates key optimization techniques:
- Memory optimization (references, generators)
- String concatenation
- PHP 8+ features (match, typed properties)
- Benchmarking framework
- Generator vs array comparison

**Run:** `php benchmark-framework.php`

## Key Optimizations

### Memory
```php
// Bad: Copies array
function sum(array $data): int { ... }

// Good: Uses reference
function sum(array &$data): int { ... }

// Best: Built-in function
array_sum(array_column($data, 'value'))
```

### Generators
```php
// Memory-efficient for large datasets
function range(int $start, int $end): Generator {
    for ($i = $start; $i <= $end; $i++) {
        yield $i;  // Constant memory
    }
}
```

### PHP 8+ Features
- **Match:** 20% faster than switch
- **Typed Properties:** Enable JIT optimizations
- **Constructor Promotion:** Cleaner, equally fast

## Performance Benchmarks

| Optimization | Improvement |
|--------------|-------------|
| OPcache | 2-3x faster |
| JIT (PHP 8.1+) | 1.5-3x for CPU-intensive |
| Match vs Switch | ~20% faster |
| Generators | 90%+ memory savings |
| Implode vs concat | 5-10x faster |

## Profiling Tools

1. **Xdebug:** Function-level profiling
2. **Blackfire:** Production profiler
3. **Tideways:** APM solution
4. **Built-in:** microtime(), memory_get_usage()

## Best Practices

1. ✓ Profile before optimizing
2. ✓ Use built-in functions
3. ✓ Enable OPcache in production
4. ✓ Minimize memory allocations
5. ✓ Use generators for large data
6. ✓ PHP 8+ typed properties

## Requirements

- PHP 8.0+ (for match, typed properties)
- Optional: Xdebug, OPcache

**Next:** [Chapter 30: Real-World Case Studies](../chapter-30/)
