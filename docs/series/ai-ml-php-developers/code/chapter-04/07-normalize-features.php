<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 7: Normalizing Numeric Features
 * 
 * Demonstrates: Min-max normalization, z-score standardization, robust scaling
 */

/**
 * Min-Max normalization: Scale values to [0, 1]
 * Formula: (x - min) / (max - min)
 * Best for: When you need bounded [0,1] values and data has no extreme outliers
 */
function minMaxNormalize(array $data, string $column): array
{
    $values = array_column($data, $column);
    $min = min($values);
    $max = max($values);

    if ($max === $min) {
        // All values are the same, set to 0.5
        return array_map(fn($row) => [
            ...$row,
            $column . '_normalized' => 0.5
        ], $data);
    }

    return array_map(function ($row) use ($column, $min, $max) {
        $normalized = ($row[$column] - $min) / ($max - $min);
        return [
            ...$row,
            $column . '_normalized' => round($normalized, 4)
        ];
    }, $data);
}

/**
 * Z-score normalization (standardization)
 * Formula: (x - mean) / standard_deviation
 * Results in mean=0, std=1
 * Best for: When algorithm assumes normally distributed data (linear regression, neural networks)
 */
function zScoreNormalize(array $data, string $column): array
{
    $values = array_column($data, $column);
    $mean = array_sum($values) / count($values);

    // Calculate standard deviation
    $squaredDiffs = array_map(fn($v) => ($v - $mean) ** 2, $values);
    $variance = array_sum($squaredDiffs) / count($values);
    $stdDev = sqrt($variance);

    if ($stdDev === 0.0) {
        // No variance, set all to 0
        return array_map(fn($row) => [
            ...$row,
            $column . '_standardized' => 0.0
        ], $data);
    }

    return array_map(function ($row) use ($column, $mean, $stdDev) {
        $standardized = ($row[$column] - $mean) / $stdDev;
        return [
            ...$row,
            $column . '_standardized' => round($standardized, 4)
        ];
    }, $data);
}

/**
 * Robust scaling using median and IQR (inter-quartile range)
 * Uses median and IQR instead of mean/std, making it resistant to outliers
 * Best for: Data with significant outliers that you want to preserve
 */
function robustScale(array $data, string $column): array
{
    $values = array_column($data, $column);
    sort($values);

    $count = count($values);
    $q1Index = (int)floor($count * 0.25);
    $q3Index = (int)floor($count * 0.75);
    $medianIndex = (int)floor($count * 0.5);

    $median = $values[$medianIndex];
    $q1 = $values[$q1Index];
    $q3 = $values[$q3Index];
    $iqr = $q3 - $q1;

    if ($iqr === 0) {
        return array_map(fn($row) => [
            ...$row,
            $column . '_robust' => 0.0
        ], $data);
    }

    return array_map(function ($row) use ($column, $median, $iqr) {
        $scaled = ($row[$column] - $median) / $iqr;
        return [
            ...$row,
            $column . '_robust' => round($scaled, 4)
        ];
    }, $data);
}

// Load clean customer data
$filePath = __DIR__ . '/processed/clean_customers.json';
if (!file_exists($filePath)) {
    echo "Error: clean_customers.json not found. Run 06-handle-missing-values.php first.\n";
    exit(1);
}

$content = file_get_contents($filePath);
if ($content === false) {
    echo "Error: Could not read clean_customers.json\n";
    exit(1);
}

$data = json_decode($content, true);
if ($data === null || !is_array($data)) {
    echo "Error: Invalid JSON in clean_customers.json\n";
    exit(1);
}

if (empty($data)) {
    echo "Error: Dataset is empty\n";
    exit(1);
}

echo "Normalizing features for " . count($data) . " customers\n\n";

// Show original value ranges
$ages = array_column($data, 'age');
$orders = array_column($data, 'total_orders');
$values = array_column($data, 'avg_order_value');

echo "Original Ranges:\n";
echo "- Age: " . min($ages) . " to " . max($ages) . "\n";
echo "- Total Orders: " . min($orders) . " to " . max($orders) . "\n";
echo "- Avg Order Value: $" . min($values) . " to $" . max($values) . "\n\n";

// Apply all normalization techniques
$normalized = $data;
$normalized = minMaxNormalize($normalized, 'age');
$normalized = minMaxNormalize($normalized, 'total_orders');
$normalized = zScoreNormalize($normalized, 'avg_order_value');

// Display sample
echo "Sample Normalized Data (first 3 customers):\n";
foreach (array_slice($normalized, 0, 3) as $customer) {
    echo "\nCustomer {$customer['customer_id']}:\n";
    echo "  Age: {$customer['age']} → {$customer['age_normalized']} (min-max)\n";
    echo "  Orders: {$customer['total_orders']} → {$customer['total_orders_normalized']} (min-max)\n";
    echo "  Avg Value: \${$customer['avg_order_value']} → {$customer['avg_order_value_standardized']} (z-score)\n";
}

// Demonstrate robust scaling (resistant to outliers)
$robustScaled = robustScale($data, 'avg_order_value');
echo "\nRobust Scaling Sample (first 3 customers):\n";
echo "Robust scaling uses median and IQR, making it resistant to outliers.\n";
foreach (array_slice($robustScaled, 0, 3) as $customer) {
    echo "  Customer {$customer['customer_id']}: \${$customer['avg_order_value']} → {$customer['avg_order_value_robust']}\n";
}

// Ensure processed directory exists
$processedDir = __DIR__ . '/processed';
if (!is_dir($processedDir)) {
    if (!mkdir($processedDir, 0755, true)) {
        echo "Error: Could not create processed directory\n";
        exit(1);
    }
}

// Save normalized data
file_put_contents(
    $processedDir . '/normalized_customers.json',
    json_encode($normalized, JSON_PRETTY_PRINT)
);

echo "\n✓ Normalized data saved to processed/normalized_customers.json\n";
