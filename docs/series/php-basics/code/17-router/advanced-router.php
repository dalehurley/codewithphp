<?php

declare(strict_types=1);

/**
 * Advanced Router with Middleware Support
 * 
 * Extended router with middleware, named routes, and redirect support.
 */

class AdvancedRouter
{
    private array $routes = [];
    private array $middleware = [];
    private array $namedRoutes = [];

    /**
     * Register a GET route
     */
    public function get(string $path, callable $handler, ?string $name = null): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable $handler, ?string $name = null): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, callable $handler, ?string $name = null): self
    {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, callable $handler, ?string $name = null): self
    {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    /**
     * Add a route
     */
    private function addRoute(string $method, string $path, callable $handler, ?string $name): self
    {
        $route = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => []
        ];

        $this->routes[] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $path;
        }

        return $this;
    }

    /**
     * Add global middleware
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Add middleware to last registered route
     */
    public function middleware(callable $middleware): self
    {
        $lastIndex = count($this->routes) - 1;
        if ($lastIndex >= 0) {
            $this->routes[$lastIndex]['middleware'][] = $middleware;
        }
        return $this;
    }

    /**
     * Generate URL for named route
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RuntimeException("Route '$name' not found");
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }

        return $path;
    }

    /**
     * Redirect to URL
     */
    public function redirect(string $url, int $code = 302): never
    {
        header("Location: $url", true, $code);
        exit;
    }

    /**
     * Dispatch the request
     */
    public function dispatch(): void
    {
        // CLI compatibility: Use mock data if not in web context
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $uri = strtok($uri, '?') ?: '/';

        // Run global middleware
        foreach ($this->middleware as $middleware) {
            $result = $middleware();
            if ($result === false) {
                return;
            }
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $uri);

            if ($params !== null) {
                // Run route middleware
                foreach ($route['middleware'] as $middleware) {
                    $result = $middleware();
                    if ($result === false) {
                        return;
                    }
                }

                // Execute handler
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // No route found
        $this->send404();
    }

    /**
     * Match route pattern
     */
    private function match(string $pattern, string $uri): ?array
    {
        if ($pattern === $uri) {
            return [];
        }

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
        echo "<h1>404 Not Found</h1>";
        echo "<p>The page you requested could not be found.</p>";
    }

    /**
     * JSON response
     */
    public function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Demo Usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $router = new AdvancedRouter();

    // Global middleware (runs for all routes)
    $router->addMiddleware(function () {
        // Simulate checking authentication
        // return false would stop execution
        return true;
    });

    // Named route with middleware
    $router->get('/', function () {
        echo "<h1>Home</h1>";
        echo "<p>This is protected by middleware</p>";
    }, 'home')
        ->middleware(function () {
            // Route-specific middleware
            echo "<!-- Middleware executed -->" . PHP_EOL;
            return true;
        });

    // API endpoint returning JSON
    $router->get('/api/users', function () use ($router) {
        $router->json([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob']
        ]);
    });

    // Generate URL from named route
    $router->get('/test', function () use ($router) {
        $homeUrl = $router->url('home');
        echo "<p>Home URL: <a href='$homeUrl'>$homeUrl</a></p>";
    });

    $router->dispatch();
}
