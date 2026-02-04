<?php

declare(strict_types=1);

/**
 * Example 05: Token Budgeting and Monitoring
 *
 * Implements comprehensive token tracking, budgeting, and alerting
 * to prevent cost overruns in production.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

/**
 * Token budget manager with alerts and enforcement
 */
class TokenBudgetManager
{
    private array $budgets = [];
    private array $usage = [];
    private array $alerts = [];
    
    // Pricing per million tokens
    private array $pricing = [
        'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-haiku-20241022' => ['input' => 0.80, 'output' => 4.00],
    ];
    
    /**
     * Set a budget for a specific scope
     */
    public function setBudget(string $scope, int $tokenLimit, float $costLimit): void
    {
        $this->budgets[$scope] = [
            'token_limit' => $tokenLimit,
            'cost_limit' => $costLimit,
            'tokens_used' => 0,
            'cost_incurred' => 0,
            'requests' => 0,
            'started_at' => time(),
        ];
    }
    
    /**
     * Record token usage for a request
     */
    public function recordUsage(
        string $scope,
        string $model,
        int $inputTokens,
        int $outputTokens
    ): array {
        if (!isset($this->budgets[$scope])) {
            throw new \InvalidArgumentException("Budget not set for scope: {$scope}");
        }
        
        // Calculate cost
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
        $totalTokens = $inputTokens + $outputTokens;
        
        // Update usage
        $this->budgets[$scope]['tokens_used'] += $totalTokens;
        $this->budgets[$scope]['cost_incurred'] += $cost;
        $this->budgets[$scope]['requests']++;
        
        // Check thresholds and generate alerts
        $status = $this->checkThresholds($scope);
        
        return $status;
    }
    
    /**
     * Calculate cost based on model and token usage
     */
    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        if (!isset($this->pricing[$model])) {
            throw new \InvalidArgumentException("Unknown model: {$model}");
        }
        
        $inputCost = ($inputTokens * $this->pricing[$model]['input']) / 1_000_000;
        $outputCost = ($outputTokens * $this->pricing[$model]['output']) / 1_000_000;
        
        return $inputCost + $outputCost;
    }
    
    /**
     * Check if usage exceeds thresholds
     */
    private function checkThresholds(string $scope): array
    {
        $budget = $this->budgets[$scope];
        $tokenUsagePercent = ($budget['tokens_used'] / $budget['token_limit']) * 100;
        $costUsagePercent = ($budget['cost_incurred'] / $budget['cost_limit']) * 100;
        
        $status = [
            'scope' => $scope,
            'within_budget' => true,
            'token_usage_percent' => $tokenUsagePercent,
            'cost_usage_percent' => $costUsagePercent,
            'alerts' => [],
        ];
        
        // Token alerts
        if ($tokenUsagePercent >= 100) {
            $status['within_budget'] = false;
            $status['alerts'][] = "🚨 TOKEN LIMIT EXCEEDED for {$scope}!";
        } elseif ($tokenUsagePercent >= 90) {
            $status['alerts'][] = "⚠️  TOKEN WARNING: {$scope} at " . number_format($tokenUsagePercent, 1) . "%";
        } elseif ($tokenUsagePercent >= 75) {
            $status['alerts'][] = "⚡ TOKEN NOTICE: {$scope} at " . number_format($tokenUsagePercent, 1) . "%";
        }
        
        // Cost alerts
        if ($costUsagePercent >= 100) {
            $status['within_budget'] = false;
            $status['alerts'][] = "🚨 COST LIMIT EXCEEDED for {$scope}!";
        } elseif ($costUsagePercent >= 90) {
            $status['alerts'][] = "⚠️  COST WARNING: {$scope} at " . number_format($costUsagePercent, 1) . "%";
        } elseif ($costUsagePercent >= 75) {
            $status['alerts'][] = "⚡ COST NOTICE: {$scope} at " . number_format($costUsagePercent, 1) . "%";
        }
        
        return $status;
    }
    
    /**
     * Check if a request is allowed within budget
     */
    public function isAllowed(string $scope): bool
    {
        if (!isset($this->budgets[$scope])) {
            return true;
        }
        
        $budget = $this->budgets[$scope];
        $tokenUsagePercent = ($budget['tokens_used'] / $budget['token_limit']) * 100;
        $costUsagePercent = ($budget['cost_incurred'] / $budget['cost_limit']) * 100;
        
        return $tokenUsagePercent < 100 && $costUsagePercent < 100;
    }
    
    /**
     * Get budget status
     */
    public function getStatus(string $scope): array
    {
        if (!isset($this->budgets[$scope])) {
            throw new \InvalidArgumentException("Budget not set for scope: {$scope}");
        }
        
        $budget = $this->budgets[$scope];
        $duration = time() - $budget['started_at'];
        
        return [
            'scope' => $scope,
            'budget' => [
                'token_limit' => $budget['token_limit'],
                'cost_limit' => $budget['cost_limit'],
            ],
            'usage' => [
                'tokens_used' => $budget['tokens_used'],
                'tokens_remaining' => max(0, $budget['token_limit'] - $budget['tokens_used']),
                'cost_incurred' => $budget['cost_incurred'],
                'cost_remaining' => max(0, $budget['cost_limit'] - $budget['cost_incurred']),
                'requests' => $budget['requests'],
            ],
            'percentages' => [
                'tokens' => ($budget['tokens_used'] / $budget['token_limit']) * 100,
                'cost' => ($budget['cost_incurred'] / $budget['cost_limit']) * 100,
            ],
            'duration_seconds' => $duration,
            'avg_tokens_per_request' => $budget['requests'] > 0 ? 
                $budget['tokens_used'] / $budget['requests'] : 0,
            'avg_cost_per_request' => $budget['requests'] > 0 ? 
                $budget['cost_incurred'] / $budget['requests'] : 0,
        ];
    }
    
    /**
     * Print budget summary
     */
    public function printSummary(string $scope): void
    {
        $status = $this->getStatus($scope);
        
        echo "\n=== Budget Summary: {$scope} ===\n";
        echo "Duration: {$status['duration_seconds']}s\n";
        echo "Requests: {$status['usage']['requests']}\n\n";
        
        echo "Token Usage:\n";
        echo "  Used: {$status['usage']['tokens_used']} / {$status['budget']['token_limit']} ";
        echo "(" . number_format($status['percentages']['tokens'], 1) . "%)\n";
        echo "  Remaining: {$status['usage']['tokens_remaining']}\n";
        echo "  Avg/request: " . number_format($status['avg_tokens_per_request'], 0) . "\n\n";
        
        echo "Cost:\n";
        echo "  Incurred: $" . number_format($status['usage']['cost_incurred'], 4) . " / ";
        echo "$" . number_format($status['budget']['cost_limit'], 4) . " ";
        echo "(" . number_format($status['percentages']['cost'], 1) . "%)\n";
        echo "  Remaining: $" . number_format($status['usage']['cost_remaining'], 4) . "\n";
        echo "  Avg/request: $" . number_format($status['avg_cost_per_request'], 6) . "\n";
    }
}

/**
 * Budget-aware agent wrapper
 */
class BudgetedAgent
{
    public function __construct(
        private Agent $agent,
        private TokenBudgetManager $budgetManager,
        private string $scope,
        private string $model
    ) {}
    
    public function run(string $query): ?array
    {
        // Check budget before execution
        if (!$this->budgetManager->isAllowed($this->scope)) {
            echo "❌ Request blocked: Budget exceeded for {$this->scope}\n";
            return null;
        }
        
        // Execute
        $result = $this->agent->run($query);
        
        // Record usage
        $usage = $result->getTokenUsage();
        $status = $this->budgetManager->recordUsage(
            $this->scope,
            $this->model,
            $usage['input'],
            $usage['output']
        );
        
        // Display alerts
        foreach ($status['alerts'] as $alert) {
            echo $alert . "\n";
        }
        
        return [
            'result' => $result,
            'status' => $status,
        ];
    }
}

// Demo
echo "=== Token Budgeting Demo ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create budget manager
$budgetManager = new TokenBudgetManager();

// Set budget: 50,000 tokens or $0.10 for this demo session
$budgetManager->setBudget('demo_session', 50_000, 0.10);

echo "📊 Budget Set:\n";
echo "   Token Limit: 50,000\n";
echo "   Cost Limit: $0.10\n\n";

// Create agent
$tool = Tool::create('get_data')
    ->description('Get data about a topic')
    ->parameter('topic', 'string', 'Topic to get data for')
    ->required('topic')
    ->handler(fn($input) => "Data for {$input['topic']}: [mock data]");

$agent = Agent::create($client)
    ->withTool($tool)
    ->withModel('claude-3-5-haiku-20241022')
    ->maxIterations(3);

$budgetedAgent = new BudgetedAgent(
    $agent,
    $budgetManager,
    'demo_session',
    'claude-3-5-haiku-20241022'
);

// Execute requests
$queries = [
    'What is PHP?',
    'Explain object-oriented programming',
    'List popular PHP frameworks',
    'Describe REST API principles',
    'What is Docker?',
];

foreach ($queries as $i => $query) {
    echo "\n--- Request " . ($i + 1) . " ---\n";
    echo "Query: {$query}\n";
    
    $response = $budgetedAgent->run($query);
    
    if ($response !== null) {
        echo "Answer: " . substr($response['result']->getAnswer(), 0, 80) . "...\n";
    }
}

// Final summary
$budgetManager->printSummary('demo_session');

echo "\n✅ Token budgeting prevents cost overruns in production!\n";
echo "💡 Use for: rate limiting, cost control, multi-tenant systems\n";
