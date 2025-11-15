# Chapter 27: Caching & Memoization Strategies

Production-ready caching implementations for PHP applications.

## Code Samples

### 1. lru-cache.php
**LRU (Least Recently Used) Cache**

Efficient cache that automatically evicts least recently used items.

- **Time:** O(1) get/set
- **Space:** O(capacity)
- **Features:** Hit rate tracking, eviction monitoring, statistics

**Run:** `php lru-cache.php`

### 2. multi-level-cache.php
**Multi-Level Caching Strategy**

Production caching with L1 (memory), L2 (APCu), L3 (Redis) levels.

- **L1:** 0.01ms (request-scoped)
- **L2:** 0.1ms (shared memory)
- **L3:** 2ms (distributed)
- **Features:** Auto-population, statistics, cost analysis

**Run:** `php multi-level-cache.php`

## Quick Start

```bash
# Run all examples
for file in *.php; do php "$file"; done
```

## Key Concepts

- **LRU Eviction:** Automatic cleanup of old data
- **Multi-Level:** Fast local cache + distributed cache
- **Hit Rate:** Aim for 85%+ in production
- **TTL Strategy:** Different timeouts for different data

## Performance

| Level | Latency | Use Case |
|-------|---------|----------|
| L1 | 0.01ms | Current request data |
| L2 | 0.1ms | Shared config, sessions |
| L3 | 2ms | Distributed data |
| DB | 50ms | Source of truth |

## Requirements

- PHP 8.0+
- Optional: Redis extension, APCu extension

**Next:** [Chapter 28: Algorithm Selection Guide](../chapter-28/)
