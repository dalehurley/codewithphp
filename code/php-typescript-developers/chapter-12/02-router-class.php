<?php
declare(strict_types=1);

/**
 * Custom Router Class
 *
 * Build a simple Express-like router in PHP.
 */

class Router {
    private array $routes = [];

    public function get(string $path, callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    private function convertPathToRegex(string $path): string {
        // Convert /users/:id to /users/([^/]+)
        $pattern = preg_replace('#:([a-zA-Z]+)#', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}

// Usage
header('Content-Type: application/json');

$router = new Router();

// In-memory data
$users = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
];

// Define routes
$router->get('/api/users', function() use ($users) {
    echo json_encode(['data' => $users]);
});

$router->get('/api/users/:id', function($id) use ($users) {
    $user = array_values(array_filter($users, fn($u) => $u['id'] == $id))[0] ?? null;

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        return;
    }

    echo json_encode(['data' => $user]);
});

$router->post('/api/users', function() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required']);
        return;
    }

    $newUser = [
        'id' => 3,
        'name' => $data['name'],
        'email' => $data['email'],
    ];

    http_response_code(201);
    echo json_encode(['data' => $newUser]);
});

$router->put('/api/users/:id', function($id) use (&$users) {
    $data = json_decode(file_get_contents('php://input'), true);
    $index = array_search($id, array_column($users, 'id'));

    if ($index === false) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        return;
    }

    $users[$index] = array_merge($users[$index], $data);
    echo json_encode(['data' => $users[$index]]);
});

$router->delete('/api/users/:id', function($id) {
    http_response_code(204);
});

// Dispatch the request
$router->dispatch();
