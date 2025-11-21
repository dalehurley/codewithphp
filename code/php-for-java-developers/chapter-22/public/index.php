<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\CorsMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\AuthMiddleware;

// Create Slim app
$app = AppFactory::create();

// Add global middleware (applied to all routes)
$app->add(new CorsMiddleware());
$app->add(new LoggingMiddleware());

// Basic route
$app->get('/', function (Request $request, Response $response): Response {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to Slim API',
        'version' => '1.0.0'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Public route
$app->get('/public', function (Request $request, Response $response): Response {
    $response->getBody()->write(json_encode([
        'message' => 'This is a public route'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Protected route with auth middleware
$app->get('/protected', function (Request $request, Response $response): Response {
    $user = $request->getAttribute('user');
    $response->getBody()->write(json_encode([
        'message' => 'This is a protected route',
        'user' => $user
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add(new AuthMiddleware());

// User routes
$app->get('/users', function (Request $request, Response $response): Response {
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ];
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/users/{id}', function (Request $request, Response $response, array $args): Response {
    $id = (int)$args['id'];
    $user = ['id' => $id, 'name' => 'User ' . $id];
    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/users', function (Request $request, Response $response): Response {
    $data = json_decode($request->getBody()->getContents(), true);
    // In real app, save to database
    $newUser = ['id' => 3, 'name' => $data['name'] ?? 'Unknown'];
    $response->getBody()->write(json_encode($newUser));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/users/{id}', function (Request $request, Response $response, array $args): Response {
    $id = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);
    // In real app, update in database
    $updatedUser = ['id' => $id, 'name' => $data['name'] ?? 'Updated'];
    $response->getBody()->write(json_encode($updatedUser));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->delete('/users/{id}', function (Request $request, Response $response, array $args): Response {
    $id = (int)$args['id'];
    // In real app, delete from database
    return $response->withStatus(204);
});

// Route groups for API versioning
$app->group('/api/v1', function ($group) {
    $group->get('/status', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode(['status' => 'ok', 'version' => '1.0']));
        return $response->withHeader('Content-Type', 'application/json');
    });
});

$app->group('/api/v1/users', function ($group) {
    $group->get('', function (Request $request, Response $response): Response {
        // List users
        $response->getBody()->write(json_encode([['id' => 1, 'name' => 'Alice']]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    $group->get('/{id}', function (Request $request, Response $response, array $args): Response {
        // Get user
        $response->getBody()->write(json_encode(['id' => (int)$args['id'], 'name' => 'User']));
        return $response->withHeader('Content-Type', 'application/json');
    });
})->add(new AuthMiddleware());

// API v2 group
$app->group('/api/v2', function ($group) {
    $group->get('/status', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'version' => '2.0',
            'features' => ['enhanced', 'improved']
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
});

// Run application
$app->run();





