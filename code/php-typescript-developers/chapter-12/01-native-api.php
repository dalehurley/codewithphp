<?php
declare(strict_types=1);

/**
 * Basic Native PHP REST API
 *
 * Simple REST API without any framework.
 * Demonstrates the fundamentals of API building in PHP.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH');

// In-memory data store (in real app, this would be a database)
$users = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
];

// GET /api/users - List all users
if ($method === 'GET' && $path === '/api/users') {
    echo json_encode(['data' => $users]);
    exit;
}

// GET /api/users/:id - Get specific user
if ($method === 'GET' && preg_match('#^/api/users/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $user = array_values(array_filter($users, fn($u) => $u['id'] === $id))[0] ?? null;

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    echo json_encode(['data' => $user]);
    exit;
}

// POST /api/users - Create new user
if ($method === 'POST' && $path === '/api/users') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required']);
        exit;
    }

    $newUser = [
        'id' => count($users) + 1,
        'name' => $data['name'],
        'email' => $data['email'],
    ];

    http_response_code(201);
    echo json_encode(['data' => $newUser]);
    exit;
}

// PUT /api/users/:id - Update user
if ($method === 'PUT' && preg_match('#^/api/users/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);

    $userIndex = array_search($id, array_column($users, 'id'));

    if ($userIndex === false) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Update user
    if (isset($data['name'])) {
        $users[$userIndex]['name'] = $data['name'];
    }
    if (isset($data['email'])) {
        $users[$userIndex]['email'] = $data['email'];
    }

    echo json_encode(['data' => $users[$userIndex]]);
    exit;
}

// DELETE /api/users/:id - Delete user
if ($method === 'DELETE' && preg_match('#^/api/users/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $userIndex = array_search($id, array_column($users, 'id'));

    if ($userIndex === false) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    http_response_code(204);
    exit;
}

// 404 Not Found
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
