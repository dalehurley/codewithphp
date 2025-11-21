<?php
/**
 * Circuit Breaker Pattern
 *
 * Implements the circuit breaker pattern for handling failures in distributed
 * systems. Prevents cascading failures by stopping calls to failing services.
 *
 * States:
 * - CLOSED: Normal operation, requests pass through
 * - OPEN: Too many failures, reject requests immediately
 * - HALF_OPEN: Testing if service recovered
 *
 * @category  Algorithms
 * @package   ConcurrentAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter31;

/**
 * Circuit breaker states
 */
enum CircuitState: string
{
    case CLOSED = 'closed';
    case OPEN = 'open';
    case HALF_OPEN = 'half_open';
}

/**
 * Circuit breaker exception
 */
class CircuitBreakerException extends \RuntimeException
{
}

/**
 * Circuit breaker for protecting against cascading failures
 */
class CircuitBreaker
{
    private CircuitState $state = CircuitState::CLOSED;
    private int $failureCount = 0;
    private int $successCount = 0;
    private int $failureThreshold;
    private int $successThreshold;
    private int $timeout;
    private ?float $lastFailureTime = null;
    private ?float $openedAt = null;

    /**
     * @param int $failureThreshold Failures before opening circuit
     * @param int $timeout Seconds to wait before half-open
     * @param int $successThreshold Successes needed to close from half-open
     */
    public function __construct(
        int $failureThreshold = 5,
        int $timeout = 60,
        int $successThreshold = 2
    ) {
        $this->failureThreshold = $failureThreshold;
        $this->timeout = $timeout;
        $this->successThreshold = $successThreshold;
    }

    /**
     * Execute operation through circuit breaker
     *
     * @param callable $operation Operation to execute
     * @return mixed Operation result
     * @throws CircuitBreakerException If circuit is open
     * @throws \Throwable If operation fails
     */
    public function call(callable $operation): mixed
    {
        if ($this->state === CircuitState::OPEN) {
            if ($this->shouldAttemptReset()) {
                $this->state = CircuitState::HALF_OPEN;
                echo "[Circuit Breaker] State: HALF_OPEN (testing recovery)\n";
            } else {
                throw new CircuitBreakerException(
                    "Circuit breaker is OPEN. Service unavailable."
                );
            }
        }

        try {
            $result = $operation();
            $this->onSuccess();
            return $result;
        } catch (\Throwable $e) {
            $this->onFailure();
            throw $e;
        }
    }

    /**
     * Check if enough time has passed to attempt reset
     */
    private function shouldAttemptReset(): bool
    {
        if ($this->openedAt === null) {
            return false;
        }

        return (microtime(true) - $this->openedAt) >= $this->timeout;
    }

    /**
     * Handle successful operation
     */
    private function onSuccess(): void
    {
        $this->lastFailureTime = null;

        if ($this->state === CircuitState::HALF_OPEN) {
            $this->successCount++;

            if ($this->successCount >= $this->successThreshold) {
                $this->state = CircuitState::CLOSED;
                $this->failureCount = 0;
                $this->successCount = 0;
                $this->openedAt = null;
                echo "[Circuit Breaker] State: CLOSED (recovered)\n";
            }
        } else {
            $this->failureCount = 0;
        }
    }

    /**
     * Handle failed operation
     */
    private function onFailure(): void
    {
        $this->lastFailureTime = microtime(true);
        $this->failureCount++;

        if ($this->state === CircuitState::HALF_OPEN) {
            $this->state = CircuitState::OPEN;
            $this->successCount = 0;
            $this->openedAt = microtime(true);
            echo "[Circuit Breaker] State: OPEN (test failed)\n";
        } elseif ($this->failureCount >= $this->failureThreshold) {
            $this->state = CircuitState::OPEN;
            $this->openedAt = microtime(true);
            echo "[Circuit Breaker] State: OPEN (threshold reached: {$this->failureCount} failures)\n";
        }
    }

    /**
     * Get current state
     */
    public function getState(): CircuitState
    {
        return $this->state;
    }

    /**
     * Get failure statistics
     */
    public function getStats(): array
    {
        return [
            'state' => $this->state->value,
            'failures' => $this->failureCount,
            'successes' => $this->successCount,
            'last_failure' => $this->lastFailureTime,
            'opened_at' => $this->openedAt
        ];
    }

    /**
     * Reset circuit breaker manually
     */
    public function reset(): void
    {
        $this->state = CircuitState::CLOSED;
        $this->failureCount = 0;
        $this->successCount = 0;
        $this->lastFailureTime = null;
        $this->openedAt = null;
    }
}

/**
 * HTTP client with circuit breaker
 */
class ResilientHttpClient
{
    private CircuitBreaker $breaker;
    private array $endpoints = [];

    public function __construct(CircuitBreaker $breaker)
    {
        $this->breaker = $breaker;
    }

    /**
     * Make HTTP request through circuit breaker
     *
     * @param string $url URL to request
     * @return array Response data
     * @throws CircuitBreakerException
     * @throws \RuntimeException
     */
    public function get(string $url): array
    {
        return $this->breaker->call(function () use ($url) {
            // Simulate HTTP request
            $response = @file_get_contents($url);

            if ($response === false) {
                throw new \RuntimeException("Failed to fetch: {$url}");
            }

            return [
                'status' => 200,
                'body' => $response,
                'size' => strlen($response)
            ];
        });
    }

    /**
     * Get request with fallback
     *
     * @param string $primaryUrl Primary endpoint
     * @param string $fallbackUrl Fallback endpoint
     * @return array Response data
     */
    public function getWithFallback(string $primaryUrl, string $fallbackUrl): array
    {
        try {
            return $this->get($primaryUrl);
        } catch (CircuitBreakerException $e) {
            echo "[Fallback] Primary circuit is open, using fallback\n";
            return $this->get($fallbackUrl);
        } catch (\Throwable $e) {
            echo "[Fallback] Primary failed: {$e->getMessage()}, trying fallback\n";
            return $this->get($fallbackUrl);
        }
    }
}

/**
 * Multi-endpoint circuit breaker manager
 */
class CircuitBreakerManager
{
    private array $breakers = [];
    private int $defaultFailureThreshold;
    private int $defaultTimeout;

    public function __construct(
        int $defaultFailureThreshold = 5,
        int $defaultTimeout = 60
    ) {
        $this->defaultFailureThreshold = $defaultFailureThreshold;
        $this->defaultTimeout = $defaultTimeout;
    }

    /**
     * Get circuit breaker for endpoint
     *
     * @param string $endpoint Endpoint identifier
     * @return CircuitBreaker
     */
    public function getBreaker(string $endpoint): CircuitBreaker
    {
        if (!isset($this->breakers[$endpoint])) {
            $this->breakers[$endpoint] = new CircuitBreaker(
                $this->defaultFailureThreshold,
                $this->defaultTimeout
            );
        }

        return $this->breakers[$endpoint];
    }

    /**
     * Execute operation with endpoint-specific circuit breaker
     *
     * @param string $endpoint Endpoint identifier
     * @param callable $operation Operation to execute
     * @return mixed Operation result
     */
    public function call(string $endpoint, callable $operation): mixed
    {
        return $this->getBreaker($endpoint)->call($operation);
    }

    /**
     * Get statistics for all circuits
     *
     * @return array Circuit statistics
     */
    public function getStats(): array
    {
        $stats = [];

        foreach ($this->breakers as $endpoint => $breaker) {
            $stats[$endpoint] = $breaker->getStats();
        }

        return $stats;
    }

    /**
     * Reset all circuit breakers
     */
    public function resetAll(): void
    {
        foreach ($this->breakers as $breaker) {
            $breaker->reset();
        }
    }
}

/**
 * Example usage demonstrating circuit breaker patterns
 */
function demonstrateCircuitBreaker(): void
{
    echo "=== Circuit Breaker Pattern Demo ===\n\n";

    // Test 1: Basic circuit breaker
    echo "1. Basic Circuit Breaker\n";
    $breaker = new CircuitBreaker(
        failureThreshold: 3,
        timeout: 2,
        successThreshold: 2
    );

    // Simulate flaky service
    $flakyService = function () {
        static $attempts = 0;
        $attempts++;

        // Fail first 5 attempts, then succeed
        if ($attempts <= 5) {
            throw new \RuntimeException("Service unavailable (attempt {$attempts})");
        }

        return "Success!";
    };

    for ($i = 1; $i <= 10; $i++) {
        try {
            usleep(200000); // 0.2s delay
            $result = $breaker->call($flakyService);
            echo "   Attempt {$i}: {$result}\n";
        } catch (CircuitBreakerException $e) {
            echo "   Attempt {$i}: ✗ {$e->getMessage()}\n";
        } catch (\Throwable $e) {
            echo "   Attempt {$i}: ✗ Service error: {$e->getMessage()}\n";
        }
    }

    echo "\n   Final state: " . $breaker->getState()->value . "\n";
    print_r($breaker->getStats());

    // Test 2: HTTP client with circuit breaker
    echo "\n2. Resilient HTTP Client\n";
    $httpBreaker = new CircuitBreaker(3, 5, 2);
    $client = new ResilientHttpClient($httpBreaker);

    // Test with real endpoint
    $testUrls = [
        'https://httpbin.org/status/200',
        'https://httpbin.org/status/500',
        'https://httpbin.org/delay/1',
    ];

    foreach ($testUrls as $url) {
        try {
            $response = $client->get($url);
            echo "   ✓ {$url}: {$response['status']}\n";
        } catch (CircuitBreakerException $e) {
            echo "   ✗ {$url}: Circuit is open\n";
        } catch (\Throwable $e) {
            echo "   ✗ {$url}: {$e->getMessage()}\n";
        }
    }

    // Test 3: Fallback pattern
    echo "\n3. Fallback Pattern\n";
    $fallbackBreaker = new CircuitBreaker(2, 3, 1);
    $fallbackClient = new ResilientHttpClient($fallbackBreaker);

    try {
        $result = $fallbackClient->getWithFallback(
            'https://httpbin.org/status/500',  // Failing primary
            'https://httpbin.org/get'           // Working fallback
        );
        echo "   Got response using fallback\n";
    } catch (\Throwable $e) {
        echo "   Both primary and fallback failed\n";
    }

    // Test 4: Multiple endpoints
    echo "\n4. Multiple Endpoint Management\n";
    $manager = new CircuitBreakerManager(3, 5);

    $endpoints = [
        'api-v1' => fn() => "API v1 response",
        'api-v2' => fn() => throw new \RuntimeException("API v2 down"),
        'cache' => fn() => "Cached data",
    ];

    foreach ($endpoints as $name => $operation) {
        for ($i = 0; $i < 5; $i++) {
            try {
                $result = $manager->call($name, $operation);
                echo "   ✓ {$name}: {$result}\n";
            } catch (CircuitBreakerException $e) {
                echo "   ✗ {$name}: Circuit is open\n";
                break; // Stop trying this endpoint
            } catch (\Throwable $e) {
                echo "   ✗ {$name}: {$e->getMessage()}\n";
            }
        }
    }

    echo "\n   Circuit states:\n";
    $stats = $manager->getStats();
    foreach ($stats as $endpoint => $stat) {
        echo "   - {$endpoint}: {$stat['state']} ({$stat['failures']} failures)\n";
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateCircuitBreaker();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
