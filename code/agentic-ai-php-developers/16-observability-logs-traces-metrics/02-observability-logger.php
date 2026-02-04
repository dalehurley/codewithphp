<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Observability\ObservabilityLogger;
use ClaudeAgents\Observability\Tracer;
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

/**
 * Example 2: ObservabilityLogger with Trace Context
 *
 * Demonstrates using ObservabilityLogger which automatically
 * enriches logs with trace IDs, span IDs, and context.
 */

echo "=== ObservabilityLogger with Trace Context ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create base logger and tracer
$baseLogger = LoggerFactory::createConsole(LogLevel::INFO);
$tracer = new Tracer();

// Create observability logger with tracer integration
$logger = new ObservabilityLogger($baseLogger, $tracer);

// Set global context that will be added to all logs
$logger->setGlobalContext([
    'service' => 'agent-demo',
    'environment' => 'development',
    'version' => '1.0.0',
]);

echo "🔍 Starting traced execution...\n\n";

// Start a trace
$traceId = $tracer->startTrace();
$logger->info('Trace started', ['trace_operation' => 'agent_calculation']);

// Create a span for tool setup
$setupSpan = $tracer->startSpan('tool_setup', [
    'operation' => 'create_calculator_tool',
]);

$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($logger, $tracer) {
        // Create a span for tool execution
        $span = $tracer->startSpan('tool_execution', [
            'tool' => 'calculate',
            'expression' => $input['expression'],
        ]);

        $logger->info('Executing calculation', [
            'expression' => $input['expression'],
        ]);

        try {
            $result = eval("return {$input['expression']};");

            $span->setAttribute('result', $result);
            $span->setStatus('OK');
            $tracer->endSpan($span);

            $logger->info('Calculation successful', [
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            $span->setStatus('ERROR', $e->getMessage());
            $tracer->endSpan($span);

            $logger->logException($e, 'Calculation failed');

            throw $e;
        }
    });

$tracer->endSpan($setupSpan);
$logger->info('Tool setup complete');

// Create agent span
$agentSpan = $tracer->startSpan('agent_execution', [
    'operation' => 'run_agent',
    'prompt' => 'What is 42 * 13 + 99?',
]);

$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

try {
    $result = $agent->run('What is 42 * 13 + 99?');

    $agentSpan->setAttribute('answer', $result->getAnswer());
    $agentSpan->setAttribute('tool_calls', count($result->getToolCalls()));
    $agentSpan->setStatus('OK');

    $logger->info('Agent execution successful', [
        'answer' => $result->getAnswer(),
        'tool_calls' => count($result->getToolCalls()),
    ]);

    echo "\n📊 Result: {$result->getAnswer()}\n";
} catch (\Throwable $e) {
    $agentSpan->setStatus('ERROR', $e->getMessage());
    $logger->logException($e, 'Agent execution failed');

    echo "\n❌ Error: {$e->getMessage()}\n";
}

$tracer->endSpan($agentSpan);

// End the trace
$tracer->endTrace();
$logger->info('Trace completed');

// Show trace summary
echo "\n📈 Trace Summary:\n";
echo "Trace ID: {$traceId}\n";
echo "Total Duration: " . round($tracer->getTotalDuration(), 2) . "ms\n";
echo "Span Count: " . count($tracer->getSpans()) . "\n\n";

echo "Spans:\n";
foreach ($tracer->getSpans() as $span) {
    echo "  - {$span->getName()}: " . round($span->getDuration(), 2) . "ms [{$span->getStatus()}]\n";
}

echo "\n✅ ObservabilityLogger demonstration complete.\n";
echo "All logs were enriched with trace context automatically.\n";
