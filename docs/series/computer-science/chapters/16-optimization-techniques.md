---
title: "16: Optimization Techniques and Trade-offs"
description: "Balance time and space complexity. Learn memoization, lazy evaluation, caching strategies, bit manipulation tricks, and how to make informed trade-offs in algorithm design."
series: "computer-science"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites: ["Algorithm analysis", "Data structures"]
---

# Chapter 16: Optimization Techniques and Trade-offs

## Introduction

Optimization is the art of making code faster, smaller, or more efficient. Understanding trade-offs helps you make informed decisions about which optimizations matter.

In this chapter, you'll learn:

- Common optimization techniques
- Time vs. space trade-offs
- Premature optimization pitfalls
- When to optimize

## Rule #1: Measure First

> "Premature optimization is the root of all evil" — Donald Knuth

**Always**:
1. Profile to find bottlenecks
2. Optimize hot paths
3. Measure improvements

```php
<?php

// Measure execution time
$start = microtime(true);

// Your code here

$end = microtime(true);
echo "Execution time: " . ($end - $start) . " seconds\n";
```

## Optimization Techniques

### 1. Caching/Memoization

Store expensive computations.

```php
<?php

class ExpensiveOperation {
    private array $cache = [];

    public function compute(int $input): int {
        if (isset($this->cache[$input])) {
            return $this->cache[$input]; // O(1)
        }

        // Expensive computation
        $result = $this->expensiveCalculation($input);

        $this->cache[$input] = $result;
        return $result;
    }

    private function expensiveCalculation(int $n): int {
        // Simulated expensive operation
        sleep(1);
        return $n * $n;
    }
}
```

**Trade-off**: Memory for speed

### 2. Lazy Evaluation

Defer computation until needed.

```php
<?php

class LazyCollection {
    private array $data;
    private ?array $filtered = null;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function filter(callable $fn): self {
        // Don't filter yet - just store the function
        $new = new self($this->data);
        $new->filterFn = $fn;
        return $new;
    }

    public function toArray(): array {
        if ($this->filtered === null) {
            // Compute only when needed
            $this->filtered = array_filter($this->data, $this->filterFn ?? fn($x) => true);
        }
        return $this->filtered;
    }
}
```

### 3. Loop Optimization

```php
<?php

// SLOW: Repeated calculation
for ($i = 0; $i < count($array); $i++) {
    process($array[$i]);
}

// FAST: Cache length
$n = count($array);
for ($i = 0; $i < $n; $i++) {
    process($array[$i]);
}

// BETTER: Use foreach
foreach ($array as $item) {
    process($item);
}
```

### 4. Early Exit

```php
<?php

// Check conditions early
function process($data) {
    if (!$data) {
        return null; // Exit early
    }

    if (!is_valid($data)) {
        return null; // Exit early
    }

    // Expensive processing
    return compute($data);
}
```

### 5. Bit Manipulation

```php
<?php

// Check if number is even
$isEven = ($n & 1) === 0; // Faster than $n % 2 === 0

// Multiply/divide by 2
$doubled = $n << 1;  // n * 2
$halved = $n >> 1;   // n / 2

// Swap without temporary variable
$a = $a ^ $b;
$b = $a ^ $b;
$a = $a ^ $b;

// Check if power of 2
$isPowerOf2 = ($n & ($n - 1)) === 0 && $n !== 0;
```

### 6. String Building

```php
<?php

// SLOW: String concatenation in loop
$result = '';
for ($i = 0; $i < 10000; $i++) {
    $result .= "Item $i\n"; // Creates new string each time
}

// FAST: Use array + join
$parts = [];
for ($i = 0; $i < 10000; $i++) {
    $parts[] = "Item $i\n";
}
$result = implode('', $parts);
```

## Time vs. Space Trade-offs

### Example 1: Two Sum

```php
<?php

// Time O(n²), Space O(1)
function twoSumBruteForce(array $nums, int $target): ?array {
    for ($i = 0; $i < count($nums); $i++) {
        for ($j = $i + 1; $j < count($nums); $j++) {
            if ($nums[$i] + $nums[$j] === $target) {
                return [$i, $j];
            }
        }
    }
    return null;
}

// Time O(n), Space O(n)
function twoSumOptimized(array $nums, int $target): ?array {
    $seen = [];
    foreach ($nums as $i => $num) {
        $complement = $target - $num;
        if (isset($seen[$complement])) {
            return [$seen[$complement], $i];
        }
        $seen[$num] = $i;
    }
    return null;
}
```

**Trade-off**: Use extra memory to reduce time

### Example 2: Fibonacci

```php
<?php

// Time O(2^n), Space O(n) - recursion stack
function fibRecursive($n) {
    if ($n <= 1) return $n;
    return fibRecursive($n - 1) + fibRecursive($n - 2);
}

// Time O(n), Space O(n) - memoization
function fibMemo($n, &$cache = []) {
    if ($n <= 1) return $n;
    if (isset($cache[$n])) return $cache[$n];
    $cache[$n] = fibMemo($n - 1, $cache) + fibMemo($n - 2, $cache);
    return $cache[$n];
}

// Time O(n), Space O(1) - iterative
function fibIterative($n) {
    if ($n <= 1) return $n;
    $prev = 0;
    $curr = 1;
    for ($i = 2; $i <= $n; $i++) {
        $next = $prev + $curr;
        $prev = $curr;
        $curr = $next;
    }
    return $curr;
}
```

**Trade-off**: Space for speed, then optimize space

## Database Query Optimization

```php
<?php

// SLOW: N+1 query problem
$posts = $db->query("SELECT * FROM posts");
foreach ($posts as $post) {
    $author = $db->query("SELECT * FROM users WHERE id = ?", [$post['user_id']]);
}

// FAST: Use JOIN
$posts = $db->query("
    SELECT posts.*, users.name as author_name
    FROM posts
    JOIN users ON posts.user_id = users.id
");

// FAST: Eager loading
$userIds = array_column($posts, 'user_id');
$users = $db->query("SELECT * FROM users WHERE id IN (?)", [$userIds]);
```

## When to Optimize

**Optimize when**:
- Profiler shows clear bottleneck
- User experience suffers
- Scalability is critical
- Hot code path (executed frequently)

**Don't optimize when**:
- Code is already fast enough
- Makes code unreadable
- Premature (no measurements)
- Negligible improvement

## Common Optimization Patterns

| Technique | Benefit | Cost |
|-----------|---------|------|
| Caching | Faster repeated operations | Memory |
| Indexing (DB) | Faster queries | Storage, slower writes |
| Connection pooling | Faster connections | Memory |
| Batch processing | Fewer round trips | Latency |
| Asynchronous | Better throughput | Complexity |
| Precomputation | Instant results | Upfront cost |

## Key Takeaways

- **Measure** before optimizing
- **Profile** to find bottlenecks
- **Trade-offs**: Time vs. space, simplicity vs. performance
- **Premature optimization** is harmful
- **Readable code** > micro-optimizations
- **Big O** matters more than constants

## Exercises

1. **Profile code**: Find the slowest part of a given script.

2. **Optimize**: Improve a slow function using caching.

3. **Trade-off analysis**: Compare space/time for different implementations.

4. **Database**: Optimize a slow query with indexes.

## What's Next?

Optimization helps individual algorithms. **System Design** (Chapter 17) applies these principles at scale, designing entire systems for performance and reliability.

---

**Further Reading**:
- [Profiling PHP Applications](https://www.php.net/manual/en/book.xdebug.php)
- [Performance Best Practices](https://www.php.net/manual/en/features.performance.php)
