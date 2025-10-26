<?php

declare(strict_types=1);

try {
    // Create SQLite database
    $db = new PDO('sqlite:' . __DIR__ . '/data/products.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create products table
    $db->exec('
        CREATE TABLE IF NOT EXISTS products (
            product_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock_quantity INTEGER NOT NULL,
            rating DECIMAL(3,2),
            created_date DATE,
            is_active BOOLEAN DEFAULT 1
        )
    ');

    // Create orders table to relate customers and products
    $db->exec('
        CREATE TABLE IF NOT EXISTS orders (
            order_id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            order_date DATE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
            FOREIGN KEY (product_id) REFERENCES products(product_id)
        )
    ');

    // Sample products data
    $products = [
        ['Laptop Pro 15"', 'Electronics', 1299.99, 50, 4.5, '2023-01-15', 1],
        ['Wireless Headphones', 'Electronics', 199.99, 100, 4.2, '2023-02-20', 1],
        ['Coffee Maker Deluxe', 'Home & Kitchen', 89.99, 75, 4.3, '2023-01-10', 1],
        ['Running Shoes', 'Sports & Outdoors', 129.99, 200, 4.6, '2023-03-05', 1],
        ['Yoga Mat Premium', 'Sports & Outdoors', 49.99, 150, 4.4, '2023-02-28', 1],
        ['Bluetooth Speaker', 'Electronics', 79.99, 80, 4.1, '2023-01-25', 1],
        ['Smart Watch Series 5', 'Electronics', 299.99, 60, 4.7, '2023-03-12', 1],
        ['Organic Cotton T-Shirt', 'Clothing', 29.99, 300, 4.0, '2023-02-15', 1],
        ['Stainless Steel Water Bottle', 'Home & Kitchen', 24.99, 120, 4.5, '2023-01-30', 1],
        ['LED Desk Lamp', 'Home & Kitchen', 59.99, 90, 4.3, '2023-03-08', 1],
        ['Mechanical Keyboard', 'Electronics', 149.99, 40, 4.8, '2023-02-10', 1],
        ['Ceramic Plant Pot Set', 'Home & Garden', 34.99, 70, 4.2, '2023-01-18', 1],
        ['Protein Powder 2kg', 'Health & Nutrition', 69.99, 85, 4.4, '2023-03-20', 1],
        ['Wireless Mouse', 'Electronics', 39.99, 110, 4.1, '2023-02-05', 1],
        ['Garden Hose 50ft', 'Home & Garden', 44.99, 55, 4.0, '2023-01-22', 1],
        ['Sunscreen SPF 50', 'Health & Beauty', 19.99, 140, 4.3, '2023-03-15', 1],
        ['Cast Iron Skillet', 'Home & Kitchen', 79.99, 65, 4.6, '2023-02-25', 1],
        ['Resistance Bands Set', 'Sports & Outdoors', 39.99, 95, 4.2, '2023-01-08', 1],
        ['Essential Oil Diffuser', 'Health & Beauty', 54.99, 75, 4.4, '2023-03-03', 1],
        ['Notebook Set (3-pack)', 'Office Supplies', 14.99, 180, 4.1, '2023-02-18', 1]
    ];

    // Insert products
    $stmt = $db->prepare('
        INSERT INTO products (name, category, price, stock_quantity, rating, created_date, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    foreach ($products as $product) {
        $stmt->execute($product);
    }

    // Generate sample orders (relating customers to products)
    $orders = [];
    for ($i = 1; $i <= 500; $i++) {
        $customerId = rand(1, 100);
        $productId = rand(1, 20);
        $quantity = rand(1, 5);

        // Get product price to calculate total
        $priceStmt = $db->prepare('SELECT price FROM products WHERE product_id = ?');
        $priceStmt->execute([$productId]);
        $price = $priceStmt->fetchColumn();

        $totalAmount = $quantity * $price;
        $orderDate = date('Y-m-d', strtotime('-' . rand(1, 365) . ' days'));

        $orders[] = [$customerId, $productId, $quantity, $orderDate, $totalAmount];
    }

    // Insert orders
    $orderStmt = $db->prepare('
        INSERT INTO orders (customer_id, product_id, quantity, order_date, total_amount)
        VALUES (?, ?, ?, ?, ?)
    ');

    foreach ($orders as $order) {
        $orderStmt->execute($order);
    }

    echo "Products database created successfully with:\n";
    echo "- 20 products across 6 categories\n";
    echo "- 500 sample orders\n";
    echo "- Realistic pricing and ratings\n";
    echo "- Ready for data preprocessing exercises\n";
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}
