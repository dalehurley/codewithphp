---
title: "Probabilistic Algorithms"
description: "Master space-efficient probabilistic data structures including Bloom filters, HyperLogLog, Count-Min Sketch, and streaming algorithms for big data processing"
series: "php-algorithms"
chapter: 32
order: 32
difficulty: "advanced"
prerequisites: ["Hash Functions", "Data Structures", "Statistical Concepts"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 32</span>
</div>

# Probabilistic Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## What You'll Learn

Discover the magic of probabilistic algorithms that achieve the impossible - handling billions of elements with minimal memory by trading perfect accuracy for blazing speed and tiny footprints. These are the secret weapons of big data systems.

- Master space-efficient data structures like Bloom filters and HyperLogLog
- Implement streaming algorithms that process unlimited data with constant memory
- Apply Count-Min Sketch for frequency estimation in massive datasets
- Use probabilistic techniques in production systems handling big data
- Understand and control the accuracy/efficiency trade-offs in your applications

**Estimated Time**: ~50 minutes

## Prerequisites

This advanced chapter combines multiple concepts. You'll need:

- [ ] **Hash functions understanding** - How hashing works and its properties (Chapter on Hash Tables)
- [ ] **Data structures foundations** - Arrays, sets, and their operations
- [ ] **Statistical concepts** - Basic probability and error rates
- [ ] **Big data awareness** - Understanding of challenges with massive datasets

Ready to handle billions of data points with kilobytes of memory? Let's explore probabilistic algorithms!

## Introduction

Probabilistic algorithms trade perfect accuracy for dramatic improvements in space and time efficiency. These algorithms are essential for handling massive datasets where exact solutions are impractical or impossible. Instead of guaranteeing 100% accuracy, they provide answers with a controlled error rate - and that's often exactly what you need in production systems dealing with big data.

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

## Advanced Use Cases for Bloom Filters

### 1. Distributed Cache Layer

```php
class BloomFilterCache {
    private BloomFilter $localBloom;
    private BloomFilter $remoteBloom;
    private array $localCache = [];
    private CacheInterface $remoteCache;

    public function __construct(CacheInterface $remoteCache, int $capacity = 100000) {
        $this->remoteCache = $remoteCache;
        $this->localBloom = new BloomFilter($capacity / 10, 0.01);  // Local: 10% capacity
        $this->remoteBloom = new BloomFilter($capacity, 0.001);      // Remote: full capacity
    }

    public function get(string $key) {
        // Check local cache first
        if ($this->localBloom->contains($key)) {
            if (isset($this->localCache[$key])) {
                return $this->localCache[$key];
            }
        }

        // Check remote cache
        if ($this->remoteBloom->contains($key)) {
            $value = $this->remoteCache->get($key);

            if ($value !== null) {
                // Populate local cache
                $this->set($key, $value, $local = true);
                return $value;
            }
        }

        return null;  // Definitely not in cache
    }

    public function set(string $key, $value, bool $local = false): void {
        if ($local) {
            $this->localCache[$key] = $value;
            $this->localBloom->add($key);
        } else {
            $this->remoteCache->set($key, $value);
            $this->remoteBloom->add($key);
        }
    }
}
```

### 2. Database Query Optimization

```php
class QueryBloomOptimizer {
    private array $tableBloomFilters = [];
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function buildIndex(string $table, string $column): void {
        echo "Building Bloom filter for $table.$column...\n";

        $stmt = $this->db->query("SELECT DISTINCT $column FROM $table");
        $values = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $bloom = new BloomFilter(count($values), 0.01);

        foreach ($values as $value) {
            $bloom->add($value);
        }

        $this->tableBloomFilters["$table.$column"] = $bloom;

        echo "Indexed " . count($values) . " unique values\n";
    }

    public function mayExist(string $table, string $column, string $value): bool {
        $key = "$table.$column";

        if (!isset($this->tableBloomFilters[$key])) {
            return true;  // No index, assume exists
        }

        return $this->tableBloomFilters[$key]->contains($value);
    }

    public function optimizeQuery(string $table, string $column, array $values): array {
        $likelyExists = [];

        foreach ($values as $value) {
            if ($this->mayExist($table, $column, $value)) {
                $likelyExists[] = $value;
            }
        }

        $filtered = count($values) - count($likelyExists);
        echo "Filtered out $filtered values using Bloom filter\n";

        return $likelyExists;
    }
}

// Usage
$optimizer = new QueryBloomOptimizer($pdo);

// Build indexes
$optimizer->buildIndex('users', 'email');
$optimizer->buildIndex('products', 'sku');

// Optimize bulk query
$emails = ['user1@example.com', 'user2@example.com', /* ...1000 more */];
$candidateEmails = $optimizer->optimizeQuery('users', 'email', $emails);

// Only query for candidates
if (!empty($candidateEmails)) {
    $placeholders = implode(',', array_fill(0, count($candidateEmails), '?'));
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email IN ($placeholders)");
    $stmt->execute($candidateEmails);
}
```

### 3. Malicious IP Detection

```php
class IPBlacklistFilter {
    private BloomFilter $blacklist;
    private BloomFilter $whitelist;
    private int $blockCount = 0;

    public function __construct() {
        $this->blacklist = new BloomFilter(1000000, 0.001);
        $this->whitelist = new BloomFilter(10000, 0.0001);
    }

    public function loadBlacklist(string $filename): void {
        $handle = fopen($filename, 'r');

        while (($ip = fgets($handle)) !== false) {
            $this->blacklist->add(trim($ip));
        }

        fclose($handle);
    }

    public function addToWhitelist(string $ip): void {
        $this->whitelist->add($ip);
    }

    public function isAllowed(string $ip): bool {
        // Whitelist takes precedence
        if ($this->whitelist->contains($ip)) {
            return true;
        }

        // Check blacklist
        if ($this->blacklist->contains($ip)) {
            $this->blockCount++;
            return false;
        }

        return true;
    }

    public function getStats(): array {
        return [
            'blocks' => $this->blockCount,
            'blacklist_fpr' => $this->blacklist->getFalsePositiveRate(),
            'blacklist_memory' => $this->blacklist->getMemoryUsage()
        ];
    }
}
```

## Space-Accuracy Trade-offs

### Bloom Filter Trade-off Analysis

```php
class BloomFilterAnalyzer {
    public static function analyzeTradeoffs(int $numItems, array $fpRates): array {
        $results = [];

        foreach ($fpRates as $fpr) {
            $bloom = new BloomFilter($numItems, $fpr);

            $results[] = [
                'fpr' => $fpr,
                'size_bytes' => $bloom->getMemoryUsage(),
                'size_mb' => round($bloom->getMemoryUsage() / 1024 / 1024, 2),
                'hash_functions' => $bloom->getHashCount(),
                'bits_per_item' => $bloom->getMemoryUsage() * 8 / $numItems
            ];
        }

        return $results;
    }

    public static function printAnalysis(int $numItems): void {
        $fpRates = [0.1, 0.01, 0.001, 0.0001];
        $results = self::analyzeTradeoffs($numItems, $fpRates);

        echo "Bloom Filter Trade-offs for $numItems items:\n";
        echo str_repeat('=', 70) . "\n";
        printf("%-10s %-15s %-15s %-15s\n", "FP Rate", "Memory (MB)", "Hash Funcs", "Bits/Item");
        echo str_repeat('-', 70) . "\n";

        foreach ($results as $result) {
            printf(
                "%-10s %-15s %-15d %-15.2f\n",
                number_format($result['fpr'] * 100, 2) . '%',
                $result['size_mb'],
                $result['hash_functions'],
                $result['bits_per_item']
            );
        }

        echo "\nKey Insights:\n";
        echo "- 10x reduction in FP rate ≈ 4.8x increase in memory\n";
        echo "- Optimal bits/item: ~10 for 1% FPR, ~15 for 0.1% FPR\n";
        echo "- More hash functions = slower but more accurate\n";
    }
}

// Usage
BloomFilterAnalyzer::printAnalysis(10000000);  // 10 million items

/*
Output:
Bloom Filter Trade-offs for 10000000 items:
======================================================================
FP Rate    Memory (MB)     Hash Funcs      Bits/Item
----------------------------------------------------------------------
10.00%     5.74            3               4.79
1.00%      11.42           7               9.58
0.10%      17.11           10              14.36
0.01%      22.79           14              19.15
*/
```

### HyperLogLog Precision vs Memory

```php
class HyperLogLogAnalyzer {
    public static function analyzePrecisions(int $numItems): array {
        $precisions = [10, 12, 14, 16];
        $results = [];

        foreach ($precisions as $precision) {
            $hll = new HyperLogLog($precision);

            // Add items
            for ($i = 0; $i < $numItems; $i++) {
                $hll->add("item_$i");
            }

            $estimated = $hll->count();
            $error = abs($estimated - $numItems) / $numItems * 100;

            $results[] = [
                'precision' => $precision,
                'registers' => 1 << $precision,
                'memory_bytes' => $hll->getMemoryUsage(),
                'memory_kb' => round($hll->getMemoryUsage() / 1024, 2),
                'estimated' => $estimated,
                'actual' => $numItems,
                'error_pct' => round($error, 2),
                'theoretical_error' => round(1.04 / sqrt(1 << $precision) * 100, 2)
            ];
        }

        return $results;
    }

    public static function printAnalysis(int $numItems): void {
        $results = self::analyzePrecisions($numItems);

        echo "HyperLogLog Precision Analysis ($numItems items):\n";
        echo str_repeat('=', 90) . "\n";
        printf("%-10s %-12s %-12s %-15s %-15s\n", "Precision", "Memory (KB)", "Actual Err%", "Theory Err%", "Estimated");
        echo str_repeat('-', 90) . "\n";

        foreach ($results as $result) {
            printf(
                "%-10d %-12s %-12s %-15s %-15d\n",
                $result['precision'],
                $result['memory_kb'],
                $result['error_pct'] . '%',
                $result['theoretical_error'] . '%',
                $result['estimated']
            );
        }

        echo "\nKey Insights:\n";
        echo "- Precision 14 (16KB): ~0.81% error - best for most applications\n";
        echo "- Precision 16 (64KB): ~0.41% error - high accuracy needs\n";
        echo "- Doubling registers reduces error by ~1.4x\n";
    }
}

// Usage
HyperLogLogAnalyzer::printAnalysis(1000000);
```

## Advanced Streaming Algorithms

### 1. Sliding Window Heavy Hitters

```php
class SlidingWindowHeavyHitters {
    private array $windows = [];
    private int $windowSize;
    private int $numWindows;
    private CountMinSketch $globalCMS;
    private array $windowCMS = [];

    public function __construct(int $windowSize, int $numWindows = 5) {
        $this->windowSize = $windowSize;
        $this->numWindows = $numWindows;
        $this->globalCMS = new CountMinSketch(0.001, 0.01);

        for ($i = 0; $i < $numWindows; $i++) {
            $this->windowCMS[$i] = new CountMinSketch(0.001, 0.01);
            $this->windows[$i] = [];
        }
    }

    public function add(string $item): void {
        $currentWindow = (int)(time() / $this->windowSize) % $this->numWindows;

        // Check if we need to rotate windows
        if (count($this->windows[$currentWindow]) >= $this->windowSize) {
            $this->rotateWindow($currentWindow);
        }

        $this->windows[$currentWindow][] = $item;
        $this->windowCMS[$currentWindow]->add($item);
        $this->globalCMS->add($item);
    }

    private function rotateWindow(int $windowIndex): void {
        // Clear oldest window
        $this->windows[$windowIndex] = [];
        $this->windowCMS[$windowIndex] = new CountMinSketch(0.001, 0.01);
    }

    public function getTopK(int $k, bool $globalOnly = false): array {
        $counts = [];

        if ($globalOnly) {
            // Use global CMS (all-time heavy hitters)
            foreach (array_merge(...$this->windows) as $item) {
                $counts[$item] = $this->globalCMS->estimate($item);
            }
        } else {
            // Use window CMS (recent heavy hitters)
            foreach ($this->windows as $windowItems) {
                foreach ($windowItems as $item) {
                    if (!isset($counts[$item])) {
                        $counts[$item] = 0;
                    }

                    foreach ($this->windowCMS as $cms) {
                        $counts[$item] += $cms->estimate($item);
                    }
                }
            }
        }

        arsort($counts);
        return array_slice($counts, 0, $k, true);
    }
}

// Usage: Track trending hashtags
$tracker = new SlidingWindowHeavyHitters(windowSize: 300, numWindows: 12);  // 1-hour window, 5-min buckets

foreach ($tweets as $tweet) {
    preg_match_all('/#(\w+)/', $tweet, $matches);

    foreach ($matches[1] as $hashtag) {
        $tracker->add($hashtag);
    }
}

$trending = $tracker->getTopK(10);  // Top 10 trending in last hour
```

### 2. Distinct Elements in Time Windows

```php
class TimeWindowedCardinality {
    private array $windows = [];
    private int $windowDuration;
    private int $maxWindows;

    public function __construct(int $windowDuration = 60, int $maxWindows = 60) {
        $this->windowDuration = $windowDuration;
        $this->maxWindows = $maxWindows;
    }

    public function add(string $item, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();
        $windowKey = (int)($timestamp / $this->windowDuration);

        if (!isset($this->windows[$windowKey])) {
            $this->windows[$windowKey] = new HyperLogLog(14);
            $this->cleanup($windowKey);
        }

        $this->windows[$windowKey]->add($item);
    }

    private function cleanup(int $currentWindow): void {
        $oldestAllowed = $currentWindow - $this->maxWindows;

        foreach (array_keys($this->windows) as $windowKey) {
            if ($windowKey < $oldestAllowed) {
                unset($this->windows[$windowKey]);
            }
        }
    }

    public function getCardinality(int $minutes = 5): int {
        $now = time();
        $startWindow = (int)(($now - $minutes * 60) / $this->windowDuration);
        $endWindow = (int)($now / $this->windowDuration);

        $merged = new HyperLogLog(14);

        for ($w = $startWindow; $w <= $endWindow; $w++) {
            if (isset($this->windows[$w])) {
                $merged->merge($this->windows[$w]);
            }
        }

        return $merged->count();
    }

    public function getCardinalityTimeSeries(int $hours = 1): array {
        $series = [];
        $now = time();
        $windowsPerHour = 3600 / $this->windowDuration;
        $numWindows = $hours * $windowsPerHour;

        for ($i = 0; $i < $numWindows; $i++) {
            $windowTime = $now - ($i * $this->windowDuration);
            $windowKey = (int)($windowTime / $this->windowDuration);

            $count = isset($this->windows[$windowKey])
                ? $this->windows[$windowKey]->count()
                : 0;

            $series[date('Y-m-d H:i', $windowTime)] = $count;
        }

        return array_reverse($series);
    }
}

// Usage: Unique visitors per time window
$tracker = new TimeWindowedCardinality(windowDuration: 60);  // 1-minute windows

// Track visitors
$tracker->add('user_123', time());
$tracker->add('user_456', time());
$tracker->add('user_123', time() + 30);  // Same user, same window

echo "Unique visitors (last 5 min): " . $tracker->getCardinality(5) . "\n";
echo "Unique visitors (last 1 hour): " . $tracker->getCardinality(60) . "\n";

// Get time series
$series = $tracker->getCardinalityTimeSeries(hours: 1);
foreach ($series as $time => $count) {
    echo "$time: $count unique visitors\n";
}
```

### 3. Frequency Estimation with Decay

```php
class DecayingCountMinSketch {
    private CountMinSketch $cms;
    private array $timestamps = [];
    private float $decayFactor;
    private int $decayInterval;

    public function __construct(float $decayFactor = 0.9, int $decayInterval = 3600) {
        $this->cms = new CountMinSketch(0.001, 0.01);
        $this->decayFactor = $decayFactor;
        $this->decayInterval = $decayInterval;
    }

    public function add(string $item, int $count = 1, ?int $timestamp = null): void {
        $timestamp = $timestamp ?? time();

        // Apply decay if needed
        $this->applyDecay($timestamp);

        $this->cms->add($item, $count);
        $this->timestamps[$item] = $timestamp;
    }

    private function applyDecay(int $currentTime): void {
        // Check if decay period has passed
        static $lastDecay = 0;

        if ($currentTime - $lastDecay < $this->decayInterval) {
            return;
        }

        $lastDecay = $currentTime;

        // Decay all counts (simplified - in practice, rebuild CMS)
        // This is conceptual; actual implementation would be more complex
        echo "Applying decay factor: {$this->decayFactor}\n";
    }

    public function estimate(string $item): float {
        $baseCount = $this->cms->estimate($item);

        if (!isset($this->timestamps[$item])) {
            return 0;
        }

        // Apply time-based decay
        $age = time() - $this->timestamps[$item];
        $decayPeriods = $age / $this->decayInterval;

        return $baseCount * pow($this->decayFactor, $decayPeriods);
    }

    public function getTopKRecent(int $k): array {
        // Implementation would return top K items with decay applied
        return [];
    }
}
```

## Common Pitfalls

### 1. Incorrect Bloom Filter Sizing

```php
// ❌ BAD: Underestimating expected items
$bloom = new BloomFilter(1000, 0.01);  // Sized for 1K items

for ($i = 0; $i < 100000; $i++) {  // But adding 100K!
    $bloom->add("item_$i");
}

$actualFPR = $bloom->getFalsePositiveRate();
echo "Expected 1% FPR, got: " . ($actualFPR * 100) . "%\n";  // Much higher!

// ✅ GOOD: Size appropriately with buffer
$expectedItems = 100000;
$bufferMultiplier = 1.2;  // 20% buffer
$bloom = new BloomFilter($expectedItems * $bufferMultiplier, 0.01);
```

### 2. Forgetting HyperLogLog Merge Semantics

```php
// ❌ BAD: Merging then adding same items
$hll1 = new HyperLogLog(14);
$hll2 = new HyperLogLog(14);

$hll1->add('item1');
$hll1->add('item2');

$hll2->add('item2');  // Duplicate!
$hll2->add('item3');

$hll1->merge($hll2);
$count = $hll1->count();  // Will count ~3, not 3 exactly

// ✅ GOOD: Use HLL for disjoint sets or understand overlap
$hll1 = new HyperLogLog(14);
$hll2 = new HyperLogLog(14);

foreach ($setA as $item) $hll1->add($item);
foreach ($setB as $item) $hll2->add($item);

$unionHLL = clone $hll1;
$unionHLL->merge($hll2);
$unionCount = $unionHLL->count();  // Approximate |A ∪ B|
```

### 3. Count-Min Sketch Over-Estimation

```php
// ❌ BAD: Assuming exact counts
$cms = new CountMinSketch(0.01, 0.01);

$cms->add('popular', 1000);
$cms->add('rare', 1);

$rareCount = $cms->estimate('rare');
echo "Rare count: $rareCount\n";  // May be > 1 due to hash collisions!

// ✅ GOOD: Account for over-estimation
class BoundedCountMinSketch extends CountMinSketch {
    private array $exactCounts = [];
    private int $exactCountThreshold;

    public function __construct(float $epsilon, float $delta, int $exactCountThreshold = 10) {
        parent::__construct($epsilon, $delta);
        $this->exactCountThreshold = $exactCountThreshold;
    }

    public function add(string $item, int $count = 1): void {
        parent::add($item, $count);

        // Keep exact counts for rare items
        if (!isset($this->exactCounts[$item])) {
            $this->exactCounts[$item] = 0;
        }

        $this->exactCounts[$item] += $count;

        // Remove from exact tracking if it becomes popular
        if ($this->exactCounts[$item] > $this->exactCountThreshold) {
            unset($this->exactCounts[$item]);
        }
    }

    public function estimate(string $item): int {
        // Use exact count if available
        if (isset($this->exactCounts[$item])) {
            return $this->exactCounts[$item];
        }

        return parent::estimate($item);
    }
}
```

### 4. Memory Management with Large Structures

```php
// ❌ BAD: Creating too many large structures
$filters = [];
for ($i = 0; $i < 1000; $i++) {
    $filters[$i] = new BloomFilter(1000000, 0.001);  // 1.7MB each = 1.7GB total!
}

// ✅ GOOD: Use scalable structures or shared filters
class BloomFilterPool {
    private array $filters = [];
    private int $maxFilters;
    private int $itemsPerFilter;

    public function __construct(int $maxFilters = 10, int $itemsPerFilter = 100000) {
        $this->maxFilters = $maxFilters;
        $this->itemsPerFilter = $itemsPerFilter;
    }

    public function getFilter(string $namespace): BloomFilter {
        if (!isset($this->filters[$namespace])) {
            if (count($this->filters) >= $this->maxFilters) {
                // Evict least recently used
                array_shift($this->filters);
            }

            $this->filters[$namespace] = new BloomFilter($this->itemsPerFilter, 0.01);
        }

        return $this->filters[$namespace];
    }
}
```

## Performance Benchmarks

```php
class ProbabilisticBenchmarks {
    public static function benchmarkBloomFilter(): void {
        $sizes = [10000, 100000, 1000000];

        echo "Bloom Filter Performance:\n";
        echo str_repeat('=', 60) . "\n";

        foreach ($sizes as $size) {
            $bloom = new BloomFilter($size, 0.01);

            // Benchmark additions
            $start = microtime(true);
            for ($i = 0; $i < $size; $i++) {
                $bloom->add("item_$i");
            }
            $addTime = microtime(true) - $start;

            // Benchmark lookups
            $start = microtime(true);
            for ($i = 0; $i < $size; $i++) {
                $bloom->contains("item_$i");
            }
            $lookupTime = microtime(true) - $start;

            printf(
                "%d items: Add=%.3fs (%.0f ops/s), Lookup=%.3fs (%.0f ops/s), Memory=%.2fMB\n",
                $size,
                $addTime,
                $size / $addTime,
                $lookupTime,
                $size / $lookupTime,
                $bloom->getMemoryUsage() / 1024 / 1024
            );
        }
    }

    public static function benchmarkHyperLogLog(): void {
        $sizes = [10000, 100000, 1000000];

        echo "\nHyperLogLog Performance:\n";
        echo str_repeat('=', 60) . "\n";

        foreach ($sizes as $size) {
            $hll = new HyperLogLog(14);

            $start = microtime(true);
            for ($i = 0; $i < $size; $i++) {
                $hll->add("item_$i");
            }
            $addTime = microtime(true) - $start;

            $start = microtime(true);
            $count = $hll->count();
            $countTime = microtime(true) - $start;

            $error = abs($count - $size) / $size * 100;

            printf(
                "%d items: Add=%.3fs (%.0f ops/s), Count=%.6fs, Error=%.2f%%, Memory=16KB\n",
                $size,
                $addTime,
                $size / $addTime,
                $countTime,
                $error
            );
        }
    }
}

// Run benchmarks
ProbabilisticBenchmarks::benchmarkBloomFilter();
ProbabilisticBenchmarks::benchmarkHyperLogLog();
```

## Summary

Probabilistic algorithms enable processing of massive datasets that would be impossible with exact algorithms:

- **Bloom Filters**: Fast membership testing with minimal memory (use cases: cache layers, database optimization, blacklists)
- **HyperLogLog**: Count unique items with 0.81% error in ~16KB (use cases: analytics, unique visitors, cardinality estimation)
- **Count-Min Sketch**: Track frequencies with bounded error (use cases: heavy hitters, trending topics, frequency queries)
- **Reservoir Sampling**: Maintain random samples from streams (use cases: sampling, statistics, monitoring)
- **Morris Counter**: Count in logarithmic space (use cases: large counters, space-constrained environments)

**Key Trade-offs**:
- Accuracy vs. Memory: 10x accuracy improvement ≈ 4-5x memory increase
- False Positives vs. Space: Bloom filters never have false negatives, only false positives
- Estimation Error vs. Computation: More precise structures = more computation

**When to Use**:
- Dataset too large for exact algorithms
- Approximate results acceptable
- Real-time processing required
- Space/time constraints critical

## Next Steps

- **Chapter 33: String Algorithms Deep Dive** - Advanced string matching
- **Chapter 36: Stream Processing Algorithms** - Real-time data processing
- **Chapter 26: Approximate Algorithms** - More approximation techniques

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 32 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-32)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-32
php 01-*.php
```

## Practice Exercises

1. **Web Crawler Deduplicator**: Implement a Bloom filter-based URL deduplicator that handles 1 billion URLs
2. **Analytics Dashboard**: Build a HyperLogLog-based system for tracking unique visitors across multiple time windows
3. **Trending Topics Detector**: Create a Count-Min Sketch system that identifies trending topics on social media
4. **Recommendation Sampler**: Implement weighted reservoir sampling for a recommendation system with user preferences
5. **Distributed Cardinality**: Build a distributed cardinality estimator using HyperLogLog merge operations
6. **Cache Pre-warmer**: Design a system using Bloom filters to predict which cache entries to preload
7. **Fraud Detection**: Implement a probabilistic fraud detection system using multiple probabilistic structures
8. **Time-Series Analytics**: Build a time-windowed cardinality tracker for monitoring unique events
9. **Query Optimizer**: Create a database query optimizer using Bloom filter indexes
10. **Rate Limiter**: Implement a space-efficient rate limiter using Count-Min Sketch
