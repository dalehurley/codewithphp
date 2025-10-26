<?php

declare(strict_types=1);

namespace Controllers;

/**
 * Base Controller
 * 
 * All controllers extend this base class.
 */

abstract class Controller
{
    /**
     * Render a view
     */
    protected function view(string $view, array $data = []): void
    {
        // Extract data array to variables
        extract($data);

        // Build view path
        $viewPath = APP_PATH . "/views/{$view}.php";

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '{$view}' not found");
        }

        // Include layout with view
        require APP_PATH . '/views/layouts/main.php';
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url): never
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get request data
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Validate input is not empty
     */
    protected function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $this->input($field);

            if ($rule === 'required' && empty($value)) {
                $errors[$field] = "The {$field} field is required";
            }
        }

        return $errors;
    }
}
