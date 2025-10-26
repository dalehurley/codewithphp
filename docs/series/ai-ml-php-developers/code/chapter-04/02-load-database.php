<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 2: Loading Database Data
 * 
 * Demonstrates: PDO database connections, SQL queries, data aggregation
 */

/**
 * Load data from SQLite database
 */
function loadFromDatabase(string $dbPath, string $query): array
{
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new RuntimeException("Database error: " . $e->getMessage());
    }
}

// Load products with aggregated order data
$query = "
    SELECT 
        p.product_id,
        p.name,
        p.category,
        p.price,
        p.stock_quantity,
        p.rating,
        COUNT(o.order_id) as total_orders,
        COALESCE(SUM(o.quantity), 0) as units_sold,
        COALESCE(SUM(o.total_amount), 0) as revenue
    FROM products p
    LEFT JOIN orders o ON p.product_id = o.product_id
    GROUP BY p.product_id
    ORDER BY revenue DESC
";

$products = loadFromDatabase(__DIR__ . '/data/products.db', $query);

echo "Loaded " . count($products) . " products\n\n";

// Top 5 products by revenue
echo "Top 5 Products by Revenue:\n";
foreach (array_slice($products, 0, 5) as $product) {
    echo sprintf(
        "- %s: $%.2f (%d orders, %d units)\n",
        $product['name'],
        $product['revenue'],
        $product['total_orders'],
        $product['units_sold']
    );
}

// Category analysis
$categories = [];
foreach ($products as $product) {
    $cat = $product['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = 0;
    }
    $categories[$cat] += (float)$product['revenue'];
}

echo "\nRevenue by Category:\n";
arsort($categories);
foreach ($categories as $category => $revenue) {
    echo "- $category: $" . number_format($revenue, 2) . "\n";
}
