<?php

declare(strict_types=1);

namespace App;

class PerformanceOptimizer
{
    private array $cache = [];
    
    public function memoize(callable $fn): callable
    {
        return function(...$args) use ($fn) {
            $key = md5(serialize($args));
            
            if (!isset($this->cache[$key])) {
                $this->cache[$key] = $fn(...$args);
            }
            
            return $this->cache[$key];
        };
    }
    
    public function batchRequests(array $requests, int $batchSize = 10): array
    {
        $results = [];
        $batches = array_chunk($requests, $batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $request) {
                $results[] = $request();
            }
        }
        
        return $results;
    }
    
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
