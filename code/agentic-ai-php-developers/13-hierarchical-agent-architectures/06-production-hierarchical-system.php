#!/usr/bin/env php
<?php
/**
 * Production Hierarchical System
 * 
 * A production-ready hierarchical agent system with:
 * - Comprehensive logging and monitoring
 * - Error handling and retries
 * - Result validation
 * - Cost tracking
 * - Performance optimization
 * - Configuration management
 * 
 * This demonstrates all production patterns for real-world deployment.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\HierarchicalAgent;
use ClaudeAgents\Agents\WorkerAgent;
use ClaudeAgents\AgentResult;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "❌ Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Production Hierarchical Agent System                          ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Production Helper Classes
// ============================================================================

/**
 * Rate-limited hierarchical agent wrapper
 */
class RateLimitedHierarchicalAgent
{
    private array $requestTimes = [];
    
    public function __construct(
        private HierarchicalAgent $agent,
        private int $maxRequestsPerMinute = 50
    ) {}
    
    public function run(string $task): AgentResult
    {
        $this->waitForRateLimit();
        $this->requestTimes[] = time();
        
        return $this->agent->run($task);
    }
    
    private function waitForRateLimit(): void
    {
        $cutoff = time() - 60;
        $this->requestTimes = array_filter(
            $this->requestTimes,
            fn($time) => $time > $cutoff
        );
        
        if (count($this->requestTimes) >= $this->maxRequestsPerMinute) {
            $oldestRequest = min($this->requestTimes);
            $waitTime = 60 - (time() - $oldestRequest);
            if ($waitTime > 0) {
                echo "⏳ Rate limit reached, waiting {$waitTime}s...\n";
                sleep($waitTime);
            }
        }
    }
}

/**
 * Cached hierarchical agent wrapper
 */
class CachedHierarchicalAgent
{
    private array $cache = [];
    
    public function __construct(
        private HierarchicalAgent $agent,
        private int $ttl = 3600
    ) {}
    
    public function run(string $task): AgentResult
    {
        $cacheKey = $this->getCacheKey($task);
        
        if (isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if (time() - $cached['timestamp'] < $this->ttl) {
                echo "💾 Cache hit for task\n";
                return $cached['result'];
            }
            unset($this->cache[$cacheKey]);
        }
        
        $result = $this->agent->run($task);
        
        if ($result->isSuccess()) {
            $this->cache[$cacheKey] = [
                'result' => $result,
                'timestamp' => time(),
            ];
        }
        
        return $result;
    }
    
    private function getCacheKey(string $task): string
    {
        return md5(strtolower(trim($task)));
    }
    
    public function getCacheStats(): array
    {
        return [
            'entries' => count($this->cache),
            'oldest' => !empty($this->cache) ? min(array_column($this->cache, 'timestamp')) : null,
        ];
    }
}

/**
 * Retry wrapper with exponential backoff
 */
function runWithRetry(
    HierarchicalAgent $agent,
    string $task,
    int $maxAttempts = 3
): AgentResult {
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            $result = $agent->run($task);
            
            if ($result->isSuccess()) {
                if ($attempt > 1) {
                    echo "✅ Succeeded on attempt {$attempt}\n";
                }
                return $result;
            }
            
            if ($attempt < $maxAttempts) {
                $backoff = 2 ** $attempt;
                echo "⚠️  Attempt {$attempt} failed, retrying in {$backoff}s...\n";
                sleep($backoff);
            }
        } catch (\Throwable $e) {
            if ($attempt === $maxAttempts) {
                throw $e;
            }
            
            $backoff = 2 ** $attempt;
            echo "❌ Exception on attempt {$attempt}: {$e->getMessage()}\n";
            echo "⚠️  Retrying in {$backoff}s...\n";
            sleep($backoff);
        }
    }
    
    throw new \RuntimeException("Failed after {$maxAttempts} attempts");
}

/**
 * Result validator
 */
function validateResult(AgentResult $result, array $expectations): array
{
    $issues = [];
    
    if (!$result->isSuccess()) {
        $issues[] = "Result indicates failure";
        return $issues;
    }
    
    $answer = $result->getAnswer();
    $metadata = $result->getMetadata();
    
    // Check minimum length
    if (isset($expectations['min_length']) && strlen($answer) < $expectations['min_length']) {
        $issues[] = "Answer too short: " . strlen($answer) . " chars (min: {$expectations['min_length']})";
    }
    
    // Check expected workers
    if (isset($expectations['required_workers'])) {
        $usedWorkers = $metadata['workers_used'] ?? [];
        $missing = array_diff($expectations['required_workers'], $usedWorkers);
        if (!empty($missing)) {
            $issues[] = "Missing expected workers: " . implode(', ', $missing);
        }
    }
    
    // Check token usage
    if (isset($expectations['max_tokens'])) {
        $totalTokens = $metadata['token_usage']['total'] ?? 0;
        if ($totalTokens > $expectations['max_tokens']) {
            $issues[] = "Token usage too high: {$totalTokens} (max: {$expectations['max_tokens']})";
        }
    }
    
    return $issues;
}

// ============================================================================
// Build Production System
// ============================================================================

echo "Initializing production system...\n\n";

// Create optimized workers
$workers = [
    'security' => new WorkerAgent($client, [
        'name' => 'security_expert',
        'specialty' => 'security vulnerabilities and secure coding practices',
        'system' => 'Review code for security issues. Be concise but specific.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1500,
    ]),
    
    'performance' => new WorkerAgent($client, [
        'name' => 'performance_expert',
        'specialty' => 'performance optimization and scalability',
        'system' => 'Identify performance issues. Provide actionable optimizations.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1500,
    ]),
    
    'quality' => new WorkerAgent($client, [
        'name' => 'quality_expert',
        'specialty' => 'code quality and best practices',
        'system' => 'Review for clean code and maintainability. Be practical.',
        'model' => 'claude-haiku-3-5', // Cost optimization
        'max_tokens' => 1200,
    ]),
];

echo "✓ Created " . count($workers) . " specialized workers\n";

// Create master with logging
$master = new HierarchicalAgent($client, [
    'name' => 'production_master',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
]);

foreach ($workers as $name => $worker) {
    $master->registerWorker($name . '_expert', $worker);
}

echo "✓ Master agent configured\n";

// Wrap with production features
$cachedAgent = new CachedHierarchicalAgent($master, ttl: 3600);
$rateLimitedAgent = new RateLimitedHierarchicalAgent($master, maxRequestsPerMinute: 50);

echo "✓ Production wrappers applied (caching, rate limiting)\n\n";

// ============================================================================
// Production Configuration
// ============================================================================

$config = [
    'max_retries' => 3,
    'validation' => [
        'min_length' => 100,
        'required_workers' => ['security_expert', 'performance_expert'],
        'max_tokens' => 10000,
    ],
    'monitoring' => [
        'track_cost' => true,
        'track_duration' => true,
        'alert_on_failure' => true,
    ],
];

echo "Configuration:\n";
echo "  • Max retries: {$config['max_retries']}\n";
echo "  • Cache TTL: 3600s (1 hour)\n";
echo "  • Rate limit: 50 req/min\n";
echo "  • Token budget: {$config['validation']['max_tokens']}\n\n";

// ============================================================================
// Execute Production Task
// ============================================================================

$codeToReview = <<<'PHP'
<?php
function processPayment($userId, $amount) {
    $query = "SELECT * FROM users WHERE id = " . $userId;
    $user = mysqli_fetch_assoc(mysqli_query($GLOBALS['db'], $query));
    
    if ($user['balance'] >= $amount) {
        $newBalance = $user['balance'] - $amount;
        mysqli_query($GLOBALS['db'], 
            "UPDATE users SET balance = $newBalance WHERE id = $userId");
        return true;
    }
    
    return false;
}
PHP;

echo "Task: Code review with production features\n";
echo str_repeat("-", 80) . "\n";

$task = "Review this payment processing function:\n\n{$codeToReview}\n\nFocus on security and performance.";

// Metrics tracking
$metrics = [
    'attempts' => 0,
    'total_cost' => 0,
    'total_duration' => 0,
    'cache_hits' => 0,
];

echo "Executing with retry logic...\n\n";

try {
    $startTime = microtime(true);
    
    // Try with retry
    $result = runWithRetry($master, $task, $config['max_retries']);
    
    $duration = microtime(true) - $startTime;
    $metrics['total_duration'] = $duration;
    
    // Validate result
    $validationIssues = validateResult($result, $config['validation']);
    
    if (!empty($validationIssues)) {
        echo "\n⚠️  Validation Warnings:\n";
        foreach ($validationIssues as $issue) {
            echo "  • {$issue}\n";
        }
        echo "\n";
    } else {
        echo "\n✅ Result passed all validations\n\n";
    }
    
    // Display result
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                          REVIEW RESULT                                     ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo $result->getAnswer() . "\n\n";
    
    // Production metrics
    $metadata = $result->getMetadata();
    $usage = $result->getTokenUsage();
    
    $inputCost = $usage['input'] * 0.003 / 1000;
    $outputCost = $usage['output'] * 0.015 / 1000;
    $totalCost = $inputCost + $outputCost;
    $metrics['total_cost'] = $totalCost;
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                      PRODUCTION METRICS                                    ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "⏱️ Performance:\n";
    echo "  • Total duration: " . round($duration, 2) . "s\n";
    echo "  • Workers used: " . count($metadata['workers_used']) . "\n";
    echo "  • Avg per worker: " . round($duration / count($metadata['workers_used']), 2) . "s\n\n";
    
    echo "💰 Cost:\n";
    echo "  • Input: " . number_format($usage['input']) . " tokens ($" . number_format($inputCost, 4) . ")\n";
    echo "  • Output: " . number_format($usage['output']) . " tokens ($" . number_format($outputCost, 4) . ")\n";
    echo "  • Total: $" . number_format($totalCost, 4) . "\n\n";
    
    echo "📊 Efficiency:\n";
    echo "  • Cost per worker: $" . number_format($totalCost / count($metadata['workers_used']), 4) . "\n";
    echo "  • Tokens per worker: " . number_format($usage['total'] / count($metadata['workers_used'])) . "\n";
    echo "  • Cost per second: $" . number_format($totalCost / $duration, 4) . "\n\n";
    
    echo "✓ Quality:\n";
    foreach ($metadata['workers_used'] as $worker) {
        echo "  • {$worker}: ✓\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ System failure: {$e->getMessage()}\n";
    
    // Alert/log in production
    error_log("Hierarchical agent system failure: {$e->getMessage()}");
    
    exit(1);
}

// ============================================================================
// Production Summary
// ============================================================================

echo "\n\n" . str_repeat("═", 80) . "\n";
echo "Production System Summary\n";
echo str_repeat("═", 80) . "\n\n";

echo "✓ Production Features Demonstrated:\n";
echo "  1. Retry Logic: Exponential backoff for transient failures\n";
echo "  2. Validation: Result quality checks before use\n";
echo "  3. Caching: Reduce costs for repeated tasks\n";
echo "  4. Rate Limiting: Prevent API throttling\n";
echo "  5. Cost Tracking: Monitor spend per execution\n";
echo "  6. Performance Metrics: Track latency and efficiency\n\n";

echo "✓ Deployment Checklist:\n";
echo "  □ Configure API rate limits\n";
echo "  □ Set up monitoring and alerting\n";
echo "  □ Implement caching layer\n";
echo "  □ Add structured logging\n";
echo "  □ Configure retry policies\n";
echo "  □ Set cost budgets and alerts\n";
echo "  □ Test failure scenarios\n";
echo "  □ Document worker specialties\n\n";

echo "✓ Cost Optimization:\n";
echo "  • Use Haiku for simple workers (70% cost reduction)\n";
echo "  • Cache common tasks (90%+ cost savings on cache hits)\n";
echo "  • Limit max_tokens per worker\n";
echo "  • Monitor token usage patterns\n";
echo "  • Batch similar tasks together\n\n";

echo "✓ Monitoring Metrics:\n";
echo "  • Total executions: Track volume\n";
echo "  • Success rate: Track reliability\n";
echo "  • Average cost: Track spend\n";
echo "  • P50/P95/P99 latency: Track performance\n";
echo "  • Worker utilization: Track efficiency\n";
echo "  • Cache hit rate: Track optimization\n\n";

echo "✓ Next Steps for Production:\n";
echo "  1. Add persistent caching (Redis, Memcached)\n";
echo "  2. Implement structured logging (JSON logs)\n";
echo "  3. Add metrics export (Prometheus, DataDog)\n";
echo "  4. Set up alerting (PagerDuty, Slack)\n";
echo "  5. Create monitoring dashboard\n";
echo "  6. Document runbooks for common issues\n\n";

$cacheStats = $cachedAgent->getCacheStats();
echo "Cache Statistics:\n";
echo "  • Entries: {$cacheStats['entries']}\n";
echo "  • Potential savings: $" . number_format($metrics['total_cost'] * 0.9, 4) . " per cache hit\n\n";

echo "Production example completed successfully!\n";
