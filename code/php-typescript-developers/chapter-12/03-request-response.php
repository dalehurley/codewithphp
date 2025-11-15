<?php
declare(strict_types=1);

/**
 * Request/Response Objects
 *
 * Clean request and response handling similar to Express.js.
 */

class Request {
    public function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    public function query(string $key = null): mixed {
        return $key ? ($_GET[$key] ?? null) : $_GET;
    }

    public function param(string $key): ?string {
        return $_GET[$key] ?? $_POST[$key] ?? null;
    }

    public function header(string $name): ?string {
        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$name] ?? null;
    }

    public function method(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function path(): string {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
}

class Response {
    private int $statusCode = 200;
    private array $headers = [];
    private bool $sent = false;

    public function status(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(array $data): void {
        if ($this->sent) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        header('Content-Type: application/json');
        echo json_encode($data);

        $this->sent = true;
    }

    public function send(string $data): void {
        if ($this->sent) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $data;
        $this->sent = true;
    }
}

// Usage example
$req = new Request();
$res = new Response();

$users = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
];

$method = $req->method();
$path = $req->path();

// GET /api/users
if ($method === 'GET' && $path === '/api/users') {
    $res->json(['data' => $users]);
    exit;
}

// GET /api/users/:id
if ($method === 'GET' && preg_match('#^/api/users/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $user = array_values(array_filter($users, fn($u) => $u['id'] === $id))[0] ?? null;

    if (!$user) {
        $res->status(404)->json(['error' => 'User not found']);
        exit;
    }

    $res->json(['data' => $user]);
    exit;
}

// POST /api/users
if ($method === 'POST' && $path === '/api/users') {
    $data = $req->body();

    if (empty($data['name']) || empty($data['email'])) {
        $res->status(400)->json([
            'error' => 'Validation failed',
            'details' => [
                'name' => empty($data['name']) ? 'Name is required' : null,
                'email' => empty($data['email']) ? 'Email is required' : null,
            ]
        ]);
        exit;
    }

    $newUser = [
        'id' => count($users) + 1,
        'name' => $data['name'],
        'email' => $data['email'],
    ];

    $res->status(201)
        ->header('X-Resource-Id', (string) $newUser['id'])
        ->json(['data' => $newUser]);
    exit;
}

// 404 Not Found
$res->status(404)->json(['error' => 'Endpoint not found']);
