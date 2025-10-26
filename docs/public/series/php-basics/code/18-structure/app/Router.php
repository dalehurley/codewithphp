<?php

declare(strict_types=1);

/**
 * Simple Router
 */

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = strtok($_SERVER['REQUEST_URI'], '?');

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

        $this->send404();
    }

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

    private function send404(): void
    {
        http_response_code(404);
        require APP_PATH . '/views/404.php';
    }
}
