# Chapter 01: Algorithm Complexity & Big O Notation - Code Samples

This directory contains all code examples from **Chapter 01: Algorithm Complexity & Big O Notation** of the **Algorithms for PHP Developers** series.

## 📁 Files

### 1. `01-complexity-examples.php`
**Purpose**: Demonstrates all major time complexity classes with working PHP code

**Contains**:
- ✅ **O(1)** - Constant time operations (array access, hash lookups)
- ✅ **O(log n)** - Logarithmic time (binary search)
- ✅ **O(n)** - Linear time (sum, max, filter operations)
- ✅ **O(n log n)** - Linearithmic time (merge sort)
- ✅ **O(n²)** - Quadratic time (bubble sort, all pairs)
- ✅ **O(2ⁿ)** - Exponential time (naive Fibonacci)
- ✅ Practical comparisons (hash lookup vs linear search)
- ✅ Optimization examples (O(n²) to O(n) using hash sets)

**Run it**:
```bash
php 01-complexity-examples.php
```

**Expected output**: Demonstrates each complexity class with timing comparisons and real-world performance differences.

---

### 2. `02-space-complexity.php`
**Purpose**: Shows memory usage patterns and space complexity analysis

**Contains**:
- ✅ **O(1) space** - Constant memory (in-place operations)
- ✅ **O(n) space** - Linear memory (copying arrays, hash sets)
- ✅ **O(n²) space** - Quadratic memory (all pairs generation)
- ✅ Recursive call stack memory usage
- ✅ In-place vs copy algorithm comparisons
- ✅ Generator functions for memory efficiency
- ✅ Memoization (trading space for time)
- ✅ Actual memory usage measurements

**Run it**:
```bash
php 02-space-complexity.php
```

**Expected output**: Memory usage comparisons, space optimization techniques, and memoization performance improvements.

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.4 or higher
- Command-line access

### Verify PHP Version
```bash
php --version
# Should output: PHP 8.4.x or higher
```

### Run All Examples
```bash
# From the chapter-01 directory
php 01-complexity-examples.php
php 02-space-complexity.php

# Or from the repository root
php code-samples/php-algorithms/chapter-01/01-complexity-examples.php
php code-samples/php-algorithms/chapter-01/02-space-complexity.php
```

---

## 📚 What You'll Learn

### Time Complexity (01-complexity-examples.php)
- How different algorithms scale with input size
- When to use each complexity class
- Performance differences with real benchmarks
- Optimization strategies (hash lookups, algorithm selection)

### Space Complexity (02-space-complexity.php)
- Memory usage patterns for algorithms
- In-place vs copy approaches
- Generator functions for large datasets
- Memoization trade-offs

---

## 🧪 Experimentation Ideas

### Modify Time Complexity Examples
Try changing these values to see how performance scales:

```php
// In binarySearch example
$sortedNumbers = range(1, 1000000); // Try 10k, 100k, 1M
$index = binarySearch($sortedNumbers, 742518);

// In fibonacci example
$result = fibonacci(25); // Try 15, 20, 25, 30 (careful: exponential!)

// In user search comparison
// Create 10k or 100k users instead of 1,000
for ($i = 1; $i <= 100000; $i++) {
    // ...
}
```

### Modify Space Complexity Examples
Experiment with different input sizes:

```php
// Test memory with larger datasets
$sizes = [1000, 10000, 100000]; // Watch memory usage scale

// Compare generator vs array for huge ranges
$n = 1000000; // One million items
```

---

## 💡 Key Takeaways

From **01-complexity-examples.php**:
- **O(1)** is ideal - instant regardless of size
- **O(log n)** is excellent - scales to millions
- **O(n)** is good - acceptable for most cases
- **O(n log n)** is efficient - best general sorting
- **O(n²)** gets slow - avoid for large data
- **O(2ⁿ)** is unusable - only for tiny inputs

From **02-space-complexity.php**:
- Space complexity matters as much as time
- Generators save memory for large datasets
- Memoization trades space for speed
- In-place algorithms use O(1) space
- Recursive functions use O(n) call stack

---

## 🔗 Related Resources

- **Chapter Tutorial**: [Algorithm Complexity & Big O Notation](https://codewithphp.com/series/php-algorithms/chapters/01-algorithm-complexity-big-o-notation)
- **Series Overview**: [Algorithms for PHP Developers](https://codewithphp.com/series/php-algorithms/)
- **Next Chapter**: [Chapter 02 - Benchmarking & Performance Testing](https://codewithphp.com/series/php-algorithms/chapters/02-benchmarking-performance-testing)

---

## 🐛 Troubleshooting

### Error: "php: command not found"
**Solution**: Install PHP 8.4 or add it to your PATH

```bash
# macOS (Homebrew)
brew install php@8.4

# Ubuntu/Debian
sudo apt install php8.4-cli

# Verify installation
php --version
```

### Warning: "Division by zero"
**Solution**: Some examples include safeguards, but ensure input arrays aren't empty:

```php
// Always check before processing
if (empty($numbers)) {
    throw new InvalidArgumentException('Array cannot be empty');
}
```

### Memory Exhausted
**Solution**: If testing with very large datasets, increase PHP memory limit:

```bash
php -d memory_limit=512M 01-complexity-examples.php
```

Or modify `php.ini`:
```ini
memory_limit = 512M
```

### Slow Performance on fibonacci(30+)
**Expected behavior**: The naive recursive Fibonacci is **intentionally slow** (O(2ⁿ) complexity) to demonstrate exponential growth. Use the memoized version in `02-space-complexity.php` for larger values.

---

## 📝 Notes

- All code uses **strict types** (`declare(strict_types=1)`)
- Examples follow **PSR-12** coding standards
- Functions include proper **type hints** and **return types**
- Code is **production-ready** but simplified for teaching

---

## 🎯 Practice Challenges

After running these examples, try:

1. **Implement your own** O(log n) algorithm (hint: binary search variations)
2. **Optimize** the bubble sort to stop early when array is sorted
3. **Create** a memoized version of factorial function
4. **Measure** the break-even point where hash lookup beats linear search
5. **Write** a generator that yields Fibonacci numbers indefinitely

---

**Happy learning!** 🚀

For questions or issues, visit the [GitHub repository](https://github.com/dalehurley/codewithphp) or check the [chapter tutorial](https://codewithphp.com/series/php-algorithms/chapters/01-algorithm-complexity-big-o-notation).
