<?php
/**
 * Reservoir Sampling Implementation
 *
 * Maintains a random sample of k items from a stream of unknown size.
 * Each item has equal probability k/n of being in the sample.
 *
 * Use cases:
 * - Sampling from large datasets
 * - Statistical analysis
 * - A/B testing
 * - Monitoring and alerting
 *
 * @category  Algorithms
 * @package   ProbabilisticAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter32;

/**
 * Basic reservoir sampling
 */
class ReservoirSampler
{
    private array $reservoir = [];
    private int $size;
    private int $seen = 0;

    /**
     * @param int $size Sample size (k)
     */
    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * Add item to the stream
     *
     * @param mixed $item Item to add
     */
    public function add(mixed $item): void
    {
        $this->seen++;

        if (count($this->reservoir) < $this->size) {
            // Reservoir not full, add directly
            $this->reservoir[] = $item;
        } else {
            // Random replacement with probability k/n
            $index = mt_rand(0, $this->seen - 1);

            if ($index < $this->size) {
                $this->reservoir[$index] = $item;
            }
        }
    }

    /**
     * Get current sample
     *
     * @return array Sample of items
     */
    public function getSample(): array
    {
        return $this->reservoir;
    }

    /**
     * Get number of items seen
     *
     * @return int Items processed
     */
    public function getSeenCount(): int
    {
        return $this->seen;
    }

    /**
     * Get sample size
     *
     * @return int Current sample size
     */
    public function getSize(): int
    {
        return count($this->reservoir);
    }

    /**
     * Reset sampler
     */
    public function reset(): void
    {
        $this->reservoir = [];
        $this->seen = 0;
    }
}

/**
 * Weighted reservoir sampling
 *
 * Samples items with probability proportional to their weights
 */
class WeightedReservoirSampler
{
    private \SplPriorityQueue $reservoir;
    private int $size;
    private int $seen = 0;

    /**
     * @param int $size Sample size
     */
    public function __construct(int $size)
    {
        $this->size = $size;
        $this->reservoir = new \SplPriorityQueue();
    }

    /**
     * Add weighted item
     *
     * @param mixed $item Item to add
     * @param float $weight Item weight (higher = more likely to be sampled)
     */
    public function add(mixed $item, float $weight): void
    {
        $this->seen++;

        // Calculate key using weight: key = random^(1/weight)
        $key = pow(mt_rand() / mt_getrandmax(), 1 / max($weight, 0.0001));

        $this->reservoir->insert($item, $key);

        // Keep only top k items
        if ($this->reservoir->count() > $this->size) {
            $this->reservoir->extract();
        }
    }

    /**
     * Get current sample
     *
     * @return array Sampled items
     */
    public function getSample(): array
    {
        $sample = [];
        $clone = clone $this->reservoir;

        while (!$clone->isEmpty()) {
            $sample[] = $clone->extract();
        }

        return array_reverse($sample);
    }

    /**
     * Get number of items seen
     *
     * @return int Items processed
     */
    public function getSeenCount(): int
    {
        return $this->seen;
    }
}

/**
 * Time-based reservoir sampling
 *
 * Maintains samples from different time windows
 */
class TimeWindowSampler
{
    private array $windows = [];
    private int $sampleSize;
    private int $windowDuration;

    /**
     * @param int $sampleSize Size of each sample
     * @param int $windowDuration Window duration in seconds
     */
    public function __construct(int $sampleSize = 100, int $windowDuration = 3600)
    {
        $this->sampleSize = $sampleSize;
        $this->windowDuration = $windowDuration;
    }

    /**
     * Add item with timestamp
     *
     * @param mixed $item Item to add
     * @param ?int $timestamp Unix timestamp (null = now)
     */
    public function add(mixed $item, ?int $timestamp = null): void
    {
        $timestamp = $timestamp ?? time();
        $windowKey = (int)($timestamp / $this->windowDuration);

        if (!isset($this->windows[$windowKey])) {
            $this->windows[$windowKey] = new ReservoirSampler($this->sampleSize);
        }

        $this->windows[$windowKey]->add($item);
    }

    /**
     * Get sample for specific window
     *
     * @param int $windowKey Window identifier
     * @return array Sample
     */
    public function getWindowSample(int $windowKey): array
    {
        return $this->windows[$windowKey]->getSample() ?? [];
    }

    /**
     * Get sample for time range
     *
     * @param int $startTime Start timestamp
     * @param int $endTime End timestamp
     * @return array Combined sample
     */
    public function getRangeSample(int $startTime, int $endTime): array
    {
        $startWindow = (int)($startTime / $this->windowDuration);
        $endWindow = (int)($endTime / $this->windowDuration);

        $combined = [];
        for ($w = $startWindow; $w <= $endWindow; $w++) {
            if (isset($this->windows[$w])) {
                $combined = array_merge($combined, $this->windows[$w]->getSample());
            }
        }

        // Re-sample if combined sample is too large
        if (count($combined) > $this->sampleSize) {
            $sampler = new ReservoirSampler($this->sampleSize);
            foreach ($combined as $item) {
                $sampler->add($item);
            }
            return $sampler->getSample();
        }

        return $combined;
    }

    /**
     * Get all active windows
     *
     * @return array Window keys
     */
    public function getActiveWindows(): array
    {
        return array_keys($this->windows);
    }
}

/**
 * Distributed reservoir sampling
 *
 * Combines samples from multiple sources
 */
class DistributedReservoirSampler
{
    private int $size;
    private array $samples = [];

    /**
     * @param int $size Global sample size
     */
    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * Add local sample from a worker/node
     *
     * @param string $nodeId Node identifier
     * @param array $localSample Local sample
     * @param int $localCount Total items seen by this node
     */
    public function addLocalSample(string $nodeId, array $localSample, int $localCount): void
    {
        $this->samples[$nodeId] = [
            'sample' => $localSample,
            'count' => $localCount
        ];
    }

    /**
     * Get merged global sample
     *
     * @return array Global sample
     */
    public function getGlobalSample(): array
    {
        $totalCount = array_sum(array_column($this->samples, 'count'));
        $globalSample = [];

        // Merge all local samples
        foreach ($this->samples as $nodeId => $data) {
            foreach ($data['sample'] as $item) {
                $globalSample[] = [
                    'item' => $item,
                    'source' => $nodeId
                ];
            }
        }

        // Re-sample to get final sample of size k
        if (count($globalSample) <= $this->size) {
            return array_column($globalSample, 'item');
        }

        $finalSampler = new ReservoirSampler($this->size);
        foreach ($globalSample as $entry) {
            $finalSampler->add($entry['item']);
        }

        return $finalSampler->getSample();
    }

    /**
     * Get statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        $totalCount = array_sum(array_column($this->samples, 'count'));
        $totalSamples = array_sum(array_map(fn($s) => count($s['sample']), $this->samples));

        return [
            'nodes' => count($this->samples),
            'total_items' => $totalCount,
            'total_samples' => $totalSamples,
            'target_size' => $this->size
        ];
    }
}

/**
 * Example usage demonstrating reservoir sampling
 */
function demonstrateReservoirSampling(): void
{
    echo "=== Reservoir Sampling Demo ===\n\n";

    // Test 1: Basic reservoir sampling
    echo "1. Basic Reservoir Sampling\n";
    $sampler = new ReservoirSampler(10);

    // Stream 1000 items
    echo "Streaming 1000 items, keeping sample of 10...\n";
    for ($i = 1; $i <= 1000; $i++) {
        $sampler->add("item_{$i}");
    }

    $sample = $sampler->getSample();
    echo "Sample size: " . count($sample) . "\n";
    echo "Sample items: " . implode(', ', array_slice($sample, 0, 10)) . "\n";
    echo "Items seen: " . $sampler->getSeenCount() . "\n";

    // Test uniform distribution
    echo "\n2. Testing Uniform Distribution\n";
    $trials = 1000;
    $streamSize = 100;
    $sampleSize = 10;
    $itemCounts = array_fill(1, $streamSize, 0);

    echo "Running {$trials} trials to verify uniformity...\n";
    for ($trial = 0; $trial < $trials; $trial++) {
        $testSampler = new ReservoirSampler($sampleSize);

        for ($i = 1; $i <= $streamSize; $i++) {
            $testSampler->add($i);
        }

        foreach ($testSampler->getSample() as $item) {
            $itemCounts[$item]++;
        }
    }

    $expectedCount = ($trials * $sampleSize) / $streamSize;
    echo "Expected count per item: " . number_format($expectedCount, 1) . "\n";
    echo "First 10 items actual counts: ";
    for ($i = 1; $i <= 10; $i++) {
        echo "{$itemCounts[$i]} ";
    }
    echo "\n";

    // Test 3: Weighted reservoir sampling
    echo "\n3. Weighted Reservoir Sampling\n";
    $weightedSampler = new WeightedReservoirSampler(5);

    // Add items with different weights
    $items = [
        ['id' => 'low_weight', 'weight' => 1],
        ['id' => 'medium_weight', 'weight' => 5],
        ['id' => 'high_weight', 'weight' => 10],
        ['id' => 'very_high_weight', 'weight' => 20]
    ];

    echo "Adding items with different weights...\n";
    for ($i = 0; $i < 100; $i++) {
        foreach ($items as $item) {
            $weightedSampler->add($item['id'], $item['weight']);
        }
    }

    $weightedSample = $weightedSampler->getSample();
    $distribution = array_count_values($weightedSample);
    echo "Sample distribution (should favor higher weights):\n";
    foreach ($distribution as $itemId => $count) {
        echo "  {$itemId}: {$count}\n";
    }

    // Test 4: Time window sampling
    echo "\n4. Time Window Sampling\n";
    $timesampler = new TimeWindowSampler(5, 3600); // 5 samples per hour

    // Simulate events across time
    $baseTime = strtotime('2025-01-01 00:00:00');
    echo "Simulating events across multiple hours...\n";

    for ($hour = 0; $hour < 3; $hour++) {
        for ($event = 0; $event < 50; $event++) {
            $timestamp = $baseTime + ($hour * 3600) + ($event * 60);
            $timesampler->add("event_h{$hour}_e{$event}", $timestamp);
        }
    }

    echo "Active windows: " . count($timesampler->getActiveWindows()) . "\n";
    echo "Sample from first window: ";
    $firstWindow = min($timesampler->getActiveWindows());
    $windowSample = $timesampler->getWindowSample($firstWindow);
    echo count($windowSample) . " items\n";

    // Range sample
    $rangeSample = $timesampler->getRangeSample(
        $baseTime,
        $baseTime + 7200 // First 2 hours
    );
    echo "Range sample (2 hours): " . count($rangeSample) . " items\n";

    // Test 5: Distributed sampling
    echo "\n5. Distributed Reservoir Sampling\n";
    $distributed = new DistributedReservoirSampler(20);

    // Simulate 4 worker nodes
    echo "Collecting samples from 4 worker nodes...\n";
    for ($node = 1; $node <= 4; $node++) {
        $nodeSampler = new ReservoirSampler(10);

        // Each node processes different number of items
        $nodeItems = $node * 100;
        for ($i = 0; $i < $nodeItems; $i++) {
            $nodeSampler->add("node{$node}_item{$i}");
        }

        $distributed->addLocalSample(
            "node_{$node}",
            $nodeSampler->getSample(),
            $nodeSampler->getSeenCount()
        );
    }

    $globalSample = $distributed->getGlobalSample();
    echo "Global sample size: " . count($globalSample) . "\n";

    $stats = $distributed->getStats();
    echo "Total items processed: {$stats['total_items']}\n";
    echo "Total local samples: {$stats['total_samples']}\n";
    echo "Final global sample: {$stats['target_size']}\n";

    // Verify distribution across nodes
    $nodeDistribution = [];
    foreach ($globalSample as $item) {
        if (preg_match('/^node(\d+)_/', $item, $matches)) {
            $nodeId = $matches[1];
            $nodeDistribution[$nodeId] = ($nodeDistribution[$nodeId] ?? 0) + 1;
        }
    }
    echo "Distribution across nodes:\n";
    foreach ($nodeDistribution as $nodeId => $count) {
        echo "  Node {$nodeId}: {$count} samples\n";
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateReservoirSampling();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
