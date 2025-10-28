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

![Working with JSON & APIs](/images/php-basics/chapter-23-json-apis-hero-full.webp)

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

### Goal

Master JSON encoding and decoding with proper error handling.

### Actions

1. **Create a file** named `json-basics.php`:

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

2. **Add error handling** for encoding/decoding failures:

```php
{# filename: json-error-handling.php #}
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

3. **Run each script** to inspect the output:

```bash
php json-basics.php
php json-error-handling.php
```

### Expected Result

```
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
JSON encoding failed: Malformed UTF-8 characters, possibly incorrectly encoded
JSON decoding failed: Syntax error
```

### Why It Works

- `json_encode()` converts PHP arrays/objects into JSON strings.
- `json_decode()` converts JSON strings back to PHP data structures; passing `true` returns associative arrays, while `false` returns objects.
- `json_last_error_msg()` reveals the reason for encoding/decoding failures, which is critical for debugging malformed or non-UTF-8 data.

### Troubleshooting

- **`json_encode` returns false** — Verify input strings are valid UTF-8 or enable `JSON_PARTIAL_OUTPUT_ON_ERROR` for best-effort encoding.
- **Decoded data is `null`** — Check `json_last_error()` for decoding issues; ensure the JSON string is valid.
- **Large numbers lose precision** — Use `JSON_BIGINT_AS_STRING` when decoding to keep big integers as strings.

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

### Goal

Use cURL to consume external REST APIs with proper error handling.

### Actions

1. **Create a file** named `api-client-curl.php`:

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

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON Decode Error: " . json_last_error_msg() . PHP_EOL;
        return null;
    }

    return $data;
}

$user = fetchGitHubUser('github');

if ($user) {
    echo "Name: " . $user['name'] . PHP_EOL;
    echo "Public Repos: " . $user['public_repos'] . PHP_EOL;
}
```

2. **Run the script**:

```bash
php api-client-curl.php
```

### Expected Result

```
Name: GitHub
Public Repos: 296
```

### Why It Works

- `curl_setopt` flags configure cURL to return data, follow redirects, and set timeouts.
- Handling HTTP status codes ensures success responses before decoding JSON.
- Decoding the JSON response yields an associative array for easy access to fields like `name` and `public_repos`.

### Troubleshooting

- **`cURL Error: Could not resolve host`** — Check your internet connection or DNS settings.
- **HTTP Error responses (401, 403)** — API may require authentication or a valid User-Agent.
- **JSON decode errors** — Log the raw response to inspect API output before decoding.

## Step 3: Building a REST API Endpoint (~7 min)

### Goal

Create a simple REST endpoint that returns JSON using plain PHP.

### Actions

1. **Create an API endpoint** `public/api/posts.php`:

```php
# filename: public/api/posts.php
<?php

declare(strict_types=1);

header('Content-Type: application/json');

$posts = [
    ['id' => 1, 'title' => 'Hello JSON', 'content' => 'JSON is great for APIs'],
    ['id' => 2, 'title' => 'Consuming APIs', 'content' => 'cURL makes it easy'],
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($posts, JSON_PRETTY_PRINT);
    exit;
}

echo json_encode(['error' => 'Method not allowed'], JSON_PRETTY_PRINT);
http_response_code(405);
```

2. **Serve the endpoint** and test it:

```bash
php -S localhost:8000 -t public
curl http://localhost:8000/api/posts.php
```

### Expected Result

```
[
    {
        "id": 1,
        "title": "Hello JSON",
        "content": "JSON is great for APIs"
    },
    {
        "id": 2,
        "title": "Consuming APIs",
        "content": "cURL makes it easy"
    }
]
```

### Why It Works

- Setting the `Content-Type` header ensures clients treat the response as JSON.
- `json_encode` converts the posts array into a properly formatted string.
- Returning a 405 status code for unsupported methods follows REST best practices.

### Troubleshooting

- **Blank output** — Ensure `json_encode` is called and no syntax errors occurred before the response.
- **`Cannot redeclare header()` warnings** — Make sure no output is sent before the `header()` call.
- **`curl` shows HTML instead of JSON** — Confirm you’re hitting the correct endpoint and not the default router.

## Step 4: Adding Authentication (~6 min)

### Goal

Demonstrate a simple token-based authentication check for API requests.

### Actions

1. **Update your endpoint** to require an API token:

```php
# filename: public/api/posts.php
<?php

declare(strict_types=1);

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if ($token !== 'secret123') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$posts = [
    ['id' => 1, 'title' => 'Hello JSON', 'content' => 'JSON is great for APIs'],
    ['id' => 2, 'title' => 'Consuming APIs', 'content' => 'cURL makes it easy'],
];

echo json_encode($posts, JSON_PRETTY_PRINT);
```

2. **Call the endpoint with the token**:

```bash
curl "http://localhost:8000/api/posts.php?token=secret123"
```

### Expected Result

Requests without the token return a `401 Unauthorized` response with an error message. Requests with the correct token return the JSON payload.

### Why It Works

- A simple token check enforces authentication before serving data.
- Returning 401 status codes communicates auth errors clearly to clients.

### Troubleshooting

- **Always unauthorized** — Confirm the query string includes `token=secret123` and matches exactly, case-sensitive.
- **Token visible in URL** — For sensitive data, use headers (`Authorization: Bearer`) instead of query parameters.

## Step 5: Handling JSON Errors (~4 min)

### Goal

Gracefully handle encoding/decoding errors and provide actionable feedback.

### Actions

1. **Create a utility function** for safe decoding:

```php
# filename: json-safe-decode.php
<?php

declare(strict_types=1);

function decodeJson(string $json): array
{
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('JSON decode failed: ' . json_last_error_msg());
    }

    return $data;
}

try {
    $payload = decodeJson('{"foo": "bar"}');
    print_r($payload);

    $broken = decodeJson('{"foo": }');
} catch (RuntimeException $e) {
    echo $e->getMessage() . PHP_EOL;
}
```

2. **Run the helper**:

```bash
php json-safe-decode.php
```

### Expected Result

```
Array
(
    [foo] => bar
)
JSON decode failed: Syntax error
```

### Why It Works

- Wrapping `json_decode` in a helper centralizes error handling and keeps calling code clean.
- Throwing exceptions clarifies which part of the pipeline failed, aiding debugging.

### Troubleshooting

- **Helper throws exceptions on valid JSON** — Ensure the input is a UTF-8 string and `json_last_error()` is checked immediately after decoding.
- **Need partial results** — Use `JSON_PARTIAL_OUTPUT_ON_ERROR` and handle truncated data carefully.

## Wrap-up

Great job! You've now combined JSON handling, API consumption, and simple REST design into a clean workflow. You can:

- ✅ Encode PHP arrays and objects into JSON with proper error handling
- ✅ Decode JSON safely and detect malformed payloads
- ✅ Fetch remote API data using cURL with timeouts and error checks
- ✅ Build basic REST endpoints that serve JSON responses
- ✅ Add authentication gates to protect API endpoints
- ✅ Centralize JSON validation logic for reuse across projects

These skills unlock third-party integrations, microservices, and modern front-end frameworks that expect JSON APIs.

## Further Reading

- [PHP Manual: JSON Functions](https://www.php.net/manual/en/ref.json.php)
- [cURL Manual](https://curl.se/docs/manpage.html) — Detailed cURL command reference
- [HTTP Status Codes](https://developer.mozilla.org/docs/Web/HTTP/Status) — Official MDN guide
- [REST API Design Guidelines](https://restfulapi.net/) — Best practices for designing RESTful services

## Knowledge Check

Test your understanding of JSON and APIs:

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
