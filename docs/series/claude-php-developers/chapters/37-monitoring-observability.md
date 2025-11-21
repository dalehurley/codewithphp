---
title: "37: Monitoring and Observability"
description: "Implement comprehensive monitoring and observability for Claude applications: structured logging, metrics collection, distributed tracing, real-time dashboards, intelligent alerting, and integration with Sentry, Datadog, and Prometheus."
series: "claude-php-developers"
chapter: 37
order: 37
difficulty: "Advanced"
prerequisites:
  - "PHP 8.4+ installed"
  - "Understanding of logging and metrics"
  - "Completion of Chapter 36"
---

![37: Monitoring and Observability](/images/claude-php/chapter-37-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 37</span>
</div>

# Chapter 37: Monitoring and Observability

## Overview

Production AI applications require comprehensive monitoring to ensure reliability, performance, cost efficiency, and rapid incident response. Unlike traditional applications, Claude integrations have unique monitoring requirements: token usage tracking, latency optimization, model performance analysis, cost attribution, and quality assurance for AI outputs.

This chapter teaches you to build robust observability into your Claude applications. You'll implement structured logging, collect meaningful metrics, set up distributed tracing, create actionable dashboards, configure intelligent alerts, and integrate with popular monitoring platforms like Sentry, Datadog, and Prometheus.

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.4+** with JSON and cURL extensions
- ✓ **Monolog** or similar logging library
- ✓ **Redis or similar** for metrics storage
- ✓ **Access to monitoring platforms** (optional but recommended)
- ✓ **Completion of Chapter 36** or equivalent understanding of security best practices

**Estimated Time**: ~60-75 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Verify Redis is running
redis-cli ping

# Check if Monolog is available
composer show monolog/monolog
```

## What You'll Build

By the end of this chapter, you will have created:

- A complete structured logging system with JSON formatting and context enrichment
- A metrics collection infrastructure using Redis for time-series data
- A distributed tracing system to track requests across services
- Real-time monitoring dashboards with performance, cost, and quality metrics
- Intelligent alerting system with configurable rules and cooldowns
- Integration examples for Sentry, Datadog, and Prometheus
- A comprehensive monitoring solution ready for production deployment

## Objectives

By completing this chapter, you will:

- Understand how to implement structured logging with Monolog for Claude applications
- Learn to collect and analyze key metrics: latency, tokens, costs, errors, and quality
- Master distributed tracing to understand request flow across multiple services
- Build real-time dashboards that provide actionable insights
- Configure intelligent alerting systems with proper thresholds and cooldowns
- Integrate with popular monitoring platforms (Sentry, Datadog, Prometheus)
- Monitor AI-specific metrics like token usage, cost attribution, and output quality

## Structured Logging

Structured logging provides searchable, analyzable log data essential for debugging and monitoring AI applications.

### Logging Infrastructure

```php
<?php
# filename: src/Logging/ClaudeLogger.php
declare(strict_types=1);

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;

class ClaudeLogger
{
    private Logger $logger;

    public function __construct(string $name = 'claude-app')
    {
        $this->logger = new Logger($name);

        // File handler with JSON formatting
        $fileHandler = new RotatingFileHandler(
            filename: '/var/log/app/claude.log',
            maxFiles: 30,
            level: Logger::INFO
        );
        $fileHandler->setFormatter(new JsonFormatter());

        // Error handler for critical issues
        $errorHandler = new StreamHandler(
            stream: '/var/log/app/claude-errors.log',
            level: Logger::ERROR
        );
        $errorHandler->setFormatter(new JsonFormatter());

        $this->logger->pushHandler($fileHandler);
        $this->logger->pushHandler($errorHandler);

        // Add contextual processors
        $this->logger->pushProcessor(new WebProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushProcessor([$this, 'addGlobalContext']);
    }

    public function addGlobalContext(array $record): array
    {
        $record['extra']['environment'] = getenv('APP_ENV') ?: 'production';
        $record['extra']['server'] = gethostname();
        $record['extra']['app_version'] = getenv('APP_VERSION') ?: 'unknown';

        return $record;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Log Claude API request
     */
    public function logRequest(
        string $model,
        int $inputTokens,
        string $userId,
        array $metadata = []
    ): void {
        $this->logger->info('claude.request.started', [
            'model' => $model,
            'input_tokens' => $inputTokens,
            'user_id' => $userId,
            'metadata' => $metadata,
            'timestamp' => microtime(true),
        ]);
    }

    /**
     * Log Claude API response
     */
    public function logResponse(
        string $messageId,
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $duration,
        string $stopReason,
        ?string $userId = null
    ): void {
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);

        $this->logger->info('claude.request.completed', [
            'message_id' => $messageId,
            'model' => $model,
            'tokens' => [
                'input' => $inputTokens,
                'output' => $outputTokens,
                'total' => $inputTokens + $outputTokens,
            ],
            'cost' => $cost,
            'duration_ms' => round($duration * 1000, 2),
            'tokens_per_second' => round($outputTokens / $duration, 2),
            'stop_reason' => $stopReason,
            'user_id' => $userId,
            'timestamp' => microtime(true),
        ]);
    }

    /**
     * Log errors with full context
     */
    public function logError(
        \Throwable $error,
        string $context,
        array $additionalData = []
    ): void {
        $this->logger->error('claude.error', [
            'error_type' => get_class($error),
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $context,
            'additional_data' => $additionalData,
        ]);
    }

    /**
     * Log quality metrics
     */
    public function logQuality(
        string $messageId,
        float $relevanceScore,
        float $coherenceScore,
        bool $userSatisfied,
        ?string $feedback = null
    ): void {
        $this->logger->info('claude.quality', [
            'message_id' => $messageId,
            'scores' => [
                'relevance' => $relevanceScore,
                'coherence' => $coherenceScore,
                'average' => ($relevanceScore + $coherenceScore) / 2,
            ],
            'user_satisfied' => $userSatisfied,
            'feedback' => $feedback,
        ]);
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): array
    {
        $pricing = match($model) {
            'claude-opus-4-1' => ['input' => 15.00, 'output' => 75.00],
            'claude-sonnet-4-5-20250929' => ['input' => 3.00, 'output' => 15.00],
            'claude-haiku-4-5-20251001' => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

        return [
            'input' => $inputCost,
            'output' => $outputCost,
            'total' => $inputCost + $outputCost,
        ];
    }
}

// Usage
$logger = new ClaudeLogger();

// Log request
$logger->logRequest(
    'claude-sonnet-4-5-20250929',
    150,
    'user-123',
    ['feature' => 'chatbot', 'session_id' => 'sess-456']
);

// Log response
$logger->logResponse(
    'msg_abc123',
    'claude-sonnet-4-5-20250929',
    150,
    300,
    2.5,
    'end_turn',
    'user-123'
);
```

### Request Logging Middleware

```php
<?php
# filename: src/Logging/RequestLoggingMiddleware.php
declare(strict_types=1);

namespace App\Logging;

class RequestLoggingMiddleware
{
    public function __construct(
        private readonly ClaudeLogger $logger
    ) {}

    /**
     * Wrap Claude requests with automatic logging
     */
    public function loggedRequest(
        callable $claudeRequest,
        string $userId,
        array $context = []
    ): mixed {
        $requestId = $this->generateRequestId();
        $startTime = microtime(true);

        // Log request start
        $this->logger->getLogger()->info('claude.request.initiated', [
            'request_id' => $requestId,
            'user_id' => $userId,
            'context' => $context,
        ]);

        try {
            $response = $claudeRequest();

            $duration = microtime(true) - $startTime;

            // Log successful response
            $this->logger->logResponse(
                $response->id,
                $response->model,
                $response->usage->inputTokens,
                $response->usage->outputTokens,
                $duration,
                $response->stopReason,
                $userId
            );

            return $response;

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;

            // Log error
            $this->logger->logError($e, 'claude_request_failed', [
                'request_id' => $requestId,
                'user_id' => $userId,
                'duration_ms' => round($duration * 1000, 2),
            ]);

            throw $e;
        }
    }

    private function generateRequestId(): string
    {
        return bin2hex(random_bytes(16));
    }
}

// Usage
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$loggingMiddleware = new RequestLoggingMiddleware($logger);

$response = $loggingMiddleware->loggedRequest(
    claudeRequest: fn() => $client->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]),
    userId: 'user-123',
    context: ['feature' => 'support_bot', 'priority' => 'high']
);
```

**Why It Works**: The middleware pattern wraps Claude API calls with automatic logging, ensuring every request is tracked without modifying the core business logic. By generating a unique request ID and capturing timing information, you can trace requests through your system and correlate logs with metrics. The try-catch ensures errors are logged with full context before re-throwing, maintaining error propagation while preserving observability.

## Metrics Collection

Track key performance indicators to understand your Claude application's health and performance.

### Metrics Collector

```php
<?php
# filename: src/Metrics/MetricsCollector.php
declare(strict_types=1);

namespace App\Metrics;

class MetricsCollector
{
    public function __construct(
        private readonly \Redis $redis
    ) {}

    /**
     * Record a metric value
     */
    public function record(string $metric, float $value, array $tags = []): void
    {
        $timestamp = time();
        $key = $this->buildKey($metric, $tags);

        // Store time-series data
        $this->redis->zAdd(
            $key,
            ['NX'],
            $timestamp,
            json_encode(['value' => $value, 'timestamp' => $timestamp])
        );

        // Keep only last 24 hours
        $oneDayAgo = $timestamp - 86400;
        $this->redis->zRemRangeByScore($key, '-inf', $oneDayAgo);

        // Update aggregates
        $this->updateAggregates($metric, $value, $tags);
    }

    /**
     * Increment a counter
     */
    public function increment(string $metric, int $amount = 1, array $tags = []): void
    {
        $key = $this->buildKey($metric, $tags) . ':counter';
        $this->redis->incrBy($key, $amount);
    }

    /**
     * Record a histogram value (for latency, token counts, etc.)
     */
    public function histogram(string $metric, float $value, array $tags = []): void
    {
        $key = $this->buildKey($metric, $tags) . ':histogram';

        // Store in sorted set for percentile calculations
        $this->redis->zAdd(
            $key,
            ['NX'],
            $value,
            json_encode(['value' => $value, 'timestamp' => time()])
        );

        // Keep last 10,000 values
        $count = $this->redis->zCard($key);
        if ($count > 10000) {
            $this->redis->zRemRangeByRank($key, 0, $count - 10001);
        }
    }

    /**
     * Get metric statistics
     */
    public function getStats(string $metric, array $tags = []): array
    {
        $key = $this->buildKey($metric, $tags);

        $values = $this->redis->zRange($key, 0, -1);
        $parsedValues = array_map(fn($v) => json_decode($v, true)['value'], $values);

        if (empty($parsedValues)) {
            return [
                'count' => 0,
                'sum' => 0,
                'avg' => 0,
                'min' => 0,
                'max' => 0,
            ];
        }

        return [
            'count' => count($parsedValues),
            'sum' => array_sum($parsedValues),
            'avg' => array_sum($parsedValues) / count($parsedValues),
            'min' => min($parsedValues),
            'max' => max($parsedValues),
        ];
    }

    /**
     * Get percentiles for histogram
     */
    public function getPercentiles(string $metric, array $percentiles = [50, 95, 99], array $tags = []): array
    {
        $key = $this->buildKey($metric, $tags) . ':histogram';
        $count = $this->redis->zCard($key);

        if ($count === 0) {
            return array_fill_keys($percentiles, 0);
        }

        $results = [];

        foreach ($percentiles as $percentile) {
            $rank = (int) ceil(($percentile / 100) * $count) - 1;
            $value = $this->redis->zRange($key, $rank, $rank);

            $results["p$percentile"] = !empty($value)
                ? json_decode($value[0], true)['value']
                : 0;
        }

        return $results;
    }

    private function buildKey(string $metric, array $tags): string
    {
        $tagString = empty($tags) ? '' : ':' . implode(':', array_map(
            fn($k, $v) => "$k=$v",
            array_keys($tags),
            $tags
        ));

        return "metrics:$metric$tagString";
    }

    private function updateAggregates(string $metric, float $value, array $tags): void
    {
        $hourKey = $this->buildKey($metric, $tags) . ':hour:' . date('Y-m-d-H');
        $dayKey = $this->buildKey($metric, $tags) . ':day:' . date('Y-m-d');

        // Update hourly aggregate
        $this->redis->hIncrByFloat($hourKey, 'sum', $value);
        $this->redis->hIncrBy($hourKey, 'count', 1);
        $this->redis->expire($hourKey, 172800); // 2 days

        // Update daily aggregate
        $this->redis->hIncrByFloat($dayKey, 'sum', $value);
        $this->redis->hIncrBy($dayKey, 'count', 1);
        $this->redis->expire($dayKey, 2592000); // 30 days
    }
}

// Usage
$metrics = new MetricsCollector($redis);

// Record request duration
$metrics->histogram('claude.request.duration', 2.5, ['model' => 'sonnet']);

// Record token usage
$metrics->record('claude.tokens.input', 150, ['model' => 'sonnet', 'user' => 'user-123']);
$metrics->record('claude.tokens.output', 300, ['model' => 'sonnet', 'user' => 'user-123']);

// Increment error counter
$metrics->increment('claude.errors', 1, ['type' => 'rate_limit']);

// Get statistics
$stats = $metrics->getStats('claude.request.duration', ['model' => 'sonnet']);
$percentiles = $metrics->getPercentiles('claude.request.duration', [50, 95, 99], ['model' => 'sonnet']);
```

**Why It Works**: Redis sorted sets (`zAdd`) provide efficient time-series storage where timestamps serve as scores, enabling fast range queries and automatic sorting. The histogram implementation uses sorted sets with values as scores, allowing percentile calculations by rank position. Aggregates are stored in Redis hashes for fast O(1) lookups, while TTL ensures old data is automatically cleaned up. This design balances query performance with storage efficiency, making it suitable for high-volume metric collection.

### Metrics Categorization

When deciding what to monitor, categorize metrics into three tiers:

**Tier 1: Critical Metrics** (Always track)
- Request success/failure rate
- API latency (p50, p95, p99)
- Total cost per day
- Error rate by type

**Tier 2: Important Metrics** (Track for optimization)
- Token usage by model and user
- Quality scores (relevance, coherence)
- Model selection distribution
- Cache hit rates

**Tier 3: Optional Metrics** (Track for deep analysis)
- Processing time by component
- Queue depth and age
- Specific user behavior patterns
- Detailed model performance metrics

**Why This Matters**: Tracking every possible metric creates noise and increases storage costs. Focus on Tier 1 metrics for real-time alerts, Tier 2 for optimization, and Tier 3 only when investigating specific issues. This prevents "alert fatigue" and keeps your observability system performant.

```php
<?php
// Example: Categorizing metrics
$criticalMetrics = [
    'claude.requests.success_rate',    // % of successful requests
    'claude.request.duration_p95',     // 95th percentile latency
    'claude.cost.daily_total',         // Daily spending
    'claude.errors.rate_limit',        // Rate limit errors
];

$importantMetrics = [
    'claude.tokens.input_total',       // Input tokens for optimization
    'claude.tokens.output_total',      // Output tokens for analysis
    'claude.quality.relevance_avg',    // Quality measurement
    'claude.cache.hit_rate',           // Cache effectiveness
];

$optionalMetrics = [
    'claude.processing.db_time_ms',    // Component-level timing
    'claude.queue.depth',              // Queue monitoring
    'claude.user.intent_distribution', // User behavior analysis
];
```

### Key Metrics to Track

```php
<?php
# filename: src/Metrics/ClaudeMetrics.php
declare(strict_types=1);

namespace App\Metrics;

class ClaudeMetrics
{
    public function __construct(
        private readonly MetricsCollector $metrics
    ) {}

    /**
     * Track request metrics
     */
    public function trackRequest(
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $duration,
        string $stopReason,
        ?string $userId = null
    ): void {
        $tags = ['model' => $this->simplifyModelName($model)];

        if ($userId) {
            $tags['user'] = $userId;
        }

        // Request count
        $this->metrics->increment('claude.requests.total', 1, $tags);

        // Duration
        $this->metrics->histogram('claude.request.duration', $duration, $tags);

        // Token usage
        $this->metrics->record('claude.tokens.input', $inputTokens, $tags);
        $this->metrics->record('claude.tokens.output', $outputTokens, $tags);
        $this->metrics->record('claude.tokens.total', $inputTokens + $outputTokens, $tags);

        // Throughput
        $tokensPerSecond = $duration > 0 ? $outputTokens / $duration : 0;
        $this->metrics->record('claude.throughput.tokens_per_second', $tokensPerSecond, $tags);

        // Cost
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
        $this->metrics->record('claude.cost.total', $cost, $tags);

        // Stop reason distribution
        $this->metrics->increment("claude.stop_reason.$stopReason", 1, $tags);
    }

    /**
     * Track errors
     */
    public function trackError(
        string $errorType,
        string $model,
        ?string $userId = null
    ): void {
        $tags = [
            'model' => $this->simplifyModelName($model),
            'error_type' => $errorType,
        ];

        if ($userId) {
            $tags['user'] = $userId;
        }

        $this->metrics->increment('claude.errors.total', 1, $tags);
    }

    /**
     * Track quality metrics
     */
    public function trackQuality(
        float $relevanceScore,
        float $coherenceScore,
        bool $userSatisfied,
        string $model
    ): void {
        $tags = ['model' => $this->simplifyModelName($model)];

        $this->metrics->record('claude.quality.relevance', $relevanceScore, $tags);
        $this->metrics->record('claude.quality.coherence', $coherenceScore, $tags);
        $this->metrics->increment(
            'claude.quality.satisfaction',
            $userSatisfied ? 1 : 0,
            $tags
        );
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData(string $timeRange = '1h'): array
    {
        return [
            'requests' => [
                'total' => $this->getMetricSum('claude.requests.total'),
                'by_model' => $this->getMetricsByTag('claude.requests.total', 'model'),
            ],
            'latency' => [
                'p50' => $this->metrics->getPercentiles('claude.request.duration', [50])['p50'],
                'p95' => $this->metrics->getPercentiles('claude.request.duration', [95])['p95'],
                'p99' => $this->metrics->getPercentiles('claude.request.duration', [99])['p99'],
            ],
            'tokens' => [
                'input' => $this->getMetricSum('claude.tokens.input'),
                'output' => $this->getMetricSum('claude.tokens.output'),
                'total' => $this->getMetricSum('claude.tokens.total'),
            ],
            'cost' => [
                'total' => $this->getMetricSum('claude.cost.total'),
                'by_model' => $this->getMetricsByTag('claude.cost.total', 'model'),
            ],
            'errors' => [
                'total' => $this->getMetricSum('claude.errors.total'),
                'by_type' => $this->getMetricsByTag('claude.errors.total', 'error_type'),
            ],
            'quality' => [
                'relevance_avg' => $this->metrics->getStats('claude.quality.relevance')['avg'],
                'coherence_avg' => $this->metrics->getStats('claude.quality.coherence')['avg'],
                'satisfaction_rate' => $this->calculateSatisfactionRate(),
            ],
        ];
    }

    private function simplifyModelName(string $model): string
    {
        return match(true) {
            str_contains($model, 'opus') => 'opus',
            str_contains($model, 'sonnet') => 'sonnet',
            str_contains($model, 'haiku') => 'haiku',
            default => 'unknown'
        };
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = match($this->simplifyModelName($model)) {
            'opus' => ['input' => 15.00, 'output' => 75.00],
            'sonnet' => ['input' => 3.00, 'output' => 15.00],
            'haiku' => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        return ($inputTokens / 1_000_000 * $pricing['input']) +
               ($outputTokens / 1_000_000 * $pricing['output']);
    }

    private function getMetricSum(string $metric): float
    {
        $stats = $this->metrics->getStats($metric);
        return $stats['sum'];
    }

    private function getMetricsByTag(string $metric, string $tag): array
    {
        // Implementation would query Redis for different tag values
        return []; // Placeholder
    }

    private function calculateSatisfactionRate(): float
    {
        $stats = $this->metrics->getStats('claude.quality.satisfaction');
        return $stats['count'] > 0 ? ($stats['sum'] / $stats['count']) * 100 : 0;
    }
}

// Usage
$claudeMetrics = new ClaudeMetrics($metrics);

// Track request
$claudeMetrics->trackRequest(
    'claude-sonnet-4-5-20250929',
    150,
    300,
    2.5,
    'end_turn',
    'user-123'
);

// Get dashboard data
$dashboard = $claudeMetrics->getDashboardData('1h');
```

## Distributed Tracing

Track requests across multiple services and understand the complete flow.

### Tracing Implementation

```php
<?php
# filename: src/Tracing/RequestTracer.php
declare(strict_types=1);

namespace App\Tracing;

class RequestTracer
{
    private array $spans = [];
    private ?string $traceId = null;
    private ?string $parentSpanId = null;

    public function startTrace(string $operationName, array $tags = []): string
    {
        $this->traceId = $this->generateId();
        return $this->startSpan($operationName, $tags);
    }

    public function startSpan(string $operationName, array $tags = []): string
    {
        $spanId = $this->generateId();

        $this->spans[$spanId] = [
            'trace_id' => $this->traceId,
            'span_id' => $spanId,
            'parent_span_id' => $this->parentSpanId,
            'operation_name' => $operationName,
            'start_time' => microtime(true),
            'tags' => $tags,
            'logs' => [],
        ];

        $this->parentSpanId = $spanId;

        return $spanId;
    }

    public function finishSpan(string $spanId, array $tags = []): void
    {
        if (!isset($this->spans[$spanId])) {
            return;
        }

        $this->spans[$spanId]['finish_time'] = microtime(true);
        $this->spans[$spanId]['duration'] = $this->spans[$spanId]['finish_time'] -
                                            $this->spans[$spanId]['start_time'];
        $this->spans[$spanId]['tags'] = array_merge(
            $this->spans[$spanId]['tags'],
            $tags
        );

        // Reset parent span ID
        $this->parentSpanId = $this->spans[$spanId]['parent_span_id'];
    }

    public function addLog(string $spanId, string $event, array $data = []): void
    {
        if (!isset($this->spans[$spanId])) {
            return;
        }

        $this->spans[$spanId]['logs'][] = [
            'timestamp' => microtime(true),
            'event' => $event,
            'data' => $data,
        ];
    }

    public function getTrace(): array
    {
        return [
            'trace_id' => $this->traceId,
            'spans' => array_values($this->spans),
            'total_duration' => $this->calculateTotalDuration(),
        ];
    }

    public function exportToJaeger(): void
    {
        // Export to Jaeger format
        $jaegerTrace = $this->convertToJaegerFormat();

        // Send to Jaeger collector
        // Implementation depends on Jaeger client library
    }

    private function generateId(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function calculateTotalDuration(): float
    {
        if (empty($this->spans)) {
            return 0;
        }

        $firstSpan = reset($this->spans);
        $lastSpan = end($this->spans);

        return ($lastSpan['finish_time'] ?? microtime(true)) -
               $firstSpan['start_time'];
    }

    private function convertToJaegerFormat(): array
    {
        // Convert internal format to Jaeger format
        return []; // Placeholder
    }
}

// Usage
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$tracer = new RequestTracer();

// Start trace
$traceId = $tracer->startTrace('claude_chatbot_request', [
    'user_id' => 'user-123',
    'session_id' => 'sess-456',
]);

// Database span
$dbSpan = $tracer->startSpan('fetch_conversation_history', ['database' => 'postgres']);
// ... fetch history ...
$tracer->finishSpan($dbSpan, ['rows_fetched' => 10]);

// Claude API span
$claudeSpan = $tracer->startSpan('claude_api_request', [
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
]);

try {
    $response = $client->messages()->create([...]);

    $tracer->addLog($claudeSpan, 'response_received', [
        'message_id' => $response->id,
        'tokens' => $response->usage->inputTokens + $response->usage->outputTokens,
    ]);

    $tracer->finishSpan($claudeSpan, [
        'status' => 'success',
        'tokens' => $response->usage->inputTokens + $response->usage->outputTokens,
    ]);

} catch (\Exception $e) {
    $tracer->addLog($claudeSpan, 'error', [
        'error_type' => get_class($e),
        'message' => $e->getMessage(),
    ]);

    $tracer->finishSpan($claudeSpan, [
        'status' => 'error',
        'error' => true,
    ]);

    throw $e;
}

// Cache span
$cacheSpan = $tracer->startSpan('cache_response', ['cache' => 'redis']);
// ... cache response ...
$tracer->finishSpan($cacheSpan);

// Get complete trace
$trace = $tracer->getTrace();
```

## Real-Time Dashboards

Create actionable dashboards to monitor your Claude application.

### Dashboard Data Provider

```php
<?php
# filename: src/Dashboard/DashboardProvider.php
declare(strict_types=1);

namespace App\Dashboard;

use App\Metrics\ClaudeMetrics;
use App\Logging\ClaudeLogger;

class DashboardProvider
{
    public function __construct(
        private readonly ClaudeMetrics $metrics,
        private readonly \Redis $redis
    ) {}

    /**
     * Get real-time dashboard data
     */
    public function getRealTimeDashboard(): array
    {
        return [
            'overview' => $this->getOverview(),
            'performance' => $this->getPerformanceMetrics(),
            'costs' => $this->getCostMetrics(),
            'quality' => $this->getQualityMetrics(),
            'errors' => $this->getErrorMetrics(),
            'alerts' => $this->getActiveAlerts(),
        ];
    }

    private function getOverview(): array
    {
        return [
            'requests_last_hour' => $this->getHourlyRequestCount(),
            'requests_last_24h' => $this->getDailyRequestCount(),
            'active_users' => $this->getActiveUserCount(),
            'avg_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'total_cost_today' => $this->getTotalCostToday(),
        ];
    }

    private function getPerformanceMetrics(): array
    {
        $percentiles = $this->metrics->getPercentiles(
            'claude.request.duration',
            [50, 75, 95, 99]
        );

        return [
            'latency' => [
                'p50' => round($percentiles['p50'] * 1000, 2),  // Convert to ms
                'p75' => round($percentiles['p75'] * 1000, 2),
                'p95' => round($percentiles['p95'] * 1000, 2),
                'p99' => round($percentiles['p99'] * 1000, 2),
            ],
            'throughput' => [
                'requests_per_minute' => $this->getRequestsPerMinute(),
                'tokens_per_second' => $this->getTokensPerSecond(),
            ],
            'by_model' => $this->getPerformanceByModel(),
        ];
    }

    private function getCostMetrics(): array
    {
        return [
            'today' => [
                'total' => $this->getTotalCostToday(),
                'by_model' => $this->getCostByModel('today'),
                'by_user' => $this->getTopCostUsers('today', 10),
            ],
            'this_month' => [
                'total' => $this->getTotalCostThisMonth(),
                'projection' => $this->getMonthlyProjection(),
            ],
            'budget' => [
                'daily_limit' => 500.00,
                'daily_spent' => $this->getTotalCostToday(),
                'daily_remaining' => 500.00 - $this->getTotalCostToday(),
                'monthly_limit' => 15000.00,
                'monthly_spent' => $this->getTotalCostThisMonth(),
            ],
        ];
    }

    private function getQualityMetrics(): array
    {
        $relevanceStats = $this->metrics->getStats('claude.quality.relevance');
        $coherenceStats = $this->metrics->getStats('claude.quality.coherence');

        return [
            'relevance' => [
                'average' => round($relevanceStats['avg'], 2),
                'min' => round($relevanceStats['min'], 2),
                'max' => round($relevanceStats['max'], 2),
            ],
            'coherence' => [
                'average' => round($coherenceStats['avg'], 2),
                'min' => round($coherenceStats['min'], 2),
                'max' => round($coherenceStats['max'], 2),
            ],
            'satisfaction_rate' => $this->getSatisfactionRate(),
        ];
    }

    private function getErrorMetrics(): array
    {
        return [
            'total_errors' => $this->getTotalErrors(),
            'error_rate' => $this->getErrorRate(),
            'by_type' => $this->getErrorsByType(),
            'recent_errors' => $this->getRecentErrors(10),
        ];
    }

    private function getActiveAlerts(): array
    {
        $alerts = [];

        // Check error rate
        $errorRate = $this->getErrorRate();
        if ($errorRate > 5.0) {
            $alerts[] = [
                'severity' => 'high',
                'type' => 'error_rate',
                'message' => "Error rate is $errorRate% (threshold: 5%)",
                'timestamp' => time(),
            ];
        }

        // Check latency
        $p95 = $this->metrics->getPercentiles('claude.request.duration', [95])['p95'];
        if ($p95 > 5.0) {
            $alerts[] = [
                'severity' => 'medium',
                'type' => 'high_latency',
                'message' => "P95 latency is " . round($p95, 2) . "s (threshold: 5s)",
                'timestamp' => time(),
            ];
        }

        // Check daily cost
        $dailyCost = $this->getTotalCostToday();
        if ($dailyCost > 450) {
            $alerts[] = [
                'severity' => 'high',
                'type' => 'budget',
                'message' => "Daily cost is $" . round($dailyCost, 2) . " (limit: $500)",
                'timestamp' => time(),
            ];
        }

        return $alerts;
    }

    // Helper methods (implementations would query actual data)
    private function getHourlyRequestCount(): int { return 0; }
    private function getDailyRequestCount(): int { return 0; }
    private function getActiveUserCount(): int { return 0; }
    private function getAverageResponseTime(): float { return 0.0; }
    private function getErrorRate(): float { return 0.0; }
    private function getTotalCostToday(): float { return 0.0; }
    private function getRequestsPerMinute(): float { return 0.0; }
    private function getTokensPerSecond(): float { return 0.0; }
    private function getPerformanceByModel(): array { return []; }
    private function getCostByModel(string $period): array { return []; }
    private function getTopCostUsers(string $period, int $limit): array { return []; }
    private function getTotalCostThisMonth(): float { return 0.0; }
    private function getMonthlyProjection(): float { return 0.0; }
    private function getSatisfactionRate(): float { return 0.0; }
    private function getTotalErrors(): int { return 0; }
    private function getErrorsByType(): array { return []; }
    private function getRecentErrors(int $limit): array { return []; }
}
```

### HTML Dashboard

```php
<?php
# filename: public/dashboard.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$dashboardProvider = new App\Dashboard\DashboardProvider($metrics, $redis);
$data = $dashboardProvider->getRealTimeDashboard();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claude Monitoring Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
               background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { margin-bottom: 30px; color: #333; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { font-size: 18px; margin-bottom: 15px; color: #666; }
        .metric { display: flex; justify-'content' => space-between; align-items: center;
                  padding: 10px 0; border-bottom: 1px solid #eee; }
        .metric:last-child { border-bottom: none; }
        .metric-label { color: #666; }
        .metric-value { font-size: 24px; font-weight: bold; color: #333; }
        .alert { padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-high { background: #fee; border-left: 4px solid #d00; }
        .alert-medium { background: #ffe; border-left: 4px solid #f90; }
        .chart-container { height: 300px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Claude Monitoring Dashboard</h1>

        <!-- Alerts -->
        <?php if (!empty($data['alerts'])): ?>
            <div class="card">
                <h2>Active Alerts</h2>
                <?php foreach ($data['alerts'] as $alert): ?>
                    <div class="alert alert-<?= $alert['severity'] ?>">
                        <strong><?= ucfirst($alert['type']) ?>:</strong>
                        <?= htmlspecialchars($alert['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Overview -->
        <div class="grid">
            <div class="card">
                <h2>Requests</h2>
                <div class="metric">
                    <span class="metric-label">Last Hour</span>
                    <span class="metric-value"><?= number_format($data['overview']['requests_last_hour']) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Last 24h</span>
                    <span class="metric-value"><?= number_format($data['overview']['requests_last_24h']) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Active Users</span>
                    <span class="metric-value"><?= number_format($data['overview']['active_users']) ?></span>
                </div>
            </div>

            <div class="card">
                <h2>Performance</h2>
                <div class="metric">
                    <span class="metric-label">Avg Response</span>
                    <span class="metric-value"><?= number_format($data['overview']['avg_response_time'], 2) ?>s</span>
                </div>
                <div class="metric">
                    <span class="metric-label">Error Rate</span>
                    <span class="metric-value"><?= number_format($data['overview']['error_rate'], 2) ?>%</span>
                </div>
            </div>

            <div class="card">
                <h2>Costs</h2>
                <div class="metric">
                    <span class="metric-label">Today</span>
                    <span class="metric-value">$<?= number_format($data['overview']['total_cost_today'], 2) ?></span>
                </div>
                <div class="metric">
                    <span class="metric-label">Monthly Budget</span>
                    <span class="metric-value">
                        $<?= number_format($data['costs']['this_month']['total'], 2) ?> /
                        $<?= number_format($data['costs']['budget']['monthly_limit'], 2) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Latency Chart -->
        <div class="card">
            <h2>Latency Distribution</h2>
            <div class="chart-container">
                <canvas id="latencyChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        <?= "
        // Latency chart
        new Chart(document.getElementById('latencyChart'), {
            type: 'bar',
            data: {
                labels: ['P50', 'P75', 'P95', 'P99'],
                datasets: [{
                    label: 'Latency (ms)',
                    data: [
                        {$data['performance']['latency']['p50']},
                        {$data['performance']['latency']['p75']},
                        {$data['performance']['latency']['p95']},
                        {$data['performance']['latency']['p99']}
                    ],
                    backgroundColor: ['#4CAF50', '#8BC34A', '#FFC107', '#FF5722']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Auto-refresh every 30 seconds
        setTimeout(() => location.reload(), 30000);
        " ?>
    </script>
</body>
</html>
```

## Platform Integrations

### Sentry Integration

```php
<?php
# filename: src/Monitoring/SentryIntegration.php
declare(strict_types=1);

namespace App\Monitoring;

class SentryIntegration
{
    public function __construct(
        private readonly string $dsn
    ) {
        \Sentry\init([
            'dsn' => $this->dsn,
            'traces_sample_rate' => 0.1,  // 10% of transactions
            'environment' => getenv('APP_ENV') ?: 'production',
        ]);
    }

    /**
     * Capture Claude request as Sentry transaction
     */
    public function traceClaudeRequest(callable $request, array $context = []): mixed
    {
        $transaction = \Sentry\startTransaction([
            'op' => 'claude.request',
            'name' => $context['operation'] ?? 'claude_api_call',
        ]);

        \Sentry\SentrySdk::getCurrentHub()->setSpan($transaction);

        try {
            $result = $request();

            $transaction->setStatus(\Sentry\Tracing\SpanStatus::ok());
            $transaction->setData($context);

            return $result;

        } catch (\Throwable $e) {
            $transaction->setStatus(\Sentry\Tracing\SpanStatus::internalError());

            \Sentry\captureException($e, [
                'tags' => [
                    'component' => 'claude_api',
                    'model' => $context['model'] ?? 'unknown',
                ],
                'extra' => $context,
            ]);

            throw $e;

        } finally {
            $transaction->finish();
        }
    }
}

// Usage
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$sentry = new SentryIntegration(getenv('SENTRY_DSN'));

$response = $sentry->traceClaudeRequest(
    fn() => $client->messages()->create([...]),
    context: [
        'operation' => 'chatbot_response',
        'model' => 'claude-sonnet-4-5-20250929',
        'user_id' => 'user-123',
    ]
);
```

### Datadog Integration

```php
<?php
# filename: src/Monitoring/DatadogIntegration.php
declare(strict_types=1);

namespace App\Monitoring;

use DataDog\DogStatsd;

class DatadogIntegration
{
    private DogStatsd $statsd;

    public function __construct(string $host = 'localhost', int $port = 8125)
    {
        $this->statsd = new DogStatsd([
            'host' => $host,
            'port' => $port,
            'global_tags' => [
                'env:' . (getenv('APP_ENV') ?: 'production'),
                'service:claude-app',
            ],
        ]);
    }

    /**
     * Send Claude metrics to Datadog
     */
    public function trackClaudeRequest(
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $duration,
        string $status = 'success'
    ): void {
        $tags = [
            "model:$model",
            "status:$status",
        ];

        // Request count
        $this->statsd->increment('claude.requests', 1, $tags);

        // Duration
        $this->statsd->timing('claude.duration', $duration * 1000, $tags);  // Convert to ms

        // Tokens
        $this->statsd->histogram('claude.tokens.input', $inputTokens, $tags);
        $this->statsd->histogram('claude.tokens.output', $outputTokens, $tags);

        // Cost
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
        $this->statsd->histogram('claude.cost', $cost, $tags);
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = match(true) {
            str_contains($model, 'opus') => ['input' => 15.00, 'output' => 75.00],
            str_contains($model, 'sonnet') => ['input' => 3.00, 'output' => 15.00],
            str_contains($model, 'haiku') => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        return ($inputTokens / 1_000_000 * $pricing['input']) +
               ($outputTokens / 1_000_000 * $pricing['output']);
    }
}

// Usage
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$datadog = new DatadogIntegration();

$startTime = microtime(true);

try {
    $response = $client->messages()->create([...]);

    $duration = microtime(true) - $startTime;

    $datadog->trackClaudeRequest(
        $response->model,
        $response->usage->inputTokens,
        $response->usage->outputTokens,
        $duration,
        'success'
    );

} catch (\Exception $e) {
    $duration = microtime(true) - $startTime;

    $datadog->trackClaudeRequest(
        'unknown',
        0,
        0,
        $duration,
        'error'
    );

    throw $e;
}
```

### Prometheus Integration

```php
<?php
# filename: src/Monitoring/PrometheusIntegration.php
declare(strict_types=1);

namespace App\Monitoring;

class PrometheusIntegration
{
    private array $counters = [];
    private array $histograms = [];
    private string $namespace = 'claude_app';

    /**
     * Increment a counter metric
     */
    public function incrementCounter(
        string $name,
        array $labels = [],
        float $value = 1.0
    ): void {
        $key = $this->buildKey($name, $labels);
        $this->counters[$key] = ($this->counters[$key] ?? 0) + $value;
    }

    /**
     * Observe a histogram value
     */
    public function observeHistogram(
        string $name,
        float $value,
        array $labels = []
    ): void {
        $key = $this->buildKey($name, $labels);
        if (!isset($this->histograms[$key])) {
            $this->histograms[$key] = [];
        }
        $this->histograms[$key][] = $value;
    }

    /**
     * Track Claude request metrics
     */
    public function trackClaudeRequest(
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $duration,
        string $status = 'success'
    ): void {
        $labels = [
            'model' => $this->simplifyModelName($model),
            'status' => $status,
        ];

        // Request counter
        $this->incrementCounter('claude_requests_total', $labels);

        // Duration histogram
        $this->observeHistogram('claude_request_duration_seconds', $duration, $labels);

        // Token histograms
        $this->observeHistogram('claude_tokens_input', $inputTokens, $labels);
        $this->observeHistogram('claude_tokens_output', $outputTokens, $labels);

        // Cost
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
        $this->observeHistogram('claude_cost_usd', $cost, $labels);
    }

    /**
     * Export metrics in Prometheus format
     */
    public function exportMetrics(): string
    {
        $output = [];

        // Export counters
        foreach ($this->counters as $key => $value) {
            [$name, $labels] = $this->parseKey($key);
            $labelString = $this->formatLabels($labels);
            $output[] = "# TYPE {$this->namespace}_{$name} counter";
            $output[] = "{$this->namespace}_{$name}{$labelString} {$value}";
        }

        // Export histograms
        foreach ($this->histograms as $key => $values) {
            [$name, $labels] = $this->parseKey($key);
            $labelString = $this->formatLabels($labels);
            
            $count = count($values);
            $sum = array_sum($values);
            
            $output[] = "# TYPE {$this->namespace}_{$name} histogram";
            $output[] = "{$this->namespace}_{$name}_count{$labelString} {$count}";
            $output[] = "{$this->namespace}_{$name}_sum{$labelString} {$sum}";
            
            // Calculate buckets (simplified - in production use proper buckets)
            $buckets = [0.1, 0.5, 1.0, 2.5, 5.0, 10.0];
            foreach ($buckets as $bucket) {
                $bucketCount = count(array_filter($values, fn($v) => $v <= $bucket));
                $bucketLabels = $this->formatLabels(array_merge($labels, ['le' => (string)$bucket]));
                $output[] = "{$this->namespace}_{$name}_bucket{$bucketLabels} {$bucketCount}";
            }
            $infLabels = $this->formatLabels(array_merge($labels, ['le' => '+Inf']));
            $output[] = "{$this->namespace}_{$name}_bucket{$infLabels} {$count}";
        }

        return implode("\n", $output);
    }

    private function buildKey(string $name, array $labels): string
    {
        ksort($labels);
        $labelString = json_encode($labels);
        return "{$name}:{$labelString}";
    }

    private function parseKey(string $key): array
    {
        [$name, $labelJson] = explode(':', $key, 2);
        $labels = json_decode($labelJson, true);
        return [$name, $labels];
    }

    private function formatLabels(array $labels): string
    {
        if (empty($labels)) {
            return '';
        }

        $parts = [];
        foreach ($labels as $key => $value) {
            $parts[] = "{$key}=\"" . addslashes((string)$value) . "\"";
        }

        return '{' . implode(',', $parts) . '}';
    }

    private function simplifyModelName(string $model): string
    {
        return match(true) {
            str_contains($model, 'opus') => 'opus',
            str_contains($model, 'sonnet') => 'sonnet',
            str_contains($model, 'haiku') => 'haiku',
            default => 'unknown'
        };
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = match(true) {
            str_contains($model, 'opus') => ['input' => 15.00, 'output' => 75.00],
            str_contains($model, 'sonnet') => ['input' => 3.00, 'output' => 15.00],
            str_contains($model, 'haiku') => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        return ($inputTokens / 1_000_000 * $pricing['input']) +
               ($outputTokens / 1_000_000 * $pricing['output']);
    }
}

// Usage
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$prometheus = new PrometheusIntegration();

$startTime = microtime(true);

try {
    $response = $client->messages()->create([...]);
    $duration = microtime(true) - $startTime;

    $prometheus->trackClaudeRequest(
        $response->model,
        $response->usage->inputTokens,
        $response->usage->outputTokens,
        $duration,
        'success'
    );

} catch (\Exception $e) {
    $duration = microtime(true) - $startTime;

    $prometheus->trackClaudeRequest(
        'unknown',
        0,
        0,
        $duration,
        'error'
    );

    throw $e;
}

// Export metrics endpoint (e.g., /metrics)
// echo $prometheus->exportMetrics();
```

### ELK Stack Integration

```php
<?php
# filename: src/Monitoring/ElkStackIntegration.php
declare(strict_types=1);

namespace App\Monitoring;

use Elasticsearch\ClientBuilder;

class ElkStackIntegration
{
    private $elasticsearchClient;

    public function __construct(string $host = 'localhost', int $port = 9200)
    {
        $this->elasticsearchClient = ClientBuilder::create()
            ->setHosts(["{$host}:{$port}"])
            ->build();
    }

    /**
     * Send log to Elasticsearch
     */
    public function logEvent(
        string $index,
        array $document,
        ?string $documentId = null
    ): void {
        try {
            $params = [
                'index' => $index,
                'body' => $document,
            ];

            if ($documentId) {
                $params['id'] = $documentId;
            }

            $this->elasticsearchClient->index($params);
        } catch (\Exception $e) {
            error_log("Failed to send log to ELK: " . $e->getMessage());
        }
    }

    /**
     * Log Claude request to ELK
     */
    public function logClaudeRequest(
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $duration,
        string $status = 'success',
        ?string $userId = null,
        ?string $requestId = null
    ): void {
        $document = [
            'timestamp' => date('c'),
            'service' => 'claude-app',
            'event_type' => 'claude_request',
            'model' => $model,
            'tokens' => [
                'input' => $inputTokens,
                'output' => $outputTokens,
                'total' => $inputTokens + $outputTokens,
            ],
            'duration_ms' => round($duration * 1000, 2),
            'status' => $status,
            'cost' => $this->calculateCost($model, $inputTokens, $outputTokens),
        ];

        if ($userId) {
            $document['user_id'] = $userId;
        }

        $this->logEvent(
            "claude-requests-" . date('Y.m.d'),
            $document,
            $requestId
        );
    }

    /**
     * Query logs from Elasticsearch
     */
    public function queryLogs(
        string $index,
        array $query,
        int $limit = 50
    ): array {
        try {
            $params = [
                'index' => $index,
                'body' => [
                    'query' => $query,
                    'size' => $limit,
                    'sort' => ['timestamp' => ['order' => 'desc']],
                ],
            ];

            $results = $this->elasticsearchClient->search($params);

            return array_map(
                fn($hit) => array_merge(['id' => $hit['_id']], $hit['_source']),
                $results['hits']['hits']
            );
        } catch (\Exception $e) {
            error_log("Failed to query ELK: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get error logs for today
     */
    public function getErrorLogsForToday(string $level = 'error'): array
    {
        $today = date('Y-m-d');

        return $this->queryLogs(
            "claude-logs-" . date('Y.m.d'),
            [
                'bool' => [
                    'must' => [
                        ['term' => ['level' => $level]],
                        ['range' => ['timestamp' => ['gte' => "{$today}T00:00:00Z"]]],
                    ],
                ],
            ]
        );
    }

    /**
     * Get aggregated metrics
     */
    public function getMetricsAggregation(
        string $startDate,
        string $endDate
    ): array {
        $params = [
            'index' => "claude-requests-*",
            'body' => [
                'query' => [
                    'range' => [
                        'timestamp' => [
                            'gte' => "{$startDate}T00:00:00Z",
                            'lte' => "{$endDate}T23:59:59Z",
                        ],
                    ],
                ],
                'aggs' => [
                    'avg_duration' => ['avg' => ['field' => 'duration_ms']],
                    'total_tokens' => ['sum' => ['field' => 'tokens.total']],
                    'total_cost' => ['sum' => ['field' => 'cost']],
                    'by_model' => [
                        'terms' => ['field' => 'model.keyword'],
                        'aggs' => [
                            'avg_duration' => ['avg' => ['field' => 'duration_ms']],
                            'total_requests' => ['value_count' => ['field' => '_id']],
                        ],
                    ],
                    'by_status' => ['terms' => ['field' => 'status.keyword']],
                ],
            ],
        ];

        try {
            return $this->elasticsearchClient->search($params);
        } catch (\Exception $e) {
            error_log("Failed to aggregate metrics from ELK: " . $e->getMessage());
            return [];
        }
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = match(true) {
            str_contains($model, 'opus') => ['input' => 15.00, 'output' => 75.00],
            str_contains($model, 'sonnet') => ['input' => 3.00, 'output' => 15.00],
            str_contains($model, 'haiku') => ['input' => 0.25, 'output' => 1.25],
            default => ['input' => 0, 'output' => 0],
        };

        return ($inputTokens / 1_000_000 * $pricing['input']) +
               ($outputTokens / 1_000_000 * $pricing['output']);
    }
}

// Usage
$elk = new ElkStackIntegration('localhost', 9200);

// Log a request
$elk->logClaudeRequest(
    'claude-sonnet-4-5-20250929',
    200,
    400,
    2.5,
    'success',
    'user-123',
    'req_abc123'
);

// Query error logs
$errors = $elk->getErrorLogsForToday('error');
echo "Found " . count($errors) . " errors today\n";

// Get metrics
$metrics = $elk->getMetricsAggregation('2025-01-01', '2025-01-15');
```

**Why It Works**: Elasticsearch provides powerful full-text search and aggregation capabilities, making it ideal for log analysis at scale. The ELK Stack (Elasticsearch, Logstash, Kibana) enables searching across millions of log entries in milliseconds, creating custom dashboards, and setting up alerts based on complex queries. Unlike time-series databases optimized for metrics, Elasticsearch excels at analyzing and correlating log events with deep searchability.

## Intelligent Alerting

```php
<?php
# filename: src/Alerting/AlertManager.php
declare(strict_types=1);

namespace App\Alerting;

class AlertManager
{
    private array $alertRules = [];

    public function addRule(string $name, callable $condition, callable $action, int $cooldownSeconds = 300): void
    {
        $this->alertRules[$name] = [
            'condition' => $condition,
            'action' => $action,
            'cooldown' => $cooldownSeconds,
            'last_triggered' => 0,
        ];
    }

    public function checkAlerts(array $metrics): void
    {
        $now = time();

        foreach ($this->alertRules as $name => $rule) {
            // Check cooldown
            if ($now - $rule['last_triggered'] < $rule['cooldown']) {
                continue;
            }

            // Check condition
            if ($rule['condition']($metrics)) {
                // Trigger action
                $rule['action']($name, $metrics);

                // Update last triggered time
                $this->alertRules[$name]['last_triggered'] = $now;
            }
        }
    }
}

// Usage
$alertManager = new AlertManager();

// High error rate alert
$alertManager->addRule(
    name: 'high_error_rate',
    condition: fn($m) => $m['error_rate'] > 5.0,
    action: function($name, $metrics) use ($logger) {
        // Log alert
        $logger->getLogger()->warning('alert.triggered', [
            'alert_name' => $name,
            'error_rate' => $metrics['error_rate'],
            'threshold' => 5.0,
        ]);

        // Send to Slack (implement your own Slack integration)
        // $this->sendSlackAlert([...]);
    },
    cooldownSeconds: 600  // Don't spam - wait 10 minutes
);

// High cost alert
$alertManager->addRule(
    name: 'daily_cost_limit',
    condition: fn($m) => $m['daily_cost'] > 450,
    action: function($name, $metrics) {
        mail(
            'ops@example.com',
            'Claude API Cost Alert',
            "Daily cost has reached $" . $metrics['daily_cost'] . " (limit: $500)"
        );
    }
);

// Check alerts periodically
$dashboardData = $dashboardProvider->getRealTimeDashboard();
$alertManager->checkAlerts([
    'error_rate' => $dashboardData['overview']['error_rate'],
    'daily_cost' => $dashboardData['overview']['total_cost_today'],
]);
```

## Exercises

### Exercise 1: Custom Metrics Dashboard

**Goal**: Build a custom dashboard showing business-critical metrics for your Claude application.

Create a `CustomDashboard` class that implements:

- Customer satisfaction scores (average, trend over time)
- Response quality trends (relevance and coherence over last 7 days)
- Cost per customer interaction (total cost / total interactions)
- Most common user intents (top 10 intents by frequency)
- Peak usage hours (requests per hour of day)

**Validation**: Test your implementation:

```php
$dashboard = new CustomDashboard();
$metrics = $dashboard->getBusinessMetrics();

// Verify all required metrics are present
assert(isset($metrics['satisfaction']));
assert(isset($metrics['quality_trends']));
assert(isset($metrics['cost_per_interaction']));
assert(isset($metrics['top_intents']));
assert(isset($metrics['peak_hours']));

// Verify data types
assert(is_float($metrics['cost_per_interaction']));
assert(is_array($metrics['top_intents']));
assert(count($metrics['top_intents']) <= 10);
```

Expected output structure:

```php
[
    'satisfaction' => ['average' => 4.2, 'trend' => 'increasing'],
    'quality_trends' => ['relevance' => [...], 'coherence' => [...]],
    'cost_per_interaction' => 0.15,
    'top_intents' => [['intent' => 'support', 'count' => 150], ...],
    'peak_hours' => [['hour' => 14, 'requests' => 250], ...]
]
```

### Exercise 2: Anomaly Detection

**Goal**: Implement statistical anomaly detection to identify unusual patterns in metrics.

Create an `AnomalyDetector` class that detects anomalies using:

- Z-score calculation for statistical outliers (threshold: |z| > 2.5)
- Sudden spikes or drops (change > 50% from previous period)
- Unusual patterns (values outside 3 standard deviations)
- Return anomalies with severity levels: 'low', 'medium', 'high'

**Validation**: Test with sample data:

```php
$detector = new AnomalyDetector();

// Test with normal data
$normalHistory = [10, 11, 9, 12, 10, 11, 10];
$anomalies = $detector->detectAnomalies('test_metric', $normalHistory);
assert(empty($anomalies)); // Should find no anomalies

// Test with outlier
$outlierHistory = [10, 11, 9, 12, 10, 11, 100]; // 100 is an outlier
$anomalies = $detector->detectAnomalies('test_metric', $outlierHistory);
assert(!empty($anomalies));
assert($anomalies[0]['severity'] === 'high');
```

Expected output format:

```php
[
    [
        'metric' => 'test_metric',
        'value' => 100,
        'expected_range' => [8.5, 12.5],
        'z_score' => 8.2,
        'severity' => 'high',
        'timestamp' => 1234567890
    ]
]
```

### Exercise 3: Performance Profiler

**Goal**: Create a detailed performance profiler to identify bottlenecks in Claude requests.

Implement a `PerformanceProfiler` that tracks:

- Time spent in each component (database, cache, Claude API, processing)
- Database query times (individual queries and total)
- Claude API latency (request time, token generation time)
- Caching effectiveness (hit rate, time saved)
- Bottleneck identification (component taking > 30% of total time)

**Validation**: Profile a sample request:

```php
$profiler = new PerformanceProfiler();
$requestId = 'req_123';

// Start profiling
$profiler->startRequest($requestId);

// Simulate components
$profiler->startComponent('database');
usleep(100000); // 100ms
$profiler->endComponent('database');

$profiler->startComponent('claude_api');
usleep(500000); // 500ms
$profiler->endComponent('claude_api');

$profiler->endRequest($requestId);

// Get profile
$profile = $profiler->getProfile($requestId);

assert($profile['total_duration'] > 0);
assert(isset($profile['components']['database']));
assert(isset($profile['components']['claude_api']));
assert(isset($profile['bottlenecks']));
assert($profile['components']['claude_api']['duration'] > 
       $profile['components']['database']['duration']);
```

Expected output:

```php
[
    'request_id' => 'req_123',
    'total_duration' => 0.6,
    'components' => [
        'database' => ['duration' => 0.1, 'percentage' => 16.7],
        'claude_api' => ['duration' => 0.5, 'percentage' => 83.3],
    ],
    'bottlenecks' => ['claude_api'],
    'cache' => ['hit_rate' => 0.75, 'time_saved' => 0.2]
]
```

## Troubleshooting

### Metrics Not Appearing

**Symptom**: Metrics are being recorded but don't appear in dashboards or queries.

**Possible Causes**:
- Redis connection issues
- Metric names inconsistent between recording and querying
- TTL too short for long-running queries
- Clock skew across servers causing time-based queries to fail

**Solutions**:

```php
// Verify Redis connection
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
if (!$redis->ping()) {
    throw new \RuntimeException('Redis connection failed');
}

// Check metric exists
$key = 'metrics:claude.request.duration';
$exists = $redis->exists($key);
if (!$exists) {
    // Metric was never recorded or expired
}

// Verify TTL settings
$ttl = $redis->ttl($key);
if ($ttl < 0) {
    // Key exists but has no expiration - may need cleanup
}
```

### High Cardinality Issues

**Symptom**: Redis memory usage growing rapidly, queries slow down, or Redis crashes.

**Possible Causes**:
- Using unbounded tag values (like user IDs) creating unique metric keys
- Too many unique tag combinations
- Not aggregating metrics before storage

**Solutions**:

```php
// ❌ BAD - Creates unique key per user
$metrics->record('claude.requests', 1, ['user' => $userId]);

// ✅ GOOD - Aggregate by user type or remove user tag
$metrics->record('claude.requests', 1, ['user_type' => 'premium']);
// Or aggregate separately for top users only
if ($isTopUser) {
    $metrics->record('claude.requests', 1, ['user' => $userId]);
}

// Use sampling for high-volume metrics
if (rand(1, 100) <= 10) { // Sample 10%
    $metrics->record('claude.requests', 10, $tags);
}
```

### Dashboard Performance Slow

**Symptom**: Dashboard takes several seconds to load or times out.

**Possible Causes**:
- Querying raw metrics instead of aggregates
- No caching for dashboard queries
- Complex calculations on large datasets

**Solutions**:

```php
// ❌ BAD - Querying all raw metrics
$allMetrics = $redis->zRange('metrics:claude.request.duration', 0, -1);
$stats = calculateStats($allMetrics); // Slow!

// ✅ GOOD - Use pre-aggregated data
$hourlyStats = $redis->hGetAll('metrics:claude.request.duration:hour:2025-01-15-14');
// Already aggregated, fast!

// Add caching layer
$cacheKey = 'dashboard:overview:' . date('Y-m-d-H');
$data = $redis->get($cacheKey);
if (!$data) {
    $data = $dashboardProvider->getRealTimeDashboard();
    $redis->setex($cacheKey, 60, json_encode($data)); // Cache for 1 minute
}
```

### Logs Not Being Written

**Symptom**: Application runs but no log files are created.

**Possible Causes**:
- File permissions issues
- Disk space full
- Log directory doesn't exist
- Handler configuration incorrect

**Solutions**:

```bash
# Check directory exists and is writable
mkdir -p /var/log/app
chmod 755 /var/log/app
chown www-data:www-data /var/log/app

# Verify disk space
df -h /var/log

# Test logging manually
php -r "
require 'vendor/autoload.php';
\$logger = new Monolog\Logger('test');
\$logger->pushHandler(new Monolog\Handler\StreamHandler('/var/log/app/test.log'));
\$logger->info('Test message');
"
```

### Distributed Tracing Not Working

**Symptom**: Traces are created but spans are missing or incomplete.

**Possible Causes**:
- Spans not being finished properly
- Parent span ID tracking incorrect
- Trace context not propagated across services

**Solutions**:

```php
// Always use try-finally to ensure spans finish
$spanId = $tracer->startSpan('operation');
try {
    // ... operation ...
    $tracer->finishSpan($spanId, ['status' => 'success']);
} catch (\Exception $e) {
    $tracer->finishSpan($spanId, ['status' => 'error', 'error' => true]);
    throw $e;
}

// Propagate trace context across services
$traceContext = [
    'trace_id' => $tracer->getTraceId(),
    'span_id' => $currentSpanId,
];
// Include in HTTP headers or message queue metadata
```

### Alert Fatigue

**Symptom**: Too many alerts being triggered, causing important ones to be ignored.

**Possible Causes**:
- Cooldown periods too short
- Thresholds too sensitive
- Alerts not properly categorized by severity

**Solutions**:

```php
// Increase cooldown for non-critical alerts
$alertManager->addRule(
    name: 'minor_latency_increase',
    condition: fn($m) => $m['p95_latency'] > 3.0,
    action: $logAction,
    cooldownSeconds: 3600 // 1 hour cooldown
);

// Use different thresholds for different times
$hour = (int)date('H');
$threshold = ($hour >= 9 && $hour <= 17) ? 5.0 : 10.0; // Higher threshold off-hours

// Group related alerts
if ($errorRate > 5.0 && $latency > 5.0) {
    // Single alert for correlated issues
    triggerAlert('system_degradation', ['error_rate' => $errorRate, 'latency' => $latency]);
}
```

### Monitoring Overhead

**Symptom**: Application performance degrades when monitoring is enabled; high CPU/memory usage from monitoring systems.

**Possible Causes**:
- Sampling rate too high (logging every request)
- Aggregations computed too frequently
- Network latency sending metrics to remote systems
- Unoptimized metric cardinality

**Solutions**:

```php
// ❌ BAD - Sample every request (100%)
foreach ($requests as $request) {
    $metrics->histogram('request.duration', $request->duration);
}

// ✅ GOOD - Use adaptive sampling
$sampleRate = 0.1; // Sample 10% of requests
foreach ($requests as $request) {
    if (rand() / getrandmax() < $sampleRate) {
        $metrics->histogram('request.duration', $request->duration);
        // Scale metric by inverse of sample rate for accuracy
        $metrics->increment('requests.total', (int)(1 / $sampleRate));
    }
}

// ✅ GOOD - Use percentile sampling for high-traffic
$percentile = rand(0, 99);
if ($percentile < 5) { // Sample top 5% and bottom 5%
    $metrics->record('request.duration', $duration);
}

// ✅ GOOD - Batch metrics before sending
$batch = [];
foreach ($requests as $request) {
    $batch[] = [
        'metric' => 'claude.request.duration',
        'value' => $request->duration,
        'tags' => ['model' => $request->model],
    ];
}

// Send in batch (reduces network overhead)
$metricsCollector->recordBatch($batch);

// ✅ GOOD - Disable expensive metrics in production
if (getenv('APP_ENV') === 'production') {
    // Skip detailed query timing
    $profiler->disable('database.query_timing');
} else {
    // Enable all metrics for local development
    $profiler->enable('database.query_timing');
}
```

**Performance Impact Guide**:

```
Activity                    CPU Overhead    Memory    Network
- JSON logging             ~1-2%           +5MB      Low
- Redis metrics            ~2-3%           +10MB     Medium
- Distributed tracing      ~3-5%           +20MB     High
- All combined (no sample) ~8-12%          +50MB     High

With 10% sampling:         ~1-2%           +10MB     Low
With adaptive sampling:    ~0.5-1%         +5MB      Very Low
```

**Monitoring Checklist**:

- [ ] Sampling enabled for high-volume systems
- [ ] Batch metrics before sending to remote systems
- [ ] Async logging (non-blocking)
- [ ] Metrics aggregated at collection, not query time
- [ ] High-cardinality metrics sampled or disabled
- [ ] Monitoring overhead < 5% CPU in production
- [ ] Alert thresholds configured to reduce noise
- [ ] Old data archived/deleted to manage storage

## Data Retention and Archival

Production monitoring systems generate enormous amounts of data. Without a retention strategy, storage costs grow unbounded and queries become slower over time.

### Retention Strategy

```php
<?php
# filename: src/Monitoring/RetentionPolicy.php
declare(strict_types=1);

namespace App\Monitoring;

class RetentionPolicy
{
    /**
     * Define retention tiers
     */
    public function getRetentionTiers(): array
    {
        return [
            'raw_metrics' => [
                'duration' => 7 * 24 * 3600,  // 7 days
                'resolution' => '1 second',
                'storage' => 'Redis',
                'cost_factor' => 1.0,
            ],
            'hourly_aggregates' => [
                'duration' => 90 * 24 * 3600, // 90 days
                'resolution' => '1 hour',
                'storage' => 'TimescaleDB',
                'cost_factor' => 0.1,
            ],
            'daily_aggregates' => [
                'duration' => 2 * 365 * 24 * 3600, // 2 years
                'resolution' => '1 day',
                'storage' => 'Parquet (S3)',
                'cost_factor' => 0.01,
            ],
            'archived_logs' => [
                'duration' => 7 * 365 * 24 * 3600, // 7 years (compliance)
                'resolution' => 'raw',
                'storage' => 'Glacier',
                'cost_factor' => 0.001,
            ],
        ];
    }

    /**
     * Aggregate data before archival
     */
    public function aggregateForArchival(
        array $rawMetrics,
        string $period = '1 hour'
    ): array {
        $aggregated = [];

        foreach ($rawMetrics as $metric) {
            $key = $metric['timestamp'];
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'timestamp' => $metric['timestamp'],
                    'count' => 0,
                    'sum' => 0,
                    'min' => PHP_FLOAT_MAX,
                    'max' => PHP_FLOAT_MIN,
                    'p50' => 0,
                    'p95' => 0,
                    'p99' => 0,
                    'values' => [],
                ];
            }

            $aggregated[$key]['values'][] = $metric['value'];
            $aggregated[$key]['count']++;
            $aggregated[$key]['sum'] += $metric['value'];
            $aggregated[$key]['min'] = min($aggregated[$key]['min'], $metric['value']);
            $aggregated[$key]['max'] = max($aggregated[$key]['max'], $metric['value']);
        }

        // Calculate percentiles
        foreach ($aggregated as &$agg) {
            sort($agg['values']);
            $count = count($agg['values']);
            $agg['p50'] = $agg['values'][(int)($count * 0.50)];
            $agg['p95'] = $agg['values'][(int)($count * 0.95)];
            $agg['p99'] = $agg['values'][(int)($count * 0.99)];
            unset($agg['values']);
        }

        return $aggregated;
    }

    /**
     * Compress data for long-term storage
     */
    public function compressForArchival(array $data): string
    {
        $json = json_encode($data);
        // Zstandard compression ratio ~5:1
        return zstd_compress($json, 3);
    }

    /**
     * Calculate storage cost for retention policy
     */
    public function calculateStorageCost(
        float $dailyGbGenerated,
        int $costPerGbMonth = 25
    ): array {
        $tiers = $this->getRetentionTiers();
        $costs = [];
        $totalCost = 0;

        foreach ($tiers as $tier => $policy) {
            $durationDays = $policy['duration'] / 86400;
            $totalGb = $durationDays * $dailyGbGenerated;
            $cost = $totalGb * ($costPerGbMonth / 30) * $policy['cost_factor'];

            $costs[$tier] = [
                'total_gb' => $totalGb,
                'duration_days' => $durationDays,
                'monthly_cost' => $cost,
                'storage' => $policy['storage'],
            ];

            $totalCost += $cost;
        }

        $costs['total_monthly'] = $totalCost;

        return $costs;
    }
}

// Usage
$retention = new RetentionPolicy();

// Example: Calculate cost for system generating 1GB/day
$costs = $retention->calculateStorageCost(
    dailyGbGenerated: 1.0,
    costPerGbMonth: 25
);

echo "Monthly storage costs:\n";
foreach ($costs as $tier => $data) {
    if (is_string($tier) && $tier !== 'total_monthly') {
        echo sprintf(
            "  %s: $%.2f (%d GB, %d days)\n",
            $tier,
            $data['monthly_cost'],
            (int)$data['total_gb'],
            (int)$data['duration_days']
        );
    }
}

echo sprintf("\nTotal monthly: $%.2f\n", $costs['total_monthly']);
```

### Downsampling Strategies

```php
<?php
// Downsampling reduces storage while maintaining accuracy

// Strategy 1: Uniform sampling (every Nth point)
function uniformDownsample(array $metrics, int $sampleRate = 10): array
{
    return array_filter(
        $metrics,
        fn($i) => $i % $sampleRate === 0,
        ARRAY_FILTER_USE_KEY
    );
}

// Strategy 2: Extrema preservation (keep min/max)
function extremaDownsample(array $metrics, int $bucketSize = 60): array
{
    $downsampled = [];

    for ($i = 0; $i < count($metrics); $i += $bucketSize) {
        $bucket = array_slice($metrics, $i, $bucketSize);
        $values = array_column($bucket, 'value');

        $downsampled[] = [
            'timestamp' => $bucket[0]['timestamp'],
            'min' => min($values),
            'max' => max($values),
            'avg' => array_sum($values) / count($values),
        ];
    }

    return $downsampled;
}

// Strategy 3: Compression-aware (keep anomalies)
function anomalyDownsample(array $metrics, float $deviation = 2.0): array
{
    $mean = array_sum(array_column($metrics, 'value')) / count($metrics);
    $stdDev = sqrt(
        array_reduce(
            $metrics,
            fn($sum, $m) => $sum + pow($m['value'] - $mean, 2),
            0
        ) / count($metrics)
    );

    return array_filter(
        $metrics,
        fn($m) => abs($m['value'] - $mean) > $deviation * $stdDev
    );
}
```

**Why This Matters**: 

A production system generating just 1GB/day of metrics produces 365GB yearly. Raw metrics storage at $25/GB/month costs $9,125/year. By implementing tiered storage with downsampling:
- Keep raw metrics for 7 days (quick debugging)
- Store hourly aggregates for 90 days (trend analysis)
- Archive daily summaries for 2 years (compliance + business analysis)
- Archive audit logs for 7 years (compliance requirements)

This reduces yearly storage from $9,125 to under $1,000 while maintaining operational and compliance requirements.

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've built a comprehensive monitoring and observability system for your Claude applications. Here's what you've accomplished:

- ✓ **Structured Logging**: Implemented JSON-formatted logging with Monolog, including context enrichment and automatic request/response tracking
- ✓ **Metrics Collection**: Created a Redis-based metrics system tracking latency, tokens, costs, errors, and quality metrics
- ✓ **Distributed Tracing**: Built a tracing system to understand request flow across multiple services and identify bottlenecks
- ✓ **Real-Time Dashboards**: Developed actionable dashboards showing performance, costs, quality, and error metrics
- ✓ **Intelligent Alerting**: Configured alert rules with cooldowns to detect issues proactively without alert fatigue
- ✓ **Platform Integration**: Integrated with Sentry, Datadog, and Prometheus for enterprise-grade monitoring
- ✓ **Cost Monitoring**: Implemented real-time cost tracking to prevent budget overruns
- ✓ **Quality Metrics**: Added monitoring for AI-specific metrics like output quality and user satisfaction

**Key Concepts Learned:**

- Structured logging with JSON formatting enables searchable, analyzable log data
- Time-series metrics storage in Redis allows for efficient aggregation and querying
- ELK Stack provides deep log analysis with full-text search and complex aggregations
- Metrics categorization (Tier 1/2/3) reduces noise and focuses monitoring efforts
- Distributed tracing provides visibility into complex request flows across services
- Real-time dashboards transform raw metrics into actionable business insights
- Intelligent alerting balances proactive issue detection with manageable notification volume
- Platform integrations extend your monitoring capabilities with enterprise features
- Monitoring overhead can be controlled through sampling, batching, and selective metrics
- Tiered storage and downsampling reduce costs from $9K/year to under $1K while maintaining compliance

**Next Steps:**

Monitoring is essential for production AI applications. With this foundation, you can:
- Scale your monitoring as your application grows
- Add custom metrics specific to your use case
- Integrate with additional monitoring platforms
- Build automated responses to common alerts

In the next chapter, you'll learn about scaling Claude applications horizontally to handle increased load while maintaining performance and reliability.

## Further Reading

### Logging & Log Analysis
- [Monolog Documentation](https://github.com/Seldaek/monolog) — Comprehensive logging library for PHP
- [ELK Stack Guide](https://www.elastic.co/what-is/elk-stack) — Elasticsearch, Logstash, Kibana deployment
- [Structured Logging Best Practices](https://www.kartar.net/2015/12/structured-logging/) — JSON logging patterns

### Metrics & Time-Series
- [Prometheus Best Practices](https://prometheus.io/docs/practices/naming/) — Metric naming conventions
- [OpenTelemetry Specification](https://opentelemetry.io/docs/specs/) — Industry standard for observability
- [Time-Series Databases Explained](https://prometheus.io/docs/concepts/cardinality/) — High-cardinality metrics

### Sampling & Performance
- [Distributed Tracing Sampling](https://opentelemetry.io/docs/concepts/sampling/) — Sampling strategies for tracing
- [Metric Sampling Techniques](https://en.wikipedia.org/wiki/Sampling_(signal_processing)) — Statistical sampling methods

### Data Retention
- [Data Retention Policies](https://www.elastic.co/guide/en/elasticsearch/reference/current/index-lifecycle-management.html) — ILM for data archival
- [Downsampling Time-Series Data](https://prometheus.io/docs/prometheus/latest/storage/#compaction-and-retention) — Long-term storage optimization

### Platform Documentation
- [Sentry PHP Documentation](https://docs.sentry.io/platforms/php/) — Error tracking and performance monitoring
- [Datadog PHP Integration](https://docs.datadoghq.com/integrations/php/) — APM and metrics collection
- [The Three Pillars of Observability](https://www.oreilly.com/library/view/distributed-systems-observability/9781492033431/ch04.html) — Logging, metrics, and tracing

### Related Chapters
- [Chapter 36: Security Best Practices](/series/claude-php-developers/chapters/36-security-best-practices) — Security considerations for monitoring
- [Chapter 38: Scaling Claude Applications](/series/claude-php-developers/chapters/38-scaling-applications) — Next chapter on horizontal scaling
- [Chapter 39: Cost Optimization and Billing](/series/claude-php-developers/chapters/39-cost-optimization) — Cost tracking and ROI measurement

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="37"
  label="You've mastered monitoring and observability for Claude applications!"
/>

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 37 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-37)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-37
composer install
php examples/monitoring-demo.php
```
