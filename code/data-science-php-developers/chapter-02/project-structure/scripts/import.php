<?php

declare(strict_types=1);

/**
 * Import Script - Data Collection Example
 * 
 * This script demonstrates importing data from various sources
 * into the data/raw/ directory.
 */

require __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;

echo "Data Import Script\n";
echo "==================\n\n";

// Example: Generate sample data
$data = [];
for ($i = 1; $i <= 100; $i++) {
    $data[] = [
        'id' => $i,
        'timestamp' => Carbon::now()->subDays(rand(1, 30))->toIso8601String(),
        'value' => rand(10, 100),
        'category' => ['A', 'B', 'C'][rand(0, 2)],
    ];
}

// Save to CSV
$outputPath = __DIR__ . '/../data/raw/sample-data.csv';
$handle = fopen($outputPath, 'w');

// Write header
fputcsv($handle, ['id', 'timestamp', 'value', 'category'], escape: "\\");

// Write data
foreach ($data as $row) {
    fputcsv($handle, $row, escape: "\\");
}

fclose($handle);

echo "✓ Imported " . count($data) . " records\n";
echo "✓ Saved to: {$outputPath}\n";

