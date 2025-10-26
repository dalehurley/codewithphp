<?php

declare(strict_types=1);

/**
 * API Test Client
 * 
 * Tests the simple-rest-api.php endpoints
 * Run: php api-test-client.php
 */

$apiUrl = 'http://localhost:8000/simple-rest-api.php';

echo "=== Testing Simple REST API ===" . PHP_EOL . PHP_EOL;

function apiRequest(string $method, string $url, ?array $data = null): array
{
    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ];

    if ($data !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Test 1: Get API info
echo "1. GET API Info:" . PHP_EOL;
$response = apiRequest('GET', $apiUrl);
echo "Status: {$response['status']}" . PHP_EOL;
echo "API: {$response['data']['name']} v{$response['data']['version']}" . PHP_EOL;
echo PHP_EOL;

// Test 2: Create todos
echo "2. Creating Todos:" . PHP_EOL;

$todos = [
    ['title' => 'Learn PHP'],
    ['title' => 'Build REST API', 'completed' => false],
    ['title' => 'Master JSON', 'completed' => true],
];

$createdIds = [];

foreach ($todos as $todo) {
    $response = apiRequest('POST', "$apiUrl/todos", $todo);

    if ($response['status'] === 200 && $response['data']['status'] === 'success') {
        $id = $response['data']['data']['id'];
        $createdIds[] = $id;
        echo "✓ Created todo #{$id}: {$todo['title']}" . PHP_EOL;
    } else {
        echo "✗ Failed to create todo" . PHP_EOL;
    }
}
echo PHP_EOL;

// Test 3: List all todos
echo "3. Listing All Todos:" . PHP_EOL;
$response = apiRequest('GET', "$apiUrl/todos");

if ($response['status'] === 200) {
    $todos = $response['data']['data'];
    echo "Found " . count($todos) . " todos:" . PHP_EOL;

    foreach ($todos as $todo) {
        $status = $todo['completed'] ? '✓' : '○';
        echo "  $status #{$todo['id']}: {$todo['title']}" . PHP_EOL;
    }
}
echo PHP_EOL;

// Test 4: Get single todo
if (!empty($createdIds)) {
    $id = $createdIds[0];
    echo "4. Getting Single Todo (ID: $id):" . PHP_EOL;
    $response = apiRequest('GET', "$apiUrl/todos/$id");

    if ($response['status'] === 200) {
        $todo = $response['data']['data'];
        echo "✓ Title: {$todo['title']}" . PHP_EOL;
        echo "  Completed: " . ($todo['completed'] ? 'Yes' : 'No') . PHP_EOL;
    }
    echo PHP_EOL;
}

// Test 5: Update todo
if (!empty($createdIds)) {
    $id = $createdIds[0];
    echo "5. Updating Todo (ID: $id):" . PHP_EOL;

    $response = apiRequest('PUT', "$apiUrl/todos/$id", [
        'title' => 'Learn PHP (Updated)',
        'completed' => true
    ]);

    if ($response['status'] === 200) {
        echo "✓ Todo updated successfully" . PHP_EOL;
        $todo = $response['data']['data'];
        echo "  New title: {$todo['title']}" . PHP_EOL;
        echo "  Completed: " . ($todo['completed'] ? 'Yes' : 'No') . PHP_EOL;
    }
    echo PHP_EOL;
}

// Test 6: Filter completed todos
echo "6. Filtering Completed Todos:" . PHP_EOL;
$response = apiRequest('GET', "$apiUrl/todos?completed=1");

if ($response['status'] === 200) {
    $todos = $response['data']['data'];
    echo "Found " . count($todos) . " completed todos" . PHP_EOL;
}
echo PHP_EOL;

// Test 7: Delete todo
if (!empty($createdIds)) {
    $id = $createdIds[0];
    echo "7. Deleting Todo (ID: $id):" . PHP_EOL;

    $response = apiRequest('DELETE', "$apiUrl/todos/$id");

    if ($response['status'] === 200) {
        echo "✓ Todo deleted successfully" . PHP_EOL;
    }
    echo PHP_EOL;
}

// Test 8: Error handling - Get non-existent todo
echo "8. Error Handling (Non-existent Todo):" . PHP_EOL;
$response = apiRequest('GET', "$apiUrl/todos/99999");

if ($response['status'] === 404) {
    echo "✓ Correctly returned 404 error" . PHP_EOL;
    echo "  Message: {$response['data']['message']}" . PHP_EOL;
}
echo PHP_EOL;

// Test 9: Validation - Create without title
echo "9. Validation (Missing Required Field):" . PHP_EOL;
$response = apiRequest('POST', "$apiUrl/todos", ['completed' => false]);

if ($response['status'] === 400) {
    echo "✓ Correctly returned validation error" . PHP_EOL;
    echo "  Message: {$response['data']['message']}" . PHP_EOL;
}
echo PHP_EOL;

echo "=== All tests complete! ===" . PHP_EOL;
