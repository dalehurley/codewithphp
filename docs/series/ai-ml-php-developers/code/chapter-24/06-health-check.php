<?php

declare(strict_types=1);

header('Content-Type: application/json');

try {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    // Check Redis connectivity
    if (!$redis->ping()) {
        throw new RuntimeException('Redis ping failed');
    }

    // Get queue stats
    $queueDepth = $redis->lLen('ml:jobs');
    $retryCount = $redis->zCard('ml:jobs:retry');
    $failedCount = $redis->lLen('ml:jobs:failed');

    // Get worker metrics
    $workerKeys = $redis->keys('metrics:worker:*');
    $activeWorkers = 0;
    $totalProcessed = 0;

    foreach ($workerKeys as $key) {
        $metrics = json_decode($redis->get($key), true);

        // Consider worker active if metrics updated in last 2 minutes
        if ($metrics && time() - $metrics['timestamp'] < 120) {
            $activeWorkers++;
            $totalProcessed += $metrics['processed'];
        }
    }

    // Get cache stats
    $cacheHits = (int) ($redis->get('metrics:cache:hits') ?: 0);
    $cacheWrites = (int) ($redis->get('metrics:cache:writes') ?: 0);
    $cacheHitRate = $cacheHits + $cacheWrites > 0
        ? round(($cacheHits / ($cacheHits + $cacheWrites)) * 100, 2)
        : 0;

    // Determine health status
    $status = 'healthy';
    $warnings = [];

    if ($activeWorkers === 0) {
        $status = 'degraded';
        $warnings[] = 'No active workers detected';
    }

    if ($queueDepth > 100) {
        $status = 'degraded';
        $warnings[] = "Queue depth high: {$queueDepth}";
    }

    if ($failedCount > 50) {
        $warnings[] = "High failure count: {$failedCount}";
    }

    http_response_code($status === 'healthy' ? 200 : 503);

    echo json_encode([
        'status' => $status,
        'timestamp' => time(),
        'worker_id' => getenv('HOSTNAME') ?: gethostname(),
        'system' => [
            'active_workers' => $activeWorkers,
            'total_processed' => $totalProcessed,
        ],
        'queue' => [
            'depth' => $queueDepth,
            'retry_count' => $retryCount,
            'failed_count' => $failedCount,
        ],
        'cache' => [
            'hit_rate' => $cacheHitRate,
            'total_hits' => $cacheHits,
            'total_writes' => $cacheWrites,
        ],
        'warnings' => $warnings,
    ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'unhealthy',
        'error' => $e->getMessage(),
        'timestamp' => time(),
    ], JSON_PRETTY_PRINT);
}
