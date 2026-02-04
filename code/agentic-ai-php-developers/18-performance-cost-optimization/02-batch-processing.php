<?php

declare(strict_types=1);

/**
 * Example 02: Batch Processing
 *
 * Demonstrates efficient batch processing of multiple tasks using AMPHP
 * for concurrent execution, reducing total processing time.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Async\BatchProcessor;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a data analysis tool
$analyzeTool = Tool::create('analyze_data')
    ->description('Analyze a dataset and return insights')
    ->parameter('dataset', 'string', 'Name of the dataset')
    ->parameter('metric', 'string', 'Metric to analyze')
    ->required('dataset', 'metric')
    ->handler(function (array $input): string {
        // Simulate data analysis
        $values = [rand(50, 150), rand(60, 140), rand(55, 145)];
        $avg = array_sum($values) / count($values);
        
        return "Dataset: {$input['dataset']}\n" .
               "Metric: {$input['metric']}\n" .
               "Average: " . number_format($avg, 2) . "\n" .
               "Range: " . min($values) . " - " . max($values);
    });

// Create agent for analysis tasks
$agent = Agent::create($client)
    ->withTool($analyzeTool)
    ->withSystemPrompt('You are a data analyst. Analyze the requested data and provide clear insights.')
    ->withModel('claude-3-5-sonnet-20241022')
    ->maxIterations(3);

echo "=== Batch Processing Demo ===\n\n";

// Define multiple analysis tasks
$tasks = [
    'sales_q1' => 'Analyze sales dataset for Q1 revenue trends',
    'sales_q2' => 'Analyze sales dataset for Q2 revenue trends',
    'users_monthly' => 'Analyze user engagement metrics for monthly active users',
    'conversions' => 'Analyze conversion rates from the marketing dataset',
    'retention' => 'Analyze customer retention metrics from the CRM dataset',
];

echo "Tasks to process: " . count($tasks) . "\n\n";

// Sequential processing (baseline)
echo "--- Sequential Processing (Baseline) ---\n";
$sequentialStart = microtime(true);
$sequentialResults = [];
$sequentialTokens = 0;

foreach ($tasks as $id => $description) {
    echo "Processing: {$id}...\n";
    $result = $agent->run($description);
    $sequentialResults[$id] = $result;
    
    $usage = $result->getTokenUsage();
    $sequentialTokens += $usage['input'] + $usage['output'];
}

$sequentialDuration = microtime(true) - $sequentialStart;

echo "\nSequential Results:\n";
echo "Duration: " . number_format($sequentialDuration, 2) . "s\n";
echo "Total tokens: {$sequentialTokens}\n";
echo "Avg per task: " . number_format($sequentialDuration / count($tasks), 2) . "s\n";

// Batch processing with concurrency
echo "\n--- Batch Processing (Concurrent) ---\n";
$batchStart = microtime(true);

$processor = BatchProcessor::create($agent)
    ->addMany($tasks);

$batchResults = $processor->run(concurrency: 3);

$batchDuration = microtime(true) - $batchStart;
$stats = $processor->getStats();

echo "\nBatch Results:\n";
echo "Duration: " . number_format($batchDuration, 2) . "s\n";
echo "Total tokens: {$stats['total_tokens']['total']}\n";
echo "Success rate: " . number_format($stats['success_rate'] * 100, 1) . "%\n";
echo "Successful: {$stats['successful']}/{$stats['total_tasks']}\n";

// Compare performance
echo "\n=== Performance Comparison ===\n";
$speedup = $sequentialDuration / $batchDuration;
$timeSaved = $sequentialDuration - $batchDuration;

echo "Sequential: " . number_format($sequentialDuration, 2) . "s\n";
echo "Batch (3x): " . number_format($batchDuration, 2) . "s\n";
echo "Speedup: " . number_format($speedup, 2) . "x faster\n";
echo "Time saved: " . number_format($timeSaved, 2) . "s (" . 
     number_format(($timeSaved / $sequentialDuration) * 100, 1) . "%)\n";

// Show sample results
echo "\n=== Sample Results ===\n";
$sampleTask = array_key_first($batchResults);
if (isset($batchResults[$sampleTask])) {
    echo "Task: {$sampleTask}\n";
    echo "Answer: " . substr($batchResults[$sampleTask]->getAnswer(), 0, 150) . "...\n";
}

echo "\n✅ Batch processing with concurrency significantly reduces total processing time!\n";
echo "💡 Ideal for: bulk operations, report generation, data processing pipelines\n";
