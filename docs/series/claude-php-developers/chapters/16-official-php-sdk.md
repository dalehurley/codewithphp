---
title: "16: The Official PHP SDK"
description: "Master the Anthropic PHP SDK architecture, advanced features, middleware, testing utilities, and best practices for production applications."
series: "claude-php-developers"
chapter: 16
order: 16
difficulty: "Expert"
prerequisites:
  - "PHP 8.2+ with type declarations"
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

# Chapter 16: The Official PHP SDK

## Overview

The official Anthropic PHP SDK is more than just a wrapper around HTTP requests—it's a robust, production-ready library with advanced features like middleware, testing utilities, custom transports, and comprehensive type safety. This chapter explores the SDK's architecture, advanced capabilities, and best practices for building enterprise-grade applications.

By mastering the SDK's internals, you'll be able to customize behavior, implement sophisticated logging and monitoring, write comprehensive tests, and optimize performance for your specific use cases.

## Prerequisites

Before diving in, ensure you have:

- ✓ **PHP 8.2+** with strict typing enabled
- ✓ **Composer** for dependency management
- ✓ **PSR-7/PSR-18** understanding (HTTP message interfaces)
- ✓ **Anthropic API key** and basic API knowledge

**Estimated Time**: 45-60 minutes

## SDK Architecture Overview

The Anthropic PHP SDK follows modern PHP best practices and PSR standards:

```php
<?php
# filename: examples/01-sdk-architecture.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Client;
use Anthropic\Resources\Messages;
use Anthropic\Contracts\ClientContract;

// The SDK uses a factory pattern for initialization
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withBaseUrl('https://api.anthropic.com/v1') // Optional: custom endpoint
    ->withHttpClient($customHttpClient)            // Optional: custom PSR-18 client
    ->withStreamHandler($customStreamHandler)      // Optional: custom streaming
    ->withHttpHeader('X-Custom-Header', 'value')   // Optional: custom headers
    ->make();

// The client implements ClientContract
assert($client instanceof ClientContract);
assert($client instanceof Client);

// Resources are accessed via the client
$messagesResource = $client->messages();
assert($messagesResource instanceof Messages);

echo "SDK Architecture:\n";
echo "- Client: " . get_class($client) . "\n";
echo "- Messages Resource: " . get_class($messagesResource) . "\n";
```

### Core Components

1. **Factory** (`Anthropic::factory()`) - Fluent API for building clients
2. **Client** (`Client`) - Main entry point, manages HTTP communication
3. **Resources** (`Messages`, etc.) - API endpoint wrappers
4. **Transports** - HTTP layer abstraction (PSR-7/PSR-18)
5. **Responses** - Strongly-typed response objects

## Advanced Factory Configuration

The factory pattern allows extensive customization:

```php
<?php
# filename: examples/02-advanced-factory.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
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

    echo "Response: " . $response->content[0]->text . "\n";
    echo "Request ID: " . $response->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Custom HTTP Transports

Implement custom transports for specialized requirements:

```php
<?php
# filename: examples/03-custom-transport.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
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

## Response Object Deep Dive

The SDK provides strongly-typed response objects:

```php
<?php
# filename: examples/04-response-objects.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Responses\Messages\Content\TextContent;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

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
echo "  Input Tokens: {$response->usage->inputTokens}\n";
echo "  Output Tokens: {$response->usage->outputTokens}\n";
echo "  Total Tokens: " . ($response->usage->inputTokens + $response->usage->outputTokens) . "\n\n";

// Convert to array (useful for logging/storage)
$responseArray = $response->toArray();
echo "As Array:\n";
print_r($responseArray);
```

## Testing with the SDK

The SDK is designed for testability:

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

## Middleware Pattern Implementation

Create reusable middleware for common concerns:

```php
<?php
# filename: examples/05-middleware-pattern.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
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
    echo "Response: " . $response->content[0]->text . "\n\n";
}

// Display metrics
echo "\n=== Metrics ===\n";
echo "Total requests: " . count(MetricsCollector::getMetrics()) . "\n";
echo "Average duration: " . number_format(MetricsCollector::getAverageDuration(), 3) . "s\n";
echo "\nDetailed metrics:\n";
print_r(MetricsCollector::getMetrics());
```

## Error Handling and Exceptions

The SDK provides specific exception types:

```php
<?php
# filename: examples/06-exception-handling.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Exceptions\ErrorException;
use Anthropic\Exceptions\RateLimitException;
use Anthropic\Exceptions\InvalidRequestException;
use Anthropic\Exceptions\AuthenticationException;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

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
        echo "Success: " . $response->content[0]->text . "\n";
    }

} catch (\Exception $e) {
    echo "Failed after retries: " . $e->getMessage() . "\n";
    error_log("Claude API error: " . $e->getMessage());
}
```

## SDK Best Practices

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
                'tokens_used' => $response->usage->outputTokens
            ]);

            return $response->content[0]->text;

        } catch (\Exception $e) {
            $this->logger->error('Content generation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

### 2. Configuration Management

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

### 3. Service Provider (Laravel Example)

```php
<?php
# filename: app/Providers/ClaudeServiceProvider.php
declare(strict_types=1);

namespace App\Providers;

use Anthropic\Anthropic;
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

## Troubleshooting

**SDK not found after installation?**
- Run `composer dump-autoload`
- Verify `anthropic-ai/sdk` is in `composer.json` and `vendor/` directory
- Check minimum PHP version (8.2+)

**Type errors with response objects?**
- Ensure strict types are enabled (`declare(strict_types=1)`)
- Check you're using the correct response object properties
- Review the SDK's type definitions in `vendor/anthropic-ai/sdk/src/Responses/`

**Custom HTTP client not working?**
- Ensure your client implements `Psr\Http\Client\ClientInterface`
- Verify PSR-7 message implementations are compatible
- Check middleware is properly configured in handler stack

**Streaming not working?**
- Ensure you're using `createStreamed()` instead of `create()`
- Verify your HTTP client supports streaming
- Check timeout settings allow for long-running connections

## Key Takeaways

- ✓ The SDK uses modern PHP patterns (factory, dependency injection, PSR standards)
- ✓ Customize behavior via factory methods, middleware, and custom transports
- ✓ Response objects are strongly typed for IDE support and type safety
- ✓ Middleware enables logging, metrics, rate limiting, and retry logic
- ✓ The SDK is designed for testability with mockable interfaces
- ✓ Use dependency injection for flexible, maintainable code
- ✓ Configuration management keeps credentials and settings separate

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
