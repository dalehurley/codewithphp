---
title: "29: Performance Optimization"
description: "Master performance optimization techniques including profiling, benchmarking, memory optimization, and PHP-specific optimizations for algorithms"
series: "php-algorithms"
chapter: 29
order: 29
difficulty: "Advanced"
prerequisites:
  - "/series/php-algorithms/chapters/28-algorithm-selection-guide"
---

![Performance Optimization](/images/php-algorithms/chapter-29-performance-optimization-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 29</span>
</div>

# 29: Performance Optimization <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Performance optimization transforms slow, resource-hungry code into lean, efficient applications that scale. This chapter reveals the profiling and optimization techniques that top PHP developers use to build lightning-fast applications at scale, achieving 10x or even 100x performance improvements when applied correctly.

Unlike premature optimization, effective performance tuning starts with measurement. You'll learn to use profiling tools to identify bottlenecks scientifically, apply benchmarking methodologies to measure improvements objectively, and leverage PHP-specific optimizations including OPcache, JIT compilation, and memory management techniques.

In this chapter, you'll master profiling with Xdebug, Blackfire, and Xhprof; optimize memory usage with generators and lazy evaluation; apply PHP 8+ specific optimizations; and implement production-grade performance monitoring workflows. You'll learn to optimize algorithms for both speed and memory efficiency simultaneously while maintaining code correctness and readability.

## Prerequisites

Before starting this chapter, you should have:

- ✓ Algorithm selection skills - Understanding when to use which algorithms ([Chapter 28](/series/php-algorithms/chapters/28-algorithm-selection-guide))
- ✓ Benchmarking basics - Experience measuring code performance
- ✓ PHP internals awareness - Familiarity with how PHP executes code
- ✓ Production experience - Understanding of real-world performance requirements

**Estimated Time**: ~55 minutes

## What You'll Build

By the end of this chapter, you will have created:

- A comprehensive benchmarking class for measuring code performance
- Memory optimization examples using references, generators, and lazy evaluation
- PHP-specific optimization implementations (OPcache, JIT, array functions)
- Algorithm-specific optimizations (early termination, loop optimization, cache locality)
- Database query optimization examples (avoiding N+1 queries, batch operations)
- Application-level caching strategies (Redis, cache invalidation, stampede prevention)
- Multi-level caching architecture (L1/L2/L3 cache layers)
- Memoization patterns for function result caching
- HTTP caching header implementations (ETags, Cache-Control)
- Professional profiling integrations (Blackfire, Xhprof, New Relic)
- A complete optimization workflow with before/after benchmarking

## Objectives

- Master profiling tools to identify bottlenecks scientifically, not by guessing
- Apply benchmarking methodologies to measure improvements objectively
- Leverage PHP-specific optimizations including OPcache, JIT, and memory management
- Optimize algorithms for both speed and memory efficiency simultaneously
- Implement application-level caching strategies with Redis/Memcached
- Prevent cache stampedes and implement proper cache invalidation
- Design multi-level caching architectures for optimal performance
- Use memoization and HTTP caching to reduce server load
- Implement production-grade performance monitoring and optimization workflows

## Profiling and Measurement

Before optimizing, you must measure. Profiling tools reveal where your code spends time and memory, allowing you to focus optimization efforts where they'll have the greatest impact.

### Basic Benchmarking

```php
# filename: benchmark.php
<?php

declare(strict_types=1);

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

Xdebug provides detailed profiling information that can be analyzed with tools like KCacheGrind or QCacheGrind. This is essential for identifying function-level bottlenecks.

```php
# filename: xdebug-profiler.php
<?php

declare(strict_types=1);

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

Memory optimization is crucial for applications processing large datasets. PHP's copy-on-write mechanism helps, but understanding when to use references and when to avoid them can significantly reduce memory usage.

### Reference vs Value

```php
# filename: memory-optimization.php
<?php

declare(strict_types=1);

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

Generators allow you to process large datasets without loading everything into memory at once. They're essential for file processing, large database result sets, and any scenario where memory is constrained.

```php
# filename: generator-example.php
<?php

declare(strict_types=1);

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

Lazy evaluation delays computation until results are actually needed. This pattern is powerful for building efficient data processing pipelines where you might only need a subset of results.

```php
# filename: lazy-collection.php
<?php

declare(strict_types=1);

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

PHP's built-in array functions are implemented in C and often outperform custom PHP loops. Understanding when to use these functions can provide significant performance improvements.

### Array Functions vs Loops

```php
# filename: array-optimizations.php
<?php

declare(strict_types=1);

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

String operations can be expensive, especially concatenation in loops. PHP's string handling has improved significantly, but certain patterns still cause unnecessary overhead.

```php
# filename: string-optimizations.php
<?php

declare(strict_types=1);

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

OPcache is PHP's opcode cache, storing precompiled script bytecode in memory. It's essential for production environments and provides 2-3x performance improvements.

```php
# filename: opcache-optimization.php
<?php

declare(strict_types=1);

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

### OPcache Preloading Deep Dive

OPcache preloading (PHP 7.4+) loads classes into shared memory at server startup, eliminating the need to compile them on each request. This provides 5-15% performance improvements for applications with many classes.

**How It Works:**

1. PHP reads a preload script at server startup
2. All classes/files required in that script are compiled and cached
3. Classes remain in shared memory across all requests
4. No compilation overhead on first request

**What to Preload:**

- Framework core classes (Laravel, Symfony, etc.)
- Frequently used vendor libraries
- Your application's base classes and interfaces
- Classes used in every request

**What NOT to Preload:**

- Classes that change frequently (defeats the purpose)
- Classes with side effects in file scope
- Classes that are rarely used

```php
# filename: opcache-preload.php
<?php

// Example: Laravel preload script
// Save as: preload.php

// Framework core
opcache_compile_file(__DIR__ . '/vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php');
opcache_compile_file(__DIR__ . '/vendor/laravel/framework/src/Illuminate/Container/Container.php');
opcache_compile_file(__DIR__ . '/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php');

// Common vendor classes
opcache_compile_file(__DIR__ . '/vendor/monolog/monolog/src/Monolog/Logger.php');
opcache_compile_file(__DIR__ . '/vendor/symfony/http-foundation/Request.php');

// Your application base classes
opcache_compile_file(__DIR__ . '/app/Models/BaseModel.php');
opcache_compile_file(__DIR__ . '/app/Http/Controllers/Controller.php');

// Or use a directory scanner
function preloadDirectory(string $directory): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            opcache_compile_file($file->getPathname());
        }
    }
}

// Preload entire directories
preloadDirectory(__DIR__ . '/vendor/laravel/framework/src/Illuminate');
preloadDirectory(__DIR__ . '/app/Models');
```

**Configuration:**

```ini
; php.ini
opcache.enable=1
opcache.preload=/path/to/preload.php
opcache.preload_user=www-data  ; User running PHP-FPM
```

**Performance Measurement:**

```php
# filename: measure-preload-impact.php
<?php

class PreloadBenchmark
{
    public function measurePreloadImpact(): array
    {
        $iterations = 1000;

        // Without preload: First request compiles classes
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            new \Illuminate\Support\Collection([]);
        }
        $withoutPreload = (microtime(true) - $start) * 1000;

        // With preload: Classes already compiled
        // (This would be measured on a fresh request after preload)
        // Typical improvement: 5-15% faster first request

        return [
            'without_preload_ms' => $withoutPreload,
            'estimated_with_preload_ms' => $withoutPreload * 0.90,  // ~10% improvement
            'improvement' => '10-15%'
        ];
    }

    public function checkPreloadStatus(): array
    {
        if (!function_exists('opcache_get_status')) {
            return ['preload_available' => false];
        }

        $status = opcache_get_status();

        return [
            'preload_available' => PHP_VERSION_ID >= 70400,
            'preload_enabled' => ini_get('opcache.preload') !== '',
            'preload_script' => ini_get('opcache.preload'),
            'preloaded_scripts' => $status['preload_statistics']['scripts'] ?? 0,
            'preloaded_functions' => $status['preload_statistics']['functions'] ?? 0,
            'preloaded_classes' => $status['preload_statistics']['classes'] ?? 0
        ];
    }
}
```

**Common Pitfalls:**

1. **Memory Limits**: Preloading uses shared memory. Monitor `opcache.memory_consumption`
2. **Circular Dependencies**: Ensure all dependencies are loaded in correct order
3. **File Changes**: Preloaded files won't update until PHP-FPM restart (use `opcache.validate_timestamps=1` in development)
4. **User Permissions**: Preload script must be readable by the PHP process user

### APCu for Single-Server Caching

APCu (Alternative PHP Cache User Cache) provides user-space caching for single-server applications. Unlike Redis/Memcached, APCu stores data in PHP's shared memory, making it faster but limited to a single server.

**When to Use APCu:**

- Single-server applications
- Need faster access than Redis (no network overhead)
- Can tolerate cache loss on server restart
- Want simple, no-dependency caching

**When NOT to Use APCu:**

- Multi-server deployments (use Redis/Memcached)
- Need persistence across restarts
- Require distributed cache invalidation

```php
# filename: apcu-cache.php
<?php

class APCUCache
{
    public function get(string $key): mixed
    {
        $value = apcu_fetch($key, $success);
        return $success ? $value : null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return apcu_store($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return apcu_delete($key);
    }

    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    public function exists(string $key): bool
    {
        return apcu_exists($key);
    }

    // Batch operations
    public function getMultiple(array $keys): array
    {
        return apcu_fetch($keys) ?: [];
    }

    public function setMultiple(array $values, int $ttl = 3600): array
    {
        $failed = [];
        foreach ($values as $key => $value) {
            if (!apcu_store($key, $value, $ttl)) {
                $failed[] = $key;
            }
        }
        return $failed;
    }

    // Cache with callback (like Redis remember)
    public function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    // Get statistics
    public function getStats(): array
    {
        $info = apcu_cache_info(true);
        $sma = apcu_sma_info();

        return [
            'hits' => $info['num_hits'],
            'misses' => $info['num_misses'],
            'hit_rate' => $info['num_hits'] / ($info['num_hits'] + $info['num_misses']) * 100,
            'entries' => $info['num_entries'],
            'memory_used' => $sma['seg_size'] - $sma['avail_mem'],
            'memory_available' => $sma['avail_mem'],
            'memory_total' => $sma['seg_size']
        ];
    }
}

// Usage
$cache = new APCUCache();

// Simple get/set
$cache->set('user:123', ['name' => 'John', 'email' => 'john@example.com'], 3600);
$user = $cache->get('user:123');

// Remember pattern
$user = $cache->remember('user:123', function() {
    // Expensive operation
    return fetchUserFromDatabase(123);
}, 3600);

// Check stats
$stats = $cache->getStats();
echo "Hit rate: {$stats['hit_rate']}%\n";
```

**APCu vs Redis Comparison:**

```php
# filename: apcu-vs-redis-benchmark.php
<?php

class CacheComparison
{
    public function benchmark(): array
    {
        $iterations = 10000;
        $key = 'test:key';
        $value = ['data' => str_repeat('x', 1000)];

        // APCu
        apcu_store($key, $value);
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            apcu_fetch($key);
        }
        $apcuTime = (microtime(true) - $start) * 1000;

        // Redis (local)
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->set($key, serialize($value));
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $redis->get($key);
        }
        $redisTime = (microtime(true) - $start) * 1000;

        return [
            'apcu_ms' => $apcuTime,
            'redis_ms' => $redisTime,
            'apcu_faster_by' => round(($redisTime / $apcuTime), 2) . 'x',
            'note' => 'APCu is faster for single-server, Redis needed for multi-server'
        ];
    }
}

// Typical results:
// APCu: ~50ms for 10k operations
// Redis: ~200ms for 10k operations
// APCu is ~4x faster for local operations
```

## Algorithm-Specific Optimizations

Beyond PHP-specific optimizations, algorithm-level improvements can provide dramatic performance gains. These techniques apply regardless of language.

### Early Termination

```php
# filename: early-termination.php
<?php

declare(strict_types=1);

class EarlyTermination
{
    // Bad: Always checks all elements
    public function containsBad(array $arr, mixed $value): bool
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
    public function containsGood(array $arr, mixed $value): bool
    {
        foreach ($arr as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }

    // Best: Use built-in
    public function containsBest(array $arr, mixed $value): bool
    {
        return in_array($value, $arr, true);
    }
}
```

### Loop Optimization

Loop optimizations reduce redundant calculations and leverage PHP's internal optimizations. Small changes can yield significant improvements in tight loops.

```php
# filename: loop-optimization.php
<?php

declare(strict_types=1);

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

Cache locality refers to accessing data in memory sequentially rather than jumping around. Modern CPUs have multiple cache levels, and sequential access patterns are much faster.

```php
# filename: cache-locality.php
<?php

declare(strict_types=1);

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

Database queries are often the biggest performance bottleneck in web applications. N+1 query problems and inefficient joins can slow applications to a crawl. Connection pooling and query optimization can dramatically improve database performance.

```php
# filename: database-optimization.php
<?php

declare(strict_types=1);

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

    // Connection pooling pattern
    public function getConnectionPool(int $maxConnections = 10): \PDO
    {
        static $pool = [];
        static $inUse = [];

        // Find available connection
        foreach ($pool as $key => $conn) {
            if (!isset($inUse[$key])) {
                $inUse[$key] = true;
                return $conn;
            }
        }

        // Create new connection if pool not full
        if (count($pool) < $maxConnections) {
            $conn = new \PDO(
                'mysql:host=localhost;dbname=test',
                'user',
                'password',
                [
                    \PDO::ATTR_PERSISTENT => true,  // Persistent connections
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );
            $key = count($pool);
            $pool[$key] = $conn;
            $inUse[$key] = true;
            return $conn;
        }

        // Wait for available connection (or throw exception)
        throw new \RuntimeException('Connection pool exhausted');
    }

    public function releaseConnection(\PDO $conn): void
    {
        foreach ($this->pool as $key => $pooledConn) {
            if ($pooledConn === $conn) {
                unset($this->inUse[$key]);
                return;
            }
        }
    }
}
```

### Connection Pooling

Connection pooling reuses database connections instead of creating new ones for each request, reducing connection overhead significantly.

**Benefits:**

- Reduces connection establishment time (TCP handshake, authentication)
- Limits total connections to database server
- Improves performance for high-traffic applications

**PHP-FPM Persistent Connections:**

```php
# filename: connection-pooling.php
<?php

class ConnectionPool
{
    private static array $pools = [];
    private static int $maxConnections = 10;

    public static function getConnection(string $dsn, string $user, string $password): \PDO
    {
        $key = md5($dsn . $user);

        if (!isset(self::$pools[$key])) {
            self::$pools[$key] = [];
        }

        // Find available connection
        foreach (self::$pools[$key] as $conn) {
            try {
                // Test if connection is still alive
                $conn->query('SELECT 1');
                return $conn;
            } catch (\PDOException $e) {
                // Connection dead, remove from pool
                self::$pools[$key] = array_filter(
                    self::$pools[$key],
                    fn($c) => $c !== $conn
                );
            }
        }

        // Create new connection if pool not full
        if (count(self::$pools[$key]) < self::$maxConnections) {
            $conn = new \PDO($dsn, $user, $password, [
                \PDO::ATTR_PERSISTENT => true,  // Persistent connection
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
            self::$pools[$key][] = $conn;
            return $conn;
        }

        // Pool exhausted - wait or create temporary connection
        return new \PDO($dsn, $user, $password);
    }

    public static function getPoolStats(): array
    {
        $stats = [];
        foreach (self::$pools as $key => $pool) {
            $stats[$key] = [
                'connections' => count($pool),
                'max' => self::$maxConnections
            ];
        }
        return $stats;
    }
}

// Usage
$pdo = ConnectionPool::getConnection(
    'mysql:host=localhost;dbname=app',
    'user',
    'password'
);

// Connection is reused from pool
$stmt = $pdo->query('SELECT * FROM users LIMIT 10');
```

**Performance Impact:**

```php
# filename: connection-pooling-benchmark.php
<?php

function benchmarkConnections(): array
{
    $iterations = 100;
    $dsn = 'mysql:host=localhost;dbname=test';
    $user = 'user';
    $password = 'password';

    // Without pooling: New connection each time
    $start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $pdo = new \PDO($dsn, $user, $password);
        $pdo->query('SELECT 1');
    }
    $withoutPool = (microtime(true) - $start) * 1000;

    // With pooling: Reuse connections
    $start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $pdo = ConnectionPool::getConnection($dsn, $user, $password);
        $pdo->query('SELECT 1');
    }
    $withPool = (microtime(true) - $start) * 1000;

    return [
        'without_pool_ms' => $withoutPool,
        'with_pool_ms' => $withPool,
        'improvement' => round(($withoutPool / $withPool), 2) . 'x faster',
        'time_saved_ms' => $withoutPool - $withPool
    ];
}

// Typical results:
// Without pool: ~5000ms (50ms per connection)
// With pool: ~500ms (5ms per query, connection reused)
// Improvement: ~10x faster
```

## PHP 8+ Specific Optimizations

PHP 8+ introduced significant performance improvements including JIT compilation, match expressions, typed properties, and fibers. Understanding these features helps you write faster code.

### JIT Compiler

```php
# filename: jit-optimization.php
<?php

declare(strict_types=1);

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
# filename: php8-features.php
<?php

declare(strict_types=1);

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
# filename: match-expression.php
<?php

declare(strict_types=1);

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
# filename: type-optimization.php
<?php

declare(strict_types=1);

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
# filename: attribute-caching.php
<?php

declare(strict_types=1);

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

    public function __call(string $method, array $args): mixed
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
# filename: fiber-concurrency.php
<?php

declare(strict_types=1);

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

While basic benchmarking helps, professional profiling tools provide deeper insights into CPU usage, memory allocation, and I/O operations. These tools are essential for production optimization.

### Blackfire.io Integration

```php
# filename: blackfire-profiler.php
<?php

declare(strict_types=1);

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
# filename: xhprof-profiler.php
<?php

declare(strict_types=1);

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
# filename: newrelic-monitoring.php
<?php

declare(strict_types=1);

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

A systematic approach to optimization ensures you measure improvements and avoid regressions. This workflow combines profiling, benchmarking, and reporting.

```php
# filename: optimization-workflow.php
<?php

declare(strict_types=1);

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

## Troubleshooting

### Error: "Xdebug extension not loaded"

**Symptom**: `RuntimeException: Xdebug extension not loaded` when trying to use Xdebug profiling

**Cause**: Xdebug extension is not installed or not enabled in php.ini

**Solution**: Install and enable Xdebug:

```bash
# Install Xdebug (example for Ubuntu/Debian)
sudo apt-get install php-xdebug

# Or via PECL
pecl install xdebug

# Enable in php.ini
echo "zend_extension=xdebug.so" >> /etc/php/8.4/cli/php.ini
echo "xdebug.mode=profile" >> /etc/php/8.4/cli/php.ini
```

### Problem: OPcache Not Working

**Symptom**: No performance improvement after enabling OPcache

**Cause**: OPcache might be disabled or misconfigured

**Solution**: Verify OPcache status:

```php
# filename: check-opcache.php
<?php

declare(strict_types=1);

if (!function_exists('opcache_get_status')) {
    echo "OPcache extension not loaded\n";
    exit(1);
}

$status = opcache_get_status();
if ($status === false) {
    echo "OPcache is disabled\n";
} else {
    echo "OPcache is enabled\n";
    echo "Hit rate: " . $status['opcache_statistics']['opcache_hit_rate'] . "%\n";
}
```

### Error: "JIT not enabled" or No Performance Improvement

**Symptom**: JIT appears enabled but no performance improvement

**Cause**: JIT only helps CPU-intensive code, not I/O-bound operations

**Solution**:

- Verify JIT is actually enabled: `opcache_get_status()['jit']['enabled']`
- Ensure you're testing CPU-intensive code (loops, calculations)
- Check JIT buffer size is sufficient: `opcache.jit_buffer_size=100M`
- Remember: JIT doesn't help with database queries, file I/O, or network requests

### Problem: Memory Usage Still High Despite Generators

**Symptom**: Using generators but memory usage remains high

**Cause**: Generator results are being accumulated in arrays or other data structures

**Solution**: Process generator results immediately without storing:

```php
# filename: generator-memory-comparison.php
<?php

declare(strict_types=1);

// Bad: Accumulates results
$results = [];
foreach ($generator as $item) {
    $results[] = process($item);  // Still uses memory
}

// Good: Process immediately
foreach ($generator as $item) {
    process($item);  // No accumulation
    // Or write directly to output/file
}
```

### Error: Benchmark Results Inconsistent

**Symptom**: Benchmark results vary significantly between runs

**Cause**: System load, other processes, or insufficient iterations

**Solution**:

- Run multiple iterations and average results
- Use statistical methods (median, percentiles)
- Ensure consistent system state
- Warm up PHP before benchmarking (OPcache, JIT)

```php
# filename: reliable-benchmark.php
<?php

declare(strict_types=1);

function reliableBenchmark(callable $fn, int $iterations = 1000): array
{
    // Warm up
    for ($i = 0; $i < 10; $i++) {
        $fn();
    }

    // Actual benchmark
    $times = [];
    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $fn();
        $times[] = microtime(true) - $start;
    }

    sort($times);

    return [
        'min' => $times[0] * 1000,
        'max' => end($times) * 1000,
        'median' => $times[(int)(count($times) / 2)] * 1000,
        'p95' => $times[(int)(count($times) * 0.95)] * 1000,
        'mean' => array_sum($times) / count($times) * 1000,
    ];
}
```

## Exercises

### Exercise 1: Create a Performance Profiler

**Goal**: Build a comprehensive profiling tool that measures time, memory, and function calls

Create a file called `performance-profiler.php` and implement:

- A `PerformanceProfiler` class that tracks:
  - Execution time (wall clock and CPU time)
  - Memory usage (current and peak)
  - Function call counts
- Methods to start/stop profiling
- A method to generate a formatted report
- Support for nested profiling (profiling within profiling)

**Validation**: Test your profiler:

```php
# filename: performance-profiler-test.php
<?php

declare(strict_types=1);

$profiler = new PerformanceProfiler();
$profiler->start('main');

// Simulate work
usleep(100000);  // 100ms
$data = range(1, 10000);

$profiler->start('sort');
sort($data);
$profiler->stop('sort');

$profiler->stop('main');
echo $profiler->getReport();
```

Expected output should show timing and memory usage for both 'main' and 'sort' operations.

### Exercise 2: Optimize a Slow Function

**Goal**: Practice identifying and fixing performance bottlenecks

Given this slow function:

```php
# filename: slow-function.php
<?php

declare(strict_types=1);

function slowFunction(array $data): array
{
    $result = [];

    foreach ($data as $item) {
        $processed = [];
        foreach ($data as $other) {
            if ($item['id'] === $other['id']) {
                $processed[] = $other;
            }
        }
        $result[] = $processed;
    }

    return $result;
}
```

Optimize this function. The function should group items by their 'id' field.

**Requirements**:

- Use profiling to identify the bottleneck
- Optimize the algorithm (hint: O(n²) → O(n))
- Benchmark before and after
- Show at least 10x improvement

**Validation**: Test with 1000 items:

```php
# filename: test-slow-function.php
<?php

declare(strict_types=1);

$data = [];
for ($i = 0; $i < 1000; $i++) {
    $data[] = ['id' => $i % 100, 'value' => $i];
}

$bench = new Benchmark();
$stats = $bench->measure(fn() => slowFunction($data));
echo "Time: {$stats['time']}ms\n";
```

### Exercise 3: Memory-Efficient File Processor

**Goal**: Process large files without running out of memory

Create a file called `file-processor.php` that:

- Reads a large CSV file line by line (use generators)
- Processes each line (e.g., calculate sum of numeric columns)
- Writes results to an output file
- Uses constant memory regardless of file size

**Requirements**:

- Handle files larger than available memory
- Process 1 million+ rows efficiently
- Track memory usage and ensure it stays constant
- Include error handling for file operations

**Validation**: Test with a large CSV file:

```php
# filename: test-file-processor.php
<?php

declare(strict_types=1);

$processor = new FileProcessor();
$processor->process('large-file.csv', 'output.txt');

echo "Peak memory: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";
// Should be under 50MB even for 1M+ row files
```

## Response Compression

Compressing HTTP responses reduces bandwidth usage and improves page load times, especially for text-based content (HTML, CSS, JavaScript, JSON). Modern browsers support gzip, deflate, and Brotli compression.

**Compression Benefits:**

- Reduces response size by 60-90% for text content
- Faster page loads, especially on slow connections
- Lower bandwidth costs
- Better user experience

**When to Compress:**

- HTML, CSS, JavaScript, JSON, XML responses
- Text-based API responses
- Large responses (>1KB)

**When NOT to Compress:**

- Already compressed content (images, videos, PDFs)
- Very small responses (<500 bytes, overhead not worth it)
- Real-time streaming data

```php
# filename: response-compression.php
<?php

class ResponseCompression
{
    private const MIN_SIZE = 500;  // Don't compress small responses

    public function compress(string $content, string $encoding = 'gzip'): string
    {
        // Don't compress if too small
        if (strlen($content) < self::MIN_SIZE) {
            return $content;
        }

        // Check if client supports compression
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

        if ($encoding === 'brotli' && str_contains($acceptEncoding, 'br')) {
            if (function_exists('brotli_compress')) {
                $compressed = brotli_compress($content, 4);  // Level 4 (balanced)
                if ($compressed !== false) {
                    header('Content-Encoding: br');
                    header('Vary: Accept-Encoding');
                    return $compressed;
                }
            }
        }

        if (str_contains($acceptEncoding, 'gzip')) {
            $compressed = gzencode($content, 6);  // Level 6 (balanced)
            if ($compressed !== false) {
                header('Content-Encoding: gzip');
                header('Vary: Accept-Encoding');
                return $compressed;
            }
        }

        if (str_contains($acceptEncoding, 'deflate')) {
            $compressed = gzdeflate($content, 6);
            if ($compressed !== false) {
                header('Content-Encoding: deflate');
                header('Vary: Accept-Encoding');
                return $compressed;
            }
        }

        return $content;
    }

    public function compressResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        // Convert to JSON if array/object
        if (is_array($data) || is_object($data)) {
            header('Content-Type: application/json');
            $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $content = (string)$data;
        }

        // Compress and output
        $compressed = $this->compress($content);
        header('Content-Length: ' . strlen($compressed));
        echo $compressed;
    }

    public function benchmarkCompression(string $content): array
    {
        $originalSize = strlen($content);

        $gzip = gzencode($content, 6);
        $gzipSize = strlen($gzip);

        $brotli = function_exists('brotli_compress')
            ? brotli_compress($content, 4)
            : false;
        $brotliSize = $brotli ? strlen($brotli) : 0;

        return [
            'original_size' => $originalSize,
            'gzip_size' => $gzipSize,
            'gzip_ratio' => round(($gzipSize / $originalSize) * 100, 2) . '%',
            'gzip_savings' => round((1 - $gzipSize / $originalSize) * 100, 2) . '%',
            'brotli_size' => $brotliSize,
            'brotli_ratio' => $brotliSize > 0
                ? round(($brotliSize / $originalSize) * 100, 2) . '%'
                : 'N/A',
            'brotli_savings' => $brotliSize > 0
                ? round((1 - $brotliSize / $originalSize) * 100, 2) . '%'
                : 'N/A',
        ];
    }
}

// Usage
$compressor = new ResponseCompression();

// Compress API response
$data = ['users' => fetchUsers()];  // Large dataset
$compressor->compressResponse($data);

// Benchmark compression
$html = file_get_contents('large-page.html');
$stats = $compressor->benchmarkCompression($html);
/*
Typical results for HTML:
- Original: 100KB
- Gzip: 25KB (75% reduction)
- Brotli: 20KB (80% reduction)
*/
```

**Server-Level Compression (Recommended):**

For better performance, configure compression at the web server level:

**Nginx:**

```nginx
gzip on;
gzip_vary on;
gzip_min_length 500;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
gzip_comp_level 6;

# Brotli (if module installed)
brotli on;
brotli_comp_level 4;
brotli_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
```

**Apache (.htaccess):**

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

## Application-Level Caching Strategies

While PHP-level optimizations (OPcache, JIT) improve code execution, application-level caching reduces database queries, API calls, and expensive computations. Effective caching can provide 10-100x improvements for frequently accessed data.

### Redis/Memcached Integration

Redis and Memcached are in-memory data stores perfect for caching. Redis offers more features (persistence, data structures), while Memcached is simpler and faster for basic key-value caching.

```php
# filename: redis-cache.php
<?php

declare(strict_types=1);

class RedisCache
{
    private \Redis $redis;

    public function __construct(string $host = '127.0.0.1', int $port = 6379)
    {
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
    }

    // Simple cache get/set
    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->redis->setex($key, $ttl, serialize($value));
    }

    // Cache with automatic refresh
    public function remember(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $cached = $this->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    // Cache tags for invalidation
    public function setWithTags(string $key, mixed $value, array $tags, int $ttl = 3600): bool
    {
        $this->set($key, $value, $ttl);

        // Store key in each tag's set
        foreach ($tags as $tag) {
            $this->redis->sAdd("tag:$tag", $key);
            $this->redis->expire("tag:$tag", $ttl);
        }

        return true;
    }

    // Invalidate by tag
    public function invalidateTag(string $tag): int
    {
        $keys = $this->redis->sMembers("tag:$tag");
        if (empty($keys)) {
            return 0;
        }

        $count = 0;
        foreach ($keys as $key) {
            if ($this->redis->del($key)) {
                $count++;
            }
        }

        $this->redis->del("tag:$tag");
        return $count;
    }

    // Batch operations
    public function getMultiple(array $keys): array
    {
        $values = $this->redis->mget($keys);
        $result = [];

        foreach ($keys as $index => $key) {
            if ($values[$index] !== false) {
                $result[$key] = unserialize($values[$index]);
            }
        }

        return $result;
    }
}

// Usage
$cache = new RedisCache();

// Simple caching
$user = $cache->remember("user:123", function() {
    // Expensive database query
    return fetchUserFromDatabase(123);
}, 3600);

// Tagged caching
$cache->setWithTags("product:456", $product, ['products', 'category:electronics'], 7200);

// Invalidate all electronics products
$cache->invalidateTag('category:electronics');
```

### Cache Invalidation Strategies

Proper cache invalidation ensures users see updated data without stale cache issues.

```php
# filename: cache-invalidation.php
<?php

declare(strict_types=1);

class CacheInvalidation
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    // Strategy 1: Time-based expiration (TTL)
    public function cacheWithTTL(string $key, mixed $value, int $ttl): void
    {
        $this->redis->setex($key, $ttl, serialize($value));
    }

    // Strategy 2: Event-based invalidation
    public function invalidateOnUpdate(string $entityType, int $entityId): void
    {
        $patterns = [
            "{$entityType}:{$entityId}",
            "{$entityType}:{$entityId}:*",
            "list:{$entityType}",
        ];

        foreach ($patterns as $pattern) {
            $keys = $this->redis->keys($pattern);
            if (!empty($keys)) {
                $this->redis->del($keys);
            }
        }
    }

    // Strategy 3: Version-based cache
    public function getWithVersion(string $key, int $currentVersion): mixed
    {
        $cached = $this->redis->get("{$key}:v{$currentVersion}");
        return $cached !== false ? unserialize($cached) : null;
    }

    public function setWithVersion(string $key, mixed $value, int $version): void
    {
        $this->redis->set("{$key}:v{$version}", serialize($value));
        $this->redis->set("{$key}:version", $version);
    }

    // Strategy 4: Cache warming after invalidation
    public function invalidateAndWarm(string $key, callable $warmCallback): mixed
    {
        $this->redis->del($key);

        // Warm cache in background (non-blocking)
        $value = $warmCallback();
        $this->redis->setex($key, 3600, serialize($value));

        return $value;
    }
}
```

### Cache Stampede Prevention

Cache stampedes occur when a cached item expires and many requests simultaneously try to regenerate it, overwhelming the system.

```php
# filename: cache-stampede-prevention.php
<?php

declare(strict_types=1);

class CacheStampedePrevention
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    // Strategy 1: Lock-based (mutex)
    public function getWithLock(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $value = $this->redis->get($key);
        if ($value !== false) {
            return unserialize($value);
        }

        $lockKey = "lock:{$key}";
        $lockAcquired = $this->redis->set($lockKey, '1', ['nx', 'ex' => 10]);

        if ($lockAcquired) {
            // We got the lock, generate value
            try {
                $value = $callback();
                $this->redis->setex($key, $ttl, serialize($value));
                return $value;
            } finally {
                $this->redis->del($lockKey);
            }
        } else {
            // Someone else is generating, wait and retry
            usleep(100000); // 100ms
            return $this->getWithLock($key, $callback, $ttl);
        }
    }

    // Strategy 2: Probabilistic early expiration
    public function getWithEarlyExpiration(string $key, callable $callback, int $ttl = 3600): mixed
    {
        $value = $this->redis->get($key);
        if ($value !== false) {
            $data = unserialize($value);

            // Check if we should refresh early (10% chance)
            $ttlRemaining = $this->redis->ttl($key);
            if ($ttlRemaining > 0 && $ttlRemaining < ($ttl * 0.1) && mt_rand(1, 100) <= 10) {
                // Refresh in background
                $this->refreshInBackground($key, $callback, $ttl);
            }

            return $data;
        }

        // Cache miss, generate synchronously
        $value = $callback();
        $this->redis->setex($key, $ttl, serialize($value));
        return $value;
    }

    private function refreshInBackground(string $key, callable $callback, int $ttl): void
    {
        // In production, use a job queue or async process
        // For demo, we'll just refresh synchronously
        $value = $callback();
        $this->redis->setex($key, $ttl, serialize($value));
    }

    // Strategy 3: Stale-while-revalidate
    public function getStaleWhileRevalidate(string $key, callable $callback, int $ttl = 3600, int $staleTtl = 7200): mixed
    {
        $value = $this->redis->get($key);
        $staleValue = $this->redis->get("stale:{$key}");

        if ($value !== false) {
            // Fresh value exists
            return unserialize($value);
        }

        if ($staleValue !== false) {
            // Return stale value and refresh in background
            $this->refreshInBackground($key, $callback, $ttl);
            return unserialize($staleValue);
        }

        // No cache, generate synchronously
        $newValue = $callback();
        $this->redis->setex($key, $ttl, serialize($newValue));
        $this->redis->setex("stale:{$key}", $staleTtl, serialize($newValue));
        return $newValue;
    }
}
```

### Multi-Level Caching

Multi-level caching uses different cache layers for optimal performance and cost.

```php
# filename: multi-level-cache.php
<?php

declare(strict_types=1);

class MultiLevelCache
{
    private array $l1Cache = []; // In-memory (fastest, smallest)
    private \Redis $l2Cache;      // Redis (fast, medium size)
    private \PDO $l3Cache;        // Database (slowest, largest)

    public function __construct(\Redis $redis, \PDO $pdo)
    {
        $this->l2Cache = $redis;
        $this->l3Cache = $pdo;
    }

    public function get(string $key): mixed
    {
        // L1: Check in-memory cache
        if (isset($this->l1Cache[$key])) {
            return $this->l1Cache[$key];
        }

        // L2: Check Redis
        $l2Value = $this->l2Cache->get($key);
        if ($l2Value !== false) {
            $value = unserialize($l2Value);
            // Promote to L1
            $this->l1Cache[$key] = $value;
            return $value;
        }

        // L3: Check database
        $stmt = $this->l3Cache->prepare("SELECT value FROM cache WHERE `key` = ? AND expires_at > NOW()");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $value = unserialize($row['value']);
            // Promote to L2 and L1
            $this->l2Cache->setex($key, 3600, serialize($value));
            $this->l1Cache[$key] = $value;
            return $value;
        }

        return null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        // Set in all levels
        $this->l1Cache[$key] = $value;
        $this->l2Cache->setex($key, $ttl, serialize($value));

        $stmt = $this->l3Cache->prepare("
            INSERT INTO cache (`key`, value, expires_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
            ON DUPLICATE KEY UPDATE value = ?, expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND)
        ");
        $serialized = serialize($value);
        $stmt->execute([$key, $serialized, $ttl, $serialized, $ttl]);
    }

    public function invalidate(string $key): void
    {
        unset($this->l1Cache[$key]);
        $this->l2Cache->del($key);

        $stmt = $this->l3Cache->prepare("DELETE FROM cache WHERE `key` = ?");
        $stmt->execute([$key]);
    }
}
```

### Memoization Patterns

Memoization caches function results to avoid recomputing expensive operations.

```php
# filename: memoization.php
<?php

declare(strict_types=1);

class Memoization
{
    private array $cache = [];

    // Simple memoization wrapper
    public function memoize(callable $fn): callable
    {
        return function(...$args) use ($fn) {
            $key = md5(serialize($args));

            if (!isset($this->cache[$key])) {
                $this->cache[$key] = $fn(...$args);
            }

            return $this->cache[$key];
        };
    }

    // Recursive memoization (e.g., Fibonacci)
    public function memoizedFibonacci(int $n): int
    {
        static $cache = [];

        if ($n <= 1) {
            return $n;
        }

        if (!isset($cache[$n])) {
            $cache[$n] = $this->memoizedFibonacci($n - 1) + $this->memoizedFibonacci($n - 2);
        }

        return $cache[$n];
    }

    // Closure-based memoization with TTL
    public function memoizeWithTTL(callable $fn, int $ttl = 3600): callable
    {
        return function(...$args) use ($fn, $ttl) {
            $key = md5(serialize($args));

            if (isset($this->cache[$key])) {
                [$value, $expires] = $this->cache[$key];
                if (time() < $expires) {
                    return $value;
                }
            }

            $value = $fn(...$args);
            $this->cache[$key] = [$value, time() + $ttl];
            return $value;
        };
    }
}

// Usage
$memo = new Memoization();

// Memoize expensive function
$expensiveFunction = $memo->memoize(function(int $n): int {
    // Simulate expensive computation
    sleep(1);
    return $n * 2;
});

echo $expensiveFunction(5); // Takes 1 second
echo $expensiveFunction(5); // Instant (cached)

// Fibonacci with memoization
$fib = new Memoization();
echo $fib->memoizedFibonacci(50); // Fast with memoization
```

### HTTP Caching Headers

HTTP caching reduces server load by allowing browsers and proxies to cache responses.

```php
# filename: http-caching.php
<?php

declare(strict_types=1);

class HttpCaching
{
    // Set Cache-Control headers
    public function setCacheControl(int $maxAge, bool $public = true, bool $mustRevalidate = false): void
    {
        $directives = [];

        if ($public) {
            $directives[] = 'public';
        } else {
            $directives[] = 'private';
        }

        $directives[] = "max-age={$maxAge}";

        if ($mustRevalidate) {
            $directives[] = 'must-revalidate';
        }

        header('Cache-Control: ' . implode(', ', $directives));
    }

    // ETag-based caching
    public function handleETag(string $content): void
    {
        $etag = md5($content);
        header("ETag: \"{$etag}\"");

        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        if ($ifNoneMatch === "\"{$etag}\"") {
            http_response_code(304); // Not Modified
            exit;
        }
    }

    // Last-Modified caching
    public function handleLastModified(int $timestamp): void
    {
        $lastModified = gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
        header("Last-Modified: {$lastModified}");

        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        if ($ifModifiedSince && strtotime($ifModifiedSince) >= $timestamp) {
            http_response_code(304); // Not Modified
            exit;
        }
    }

    // Vary header for content negotiation
    public function setVary(array $headers): void
    {
        header('Vary: ' . implode(', ', $headers));
    }

    // Complete caching setup
    public function cacheResponse(string $content, int $maxAge = 3600, ?int $lastModified = null): void
    {
        $this->setCacheControl($maxAge);

        if ($lastModified) {
            $this->handleLastModified($lastModified);
        }

        $this->handleETag($content);
        echo $content;
    }
}

// Usage in a controller
$httpCache = new HttpCaching();

// Cache API response for 1 hour
$data = json_encode(['users' => getUsers()]);
$httpCache->cacheResponse($data, 3600, filemtime(__FILE__));
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
- [ ] OPcache preloading configured
- [ ] APCu enabled for single-server caching
- [ ] Database connection pooling implemented

## Key Takeaways

- Profile before optimizing - measure to find bottlenecks
- Use PHP's built-in array functions - they're optimized in C
- OPcache provides 2-3x performance boost (always enable)
- OPcache preloading provides 5-15% additional improvement by precompiling classes
- JIT adds 1.5-3x improvement for CPU-intensive code (PHP 8+)
- Generators save memory for large datasets
- Early termination saves unnecessary computations
- Cache locality affects performance significantly
- String concatenation in loops is expensive
- Batch database operations when possible
- Connection pooling reduces database connection overhead by 10x
- References can reduce memory copies but add complexity
- Lazy evaluation delays computation until needed
- PHP 8+ typed properties enable JIT optimizations
- Match expressions are 20% faster than switch
- Fibers improve I/O-bound concurrent operations
- APCu provides faster caching than Redis for single-server deployments
- Response compression (gzip/brotli) reduces bandwidth by 60-90%
- Application-level caching (Redis/Memcached) provides 10-100x improvements for frequently accessed data
- Cache stampede prevention (locks, early expiration, stale-while-revalidate) protects against thundering herd
- Multi-level caching (L1/L2/L3) optimizes for both speed and cost
- Memoization caches function results to avoid recomputing expensive operations
- HTTP caching headers (ETags, Cache-Control) reduce server load significantly
- Proper cache invalidation strategies prevent stale data issues
- Professional profiling tools (Blackfire, Tideways, New Relic) are essential
- Continuous monitoring prevents performance regressions
- 80/20 rule: Focus on optimizing the 20% of code causing 80% of issues

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 29 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-29)**

Clone the repository to run examples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-29
php 01-*.php
```

## Wrap-up

Congratulations! You've mastered performance optimization techniques that can dramatically improve your PHP applications. In this chapter, you've learned to:

- ✓ Profile code using Xdebug, Blackfire, and Xhprof to identify bottlenecks
- ✓ Benchmark code performance with custom measurement tools
- ✓ Optimize memory usage with generators, lazy evaluation, and references
- ✓ Apply PHP-specific optimizations including OPcache and JIT compilation
- ✓ Configure OPcache preloading for 5-15% additional performance
- ✓ Optimize algorithms with early termination, loop improvements, and cache locality
- ✓ Optimize database queries to avoid N+1 problems and use batch operations
- ✓ Implement connection pooling to reduce database overhead
- ✓ Use PHP 8+ features like match expressions, typed properties, and fibers
- ✓ Use APCu for fast single-server caching
- ✓ Enable response compression (gzip/brotli) to reduce bandwidth
- ✓ Implement application-level caching with Redis/Memcached and proper invalidation
- ✓ Prevent cache stampedes and design multi-level caching architectures
- ✓ Apply memoization patterns and HTTP caching headers
- ✓ Implement professional profiling tools for production monitoring
- ✓ Follow a systematic optimization workflow with before/after measurements

Performance optimization is an ongoing process. Remember the 80/20 rule: focus on optimizing the 20% of code that causes 80% of performance issues. Always measure before optimizing, and verify improvements with benchmarks.

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="29"
  label="Performance Optimization mastered!"
/>

## Further Reading

- [PHP Performance Best Practices](https://www.php.net/manual/en/features.gc.performance-considerations.php) — Official PHP documentation on performance considerations
- [OPcache Configuration](https://www.php.net/manual/en/opcache.configuration.php) — Complete OPcache configuration reference
- [Blackfire Documentation](https://blackfire.io/docs) — Professional profiling tool documentation
- [PHP Internals Book](https://www.phpinternalsbook.com/) — Deep dive into PHP's internal workings
- [Xdebug Profiling Guide](https://xdebug.org/docs/profiler) — Xdebug profiling documentation
- [New Relic PHP Agent](https://docs.newrelic.com/docs/apm/agents/php-agent/) — APM monitoring for PHP applications

## Next Steps

In the final chapter, we'll explore real-world case studies demonstrating these algorithms and optimization techniques in practical PHP applications with before/after metrics.
