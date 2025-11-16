---
title: "22: Micro-frameworks (Slim)"
description: "Build lightweight APIs and microservices with Slim framework, PHP's equivalent to Spark"
series: "php-for-java-developers"
chapter: 22
order: 22
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/21-symfony-components"
---

![Micro-frameworks (Slim)](/images/php-for-java-developers/chapter-22-micro-frameworks-slim-hero-full.webp)

# Chapter 22: Micro-frameworks (Slim)

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Slim is a micro-framework for PHP that's perfect for building lightweight APIs and microservices. If you're coming from Java, think of Slim as PHP's equivalent to Spark or Javalin—minimal, fast, and focused on doing one thing well: handling HTTP requests and responses efficiently.

Unlike full-stack frameworks like Laravel or Symfony, Slim provides just the essentials: routing, middleware, dependency injection, and PSR-7 HTTP message handling. This makes it ideal for microservices, APIs, and small applications where you don't need the overhead of a full framework. Slim follows the "convention over configuration" philosophy but gives you more control than heavier frameworks.

In this chapter, you'll learn how to build production-ready APIs with Slim. We'll start with basic routing and request handling, then move through middleware, dependency injection, error handling, and advanced features like route groups and custom containers. You'll build a complete REST API with authentication, validation, and proper error responses. By the end, you'll understand when to choose Slim over full frameworks and how to structure Slim applications for maintainability.

**What You'll Learn:**

- Slim framework fundamentals and architecture
- Routing with HTTP methods and route parameters
- Middleware for cross-cutting concerns (auth, logging, CORS)
- Dependency injection with Pimple container
- PSR-7 request/response handling
- Error handling and custom error responses
- Building RESTful APIs with Slim
- Comparing Slim to Java micro-frameworks (Spark, Javalin)

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- PHP namespaces and autoloading (Chapter 6)
- Object-oriented programming (Chapters 3-5)
- Composer dependency management (Chapter 8)
- HTTP fundamentals (methods, headers, status codes)
- REST API principles (Chapter 10)
- Symfony Components, especially HttpFoundation (Chapter 21)

**Verify your setup:**

```bash
# Check PHP version
php --version  # Should be PHP 8.4+

# Check Composer
composer --version

# Create project directory
mkdir slim-api && cd slim-api
```

## What You'll Build

By the end of this chapter, you will have created:

- A complete REST API for a task management system
- Route handlers for CRUD operations
- Authentication middleware using JWT tokens
- Request validation middleware
- Error handling with custom error responses
- Dependency injection container setup
- Route groups for API versioning
- A production-ready Slim application structure

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Set up Slim applications** with proper project structure and dependencies
2. **Define routes** using HTTP methods and route parameters
3. **Implement middleware** for authentication, logging, and CORS
4. **Use dependency injection** with Pimple container
5. **Handle errors gracefully** with custom error handlers
6. **Build RESTful APIs** following best practices
7. **Structure Slim applications** for maintainability and scalability
8. **Compare Slim** to Java micro-frameworks and understand when to use it

## Quick Start

Get started with Slim in 5 minutes:

```bash
# Create project
mkdir slim-demo && cd slim-demo
composer init --no-interaction --name="demo/slim-api"

# Install Slim
composer require slim/slim:"^4.12" slim/psr7

# Create entry point
cat > public/index.php << 'EOF'
<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

$app->get('/hello/{name}', function (Request $request, Response $response, array $args): Response {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name!");
    return $response;
});

$app->run();
EOF

# Run server
php -S localhost:8000 -t public
```

Visit `http://localhost:8000/hello/world` to see your first Slim application!

---

## Step 1: Setting Up Slim (~10 min)

### Goal

Install Slim and create a basic application structure.

### Actions

1. **Create a new project**:

```bash
# Create project directory
mkdir slim-api && cd slim-api

# Initialize Composer
composer init --no-interaction --name="demo/slim-api" \
  --description="Slim micro-framework API demo" \
  --type="project" \
  --require="php:^8.4"
```

2. **Install Slim framework**:

```bash
# Install Slim and PSR-7 implementation
composer require slim/slim:"^4.12" slim/psr7

# Optional: Install additional packages for this chapter
composer require php-di/php-di:"^7.0"  # Dependency injection
composer require firebase/php-jwt:"^6.0"  # JWT authentication
```

3. **Create project structure**:

```bash
# Create directory structure
mkdir -p public src/Controllers src/Middleware src/Services config
```

4. **Create entry point**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Create Slim app
$app = AppFactory::create();

// Basic route
$app->get('/', function (Request $request, Response $response): Response {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to Slim API',
        'version' => '1.0.0'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run application
$app->run();
```

5. **Create `.htaccess` for Apache** (optional):

```apache
# filename: public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

### Expected Result

```bash
# Run the server
php -S localhost:8000 -t public

# Test in another terminal
curl http://localhost:8000/
```

Output:

```json
{"message":"Welcome to Slim API","version":"1.0.0"}
```

### Why It Works

Slim uses PSR-7 (HTTP message interfaces) for request and response handling, making it framework-agnostic and interoperable with other PSR-7 components. The `AppFactory::create()` method creates a new Slim application instance with sensible defaults. Routes are defined using HTTP method helpers (`get()`, `post()`, etc.) that accept a path pattern and a callable handler.

The handler receives three parameters: `Request` (PSR-7 request), `Response` (PSR-7 response), and `$args` (route parameters). You modify the response and return it, which Slim then sends to the client.

### Troubleshooting

- **Error: "Class 'Slim\Factory\AppFactory' not found"** — Run `composer install` to install dependencies
- **Error: "404 Not Found"** — Ensure you're accessing the correct URL and routes are defined
- **Error: "500 Internal Server Error"** — Check PHP error logs and ensure all dependencies are installed

---

## Step 2: Routing and Route Parameters (~15 min)

### Goal

Learn how to define routes with different HTTP methods and extract route parameters.

### Actions

1. **Define routes for different HTTP methods**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

// GET route
$app->get('/users', function (Request $request, Response $response): Response {
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ];
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET route with parameter
$app->get('/users/{id}', function (Request $request, Response $response, array $args): Response {
    $id = (int)$args['id'];
    $user = ['id' => $id, 'name' => 'User ' . $id];
    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST route
$app->post('/users', function (Request $request, Response $response): Response {
    $data = json_decode($request->getBody()->getContents(), true);
    // In real app, save to database
    $newUser = ['id' => 3, 'name' => $data['name'] ?? 'Unknown'];
    $response->getBody()->write(json_encode($newUser));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

// PUT route
$app->put('/users/{id}', function (Request $request, Response $response, array $args): Response {
    $id = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);
    // In real app, update in database
    $updatedUser = ['id' => $id, 'name' => $data['name'] ?? 'Updated'];
    $response->getBody()->write(json_encode($updatedUser));
    return $response->withHeader('Content-Type', 'application/json');
});

// DELETE route
$app->delete('/users/{id}', function (Request $request, Response $response, array $args): Response {
    $id = (int)$args['id'];
    // In real app, delete from database
    return $response->withStatus(204);
});

$app->run();
```

2. **Use route parameters with constraints**:

```php
# filename: public/index.php
// ... existing code ...

// Route with optional parameter
$app->get('/posts[/{id}]', function (Request $request, Response $response, array $args): Response {
    if (isset($args['id'])) {
        // Return specific post
        $post = ['id' => (int)$args['id'], 'title' => 'Post ' . $args['id']];
    } else {
        // Return all posts
        $post = [
            ['id' => 1, 'title' => 'First Post'],
            ['id' => 2, 'title' => 'Second Post']
        ];
    }
    $response->getBody()->write(json_encode($post));
    return $response->withHeader('Content-Type', 'application/json');
});

// Multiple parameters
$app->get('/users/{userId}/posts/{postId}', function (Request $request, Response $response, array $args): Response {
    $userId = (int)$args['userId'];
    $postId = (int)$args['postId'];
    $post = [
        'userId' => $userId,
        'postId' => $postId,
        'title' => "Post $postId by User $userId"
    ];
    $response->getBody()->write(json_encode($post));
    return $response->withHeader('Content-Type', 'application/json');
});
```

3. **Test the routes**:

```bash
# Start server
php -S localhost:8000 -t public

# Test GET all users
curl http://localhost:8000/users

# Test GET specific user
curl http://localhost:8000/users/1

# Test POST user
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Charlie"}'

# Test PUT user
curl -X PUT http://localhost:8000/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice Updated"}'

# Test DELETE user
curl -X DELETE http://localhost:8000/users/1
```

### Expected Result

```json
# GET /users
[{"id":1,"name":"Alice"},{"id":2,"name":"Bob"}]

# GET /users/1
{"id":1,"name":"User 1"}

# POST /users
{"id":3,"name":"Charlie"}  # Status: 201

# PUT /users/1
{"id":1,"name":"Alice Updated"}

# DELETE /users/1
# Status: 204 (No Content)
```

### Why It Works

Slim uses FastRoute under the hood for routing, which supports route parameters defined with `{param}` syntax. Parameters are automatically extracted and passed to your handler in the `$args` array. Optional parameters use square brackets `[/{id}]`, meaning the route matches both `/posts` and `/posts/123`.

HTTP method helpers (`get()`, `post()`, `put()`, `delete()`, `patch()`, `options()`) are shortcuts for `map(['GET'], ...)`. You can also use `map()` to handle multiple methods for the same route.

### Troubleshooting

- **Error: "Route not found"** — Check route path matches exactly (including trailing slashes)
- **Error: "Method not allowed"** — Ensure you're using the correct HTTP method (GET, POST, etc.)
- **Route parameters are strings** — Cast to appropriate types: `(int)$args['id']` or `(string)$args['name']`

---

## Step 3: Middleware (~20 min)

### Goal

Implement middleware for cross-cutting concerns like authentication, logging, and CORS.

### Actions

1. **Create authentication middleware**:

```php
# filename: src/Middleware/AuthMiddleware.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid authorization token'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        // Extract token
        $token = substr($authHeader, 7); // Remove "Bearer "

        // Validate token (simplified - use JWT library in production)
        if ($token !== 'valid-token-123') {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid token'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        // Add user info to request attributes
        $request = $request->withAttribute('user', [
            'id' => 1,
            'name' => 'Authenticated User'
        ]);

        // Continue to next middleware/handler
        return $handler->handle($request);
    }
}
```

2. **Create CORS middleware**:

```php
# filename: src/Middleware/CorsMiddleware.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // Add CORS headers
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }
}
```

3. **Create logging middleware**:

```php
# filename: src/Middleware/LoggingMiddleware.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $startTime = microtime(true);
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        // Log request
        error_log("[$method] $uri - Start");

        // Process request
        $response = $handler->handle($request);

        // Calculate duration
        $duration = microtime(true) - $startTime;
        $statusCode = $response->getStatusCode();

        // Log response
        error_log("[$method] $uri - $statusCode - " . round($duration * 1000, 2) . "ms");

        return $response;
    }
}
```

4. **Apply middleware in your application**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use App\Middleware\CorsMiddleware;
use App\Middleware\LoggingMiddleware;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

// Add global middleware (applied to all routes)
$app->add(new CorsMiddleware());
$app->add(new LoggingMiddleware());

// Protected route with auth middleware
$app->get('/protected', function (Request $request, Response $response): Response {
    $user = $request->getAttribute('user');
    $response->getBody()->write(json_encode([
        'message' => 'This is a protected route',
        'user' => $user
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->add(new AuthMiddleware());

// Public route
$app->get('/public', function (Request $request, Response $response): Response {
    $response->getBody()->write(json_encode([
        'message' => 'This is a public route'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
```

5. **Test middleware**:

```bash
# Test public route (no auth needed)
curl http://localhost:8000/public

# Test protected route without token (should fail)
curl http://localhost:8000/protected

# Test protected route with token (should succeed)
curl http://localhost:8000/protected \
  -H "Authorization: Bearer valid-token-123"
```

### Expected Result

```json
# GET /public
{"message":"This is a public route"}

# GET /protected (no token)
{"error":"Unauthorized","message":"Missing or invalid authorization token"}

# GET /protected (with token)
{"message":"This is a protected route","user":{"id":1,"name":"Authenticated User"}}
```

### Why It Works

Middleware in Slim implements PSR-15 (`MiddlewareInterface`), which defines a standard way to process HTTP requests and responses. Middleware receives a request, optionally modifies it, passes it to the next handler, and can modify the response before returning it.

The order matters: middleware added first runs first on the request, but last on the response (stack-based). Global middleware (added with `$app->add()`) applies to all routes, while route-specific middleware (added with `->add()`) applies only to that route.

Request attributes (set with `withAttribute()`) allow middleware to pass data to route handlers, similar to request attributes in Java servlets.

### Troubleshooting

- **Middleware not executing** — Ensure middleware implements `MiddlewareInterface` correctly
- **CORS errors in browser** — Add OPTIONS method handling for preflight requests
- **Attributes not accessible** — Use `$request->getAttribute('key')` to retrieve attributes set by middleware

---

## Step 4: Dependency Injection (~15 min)

### Goal

Set up dependency injection using PHP-DI container with Slim.

### Actions

1. **Install PHP-DI**:

```bash
composer require php-di/php-di:"^7.0" php-di/slim-bridge:"^1.0"
```

2. **Create service classes**:

```php
# filename: src/Services/UserService.php
<?php

declare(strict_types=1);

namespace App\Services;

class UserService
{
    public function findAll(): array
    {
        // In real app, fetch from database
        return [
            ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
        ];
    }

    public function findById(int $id): ?array
    {
        $users = $this->findAll();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    public function create(array $data): array
    {
        // In real app, save to database
        return [
            'id' => 3,
            'name' => $data['name'] ?? 'Unknown',
            'email' => $data['email'] ?? 'unknown@example.com'
        ];
    }
}
```

3. **Create controller**:

```php
# filename: src/Controllers/UserController.php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $users = $this->userService->findAll();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $user = $this->userService->findById($id);

        if ($user === null) {
            $response->getBody()->write(json_encode([
                'error' => 'User not found'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $user = $this->userService->create($data);
        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
}
```

4. **Configure container and use in routes**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use DI\Bridge\Slim\Bridge;
use App\Controllers\UserController;
use App\Services\UserService;

// Create container
$container = require __DIR__ . '/../config/container.php';

// Create Slim app with container
$app = Bridge::create($container);

// Define routes using controller methods
$app->get('/users', [UserController::class, 'index']);
$app->get('/users/{id}', [UserController::class, 'show']);
$app->post('/users', [UserController::class, 'store']);

$app->run();
```

5. **Create container configuration**:

```php
# filename: config/container.php
<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use App\Services\UserService;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    UserService::class => \DI\create(UserService::class),
]);

return $containerBuilder->build();
```

### Expected Result

```bash
# Test routes
curl http://localhost:8000/users
curl http://localhost:8000/users/1
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Charlie","email":"charlie@example.com"}'
```

### Why It Works

PHP-DI provides dependency injection for Slim through the bridge. The container automatically resolves constructor dependencies, so when Slim instantiates `UserController`, it injects `UserService` automatically. This follows the dependency inversion principle and makes your code more testable and maintainable.

Controller methods can be referenced as callables using the array syntax `[ClassName::class, 'methodName']`, which Slim resolves through the container.

### Troubleshooting

- **Error: "Class not found"** — Ensure classes are autoloaded via Composer (`composer dump-autoload`)
- **Error: "Cannot resolve dependency"** — Register all dependencies in container configuration
- **Controller not receiving dependencies** — Use `Bridge::create($container)` instead of `AppFactory::create()`

---

## Step 5: Error Handling (~15 min)

### Goal

Implement custom error handling for consistent API error responses.

### Actions

1. **Create custom error handler**:

```php
# filename: src/Middleware/ErrorHandler.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpBadRequestException;

class ErrorHandler
{
    public function __invoke(
        Request $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = new \Slim\Psr7\Response();
        $statusCode = 500;
        $message = 'Internal Server Error';

        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
            $message = 'Resource not found';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $statusCode = 405;
            $message = 'Method not allowed';
        } elseif ($exception instanceof HttpBadRequestException) {
            $statusCode = 400;
            $message = 'Bad request';
        }

        $error = [
            'error' => [
                'status' => $statusCode,
                'message' => $message,
                'path' => $request->getUri()->getPath()
            ]
        ];

        if ($displayErrorDetails) {
            $error['error']['details'] = $exception->getMessage();
            $error['error']['trace'] = $exception->getTraceAsString();
        }

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
```

2. **Register error handler in application**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use App\Middleware\ErrorHandler;

$app = AppFactory::create();

// Get error handler
$errorHandler = new ErrorHandler();

// Register error handler
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Define routes
$app->get('/users/{id}', function ($request, $response, $args) {
    $id = (int)$args['id'];
    if ($id < 1) {
        throw new \Slim\Exception\HttpBadRequestException($request, 'Invalid user ID');
    }
    // ... rest of handler
});

$app->run();
```

3. **Create custom exception**:

```php
# filename: src/Exceptions/ValidationException.php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct(
        private array $errors,
        string $message = 'Validation failed',
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

4. **Handle validation errors**:

```php
# filename: public/index.php
// ... existing code ...

$app->post('/users', function (Request $request, Response $response) use ($app) {
    $data = json_decode($request->getBody()->getContents(), true);
    
    // Validate
    $errors = [];
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }
    
    if (!empty($errors)) {
        $response->getBody()->write(json_encode([
            'error' => [
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $errors
            ]
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(422);
    }
    
    // Process valid data
    // ...
});
```

### Expected Result

```json
# GET /nonexistent
{
  "error": {
    "status": 404,
    "message": "Resource not found",
    "path": "/nonexistent"
  }
}

# POST /users (invalid data)
{
  "error": {
    "status": 422,
    "message": "Validation failed",
    "errors": {
      "name": "Name is required",
      "email": "Valid email is required"
    }
  }
}
```

### Why It Works

Slim's error middleware catches all exceptions and passes them to the error handler. The error handler receives the exception, request, and configuration flags, allowing you to customize error responses. HTTP-specific exceptions (`HttpNotFoundException`, etc.) provide status codes automatically, while custom exceptions require manual status code handling.

Error details should only be shown in development (`$displayErrorDetails = true`), never in production, to avoid exposing sensitive information.

### Troubleshooting

- **Error handler not called** — Ensure error middleware is added: `$app->addErrorMiddleware()`
- **Generic 500 errors** — Check PHP error logs for actual exception details
- **Error details not showing** — Set `$displayErrorDetails = true` in development

---

## Step 6: Route Groups and API Versioning (~10 min)

### Goal

Organize routes using groups and implement API versioning.

### Actions

1. **Create route groups**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\AuthMiddleware;

$app = AppFactory::create();

// Public routes group
$app->group('/api/v1', function ($group) {
    $group->get('/status', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode(['status' => 'ok', 'version' => '1.0']));
        return $response->withHeader('Content-Type', 'application/json');
    });
});

// Protected routes group
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

$app->run();
```

2. **Test route groups**:

```bash
# Test v1 status (public)
curl http://localhost:8000/api/v1/status

# Test v1 users (requires auth)
curl http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer valid-token-123"

# Test v2 status
curl http://localhost:8000/api/v2/status
```

### Expected Result

```json
# GET /api/v1/status
{"status":"ok","version":"1.0"}

# GET /api/v2/status
{"status":"ok","version":"2.0","features":["enhanced","improved"]}
```

### Why It Works

Route groups allow you to apply common path prefixes, middleware, and other settings to multiple routes. Middleware added to a group applies to all routes within that group. This is perfect for API versioning, where you can maintain multiple API versions simultaneously.

Groups can be nested, and middleware is applied in order: group middleware runs before route-specific middleware.

### Troubleshooting

- **Group routes not matching** — Ensure group path doesn't conflict with individual routes
- **Middleware not applying** — Add middleware to group using `->add()` after the group definition

---

## Exercises

### Exercise 1: Build a Task API

**Goal**: Create a complete REST API for task management using Slim.

**Requirements:**

- Create a `TaskService` class with methods: `findAll()`, `findById()`, `create()`, `update()`, `delete()`
- Create a `TaskController` with CRUD operations
- Implement routes: `GET /tasks`, `GET /tasks/{id}`, `POST /tasks`, `PUT /tasks/{id}`, `DELETE /tasks/{id}`
- Add validation: tasks must have a title (required) and optional description
- Return appropriate HTTP status codes (200, 201, 204, 404)
- Use dependency injection for `TaskService`

**Validation**: Test all endpoints:

```bash
# Create task
curl -X POST http://localhost:8000/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Learn Slim","description":"Complete chapter 22"}'

# Get all tasks
curl http://localhost:8000/tasks

# Get specific task
curl http://localhost:8000/tasks/1

# Update task
curl -X PUT http://localhost:8000/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Master Slim","description":"Completed!"}'

# Delete task
curl -X DELETE http://localhost:8000/tasks/1
```

### Exercise 2: JWT Authentication Middleware

**Goal**: Implement real JWT authentication using the Firebase JWT library.

**Requirements:**

- Install `firebase/php-jwt` package
- Create `JwtAuthMiddleware` that validates JWT tokens
- Extract user ID from token payload
- Add user info to request attributes
- Protect routes with the middleware
- Return 401 for invalid/missing tokens

**Validation**: Generate a JWT token and test protected routes:

```php
// Generate token (use in a login endpoint)
use Firebase\JWT\JWT;

$payload = ['user_id' => 1, 'exp' => time() + 3600];
$token = JWT::encode($payload, 'your-secret-key', 'HS256');
```

---

## Wrap-up

Congratulations! You've completed the Slim micro-framework chapter. Here's what you've accomplished:

✓ **Set up Slim applications** with proper project structure  
✓ **Implemented routing** with HTTP methods and route parameters  
✓ **Created middleware** for authentication, CORS, and logging  
✓ **Configured dependency injection** using PHP-DI  
✓ **Built error handling** with custom error responses  
✓ **Organized routes** using groups and API versioning  
✓ **Compared Slim** to Java micro-frameworks like Spark  

**Key Takeaways:**

- Slim is perfect for microservices and lightweight APIs
- Middleware provides a clean way to handle cross-cutting concerns
- Dependency injection makes code more testable and maintainable
- Route groups help organize and version APIs
- PSR-7 and PSR-15 standards ensure interoperability

**Next Steps:**

- Explore Slim's advanced features: custom containers, route caching, and more
- Build a complete microservice using Slim
- Compare Slim with other PHP micro-frameworks (Lumen, Silex)
- Learn about deploying Slim applications to production

---

## Further Reading

- [Slim Framework Documentation](https://www.slimframework.com/docs/v4/) — Official Slim 4 documentation
- [PSR-7 HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/) — Standard for HTTP messages
- [PSR-15 HTTP Server Handlers](https://www.php-fig.org/psr/psr-15/) — Middleware standard
- [PHP-DI Documentation](https://php-di.org/doc/) — Dependency injection container
- [Spark Framework (Java)](https://sparkjava.com/) — Java micro-framework similar to Slim
- [Javalin Framework](https://javalin.io/) — Another Java micro-framework for comparison

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="22"
  label="Completed Slim micro-framework chapter - ready to build lightweight APIs!"
/>

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/21-symfony-components">← Chapter 21: Symfony Components</a></div>
  <div><strong>Series Complete!</strong> 🎉</div>
</div>
