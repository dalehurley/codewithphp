<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudePhp\ClaudePhp;

/**
 * Basic Plan-Execute Workflow
 * 
 * Demonstrates the fundamental plan-execute pattern:
 * 1. Agent creates a plan with multiple steps
 * 2. Executes each step systematically
 * 3. Synthesizes results into final answer
 */

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create Plan-Execute loop
$loop = new PlanExecuteLoop(allowReplan: true);

// Track the planning phase
$loop->onPlanCreated(function ($steps, $context) {
    echo "=== Plan Created ===\n\n";
    echo "Total steps: " . count($steps) . "\n\n";
    
    foreach ($steps as $i => $step) {
        echo "  " . ($i + 1) . ". {$step}\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
});

// Track each step completion
$loop->onStepComplete(function ($stepNumber, $description, $result) {
    echo "✅ Step {$stepNumber} complete\n";
    echo "   {$description}\n";
    echo "   → " . substr($result, 0, 100) . "...\n\n";
});

// Create agent with plan-execute loop
$agent = Agent::create($client)
    ->withName('planning-agent')
    ->withLoopStrategy($loop)
    ->maxIterations(15);

// Run a multi-step task
echo "=== Task: Research and Compare PHP Frameworks ===\n\n";

$task = "Research Laravel, Symfony, and Slim frameworks. Compare their features, performance, " .
        "and use cases. Recommend which one to use for a REST API project.";

$result = $agent->run($task);

// Display result
echo "=== Final Answer ===\n\n";
echo $result->getAnswer() . "\n\n";

echo "=== Metadata ===\n";
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Iterations: " . $result->getIterations() . "\n";
echo "Tokens: " . json_encode($result->getTokenUsage()) . "\n";

$metadata = $result->getMetadata();
if (isset($metadata['plan_steps'])) {
    echo "Plan steps: {$metadata['plan_steps']}\n";
}
if (isset($metadata['replan_count'])) {
    echo "Replans: {$metadata['replan_count']}\n";
}
