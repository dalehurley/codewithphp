<?php

declare(strict_types=1);

/**
 * Chapter 04: Exercise 1 Solution
 * Load and Clean E-commerce Data
 * 
 * Goal: Practice complete data loading and cleaning workflow with median imputation
 */

/**
 * Calculate median of an array
 */
function calculateMedian(array $values): float
{
    sort($values);
    $count = count($values);
    $middle = (int)floor($count / 2);

    if ($count % 2 === 0) {
        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    return $values[$middle];
}

/**
 * Analyze missing values
 */
function analyzeMissingValues(array $data): array
{
    $missingCount = [];
    $totalRows = count($data);

    foreach ($data as $row) {
        foreach ($row as $column => $value) {
            if (!isset($missingCount[$column])) {
                $missingCount[$column] = 0;
            }
            if ($value === null || $value === '') {
                $missingCount[$column]++;
            }
        }
    }

    $report = [];
    foreach ($missingCount as $column => $count) {
        $report[$column] = [
            'missing_count' => $count,
            'missing_percentage' => round(($count / $totalRows) * 100, 2)
        ];
    }

    return $report;
}

/**
 * Fill missing numeric values with median
 */
function imputeMedian(array $data, string $column): array
{
    $values = array_filter(
        array_column($data, $column),
        fn($v) => $v !== null && $v !== '' && is_numeric($v)
    );

    if (empty($values)) {
        return $data;
    }

    $median = calculateMedian(array_map('floatval', $values));

    return array_map(function ($row) use ($column, $median) {
        if ($row[$column] === null || $row[$column] === '') {
            $row[$column] = $median;
        }
        return $row;
    }, $data);
}

/**
 * Fill missing categorical values with "Unknown"
 */
function imputeUnknown(array $data, string $column): array
{
    return array_map(function ($row) use ($column) {
        if ($row[$column] === null || $row[$column] === '') {
            $row[$column] = 'Unknown';
        }
        return $row;
    }, $data);
}

// Load customer data
$file = fopen(__DIR__ . '/../data/customers.csv', 'r');
if ($file === false) {
    echo "Error: Could not open customers.csv\n";
    exit(1);
}

$headers = fgetcsv($file, 0, ',', '"', '\\');
$customers = [];
while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
    $customers[] = array_combine($headers, $row);
}
fclose($file);

echo "→ Loaded " . count($customers) . " customers\n";

// Analyze missing values
$beforeReport = analyzeMissingValues($customers);
echo "\n→ Missing Value Analysis:\n";
foreach ($beforeReport as $column => $stats) {
    if ($stats['missing_count'] > 0) {
        echo "  - $column: {$stats['missing_count']} missing ({$stats['missing_percentage']}%)\n";
    }
}

// Clean data: fill numeric with median, categorical with "Unknown"
$cleanedData = $customers;
$cleanedData = imputeMedian($cleanedData, 'age');
$cleanedData = imputeMedian($cleanedData, 'avg_order_value');
$cleanedData = imputeUnknown($cleanedData, 'city');
$cleanedData = imputeUnknown($cleanedData, 'gender');

// Verify no missing values remain
$afterReport = analyzeMissingValues($cleanedData);
$totalMissing = array_sum(array_column($afterReport, 'missing_count'));

echo "\n→ After cleaning:\n";
echo "  - Total missing values: $totalMissing\n";
echo "  - Rows: " . count($cleanedData) . "\n";

// Ensure processed directory exists
$processedDir = dirname(__DIR__) . '/processed';
if (!is_dir($processedDir)) {
    mkdir($processedDir, 0755, true);
}

// Save cleaned data
file_put_contents(
    $processedDir . '/exercise1_clean.json',
    json_encode($cleanedData, JSON_PRETTY_PRINT)
);

echo "\n✓ Cleaned data saved to processed/exercise1_clean.json\n";
echo "✓ Exercise 1 complete!\n";
