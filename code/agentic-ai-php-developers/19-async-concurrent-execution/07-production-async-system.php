<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 7: Production Async System
 * 
 * Demonstrates a complete production-ready async system combining all strategies.
 * 
 * Learn:
 * - Integrated async architecture
 * - Strategy selection (batch, race, parallel)
 * - Error handling in async systems
 * - Circuit breaker pattern
 * - Performance monitoring
 * - Production-ready async implementation
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Async\BatchProcessor;
use ClaudeAgents\MultiAgent\AsyncCollaborationManager;
use ClaudeAgents\MultiAgent\SimpleCollaborativeAgent;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp($apiKey);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         Production Async System Demonstration                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Production Async System Architecture
// ============================================================================

class ProductionAsyncSystem
{
    private BatchProcessor $batchProcessor;
    private AsyncCollaborationManager $collaborationManager;
    private array $metrics = [];
    private int $maxConcurrency;
    
    public function __construct(
        private ClaudePhp $client,
        array $config = []
    ) {
        $this->maxConcurrency = $config['max_concurrency'] ?? 5;
        
        // Initialize components
        $agent = Agent::create($client)
            ->withSystemPrompt("You are a helpful assistant. Be concise.");
        $this->batchProcessor = BatchProcessor::create($agent);
        
        $this->collaborationManager = new AsyncCollaborationManager($client, [
            'max_concurrent' => $this->maxConcurrency,
        ]);
        
        // Register default agents
        $this->registerDefaultAgents();
    }
    
    private function registerDefaultAgents(): void
    {
        $agentTypes = ['researcher', 'analyst', 'writer'];
        
        foreach ($agentTypes as $type) {
            $agent = new SimpleCollaborativeAgent(
                client: $this->client,
                agentId: $type,
                capabilities: [$type],
                options: [
                    'system_prompt' => "You are a {$type}. Provide concise, accurate responses.",
                ]
            );
            $this->collaborationManager->registerAgent($type, $agent);
        }
    }
    
    /**
     * Execute tasks with intelligent strategy selection
     */
    public function executeTasks(array $tasks, array $options = []): array
    {
        $startTime = microtime(true);
        
        echo "📋 Executing " . count($tasks) . " tasks...\n";
        
        // Select strategy
        $strategy = $this->selectStrategy(count($tasks), $options);
        
        echo "📊 Selected strategy: {$strategy}\n";
        echo str_repeat("─", 60) . "\n\n";
        
        try {
            $results = match ($strategy) {
                'batch' => $this->executeBatch($tasks, $options),
                'parallel' => $this->executeParallel($tasks, $options),
                'race' => $this->executeRace($tasks, $options),
                default => $this->executeSequential($tasks),
            };
            
            $duration = microtime(true) - $startTime;
            
            // Record metrics
            $this->recordMetrics($strategy, count($tasks), $duration, $results);
            
            echo "\n✓ Execution complete in " . round($duration, 2) . " seconds\n";
            
            return $results;
            
        } catch (\Throwable $e) {
            echo "\n✗ Execution failed: {$e->getMessage()}\n";
            $this->handleError($e, $strategy);
            throw $e;
        }
    }
    
    private function selectStrategy(int $taskCount, array $options): string
    {
        // Manual override
        if (isset($options['strategy'])) {
            return $options['strategy'];
        }
        
        // Intelligent selection
        if ($taskCount === 1) {
            return 'sequential';
        }
        
        if ($options['speed_critical'] ?? false) {
            return 'race';
        }
        
        if ($taskCount <= 3) {
            return 'parallel';
        }
        
        return 'batch';
    }
    
    private function executeBatch(array $tasks, array $options): array
    {
        $this->batchProcessor->reset();
        $this->batchProcessor->addMany($tasks);
        
        $concurrency = $options['concurrency'] ?? $this->maxConcurrency;
        
        echo "Using BatchProcessor with concurrency: {$concurrency}\n\n";
        
        $results = $this->batchProcessor->run(concurrency: $concurrency);
        
        // Show stats
        $stats = $this->batchProcessor->getStats();
        echo "  Tasks: {$stats['total_tasks']}\n";
        echo "  Success: {$stats['successful']}\n";
        echo "  Failed: {$stats['failed']}\n";
        echo "  Tokens: {$stats['total_tokens']['total']}\n";
        
        return $results;
    }
    
    private function executeParallel(array $tasks, array $options): array
    {
        echo "Using AsyncCollaborationManager (parallel execution)\n\n";
        
        return $this->collaborationManager->executeParallel($tasks);
    }
    
    private function executeRace(array $tasks, array $options): array
    {
        echo "Using AsyncCollaborationManager (racing mode)\n\n";
        
        $winner = $this->collaborationManager->race($tasks);
        return [$winner['agent_id'] => $winner['result']];
    }
    
    private function executeSequential(array $tasks): array
    {
        echo "Using sequential execution\n\n";
        
        $results = [];
        $agent = Agent::create($this->client);
        
        foreach ($tasks as $id => $task) {
            $results[$id] = $agent->run($task);
        }
        
        return $results;
    }
    
    private function recordMetrics(string $strategy, int $taskCount, float $duration, array $results): void
    {
        $successCount = count(array_filter($results, fn($r) => $r->isSuccess()));
        
        $this->metrics[] = [
            'strategy' => $strategy,
            'task_count' => $taskCount,
            'duration' => $duration,
            'throughput' => $taskCount / $duration,
            'success_rate' => $successCount / $taskCount,
            'timestamp' => time(),
        ];
    }
    
    private function handleError(\Throwable $e, string $strategy): void
    {
        error_log("Async system error [{$strategy}]: {$e->getMessage()}");
        // In production: send alerts, log to monitoring system, etc.
    }
    
    public function getMetrics(): array
    {
        return $this->metrics;
    }
    
    public function getPerformanceReport(): string
    {
        if (empty($this->metrics)) {
            return "No metrics available\n";
        }
        
        $report = "\n" . str_repeat("═", 60) . "\n";
        $report .= "PERFORMANCE REPORT\n";
        $report .= str_repeat("═", 60) . "\n\n";
        
        $totalTasks = array_sum(array_column($this->metrics, 'task_count'));
        $avgThroughput = array_sum(array_column($this->metrics, 'throughput')) / count($this->metrics);
        $avgSuccessRate = array_sum(array_column($this->metrics, 'success_rate')) / count($this->metrics);
        
        $report .= "Summary:\n";
        $report .= "  Total executions: " . count($this->metrics) . "\n";
        $report .= "  Total tasks: {$totalTasks}\n";
        $report .= "  Avg throughput: " . round($avgThroughput, 2) . " tasks/sec\n";
        $report .= "  Avg success rate: " . round($avgSuccessRate * 100, 1) . "%\n\n";
        
        $report .= "By Strategy:\n";
        $strategyCounts = [];
        foreach ($this->metrics as $metric) {
            $strategy = $metric['strategy'];
            $strategyCounts[$strategy] = ($strategyCounts[$strategy] ?? 0) + 1;
        }
        
        foreach ($strategyCounts as $strategy => $count) {
            $report .= "  {$strategy}: {$count} executions\n";
        }
        
        return $report;
    }
}

// ============================================================================
// Circuit Breaker Pattern
// ============================================================================

class AsyncCircuitBreaker
{
    private int $failureCount = 0;
    private int $threshold;
    private bool $open = false;
    private ?int $openedAt = null;
    private int $cooldownSeconds;
    
    public function __construct(int $threshold = 5, int $cooldownSeconds = 60)
    {
        $this->threshold = $threshold;
        $this->cooldownSeconds = $cooldownSeconds;
    }
    
    public function execute(callable $operation): mixed
    {
        // Check if circuit should be closed (after cooldown)
        if ($this->open && $this->openedAt !== null) {
            $elapsed = time() - $this->openedAt;
            if ($elapsed >= $this->cooldownSeconds) {
                echo "🔄 Circuit breaker closing after cooldown\n";
                $this->open = false;
                $this->failureCount = 0;
            }
        }
        
        if ($this->open) {
            throw new \RuntimeException('Circuit breaker is OPEN - too many failures');
        }
        
        try {
            $result = $operation();
            $this->failureCount = 0; // Reset on success
            return $result;
        } catch (\Throwable $e) {
            $this->failureCount++;
            
            if ($this->failureCount >= $this->threshold) {
                $this->open = true;
                $this->openedAt = time();
                echo "🚨 Circuit breaker OPENED after {$this->failureCount} failures\n";
            }
            
            throw $e;
        }
    }
    
    public function isOpen(): bool
    {
        return $this->open;
    }
    
    public function getFailureCount(): int
    {
        return $this->failureCount;
    }
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

echo "Example 1: Strategy Selection\n";
echo str_repeat("─", 60) . "\n\n";

$system = new ProductionAsyncSystem($client, [
    'max_concurrency' => 5,
]);

// Test 1: Small batch (will use parallel)
$smallBatch = [
    'task1' => 'What is 10 + 10?',
    'task2' => 'What is 20 * 2?',
    'task3' => 'What is 50 / 5?',
];

echo "Test 1: Small batch (3 tasks)\n";
$results1 = $system->executeTasks($smallBatch);

echo "\n\n";

// Test 2: Large batch (will use batch processor)
$largeBatch = [];
for ($i = 1; $i <= 12; $i++) {
    $largeBatch["calc_{$i}"] = "What is {$i} * 3?";
}

echo "Test 2: Large batch (12 tasks)\n";
$results2 = $system->executeTasks($largeBatch, ['concurrency' => 4]);

echo "\n\n";

// Test 3: Speed-critical (will use race)
$raceTasks = [
    'agent1' => 'Quick: Name a programming language',
    'agent2' => 'Quick: Name a programming language',
];

echo "Test 3: Speed-critical (racing)\n";
$results3 = $system->executeTasks($raceTasks, ['speed_critical' => true]);

echo "\n\n";

// ============================================================================
// Example 2: Circuit Breaker
// ============================================================================

echo "Example 2: Circuit Breaker Pattern\n";
echo str_repeat("─", 60) . "\n\n";

$circuitBreaker = new AsyncCircuitBreaker(threshold: 3, cooldownSeconds: 5);

echo "Simulating operations with potential failures...\n\n";

for ($i = 1; $i <= 5; $i++) {
    echo "Attempt {$i}: ";
    
    try {
        $result = $circuitBreaker->execute(function () use ($i) {
            // Simulate failures on attempts 1-3
            if ($i <= 3) {
                throw new \RuntimeException("Simulated failure");
            }
            return "Success";
        });
        
        echo "✓ {$result}\n";
    } catch (\RuntimeException $e) {
        echo "✗ {$e->getMessage()}\n";
    }
}

echo "\nCircuit breaker protected system from cascading failures\n";

echo "\n\n";

// ============================================================================
// Example 3: Performance Report
// ============================================================================

echo "Example 3: Performance Report\n";
echo str_repeat("─", 60) . "\n\n";

echo $system->getPerformanceReport();

echo "\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ Production systems need intelligent strategy selection\n";
echo "✓ Combine batch, parallel, and racing based on workload\n";
echo "✓ Implement circuit breakers to prevent cascading failures\n";
echo "✓ Monitor metrics (throughput, latency, success rate)\n";
echo "✓ Handle errors gracefully in async contexts\n";
echo "✓ Track performance over time for optimization\n";
echo "✓ Production-ready: error handling + monitoring + metrics\n\n";

echo "🎉 Chapter 19 Complete!\n\n";

echo "You've mastered async & concurrent execution with AMPHP!\n";
echo "Ready for Chapter 20: Capstone Project\n";
