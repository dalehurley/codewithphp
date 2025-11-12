<?php

declare(strict_types=1);

/**
 * Exercise 3: Add HTTP Method Handlers (Advanced)
 * 
 * Add support for PUT and DELETE methods to router:
 * 
 * Requirements:
 * - Extend Router class with put() and delete() methods
 * - Create test routes for all HTTP methods
 * - Test with curl commands
 * 
 * Usage examples:
 * curl -X GET http://localhost:8000/users
 * curl -X POST http://localhost:8000/users -d "name=John"
 * curl -X PUT http://localhost:8000/users/123 -d "name=John Updated"
 * curl -X DELETE http://localhost:8000/users/123
 */

// Extended Router with PUT and DELETE support
class ExtendedRouter
{
    private array $routes = [];

    /**
     * Register a GET route
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $path, callable $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Register a route for any method
     */
    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Remove query string
        $uri = strtok($uri, '?');

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $uri);

            if ($params !== null) {
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // No route found
        $this->send404();
    }

    /**
     * Match a route pattern against a URI
     */
    private function match(string $pattern, string $uri): ?array
    {
        // Exact match
        if ($pattern === $uri) {
            return [];
        }

        // Convert route pattern to regex
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return array_values($params);
        }

        return null;
    }

    /**
     * Send 404 response
     */
    private function send404(): void
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Not Found',
            'message' => 'The requested resource was not found'
        ]);
    }
}

// Helper function to send JSON response
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// Simulated user database
$users = [
    '1' => ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
    '2' => ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
    '3' => ['id' => 3, 'name' => 'Bob Wilson', 'email' => 'bob@example.com']
];

$router = new ExtendedRouter();

// GET all users
$router->get('/users', function () use ($users) {
    jsonResponse([
        'message' => 'List of all users',
        'data' => array_values($users),
        'count' => count($users)
    ]);
});

// GET single user
$router->get('/users/{id}', function ($id) use ($users) {
    if (!isset($users[$id])) {
        jsonResponse([
            'error' => 'User not found',
            'id' => $id
        ], 404);
        return;
    }

    jsonResponse([
        'message' => 'User found',
        'data' => $users[$id]
    ]);
});

// POST create user
$router->post('/users', function () use (&$users) {
    // Get input data
    $input = file_get_contents('php://input');
    parse_str($input, $data);

    $name = $data['name'] ?? 'Unknown';
    $email = $data['email'] ?? 'no-email@example.com';

    $newId = (string) (max(array_keys($users)) + 1);
    $users[$newId] = [
        'id' => (int) $newId,
        'name' => $name,
        'email' => $email
    ];

    jsonResponse([
        'message' => 'User created successfully',
        'data' => $users[$newId]
    ], 201);
});

// PUT update user (full update)
$router->put('/users/{id}', function ($id) use (&$users) {
    if (!isset($users[$id])) {
        jsonResponse([
            'error' => 'User not found',
            'id' => $id
        ], 404);
        return;
    }

    // Get input data
    $input = file_get_contents('php://input');
    parse_str($input, $data);

    $users[$id]['name'] = $data['name'] ?? $users[$id]['name'];
    $users[$id]['email'] = $data['email'] ?? $users[$id]['email'];

    jsonResponse([
        'message' => 'User updated successfully',
        'data' => $users[$id]
    ]);
});

// PATCH update user (partial update)
$router->patch('/users/{id}', function ($id) use (&$users) {
    if (!isset($users[$id])) {
        jsonResponse([
            'error' => 'User not found',
            'id' => $id
        ], 404);
        return;
    }

    $input = file_get_contents('php://input');
    parse_str($input, $data);

    // Only update provided fields
    if (isset($data['name'])) {
        $users[$id]['name'] = $data['name'];
    }
    if (isset($data['email'])) {
        $users[$id]['email'] = $data['email'];
    }

    jsonResponse([
        'message' => 'User patched successfully',
        'data' => $users[$id]
    ]);
});

// DELETE user
$router->delete('/users/{id}', function ($id) use (&$users) {
    if (!isset($users[$id])) {
        jsonResponse([
            'error' => 'User not found',
            'id' => $id
        ], 404);
        return;
    }

    $deletedUser = $users[$id];
    unset($users[$id]);

    jsonResponse([
        'message' => 'User deleted successfully',
        'data' => $deletedUser
    ]);
});

// Home route with API documentation
$router->get('/', function () {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>RESTful API Demo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 0 20px; }
        h1 { color: #2c3e50; }
        .endpoint { 
            background: #f8f9fa; 
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 15px;
        }
        .method { 
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            font-size: 0.85em;
            margin-right: 10px;
        }
        .get { background: #28a745; }
        .post { background: #007bff; }
        .put { background: #ffc107; color: #333; }
        .patch { background: #6c757d; }
        .delete { background: #dc3545; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>RESTful API with HTTP Methods</h1>
    <p>Test the following endpoints using curl:</p>
    
    <div class='endpoint'>
        <span class='method get'>GET</span>
        <strong>/users</strong><br>
        <code>curl http://localhost:8000/users</code>
    </div>
    
    <div class='endpoint'>
        <span class='method get'>GET</span>
        <strong>/users/{id}</strong><br>
        <code>curl http://localhost:8000/users/1</code>
    </div>
    
    <div class='endpoint'>
        <span class='method post'>POST</span>
        <strong>/users</strong><br>
        <code>curl -X POST http://localhost:8000/users -d 'name=John&email=john@test.com'</code>
    </div>
    
    <div class='endpoint'>
        <span class='method put'>PUT</span>
        <strong>/users/{id}</strong><br>
        <code>curl -X PUT http://localhost:8000/users/1 -d 'name=Updated Name'</code>
    </div>
    
    <div class='endpoint'>
        <span class='method patch'>PATCH</span>
        <strong>/users/{id}</strong><br>
        <code>curl -X PATCH http://localhost:8000/users/1 -d 'email=new@test.com'</code>
    </div>
    
    <div class='endpoint'>
        <span class='method delete'>DELETE</span>
        <strong>/users/{id}</strong><br>
        <code>curl -X DELETE http://localhost:8000/users/1</code>
    </div>
</body>
</html>";
});

// Dispatch the request
$router->dispatch();
