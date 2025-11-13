---
title: "06: Hash Tables and Hash Functions"
description: "Master O(1) lookups with hash tables. Understand hash functions, collision resolution strategies (chaining, open addressing), and how PHP's associative arrays work under the hood."
series: "computer-science"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites: ["Arrays", "Linked Lists"]
---

# Chapter 06: Hash Tables and Hash Functions

## Introduction

Hash tables are one of the most important data structures in computer science, providing **average O(1)** time complexity for insertions, deletions, and lookups. They power associative arrays, database indexes, caches, and countless other applications.

In this chapter, you'll learn:

- How hash tables work
- Hash functions and their properties
- Collision resolution strategies
- Implementation in PHP
- When to use hash tables

## What is a Hash Table?

A **hash table** (or hash map) stores key-value pairs using a **hash function** to compute an index where the value should be stored:

```
Key → Hash Function → Index → Value

"alice" → hash("alice") → 3 → 30
"bob"   → hash("bob")   → 7 → 25
```

### Hash Table Structure

```
Index   Bucket
  0  →  [empty]
  1  →  [empty]
  2  →  ["charlie" => 35]
  3  →  ["alice" => 30]
  4  →  [empty]
  5  →  [empty]
  6  →  [empty]
  7  →  ["bob" => 25]
```

## Hash Functions

A **hash function** converts a key into an integer index:

```php
<?php

// Simple hash function
function simpleHash(string $key, int $tableSize): int {
    $hash = 0;

    for ($i = 0; $i < strlen($key); $i++) {
        $hash += ord($key[$i]);
    }

    return $hash % $tableSize;
}

echo simpleHash("alice", 10); // 3
echo simpleHash("bob", 10);   // 7
```

### Properties of Good Hash Functions

1. **Deterministic**: Same key always produces same hash
2. **Uniform distribution**: Keys spread evenly across table
3. **Fast to compute**: O(1) complexity
4. **Avalanche effect**: Small key change → big hash change

### Better Hash Function

```php
<?php

function betterHash(string $key, int $tableSize): int {
    $hash = 0;
    $prime = 31; // Use a prime number

    for ($i = 0; $i < strlen($key); $i++) {
        $hash = ($hash * $prime + ord($key[$i])) % $tableSize;
    }

    return abs($hash);
}
```

## Collision Resolution

**Collision**: When two keys hash to the same index

```
hash("alice") → 3
hash("clara") → 3  ← Collision!
```

### Strategy 1: Chaining (Separate Chaining)

Store a linked list at each index:

```
Index   Bucket (linked list)
  3  →  [alice=>30] → [clara=>28] → null
```

```php
<?php

class HashTableChaining {
    private array $buckets;
    private int $size;

    public function __construct(int $size = 10) {
        $this->size = $size;
        $this->buckets = array_fill(0, $size, []);
    }

    private function hash(string $key): int {
        $hash = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }
        return abs($hash);
    }

    // Insert/Update - O(1) average
    public function set(string $key, mixed $value): void {
        $index = $this->hash($key);

        // Check if key exists and update
        foreach ($this->buckets[$index] as $i => $pair) {
            if ($pair['key'] === $key) {
                $this->buckets[$index][$i]['value'] = $value;
                return;
            }
        }

        // Key doesn't exist, add new pair
        $this->buckets[$index][] = [
            'key' => $key,
            'value' => $value
        ];
    }

    // Get - O(1) average
    public function get(string $key): mixed {
        $index = $this->hash($key);

        foreach ($this->buckets[$index] as $pair) {
            if ($pair['key'] === $key) {
                return $pair['value'];
            }
        }

        return null; // Key not found
    }

    // Delete - O(1) average
    public function delete(string $key): bool {
        $index = $this->hash($key);

        foreach ($this->buckets[$index] as $i => $pair) {
            if ($pair['key'] === $key) {
                array_splice($this->buckets[$index], $i, 1);
                return true;
            }
        }

        return false; // Key not found
    }

    // Check if key exists
    public function has(string $key): bool {
        return $this->get($key) !== null;
    }

    // Get all keys
    public function keys(): array {
        $keys = [];
        foreach ($this->buckets as $bucket) {
            foreach ($bucket as $pair) {
                $keys[] = $pair['key'];
            }
        }
        return $keys;
    }

    // Get all values
    public function values(): array {
        $values = [];
        foreach ($this->buckets as $bucket) {
            foreach ($bucket as $pair) {
                $values[] = $pair['value'];
            }
        }
        return $values;
    }
}

// Usage
$hashTable = new HashTableChaining(10);
$hashTable->set("alice", 30);
$hashTable->set("bob", 25);
$hashTable->set("charlie", 35);

echo $hashTable->get("alice"); // 30
echo $hashTable->has("bob") ? "Yes" : "No"; // Yes

$hashTable->delete("bob");
echo $hashTable->get("bob"); // null
```

### Strategy 2: Open Addressing

Store all elements in the table itself. When collision occurs, probe for the next available slot.

#### Linear Probing

```
If index is occupied, try index+1, index+2, ...
```

```php
<?php

class HashTableOpenAddressing {
    private array $keys;
    private array $values;
    private int $size;
    private int $count = 0;

    public function __construct(int $size = 10) {
        $this->size = $size;
        $this->keys = array_fill(0, $size, null);
        $this->values = array_fill(0, $size, null);
    }

    private function hash(string $key): int {
        $hash = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            $hash = ($hash * 31 + ord($key[$i])) % $this->size;
        }
        return abs($hash);
    }

    // Insert/Update - O(1) average
    public function set(string $key, mixed $value): void {
        if ($this->count >= $this->size * 0.7) {
            $this->resize();
        }

        $index = $this->hash($key);

        // Linear probing
        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                // Update existing key
                $this->values[$index] = $value;
                return;
            }
            $index = ($index + 1) % $this->size; // Wrap around
        }

        // Found empty slot
        $this->keys[$index] = $key;
        $this->values[$index] = $value;
        $this->count++;
    }

    // Get - O(1) average
    public function get(string $key): mixed {
        $index = $this->hash($key);

        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                return $this->values[$index];
            }
            $index = ($index + 1) % $this->size;
        }

        return null; // Not found
    }

    // Delete - O(1) average
    public function delete(string $key): bool {
        $index = $this->hash($key);

        while ($this->keys[$index] !== null) {
            if ($this->keys[$index] === $key) {
                $this->keys[$index] = null;
                $this->values[$index] = null;
                $this->count--;
                return true;
            }
            $index = ($index + 1) % $this->size;
        }

        return false;
    }

    private function resize(): void {
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

## Load Factor and Resizing

**Load factor** = Number of entries / Table size

```php
Load factor = 7 entries / 10 slots = 0.7
```

- **Low load factor** (< 0.5): Wasted space
- **High load factor** (> 0.7): More collisions

**Best practice**: Resize (usually double) when load factor > 0.7

## Hash Table Performance

| Operation | Average Case | Worst Case |
|-----------|-------------|------------|
| Search | O(1) | O(n) |
| Insert | O(1) | O(n) |
| Delete | O(1) | O(n) |
| Space | O(n) | O(n) |

**Worst case** occurs when all keys collide (rare with good hash function)

## PHP's Associative Arrays

PHP arrays are **hash tables** internally:

```php
<?php

$ages = [
    'alice' => 30,
    'bob' => 25,
    'charlie' => 35
];

// O(1) average lookups
echo $ages['alice']; // 30

// O(1) average insertion
$ages['diana'] = 28;

// O(1) average deletion
unset($ages['bob']);
```

### PHP Array Characteristics

- Ordered hash table (maintains insertion order)
- Automatic resizing
- Mixed keys (integers and strings)
- More memory overhead than true arrays

## Common Hash Table Applications

### 1. Caching

```php
<?php

class Cache {
    private array $cache = [];
    private int $maxSize;

    public function __construct(int $maxSize = 100) {
        $this->maxSize = $maxSize;
    }

    public function get(string $key): mixed {
        return $this->cache[$key] ?? null;
    }

    public function set(string $key, mixed $value): void {
        if (count($this->cache) >= $this->maxSize) {
            // Simple eviction: remove first item
            array_shift($this->cache);
        }
        $this->cache[$key] = $value;
    }

    public function has(string $key): bool {
        return isset($this->cache[$key]);
    }
}
```

### 2. Count Frequencies

```php
<?php

function countFrequencies(array $items): array {
    $frequencies = [];

    foreach ($items as $item) {
        if (!isset($frequencies[$item])) {
            $frequencies[$item] = 0;
        }
        $frequencies[$item]++;
    }

    return $frequencies;
}

$words = ['apple', 'banana', 'apple', 'orange', 'banana', 'apple'];
$freq = countFrequencies($words);
// ['apple' => 3, 'banana' => 2, 'orange' => 1]
```

### 3. Two Sum Problem

```php
<?php

function twoSum(array $nums, int $target): ?array {
    $seen = [];

    foreach ($nums as $i => $num) {
        $complement = $target - $num;

        if (isset($seen[$complement])) {
            return [$seen[$complement], $i];
        }

        $seen[$num] = $i;
    }

    return null;
}

$nums = [2, 7, 11, 15];
$result = twoSum($nums, 9); // [0, 1] (2 + 7 = 9)
```

### 4. First Non-Repeating Character

```php
<?php

function firstNonRepeating(string $str): ?string {
    $counts = [];

    // Count frequencies
    for ($i = 0; $i < strlen($str); $i++) {
        $char = $str[$i];
        $counts[$char] = ($counts[$char] ?? 0) + 1;
    }

    // Find first with count 1
    for ($i = 0; $i < strlen($str); $i++) {
        if ($counts[$str[$i]] === 1) {
            return $str[$i];
        }
    }

    return null;
}

echo firstNonRepeating("leetcode"); // l
echo firstNonRepeating("aabbcc");   // null
```

## Hash Table vs. Other Data Structures

| Feature | Hash Table | Array | BST |
|---------|-----------|-------|-----|
| Lookup by key | O(1) avg | O(n) | O(log n) |
| Insertion | O(1) avg | O(n) | O(log n) |
| Deletion | O(1) avg | O(n) | O(log n) |
| Ordered traversal | No | Yes | Yes |
| Range queries | No | No | Yes |
| Memory overhead | High | Low | Medium |

## When to Use Hash Tables

**Use hash tables when**:
- Fast lookups by key are critical
- Order doesn't matter
- You need to count/track unique items
- Implementing caches, sets, or dictionaries

**Avoid hash tables when**:
- You need sorted data
- Memory is extremely limited
- Range queries are common
- Keys don't have good hash functions

## Key Takeaways

- Hash tables provide **O(1) average-case** operations
- **Hash functions** convert keys to array indices
- **Collisions** are resolved with chaining or open addressing
- **Load factor** should be kept under 0.7
- PHP arrays are hash tables with extra features
- Perfect for lookups, counting, and caching

## Exercises

1. **Design a HashSet**: Implement a set (no duplicates) using a hash table.

2. **Group Anagrams**: Group words that are anagrams using a hash table.

3. **LRU Cache**: Implement an LRU cache using hash table + doubly linked list.

4. **Longest Substring Without Repeating Characters**: Use a hash table to track character positions.

5. **Design HashMap with Chaining**: Implement a complete hash map from scratch.

## What's Next?

We've now covered fundamental data structures. In Chapter 07, we'll explore **Sorting Algorithms**—the techniques for organizing data efficiently.

---

**Further Reading**:
- [Hash Table (Wikipedia)](https://en.wikipedia.org/wiki/Hash_table)
- [Hash Functions Explained](https://en.wikipedia.org/wiki/Hash_function)
- [PHP Array Internals](https://www.npopov.com/2014/12/22/PHPs-new-hashtable-implementation.html)
