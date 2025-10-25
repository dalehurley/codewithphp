<?php

$dbPath = __DIR__ . '/data/database.sqlite';
$dsn = "sqlite:$dbPath";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create a table with nullable columns
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            discount_price REAL
        )
    ");

    echo "=== Working with NULL Values ===" . PHP_EOL . PHP_EOL;

    // Insert products with and without NULL values
    $stmt = $pdo->prepare("
        INSERT INTO products (name, description, price, discount_price) 
        VALUES (?, ?, ?, ?)
    ");

    // Product with all values
    $stmt->execute(["Laptop", "High-performance laptop", 999.99, 899.99]);

    // Product with NULL description and discount_price
    $stmt->execute(["Mouse", null, 29.99, null]);

    // Product with empty string description (different from NULL!)
    $stmt->execute(["Keyboard", "", 79.99, null]);

    echo "Inserted 3 products." . PHP_EOL . PHP_EOL;

    // Fetch and check for NULL
    echo "Products:" . PHP_EOL;
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        echo "- {$product['name']}: \${$product['price']}" . PHP_EOL;

        // Check if description is NULL
        if ($product['description'] === null) {
            echo "  Description: [No description]" . PHP_EOL;
        } elseif ($product['description'] === "") {
            echo "  Description: [Empty string]" . PHP_EOL;
        } else {
            echo "  Description: {$product['description']}" . PHP_EOL;
        }

        // Check if discount price is NULL
        if ($product['discount_price'] === null) {
            echo "  Discount: [No discount]" . PHP_EOL;
        } else {
            echo "  Discount: \${$product['discount_price']}" . PHP_EOL;
        }
        echo PHP_EOL;
    }

    // Query for products with NULL discount_price
    echo "Products with no discount (WHERE discount_price IS NULL):" . PHP_EOL;
    $stmt = $pdo->query("SELECT name FROM products WHERE discount_price IS NULL");
    foreach ($stmt->fetchAll() as $product) {
        echo "- {$product['name']}" . PHP_EOL;
    }
    echo PHP_EOL;

    // Query for products WITH a discount (IS NOT NULL)
    echo "Products with a discount (WHERE discount_price IS NOT NULL):" . PHP_EOL;
    $stmt = $pdo->query("SELECT name, discount_price FROM products WHERE discount_price IS NOT NULL");
    foreach ($stmt->fetchAll() as $product) {
        echo "- {$product['name']}: \${$product['discount_price']}" . PHP_EOL;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . PHP_EOL);
}
