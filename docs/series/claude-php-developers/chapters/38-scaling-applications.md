---
title: "38: Scaling Claude Applications"
description: "Scale Claude applications to production traffic: horizontal scaling strategies, load balancing, queue-based processing, circuit breakers, retry patterns, capacity planning, and performance optimization for high-throughput PHP applications."
series: "claude-php-developers"
chapter: 38
order: 38
difficulty: "Advanced"
prerequisites:
  - "PHP 8.2+ installed"
  - "Understanding of distributed systems"
  - "Completion of Chapters 36-37"
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

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.2+** with Redis and process control extensions
- ✓ **Queue system** (Redis, RabbitMQ, or SQS)
- ✓ **Load balancer** (nginx, HAProxy, or cloud LB)
- ✓ **Understanding of async processing**

## Horizontal Scaling Architecture

Design your application to scale horizontally across multiple servers.

### Stateless Application Design

```php
<?php
# filename: src/Scaling/StatelessClaudeService.php
declare(strict_types=1);

namespace App\Scaling;

use Anthropic\Anthropic;

class StatelessClaudeService
{
    /**
     * Stateless service - no instance state
     * Can run on any server in the cluster
     */
    public function __construct(
        private readonly Anthropic $client,
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
            'model' => 'claude-sonnet-4-20250514',
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
    if (!getenv('ANTHROPIC_API_KEY')) {
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

use Anthropic\Anthropic;

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

    public function handle(Anthropic $client, \Redis $redis): void
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
    model: 'claude-sonnet-4-20250514',
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
    'model' => 'claude-opus-4-20250514',
], 'high');

// Normal priority - regular requests
$queueManager->enqueue([
    'user_id' => 'user-456',
    'prompt' => 'Generate blog post',
    'model' => 'claude-sonnet-4-20250514',
], 'normal');

// Low priority - batch processing
$queueManager->enqueue([
    'user_id' => 'system',
    'prompt' => 'Analyze logs from yesterday',
    'model' => 'claude-haiku-4-20250514',
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
            'model' => 'claude-sonnet-4-20250514',
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
            'model' => 'claude-sonnet-4-20250514',
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

use Anthropic\Anthropic;

class ResilientClaudeClient
{
    public function __construct(
        private readonly Anthropic $client,
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
        'model' => 'claude-sonnet-4-20250514',
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

## Performance Optimization

### Connection Pooling

```php
<?php
# filename: src/Performance/ClaudeConnectionPool.php
declare(strict_types=1);

namespace App\Performance;

use Anthropic\Anthropic;

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
    public function getClient(): Anthropic
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
    public function returnClient(Anthropic $client): void
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

    private function createClient(): Anthropic
    {
        return Anthropic::factory()
            ->withApiKey(getenv('ANTHROPIC_API_KEY'))
            ->withHttpClient(new \GuzzleHttp\Client([
                'timeout' => 60,
                'connect_timeout' => 10,
                'http_errors' => false,
            ]))
            ->make();
    }
}

// Usage
$pool = new ClaudeConnectionPool(poolSize: 10);

$response = $pool->execute(fn($client) =>
    $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => 'Hello']]
    ])
);
```

## Exercises

### Exercise 1: Auto-Scaling Controller

Implement an auto-scaling controller:

```php
<?php
class AutoScaler
{
    public function checkAndScale(): array
    {
        // TODO: Implement auto-scaling logic
        // - Monitor queue depth
        // - Check server CPU/memory
        // - Scale up if queue > threshold
        // - Scale down if underutilized
        // - Return scaling actions taken
    }
}
```

### Exercise 2: Traffic Shaper

Create a traffic shaping system:

```php
<?php
class TrafficShaper
{
    public function shapeTraffic(array $request): array
    {
        // TODO: Implement traffic shaping
        // - Rate limiting per user tier
        // - Request prioritization
        // - Queue overflow handling
        // - Fair usage policies
    }
}
```

### Exercise 3: Load Test Framework

Build a load testing framework:

```php
<?php
class LoadTester
{
    public function runLoadTest(array $config): array
    {
        // TODO: Implement load test
        // - Concurrent request generation
        // - Latency measurement
        // - Error rate tracking
        // - Resource utilization
        // - Generate performance report
    }
}
```

## Troubleshooting

**Queue backing up?**
- Add more workers
- Check for slow jobs blocking the queue
- Implement job timeouts
- Use priority queues for important requests

**High latency under load?**
- Check connection pooling
- Review timeout settings
- Implement caching for common requests
- Consider using faster models (Haiku) for simple tasks

**Rate limit errors?**
- Implement proper backoff and retry
- Use concurrency limiting
- Spread requests over time
- Contact Anthropic for higher limits

## Key Takeaways

- ✓ **Stateless Design**: Enable horizontal scaling by externalizing state
- ✓ **Queue-Based Processing**: Handle spiky traffic with asynchronous processing
- ✓ **Circuit Breakers**: Prevent cascading failures with automatic circuit breaking
- ✓ **Retry Logic**: Handle transient failures with exponential backoff
- ✓ **Concurrency Control**: Respect rate limits with semaphore-based limiting
- ✓ **Capacity Planning**: Calculate infrastructure needs using Little's Law
- ✓ **Connection Pooling**: Reuse HTTP connections for better performance
- ✓ **Priority Queues**: Serve important requests first

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="38"
  label="You've mastered scaling Claude applications!"
/>

---

Continue to [Chapter 39: Cost Optimization and Billing](/series/claude-php-developers/chapters/39-cost-optimization) to learn cost management strategies.

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
