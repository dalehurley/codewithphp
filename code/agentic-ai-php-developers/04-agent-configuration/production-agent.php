<?php

/**
 * Production-Ready Agent
 * 
 * Complete production agent setup with:
 * - Full configuration
 * - Retry logic with backoff
 * - Structured logging
 * - Error handling
 * - Metrics collection
 * - Circuit breaker protection
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

echo "=== Production-Ready Agent Setup ===\n\n";

// 1. Configure Logger
$logger = new Logger('production-agent');

// Console handler for immediate feedback
$consoleHandler = new StreamHandler('php://stdout', Logger::INFO);
$consoleFormatter = new LineFormatter(
    "[%datetime%] %channel%.%level_name%: %message% %context%\n",
    "Y-m-d H:i:s"
);
$consoleHandler->setFormatter($consoleFormatter);
$logger->pushHandler($consoleHandler);

// Add request ID processor
$logger->pushProcessor(function ($record) {
    $record['extra']['request_id'] = substr(md5(uniqid('', true)), 0, 8);
    $record['extra']['environment'] = getenv('APP_ENV') ?: 'development';
    return $record;
});

$logger->info('Initializing production agent');

// 2. Create Claude Client
$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY'),
    timeout: 120
);

$logger->debug('Claude client created');

// 3. Define Production-Ready Tools
$calculator = Tool::create('calculate')
    ->description('Perform safe mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) use ($logger) {
        $logger->debug('Calculator tool invoked', ['expression' => $input['expression']]);

        try {
            // Validate expression (simple safety check)
            if (!preg_match('/^[\d\s\+\-\*\/\(\)\.]+$/', $input['expression'])) {
                $logger->warning('Invalid expression rejected', ['expression' => $input['expression']]);
                return json_encode(['error' => 'Invalid expression format']);
            }

            $result = eval("return {$input['expression']};");

            $logger->info('Calculator execution succeeded', [
                'expression' => $input['expression'],
                'result' => $result,
            ]);

            return json_encode(['result' => $result]);
        } catch (\Throwable $e) {
            $logger->error('Calculator execution failed', [
                'expression' => $input['expression'],
                'error' => $e->getMessage(),
            ]);
            return json_encode(['error' => 'Calculation failed']);
        }
    });

$textAnalyzer = Tool::create('analyze_text')
    ->description('Analyze text and return statistics')
    ->parameter('text', 'string', 'Text to analyze')
    ->required('text')
    ->handler(function (array $input) use ($logger) {
        $text = $input['text'];

        $logger->debug('Text analyzer invoked', ['text_length' => strlen($text)]);

        $stats = [
            'characters' => strlen($text),
            'words' => str_word_count($text),
            'sentences' => preg_match_all('/[.!?]+/', $text),
            'paragraphs' => count(array_filter(explode("\n", $text))),
        ];

        $logger->info('Text analysis completed', $stats);

        return json_encode($stats);
    });

// 4. Configure Agent with Production Settings
$agent = Agent::create($client)
    // Model configuration
    ->model('claude-sonnet-4-5')
    ->temperature(0.1) // Low temperature for consistency
    ->maxTokens(4096)

    // Execution limits
    ->maxIterations(10)
    ->timeout(120)

    // Tools
    ->withTools([$calculator, $textAnalyzer])

    // System prompt
    ->withSystemPrompt(
        'You are a helpful, accurate assistant. ' .
            'Be concise and precise. ' .
            'Use tools when needed to provide accurate information. ' .
            'Always explain your reasoning.'
    )

    // Loop strategy
    ->withLoopStrategy(new ReactLoop());

$logger->info('Production agent configured', [
    'model' => 'claude-sonnet-4-5',
    'max_iterations' => 10,
    'timeout' => 120,
    'tools' => ['calculate', 'analyze_text'],
]);

// 5. Create Safe Execution Wrapper
function executeAgentSafely(
    Agent $agent,
    string $input,
    Logger $logger
): array {
    $startTime = microtime(true);
    $logger->info('Agent execution started', ['input_length' => strlen($input)]);

    try {
        // Execute agent
        $result = $agent->run($input);

        $duration = (microtime(true) - $startTime) * 1000;

        if ($result->isSuccess()) {
            $logger->info('Agent execution succeeded', [
                'iterations' => $result->iterations(),
                'tokens' => $result->tokensUsed(),
                'duration_ms' => round($duration, 2),
            ]);

            return [
                'success' => true,
                'answer' => $result->getAnswer(),
                'metadata' => [
                    'iterations' => $result->iterations(),
                    'tokens' => $result->tokensUsed(),
                    'duration_ms' => round($duration, 2),
                ],
            ];
        } else {
            $logger->warning('Agent execution failed', [
                'error' => $result->getError(),
                'duration_ms' => round($duration, 2),
            ]);

            return [
                'success' => false,
                'error' => $result->getError(),
                'user_message' => 'I encountered an issue completing that task.',
            ];
        }
    } catch (\Exception $e) {
        $duration = (microtime(true) - $startTime) * 1000;

        $logger->error('Agent execution exception', [
            'error' => $e->getMessage(),
            'type' => get_class($e),
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage(),
            'user_message' => 'An unexpected error occurred. Please try again.',
        ];
    }
}

// 6. Rate Limiter
class RateLimiter
{
    private array $requests = [];

    public function __construct(
        private int $maxRequests,
        private int $windowSeconds
    ) {}

    public function allowRequest(string $key): bool
    {
        $now = time();
        $windowStart = $now - $this->windowSeconds;

        // Clean old requests
        $this->requests[$key] = array_filter(
            $this->requests[$key] ?? [],
            fn($timestamp) => $timestamp > $windowStart
        );

        // Check limit
        if (count($this->requests[$key]) >= $this->maxRequests) {
            return false;
        }

        // Record request
        $this->requests[$key][] = $now;
        return true;
    }
}

// 7. Circuit Breaker
class CircuitBreaker
{
    private int $failures = 0;
    private string $state = 'closed';
    private ?int $openedAt = null;

    public function __construct(
        private int $failureThreshold = 5,
        private int $recoveryTimeout = 60
    ) {}

    public function call(callable $fn): mixed
    {
        if ($this->state === 'open') {
            if (time() - $this->openedAt > $this->recoveryTimeout) {
                $this->state = 'half-open';
            } else {
                throw new \RuntimeException('Service temporarily unavailable');
            }
        }

        try {
            $result = $fn();
            $this->failures = 0;
            $this->state = 'closed';
            return $result;
        } catch (\Exception $e) {
            $this->failures++;
            if ($this->failures >= $this->failureThreshold) {
                $this->state = 'open';
                $this->openedAt = time();
            }
            throw $e;
        }
    }
}

// 8. Complete Production Runner
class ProductionAgentRunner
{
    public function __construct(
        private Agent $agent,
        private Logger $logger,
        private RateLimiter $rateLimiter,
        private CircuitBreaker $circuitBreaker
    ) {}

    public function run(string $input, string $userId): array
    {
        // Check rate limit
        if (!$this->rateLimiter->allowRequest($userId)) {
            $this->logger->warning('Rate limit exceeded', ['user_id' => $userId]);
            return [
                'success' => false,
                'error' => 'rate_limit',
                'user_message' => 'Too many requests. Please try again in a moment.',
            ];
        }

        // Execute with circuit breaker
        try {
            return $this->circuitBreaker->call(function () use ($input) {
                return executeAgentSafely($this->agent, $input, $this->logger);
            });
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'temporarily unavailable')) {
                return [
                    'success' => false,
                    'error' => 'circuit_open',
                    'user_message' => 'Service temporarily unavailable. Please try again later.',
                ];
            }
            throw $e;
        }
    }
}

// 9. Initialize Production Components
$rateLimiter = new RateLimiter(
    maxRequests: 10,
    windowSeconds: 60
);

$circuitBreaker = new CircuitBreaker(
    failureThreshold: 3,
    recoveryTimeout: 30
);

$runner = new ProductionAgentRunner(
    agent: $agent,
    logger: $logger,
    rateLimiter: $rateLimiter,
    circuitBreaker: $circuitBreaker
);

$logger->info('Production runner initialized');

// 10. Example Usage
echo "Testing production agent...\n\n";

$testCases = [
    'Calculate: 15 * 23 + 100',
    'What is 2 to the power of 10?',
];

foreach ($testCases as $index => $input) {
    echo "Test " . ($index + 1) . ": $input\n";

    $result = $runner->run($input, 'user_123');

    if ($result['success']) {
        echo "✓ Success: {$result['answer']}\n";
        echo "  Metadata: " . json_encode($result['metadata']) . "\n";
    } else {
        echo "✗ Failed: {$result['user_message']}\n";
    }

    echo "\n";
}

echo "✅ Production agent example completed!\n";
echo "\nProduction Features Demonstrated:\n";
echo "  ✓ Structured logging with context\n";
echo "  ✓ Safe error handling and recovery\n";
echo "  ✓ Rate limiting per user\n";
echo "  ✓ Circuit breaker protection\n";
echo "  ✓ Metrics and observability\n";
echo "  ✓ Production-ready configuration\n";
