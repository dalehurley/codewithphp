<?php
/**
 * Count-Min Sketch Implementation
 *
 * Probabilistic data structure for frequency estimation with controlled error bounds.
 * Provides approximate counts with space-time efficiency.
 *
 * Error bound: ±εN with probability 1-δ
 * where ε = error margin, δ = confidence level, N = total count
 *
 * Use cases:
 * - Heavy hitter detection
 * - Trending topic analysis
 * - Frequency queries in streams
 * - Network traffic analysis
 *
 * @category  Algorithms
 * @package   ProbabilisticAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter32;

/**
 * Count-Min Sketch for frequency estimation
 */
class CountMinSketch
{
    private array $table;
    private int $width;
    private int $depth;
    private int $totalCount = 0;

    /**
     * @param float $epsilon Error margin (e.g., 0.001 = 0.1% error)
     * @param float $delta Confidence level (e.g., 0.01 = 99% confidence)
     */
    public function __construct(float $epsilon = 0.001, float $delta = 0.01)
    {
        // width = ceil(e / epsilon)
        $this->width = (int)ceil(M_E / $epsilon);

        // depth = ceil(ln(1 / delta))
        $this->depth = (int)ceil(log(1 / $delta));

        // Initialize table
        $this->table = array_fill(0, $this->depth, array_fill(0, $this->width, 0));

        echo "Count-Min Sketch initialized:\n";
        echo "  Dimensions: {$this->depth} × {$this->width}\n";
        echo "  Memory: " . number_format($this->getMemoryUsage() / 1024, 2) . " KB\n";
        echo "  Error bound: ±" . number_format($epsilon * 100, 3) . "% with " . number_format((1 - $delta) * 100, 1) . "% confidence\n\n";
    }

    /**
     * Hash function with seed
     *
     * @param string $item Item to hash
     * @param int $seed Hash seed
     * @return int Hash value
     */
    private function hash(string $item, int $seed): int
    {
        $hash = crc32($item . $seed);
        return abs($hash) % $this->width;
    }

    /**
     * Add item with count
     *
     * @param string $item Item to add
     * @param int $count Count to add (default: 1)
     */
    public function add(string $item, int $count = 1): void
    {
        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash($item, $i);
            $this->table[$i][$index] += $count;
        }

        $this->totalCount += $count;
    }

    /**
     * Estimate frequency of item
     *
     * @param string $item Item to estimate
     * @return int Estimated frequency
     */
    public function estimate(string $item): int
    {
        $min = PHP_INT_MAX;

        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash($item, $i);
            $min = min($min, $this->table[$i][$index]);
        }

        return $min;
    }

    /**
     * Merge another CMS into this one
     *
     * @param CountMinSketch $other Another CMS to merge
     * @throws \InvalidArgumentException If dimensions don't match
     */
    public function merge(CountMinSketch $other): void
    {
        if ($this->width !== $other->width || $this->depth !== $other->depth) {
            throw new \InvalidArgumentException("Cannot merge CMS with different dimensions");
        }

        for ($i = 0; $i < $this->depth; $i++) {
            for ($j = 0; $j < $this->width; $j++) {
                $this->table[$i][$j] += $other->table[$i][$j];
            }
        }

        $this->totalCount += $other->totalCount;
    }

    /**
     * Get memory usage in bytes
     *
     * @return int Memory usage
     */
    public function getMemoryUsage(): int
    {
        // Each counter is ~8 bytes (PHP int)
        return $this->width * $this->depth * 8;
    }

    /**
     * Get total count of all items
     *
     * @return int Total count
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * Get statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        return [
            'width' => $this->width,
            'depth' => $this->depth,
            'total_count' => $this->totalCount,
            'memory_bytes' => $this->getMemoryUsage(),
            'memory_kb' => $this->getMemoryUsage() / 1024
        ];
    }
}

/**
 * Heavy hitters detector using Count-Min Sketch
 */
class HeavyHittersDetector
{
    private CountMinSketch $cms;
    private array $topK = [];
    private int $k;

    /**
     * @param int $k Number of top items to track
     * @param float $epsilon Error margin
     */
    public function __construct(int $k = 10, float $epsilon = 0.001)
    {
        $this->k = $k;
        $this->cms = new CountMinSketch($epsilon, 0.01);
    }

    /**
     * Add item
     *
     * @param string $item Item to add
     * @param int $count Count to add
     */
    public function add(string $item, int $count = 1): void
    {
        $this->cms->add($item, $count);
        $estimated = $this->cms->estimate($item);

        // Update top-K
        $this->topK[$item] = $estimated;
        arsort($this->topK);
        $this->topK = array_slice($this->topK, 0, $this->k, true);
    }

    /**
     * Get top K items
     *
     * @return array Top K items with counts
     */
    public function getTopK(): array
    {
        return $this->topK;
    }

    /**
     * Check if item is a heavy hitter
     *
     * @param string $item Item to check
     * @param float $threshold Threshold percentage (0-1)
     * @return bool True if heavy hitter
     */
    public function isHeavyHitter(string $item, float $threshold = 0.01): bool
    {
        $count = $this->cms->estimate($item);
        $total = $this->cms->getTotalCount();

        return $total > 0 && ($count / $total) >= $threshold;
    }

    /**
     * Get detailed report
     *
     * @return array Report with statistics
     */
    public function getReport(): array
    {
        $report = [];
        $total = $this->cms->getTotalCount();

        foreach ($this->topK as $item => $count) {
            $report[] = [
                'item' => $item,
                'count' => $count,
                'percentage' => $total > 0 ? ($count / $total) * 100 : 0
            ];
        }

        return $report;
    }
}

/**
 * URL frequency tracker
 */
class UrlFrequencyTracker
{
    private CountMinSketch $cms;
    private array $urlStats = [];

    public function __construct()
    {
        $this->cms = new CountMinSketch(0.0001, 0.001);
    }

    /**
     * Track URL access
     *
     * @param string $url URL accessed
     */
    public function trackAccess(string $url): void
    {
        $this->cms->add($url);

        if (!isset($this->urlStats[$url])) {
            $this->urlStats[$url] = ['first_seen' => time()];
        }
        $this->urlStats[$url]['last_seen'] = time();
    }

    /**
     * Get access count for URL
     *
     * @param string $url URL to check
     * @return int Estimated access count
     */
    public function getAccessCount(string $url): int
    {
        return $this->cms->estimate($url);
    }

    /**
     * Get most accessed URLs
     *
     * @param int $limit Number of URLs to return
     * @return array Most accessed URLs
     */
    public function getMostAccessed(int $limit = 10): array
    {
        $counts = [];

        foreach (array_keys($this->urlStats) as $url) {
            $counts[$url] = $this->getAccessCount($url);
        }

        arsort($counts);
        return array_slice($counts, 0, $limit, true);
    }

    /**
     * Get total requests
     *
     * @return int Total requests
     */
    public function getTotalRequests(): int
    {
        return $this->cms->getTotalCount();
    }
}

/**
 * Example usage demonstrating Count-Min Sketch
 */
function demonstrateCountMinSketch(): void
{
    echo "=== Count-Min Sketch Demo ===\n\n";

    // Test 1: Basic frequency estimation
    echo "1. Basic Frequency Estimation\n";
    $cms = new CountMinSketch(0.001, 0.01);

    // Add items with known frequencies
    $items = [
        'apple' => 100,
        'banana' => 250,
        'cherry' => 75,
        'date' => 500,
        'elderberry' => 50
    ];

    foreach ($items as $item => $count) {
        $cms->add($item, $count);
    }

    echo "Item frequencies (actual vs estimated):\n";
    foreach ($items as $item => $actualCount) {
        $estimated = $cms->estimate($item);
        $error = abs($estimated - $actualCount);
        echo "  {$item}: {$actualCount} → {$estimated} (error: {$error})\n";
    }

    // Test 2: Heavy hitters detection
    echo "\n2. Heavy Hitters Detection\n";
    $detector = new HeavyHittersDetector(5, 0.001);

    // Simulate stream of URL accesses
    $urls = [
        '/home' => 1000,
        '/about' => 50,
        '/products' => 500,
        '/contact' => 30,
        '/blog' => 200,
        '/pricing' => 75,
        '/features' => 300,
        '/docs' => 100
    ];

    echo "Processing URL access stream...\n";
    foreach ($urls as $url => $hits) {
        for ($i = 0; $i < $hits; $i++) {
            $detector->add($url);
        }
    }

    $topK = $detector->getTopK();
    echo "\nTop " . count($topK) . " URLs:\n";
    foreach ($topK as $url => $count) {
        $actual = $urls[$url] ?? 0;
        echo "  {$url}: {$count} hits (actual: {$actual})\n";
    }

    // Test heavy hitter threshold (>1% of traffic)
    echo "\nHeavy hitters (>1% threshold):\n";
    foreach (array_keys($urls) as $url) {
        if ($detector->isHeavyHitter($url, 0.01)) {
            echo "  ✓ {$url}\n";
        }
    }

    // Test 3: Merging sketches
    echo "\n3. Merging Count-Min Sketches\n";
    $cms1 = new CountMinSketch(0.01, 0.01);
    $cms2 = new CountMinSketch(0.01, 0.01);

    // Different time windows
    $cms1->add('user_1', 100);
    $cms1->add('user_2', 50);
    $cms1->add('user_3', 75);

    $cms2->add('user_1', 50);
    $cms2->add('user_2', 150);
    $cms2->add('user_4', 25);

    echo "Before merge:\n";
    echo "  CMS1 - user_1: " . $cms1->estimate('user_1') . "\n";
    echo "  CMS2 - user_1: " . $cms2->estimate('user_1') . "\n";

    $cms1->merge($cms2);

    echo "After merge:\n";
    echo "  Merged - user_1: " . $cms1->estimate('user_1') . " (expected: 150)\n";
    echo "  Merged - user_2: " . $cms1->estimate('user_2') . " (expected: 200)\n";
    echo "  Merged - user_4: " . $cms1->estimate('user_4') . " (expected: 25)\n";

    // Test 4: URL frequency tracker
    echo "\n4. URL Frequency Tracker\n";
    $tracker = new UrlFrequencyTracker();

    // Simulate web traffic
    $traffic = [
        '/', '/blog', '/', '/products', '/', '/blog', '/contact',
        '/', '/blog', '/products', '/', '/', '/pricing', '/',
        '/blog', '/', '/products', '/', '/', '/blog'
    ];

    echo "Processing " . count($traffic) . " requests...\n";
    foreach ($traffic as $url) {
        $tracker->trackAccess($url);
    }

    echo "\nMost accessed URLs:\n";
    $mostAccessed = $tracker->getMostAccessed(5);
    foreach ($mostAccessed as $url => $count) {
        $percentage = ($count / $tracker->getTotalRequests()) * 100;
        echo "  {$url}: {$count} requests (" . number_format($percentage, 1) . "%)\n";
    }

    // Test 5: Accuracy analysis
    echo "\n5. Accuracy Analysis\n";
    $testCMS = new CountMinSketch(0.001, 0.01);

    // Add items with Zipf distribution
    $totalItems = 10000;
    $uniqueItems = 100;

    echo "Adding {$totalItems} items (Zipf distribution)...\n";
    for ($i = 0; $i < $totalItems; $i++) {
        // Zipf: first items appear more frequently
        $item = "item_" . (int)($uniqueItems / (1 + $i / 100));
        $testCMS->add($item);
    }

    // Check estimation accuracy
    $errors = [];
    $testItems = ['item_0', 'item_50', 'item_99'];

    echo "Estimation accuracy:\n";
    foreach ($testItems as $item) {
        $estimated = $testCMS->estimate($item);
        echo "  {$item}: {$estimated} occurrences\n";
    }

    $stats = $testCMS->getStats();
    echo "\nTotal processed: {$stats['total_count']} items\n";
    echo "Memory usage: " . number_format($stats['memory_kb'], 2) . " KB\n";

    // Compare with exact counting
    $exactCounts = [];
    for ($i = 0; $i < $totalItems; $i++) {
        $item = "item_" . (int)($uniqueItems / (1 + $i / 100));
        $exactCounts[$item] = ($exactCounts[$item] ?? 0) + 1;
    }

    echo "\nComparison (CMS vs Exact):\n";
    foreach ($testItems as $item) {
        $exact = $exactCounts[$item] ?? 0;
        $estimated = $testCMS->estimate($item);
        $error = $exact > 0 ? abs($estimated - $exact) / $exact * 100 : 0;
        echo "  {$item}: {$estimated} vs {$exact} (error: " . number_format($error, 2) . "%)\n";
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateCountMinSketch();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
