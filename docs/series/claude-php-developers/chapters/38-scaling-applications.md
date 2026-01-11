---
title: "38: Scaling Claude Applications"
description: "Scale Claude applications to production traffic: horizontal scaling strategies, load balancing, queue-based processing, circuit breakers, retry patterns, capacity planning, and performance optimization for high-throughput PHP applications."
series: "claude-php-developers"
chapter: 38
order: 38
difficulty: "Advanced"
prerequisites:
  - "PHP 8.4+ installed"
  - "Understanding of distributed systems"
  - "Completion of Chapters 36-37"
teaches:
  - 'Understand horizontal scaling patterns and stateless application design'
  - 'Configure load balancers for AI workloads with appropriate timeouts and health checks'
  - 'Implement queue-based processing to handle spiky traffic and long-running tasks'
  - 'Build circuit breakers to prevent cascading failures in distributed systems'
  - 'Implement retry logic with exponential backoff and jitter for transient failures'
---
![38: Scaling Claude Applications](/images/claude-php/chapter-38-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 38</span>
</div>

# Chapter 38: Scaling Claude Applications

## Overview

Production Claude applications must handle variable traffic loads, API rate limits, and unpredictable response times. Scaling AI applications presents unique challenges: managing concurrent API calls, handling rate limits gracefully, optimizing for high latency operations, and maintaining cost efficiency at scale.

This chapter teaches you to build scalable Claude applications. You'll implement horizontal scaling patterns, configure intelligent load balancing, build queue-based processing systems, implement circuit breakers and retry logic, plan capacity for growth, and optimize performance for high-throughput scenarios.

**What You'll Learn:**

- Horizontal scaling architecture patterns
- Load balancing strategies for AI workloads
- Queue-based processing with Laravel queues
- Circuit breakers and resilience patterns
- Retry logic with exponential backoff
- Connection pooling and concurrency control
- Capacity planning and traffic shaping
- Performance optimization techniques

**Estimated Time**: 60-75 minutes

## What You'll Build

By the end of this chapter, you will have created:

- **Stateless Claude Service** (`StatelessClaudeService.php`) - Horizontally scalable service with externalized state management
- **Load Balancer Configuration** - Nginx configuration with health checks and intelligent routing
- **Queue-Based Processing System** (`ClaudeQueueJob.php`) - Asynchronous job processing with retry logic and webhook notifications
- **Priority Queue Manager** (`PriorityQueueManager.php`) - Multi-tier queue system for request prioritization
- **Circuit Breaker** (`CircuitBreaker.php`) - Resilience pattern preventing cascading failures
- **Retry Manager** (`RetryManager.php`) - Exponential backoff with jitter for transient failures
- **Concurrency Limiter** (`ConcurrencyLimiter.php`) - Semaphore-based rate limit enforcement
- **Capacity Calculator** (`CapacityCalculator.php`) - Infrastructure planning tool using Little's Law
- **Connection Pool** (`ClaudeConnectionPool.php`) - HTTP connection reuse for performance optimization
- **Database Connection Pool** (`DatabaseConnectionPool.php`) - Read replicas and write primary for database scaling
- **Distributed Cache** (`DistributedClaudeCache.php`) - Redis-based response caching across servers
- **Cache Invalidation Manager** (`CacheInvalidationManager.php`) - Cross-server cache invalidation with pattern matching
- **Distributed Tracer** (`DistributedTracer.php`) - Request tracing across multiple servers for debugging
- **Header-Aware Rate Limiter** (`HeaderAwareRateLimiter.php`) - Proactive concurrency management using API response headers

## Objectives

By completing this chapter, you will:

- Understand horizontal scaling patterns and stateless application design
- Configure load balancers for AI workloads with appropriate timeouts and health checks
- Implement queue-based processing to handle spiky traffic and long-running tasks
- Build circuit breakers to prevent cascading failures in distributed systems
- Implement retry logic with exponential backoff and jitter for transient failures
- Control concurrency to respect API rate limits using semaphore patterns
- Plan infrastructure capacity using Little's Law and cost calculations
- Optimize performance through connection pooling and resource reuse
- Scale databases with read replicas and connection pooling strategies
- Implement distributed caching across multiple servers
- Invalidate cache efficiently across distributed systems
- Trace requests across multiple servers for debugging and monitoring
- Proactively manage concurrency using API rate limit headers

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.4+** with Redis and process control extensions
- ✓ **Queue system** (Redis, RabbitMQ, or SQS)
- ✓ **Load balancer** (nginx, HAProxy, or cloud LB)
- ✓ **Understanding of async processing**
- ✓ **Completion of Chapters 36-37** or equivalent understanding of security and monitoring

**Estimated Time**: ~60-75 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Verify Redis extension
php -m | grep redis

# Verify Redis is running
redis-cli ping

# Check if queue system is available (Laravel example)
composer show illuminate/queue

# Test nginx configuration (if using nginx)
nginx -t
```

## Horizontal Scaling Architecture

Design your application to scale horizontally across multiple servers.

### Stateless Application Design

```php
<?php
# filename: src/Scaling/StatelessClaudeService.php
declare(strict_types=1);

namespace App\Scaling;


class StatelessClaudeService
{
    /**
     * Stateless service - no instance state
     * Can run on any server in the cluster
     */
    public function __construct(
        private readonly \ClaudePhp\ClaudePhp $client,
        private readonly \Redis $redis,
        private readonly string $sessionStore = 'redis'
    ) {}

    /**
     * Process request with externalized state
     */
    public function processRequest(
        string $userId,
        string $message,
        string $sessionId
    ): array {
        // Load conversation history from shared storage
        $history = $this->loadConversationHistory($sessionId);

        // Build messages array
        $messages = $history;
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        // Make Claude request
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => $messages
        ]);

        // Extract response
        $assistantMessage = $response->content[0]->text;

        // Save updated history to shared storage
        $messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];
        $this->saveConversationHistory($sessionId, $messages);

        return [
            'response' => $assistantMessage,
            'message_id' => $response->id,
            'session_id' => $sessionId,
        ];
    }

    /**
     * Load conversation history from shared storage
     */
    private function loadConversationHistory(string $sessionId): array
    {
        $key = "conversation:$sessionId";
        $data = $this->redis->get($key);

        return $data ? json_decode($data, true) : [];
    }

    /**
     * Save conversation history to shared storage
     */
    private function saveConversationHistory(string $sessionId, array $messages): void
    {
        $key = "conversation:$sessionId";

        // Store with 24-hour expiration
        $this->redis->setex(
            $key,
            86400,
            json_encode($messages)
        );
    }
}

// Deploy across multiple servers - any server can handle any request
$service = new StatelessClaudeService($client, $redis);
$result = $service->processRequest($userId, $message, $sessionId);
```

### Load Balancer Configuration

```nginx
# filename: /etc/nginx/conf.d/claude-app.conf
# Nginx load balancer configuration

upstream claude_app {
    # Least connections algorithm - best for varying response times
    least_conn;

    # Application servers
    server app1.example.com:8080 max_fails=3 fail_timeout=30s;
    server app2.example.com:8080 max_fails=3 fail_timeout=30s;
    server app3.example.com:8080 max_fails=3 fail_timeout=30s;

    # Health check
    keepalive 32;
}

server {
    listen 80;
    server_name api.example.com;

    location / {
        proxy_pass http://claude_app;

        # Timeouts for long-running AI requests
        proxy_connect_timeout 10s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;

        # Headers
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Connection reuse
        proxy_http_version 1.1;
        proxy_set_header Connection "";
    }

    location /health {
        access_log off;
        proxy_pass http://claude_app/health;
    }
}
```

### Health Check Endpoint

```php
<?php
# filename: public/health.php
# Health check endpoint for load balancer
declare(strict_types=1);

header('Content-Type: application/json');

try {
    // Check Redis connection
    $redis = new Redis();
    $redis->connect('localhost', 6379);
    $redis->ping();

    // Check API key is configured
    if (!$_ENV['ANTHROPIC_API_KEY']) {
        throw new Exception('API key not configured');
    }

    // Optional: Check Claude API connectivity (sparingly - costs money)
    // $client->messages()->create([...]);

    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => time(),
        'server' => gethostname(),
    ]);

} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'unhealthy',
        'error' => $e->getMessage(),
        'timestamp' => time(),
    ]);
}
```

## Queue-Based Processing

Handle spiky traffic and long-running tasks with queue-based architecture.

### Queue Worker Implementation

```php
<?php
# filename: src/Queue/ClaudeQueueJob.php
declare(strict_types=1);

namespace App\Queue;


class ClaudeQueueJob
{
    /**
     * Laravel Queue Job for Claude processing
     */
    public function __construct(
        public string $userId,
        public string $prompt,
        public string $model,
        public array $metadata = []
    ) {}

    public function handle(\ClaudePhp\ClaudePhp $client, \Redis $redis): void
    {
        $startTime = microtime(true);

        try {
            // Make Claude request
            $response = $client->messages()->create([
                'model' => $this->model,
                'max_tokens' => 2048,
                'messages' => [[
                    'role' => 'user',
                    'content' => $this->prompt
                ]]
            ]);

            $duration = microtime(true) - $startTime;

            // Store result
            $result = [
                'status' => 'completed',
                'response' => $response->content[0]->text,
                'message_id' => $response->id,
                'tokens' => [
                    'input' => $response->usage->inputTokens,
                    'output' => $response->usage->outputTokens,
                ],
                'duration' => $duration,
                'completed_at' => time(),
            ];

            $this->storeResult($redis, $result);

            // Trigger webhook or notification
            $this->notifyCompletion($result);

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            // Store error
            $result = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => $duration,
                'failed_at' => time(),
            ];

            $this->storeResult($redis, $result);

            // Re-throw for queue retry logic
            throw $e;
        }
    }

    /**
     * Define retry strategy
     */
    public function retries(): int
    {
        return 3;
    }

    public function backoff(): array
    {
        return [10, 30, 60]; // Retry after 10s, 30s, 60s
    }

    private function storeResult(\Redis $redis, array $result): void
    {
        $key = "claude:result:{$this->userId}:" . ($this->metadata['request_id'] ?? 'unknown');
        $redis->setex($key, 3600, json_encode($result)); // 1 hour TTL
    }

    private function notifyCompletion(array $result): void
    {
        if (isset($this->metadata['webhook_url'])) {
            // Send webhook notification
            $this->sendWebhook($this->metadata['webhook_url'], $result);
        }
    }

    private function sendWebhook(string $url, array $data): void
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }
}

// Dispatch job to queue
use Illuminate\Support\Facades\Queue;

Queue::push(new ClaudeQueueJob(
    userId: 'user-123',
    prompt: 'Analyze this large document...',
    model: 'claude-sonnet-4-5',
    metadata: [
        'request_id' => 'req-abc123',
        'webhook_url' => 'https://example.com/webhook/claude-complete'
    ]
));
```

### Priority Queue System

```php
<?php
# filename: src/Queue/PriorityQueueManager.php
declare(strict_types=1);

namespace App\Queue;

class PriorityQueueManager
{
    private const QUEUE_HIGH = 'claude:queue:high';
    private const QUEUE_NORMAL = 'claude:queue:normal';
    private const QUEUE_LOW = 'claude:queue:low';

    public function __construct(
        private readonly \Redis $redis
    ) {}

    /**
     * Add job to priority queue
     */
    public function enqueue(array $job, string $priority = 'normal'): void
    {
        $queue = match($priority) {
            'high' => self::QUEUE_HIGH,
            'low' => self::QUEUE_LOW,
            default => self::QUEUE_NORMAL
        };

        $this->redis->rPush($queue, json_encode($job));
    }

    /**
     * Get next job (check high priority first)
     */
    public function dequeue(): ?array
    {
        // Try high priority first
        $job = $this->redis->lPop(self::QUEUE_HIGH);
        if ($job) {
            return json_decode($job, true);
        }

        // Then normal priority
        $job = $this->redis->lPop(self::QUEUE_NORMAL);
        if ($job) {
            return json_decode($job, true);
        }

        // Finally low priority
        $job = $this->redis->lPop(self::QUEUE_LOW);
        if ($job) {
            return json_decode($job, true);
        }

        return null;
    }

    /**
     * Get queue depths
     */
    public function getQueueStats(): array
    {
        return [
            'high' => $this->redis->lLen(self::QUEUE_HIGH),
            'normal' => $this->redis->lLen(self::QUEUE_NORMAL),
            'low' => $this->redis->lLen(self::QUEUE_LOW),
        ];
    }
}

// Usage
$queueManager = new PriorityQueueManager($redis);

// High priority - paying customers
$queueManager->enqueue([
    'user_id' => 'premium-user-123',
    'prompt' => 'Urgent analysis needed',
    'model' => 'claude-opus-4-1',
], 'high');

// Normal priority - regular requests
$queueManager->enqueue([
    'user_id' => 'user-456',
    'prompt' => 'Generate blog post',
    'model' => 'claude-sonnet-4-5',
], 'normal');

// Low priority - batch processing
$queueManager->enqueue([
    'user_id' => 'system',
    'prompt' => 'Analyze logs from yesterday',
    'model' => 'claude-haiku-4-5-20251001',
], 'low');
```

## Circuit Breakers

Prevent cascading failures with circuit breaker pattern.

### Circuit Breaker Implementation

```php
<?php
# filename: src/Resilience/CircuitBreaker.php
declare(strict_types=1);

namespace App\Resilience;

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';      // Normal operation
    private const STATE_OPEN = 'open';          // Failing - reject requests
    private const STATE_HALF_OPEN = 'half_open'; // Testing - allow limited requests

    private const FAILURE_THRESHOLD = 5;         // Open after 5 failures
    private const SUCCESS_THRESHOLD = 2;         // Close after 2 successes in half-open
    private const TIMEOUT = 60;                  // Try half-open after 60 seconds

    public function __construct(
        private readonly \Redis $redis,
        private readonly string $serviceName
    ) {}

    /**
     * Execute operation with circuit breaker protection
     */
    public function execute(callable $operation): mixed
    {
        $state = $this->getState();

        if ($state === self::STATE_OPEN) {
            // Check if timeout has passed
            if ($this->shouldAttemptReset()) {
                $this->setState(self::STATE_HALF_OPEN);
            } else {
                throw new CircuitBreakerOpenException(
                    "Circuit breaker is OPEN for {$this->serviceName}"
                );
            }
        }

        try {
            $result = $operation();

            // Success - record it
            $this->recordSuccess();

            return $result;

        } catch (\Exception $e) {
            // Failure - record it
            $this->recordFailure();

            throw $e;
        }
    }

    private function getState(): string
    {
        $state = $this->redis->get($this->getStateKey());
        return $state ?: self::STATE_CLOSED;
    }

    private function setState(string $state): void
    {
        $this->redis->setex($this->getStateKey(), 300, $state);

        if ($state === self::STATE_OPEN) {
            // Record when we opened
            $this->redis->setex($this->getOpenedAtKey(), 300, time());
        }
    }

    private function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_HALF_OPEN) {
            // Increment success counter
            $successes = $this->redis->incr($this->getSuccessCountKey());

            if ($successes >= self::SUCCESS_THRESHOLD) {
                // Close the circuit
                $this->setState(self::STATE_CLOSED);
                $this->resetCounters();
            }
        } elseif ($state === self::STATE_CLOSED) {
            // Reset failure counter on success
            $this->redis->del($this->getFailureCountKey());
        }
    }

    private function recordFailure(): void
    {
        $state = $this->getState();

        if ($state === self::STATE_HALF_OPEN) {
            // Failed in half-open - back to open
            $this->setState(self::STATE_OPEN);
            $this->resetCounters();
        } else {
            // Increment failure counter
            $failures = $this->redis->incr($this->getFailureCountKey());
            $this->redis->expire($this->getFailureCountKey(), 300);

            if ($failures >= self::FAILURE_THRESHOLD) {
                // Open the circuit
                $this->setState(self::STATE_OPEN);
                $this->resetCounters();

                error_log("[CIRCUIT BREAKER] Opened circuit for {$this->serviceName} after $failures failures");
            }
        }
    }

    private function shouldAttemptReset(): bool
    {
        $openedAt = $this->redis->get($this->getOpenedAtKey());

        if (!$openedAt) {
            return true;
        }

        return (time() - (int)$openedAt) >= self::TIMEOUT;
    }

    private function resetCounters(): void
    {
        $this->redis->del($this->getFailureCountKey());
        $this->redis->del($this->getSuccessCountKey());
    }

    private function getStateKey(): string
    {
        return "circuit_breaker:{$this->serviceName}:state";
    }

    private function getFailureCountKey(): string
    {
        return "circuit_breaker:{$this->serviceName}:failures";
    }

    private function getSuccessCountKey(): string
    {
        return "circuit_breaker:{$this->serviceName}:successes";
    }

    private function getOpenedAtKey(): string
    {
        return "circuit_breaker:{$this->serviceName}:opened_at";
    }
}

class CircuitBreakerOpenException extends \Exception {}

// Usage
$circuitBreaker = new CircuitBreaker($redis, 'claude_api');

try {
    $response = $circuitBreaker->execute(function() use ($client) {
        return $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => 'Hello']]
        ]);
    });

} catch (CircuitBreakerOpenException $e) {
    // Circuit is open - use fallback
    $response = "Service temporarily unavailable. Please try again later.";

    // Log for monitoring
    error_log("[CIRCUIT BREAKER] {$e->getMessage()}");
}
```

## Retry Logic with Exponential Backoff

Handle transient failures gracefully.

### Retry Manager

```php
<?php
# filename: src/Resilience/RetryManager.php
declare(strict_types=1);

namespace App\Resilience;

class RetryManager
{
    public function __construct(
        private readonly int $maxAttempts = 3,
        private readonly int $baseDelayMs = 1000,
        private readonly int $maxDelayMs = 30000,
        private readonly float $jitterFactor = 0.1
    ) {}

    /**
     * Execute with retry logic and exponential backoff
     */
    public function execute(callable $operation, ?callable $shouldRetry = null): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxAttempts) {
            $attempt++;

            try {
                return $operation();

            } catch (\Exception $e) {
                $lastException = $e;

                // Check if we should retry this exception
                if ($shouldRetry && !$shouldRetry($e)) {
                    throw $e;
                }

                // Don't retry on last attempt
                if ($attempt >= $this->maxAttempts) {
                    break;
                }

                // Calculate delay with exponential backoff
                $delay = $this->calculateDelay($attempt);

                error_log(sprintf(
                    "[RETRY] Attempt %d/%d failed: %s. Retrying in %dms...",
                    $attempt,
                    $this->maxAttempts,
                    $e->getMessage(),
                    $delay
                ));

                // Wait before retry
                usleep($delay * 1000);
            }
        }

        // All retries exhausted
        throw new MaxRetriesExceededException(
            "Operation failed after {$this->maxAttempts} attempts",
            previous: $lastException
        );
    }

    /**
     * Calculate delay with exponential backoff and jitter
     */
    private function calculateDelay(int $attempt): int
    {
        // Exponential backoff: delay = baseDelay * (2 ^ (attempt - 1))
        $delay = $this->baseDelayMs * (2 ** ($attempt - 1));

        // Cap at max delay
        $delay = min($delay, $this->maxDelayMs);

        // Add jitter to prevent thundering herd
        $jitter = $delay * $this->jitterFactor;
        $delay = $delay + random_int(-$jitter, $jitter);

        return (int) $delay;
    }

    /**
     * Check if exception is retryable
     */
    public static function isRetryableException(\Exception $e): bool
    {
        $message = $e->getMessage();

        // Retry on rate limiting
        if (str_contains($message, '429') || str_contains($message, 'rate_limit')) {
            return true;
        }

        // Retry on timeout
        if (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
            return true;
        }

        // Retry on temporary errors
        if (str_contains($message, '502') ||
            str_contains($message, '503') ||
            str_contains($message, '504')) {
            return true;
        }

        // Don't retry on client errors (4xx except 429)
        if (preg_match('/\b4\d{2}\b/', $message) && !str_contains($message, '429')) {
            return false;
        }

        // Default: retry
        return true;
    }
}

class MaxRetriesExceededException extends \Exception {}

// Usage
$retryManager = new RetryManager(
    maxAttempts: 3,
    baseDelayMs: 1000,
    maxDelayMs: 30000,
    jitterFactor: 0.1
);

try {
    $response = $retryManager->execute(
        operation: fn() => $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => 'Hello']]
        ]),
        shouldRetry: fn($e) => RetryManager::isRetryableException($e)
    );

} catch (MaxRetriesExceededException $e) {
    error_log("[ERROR] All retries exhausted: " . $e->getMessage());
    throw $e;
}
```

### Combined Resilience Pattern

```php
<?php
# filename: src/Resilience/ResilientClaudeClient.php
declare(strict_types=1);

namespace App\Resilience;


class ResilientClaudeClient
{
    public function __construct(
        private readonly \ClaudePhp\ClaudePhp $client,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly RetryManager $retryManager
    ) {}

    /**
     * Make Claude request with full resilience pattern
     */
    public function request(array $params): mixed
    {
        return $this->circuitBreaker->execute(function() use ($params) {
            return $this->retryManager->execute(
                operation: fn() => $this->client->messages()->create($params),
                shouldRetry: fn($e) => RetryManager::isRetryableException($e)
            );
        });
    }
}

// Usage - automatic circuit breaking and retries
$resilientClient = new ResilientClaudeClient(
    client: $client,
    circuitBreaker: new CircuitBreaker($redis, 'claude_api'),
    retryManager: new RetryManager(maxAttempts: 3)
);

try {
    $response = $resilientClient->request([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ]);

} catch (CircuitBreakerOpenException $e) {
    // Service is down - use fallback
    $response = $this->getFallbackResponse();

} catch (MaxRetriesExceededException $e) {
    // Persistent failure - alert operations
    $this->alertOps($e);
    throw $e;
}
```

## Concurrency Control

Manage concurrent API requests to respect rate limits.

### Semaphore-Based Concurrency Limiter

```php
<?php
# filename: src/Scaling/ConcurrencyLimiter.php
declare(strict_types=1);

namespace App\Scaling;

class ConcurrencyLimiter
{
    public function __construct(
        private readonly \Redis $redis,
        private readonly int $maxConcurrent = 10,
        private readonly int $acquireTimeout = 30
    ) {}

    /**
     * Execute with concurrency limit
     */
    public function execute(callable $operation, string $key = 'default'): mixed
    {
        $semaphoreKey = "concurrency:semaphore:$key";
        $acquired = false;

        try {
            // Try to acquire semaphore
            $acquired = $this->acquire($semaphoreKey);

            if (!$acquired) {
                throw new ConcurrencyLimitException(
                    "Concurrency limit reached ($this->maxConcurrent concurrent requests)"
                );
            }

            // Execute operation
            return $operation();

        } finally {
            // Always release semaphore
            if ($acquired) {
                $this->release($semaphoreKey);
            }
        }
    }

    private function acquire(string $key): bool
    {
        $timeout = time() + $this->acquireTimeout;

        while (time() < $timeout) {
            // Get current count
            $current = (int) $this->redis->get($key) ?: 0;

            if ($current < $this->maxConcurrent) {
                // Try to increment
                $new = $this->redis->incr($key);

                if ($new <= $this->maxConcurrent) {
                    // Successfully acquired
                    return true;
                } else {
                    // Someone else got it first - decrement back
                    $this->redis->decr($key);
                }
            }

            // Wait a bit before retry
            usleep(100000); // 100ms
        }

        return false;
    }

    private function release(string $key): void
    {
        $this->redis->decr($key);

        // Cleanup if zero
        if ((int) $this->redis->get($key) <= 0) {
            $this->redis->del($key);
        }
    }

    /**
     * Get current concurrency
     */
    public function getCurrentConcurrency(string $key = 'default'): int
    {
        return (int) $this->redis->get("concurrency:semaphore:$key") ?: 0;
    }
}

class ConcurrencyLimitException extends \Exception {}

// Usage
$concurrencyLimiter = new ConcurrencyLimiter(
    redis: $redis,
    maxConcurrent: 10,  // Max 10 concurrent Claude requests
    acquireTimeout: 30   // Wait up to 30 seconds
);

try {
    $response = $concurrencyLimiter->execute(
        operation: fn() => $client->messages()->create([...]),
        key: 'claude_api'  // Separate limits for different services
    );

} catch (ConcurrencyLimitException $e) {
    // Too many concurrent requests - queue for later
    Queue::push(new ClaudeQueueJob(...));

    return ['status' => 'queued', 'message' => 'Request queued for processing'];
}
```

## Capacity Planning

Plan infrastructure capacity for expected load.

### Capacity Calculator

```php
<?php
# filename: src/Planning/CapacityCalculator.php
declare(strict_types=1);

namespace App\Planning;

class CapacityCalculator
{
    /**
     * Calculate required capacity
     */
    public function calculateCapacity(array $requirements): array
    {
        $peakRps = $requirements['peak_requests_per_second'];
        $avgLatency = $requirements['avg_latency_seconds'];
        $targetConcurrency = $requirements['target_concurrency'] ?? null;

        // Calculate required concurrent workers
        // Little's Law: L = λ * W
        // L = average number of requests in system (concurrency)
        // λ = arrival rate (requests per second)
        // W = average time in system (latency)
        $requiredConcurrency = $targetConcurrency ?? ceil($peakRps * $avgLatency);

        // Add headroom for spikes (20%)
        $withHeadroom = ceil($requiredConcurrency * 1.2);

        // Calculate number of servers needed
        $workersPerServer = $requirements['workers_per_server'] ?? 4;
        $serversNeeded = ceil($withHeadroom / $workersPerServer);

        // Calculate queue capacity
        $queueCapacity = $this->calculateQueueCapacity($peakRps, $avgLatency);

        // Calculate costs
        $costs = $this->calculateCosts($requirements, $serversNeeded, $peakRps);

        return [
            'concurrent_requests' => $requiredConcurrency,
            'concurrent_with_headroom' => $withHeadroom,
            'workers_per_server' => $workersPerServer,
            'servers_needed' => $serversNeeded,
            'queue_capacity' => $queueCapacity,
            'costs' => $costs,
            'recommendations' => $this->getRecommendations($requirements, $serversNeeded),
        ];
    }

    private function calculateQueueCapacity(float $peakRps, float $avgLatency): int
    {
        // Queue should handle 5 minutes of peak traffic
        return (int) ceil($peakRps * 300);
    }

    private function calculateCosts(array $requirements, int $servers, float $peakRps): array
    {
        // Infrastructure costs
        $serverCost = $requirements['server_cost_per_month'] ?? 50.00;
        $infrastructureCost = $servers * $serverCost;

        // Claude API costs (estimated)
        $requestsPerMonth = $peakRps * 3600 * 24 * 30 * 0.3; // 30% of peak sustained
        $avgTokensPerRequest = $requirements['avg_tokens_per_request'] ?? 500;
        $model = $requirements['model'] ?? 'sonnet';

        $pricing = match($model) {
            'opus' => 0.018,      // ~$18 per 1M tokens (mixed input/output)
            'sonnet' => 0.0036,   // ~$3.6 per 1M tokens
            'haiku' => 0.0003,    // ~$0.3 per 1M tokens
            default => 0.0036
        };

        $totalTokens = $requestsPerMonth * $avgTokensPerRequest;
        $apiCost = ($totalTokens / 1_000_000) * $pricing;

        return [
            'infrastructure_monthly' => $infrastructureCost,
            'api_monthly' => $apiCost,
            'total_monthly' => $infrastructureCost + $apiCost,
            'cost_per_request' => ($infrastructureCost + $apiCost) / $requestsPerMonth,
        ];
    }

    private function getRecommendations(array $requirements, int $servers): array
    {
        $recommendations = [];

        if ($servers > 10) {
            $recommendations[] = "Consider using auto-scaling for cost efficiency";
        }

        if ($requirements['avg_latency_seconds'] > 3) {
            $recommendations[] = "High latency detected - consider caching or faster models";
        }

        $avgTokens = $requirements['avg_tokens_per_request'] ?? 500;
        if ($avgTokens > 2000) {
            $recommendations[] = "High token usage - consider prompt optimization";
        }

        return $recommendations;
    }
}

// Usage
$calculator = new CapacityCalculator();

$capacity = $calculator->calculateCapacity([
    'peak_requests_per_second' => 50,
    'avg_latency_seconds' => 2.5,
    'workers_per_server' => 4,
    'server_cost_per_month' => 50.00,
    'avg_tokens_per_request' => 800,
    'model' => 'sonnet',
]);

print_r($capacity);
/*
Array (
    [concurrent_requests] => 125
    [concurrent_with_headroom] => 150
    [workers_per_server] => 4
    [servers_needed] => 38
    [queue_capacity] => 15000
    [costs] => Array (
        [infrastructure_monthly] => 1900.00
        [api_monthly] => 1166.40
        [total_monthly] => 3066.40
        [cost_per_request] => 0.0009484
    )
    [recommendations] => Array (
        [0] => Consider using auto-scaling for cost efficiency
    )
)
*/
```

## Database Scaling

Scale your database to support scaled Claude applications.

### Read Replicas and Connection Pooling

```php
<?php
# filename: src/Scaling/DatabaseConnectionPool.php
declare(strict_types=1);

namespace App\Scaling;

class DatabaseConnectionPool
{
    private array $readReplicas = [];
    private mixed $writeConnection;
    private int $replicaIndex = 0;

    public function __construct(
        private readonly array $config
    ) {
        $this->initializeConnections();
    }

    /**
     * Initialize connection pool with write and read replicas
     */
    private function initializeConnections(): void
    {
        // Primary write connection
        $this->writeConnection = $this->createConnection(
            $this->config['primary'],
            isWrite: true
        );

        // Read replicas for scaling reads
        foreach ($this->config['replicas'] as $replica) {
            $this->readReplicas[] = $this->createConnection(
                $replica,
                isWrite: false
            );
        }
    }

    /**
     * Get write connection (primary only)
     */
    public function getWriteConnection(): mixed
    {
        return $this->writeConnection;
    }

    /**
     * Get read connection (load-balanced across replicas)
     */
    public function getReadConnection(): mixed
    {
        if (empty($this->readReplicas)) {
            // Fallback to primary if no replicas
            return $this->writeConnection;
        }

        // Round-robin load balancing
        $connection = $this->readReplicas[$this->replicaIndex];
        $this->replicaIndex = ($this->replicaIndex + 1) % count($this->readReplicas);

        return $connection;
    }

    private function createConnection(array $config, bool $isWrite): mixed
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'] ?? 3306,
                $config['database']
            );

            $pdo = new \PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Set connection-specific attributes
            if ($isWrite) {
                // Stricter settings for writes
                $pdo->setAttribute(\PDO::ATTR_TIMEOUT, 30);
            } else {
                // Relaxed settings for reads
                $pdo->setAttribute(\PDO::ATTR_TIMEOUT, 60);
            }

            return $pdo;

        } catch (\PDOException $e) {
            throw new DatabaseConnectionException(
                "Failed to connect to {$config['host']}: {$e->getMessage()}"
            );
        }
    }
}

class DatabaseConnectionException extends \Exception {}

// Usage
$pool = new DatabaseConnectionPool([
    'primary' => [
        'host' => 'primary.example.com',
        'port' => 3306,
        'database' => 'claude_app',
        'username' => 'app_user',
        'password' => getenv('DB_PASSWORD')
    ],
    'replicas' => [
        [
            'host' => 'replica1.example.com',
            'port' => 3306,
            'database' => 'claude_app',
            'username' => 'app_user',
            'password' => getenv('DB_PASSWORD')
        ],
        [
            'host' => 'replica2.example.com',
            'port' => 3306,
            'database' => 'claude_app',
            'username' => 'app_user',
            'password' => getenv('DB_PASSWORD')
        ]
    ]
]);

// Write operations go to primary
$write = $pool->getWriteConnection();
$write->prepare('INSERT INTO conversations (user_id, content) VALUES (?, ?)')
      ->execute([$userId, $content]);

// Read operations distributed across replicas
$read = $pool->getReadConnection();
$conversations = $read->prepare('SELECT * FROM conversations WHERE user_id = ?')
    ->execute([$userId])
    ->fetchAll();
```

### PgBouncer Configuration for PostgreSQL

```bash
# filename: /etc/pgbouncer/pgbouncer.ini
# Connection pooling configuration for PostgreSQL

[databases]
claude_app = host=primary.example.com port=5432 dbname=claude_app

[pgbouncer]
# Pool mode: transaction or session
pool_mode = transaction

# Maximum number of client connections
max_client_conn = 1000

# Maximum number of server connections
default_pool_size = 25

# Minimum pool size
min_pool_size = 10

# Maximum idle time in seconds
idle_in_transaction_session_timeout = 900

# Statement timeout
server_lifetime = 3600

# Query timeout
query_timeout = 1800

# Listen address
listen_addr = 0.0.0.0
listen_port = 6432

# Logging
log_connections = 1
log_disconnections = 1
log_pooler_errors = 1
```

## Multi-Server Caching Strategy

Implement distributed caching across all servers.

### Redis-Based Response Caching

```php
<?php
# filename: src/Caching/DistributedClaudeCache.php
declare(strict_types=1);

namespace App\Caching;

use Redis;

class DistributedClaudeCache
{
    private const PREFIX = 'claude:response:';
    private const TTL_SHORT = 3600;      // 1 hour
    private const TTL_MEDIUM = 86400;    // 1 day
    private const TTL_LONG = 604800;     // 7 days

    public function __construct(
        private readonly Redis $redis
    ) {}

    /**
     * Get cached response by prompt hash
     */
    public function get(string $prompt, string $model): ?array
    {
        $key = $this->getKey($prompt, $model);
        $cached = $this->redis->get($key);

        if ($cached === false) {
            return null;
        }

        return json_decode($cached, true);
    }

    /**
     * Cache response with semantic similarity detection
     */
    public function put(
        string $prompt,
        string $model,
        mixed $response,
        int $ttl = self::TTL_SHORT
    ): void {
        $key = $this->getKey($prompt, $model);

        $data = [
            'response' => $response->content[0]->text,
            'tokens' => [
                'input' => $response->usage->inputTokens,
                'output' => $response->usage->outputTokens,
            ],
            'cached_at' => time(),
            'model' => $model,
        ];

        // Store response
        $this->redis->setex($key, $ttl, json_encode($data));

        // Add to index for discovery
        $this->addToIndex($prompt, $model, $key, $ttl);
    }

    /**
     * Invalidate cache based on patterns
     */
    public function invalidate(string $pattern): int
    {
        $keys = $this->redis->keys(self::PREFIX . $pattern);
        $invalidated = 0;

        foreach ($keys as $key) {
            if ($this->redis->del($key)) {
                $invalidated++;
            }
        }

        return $invalidated;
    }

    /**
     * Clear all cache (use sparingly in production)
     */
    public function flush(): void
    {
        $this->redis->flushAll();
    }

    /**
     * Get cache statistics across all servers
     */
    public function getStats(): array
    {
        $info = $this->redis->info('stats');

        return [
            'total_keys' => $this->redis->dbSize(),
            'evicted' => $info['evicted_keys'] ?? 0,
            'hits' => $info['keyspace_hits'] ?? 0,
            'misses' => $info['keyspace_misses'] ?? 0,
            'memory_used' => $info['used_memory_human'] ?? 'unknown',
        ];
    }

    private function getKey(string $prompt, string $model): string
    {
        return self::PREFIX . md5($prompt . ':' . $model);
    }

    private function addToIndex(
        string $prompt,
        string $model,
        string $key,
        int $ttl
    ): void {
        $indexKey = 'claude:cache:index';

        $this->redis->hSet(
            $indexKey,
            $key,
            json_encode([
                'prompt_hash' => md5($prompt),
                'model' => $model,
                'cached_at' => time(),
            ])
        );

        // Set index TTL to match data TTL
        $this->redis->expire($indexKey, $ttl);
    }
}

// Usage
$cache = new DistributedClaudeCache($redis);

// Check cache first
$cached = $cache->get($prompt, 'claude-sonnet-4-5');
if ($cached) {
    return $cached['response'];
}

// Make API call if not cached
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);

// Cache response
$cache->put($prompt, 'claude-sonnet-4-5', $response, ttl: 86400);

return $response->content[0]->text;
```

### Cache Invalidation Strategy

```php
<?php
# filename: src/Caching/CacheInvalidationManager.php
declare(strict_types=1);

namespace App\Caching;

use Redis;

class CacheInvalidationManager
{
    public function __construct(
        private readonly Redis $redis,
        private readonly DistributedClaudeCache $cache
    ) {}

    /**
     * Invalidate cache for specific user's data
     */
    public function invalidateUser(string $userId): int
    {
        return $this->cache->invalidate("user:$userId:*");
    }

    /**
     * Invalidate cache for specific model
     */
    public function invalidateModel(string $model): int
    {
        return $this->cache->invalidate("model:$model:*");
    }

    /**
     * Invalidate cache older than days
     */
    public function invalidateOlderThan(int $days): int
    {
        $cutoff = time() - ($days * 86400);
        $keys = $this->redis->keys('claude:response:*');
        $invalidated = 0;

        foreach ($keys as $key) {
            $data = $this->redis->get($key);
            if ($data) {
                $cached = json_decode($data, true);
                if ($cached['cached_at'] < $cutoff) {
                    if ($this->redis->del($key)) {
                        $invalidated++;
                    }
                }
            }
        }

        return $invalidated;
    }

    /**
     * Broadcast cache invalidation to all servers
     */
    public function broadcastInvalidation(string $pattern): void
    {
        $this->redis->publish('cache:invalidate', json_encode([
            'pattern' => $pattern,
            'timestamp' => time(),
            'server' => gethostname(),
        ]));
    }
}

// Usage in scheduler (runs on all servers)
$invalidationManager = new CacheInvalidationManager($redis, $cache);

// Clean up old cache entries daily
$invalidationManager->invalidateOlderThan(days: 7);

// Clear user cache after update
$invalidationManager->invalidateUser($userId);
$invalidationManager->broadcastInvalidation("user:$userId:*");
```

## Distributed Observability

Monitor and trace requests across multiple servers.

### Distributed Request Tracing

```php
<?php
# filename: src/Observability/DistributedTracer.php
declare(strict_types=1);

namespace App\Observability;

use Redis;

class DistributedTracer
{
    private const TRACE_PREFIX = 'trace:';
    private const SPAN_PREFIX = 'span:';

    public function __construct(
        private readonly Redis $redis
    ) {}

    /**
     * Start a distributed trace
     */
    public function startTrace(string $traceId): DistributedTrace
    {
        return new DistributedTrace(
            traceId: $traceId,
            startTime: microtime(true),
            server: gethostname(),
            redis: $this->redis
        );
    }

    /**
     * Get trace by ID across all servers
     */
    public function getTrace(string $traceId): ?array
    {
        $key = self::TRACE_PREFIX . $traceId;
        $data = $this->redis->get($key);

        if ($data === false) {
            return null;
        }

        return json_decode($data, true);
    }

    /**
     * Get all spans for a trace
     */
    public function getSpans(string $traceId): array
    {
        $spanKeys = $this->redis->keys(self::SPAN_PREFIX . $traceId . ':*');
        $spans = [];

        foreach ($spanKeys as $key) {
            $data = $this->redis->get($key);
            if ($data) {
                $spans[] = json_decode($data, true);
            }
        }

        // Sort by start time
        usort($spans, fn($a, $b) => $a['startTime'] <=> $b['startTime']);

        return $spans;
    }

    /**
     * Get trace timeline (visual representation)
     */
    public function getTimeline(string $traceId): array
    {
        $trace = $this->getTrace($traceId);
        $spans = $this->getSpans($traceId);

        if (!$trace) {
            return [];
        }

        $startTime = $trace['startTime'];
        $timeline = [];

        foreach ($spans as $span) {
            $relativeStart = ($span['startTime'] - $startTime) * 1000; // ms
            $duration = ($span['endTime'] - $span['startTime']) * 1000; // ms

            $timeline[] = [
                'name' => $span['name'],
                'server' => $span['server'],
                'startMs' => $relativeStart,
                'durationMs' => $duration,
                'status' => $span['status'],
            ];
        }

        return $timeline;
    }
}

class DistributedTrace
{
    private array $spans = [];

    public function __construct(
        private readonly string $traceId,
        private readonly float $startTime,
        private readonly string $server,
        private readonly Redis $redis
    ) {}

    /**
     * Record a span within the trace
     */
    public function recordSpan(
        string $name,
        callable $operation,
        string $spanType = 'operation'
    ): mixed {
        $spanId = uniqid('span_', true);
        $spanStartTime = microtime(true);

        try {
            $result = $operation();

            $this->saveSpan([
                'spanId' => $spanId,
                'name' => $name,
                'type' => $spanType,
                'server' => $this->server,
                'startTime' => $spanStartTime,
                'endTime' => microtime(true),
                'status' => 'success',
                'result' => is_array($result) ? $result : ['result' => $result],
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->saveSpan([
                'spanId' => $spanId,
                'name' => $name,
                'type' => $spanType,
                'server' => $this->server,
                'startTime' => $spanStartTime,
                'endTime' => microtime(true),
                'status' => 'error',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function saveSpan(array $span): void
    {
        $key = 'span:' . $this->traceId . ':' . $span['spanId'];

        $this->redis->setex(
            $key,
            3600, // 1 hour TTL
            json_encode($span)
        );
    }

    /**
     * Save complete trace
     */
    public function save(): void
    {
        $trace = [
            'traceId' => $this->traceId,
            'server' => $this->server,
            'startTime' => $this->startTime,
            'endTime' => microtime(true),
            'duration' => microtime(true) - $this->startTime,
            'spanCount' => count($this->spans),
        ];

        $key = 'trace:' . $this->traceId;
        $this->redis->setex($key, 3600, json_encode($trace));
    }
}

// Usage
$tracer = new DistributedTracer($redis);
$traceId = uniqid('trace_');
$trace = $tracer->startTrace($traceId);

// Record operations across servers
$response = $trace->recordSpan('claude_api_call', function() use ($client) {
    return $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ]);
}, 'api_call');

// Save trace for cross-server analysis
$trace->save();

// Get trace timeline later
$timeline = $tracer->getTimeline($traceId);
// Shows exact timing of operations across all servers
```

## Rate Limit Header Optimization

Proactively manage concurrency using API response headers.

```php
<?php
# filename: src/RateLimiting/HeaderAwareRateLimiter.php
declare(strict_types=1);

namespace App\RateLimiting;

use Redis;

class HeaderAwareRateLimiter
{
    public function __construct(
        private readonly \ClaudePhp\ClaudePhp $client,
        private readonly Redis $redis,
        private readonly int $defaultMaxConcurrent = 10
    ) {}

    /**
     * Make request with automatic concurrency adjustment
     */
    public function executeWithHeaderAwareness(
        callable $requestFn,
        string $limitKey = 'claude:rate_limit'
    ): mixed {
        try {
            // Execute the request
            $response = $requestFn();

            // Parse rate limit headers (SDK-specific implementation)
            // Note: Header access may vary by SDK version
            // For now, implement basic rate limiting without headers
            $this->updateConcurrencyLimit($limitKey, $this->defaultMaxConcurrent);

            return $response;

        } catch (\Exception $e) {
            // Check if rate limited
            if ($this->isRateLimited($e)) {
                $this->reduceConurrency($limitKey);
                throw new RateLimitedException(
                    "Rate limited. Concurrency reduced. {$e->getMessage()}"
                );
            }

            throw $e;
        }
    }

    /**
     * Adjust concurrency based on remaining requests
     */
    private function updateConcurrencyLimit(string $limitKey, int $remaining): void
    {
        $current = (int) $this->redis->get($limitKey) ?: $this->defaultMaxConcurrent;

        // Scale concurrency with available capacity
        if ($remaining > 100) {
            // Plenty of capacity - can increase concurrency
            $newLimit = min($this->defaultMaxConcurrent + 5, 20);
        } elseif ($remaining > 50) {
            // Moderate capacity
            $newLimit = $this->defaultMaxConcurrent;
        } elseif ($remaining > 20) {
            // Low capacity - reduce
            $newLimit = max($this->defaultMaxConcurrent - 3, 5);
        } else {
            // Critical - minimal concurrency
            $newLimit = 2;
        }

        $this->redis->setex($limitKey, 60, $newLimit);
    }

    /**
     * Reduce concurrency on rate limit error
     */
    private function reduceConurrency(string $limitKey): void
    {
        $current = (int) $this->redis->get($limitKey) ?: $this->defaultMaxConcurrent;
        $reduced = max((int)($current * 0.7), 1);

        $this->redis->setex($limitKey, 300, $reduced); // 5 min backoff
    }

    /**
     * Check if error is rate limit related
     */
    private function isRateLimited(\Exception $e): bool
    {
        return str_contains($e->getMessage(), '429') ||
               str_contains($e->getMessage(), 'rate_limit');
    }

    /**
     * Get current rate limit status
     */
    public function getStatus(string $limitKey = 'claude:rate_limit'): array
    {
        return [
            'current_concurrency' => (int) $this->redis->get($limitKey) ?: $this->defaultMaxConcurrent,
            'remaining_requests' => (int) $this->redis->get($limitKey . ':remaining') ?: 'unknown',
            'remaining_tokens' => (int) $this->redis->get($limitKey . ':tokens') ?: 'unknown',
            'reset_at' => $this->redis->get($limitKey . ':reset') ?: 'unknown',
        ];
    }
}

class RateLimitedException extends \Exception {}

// Usage
$rateLimiter = new HeaderAwareRateLimiter($client, $redis);

try {
    $response = $rateLimiter->executeWithHeaderAwareness(
        requestFn: fn() => $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => 'Hello']]
        ])
    );

} catch (RateLimitedException $e) {
    // Concurrency already reduced, can queue or retry
    echo "Rate limited: " . $e->getMessage();
}

// Monitor rate limit status
$status = $rateLimiter->getStatus();
echo "Current concurrency: " . $status['current_concurrency'];
```

## Performance Optimization

### Connection Pooling

```php
<?php
# filename: src/Performance/ClaudeConnectionPool.php
declare(strict_types=1);

namespace App\Performance;


class ClaudeConnectionPool
{
    private array $pool = [];
    private int $poolSize;

    public function __construct(int $poolSize = 5)
    {
        $this->poolSize = $poolSize;

        // Pre-create connections
        for ($i = 0; $i < $poolSize; $i++) {
            $this->pool[] = $this->createClient();
        }
    }

    /**
     * Get client from pool
     */
    public function getClient(): \ClaudePhp\ClaudePhp
    {
        if (empty($this->pool)) {
            // Pool exhausted - create new client
            return $this->createClient();
        }

        return array_pop($this->pool);
    }

    /**
     * Return client to pool
     */
    public function returnClient(\ClaudePhp\ClaudePhp $client): void
    {
        if (count($this->pool) < $this->poolSize) {
            $this->pool[] = $client;
        }
    }

    /**
     * Execute with pooled client
     */
    public function execute(callable $operation): mixed
    {
        $client = $this->getClient();

        try {
            return $operation($client);
        } finally {
            $this->returnClient($client);
        }
    }

    private function createClient(): \ClaudePhp\ClaudePhp
    {
        return new \ClaudePhp\ClaudePhp(
            apiKey: $_ENV['ANTHROPIC_API_KEY']
        );
    }
}

// Usage
$pool = new ClaudeConnectionPool(poolSize: 10);

$response = $pool->execute(fn($client) =>
    $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ])
);
```

## Exercises

### Exercise 1: Auto-Scaling Controller

**Goal**: Build an auto-scaling system that monitors queue depth and server metrics to scale infrastructure automatically.

Create a file called `AutoScaler.php` and implement:

- Monitor queue depth using `PriorityQueueManager`
- Check server CPU and memory utilization
- Scale up when queue depth exceeds threshold (e.g., > 1000 jobs)
- Scale down when utilization is low (< 30% CPU, < 50% memory)
- Return array of scaling actions taken with timestamps

**Validation**: Test your implementation:

```php
<?php
$autoScaler = new AutoScaler($redis, $queueManager);

// Simulate high queue depth
$queueManager->enqueue([...], 'normal'); // Add 1500 jobs

$actions = $autoScaler->checkAndScale();

// Should return scaling actions
print_r($actions);
/*
Array (
    [0] => Array (
        [action] => 'scale_up'
        [reason] => 'Queue depth 1500 exceeds threshold 1000'
        [timestamp] => 1234567890
    )
)
*/
```

### Exercise 2: Traffic Shaper

**Goal**: Implement a traffic shaping system that enforces rate limits per user tier and handles queue overflow gracefully.

Create a file called `TrafficShaper.php` and implement:

- Rate limiting per user tier (free: 10/min, premium: 100/min, enterprise: unlimited)
- Request prioritization based on user tier
- Queue overflow handling (reject or downgrade when queue is full)
- Fair usage policies (distribute capacity evenly)

**Validation**: Test your implementation:

```php
<?php
$trafficShaper = new TrafficShaper($redis, $queueManager);

// Free tier user - should be rate limited
$result1 = $trafficShaper->shapeTraffic([
    'user_id' => 'free-user-123',
    'tier' => 'free',
    'prompt' => 'Test request'
]);

// Premium user - should pass through
$result2 = $trafficShaper->shapeTraffic([
    'user_id' => 'premium-user-456',
    'tier' => 'premium',
    'prompt' => 'Test request'
]);

echo $result1['status']; // Should be 'rate_limited' after 10 requests
echo $result2['status']; // Should be 'queued' or 'processing'
```

### Exercise 3: Load Test Framework

**Goal**: Build a load testing framework to measure system performance under various load conditions.

Create a file called `LoadTester.php` and implement:

- Concurrent request generation with configurable concurrency
- Latency measurement (p50, p95, p99 percentiles)
- Error rate tracking (success vs failure counts)
- Resource utilization monitoring (CPU, memory, queue depth)
- Generate comprehensive performance report

**Validation**: Test your implementation:

```php
<?php
$loadTester = new LoadTester($client, $redis);

$results = $loadTester->runLoadTest([
    'concurrency' => 50,
    'duration' => 60, // seconds
    'requests_per_second' => 10,
]);

print_r($results);
/*
Array (
    [total_requests] => 600
    [successful] => 580
    [failed] => 20
    [error_rate] => 0.033
    [latency] => Array (
        [p50] => 2.1
        [p95] => 4.5
        [p99] => 6.2
    )
    [throughput] => 9.67 // requests per second
)
*/
```

## Troubleshooting

### Error: "Queue depth exceeds capacity"

**Symptom**: Queue backing up, jobs not processing fast enough

**Cause**: Insufficient workers or slow job processing

**Solution**:

- Add more queue workers: `php artisan queue:work --workers=10`
- Check for slow jobs blocking the queue
- Implement job timeouts to prevent stuck jobs
- Use priority queues to process important requests first
- Scale horizontally by adding more application servers

```php
// Add timeout to queue jobs
public function timeout(): int
{
    return 300; // 5 minutes
}
```

### Error: "High latency under load"

**Symptom**: Response times increase significantly when traffic increases

**Cause**: Connection exhaustion, insufficient resources, or inefficient code

**Solution**:

- Check connection pooling is enabled and sized correctly
- Review timeout settings (increase if needed for long-running requests)
- Implement caching for common requests to reduce API calls
- Consider using faster models (Haiku) for simple tasks
- Monitor server resources (CPU, memory, network)

```php
// Increase connection pool size
$pool = new ClaudeConnectionPool(poolSize: 20); // Increase from default 5
```

### Error: "Rate limit exceeded (429)"

**Symptom**: Frequent 429 errors from Claude API

**Cause**: Exceeding API rate limits, too many concurrent requests

**Solution**:

- Implement proper exponential backoff and retry logic
- Use concurrency limiting to cap simultaneous requests
- Spread requests over time using queues
- Contact Anthropic for higher rate limits if needed
- Monitor rate limit headers and adjust accordingly

```php
// Use concurrency limiter
$concurrencyLimiter = new ConcurrencyLimiter(
    redis: $redis,
    maxConcurrent: 5, // Reduce if hitting limits
    acquireTimeout: 30
);
```

### Error: "Circuit breaker is OPEN"

**Symptom**: All requests failing, circuit breaker preventing new requests

**Cause**: Service experiencing persistent failures

**Solution**:

- Check underlying service health (Claude API status)
- Review error logs to identify root cause
- Wait for circuit breaker timeout (default 60 seconds)
- Implement fallback responses for degraded service
- Manually reset circuit breaker if needed (for testing)

```php
// Check circuit breaker state
$state = $redis->get("circuit_breaker:claude_api:state");
if ($state === 'open') {
    // Use fallback or cached responses
    return $this->getFallbackResponse();
}
```

### Error: "Concurrency limit reached"

**Symptom**: Requests being rejected due to concurrency limits

**Cause**: Too many simultaneous requests exceeding configured limit

**Solution**:

- Queue requests instead of rejecting them
- Increase concurrency limit if infrastructure can handle it
- Implement request prioritization
- Use batch processing for multiple requests

```php
// Queue instead of rejecting
try {
    $response = $concurrencyLimiter->execute(...);
} catch (ConcurrencyLimitException $e) {
    // Queue for later processing
    Queue::push(new ClaudeQueueJob(...));
    return ['status' => 'queued'];
}
```

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've completed Chapter 38 on scaling Claude applications. Here's what you've accomplished:

- ✓ **Built stateless services** that can scale horizontally across multiple servers
- ✓ **Configured load balancers** with health checks and intelligent routing
- ✓ **Implemented queue-based processing** to handle variable traffic loads
- ✓ **Created circuit breakers** to prevent cascading failures
- ✓ **Added retry logic** with exponential backoff for resilience
- ✓ **Controlled concurrency** to respect API rate limits
- ✓ **Planned infrastructure capacity** using mathematical models
- ✓ **Optimized performance** through connection pooling and resource reuse

You now have the knowledge and tools to scale Claude applications to production traffic levels. The patterns you've learned—stateless design, queue processing, circuit breakers, and capacity planning—are fundamental to building reliable, scalable distributed systems.

In the next chapter, you'll learn to optimize costs and manage billing for your scaled Claude applications, ensuring your infrastructure remains cost-effective as it grows.

## Key Takeaways

- ✓ **Stateless Design**: Enable horizontal scaling by externalizing state to shared storage (Redis)
- ✓ **Queue-Based Processing**: Handle spiky traffic with asynchronous processing and priority queues
- ✓ **Circuit Breakers**: Prevent cascading failures with automatic circuit breaking and recovery
- ✓ **Retry Logic**: Handle transient failures with exponential backoff and jitter
- ✓ **Concurrency Control**: Respect rate limits with semaphore-based limiting
- ✓ **Capacity Planning**: Calculate infrastructure needs using Little's Law (L = λ × W)
- ✓ **Connection Pooling**: Reuse HTTP connections for better performance and reduced overhead
- ✓ **Priority Queues**: Serve important requests first with multi-tier queue systems
- ✓ **Database Scaling**: Use read replicas and connection pooling (PgBouncer) for database performance
- ✓ **Distributed Caching**: Share cache across servers with Redis for consistency and efficiency
- ✓ **Cache Invalidation**: Broadcast invalidation patterns to ensure all servers stay in sync
- ✓ **Distributed Tracing**: Track request flow across servers for debugging and performance analysis
- ✓ **Header-Aware Rate Limiting**: Use API response headers to proactively adjust concurrency limits

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="38"
  label="You've mastered scaling Claude applications!"
/>

---

Continue to [Chapter 39: Cost Optimization and Billing](/series/claude-php-developers/chapters/39-cost-optimization) to learn cost management strategies.

## Further Reading

- [Anthropic Rate Limits Documentation](https://docs.claude.com/en/api/rate-limits) — Understanding Claude API rate limits and best practices
- [Laravel Queue Documentation](https://laravel.com/docs/queues) — Comprehensive guide to Laravel's queue system
- [Redis Documentation](https://redis.io/docs/) — Redis data structures and patterns for distributed systems
- [Nginx Load Balancing](https://nginx.org/en/docs/http/load_balancing.html) — Load balancing strategies and configuration
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html) — Martin Fowler's explanation of the circuit breaker pattern
- [Exponential Backoff and Jitter](https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/) — AWS best practices for retry logic
- [Little's Law](https://en.wikipedia.org/wiki/Little%27s_law) — Mathematical foundation for capacity planning
- [Chapter 37: Monitoring and Observability](/series/claude-php-developers/chapters/37-monitoring-observability) — Monitor your scaled applications effectively

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 38 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-38)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-38
composer install
php examples/scaling-demo.php
```
