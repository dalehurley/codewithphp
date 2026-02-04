<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use ClaudeAgents\Support\LoggerFactory;
use Psr\Log\LogLevel;

/**
 * Example 5: OpenTelemetry Integration (Simulated)
 *
 * Demonstrates OpenTelemetry-style metrics collection with counters,
 * gauges, and histograms. In production, use the OpenTelemetry SDK.
 *
 * Note: This is a simplified demonstration. For production, install:
 * composer require open-telemetry/sdk open-telemetry/exporter-otlp
 */

echo "=== OpenTelemetry Integration (Simulated) ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$logger = LoggerFactory::createConsole(LogLevel::INFO);

// Simulated telemetry collector
class SimpleTelemetry
{
    private array $counters = [];
    private array $gauges = [];
    private array $histograms = [];

    public function recordCounter(string $name, int $value = 1, array $attributes = []): void
    {
        $key = $this->buildKey($name, $attributes);
        $this->counters[$key] = ($this->counters[$key] ?? 0) + $value;
    }

    public function recordGauge(string $name, float $value, array $attributes = []): void
    {
        $key = $this->buildKey($name, $attributes);
        $this->gauges[$key] = $value;
    }

    public function recordHistogram(string $name, float $value, array $attributes = []): void
    {
        $key = $this->buildKey($name, $attributes);
        if (! isset($this->histograms[$key])) {
            $this->histograms[$key] = [];
        }
        $this->histograms[$key][] = $value;
    }

    public function getAllMetrics(): array
    {
        return [
            'counters' => $this->counters,
            'gauges' => $this->gauges,
            'histograms' => array_map(function ($values) {
                return [
                    'count' => count($values),
                    'min' => min($values),
                    'max' => max($values),
                    'avg' => array_sum($values) / count($values),
                    'sum' => array_sum($values),
                ];
            }, $this->histograms),
        ];
    }

    private function buildKey(string $name, array $attributes): string
    {
        if (empty($attributes)) {
            return $name;
        }
        ksort($attributes);
        $parts = [$name];
        foreach ($attributes as $key => $value) {
            $parts[] = "{$key}={$value}";
        }
        return implode('|', $parts);
    }
}

$telemetry = new SimpleTelemetry();

echo "✅ Telemetry collector initialized\n";
echo "   OTLP Endpoint: http://localhost:4318/v1/metrics (simulated)\n\n";

// Create a tool that records telemetry
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($telemetry) {
        $startTime = microtime(true);

        // Record counter: tool invocation
        $telemetry->recordCounter('tool.invocations', 1, [
            'tool' => 'calculate',
        ]);

        try {
            $result = eval("return {$input['expression']};");
            $duration = (microtime(true) - $startTime) * 1000;

            // Record successful execution
            $telemetry->recordCounter('tool.executions.success', 1, [
                'tool' => 'calculate',
            ]);

            // Record latency histogram
            $telemetry->recordHistogram('tool.duration.ms', $duration, [
                'tool' => 'calculate',
            ]);

            // Record gauge: last result
            $telemetry->recordGauge('tool.last_result', (float) $result, [
                'tool' => 'calculate',
            ]);

            return $result;
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            // Record failed execution
            $telemetry->recordCounter('tool.executions.failed', 1, [
                'tool' => 'calculate',
                'error_type' => get_class($e),
            ]);

            $telemetry->recordHistogram('tool.duration.ms', $duration, [
                'tool' => 'calculate',
                'status' => 'failed',
            ]);

            throw $e;
        }
    });

$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful math assistant.');

// Run queries and collect telemetry
$queries = [
    'What is 156 * 23?',
    'Calculate 500 + 250',
    'What is 1000 / 25?',
];

echo "🚀 Running queries with telemetry...\n\n";

foreach ($queries as $i => $query) {
    echo "[" . ($i + 1) . "] {$query}\n";

    $startTime = microtime(true);

    // Record counter: agent request started
    $telemetry->recordCounter('agent.requests.total');

    try {
        $result = $agent->run($query);
        $duration = (microtime(true) - $startTime) * 1000;

        // Get token usage
        $usage = $result->getTokenUsage();
        $inputTokens = $usage['input'] ?? 0;
        $outputTokens = $usage['output'] ?? 0;

        // Record agent metrics
        $telemetry->recordCounter('agent.requests.success');
        $telemetry->recordHistogram('agent.tokens.input', (float) $inputTokens);
        $telemetry->recordHistogram('agent.tokens.output', (float) $outputTokens);
        $telemetry->recordHistogram('agent.duration.ms', $duration);
        $telemetry->recordGauge('agent.active_requests', 0.0);

        echo "  ✅ {$result->getAnswer()}\n";
        echo "     Tokens: {$inputTokens} in, {$outputTokens} out\n";
        echo "     Duration: " . round($duration, 2) . "ms\n\n";
    } catch (\Throwable $e) {
        $duration = (microtime(true) - $startTime) * 1000;

        $telemetry->recordCounter('agent.requests.failed');
        $telemetry->recordHistogram('agent.duration.ms', $duration);

        echo "  ❌ Error: {$e->getMessage()}\n\n";
    }
}

// Display collected metrics
echo "\n📊 Telemetry Summary:\n";
echo str_repeat('─', 60) . "\n\n";

$allMetrics = $telemetry->getAllMetrics();

echo "Counters:\n";
foreach ($allMetrics['counters'] as $name => $value) {
    echo "  {$name}: {$value}\n";
}

echo "\nGauges:\n";
foreach ($allMetrics['gauges'] as $name => $value) {
    echo "  {$name}: {$value}\n";
}

echo "\nHistograms:\n";
foreach ($allMetrics['histograms'] as $name => $stats) {
    echo "  {$name}:\n";
    echo "    Count: {$stats['count']}\n";
    echo "    Min: " . round($stats['min'], 2) . "\n";
    echo "    Max: " . round($stats['max'], 2) . "\n";
    echo "    Avg: " . round($stats['avg'], 2) . "\n";
    echo "    Sum: " . round($stats['sum'], 2) . "\n";
}

// Calculate summary statistics
$totalRequests = ($allMetrics['counters']['agent.requests.success'] ?? 0) +
                  ($allMetrics['counters']['agent.requests.failed'] ?? 0);
$successCount = $allMetrics['counters']['agent.requests.success'] ?? 0;
$successRate = $totalRequests > 0 ? ($successCount / $totalRequests) * 100 : 0;

echo "\n📦 Agent Summary:\n";
echo "  Total Requests: {$totalRequests}\n";
echo "  Success Rate: " . round($successRate, 2) . "%\n";

// Flush metrics (would send to OTLP endpoint)
echo "\n🌐 Flushing metrics to OTLP endpoint (simulated)...\n";
echo "   Endpoint: http://localhost:4318/v1/metrics\n";
echo "   Counters: " . count($allMetrics['counters']) . "\n";
echo "   Gauges: " . count($allMetrics['gauges']) . "\n";
echo "   Histograms: " . count($allMetrics['histograms']) . "\n";

echo "\n✅ OpenTelemetry demonstration complete.\n";
echo "\nIn production, install official OpenTelemetry SDK:\n";
echo "  composer require open-telemetry/sdk open-telemetry/exporter-otlp\n";
echo "\nMetrics would be sent to Grafana, Prometheus, Datadog, or similar.\n";
