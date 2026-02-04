<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Observability\Metrics;
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

/**
 * Example 4: Metrics Collection
 *
 * Demonstrates collecting and reporting metrics:
 * - Request counts and success rates
 * - Token usage tracking
 * - Latency measurements
 * - Error categorization
 */

echo "=== Metrics Collection ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$metrics = new Metrics();
$logger = LoggerFactory::createConsole(LogLevel::INFO);

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
            $metrics->recordRequest(
                success: true,
                tokensInput: 0,
                tokensOutput: 0,
                duration: $duration
            );

            $logger->info('Tool execution successful', [
                'duration_ms' => round($duration, 2),
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $e) {
            // Record failed tool execution
            $duration = (microtime(true) - $startTime) * 1000;
            $metrics->recordRequest(
                success: false,
                tokensInput: 0,
                tokensOutput: 0,
                duration: $duration,
                error: 'EvaluationError: ' . $e->getMessage()
            );

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

echo "🚀 Running {count($queries)} queries to collect metrics...\n\n";

foreach ($queries as $i => $query) {
    echo "[" . ($i + 1) . "/" . count($queries) . "] {$query}\n";

    $startTime = microtime(true);

    try {
        $result = $agent->run($query);
        $duration = (microtime(true) - $startTime) * 1000;

        // Get usage from result if available
        $usage = $result->getTokenUsage();
        $inputTokens = $usage['input'] ?? 0;
        $outputTokens = $usage['output'] ?? 0;

        // Record agent request
        $metrics->recordRequest(
            success: true,
            tokensInput: $inputTokens,
            tokensOutput: $outputTokens,
            duration: $duration
        );

        echo "  ✅ {$result->getAnswer()} ({$inputTokens} in, {$outputTokens} out, " . round($duration, 2) . "ms)\n\n";
    } catch (\Throwable $e) {
        $duration = (microtime(true) - $startTime) * 1000;

        $metrics->recordRequest(
            success: false,
            tokensInput: 0,
            tokensOutput: 0,
            duration: $duration,
            error: get_class($e) . ': ' . $e->getMessage()
        );

        echo "  ❌ Error: {$e->getMessage()}\n\n";
    }
}

// Display metrics summary
echo "\n📊 Metrics Summary:\n";
echo str_repeat('─', 50) . "\n\n";

$summary = $metrics->getSummary();

echo "Requests:\n";
echo "  Total: {$summary['total_requests']}\n";
echo "  Successful: {$summary['successful_requests']}\n";
echo "  Failed: {$summary['failed_requests']}\n";
echo "  Success Rate: " . round($summary['success_rate'] * 100, 2) . "%\n\n";

echo "Tokens:\n";
echo "  Input: {$summary['total_tokens']['input']}\n";
echo "  Output: {$summary['total_tokens']['output']}\n";
echo "  Total: {$summary['total_tokens']['total']}\n\n";

echo "Latency:\n";
echo "  Total Duration: " . round($summary['total_duration_ms'], 2) . "ms\n";
echo "  Average Duration: " . round($summary['average_duration_ms'], 2) . "ms\n\n";

if (! empty($summary['error_counts'])) {
    echo "Errors by Type:\n";
    foreach ($summary['error_counts'] as $errorType => $count) {
        echo "  {$errorType}: {$count}\n";
    }
    echo "\n";
}

// Calculate additional metrics
$totalTokens = $summary['total_tokens']['total'];
$avgTokensPerRequest = $summary['total_requests'] > 0
    ? round($totalTokens / $summary['total_requests'], 2)
    : 0;

echo "Derived Metrics:\n";
echo "  Avg Tokens/Request: {$avgTokensPerRequest}\n";
echo "  Requests/Second: " . ($summary['total_duration_ms'] > 0
    ? round($summary['total_requests'] / ($summary['total_duration_ms'] / 1000), 2)
    : 0) . "\n";

echo "\n✅ Metrics collection demonstration complete.\n";
echo "These metrics would typically be exported to monitoring systems.\n";
