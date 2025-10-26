<?php

declare(strict_types=1);

/**
 * Simple REST API Example
 * 
 * A basic REST API for managing products.
 * Run with: php -S localhost:8000 simple-rest-api.php
 * 
 * Endpoints:
 * - GET    /products        - List all products
 * - GET    /products/{id}   - Get single product
 * - POST   /products        - Create product
 * - PUT    /products/{id}   - Update product
 * - DELETE /products/{id}   - Delete product
 */

// CLI compatibility: Only set headers in web context
if (php_sapi_name() !== 'cli') {
    // Enable CORS for development
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Handle preflight requests
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    // Set JSON response header
    header('Content-Type: application/json; charset=utf-8');
}

// In-memory database (use a real database in production)
$GLOBALS['products'] = [
    1 => ['id' => 1, 'name' => 'Laptop Pro 15"', 'price' => 1299.99, 'stock' => 15],
    2 => ['id' => 2, 'name' => 'Wireless Mouse', 'price' => 29.99, 'stock' => 50],
    3 => ['id' => 3, 'name' => 'Mechanical Keyboard', 'price' => 129.99, 'stock' => 30],
    4 => ['id' => 4, 'name' => '27" Monitor 4K', 'price' => 449.99, 'stock' => 8],
    5 => ['id' => 5, 'name' => 'USB-C Hub', 'price' => 59.99, 'stock' => 25],
];

$GLOBALS['nextId'] = 6;

/**
 * Send JSON response with status code
 */
function sendResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send error response
 */
function sendError(int $statusCode, string $message): void
{
    sendResponse($statusCode, [
        'success' => false,
        'error' => $message
    ]);
}

/**
 * Find product by ID
 */
function findProduct(int $id): ?array
{
    return $GLOBALS['products'][$id] ?? null;
}

/**
 * Validate product data
 */
function validateProduct(array $data): ?string
{
    if (empty($data['name'])) {
        return 'Name is required';
    }

    if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
        return 'Price must be a positive number';
    }

    if (isset($data['stock']) && (!is_numeric($data['stock']) || $data['stock'] < 0)) {
        return 'Stock must be a positive number';
    }

    return null;
}

// Parse request
// CLI compatibility: Use defaults if not in web context
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/products', PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$productId = isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;

// Get request body for POST/PUT
$requestBody = null;
if (in_array($method, ['POST', 'PUT'])) {
    $input = file_get_contents('php://input');
    $requestBody = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError(400, 'Invalid JSON: ' . json_last_error_msg());
    }
}

// Route: GET /products - List all products
if ($method === 'GET' && $productId === null) {
    sendResponse(200, [
        'success' => true,
        'data' => array_values($GLOBALS['products']),
        'count' => count($GLOBALS['products'])
    ]);
}

// Route: GET /products/{id} - Get single product
if ($method === 'GET' && $productId !== null) {
    $product = findProduct($productId);

    if ($product === null) {
        sendError(404, 'Product not found');
    }

    sendResponse(200, [
        'success' => true,
        'data' => $product
    ]);
}

// Route: POST /products - Create product
if ($method === 'POST' && $productId === null) {
    $error = validateProduct($requestBody);
    if ($error !== null) {
        sendError(400, $error);
    }

    $newProduct = [
        'id' => $GLOBALS['nextId']++,
        'name' => $requestBody['name'],
        'price' => (float)$requestBody['price'],
        'stock' => isset($requestBody['stock']) ? (int)$requestBody['stock'] : 0
    ];

    $GLOBALS['products'][$newProduct['id']] = $newProduct;

    sendResponse(201, [
        'success' => true,
        'message' => 'Product created successfully',
        'data' => $newProduct
    ]);
}

// Route: PUT /products/{id} - Update product
if ($method === 'PUT' && $productId !== null) {
    $product = findProduct($productId);

    if ($product === null) {
        sendError(404, 'Product not found');
    }

    $error = validateProduct($requestBody);
    if ($error !== null) {
        sendError(400, $error);
    }

    $updatedProduct = [
        'id' => $productId,
        'name' => $requestBody['name'],
        'price' => (float)$requestBody['price'],
        'stock' => isset($requestBody['stock']) ? (int)$requestBody['stock'] : $product['stock']
    ];

    $GLOBALS['products'][$productId] = $updatedProduct;

    sendResponse(200, [
        'success' => true,
        'message' => 'Product updated successfully',
        'data' => $updatedProduct
    ]);
}

// Route: DELETE /products/{id} - Delete product
if ($method === 'DELETE' && $productId !== null) {
    $product = findProduct($productId);

    if ($product === null) {
        sendError(404, 'Product not found');
    }

    unset($GLOBALS['products'][$productId]);

    sendResponse(200, [
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
}

// No route matched
sendError(404, 'Endpoint not found');
