<?php
declare(strict_types=1);

/**
 * Input Validation Example
 *
 * Demonstrates input validation using respect/validation library.
 * Run: composer require respect/validation
 */

// Note: This is a demo of validation patterns.
// To run, you need: composer require respect/validation

header('Content-Type: application/json');

// Simple validation without external library
class Validator {
    private array $errors = [];

    public function validate(array $data, array $rules): bool {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $this->errors[$field][] = "{$field} is required";
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field][] = "{$field} must be a valid email";
                        }
                        break;

                    case 'min:3':
                        if (strlen((string)$value) < 3) {
                            $this->errors[$field][] = "{$field} must be at least 3 characters";
                        }
                        break;

                    case 'numeric':
                        if (!is_numeric($value)) {
                            $this->errors[$field][] = "{$field} must be numeric";
                        }
                        break;
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}

// Example API endpoint with validation
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'POST' && $path === '/api/users') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    $validator = new Validator();

    $isValid = $validator->validate($data, [
        'name' => ['required', 'min:3'],
        'email' => ['required', 'email'],
        'age' => ['numeric'],
    ]);

    if (!$isValid) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Validation failed',
            'details' => $validator->getErrors()
        ]);
        exit;
    }

    // Validation passed - create user
    $user = [
        'id' => 1,
        'name' => $data['name'],
        'email' => $data['email'],
        'age' => $data['age'] ?? null,
    ];

    http_response_code(201);
    echo json_encode([
        'message' => 'User created successfully',
        'data' => $user
    ]);
    exit;
}

// GET request - show validation example
if ($method === 'GET' && $path === '/api/users') {
    echo json_encode([
        'message' => 'POST to this endpoint with user data',
        'example' => [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'age' => 25
        ],
        'rules' => [
            'name' => 'required, min 3 characters',
            'email' => 'required, valid email',
            'age' => 'optional, must be numeric'
        ]
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
