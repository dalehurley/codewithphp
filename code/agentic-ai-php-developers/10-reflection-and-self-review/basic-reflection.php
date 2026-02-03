<?php

/**
 * Basic Reflection Loop Example
 * 
 * Demonstrates the fundamental Generate-Reflect-Refine cycle using ReflectionLoop.
 * The agent creates an initial output, evaluates its quality, and iteratively
 * improves it until a quality threshold is met.
 * 
 * From: Agentic AI for PHP Developers
 * Chapter 10: Reflection and Self-Review Loops
 * 
 * @link https://github.com/claude-php/claude-php-agent
 */

declare(strict_types=1);

// Adjust path to your vendor/autoload.php location
require __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "Basic Reflection Loop Example\n";
echo str_repeat("=", 80) . "\n\n";

// Create reflection loop with clear parameters
$loop = new ReflectionLoop(
    maxRefinements: 3,        // Allow up to 3 refinement iterations
    qualityThreshold: 8,      // Stop when quality score reaches 8/10
    criteria: 'clarity, accuracy, and completeness'
);

// Add callback to monitor reflection progress
$loop->onReflection(function (int $refinement, int $score, string $feedback): void {
    echo "📊 Refinement #{$refinement}: Quality Score {$score}/10\n";
    echo "   Feedback: " . substr($feedback, 0, 200) . "...\n\n";
});

// Create agent with reflection loop
$agent = Agent::create($client)
    ->withLoopStrategy($loop)
    ->withSystemPrompt('You are a helpful assistant that creates high-quality, clear explanations for developers.')
    ->maxIterations(15);  // Allow enough iterations for generation + refinements

// Task: Explain a technical concept
$task = 'Explain the concept of dependency injection in PHP to a junior developer. ' .
        'Include a simple code example and explain the benefits.';

echo "Task: {$task}\n\n";
echo "Processing with reflection loop...\n";
echo str_repeat("-", 80) . "\n\n";

$startTime = microtime(true);
$result = $agent->run($task);
$duration = microtime(true) - $startTime;

// Display final output
echo "\n" . str_repeat("=", 80) . "\n";
echo "FINAL OUTPUT (After Reflection)\n";
echo str_repeat("=", 80) . "\n";
echo $result->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

// Display quality metrics
$metadata = $result->getMetadata();
echo "\n📈 Quality Metrics:\n";
echo "   - Final Score: {$metadata['final_score']}/10\n";
echo "   - Total Refinements: " . count($metadata['reflections']) . "\n";
echo "   - Total Iterations: {$result->getIterations()}\n";
echo "   - Duration: " . round($duration, 2) . "s\n";
echo "   - Tokens Used: " . json_encode($result->getTokenUsage()) . "\n";

// Show refinement history
if (isset($metadata['reflections'])) {
    echo "\n📝 Refinement History:\n";
    foreach ($metadata['reflections'] as $reflection) {
        echo "   Iteration {$reflection['iteration']}: {$reflection['score']}/10\n";
    }
}

echo "\n✅ Reflection cycle complete!\n";
