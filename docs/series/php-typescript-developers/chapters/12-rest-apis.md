---
title: "12: REST APIs - Express.js vs PHP Native"
description: "Build REST APIs in PHP using patterns familiar from Express.js. Learn routing, middleware, request/response handling, and validation without frameworks."
series: "php-typescript-developers"
chapter: 12
order: 12
difficulty: "Advanced"
prerequisites:
  - "/series/php-typescript-developers/chapters/11-async-in-php"
---

# REST APIs: Express.js vs PHP Native

## Overview

If you've built APIs with Express.js, you'll find PHP's approach familiar. This chapter shows you how to build REST APIs in native PHP, then compares it to using Slim Framework (PHP's Express equivalent). We'll cover routing, middleware, validation, and best practices.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Build REST APIs without frameworks
- ✅ Implement routing (like Express routes)
- ✅ Create middleware pipelines
- ✅ Handle JSON requests/responses
- ✅ Validate input data
- ✅ Use Slim Framework (PHP's Express)
- ✅ Apply RESTful best practices
- ✅ Handle errors properly

## Quick Comparison

| Feature | Express.js | PHP Native | Slim Framework |
|---------|-----------|------------|----------------|
| **Routing** | `app.get('/users')` | Manual routing | `$app->get('/users')` |
| **Middleware** | `app.use()` | Manual | `->add()` |
| **JSON Parsing** | `express.json()` | `json_decode()` | Automatic |
| **Response** | `res.json()` | `echo json_encode()` | `$response->withJson()` |
| **Parameters** | `req.params.id` | `$_GET`, `$_POST` | `$request->getAttribute()` |

## Basic REST API

### Express.js API

```javascript
// server.js
const express = require('express');
const app = express();

app.use(express.json());

// GET /api/users
app.get('/api/users', (req, res) => {
  const users = [
    { id: 1, name: 'Alice' },
    { id: 2, name: 'Bob' }
  ];
  res.json(users);
});

// GET /api/users/:id
app.get('/api/users/:id', (req, res) => {
  const id = parseInt(req.params.id);
  const user = { id, name: 'Alice' };
  res.json(user);
});

// POST /api/users
app.post('/api/users', (req, res) => {
  const { name, email } = req.body;
  const user = { id: 3, name, email };
  res.status(201).json(user);
});

app.listen(3000, () => {
  console.log('Server running on port 3000');
});
```

### PHP Native API

```php
<?php
// index.php
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple router
if ($method === 'GET' && $path === '/api/users') {
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ];
    echo json_encode($users);
    exit;
}

if ($method === 'GET' && preg_match('#^/api/users/(\d+)$#', $path, $matches)) {
    $id = (int) $matches[1];
    $user = ['id' => $id, 'name' => 'Alice'];
    echo json_encode($user);
    exit;
}

if ($method === 'POST' && $path === '/api/users') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user = [
        'id' => 3,
        'name' => $data['name'] ?? '',
        'email' => $data['email'] ?? ''
    ];
    http_response_code(201);
    echo json_encode($user);
    exit;
}

// 404 Not Found
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
```

**Run:**
```bash
php -S localhost:8000
```

## Building a Router

### Express.js Router

```javascript
const express = require('express');
const router = express.Router();

router.get('/', (req, res) => {
  res.json({ message: 'User list' });
});

router.post('/', (req, res) => {
  res.json({ message: 'User created' });
});

app.use('/api/users', router);
```

### PHP Router Class

```php
<?php
declare(strict_types=1);

class Router {
    private array $routes = [];

    public function get(string $path, callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    private function convertPathToRegex(string $path): string {
        // Convert /users/:id to /users/(\d+)
        $pattern = preg_replace('#:([a-zA-Z]+)#', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
```

**Usage:**
```php
<?php
require 'Router.php';

header('Content-Type: application/json');

$router = new Router();

$router->get('/api/users', function() {
    echo json_encode([
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ]);
});

$router->get('/api/users/:id', function($id) {
    echo json_encode(['id' => $id, 'name' => 'Alice']);
});

$router->post('/api/users', function() {
    $data = json_decode(file_get_contents('php://input'), true);
    http_response_code(201);
    echo json_encode(['id' => 3, 'name' => $data['name']]);
});

$router->dispatch();
```

## Middleware

### Express.js Middleware

```javascript
// Logging middleware
app.use((req, res, next) => {
  console.log(`${req.method} ${req.path}`);
  next();
});

// Auth middleware
const requireAuth = (req, res, next) => {
  const token = req.headers.authorization;
  if (!token) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  next();
};

app.get('/api/protected', requireAuth, (req, res) => {
  res.json({ message: 'Protected data' });
});
```

### PHP Middleware

```php
<?php
class Router {
    private array $middleware = [];

    public function use(callable $middleware): void {
        $this->middleware[] = $middleware;
    }

    public function dispatch(): void {
        // Run middleware chain
        $this->runMiddleware(0, function() {
            // Then dispatch route
            $this->matchRoute();
        });
    }

    private function runMiddleware(int $index, callable $next): void {
        if ($index >= count($this->middleware)) {
            $next();
            return;
        }

        $middleware = $this->middleware[$index];
        $middleware(function() use ($index, $next) {
            $this->runMiddleware($index + 1, $next);
        });
    }
}

// Usage
$router = new Router();

// Logging middleware
$router->use(function($next) {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];
    error_log("{$method} {$path}");
    $next();
});

// Auth middleware
$requireAuth = function($next) {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $next();
};

$router->use($requireAuth);

$router->get('/api/protected', function() {
    echo json_encode(['message' => 'Protected data']);
});
```

## Request/Response Objects

### Express.js

```javascript
app.post('/api/users', (req, res) => {
  // Request
  const body = req.body;          // Parsed JSON
  const query = req.query;        // Query params
  const params = req.params;      // Route params
  const headers = req.headers;    // Headers

  // Response
  res.status(201)
    .header('X-Custom', 'value')
    .json({ success: true });
});
```

### PHP Request/Response Classes

```php
<?php
class Request {
    public function body(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    public function query(string $key = null): mixed {
        return $key ? ($_GET[$key] ?? null) : $_GET;
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

    public function status(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(array $data): void {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

// Usage
$req = new Request();
$res = new Response();

$res->status(201)
    ->header('X-Custom', 'value')
    ->json(['success' => true]);
```

## Slim Framework (PHP's Express)

Slim is a microframework inspired by Sinatra/Express:

### Installation

```bash
composer require slim/slim slim/psr7
```

### Slim API

```php
<?php
require 'vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

// GET /api/users
$app->get('/api/users', function (Request $request, Response $response) {
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ];

    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /api/users/{id}
$app->get('/api/users/{id}', function (Request $request, Response $response, $args) {
    $id = (int) $args['id'];
    $user = ['id' => $id, 'name' => 'Alice'];

    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST /api/users
$app->post('/api/users', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    $user = [
        'id' => 3,
        'name' => $data['name'] ?? '',
        'email' => $data['email'] ?? ''
    ];

    $response->getBody()->write(json_encode($user));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->run();
```

**Run:**
```bash
php -S localhost:8000 -t public/
```

## Validation

### Express.js with Joi

```javascript
const Joi = require('joi');

const userSchema = Joi.object({
  name: Joi.string().required(),
  email: Joi.string().email().required(),
  age: Joi.number().min(18)
});

app.post('/api/users', (req, res) => {
  const { error } = userSchema.validate(req.body);
  if (error) {
    return res.status(400).json({ errors: error.details });
  }

  // Create user...
  res.json({ success: true });
});
```

### PHP with respect/validation

```bash
composer require respect/validation
```

```php
<?php
use Respect\Validation\Validator as v;

$app->post('/api/users', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);

    $validator = v::key('name', v::stringType()->notEmpty())
        ->key('email', v::email())
        ->key('age', v::intType()->min(18));

    try {
        $validator->assert($data);
    } catch (\Respect\Validation\Exceptions\ValidationException $e) {
        $response->getBody()->write(json_encode([
            'errors' => $e->getMessages()
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // Create user...
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
```

## Error Handling

### Express.js Error Middleware

```javascript
// Error handler (must be last)
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({
    error: 'Internal Server Error',
    message: err.message
  });
});
```

### Slim Error Handling

```php
<?php
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;

$customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    $payload = [
        'error' => get_class($exception),
        'message' => $exception->getMessage()
    ];

    $response->getBody()->write(json_encode($payload));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(500);
};

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);
```

## RESTful Best Practices

### Resource Naming

```javascript
// ✅ Good
GET    /api/users         // List users
GET    /api/users/1       // Get user
POST   /api/users         // Create user
PUT    /api/users/1       // Update user
DELETE /api/users/1       // Delete user

// ❌ Bad
GET    /api/getUsers
POST   /api/createUser
GET    /api/user_detail/1
```

### HTTP Status Codes

| Code | Meaning | When to Use |
|------|---------|-------------|
| **200** | OK | Successful GET, PUT, PATCH, DELETE |
| **201** | Created | Successful POST |
| **204** | No Content | Successful DELETE (no body) |
| **400** | Bad Request | Validation error |
| **401** | Unauthorized | Missing/invalid auth |
| **403** | Forbidden | Auth valid but insufficient permissions |
| **404** | Not Found | Resource doesn't exist |
| **422** | Unprocessable Entity | Validation error (alternative to 400) |
| **500** | Internal Server Error | Server error |

### Response Format

**Successful Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Alice",
    "email": "alice@example.com"
  }
}
```

**Error Response:**
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": [
      {
        "field": "email",
        "message": "Invalid email format"
      }
    ]
  }
}
```

### CORS Handling

**Express.js:**
```javascript
const cors = require('cors');
app.use(cors());
```

**PHP:**
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
```

## Complete CRUD Example

### Express.js

```javascript
const express = require('express');
const app = express();
app.use(express.json());

let users = [
  { id: 1, name: 'Alice', email: 'alice@example.com' },
  { id: 2, name: 'Bob', email: 'bob@example.com' }
];
let nextId = 3;

// List
app.get('/api/users', (req, res) => {
  res.json({ data: users });
});

// Get
app.get('/api/users/:id', (req, res) => {
  const user = users.find(u => u.id === parseInt(req.params.id));
  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }
  res.json({ data: user });
});

// Create
app.post('/api/users', (req, res) => {
  const user = { id: nextId++, ...req.body };
  users.push(user);
  res.status(201).json({ data: user });
});

// Update
app.put('/api/users/:id', (req, res) => {
  const index = users.findIndex(u => u.id === parseInt(req.params.id));
  if (index === -1) {
    return res.status(404).json({ error: 'User not found' });
  }
  users[index] = { ...users[index], ...req.body };
  res.json({ data: users[index] });
});

// Delete
app.delete('/api/users/:id', (req, res) => {
  const index = users.findIndex(u => u.id === parseInt(req.params.id));
  if (index === -1) {
    return res.status(404).json({ error: 'User not found' });
  }
  users.splice(index, 1);
  res.status(204).send();
});

app.listen(3000);
```

### Slim Framework

```php
<?php
require 'vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$users = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
];
$nextId = 3;

// List
$app->get('/api/users', function (Request $request, Response $response) use (&$users) {
    $response->getBody()->write(json_encode(['data' => $users]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Get
$app->get('/api/users/{id}', function (Request $request, Response $response, $args) use (&$users) {
    $id = (int) $args['id'];
    $user = array_values(array_filter($users, fn($u) => $u['id'] === $id))[0] ?? null;

    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode(['data' => $user]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Create
$app->post('/api/users', function (Request $request, Response $response) use (&$users, &$nextId) {
    $data = $request->getParsedBody();
    $user = ['id' => $nextId++] + $data;
    $users[] = $user;

    $response->getBody()->write(json_encode(['data' => $user]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

// Update
$app->put('/api/users/{id}', function (Request $request, Response $response, $args) use (&$users) {
    $id = (int) $args['id'];
    $data = $request->getParsedBody();

    $index = array_search($id, array_column($users, 'id'));
    if ($index === false) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $users[$index] = array_merge($users[$index], $data);

    $response->getBody()->write(json_encode(['data' => $users[$index]]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Delete
$app->delete('/api/users/{id}', function (Request $request, Response $response, $args) use (&$users) {
    $id = (int) $args['id'];
    $index = array_search($id, array_column($users, 'id'));

    if ($index === false) {
        $response->getBody()->write(json_encode(['error' => 'User not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    array_splice($users, $index, 1);

    return $response->withStatus(204);
});

$app->run();
```

## Key Takeaways

1. **PHP can build REST APIs** just like Express.js with similar patterns
2. **Slim Framework** is PHP's Express equivalent - lightweight and minimal
3. **Routing patterns** are nearly identical (verb + path + handler)
4. **Middleware** works the same way (request → middleware chain → response)
5. **PSR-7** provides standard request/response objects across frameworks
6. **Native PHP** is verbose but gives full control without framework overhead
7. **For production**, use Laravel or Symfony for full-featured APIs
8. **Laravel API resources** transform models to JSON (like Express serializers)
9. **Route model binding** automatically injects models by ID (Laravel feature)
10. **API versioning** typically done via URL prefix (`/api/v1/`) or headers
11. **CORS middleware** needed for frontend consumption - built into most frameworks
12. **Use `apiResource`** in Laravel for instant RESTful routes (index, store, show, update, destroy)

## Comparison Summary

| Feature | Express.js | Slim Framework |
|---------|-----------|----------------|
| **Setup** | `npm install express` | `composer require slim/slim` |
| **Routing** | `app.get('/users')` | `$app->get('/users')` |
| **Middleware** | `app.use(middleware)` | `$app->add(middleware)` |
| **Request** | `req.body` | `$request->getParsedBody()` |
| **Response** | `res.json()` | `$response->withJson()` |
| **Error Handling** | Error middleware | Error middleware |
| **Ecosystem** | Large | Moderate |

## Next Steps

Now that you understand REST APIs, let's dive into Laravel—PHP's most popular framework.

**Next Chapter:** [13: Laravel Foundations: The PHP Framework](/series/php-typescript-developers/chapters/13-laravel-foundations)

## Resources

- [Slim Framework](https://www.slimframework.com/)
- [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7/)
- [FastRoute](https://github.com/nikic/FastRoute) - Router library
- [Respect/Validation](https://respect-validation.readthedocs.io/)
- [REST API Design Best Practices](https://restfulapi.net/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
