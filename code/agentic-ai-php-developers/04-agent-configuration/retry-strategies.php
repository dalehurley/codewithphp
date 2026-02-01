<?php

/**
 * Retry Strategies and Backoff
 * 
 * Demonstrates retry logic patterns:
 * - Exponential backoff
 * - Backoff with jitter
 * - Selective retry policies
 * - Retry callbacks
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Simulated retry strategy classes (framework provides these)
class ExponentialBackoff
{
    public function __construct(
        private int $baseDelay,
        private int $maxDelay,
        private float $multiplier = 2.0
    ) {}
    
    public function calculateDelay(int $attempt): int
    {
        $delay = $this->baseDelay * pow($this->multiplier, $attempt - 1);
        return min((int)$delay, $this->maxDelay);
    }
}

class ExponentialBackoffWithJitter extends ExponentialBackoff
{
    public function __construct(
        int $baseDelay,
        int $maxDelay,
        float $multiplier = 2.0,
        private float $jitterFactor = 0.25
    ) {
        parent::__construct($baseDelay, $maxDelay, $multiplier);
    }
    
    public function calculateDelay(int $attempt): int
    {
        $baseDelay = parent::calculateDelay($attempt);
        $jitter = $baseDelay * $this->jitterFactor;
        $min = $baseDelay - $jitter;
        $max = $baseDelay + $jitter;
        return random_int((int)$min, (int)$max);
    }
}

// Example 1: Exponential Backoff
echo "=== Example 1: Exponential Backoff ===\n";

$backoff = new ExponentialBackoff(
    baseDelay: 1000, // 1 second
    maxDelay: 30000, // 30 seconds
    multiplier: 2.0
);

echo "Delay progression:\n";
for ($attempt = 1; $attempt <= 5; $attempt++) {
    $delay = $backoff->calculateDelay($attempt);
    echo sprintf("  Attempt %d: wait %dms (%.1fs)\n", $attempt, $delay, $delay / 1000);
}
echo "\n";

// Example 2: Backoff with Jitter
echo "=== Example 2: Exponential Backoff with Jitter ===\n";

$jitterBackoff = new ExponentialBackoffWithJitter(
    baseDelay: 1000,
    maxDelay: 30000,
    multiplier: 2.0,
    jitterFactor: 0.25 // ±25%
);

echo "Delay progression with jitter (5 runs):\n";
for ($run = 1; $run <= 5; $run++) {
    echo "Run $run: ";
    $delays = [];
    for ($attempt = 1; $attempt <= 4; $attempt++) {
        $delays[] = $jitterBackoff->calculateDelay($attempt) . 'ms';
    }
    echo implode(' → ', $delays) . "\n";
}
echo "\n";

// Example 3: Retry Policy Simulation
echo "=== Example 3: Retry Policy Simulation ===\n";

class RetryableException extends \Exception {}
class NonRetryableException extends \Exception {}

class RetryPolicy
{
    private array $retryableExceptions = [];
    private array $nonRetryableExceptions = [];
    private int $maxAttempts = 3;
    
    public static function create(): self
    {
        return new self();
    }
    
    public function retryOn(array $exceptions): self
    {
        $this->retryableExceptions = $exceptions;
        return $this;
    }
    
    public function dontRetryOn(array $exceptions): self
    {
        $this->nonRetryableExceptions = $exceptions;
        return $this;
    }
    
    public function maxAttempts(int $attempts): self
    {
        $this->maxAttempts = $attempts;
        return $this;
    }
    
    public function shouldRetry(\Exception $e, int $attempt): bool
    {
        if ($attempt >= $this->maxAttempts) {
            return false;
        }
        
        foreach ($this->nonRetryableExceptions as $class) {
            if ($e instanceof $class) {
                return false;
            }
        }
        
        foreach ($this->retryableExceptions as $class) {
            if ($e instanceof $class) {
                return true;
            }
        }
        
        return false;
    }
}

$policy = RetryPolicy::create()
    ->retryOn([RetryableException::class])
    ->dontRetryOn([NonRetryableException::class])
    ->maxAttempts(5);

// Test with retryable exception
echo "Testing retryable exception:\n";
$retryable = new RetryableException('Temporary failure');
for ($attempt = 1; $attempt <= 6; $attempt++) {
    $shouldRetry = $policy->shouldRetry($retryable, $attempt);
    echo sprintf(
        "  Attempt %d: %s\n",
        $attempt,
        $shouldRetry ? '↻ Retry' : '⊗ Stop'
    );
}
echo "\n";

// Test with non-retryable exception
echo "Testing non-retryable exception:\n";
$nonRetryable = new NonRetryableException('Permanent failure');
$shouldRetry = $policy->shouldRetry($nonRetryable, 1);
echo sprintf("  Attempt 1: %s\n", $shouldRetry ? '↻ Retry' : '⊗ Stop (permanent failure)');
echo "\n";

// Example 4: Complete Retry Logic with Agent
echo "=== Example 4: Retry with Callback ===\n\n";

// Skip this example if no API key is available
if (!getenv('ANTHROPIC_API_KEY')) {
    echo "Skipping agent example (requires ANTHROPIC_API_KEY)\n";
    echo "Simulating retry behavior without agent...\n\n";
    
    // Simulate retry behavior with callbacks
    $retryCount = 0;
    $backoffStrategy = new ExponentialBackoffWithJitter(1000, 30000, 2.0, 0.25);
    
    $onRetry = function (int $attempt, \Exception $exception, int $delay) use (&$retryCount) {
        $retryCount++;
        echo sprintf(
            "  ↻ Retry %d: %s (waiting %dms)\n",
            $attempt,
            $exception->getMessage(),
            $delay
        );
    };
    
    echo "Simulating retry with callbacks:\n";
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        if ($attempt > 1) {
            $delay = $backoffStrategy->calculateDelay($attempt - 1);
            $onRetry($attempt - 1, new \RuntimeException('Simulated failure'), $delay);
            usleep($delay * 1000); // Actually wait (converted to microseconds)
        }
        
        if ($attempt === 3) {
            echo "  ✓ Success on attempt 3\n";
        }
    }
    
    echo "\nTotal retries: $retryCount\n";
} else {
    $client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

    // Create a tool that simulates failures
    $unreliableTool = Tool::create('unreliable_api')
        ->description('Simulates an unreliable API call')
        ->parameter('data', 'string', 'Data to process')
        ->required('data')
        ->handler(function (array $input) {
            static $attempts = 0;
            $attempts++;
            
            // Fail first 2 attempts, succeed on 3rd
            if ($attempts <= 2) {
                throw new \RuntimeException("API temporarily unavailable (attempt $attempts)");
            }
            
            return json_encode([
                'success' => true,
                'data' => strtoupper($input['data']),
                'attempts' => $attempts,
            ]);
        });

    // Simulate retry behavior with callbacks
    $retryCount = 0;
    $backoffStrategy = new ExponentialBackoffWithJitter(1000, 30000, 2.0, 0.25);

    $onRetry = function (int $attempt, \Exception $exception, int $delay) use (&$retryCount) {
        $retryCount++;
        echo sprintf(
            "  ↻ Retry %d: %s (waiting %dms)\n",
            $attempt,
            $exception->getMessage(),
            $delay
        );
    };

    echo "Simulating retry with callbacks:\n";
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        if ($attempt > 1) {
            $delay = $backoffStrategy->calculateDelay($attempt - 1);
            $onRetry($attempt - 1, new \RuntimeException('Simulated failure'), $delay);
            usleep($delay * 1000); // Actually wait (converted to microseconds)
        }
        
        if ($attempt === 3) {
            echo "  ✓ Success on attempt 3\n";
        }
    }

    echo "\nTotal retries: $retryCount\n";
}

echo "\n✅ Retry strategy examples completed!\n";
