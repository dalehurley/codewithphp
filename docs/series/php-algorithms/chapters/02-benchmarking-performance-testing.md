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

## Professional Profiling Tools

While our custom benchmark class is useful, production applications benefit from professional tools.

### Xdebug Profiler

Xdebug provides detailed profiling with function call traces:

```php
// Enable in php.ini
// xdebug.mode = profile
// xdebug.output_dir = /tmp/xdebug
// xdebug.profiler_output_name = cachegrind.out.%p

function complexOperation(): void
{
    // Your code here
    processData();
    queryDatabase();
    renderTemplate();
}

// Generates cachegrind.out file
// Analyze with: qcachegrind cachegrind.out.12345
```

**Xdebug provides:**
- Function call times
- Memory usage per function
- Call graphs
- Number of invocations

### Blackfire.io

Professional PHP profiler with beautiful visualizations:

```php
// Install Blackfire probe and CLI
// Then profile any script:

// blackfire run php your-script.php

// Or profile web requests via browser extension
// Provides: flame graphs, call graphs, recommendations
```

**Blackfire features:**
- Comparison between profiles
- Automated performance testing
- Production-safe profiling
- SQL query analysis

### Tideways / XHProf

Lightweight profilers suitable for production:

```php
// Start profiling
xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

// Your application code
yourApplication();

// Stop profiling and get data
$xhprof_data = xhprof_disable();

// Save or display results
print_r($xhprof_data);
```

## Profiling in Production

### Safe Production Profiling

```php
class ProductionProfiler
{
    private float $sampleRate = 0.01; // Profile 1% of requests

    public function shouldProfile(): bool
    {
        // Only profile a small percentage
        return (mt_rand() / mt_getrandmax()) < $this->sampleRate;
    }

    public function profileRequest(callable $handler): mixed
    {
        if (!$this->shouldProfile()) {
            return $handler();
        }

        // Profile this request
        $start = hrtime(true);
        $startMemory = memory_get_usage();

        try {
            $result = $handler();
        } finally {
            $duration = (hrtime(true) - $start) / 1_000_000; // ms
            $memoryUsed = memory_get_usage() - $startMemory;

            $this->logMetrics([
                'duration_ms' => $duration,
                'memory_bytes' => $memoryUsed,
                'endpoint' => $_SERVER['REQUEST_URI'] ?? 'cli',
                'timestamp' => time(),
            ]);
        }

        return $result;
    }

    private function logMetrics(array $metrics): void
    {
        // Send to logging service (e.g., CloudWatch, Datadog)
        error_log(json_encode($metrics));
    }
}

// Usage in your application
$profiler = new ProductionProfiler();
$response = $profiler->profileRequest(function() {
    return handleRequest();
});
```

### Application Performance Monitoring (APM)

Integrate with APM tools for continuous monitoring:

```php
// New Relic integration example
class NewRelicMonitor
{
    public function trackTransaction(string $name, callable $callback): mixed
    {
        if (extension_loaded('newrelic')) {
            newrelic_name_transaction($name);
            $startTime = microtime(true);
        }

        try {
            return $callback();
        } finally {
            if (extension_loaded('newrelic')) {
                $duration = microtime(true) - $startTime;
                newrelic_custom_metric('Custom/TransactionTime', $duration);
            }
        }
    }

    public function trackError(\Throwable $e): void
    {
        if (extension_loaded('newrelic')) {
            newrelic_notice_error($e->getMessage(), $e);
        }
    }
}
```

## Database Query Profiling

Database queries are often the biggest bottleneck:

```php
class QueryProfiler
{
    private array $queries = [];

    public function profile(PDO $pdo, string $sql, array $params = []): mixed
    {
        $start = hrtime(true);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();

        $duration = (hrtime(true) - $start) / 1_000_000; // ms

        $this->queries[] = [
            'sql' => $sql,
            'duration' => $duration,
            'rows' => count($result),
            'params' => $params,
        ];

        // Warn on slow queries
        if ($duration > 100) { // > 100ms
            error_log("Slow query ({$duration}ms): {$sql}");
        }

        return $result;
    }

    public function getReport(): array
    {
        $total = array_sum(array_column($this->queries, 'duration'));
        $slowest = max(array_column($this->queries, 'duration'));
        $count = count($this->queries);

        return [
            'query_count' => $count,
            'total_time' => $total,
            'average_time' => $count > 0 ? $total / $count : 0,
            'slowest_query' => $slowest,
            'queries' => $this->queries,
        ];
    }

    public function printReport(): void
    {
        $report = $this->getReport();

        echo "\nDatabase Query Report:\n";
        echo str_repeat('=', 60) . "\n";
        printf("Total Queries: %d\n", $report['query_count']);
        printf("Total Time: %.2f ms\n", $report['total_time']);
        printf("Average Time: %.2f ms\n", $report['average_time']);
        printf("Slowest Query: %.2f ms\n", $report['slowest_query']);
        echo str_repeat('=', 60) . "\n\n";

        echo "Individual Queries:\n";
        foreach ($report['queries'] as $i => $query) {
            printf(
                "%d. [%.2f ms] [%d rows] %s\n",
                $i + 1,
                $query['duration'],
                $query['rows'],
                substr($query['sql'], 0, 80)
            );
        }
    }
}

// Usage
$profiler = new QueryProfiler();
$profiler->profile($pdo, "SELECT * FROM users WHERE active = ?", [1]);
$profiler->profile($pdo, "SELECT * FROM posts WHERE user_id = ?", [123]);
$profiler->printReport();
```

### Detecting N+1 Query Problems

```php
class N1QueryDetector
{
    private array $queryHashes = [];
    private int $threshold = 10;

    public function logQuery(string $sql): void
    {
        // Normalize SQL (remove specific values)
        $normalized = preg_replace('/\d+/', '?', $sql);
        $hash = md5($normalized);

        if (!isset($this->queryHashes[$hash])) {
            $this->queryHashes[$hash] = [
                'sql' => $normalized,
                'count' => 0,
            ];
        }

        $this->queryHashes[$hash]['count']++;

        // Alert on repeated similar queries
        if ($this->queryHashes[$hash]['count'] === $this->threshold) {
            trigger_error(
                "Potential N+1 query detected: {$normalized} executed {$this->threshold} times",
                E_USER_WARNING
            );
        }
    }
}
```

## Memory Leak Detection

Detect and prevent memory leaks in long-running processes:

```php
class MemoryLeakDetector
{
    private int $baseline;
    private int $threshold;

    public function __construct(int $thresholdMB = 50)
    {
        $this->baseline = memory_get_usage(true);
        $this->threshold = $thresholdMB * 1024 * 1024;
    }

    public function check(string $context = ''): void
    {
        $current = memory_get_usage(true);
        $increase = $current - $this->baseline;

        if ($increase > $this->threshold) {
            $mb = round($increase / 1024 / 1024, 2);
            trigger_error(
                "Possible memory leak detected{$context}: {$mb} MB increase",
                E_USER_WARNING
            );

            // Optional: dump memory info
            if (function_exists('memory_get_peak_usage')) {
                $peak = memory_get_peak_usage(true) / 1024 / 1024;
                error_log("Peak memory: {$peak} MB");
            }
        }
    }

    public function reset(): void
    {
        gc_collect_cycles();
        $this->baseline = memory_get_usage(true);
    }
}

// Usage in long-running script
$detector = new MemoryLeakDetector(50); // 50 MB threshold

for ($i = 0; $i < 10000; $i++) {
    processItem($i);

    if ($i % 1000 === 0) {
        $detector->check(" at iteration {$i}");
        $detector->reset(); // Reset baseline periodically
    }
}
```

## CI/CD Integration

Automate performance testing in your deployment pipeline:

### GitHub Actions Example

```yaml
# .github/workflows/performance.yml
name: Performance Tests

on: [pull_request]

jobs:
  benchmark:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: xdebug

      - name: Install Dependencies
        run: composer install

      - name: Run Benchmarks
        run: php tests/benchmarks/run-all.php --output=json > results.json

      - name: Compare with Baseline
        run: php tests/benchmarks/compare.php results.json baseline.json

      - name: Comment on PR
        uses: actions/github-script@v6
        with:
          script: |
            const fs = require('fs');
            const results = JSON.parse(fs.readFileSync('results.json'));
            // Post results as PR comment
```

### Automated Benchmark Script

```php
// tests/benchmarks/run-all.php
class BenchmarkRunner
{
    private array $results = [];

    public function runAll(): void
    {
        $this->results['sorting'] = $this->benchmarkSorting();
        $this->results['searching'] = $this->benchmarkSearching();
        $this->results['database'] = $this->benchmarkDatabase();
    }

    private function benchmarkSorting(): array
    {
        $bench = new Benchmark();
        $data = $this->generateTestData(1000);

        return [
            'bubble_sort' => $bench->run('bubble', fn() => bubbleSort($data), 10),
            'quick_sort' => $bench->run('quick', fn() => quickSort($data), 10),
            'php_sort' => $bench->run('native', function() use ($data) {
                sort($data);
                return $data;
            }, 10),
        ];
    }

    public function compareWithBaseline(array $baseline): array
    {
        $regressions = [];

        foreach ($this->results as $category => $tests) {
            foreach ($tests as $test => $time) {
                $baselineTime = $baseline[$category][$test] ?? null;

                if ($baselineTime && $time > $baselineTime * 1.1) {
                    $increase = (($time / $baselineTime) - 1) * 100;
                    $regressions[] = [
                        'test' => "{$category}.{$test}",
                        'baseline' => $baselineTime,
                        'current' => $time,
                        'increase' => $increase,
                    ];
                }
            }
        }

        return $regressions;
    }

    public function outputJSON(): void
    {
        echo json_encode($this->results, JSON_PRETTY_PRINT);
    }

    private function generateTestData(int $size): array
    {
        $data = range(1, $size);
        shuffle($data);
        return $data;
    }
}

// Run benchmarks
$runner = new BenchmarkRunner();
$runner->runAll();
$runner->outputJSON();
```

### Performance Regression Detection

```php
class PerformanceGuard
{
    private float $maxRegression = 0.10; // 10% slower is a failure

    public function assertPerformance(
        callable $function,
        float $baselineMs,
        string $name = 'test'
    ): void {
        $bench = new Benchmark();
        $actual = $bench->run($name, $function, 100);

        $ratio = $actual / $baselineMs;

        if ($ratio > (1 + $this->maxRegression)) {
            $increase = ($ratio - 1) * 100;
            throw new Exception(
                "{$name} performance regression: {$increase}% slower than baseline"
            );
        }

        echo "✓ {$name} passed: {$actual}ms (baseline: {$baselineMs}ms)\n";
    }
}

// Usage in tests
$guard = new PerformanceGuard();
$guard->assertPerformance(
    fn() => bubbleSort(range(1, 100)),
    5.0, // baseline: 5ms
    'bubble_sort_100'
);
```

## Load Testing

Test performance under concurrent load:

```php
// Simple parallel request simulator
class LoadTester
{
    public function test(string $url, int $requests, int $concurrency): array
    {
        $results = [];
        $batches = ceil($requests / $concurrency);

        for ($batch = 0; $batch < $batches; $batch++) {
            $start = hrtime(true);

            // Simulate concurrent requests (in reality, use tools like Apache Bench)
            $batchResults = $this->runBatch($url, min($concurrency, $requests - ($batch * $concurrency)));

            $duration = (hrtime(true) - $start) / 1_000_000_000; // seconds
            $results = array_merge($results, $batchResults);

            echo "Batch " . ($batch + 1) . " completed: {$duration}s\n";
        }

        return $this->analyzeResults($results);
    }

    private function runBatch(string $url, int $count): array
    {
        $results = [];

        // In reality, use curl_multi for true parallelism
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            file_get_contents($url);
            $duration = microtime(true) - $start;
            $results[] = $duration;
        }

        return $results;
    }

    private function analyzeResults(array $results): array
    {
        sort($results);
        $count = count($results);

        return [
            'total_requests' => $count,
            'min' => min($results),
            'max' => max($results),
            'mean' => array_sum($results) / $count,
            'median' => $results[(int)($count / 2)],
            'p95' => $results[(int)($count * 0.95)],
            'p99' => $results[(int)($count * 0.99)],
        ];
    }
}

// Better: Use Apache Bench from command line
// ab -n 1000 -c 10 http://localhost/api/endpoint
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

### Exercise 3: Create a Memory Profiler

Build a profiler that tracks memory usage across different operations:

```php
class MemoryBenchmark
{
    // Your implementation here
    public function profile(string $name, callable $fn): void
    {
        // Track memory before and after
        // Store results
    }

    public function getResults(): array
    {
        // Return all profiled operations
    }
}

// Test it on operations that use different amounts of memory
```

### Exercise 4: Detect Performance Regression

Write a test that fails if performance regresses more than 20%:

```php
function testSortPerformance(): void
{
    $baseline = 10.5; // ms (from previous run)

    // Your code here to:
    // 1. Benchmark current performance
    // 2. Compare to baseline
    // 3. Fail test if >20% slower
}
```

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
