<?php

declare(strict_types=1);

/**
 * Chapter 04: Exercise 3 Solution
 * One-Hot Encode Product Categories
 * 
 * Goal: Handle multi-class categorical encoding
 */

/**
 * One-hot encode a categorical column
 */
function oneHotEncode(array $data, string $column): array
{
    // Get unique categories
    $uniqueValues = array_unique(array_column($data, $column));
    sort($uniqueValues);

    echo "Found " . count($uniqueValues) . " unique categories: " . implode(', ', $uniqueValues) . "\n\n";

    // Create one-hot encoded columns
    $encoded = [];
    foreach ($data as $row) {
        $newRow = $row;
        foreach ($uniqueValues as $value) {
            // Create column name: category_Electronics, category_Sports_Outdoors
            $colName = $column . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $value);
            $newRow[$colName] = ($row[$column] === $value) ? 1 : 0;
        }
        $encoded[] = $newRow;
    }

    return $encoded;
}

// Load products from database
$dbPath = __DIR__ . '/../data/products.db';
if (!file_exists($dbPath)) {
    echo "Error: products.db not found. Run create-products-db.php first.\n";
    exit(1);
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT product_id, name, category, price FROM products ORDER BY category, product_id";
    $stmt = $db->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($products)) {
    echo "Error: No products found in database\n";
    exit(1);
}

echo "→ Loaded " . count($products) . " products\n";
echo "→ Applying one-hot encoding to 'category' column...\n\n";

// Apply one-hot encoding
$encodedProducts = oneHotEncode($products, 'category');

// Display results for first 5 products
echo "One-Hot Encoded Categories (first 5 products):\n";
echo str_repeat("=", 100) . "\n";

// Get category column names
$categoryColumns = array_filter(
    array_keys($encodedProducts[0]),
    fn($key) => str_starts_with($key, 'category_')
);

printf("%-5s %-30s %-20s ", "ID", "Product", "Original Category");
foreach ($categoryColumns as $col) {
    $shortName = substr(str_replace('category_', '', $col), 0, 10);
    printf("%-11s ", $shortName);
}
echo "\n" . str_repeat("-", 100) . "\n";

for ($i = 0; $i < min(5, count($encodedProducts)); $i++) {
    $product = $encodedProducts[$i];
    printf("%-5s %-30s %-20s ", $product['product_id'], substr($product['name'], 0, 30), $product['category']);
    foreach ($categoryColumns as $col) {
        printf("%-11s ", $product[$col]);
    }
    echo "\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "Summary:\n";
echo "  - Original columns: " . count($products[0]) . "\n";
echo "  - After one-hot encoding: " . count($encodedProducts[0]) . "\n";
echo "  - New binary columns created: " . count($categoryColumns) . "\n";
echo "  - Column names: " . implode(', ', array_map(fn($c) => str_replace('category_', '', $c), $categoryColumns)) . "\n";

// Verify that each row has exactly one '1' in category columns
$isValid = true;
foreach ($encodedProducts as $product) {
    $sum = array_sum(array_map(fn($col) => $product[$col], $categoryColumns));
    if ($sum !== 1) {
        $isValid = false;
        break;
    }
}

echo "\n✓ Validation: " . ($isValid ? "Each product has exactly one category (✓)" : "Error: Invalid encoding (✗)") . "\n";

// Ensure processed directory exists
$processedDir = dirname(__DIR__) . '/processed';
if (!is_dir($processedDir)) {
    mkdir($processedDir, 0755, true);
}

// Save encoded data
file_put_contents(
    $processedDir . '/exercise3_onehot.json',
    json_encode($encodedProducts, JSON_PRETTY_PRINT)
);

echo "✓ Encoded data saved to processed/exercise3_onehot.json\n";
echo "✓ Exercise 3 complete!\n";
