<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

/**
 * Example 1: Basic Structured Logging
 *
 * Demonstrates PSR-3 logging integration with agents.
 * Every agent operation is logged with context and metadata.
 */

echo "=== Basic Structured Logging ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a console logger
$logger = LoggerFactory::createConsole(LogLevel::DEBUG);

// Create a tool that we'll monitor
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($logger) {
        $logger->info('Tool execution started', [
            'tool' => 'calculate',
            'expression' => $input['expression'],
        ]);

        try {
            $result = eval("return {$input['expression']};");

            $logger->info('Tool execution successful', [
                'tool' => 'calculate',
                'expression' => $input['expression'],
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            $logger->error('Tool execution failed', [
                'tool' => 'calculate',
                'expression' => $input['expression'],
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    });

// Create an agent with logging
$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

// Log agent creation
$logger->info('Agent created', [
    'tools' => ['calculate'],
    'system_prompt' => 'You are a helpful math assistant.',
]);

// Run the agent
$logger->info('Starting agent execution', [
    'prompt' => 'What is 25 * 17 + 100?',
]);

$startTime = microtime(true);

try {
    $result = $agent->run('What is 25 * 17 + 100?');

    $duration = (microtime(true) - $startTime) * 1000;

    $logger->info('Agent execution completed', [
        'answer' => $result->getAnswer(),
        'duration_ms' => round($duration, 2),
        'tool_calls' => count($result->getToolCalls()),
    ]);

    echo "\n📊 Result: {$result->getAnswer()}\n";
    echo "⏱️  Duration: " . round($duration, 2) . "ms\n";
} catch (\Throwable $e) {
    $duration = (microtime(true) - $startTime) * 1000;

    $logger->error('Agent execution failed', [
        'error' => $e->getMessage(),
        'duration_ms' => round($duration, 2),
    ]);

    echo "\n❌ Error: {$e->getMessage()}\n";
}

echo "\n✅ Structured logging demonstration complete.\n";
echo "All operations were logged with context and metadata.\n";
