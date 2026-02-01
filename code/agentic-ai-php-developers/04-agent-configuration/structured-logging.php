<?php

/**
 * Structured Logging and Observability
 * 
 * Demonstrates logging patterns:
 * - PSR-3 logger integration
 * - Structured log context
 * - Log levels and filtering
 * - Custom processors
 * - Lifecycle callbacks
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

// Example 1: Basic Logging Setup
echo "=== Example 1: Basic Logging Setup ===\n\n";

$logger = new Logger('agent');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$logger->debug('This is a debug message', ['key' => 'value']);
$logger->info('Agent initialized', ['model' => 'claude-sonnet-4-5']);
$logger->warning('Rate limit approaching', ['requests' => 90, 'limit' => 100]);
$logger->error('Tool execution failed', ['tool' => 'database_query', 'error' => 'Connection timeout']);

echo "\n";

// Example 2: JSON Structured Logging
echo "=== Example 2: JSON Structured Logging ===\n\n";

$jsonLogger = new Logger('agent');
$handler = new StreamHandler('php://stdout', Logger::INFO);
$handler->setFormatter(new JsonFormatter());
$jsonLogger->pushHandler($handler);

$jsonLogger->info('Agent task started', [
    'task' => 'Calculate quarterly revenue',
    'user_id' => 'user_123',
    'session_id' => 'sess_abc',
]);

$jsonLogger->info('Tool executed', [
    'tool' => 'database_query',
    'duration_ms' => 245,
    'rows_returned' => 1250,
]);

$jsonLogger->info('Agent task completed', [
    'iterations' => 4,
    'tokens_used' => 3421,
    'duration_ms' => 2341,
]);

echo "\n";

// Example 3: Log Processors
echo "=== Example 3: Custom Log Processors ===\n\n";

class UidProcessor
{
    private string $uid;

    public function __construct()
    {
        $this->uid = substr(md5(uniqid('', true)), 0, 8);
    }

    public function __invoke(array $record): array
    {
        $record['extra']['request_id'] = $this->uid;
        return $record;
    }
}

class EnvironmentProcessor
{
    public function __invoke(array $record): array
    {
        $record['extra']['environment'] = getenv('APP_ENV') ?: 'development';
        $record['extra']['service'] = 'agentic-api';
        $record['extra']['hostname'] = gethostname();
        return $record;
    }
}

$enrichedLogger = new Logger('agent');
$handler = new StreamHandler('php://stdout', Logger::INFO);
$handler->setFormatter(new JsonFormatter());
$enrichedLogger->pushHandler($handler);
$enrichedLogger->pushProcessor(new UidProcessor());
$enrichedLogger->pushProcessor(new EnvironmentProcessor());

$enrichedLogger->info('Request processed', [
    'user' => 'john@example.com',
    'action' => 'analyze_data',
]);

echo "\n";

// Example 4: Lifecycle Logging with Agent
echo "=== Example 4: Agent Lifecycle Logging ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create logger for agent
$agentLogger = new Logger('agent');
$agentLogger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
$agentLogger->pushProcessor(new UidProcessor());

// Define a simple tool
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($agentLogger) {
        $agentLogger->debug('Tool handler executing', [
            'tool' => 'calculate',
            'expression' => $input['expression'],
        ]);

        try {
            $result = eval("return {$input['expression']};");

            $agentLogger->debug('Tool execution succeeded', [
                'tool' => 'calculate',
                'result' => $result,
            ]);

            return json_encode(['result' => $result]);
        } catch (\Throwable $e) {
            $agentLogger->error('Tool execution failed', [
                'tool' => 'calculate',
                'error' => $e->getMessage(),
            ]);
            return json_encode(['error' => 'Invalid expression']);
        }
    });

// Create agent with logging callbacks (simulated)
$agent = Agent::create($client)
    ->withTool($calculator)
    ->withSystemPrompt('You are a helpful assistant.');

// Simulate lifecycle events
$agentLogger->info('Agent task started', [
    'task' => 'Calculate: 42 * 13',
]);

$agentLogger->debug('Iteration started', [
    'iteration' => 1,
]);

$agentLogger->debug('Tool called', [
    'tool' => 'calculate',
    'input' => ['expression' => '42 * 13'],
]);

$agentLogger->debug('Tool completed', [
    'tool' => 'calculate',
    'duration_ms' => 15,
]);

$agentLogger->debug('Iteration completed', [
    'iteration' => 1,
    'tokens_used' => 234,
]);

$agentLogger->info('Agent task completed', [
    'iterations' => 1,
    'total_tokens' => 234,
    'duration_ms' => 1250,
]);

echo "\n";

// Example 5: Log Filtering by Level
echo "=== Example 5: Log Level Filtering ===\n\n";

$levels = [
    'debug' => Logger::DEBUG,
    'info' => Logger::INFO,
    'warning' => Logger::WARNING,
    'error' => Logger::ERROR,
];

foreach ($levels as $name => $level) {
    $filteredLogger = new Logger('agent');
    $filteredLogger->pushHandler(new StreamHandler('php://stdout', $level));

    echo "Logger with level: " . strtoupper($name) . "\n";

    $filteredLogger->debug('Debug message (only visible in DEBUG mode)');
    $filteredLogger->info('Info message (visible in INFO and above)');
    $filteredLogger->warning('Warning message (visible in WARNING and above)');
    $filteredLogger->error('Error message (visible in ERROR and above)');

    echo "\n";
}

// Example 6: Metrics and Performance Logging
echo "=== Example 6: Performance Metrics Logging ===\n\n";

$metricsLogger = new Logger('metrics');
$handler = new StreamHandler('php://stdout', Logger::INFO);
$handler->setFormatter(new JsonFormatter());
$metricsLogger->pushHandler($handler);

// Simulate performance metrics
$metricsLogger->info('agent.request', [
    'duration_ms' => 2341,
    'tokens_input' => 234,
    'tokens_output' => 567,
    'tokens_total' => 801,
    'iterations' => 4,
    'tools_called' => 2,
    'status' => 'success',
]);

$metricsLogger->info('tool.execution', [
    'tool' => 'database_query',
    'duration_ms' => 245,
    'rows_returned' => 1250,
    'status' => 'success',
]);

$metricsLogger->warning('rate_limit.approaching', [
    'current' => 90,
    'limit' => 100,
    'percentage' => 90.0,
]);

echo "\n✅ Structured logging examples completed!\n";
