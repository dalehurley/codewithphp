<?php
/**
 * Bloom Filter Implementation
 *
 * A space-efficient probabilistic data structure for testing set membership.
 * Can have false positives but never false negatives.
 *
 * Use cases:
 * - Cache filtering
 * - Spam detection
 * - Database query optimization
 * - URL deduplication
 *
 * @category  Algorithms
 * @package   ProbabilisticAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter32;

/**
 * Bloom filter for membership testing
 */
class BloomFilter
{
    private array $bits;
    private int $size;
    private int $hashCount;

    /**
     * @param int $expectedItems Expected number of items
     * @param float $falsePositiveRate Desired false positive rate (0-1)
     */
    public function __construct(int $expectedItems, float $falsePositiveRate = 0.01)
    {
        $this->size = $this->calculateSize($expectedItems, $falsePositiveRate);
        $this->hashCount = $this->calculateHashCount($this->size, $expectedItems);
        $this->bits = array_fill(0, $this->size, false);

        echo "Bloom Filter initialized: {$this->size} bits, {$this->hashCount} hash functions\n";
        echo "Memory usage: " . number_format($this->size / 8 / 1024, 2) . " KB\n\n";
    }

    /**
     * Calculate optimal bit array size
     *
     * Formula: m = -(n * ln(p)) / (ln(2)^2)
     * where n = number of items, p = false positive rate
     *
     * @param int $n Expected items
     * @param float $p False positive rate
     * @return int Optimal size
     */
    private function calculateSize(int $n, float $p): int
    {
        return (int)ceil(-($n * log($p)) / pow(log(2), 2));
    }

    /**
     * Calculate optimal number of hash functions
     *
     * Formula: k = (m/n) * ln(2)
     *
     * @param int $m Bit array size
     * @param int $n Number of items
     * @return int Optimal hash count
     */
    private function calculateHashCount(int $m, int $n): int
    {
        return (int)max(1, round(($m / $n) * log(2)));
    }

    /**
     * Hash function for item with seed
     *
     * @param string $item Item to hash
     * @param int $seed Hash seed
     * @return int Hash value
     */
    private function hash(string $item, int $seed): int
    {
        // Use different hash algorithms for different seeds
        $hashFunctions = [
            fn($s) => abs(crc32($s)),
            fn($s) => abs(crc32(strrev($s))),
            fn($s) => abs(hash('fnv1a32', $s, false)),
            fn($s) => abs(hash('adler32', $s, false)),
        ];

        $hashFunc = $hashFunctions[$seed % count($hashFunctions)];
        $hash = $hashFunc($item . $seed);

        return $hash % $this->size;
    }

    /**
     * Add item to the filter
     *
     * @param string $item Item to add
     */
    public function add(string $item): void
    {
        for ($i = 0; $i < $this->hashCount; $i++) {
            $index = $this->hash($item, $i);
            $this->bits[$index] = true;
        }
    }

    /**
     * Check if item might be in the set
     *
     * @param string $item Item to check
     * @return bool True if possibly in set, false if definitely not
     */
    public function contains(string $item): bool
    {
        for ($i = 0; $i < $this->hashCount; $i++) {
            $index = $this->hash($item, $i);
            if (!$this->bits[$index]) {
                return false; // Definitely not in set
            }
        }
        return true; // Probably in set
    }

    /**
     * Get current false positive rate
     *
     * @return float Estimated false positive rate
     */
    public function getFalsePositiveRate(): float
    {
        $bitsSet = array_sum($this->bits);
        $ratio = $bitsSet / $this->size;

        // Formula: (1 - e^(-k*n/m))^k
        return pow(1 - exp(-$this->hashCount * $ratio), $this->hashCount);
    }

    /**
     * Get memory usage in bytes
     *
     * @return int Memory usage
     */
    public function getMemoryUsage(): int
    {
        return (int)ceil($this->size / 8);
    }

    /**
     * Get statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        $bitsSet = array_sum($this->bits);

        return [
            'size' => $this->size,
            'hash_functions' => $this->hashCount,
            'bits_set' => $bitsSet,
            'fill_ratio' => $bitsSet / $this->size,
            'false_positive_rate' => $this->getFalsePositiveRate(),
            'memory_bytes' => $this->getMemoryUsage(),
            'memory_kb' => $this->getMemoryUsage() / 1024
        ];
    }
}

/**
 * Scalable Bloom Filter that grows automatically
 */
class ScalableBloomFilter
{
    private array $filters = [];
    private int $capacity;
    private float $errorRate;
    private float $ratio;
    private int $currentFilter = 0;

    /**
     * @param int $initialCapacity Initial capacity
     * @param float $errorRate Target error rate
     * @param float $ratio Error rate reduction ratio for new filters
     */
    public function __construct(
        int $initialCapacity = 10000,
        float $errorRate = 0.01,
        float $ratio = 0.9
    ) {
        $this->capacity = $initialCapacity;
        $this->errorRate = $errorRate;
        $this->ratio = $ratio;
        $this->addFilter();
    }

    /**
     * Add a new filter to the chain
     */
    private function addFilter(): void
    {
        $capacity = $this->capacity * (2 ** $this->currentFilter);
        $errorRate = $this->errorRate * ($this->ratio ** $this->currentFilter);

        $this->filters[$this->currentFilter] = new BloomFilter($capacity, $errorRate);
        $this->currentFilter++;

        echo "Added filter #{$this->currentFilter} (capacity: {$capacity})\n";
    }

    /**
     * Add item to the filter
     *
     * @param string $item Item to add
     */
    public function add(string $item): void
    {
        $lastFilter = $this->filters[$this->currentFilter - 1];

        // Check if we need to add a new filter
        if ($lastFilter->getFalsePositiveRate() > $this->errorRate) {
            $this->addFilter();
        }

        $this->filters[$this->currentFilter - 1]->add($item);
    }

    /**
     * Check if item might be in any filter
     *
     * @param string $item Item to check
     * @return bool True if possibly in set
     */
    public function contains(string $item): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->contains($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get total memory usage
     *
     * @return int Total memory in bytes
     */
    public function getMemoryUsage(): int
    {
        $total = 0;
        foreach ($this->filters as $filter) {
            $total += $filter->getMemoryUsage();
        }
        return $total;
    }
}

/**
 * Example: Spam email filter using Bloom filter
 */
class SpamFilter
{
    private BloomFilter $knownSpam;
    private BloomFilter $knownHam;

    public function __construct()
    {
        // 1 million spam emails, 1% false positive
        $this->knownSpam = new BloomFilter(1000000, 0.01);

        // 10 million legitimate emails, 0.1% false positive
        $this->knownHam = new BloomFilter(10000000, 0.001);
    }

    /**
     * Train with spam email hash
     *
     * @param string $emailHash Email hash
     */
    public function trainSpam(string $emailHash): void
    {
        $this->knownSpam->add($emailHash);
    }

    /**
     * Train with legitimate email hash
     *
     * @param string $emailHash Email hash
     */
    public function trainHam(string $emailHash): void
    {
        $this->knownHam->add($emailHash);
    }

    /**
     * Classify email
     *
     * @param string $emailHash Email hash
     * @return string Classification: 'spam', 'ham', or 'unknown'
     */
    public function classify(string $emailHash): string
    {
        $isSpam = $this->knownSpam->contains($emailHash);
        $isHam = $this->knownHam->contains($emailHash);

        if ($isSpam && !$isHam) {
            return 'spam';
        } elseif ($isHam && !$isSpam) {
            return 'ham';
        } elseif ($isSpam && $isHam) {
            return 'unknown'; // Conflicting signals
        } else {
            return 'unknown'; // Not seen before
        }
    }
}

/**
 * Example usage demonstrating Bloom filters
 */
function demonstrateBloomFilter(): void
{
    echo "=== Bloom Filter Demo ===\n\n";

    // Test 1: Basic Bloom filter
    echo "1. Basic Bloom Filter\n";
    $filter = new BloomFilter(10000, 0.01);

    // Add items
    $items = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
    foreach ($items as $item) {
        $filter->add($item);
    }

    // Test membership
    $testItems = ['apple', 'banana', 'fig', 'grape', 'cherry', 'kiwi'];
    echo "Testing membership:\n";
    foreach ($testItems as $item) {
        $exists = $filter->contains($item);
        $actual = in_array($item, $items);
        $symbol = ($exists && $actual) ? '✓' : ($exists && !$actual ? '?' : '✗');
        echo "  {$symbol} {$item}: " . ($exists ? 'might exist' : 'definitely not') . "\n";
    }

    echo "\nStatistics:\n";
    $stats = $filter->getStats();
    foreach ($stats as $key => $value) {
        if (is_float($value)) {
            echo "  {$key}: " . number_format($value, 4) . "\n";
        } else {
            echo "  {$key}: {$value}\n";
        }
    }

    // Test 2: False positive rate analysis
    echo "\n2. False Positive Rate Analysis\n";
    $sizes = [1000, 10000, 100000];

    foreach ($sizes as $size) {
        $bf = new BloomFilter($size, 0.01);

        // Add items
        for ($i = 0; $i < $size; $i++) {
            $bf->add("item_{$i}");
        }

        // Test false positives
        $falsePositives = 0;
        $tests = 10000;

        for ($i = $size; $i < $size + $tests; $i++) {
            if ($bf->contains("item_{$i}")) {
                $falsePositives++;
            }
        }

        $actualFPR = $falsePositives / $tests;
        $predictedFPR = $bf->getFalsePositiveRate();

        echo "  Size {$size}:\n";
        echo "    Predicted FPR: " . number_format($predictedFPR * 100, 2) . "%\n";
        echo "    Actual FPR: " . number_format($actualFPR * 100, 2) . "%\n";
        echo "    Memory: " . number_format($bf->getMemoryUsage() / 1024, 2) . " KB\n\n";
    }

    // Test 3: Spam filter
    echo "3. Spam Filter Example\n";
    $spamFilter = new SpamFilter();

    // Train with known spam
    $spamEmails = ['spam1@bad.com', 'spam2@bad.com', 'spam3@bad.com'];
    foreach ($spamEmails as $email) {
        $spamFilter->trainSpam(hash('sha256', $email));
    }

    // Train with known ham
    $hamEmails = ['good1@nice.com', 'good2@nice.com', 'good3@nice.com'];
    foreach ($hamEmails as $email) {
        $spamFilter->trainHam(hash('sha256', $email));
    }

    // Test classification
    $testEmails = array_merge($spamEmails, $hamEmails, ['new@unknown.com']);

    echo "Email classification:\n";
    foreach ($testEmails as $email) {
        $hash = hash('sha256', $email);
        $classification = $spamFilter->classify($hash);
        echo "  {$email}: {$classification}\n";
    }

    // Test 4: Scalable Bloom filter
    echo "\n4. Scalable Bloom Filter\n";
    $scalable = new ScalableBloomFilter(1000, 0.01, 0.9);

    // Add many items
    for ($i = 0; $i < 5000; $i++) {
        $scalable->add("item_{$i}");
    }

    echo "\nTotal memory usage: " . number_format($scalable->getMemoryUsage() / 1024, 2) . " KB\n";

    // Test containment
    $testCount = 100;
    $found = 0;
    for ($i = 0; $i < $testCount; $i++) {
        if ($scalable->contains("item_{$i}")) {
            $found++;
        }
    }
    echo "Found {$found}/{$testCount} test items\n";
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateBloomFilter();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
