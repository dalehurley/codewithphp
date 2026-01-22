---
title: "15: HTTP & Request/Response"
description: "Master HTTP handling in PHP from superglobals to PSR-7 standards, with Java Servlet API comparisons"
sidebar:
  label: "15: HTTP & Request/Response"
  order: 15
  badge:
    text: Intermediate
    variant: caution
---

![HTTP & Request/Response](/images/php-for-java-developers/chapter-15-http-request-response-hero-full.webp)

# Chapter 15: HTTP & Request/Response

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Understanding HTTP request and response handling is fundamental to web development in PHP. If you're coming from Java, you're familiar with the Servlet API's `HttpServletRequest` and `HttpServletResponse` interfaces. PHP offers both traditional superglobals (similar to JSP's implicit objects) and modern PSR-7 interfaces (similar to Java's Servlet API) for HTTP handling.

This chapter bridges Java's Servlet API concepts to PHP's HTTP handling mechanisms. You'll learn how PHP's superglobals compare to Java's request/response objects, how to work with PSR-7 interfaces (the PHP standard for HTTP messages), and how to implement middleware patterns similar to Java servlet filters.

**What You'll Learn:**

- PHP superglobals (`$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES`) and their Java equivalents
- PSR-7 Request and Response interfaces (similar to Servlet API)
- Content negotiation (Accept headers, content types, languages)
- Request body parsing (JSON, XML, form-urlencoded, multipart)
- Working with HTTP headers and status codes
- Cookie management and session handling
- CORS preflight requests and OPTIONS method handling
- Caching headers (ETag, Last-Modified, Cache-Control)
- Content encoding (Gzip compression)
- HTTP method override (PUT/DELETE via POST)
- File upload handling and validation
- Range requests for partial content delivery
- Request size limits and validation
- Middleware pattern implementation
- Stream handling for large data
- Security best practices for HTTP handling

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- PHP arrays and associative arrays (Chapter 2)
- Object-oriented programming in PHP (Chapter 3)
- Error handling and exceptions (Chapter 7)
- Basic understanding of HTTP protocol (methods, headers, status codes)
- Familiarity with Java's Servlet API (`HttpServletRequest`, `HttpServletResponse`)

## What You'll Build

In this chapter, you'll create:

- A request handler class using superglobals
- A PSR-7 compatible request/response wrapper
- A header management utility class
- A file upload handler with validation
- A middleware stack implementation
- A complete example combining all concepts

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Use PHP superglobals** to access HTTP request data safely
2. **Work with PSR-7 interfaces** for modern HTTP message handling
3. **Implement content negotiation** for Accept headers, content types, and languages
4. **Parse request bodies** in multiple formats (JSON, XML, form-urlencoded, multipart)
5. **Manage HTTP headers** programmatically (set, get, remove)
6. **Handle cookies** securely with proper options
7. **Handle CORS preflight requests** and OPTIONS method
8. **Implement caching** with ETag, Last-Modified, and Cache-Control headers
9. **Compress responses** using Gzip content encoding
10. **Support HTTP method override** for PUT/DELETE via POST
11. **Process file uploads** with validation and security checks
12. **Handle range requests** for partial content delivery
13. **Validate request size limits** and handle large requests
14. **Implement middleware** patterns similar to servlet filters
15. **Work with streams** for handling large request/response bodies
16. **Use HTTP status codes** appropriately
17. **Apply security best practices** for HTTP handling

---

## Section 1: PHP Superglobals vs Java Servlet API

### Goal

Understand how PHP's superglobals compare to Java's `HttpServletRequest` and `HttpServletResponse`.

### PHP Superglobals Overview

PHP provides several **superglobals**—predefined arrays that are automatically available in all scopes. These are similar to JSP's implicit objects but work differently from Java's servlet API.

::: code-group

```php [PHP Superglobals]
<?php

declare(strict_types=1);

// $_GET - Query string parameters (like ?id=123)
$userId = $_GET['id'] ?? null;

// $_POST - POST request body data
$username = $_POST['username'] ?? '';

// $_SERVER - Server and request information
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// $_COOKIE - HTTP cookies
$sessionId = $_COOKIE['session_id'] ?? null;

// $_FILES - Uploaded files
$uploadedFile = $_FILES['document'] ?? null;

// $_REQUEST - Combined GET, POST, and COOKIE (avoid using this!)
```

```java [Java Servlet API]
// Java equivalent using HttpServletRequest
public void doGet(HttpServletRequest request, HttpServletResponse response) {
    // Query parameters
    String userId = request.getParameter("id");
    
    // POST data (same method for GET/POST)
    String username = request.getParameter("username");
    
    // Request metadata
    String method = request.getMethod();
    String uri = request.getRequestURI();
    String userAgent = request.getHeader("User-Agent");
    
    // Cookies
    Cookie[] cookies = request.getCookies();
    String sessionId = null;
    if (cookies != null) {
        for (Cookie cookie : cookies) {
            if ("session_id".equals(cookie.getName())) {
                sessionId = cookie.getValue();
                break;
            }
        }
    }
    
    // File uploads (requires multipart/form-data)
    Part filePart = request.getPart("document");
}
```

:::

### Key Differences

| Feature | PHP Superglobals | Java Servlet API |
|---------|------------------|------------------|
| **Access** | Global arrays (`$_GET['id']`) | Object methods (`request.getParameter("id")`) |
| **Type Safety** | No type hints (returns mixed) | Type-safe methods |
| **Immutability** | Mutable (can be modified) | Request is immutable |
| **Scope** | Available everywhere | Must be passed as parameters |
| **Validation** | Manual filtering required | Built-in parameter methods |

### Safe Access Patterns

PHP superglobals can contain any data, so always validate and sanitize:

```php
<?php

declare(strict_types=1);

// ❌ BAD: Direct access without validation
$id = $_GET['id'];
$email = $_POST['email'];

// ✅ GOOD: Use null coalescing and filtering
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    throw new InvalidArgumentException('Invalid ID');
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    throw new InvalidArgumentException('Invalid email');
}

// ✅ GOOD: Use null coalescing operator
$page = $_GET['page'] ?? 1;
$sort = $_GET['sort'] ?? 'asc';
```

### Common `$_SERVER` Variables

```php
<?php

declare(strict_types=1);

// Request information
$method = $_SERVER['REQUEST_METHOD'];           // GET, POST, PUT, DELETE
$uri = $_SERVER['REQUEST_URI'];                  // /users?id=123
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // /users
$query = $_SERVER['QUERY_STRING'] ?? '';         // id=123

// Server information
$serverName = $_SERVER['SERVER_NAME'];           // example.com
$serverPort = (int)$_SERVER['SERVER_PORT'];      // 80 or 443
$protocol = $_SERVER['SERVER_PROTOCOL'];         // HTTP/1.1

// Client information
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';     // Client IP
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? ''; // Browser info

// Headers (prefixed with HTTP_)
$contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
```

### Why It Works

- **Superglobals are automatically populated** by PHP before your script runs
- **`filter_input()`** provides built-in validation and sanitization
- **Null coalescing operator (`??`)** safely handles missing values
- **`parse_url()`** extracts specific parts of URIs safely

### Complete Example: Request Handler Class

Here's a complete example combining superglobals with validation:

```php
<?php

declare(strict_types=1);

namespace App\Http;

class RequestHandler
{
    /**
     * Safely get query parameter with validation
     */
    public function getQueryParam(string $key, ?string $default = null): ?string
    {
        $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
        return $value !== false && $value !== null ? $value : $default;
    }

    /**
     * Get integer query parameter
     */
    public function getIntParam(string $key, ?int $default = null): ?int
    {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
        return $value !== false ? $value : $default;
    }

    /**
     * Get POST data with validation
     */
    public function getPostData(string $key, ?string $default = null): ?string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        return $value !== false && $value !== null ? $value : $default;
    }

    /**
     * Get request method
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get request URI path
     */
    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?? '/';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

// Usage
$handler = new RequestHandler();
$userId = $handler->getIntParam('id');
$method = $handler->getMethod();
$path = $handler->getPath();
```

### Troubleshooting

- **Error: "Undefined array key"** — Use null coalescing (`??`) or `isset()` checks
- **Type errors** — Always validate with `filter_input()` or type casting
- **Security warnings** — Never trust superglobal data; always validate

---

## Section 2: PSR-7 Interfaces

### Goal

Learn how to use PSR-7 interfaces for modern, object-oriented HTTP message handling.

### What is PSR-7?

PSR-7 (PHP Standard Recommendation 7) defines interfaces for HTTP messages. It's similar to Java's Servlet API but designed specifically for PHP. PSR-7 messages are **immutable**—methods return new instances rather than modifying existing ones.

### Installing PSR-7

```bash
# Install PSR-7 interfaces
composer require psr/http-message

# Install a PSR-7 implementation (Guzzle HTTP)
composer require guzzlehttp/psr7
```

### Basic PSR-7 Usage

::: code-group

```php [PHP PSR-7]
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use GuzzleHttp\Psr7\{Request, Response, ServerRequest};

// Create a request
$request = new Request(
    'GET',
    'https://api.example.com/users',
    ['Authorization' => 'Bearer token123'],
    '{"name": "John"}'
);

// Access request properties
$method = $request->getMethod();              // GET
$uri = $request->getUri();                     // UriInterface
$headers = $request->getHeaders();             // ['Authorization' => ['Bearer token123']]
$body = $request->getBody();                   // StreamInterface

// Immutability: methods return new instances
$newRequest = $request->withMethod('POST');
$newRequest = $request->withHeader('Content-Type', 'application/json');

// Create a response
$response = new Response(
    200,
    ['Content-Type' => 'application/json'],
    json_encode(['status' => 'success'])
);

$statusCode = $response->getStatusCode();      // 200
$headers = $response->getHeaders();            // ['Content-Type' => ['application/json']]
```

```java [Java Servlet API]
// Java equivalent
public void doGet(HttpServletRequest request, HttpServletResponse response) {
    // Request properties
    String method = request.getMethod();
    String uri = request.getRequestURI();
    Enumeration<String> headerNames = request.getHeaderNames();
    
    // Response
    response.setStatus(HttpServletResponse.SC_OK);
    response.setContentType("application/json");
    response.getWriter().write("{\"status\":\"success\"}");
}
```

:::

### ServerRequest: Handling Incoming Requests

For handling incoming HTTP requests, use `ServerRequest` which extends `Request`:

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;

// Create ServerRequest from superglobals
$request = ServerRequest::fromGlobals();

// Access parsed body (for JSON, form data, etc.)
$parsedBody = $request->getParsedBody();  // Array or object

// Access query parameters
$queryParams = $request->getQueryParams(); // ['id' => '123']

// Access uploaded files
$uploadedFiles = $request->getUploadedFiles(); // ['file' => UploadedFileInterface]

// Access server parameters (like $_SERVER)
$serverParams = $request->getServerParams();

// Access attributes (custom data)
$request = $request->withAttribute('userId', 123);
$userId = $request->getAttribute('userId');
```

### Working with URIs

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Uri;

// Create URI
$uri = new Uri('https://example.com:8080/users?id=123#section');

// Access URI components
$scheme = $uri->getScheme();        // https
$host = $uri->getHost();            // example.com
$port = $uri->getPort();            // 8080
$path = $uri->getPath();            // /users
$query = $uri->getQuery();          // id=123
$fragment = $uri->getFragment();    // section

// Modify URI (returns new instance)
$newUri = $uri->withPath('/posts')
              ->withQuery('page=2');
```

### Working with Streams

PSR-7 uses streams for request/response bodies:

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

// Create stream from string
$body = \GuzzleHttp\Psr7\Utils::streamFor('Hello, World!');

// Create stream from file
$fileStream = \GuzzleHttp\Psr7\Utils::streamFor(
    fopen('/path/to/file.txt', 'r')
);

// Read from stream
$content = $body->getContents();  // Reads entire stream
$body->rewind();                   // Reset pointer
$chunk = $body->read(5);          // Read 5 bytes

// Write to stream
$writableStream = \GuzzleHttp\Psr7\Utils::streamFor(
    fopen('/path/to/output.txt', 'w')
);
$writableStream->write('Data to write');
```

### Why It Works

- **Immutability** prevents accidental modifications and enables safe sharing
- **Interfaces** allow different implementations (Guzzle, Zend, Symfony)
- **Streams** handle large data efficiently without loading everything into memory
- **Type safety** through interfaces provides better IDE support and error detection

### Complete Example: PSR-7 Request Handler

Here's a complete example using PSR-7 for handling requests:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\{ServerRequest, Response};
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class ApiHandler
{
    public function handle(RequestInterface $request): ResponseInterface
    {
        // Get method and path
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        // Get query parameters
        $queryParams = $request->getQueryParams();
        $userId = $queryParams['id'] ?? null;

        // Get parsed body (for JSON POST requests)
        $body = $request->getParsedBody();
        $data = is_array($body) ? $body : [];

        // Get headers
        $contentType = $request->getHeaderLine('Content-Type');
        $authorization = $request->getHeaderLine('Authorization');

        // Process request based on method
        return match ($method) {
            'GET' => $this->handleGet($request, $userId),
            'POST' => $this->handlePost($request, $data),
            'PUT' => $this->handlePut($request, $userId, $data),
            'DELETE' => $this->handleDelete($request, $userId),
            default => new Response(405, [], 'Method Not Allowed')
        };
    }

    private function handleGet(RequestInterface $request, ?string $userId): ResponseInterface
    {
        if ($userId === null) {
            // Return list
            $data = ['users' => [/* ... */]];
        } else {
            // Return single user
            $data = ['user' => ['id' => $userId, /* ... */]];
        }

        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }

    private function handlePost(RequestInterface $request, array $data): ResponseInterface
    {
        // Create resource
        $newId = 123; // Create logic here

        return new Response(
            201,
            [
                'Content-Type' => 'application/json',
                'Location' => '/api/users/' . $newId
            ],
            json_encode(['id' => $newId, 'data' => $data])
        );
    }

    private function handlePut(RequestInterface $request, ?string $userId, array $data): ResponseInterface
    {
        if ($userId === null) {
            return new Response(400, [], 'Missing user ID');
        }

        // Update logic here
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['id' => $userId, 'updated' => true])
        );
    }

    private function handleDelete(RequestInterface $request, ?string $userId): ResponseInterface
    {
        if ($userId === null) {
            return new Response(400, [], 'Missing user ID');
        }

        // Delete logic here
        return new Response(204); // No Content
    }
}

// Usage
$request = ServerRequest::fromGlobals();
$handler = new ApiHandler();
$response = $handler->handle($request);

// Send response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getBody();
```

### Troubleshooting

- **Error: "Class not found"** — Install PSR-7 implementation (`composer require guzzlehttp/psr7`)
- **Stream already read** — Call `rewind()` before reading again, or use `getContents()` once
- **Memory issues** — Use streams for large files instead of loading entire content

---

## Section 2.5: Content Negotiation and Request Body Parsing

### Goal

Learn how to handle content negotiation (Accept headers) and parse different request body formats.

### Content Negotiation

Content negotiation allows clients to specify what content types, languages, and encodings they prefer. PHP doesn't have built-in content negotiation, but you can implement it easily.

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;

class ContentNegotiation
{
    /**
     * Parse Accept header and return preferred content type
     */
    public static function negotiateContentType(
        RequestInterface $request,
        array $supportedTypes = ['application/json', 'application/xml', 'text/html']
    ): ?string {
        $acceptHeader = $request->getHeaderLine('Accept');
        
        if (empty($acceptHeader)) {
            return $supportedTypes[0]; // Default to first supported type
        }

        // Parse Accept header with quality values
        // Accept: application/json, application/xml;q=0.9, text/html;q=0.8
        $acceptedTypes = [];
        foreach (explode(',', $acceptHeader) as $type) {
            $parts = explode(';', trim($type));
            $mimeType = trim($parts[0]);
            $quality = 1.0;

            if (isset($parts[1]) && str_starts_with($parts[1], 'q=')) {
                $quality = (float)substr($parts[1], 2);
            }

            $acceptedTypes[$mimeType] = $quality;
        }

        // Sort by quality (descending)
        arsort($acceptedTypes);

        // Find best match
        foreach ($acceptedTypes as $mimeType => $quality) {
            // Exact match
            if (in_array($mimeType, $supportedTypes, true)) {
                return $mimeType;
            }

            // Wildcard match (application/*)
            if (str_ends_with($mimeType, '/*')) {
                $baseType = substr($mimeType, 0, -2);
                foreach ($supportedTypes as $supported) {
                    if (str_starts_with($supported, $baseType . '/')) {
                        return $supported;
                    }
                }
            }
        }

        // Check for */* wildcard
        if (isset($acceptedTypes['*/*'])) {
            return $supportedTypes[0];
        }

        return null; // No acceptable type found
    }

    /**
     * Negotiate language from Accept-Language header
     */
    public static function negotiateLanguage(
        RequestInterface $request,
        array $supportedLanguages = ['en', 'es', 'fr']
    ): string {
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        
        if (empty($acceptLanguage)) {
            return $supportedLanguages[0];
        }

        $acceptedLanguages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $language = trim($parts[0]);
            $quality = 1.0;

            if (isset($parts[1]) && str_starts_with($parts[1], 'q=')) {
                $quality = (float)substr($parts[1], 2);
            }

            // Extract base language (en-US -> en)
            $baseLang = explode('-', $language)[0];
            $acceptedLanguages[$baseLang] = max($acceptedLanguages[$baseLang] ?? 0, $quality);
        }

        arsort($acceptedLanguages);

        foreach ($acceptedLanguages as $lang => $quality) {
            if (in_array($lang, $supportedLanguages, true)) {
                return $lang;
            }
        }

        return $supportedLanguages[0];
    }
}

// Usage
$request = ServerRequest::fromGlobals();
$contentType = ContentNegotiation::negotiateContentType(
    $request,
    ['application/json', 'application/xml']
);
$language = ContentNegotiation::negotiateLanguage($request, ['en', 'es']);
```

### Request Body Parsing

Different content types require different parsing approaches:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;

class RequestBodyParser
{
    /**
     * Parse request body based on Content-Type
     */
    public static function parse(RequestInterface $request): mixed
    {
        $contentType = $request->getHeaderLine('Content-Type');
        
        // Remove charset and other parameters
        $contentType = explode(';', $contentType)[0];
        $contentType = trim($contentType);

        $body = $request->getBody()->getContents();

        return match ($contentType) {
            'application/json' => self::parseJson($body),
            'application/xml', 'text/xml' => self::parseXml($body),
            'application/x-www-form-urlencoded' => self::parseFormUrlencoded($body),
            'multipart/form-data' => self::parseMultipart($request),
            default => $body // Return raw body
        };
    }

    private static function parseJson(string $body): array
    {
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                'Invalid JSON: ' . json_last_error_msg()
            );
        }

        return $data ?? [];
    }

    private static function parseXml(string $body): \SimpleXMLElement
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \InvalidArgumentException(
                'Invalid XML: ' . ($errors[0]->message ?? 'Unknown error')
            );
        }

        return $xml;
    }

    private static function parseFormUrlencoded(string $body): array
    {
        parse_str($body, $data);
        return $data;
    }

    private static function parseMultipart(RequestInterface $request): array
    {
        // For multipart, use getParsedBody() which PSR-7 handles
        $parsed = $request->getParsedBody();
        return is_array($parsed) ? $parsed : [];
    }
}

// Usage
$request = ServerRequest::fromGlobals();
$data = RequestBodyParser::parse($request);
```

### Why It Works

- **Content negotiation** allows clients to specify preferences, improving API flexibility
- **Quality values (q-values)** let clients prioritize content types (e.g., `application/json;q=0.9`)
- **Wildcard matching** (`*/*`, `application/*`) provides fallback options
- **Body parsing** handles different content types appropriately (JSON, XML, form data)

---

## Section 3: Headers and Cookies

### Goal

Learn how to manage HTTP headers and cookies in PHP, comparing to Java's servlet API.

### Setting HTTP Headers

::: code-group

```php [PHP Headers]
<?php

declare(strict_types=1);

// Set response headers (must be called before any output)
header('Content-Type: application/json');
header('X-Custom-Header: value');
header('Location: /redirect', true, 302);  // Redirect with status code

// Set multiple headers at once
header('Content-Type: application/json', false);
header('Cache-Control: no-cache, must-revalidate', false);

// Check if headers already sent
if (!headers_sent()) {
    header('X-Custom: value');
} else {
    error_log('Cannot set headers - output already started');
}

// Get response headers (using PSR-7)
use GuzzleHttp\Psr7\Response;

$response = new Response(200, [
    'Content-Type' => 'application/json',
    'X-Custom' => 'value'
]);

// Modify headers (immutable)
$newResponse = $response->withHeader('X-New-Header', 'new-value');
$newResponse = $response->withAddedHeader('X-Custom', 'another-value');
```

```java [Java Headers]
// Java Servlet API equivalent
public void doGet(HttpServletRequest request, HttpServletResponse response) {
    // Set headers
    response.setContentType("application/json");
    response.setHeader("X-Custom-Header", "value");
    response.setStatus(HttpServletResponse.SC_FOUND);
    response.setHeader("Location", "/redirect");
    
    // Add header (allows multiple values)
    response.addHeader("X-Custom", "value1");
    response.addHeader("X-Custom", "value2");
    
    // Check if response committed
    if (!response.isCommitted()) {
        response.setHeader("X-Custom", "value");
    }
}
```

:::

### Reading Request Headers

```php
<?php

declare(strict_types=1);

// Using superglobals (headers prefixed with HTTP_)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

// Using PSR-7
use GuzzleHttp\Psr7\ServerRequest;

$request = ServerRequest::fromGlobals();

// Get single header (returns array of values)
$userAgent = $request->getHeader('User-Agent');
$firstValue = $request->getHeaderLine('User-Agent');  // Comma-separated string

// Check if header exists
if ($request->hasHeader('Authorization')) {
    $authHeader = $request->getHeaderLine('Authorization');
}
```

### Working with Cookies

::: code-group

```php [PHP Cookies]
<?php

declare(strict_types=1);

// Set cookie (basic)
setcookie('username', 'john', time() + 3600);

// Set cookie with options
setcookie(
    'session_id',
    'abc123',
    [
        'expires' => time() + 3600,      // Expiration time
        'path' => '/',                    // Available path
        'domain' => '.example.com',      // Domain
        'secure' => true,                 // HTTPS only
        'httponly' => true,               // JavaScript cannot access
        'samesite' => 'Strict'           // CSRF protection
    ]
);

// Read cookies
$username = $_COOKIE['username'] ?? null;
$sessionId = $_COOKIE['session_id'] ?? null;

// Delete cookie (set expiration in past)
setcookie('username', '', time() - 3600, '/');

// Using PSR-7 for cookies
use GuzzleHttp\Psr7\ServerRequest;

$request = ServerRequest::fromGlobals();
$cookies = $request->getCookieParams();  // ['username' => 'john']
```

```java [Java Cookies]
// Java Servlet API equivalent
public void doGet(HttpServletRequest request, HttpServletResponse response) {
    // Set cookie
    Cookie cookie = new Cookie("username", "john");
    cookie.setMaxAge(3600);
    cookie.setPath("/");
    cookie.setSecure(true);
    cookie.setHttpOnly(true);
    response.addCookie(cookie);
    
    // Read cookies
    Cookie[] cookies = request.getCookies();
    if (cookies != null) {
        for (Cookie c : cookies) {
            if ("username".equals(c.getName())) {
                String value = c.getValue();
                break;
            }
        }
    }
    
    // Delete cookie
    Cookie deleteCookie = new Cookie("username", "");
    deleteCookie.setMaxAge(0);
    response.addCookie(deleteCookie);
}
```

:::

### Security Best Practices for Cookies

```php
<?php

declare(strict_types=1);

/**
 * Set a secure session cookie
 */
function setSecureCookie(
    string $name,
    string $value,
    int $expiresIn = 3600
): bool {
    return setcookie(
        $name,
        $value,
        [
            'expires' => time() + $expiresIn,
            'path' => '/',
            'domain' => '',  // Current domain only
            'secure' => true,  // HTTPS only
            'httponly' => true,  // Prevent XSS
            'samesite' => 'Strict'  // CSRF protection
        ]
    );
}

// Usage
setSecureCookie('session_id', bin2hex(random_bytes(32)));
```

### Why It Works

- **`header()` function** sends raw HTTP headers before content
- **`setcookie()`** sets the `Set-Cookie` header with proper formatting
- **PSR-7 interfaces** provide object-oriented, immutable header management
- **Cookie options** control security, scope, and expiration

### Complete Example: Response Builder

Here's a utility class for building responses:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\Response;

class ResponseBuilder
{
    /**
     * Create JSON response
     */
    public static function json(
        mixed $data,
        int $statusCode = 200,
        array $headers = []
    ): Response {
        $headers['Content-Type'] = 'application/json';
        
        return new Response(
            $statusCode,
            $headers,
            json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        );
    }

    /**
     * Create redirect response
     */
    public static function redirect(string $url, int $statusCode = 302): Response
    {
        return new Response(
            $statusCode,
            ['Location' => $url],
            ''
        );
    }

    /**
     * Create error response
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        ?array $errors = null
    ): Response {
        $data = ['error' => $message];
        
        if ($errors !== null) {
            $data['errors'] = $errors;
        }

        return self::json($data, $statusCode);
    }

    /**
     * Create success response
     */
    public static function success(
        mixed $data,
        string $message = 'Success',
        int $statusCode = 200
    ): Response {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}

// Usage
$response = ResponseBuilder::json(['users' => []], 200);
$response = ResponseBuilder::redirect('/login', 302);
$response = ResponseBuilder::error('Validation failed', 422, ['email' => 'Invalid']);
```

### CORS Preflight Requests

CORS (Cross-Origin Resource Sharing) preflight requests use the OPTIONS method to check if a cross-origin request is allowed:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use GuzzleHttp\Psr7\Response;

class CorsPreflightMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;
    private int $maxAge;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization'],
        int $maxAge = 3600
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
        $this->maxAge = $maxAge;
    }

    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflight($request);
        }

        // Handle actual request - add CORS headers to response
        $response = $handler->handle($request);
        return $this->addCorsHeaders($request, $response);
    }

    private function handlePreflight(RequestInterface $request): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $requestMethod = $request->getHeaderLine('Access-Control-Request-Method');
        $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

        // Check if origin is allowed
        if (!$this->isOriginAllowed($origin)) {
            return new Response(403, [], 'Origin not allowed');
        }

        // Build CORS headers
        $headers = [
            'Access-Control-Allow-Origin' => $this->getAllowedOrigin($origin),
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => $requestHeaders ?: implode(', ', $this->allowedHeaders),
            'Access-Control-Max-Age' => (string)$this->maxAge,
        ];

        // Add credentials if needed
        if (in_array($origin, $this->allowedOrigins, true)) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        return new Response(204, $headers, '');
    }

    private function addCorsHeaders(
        RequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $origin = $request->getHeaderLine('Origin');
        
        if (!$this->isOriginAllowed($origin)) {
            return $response;
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($origin))
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Expose-Headers', 'X-Total-Count, X-Page-Count');
    }

    private function isOriginAllowed(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        if (in_array('*', $this->allowedOrigins, true)) {
            return true;
        }

        return in_array($origin, $this->allowedOrigins, true);
    }

    private function getAllowedOrigin(string $origin): string
    {
        return in_array('*', $this->allowedOrigins, true) ? '*' : $origin;
    }
}
```

### Caching Headers

HTTP caching headers help reduce server load and improve performance:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class CacheControl
{
    /**
     * Add ETag and caching headers to response
     */
    public static function withEtag(
        ResponseInterface $response,
        string $content,
        int $maxAge = 3600
    ): ResponseInterface {
        // Generate ETag from content
        $etag = '"' . md5($content) . '"';
        
        return $response
            ->withHeader('ETag', $etag)
            ->withHeader('Cache-Control', "public, max-age=$maxAge")
            ->withHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
    }

    /**
     * Check if request has valid cached version (304 Not Modified)
     */
    public static function checkNotModified(
        RequestInterface $request,
        ResponseInterface $response
    ): ?ResponseInterface {
        // Check If-None-Match (ETag)
        $ifNoneMatch = $request->getHeaderLine('If-None-Match');
        $etag = $response->getHeaderLine('ETag');
        
        if ($ifNoneMatch && $etag && $ifNoneMatch === $etag) {
            return $response->withStatus(304)->withBody(
                \GuzzleHttp\Psr7\Utils::streamFor('')
            );
        }

        // Check If-Modified-Since
        $ifModifiedSince = $request->getHeaderLine('If-Modified-Since');
        $lastModified = $response->getHeaderLine('Last-Modified');
        
        if ($ifModifiedSince && $lastModified) {
            $ifModifiedSinceTime = strtotime($ifModifiedSince);
            $lastModifiedTime = strtotime($lastModified);
            
            if ($ifModifiedSinceTime >= $lastModifiedTime) {
                return $response->withStatus(304)->withBody(
                    \GuzzleHttp\Psr7\Utils::streamFor('')
                );
            }
        }

        return null; // Not modified check failed, return original response
    }

    /**
     * Set no-cache headers
     */
    public static function noCache(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');
    }
}

// Usage
$request = ServerRequest::fromGlobals();
$content = json_encode(['data' => 'value']);
$response = new Response(200, ['Content-Type' => 'application/json'], $content);

// Add caching
$response = CacheControl::withEtag($response, $content, 3600);

// Check if client has cached version
$notModified = CacheControl::checkNotModified($request, $response);
if ($notModified !== null) {
    // Send 304 Not Modified
    $response = $notModified;
}
```

### Content Encoding (Gzip Compression)

Compress responses to reduce bandwidth:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class CompressionMiddleware implements MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        // Check if client accepts gzip
        $acceptEncoding = $request->getHeaderLine('Accept-Encoding');
        
        if (str_contains($acceptEncoding, 'gzip')) {
            $body = $response->getBody()->getContents();
            $compressed = gzencode($body, 6); // Compression level 1-9

            if ($compressed !== false) {
                return $response
                    ->withHeader('Content-Encoding', 'gzip')
                    ->withHeader('Content-Length', (string)strlen($compressed))
                    ->withBody(\GuzzleHttp\Psr7\Utils::streamFor($compressed));
            }
        }

        return $response;
    }
}
```

### HTTP Method Override

Support PUT/DELETE methods via POST (useful for HTML forms):

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class MethodOverrideMiddleware implements MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check X-HTTP-Method-Override header
        $overrideMethod = $request->getHeaderLine('X-HTTP-Method-Override');
        
        if (!empty($overrideMethod)) {
            $request = $request->withMethod(strtoupper($overrideMethod));
        }
        // Check _method parameter in POST body
        elseif ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            
            if (is_array($parsedBody) && isset($parsedBody['_method'])) {
                $method = strtoupper($parsedBody['_method']);
                
                if (in_array($method, ['PUT', 'DELETE', 'PATCH'], true)) {
                    $request = $request->withMethod($method);
                    // Remove _method from parsed body
                    unset($parsedBody['_method']);
                    $request = $request->withParsedBody($parsedBody);
                }
            }
        }

        return $handler->handle($request);
    }
}

// Usage in HTML form
// <form method="POST">
//     <input type="hidden" name="_method" value="PUT">
//     <!-- form fields -->
// </form>
```

### Troubleshooting

- **Error: "Cannot modify header information"** — Headers already sent; check for output before `header()` calls
- **Cookies not working** — Check `secure` flag (must be `false` for HTTP), `path`, and `domain` settings
- **SameSite warnings** — Use `'Strict'` or `'Lax'` for CSRF protection
- **CORS preflight failing** — Ensure OPTIONS method is handled and all required headers are returned
- **304 Not Modified not working** — Verify ETag or Last-Modified headers are set correctly

---

## Section 4: File Uploads

### Goal

Learn how to handle file uploads securely in PHP, comparing to Java's multipart handling.

### Basic File Upload Handling

::: code-group

```php [PHP File Uploads]
<?php

declare(strict_types=1);

// Check if file was uploaded
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['document'];
    
    // File information
    $fileName = $file['name'];
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $tmpName = $file['tmp_name'];
    $error = $file['error'];
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024;  // 5MB
    
    if (!in_array($fileType, $allowedTypes)) {
        throw new InvalidArgumentException('Invalid file type');
    }
    
    if ($fileSize > $maxSize) {
        throw new InvalidArgumentException('File too large');
    }
    
    // Move uploaded file to destination
    $destination = __DIR__ . '/uploads/' . basename($fileName);
    if (move_uploaded_file($tmpName, $destination)) {
        echo "File uploaded successfully";
    } else {
        throw new RuntimeException('Failed to move uploaded file');
    }
}
```

```java [Java File Upload]
// Java Servlet API equivalent
@MultipartConfig
public class FileUploadServlet extends HttpServlet {
    
    protected void doPost(HttpServletRequest request, HttpServletResponse response) {
        try {
            Part filePart = request.getPart("document");
            String fileName = getFileName(filePart);
            String contentType = filePart.getContentType();
            long fileSize = filePart.getSize();
            
            // Validate
            List<String> allowedTypes = Arrays.asList("image/jpeg", "image/png", "application/pdf");
            long maxSize = 5 * 1024 * 1024;
            
            if (!allowedTypes.contains(contentType)) {
                throw new IllegalArgumentException("Invalid file type");
            }
            
            if (fileSize > maxSize) {
                throw new IllegalArgumentException("File too large");
            }
            
            // Save file
            String destination = getServletContext().getRealPath("/uploads/") + fileName;
            filePart.write(destination);
            
        } catch (Exception e) {
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }
    
    private String getFileName(Part part) {
        String contentDisposition = part.getHeader("content-disposition");
        // Parse filename from content-disposition header
        return /* parsed filename */;
    }
}
```

:::

### Upload Error Codes

```php
<?php

declare(strict_types=1);

function handleUploadError(int $errorCode): string
{
    return match ($errorCode) {
        UPLOAD_ERR_OK => 'No error',
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload',
        default => 'Unknown error'
    };
}

// Usage
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = handleUploadError($_FILES['file']['error']);
    throw new RuntimeException("Upload failed: $errorMessage");
}
```

### Secure File Upload Class

```php
<?php

declare(strict_types=1);

class SecureFileUpload
{
    private array $allowedTypes;
    private int $maxSize;
    private string $uploadDir;

    public function __construct(
        array $allowedTypes,
        int $maxSize,
        string $uploadDir
    ) {
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(string $fieldName): string
    {
        if (!isset($_FILES[$fieldName])) {
            throw new InvalidArgumentException("No file uploaded for field: $fieldName");
        }

        $file = $_FILES[$fieldName];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Upload error: " . $file['error']);
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes, true)) {
            throw new InvalidArgumentException("Invalid file type: $mimeType");
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            throw new InvalidArgumentException("File too large: {$file['size']} bytes");
        }

        // Generate secure filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->uploadDir . $safeFileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException("Failed to move uploaded file");
        }

        return $safeFileName;
    }
}

// Usage
$uploader = new SecureFileUpload(
    ['image/jpeg', 'image/png', 'application/pdf'],
    5 * 1024 * 1024,  // 5MB
    __DIR__ . '/uploads'
);

try {
    $fileName = $uploader->upload('document');
    echo "File uploaded: $fileName";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Using PSR-7 for File Uploads

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;

$request = ServerRequest::fromGlobals();
$uploadedFiles = $request->getUploadedFiles();

if (isset($uploadedFiles['document'])) {
    $file = $uploadedFiles['document'];
    
    if ($file->getError() === UPLOAD_ERR_OK) {
        $stream = $file->getStream();
        $destination = __DIR__ . '/uploads/' . $file->getClientFilename();
        
        // Write stream to file
        $handle = fopen($destination, 'wb');
        while (!$stream->eof()) {
            fwrite($handle, $stream->read(8192));
        }
        fclose($handle);
    }
}
```

### Why It Works

- **`$_FILES` superglobal** contains uploaded file information
- **`move_uploaded_file()`** safely moves files from temporary location
- **`finfo_file()`** detects actual MIME type (more secure than trusting client)
- **Random filenames** prevent overwriting and directory traversal attacks
- **PSR-7 `UploadedFileInterface`** provides object-oriented file handling

### Complete Example: File Upload Endpoint

Here's a complete file upload endpoint using PSR-7:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Message\UploadedFileInterface;
use App\Http\ResponseBuilder;

class FileUploadController
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    private const UPLOAD_DIR = __DIR__ . '/../../uploads/';

    public function upload(RequestInterface $request): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();

        if (!isset($uploadedFiles['file'])) {
            return ResponseBuilder::error('No file uploaded', 400);
        }

        $file = $uploadedFiles['file'];

        // Check upload error
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return ResponseBuilder::error(
                'Upload failed: ' . $this->getUploadErrorMessage($file->getError()),
                400
            );
        }

        // Validate file type
        $mimeType = $this->detectMimeType($file);
        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            return ResponseBuilder::error("Invalid file type: $mimeType", 400);
        }

        // Validate file size
        if ($file->getSize() > self::MAX_SIZE) {
            return ResponseBuilder::error('File too large', 400);
        }

        // Generate secure filename
        $extension = $this->getExtensionFromMimeType($mimeType);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = self::UPLOAD_DIR . $filename;

        // Ensure upload directory exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        // Move uploaded file
        try {
            $file->moveTo($destination);
        } catch (\RuntimeException $e) {
            return ResponseBuilder::error('Failed to save file', 500);
        }

        return ResponseBuilder::success([
            'filename' => $filename,
            'size' => $file->getSize(),
            'type' => $mimeType,
            'url' => '/uploads/' . $filename
        ], 'File uploaded successfully', 201);
    }

    private function detectMimeType(UploadedFileInterface $file): string
    {
        // Use finfo for accurate MIME type detection
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $stream = $file->getStream();
        $stream->rewind();
        $chunk = $stream->read(8192);
        $mimeType = finfo_buffer($finfo, $chunk);
        finfo_close($finfo);
        
        return $mimeType ?: 'application/octet-stream';
    }

    private function getExtensionFromMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            default => 'bin'
        };
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload',
            default => 'Unknown upload error'
        };
    }
}

// Usage
$request = ServerRequest::fromGlobals();
$controller = new FileUploadController();
$response = $controller->upload($request);
```

### Request Size Limits

PHP has several configuration limits for request handling:

```php
<?php

declare(strict_types=1);

namespace App\Http;

class RequestLimits
{
    /**
     * Check if request exceeds size limits
     */
    public static function checkLimits(): array
    {
        $limits = [
            'post_max_size' => self::parseSize(ini_get('post_max_size')),
            'upload_max_filesize' => self::parseSize(ini_get('upload_max_filesize')),
            'max_file_uploads' => (int)ini_get('max_file_uploads'),
            'memory_limit' => self::parseSize(ini_get('memory_limit')),
        ];

        // Check Content-Length header
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        $limits['request_size'] = (int)$contentLength;
        $limits['exceeds_post_max'] = $contentLength > $limits['post_max_size'];

        return $limits;
    }

    /**
     * Parse PHP size string (e.g., "8M", "128K") to bytes
     */
    private static function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int)$size;

        return match ($last) {
            'g' => $size * 1024 * 1024 * 1024,
            'm' => $size * 1024 * 1024,
            'k' => $size * 1024,
            default => $size
        };
    }

    /**
     * Validate request size before processing
     */
    public static function validateRequestSize(int $maxSize = 0): void
    {
        if ($maxSize === 0) {
            $maxSize = self::parseSize(ini_get('post_max_size'));
        }

        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($contentLength > $maxSize) {
            throw new \RuntimeException(
                "Request size ($contentLength bytes) exceeds maximum ($maxSize bytes)"
            );
        }
    }
}

// Usage
try {
    RequestLimits::validateRequestSize();
    // Process request
} catch (\RuntimeException $e) {
    http_response_code(413); // Payload Too Large
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Range Requests (Partial Content)

Support HTTP Range requests for file downloads and streaming:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\{Response, ServerRequest};
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class RangeRequestHandler
{
    /**
     * Handle Range request and return 206 Partial Content or full file
     */
    public static function handle(
        RequestInterface $request,
        string $filePath,
        string $contentType = 'application/octet-stream'
    ): ResponseInterface {
        if (!file_exists($filePath)) {
            return new Response(404, [], 'File not found');
        }

        $fileSize = filesize($filePath);
        $rangeHeader = $request->getHeaderLine('Range');

        // No Range header - return full file
        if (empty($rangeHeader)) {
            return self::sendFullFile($filePath, $fileSize, $contentType);
        }

        // Parse Range header: bytes=0-499, 500-999, etc.
        if (!preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
            return new Response(416, ['Content-Range' => "bytes */$fileSize"], 'Range Not Satisfiable');
        }

        $start = (int)$matches[1];
        $end = !empty($matches[2]) ? (int)$matches[2] : $fileSize - 1;

        // Validate range
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            return new Response(416, ['Content-Range' => "bytes */$fileSize"], 'Range Not Satisfiable');
        }

        $length = $end - $start + 1;

        // Read file chunk
        $handle = fopen($filePath, 'rb');
        fseek($handle, $start);
        $content = fread($handle, $length);
        fclose($handle);

        $headers = [
            'Content-Range' => "bytes $start-$end/$fileSize",
            'Content-Length' => (string)$length,
            'Content-Type' => $contentType,
            'Accept-Ranges' => 'bytes',
        ];

        return new Response(206, $headers, $content);
    }

    private static function sendFullFile(
        string $filePath,
        int $fileSize,
        string $contentType
    ): ResponseInterface {
        $headers = [
            'Content-Length' => (string)$fileSize,
            'Content-Type' => $contentType,
            'Accept-Ranges' => 'bytes',
        ];

        $stream = \GuzzleHttp\Psr7\Utils::streamFor(fopen($filePath, 'rb'));
        
        return new Response(200, $headers, $stream);
    }
}

// Usage
$request = ServerRequest::fromGlobals();
$response = RangeRequestHandler::handle(
    $request,
    '/path/to/video.mp4',
    'video/mp4'
);
```

### Troubleshooting

- **Error: "File exceeds upload_max_filesize"** — Increase `upload_max_filesize` in `php.ini`
- **Error: "No file uploaded"** — Check form has `enctype="multipart/form-data"`
- **Permission errors** — Ensure upload directory is writable (`chmod 755`)
- **Security warnings** — Always validate file type and size; never trust client-provided names
- **Request too large** — Check `post_max_size` and `memory_limit` in `php.ini`
- **Range request failing** — Verify file exists and Range header is properly formatted

---

## Section 5: Middleware Pattern

### Goal

Implement middleware patterns in PHP, similar to Java servlet filters.

### What is Middleware?

Middleware is code that executes before and/or after your main request handler. It's similar to Java's servlet filters and allows you to add cross-cutting concerns like authentication, logging, and CORS handling.

### Basic Middleware Interface

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Middleware interface (PSR-15 style)
 */
interface MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;
}
```

### Simple Middleware Examples

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use GuzzleHttp\Psr7\Response;

/**
 * Logging middleware
 */
class LoggingMiddleware implements MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Before: Log request
        error_log(sprintf(
            '%s %s - %s',
            $request->getMethod(),
            $request->getUri()->getPath(),
            date('Y-m-d H:i:s')
        ));

        // Call next middleware/handler
        $response = $handler->handle($request);

        // After: Log response
        error_log(sprintf(
            'Response: %d - %s',
            $response->getStatusCode(),
            date('Y-m-d H:i:s')
        ));

        return $response;
    }
}

/**
 * Authentication middleware
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check authentication
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || !$this->isValidToken($authHeader)) {
            return new Response(401, [], 'Unauthorized');
        }

        // Add user info to request attributes
        $user = $this->getUserFromToken($authHeader);
        $request = $request->withAttribute('user', $user);

        // Continue to next handler
        return $handler->handle($request);
    }

    private function isValidToken(string $authHeader): bool
    {
        // Token validation logic
        return str_starts_with($authHeader, 'Bearer ') && 
               strlen($authHeader) > 20;
    }

    private function getUserFromToken(string $authHeader): array
    {
        // Extract and decode token
        $token = str_replace('Bearer ', '', $authHeader);
        // Decode JWT or lookup user
        return ['id' => 123, 'username' => 'john'];
    }
}

/**
 * CORS middleware
 */
class CorsMiddleware implements MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        // Add CORS headers
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

### Middleware Stack

```php
<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Middleware stack (similar to Java FilterChain)
 */
class MiddlewareStack implements RequestHandlerInterface
{
    private array $middlewares = [];
    private RequestHandlerInterface $fallbackHandler;

    public function __construct(RequestHandlerInterface $fallbackHandler)
    {
        $this->fallbackHandler = $fallbackHandler;
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares)) {
            return $this->fallbackHandler->handle($request);
        }

        // Create handler chain
        $handler = $this->fallbackHandler;
        
        // Build chain in reverse order
        for ($i = count($this->middlewares) - 1; $i >= 0; $i--) {
            $middleware = $this->middlewares[$i];
            $handler = new MiddlewareHandler($middleware, $handler);
        }

        return $handler->handle($request);
    }
}

/**
 * Wrapper to execute middleware
 */
class MiddlewareHandler implements RequestHandlerInterface
{
    public function __construct(
        private MiddlewareInterface $middleware,
        private RequestHandlerInterface $next
    ) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->next);
    }
}

// Usage
$stack = new MiddlewareStack($finalHandler);
$stack->add(new CorsMiddleware());
$stack->add(new LoggingMiddleware());
$stack->add(new AuthMiddleware());

$response = $stack->handle($request);
```

### Simple Middleware (Without PSR-15)

If you're not using PSR-15, you can create a simpler middleware pattern:

```php
<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Simple middleware interface
 */
interface Middleware
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface;
}

/**
 * Simple middleware stack
 */
class MiddlewarePipeline
{
    private array $middlewares = [];

    public function add(Middleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function process(RequestInterface $request, callable $finalHandler): ResponseInterface
    {
        $handler = $finalHandler;

        // Build chain in reverse
        foreach (array_reverse($this->middlewares) as $middleware) {
            $handler = function ($request) use ($middleware, $handler) {
                return $middleware->handle($request, $handler);
            };
        }

        return $handler($request);
    }
}

// Usage
$pipeline = new MiddlewarePipeline();
$pipeline->add(new LoggingMiddleware());
$pipeline->add(new AuthMiddleware());

$response = $pipeline->process($request, function ($request) {
    // Final handler
    return new Response(200, [], 'Hello, World!');
});
```

### Why It Works

- **Middleware pattern** allows cross-cutting concerns without modifying core logic
- **Chain of responsibility** passes request through middleware stack
- **PSR-15 standard** provides interoperability between frameworks
- **Immutability** ensures middleware doesn't accidentally modify shared state

### Complete Example: Middleware Application

Here's a complete example showing middleware in action:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\{ServerRequest, Response};
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Complete middleware application example
 */
class Application
{
    private array $middlewares = [];
    private RequestHandlerInterface $fallbackHandler;

    public function __construct(?RequestHandlerInterface $fallbackHandler = null)
    {
        $this->fallbackHandler = $fallbackHandler ?? new class implements RequestHandlerInterface {
            public function handle(RequestInterface $request): ResponseInterface
            {
                return new Response(404, [], 'Not Found');
            }
        };
    }

    public function add(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function run(RequestInterface $request): ResponseInterface
    {
        $handler = $this->fallbackHandler;

        // Build middleware chain in reverse order
        foreach (array_reverse($this->middlewares) as $middleware) {
            $handler = new MiddlewareHandler($middleware, $handler);
        }

        return $handler->handle($request);
    }
}

/**
 * Wrapper for middleware execution
 */
class MiddlewareHandler implements RequestHandlerInterface
{
    public function __construct(
        private MiddlewareInterface $middleware,
        private RequestHandlerInterface $next
    ) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        return $this->middleware->process($request, $this->next);
    }
}

// Usage example
$app = new Application();

// Add middleware in order (executes first to last)
$app->add(new CorsMiddleware())
    ->add(new LoggingMiddleware())
    ->add(new AuthMiddleware())
    ->add(new RateLimitMiddleware());

// Create request from superglobals
$request = ServerRequest::fromGlobals();

// Run application
$response = $app->run($request);

// Send response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getBody();
```

::: tip Middleware Execution Order

Middleware executes in the order it's added:
1. **CORS** - Adds headers (runs first, last to modify response)
2. **Logging** - Logs request/response
3. **Auth** - Validates authentication
4. **Rate Limit** - Checks rate limits (runs last before handler)

The response flows back through middleware in reverse order, allowing CORS to modify headers last.
:::

### Troubleshooting

- **Middleware not executing** — Check order in stack (executes in order added)
- **Response not returned** — Ensure middleware calls `$handler->handle($request)`
- **Headers already sent** — Use PSR-7 responses instead of `header()` function

---

## Exercises

### Exercise 1: Request Validator Middleware

**Goal**: Create a request validation middleware that checks required parameters.

Create a `RequestValidatorMiddleware` class that:

- Validates required query parameters exist
- Validates required POST body fields exist
- Returns 400 Bad Request if validation fails
- Adds validated data to request attributes if successful

**Starter Code**:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use GuzzleHttp\Psr7\Response;

class RequestValidatorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private array $requiredQueryParams = [],
        private array $requiredBodyFields = []
    ) {}

    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // TODO: Validate query parameters
        // TODO: Validate body fields
        // TODO: Return 400 if validation fails
        // TODO: Add validated data to request attributes
        // TODO: Call next handler if validation passes
    }
}
```

**Validation**: Test with valid and invalid requests:

```php
// Valid request
GET /api/users?id=123

// Invalid request
GET /api/users
// Should return 400 with error message: {"error": "Missing required parameter: id"}
```

**Hint**: Use `$request->getQueryParams()` and `$request->getParsedBody()` to access data.

### Exercise 2: Rate Limiting Middleware

**Goal**: Implement rate limiting to prevent abuse.

Create a `RateLimitMiddleware` class that:

- Tracks requests per IP address
- Limits to 100 requests per hour per IP
- Returns 429 Too Many Requests if limit exceeded
- Adds `X-RateLimit-Remaining` header to responses

**Starter Code**:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class RateLimitMiddleware implements MiddlewareInterface
{
    private array $requestCounts = [];
    private const MAX_REQUESTS = 100;
    private const TIME_WINDOW = 3600; // 1 hour

    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // TODO: Get client IP from request
        // TODO: Check request count for this IP
        // TODO: Increment count if within time window
        // TODO: Return 429 if limit exceeded
        // TODO: Add X-RateLimit-Remaining header
        // TODO: Call next handler if within limit
    }

    private function getClientIp(RequestInterface $request): string
    {
        // Get IP from X-Forwarded-For header or REMOTE_ADDR
        $serverParams = $request->getServerParams();
        return $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
```

**Validation**: Make 101 requests quickly and verify 429 response on the 101st request.

**Hint**: Store request timestamps in an array keyed by IP address. Clean up old entries periodically.

### Exercise 3: File Upload Handler

**Goal**: Create a secure file upload endpoint.

Create a file upload handler that:

- Accepts only image files (JPEG, PNG, GIF)
- Limits file size to 2MB
- Generates secure random filenames
- Stores files in `uploads/` directory
- Returns JSON response with file information

**Starter Code**:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use App\Http\ResponseBuilder;

class FileUploadController
{
    public function upload(RequestInterface $request): ResponseInterface
    {
        // TODO: Get uploaded file from request
        // TODO: Validate file type (use finfo)
        // TODO: Validate file size
        // TODO: Generate secure filename
        // TODO: Move file to uploads directory
        // TODO: Return success response with file info
    }
}
```

**Validation**: Upload valid and invalid files and verify proper handling:

```bash
# Valid upload
curl -X POST -F "file=@image.jpg" http://localhost:8000/upload

# Invalid upload (too large)
curl -X POST -F "file=@large-file.jpg" http://localhost:8000/upload
# Should return: {"error": "File too large"}

# Invalid upload (wrong type)
curl -X POST -F "file=@document.pdf" http://localhost:8000/upload
# Should return: {"error": "Invalid file type"}
```

---

## Complete Example: HTTP Application

Let's combine everything into a complete HTTP application example:

```php
<?php

declare(strict_types=1);

// public/index.php - Entry point
require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\{Application, RequestHandler};
use App\Http\Middleware\{CorsMiddleware, LoggingMiddleware, AuthMiddleware};
use GuzzleHttp\Psr7\ServerRequest;

// Create application (uses default 404 handler)
$app = new Application();

// Add middleware
$app->add(new CorsMiddleware())
    ->add(new LoggingMiddleware())
    ->add(new AuthMiddleware());

// Create request from superglobals
$request = ServerRequest::fromGlobals();

// Run application
$response = $app->run($request);

// Send response
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getBody();
```

This example demonstrates:

- ✅ Using PSR-7 for request/response handling
- ✅ Middleware stack for cross-cutting concerns
- ✅ Proper header management
- ✅ Complete request lifecycle

---

## HTTP Status Codes Reference

Here's a comprehensive reference for HTTP status codes commonly used in PHP applications:

### Success Codes (2xx)

| Code | Constant | Meaning | Use Case |
|------|----------|---------|----------|
| 200 | `200` | OK | Standard successful response |
| 201 | `201` | Created | Resource created successfully |
| 202 | `202` | Accepted | Request accepted for async processing |
| 204 | `204` | No Content | Success with no response body |
| 206 | `206` | Partial Content | Range request response |

### Redirection Codes (3xx)

| Code | Constant | Meaning | Use Case |
|------|----------|---------|----------|
| 301 | `301` | Moved Permanently | Permanent URL redirect |
| 302 | `302` | Found | Temporary redirect |
| 304 | `304` | Not Modified | Cached content is valid |

### Client Error Codes (4xx)

| Code | Constant | Meaning | Use Case |
|------|----------|---------|----------|
| 400 | `400` | Bad Request | Invalid request syntax |
| 401 | `401` | Unauthorized | Authentication required |
| 403 | `403` | Forbidden | Authenticated but not authorized |
| 404 | `404` | Not Found | Resource doesn't exist |
| 405 | `405` | Method Not Allowed | HTTP method not supported |
| 409 | `409` | Conflict | Resource conflict (e.g., duplicate) |
| 413 | `413` | Payload Too Large | Request body too large |
| 415 | `415` | Unsupported Media Type | Content-Type not supported |
| 422 | `422` | Unprocessable Entity | Validation failed |
| 429 | `429` | Too Many Requests | Rate limit exceeded |

### Server Error Codes (5xx)

| Code | Constant | Meaning | Use Case |
|------|----------|---------|----------|
| 500 | `500` | Internal Server Error | Generic server error |
| 501 | `501` | Not Implemented | Feature not implemented |
| 503 | `503` | Service Unavailable | Temporarily unavailable |

### Using Status Codes in PHP

```php
<?php

declare(strict_types=1);

// Using header() function
http_response_code(404);
header('Location: /new-url', true, 301);

// Using PSR-7
use GuzzleHttp\Psr7\Response;

$response = new Response(201, ['Location' => '/api/users/123'], '');
$response = new Response(404, [], 'Not Found');
$response = new Response(204); // No Content

// Common status code constants
class HttpStatus
{
    public const OK = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const NOT_MODIFIED = 304;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const CONFLICT = 409;
    public const PAYLOAD_TOO_LARGE = 413;
    public const UNPROCESSABLE_ENTITY = 422;
    public const TOO_MANY_REQUESTS = 429;
    public const INTERNAL_SERVER_ERROR = 500;
    public const SERVICE_UNAVAILABLE = 503;
}
```

---

## Wrap-up

In this chapter, you've learned:

✓ **PHP superglobals** (`$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES`) and how they compare to Java's Servlet API

✓ **PSR-7 interfaces** for modern, object-oriented HTTP message handling

✓ **Content negotiation** for Accept headers, content types, and languages

✓ **Request body parsing** for JSON, XML, form-urlencoded, and multipart data

✓ **Header management** using both `header()` function and PSR-7 methods

✓ **Cookie handling** with security best practices (secure, httponly, samesite)

✓ **CORS preflight requests** and OPTIONS method handling

✓ **Caching headers** (ETag, Last-Modified, Cache-Control) for performance

✓ **Content encoding** (Gzip compression) to reduce bandwidth

✓ **HTTP method override** for PUT/DELETE via POST

✓ **File upload processing** with validation and security checks

✓ **Range requests** for partial content delivery

✓ **Request size limits** and validation

✓ **Middleware patterns** similar to Java servlet filters

✓ **HTTP status codes** comprehensive reference

✓ **Security best practices** for HTTP handling (validation, sanitization, CSRF protection)

### Key Takeaways

- **Superglobals are convenient but require validation** — Always use `filter_input()` or type checking
- **PSR-7 provides modern, testable HTTP handling** — Use for new projects or when integrating with frameworks
- **Middleware enables cross-cutting concerns** — Similar to Java filters, perfect for auth, logging, CORS
- **Security is critical** — Never trust user input; always validate and sanitize
- **Choose the right tool** — Use superglobals for simple scripts, PSR-7 for applications and frameworks

### Comparison Summary: PHP vs Java

| Feature | PHP | Java |
|---------|-----|------|
| **Request Access** | Superglobals (`$_GET`) or PSR-7 | `HttpServletRequest` |
| **Response Handling** | `header()` function or PSR-7 | `HttpServletResponse` |
| **Middleware** | PSR-15 middleware | Servlet Filters |
| **File Uploads** | `$_FILES` or PSR-7 `UploadedFileInterface` | `Part` interface |
| **Cookies** | `setcookie()` or PSR-7 | `Cookie` class |
| **Headers** | `header()` or PSR-7 methods | `setHeader()` / `addHeader()` |
| **Content Negotiation** | Manual implementation | Built-in via `@Produces` annotation |
| **CORS** | Manual middleware | Built-in via `@CrossOrigin` annotation |
| **Caching** | Manual ETag/Last-Modified | Built-in via `@CacheControl` annotation |
| **Compression** | Manual gzip middleware | Built-in via server configuration |
| **Range Requests** | Manual implementation | Built-in via `@Range` annotation |

### Next Steps

In the next chapter, you'll learn about **Sessions and Authentication**, building on the HTTP concepts covered here. You'll implement session management, authentication strategies, and authorization patterns.

---

## Further Reading

- [PSR-7 HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/) — Official PSR-7 specification
- [PSR-15 HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/) — Middleware standard
- [PHP Manual: Superglobals](https://www.php.net/manual/en/language.variables.superglobals.php) — Official documentation
- [PHP Manual: File Uploads](https://www.php.net/manual/en/features.file-upload.php) — File upload handling
- [OWASP: File Upload Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html) — Security best practices
- [Guzzle PSR-7](https://github.com/guzzle/psr7) — Popular PSR-7 implementation


---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/14-code-quality-tools">← Chapter 14</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/16-sessions-and-authentication">Chapter 16 →</a></div>
</div>
