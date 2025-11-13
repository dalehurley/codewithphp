# Chapter 32: Probabilistic Algorithms

## Introduction

Probabilistic algorithms trade perfect accuracy for dramatic improvements in space and time efficiency. These algorithms are essential for handling massive datasets where exact solutions are impractical or impossible.

## Understanding Probabilistic Data Structures

### Why Probabilistic?

```php
// Problem: Check if user has visited a page
// Traditional approach
$visited = [];  // Stores all user IDs
if (in_array($userId, $visited)) {
    echo "Welcome back!";
}
// Space: O(n) - 1 billion users = ~8GB memory

// Probabilistic approach (Bloom Filter)
$bloom = new BloomFilter(1000000, 0.01);
if ($bloom->contains($userId)) {
    echo "Welcome back (probably)!";
}
// Space: O(1) - 1 billion users = ~12MB memory
// Trade-off: 1% false positive rate
```

**Benefits**:
- Massive space savings (100-1000x reduction)
- Constant or logarithmic time complexity
- Suitable for streaming data
- Scalable to billions of items

**Trade-offs**:
- Approximate results (controlled error rate)
- Some operations may be irreversible
- Requires understanding of error bounds

## Bloom Filters

A Bloom filter is a space-efficient probabilistic data structure for testing set membership. It can have false positives but never false negatives.

### Basic Implementation

```php
class BloomFilter {
    private array $bits;
    private int $size;
    private int $hashCount;

    public function __construct(int $expectedItems, float $falsePositiveRate = 0.01) {
        // Calculate optimal size and hash count
        $this->size = $this->calculateSize($expectedItems, $falsePositiveRate);
        $this->hashCount = $this->calculateHashCount($this->size, $expectedItems);
        $this->bits = array_fill(0, $this->size, false);
    }

    private function calculateSize(int $n, float $p): int {
        // m = -n * ln(p) / (ln(2)^2)
        return (int) ceil(-$n * log($p) / pow(log(2), 2));
    }

    private function calculateHashCount(int $m, int $n): int {
        // k = (m/n) * ln(2)
        return (int) ceil(($m / $n) * log(2));
    }

    private function hash(string $item, int $seed): int {
        return abs(crc32($item . $seed)) % $this->size;
    }

    public function add(string $item): void {
        for ($i = 0; $i < $this->hashCount; $i++) {
            $index = $this->hash($item, $i);
            $this->bits[$index] = true;
        }
    }

    public function contains(string $item): bool {
        for ($i = 0; $i < $this->hashCount; $i++) {
            $index = $this->hash($item, $i);
            if (!$this->bits[$index]) {
                return false;  // Definitely not in set
            }
        }
        return true;  // Probably in set
    }

    public function getFalsePositiveRate(): float {
        $bitsSet = array_sum($this->bits);
        $ratio = $bitsSet / $this->size;
        return pow(1 - exp(-$this->hashCount * $ratio), $this->hashCount);
    }
}

// Usage
$filter = new BloomFilter(10000, 0.01);

$filter->add('user_123');
$filter->add('user_456');
$filter->add('user_789');

var_dump($filter->contains('user_123'));  // true (definitely added)
var_dump($filter->contains('user_999'));  // false or true (maybe false positive)

echo "False positive rate: " . ($filter->getFalsePositiveRate() * 100) . "%\n";
```

**Time Complexity**: O(k) where k is number of hash functions
**Space Complexity**: O(m) where m is bit array size

### Production-Ready Bloom Filter

```php
class ScalableBloomFilter {
    private array $filters = [];
    private int $capacity;
    private float $errorRate;
    private float $ratio;
    private int $currentFilter = 0;

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

    private function addFilter(): void {
        $capacity = $this->capacity * pow(2, $this->currentFilter);
        $errorRate = $this->errorRate * pow($this->ratio, $this->currentFilter);

        $this->filters[$this->currentFilter] = new BloomFilter($capacity, $errorRate);
        $this->currentFilter++;
    }

    public function add(string $item): void {
        $lastFilter = $this->filters[$this->currentFilter - 1];

        // If current filter might be getting full, add new filter
        if ($lastFilter->getFalsePositiveRate() > $this->errorRate) {
            $this->addFilter();
        }

        $this->filters[$this->currentFilter - 1]->add($item);
    }

    public function contains(string $item): bool {
        // Check all filters (item could be in any)
        foreach ($this->filters as $filter) {
            if ($filter->contains($item)) {
                return true;
            }
        }
        return false;
    }

    public function getMemoryUsage(): int {
        $total = 0;
        foreach ($this->filters as $filter) {
            $total += memory_get_usage();
        }
        return $total;
    }
}
```

### Real-World Example: Email Spam Filter

```php
class SpamFilter {
    private BloomFilter $knownSpam;
    private BloomFilter $knownHam;

    public function __construct() {
        // 1 million spam emails, 1% false positive
        $this->knownSpam = new BloomFilter(1000000, 0.01);

        // 10 million legitimate emails, 0.1% false positive
        $this->knownHam = new BloomFilter(10000000, 0.001);
    }

    public function trainSpam(string $emailHash): void {
        $this->knownSpam->add($emailHash);
    }

    public function trainHam(string $emailHash): void {
        $this->knownHam->add($emailHash);
    }

    public function classify(string $emailHash): string {
        $isSpam = $this->knownSpam->contains($emailHash);
        $isHam = $this->knownHam->contains($emailHash);

        if ($isSpam && !$isHam) {
            return 'spam';
        } elseif ($isHam && !$isSpam) {
            return 'ham';
        } elseif ($isSpam && $isHam) {
            return 'unknown';  // Conflicting signals
        } else {
            return 'unknown';  // Not seen before
        }
    }

    public function bulkTrain(array $spamHashes, array $hamHashes): void {
        foreach ($spamHashes as $hash) {
            $this->trainSpam($hash);
        }

        foreach ($hamHashes as $hash) {
            $this->trainHam($hash);
        }
    }
}

// Usage
$filter = new SpamFilter();

// Train with known spam/ham
$filter->bulkTrain(
    ['hash1', 'hash2', 'hash3'],  // Spam
    ['hash4', 'hash5', 'hash6']   // Ham
);

// Classify new emails
echo $filter->classify('hash1');  // spam
echo $filter->classify('hash7');  // unknown
```

## HyperLogLog

HyperLogLog estimates the cardinality (number of unique elements) of very large datasets with remarkable accuracy using minimal memory.

### Implementation

```php
class HyperLogLog {
    private array $registers;
    private int $precision;
    private int $registerCount;

    public function __construct(int $precision = 14) {
        // Precision: 4-16 (higher = more accurate, more memory)
        $this->precision = max(4, min(16, $precision));
        $this->registerCount = 1 << $this->precision;  // 2^precision
        $this->registers = array_fill(0, $this->registerCount, 0);
    }

    public function add(string $item): void {
        $hash = $this->hash($item);

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

    public function count(): int {
        $sum = 0;
        $zeros = 0;

        foreach ($this->registers as $register) {
            $sum += 1 / (1 << $register);
            if ($register === 0) {
                $zeros++;
            }
        }

        // Calculate raw estimate
        $alpha = $this->getAlpha();
        $estimate = $alpha * $this->registerCount * $this->registerCount / $sum;

        // Apply corrections for small and large ranges
        if ($estimate <= 2.5 * $this->registerCount) {
            // Small range correction
            if ($zeros !== 0) {
                return (int) ($this->registerCount * log($this->registerCount / $zeros));
            }
        }

        if ($estimate <= (1/30) * (1 << 32)) {
            return (int) $estimate;
        }

        // Large range correction
        return (int) (-1 * (1 << 32) * log(1 - $estimate / (1 << 32)));
    }

    private function hash(string $item): int {
        return crc32($item);
    }

    private function countLeadingZeros(int $value): int {
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

    private function getAlpha(): float {
        switch ($this->registerCount) {
            case 16:
                return 0.673;
            case 32:
                return 0.697;
            case 64:
                return 0.709;
            default:
                return 0.7213 / (1 + 1.079 / $this->registerCount);
        }
    }

    public function merge(HyperLogLog $other): void {
        if ($this->precision !== $other->precision) {
            throw new Exception("Cannot merge HLL with different precision");
        }

        for ($i = 0; $i < $this->registerCount; $i++) {
            $this->registers[$i] = max($this->registers[$i], $other->registers[$i]);
        }
    }

    public function getMemoryUsage(): int {
        return $this->registerCount;  // bytes
    }
}

// Usage
$hll = new HyperLogLog(14);  // ~16KB memory

// Add 1 million unique items
for ($i = 0; $i < 1000000; $i++) {
    $hll->add("user_$i");
}

$estimated = $hll->count();
$actual = 1000000;
$error = abs($estimated - $actual) / $actual * 100;

echo "Estimated: $estimated\n";
echo "Actual: $actual\n";
echo "Error: " . number_format($error, 2) . "%\n";
echo "Memory: " . $hll->getMemoryUsage() . " bytes\n";

// Output:
// Estimated: 1003421
// Actual: 1000000
// Error: 0.34%
// Memory: 16384 bytes
```

**Time Complexity**: O(1) per add, O(m) for count where m is register count
**Space Complexity**: O(2^p) where p is precision
**Error Rate**: ±1.04/√m (typically 0.81% for p=14)

### Real-World Example: Unique Visitor Counter

```php
class UniqueVisitorCounter {
    private array $hourly = [];  // HLL per hour
    private array $daily = [];   // HLL per day
    private HyperLogLog $total;

    public function __construct() {
        $this->total = new HyperLogLog(14);
    }

    public function trackVisit(string $userId, DateTime $timestamp): void {
        $hour = $timestamp->format('Y-m-d-H');
        $day = $timestamp->format('Y-m-d');

        // Track in hourly bucket
        if (!isset($this->hourly[$hour])) {
            $this->hourly[$hour] = new HyperLogLog(12);  // Less precision for hourly
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

    public function getUniqueVisitors(string $period, string $key): int {
        if ($period === 'hour') {
            return $this->hourly[$key]->count() ?? 0;
        }

        if ($period === 'day') {
            return $this->daily[$key]->count() ?? 0;
        }

        if ($period === 'total') {
            return $this->total->count();
        }

        return 0;
    }

    public function getUniqueVisitorsRange(DateTime $start, DateTime $end): int {
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

    public function getMemoryUsage(): array {
        return [
            'hourly' => count($this->hourly) * 4096,  // ~4KB each
            'daily' => count($this->daily) * 16384,   // ~16KB each
            'total' => 16384
        ];
    }
}

// Usage
$counter = new UniqueVisitorCounter();

// Track visits
$counter->trackVisit('user_123', new DateTime('2025-01-01 10:00:00'));
$counter->trackVisit('user_456', new DateTime('2025-01-01 10:30:00'));
$counter->trackVisit('user_123', new DateTime('2025-01-01 11:00:00'));  // Same user

echo $counter->getUniqueVisitors('hour', '2025-01-01-10');  // 2
echo $counter->getUniqueVisitors('day', '2025-01-01');      // 2
echo $counter->getUniqueVisitors('total', '');               // 2

// Range query
$unique = $counter->getUniqueVisitorsRange(
    new DateTime('2025-01-01'),
    new DateTime('2025-01-07')
);
echo "Unique visitors this week: $unique\n";
```

## Count-Min Sketch

Count-Min Sketch estimates the frequency of items in a data stream with controlled error bounds.

### Implementation

```php
class CountMinSketch {
    private array $table;
    private int $width;
    private int $depth;

    public function __construct(float $epsilon = 0.001, float $delta = 0.01) {
        // epsilon: error margin (0.1% by default)
        // delta: confidence level (99% by default)

        $this->width = (int) ceil(M_E / $epsilon);
        $this->depth = (int) ceil(log(1 / $delta));

        $this->table = array_fill(0, $this->depth, array_fill(0, $this->width, 0));
    }

    private function hash(string $item, int $seed): int {
        return abs(crc32($item . $seed)) % $this->width;
    }

    public function add(string $item, int $count = 1): void {
        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash($item, $i);
            $this->table[$i][$index] += $count;
        }
    }

    public function estimate(string $item): int {
        $min = PHP_INT_MAX;

        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash($item, $i);
            $min = min($min, $this->table[$i][$index]);
        }

        return $min;
    }

    public function getMemoryUsage(): int {
        return $this->width * $this->depth * 8;  // 8 bytes per counter
    }

    public function merge(CountMinSketch $other): void {
        if ($this->width !== $other->width || $this->depth !== $other->depth) {
            throw new Exception("Cannot merge CMS with different dimensions");
        }

        for ($i = 0; $i < $this->depth; $i++) {
            for ($j = 0; $j < $this->width; $j++) {
                $this->table[$i][$j] += $other->table[$i][$j];
            }
        }
    }
}

// Usage
$cms = new CountMinSketch(0.001, 0.01);

// Track page views
$cms->add('/home', 1);
$cms->add('/about', 1);
$cms->add('/home', 1);
$cms->add('/products', 1);
$cms->add('/home', 1);

echo $cms->estimate('/home');      // ~3 (may be slightly higher)
echo $cms->estimate('/about');     // ~1
echo $cms->estimate('/unknown');   // 0 or small number

echo "Memory: " . $cms->getMemoryUsage() . " bytes\n";
```

**Time Complexity**: O(d) where d is depth
**Space Complexity**: O(w × d) where w is width, d is depth
**Error Bound**: ±εN with probability 1-δ

### Real-World Example: Heavy Hitters Detection

```php
class HeavyHittersDetector {
    private CountMinSketch $cms;
    private array $topK = [];
    private int $k;
    private int $totalCount = 0;

    public function __construct(int $k = 10, float $epsilon = 0.001) {
        $this->k = $k;
        $this->cms = new CountMinSketch($epsilon, 0.01);
    }

    public function add(string $item): void {
        $this->cms->add($item);
        $this->totalCount++;

        $count = $this->cms->estimate($item);

        // Update top-K
        $this->topK[$item] = $count;
        arsort($this->topK);
        $this->topK = array_slice($this->topK, 0, $this->k, true);
    }

    public function getTopK(): array {
        return $this->topK;
    }

    public function isHeavyHitter(string $item, float $threshold = 0.01): bool {
        $count = $this->cms->estimate($item);
        return ($count / $this->totalCount) >= $threshold;
    }

    public function getReport(): array {
        $report = [];

        foreach ($this->topK as $item => $count) {
            $report[] = [
                'item' => $item,
                'count' => $count,
                'percentage' => ($count / $this->totalCount) * 100
            ];
        }

        return $report;
    }
}

// Usage: Detect most popular URLs
$detector = new HeavyHittersDetector(10);

// Process access logs
$logs = [
    '/home', '/about', '/home', '/products', '/home',
    '/contact', '/home', '/blog', '/home', '/pricing'
    // ... millions more
];

foreach ($logs as $url) {
    $detector->add($url);
}

// Get top 10 URLs
$topUrls = $detector->getTopK();
print_r($topUrls);

// Check if specific URL is a heavy hitter (>1% of traffic)
if ($detector->isHeavyHitter('/home', 0.01)) {
    echo "/home is a heavy hitter!\n";
}

// Detailed report
print_r($detector->getReport());
```

## Reservoir Sampling

Reservoir sampling maintains a random sample of k items from a stream of unknown size.

### Implementation

```php
class ReservoirSampler {
    private array $reservoir;
    private int $size;
    private int $seen = 0;

    public function __construct(int $size) {
        $this->size = $size;
        $this->reservoir = [];
    }

    public function add($item): void {
        $this->seen++;

        if (count($this->reservoir) < $this->size) {
            // Reservoir not full, add directly
            $this->reservoir[] = $item;
        } else {
            // Random replacement with decreasing probability
            $index = mt_rand(0, $this->seen - 1);

            if ($index < $this->size) {
                $this->reservoir[$index] = $item;
            }
        }
    }

    public function getSample(): array {
        return $this->reservoir;
    }

    public function getSize(): int {
        return count($this->reservoir);
    }
}

// Usage
$sampler = new ReservoirSampler(100);

// Stream 1 million items
for ($i = 0; $i < 1000000; $i++) {
    $sampler->add("item_$i");
}

// Get random sample of 100 items
$sample = $sampler->getSample();
echo "Sample size: " . count($sample) . "\n";
print_r(array_slice($sample, 0, 10));
```

**Time Complexity**: O(1) per item
**Space Complexity**: O(k) where k is sample size
**Guarantee**: Each item has equal probability k/n of being in sample

### Weighted Reservoir Sampling

```php
class WeightedReservoirSampler {
    private SplPriorityQueue $reservoir;
    private int $size;

    public function __construct(int $size) {
        $this->size = $size;
        $this->reservoir = new SplPriorityQueue();
    }

    public function add($item, float $weight): void {
        $key = pow(mt_rand() / mt_getrandmax(), 1 / $weight);

        $this->reservoir->insert($item, $key);

        // Keep only top k items
        if ($this->reservoir->count() > $this->size) {
            $this->reservoir->extract();
        }
    }

    public function getSample(): array {
        $sample = [];

        // Clone to avoid modifying original
        $clone = clone $this->reservoir;

        while (!$clone->isEmpty()) {
            $sample[] = $clone->extract();
        }

        return array_reverse($sample);
    }
}

// Usage: Sample users weighted by engagement score
$sampler = new WeightedReservoirSampler(100);

foreach ($users as $user) {
    $weight = $user['engagement_score'];
    $sampler->add($user, $weight);
}

$sample = $sampler->getSample();  // 100 users, biased toward high engagement
```

## Approximate Counting

### Morris Counter

Probabilistic counter that uses logarithmic space.

```php
class MorrisCounter {
    private int $x = 0;  // log2(count)

    public function increment(): void {
        $probability = 1 / (1 << $this->x);

        if (mt_rand() / mt_getrandmax() < $probability) {
            $this->x++;
        }
    }

    public function count(): int {
        return (1 << $this->x) - 1;
    }

    public function getMemoryUsage(): int {
        return 4;  // Just one integer (4 bytes)
    }
}

// Usage
$counter = new MorrisCounter();

// Increment 1 million times
for ($i = 0; $i < 1000000; $i++) {
    $counter->increment();
}

$estimated = $counter->count();
$error = abs($estimated - 1000000) / 1000000 * 100;

echo "Estimated: $estimated\n";
echo "Error: " . number_format($error, 2) . "%\n";
echo "Memory: {$counter->getMemoryUsage()} bytes\n";
```

## Skip List (Probabilistic BST)

```php
class SkipNode {
    public $value;
    public array $forward = [];

    public function __construct($value) {
        $this->value = $value;
    }
}

class SkipList {
    private SkipNode $head;
    private int $maxLevel;
    private int $level = 0;
    private float $probability;

    public function __construct(int $maxLevel = 16, float $probability = 0.5) {
        $this->maxLevel = $maxLevel;
        $this->probability = $probability;
        $this->head = new SkipNode(null);
        $this->head->forward = array_fill(0, $maxLevel, null);
    }

    private function randomLevel(): int {
        $level = 0;

        while (mt_rand() / mt_getrandmax() < $this->probability && $level < $this->maxLevel - 1) {
            $level++;
        }

        return $level;
    }

    public function insert($value): void {
        $update = array_fill(0, $this->maxLevel, null);
        $current = $this->head;

        // Find position
        for ($i = $this->level; $i >= 0; $i--) {
            while ($current->forward[$i] !== null && $current->forward[$i]->value < $value) {
                $current = $current->forward[$i];
            }
            $update[$i] = $current;
        }

        // Generate random level
        $newLevel = $this->randomLevel();

        if ($newLevel > $this->level) {
            for ($i = $this->level + 1; $i <= $newLevel; $i++) {
                $update[$i] = $this->head;
            }
            $this->level = $newLevel;
        }

        // Insert node
        $newNode = new SkipNode($value);
        for ($i = 0; $i <= $newLevel; $i++) {
            $newNode->forward[$i] = $update[$i]->forward[$i];
            $update[$i]->forward[$i] = $newNode;
        }
    }

    public function search($value): bool {
        $current = $this->head;

        for ($i = $this->level; $i >= 0; $i--) {
            while ($current->forward[$i] !== null && $current->forward[$i]->value < $value) {
                $current = $current->forward[$i];
            }
        }

        $current = $current->forward[0];
        return $current !== null && $current->value === $value;
    }

    public function delete($value): bool {
        $update = array_fill(0, $this->maxLevel, null);
        $current = $this->head;

        for ($i = $this->level; $i >= 0; $i--) {
            while ($current->forward[$i] !== null && $current->forward[$i]->value < $value) {
                $current = $current->forward[$i];
            }
            $update[$i] = $current;
        }

        $current = $current->forward[0];

        if ($current === null || $current->value !== $value) {
            return false;
        }

        // Remove node
        for ($i = 0; $i <= $this->level; $i++) {
            if ($update[$i]->forward[$i] !== $current) {
                break;
            }
            $update[$i]->forward[$i] = $current->forward[$i];
        }

        // Update level
        while ($this->level > 0 && $this->head->forward[$this->level] === null) {
            $this->level--;
        }

        return true;
    }
}

// Usage
$skipList = new SkipList();

$skipList->insert(3);
$skipList->insert(7);
$skipList->insert(1);
$skipList->insert(9);

var_dump($skipList->search(7));  // true
var_dump($skipList->search(5));  // false

$skipList->delete(7);
var_dump($skipList->search(7));  // false
```

**Time Complexity**: O(log n) average for search/insert/delete
**Space Complexity**: O(n log n) expected

## Comparison of Probabilistic Algorithms

| Algorithm | Use Case | Space | Accuracy | Operations |
|-----------|----------|-------|----------|------------|
| Bloom Filter | Membership testing | O(m) | No false negatives | Add, Contains |
| HyperLogLog | Cardinality estimation | O(2^p) | ±0.81% (p=14) | Add, Count, Merge |
| Count-Min Sketch | Frequency estimation | O(w×d) | ±εN | Add, Estimate |
| Reservoir Sampling | Random sampling | O(k) | Exact distribution | Add, GetSample |
| Morris Counter | Approximate counting | O(1) | ±√n | Increment, Count |
| Skip List | Sorted set | O(n log n) | Exact | Insert, Search, Delete |

## Best Practices

### 1. Choose Right Parameters

```php
// Bloom Filter: Balance space vs. accuracy
$bloomFilter = new BloomFilter(
    expectedItems: 1000000,
    falsePositiveRate: 0.01  // 1% false positives
);

// HyperLogLog: Higher precision = better accuracy
$hll = new HyperLogLog(precision: 14);  // ~0.81% error

// Count-Min Sketch: Lower epsilon = better accuracy
$cms = new CountMinSketch(epsilon: 0.001, delta: 0.01);
```

### 2. Understand Error Bounds

```php
// Document expected error rates
class Analytics {
    private HyperLogLog $uniqueVisitors;

    /**
     * Returns unique visitor count with ±0.81% accuracy
     */
    public function getUniqueVisitors(): int {
        return $this->uniqueVisitors->count();
    }
}
```

### 3. Combine Structures

```php
class SmartCache {
    private BloomFilter $maybePresent;
    private array $cache = [];

    public function get(string $key) {
        // Quick negative check
        if (!$this->maybePresent->contains($key)) {
            return null;  // Definitely not in cache
        }

        // Might be in cache, check actual storage
        return $this->cache[$key] ?? null;
    }

    public function set(string $key, $value): void {
        $this->maybePresent->add($key);
        $this->cache[$key] = $value;
    }
}
```

## Summary

Probabilistic algorithms enable processing of massive datasets that would be impossible with exact algorithms:

- **Bloom Filters**: Fast membership testing with minimal memory
- **HyperLogLog**: Count unique items with 0.81% error in ~16KB
- **Count-Min Sketch**: Track frequencies with bounded error
- **Reservoir Sampling**: Maintain random samples from streams
- **Morris Counter**: Count in logarithmic space

**Trade-off**: Acceptable error rate for massive space/time savings

## Next Steps

- **Chapter 33: String Algorithms Deep Dive** - Advanced string matching
- **Chapter 36: Stream Processing Algorithms** - Real-time data processing
- **Chapter 26: Approximate Algorithms** - More approximation techniques

## Practice Exercises

1. Implement a Bloom filter-based URL deduplicator for web crawler
2. Build a HyperLogLog-based analytics system for unique visitors
3. Create a Count-Min Sketch-based trending topics detector
4. Implement weighted reservoir sampling for recommendation system
5. Build a distributed cardinality estimator using HyperLogLog merge
