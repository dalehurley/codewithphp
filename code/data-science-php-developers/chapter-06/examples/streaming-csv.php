<?php

# filename: examples/streaming-csv.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Streaming\StreamingCSVReader;
use DataScience\Memory\MemoryMonitor;

$monitor = new MemoryMonitor();
$reader = new StreamingCSVReader();
$filename = __DIR__ . '/../data/large_dataset.csv';

if (!file_exists($filename)) {
    echo "Error: Please run generate-large-csv.php first to create sample data.\n";
    exit(1);
}

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
