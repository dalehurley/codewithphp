---
title: "05: Error Handling & Resilience"
description: "Build fault-tolerant OpenAI applications with comprehensive error handling, retry strategies, and resilience patterns"
series: "openai-php"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/04-http-clients-api-integration"
  - "Understanding of exceptions in PHP"
---

![Error Handling & Resilience](/images/openai-php/chapter-05-error-handling-hero-full.webp)

[Home](/series/openai-php) > [Chapter 04](/series/openai-php/chapters/04-http-clients-api-integration) > Error Handling & Resilience

# Chapter 05: Error Handling & Resilience

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">45-55 minutes</span>

## Overview

APIs fail. Networks drop. Rate limits hit. Building production-ready applications means expecting failure and handling it gracefully. The difference between a fragile proof-of-concept and a robust production system is comprehensive error handling.

OpenAI's API can return various error types: authentication failures, rate limiting, invalid requests, model errors, and network timeouts. Each requires different handling strategies. In this chapter, you'll learn to identify, categorize, and respond to every error type appropriately.

You'll implement retry logic with exponential backoff, circuit breakers to prevent cascading failures, graceful degradation strategies, and comprehensive logging. By the end, your applications will handle errors smoothly and recover automatically when possible.

## What You'll Learn

- 🚨 **Error Types**: Understand all OpenAI error codes and their meanings
- 🔄 **Retry Strategies**: Implement intelligent retry logic with backoff
- 🔌 **Circuit Breakers**: Prevent cascading failures
- 📊 **Error Logging**: Track and analyze failures effectively
- 🛡️ **Graceful Degradation**: Maintain service during outages
- ⚡ **Timeout Management**: Handle slow responses appropriately
- 🧪 **Testing Failures**: Simulate and test error scenarios

## Prerequisites

- ✅ Completed Chapters 01-04
- ✅ Understanding of PHP exceptions
- ✅ Basic knowledge of HTTP status codes
- ✅ Familiarity with logging concepts

---

## OpenAI Error Types

### HTTP Status Codes

```php
<?php

class OpenAIErrorCodes
{
    public const INVALID_REQUEST = 400;      // Bad request
    public const AUTHENTICATION_FAILED = 401; // Invalid API key
    public const PERMISSION_DENIED = 403;     // Insufficient permissions
    public const NOT_FOUND = 404;             // Resource not found
    public const RATE_LIMIT_EXCEEDED = 429;   // Too many requests
    public const SERVER_ERROR = 500;          // OpenAI server issue
    public const SERVICE_UNAVAILABLE = 503;   // Temporary outage

    public static function isRetryable(int $code): bool
    {
        return in_array($code, [
            self::RATE_LIMIT_EXCEEDED,
            self::SERVER_ERROR,
            self::SERVICE_UNAVAILABLE,
        ]);
    }

    public static function getMessage(int $code): string
    {
        return match($code) {
            400 => 'Invalid request parameters',
            401 => 'Authentication failed - check API key',
            403 => 'Permission denied',
            404 => 'Resource not found',
            429 => 'Rate limit exceeded',
            500 => 'OpenAI server error',
            503 => 'Service temporarily unavailable',
            default => 'Unknown error',
        };
    }
}
```

### Error Response Structure

```json
{
  "error": {
    "message": "You exceeded your current quota...",
    "type": "insufficient_quota",
    "param": null,
    "code": "insufficient_quota"
  }
}
```

### Custom Exception Hierarchy

```php
<?php

namespace App\Exceptions;

class OpenAIException extends \Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?string $type = null,
        public readonly ?string $param = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromResponse(array $error, int $httpCode): self
    {
        return match($httpCode) {
            401 => new AuthenticationException(
                $error['message'] ?? 'Authentication failed',
                $httpCode,
                $error['type'] ?? null,
                $error['param'] ?? null
            ),
            429 => new RateLimitException(
                $error['message'] ?? 'Rate limit exceeded',
                $httpCode,
                $error['type'] ?? null,
                $error['param'] ?? null
            ),
            500, 503 => new ServerException(
                $error['message'] ?? 'Server error',
                $httpCode,
                $error['type'] ?? null,
                $error['param'] ?? null
            ),
            default => new self(
                $error['message'] ?? 'Unknown error',
                $httpCode,
                $error['type'] ?? null,
                $error['param'] ?? null
            ),
        };
    }

    public function isRetryable(): bool
    {
        return OpenAIErrorCodes::isRetryable($this->getCode());
    }
}

class AuthenticationException extends OpenAIException {}
class RateLimitException extends OpenAIException {}
class ServerException extends OpenAIException {}
class InvalidRequestException extends OpenAIException {}
```

---

## Retry Strategies

### Exponential Backoff

```php
<?php

class RetryStrategy
{
    public function __construct(
        private int $maxRetries = 3,
        private int $baseDelay = 1000, // milliseconds
        private int $maxDelay = 60000,
        private float $multiplier = 2.0,
        private float $jitter = 0.1
    ) {}

    public function execute(callable $operation): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                return $operation();

            } catch (OpenAIException $e) {
                $lastException = $e;
                $attempt++;

                if (!$e->isRetryable() || $attempt >= $this->maxRetries) {
                    throw $e;
                }

                $delay = $this->calculateDelay($attempt);
                $this->log($attempt, $delay, $e);
                usleep($delay * 1000);
            }
        }

        throw $lastException;
    }

    private function calculateDelay(int $attempt): int
    {
        // Exponential backoff: baseDelay * (multiplier ^ attempt)
        $delay = $this->baseDelay * pow($this->multiplier, $attempt - 1);

        // Apply max delay cap
        $delay = min($delay, $this->maxDelay);

        // Add jitter to prevent thundering herd
        $jitterRange = $delay * $this->jitter;
        $jitter = mt_rand(-$jitterRange, $jitterRange);

        return (int) max($delay + $jitter, 0);
    }

    private function log(int $attempt, int $delay, OpenAIException $e): void
    {
        error_log(sprintf(
            "[Retry] Attempt %d failed: %s. Waiting %dms before retry.",
            $attempt,
            $e->getMessage(),
            $delay
        ));
    }
}

// Usage
$retry = new RetryStrategy(maxRetries: 3);

$response = $retry->execute(function() use ($client) {
    return $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ]);
});
```

### Advanced Retry with Rate Limit Headers

```php
<?php

class RateLimitAwareRetry
{
    public function execute(callable $operation): mixed
    {
        $attempt = 0;
        $maxAttempts = 5;

        while ($attempt < $maxAttempts) {
            try {
                return $operation();

            } catch (RateLimitException $e) {
                $attempt++;

                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                // Extract retry-after from exception or use default
                $retryAfter = $this->getRetryAfter($e);

                error_log(sprintf(
                    "[RateLimit] Waiting %d seconds before retry (attempt %d/%d)",
                    $retryAfter,
                    $attempt,
                    $maxAttempts
                ));

                sleep($retryAfter);
            }
        }
    }

    private function getRetryAfter(RateLimitException $e): int
    {
        // Try to parse from error message
        // "Please retry after 20 seconds"
        if (preg_match('/retry after (\d+) seconds?/i', $e->getMessage(), $matches)) {
            return (int) $matches[1];
        }

        // Default exponential backoff
        return min(pow(2, $attempt), 60);
    }
}
```

---

## Circuit Breaker Pattern

```php
<?php

/**
 * Circuit Breaker prevents repeated calls to failing services
 */

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';     // Normal operation
    private const STATE_OPEN = 'open';         // Blocking requests
    private const STATE_HALF_OPEN = 'half_open'; // Testing recovery

    private string $state = self::STATE_CLOSED;
    private int $failureCount = 0;
    private int $lastFailureTime = 0;

    public function __construct(
        private int $failureThreshold = 5,
        private int $timeout = 60,  // seconds
        private int $successThreshold = 2
    ) {}

    public function call(callable $operation): mixed
    {
        if ($this->state === self::STATE_OPEN) {
            if (time() - $this->lastFailureTime >= $this->timeout) {
                // Timeout elapsed, try half-open
                $this->state = self::STATE_HALF_OPEN;
                error_log("[CircuitBreaker] Entering HALF_OPEN state");
            } else {
                throw new \RuntimeException("Circuit breaker is OPEN");
            }
        }

        try {
            $result = $operation();

            // Success - reset or close circuit
            if ($this->state === self::STATE_HALF_OPEN) {
                $this->reset();
                error_log("[CircuitBreaker] Entering CLOSED state");
            }

            return $result;

        } catch (\Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function recordFailure(): void
    {
        $this->failureCount++;
        $this->lastFailureTime = time();

        if ($this->failureCount >= $this->failureThreshold) {
            $this->state = self::STATE_OPEN;
            error_log(sprintf(
                "[CircuitBreaker] Entering OPEN state after %d failures",
                $this->failureCount
            ));
        }
    }

    private function reset(): void
    {
        $this->state = self::STATE_CLOSED;
        $this->failureCount = 0;
        $this->lastFailureTime = 0;
    }

    public function getState(): string
    {
        return $this->state;
    }
}

// Usage
$breaker = new CircuitBreaker(
    failureThreshold: 5,
    timeout: 60
);

try {
    $response = $breaker->call(function() use ($client) {
        return $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => 'Hello']],
        ]);
    });
} catch (\RuntimeException $e) {
    // Circuit is open, use fallback
    $response = $this->getFallbackResponse();
}
```

---

## Timeout Management

```php
<?php

/**
 * Comprehensive timeout handling
 */

class TimeoutHandler
{
    public function __construct(
        private int $connectionTimeout = 10,  // seconds
        private int $requestTimeout = 30,
        private int $streamTimeout = 120
    ) {}

    public function createClient(): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => $this->requestTimeout,
            'connect_timeout' => $this->connectionTimeout,
            'read_timeout' => $this->streamTimeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
            ],
        ]);
    }

    public function executeWithTimeout(callable $operation, ?int $timeout = null): mixed
    {
        $timeout = $timeout ?? $this->requestTimeout;

        // Set alarm for PHP timeout
        if (function_exists('pcntl_alarm')) {
            pcntl_alarm($timeout);
        }

        try {
            return $operation();
        } finally {
            if (function_exists('pcntl_alarm')) {
                pcntl_alarm(0); // Cancel alarm
            }
        }
    }
}
```

---

## Comprehensive Error Logging

```php
<?php

/**
 * Structured error logging for OpenAI errors
 */

class ErrorLogger
{
    private string $logFile;

    public function __construct(string $logFile = 'openai_errors.log')
    {
        $this->logFile = $logFile;
    }

    public function logError(
        \Throwable $exception,
        array $context = []
    ): void {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'error_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $this->formatTrace($exception->getTrace()),
            'context' => $context,
        ];

        if ($exception instanceof OpenAIException) {
            $entry['type'] = $exception->type;
            $entry['param'] = $exception->param;
            $entry['retryable'] = $exception->isRetryable();
        }

        file_put_contents(
            $this->logFile,
            json_encode($entry) . PHP_EOL,
            FILE_APPEND
        );

        // Also log to error_log
        error_log(sprintf(
            "[OpenAI Error] %s: %s (Code: %d)",
            get_class($exception),
            $exception->getMessage(),
            $exception->getCode()
        ));
    }

    private function formatTrace(array $trace): array
    {
        return array_slice(
            array_map(fn($t) => ($t['file'] ?? 'unknown') . ':' . ($t['line'] ?? '?'), $trace),
            0,
            5
        );
    }

    public function getRecentErrors(int $limit = 100): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -$limit);

        return array_map(fn($line) => json_decode($line, true), $lines);
    }

    public function getErrorStats(): array
    {
        $errors = $this->getRecentErrors(1000);

        $stats = [
            'total' => count($errors),
            'by_class' => [],
            'by_code' => [],
            'retryable' => 0,
        ];

        foreach ($errors as $error) {
            $class = $error['error_class'];
            $code = $error['code'];

            $stats['by_class'][$class] = ($stats['by_class'][$class] ?? 0) + 1;
            $stats['by_code'][$code] = ($stats['by_code'][$code] ?? 0) + 1;

            if ($error['retryable'] ?? false) {
                $stats['retryable']++;
            }
        }

        return $stats;
    }
}
```

---

## Graceful Degradation

```php
<?php

/**
 * Fallback strategies when OpenAI is unavailable
 */

class ResilientOpenAI
{
    private CircuitBreaker $breaker;
    private RetryStrategy $retry;
    private ErrorLogger $logger;

    public function __construct(
        private \OpenAI\Client $client,
        private ?callable $fallback = null
    ) {
        $this->breaker = new CircuitBreaker();
        $this->retry = new RetryStrategy();
        $this->logger = new ErrorLogger();
    }

    public function chat(array $messages, array $options = []): array
    {
        try {
            return $this->breaker->call(function() use ($messages, $options) {
                return $this->retry->execute(function() use ($messages, $options) {
                    $response = $this->client->chat()->create(array_merge([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => $messages,
                    ], $options));

                    return $this->formatResponse($response);
                });
            });

        } catch (\Exception $e) {
            $this->logger->logError($e, [
                'messages' => $messages,
                'options' => $options,
            ]);

            if ($this->fallback) {
                return ($this->fallback)($messages, $e);
            }

            throw $e;
        }
    }

    private function formatResponse($response): array
    {
        return [
            'content' => $response->choices[0]->message->content,
            'model' => $response->model,
            'usage' => [
                'prompt_tokens' => $response->usage->promptTokens,
                'completion_tokens' => $response->usage->completionTokens,
                'total_tokens' => $response->usage->totalTokens,
            ],
        ];
    }
}

// Usage with fallback
$resilient = new ResilientOpenAI(
    $client,
    fallback: function($messages, $exception) {
        error_log("Using fallback: " . $exception->getMessage());

        return [
            'content' => "I'm temporarily unable to process your request. Please try again later.",
            'model' => 'fallback',
            'usage' => ['total_tokens' => 0],
        ];
    }
);

$response = $resilient->chat([
    ['role' => 'user', 'content' => 'Hello']
]);
```

---

## Testing Error Scenarios

```php
<?php

/**
 * Mock client for testing error handling
 */

class MockOpenAIClient
{
    private array $responses = [];
    private int $callCount = 0;

    public function addResponse($response): void
    {
        $this->responses[] = $response;
    }

    public function addError(int $code, string $message): void
    {
        $this->responses[] = new OpenAIException($message, $code);
    }

    public function chat(): self
    {
        return $this;
    }

    public function create(array $params)
    {
        $response = $this->responses[$this->callCount] ?? null;
        $this->callCount++;

        if ($response instanceof \Exception) {
            throw $response;
        }

        return $response;
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }
}

// Test retry logic
$mock = new MockOpenAIClient();
$mock->addError(429, "Rate limit exceeded");
$mock->addError(500, "Server error");
$mock->addResponse((object)[
    'choices' => [(object)['message' => (object)['content' => 'Success']]],
    'usage' => (object)['totalTokens' => 10],
]);

$resilient = new ResilientOpenAI($mock);

try {
    $response = $resilient->chat([['role' => 'user', 'content' => 'Test']]);
    echo "Success after retries\n";
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
```

---

## Exercises

### Exercise 1: Custom Retry Logic

Create retry logic that:
1. Uses different strategies per error type
2. Respects Retry-After headers
3. Logs each attempt
4. Has configurable max attempts per error type

### Exercise 2: Health Check System

Build a system that:
1. Periodically checks OpenAI API health
2. Adjusts circuit breaker thresholds
3. Sends alerts when unhealthy
4. Tracks uptime percentage

### Exercise 3: Error Recovery Dashboard

Create a dashboard showing:
1. Error rate over time
2. Most common errors
3. Retry success rate
4. Circuit breaker state history

### Exercise 4: Intelligent Fallback

Implement fallback that:
1. Uses cached responses when available
2. Degrades to simpler model (GPT-3.5 if GPT-4 fails)
3. Queues requests for later retry
4. Notifies users of degraded service

---

## Key Takeaways

- ✅ Always expect and handle API failures gracefully
- ✅ Use exponential backoff with jitter for retries
- ✅ Implement circuit breakers to prevent cascading failures
- ✅ Log errors comprehensively for debugging and monitoring
- ✅ Respect rate limit headers and Retry-After directives
- ✅ Provide fallback responses when possible
- ✅ Test error scenarios thoroughly
- ✅ Different errors require different handling strategies

---

## Next Steps

With robust error handling in place, you're ready to dive deep into token management!

👉 **[Chapter 06: Working with Tokens](/series/openai-php/chapters/06-working-with-tokens)**

In the next chapter, you'll learn:
- How tokenization works
- Accurate token counting
- Context window management
- Cost optimization through token strategies

---

[← Previous: Chapter 04](/series/openai-php/chapters/04-http-clients-api-integration) | [Next: Chapter 06 →](/series/openai-php/chapters/06-working-with-tokens)
