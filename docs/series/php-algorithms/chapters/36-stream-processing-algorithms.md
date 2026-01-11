---
title: "36: Stream Processing Algorithms"
description: "Real-time stream processing algorithms for analytics, monitoring, and rate limiting including sliding windows and aggregation techniques"
series: "php-algorithms"
chapter: 36
order: 36
difficulty: "Advanced"
prerequisites: []
estimatedTime: "PT50M"
teaches:
  - 'Implement sliding window algorithms for tracking recent data efficiently'
  - 'Build rate limiting and throttling mechanisms for API protection'
  - 'Apply stream aggregation techniques for real-time metrics and dashboards'
  - 'Use Lossy Counting to find frequent items in streams with bounded memory'
  - 'Design time-series algorithms for monitoring and anomaly detection'
---
![Stream Processing Algorithms](/images/php-algorithms/chapter-36-stream-processing-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 36</span>
</div>

# 36: Stream Processing Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Stream processing algorithms handle continuous, potentially infinite data streams where the entire dataset cannot fit in memory. These algorithms are essential for real-time analytics, monitoring systems, and big data processing. They power everything from rate limiters protecting APIs to dashboards showing live metrics for millions of users.

While traditional batch processing analyzes data after it's been collected, stream processing algorithms analyze data as it arrives, enabling real-time decision-making and immediate responses to events. This capability is crucial for modern applications that need to react instantly to user behavior, system metrics, or business events.

By the end of this chapter, you'll have implemented sliding window algorithms for tracking recent events, rate limiting mechanisms for API protection, stream aggregation techniques for real-time metrics, and time-series algorithms for monitoring and anomaly detection. These techniques form the foundation of modern real-time data processing systems.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Understanding of queues, deques, and specialized data structures
- Familiarity with window-based computations and sliding window concepts
- Awareness of latency requirements and throughput constraints in real-time systems
- Ability to design O(1) space and time solutions

**Estimated Time**: ~50 minutes

**Verify your setup:**

```bash
php --version
```

## What You'll Build

By the end of this chapter, you will have created:

- Fixed-size and time-based sliding window implementations for tracking recent events
- Token bucket and leaky bucket rate limiting algorithms for API protection
- Sliding window counter for precise rate limiting
- Moving average and exponential moving average aggregators
- Top-K elements tracker for finding most frequent items in streams
- Lossy Counting algorithm for finding frequent items with bounded memory
- Session window tracker for grouping related events
- Metrics aggregator with percentile calculations
- Real-time dashboard combining multiple stream processing techniques
- Log stream processor with error rate monitoring
- Event stream processor with handler registration

## Objectives

- Implement sliding window algorithms for tracking recent data efficiently
- Build rate limiting and throttling mechanisms for API protection
- Apply stream aggregation techniques for real-time metrics and dashboards
- Use Lossy Counting to find frequent items in streams with bounded memory
- Design time-series algorithms for monitoring and anomaly detection
- Create memory-efficient solutions that process unlimited data streams

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

A fixed-size sliding window maintains a constant number of recent elements, automatically removing the oldest when new ones arrive. This is ideal for tracking metrics over a fixed number of events.

```php
# filename: sliding-window.php
<?php

declare(strict_types=1);

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

**Why It Works**: The sliding window maintains a running sum, allowing O(1) average calculation. When the window exceeds the size limit, `array_shift()` removes the oldest element and subtracts it from the sum. Min/max operations require scanning the entire window, resulting in O(n) complexity.

### Optimized Sliding Window (Min/Max in O(1))

For applications requiring frequent min/max queries, we can optimize using monotonic queues (deques) to achieve O(1) amortized time for all operations.

```php
# filename: optimized-sliding-window.php
<?php

declare(strict_types=1);

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

**Why It Works**: We maintain two monotonic queues—one increasing (for min) and one decreasing (for max). When adding a value, we remove all elements that violate the monotonic property. This ensures the front of each queue always contains the min/max. Each element is added and removed at most once, giving O(1) amortized complexity.

### Time-Based Sliding Window

Time-based windows track events within a specific time period rather than a fixed count. This is essential for rate limiting and real-time analytics where temporal patterns matter more than event counts.

```php
# filename: time-based-window.php
<?php

declare(strict_types=1);

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

**Why It Works**: Events are stored by timestamp, allowing efficient cleanup of expired entries. The `cleanup()` method removes all events older than the window period. This approach uses O(n) space where n is the number of unique timestamps, but provides accurate time-based queries.

### Tumbling Windows

Tumbling windows are fixed-size, non-overlapping windows that partition the stream into discrete time periods. Each event belongs to exactly one window, making them ideal for periodic reporting and batch-like processing.

```php
# filename: tumbling-window.php
<?php

declare(strict_types=1);

class TumblingWindow {
    private array $windows = [];
    private int $windowSize;
    private int $currentWindowStart = 0;

    public function __construct(int $windowSizeSeconds) {
        $this->windowSize = $windowSizeSeconds;
    }

    public function add($value, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();
        $windowStart = $this->getWindowStart($timestamp);

        if ($windowStart !== $this->currentWindowStart) {
            // New window started - emit previous window if needed
            $this->currentWindowStart = $windowStart;
        }

        if (!isset($this->windows[$windowStart])) {
            $this->windows[$windowStart] = [
                'start' => $windowStart,
                'end' => $windowStart + $this->windowSize,
                'values' => [],
                'count' => 0,
                'sum' => 0
            ];
        }

        $this->windows[$windowStart]['values'][] = $value;
        $this->windows[$windowStart]['count']++;
        $this->windows[$windowStart]['sum'] += is_numeric($value) ? (float)$value : 0;
    }

    private function getWindowStart(int $timestamp): int {
        return (int) floor($timestamp / $this->windowSize) * $this->windowSize;
    }

    public function getWindow(int $windowStart): ?array {
        return $this->windows[$windowStart] ?? null;
    }

    public function getCurrentWindow(): ?array {
        $now = time();
        $windowStart = $this->getWindowStart($now);
        return $this->getWindow($windowStart);
    }

    public function getCompletedWindows(): array {
        $now = time();
        $currentWindowStart = $this->getWindowStart($now);

        $completed = [];
        foreach ($this->windows as $start => $window) {
            if ($start < $currentWindowStart) {
                $window['average'] = $window['count'] > 0
                    ? $window['sum'] / $window['count']
                    : 0;
                $completed[$start] = $window;
            }
        }

        return $completed;
    }

    public function cleanup(int $beforeTimestamp): void {
        $cutoffWindow = $this->getWindowStart($beforeTimestamp);

        foreach ($this->windows as $start => $window) {
            if ($start < $cutoffWindow) {
                unset($this->windows[$start]);
            }
        }
    }
}

// Usage
$tumbling = new TumblingWindow(60); // 60-second windows

$tumbling->add(10, time());
$tumbling->add(20, time() + 30);
$tumbling->add(30, time() + 45);

// Wait for window to complete
sleep(65);

$completed = $tumbling->getCompletedWindows();
foreach ($completed as $window) {
    echo "Window {$window['start']}-{$window['end']}: " .
         "Count={$window['count']}, Avg={$window['average']}\n";
}
```

**Why It Works**: Tumbling windows divide time into non-overlapping intervals. Each timestamp maps to exactly one window using integer division. Windows are emitted only when they're complete, ensuring no overlap. This makes them ideal for periodic reporting where you need discrete time buckets.

### Hopping Windows

Hopping windows are fixed-size windows that slide forward by a hop interval, creating overlapping windows. They provide more frequent updates than tumbling windows while maintaining fixed window sizes.

```php
# filename: hopping-window.php
<?php

declare(strict_types=1);

class HoppingWindow {
    private array $windows = [];
    private int $windowSize;
    private int $hopSize;

    public function __construct(int $windowSizeSeconds, int $hopSizeSeconds) {
        $this->windowSize = $windowSizeSeconds;
        $this->hopSize = $hopSizeSeconds;
    }

    public function add($value, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();
        $windowStarts = $this->getActiveWindows($timestamp);

        foreach ($windowStarts as $start) {
            if (!isset($this->windows[$start])) {
                $this->windows[$start] = [
                    'start' => $start,
                    'end' => $start + $this->windowSize,
                    'values' => [],
                    'count' => 0
                ];
            }

            $this->windows[$start]['values'][] = $value;
            $this->windows[$start]['count']++;
        }
    }

    private function getActiveWindows(int $timestamp): array {
        $windows = [];
        $endTime = $timestamp;
        $startTime = $endTime - $this->windowSize;

        // Find all windows that contain this timestamp
        $firstWindowStart = (int) ceil($startTime / $this->hopSize) * $this->hopSize;
        $lastWindowStart = (int) floor($endTime / $this->hopSize) * $this->hopSize;

        for ($start = $firstWindowStart; $start <= $lastWindowStart; $start += $this->hopSize) {
            if ($start <= $timestamp && ($start + $this->windowSize) > $timestamp) {
                $windows[] = $start;
            }
        }

        return $windows;
    }

    public function getWindows(?int $timestamp = null): array {
        $timestamp = $timestamp ?? time();
        $active = [];

        foreach ($this->windows as $start => $window) {
            if ($start <= $timestamp && ($start + $this->windowSize) > $timestamp) {
                $active[$start] = $window;
            }
        }

        return $active;
    }

    public function cleanup(int $beforeTimestamp): void {
        $cutoff = $beforeTimestamp - $this->windowSize;

        foreach ($this->windows as $start => $window) {
            if ($start < $cutoff) {
                unset($this->windows[$start]);
            }
        }
    }
}

// Usage
$hopping = new HoppingWindow(60, 10); // 60-second windows, 10-second hop

$hopping->add('event1', time());
$hopping->add('event2', time() + 5);
$hopping->add('event3', time() + 15);

$active = $hopping->getWindows();
echo "Active windows: " . count($active) . "\n";
```

**Why It Works**: Hopping windows create multiple overlapping windows. An event belongs to all windows that contain its timestamp. The hop size determines how frequently new windows start. Smaller hops provide more frequent updates but require more memory to track multiple overlapping windows.

## Rate Limiting Algorithms

Rate limiting protects APIs and services from being overwhelmed by too many requests. Different algorithms offer different trade-offs between accuracy, memory usage, and implementation complexity.

### Token Bucket

The token bucket algorithm allows bursts of traffic up to the bucket capacity while maintaining a steady average rate. Tokens are added at a fixed rate, and requests consume tokens.

```php
# filename: token-bucket.php
<?php

declare(strict_types=1);

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

**Why It Works**: Tokens are refilled based on elapsed time since the last refill. This allows bursts (up to capacity) while ensuring the long-term rate doesn't exceed the refill rate. The algorithm is memory-efficient (O(1) space) and provides smooth rate limiting.

### Leaky Bucket

The leaky bucket algorithm processes requests at a fixed rate, queuing excess requests up to capacity. Unlike token bucket, it enforces a strict output rate.

```php
# filename: leaky-bucket.php
<?php

declare(strict_types=1);

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

**Why It Works**: The bucket "leaks" items at a constant rate, processing them in order. When the bucket is full, new requests are rejected. This provides strict rate limiting but may reject valid requests during bursts. The queue size represents current load.

### Sliding Window Counter

The sliding window counter divides time into buckets, providing more accurate rate limiting than fixed windows while being simpler than true sliding windows.

```php
# filename: sliding-window-counter.php
<?php

declare(strict_types=1);

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

**Why It Works**: Time is divided into buckets, and requests are counted per bucket. The algorithm sums counts from buckets within the window. This provides a good balance between accuracy and memory usage—more buckets mean higher accuracy but more memory.

## Stream Aggregation

Aggregation algorithms compute statistics over streams without storing all data. Common aggregations include averages, top-K elements, and percentiles.

### Moving Average

Moving averages smooth out short-term fluctuations, revealing longer-term trends. Simple moving average (SMA) uses equal weights, while exponential moving average (EMA) gives more weight to recent values.

```php
# filename: moving-average.php
<?php

declare(strict_types=1);

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

**Why It Works**: SMA uses a sliding window to compute the average of the last n values. EMA uses a weighted average where recent values have exponentially decreasing weights. EMA requires O(1) space (only stores the current EMA value) while SMA requires O(n) space for the window.

### Top-K Elements

Finding the K most frequent elements in a stream is essential for trending topics, popular items, and anomaly detection. We use a combination of counting and heap-based selection.

```php
# filename: top-k-elements.php
<?php

declare(strict_types=1);

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

**Why It Works**: We maintain counts for each element in O(1) time per addition. When querying top-K, we rebuild a max-heap from counts and extract the K largest elements. This gives O(n log n) query time but O(1) update time, ideal for frequent updates with occasional queries.

### Lossy Counting

Lossy Counting is an algorithm for finding frequent items in a data stream with bounded memory. Unlike Top-K which requires storing all unique elements, Lossy Counting provides approximate results with guaranteed error bounds using only O(1/ε) space, where ε is the error tolerance.

```php
# filename: lossy-counting.php
<?php

declare(strict_types=1);

class LossyCounting {
    private array $counters = [];
    private float $epsilon;  // Error tolerance (0 < ε < 1)
    private int $currentBucket = 1;
    private int $bucketSize;
    private int $itemsProcessed = 0;

    public function __construct(float $epsilon) {
        if ($epsilon <= 0 || $epsilon >= 1) {
            throw new InvalidArgumentException("Epsilon must be between 0 and 1");
        }
        $this->epsilon = $epsilon;
        $this->bucketSize = (int)ceil(1 / $epsilon);
    }

    public function add($item): void {
        $this->itemsProcessed++;

        // Add or increment counter
        if (!isset($this->counters[$item])) {
            $this->counters[$item] = [
                'count' => 1,
                'bucket' => $this->currentBucket
            ];
        } else {
            $this->counters[$item]['count']++;
        }

        // Periodically decrement and remove low-frequency items
        if ($this->itemsProcessed % $this->bucketSize === 0) {
            $this->cleanup();
            $this->currentBucket++;
        }
    }

    private function cleanup(): void {
        foreach ($this->counters as $item => $data) {
            // Decrement count
            $data['count']--;

            // Remove if count reaches zero or below
            if ($data['count'] <= 0) {
                unset($this->counters[$item]);
            } else {
                $this->counters[$item] = $data;
            }
        }
    }

    public function getFrequent(float $supportThreshold): array {
        // supportThreshold is the minimum frequency (e.g., 0.1 for 10%)
        $minCount = (int)ceil(($supportThreshold - $this->epsilon) * $this->itemsProcessed);
        $frequent = [];

        foreach ($this->counters as $item => $data) {
            if ($data['count'] >= $minCount) {
                $frequent[$item] = [
                    'count' => $data['count'],
                    'estimated_frequency' => $data['count'] / $this->itemsProcessed,
                    'error_bound' => $this->epsilon
                ];
            }
        }

        // Sort by count descending
        arsort($frequent);

        return $frequent;
    }

    public function getMemoryUsage(): int {
        return count($this->counters);
    }

    public function getItemsProcessed(): int {
        return $this->itemsProcessed;
    }
}

// Usage
$lossy = new LossyCounting(0.01);  // 1% error tolerance

// Simulate a stream of items
$stream = array_merge(
    array_fill(0, 1000, 'frequent_item'),
    array_fill(0, 500, 'another_frequent'),
    array_fill(0, 100, 'rare_item'),
    array_map(fn($i) => "unique_$i", range(1, 10000))
);

shuffle($stream);

foreach ($stream as $item) {
    $lossy->add($item);
}

// Find items with frequency >= 5%
$frequent = $lossy->getFrequent(0.05);

echo "Frequent items (>= 5% frequency):\n";
foreach ($frequent as $item => $data) {
    echo sprintf(
        "%s: count=%d, frequency=%.2f%%\n",
        $item,
        $data['count'],
        $data['estimated_frequency'] * 100
    );
}

echo "\nMemory used: " . $lossy->getMemoryUsage() . " counters\n";
echo "Items processed: " . $lossy->getItemsProcessed() . "\n";
```

**Why It Works**: Lossy Counting divides the stream into buckets of size ⌈1/ε⌉. For each item, it maintains a counter and the bucket where it was first seen. Periodically (at bucket boundaries), it decrements all counters and removes items with count ≤ 0. This ensures that any item with true frequency ≥ εN will be found, and the estimated frequency has error at most εN. The space complexity is O(1/ε), independent of stream size.

**When to Use**: Lossy Counting is ideal when you need to find frequent items in massive streams where storing all unique items is impossible. It's more memory-efficient than Top-K for very large streams with many unique items, but provides approximate results with error bounds.

**Algorithm Comparison**:

- **Top-K**: Use when you need exact top-K items and the number of unique items is manageable (O(n) space)
- **Lossy Counting**: Use when you need frequent items with guaranteed error bounds and bounded memory (O(1/ε) space)
- **Count-Min Sketch** (Chapter 32): Use when you need frequency estimates for any item with very low memory overhead, but without guaranteed false positive elimination

## Real-Time Analytics

Real-time analytics require grouping events by time windows or user sessions, computing metrics, and detecting patterns as data arrives.

### Session Window

Session windows group events by user activity periods, automatically creating new sessions after periods of inactivity. This is essential for user behavior analysis and session-based metrics.

```php
# filename: session-window.php
<?php

declare(strict_types=1);

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

**Why It Works**: Sessions are tracked per user with start and end timestamps. When a new event arrives, we check if it's within the timeout period of the last event. If not, we start a new session. This allows natural grouping of related user activities.

### Metrics Aggregator

Metrics aggregators compute statistical summaries (count, sum, average, min, max, percentiles) over streams of numeric values. This is the foundation of monitoring and observability systems.

```php
# filename: metrics-aggregator.php
<?php

declare(strict_types=1);

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

**Why It Works**: We maintain running statistics (count, sum, min, max) in O(1) time per record. For percentiles, we store all values and sort when queried. For production systems, consider approximate algorithms like t-digest or HDR histograms for O(1) space percentile estimation.

## Real-World Applications

These examples combine multiple stream processing techniques to solve real-world problems.

### 1. Real-Time Dashboard

A real-time dashboard combines multiple stream processing techniques to provide live insights into system performance.

```php
# filename: real-time-dashboard.php
<?php

declare(strict_types=1);

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

**Why It Works**: The dashboard combines time-based windows for request rate tracking, metrics aggregation for response times, top-K tracking for popular pages, and rate limiting for protection. Each component handles a specific aspect of real-time monitoring.

### 2. Log Stream Processor

Log stream processors analyze log files in real-time, detecting error spikes, performance issues, and security threats as they occur.

```php
# filename: log-stream-processor.php
<?php

declare(strict_types=1);

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

**Why It Works**: Errors are tracked in a time-based window. When the error count exceeds the threshold, an alert is triggered. This enables proactive monitoring and rapid response to system issues.

### 3. Event Stream Processor

Event stream processors handle user events (page views, clicks, purchases) with flexible handler registration and session tracking.

```php
# filename: event-stream-processor.php
<?php

declare(strict_types=1);

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

**Why It Works**: Events are routed to registered handlers based on event type, enabling flexible processing pipelines. Session tracking groups related events, while wildcard handlers (`*`) allow global event logging or analytics.

## Handling Out-of-Order Data

Real-world streams often have events arriving out of order due to network delays, distributed systems, or retries. Handling late-arriving data is crucial for accurate stream processing.

### Watermarks

Watermarks represent the progress of event time in the stream. They indicate that all events with timestamps before the watermark have likely been received, allowing safe window emission.

```php
# filename: watermark-handler.php
<?php

declare(strict_types=1);

class WatermarkHandler {
    private int $maxOutOfOrderness;
    private int $currentWatermark = 0;
    private array $lateEvents = [];

    public function __construct(int $maxOutOfOrdernessSeconds = 5) {
        $this->maxOutOfOrderness = $maxOutOfOrdernessSeconds;
    }

    public function updateWatermark(int $eventTimestamp): void {
        // Watermark is max timestamp seen minus max out-of-orderness
        $newWatermark = $eventTimestamp - $this->maxOutOfOrderness;

        if ($newWatermark > $this->currentWatermark) {
            $this->currentWatermark = $newWatermark;
            $this->processLateEvents();
        }
    }

    public function isLate(int $eventTimestamp): bool {
        return $eventTimestamp < $this->currentWatermark;
    }

    public function handleEvent($event, int $eventTimestamp): void {
        if ($this->isLate($eventTimestamp)) {
            // Store late events for potential processing
            $this->lateEvents[] = [
                'event' => $event,
                'timestamp' => $eventTimestamp
            ];
        } else {
            // Process normally
            $this->updateWatermark($eventTimestamp);
        }
    }

    private function processLateEvents(): void {
        $processed = [];

        foreach ($this->lateEvents as $index => $lateEvent) {
            if ($lateEvent['timestamp'] >= $this->currentWatermark) {
                // No longer late, process it
                $processed[] = $index;
            }
        }

        // Remove processed events (in reverse to maintain indices)
        foreach (array_reverse($processed) as $index) {
            unset($this->lateEvents[$index]);
        }

        $this->lateEvents = array_values($this->lateEvents);
    }

    public function getCurrentWatermark(): int {
        return $this->currentWatermark;
    }

    public function getLateEventCount(): int {
        return count($this->lateEvents);
    }
}

// Usage
$handler = new WatermarkHandler(5); // 5-second max out-of-orderness

// Simulate out-of-order events
$handler->handleEvent('event1', 100); // Normal
$handler->handleEvent('event2', 95);  // Late (arrived after event1)
$handler->handleEvent('event3', 105); // Normal

echo "Watermark: {$handler->getCurrentWatermark()}\n";
echo "Late events: {$handler->getLateEventCount()}\n";
```

**Why It Works**: Watermarks track the progress of event time. By subtracting the maximum expected out-of-orderness from the latest timestamp, we create a safe point for window emission. Events arriving before the watermark are considered late and can be handled separately (dropped, buffered, or processed in a late window).

### Buffering Strategy for Late Data

Buffering late-arriving events allows processing them when they're no longer considered late, improving accuracy at the cost of latency.

```php
# filename: late-data-buffer.php
<?php

declare(strict_types=1);

class LateDataBuffer {
    private array $buffer = [];
    private int $bufferWindow;
    private int $currentTime = 0;

    public function __construct(int $bufferWindowSeconds = 10) {
        $this->bufferWindow = $bufferWindowSeconds;
    }

    public function addEvent($event, int $eventTimestamp): void {
        $this->currentTime = max($this->currentTime, $eventTimestamp);

        $this->buffer[] = [
            'event' => $event,
            'timestamp' => $eventTimestamp
        ];

        // Sort by timestamp to maintain order
        usort($this->buffer, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
    }

    public function getReadyEvents(?int $currentTime = null): array {
        $currentTime = $currentTime ?? $this->currentTime;
        $cutoff = $currentTime - $this->bufferWindow;

        $ready = [];
        $remaining = [];

        foreach ($this->buffer as $item) {
            if ($item['timestamp'] <= $cutoff) {
                $ready[] = $item;
            } else {
                $remaining[] = $item;
            }
        }

        $this->buffer = $remaining;
        return $ready;
    }

    public function getBufferSize(): int {
        return count($this->buffer);
    }
}

// Usage
$buffer = new LateDataBuffer(10);

// Add events out of order
$buffer->addEvent('event1', 100);
$buffer->addEvent('event3', 110);
$buffer->addEvent('event2', 105); // Out of order

// Get events ready for processing (older than 10 seconds)
$ready = $buffer->getReadyEvents(115);
foreach ($ready as $event) {
    echo "Processing: {$event['event']} at {$event['timestamp']}\n";
}
```

**Why It Works**: The buffer holds events for a configurable window period. Events older than the buffer window are considered "ready" and can be safely processed. This allows catching late-arriving events while maintaining bounded memory usage.

## Window Triggers

Window triggers determine when to emit window results. Different trigger strategies serve different use cases—early triggers for low latency, on-time triggers for accuracy, and late triggers for handling out-of-order data.

```php
# filename: window-triggers.php
<?php

declare(strict_types=1);

class TriggeredWindow {
    private array $window = [];
    private int $windowSize;
    private int $windowStart;
    private array $earlyTriggers = [];
    private ?callable $onTimeTrigger = null;
    private ?callable $lateTrigger = null;
    private int $earlyTriggerInterval;

    public function __construct(int $windowSizeSeconds, int $earlyTriggerIntervalSeconds = 10) {
        $this->windowSize = $windowSizeSeconds;
        $this->earlyTriggerInterval = $earlyTriggerIntervalSeconds;
        $this->windowStart = time();
    }

    public function onEarly(callable $trigger): void {
        $this->earlyTriggers[] = $trigger;
    }

    public function onTime(callable $trigger): void {
        $this->onTimeTrigger = $trigger;
    }

    public function onLate(callable $trigger): void {
        $this->lateTrigger = $trigger;
    }

    public function add($value, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();

        // Check if we need a new window
        if ($timestamp >= $this->windowStart + $this->windowSize) {
            $this->emitOnTime();
            $this->windowStart = (int) floor($timestamp / $this->windowSize) * $this->windowSize;
            $this->window = [];
        }

        $this->window[] = $value;

        // Check for early trigger
        $elapsed = $timestamp - $this->windowStart;
        if ($elapsed > 0 && $elapsed % $this->earlyTriggerInterval === 0) {
            $this->emitEarly();
        }
    }

    public function addLate($value, int $eventTimestamp, int $currentTime): void {
        // Check if event is late
        if ($eventTimestamp < $this->windowStart) {
            if ($this->lateTrigger !== null) {
                ($this->lateTrigger)($value, $eventTimestamp, $this->window);
            }
        } else {
            $this->add($value, $eventTimestamp);
        }
    }

    private function emitEarly(): void {
        foreach ($this->earlyTriggers as $trigger) {
            $trigger($this->window, $this->windowStart);
        }
    }

    private function emitOnTime(): void {
        if ($this->onTimeTrigger !== null && !empty($this->window)) {
            ($this->onTimeTrigger)($this->window, $this->windowStart);
        }
    }

    public function getCurrentWindow(): array {
        return $this->window;
    }
}

// Usage
$window = new TriggeredWindow(60, 15); // 60-second window, early trigger every 15s

$window->onEarly(function ($data, $start) {
    echo "Early trigger: " . count($data) . " events\n";
});

$window->onTime(function ($data, $start) {
    echo "On-time trigger: " . count($data) . " events\n";
});

$window->onLate(function ($value, $timestamp, $window) {
    echo "Late event: $value at $timestamp\n";
});

// Simulate events
for ($i = 0; $i < 5; $i++) {
    $window->add("event_$i", time() + ($i * 15));
    sleep(1);
}
```

**Why It Works**: Triggers provide flexible emission strategies. Early triggers emit partial results for low-latency monitoring. On-time triggers emit complete windows for accurate reporting. Late triggers handle out-of-order events, allowing updates to already-emitted windows or separate late-event processing.

## Performance Considerations

Stream processing systems must handle high throughput while maintaining low latency and bounded memory usage.

### Memory Management

Memory bounds prevent stream processors from consuming unlimited memory, essential for long-running processes.

```php
# filename: bounded-stream-processor.php
<?php

declare(strict_types=1);

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

**Why It Works**: By monitoring memory usage and refusing to process when limits are exceeded, we prevent out-of-memory errors. In production, consider implementing backpressure mechanisms that slow down processing or drop low-priority events when memory is constrained.

### Backpressure Mechanisms

Backpressure occurs when the stream producer generates data faster than the consumer can process it. Effective backpressure strategies prevent memory exhaustion and system overload.

```php
# filename: backpressure-handler.php
<?php

declare(strict_types=1);

class BackpressureHandler {
    private int $maxQueueSize;
    private array $queue = [];
    private string $strategy; // 'drop_oldest', 'drop_newest', 'throttle', 'block'

    public function __construct(int $maxQueueSize, string $strategy = 'drop_oldest') {
        $this->maxQueueSize = $maxQueueSize;
        $this->strategy = $strategy;
    }

    public function add($item): bool {
        if (count($this->queue) >= $this->maxQueueSize) {
            return $this->handleBackpressure($item);
        }

        $this->queue[] = $item;
        return true;
    }

    private function handleBackpressure($item): bool {
        return match ($this->strategy) {
            'drop_oldest' => $this->dropOldest($item),
            'drop_newest' => $this->dropNewest($item),
            'throttle' => $this->throttle($item),
            'block' => $this->block($item),
            default => false
        };
    }

    private function dropOldest($item): bool {
        // Remove oldest item, add new one
        array_shift($this->queue);
        $this->queue[] = $item;
        return true;
    }

    private function dropNewest($item): bool {
        // Reject new item, keep existing
        return false;
    }

    private function throttle($item): bool {
        // Wait briefly, then try again
        usleep(1000); // 1ms delay

        if (count($this->queue) < $this->maxQueueSize) {
            $this->queue[] = $item;
            return true;
        }

        return false; // Still full after throttle
    }

    private function block($item): bool {
        // Block until space available (not recommended for production)
        while (count($this->queue) >= $this->maxQueueSize) {
            usleep(1000);
        }

        $this->queue[] = $item;
        return true;
    }

    public function process(callable $processor, int $batchSize = 1): int {
        $processed = 0;

        while (!empty($this->queue) && $processed < $batchSize) {
            $item = array_shift($this->queue);
            $processor($item);
            $processed++;
        }

        return $processed;
    }

    public function getQueueSize(): int {
        return count($this->queue);
    }

    public function getQueueUtilization(): float {
        return count($this->queue) / $this->maxQueueSize;
    }
}

// Priority-based backpressure
class PriorityBackpressureHandler {
    private SplPriorityQueue $highPriority;
    private SplPriorityQueue $lowPriority;
    private int $maxSize;

    public function __construct(int $maxSize) {
        $this->maxSize = $maxSize;
        $this->highPriority = new SplPriorityQueue();
        $this->lowPriority = new SplPriorityQueue();
    }

    public function add($item, int $priority = 0): bool {
        $totalSize = $this->highPriority->count() + $this->lowPriority->count();

        if ($totalSize >= $this->maxSize) {
            // Drop lowest priority item
            if (!$this->lowPriority->isEmpty()) {
                $this->lowPriority->extract();
            } elseif (!$this->highPriority->isEmpty()) {
                $this->highPriority->extract();
            } else {
                return false;
            }
        }

        if ($priority >= 5) {
            $this->highPriority->insert($item, $priority);
        } else {
            $this->lowPriority->insert($item, $priority);
        }

        return true;
    }

    public function process(callable $processor): void {
        // Process high priority first
        while (!$this->highPriority->isEmpty()) {
            $item = $this->highPriority->extract();
            $processor($item);
        }

        // Then low priority
        while (!$this->lowPriority->isEmpty()) {
            $item = $this->lowPriority->extract();
            $processor($item);
        }
    }
}

// Usage
$handler = new BackpressureHandler(100, 'drop_oldest');

// Simulate high-rate producer
for ($i = 0; $i < 150; $i++) {
    $handler->add("item_$i");
}

echo "Queue size: {$handler->getQueueSize()}\n";
echo "Utilization: " . ($handler->getQueueUtilization() * 100) . "%\n";
```

**Why It Works**: Different backpressure strategies serve different needs. Dropping oldest preserves recent data (good for real-time monitoring). Dropping newest preserves historical data (good for batch processing). Throttling slows producers to match consumer speed. Priority-based handling ensures critical events aren't lost during overload.

## Troubleshooting

### Issue: Memory Usage Grows Unbounded

**Symptom**: Process memory increases continuously, eventually causing out-of-memory errors.

**Cause**: Windows or aggregators not cleaning up old data, or storing all stream values instead of summaries.

**Solution**:

- Ensure time-based windows call `cleanup()` regularly
- Use approximate algorithms (e.g., t-digest for percentiles) instead of storing all values
- Implement memory bounds and backpressure mechanisms
- Consider using bounded data structures with eviction policies

### Issue: Rate Limiter Allows Too Many Requests

**Symptom**: Rate limiter allows bursts exceeding the intended limit.

**Cause**: Token bucket algorithm allows bursts up to capacity; this is by design.

**Solution**:

- Use sliding window counter for stricter limits
- Reduce token bucket capacity
- Combine token bucket with per-second limits
- Consider leaky bucket for strict rate enforcement

### Issue: Sliding Window Min/Max Returns Wrong Values

**Symptom**: Optimized sliding window returns incorrect min or max values.

**Cause**: Queue maintenance logic error—not removing elements when they leave the window.

**Solution**:

- Verify queue removal logic matches window removal
- Check that queue comparisons use correct operators (`>` for min, `<` for max)
- Ensure queue is checked before accessing `bottom()` or `top()`
- Add unit tests with edge cases (duplicate values, window size 1)

### Issue: Time-Based Window Not Cleaning Up

**Symptom**: Memory usage grows even though events should be expired.

**Cause**: `cleanup()` not called frequently enough, or timestamp comparison logic error.

**Solution**:

- Call `cleanup()` on every `add()` and query operation
- Verify timestamp comparison uses correct operator (`<` not `<=` for cutoff)
- Consider periodic background cleanup task for high-throughput scenarios
- Use monotonic timestamps (avoid `time()` jumps from system clock adjustments)

## Wrap-up

You've mastered stream processing algorithms that enable real-time data analysis:

- ✓ **Sliding Windows**: Implemented fixed-size, time-based, and optimized sliding windows for tracking recent data efficiently
- ✓ **Window Types**: Built tumbling windows for periodic reporting and hopping windows for overlapping aggregations
- ✓ **Rate Limiting**: Built token bucket, leaky bucket, and sliding window counter algorithms for API protection
- ✓ **Stream Aggregation**: Created moving averages, top-K trackers, and metrics aggregators for real-time statistics
- ✓ **Out-of-Order Handling**: Implemented watermarks and buffering strategies for late-arriving data
- ✓ **Window Triggers**: Created early, on-time, and late trigger mechanisms for flexible window emission
- ✓ **Backpressure**: Built multiple backpressure strategies (drop oldest/newest, throttle, priority-based) for overload protection
- ✓ **Session Tracking**: Implemented session windows for grouping related events over time
- ✓ **Real-Time Analytics**: Built complete dashboard, log processor, and event stream processor systems

**Key Principles Learned**:

- O(1) space complexity when possible for unlimited streams
- Approximate results are often acceptable for real-time systems
- Different window types (sliding, tumbling, hopping, session) serve different use cases
- Watermarks enable safe window emission in the presence of out-of-order data
- Window triggers provide flexibility between latency and accuracy
- Backpressure strategies prevent system overload during high load
- Efficient data structures (queues, heaps, deques) are essential for performance

These algorithms power modern applications from API gateways to monitoring dashboards, enabling systems to process millions of events per second while maintaining low latency and memory usage.

## Further Reading

- [PHP SPL Data Structures](https://www.php.net/manual/en/book.spl.php) — Standard PHP Library documentation for queues, stacks, and heaps
- [Rate Limiting Strategies](https://en.wikipedia.org/wiki/Rate_limiting) — Wikipedia article on rate limiting algorithms and patterns
- [Stream Processing Patterns](https://www.oreilly.com/library/view/streaming-systems/9781491983867/) — O'Reilly book on streaming systems architecture
- [Chapter 32: Probabilistic Algorithms](/series/php-algorithms/chapters/32-probabilistic-algorithms) — Probabilistic stream algorithms including reservoir sampling, Bloom filters, and HyperLogLog for massive datasets
- [Chapter 31: Concurrent Algorithms](/series/php-algorithms/chapters/31-concurrent-algorithms) — Parallel stream processing techniques
- [Chapter 29: Performance Optimization](/series/php-algorithms/chapters/29-performance-optimization) — Optimizing stream processing performance

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 36 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-36)**

Clone the repository to run examples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-36
php 01-*.php
```

## Practice Exercises

### Exercise 1: Trending Topics Detector

**Goal**: Build a system that tracks trending topics from a social media stream

Create a file called `trending-topics.php` and implement:

- Use a time-based sliding window to track hashtags or keywords
- Implement a top-K tracker to find the most mentioned topics
- Calculate trending score based on recent mentions vs historical average
- Output the top 10 trending topics every minute

**Validation**: Test with a stream of social media posts:

```php
$detector = new TrendingTopicsDetector(300); // 5-minute window

// Simulate posts
$detector->addPost(['hashtags' => ['#php', '#webdev']]);
$detector->addPost(['hashtags' => ['#php', '#laravel']]);

$trending = $detector->getTrending(10);
// Should return top 10 trending hashtags
```

### Exercise 2: Real-Time Fraud Detection

**Goal**: Build a fraud detection system using stream processing

Create a file called `fraud-detector.php` and implement:

- Track transaction amounts in a sliding window per user
- Detect anomalies when transaction amount exceeds 3x the average
- Flag rapid transactions (more than 5 in 10 seconds)
- Use session windows to track user behavior patterns

**Validation**: Test with transaction streams:

```php
$detector = new FraudDetector();

$detector->processTransaction('user123', 100.00);
$detector->processTransaction('user123', 500.00); // Should flag as anomaly
```

### Exercise 3: Distributed Rate Limiter

**Goal**: Create a rate limiter that works across multiple servers

Create a file called `distributed-rate-limiter.php` and implement:

- Use a shared storage mechanism (Redis simulation or file-based)
- Implement sliding window counter with distributed state
- Handle race conditions with atomic operations
- Support per-user and global rate limits

**Validation**: Test concurrent requests:

```php
$limiter = new DistributedRateLimiter('redis://localhost');

// Multiple processes should share the same rate limit
$allowed = $limiter->allowRequest('user123', 100, 60); // 100 req/min
```

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="36"
  label="Stream Processing Algorithms complete!"
/>
