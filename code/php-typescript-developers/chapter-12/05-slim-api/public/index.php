<?php
declare(strict_types=1);

/**
 * Slim Framework Complete CRUD API
 *
 * Full-featured REST API using Slim Framework (PHP's Express equivalent).
 */

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

// Add middleware
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// In-memory data store
$users = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
];
$nextId = 3;

// CORS middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// OPTIONS requests
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response->withStatus(204);
});

// GET /api/users - List all users
$app->get('/api/users', function (Request $request, Response $response) use (&$users) {
    $response->getBody()->write(json_encode(['data' => $users]));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /api/users/{id} - Get specific user
$app->get('/api/users/{id}', function (Request $request, Response $response, array $args) use (&$users) {
    $id = (int) $args['id'];
    $user = array_values(array_filter($users, fn($u) => $u['id'] === $id))[0] ?? null;

    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode(['data' => $user]));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /api/users - Create new user
$app->post('/api/users', function (Request $request, Response $response) use (&$users, &$nextId) {
    $data = $request->getParsedBody();

    // Validation
    if (empty($data['name']) || empty($data['email'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Validation failed',
            'details' => [
                'name' => empty($data['name']) ? 'Name is required' : null,
                'email' => empty($data['email']) ? 'Email is required' : null,
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $newUser = [
        'id' => $nextId++,
        'name' => $data['name'],
        'email' => $data['email'],
    ];

    $users[] = $newUser;

    $response->getBody()->write(json_encode(['data' => $newUser]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// PUT /api/users/{id} - Update user
$app->put('/api/users/{id}', function (Request $request, Response $response, array $args) use (&$users) {
    $id = (int) $args['id'];
    $data = $request->getParsedBody();

    $index = array_search($id, array_column($users, 'id'));

    if ($index === false) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    // Update user
    if (isset($data['name'])) {
        $users[$index]['name'] = $data['name'];
    }
    if (isset($data['email'])) {
        $users[$index]['email'] = $data['email'];
    }

    $response->getBody()->write(json_encode(['data' => $users[$index]]));
    return $response->withHeader('Content-Type', 'application/json');
});

// DELETE /api/users/{id} - Delete user
$app->delete('/api/users/{id}', function (Request $request, Response $response, array $args) use (&$users) {
    $id = (int) $args['id'];
    $index = array_search($id, array_column($users, 'id'));

    if ($index === false) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    array_splice($users, $index, 1);

    return $response->withStatus(204);
});

// Run application
$app->run();
