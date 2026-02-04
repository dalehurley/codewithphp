<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Observability\MetricsAggregator;
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

/**
 * Example 5: MetricsAggregator
 *
 * Demonstrates using MetricsAggregator for advanced metrics collection
 * with aggregation, percentiles, and time-series data.
 */

echo "=== MetricsAggregator ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$logger = LoggerFactory::createConsole(LogLevel::INFO);

// Create metrics aggregator
$metrics = new MetricsAggregator();

echo "✅ MetricsAggregator initialized\n\n";

// Create a tool that tracks metrics
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($metrics, $logger) {
        $startTime = microtime(true);

        try {
            $result = eval("return {$input['expression']};");

            // Record successful tool execution
            $duration = (microtime(true) - $startTime) * 1000;
            
            $metrics->recordLatency($duration);
            $metrics->recordSuccess();

            $logger->info('Tool execution successful', [
                'duration_ms' => round($duration, 2),
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            // Record failed tool execution
            $duration = (microtime(true) - $startTime) * 1000;
            
            $metrics->recordLatency($duration);
            $metrics->recordFailure($e->getMessage());

            $logger->error('Tool execution failed', [
                'duration_ms' => round($duration, 2),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    });

$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

// Run multiple queries to collect metrics
$queries = [
    'What is 25 * 17?',
    'Calculate 100 + 50 * 2',
    'What is 1234 + 5678?',
    'Compute 99 * 99',
    'What is 500 / 25?',
];

echo "🚀 Running " . count($queries) . " queries to collect metrics...\n\n";

foreach ($queries as $i => $query) {
    echo "[" . ($i + 1) . "/" . count($queries) . "] {$query}\n";

    $startTime = microtime(true);

    try {
        $result = $agent->run($query);
        $duration = (microtime(true) - $startTime) * 1000;

        // Get token usage
        $usage = $result->getTokenUsage();
        $inputTokens = $usage['input'] ?? 0;
        $outputTokens = $usage['output'] ?? 0;

        // Record agent metrics
        $metrics->recordLatency($duration);
        $metrics->recordTokens($inputTokens + $outputTokens);
        $metrics->recordSuccess();

        echo "  ✅ {$result->getAnswer()}\n";
        echo "     Tokens: {$inputTokens} in, {$outputTokens} out, Duration: " . round($duration, 2) . "ms\n\n";
    } catch (\Throwable $e) {
        $duration = (microtime(true) - $startTime) * 1000;

        $metrics->recordLatency($duration);
        $metrics->recordFailure($e->getMessage());

        echo "  ❌ Error: {$e->getMessage()}\n\n";
    }
}

// Display metrics summary
echo "\n📊 Metrics Summary:\n";
echo str_repeat('─', 50) . "\n\n";

$summary = $metrics->getSummary();

echo "Requests:\n";
echo "  Total: {$summary['total_requests']}\n";
echo "  Successful: {$summary['success_count']}\n";
echo "  Failed: {$summary['failure_count']}\n";
echo "  Success Rate: " . round($summary['success_rate'] * 100, 2) . "%\n\n";

echo "Latency Statistics:\n";
$latency = $summary['latency_stats'] ?? [];
if (! empty($latency)) {
    echo "  Min: " . round($latency['min'] ?? 0, 2) . "ms\n";
    echo "  Max: " . round($latency['max'] ?? 0, 2) . "ms\n";
    echo "  Average: " . round($latency['avg'] ?? 0, 2) . "ms\n";
    if (isset($latency['p50'])) {
        echo "  p50: " . round($latency['p50'], 2) . "ms\n";
    }
    if (isset($latency['p95'])) {
        echo "  p95: " . round($latency['p95'], 2) . "ms\n";
    }
    if (isset($latency['p99'])) {
        echo "  p99: " . round($latency['p99'], 2) . "ms\n";
    }
}
echo "\n";

$tokenStats = $summary['token_stats'] ?? [];
if (! empty($tokenStats)) {
    echo "Token Statistics:\n";
    echo "  Total: " . ($tokenStats['total'] ?? 0) . "\n";
    echo "  Average: " . round($tokenStats['avg'] ?? 0, 2) . " per request\n\n";
}

if (! empty($summary['errors'])) {
    echo "Errors:\n";
    foreach ($summary['errors'] as $error => $count) {
        echo "  {$error}: {$count}\n";
    }
    echo "\n";
}

echo "✅ MetricsAggregator demonstration complete.\n";
echo "This provides advanced metrics with percentiles and aggregation.\n";
