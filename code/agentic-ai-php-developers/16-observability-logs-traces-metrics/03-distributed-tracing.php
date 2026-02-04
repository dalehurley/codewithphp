<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Observability\Tracer;
use ClaudeAgents\Observability\Span;

/**
 * Example 3: Distributed Tracing with Spans
 *
 * Demonstrates hierarchical span relationships for detailed
 * performance profiling and distributed tracing.
 */

echo "=== Distributed Tracing with Spans ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$tracer = new Tracer();

// Start a trace
$traceId = $tracer->startTrace();
echo "🔍 Started trace: {$traceId}\n\n";

// Root span for the entire operation
$rootSpan = $tracer->startSpan('multi_agent_workflow', [
    'workflow' => 'math_and_analysis',
]);

// Span for first agent (calculator)
$calculatorSpan = $tracer->startSpan('calculator_agent', [
    'agent_type' => 'calculator',
], $rootSpan);

$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($tracer, $calculatorSpan) {
        // Create a child span for the tool execution
        $toolSpan = $tracer->startSpan('calculate_tool', [
            'expression' => $input['expression'],
        ], $calculatorSpan);

        $toolSpan->addEvent('calculation_started');

        try {
            $result = eval("return {$input['expression']};");

            $toolSpan->addEvent('calculation_completed', [
                'result' => $result,
            ]);
            $toolSpan->setAttribute('result', $result);
            $toolSpan->setStatus('OK');

            return $result;
        } catch (\Throwable $e) {
            $toolSpan->addEvent('calculation_failed', [
                'error' => $e->getMessage(),
            ]);
            $toolSpan->setStatus('ERROR', $e->getMessage());

            throw $e;
        } finally {
            $tracer->endSpan($toolSpan);
        }
    });

$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

echo "🔢 Running calculator agent...\n";

try {
    $result = $agent->run('Calculate 156 * 23 + 500');

    $calculatorSpan->setAttribute('answer', $result->getAnswer());
    $calculatorSpan->setAttribute('tool_calls', count($result->getToolCalls()));
    $calculatorSpan->setStatus('OK');

    echo "Result: {$result->getAnswer()}\n\n";
} catch (\Throwable $e) {
    $calculatorSpan->setStatus('ERROR', $e->getMessage());
    echo "Error: {$e->getMessage()}\n\n";
}

$tracer->endSpan($calculatorSpan);

// Span for analysis
$analysisSpan = $tracer->startSpan('analysis_agent', [
    'agent_type' => 'analyzer',
], $rootSpan);

$analysisAgent = Agent::create($client)
    ->withSystemPrompt('You are an analytical assistant. Provide brief insights.');

echo "📊 Running analysis agent...\n";

try {
    $analysisResult = $analysisAgent->run(
        'Analyze this number: ' . $result->getAnswer() . '. Is it divisible by 2? By 3?'
    );

    $analysisSpan->setAttribute('answer', $analysisResult->getAnswer());
    $analysisSpan->setStatus('OK');

    echo "Analysis: {$analysisResult->getAnswer()}\n\n";
} catch (\Throwable $e) {
    $analysisSpan->setStatus('ERROR', $e->getMessage());
    echo "Error: {$e->getMessage()}\n\n";
}

$tracer->endSpan($analysisSpan);

// End root span and trace
$rootSpan->setStatus('OK');
$tracer->endSpan($rootSpan);
$tracer->endTrace();

// Display trace tree
echo "\n📈 Trace Summary:\n";
echo "Trace ID: {$traceId}\n";
echo "Total Duration: " . round($tracer->getTotalDuration(), 2) . "ms\n\n";

echo "Span Hierarchy:\n";
displaySpanTree($tracer->buildSpanTree());

// Export to OpenTelemetry format
echo "\n📦 OpenTelemetry Export:\n";
$otelData = $tracer->toOpenTelemetry();
echo json_encode($otelData, JSON_PRETTY_PRINT) . "\n";

echo "\n✅ Distributed tracing demonstration complete.\n";

/**
 * Display span tree recursively.
 */
function displaySpanTree(array $tree, int $depth = 0): void
{
    foreach ($tree as $node) {
        $indent = str_repeat('  ', $depth);
        $span = $node['span'];
        $duration = round($span->getDuration(), 2);
        $status = $span->getStatus();

        echo "{$indent}├─ {$span->getName()}: {$duration}ms [{$status}]\n";

        if (! empty($node['children'])) {
            displaySpanTree($node['children'], $depth + 1);
        }
    }
}
