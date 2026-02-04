<?php

/**
 * Chapter 17: Evaluation Harnesses and QA
 * Example 05: Cost Tracking
 * 
 * Demonstrates tracking token usage, costs, and latency during evaluation.
 * Helps optimize agent performance and control expenses.
 * 
 * Run: php 05-cost-tracking.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Observability\CostTracker as AgentCostTracker;
use ClaudePhp\ClaudePhp;

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  Chapter 17: Cost Tracking                                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Error: ANTHROPIC_API_KEY environment variable not set\n");
}

/**
 * Cost tracking for evaluations
 */
class EvaluationCostTracker
{
    private array $executions = [];
    
    // Claude Sonnet 4 pricing (as of 2024)
    private const INPUT_COST_PER_1M = 3.00;
    private const OUTPUT_COST_PER_1M = 15.00;
    
    /**
     * Track a single test execution
     */
    public function trackExecution(
        string $testId,
        int $inputTokens,
        int $outputTokens,
        float $latencyMs,
        bool $cached = false
    ): void {
        $cost = $this->calculateCost($inputTokens, $outputTokens);
        
        $this->executions[] = [
            'test_id' => $testId,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'cost' => $cost,
            'latency_ms' => $latencyMs,
            'cached' => $cached,
            'timestamp' => time(),
        ];
    }
    
    /**
     * Calculate cost for token usage
     */
    private function calculateCost(int $inputTokens, int $outputTokens): float
    {
        $inputCost = ($inputTokens / 1_000_000) * self::INPUT_COST_PER_1M;
        $outputCost = ($outputTokens / 1_000_000) * self::OUTPUT_COST_PER_1M;
        
        return $inputCost + $outputCost;
    }
    
    /**
     * Get summary statistics
     */
    public function getSummary(): array
    {
        if (empty($this->executions)) {
            return [
                'total_executions' => 0,
                'total_cost' => 0,
                'total_tokens' => 0,
                'avg_cost_per_test' => 0,
                'avg_tokens_per_test' => 0,
                'avg_latency_ms' => 0,
            ];
        }
        
        $totalCost = array_sum(array_column($this->executions, 'cost'));
        $totalTokens = array_sum(array_column($this->executions, 'total_tokens'));
        $totalLatency = array_sum(array_column($this->executions, 'latency_ms'));
        $count = count($this->executions);
        
        return [
            'total_executions' => $count,
            'total_cost' => $totalCost,
            'total_tokens' => $totalTokens,
            'total_input_tokens' => array_sum(array_column($this->executions, 'input_tokens')),
            'total_output_tokens' => array_sum(array_column($this->executions, 'output_tokens')),
            'avg_cost_per_test' => $totalCost / $count,
            'avg_tokens_per_test' => $totalTokens / $count,
            'avg_latency_ms' => $totalLatency / $count,
            'min_latency_ms' => min(array_column($this->executions, 'latency_ms')),
            'max_latency_ms' => max(array_column($this->executions, 'latency_ms')),
        ];
    }
    
    /**
     * Get cost breakdown by test
     */
    public function getCostBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->executions as $exec) {
            $breakdown[] = [
                'test_id' => $exec['test_id'],
                'cost' => $exec['cost'],
                'tokens' => $exec['total_tokens'],
                'latency_ms' => $exec['latency_ms'],
            ];
        }
        
        // Sort by cost descending
        usort($breakdown, fn($a, $b) => $b['cost'] <=> $a['cost']);
        
        return $breakdown;
    }
    
    /**
     * Export to JSON
     */
    public function exportToJson(string $path): void
    {
        $data = [
            'summary' => $this->getSummary(),
            'executions' => $this->executions,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
        
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}

/**
 * Cost report display
 */
class CostReport
{
    public function __construct(
        private readonly array $summary,
        private readonly array $breakdown,
    ) {}
    
    public function display(): void
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                      COST REPORT                               ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Summary:\n";
        echo "  Total Executions: {$this->summary['total_executions']}\n";
        echo "  Total Cost: $" . number_format($this->summary['total_cost'], 6) . "\n";
        echo "  Total Tokens: " . number_format($this->summary['total_tokens']) . "\n";
        echo "    - Input: " . number_format($this->summary['total_input_tokens']) . "\n";
        echo "    - Output: " . number_format($this->summary['total_output_tokens']) . "\n\n";
        
        echo "Averages:\n";
        echo "  Cost per Test: $" . number_format($this->summary['avg_cost_per_test'], 6) . "\n";
        echo "  Tokens per Test: " . number_format($this->summary['avg_tokens_per_test'], 1) . "\n";
        echo "  Latency: " . number_format($this->summary['avg_latency_ms'], 2) . "ms\n";
        echo "    - Min: " . number_format($this->summary['min_latency_ms'], 2) . "ms\n";
        echo "    - Max: " . number_format($this->summary['max_latency_ms'], 2) . "ms\n\n";
        
        echo "Cost Breakdown (Top 5 Most Expensive):\n";
        echo str_repeat('─', 66) . "\n";
        
        $top5 = array_slice($this->breakdown, 0, 5);
        
        foreach ($top5 as $item) {
            echo "  {$item['test_id']}\n";
            echo "    Cost: $" . number_format($item['cost'], 6);
            echo "  |  Tokens: " . number_format($item['tokens']);
            echo "  |  Latency: " . number_format($item['latency_ms'], 2) . "ms\n";
        }
        
        // Calculate projected costs
        echo "\n";
        echo "Projected Costs:\n";
        $costPer1k = $this->summary['avg_cost_per_test'] * 1000;
        $costPer10k = $this->summary['avg_cost_per_test'] * 10000;
        $costPer100k = $this->summary['avg_cost_per_test'] * 100000;
        
        echo "  1,000 tests: $" . number_format($costPer1k, 2) . "\n";
        echo "  10,000 tests: $" . number_format($costPer10k, 2) . "\n";
        echo "  100,000 tests: $" . number_format($costPer100k, 2) . "\n";
    }
}

/**
 * Evaluation with integrated cost tracking
 */
class CostAwareEvaluator
{
    public function __construct(
        private readonly EvaluationCostTracker $costTracker = new EvaluationCostTracker(),
    ) {}
    
    public function evaluate(Agent $agent, array $testCases): array
    {
        echo "Running cost-aware evaluation...\n\n";
        
        $results = [];
        
        foreach ($testCases as $index => $test) {
            echo "Test " . ($index + 1) . "/{$this->getCount($testCases)}: {$test['id']}... ";
            
            $startTime = microtime(true);
            
            try {
                $result = $agent->run($test['input']);
                $latency = (microtime(true) - $startTime) * 1000;
                
                $output = $result->getAnswer();
                
                // Extract token usage from result (if available via usage tracking)
                // For this example, we'll estimate based on text length
                $inputTokens = $this->estimateTokens($test['input']);
                $outputTokens = $this->estimateTokens($output);
                
                $this->costTracker->trackExecution(
                    testId: $test['id'],
                    inputTokens: $inputTokens,
                    outputTokens: $outputTokens,
                    latencyMs: $latency,
                );
                
                $results[] = [
                    'test_id' => $test['id'],
                    'input' => $test['input'],
                    'output' => $output,
                    'latency_ms' => $latency,
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                ];
                
                echo "✅ (" . number_format($latency, 0) . "ms)\n";
            } catch (\Throwable $e) {
                echo "❌\n";
                $results[] = [
                    'test_id' => $test['id'],
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Rough token estimation (actual usage would come from API response)
     */
    private function estimateTokens(string $text): int
    {
        // Rough estimate: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }
    
    private function getCount(array $items): int
    {
        return count($items);
    }
    
    public function getCostTracker(): EvaluationCostTracker
    {
        return $this->costTracker;
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create agent
echo "Creating agent...\n\n";

$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant. Answer questions concisely and accurately.');

// Define test cases with varying complexity
$testCases = [
    [
        'id' => 'simple_math',
        'input' => 'What is 5 + 3?',
    ],
    [
        'id' => 'capitals',
        'input' => 'What is the capital of Italy?',
    ],
    [
        'id' => 'complex_reasoning',
        'input' => 'If a car travels 60 mph for 2.5 hours, how far does it go? Show your work.',
    ],
    [
        'id' => 'history',
        'input' => 'Who was the first president of the United States?',
    ],
    [
        'id' => 'science',
        'input' => 'What is photosynthesis? Explain briefly.',
    ],
];

// Run cost-aware evaluation
$evaluator = new CostAwareEvaluator();
$results = $evaluator->evaluate($agent, $testCases);

// Get cost tracker
$costTracker = $evaluator->getCostTracker();

// Display cost report
echo "\n";
$report = new CostReport(
    summary: $costTracker->getSummary(),
    breakdown: $costTracker->getCostBreakdown(),
);
$report->display();

// Export cost data
$exportPath = __DIR__ . '/cost-report.json';
$costTracker->exportToJson($exportPath);
echo "\n✅ Cost data exported to: {$exportPath}\n";

// Check if costs are within budget
echo "\n";
$budget = 0.01; // $0.01 budget
$actualCost = $costTracker->getSummary()['total_cost'];

if ($actualCost <= $budget) {
    echo "✅ Within budget! ({$actualCost} <= {$budget})\n";
} else {
    echo "⚠️  Over budget! ({$actualCost} > {$budget})\n";
    echo "   Consider optimizing prompts or using caching.\n";
}

echo "\n✅ Cost tracking complete!\n";
