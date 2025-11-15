<?php

declare(strict_types=1);

namespace App;

class Dashboard
{
    public function __construct(
        private MetricsCollector $metrics
    ) {}
    
    public function render(): array
    {
        $data = $this->metrics->getMetrics();
        
        return [
            'total_requests' => $this->countMetric($data, 'api.request'),
            'avg_response_time' => $this->avgMetric($data, 'api.response_time'),
            'error_rate' => $this->errorRate($data),
            'recent_metrics' => array_slice($data, -10)
        ];
    }
    
    private function countMetric(array $data, string $name): int
    {
        return count(array_filter($data, fn($m) => $m['name'] === $name));
    }
    
    private function avgMetric(array $data, string $name): float
    {
        $values = array_filter($data, fn($m) => $m['name'] === $name);
        if (empty($values)) return 0;
        return array_sum(array_column($values, 'value')) / count($values);
    }
    
    private function errorRate(array $data): float
    {
        $total = $this->countMetric($data, 'api.request');
        $errors = $this->countMetric($data, 'api.error');
        return $total > 0 ? $errors / $total : 0;
    }
}
