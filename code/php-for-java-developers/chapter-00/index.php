<?php

declare(strict_types=1);

// Set response headers
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Simple routing
if ($method === 'GET') {
    $response = [
        'message' => 'Hello from PHP!',
        'timestamp' => time(),
        'version' => phpversion()
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
