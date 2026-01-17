<?php

declare(strict_types=1);

/**
 * Clean Script - Data Preprocessing Example
 * 
 * This script demonstrates cleaning and validating data.
 */

require __DIR__ . '/../vendor/autoload.php';

use League\Csv\Reader;
use League\Csv\Writer;

echo "Data Cleaning Script\n";
echo "====================\n\n";

// Read raw data
$inputPath = __DIR__ . '/../data/raw/sample-data.csv';
$reader = Reader::createFromPath($inputPath, 'r');
$reader->setHeaderOffset(0);

$cleaned = [];
$errors = 0;

foreach ($reader as $record) {
    // Validate and clean
    if (empty($record['id']) || empty($record['value'])) {
        $errors++;
        continue;
    }
    
    $cleaned[] = [
        'id' => (int) $record['id'],
        'timestamp' => $record['timestamp'],
        'value' => (float) $record['value'],
        'category' => strtoupper($record['category']),
    ];
}

// Save cleaned data
$outputPath = __DIR__ . '/../data/processed/cleaned-data.csv';
$writer = Writer::createFromPath($outputPath, 'w+');
$writer->insertOne(['id', 'timestamp', 'value', 'category']);
$writer->insertAll($cleaned);

echo "✓ Processed " . count($cleaned) . " records\n";
echo "✓ Skipped " . $errors . " invalid records\n";
echo "✓ Saved to: {$outputPath}\n";


