<?php

declare(strict_types=1);

/**
 * Exercise 1: Create a Base Controller
 * 
 * Create an abstract base controller that all controllers can extend.
 * 
 * Requirements:
 * - Abstract Controller class in src/Core/Controller.php
 * - Protected view() method that wraps the global view() helper
 * - PageController extends Controller
 * - Controllers use $this->view() instead of global function
 * 
 * This demonstrates inheritance in MVC and provides a place to add
 * shared functionality like authentication, logging, etc.
 */

namespace App\Core;

/**
 * Abstract base controller that all controllers should extend
 * 
 * This provides common functionality and enforces a consistent
 * structure across all controllers in the application.
 */
abstract class Controller
{
    /**
     * Render a view with optional data
     * 
     * @param string $viewName The view file to render (without .php extension)
     * @param array $data Associative array of data to pass to the view
     */
    protected function view(string $viewName, array $data = []): void
    {
        // Extract data array to variables
        extract($data);

        // Build the view file path
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$viewName}");
        }

        // Include the view file
        require $viewPath;
    }

    /**
     * Redirect to another URL
     * 
     * @param string $url The URL to redirect to
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Return JSON response
     * 
     * @param array $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check if the request is a POST request
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if the request is a GET request
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Get POST data with optional default value
     * 
     * @param string $key The POST key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data with optional default value
     * 
     * @param string $key The GET key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}

// Example PageController extending the base Controller
namespace App\Controllers;

use App\Core\Controller;

class PageController extends Controller
{
    public function home(): void
    {
        $this->view('home', [
            'title' => 'Welcome Home',
            'message' => 'This page uses the base controller!'
        ]);
    }

    public function about(): void
    {
        $this->view('about', [
            'title' => 'About Us',
            'team' => ['Alice', 'Bob', 'Charlie']
        ]);
    }

    public function contact(): void
    {
        if ($this->isPost()) {
            $name = $this->post('name');
            $email = $this->post('email');

            // Process form data...
            $this->redirect('/thank-you');
        }

        $this->view('contact', [
            'title' => 'Contact Us'
        ]);
    }

    public function api(): void
    {
        $this->json([
            'message' => 'This is a JSON response',
            'status' => 'success',
            'data' => [
                'controller' => 'PageController',
                'method' => 'api'
            ]
        ]);
    }
}

// Example usage demonstration
echo "=== Base Controller Demo ===" . PHP_EOL . PHP_EOL;

echo "✓ Created abstract Controller class with:" . PHP_EOL;
echo "  - view() method for rendering views" . PHP_EOL;
echo "  - redirect() method for URL redirects" . PHP_EOL;
echo "  - json() method for JSON responses" . PHP_EOL;
echo "  - isPost() and isGet() helper methods" . PHP_EOL;
echo "  - post() and get() data accessors" . PHP_EOL . PHP_EOL;

echo "✓ PageController extends Controller and uses:" . PHP_EOL;
echo "  - \$this->view() instead of global view() function" . PHP_EOL;
echo "  - \$this->redirect() for redirections" . PHP_EOL;
echo "  - \$this->json() for API responses" . PHP_EOL;
echo "  - \$this->isPost() / isGet() for request type checking" . PHP_EOL . PHP_EOL;

echo "Benefits of this approach:" . PHP_EOL;
echo "  1. Shared functionality in one place" . PHP_EOL;
echo "  2. Easy to add authentication checks" . PHP_EOL;
echo "  3. Consistent interface across all controllers" . PHP_EOL;
echo "  4. Can add logging, caching, etc. in base class" . PHP_EOL;
