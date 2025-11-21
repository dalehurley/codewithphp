<?php

declare(strict_types=1);

/**
 * Performance Optimization - Benchmark Framework
 *
 * Comprehensive benchmarking and optimization tools for PHP applications.
 */

/**
 * Benchmark utility class
 */
class Benchmark
{
    private float $startTime;
    private int $startMemory;

    public function start(): void
    {
        gc_collect_cycles();  // Clean up before measuring
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    public function end(): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        return [
            'time_ms' => ($endTime - $this->startTime) * 1000,
            'memory_kb' => ($endMemory - $this->startMemory) / 1024,
            'peak_memory_kb' => memory_get_peak_usage() / 1024
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

    public function compare(array $functions, array $args = [], int $iterations = 1): array
    {
        $results = [];

        foreach ($functions as $name => $fn) {
            $times = [];
            $memories = [];

            for ($i = 0; $i < $iterations; $i++) {
                $stats = $this->measure($fn, $args);
                $times[] = $stats['time_ms'];
                $memories[] = $stats['memory_kb'];
            }

            $results[$name] = [
                'avg_time_ms' => array_sum($times) / count($times),
                'min_time_ms' => min($times),
                'max_time_ms' => max($times),
                'avg_memory_kb' => array_sum($memories) / count($memories),
                'iterations' => $iterations
            ];
        }

        return $results;
    }
}

/**
 * Memory Optimization Examples
 */
class MemoryOptimization
{
    // Bad: Copies entire array
    public function sumBad(array $data): int
    {
        $sum = 0;
        foreach ($data as $item) {
            $sum += $item['value'];
        }
        return $sum;
    }

    // Good: Uses reference
    public function sumGood(array &$data): int
    {
        $sum = 0;
        foreach ($data as &$item) {
            $sum += $item['value'];
        }
        return $sum;
    }

    // Best: Built-in function
    public function sumBest(array $data): int
    {
        return array_sum(array_column($data, 'value'));
    }
}

/**
 * Generator Examples for Memory Efficiency
 */
class GeneratorExamples
{
    // Bad: Loads all into memory
    public function rangeBad(int $start, int $end): array
    {
        $result = [];
        for ($i = $start; $i <= $end; $i++) {
            $result[] = $i;
        }
        return $result;
    }

    // Good: Yields values
    public function rangeGood(int $start, int $end): \Generator
    {
        for ($i = $start; $i <= $end; $i++) {
            yield $i;
        }
    }

    public function processLargeFileBad(string $filename): array
    {
        return file($filename);  // Loads entire file
    }

    public function processLargeFileGood(string $filename): \Generator
    {
        $handle = fopen($filename, 'r');
        if (!$handle) {
            throw new RuntimeException("Cannot open file: $filename");
        }

        while (($line = fgets($handle)) !== false) {
            yield $line;
        }

        fclose($handle);
    }
}

/**
 * PHP 8+ Optimizations
 */
class PHP8Optimizations
{
    // Match expression (faster than switch)
    public function getStatusMatch(string $status): string
    {
        return match($status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
            default => 'Unknown'
        };
    }

    public function getStatusSwitch(string $status): string
    {
        switch ($status) {
            case 'draft': return 'Draft';
            case 'published': return 'Published';
            case 'archived': return 'Archived';
            default: return 'Unknown';
        }
    }

    // Typed properties enable JIT optimizations
    private int $count = 0;

    public function processTyped(int $value): int
    {
        return $value * 2;
    }

    public function processMixed(mixed $value): mixed
    {
        return $value * 2;
    }
}

/**
 * String Optimization
 */
class StringOptimization
{
    // Bad: String concatenation in loop
    public function concatenateBad(array $strings): string
    {
        $result = '';
        foreach ($strings as $str) {
            $result .= $str;
        }
        return $result;
    }

    // Good: Use implode
    public function concatenateGood(array $strings): string
    {
        return implode('', $strings);
    }
}

// =============================================================================
// EXAMPLES
// =============================================================================

echo "Performance Optimization Examples\n";
echo str_repeat("=", 70) . "\n\n";

$bench = new Benchmark();

// Example 1: Memory optimization comparison
echo "Example 1: Memory Optimization\n";
echo "-" . str_repeat("-", 69) . "\n";

$data = array_fill(0, 10000, ['value' => 1]);
$memOpt = new MemoryOptimization();

$results = $bench->compare([
    'Bad (by value)' => [$memOpt, 'sumBad'],
    'Good (by reference)' => [$memOpt, 'sumGood'],
    'Best (array functions)' => [$memOpt, 'sumBest']
], [$data]);

foreach ($results as $name => $stats) {
    echo sprintf(
        "%25s: %6.2f ms, %7.2f KB\n",
        $name,
        $stats['avg_time_ms'],
        $stats['avg_memory_kb']
    );
}
echo "\n";

// Example 2: Generator vs Array
echo "Example 2: Generator Memory Efficiency\n";
echo "-" . str_repeat("-", 69) . "\n";

$genEx = new GeneratorExamples();
$size = 1000000;

echo "Processing $size numbers:\n";

$stats1 = $bench->measure(function() use ($genEx, $size) {
    $sum = 0;
    foreach ($genEx->rangeBad(1, $size) as $n) {
        $sum += $n;
        if ($sum > PHP_INT_MAX) break;  // Prevent overflow
    }
    return $sum;
});

echo sprintf("Array:     Time = %7.2f ms, Memory = %8.2f KB\n",
    $stats1['time_ms'], $stats1['memory_kb']);

$stats2 = $bench->measure(function() use ($genEx, $size) {
    $sum = 0;
    foreach ($genEx->rangeGood(1, $size) as $n) {
        $sum += $n;
        if ($sum > PHP_INT_MAX) break;
    }
    return $sum;
});

echo sprintf("Generator: Time = %7.2f ms, Memory = %8.2f KB\n",
    $stats2['time_ms'], $stats2['memory_kb']);

$memSaved = $stats1['memory_kb'] - $stats2['memory_kb'];
echo "Memory saved: " . number_format($memSaved, 2) . " KB\n\n";

// Example 3: PHP 8 Match vs Switch
echo "Example 3: Match vs Switch Performance\n";
echo "-" . str_repeat("-", 69) . "\n";

$php8 = new PHP8Optimizations();
$statuses = ['draft', 'published', 'archived', 'unknown'];

$results = $bench->compare([
    'Switch' => function() use ($php8, $statuses) {
        for ($i = 0; $i < 10000; $i++) {
            $php8->getStatusSwitch($statuses[$i % 4]);
        }
    },
    'Match' => function() use ($php8, $statuses) {
        for ($i = 0; $i < 10000; $i++) {
            $php8->getStatusMatch($statuses[$i % 4]);
        }
    }
], [], 5);  // 5 iterations

foreach ($results as $name => $stats) {
    echo sprintf(
        "%10s: %6.2f ms (avg of %d iterations)\n",
        $name,
        $stats['avg_time_ms'],
        $stats['iterations']
    );
}

$improvement = (($results['Switch']['avg_time_ms'] - $results['Match']['avg_time_ms'])
               / $results['Switch']['avg_time_ms']) * 100;
echo sprintf("Match is %.1f%% faster\n\n", $improvement);

// Example 4: String Concatenation
echo "Example 4: String Concatenation\n";
echo "-" . str_repeat("-", 69) . "\n";

$strings = array_fill(0, 1000, 'test');
$strOpt = new StringOptimization();

$results = $bench->compare([
    'Concatenation' => [$strOpt, 'concatenateBad'],
    'Implode' => [$strOpt, 'concatenateGood']
], [$strings], 10);

foreach ($results as $name => $stats) {
    echo sprintf(
        "%15s: %6.2f ms, %7.2f KB\n",
        $name,
        $stats['avg_time_ms'],
        $stats['avg_memory_kb']
    );
}

$speedup = $results['Concatenation']['avg_time_ms'] / $results['Implode']['avg_time_ms'];
echo sprintf("Implode is %.1fx faster\n\n", $speedup);

// Example 5: OPcache Impact Simulation
echo "Example 5: OPcache Benefits\n";
echo "-" . str_repeat("-", 69) . "\n";

echo "Typical performance improvements with OPcache:\n";
echo "  • Code execution: 2-3x faster\n";
echo "  • Memory usage: 20-30% reduction\n";
echo "  • Request handling: 40-50% more throughput\n\n";

echo "Enable in php.ini:\n";
echo "  opcache.enable=1\n";
echo "  opcache.memory_consumption=256\n";
echo "  opcache.max_accelerated_files=10000\n";

echo "\n✓ All examples completed successfully!\n";
echo "\nKey Optimization Tips:\n";
echo "  1. Use generators for large datasets\n";
echo "  2. Prefer built-in functions over loops\n";
echo "  3. Enable OPcache in production\n";
echo "  4. Use PHP 8+ features (match, typed properties)\n";
echo "  5. Profile before optimizing\n";
