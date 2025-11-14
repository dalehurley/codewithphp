<?php

declare(strict_types=1);

/**
 * Chapter 36: Stream Processing Algorithms - Rate Limiting
 *
 * This example demonstrates rate limiting algorithms:
 * - Token Bucket algorithm
 * - Leaky Bucket algorithm
 * - Sliding Window Counter
 * - Fixed Window Counter
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Token Bucket rate limiter
 * Allows bursts while maintaining average rate
 */
class TokenBucket
{
    private float $tokens;
    private int $capacity;
    private int $refillRate;  // Tokens per second
    private float $lastRefill;

    /**
     * @param int $capacity Maximum tokens
     * @param int $refillRate Tokens added per second
     */
    public function __construct(int $capacity, int $refillRate)
    {
        $this->capacity = $capacity;
        $this->refillRate = $refillRate;
        $this->tokens = $capacity;
        $this->lastRefill = microtime(true);
    }

    /**
     * Refill tokens based on time elapsed
     */
    private function refill(): void
    {
        $now = microtime(true);
        $elapsed = $now - $this->lastRefill;

        $this->tokens = min(
            $this->capacity,
            $this->tokens + ($elapsed * $this->refillRate)
        );

        $this->lastRefill = $now;
    }

    /**
     * Attempt to consume tokens
     *
     * @param int $tokens Number of tokens to consume
     * @return bool True if tokens were consumed
     */
    public function consume(int $tokens = 1): bool
    {
        $this->refill();

        if ($this->tokens >= $tokens) {
            $this->tokens -= $tokens;
            return true;
        }

        return false;
    }

    /**
     * Get available tokens
     *
     * @return float Available tokens
     */
    public function getAvailableTokens(): float
    {
        $this->refill();
        return $this->tokens;
    }

    /**
     * Get time until next token
     *
     * @return float Seconds until next token
     */
    public function getTimeUntilToken(): float
    {
        $this->refill();

        if ($this->tokens >= 1) {
            return 0;
        }

        return (1 - $this->tokens) / $this->refillRate;
    }
}

/**
 * Leaky Bucket rate limiter
 * Smooths out bursts by processing at constant rate
 */
class LeakyBucket
{
    private array $queue = [];
    private int $capacity;
    private int $leakRate;  // Items per second
    private float $lastLeak;

    /**
     * @param int $capacity Maximum queue size
     * @param int $leakRate Items processed per second
     */
    public function __construct(int $capacity, int $leakRate)
    {
        $this->capacity = $capacity;
        $this->leakRate = $leakRate;
        $this->lastLeak = microtime(true);
    }

    /**
     * Process (leak) items from the queue
     */
    private function leak(): void
    {
        $now = microtime(true);
        $elapsed = $now - $this->lastLeak;
        $itemsToLeak = (int)floor($elapsed * $this->leakRate);

        for ($i = 0; $i < $itemsToLeak && !empty($this->queue); $i++) {
            array_shift($this->queue);
        }

        if ($itemsToLeak > 0) {
            $this->lastLeak = $now;
        }
    }

    /**
     * Add item to bucket
     *
     * @param mixed $item Item to add
     * @return bool True if item was added
     */
    public function add(mixed $item): bool
    {
        $this->leak();

        if (count($this->queue) < $this->capacity) {
            $this->queue[] = $item;
            return true;
        }

        return false;  // Bucket full
    }

    /**
     * Get current queue size
     *
     * @return int Queue size
     */
    public function getSize(): int
    {
        $this->leak();
        return count($this->queue);
    }
}

/**
 * Sliding Window Counter rate limiter
 * More accurate than fixed window, prevents edge case bursts
 */
class SlidingWindowCounter
{
    private array $buckets = [];
    private int $windowSize;
    private int $bucketSize;
    private int $maxRequests;

    /**
     * @param int $windowSeconds Window duration
     * @param int $maxRequests Maximum requests in window
     * @param int $bucketCount Number of sub-buckets
     */
    public function __construct(
        int $windowSeconds,
        int $maxRequests,
        int $bucketCount = 10
    ) {
        $this->windowSize = $windowSeconds;
        $this->maxRequests = $maxRequests;
        $this->bucketSize = (int)ceil($windowSeconds / $bucketCount);
    }

    /**
     * Get bucket key for timestamp
     *
     * @param int|null $timestamp Timestamp
     * @return int Bucket key
     */
    private function getBucketKey(?int $timestamp = null): int
    {
        $timestamp = $timestamp ?? time();
        return (int)floor($timestamp / $this->bucketSize);
    }

    /**
     * Remove old buckets
     *
     * @param int $currentBucket Current bucket key
     */
    private function cleanup(int $currentBucket): void
    {
        $oldestAllowed = $currentBucket - ceil($this->windowSize / $this->bucketSize);

        foreach ($this->buckets as $bucket => $count) {
            if ($bucket < $oldestAllowed) {
                unset($this->buckets[$bucket]);
            }
        }
    }

    /**
     * Check if request is allowed
     *
     * @param int|null $timestamp Request timestamp
     * @return bool True if request is allowed
     */
    public function allowRequest(?int $timestamp = null): bool
    {
        $timestamp = $timestamp ?? time();
        $bucket = $this->getBucketKey($timestamp);

        $this->cleanup($bucket);

        // Count requests in window
        $total = array_sum($this->buckets);

        if ($total < $this->maxRequests) {
            if (!isset($this->buckets[$bucket])) {
                $this->buckets[$bucket] = 0;
            }

            $this->buckets[$bucket]++;
            return true;
        }

        return false;
    }

    /**
     * Get current request count
     *
     * @param int|null $timestamp Reference timestamp
     * @return int Request count
     */
    public function getRequestCount(?int $timestamp = null): int
    {
        $timestamp = $timestamp ?? time();
        $bucket = $this->getBucketKey($timestamp);
        $this->cleanup($bucket);

        return array_sum($this->buckets);
    }
}

/**
 * Fixed Window Counter (simpler but has edge case issues)
 */
class FixedWindowCounter
{
    private array $windows = [];
    private int $windowSeconds;
    private int $maxRequests;

    /**
     * @param int $windowSeconds Window duration
     * @param int $maxRequests Maximum requests per window
     */
    public function __construct(int $windowSeconds, int $maxRequests)
    {
        $this->windowSeconds = $windowSeconds;
        $this->maxRequests = $maxRequests;
    }

    /**
     * Get current window key
     *
     * @param int|null $timestamp Timestamp
     * @return int Window key
     */
    private function getWindowKey(?int $timestamp = null): int
    {
        $timestamp = $timestamp ?? time();
        return (int)floor($timestamp / $this->windowSeconds);
    }

    /**
     * Check if request is allowed
     *
     * @param int|null $timestamp Request timestamp
     * @return bool True if request is allowed
     */
    public function allowRequest(?int $timestamp = null): bool
    {
        $window = $this->getWindowKey($timestamp);

        if (!isset($this->windows[$window])) {
            $this->windows[$window] = 0;
        }

        if ($this->windows[$window] < $this->maxRequests) {
            $this->windows[$window]++;

            // Cleanup old windows
            foreach ($this->windows as $key => $count) {
                if ($key < $window - 1) {
                    unset($this->windows[$key]);
                }
            }

            return true;
        }

        return false;
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Rate Limiting Demo ===\n\n";

// Example 1: Token Bucket
echo "1. Token Bucket Algorithm:\n";
$bucket = new TokenBucket(10, 2);  // 10 tokens, refill 2/second

echo "Bucket: 10 capacity, refill 2 tokens/second\n";
echo "Attempting 15 requests immediately:\n\n";

for ($i = 1; $i <= 15; $i++) {
    $allowed = $bucket->consume();
    $available = $bucket->getAvailableTokens();

    printf(
        "Request %2d: %s (Available: %.1f tokens)\n",
        $i,
        $allowed ? "✓ Allowed " : "✗ Limited",
        $available
    );
}

echo "\nWaiting 3 seconds for token refill...\n";
sleep(3);

echo "After 3s (should have ~6 tokens):\n";
printf("Available tokens: %.1f\n", $bucket->getAvailableTokens());

// Example 2: Leaky Bucket
echo "\n2. Leaky Bucket Algorithm:\n";
$leakyBucket = new LeakyBucket(5, 1);  // Capacity 5, leak 1/second

echo "Bucket: 5 capacity, leak 1 item/second\n";
echo "Adding 10 requests:\n\n";

for ($i = 1; $i <= 10; $i++) {
    $added = $leakyBucket->add("request_$i");
    $size = $leakyBucket->getSize();

    printf(
        "Request %2d: %s (Queue size: %d)\n",
        $i,
        $added ? "✓ Queued " : "✗ Dropped",
        $size
    );
}

echo "\nWaiting 3 seconds for leak...\n";
sleep(3);

echo "After 3s:\n";
printf("Queue size: %d (3 items should have leaked)\n", $leakyBucket->getSize());

// Example 3: Sliding Window Counter
echo "\n3. Sliding Window Counter:\n";
$slidingWindow = new SlidingWindowCounter(60, 100);  // 100 requests per 60s

echo "Limit: 100 requests per 60 seconds\n";
echo "Attempting 120 requests:\n\n";

$allowed = $denied = 0;
for ($i = 1; $i <= 120; $i++) {
    if ($slidingWindow->allowRequest()) {
        $allowed++;
    } else {
        $denied++;
    }

    if ($i % 20 == 0) {
        printf(
            "After %3d requests: %3d allowed, %2d denied (Current count: %d)\n",
            $i,
            $allowed,
            $denied,
            $slidingWindow->getRequestCount()
        );
    }
}

// Example 4: Fixed Window Counter (showing edge case)
echo "\n4. Fixed Window Counter (Edge Case Demo):\n";
$fixedWindow = new FixedWindowCounter(10, 10);  // 10 requests per 10s

echo "Limit: 10 requests per 10-second window\n";
echo "Demonstrating edge case issue:\n\n";

// Simulate time at end of window
$windowEnd = (floor(time() / 10) + 1) * 10 - 1;  // Last second of window

echo "Sending 10 requests at end of window:\n";
for ($i = 1; $i <= 10; $i++) {
    $allowed = $fixedWindow->allowRequest($windowEnd);
    echo "  Request $i: " . ($allowed ? "✓" : "✗") . "\n";
}

echo "\nSending 10 requests at start of next window:\n";
$nextWindowStart = $windowEnd + 1;
for ($i = 1; $i <= 10; $i++) {
    $allowed = $fixedWindow->allowRequest($nextWindowStart);
    echo "  Request $i: " . ($allowed ? "✓" : "✗") . "\n";
}

echo "\nNote: 20 requests in ~1 second (2x limit) - edge case problem!\n";

// Example 5: Real-world API rate limiter
echo "\n5. Real-World Example - API Rate Limiter:\n";

class ApiRateLimiter
{
    private array $limiters = [];

    public function __construct(
        private int $perSecondLimit = 10,
        private int $perMinuteLimit = 100,
        private int $perHourLimit = 1000
    ) {}

    public function checkLimit(string $apiKey): array
    {
        if (!isset($this->limiters[$apiKey])) {
            $this->limiters[$apiKey] = [
                'second' => new TokenBucket($this->perSecondLimit, $this->perSecondLimit),
                'minute' => new SlidingWindowCounter(60, $this->perMinuteLimit),
                'hour' => new SlidingWindowCounter(3600, $this->perHourLimit),
            ];
        }

        $limiters = $this->limiters[$apiKey];

        // Check all limits
        $secondOk = $limiters['second']->consume();
        $minuteOk = $limiters['minute']->allowRequest();
        $hourOk = $limiters['hour']->allowRequest();

        return [
            'allowed' => $secondOk && $minuteOk && $hourOk,
            'limits' => [
                'per_second' => [
                    'limit' => $this->perSecondLimit,
                    'remaining' => (int)$limiters['second']->getAvailableTokens(),
                    'ok' => $secondOk
                ],
                'per_minute' => [
                    'limit' => $this->perMinuteLimit,
                    'remaining' => $this->perMinuteLimit -
                                  $limiters['minute']->getRequestCount(),
                    'ok' => $minuteOk
                ],
                'per_hour' => [
                    'limit' => $this->perHourLimit,
                    'remaining' => $this->perHourLimit -
                                  $limiters['hour']->getRequestCount(),
                    'ok' => $hourOk
                ]
            ]
        ];
    }
}

$apiLimiter = new ApiRateLimiter(5, 50, 500);

echo "API limits: 5/second, 50/minute, 500/hour\n";
echo "Testing with API key 'test-key':\n\n";

for ($i = 1; $i <= 10; $i++) {
    $result = $apiLimiter->checkLimit('test-key');

    printf("Request %2d: %s\n", $i, $result['allowed'] ? "✓ Allowed" : "✗ Limited");

    if ($i == 5 || $i == 10) {
        echo "  Remaining limits:\n";
        foreach ($result['limits'] as $period => $info) {
            printf(
                "    %s: %d/%d\n",
                str_replace('_', ' ', $period),
                $info['remaining'],
                $info['limit']
            );
        }
        echo "\n";
    }
}

// Example 6: Performance comparison
echo "6. Performance Comparison:\n";

$iterations = 10000;

// Token Bucket
$start = microtime(true);
$tb = new TokenBucket(1000, 100);
for ($i = 0; $i < $iterations; $i++) {
    $tb->consume();
}
$tbTime = microtime(true) - $start;

// Sliding Window
$start = microtime(true);
$sw = new SlidingWindowCounter(60, 1000);
for ($i = 0; $i < $iterations; $i++) {
    $sw->allowRequest();
}
$swTime = microtime(true) - $start;

printf("\n%d operations:\n", $iterations);
printf("  Token Bucket:         %.3f ms\n", $tbTime * 1000);
printf("  Sliding Window:       %.3f ms\n", $swTime * 1000);

echo "\n=== Demo Complete ===\n";
