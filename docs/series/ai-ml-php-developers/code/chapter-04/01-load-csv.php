<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 1: Loading CSV Data
 * 
 * Demonstrates: CSV file parsing, type coercion, basic statistics
 */

/**
 * Coerce string values to appropriate types for numeric fields
 */
function coerceTypes(array $row, array $numericFields): array
{
    foreach ($numericFields as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            $row[$field] = is_numeric($row[$field]) ? (float)$row[$field] : $row[$field];
        }
    }
    return $row;
}

/**
 * Load and explore CSV data
 */
function loadCsv(string $filepath, array $numericFields = []): array
{
    if (!file_exists($filepath)) {
        throw new RuntimeException("File not found: $filepath");
    }

    $file = fopen($filepath, 'r');
    if ($file === false) {
        throw new RuntimeException("Could not open file: $filepath");
    }

    // First row contains headers
    $headers = fgetcsv($file, 0, ',', '"', '\\');
    if ($headers === false) {
        throw new RuntimeException("Invalid CSV format");
    }

    $data = [];
    while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
        // Combine headers with row data for associative array
        $combined = array_combine($headers, $row);

        // Coerce numeric fields from strings to numbers
        if (!empty($numericFields)) {
            $combined = coerceTypes($combined, $numericFields);
        }

        $data[] = $combined;
    }

    fclose($file);

    return $data;
}

// Load customer data with type coercion for numeric fields
$numericFields = ['age', 'total_orders', 'avg_order_value', 'has_subscription', 'is_active'];
$customers = loadCsv(__DIR__ . '/data/customers.csv', $numericFields);

if (empty($customers)) {
    echo "Error: No data loaded from CSV file\n";
    exit(1);
}

echo "Loaded " . count($customers) . " customers\n\n";

// Display first 3 records
echo "Sample records:\n";
foreach (array_slice($customers, 0, 3) as $customer) {
    echo "- {$customer['first_name']} {$customer['last_name']} ";
    echo "(Age: {$customer['age']}, Orders: {$customer['total_orders']})\n";
}

// Basic statistics
$ages = array_column($customers, 'age');
$avgAge = array_sum($ages) / count($ages);

echo "\nBasic Statistics:\n";
echo "- Average age: " . round($avgAge, 1) . "\n";
echo "- Age range: " . min($ages) . " to " . max($ages) . "\n";
