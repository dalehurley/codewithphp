<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudeAgents\Agents\PlanExecuteAgent;
use ClaudePhp\ClaudePhp;

/**
 * ML-Optimized Planning
 * 
 * Demonstrates ML-enhanced planning that learns over time:
 * - Learns optimal plan detail level (high/medium/low)
 * - Learns ideal step count per task type
 * - Learns when replanning helps vs wastes tokens
 * - Improves efficiency by 15-25% over time
 */

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create ML-optimized agent
$agent = new PlanExecuteAgent($client, [
    'enable_ml_optimization' => true,
    'ml_history_path' => __DIR__ . '/plan_history.json',
    'allow_replan' => true,
]);

echo "=== ML-Optimized Planning Demo ===\n\n";

// Run multiple similar tasks
$tasks = [
    "Research and compare 3 PHP frameworks for REST APIs",
    "Research and compare 3 JavaScript frameworks for web apps",
    "Research and compare 3 Python frameworks for data science",
];

foreach ($tasks as $i => $task) {
    echo "Task " . ($i + 1) . ": {$task}\n";
    echo str_repeat('-', 60) . "\n";
    
    $result = $agent->run($task);
    
    $metadata = $result->getMetadata();
    
    echo "Plan steps: {$metadata['plan_steps']}\n";
    echo "Detail level: {$metadata['detail_level']}\n";
    echo "ML enabled: " . ($metadata['ml_enabled'] ? 'Yes' : 'No') . "\n";
    echo "Iterations: " . $result->getIterations() . "\n\n";
}

echo "=== ML Learning Summary ===\n\n";
echo "The agent learns:\n";
echo "• Optimal plan detail level (high/medium/low) per task type\n";
echo "• Ideal number of steps for different task complexities\n";
echo "• When replanning is beneficial vs. wasteful\n";
echo "• Cost/quality trade-offs for planning granularity\n\n";

echo "Over time, the agent becomes 15-25% more efficient by:\n";
echo "✓ Avoiding overly detailed plans for simple tasks\n";
echo "✓ Adding more detail for complex tasks that benefit from it\n";
echo "✓ Reducing unnecessary replanning attempts\n";
echo "✓ Optimizing token usage while maintaining quality\n";
