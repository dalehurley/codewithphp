---
title: "Caching & Memoization Strategies"
description: "Learn practical caching strategies for PHP applications including in-memory caching, Redis integration, query result caching, and computed property memoization"
series: "php-algorithms"
chapter: 27
order: 27
difficulty: "intermediate"
prerequisites: ["Dynamic Programming Fundamentals", "Hash Tables & Hash Functions"]
---

# Caching & Memoization Strategies

Caching is one of the most effective performance optimizations. This chapter explores practical caching techniques for PHP applications, from simple in-memory caches to distributed caching systems.

## In-Memory Caching

### Simple Array-Based Cache

```php
<?php

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

```php
<?php

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
        // Remove from current position
        $this->keys = array_values(array_filter($this->keys, fn($k) => $k !== $key));
        // Add to end (most recent)
        $this->keys[] = $key;
    }

    public function getSize(): int
    {
        return count($this->cache);
    }

    public function getAccessOrder(): array
    {
        return $this->keys;
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

### TTL Cache (Time To Live)

```php
<?php

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

Following PHP-FIG standards for interoperability.

```php
<?php

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

### Query Result Cache

```php
<?php

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
        return 'query_' . md5($sql . serialize($params));
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
<?php

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

### Class Property Caching

```php
<?php

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
<?php

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

## Redis Integration

### Redis Cache Implementation

```php
<?php

class RedisCache implements CacheInterface
{
    private \Redis $redis;
    private string $prefix;

    public function __construct(string $host = '127.0.0.1', int $port = 6379, string $prefix = '')
    {
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
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
        $keys = $this->redis->keys($this->prefix . '*');
        if (!empty($keys)) {
            $this->redis->del(...$keys);
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

## Cache Invalidation Strategies

### Tag-Based Invalidation

```php
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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
<?php

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
$redis = new \Redis();
$redis->connect('127.0.0.1');
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
<?php

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
- Redis enables distributed caching across multiple servers
- APCu is fastest for single-server shared caching
- Memcached is simpler than Redis but less feature-rich
- Multi-level caching provides optimal performance: memory → APCu → Redis → Database
- Query result caching dramatically improves database performance (25-200x)
- Computed property memoization prevents redundant calculations
- Tag-based invalidation simplifies cache management
- Cache stampede prevention is critical for high-traffic applications
- Proper cache key design and invalidation strategy are critical
- Monitor cache metrics to optimize configuration (aim for 85%+ hit rate)
- Always use OPcache in production (free 2-3x performance boost)

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 27 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code-samples/php-algorithms/chapter-27)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code-samples/php-algorithms/chapter-27
php 01-*.php
```

## Next Steps

In the next chapter, we'll create an algorithm selection guide to help you choose the right algorithm for different problem types and constraints.
