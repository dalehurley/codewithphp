<?php

declare(strict_types=1);

/**
 * Example 03: Model Routing
 *
 * Demonstrates intelligent model routing - using smaller, faster, cheaper models
 * for simple tasks and larger models only for complex reasoning.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

/**
 * Task complexity analyzer
 */
class TaskComplexityAnalyzer
{
    /**
     * Analyze task complexity based on heuristics
     */
    public function analyze(string $task): array
    {
        $complexity = 'simple';
        $score = 0;
        $reasons = [];
        
        // Check for complexity indicators
        if (preg_match('/\b(analyze|complex|detailed|comprehensive|explain why|reasoning)\b/i', $task)) {
            $score += 2;
            $reasons[] = 'Requires analysis or reasoning';
        }
        
        if (preg_match('/\b(multiple|several|various|compare|contrast)\b/i', $task)) {
            $score += 1;
            $reasons[] = 'Multiple elements to consider';
        }
        
        if (str_word_count($task) > 20) {
            $score += 1;
            $reasons[] = 'Long task description';
        }
        
        if (preg_match('/\b(calculate|compute|solve)\b/i', $task)) {
            $score += 1;
            $reasons[] = 'Mathematical computation';
        }
        
        // Determine complexity level
        if ($score >= 3) {
            $complexity = 'complex';
        } elseif ($score >= 1) {
            $complexity = 'moderate';
        }
        
        return [
            'complexity' => $complexity,
            'score' => $score,
            'reasons' => $reasons,
        ];
    }
    
    /**
     * Get recommended model based on complexity
     */
    public function getRecommendedModel(string $complexity): string
    {
        return match ($complexity) {
            'complex' => 'claude-3-5-sonnet-20241022', // Most capable
            'moderate' => 'claude-3-5-haiku-20241022', // Balanced
            'simple' => 'claude-3-5-haiku-20241022',   // Fast & cheap
            default => 'claude-3-5-haiku-20241022',
        };
    }
}

/**
 * Adaptive agent router with model selection
 */
class AdaptiveAgentRouter
{
    private array $agents = [];
    private TaskComplexityAnalyzer $analyzer;
    private array $stats = [
        'tasks_by_model' => [],
        'tokens_by_model' => [],
        'duration_by_model' => [],
    ];
    
    public function __construct(ClaudePhp $client)
    {
        $this->analyzer = new TaskComplexityAnalyzer();
        
        // Create agents for different models
        $this->agents['claude-3-5-sonnet-20241022'] = $this->createAgent($client, 'claude-3-5-sonnet-20241022');
        $this->agents['claude-3-5-haiku-20241022'] = $this->createAgent($client, 'claude-3-5-haiku-20241022');
    }
    
    private function createAgent(ClaudePhp $client, string $model): Agent
    {
        $tool = Tool::create('get_info')
            ->description('Get information about a topic')
            ->parameter('topic', 'string', 'Topic to get info about')
            ->required('topic')
            ->handler(fn($input) => "Information about {$input['topic']}: [Sample data]");
        
        return Agent::create($client)
            ->withTool($tool)
            ->withModel($model)
            ->maxIterations(5);
    }
    
    public function route(string $task): array
    {
        $start = microtime(true);
        
        // Analyze complexity
        $analysis = $this->analyzer->analyze($task);
        $model = $this->analyzer->getRecommendedModel($analysis['complexity']);
        
        echo "\n📊 Task: " . substr($task, 0, 60) . "...\n";
        echo "Complexity: {$analysis['complexity']} (score: {$analysis['score']})\n";
        if (!empty($analysis['reasons'])) {
            echo "Reasons: " . implode(', ', $analysis['reasons']) . "\n";
        }
        echo "Routing to: {$model}\n";
        
        // Execute with selected agent
        $agent = $this->agents[$model];
        $result = $agent->run($task);
        
        $duration = microtime(true) - $start;
        $usage = $result->getTokenUsage();
        $totalTokens = $usage['input'] + $usage['output'];
        
        // Track stats
        if (!isset($this->stats['tasks_by_model'][$model])) {
            $this->stats['tasks_by_model'][$model] = 0;
            $this->stats['tokens_by_model'][$model] = 0;
            $this->stats['duration_by_model'][$model] = 0;
        }
        
        $this->stats['tasks_by_model'][$model]++;
        $this->stats['tokens_by_model'][$model] += $totalTokens;
        $this->stats['duration_by_model'][$model] += $duration;
        
        return [
            'answer' => $result->getAnswer(),
            'model' => $model,
            'complexity' => $analysis['complexity'],
            'tokens' => $totalTokens,
            'duration' => $duration,
        ];
    }
    
    public function getStats(): array
    {
        return $this->stats;
    }
    
    public function printStats(): void
    {
        echo "\n=== Model Routing Statistics ===\n";
        
        foreach ($this->stats['tasks_by_model'] as $model => $count) {
            $tokens = $this->stats['tokens_by_model'][$model];
            $duration = $this->stats['duration_by_model'][$model];
            
            // Pricing (approximate)
            $costs = [
                'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00], // per million
                'claude-3-5-haiku-20241022' => ['input' => 0.80, 'output' => 4.00],
            ];
            
            $avgCost = (($costs[$model]['input'] + $costs[$model]['output']) / 2) / 1_000_000;
            $estimatedCost = $tokens * $avgCost;
            
            echo "\nModel: {$model}\n";
            echo "  Tasks: {$count}\n";
            echo "  Total tokens: {$tokens}\n";
            echo "  Avg tokens/task: " . number_format($tokens / $count, 0) . "\n";
            echo "  Total duration: " . number_format($duration, 2) . "s\n";
            echo "  Avg duration/task: " . number_format($duration / $count, 2) . "s\n";
            echo "  Estimated cost: $" . number_format($estimatedCost, 4) . "\n";
        }
        
        // Calculate total and potential savings
        $totalTokens = array_sum($this->stats['tokens_by_model']);
        $sonnetCost = ($costs['claude-3-5-sonnet-20241022']['input'] + 
                       $costs['claude-3-5-sonnet-20241022']['output']) / 2 / 1_000_000;
        $costIfAllSonnet = $totalTokens * $sonnetCost;
        
        $actualCost = 0;
        foreach ($this->stats['tokens_by_model'] as $model => $tokens) {
            $avgCost = (($costs[$model]['input'] + $costs[$model]['output']) / 2) / 1_000_000;
            $actualCost += $tokens * $avgCost;
        }
        
        echo "\n=== Cost Comparison ===\n";
        echo "Cost with routing: $" . number_format($actualCost, 4) . "\n";
        echo "Cost if all Sonnet: $" . number_format($costIfAllSonnet, 4) . "\n";
        echo "Savings: $" . number_format($costIfAllSonnet - $actualCost, 4) . 
             " (" . number_format((($costIfAllSonnet - $actualCost) / $costIfAllSonnet) * 100, 1) . "%)\n";
    }
}

// Demo: Route various tasks
echo "=== Model Routing Demo ===\n";

$router = new AdaptiveAgentRouter($client);

$tasks = [
    'What is PHP?',
    'List the top 3 PHP frameworks',
    'Analyze the architectural differences between Laravel and Symfony, considering scalability, performance, and developer experience',
    'What is the current time?',
    'Compare and contrast functional programming versus object-oriented programming in PHP, with detailed examples',
    'Get the weather for San Francisco',
    'Explain the mathematical principles behind RSA encryption and how they apply to web security',
];

foreach ($tasks as $task) {
    $result = $router->route($task);
    echo "Result: " . substr($result['answer'], 0, 80) . "...\n";
    echo "Tokens: {$result['tokens']}, Duration: " . number_format($result['duration'], 2) . "s\n";
}

$router->printStats();

echo "\n✅ Model routing optimizes costs by using appropriate models for each task!\n";
echo "💡 Simple tasks → Haiku (fast, cheap), Complex reasoning → Sonnet (powerful)\n";
