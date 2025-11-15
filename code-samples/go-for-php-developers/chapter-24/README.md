# Chapter 24: Redis & Caching

Master Redis with Go for caching, sessions, and real-time features. Learn go-redis, caching strategies, and performance optimization.

## Overview

Redis is essential for modern web applications. Go's go-redis client provides excellent Redis support for caching, sessions, pub/sub, and more - similar to PHP's Predis or PhpRedis.

## Files

1. `01-redis-basics.go` - Connecting, basic operations
2. `02-caching-strategies.go` - Cache-aside, write-through, TTL
3. `03-sessions.go` - Redis-backed sessions
4. `04-pub-sub.go` - Real-time messaging with pub/sub
5. `05-rate-limiting.go` - Rate limiting with Redis
6. `06-distributed-locks.go` - Distributed locking patterns

## Quick Reference

**PHP (Predis)**:
```php
$redis = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

$redis->set('key', 'value');
$value = $redis->get('key');
$redis->setex('key', 3600, 'value');  // With TTL
```

**Go (go-redis)**:
```go
import "github.com/go-redis/redis/v8"

rdb := redis.NewClient(&redis.Options{
    Addr: "localhost:6379",
})

ctx := context.Background()

rdb.Set(ctx, "key", "value", 0)
val, _ := rdb.Get(ctx, "key").Result()
rdb.Set(ctx, "key", "value", time.Hour)  // With TTL
```

## Common Patterns

### Cache-Aside Pattern
```go
func getUser(id int) (*User, error) {
    cacheKey := fmt.Sprintf("user:%d", id)

    // Try cache first
    cached, err := rdb.Get(ctx, cacheKey).Result()
    if err == nil {
        var user User
        json.Unmarshal([]byte(cached), &user)
        return &user, nil
    }

    // Cache miss - get from database
    user, err := db.GetUser(id)
    if err != nil {
        return nil, err
    }

    // Store in cache
    data, _ := json.Marshal(user)
    rdb.Set(ctx, cacheKey, data, 10*time.Minute)

    return user, nil
}
```

### Rate Limiting
```go
func checkRateLimit(userID string) bool {
    key := fmt.Sprintf("rate_limit:%s", userID)

    count, err := rdb.Incr(ctx, key).Result()
    if err != nil {
        return false
    }

    if count == 1 {
        rdb.Expire(ctx, key, time.Minute)
    }

    return count <= 100  // Max 100 requests per minute
}
```

### Session Management
```go
func saveSession(sessionID string, data map[string]interface{}) error {
    key := fmt.Sprintf("session:%s", sessionID)
    value, _ := json.Marshal(data)
    return rdb.Set(ctx, key, value, 24*time.Hour).Err()
}

func getSession(sessionID string) (map[string]interface{}, error) {
    key := fmt.Sprintf("session:%s", sessionID)
    val, err := rdb.Get(ctx, key).Result()
    if err != nil {
        return nil, err
    }

    var data map[string]interface{}
    json.Unmarshal([]byte(val), &data)
    return data, nil
}
```

## Best Practices

- Use connection pooling (go-redis handles this)
- Set appropriate TTLs for cache entries
- Handle cache misses gracefully
- Use pipelines for batch operations
- Monitor Redis memory usage
- Use Redis Cluster for high availability
- Implement cache warming for critical data
- Consider cache invalidation strategies

## Next Steps

- Chapter 25: Data Migrations
- Chapter 26: Unit Testing
- Chapter 37: Logging & Monitoring

---

**Key Takeaway**: go-redis provides excellent Redis support with features like pipelining, pub/sub, and clustering. Use Redis for caching, sessions, and real-time features to dramatically improve application performance.
