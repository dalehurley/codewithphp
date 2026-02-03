<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudeAgents\Agents\PlanExecuteAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

/**
 * Planning with Tools
 * 
 * Demonstrates how plans integrate with tool execution:
 * - Rich tool set for different operations
 * - Tools used during step execution
 * - Tool results influence next steps
 * - Complete workflows with multiple tools
 */

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a rich tool set
$tools = [
    // Database query tool
    Tool::create('query_database')
        ->description('Query the database for information')
        ->parameter('query', 'string', 'SQL-like query description')
        ->required('query')
        ->handler(fn ($input) => "Query results: [Sample data for: {$input['query']}]"),
    
    // Email sending tool
    Tool::create('send_email')
        ->description('Send an email notification')
        ->parameter('to', 'string', 'Recipient email')
        ->parameter('subject', 'string', 'Email subject')
        ->parameter('body', 'string', 'Email body')
        ->required('to', 'subject', 'body')
        ->handler(fn ($input) => "Email sent to {$input['to']}: {$input['subject']}"),
    
    // Report generation tool
    Tool::create('generate_report')
        ->description('Generate a formatted report')
        ->parameter('data', 'string', 'Data to include in report')
        ->parameter('format', 'string', 'Report format (PDF, HTML, etc.)')
        ->required('data', 'format')
        ->handler(fn ($input) => "Report generated in {$input['format']} format"),
    
    // File storage tool
    Tool::create('store_file')
        ->description('Store a file in the file system')
        ->parameter('filename', 'string', 'Name of file to store')
        ->parameter('content', 'string', 'File content')
        ->required('filename', 'content')
        ->handler(fn ($input) => "File stored: {$input['filename']}"),
];

// Create PlanExecuteAgent with tools
$agent = new PlanExecuteAgent($client, [
    'tools' => $tools,
    'allow_replan' => true,
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
]);

echo "=== Task: Weekly Sales Report Automation ===\n\n";

$task = "Generate and distribute the weekly sales report:\n" .
        "1. Query last 7 days of sales data\n" .
        "2. Calculate key metrics (total revenue, top products, growth %)\n" .
        "3. Generate a PDF report\n" .
        "4. Email the report to sales@company.com\n" .
        "5. Store a copy in the reports archive";

$result = $agent->run($task);

echo "=== Result ===\n\n";
echo $result->getAnswer() . "\n\n";

echo "=== Execution Details ===\n";
$metadata = $result->getMetadata();

echo "Steps executed: " . count($metadata['step_results']) . "\n";
echo "Tools used:\n";

foreach ($metadata['step_results'] as $step) {
    // Extract tool usage from results
    if (str_contains($step['result'], 'Query results') || 
        str_contains($step['result'], 'Email sent') || 
        str_contains($step['result'], 'Report generated') ||
        str_contains($step['result'], 'File stored')) {
        echo "  • Step {$step['step']}: Used tools\n";
    }
}

echo "\nToken usage: " . json_encode($result->getTokenUsage()) . "\n";
