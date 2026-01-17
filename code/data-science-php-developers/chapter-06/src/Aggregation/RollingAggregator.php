<?php

declare(strict_types=1);

namespace DataScience\Aggregation;

use Generator;

/**
 * Calculate rolling window statistics on streaming data
 */
class RollingAggregator
{
    private int $windowSize;
    private array $window = [];
    
    public function __construct(int $windowSize)
    {
        if ($windowSize <= 0) {
            throw new \InvalidArgumentException("Window size must be positive");
        }
        
        $this->windowSize = $windowSize;
    }
    
    /**
     * Process stream and yield statistics for each window
     */
    public function process(Generator $stream): Generator
    {
        foreach ($stream as $key => $value) {
            // Add to window
            $this->window[] = $value;
            
            // Remove old values if window is full
            if (count($this->window) > $this->windowSize) {
                array_shift($this->window);
            }
            
            // Yield statistics for current window
            yield $key => $this->calculateStats();
        }
    }
    
    /**
     * Calculate statistics for current window
     */
    private function calculateStats(): array
    {
        if (empty($this->window)) {
            return [
                'count' => 0,
                'mean' => 0,
                'min' => 0,
                'max' => 0,
                'sum' => 0,
            ];
        }
        
        $count = count($this->window);
        $sum = array_sum($this->window);
        $mean = $sum / $count;
        
        return [
            'count' => $count,
            'mean' => $mean,
            'min' => min($this->window),
            'max' => max($this->window),
            'sum' => $sum,
            'std_dev' => $this->calculateStdDev($mean),
        ];
    }
    
    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(float $mean): float
    {
        if (count($this->window) <= 1) {
            return 0.0;
        }
        
        $variance = 0;
        foreach ($this->window as $value) {
            $variance += ($value - $mean) ** 2;
        }
        
        return sqrt($variance / count($this->window));
    }
    
    /**
     * Detect anomalies based on standard deviation
     */
    public function detectAnomalies(
        Generator $stream,
        float $threshold = 2.0
    ): Generator {
        foreach ($this->process($stream) as $key => $stats) {
            $currentValue = end($this->window);
            $deviation = abs($currentValue - $stats['mean']);
            
            if ($stats['std_dev'] > 0) {
                $zScore = $deviation / $stats['std_dev'];
                
                if ($zScore > $threshold) {
                    yield $key => [
                        'value' => $currentValue,
                        'z_score' => $zScore,
                        'stats' => $stats,
                        'is_anomaly' => true,
                    ];
                }
            }
        }
    }
}
