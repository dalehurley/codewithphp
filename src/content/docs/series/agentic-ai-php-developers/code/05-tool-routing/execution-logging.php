<?php

/**
 * Comprehensive Execution Logging
 * 
 * Demonstrates:
 * - Structured logging with context
 * - Execution lifecycle logging
 * - Performance metrics
 * - Error tracking
 * - Audit trails
 */

declare(strict_types=1);

// ============================================================================
// EXECUTION LOGGER
// ============================================================================

class ExecutionLogger
{
    private array $logs = [];
    
    /**
     * Log execution start.
     */
    public function logStart(string $executionId, string $toolName, array $input): void
    {
        $this->log('INFO', "Tool execution started", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'input_size' => strlen(json_encode($input)),
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log execution completion.
     */
    public function logComplete(
        string $executionId,
        string $toolName,
        bool $success,
        string $content,
        float $duration
    ): void {
        $this->log('INFO', "Tool execution completed", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'success' => $success,
            'duration_ms' => round($duration * 1000, 2),
            'result_size' => strlen($content),
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log execution error.
     */
    public function logError(
        string $executionId,
        string $toolName,
        \Throwable $error,
        float $duration
    ): void {
        $this->log('ERROR', "Tool execution failed", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'error' => $error->getMessage(),
            'exception' => get_class($error),
            'duration_ms' => round($duration * 1000, 2),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log retry attempt.
     */
    public function logRetry(
        string $executionId,
        string $toolName,
        int $attempt,
        int $maxRetries,
        int $delayMs
    ): void {
        $this->log('WARNING', "Tool execution retry", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'attempt' => $attempt,
            'max_retries' => $maxRetries,
            'delay_ms' => $delayMs,
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log validation failure.
     */
    public function logValidationFailure(
        string $executionId,
        string $toolName,
        array $errors
    ): void {
        $this->log('WARNING', "Input validation failed", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'errors' => $errors,
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log cache hit.
     */
    public function logCacheHit(string $executionId, string $toolName): void
    {
        $this->log('DEBUG', "Cache hit - returning cached result", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Log cache miss.
     */
    public function logCacheMiss(string $executionId, string $toolName): void
    {
        $this->log('DEBUG', "Cache miss - executing tool", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'timestamp' => microtime(true),
        ]);
    }
    
    /**
     * Internal logging method.
     */
    private function log(string $level, string $message, array $context): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s.u'),
        ];
    }
    
    /**
     * Get all logs.
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
    
    /**
     * Get logs by level.
     */
    public function getLogsByLevel(string $level): array
    {
        return array_filter(
            $this->logs,
            fn($log) => $log['level'] === $level
        );
    }
    
    /**
     * Get logs by tool name.
     */
    public function getLogsByTool(string $toolName): array
    {
        return array_filter(
            $this->logs,
            fn($log) => ($log['context']['tool'] ?? '') === $toolName
        );
    }
    
    /**
     * Export logs as JSON.
     */
    public function exportJson(): string
    {
        return json_encode($this->logs, JSON_PRETTY_PRINT);
    }
    
    /**
     * Print logs in human-readable format.
     */
    public function printLogs(): void
    {
        foreach ($this->logs as $log) {
            $level = str_pad($log['level'], 7);
            $time = substr($log['timestamp'], 11);
            
            echo "[{$time}] {$level} {$log['message']}\n";
            
            foreach ($log['context'] as $key => $value) {
                if ($key === 'timestamp') continue;
                
                $valueStr = is_array($value) ? json_encode($value) : (string)$value;
                echo "  {$key}: {$valueStr}\n";
            }
            
            echo "\n";
        }
    }
    
    /**
     * Clear all logs.
     */
    public function clear(): void
    {
        $this->logs = [];
    }
}

// ============================================================================
// TOOL METRICS COLLECTOR
// ============================================================================

class ToolMetrics
{
    private array $metrics = [];
    
    /**
     * Record a tool execution.
     */
    public function recordExecution(
        string $toolName,
        bool $success,
        float $durationMs,
        int $attempt = 1
    ): void {
        $this->metrics[] = [
            'type' => 'execution',
            'tool' => $toolName,
            'success' => $success,
            'duration_ms' => $durationMs,
            'attempt' => $attempt,
            'timestamp' => microtime(true),
        ];
    }
    
    /**
     * Get metrics for a specific tool.
     */
    public function getToolMetrics(string $toolName): array
    {
        $toolMetrics = array_filter(
            $this->metrics,
            fn($m) => $m['type'] === 'execution' && $m['tool'] === $toolName
        );
        
        if (empty($toolMetrics)) {
            return [
                'tool' => $toolName,
                'total_executions' => 0,
                'successful' => 0,
                'failed' => 0,
                'success_rate' => 0,
                'avg_duration_ms' => 0,
                'max_duration_ms' => 0,
                'min_duration_ms' => 0,
            ];
        }
        
        $total = count($toolMetrics);
        $successful = count(array_filter($toolMetrics, fn($m) => $m['success']));
        $failed = $total - $successful;
        
        $durations = array_map(fn($m) => $m['duration_ms'], $toolMetrics);
        $avgDuration = array_sum($durations) / count($durations);
        $maxDuration = max($durations);
        $minDuration = min($durations);
        
        return [
            'tool' => $toolName,
            'total_executions' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => round(($successful / $total) * 100, 2),
            'avg_duration_ms' => round($avgDuration, 2),
            'max_duration_ms' => round($maxDuration, 2),
            'min_duration_ms' => round($minDuration, 2),
        ];
    }
    
    /**
     * Get overall metrics summary.
     */
    public function getSummary(): array
    {
        $executions = array_filter($this->metrics, fn($m) => $m['type'] === 'execution');
        
        $tools = array_unique(array_map(fn($m) => $m['tool'], $executions));
        $summary = [];
        
        foreach ($tools as $tool) {
            $summary[$tool] = $this->getToolMetrics($tool);
        }
        
        return $summary;
    }
    
    /**
     * Print metrics report.
     */
    public function printReport(): void
    {
        $summary = $this->getSummary();
        
        echo "╔═══════════════════════════════════════════════════════════════════╗\n";
        echo "║                      TOOL EXECUTION METRICS                       ║\n";
        echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
        
        foreach ($summary as $metrics) {
            echo "Tool: {$metrics['tool']}\n";
            echo "─────────────────────────────────────────────────────────────────\n";
            echo "  Total Executions: {$metrics['total_executions']}\n";
            echo "  Successful: {$metrics['successful']}\n";
            echo "  Failed: {$metrics['failed']}\n";
            echo "  Success Rate: {$metrics['success_rate']}%\n";
            echo "  Avg Duration: {$metrics['avg_duration_ms']}ms\n";
            echo "  Min Duration: {$metrics['min_duration_ms']}ms\n";
            echo "  Max Duration: {$metrics['max_duration_ms']}ms\n";
            echo "\n";
        }
    }
}

// ============================================================================
// DEMO: LOGGED TOOL EXECUTOR
// ============================================================================

class LoggedToolExecutor
{
    public function __construct(
        private ExecutionLogger $logger,
        private ToolMetrics $metrics,
    ) {}
    
    public function execute(string $toolName, array $input, callable $toolFn): array
    {
        $executionId = bin2hex(random_bytes(8));
        $startTime = microtime(true);
        
        // Log start
        $this->logger->logStart($executionId, $toolName, $input);
        
        try {
            // Execute tool
            $result = $toolFn($input);
            
            $duration = microtime(true) - $startTime;
            $success = $result['success'] ?? true;
            $content = $result['content'] ?? '';
            
            // Log completion
            $this->logger->logComplete($executionId, $toolName, $success, $content, $duration);
            
            // Record metrics
            $this->metrics->recordExecution($toolName, $success, $duration * 1000);
            
            return $result;
            
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            
            // Log error
            $this->logger->logError($executionId, $toolName, $e, $duration);
            
            // Record metrics
            $this->metrics->recordExecution($toolName, false, $duration * 1000);
            
            return [
                'success' => false,
                'content' => "Error: {$e->getMessage()}",
            ];
        }
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "              EXECUTION LOGGING DEMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$logger = new ExecutionLogger();
$metrics = new ToolMetrics();
$executor = new LoggedToolExecutor($logger, $metrics);

// Example tools
$successTool = fn($input) => [
    'success' => true,
    'content' => 'Operation completed successfully',
];

$slowTool = function($input) {
    usleep(150000); // 150ms
    return [
        'success' => true,
        'content' => 'Slow operation completed',
    ];
};

$errorTool = fn($input) => throw new \RuntimeException('Simulated error');

// Execute various tools
echo "Executing tools...\n\n";

$executor->execute('fast_tool', ['param' => 'value1'], $successTool);
$executor->execute('slow_tool', ['param' => 'value2'], $slowTool);
$executor->execute('fast_tool', ['param' => 'value3'], $successTool);
$executor->execute('error_tool', ['param' => 'value4'], $errorTool);
$executor->execute('slow_tool', ['param' => 'value5'], $slowTool);

// Print logs
echo "═══════════════════════════════════════════════════════════════════\n";
echo "                         EXECUTION LOGS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$logger->printLogs();

// Print metrics
$metrics->printReport();

// Show error logs only
echo "═══════════════════════════════════════════════════════════════════\n";
echo "                         ERROR LOGS ONLY\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$errorLogs = $logger->getLogsByLevel('ERROR');
foreach ($errorLogs as $log) {
    echo "Error: {$log['message']}\n";
    echo "  Tool: {$log['context']['tool']}\n";
    echo "  Exception: {$log['context']['exception']}\n";
    echo "  Message: {$log['context']['error']}\n\n";
}

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
