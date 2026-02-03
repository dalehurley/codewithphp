<?php

/**
 * Tool Execution Pipeline
 * 
 * Demonstrates:
 * - Complete execution pipeline with hooks
 * - Pre-execution validation
 * - Post-execution callbacks
 * - Execution timing and metrics
 * - Hook-based extensibility
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// ============================================================================
// SECURE TOOL ROUTER (with validation and permissions)
// ============================================================================

class SecureToolRouter
{
    private array $permissions = [];
    private array $disabledTools = [];
    
    public function __construct(
        private ToolRegistry $registry,
        private ?\Psr\Log\LoggerInterface $logger = null,
    ) {}
    
    public function setPermissions(string $toolName, array $allowedRoles): self
    {
        $this->permissions[$toolName] = $allowedRoles;
        return $this;
    }
    
    public function disableTool(string $toolName): self
    {
        $this->disabledTools[] = $toolName;
        return $this;
    }
    
    public function route(
        string $toolName,
        array $input,
        ?string $userRole = null
    ): ToolResult {
        // Check if disabled
        if (in_array($toolName, $this->disabledTools)) {
            $this->logger?->warning("Attempted to use disabled tool: {$toolName}");
            return ToolResult::error("Tool '{$toolName}' is currently unavailable");
        }
        
        // Check permissions
        if (isset($this->permissions[$toolName])) {
            $allowedRoles = $this->permissions[$toolName];
            
            if ($userRole === null || !in_array($userRole, $allowedRoles)) {
                $this->logger?->warning("Permission denied for tool: {$toolName}", [
                    'user_role' => $userRole,
                    'allowed_roles' => $allowedRoles,
                ]);
                return ToolResult::error("Permission denied for tool: {$toolName}");
            }
        }
        
        // Find and execute tool
        $tool = $this->registry->get($toolName);
        
        if ($tool === null) {
            return ToolResult::error("Unknown tool: {$toolName}");
        }
        
        try {
            return $tool->execute($input);
        } catch (\Throwable $e) {
            $this->logger?->error("Tool execution failed: {$toolName}", [
                'error' => $e->getMessage(),
            ]);
            
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
    
    /**
     * Add a pre-execution hook.
     */
    public function addPreExecutionHook(callable $hook): self
    {
        $this->preExecutionHooks[] = $hook;
        return $this;
    }
    
    /**
     * Add a post-execution hook.
     */
    public function addPostExecutionHook(callable $hook): self
    {
        $this->postExecutionHooks[] = $hook;
        return $this;
    }
    
    /**
     * Execute a tool through the complete pipeline.
     */
    public function execute(
        string $toolName,
        array $input,
        ?string $userRole = null
    ): ToolResult {
        $executionId = bin2hex(random_bytes(8));
        $startTime = microtime(true);
        
        $this->logger?->info("Pipeline execution started", [
            'execution_id' => $executionId,
            'tool' => $toolName,
            'user_role' => $userRole,
        ]);
        
        try {
            // 1. PRE-EXECUTION HOOKS
            foreach ($this->preExecutionHooks as $hook) {
                try {
                    $hook($toolName, $input);
                } catch (\Throwable $e) {
                    $this->logger?->error("Pre-execution hook failed", [
                        'execution_id' => $executionId,
                        'error' => $e->getMessage(),
                    ]);
                    
                    return ToolResult::error(
                        "Pre-execution check failed: {$e->getMessage()}"
                    );
                }
            }
            
            // 2. EXECUTE TOOL
            $result = $this->router->route($toolName, $input, $userRole);
            
            // 3. CALCULATE DURATION
            $duration = microtime(true) - $startTime;
            
            // 4. POST-EXECUTION HOOKS
            foreach ($this->postExecutionHooks as $hook) {
                try {
                    $hook($toolName, $input, $result, $duration);
                } catch (\Throwable $e) {
                    $this->logger?->warning("Post-execution hook failed", [
                        'execution_id' => $executionId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            $this->logger?->info("Pipeline execution completed", [
                'execution_id' => $executionId,
                'tool' => $toolName,
                'duration_ms' => round($duration * 1000, 2),
                'success' => $result->isSuccess(),
            ]);
            
            return $result;
            
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            
            $this->logger?->error("Pipeline execution failed", [
                'execution_id' => $executionId,
                'tool' => $toolName,
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
            ]);
            
            return ToolResult::error($e->getMessage());
        }
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

// Setup
$logger = new Logger('pipeline');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$registry = new ToolRegistry();

// Register tools
$databaseTool = Tool::create('query_database')
    ->description('Query the database')
    ->stringParam('query', 'SQL query to execute')
    ->handler(function (array $input): ToolResult {
        $query = $input['query'];
        
        // Simulate database query
        sleep(1); // Simulate delay
        
        return ToolResult::success(json_encode([
            'rows' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
            ],
            'count' => 2,
        ]));
    });

$registry->register($databaseTool);

// Create pipeline
$router = new SecureToolRouter($registry, $logger);
$router->setPermissions('query_database', ['admin', 'developer']);

$pipeline = new ToolExecutionPipeline($router, $logger);

// Add pre-execution hook (rate limiting simulation)
$rateLimits = [];
$pipeline->addPreExecutionHook(function ($toolName, $input) use (&$rateLimits) {
    $key = $toolName;
    $now = time();
    
    // Clean old entries
    $rateLimits[$key] = array_filter(
        $rateLimits[$key] ?? [],
        fn($timestamp) => $timestamp > $now - 60
    );
    
    // Check limit (5 requests per minute)
    if (count($rateLimits[$key]) >= 5) {
        throw new \Exception("Rate limit exceeded for {$toolName}");
    }
    
    $rateLimits[$key][] = $now;
});

// Add post-execution hook (metrics)
$metrics = [];
$pipeline->addPostExecutionHook(function ($toolName, $input, $result, $duration) use (&$metrics) {
    if (!isset($metrics[$toolName])) {
        $metrics[$toolName] = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'total_time' => 0,
        ];
    }
    
    $metrics[$toolName]['total']++;
    
    if ($result->isSuccess()) {
        $metrics[$toolName]['successful']++;
    } else {
        $metrics[$toolName]['failed']++;
    }
    
    $metrics[$toolName]['total_time'] += $duration;
});

// ============================================================================
// TEST PIPELINE
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                 EXECUTION PIPELINE DEMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Test 1: Successful execution with admin role
echo "Test 1: Execute with admin role\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $pipeline->execute(
    'query_database',
    ['query' => 'SELECT * FROM users'],
    'admin'
);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: " . substr($result->getContent(), 0, 100) . "...\n\n";

// Test 2: Permission denied (user role)
echo "Test 2: Execute with user role (should fail)\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $pipeline->execute(
    'query_database',
    ['query' => 'SELECT * FROM users'],
    'user'
);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

// Test 3: Multiple executions to show hooks working
echo "Test 3: Multiple executions (testing hooks)\n";
echo "─────────────────────────────────────────────────────────────────\n";
for ($i = 1; $i <= 3; $i++) {
    $result = $pipeline->execute(
        'query_database',
        ['query' => "SELECT * FROM users WHERE id = {$i}"],
        'admin'
    );
    echo "Execution {$i}: " . ($result->isSuccess() ? 'Success' : 'Failed') . "\n";
}
echo "\n";

// Show metrics
echo "Metrics Summary:\n";
echo "─────────────────────────────────────────────────────────────────\n";
foreach ($metrics as $toolName => $stats) {
    echo "Tool: {$toolName}\n";
    echo "  Total executions: {$stats['total']}\n";
    echo "  Successful: {$stats['successful']}\n";
    echo "  Failed: {$stats['failed']}\n";
    echo "  Avg time: " . round(($stats['total_time'] / $stats['total']) * 1000, 2) . "ms\n";
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
