<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 6: Handling Missing Values
 * 
 * Demonstrates: Missing value analysis, deletion, mean/mode imputation
 */

/**
 * Analyze missing values in dataset
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
 * Remove rows with any missing values
 */
function dropMissingRows(array $data): array
{
    return array_filter($data, function ($row) {
        foreach ($row as $value) {
            if ($value === null || $value === '') {
                return false;
            }
        }
        return true;
    });
}

/**
 * Fill missing numeric values with mean
 */
function imputeMean(array $data, string $column): array
{
    // Calculate mean of non-null values
    $values = array_filter(
        array_column($data, $column),
        fn($v) => $v !== null && $v !== ''
    );

    if (empty($values)) {
        return $data;
    }

    $mean = array_sum($values) / count($values);

    // Fill missing values
    return array_map(function ($row) use ($column, $mean) {
        if ($row[$column] === null || $row[$column] === '') {
            $row[$column] = round($mean, 2);
        }
        return $row;
    }, $data);
}

/**
 * Fill missing categorical values with mode (most common)
 */
function imputeMode(array $data, string $column): array
{
    // Find mode
    $values = array_filter(
        array_column($data, $column),
        fn($v) => $v !== null && $v !== ''
    );

    if (empty($values)) {
        return $data;
    }

    $frequency = array_count_values($values);
    arsort($frequency);
    $mode = array_key_first($frequency);

    // Fill missing values
    return array_map(function ($row) use ($column, $mode) {
        if ($row[$column] === null || $row[$column] === '') {
            $row[$column] = $mode;
        }
        return $row;
    }, $data);
}

// Load incomplete data
$filePath = __DIR__ . '/data/incomplete_customers.json';
if (!file_exists($filePath)) {
    echo "Error: incomplete_customers.json not found. Run 05-create-incomplete-data.php first.\n";
    exit(1);
}

$content = file_get_contents($filePath);
if ($content === false) {
    echo "Error: Could not read incomplete_customers.json\n";
    exit(1);
}

$data = json_decode($content, true);
if ($data === null || !is_array($data)) {
    echo "Error: Invalid JSON in incomplete_customers.json\n";
    exit(1);
}

if (empty($data)) {
    echo "Error: Dataset is empty\n";
    exit(1);
}

echo "Original dataset: " . count($data) . " rows\n\n";

// Analyze missing values
$missingReport = analyzeMissingValues($data);
echo "Missing Value Analysis:\n";
foreach ($missingReport as $column => $stats) {
    if ($stats['missing_count'] > 0) {
        echo "- $column: {$stats['missing_count']} missing ({$stats['missing_percentage']}%)\n";
    }
}

// Strategy 1: Drop rows with missing values
$cleanData = dropMissingRows($data);
echo "\n✓ After dropping rows: " . count($cleanData) . " rows remain\n";

// Strategy 2: Impute missing values
$imputedData = $data;
$imputedData = imputeMean($imputedData, 'age');
$imputedData = imputeMean($imputedData, 'avg_order_value');
$imputedData = imputeMode($imputedData, 'city');
$imputedData = imputeMode($imputedData, 'has_subscription');

$afterImpute = analyzeMissingValues($imputedData);
$totalMissing = array_sum(array_column($afterImpute, 'missing_count'));
echo "✓ After imputation: $totalMissing missing values remain\n";

// Ensure processed directory exists
$processedDir = __DIR__ . '/processed';
if (!is_dir($processedDir)) {
    if (!mkdir($processedDir, 0755, true)) {
        echo "Error: Could not create processed directory\n";
        exit(1);
    }
}

// Save cleaned data
file_put_contents(
    $processedDir . '/clean_customers.json',
    json_encode($imputedData, JSON_PRETTY_PRINT)
);

echo "\n✓ Cleaned data saved to processed/clean_customers.json\n";
