# Chapter 36: Stream Processing Algorithms - Code Samples

This directory contains comprehensive, runnable PHP code examples for Chapter 36 of the PHP Algorithms series, focusing on real-time stream processing algorithms.

## Overview

Stream processing algorithms handle continuous, potentially infinite data streams where the entire dataset cannot fit in memory. These algorithms are essential for real-time analytics, monitoring systems, and big data processing.

## Code Samples

### 1. Sliding Windows (`01-sliding-windows.php`)

**Purpose**: Implements sliding window algorithms for tracking recent data efficiently.

**Key Concepts**:
- Fixed-size sliding windows with aggregations (sum, average, min, max)
- Time-based sliding windows for temporal data
- Optimized O(1) min/max tracking using deques
- Traffic spike detection
- Response time monitoring

**Classes**:
- `SlidingWindow`: Basic fixed-size window with O(n) min/max
- `TimeBasedWindow`: Time-based window for event streams
- `OptimizedSlidingWindow`: O(1) min/max using deque data structure
- `ResponseTimeMonitor`: Real-world monitoring example
- `TrafficSpikeDetector`: Anomaly detection system

**Use Cases**:
- Real-time metrics and monitoring
- Performance tracking (response times, throughput)
- Anomaly and spike detection
- Moving averages for analytics
- Recent activity tracking

**Time Complexity**:
- Add: O(1) for basic operations
- Min/Max: O(1) with optimized window, O(n) with basic window
- Average/Sum: O(1)

**Space Complexity**: O(n) where n is window size

**Run**:
```bash
php 01-sliding-windows.php
```

---

### 2. Rate Limiting (`02-rate-limiting.php`)

**Purpose**: Implements various rate limiting algorithms to control request rates.

**Key Concepts**:
- Token Bucket (allows bursts, maintains average rate)
- Leaky Bucket (smooths bursts, constant processing rate)
- Sliding Window Counter (accurate, prevents edge cases)
- Fixed Window Counter (simple but has edge case issues)
- Multi-tier rate limiting (per-second, per-minute, per-hour)

**Classes**:
- `TokenBucket`: Token-based rate limiter with burst support
- `LeakyBucket`: Queue-based rate limiter for smoothing
- `SlidingWindowCounter`: Accurate sliding window implementation
- `FixedWindowCounter`: Simple fixed window approach
- `ApiRateLimiter`: Complete API rate limiting system

**Use Cases**:
- API rate limiting
- Request throttling
- DDoS protection
- Resource usage control
- Cost management for metered services

**Algorithm Comparison**:

| Algorithm | Burst Handling | Accuracy | Memory | Best For |
|-----------|---------------|----------|--------|----------|
| Token Bucket | ✓ Allows | Good | Low | APIs, general use |
| Leaky Bucket | ✗ Smooths | Good | Medium | Traffic shaping |
| Sliding Window | ✗ Prevents | High | Medium | Precise limits |
| Fixed Window | ✗ Edge cases | Low | Low | Simple cases |

**Run**:
```bash
php 02-rate-limiting.php
```

---

## Running All Examples

To run all examples in sequence:

```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo "---"
done
```

## Requirements

- PHP 8.0 or higher
- No external dependencies required
- Uses SPL data structures (SplDoublyLinkedList)

## Key Algorithms Summary

### Sliding Windows

**When to Use**:
- Need recent data aggregation
- Real-time metrics and monitoring
- Anomaly detection
- Time-series analysis

**Variants**:
1. **Count-based**: Fixed number of elements
2. **Time-based**: Fixed time duration
3. **Session-based**: Timeout-based grouping

### Rate Limiting

**When to Use**:
- Protect against abuse
- Enforce usage quotas
- Manage resource consumption
- Control costs

**Algorithm Selection**:
- **Token Bucket**: Best for most APIs (allows bursts)
- **Leaky Bucket**: Traffic shaping, constant output rate
- **Sliding Window**: Most accurate, no edge cases
- **Fixed Window**: Simplest, acceptable for internal use

## Real-World Applications

### 1. API Management
```php
// Rate limit by API key
$limiter = new ApiRateLimiter(100, 1000, 10000);
$result = $limiter->checkLimit($apiKey);

if (!$result['allowed']) {
    http_response_code(429); // Too Many Requests
    header('Retry-After: 60');
    die('Rate limit exceeded');
}
```

### 2. Metrics and Monitoring
```php
// Track response times
$monitor = new ResponseTimeMonitor(100);
$monitor->recordResponse($responseTime);

if (!$monitor->isHealthy()) {
    alert('Service degradation detected');
}
```

### 3. Anomaly Detection
```php
// Detect traffic spikes
$detector = new TrafficSpikeDetector(300, 60, 2.0);
$detector->recordRequest($request);

if ($spike = $detector->detectSpike()) {
    alert("Traffic spike: {$spike['multiplier']}x normal");
}
```

### 4. System Protection
```php
// Protect database from overload
$rateLimiter = new TokenBucket(1000, 100);

if ($rateLimiter->consume()) {
    executeQuery($sql);
} else {
    queueForLater($query);
}
```

## Performance Characteristics

### Memory Usage

| Algorithm | Memory per User | Total Memory |
|-----------|----------------|--------------|
| Token Bucket | O(1) | O(users) |
| Leaky Bucket | O(queue size) | O(users × queue) |
| Sliding Window | O(buckets) | O(users × buckets) |
| Time Window | O(events in window) | O(total events) |

### Throughput

Based on 10,000 operations:
- Token Bucket: ~0.5ms total (~0.05μs per op)
- Sliding Window: ~2ms total (~0.2μs per op)
- Basic Window: ~5ms total (~0.5μs per op)

All algorithms are suitable for high-traffic applications.

## Best Practices

### 1. Choose the Right Algorithm

```php
// For APIs: Token Bucket (allows bursts)
$apiLimiter = new TokenBucket(100, 10);

// For traffic shaping: Leaky Bucket (constant rate)
$trafficShaper = new LeakyBucket(50, 5);

// For precise limits: Sliding Window (no edge cases)
$preciseLimiter = new SlidingWindowCounter(60, 100);
```

### 2. Use Multiple Limits

```php
// Protect at multiple time scales
$limiter = new ApiRateLimiter(
    perSecondLimit: 10,   // Prevent bursts
    perMinuteLimit: 100,  // Short-term limit
    perHourLimit: 1000    // Long-term quota
);
```

### 3. Return Useful Headers

```php
if (!$allowed) {
    header('X-RateLimit-Limit: 100');
    header('X-RateLimit-Remaining: 0');
    header('X-RateLimit-Reset: ' . ($resetTime));
    header('Retry-After: 60');
    http_response_code(429);
}
```

### 4. Log Rate Limit Events

```php
if (!$allowed) {
    error_log(sprintf(
        'Rate limit exceeded for %s: %d requests in window',
        $identifier,
        $currentCount
    ));
}
```

## Common Pitfalls

### 1. Clock Skew

```php
// ❌ Don't use server time directly for distributed systems
$timestamp = time();

// ✓ Use synchronized time source
$timestamp = $timeSync->getCurrentTime();
```

### 2. Missing Cleanup

```php
// ❌ Memory leak - old data never removed
$this->windows[$userId][] = $timestamp;

// ✓ Regular cleanup
$this->cleanup($currentTime);
```

### 3. Race Conditions

```php
// ❌ Not atomic in concurrent environments
if ($count < $limit) {
    $count++;
    return true;
}

// ✓ Use atomic operations or locks
$allowed = $redis->incr($key) <= $limit;
```

### 4. Edge Cases in Fixed Windows

```php
// ❌ Fixed Window allows 2x rate at boundaries
// 10 requests at 23:59:59
// 10 requests at 00:00:00
// = 20 requests in 1 second!

// ✓ Use Sliding Window to prevent this
$limiter = new SlidingWindowCounter(60, 100);
```

## Testing

### Unit Tests
```php
// Test rate limiter
$limiter = new TokenBucket(10, 1);

// Should allow 10 requests immediately
for ($i = 0; $i < 10; $i++) {
    assert($limiter->consume() === true);
}

// 11th should fail
assert($limiter->consume() === false);
```

### Load Tests
```php
// Measure performance under load
$start = microtime(true);
for ($i = 0; $i < 100000; $i++) {
    $limiter->consume();
}
$elapsed = microtime(true) - $start;

assert($elapsed < 1.0, 'Performance regression');
```

## Monitoring

Track these metrics in production:
- Rate limit hit rate (% of requests limited)
- Average tokens available
- Window size and fill rate
- Response times for rate check
- Memory usage per user

## Further Reading

- [Token Bucket Algorithm](https://en.wikipedia.org/wiki/Token_bucket)
- [Leaky Bucket Algorithm](https://en.wikipedia.org/wiki/Leaky_bucket)
- [Rate Limiting Strategies](https://cloud.google.com/architecture/rate-limiting-strategies-techniques)
- [Stream Processing Patterns](https://www.oreilly.com/library/view/streaming-systems/9781491983867/)

## License

MIT License - Free to use for learning and commercial purposes.

---

**Part of the PHP Algorithms Series**
Chapter 36: Stream Processing Algorithms

💡 **Pro Tip**: For distributed systems, consider using Redis or other shared storage for rate limiting across multiple servers. These examples work great for single-server applications or as building blocks for distributed systems.
