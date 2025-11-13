<?php
/**
 * HyperLogLog Implementation
 *
 * Cardinality estimation algorithm that uses minimal memory to count unique
 * elements with remarkable accuracy. Typical error rate: ±0.81% with 16KB memory.
 *
 * Use cases:
 * - Unique visitor counting
 * - Distinct element counting in streams
 * - Database query optimization
 * - Analytics systems
 *
 * @category  Algorithms
 * @package   ProbabilisticAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter32;

/**
 * HyperLogLog for cardinality estimation
 */
class HyperLogLog
{
    private array $registers;
    private int $precision;
    private int $registerCount;

    /**
     * @param int $precision Precision parameter (4-16), higher = more accurate
     */
    public function __construct(int $precision = 14)
    {
        // Precision: 4-16 (14 is a good default: ~0.81% error, 16KB)
        $this->precision = max(4, min(16, $precision));
        $this->registerCount = 1 << $this->precision; // 2^precision
        $this->registers = array_fill(0, $this->registerCount, 0);

        $memoryKB = $this->registerCount / 1024;
        $errorRate = 1.04 / sqrt($this->registerCount);

        echo "HyperLogLog initialized:\n";
        echo "  Precision: {$this->precision}\n";
        echo "  Registers: {$this->registerCount}\n";
        echo "  Memory: " . number_format($memoryKB, 2) . " KB\n";
        echo "  Standard error: ±" . number_format($errorRate * 100, 2) . "%\n\n";
    }

    /**
     * Add an item to the HyperLogLog
     *
     * @param string $item Item to add
     */
    public function add(string $item): void
    {
        $hash = $this->hash64($item);

        // Extract register index from first p bits
        $registerIndex = $hash & ($this->registerCount - 1);

        // Count leading zeros in remaining bits + 1
        $leadingZeros = $this->countLeadingZeros($hash >> $this->precision) + 1;

        // Update register with maximum leading zeros seen
        $this->registers[$registerIndex] = max(
            $this->registers[$registerIndex],
            $leadingZeros
        );
    }

    /**
     * Estimate cardinality (number of unique items)
     *
     * @return int Estimated count
     */
    public function count(): int
    {
        $sum = 0.0;
        $zeros = 0;

        foreach ($this->registers as $register) {
            $sum += 1.0 / (1 << $register);
            if ($register === 0) {
                $zeros++;
            }
        }

        // Calculate raw estimate
        $alpha = $this->getAlpha();
        $estimate = $alpha * $this->registerCount * $this->registerCount / $sum;

        // Apply small range correction
        if ($estimate <= 2.5 * $this->registerCount) {
            if ($zeros !== 0) {
                return (int)($this->registerCount * log($this->registerCount / $zeros));
            }
        }

        // Apply intermediate range (no correction)
        if ($estimate <= (1 / 30) * (1 << 32)) {
            return (int)$estimate;
        }

        // Apply large range correction
        return (int)(-1 * (1 << 32) * log(1 - $estimate / (1 << 32)));
    }

    /**
     * Merge another HyperLogLog into this one
     *
     * @param HyperLogLog $other Another HyperLogLog to merge
     * @throws \InvalidArgumentException If precisions don't match
     */
    public function merge(HyperLogLog $other): void
    {
        if ($this->precision !== $other->precision) {
            throw new \InvalidArgumentException("Cannot merge HLL with different precision");
        }

        for ($i = 0; $i < $this->registerCount; $i++) {
            $this->registers[$i] = max($this->registers[$i], $other->registers[$i]);
        }
    }

    /**
     * 64-bit hash function
     *
     * @param string $item Item to hash
     * @return int Hash value
     */
    private function hash64(string $item): int
    {
        // Use murmur3 or similar for production
        $hash = hash('crc32', $item, true);
        return unpack('N', $hash)[1];
    }

    /**
     * Count leading zeros in 32-bit integer
     *
     * @param int $value Value to count zeros in
     * @return int Number of leading zeros
     */
    private function countLeadingZeros(int $value): int
    {
        if ($value === 0) {
            return 32;
        }

        $zeros = 0;
        $mask = 1 << 31;

        while (($value & $mask) === 0) {
            $zeros++;
            $mask >>= 1;
        }

        return $zeros;
    }

    /**
     * Get alpha constant for bias correction
     *
     * @return float Alpha value
     */
    private function getAlpha(): float
    {
        return match ($this->registerCount) {
            16 => 0.673,
            32 => 0.697,
            64 => 0.709,
            default => 0.7213 / (1 + 1.079 / $this->registerCount)
        };
    }

    /**
     * Get memory usage in bytes
     *
     * @return int Memory usage
     */
    public function getMemoryUsage(): int
    {
        return $this->registerCount;
    }

    /**
     * Get statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        $nonZeroRegisters = count(array_filter($this->registers));

        return [
            'precision' => $this->precision,
            'registers' => $this->registerCount,
            'non_zero_registers' => $nonZeroRegisters,
            'fill_ratio' => $nonZeroRegisters / $this->registerCount,
            'estimated_count' => $this->count(),
            'memory_bytes' => $this->getMemoryUsage(),
            'memory_kb' => $this->getMemoryUsage() / 1024
        ];
    }
}

/**
 * Unique visitor counter using HyperLogLog
 */
class UniqueVisitorCounter
{
    private array $hourly = [];
    private array $daily = [];
    private HyperLogLog $total;

    public function __construct()
    {
        $this->total = new HyperLogLog(14);
    }

    /**
     * Track a visitor
     *
     * @param string $userId User identifier
     * @param \DateTime $timestamp Visit timestamp
     */
    public function trackVisit(string $userId, \DateTime $timestamp): void
    {
        $hour = $timestamp->format('Y-m-d-H');
        $day = $timestamp->format('Y-m-d');

        // Track in hourly bucket
        if (!isset($this->hourly[$hour])) {
            $this->hourly[$hour] = new HyperLogLog(12); // Less precision for hourly
        }
        $this->hourly[$hour]->add($userId);

        // Track in daily bucket
        if (!isset($this->daily[$day])) {
            $this->daily[$day] = new HyperLogLog(14);
        }
        $this->daily[$day]->add($userId);

        // Track in total
        $this->total->add($userId);
    }

    /**
     * Get unique visitors for a period
     *
     * @param string $period Period type: 'hour', 'day', 'total'
     * @param string $key Period key (e.g., '2025-01-01-10')
     * @return int Unique visitors
     */
    public function getUniqueVisitors(string $period, string $key = ''): int
    {
        return match ($period) {
            'hour' => $this->hourly[$key]->count() ?? 0,
            'day' => $this->daily[$key]->count() ?? 0,
            'total' => $this->total->count(),
            default => 0
        };
    }

    /**
     * Get unique visitors for date range
     *
     * @param \DateTime $start Start date
     * @param \DateTime $end End date
     * @return int Unique visitors
     */
    public function getUniqueVisitorsRange(\DateTime $start, \DateTime $end): int
    {
        $merged = new HyperLogLog(14);
        $current = clone $start;

        while ($current <= $end) {
            $day = $current->format('Y-m-d');

            if (isset($this->daily[$day])) {
                $merged->merge($this->daily[$day]);
            }

            $current->modify('+1 day');
        }

        return $merged->count();
    }

    /**
     * Get memory usage breakdown
     *
     * @return array Memory usage in bytes
     */
    public function getMemoryUsage(): array
    {
        return [
            'hourly' => count($this->hourly) * 4096,
            'daily' => count($this->daily) * 16384,
            'total' => 16384,
            'total_kb' => (count($this->hourly) * 4096 + count($this->daily) * 16384 + 16384) / 1024
        ];
    }
}

/**
 * Example usage demonstrating HyperLogLog
 */
function demonstrateHyperLogLog(): void
{
    echo "=== HyperLogLog Demo ===\n\n";

    // Test 1: Basic cardinality estimation
    echo "1. Basic Cardinality Estimation\n";
    $hll = new HyperLogLog(14);

    // Add unique items
    $uniqueItems = 100000;
    echo "Adding {$uniqueItems} unique items...\n";

    for ($i = 0; $i < $uniqueItems; $i++) {
        $hll->add("user_{$i}");
    }

    $estimated = $hll->count();
    $error = abs($estimated - $uniqueItems) / $uniqueItems * 100;

    echo "  Actual: {$uniqueItems}\n";
    echo "  Estimated: {$estimated}\n";
    echo "  Error: " . number_format($error, 2) . "%\n";
    echo "  Memory: " . number_format($hll->getMemoryUsage() / 1024, 2) . " KB\n\n";

    // Test 2: Precision analysis
    echo "2. Precision vs Memory Trade-off\n";
    $precisions = [10, 12, 14, 16];
    $testSize = 10000;

    printf("%-10s %-12s %-12s %-12s %-12s\n", "Precision", "Memory (KB)", "Estimated", "Error %", "Registers");
    echo str_repeat('-', 60) . "\n";

    foreach ($precisions as $precision) {
        $testHLL = new HyperLogLog($precision);

        for ($i = 0; $i < $testSize; $i++) {
            $testHLL->add("item_{$i}");
        }

        $est = $testHLL->count();
        $err = abs($est - $testSize) / $testSize * 100;
        $mem = $testHLL->getMemoryUsage() / 1024;
        $registers = 1 << $precision;

        printf("%-10d %-12.2f %-12d %-12.2f %-12d\n", $precision, $mem, $est, $err, $registers);
    }

    // Test 3: Merge operation
    echo "\n3. Merging HyperLogLogs\n";
    $hll1 = new HyperLogLog(12);
    $hll2 = new HyperLogLog(12);
    $hll3 = new HyperLogLog(12);

    // Add items to different HLLs with some overlap
    for ($i = 0; $i < 5000; $i++) {
        $hll1->add("user_{$i}");
    }

    for ($i = 4000; $i < 9000; $i++) {
        $hll2->add("user_{$i}");
    }

    for ($i = 8000; $i < 13000; $i++) {
        $hll3->add("user_{$i}");
    }

    echo "  HLL1 count: " . $hll1->count() . "\n";
    echo "  HLL2 count: " . $hll2->count() . "\n";
    echo "  HLL3 count: " . $hll3->count() . "\n";

    // Merge all
    $merged = new HyperLogLog(12);
    $merged->merge($hll1);
    $merged->merge($hll2);
    $merged->merge($hll3);

    $expectedUnion = 13000; // 0-12999
    echo "  Merged count: " . $merged->count() . " (expected ~{$expectedUnion})\n";

    // Test 4: Unique visitor counter
    echo "\n4. Unique Visitor Counter\n";
    $counter = new UniqueVisitorCounter();

    // Simulate visitors
    $dates = [
        new \DateTime('2025-01-01 10:00:00'),
        new \DateTime('2025-01-01 10:30:00'),
        new \DateTime('2025-01-01 11:00:00'),
        new \DateTime('2025-01-02 10:00:00'),
    ];

    $userIds = ['user_1', 'user_2', 'user_3', 'user_1', 'user_4', 'user_2', 'user_5'];

    echo "Tracking " . count($userIds) . " visits...\n";
    foreach ($userIds as $index => $userId) {
        $timestamp = $dates[$index % count($dates)];
        $counter->trackVisit($userId, $timestamp);
    }

    echo "\nUnique visitors:\n";
    echo "  Hour (2025-01-01-10): " . $counter->getUniqueVisitors('hour', '2025-01-01-10') . "\n";
    echo "  Hour (2025-01-01-11): " . $counter->getUniqueVisitors('hour', '2025-01-01-11') . "\n";
    echo "  Day (2025-01-01): " . $counter->getUniqueVisitors('day', '2025-01-01') . "\n";
    echo "  Day (2025-01-02): " . $counter->getUniqueVisitors('day', '2025-01-02') . "\n";
    echo "  Total: " . $counter->getUniqueVisitors('total') . "\n";

    // Range query
    $start = new \DateTime('2025-01-01');
    $end = new \DateTime('2025-01-02');
    echo "  Range (01-01 to 01-02): " . $counter->getUniqueVisitorsRange($start, $end) . "\n";

    $memory = $counter->getMemoryUsage();
    echo "\nMemory usage: " . number_format($memory['total_kb'], 2) . " KB\n";

    // Test 5: Accuracy analysis
    echo "\n5. Accuracy Analysis (10 trials)\n";
    $trials = 10;
    $sizes = [1000, 10000, 100000];

    foreach ($sizes as $size) {
        $errors = [];

        for ($trial = 0; $trial < $trials; $trial++) {
            $testHLL = new HyperLogLog(14);

            for ($i = 0; $i < $size; $i++) {
                $testHLL->add("item_{$trial}_{$i}");
            }

            $est = $testHLL->count();
            $errors[] = abs($est - $size) / $size * 100;
        }

        $avgError = array_sum($errors) / count($errors);
        $maxError = max($errors);
        $minError = min($errors);

        echo "  Size {$size}:\n";
        echo "    Average error: " . number_format($avgError, 2) . "%\n";
        echo "    Min error: " . number_format($minError, 2) . "%\n";
        echo "    Max error: " . number_format($maxError, 2) . "%\n";
    }
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateHyperLogLog();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
