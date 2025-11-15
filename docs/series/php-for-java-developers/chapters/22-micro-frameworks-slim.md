---
title: "22: Micro-frameworks (Slim)"
description: "Building lightweight APIs with Slim framework"
series: "php-for-java-developers"
chapter: 22
order: 22
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/21-symfony-components"
---

# Chapter 22: Micro-frameworks (Slim)

<Badge type="warning">Intermediate</Badge>

## Overview

Slim is a micro-framework perfect for APIs and microservices, similar to Spark for Java.

**Topics:** Slim routing, Middleware, PSR-7, Dependency injection, API development

## Section 1: Getting Started

```bash
composer require slim/slim slim/psr7
```

```php
<?php
require 'vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/hello/{name}', function ($request, $response, $args) {
    $response->getBody()->write("Hello, {$args['name']}");
    return $response;
});

$app->run();
```

## Section 2: RESTful API

```php
<?php
$app->get('/api/users', function ($request, $response) {
    $users = $this->get('userRepository')->findAll();
    return $response->withJson($users);
});

$app->post('/api/users', function ($request, $response) {
    $data = $request->getParsedBody();
    $user = $this->get('userRepository')->create($data);
    return $response->withJson($user, 201);
});
```

## Section 3: Middleware

```php
<?php
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('Access-Control-Allow-Origin', '*');
});

class AuthMiddleware {
    public function __invoke($request, $handler) {
        if (!$this->validateToken($request)) {
            return new Response(401);
        }
        return $handler->handle($request);
    }
}
```

## Section 4: Dependency Container

```php
<?php
use DI\Container;

$container = new Container();
$container->set('userRepository', function() {
    return new UserRepository(new PDO(/*...*/));
});

AppFactory::setContainer($container);
$app = AppFactory::create();
```

## Section 5: Complete API Example

```php
<?php
require 'vendor/autoload.php';

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Middleware
$app->add(new AuthMiddleware());

// Routes
$app->group('/api/v1', function ($group) {
    $group->get('/users', UserController::class . ':index');
    $group->get('/users/{id}', UserController::class . ':show');
    $group->post('/users', UserController::class . ':store');
    $group->put('/users/{id}', UserController::class . ':update');
    $group->delete('/users/{id}', UserController::class . ':destroy');
});

$app->run();
```

---

## Course Completion

Congratulations! You've completed the PHP for Java Developers series. You now have the knowledge to build modern PHP applications using best practices, frameworks, and tools.

**Next Steps:**
- Build a complete project using Laravel or Symfony
- Contribute to open-source PHP projects
- Explore advanced topics like queues, caching, and performance optimization
- Join the PHP community

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/21-symfony-components">← Chapter 21</a></div>
  <div><strong>Series Complete!</strong></div>
</div>
