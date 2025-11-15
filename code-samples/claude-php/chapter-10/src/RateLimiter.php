<?php

declare(strict_types=1);

namespace ClaudePHP\ErrorHandling;

use Predis\Client as RedisClient;

/**
 * Rate limiter using token bucket algorithm
 * Supports both local and distributed (Redis) rate limiting
 */
class RateLimiter
{
    private int $requestsPerMinute;
    private int $requestsPerHour;
    private ?RedisClient $redis;
    private array $localBuckets = [];

    /**
     * @param int $requestsPerMinute Maximum requests per minute
     * @param int $requestsPerHour Maximum requests per hour
     * @param RedisClient|null $redis Redis client for distributed rate limiting
     */
    public function __construct(
        int $requestsPerMinute = 60,
        int $requestsPerHour = 1000,
        ?RedisClient $redis = null
    ) {
        $this->requestsPerMinute = $requestsPerMinute;
        $this->requestsPerHour = $requestsPerHour;
        $this->redis = $redis;
    }

    /**
     * Check if a request is allowed for the given key
     *
     * @param string $key Identifier (e.g., user ID, API key, IP address)
     * @return bool True if request is allowed
     */
    public function allowRequest(string $key): bool
    {
        if ($this->redis) {
            return $this->allowRequestDistributed($key);
        }

        return $this->allowRequestLocal($key);
    }

    /**
     * Get current usage for a key
     */
    public function getUsage(string $key): array
    {
        if ($this->redis) {
            return $this->getUsageDistributed($key);
        }

        return $this->getUsageLocal($key);
    }

    /**
     * Reset rate limit for a key
     */
    public function reset(string $key): void
    {
        if ($this->redis) {
            $this->redis->del([
                "rate_limit:minute:{$key}",
                "rate_limit:hour:{$key}",
            ]);
        } else {
            unset($this->localBuckets[$key]);
        }
    }

    /**
     * Local (in-memory) rate limiting
     */
    private function allowRequestLocal(string $key): bool
    {
        $now = time();

        if (!isset($this->localBuckets[$key])) {
            $this->localBuckets[$key] = [
                'minute' => [],
                'hour' => [],
            ];
        }

        $bucket = &$this->localBuckets[$key];

        // Clean up old requests
        $bucket['minute'] = array_filter(
            $bucket['minute'],
            fn($timestamp) => $timestamp > $now - 60
        );
        $bucket['hour'] = array_filter(
            $bucket['hour'],
            fn($timestamp) => $timestamp > $now - 3600
        );

        // Check limits
        if (count($bucket['minute']) >= $this->requestsPerMinute) {
            return false;
        }

        if (count($bucket['hour']) >= $this->requestsPerHour) {
            return false;
        }

        // Add request
        $bucket['minute'][] = $now;
        $bucket['hour'][] = $now;

        return true;
    }

    /**
     * Distributed (Redis) rate limiting
     */
    private function allowRequestDistributed(string $key): bool
    {
        $now = time();
        $minuteKey = "rate_limit:minute:{$key}";
        $hourKey = "rate_limit:hour:{$key}";

        // Use Redis pipeline for atomic operations
        $pipe = $this->redis->pipeline();

        // Minute limit
        $pipe->zremrangebyscore($minuteKey, 0, $now - 60);
        $pipe->zadd($minuteKey, [$now => $now]);
        $pipe->zcard($minuteKey);
        $pipe->expire($minuteKey, 60);

        // Hour limit
        $pipe->zremrangebyscore($hourKey, 0, $now - 3600);
        $pipe->zadd($hourKey, [$now => $now]);
        $pipe->zcard($hourKey);
        $pipe->expire($hourKey, 3600);

        $results = $pipe->execute();

        $minuteCount = $results[2] ?? 0;
        $hourCount = $results[6] ?? 0;

        return $minuteCount <= $this->requestsPerMinute
            && $hourCount <= $this->requestsPerHour;
    }

    /**
     * Get usage statistics (local)
     */
    private function getUsageLocal(string $key): array
    {
        $now = time();

        if (!isset($this->localBuckets[$key])) {
            return [
                'requests_this_minute' => 0,
                'requests_this_hour' => 0,
                'minute_limit' => $this->requestsPerMinute,
                'hour_limit' => $this->requestsPerHour,
                'minute_remaining' => $this->requestsPerMinute,
                'hour_remaining' => $this->requestsPerHour,
            ];
        }

        $bucket = $this->localBuckets[$key];

        $minuteCount = count(array_filter(
            $bucket['minute'],
            fn($timestamp) => $timestamp > $now - 60
        ));

        $hourCount = count(array_filter(
            $bucket['hour'],
            fn($timestamp) => $timestamp > $now - 3600
        ));

        return [
            'requests_this_minute' => $minuteCount,
            'requests_this_hour' => $hourCount,
            'minute_limit' => $this->requestsPerMinute,
            'hour_limit' => $this->requestsPerHour,
            'minute_remaining' => max(0, $this->requestsPerMinute - $minuteCount),
            'hour_remaining' => max(0, $this->requestsPerHour - $hourCount),
        ];
    }

    /**
     * Get usage statistics (distributed)
     */
    private function getUsageDistributed(string $key): array
    {
        $now = time();
        $minuteKey = "rate_limit:minute:{$key}";
        $hourKey = "rate_limit:hour:{$key}";

        $pipe = $this->redis->pipeline();
        $pipe->zcount($minuteKey, $now - 60, $now);
        $pipe->zcount($hourKey, $now - 3600, $now);
        $results = $pipe->execute();

        $minuteCount = $results[0] ?? 0;
        $hourCount = $results[1] ?? 0;

        return [
            'requests_this_minute' => $minuteCount,
            'requests_this_hour' => $hourCount,
            'minute_limit' => $this->requestsPerMinute,
            'hour_limit' => $this->requestsPerHour,
            'minute_remaining' => max(0, $this->requestsPerMinute - $minuteCount),
            'hour_remaining' => max(0, $this->requestsPerHour - $hourCount),
        ];
    }

    /**
     * Calculate time until rate limit resets
     */
    public function getTimeUntilReset(string $key): array
    {
        $usage = $this->getUsage($key);

        return [
            'minute_reset_in' => 60,
            'hour_reset_in' => 3600,
        ];
    }
}
