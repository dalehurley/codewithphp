<?php

declare(strict_types=1);

/**
 * Exercise 3: Add a 404 Page
 * 
 * Improve user experience when visiting invalid routes.
 * 
 * Requirements:
 * - Create src/Views/404.php with friendly "Page not found" message
 * - Update router's dispatch() method to call view('404')
 * - Test by visiting non-existent pages
 */

// ============================================================================
// src/Views/404.php - Custom 404 View
// ============================================================================

function render404View(string $requestedUrl = '', string $message = 'Page Not Found'): string
{
    ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Page Not Found</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .error-container {
                background: white;
                padding: 60px 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                text-align: center;
                max-width: 600px;
                width: 100%;
            }

            .error-code {
                font-size: 120px;
                font-weight: bold;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1;
                margin-bottom: 20px;
            }

            .error-title {
                font-size: 2em;
                color: #2c3e50;
                margin-bottom: 15px;
            }

            .error-message {
                color: #7f8c8d;
                font-size: 1.1em;
                margin-bottom: 30px;
                line-height: 1.6;
            }

            .error-details {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                font-family: monospace;
                color: #555;
                word-break: break-all;
            }

            .actions {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                display: inline-block;
                padding: 15px 30px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
                transition: all 0.3s;
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            }

            .btn-secondary {
                background: #ecf0f1;
                color: #2c3e50;
            }

            .btn-secondary:hover {
                background: #d5dbdb;
            }

            .suggestions {
                margin-top: 40px;
                text-align: left;
            }

            .suggestions h3 {
                color: #2c3e50;
                margin-bottom: 15px;
            }

            .suggestions ul {
                list-style: none;
                padding: 0;
            }

            .suggestions li {
                padding: 10px 0;
                border-bottom: 1px solid #ecf0f1;
            }

            .suggestions li:last-child {
                border-bottom: none;
            }

            .suggestions a {
                color: #667eea;
                text-decoration: none;
            }

            .suggestions a:hover {
                text-decoration: underline;
            }

            @media (max-width: 600px) {
                .error-code {
                    font-size: 80px;
                }

                .error-title {
                    font-size: 1.5em;
                }

                .error-container {
                    padding: 40px 20px;
                }
            }
        </style>
    </head>

    <body>
        <div class="error-container">
            <div class="error-code">404</div>
            <h1 class="error-title">Oops! Page Not Found</h1>
            <p class="error-message">
                <?php echo htmlspecialchars($message); ?>
            </p>

            <?php if (!empty($requestedUrl)): ?>
                <div class="error-details">
                    Requested URL: <?php echo htmlspecialchars($requestedUrl); ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="/" class="btn btn-primary">Go to Homepage</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            </div>

            <div class="suggestions">
                <h3>Here are some helpful links:</h3>
                <ul>
                    <li><a href="/">🏠 Home</a></li>
                    <li><a href="/about">ℹ️ About Us</a></li>
                    <li><a href="/posts">📝 Blog Posts</a></li>
                    <li><a href="/contact">📧 Contact</a></li>
                </ul>
            </div>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}

// ============================================================================
// Updated Router with 404 view support
// ============================================================================

class RouterWith404
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

        // No route found - show custom 404 view
        $this->view404($uri);
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
     * Display custom 404 view instead of plain text
     */
    private function view404(string $requestedUrl): void
    {
        http_response_code(404);
        echo render404View(
            $requestedUrl,
            "The page you're looking for doesn't exist. It might have been moved or deleted."
        );
    }
}

// ============================================================================
// Demo
// ============================================================================

echo "=== Custom 404 Page Demo ===" . PHP_EOL . PHP_EOL;

echo "✓ Created custom 404 view (src/Views/404.php) with:" . PHP_EOL;
echo "  - Modern, attractive design with gradient" . PHP_EOL;
echo "  - Displays requested URL" . PHP_EOL;
echo "  - Action buttons (Go Home, Go Back)" . PHP_EOL;
echo "  - Helpful navigation links" . PHP_EOL;
echo "  - Responsive design for mobile devices" . PHP_EOL . PHP_EOL;

echo "✓ Updated Router::dispatch() method to:" . PHP_EOL;
echo "  - Call view('404') instead of echo \"404 Not Found\"" . PHP_EOL;
echo "  - Pass requested URL to the view" . PHP_EOL;
echo "  - Set proper HTTP 404 status code" . PHP_EOL . PHP_EOL;

echo "Benefits of custom 404 page:" . PHP_EOL;
echo "  1. Better user experience" . PHP_EOL;
echo "  2. Maintains site branding" . PHP_EOL;
echo "  3. Provides navigation options" . PHP_EOL;
echo "  4. Helps reduce bounce rate" . PHP_EOL;
echo "  5. More professional appearance" . PHP_EOL . PHP_EOL;

echo "Example HTML output saved to demonstrate the 404 view:" . PHP_EOL;
$example404Html = render404View('/nonexistent-page', "This is a demo 404 page");
echo "  Length: " . strlen($example404Html) . " characters" . PHP_EOL;
echo "  Includes: styling, error code, message, and navigation" . PHP_EOL;
