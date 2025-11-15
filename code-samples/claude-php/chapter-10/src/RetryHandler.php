<?php

declare(strict_types=1);

namespace ClaudePHP\ErrorHandling;

use Exception;
use Throwable;

/**
 * Retry handler with exponential backoff and jitter
 */
class RetryHandler
{
    private int $maxAttempts;
    private int $initialDelay;
    private int $maxDelay;
    private float $multiplier;
    private bool $useJitter;

    /**
     * @param int $maxAttempts Maximum number of retry attempts
     * @param int $initialDelay Initial delay in milliseconds
     * @param int $maxDelay Maximum delay in milliseconds
     * @param float $multiplier Backoff multiplier
     * @param bool $useJitter Add randomization to prevent thundering herd
     */
    public function __construct(
        int $maxAttempts = 3,
        int $initialDelay = 1000,
        int $maxDelay = 30000,
        float $multiplier = 2.0,
        bool $useJitter = true
    ) {
        $this->maxAttempts = $maxAttempts;
        $this->initialDelay = $initialDelay;
        $this->maxDelay = $maxDelay;
        $this->multiplier = $multiplier;
        $this->useJitter = $useJitter;
    }

    /**
     * Execute a callable with retry logic
     *
     * @param callable $callback Function to execute
     * @param callable|null $shouldRetry Custom retry condition (optional)
     * @return mixed Result of the callback
     * @throws Throwable If all retries fail
     */
    public function execute(callable $callback, ?callable $shouldRetry = null): mixed
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxAttempts) {
            try {
                $result = $callback();

                // Success
                if ($attempt > 0) {
                    echo "✓ Succeeded on attempt " . ($attempt + 1) . "\n";
                }

                return $result;
            } catch (Throwable $e) {
                $lastException = $e;
                $attempt++;

                // Check if we should retry this exception
                if ($shouldRetry && !$shouldRetry($e, $attempt)) {
                    throw $e;
                }

                // Check if this is a retryable error
                if (!$this->isRetryable($e)) {
                    throw $e;
                }

                // Don't sleep on the last attempt
                if ($attempt >= $this->maxAttempts) {
                    break;
                }

                $delay = $this->calculateDelay($attempt);
                echo "✗ Attempt {$attempt} failed: {$e->getMessage()}\n";
                echo "  Retrying in {$delay}ms...\n";

                usleep($delay * 1000); // Convert to microseconds
            }
        }

        // All retries exhausted
        throw new Exception(
            "Failed after {$this->maxAttempts} attempts. Last error: " .
            $lastException?->getMessage(),
            0,
            $lastException
        );
    }

    /**
     * Calculate delay with exponential backoff and optional jitter
     */
    private function calculateDelay(int $attempt): int
    {
        $exponentialDelay = min(
            $this->initialDelay * pow($this->multiplier, $attempt - 1),
            $this->maxDelay
        );

        if (!$this->useJitter) {
            return (int) $exponentialDelay;
        }

        // Add jitter: random value between 0 and exponentialDelay
        return (int) (mt_rand(0, 1000) / 1000 * $exponentialDelay);
    }

    /**
     * Determine if an exception is retryable
     */
    private function isRetryable(Throwable $e): bool
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        // Retryable HTTP status codes
        $retryableCodes = [408, 429, 500, 502, 503, 504];
        if (in_array($code, $retryableCodes)) {
            return true;
        }

        // Retryable error messages
        $retryableMessages = [
            'timeout',
            'connection',
            'rate limit',
            'overloaded',
            'temporarily unavailable',
            'too many requests',
        ];

        foreach ($retryableMessages as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get retry statistics
     */
    public function getStats(): array
    {
        return [
            'max_attempts' => $this->maxAttempts,
            'initial_delay' => $this->initialDelay,
            'max_delay' => $this->maxDelay,
            'multiplier' => $this->multiplier,
            'use_jitter' => $this->useJitter,
        ];
    }
}
