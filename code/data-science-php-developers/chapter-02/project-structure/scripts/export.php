<?php

declare(strict_types=1);

/**
 * Export Script - Data Output Example
 * 
 * This script demonstrates exporting analysis results.
 */

require __DIR__ . '/../vendor/autoload.php';

use League\Csv\Reader;

echo "Data Export Script\n";
echo "==================\n\n";

// Read processed data
$inputPath = __DIR__ . '/../data/processed/cleaned-data.csv';
$reader = Reader::createFromPath($inputPath, 'r');
$reader->setHeaderOffset(0);

// Calculate summary statistics
$byCategory = [];

foreach ($reader as $record) {
    $cat = $record['category'];
    if (!isset($byCategory[$cat])) {
        $byCategory[$cat] = ['count' => 0, 'sum' => 0];
    }
    $byCategory[$cat]['count']++;
    $byCategory[$cat]['sum'] += (float) $record['value'];
}

// Generate report
$outputPath = __DIR__ . '/../output/reports/summary.txt';
$report = "Data Analysis Summary Report\n";
$report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($byCategory as $category => $stats) {
    $avg = $stats['sum'] / $stats['count'];
    $report .= sprintf(
        "Category %s: %d records, avg value: %.2f\n",
        $category,
        $stats['count'],
        $avg
    );
}

file_put_contents($outputPath, $report);

echo $report;
echo "\n✓ Report saved to: {$outputPath}\n";


