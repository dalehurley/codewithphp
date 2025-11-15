<?php
declare(strict_types=1);

/**
 * Middleware Pipeline
 *
 * Implement Express-style middleware in PHP.
 */

class Router {
    private array $routes = [];
    private array $middleware = [];

    public function use(callable $middleware): void {
        $this->middleware[] = $middleware;
    }

    public function get(string $path, callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $this->runMiddleware(0, function() {
            $this->matchRoute();
        });
    }

    private function runMiddleware(int $index, callable $next): void {
        if ($index >= count($this->middleware)) {
            $next();
            return;
        }

        $middleware = $this->middleware[$index];
        $middleware(function() use ($index, $next) {
            $this->runMiddleware($index + 1, $next);
        });
    }

    private function matchRoute(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = '#^' . $route['path'] . '$#';
            if (preg_match($pattern, $path)) {
                call_user_func($route['handler']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}

// Middleware examples
function loggingMiddleware(callable $next): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];
    $timestamp = date('Y-m-d H:i:s');

    error_log("[{$timestamp}] {$method} {$path}");
    echo "<!-- Request logged: {$method} {$path} -->\n";

    $next();
}

function corsMiddleware(callable $next): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $next();
}

function authMiddleware(callable $next): void {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (!$token) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized - No token provided']);
        exit;
    }

    // Simple token validation (in real app, validate JWT, etc.)
    if ($token !== 'Bearer valid-token') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized - Invalid token']);
        exit;
    }

    $next();
}

function jsonMiddleware(callable $next): void {
    header('Content-Type: application/json');
    $next();
}

// Setup router
$router = new Router();

// Global middleware (runs for all routes)
$router->use('loggingMiddleware');
$router->use('corsMiddleware');
$router->use('jsonMiddleware');

// Public route (no auth required)
$router->get('/api/public', function() {
    echo json_encode(['message' => 'This is a public endpoint']);
});

// Protected route (requires auth)
$router->get('/api/protected', function() {
    // Add auth middleware just for this route
    authMiddleware(function() {
        echo json_encode([
            'message' => 'This is a protected endpoint',
            'data' => ['secret' => 'confidential information']
        ]);
    });
});

// Another public route
$router->get('/api/users', function() {
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
    ];
    echo json_encode(['data' => $users]);
});

// Dispatch
$router->dispatch();
