<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ErrorHandling\RetryHandler;
use Dotenv\Dotenv;
use GuzzleHttp\ClaudePhp;
use GuzzleHttp\Exception\GuzzleException;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Claude API - Retry Logic Example\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Initialize retry handler
$retry = new RetryHandler(
    maxAttempts: (int) ($_ENV['RETRY_MAX_ATTEMPTS'] ?? 3),
    initialDelay: (int) ($_ENV['RETRY_INITIAL_DELAY'] ?? 1000),
    maxDelay: (int) ($_ENV['RETRY_MAX_DELAY'] ?? 30000),
    multiplier: (float) ($_ENV['RETRY_MULTIPLIER'] ?? 2),
    useJitter: true
);

echo "Retry Configuration:\n";
print_r($retry->getStats());
echo "\n";

// HTTP client
$client = new ClaudePhp([
    'base_uri' => 'https://api.anthropic.com',
    'timeout' => 30,
]);

/**
 * Make Claude API request
 */
function makeClaudeRequest(ClaudePhp $client, string $prompt): array
{
    $apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? '';

    if (empty($apiKey)) {
        throw new Exception('ANTHROPIC_API_KEY not set');
    }

    $response = $client->post('/v1/messages', [
        'headers' => [
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ],
        'json' => [
            'model' => $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-5',
            'max_tokens' => (int) ($_ENV['ANTHROPIC_MAX_TOKENS'] ?? 1024),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ],
    ]);

    return json_decode($response->getBody()->getContents(), true);
}

// Example 1: Basic retry with automatic error detection
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic Retry Logic\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $result = $retry->execute(function() use ($client) {
        echo "Making API request...\n";
        return makeClaudeRequest($client, 'Say hello in 5 words');
    });

    echo "\n✓ Success!\n";
    echo "Response: " . $result['content'][0]['text'] . "\n";
    echo "Usage: {$result['usage']['input_tokens']} input + {$result['usage']['output_tokens']} output tokens\n";
} catch (Exception $e) {
    echo "\n✗ Failed: {$e->getMessage()}\n";
}

// Example 2: Retry with custom retry condition
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Custom Retry Condition\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $result = $retry->execute(
        callback: function() use ($client) {
            echo "Making API request with custom retry logic...\n";
            return makeClaudeRequest($client, 'What is 2+2?');
        },
        shouldRetry: function(\Throwable $e, int $attempt): bool {
            // Only retry on specific conditions
            echo "  Custom retry check (attempt {$attempt})\n";

            // Don't retry authentication errors
            if (str_contains($e->getMessage(), '401') ||
                str_contains($e->getMessage(), '403')) {
                echo "  → Not retrying authentication error\n";
                return false;
            }

            // Retry rate limits and server errors
            if (str_contains($e->getMessage(), '429') ||
                str_contains($e->getMessage(), '5')) {
                echo "  → Retrying (retryable error)\n";
                return true;
            }

            return false;
        }
    );

    echo "\n✓ Success!\n";
    echo "Response: " . $result['content'][0]['text'] . "\n";
} catch (Exception $e) {
    echo "\n✗ Failed: {$e->getMessage()}\n";
}

// Example 3: Simulating failures
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Simulating Transient Failures\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$attemptCount = 0;

try {
    $result = $retry->execute(function() use (&$attemptCount, $client) {
        $attemptCount++;
        echo "Simulated attempt {$attemptCount}...\n";

        // Fail first 2 attempts
        if ($attemptCount < 3) {
            throw new Exception('Simulated timeout error', 503);
        }

        // Succeed on 3rd attempt
        return makeClaudeRequest($client, 'Name 3 programming languages');
    });

    echo "\n✓ Success after {$attemptCount} attempts!\n";
    echo "Response: " . $result['content'][0]['text'] . "\n";
} catch (Exception $e) {
    echo "\n✗ Failed after {$attemptCount} attempts: {$e->getMessage()}\n";
}

// Example 4: Non-retryable error
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Non-Retryable Error (Immediate Failure)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $result = $retry->execute(function() {
        echo "Attempting operation that will fail immediately...\n";
        // Simulate authentication error (non-retryable)
        throw new Exception('Invalid API key', 401);
    });
} catch (Exception $e) {
    echo "✗ Failed immediately (as expected): {$e->getMessage()}\n";
    echo "  This error type is not retryable\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Best Practices:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Use exponential backoff with jitter to avoid thundering herd\n";
echo "2. Classify errors: retry transient failures, fail fast on permanent errors\n";
echo "3. Set reasonable max retry attempts (typically 3-5)\n";
echo "4. Log all retry attempts for debugging\n";
echo "5. Consider circuit breaker for repeated failures\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
