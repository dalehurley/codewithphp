<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Observability\ObservabilityLogger;
use ClaudeAgents\Observability\Tracer;
use ClaudeAgents\Observability\Metrics;
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

/**
 * Example 6: Comprehensive Observability
 *
 * Combines logging, tracing, and metrics for complete observability.
 * This demonstrates how to instrument agents with all three pillars.
 */

echo "=== Comprehensive Observability ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Setup: Initialize all observability components
echo "🚀 Initializing observability stack...\n\n";

// 1. Base logger
$baseLogger = LoggerFactory::createConsole(LogLevel::INFO);

// 2. Tracer
$tracer = new Tracer();

// 3. ObservabilityLogger with tracer
$logger = new ObservabilityLogger($baseLogger, $tracer);
$logger->setGlobalContext([
    'service' => 'agent-api',
    'environment' => 'production',
    'version' => '2.0.0',
]);

// 4. Metrics
$metrics = new Metrics();

echo "✅ Observability stack initialized\n";
echo "   - Logger: Console with trace correlation\n";
echo "   - Tracer: Distributed tracing enabled\n";
echo "   - Metrics: Collection active\n\n";

// Create observable tool
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($tracer, $logger, $metrics) {
        $span = $tracer->startSpan('tool:calculate', [
            'expression' => $input['expression'],
        ]);

        $logger->info('Tool execution started', [
            'tool' => 'calculate',
            'expression' => $input['expression'],
        ]);

        $startTime = microtime(true);

        try {
            $result = eval("return {$input['expression']};");
            $duration = (microtime(true) - $startTime) * 1000;

            $span->setAttribute('result', $result);
            $span->setStatus('OK');

            $metrics->recordRequest(
                success: true,
                tokensInput: 0,
                tokensOutput: 0,
                duration: $duration
            );

            $logger->info('Tool execution successful', [
                'tool' => 'calculate',
                'result' => $result,
                'duration_ms' => round($duration, 2),
            ]);

            return $result;
        } catch (\Throwable $e) {
            $span->setStatus('ERROR', $e->getMessage());

            $logger->logException($e, 'Tool execution failed', [
                'tool' => 'calculate',
            ]);

            throw $e;
        } finally {
            $tracer->endSpan($span);
        }
    });

// Create agent
$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

// Run queries with full observability
$queries = [
    'What is 123 * 456?',
    'Calculate 1000 - 735',
];

echo "📊 Running agent with full observability...\n\n";

foreach ($queries as $i => $query) {
    echo "[" . ($i + 1) . "/" . count($queries) . "] {$query}\n";

    // Start trace
    $traceId = $tracer->startTrace();
    $rootSpan = $tracer->startSpan('agent_execution', [
        'query' => $query,
    ]);

    $logger->info('Agent execution started', [
        'query' => $query,
    ]);

    $startTime = microtime(true);

    try {
        $result = $agent->run($query);
        $duration = (microtime(true) - $startTime) * 1000;

        // Get usage
        $usage = $result->getTokenUsage();
        $inputTokens = $usage['input'] ?? 0;
        $outputTokens = $usage['output'] ?? 0;

        // Record metrics
        $metrics->recordRequest(
            success: true,
            tokensInput: $inputTokens,
            tokensOutput: $outputTokens,
            duration: $duration
        );

        // Update span
        $rootSpan->setAttribute('answer_length', strlen($result->getAnswer()));
        $rootSpan->setAttribute('tool_calls', count($result->getToolCalls()));
        $rootSpan->setStatus('OK');

        $logger->info('Agent execution completed', [
            'answer' => substr($result->getAnswer(), 0, 100),
            'duration_ms' => round($duration, 2),
            'tokens' => ['input' => $inputTokens, 'output' => $outputTokens],
        ]);

        echo "  ✅ {$result->getAnswer()}\n\n";
    } catch (\Throwable $e) {
        $duration = (microtime(true) - $startTime) * 1000;

        $metrics->recordRequest(
            success: false,
            tokensInput: 0,
            tokensOutput: 0,
            duration: $duration,
            error: get_class($e) . ': ' . $e->getMessage()
        );

        $rootSpan->setStatus('ERROR', $e->getMessage());
        $logger->logException($e, 'Agent execution failed');

        echo "  ❌ Error: {$e->getMessage()}\n\n";
    } finally {
        $tracer->endSpan($rootSpan);
        $tracer->endTrace();
    }
}

// Display comprehensive summary
echo "\n" . str_repeat('=', 70) . "\n";
echo "📈 OBSERVABILITY DASHBOARD\n";
echo str_repeat('=', 70) . "\n\n";

// Metrics Summary
$summary = $metrics->getSummary();
echo "METRICS:\n";
echo "  Requests: {$summary['total_requests']} total, {$summary['successful_requests']} success, {$summary['failed_requests']} failed\n";
echo "  Success Rate: " . round($summary['success_rate'] * 100, 2) . "%\n";
echo "  Tokens: {$summary['total_tokens']['input']} in, {$summary['total_tokens']['output']} out, {$summary['total_tokens']['total']} total\n";
echo "  Latency: " . round($summary['average_duration_ms'], 2) . "ms avg, " . round($summary['total_duration_ms'], 2) . "ms total\n\n";

// Traces Summary
echo "TRACES:\n";
echo "  Total Spans: " . count($tracer->getSpans()) . "\n";
echo "  Total Duration: " . round($tracer->getTotalDuration(), 2) . "ms\n\n";

echo "  Recent Spans:\n";
foreach (array_slice($tracer->getSpans(), -8) as $span) {
    echo "    - {$span->getName()}: " . round($span->getDuration(), 2) . "ms [{$span->getStatus()}]\n";
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "✅ Comprehensive observability demonstration complete.\n";
echo str_repeat('=', 70) . "\n";

echo "\nIn production, this data would be sent to:\n";
echo "  - Logs: → Elasticsearch / Loki / CloudWatch\n";
echo "  - Traces: → Jaeger / Zipkin / Tempo\n";
echo "  - Metrics: → Prometheus / Grafana / Datadog\n";
