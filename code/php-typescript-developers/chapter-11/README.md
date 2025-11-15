# Chapter 11: Async in PHP - Promises vs Fibers

Code examples demonstrating asynchronous programming patterns in PHP.

## Prerequisites

- PHP 8.1+ (for Fibers support)
- Composer

## Installation

```bash
composer install
```

## Examples

### 1. Basic Fibers (`01-basic-fibers.php`)
Introduction to PHP Fibers for cooperative multitasking.

```bash
php 01-basic-fibers.php
```

### 2. Fiber Task Manager (`02-fiber-tasks.php`)
Practical fiber usage with a task manager class.

```bash
php 02-fiber-tasks.php
```

### 3. ReactPHP HTTP Server (`03-reactphp-server.php`)
Build an async HTTP server with ReactPHP.

```bash
php 03-reactphp-server.php
# Visit: http://127.0.0.1:8080
```

### 4. ReactPHP Promises (`04-promises.php`)
Promise patterns similar to JavaScript.

```bash
php 04-promises.php
```

### 5. Concurrent HTTP Requests (`05-guzzle-async.php`)
Make multiple HTTP requests concurrently with Guzzle.

```bash
php 05-guzzle-async.php
```

### 6. WebSocket Server (`06-websocket-server.php`)
Real-time WebSocket server with Ratchet.

```bash
php 06-websocket-server.php
# Connect with a WebSocket client on ws://localhost:8080
```

## Key Concepts

- **Fibers**: Cooperative multitasking (pause/resume execution)
- **ReactPHP**: Event loop for async I/O
- **Promises**: Async operation handling
- **Guzzle Async**: Concurrent HTTP requests
- **Ratchet**: WebSocket server implementation

## When to Use Async PHP

✅ **Good Use Cases:**
- WebSocket servers
- Concurrent API calls
- Long-running workers
- Real-time applications

❌ **Not Needed For:**
- Regular web requests
- CRUD applications
- Most Laravel apps (use queues instead)

## Resources

- [PHP Fibers RFC](https://wiki.php.net/rfc/fibers)
- [ReactPHP](https://reactphp.org/)
- [Guzzle Async](https://docs.guzzlephp.org/)
- [Ratchet WebSockets](http://socketo.me/)
