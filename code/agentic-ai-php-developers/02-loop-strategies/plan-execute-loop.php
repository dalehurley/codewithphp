<?php

/**
 * PlanExecuteLoop Example - Upfront Planning Workflow
 * 
 * Demonstrates systematic execution where the agent:
 * 1. Plans all steps upfront
 * 2. Executes steps in sequence
 * 3. Tracks progress
 * 4. Replans if steps fail (optional)
 * 
 * Use case: Complex multi-step workflows with dependencies
 */

declare(strict_types=1);

require_once __DIR__ . '/../../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define database and reporting tools
$queryDatabase = Tool::create('query_database')
    ->description('Execute SQL query on the orders database')
    ->parameter('query', 'string', 'SQL query to execute')
    ->required('query')
    ->handler(function (array $input): string {
        echo "  🗄️  Executing query: {$input['query']}\n";
        sleep(1); // Simulate query execution
        
        // Simulate query results
        $results = [
            'rows_returned' => rand(100, 1000),
            'execution_time_ms' => rand(50, 500),
            'data_preview' => [
                ['id' => 1, 'product' => 'Widget A', 'revenue' => 1250.50],
                ['id' => 2, 'product' => 'Widget B', 'revenue' => 890.25],
                ['id' => 3, 'product' => 'Widget C', 'revenue' => 2100.75]
            ]
        ];
        
        return json_encode($results);
    });

$aggregateData = Tool::create('aggregate_data')
    ->description('Aggregate and calculate statistics from query results')
    ->parameter('data', 'string', 'JSON data to aggregate')
    ->parameter('group_by', 'string', 'Field to group by')
    ->required(['data', 'group_by'])
    ->handler(function (array $input): string {
        echo "  📊 Aggregating data by: {$input['group_by']}\n";
        sleep(1); // Simulate aggregation
        
        return json_encode([
            'total_revenue' => rand(50000, 150000),
            'total_orders' => rand(500, 2000),
            'average_order_value' => rand(80, 200),
            'groups' => [
                'North Region' => rand(20000, 50000),
                'South Region' => rand(15000, 40000),
                'East Region' => rand(10000, 30000),
                'West Region' => rand(5000, 30000)
            ]
        ]);
    });

$generateReport = Tool::create('generate_report')
    ->description('Generate a formatted report from aggregated data')
    ->parameter('data', 'string', 'Aggregated data in JSON format')
    ->parameter('format', 'string', 'Report format (pdf, csv, html)')
    ->parameter('title', 'string', 'Report title')
    ->required(['data', 'format', 'title'])
    ->handler(function (array $input): string {
        echo "  📄 Generating {$input['format']} report: {$input['title']}\n";
        sleep(2); // Simulate report generation
        
        return json_encode([
            'format' => $input['format'],
            'filename' => 'report_' . time() . '.' . $input['format'],
            'size_kb' => rand(50, 500),
            'pages' => rand(5, 20),
            'status' => 'generated'
        ]);
    });

$sendEmail = Tool::create('send_email')
    ->description('Send email with optional attachment')
    ->parameter('recipient', 'string', 'Email recipient address')
    ->parameter('subject', 'string', 'Email subject line')
    ->parameter('body', 'string', 'Email body content')
    ->parameter('attachment', 'string', 'Filename to attach', false)
    ->required(['recipient', 'subject', 'body'])
    ->handler(function (array $input): string {
        echo "  📧 Sending email to: {$input['recipient']}\n";
        sleep(1); // Simulate email sending
        
        return json_encode([
            'recipient' => $input['recipient'],
            'subject' => $input['subject'],
            'status' => 'sent',
            'message_id' => 'msg_' . uniqid(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });

// Create agent with PlanExecuteLoop
$agent = Agent::create($client)
    ->withSystemPrompt(
        'You are a business intelligence assistant specializing in data analysis and reporting. ' .
        'Break down complex tasks into clear, sequential steps. ' .
        'Execute each step systematically and track progress.'
    )
    ->withTools([$queryDatabase, $aggregateData, $generateReport, $sendEmail])
    ->withLoopStrategy(new PlanExecuteLoop(
        allowReplan: true,      // Regenerate plan if steps fail
        showPlanInOutput: true, // Include plan in final response
        validatePlan: true,     // Validate plan feasibility
        maxSteps: 15
    ))
    ->maxIterations(12)
    ->onPlanGenerated(function (array $plan) {
        echo "\n📋 Generated Plan:\n";
        foreach ($plan['steps'] as $i => $step) {
            echo "  " . ($i + 1) . ". {$step['description']}\n";
        }
        echo "\n";
    })
    ->onUpdate(function ($update) {
        if ($update->getType() === 'step_start') {
            echo "▶️  Starting: {$update->getMessage()}\n";
        } elseif ($update->getType() === 'step_complete') {
            echo "✅ Completed: {$update->getMessage()}\n\n";
        }
    });

// Complex multi-step task
$task = "Generate a comprehensive sales analysis report:\n" .
        "1. Query October 2024 order data from the database\n" .
        "2. Aggregate the data by region and calculate key metrics\n" .
        "3. Generate a PDF report with visualizations\n" .
        "4. Email the report to manager@company.com with subject 'Q4 Sales Analysis'";

echo str_repeat('=', 80) . "\n";
echo "Complex Multi-Step Task (PlanExecuteLoop)\n";
echo str_repeat('=', 80) . "\n\n";
echo "Task: {$task}\n\n";

try {
    $startTime = microtime(true);
    $result = $agent->run($task);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "📊 Agent Response:\n";
    echo str_repeat('=', 80) . "\n";
    echo $result->getAnswer() . "\n";
    echo str_repeat('=', 80) . "\n";
    
    // Show execution statistics
    $metadata = $result->getMetadata();
    echo "\n📈 Execution Statistics:\n";
    echo "  - Total duration: {$duration}ms\n";
    echo "  - Steps executed: " . ($metadata['steps_executed'] ?? 'N/A') . "\n";
    echo "  - Tool calls: " . count($result->getToolCalls()) . "\n";
    echo "  - Plan changes: " . ($metadata['replans'] ?? 0) . "\n";
    
    // Show the execution plan
    if (isset($metadata['final_plan'])) {
        echo "\n📋 Executed Plan:\n";
        foreach ($metadata['final_plan'] as $i => $step) {
            $status = $step['completed'] ? '✅' : '❌';
            echo "  {$status} Step " . ($i + 1) . ": {$step['description']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n✅ PlanExecuteLoop example completed\n";
echo "\nKey takeaways:\n";
echo "  - PlanExecuteLoop creates upfront plan before execution\n";
echo "  - Systematic step-by-step execution with progress tracking\n";
echo "  - Supports replanning when steps fail\n";
echo "  - Ideal for workflows with clear dependencies\n";
echo "  - Higher token usage but more predictable execution\n";
