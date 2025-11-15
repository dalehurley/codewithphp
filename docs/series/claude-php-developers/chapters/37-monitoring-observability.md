---
title: "37: Monitoring and Observability"
description: "Implement comprehensive monitoring and observability for Claude applications: structured logging, metrics collection, distributed tracing, real-time dashboards, intelligent alerting, and integration with Sentry, Datadog, and Prometheus."
series: "claude-php-developers"
chapter: 37
order: 37
difficulty: "Advanced"
prerequisites:
  - "PHP 8.2+ installed"
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

**What You'll Learn:**
- Structured logging with context enrichment
- Key metrics: latency, tokens, costs, errors
- Distributed tracing for Claude requests
- Building real-time monitoring dashboards
- Intelligent alerting and incident response
- Integration with monitoring platforms
- Performance profiling and optimization
- Quality metrics for AI outputs

**Estimated Time**: 60-75 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **PHP 8.2+** with JSON and cURL extensions
- ✓ **Monolog** or similar logging library
- ✓ **Redis or similar** for metrics storage
- ✓ **Access to monitoring platforms** (optional but recommended)

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
            'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
            'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
            'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
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
    model: 'claude-sonnet-4-20250514',
    inputTokens: 150,
    userId: 'user-123',
    metadata: ['feature' => 'chatbot', 'session_id' => 'sess-456']
);

// Log response
$logger->logResponse(
    messageId: 'msg_abc123',
    model: 'claude-sonnet-4-20250514',
    inputTokens: 150,
    outputTokens: 300,
    duration: 2.5,
    stopReason: 'end_turn',
    userId: 'user-123'
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
                messageId: $response->id,
                model: $response->model,
                inputTokens: $response->usage->inputTokens,
                outputTokens: $response->usage->outputTokens,
                duration: $duration,
                stopReason: $response->stopReason,
                userId: $userId
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
$loggingMiddleware = new RequestLoggingMiddleware($logger);

$response = $loggingMiddleware->loggedRequest(
    claudeRequest: fn() => $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]),
    userId: 'user-123',
    context: ['feature' => 'support_bot', 'priority' => 'high']
);
```

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
    model: 'claude-sonnet-4-20250514',
    inputTokens: 150,
    outputTokens: 300,
    duration: 2.5,
    stopReason: 'end_turn',
    userId: 'user-123'
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
    'model' => 'claude-sonnet-4-20250514',
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
        .metric { display: flex; justify-content: space-between; align-items: center;
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
        // Latency chart
        new Chart(document.getElementById('latencyChart'), {
            type: 'bar',
            data: {
                labels: ['P50', 'P75', 'P95', 'P99'],
                datasets: [{
                    label: 'Latency (ms)',
                    data: [
                        <?= $data['performance']['latency']['p50'] ?>,
                        <?= $data['performance']['latency']['p75'] ?>,
                        <?= $data['performance']['latency']['p95'] ?>,
                        <?= $data['performance']['latency']['p99'] ?>
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
$sentry = new SentryIntegration(getenv('SENTRY_DSN'));

$response = $sentry->traceClaudeRequest(
    fn() => $client->messages()->create([...]),
    context: [
        'operation' => 'chatbot_response',
        'model' => 'claude-sonnet-4-20250514',
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
$datadog = new DatadogIntegration();

$startTime = microtime(true);

try {
    $response = $client->messages()->create([...]);

    $duration = microtime(true) - $startTime;

    $datadog->trackClaudeRequest(
        model: $response->model,
        inputTokens: $response->usage->inputTokens,
        outputTokens: $response->usage->outputTokens,
        duration: $duration,
        status: 'success'
    );

} catch (\Exception $e) {
    $duration = microtime(true) - $startTime;

    $datadog->trackClaudeRequest(
        model: 'unknown',
        inputTokens: 0,
        outputTokens: 0,
        duration: $duration,
        status: 'error'
    );

    throw $e;
}
```

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
    action: function($name, $metrics) {
        // Send to Slack
        $this->sendSlackAlert([
            'text' => "🚨 High Error Rate Alert",
            'attachments' => [[
                'color' => 'danger',
                'fields' => [
                    ['title' => 'Error Rate', 'value' => $metrics['error_rate'] . '%', 'short' => true],
                    ['title' => 'Threshold', 'value' => '5%', 'short' => true],
                ],
            ]],
        ]);
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

Build a custom dashboard showing your most important metrics:

```php
<?php
class CustomDashboard
{
    public function getBusinessMetrics(): array
    {
        // TODO: Implement dashboard showing:
        // - Customer satisfaction scores
        // - Response quality trends
        // - Cost per customer interaction
        // - Most common user intents
        // - Peak usage hours
    }
}
```

### Exercise 2: Anomaly Detection

Implement anomaly detection for unusual patterns:

```php
<?php
class AnomalyDetector
{
    public function detectAnomalies(string $metric, array $history): array
    {
        // TODO: Detect anomalies using:
        // - Statistical outliers (z-score)
        // - Sudden spikes or drops
        // - Unusual patterns
        // - Return anomalies with severity
    }
}
```

### Exercise 3: Performance Profiler

Create a detailed performance profiler:

```php
<?php
class PerformanceProfiler
{
    public function profileRequest(string $requestId): array
    {
        // TODO: Profile showing:
        // - Time spent in each component
        // - Database query times
        // - Claude API latency
        // - Caching effectiveness
        // - Bottleneck identification
    }
}
```

## Troubleshooting

**Metrics not appearing?**
- Check Redis connection
- Verify metric names are consistent
- Ensure TTL isn't too short for long-running queries
- Check for clock skew across servers

**High cardinality issues?**
- Limit tag values (don't use unbounded fields like user IDs)
- Use sampling for high-volume metrics
- Aggregate before storing

**Dashboard performance slow?**
- Implement caching for dashboard queries
- Use aggregated data instead of raw metrics
- Consider pre-computing common queries

## Key Takeaways

- ✓ **Structured Logging**: Use JSON format with rich context for searchability
- ✓ **Key Metrics**: Track latency, tokens, costs, errors, and quality
- ✓ **Distributed Tracing**: Understand request flow across services
- ✓ **Real-Time Dashboards**: Build actionable views of system health
- ✓ **Intelligent Alerts**: Detect issues proactively with smart thresholds
- ✓ **Platform Integration**: Leverage Sentry, Datadog, Prometheus for enterprise monitoring
- ✓ **Cost Monitoring**: Track spending in real-time to avoid budget overruns
- ✓ **Quality Metrics**: Monitor AI output quality, not just system performance

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="37"
  label="You've mastered monitoring and observability for Claude applications!"
/>

---

Continue to [Chapter 38: Scaling Claude Applications](/series/claude-php-developers/chapters/38-scaling-applications) to learn horizontal scaling strategies.

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
