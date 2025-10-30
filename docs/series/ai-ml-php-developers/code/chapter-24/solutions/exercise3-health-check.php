<?php

declare(strict_types=1);

/**
 * Exercise 3 Solution: Advanced Health Check with Circuit Breaker
 * Detects degraded states before complete failure
 */

header('Content-Type: application/json');

final class AdvancedHealthCheck
{
    private const DEGRADED_THRESHOLD = 0.05; // 5%
    private const UNHEALTHY_THRESHOLD = 0.20; // 20%
    private const WARMUP_PERIOD = 60; // seconds
    private const SAMPLE_SIZE = 100;

    public function __construct(
        private readonly Redis $redis,
    ) {}

    public function check(): array
    {
        $startTime = microtime(true);

        // Get startup time
        $uptime = $this->getUptime();
        $isWarming = $uptime < self::WARMUP_PERIOD;

        // Collect metrics
        $errorRate = $this->calculateErrorRate();
        $avgResponseTime = $this->getAverageResponseTime();
        $queueDepth = $this->redis->lLen('ml:jobs');
        $activeWorkers = $this->countActiveWorkers();

        // Determine health status
        $status = $this->determineStatus($errorRate, $isWarming, $queueDepth, $activeWorkers);

        // Build response
        $response = [
            'status' => $status,
            'timestamp' => time(),
            'uptime_seconds' => $uptime,
            'is_warming_up' => $isWarming,
            'metrics' => [
                'error_rate' => round($errorRate * 100, 2) . '%',
                'avg_response_time_ms' => round($avgResponseTime * 1000, 2),
                'queue_depth' => $queueDepth,
                'active_workers' => $activeWorkers,
            ],
            'thresholds' => [
                'degraded_at' => (self::DEGRADED_THRESHOLD * 100) . '%',
                'unhealthy_at' => (self::UNHEALTHY_THRESHOLD * 100) . '%',
            ],
            'check_duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ];

        // Add warnings based on status
        if ($status === 'degraded' || $status === 'unhealthy') {
            $response['warnings'] = $this->getWarnings($errorRate, $queueDepth, $activeWorkers);
        }

        // Set appropriate HTTP status code
        http_response_code($this->getHttpCode($status));

        return $response;
    }

    public function recordRequest(bool $success, float $duration): void
    {
        $key = 'health:requests';

        // Store request result and duration
        $this->redis->lPush($key, json_encode([
            'success' => $success,
            'duration' => $duration,
            'timestamp' => microtime(true),
        ]));

        // Keep only last 100 samples
        $this->redis->lTrim($key, 0, self::SAMPLE_SIZE - 1);

        // Track error count for last minute
        if (!$success) {
            $minuteKey = 'health:errors:' . floor(time() / 60);
            $this->redis->incr($minuteKey);
            $this->redis->expire($minuteKey, 120); // Keep for 2 minutes
        }
    }

    private function calculateErrorRate(): float
    {
        $samples = $this->redis->lRange('health:requests', 0, -1);

        if (empty($samples)) {
            return 0.0;
        }

        $errors = 0;
        foreach ($samples as $sample) {
            $data = json_decode($sample, true);
            if (!$data['success']) {
                $errors++;
            }
        }

        return $errors / count($samples);
    }

    private function getAverageResponseTime(): float
    {
        $samples = $this->redis->lRange('health:requests', 0, -1);

        if (empty($samples)) {
            return 0.0;
        }

        $totalDuration = 0;
        foreach ($samples as $sample) {
            $data = json_decode($sample, true);
            $totalDuration += $data['duration'];
        }

        return $totalDuration / count($samples);
    }

    private function getUptime(): int
    {
        $startTime = (int) $this->redis->get('health:start_time');

        if (!$startTime) {
            $startTime = time();
            $this->redis->set('health:start_time', $startTime);
        }

        return time() - $startTime;
    }

    private function countActiveWorkers(): int
    {
        $workerKeys = $this->redis->keys('metrics:worker:*');
        $active = 0;

        foreach ($workerKeys as $key) {
            $metrics = json_decode($this->redis->get($key), true);
            if ($metrics && time() - $metrics['timestamp'] < 120) {
                $active++;
            }
        }

        return $active;
    }

    private function determineStatus(
        float $errorRate,
        bool $isWarming,
        int $queueDepth,
        int $activeWorkers
    ): string {
        if ($isWarming) {
            return 'warming_up';
        }

        if ($activeWorkers === 0) {
            return 'unhealthy';
        }

        if ($errorRate >= self::UNHEALTHY_THRESHOLD) {
            return 'unhealthy';
        }

        if ($errorRate >= self::DEGRADED_THRESHOLD || $queueDepth > 100) {
            return 'degraded';
        }

        return 'healthy';
    }

    private function getWarnings(float $errorRate, int $queueDepth, int $activeWorkers): array
    {
        $warnings = [];

        if ($errorRate >= self::DEGRADED_THRESHOLD) {
            $warnings[] = sprintf('High error rate: %.1f%%', $errorRate * 100);
        }

        if ($queueDepth > 100) {
            $warnings[] = "Queue depth high: {$queueDepth}";
        }

        if ($activeWorkers < 2) {
            $warnings[] = "Low worker count: {$activeWorkers}";
        }

        return $warnings;
    }

    private function getHttpCode(string $status): int
    {
        return match ($status) {
            'healthy', 'warming_up' => 200,
            'degraded' => 200, // Still accepting traffic but warning
            'unhealthy' => 503,
            default => 500,
        };
    }

    public function getPrometheusMetrics(): string
    {
        $errorRate = $this->calculateErrorRate();
        $avgResponseTime = $this->getAverageResponseTime();
        $queueDepth = $this->redis->lLen('ml:jobs');

        $metrics = [];
        $metrics[] = '# HELP ml_service_error_rate Error rate (0-1)';
        $metrics[] = '# TYPE ml_service_error_rate gauge';
        $metrics[] = "ml_service_error_rate {$errorRate}";

        $metrics[] = '# HELP ml_service_response_time_seconds Average response time';
        $metrics[] = '# TYPE ml_service_response_time_seconds gauge';
        $metrics[] = "ml_service_response_time_seconds {$avgResponseTime}";

        $metrics[] = '# HELP ml_service_queue_depth Current queue depth';
        $metrics[] = '# TYPE ml_service_queue_depth gauge';
        $metrics[] = "ml_service_queue_depth {$queueDepth}";

        return implode("\n", $metrics) . "\n";
    }
}

// Handle requests
try {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    $healthCheck = new AdvancedHealthCheck($redis);

    // Check if requesting Prometheus format
    if (isset($_GET['format']) && $_GET['format'] === 'prometheus') {
        header('Content-Type: text/plain');
        echo $healthCheck->getPrometheusMetrics();
    } else {
        $result = $healthCheck->check();
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'unhealthy',
        'error' => 'Health check failed',
        'timestamp' => time(),
    ], JSON_PRETTY_PRINT);
}
