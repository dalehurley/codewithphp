<?php

declare(strict_types=1);

namespace ClaudePHP\ErrorHandling;

use Exception;

/**
 * Circuit breaker pattern implementation
 *
 * States:
 * - CLOSED: Normal operation
 * - OPEN: Too many failures, reject requests
 * - HALF_OPEN: Testing if service recovered
 */
class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';

    private string $name;
    private int $failureThreshold;
    private int $timeout;
    private int $resetTimeout;

    private string $state = self::STATE_CLOSED;
    private int $failureCount = 0;
    private int $successCount = 0;
    private ?int $lastFailureTime = null;
    private ?int $openedAt = null;

    /**
     * @param string $name Circuit breaker identifier
     * @param int $failureThreshold Number of failures before opening circuit
     * @param int $timeout Timeout in seconds for requests
     * @param int $resetTimeout Seconds to wait before attempting reset
     */
    public function __construct(
        string $name = 'default',
        int $failureThreshold = 5,
        int $timeout = 60,
        int $resetTimeout = 300
    ) {
        $this->name = $name;
        $this->failureThreshold = $failureThreshold;
        $this->timeout = $timeout;
        $this->resetTimeout = $resetTimeout;
    }

    /**
     * Check if circuit breaker allows requests
     */
    public function isAvailable(): bool
    {
        if ($this->state === self::STATE_CLOSED) {
            return true;
        }

        if ($this->state === self::STATE_OPEN) {
            // Check if we should transition to half-open
            if ($this->shouldAttemptReset()) {
                $this->state = self::STATE_HALF_OPEN;
                echo "⚡ Circuit breaker '{$this->name}' entering HALF-OPEN state\n";
                return true;
            }
            return false;
        }

        // HALF_OPEN state - allow limited requests
        return true;
    }

    /**
     * Record a successful operation
     */
    public function recordSuccess(): void
    {
        if ($this->state === self::STATE_HALF_OPEN) {
            $this->successCount++;

            // Reset circuit breaker after successful test
            if ($this->successCount >= 2) {
                $this->reset();
                echo "✓ Circuit breaker '{$this->name}' CLOSED after successful recovery\n";
            }
        } else {
            // Reset failure count on success
            $this->failureCount = 0;
        }
    }

    /**
     * Record a failed operation
     */
    public function recordFailure(): void
    {
        $this->failureCount++;
        $this->lastFailureTime = time();

        if ($this->state === self::STATE_HALF_OPEN) {
            // Failed during testing, reopen circuit
            $this->open();
            echo "✗ Circuit breaker '{$this->name}' reopened after failed test\n";
            return;
        }

        if ($this->failureCount >= $this->failureThreshold) {
            $this->open();
            echo "⚠ Circuit breaker '{$this->name}' OPENED after {$this->failureCount} failures\n";
        }
    }

    /**
     * Execute a callable with circuit breaker protection
     *
     * @throws Exception If circuit is open
     */
    public function execute(callable $callback): mixed
    {
        if (!$this->isAvailable()) {
            throw new Exception(
                "Circuit breaker '{$this->name}' is OPEN. " .
                "Will retry in " . $this->getTimeUntilRetry() . " seconds"
            );
        }

        try {
            $result = $callback();
            $this->recordSuccess();
            return $result;
        } catch (\Throwable $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    /**
     * Get current state
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Get circuit breaker statistics
     */
    public function getStats(): array
    {
        return [
            'name' => $this->name,
            'state' => $this->state,
            'failure_count' => $this->failureCount,
            'success_count' => $this->successCount,
            'failure_threshold' => $this->failureThreshold,
            'time_until_retry' => $this->getTimeUntilRetry(),
            'last_failure_time' => $this->lastFailureTime
                ? date('Y-m-d H:i:s', $this->lastFailureTime)
                : null,
        ];
    }

    /**
     * Force reset the circuit breaker
     */
    public function reset(): void
    {
        $this->state = self::STATE_CLOSED;
        $this->failureCount = 0;
        $this->successCount = 0;
        $this->lastFailureTime = null;
        $this->openedAt = null;
    }

    /**
     * Open the circuit
     */
    private function open(): void
    {
        $this->state = self::STATE_OPEN;
        $this->openedAt = time();
        $this->successCount = 0;
    }

    /**
     * Check if enough time has passed to attempt reset
     */
    private function shouldAttemptReset(): bool
    {
        if ($this->openedAt === null) {
            return false;
        }

        return (time() - $this->openedAt) >= $this->resetTimeout;
    }

    /**
     * Get seconds until retry is allowed
     */
    private function getTimeUntilRetry(): int
    {
        if ($this->state !== self::STATE_OPEN || $this->openedAt === null) {
            return 0;
        }

        $elapsed = time() - $this->openedAt;
        return max(0, $this->resetTimeout - $elapsed);
    }
}
