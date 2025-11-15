---
title: "04: HTTP Clients & API Integration"
description: "Master different HTTP clients and build robust API integration layers for OpenAI in PHP"
series: "openai-php"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/03-api-authentication-configuration"
  - "Understanding of HTTP/REST APIs"
  - "Experience with Composer packages"
---

![HTTP Clients & API Integration](/images/openai-php/chapter-04-http-clients-hero-full.webp)

[Home](/series/openai-php) > [Chapter 03](/series/openai-php/chapters/03-api-authentication-configuration) > HTTP Clients & API Integration

# Chapter 04: HTTP Clients & API Integration

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">50-60 minutes</span>

## Overview

The HTTP client you choose and how you integrate with the OpenAI API can significantly impact your application's reliability, performance, and maintainability. While the OpenAI PHP SDK provides an excellent starting point, understanding the underlying HTTP layer gives you fine-grained control when you need it.

In this chapter, you'll explore three major HTTP client options in PHP: Guzzle, cURL, and Symfony HTTP Client. You'll learn when to use the OpenAI SDK versus building custom wrappers, and how to implement middleware, connection pooling, and advanced request handling patterns.

Whether you're building a simple chatbot or a complex multi-tenant AI platform, the techniques you learn here will help you create robust, performant API integrations.

## What You'll Learn

- 🌐 **HTTP Client Comparison**: Evaluate Guzzle, cURL, and Symfony HTTP Client
- 📦 **OpenAI SDK vs Custom**: Choose between SDK and raw API calls
- 🔧 **Custom API Wrappers**: Build flexible, reusable integration layers
- 🔄 **Middleware Patterns**: Implement logging, retry, and monitoring
- ⚡ **Performance Optimization**: Connection pooling and request batching
- 🎯 **Request/Response Handling**: Process API data effectively
- 🛠️ **Best Practices**: Production-ready integration patterns

## Prerequisites

- ✅ Completed Chapters 01-03
- ✅ Understanding of HTTP requests/responses
- ✅ Familiarity with Composer and PSR standards
- ✅ Basic knowledge of object-oriented PHP

---

## HTTP Client Options

### 1. Guzzle - The Standard

**Strengths:**
- ✅ Most popular PHP HTTP client
- ✅ Rich middleware ecosystem
- ✅ PSR-7/PSR-18 compliant
- ✅ Excellent documentation
- ✅ Async support

```bash
composer require guzzlehttp/guzzle
```

```php
<?php

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://api.openai.com/v1/',
    'timeout' => 30.0,
    'headers' => [
        'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
        'Content-Type' => 'application/json',
    ],
]);

$response = $client->post('chat/completions', [
    'json' => [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ],
    ],
]);

$data = json_decode($response->getBody(), true);
```

### 2. cURL - The Native Option

**Strengths:**
- ✅ Built into PHP
- ✅ No dependencies
- ✅ Maximum control
- ✅ High performance

```php
<?php

function openaiCurlRequest(string $endpoint, array $payload): array
{
    $ch = curl_init("https://api.openai.com/v1/$endpoint");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_ENV['OPENAI_API_KEY'],
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        throw new \RuntimeException('cURL error: ' . curl_error($ch));
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        throw new \RuntimeException("API error: HTTP $httpCode");
    }

    return json_decode($response, true);
}

// Usage
$result = openaiCurlRequest('chat/completions', [
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);
```

### 3. Symfony HTTP Client

**Strengths:**
- ✅ Modern async support
- ✅ HTTP/2 support
- ✅ Excellent performance
- ✅ Clean API

```bash
composer require symfony/http-client
```

```php
<?php

use Symfony\Component\HttpClient\HttpClient;

$client = HttpClient::create([
    'base_uri' => 'https://api.openai.com/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
        'Content-Type' => 'application/json',
    ],
]);

$response = $client->request('POST', 'chat/completions', [
    'json' => [
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ],
]);

$data = $response->toArray();
```

---

## OpenAI SDK vs Custom Implementation

### Using the OpenAI PHP SDK

```bash
composer require openai-php/client
```

**Pros:**
- ✅ Type-safe API
- ✅ Handles authentication
- ✅ Response objects
- ✅ Maintained by community

```php
<?php

use OpenAI;

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello'],
    ],
]);

// Type-safe access
$message = $response->choices[0]->message->content;
$tokens = $response->usage->totalTokens;
```

### When to Build Custom

**Build custom when you need:**
- 🎯 Full control over requests
- 🎯 Custom retry logic
- 🎯 Advanced middleware
- 🎯 Non-standard endpoints
- 🎯 Extreme performance tuning

---

## Building a Custom API Wrapper

```php
<?php

/**
 * Custom OpenAI API Wrapper
 */

namespace App\OpenAI;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OpenAIClient
{
    private Client $httpClient;
    private array $defaultOptions;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $organization = '',
        array $options = []
    ) {
        $this->defaultOptions = array_merge([
            'timeout' => 30,
            'connect_timeout' => 10,
            'max_retries' => 3,
        ], $options);

        $this->httpClient = $this->createHttpClient();
    }

    private function createHttpClient(): Client
    {
        $stack = HandlerStack::create();

        // Add retry middleware
        $stack->push(Middleware::retry(
            function ($retries, $request, $response, $exception) {
                // Retry on 5xx errors or network issues
                if ($retries >= $this->defaultOptions['max_retries']) {
                    return false;
                }

                if ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
                    return true;
                }

                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff: 1s, 2s, 4s
                return 1000 * pow(2, $retries);
            }
        ));

        // Add logging middleware
        $stack->push($this->loggingMiddleware());

        return new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'handler' => $stack,
            'timeout' => $this->defaultOptions['timeout'],
            'connect_timeout' => $this->defaultOptions['connect_timeout'],
            'headers' => $this->getDefaultHeaders(),
        ]);
    }

    private function getDefaultHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($this->organization) {
            $headers['OpenAI-Organization'] = $this->organization;
        }

        return $headers;
    }

    private function loggingMiddleware(): callable
    {
        return Middleware::mapRequest(function (RequestInterface $request) {
            error_log(sprintf(
                '[OpenAI] %s %s',
                $request->getMethod(),
                $request->getUri()->getPath()
            ));
            return $request;
        });
    }

    public function chat(array $messages, array $options = []): array
    {
        $payload = array_merge([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ], $options);

        return $this->request('POST', 'chat/completions', $payload);
    }

    public function embedding(string $input, string $model = 'text-embedding-ada-002'): array
    {
        return $this->request('POST', 'embeddings', [
            'model' => $model,
            'input' => $input,
        ]);
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, [
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw $this->handleClientException($e);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new \RuntimeException(
                "OpenAI server error: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    private function handleClientException(\GuzzleHttp\Exception\ClientException $e): \Exception
    {
        $response = $e->getResponse();
        $body = json_decode($response->getBody()->getContents(), true);

        $message = $body['error']['message'] ?? $e->getMessage();
        $code = $response->getStatusCode();

        return match ($code) {
            401 => new \RuntimeException("Authentication failed: $message", 401),
            429 => new \RuntimeException("Rate limit exceeded: $message", 429),
            default => new \RuntimeException("API error: $message", $code),
        };
    }
}

// Usage
$client = new OpenAIClient($_ENV['OPENAI_API_KEY']);

$response = $client->chat([
    ['role' => 'user', 'content' => 'Hello!']
]);
```

---

## Advanced Middleware Patterns

### Request/Response Logging Middleware

```php
<?php

class LoggingMiddleware
{
    public function __invoke(callable $handler): callable
    {
        return function (
            RequestInterface $request,
            array $options
        ) use ($handler) {
            $startTime = microtime(true);

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request, $startTime) {
                    $duration = round((microtime(true) - $startTime) * 1000);

                    error_log(sprintf(
                        '[OpenAI] %s %s - %d - %dms',
                        $request->getMethod(),
                        $request->getUri()->getPath(),
                        $response->getStatusCode(),
                        $duration
                    ));

                    return $response;
                }
            );
        };
    }
}
```

### Token Usage Tracking Middleware

```php
<?php

class TokenTrackingMiddleware
{
    private array $usage = [];

    public function __invoke(callable $handler): callable
    {
        return function (
            RequestInterface $request,
            array $options
        ) use ($handler) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request) {
                    $body = json_decode($response->getBody()->getContents(), true);

                    if (isset($body['usage'])) {
                        $this->usage[] = [
                            'timestamp' => time(),
                            'endpoint' => $request->getUri()->getPath(),
                            'tokens' => $body['usage']['total_tokens'],
                        ];
                    }

                    // Return new response with rewound body
                    return $response->withBody(
                        \GuzzleHttp\Psr7\Utils::streamFor(json_encode($body))
                    );
                }
            );
        };
    }

    public function getUsage(): array
    {
        return $this->usage;
    }

    public function getTotalTokens(): int
    {
        return array_sum(array_column($this->usage, 'tokens'));
    }
}
```

---

## Connection Pooling

```php
<?php

/**
 * Connection pool for reusing HTTP clients
 */

class ConnectionPool
{
    private static array $clients = [];
    private static int $maxConnections = 10;

    public static function getClient(string $key = 'default'): Client
    {
        if (!isset(self::$clients[$key])) {
            if (count(self::$clients) >= self::$maxConnections) {
                // Remove oldest client
                array_shift(self::$clients);
            }

            self::$clients[$key] = self::createClient();
        }

        return self::$clients[$key];
    }

    private static function createClient(): Client
    {
        return new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                'Content-Type' => 'application/json',
            ],
            // Enable connection persistence
            'curl' => [
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 120,
            ],
        ]);
    }

    public static function resetPool(): void
    {
        self::$clients = [];
    }
}
```

---

## Async Request Handling

```php
<?php

/**
 * Async batch processing with Guzzle
 */

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class AsyncOpenAI
{
    private Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function batchChat(array $requests): array
    {
        $promises = [];

        foreach ($requests as $key => $request) {
            $promises[$key] = $this->client->postAsync('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => $request['messages'],
                ],
            ]);
        }

        // Wait for all requests to complete
        $results = Promise\Utils::settle($promises)->wait();

        $responses = [];
        foreach ($results as $key => $result) {
            if ($result['state'] === 'fulfilled') {
                $responses[$key] = json_decode(
                    $result['value']->getBody()->getContents(),
                    true
                );
            } else {
                $responses[$key] = [
                    'error' => $result['reason']->getMessage(),
                ];
            }
        }

        return $responses;
    }
}

// Usage
$async = new AsyncOpenAI($_ENV['OPENAI_API_KEY']);

$requests = [
    'greeting' => [
        'messages' => [['role' => 'user', 'content' => 'Say hello']]
    ],
    'question' => [
        'messages' => [['role' => 'user', 'content' => 'What is PHP?']]
    ],
];

$responses = $async->batchChat($requests);
```

---

## Exercises

### Exercise 1: Custom Rate Limiter

Build middleware that:
1. Tracks requests per minute
2. Delays requests when limit approached
3. Logs rate limit status
4. Provides usage statistics

### Exercise 2: Response Cache Layer

Implement:
1. Cache identical requests
2. TTL-based invalidation
3. Cache hit/miss metrics
4. Memory-efficient storage

### Exercise 3: Multi-Client Manager

Create a manager that:
1. Routes to different API keys based on load
2. Implements health checks
3. Automatic failover
4. Performance monitoring

### Exercise 4: Request Builder

Build a fluent interface:
```php
$response = OpenAI::chat()
    ->model('gpt-4')
    ->system('You are helpful')
    ->user('Hello')
    ->temperature(0.8)
    ->maxTokens(100)
    ->send();
```

---

## Key Takeaways

- ✅ Guzzle is the de facto standard for HTTP in PHP
- ✅ OpenAI SDK provides convenience, custom clients provide control
- ✅ Middleware enables cross-cutting concerns like logging and retry
- ✅ Connection pooling improves performance for high-volume applications
- ✅ Async requests enable parallel processing of multiple calls
- ✅ Custom wrappers allow domain-specific APIs
- ✅ Error handling should be consistent across all HTTP clients

---

## Next Steps

With a solid HTTP integration layer, you're ready to tackle error handling and resilience patterns!

👉 **[Chapter 05: Error Handling & Resilience](/series/openai-php/chapters/05-error-handling-resilience)**

In the next chapter, you'll learn:
- OpenAI error types and codes
- Retry strategies with exponential backoff
- Circuit breaker patterns
- Building fault-tolerant applications

---

[← Previous: Chapter 03](/series/openai-php/chapters/03-api-authentication-configuration) | [Next: Chapter 05 →](/series/openai-php/chapters/05-error-handling-resilience)
