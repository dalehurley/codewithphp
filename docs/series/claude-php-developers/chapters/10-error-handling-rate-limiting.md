---
title: "10: Error Handling and Rate Limiting"
description: "Build resilient Claude applications with exponential backoff, circuit breakers, graceful degradation, and intelligent retry logic for production systems."
series: "claude-php-developers"
chapter: 10
order: 10
difficulty: "Expert"
prerequisites:
  - "Chapter 00-09 completed"
  - "Understanding of HTTP status codes"
  - "Familiarity with exception handling in PHP"
---

![10: Error Handling and Rate Limiting](/images/claude-php/chapter-10-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 10</span>
</div>

# Chapter 10: Error Handling and Rate Limiting

## Overview

Production Claude applications face inevitable challenges: API errors, rate limits, network failures, and service degradation. The difference between a fragile prototype and a robust production system is comprehensive error handling and intelligent retry logic.

This chapter teaches you to handle all Claude API error types, implement exponential backoff for retries, build circuit breakers to prevent cascading failures, manage rate limits effectively, create fallback strategies for degraded service, and monitor error patterns for system health.

By the end, you'll build production-grade error handling systems that keep your application running smoothly even when things go wrong.

## Prerequisites

Before starting, ensure you understand:

- ✓ Basic Claude API usage (Chapters 00-03)
- ✓ Exception handling in PHP
- ✓ HTTP status codes and error responses
- ✓ Basic understanding of distributed systems

**Estimated Time**: 45-60 minutes

## What You'll Build

By the end of this chapter, you will have created:

- An `ErrorParser` class that categorizes Claude API errors and determines retry strategies
- An `ExponentialBackoff` retry mechanism with configurable delays and jitter
- An `AdaptiveBackoff` system that adjusts parameters based on error patterns
- A `CircuitBreaker` implementation with three states (closed, open, half-open) to prevent cascading failures
- A `RateLimiter` class that enforces request limits using sliding window algorithms
- A `TokenBucketRateLimiter` for smoother rate limiting with token-based consumption
- A complete `ResilientClaudeClient` that combines all protection mechanisms
- A `FallbackStrategy` system with multiple degradation layers (cache, simpler models, defaults, queuing)
- An `ErrorMonitor` for tracking error patterns and triggering alerts
- HTTP client configuration with proper timeout handling and request cancellation
- An `IdempotentRequest` system to prevent duplicate processing during retries
- Streaming error handling for mid-stream failures and partial responses
- Payload size error handling with automatic request reduction
- Health check and readiness probe systems
- Priority-based error handling for different request types
- Error budget tracking to monitor SLO compliance
- Comprehensive test suites for error handling scenarios (unit and integration tests)
- Production-ready error handling systems that keep applications running during API failures

## Objectives

By the end of this chapter, you will:

- Understand all Claude API error types and their HTTP status codes
- Distinguish between retryable and non-retryable errors
- Implement exponential backoff with jitter for intelligent retry logic
- Build circuit breakers to prevent cascading failures in distributed systems
- Apply rate limiting strategies to respect API quotas and prevent throttling
- Create graceful degradation systems with multiple fallback layers
- Monitor error patterns and implement alerting for production systems
- Configure HTTP client timeouts and handle request cancellation
- Implement idempotency keys to prevent duplicate processing during retries
- Handle streaming response errors and recover from partial responses
- Manage payload size errors with automatic request reduction strategies
- Implement health checks and readiness probes for API availability
- Apply priority-based error handling for different request criticality levels
- Track error budgets and monitor service level objectives (SLOs)
- Build a complete resilient client wrapper that handles all error scenarios
- Test error handling with unit tests and integration tests against real API errors

## Understanding Claude API Errors

::: info Related Chapter

For scaling error handling patterns across multiple servers and high-traffic scenarios, see [Chapter 38: Scaling Applications](/series/claude-php-developers/chapters/38-scaling-applications) which covers distributed circuit breakers, queue-based retries, and concurrency management.

:::

### Error Types and HTTP Status Codes

```php
<?php
# filename: examples/01-error-types.php
declare(strict_types=1);

/**
 * Claude API Error Reference
 *
 * The API uses standard HTTP status codes with detailed error responses
 */

class ClaudeErrorReference
{
    public const ERROR_TYPES = [
        // ClaudePhp Errors (4xx)
        400 => [
            'type' => 'invalid_request_error',
            'description' => 'Invalid request format or parameters',
            'retry' => false,
            'examples' => [
                'Missing required parameter',
                'Invalid model name',
                'Malformed JSON',
            ]
        ],
        401 => [
            'type' => 'authentication_error',
            'description' => 'Invalid or missing API key',
            'retry' => false,
            'examples' => [
                'Invalid API key',
                'API key not provided',
                'Expired API key',
            ]
        ],
        403 => [
            'type' => 'permission_error',
            'description' => 'Insufficient permissions',
            'retry' => false,
            'examples' => [
                'Account not activated',
                'Feature not available on current plan',
                'Geographic restrictions',
            ]
        ],
        404 => [
            'type' => 'not_found_error',
            'description' => 'Resource not found',
            'retry' => false,
            'examples' => [
                'Invalid endpoint',
                'Model not found',
            ]
        ],
        429 => [
            'type' => 'rate_limit_error',
            'description' => 'Rate limit exceeded',
            'retry' => true,
            'examples' => [
                'Requests per minute exceeded',
                'Tokens per day exceeded',
                'Concurrent requests exceeded',
            ]
        ],

        // Server Errors (5xx)
        500 => [
            'type' => 'api_error',
            'description' => 'Internal server error',
            'retry' => true,
            'examples' => [
                'Unexpected API error',
                'Service temporarily unavailable',
            ]
        ],
        503 => [
            'type' => 'overloaded_error',
            'description' => 'Service overloaded',
            'retry' => true,
            'examples' => [
                'High traffic',
                'Model temporarily unavailable',
            ]
        ],
        529 => [
            'type' => 'overloaded_error',
            'description' => 'Service overloaded (alternative code)',
            'retry' => true,
            'examples' => [
                'Extreme load',
            ]
        ],
    ];

    public static function shouldRetry(int $statusCode): bool
    {
        return self::ERROR_TYPES[$statusCode]['retry'] ?? false;
    }

    public static function getDescription(int $statusCode): string
    {
        return self::ERROR_TYPES[$statusCode]['description'] ?? 'Unknown error';
    }

    public static function getType(int $statusCode): string
    {
        return self::ERROR_TYPES[$statusCode]['type'] ?? 'unknown_error';
    }
}

// Display error reference
echo "Claude API Error Reference:\n\n";

foreach (ClaudeErrorReference::ERROR_TYPES as $code => $info) {
    echo "HTTP {$code} - {$info['type']}\n";
    echo "  Description: {$info['description']}\n";
    echo "  Retry: " . ($info['retry'] ? 'Yes' : 'No') . "\n";
    echo "  Examples:\n";
    foreach ($info['examples'] as $example) {
        echo "    - {$example}\n";
    }
    echo "\n";
}
```

### Parsing Error Responses

```php
<?php
# filename: src/ErrorParser.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\Exceptions\AnthropicException;
use ClaudePhp\Exceptions\RateLimitError;
use ClaudePhp\Exceptions\AuthenticationError;

/**
 * ErrorParser - Parses Claude API errors and determines retry strategies
 *
 * Works with Claude-PHP-SDK exceptions and determines appropriate retry strategies
 */
class ErrorParser
{
    /**
     * Parse Claude API error response
     */
    public static function parse(\Throwable $exception): array
    {
        $errorData = [
            'type' => 'unknown_error',
            'message' => $exception->getMessage(),
            'status_code' => null,
            'retryable' => false,
            'retry_after' => null,
        ];

        // Parse Claude-PHP-SDK specific exceptions
        if ($exception instanceof RateLimitException) {
            $errorData['type'] = 'rate_limit_error';
            $errorData['status_code'] = 429;
            $errorData['retryable'] = true;
            // Extract retry-after if available in message
            if (preg_match('/retry[- ]after[:\s]+(\d+)/i', $exception->getMessage(), $matches)) {
                $errorData['retry_after'] = (int) $matches[1];
            }
        } elseif ($exception instanceof AuthenticationException) {
            $errorData['type'] = 'authentication_error';
            $errorData['status_code'] = 401;
            $errorData['retryable'] = false;
        } elseif ($exception instanceof ClaudeException) {
            $errorData['type'] = 'api_error';
            $errorData['status_code'] = $exception->getCode() ?: 500;
            $errorData['retryable'] = self::isRetryableStatusCode($errorData['status_code']);
        }

        return $errorData;
    }

    /**
     * Determine if error is transient (should retry)
     */
    public static function isTransient(\Throwable $exception): bool
    {
        // Rate limit errors are always retryable
        if ($exception instanceof RateLimitException) {
            return true;
        }

        // Authentication errors are not retryable
        if ($exception instanceof AuthenticationException) {
            return false;
        }

        $errorData = self::parse($exception);
        return $errorData['retryable'];
    }

    /**
     * Get recommended wait time before retry
     */
    public static function getRetryWait(\Throwable $exception): int
    {
        $errorData = self::parse($exception);

        // Use retry-after if provided
        if ($errorData['retry_after']) {
            return $errorData['retry_after'];
        }

        // Default wait times by error type
        return match($errorData['status_code']) {
            429 => 60,      // Rate limit: wait 60 seconds
            503, 529 => 30, // Overload: wait 30 seconds
            500 => 5,       // Server error: wait 5 seconds
            default => 1,   // Other: wait 1 second
        };
    }

    /**
     * Check if status code is retryable
     */
    private static function isRetryableStatusCode(int $statusCode): bool
    {
        return in_array($statusCode, [429, 500, 503, 529], true);
    }
}
```

## Exponential Backoff Implementation

### Basic Exponential Backoff

```php
<?php
# filename: src/ExponentialBackoff.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class ExponentialBackoff
{
    public function __construct(
        private int $maxRetries = 5,
        private int $baseDelayMs = 1000,
        private int $maxDelayMs = 60000,
        private float $multiplier = 2.0,
        private float $jitter = 0.1
    ) {}

    /**
     * Execute callable with exponential backoff
     */
    public function execute(callable $operation): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                return $operation();

            } catch (\Throwable $e) {
                $lastException = $e;

                // Don't retry if not transient
                if (!ErrorParser::isTransient($e)) {
                    throw $e;
                }

                $attempt++;

                // Calculate delay with exponential backoff
                $delay = $this->calculateDelay($attempt);

                // Log retry attempt
                error_log(sprintf(
                    "Retry attempt %d/%d after %dms. Error: %s",
                    $attempt,
                    $this->maxRetries,
                    $delay,
                    $e->getMessage()
                ));

                // Wait before retry
                usleep($delay * 1000); // Convert ms to microseconds
            }
        }

        // All retries exhausted
        throw new \RuntimeException(
            "Max retries ({$this->maxRetries}) exceeded",
            0,
            $lastException
        );
    }

    /**
     * Calculate delay for attempt with exponential backoff and jitter
     */
    private function calculateDelay(int $attempt): int
    {
        // Base exponential backoff: baseDelay * multiplier^(attempt-1)
        $delay = $this->baseDelayMs * pow($this->multiplier, $attempt - 1);

        // Cap at max delay
        $delay = min($delay, $this->maxDelayMs);

        // Add jitter (randomness) to prevent thundering herd
        $jitterAmount = $delay * $this->jitter;
        $jitter = mt_rand(
            (int) -$jitterAmount,
            (int) $jitterAmount
        );
        $delay += $jitter;

        return max((int) $delay, $this->baseDelayMs);
    }

    /**
     * Get delay sequence for debugging
     */
    public function getDelaySequence(): array
    {
        $sequence = [];
        for ($i = 1; $i <= $this->maxRetries; $i++) {
            $sequence[] = $this->calculateDelay($i);
        }
        return $sequence;
    }
}

// Usage example
$backoff = new ExponentialBackoff(
    maxRetries: 5,
    baseDelayMs: 1000,
    maxDelayMs: 30000,
    multiplier: 2.0
);

echo "Exponential backoff delay sequence:\n";
$delays = $backoff->getDelaySequence();
foreach ($delays as $attempt => $delay) {
    echo "Attempt " . ($attempt + 1) . ": " . number_format($delay) . "ms\n";
}

// Execute with retry using Claude-PHP-SDK
try {
    $result = $backoff->execute(function() use ($client) {
        return $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => 'Hello, Claude!'
            ]]
        ]);
    });

    echo "\nSuccess: " . $result['content'][0]['text'] . "\n";

} catch (\RuntimeException $e) {
    echo "\nFailed after all retries: " . $e->getMessage() . "\n";
}
```

### Adaptive Backoff Strategy

```php
<?php
# filename: src/AdaptiveBackoff.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class AdaptiveBackoff extends ExponentialBackoff
{
    private array $recentErrors = [];
    private int $errorWindowSeconds = 300; // 5 minutes

    public function execute(callable $operation): mixed
    {
        // Adjust parameters based on recent error rate
        $this->adjustParameters();

        return parent::execute($operation);
    }

    /**
     * Adjust backoff parameters based on error patterns
     */
    private function adjustParameters(): void
    {
        $this->cleanOldErrors();

        $errorCount = count($this->recentErrors);

        if ($errorCount > 10) {
            // High error rate: more aggressive backoff
            $this->baseDelayMs = 5000;  // 5 seconds
            $this->multiplier = 3.0;
            $this->maxRetries = 3;

        } elseif ($errorCount > 5) {
            // Moderate error rate: standard backoff
            $this->baseDelayMs = 2000;  // 2 seconds
            $this->multiplier = 2.5;
            $this->maxRetries = 4;

        } else {
            // Low error rate: gentle backoff
            $this->baseDelayMs = 1000;  // 1 second
            $this->multiplier = 2.0;
            $this->maxRetries = 5;
        }
    }

    /**
     * Track error occurrence
     */
    protected function trackError(\Throwable $e): void
    {
        $this->recentErrors[] = time();
        $this->cleanOldErrors();
    }

    /**
     * Remove errors outside the tracking window
     */
    private function cleanOldErrors(): void
    {
        $cutoff = time() - $this->errorWindowSeconds;
        $this->recentErrors = array_filter(
            $this->recentErrors,
            fn($timestamp) => $timestamp > $cutoff
        );
    }

    /**
     * Get current error rate
     */
    public function getErrorRate(): float
    {
        $this->cleanOldErrors();
        return count($this->recentErrors) / ($this->errorWindowSeconds / 60);
    }
}
```

## Circuit Breaker Pattern

::: tip Distributed Circuit Breakers

In multi-instance deployments, circuit breaker state should be shared across instances using Redis or a database. Otherwise, each instance maintains its own circuit state, which can lead to inconsistent behavior. Consider using a distributed circuit breaker implementation for production systems with multiple servers.

:::

### Circuit Breaker Implementation

```php
<?php
# filename: src/CircuitBreaker.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

enum CircuitState: string
{
    case CLOSED = 'closed';      // Normal operation
    case OPEN = 'open';          // Blocking requests
    case HALF_OPEN = 'half_open'; // Testing recovery
}

class CircuitBreaker
{
    private CircuitState $state = CircuitState::CLOSED;
    private int $failureCount = 0;
    private int $successCount = 0;
    private ?int $lastFailureTime = null;
    private ?int $openedAt = null;

    public function __construct(
        private int $failureThreshold = 5,      // Failures before opening
        private int $successThreshold = 2,      // Successes to close from half-open
        private int $timeout = 60,              // Seconds to wait before half-open
        private ?string $name = null
    ) {}

    /**
     * Execute operation through circuit breaker
     */
    public function execute(callable $operation): mixed
    {
        // Check circuit state
        $this->updateState();

        if ($this->state === CircuitState::OPEN) {
            throw new \RuntimeException(
                "Circuit breaker '{$this->name}' is OPEN. Service temporarily unavailable."
            );
        }

        try {
            $result = $operation();

            // Success
            $this->onSuccess();

            return $result;

        } catch (\Throwable $e) {
            // Failure
            $this->onFailure($e);

            throw $e;
        }
    }

    /**
     * Update circuit state based on time and thresholds
     */
    private function updateState(): void
    {
        if ($this->state === CircuitState::OPEN) {
            // Check if timeout has elapsed
            if (time() - $this->openedAt >= $this->timeout) {
                $this->transitionTo(CircuitState::HALF_OPEN);
                error_log("Circuit breaker '{$this->name}' transitioned to HALF_OPEN");
            }
        }
    }

    /**
     * Handle successful operation
     */
    private function onSuccess(): void
    {
        $this->failureCount = 0;

        if ($this->state === CircuitState::HALF_OPEN) {
            $this->successCount++;

            if ($this->successCount >= $this->successThreshold) {
                $this->transitionTo(CircuitState::CLOSED);
                error_log("Circuit breaker '{$this->name}' CLOSED after successful recovery");
            }
        }
    }

    /**
     * Handle failed operation
     */
    private function onFailure(\Throwable $e): void
    {
        $this->lastFailureTime = time();
        $this->failureCount++;
        $this->successCount = 0;

        error_log(sprintf(
            "Circuit breaker '{$this->name}' failure #%d: %s",
            $this->failureCount,
            $e->getMessage()
        ));

        if ($this->failureCount >= $this->failureThreshold) {
            $this->transitionTo(CircuitState::OPEN);
            error_log("Circuit breaker '{$this->name}' OPENED due to repeated failures");
        }
    }

    /**
     * Transition to new state
     */
    private function transitionTo(CircuitState $newState): void
    {
        $oldState = $this->state;
        $this->state = $newState;

        if ($newState === CircuitState::OPEN) {
            $this->openedAt = time();
        }

        if ($newState === CircuitState::CLOSED) {
            $this->failureCount = 0;
            $this->successCount = 0;
            $this->openedAt = null;
        }
    }

    /**
     * Get current circuit state
     */
    public function getState(): CircuitState
    {
        $this->updateState();
        return $this->state;
    }

    /**
     * Get circuit statistics
     */
    public function getStats(): array
    {
        return [
            'state' => $this->state->value,
            'failure_count' => $this->failureCount,
            'success_count' => $this->successCount,
            'last_failure_time' => $this->lastFailureTime,
            'opened_at' => $this->openedAt,
        ];
    }

    /**
     * Manually reset circuit
     */
    public function reset(): void
    {
        $this->transitionTo(CircuitState::CLOSED);
        error_log("Circuit breaker '{$this->name}' manually reset");
    }
}

// Usage
$circuitBreaker = new CircuitBreaker(
    failureThreshold: 5,
    successThreshold: 2,
    timeout: 60,
    name: 'claude-api'
);

try {
    $result = $circuitBreaker->execute(function() use ($client) {
        return $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => 'Hello!'
            ]]
        ]);
    });

    echo "Success: " . $result['content'][0]['text'] . "\n";

} catch (\RuntimeException $e) {
    echo "Circuit breaker error: " . $e->getMessage() . "\n";

    $stats = $circuitBreaker->getStats();
    echo "Circuit state: {$stats['state']}\n";
}
```

## Rate Limiting Strategies

::: tip Distributed Rate Limiting

For applications running multiple instances (load-balanced servers), use distributed rate limiting with Redis or a database. The in-memory `RateLimiter` shown here only works for single-instance applications. For multi-instance deployments, coordinate rate limits across instances to prevent exceeding API quotas.

:::

### Rate Limiter Implementation

```php
<?php
# filename: src/RateLimiter.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class RateLimiter
{
    private array $requests = [];

    public function __construct(
        private int $maxRequests,
        private int $windowSeconds,
        private ?string $identifier = null
    ) {}

    /**
     * Check if request is allowed
     */
    public function allow(): bool
    {
        $this->cleanup();

        $currentCount = count($this->requests);

        if ($currentCount >= $this->maxRequests) {
            return false;
        }

        $this->requests[] = time();
        return true;
    }

    /**
     * Execute operation with rate limiting
     */
    public function execute(callable $operation): mixed
    {
        if (!$this->allow()) {
            $retryAfter = $this->getRetryAfter();

            throw new \RuntimeException(
                "Rate limit exceeded. Retry after {$retryAfter} seconds."
            );
        }

        return $operation();
    }

    /**
     * Get seconds until next request is allowed
     */
    public function getRetryAfter(): int
    {
        $this->cleanup();

        if (empty($this->requests)) {
            return 0;
        }

        $oldestRequest = min($this->requests);
        $windowEnd = $oldestRequest + $this->windowSeconds;

        return max(0, $windowEnd - time());
    }

    /**
     * Remove old requests outside the window
     */
    private function cleanup(): void
    {
        $cutoff = time() - $this->windowSeconds;

        $this->requests = array_filter(
            $this->requests,
            fn($timestamp) => $timestamp > $cutoff
        );
    }

    /**
     * Get current usage statistics
     */
    public function getStats(): array
    {
        $this->cleanup();

        return [
            'current_requests' => count($this->requests),
            'max_requests' => $this->maxRequests,
            'window_seconds' => $this->windowSeconds,
            'requests_remaining' => max(0, $this->maxRequests - count($this->requests)),
            'retry_after' => $this->getRetryAfter(),
        ];
    }

    /**
     * Reset rate limiter
     */
    public function reset(): void
    {
        $this->requests = [];
    }
}

// Usage
$rateLimiter = new RateLimiter(
    maxRequests: 50,        // 50 requests
    windowSeconds: 60,      // per minute
    identifier: 'claude-api'
);

try {
    $result = $rateLimiter->execute(function() use ($client) {
        return $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => 'Hello!'
            ]]
        ]);
    });

    echo "Success\n";

    $stats = $rateLimiter->getStats();
    echo "Rate limit stats:\n";
    print_r($stats);

} catch (\RuntimeException $e) {
    echo "Rate limit error: " . $e->getMessage() . "\n";
}
```

### Token Bucket Rate Limiter

```php
<?php
# filename: src/TokenBucketRateLimiter.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

/**
 * Token bucket algorithm for smoother rate limiting
 */
class TokenBucketRateLimiter
{
    private float $tokens;
    private int $lastRefill;

    public function __construct(
        private int $capacity,         // Max tokens
        private float $refillRate,     // Tokens per second
    ) {
        $this->tokens = $capacity;
        $this->lastRefill = time();
    }

    /**
     * Try to consume tokens
     */
    public function consume(float $tokens = 1.0): bool
    {
        $this->refill();

        if ($this->tokens >= $tokens) {
            $this->tokens -= $tokens;
            return true;
        }

        return false;
    }

    /**
     * Refill tokens based on elapsed time
     */
    private function refill(): void
    {
        $now = time();
        $elapsed = $now - $this->lastRefill;

        if ($elapsed > 0) {
            $tokensToAdd = $elapsed * $this->refillRate;
            $this->tokens = min($this->capacity, $this->tokens + $tokensToAdd);
            $this->lastRefill = $now;
        }
    }

    /**
     * Get seconds until tokens available
     */
    public function getWaitTime(float $tokensNeeded = 1.0): float
    {
        $this->refill();

        if ($this->tokens >= $tokensNeeded) {
            return 0;
        }

        $deficit = $tokensNeeded - $this->tokens;
        return $deficit / $this->refillRate;
    }

    /**
     * Execute operation with token bucket
     */
    public function execute(callable $operation, float $cost = 1.0): mixed
    {
        $waitTime = $this->getWaitTime($cost);

        if ($waitTime > 0) {
            usleep((int) ($waitTime * 1_000_000));
        }

        while (!$this->consume($cost)) {
            usleep(100_000); // 100ms
        }

        return $operation();
    }

    /**
     * Get current bucket state
     */
    public function getStats(): array
    {
        $this->refill();

        return [
            'tokens_available' => round($this->tokens, 2),
            'capacity' => $this->capacity,
            'refill_rate' => $this->refillRate,
            'utilization' => round(($this->capacity - $this->tokens) / $this->capacity * 100, 2) . '%',
        ];
    }
}

// Usage
$bucket = new TokenBucketRateLimiter(
    capacity: 100,      // 100 tokens max
    refillRate: 10      // 10 tokens per second
);

// Different operations can cost different amounts
$result = $bucket->execute(
    operation: fn() => $client->messages()->create([...]),
    cost: 5.0  // This request costs 5 tokens
);
```

## Request Timeout and Cancellation

### Configuring HTTP ClaudePhp Timeouts

The Claude-PHP-SDK handles HTTP client configuration internally, but you can control timeouts through the client initialization:

```php
<?php
# filename: src/HttpClientConfig.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class HttpClientConfig
{
    /**
     * Create Claude client with appropriate configuration
     */
    public static function createClient(string $apiKey): ClaudePhp
    {
        // Claude-PHP-SDK initializes with sensible defaults
        $client = new ClaudePhp(apiKey: $apiKey);

        // Configure timeouts if needed (framework-dependent)
        // The SDK handles retries automatically for transient errors

        return $client;
    }

    /**
     * Create client with extended timeout for long-running requests
     */
    public static function createLongRunningClient(string $apiKey): ClaudePhp
    {
        // Claude-PHP-SDK manages timeouts internally
        // For long-running operations, use exponential backoff with higher max retries
        $client = new ClaudePhp(apiKey: $apiKey);

        // Consider wrapping with AdaptiveBackoff for long operations
        return $client;
    }
}

// Usage
$client = HttpClientConfig::createClient($apiKey);

// For requests that might take longer (e.g., large context windows)
$longClient = HttpClientConfig::createLongRunningClient($apiKey);
```

### Handling Timeout Errors

```php
<?php
# filename: examples/timeout-handling.php
declare(strict_types=1);

use ClaudePhp\Exceptions\AnthropicException;

try {
    $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ]);
} catch (ClaudeException $e) {
    // Handle Claude-specific errors
    if (str_contains($e->getMessage(), 'timeout')) {
        error_log("Request timeout: " . $e->getMessage());
        // Retry with exponential backoff
    } elseif (str_contains($e->getMessage(), 'connection')) {
        error_log("Connection error: " . $e->getMessage());
        // Retry with exponential backoff
    } else {
        error_log("Claude API error: " . $e->getMessage());
    }
} catch (\Exception $e) {
    // Handle other exceptions
    error_log("Unexpected error: " . $e->getMessage());
}
```

### Request Cancellation

For long-running requests, implement cancellation to free resources:

```php
<?php
# filename: src/CancellableRequest.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class CancellableRequest
{
    private bool $cancelled = false;
    private ?int $requestId = null;

    public function cancel(): void
    {
        $this->cancelled = true;
        error_log("Request {$this->requestId} cancelled");
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * Execute operation with cancellation support
     */
    public function execute(callable $operation): mixed
    {
        $this->requestId = uniqid('req_', true);

        if ($this->cancelled) {
            throw new \RuntimeException("Request {$this->requestId} was cancelled");
        }

        return $operation();
    }
}

// Usage with timeout
$cancellable = new CancellableRequest();

// Set timeout to cancel after 30 seconds
$timeout = new \React\Promise\Timer\TimeoutException("Request timeout");
$promise = $operation();
$promise->timeout(30)->then(
    fn($result) => $result,
    fn($error) => $cancellable->cancel()
);
```

## Idempotency and Request Deduplication

### Ensuring Idempotent Retries

When retrying requests, ensure they're idempotent to prevent duplicate processing:

```php
<?php
# filename: src/IdempotentRequest.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class IdempotentRequest
{
    /**
     * Generate idempotency key for request
     */
    public static function generateKey(array $request): string
    {
        // Create deterministic key from request content
        $keyData = [
            'model' => $request['model'] ?? '',
            'messages' => $request['messages'] ?? [],
            'max_tokens' => $request['max_tokens'] ?? 1024,
        ];

        return hash('sha256', json_encode($keyData, JSON_SORT_KEYS));
    }

    /**
     * Check if request was already processed
     */
    public static function isDuplicate(string $idempotencyKey, \Redis $cache): bool
    {
        return $cache->exists("idempotency:{$idempotencyKey}") === 1;
    }

    /**
     * Mark request as processed
     */
    public static function markProcessed(string $idempotencyKey, mixed $result, \Redis $cache, int $ttl = 3600): void
    {
        $cache->setex(
            "idempotency:{$idempotencyKey}",
            $ttl,
            json_encode($result)
        );
    }

    /**
     * Get cached result if duplicate
     */
    public static function getCachedResult(string $idempotencyKey, \Redis $cache): ?array
    {
        $cached = $cache->get("idempotency:{$idempotencyKey}");
        return $cached ? json_decode($cached, true) : null;
    }
}

// Usage with retry logic
$idempotencyKey = IdempotentRequest::generateKey($request);

// Check for duplicate
if ($cached = IdempotentRequest::getCachedResult($idempotencyKey, $redis)) {
    return $cached; // Return cached result
}

// Execute request
try {
    $result = $backoff->execute(function() use ($client, $request) {
        return $client->messages()->create($request);
    });

    // Cache result
    IdempotentRequest::markProcessed($idempotencyKey, $result, $redis);
    return $result;

} catch (\Exception $e) {
    // On failure, don't cache - allow retry
    throw $e;
}
```

## Resilient ClaudePhp Implementation

### Complete Resilient ClaudePhp

```php
<?php
# filename: src/ResilientClaudeClient.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class ResilientClaudeClient
{
    private ExponentialBackoff $backoff;
    private CircuitBreaker $circuitBreaker;
    private RateLimiter $rateLimiter;
    private array $stats = [];

    public function __construct(
        private ClaudePhp $client,
        array $options = []
    ) {
        $this->backoff = new ExponentialBackoff(
            maxRetries: $options['max_retries'] ?? 5,
            baseDelayMs: $options['base_delay_ms'] ?? 1000,
            maxDelayMs: $options['max_delay_ms'] ?? 60000,
        );

        $this->circuitBreaker = new CircuitBreaker(
            failureThreshold: $options['failure_threshold'] ?? 5,
            successThreshold: $options['success_threshold'] ?? 2,
            timeout: $options['circuit_timeout'] ?? 60,
            name: 'claude-api'
        );

        $this->rateLimiter = new RateLimiter(
            maxRequests: $options['max_requests'] ?? 50,
            windowSeconds: $options['window_seconds'] ?? 60,
        );
    }

    /**
     * Create message with full resilience
     */
    public function createMessage(array $request): array
    {
        $startTime = microtime(true);

        try {
            // Execute with all protection layers
            $result = $this->rateLimiter->execute(function() use ($request) {
                return $this->circuitBreaker->execute(function() use ($request) {
                    return $this->backoff->execute(function() use ($request) {
                        return $this->client->messages()->create($request);
                    });
                });
            });

            $this->recordSuccess(microtime(true) - $startTime);

            return $result;

        } catch (\Throwable $e) {
            $this->recordFailure($e, microtime(true) - $startTime);
            throw $e;
        }
    }

    /**
     * Create message with fallback
     */
    public function createMessageWithFallback(
        array $request,
        callable $fallback
    ): array {
        try {
            return $this->createMessage($request);

        } catch (\Throwable $e) {
            error_log("Primary request failed, using fallback: " . $e->getMessage());

            return $fallback($e);
        }
    }

    /**
     * Health check
     */
    public function isHealthy(): bool
    {
        return $this->circuitBreaker->getState() !== CircuitState::OPEN;
    }

    /**
     * Get comprehensive statistics
     */
    public function getStats(): array
    {
        return [
            'circuit_breaker' => $this->circuitBreaker->getStats(),
            'rate_limiter' => $this->rateLimiter->getStats(),
            'requests' => [
                'total' => $this->stats['total_requests'] ?? 0,
                'successful' => $this->stats['successful_requests'] ?? 0,
                'failed' => $this->stats['failed_requests'] ?? 0,
                'success_rate' => $this->calculateSuccessRate(),
            ],
            'performance' => [
                'avg_duration' => $this->stats['avg_duration'] ?? 0,
            ],
        ];
    }

    private function recordSuccess(float $duration): void
    {
        $this->stats['total_requests'] = ($this->stats['total_requests'] ?? 0) + 1;
        $this->stats['successful_requests'] = ($this->stats['successful_requests'] ?? 0) + 1;
        $this->updateAvgDuration($duration);
    }

    private function recordFailure(\Throwable $e, float $duration): void
    {
        $this->stats['total_requests'] = ($this->stats['total_requests'] ?? 0) + 1;
        $this->stats['failed_requests'] = ($this->stats['failed_requests'] ?? 0) + 1;
        $this->updateAvgDuration($duration);
    }

    private function updateAvgDuration(float $duration): void
    {
        $total = $this->stats['total_requests'] ?? 1;
        $currentAvg = $this->stats['avg_duration'] ?? 0;
        $this->stats['avg_duration'] = (($currentAvg * ($total - 1)) + $duration) / $total;
    }

    private function calculateSuccessRate(): float
    {
        $total = $this->stats['total_requests'] ?? 0;
        if ($total === 0) return 0.0;

        $successful = $this->stats['successful_requests'] ?? 0;
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Reset all protection mechanisms
     */
    public function reset(): void
    {
        $this->circuitBreaker->reset();
        $this->rateLimiter->reset();
        $this->stats = [];
    }
}

// Usage with Claude-PHP-SDK client
use ClaudePhp\ClaudePhp;

$apiKey = $_ENV['ANTHROPIC_API_KEY'];
$claudeClient = new ClaudePhp(apiKey: $apiKey);

$resilientClient = new ResilientClaudeClient(
    client: $claudeClient,
    options: [
        'max_retries' => 5,
        'failure_threshold' => 5,
        'max_requests' => 50,
        'window_seconds' => 60,
    ]
);

// Make request with full resilience
try {
    $response = $resilientClient->createMessage([
            'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => 'Hello, Claude!'
        ]]
    ]);

    echo $response->content[0]->text . "\n\n";

} catch (\RuntimeException $e) {
    echo "Request failed: " . $e->getMessage() . "\n";
}

// Check health and stats
echo "ClaudePhp healthy: " . ($resilientClient->isHealthy() ? 'Yes' : 'No') . "\n";
print_r($resilientClient->getStats());
```

## Graceful Degradation

### Fallback Strategies

```php
<?php
# filename: src/FallbackStrategy.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class FallbackStrategy
{
    /**
     * Fallback to cached response
     */
    public static function useCached(string $cacheKey, \Throwable $error): object
    {
        $cache = self::getCache();

        if ($cached = $cache->get($cacheKey)) {
            error_log("Using cached response due to error: " . $error->getMessage());

            return (object) [
                'content' => [(object) ['text' => $cached]],
                'usage' => (object) ['inputTokens' => 0, 'outputTokens' => 0],
                'cached' => true,
            ];
        }

        throw $error;
    }

    /**
     * Fallback to simpler model
     */
    public static function useSimplerModel(
        \Claude\ClaudeClient $client,
        array $request,
        \Throwable $error
    ): array {
        error_log("Falling back to Haiku due to error: " . $error->getMessage());

        $request['model'] = 'claude-haiku-4-5-20251001';
        return $client->messages()->create($request);
    }

    /**
     * Fallback to default response
     */
    public static function useDefault(string $defaultMessage, \Throwable $error): array
    {
        error_log("Using default response due to error: " . $error->getMessage());

        return [
            'content' => [['type' => 'text', 'text' => $defaultMessage]],
            'usage' => ['input_tokens' => 0, 'output_tokens' => 0],
            'fallback' => true,
        ];
    }

    /**
     * Fallback to queuing for later
     */
    public static function queueForLater(array $request, \Throwable $error): object
    {
        error_log("Queueing request for later due to error: " . $error->getMessage());

        // Add to queue (Redis, database, etc.)
        $queue = self::getQueue();
        $queue->push($request);

        return (object) [
            'content' => [(object) ['text' => 'Your request has been queued and will be processed shortly.']],
            'usage' => (object) ['inputTokens' => 0, 'outputTokens' => 0],
            'queued' => true,
        ];
    }

    private static function getCache(): object
    {
        // Return your cache implementation
        return new class {
            private array $cache = [];
            public function get(string $key) { return $this->cache[$key] ?? null; }
            public function set(string $key, $value) { $this->cache[$key] = $value; }
        };
    }

    private static function getQueue(): object
    {
        // Return your queue implementation
        return new class {
            private array $queue = [];
            public function push($item) { $this->queue[] = $item; }
            public function pop() { return array_shift($this->queue); }
        };
    }
}

// Usage with multiple fallback layers
use ClaudePhp\ClaudePhp;

$apiKey = $_ENV['ANTHROPIC_API_KEY'];
$claudeClient = new ClaudePhp(apiKey: $apiKey);
$resilientClient = new ResilientClaudeClient($claudeClient);

$request = [
            'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Hello!']]
];

try {
    $response = $resilientClient->createMessageWithFallback(
        request: $request,
        fallback: function(\Throwable $e) use ($claudeClient, $request) {
            // Try simpler model first
            try {
                return FallbackStrategy::useSimplerModel($claudeClient, $request, $e);
            } catch (\Throwable $e2) {
                // Then try cache
                return FallbackStrategy::useCached('cache-key', $e2);
            }
        }
    );

    echo $response->content[0]->text;

} catch (\Throwable $e) {
    echo "All fallbacks exhausted: " . $e->getMessage();
}
```

## Testing Error Handling

### Mocking Errors for Testing

Test your error handling logic with simulated failures:

```php
<?php
# filename: tests/ErrorHandlingTest.php
declare(strict_types=1);

namespace Tests;

use CodeWithPHP\Claude\ResilientClaudeClient;
use CodeWithPHP\Claude\ExponentialBackoff;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;

class ErrorHandlingTest
{
    /**
     * Test exponential backoff with simulated errors
     */
    public function testExponentialBackoff(): void
    {
        $attempts = 0;
        $backoff = new ExponentialBackoff(maxRetries: 3, baseDelayMs: 100);

        try {
            $result = $backoff->execute(function() use (&$attempts) {
                $attempts++;

                // Simulate transient error for first 2 attempts
                if ($attempts < 3) {
                    throw new ServerException(
                        'Service temporarily unavailable',
                        new \GuzzleHttp\Psr7\Request('POST', '/v1/messages'),
                        new Response(503)
                    );
                }

                return ['success' => true];
            });

            $this->assertEquals(3, $attempts);
            $this->assertTrue($result['success']);

        } catch (\Exception $e) {
            $this->fail("Should have succeeded after retries");
        }
    }

    /**
     * Test circuit breaker opens after threshold
     */
    public function testCircuitBreakerOpens(): void
    {
        $circuitBreaker = new CircuitBreaker(
            failureThreshold: 3,
            timeout: 60
        );

        // Simulate failures
        for ($i = 0; $i < 3; $i++) {
            try {
                $circuitBreaker->execute(function() {
                    throw new \RuntimeException("Simulated failure");
                });
            } catch (\RuntimeException $e) {
                // Expected
            }
        }

        // Circuit should be open
        $this->assertEquals(CircuitState::OPEN, $circuitBreaker->getState());

        // Next request should fail immediately
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Circuit breaker");

        $circuitBreaker->execute(function() {
            return ['success' => true];
        });
    }

    /**
     * Test rate limiter prevents exceeding limits
     */
    public function testRateLimiter(): void
    {
        $rateLimiter = new RateLimiter(
            maxRequests: 2,
            windowSeconds: 60
        );

        // First two requests should succeed
        $this->assertTrue($rateLimiter->allow());
        $this->assertTrue($rateLimiter->allow());

        // Third should be blocked
        $this->assertFalse($rateLimiter->allow());

        // After window expires, should allow again
        // (In real test, you'd mock time or wait)
    }
}
```

### Integration Testing with Real API

Test against real API errors (use test API key):

```php
<?php
# filename: tests/IntegrationTest.php
declare(strict_types=1);

namespace Tests;

use CodeWithPHP\Claude\ResilientClaudeClient;

class IntegrationTest
{
    /**
     * Test handling of real rate limit errors
     */
    public function testRateLimitHandling(): void
    {
        $client = new ResilientClaudeClient(
            client: $this->createClient(),
            options: [
                'max_retries' => 3,
                'max_requests' => 1, // Very low limit to trigger rate limit
                'window_seconds' => 60,
            ]
        );

        // First request should succeed
        $response1 = $client->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 10,
            'messages' => [['role' => 'user', 'content' => 'Hi']]
        ]);

        $this->assertNotNull($response1);

        // Second request should be rate limited
        // Should either wait or use fallback
        try {
            $response2 = $client->messages()->create([...]);
            // If it succeeds, rate limiter worked correctly
        } catch (\Exception $e) {
            // If it fails, should be handled gracefully
            $this->assertStringContainsString('rate limit', strtolower($e->getMessage()));
        }
    }
}
```

## Streaming Response Error Handling

### Handling Mid-Stream Failures

Streaming responses can fail partway through. Handle partial responses and connection drops gracefully:

```php
<?php
# filename: src/StreamingErrorHandler.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class StreamingErrorHandler
{
    private string $partialContent = '';
    private bool $streamComplete = false;
    private ?\Throwable $lastError = null;

    /**
     * Handle streaming events with error recovery
     */
    public function handleStreamEvent(string $event, array $data, callable $onChunk, callable $onError): void
    {
        try {
            switch ($event) {
                case 'content_block_delta':
                    $this->partialContent .= $data['delta']['text'] ?? '';
                    $onChunk($data['delta']['text'] ?? '');
                    break;

                case 'message_stop':
                    $this->streamComplete = true;
                    break;

                case 'error':
                    $this->lastError = new \RuntimeException($data['error']['message'] ?? 'Stream error');
                    $onError($this->lastError, $this->partialContent);
                    break;
            }
        } catch (\Throwable $e) {
            $this->lastError = $e;
            $onError($e, $this->partialContent);
        }
    }

    /**
     * Get partial content if stream failed
     */
    public function getPartialContent(): string
    {
        return $this->partialContent;
    }

    /**
     * Check if stream completed successfully
     */
    public function isComplete(): bool
    {
        return $this->streamComplete && $this->lastError === null;
    }

    /**
     * Retry failed stream with fallback
     */
    public function retryWithFallback(callable $streamOperation, callable $fallback): mixed
    {
        try {
            return $streamOperation();
        } catch (\Throwable $e) {
            // If we have partial content, use it with fallback
            if (!empty($this->partialContent)) {
                error_log("Stream failed with partial 'content' => " . strlen($this->partialContent) . " chars");
                return $fallback($this->partialContent, $e);
            }
            throw $e;
        }
    }
}

// Usage
$handler = new StreamingErrorHandler();

try {
    $stream = $client->messages()->stream([...]);

    foreach ($stream as $event) {
        $handler->handleStreamEvent(
            event: $event->type,
            data: $event->toArray(),
            onChunk: fn($text) => echo $text,
            onError: fn($error, $partial) => {
                error_log("Stream error: " . $error->getMessage());
                // Save partial content for recovery
                savePartialResponse($partial);
            }
        );
    }
} catch (\Exception $e) {
    // Handle stream failure
    $partial = $handler->getPartialContent();
    if ($partial) {
        // Use partial content or retry
        $result = $handler->retryWithFallback(
            streamOperation: fn() => retryStream(),
            fallback: fn($partial, $error) => completePartialResponse($partial)
        );
    }
}
```

### Connection Drop Recovery

Handle network interruptions during streaming:

```php
<?php
# filename: src/StreamRecovery.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class StreamRecovery
{
    /**
     * Resume stream from last received chunk
     */
    public function resumeStream(string $lastChunk, array $originalRequest): mixed
    {
        // Store context for resumption
        $context = [
            'last_chunk' => $lastChunk,
            'original_request' => $originalRequest,
            'timestamp' => time(),
        ];

        // Retry with context hint
        $retryRequest = $originalRequest;
        $retryRequest['messages'][] = [
            'role' => 'assistant',
            'content' => $lastChunk . '[RESUMED]'
        ];

        return $this->retryStream($retryRequest);
    }

    private function retryStream(array $request): mixed
    {
        // Implement retry logic with exponential backoff
        $backoff = new ExponentialBackoff(maxRetries: 3);
        return $backoff->execute(fn() => $client->messages()->stream($request));
    }
}
```

## Payload Size and Quota Errors

### Handling 413 Payload Too Large

When requests exceed size limits, implement automatic chunking or summarization:

```php
<?php
# filename: src/PayloadSizeHandler.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class PayloadSizeHandler
{
    private const MAX_PAYLOAD_SIZE = 100000; // bytes
    private const MAX_MESSAGES = 100;

    /**
     * Handle payload size errors with automatic reduction
     */
    public function handleOversizedRequest(array $request, \Throwable $error): array
    {
        if ($error->getCode() !== 413) {
            throw $error;
        }

        // Strategy 1: Truncate messages
        if (count($request['messages']) > self::MAX_MESSAGES) {
            $request['messages'] = array_slice(
                $request['messages'],
                -self::MAX_MESSAGES
            );
            return $request;
        }

        // Strategy 2: Summarize old messages
        $request['messages'] = $this->summarizeOldMessages($request['messages']);

        // Strategy 3: Reduce max_tokens
        if (isset($request['max_tokens']) && $request['max_tokens'] > 4096) {
            $request['max_tokens'] = 4096;
        }

        return $request;
    }

    /**
     * Summarize older messages to reduce payload
     */
    private function summarizeOldMessages(array $messages): array
    {
        if (count($messages) <= 10) {
            return $messages;
        }

        // Keep recent messages, summarize older ones
        $recent = array_slice($messages, -10);
        $old = array_slice($messages, 0, -10);

        $summary = $this->createSummary($old);

        return array_merge(
            [['role' => 'system', 'content' => "Previous conversation summary: {$summary}"]],
            $recent
        );
    }

    private function createSummary(array $messages): string
    {
        // Use Claude to summarize (or simple truncation)
        $content = implode("\n", array_column($messages, 'content'));
        return substr($content, 0, 500) . '...';
    }
}

// Usage
try {
    $response = $client->messages()->create($request);
} catch (\Exception $e) {
    if ($e->getCode() === 413) {
        $handler = new PayloadSizeHandler();
        $reducedRequest = $handler->handleOversizedRequest($request, $e);
        $response = $client->messages()->create($reducedRequest);
    } else {
        throw $e;
    }
}
```

## Health Checks and Readiness Probes

### API Health Monitoring

Check Claude API health before making requests:

```php
<?php
# filename: src/HealthChecker.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class HealthChecker
{
    private ?int $lastCheck = null;
    private ?bool $lastHealthStatus = null;
    private int $checkInterval = 60; // seconds

    /**
     * Check if Claude API is healthy
     */
    public function isHealthy(\Claude\ClaudeClient $client): bool
    {
        // Cache health check result
        if ($this->lastCheck && (time() - $this->lastCheck) < $this->checkInterval) {
            return $this->lastHealthStatus ?? false;
        }

        try {
            // Lightweight health check request
            $response = $client->messages()->create([
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 10,
                'messages' => [['role' => 'user', 'content' => 'ping']]
            ]);

            $this->lastHealthStatus = true;
            $this->lastCheck = time();
            return true;

        } catch (\Exception $e) {
            $this->lastHealthStatus = false;
            $this->lastCheck = time();
            return false;
        }
    }

    /**
     * Wait for API to become healthy
     */
    public function waitForHealthy(\Claude\ClaudeClient $client, int $timeout = 300): bool
    {
        $start = time();

        while ((time() - $start) < $timeout) {
            if ($this->isHealthy($client)) {
                return true;
            }
            sleep(5); // Check every 5 seconds
        }

        return false;
    }
}

// Usage with Claude-PHP-SDK
use ClaudePhp\ClaudePhp;

$apiKey = $_ENV['ANTHROPIC_API_KEY'];
$client = new ClaudePhp(apiKey: $apiKey);
$healthChecker = new HealthChecker();

if (!$healthChecker->isHealthy($client)) {
    // Use fallback or wait
    if ($healthChecker->waitForHealthy($client, timeout: 60)) {
        // API recovered, proceed
    } else {
        // Use fallback service
        return $fallbackService->handle($request);
    }
}
```

## Request Prioritization During Errors

### Priority-Based Error Handling

Handle errors differently based on request priority:

```php
<?php
# filename: src/PriorityErrorHandler.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

enum RequestPriority: string
{
    case CRITICAL = 'critical';  // User-facing, must succeed
    case HIGH = 'high';          // Important but can wait
    case NORMAL = 'normal';      // Standard requests
    case LOW = 'low';            // Background tasks
}

class PriorityErrorHandler
{
    /**
     * Handle error based on request priority
     */
    public function handleError(\Throwable $error, RequestPriority $priority): mixed
    {
        return match($priority) {
            RequestPriority::CRITICAL => $this->handleCriticalError($error),
            RequestPriority::HIGH => $this->handleHighPriorityError($error),
            RequestPriority::NORMAL => $this->handleNormalError($error),
            RequestPriority::LOW => $this->handleLowPriorityError($error),
        };
    }

    private function handleCriticalError(\Throwable $error): mixed
    {
        // Critical: Aggressive retries, multiple fallbacks
        $backoff = new ExponentialBackoff(maxRetries: 10, baseDelayMs: 500);

        return $backoff->execute(function() use ($error) {
            // Try primary, then fallback models, then cache
            return $this->tryWithAllFallbacks();
        });
    }

    private function handleHighPriorityError(\Throwable $error): mixed
    {
        // High: Standard retries with fallback
        $backoff = new ExponentialBackoff(maxRetries: 5);
        return $backoff->execute(fn() => $this->tryWithFallback());
    }

    private function handleNormalError(\Throwable $error): mixed
    {
        // Normal: Limited retries
        $backoff = new ExponentialBackoff(maxRetries: 3);
        try {
            return $backoff->execute(fn() => $this->retry());
        } catch (\Exception $e) {
            return $this->useDefaultResponse();
        }
    }

    private function handleLowPriorityError(\Throwable $error): mixed
    {
        // Low: Queue for later, don't retry now
        $this->queueForLater($error);
        return ['queued' => true, 'message' => 'Request queued for processing'];
    }
}

// Usage
$handler = new PriorityErrorHandler();

try {
    $response = $client->messages()->create($request);
} catch (\Exception $e) {
    $response = $handler->handleError($e, RequestPriority::CRITICAL);
}
```

## Error Budget and SLO Tracking

### Tracking Error Rates Against SLAs

Monitor error rates to ensure service level objectives are met:

```php
<?php
# filename: src/ErrorBudgetTracker.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class ErrorBudgetTracker
{
    private const SLO_ERROR_RATE = 0.01; // 1% error rate target
    private const BUDGET_WINDOW = 3600; // 1 hour window

    /**
     * Track error and check if budget is exhausted
     */
    public function trackError(\Throwable $error, \Redis $redis): bool
    {
        $now = time();
        $windowStart = $now - self::BUDGET_WINDOW;

        // Increment error count
        $errorKey = "error_budget:errors:{$windowStart}";
        $redis->incr($errorKey);
        $redis->expire($errorKey, self::BUDGET_WINDOW);

        // Increment total requests
        $totalKey = "error_budget:total:{$windowStart}";
        $total = $redis->incr($totalKey);
        $redis->expire($totalKey, self::BUDGET_WINDOW);

        // Calculate error rate
        $errors = (int) $redis->get($errorKey);
        $errorRate = $total > 0 ? $errors / $total : 0;

        // Check if budget exhausted
        if ($errorRate > self::SLO_ERROR_RATE) {
            $this->alertBudgetExhausted($errorRate);
            return false; // Budget exhausted
        }

        return true; // Within budget
    }

    /**
     * Get current error budget status
     */
    public function getBudgetStatus(\Redis $redis): array
    {
        $now = time();
        $windowStart = $now - self::BUDGET_WINDOW;

        $errors = (int) ($redis->get("error_budget:errors:{$windowStart}") ?: 0);
        $total = (int) ($redis->get("error_budget:total:{$windowStart}") ?: 0);
        $errorRate = $total > 0 ? $errors / $total : 0;

        return [
            'error_rate' => round($errorRate * 100, 2) . '%',
            'slo_target' => round(self::SLO_ERROR_RATE * 100, 2) . '%',
            'within_budget' => $errorRate <= self::SLO_ERROR_RATE,
            'errors' => $errors,
            'total_requests' => $total,
        ];
    }

    private function alertBudgetExhausted(float $errorRate): void
    {
        error_log("ERROR BUDGET EXHAUSTED: Error rate {$errorRate} exceeds SLO");
        // Send alert to monitoring system
    }
}

// Usage
$tracker = new ErrorBudgetTracker();

try {
    $response = $client->messages()->create($request);
    // Track success
} catch (\Exception $e) {
    // Track error
    $withinBudget = $tracker->trackError($e, $redis);

    if (!$withinBudget) {
        // Budget exhausted - use more aggressive fallbacks
        return $this->useAggressiveFallback();
    }

    throw $e;
}
```

## Error Logging Best Practices

### Structured Error Logging

Proper error logging is essential for debugging production issues. Use structured logging with context:

```php
<?php
# filename: src/ErrorLogger.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use Psr\Log\LoggerInterface;

class ErrorLogger
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Log error with full context
     */
    public function logError(\Throwable $error, array $context = []): void
    {
        $this->logger->error('Claude API error', [
            'error_type' => get_class($error),
            'error_message' => $error->getMessage(),
            'error_code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => array_merge([
                'timestamp' => time(),
                'request_id' => $context['request_id'] ?? uniqid('req_', true),
            ], $context),
        ]);
    }

    /**
     * Log retry attempt
     */
    public function logRetry(int $attempt, int $maxAttempts, \Throwable $error, int $delayMs): void
    {
        $this->logger->warning('Retrying Claude API request', [
            'attempt' => $attempt,
            'max_attempts' => $maxAttempts,
            'delay_ms' => $delayMs,
            'error' => $error->getMessage(),
            'error_code' => $error->getCode(),
        ]);
    }

    /**
     * Log circuit breaker state change
     */
    public function logCircuitBreakerState(string $name, string $oldState, string $newState, array $stats = []): void
    {
        $this->logger->info('Circuit breaker state changed', [
            'circuit_name' => $name,
            'old_state' => $oldState,
            'new_state' => $newState,
            'stats' => $stats,
        ]);
    }

    /**
     * Log rate limit hit
     */
    public function logRateLimit(string $identifier, int $retryAfter): void
    {
        $this->logger->warning('Rate limit exceeded', [
            'identifier' => $identifier,
            'retry_after_seconds' => $retryAfter,
        ]);
    }
}

// Usage with PSR-3 logger (Monolog, etc.)
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('claude_api');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

$errorLogger = new ErrorLogger($logger);

try {
    $result = $client->messages()->create([...]);
} catch (\Exception $e) {
    $errorLogger->logError($e, [
            'model' => 'claude-sonnet-4-5-20250929',
        'user_id' => 123,
        'request_size' => strlen(json_encode($request)),
    ]);
    throw $e;
}
```

### Log Levels and When to Use Them

```php
// DEBUG: Detailed information for debugging
$logger->debug('Retry delay calculated', ['delay_ms' => $delay]);

// INFO: General informational messages
$logger->info('Circuit breaker closed', ['circuit' => 'claude-api']);

// WARNING: Something unexpected but handled
$logger->warning('Rate limit approaching', ['utilization' => '90%']);

// ERROR: Error occurred but application continues
$logger->error('API request failed', ['error' => $e->getMessage()]);

// CRITICAL: Critical error requiring immediate attention
$logger->critical('Circuit breaker opened', ['failures' => 10]);
```

### Sensitive Data Protection

Never log sensitive information:

```php
// ❌ BAD - Logs API key
$logger->error('Request failed', ['api_key' => $apiKey]);

// ✅ GOOD - Redact sensitive data
$logger->error('Request failed', [
    'api_key' => substr($apiKey, 0, 8) . '...',
    'headers' => $this->redactHeaders($headers),
]);

private function redactHeaders(array $headers): array
{
    $redacted = $headers;
    if (isset($redacted['x-api-key'])) {
        $redacted['x-api-key'] = substr($redacted['x-api-key'], 0, 8) . '...';
    }
    return $redacted;
}
```

## Monitoring and Alerting

### Error Monitor

```php
<?php
# filename: src/ErrorMonitor.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class ErrorMonitor
{
    private array $errors = [];
    private array $alerts = [];

    public function __construct(
        private int $errorThreshold = 10,
        private int $windowMinutes = 5
    ) {}

    /**
     * Record error occurrence
     */
    public function recordError(\Throwable $error, array $context = []): void
    {
        $this->errors[] = [
            'timestamp' => time(),
            'type' => get_class($error),
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'context' => $context,
        ];

        $this->checkThresholds();
    }

    /**
     * Check if error thresholds are exceeded
     */
    private function checkThresholds(): void
    {
        $recentErrors = $this->getRecentErrors();

        if (count($recentErrors) >= $this->errorThreshold) {
            $this->triggerAlert(
                "Error threshold exceeded: " . count($recentErrors) .
                " errors in {$this->windowMinutes} minutes"
            );
        }

        // Check specific error types
        $errorTypes = array_count_values(array_column($recentErrors, 'type'));

        foreach ($errorTypes as $type => $count) {
            if ($count >= ($this->errorThreshold / 2)) {
                $this->triggerAlert(
                    "High frequency of {$type}: {$count} occurrences"
                );
            }
        }
    }

    /**
     * Get errors within the monitoring window
     */
    private function getRecentErrors(): array
    {
        $cutoff = time() - ($this->windowMinutes * 60);

        return array_filter(
            $this->errors,
            fn($error) => $error['timestamp'] > $cutoff
        );
    }

    /**
     * Trigger alert
     */
    private function triggerAlert(string $message): void
    {
        // Prevent duplicate alerts
        $alertKey = md5($message);
        if (isset($this->alerts[$alertKey]) &&
            time() - $this->alerts[$alertKey] < 300) {  // 5 min cooldown
            return;
        }

        $this->alerts[$alertKey] = time();

        // Log alert
        error_log("ALERT: {$message}");

        // Send to monitoring service (DataDog, Sentry, etc.)
        $this->sendToMonitoringService($message);

        // Send notification (email, Slack, etc.)
        $this->sendNotification($message);
    }

    private function sendToMonitoringService(string $message): void
    {
        // Integrate with your monitoring service
        // Example: Sentry, DataDog, CloudWatch, etc.
    }

    private function sendNotification(string $message): void
    {
        // Send alert notification
        // Example: Email, Slack, PagerDuty, etc.
    }

    /**
     * Get error statistics
     */
    public function getStats(): array
    {
        $recent = $this->getRecentErrors();

        return [
            'total_errors' => count($this->errors),
            'recent_errors' => count($recent),
            'error_rate' => count($recent) / $this->windowMinutes,  // per minute
            'error_types' => array_count_values(array_column($recent, 'type')),
            'alerts_triggered' => count($this->alerts),
        ];
    }
}

// Usage
$monitor = new ErrorMonitor(
    errorThreshold: 10,
    windowMinutes: 5
);

// Record errors from your application
try {
    $response = $resilientClient->createMessage([...]);
} catch (\Throwable $e) {
    $monitor->recordError($e, [
            'model' => 'claude-sonnet-4-5-20250929',
        'user_id' => 123,
    ]);
    throw $e;
}

// Check statistics
$stats = $monitor->getStats();
print_r($stats);
```

## Troubleshooting

### Error: "Circuit breaker is OPEN. Service temporarily unavailable."

**Symptom**: All requests fail immediately with circuit breaker error, even for new requests.

**Cause**: The circuit breaker has detected too many failures and opened to protect your application. It will remain open until the timeout period elapses.

**Solution**:

```php
// Check circuit breaker state
$stats = $circuitBreaker->getStats();
if ($stats['state'] === 'open') {
    // Wait for timeout or manually reset if needed
    $circuitBreaker->reset();
}

// Or use fallback when circuit is open
try {
    $result = $circuitBreaker->execute($operation);
} catch (\RuntimeException $e) {
    if (str_contains($e->getMessage(), 'OPEN')) {
        // Use fallback strategy
        return $fallbackStrategy->useDefault('Service temporarily unavailable', $e);
    }
    throw $e;
}
```

### Error: "Rate limit exceeded. Retry after X seconds."

**Symptom**: Requests fail with rate limit errors even though you're not making many requests.

**Cause**: Rate limiter is tracking requests incorrectly, or multiple instances are sharing the same limiter without coordination.

**Solution**:

```php
// Use distributed rate limiter (Redis, database) for multiple instances
$rateLimiter = new DistributedRateLimiter(
    storage: new RedisStorage($redis),
    maxRequests: 50,
    windowSeconds: 60
);

// Or check if rate limiter needs cleanup
$stats = $rateLimiter->getStats();
if ($stats['current_requests'] > $stats['max_requests']) {
    $rateLimiter->reset(); // Reset if tracking is incorrect
}
```

### Problem: Exponential Backoff Causing Long Delays

**Symptom**: Retries take too long, causing poor user experience.

**Cause**: Base delay or multiplier is too high, or max delay is set too high.

**Solution**:

```php
// Adjust backoff parameters for faster retries
$backoff = new ExponentialBackoff(
    maxRetries: 3,           // Reduce retries
    baseDelayMs: 500,        // Start with 500ms instead of 1000ms
    maxDelayMs: 5000,        // Cap at 5 seconds instead of 60
    multiplier: 1.5           // Slower growth (1.5x instead of 2x)
);
```

### Problem: Errors Not Being Retried When They Should Be

**Symptom**: Transient errors (like 503) are not retried automatically.

**Cause**: Error parser is not correctly identifying errors as retryable, or exception type is not recognized.

**Solution**:

```php
// Verify error is identified as transient
$isTransient = ErrorParser::isTransient($exception);
if (!$isTransient && $exception->getCode() === 503) {
    // Manually mark as retryable
    $errorData = ErrorParser::parse($exception);
    $errorData['retryable'] = true;
}

// Or check ErrorParser logic
$errorData = ErrorParser::parse($exception);
if ($errorData['status_code'] === 503 && !$errorData['retryable']) {
    // Fix ErrorParser::isTransient() method
}
```

### Problem: Requests Hanging or Timing Out

**Symptom**: Requests take too long or hang indefinitely, blocking application threads.

**Cause**: HTTP client timeout not configured, or timeout values too high.

**Solution**:

```php
// Configure appropriate timeouts
$client = new ClaudePhp([
    RequestOptions::TIMEOUT => 60,           // Total timeout
    RequestOptions::CONNECT_TIMEOUT => 10,    // Connection timeout
    RequestOptions::READ_TIMEOUT => 60,       // Read timeout
]);

// For long-running requests, use separate client
$longClient = new ClaudePhp([
    RequestOptions::TIMEOUT => 300,  // 5 minutes
    RequestOptions::READ_TIMEOUT => 300,
]);

// Implement request cancellation for user-initiated cancellations
$cancellable = new CancellableRequest();
// ... cancel when user abandons request
```

### Problem: Duplicate Requests After Retries

**Symptom**: Same request processed multiple times after retries, causing duplicate side effects.

**Cause**: Requests are not idempotent, or idempotency keys not implemented.

**Solution**:

```php
// Generate idempotency key
$idempotencyKey = IdempotentRequest::generateKey($request);

// Check for duplicate before processing
if ($cached = IdempotentRequest::getCachedResult($idempotencyKey, $redis)) {
    return $cached; // Return cached result
}

// Process request
$result = $client->messages()->create($request);

// Cache result for future duplicate checks
IdempotentRequest::markProcessed($idempotencyKey, $result, $redis);
```

## Exercises

### Exercise 1: Smart Retry System

Build an intelligent retry system that learns optimal retry parameters from historical data.

**Requirements:**

- Track success/failure patterns over time
- Adjust backoff parameters dynamically based on error rates
- Optimize for different error types (429 vs 503 vs 500)
- Provide recommendations for optimal settings
- Store historical data in a persistent format (file or database)

**Validation**: Test with simulated error patterns:

```php
// Simulate different error scenarios
$scenarios = [
    'high_429_rate' => [429, 429, 429, 200], // Rate limit errors
    'intermittent_503' => [503, 200, 503, 200], // Intermittent failures
    'temporary_outage' => [500, 500, 500, 200], // Temporary outage
];

foreach ($scenarios as $name => $errors) {
    $system = new SmartRetrySystem();
    foreach ($errors as $errorCode) {
        // Simulate error
        $system->recordAttempt($errorCode === 200, $errorCode);
    }

    $recommendations = $system->getRecommendations();
    echo "Scenario: {$name}\n";
    print_r($recommendations);
    // Should show different recommendations for each scenario
}
```

### Exercise 2: Multi-Region Failover

Create a system that automatically fails over to different API regions when one is unavailable.

**Requirements:**

- Detect region-specific failures (timeout, 503, connection errors)
- Automatically switch to healthy regions
- Load balance across multiple regions when all are healthy
- Track region health with health checks
- Implement region priority/weighting

**Validation**: Test failover behavior:

```php
$regionManager = new MultiRegionManager([
    'us-east' => 'https://api.anthropic.com',
    'us-west' => 'https://api-west.anthropic.com',
    'eu' => 'https://api-eu.anthropic.com',
]);

// Simulate region failure
$regionManager->markRegionUnhealthy('us-east');

// Next request should use us-west or eu
$response = $regionManager->execute(function() {
    return $client->messages()->create([...]);
});

// Verify region was switched
assert($regionManager->getCurrentRegion() !== 'us-east');
```

### Exercise 3: Comprehensive Error Dashboard

Build a real-time dashboard showing error rates, circuit breaker states, and system health.

**Requirements:**

- Real-time error visualization (WebSocket or Server-Sent Events)
- Circuit breaker status for all breakers
- Rate limit utilization graphs
- Error rate trends over time
- Alert management interface
- Export error logs functionality

**Validation**: Dashboard should display:

```php
// Dashboard should show:
$dashboard = new ErrorDashboard();

$stats = $dashboard->getStats();
assert(isset($stats['circuit_breakers']));
assert(isset($stats['rate_limiters']));
assert(isset($stats['error_rates']));
assert(isset($stats['alerts']));

// Real-time updates should work
$dashboard->subscribe(function($update) {
    // Should receive updates when errors occur
    assert(isset($update['timestamp']));
    assert(isset($update['type']));
});
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Build a machine learning model or heuristic system that analyzes error patterns and adjusts parameters. Consider using a simple moving average or exponential smoothing to track error rates. Store success/failure history in a database or file.

**Exercise 2**: Implement a region manager with health checks and failover logic. Use the circuit breaker pattern per region. Track region health with periodic health checks. Implement round-robin or weighted load balancing when multiple regions are healthy.

**Exercise 3**: Use WebSockets or Server-Sent Events (SSE) to push real-time updates to a web dashboard. Store error data in a time-series database or use Redis for real-time data. Create a simple HTML/JavaScript frontend that connects to your PHP backend via WebSocket or SSE.

</details>

## Further Reading

- **[Claude-PHP-SDK on GitHub](https://github.com/claude-php/Claude-PHP-SDK)** — Community PHP SDK for Claude AI with comprehensive error handling patterns
- **[Claude-PHP-SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Install via Composer: `composer require claude-php/claude-php-sdk`
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[Official Anthropic SDK](https://github.com/anthropics/anthropic-sdk-php)** — Official Python/JavaScript SDK reference (Python examples)

## Wrap-up

Congratulations! You've built production-grade error handling and rate limiting systems for Claude API applications. Here's what you accomplished:

- ✓ **Error Classification**: Created systems to parse and categorize all Claude API error types, distinguishing retryable from non-retryable errors
- ✓ **Intelligent Retries**: Implemented exponential backoff with jitter to handle transient failures gracefully
- ✓ **Circuit Breakers**: Built circuit breaker patterns to prevent cascading failures and protect your application from degraded services
- ✓ **Rate Limiting**: Applied multiple rate limiting strategies (sliding window, token bucket) to respect API quotas
- ✓ **Resilient ClaudePhp**: Combined all protection mechanisms into a single `ResilientClaudeClient` wrapper
- ✓ **Graceful Degradation**: Created fallback strategies that keep your application functional even when Claude API is unavailable
- ✓ **Monitoring & Alerting**: Implemented error monitoring systems that track patterns and trigger alerts for production issues

These error handling patterns are essential for any production Claude application. They ensure your application remains stable and responsive even when external services experience problems. The circuit breaker pattern, exponential backoff, and rate limiting are industry-standard techniques used by major tech companies.

In the next chapter, you'll learn about **Tool Use Fundamentals** - extending Claude's capabilities with function calling to create more powerful, interactive applications.

## Further Reading

- [Anthropic API Error Handling Guide](https://docs.claude.com/en/api/errors) — Official documentation on Claude API error types and handling
- [Exponential Backoff and Jitter](https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/) — AWS best practices for retry strategies
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html) — Martin Fowler's explanation of the circuit breaker pattern
- [Rate Limiting Strategies](https://cloud.google.com/architecture/rate-limiting-strategies-techniques) — Google Cloud's guide to rate limiting techniques
- [Resilience Patterns](https://www.oreilly.com/library/view/release-it/9781680500268/) — Release It! by Michael Nygard covers production resilience patterns
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/) — PHP standard for logging, useful for error monitoring
- [Chapter 38: Scaling Applications](/series/claude-php-developers/chapters/38-scaling-applications) — Distributed error handling patterns for multi-server deployments
- [Guzzle HTTP ClaudePhp Documentation](https://docs.guzzlephp.org/en/stable/) — HTTP client configuration and timeout options
- [Idempotency Keys Best Practices](https://stripe.com/docs/api/idempotent_requests) — Stripe's guide to idempotent API design

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="10"
  label="You've mastered error handling and rate limiting!"
/>

---

Continue to [Chapter 11: Tool Use Fundamentals](/series/claude-php-developers/chapters/11-tool-use-fundamentals) to learn about extending Claude with function calling capabilities.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 10 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-10)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-10
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-error-types.php
```
