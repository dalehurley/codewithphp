<?php

# filename: examples/memory-problem-demo.php

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
