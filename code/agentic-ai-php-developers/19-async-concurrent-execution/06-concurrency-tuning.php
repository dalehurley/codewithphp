<?php

declare(strict_types=1);

/**
 * Chapter 19: Async & Concurrent Execution
 * Example 6: Concurrency Tuning
 * 
 * Demonstrates how to choose and tune concurrency levels for optimal performance.
 * 
 * Learn:
 * - Testing different concurrency levels
 * - Measuring throughput and latency
 * - Adaptive concurrency adjustment
 * - Cost-aware concurrency
 * - Performance monitoring
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Async\BatchProcessor;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp($apiKey);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║          Concurrency Tuning Demonstration                    ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// EXAMPLE 1: Concurrency Level Testing
// ============================================================================

echo "Example 1: Testing Different Concurrency Levels\n";
echo str_repeat("─", 60) . "\n\n";

// Create agent
$agent = Agent::create($client)
    ->withSystemPrompt("You are a helpful assistant. Be concise.");

// Create test tasks
$testTasks = [];
for ($i = 1; $i <= 15; $i++) {
    $testTasks["task_{$i}"] = "What is {$i} * 2? Just give the number.";
}

echo "Testing 15 tasks at different concurrency levels...\n\n";

$concurrencyLevels = [1, 3, 5, 10, 15];
$results = [];

foreach ($concurrencyLevels as $level) {
    echo "Concurrency {$level}: ";
    
    $processor = BatchProcessor::create($agent);
    $processor->addMany($testTasks);
    
    $startTime = microtime(true);
    $taskResults = $processor->run(concurrency: $level);
    $duration = microtime(true) - $startTime;
    
    $stats = $processor->getStats();
    $throughput = count($taskResults) / $duration;
    $avgLatency = $duration / count($taskResults);
    
    $results[$level] = [
        'duration' => $duration,
        'throughput' => $throughput,
        'avg_latency' => $avgLatency,
        'success_rate' => $stats['success_rate'],
        'total_tokens' => $stats['total_tokens']['total'],
    ];
    
    echo round($duration, 2) . "s ";
    echo "(" . round($throughput, 2) . " tasks/sec)\n";
}

echo "\n" . str_repeat("─", 60) . "\n";
echo "Performance Summary:\n";
echo str_repeat("─", 60) . "\n\n";

echo sprintf("%-12s %-10s %-15s %-12s %-10s\n", 
    "Concurrency", "Duration", "Throughput", "Avg Latency", "Tokens");
echo str_repeat("─", 60) . "\n";

foreach ($results as $level => $data) {
    echo sprintf("%-12d %-10s %-15s %-12s %-10d\n",
        $level,
        round($data['duration'], 2) . "s",
        round($data['throughput'], 2) . " t/s",
        round($data['avg_latency'], 2) . "s",
        $data['total_tokens']
    );
}

echo "\n";

// Find optimal concurrency
$bestThroughput = 0;
$optimalConcurrency = 1;

foreach ($results as $level => $data) {
    if ($data['throughput'] > $bestThroughput) {
        $bestThroughput = $data['throughput'];
        $optimalConcurrency = $level;
    }
}

echo "Optimal concurrency: {$optimalConcurrency} (highest throughput)\n";

echo "\n\n";

// ============================================================================
// EXAMPLE 2: Adaptive Concurrency Manager
// ============================================================================

echo "Example 2: Adaptive Concurrency\n";
echo str_repeat("─", 60) . "\n\n";

class AdaptiveConcurrencyManager
{
    private int $concurrency = 3;
    private array $performanceHistory = [];
    private int $minConcurrency = 1;
    private int $maxConcurrency = 10;
    
    public function execute(BatchProcessor $processor): array
    {
        $startTime = microtime(true);
        
        echo "Executing with concurrency: {$this->concurrency}\n";
        
        $results = $processor->run(concurrency: $this->concurrency);
        $duration = microtime(true) - $startTime;
        
        $stats = $processor->getStats();
        $successRate = $stats['success_rate'];
        
        // Record performance
        $this->performanceHistory[] = [
            'concurrency' => $this->concurrency,
            'duration' => $duration,
            'success_rate' => $successRate,
        ];
        
        // Adjust concurrency
        $this->adjustConcurrency($successRate, $duration);
        
        return $results;
    }
    
    private function adjustConcurrency(float $successRate, float $duration): void
    {
        echo "  Success rate: " . round($successRate * 100, 1) . "%\n";
        echo "  Duration: " . round($duration, 2) . "s\n";
        
        // Increase if performing well
        if ($successRate > 0.95 && $duration < 10.0) {
            $newConcurrency = min($this->concurrency + 1, $this->maxConcurrency);
            if ($newConcurrency > $this->concurrency) {
                echo "  → Increasing concurrency to {$newConcurrency}\n";
                $this->concurrency = $newConcurrency;
            }
        }
        
        // Decrease if errors or too slow
        if ($successRate < 0.85 || $duration > 20.0) {
            $newConcurrency = max($this->concurrency - 1, $this->minConcurrency);
            if ($newConcurrency < $this->concurrency) {
                echo "  → Decreasing concurrency to {$newConcurrency}\n";
                $this->concurrency = $newConcurrency;
            }
        }
        
        echo "\n";
    }
    
    public function getPerformanceHistory(): array
    {
        return $this->performanceHistory;
    }
}

// Test adaptive manager
$adaptiveManager = new AdaptiveConcurrencyManager();

echo "Running 3 iterations with adaptive concurrency...\n\n";

for ($iteration = 1; $iteration <= 3; $iteration++) {
    echo "Iteration {$iteration}:\n";
    echo str_repeat("─", 40) . "\n";
    
    $processor = BatchProcessor::create($agent);
    
    $tasks = [];
    for ($i = 1; $i <= 10; $i++) {
        $tasks["task_{$i}"] = "Calculate " . ($i * 5) . " + " . ($i * 3);
    }
    
    $processor->addMany($tasks);
    $adaptiveManager->execute($processor);
}

echo str_repeat("─", 60) . "\n";
echo "Adaptive tuning complete\n\n";

echo "\n";

// ============================================================================
// EXAMPLE 3: Cost-Aware Concurrency
// ============================================================================

echo "Example 3: Cost-Aware Concurrency\n";
echo str_repeat("─", 60) . "\n\n";

class CostAwareConcurrency
{
    public function determineConcurrency(
        int $taskCount,
        float $dailyBudget,
        float $currentSpend,
        float $estimatedCostPerTask = 0.05
    ): int {
        $remainingBudget = $dailyBudget - $currentSpend;
        $affordableTasks = floor($remainingBudget / $estimatedCostPerTask);
        
        echo "Budget Analysis:\n";
        echo "  Daily budget: \${$dailyBudget}\n";
        echo "  Current spend: \${$currentSpend}\n";
        echo "  Remaining: \$" . round($remainingBudget, 2) . "\n";
        echo "  Affordable tasks: {$affordableTasks}\n";
        echo "  Requested tasks: {$taskCount}\n\n";
        
        if ($affordableTasks < $taskCount) {
            // Reduce concurrency to stretch budget
            $concurrency = max(1, (int) ceil($taskCount / 10));
            echo "⚠️  Budget constrained\n";
            echo "  Reducing concurrency to: {$concurrency}\n";
            echo "  (Slower execution to stay within budget)\n";
            return $concurrency;
        }
        
        // Can afford high concurrency
        $concurrency = min(10, $taskCount);
        echo "✓ Budget allows high concurrency\n";
        echo "  Setting concurrency to: {$concurrency}\n";
        return $concurrency;
    }
}

$costManager = new CostAwareConcurrency();

// Scenario 1: Plenty of budget
echo "Scenario 1: Plenty of Budget\n";
echo str_repeat("─", 40) . "\n";
$concurrency1 = $costManager->determineConcurrency(
    taskCount: 20,
    dailyBudget: 100.0,
    currentSpend: 10.0
);
echo "\n";

// Scenario 2: Limited budget
echo "Scenario 2: Limited Budget\n";
echo str_repeat("─", 40) . "\n";
$concurrency2 = $costManager->determineConcurrency(
    taskCount: 20,
    dailyBudget: 100.0,
    currentSpend: 95.0
);
echo "\n";

echo "\n";

// ============================================================================
// EXAMPLE 4: Performance Monitoring
// ============================================================================

echo "Example 4: Performance Monitoring\n";
echo str_repeat("─", 60) . "\n\n";

class PerformanceMonitor
{
    private array $metrics = [];
    
    public function recordExecution(
        int $concurrency,
        int $taskCount,
        float $duration,
        float $successRate,
        int $totalTokens
    ): void {
        $this->metrics[] = [
            'concurrency' => $concurrency,
            'task_count' => $taskCount,
            'duration' => $duration,
            'throughput' => $taskCount / $duration,
            'success_rate' => $successRate,
            'total_tokens' => $totalTokens,
            'tokens_per_task' => $totalTokens / $taskCount,
            'timestamp' => time(),
        ];
    }
    
    public function getReport(): string
    {
        if (empty($this->metrics)) {
            return "No metrics recorded\n";
        }
        
        $report = "Performance Report:\n";
        $report .= str_repeat("═", 60) . "\n\n";
        
        $avgThroughput = array_sum(array_column($this->metrics, 'throughput')) / count($this->metrics);
        $avgSuccessRate = array_sum(array_column($this->metrics, 'success_rate')) / count($this->metrics);
        $totalTasks = array_sum(array_column($this->metrics, 'task_count'));
        $totalTokens = array_sum(array_column($this->metrics, 'total_tokens'));
        
        $report .= "Summary:\n";
        $report .= "  Total executions: " . count($this->metrics) . "\n";
        $report .= "  Total tasks: {$totalTasks}\n";
        $report .= "  Total tokens: {$totalTokens}\n";
        $report .= "  Avg throughput: " . round($avgThroughput, 2) . " tasks/sec\n";
        $report .= "  Avg success rate: " . round($avgSuccessRate * 100, 1) . "%\n\n";
        
        $report .= "Recommendations:\n";
        
        if ($avgSuccessRate < 0.9) {
            $report .= "  ⚠️  Success rate below 90% - consider reducing concurrency\n";
        } else {
            $report .= "  ✓ Success rate is healthy\n";
        }
        
        if ($avgThroughput < 1.0) {
            $report .= "  ⚠️  Low throughput - consider increasing concurrency\n";
        } else {
            $report .= "  ✓ Throughput is acceptable\n";
        }
        
        return $report;
    }
}

$monitor = new PerformanceMonitor();

echo "Recording performance metrics for different configurations...\n\n";

$testConfigs = [
    ['concurrency' => 3, 'tasks' => 10],
    ['concurrency' => 5, 'tasks' => 15],
    ['concurrency' => 7, 'tasks' => 20],
];

foreach ($testConfigs as $config) {
    $processor = BatchProcessor::create($agent);
    
    $tasks = [];
    for ($i = 1; $i <= $config['tasks']; $i++) {
        $tasks["task_{$i}"] = "What is {$i} + {$i}?";
    }
    
    $processor->addMany($tasks);
    
    $start = microtime(true);
    $results = $processor->run(concurrency: $config['concurrency']);
    $duration = microtime(true) - $start;
    
    $stats = $processor->getStats();
    
    $monitor->recordExecution(
        concurrency: $config['concurrency'],
        taskCount: $config['tasks'],
        duration: $duration,
        successRate: $stats['success_rate'],
        totalTokens: $stats['total_tokens']['total']
    );
    
    echo "✓ Tested: Concurrency {$config['concurrency']}, {$config['tasks']} tasks\n";
}

echo "\n" . $monitor->getReport() . "\n";

echo "\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    Key Takeaways                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ Test multiple concurrency levels to find optimal setting\n";
echo "✓ Higher concurrency ≠ always better (diminishing returns)\n";
echo "✓ Adaptive tuning adjusts concurrency based on performance\n";
echo "✓ Cost-aware strategies balance speed vs budget\n";
echo "✓ Monitor throughput, latency, success rate, and tokens\n";
echo "✓ Typical sweet spot: 3-5 concurrent tasks\n";
echo "✓ Adjust based on API limits, error rates, and cost\n\n";

echo "Next: 07-production-async-system.php\n";
