---
title: "23: Working with JSON & APIs"
description: "Master JSON data handling and API integration to connect your PHP applications with external services and build your own RESTful endpoints."
series: "php-basics"
chapter: 23
order: 23
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/11-error-and-exception-handling"
  - "/series/php-basics/chapters/14-interacting-with-databases-using-pdo"
estimatedTime: "25 minutes"
tags: ["json", "api", "rest", "curl", "http"]
---

# Chapter 23: Working with JSON & APIs

## Overview

Modern web applications rarely work in isolation. Whether you're integrating with payment gateways, weather services, social media platforms, or building your own API, you need to exchange data with other systems. JSON (JavaScript Object Notation) has become the universal language for this data exchange.

In this chapter, you'll learn how to encode and decode JSON, consume external APIs using cURL, handle errors gracefully, and build your own simple REST API endpoints. By the end, you'll be able to integrate any web service into your PHP applications.

## Prerequisites

- PHP 8.4 installed and accessible from your command line ([Chapter 00](/series/php-basics/chapters/00-setting-up-your-development-environment))
- Understanding of arrays and associative arrays ([Chapter 06](/series/php-basics/chapters/06-deep-dive-into-arrays))
- Familiarity with error handling ([Chapter 11](/series/php-basics/chapters/11-error-and-exception-handling))
- Basic understanding of HTTP (GET, POST requests)
- A text editor
- Estimated time: **25 minutes**

## What You'll Build

By the end of this chapter, you will have created:

- JSON encoding and decoding scripts with error handling
- A cURL-based API client that fetches weather data
- A GitHub API integration that retrieves user information
- Your own simple REST API endpoint
- Error handling for API failures and JSON errors
- Working examples of all major JSON and API patterns

All examples will be working PHP scripts you can run immediately.

## Quick Start

Want to see JSON and API calls in action right away? Create this file and run it:

```php
<?php
// filename: quick-api.php

declare(strict_types=1);

// Encode PHP data to JSON
$user = [
    'name' => 'Alice Johnson',
    'email' => 'alice@example.com',
    'roles' => ['admin', 'user']
];

$json = json_encode($user, JSON_PRETTY_PRINT);
echo "JSON Output:\n" . $json . PHP_EOL;

// Decode JSON back to PHP
$decoded = json_decode($json, true);
echo "\nDecoded Name: " . $decoded['name'] . PHP_EOL;

// Make an API request (using a free test API)
$ch = curl_init('https://api.github.com/users/github');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Tutorial');
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "\nGitHub User: " . $data['name'] . PHP_EOL;
echo "Public Repos: " . $data['public_repos'] . PHP_EOL;
```

```bash
# Run it
php quick-api.php
```

**Expected output:**

```text
JSON Output:
{
    "name": "Alice Johnson",
    "email": "alice@example.com",
    "roles": [
        "admin",
        "user"
    ]
}

Decoded Name: Alice Johnson

GitHub User: GitHub
Public Repos: 296
```

This compact example shows JSON encoding, decoding, and making an actual API call. Now let's build this understanding step by step.

## Objectives

- Understand JSON format and its relationship to PHP arrays
- Encode PHP data to JSON strings
- Decode JSON strings to PHP data structures
- Handle JSON encoding and decoding errors
- Make HTTP requests using cURL
- Consume REST APIs with proper error handling
- Build a simple REST API endpoint
- Work with common API authentication patterns

## Step 1: JSON Basics (~5 min)

**Goal**: Master JSON encoding and decoding with proper error handling.

JSON is a text format for storing and transporting data. It looks similar to PHP arrays but is a string that can be sent over HTTP. PHP provides two essential functions: [`json_encode()`](https://www.php.net/manual/en/function.json-encode.php) and [`json_decode()`](https://www.php.net/manual/en/function.json-decode.php).

### Actions

1.  **Create a File**:
    Create a new file named `json-basics.php` in your working directory.

2.  **Encoding and Decoding JSON**:

```php
# filename: json-basics.php
<?php

declare(strict_types=1);

// Encode PHP array to JSON
$products = [
    [
        'id' => 1,
        'name' => 'Laptop',
        'price' => 999.99,
        'in_stock' => true
    ],
    [
        'id' => 2,
        'name' => 'Mouse',
        'price' => 29.99,
        'in_stock' => false
    ]
];

// Basic encoding
$json = json_encode($products);
echo "Compact JSON:\n" . $json . "\n\n";

// Pretty-printed JSON (easier to read)
$prettyJson = json_encode($products, JSON_PRETTY_PRINT);
echo "Pretty JSON:\n" . $prettyJson . "\n\n";

// Decode JSON string back to PHP array
$decoded = json_decode($prettyJson, true); // true = return associative array
echo "First product name: " . $decoded[0]['name'] . PHP_EOL;

// Without true, you get an object
$decodedAsObject = json_decode($prettyJson);
echo "First product name (object): " . $decodedAsObject[0]->name . PHP_EOL;
```

3.  **Run the Script**:

```bash
php json-basics.php
```

### Expected Output

```text
Compact JSON:
[{"id":1,"name":"Laptop","price":999.99,"in_stock":true},{"id":2,"name":"Mouse","price":29.99,"in_stock":false}]

Pretty JSON:
[
    {
        "id": 1,
        "name": "Laptop",
        "price": 999.99,
        "in_stock": true
    },
    {
        "id": 2,
        "name": "Mouse",
        "price": 29.99,
        "in_stock": false
    }
]

First product name: Laptop
First product name (object): Laptop
```

### Why It Works

- [`json_encode()`](https://www.php.net/manual/en/function.json-encode.php) converts PHP arrays and objects into a JSON string
- [`json_decode($json, true)`](https://www.php.net/manual/en/function.json-decode.php) converts JSON back to PHP associative arrays
- `json_decode($json)` (without `true`) returns PHP objects instead
- `JSON_PRETTY_PRINT` adds formatting for human readability

### JSON Error Handling

Always check for errors when working with JSON:

```php
<?php

declare(strict_types=1);

// Invalid UTF-8 sequences will cause encoding to fail
$invalid = [
    'name' => "Invalid \xB1\x31 data"
];

$json = json_encode($invalid);

if ($json === false) {
    echo "JSON encoding failed: " . json_last_error_msg() . PHP_EOL;
} else {
    echo "Success: " . $json . PHP_EOL;
}

// Decoding invalid JSON
$badJson = '{"name": "Bob", "age": }'; // Missing value
$decoded = json_decode($badJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON decoding failed: " . json_last_error_msg() . PHP_EOL;
}
```

::: tip JSON Options
Common [`json_encode()`](https://www.php.net/manual/en/function.json-encode.php) options:

- `JSON_PRETTY_PRINT` - Format with indentation
- `JSON_UNESCAPED_UNICODE` - Don't escape unicode characters
- `JSON_UNESCAPED_SLASHES` - Don't escape forward slashes
- `JSON_THROW_ON_ERROR` - Throw exceptions instead of returning false (PHP 7.3+)
  :::

## Step 2: Making API Requests with cURL (~8 min)

**Goal**: Use cURL to consume external REST APIs with proper error handling.

cURL is a powerful library for making HTTP requests. It's included with most PHP installations and is the standard way to interact with APIs.

### Actions

1.  **Create a File**:
    Create `api-client-curl.php`.

2.  **Basic GET Request**:

```php
# filename: api-client-curl.php
<?php

declare(strict_types=1);

/**
 * Fetch user data from GitHub API
 */
function fetchGitHubUser(string $username): ?array
{
    $url = "https://api.github.com/users/" . urlencode($username);

    // Initialize cURL session
    $ch = curl_init($url);

    // Set options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as string
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Tutorial'); // GitHub requires user agent
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout

    // Execute request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        echo "cURL Error: " . curl_error($ch) . PHP_EOL;
        curl_close($ch);
        return null;
    }

    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "HTTP Error: " . $httpCode . PHP_EOL;
        return null;
    }

    // Decode JSON response
    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Error: " . json_last_error_msg() . PHP_EOL;
        return null;
    }

    return $data;
}

// Test the function
$user = fetchGitHubUser('github');

if ($user !== null) {
    echo "Name: " . $user['name'] . PHP_EOL;
    echo "Bio: " . $user['bio'] . PHP_EOL;
    echo "Public Repos: " . $user['public_repos'] . PHP_EOL;
    echo "Followers: " . $user['followers'] . PHP_EOL;
    echo "Created: " . $user['created_at'] . PHP_EOL;
}
```

3.  **Run the Script**:

```bash
php api-client-curl.php
```

### Expected Output

```text
Name: GitHub
Bio: How people build software.
Public Repos: 296
Followers: 125000
Created: 2008-05-10T21:37:00Z
```

### Why It Works

- [`curl_init()`](https://www.php.net/manual/en/function.curl-init.php) creates a cURL session
- [`curl_setopt()`](https://www.php.net/manual/en/function.curl-setopt.php) configures the request
- [`curl_exec()`](https://www.php.net/manual/en/function.curl-exec.php) executes the request and returns the response
- [`curl_getinfo()`](https://www.php.net/manual/en/function.curl-getinfo.php) retrieves information about the request (like HTTP status)
- Always close the cURL handle with [`curl_close()`](https://www.php.net/manual/en/function.curl-close.php)

### POST Requests with JSON

To send data to an API, use POST requests:

```php
<?php

declare(strict_types=1);

/**
 * Example: Send data to an API endpoint
 */
function createUser(string $name, string $email): ?array
{
    $url = 'https://jsonplaceholder.typicode.com/users'; // Test API

    $data = [
        'name' => $name,
        'email' => $email,
        'username' => strtolower($name)
    ];

    $json = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); // Make it a POST request
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json); // Send JSON data
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) { // 201 = Created
        return json_decode($response, true);
    }

    echo "Error: HTTP " . $httpCode . PHP_EOL;
    return null;
}

$newUser = createUser('Alice Johnson', 'alice@example.com');

if ($newUser !== null) {
    echo "User created with ID: " . $newUser['id'] . PHP_EOL;
    echo "Name: " . $newUser['name'] . PHP_EOL;
}
```

::: warning API Rate Limits
Most APIs have rate limits (e.g., GitHub allows 60 requests/hour without authentication). Always:

- Check the API documentation
- Handle 429 (Too Many Requests) status codes
- Consider implementing caching
- Add authentication when available for higher limits
  :::

## Step 3: Building a Simple REST API (~7 min)

**Goal**: Create your own REST API endpoint that returns JSON data.

### Actions

1.  **Create a File**:
    Create `simple-api.php`.

2.  **Build a REST API Endpoint**:

```php
# filename: simple-api.php
<?php

declare(strict_types=1);

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Simple in-memory database (in real apps, use a database)
$products = [
    ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'stock' => 15],
    ['id' => 2, 'name' => 'Mouse', 'price' => 29.99, 'stock' => 50],
    ['id' => 3, 'name' => 'Keyboard', 'price' => 79.99, 'stock' => 30],
    ['id' => 4, 'name' => 'Monitor', 'price' => 299.99, 'stock' => 8],
];

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

// Parse URL to get product ID if present
$urlParts = explode('/', trim($path, '/'));
$productId = isset($urlParts[1]) && is_numeric($urlParts[1])
    ? (int)$urlParts[1]
    : null;

/**
 * Send JSON response with status code
 */
function sendResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Find product by ID
 */
function findProduct(int $id, array $products): ?array
{
    foreach ($products as $product) {
        if ($product['id'] === $id) {
            return $product;
        }
    }
    return null;
}

// Route: GET /products - List all products
if ($method === 'GET' && $productId === null) {
    sendResponse(200, [
        'success' => true,
        'data' => $products,
        'count' => count($products)
    ]);
}

// Route: GET /products/{id} - Get single product
if ($method === 'GET' && $productId !== null) {
    $product = findProduct($productId, $products);

    if ($product === null) {
        sendResponse(404, [
            'success' => false,
            'error' => 'Product not found'
        ]);
    }

    sendResponse(200, [
        'success' => true,
        'data' => $product
    ]);
}

// Route: POST /products - Create product (example, not persisted)
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(400, [
            'success' => false,
            'error' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
    }

    // Validate required fields
    if (empty($data['name']) || empty($data['price'])) {
        sendResponse(400, [
            'success' => false,
            'error' => 'Name and price are required'
        ]);
    }

    // Create new product
    $newProduct = [
        'id' => count($products) + 1,
        'name' => $data['name'],
        'price' => (float)$data['price'],
        'stock' => $data['stock'] ?? 0
    ];

    sendResponse(201, [
        'success' => true,
        'message' => 'Product created',
        'data' => $newProduct
    ]);
}

// Method not allowed
sendResponse(405, [
    'success' => false,
    'error' => 'Method not allowed'
]);
```

3.  **Run the API Server**:

```bash
# Start PHP's built-in web server
php -S localhost:8000 simple-api.php
```

4.  **Test the API** (open a new terminal):

```bash
# Get all products
curl http://localhost:8000/products

# Get single product
curl http://localhost:8000/products/1

# Create a product
curl -X POST http://localhost:8000/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Webcam","price":89.99,"stock":20}'
```

### Expected Output

For `GET /products`:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Laptop",
            "price": 999.99,
            "stock": 15
        },
        ...
    ],
    "count": 4
}
```

For `POST /products`:

```json
{
  "success": true,
  "message": "Product created",
  "data": {
    "id": 5,
    "name": "Webcam",
    "price": 89.99,
    "stock": 20
  }
}
```

### Why It Works

- `header('Content-Type: application/json')` tells clients to expect JSON
- [`http_response_code()`](https://www.php.net/manual/en/function.http-response-code.php) sets proper HTTP status codes
- `$_SERVER['REQUEST_METHOD']` determines the HTTP method (GET, POST, etc.)
- [`file_get_contents('php://input')`](https://www.php.net/manual/en/wrappers.php.php) reads the request body
- Standard REST conventions: 200 (OK), 201 (Created), 404 (Not Found), 400 (Bad Request)

::: tip REST Best Practices

- Use appropriate HTTP methods: GET (read), POST (create), PUT/PATCH (update), DELETE (delete)
- Return meaningful HTTP status codes
- Include error messages in a consistent format
- Version your API (e.g., `/api/v1/products`)
- Add authentication for sensitive endpoints
  :::

## Step 4: Real-World API Integration Example (~5 min)

**Goal**: Build a reusable API client class with error handling.

### Actions

1.  **Create a File**:
    Create `api-client-class.php`.

2.  **Build a Reusable API Client**:

```php
# filename: api-client-class.php
<?php

declare(strict_types=1);

/**
 * Simple HTTP client for API requests
 */
class ApiClient
{
    public function __construct(
        private string $baseUrl,
        private int $timeout = 10,
        private array $defaultHeaders = []
    ) {}

    /**
     * Make a GET request
     */
    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->request('GET', $url);
    }

    /**
     * Make a POST request
     */
    public function post(string $endpoint, array $data = []): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('POST', $url, $data);
    }

    /**
     * Execute HTTP request
     */
    private function request(string $method, string $url, ?array $data = null): array
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Set headers
        $headers = $this->defaultHeaders;

        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Type: application/json';
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("cURL error: " . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        return [
            'status' => $httpCode,
            'data' => $decoded
        ];
    }

    /**
     * Build full URL with query parameters
     */
    private function buildUrl(string $endpoint, array $params = []): string
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}

// Example usage: GitHub API client
try {
    $github = new ApiClient(
        baseUrl: 'https://api.github.com',
        defaultHeaders: ['User-Agent: PHP-Tutorial']
    );

    // Get user information
    $result = $github->get('users/github');

    if ($result['status'] === 200) {
        $user = $result['data'];
        echo "User: " . $user['name'] . PHP_EOL;
        echo "Repos: " . $user['public_repos'] . PHP_EOL;
    }

    // Search repositories
    $result = $github->get('search/repositories', [
        'q' => 'language:php stars:>1000',
        'sort' => 'stars',
        'per_page' => 5
    ]);

    if ($result['status'] === 200) {
        echo "\nTop PHP Repositories:\n";
        foreach ($result['data']['items'] as $repo) {
            echo "- {$repo['name']} ({$repo['stargazers_count']} stars)\n";
        }
    }

} catch (RuntimeException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
```

3.  **Run the Script**:

```bash
php api-client-class.php
```

### Expected Output

```text
User: GitHub
Repos: 296

Top PHP Repositories:
- laravel (76000 stars)
- symfony (29000 stars)
- composer (28000 stars)
- ...
```

### Why It Works

- The class encapsulates all cURL logic in a reusable way
- Error handling is consistent across all requests
- The `buildUrl()` method handles query parameters automatically
- Constructor injection allows configuring timeouts and default headers
- The API returns both status code and data for flexible error handling

## Code Files

Explore comprehensive examples of JSON and API handling:

- [`code/23-json-apis/json-basics.php`](../code/23-json-apis/json-basics.php) - JSON encoding/decoding with error handling
- [`code/23-json-apis/api-client-curl.php`](../code/23-json-apis/api-client-curl.php) - cURL API client
- [`code/23-json-apis/simple-rest-api.php`](../code/23-json-apis/simple-rest-api.php) - Complete REST API example

## Troubleshooting

### cURL Extension Not Found

If you see "Call to undefined function curl_init()":

```bash
# On macOS
brew install curl

# On Ubuntu/Debian
sudo apt-get install php-curl

# On Windows
# Enable extension=curl in php.ini
```

### JSON Encoding Fails

If [`json_encode()`](https://www.php.net/manual/en/function.json-encode.php) returns `false`:

- Check for invalid UTF-8: [`mb_detect_encoding()`](https://www.php.net/manual/en/function.mb-detect-encoding.php)
- Use `JSON_INVALID_UTF8_SUBSTITUTE` or `JSON_INVALID_UTF8_IGNORE` flags
- Check for circular references in objects

### API Returns 403 Forbidden

- Add a `User-Agent` header (many APIs require it)
- Check if you need authentication (API key, OAuth token)
- Verify you're not hitting rate limits

## Exercises

1.  **Weather API Integration**: Create a function that fetches current weather using OpenWeatherMap API (free tier available)

2.  **API Response Cache**: Modify the `ApiClient` class to cache responses for 5 minutes to reduce API calls

3.  **Full CRUD API**: Extend `simple-api.php` to handle PUT (update) and DELETE operations

4.  **Error Response Handler**: Create a class that standardizes error responses with error codes and messages

## Wrap-up

You've learned how to work with JSON and integrate external APIs into your PHP applications. You can now:

- ✓ Encode and decode JSON with proper error handling
- ✓ Make HTTP requests using cURL
- ✓ Consume REST APIs with authentication
- ✓ Build your own REST API endpoints
- ✓ Handle errors gracefully at every step
- ✓ Create reusable API client classes

### What's Next?

- [Chapter 20: A Gentle Introduction to Laravel](/series/php-basics/chapters/20-a-gentle-introduction-to-laravel) - Laravel has powerful HTTP client and API resource features
- [Chapter 14: Interacting with Databases using PDO](/series/php-basics/chapters/14-interacting-with-databases-using-pdo) - Persist API data in a database
- Explore [Guzzle](https://docs.guzzlephp.org/), a modern HTTP client library for PHP
- Learn about API authentication (OAuth 2.0, JWT tokens)
- Study [REST API design best practices](https://restfulapi.net/)

## Knowledge Check

Test your understanding of JSON and API integration:

<Quiz 
  title="Chapter 23 Quiz: JSON & APIs"
  :questions="[
    {
      question: 'What does JSON stand for?',
      options: [
        { text: 'JavaScript Object Notation', correct: true, explanation: 'JSON is a text format based on JavaScript object syntax.' },
        { text: 'Java Serialized Object Network', correct: false, explanation: 'JSON is not related to Java or serialization specifically.' },
        { text: 'JavaScript Online Network', correct: false, explanation: 'This is not what JSON stands for.' },
        { text: 'Just Simple Object Notation', correct: false, explanation: 'While simple, this isn\'t the correct meaning.' }
      ]
    },
    {
      question: 'What does json_decode($json, true) return?',
      options: [
        { text: 'An associative array', correct: true, explanation: 'The second parameter `true` converts JSON to arrays instead of objects.' },
        { text: 'A stdClass object', correct: false, explanation: 'Without the `true` parameter, it returns an object.' },
        { text: 'A boolean value', correct: false, explanation: 'The second parameter determines the return type, not a boolean.' },
        { text: 'A JSON string', correct: false, explanation: 'json_decode() converts JSON strings to PHP data, not the reverse.' }
      ]
    },
    {
      question: 'Which function should you use to make HTTP requests in PHP?',
      options: [
        { text: 'curl_init() and curl_exec()', correct: true, explanation: 'cURL is the standard PHP extension for HTTP requests.' },
        { text: 'file_get_contents() only', correct: false, explanation: 'While possible for simple GET requests, cURL is more flexible.' },
        { text: 'http_request()', correct: false, explanation: 'This is not a built-in PHP function.' },
        { text: 'fetch()', correct: false, explanation: 'fetch() is a JavaScript API, not PHP.' }
      ]
    },
    {
      question: 'What HTTP status code indicates a successful resource creation?',
      options: [
        { text: '201 Created', correct: true, explanation: '201 means the request succeeded and created a new resource.' },
        { text: '200 OK', correct: false, explanation: '200 means success but is more generic; 201 specifically means created.' },
        { text: '204 No Content', correct: false, explanation: '204 means success but no content to return (used for DELETE).' },
        { text: '301 Moved Permanently', correct: false, explanation: '301 is a redirect, not a success status.' }
      ]
    },
    {
      question: 'Why should you always check json_last_error() after json_decode()?',
      options: [
        { text: 'To detect malformed JSON', correct: true, explanation: 'json_decode() returns null on error, but null is also valid JSON, so check errors explicitly.' },
        { text: 'To improve performance', correct: false, explanation: 'Error checking doesn\'t improve performance.' },
        { text: 'It\'s required for the function to work', correct: false, explanation: 'json_decode() works without it, but you should check for errors.' },
        { text: 'To enable pretty printing', correct: false, explanation: 'Pretty printing is controlled by JSON_PRETTY_PRINT in json_encode().' }
      ]
    }
  ]"
/>

## Further Reading

- [PHP JSON Functions Documentation](https://www.php.net/manual/en/ref.json.php)
- [PHP cURL Documentation](https://www.php.net/manual/en/book.curl.php)
- [REST API Tutorial](https://restfulapi.net/)
- [HTTP Status Codes Reference](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
- [JSONPlaceholder](https://jsonplaceholder.typicode.com/) - Free fake API for testing
