<?php

declare(strict_types=1);

namespace DataScience\Memory;

class MemoryMonitor
{
    private int $startMemory;
    private int $peakMemory;
    private float $startTime;
    
    public function __construct()
    {
        $this->startMemory = memory_get_usage();
        $this->peakMemory = memory_get_peak_usage();
        $this->startTime = microtime(true);
    }
    
    /**
     * Get current memory usage
     */
    public function getCurrentUsage(): string
    {
        $bytes = memory_get_usage();
        return $this->formatBytes($bytes);
    }
    
    /**
     * Get peak memory usage
     */
    public function getPeakUsage(): string
    {
        $bytes = memory_get_peak_usage();
        return $this->formatBytes($bytes);
    }
    
    /**
     * Get memory used since start
     */
    public function getMemoryDelta(): string
    {
        $delta = memory_get_usage() - $this->startMemory;
        return $this->formatBytes($delta);
    }
    
    /**
     * Get elapsed time
     */
    public function getElapsedTime(): float
    {
        return round(microtime(true) - $this->startTime, 3);
    }
    
    /**
     * Print memory report
     */
    public function report(string $label = ''): void
    {
        $prefix = $label ? "{$label}: " : '';
        
        echo "{$prefix}Memory: {$this->getCurrentUsage()} " .
             "(Peak: {$this->getPeakUsage()}, " .
             "Delta: {$this->getMemoryDelta()}, " .
             "Time: {$this->getElapsedTime()}s)\n";
    }
    
    /**
     * Format bytes to human-readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Check if memory usage is acceptable
     */
    public function isMemoryAcceptable(int $maxMB = 128): bool
    {
        $currentMB = memory_get_usage() / 1024 / 1024;
        return $currentMB < $maxMB;
    }
    
    /**
     * Get memory limit in bytes
     */
    public function getMemoryLimitBytes(): int
    {
        $limit = ini_get('memory_limit');
        return $this->parseMemoryLimit($limit);
    }
    
    /**
     * Get percentage of memory limit used
     */
    public function getPercentageUsed(): float
    {
        $current = memory_get_usage();
        $limit = $this->getMemoryLimitBytes();
        
        if ($limit === -1) {
            return 0.0; // No limit
        }
        
        return ($current / $limit) * 100;
    }
    
    /**
     * Check if memory usage is near limit
     */
    public function isNearLimit(int $thresholdPercent = 80): bool
    {
        return $this->getPercentageUsed() >= $thresholdPercent;
    }
    
    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        
        if ($limit === '-1') {
            return -1; // No limit
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int)$limit,
        };
    }
}


