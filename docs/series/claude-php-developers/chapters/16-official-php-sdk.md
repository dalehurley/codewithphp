---
title: "16: The Community PHP SDK"
description: "Master the Claude PHP SDK (claude-php/Claude-PHP-SDK) architecture, advanced features, configuration, testing utilities, and best practices for production applications. This community SDK provides full parity with the Python SDK."
series: "claude-php-developers"
chapter: 16
order: 16
difficulty: "Expert"
prerequisites:
  - "PHP 8.4+ with type declarations"
  - "Composer dependency management"
  - "Understanding of PSR standards"
  - "Basic Claude API knowledge"
---

![16: The Official PHP SDK](/images/claude-php/chapter-16-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 16</span>
</div>

# Chapter 16: The Community PHP SDK

## Overview

The Claude PHP SDK (claude-php/Claude-PHP-SDK) is a community-maintained, production-ready library that provides **full parity with the Python SDK**. Created by Dale Hurley, this SDK offers comprehensive Claude API coverage, PSR compliance, advanced features, and extensive examples. This chapter explores the SDK's architecture, advanced capabilities, and best practices for building enterprise-grade applications.

By mastering the SDK's internals, you'll be able to customize behavior, implement sophisticated logging and monitoring, write comprehensive tests, and optimize performance for your specific use cases.

## What You'll Build

By the end of this chapter, you will have created:

- A fully configured Anthropic SDK client with custom middleware
- Custom HTTP transport implementations for caching and specialized requirements
- Comprehensive middleware for logging, metrics collection, and rate limiting
- Production-ready error handling with retry logic
- A testable service layer using dependency injection
- Laravel service provider integration (optional)
- Configuration management system for SDK settings

## Prerequisites

Before diving in, ensure you have:

- ✓ **PHP 8.4+** with strict typing enabled
- ✓ **Composer** for dependency management
- ✓ **PSR-7/PSR-18** understanding (HTTP message interfaces)
- ✓ **Anthropic API key** and basic API knowledge
- ✓ Completed [Chapter 03: Your First Claude Request](/series/claude-php-developers/chapters/03-first-claude-request) or equivalent SDK experience

**Estimated Time**: 45-60 minutes

## Installing the SDK

Install the community Claude PHP SDK:

```bash
# Install the Claude PHP SDK (community SDK with full Python SDK parity)
composer require claude-php/claude-php-sdk
```

**Verify installation:**

```bash
# Check that the package was installed correctly
composer show claude-php/claude-php-sdk
```

You should see package details including version, description, and dependencies. The SDK requires PHP 8.1+ and follows PSR standards for HTTP messaging.

::: tip Community SDK Benefits
The community Claude PHP SDK (claude-php/Claude-PHP-SDK) provides:
- **Full Python SDK parity** - Complete feature coverage
- **29 comprehensive examples** covering all Claude documentation pages
- **PSR compliance** - Works with any PSR-18 HTTP client
- **Production ready** - All examples tested and verified
- **Active maintenance** - Regularly updated with latest Claude API features

For production applications, this SDK is strongly recommended over direct HTTP requests.
:::

## Objectives

By completing this chapter, you will:

- Understand the Claude PHP SDK architecture and core components
- Master client configuration and customization patterns
- Implement custom HTTP transports and middleware for production needs
- Work with strongly-typed response objects and their properties
- Write comprehensive tests using mockable SDK interfaces
- Apply dependency injection patterns for maintainable code
- Integrate the SDK into Laravel applications with service providers

## SDK Architecture Overview

The Anthropic PHP SDK follows modern PHP best practices and PSR standards. Understanding its architecture helps you leverage its full power and customize it for your needs.

### Why SDK Architecture Matters

The SDK uses a factory pattern, dependency injection, and PSR interfaces to provide:
- **Flexibility**: Customize HTTP clients, middleware, and behavior
- **Testability**: Mock interfaces for unit testing
- **Type Safety**: Strongly-typed responses for IDE support
- **Standards Compliance**: PSR-7/PSR-18 for interoperability

Let's explore the core architecture:

```php
<?php
# filename: examples/01-sdk-architecture.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

// The SDK uses a simple constructor pattern for initialization
$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY'),
    baseUrl: 'https://api.anthropic.com/v1',  // Optional: custom endpoint
    timeout: 30.0,                             // Optional: request timeout
    maxRetries: 2,                             // Optional: auto-retry on 429/5xx
    customHeaders: [                           // Optional: custom headers
        'X-Custom-Header' => 'value'
    ]
);

// Resources are accessed via the client
$messagesResource = $client->messages();

echo "SDK Architecture:\n";
echo "- Client: " . get_class($client) . "\n";
echo "- Messages Resource: " . get_class($messagesResource) . "\n";
```

### Why It Works

The SDK uses a simple constructor pattern with named parameters for configuration. This provides a clean, readable API while maintaining flexibility. All configuration options are optional with sensible defaults, making it easy to get started while allowing customization for production needs.

Resources like `Messages` are accessed through the client, providing a clean API for different endpoint groups. The SDK is PSR-compliant and works with any PSR-18 HTTP client.

### Core Components

1. **Client** (`ClaudePhp`) - Main entry point, manages HTTP communication
2. **Resources** (`messages()`, etc.) - API endpoint wrappers
3. **HTTP Layer** - PSR-18 compliant HTTP client abstraction
4. **Responses** - Structured response objects with array access
5. **Configuration** - Simple constructor-based configuration with named parameters

## Advanced Client Configuration

The SDK constructor allows extensive customization through named parameters. You can configure timeouts, set custom headers, enable automatic retries, and customize the HTTP client. This is essential for production applications that need logging, metrics, retry logic, or custom authentication.

### Why Customize the Client?

Production applications often need:
- **Request/Response Logging**: Track all API calls for debugging
- **Retry Logic**: Automatically retry failed requests
- **Rate Limiting**: Prevent exceeding API quotas
- **Custom Headers**: Add request IDs, tracing, or authentication
- **Timeout Configuration**: Adjust for long-running requests

Let's build a production-ready client configuration:

```php
<?php
# filename: examples/02-advanced-factory.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

// Custom HTTP client with middleware
$handlerStack = HandlerStack::create();

// Add request logging middleware
$handlerStack->push(Middleware::mapRequest(function (RequestInterface $request) {
    error_log('API Request: ' . $request->getMethod() . ' ' . $request->getUri());
    error_log('Headers: ' . json_encode($request->getHeaders()));
    return $request;
}));

// Add retry middleware
$handlerStack->push(Middleware::retry(function ($retries, $request, $response, $exception) {
    // Retry on 5xx errors or network issues
    if ($retries >= 3) {
        return false;
    }
    if ($exception) {
        return true;
    }
    if ($response && $response->getStatusCode() >= 500) {
        return true;
    }
    return false;
}, function ($retries) {
    // Exponential backoff: 1s, 2s, 4s
    return 1000 * pow(2, $retries);
}));

$guzzleClient = new GuzzleClient([
    'handler' => $handlerStack,
    'timeout' => 120,
    'connect_timeout' => 10,
]);

// Create Anthropic client with custom configuration
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($guzzleClient)
    ->withHttpHeader('X-Request-ID', uniqid('req_'))
    ->withHttpHeader('X-Application', 'MyApp/1.0')
    ->make();

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 100,
        'messages' => [
            ['role' => 'user', 'content' => 'Say "SDK Test"']
        ]
    ]);

    echo "Response: " . $response->content[0]['text'] . "\n";
    echo "Request ID: " . $response->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Why It Works

The `HandlerStack` is Guzzle's middleware system. Each middleware wraps the next one, creating a chain. When a request is made, it flows through each middleware in order, then the response flows back through in reverse order.

The `mapRequest` middleware logs requests before they're sent. The `retry` middleware intercepts responses and exceptions, deciding whether to retry based on error type and retry count. The exponential backoff function calculates wait times: 1 second, 2 seconds, 4 seconds, etc.

Custom headers like `X-Request-ID` help track requests across distributed systems, while `X-Application` identifies your application to the API.

## Custom HTTP Transports

Sometimes you need specialized HTTP behavior beyond middleware. Custom transports implement the PSR-18 `ClientInterface`, allowing you to wrap, modify, or replace the HTTP layer entirely. Common use cases include caching, request transformation, or integration with custom infrastructure.

Implement custom transports for specialized requirements:

```php
<?php
# filename: examples/03-custom-transport.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Custom HTTP transport that caches responses
 */
class CachingHttpClient implements ClientInterface
{
    private array $cache = [];

    public function __construct(
        private ClientInterface $innerClient,
        private int $ttl = 3600
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // Generate cache key from request
        $cacheKey = $this->getCacheKey($request);

        // Check cache
        if (isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if (time() < $cached['expires']) {
                error_log("Cache HIT: {$cacheKey}");
                return $cached['response'];
            }
            unset($this->cache[$cacheKey]);
        }

        // Cache miss - make actual request
        error_log("Cache MISS: {$cacheKey}");
        $response = $this->innerClient->sendRequest($request);

        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            $this->cache[$cacheKey] = [
                'response' => $response,
                'expires' => time() + $this->ttl
            ];
        }

        return $response;
    }

    private function getCacheKey(RequestInterface $request): string
    {
        $body = (string) $request->getBody();
        return md5($request->getMethod() . $request->getUri() . $body);
    }
}

// Use custom caching transport
$guzzleClient = new \GuzzleHttp\Client(['timeout' => 30]);
$cachingClient = new CachingHttpClient($guzzleClient, ttl: 1800);

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($cachingClient)
    ->make();

// First request - cache miss
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 50,
    'messages' => [['role' => 'user', 'content' => 'Count to 3']]
]);

echo "First response: " . $response1->content[0]->text . "\n\n";

// Second identical request - cache hit
$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 50,
    'messages' => [['role' => 'user', 'content' => 'Count to 3']]
]);

echo "Second response: " . $response2->content[0]->text . "\n";
```

### Why It Works

The `CachingHttpClient` implements `ClientInterface`, so it can be used anywhere a standard HTTP client is expected. It wraps another client (the "inner client") and intercepts requests.

The cache key is generated from the HTTP method, URI, and request body. This ensures identical requests produce the same cache key. The TTL (time-to-live) determines how long cached responses remain valid.

::: warning Cache Considerations
This is a simple in-memory cache for demonstration. Production applications should use Redis, Memcached, or another persistent cache store. Also, be careful caching responses—Claude responses are often unique and shouldn't be cached in many scenarios. Only cache responses when the same input should always produce the same output.
:::

## Response Object Deep Dive

The SDK provides strongly-typed response objects instead of raw arrays. This gives you IDE autocomplete, type checking, and better error messages. Understanding the response structure helps you extract data efficiently and handle edge cases.

```php
<?php
# filename: examples/04-response-objects.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Responses\Messages\Content\TextContent;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 200,
    'messages' => [
        ['role' => 'user', 'content' => 'What is PHP?']
    ]
]);

// Response object is strongly typed
assert($response instanceof CreateResponse);

// Access all response properties
echo "Response Analysis:\n";
echo "================\n\n";

// Basic properties
echo "ID: {$response->id}\n";
echo "Type: {$response->type}\n";
echo "Role: {$response->role}\n";
echo "Model: {$response->model}\n";
echo "Stop Reason: {$response->stopReason}\n";
echo "Stop Sequence: " . ($response->stopSequence ?? 'null') . "\n\n";

// Content array
echo "Content Blocks: " . count($response->content) . "\n";
foreach ($response->content as $index => $contentBlock) {
    if ($contentBlock instanceof TextContent) {
        echo "Block {$index} (text): " . substr($contentBlock->text, 0, 100) . "...\n";
    }
}
echo "\n";

// Usage statistics
echo "Token Usage:\n";
echo "  Input Tokens: {$response->usage->input_tokens}\n";
echo "  Output Tokens: {$response->usage->output_tokens}\n";
echo "  Total Tokens: " . ($response->usage->input_tokens + $response->usage->output_tokens) . "\n\n";

// Convert to array (useful for logging/storage)
$responseArray = $response->toArray();
echo "As Array:\n";
print_r($responseArray);
```

### Why It Works

Response objects are value objects—immutable data structures with typed properties. The `CreateResponse` class has properties like `id`, `type`, `role`, `model`, `content`, `usage`, etc., all with proper types.

The `content` property is an array of content blocks. Each block can be a `TextContent`, `ImageContent`, or other types. The `instanceof` check ensures type safety when accessing block-specific properties.

The `toArray()` method converts the response to a plain array, useful for JSON serialization, logging, or database storage. This maintains backward compatibility while providing type safety in your application code.

## Testing with the SDK

The SDK's contract-based design makes it highly testable. You can mock the `ClientContract` interface or individual resources like `Messages` to test your application logic without making real API calls. This is essential for fast, reliable unit tests.

```php
<?php
# filename: tests/ClaudeServiceTest.php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Anthropic\Contracts\ClientContract;
use Anthropic\Resources\Messages;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Responses\Messages\Content\TextContent;
use Anthropic\Responses\Messages\Usage;

class ClaudeServiceTest extends TestCase
{
    public function testGenerateText(): void
    {
        // Create mock response
        $mockResponse = new CreateResponse(
            id: 'msg_test123',
            type: 'message',
            role: 'assistant',
            content: [
                new TextContent(
                    type: 'text',
                    text: 'This is a test response'
                )
            ],
            model: 'claude-sonnet-4-20250514',
            stopReason: 'end_turn',
            stopSequence: null,
            usage: new Usage(
                inputTokens: 10,
                outputTokens: 15
            )
        );

        // Create mock Messages resource
        $mockMessages = $this->createMock(Messages::class);
        $mockMessages->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return $params['model'] === 'claude-sonnet-4-20250514'
                    && $params['max_tokens'] === 100
                    && $params['messages'][0]['content'] === 'Test prompt';
            }))
            ->willReturn($mockResponse);

        // Create mock client
        $mockClient = $this->createMock(ClientContract::class);
        $mockClient->expects($this->once())
            ->method('messages')
            ->willReturn($mockMessages);

        // Test your service with mocked client
        $service = new \App\Services\ClaudeService($mockClient);
        $result = $service->generateText('Test prompt', maxTokens: 100);

        $this->assertEquals('This is a test response', $result);
    }
}
```

### Why It Works

PHPUnit's `createMock()` generates mock objects that implement the same interface as the real classes. The `expects()` method sets up expectations—how many times a method should be called and with what parameters.

The `callback()` matcher allows custom validation logic. Here, we verify that the `create()` method is called with the correct parameters. The mock returns a predefined response, allowing us to test our service logic without hitting the real API.

This pattern enables fast, isolated unit tests that don't depend on external services. You can test error handling, retry logic, and business rules without making real API calls.

## Middleware Pattern Implementation

Middleware is a powerful pattern for cross-cutting concerns like logging, metrics, and rate limiting. Instead of modifying every API call, you configure middleware once and it applies to all requests. This keeps your code DRY (Don't Repeat Yourself) and makes it easy to add or remove functionality.

Create reusable middleware for common concerns:

```php
<?php
# filename: examples/05-middleware-pattern.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestLogger
{
    public static function middleware(string $logFile): callable
    {
        return Middleware::tap(
            function (RequestInterface $request) use ($logFile) {
                $logEntry = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                ];
                file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
            }
        );
    }
}

class MetricsCollector
{
    private static array $metrics = [];

    public static function middleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $startTime = microtime(true);

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request, $startTime) {
                        $duration = microtime(true) - $startTime;

                        self::$metrics[] = [
                            'endpoint' => (string) $request->getUri(),
                            'status' => $response->getStatusCode(),
                            'duration' => $duration,
                            'timestamp' => time(),
                        ];

                        return $response;
                    }
                );
            };
        };
    }

    public static function getMetrics(): array
    {
        return self::$metrics;
    }

    public static function getAverageDuration(): float
    {
        if (empty(self::$metrics)) {
            return 0.0;
        }
        $total = array_sum(array_column(self::$metrics, 'duration'));
        return $total / count(self::$metrics);
    }
}

class RateLimiter
{
    private int $requestsPerMinute;
    private array $timestamps = [];

    public function __construct(int $requestsPerMinute = 60)
    {
        $this->requestsPerMinute = $requestsPerMinute;
    }

    public function middleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                // Clean old timestamps (older than 1 minute)
                $now = time();
                $this->timestamps = array_filter(
                    $this->timestamps,
                    fn($ts) => $ts > $now - 60
                );

                // Check rate limit
                if (count($this->timestamps) >= $this->requestsPerMinute) {
                    $waitTime = 60 - ($now - min($this->timestamps));
                    error_log("Rate limit reached. Waiting {$waitTime} seconds...");
                    sleep($waitTime);
                    $this->timestamps = [];
                }

                $this->timestamps[] = time();
                return $handler($request, $options);
            };
        };
    }
}

// Build handler stack with multiple middleware
$handlerStack = HandlerStack::create();
$handlerStack->push(RequestLogger::middleware('/tmp/claude-requests.log'));
$handlerStack->push(MetricsCollector::middleware());
$handlerStack->push((new RateLimiter(requestsPerMinute: 50))->middleware());

$guzzleClient = new GuzzleClient([
    'handler' => $handlerStack,
    'timeout' => 30,
]);

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($guzzleClient)
    ->make();

// Make several requests
for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}...\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 50,
        'messages' => [
            ['role' => 'user', 'content' => "Count to {$i}"]
        ]
    ]);
    echo "Response: " . $response->content[0]['text'] . "\n\n";
}

// Display metrics
echo "\n=== Metrics ===\n";
echo "Total requests: " . count(MetricsCollector::getMetrics()) . "\n";
echo "Average duration: " . number_format(MetricsCollector::getAverageDuration(), 3) . "s\n";
echo "\nDetailed metrics:\n";
print_r(MetricsCollector::getMetrics());
```

### Why It Works

Each middleware class encapsulates a specific concern. `RequestLogger` handles logging, `MetricsCollector` tracks performance, and `RateLimiter` enforces limits. They're composed together in a handler stack.

The middleware functions return closures that wrap the next handler. When a request is made, each middleware can:
- **Before**: Modify the request or perform actions
- **After**: Process the response or collect metrics
- **Intercept**: Stop the request (like rate limiting) or retry

The `MetricsCollector` uses static properties to store metrics across requests. In production, you'd send these to a monitoring system like Prometheus, Datadog, or CloudWatch.

The `RateLimiter` maintains a sliding window of request timestamps. When the limit is reached, it calculates how long to wait before allowing more requests. This prevents exceeding API rate limits.

## Error Handling and Exceptions

The SDK throws specific exception types for different error scenarios. Handling these appropriately ensures your application responds correctly to various failure modes. Some errors are retryable (network issues, server errors), while others aren't (invalid requests, authentication failures).

The SDK provides specific exception types:

```php
<?php
# filename: examples/06-exception-handling.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Anthropic\Exceptions\ErrorException;
use Anthropic\Exceptions\RateLimitException;
use Anthropic\Exceptions\InvalidRequestException;
use Anthropic\Exceptions\AuthenticationException;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

function makeRequestWithRetry(
    Anthropic\Contracts\ClientContract $client,
    array $params,
    int $maxRetries = 3
): ?Anthropic\Responses\Messages\CreateResponse {
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            return $client->messages()->create($params);

        } catch (RateLimitException $e) {
            $attempt++;
            $waitTime = min(pow(2, $attempt), 60); // Exponential backoff, max 60s

            echo "Rate limit hit. Attempt {$attempt}/{$maxRetries}. ";
            echo "Waiting {$waitTime}s...\n";

            if ($attempt >= $maxRetries) {
                throw $e;
            }

            sleep($waitTime);

        } catch (InvalidRequestException $e) {
            // Don't retry invalid requests
            echo "Invalid request: " . $e->getMessage() . "\n";
            echo "Response body: " . $e->getResponse()?->getBody() . "\n";
            throw $e;

        } catch (AuthenticationException $e) {
            // Don't retry auth errors
            echo "Authentication failed: " . $e->getMessage() . "\n";
            throw $e;

        } catch (ErrorException $e) {
            // Retry server errors
            $attempt++;
            $statusCode = $e->getResponse()?->getStatusCode();

            if ($statusCode >= 500 && $attempt < $maxRetries) {
                $waitTime = $attempt * 2;
                echo "Server error ({$statusCode}). Retrying in {$waitTime}s...\n";
                sleep($waitTime);
            } else {
                throw $e;
            }

        } catch (\Exception $e) {
            echo "Unexpected error: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    return null;
}

// Example usage
try {
    $response = makeRequestWithRetry($client, [
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 100,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, Claude!']
        ]
    ]);

    if ($response) {
        echo "Success: " . $response->content[0]['text'] . "\n";
    }

} catch (\Exception $e) {
    echo "Failed after retries: " . $e->getMessage() . "\n";
    error_log("Claude API error: " . $e->getMessage());
}
```

### Why It Works

Different exception types require different handling strategies:

- **`RateLimitException`**: Retry with exponential backoff, respecting rate limit headers
- **`InvalidRequestException`**: Don't retry—fix the request instead
- **`AuthenticationException`**: Don't retry—check credentials
- **`ErrorException`** (5xx): Retry—server errors are often transient

The exponential backoff (`pow(2, $attempt)`) increases wait time with each retry: 1s, 2s, 4s, 8s. This prevents overwhelming a struggling server while still retrying transient failures.

The `?->` null-safe operator (`$e->getResponse()?->getStatusCode()`) safely accesses the response status code, handling cases where the exception doesn't have a response object.

## SDK Best Practices

Following best practices ensures your SDK integration is maintainable, testable, and production-ready. These patterns apply whether you're building a small script or a large enterprise application.

### 1. Use Dependency Injection

```php
<?php
# filename: examples/07-dependency-injection.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Contracts\ClientContract;
use Anthropic\Responses\Messages\CreateResponse;
use Psr\Log\LoggerInterface;

class ContentGenerator
{
    public function __construct(
        private ClientContract $client,
        private LoggerInterface $logger,
        private string $defaultModel = 'claude-sonnet-4-20250514'
    ) {}

    public function generate(string $prompt, int $maxTokens = 1024): string
    {
        $this->logger->info('Generating content', [
            'prompt_length' => strlen($prompt),
            'max_tokens' => $maxTokens
        ]);

        try {
            $response = $this->client->messages()->create([
                'model' => $this->defaultModel,
                'max_tokens' => $maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            $this->logger->info('Content generated successfully', [
                'tokens_used' => $response->usage->output_tokens
            ]);

            return $response->content[0]['text'];

        } catch (\Exception $e) {
            $this->logger->error('Content generation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

### Why It Works

Dependency injection allows you to:
- **Test easily**: Inject mock clients in tests
- **Swap implementations**: Use different clients for different environments
- **Follow SOLID principles**: Depend on abstractions, not concretions

The `ClientContract` interface is the abstraction. Your service depends on this interface, not the concrete `Client` class. This makes your code flexible and testable.

The logger is injected via PSR-3 `LoggerInterface`, making it framework-agnostic. You can use Monolog, Laravel's logger, or any PSR-3 compatible logger.

### 2. Configuration Management

Centralizing configuration makes it easy to manage different environments (development, staging, production) and update settings without changing code. Configuration files separate "what" (settings) from "how" (implementation).

```php
<?php
# filename: config/claude.php

return [
    'api_key' => env('ANTHROPIC_API_KEY'),

    'default_model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),

    'models' => [
        'fast' => 'claude-haiku-4-20250514',
        'balanced' => 'claude-sonnet-4-20250514',
        'powerful' => 'claude-opus-4-20250514',
    ],

    'defaults' => [
        'max_tokens' => 4096,
        'temperature' => 1.0,
    ],

    'http' => [
        'timeout' => 120,
        'connect_timeout' => 10,
        'retry' => [
            'max_attempts' => 3,
            'backoff_multiplier' => 2,
        ],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'driver' => 'redis',
    ],
];
```

### Why It Works

Configuration arrays provide a single source of truth for SDK settings. The `env()` helper reads from environment variables, keeping secrets out of code. Default values ensure the application works even if environment variables aren't set.

Model aliases (`fast`, `balanced`, `powerful`) make it easy to switch models without changing code throughout your application. HTTP configuration centralizes timeout and retry settings, making it easy to tune for your use case.

### 3. Service Provider (Laravel Example)

Laravel's service container manages object creation and dependencies. A service provider registers the SDK client as a singleton, ensuring only one instance exists per request lifecycle. This improves performance and ensures consistent configuration.

```php
<?php
# filename: app/Providers/ClaudeServiceProvider.php
declare(strict_types=1);

namespace App\Providers;

use ClaudePhp\ClaudePhp;
use Anthropic\Contracts\ClientContract;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\ServiceProvider;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClientContract::class, function ($app) {
            $config = config('claude');

            // Build HTTP client with middleware
            $handlerStack = HandlerStack::create();

            // Add logging middleware
            if ($app->bound('log')) {
                $handlerStack->push(
                    Middleware::log($app['log'], new \GuzzleHttp\MessageFormatter())
                );
            }

            // Add retry middleware
            $handlerStack->push(
                Middleware::retry(
                    function ($retries, $request, $response, $exception) use ($config) {
                        return $retries < $config['http']['retry']['max_attempts']
                            && ($exception || ($response && $response->getStatusCode() >= 500));
                    },
                    function ($retries) use ($config) {
                        return 1000 * pow($config['http']['retry']['backoff_multiplier'], $retries);
                    }
                )
            );

            $guzzleClient = new GuzzleClient([
                'handler' => $handlerStack,
                'timeout' => $config['http']['timeout'],
                'connect_timeout' => $config['http']['connect_timeout'],
            ]);

            // Create Claude client
            return Anthropic::factory()
                ->withApiKey($config['api_key'])
                ->withHttpClient($guzzleClient)
                ->make();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/claude.php' => config_path('claude.php'),
        ], 'claude-config');
    }
}
```

### Why It Works

The `register()` method binds the `ClientContract` interface to a concrete implementation. Laravel's container resolves this automatically when you type-hint `ClientContract` in constructors.

The singleton binding ensures the same client instance is reused throughout the request, improving performance. Middleware is configured once during service registration, applying to all SDK calls automatically.

The `boot()` method publishes the configuration file, allowing developers to customize settings via `config/claude.php` without modifying the service provider.

## Advanced SDK Configuration for Production

Production Claude applications need advanced configuration beyond basic usage. This section covers streaming implementation, performance tuning, metrics collection, security hardening, cost tracking, and development tools.

### 1. Streaming with the SDK

The SDK supports streaming responses for real-time output. Use `createStreamed()` instead of `create()` to receive tokens as they're generated.

```php
<?php
# filename: examples/08-streaming.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Streaming configuration - use createStreamed() instead of create()
$stream = $client->messages()->createStreamed([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Write a short poem about PHP']
    ]
]);

// Process streaming events
foreach ($stream as $event) {
    // Check event type
    if ($event->type === 'content_block_delta') {
        // New content delta
        if ($event->delta->type === 'text_delta') {
            echo $event->delta->text;
        }
    } elseif ($event->type === 'message_start') {
        echo "Stream started...\n";
    } elseif ($event->type === 'message_stop') {
        echo "\nStream finished.\n";
    } elseif ($event->type === 'message_delta') {
        // Usage information at end
        if (isset($event->usage)) {
            echo "\nUsage: " . $event->usage->outputTokens . " output tokens\n";
        }
    }
}
```

### Why It Works

The `createStreamed()` method returns an iterator that yields events instead of waiting for the complete response. This allows you to process tokens as they arrive, creating real-time user experiences.

Event types include:
- `message_start`: Stream beginning
- `content_block_delta`: Text chunks arriving
- `message_delta`: Final metrics and stop information
- `message_stop`: Stream complete

For production streaming via HTTP (Server-Sent Events), see [Chapter 06: Streaming Responses](/series/claude-php-developers/chapters/06-streaming-responses).

### 2. Performance Optimization

Configure the HTTP client for optimal performance with connection pooling, timeouts, and keep-alive settings.

```php
<?php
# filename: examples/09-performance-tuning.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

// Create handler stack with performance middleware
$handlerStack = HandlerStack::create();

// Add request timing middleware
$handlerStack->push(function (callable $handler) {
    return function ($request, $options) use ($handler) {
        $start = microtime(true);
        return $handler($request, $options)->then(
            function ($response) use ($start) {
                $duration = microtime(true) - $start;
                error_log(sprintf(
                    'Request took %.3f seconds',
                    $duration
                ));
                return $response;
            }
        );
    };
});

// Optimized Guzzle configuration
$guzzleClient = new GuzzleClient([
    'handler' => $handlerStack,
    'timeout' => 120,                           // Overall request timeout
    'connect_timeout' => 10,                    // Connection establishment
    'pool_size' => 10,                          // Connection pool size
    'http_errors' => false,                     // Handle errors manually
    'headers' => [
        'Connection' => 'keep-alive',           // Reuse connections
        'Keep-Alive' => 'timeout=5, max=100',  // Keep-alive settings
    ],
    'curl' => [
        CURLOPT_TCP_KEEPALIVE => 1,            // Enable TCP keep-alive
        CURLOPT_TCP_KEEPIDLE => 60,            // Idle timeout (seconds)
        CURLOPT_TCP_KEEPINTVL => 30,           // Probing interval
    ],
]);

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($guzzleClient)
    ->make();

// Make multiple requests to benefit from connection pooling
for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}...\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 50,
        'messages' => [
            ['role' => 'user', 'content' => "Say 'Request {$i}'"]
        ]
    ]);
    echo $response->content[0]['text'] . "\n\n";
}
```

### Why It Works

**Connection Pooling**: Reusing connections across requests reduces latency (connection establishment takes ~10-50ms). The `pool_size` parameter controls how many simultaneous connections can be maintained.

**Keep-Alive**: HTTP keep-alive prevents closing connections after each request, allowing reuse. The `Connection: keep-alive` header and TCP-level keep-alive settings maintain the connection even during idle periods.

**Timeouts**: Different timeout values handle different scenarios:
- `connect_timeout`: Fails fast if server is unreachable
- `timeout`: Prevents requests from hanging indefinitely
- TCP keep-alive: Detects stale connections before timeout

This configuration can reduce average latency by 30-50% for multiple requests.

### 3. Collecting Metrics at SDK Level

Track API usage and performance directly from SDK responses.

```php
<?php
# filename: examples/10-sdk-metrics.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

class SDKMetricsCollector
{
    private array $metrics = [];

    public function __construct(
        private Anthropic $client
    ) {}

    public function trackRequest(array $params): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $this->client->messages()->create($params);

        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;

        // Extract metrics from response
        $this->metrics[] = [
            'timestamp' => time(),
            'model' => $response->model,
            'input_tokens' => $response->usage->input_tokens,
            'output_tokens' => $response->usage->output_tokens,
            'total_tokens' => $response->usage->input_tokens + $response->usage->output_tokens,
            'duration_seconds' => $duration,
            'memory_used_bytes' => $memoryUsed,
            'stop_reason' => $response->stopReason,
        ];
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getSummary(): array
    {
        if (empty($this->metrics)) {
            return [];
        }

        $totalRequests = count($this->metrics);
        $totalInputTokens = array_sum(array_column($this->metrics, 'input_tokens'));
        $totalOutputTokens = array_sum(array_column($this->metrics, 'output_tokens'));
        $totalDuration = array_sum(array_column($this->metrics, 'duration_seconds'));
        $durations = array_column($this->metrics, 'duration_seconds');

        return [
            'total_requests' => $totalRequests,
            'total_input_tokens' => $totalInputTokens,
            'total_output_tokens' => $totalOutputTokens,
            'total_tokens' => $totalInputTokens + $totalOutputTokens,
            'total_duration_seconds' => $totalDuration,
            'average_duration_seconds' => $totalDuration / $totalRequests,
            'min_duration_seconds' => min($durations),
            'max_duration_seconds' => max($durations),
            'tokens_per_second' => ($totalInputTokens + $totalOutputTokens) / $totalDuration,
        ];
    }
}

// Usage
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$collector = new SDKMetricsCollector($client);

// Track multiple requests
for ($i = 1; $i <= 3; $i++) {
    echo "Processing request {$i}...\n";
    $collector->trackRequest([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 100,
        'messages' => [
            ['role' => 'user', 'content' => "What is the use of PHP? (Request {$i})"]
        ]
    ]);
}

// Display summary
$summary = $collector->getSummary();
echo "\n=== API Metrics Summary ===\n";
echo "Total Requests: " . $summary['total_requests'] . "\n";
echo "Total Tokens: " . $summary['total_tokens'] . "\n";
echo "Average Duration: " . number_format($summary['average_duration_seconds'], 3) . "s\n";
echo "Tokens/Second: " . number_format($summary['tokens_per_second'], 2) . "\n";
```

### Why It Works

Response objects contain usage data (`inputTokens`, `outputTokens`) and metadata (`stopReason`, `model`). By collecting these metrics across multiple requests, you can:
- Track API cost (multiply tokens by per-token pricing)
- Monitor performance (average duration, latency trends)
- Detect anomalies (unusual token counts, slow responses)
- Optimize (identify expensive operations, slow patterns)

This is essential for [Chapter 39: Cost Optimization](/series/claude-php-developers/chapters/39-cost-optimization) and [Chapter 37: Monitoring](/series/claude-php-developers/chapters/37-monitoring-observability).

### 4. Security: Request Signing and Certificate Pinning

Enhance security with request signing and certificate validation for sensitive environments.

```php
<?php
# filename: examples/11-security-hardening.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class RequestSigner
{
    public static function middleware(string $signingSecret): callable
    {
        return Middleware::mapRequest(function (RequestInterface $request) use ($signingSecret) {
            // Create signature from request
            $body = (string) $request->getBody();
            $signature = hash_hmac('sha256', $body, $signingSecret);

            // Add signature header
            return $request->withHeader('X-Signature', $signature)
                          ->withHeader('X-Signature-Algorithm', 'hmac-sha256');
        });
    }
}

// Create handler stack with security middleware
$handlerStack = HandlerStack::create();
$handlerStack->push(RequestSigner::middleware(getenv('REQUEST_SIGNING_SECRET', '')));

// Configure certificate pinning for sensitive environments
$certPath = getenv('CERT_PIN_PATH', '');
$verifySsl = $certPath ? ['cafile' => $certPath] : true;

$guzzleClient = new GuzzleClient([
    'handler' => $handlerStack,
    'timeout' => 120,
    'verify' => $verifySsl,  // Certificate validation
    'headers' => [
        'User-Agent' => 'MySecureApp/1.0',
    ],
]);

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($guzzleClient)
    ->make();

// Make request with signed headers
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 100,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, Claude!']
    ]
]);

echo "Response: " . $response->content[0]['text'] . "\n";
echo "Request was signed for security.\n";
```

### Why It Works

**Request Signing**: HMAC-SHA256 signatures prove the request came from your application. The server can verify the signature matches the body, preventing tampering.

**Certificate Pinning**: Instead of trusting any certificate authority, pinning validates against specific certificates. This prevents man-in-the-middle attacks even if a CA is compromised.

These patterns are covered in detail in [Chapter 36: Security Best Practices](/series/claude-php-developers/chapters/36-security-best-practices).

### 5. Cost Tracking from Responses

Calculate API costs directly from SDK responses using current pricing.

```php
<?php
# filename: examples/12-cost-tracking.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

class CostCalculator
{
    // Current pricing (2025) - verify at https://www.anthropic.com/pricing
    private const PRICING = [
        'claude-sonnet-4-20250514' => [
            'input' => 0.003 / 1000,      // $0.003 per 1M input tokens
            'output' => 0.015 / 1000,     // $0.015 per 1M output tokens
        ],
        'claude-haiku-4-20250514' => [
            'input' => 0.0008 / 1000,     // $0.0008 per 1M input tokens
            'output' => 0.004 / 1000,     // $0.004 per 1M output tokens
        ],
        'claude-opus-4-20250514' => [
            'input' => 0.015 / 1000,      // $0.015 per 1M input tokens
            'output' => 0.075 / 1000,     // $0.075 per 1M output tokens
        ],
    ];

    public function __construct(
        private Anthropic $client
    ) {}

    public function calculateCost(
        string $model,
        int $inputTokens,
        int $outputTokens
    ): float {
        if (!isset(self::PRICING[$model])) {
            throw new \RuntimeException("Unknown model: {$model}");
        }

        $pricing = self::PRICING[$model];
        $inputCost = $inputTokens * $pricing['input'];
        $outputCost = $outputTokens * $pricing['output'];

        return $inputCost + $outputCost;
    }

    public function estimateRequestCost(array $params): float
    {
        $estimatedInputTokens = $this->estimateTokens($params['messages'] ?? []);
        $maxOutputTokens = $params['max_tokens'] ?? 4096;

        $model = $params['model'] ?? 'claude-sonnet-4-20250514';
        return $this->calculateCost($model, $estimatedInputTokens, $maxOutputTokens);
    }

    public function getActualCost($response): float
    {
        return $this->calculateCost(
            $response->model,
            $response->usage->input_tokens,
            $response->usage->output_tokens
        );
    }

    private function estimateTokens(array $messages): int
    {
        // Rough estimate: ~4 characters per token
        $totalChars = 0;
        foreach ($messages as $message) {
            if (is_string($message['content'] ?? '')) {
                $totalChars += strlen($message['content']);
            }
        }
        return (int) ceil($totalChars / 4);
    }
}

// Usage
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$calculator = new CostCalculator($client);

$params = [
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'messages' => [
        ['role' => 'user', 'content' => 'Explain PHP type system']
    ]
];

// Estimate cost before making request
$estimatedCost = $calculator->estimateRequestCost($params);
echo "Estimated cost: $" . number_format($estimatedCost, 6) . "\n";

// Make actual request
$response = $client->messages()->create($params);

// Calculate actual cost
$actualCost = $calculator->getActualCost($response);
echo "Actual cost: $" . number_format($actualCost, 6) . "\n";
echo "Input tokens: " . $response->usage->input_tokens . "\n";
echo "Output tokens: " . $response->usage->output_tokens . "\n";
echo "Response: " . substr($response->content[0]['text'], 0, 100) . "...\n";
```

### Why It Works

By tracking costs at the SDK level, you can:
- **Pre-estimate**: Warn users or reject expensive requests before making them
- **Post-calculate**: Track actual costs for billing or budget tracking
- **Optimize**: Identify expensive operations and optimize prompts
- **Allocate**: Attribute costs to specific users or features

This is covered in depth in [Chapter 39: Cost Optimization](/series/claude-php-developers/chapters/39-cost-optimization).

### 6. Development Tools: Request Debugging and Replay

Debug and test SDK integration with request/response inspection and replay.

```php
<?php
# filename: examples/13-development-tools.php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestRecorder
{
    private array $requests = [];
    private string $recordFile;

    public function __construct(string $recordFile = '/tmp/claude-requests.json')
    {
        $this->recordFile = $recordFile;
    }

    public function middleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $startTime = microtime(true);

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request, $startTime) {
                        $duration = microtime(true) - $startTime;

                        // Record request/response
                        $this->requests[] = [
                            'timestamp' => date('Y-m-d H:i:s'),
                            'duration' => $duration,
                            'request' => [
                                'method' => $request->getMethod(),
                                'uri' => (string) $request->getUri(),
                                'headers' => $request->getHeaders(),
                                'body' => (string) $request->getBody(),
                            ],
                            'response' => [
                                'status' => $response->getStatusCode(),
                                'headers' => $response->getHeaders(),
                                'body' => (string) $response->getBody(),
                            ],
                        ];

                        return $response;
                    }
                );
            };
        };
    }

    public function saveRecording(): void
    {
        file_put_contents(
            $this->recordFile,
            json_encode($this->requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            FILE_APPEND
        );
        echo "Recorded " . count($this->requests) . " requests to {$this->recordFile}\n";
    }
}

// Create SDK client with recording middleware
$handlerStack = HandlerStack::create();
$recorder = new RequestRecorder();
$handlerStack->push($recorder->middleware());

$guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpClient($guzzleClient)
    ->make();

// Make requests - they'll be recorded
echo "Making requests with debugging enabled...\n";

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 100,
    'messages' => [
        ['role' => 'user', 'content' => 'What is PHP?']
    ]
]);

echo "Response: " . $response->content[0]['text'] . "\n\n";

// Save recording for later analysis
$recorder->saveRecording();

// In development, you can replay recorded requests
echo "Requests have been recorded to /tmp/claude-requests.json\n";
echo "Use this data for:\n";
echo "- Request/response inspection\n";
echo "- Performance analysis\n";
echo "- Debugging issues\n";
echo "- Unit test fixtures\n";
```

### Why It Works

Request recording in development provides:
- **Debugging**: Inspect exactly what was sent/received
- **Performance Analysis**: Track latency patterns
- **Testing**: Use recorded responses as fixtures for unit tests
- **Documentation**: Show example requests/responses in documentation
- **Troubleshooting**: Replay requests to diagnose issues

This is particularly useful during development and for [Chapter 36: Security Best Practices](/series/claude-php-developers/chapters/36-security-best-practices) audit logging.

## Troubleshooting

Common issues and solutions when working with the SDK:

**SDK not found after installation?**
- Run `composer dump-autoload`
- Verify `claude-php/claude-php-sdk` is in `composer.json` and `vendor/` directory
- Check minimum PHP version (8.2+)

**Type errors with response objects?**
- Ensure strict types are enabled (`declare(strict_types=1)`)
- Check you're using the correct response object properties
- Review the SDK's type definitions in `vendor/claude-php/claude-php-sdk/src/Responses/`

**Custom HTTP client not working?**
- Ensure your client implements `Psr\Http\Client\ClientInterface`
- Verify PSR-7 message implementations are compatible
- Check middleware is properly configured in handler stack

**Streaming not working?**
- Ensure you're using `createStreamed()` instead of `create()`
- Verify your HTTP client supports streaming
- Check timeout settings allow for long-running connections

## Wrap-up

Congratulations! You've mastered the official Anthropic PHP SDK. Here's what you've accomplished:

- ✓ **Understood SDK architecture** — Factory pattern, client, resources, and transports
- ✓ **Configured advanced clients** — Custom HTTP clients, middleware, and headers
- ✓ **Built custom transports** — Implemented caching and specialized HTTP layers
- ✓ **Worked with typed responses** — Leveraged strongly-typed response objects
- ✓ **Created testable code** — Used mockable interfaces for comprehensive testing
- ✓ **Implemented middleware** — Logging, metrics, and rate limiting patterns
- ✓ **Handled errors gracefully** — Retry logic with exponential backoff
- ✓ **Applied best practices** — Dependency injection and configuration management

The SDK's architecture follows modern PHP standards, making it production-ready and highly customizable. You can now build enterprise-grade applications with sophisticated monitoring, caching, and error handling.

In the next chapter, you'll build a reusable Claude service class that wraps the SDK with your own business logic, making it even easier to integrate Claude into your applications.

## Further Reading

- [Anthropic PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php) — Official SDK repository and documentation
- [PSR-7: HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/) — Standard interfaces for HTTP messages
- [PSR-18: HTTP Client](https://www.php-fig.org/psr/psr-18/) — Standard HTTP client interface
- [Guzzle HTTP Client Documentation](https://docs.guzzlephp.org/) — Popular PSR-18 implementation used in examples
- [PHP Dependency Injection](https://www.php.net/manual/en/language.oop5.decon.php) — PHP constructor and dependency injection patterns

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="16"
  label="You've mastered the Anthropic PHP SDK architecture!"
/>

---

Continue to [Chapter 17: Building a Claude Service Class](/series/claude-php-developers/chapters/17-claude-service-class) to create reusable service layers.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 16 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-16)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-16
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-sdk-architecture.php
```
