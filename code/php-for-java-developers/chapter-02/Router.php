<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    /**
     * Register a route
     */
    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Convenience methods
     */
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * Dispatch request
     */
    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $handler = $route['handler'];
                $handler();
                return;
            }
        }

        // No route found
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}

// Usage example
$router = new Router();

// Register routes
$router->get('/users', function() {
    echo json_encode(['users' => ['Alice', 'Bob', 'Charlie']]);
});

$router->get('/about', function() {
    echo json_encode(['version' => '1.0', 'name' => 'My API']);
});

$router->post('/users', function() {
    http_response_code(201);
    echo json_encode(['message' => 'User created']);
});

// Dispatch
header('Content-Type: application/json');
$router->dispatch('GET', '/users');
