<?php

declare(strict_types=1);

require __DIR__ . '/../report.php';

$method = $_SERVER['REQUEST_METHOD'];
$inStock = $_GET['in_stock'] ?? '0';

header('Content-Type: application/json; charset=utf-8');

if ($method !== 'GET' && $method !== 'HEAD') {
    http_response_code(405);
    header('Allow: GET, HEAD');
    $response = ['error' => 'Method not allowed.'];
} elseif ($inStock !== '0' && $inStock !== '1') {
    http_response_code(400);
    $response = ['error' => 'in_stock must be 0 or 1.'];
} else {
    $response = inventoryReport(inventoryRows(), $inStock === '1');
}

if ($method !== 'HEAD') {
    echo json_encode($response, JSON_THROW_ON_ERROR), PHP_EOL;
}
