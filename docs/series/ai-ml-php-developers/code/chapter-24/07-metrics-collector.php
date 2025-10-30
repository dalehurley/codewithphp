<?php

declare(strict_types=1);

/**
 * Metrics collector for system-wide statistics
 */
final class MetricsCollector
{
    public function __construct(
        private readonly Redis $redis,
    ) {}

    /**
     * Record a prediction request
     */
    public function recordRequest(string $endpoint, float $duration, bool $cached = false): void
    {
        $minute = floor(time() / 60) * 60;  // Round to minute

        // Increment request counter
        $this->redis->incr("metrics:requests:total");
        $this->redis->incr("metrics:requests:minute:{$minute}");

        // Track endpoint usage
        $this->redis->zIncrBy("metrics:endpoints", 1, $endpoint);

        // Record latency
        $this->redis->rPush("metrics:latency:recent", (string) $duration);
        $this->redis->lTrim("metrics:latency:recent", -1000, -1);  // Keep last 1000

        // Track cache performance
        if ($cached) {
            $this->redis->incr("metrics:requests:cached");
        }

        // Set TTL on minute-level metrics
        $this->redis->expire("metrics:requests:minute:{$minute}", 3600);
    }

    /**
     * Record a prediction result
     */
    public function recordPrediction(string $modelName, float $inferenceTime, bool $success = true): void
    {
        $key = "metrics:model:{$modelName}";

        if ($success) {
            $this->redis->hIncrBy($key, 'predictions', 1);
            $this->redis->rPush("{$key}:inference_times", (string) $inferenceTime);
            $this->redis->lTrim("{$key}:inference_times", -1000, -1);
        } else {
            $this->redis->hIncrBy($key, 'errors', 1);
        }

        $this->redis->expire($key, 86400);  // 24 hours
    }

    /**
     * Get current metrics snapshot
     */
    public function getSnapshot(): array
    {
        // Calculate requests per minute
        $currentMinute = floor(time() / 60) * 60;
        $rpm = (int) $this->redis->get("metrics:requests:minute:{$currentMinute}") ?: 0;

        // Calculate average latency
        $latencies = $this->redis->lRange("metrics:latency:recent", 0, -1);
        $avgLatency = !empty($latencies)
            ? array_sum(array_map('floatval', $latencies)) / count($latencies)
            : 0;

        // Get top endpoints
        $endpoints = $this->redis->zRevRange("metrics:endpoints", 0, 4, true);

        // Get cache hit rate
        $totalRequests = (int) $this->redis->get("metrics:requests:total") ?: 0;
        $cachedRequests = (int) $this->redis->get("metrics:requests:cached") ?: 0;
        $cacheHitRate = $totalRequests > 0
            ? round(($cachedRequests / $totalRequests) * 100, 2)
            : 0;

        return [
            'timestamp' => time(),
            'requests' => [
                'total' => $totalRequests,
                'per_minute' => $rpm,
                'cached_percent' => $cacheHitRate,
            ],
            'performance' => [
                'avg_latency_ms' => round($avgLatency * 1000, 2),
                'recent_samples' => count($latencies),
            ],
            'top_endpoints' => $endpoints,
        ];
    }

    /**
     * Get model-specific metrics
     */
    public function getModelMetrics(string $modelName): array
    {
        $key = "metrics:model:{$modelName}";

        $predictions = (int) $this->redis->hGet($key, 'predictions') ?: 0;
        $errors = (int) $this->redis->hGet($key, 'errors') ?: 0;

        $inferenceTimes = $this->redis->lRange("{$key}:inference_times", 0, -1);
        $avgInference = !empty($inferenceTimes)
            ? array_sum(array_map('floatval', $inferenceTimes)) / count($inferenceTimes)
            : 0;

        return [
            'model' => $modelName,
            'total_predictions' => $predictions,
            'errors' => $errors,
            'error_rate' => $predictions > 0 ? round(($errors / $predictions) * 100, 2) : 0,
            'avg_inference_time_ms' => round($avgInference * 1000, 2),
        ];
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    $metrics = new MetricsCollector($redis);

    // Simulate some activity
    for ($i = 0; $i < 10; $i++) {
        $metrics->recordRequest('/api/predict', random_int(100, 500) / 1000);
        $metrics->recordPrediction('classifier-v1', random_int(200, 800) / 1000);
    }

    // Get snapshot
    $snapshot = $metrics->getSnapshot();
    echo "Metrics Snapshot:\n";
    echo json_encode($snapshot, JSON_PRETTY_PRINT) . "\n";

    // Get model metrics
    $modelMetrics = $metrics->getModelMetrics('classifier-v1');
    echo "\nModel Metrics:\n";
    echo json_encode($modelMetrics, JSON_PRETTY_PRINT) . "\n";
}
