<?php

declare(strict_types=1);

/**
 * Exercise 4: Improve 404 Handling (Intermediate)
 * 
 * Modify router to display a custom HTML 404 page:
 * 
 * Requirements:
 * - Custom 404 page with helpful information
 * - Display the requested URL
 * - Suggest available routes
 * - Include styling for better UX
 */

class ImprovedRouter
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
        $uri = $_SERVER['REQUEST_URI'];
        $uri = strtok($uri, '?');

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

        // Custom 404 handler
        $this->handleNotFound($uri, $method);
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

    /**
     * Display custom 404 page with helpful information
     */
    private function handleNotFound(string $uri, string $method): void
    {
        http_response_code(404);

        // Get available routes for the current method
        $availableRoutes = array_filter(
            $this->routes,
            fn($route) => $route['method'] === $method
        );

        // Get all routes
        $allRoutes = $this->routes;

        echo "<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 0 20px;
            background: #f5f5f5;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 6em;
            color: #e74c3c;
            margin: 0;
            font-weight: bold;
        }
        .error-message {
            font-size: 1.5em;
            color: #2c3e50;
            margin: 10px 0;
        }
        .error-details {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-family: monospace;
        }
        .error-label {
            font-weight: bold;
            color: #7f8c8d;
        }
        .routes-section {
            margin-top: 30px;
        }
        .routes-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .route-list {
            list-style: none;
            padding: 0;
        }
        .route-item {
            background: #f8f9fa;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }
        .route-method {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
            margin-right: 10px;
        }
        .route-path {
            font-family: monospace;
            color: #2c3e50;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
        }
        .back-link:hover { text-decoration: underline; }
        .tip {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .tip strong { color: #856404; }
    </style>
</head>
<body>
    <div class='error-container'>
        <div class='error-code'>404</div>
        <div class='error-message'>Page Not Found</div>
        
        <div class='error-details'>
            <div><span class='error-label'>Requested URL:</span> " . htmlspecialchars($uri) . "</div>
            <div><span class='error-label'>Request Method:</span> {$method}</div>
            <div><span class='error-label'>Server Time:</span> " . date('Y-m-d H:i:s') . "</div>
        </div>
        
        <p>The page you're looking for doesn't exist. It might have been moved or deleted.</p>";

        if (!empty($availableRoutes)) {
            echo "
        <div class='routes-section'>
            <h3>Available {$method} Routes</h3>
            <ul class='route-list'>";

            foreach ($availableRoutes as $route) {
                echo "
                <li class='route-item'>
                    <span class='route-method'>{$route['method']}</span>
                    <span class='route-path'>{$route['path']}</span>
                </li>";
            }

            echo "
            </ul>
        </div>";
        }

        // Show all routes if there are routes for other methods
        $otherMethodRoutes = array_filter(
            $allRoutes,
            fn($route) => $route['method'] !== $method
        );

        if (!empty($otherMethodRoutes)) {
            echo "
        <div class='routes-section'>
            <h3>Other Available Routes</h3>
            <ul class='route-list'>";

            foreach ($otherMethodRoutes as $route) {
                echo "
                <li class='route-item'>
                    <span class='route-method'>{$route['method']}</span>
                    <span class='route-path'>{$route['path']}</span>
                </li>";
            }

            echo "
            </ul>
        </div>";
        }

        echo "
        <div class='tip'>
            <strong>💡 Tip:</strong> Check the URL for typos, or use the links above to navigate to a valid page.
        </div>
        
        <a href='/' class='back-link'>← Go to Home Page</a>
    </div>
</body>
</html>";
    }
}

// Test the improved 404 handling
$router = new ImprovedRouter();

$router->get('/', function () {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; text-align: center; }
        h1 { color: #2c3e50; }
        .links { margin-top: 30px; }
        a { 
            display: inline-block;
            margin: 10px;
            padding: 15px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover { background: #2980b9; }
        .test-link {
            background: #e74c3c;
        }
        .test-link:hover { background: #c0392b; }
    </style>
</head>
<body>
    <h1>404 Error Handling Demo</h1>
    <p>Test the improved 404 error page by clicking invalid links below:</p>
    
    <div class='links'>
        <a href='/about'>Valid: About Page</a>
        <a href='/contact'>Valid: Contact Page</a>
        <a href='/invalid-page' class='test-link'>Test: Invalid Page</a>
        <a href='/this/does/not/exist' class='test-link'>Test: Nested Invalid</a>
    </div>
</body>
</html>";
});

$router->get('/about', function () {
    echo "<h1>About Page</h1><p>This is a valid page.</p><a href='/'>← Home</a>";
});

$router->get('/contact', function () {
    echo "<h1>Contact Page</h1><p>This is a valid page.</p><a href='/'>← Home</a>";
});

$router->post('/api/submit', function () {
    echo json_encode(['message' => 'POST endpoint']);
});

$router->dispatch();
