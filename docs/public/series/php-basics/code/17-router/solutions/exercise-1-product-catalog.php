<?php

declare(strict_types=1);

/**
 * Exercise 1: Product Catalog with Dynamic Routes
 * 
 * Build a simple product catalog using dynamic routing:
 * 
 * Requirements:
 * - /products route displays list of products
 * - /products/{id} route displays specific product details
 * - Product data from array with id, name, price, description
 */

require_once __DIR__ . '/../Router.php';

// Sample product data
$products = [
    1 => [
        'id' => 1,
        'name' => 'Laptop',
        'price' => 999.99,
        'description' => 'Powerful laptop for work and gaming'
    ],
    2 => [
        'id' => 2,
        'name' => 'Mouse',
        'price' => 29.99,
        'description' => 'Wireless ergonomic mouse'
    ],
    3 => [
        'id' => 3,
        'name' => 'Keyboard',
        'price' => 79.99,
        'description' => 'Mechanical keyboard with RGB lighting'
    ],
    4 => [
        'id' => 4,
        'name' => 'Monitor',
        'price' => 349.99,
        'description' => '27-inch 4K display'
    ],
    5 => [
        'id' => 5,
        'name' => 'Webcam',
        'price' => 89.99,
        'description' => '1080p HD webcam with microphone'
    ]
];

$router = new Router();

// Product list route
$router->get('/products', function () use ($products) {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Product Catalog</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 0 20px; }
        h1 { color: #333; }
        .product-grid { display: grid; gap: 20px; }
        .product-card { 
            border: 1px solid #ddd; 
            padding: 20px; 
            border-radius: 8px;
            transition: box-shadow 0.3s;
        }
        .product-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .product-name { font-size: 1.2em; font-weight: bold; color: #2c3e50; margin: 0 0 10px 0; }
        .product-price { color: #27ae60; font-size: 1.1em; font-weight: bold; }
        .product-description { color: #666; margin: 10px 0; }
        .view-details { 
            display: inline-block;
            margin-top: 10px;
            color: #3498db; 
            text-decoration: none;
            font-weight: bold;
        }
        .view-details:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Product Catalog</h1>
    <div class='product-grid'>";

    foreach ($products as $product) {
        echo "
        <div class='product-card'>
            <h2 class='product-name'>{$product['name']}</h2>
            <p class='product-price'>\${$product['price']}</p>
            <p class='product-description'>{$product['description']}</p>
            <a href='/products/{$product['id']}' class='view-details'>View Details →</a>
        </div>";
    }

    echo "
    </div>
</body>
</html>";
});

// Single product route
$router->get('/products/{id}', function ($id) use ($products) {
    $id = (int) $id;

    if (!isset($products[$id])) {
        http_response_code(404);
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Product Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; text-align: center; }
        h1 { color: #e74c3c; }
    </style>
</head>
<body>
    <h1>Product Not Found</h1>
    <p>The product you're looking for doesn't exist.</p>
    <a href='/products'>← Back to Product List</a>
</body>
</html>";
        return;
    }

    $product = $products[$id];

    echo "<!DOCTYPE html>
<html>
<head>
    <title>{$product['name']} - Product Details</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 0 20px; }
        .back-link { color: #3498db; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .back-link:hover { text-decoration: underline; }
        .product-detail { border: 2px solid #ddd; padding: 30px; border-radius: 8px; }
        .product-name { color: #2c3e50; margin-top: 0; }
        .product-price { color: #27ae60; font-size: 2em; font-weight: bold; margin: 20px 0; }
        .product-description { color: #555; line-height: 1.6; }
        .product-id { color: #999; font-size: 0.9em; }
        .add-to-cart { 
            background: #27ae60; 
            color: white; 
            border: none; 
            padding: 12px 30px; 
            font-size: 1.1em;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .add-to-cart:hover { background: #229954; }
    </style>
</head>
<body>
    <a href='/products' class='back-link'>← Back to Products</a>
    <div class='product-detail'>
        <p class='product-id'>Product ID: {$product['id']}</p>
        <h1 class='product-name'>{$product['name']}</h1>
        <div class='product-price'>\${$product['price']}</div>
        <p class='product-description'>{$product['description']}</p>
        <button class='add-to-cart' onclick='alert(\"Added to cart!\")'>Add to Cart</button>
    </div>
</body>
</html>";
});

// Home route
$router->get('/', function () {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; text-align: center; }
        h1 { color: #2c3e50; }
        a { 
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 1.1em;
            margin-top: 20px;
        }
        a:hover { background: #2980b9; }
    </style>
</head>
<body>
    <h1>Welcome to the Product Catalog</h1>
    <a href='/products'>Browse Products</a>
</body>
</html>";
});

// Dispatch the request
$router->dispatch();
