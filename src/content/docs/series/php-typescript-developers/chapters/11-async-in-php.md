---
title: "11: Async in PHP - Promises vs Fibers"
description: "Understand asynchronous programming in PHP. Learn about Fibers, ReactPHP, Amp, and how PHP handles concurrency differently from JavaScript."
sidebar:
  label: "11: Async in PHP - Promises vs Fibers"
  order: 11
  badge:
    text: Advanced
    variant: danger
---

# Async in PHP: Promises vs Fibers

## Overview

Coming from JavaScript's async/await, you might wonder: "How does PHP handle asynchronous operations?" The answer: **PHP traditionally doesn't**—it's synchronous by design. However, modern PHP offers Fibers (PHP 8.1+), event loops (ReactPHP, Amp), and async frameworks that bring async capabilities to PHP.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Understand PHP's synchronous execution model
- ✅ Use Fibers for cooperative multitasking
- ✅ Work with ReactPHP for event-driven programming
- ✅ Implement promise-like patterns
- ✅ Handle concurrent HTTP requests
- ✅ Know when async PHP makes sense
- ✅ Use async libraries effectively

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-11)**

This chapter includes comprehensive async examples:
- `01-basic-fibers.php` - Introduction to PHP Fibers
- `02-fiber-tasks.php` - Practical task management with Fibers
- `03-reactphp-server.php` - Async HTTP server with ReactPHP
- `04-promises.php` - Promise patterns in PHP
- `05-guzzle-async.php` - Concurrent HTTP requests with Guzzle
- `06-websocket-server.php` - Real-time WebSocket server

Run the examples:
```bash
cd code/php-typescript-developers/chapter-11
composer install
php 01-basic-fibers.php
```

## The Fundamental Difference

### JavaScript: Async by Default

```javascript
// JavaScript is single-threaded with event loop
console.log('1: Start');

setTimeout(() => {
  console.log('2: Async operation');
}, 0);

console.log('3: End');

// Output: 1, 3, 2 (async runs later)
```

**JavaScript event loop** allows non-blocking I/O operations.

### PHP: Synchronous by Default

```php
<?php
echo "1: Start\n";

sleep(1); // Blocks execution

echo "2: After sleep\n";
echo "3: End\n";

// Output: 1, 2, 3 (sequential, blocking)
```

**PHP blocks** on I/O operations by default. No event loop.

## Why PHP is Synchronous

### Request/Response Model

PHP was designed for web requests:

```
1. Request comes in
2. PHP executes script (blocking is fine)
3. Response sent back
4. Process ends
```

**Each request is independent.** Blocking doesn't matter because the process dies after response.

### Node.js Long-Running Process

```
1. Server starts
2. Event loop runs indefinitely
3. Handles multiple requests concurrently
4. Process stays alive
```

**Node.js must be non-blocking** because one blocked operation would freeze all requests.

## When You Need Async in PHP

### ✅ Use Cases for Async PHP

1. **Long-running processes** (workers, daemons)
2. **Concurrent HTTP requests** (calling multiple APIs)
3. **WebSocket servers** (real-time communication)
4. **Queue workers** (processing background jobs)
5. **Scraping/crawling** (many concurrent requests)

### ❌ You DON'T Need Async PHP For

1. **Regular web requests** (Laravel, Symfony handle this fine)
2. **Database queries** (connection pooling handles this)
3. **File operations** (usually fast enough)
4. **Most CRUD applications**

**Reality:** 95% of PHP applications don't need async programming.

## Fibers (PHP 8.1+)

Fibers provide **cooperative multitasking** (like coroutines):

### JavaScript Async/Await

```javascript
async function fetchUser(id) {
  const response = await fetch(`/api/users/${id}`);
  return response.json();
}

async function main() {
  const user = await fetchUser(1);
  console.log(user);
}
```

### PHP Fibers

```php
<?php
// Fibers allow pausing/resuming execution
$fiber = new Fiber(function(): void {
    echo "1: Fiber starts\n";
    Fiber::suspend(); // Pause here
    echo "3: Fiber resumes\n";
});

echo "0: Before fiber\n";
$fiber->start();
echo "2: Fiber suspended\n";
$fiber->resume();
echo "4: After fiber\n";

// Output: 0, 1, 2, 3, 4
```

**Fibers are low-level** - you typically don't use them directly. Libraries build on top of them.

### Practical Fiber Example

```php
<?php
class AsyncTask {
    private Fiber $fiber;

    public function __construct(callable $task) {
        $this->fiber = new Fiber($task);
    }

    public function run(): mixed {
        if (!$this->fiber->isStarted()) {
            return $this->fiber->start();
        }
        return $this->fiber->resume();
    }

    public function isFinished(): bool {
        return $this->fiber->isTerminated();
    }
}

// Simulate async operation
$task1 = new AsyncTask(function() {
    echo "Task 1: Starting\n";
    Fiber::suspend();
    echo "Task 1: Finishing\n";
    return "Result 1";
});

$task2 = new AsyncTask(function() {
    echo "Task 2: Starting\n";
    Fiber::suspend();
    echo "Task 2: Finishing\n";
    return "Result 2";
});

// Interleave execution
$task1->run();
$task2->run();
$task1->run();
$task2->run();

// Output:
// Task 1: Starting
// Task 2: Starting
// Task 1: Finishing
// Task 2: Finishing
```

## ReactPHP: Event Loop for PHP

ReactPHP brings Node.js-style async to PHP:

### Installation

```bash
composer require react/http react/promise
```

### JavaScript HTTP Server

```javascript
const http = require('http');

const server = http.createServer((req, res) => {
  res.writeHead(200);
  res.end('Hello World');
});

server.listen(3000);
console.log('Server running on port 3000');
```

### ReactPHP HTTP Server

```php
<?php
require 'vendor/autoload.php';

use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

$server = new HttpServer(function (ServerRequestInterface $request) {
    return new Response(200, ['Content-Type' => 'text/plain'], 'Hello World');
});

$socket = new React\Socket\SocketServer('127.0.0.1:8080');
$server->listen($socket);

echo "Server running on http://127.0.0.1:8080\n";
```

**Run with:**
```bash
php server.php
# Server stays running (like Node.js)
```

## Promises in PHP

### JavaScript Promises

```javascript
function fetchUser(id) {
  return new Promise((resolve, reject) => {
    fetch(`/api/users/${id}`)
      .then(response => response.json())
      .then(user => resolve(user))
      .catch(error => reject(error));
  });
}

fetchUser(1)
  .then(user => console.log(user))
  .catch(error => console.error(error));
```

### ReactPHP Promises

```php
<?php
use React\Promise\Promise;

function fetchUser(int $id): Promise {
    return new Promise(function ($resolve, $reject) use ($id) {
        // Simulate async operation
        $user = ['id' => $id, 'name' => 'Alice'];
        $resolve($user);
    });
}

fetchUser(1)
    ->then(function ($user) {
        echo "User: " . $user['name'] . "\n";
    })
    ->catch(function ($error) {
        echo "Error: {$error}\n";
    });
```

### Promise Chaining

**JavaScript:**
```javascript
fetchUser(1)
  .then(user => fetchPosts(user.id))
  .then(posts => console.log(posts))
  .catch(error => console.error(error));
```

**ReactPHP:**
```php
<?php
fetchUser(1)
    ->then(function ($user) {
        return fetchPosts($user['id']);
    })
    ->then(function ($posts) {
        echo "Posts: " . count($posts) . "\n";
    })
    ->catch(function ($error) {
        echo "Error: {$error}\n";
    });
```

## Concurrent HTTP Requests

### JavaScript (Promise.all)

```javascript
async function fetchMultipleUsers() {
  const promises = [
    fetch('/api/users/1').then(r => r.json()),
    fetch('/api/users/2').then(r => r.json()),
    fetch('/api/users/3').then(r => r.json()),
  ];

  const users = await Promise.all(promises);
  console.log(users);
}
```

### PHP with Guzzle (Concurrent Requests)

```php
<?php
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

$client = new Client();

// Create promises
$promises = [
    'user1' => $client->getAsync('https://api.example.com/users/1'),
    'user2' => $client->getAsync('https://api.example.com/users/2'),
    'user3' => $client->getAsync('https://api.example.com/users/3'),
];

// Wait for all to complete
$results = Promise\Utils::unwrap($promises);

foreach ($results as $key => $response) {
    echo "{$key}: " . $response->getBody() . "\n";
}
```

**Performance:**
- **Sequential:** 3 × 1s = 3s total
- **Concurrent:** ~1s total (all at once)

## Amp: Alternative Async Library

Amp is another async library (competitor to ReactPHP):

### Installation

```bash
composer require amphp/http-client amphp/parallel
```

### Amp HTTP Requests

```php
<?php
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;

require 'vendor/autoload.php';

// Amp uses coroutines (async functions)
$client = HttpClientBuilder::buildDefault();

$request = new Request('https://api.example.com/users/1');
$response = $client->request($request);

$body = $response->getBody()->buffer();
echo $body;
```

### Concurrent Requests with Amp

```php
<?php
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use function Amp\Promise\all;

$client = HttpClientBuilder::buildDefault();

$promises = [
    $client->request(new Request('https://api.example.com/users/1')),
    $client->request(new Request('https://api.example.com/users/2')),
    $client->request(new Request('https://api.example.com/users/3')),
];

$responses = all($promises);

foreach ($responses as $response) {
    echo $response->getBody()->buffer() . "\n";
}
```

## Async Frameworks

### Swoole (High-Performance)

Swoole is a PHP extension that provides async capabilities:

```php
<?php
$server = new Swoole\HTTP\Server("127.0.0.1", 9501);

$server->on("start", function ($server) {
    echo "Swoole HTTP server started at http://127.0.0.1:9501\n";
});

$server->on("request", function ($request, $response) {
    $response->header("Content-Type", "text/plain");
    $response->end("Hello World\n");
});

$server->start();
```

**Performance:** 10-100x faster than traditional PHP-FPM.

### RoadRunner (Go-based PHP Server)

```yaml
# .rr.yaml
server:
  command: "php worker.php"

http:
  address: 0.0.0.0:8080
  workers:
    num_workers: 4
```

```php
<?php
// worker.php
use Spiral\RoadRunner;

require 'vendor/autoload.php';

$worker = RoadRunner\Worker::create();
$psr7 = new RoadRunner\Http\PSR7Worker($worker);

while ($req = $psr7->waitRequest()) {
    $psr7->respond(new Response(200, [], 'Hello World'));
}
```

## Practical Async Patterns

### Pattern 1: Concurrent API Calls

**Problem:** Fetch data from 3 APIs sequentially (slow).

**Solution:** Use Guzzle async requests:

```php
<?php
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

function fetchMultipleAPIs(): array {
    $client = new Client();

    $promises = [
        'weather' => $client->getAsync('https://api.weather.com/current'),
        'news' => $client->getAsync('https://api.news.com/latest'),
        'stocks' => $client->getAsync('https://api.stocks.com/prices'),
    ];

    $results = Promise\Utils::settle($promises)->wait();

    $data = [];
    foreach ($results as $key => $result) {
        if ($result['state'] === 'fulfilled') {
            $data[$key] = json_decode($result['value']->getBody(), true);
        } else {
            $data[$key] = null; // Handle error
        }
    }

    return $data;
}

$data = fetchMultipleAPIs();
// All API calls made concurrently!
```

### Pattern 2: Background Job Processing

```php
<?php
// Traditional (blocking)
function processOrder($order) {
    sendEmail($order);        // Blocks for 2s
    updateInventory($order);  // Blocks for 1s
    notifyShipping($order);   // Blocks for 1s
    // Total: 4s
}

// Async (non-blocking)
use React\Promise;

function processOrderAsync($order) {
    return Promise\all([
        sendEmailAsync($order),
        updateInventoryAsync($order),
        notifyShippingAsync($order),
    ]);
    // Total: ~2s (longest operation)
}
```

### Pattern 3: WebSocket Server

**JavaScript (ws library):**
```javascript
const WebSocket = require('ws');
const wss = new WebSocket.Server({ port: 8080 });

wss.on('connection', ws => {
  ws.on('message', message => {
    ws.send(`Echo: ${message}`);
  });
});
```

**PHP (Ratchet):**
```php
<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    public function onMessage(ConnectionInterface $from, $msg) {
        $from->send("Echo: {$msg}");
    }

    // Other interface methods...
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
```

## When to Use Async PHP

### ✅ Good Use Cases

**1. Microservices / API Gateway**
```php
// Aggregate data from multiple services
$data = Promise\all([
    $userService->getUser($id),
    $orderService->getOrders($id),
    $paymentService->getPayments($id),
]);
```

**2. Real-Time Applications**
- Chat servers
- Live dashboards
- WebSocket connections

**3. Background Workers**
- Queue processing
- Scheduled tasks
- Data synchronization

**4. High-Concurrency Scenarios**
- Web scraping
- Bulk API calls
- Load testing tools

### ❌ Bad Use Cases

**1. Traditional Web Apps** - Use Laravel/Symfony (they handle concurrency at the process level)

**2. Simple CRUD** - Async adds complexity without benefit

**3. Database-Heavy Apps** - Connection pooling is sufficient

## Laravel Queue (Alternative to Async)

Instead of async in-process, Laravel uses **job queues**:

```php
<?php
// Dispatch job to queue (non-blocking)
dispatch(new SendEmailJob($user));

// Job runs asynchronously in background worker
class SendEmailJob implements ShouldQueue {
    public function handle() {
        Mail::to($this->user)->send(new WelcomeEmail());
    }
}
```

**Start worker:**
```bash
php artisan queue:work
```

**Advantages:**
- Simpler than async code
- Failed jobs can retry
- Easy to scale (add more workers)
- Works with traditional PHP

## Comparison Summary

| Feature | JavaScript | PHP Traditional | PHP Async (ReactPHP/Amp) |
|---------|-----------|-----------------|--------------------------|
| **Event Loop** | Built-in | None | Library-provided |
| **Async/Await** | Native | N/A | Fibers + libraries |
| **Promises** | Native | Library | ReactPHP/Amp |
| **Non-blocking I/O** | Default | No | With libraries |
| **Long-running** | Yes | No (per-request) | Yes (with async libs) |
| **Complexity** | Medium | Low | High |
| **Use Cases** | All apps | Web requests | Specific scenarios |

## Key Takeaways

1. **PHP is synchronous by default** - This is fine for 95% of web applications
2. **Fibers (PHP 8.1+)** enable cooperative multitasking (low-level building block)
3. **ReactPHP/Amp** bring Node.js-style async event loops to PHP
4. **Guzzle async** handles concurrent HTTP requests easily with promises
5. **Most PHP apps don't need async** - Laravel queues are often sufficient alternative
6. **Async PHP is complex** - Only use for WebSockets, high-concurrency, or microservices
7. **Swoole/RoadRunner** offer high-performance alternatives (10-100x faster than PHP-FPM)
8. **No automatic async** like JavaScript - must explicitly use async libraries
9. **Use Laravel queues** for background jobs instead of in-process async
10. **ReactPHP promises** work like JavaScript promises but with PHP syntax
11. **Async PHP best for**: WebSocket servers, scraping, concurrent API calls, long-running daemons
12. **Traditional PHP-FPM handles** concurrency at process level, not async level

## Practical Advice

### For TypeScript Developers

**Coming from Node.js:**
- Don't expect everything to be async
- Traditional PHP-FPM is fine for web apps
- Use queues instead of async for background work
- ReactPHP feels like Node, but ecosystem is smaller

### When to Choose Async PHP

1. **Building WebSocket server?** → Use ReactPHP/Ratchet
2. **Need high concurrency?** → Consider Swoole
3. **Making many HTTP requests?** → Use Guzzle async
4. **Building traditional web app?** → Stick with Laravel/Symfony

## Next Steps

Now that you understand async patterns, let's build REST APIs with PHP.

**Next Chapter:** [12: REST APIs: Express.js vs PHP Native](/series/php-typescript-developers/chapters/12-rest-apis)

## Resources

- [PHP Fibers RFC](https://wiki.php.net/rfc/fibers)
- [ReactPHP](https://reactphp.org/)
- [Amp](https://amphp.org/)
- [Swoole](https://www.swoole.co.uk/)
- [RoadRunner](https://roadrunner.dev/)
- [Guzzle Async](https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests)
- [Ratchet WebSockets](http://socketo.me/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
