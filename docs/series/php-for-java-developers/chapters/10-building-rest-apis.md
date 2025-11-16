---
title: "10: Building REST APIs"
description: "RESTful principles, routing, JSON responses, authentication"
series: "php-for-java-developers"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/09-working-with-databases"
---

![Building REST APIs](/images/php-for-java-developers/chapter-10-building-rest-apis-hero-full.webp)

# Chapter 10: Building REST APIs

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Building RESTful APIs is a fundamental skill for modern PHP development. Whether you're creating microservices, mobile app backends, or integrating different systems, understanding how to design and implement robust REST APIs is essential. If you're coming from Java, you'll find that PHP's approach to API development shares many similarities with frameworks like Spring Boot, but with PHP-specific patterns and conventions.

In this chapter, you'll learn how to build production-ready REST APIs from scratch. We'll start with RESTful principles and HTTP fundamentals, then move through routing, request/response handling, authentication, and security. You'll build a complete blog API with CRUD operations, JWT authentication, rate limiting, and proper error handling. By the end, you'll understand how to create APIs that are secure, scalable, and maintainable.

We'll cover everything from basic routing to advanced topics like API versioning, CORS configuration, and validation. Each concept is demonstrated with practical, runnable code examples that you can adapt for your own projects. The patterns you'll learn here form the foundation for working with modern PHP frameworks like Laravel and Symfony, which build upon these core concepts.

## What You'll Build

In this chapter, you'll create:

- A complete REST API with CRUD operations for a blog post system
- A routing system that handles HTTP methods and URL parameters
- JSON request/response handlers with proper validation
- JWT authentication middleware for securing endpoints
- API versioning implementation (URL and header-based)
- Rate limiting middleware to protect your API
- CORS configuration for cross-origin requests
- Consistent error handling with proper HTTP status codes
- A fully functional REST API ready for production use

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- PHP namespaces and autoloading (Chapter 6)
- Error handling with exceptions (Chapter 7)
- Database operations with PDO (Chapter 9)
- HTTP fundamentals (methods, headers, status codes)
- JSON data format

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Design RESTful APIs** following industry best practices and conventions
2. **Implement routing systems** to handle different HTTP methods and endpoints
3. **Process JSON data** for both requests and responses with proper validation
4. **Authenticate API requests** using JWT tokens, API keys, and OAuth flows
5. **Version your APIs** to maintain backward compatibility
6. **Handle errors gracefully** with consistent error responses
7. **Implement rate limiting** to protect your API from abuse
8. **Configure CORS** for cross-origin requests
9. **Document your API** using industry-standard formats

---

## Section 1: RESTful Principles

REST (Representational State Transfer) is an architectural style for designing networked applications. Understanding REST principles helps you create APIs that are intuitive, scalable, and maintainable.

### Core REST Principles

**1. Resource-Based Architecture**

Everything in REST is a resource, identified by URIs:

```php
<?php

declare(strict_types=1);

// ✅ Good: Resources are nouns
GET    /api/users           // Get all users
GET    /api/users/123       // Get specific user
POST   /api/users           // Create new user
PUT    /api/users/123       // Update entire user
PATCH  /api/users/123       // Partial update
DELETE /api/users/123       // Delete user

// Collection resources
GET    /api/users/123/posts // Get posts for user 123
POST   /api/users/123/posts // Create post for user 123

// ❌ Bad: Using verbs in URLs
GET    /api/getUser?id=123
POST   /api/createUser
POST   /api/deleteUser?id=123
```

**2. Stateless Communication**

Each request contains all information needed to process it:

::: code-group

```php [PHP]
<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    /**
     * Stateless request - all info included
     */
    public function authenticate(): ?User
    {
        // Extract token from request
        $token = $this->getBearerToken();

        if ($token === null) {
            return null;
        }

        // Validate token (contains user info)
        $payload = JWT::decode($token, $_ENV['JWT_SECRET']);

        // No server-side session needed
        return User::findById($payload->userId);
    }

    private function getBearerToken(): ?string
    {
        $header = $this->getHeader('Authorization');

        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
```

```java [Java]
// Java Spring Boot equivalent
@RestController
public class UserController {

    @GetMapping("/api/users")
    public ResponseEntity<List<User>> getUsers(
        @RequestHeader("Authorization") String authHeader
    ) {
        // Extract and validate token
        String token = authHeader.replace("Bearer ", "");
        Claims claims = Jwts.parser()
            .setSigningKey(jwtSecret)
            .parseClaimsJws(token)
            .getBody();

        // Process request with token info
        return ResponseEntity.ok(userService.findAll());
    }
}
```

:::

**3. HTTP Methods Map to CRUD Operations**

| HTTP Method | CRUD Operation | Idempotent | Safe |
| ----------- | -------------- | ---------- | ---- |
| GET         | Read           | Yes        | Yes  |
| POST        | Create         | No         | No   |
| PUT         | Update/Replace | Yes        | No   |
| PATCH       | Partial Update | No         | No   |
| DELETE      | Delete         | Yes        | No   |

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

class UserController
{
    public function __construct(
        private UserRepository $users
    ) {}

    /**
     * GET /api/users
     * Safe and idempotent - no side effects
     */
    public function index(): Response
    {
        $users = $this->users->findAll();
        return Response::json($users);
    }

    /**
     * POST /api/users
     * Not idempotent - creates new resource each time
     */
    public function store(Request $request): Response
    {
        $data = $request->json();
        $user = $this->users->create($data);

        return Response::json($user, 201)
            ->withHeader('Location', "/api/users/{$user->id}");
    }

    /**
     * PUT /api/users/123
     * Idempotent - same request produces same result
     */
    public function update(Request $request, int $id): Response
    {
        $data = $request->json();
        $user = $this->users->update($id, $data);

        return Response::json($user);
    }

    /**
     * DELETE /api/users/123
     * Idempotent - deleting same resource multiple times
     */
    public function destroy(int $id): Response
    {
        $this->users->delete($id);
        return Response::noContent(); // 204
    }
}
```

### HTTP Status Codes

Use appropriate status codes to communicate results:

```php
<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    // Success codes (2xx)
    public const OK = 200;                    // Request succeeded
    public const CREATED = 201;               // Resource created
    public const ACCEPTED = 202;              // Async processing started
    public const NO_CONTENT = 204;            // Success, no body

    // Redirection codes (3xx)
    public const MOVED_PERMANENTLY = 301;     // Resource moved
    public const FOUND = 302;                 // Temporary redirect
    public const NOT_MODIFIED = 304;          // Cached version is valid

    // Client error codes (4xx)
    public const BAD_REQUEST = 400;           // Invalid request
    public const UNAUTHORIZED = 401;          // Missing/invalid auth
    public const FORBIDDEN = 403;             // Auth valid but denied
    public const NOT_FOUND = 404;             // Resource doesn't exist
    public const METHOD_NOT_ALLOWED = 405;    // Wrong HTTP method
    public const CONFLICT = 409;              // Resource conflict
    public const UNPROCESSABLE_ENTITY = 422;  // Validation failed
    public const TOO_MANY_REQUESTS = 429;     // Rate limit exceeded

    // Server error codes (5xx)
    public const INTERNAL_SERVER_ERROR = 500; // Server error
    public const NOT_IMPLEMENTED = 501;       // Not implemented
    public const SERVICE_UNAVAILABLE = 503;   // Temporarily down

    public static function json(
        mixed $data,
        int $status = self::OK,
        array $headers = []
    ): self {
        $response = new self();
        $response->setStatus($status);
        $response->setHeader('Content-Type', 'application/json');

        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        $response->body = json_encode($data, JSON_THROW_ON_ERROR);
        return $response;
    }

    public static function error(
        string $message,
        int $status = self::BAD_REQUEST,
        ?array $errors = null
    ): self {
        $data = ['message' => $message];

        if ($errors !== null) {
            $data['errors'] = $errors;
        }

        return self::json($data, $status);
    }
}
```

---

## Section 2: Request Routing

Routing maps HTTP requests to controller actions. PHP doesn't have built-in routing like Java Spring's `@RequestMapping`, so we need to implement it.

### Simple Router Implementation

```php
<?php

declare(strict_types=1);

namespace App\Routing;

class Router
{
    /** @var array<string, array<string, Route>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): Route
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(
        string $method,
        string $path,
        callable|array $handler
    ): Route {
        $route = new Route($method, $path, $handler);
        $this->routes[$method][$path] = $route;
        return $route;
    }

    /**
     * Match request to route
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        // Check exact matches first
        if (isset($this->routes[$method][$path])) {
            return $this->executeRoute(
                $this->routes[$method][$path],
                $request
            );
        }

        // Check pattern matches
        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $route->matches($path);
            if ($params !== null) {
                $request->setRouteParams($params);
                return $this->executeRoute($route, $request);
            }
        }

        // No route found
        return Response::error('Not Found', 404);
    }

    private function executeRoute(Route $route, Request $request): Response
    {
        try {
            // Run middleware
            foreach ($route->getMiddleware() as $middleware) {
                $middlewareInstance = new $middleware();
                $middlewareInstance->handle($request);
            }

            // Execute handler
            $handler = $route->getHandler();

            if (is_array($handler)) {
                [$controller, $method] = $handler;
                $controllerInstance = new $controller();
                return $controllerInstance->$method($request);
            }

            return $handler($request);

        } catch (\Throwable $e) {
            return Response::error(
                'Internal Server Error',
                500
            );
        }
    }
}
```

### Route Class with Parameters

```php
<?php

declare(strict_types=1);

namespace App\Routing;

class Route
{
    private array $middleware = [];
    private ?string $name = null;
    private string $pattern;
    private array $paramNames = [];

    public function __construct(
        private string $method,
        private string $path,
        private mixed $handler
    ) {
        $this->compilePattern();
    }

    /**
     * Convert route path to regex pattern
     */
    private function compilePattern(): void
    {
        // Convert /users/{id} to regex
        $pattern = preg_replace_callback(
            '/\{(\w+)(:[^}]+)?\}/',
            function ($matches) {
                $this->paramNames[] = $matches[1];
                $constraint = $matches[2] ?? ':[^/]+';
                return '(' . substr($constraint, 1) . ')';
            },
            $this->path
        );

        $this->pattern = '#^' . $pattern . '$#';
    }

    /**
     * Check if path matches route pattern
     */
    public function matches(string $path): ?array
    {
        if (!preg_match($this->pattern, $path, $matches)) {
            return null;
        }

        // Remove full match
        array_shift($matches);

        // Combine param names with values
        return array_combine($this->paramNames, $matches) ?: [];
    }

    public function middleware(string ...$middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }
}
```

### Route Definition

```php
<?php

declare(strict_types=1);

// routes/api.php

use App\Controllers\{UserController, PostController};
use App\Middleware\{AuthMiddleware, RateLimitMiddleware};

return function (Router $router) {
    // Public routes
    $router->post('/api/auth/login', [AuthController::class, 'login']);
    $router->post('/api/auth/register', [AuthController::class, 'register']);

    // Protected routes - require authentication
    $router->get('/api/users', [UserController::class, 'index'])
        ->middleware(AuthMiddleware::class, RateLimitMiddleware::class)
        ->name('users.index');

    $router->get('/api/users/{id:\d+}', [UserController::class, 'show'])
        ->middleware(AuthMiddleware::class)
        ->name('users.show');

    $router->post('/api/users', [UserController::class, 'store'])
        ->middleware(AuthMiddleware::class)
        ->name('users.store');

    $router->put('/api/users/{id:\d+}', [UserController::class, 'update'])
        ->middleware(AuthMiddleware::class)
        ->name('users.update');

    $router->delete('/api/users/{id:\d+}', [UserController::class, 'destroy'])
        ->middleware(AuthMiddleware::class)
        ->name('users.destroy');

    // Nested resources
    $router->get('/api/users/{userId:\d+}/posts', [PostController::class, 'index'])
        ->middleware(AuthMiddleware::class);

    $router->post('/api/users/{userId:\d+}/posts', [PostController::class, 'store'])
        ->middleware(AuthMiddleware::class);
};
```

---

## Section 3: Request and Response Handling

Proper request/response handling is crucial for robust APIs.

### Request Class

```php
<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    private array $routeParams = [];
    private ?array $jsonData = null;

    public function __construct(
        private array $server,
        private array $query,
        private array $post,
        private string $body
    ) {}

    public static function capture(): self
    {
        return new self(
            $_SERVER,
            $_GET,
            $_POST,
            file_get_contents('php://input')
        );
    }

    public function getMethod(): string
    {
        // Support method override
        if ($this->hasHeader('X-HTTP-Method-Override')) {
            return strtoupper($this->getHeader('X-HTTP-Method-Override'));
        }

        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        $path = $this->server['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        return $path;
    }

    /**
     * Get JSON request body
     */
    public function json(): ?array
    {
        if ($this->jsonData === null && $this->body !== '') {
            $this->jsonData = json_decode($this->body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException(
                    'Invalid JSON: ' . json_last_error_msg()
                );
            }
        }

        return $this->jsonData;
    }

    /**
     * Get specific input value
     */
    public function input(string $key, mixed $default = null): mixed
    {
        // Check JSON body first
        $json = $this->json();
        if ($json !== null && array_key_exists($key, $json)) {
            return $json[$key];
        }

        // Check POST data
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }

        // Check query string
        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return array_merge(
            $this->query,
            $this->post,
            $this->json() ?? []
        );
    }

    /**
     * Get only specified keys
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Get all except specified keys
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }

    public function hasHeader(string $name): bool
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($this->server[$key]);
    }

    public function getHeader(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? null;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function getRouteParam(string $name, mixed $default = null): mixed
    {
        return $this->routeParams[$name] ?? $default;
    }

    public function ip(): ?string
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }

    public function userAgent(): ?string
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Set authenticated user data
     */
    public function setUser(array $user): void
    {
        $this->server['_user'] = $user;
    }

    /**
     * Get authenticated user data
     */
    public function getUser(): ?array
    {
        return $this->server['_user'] ?? null;
    }

    /**
     * Set CORS headers to be added to response
     */
    public function setCorsHeaders(array $headers): void
    {
        $this->server['_cors_headers'] = $headers;
    }

    /**
     * Get CORS headers
     */
    public function getCorsHeaders(): array
    {
        return $this->server['_cors_headers'] ?? [];
    }

    /**
     * Set rate limit headers to be added to response
     */
    public function setRateLimitHeaders(array $headers): void
    {
        $this->server['_rate_limit_headers'] = $headers;
    }

    /**
     * Get rate limit headers
     */
    public function getRateLimitHeaders(): array
    {
        return $this->server['_rate_limit_headers'] ?? [];
    }
}
```

### Response Class

```php
<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    private int $status = 200;
    private array $headers = [];
    private string $body = '';

    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): self {
        $response = new self();
        $response->setStatus($status);
        $response->setHeader('Content-Type', 'application/json');

        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        $response->body = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return $response;
    }

    public static function noContent(): self
    {
        $response = new self();
        $response->setStatus(204);
        return $response;
    }

    public static function created(mixed $data, ?string $location = null): self
    {
        $response = self::json($data, 201);

        if ($location !== null) {
            $response->setHeader('Location', $location);
        }

        return $response;
    }

    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null
    ): self {
        $data = [
            'error' => [
                'message' => $message,
                'status' => $status,
            ],
        ];

        if ($errors !== null) {
            $data['error']['details'] = $errors;
        }

        return self::json($data, $status);
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    /**
     * Add headers from request (CORS, rate limit, etc.)
     */
    public function addRequestHeaders(Request $request): self
    {
        // Add CORS headers
        foreach ($request->getCorsHeaders() as $name => $value) {
            $this->setHeader($name, $value);
        }

        // Add rate limit headers
        foreach ($request->getRateLimitHeaders() as $name => $value) {
            $this->setHeader($name, (string) $value);
        }

        return $this;
    }

    public function send(): void
    {
        // Send status
        http_response_code($this->status);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Send body
        echo $this->body;
    }
}
```

### Content Negotiation

Content negotiation allows clients to request different response formats using the `Accept` header. This is essential for APIs that support multiple formats (JSON, XML, etc.).

```php
<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    /**
     * Determine response format based on Accept header
     */
    public static function negotiate(Request $request, mixed $data, int $status = 200): self
    {
        $accept = $request->getHeader('Accept') ?? 'application/json';

        // Parse Accept header (e.g., "application/json, application/xml;q=0.9")
        $formats = self::parseAcceptHeader($accept);

        // Find best match
        foreach ($formats as $format => $quality) {
            if ($format === 'application/json' || $format === '*/*') {
                return self::json($data, $status);
            }

            if ($format === 'application/xml') {
                return self::xml($data, $status);
            }
        }

        // Default to JSON
        return self::json($data, $status);
    }

    /**
     * Parse Accept header into format => quality array
     */
    private static function parseAcceptHeader(string $accept): array
    {
        $formats = [];
        $parts = explode(',', $accept);

        foreach ($parts as $part) {
            $part = trim($part);

            if (strpos($part, ';') !== false) {
                [$mime, $quality] = explode(';', $part, 2);
                $quality = (float) str_replace('q=', '', trim($quality));
            } else {
                $mime = $part;
                $quality = 1.0;
            }

            $formats[trim($mime)] = $quality;
        }

        // Sort by quality (descending)
        arsort($formats);

        return $formats;
    }

    /**
     * Create XML response
     */
    public static function xml(mixed $data, int $status = 200): self
    {
        $response = new self();
        $response->setStatus($status);
        $response->setHeader('Content-Type', 'application/xml');
        $response->body = self::arrayToXml($data);
        return $response;
    }

    private static function arrayToXml(array $data, string $root = 'root'): string
    {
        $xml = new \SimpleXMLElement("<{$root}/>");
        self::arrayToXmlRecursive($data, $xml);
        return $xml->asXML();
    }

    private static function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                self::arrayToXmlRecursive($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
```

### ETags and HTTP Caching

ETags (Entity Tags) enable efficient caching by allowing clients to check if content has changed without downloading it again.

```php
<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    /**
     * Generate ETag from content
     */
    public function withETag(string $content): self
    {
        $etag = '"' . md5($content) . '"';
        $this->setHeader('ETag', $etag);
        return $this;
    }

    /**
     * Check if content is unchanged (304 Not Modified)
     */
    public static function checkETag(Request $request, string $content): ?self
    {
        $ifNoneMatch = $request->getHeader('If-None-Match');

        if ($ifNoneMatch === null) {
            return null;
        }

        $currentETag = '"' . md5($content) . '"';

        // Remove quotes and compare
        $requestETag = trim($ifNoneMatch, '"');
        $currentETagClean = trim($currentETag, '"');

        if ($requestETag === $currentETagClean) {
            // Content unchanged - return 304
            $response = new self();
            $response->setStatus(304);
            return $response;
        }

        return null;
    }

    /**
     * Add cache control headers
     */
    public function withCacheControl(int $maxAge = 3600, bool $public = true): self
    {
        $directive = $public ? 'public' : 'private';
        $this->setHeader('Cache-Control', "{$directive}, max-age={$maxAge}");
        return $this;
    }

    /**
     * Add Last-Modified header
     */
    public function withLastModified(int $timestamp): self
    {
        $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s \G\M\T', $timestamp));
        return $this;
    }
}
```

**Usage in Controller:**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response};

class PostController
{
    public function show(Request $request): Response
    {
        $post = $this->posts->findBySlug($request->getRouteParam('slug'));

        if ($post === null) {
            return Response::error('Post not found', 404);
        }

        $content = json_encode($post);

        // Check ETag
        $notModified = Response::checkETag($request, $content);
        if ($notModified !== null) {
            return $notModified;
        }

        // Return with ETag and cache headers
        return Response::json($post)
            ->withETag($content)
            ->withCacheControl(3600) // Cache for 1 hour
            ->withLastModified(strtotime($post['updated_at']));
    }
}
```

---

## Section 4: JSON Validation

Validating input is critical for API security and data integrity.

### Validator Class

```php
<?php

declare(strict_types=1);

namespace App\Validation;

class Validator
{
    private array $errors = [];

    public function __construct(
        private array $data,
        private array $rules
    ) {}

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;
            $fieldRules = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($fieldRules as $rule) {
                $this->validateRule($field, $value, $rule);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        return $this->data;
    }

    private function validateRule(string $field, mixed $value, string $rule): void
    {
        // Parse rule and parameters (e.g., "max:255")
        [$ruleName, $params] = $this->parseRule($rule);

        $method = 'validate' . ucfirst($ruleName);

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Unknown rule: {$ruleName}");
        }

        if (!$this->$method($value, ...$params)) {
            $this->addError($field, $ruleName, $params);
        }
    }

    private function parseRule(string $rule): array
    {
        if (strpos($rule, ':') === false) {
            return [$rule, []];
        }

        [$name, $params] = explode(':', $rule, 2);
        return [$name, explode(',', $params)];
    }

    private function addError(string $field, string $rule, array $params): void
    {
        $messages = [
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'min' => "The {$field} must be at least {$params[0]} characters.",
            'max' => "The {$field} must not exceed {$params[0]} characters.",
            'numeric' => "The {$field} must be a number.",
            'integer' => "The {$field} must be an integer.",
            'array' => "The {$field} must be an array.",
            'in' => "The {$field} must be one of: " . implode(', ', $params),
        ];

        $this->errors[$field][] = $messages[$rule] ?? "Validation failed for {$field}.";
    }

    // Validation rules

    private function validateRequired(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== [];
    }

    private function validateEmail(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(mixed $value, int $min): bool
    {
        if ($value === null) {
            return true;
        }
        return strlen((string) $value) >= $min;
    }

    private function validateMax(mixed $value, int $max): bool
    {
        if ($value === null) {
            return true;
        }
        return strlen((string) $value) <= $max;
    }

    private function validateNumeric(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        return is_numeric($value);
    }

    private function validateInteger(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateArray(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        return is_array($value);
    }

    private function validateIn(mixed $value, string ...$allowed): bool
    {
        if ($value === null) {
            return true;
        }
        return in_array($value, $allowed, true);
    }
}

class ValidationException extends \Exception
{
    public function __construct(
        private array $errors
    ) {
        parent::__construct('Validation failed');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

### Using Validation in Controllers

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response};
use App\Validation\{Validator, ValidationException};

class UserController
{
    public function __construct(
        private UserRepository $users
    ) {}

    public function store(Request $request): Response
    {
        try {
            // Validate request data
            $validated = Validator::make($request->all(), [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'role' => 'in:admin,user,guest',
            ])->validate();

            // Hash password
            $validated['password'] = password_hash(
                $validated['password'],
                PASSWORD_ARGON2ID
            );

            // Create user
            $user = $this->users->create($validated);

            // Remove password from response
            unset($user['password']);

            return Response::created(
                $user,
                "/api/users/{$user['id']}"
            );

        } catch (ValidationException $e) {
            return Response::error(
                'Validation failed',
                422,
                $e->getErrors()
            );
        }
    }
}
```

---

## Section 5: Authentication with JWT

JSON Web Tokens (JWT) are the most common authentication method for REST APIs.

### JWT Implementation

```php
<?php

declare(strict_types=1);

namespace App\Auth;

class JWT
{
    private const ALGORITHM = 'HS256';

    /**
     * Create JWT token
     */
    public static function encode(array $payload, string $secret): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => self::ALGORITHM,
        ];

        // Add issued at and expiration
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60 * 24); // 24 hours

        // Create segments
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // Create signature
        $signature = hash_hmac(
            'sha256',
            "{$headerEncoded}.{$payloadEncoded}",
            $secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * Decode and verify JWT token
     */
    public static function decode(string $token, string $secret): object
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid token format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac(
            'sha256',
            "{$headerEncoded}.{$payloadEncoded}",
            $secret,
            true
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new \InvalidArgumentException('Invalid signature');
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded));

        // Check expiration
        if (isset($payload->exp) && $payload->exp < time()) {
            throw new \InvalidArgumentException('Token expired');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
```

### Auth Controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\JWT;
use App\Http\{Request, Response};
use App\Validation\{Validator, ValidationException};

class AuthController
{
    public function __construct(
        private UserRepository $users
    ) {}

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): Response
    {
        try {
            // Validate credentials
            $validated = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ])->validate();

            // Find user
            $user = $this->users->findByEmail($validated['email']);

            if ($user === null || !password_verify(
                $validated['password'],
                $user['password']
            )) {
                return Response::error('Invalid credentials', 401);
            }

            // Create token
            $token = JWT::encode([
                'userId' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
            ], $_ENV['JWT_SECRET']);

            return Response::json([
                'token' => $token,
                'type' => 'Bearer',
                'expiresIn' => 86400, // 24 hours in seconds
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ],
            ]);

        } catch (ValidationException $e) {
            return Response::error(
                'Validation failed',
                422,
                $e->getErrors()
            );
        }
    }

    /**
     * POST /api/auth/register
     */
    public function register(Request $request): Response
    {
        try {
            // Validate registration data
            $validated = Validator::make($request->all(), [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ])->validate();

            // Check if email exists
            if ($this->users->findByEmail($validated['email']) !== null) {
                return Response::error('Email already registered', 409);
            }

            // Hash password
            $validated['password'] = password_hash(
                $validated['password'],
                PASSWORD_ARGON2ID
            );

            // Create user
            $user = $this->users->create($validated);

            // Create token
            $token = JWT::encode([
                'userId' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user',
            ], $_ENV['JWT_SECRET']);

            return Response::created([
                'token' => $token,
                'type' => 'Bearer',
                'expiresIn' => 86400,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ],
            ], "/api/users/{$user['id']}");

        } catch (ValidationException $e) {
            return Response::error(
                'Validation failed',
                422,
                $e->getErrors()
            );
        }
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh(Request $request): Response
    {
        try {
            $token = $this->extractToken($request);

            // Decode current token (will throw if expired)
            $payload = JWT::decode($token, $_ENV['JWT_SECRET']);

            // Create new token
            $newToken = JWT::encode([
                'userId' => $payload->userId,
                'email' => $payload->email,
                'role' => $payload->role,
            ], $_ENV['JWT_SECRET']);

            return Response::json([
                'token' => $newToken,
                'type' => 'Bearer',
                'expiresIn' => 86400,
            ]);

        } catch (\InvalidArgumentException $e) {
            return Response::error('Invalid token', 401);
        }
    }

    private function extractToken(Request $request): string
    {
        $header = $request->getHeader('Authorization');

        if ($header === null || !preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            throw new \InvalidArgumentException('No token provided');
        }

        return $matches[1];
    }
}
```

### Auth Middleware

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\JWT;
use App\Http\{Request, Response};

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        try {
            $token = $this->extractToken($request);
            $payload = JWT::decode($token, $_ENV['JWT_SECRET']);

            // Attach user info to request
            $request->setUser([
                'id' => $payload->userId,
                'email' => $payload->email,
                'role' => $payload->role,
            ]);

        } catch (\Throwable $e) {
            $response = Response::error('Unauthorized', 401);
            $response->send();
            exit;
        }
    }

    private function extractToken(Request $request): string
    {
        $header = $request->getHeader('Authorization');

        if ($header === null || !preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            throw new \InvalidArgumentException('No token provided');
        }

        return $matches[1];
    }
}
```

---

## Section 6: API Versioning

Versioning allows you to make breaking changes while supporting existing clients.

### URL Versioning (Most Common)

```php
<?php

declare(strict_types=1);

// routes/api.php

return function (Router $router) {
    // Version 1
    $router->get('/api/v1/users', [V1\UserController::class, 'index']);
    $router->get('/api/v1/users/{id}', [V1\UserController::class, 'show']);

    // Version 2 - Breaking changes
    $router->get('/api/v2/users', [V2\UserController::class, 'index']);
    $router->get('/api/v2/users/{id}', [V2\UserController::class, 'show']);
};
```

### Header Versioning

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

class ApiVersionMiddleware
{
    public function handle(Request $request): void
    {
        // Get version from Accept header
        // Accept: application/vnd.myapi.v1+json
        $accept = $request->getHeader('Accept') ?? '';

        if (preg_match('/vnd\.myapi\.v(\d+)\+json/', $accept, $matches)) {
            $version = (int) $matches[1];
        } else {
            $version = 1; // Default version
        }

        $request->setApiVersion($version);
    }
}
```

### Version-Specific Controllers

```php
<?php

declare(strict_types=1);

namespace App\Controllers\V1;

class UserController
{
    public function show(Request $request): Response
    {
        $id = (int) $request->getRouteParam('id');
        $user = $this->users->findById($id);

        if ($user === null) {
            return Response::error('User not found', 404);
        }

        // V1 response format
        return Response::json([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }
}

namespace App\Controllers\V2;

class UserController
{
    public function show(Request $request): Response
    {
        $id = (int) $request->getRouteParam('id');
        $user = $this->users->findById($id);

        if ($user === null) {
            return Response::error('User not found', 404);
        }

        // V2 response format - nested data
        return Response::json([
            'data' => [
                'type' => 'user',
                'id' => $user['id'],
                'attributes' => [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'createdAt' => $user['created_at'],
                ],
            ],
        ]);
    }
}
```

---

## Section 7: Rate Limiting

Protect your API from abuse with rate limiting.

### Rate Limiter Implementation

```php
<?php

declare(strict_types=1);

namespace App\RateLimit;

class RateLimiter
{
    public function __construct(
        private \Redis $redis
    ) {}

    /**
     * Check if request should be rate limited
     *
     * @param string $key Unique identifier (e.g., IP, user ID)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     */
    public function tooManyAttempts(
        string $key,
        int $maxAttempts,
        int $decayMinutes = 1
    ): bool {
        $attempts = $this->attempts($key);

        if ($attempts >= $maxAttempts) {
            return true;
        }

        return false;
    }

    /**
     * Increment attempts
     */
    public function hit(string $key, int $decayMinutes = 1): int
    {
        $redisKey = $this->cleanRateLimiterKey($key);

        $this->redis->multi();
        $this->redis->incr($redisKey);
        $this->redis->expire($redisKey, $decayMinutes * 60);
        $results = $this->redis->exec();

        return (int) $results[0];
    }

    /**
     * Get current attempts
     */
    public function attempts(string $key): int
    {
        return (int) $this->redis->get($this->cleanRateLimiterKey($key)) ?: 0;
    }

    /**
     * Get remaining attempts
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        $attempts = $this->attempts($key);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get seconds until reset
     */
    public function availableIn(string $key): int
    {
        return (int) $this->redis->ttl($this->cleanRateLimiterKey($key));
    }

    /**
     * Clear attempts
     */
    public function clear(string $key): void
    {
        $this->redis->del($this->cleanRateLimiterKey($key));
    }

    private function cleanRateLimiterKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }
}
```

### Rate Limit Middleware

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\RateLimit\RateLimiter;
use App\Http\{Request, Response};

class RateLimitMiddleware
{
    private const MAX_ATTEMPTS = 60;
    private const DECAY_MINUTES = 1;

    public function __construct(
        private RateLimiter $limiter
    ) {}

    public function handle(Request $request): void
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, self::MAX_ATTEMPTS, self::DECAY_MINUTES)) {
            $response = $this->buildResponse($key);
            $response->send();
            exit;
        }

        $this->limiter->hit($key, self::DECAY_MINUTES);

        // Add rate limit headers to response
        $this->addHeaders($request, $key);
    }

    private function resolveRequestSignature(Request $request): string
    {
        // Use user ID if authenticated, otherwise IP
        $user = $request->getUser();

        if ($user !== null) {
            return 'user:' . $user['id'];
        }

        return 'ip:' . $request->ip();
    }

    private function buildResponse(string $key): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return Response::error('Too Many Requests', 429)
            ->withHeader('Retry-After', (string) $retryAfter)
            ->withHeader('X-RateLimit-Limit', (string) self::MAX_ATTEMPTS)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('X-RateLimit-Reset', (string) (time() + $retryAfter));
    }

    private function addHeaders(Request $request, string $key): void
    {
        $request->setRateLimitHeaders([
            'X-RateLimit-Limit' => self::MAX_ATTEMPTS,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, self::MAX_ATTEMPTS),
            'X-RateLimit-Reset' => time() + $this->limiter->availableIn($key),
        ]);
    }
}
```

---

## Section 8: CORS Configuration

Cross-Origin Resource Sharing (CORS) allows browsers to make requests to your API from different domains.

### CORS Middleware

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\{Request, Response};

class CorsMiddleware
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'allowed_origins' => $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*',
            'allowed_methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'allowed_headers' => 'Content-Type, Authorization, X-Requested-With',
            'exposed_headers' => 'X-RateLimit-Limit, X-RateLimit-Remaining',
            'max_age' => 86400, // 24 hours
            'supports_credentials' => true,
        ];
    }

    public function handle(Request $request): ?Response
    {
        $origin = $request->getHeader('Origin');

        // Handle preflight request
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflight($origin);
        }

        // Add CORS headers to actual request
        if ($origin !== null) {
            $this->addCorsHeaders($request, $origin);
        }

        return null; // Continue to next middleware
    }

    private function handlePreflight(?string $origin): Response
    {
        $response = Response::noContent();

        if ($origin !== null && $this->isOriginAllowed($origin)) {
            $response
                ->setHeader('Access-Control-Allow-Origin', $origin)
                ->setHeader('Access-Control-Allow-Methods', $this->config['allowed_methods'])
                ->setHeader('Access-Control-Allow-Headers', $this->config['allowed_headers'])
                ->setHeader('Access-Control-Max-Age', (string) $this->config['max_age']);

            if ($this->config['supports_credentials']) {
                $response->setHeader('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }

    private function addCorsHeaders(Request $request, string $origin): void
    {
        if (!$this->isOriginAllowed($origin)) {
            return;
        }

        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Expose-Headers' => $this->config['exposed_headers'],
        ];

        if ($this->config['supports_credentials']) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        $request->setCorsHeaders($headers);
    }

    private function isOriginAllowed(string $origin): bool
    {
        $allowed = $this->config['allowed_origins'];

        if ($allowed === '*') {
            return true;
        }

        $allowedOrigins = array_map('trim', explode(',', $allowed));
        return in_array($origin, $allowedOrigins, true);
    }
}
```

---

## Section 9: File Uploads via REST API

Handling file uploads in REST APIs requires special handling of `multipart/form-data` requests. This is common for APIs that accept images, documents, or other binary data.

### File Upload Handler

```php
<?php

declare(strict_types=1);

namespace App\Http;

class FileUploadHandler
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

    /**
     * Handle file upload from request
     */
    public function handleUpload(Request $request, string $fieldName = 'file'): array
    {
        if (!isset($_FILES[$fieldName])) {
            throw new \InvalidArgumentException("No file uploaded in field: {$fieldName}");
        }

        $file = $_FILES[$fieldName];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed: ' . $this->getUploadErrorMessage($file['error']));
        }

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size');
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException('File type not allowed');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('upload_', true) . '.' . $extension;
        $destination = __DIR__ . '/../../storage/uploads/' . $filename;

        // Create upload directory if it doesn't exist
        $uploadDir = dirname($destination);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Failed to save uploaded file');
        }

        return [
            'filename' => $filename,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'mime_type' => $mimeType,
            'path' => $destination,
            'url' => '/uploads/' . $filename,
        ];
    }

    private function getUploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown upload error',
        };
    }
}
```

### File Upload Controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response};
use App\Http\FileUploadHandler;
use App\Validation\{Validator, ValidationException};

class FileController
{
    public function __construct(
        private FileUploadHandler $uploadHandler
    ) {}

    /**
     * POST /api/files/upload
     */
    public function upload(Request $request): Response
    {
        try {
            // Handle file upload
            $fileInfo = $this->uploadHandler->handleUpload($request, 'file');

            // Optionally save metadata to database
            // $this->files->create($fileInfo);

            return Response::created([
                'data' => [
                    'id' => $fileInfo['filename'],
                    'filename' => $fileInfo['filename'],
                    'original_name' => $fileInfo['original_name'],
                    'size' => $fileInfo['size'],
                    'mime_type' => $fileInfo['mime_type'],
                    'url' => $fileInfo['url'],
                    'uploaded_at' => date('Y-m-d H:i:s'),
                ],
            ], "/api/files/{$fileInfo['filename']}");

        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/posts/{id}/image
     * Upload image for a post
     */
    public function uploadPostImage(Request $request): Response
    {
        try {
            $postId = (int) $request->getRouteParam('id');

            // Verify post exists
            $post = $this->posts->findById($postId);
            if ($post === null) {
                return Response::error('Post not found', 404);
            }

            // Handle image upload
            $fileInfo = $this->uploadHandler->handleUpload($request, 'image');

            // Update post with image URL
            $this->posts->update($postId, ['image_url' => $fileInfo['url']]);

            return Response::json([
                'data' => [
                    'post_id' => $postId,
                    'image_url' => $fileInfo['url'],
                ],
            ]);

        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 400);
        }
    }
}
```

### File Upload Route

```php
<?php

// routes/api.php

$router->post('/api/files/upload', [FileController::class, 'upload'])
    ->middleware(AuthMiddleware::class);

$router->post('/api/posts/{id}/image', [FileController::class, 'uploadPostImage'])
    ->middleware(AuthMiddleware::class);
```

**Testing File Upload:**

```bash
# Upload a file
curl -X POST http://localhost:8000/api/files/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/image.jpg"

# Upload post image
curl -X POST http://localhost:8000/api/posts/123/image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@/path/to/image.jpg"
```

---

## Section 10: Error Handling for APIs

Consistent error responses improve API usability.

### Global Error Handler

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Response;
use App\Validation\ValidationException;

class ApiExceptionHandler
{
    public function handle(\Throwable $e): Response
    {
        // Log error
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());

        // Handle specific exceptions
        return match (true) {
            $e instanceof ValidationException => Response::error(
                'Validation failed',
                422,
                $e->getErrors()
            ),

            $e instanceof AuthenticationException => Response::error(
                $e->getMessage(),
                401
            ),

            $e instanceof AuthorizationException => Response::error(
                'Forbidden',
                403
            ),

            $e instanceof NotFoundException => Response::error(
                $e->getMessage(),
                404
            ),

            $e instanceof ConflictException => Response::error(
                $e->getMessage(),
                409
            ),

            $e instanceof \InvalidArgumentException => Response::error(
                $e->getMessage(),
                400
            ),

            default => $this->handleGenericException($e)
        };
    }

    private function handleGenericException(\Throwable $e): Response
    {
        // In production, don't expose internal errors
        if ($_ENV['APP_ENV'] === 'production') {
            return Response::error('Internal Server Error', 500);
        }

        // In development, show detailed error
        return Response::json([
            'error' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ],
        ], 500);
    }
}

class AuthenticationException extends \Exception {}
class AuthorizationException extends \Exception {}
class NotFoundException extends \Exception {}
class ConflictException extends \Exception {}
```

### Using Error Handler

```php
<?php

declare(strict_types=1);

// public/index.php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Exceptions\ApiExceptionHandler;
use App\Http\{Request, Response};
use App\Routing\Router;

$router = new Router();
$exceptionHandler = new ApiExceptionHandler();

try {
    // Load routes
    $routeLoader = require __DIR__ . '/../routes/api.php';
    $routeLoader($router);

    // Capture request
    $request = Request::capture();

    // Dispatch request
    $response = $router->dispatch($request);

} catch (\Throwable $e) {
    $response = $exceptionHandler->handle($e);
}

// Add headers from request (CORS, rate limit, etc.)
$response->addRequestHeaders($request);

// Send response
$response->send();
```

---

## Section 11: Pagination and Filtering

Pagination is essential for APIs that return large datasets. It improves performance and reduces bandwidth usage. Filtering and sorting allow clients to retrieve specific subsets of data.

### Pagination Implementation

```php
<?php

declare(strict_types=1);

namespace App\Http;

class Paginator
{
    public function __construct(
        private array $items,
        private int $total,
        private int $page,
        private int $perPage,
        private string $baseUrl
    ) {}

    public function toArray(): array
    {
        $lastPage = (int) ceil($this->total / $this->perPage);

        return [
            'data' => $this->items,
            'meta' => [
                'current_page' => $this->page,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'last_page' => $lastPage,
                'from' => ($this->page - 1) * $this->perPage + 1,
                'to' => min($this->page * $this->perPage, $this->total),
            ],
            'links' => $this->buildLinks($lastPage),
        ];
    }

    private function buildLinks(int $lastPage): array
    {
        $links = [
            'first' => $this->buildUrl(1),
            'last' => $this->buildUrl($lastPage),
        ];

        if ($this->page > 1) {
            $links['prev'] = $this->buildUrl($this->page - 1);
        }

        if ($this->page < $lastPage) {
            $links['next'] = $this->buildUrl($this->page + 1);
        }

        return $links;
    }

    private function buildUrl(int $page): string
    {
        $separator = strpos($this->baseUrl, '?') !== false ? '&' : '?';
        return $this->baseUrl . $separator . 'page=' . $page . '&per_page=' . $this->perPage;
    }
}
```

### Pagination in Controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response, Paginator};

class PostController
{
    public function index(Request $request): Response
    {
        // Get pagination parameters
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = $this->posts->count();

        // Get paginated results
        $posts = $this->posts->findAll($perPage, $offset);

        // Build pagination response
        $paginator = new Paginator(
            $posts,
            $total,
            $page,
            $perPage,
            $request->getPath()
        );

        return Response::json($paginator->toArray());
    }
}
```

### Filtering and Sorting

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

class PostRepository extends Repository
{
    /**
     * Search posts with filters and sorting
     */
    public function search(array $filters): array
    {
        $query = 'SELECT p.*, u.name as author_name
                  FROM posts p
                  JOIN users u ON p.user_id = u.id
                  WHERE 1=1';

        $params = [];

        // Search in title and content
        if (!empty($filters['search'])) {
            $query .= ' AND (p.title LIKE ? OR p.content LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query .= ' AND p.status = ?';
            $params[] = $filters['status'];
        }

        // Filter by author
        if (!empty($filters['author_id'])) {
            $query .= ' AND p.user_id = ?';
            $params[] = $filters['author_id'];
        }

        // Sorting
        $allowedSorts = ['created_at', 'published_at', 'title', 'updated_at'];
        $sort = $filters['sort'] ?? 'created_at';
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        $order = strtoupper($filters['order'] ?? 'DESC');
        $order = ($order === 'ASC' || $order === 'DESC') ? $order : 'DESC';

        $query .= " ORDER BY p.{$sort} {$order}";

        // Pagination
        if (isset($filters['limit'])) {
            $query .= ' LIMIT ?';
            $params[] = $filters['limit'];

            if (isset($filters['offset'])) {
                $query .= ' OFFSET ?';
                $params[] = $filters['offset'];
            }
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### Filtering in Controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response, Paginator};

class PostController
{
    public function index(Request $request): Response
    {
        // Pagination
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));
        $offset = ($page - 1) * $perPage;

        // Filters
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'author_id' => $request->input('author') ? (int) $request->input('author') : null,
            'sort' => $request->input('sort', 'created_at'),
            'order' => $request->input('order', 'desc'),
            'limit' => $perPage,
            'offset' => $offset,
        ];

        // Remove null filters
        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

        // Get results
        $posts = $this->posts->search($filters);

        // Get total count (without pagination)
        $totalFilters = $filters;
        unset($totalFilters['limit'], $totalFilters['offset']);
        $total = count($this->posts->search($totalFilters));

        // Build pagination response
        $paginator = new Paginator(
            $posts,
            $total,
            $page,
            $perPage,
            $request->getPath() . '?' . http_build_query($request->input())
        );

        return Response::json($paginator->toArray());
    }
}
```

**Usage Examples:**

```bash
# Pagination
curl "http://localhost:8000/api/posts?page=2&per_page=20"

# Search
curl "http://localhost:8000/api/posts?search=php&status=published"

# Filter and sort
curl "http://localhost:8000/api/posts?author=5&sort=title&order=asc"

# Combined
curl "http://localhost:8000/api/posts?search=api&status=published&page=1&per_page=10&sort=created_at&order=desc"
```

---

## Section 12: Complete API Example

Let's build a complete REST API for a blog system.

### Database Schema

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'author', 'reader') DEFAULT 'reader',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
);

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Post Repository

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

class PostRepository extends Repository
{
    protected function getTable(): string
    {
        return 'posts';
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, u.name as author_name, u.email as author_email
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.slug = ?'
        );
        $stmt->execute([$slug]);

        $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $post ?: null;
    }

    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, u.name as author_name, u.email as author_email
             FROM posts p
             JOIN users u ON p.user_id = u.id
             WHERE p.status = ?
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute(['published', $limit, $offset]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function publish(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE posts
             SET status = ?, published_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );

        return $stmt->execute(['published', $id]);
    }
}
```

### Post Controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response};
use App\Validation\{Validator, ValidationException};
use App\Exceptions\{AuthorizationException, NotFoundException};

class PostController
{
    public function __construct(
        private PostRepository $posts,
        private CommentRepository $comments
    ) {}

    /**
     * GET /api/posts
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));
        $offset = ($page - 1) * $perPage;

        $posts = $this->posts->findPublished($perPage, $offset);

        return Response::json([
            'data' => $posts,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * GET /api/posts/{slug}
     */
    public function show(Request $request): Response
    {
        $slug = $request->getRouteParam('slug');
        $post = $this->posts->findBySlug($slug);

        if ($post === null) {
            throw new NotFoundException('Post not found');
        }

        // Load comments
        $post['comments'] = $this->comments->findByPost($post['id']);

        return Response::json(['data' => $post]);
    }

    /**
     * POST /api/posts
     */
    public function store(Request $request): Response
    {
        try {
            $validated = Validator::make($request->all(), [
                'title' => 'required|min:3|max:255',
                'content' => 'required|min:10',
                'status' => 'in:draft,published',
            ])->validate();

            // Generate slug from title
            $validated['slug'] = $this->generateSlug($validated['title']);
            $validated['user_id'] = $request->getUser()['id'];

            // Set published_at if publishing
            if (($validated['status'] ?? 'draft') === 'published') {
                $validated['published_at'] = date('Y-m-d H:i:s');
            }

            $post = $this->posts->create($validated);

            return Response::created(
                ['data' => $post],
                "/api/posts/{$post['slug']}"
            );

        } catch (ValidationException $e) {
            return Response::error('Validation failed', 422, $e->getErrors());
        }
    }

    /**
     * PUT /api/posts/{slug}
     */
    public function update(Request $request): Response
    {
        try {
            $slug = $request->getRouteParam('slug');
            $post = $this->posts->findBySlug($slug);

            if ($post === null) {
                throw new NotFoundException('Post not found');
            }

            // Check authorization
            $user = $request->getUser();
            if ($post['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
                throw new AuthorizationException('Cannot edit this post');
            }

            $validated = Validator::make($request->all(), [
                'title' => 'min:3|max:255',
                'content' => 'min:10',
                'status' => 'in:draft,published',
            ])->validate();

            // Update slug if title changed
            if (isset($validated['title'])) {
                $validated['slug'] = $this->generateSlug($validated['title']);
            }

            // Set published_at if publishing for first time
            if (isset($validated['status']) &&
                $validated['status'] === 'published' &&
                $post['status'] === 'draft'
            ) {
                $validated['published_at'] = date('Y-m-d H:i:s');
            }

            $updated = $this->posts->update($post['id'], $validated);

            return Response::json(['data' => $updated]);

        } catch (ValidationException $e) {
            return Response::error('Validation failed', 422, $e->getErrors());
        }
    }

    /**
     * DELETE /api/posts/{slug}
     */
    public function destroy(Request $request): Response
    {
        $slug = $request->getRouteParam('slug');
        $post = $this->posts->findBySlug($slug);

        if ($post === null) {
            throw new NotFoundException('Post not found');
        }

        // Check authorization
        $user = $request->getUser();
        if ($post['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
            throw new AuthorizationException('Cannot delete this post');
        }

        $this->posts->delete($post['id']);

        return Response::noContent();
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;

        while ($this->posts->findBySlug($slug) !== null) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
```

### API Routes

```php
<?php

declare(strict_types=1);

// routes/api.php

use App\Controllers\{AuthController, PostController, CommentController};
use App\Middleware\{AuthMiddleware, RateLimitMiddleware, CorsMiddleware};

return function (Router $router) {
    // Apply CORS to all routes
    $router->middleware(CorsMiddleware::class);

    // Public routes
    $router->post('/api/v1/auth/login', [AuthController::class, 'login'])
        ->middleware(RateLimitMiddleware::class);

    $router->post('/api/v1/auth/register', [AuthController::class, 'register'])
        ->middleware(RateLimitMiddleware::class);

    // Public read access to posts
    $router->get('/api/v1/posts', [PostController::class, 'index']);
    $router->get('/api/v1/posts/{slug}', [PostController::class, 'show']);

    // Protected routes
    $router->post('/api/v1/posts', [PostController::class, 'store'])
        ->middleware(AuthMiddleware::class);

    $router->put('/api/v1/posts/{slug}', [PostController::class, 'update'])
        ->middleware(AuthMiddleware::class);

    $router->delete('/api/v1/posts/{slug}', [PostController::class, 'destroy'])
        ->middleware(AuthMiddleware::class);

    // Comments
    $router->get('/api/v1/posts/{slug}/comments', [CommentController::class, 'index']);

    $router->post('/api/v1/posts/{slug}/comments', [CommentController::class, 'store'])
        ->middleware(AuthMiddleware::class);
};
```

---

## Section 13: API Documentation with OpenAPI

API documentation is crucial for developer adoption. OpenAPI (formerly Swagger) is the industry standard for REST API documentation. It provides a machine-readable specification that can generate interactive documentation, client SDKs, and server stubs.

### OpenAPI Specification Generator

```php
<?php

declare(strict_types=1);

namespace App\Documentation;

class OpenApiGenerator
{
    public function generate(array $routes): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Blog API',
                'version' => '1.0.0',
                'description' => 'REST API for managing blog posts and comments',
            ],
            'servers' => [
                ['url' => 'https://api.example.com/v1', 'description' => 'Production'],
                ['url' => 'http://localhost:8000/api/v1', 'description' => 'Development'],
            ],
            'paths' => $this->generatePaths($routes),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
                'schemas' => $this->generateSchemas(),
            ],
        ];
    }

    private function generatePaths(array $routes): array
    {
        $paths = [];

        foreach ($routes as $route) {
            $path = $route['path'];
            $method = strtolower($route['method']);

            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }

            $paths[$path][$method] = [
                'summary' => $route['summary'] ?? '',
                'description' => $route['description'] ?? '',
                'operationId' => $route['operationId'] ?? '',
                'tags' => $route['tags'] ?? [],
                'parameters' => $this->generateParameters($route),
                'requestBody' => $this->generateRequestBody($route),
                'responses' => $this->generateResponses($route),
                'security' => $route['security'] ?? [],
            ];
        }

        return $paths;
    }

    private function generateParameters(array $route): array
    {
        $parameters = [];

        // Extract path parameters
        if (preg_match_all('/\{(\w+)\}/', $route['path'], $matches)) {
            foreach ($matches[1] as $param) {
                $parameters[] = [
                    'name' => $param,
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'integer'],
                ];
            }
        }

        // Add query parameters
        if (isset($route['queryParams'])) {
            foreach ($route['queryParams'] as $param => $config) {
                $parameters[] = [
                    'name' => $param,
                    'in' => 'query',
                    'required' => $config['required'] ?? false,
                    'schema' => $config['schema'] ?? ['type' => 'string'],
                ];
            }
        }

        return $parameters;
    }

    private function generateRequestBody(array $route): ?array
    {
        if (!in_array(strtolower($route['method']), ['post', 'put', 'patch'], true)) {
            return null;
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/' . ($route['requestSchema'] ?? 'PostRequest'),
                    ],
                ],
            ],
        ];
    }

    private function generateResponses(array $route): array
    {
        $responses = [
            '200' => [
                'description' => 'Success',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/' . ($route['responseSchema'] ?? 'Post'),
                        ],
                    ],
                ],
            ],
            '400' => ['description' => 'Bad Request'],
            '401' => ['description' => 'Unauthorized'],
            '404' => ['description' => 'Not Found'],
            '422' => ['description' => 'Validation Error'],
            '500' => ['description' => 'Internal Server Error'],
        ];

        // Add method-specific responses
        if (strtolower($route['method']) === 'post') {
            $responses['201'] = ['description' => 'Created'];
        }

        if (strtolower($route['method']) === 'delete') {
            $responses['204'] = ['description' => 'No Content'];
        }

        return $responses;
    }

    private function generateSchemas(): array
    {
        return [
            'Post' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'PostRequest' => [
                'type' => 'object',
                'required' => ['title', 'content'],
                'properties' => [
                    'title' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'content' => ['type' => 'string', 'minLength' => 10],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published']],
                ],
            ],
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'error' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'status' => ['type' => 'integer'],
                            'details' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
```

### OpenAPI Documentation Endpoint

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Documentation\OpenApiGenerator;
use App\Http\{Request, Response};

class DocumentationController
{
    public function __construct(
        private OpenApiGenerator $generator
    ) {}

    /**
     * GET /api/docs/openapi.json
     */
    public function openapi(Request $request): Response
    {
        $routes = $this->loadRoutes();
        $spec = $this->generator->generate($routes);

        return Response::json($spec)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * GET /api/docs/swagger-ui
     */
    public function swaggerUi(Request $request): Response
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: '/api/docs/openapi.json',
            dom_id: '#swagger-ui',
        });
    </script>
</body>
</html>
HTML;

        $response = new Response();
        $response->setHeader('Content-Type', 'text/html');
        $response->body = $html;
        return $response;
    }

    private function loadRoutes(): array
    {
        // Load route definitions with metadata
        return [
            [
                'method' => 'GET',
                'path' => '/api/v1/posts',
                'summary' => 'List all posts',
                'operationId' => 'listPosts',
                'tags' => ['Posts'],
                'queryParams' => [
                    'page' => ['schema' => ['type' => 'integer']],
                    'per_page' => ['schema' => ['type' => 'integer']],
                ],
                'responseSchema' => 'Post',
            ],
            [
                'method' => 'POST',
                'path' => '/api/v1/posts',
                'summary' => 'Create a new post',
                'operationId' => 'createPost',
                'tags' => ['Posts'],
                'requestSchema' => 'PostRequest',
                'responseSchema' => 'Post',
                'security' => [['bearerAuth' => []]],
            ],
            // ... more routes
        ];
    }
}
```

### API Testing Examples

Testing REST APIs ensures they work correctly and handle edge cases properly.

```php
<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8000/api/v1';

    /**
     * Test GET /posts endpoint
     */
    public function testListPosts(): void
    {
        $response = $this->makeRequest('GET', '/posts');

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertIsArray($response['body']['data']);
    }

    /**
     * Test POST /posts endpoint with authentication
     */
    public function testCreatePost(): void
    {
        $token = $this->getAuthToken();

        $response = $this->makeRequest('POST', '/posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'status' => 'draft',
        ], [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertEquals(201, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertEquals('Test Post', $response['body']['data']['title']);
    }

    /**
     * Test validation errors
     */
    public function testCreatePostValidationError(): void
    {
        $token = $this->getAuthToken();

        $response = $this->makeRequest('POST', '/posts', [
            'title' => 'AB', // Too short
            'content' => 'Short', // Too short
        ], [
            'Authorization: Bearer ' . $token,
        ]);

        $this->assertEquals(422, $response['status']);
        $this->assertArrayHasKey('error', $response['body']);
        $this->assertArrayHasKey('details', $response['body']['error']);
    }

    private function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        $ch = curl_init($this->baseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json',
            ], $headers),
        ]);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => json_decode($response, true),
        ];
    }

    private function getAuthToken(): string
    {
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        return $response['body']['token'];
    }
}
```

### Request/Response Logging Middleware

Logging API requests and responses helps with debugging, monitoring, and auditing.

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\{Request, Response};

class LoggingMiddleware
{
    public function handle(Request $request): void
    {
        $startTime = microtime(true);

        // Log request
        $this->logRequest($request);

        // Continue to next middleware/controller
        // (In real implementation, this would be handled by the router)
    }

    public function logResponse(Request $request, Response $response, float $duration): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'status' => $response->getStatus(),
            'duration_ms' => round($duration * 1000, 2),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Add user info if authenticated
        $user = $request->getUser();
        if ($user !== null) {
            $logData['user_id'] = $user['id'];
        }

        // Log to file or service
        $this->writeLog($logData);
    }

    private function logRequest(Request $request): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'query' => $request->input(),
            'ip' => $request->ip(),
        ];

        // Don't log sensitive data
        if ($request->getMethod() === 'POST' || $request->getMethod() === 'PUT') {
            $body = $request->json();
            if ($body !== null) {
                // Remove password fields
                unset($body['password'], $body['password_confirmation']);
                $logData['body'] = $body;
            }
        }

        $this->writeLog($logData);
    }

    private function writeLog(array $data): void
    {
        $logFile = __DIR__ . '/../../storage/logs/api-' . date('Y-m-d') . '.log';
        $logLine = json_encode($data) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }
}
```

---

## Exercises

Test your understanding of REST API development:

### Exercise 1: Pagination

Implement proper pagination with total count and page links:

```php
<?php

declare(strict_types=1);

// TODO: Enhance the index method to return:
// - total: Total number of records
// - last_page: Last page number
// - links: next and previous URLs

public function index(Request $request): Response
{
    $page = max(1, (int) $request->input('page', 1));
    $perPage = min(100, max(1, (int) $request->input('per_page', 10)));

    // Your code here
}
```

### Exercise 2: API Search and Filtering

Add search and filtering capabilities:

```php
<?php

declare(strict_types=1);

// TODO: Implement search and filters
// - search: Search in title and content
// - status: Filter by status
// - author: Filter by author ID
// - sort: Sort by field (created_at, published_at, title)
// - order: Sort direction (asc, desc)

public function index(Request $request): Response
{
    // Your code here
}
```

### Exercise 3: API Key Authentication

**Goal**: Implement API key authentication as an alternative to JWT

Create an API key middleware that validates keys from the `X-API-Key` header:

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\{Request, Response};
use App\Repositories\ApiKeyRepository;

class ApiKeyMiddleware
{
    public function __construct(
        private ApiKeyRepository $apiKeys
    ) {}

    public function handle(Request $request): void
    {
        $apiKey = $request->getHeader('X-API-Key');

        if ($apiKey === null) {
            $response = Response::error('API key required', 401);
            $response->send();
            exit;
        }

        // Find key in database
        $keyData = $this->apiKeys->findByKey($apiKey);

        if ($keyData === null || !$keyData['is_active']) {
            $response = Response::error('Invalid API key', 401);
            $response->send();
            exit;
        }

        // Check expiration
        if (isset($keyData['expires_at']) &&
            strtotime($keyData['expires_at']) < time()) {
            $response = Response::error('API key expired', 401);
            $response->send();
            exit;
        }

        // Attach user to request
        $request->setUser([
            'id' => $keyData['user_id'],
            'api_key_id' => $keyData['id'],
        ]);

        // Update last used timestamp
        $this->apiKeys->updateLastUsed($keyData['id']);
    }
}
```

**Validation**: Test your implementation:

```bash
# Request without API key (should fail)
curl http://localhost:8000/api/users

# Request with valid API key
curl -H "X-API-Key: your-api-key-here" http://localhost:8000/api/users

# Expected: Authenticated request succeeds
```

### Exercise 4: Resource Transformation

**Goal**: Create a resource transformer to format API responses consistently

Implement a `PostResource` class that transforms database arrays into consistent API responses:

```php
<?php

declare(strict_types=1);

namespace App\Resources;

class PostResource
{
    public static function make(array $post, string $level = 'list'): array
    {
        $resource = [
            'id' => $post['id'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'status' => $post['status'],
            'created_at' => $post['created_at'],
        ];

        // Include author for both list and detail
        if (isset($post['author_name'])) {
            $resource['author'] = [
                'id' => $post['user_id'],
                'name' => $post['author_name'],
                'email' => $post['author_email'] ?? null,
            ];
        }

        // Include full content and comments for detail view
        if ($level === 'show') {
            $resource['content'] = $post['content'];
            $resource['published_at'] = $post['published_at'] ?? null;
            $resource['updated_at'] = $post['updated_at'] ?? null;

            // Include comments if available
            if (isset($post['comments'])) {
                $resource['comments'] = array_map(
                    fn($comment) => CommentResource::make($comment),
                    $post['comments']
                );
            }
        }

        return $resource;
    }

    public static function collection(array $posts): array
    {
        return array_map(
            fn($post) => self::make($post, 'list'),
            $posts
        );
    }
}
```

**Usage in Controller**:

```php
// List view
return Response::json([
    'data' => PostResource::collection($posts)
]);

// Detail view
return Response::json([
    'data' => PostResource::make($post, 'show')
]);
```

**Validation**: Test your implementation:

```bash
# List endpoint should return simplified post data
curl http://localhost:8000/api/posts

# Detail endpoint should include full content and comments
curl http://localhost:8000/api/posts/my-post-slug

# Expected: Consistent resource format across endpoints
```

---

## Common Pitfalls

**❌ Not Using HTTP Status Codes Correctly**

```php
<?php
// Bad - Always returns 200
return Response::json(['error' => 'Not found'], 200);

// Good - Use appropriate status code
return Response::error('Not found', 404);
```

**❌ Exposing Sensitive Data**

```php
<?php
// Bad - Exposing password hash
return Response::json($user);

// Good - Remove sensitive fields
unset($user['password']);
return Response::json($user);
```

**❌ Not Validating Input**

```php
<?php
// Bad - Direct database insertion
$this->posts->create($request->all());

// Good - Validate first
$validated = Validator::make($request->all(), $rules)->validate();
$this->posts->create($validated);
```

**❌ Inconsistent Error Responses**

```php
<?php
// Bad - Inconsistent formats
return Response::json(['message' => 'Error']);
return Response::json(['error' => 'Error']);

// Good - Consistent format
return Response::error('Error message', 400);
```

---

## Best Practices Summary

✅ **Use RESTful conventions** - Resources are nouns, HTTP methods are verbs
✅ **Return appropriate status codes** - 2xx success, 4xx client errors, 5xx server errors
✅ **Validate all input** - Never trust client data
✅ **Implement authentication** - Protect sensitive endpoints
✅ **Version your API** - Allow breaking changes without disrupting clients
✅ **Use rate limiting** - Protect against abuse
✅ **Handle CORS properly** - Enable cross-origin requests securely
✅ **Document your API** - Make it easy for developers to use
✅ **Log errors** - Track issues in production
✅ **Use pagination** - Don't return unbounded result sets

---

## Wrap-up

Congratulations! You've completed a comprehensive chapter on building REST APIs in PHP. Here's what you've accomplished:

✓ **Mastered RESTful principles** - You understand resource-based architecture, stateless communication, and proper HTTP method usage

✓ **Built a complete routing system** - You can now create routers that handle different HTTP methods, extract route parameters, and apply middleware

✓ **Implemented request/response handling** - You've created robust Request and Response classes that handle JSON, headers, and various input sources

✓ **Created a validation system** - You can validate API input with custom rules and return consistent error messages

✓ **Implemented JWT authentication** - You've built a complete authentication system with login, registration, and token refresh

✓ **Added API versioning** - You understand how to maintain backward compatibility while evolving your API

✓ **Protected your API** - You've implemented rate limiting and CORS to secure your endpoints

✓ **Built a complete blog API** - You've created a working example with posts, comments, and proper authorization

**Key Takeaways:**

- REST APIs follow consistent patterns that make them intuitive and maintainable
- Proper validation and error handling are essential for API security
- Authentication and authorization protect your resources
- Rate limiting and CORS are critical for production APIs
- Versioning allows you to evolve your API without breaking existing clients

**Next Steps:**

In the next chapter, you'll explore dependency injection and how it helps manage object dependencies in PHP applications, similar to Spring's dependency injection in Java.

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Explain RESTful principles and resource-based architecture
- [ ] Implement routing with parameter extraction
- [ ] Handle JSON requests and responses
- [ ] Validate input data with custom rules
- [ ] Implement JWT authentication
- [ ] Version your API using URL or header versioning
- [ ] Add rate limiting to protect endpoints
- [ ] Configure CORS for cross-origin requests
- [ ] Handle errors consistently
- [ ] Build a complete REST API with CRUD operations

::: tip Ready for More?
In [Chapter 11: Dependency Injection](/series/php-for-java-developers/chapters/11-dependency-injection), we'll explore how to manage dependencies and build loosely coupled, testable applications using dependency injection containers.
:::

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="10"
  label="Completed building REST APIs with authentication and validation!"
/>

---

## Further Reading

- [REST API Tutorial](https://restfulapi.net/) - Comprehensive guide to RESTful API design
- [HTTP Status Codes](https://httpstatuses.com/) - Complete reference for HTTP status codes
- [JWT.io](https://jwt.io/) - JSON Web Token introduction and debugger
- [OWASP API Security Top 10](https://owasp.org/www-project-api-security/) - API security best practices
- [JSON:API Specification](https://jsonapi.org/) - Standard for building APIs in JSON

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/09-working-with-databases">← Chapter 9: Working with Databases</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/11-dependency-injection">Chapter 11: Dependency Injection →</a>
  </div>
</div>
