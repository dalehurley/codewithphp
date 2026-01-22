---
title: "27: Caching & Memoization Strategies"
description: "Learn practical caching strategies for PHP applications including in-memory caching, Redis integration, query result caching, and computed property memoization"
sidebar:
  label: "27: Caching & Memoization Strategies"
  order: 27
  badge:
    text: Intermediate
    variant: caution
---
![Caching & Memoization Strategies](/images/php-algorithms/chapter-27-caching-hero-full.webp)


# 27: Caching & Memoization Strategies <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Caching is one of the most effective performance optimizations available to developers. By storing computed results and frequently accessed data in fast storage, you can transform slow applications into blazing-fast experiences. This chapter explores practical caching techniques for PHP applications, from simple in-memory caches to distributed caching systems like Redis and Memcached.

You'll learn when to cache, what to cache, and how to avoid common pitfalls. We'll cover everything from basic array-based caches to production-ready multi-level caching architectures. By the end, you'll understand how caching can improve performance by 25-1000x in real-world scenarios.

This chapter builds on your understanding of dynamic programming memoization patterns and hash table lookups, showing you how to apply these concepts to real PHP applications.

## What You'll Learn

Unlock massive performance gains with one of the most powerful optimization techniques available. Caching can transform slow applications into blazing-fast experiences, and you're about to learn exactly how to implement it effectively in PHP.

- Build and implement various caching strategies from simple arrays to distributed systems
- Master Redis and Memcached integration for production environments
- Apply memoization patterns to eliminate redundant computations automatically
- Implement smart cache invalidation and expiration policies
- Design multi-level caching architectures for maximum performance

## Prerequisites

Before starting this chapter, you should have:

- ✓ Understanding of memoization concepts from [Dynamic Programming Fundamentals](/series/php-algorithms/chapters/25-dynamic-programming-fundamentals)
- ✓ Knowledge of hash tables and hash functions ([Chapter 13](/series/php-algorithms/chapters/13-hash-tables-hash-functions))
- ✓ Comfortable manipulating PHP arrays and data structures
- ✓ Basic understanding of database queries and their performance costs

**Estimated Time**: ~50 minutes

## What You'll Build

By the end of this chapter, you will have created:

- Simple array-based cache with hit/miss statistics
- LRU (Least Recently Used) cache implementation
- TTL (Time To Live) cache with automatic expiration
- PSR-16 compliant cache interface implementation
- Database query result caching system
- Redis cache integration for distributed caching
- Multi-level caching architecture (L1/L2/L3)
- Cache-aside pattern implementation (lazy loading)
- Read-through cache pattern with automatic data loading
- Cache warming strategies for deployments
- Write patterns (write-through, write-behind, write-around)
- Cache compression for memory optimization
- Advanced serialization strategies (JSON, MessagePack, Igbinary)
- Cache key design patterns and naming conventions
- Cache versioning for schema migrations
- Distributed cache consistency models (strong, eventual, read-repair)
- Cache coherency protocols (write-invalidate, write-update)
- Cache invalidation strategies (tag-based, time-based, event-based)
- Cache stampede prevention mechanisms
- Production monitoring and debugging tools with advanced metrics

## Objectives

- Understand when and why caching is the right optimization strategy
- Implement various caching strategies from simple arrays to distributed systems
- Master Redis and Memcached integration for production environments
- Apply memoization patterns to eliminate redundant computations
- Design multi-level caching architectures for maximum performance
- Implement smart cache invalidation and expiration policies
- Prevent cache stampedes and handle high-traffic scenarios
- Monitor and optimize cache performance in production

## In-Memory Caching

### Simple Array-Based Cache

The simplest caching implementation uses a PHP array to store key-value pairs. This is perfect for single-request caching where data doesn't need to persist across requests.

**Use Cases:**
- Memoizing function results within a single request
- Storing computed values that are expensive to recalculate
- Temporary data that doesn't need to survive request completion

```php
# filename: simple-cache.php
<?php

declare(strict_types=1);

class SimpleCache
{
    private array $cache = [];
    private int $hits = 0;
    private int $misses = 0;

    public function get(string $key): mixed
    {
        if (isset($this->cache[$key])) {
            $this->hits++;
            return $this->cache[$key];
        }

        $this->misses++;
        return null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    public function delete(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->hits = 0;
        $this->misses = 0;
    }

    public function getStats(): array
    {
        $total = $this->hits + $this->misses;
        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'hit_rate' => $total > 0 ? $this->hits / $total : 0,
            'size' => count($this->cache)
        ];
    }
}

// Usage
$cache = new SimpleCache();

function fibonacci(int $n, SimpleCache $cache): int
{
    $key = "fib_$n";

    if ($cache->has($key)) {
        return $cache->get($key);
    }

    if ($n <= 1) {
        $result = $n;
    } else {
        $result = fibonacci($n - 1, $cache) + fibonacci($n - 2, $cache);
    }

    $cache->set($key, $result);
    return $result;
}

echo fibonacci(30, $cache) . "\n";
print_r($cache->getStats());
// hits: 27, misses: 31, hit_rate: ~0.466
```

### LRU Cache (Least Recently Used)

LRU cache automatically evicts the least recently accessed item when the cache reaches capacity. This ensures frequently used items stay in cache while rarely used items are removed.

**Use Cases:**
- Limited memory scenarios where you need automatic eviction
- Caching with predictable access patterns (recent items are likely to be accessed again)
- Implementing browser-like caching behavior

**Time Complexity:** O(1) for get and set operations (with efficient implementation)

```php
# filename: lru-cache.php
<?php

declare(strict_types=1);

class LRUCache
{
    private array $cache = [];
    private array $keys = [];  // Track access order
    private int $capacity;

    public function __construct(int $capacity)
    {
        $this->capacity = $capacity;
    }

    public function get(string $key): mixed
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        // Move to end (most recently used)
        $this->updateAccessOrder($key);
        return $this->cache[$key];
    }

    public function set(string $key, mixed $value): void
    {
        if (isset($this->cache[$key])) {
            // Update existing
            $this->cache[$key] = $value;
            $this->updateAccessOrder($key);
        } else {
            // Add new
            if (count($this->cache) >= $this->capacity) {
                // Evict least recently used
                $lruKey = array_shift($this->keys);
                unset($this->cache[$lruKey]);
            }

            $this->cache[$key] = $value;
            $this->keys[] = $key;
        }
    }

    private function updateAccessOrder(string $key): void
    {
        // Remove from current position (more efficient than array_filter)
        $index = array_search($key, $this->keys, true);
        if ($index !== false) {
            unset($this->keys[$index]);
        }
        // Add to end (most recently used)
        $this->keys[] = $key;
    }

    public function getSize(): int
    {
        return count($this->cache);
    }

    public function getAccessOrder(): array
    {
        // Re-index array to remove gaps from unset()
        return array_values($this->keys);
    }
}

// Usage
$lru = new LRUCache(3);

$lru->set('a', 1);
$lru->set('b', 2);
$lru->set('c', 3);
echo "Size: " . $lru->getSize() . "\n";  // 3

$lru->get('a');  // Access 'a'
$lru->set('d', 4);  // 'b' gets evicted (least recently used)

echo "Access order: " . implode(', ', $lru->getAccessOrder()) . "\n";  // c, a, d
```

**Note:** This implementation uses O(n) operations for `updateAccessOrder()`. For true O(1) operations, you'd need a doubly-linked list combined with a hash map. However, for most practical purposes, this array-based approach is simpler and performs well for typical cache sizes.

### TTL Cache (Time To Live)

TTL cache automatically expires entries after a specified time period. This is ideal for data that becomes stale over time but doesn't need immediate invalidation.

**Use Cases:**
- Session data with expiration times
- API response caching with known refresh intervals
- Configuration data that updates periodically
- Rate limiting counters

**Note:** The `cleanExpired()` method is called on every `get()` operation. For high-traffic scenarios, consider periodic cleanup instead.

```php
# filename: ttl-cache.php
<?php

declare(strict_types=1);

class TTLCache
{
    private array $cache = [];
    private array $expiry = [];
    private int $defaultTTL;

    public function __construct(int $defaultTTL = 3600)
    {
        $this->defaultTTL = $defaultTTL;
    }

    public function get(string $key): mixed
    {
        $this->cleanExpired();

        if (!isset($this->cache[$key])) {
            return null;
        }

        if (time() > $this->expiry[$key]) {
            $this->delete($key);
            return null;
        }

        return $this->cache[$key];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $this->cache[$key] = $value;
        $this->expiry[$key] = time() + $ttl;
    }

    public function delete(string $key): void
    {
        unset($this->cache[$key], $this->expiry[$key]);
    }

    public function cleanExpired(): void
    {
        $now = time();
        foreach ($this->expiry as $key => $expiryTime) {
            if ($now > $expiryTime) {
                $this->delete($key);
            }
        }
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->expiry = [];
    }
}

// Usage
$cache = new TTLCache(10);  // 10 second default TTL

$cache->set('session_123', ['user_id' => 1, 'name' => 'John'], 5);
$cache->set('config', ['theme' => 'dark']);

echo "Immediately: " . ($cache->get('session_123') ? 'Found' : 'Not found') . "\n";  // Found

sleep(6);

echo "After 6 seconds: " . ($cache->get('session_123') ? 'Found' : 'Not found') . "\n";  // Not found
echo "Config still valid: " . ($cache->get('config') ? 'Found' : 'Not found') . "\n";  // Found
```

## PSR-16 Simple Cache Implementation

PSR-16 defines a standard cache interface that allows you to swap cache implementations without changing your application code. This promotes interoperability between different caching libraries and backends.

**Benefits:**
- Standard interface means code works with any PSR-16 compliant cache
- Easy to switch between ArrayCache, RedisCache, MemcachedCache, etc.
- Consistent API across different caching solutions
- Better testability (can use ArrayCache in tests, RedisCache in production)

Following PHP-FIG standards for interoperability.

```php
# filename: psr16-cache.php
<?php

declare(strict_types=1);

interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function getMultiple(iterable $keys, mixed $default = null): iterable;
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;
    public function deleteMultiple(iterable $keys): bool;
    public function has(string $key): bool;
}

class ArrayCache implements CacheInterface
{
    private array $cache = [];
    private array $expiry = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->cache[$key];
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $this->cache[$key] = $value;

        if ($ttl !== null) {
            $seconds = $ttl instanceof \DateInterval
                ? (new \DateTime())->add($ttl)->getTimestamp() - time()
                : $ttl;

            $this->expiry[$key] = time() + $seconds;
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expiry[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->cache = [];
        $this->expiry = [];
        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        if (isset($this->expiry[$key]) && time() > $this->expiry[$key]) {
            $this->delete($key);
            return false;
        }

        return true;
    }
}
```

## Database Query Caching

Database queries are one of the most common bottlenecks in web applications. Query result caching can dramatically improve performance by storing query results and avoiding repeated database hits.

**When to Cache Queries:**
- Read-heavy workloads with infrequent data changes
- Expensive queries (joins, aggregations, complex WHERE clauses)
- Frequently accessed data (user profiles, configuration, reference data)
- Queries that return the same results for multiple users

**When NOT to Cache:**
- Frequently updated data (real-time inventory, user activity)
- User-specific queries with unique parameters
- Queries that must always return fresh data (financial transactions)

### Query Result Cache

```php
# filename: query-cache.php
<?php

declare(strict_types=1);

class QueryCache
{
    private CacheInterface $cache;
    private \PDO $db;
    private int $defaultTTL = 300;

    public function __construct(\PDO $db, CacheInterface $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function query(string $sql, array $params = [], ?int $ttl = null): array
    {
        $cacheKey = $this->generateCacheKey($sql, $params);

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Execute query
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Store in cache
        $this->cache->set($cacheKey, $result, $ttl ?? $this->defaultTTL);

        return $result;
    }

    public function invalidate(string $sql, array $params = []): void
    {
        $cacheKey = $this->generateCacheKey($sql, $params);
        $this->cache->delete($cacheKey);
    }

    public function invalidateTable(string $tableName): void
    {
        // In production, you'd track keys by table
        // For now, clear all cache
        $this->cache->clear();
    }

    private function generateCacheKey(string $sql, array $params): string
    {
        // Normalize SQL (remove extra whitespace) for consistent keys
        $normalizedSql = preg_replace('/\s+/', ' ', trim($sql));
        return 'query_' . md5($normalizedSql . serialize($params));
    }
}

// Usage
$cache = new ArrayCache();
$pdo = new \PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$queryCache = new QueryCache($pdo, $cache);

// First call: hits database
$users = $queryCache->query('SELECT * FROM users WHERE active = ?', [1], 60);

// Second call: from cache
$users = $queryCache->query('SELECT * FROM users WHERE active = ?', [1], 60);

// After update, invalidate
$queryCache->invalidateTable('users');
```

### ORM Query Caching (Laravel-style)

```php
# filename: orm-cache.php
<?php

declare(strict_types=1);

class Model
{
    protected static CacheInterface $cache;
    protected static \PDO $db;
    protected static string $table;

    public static function find(int $id): ?array
    {
        $cacheKey = static::$table . '_' . $id;

        $cached = static::$cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $stmt = static::$db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            static::$cache->set($cacheKey, $result, 300);
        }

        return $result ?: null;
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = static::$cache->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $result = $callback();
        static::$cache->set($key, $result, $ttl);

        return $result;
    }
}

class User extends Model
{
    protected static string $table = 'users';

    public static function getActiveUsers(): array
    {
        return static::remember('active_users', 300, function() {
            $stmt = static::$db->query("SELECT * FROM users WHERE active = 1");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        });
    }
}
```

## Computed Property Memoization

Computed property memoization caches expensive calculations within object instances. This is particularly useful when a property is accessed multiple times but its calculation depends on other properties that don't change frequently.

**Use Cases:**
- Expensive calculations based on object properties
- Derived values that are accessed multiple times
- Properties that depend on multiple other properties
- Lazy evaluation of expensive computations

### Class Property Caching

```php
# filename: memoizable-trait.php
<?php

declare(strict_types=1);

trait Memoizable
{
    private array $memoized = [];

    protected function memoize(string $property, callable $calculator): mixed
    {
        if (!isset($this->memoized[$property])) {
            $this->memoized[$property] = $calculator();
        }

        return $this->memoized[$property];
    }

    protected function clearMemoized(?string $property = null): void
    {
        if ($property === null) {
            $this->memoized = [];
        } else {
            unset($this->memoized[$property]);
        }
    }
}

class Product
{
    use Memoizable;

    public function __construct(
        private float $basePrice,
        private float $taxRate,
        private float $discountPercent = 0
    ) {}

    public function getFinalPrice(): float
    {
        return $this->memoize('finalPrice', function() {
            // Expensive calculation
            $discounted = $this->basePrice * (1 - $this->discountPercent / 100);
            return $discounted * (1 + $this->taxRate);
        });
    }

    public function setDiscount(float $percent): void
    {
        $this->discountPercent = $percent;
        $this->clearMemoized('finalPrice');  // Invalidate cache
    }
}

// Usage
$product = new Product(100, 0.08, 10);
echo $product->getFinalPrice() . "\n";  // Calculated: 97.2
echo $product->getFinalPrice() . "\n";  // From cache: 97.2

$product->setDiscount(20);
echo $product->getFinalPrice() . "\n";  // Recalculated: 86.4
```

### Attribute-Based Memoization (PHP 8+)

```php
# filename: attribute-memoization.php
<?php

declare(strict_types=1);

#[\Attribute(\Attribute::TARGET_METHOD)]
class Memoize
{
    public function __construct(public ?int $ttl = null) {}
}

class MemoizationProxy
{
    private array $cache = [];

    public function __call(string $method, array $args)
    {
        $reflection = new \ReflectionMethod($this, $method);
        $attributes = $reflection->getAttributes(Memoize::class);

        if (empty($attributes)) {
            return $this->$method(...$args);
        }

        $key = $method . '_' . md5(serialize($args));

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $result = $this->$method(...$args);
        $this->cache[$key] = $result;

        return $result;
    }
}

class Calculator extends MemoizationProxy
{
    #[Memoize(ttl: 60)]
    public function expensiveCalculation(int $n): int
    {
        // Simulate expensive operation
        sleep(1);
        return $n * $n;
    }
}
```

## APCu Integration

APCu (Alternative PHP Cache User) provides shared memory caching for PHP applications running on a single server. It's faster than Redis for single-server deployments since it uses shared memory instead of network communication.

**Advantages:**
- Fastest shared caching option for single-server deployments
- Shared across all PHP processes on the same server
- Built into PHP (no separate service required)
- Automatic memory management

**When to Use APCu:**
- Single-server deployments
- Need for shared caching across requests
- Configuration data, opcode caching
- Session storage (when not using Redis)

### APCu Cache Implementation

```php
# filename: apcu-cache.php
<?php

declare(strict_types=1);

class APCuCache implements CacheInterface
{
    private string $prefix;

    public function __construct(string $prefix = '')
    {
        if (!extension_loaded('apcu')) {
            throw new \RuntimeException('APCu extension not loaded');
        }
        $this->prefix = $prefix;
    }

    private function prefixKey(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = apcu_fetch($this->prefixKey($key), $success);
        return $success ? $value : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $seconds = null;
        if ($ttl !== null) {
            $seconds = $ttl instanceof \DateInterval
                ? (new \DateTime())->add($ttl)->getTimestamp() - time()
                : $ttl;
        }

        return apcu_store($this->prefixKey($key), $value, $seconds ?? 0);
    }

    public function delete(string $key): bool
    {
        return apcu_delete($this->prefixKey($key));
    }

    public function clear(): bool
    {
        if (empty($this->prefix)) {
            return apcu_clear_cache();
        }

        // Clear only keys with prefix
        $iterator = new \APCuIterator('/^' . preg_quote($this->prefix, '/') . '/');
        foreach ($iterator as $item) {
            apcu_delete($item['key']);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return apcu_exists($this->prefixKey($key));
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), iterator_to_array($keys));
        $values = apcu_fetch($prefixedKeys);

        $results = [];
        foreach ($keys as $key) {
            $prefixedKey = $this->prefixKey($key);
            $results[$key] = $values[$prefixedKey] ?? $default;
        }

        return $results;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $seconds = null;
        if ($ttl !== null) {
            $seconds = $ttl instanceof \DateInterval
                ? (new \DateTime())->add($ttl)->getTimestamp() - time()
                : $ttl;
        }

        $prefixedValues = [];
        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefixKey($key)] = $value;
        }

        $result = apcu_store($prefixedValues, null, $seconds ?? 0);
        return $result !== false;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            apcu_delete($this->prefixKey($key));
        }
        return true;
    }

    // APCu-specific methods
    public function getStats(): array
    {
        $info = apcu_cache_info();
        $sma = apcu_sma_info();

        return [
            'hits' => $info['num_hits'] ?? 0,
            'misses' => $info['num_misses'] ?? 0,
            'hit_rate' => ($info['num_hits'] ?? 0) / (($info['num_hits'] ?? 0) + ($info['num_misses'] ?? 0) ?: 1),
            'memory_used' => $sma['seg_size'] - $sma['avail_mem'],
            'memory_available' => $sma['avail_mem'],
            'num_entries' => $info['num_entries'] ?? 0
        ];
    }
}

// Usage
$cache = new APCuCache('myapp:');

$cache->set('config:theme', 'dark', 3600);
$theme = $cache->get('config:theme');

// Batch operations
$cache->setMultiple([
    'user:1' => ['name' => 'John'],
    'user:2' => ['name' => 'Jane']
], 300);

$users = $cache->getMultiple(['user:1', 'user:2']);

print_r($cache->getStats());
```

## Memcached Integration

Memcached is a distributed memory caching system that's simpler than Redis but highly performant for simple key-value caching. It's ideal when you need distributed caching without Redis's additional features.

**Advantages:**
- Simple key-value interface
- Distributed across multiple servers
- High performance for simple use cases
- Mature and stable

**When to Use Memcached:**
- Multi-server deployments requiring simple caching
- Don't need Redis's advanced features (pub/sub, persistence, complex data types)
- High-performance simple key-value caching
- When you want a simpler alternative to Redis

### Memcached Cache Implementation

```php
# filename: memcached-cache.php
<?php

declare(strict_types=1);

class MemcachedCache implements CacheInterface
{
    private \Memcached $memcached;
    private string $prefix;

    public function __construct(array $servers = [['127.0.0.1', 11211]], string $prefix = '')
    {
        if (!extension_loaded('memcached')) {
            throw new \RuntimeException('Memcached extension not loaded');
        }

        $this->memcached = new \Memcached();
        $this->memcached->addServers($servers);
        $this->prefix = $prefix;
    }

    private function prefixKey(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->memcached->get($this->prefixKey($key));
        
        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $seconds = 0;
        if ($ttl !== null) {
            $seconds = $ttl instanceof \DateInterval
                ? (new \DateTime())->add($ttl)->getTimestamp() - time()
                : $ttl;
        }

        return $this->memcached->set($this->prefixKey($key), $value, $seconds);
    }

    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->prefixKey($key));
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    public function has(string $key): bool
    {
        $this->memcached->get($this->prefixKey($key));
        return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), iterator_to_array($keys));
        $values = $this->memcached->getMulti($prefixedKeys);

        $results = [];
        foreach ($keys as $key) {
            $prefixedKey = $this->prefixKey($key);
            $results[$key] = $values[$prefixedKey] ?? $default;
        }

        return $results;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $seconds = 0;
        if ($ttl !== null) {
            $seconds = $ttl instanceof \DateInterval
                ? (new \DateTime())->add($ttl)->getTimestamp() - time()
                : $ttl;
        }

        $prefixedValues = [];
        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefixKey($key)] = $value;
        }

        return $this->memcached->setMulti($prefixedValues, $seconds);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), iterator_to_array($keys));
        $this->memcached->deleteMulti($prefixedKeys);
        return true;
    }

    // Memcached-specific methods
    public function getStats(): array
    {
        $stats = $this->memcached->getStats();
        $serverStats = reset($stats);

        return [
            'uptime' => $serverStats['uptime'] ?? 0,
            'bytes' => $serverStats['bytes'] ?? 0,
            'curr_items' => $serverStats['curr_items'] ?? 0,
            'total_items' => $serverStats['total_items'] ?? 0,
            'get_hits' => $serverStats['get_hits'] ?? 0,
            'get_misses' => $serverStats['get_misses'] ?? 0,
            'hit_rate' => ($serverStats['get_hits'] ?? 0) / (($serverStats['get_hits'] ?? 0) + ($serverStats['get_misses'] ?? 0) ?: 1)
        ];
    }
}

// Usage
$cache = new MemcachedCache([
    ['127.0.0.1', 11211],
    ['127.0.0.1', 11212]  // Multiple servers for distribution
], 'myapp:');

$cache->set('session:123', ['user_id' => 1], 3600);
$session = $cache->get('session:123');

print_r($cache->getStats());
```

## Redis Integration

Redis is an in-memory data structure store that can serve as a distributed cache. It's ideal for multi-server deployments where you need shared caching across multiple application servers.

**Advantages:**
- Distributed caching across multiple servers
- Persistent storage (optional)
- Rich data types (strings, hashes, lists, sets, sorted sets)
- Built-in expiration and atomic operations
- Pub/sub capabilities for cache invalidation

**When to Use Redis:**
- Multi-server deployments
- Need for distributed caching
- Complex data structures (beyond simple key-value)
- High-performance requirements with persistence

### Redis Cache Implementation

```php
# filename: redis-cache.php
<?php

declare(strict_types=1);

class RedisCache implements CacheInterface
{
    private \Redis $redis;
    private string $prefix;

    public function __construct(string $host = '127.0.0.1', int $port = 6379, string $prefix = '')
    {
        $this->redis = new \Redis();
        if (!$this->redis->connect($host, $port)) {
            throw new \RuntimeException("Failed to connect to Redis at $host:$port");
        }
        $this->prefix = $prefix;
    }

    private function prefixKey(string $key): string
    {
        return $this->prefix . $key;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->prefixKey($key));

        if ($value === false) {
            return $default;
        }

        return unserialize($value);
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $serialized = serialize($value);

        if ($ttl === null) {
            return $this->redis->set($this->prefixKey($key), $serialized);
        }

        $seconds = $ttl instanceof \DateInterval
            ? (new \DateTime())->add($ttl)->getTimestamp() - time()
            : $ttl;

        return $this->redis->setex($this->prefixKey($key), $seconds, $serialized);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->prefixKey($key)) > 0;
    }

    public function clear(): bool
    {
        if (empty($this->prefix)) {
            return $this->redis->flushDB();
        }

        // Clear only keys with prefix
        // Note: keys() can be slow on large datasets - use SCAN in production
        $keys = $this->redis->keys($this->prefix . '*');
        if (!empty($keys)) {
            // Delete in batches to avoid memory issues
            $chunks = array_chunk($keys, 1000);
            foreach ($chunks as $chunk) {
                $this->redis->del(...$chunk);
            }
        }

        return true;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->prefixKey($key)) > 0;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), iterator_to_array($keys));
        $values = $this->redis->mGet($prefixedKeys);

        $results = [];
        $i = 0;
        foreach ($keys as $key) {
            $results[$key] = $values[$i] !== false ? unserialize($values[$i]) : $default;
            $i++;
        }

        return $results;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $prefixedKeys = array_map(fn($k) => $this->prefixKey($k), iterator_to_array($keys));
        $this->redis->del(...$prefixedKeys);
        return true;
    }

    // Redis-specific methods
    public function increment(string $key, int $value = 1): int
    {
        return $this->redis->incrBy($this->prefixKey($key), $value);
    }

    public function decrement(string $key, int $value = 1): int
    {
        return $this->redis->decrBy($this->prefixKey($key), $value);
    }

    public function getRedis(): \Redis
    {
        return $this->redis;
    }
}

// Usage
$cache = new RedisCache('127.0.0.1', 6379, 'myapp:');

$cache->set('user:1', ['name' => 'John', 'email' => 'john@example.com'], 3600);
$user = $cache->get('user:1');

// Rate limiting with Redis
$cache->set('api_calls:user_123', 0, 3600);
$calls = $cache->increment('api_calls:user_123');

if ($calls > 100) {
    die('Rate limit exceeded');
}
```

## Cache Patterns

### Cache-Aside Pattern (Lazy Loading)

Cache-aside is the most common caching pattern. The application is responsible for loading data into the cache. On a cache miss, the application loads data from the data source, stores it in cache, and returns it.

**How It Works:**
1. Check cache for data
2. If found (cache hit), return cached data
3. If not found (cache miss), load from data source
4. Store in cache for future requests
5. Return data

**Use Cases:**
- Most common pattern for application-level caching
- When you want explicit control over cache population
- When cache and data source can be inconsistent temporarily

```php
# filename: cache-aside.php
<?php

declare(strict_types=1);

class CacheAsideRepository
{
    private CacheInterface $cache;
    private \PDO $db;

    public function __construct(CacheInterface $cache, \PDO $db)
    {
        $this->cache = $cache;
        $this->db = $db;
    }

    public function findById(int $id): ?array
    {
        $cacheKey = "user:$id";

        // Step 1: Check cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;  // Cache hit
        }

        // Step 2: Cache miss - load from database
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            // Step 3: Store in cache for future requests
            $this->cache->set($cacheKey, $user, 3600);
            return $user;
        }

        return null;
    }

    public function update(int $id, array $data): bool
    {
        // Update database
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $result = $stmt->execute([$data['name'], $data['email'], $id]);

        if ($result) {
            // Invalidate cache (or update it)
            $this->cache->delete("user:$id");
            
            // Optionally: Update cache with new data
            // $this->cache->set("user:$id", $data, 3600);
        }

        return $result;
    }
}

// Usage
$cache = new ArrayCache();
$pdo = new \PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$repo = new CacheAsideRepository($cache, $pdo);

// First call: Cache miss, loads from DB
$user = $repo->findById(123);

// Second call: Cache hit, returns from cache
$user = $repo->findById(123);

// Update invalidates cache
$repo->update(123, ['name' => 'John Updated', 'email' => 'john@example.com']);
```

**Advantages:**
- Simple to implement
- Application has full control
- Cache failures don't break application
- Easy to debug

**Disadvantages:**
- Two round trips on cache miss (check cache, then load from source)
- Cache can become stale if not properly invalidated
- Application code must handle cache logic

### Read-Through Cache Pattern

Read-through cache automatically loads data from the data source when there's a cache miss. The cache itself handles the loading logic, making it transparent to the application.

**How It Works:**
1. Application requests data from cache
2. Cache checks if data exists
3. If cache miss, cache automatically loads from data source
4. Cache stores and returns data
5. Application receives data without knowing if it was cached

**Use Cases:**
- When you want transparent caching
- When cache loading logic is complex
- When multiple applications share the same cache

```php
# filename: read-through-cache.php
<?php

declare(strict_types=1);

class ReadThroughCache
{
    private CacheInterface $cache;
    private callable $loader;

    public function __construct(CacheInterface $cache, callable $loader)
    {
        $this->cache = $cache;
        $this->loader = $loader;
    }

    public function get(string $key, ?int $ttl = null): mixed
    {
        // Check cache first
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;  // Cache hit
        }

        // Cache miss - automatically load from data source
        $value = ($this->loader)($key);

        if ($value !== null) {
            // Store in cache for future requests
            $this->cache->set($key, $value, $ttl ?? 3600);
        }

        return $value;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl ?? 3600);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }
}

// Usage with database loader
$pdo = new \PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$cache = new ArrayCache();

$loader = function(string $key) use ($pdo) {
    // Extract ID from key (e.g., "user:123" -> 123)
    [, $id] = explode(':', $key);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
};

$readThrough = new ReadThroughCache($cache, $loader);

// Application just requests data - cache handles loading automatically
$user = $readThrough->get('user:123');  // Loads from DB if not cached
$user = $readThrough->get('user:123');  // Returns from cache
```

**Advantages:**
- Transparent to application code
- Centralized cache loading logic
- Consistent behavior across application
- Easier to maintain

**Disadvantages:**
- Less flexible than cache-aside
- Cache must know how to load data
- Harder to handle complex loading scenarios

## Cache Warming Strategies

Cache warming preloads frequently accessed data into cache before it's needed, reducing cache misses and improving response times. This is especially important after deployments or server restarts.

**When to Warm Cache:**
- After application deployments
- On server startup
- Before peak traffic periods
- For critical data that must be fast

### Cache Warmer Implementation

```php
# filename: cache-warmer.php
<?php

declare(strict_types=1);

class CacheWarmer
{
    private CacheInterface $cache;
    private array $stats = [
        'warmed' => 0,
        'failed' => 0,
        'skipped' => 0,
        'time_ms' => 0
    ];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Warm cache synchronously
     */
    public function warm(array $keys, callable $loader, ?int $ttl = null): array
    {
        $start = microtime(true);
        $this->stats = ['warmed' => 0, 'failed' => 0, 'skipped' => 0, 'time_ms' => 0];

        foreach ($keys as $key) {
            try {
                // Skip if already cached
                if ($this->cache->has($key)) {
                    $this->stats['skipped']++;
                    continue;
                }

                $value = $loader($key);
                $this->cache->set($key, $value, $ttl);
                $this->stats['warmed']++;
            } catch (\Throwable $e) {
                $this->stats['failed']++;
                error_log("Cache warming failed for key '$key': " . $e->getMessage());
            }
        }

        $this->stats['time_ms'] = (microtime(true) - $start) * 1000;
        return $this->stats;
    }

    /**
     * Warm cache in parallel using multiple processes
     */
    public function warmParallel(array $keys, callable $loader, ?int $ttl = null, int $workers = 4): array
    {
        $start = microtime(true);
        $chunks = array_chunk($keys, (int)ceil(count($keys) / $workers));

        $processes = [];
        foreach ($chunks as $chunk) {
            $pid = pcntl_fork();
            
            if ($pid == -1) {
                // Fork failed, fall back to sequential
                return $this->warm($keys, $loader, $ttl);
            } elseif ($pid) {
                // Parent process
                $processes[] = $pid;
            } else {
                // Child process - warm this chunk
                foreach ($chunk as $key) {
                    try {
                        if (!$this->cache->has($key)) {
                            $value = $loader($key);
                            $this->cache->set($key, $value, $ttl);
                        }
                    } catch (\Throwable $e) {
                        error_log("Cache warming failed: " . $e->getMessage());
                    }
                }
                exit(0);
            }
        }

        // Wait for all children
        foreach ($processes as $pid) {
            pcntl_waitpid($pid, $status);
        }

        $this->stats['time_ms'] = (microtime(true) - $start) * 1000;
        return $this->stats;
    }

    /**
     * Warm cache with rate limiting to avoid overwhelming backend
     */
    public function warmWithRateLimit(
        array $keys,
        callable $loader,
        ?int $ttl = null,
        int $rateLimit = 10  // keys per second
    ): array {
        $start = microtime(true);
        $this->stats = ['warmed' => 0, 'failed' => 0, 'skipped' => 0, 'time_ms' => 0];
        
        $delay = 1.0 / $rateLimit;  // seconds between operations

        foreach ($keys as $key) {
            try {
                if ($this->cache->has($key)) {
                    $this->stats['skipped']++;
                    continue;
                }

                $value = $loader($key);
                $this->cache->set($key, $value, $ttl);
                $this->stats['warmed']++;

                // Rate limiting
                usleep((int)($delay * 1000000));
            } catch (\Throwable $e) {
                $this->stats['failed']++;
                error_log("Cache warming failed for key '$key': " . $e->getMessage());
            }
        }

        $this->stats['time_ms'] = (microtime(true) - $start) * 1000;
        return $this->stats;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}

// Usage Example
$cache = new RedisCache('127.0.0.1', 6379);
$warmer = new CacheWarmer($cache);

// Define what to warm
$criticalKeys = [
    'config:app',
    'config:features',
    'popular:products',
    'homepage:content'
];

// Loader function
$loader = function(string $key) {
    // Simulate loading from database
    switch ($key) {
        case 'config:app':
            return ['name' => 'MyApp', 'version' => '1.0'];
        case 'config:features':
            return ['feature1' => true, 'feature2' => false];
        default:
            return [];
    }
};

// Warm cache
$stats = $warmer->warm($criticalKeys, $loader, 3600);
echo "Warmed: {$stats['warmed']}, Failed: {$stats['failed']}, Time: {$stats['time_ms']}ms\n";

// Warm with rate limiting (10 keys/second)
$stats = $warmer->warmWithRateLimit($criticalKeys, $loader, 3600, 10);
```

## Write Patterns

Different write patterns optimize for different scenarios. Choose based on your consistency requirements and performance needs.

### Write-Through Cache

Write-through writes to both cache and database simultaneously. This ensures cache and database are always consistent but is slower for writes.

**Use Cases:**
- Data consistency is critical
- Write performance is acceptable
- Need immediate consistency guarantees

```php
# filename: write-through-cache.php
<?php

declare(strict_types=1);

class WriteThroughCache
{
    private CacheInterface $cache;
    private \PDO $db;

    public function __construct(CacheInterface $cache, \PDO $db)
    {
        $this->cache = $cache;
        $this->db = $db;
    }

    public function write(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Write to database first
        $stmt = $this->db->prepare("INSERT INTO cache_data (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $serialized = serialize($value);
        $stmt->execute([$key, $serialized, $serialized]);

        // Then write to cache
        return $this->cache->set($key, $value, $ttl);
    }

    public function read(string $key, mixed $default = null): mixed
    {
        // Try cache first
        $value = $this->cache->get($key);
        if ($value !== null) {
            return $value;
        }

        // Fallback to database
        $stmt = $this->db->prepare("SELECT value FROM cache_data WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $value = unserialize($row['value']);
            // Populate cache
            $this->cache->set($key, $value);
            return $value;
        }

        return $default;
    }
}
```

### Write-Behind Cache (Write-Back)

Write-behind writes to cache immediately and writes to database asynchronously. This provides fast writes but risks data loss if cache fails.

**Use Cases:**
- Write performance is critical
- Can tolerate brief inconsistency
- Have reliable cache persistence

```php
# filename: write-behind-cache.php
<?php

declare(strict_types=1);

class WriteBehindCache
{
    private CacheInterface $cache;
    private \PDO $db;
    private array $writeQueue = [];
    private bool $processing = false;

    public function __construct(CacheInterface $cache, \PDO $db)
    {
        $this->cache = $cache;
        $this->db = $db;
    }

    public function write(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Write to cache immediately
        $this->cache->set($key, $value, $ttl);

        // Queue for database write
        $this->writeQueue[] = ['key' => $key, 'value' => serialize($value)];

        // Process queue asynchronously (in real app, use background job)
        $this->processQueue();

        return true;
    }

    private function processQueue(): void
    {
        if ($this->processing || empty($this->writeQueue)) {
            return;
        }

        $this->processing = true;

        // Process in batches
        $batch = array_splice($this->writeQueue, 0, 100);
        
        $stmt = $this->db->prepare("INSERT INTO cache_data (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        
        foreach ($batch as $item) {
            try {
                $stmt->execute([$item['key'], $item['value'], $item['value']]);
            } catch (\Throwable $e) {
                // Re-queue failed writes
                $this->writeQueue[] = $item;
                error_log("Write-behind failed: " . $e->getMessage());
            }
        }

        $this->processing = false;
    }

    public function read(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function flush(): void
    {
        // Force all queued writes to database
        while (!empty($this->writeQueue)) {
            $this->processQueue();
        }
    }
}
```

### Write-Around Cache

Write-around writes directly to database and only populates cache on read. This avoids cache pollution with write-only data.

**Use Cases:**
- Many writes but few reads
- Want to avoid cache pollution
- Write performance is acceptable

```php
# filename: write-around-cache.php
<?php

declare(strict_types=1);

class WriteAroundCache
{
    private CacheInterface $cache;
    private \PDO $db;

    public function __construct(CacheInterface $cache, \PDO $db)
    {
        $this->cache = $cache;
        $this->db = $db;
    }

    public function write(string $key, mixed $value): bool
    {
        // Write only to database
        $stmt = $this->db->prepare("INSERT INTO cache_data (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $serialized = serialize($value);
        $stmt->execute([$key, $serialized, $serialized]);

        // Invalidate cache if exists
        $this->cache->delete($key);

        return true;
    }

    public function read(string $key, mixed $default = null): mixed
    {
        // Try cache first
        $value = $this->cache->get($key);
        if ($value !== null) {
            return $value;
        }

        // Read from database
        $stmt = $this->db->prepare("SELECT value FROM cache_data WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $value = unserialize($row['value']);
            // Populate cache for future reads
            $this->cache->set($key, $value);
            return $value;
        }

        return $default;
    }
}
```

## Cache Compression

Compressing cached values reduces memory usage and network bandwidth, especially important for large values or when memory is constrained.

**When to Compress:**
- Cached values are large (>1KB)
- Memory is limited
- Network bandwidth is a concern
- Cache contains compressible data (JSON, HTML, text)

```php
# filename: compressed-cache.php
<?php

declare(strict_types=1);

class CompressedCache implements CacheInterface
{
    private CacheInterface $cache;
    private int $compressThreshold;  // Compress values larger than this (bytes)
    private string $compressionPrefix = 'compressed:';

    public function __construct(CacheInterface $cache, int $compressThreshold = 1024)
    {
        $this->cache = $cache;
        $this->compressThreshold = $compressThreshold;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // Check for compressed version first
        $compressedKey = $this->compressionPrefix . $key;
        $compressed = $this->cache->get($compressedKey);
        
        if ($compressed !== null) {
            return $this->decompress($compressed);
        }

        // Check uncompressed version
        $value = $this->cache->get($key);
        return $value !== null ? $value : $default;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $serialized = serialize($value);
        $size = strlen($serialized);

        // Compress if value is large enough
        if ($size > $this->compressThreshold) {
            $compressed = $this->compress($serialized);
            
            // Only use compression if it actually saves space
            if (strlen($compressed) < $size) {
                // Delete uncompressed version if exists
                $this->cache->delete($key);
                return $this->cache->set($this->compressionPrefix . $key, $compressed, $ttl);
            }
        }

        // Use uncompressed version
        $this->cache->delete($this->compressionPrefix . $key);
        return $this->cache->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        $deleted1 = $this->cache->delete($key);
        $deleted2 = $this->cache->delete($this->compressionPrefix . $key);
        return $deleted1 || $deleted2;
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key) || $this->cache->has($this->compressionPrefix . $key);
    }

    private function compress(string $data): string
    {
        $compressed = gzcompress($data, 6);  // Compression level 1-9
        return base64_encode($compressed);  // Encode for safe storage
    }

    private function decompress(string $compressed): mixed
    {
        $decoded = base64_decode($compressed);
        $decompressed = gzuncompress($decoded);
        return unserialize($decompressed);
    }

    public function getStats(): array
    {
        // In production, track compression statistics
        return [
            'compression_threshold' => $this->compressThreshold,
            'compression_enabled' => function_exists('gzcompress')
        ];
    }
}

// Usage
$baseCache = new ArrayCache();
$compressedCache = new CompressedCache($baseCache, 1024);  // Compress values >1KB

// Large JSON data
$largeData = [
    'products' => array_fill(0, 1000, [
        'id' => 1,
        'name' => 'Product Name',
        'description' => str_repeat('Long description text. ', 100)
    ])
];

$compressedCache->set('large:products', $largeData, 3600);
$retrieved = $compressedCache->get('large:products');

// Check memory savings
$uncompressedSize = strlen(serialize($largeData));
$compressedSize = strlen($baseCache->get('compressed:large:products') ?? '');
$savings = $uncompressedSize > 0 ? (1 - $compressedSize / $uncompressedSize) * 100 : 0;

echo "Original: {$uncompressedSize} bytes\n";
echo "Compressed: {$compressedSize} bytes\n";
echo "Savings: " . round($savings, 2) . "%\n";
```

## Advanced Serialization Strategies

Different serialization formats offer different trade-offs in speed, size, and compatibility. Choose based on your specific needs.

### Serialization Format Comparison

```php
# filename: serialization-strategies.php
<?php

declare(strict_types=1);

class SerializationBenchmark
{
    private array $testData;

    public function __construct()
    {
        // Sample data structure
        $this->testData = [
            'user' => [
                'id' => 12345,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'preferences' => [
                    'theme' => 'dark',
                    'notifications' => true,
                    'language' => 'en'
                ],
                'metadata' => array_fill(0, 50, ['key' => 'value', 'timestamp' => time()])
            ]
        ];
    }

    public function benchmark(): array
    {
        $results = [];

        // 1. PHP serialize() - Native, handles all PHP types
        $start = microtime(true);
        $serialized = serialize($this->testData);
        $serializeTime = (microtime(true) - $start) * 1000;
        $start = microtime(true);
        unserialize($serialized);
        $unserializeTime = (microtime(true) - $start) * 1000;

        $results['serialize'] = [
            'size' => strlen($serialized),
            'encode_time_ms' => round($serializeTime, 3),
            'decode_time_ms' => round($unserializeTime, 3),
            'pros' => ['Handles all PHP types', 'Native support', 'Preserves object types'],
            'cons' => ['Larger size', 'PHP-specific', 'Security concerns']
        ];

        // 2. JSON - Human-readable, language-agnostic
        $start = microtime(true);
        $json = json_encode($this->testData, JSON_UNESCAPED_UNICODE);
        $jsonEncodeTime = (microtime(true) - $start) * 1000;
        $start = microtime(true);
        json_decode($json, true);
        $jsonDecodeTime = (microtime(true) - $start) * 1000;

        $results['json'] = [
            'size' => strlen($json),
            'encode_time_ms' => round($jsonEncodeTime, 3),
            'decode_time_ms' => round($jsonDecodeTime, 3),
            'pros' => ['Human-readable', 'Language-agnostic', 'Fast', 'Small size'],
            'cons' => ['No object types', 'No binary data', 'Limited types']
        ];

        // 3. MessagePack - Compact binary format
        if (function_exists('msgpack_pack')) {
            $start = microtime(true);
            $msgpack = msgpack_pack($this->testData);
            $msgpackEncodeTime = (microtime(true) - $start) * 1000;
            $start = microtime(true);
            msgpack_unpack($msgpack);
            $msgpackDecodeTime = (microtime(true) - $start) * 1000;

            $results['msgpack'] = [
                'size' => strlen($msgpack),
                'encode_time_ms' => round($msgpackEncodeTime, 3),
                'decode_time_ms' => round($msgpackDecodeTime, 3),
                'pros' => ['Very compact', 'Fast', 'Cross-language'],
                'cons' => ['Requires extension', 'Binary format', 'Less human-readable']
            ];
        }

        // 4. Igbinary - Faster serialize alternative
        if (function_exists('igbinary_serialize')) {
            $start = microtime(true);
            $igbinary = igbinary_serialize($this->testData);
            $igbinaryEncodeTime = (microtime(true) - $start) * 1000;
            $start = microtime(true);
            igbinary_unserialize($igbinary);
            $igbinaryDecodeTime = (microtime(true) - $start) * 1000;

            $results['igbinary'] = [
                'size' => strlen($igbinary),
                'encode_time_ms' => round($igbinaryEncodeTime, 3),
                'decode_time_ms' => round($igbinaryDecodeTime, 3),
                'pros' => ['Faster than serialize', 'Smaller size', 'PHP-compatible'],
                'cons' => ['Requires extension', 'PHP-specific']
            ];
        }

        return $results;
    }
}

// Usage
$benchmark = new SerializationBenchmark();
$results = $benchmark->benchmark();

foreach ($results as $format => $metrics) {
    echo strtoupper($format) . ":\n";
    echo "  Size: {$metrics['size']} bytes\n";
    echo "  Encode: {$metrics['encode_time_ms']}ms\n";
    echo "  Decode: {$metrics['decode_time_ms']}ms\n";
    echo "  Pros: " . implode(', ', $metrics['pros']) . "\n";
    echo "  Cons: " . implode(', ', $metrics['cons']) . "\n\n";
}

/*
Typical Results:

SERIALIZE:
  Size: ~2500 bytes
  Encode: 0.5ms
  Decode: 0.3ms
  Pros: Handles all PHP types, Native support, Preserves object types
  Cons: Larger size, PHP-specific, Security concerns

JSON:
  Size: ~1800 bytes
  Encode: 0.2ms
  Decode: 0.15ms
  Pros: Human-readable, Language-agnostic, Fast, Small size
  Cons: No object types, No binary data, Limited types

MSGPACK:
  Size: ~1200 bytes
  Encode: 0.3ms
  Decode: 0.2ms
  Pros: Very compact, Fast, Cross-language
  Cons: Requires extension, Binary format, Less human-readable

IGBINARY:
  Size: ~2000 bytes
  Encode: 0.3ms
  Decode: 0.2ms
  Pros: Faster than serialize, Smaller size, PHP-compatible
  Cons: Requires extension, PHP-specific
*/
```

### Smart Serialization Cache

```php
# filename: smart-serialization-cache.php
<?php

declare(strict_types=1);

class SmartSerializationCache implements CacheInterface
{
    private CacheInterface $cache;
    private string $format;

    public function __construct(CacheInterface $cache, string $format = 'auto')
    {
        $this->cache = $cache;
        $this->format = $format;
    }

    private function detectBestFormat(mixed $value): string
    {
        if ($this->format !== 'auto') {
            return $this->format;
        }

        // Simple heuristics
        if (is_array($value) || is_object($value)) {
            // Check if contains only JSON-serializable types
            if ($this->isJsonCompatible($value)) {
                return 'json';  // Fastest and smallest for simple data
            }
            
            // Check if MessagePack available and data is suitable
            if (function_exists('msgpack_pack')) {
                return 'msgpack';  // Most compact
            }
        }

        // Default to serialize for complex PHP types
        return 'serialize';
    }

    private function isJsonCompatible(mixed $value): bool
    {
        if (is_scalar($value) || $value === null) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (!$this->isJsonCompatible($item)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    private function serialize(mixed $value, string $format): string
    {
        return match($format) {
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE),
            'msgpack' => function_exists('msgpack_pack') ? msgpack_pack($value) : serialize($value),
            'igbinary' => function_exists('igbinary_serialize') ? igbinary_serialize($value) : serialize($value),
            default => serialize($value)
        };
    }

    private function unserialize(string $data, string $format): mixed
    {
        return match($format) {
            'json' => json_decode($data, true),
            'msgpack' => function_exists('msgpack_unpack') ? msgpack_unpack($data) : unserialize($data),
            'igbinary' => function_exists('igbinary_unserialize') ? igbinary_unserialize($data) : unserialize($data),
            default => unserialize($data)
        };
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cached = $this->cache->get($key);
        if ($cached === null) {
            return $default;
        }

        // Extract format from stored data
        if (is_string($cached) && str_starts_with($cached, 'format:')) {
            [$format, $data] = explode(':', $cached, 2);
            $format = substr($format, 7);  // Remove 'format:' prefix
            return $this->unserialize($data, $format);
        }

        // Legacy: assume serialize
        return unserialize($cached);
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        $format = $this->detectBestFormat($value);
        $serialized = $this->serialize($value, $format);
        
        // Store with format prefix for deserialization
        $stored = "format:$format:" . $serialized;
        
        return $this->cache->set($key, $stored, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }
}
```

## Cache Key Design Patterns

Well-designed cache keys improve maintainability, debuggability, and performance. Follow consistent patterns for better cache management.

### Hierarchical Key Structure

```php
# filename: cache-key-patterns.php
<?php

declare(strict_types=1);

class CacheKeyBuilder
{
    private string $prefix;
    private string $separator = ':';

    public function __construct(string $prefix = 'app')
    {
        $this->prefix = $prefix;
    }

    /**
     * Build hierarchical cache key
     * Format: prefix:resource:id:property:version
     */
    public function build(string $resource, ?int $id = null, ?string $property = null, ?string $version = null): string
    {
        $parts = [$this->prefix, $resource];
        
        if ($id !== null) {
            $parts[] = (string)$id;
        }
        
        if ($property !== null) {
            $parts[] = $property;
        }
        
        if ($version !== null) {
            $parts[] = $version;
        }
        
        return implode($this->separator, $parts);
    }

    /**
     * Build key with hash for long/complex keys
     */
    public function buildHashed(string $resource, array $params): string
    {
        $key = $this->build($resource);
        $hash = md5(json_encode($params));
        return $key . $this->separator . substr($hash, 0, 8);
    }

    /**
     * Normalize key (lowercase, trim, remove special chars)
     */
    public function normalize(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9:_-]/', '', $key);
        return $key;
    }

    /**
     * Build pattern for wildcard matching
     */
    public function buildPattern(string $resource, ?int $id = null): string
    {
        $key = $this->build($resource, $id);
        return $key . $this->separator . '*';
    }
}

// Usage Examples
$keyBuilder = new CacheKeyBuilder('myapp');

// Simple resource key
$key = $keyBuilder->build('user', 123);
// Result: "myapp:user:123"

// Resource with property
$key = $keyBuilder->build('user', 123, 'profile');
// Result: "myapp:user:123:profile"

// Versioned key
$key = $keyBuilder->build('user', 123, 'profile', 'v2');
// Result: "myapp:user:123:profile:v2"

// Hashed key for complex queries
$key = $keyBuilder->buildHashed('products', ['category' => 'electronics', 'price_min' => 100, 'sort' => 'popularity']);
// Result: "myapp:products:a1b2c3d4"

// Pattern for invalidation
$pattern = $keyBuilder->buildPattern('user', 123);
// Result: "myapp:user:123:*"
```

### Key Naming Conventions

```php
# filename: cache-key-conventions.php
<?php

declare(strict_types=1);

class CacheKeyConventions
{
    /**
     * Best Practices:
     * 
     * 1. Use consistent separator (usually ':')
     * 2. Start with application/namespace prefix
     * 3. Use lowercase for consistency
     * 4. Include version for schema changes
     * 5. Keep keys under 250 characters (Redis limit)
     * 6. Use descriptive names, not abbreviations
     * 7. Include relevant identifiers (user_id, etc.)
     */
    
    public static function userKey(int $userId, ?string $property = null): string
    {
        $key = "app:user:{$userId}";
        if ($property) {
            $key .= ":{$property}";
        }
        return $key;
    }

    public static function queryKey(string $table, array $conditions): string
    {
        $hash = md5(json_encode($conditions));
        return "app:query:{$table}:" . substr($hash, 0, 8);
    }

    public static function sessionKey(string $sessionId): string
    {
        return "app:session:{$sessionId}";
    }

    public static function rateLimitKey(string $identifier, string $action): string
    {
        return "app:ratelimit:{$action}:{$identifier}";
    }

    public static function tagKey(string $tag): string
    {
        return "app:tag:{$tag}";
    }
}

// Usage
$userKey = CacheKeyConventions::userKey(123, 'profile');
$queryKey = CacheKeyConventions::queryKey('products', ['category' => 'electronics']);
$sessionKey = CacheKeyConventions::sessionKey('abc123');
```

## Cache Versioning

Cache versioning allows you to invalidate all cached data when your application schema or data structure changes, without manually clearing individual keys.

**Benefits:**
- Gradual migration of cached data
- Schema changes without cache invalidation issues
- A/B testing different cache structures

### Cache Versioning Implementation

```php
# filename: cache-versioning.php
<?php

declare(strict_types=1);

class VersionedCache
{
    private CacheInterface $cache;
    private string $currentVersion;
    private string $versionKey = 'cache:version';

    public function __construct(CacheInterface $cache, string $currentVersion = 'v1')
    {
        $this->cache = $cache;
        $this->currentVersion = $currentVersion;
    }

    private function versionedKey(string $key): string
    {
        $version = $this->getCurrentVersion();
        return "$version:$key";
    }

    public function getCurrentVersion(): string
    {
        $version = $this->cache->get($this->versionKey);
        return $version ?: $this->currentVersion;
    }

    public function setVersion(string $version): void
    {
        $this->cache->set($this->versionKey, $version);
        $this->currentVersion = $version;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->versionedKey($key), $default);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->cache->set($this->versionedKey($key), $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($this->versionedKey($key));
    }

    /**
     * Invalidate all data for a specific version
     */
    public function invalidateVersion(string $version): void
    {
        // In production, track keys by version
        // For now, clear all cache
        $this->cache->clear();
    }

    /**
     * Migrate to new version gradually
     */
    public function migrateToVersion(string $newVersion, callable $migrator): void
    {
        $oldVersion = $this->getCurrentVersion();
        
        // Set new version
        $this->setVersion($newVersion);
        
        // Old version keys will naturally expire
        // New reads will use new version
        // Optionally migrate critical data immediately
        if ($migrator) {
            $migrator($oldVersion, $newVersion);
        }
    }
}

// Usage
$cache = new RedisCache('127.0.0.1', 6379);
$versionedCache = new VersionedCache($cache, 'v1');

// Store data
$versionedCache->set('user:123', ['name' => 'John'], 3600);

// Migrate to v2
$versionedCache->migrateToVersion('v2', function($oldVersion, $newVersion) use ($cache) {
    // Migrate critical data
    $criticalKeys = ['user:123', 'config:app'];
    foreach ($criticalKeys as $key) {
        $oldKey = "$oldVersion:$key";
        $newKey = "$newVersion:$key";
        $value = $cache->get($oldKey);
        if ($value !== null) {
            $cache->set($newKey, $value, 3600);
        }
    }
});

// All new reads use v2 automatically
$user = $versionedCache->get('user:123');
```

## Cache Invalidation Strategies

### Tag-Based Invalidation

```php
# filename: taggable-cache.php
<?php

declare(strict_types=1);

class TaggableCache
{
    private CacheInterface $cache;
    private array $tags = [];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function tags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->taggedKey($key), $default);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Store key in each tag's list
        foreach ($this->tags as $tag) {
            $tagKeys = $this->cache->get("tag:$tag", []);
            $tagKeys[] = $key;
            $this->cache->set("tag:$tag", array_unique($tagKeys));
        }

        $result = $this->cache->set($this->taggedKey($key), $value, $ttl);
        $this->tags = [];
        return $result;
    }

    public function flush(): bool
    {
        foreach ($this->tags as $tag) {
            $keys = $this->cache->get("tag:$tag", []);
            foreach ($keys as $key) {
                $this->cache->delete($this->taggedKey($key));
            }
            $this->cache->delete("tag:$tag");
        }

        $this->tags = [];
        return true;
    }

    private function taggedKey(string $key): string
    {
        if (empty($this->tags)) {
            return $key;
        }

        return implode(':', $this->tags) . ':' . $key;
    }
}

// Usage
$cache = new TaggableCache(new ArrayCache());

$cache->tags(['users', 'profiles'])->set('user:1', ['name' => 'John']);
$cache->tags(['users'])->set('user:2', ['name' => 'Jane']);

// Invalidate all user caches
$cache->tags(['users'])->flush();
```

### Time-Based Invalidation

```php
# filename: cache-invalidator.php
<?php

declare(strict_types=1);

class CacheInvalidator
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    // Invalidate at specific time
    public function invalidateAt(string $key, \DateTime $time): void
    {
        $ttl = $time->getTimestamp() - time();
        if ($ttl > 0) {
            $this->cache->set($key . ':expires_at', $time->format('c'), $ttl);
        }
    }

    // Invalidate after events
    public function invalidateOn(string $key, string $event): void
    {
        $events = $this->cache->get('events:' . $event, []);
        $events[] = $key;
        $this->cache->set('events:' . $event, $events);
    }

    public function triggerEvent(string $event): void
    {
        $keys = $this->cache->get('events:' . $event, []);
        foreach ($keys as $key) {
            $this->cache->delete($key);
        }
        $this->cache->delete('events:' . $event);
    }
}

// Usage
$invalidator = new CacheInvalidator(new ArrayCache());

// Cache expires at midnight
$midnight = new \DateTime('tomorrow midnight');
$invalidator->invalidateAt('daily_stats', $midnight);

// Invalidate on user update
$invalidator->invalidateOn('user_profile_123', 'user_updated');
// Later...
$invalidator->triggerEvent('user_updated');
```

## Caching Backend Comparison

### Redis vs APCu vs Memcached

```php
# filename: cache-benchmark.php
<?php

declare(strict_types=1);

class CachingComparison
{
    // Benchmark different caching backends
    public function benchmarkCaches(int $iterations = 10000): array
    {
        $results = [];

        // Test data
        $testData = [
            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            'config' => array_fill(0, 100, 'config_value'),
            'large' => str_repeat('x', 10000)
        ];

        // Redis
        if (extension_loaded('redis')) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $results['redis'] = $this->benchmarkBackend($redis, $testData, $iterations);
        }

        // APCu
        if (extension_loaded('apcu')) {
            $apcu = new APCuAdapter();
            $results['apcu'] = $this->benchmarkBackend($apcu, $testData, $iterations);
        }

        // Memcached
        if (extension_loaded('memcached')) {
            $memcached = new \Memcached();
            $memcached->addServer('127.0.0.1', 11211);
            $memcachedAdapter = new MemcachedAdapter($memcached);
            $results['memcached'] = $this->benchmarkBackend($memcachedAdapter, $testData, $iterations);
        }

        // Array (in-memory baseline)
        $array = new ArrayCache();
        $results['array'] = $this->benchmarkBackend($array, $testData, $iterations);

        return $results;
    }

    private function benchmarkBackend($cache, array $testData, int $iterations): array
    {
        $writeStart = microtime(true);

        // Write benchmark
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($testData as $key => $value) {
                $cache->set("test:$key:$i", $value);
            }
        }

        $writeTime = (microtime(true) - $writeStart) * 1000;

        $readStart = microtime(true);

        // Read benchmark
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($testData as $key => $value) {
                $cache->get("test:$key:$i");
            }
        }

        $readTime = (microtime(true) - $readStart) * 1000;

        return [
            'write_ms' => round($writeTime, 2),
            'read_ms' => round($readTime, 2),
            'write_ops_per_sec' => round($iterations * count($testData) / ($writeTime / 1000)),
            'read_ops_per_sec' => round($iterations * count($testData) / ($readTime / 1000))
        ];
    }
}

class APCuAdapter
{
    public function set(string $key, $value): bool
    {
        return apcu_store($key, $value);
    }

    public function get(string $key)
    {
        return apcu_fetch($key);
    }
}

class MemcachedAdapter
{
    private \Memcached $memcached;

    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    public function set(string $key, $value): bool
    {
        return $this->memcached->set($key, $value);
    }

    public function get(string $key)
    {
        return $this->memcached->get($key);
    }
}

// Usage & Results
$comparison = new CachingComparison();
$results = $comparison->benchmarkCaches(10000);

echo "Caching Backend Performance (10,000 operations):\n\n";

foreach ($results as $backend => $metrics) {
    echo strtoupper($backend) . ":\n";
    echo "  Write: {$metrics['write_ms']}ms ({$metrics['write_ops_per_sec']} ops/sec)\n";
    echo "  Read:  {$metrics['read_ms']}ms ({$metrics['read_ops_per_sec']} ops/sec)\n\n";
}

/*
Typical Results:

ARRAY (baseline):
  Write: 45ms (666,666 ops/sec)
  Read:  38ms (789,473 ops/sec)

APCU (shared memory):
  Write: 120ms (250,000 ops/sec)
  Read:  95ms (315,789 ops/sec)

REDIS (network):
  Write: 450ms (66,666 ops/sec)
  Read:  380ms (78,947 ops/sec)

MEMCACHED (network):
  Write: 420ms (71,428 ops/sec)
  Read:  360ms (83,333 ops/sec)

Key Insights:
- Array: Fastest but not shared across requests
- APCu: 2-3x slower than array, shared across PHP processes (same server)
- Redis: 10x slower than array, distributed across servers, persistent
- Memcached: Similar to Redis, simpler feature set
*/
```

### Feature Comparison Matrix

```php
# filename: cache-feature-matrix.php
<?php

declare(strict_types=1);

class CachingFeatureMatrix
{
    public function getFeatureComparison(): array
    {
        return [
            'Array (in-memory)' => [
                'Persistence' => 'Request only',
                'Sharing' => 'No',
                'Distribution' => 'No',
                'TTL Support' => 'Manual',
                'Atomic Operations' => 'No',
                'Data Types' => 'Any PHP type',
                'Max Size' => 'memory_limit',
                'Use Case' => 'Single request caching',
                'Performance' => '★★★★★',
                'Complexity' => '★☆☆☆☆'
            ],
            'APCu' => [
                'Persistence' => 'Server restart',
                'Sharing' => 'Same server only',
                'Distribution' => 'No',
                'TTL Support' => 'Yes',
                'Atomic Operations' => 'Yes (inc/dec)',
                'Data Types' => 'Serialized',
                'Max Size' => 'apc.shm_size',
                'Use Case' => 'Single server, shared across requests',
                'Performance' => '★★★★☆',
                'Complexity' => '★☆☆☆☆'
            ],
            'Redis' => [
                'Persistence' => 'Disk (optional)',
                'Sharing' => 'Network',
                'Distribution' => 'Yes',
                'TTL Support' => 'Yes',
                'Atomic Operations' => 'Yes (many)',
                'Data Types' => 'String, Hash, List, Set, Sorted Set',
                'Max Size' => 'RAM',
                'Use Case' => 'Distributed caching, pub/sub, queues',
                'Performance' => '★★★☆☆',
                'Complexity' => '★★★☆☆'
            ],
            'Memcached' => [
                'Persistence' => 'No',
                'Sharing' => 'Network',
                'Distribution' => 'Yes',
                'TTL Support' => 'Yes',
                'Atomic Operations' => 'Yes (inc/dec)',
                'Data Types' => 'String only',
                'Max Size' => 'RAM',
                'Use Case' => 'Simple distributed caching',
                'Performance' => '★★★☆☆',
                'Complexity' => '★★☆☆☆'
            ],
            'OPcache' => [
                'Persistence' => 'Server restart',
                'Sharing' => 'Same server',
                'Distribution' => 'No',
                'TTL Support' => 'No (validates on timestamp)',
                'Atomic Operations' => 'N/A',
                'Data Types' => 'Compiled PHP code',
                'Max Size' => 'opcache.memory_consumption',
                'Use Case' => 'PHP bytecode caching (always use!)',
                'Performance' => '★★★★★',
                'Complexity' => '★☆☆☆☆'
            ]
        ];
    }

    public function printComparisonTable(): void
    {
        $comparison = $this->getFeatureComparison();

        $features = array_keys(reset($comparison));

        echo str_pad('Feature', 25) . ' | ' . implode(' | ', array_map(fn($b) => str_pad($b, 20), array_keys($comparison))) . "\n";
        echo str_repeat('-', 150) . "\n";

        foreach ($features as $feature) {
            echo str_pad($feature, 25) . ' | ';
            $values = [];
            foreach ($comparison as $backend => $features) {
                $values[] = str_pad($features[$feature], 20);
            }
            echo implode(' | ', $values) . "\n";
        }
    }
}

// Usage
$matrix = new CachingFeatureMatrix();
$matrix->printComparisonTable();
```

## Production Caching Architecture

### Multi-Level Caching Strategy

```php
# filename: multi-level-cache.php
<?php

declare(strict_types=1);

class MultiLevelCache
{
    private array $l1Cache = [];  // In-memory (fastest)
    private ?\Redis $l2Cache = null;  // Redis (shared, fast)
    private ?\PDO $l3Database = null;  // Database (source of truth)

    private array $stats = [
        'l1_hits' => 0,
        'l2_hits' => 0,
        'l3_hits' => 0,
        'misses' => 0
    ];

    public function __construct(?\Redis $redis = null, ?\PDO $pdo = null)
    {
        $this->l2Cache = $redis;
        $this->l3Database = $pdo;
    }

    public function get(string $key, ?callable $fallback = null): mixed
    {
        // L1: In-memory cache (fastest)
        if (isset($this->l1Cache[$key])) {
            $this->stats['l1_hits']++;
            return $this->l1Cache[$key];
        }

        // L2: Redis cache (fast, shared)
        if ($this->l2Cache) {
            $value = $this->l2Cache->get($key);
            if ($value !== false) {
                $this->stats['l2_hits']++;
                $decoded = unserialize($value);
                $this->l1Cache[$key] = $decoded;  // Populate L1
                return $decoded;
            }
        }

        // L3: Database or computed value
        if ($fallback) {
            $this->stats['l3_hits']++;
            $value = $fallback();

            // Populate caches
            $this->set($key, $value);

            return $value;
        }

        $this->stats['misses']++;
        return null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        // Set in all levels
        $this->l1Cache[$key] = $value;

        if ($this->l2Cache) {
            $this->l2Cache->setex($key, $ttl, serialize($value));
        }
    }

    public function delete(string $key): void
    {
        unset($this->l1Cache[$key]);

        if ($this->l2Cache) {
            $this->l2Cache->del($key);
        }
    }

    public function getStats(): array
    {
        $total = array_sum($this->stats);

        return [
            'l1_hits' => $this->stats['l1_hits'],
            'l2_hits' => $this->stats['l2_hits'],
            'l3_hits' => $this->stats['l3_hits'],
            'misses' => $this->stats['misses'],
            'total' => $total,
            'l1_hit_rate' => $total > 0 ? $this->stats['l1_hits'] / $total : 0,
            'l2_hit_rate' => $total > 0 ? $this->stats['l2_hits'] / $total : 0,
            'overall_cache_hit_rate' => $total > 0 ? ($this->stats['l1_hits'] + $this->stats['l2_hits']) / $total : 0
        ];
    }
}

// Production Usage Example
class UserRepository
{
    private MultiLevelCache $cache;
    private \PDO $db;

    public function __construct(MultiLevelCache $cache, \PDO $db)
    {
        $this->cache = $cache;
        $this->db = $db;
    }

    public function findById(int $id): ?array
    {
        return $this->cache->get("user:$id", function() use ($id) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        });
    }

    public function updateUser(int $id, array $data): void
    {
        // Update database
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['email'], $id]);

        // Invalidate cache
        $this->cache->delete("user:$id");
    }
}

// Usage
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
$pdo = new \PDO('mysql:host=localhost;dbname=app', 'user', 'pass');

$cache = new MultiLevelCache($redis, $pdo);
$users = new UserRepository($cache, $pdo);

// First call: Database hit (L3)
$user = $users->findById(123);

// Second call: Redis hit (L2)
$user = $users->findById(123);

// Third call: In-memory hit (L1) - fastest
$user = $users->findById(123);

print_r($cache->getStats());
/*
Array
(
    [l1_hits] => 1
    [l2_hits] => 1
    [l3_hits] => 1
    [misses] => 0
    [total] => 3
    [l1_hit_rate] => 0.33
    [l2_hit_rate] => 0.33
    [overall_cache_hit_rate] => 0.67
)
*/
```

### Cache Stampede Prevention

```php
# filename: stampede-prevention.php
<?php

declare(strict_types=1);

class StampedePrevention
{
    private CacheInterface $cache;
    private array $locks = [];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    // Method 1: Probabilistic Early Expiration
    public function getWithProbabilisticExpiration(
        string $key,
        callable $callback,
        int $ttl = 3600,
        float $beta = 1.0
    ): mixed {
        $value = $this->cache->get($key . ':value');
        $expiry = $this->cache->get($key . ':expiry');

        if ($value !== null && $expiry !== null) {
            $timeLeft = $expiry - time();
            $random = -log(mt_rand() / mt_getrandmax());

            // Probabilistically recompute before expiration
            if ($timeLeft > $random * $beta) {
                return $value;
            }
        }

        // Recompute
        $value = $callback();
        $this->cache->set($key . ':value', $value, $ttl);
        $this->cache->set($key . ':expiry', time() + $ttl, $ttl);

        return $value;
    }

    // Method 2: Lock-based (Redis)
    public function getWithLock(
        string $key,
        callable $callback,
        int $ttl = 3600,
        int $lockTimeout = 10
    ): mixed {
        $value = $this->cache->get($key);

        if ($value !== null) {
            return $value;
        }

        // Try to acquire lock
        $lockKey = "lock:$key";
        $locked = $this->acquireLock($lockKey, $lockTimeout);

        if ($locked) {
            try {
                // Double-check cache (another process might have updated it)
                $value = $this->cache->get($key);
                if ($value !== null) {
                    return $value;
                }

                // Compute value
                $value = $callback();
                $this->cache->set($key, $value, $ttl);

                return $value;
            } finally {
                $this->releaseLock($lockKey);
            }
        } else {
            // Wait for other process to finish
            $attempts = 0;
            while ($attempts < 50) {  // Max 5 seconds
                usleep(100000);  // 100ms
                $value = $this->cache->get($key);
                if ($value !== null) {
                    return $value;
                }
                $attempts++;
            }

            // Fallback: compute anyway
            return $callback();
        }
    }

    private function acquireLock(string $key, int $timeout): bool
    {
        // For Redis with NX and EX
        if ($this->cache instanceof RedisCache) {
            return $this->cache->getRedis()->set($key, 1, ['nx', 'ex' => $timeout]);
        }

        // Fallback
        if (!$this->cache->has($key)) {
            $this->cache->set($key, 1, $timeout);
            return true;
        }

        return false;
    }

    private function releaseLock(string $key): void
    {
        $this->cache->delete($key);
    }

    // Method 3: Staggered Expiration
    public function setWithStaggeredExpiration(
        string $key,
        mixed $value,
        int $baseTTL = 3600,
        int $jitter = 300
    ): void {
        $ttl = $baseTTL + rand(-$jitter, $jitter);
        $this->cache->set($key, $value, $ttl);
    }
}

// Usage Example
// Note: In production, use connection pooling and handle connection failures gracefully
$cache = new RedisCache('127.0.0.1', 6379);

$stampede = new StampedePrevention($cache);

// Prevent stampede when fetching expensive data
$popularPost = $stampede->getWithLock('post:popular:1', function() {
    // Expensive operation
    sleep(2);  // Simulate slow query
    return ['id' => 1, 'title' => 'Popular Post', 'views' => 1000000];
}, 3600, 10);
```

### Production Monitoring & Debugging

```php
# filename: cache-monitor.php
<?php

declare(strict_types=1);

class CacheMonitor
{
    private CacheInterface $cache;
    private array $metrics = [];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $key): mixed
    {
        $start = microtime(true);
        $value = $this->cache->get($key);
        $time = (microtime(true) - $start) * 1000;

        $this->recordMetric('get', $key, $time, $value !== null);

        return $value;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $start = microtime(true);
        $result = $this->cache->set($key, $value, $ttl);
        $time = (microtime(true) - $start) * 1000;

        $this->recordMetric('set', $key, $time, true);

        return $result;
    }

    private function recordMetric(string $operation, string $key, float $time, bool $hit): void
    {
        $this->metrics[] = [
            'operation' => $operation,
            'key' => $key,
            'time_ms' => $time,
            'hit' => $hit,
            'timestamp' => microtime(true)
        ];

        // Keep only last 1000 metrics
        if (count($this->metrics) > 1000) {
            array_shift($this->metrics);
        }
    }

    public function getStatistics(): array
    {
        if (empty($this->metrics)) {
            return [];
        }

        $gets = array_filter($this->metrics, fn($m) => $m['operation'] === 'get');
        $sets = array_filter($this->metrics, fn($m) => $m['operation'] === 'set');

        $hits = array_filter($gets, fn($m) => $m['hit']);
        $misses = array_filter($gets, fn($m) => !$m['hit']);

        $getTimes = array_map(fn($m) => $m['time_ms'], $gets);
        $setTimes = array_map(fn($m) => $m['time_ms'], $sets);

        return [
            'total_operations' => count($this->metrics),
            'get_operations' => count($gets),
            'set_operations' => count($sets),
            'cache_hits' => count($hits),
            'cache_misses' => count($misses),
            'hit_rate' => count($gets) > 0 ? count($hits) / count($gets) : 0,
            'avg_get_time_ms' => !empty($getTimes) ? array_sum($getTimes) / count($getTimes) : 0,
            'avg_set_time_ms' => !empty($setTimes) ? array_sum($setTimes) / count($setTimes) : 0,
            'p95_get_time_ms' => !empty($getTimes) ? $this->percentile($getTimes, 0.95) : 0,
            'p95_set_time_ms' => !empty($setTimes) ? $this->percentile($setTimes, 0.95) : 0
        ];
    }

    private function percentile(array $values, float $percentile): float
    {
        sort($values);
        $index = (int)ceil(count($values) * $percentile) - 1;
        return $values[max(0, $index)];
    }

    public function getSlowOperations(float $threshold = 10.0): array
    {
        return array_filter($this->metrics, fn($m) => $m['time_ms'] > $threshold);
    }

    public function getMostAccessedKeys(int $limit = 10): array
    {
        $keyCounts = [];

        foreach ($this->metrics as $metric) {
            $key = $metric['key'];
            $keyCounts[$key] = ($keyCounts[$key] ?? 0) + 1;
        }

        arsort($keyCounts);

        return array_slice($keyCounts, 0, $limit, true);
    }

    public function getCacheEfficiency(): array
    {
        $gets = array_filter($this->metrics, fn($m) => $m['operation'] === 'get');
        $hits = array_filter($gets, fn($m) => $m['hit']);
        $misses = array_filter($gets, fn($m) => !$m['hit']);

        $totalGets = count($gets);
        $totalHits = count($hits);
        $totalMisses = count($misses);

        // Calculate cost savings (assuming cache hit is 100x faster than miss)
        $hitTime = array_sum(array_map(fn($m) => $m['time_ms'], $hits));
        $missTime = array_sum(array_map(fn($m) => $m['time_ms'], $misses));
        
        // Estimated time if all were misses
        $estimatedMissTime = $totalGets * ($missTime / max($totalMisses, 1));
        $timeSaved = $estimatedMissTime - ($hitTime + $missTime);

        return [
            'hit_rate' => $totalGets > 0 ? $totalHits / $totalGets : 0,
            'miss_rate' => $totalGets > 0 ? $totalMisses / $totalGets : 0,
            'total_operations' => $totalGets,
            'cache_hits' => $totalHits,
            'cache_misses' => $totalMisses,
            'estimated_time_saved_ms' => round($timeSaved, 2),
            'efficiency_score' => $totalGets > 0 ? round(($totalHits / $totalGets) * 100, 2) : 0
        ];
    }

    public function getKeyAccessPatterns(): array
    {
        $patterns = [
            'hot_keys' => [],      // Frequently accessed
            'cold_keys' => [],     // Rarely accessed
            'one_time_keys' => []  // Accessed only once
        ];

        $keyAccessCounts = [];
        foreach ($this->metrics as $metric) {
            $key = $metric['key'];
            $keyAccessCounts[$key] = ($keyAccessCounts[$key] ?? 0) + 1;
        }

        $sorted = $keyAccessCounts;
        arsort($sorted);

        $total = count($sorted);
        $hotThreshold = (int)($total * 0.1);  // Top 10%
        $coldThreshold = (int)($total * 0.9);  // Bottom 10%

        $index = 0;
        foreach ($sorted as $key => $count) {
            if ($index < $hotThreshold) {
                $patterns['hot_keys'][$key] = $count;
            } elseif ($index >= $coldThreshold) {
                $patterns['cold_keys'][$key] = $count;
            }
            
            if ($count === 1) {
                $patterns['one_time_keys'][$key] = $count;
            }
            
            $index++;
        }

        return $patterns;
    }
}

// Usage
$cache = new RedisCache('127.0.0.1', 6379);
$monitor = new CacheMonitor($cache);

// Use monitored cache
for ($i = 0; $i < 1000; $i++) {
    $monitor->set("key:$i", "value:$i");
    $monitor->get("key:" . rand(0, 999));
}

$stats = $monitor->getStatistics();
print_r($stats);

$slowOps = $monitor->getSlowOperations(5.0);
echo "Slow operations (>5ms): " . count($slowOps) . "\n";

$topKeys = $monitor->getMostAccessedKeys(5);
echo "Most accessed keys:\n";
foreach ($topKeys as $key => $count) {
    echo "  $key: $count accesses\n";
}
```

## Distributed Cache Consistency

In distributed systems with multiple cache servers, maintaining consistency becomes challenging. Different consistency models offer different trade-offs.

### Consistency Models

```php
# filename: distributed-cache-consistency.php
<?php

declare(strict_types=1);

/**
 * Strong Consistency: All nodes see updates immediately
 * - Slower writes (must update all nodes)
 * - No stale data
 * - Higher latency
 */
class StrongConsistentCache
{
    private array $nodes = [];

    public function addNode(CacheInterface $cache): void
    {
        $this->nodes[] = $cache;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $success = true;
        
        // Write to all nodes (synchronous)
        foreach ($this->nodes as $node) {
            if (!$node->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        
        return $success;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // Read from first available node
        foreach ($this->nodes as $node) {
            $value = $node->get($key);
            if ($value !== null) {
                return $value;
            }
        }
        
        return $default;
    }
}

/**
 * Eventual Consistency: Updates propagate asynchronously
 * - Faster writes (update one node)
 * - May have temporary inconsistencies
 * - Lower latency
 */
class EventuallyConsistentCache
{
    private CacheInterface $primary;
    private array $replicas = [];

    public function __construct(CacheInterface $primary)
    {
        $this->primary = $primary;
    }

    public function addReplica(CacheInterface $replica): void
    {
        $this->replicas[] = $replica;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Write to primary immediately
        $result = $this->primary->set($key, $value, $ttl);
        
        // Replicate asynchronously (in production, use message queue)
        $this->replicateAsync($key, $value, $ttl);
        
        return $result;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // Try primary first
        $value = $this->primary->get($key);
        if ($value !== null) {
            return $value;
        }
        
        // Fallback to replicas
        foreach ($this->replicas as $replica) {
            $value = $replica->get($key);
            if ($value !== null) {
                return $value;
            }
        }
        
        return $default;
    }

    private function replicateAsync(string $key, mixed $value, ?int $ttl): void
    {
        // In production, use message queue or background job
        foreach ($this->replicas as $replica) {
            // Async replication would happen here
            $replica->set($key, $value, $ttl);
        }
    }
}

/**
 * Read-Repair: Fix inconsistencies on read
 */
class ReadRepairCache
{
    private array $nodes = [];

    public function addNode(CacheInterface $cache): void
    {
        $this->nodes[] = $cache;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $values = [];
        $nodeValues = [];
        
        // Read from all nodes
        foreach ($this->nodes as $index => $node) {
            $value = $node->get($key);
            if ($value !== null) {
                $values[] = $value;
                $nodeValues[$index] = $value;
            }
        }
        
        if (empty($values)) {
            return $default;
        }
        
        // If values differ, repair (use most recent or majority)
        if (count(array_unique($values)) > 1) {
            $correctValue = $this->resolveConflict($values);
            
            // Repair nodes with incorrect values
            foreach ($nodeValues as $index => $value) {
                if ($value !== $correctValue) {
                    $this->nodes[$index]->set($key, $correctValue);
                }
            }
            
            return $correctValue;
        }
        
        return $values[0];
    }

    private function resolveConflict(array $values): mixed
    {
        // Simple strategy: use most common value
        $counts = array_count_values(array_map('serialize', $values));
        arsort($counts);
        return unserialize(array_key_first($counts));
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($this->nodes as $node) {
            if (!$node->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
}
```

### Cache Coherency Protocols

```php
# filename: cache-coherency.php
<?php

declare(strict_types=1);

/**
 * Write-Invalidate: Invalidate other caches on write
 */
class WriteInvalidateCache
{
    private CacheInterface $localCache;
    private array $remoteCaches = [];
    private string $invalidationChannel = 'cache:invalidate';

    public function __construct(CacheInterface $localCache)
    {
        $this->localCache = $localCache;
    }

    public function addRemoteCache(CacheInterface $cache): void
    {
        $this->remoteCaches[] = $cache;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Write to local cache
        $this->localCache->set($key, $value, $ttl);
        
        // Invalidate remote caches
        $this->invalidateRemote($key);
        
        return true;
    }

    private function invalidateRemote(string $key): void
    {
        foreach ($this->remoteCaches as $cache) {
            $cache->delete($key);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->localCache->get($key, $default);
    }
}

/**
 * Write-Update: Update other caches on write
 */
class WriteUpdateCache
{
    private CacheInterface $localCache;
    private array $remoteCaches = [];

    public function __construct(CacheInterface $localCache)
    {
        $this->localCache = $localCache;
    }

    public function addRemoteCache(CacheInterface $cache): void
    {
        $this->remoteCaches[] = $cache;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        // Write to local cache
        $this->localCache->set($key, $value, $ttl);
        
        // Update remote caches
        foreach ($this->remoteCaches as $cache) {
            $cache->set($key, $value, $ttl);
        }
        
        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->localCache->get($key, $default);
    }
}
```

## Best Practices

1. **Choose the Right TTL**
   - Frequently changing data: Short TTL (seconds to minutes)
   - Relatively static data: Long TTL (hours to days)
   - Computed expensive operations: Long TTL with manual invalidation

2. **Cache Key Naming**
   - Use consistent naming: `resource:id:property`
   - Include version: `v1:user:123`
   - Use prefixes for namespacing

3. **Invalidation Strategy**
   - Prefer TTL over manual invalidation when possible
   - Use tags for related data
   - Implement cache warming for critical data

4. **Monitor Cache Performance**
   - Track hit/miss ratios
   - Monitor memory usage
   - Measure cache overhead vs benefit

5. **Avoid Cache Stampede**
   - Use cache locking
   - Stagger cache expiration
   - Implement probabilistic early expiration

6. **Multi-Level Caching**
   - L1: In-memory (request-scoped)
   - L2: APCu/OPcache (server-scoped)
   - L3: Redis/Memcached (distributed)
   - L4: Database (source of truth)

7. **Backend Selection**
   - **Array**: Single request, temporary data
   - **APCu**: Single server, shared across requests, configuration
   - **Redis**: Multi-server, complex data types, pub/sub, persistence
   - **Memcached**: Multi-server, simple key-value, no persistence

8. **Cache Patterns**
   - **Cache-aside**: Application loads data into cache (most common)
   - **Read-through**: Cache automatically loads from data source
   - **Write-through**: Write to cache and database simultaneously
   - **Write-behind**: Write to cache, database asynchronously
   - **Write-around**: Write to database, populate cache on read

9. **Serialization Strategy**
   - **JSON**: Fast, human-readable, language-agnostic (best for simple data)
   - **MessagePack**: Very compact, fast, cross-language (best for large data)
   - **Igbinary**: Faster than serialize, PHP-compatible (best for PHP-only)
   - **Serialize**: Handles all PHP types, but larger and slower

10. **Cache Key Design**
    - Use hierarchical structure: `app:resource:id:property`
    - Include version for schema changes
    - Keep keys under 250 characters
    - Use consistent separators
    - Normalize keys (lowercase, no special chars)

11. **Distributed Consistency**
    - **Strong consistency**: All nodes updated synchronously (slower, no stale data)
    - **Eventual consistency**: Updates propagate asynchronously (faster, may have temporary inconsistencies)
    - **Read-repair**: Fix inconsistencies when detected on read

## Production Metrics

Based on real-world implementations:

### Cache Hit Rates

- **Good**: 85-95% hit rate
- **Acceptable**: 70-85% hit rate
- **Poor**: <70% hit rate (review caching strategy)

### Performance Gains

| Scenario | Without Cache | With Cache | Improvement |
|----------|---------------|------------|-------------|
| Database Query | 50ms | 2ms | 25x faster |
| API Call | 200ms | 1ms | 200x faster |
| Complex Calculation | 500ms | 0.5ms | 1000x faster |
| Template Rendering | 30ms | 1ms | 30x faster |

### Memory vs Speed Trade-off

```php
// Example: 100,000 user profiles
// Without cache: 0 MB memory, 5000ms total query time
// With cache: 50 MB memory, 100ms total query time (98% faster)
// Trade-off: 50 MB for 50x performance improvement
```

## Key Takeaways

- Caching trades memory for speed by storing computed results
- LRU cache automatically evicts least recently used items
- TTL cache automatically expires stale data
- PSR-16 provides standard cache interface for interoperability
- **APCu** is fastest for single-server shared caching (PSR-16 compliant implementation provided)
- **Memcached** is simpler than Redis but highly performant for distributed caching (PSR-16 compliant implementation provided)
- **Redis** enables distributed caching across multiple servers with rich data types
- Multi-level caching provides optimal performance: memory → APCu → Redis → Database
- **Cache warming** preloads critical data after deployments to reduce cache misses
- **Write patterns** optimize for different scenarios: write-through (consistency), write-behind (performance), write-around (avoid pollution)
- **Cache versioning** enables gradual schema migrations without manual invalidation
- **Cache-aside** is the most common pattern where application loads data into cache
- **Read-through** cache automatically loads from data source on miss
- **Cache compression** reduces memory usage for large values (>1KB typically)
- **Serialization format** choice impacts performance: JSON (fast, simple), MessagePack (compact), Igbinary (PHP-optimized)
- **Cache key design** follows hierarchical patterns: `app:resource:id:property:version`
- **Distributed consistency** models: strong (synchronous, consistent) vs eventual (asynchronous, may be stale)
- Query result caching dramatically improves database performance (25-200x)
- Computed property memoization prevents redundant calculations
- Tag-based invalidation simplifies cache management
- Cache stampede prevention is critical for high-traffic applications
- Proper cache key design and invalidation strategy are critical
- Monitor cache metrics to optimize configuration (aim for 85%+ hit rate)
- Always use OPcache in production (free 2-3x performance boost)

## Exercises

### Exercise 1: Implement LFU Cache (Least Frequently Used)

**Goal**: Build a cache that evicts the least frequently accessed item when full.

Create a file called `lfu-cache.php` and implement:

- `LFUCache` class with capacity limit
- `get(string $key)` method that increments access count
- `set(string $key, mixed $value)` method that evicts least frequently used item when at capacity
- Track access frequency for each key
- When frequencies tie, use LRU as tiebreaker

**Validation**: Test your implementation:

```php
$lfu = new LFUCache(3);
$lfu->set('a', 1);
$lfu->set('b', 2);
$lfu->set('c', 3);

$lfu->get('a');  // Access 'a' twice
$lfu->get('a');
$lfu->get('b');  // Access 'b' once

$lfu->set('d', 4);  // 'c' should be evicted (lowest frequency)

echo $lfu->get('c') ?? 'null';  // Should output: null
echo $lfu->get('a');  // Should output: 1
```

### Exercise 2: Cache Warming Strategy

**Goal**: Implement a cache warming system that preloads frequently accessed data.

Create a file called `cache-warmer.php` and implement:

- `CacheWarmer` class that accepts a cache and list of keys to warm
- `warm(array $keys, callable $loader)` method that loads and caches data
- `warmAsync(array $keys, callable $loader)` method using multiple processes/threads
- Track warming statistics (keys warmed, time taken, errors)

**Validation**: Test with a mock cache and verify all keys are populated.

### Exercise 3: Cache Hit Rate Monitor

**Goal**: Build a monitoring system that tracks cache performance over time.

Create a file called `cache-analytics.php` and implement:

- `CacheAnalytics` class that wraps a cache and tracks metrics
- Track hit/miss rates per hour/day
- Identify cache keys with highest miss rates
- Generate performance reports
- Alert when hit rate drops below threshold

**Validation**: Run with various cache patterns and verify accurate reporting.

## Troubleshooting

### Error: "Call to undefined method RedisCache::getRedis()"

**Symptom**: `Fatal error: Call to undefined method RedisCache::getRedis()`

**Cause**: The `StampedePrevention` class calls `getRedis()` but it wasn't defined in `RedisCache`.

**Solution**: Add the `getRedis()` method to `RedisCache`:

```php
public function getRedis(): \Redis
{
    return $this->redis;
}
```

### Problem: Cache Memory Growing Unbounded

**Symptom**: Memory usage increases continuously, eventually causing out-of-memory errors.

**Cause**: Cache never evicts old entries or TTL not being enforced.

**Solution**: 

1. Implement LRU eviction when cache reaches size limit
2. Ensure TTL expiration is checked on every access
3. Add periodic cleanup job for expired entries
4. Monitor cache size and set maximum capacity

```php
// Add size limit to SimpleCache
public function set(string $key, mixed $value, ?int $maxSize = null): void
{
    if ($maxSize !== null && count($this->cache) >= $maxSize) {
        // Evict oldest entry
        $oldestKey = array_key_first($this->cache);
        unset($this->cache[$oldestKey]);
    }
    $this->cache[$key] = $value;
}
```

### Problem: Stale Data After Updates

**Symptom**: Cache returns old data even after database updates.

**Cause**: Cache not invalidated when source data changes.

**Solution**: 

1. Invalidate cache on write operations
2. Use tag-based invalidation for related data
3. Implement cache versioning
4. Use shorter TTL for frequently changing data

```php
// Invalidate on update
public function updateUser(int $id, array $data): void
{
    // Update database
    $this->db->update('users', $data, ['id' => $id]);
    
    // Invalidate cache
    $this->cache->delete("user:$id");
    $this->cache->tags(['users'])->flush();  // Or invalidate tag
}
```

### Problem: Cache Stampede on Expiration

**Symptom**: Multiple requests hit database simultaneously when cache expires.

**Cause**: All cache entries expire at same time, causing thundering herd.

**Solution**: 

1. Use staggered expiration (add random jitter to TTL)
2. Implement probabilistic early expiration
3. Use cache locking to prevent concurrent recomputation
4. Pre-warm cache before expiration

```php
// Staggered expiration
public function set(string $key, mixed $value, int $baseTTL = 3600): void
{
    $jitter = rand(-300, 300);  // ±5 minutes
    $ttl = $baseTTL + $jitter;
    $this->cache->set($key, $value, $ttl);
}
```

## Wrap-up

You've completed a comprehensive journey through caching and memoization strategies! Here's what you've accomplished:

- ✓ Built multiple cache implementations (Simple, LRU, TTL)
- ✓ Created PSR-16 compliant cache interfaces
- ✓ Integrated Redis for distributed caching
- ✓ Implemented database query result caching
- ✓ Designed multi-level caching architectures
- ✓ Created cache invalidation strategies
- ✓ Built cache stampede prevention mechanisms
- ✓ Developed production monitoring tools

Caching is one of the highest-impact optimizations you can apply to PHP applications. The techniques you've learned can improve performance by 25-1000x in real-world scenarios. Remember to monitor your cache hit rates (aim for 85%+) and choose the right caching backend for your use case.


## Further Reading

- [PSR-16: Simple Cache](https://www.php-fig.org/psr/psr-16/) — PHP-FIG cache interface standard
- [Redis Documentation](https://redis.io/docs/) — Complete Redis reference
- [APCu Documentation](https://www.php.net/manual/en/book.apcu.php) — PHP shared memory caching
- [Memcached Documentation](https://www.php.net/manual/en/book.memcached.php) — PHP Memcached extension
- [OPcache Configuration](https://www.php.net/manual/en/opcache.configuration.php) — PHP bytecode caching
- [Cache Stampede Prevention](https://en.wikipedia.org/wiki/Cache_stampede) — Wikipedia article on cache stampede patterns

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 27 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-27)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-27
php 01-*.php
```

## Next Steps

In the next chapter, we'll create an algorithm selection guide to help you choose the right algorithm for different problem types and constraints.
