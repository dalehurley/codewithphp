<?php

/**
 * Monitoring and Metrics Integration
 * 
 * Demonstrates observability patterns:
 * - Metrics collection (counters, gauges, histograms)
 * - Performance tracking
 * - Health checks
 * - Alerting thresholds
 * - Dashboard-ready metrics
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

// Example 1: Basic Metrics Collector
echo "=== Example 1: Basic Metrics Collector ===\n\n";

class MetricsCollector
{
    private array $metrics = [];

    public function increment(string $name, array $tags = []): void
    {
        $this->record($name, 1, 'counter', $tags);
    }

    public function gauge(string $name, float $value, array $tags = []): void
    {
        $this->record($name, $value, 'gauge', $tags);
    }

    public function histogram(string $name, float $value, array $tags = []): void
    {
        $this->record($name, $value, 'histogram', $tags);
    }

    public function timing(string $name, callable $fn, array $tags = []): mixed
    {
        $start = microtime(true);
        $result = $fn();
        $duration = (microtime(true) - $start) * 1000; // ms

        $this->histogram($name, $duration, $tags);
        return $result;
    }

    private function record(string $name, float $value, string $type, array $tags): void
    {
        $this->metrics[] = [
            'name' => $name,
            'value' => $value,
            'type' => $type,
            'tags' => $tags,
            'timestamp' => microtime(true),
        ];
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function flush(): void
    {
        // In production, this would send to monitoring system
        // (Prometheus, DataDog, CloudWatch, etc.)
        $this->metrics = [];
    }

    public function getSummary(): array
    {
        $summary = [
            'total_metrics' => count($this->metrics),
            'by_type' => [],
            'by_name' => [],
        ];

        foreach ($this->metrics as $metric) {
            $type = $metric['type'];
            $name = $metric['name'];

            $summary['by_type'][$type] = ($summary['by_type'][$type] ?? 0) + 1;
            $summary['by_name'][$name] = ($summary['by_name'][$name] ?? 0) + 1;
        }

        return $summary;
    }
}

$metrics = new MetricsCollector();

// Record various metrics
$metrics->increment('agent.requests', ['model' => 'claude-sonnet-4-5']);
$metrics->increment('agent.requests', ['model' => 'claude-sonnet-4-5']);
$metrics->gauge('agent.iterations', 4, ['task_type' => 'calculation']);
$metrics->histogram('agent.duration_ms', 2341, ['status' => 'success']);
$metrics->histogram('tool.duration_ms', 245, ['tool' => 'database']);

echo "Collected metrics: " . count($metrics->getMetrics()) . "\n";
echo "Summary:\n";
print_r($metrics->getSummary());
echo "\n";

// Example 2: Agent Performance Tracker
echo "=== Example 2: Agent Performance Tracker ===\n\n";

class AgentPerformanceTracker
{
    private array $requests = [];

    public function trackRequest(array $data): void
    {
        $this->requests[] = array_merge($data, [
            'timestamp' => microtime(true),
        ]);
    }

    public function getStats(): array
    {
        if (empty($this->requests)) {
            return ['message' => 'No data collected'];
        }

        $durations = array_column($this->requests, 'duration_ms');
        $tokens = array_column($this->requests, 'tokens');
        $iterations = array_column($this->requests, 'iterations');

        return [
            'total_requests' => count($this->requests),
            'success_rate' => $this->calculateSuccessRate(),
            'duration' => [
                'min' => min($durations),
                'max' => max($durations),
                'avg' => round(array_sum($durations) / count($durations), 2),
                'p95' => $this->percentile($durations, 95),
            ],
            'tokens' => [
                'min' => min($tokens),
                'max' => max($tokens),
                'avg' => round(array_sum($tokens) / count($tokens), 2),
                'total' => array_sum($tokens),
            ],
            'iterations' => [
                'min' => min($iterations),
                'max' => max($iterations),
                'avg' => round(array_sum($iterations) / count($iterations), 2),
            ],
        ];
    }

    private function calculateSuccessRate(): float
    {
        $successes = count(array_filter($this->requests, fn($r) => $r['success']));
        return round($successes / count($this->requests) * 100, 2);
    }

    private function percentile(array $values, int $percentile): float
    {
        sort($values);
        $index = ceil(count($values) * $percentile / 100) - 1;
        return $values[(int)$index];
    }
}

$tracker = new AgentPerformanceTracker();

// Simulate request tracking
$requests = [
    ['duration_ms' => 1234, 'tokens' => 456, 'iterations' => 3, 'success' => true],
    ['duration_ms' => 2341, 'tokens' => 801, 'iterations' => 4, 'success' => true],
    ['duration_ms' => 892, 'tokens' => 234, 'iterations' => 2, 'success' => true],
    ['duration_ms' => 3456, 'tokens' => 1234, 'iterations' => 6, 'success' => true],
    ['duration_ms' => 1567, 'tokens' => 567, 'iterations' => 3, 'success' => false],
];

foreach ($requests as $request) {
    $tracker->trackRequest($request);
}

echo "Performance Statistics:\n";
print_r($tracker->getStats());
echo "\n";

// Example 3: Health Check System
echo "=== Example 3: Health Check System ===\n\n";

class HealthChecker
{
    private array $checks = [];

    public function addCheck(string $name, callable $fn): void
    {
        $this->checks[$name] = $fn;
    }

    public function runChecks(): array
    {
        $results = [];
        $allHealthy = true;

        foreach ($this->checks as $name => $check) {
            try {
                $start = microtime(true);
                $healthy = $check();
                $duration = (microtime(true) - $start) * 1000;

                $results[$name] = [
                    'status' => $healthy ? 'healthy' : 'unhealthy',
                    'duration_ms' => round($duration, 2),
                ];

                if (!$healthy) {
                    $allHealthy = false;
                }
            } catch (\Exception $e) {
                $results[$name] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
                $allHealthy = false;
            }
        }

        return [
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => time(),
            'checks' => $results,
        ];
    }
}

$health = new HealthChecker();

// Add health checks
$health->addCheck('api', function () {
    // Check if API key is set
    return !empty(getenv('ANTHROPIC_API_KEY'));
});

$health->addCheck('memory', function () {
    // Check memory usage
    $usage = memory_get_usage(true);
    $limit = 128 * 1024 * 1024; // 128MB
    return $usage < $limit;
});

$health->addCheck('disk', function () {
    // Check disk space
    $free = disk_free_space(__DIR__);
    $required = 100 * 1024 * 1024; // 100MB
    return $free > $required;
});

echo "Health Check Results:\n";
print_r($health->runChecks());
echo "\n";

// Example 4: Alert Manager
echo "=== Example 4: Alert Manager ===\n\n";

class AlertManager
{
    private array $rules = [];
    private array $alerts = [];

    public function addRule(string $name, callable $condition, string $message): void
    {
        $this->rules[$name] = [
            'condition' => $condition,
            'message' => $message,
        ];
    }

    public function evaluate(array $metrics): array
    {
        $this->alerts = [];

        foreach ($this->rules as $name => $rule) {
            if ($rule['condition']($metrics)) {
                $this->alerts[] = [
                    'name' => $name,
                    'message' => $rule['message'],
                    'timestamp' => time(),
                    'severity' => $this->getSeverity($name),
                ];
            }
        }

        return $this->alerts;
    }

    private function getSeverity(string $name): string
    {
        if (str_contains($name, 'critical')) return 'critical';
        if (str_contains($name, 'error')) return 'error';
        if (str_contains($name, 'warning')) return 'warning';
        return 'info';
    }

    public function getActiveAlerts(): array
    {
        return $this->alerts;
    }
}

$alertManager = new AlertManager();

// Define alert rules
$alertManager->addRule(
    'high_error_rate',
    fn($m) => ($m['error_rate'] ?? 0) > 5,
    'Error rate is above 5%'
);

$alertManager->addRule(
    'slow_response_warning',
    fn($m) => ($m['avg_duration'] ?? 0) > 5000,
    'Average response time is above 5 seconds'
);

$alertManager->addRule(
    'token_usage_critical',
    fn($m) => ($m['tokens_per_request'] ?? 0) > 8000,
    'Token usage is critically high'
);

$alertManager->addRule(
    'iteration_warning',
    fn($m) => ($m['avg_iterations'] ?? 0) > 15,
    'Average iterations is unusually high'
);

// Test with different scenarios
$scenarios = [
    'healthy' => [
        'error_rate' => 1.5,
        'avg_duration' => 2000,
        'tokens_per_request' => 4000,
        'avg_iterations' => 5,
    ],
    'degraded' => [
        'error_rate' => 3.5,
        'avg_duration' => 6000,
        'tokens_per_request' => 6000,
        'avg_iterations' => 12,
    ],
    'critical' => [
        'error_rate' => 8.0,
        'avg_duration' => 8000,
        'tokens_per_request' => 9000,
        'avg_iterations' => 18,
    ],
];

foreach ($scenarios as $name => $metrics) {
    echo strtoupper($name) . " scenario:\n";
    $alerts = $alertManager->evaluate($metrics);

    if (empty($alerts)) {
        echo "  ✓ All systems normal\n";
    } else {
        foreach ($alerts as $alert) {
            echo "  ⚠ [{$alert['severity']}] {$alert['message']}\n";
        }
    }
    echo "\n";
}

// Example 5: Dashboard Metrics
echo "=== Example 5: Dashboard Metrics Format ===\n\n";

class DashboardMetrics
{
    public static function format(array $stats): array
    {
        return [
            'overview' => [
                'total_requests' => $stats['total_requests'] ?? 0,
                'success_rate' => ($stats['success_rate'] ?? 100) . '%',
                'avg_response_time' => ($stats['avg_duration'] ?? 0) . 'ms',
                'total_cost' => '$' . number_format(($stats['total_cost'] ?? 0), 2),
            ],
            'performance' => [
                'p50_latency' => ($stats['p50_latency'] ?? 0) . 'ms',
                'p95_latency' => ($stats['p95_latency'] ?? 0) . 'ms',
                'p99_latency' => ($stats['p99_latency'] ?? 0) . 'ms',
            ],
            'resources' => [
                'avg_tokens' => $stats['avg_tokens'] ?? 0,
                'avg_iterations' => $stats['avg_iterations'] ?? 0,
                'cache_hit_rate' => ($stats['cache_hit_rate'] ?? 0) . '%',
            ],
            'errors' => [
                'error_rate' => ($stats['error_rate'] ?? 0) . '%',
                'timeout_rate' => ($stats['timeout_rate'] ?? 0) . '%',
                'retry_rate' => ($stats['retry_rate'] ?? 0) . '%',
            ],
        ];
    }
}

$dashboardData = DashboardMetrics::format([
    'total_requests' => 1250,
    'success_rate' => 96.5,
    'avg_duration' => 2341,
    'total_cost' => 12.45,
    'p50_latency' => 1800,
    'p95_latency' => 4200,
    'p99_latency' => 6800,
    'avg_tokens' => 3421,
    'avg_iterations' => 4.2,
    'cache_hit_rate' => 45.3,
    'error_rate' => 3.5,
    'timeout_rate' => 0.8,
    'retry_rate' => 5.2,
]);

echo "Dashboard Metrics:\n";
print_r($dashboardData);

echo "\n✅ Monitoring and metrics examples completed!\n";
echo "\nKey Metrics to Track:\n";
echo "  • Request volume and success rate\n";
echo "  • Response time (p50, p95, p99)\n";
echo "  • Token usage and cost\n";
echo "  • Error and retry rates\n";
echo "  • Tool execution times\n";
echo "  • Iteration counts\n";
