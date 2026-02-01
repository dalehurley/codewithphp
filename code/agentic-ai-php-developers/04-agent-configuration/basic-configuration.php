<?php

/**
 * Basic Agent Configuration
 * 
 * Demonstrates essential agent configuration options:
 * - Model selection
 * - Execution limits
 * - Temperature and creativity
 * - Token limits
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Create Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define a simple tool
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) {
        try {
            // Safe evaluation of simple math expressions
            $result = eval("return {$input['expression']};");
            return json_encode(['result' => $result]);
        } catch (\Throwable $e) {
            return json_encode(['error' => 'Invalid expression']);
        }
    });

// Example 1: Minimal Configuration (defaults)
echo "=== Example 1: Minimal Configuration ===\n";

$minimalAgent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

$result = $minimalAgent->run('What is 25 * 4?');
echo "Result: {$result->getAnswer()}\n\n";

// Example 2: Production Configuration
echo "=== Example 2: Production Configuration ===\n";

$productionAgent = Agent::create($client)
    ->model('claude-sonnet-4-5') // Explicit model
    ->temperature(0.1) // Low for consistency
    ->maxTokens(4096) // Reasonable limit
    ->maxIterations(10) // Prevent runaway loops
    ->timeout(120) // 2 minute timeout
    ->withTool($calculator)
    ->withSystemPrompt('You are a precise math assistant.')
    ->withLoopStrategy(new ReactLoop());

$result = $productionAgent->run('Calculate: (123 + 456) * 2 - 100');
echo "Result: {$result->getAnswer()}\n";
echo "Iterations: {$result->iterations()}\n";
echo "Tokens used: {$result->tokensUsed()}\n\n";

// Example 3: High-Creativity Configuration
echo "=== Example 3: High-Creativity Configuration ===\n";

$creativeAgent = Agent::create($client)
    ->model('claude-sonnet-4-5')
    ->temperature(0.9) // High creativity
    ->maxTokens(8192) // More space for creative output
    ->maxIterations(5)
    ->withSystemPrompt('You are a creative writing assistant.');

$result = $creativeAgent->run('Write a haiku about programming');
echo "Result: {$result->getAnswer()}\n\n";

// Example 4: Configuration Comparison
echo "=== Example 4: Configuration Comparison ===\n";

$configs = [
    'deterministic' => ['temperature' => 0.0, 'maxTokens' => 1024],
    'balanced' => ['temperature' => 0.5, 'maxTokens' => 4096],
    'creative' => ['temperature' => 0.9, 'maxTokens' => 8192],
];

foreach ($configs as $name => $config) {
    $agent = Agent::create($client)
        ->model('claude-sonnet-4-5')
        ->temperature($config['temperature'])
        ->maxTokens($config['maxTokens'])
        ->withSystemPrompt('You are a helpful assistant.');

    echo sprintf(
        "%s config (temp=%.1f, tokens=%d): Ready\n",
        ucfirst($name),
        $config['temperature'],
        $config['maxTokens']
    );
}

echo "\n✅ Agent configuration examples completed!\n";
