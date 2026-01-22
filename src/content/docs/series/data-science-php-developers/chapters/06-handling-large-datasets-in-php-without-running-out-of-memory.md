---
title: "06: Handling Large Datasets in PHP Without Running Out of Memory"
description: "Master streaming, chunking, and memory-safe techniques for processing large datasets efficiently"
sidebar:
  label: "06: Handling Large Datasets in PHP Without Running Out of Memory"
  order: 6
  badge:
    text: Intermediate
    variant: caution
---

![Handling Large Datasets in PHP Without Running Out of Memory](/images/data-science-php-developers/chapter-06-large-datasets-hero-full.webp)

# Chapter 06: Handling Large Datasets in PHP Without Running Out of Memory

## Overview

You've learned to collect, clean, and analyze data—but what happens when your dataset is too large to fit in memory? A 5GB CSV file, a million-row database table, or a continuous stream of API data will crash your script if you try to load it all at once.

This chapter teaches you to process large datasets efficiently using PHP's memory-safe techniques: generators, iterators, streaming, and chunking. You'll learn to handle files of any size, process database results without exhausting memory, and build scalable data pipelines that work in production.

By the end of this chapter, you'll know how to process gigabytes of data with minimal memory footprint, profile and optimize memory usage, and build systems that scale from thousands to millions of records. PHP isn't slow—you just need the right approach.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 05: Exploratory Data Analysis](/series/data-science-php-developers/chapters/05-exploratory-data-analysis-for-php-developers)
- PHP 8.4+ installed
- Understanding of PHP generators and iterators (we'll review)
- League CSV library (`composer require league/csv`)
- Access to large sample datasets (we'll provide)
- **Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Check memory limit
php -r "echo ini_get('memory_limit');"

# Verify League CSV
composer show league/csv
```

## What You'll Build

By the end of this chapter, you will have created:

- **StreamingCSVReader**: Read CSV files of any size without loading into memory
- **ChunkedDatabaseProcessor**: Process millions of database rows in batches
- **LazyDataTransformer**: Transform data on-the-fly without intermediate storage
- **MemoryEfficientAggregator**: Calculate statistics on large datasets
- **BatchWriter**: Write large datasets efficiently
- **MemoryProfiler**: Monitor and optimize memory usage
- **Complete Pipeline**: End-to-end large dataset processing system
- **Performance Benchmarks**: Compare memory-safe vs naive approaches

## Objectives

- Understand PHP memory management and limits
- Use generators for memory-efficient iteration
- Stream large files without loading into memory
- Process database results in chunks
- Implement lazy loading and transformation
- Profile and optimize memory usage
- Build scalable data processing pipelines
- Handle datasets larger than available RAM

## Step 1: Understanding PHP Memory Management (~10 min)

### Goal

Understand how PHP manages memory and why naive approaches fail with large datasets.

### PHP Memory Basics

**Memory Limit**: PHP has a configurable memory limit (default: 128MB).

```php
# filename: examples/check-memory.php
<?php

echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Current Usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "Peak Usage: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
```

**Why Naive Approaches Fail:**

```php
# filename: examples/naive-approach.php
<?php

// ❌ BAD: Loads entire file into memory
$lines = file('large-file.csv'); // Fatal error: Allowed memory size exhausted

// ❌ BAD: Loads all database rows into array
$users = $pdo->query('SELECT * FROM users')->fetchAll(); // Memory exhausted

// ❌ BAD: Builds huge array in memory
$data = [];
for ($i = 0; $i < 1000000; $i++) {
    $data[] = ['id' => $i, 'value' => rand()];
}
// Memory exhausted
```

### Memory-Safe Approach

**The Flow**: Large Dataset → Stream/Generator → Process One Item → Output/Transform → Next Item (repeat)

This creates a continuous loop where each item is processed individually, then discarded before moving to the next one.

**Key Principle**: Process one item at a time, never loading the entire dataset.

### Actions

**1. Create a memory monitoring utility:**

```php
# filename: src/Memory/MemoryMonitor.php
<?php

declare(strict_types=1);

namespace DataScience\Memory;

class MemoryMonitor
{
    private int $startMemory;
    private int $peakMemory;
    private float $startTime;
    
    public function __construct()
    {
        $this->startMemory = memory_get_usage();
        $this->peakMemory = memory_get_peak_usage();
        $this->startTime = microtime(true);
    }
    
    /**
     * Get current memory usage
     */
    public function getCurrentUsage(): string
    {
        $bytes = memory_get_usage();
        return $this->formatBytes($bytes);
    }
    
    /**
     * Get peak memory usage
     */
    public function getPeakUsage(): string
    {
        $bytes = memory_get_peak_usage();
        return $this->formatBytes($bytes);
    }
    
    /**
     * Get memory used since start
     */
    public function getMemoryDelta(): string
    {
        $delta = memory_get_usage() - $this->startMemory;
        return $this->formatBytes($delta);
    }
    
    /**
     * Get elapsed time
     */
    public function getElapsedTime(): float
    {
        return round(microtime(true) - $this->startTime, 3);
    }
    
    /**
     * Print memory report
     */
    public function report(string $label = ''): void
    {
        $prefix = $label ? "{$label}: " : '';
        
        echo "{$prefix}Memory: {$this->getCurrentUsage()} " .
             "(Peak: {$this->getPeakUsage()}, " .
             "Delta: {$this->getMemoryDelta()}, " .
             "Time: {$this->getElapsedTime()}s)\n";
    }
    
    /**
     * Format bytes to human-readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Check if memory usage is acceptable
     */
    public function isMemoryAcceptable(int $maxMB = 128): bool
    {
        $currentMB = memory_get_usage() / 1024 / 1024;
        return $currentMB < $maxMB;
    }
}
```

**2. Demonstrate the problem:**

```php
# filename: examples/memory-problem-demo.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();

echo "=== Memory Problem Demonstration ===\n\n";

// Naive approach: Load everything into memory
echo "Approach 1: Load all data into array\n";
$data = [];

try {
    for ($i = 0; $i < 1000000; $i++) {
        $data[] = [
            'id' => $i,
            'name' => 'User ' . $i,
            'email' => "user{$i}@example.com",
            'value' => rand(1, 1000),
        ];
        
        if ($i % 100000 === 0) {
            $monitor->report("  After {$i} records");
        }
    }
    
    echo "✓ Successfully loaded " . count($data) . " records\n";
    $monitor->report("Final");
    
} catch (\Throwable $e) {
    echo "✗ Failed: {$e->getMessage()}\n";
    $monitor->report("At failure");
}

echo "\nNotice how memory grows linearly with data size!\n";
echo "This approach doesn't scale to millions of records.\n";
```

### Expected Result

```
=== Memory Problem Demonstration ===

Approach 1: Load all data into array
  After 0 records: Memory: 2.15 MB (Peak: 2.15 MB, Delta: 0.02 MB, Time: 0.001s)
  After 100000 records: Memory: 45.32 MB (Peak: 45.32 MB, Delta: 43.19 MB, Time: 0.125s)
  After 200000 records: Memory: 88.67 MB (Peak: 88.67 MB, Delta: 86.54 MB, Time: 0.251s)
  After 300000 records: Memory: 132.01 MB (Peak: 132.01 MB, Delta: 129.88 MB, Time: 0.378s)
✗ Failed: Allowed memory size of 134217728 bytes exhausted

Notice how memory grows linearly with data size!
This approach doesn't scale to millions of records.
```

### Why It Works

**Memory Growth**: Each array element consumes memory. With 1 million records, you're storing everything simultaneously.

**The Solution**: Process one record at a time using generators—memory stays constant regardless of dataset size.

### Troubleshooting

**Error: "Allowed memory size exhausted"**

**Cause**: Script exceeded PHP's memory limit.

**Solution**: Don't increase memory limit—use memory-safe techniques:

```php
// ❌ BAD: Increasing memory limit is a band-aid
ini_set('memory_limit', '512M');

// ✅ GOOD: Use generators to process one item at a time
function processLargeDataset(): Generator {
    foreach ($dataSource as $item) {
        yield $item;
    }
}
```

**Problem: Script is slow**

**Cause**: Processing too much data in memory causes swapping.

**Solution**: Use chunking and streaming:

```php
// Process in chunks of 1000
foreach (array_chunk($data, 1000) as $chunk) {
    processChunk($chunk);
    unset($chunk); // Free memory
}
```

## Step 1.5: PHP Memory Configuration (~5 min)

### Goal

Understand PHP's memory configuration and how to work within (not around) memory limits.

### Understanding Memory Limits

PHP has several configuration settings that control memory usage:

```php
# filename: examples/memory-config.php
<?php

// Check current configuration
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Max Input Time: " . ini_get('max_input_time') . "s\n";
echo "\n";

// Current usage
echo "Current Memory: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "Peak Memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
```

### Runtime Configuration

You can temporarily adjust settings within a script:

```php
# filename: examples/runtime-config.php
<?php

// ❌ BAD: Increasing memory limit is a band-aid
ini_set('memory_limit', '512M');

// This doesn't fix the underlying problem!
// You're just delaying the inevitable crash.

// ✅ GOOD: Use memory-efficient code instead
// Use generators, streaming, and chunking
```

### Configuration Files

For production environments, configure via `php.ini`:

```ini
# php.ini or .user.ini
memory_limit = 256M          ; Maximum memory per script
max_execution_time = 300     ; 5 minutes maximum
max_input_time = 300         ; 5 minutes for input parsing
post_max_size = 50M          ; Maximum POST data size
upload_max_filesize = 50M    ; Maximum upload file size
```

### Best Practices

**1. Don't increase memory_limit to solve memory problems**

```php
// ❌ WRONG APPROACH
ini_set('memory_limit', '2G');  // Just masks the problem
$data = file('huge-file.csv');  // Still fails with larger files

// ✅ RIGHT APPROACH
// Fix the code to use streaming
foreach ($reader->readFile('huge-file.csv') as $row) {
    processRow($row);
}
```

**2. Set appropriate limits for your use case**

```php
// Web requests: Lower limits (128-256 MB)
ini_set('memory_limit', '128M');

// CLI batch jobs: Higher limits if needed (512 MB - 1 GB)
// But still use memory-efficient code!
if (PHP_SAPI === 'cli') {
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', '0'); // No limit for CLI
}
```

**3. Monitor memory usage in production**

```php
# filename: examples/memory-monitoring.php
<?php

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();

// Check if approaching limit
if ($monitor->isNearLimit(80)) {
    // Log warning
    error_log("Memory usage at " . $monitor->getPercentageUsed() . "%");
    
    // Take action (e.g., flush caches, trigger cleanup)
    gc_collect_cycles();
}

// Detailed status
$monitor->report("Current Status");
```

### Actions

**1. Check your memory limit:**

```bash
php -r "echo ini_get('memory_limit');"
```

**2. Test MemoryMonitor enhancements:**

```php
# filename: examples/test-memory-monitor.php
<?php

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();

echo "=== Memory Configuration ===\n";
echo "Memory Limit: " . ($monitor->getMemoryLimitBytes() === -1 
    ? "Unlimited" 
    : round($monitor->getMemoryLimitBytes() / 1024 / 1024, 2) . " MB") . "\n";
echo "Current Usage: {$monitor->getCurrentUsage()}\n";
echo "Percentage Used: " . round($monitor->getPercentageUsed(), 2) . "%\n";
echo "Near Limit (80%): " . ($monitor->isNearLimit() ? "YES ⚠️" : "NO ✅") . "\n";
```

### Why It Works

**Memory Limit Purpose**: Prevents runaway scripts from consuming all server RAM.

**The Right Solution**: Write memory-efficient code that works within reasonable limits, not code that requires increasingly larger limits.

**Production Reality**: On shared hosting or containers, you can't always increase limits. Your code must be efficient.

### Key Insight

> "If you need to increase memory_limit to make your code work, your code is broken. Fix the code, not the limit."

The techniques in this chapter (generators, streaming, chunking) allow you to process unlimited data within fixed memory constraints.

## Step 2: Streaming Large Files with Generators (~20 min)

### Goal

Read and process files of any size using generators without loading into memory.

### Actions

**1. Create a streaming CSV reader:**

```php
# filename: src/Streaming/StreamingCSVReader.php
<?php

declare(strict_types=1);

namespace DataScience\Streaming;

use Generator;

class StreamingCSVReader
{
    /**
     * Read CSV file line by line using generator
     */
    public function readFile(
        string $filename,
        bool $hasHeader = true,
        string $delimiter = ',',
        string $enclosure = '"'
    ): Generator {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("File not found: {$filename}");
        }
        
        $handle = fopen($filename, 'r');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        try {
            $header = null;
            $lineNumber = 0;
            
            // Read header if present
            if ($hasHeader) {
                $header = fgetcsv($handle, 0, $delimiter, $enclosure);
                $lineNumber++;
            }
            
            // Yield each row
            while (($row = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false) {
                $lineNumber++;
                
                // Skip empty rows
                if ($row === [null]) {
                    continue;
                }
                
                // Convert to associative array if header exists
                if ($header !== null) {
                    yield $lineNumber => array_combine($header, $row);
                } else {
                    yield $lineNumber => $row;
                }
            }
        } finally {
            fclose($handle);
        }
    }
    
    /**
     * Read file in chunks
     */
    public function readChunks(
        string $filename,
        int $chunkSize = 1000,
        bool $hasHeader = true
    ): Generator {
        $chunk = [];
        $count = 0;
        
        foreach ($this->readFile($filename, $hasHeader) as $lineNumber => $row) {
            $chunk[] = $row;
            $count++;
            
            if ($count >= $chunkSize) {
                yield $chunk;
                $chunk = [];
                $count = 0;
            }
        }
        
        // Yield remaining rows
        if (!empty($chunk)) {
            yield $chunk;
        }
    }
    
    /**
     * Filter rows while streaming
     */
    public function filter(
        string $filename,
        callable $predicate,
        bool $hasHeader = true
    ): Generator {
        foreach ($this->readFile($filename, $hasHeader) as $lineNumber => $row) {
            if ($predicate($row)) {
                yield $lineNumber => $row;
            }
        }
    }
    
    /**
     * Transform rows while streaming
     */
    public function transform(
        string $filename,
        callable $transformer,
        bool $hasHeader = true
    ): Generator {
        foreach ($this->readFile($filename, $hasHeader) as $lineNumber => $row) {
            yield $lineNumber => $transformer($row);
        }
    }
    
    /**
     * Count rows without loading into memory
     */
    public function count(string $filename, bool $hasHeader = true): int
    {
        $count = 0;
        
        foreach ($this->readFile($filename, $hasHeader) as $row) {
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Calculate statistics while streaming
     */
    public function calculateStats(
        string $filename,
        string $column,
        bool $hasHeader = true
    ): array {
        $count = 0;
        $sum = 0;
        $min = PHP_FLOAT_MAX;
        $max = PHP_FLOAT_MIN;
        $values = [];
        
        foreach ($this->readFile($filename, $hasHeader) as $row) {
            $value = (float)($row[$column] ?? 0);
            
            $count++;
            $sum += $value;
            $min = min($min, $value);
            $max = max($max, $value);
            $values[] = $value;
        }
        
        if ($count === 0) {
            return [];
        }
        
        $mean = $sum / $count;
        
        // Calculate variance
        $variance = 0;
        foreach ($values as $value) {
            $variance += ($value - $mean) ** 2;
        }
        $variance /= $count;
        
        return [
            'count' => $count,
            'sum' => $sum,
            'mean' => $mean,
            'min' => $min,
            'max' => $max,
            'variance' => $variance,
            'std_dev' => sqrt($variance),
        ];
    }
}
```

**2. Create a sample large CSV file:**

```php
# filename: examples/generate-large-csv.php
<?php

declare(strict_types=1);

$filename = __DIR__ . '/../data/large_dataset.csv';
$rows = 100000; // 100k rows

echo "Generating large CSV file with {$rows} rows...\n";

$handle = fopen($filename, 'w');

// Write header
fputcsv($handle, ['id', 'name', 'email', 'age', 'salary', 'department']);

// Write rows
for ($i = 1; $i <= $rows; $i++) {
    fputcsv($handle, [
        $i,
        'Employee ' . $i,
        "employee{$i}@company.com",
        rand(22, 65),
        rand(30000, 150000),
        ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'][rand(0, 4)],
    ]);
    
    if ($i % 10000 === 0) {
        echo "  Written {$i} rows...\n";
    }
}

fclose($handle);

$size = filesize($filename);
echo "✓ Generated " . round($size / 1024 / 1024, 2) . " MB file\n";
echo "✓ File: {$filename}\n";
```

**3. Create streaming examples:**

```php
# filename: examples/streaming-csv.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Streaming\StreamingCSVReader;
use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();
$reader = new StreamingCSVReader();
$filename = __DIR__ . '/../data/large_dataset.csv';

echo "=== Streaming CSV Processing ===\n\n";

// 1. Count rows without loading into memory
echo "1. Counting rows (streaming):\n";
$count = $reader->count($filename);
echo "   Total rows: " . number_format($count) . "\n";
$monitor->report("   ");
echo "\n";

// 2. Filter high earners
echo "2. Finding high earners (salary > 100k):\n";
$highEarners = 0;

foreach ($reader->filter($filename, fn($row) => $row['salary'] > 100000) as $row) {
    $highEarners++;
}

echo "   High earners: " . number_format($highEarners) . "\n";
$monitor->report("   ");
echo "\n";

// 3. Calculate salary statistics
echo "3. Calculating salary statistics (streaming):\n";
$stats = $reader->calculateStats($filename, 'salary');

echo "   Mean: $" . number_format($stats['mean'], 2) . "\n";
echo "   Min: $" . number_format($stats['min'], 2) . "\n";
echo "   Max: $" . number_format($stats['max'], 2) . "\n";
echo "   Std Dev: $" . number_format($stats['std_dev'], 2) . "\n";
$monitor->report("   ");
echo "\n";

// 4. Transform data while streaming
echo "4. Transforming data (add bonus field):\n";
$processed = 0;

foreach ($reader->transform($filename, function($row) {
    $row['bonus'] = $row['salary'] * 0.1;
    return $row;
}) as $row) {
    $processed++;
    
    if ($processed >= 5) {
        break; // Just show first 5
    }
    
    echo "   {$row['name']}: Salary \${$row['salary']}, Bonus \${$row['bonus']}\n";
}

$monitor->report("   ");
echo "\n";

// 5. Process in chunks
echo "5. Processing in chunks (1000 rows each):\n";
$chunkCount = 0;

foreach ($reader->readChunks($filename, chunkSize: 1000) as $chunk) {
    $chunkCount++;
    // Process chunk here
}

echo "   Processed {$chunkCount} chunks\n";
$monitor->report("   ");
echo "\n";

echo "✓ All operations completed with minimal memory usage!\n";
$monitor->report("Final");
```

### Expected Result

```
=== Streaming CSV Processing ===

1. Counting rows (streaming):
   Total rows: 100,000
   Memory: 2.18 MB (Peak: 2.18 MB, Delta: 0.05 MB, Time: 0.234s)

2. Finding high earners (salary > 100k):
   High earners: 27,842
   Memory: 2.18 MB (Peak: 2.18 MB, Delta: 0.05 MB, Time: 0.478s)

3. Calculating salary statistics (streaming):
   Mean: $89,987.45
   Min: $30,000.00
   Max: $150,000.00
   Std Dev: $34,652.12
   Memory: 2.18 MB (Peak: 2.18 MB, Delta: 0.05 MB, Time: 0.723s)

4. Transforming data (add bonus field):
   Employee 1: Salary $87234, Bonus $8723.4
   Employee 2: Salary $45678, Bonus $4567.8
   Employee 3: Salary $123456, Bonus $12345.6
   Employee 4: Salary $67890, Bonus $6789.0
   Employee 5: Salary $98765, Bonus $9876.5
   Memory: 2.18 MB (Peak: 2.18 MB, Delta: 0.05 MB, Time: 0.725s)

5. Processing in chunks (1000 rows each):
   Processed 100 chunks
   Memory: 2.18 MB (Peak: 2.18 MB, Delta: 0.05 MB, Time: 0.956s)

✓ All operations completed with minimal memory usage!
Final: Memory: 2.18 MB (Peak: 2.18 MB, Delta: 0.05 MB, Time: 0.957s)
```

### Why It Works

**Generators**: The `yield` keyword creates a generator that produces values one at a time. Memory usage stays constant because only one row is in memory at any moment.

**Streaming**: Files are read line-by-line using `fgetcsv()`, never loading the entire file.

**Lazy Evaluation**: Data is only processed when requested, allowing efficient filtering and transformation.

**Key Insight**: You can process files larger than your available RAM because you never load the entire file—just one line at a time.

### Troubleshooting

**Error: "File not found"**

**Cause**: CSV file doesn't exist.

**Solution**: Generate the sample file first:

```bash
php examples/generate-large-csv.php
```

**Problem: Statistics calculation uses too much memory**

**Cause**: Storing all values for variance calculation.

**Solution**: Use online algorithms (calculate variance in one pass):

```php
// Two-pass variance (stores values)
$values = [];
foreach ($data as $value) {
    $values[] = $value;
}
$variance = calculateVariance($values);

// ✅ One-pass variance (Welford's algorithm)
$count = 0;
$mean = 0;
$M2 = 0;

foreach ($data as $value) {
    $count++;
    $delta = $value - $mean;
    $mean += $delta / $count;
    $M2 += $delta * ($value - $mean);
}

$variance = $M2 / $count;
```

**Problem: Generator is slower than array**

**Cause**: Generators have slight overhead.

**Solution**: This is expected—you're trading speed for memory. For small datasets, arrays are fine. For large datasets, generators are necessary.

## Step 3: Chunked Database Processing (~20 min)

### Goal

Process millions of database rows efficiently using pagination and batch processing.

### Actions

**1. Create a chunked database processor:**

```php
# filename: src/Database/ChunkedProcessor.php
<?php

declare(strict_types=1);

namespace DataScience\Database;

use PDO;
use Generator;

class ChunkedProcessor
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Process database results in chunks
     */
    public function processInChunks(
        string $table,
        int $chunkSize = 1000,
        ?string $orderBy = 'id',
        ?string $where = null
    ): Generator {
        $offset = 0;
        
        while (true) {
            $sql = "SELECT * FROM {$table}";
            
            if ($where !== null) {
                $sql .= " WHERE {$where}";
            }
            
            if ($orderBy !== null) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            $sql .= " LIMIT {$chunkSize} OFFSET {$offset}";
            
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($rows)) {
                break;
            }
            
            yield $rows;
            
            $offset += $chunkSize;
            
            // Free memory
            unset($rows, $stmt);
        }
    }
    
    /**
     * Process using cursor (more efficient for large datasets)
     */
    public function processWithCursor(
        string $sql,
        array $params = []
    ): Generator {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // Fetch one row at a time
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
        
        $stmt->closeCursor();
    }
    
    /**
     * Batch insert records efficiently
     */
    public function batchInsert(
        string $table,
        array $columns,
        Generator $dataGenerator,
        int $batchSize = 1000
    ): int {
        $batch = [];
        $totalInserted = 0;
        
        foreach ($dataGenerator as $row) {
            $batch[] = $row;
            
            if (count($batch) >= $batchSize) {
                $inserted = $this->insertBatch($table, $columns, $batch);
                $totalInserted += $inserted;
                $batch = [];
            }
        }
        
        // Insert remaining rows
        if (!empty($batch)) {
            $inserted = $this->insertBatch($table, $columns, $batch);
            $totalInserted += $inserted;
        }
        
        return $totalInserted;
    }
    
    /**
     * Insert a batch of records
     */
    private function insertBatch(
        string $table,
        array $columns,
        array $rows
    ): int {
        if (empty($rows)) {
            return 0;
        }
        
        $columnList = implode(', ', $columns);
        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $values = implode(', ', array_fill(0, count($rows), $placeholders));
        
        $sql = "INSERT INTO {$table} ({$columnList}) VALUES {$values}";
        
        // Flatten row data
        $params = [];
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $params[] = $row[$column] ?? null;
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Aggregate data while streaming
     */
    public function aggregate(
        string $sql,
        callable $aggregator,
        mixed $initial = null
    ): mixed {
        $result = $initial;
        
        foreach ($this->processWithCursor($sql) as $row) {
            $result = $aggregator($result, $row);
        }
        
        return $result;
    }
    
    /**
     * Count rows efficiently
     */
    public function count(string $table, ?string $where = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        
        if ($where !== null) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['count'];
    }
}
```

**2. Create database examples:**

```php
# filename: examples/chunked-database.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Database\ChunkedProcessor;
use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();

echo "=== Chunked Database Processing ===\n\n";

// Setup SQLite database
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create sample table
$pdo->exec('
    CREATE TABLE users (
        id INTEGER PRIMARY KEY,
        name TEXT,
        email TEXT,
        age INTEGER,
        salary REAL,
        department TEXT
    )
');

// Insert sample data
echo "Inserting 50,000 sample records...\n";
$stmt = $pdo->prepare('
    INSERT INTO users (name, email, age, salary, department)
    VALUES (?, ?, ?, ?, ?)
');

for ($i = 1; $i <= 50000; $i++) {
    $stmt->execute([
        'User ' . $i,
        "user{$i}@example.com",
        rand(22, 65),
        rand(30000, 150000),
        ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'][rand(0, 4)],
    ]);
}

echo "✓ Sample data inserted\n\n";
$monitor->report("After insert");

$processor = new ChunkedProcessor($pdo);

// 1. Process in chunks
echo "\n1. Processing in chunks (1000 rows each):\n";
$chunkCount = 0;
$totalProcessed = 0;

foreach ($processor->processInChunks('users', chunkSize: 1000) as $chunk) {
    $chunkCount++;
    $totalProcessed += count($chunk);
    
    // Process chunk here
    if ($chunkCount <= 3) {
        echo "   Chunk {$chunkCount}: " . count($chunk) . " rows\n";
    }
}

echo "   Total: {$chunkCount} chunks, {$totalProcessed} rows\n";
$monitor->report("   ");

// 2. Process with cursor (one row at a time)
echo "\n2. Processing with cursor:\n";
$highEarners = 0;

foreach ($processor->processWithCursor('SELECT * FROM users WHERE salary > 100000') as $row) {
    $highEarners++;
}

echo "   High earners: " . number_format($highEarners) . "\n";
$monitor->report("   ");

// 3. Aggregate while streaming
echo "\n3. Calculating average salary (streaming):\n";

$result = $processor->aggregate(
    'SELECT salary FROM users',
    function($acc, $row) {
        $acc['sum'] += $row['salary'];
        $acc['count']++;
        return $acc;
    },
    ['sum' => 0, 'count' => 0]
);

$avgSalary = $result['sum'] / $result['count'];
echo "   Average salary: $" . number_format($avgSalary, 2) . "\n";
$monitor->report("   ");

// 4. Count efficiently
echo "\n4. Counting records:\n";
$total = $processor->count('users');
$engineering = $processor->count('users', "department = 'Engineering'");

echo "   Total users: " . number_format($total) . "\n";
echo "   Engineering: " . number_format($engineering) . "\n";
$monitor->report("   ");

echo "\n✓ All database operations completed with minimal memory!\n";
$monitor->report("Final");
```

### Expected Result

```
=== Chunked Database Processing ===

Inserting 50,000 sample records...
✓ Sample data inserted

After insert: Memory: 4.52 MB (Peak: 4.52 MB, Delta: 2.39 MB, Time: 1.234s)

1. Processing in chunks (1000 rows each):
   Chunk 1: 1000 rows
   Chunk 2: 1000 rows
   Chunk 3: 1000 rows
   Total: 50 chunks, 50000 rows
   Memory: 4.53 MB (Peak: 4.53 MB, Delta: 2.40 MB, Time: 1.456s)

2. Processing with cursor:
   High earners: 13,921
   Memory: 4.53 MB (Peak: 4.53 MB, Delta: 2.40 MB, Time: 1.567s)

3. Calculating average salary (streaming):
   Average salary: $89,987.45
   Memory: 4.53 MB (Peak: 4.53 MB, Delta: 2.40 MB, Time: 1.678s)

4. Counting records:
   Total users: 50,000
   Engineering: 10,234
   Memory: 4.53 MB (Peak: 4.53 MB, Delta: 2.40 MB, Time: 1.723s)

✓ All database operations completed with minimal memory!
Final: Memory: 4.53 MB (Peak: 4.53 MB, Delta: 2.40 MB, Time: 1.724s)
```

### Why It Works

**Chunking**: `LIMIT` and `OFFSET` load only a subset of rows at a time.

**Cursors**: `fetch()` retrieves one row at a time instead of `fetchAll()` which loads everything.

**Batch Inserts**: Multiple rows inserted in one query reduces overhead.

**Aggregation**: Calculate statistics while streaming without storing all rows.

### Troubleshooting

**Problem: OFFSET becomes slow for large offsets**

**Cause**: Database must scan all previous rows.

**Solution**: Use keyset pagination (WHERE id > last_id):

```php
// ❌ SLOW for large offsets
SELECT * FROM users ORDER BY id LIMIT 1000 OFFSET 1000000;

// ✅ FAST: Keyset pagination
SELECT * FROM users WHERE id > 1000000 ORDER BY id LIMIT 1000;
```

**Problem: Memory still grows with chunks**

**Cause**: Not freeing chunk memory between iterations.

**Solution**: Explicitly unset variables:

```php
foreach ($processor->processInChunks('users', 1000) as $chunk) {
    processChunk($chunk);
    unset($chunk); // Free memory immediately
}
```

## Step 4: Building Memory-Efficient Pipelines (~20 min)

### Goal

Create complete data processing pipelines that handle large datasets efficiently.

### Actions

**1. Create a lazy data pipeline:**

```php
# filename: src/Pipeline/LazyPipeline.php
<?php

declare(strict_types=1);

namespace DataScience\Pipeline;

use Generator;

class LazyPipeline
{
    private Generator $source;
    private array $operations = [];
    
    /**
     * Create pipeline from generator source
     */
    public static function from(Generator $source): self
    {
        $pipeline = new self();
        $pipeline->source = $source;
        return $pipeline;
    }
    
    /**
     * Filter items
     */
    public function filter(callable $predicate): self
    {
        $this->operations[] = function(Generator $source) use ($predicate): Generator {
            foreach ($source as $key => $item) {
                if ($predicate($item)) {
                    yield $key => $item;
                }
            }
        };
        
        return $this;
    }
    
    /**
     * Transform items
     */
    public function map(callable $transformer): self
    {
        $this->operations[] = function(Generator $source) use ($transformer): Generator {
            foreach ($source as $key => $item) {
                yield $key => $transformer($item);
            }
        };
        
        return $this;
    }
    
    /**
     * Take first N items
     */
    public function take(int $limit): self
    {
        $this->operations[] = function(Generator $source) use ($limit): Generator {
            $count = 0;
            
            foreach ($source as $key => $item) {
                if ($count >= $limit) {
                    break;
                }
                
                yield $key => $item;
                $count++;
            }
        };
        
        return $this;
    }
    
    /**
     * Skip first N items
     */
    public function skip(int $offset): self
    {
        $this->operations[] = function(Generator $source) use ($offset): Generator {
            $count = 0;
            
            foreach ($source as $key => $item) {
                $count++;
                
                if ($count <= $offset) {
                    continue;
                }
                
                yield $key => $item;
            }
        };
        
        return $this;
    }
    
    /**
     * Group items by key
     */
    public function groupBy(callable $keySelector): self
    {
        $this->operations[] = function(Generator $source) use ($keySelector): Generator {
            $groups = [];
            
            foreach ($source as $item) {
                $key = $keySelector($item);
                
                if (!isset($groups[$key])) {
                    $groups[$key] = [];
                }
                
                $groups[$key][] = $item;
            }
            
            foreach ($groups as $key => $items) {
                yield $key => $items;
            }
        };
        
        return $this;
    }
    
    /**
     * Reduce to single value
     */
    public function reduce(callable $reducer, mixed $initial = null): mixed
    {
        $result = $initial;
        
        foreach ($this->execute() as $item) {
            $result = $reducer($result, $item);
        }
        
        return $result;
    }
    
    /**
     * Collect to array
     */
    public function toArray(): array
    {
        return iterator_to_array($this->execute());
    }
    
    /**
     * Count items
     */
    public function count(): int
    {
        $count = 0;
        
        foreach ($this->execute() as $item) {
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Execute pipeline and return generator
     */
    public function execute(): Generator
    {
        $current = $this->source;
        
        foreach ($this->operations as $operation) {
            $current = $operation($current);
        }
        
        return $current;
    }
    
    /**
     * Iterate over results
     */
    public function each(callable $callback): void
    {
        foreach ($this->execute() as $key => $item) {
            $callback($item, $key);
        }
    }
}
```

**2. Create pipeline examples:**

```php
# filename: examples/lazy-pipeline.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Pipeline\LazyPipeline;
use DataScience\Streaming\StreamingCSVReader;
use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();
$reader = new StreamingCSVReader();
$filename = __DIR__ . '/../data/large_dataset.csv';

echo "=== Lazy Data Pipeline ===\n\n";

// 1. Filter, transform, and limit
echo "1. Find top 5 high earners in Engineering:\n";

$topEarners = LazyPipeline::from($reader->readFile($filename))
    ->filter(fn($row) => $row['department'] === 'Engineering')
    ->filter(fn($row) => $row['salary'] > 100000)
    ->map(fn($row) => [
        'name' => $row['name'],
        'salary' => $row['salary'],
        'bonus' => $row['salary'] * 0.15,
    ])
    ->take(5)
    ->toArray();

foreach ($topEarners as $employee) {
    echo "   {$employee['name']}: \${$employee['salary']} (Bonus: \${$employee['bonus']})\n";
}

$monitor->report("   ");
echo "\n";

// 2. Count by department
echo "2. Count employees by department:\n";

$counts = LazyPipeline::from($reader->readFile($filename))
    ->reduce(function($acc, $row) {
        $dept = $row['department'];
        $acc[$dept] = ($acc[$dept] ?? 0) + 1;
        return $acc;
    }, []);

foreach ($counts as $dept => $count) {
    echo "   {$dept}: " . number_format($count) . "\n";
}

$monitor->report("   ");
echo "\n";

// 3. Calculate average salary by age group
echo "3. Average salary by age group:\n";

$ageGroups = LazyPipeline::from($reader->readFile($filename))
    ->map(function($row) {
        $ageGroup = floor($row['age'] / 10) * 10;
        return [
            'age_group' => "{$ageGroup}s",
            'salary' => $row['salary'],
        ];
    })
    ->reduce(function($acc, $row) {
        $group = $row['age_group'];
        
        if (!isset($acc[$group])) {
            $acc[$group] = ['sum' => 0, 'count' => 0];
        }
        
        $acc[$group]['sum'] += $row['salary'];
        $acc[$group]['count']++;
        
        return $acc;
    }, []);

foreach ($ageGroups as $group => $data) {
    $avg = $data['sum'] / $data['count'];
    echo "   {$group}: $" . number_format($avg, 2) . " ({$data['count']} employees)\n";
}

$monitor->report("   ");
echo "\n";

// 4. Complex pipeline: filter, transform, aggregate
echo "4. Senior employees (age > 50) with high salaries:\n";

$seniorStats = LazyPipeline::from($reader->readFile($filename))
    ->filter(fn($row) => $row['age'] > 50)
    ->filter(fn($row) => $row['salary'] > 80000)
    ->reduce(function($acc, $row) {
        $acc['count']++;
        $acc['total_salary'] += $row['salary'];
        $acc['max_salary'] = max($acc['max_salary'], $row['salary']);
        $acc['min_salary'] = min($acc['min_salary'], $row['salary']);
        return $acc;
    }, [
        'count' => 0,
        'total_salary' => 0,
        'max_salary' => 0,
        'min_salary' => PHP_INT_MAX,
    ]);

echo "   Count: " . number_format($seniorStats['count']) . "\n";
echo "   Avg Salary: $" . number_format($seniorStats['total_salary'] / $seniorStats['count'], 2) . "\n";
echo "   Range: $" . number_format($seniorStats['min_salary']) . " - $" . number_format($seniorStats['max_salary']) . "\n";

$monitor->report("   ");
echo "\n";

echo "✓ All pipeline operations completed with minimal memory!\n";
$monitor->report("Final");
```

### Expected Result

```
=== Lazy Data Pipeline ===

1. Find top 5 high earners in Engineering:
   Employee 42: $145678 (Bonus: $21851.7)
   Employee 157: $142345 (Bonus: $21351.75)
   Employee 289: $138901 (Bonus: $20835.15)
   Employee 456: $135678 (Bonus: $20351.7)
   Employee 723: $132456 (Bonus: $19868.4)
   Memory: 2.19 MB (Peak: 2.19 MB, Delta: 0.06 MB, Time: 0.145s)

2. Count employees by department:
   Engineering: 20,123
   Sales: 19,876
   Marketing: 20,045
   HR: 19,987
   Finance: 19,969
   Memory: 2.19 MB (Peak: 2.19 MB, Delta: 0.06 MB, Time: 0.389s)

3. Average salary by age group:
   20s: $89,234.56 (15,234 employees)
   30s: $92,456.78 (25,678 employees)
   40s: $95,123.45 (28,901 employees)
   50s: $98,765.43 (20,456 employees)
   60s: $102,345.67 (9,731 employees)
   Memory: 2.19 MB (Peak: 2.19 MB, Delta: 0.06 MB, Time: 0.634s)

4. Senior employees (age > 50) with high salaries:
   Count: 18,234
   Avg Salary: $105,678.90
   Range: $80,001 - $150,000
   Memory: 2.19 MB (Peak: 2.19 MB, Delta: 0.06 MB, Time: 0.878s)

✓ All pipeline operations completed with minimal memory!
Final: Memory: 2.19 MB (Peak: 2.19 MB, Delta: 0.06 MB, Time: 0.879s)
```

### Why It Works

**Lazy Evaluation**: Operations are chained but not executed until `execute()`, `toArray()`, or `reduce()` is called.

**Composability**: Each operation returns `$this`, allowing method chaining.

**Memory Efficiency**: Data flows through the pipeline one item at a time—never storing the entire dataset.

**Flexibility**: Combine filtering, transformation, and aggregation in any order.

## Step 5: Memory Profiling and Optimization (~15 min)

### Goal

Learn to profile memory usage, identify bottlenecks, and optimize memory-intensive operations.

### Built-in Memory Functions

PHP provides several functions for memory profiling:

```php
# filename: examples/memory-profiling.php
<?php

// Get current memory usage
$current = memory_get_usage();      // Actual memory used by PHP
$currentReal = memory_get_usage(true);  // Memory allocated from system

// Get peak memory usage
$peak = memory_get_peak_usage();
$peakReal = memory_get_peak_usage(true);

echo "Current: " . round($current / 1024 / 1024, 2) . " MB\n";
echo "Current (real): " . round($currentReal / 1024 / 1024, 2) . " MB\n";
echo "Peak: " . round($peak / 1024 / 1024, 2) . " MB\n";
echo "Peak (real): " . round($peakReal / 1024 / 1024, 2) . " MB\n";
```

### Finding Memory Leaks

Memory leaks occur when memory isn't freed after use:

```php
# filename: examples/detect-memory-leak.php
<?php

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();

echo "=== Memory Leak Detection ===\n\n";

// Simulate processing in loop
for ($i = 1; $i <= 10; $i++) {
    $data = [];
    
    // Process some data
    for ($j = 0; $j < 10000; $j++) {
        $data[] = ['id' => $j, 'value' => rand()];
    }
    
    // Do something with data
    $count = count($data);
    
    // ❌ BAD: Not freeing memory
    // Data accumulates in scope
    
    $monitor->report("Iteration {$i}");
}

echo "\nNotice: Memory keeps growing!\n";
echo "Solution: unset(\$data) at end of loop\n";
```

**Fixed version:**

```php
# filename: examples/no-memory-leak.php
<?php

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();

echo "=== Fixed: No Memory Leak ===\n\n";

for ($i = 1; $i <= 10; $i++) {
    $data = [];
    
    for ($j = 0; $j < 10000; $j++) {
        $data[] = ['id' => $j, 'value' => rand()];
    }
    
    $count = count($data);
    
    // ✅ GOOD: Free memory explicitly
    unset($data);
    
    // Force garbage collection if needed
    if ($i % 5 === 0) {
        gc_collect_cycles();
    }
    
    $monitor->report("Iteration {$i}");
}

echo "\nMemory stays constant!\n";
```

### Using Xdebug for Profiling

Xdebug provides detailed memory profiling:

**1. Enable Xdebug profiling:**

```ini
# php.ini
zend_extension=xdebug.so
xdebug.mode=profile
xdebug.output_dir=/tmp/xdebug
xdebug.profiler_output_name=cachegrind.out.%p
```

**2. Run your script:**

```bash
php your-script.php
```

**3. Analyze with tools:**

```bash
# Use kcachegrind (Linux) or qcachegrind (macOS)
qcachegrind /tmp/xdebug/cachegrind.out.12345

# Or use webgrind (web-based)
```

### Using Blackfire.io

Blackfire provides professional profiling (free tier available):

**1. Install Blackfire:**

```bash
# Install Blackfire agent and probe
# See: https://blackfire.io/docs/up-and-running/installation
```

**2. Profile your script:**

```bash
blackfire run php your-script.php
```

**3. View results in dashboard:**

Blackfire shows:
- Memory usage timeline
- Function call graphs
- Bottleneck identification
- Performance recommendations

### Custom Profiling Wrapper

Create a simple profiler for critical sections:

```php
# filename: examples/profiling-wrapper.php
<?php

class Profiler
{
    private array $checkpoints = [];
    
    public function checkpoint(string $label): void
    {
        $this->checkpoints[] = [
            'label' => $label,
            'time' => microtime(true),
            'memory' => memory_get_usage(),
            'peak' => memory_get_peak_usage(),
        ];
    }
    
    public function report(): void
    {
        echo "=== Profiling Report ===\n\n";
        
        $start = $this->checkpoints[0];
        
        foreach ($this->checkpoints as $i => $checkpoint) {
            $timeDelta = $checkpoint['time'] - $start['time'];
            $memoryDelta = $checkpoint['memory'] - $start['memory'];
            
            echo sprintf(
                "%s: +%.3fs, %s MB (+%s MB)\n",
                $checkpoint['label'],
                $timeDelta,
                round($checkpoint['memory'] / 1024 / 1024, 2),
                round($memoryDelta / 1024 / 1024, 2)
            );
        }
    }
}

// Usage
$profiler = new Profiler();

$profiler->checkpoint('Start');

// Operation 1
$data = range(1, 100000);
$profiler->checkpoint('Created array');

// Operation 2
$squared = array_map(fn($x) => $x ** 2, $data);
$profiler->checkpoint('Mapped array');

// Operation 3
$filtered = array_filter($squared, fn($x) => $x > 50000);
$profiler->checkpoint('Filtered array');

$profiler->report();
```

### Optimization Strategies

**1. Identify the bottleneck:**

```php
// Profile each section
$profiler->checkpoint('Before read');
$reader->readFile($file);
$profiler->checkpoint('After read');  // Is this slow?

$profiler->checkpoint('Before process');
processData($data);
$profiler->checkpoint('After process');  // Or is this slow?
```

**2. Optimize the critical path:**

```php
// If reading is slow: Use streaming
// If processing is slow: Use chunking
// If both are slow: Combine techniques
```

**3. Validate improvements:**

```php
// Before optimization
$monitor1 = new MemoryMonitor();
// ... old code ...
$monitor1->report("Old approach");

// After optimization
$monitor2 = new MemoryMonitor();
// ... new code ...
$monitor2->report("New approach");

// Compare results
```

### Actions

Create a comprehensive profiling script:

```php
# filename: examples/comprehensive-profiling.php
<?php

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Memory\MemoryMonitor;
use DataScience\Streaming\StreamingCSVReader;

$monitor = new MemoryMonitor();
$reader = new StreamingCSVReader();

echo "=== Comprehensive Profiling ===\n\n";

// Test: Count rows in large file
echo "Test 1: Count rows\n";
$start = microtime(true);
$monitor1 = new MemoryMonitor();

$count = $reader->count(__DIR__ . '/../data/large_dataset.csv');

$elapsed = microtime(true) - $start;
echo "  Rows: " . number_format($count) . "\n";
echo "  Time: " . round($elapsed, 3) . "s\n";
$monitor1->report("  ");
echo "\n";

// Test: Calculate statistics
echo "Test 2: Calculate statistics\n";
$start = microtime(true);
$monitor2 = new MemoryMonitor();

$stats = $reader->calculateStats(__DIR__ . '/../data/large_dataset.csv', 'salary');

$elapsed = microtime(true) - $start;
echo "  Mean: $" . number_format($stats['mean'], 2) . "\n";
echo "  Time: " . round($elapsed, 3) . "s\n";
$monitor2->report("  ");
echo "\n";

// Test: Filter and count
echo "Test 3: Filter and count\n";
$start = microtime(true);
$monitor3 = new MemoryMonitor();

$filtered = 0;
foreach ($reader->filter(__DIR__ . '/../data/large_dataset.csv', fn($r) => $r['salary'] > 100000) as $row) {
    $filtered++;
}

$elapsed = microtime(true) - $start;
echo "  Filtered: " . number_format($filtered) . "\n";
echo "  Time: " . round($elapsed, 3) . "s\n";
$monitor3->report("  ");

echo "\n✓ Profiling complete!\n";
```

### Why It Works

**Profiling reveals**: Where your code spends time and memory.

**Optimization targets**: Focus on the 20% of code causing 80% of issues.

**Validation proves**: Your optimizations actually work.

**Key Insight**: You can't optimize what you don't measure. Always profile before optimizing.

## Step 6: Advanced Memory Patterns (~15 min)

### Goal

Learn advanced techniques for extreme memory efficiency: temporary files, database optimizations, and hybrid approaches.

### Temporary File Handling

When data doesn't fit in memory, use disk as overflow:

```php
# filename: examples/temp-file-sorting.php
<?php

/**
 * External sort using temporary files
 */
class TempFileSorter
{
    public function sort(array $data, int $chunkSize = 10000): array
    {
        $chunks = array_chunk($data, $chunkSize);
        $tempFiles = [];
        
        // Sort each chunk and write to temp file
        foreach ($chunks as $i => $chunk) {
            sort($chunk);
            
            $tempFile = tmpfile();
            $tempFiles[] = $tempFile;
            
            foreach ($chunk as $value) {
                fwrite($tempFile, $value . "\n");
            }
            
            rewind($tempFile);
        }
        
        // Merge sorted chunks
        return $this->mergeFiles($tempFiles);
    }
    
    private function mergeFiles(array $files): array
    {
        $result = [];
        $current = [];
        
        // Read first line from each file
        foreach ($files as $i => $file) {
            $line = fgets($file);
            if ($line !== false) {
                $current[$i] = trim($line);
            }
        }
        
        // Merge
        while (!empty($current)) {
            $min = min($current);
            $minKey = array_search($min, $current);
            
            $result[] = $min;
            
            // Read next line from that file
            $line = fgets($files[$minKey]);
            if ($line !== false) {
                $current[$minKey] = trim($line);
            } else {
                unset($current[$minKey]);
            }
        }
        
        // Cleanup
        foreach ($files as $file) {
            fclose($file);
        }
        
        return $result;
    }
}

// Usage
$sorter = new TempFileSorter();
$unsorted = [5, 2, 8, 1, 9, 3, 7, 4, 6];
$sorted = $sorter->sort($unsorted, chunkSize: 3);

echo "Sorted: " . implode(', ', $sorted) . "\n";
```

### Database Optimizations

**1. PDO::FETCH_LAZY mode:**

```php
# filename: examples/fetch-lazy.php
<?php

$pdo = new PDO('sqlite::memory:');

// ❌ Standard fetch - loads row into memory
$stmt = $pdo->query('SELECT * FROM large_table');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Row is in memory
    process($row);
}

// ✅ Lazy fetch - minimal memory footprint
$stmt = $pdo->query('SELECT * FROM large_table');
while ($row = $stmt->fetch(PDO::FETCH_LAZY)) {
    // Row data stays in buffer
    process($row);
}
```

**2. Keyset pagination (cursor-based):**

```php
# filename: examples/keyset-pagination.php
<?php

/**
 * Keyset pagination - faster than OFFSET for large offsets
 */
function keysetPagination(PDO $pdo, int $chunkSize = 1000): Generator
{
    $lastId = 0;
    
    while (true) {
        // ✅ FAST: Uses index, no full table scan
        $stmt = $pdo->prepare('
            SELECT * FROM users 
            WHERE id > :lastId 
            ORDER BY id 
            LIMIT :limit
        ');
        
        $stmt->bindValue(':lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $chunkSize, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            break;
        }
        
        yield $rows;
        
        $lastId = end($rows)['id'];
    }
}

// Usage
foreach (keysetPagination($pdo, 1000) as $chunk) {
    foreach ($chunk as $user) {
        processUser($user);
    }
}
```

**3. Index optimization:**

```php
# filename: examples/index-optimization.sql
-- ❌ BAD: No index on column used for ORDER BY
SELECT * FROM users ORDER BY created_at LIMIT 1000 OFFSET 1000000;
-- Slow: Must scan all rows

-- ✅ GOOD: Add index on ORDER BY column
CREATE INDEX idx_users_created_at ON users(created_at);
SELECT * FROM users ORDER BY created_at LIMIT 1000 OFFSET 1000000;
-- Faster: Uses index

-- ✅ BETTER: Use keyset pagination with indexed column
SELECT * FROM users 
WHERE created_at > '2024-01-01' 
ORDER BY created_at 
LIMIT 1000;
-- Fastest: Uses index, no offset scan
```

### Stream Wrappers

PHP stream wrappers provide memory-efficient I/O:

```php
# filename: examples/stream-wrappers.php
<?php

// Read from compressed file
$handle = fopen('compress.zlib://large-file.gz', 'r');
while (($line = fgets($handle)) !== false) {
    processLine($line);
}
fclose($handle);

// Read from HTTP stream
$handle = fopen('https://example.com/large-data.csv', 'r');
while (($line = fgets($handle)) !== false) {
    processLine($line);
}
fclose($handle);

// Custom stream filter
stream_filter_register('uppercase', UppercaseFilter::class);
$handle = fopen('data.txt', 'r');
stream_filter_append($handle, 'uppercase');
// Data is transformed as it's read
```

### Hybrid Approaches

Combine multiple techniques for maximum efficiency:

```php
# filename: examples/hybrid-approach.php
<?php

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Streaming\StreamingCSVReader;
use DataScience\Pipeline\LazyPipeline;

/**
 * Process large CSV, aggregate by groups, write results
 */
function hybridProcess(string $inputFile, string $outputFile): void
{
    $reader = new StreamingCSVReader();
    
    // Step 1: Stream input, filter, and group in memory (only groups, not all data)
    $groups = LazyPipeline::from($reader->readFile($inputFile))
        ->filter(fn($row) => $row['salary'] > 50000)
        ->reduce(function($acc, $row) {
            $dept = $row['department'];
            
            if (!isset($acc[$dept])) {
                $acc[$dept] = ['count' => 0, 'total_salary' => 0];
            }
            
            $acc[$dept]['count']++;
            $acc[$dept]['total_salary'] += $row['salary'];
            
            return $acc;
        }, []);
    
    // Step 2: Write aggregated results (small dataset)
    $output = fopen($outputFile, 'w');
    fputcsv($output, ['department', 'count', 'average_salary']);
    
    foreach ($groups as $dept => $stats) {
        fputcsv($output, [
            $dept,
            $stats['count'],
            round($stats['total_salary'] / $stats['count'], 2),
        ]);
    }
    
    fclose($output);
}

// Usage: Process 100K rows → 5 department aggregates
hybridProcess(
    __DIR__ . '/../data/large_dataset.csv',
    __DIR__ . '/../data/department_stats.csv'
);

echo "✓ Hybrid processing complete!\n";
echo "Processed large input, produced small output\n";
```

### Decision Tree

**When to use each approach:**

**Small datasets (<10K rows):**
- Load into array directly (simple and fast)

**Medium datasets (10K-1M rows):**
- Need multiple passes? 
  - Yes, fits in memory? → Load into array
  - Yes, doesn't fit? → Use temp files
  - No (single pass)? → Database: keyset pagination, File: stream with generators

**Large datasets (>1M rows):**
- Stream/Chunk approach required
- Sequential access? → Stream with generators
- Random access or sorting? → External sort with temp files

### Performance Checklist

Use this checklist for production code:

- [ ] **Use streaming** for files > 10 MB
- [ ] **Use chunking** for database queries > 10K rows  
- [ ] **Free memory** with `unset()` in loops
- [ ] **Use keyset pagination** instead of OFFSET for large offsets
- [ ] **Add indexes** on columns used in ORDER BY and WHERE
- [ ] **Profile** with real data volumes
- [ ] **Monitor** memory usage in production
- [ ] **Set appropriate** memory_limit for your use case
- [ ] **Handle errors** gracefully (malformed data, connection issues)
- [ ] **Test edge cases** (empty files, single row, max size)

### Actions

Test the complete decision workflow:

```bash
# Generate test data
php examples/generate-large-csv.php

# Test each approach
php examples/hybrid-approach.php

# Verify results
cat data/department_stats.csv
```

### Why It Works

**Temporary Files**: Disk is slower than RAM, but unlimited in size.

**Database Optimization**: Proper indexes and pagination make huge difference.

**Hybrid Approaches**: Combine streaming input with in-memory aggregation when output is small.

**Key Insight**: Real-world problems often need multiple techniques combined intelligently.

## Comprehensive Troubleshooting Guide

### Common Problems and Solutions

#### Problem 1: "Allowed memory size of X bytes exhausted"

**Symptoms:**
```
Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 32768 bytes)
```

**Root Causes:**
1. Loading entire dataset into memory
2. Not freeing memory in loops
3. Accumulating data in global scope
4. Memory leaks from circular references

**Solutions:**

```php
// ❌ BAD: Loading everything
$data = file('huge.csv');  // CRASH!

// ✅ GOOD: Stream one line at a time
$handle = fopen('huge.csv', 'r');
while (($line = fgets($handle)) !== false) {
    processLine($line);
}
fclose($handle);

// ❌ BAD: Accumulating in loop
$results = [];
foreach ($source as $item) {
    $results[] = process($item);  // Grows forever
}

// ✅ GOOD: Process and discard
foreach ($source as $item) {
    $processed = process($item);
    output($processed);
    // Memory freed after each iteration
}
```

#### Problem 2: "Maximum execution time exceeded"

**Symptoms:**
```
Fatal error: Maximum execution time of 30 seconds exceeded
```

**Root Causes:**
1. Inefficient algorithms (O(n²) loops)
2. Database queries without indexes
3. Processing too much data
4. No progress indicators

**Solutions:**

```php
// For CLI scripts, remove time limit
if (PHP_SAPI === 'cli') {
    set_time_limit(0);
}

// Add progress indicators
foreach ($reader->readChunks($file, 1000) as $i => $chunk) {
    processChunk($chunk);
    
    if ($i % 10 === 0) {
        echo "Processed " . ($i * 1000) . " rows...\n";
    }
}

// Optimize database queries
// Before: No index, slow
SELECT * FROM users ORDER BY created_at LIMIT 1000 OFFSET 100000;

// After: Add index
CREATE INDEX idx_created_at ON users(created_at);
```

#### Problem 3: Memory Usage Grows Over Time

**Symptoms:**
- Script starts fine but memory grows continuously
- Eventually hits memory limit

**Root Causes:**
1. Not freeing variables in loops
2. Circular references preventing garbage collection
3. Global variables accumulating data
4. Unclosed resources (files, database connections)

**Solutions:**

```php
// ❌ BAD: Memory leak in loop
$globalCache = [];
foreach ($items as $item) {
    $globalCache[$item['id']] = $item;  // Grows forever
}

// ✅ GOOD: Process and free
foreach ($items as $item) {
    process($item);
    unset($item);  // Free immediately
    
    // Force GC periodically
    if ($count % 1000 === 0) {
        gc_collect_cycles();
    }
}

// ✅ GOOD: Limit cache size
$cache = [];
$maxCacheSize = 1000;

foreach ($items as $item) {
    $cache[$item['id']] = $item;
    
    // Evict oldest when full
    if (count($cache) > $maxCacheSize) {
        array_shift($cache);
    }
}
```

#### Problem 4: Slow Database Queries with Large Offsets

**Symptoms:**
- First few pages load quickly
- Later pages take longer and longer
- Timeouts on high page numbers

**Root Cause:**
OFFSET requires scanning all previous rows.

**Solution:**

```php
// ❌ SLOW: OFFSET-based pagination
// Page 1000: Must scan 1,000,000 rows!
SELECT * FROM users ORDER BY id LIMIT 1000 OFFSET 1000000;

// ✅ FAST: Keyset pagination
// Uses index, no full scan
SELECT * FROM users WHERE id > 1000000 ORDER BY id LIMIT 1000;
```

#### Problem 5: Generator vs Array Confusion

**Symptoms:**
- `count()` doesn't work on generators
- Can't rewind generators
- Unexpected behavior with `foreach`

**Understanding:**

```php
// Generators are NOT arrays!
$gen = generateData();

// ❌ WRONG: count() doesn't work
$count = count($gen);  // Always returns 1

// ✅ RIGHT: Manually count
$count = 0;
foreach ($gen as $item) {
    $count++;
}

// ❌ WRONG: Can't reuse generator
foreach ($gen as $item) { ... }  // Works
foreach ($gen as $item) { ... }  // Empty! Already consumed

// ✅ RIGHT: Create new generator
function getData(): Generator { ... }
foreach (getData() as $item) { ... }  // Works
foreach (getData() as $item) { ... }  // Works (new generator)
```

#### Problem 6: Statistics Calculation Using Too Much Memory

**Symptoms:**
- Calculating variance/std dev uses lots of memory
- Need two passes through data

**Root Cause:**
Standard variance calculation stores all values.

**Solution:**

```php
// ❌ BAD: Stores all values
$values = [];
foreach ($data as $value) {
    $values[] = $value;
}
$variance = calculateVariance($values);  // Memory intensive

// ✅ GOOD: Welford's online algorithm (single pass)
$count = 0;
$mean = 0;
$M2 = 0;

foreach ($data as $value) {
    $count++;
    $delta = $value - $mean;
    $mean += $delta / $count;
    $M2 += $delta * ($value - $mean);
}

$variance = $count > 1 ? $M2 / $count : 0;
$stdDev = sqrt($variance);
```

### Debugging Workflow

**1. Identify the bottleneck:**

```php
$monitor = new MemoryMonitor();

$monitor->checkpoint('Start');
// ... operation 1 ...
$monitor->checkpoint('After step 1');
// ... operation 2 ...
$monitor->checkpoint('After step 2');
// ... operation 3 ...
$monitor->checkpoint('After step 3');

// Which step uses most memory/time?
```

**2. Profile the critical section:**

```bash
# Use Xdebug
php -d xdebug.mode=profile script.php

# Or Blackfire
blackfire run php script.php
```

**3. Test with different data sizes:**

```php
// Test scaling behavior
$sizes = [100, 1000, 10000, 100000];

foreach ($sizes as $size) {
    $monitor = new MemoryMonitor();
    $data = generateData($size);
    
    processData($data);
    
    echo "Size {$size}: ";
    $monitor->report();
}

// Memory should stay constant, not grow linearly
```

**4. Validate the fix:**

```php
// Before and after comparison
echo "BEFORE optimization:\n";
$monitor1 = new MemoryMonitor();
oldImplementation();
$monitor1->report();

echo "\nAFTER optimization:\n";
$monitor2 = new MemoryMonitor();
newImplementation();
$monitor2->report();

// Expect significant improvement
```

### Decision Matrix

Use this matrix to choose the right approach:

| Scenario | Best Approach | Why |
|----------|---------------|-----|
| File < 1 MB | `file()` or array | Fast, simple, fits in memory |
| File 1-100 MB | Streaming CSV reader | Constant memory, sequential access |
| File > 100 MB | Chunked processing + temp files | Balance speed and memory |
| Database < 10K rows | `fetchAll()` | Fast, simple |
| Database 10K-1M rows | Chunked with LIMIT/OFFSET | Manageable chunks |
| Database > 1M rows | Keyset pagination + cursor | Scalable, fast |
| Need sorting, large data | External merge sort | Disk-based, unlimited size |
| Real-time stream | Generators + rolling window | Fixed memory, continuous |
| Multiple passes needed | Load to array (if fits) or temp file | Allows random access |
| Single pass sufficient | Generator pipeline | Minimal memory, fast |

### Performance Anti-Patterns

**Anti-Pattern 1: Premature Loading**

```php
// ❌ Loading everything before checking
$all Data = loadAllData();
if (needsProcessing($criteria)) {
    process($allData);
}

// ✅ Filter before loading
if (needsProcessing($criteria)) {
    foreach (streamData() as $item) {
        if (matches($item, $criteria)) {
            process($item);
        }
    }
}
```

**Anti-Pattern 2: Ignoring Indexes**

```php
// ❌ Full table scan
SELECT * FROM orders WHERE customer_id = 123;  // Slow without index

// ✅ Add index
CREATE INDEX idx_customer_id ON orders(customer_id);
```

**Anti-Pattern 3: Building Intermediate Arrays**

```php
// ❌ Multiple intermediate arrays
$filtered = array_filter($data, $predicate);
$mapped = array_map($transformer, $filtered);
$result = array_slice($mapped, 0, 10);

// ✅ Pipeline processes on-the-fly
$result = LazyPipeline::from($dataSource)
    ->filter($predicate)
    ->map($transformer)
    ->take(10)
    ->toArray();
```

### Common Mistakes

1. **Using `file()` instead of `fopen()` for large files**
2. **Forgetting to `unset()` large variables**
3. **Using OFFSET for pagination beyond 10K rows**
4. **Not adding database indexes**
5. **Trying to `count()` a generator**
6. **Reusing a consumed generator**
7. **Building CSV from array instead of streaming output**
8. **Not profiling before optimizing**
9. **Increasing memory_limit instead of fixing code**
10. **Using inefficient algorithms (O(n²) when O(n) exists)**

### Quick Reference Card

**Problem**: Out of memory reading file
**Solution**: Use `StreamingCSVReader` or `fopen()`/`fgets()`

**Problem**: Out of memory with database query
**Solution**: Use `ChunkedProcessor` or `processWithCursor()`

**Problem**: Need to sort huge dataset
**Solution**: Use `ExternalSorter` with temp files

**Problem**: Slow pagination
**Solution**: Use keyset pagination instead of OFFSET

**Problem**: Memory grows in loop
**Solution**: Use `unset()` and `gc_collect_cycles()`

**Problem**: Need statistics on large dataset
**Solution**: Use online algorithms (Welford's for variance)

**Problem**: Multiple passes through data
**Solution**: Use temp files or increase chunk size

**Problem**: Generators not working as expected
**Solution**: Remember they're single-use, create new ones for each pass

## Exercises

### Exercise 1: Streaming JSON Processing

**Goal**: Process large JSON files without loading into memory.

```php
// Create a StreamingJSONReader that:
// - Reads JSON arrays line-by-line (NDJSON format)
// - Yields one object at a time
// - Handles malformed JSON gracefully
// - Calculates statistics while streaming

class StreamingJSONReader
{
    public function readFile(string $filename): Generator
    {
        // Your implementation here
    }
    
    public function filter(string $filename, callable $predicate): Generator
    {
        // Your implementation here
    }
}
```

**Validation**: Should process 100MB+ JSON files with <5MB memory usage.

### Exercise 2: Memory-Efficient Sorting

**Goal**: Sort large datasets that don't fit in memory.

```php
// Implement external merge sort:
// 1. Split large file into sorted chunks
// 2. Merge chunks using priority queue
// 3. Write sorted output

class ExternalSorter
{
    public function sort(
        string $inputFile,
        string $outputFile,
        int $chunkSize = 10000
    ): void {
        // Your implementation here
    }
}
```

**Validation**: Should sort 1M+ records with constant memory usage.

### Exercise 3: Real-Time Data Aggregation

**Goal**: Calculate rolling statistics on streaming data.

```php
// Implement rolling window aggregator:
// - Calculate moving average
// - Track min/max in window
// - Detect anomalies in real-time

class RollingAggregator
{
    public function __construct(private int $windowSize) {}
    
    public function process(Generator $stream): Generator
    {
        // Yield statistics for each window
        // Your implementation here
    }
}
```

**Validation**: Should handle infinite streams with fixed memory.

## Wrap-up


### What You've Accomplished

✅ Understood PHP memory management and limits  
✅ Used generators for memory-efficient iteration  
✅ Streamed large files without loading into memory  
✅ Processed database results in chunks  
✅ Built lazy evaluation pipelines  
✅ Profiled and optimized memory usage  
✅ Created scalable data processing systems  
✅ Handled datasets larger than available RAM  

### Key Concepts Learned

**Generators**: The `yield` keyword creates iterators that produce values on-demand, keeping memory constant regardless of dataset size.

**Streaming**: Process data one item at a time, never loading the entire dataset into memory.

**Chunking**: Break large operations into smaller batches, processing and freeing memory between chunks.

**Lazy Evaluation**: Chain operations without executing them until results are needed, allowing efficient pipelines.

**Memory Profiling**: Monitor memory usage to identify and fix memory leaks and inefficiencies.

### Real-World Applications

You can now:

- **Process log files** of any size for analysis
- **Import/export** millions of database records
- **Transform large datasets** without memory errors
- **Build ETL pipelines** that scale to production
- **Handle real-time streams** of incoming data
- **Aggregate statistics** on datasets larger than RAM

### Performance Comparison

| Approach | 100K Records | 1M Records | 10M Records |
|----------|--------------|------------|-------------|
| Load All | 45 MB | 450 MB | ❌ Out of Memory |
| Generators | 2 MB | 2 MB | 2 MB ✅ |
| Chunking | 5 MB | 5 MB | 5 MB ✅ |

### Connection to Data Science

Memory efficiency isn't just about avoiding errors—it's about scalability. The techniques you've learned allow you to process production-scale datasets on modest hardware. This is essential for real-world data science where datasets grow continuously.

In the next chapter, you'll apply these memory-efficient techniques to statistical analysis, learning the essential statistics every PHP developer needs for data science.

## Further Reading

- [PHP Generators Documentation](https://www.php.net/manual/en/language.generators.php) — Official generator reference
- [Memory Management in PHP](https://www.php.net/manual/en/features.gc.php) — How PHP manages memory
- [League CSV Documentation](https://csv.thephpleague.com/) — Professional CSV handling
- [External Sorting Algorithms](https://en.wikipedia.org/wiki/External_sorting) — Sorting data larger than memory
- [Stream Processing Patterns](https://www.oreilly.com/library/view/streaming-systems/9781491983867/) — Advanced streaming concepts

::: tip Next Chapter
Continue to [Chapter 07: Statistics Every PHP Developer Needs for Data Science](/series/data-science-php-developers/chapters/07-statistics-every-php-developer-needs-for-data-science) to learn essential statistical concepts!
:::
