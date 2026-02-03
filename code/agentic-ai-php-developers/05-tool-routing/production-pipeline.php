<?php

/**
 * Production-Ready Tool Execution Pipeline
 * 
 * Demonstrates:
 * - Complete production pipeline setup
 * - All patterns integrated together
 * - Router + Pipeline + Retry + Logging + Metrics
 * - Real-world usage patterns
 * 
 * This is the comprehensive example showing all concepts from Chapter 05.
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

// ============================================================================
// SUPPORTING CLASSES (condensed from other examples)
// ============================================================================

class ToolErrorCode
{
    public const NETWORK_ERROR = 'NETWORK_ERROR';
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    
    public static function isRetryable(string $code): bool
    {
        return in_array($code, [
            self::NETWORK_ERROR,
            self::RATE_LIMIT_EXCEEDED,
        ]);
    }
}

class IdempotencyCache
{
    private array $cache = [];
    private int $ttl = 3600;
    
    public function get(string $key): ?array
    {
        if (!isset($this->cache[$key]) || time() > $this->cache[$key]['expires_at']) {
            return null;
        }
        return $this->cache[$key];
    }
    
    public function set(string $key, ToolResult $result): void
    {
        $this->cache[$key] = [
            'result' => $result,
            'stored_at' => time(),
            'expires_at' => time() + $this->ttl,
        ];
    }
}

class ToolMetrics
{
    private array $metrics = [];
    
    public function recordExecution(string $toolName, bool $success, float $durationMs): void
    {
        $this->metrics[] = [
            'tool' => $toolName,
            'success' => $success,
            'duration_ms' => $durationMs,
            'timestamp' => microtime(true),
        ];
    }
    
    public function getSummary(): array
    {
        $tools = array_unique(array_column($this->metrics, 'tool'));
        $summary = [];
        
        foreach ($tools as $tool) {
            $toolMetrics = array_filter($this->metrics, fn($m) => $m['tool'] === $tool);
            
            $total = count($toolMetrics);
            $successful = count(array_filter($toolMetrics, fn($m) => $m['success']));
            
            $durations = array_column($toolMetrics, 'duration_ms');
            $avgDuration = !empty($durations) ? array_sum($durations) / count($durations) : 0;
            
            $summary[$tool] = [
                'total' => $total,
                'successful' => $successful,
                'failed' => $total - $successful,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 1) : 0,
                'avg_duration_ms' => round($avgDuration, 2),
            ];
        }
        
        return $summary;
    }
}

// ============================================================================
// SECURE TOOL ROUTER
// ============================================================================

class SecureToolRouter
{
    private array $permissions = [];
    
    public function __construct(
        private ToolRegistry $registry,
        private ?\Psr\Log\LoggerInterface $logger = null,
    ) {}
    
    public function setPermissions(string $toolName, array $allowedRoles): self
    {
        $this->permissions[$toolName] = $allowedRoles;
        return $this;
    }
    
    public function route(string $toolName, array $input, ?string $userRole = null): ToolResult
    {
        // Check permissions
        if (isset($this->permissions[$toolName])) {
            if ($userRole === null || !in_array($userRole, $this->permissions[$toolName])) {
                $this->logger?->warning("Permission denied: {$toolName}", ['role' => $userRole]);
                return ToolResult::error("Permission denied");
            }
        }
        
        // Execute tool
        $tool = $this->registry->get($toolName);
        
        if ($tool === null) {
            return ToolResult::error("Unknown tool: {$toolName}");
        }
        
        try {
            return $tool->execute($input);
        } catch (\Throwable $e) {
            $this->logger?->error("Tool failed: {$toolName}", ['error' => $e->getMessage()]);
            return ToolResult::error($e->getMessage());
        }
    }
}

// ============================================================================
// TOOL EXECUTION PIPELINE
// ============================================================================

class ToolExecutionPipeline
{
    private array $preExecutionHooks = [];
    private array $postExecutionHooks = [];
    
    public function __construct(
        private SecureToolRouter $router,
        private ?\Psr\Log\LoggerInterface $logger = null,
    ) {}
    
    public function addPreExecutionHook(callable $hook): self
    {
        $this->preExecutionHooks[] = $hook;
        return $this;
    }
    
    public function addPostExecutionHook(callable $hook): self
    {
        $this->postExecutionHooks[] = $hook;
        return $this;
    }
    
    public function execute(string $toolName, array $input, ?string $userRole = null): ToolResult
    {
        $executionId = bin2hex(random_bytes(8));
        $startTime = microtime(true);
        
        try {
            // Pre-execution hooks
            foreach ($this->preExecutionHooks as $hook) {
                $hook($toolName, $input);
            }
            
            // Execute
            $result = $this->router->route($toolName, $input, $userRole);
            
            // Post-execution hooks
            $duration = microtime(true) - $startTime;
            foreach ($this->postExecutionHooks as $hook) {
                $hook($toolName, $input, $result, $duration);
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger?->error("Pipeline failed: {$toolName}", [
                'execution_id' => $executionId,
                'error' => $e->getMessage(),
            ]);
            
            return ToolResult::error($e->getMessage());
        }
    }
}

// ============================================================================
// RETRYABLE PIPELINE
// ============================================================================

class RetryableToolPipeline
{
    public function __construct(
        private ToolExecutionPipeline $pipeline,
        private IdempotencyCache $cache,
        private int $maxRetries = 3,
        private int $baseDelayMs = 1000,
        private ?\Psr\Log\LoggerInterface $logger = null,
    ) {}
    
    public function execute(string $toolName, array $input, ?string $userRole = null): ToolResult
    {
        // Generate idempotency key
        $key = hash('sha256', $toolName . json_encode($input));
        
        // Check cache
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            $this->logger?->info("Cache hit", ['tool' => $toolName]);
            return $cached['result'];
        }
        
        // Execute with retries
        $attempt = 0;
        $delay = $this->baseDelayMs;
        
        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            $result = $this->pipeline->execute($toolName, $input, $userRole);
            
            // Success - cache and return
            if ($result->isSuccess()) {
                $this->cache->set($key, $result);
                return $result;
            }
            
            // Check if retryable (simplified)
            $isRetryable = str_contains($result->getContent(), 'retryable');
            
            if (!$isRetryable || $attempt >= $this->maxRetries) {
                return $result;
            }
            
            // Retry with backoff
            $this->logger?->warning("Retrying", [
                'tool' => $toolName,
                'attempt' => $attempt,
                'delay_ms' => $delay,
            ]);
            
            usleep($delay * 1000);
            $delay *= 2;
        }
        
        return ToolResult::error('Max retries exceeded');
    }
}

// ============================================================================
// PRODUCTION SETUP
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "         PRODUCTION-READY TOOL EXECUTION PIPELINE\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// 1. Setup logger
$logger = new Logger('production');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
$logger->pushProcessor(new UidProcessor());

// 2. Create supporting infrastructure
$registry = new ToolRegistry();
$metrics = new ToolMetrics();
$cache = new IdempotencyCache();

// 3. Register tools
$weatherTool = Tool::create('get_weather')
    ->description('Get weather for a city')
    ->stringParam('city', 'City name')
    ->handler(function (array $input): ToolResult {
        $city = $input['city'];
        
        // Simulate API call
        usleep(200000); // 200ms
        
        return ToolResult::success("Weather in {$city}: 75°F, Clear skies");
    });

$databaseTool = Tool::create('query_database')
    ->description('Query database')
    ->stringParam('query', 'SQL query')
    ->handler(function (array $input): ToolResult {
        $query = $input['query'];
        
        // Simulate database query
        usleep(100000); // 100ms
        
        return ToolResult::success(json_encode([
            'rows' => [['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob']],
            'count' => 2,
        ]));
    });

$registry->register($weatherTool);
$registry->register($databaseTool);

// 4. Build pipeline
$router = new SecureToolRouter($registry, $logger);
$router->setPermissions('query_database', ['admin', 'developer']);

$pipeline = new ToolExecutionPipeline($router, $logger);

// 5. Add rate limiting hook
$rateLimits = [];
$pipeline->addPreExecutionHook(function ($toolName, $input) use (&$rateLimits) {
    $key = $toolName;
    $now = time();
    
    $rateLimits[$key] = array_filter(
        $rateLimits[$key] ?? [],
        fn($timestamp) => $timestamp > $now - 60
    );
    
    if (count($rateLimits[$key]) >= 10) {
        throw new \Exception("Rate limit exceeded");
    }
    
    $rateLimits[$key][] = $now;
});

// 6. Add metrics hook
$pipeline->addPostExecutionHook(function ($toolName, $input, $result, $duration) use ($metrics) {
    $metrics->recordExecution($toolName, $result->isSuccess(), $duration * 1000);
});

// 7. Create retryable pipeline
$retryablePipeline = new RetryableToolPipeline(
    pipeline: $pipeline,
    cache: $cache,
    maxRetries: 3,
    baseDelayMs: 100,
    logger: $logger
);

// ============================================================================
// EXECUTE TOOLS
// ============================================================================

echo "Executing production tools...\n\n";

// Test 1: Weather tool (no permission required)
echo "1. Weather Tool\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $retryablePipeline->execute('get_weather', ['city' => 'San Francisco']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

// Test 2: Database query with admin role
echo "2. Database Query (Admin Role)\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $retryablePipeline->execute('query_database', ['query' => 'SELECT * FROM users'], 'admin');
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: " . substr($result->getContent(), 0, 50) . "...\n\n";

// Test 3: Database query with user role (should fail)
echo "3. Database Query (User Role - Should Fail)\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $retryablePipeline->execute('query_database', ['query' => 'SELECT * FROM users'], 'user');
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

// Test 4: Cached execution
echo "4. Cached Weather Request (Same Input)\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $retryablePipeline->execute('get_weather', ['city' => 'San Francisco']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n";
echo "(Should return instantly from cache)\n\n";

// ============================================================================
// METRICS REPORT
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                       EXECUTION METRICS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$summary = $metrics->getSummary();

foreach ($summary as $toolName => $stats) {
    echo "Tool: {$toolName}\n";
    echo "  Total: {$stats['total']}\n";
    echo "  Successful: {$stats['successful']}\n";
    echo "  Failed: {$stats['failed']}\n";
    echo "  Success Rate: {$stats['success_rate']}%\n";
    echo "  Avg Duration: {$stats['avg_duration_ms']}ms\n";
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                    PRODUCTION FEATURES DEMONSTRATED\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "✓ Tool routing with registry\n";
echo "✓ Permission-based access control\n";
echo "✓ Pre-execution hooks (rate limiting)\n";
echo "✓ Post-execution hooks (metrics)\n";
echo "✓ Retry logic with exponential backoff\n";
echo "✓ Idempotency caching\n";
echo "✓ Structured logging with Monolog\n";
echo "✓ Performance metrics tracking\n";
echo "✓ Error handling and standardization\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
