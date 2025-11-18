<?php

declare(strict_types=1);

namespace CodeWithPHP\ClaudeShared;

use ClaudePhp\Exceptions\RateLimitError;
use ClaudePhp\Exceptions\APIError;

/**
 * Retry helper with exponential backoff
 */
class RetryHelper
{
    /**
     * Retry a callable with exponential backoff
     *
     * @template T
     * @param callable(): T $fn
     * @return T
     */
    public static function retry(
        callable $fn,
        int $maxAttempts = 3,
        int $initialDelay = 1,
        float $multiplier = 2.0,
        bool $jitter = true
    ): mixed {
        $attempt = 0;

        while (true) {
            try {
                return $fn();
            } catch (RateLimitException $e) {
                $attempt++;

                if ($attempt >= $maxAttempts) {
                    throw $e;
                }

                $delay = $initialDelay * ($multiplier ** ($attempt - 1));

                if ($jitter) {
                    $delay += (rand(0, 1000) / 1000); // Add 0-1 second jitter
                }

                sleep((int)$delay);
            } catch (ErrorException $e) {
                // Check if error is retryable
                if (self::isRetryable($e)) {
                    $attempt++;

                    if ($attempt >= $maxAttempts) {
                        throw $e;
                    }

                    $delay = $initialDelay * ($multiplier ** ($attempt - 1));

                    if ($jitter) {
                        $delay += (rand(0, 1000) / 1000);
                    }

                    sleep((int)$delay);
                } else {
                    // Non-retryable error, throw immediately
                    throw $e;
                }
            }
        }
    }

    /**
     * Check if an error is retryable
     */
    private static function isRetryable(ErrorException $e): bool
    {
        $retryableMessages = [
            'overloaded',
            'timeout',
            'temporarily unavailable',
            'service unavailable',
        ];

        $message = strtolower($e->getMessage());

        foreach ($retryableMessages as $retryable) {
            if (str_contains($message, $retryable)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retry with custom retry logic
     *
     * @template T
     * @param callable(): T $fn
     * @param callable(\Exception, int): bool $shouldRetry
     * @return T
     */
    public static function retryWith(
        callable $fn,
        callable $shouldRetry,
        int $maxAttempts = 3,
        int $initialDelay = 1
    ): mixed {
        $attempt = 0;

        while (true) {
            try {
                return $fn();
            } catch (\Exception $e) {
                $attempt++;

                if ($attempt >= $maxAttempts || !$shouldRetry($e, $attempt)) {
                    throw $e;
                }

                sleep($initialDelay * (2 ** ($attempt - 1)));
            }
        }
    }
}
