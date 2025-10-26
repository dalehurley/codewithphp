<?php

declare(strict_types=1);

/**
 * Chapter 04: Exercise 2 Solution
 * Normalize Product Prices
 * 
 * Goal: Apply multiple normalization techniques and compare results
 */

/**
 * Min-Max normalization
 */
function minMaxNormalize(array $values): array
{
    $min = min($values);
    $max = max($values);

    if ($max === $min) {
        return array_fill(0, count($values), 0.5);
    }

    return array_map(fn($v) => round(($v - $min) / ($max - $min), 4), $values);
}

/**
 * Z-score normalization
 */
function zScoreNormalize(array $values): array
{
    $mean = array_sum($values) / count($values);
    $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
    $stdDev = sqrt($variance);

    if ($stdDev === 0) {
        return array_fill(0, count($values), 0);
    }

    return array_map(fn($v) => round(($v - $mean) / $stdDev, 4), $values);
}

/**
 * Robust scaling
 */
function robustScale(array $values): array
{
    $sorted = $values;
    sort($sorted);

    $count = count($sorted);
    $q1Index = (int)floor($count * 0.25);
    $q3Index = (int)floor($count * 0.75);
    $medianIndex = (int)floor($count * 0.5);

    $median = $sorted[$medianIndex];
    $iqr = $sorted[$q3Index] - $sorted[$q1Index];

    if ($iqr === 0) {
        return array_fill(0, count($values), 0);
    }

    return array_map(fn($v) => round(($v - $median) / $iqr, 4), $values);
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

    $query = "SELECT product_id, name, price, rating FROM products ORDER BY product_id";
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

echo "→ Loaded " . count($products) . " products\n\n";

// Extract prices and ratings
$prices = array_column($products, 'price');
$ratings = array_column($products, 'rating');

// Apply all three normalization techniques to prices
$pricesMinMax = minMaxNormalize($prices);
$pricesZScore = zScoreNormalize($prices);
$pricesRobust = robustScale($prices);

// Display comparison for first 5 products
echo "Normalization Comparison (first 5 products):\n";
echo str_repeat("=", 80) . "\n";
printf("%-5s %-25s %10s %10s %10s %10s\n", "ID", "Product", "Original", "Min-Max", "Z-Score", "Robust");
echo str_repeat("-", 80) . "\n";

for ($i = 0; $i < min(5, count($products)); $i++) {
    printf(
        "%-5s %-25s %10.2f %10.4f %10.4f %10.4f\n",
        $products[$i]['product_id'],
        substr($products[$i]['name'], 0, 25),
        $products[$i]['price'],
        $pricesMinMax[$i],
        $pricesZScore[$i],
        $pricesRobust[$i]
    );
}

// Statistics
echo "\n" . str_repeat("=", 80) . "\n";
echo "Statistics:\n";
echo "  Min-Max range: [" . min($pricesMinMax) . ", " . max($pricesMinMax) . "]\n";
echo "  Z-Score mean: " . round(array_sum($pricesZScore) / count($pricesZScore), 4) . " (should be ~0)\n";
echo "  Z-Score std: " . round(sqrt(array_sum(array_map(fn($v) => $v ** 2, $pricesZScore)) / count($pricesZScore)), 4) . " (should be ~1)\n";
echo "  Robust median: " . round(array_sum($pricesRobust) / count($pricesRobust), 4) . "\n";

echo "\n✓ Exercise 2 complete!\n";
echo "\nKey Insights:\n";
echo "  - Min-Max scales all values to [0, 1]\n";
echo "  - Z-Score centers around 0 with std=1\n";
echo "  - Robust scaling is less affected by outliers\n";
