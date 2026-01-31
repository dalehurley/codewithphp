<?php

/**
 * StreamingLoop Example - Real-Time Progress Updates
 * 
 * Demonstrates streaming execution where the agent:
 * 1. Emits progress updates in real-time
 * 2. Shows tool calls as they happen
 * 3. Provides incremental results
 * 4. Improves perceived performance
 * 
 * Use case: Long-running tasks, user-facing applications, progress feedback
 */

declare(strict_types=1);

require_once __DIR__ . '/../../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\StreamingLoop;
use ClaudeAgents\Progress\AgentUpdate;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define slow data processing tools
$fetchLargeDataset = Tool::create('fetch_large_dataset')
    ->description('Retrieve large dataset from data warehouse')
    ->parameter('dataset_id', 'string', 'Dataset identifier')
    ->parameter('date_range', 'string', 'Date range to query')
    ->required(['dataset_id', 'date_range'])
    ->handler(function (array $input): string {
        echo "    ⏳ Fetching dataset {$input['dataset_id']}...\n";
        sleep(2); // Simulate slow data fetch
        
        return json_encode([
            'dataset_id' => $input['dataset_id'],
            'date_range' => $input['date_range'],
            'records_fetched' => rand(10000, 100000),
            'size_mb' => rand(50, 500),
            'status' => 'complete'
        ]);
    });

$processDataset = Tool::create('process_dataset')
    ->description('Clean, transform, and aggregate raw data')
    ->parameter('data', 'string', 'Raw dataset in JSON format')
    ->parameter('operations', 'string', 'Processing operations to apply')
    ->required(['data', 'operations'])
    ->handler(function (array $input): string {
        echo "    ⚙️  Processing data with operations: {$input['operations']}...\n";
        sleep(3); // Simulate data processing
        
        return json_encode([
            'records_processed' => rand(8000, 95000),
            'records_filtered' => rand(1000, 5000),
            'aggregations' => [
                'total_revenue' => rand(500000, 2000000),
                'avg_transaction' => rand(50, 300),
                'unique_customers' => rand(1000, 5000)
            ],
            'status' => 'processed'
        ]);
    });

$generateVisualization = Tool::create('generate_visualization')
    ->description('Create charts and graphs from processed data')
    ->parameter('data', 'string', 'Processed data in JSON format')
    ->parameter('chart_types', 'string', 'Types of charts to generate')
    ->required(['data', 'chart_types'])
    ->handler(function (array $input): string {
        echo "    📊 Generating visualizations: {$input['chart_types']}...\n";
        sleep(2); // Simulate chart generation
        
        return json_encode([
            'charts_generated' => explode(',', $input['chart_types']),
            'format' => 'png',
            'total_charts' => substr_count($input['chart_types'], ',') + 1,
            'status' => 'complete'
        ]);
    });

$exportReport = Tool::create('export_report')
    ->description('Export final report with data and visualizations')
    ->parameter('data', 'string', 'Report data and metadata')
    ->parameter('format', 'string', 'Export format (pdf, html, excel)')
    ->required(['data', 'format'])
    ->handler(function (array $input): string {
        echo "    📄 Exporting report as {$input['format']}...\n";
        sleep(2); // Simulate export
        
        return json_encode([
            'format' => $input['format'],
            'filename' => 'analytics_report_' . time() . '.' . $input['format'],
            'size_mb' => rand(5, 50),
            'pages' => rand(10, 50),
            'status' => 'exported'
        ]);
    });

// Progress tracking
$progressSteps = [];
$currentStep = 0;

// Create agent with StreamingLoop
$agent = Agent::create($client)
    ->withSystemPrompt(
        'You are a data analytics assistant that processes large datasets and generates comprehensive reports. ' .
        'Keep users informed of your progress at each step.'
    )
    ->withTools([$fetchLargeDataset, $processDataset, $generateVisualization, $exportReport])
    ->withLoopStrategy(new StreamingLoop(
        updateInterval: 500,              // Update every 500ms
        includeIntermediateSteps: true,   // Show all intermediate steps
        verboseProgress: true              // Detailed progress messages
    ))
    ->maxIterations(10)
    ->onUpdate(function (AgentUpdate $update) use (&$progressSteps, &$currentStep) {
        $timestamp = date('H:i:s');
        $type = $update->getType();
        $data = $update->getData();
        $message = $data['message'] ?? $data['text'] ?? $type;
        
        // Format different update types with visual indicators
        switch ($type) {
            case 'thinking':
                echo "[{$timestamp}] 🤔 Thinking: {$message}\n";
                break;
                
            case 'tool_call':
                $currentStep++;
                $toolName = $data['tool'] ?? 'unknown';
                echo "\n[{$timestamp}] 🔧 Step {$currentStep}: Calling tool '{$toolName}'\n";
                $progressSteps[] = ['step' => $currentStep, 'tool' => $toolName, 'status' => 'started'];
                break;
                
            case 'tool_result':
                echo "[{$timestamp}] ✅ Tool completed successfully\n";
                if (!empty($progressSteps)) {
                    $progressSteps[count($progressSteps) - 1]['status'] = 'complete';
                }
                break;
                
            case 'progress':
                $progress = $data['progress'] ?? 0;
                $total = $data['total'] ?? 100;
                $percent = round(($progress / $total) * 100);
                echo "[{$timestamp}] 📈 Progress: {$percent}% ({$progress}/{$total})\n";
                break;
                
            case 'error':
                echo "[{$timestamp}] ❌ Error: {$message}\n";
                break;
                
            default:
                echo "[{$timestamp}] ℹ️  {$message}\n";
        }
        
        // Show progress bar for visual feedback
        if ($type === 'progress') {
            $progress = $data['progress'] ?? 0;
            $total = $data['total'] ?? 100;
            $percent = round(($progress / $total) * 100);
            $barLength = 40;
            $filled = (int) round(($progress / $total) * $barLength);
            $bar = str_repeat('█', $filled) . str_repeat('░', $barLength - $filled);
            echo "           [{$bar}] {$percent}%\n";
        }
    });

// Long-running analytics task
$task = "Perform a comprehensive sales analysis:\n" .
        "1. Fetch the 'quarterly_sales' dataset for Q4 2024\n" .
        "2. Process the data: clean, filter outliers, and calculate key metrics\n" .
        "3. Generate visualizations: revenue trends, top products, regional performance\n" .
        "4. Export a comprehensive PDF report with all findings\n\n" .
        "This is a large dataset, so provide progress updates.";

echo str_repeat('=', 80) . "\n";
echo "Long-Running Task with Streaming Updates\n";
echo str_repeat('=', 80) . "\n\n";
echo "Task: {$task}\n\n";
echo str_repeat('-', 80) . "\n";
echo "REAL-TIME PROGRESS STREAM\n";
echo str_repeat('-', 80) . "\n\n";

try {
    $startTime = microtime(true);
    
    echo "[" . date('H:i:s') . "] 🚀 Starting agent execution...\n\n";
    
    $result = $agent->run($task);
    
    $duration = round((microtime(true) - $startTime) * 1000);
    
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "📊 Final Result:\n";
    echo str_repeat('=', 80) . "\n";
    echo $result->getAnswer() . "\n";
    echo str_repeat('=', 80) . "\n";
    
    // Show execution summary
    $metadata = $result->getMetadata();
    echo "\n📈 Execution Summary:\n";
    echo "  - Total duration: {$duration}ms (" . round($duration / 1000, 1) . "s)\n";
    echo "  - Steps completed: " . count($progressSteps) . "\n";
    echo "  - Tool calls: " . count($result->getToolCalls()) . "\n";
    echo "  - Updates emitted: " . ($metadata['updates_emitted'] ?? $updateCount) . "\n";
    
    // Show step-by-step breakdown
    echo "\n📋 Step-by-Step Breakdown:\n";
    foreach ($progressSteps as $step) {
        $status = $step['status'] === 'complete' ? '✅' : '⏸️';
        echo "  {$status} Step {$step['step']}: {$step['tool']}\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n✅ StreamingLoop example completed\n";
echo "\nKey takeaways:\n";
echo "  - StreamingLoop provides real-time progress feedback\n";
echo "  - Improves perceived performance (users see progress)\n";
echo "  - Ideal for long-running tasks (>10 seconds)\n";
echo "  - Same token usage as underlying loop strategy\n";
echo "  - Requires callback infrastructure for production (WebSockets, SSE)\n";
echo "  - Can wrap any loop strategy (React, PlanExecute, Reflection)\n";
