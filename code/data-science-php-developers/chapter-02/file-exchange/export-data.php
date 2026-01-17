<?php

declare(strict_types=1);

/**
 * File Exchange Example - PHP Export
 * 
 * Demonstrates exporting data for Python to process.
 */

// Create simple CSV without dependencies
$data = [
    ['id', 'value', 'category'],
    [1, 100, 'A'],
    [2, 200, 'B'],
    [3, 150, 'A'],
    [4, 300, 'B'],
    [5, 250, 'A'],
];

$outputPath = __DIR__ . '/data-export.csv';
$handle = fopen($outputPath, 'w');

foreach ($data as $row) {
    fputcsv($handle, $row, escape: "\\");
}

fclose($handle);

echo "✓ Exported data to {$outputPath}\n";
echo "Run: python3 process-data.py\n";

