<?php

declare(strict_types=1);

namespace App;

class MetricsCollector
{
    private array $metrics = [];
    
    public function record(string $name, $value, array $tags = []): void
    {
        $this->metrics[] = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => microtime(true)
        ];
    }
    
    public function increment(string $name, array $tags = []): void
    {
        $this->record($name, 1, array_merge($tags, ['type' => 'counter']));
    }
    
    public function timing(string $name, float $duration, array $tags = []): void
    {
        $this->record($name, $duration, array_merge($tags, ['type' => 'timing']));
    }
    
    public function gauge(string $name, $value, array $tags = []): void
    {
        $this->record($name, $value, array_merge($tags, ['type' => 'gauge']));
    }
    
    public function getMetrics(): array
    {
        return $this->metrics;
    }
    
    public function flush(): void
    {
        $this->metrics = [];
    }
}
