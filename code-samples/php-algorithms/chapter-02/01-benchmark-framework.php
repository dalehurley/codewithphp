<?php
/**
 * Benchmarking Framework
 *
 * Complete benchmarking system for measuring algorithm performance
 *
 * @package PHPAlgorithms
 * @chapter 02
 */

declare(strict_types=1);

class Benchmark
{
    private array $results = [];

    /**
     * Run a single benchmark
     *
     * @param string $name Test name
     * @param callable $function Function to benchmark
     * @param int $iterations Number of times to run
     * @return float Average time in milliseconds
     */
    public function run(string $name, callable $function, int $iterations = 1): float
    {
        // Warm up
        $function();

        // Garbage collection for clean measurement
        gc_collect_cycles();

        // Measure
        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $function();
        }

        $end = hrtime(true);

        // Calculate average in milliseconds
        $totalNs = $end - $start;
        $averageMs = ($totalNs / $iterations) / 1_000_000;

        $this->results[$name] = $averageMs;

        return $averageMs;
    }

    /**
     * Compare multiple implementations
     *
     * @param array<string, callable> $tests Name => function pairs
     * @param mixed $input Input to pass to all functions
     * @param int $iterations Number of iterations
     */
    public function compare(array $tests, mixed $input, int $iterations = 100): void
    {
        $inputSize = is_array($input) ? count($input) : strlen((string)$input);
        echo "Benchmarking with input size: {$inputSize}\n";
        echo str_repeat('-', 60) . "\n";

        foreach ($tests as $name => $function) {
            $time = $this->run($name, fn() => $function($input), $iterations);
            printf("%-30s: %10.4f ms\n", $name, $time);
        }

        echo str_repeat('-', 60) . "\n";
        $this->printRankings();
        $this->results = [];
    }

    private function printRankings(): void
    {
        asort($this->results);
        $fastest = reset($this->results);

        echo "\nRankings:\n";
        $rank = 1;

        foreach ($this->results as $name => $time) {
            $ratio = $time / max($fastest, 0.001);
            printf("%d. %-30s (%.2fx)\n", $rank++, $name, $ratio);
        }
    }
}

class MemoryProfiler
{
    /**
     * Profile memory usage of a function
     */
    public function profile(string $name, callable $function): array
    {
        gc_collect_cycles();
        $startMemory = memory_get_usage(true);

        $result = $function();

        $endMemory = memory_get_usage(true);
        $used = $endMemory - $startMemory;

        echo "{$name}: " . $this->formatBytes($used) . "\n";

        return [
            'result' => $result,
            'memory' => $used,
            'formatted' => $this->formatBytes($used)
        ];
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

class StatisticalBenchmark
{
    /**
     * Run multiple times and compute statistics
     */
    public function runWithStats(string $name, callable $function, int $runs = 10): array
    {
        $times = [];

        for ($i = 0; $i < $runs; $i++) {
            $start = hrtime(true);
            $function();
            $end = hrtime(true);
            $times[] = ($end - $start) / 1_000_000;
        }

        return [
            'name' => $name,
            'min' => min($times),
            'max' => max($times),
            'avg' => array_sum($times) / count($times),
            'median' => $this->median($times),
            'stddev' => $this->stddev($times)
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

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Benchmarking Framework ===\n\n";

    // Example: Compare sorting algorithms
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

    function quickSort(array $arr): array {
        if (count($arr) < 2) return $arr;
        $pivot = $arr[0];
        $left = $right = [];
        for ($i = 1; $i < count($arr); $i++) {
            if ($arr[$i] < $pivot) $left[] = $arr[$i];
            else $right[] = $arr[$i];
        }
        return array_merge(quickSort($left), [$pivot], quickSort($right));
    }

    // Test sorting
    echo "1. Comparing Sorting Algorithms:\n";
    $data = range(1, 100);
    shuffle($data);

    $bench->compare([
        'Bubble Sort' => fn($arr) => bubbleSort($arr),
        'Quick Sort' => fn($arr) => quickSort($arr),
        'PHP sort()' => function($arr) {
            sort($arr);
            return $arr;
        },
    ], $data);

    // Memory profiling
    echo "\n2. Memory Profiling:\n";
    $profiler = new MemoryProfiler();

    $profiler->profile('Create 10K array', function() {
        return range(1, 10000);
    });

    $profiler->profile('Create 100K array', function() {
        return range(1, 100000);
    });

    // Statistical analysis
    echo "\n3. Statistical Analysis:\n";
    $statBench = new StatisticalBenchmark();
    $stats = $statBench->runWithStats('Quick Sort', fn() => quickSort($data), 20);
    $statBench->printStats($stats);
}
