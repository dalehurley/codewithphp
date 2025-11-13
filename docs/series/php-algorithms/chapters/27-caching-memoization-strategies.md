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

## Key Takeaways

- Caching trades memory for speed by storing computed results
- LRU cache automatically evicts least recently used items
- TTL cache automatically expires stale data
- PSR-16 provides standard cache interface for interoperability
- Redis enables distributed caching across multiple servers
- Query result caching dramatically improves database performance
- Computed property memoization prevents redundant calculations
- Tag-based invalidation simplifies cache management
- Proper cache key design and invalidation strategy are critical
- Monitor cache metrics to optimize configuration

## Next Steps

In the next chapter, we'll create an algorithm selection guide to help you choose the right algorithm for different problem types and constraints.
