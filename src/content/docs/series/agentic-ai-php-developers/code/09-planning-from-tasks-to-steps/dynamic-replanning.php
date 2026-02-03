<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

/**
 * Dynamic Replanning
 * 
 * Demonstrates how PlanExecuteLoop adapts when steps fail:
 * - Detects failure indicators in step results
 * - Automatically triggers replanning
 * - Revises remaining steps based on progress
 * - Continues execution with new plan
 */

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a file reader tool that can fail
$fileReader = Tool::create('read_file')
    ->description('Read contents of a file')
    ->parameter('path', 'string', 'File path to read')
    ->required('path')
    ->handler(function (array $input): string {
        $path = $input['path'];
        
        // Simulate some files being unavailable
        if (str_contains($path, 'unavailable')) {
            return "ERROR: File not found or access denied: {$path}";
        }
        
        return "File contents of {$path}: [Sample data from file]";
    });

// Create API call tool that can fail
$apiCall = Tool::create('call_api')
    ->description('Make an API call to external service')
    ->parameter('endpoint', 'string', 'API endpoint to call')
    ->parameter('method', 'string', 'HTTP method (GET, POST, etc.)', required: false)
    ->required('endpoint')
    ->handler(function (array $input): string {
        $endpoint = $input['endpoint'];
        
        // Simulate API failures
        if (str_contains($endpoint, 'legacy-api')) {
            return "ERROR: API endpoint deprecated and no longer available";
        }
        
        return "API response from {$endpoint}: {success: true, data: [...]}";
    });

// Create loop with replanning enabled
$loop = new PlanExecuteLoop(allowReplan: true);

$replanCount = 0;

$loop->onPlanCreated(function ($steps, $context) use (&$replanCount) {
    if ($replanCount === 0) {
        echo "📋 Initial Plan Created\n\n";
    } else {
        echo "🔄 Plan Revised (Replan #{$replanCount})\n\n";
    }
    
    foreach ($steps as $i => $step) {
        echo "  " . ($i + 1) . ". {$step}\n";
    }
    echo "\n";
});

$loop->onStepComplete(function ($stepNumber, $description, $result) use (&$replanCount) {
    // Check if step failed
    if (str_contains(strtolower($result), 'error')) {
        echo "❌ Step {$stepNumber} FAILED\n";
        echo "   {$description}\n";
        echo "   → {$result}\n";
        echo "   🔄 Replanning required...\n\n";
        $replanCount++;
    } else {
        echo "✅ Step {$stepNumber} complete\n";
        echo "   {$description}\n\n";
    }
});

// Create agent
$agent = Agent::create($client)
    ->withTool($fileReader)
    ->withTool($apiCall)
    ->withLoopStrategy($loop)
    ->maxIterations(20);

// Run task that will require replanning
echo "=== Task: Data Migration with Failures ===\n\n";

$task = "Migrate customer data:\n" .
        "1. Read from legacy-api.unavailable.com\n" .
        "2. Transform the data\n" .
        "3. Write to new database\n" .
        "If any step fails, find an alternative approach.";

$result = $agent->run($task);

// Display result
echo "=== Final Result ===\n\n";
echo $result->getAnswer() . "\n\n";

echo "=== Metadata ===\n";
$metadata = $result->getMetadata();
echo "Replans: {$metadata['replan_count']}\n";
echo "Total iterations: " . $result->getIterations() . "\n";
