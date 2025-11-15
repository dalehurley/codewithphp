<?php

declare(strict_types=1);

namespace App;

class CircuitBreaker
{
    private int $failures = 0;
    private string $state = 'closed'; // closed, open, half-open
    private ?float $openedAt = null;
    
    public function __construct(
        private int $threshold = 5,
        private int $timeout = 60
    ) {}
    
    public function call(callable $action)
    {
        if ($this->state === 'open') {
            if (time() - $this->openedAt >= $this->timeout) {
                $this->state = 'half-open';
            } else {
                throw new \RuntimeException('Circuit breaker is open');
            }
        }
        
        try {
            $result = $action();
            $this->onSuccess();
            return $result;
        } catch (\Throwable $e) {
            $this->onFailure();
            throw $e;
        }
    }
    
    private function onSuccess(): void
    {
        $this->failures = 0;
        $this->state = 'closed';
    }
    
    private function onFailure(): void
    {
        $this->failures++;
        
        if ($this->failures >= $this->threshold) {
            $this->state = 'open';
            $this->openedAt = time();
        }
    }
    
    public function getState(): string
    {
        return $this->state;
    }
}
