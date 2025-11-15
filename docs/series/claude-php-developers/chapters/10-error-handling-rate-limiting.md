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

## Understanding Claude API Errors

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
        // Client Errors (4xx)
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

        // Extract HTTP status code
        if (method_exists($exception, 'getCode')) {
            $errorData['status_code'] = $exception->getCode();
            $errorData['type'] = ClaudeErrorReference::getType($exception->getCode());
            $errorData['retryable'] = ClaudeErrorReference::shouldRetry($exception->getCode());
        }

        // Parse error message for details
        $message = $exception->getMessage();

        // Extract retry-after header if present
        if (preg_match('/retry[- ]after[:\s]+(\d+)/i', $message, $matches)) {
            $errorData['retry_after'] = (int) $matches[1];
        }

        // Extract error type from message
        if (preg_match('/error[_\s]?type[:\s]+([a-z_]+)/i', $message, $matches)) {
            $errorData['type'] = $matches[1];
        }

        return $errorData;
    }

    /**
     * Determine if error is transient (should retry)
     */
    public static function isTransient(\Throwable $exception): bool
    {
        $errorData = self::parse($exception);

        // Network errors are transient
        if ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
            return true;
        }

        // Timeout errors are transient
        if ($exception instanceof \GuzzleHttp\Exception\RequestException) {
            return true;
        }

        // Check HTTP status code
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

// Execute with retry
try {
    $result = $backoff->execute(function() use ($client) {
        return $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => 'Hello, Claude!'
            ]]
        ]);
    });

    echo "\nSuccess: " . $result->content[0]->text . "\n";

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
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => 'Hello!'
            ]]
        ]);
    });

    echo "Success: " . $result->content[0]->text . "\n";

} catch (\RuntimeException $e) {
    echo "Circuit breaker error: " . $e->getMessage() . "\n";

    $stats = $circuitBreaker->getStats();
    echo "Circuit state: {$stats['state']}\n";
}
```

## Rate Limiting Strategies

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
            'model' => 'claude-sonnet-4-20250514',
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

## Resilient Client Implementation

### Complete Resilient Client

```php
<?php
# filename: src/ResilientClaudeClient.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use Anthropic\Contracts\ClientContract;

class ResilientClaudeClient
{
    private ExponentialBackoff $backoff;
    private CircuitBreaker $circuitBreaker;
    private RateLimiter $rateLimiter;
    private array $stats = [];

    public function __construct(
        private ClientContract $client,
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
    public function createMessage(array $request): object
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
    ): object {
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

// Usage
$resilientClient = new ResilientClaudeClient(
    client: $client,
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
        'model' => 'claude-sonnet-4-20250514',
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
echo "Client healthy: " . ($resilientClient->isHealthy() ? 'Yes' : 'No') . "\n";
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
        ClientContract $client,
        array $request,
        \Throwable $error
    ): object {
        error_log("Falling back to Haiku due to error: " . $error->getMessage());

        $request['model'] = 'claude-haiku-4-20250514';
        return $client->messages()->create($request);
    }

    /**
     * Fallback to default response
     */
    public static function useDefault(string $defaultMessage, \Throwable $error): object
    {
        error_log("Using default response due to error: " . $error->getMessage());

        return (object) [
            'content' => [(object) ['text' => $defaultMessage]],
            'usage' => (object) ['inputTokens' => 0, 'outputTokens' => 0],
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
$resilientClient = new ResilientClaudeClient($client);

try {
    $response = $resilientClient->createMessageWithFallback(
        request: [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => 'Hello!']]
        ],
        fallback: function(\Throwable $e) use ($client) {
            // Try simpler model first
            try {
                return FallbackStrategy::useSimplerModel($client, [...], $e);
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
        'model' => 'claude-sonnet-4-20250514',
        'user_id' => 123,
    ]);
    throw $e;
}

// Check statistics
$stats = $monitor->getStats();
print_r($stats);
```

## Exercises

### Exercise 1: Smart Retry System

Build an intelligent retry system that learns optimal retry parameters from historical data.

**Requirements:**
- Track success/failure patterns
- Adjust backoff parameters dynamically
- Optimize for different error types
- Provide recommendations

### Exercise 2: Multi-Region Failover

Create a system that automatically fails over to different API regions when one is unavailable.

**Requirements:**
- Detect region-specific failures
- Automatically switch regions
- Load balance across regions
- Track region health

### Exercise 3: Comprehensive Error Dashboard

Build a real-time dashboard showing error rates, circuit breaker states, and system health.

**Requirements:**
- Real-time error visualization
- Circuit breaker status
- Rate limit utilization
- Alert management

<details>
<summary>Solution Hints</summary>

For Exercise 1, build a machine learning model or heuristic system that analyzes error patterns and adjusts parameters. For Exercise 2, implement a region manager with health checks and failover logic. For Exercise 3, use WebSockets or SSE to push real-time updates to a web dashboard.

</details>

## Key Takeaways

- ✓ Implement exponential backoff for transient errors
- ✓ Use circuit breakers to prevent cascading failures
- ✓ Apply rate limiting to respect API limits
- ✓ Build multiple fallback layers for resilience
- ✓ Monitor error patterns to detect issues early
- ✓ Distinguish between retryable and non-retryable errors
- ✓ Always log errors with context for debugging
- ✓ Test error handling under failure conditions

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
