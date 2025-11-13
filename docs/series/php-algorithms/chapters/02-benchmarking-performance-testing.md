---
title: "02: Benchmarking & Performance Testing"
description: "Build a benchmarking framework to measure algorithm performance in PHP. Learn to profile code and interpret results."
series: "php-algorithms"
chapter: 2
order: 2
difficulty: "Intermediate"
prerequisites:
  - "Understanding of Big O notation"
  - "Familiarity with PHP classes"
  - "Completion of Chapters 0-1"
---

# Benchmarking & Performance Testing

In the previous chapter, we learned that some algorithms are theoretically faster than others. But how do we **prove** it? How do we measure actual performance in PHP? In this chapter, we'll build a benchmarking framework to test our algorithms and validate our complexity analysis.

## Why Benchmark?

Big O notation tells us how algorithms scale, but it doesn't give us exact timings. Benchmarking helps us:

- **Validate theoretical analysis** with real-world data
- **Compare implementations** of the same algorithm
- **Find performance bottlenecks** in our code
- **Make data-driven decisions** about optimizations
- **Understand PHP's performance characteristics**

## Building a Simple Benchmark Class

Let's create a basic benchmarking tool:

```php
class Benchmark
{
    private array $results = [];

    public function run(string $name, callable $function, int $iterations = 1): float
    {
        // Warm up (run once to avoid cold start)
        $function();

        // Force garbage collection for clean measurement
        gc_collect_cycles();

        // Measure execution time
        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $function();
        }

        $end = hrtime(true);

        // Calculate average time in milliseconds
        $totalNanoseconds = $end - $start;
        $averageMs = ($totalNanoseconds / $iterations) / 1_000_000;

        $this->results[$name] = $averageMs;

        return $averageMs;
    }

    public function compare(array $tests, mixed $input, int $iterations = 100): void
    {
        echo "Benchmarking with input size: " . (is_array($input) ? count($input) : strlen($input)) . "\n";
        echo str_repeat('-', 60) . "\n";

        foreach ($tests as $name => $function) {
            $time = $this->run($name, fn() => $function($input), $iterations);
            printf("%-30s: %10.4f ms\n", $name, $time);
        }

        echo str_repeat('-', 60) . "\n";
        $this->printRankings();
    }

    private function printRankings(): void
    {
        asort($this->results);
        $fastest = reset($this->results);

        echo "\nRankings:\n";
        $rank = 1;

        foreach ($this->results as $name => $time) {
            $ratio = $time / $fastest;
            printf("%d. %-30s (%.2fx slower)\n", $rank++, $name, $ratio);
        }

        $this->results = [];
    }
}
```

### How It Works

1. **hrtime(true)**: High-resolution timer (nanosecond precision)
2. **Warm-up run**: Prevents JIT compilation from skewing results
3. **Garbage collection**: Ensures clean memory state
4. **Multiple iterations**: Averages out random variations
5. **Results comparison**: Shows relative performance

## Using the Benchmark Class

Let's benchmark different search algorithms:

```php
// Test data
$smallArray = range(1, 100);
$largeArray = range(1, 10000);

// Linear search
function linearSearch(array $arr, int $target): int|false
{
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return false;
}

// Binary search (requires sorted array)
function binarySearch(array $arr, int $target): int|false
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        } elseif ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return false;
}

// Hash lookup (using array keys)
function hashSearch(array $arr, int $target): bool
{
    return isset($arr[$target]);
}

// Benchmark
$bench = new Benchmark();

// Small array test
$bench->compare([
    'Linear Search' => fn($arr) => linearSearch($arr, 75),
    'Binary Search' => fn($arr) => binarySearch($arr, 75),
    'Hash Lookup' => fn($arr) => hashSearch(array_flip($arr), 75),
], $smallArray);

echo "\n\n";

// Large array test
$bench->compare([
    'Linear Search' => fn($arr) => linearSearch($arr, 7500),
    'Binary Search' => fn($arr) => binarySearch($arr, 7500),
    'Hash Lookup' => fn($arr) => hashSearch(array_flip($arr), 7500),
], $largeArray);
```

**Output example:**
```
Benchmarking with input size: 100
------------------------------------------------------------
Linear Search                 :     0.0012 ms
Binary Search                 :     0.0008 ms
Hash Lookup                   :     0.0003 ms
------------------------------------------------------------

Rankings:
1. Hash Lookup                   (1.00x slower)
2. Binary Search                 (2.67x slower)
3. Linear Search                 (4.00x slower)

Benchmarking with input size: 10000
------------------------------------------------------------
Linear Search                 :     0.1200 ms
Binary Search                 :     0.0015 ms
Hash Lookup                   :     0.0003 ms
------------------------------------------------------------

Rankings:
1. Hash Lookup                   (1.00x slower)
2. Binary Search                 (5.00x slower)
3. Linear Search                 (400.00x slower)
```

Notice how **linear search gets dramatically slower** with larger inputs!

## Benchmarking Sorting Algorithms

Let's compare sorting algorithms:

```php
// Bubble Sort - O(n²)
function bubbleSort(array $arr): array
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
            }
        }
    }
    return $arr;
}

// Quick Sort - O(n log n)
function quickSort(array $arr): array
{
    if (count($arr) < 2) {
        return $arr;
    }

    $pivot = $arr[0];
    $left = $right = [];

    for ($i = 1; $i < count($arr); $i++) {
        if ($arr[$i] < $pivot) {
            $left[] = $arr[$i];
        } else {
            $right[] = $arr[$i];
        }
    }

    return array_merge(quickSort($left), [$pivot], quickSort($right));
}

// Test with random data
$sizes = [10, 50, 100, 500, 1000];

foreach ($sizes as $size) {
    $data = range(1, $size);
    shuffle($data);

    echo "Array size: $size\n";

    $bench = new Benchmark();
    $bench->compare([
        'Bubble Sort' => fn($arr) => bubbleSort($arr),
        'Quick Sort' => fn($arr) => quickSort($arr),
        'PHP sort()' => function($arr) {
            sort($arr);
            return $arr;
        },
    ], $data, iterations: 10);

    echo "\n";
}
```

## Memory Profiling

Performance isn't just about speed—memory usage matters too:

```php
class MemoryProfiler
{
    private int $startMemory;

    public function start(): void
    {
        gc_collect_cycles();
        $this->startMemory = memory_get_usage(true);
    }

    public function stop(): int
    {
        $endMemory = memory_get_usage(true);
        return $endMemory - $this->startMemory;
    }

    public function profile(string $name, callable $function): void
    {
        $this->start();
        $result = $function();
        $memory = $this->stop();

        printf(
            "%s: %s\n",
            $name,
            $this->formatBytes($memory)
        );

        return $result;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// Example: Compare memory usage
$profiler = new MemoryProfiler();

// Creating an array vs using a generator
$profiler->profile('Array (100K items)', function() {
    $data = [];
    for ($i = 0; $i < 100000; $i++) {
        $data[] = $i;
    }
    return $data;
});

$profiler->profile('Generator (100K items)', function() {
    $generator = function() {
        for ($i = 0; $i < 100000; $i++) {
            yield $i;
        }
    };

    // Consume the generator
    foreach ($generator() as $value) {
        // Process value
    }
});
```

## Understanding Benchmark Results

### Statistical Variation

Running the same test multiple times may give different results:

```php
class StatisticalBenchmark
{
    public function runWithStats(string $name, callable $function, int $runs = 10): array
    {
        $times = [];

        for ($i = 0; $i < $runs; $i++) {
            $start = hrtime(true);
            $function();
            $end = hrtime(true);

            $times[] = ($end - $start) / 1_000_000; // Convert to ms
        }

        return [
            'name' => $name,
            'min' => min($times),
            'max' => max($times),
            'avg' => array_sum($times) / count($times),
            'median' => $this->median($times),
            'stddev' => $this->stddev($times),
        ];
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = (int)($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    private function stddev(array $values): float
    {
        $avg = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => ($x - $avg) ** 2, $values)) / count($values);
        return sqrt($variance);
    }

    public function printStats(array $stats): void
    {
        printf("Function: %s\n", $stats['name']);
        printf("  Min:    %.4f ms\n", $stats['min']);
        printf("  Max:    %.4f ms\n", $stats['max']);
        printf("  Avg:    %.4f ms\n", $stats['avg']);
        printf("  Median: %.4f ms\n", $stats['median']);
        printf("  StdDev: %.4f ms\n", $stats['stddev']);
    }
}
```

### Growth Rate Analysis

Test with increasing input sizes to visualize Big O:

```php
function analyzeGrowth(callable $algorithm, array $sizes, int $iterations = 10): void
{
    echo "Input Size | Time (ms) | Growth Factor\n";
    echo str_repeat('-', 45) . "\n";

    $previousTime = null;

    foreach ($sizes as $size) {
        $data = range(1, $size);
        shuffle($data);

        $bench = new Benchmark();
        $time = $bench->run("Size $size", fn() => $algorithm($data), $iterations);

        $growthFactor = $previousTime ? $time / $previousTime : 1.0;

        printf(
            "%10d | %9.4f | %.2fx\n",
            $size,
            $time,
            $growthFactor
        );

        $previousTime = $time;
    }
}

// Test bubble sort growth
echo "Bubble Sort Growth:\n";
analyzeGrowth(fn($arr) => bubbleSort($arr), [100, 200, 400, 800]);
```

**Expected output:**
```
Bubble Sort Growth:
Input Size | Time (ms) | Growth Factor
---------------------------------------------
       100 |    0.5000 | 1.00x
       200 |    2.0000 | 4.00x  (doubled size = 4x time)
       400 |    8.0000 | 4.00x  (confirms O(n²))
       800 |   32.0000 | 4.00x
```

## Real-World Benchmarking Example

Let's optimize a practical function:

```php
// Version 1: Check if email exists in database (naive)
function emailExists_v1(PDO $pdo, string $email): bool
{
    $stmt = $pdo->query("SELECT email FROM users");
    $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return in_array($email, $emails);
}

// Version 2: Use SQL WHERE clause
function emailExists_v2(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

// Version 3: Use EXISTS (most efficient)
function emailExists_v3(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare("SELECT EXISTS(SELECT 1 FROM users WHERE email = ?)");
    $stmt->execute([$email]);
    return (bool)$stmt->fetchColumn();
}

// Benchmark (assuming database connection $pdo)
$bench = new Benchmark();
$testEmail = 'test@example.com';

$bench->compare([
    'Version 1 (fetch all)' => fn() => emailExists_v1($pdo, $testEmail),
    'Version 2 (COUNT)' => fn() => emailExists_v2($pdo, $testEmail),
    'Version 3 (EXISTS)' => fn() => emailExists_v3($pdo, $testEmail),
], $testEmail, iterations: 100);
```

## Common Benchmarking Pitfalls

### Pitfall 1: Not Warming Up

```php
// Bad: First run includes JIT compilation overhead
$time = benchmark($function);

// Good: Warm up first
$function(); // Warm-up run
$time = benchmark($function);
```

### Pitfall 2: Small Sample Sizes

```php
// Bad: Single run, unreliable
$start = microtime(true);
$function();
$time = microtime(true) - $start;

// Good: Average multiple runs
$times = [];
for ($i = 0; $i < 100; $i++) {
    $start = microtime(true);
    $function();
    $times[] = microtime(true) - $start;
}
$avgTime = array_sum($times) / count($times);
```

### Pitfall 3: Using microtime() Instead of hrtime()

```php
// Bad: Low resolution (microseconds)
$start = microtime(true);
// ... code ...
$elapsed = microtime(true) - $start;

// Good: High resolution (nanoseconds)
$start = hrtime(true);
// ... code ...
$elapsed = hrtime(true) - $start;
```

### Pitfall 4: Not Considering Overhead

```php
// Measure loop overhead
$bench = new Benchmark();

$overhead = $bench->run('Empty loop', function() {
    for ($i = 0; $i < 1000; $i++) {
        // Empty
    }
}, 1000);

$withWork = $bench->run('Loop with work', function() {
    for ($i = 0; $i < 1000; $i++) {
        $x = $i * 2;
    }
}, 1000);

$actualWork = $withWork - $overhead;
echo "Actual work time: {$actualWork}ms\n";
```

## PHP Performance Tips

Based on benchmarking, here are PHP-specific optimizations:

### Tip 1: Pre-calculate count()

```php
// Slow: count() is called in every iteration
for ($i = 0; $i < count($array); $i++) { }

// Fast: count() called once
$n = count($array);
for ($i = 0; $i < $n; $i++) { }
```

### Tip 2: Use isset() Over in_array()

```php
// Slow: O(n)
if (in_array($key, $array)) { }

// Fast: O(1)
if (isset($array[$key])) { }
```

### Tip 3: String Concatenation

```php
// Slow: Creates new string each time
$result = '';
for ($i = 0; $i < 1000; $i++) {
    $result .= $i . ',';
}

// Fast: Build array then join
$parts = [];
for ($i = 0; $i < 1000; $i++) {
    $parts[] = $i;
}
$result = implode(',', $parts);
```

## Practice Exercises

### Exercise 1: Benchmark Array Functions

Compare these three ways to filter an array:

```php
$numbers = range(1, 10000);

// Method 1: foreach loop
function filterLoop(array $arr): array {
    $result = [];
    foreach ($arr as $num) {
        if ($num % 2 === 0) {
            $result[] = $num;
        }
    }
    return $result;
}

// Method 2: array_filter
function filterBuiltIn(array $arr): array {
    return array_filter($arr, fn($n) => $n % 2 === 0);
}

// Method 3: array_filter with array_values (re-index)
function filterReindex(array $arr): array {
    return array_values(array_filter($arr, fn($n) => $n % 2 === 0));
}

// Your task: Benchmark these three methods
```

### Exercise 2: Find the Bottleneck

This function is slow. Use benchmarking to find why:

```php
function processUsers(array $users): array
{
    $result = [];

    foreach ($users as $user) {
        // Check if email is valid
        if (filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
            // Get user's posts
            $posts = getUserPosts($user['id']); // Database query

            // Count active posts
            $activePosts = 0;
            foreach ($posts as $post) {
                if ($post['status'] === 'active') {
                    $activePosts++;
                }
            }

            $result[] = [
                'name' => $user['name'],
                'email' => $user['email'],
                'active_posts' => $activePosts
            ];
        }
    }

    return $result;
}
```

<details>
<summary>Hint</summary>
Use benchmarking to isolate each part: email validation, database queries, and counting. The database query in the loop is likely the bottleneck (N+1 query problem).
</details>

## Key Takeaways

- **Always benchmark** before optimizing—measure, don't guess
- Use **hrtime()** for precise measurements
- Run **multiple iterations** and calculate averages
- Consider both **time and space** complexity
- Test with **various input sizes** to confirm Big O analysis
- **Warm up** before measuring to avoid JIT overhead
- Watch for **hidden costs** in PHP functions

## What's Next

In the next chapter, we'll dive deep into **Recursion Fundamentals**, learning to write elegant recursive solutions and understanding their performance characteristics.

---

Continue to [Chapter 03: Recursion Fundamentals](/series/php-algorithms/chapters/03-recursion-fundamentals).
