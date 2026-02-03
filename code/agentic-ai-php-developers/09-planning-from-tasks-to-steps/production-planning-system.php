<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudeAgents\Agents\PlanExecuteAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Production Planning System
 * 
 * A complete, production-ready planning system with:
 * - Comprehensive logging
 * - Tool integration
 * - ML optimization
 * - Batch execution support
 * - Error handling and monitoring
 */

class ProductionPlanningSystem
{
    private ClaudePhp $client;
    private Logger $logger;
    private array $tools = [];
    
    public function __construct(string $apiKey)
    {
        $this->client = new ClaudePhp(apiKey: $apiKey);
        
        // Setup logger
        $this->logger = new Logger('planning-system');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        $this->setupTools();
        
        $this->logger->info('Production planning system initialized');
    }
    
    private function setupTools(): void
    {
        // Add production tools
        $this->tools[] = Tool::create('execute_query')
            ->description('Execute a database query')
            ->parameter('query', 'string', 'Query to execute')
            ->required('query')
            ->handler(function (array $input): string {
                $this->logger->info('Executing query', ['query' => substr($input['query'], 0, 100)]);
                // Simulate query execution
                return json_encode(['success' => true, 'rows' => 42]);
            });
        
        $this->tools[] = Tool::create('send_notification')
            ->description('Send notification via email, SMS, or Slack')
            ->parameter('channel', 'string', 'Notification channel (email, sms, slack)')
            ->parameter('message', 'string', 'Notification message')
            ->required('channel', 'message')
            ->handler(function (array $input): string {
                $this->logger->info('Sending notification', [
                    'channel' => $input['channel'],
                    'message' => substr($input['message'], 0, 50),
                ]);
                return json_encode(['sent' => true, 'channel' => $input['channel']]);
            });
        
        $this->tools[] = Tool::create('log_metric')
            ->description('Log a metric for monitoring')
            ->parameter('metric_name', 'string', 'Name of the metric')
            ->parameter('value', 'string', 'Metric value')
            ->required('metric_name', 'value')
            ->handler(function (array $input): string {
                $this->logger->info('Logging metric', [
                    'metric' => $input['metric_name'],
                    'value' => $input['value'],
                ]);
                return json_encode(['logged' => true]);
            });
    }
    
    public function executePlan(string $task, array $options = []): array
    {
        $startTime = microtime(true);
        
        $this->logger->info('Starting plan execution', ['task' => substr($task, 0, 100)]);
        
        // Create agent with all features
        $agent = new PlanExecuteAgent($this->client, array_merge([
            'tools' => $this->tools,
            'allow_replan' => true,
            'enable_ml_optimization' => true,
            'ml_history_path' => __DIR__ . '/production_plan_history.json',
            'logger' => $this->logger,
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
        ], $options));
        
        // Execute
        $result = $agent->run($task);
        
        $duration = microtime(true) - $startTime;
        
        // Build response
        $response = [
            'success' => $result->isSuccess(),
            'answer' => $result->getAnswer(),
            'metadata' => [
                'duration' => round($duration, 3),
                'iterations' => $result->getIterations(),
                'token_usage' => $result->getTokenUsage(),
                'plan_metadata' => $result->getMetadata(),
            ],
        ];
        
        if (!$result->isSuccess()) {
            $response['error'] = $result->getError();
            $this->logger->error('Plan execution failed', ['error' => $result->getError()]);
        } else {
            $this->logger->info('Plan execution completed', [
                'duration' => $duration,
                'iterations' => $result->getIterations(),
            ]);
        }
        
        return $response;
    }
    
    public function executeBatch(array $tasks): array
    {
        $results = [];
        
        foreach ($tasks as $i => $task) {
            $this->logger->info("Executing batch task " . ($i + 1) . "/" . count($tasks));
            $results[] = $this->executePlan($task);
        }
        
        return $results;
    }
}

// Usage
$system = new ProductionPlanningSystem(getenv('ANTHROPIC_API_KEY'));

echo "=== Production Planning System ===\n\n";

// Execute a complex task
$task = "Process monthly sales report:\n" .
        "1. Query sales data for last 30 days\n" .
        "2. Calculate key metrics (revenue, growth, top products)\n" .
        "3. Generate summary report\n" .
        "4. Send notifications to stakeholders\n" .
        "5. Log completion metrics";

$result = $system->executePlan($task);

echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "Duration: {$result['metadata']['duration']}s\n";
echo "Iterations: {$result['metadata']['iterations']}\n";
echo "Tokens: " . json_encode($result['metadata']['token_usage']) . "\n\n";

echo "Answer:\n";
echo $result['answer'] . "\n\n";

// Execute batch tasks
echo "=== Batch Execution ===\n\n";

$batchTasks = [
    "Generate Q1 financial summary",
    "Process customer feedback survey results",
    "Create inventory restock recommendations",
];

$batchResults = $system->executeBatch($batchTasks);

echo "Completed " . count($batchResults) . " tasks\n";
$successCount = count(array_filter($batchResults, fn($r) => $r['success']));
echo "Success rate: " . round(($successCount / count($batchResults)) * 100) . "%\n";
