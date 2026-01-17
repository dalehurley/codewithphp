<?php

# filename: examples/chunked-database.php

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
