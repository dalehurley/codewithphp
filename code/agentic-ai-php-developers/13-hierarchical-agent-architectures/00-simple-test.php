#!/usr/bin/env php
<?php
/**
 * Simple Hierarchical Agent Test
 * 
 * Minimal example to verify the system works.
 * Use this to test your API key and setup.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\HierarchicalAgent;
use ClaudeAgents\Agents\WorkerAgent;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "❌ Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "Simple Hierarchical Agent Test\n";
echo str_repeat("=", 50) . "\n\n";

// Create two simple workers
$mathWorker = new WorkerAgent($client, [
    'name' => 'math',
    'specialty' => 'mathematics and calculations',
]);

$writerWorker = new WorkerAgent($client, [
    'name' => 'writer',  
    'specialty' => 'clear explanations',
]);

// Create master and register workers
$master = new HierarchicalAgent($client);
$master->registerWorker('math', $mathWorker);
$master->registerWorker('writer', $writerWorker);

echo "Workers registered: " . implode(', ', $master->getWorkerNames()) . "\n\n";

// Simple task
$task = 'Calculate 10 + 15 and explain the result';

echo "Task: {$task}\n";
echo "Processing...\n\n";

$result = $master->run($task);

if ($result->isSuccess()) {
    echo "✅ SUCCESS!\n\n";
    echo $result->getAnswer() . "\n\n";
    
    $metadata = $result->getMetadata();
    $usage = $result->getTokenUsage();
    
    echo "Workers used: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "Total tokens: {$usage['total']}\n";
} else {
    echo "❌ FAILED: {$result->getError()}\n";
    echo "\nNote: If this fails with 'Failed to decompose', try again in a moment.\n";
    echo "The API may need a brief pause between requests.\n";
    exit(1);
}

echo "\nTest completed successfully!\n";
