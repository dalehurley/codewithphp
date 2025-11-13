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

## PHP 8+ Specific Optimizations

### JIT Compiler

```php
<?php

class JITOptimization
{
    // JIT is particularly effective for CPU-intensive operations
    public function benchmarkJIT(): array
    {
        $iterations = 1000000;

        // CPU-intensive: Matrix multiplication
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->complexCalculation(100);
        }
        $time = (microtime(true) - $start) * 1000;

        return [
            'operations' => $iterations,
            'time_ms' => $time,
            'ops_per_sec' => $iterations / ($time / 1000),
            'jit_enabled' => function_exists('opcache_get_status') && opcache_get_status()['jit']['enabled']
        ];
    }

    private function complexCalculation(int $n): float
    {
        $result = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $result += sqrt($i) * sin($i) * cos($i);
        }
        return $result;
    }

    public function configureJIT(): array
    {
        // php.ini recommended settings
        return [
            'opcache.enable' => '1',
            'opcache.jit_buffer_size' => '100M',
            'opcache.jit' => '1255',  // tracing JIT, all optimizations
            // Alternative modes:
            // '1205' => 'tracing JIT, minimal optimizations',
            // '1255' => 'tracing JIT, all optimizations (recommended)',
            // '1275' => 'tracing JIT, maximum optimizations',
        ];
    }
}

// Benchmark results (PHP 8.1+):
// Without JIT: ~2500ms (400,000 ops/sec)
// With JIT:    ~800ms  (1,250,000 ops/sec)
// Improvement: 3.1x faster

$jit = new JITOptimization();
$results = $jit->benchmarkJIT();
print_r($results);
```

### Named Arguments & Constructor Property Promotion

```php
<?php

// PHP 7.4 style (verbose)
class UserOld
{
    private int $id;
    private string $name;
    private string $email;

    public function __construct(int $id, string $name, string $email)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }
}

// PHP 8.0+ style (concise, equally performant)
class UserNew
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email
    ) {}
}

// Named arguments improve readability and performance
$user1 = new UserNew(id: 1, name: 'John', email: 'john@example.com');

// Can skip optional parameters
class Product
{
    public function __construct(
        private string $name,
        private float $price,
        private string $currency = 'USD',
        private bool $taxable = true
    ) {}
}

$product = new Product(
    name: 'Widget',
    price: 19.99,
    taxable: false  // Skip currency, use default
);
```

### Match Expression (Faster than switch)

```php
<?php

class MatchOptimization
{
    // Old way: switch (slower)
    public function getStatusLabelSwitch(string $status): string
    {
        switch ($status) {
            case 'draft':
                return 'Draft';
            case 'published':
                return 'Published';
            case 'archived':
                return 'Archived';
            default:
                return 'Unknown';
        }
    }

    // New way: match (faster, ~20% improvement)
    public function getStatusLabelMatch(string $status): string
    {
        return match($status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
            default => 'Unknown'
        };
    }

    // Match with complex conditions
    public function calculateShipping(int $weight, string $zone): float
    {
        return match(true) {
            $weight < 1 && $zone === 'domestic' => 5.00,
            $weight < 1 && $zone === 'international' => 15.00,
            $weight < 5 && $zone === 'domestic' => 10.00,
            $weight < 5 && $zone === 'international' => 30.00,
            default => throw new \InvalidArgumentException('Invalid shipping parameters')
        };
    }

    public function benchmark(): array
    {
        $iterations = 100000;
        $statuses = ['draft', 'published', 'archived', 'unknown'];

        // Switch
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->getStatusLabelSwitch($statuses[$i % 4]);
        }
        $switchTime = (microtime(true) - $start) * 1000;

        // Match
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->getStatusLabelMatch($statuses[$i % 4]);
        }
        $matchTime = (microtime(true) - $start) * 1000;

        return [
            'switch_ms' => $switchTime,
            'match_ms' => $matchTime,
            'improvement' => round(($switchTime - $matchTime) / $switchTime * 100, 2) . '%'
        ];
    }
}

$optimizer = new MatchOptimization();
print_r($optimizer->benchmark());
// Typical result: switch: 45ms, match: 36ms, improvement: 20%
```

### Union Types & Type Performance

```php
<?php

class TypeOptimization
{
    // Typed properties are faster (JIT optimization)
    private int $count = 0;
    private array $items = [];

    // Union types (PHP 8.0+)
    private int|float $price = 0;
    private User|Guest|null $user = null;

    // Mixed is slower than specific types
    public function processTyped(int $value): int
    {
        return $value * 2;  // JIT can optimize
    }

    public function processMixed(mixed $value): mixed
    {
        return $value * 2;  // JIT cannot optimize as effectively
    }

    public function benchmarkTypes(): array
    {
        $iterations = 1000000;

        // Typed
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->processTyped($i);
        }
        $typedTime = (microtime(true) - $start) * 1000;

        // Mixed
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->processMixed($i);
        }
        $mixedTime = (microtime(true) - $start) * 1000;

        return [
            'typed_ms' => $typedTime,
            'mixed_ms' => $mixedTime,
            'improvement' => round(($mixedTime - $typedTime) / $mixedTime * 100, 2) . '%'
        ];
    }
}

// Result: Typed is ~15-30% faster with JIT
```

### Attributes for Caching (PHP 8.0+)

```php
<?php

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cache
{
    public function __construct(
        public int $ttl = 3600,
        public ?string $key = null
    ) {}
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class RateLimit
{
    public function __construct(
        public int $maxAttempts = 60,
        public int $decayMinutes = 1
    ) {}
}

class AttributeBasedOptimization
{
    private array $cache = [];

    #[Cache(ttl: 300, key: 'expensive_calc')]
    #[RateLimit(maxAttempts: 10, decayMinutes: 1)]
    public function expensiveCalculation(int $n): int
    {
        sleep(1);  // Simulate expensive operation
        return $n * $n;
    }

    public function __call(string $method, array $args)
    {
        $reflection = new \ReflectionMethod($this, $method);
        $cacheAttrs = $reflection->getAttributes(Cache::class);

        if (!empty($cacheAttrs)) {
            $cacheAttr = $cacheAttrs[0]->newInstance();
            $key = $cacheAttr->key ?? $method . ':' . md5(serialize($args));

            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }

            $result = $this->$method(...$args);
            $this->cache[$key] = $result;

            return $result;
        }

        return $this->$method(...$args);
    }
}
```

### Fibers for Concurrency (PHP 8.1+)

```php
<?php

class FiberOptimization
{
    // Traditional blocking approach
    public function fetchDataBlocking(array $urls): array
    {
        $results = [];

        foreach ($urls as $url) {
            $results[$url] = $this->fetchUrl($url);
        }

        return $results;
    }

    // Fiber-based concurrent approach
    public function fetchDataConcurrent(array $urls): array
    {
        $fibers = [];
        $results = [];

        // Start all fibers
        foreach ($urls as $url) {
            $fibers[$url] = new \Fiber(function() use ($url) {
                return $this->fetchUrl($url);
            });
            $fibers[$url]->start();
        }

        // Collect results
        foreach ($fibers as $url => $fiber) {
            if ($fiber->isTerminated()) {
                $results[$url] = $fiber->getReturn();
            }
        }

        return $results;
    }

    private function fetchUrl(string $url): string
    {
        // Simulate network delay
        usleep(100000);  // 100ms
        return "Data from $url";
    }

    public function benchmark(): array
    {
        $urls = array_fill(0, 10, 'https://example.com/api/data');

        $start = microtime(true);
        $this->fetchDataBlocking($urls);
        $blockingTime = (microtime(true) - $start) * 1000;

        $start = microtime(true);
        $this->fetchDataConcurrent($urls);
        $fiberTime = (microtime(true) - $start) * 1000;

        return [
            'blocking_ms' => $blockingTime,
            'fiber_ms' => $fiberTime,
            'improvement' => round(($blockingTime - $fiberTime) / $blockingTime * 100, 2) . '%'
        ];
    }
}

// Result: ~90% improvement for I/O-bound operations
```

## Professional Profiling Tools

### Blackfire.io Integration

```php
<?php

class BlackfireProfiler
{
    private \Blackfire\Client $client;

    public function __construct()
    {
        if (extension_loaded('blackfire')) {
            $this->client = new \Blackfire\Client();
        }
    }

    public function profileFunction(callable $fn, string $profileName): array
    {
        $probe = $this->client->createProbe();

        $start = microtime(true);
        $result = $fn();
        $time = microtime(true) - $start;

        $this->client->endProbe($probe);

        return [
            'result' => $result,
            'time_ms' => $time * 1000,
            'profile_url' => $probe->getUrl()
        ];
    }

    // Example: Profile algorithm comparison
    public function compareAlgorithms(): array
    {
        $data = range(1, 10000);
        shuffle($data);

        $results = [];

        // Profile bubble sort
        $results['bubble_sort'] = $this->profileFunction(
            fn() => $this->bubbleSort($data),
            'Bubble Sort - 10k elements'
        );

        // Profile quick sort
        $results['quick_sort'] = $this->profileFunction(
            fn() => $this->quickSort($data),
            'Quick Sort - 10k elements'
        );

        return $results;
    }

    private function bubbleSort(array $arr): array
    {
        $n = count($arr);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($arr[$j] > $arr[$j + 1]) {
                    [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                }
            }
        }
        return $arr;
    }

    private function quickSort(array $arr): array
    {
        if (count($arr) < 2) return $arr;
        $pivot = $arr[0];
        $left = array_filter(array_slice($arr, 1), fn($x) => $x <= $pivot);
        $right = array_filter(array_slice($arr, 1), fn($x) => $x > $pivot);
        return array_merge($this->quickSort($left), [$pivot], $this->quickSort($right));
    }
}

// Usage
$profiler = new BlackfireProfiler();
$comparison = $profiler->compareAlgorithms();

/*
Results:
- Bubble Sort: 850ms, CPU: 95%, Memory: 2.5MB, Profile: https://blackfire.io/profiles/...
- Quick Sort: 45ms, CPU: 80%, Memory: 3.2MB, Profile: https://blackfire.io/profiles/...
*/
```

### Xhprof/Tideways Profiling

```php
<?php

class XhprofProfiler
{
    private bool $enabled = false;

    public function start(): void
    {
        if (extension_loaded('tideways_xhprof')) {
            tideways_xhprof_enable(TIDEWAYS_XHPROF_FLAGS_CPU | TIDEWAYS_XHPROF_FLAGS_MEMORY);
            $this->enabled = true;
        } elseif (extension_loaded('xhprof')) {
            xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
            $this->enabled = true;
        }
    }

    public function stop(): array
    {
        if (!$this->enabled) {
            return [];
        }

        if (extension_loaded('tideways_xhprof')) {
            $data = tideways_xhprof_disable();
        } elseif (extension_loaded('xhprof')) {
            $data = xhprof_disable();
        } else {
            return [];
        }

        return $this->analyzeProfile($data);
    }

    private function analyzeProfile(array $data): array
    {
        // Find top 10 most time-consuming functions
        $functions = [];

        foreach ($data as $key => $stats) {
            [$caller, $callee] = explode('==>', $key . '==>main()');

            if (!isset($functions[$callee])) {
                $functions[$callee] = [
                    'calls' => 0,
                    'wall_time' => 0,
                    'cpu' => 0,
                    'memory' => 0
                ];
            }

            $functions[$callee]['calls'] += $stats['ct'] ?? 0;
            $functions[$callee]['wall_time'] += $stats['wt'] ?? 0;
            $functions[$callee]['cpu'] += $stats['cpu'] ?? 0;
            $functions[$callee]['memory'] += $stats['mu'] ?? 0;
        }

        // Sort by wall time
        uasort($functions, fn($a, $b) => $b['wall_time'] <=> $a['wall_time']);

        return [
            'functions' => array_slice($functions, 0, 10, true),
            'total_time' => array_sum(array_column($functions, 'wall_time')),
            'total_memory' => array_sum(array_column($functions, 'memory'))
        ];
    }

    public function profileRequest(callable $handler): array
    {
        $this->start();
        $result = $handler();
        $profile = $this->stop();

        return [
            'result' => $result,
            'profile' => $profile
        ];
    }
}

// Usage
$profiler = new XhprofProfiler();

$result = $profiler->profileRequest(function() {
    // Your application code
    $users = User::where('active', 1)->get();
    return $users->count();
});

print_r($result['profile']);
/*
Array
(
    [functions] => Array
        (
            [PDO::query] => Array
                (
                    [calls] => 5
                    [wall_time] => 45000  // microseconds
                    [cpu] => 42000
                    [memory] => 1024000  // bytes
                )
            [json_decode] => Array
                (
                    [calls] => 20
                    [wall_time] => 8000
                    [cpu] => 7500
                    [memory] => 102400
                )
        )
)
*/
```

### New Relic APM Integration

```php
<?php

class NewRelicMonitoring
{
    public function __construct(
        private string $appName,
        private string $licenseKey
    ) {
        if (extension_loaded('newrelic')) {
            newrelic_set_appname($this->appName);
        }
    }

    public function trackTransaction(string $name, callable $callback): mixed
    {
        if (extension_loaded('newrelic')) {
            newrelic_name_transaction($name);
        }

        $start = microtime(true);

        try {
            $result = $callback();

            if (extension_loaded('newrelic')) {
                newrelic_custom_metric('Custom/TransactionTime', (microtime(true) - $start) * 1000);
            }

            return $result;
        } catch (\Exception $e) {
            if (extension_loaded('newrelic')) {
                newrelic_notice_error($e->getMessage(), $e);
            }
            throw $e;
        }
    }

    public function addCustomMetrics(array $metrics): void
    {
        if (!extension_loaded('newrelic')) {
            return;
        }

        foreach ($metrics as $name => $value) {
            newrelic_custom_metric("Custom/$name", $value);
        }
    }

    // Track algorithm performance
    public function trackAlgorithmPerformance(string $algorithm, callable $fn, array $input): array
    {
        $inputSize = is_array($input) ? count($input) : strlen($input);

        return $this->trackTransaction("Algorithm/$algorithm", function() use ($fn, $input, $algorithm, $inputSize) {
            $start = microtime(true);
            $memStart = memory_get_usage();

            $result = $fn($input);

            $time = (microtime(true) - $start) * 1000;
            $memory = (memory_get_usage() - $memStart) / 1024;

            $this->addCustomMetrics([
                "Algorithm/{$algorithm}/Time" => $time,
                "Algorithm/{$algorithm}/Memory" => $memory,
                "Algorithm/{$algorithm}/InputSize" => $inputSize
            ]);

            return [
                'result' => $result,
                'time_ms' => $time,
                'memory_kb' => $memory,
                'input_size' => $inputSize
            ];
        });
    }
}

// Usage
$monitor = new NewRelicMonitoring('MyApp', 'license_key_here');

$data = range(1, 10000);

$result = $monitor->trackAlgorithmPerformance('QuickSort', function($data) {
    sort($data);
    return $data;
}, $data);

print_r($result);
// Results sent to New Relic dashboard with custom metrics
```

## Complete Optimization Workflow

```php
<?php

class OptimizationWorkflow
{
    private array $benchmarks = [];

    // Step 1: Identify bottleneck
    public function identifyBottleneck(callable $fn): array
    {
        $profiler = new XhprofProfiler();

        $profiler->start();
        $fn();
        $profile = $profiler->stop();

        // Find slowest function
        $slowest = array_key_first($profile['functions']);

        return [
            'bottleneck' => $slowest,
            'time_ms' => $profile['functions'][$slowest]['wall_time'] / 1000,
            'percentage' => ($profile['functions'][$slowest]['wall_time'] / $profile['total_time']) * 100
        ];
    }

    // Step 2: Benchmark before optimization
    public function benchmarkBefore(string $name, callable $fn): void
    {
        $iterations = 1000;

        $times = [];
        $memories = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $memStart = memory_get_usage();

            $fn();

            $times[] = (microtime(true) - $start) * 1000;
            $memories[] = (memory_get_usage() - $memStart) / 1024;
        }

        $this->benchmarks[$name] = [
            'before' => [
                'avg_time_ms' => array_sum($times) / count($times),
                'min_time_ms' => min($times),
                'max_time_ms' => max($times),
                'avg_memory_kb' => array_sum($memories) / count($memories),
                'iterations' => $iterations
            ]
        ];
    }

    // Step 3: Benchmark after optimization
    public function benchmarkAfter(string $name, callable $fn): array
    {
        $iterations = 1000;

        $times = [];
        $memories = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $memStart = memory_get_usage();

            $fn();

            $times[] = (microtime(true) - $start) * 1000;
            $memories[] = (memory_get_usage() - $memStart) / 1024;
        }

        $after = [
            'avg_time_ms' => array_sum($times) / count($times),
            'min_time_ms' => min($times),
            'max_time_ms' => max($times),
            'avg_memory_kb' => array_sum($memories) / count($memories),
            'iterations' => $iterations
        ];

        $this->benchmarks[$name]['after'] = $after;

        // Calculate improvement
        $before = $this->benchmarks[$name]['before'];

        return [
            'before' => $before,
            'after' => $after,
            'time_improvement' => round((($before['avg_time_ms'] - $after['avg_time_ms']) / $before['avg_time_ms']) * 100, 2) . '%',
            'memory_improvement' => round((($before['avg_memory_kb'] - $after['avg_memory_kb']) / $before['avg_memory_kb']) * 100, 2) . '%',
            'speedup' => round($before['avg_time_ms'] / $after['avg_time_ms'], 2) . 'x'
        ];
    }

    // Generate optimization report
    public function generateReport(): string
    {
        $report = "# Optimization Report\n\n";

        foreach ($this->benchmarks as $name => $data) {
            if (!isset($data['after'])) continue;

            $before = $data['before'];
            $after = $data['after'];

            $timeImprovement = (($before['avg_time_ms'] - $after['avg_time_ms']) / $before['avg_time_ms']) * 100;
            $memoryImprovement = (($before['avg_memory_kb'] - $after['avg_memory_kb']) / $before['avg_memory_kb']) * 100;

            $report .= "## $name\n\n";
            $report .= "| Metric | Before | After | Improvement |\n";
            $report .= "|--------|--------|-------|-------------|\n";
            $report .= sprintf("| Avg Time | %.2fms | %.2fms | %.1f%% |\n",
                $before['avg_time_ms'], $after['avg_time_ms'], $timeImprovement);
            $report .= sprintf("| Memory | %.2fKB | %.2fKB | %.1f%% |\n",
                $before['avg_memory_kb'], $after['avg_memory_kb'], $memoryImprovement);
            $report .= sprintf("| Speedup | 1.0x | %.2fx | - |\n\n",
                $before['avg_time_ms'] / $after['avg_time_ms']);
        }

        return $report;
    }
}

// Example Usage
$workflow = new OptimizationWorkflow();

// Benchmark unoptimized version
$workflow->benchmarkBefore('User Query', function() {
    // Unoptimized: N+1 query problem
    $users = getAllUsers();
    foreach ($users as $user) {
        $user['posts'] = getPostsByUserId($user['id']);
    }
});

// Benchmark optimized version
$result = $workflow->benchmarkAfter('User Query', function() {
    // Optimized: Single query with JOIN
    $users = getAllUsersWithPosts();
});

print_r($result);
/*
Array
(
    [time_improvement] => 87.5%
    [memory_improvement] => 45.2%
    [speedup] => 8.0x
)
*/

echo $workflow->generateReport();
```

## Best Practices

1. **Measure First**
   - Profile to find bottlenecks
   - Don't optimize prematurely
   - Focus on hot paths (80/20 rule)
   - Use professional tools (Blackfire, New Relic, Tideways)

2. **Use Built-in Functions**
   - PHP's array functions are optimized in C
   - Trust the optimizer
   - Prefer built-ins over custom implementations

3. **Minimize Memory Allocations**
   - Reuse objects/arrays when possible
   - Use generators for large datasets
   - Avoid unnecessary copies

4. **Database Optimization**
   - Use indexes appropriately
   - Avoid N+1 queries
   - Cache query results
   - Batch operations

5. **Enable OPcache & JIT**
   - Essential for production
   - OPcache: 2-3x improvement
   - JIT: Additional 1.5-3x for CPU-intensive code
   - Configure appropriately

6. **PHP 8+ Features**
   - Use typed properties (JIT optimization)
   - Match expressions (20% faster than switch)
   - Constructor property promotion
   - Fibers for concurrent I/O
   - Attributes for meta-programming

7. **Continuous Monitoring**
   - Track performance metrics
   - Set up alerts for regressions
   - Regular profiling in production
   - A/B test optimizations

## Performance Checklist

- [ ] OPcache enabled and configured
- [ ] JIT enabled for CPU-intensive workloads
- [ ] Database indexes on frequently queried columns
- [ ] Query result caching (Redis/Memcached)
- [ ] Multi-level caching strategy
- [ ] Generators for large datasets
- [ ] Batch database operations
- [ ] Typed properties and return types
- [ ] Match expressions instead of switch
- [ ] Profiling enabled in staging
- [ ] Performance monitoring (APM)
- [ ] Cache stampede prevention
- [ ] CDN for static assets
- [ ] HTTP/2 or HTTP/3
- [ ] Compression enabled (gzip/brotli)

## Key Takeaways

- Profile before optimizing - measure to find bottlenecks
- Use PHP's built-in array functions - they're optimized in C
- OPcache provides 2-3x performance boost (always enable)
- JIT adds 1.5-3x improvement for CPU-intensive code (PHP 8+)
- Generators save memory for large datasets
- Early termination saves unnecessary computations
- Cache locality affects performance significantly
- String concatenation in loops is expensive
- Batch database operations when possible
- References can reduce memory copies but add complexity
- Lazy evaluation delays computation until needed
- PHP 8+ typed properties enable JIT optimizations
- Match expressions are 20% faster than switch
- Fibers improve I/O-bound concurrent operations
- Professional profiling tools (Blackfire, Tideways, New Relic) are essential
- Continuous monitoring prevents performance regressions
- 80/20 rule: Focus on optimizing the 20% of code causing 80% of issues

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 29 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-29)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-29
php 01-*.php
```

## Next Steps

In the final chapter, we'll explore real-world case studies demonstrating these algorithms and optimization techniques in practical PHP applications with before/after metrics.
