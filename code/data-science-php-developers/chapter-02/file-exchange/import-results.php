<?php

declare(strict_types=1);

/**
 * File Exchange Example - PHP Import Results
 * 
 * Demonstrates reading Python-processed results.
 */

$resultsPath = __DIR__ . '/data-results.csv';

if (!file_exists($resultsPath)) {
    echo "❌ Results file not found. Run process-data.py first.\n";
    exit(1);
}

$handle = fopen($resultsPath, 'r');
$headers = fgetcsv($handle);

echo "Python Processing Results:\n";
echo "==========================\n\n";

while (($row = fgetcsv($handle)) !== false) {
    $category = $row[0];
    $mean = number_format((float) $row[1], 2);
    $count = $row[2];
    
    echo "Category {$category}: {$count} records, average value: {$mean}\n";
}

fclose($handle);

echo "\n✓ Results imported successfully\n";


