---
title: "17: System Design Basics"
description: "Design scalable systems with CS principles. Understand load balancing, caching layers, database indexing, message queues, and how computer science concepts apply to distributed systems."
series: "computer-science"
chapter: 17
order: 17
difficulty: "Advanced"
prerequisites: ["Data structures", "Algorithms", "Optimization techniques"]
---

# Chapter 17: System Design Basics

## Introduction

System design applies computer science principles to build scalable, reliable applications. It's about making architectural decisions that affect millions of users.

In this chapter, you'll learn:

- System design principles
- Scalability patterns
- Common system components
- How CS concepts apply at scale

## System Design Principles

### 1. Scalability

**Vertical scaling**: Add more power (CPU, RAM)
**Horizontal scaling**: Add more servers

```php
<?php

// Before: Single server
$db = new PDO('mysql:host=localhost');

// After: Multiple servers with load balancer
$servers = ['db1.example.com', 'db2.example.com', 'db3.example.com'];
$server = $servers[array_rand($servers)]; // Simple load balancing
$db = new PDO("mysql:host=$server");
```

### 2. Reliability

**Redundancy**: Multiple copies of data/services
**Failover**: Automatic switching to backup

### 3. Availability

**Target**: 99.9% uptime = 8.76 hours downtime/year
**Strategy**: Eliminate single points of failure

### 4. Performance

**Latency**: Response time
**Throughput**: Requests per second

## Key System Components

### Load Balancer

Distributes traffic across servers.

**Algorithms**:
- Round Robin
- Least Connections
- IP Hash

```php
<?php

class LoadBalancer {
    private array $servers;
    private int $current = 0;

    public function __construct(array $servers) {
        $this->servers = $servers;
    }

    // Round robin
    public function getServer(): string {
        $server = $this->servers[$this->current];
        $this->current = ($this->current + 1) % count($this->servers);
        return $server;
    }
}
```

### Caching Layer

Store frequently accessed data in memory.

**Strategies**:
1. **Cache-aside**: App checks cache, then DB
2. **Write-through**: Write to cache and DB simultaneously
3. **Write-behind**: Write to cache, async to DB

```php
<?php

class Cache {
    private array $store = [];
    private array $timestamps = [];
    private int $ttl = 3600; // 1 hour

    public function get(string $key): mixed {
        if (!isset($this->store[$key])) {
            return null;
        }

        // Check expiration
        if (time() - $this->timestamps[$key] > $this->ttl) {
            unset($this->store[$key], $this->timestamps[$key]);
            return null;
        }

        return $this->store[$key];
    }

    public function set(string $key, mixed $value): void {
        $this->store[$key] = $value;
        $this->timestamps[$key] = time();
    }
}

// Usage with database
function getUser(int $id, Cache $cache, PDO $db): array {
    $key = "user:$id";

    // Try cache first (O(1))
    $user = $cache->get($key);
    if ($user !== null) {
        return $user;
    }

    // Cache miss - query database (slow)
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Store in cache for next time
    $cache->set($key, $user);

    return $user;
}
```

**Eviction Policies**:
- **LRU** (Least Recently Used)
- **LFU** (Least Frequently Used)
- **FIFO** (First In, First Out)

### Database Indexing

Speed up queries using B-trees/hash tables.

```sql
-- Without index: O(n) - full table scan
SELECT * FROM users WHERE email = 'john@example.com';

-- With index: O(log n) - B-tree lookup
CREATE INDEX idx_email ON users(email);
SELECT * FROM users WHERE email = 'john@example.com';
```

**Trade-off**:
- Faster reads
- Slower writes (maintain index)
- More storage

### Message Queues

Decouple services, handle asynchronous tasks.

```php
<?php

class MessageQueue {
    private array $queue = [];

    public function enqueue(array $message): void {
        $this->queue[] = $message;
    }

    public function dequeue(): ?array {
        return array_shift($this->queue);
    }

    public function isEmpty(): bool {
        return empty($this->queue);
    }
}

// Producer
$queue = new MessageQueue();
$queue->enqueue(['type' => 'email', 'to' => 'user@example.com']);

// Consumer (separate process)
while (!$queue->isEmpty()) {
    $message = $queue->dequeue();
    processMessage($message);
}
```

**Benefits**:
- Asynchronous processing
- Fault tolerance
- Load leveling

## Common System Design Patterns

### 1. Microservices

Break monolith into small, independent services.

```
Monolith:             Microservices:
┌─────────────┐      ┌──────┐ ┌──────┐
│ Everything  │      │ Auth │ │ User │
│ in one app  │  →   └──────┘ └──────┘
└─────────────┘      ┌──────┐ ┌──────┐
                     │Order │ │ Pay  │
                     └──────┘ └──────┘
```

### 2. Content Delivery Network (CDN)

Cache static content geographically close to users.

```php
<?php

// Instead of:
<img src="https://myserver.com/images/logo.png">

// Use CDN:
<img src="https://cdn.example.com/images/logo.png">
```

### 3. Database Sharding

Split database across multiple servers.

```php
<?php

function getUserShard(int $userId): int {
    // Shard by user ID
    return $userId % 4; // 4 database shards
}

function getUser(int $userId): array {
    $shard = getUserShard($userId);
    $db = connectToShard($shard);
    return $db->query("SELECT * FROM users WHERE id = ?", [$userId]);
}
```

### 4. Rate Limiting

Prevent abuse, ensure fair usage.

```php
<?php

class RateLimiter {
    private array $requests = [];
    private int $limit;
    private int $window;

    public function __construct(int $limit = 100, int $window = 3600) {
        $this->limit = $limit;
        $this->window = $window;
    }

    public function isAllowed(string $clientId): bool {
        $now = time();

        // Remove old requests
        $this->requests[$clientId] = array_filter(
            $this->requests[$clientId] ?? [],
            fn($timestamp) => $now - $timestamp < $this->window
        );

        // Check limit
        if (count($this->requests[$clientId]) >= $this->limit) {
            return false; // Rate limit exceeded
        }

        $this->requests[$clientId][] = $now;
        return true;
    }
}
```

## CS Concepts at Scale

### Hash Tables → Consistent Hashing

Distribute data across servers evenly.

### Trees → B-Trees in Databases

Index data for fast queries.

### Graphs → Social Networks

Model relationships between users.

### Queues → Message Brokers

Handle asynchronous tasks (RabbitMQ, Kafka).

### Tries → Autocomplete

Fast prefix matching for search.

## Designing a URL Shortener

**Requirements**:
- Shorten URLs
- Redirect to original URL
- Handle millions of requests

**Design**:

```php
<?php

class URLShortener {
    private PDO $db;
    private Cache $cache;

    public function shorten(string $longUrl): string {
        // Generate short code (hash + collision handling)
        $shortCode = $this->generateShortCode($longUrl);

        // Store in database
        $this->db->prepare("INSERT INTO urls (short_code, long_url) VALUES (?, ?)")
                 ->execute([$shortCode, $longUrl]);

        return "https://short.url/$shortCode";
    }

    public function redirect(string $shortCode): string {
        // Check cache first (O(1))
        $longUrl = $this->cache->get($shortCode);
        if ($longUrl !== null) {
            return $longUrl;
        }

        // Query database (O(log n) with index)
        $stmt = $this->db->prepare("SELECT long_url FROM urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        $longUrl = $stmt->fetchColumn();

        // Cache result
        $this->cache->set($shortCode, $longUrl);

        return $longUrl;
    }

    private function generateShortCode(string $url): string {
        return substr(base64_encode(hash('sha256', $url, true)), 0, 7);
    }
}
```

**Key decisions**:
- Caching for performance
- Database indexing on short_code
- Hash function for collision handling
- Scalability via sharding

## Key Takeaways

- **System design** applies CS at scale
- **Load balancing** distributes traffic
- **Caching** reduces latency
- **Indexing** speeds up queries
- **Message queues** enable async processing
- **Trade-offs**: Consistency vs. availability, latency vs. throughput

## Exercises

1. **Design Instagram**: Photo upload, feed, followers.

2. **Design Notification Service**: Send millions of notifications.

3. **Design Chat Application**: Real-time messaging.

4. **Design E-commerce Checkout**: High availability, consistency.

## What's Next?

System design is about solving problems. Chapter 18 covers **Problem Solving Strategies**—systematic approaches to tackling any challenge.

---

**Further Reading**:
- [System Design Primer](https://github.com/donnemartin/system-design-primer)
- [Designing Data-Intensive Applications](https://dataintensive.net/)
