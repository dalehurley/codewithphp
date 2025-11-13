---
title: "Stream Processing Algorithms"
description: "Real-time stream processing algorithms for analytics, monitoring, and rate limiting including sliding windows and aggregation techniques"
series: "php-algorithms"
chapter: 36
order: 36
difficulty: "advanced"
prerequisites: ["Data Structures", "Sliding Windows", "Real-time Systems"]
---

# Chapter 36: Stream Processing Algorithms

## Introduction

Stream processing algorithms handle continuous, potentially infinite data streams where the entire dataset cannot fit in memory. These algorithms are essential for real-time analytics, monitoring systems, and big data processing.

## Characteristics of Stream Processing

**Challenges**:
- Data arrives continuously
- Cannot store entire stream in memory
- Need real-time or near-real-time results
- Data may arrive out of order
- Limited processing time per element

**Requirements**:
- O(1) or O(log n) space complexity
- O(1) time per element
- Approximate results often acceptable
- Windowing for temporal patterns

## Sliding Window Algorithms

Sliding windows track recent elements efficiently.

### Fixed-Size Sliding Window

```php
class SlidingWindow {
    private array $window = [];
    private int $size;
    private int $sum = 0;

    public function __construct(int $size) {
        $this->size = $size;
    }

    public function add(int $value): void {
        $this->window[] = $value;
        $this->sum += $value;

        if (count($this->window) > $this->size) {
            $removed = array_shift($this->window);
            $this->sum -= $removed;
        }
    }

    public function getAverage(): float {
        if (empty($this->window)) {
            return 0;
        }

        return $this->sum / count($this->window);
    }

    public function getMin(): ?int {
        return empty($this->window) ? null : min($this->window);
    }

    public function getMax(): ?int {
        return empty($this->window) ? null : max($this->window);
    }

    public function getSum(): int {
        return $this->sum;
    }

    public function getValues(): array {
        return $this->window;
    }
}

// Usage
$window = new SlidingWindow(5);

foreach ([10, 20, 30, 40, 50, 60] as $value) {
    $window->add($value);
    echo "Average: " . $window->getAverage() . ", Min: " . $window->getMin() . ", Max: " . $window->getMax() . "\n";
}
```

**Time Complexity**: O(1) for add/sum/average, O(n) for min/max
**Space Complexity**: O(n) where n is window size

### Optimized Sliding Window (Min/Max in O(1))

```php
class OptimizedSlidingWindow {
    private array $window = [];
    private SplDoublyLinkedList $minQueue;
    private SplDoublyLinkedList $maxQueue;
    private int $size;

    public function __construct(int $size) {
        $this->size = $size;
        $this->minQueue = new SplDoublyLinkedList();
        $this->maxQueue = new SplDoublyLinkedList();
    }

    public function add(int $value): void {
        // Add to window
        $this->window[] = $value;

        // Maintain min queue (increasing order)
        while (!$this->minQueue->isEmpty() && $this->minQueue->top() > $value) {
            $this->minQueue->pop();
        }
        $this->minQueue->push($value);

        // Maintain max queue (decreasing order)
        while (!$this->maxQueue->isEmpty() && $this->maxQueue->top() < $value) {
            $this->maxQueue->pop();
        }
        $this->maxQueue->push($value);

        // Remove old element if window is full
        if (count($this->window) > $this->size) {
            $removed = array_shift($this->window);

            if (!$this->minQueue->isEmpty() && $this->minQueue->bottom() === $removed) {
                $this->minQueue->shift();
            }

            if (!$this->maxQueue->isEmpty() && $this->maxQueue->bottom() === $removed) {
                $this->maxQueue->shift();
            }
        }
    }

    public function getMin(): ?int {
        return $this->minQueue->isEmpty() ? null : $this->minQueue->bottom();
    }

    public function getMax(): ?int {
        return $this->maxQueue->isEmpty() ? null : $this->maxQueue->bottom();
    }
}

// Usage
$window = new OptimizedSlidingWindow(3);

foreach ([1, 3, -1, -3, 5, 3, 6, 7] as $value) {
    $window->add($value);
    echo "Value: $value, Min: " . $window->getMin() . ", Max: " . $window->getMax() . "\n";
}
```

**Time Complexity**: O(1) amortized for all operations
**Space Complexity**: O(n)

### Time-Based Sliding Window

```php
class TimeBasedWindow {
    private array $events = [];  // [timestamp => [values]]
    private int $windowSeconds;

    public function __construct(int $windowSeconds) {
        $this->windowSeconds = $windowSeconds;
    }

    public function add($value, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();

        if (!isset($this->events[$timestamp])) {
            $this->events[$timestamp] = [];
        }

        $this->events[$timestamp][] = $value;
        $this->cleanup($timestamp);
    }

    private function cleanup(int $currentTimestamp): void {
        $cutoff = $currentTimestamp - $this->windowSeconds;

        foreach ($this->events as $timestamp => $values) {
            if ($timestamp < $cutoff) {
                unset($this->events[$timestamp]);
            }
        }
    }

    public function getCount(?int $timestamp = null): int {
        $timestamp = $timestamp ?? time();
        $this->cleanup($timestamp);

        $count = 0;
        foreach ($this->events as $values) {
            $count += count($values);
        }

        return $count;
    }

    public function getValues(?int $timestamp = null): array {
        $timestamp = $timestamp ?? time();
        $this->cleanup($timestamp);

        $allValues = [];
        foreach ($this->events as $values) {
            $allValues = array_merge($allValues, $values);
        }

        return $allValues;
    }

    public function getRate(?int $timestamp = null): float {
        $timestamp = $timestamp ?? time();
        $count = $this->getCount($timestamp);

        return $count / $this->windowSeconds;
    }
}

// Usage
$window = new TimeBasedWindow(60);  // 60-second window

$window->add('request1', time());
sleep(1);
$window->add('request2', time());
sleep(1);
$window->add('request3', time());

echo "Requests in last 60s: " . $window->getCount() . "\n";
echo "Request rate: " . $window->getRate() . " req/s\n";
```

## Rate Limiting Algorithms

### Token Bucket

```php
class TokenBucket {
    private float $tokens;
    private int $capacity;
    private int $refillRate;  // Tokens per second
    private int $lastRefill;

    public function __construct(int $capacity, int $refillRate) {
        $this->capacity = $capacity;
        $this->refillRate = $refillRate;
        $this->tokens = $capacity;
        $this->lastRefill = time();
    }

    private function refill(): void {
        $now = time();
        $elapsed = $now - $this->lastRefill;

        $this->tokens = min(
            $this->capacity,
            $this->tokens + ($elapsed * $this->refillRate)
        );

        $this->lastRefill = $now;
    }

    public function consume(int $tokens = 1): bool {
        $this->refill();

        if ($this->tokens >= $tokens) {
            $this->tokens -= $tokens;
            return true;
        }

        return false;
    }

    public function getAvailableTokens(): float {
        $this->refill();
        return $this->tokens;
    }
}

// Usage
$bucket = new TokenBucket(10, 2);  // 10 capacity, refill 2/second

for ($i = 0; $i < 15; $i++) {
    if ($bucket->consume()) {
        echo "Request $i: Allowed\n";
    } else {
        echo "Request $i: Rate limited (available: {$bucket->getAvailableTokens()})\n";
    }
}
```

### Leaky Bucket

```php
class LeakyBucket {
    private array $queue = [];
    private int $capacity;
    private int $leakRate;  // Items per second
    private int $lastLeak;

    public function __construct(int $capacity, int $leakRate) {
        $this->capacity = $capacity;
        $this->leakRate = $leakRate;
        $this->lastLeak = time();
    }

    private function leak(): void {
        $now = time();
        $elapsed = $now - $this->lastLeak;
        $itemsToLeak = $elapsed * $this->leakRate;

        for ($i = 0; $i < $itemsToLeak && !empty($this->queue); $i++) {
            array_shift($this->queue);
        }

        $this->lastLeak = $now;
    }

    public function add($item): bool {
        $this->leak();

        if (count($this->queue) < $this->capacity) {
            $this->queue[] = $item;
            return true;
        }

        return false;  // Bucket full
    }

    public function getSize(): int {
        $this->leak();
        return count($this->queue);
    }
}

// Usage
$bucket = new LeakyBucket(5, 1);  // Capacity 5, leak 1/second

for ($i = 0; $i < 10; $i++) {
    if ($bucket->add("request_$i")) {
        echo "Request $i: Queued (size: {$bucket->getSize()})\n";
    } else {
        echo "Request $i: Rejected (bucket full)\n";
    }
}
```

### Sliding Window Counter

```php
class SlidingWindowCounter {
    private array $buckets = [];
    private int $windowSize;
    private int $bucketSize;
    private int $maxRequests;

    public function __construct(int $windowSeconds, int $maxRequests, int $bucketCount = 10) {
        $this->windowSize = $windowSeconds;
        $this->maxRequests = $maxRequests;
        $this->bucketSize = (int) ceil($windowSeconds / $bucketCount);
    }

    private function getBucketKey(?int $timestamp = null): int {
        $timestamp = $timestamp ?? time();
        return (int) floor($timestamp / $this->bucketSize);
    }

    private function cleanup(int $currentBucket): void {
        $oldestAllowed = $currentBucket - ceil($this->windowSize / $this->bucketSize);

        foreach ($this->buckets as $bucket => $count) {
            if ($bucket < $oldestAllowed) {
                unset($this->buckets[$bucket]);
            }
        }
    }

    public function allowRequest(?int $timestamp = null): bool {
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

    public function getRequestCount(?int $timestamp = null): int {
        $timestamp = $timestamp ?? time();
        $bucket = $this->getBucketKey($timestamp);
        $this->cleanup($bucket);

        return array_sum($this->buckets);
    }
}

// Usage
$limiter = new SlidingWindowCounter(60, 100);  // 100 requests per 60 seconds

for ($i = 0; $i < 120; $i++) {
    if ($limiter->allowRequest()) {
        echo "Request $i: Allowed\n";
    } else {
        echo "Request $i: Rate limited\n";
    }
}
```

## Stream Aggregation

### Moving Average

```php
class MovingAverage {
    private SlidingWindow $window;

    public function __construct(int $size) {
        $this->window = new SlidingWindow($size);
    }

    public function add(float $value): float {
        $this->window->add($value);
        return $this->window->getAverage();
    }

    public function get(): float {
        return $this->window->getAverage();
    }
}

// Exponential Moving Average
class ExponentialMovingAverage {
    private ?float $ema = null;
    private float $alpha;

    public function __construct(float $alpha = 0.3) {
        $this->alpha = $alpha;
    }

    public function add(float $value): float {
        if ($this->ema === null) {
            $this->ema = $value;
        } else {
            $this->ema = $this->alpha * $value + (1 - $this->alpha) * $this->ema;
        }

        return $this->ema;
    }

    public function get(): ?float {
        return $this->ema;
    }
}

// Usage
$sma = new MovingAverage(5);
$ema = new ExponentialMovingAverage(0.3);

$values = [10, 12, 15, 11, 13, 14, 16, 18, 17, 19];

foreach ($values as $value) {
    echo "Value: $value, SMA: " . number_format($sma->add($value), 2);
    echo ", EMA: " . number_format($ema->add($value), 2) . "\n";
}
```

### Top-K Elements

```php
class TopKElements {
    private SplPriorityQueue $heap;
    private array $counts = [];
    private int $k;

    public function __construct(int $k) {
        $this->k = $k;
        $this->heap = new SplPriorityQueue();
    }

    public function add($element): void {
        if (!isset($this->counts[$element])) {
            $this->counts[$element] = 0;
        }

        $this->counts[$element]++;
    }

    public function getTopK(): array {
        // Rebuild heap
        $this->heap = new SplPriorityQueue();

        foreach ($this->counts as $element => $count) {
            $this->heap->insert($element, $count);
        }

        // Extract top K
        $topK = [];
        for ($i = 0; $i < $this->k && !$this->heap->isEmpty(); $i++) {
            $element = $this->heap->extract();
            $topK[] = [
                'element' => $element,
                'count' => $this->counts[$element]
            ];
        }

        return $topK;
    }
}

// Usage
$topK = new TopKElements(3);

$stream = ['apple', 'banana', 'apple', 'orange', 'apple', 'banana', 'grape', 'apple'];

foreach ($stream as $item) {
    $topK->add($item);
}

print_r($topK->getTopK());
```

## Real-Time Analytics

### Session Window

```php
class SessionWindow {
    private array $sessions = [];
    private int $timeout;  // Inactivity timeout in seconds

    public function __construct(int $timeout = 300) {
        $this->timeout = $timeout;
    }

    public function addEvent(string $userId, string $event, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();

        if (!isset($this->sessions[$userId])) {
            $this->sessions[$userId] = [
                'start' => $timestamp,
                'end' => $timestamp,
                'events' => []
            ];
        }

        $session = &$this->sessions[$userId];

        // Check for session timeout
        if ($timestamp - $session['end'] > $this->timeout) {
            // Start new session
            $this->sessions[$userId] = [
                'start' => $timestamp,
                'end' => $timestamp,
                'events' => []
            ];
        } else {
            // Extend session
            $session['end'] = $timestamp;
        }

        $this->sessions[$userId]['events'][] = [
            'event' => $event,
            'timestamp' => $timestamp
        ];
    }

    public function getSession(string $userId): ?array {
        return $this->sessions[$userId] ?? null;
    }

    public function getSessionDuration(string $userId): ?int {
        if (!isset($this->sessions[$userId])) {
            return null;
        }

        $session = $this->sessions[$userId];
        return $session['end'] - $session['start'];
    }

    public function getActiveSessions(?int $timestamp = null): int {
        $timestamp = $timestamp ?? time();
        $active = 0;

        foreach ($this->sessions as $session) {
            if ($timestamp - $session['end'] <= $this->timeout) {
                $active++;
            }
        }

        return $active;
    }
}

// Usage
$sessions = new SessionWindow(300);  // 5-minute timeout

$sessions->addEvent('user1', 'page_view', time());
$sessions->addEvent('user1', 'click', time() + 10);
$sessions->addEvent('user1', 'page_view', time() + 20);

$session = $sessions->getSession('user1');
echo "Session duration: " . $sessions->getSessionDuration('user1') . " seconds\n";
echo "Events: " . count($session['events']) . "\n";
```

### Metrics Aggregator

```php
class MetricsAggregator {
    private array $metrics = [];

    public function record(string $metric, float $value, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();

        if (!isset($this->metrics[$metric])) {
            $this->metrics[$metric] = [
                'count' => 0,
                'sum' => 0,
                'min' => PHP_FLOAT_MAX,
                'max' => PHP_FLOAT_MIN,
                'values' => []
            ];
        }

        $m = &$this->metrics[$metric];
        $m['count']++;
        $m['sum'] += $value;
        $m['min'] = min($m['min'], $value);
        $m['max'] = max($m['max'], $value);
        $m['values'][] = ['value' => $value, 'timestamp' => $timestamp];
    }

    public function getStats(string $metric): array {
        if (!isset($this->metrics[$metric])) {
            return [];
        }

        $m = $this->metrics[$metric];

        return [
            'count' => $m['count'],
            'sum' => $m['sum'],
            'avg' => $m['count'] > 0 ? $m['sum'] / $m['count'] : 0,
            'min' => $m['min'] === PHP_FLOAT_MAX ? null : $m['min'],
            'max' => $m['max'] === PHP_FLOAT_MIN ? null : $m['max'],
            'p50' => $this->percentile($m['values'], 50),
            'p95' => $this->percentile($m['values'], 95),
            'p99' => $this->percentile($m['values'], 99)
        ];
    }

    private function percentile(array $values, float $percentile): ?float {
        if (empty($values)) {
            return null;
        }

        $sorted = array_column($values, 'value');
        sort($sorted);

        $index = (int) ceil(($percentile / 100) * count($sorted)) - 1;
        $index = max(0, min($index, count($sorted) - 1));

        return $sorted[$index];
    }

    public function reset(string $metric): void {
        unset($this->metrics[$metric]);
    }

    public function getAllMetrics(): array {
        $all = [];

        foreach (array_keys($this->metrics) as $metric) {
            $all[$metric] = $this->getStats($metric);
        }

        return $all;
    }
}

// Usage
$metrics = new MetricsAggregator();

// Record response times
$metrics->record('api.response_time', 45.2);
$metrics->record('api.response_time', 52.1);
$metrics->record('api.response_time', 38.9);
$metrics->record('api.response_time', 150.3);
$metrics->record('api.response_time', 42.7);

$stats = $metrics->getStats('api.response_time');
print_r($stats);
```

## Real-World Applications

### 1. Real-Time Dashboard

```php
class RealTimeDashboard {
    private TimeBasedWindow $requestsWindow;
    private MetricsAggregator $metrics;
    private TopKElements $topPages;
    private SlidingWindowCounter $rateLimiter;

    public function __construct() {
        $this->requestsWindow = new TimeBasedWindow(300);  // 5 minutes
        $this->metrics = new MetricsAggregator();
        $this->topPages = new TopKElements(10);
        $this->rateLimiter = new SlidingWindowCounter(60, 1000);
    }

    public function trackRequest(string $url, float $responseTime, int $statusCode): void {
        // Track request in time window
        $this->requestsWindow->add($url);

        // Record metrics
        $this->metrics->record('response_time', $responseTime);
        $this->metrics->record("status.$statusCode", 1);

        // Track top pages
        $this->topPages->add($url);
    }

    public function getDashboard(): array {
        return [
            'current_rps' => $this->requestsWindow->getRate(),
            'requests_5min' => $this->requestsWindow->getCount(),
            'response_time' => $this->metrics->getStats('response_time'),
            'top_pages' => $this->topPages->getTopK(),
            'rate_limit_remaining' => 1000 - $this->rateLimiter->getRequestCount()
        ];
    }
}

// Usage
$dashboard = new RealTimeDashboard();

// Simulate traffic
for ($i = 0; $i < 100; $i++) {
    $urls = ['/home', '/about', '/products', '/contact'];
    $url = $urls[array_rand($urls)];
    $responseTime = rand(20, 200) / 10;  // 2-20ms
    $statusCode = rand(0, 10) > 8 ? 500 : 200;

    $dashboard->trackRequest($url, $responseTime, $statusCode);
}

print_r($dashboard->getDashboard());
```

### 2. Log Stream Processor

```php
class LogStreamProcessor {
    private array $errorCounts = [];
    private TimeBasedWindow $errorWindow;
    private int $alertThreshold;

    public function __construct(int $windowSeconds = 60, int $alertThreshold = 10) {
        $this->errorWindow = new TimeBasedWindow($windowSeconds);
        $this->alertThreshold = $alertThreshold;
    }

    public function processLog(string $line): ?array {
        // Parse log line (simplified)
        if (preg_match('/ERROR/', $line)) {
            $this->errorWindow->add($line);

            $errorCount = $this->errorWindow->getCount();

            if ($errorCount >= $this->alertThreshold) {
                return [
                    'type' => 'alert',
                    'message' => "High error rate: $errorCount errors in last 60s",
                    'errors' => $this->errorWindow->getValues()
                ];
            }
        }

        return null;
    }

    public function getErrorRate(): float {
        return $this->errorWindow->getRate();
    }
}

// Usage
$processor = new LogStreamProcessor(60, 5);

$logs = [
    '[INFO] Request received',
    '[ERROR] Database connection failed',
    '[INFO] Processing...',
    '[ERROR] Timeout',
    '[ERROR] Invalid input',
    '[ERROR] Permission denied',
    '[ERROR] File not found',
    '[ERROR] Out of memory',
];

foreach ($logs as $log) {
    $alert = $processor->processLog($log);

    if ($alert !== null) {
        echo "ALERT: {$alert['message']}\n";
    }
}
```

### 3. Event Stream Processor

```php
class EventStreamProcessor {
    private array $handlers = [];
    private SessionWindow $sessions;

    public function __construct() {
        $this->sessions = new SessionWindow(300);
    }

    public function on(string $eventType, callable $handler): void {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }

        $this->handlers[$eventType][] = $handler;
    }

    public function process(array $event): void {
        $type = $event['type'] ?? 'unknown';
        $userId = $event['user_id'] ?? 'anonymous';

        // Track in session
        $this->sessions->addEvent($userId, $type, $event['timestamp'] ?? time());

        // Trigger handlers
        if (isset($this->handlers[$type])) {
            foreach ($this->handlers[$type] as $handler) {
                $handler($event);
            }
        }

        // Global handlers
        if (isset($this->handlers['*'])) {
            foreach ($this->handlers['*'] as $handler) {
                $handler($event);
            }
        }
    }

    public function getSession(string $userId): ?array {
        return $this->sessions->getSession($userId);
    }
}

// Usage
$processor = new EventStreamProcessor();

// Register handlers
$processor->on('page_view', function ($event) {
    echo "Page viewed: {$event['page']}\n";
});

$processor->on('purchase', function ($event) {
    echo "Purchase made: \${$event['amount']}\n";
});

$processor->on('*', function ($event) {
    error_log("Event: " . json_encode($event));
});

// Process events
$processor->process([
    'type' => 'page_view',
    'user_id' => 'user123',
    'page' => '/products'
]);

$processor->process([
    'type' => 'purchase',
    'user_id' => 'user123',
    'amount' => 99.99
]);
```

## Performance Considerations

### Memory Management

```php
class BoundedStreamProcessor {
    private int $maxMemory;
    private int $currentMemory = 0;

    public function __construct(int $maxMemoryMB = 100) {
        $this->maxMemory = $maxMemoryMB * 1024 * 1024;
    }

    public function canProcess(): bool {
        $this->currentMemory = memory_get_usage(true);
        return $this->currentMemory < $this->maxMemory;
    }

    public function getMemoryUsage(): array {
        return [
            'current' => $this->currentMemory,
            'max' => $this->maxMemory,
            'percent' => ($this->currentMemory / $this->maxMemory) * 100
        ];
    }
}
```

## Summary

Stream processing algorithms enable real-time data analysis:

- **Sliding Windows**: Track recent data efficiently
- **Rate Limiting**: Control request rates (token bucket, leaky bucket)
- **Aggregation**: Compute statistics on streams
- **Session Tracking**: Group related events
- **Real-Time Analytics**: Monitor systems in real-time

**Key Principles**:
- O(1) space when possible
- Approximate results acceptable
- Time-based and count-based windows
- Efficient data structures (queues, heaps)

## Next Steps

- **Chapter 32: Probabilistic Algorithms** - Approximate stream algorithms
- **Chapter 31: Concurrent Algorithms** - Parallel stream processing
- **Chapter 29: Performance Optimization** - Optimize stream processing

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 36 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-36)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-36
php 01-*.php
```

## Practice Exercises

1. Implement a trending topics detector for social media
2. Build a real-time fraud detection system
3. Create a distributed rate limiter
4. Implement anomaly detection for server metrics
5. Build a real-time recommendation engine
