---
title: "Performance Optimization"
description: "Master performance optimization techniques including profiling, benchmarking, memory optimization, and PHP-specific optimizations for algorithms"
series: "php-algorithms"
chapter: 29
order: 29
difficulty: "advanced"
prerequisites: ["Algorithm Selection Guide", "Benchmarking & Performance Testing"]
---

# Performance Optimization

Performance optimization is about making code run faster and use less memory. This chapter covers profiling, benchmarking, and PHP-specific optimization techniques.

## Profiling and Measurement

### Basic Benchmarking

```php
<?php

class Benchmark
{
    private float $startTime;
    private int $startMemory;

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    public function end(): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        return [
            'time' => ($endTime - $this->startTime) * 1000,  // ms
            'memory' => ($endMemory - $this->startMemory) / 1024,  // KB
            'peak_memory' => memory_get_peak_usage() / 1024  // KB
        ];
    }

    public function measure(callable $fn, array $args = []): array
    {
        $this->start();
        $result = $fn(...$args);
        $stats = $this->end();
        $stats['result'] = $result;

        return $stats;
    }

    public function compare(array $functions, array $args = []): array
    {
        $results = [];

        foreach ($functions as $name => $fn) {
            $results[$name] = $this->measure($fn, $args);
        }

        return $results;
    }
}

// Usage
$bench = new Benchmark();

function bubbleSort(array $arr): array {
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

function quickSortWrapper(array $arr): array {
    if (count($arr) < 2) return $arr;
    $pivot = $arr[0];
    $left = array_filter(array_slice($arr, 1), fn($x) => $x <= $pivot);
    $right = array_filter(array_slice($arr, 1), fn($x) => $x > $pivot);
    return array_merge(quickSortWrapper($left), [$pivot], quickSortWrapper($right));
}

$data = range(1, 100);
shuffle($data);

$results = $bench->compare([
    'Bubble Sort' => 'bubbleSort',
    'Quick Sort' => 'quickSortWrapper',
    'PHP sort()' => function($arr) {
        sort($arr);
        return $arr;
    }
], [$data]);

foreach ($results as $name => $stats) {
    echo "$name: {$stats['time']}ms, {$stats['memory']}KB\n";
}
```

### Xdebug Profiling

```php
<?php

class XdebugProfiler
{
    public function enableProfiling(string $outputDir = '/tmp'): void
    {
        if (!extension_loaded('xdebug')) {
            throw new RuntimeException('Xdebug extension not loaded');
        }

        xdebug_start_trace($outputDir . '/trace');
    }

    public function disableProfiling(): void
    {
        xdebug_stop_trace();
    }

    public function profileFunction(callable $fn, array $args = []): mixed
    {
        xdebug_start_trace();
        $result = $fn(...$args);
        xdebug_stop_trace();

        return $result;
    }

    public function getMemoryUsage(): array
    {
        return [
            'current' => memory_get_usage(true) / 1024 / 1024,  // MB
            'peak' => memory_get_peak_usage(true) / 1024 / 1024  // MB
        ];
    }
}

// Usage
// Configure in php.ini:
// xdebug.mode=profile
// xdebug.output_dir=/tmp
// xdebug.profiler_output_name=cachegrind.out.%p
//
// Then analyze with:
// kcachegrind /tmp/cachegrind.out.12345
```

## Memory Optimization

### Reference vs Value

```php
<?php

class MemoryOptimization
{
    // Bad: Creates copy of large array
    public function processBad(array $data): int
    {
        $sum = 0;
        foreach ($data as $item) {
            $sum += $item['value'];
        }
        return $sum;
    }

    // Good: Uses reference to avoid copy
    public function processGood(array &$data): int
    {
        $sum = 0;
        foreach ($data as &$item) {
            $sum += $item['value'];
        }
        return $sum;
    }

    // Best: No reference needed if not modifying
    public function processBest(array $data): int
    {
        return array_sum(array_column($data, 'value'));
    }

    public function demonstrateMemory(): void
    {
        $data = array_fill(0, 100000, ['value' => 1]);

        $bench = new Benchmark();

        $stats1 = $bench->measure(fn() => $this->processBad($data));
        echo "Bad (by value): {$stats1['memory']} KB\n";

        $stats2 = $bench->measure(fn() => $this->processGood($data));
        echo "Good (by reference): {$stats2['memory']} KB\n";

        $stats3 = $bench->measure(fn() => $this->processBest($data));
        echo "Best (array functions): {$stats3['memory']} KB\n";
    }
}
```

### Generators for Memory Efficiency

```php
<?php

class GeneratorExample
{
    // Bad: Loads entire range into memory
    public function rangeBad(int $start, int $end): array
    {
        $result = [];
        for ($i = $start; $i <= $end; $i++) {
            $result[] = $i;
        }
        return $result;
    }

    // Good: Yields values one at a time
    public function rangeGood(int $start, int $end): \Generator
    {
        for ($i = $start; $i <= $end; $i++) {
            yield $i;
        }
    }

    // Example: Large file processing
    public function readFileBad(string $filename): array
    {
        return file($filename);  // Loads entire file into memory
    }

    public function readFileGood(string $filename): \Generator
    {
        $handle = fopen($filename, 'r');
        while (($line = fgets($handle)) !== false) {
            yield $line;
        }
        fclose($handle);
    }

    public function demonstrateGenerators(): void
    {
        $bench = new Benchmark();

        // Bad: 100MB+ memory for 10 million numbers
        echo "Array approach:\n";
        $stats1 = $bench->measure(function() {
            $sum = 0;
            foreach ($this->rangeBad(1, 10000000) as $n) {
                $sum += $n;
            }
            return $sum;
        });
        echo "Memory: {$stats1['memory']} KB, Time: {$stats1['time']} ms\n";

        // Good: Constant memory
        echo "Generator approach:\n";
        $stats2 = $bench->measure(function() {
            $sum = 0;
            foreach ($this->rangeGood(1, 10000000) as $n) {
                $sum += $n;
            }
            return $sum;
        });
        echo "Memory: {$stats2['memory']} KB, Time: {$stats2['time']} ms\n";
    }
}
```

### Lazy Evaluation

```php
<?php

class LazyCollection
{
    private array $items;
    private array $operations = [];

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function map(callable $fn): self
    {
        $this->operations[] = ['map', $fn];
        return $this;
    }

    public function filter(callable $fn): self
    {
        $this->operations[] = ['filter', $fn];
        return $this;
    }

    // Only execute when needed
    public function toArray(): array
    {
        $result = $this->items;

        foreach ($this->operations as [$operation, $fn]) {
            if ($operation === 'map') {
                $result = array_map($fn, $result);
            } elseif ($operation === 'filter') {
                $result = array_filter($result, $fn);
            }
        }

        return $result;
    }

    // Take first N without processing all
    public function take(int $n): array
    {
        $result = [];
        $count = 0;

        foreach ($this->items as $item) {
            if ($count >= $n) break;

            // Apply operations
            $value = $item;
            $skip = false;

            foreach ($this->operations as [$operation, $fn]) {
                if ($operation === 'map') {
                    $value = $fn($value);
                } elseif ($operation === 'filter') {
                    if (!$fn($value)) {
                        $skip = true;
                        break;
                    }
                }
            }

            if (!$skip) {
                $result[] = $value;
                $count++;
            }
        }

        return $result;
    }
}

// Usage
$collection = new LazyCollection(range(1, 1000000));

// Operations not executed yet
$lazy = $collection
    ->map(fn($x) => $x * 2)
    ->filter(fn($x) => $x % 4 === 0)
    ->map(fn($x) => $x / 2);

// Only processes first 10 items
$result = $lazy->take(10);
print_r($result);
```

## PHP-Specific Optimizations

### Array Functions vs Loops

```php
<?php

class ArrayOptimizations
{
    public function compareMethods(array $data): void
    {
        $bench = new Benchmark();

        // Method 1: Foreach loop
        $stats1 = $bench->measure(function() use ($data) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $item * 2;
            }
            return $result;
        });

        // Method 2: array_map
        $stats2 = $bench->measure(function() use ($data) {
            return array_map(fn($x) => $x * 2, $data);
        });

        // Method 3: array_walk (modifies in place)
        $stats3 = $bench->measure(function() use ($data) {
            array_walk($data, function(&$x) { $x *= 2; });
            return $data;
        });

        echo "Foreach: {$stats1['time']}ms, {$stats1['memory']}KB\n";
        echo "array_map: {$stats2['time']}ms, {$stats2['memory']}KB\n";
        echo "array_walk: {$stats3['time']}ms, {$stats3['memory']}KB\n";
    }

    public function optimizedArrayOperations(): void
    {
        // Prefer array functions for readability and potential optimization
        $data = range(1, 10000);

        // Good: Clear intent
        $doubled = array_map(fn($x) => $x * 2, $data);
        $filtered = array_filter($data, fn($x) => $x % 2 === 0);
        $sum = array_sum($data);
        $product = array_product(array_slice($data, 0, 10));

        // Combine operations efficiently
        $result = array_sum(
            array_map(
                fn($x) => $x * 2,
                array_filter($data, fn($x) => $x % 2 === 0)
            )
        );
    }
}
```

### String Optimization

```php
<?php

class StringOptimizations
{
    // Bad: String concatenation in loop
    public function concatenateBad(array $strings): string
    {
        $result = '';
        foreach ($strings as $str) {
            $result .= $str;  // Creates new string each iteration
        }
        return $result;
    }

    // Good: Use implode
    public function concatenateGood(array $strings): string
    {
        return implode('', $strings);
    }

    // Good: Use array and implode
    public function buildStringGood(int $n): string
    {
        $parts = [];
        for ($i = 0; $i < $n; $i++) {
            $parts[] = "Item $i";
        }
        return implode("\n", $parts);
    }

    public function demonstrateStringOps(): void
    {
        $bench = new Benchmark();
        $strings = array_fill(0, 10000, 'test');

        $stats1 = $bench->measure(fn() => $this->concatenateBad($strings));
        $stats2 = $bench->measure(fn() => $this->concatenateGood($strings));

        echo "Bad (concat): {$stats1['time']}ms, {$stats1['memory']}KB\n";
        echo "Good (implode): {$stats2['time']}ms, {$stats2['memory']}KB\n";
    }

    // Use single quotes when possible
    public function quotesOptimization(): void
    {
        // Faster: No variable parsing
        $str1 = 'Hello World';

        // Slower: Variable parsing even if no variables
        $str2 = "Hello World";

        // Use double quotes only when needed
        $name = 'John';
        $str3 = "Hello $name";  // Necessary
    }
}
```

### OPcache Optimization

```php
<?php

class OPcacheOptimization
{
    public function configureOPcache(): array
    {
        // Recommended php.ini settings
        return [
            'opcache.enable' => '1',
            'opcache.memory_consumption' => '256',  // MB
            'opcache.interned_strings_buffer' => '16',  // MB
            'opcache.max_accelerated_files' => '10000',
            'opcache.validate_timestamps' => '0',  // Production: disable for max performance
            'opcache.revalidate_freq' => '0',
            'opcache.fast_shutdown' => '1',
            'opcache.enable_file_override' => '1',
            'opcache.preload' => '/path/to/preload.php',  // PHP 7.4+
        ];
    }

    public function checkOPcacheStatus(): array
    {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false];
        }

        $status = opcache_get_status();

        return [
            'enabled' => $status !== false,
            'full' => ($status['memory_usage']['used_memory'] / $status['memory_usage']['free_memory']) > 0.9,
            'hit_rate' => $status['opcache_statistics']['opcache_hit_rate'] ?? 0,
            'num_cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0
        ];
    }

    // Preload script for PHP 7.4+
    public function generatePreload(array $files): string
    {
        $script = "<?php\n";
        $script .= "// Preload frequently used classes\n";

        foreach ($files as $file) {
            $script .= "require_once '$file';\n";
        }

        return $script;
    }
}
```

## Algorithm-Specific Optimizations

### Early Termination

```php
<?php

class EarlyTermination
{
    // Bad: Always checks all elements
    public function containsBad(array $arr, $value): bool
    {
        $found = false;
        foreach ($arr as $item) {
            if ($item === $value) {
                $found = true;
            }
        }
        return $found;
    }

    // Good: Returns immediately when found
    public function containsGood(array $arr, $value): bool
    {
        foreach ($arr as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }

    // Best: Use built-in
    public function containsBest(array $arr, $value): bool
    {
        return in_array($value, $arr, true);
    }
}
```

### Loop Optimization

```php
<?php

class LoopOptimization
{
    // Bad: Recalculates count every iteration
    public function loopBad(array $arr): int
    {
        $sum = 0;
        for ($i = 0; $i < count($arr); $i++) {
            $sum += $arr[$i];
        }
        return $sum;
    }

    // Good: Calculate count once
    public function loopGood(array $arr): int
    {
        $sum = 0;
        $n = count($arr);
        for ($i = 0; $i < $n; $i++) {
            $sum += $arr[$i];
        }
        return $sum;
    }

    // Best: Use foreach (optimized by PHP)
    public function loopBest(array $arr): int
    {
        $sum = 0;
        foreach ($arr as $value) {
            $sum += $value;
        }
        return $sum;
    }

    // Best: Use array function
    public function loopOptimal(array $arr): int
    {
        return array_sum($arr);
    }
}
```

### Cache Locality

```php
<?php

class CacheLocality
{
    // Bad: Poor cache locality (column-major traversal of row-major array)
    public function traverseBad(array $matrix): int
    {
        $sum = 0;
        $cols = count($matrix[0]);
        $rows = count($matrix);

        for ($col = 0; $col < $cols; $col++) {
            for ($row = 0; $row < $rows; $row++) {
                $sum += $matrix[$row][$col];  // Jumping around in memory
            }
        }

        return $sum;
    }

    // Good: Good cache locality (row-major traversal)
    public function traverseGood(array $matrix): int
    {
        $sum = 0;

        foreach ($matrix as $row) {
            foreach ($row as $value) {
                $sum += $value;  // Sequential memory access
            }
        }

        return $sum;
    }

    public function demonstrateCacheLocality(): void
    {
        $size = 1000;
        $matrix = array_fill(0, $size, array_fill(0, $size, 1));

        $bench = new Benchmark();

        $stats1 = $bench->measure(fn() => $this->traverseBad($matrix));
        $stats2 = $bench->measure(fn() => $this->traverseGood($matrix));

        echo "Bad (column-major): {$stats1['time']}ms\n";
        echo "Good (row-major): {$stats2['time']}ms\n";
    }
}
```

## Database Query Optimization

```php
<?php

class DatabaseOptimization
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Bad: N+1 queries
    public function getUsersWithPostsBad(): array
    {
        $users = $this->pdo->query("SELECT * FROM users")->fetchAll();

        foreach ($users as &$user) {
            $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $user['posts'] = $stmt->fetchAll();
        }

        return $users;
    }

    // Good: Single query with JOIN
    public function getUsersWithPostsGood(): array
    {
        $sql = "
            SELECT u.*, p.id as post_id, p.title, p.content
            FROM users u
            LEFT JOIN posts p ON u.id = p.user_id
            ORDER BY u.id, p.id
        ";

        $results = $this->pdo->query($sql)->fetchAll();

        // Group by user
        $users = [];
        foreach ($results as $row) {
            $userId = $row['id'];

            if (!isset($users[$userId])) {
                $users[$userId] = [
                    'id' => $userId,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'posts' => []
                ];
            }

            if ($row['post_id']) {
                $users[$userId]['posts'][] = [
                    'id' => $row['post_id'],
                    'title' => $row['title'],
                    'content' => $row['content']
                ];
            }
        }

        return array_values($users);
    }

    // Batch operations
    public function insertBatch(array $users): void
    {
        // Bad: Individual inserts
        // foreach ($users as $user) {
        //     $stmt->execute([$user['name'], $user['email']]);
        // }

        // Good: Batch insert
        $placeholders = implode(',', array_fill(0, count($users), '(?,?)'));
        $sql = "INSERT INTO users (name, email) VALUES $placeholders";

        $params = [];
        foreach ($users as $user) {
            $params[] = $user['name'];
            $params[] = $user['email'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}
```

## Best Practices

1. **Measure First**
   - Profile to find bottlenecks
   - Don't optimize prematurely
   - Focus on hot paths

2. **Use Built-in Functions**
   - PHP's array functions are optimized in C
   - Trust the optimizer

3. **Minimize Memory Allocations**
   - Reuse objects/arrays when possible
   - Use generators for large datasets
   - Avoid unnecessary copies

4. **Database Optimization**
   - Use indexes appropriately
   - Avoid N+1 queries
   - Cache query results

5. **Enable OPcache**
   - Essential for production
   - Dramatically improves performance
   - Configure appropriately

## Key Takeaways

- Profile before optimizing - measure to find bottlenecks
- Use PHP's built-in array functions - they're optimized in C
- Generators save memory for large datasets
- Early termination saves unnecessary computations
- Cache locality affects performance significantly
- OPcache is essential for production performance
- String concatenation in loops is expensive
- Batch database operations when possible
- References can reduce memory copies but add complexity
- Lazy evaluation delays computation until needed

## Next Steps

In the final chapter, we'll explore real-world case studies demonstrating these algorithms and optimization techniques in practical PHP applications.
