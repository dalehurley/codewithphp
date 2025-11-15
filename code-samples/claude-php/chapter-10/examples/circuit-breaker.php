<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ErrorHandling\CircuitBreaker;
use Dotenv\Dotenv;
use GuzzleHttp\Client;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Claude API - Circuit Breaker Pattern\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Initialize circuit breaker
$breaker = new CircuitBreaker(
    name: 'claude-api',
    failureThreshold: (int) ($_ENV['CIRCUIT_BREAKER_THRESHOLD'] ?? 5),
    timeout: (int) ($_ENV['CIRCUIT_BREAKER_TIMEOUT'] ?? 60),
    resetTimeout: (int) ($_ENV['CIRCUIT_BREAKER_RESET_TIMEOUT'] ?? 300)
);

// HTTP client
$client = new Client([
    'base_uri' => 'https://api.anthropic.com',
    'timeout' => 30,
]);

/**
 * Simulated API call that may fail
 */
function simulatedAPICall(bool $shouldFail = false): array
{
    if ($shouldFail) {
        throw new Exception('API service unavailable', 503);
    }

    return [
        'success' => true,
        'message' => 'Request completed successfully',
    ];
}

// Example 1: Normal operation (circuit CLOSED)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Normal Operation (Circuit CLOSED)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}: ";

    try {
        $result = $breaker->execute(function() {
            return simulatedAPICall(shouldFail: false);
        });

        echo "✓ Success - {$result['message']}\n";
    } catch (Exception $e) {
        echo "✗ Failed - {$e->getMessage()}\n";
    }
}

echo "\nCircuit Breaker State:\n";
print_r($breaker->getStats());
echo "\n";

// Example 2: Triggering circuit breaker (multiple failures)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Multiple Failures (Opening Circuit)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

for ($i = 1; $i <= 7; $i++) {
    echo "Request {$i}: ";

    try {
        $result = $breaker->execute(function() {
            return simulatedAPICall(shouldFail: true);
        });

        echo "✓ Success\n";
    } catch (Exception $e) {
        echo "✗ Failed - {$e->getMessage()}\n";
    }

    usleep(100000); // 100ms delay
}

echo "\nCircuit Breaker State after failures:\n";
print_r($breaker->getStats());
echo "\n";

// Example 3: Requests rejected while circuit is OPEN
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Circuit OPEN (Requests Rejected)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}: ";

    if (!$breaker->isAvailable()) {
        $stats = $breaker->getStats();
        echo "⊘ Rejected - Circuit is {$stats['state']} ";
        echo "(retry in {$stats['time_until_retry']}s)\n";
        continue;
    }

    try {
        $result = $breaker->execute(function() {
            return simulatedAPICall(shouldFail: false);
        });

        echo "✓ Success\n";
    } catch (Exception $e) {
        echo "✗ Failed - {$e->getMessage()}\n";
    }
}

echo "\n";

// Example 4: Circuit breaker with manual reset
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Manual Reset and Recovery\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Resetting circuit breaker...\n";
$breaker->reset();

echo "State after reset: {$breaker->getState()}\n\n";

for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}: ";

    try {
        $result = $breaker->execute(function() {
            return simulatedAPICall(shouldFail: false);
        });

        echo "✓ Success - Circuit is functioning normally\n";
    } catch (Exception $e) {
        echo "✗ Failed - {$e->getMessage()}\n";
    }
}

echo "\nFinal Circuit Breaker State:\n";
print_r($breaker->getStats());

// Example 5: Demonstrating HALF-OPEN state
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: HALF-OPEN State (Testing Recovery)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Create new circuit breaker with short timeout for demo
$testBreaker = new CircuitBreaker(
    name: 'test-api',
    failureThreshold: 3,
    timeout: 60,
    resetTimeout: 1 // 1 second for demo
);

echo "1. Causing failures to open circuit...\n";
for ($i = 1; $i <= 3; $i++) {
    try {
        $testBreaker->execute(function() {
            throw new Exception('Service error', 503);
        });
    } catch (Exception $e) {
        echo "   ✗ Request {$i} failed\n";
    }
}

echo "\n2. Circuit is now: {$testBreaker->getState()}\n";
echo "   Waiting for reset timeout...\n";
sleep(2);

echo "\n3. Testing recovery (HALF-OPEN state):\n";
if ($testBreaker->isAvailable()) {
    echo "   ⚡ Circuit entered HALF-OPEN state\n";

    // Successful test closes circuit
    try {
        $testBreaker->execute(function() {
            return simulatedAPICall(shouldFail: false);
        });
        echo "   ✓ Test request succeeded\n";

        $testBreaker->execute(function() {
            return simulatedAPICall(shouldFail: false);
        });
        echo "   ✓ Second test succeeded - Circuit CLOSED\n";
    } catch (Exception $e) {
        echo "   ✗ Test failed - Circuit reopened\n";
    }
}

echo "\nFinal state: {$testBreaker->getState()}\n";

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Circuit Breaker Best Practices:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Use circuit breakers to prevent cascading failures\n";
echo "2. Set appropriate failure thresholds (typically 5-10)\n";
echo "3. Configure reset timeout based on service recovery time\n";
echo "4. Monitor circuit breaker state changes\n";
echo "5. Provide fallback responses when circuit is open\n";
echo "6. Log state transitions for debugging\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
