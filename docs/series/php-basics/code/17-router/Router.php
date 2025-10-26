<?php

declare(strict_types=1);

/**
 * Simple HTTP Router
 * 
 * Routes HTTP requests to appropriate handlers based on URL patterns.
 */

class Router
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
     * Register a route for any method
     */
    public function addRoute(string $method, string $path, callable $handler): void
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
        // CLI compatibility: Use mock data if not in web context
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $uri = strtok($uri, '?') ?: '/';

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
     * 
     * @return array|null Parameters if matched, null otherwise
     */
    private function match(string $pattern, string $uri): ?array
    {
        // Exact match
        if ($pattern === $uri) {
            return [];
        }

        // Convert route pattern to regex
        // {id} becomes a capture group
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $uri, $matches)) {
            // Extract named parameters
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
        echo "404 Not Found";
    }
}
