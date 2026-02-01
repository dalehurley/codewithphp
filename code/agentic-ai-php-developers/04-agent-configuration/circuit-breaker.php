<?php

/**
 * Circuit Breaker Pattern
 * 
 * Demonstrates fault tolerance with circuit breakers:
 * - Basic circuit breaker implementation
 * - State management (closed, open, half-open)
 * - Failure threshold detection
 * - Recovery testing
 * - Integration with agent execution
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

// Example 1: Basic Circuit Breaker
echo "=== Example 1: Basic Circuit Breaker ===\n\n";

class CircuitBreaker
{
    private int $failures = 0;
    private string $state = 'closed'; // closed, open, half-open
    private ?int $openedAt = null;
    
    public function __construct(
        private int $failureThreshold = 5,
        private int $recoveryTimeout = 60, // seconds
        private string $name = 'default'
    ) {}
    
    public function call(callable $fn): mixed
    {
        $this->checkState();
        
        if ($this->state === 'open') {
            throw new \RuntimeException(
                "Circuit breaker '{$this->name}' is open - service unavailable"
            );
        }
        
        try {
            $result = $fn();
            $this->onSuccess();
            return $result;
            
        } catch (\Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }
    
    private function checkState(): void
    {
        if ($this->state === 'open' && $this->openedAt !== null) {
            $elapsed = time() - $this->openedAt;
            
            if ($elapsed >= $this->recoveryTimeout) {
                echo "[{$this->name}] Transitioning to half-open state\n";
                $this->state = 'half-open';
                $this->failures = 0;
            }
        }
    }
    
    private function onSuccess(): void
    {
        if ($this->state === 'half-open') {
            echo "[{$this->name}] Recovery successful, closing circuit\n";
            $this->state = 'closed';
        }
        
        $this->failures = 0;
    }
    
    private function onFailure(): void
    {
        $this->failures++;
        echo "[{$this->name}] Failure #{$this->failures}\n";
        
        if ($this->state === 'half-open') {
            echo "[{$this->name}] Half-open test failed, re-opening circuit\n";
            $this->state = 'open';
            $this->openedAt = time();
        } elseif ($this->failures >= $this->failureThreshold) {
            echo "[{$this->name}] Threshold reached, opening circuit\n";
            $this->state = 'open';
            $this->openedAt = time();
        }
    }
    
    public function getState(): string
    {
        return $this->state;
    }
    
    public function getFailures(): int
    {
        return $this->failures;
    }
    
    public function reset(): void
    {
        $this->state = 'closed';
        $this->failures = 0;
        $this->openedAt = null;
        echo "[{$this->name}] Circuit breaker reset\n";
    }
}

// Test circuit breaker behavior
$breaker = new CircuitBreaker(
    failureThreshold: 3,
    recoveryTimeout: 5, // 5 seconds for testing
    name: 'test-service'
);

echo "Initial state: {$breaker->getState()}\n\n";

// Simulate failures until circuit opens
for ($i = 1; $i <= 4; $i++) {
    try {
        $breaker->call(function () use ($i) {
            throw new \RuntimeException("Simulated failure $i");
        });
    } catch (\Exception $e) {
        echo "Caught: {$e->getMessage()}\n";
    }
    echo "State: {$breaker->getState()}, Failures: {$breaker->getFailures()}\n\n";
}

// Try to call while open
echo "Attempting call while circuit is open...\n";
try {
    $breaker->call(fn() => "This won't execute");
} catch (\Exception $e) {
    echo "Caught: {$e->getMessage()}\n\n";
}

echo "\n";

// Example 2: Circuit Breaker with Metrics
echo "=== Example 2: Circuit Breaker with Metrics ===\n\n";

class MonitoredCircuitBreaker extends CircuitBreaker
{
    private int $successCount = 0;
    private int $failureCount = 0;
    private int $rejectedCount = 0;
    private array $stateHistory = [];
    
    public function call(callable $fn): mixed
    {
        try {
            $result = parent::call($fn);
            $this->successCount++;
            return $result;
            
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'is open')) {
                $this->rejectedCount++;
            }
            throw $e;
        } catch (\Exception $e) {
            $this->failureCount++;
            throw $e;
        }
    }
    
    private function recordStateChange(string $from, string $to): void
    {
        $this->stateHistory[] = [
            'from' => $from,
            'to' => $to,
            'timestamp' => time(),
        ];
    }
    
    public function getMetrics(): array
    {
        $total = $this->successCount + $this->failureCount;
        
        return [
            'success_count' => $this->successCount,
            'failure_count' => $this->failureCount,
            'rejected_count' => $this->rejectedCount,
            'total_attempts' => $total,
            'success_rate' => $total > 0 ? round($this->successCount / $total * 100, 2) : 0,
            'failure_rate' => $total > 0 ? round($this->failureCount / $total * 100, 2) : 0,
            'current_state' => $this->getState(),
        ];
    }
}

$monitored = new MonitoredCircuitBreaker(
    failureThreshold: 3,
    recoveryTimeout: 5,
    name: 'monitored-service'
);

// Mix of successes and failures
$operations = [
    ['success', true],
    ['success', true],
    ['failure', false],
    ['failure', false],
    ['failure', false], // Opens circuit
    ['rejected', false],
    ['rejected', false],
];

foreach ($operations as [$label, $shouldSucceed]) {
    try {
        $monitored->call(function () use ($shouldSucceed) {
            if (!$shouldSucceed) {
                throw new \RuntimeException('Operation failed');
            }
            return 'Success';
        });
        echo "✓ $label\n";
    } catch (\Exception $e) {
        echo "✗ $label: {$e->getMessage()}\n";
    }
}

echo "\nMetrics:\n";
$metrics = $monitored->getMetrics();
foreach ($metrics as $key => $value) {
    echo "  " . str_replace('_', ' ', ucfirst($key)) . ": $value\n";
}

echo "\n";

// Example 3: Per-Service Circuit Breakers
echo "=== Example 3: Per-Service Circuit Breakers ===\n\n";

class CircuitBreakerRegistry
{
    private array $breakers = [];
    
    public function get(string $service): CircuitBreaker
    {
        if (!isset($this->breakers[$service])) {
            $this->breakers[$service] = new CircuitBreaker(
                failureThreshold: 5,
                recoveryTimeout: 30,
                name: $service
            );
        }
        
        return $this->breakers[$service];
    }
    
    public function getStatus(): array
    {
        $status = [];
        foreach ($this->breakers as $service => $breaker) {
            $status[$service] = [
                'state' => $breaker->getState(),
                'failures' => $breaker->getFailures(),
            ];
        }
        return $status;
    }
    
    public function resetAll(): void
    {
        foreach ($this->breakers as $breaker) {
            $breaker->reset();
        }
    }
}

$registry = new CircuitBreakerRegistry();

// Simulate calls to different services
$services = ['database', 'api', 'cache'];

foreach ($services as $service) {
    $breaker = $registry->get($service);
    
    echo "Testing $service:\n";
    
    // Simulate some failures
    for ($i = 1; $i <= 2; $i++) {
        try {
            $breaker->call(function () {
                throw new \RuntimeException('Service error');
            });
        } catch (\Exception $e) {
            // Ignore for simulation
        }
    }
    
    echo "\n";
}

echo "Overall status:\n";
$status = $registry->getStatus();
foreach ($status as $service => $info) {
    echo sprintf(
        "  %s: %s (%d failures)\n",
        ucfirst($service),
        $info['state'],
        $info['failures']
    );
}

echo "\n";

// Example 4: Integration with Agent Execution
echo "=== Example 4: Agent Wrapper with Circuit Breaker ===\n\n";

class ResilientAgentRunner
{
    private CircuitBreaker $breaker;
    
    public function __construct(
        private object $agent,
        ?CircuitBreaker $breaker = null
    ) {
        $this->breaker = $breaker ?? new CircuitBreaker(
            failureThreshold: 3,
            recoveryTimeout: 60,
            name: 'agent-runner'
        );
    }
    
    public function run(string $input): string
    {
        try {
            return $this->breaker->call(function () use ($input) {
                // This would normally call $this->agent->run($input)
                // For simulation, we'll just return a result
                return "Processed: $input";
            });
            
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'is open')) {
                return "Service is temporarily unavailable. Please try again later.";
            }
            throw $e;
        }
    }
    
    public function getStatus(): array
    {
        return [
            'state' => $this->breaker->getState(),
            'failures' => $this->breaker->getFailures(),
        ];
    }
}

$runner = new ResilientAgentRunner(
    agent: new stdClass(), // Simulated agent
    breaker: new CircuitBreaker(
        failureThreshold: 2,
        recoveryTimeout: 5,
        name: 'agent'
    )
);

echo "Running agent with circuit breaker protection:\n";
try {
    $result = $runner->run("Calculate 2 + 2");
    echo "Result: $result\n";
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

$status = $runner->getStatus();
echo "Agent status: {$status['state']} ({$status['failures']} failures)\n";

echo "\n✅ Circuit breaker examples completed!\n";
