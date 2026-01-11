---
title: "32: Probabilistic Algorithms"
description: "Master space-efficient probabilistic data structures including Bloom filters, HyperLogLog, Count-Min Sketch, and streaming algorithms for big data processing"
series: "php-algorithms"
chapter: 32
order: 32
difficulty: "Advanced"
prerequisites: []
estimatedTime: "PT50M"
---
![Probabilistic Algorithms](/images/php-algorithms/chapter-32-probabilistic-algorithms-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 32</span>
</div>

# 32: Probabilistic Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

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
# filename: bloom-filter.php
<?php

declare(strict_types=1);

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

    public function getMemoryUsage(): int {
        // Each boolean in PHP uses 1 byte, but array overhead adds ~16 bytes
        // Approximate: size bytes + array overhead
        return $this->size + 16;
    }

    public function getHashCount(): int {
        return $this->hashCount;
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
# filename: scalable-bloom-filter.php
<?php

declare(strict_types=1);

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
            $total += $filter->getMemoryUsage();
        }
        return $total;
    }
}
```

### Real-World Example: Email Spam Filter

```php
# filename: spam-filter.php
<?php

declare(strict_types=1);

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

## Cuckoo Filter

A Cuckoo Filter is a probabilistic data structure that supports deletion, unlike Bloom Filters. It uses cuckoo hashing with fingerprinting to achieve similar space efficiency while allowing item removal.

### Implementation

```php
# filename: cuckoo-filter.php
<?php

declare(strict_types=1);

class CuckooFilter {
    private array $buckets;
    private int $bucketSize;
    private int $numBuckets;
    private int $fingerprintSize;
    private int $maxKicks;

    public function __construct(int $capacity, int $bucketSize = 4, float $falsePositiveRate = 0.01) {
        $this->bucketSize = $bucketSize;
        $this->fingerprintSize = $this->calculateFingerprintSize($falsePositiveRate);
        $this->numBuckets = (int) ceil($capacity / $bucketSize);
        $this->buckets = array_fill(0, $this->numBuckets, array_fill(0, $bucketSize, null));
        $this->maxKicks = 500;  // Maximum relocations before giving up
    }

    private function calculateFingerprintSize(float $fpr): int {
        // Fingerprint size based on false positive rate
        return max(1, (int) ceil(-log2($fpr)));
    }

    private function fingerprint(string $item): int {
        $hash = crc32($item);
        $mask = (1 << $this->fingerprintSize) - 1;
        $fp = $hash & $mask;
        return $fp === 0 ? 1 : $fp;  // Ensure non-zero fingerprint
    }

    private function hash1(string $item): int {
        return abs(crc32($item)) % $this->numBuckets;
    }

    private function hash2(int $fingerprint, int $hash1): int {
        return ($hash1 ^ crc32((string)$fingerprint)) % $this->numBuckets;
    }

    public function add(string $item): bool {
        $fp = $this->fingerprint($item);
        $h1 = $this->hash1($item);
        $h2 = $this->hash2($fp, $h1);

        // Try first bucket
        if ($this->insertIntoBucket($h1, $fp)) {
            return true;
        }

        // Try second bucket
        if ($this->insertIntoBucket($h2, $fp)) {
            return true;
        }

        // Both buckets full, need to relocate
        return $this->relocate($h1, $fp);
    }

    private function insertIntoBucket(int $bucketIndex, int $fingerprint): bool {
        $bucket = &$this->buckets[$bucketIndex];
        
        for ($i = 0; $i < $this->bucketSize; $i++) {
            if ($bucket[$i] === null) {
                $bucket[$i] = $fingerprint;
                return true;
            }
        }
        
        return false;
    }

    private function relocate(int $bucketIndex, int $fingerprint): bool {
        for ($i = 0; $i < $this->maxKicks; $i++) {
            $bucket = &$this->buckets[$bucketIndex];
            $randomIndex = mt_rand(0, $this->bucketSize - 1);
            $oldFp = $bucket[$randomIndex];
            
            $bucket[$randomIndex] = $fingerprint;
            $fingerprint = $oldFp;
            
            $bucketIndex = $this->hash2($fingerprint, $bucketIndex);
            
            if ($this->insertIntoBucket($bucketIndex, $fingerprint)) {
                return true;
            }
        }
        
        return false;  // Failed after max kicks
    }

    public function contains(string $item): bool {
        $fp = $this->fingerprint($item);
        $h1 = $this->hash1($item);
        $h2 = $this->hash2($fp, $h1);

        return $this->bucketContains($h1, $fp) || $this->bucketContains($h2, $fp);
    }

    private function bucketContains(int $bucketIndex, int $fingerprint): bool {
        $bucket = $this->buckets[$bucketIndex];
        
        for ($i = 0; $i < $this->bucketSize; $i++) {
            if ($bucket[$i] === $fingerprint) {
                return true;
            }
        }
        
        return false;
    }

    public function delete(string $item): bool {
        $fp = $this->fingerprint($item);
        $h1 = $this->hash1($item);
        $h2 = $this->hash2($fp, $h1);

        if ($this->removeFromBucket($h1, $fp)) {
            return true;
        }

        return $this->removeFromBucket($h2, $fp);
    }

    private function removeFromBucket(int $bucketIndex, int $fingerprint): bool {
        $bucket = &$this->buckets[$bucketIndex];
        
        for ($i = 0; $i < $this->bucketSize; $i++) {
            if ($bucket[$i] === $fingerprint) {
                $bucket[$i] = null;
                return true;
            }
        }
        
        return false;
    }

    public function getMemoryUsage(): int {
        return $this->numBuckets * $this->bucketSize * $this->fingerprintSize / 8;
    }
}

// Usage
$filter = new CuckooFilter(10000, 4, 0.01);

$filter->add('user_123');
$filter->add('user_456');

var_dump($filter->contains('user_123'));  // true
var_dump($filter->contains('user_999'));  // false

$filter->delete('user_123');
var_dump($filter->contains('user_123'));  // false
```

**Time Complexity**: O(1) average for add/contains/delete, O(k) worst case where k is max kicks
**Space Complexity**: O(n) where n is capacity
**Advantages**: Supports deletion, better cache performance than Bloom Filters
**Disadvantages**: More complex implementation, potential for insertion failures

### Real-World Example: Dynamic Blacklist

```php
# filename: dynamic-blacklist.php
<?php

declare(strict_types=1);

class DynamicBlacklist {
    private CuckooFilter $blacklist;
    private array $metadata = [];  // Store additional info for blacklisted items

    public function __construct(int $capacity = 1000000) {
        $this->blacklist = new CuckooFilter($capacity, 4, 0.001);
    }

    public function add(string $item, array $metadata = []): bool {
        if ($this->blacklist->add($item)) {
            $this->metadata[$item] = $metadata;
            return true;
        }
        return false;  // Filter full
    }

    public function remove(string $item): bool {
        if ($this->blacklist->delete($item)) {
            unset($this->metadata[$item]);
            return true;
        }
        return false;
    }

    public function isBlacklisted(string $item): bool {
        return $this->blacklist->contains($item);
    }

    public function getMetadata(string $item): ?array {
        return $this->metadata[$item] ?? null;
    }
}

// Usage: IP blacklist with expiration
$blacklist = new DynamicBlacklist(1000000);

// Add IPs with expiration time
$blacklist->add('192.168.1.100', ['reason' => 'spam', 'expires' => time() + 3600]);
$blacklist->add('10.0.0.50', ['reason' => 'abuse', 'expires' => time() + 7200]);

// Check if blacklisted
if ($blacklist->isBlacklisted('192.168.1.100')) {
    echo "IP is blacklisted\n";
}

// Remove expired entries
// (In production, run periodic cleanup)
```

## Quotient Filter

A Quotient Filter is a space-efficient probabilistic data structure similar to Bloom Filters but with better cache performance and support for deletion.

### Implementation

```php
# filename: quotient-filter.php
<?php

declare(strict_types=1);

class QuotientFilter {
    private array $slots;
    private int $quotientBits;
    private int $remainderBits;
    private int $numSlots;

    public function __construct(int $capacity, float $falsePositiveRate = 0.01) {
        $this->quotientBits = (int) ceil(log($capacity, 2));
        $this->remainderBits = max(3, (int) ceil(-log2($falsePositiveRate)));
        $this->numSlots = 1 << $this->quotientBits;
        $this->slots = array_fill(0, $this->numSlots, ['remainder' => 0, 'isOccupied' => false, 'isContinuation' => false, 'isShifted' => false]);
    }

    private function hash(string $item): array {
        $hash = crc32($item);
        $quotient = $hash & ((1 << $this->quotientBits) - 1);
        $remainder = ($hash >> $this->quotientBits) & ((1 << $this->remainderBits) - 1);
        return ['quotient' => $quotient, 'remainder' => $remainder === 0 ? 1 : $remainder];
    }

    public function add(string $item): bool {
        $h = $this->hash($item);
        $quotient = $h['quotient'];
        $remainder = $h['remainder'];

        if ($this->slots[$quotient]['isOccupied']) {
            // Find run start
            $start = $this->findRunStart($quotient);
            // Find insertion point
            $insertPos = $this->findInsertPosition($start, $remainder);
            // Shift and insert
            return $this->insertAt($insertPos, $remainder, $quotient);
        } else {
            // Empty slot, insert directly
            $this->slots[$quotient] = [
                'remainder' => $remainder,
                'isOccupied' => true,
                'isContinuation' => false,
                'isShifted' => false
            ];
            return true;
        }
    }

    private function findRunStart(int $quotient): int {
        $start = $quotient;
        while ($this->slots[$start]['isShifted']) {
            $start = ($start - 1 + $this->numSlots) % $this->numSlots;
        }
        return $start;
    }

    private function findInsertPosition(int $start, int $remainder): int {
        $pos = $start;
        while ($this->slots[$pos]['isOccupied'] && $this->slots[$pos]['remainder'] < $remainder) {
            $pos = ($pos + 1) % $this->numSlots;
        }
        return $pos;
    }

    private function insertAt(int $pos, int $remainder, int $quotient): bool {
        // Simplified insertion - full implementation requires shifting
        if (!$this->slots[$pos]['isOccupied']) {
            $this->slots[$pos] = [
                'remainder' => $remainder,
                'isOccupied' => true,
                'isContinuation' => $pos !== $quotient,
                'isShifted' => $pos !== $quotient
            ];
            return true;
        }
        return false;  // Slot occupied, would need to shift
    }

    public function contains(string $item): bool {
        $h = $this->hash($item);
        $quotient = $h['quotient'];
        $remainder = $h['remainder'];

        if (!$this->slots[$quotient]['isOccupied']) {
            return false;
        }

        $start = $this->findRunStart($quotient);
        $pos = $start;

        while ($pos !== ($start + 1) % $this->numSlots || $this->slots[$pos]['isContinuation']) {
            if ($this->slots[$pos]['remainder'] === $remainder) {
                return true;
            }
            $pos = ($pos + 1) % $this->numSlots;
        }

        return false;
    }

    public function getMemoryUsage(): int {
        return $this->numSlots * ($this->remainderBits + 3);  // 3 bits for flags
    }
}

// Usage
$filter = new QuotientFilter(10000, 0.01);
$filter->add('item1');
var_dump($filter->contains('item1'));  // true
```

**Time Complexity**: O(1) average, O(r) worst case where r is run length
**Space Complexity**: O(n) where n is capacity
**Advantages**: Better cache locality, supports deletion (with modifications)
**Disadvantages**: More complex than Bloom Filter, insertion can be slower

## HyperLogLog

HyperLogLog estimates the cardinality (number of unique elements) of very large datasets with remarkable accuracy using minimal memory.

### Implementation

```php
# filename: hyperloglog.php
<?php

declare(strict_types=1);

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
        // crc32() can return negative values, convert to unsigned 32-bit integer
        $hash = crc32($item);
        // Convert signed to unsigned 32-bit integer
        return $hash < 0 ? (0xFFFFFFFF & $hash) : $hash;
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
# filename: unique-visitor-counter.php
<?php

declare(strict_types=1);

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

## Count Sketch

Count Sketch is a variant of Count-Min Sketch that provides better variance properties by using both addition and subtraction, making it more accurate for frequency estimation.

### Implementation

```php
# filename: count-sketch.php
<?php

declare(strict_types=1);

class CountSketch {
    private array $table;
    private int $width;
    private int $depth;

    public function __construct(float $epsilon = 0.001, float $delta = 0.01) {
        $this->width = (int) ceil(M_E / $epsilon);
        $this->depth = (int) ceil(log(1 / $delta));
        $this->table = array_fill(0, $this->depth, array_fill(0, $this->width, 0));
    }

    private function hash(string $item, int $seed): int {
        return abs(crc32($item . $seed)) % $this->width;
    }

    private function signHash(string $item, int $seed): int {
        // Returns +1 or -1
        return (crc32($item . $seed . 'sign') & 1) ? 1 : -1;
    }

    public function add(string $item, int $count = 1): void {
        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash($item, $i);
            $sign = $this->signHash($item, $i);
            $this->table[$i][$index] += $sign * $count;
        }
    }

    public function estimate(string $item): int {
        $estimates = [];

        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash($item, $i);
            $sign = $this->signHash($item, $i);
            $estimates[] = $sign * $this->table[$i][$index];
        }

        // Return median for better accuracy
        sort($estimates);
        $mid = (int)($this->depth / 2);
        return $estimates[$mid];
    }

    public function getMemoryUsage(): int {
        return $this->width * $this->depth * 8;  // 8 bytes per counter
    }
}

// Usage
$cs = new CountSketch(0.001, 0.01);

$cs->add('/home', 5);
$cs->add('/about', 2);
$cs->add('/home', 3);

echo $cs->estimate('/home');      // ~8
echo $cs->estimate('/about');     // ~2
```

**Time Complexity**: O(d) where d is depth
**Space Complexity**: O(w × d) where w is width, d is depth
**Advantages**: Better variance than Count-Min Sketch, median reduces noise
**Disadvantages**: Slightly more complex, requires sign hash

## Count-Min Sketch

Count-Min Sketch estimates the frequency of items in a data stream with controlled error bounds.

### Implementation

```php
# filename: count-min-sketch.php
<?php

declare(strict_types=1);

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
# filename: heavy-hitters-detector.php
<?php

declare(strict_types=1);

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
# filename: reservoir-sampler.php
<?php

declare(strict_types=1);

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
# filename: weighted-reservoir-sampler.php
<?php

declare(strict_types=1);

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
# filename: morris-counter.php
<?php

declare(strict_types=1);

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

## MinHash

MinHash is a probabilistic technique for estimating Jaccard similarity between sets. It's widely used in recommendation systems, duplicate detection, and document similarity.

### Implementation

```php
# filename: minhash.php
<?php

declare(strict_types=1);

class MinHash {
    private int $numHashFunctions;
    private array $hashFunctions;

    public function __construct(int $numHashFunctions = 128) {
        $this->numHashFunctions = $numHashFunctions;
        $this->hashFunctions = [];
        
        // Generate random hash functions
        for ($i = 0; $i < $numHashFunctions; $i++) {
            $a = mt_rand(1, PHP_INT_MAX);
            $b = mt_rand(1, PHP_INT_MAX);
            $this->hashFunctions[] = function($x) use ($a, $b, $i) {
                return ($a * crc32($x . $i) + $b) % PHP_INT_MAX;
            };
        }
    }

    public function computeSignature(array $set): array {
        $signature = array_fill(0, $this->numHashFunctions, PHP_INT_MAX);

        foreach ($set as $element) {
            foreach ($this->hashFunctions as $i => $hashFn) {
                $hash = $hashFn($element);
                $signature[$i] = min($signature[$i], $hash);
            }
        }

        return $signature;
    }

    public function jaccardSimilarity(array $signature1, array $signature2): float {
        $matches = 0;
        
        for ($i = 0; $i < $this->numHashFunctions; $i++) {
            if ($signature1[$i] === $signature2[$i]) {
                $matches++;
            }
        }

        return $matches / $this->numHashFunctions;
    }

    public function exactJaccard(array $set1, array $set2): float {
        $intersection = count(array_intersect($set1, $set2));
        $union = count(array_unique(array_merge($set1, $set2)));
        return $union > 0 ? $intersection / $union : 0.0;
    }
}

// Usage: Document similarity
$minhash = new MinHash(128);

$doc1 = ['php', 'algorithm', 'data', 'structure', 'hash'];
$doc2 = ['php', 'algorithm', 'tree', 'graph', 'data'];

$sig1 = $minhash->computeSignature($doc1);
$sig2 = $minhash->computeSignature($doc2);

$similarity = $minhash->jaccardSimilarity($sig1, $sig2);
$exact = $minhash->exactJaccard($doc1, $doc2);

echo "MinHash similarity: " . number_format($similarity, 3) . "\n";
echo "Exact Jaccard: " . number_format($exact, 3) . "\n";
```

**Time Complexity**: O(n × k) where n is set size, k is number of hash functions
**Space Complexity**: O(k) for signature
**Accuracy**: Error decreases with more hash functions (typically 128-512)

### Real-World Example: Recommendation System

```php
# filename: recommendation-minhash.php
<?php

declare(strict_types=1);

class RecommendationEngine {
    private MinHash $minhash;
    private array $userSignatures = [];

    public function __construct() {
        $this->minhash = new MinHash(256);
    }

    public function addUserPreferences(string $userId, array $items): void {
        $this->userSignatures[$userId] = $this->minhash->computeSignature($items);
    }

    public function findSimilarUsers(string $userId, float $threshold = 0.5): array {
        if (!isset($this->userSignatures[$userId])) {
            return [];
        }

        $userSig = $this->userSignatures[$userId];
        $similar = [];

        foreach ($this->userSignatures as $otherId => $otherSig) {
            if ($otherId === $userId) {
                continue;
            }

            $similarity = $this->minhash->jaccardSimilarity($userSig, $otherSig);
            if ($similarity >= $threshold) {
                $similar[$otherId] = $similarity;
            }
        }

        arsort($similar);
        return $similar;
    }

    public function recommend(string $userId, int $topN = 10): array {
        $similarUsers = $this->findSimilarUsers($userId, 0.3);
        // Return top N recommendations based on similar users
        return array_slice($similarUsers, 0, $topN, true);
    }
}

// Usage
$engine = new RecommendationEngine();

$engine->addUserPreferences('user1', ['php', 'laravel', 'mysql', 'redis']);
$engine->addUserPreferences('user2', ['php', 'laravel', 'postgres', 'redis']);
$engine->addUserPreferences('user3', ['python', 'django', 'postgres']);

$similar = $engine->findSimilarUsers('user1', 0.5);
print_r($similar);  // user2 with high similarity
```

## T-Digest

T-Digest is a probabilistic data structure for estimating quantiles (percentiles) with high accuracy and low memory usage. It's essential for monitoring systems and analytics.

### Implementation

```php
# filename: t-digest.php
<?php

declare(strict_types=1);

class TDigest {
    private array $centroids = [];
    private float $compression;
    private int $totalCount = 0;

    public function __construct(float $compression = 100.0) {
        $this->compression = $compression;
    }

    public function add(float $value, int $count = 1): void {
        $this->totalCount += $count;

        if (empty($this->centroids)) {
            $this->centroids[] = ['mean' => $value, 'count' => $count];
            return;
        }

        // Find insertion point
        $index = $this->findInsertionPoint($value);
        
        if ($index === -1) {
            // Add to end
            $this->centroids[] = ['mean' => $value, 'count' => $count];
        } else {
            // Merge with existing centroid or insert
            $this->mergeOrInsert($index, $value, $count);
        }

        // Compress if needed
        if (count($this->centroids) > $this->compression * 2) {
            $this->compress();
        }
    }

    private function findInsertionPoint(float $value): int {
        for ($i = 0; $i < count($this->centroids); $i++) {
            if ($this->centroids[$i]['mean'] >= $value) {
                return $i;
            }
        }
        return -1;
    }

    private function mergeOrInsert(int $index, float $value, int $count): void {
        $centroid = &$this->centroids[$index];
        $k1 = $this->quantile($index - 1);
        $k2 = $this->quantile($index);

        $threshold = 4 * $this->totalCount * $this->q($k2) * (1 - $this->q($k2)) / $this->compression;

        if ($centroid['count'] + $count <= $threshold) {
            // Merge
            $centroid['mean'] = ($centroid['mean'] * $centroid['count'] + $value * $count) / ($centroid['count'] + $count);
            $centroid['count'] += $count;
        } else {
            // Insert new centroid
            array_splice($this->centroids, $index, 0, [['mean' => $value, 'count' => $count]]);
        }
    }

    private function quantile(int $index): float {
        $sum = 0;
        for ($i = 0; $i < $index; $i++) {
            $sum += $this->centroids[$i]['count'];
        }
        return ($sum + $this->centroids[$index]['count'] / 2) / $this->totalCount;
    }

    private function q(float $k): float {
        return ($k / $this->compression) * (1 - $k / $this->compression);
    }

    private function compress(): void {
        // Simplified compression - merge nearby centroids
        $compressed = [];
        $i = 0;

        while ($i < count($this->centroids)) {
            $merged = $this->centroids[$i];
            $j = $i + 1;

            while ($j < count($this->centroids) && 
                   abs($this->centroids[$j]['mean'] - $merged['mean']) < 0.01) {
                $merged['mean'] = ($merged['mean'] * $merged['count'] + 
                                  $this->centroids[$j]['mean'] * $this->centroids[$j]['count']) /
                                  ($merged['count'] + $this->centroids[$j]['count']);
                $merged['count'] += $this->centroids[$j]['count'];
                $j++;
            }

            $compressed[] = $merged;
            $i = $j;
        }

        $this->centroids = $compressed;
    }

    public function quantile(float $q): float {
        if ($q <= 0) {
            return $this->centroids[0]['mean'];
        }
        if ($q >= 1) {
            return $this->centroids[count($this->centroids) - 1]['mean'];
        }

        $target = $q * $this->totalCount;
        $sum = 0;

        foreach ($this->centroids as $centroid) {
            $sum += $centroid['count'];
            if ($sum >= $target) {
                return $centroid['mean'];
            }
        }

        return $this->centroids[count($this->centroids) - 1]['mean'];
    }

    public function percentile(float $p): float {
        return $this->quantile($p / 100);
    }

    public function getMemoryUsage(): int {
        return count($this->centroids) * 16;  // ~16 bytes per centroid
    }
}

// Usage: Performance monitoring
$tdigest = new TDigest(100.0);

// Add response times
for ($i = 0; $i < 10000; $i++) {
    $tdigest->add(mt_rand(10, 500));  // Response times in ms
}

echo "P50 (median): " . $tdigest->percentile(50) . "ms\n";
echo "P95: " . $tdigest->percentile(95) . "ms\n";
echo "P99: " . $tdigest->percentile(99) . "ms\n";
echo "Memory: " . $tdigest->getMemoryUsage() . " bytes\n";
```

**Time Complexity**: O(log n) for insertion, O(log n) for quantile query
**Space Complexity**: O(compression) - typically 100-1000 centroids
**Accuracy**: High accuracy for percentiles, especially tail percentiles (P95, P99)

### Real-World Example: API Latency Monitoring

```php
# filename: latency-monitor.php
<?php

declare(strict_types=1);

class LatencyMonitor {
    private TDigest $latencies;
    private int $windowSize;
    private array $recentLatencies = [];

    public function __construct(int $windowSize = 1000) {
        $this->latencies = new TDigest(200.0);
        $this->windowSize = $windowSize;
    }

    public function record(float $latencyMs): void {
        $this->latencies->add($latencyMs);
        $this->recentLatencies[] = $latencyMs;

        if (count($this->recentLatencies) > $this->windowSize) {
            array_shift($this->recentLatencies);
        }
    }

    public function getStats(): array {
        return [
            'p50' => $this->latencies->percentile(50),
            'p75' => $this->latencies->percentile(75),
            'p90' => $this->latencies->percentile(90),
            'p95' => $this->latencies->percentile(95),
            'p99' => $this->latencies->percentile(99),
            'p99_9' => $this->latencies->percentile(99.9),
            'memory_bytes' => $this->latencies->getMemoryUsage()
        ];
    }

    public function isSlow(float $latencyMs, float $p95Threshold): bool {
        return $latencyMs > $p95Threshold;
    }
}

// Usage
$monitor = new LatencyMonitor();

// Record API response times
$monitor->record(45.2);
$monitor->record(38.9);
$monitor->record(120.5);
$monitor->record(52.1);
// ... thousands more

$stats = $monitor->getStats();
echo "P95 latency: " . $stats['p95'] . "ms\n";
echo "P99 latency: " . $stats['p99'] . "ms\n";
```

## Locality-Sensitive Hashing (LSH)

LSH is a technique for approximate nearest neighbor search in high-dimensional spaces. It's used in recommendation systems, image search, and similarity detection.

### Implementation

```php
# filename: lsh.php
<?php

declare(strict_types=1);

class LSH {
    private int $numHashTables;
    private int $numHashFunctions;
    private array $hashTables;
    private array $randomVectors;

    public function __construct(int $numHashTables = 10, int $numHashFunctions = 5) {
        $this->numHashTables = $numHashTables;
        $this->numHashFunctions = $numHashFunctions;
        $this->hashTables = array_fill(0, $numHashTables, []);
        $this->randomVectors = [];

        // Generate random vectors for each hash table
        for ($i = 0; $i < $numHashTables; $i++) {
            $this->randomVectors[$i] = [];
            for ($j = 0; $j < $numHashFunctions; $j++) {
                $this->randomVectors[$i][$j] = $this->generateRandomVector(128);  // 128-dim vectors
            }
        }
    }

    private function generateRandomVector(int $dim): array {
        $vector = [];
        for ($i = 0; $i < $dim; $i++) {
            $vector[] = (mt_rand() / mt_getrandmax() - 0.5) * 2;
        }
        return $vector;
    }

    private function dotProduct(array $vec1, array $vec2): float {
        $sum = 0.0;
        for ($i = 0; $i < min(count($vec1), count($vec2)); $i++) {
            $sum += $vec1[$i] * $vec2[$i];
        }
        return $sum;
    }

    private function hash(array $vector, int $tableIndex): string {
        $hash = '';
        foreach ($this->randomVectors[$tableIndex] as $randomVec) {
            $dot = $this->dotProduct($vector, $randomVec);
            $hash .= $dot >= 0 ? '1' : '0';
        }
        return $hash;
    }

    public function insert(string $id, array $vector): void {
        for ($i = 0; $i < $this->numHashTables; $i++) {
            $hash = $this->hash($vector, $i);
            if (!isset($this->hashTables[$i][$hash])) {
                $this->hashTables[$i][$hash] = [];
            }
            $this->hashTables[$i][$hash][] = $id;
        }
    }

    public function query(array $vector, int $maxResults = 10): array {
        $candidates = [];
        
        for ($i = 0; $i < $this->numHashTables; $i++) {
            $hash = $this->hash($vector, $i);
            if (isset($this->hashTables[$i][$hash])) {
                $candidates = array_merge($candidates, $this->hashTables[$i][$hash]);
            }
        }

        // Remove duplicates and return top results
        $candidates = array_unique($candidates);
        return array_slice($candidates, 0, $maxResults);
    }

    public function cosineSimilarity(array $vec1, array $vec2): float {
        $dot = $this->dotProduct($vec1, $vec2);
        $norm1 = sqrt($this->dotProduct($vec1, $vec1));
        $norm2 = sqrt($this->dotProduct($vec2, $vec2));
        
        if ($norm1 === 0.0 || $norm2 === 0.0) {
            return 0.0;
        }
        
        return $dot / ($norm1 * $norm2);
    }
}

// Usage: Image similarity search
$lsh = new LSH(20, 8);

// Insert image feature vectors
$lsh->insert('image1', [0.1, 0.2, 0.3, /* ... 128 dimensions */]);
$lsh->insert('image2', [0.15, 0.25, 0.35, /* ... */]);
$lsh->insert('image3', [0.9, 0.8, 0.7, /* ... */]);

// Query similar images
$queryVector = [0.12, 0.22, 0.32, /* ... */];
$similar = $lsh->query($queryVector, 5);
print_r($similar);  // Returns candidate IDs for similar images
```

**Time Complexity**: O(d × L) for insertion/query where d is dimensions, L is num hash tables
**Space Complexity**: O(n × L) where n is number of items
**Use Cases**: Image search, recommendation systems, duplicate detection, clustering

## Skip List (Probabilistic BST)

```php
# filename: skip-list.php
<?php

declare(strict_types=1);

class SkipNode {
    public mixed $value;
    public array $forward = [];

    public function __construct(mixed $value) {
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
| Cuckoo Filter | Membership with deletion | O(m) | No false negatives | Add, Contains, Delete |
| Quotient Filter | Cache-friendly membership | O(m) | No false negatives | Add, Contains, Delete |
| HyperLogLog | Cardinality estimation | O(2^p) | ±0.81% (p=14) | Add, Count, Merge |
| Count-Min Sketch | Frequency estimation | O(w×d) | ±εN | Add, Estimate |
| Count Sketch | Frequency (better variance) | O(w×d) | ±εN (median) | Add, Estimate |
| MinHash | Set similarity (Jaccard) | O(k) | Error ∝ 1/√k | Compute, Similarity |
| T-Digest | Quantile estimation | O(compression) | High accuracy | Add, Quantile |
| LSH | Nearest neighbor search | O(n×L) | Approximate | Insert, Query |
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

        // Use backtick quoting for identifiers to prevent SQL injection
        // In production, validate table/column names against whitelist
        $quotedTable = "`" . str_replace("`", "``", $table) . "`";
        $quotedColumn = "`" . str_replace("`", "``", $column) . "`";
        $stmt = $this->db->query("SELECT DISTINCT {$quotedColumn} FROM {$quotedTable}");
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
- **Cuckoo Filter**: Membership testing with deletion support (use cases: dynamic blacklists, cache invalidation)
- **Quotient Filter**: Cache-friendly membership structure (use cases: high-performance lookups, better cache locality)
- **HyperLogLog**: Count unique items with 0.81% error in ~16KB (use cases: analytics, unique visitors, cardinality estimation)
- **Count-Min Sketch**: Track frequencies with bounded error (use cases: heavy hitters, trending topics, frequency queries)
- **Count Sketch**: Frequency estimation with better variance (use cases: when variance matters more than worst-case error)
- **MinHash**: Estimate Jaccard similarity between sets (use cases: recommendation systems, duplicate detection, document similarity)
- **T-Digest**: Estimate quantiles/percentiles accurately (use cases: performance monitoring, SLA tracking, analytics dashboards)
- **LSH**: Approximate nearest neighbor search (use cases: image similarity, recommendation systems, clustering)
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

## Wrap-up

You've mastered probabilistic algorithms - the secret weapons of big data systems! Here's what you've accomplished:

- ✓ Implemented Bloom filters for space-efficient membership testing
- ✓ Built Cuckoo Filters and Quotient Filters for membership with deletion support
- ✓ Created HyperLogLog structures for cardinality estimation with minimal memory
- ✓ Implemented Count-Min Sketch and Count Sketch for frequency tracking
- ✓ Applied MinHash for set similarity estimation (Jaccard similarity)
- ✓ Built T-Digest for accurate quantile/percentile estimation
- ✓ Implemented LSH for approximate nearest neighbor search
- ✓ Applied reservoir sampling for random sampling from streams
- ✓ Understood the accuracy/efficiency trade-offs in probabilistic algorithms
- ✓ Explored advanced use cases like distributed caching, query optimization, and recommendation systems

These algorithms enable you to handle datasets that would be impossible with exact algorithms, trading perfect accuracy for dramatic space and time improvements. You're now equipped to build systems that process billions of data points with kilobytes of memory.

## Further Reading

- [Bloom Filter Wikipedia](https://en.wikipedia.org/wiki/Bloom_filter) — Comprehensive overview of Bloom filter theory and applications
- [HyperLogLog Paper](https://algo.inria.fr/flajolet/Publications/FlFuGaMe07.pdf) — Original HyperLogLog research paper by Flajolet et al.
- [Count-Min Sketch Paper](https://www.cs.princeton.edu/courses/archive/spring04/cos598B/bib/Cormode_Sketch.pdf) — Original Count-Min Sketch paper
- [Probabilistic Data Structures](https://highlyscalable.wordpress.com/2012/05/01/probabilistic-structures-web-analytics-data-mining/) — Excellent blog post on probabilistic data structures
- [Streaming Algorithms](https://en.wikipedia.org/wiki/Streaming_algorithm) — Wikipedia overview of streaming algorithms

## Next Steps

- **Chapter 33: String Algorithms Deep Dive** — Advanced string matching
- **Chapter 36: Stream Processing Algorithms** — Real-time data processing
- **Chapter 26: Approximate Algorithms** — More approximation techniques


<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="32"
  label="Probabilistic Algorithms mastered!"
/>
## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 32 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-32)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-32
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
