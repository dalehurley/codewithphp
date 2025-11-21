# Chapter 31: Concurrent Algorithms - Code Samples

This directory contains comprehensive, runnable PHP code examples for Chapter 31: Concurrent Algorithms from the PHP Algorithms series.

## Overview

These examples demonstrate concurrent and parallel programming patterns in PHP, showing how to handle I/O-bound and CPU-intensive tasks efficiently.

## Code Samples

### 1. ReactPHP Async HTTP (`01-reactphp-async-http.php`)

Demonstrates asynchronous HTTP requests using ReactPHP's event loop and promises.

**Key Concepts:**
- Async/await patterns with ReactPHP
- Promise-based concurrent requests
- Rate limiting for concurrent operations
- Retry logic with exponential backoff

**Installation:**
```bash
composer require react/http react/promise
```

**Usage:**
```bash
php 01-reactphp-async-http.php
```

**Features:**
- Concurrent API requests
- Rate-limited fetching
- Retry mechanisms
- Error handling

---

### 2. Swoole Coroutines (`02-swoole-coroutines.php`)

High-performance concurrent operations using Swoole's coroutine system.

**Key Concepts:**
- Coroutine-based concurrency
- Producer-consumer pattern
- Concurrent database queries
- Atomic operations

**Installation:**
```bash
pecl install swoole
```

**Usage:**
```bash
php 02-swoole-coroutines.php
```

**Features:**
- Lightweight coroutines
- Channel-based communication
- Atomic counters
- Concurrent HTTP requests

---

### 3. Worker Pool Pattern (`03-worker-pool.php`)

Implements worker pool pattern for distributing tasks across multiple workers.

**Key Concepts:**
- Task distribution across workers
- Priority-based task processing
- Parallel data processing (map/reduce)
- Batch processing

**Usage:**
```bash
php 03-worker-pool.php
```

**Features:**
- Basic worker pool
- Priority task queues
- Parallel map/filter/reduce
- Image processing simulation

**Note:** This is a simulated implementation. For production parallel execution, use ext-parallel or Swoole.

---

### 4. Circuit Breaker Pattern (`04-circuit-breaker.php`)

Implements circuit breaker pattern for handling failures in distributed systems.

**Key Concepts:**
- Circuit breaker states (CLOSED, OPEN, HALF_OPEN)
- Failure threshold management
- Automatic recovery testing
- Multi-endpoint management

**Usage:**
```bash
php 04-circuit-breaker.php
```

**Features:**
- Automatic failure detection
- Graceful degradation
- Fallback mechanisms
- Service health monitoring

---

### 5. Concurrent Data Structures (`05-concurrent-data-structures.php`)

Thread-safe and lock-free data structures for concurrent programming.

**Key Concepts:**
- Atomic operations
- Lock-free queues
- Concurrent hash maps
- Read-write locks
- Blocking queues

**Usage:**
```bash
php 05-concurrent-data-structures.php
```

**Features:**
- Concurrent counter
- Lock-free queue
- Thread-safe hash map
- Blocking queue with capacity
- Read-write lock

---

## Requirements

- PHP 8.0 or higher
- Composer (for package management)
- Optional: Swoole extension for coroutines
- Optional: ext-parallel for true parallelism

## Installation

```bash
# Install Composer dependencies
composer require react/http react/promise

# Install Swoole (optional)
pecl install swoole

# Enable extension in php.ini
echo "extension=swoole.so" >> php.ini
```

## Running All Examples

```bash
# ReactPHP examples
php 01-reactphp-async-http.php

# Swoole examples (requires Swoole extension)
php 02-swoole-coroutines.php

# Pure PHP examples
php 03-worker-pool.php
php 04-circuit-breaker.php
php 05-concurrent-data-structures.php
```

## Key Takeaways

1. **ReactPHP**: Best for I/O-bound asynchronous operations
2. **Swoole**: High-performance coroutines for both I/O and CPU tasks
3. **Worker Pools**: Distribute CPU-intensive work across processes
4. **Circuit Breakers**: Prevent cascading failures in distributed systems
5. **Concurrent Data Structures**: Safe shared state in concurrent environments

## Performance Benefits

- **Sequential**: 10 URLs × 2s = 20 seconds
- **Concurrent**: 10 URLs in ~2 seconds (10x speedup)
- **Worker Pool**: CPU tasks distributed across cores
- **Circuit Breaker**: Fast-fail for unhealthy services

## Production Considerations

- Always use proper error handling
- Implement rate limiting to avoid overwhelming services
- Monitor resource usage (memory, connections)
- Use circuit breakers for external dependencies
- Test concurrent code thoroughly under load

## Related Chapters

- **Chapter 27**: Caching & Memoization
- **Chapter 29**: Performance Optimization
- **Chapter 32**: Probabilistic Algorithms

## Further Reading

- [ReactPHP Documentation](https://reactphp.org/)
- [Swoole Documentation](https://www.swoole.co.uk/)
- [PHP ext-parallel](https://www.php.net/manual/en/book.parallel.php)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
