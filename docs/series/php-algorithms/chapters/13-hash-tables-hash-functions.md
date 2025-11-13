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

# Hash Tables & Hash Functions

Hash tables are one of the most important data structures in computer science, providing **O(1) average-case** lookups, insertions, and deletions. In this chapter, we'll build hash tables from scratch, design hash functions, and handle collisions effectively.

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

## Complexity Analysis

| Operation | Average | Worst Case |
|-----------|---------|------------|
| **Insert** | O(1) | O(n)* |
| **Search** | O(1) | O(n)* |
| **Delete** | O(1) | O(n)* |
| **Space** | O(n) | O(n) |

*Worst case happens with many collisions or poor hash function

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

---

Continue to [Chapter 14: String Search Algorithms](/series/php-algorithms/chapters/14-string-search-algorithms).
