---
title: "13: Hash Tables & Hash Functions"
description: "Build a hash table from scratch. Learn collision handling with chaining and open addressing."
series: "php-algorithms"
chapter: 13
order: 13
difficulty: "Advanced"
prerequisites:
  - "Understanding of arrays"
  - "Familiarity with linked lists"
  - "Completion of Chapters 11-12"
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 13</span>
</div>

# Hash Tables & Hash Functions <span class="difficulty-badge difficulty-advanced">Advanced</span>

## What You'll Learn

- Build hash tables from scratch with proper hash functions
- Implement collision handling using separate chaining and open addressing
- Master advanced techniques like Robin Hood, Cuckoo, and Hopscotch hashing
- Apply hash tables to solve real-world problems efficiently
- Understand security considerations and protect against hash flooding attacks

**Estimated Time**: ~60 minutes

## Prerequisites

Before starting this chapter, you should have:

- ✓ Understanding of arrays and how they work
- ✓ Familiarity with linked lists (covered in Chapter 16, but helpful context)
- ✓ Completion of Chapters 11-12 (search algorithms)
- ✓ Basic understanding of O(1) time complexity

Hash tables are one of the most important data structures in computer science, providing **O(1) average-case** lookups, insertions, and deletions. Think of them as the secret sauce behind PHP's lightning-fast associative arrays! In this chapter, we'll build hash tables from scratch, design hash functions, and handle collisions effectively.

## What Is a Hash Table?

A **hash table** (or hash map) stores key-value pairs and uses a **hash function** to compute an index where the value should be stored.

**Concept:**
```
Key → Hash Function → Index → Value

"Alice" → hash("Alice") → 3 → "alice@example.com"
"Bob"   → hash("Bob")   → 7 → "bob@example.com"
```

**Array representation:**
```
Index:  0    1    2    3              4    5    6    7
Value: null null null alice@...com  null null null bob@...com
```

## How Hash Tables Work

### The Hash Function

A hash function converts a key into an array index:

```php
function simpleHash(string $key, int $size): int
{
    $hash = 0;

    for ($i = 0; $i < strlen($key); $i++) {
        $hash += ord($key[$i]);
    }

    return $hash % $size; // Mod to fit in array
}

echo simpleHash("Alice", 10); // Some index 0-9
echo simpleHash("Bob", 10);   // Some index 0-9
```

### Basic Hash Table Operations

- **Insert**: hash(key) → index, store value at index
- **Search**: hash(key) → index, retrieve value at index
- **Delete**: hash(key) → index, remove value at index

**All O(1) average case!**

## Building a Simple Hash Table

```php
class HashTable
{
    private array $table;
    private int $size;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, null);
    }

    private function hash(string $key): int
    {
        $hash = 0;

        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }

        return $hash;
    }

    public function set(string $key, mixed $value): void
    {
        $index = $this->hash($key);
        $this->table[$index] = $value;
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);
        return $this->table[$index];
    }

    public function delete(string $key): void
    {
        $index = $this->hash($key);
        $this->table[$index] = null;
    }

    public function has(string $key): bool
    {
        $index = $this->hash($key);
        return $this->table[$index] !== null;
    }
}

// Usage
$ht = new HashTable();
$ht->set("name", "Alice");
$ht->set("age", 30);

echo $ht->get("name"); // Alice
echo $ht->get("age");  // 30
```

**Problem:** What happens when two keys hash to the same index? **Collision!**

## Collision Handling

Two main strategies for handling collisions:

### 1. Separate Chaining

Store multiple key-value pairs at each index using a linked list or array:

```php
class HashTableChaining
{
    private array $table;
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, []);
    }

    private function hash(string $key): int
    {
        $hash = 0;

        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }

        return abs($hash);
    }

    public function set(string $key, mixed $value): void
    {
        $index = $this->hash($key);

        // Check if key already exists
        foreach ($this->table[$index] as &$pair) {
            if ($pair['key'] === $key) {
                $pair['value'] = $value;
                return;
            }
        }

        // Add new key-value pair
        $this->table[$index][] = ['key' => $key, 'value' => $value];
        $this->count++;
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);

        foreach ($this->table[$index] as $pair) {
            if ($pair['key'] === $key) {
                return $pair['value'];
            }
        }

        return null;
    }

    public function delete(string $key): bool
    {
        $index = $this->hash($key);

        foreach ($this->table[$index] as $i => $pair) {
            if ($pair['key'] === $key) {
                array_splice($this->table[$index], $i, 1);
                $this->count--;
                return true;
            }
        }

        return false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function size(): int
    {
        return $this->count;
    }

    public function keys(): array
    {
        $keys = [];

        foreach ($this->table as $bucket) {
            foreach ($bucket as $pair) {
                $keys[] = $pair['key'];
            }
        }

        return $keys;
    }

    public function values(): array
    {
        $values = [];

        foreach ($this->table as $bucket) {
            foreach ($bucket as $pair) {
                $values[] = $pair['value'];
            }
        }

        return $values;
    }

    public function getLoadFactor(): float
    {
        return $this->count / $this->size;
    }
}

// Usage
$ht = new HashTableChaining();
$ht->set("Alice", "alice@example.com");
$ht->set("Bob", "bob@example.com");
$ht->set("Charlie", "charlie@example.com");

echo $ht->get("Bob"); // bob@example.com
print_r($ht->keys());  // ['Alice', 'Bob', 'Charlie']
```

### 2. Open Addressing

Store all entries in the main array, probe for the next available slot on collision:

#### Linear Probing

```php
class HashTableLinearProbing
{
    private array $keys;
    private array $values;
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->keys = array_fill(0, $size, null);
        $this->values = array_fill(0, $size, null);
    }

    private function hash(string $key): int
    {
        $hash = 0;

        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }

        return abs($hash);
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->count >= $this->size * 0.7) {
            $this->resize();
        }

        $index = $this->hash($key);

        // Linear probing: find next available slot
        while ($this->keys[$index] !== null && $this->keys[$index] !== $key) {
            $index = ($index + 1) % $this->size;
        }

        $isNew = $this->keys[$index] === null;
        $this->keys[$index] = $key;
        $this->values[$index] = $value;

        if ($isNew) {
            $this->count++;
        }
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);

        // Linear probing: search for key
        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                return $this->values[$index];
            }
            $index = ($index + 1) % $this->size;
        }

        return null;
    }

    public function delete(string $key): bool
    {
        $index = $this->hash($key);

        // Find the key
        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                $this->keys[$index] = null;
                $this->values[$index] = null;
                $this->count--;

                // Rehash subsequent entries
                $this->rehashFrom($index);
                return true;
            }
            $index = ($index + 1) % $this->size;
        }

        return false;
    }

    private function rehashFrom(int $start): void
    {
        $index = ($start + 1) % $this->size;

        while ($this->keys[$index] !== null) {
            $key = $this->keys[$index];
            $value = $this->values[$index];

            $this->keys[$index] = null;
            $this->values[$index] = null;
            $this->count--;

            $this->set($key, $value);

            $index = ($index + 1) % $this->size;
        }
    }

    private function resize(): void
    {
        $oldKeys = $this->keys;
        $oldValues = $this->values;

        $this->size *= 2;
        $this->keys = array_fill(0, $this->size, null);
        $this->values = array_fill(0, $this->size, null);
        $this->count = 0;

        foreach ($oldKeys as $i => $key) {
            if ($key !== null) {
                $this->set($key, $oldValues[$i]);
            }
        }
    }
}
```

#### Quadratic Probing

```php
private function quadraticProbe(string $key): int
{
    $index = $this->hash($key);
    $i = 0;

    // Try index, index + 1², index + 2², etc.
    while ($this->keys[$index] !== null && $this->keys[$index] !== $key) {
        $i++;
        $index = ($this->hash($key) + $i * $i) % $this->size;
    }

    return $index;
}
```

#### Double Hashing

```php
private function doubleHash(string $key): int
{
    $hash1 = $this->hash($key);
    $hash2 = 7 - ($hash1 % 7); // Secondary hash function

    $index = $hash1;
    $i = 0;

    while ($this->keys[$index] !== null && $this->keys[$index] !== $key) {
        $i++;
        $index = ($hash1 + $i * $hash2) % $this->size;
    }

    return $index;
}
```

## Designing Good Hash Functions

### Properties of Good Hash Functions

1. **Deterministic**: Same input always produces same output
2. **Uniform distribution**: Spreads keys evenly across table
3. **Fast to compute**: O(1) complexity
4. **Minimizes collisions**

### Common Hash Function Techniques

#### 1. Division Method

```php
function hashDivision(string $key, int $size): int
{
    $hash = 0;

    for ($i = 0; $i < strlen($key); $i++) {
        $hash += ord($key[$i]);
    }

    return $hash % $size;
}
```

#### 2. Multiplication Method

```php
function hashMultiplication(string $key, int $size): int
{
    $hash = 0;
    $A = 0.6180339887; // Golden ratio

    for ($i = 0; $i < strlen($key); $i++) {
        $hash += ord($key[$i]);
    }

    return (int)($size * (($hash * $A) - floor($hash * $A)));
}
```

#### 3. Polynomial Rolling Hash

```php
function hashPolynomial(string $key, int $size): int
{
    $hash = 0;
    $prime = 31; // Prime number

    for ($i = 0; $i < strlen($key); $i++) {
        $hash = ($hash * $prime + ord($key[$i])) % $size;
    }

    return abs($hash);
}
```

#### 4. FNV-1a Hash (Fast, Good Distribution)

```php
function hashFNV1a(string $key): int
{
    $hash = 2166136261; // FNV offset basis

    for ($i = 0; $i < strlen($key); $i++) {
        $hash ^= ord($key[$i]);
        $hash *= 16777619; // FNV prime
    }

    return abs($hash);
}
```

### PHP's Built-in Hash Functions

```php
// For strings - very fast
$hash = crc32("my-key");

// Cryptographic (slower, but better distribution)
$hash = hash('fnv1a32', "my-key");

// MD5 (don't use for security, but okay for hash tables)
$hash = md5("my-key", true);
```

## Load Factor and Resizing

**Load factor** = number of entries / table size

```php
class ResizableHashTable extends HashTableChaining
{
    private const MAX_LOAD_FACTOR = 0.75;

    public function set(string $key, mixed $value): void
    {
        parent::set($key, $value);

        // Resize if load factor too high
        if ($this->getLoadFactor() > self::MAX_LOAD_FACTOR) {
            $this->resize();
        }
    }

    private function resize(): void
    {
        $oldTable = $this->table;
        $this->size *= 2;
        $this->table = array_fill(0, $this->size, []);
        $this->count = 0;

        // Rehash all entries
        foreach ($oldTable as $bucket) {
            foreach ($bucket as $pair) {
                $this->set($pair['key'], $pair['value']);
            }
        }
    }
}
```

**When to resize:**
- Load factor > 0.75: table getting full, more collisions
- Load factor < 0.25: table too empty, wasting space

## Real-World Applications

### 1. Caching

```php
class Cache
{
    private HashTableChaining $cache;
    private int $maxSize;

    public function __construct(int $maxSize = 100)
    {
        $this->cache = new HashTableChaining($maxSize);
        $this->maxSize = $maxSize;
    }

    public function get(string $key): mixed
    {
        return $this->cache->get($key);
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->cache->size() >= $this->maxSize) {
            // Evict oldest entry (simplified)
            $keys = $this->cache->keys();
            $this->cache->delete($keys[0]);
        }

        $this->cache->set($key, $value);
    }
}
```

### 2. Counting Frequencies

```php
function countFrequencies(array $items): array
{
    $frequencies = new HashTableChaining();

    foreach ($items as $item) {
        $count = $frequencies->get($item) ?? 0;
        $frequencies->set($item, $count + 1);
    }

    // Convert to array
    $result = [];
    foreach ($frequencies->keys() as $key) {
        $result[$key] = $frequencies->get($key);
    }

    return $result;
}

$words = ['apple', 'banana', 'apple', 'cherry', 'banana', 'apple'];
print_r(countFrequencies($words));
// ['apple' => 3, 'banana' => 2, 'cherry' => 1]
```

### 3. Detecting Duplicates

```php
function hasDuplicates(array $arr): bool
{
    $seen = new HashTableChaining();

    foreach ($arr as $item) {
        if ($seen->has($item)) {
            return true;
        }
        $seen->set($item, true);
    }

    return false;
}

// O(n) instead of O(n²)!
```

### 4. Two Sum Problem

```php
function twoSum(array $nums, int $target): ?array
{
    $map = new HashTableChaining();

    foreach ($nums as $i => $num) {
        $complement = $target - $num;

        if ($map->has((string)$complement)) {
            return [$map->get((string)$complement), $i];
        }

        $map->set((string)$num, $i);
    }

    return null;
}

$nums = [2, 7, 11, 15];
print_r(twoSum($nums, 9)); // [0, 1]
```

## PHP's Built-in Arrays ARE Hash Tables!

PHP arrays are actually hash tables:

```php
// Associative array = hash table
$ages = [
    'Alice' => 30,
    'Bob' => 25,
    'Charlie' => 35
];

// O(1) lookup!
echo $ages['Bob']; // 25

// O(1) insert!
$ages['David'] = 28;

// O(1) check!
if (isset($ages['Alice'])) {
    echo "Alice exists";
}
```

**PHP array operations:**
- `$arr[$key]` → O(1) get
- `$arr[$key] = $value` → O(1) set
- `isset($arr[$key])` → O(1) check
- `unset($arr[$key])` → O(1) delete
- `array_key_exists()` → O(1)
- `in_array()` → O(n) (searches values, not keys!)

## Advanced Collision Handling Strategies

### Robin Hood Hashing

Robin Hood hashing improves on linear probing by reducing variance in probe sequence lengths:

```php
class RobinHoodHashTable
{
    private array $keys;
    private array $values;
    private array $distances; // Distance from ideal position
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->keys = array_fill(0, $size, null);
        $this->values = array_fill(0, $size, null);
        $this->distances = array_fill(0, $size, -1);
    }

    private function hash(string $key): int
    {
        return abs(crc32($key) % $this->size);
    }

    public function set(string $key, mixed $value): void
    {
        $index = $this->hash($key);
        $distance = 0;

        while (true) {
            // Empty slot found
            if ($this->keys[$index] === null) {
                $this->keys[$index] = $key;
                $this->values[$index] = $value;
                $this->distances[$index] = $distance;
                $this->count++;
                return;
            }

            // Key already exists
            if ($this->keys[$index] === $key) {
                $this->values[$index] = $value;
                return;
            }

            // Robin Hood: if current item is richer (lower distance), swap
            if ($distance > $this->distances[$index]) {
                // Swap
                $tempKey = $this->keys[$index];
                $tempValue = $this->values[$index];
                $tempDist = $this->distances[$index];

                $this->keys[$index] = $key;
                $this->values[$index] = $value;
                $this->distances[$index] = $distance;

                $key = $tempKey;
                $value = $tempValue;
                $distance = $tempDist;
            }

            $index = ($index + 1) % $this->size;
            $distance++;
        }
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);
        $distance = 0;

        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                return $this->values[$index];
            }

            // If we've gone further than this key's distance, it's not here
            if ($distance > $this->distances[$index]) {
                return null;
            }

            $index = ($index + 1) % $this->size;
            $distance++;
        }

        return null;
    }
}
```

### Cuckoo Hashing

Uses two hash functions and two tables, guaranteeing O(1) lookup:

```php
class CuckooHashTable
{
    private array $table1;
    private array $table2;
    private int $size;
    private int $count = 0;
    private const MAX_KICKS = 100;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table1 = array_fill(0, $size, null);
        $this->table2 = array_fill(0, $size, null);
    }

    private function hash1(string $key): int
    {
        return abs(crc32($key) % $this->size);
    }

    private function hash2(string $key): int
    {
        $hash = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }
        return abs($hash);
    }

    public function set(string $key, mixed $value): void
    {
        // Check if key already exists
        $idx1 = $this->hash1($key);
        $idx2 = $this->hash2($key);

        if ($this->table1[$idx1] !== null && $this->table1[$idx1]['key'] === $key) {
            $this->table1[$idx1]['value'] = $value;
            return;
        }

        if ($this->table2[$idx2] !== null && $this->table2[$idx2]['key'] === $key) {
            $this->table2[$idx2]['value'] = $value;
            return;
        }

        // Insert new key
        $item = ['key' => $key, 'value' => $value];
        $currentTable = 1;
        $kicks = 0;

        while ($kicks < self::MAX_KICKS) {
            if ($currentTable === 1) {
                $idx = $this->hash1($item['key']);

                if ($this->table1[$idx] === null) {
                    $this->table1[$idx] = $item;
                    $this->count++;
                    return;
                }

                // Kick out existing item
                $temp = $this->table1[$idx];
                $this->table1[$idx] = $item;
                $item = $temp;
                $currentTable = 2;
            } else {
                $idx = $this->hash2($item['key']);

                if ($this->table2[$idx] === null) {
                    $this->table2[$idx] = $item;
                    $this->count++;
                    return;
                }

                // Kick out existing item
                $temp = $this->table2[$idx];
                $this->table2[$idx] = $item;
                $item = $temp;
                $currentTable = 1;
            }

            $kicks++;
        }

        // Rehash if too many kicks
        $this->rehash();
        $this->set($key, $value);
    }

    public function get(string $key): mixed
    {
        $idx1 = $this->hash1($key);
        if ($this->table1[$idx1] !== null && $this->table1[$idx1]['key'] === $key) {
            return $this->table1[$idx1]['value'];
        }

        $idx2 = $this->hash2($key);
        if ($this->table2[$idx2] !== null && $this->table2[$idx2]['key'] === $key) {
            return $this->table2[$idx2]['value'];
        }

        return null;
    }

    private function rehash(): void
    {
        $oldTable1 = $this->table1;
        $oldTable2 = $this->table2;

        $this->size *= 2;
        $this->table1 = array_fill(0, $this->size, null);
        $this->table2 = array_fill(0, $this->size, null);
        $this->count = 0;

        foreach ($oldTable1 as $item) {
            if ($item !== null) {
                $this->set($item['key'], $item['value']);
            }
        }

        foreach ($oldTable2 as $item) {
            if ($item !== null) {
                $this->set($item['key'], $item['value']);
            }
        }
    }
}
```

### Hopscotch Hashing

Combines benefits of chaining and open addressing:

```php
class HopscotchHashTable
{
    private array $table;
    private array $hopInfo; // Bitmap of nearby items
    private int $size;
    private int $hopRange = 32; // Neighborhood size

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, null);
        $this->hopInfo = array_fill(0, $size, 0);
    }

    private function hash(string $key): int
    {
        return abs(crc32($key) % $this->size);
    }

    public function set(string $key, mixed $value): void
    {
        $idx = $this->hash($key);

        // Find empty slot
        $emptyIdx = $this->findEmptySlot($idx);

        if ($emptyIdx === false) {
            // Table too full, need to resize
            return;
        }

        // Move empty slot into hop range if needed
        while ($emptyIdx - $idx >= $this->hopRange) {
            $emptyIdx = $this->moveCloser($idx, $emptyIdx);
            if ($emptyIdx === false) {
                return;
            }
        }

        // Insert item
        $this->table[$emptyIdx] = ['key' => $key, 'value' => $value];
        $this->hopInfo[$idx] |= (1 << ($emptyIdx - $idx));
    }

    private function findEmptySlot(int $start): int|false
    {
        for ($i = 0; $i < $this->size; $i++) {
            $idx = ($start + $i) % $this->size;
            if ($this->table[$idx] === null) {
                return $idx;
            }
        }
        return false;
    }

    private function moveCloser(int $target, int $empty): int|false
    {
        // Implementation of repositioning algorithm
        // Simplified for brevity
        return false;
    }

    public function get(string $key): mixed
    {
        $idx = $this->hash($key);
        $hopInfo = $this->hopInfo[$idx];

        // Check all positions in hop range
        for ($i = 0; $i < $this->hopRange; $i++) {
            if ($hopInfo & (1 << $i)) {
                $checkIdx = ($idx + $i) % $this->size;
                if ($this->table[$checkIdx] !== null &&
                    $this->table[$checkIdx]['key'] === $key) {
                    return $this->table[$checkIdx]['value'];
                }
            }
        }

        return null;
    }
}
```

## Performance Benchmarks: Collision Strategies

Comparing different collision handling approaches:

```php
class CollisionBenchmark
{
    public function compareStrategies(): void
    {
        $sizes = [1000, 10000, 50000];
        $loadFactors = [0.5, 0.75, 0.9];

        foreach ($sizes as $size) {
            echo "\n=== Testing with $size items ===\n";

            foreach ($loadFactors as $loadFactor) {
                $capacity = (int)($size / $loadFactor);

                echo "\nLoad Factor: $loadFactor\n";
                echo str_repeat('-', 50) . "\n";

                // Generate test data
                $keys = $this->generateKeys($size);
                $values = range(1, $size);

                // Test Chaining
                $chaining = new HashTableChaining($capacity);
                $start = microtime(true);
                foreach ($keys as $i => $key) {
                    $chaining->set($key, $values[$i]);
                }
                $chainingInsert = microtime(true) - $start;

                $start = microtime(true);
                foreach ($keys as $key) {
                    $chaining->get($key);
                }
                $chainingSearch = microtime(true) - $start;

                // Test Linear Probing
                $linear = new HashTableLinearProbing($capacity);
                $start = microtime(true);
                foreach ($keys as $i => $key) {
                    $linear->set($key, $values[$i]);
                }
                $linearInsert = microtime(true) - $start;

                $start = microtime(true);
                foreach ($keys as $key) {
                    $linear->get($key);
                }
                $linearSearch = microtime(true) - $start;

                // Test Robin Hood
                $robinHood = new RobinHoodHashTable($capacity);
                $start = microtime(true);
                foreach ($keys as $i => $key) {
                    $robinHood->set($key, $values[$i]);
                }
                $robinInsert = microtime(true) - $start;

                $start = microtime(true);
                foreach ($keys as $key) {
                    $robinHood->get($key);
                }
                $robinSearch = microtime(true) - $start;

                // Display results
                printf("Chaining:      Insert: %.4fs, Search: %.4fs\n",
                    $chainingInsert, $chainingSearch);
                printf("Linear Probe:  Insert: %.4fs, Search: %.4fs\n",
                    $linearInsert, $linearSearch);
                printf("Robin Hood:    Insert: %.4fs, Search: %.4fs\n",
                    $robinInsert, $robinSearch);
            }
        }
    }

    private function generateKeys(int $count): array
    {
        $keys = [];
        for ($i = 0; $i < $count; $i++) {
            $keys[] = 'key_' . bin2hex(random_bytes(8));
        }
        return $keys;
    }

    public function memoryComparison(): void
    {
        $size = 10000;
        $keys = $this->generateKeys($size);

        echo "\n=== Memory Usage Comparison ===\n\n";

        // Chaining
        $memBefore = memory_get_usage();
        $chaining = new HashTableChaining($size);
        foreach ($keys as $i => $key) {
            $chaining->set($key, $i);
        }
        $chainingMem = memory_get_usage() - $memBefore;

        // Linear Probing
        $memBefore = memory_get_usage();
        $linear = new HashTableLinearProbing($size);
        foreach ($keys as $i => $key) {
            $linear->set($key, $i);
        }
        $linearMem = memory_get_usage() - $memBefore;

        // Robin Hood
        $memBefore = memory_get_usage();
        $robin = new RobinHoodHashTable($size);
        foreach ($keys as $i => $key) {
            $robin->set($key, $i);
        }
        $robinMem = memory_get_usage() - $memBefore;

        printf("Chaining:     %s\n", $this->formatBytes($chainingMem));
        printf("Linear Probe: %s\n", $this->formatBytes($linearMem));
        printf("Robin Hood:   %s\n", $this->formatBytes($robinMem));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// Run benchmarks
$benchmark = new CollisionBenchmark();
$benchmark->compareStrategies();
$benchmark->memoryComparison();
```

## PHP SPL Implementations

### Using SplObjectStorage as Hash Table

```php
class SPLHashExamples
{
    /**
     * SplObjectStorage - hash table for objects
     */
    public function objectHashTable(): void
    {
        $storage = new SplObjectStorage();

        // Create objects
        $user1 = new stdClass();
        $user1->name = 'Alice';
        $user1->email = 'alice@example.com';

        $user2 = new stdClass();
        $user2->name = 'Bob';
        $user2->email = 'bob@example.com';

        // Store objects with associated data
        $storage[$user1] = ['role' => 'admin', 'lastLogin' => time()];
        $storage[$user2] = ['role' => 'user', 'lastLogin' => time() - 3600];

        // Retrieve
        echo $storage[$user1]['role']; // admin

        // Check existence
        if ($storage->contains($user1)) {
            echo "User exists\n";
        }

        // Iterate
        foreach ($storage as $user) {
            $data = $storage[$user];
            echo "{$user->name}: {$data['role']}\n";
        }
    }

    /**
     * Custom hash table using SplFixedArray
     */
    public function fixedArrayHashTable(): void
    {
        $size = 1000;
        $table = new SplFixedArray($size);

        $hash = function($key) use ($size) {
            return abs(crc32($key) % $size);
        };

        // Set value
        $set = function($key, $value) use ($table, $hash) {
            $idx = $hash($key);

            if (!isset($table[$idx])) {
                $table[$idx] = [];
            }

            $table[$idx][$key] = $value;
        };

        // Get value
        $get = function($key) use ($table, $hash) {
            $idx = $hash($key);

            if (!isset($table[$idx])) {
                return null;
            }

            return $table[$idx][$key] ?? null;
        };

        // Usage
        $set('name', 'John');
        $set('age', 30);

        echo $get('name'); // John
        echo $get('age');  // 30
    }

    /**
     * Weak reference hash table
     */
    public function weakReferenceHash(): void
    {
        if (!class_exists('WeakMap')) {
            echo "WeakMap not available (PHP 8.0+)\n";
            return;
        }

        $map = new WeakMap();

        $obj1 = new stdClass();
        $obj2 = new stdClass();

        $map[$obj1] = 'data for obj1';
        $map[$obj2] = 'data for obj2';

        echo $map[$obj1]; // 'data for obj1'

        unset($obj1); // Object and its entry are garbage collected
    }
}

$examples = new SPLHashExamples();
$examples->objectHashTable();
$examples->fixedArrayHashTable();
$examples->weakReferenceHash();
```

## Security Considerations

### Hash Flooding Attacks

Protect against algorithmic complexity attacks:

```php
class SecureHashTable
{
    private array $table;
    private int $size;
    private string $randomSeed;
    private int $maxChainLength = 8;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, []);

        // Random seed to prevent hash prediction
        $this->randomSeed = bin2hex(random_bytes(16));
    }

    /**
     * Secure hash function with random seed
     */
    private function hash(string $key): int
    {
        // Use random seed to make hash unpredictable
        $hash = hash_hmac('sha256', $key, $this->randomSeed);
        return abs(hexdec(substr($hash, 0, 8)) % $this->size);
    }

    public function set(string $key, mixed $value): void
    {
        $index = $this->hash($key);

        // Check chain length to prevent DOS
        if (count($this->table[$index]) >= $this->maxChainLength) {
            $this->resize();
            $index = $this->hash($key);
        }

        // Check if key exists
        foreach ($this->table[$index] as &$pair) {
            if ($pair['key'] === $key) {
                $pair['value'] = $value;
                return;
            }
        }

        // Add new entry
        $this->table[$index][] = ['key' => $key, 'value' => $value];
    }

    public function get(string $key): mixed
    {
        $index = $this->hash($key);

        foreach ($this->table[$index] as $pair) {
            if ($pair['key'] === $key) {
                return $pair['value'];
            }
        }

        return null;
    }

    private function resize(): void
    {
        $oldTable = $this->table;
        $this->size *= 2;
        $this->table = array_fill(0, $this->size, []);

        // Rehash all entries
        foreach ($oldTable as $bucket) {
            foreach ($bucket as $pair) {
                $this->set($pair['key'], $pair['value']);
            }
        }
    }

    /**
     * Rate limiting for set operations
     */
    private array $setAttempts = [];

    public function setWithRateLimit(
        string $clientId,
        string $key,
        mixed $value,
        int $maxOps = 1000,
        int $windowSec = 60
    ): bool {
        $now = time();

        if (!isset($this->setAttempts[$clientId])) {
            $this->setAttempts[$clientId] = ['count' => 0, 'window' => $now];
        }

        $clientData = &$this->setAttempts[$clientId];

        // Reset window if expired
        if ($now - $clientData['window'] > $windowSec) {
            $clientData = ['count' => 0, 'window' => $now];
        }

        // Check rate limit
        if ($clientData['count'] >= $maxOps) {
            throw new Exception("Rate limit exceeded for client: $clientId");
        }

        $clientData['count']++;
        $this->set($key, $value);

        return true;
    }
}
```

### Cryptographic Hash Functions for Sensitive Data

```php
class CryptographicHashTable
{
    private array $table;
    private int $size;

    public function __construct(int $size = 100)
    {
        $this->size = $size;
        $this->table = array_fill(0, $size, []);
    }

    /**
     * Use cryptographic hash for sensitive keys
     */
    private function hash(string $key): int
    {
        $hash = hash('sha256', $key);
        return abs(hexdec(substr($hash, 0, 8)) % $this->size);
    }

    /**
     * Store sensitive data with encryption
     */
    public function setSecure(string $key, string $sensitiveValue, string $encryptionKey): void
    {
        // Encrypt value
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $sensitiveValue,
            'aes-256-cbc',
            $encryptionKey,
            0,
            $iv
        );

        $index = $this->hash($key);

        $this->table[$index][] = [
            'key_hash' => hash('sha256', $key), // Don't store actual key
            'value' => base64_encode($encrypted),
            'iv' => base64_encode($iv)
        ];
    }

    /**
     * Retrieve and decrypt sensitive data
     */
    public function getSecure(string $key, string $encryptionKey): ?string
    {
        $index = $this->hash($key);
        $keyHash = hash('sha256', $key);

        foreach ($this->table[$index] as $pair) {
            if ($pair['key_hash'] === $keyHash) {
                $decrypted = openssl_decrypt(
                    base64_decode($pair['value']),
                    'aes-256-cbc',
                    $encryptionKey,
                    0,
                    base64_decode($pair['iv'])
                );

                return $decrypted !== false ? $decrypted : null;
            }
        }

        return null;
    }
}

// Usage
$secureTable = new CryptographicHashTable();
$encryptionKey = hash('sha256', 'your-secret-key', true);

$secureTable->setSecure('user:123:ssn', '123-45-6789', $encryptionKey);
$ssn = $secureTable->getSecure('user:123:ssn', $encryptionKey);
```

## Complexity Analysis

| Operation | Average | Worst Case |
|-----------|---------|------------|
| **Insert** | O(1) | O(n)* |
| **Search** | O(1) | O(n)* |
| **Delete** | O(1) | O(n)* |
| **Space** | O(n) | O(n) |

*Worst case happens with many collisions or poor hash function

| Collision Strategy | Insert | Search | Delete | Memory | Best For |
|-------------------|---------|---------|---------|---------|----------|
| **Chaining** | O(1) avg | O(1) avg | O(1) avg | High | General use |
| **Linear Probing** | O(1) avg | O(1) avg | O(1) avg | Low | Cache-friendly |
| **Quadratic Probing** | O(1) avg | O(1) avg | O(1) avg | Low | Better clustering |
| **Double Hashing** | O(1) avg | O(1) avg | O(1) avg | Low | Uniform distribution |
| **Robin Hood** | O(1) avg | O(1) avg | O(1) avg | Low | Bounded variance |
| **Cuckoo** | O(1) worst | O(1) | O(1) | Medium | Guaranteed O(1) lookup |

## Practice Exercises

### Exercise 1: Implement Set

Create a Set data structure using a hash table:

```php
class Set
{
    // Your implementation here

    public function add(mixed $value): void {}
    public function has(mixed $value): bool {}
    public function delete(mixed $value): bool {}
    public function size(): int {}
}

$set = new Set();
$set->add(1);
$set->add(2);
$set->add(1); // Duplicate ignored
echo $set->size(); // 2
```

### Exercise 2: Group Anagrams

Group strings that are anagrams:

```php
function groupAnagrams(array $words): array
{
    // Use hash table where key is sorted characters
    // Your code here
}

$words = ['eat', 'tea', 'tan', 'ate', 'nat', 'bat'];
print_r(groupAnagrams($words));
// [['eat', 'tea', 'ate'], ['tan', 'nat'], ['bat']]
```

### Exercise 3: LRU Cache

Implement an LRU (Least Recently Used) cache:

```php
class LRUCache
{
    // Use hash table + doubly linked list
    // Your implementation here

    public function get(string $key): mixed {}
    public function put(string $key, mixed $value): void {}
}
```

## Key Takeaways

- **Hash tables** provide O(1) average-case operations
- **Hash functions** convert keys to array indices
- **Collisions** are inevitable, handle with chaining or open addressing
- **Separate chaining** uses lists at each index
- **Open addressing** probes for next available slot
- **Load factor** determines when to resize
- **PHP arrays** are hash tables internally
- Good hash functions are **fast, deterministic, and distribute uniformly**

## What's Next

In the next chapter, we'll explore **String Search Algorithms**, learning pattern matching techniques like naive search, KMP, and Boyer-Moore.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 13 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-13)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-13
php 01-*.php
```

---

Continue to [Chapter 14: String Search Algorithms](/series/php-algorithms/chapters/14-string-search-algorithms).
