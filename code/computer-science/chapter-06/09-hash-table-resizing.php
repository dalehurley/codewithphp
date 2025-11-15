<?php

declare(strict_types=1);

/**
 * Hash Table Dynamic Resizing
 *
 * Demonstrates:
 * - Load factor monitoring
 * - Dynamic resizing (rehashing)
 * - Performance impact of resizing
 * - Amortized O(1) operations
 */

class HashNode
{
    public function __construct(
        public string $key,
        public mixed $value,
        public ?HashNode $next = null
    ) {}
}

class DynamicHashTable
{
    private array $buckets = [];
    private int $size;
    private int $count = 0;
    private float $loadFactorThreshold = 0.75;
    private int $resizeCount = 0;

    public function __construct(int $initialSize = 8)
    {
        $this->size = $initialSize;
        $this->buckets = array_fill(0, $this->size, null);
    }

    /**
     * Put key-value pair with automatic resizing
     */
    public function put(string $key, mixed $value): void
    {
        $index = $this->hash($key);
        $node = $this->buckets[$index];

        // Update existing key
        while ($node !== null) {
            if ($node->key === $key) {
                $node->value = $value;
                return;
            }
            $node = $node->next;
        }

        // Insert new key
        $newNode = new HashNode($key, $value, $this->buckets[$index]);
        $this->buckets[$index] = $newNode;
        $this->count++;

        // Check if resize needed
        if ($this->getLoadFactor() > $this->loadFactorThreshold) {
            $this->resize();
        }
    }

    /**
     * Get value by key
     */
    public function get(string $key): mixed
    {
        $index = $this->hash($key);
        $node = $this->buckets[$index];

        while ($node !== null) {
            if ($node->key === $key) {
                return $node->value;
            }
            $node = $node->next;
        }

        return null;
    }

    /**
     * Remove key-value pair
     */
    public function remove(string $key): bool
    {
        $index = $this->hash($key);
        $node = $this->buckets[$index];
        $prev = null;

        while ($node !== null) {
            if ($node->key === $key) {
                if ($prev === null) {
                    $this->buckets[$index] = $node->next;
                } else {
                    $prev->next = $node->next;
                }
                $this->count--;
                return true;
            }
            $prev = $node;
            $node = $node->next;
        }

        return false;
    }

    /**
     * Resize hash table (double the size)
     */
    private function resize(): void
    {
        $this->resizeCount++;
        $oldBuckets = $this->buckets;
        $oldSize = $this->size;

        // Double the size
        $this->size *= 2;
        $this->buckets = array_fill(0, $this->size, null);
        $this->count = 0;

        // Rehash all elements
        foreach ($oldBuckets as $node) {
            while ($node !== null) {
                $this->put($node->key, $node->value);
                $node = $node->next;
            }
        }

        // Restore count (put() incremented it)
        $this->count = $this->count / 2;

        echo "  ⚠️ RESIZED: $oldSize → {$this->size} buckets " .
             "(load factor: " . number_format($this->getLoadFactor(), 2) . ")\n";
    }

    /**
     * Hash function
     */
    private function hash(string $key): int
    {
        $hash = 5381;
        $len = strlen($key);

        for ($i = 0; $i < $len; $i++) {
            $hash = (($hash << 5) + $hash) + ord($key[$i]);
        }

        return abs($hash) % $this->size;
    }

    /**
     * Get current load factor
     */
    public function getLoadFactor(): float
    {
        return $this->count / $this->size;
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $maxChain = 0;
        $emptyBuckets = 0;
        $usedBuckets = 0;

        foreach ($this->buckets as $node) {
            if ($node === null) {
                $emptyBuckets++;
            } else {
                $usedBuckets++;
                $chainLength = 0;
                while ($node !== null) {
                    $chainLength++;
                    $node = $node->next;
                }
                $maxChain = max($maxChain, $chainLength);
            }
        }

        return [
            'size' => $this->size,
            'count' => $this->count,
            'loadFactor' => $this->getLoadFactor(),
            'emptyBuckets' => $emptyBuckets,
            'usedBuckets' => $usedBuckets,
            'maxChainLength' => $maxChain,
            'resizeCount' => $this->resizeCount,
        ];
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getResizeCount(): int
    {
        return $this->resizeCount;
    }
}

// Demo
echo "=== Hash Table Dynamic Resizing ===\n\n";

echo "Load Factor = Number of Elements / Table Size\n";
echo "When load factor exceeds threshold (0.75), table doubles in size\n\n";

// Test 1: Watch Resizing in Action
echo "1. Automatic Resizing\n";
echo "======================\n";

$table = new DynamicHashTable(4); // Start small to see resizing
echo "Initial size: 4 buckets\n";
echo "Load factor threshold: 0.75\n\n";

for ($i = 1; $i <= 20; $i++) {
    echo "Insert key_$i (count: $i)\n";
    $table->put("key_$i", "value_$i");

    if ($i % 5 === 0) {
        $stats = $table->getStats();
        echo "  Size: {$stats['size']}, Load Factor: " .
             number_format($stats['loadFactor'], 2) . "\n\n";
    }
}

// Test 2: Statistics After Resizing
echo "2. Final Statistics\n";
echo "====================\n";

$stats = $table->getStats();
echo "Total elements:    {$stats['count']}\n";
echo "Table size:        {$stats['size']} buckets\n";
echo "Load factor:       " . number_format($stats['loadFactor'], 2) . "\n";
echo "Empty buckets:     {$stats['emptyBuckets']}\n";
echo "Used buckets:      {$stats['usedBuckets']}\n";
echo "Max chain length:  {$stats['maxChainLength']}\n";
echo "Resize operations: {$stats['resizeCount']}\n\n";

// Test 3: Performance Impact
echo "3. Performance Impact of Resizing\n";
echo "==================================\n";

echo "Testing with 10,000 insertions...\n\n";

// Small initial size (more resizes)
$smallTable = new DynamicHashTable(8);
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $smallTable->put("key_$i", $i);
}
$smallTime = (microtime(true) - $start) * 1000;

// Large initial size (fewer resizes)
$largeTable = new DynamicHashTable(8192);
$start = microtime(true);
for ($i = 0; $i < 10000; $i++) {
    $largeTable->put("key_$i", $i);
}
$largeTime = (microtime(true) - $start) * 1000;

$smallStats = $smallTable->getStats();
$largeStats = $largeTable->getStats();

echo "Small initial size (8 buckets):\n";
echo "  Time:          " . number_format($smallTime, 3) . " ms\n";
echo "  Resizes:       {$smallStats['resizeCount']}\n";
echo "  Final size:    {$smallStats['size']} buckets\n";
echo "  Load factor:   " . number_format($smallStats['loadFactor'], 3) . "\n\n";

echo "Large initial size (8192 buckets):\n";
echo "  Time:          " . number_format($largeTime, 3) . " ms\n";
echo "  Resizes:       {$largeStats['resizeCount']}\n";
echo "  Final size:    {$largeStats['size']} buckets\n";
echo "  Load factor:   " . number_format($largeStats['loadFactor'], 3) . "\n\n";

echo "Despite resizing overhead, small initial size is only ~" .
     number_format($smallTime / $largeTime, 2) . "x slower\n";
echo "This demonstrates AMORTIZED O(1) insertion!\n\n";

// Test 4: Resizing Visualization
echo "4. Growth Pattern Visualization\n";
echo "================================\n";

$viz = new DynamicHashTable(2);
$growthPattern = [];

for ($i = 1; $i <= 100; $i++) {
    $oldSize = $viz->getSize();
    $viz->put("item_$i", $i);
    $newSize = $viz->getSize();

    if ($newSize !== $oldSize) {
        $growthPattern[] = [
            'at_count' => $i,
            'old_size' => $oldSize,
            'new_size' => $newSize,
        ];
    }
}

echo "Resize events during 100 insertions:\n";
echo "Count | Old Size | New Size | Doubling\n";
echo "------|----------|----------|----------\n";

foreach ($growthPattern as $growth) {
    echo sprintf(
        "%5d | %8d | %8d | %s\n",
        $growth['at_count'],
        $growth['old_size'],
        $growth['new_size'],
        str_repeat('█', (int)log($growth['new_size'], 2))
    );
}

echo "\nPattern: Size doubles each time (2 → 4 → 8 → 16 → 32 → 64 → 128)\n\n";

// Test 5: Load Factor Impact on Performance
echo "5. Load Factor Impact on Performance\n";
echo "======================================\n";

function testLoadFactor(float $threshold): array
{
    $table = new DynamicHashTable(16);

    // Temporarily modify threshold (for demonstration)
    $reflection = new ReflectionClass($table);
    $property = $reflection->getProperty('loadFactorThreshold');
    $property->setAccessible(true);
    $property->setValue($table, $threshold);

    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $table->put("key_$i", $i);
    }
    $insertTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $table->get("key_$i");
    }
    $searchTime = (microtime(true) - $start) * 1000;

    $stats = $table->getStats();

    return [
        'threshold' => $threshold,
        'insertTime' => $insertTime,
        'searchTime' => $searchTime,
        'resizes' => $stats['resizeCount'],
        'finalSize' => $stats['size'],
        'maxChain' => $stats['maxChainLength'],
    ];
}

echo "Testing different load factor thresholds:\n\n";
echo "Threshold | Inserts (ms) | Searches (ms) | Resizes | Max Chain\n";
echo "----------|--------------|---------------|---------|----------\n";

foreach ([0.5, 0.75, 1.0, 2.0] as $threshold) {
    $result = testLoadFactor($threshold);
    echo sprintf(
        "   %4.2f   |    %7.3f   |    %7.3f    |   %2d    |    %2d\n",
        $result['threshold'],
        $result['insertTime'],
        $result['searchTime'],
        $result['resizes'],
        $result['maxChain']
    );
}

echo "\nObservations:\n";
echo "  • Lower threshold (0.5): More resizes, shorter chains, faster searches\n";
echo "  • Higher threshold (2.0): Fewer resizes, longer chains, slower searches\n";
echo "  • Optimal (0.75): Good balance between space and time\n\n";

// Test 6: Amortized Analysis
echo "6. Amortized Cost Analysis\n";
echo "===========================\n";

$amortized = new DynamicHashTable(4);
$operations = [];

for ($i = 1; $i <= 64; $i++) {
    $start = microtime(true);
    $amortized->put("key_$i", $i);
    $time = (microtime(true) - $start) * 1000000; // microseconds

    $operations[] = [
        'count' => $i,
        'time' => $time,
        'size' => $amortized->getSize(),
    ];
}

echo "Individual operation times (first 32):\n";
echo "Insert # | Time (μs) | Table Size | Note\n";
echo "---------|-----------|------------|-----\n";

foreach (array_slice($operations, 0, 32) as $op) {
    $note = '';
    if ($op['count'] > 1 && $operations[$op['count'] - 2]['size'] !== $op['size']) {
        $note = '← RESIZE';
    }

    echo sprintf(
        "%8d | %9.3f | %10d | %s\n",
        $op['count'],
        $op['time'],
        $op['size'],
        $note
    );
}

$avgTime = array_sum(array_column($operations, 'time')) / count($operations);
echo "\nAverage time per operation: " . number_format($avgTime, 3) . " μs\n";
echo "Even though some operations are expensive (resize), \n";
echo "the AMORTIZED cost per operation is still O(1)!\n\n";

echo "7. Key Insights\n";
echo "================\n";
echo "Why Dynamic Resizing?\n";
echo "  • Unknown number of elements at creation time\n";
echo "  • Maintain good load factor for performance\n";
echo "  • Prevent excessive collisions\n\n";

echo "Resizing Strategy:\n";
echo "  1. Monitor load factor (count / size)\n";
echo "  2. When threshold exceeded (typically 0.75):\n";
echo "     a) Create new table (usually 2x size)\n";
echo "     b) Rehash all existing elements\n";
echo "     c) Replace old table\n\n";

echo "Time Complexity:\n";
echo "  • Single resize:    O(n) - must rehash all elements\n";
echo "  • Amortized insert: O(1) - resizes are infrequent\n";
echo "  • Get/Remove:       O(1) average\n\n";

echo "Space-Time Tradeoff:\n";
echo "  • Low threshold (0.5):  More space, faster lookups\n";
echo "  • High threshold (2.0): Less space, slower lookups\n";
echo "  • Standard (0.75):      Balanced approach\n\n";

echo "Real-World Examples:\n";
echo "  ✓ Java HashMap:    Default capacity 16, load factor 0.75\n";
echo "  ✓ Python dict:     Resizes at 2/3 full\n";
echo "  ✓ PHP arrays:      Dynamic resizing built-in\n";
echo "  ✓ Redis:           Incremental resizing for large tables\n";

echo "\n✅ Complete! Hash table resizing demonstrated.\n";
