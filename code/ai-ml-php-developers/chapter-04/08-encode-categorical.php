<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 8: Encoding Categorical Variables
 * 
 * Demonstrates: Label encoding, one-hot encoding, frequency encoding
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
 * Label Encoding: Convert categories to sequential integers
 * Example: ['red', 'blue', 'green'] → [0, 1, 2]
 */
function labelEncode(array $data, string $column): array
{
    // Get unique values and assign numeric labels
    $uniqueValues = array_unique(array_column($data, $column));
    $mapping = array_flip(array_values($uniqueValues));

    return [
        'data' => array_map(function ($row) use ($column, $mapping) {
            return [
                ...$row,
                $column . '_encoded' => $mapping[$row[$column]]
            ];
        }, $data),
        'mapping' => $mapping
    ];
}

/**
 * One-Hot Encoding: Create binary column for each category
 * Example: 'red' → [1, 0, 0], 'blue' → [0, 1, 0], 'green' → [0, 0, 1]
 */
function oneHotEncode(array $data, string $column): array
{
    $uniqueValues = array_unique(array_column($data, $column));
    sort($uniqueValues);

    return array_map(function ($row) use ($column, $uniqueValues) {
        $encoded = $row;
        foreach ($uniqueValues as $value) {
            $colName = $column . '_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($value));
            $encoded[$colName] = ($row[$column] === $value) ? 1 : 0;
        }
        return $encoded;
    }, $data);
}

/**
 * Frequency Encoding: Replace category with its frequency
 * Useful for high-cardinality categorical features
 */
function frequencyEncode(array $data, string $column): array
{
    // Count occurrences of each value
    $values = array_column($data, $column);
    $frequency = array_count_values($values);

    return array_map(function ($row) use ($column, $frequency) {
        return [
            ...$row,
            $column . '_frequency' => $frequency[$row[$column]]
        ];
    }, $data);
}

// Load customer data from CSV with type coercion
$file = fopen(__DIR__ . '/data/customers.csv', 'r');
if ($file === false) {
    echo "Error: Could not open customers.csv\n";
    exit(1);
}

$headers = fgetcsv($file, 0, ',', '"', '\\');
if ($headers === false) {
    echo "Error: Invalid CSV format\n";
    exit(1);
}

$numericFields = ['age', 'total_orders', 'avg_order_value', 'has_subscription', 'is_active'];
$customers = [];
while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
    $combined = array_combine($headers, $row);
    $customers[] = coerceTypes($combined, $numericFields);
}
fclose($file);

if (empty($customers)) {
    echo "Error: No customer data loaded\n";
    exit(1);
}

echo "Encoding categorical variables for " . count($customers) . " customers\n\n";

// Example 1: Label Encoding for gender
$result = labelEncode(array_slice($customers, 0, 10), 'gender');
echo "Label Encoding (Gender):\n";
echo "Mapping: " . json_encode($result['mapping']) . "\n";
foreach (array_slice($result['data'], 0, 3) as $customer) {
    echo "- {$customer['gender']} → {$customer['gender_encoded']}\n";
}

// Example 2: One-Hot Encoding for country
$oneHotData = oneHotEncode(array_slice($customers, 0, 10), 'country');
echo "\nOne-Hot Encoding (Country):\n";
foreach (array_slice($oneHotData, 0, 3) as $customer) {
    $countryFields = array_filter(
        $customer,
        fn($key) => str_starts_with((string)$key, 'country_'),
        ARRAY_FILTER_USE_KEY
    );
    echo "- {$customer['country']}: " . json_encode($countryFields) . "\n";
}

// Example 3: Frequency Encoding for city
$freqData = frequencyEncode($customers, 'city');
echo "\nFrequency Encoding (City):\n";
$uniqueCities = array_unique(array_column($customers, 'city'));
foreach (array_slice($uniqueCities, 0, 5) as $city) {
    $matches = array_filter($freqData, fn($c) => $c['city'] === $city);
    $example = reset($matches);
    if ($example) {
        echo "- $city: appears {$example['city_frequency']} times\n";
    }
}

// Ensure processed directory exists
$processedDir = __DIR__ . '/processed';
if (!is_dir($processedDir)) {
    if (!mkdir($processedDir, 0755, true)) {
        echo "Error: Could not create processed directory\n";
        exit(1);
    }
}

// Save encoded data
file_put_contents(
    $processedDir . '/encoded_customers.json',
    json_encode($freqData, JSON_PRETTY_PRINT)
);

echo "\n✓ Encoded data saved to processed/encoded_customers.json\n";
