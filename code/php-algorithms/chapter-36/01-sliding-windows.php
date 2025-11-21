<?php

declare(strict_types=1);

/**
 * Chapter 36: Stream Processing Algorithms - Sliding Windows
 *
 * This example demonstrates sliding window algorithms:
 * - Fixed-size sliding windows
 * - Time-based sliding windows
 * - Optimized min/max tracking
 * - Window aggregation
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Fixed-size sliding window with basic aggregations
 */
class SlidingWindow
{
    private array $window = [];
    private int $size;
    private float $sum = 0;

    /**
     * @param int $size Window size
     */
    public function __construct(int $size)
    {
        if ($size <= 0) {
            throw new InvalidArgumentException("Window size must be positive");
        }

        $this->size = $size;
    }

    /**
     * Add a value to the window
     *
     * @param float $value Value to add
     */
    public function add(float $value): void
    {
        $this->window[] = $value;
        $this->sum += $value;

        if (count($this->window) > $this->size) {
            $removed = array_shift($this->window);
            $this->sum -= $removed;
        }
    }

    /**
     * Get the average of values in the window
     *
     * @return float Average value
     */
    public function getAverage(): float
    {
        if (empty($this->window)) {
            return 0;
        }

        return $this->sum / count($this->window);
    }

    /**
     * Get the minimum value in the window
     *
     * @return float|null Minimum value or null if empty
     */
    public function getMin(): ?float
    {
        return empty($this->window) ? null : min($this->window);
    }

    /**
     * Get the maximum value in the window
     *
     * @return float|null Maximum value or null if empty
     */
    public function getMax(): ?float
    {
        return empty($this->window) ? null : max($this->window);
    }

    /**
     * Get the sum of values in the window
     *
     * @return float Sum
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * Get all values in the window
     *
     * @return array Current window values
     */
    public function getValues(): array
    {
        return $this->window;
    }

    /**
     * Get the current window size
     *
     * @return int Number of elements in window
     */
    public function getCount(): int
    {
        return count($this->window);
    }

    /**
     * Clear the window
     */
    public function clear(): void
    {
        $this->window = [];
        $this->sum = 0;
    }
}

/**
 * Time-based sliding window
 */
class TimeBasedWindow
{
    private array $events = [];  // [timestamp => [values]]
    private int $windowSeconds;

    /**
     * @param int $windowSeconds Window duration in seconds
     */
    public function __construct(int $windowSeconds)
    {
        if ($windowSeconds <= 0) {
            throw new InvalidArgumentException("Window duration must be positive");
        }

        $this->windowSeconds = $windowSeconds;
    }

    /**
     * Add a value to the window
     *
     * @param mixed $value Value to add
     * @param int|null $timestamp Timestamp (default: current time)
     */
    public function add(mixed $value, ?int $timestamp = null): void
    {
        $timestamp = $timestamp ?? time();

        if (!isset($this->events[$timestamp])) {
            $this->events[$timestamp] = [];
        }

        $this->events[$timestamp][] = $value;
        $this->cleanup($timestamp);
    }

    /**
     * Remove old events outside the window
     *
     * @param int $currentTimestamp Current timestamp
     */
    private function cleanup(int $currentTimestamp): void
    {
        $cutoff = $currentTimestamp - $this->windowSeconds;

        foreach ($this->events as $timestamp => $values) {
            if ($timestamp < $cutoff) {
                unset($this->events[$timestamp]);
            }
        }
    }

    /**
     * Get count of events in the window
     *
     * @param int|null $timestamp Reference timestamp
     * @return int Event count
     */
    public function getCount(?int $timestamp = null): int
    {
        $timestamp = $timestamp ?? time();
        $this->cleanup($timestamp);

        $count = 0;
        foreach ($this->events as $values) {
            $count += count($values);
        }

        return $count;
    }

    /**
     * Get all values in the window
     *
     * @param int|null $timestamp Reference timestamp
     * @return array All values
     */
    public function getValues(?int $timestamp = null): array
    {
        $timestamp = $timestamp ?? time();
        $this->cleanup($timestamp);

        $allValues = [];
        foreach ($this->events as $values) {
            $allValues = array_merge($allValues, $values);
        }

        return $allValues;
    }

    /**
     * Get the rate (events per second)
     *
     * @param int|null $timestamp Reference timestamp
     * @return float Rate
     */
    public function getRate(?int $timestamp = null): float
    {
        $timestamp = $timestamp ?? time();
        $count = $this->getCount($timestamp);

        return $count / $this->windowSeconds;
    }

    /**
     * Clear all events
     */
    public function clear(): void
    {
        $this->events = [];
    }
}

/**
 * Optimized sliding window with O(1) min/max using deque
 */
class OptimizedSlidingWindow
{
    private array $window = [];
    private SplDoublyLinkedList $minDeque;
    private SplDoublyLinkedList $maxDeque;
    private int $size;

    /**
     * @param int $size Window size
     */
    public function __construct(int $size)
    {
        if ($size <= 0) {
            throw new InvalidArgumentException("Window size must be positive");
        }

        $this->size = $size;
        $this->minDeque = new SplDoublyLinkedList();
        $this->maxDeque = new SplDoublyLinkedList();
    }

    /**
     * Add a value to the window (O(1) amortized)
     *
     * @param float $value Value to add
     */
    public function add(float $value): void
    {
        // Add to window
        $this->window[] = $value;

        // Maintain min deque (increasing order)
        while (!$this->minDeque->isEmpty() && $this->minDeque->top() > $value) {
            $this->minDeque->pop();
        }
        $this->minDeque->push($value);

        // Maintain max deque (decreasing order)
        while (!$this->maxDeque->isEmpty() && $this->maxDeque->top() < $value) {
            $this->maxDeque->pop();
        }
        $this->maxDeque->push($value);

        // Remove old element if window is full
        if (count($this->window) > $this->size) {
            $removed = array_shift($this->window);

            if (!$this->minDeque->isEmpty() && $this->minDeque->bottom() === $removed) {
                $this->minDeque->shift();
            }

            if (!$this->maxDeque->isEmpty() && $this->maxDeque->bottom() === $removed) {
                $this->maxDeque->shift();
            }
        }
    }

    /**
     * Get minimum value in window (O(1))
     *
     * @return float|null Minimum value
     */
    public function getMin(): ?float
    {
        return $this->minDeque->isEmpty() ? null : $this->minDeque->bottom();
    }

    /**
     * Get maximum value in window (O(1))
     *
     * @return float|null Maximum value
     */
    public function getMax(): ?float
    {
        return $this->maxDeque->isEmpty() ? null : $this->maxDeque->bottom();
    }

    /**
     * Get all values in the window
     *
     * @return array Window values
     */
    public function getValues(): array
    {
        return $this->window;
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Sliding Windows Demo ===\n\n";

// Example 1: Basic fixed-size sliding window
echo "1. Fixed-Size Sliding Window:\n";
$window = new SlidingWindow(5);

$values = [10, 20, 30, 40, 50, 60, 70];
echo "Adding values: " . implode(', ', $values) . "\n\n";

foreach ($values as $value) {
    $window->add($value);

    printf(
        "Added %2d: Window [%s] - Avg: %5.1f, Min: %2.0f, Max: %2.0f, Sum: %3.0f\n",
        $value,
        implode(', ', array_map(fn($v) => sprintf("%2.0f", $v), $window->getValues())),
        $window->getAverage(),
        $window->getMin(),
        $window->getMax(),
        $window->getSum()
    );
}

// Example 2: Time-based sliding window
echo "\n2. Time-Based Sliding Window (60-second window):\n";
$timeWindow = new TimeBasedWindow(60);

// Simulate events over time
$currentTime = time();
$events = [
    [$currentTime - 70, 'old_event_1'],
    [$currentTime - 65, 'old_event_2'],
    [$currentTime - 50, 'event_1'],
    [$currentTime - 30, 'event_2'],
    [$currentTime - 10, 'event_3'],
    [$currentTime - 5, 'event_4'],
    [$currentTime, 'event_5'],
];

foreach ($events as [$timestamp, $event]) {
    $timeWindow->add($event, $timestamp);
    $age = $currentTime - $timestamp;
    echo "  Added '$event' ({$age}s ago)\n";
}

echo "\nWindow statistics:\n";
echo "  Events in window: " . $timeWindow->getCount() . "\n";
echo "  Event rate: " . number_format($timeWindow->getRate(), 3) . " events/sec\n";
echo "  Events: " . implode(', ', $timeWindow->getValues()) . "\n";

// Example 3: Optimized sliding window (O(1) min/max)
echo "\n3. Optimized Sliding Window (O(1) Min/Max):\n";
$optimized = new OptimizedSlidingWindow(3);

$sequence = [1, 3, -1, -3, 5, 3, 6, 7];
echo "Processing sequence: " . implode(', ', $sequence) . "\n\n";

foreach ($sequence as $value) {
    $optimized->add($value);

    $window = $optimized->getValues();
    $min = $optimized->getMin();
    $max = $optimized->getMax();

    printf(
        "Value: %2d -> Window [%s] -> Min: %2.0f, Max: %2.0f\n",
        $value,
        implode(', ', array_map(fn($v) => sprintf("%2.0f", $v), $window)),
        $min,
        $max
    );
}

// Example 4: Real-world - Response time monitoring
echo "\n4. Real-World Example - Response Time Monitoring:\n";

class ResponseTimeMonitor
{
    private SlidingWindow $window;

    public function __construct(int $windowSize = 100)
    {
        $this->window = new SlidingWindow($windowSize);
    }

    public function recordResponse(float $responseTime): void
    {
        $this->window->add($responseTime);
    }

    public function getStats(): array
    {
        return [
            'average' => $this->window->getAverage(),
            'min' => $this->window->getMin(),
            'max' => $this->window->getMax(),
            'count' => $this->window->getCount()
        ];
    }

    public function isHealthy(float $threshold = 200): bool
    {
        return $this->window->getAverage() < $threshold;
    }
}

$monitor = new ResponseTimeMonitor(10);

echo "Simulating API response times (ms):\n";

for ($i = 0; $i < 15; $i++) {
    // Random response time between 50-300ms
    $responseTime = rand(50, 300);
    $monitor->recordResponse($responseTime);

    $stats = $monitor->getStats();
    $health = $monitor->isHealthy(200) ? "✓ Healthy" : "✗ Slow";

    printf(
        "  Request %2d: %3dms - Avg: %5.1fms (Min: %3.0fms, Max: %3.0fms) - %s\n",
        $i + 1,
        $responseTime,
        $stats['average'],
        $stats['min'],
        $stats['max'],
        $health
    );
}

// Example 5: Performance comparison
echo "\n5. Performance Comparison:\n";

$testData = range(1, 10000);
$windowSize = 100;

// Standard sliding window (O(n) min/max)
$start = microtime(true);
$sw = new SlidingWindow($windowSize);
foreach ($testData as $value) {
    $sw->add($value);
    $sw->getMin();
    $sw->getMax();
}
$standardTime = microtime(true) - $start;

// Optimized sliding window (O(1) min/max)
$start = microtime(true);
$osw = new OptimizedSlidingWindow($windowSize);
foreach ($testData as $value) {
    $osw->add($value);
    $osw->getMin();
    $osw->getMax();
}
$optimizedTime = microtime(true) - $start;

printf("Standard window:  %.3f ms\n", $standardTime * 1000);
printf("Optimized window: %.3f ms\n", $optimizedTime * 1000);
printf("Speedup: %.2fx\n", $standardTime / $optimizedTime);

// Example 6: Traffic spike detection
echo "\n6. Traffic Spike Detection:\n";

class TrafficSpikeDetector
{
    private TimeBasedWindow $normalWindow;
    private TimeBasedWindow $recentWindow;
    private float $spikeThreshold;

    public function __construct(
        int $normalPeriod = 300,    // 5 minutes
        int $recentPeriod = 60,     // 1 minute
        float $spikeThreshold = 2.0  // 2x normal rate
    ) {
        $this->normalWindow = new TimeBasedWindow($normalPeriod);
        $this->recentWindow = new TimeBasedWindow($recentPeriod);
        $this->spikeThreshold = $spikeThreshold;
    }

    public function recordRequest(string $request): void
    {
        $this->normalWindow->add($request);
        $this->recentWindow->add($request);
    }

    public function detectSpike(): ?array
    {
        $normalRate = $this->normalWindow->getRate();
        $recentRate = $this->recentWindow->getRate();

        if ($normalRate > 0 && $recentRate / $normalRate > $this->spikeThreshold) {
            return [
                'normal_rate' => $normalRate,
                'recent_rate' => $recentRate,
                'multiplier' => $recentRate / $normalRate
            ];
        }

        return null;
    }
}

$spikeDetector = new TrafficSpikeDetector(300, 60, 2.0);

echo "Simulating traffic with spike:\n";

// Normal traffic
for ($i = 0; $i < 50; $i++) {
    $spikeDetector->recordRequest("request_$i");
    usleep(10000); // 10ms delay
}

echo "  Normal traffic period...\n";

// Traffic spike
for ($i = 50; $i < 150; $i++) {
    $spikeDetector->recordRequest("request_$i");
    usleep(1000); // 1ms delay - 10x faster

    if ($i % 20 == 0) {
        $spike = $spikeDetector->detectSpike();
        if ($spike) {
            printf(
                "  ⚠ SPIKE DETECTED! Normal: %.2f req/s, Recent: %.2f req/s (%.1fx)\n",
                $spike['normal_rate'],
                $spike['recent_rate'],
                $spike['multiplier']
            );
        }
    }
}

echo "\n=== Demo Complete ===\n";
