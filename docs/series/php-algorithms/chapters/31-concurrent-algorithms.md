---
title: "31: Concurrent Algorithms"
description: "Master concurrent and parallel algorithms in PHP using ReactPHP, Swoole, and ext-parallel for high-performance applications handling I/O-bound and CPU-intensive tasks"
series: "php-algorithms"
chapter: 31
order: 31
difficulty: "Advanced"
prerequisites: []
estimatedTime: "PT70M"
teaches:
  - 'Master async/await patterns with ReactPHP for non-blocking I/O operations'
  - 'Implement parallel processing with ext-parallel for CPU-intensive tasks'
  - 'Build high-performance concurrent applications with Swoole coroutines'
  - 'Design thread-safe concurrent data structures and avoid race conditions'
  - 'Detect, prevent, and resolve deadlocks in concurrent systems'
---
![Concurrent Algorithms](/images/php-algorithms/chapter-31-concurrent-algorithms-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 31</span>
</div>

# 31: Concurrent Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Break free from PHP's traditional synchronous execution model and unlock massive performance gains through concurrency. This chapter teaches you how to handle multiple operations simultaneously, dramatically reducing wait times for I/O and maximizing CPU utilization.

Concurrency allows multiple tasks to make progress simultaneously, dramatically improving performance for I/O-bound operations and CPU-intensive tasks. You'll discover how to transform sequential code that waits idly into concurrent code that maximizes resource utilization. We'll explore async/await patterns with ReactPHP, parallel processing with ext-parallel, and high-performance concurrent applications with Swoole coroutines.

By the end of this chapter, you'll understand when and how to apply concurrent algorithms to solve real-world problems like web scraping, data processing, and high-performance web applications. You'll learn to design thread-safe concurrent data structures, avoid race conditions, and implement production-ready concurrent systems.

## Prerequisites

Before starting this chapter, you should have:

- **Strong PHP fundamentals** - Comfortable with classes, closures, and error handling
- **Data structures knowledge** - Understanding of queues, channels, and synchronization primitives
- **Asynchronous concepts** - Basic understanding of non-blocking operations and callbacks
- **Multi-threading awareness** - Familiarity with concurrency challenges and patterns

**Estimated Time**: ~70 minutes

## What You'll Build

By the end of this chapter, you will have created:

- Async HTTP client with ReactPHP for concurrent API requests
- Parallel data processor using ext-parallel for CPU-intensive tasks
- Concurrent web scraper with rate limiting and error handling
- Thread-safe concurrent data structures (queues, counters, hash maps)
- Production-ready concurrent cache warmer with Swoole coroutines
- Circuit breaker pattern implementation for resilient async operations
- Worker pool pattern for parallel image processing
- Deadlock detection and prevention mechanisms
- Stress testing and property-based testing frameworks for concurrent code
- Complete concurrent application examples with monitoring and health checks

## Objectives

- Master async/await patterns with ReactPHP for non-blocking I/O operations
- Implement parallel processing with ext-parallel for CPU-intensive tasks
- Build high-performance concurrent applications with Swoole coroutines
- Design thread-safe concurrent data structures and avoid race conditions
- Detect, prevent, and resolve deadlocks in concurrent systems
- Test concurrent code effectively using stress testing and property-based testing
- Apply concurrency patterns to real-world problems like web scraping and data processing
- Implement production deployment strategies with monitoring and graceful shutdown

## Understanding Concurrency in PHP (~5 min)

### Traditional PHP vs. Concurrent PHP

```php
# filename: traditional-synchronous.php
<?php

declare(strict_types=1);

// Traditional synchronous approach
function fetchMultipleUrls(array $urls): array {
    $results = [];
    foreach ($urls as $url) {
        $results[] = file_get_contents($url);  // Blocks until complete
    }
    return $results;
}

// Time: 5 URLs × 2 seconds each = 10 seconds total
```

**Limitations**:
- Sequential execution
- Wasted time waiting for I/O
- Poor resource utilization
- Long response times

### Concurrency Solutions

PHP offers several approaches:

1. **Asynchronous I/O** (ReactPHP, Amp)
2. **Multi-processing** (pcntl, parallel extension)
3. **Swoole/OpenSwoole** (coroutines)
4. **Parallel Extension** (threading)

## Async/Await with ReactPHP (~15 min)

### Setting Up ReactPHP

```bash
composer require react/http react/promise
```

### Basic Promise Pattern

```php
# filename: async-http.php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use React\Promise\Promise;

class AsyncHttp {
    public function get(string $url): Promise {
        return new Promise(function ($resolve, $reject) use ($url) {
            $context = stream_context_create([
                'http' => ['timeout' => 10]
            ]);

            Loop::addTimer(0, function () use ($url, $context, $resolve, $reject) {
                try {
                    $result = @file_get_contents($url, false, $context);
                    if ($result === false) {
                        $reject(new Exception("Failed to fetch: $url"));
                    } else {
                        $resolve($result);
                    }
                } catch (Exception $e) {
                    $reject($e);
                }
            });
        });
    }
}

// Usage
$http = new AsyncHttp();

$http->get('https://api.example.com/users')
    ->then(function ($data) {
        echo "Got data: " . strlen($data) . " bytes\n";
        return json_decode($data, true);
    })
    ->then(function ($users) {
        echo "Processed " . count($users) . " users\n";
    })
    ->otherwise(function (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    });

Loop::run();
```

**Time Complexity**: O(1) setup, concurrent execution
**Space Complexity**: O(n) where n is number of pending promises

### Concurrent API Requests

```php
# filename: concurrent-api-client.php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use React\Http\Browser;
use function React\Promise\all;

class ConcurrentApiClient {
    private Browser $browser;

    public function __construct() {
        $this->browser = new Browser();
    }

    public function fetchMultiple(array $urls): Promise {
        $promises = [];

        foreach ($urls as $url) {
            $promises[] = $this->browser->get($url)
                ->then(function ($response) {
                    return (string) $response->getBody();
                });
        }

        // Wait for all requests to complete
        return all($promises);
    }

    public function fetchWithRateLimit(array $urls, int $concurrency = 5): Promise {
        $results = [];
        $queue = $urls;
        $active = 0;

        return new Promise(function ($resolve) use (&$queue, &$active, &$results, $concurrency) {
            $process = function () use (&$queue, &$active, &$results, &$process, $concurrency, $resolve) {
                while ($active < $concurrency && count($queue) > 0) {
                    $url = array_shift($queue);
                    $active++;

                    $this->browser->get($url)
                        ->then(function ($response) use (&$active, &$results, &$process, $url, $resolve) {
                            $results[$url] = (string) $response->getBody();
                            $active--;

                            if (count($results) === count($urls) && $active === 0) {
                                $resolve($results);
                            } else {
                                $process();
                            }
                        });
                }
            };

            $process();
        });
    }
}

// Usage
$client = new ConcurrentApiClient();

$urls = [
    'https://api.example.com/users',
    'https://api.example.com/posts',
    'https://api.example.com/comments',
    'https://api.example.com/tags',
];

$client->fetchMultiple($urls)
    ->then(function ($results) {
        echo "Fetched " . count($results) . " endpoints concurrently\n";
        foreach ($results as $i => $data) {
            echo "Result $i: " . strlen($data) . " bytes\n";
        }
    });

Loop::run();
```

**Performance**: 10 URLs × 2 seconds → ~2 seconds total (vs. 20 seconds sequential)

### Advanced ReactPHP: Database Operations

```php
# filename: async-database-client.php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use React\MySQL\Factory;
use React\MySQL\QueryResult;
use function React\Promise\all;

class AsyncDatabaseClient {
    private $factory;
    private $connection;

    public function __construct(string $uri = 'mysql://user:pass@localhost/dbname') {
        $this->factory = new Factory();
        $this->connection = $this->factory->createLazyConnection($uri);
    }

    public function queryMultiple(array $queries): Promise {
        $promises = [];

        foreach ($queries as $key => $sql) {
            $promises[$key] = $this->connection->query($sql)
                ->then(function (QueryResult $result) use ($sql) {
                    return [
                        'sql' => $sql,
                        'rows' => $result->resultRows,
                        'affected' => $result->affected
                    ];
                })
                ->otherwise(function (Exception $e) use ($sql) {
                    return [
                        'sql' => $sql,
                        'error' => $e->getMessage()
                    ];
                });
        }

        return all($promises);
    }

    public function batchInsert(string $table, array $records): Promise {
        $promises = [];

        foreach ($records as $record) {
            $columns = implode(', ', array_keys($record));
            $placeholders = implode(', ', array_fill(0, count($record), '?'));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

            $promises[] = $this->connection->query($sql, array_values($record))
                ->then(function (QueryResult $result) use ($record) {
                    return [
                        'success' => true,
                        'id' => $result->insertId,
                        'data' => $record
                    ];
                })
                ->otherwise(function (Exception $e) use ($record) {
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'data' => $record
                    ];
                });
        }

        return all($promises);
    }

    public function close(): void {
        $this->connection->quit();
    }
}

// Usage
$db = new AsyncDatabaseClient('mysql://user:pass@localhost/mydb');

$queries = [
    'users' => 'SELECT * FROM users LIMIT 100',
    'orders' => 'SELECT * FROM orders WHERE status = "pending"',
    'products' => 'SELECT * FROM products WHERE stock > 0'
];

$db->queryMultiple($queries)
    ->then(function ($results) {
        foreach ($results as $key => $result) {
            if (isset($result['error'])) {
                echo "Query '$key' failed: {$result['error']}\n";
            } else {
                echo "Query '$key': " . count($result['rows']) . " rows\n";
            }
        }
    })
    ->otherwise(function (Exception $e) {
        echo "Batch query failed: " . $e->getMessage() . "\n";
    })
    ->always(function () use ($db) {
        $db->close();
    });

Loop::run();
```

### Error Handling Patterns in ReactPHP

```php
# filename: resilient-async-client.php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use React\Promise\Promise;
use function React\Promise\all;

class ResilientAsyncClient {
    private Browser $browser;
    private int $maxRetries;
    private int $retryDelay;

    public function __construct(int $maxRetries = 3, int $retryDelay = 1) {
        $this->browser = new Browser();
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;
    }

    public function getWithRetry(string $url, int $attempt = 1): Promise {
        return $this->browser->get($url)
            ->then(function ($response) {
                if ($response->getStatusCode() >= 500) {
                    throw new Exception("Server error: " . $response->getStatusCode());
                }
                return (string) $response->getBody();
            })
            ->otherwise(function (Exception $e) use ($url, $attempt) {
                if ($attempt < $this->maxRetries) {
                    echo "Attempt $attempt failed for $url, retrying...\n";

                    return new Promise(function ($resolve, $reject) use ($url, $attempt) {
                        Loop::addTimer($this->retryDelay, function () use ($url, $attempt, $resolve, $reject) {
                            $this->getWithRetry($url, $attempt + 1)
                                ->then($resolve, $reject);
                        });
                    });
                }

                throw $e;  // Max retries exceeded
            });
    }

    public function fetchWithFallback(string $primaryUrl, string $fallbackUrl): Promise {
        return $this->getWithRetry($primaryUrl)
            ->otherwise(function (Exception $e) use ($fallbackUrl) {
                echo "Primary URL failed, trying fallback...\n";
                return $this->getWithRetry($fallbackUrl);
            });
    }

    public function fetchWithTimeout(string $url, int $timeout = 10): Promise {
        $timeoutPromise = new Promise(function ($resolve, $reject) use ($timeout) {
            Loop::addTimer($timeout, function () use ($reject) {
                $reject(new Exception("Request timed out after $timeout seconds"));
            });
        });

        $requestPromise = $this->getWithRetry($url);

        return Promise::race([$requestPromise, $timeoutPromise]);
    }
}

// Usage
$client = new ResilientAsyncClient(maxRetries: 3, retryDelay: 2);

$client->getWithRetry('https://api.example.com/data')
    ->then(function ($data) {
        echo "Success: " . strlen($data) . " bytes\n";
    })
    ->otherwise(function (Exception $e) {
        echo "All retries failed: " . $e->getMessage() . "\n";
    });

// Fallback pattern
$client->fetchWithFallback(
    'https://primary-api.example.com/data',
    'https://backup-api.example.com/data'
)
    ->then(function ($data) {
        echo "Data retrieved: " . strlen($data) . " bytes\n";
    });

// Timeout pattern
$client->fetchWithTimeout('https://slow-api.example.com/data', 5)
    ->then(function ($data) {
        echo "Received within timeout\n";
    })
    ->otherwise(function (Exception $e) {
        echo "Request failed or timed out: " . $e->getMessage() . "\n";
    });

Loop::run();
```

### Circuit Breaker Pattern

```php
# filename: circuit-breaker.php
<?php

declare(strict_types=1);

class CircuitBreaker {
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';

    private string $state = self::STATE_CLOSED;
    private int $failureCount = 0;
    private int $failureThreshold;
    private int $timeout;
    private ?int $lastFailureTime = null;

    public function __construct(int $failureThreshold = 5, int $timeout = 60) {
        $this->failureThreshold = $failureThreshold;
        $this->timeout = $timeout;
    }

    public function call(callable $operation): Promise {
        if ($this->state === self::STATE_OPEN) {
            if (time() - $this->lastFailureTime >= $this->timeout) {
                // Try to recover
                $this->state = self::STATE_HALF_OPEN;
                echo "Circuit breaker: HALF_OPEN (testing recovery)\n";
            } else {
                return Promise::reject(new Exception("Circuit breaker is OPEN"));
            }
        }

        return $operation()
            ->then(function ($result) {
                $this->onSuccess();
                return $result;
            })
            ->otherwise(function (Exception $e) {
                $this->onFailure();
                throw $e;
            });
    }

    private function onSuccess(): void {
        $this->failureCount = 0;

        if ($this->state === self::STATE_HALF_OPEN) {
            $this->state = self::STATE_CLOSED;
            echo "Circuit breaker: CLOSED (recovered)\n";
        }
    }

    private function onFailure(): void {
        $this->failureCount++;
        $this->lastFailureTime = time();

        if ($this->failureCount >= $this->failureThreshold) {
            $this->state = self::STATE_OPEN;
            echo "Circuit breaker: OPEN (too many failures)\n";
        }
    }

    public function getState(): string {
        return $this->state;
    }
}

// Usage
$breaker = new CircuitBreaker(failureThreshold: 3, timeout: 10);
$client = new Browser();

for ($i = 0; $i < 10; $i++) {
    $breaker->call(function () use ($client) {
        return $client->get('https://flaky-api.example.com/data')
            ->then(function ($response) {
                return (string) $response->getBody();
            });
    })
        ->then(function ($data) use ($i) {
            echo "Request $i succeeded\n";
        })
        ->otherwise(function (Exception $e) use ($i) {
            echo "Request $i failed: {$e->getMessage()}\n";
        });
}

Loop::run();
```

## Parallel Processing with ext-parallel (~10 min)

### Installing Parallel Extension

```bash
pecl install parallel
```

### Basic Parallel Execution

```php
# filename: parallel-processor.php
<?php

declare(strict_types=1);

use parallel\{Runtime, Channel, Events};

class ParallelProcessor {
    private int $workers;

    public function __construct(int $workers = 4) {
        $this->workers = $workers;
    }

    public function map(array $items, callable $callback): array {
        $chunks = array_chunk($items, ceil(count($items) / $this->workers));
        $runtimes = [];
        $futures = [];

        // Spawn workers
        foreach ($chunks as $chunk) {
            $runtime = new Runtime();
            $runtimes[] = $runtime;

            $futures[] = $runtime->run(function ($data, $callback) {
                $results = [];
                foreach ($data as $item) {
                    $results[] = $callback($item);
                }
                return $results;
            }, [$chunk, $callback]);
        }

        // Collect results
        $results = [];
        foreach ($futures as $future) {
            $results = array_merge($results, $future->value());
        }

        // Clean up
        foreach ($runtimes as $runtime) {
            $runtime->close();
        }

        return $results;
    }

    public function reduce(array $items, callable $callback, $initial = null): mixed {
        $chunks = array_chunk($items, ceil(count($items) / $this->workers));
        $partialResults = $this->map($chunks, function ($chunk) use ($callback, $initial) {
            return array_reduce($chunk, $callback, $initial);
        });

        return array_reduce($partialResults, $callback, $initial);
    }
}

// Usage: Process large dataset in parallel
$processor = new ParallelProcessor(4);

$numbers = range(1, 1000000);

// Parallel map
$squared = $processor->map($numbers, fn($n) => $n * $n);

// Parallel reduce
$sum = $processor->reduce($numbers, fn($carry, $n) => $carry + $n, 0);

echo "Sum: $sum\n";
```

**Time Complexity**: O(n/p) where p is number of workers
**Space Complexity**: O(n) for data + O(p) for worker overhead

### Worker Pool Pattern

```php
# filename: worker-pool.php
<?php

declare(strict_types=1);

class WorkerPool {
    private array $workers = [];
    private Channel $tasks;
    private Channel $results;
    private int $workerCount;

    public function __construct(int $workerCount = 4) {
        $this->workerCount = $workerCount;
        $this->tasks = new Channel(Channel::Infinite);
        $this->results = new Channel(Channel::Infinite);
    }

    public function start(callable $worker): void {
        for ($i = 0; $i < $this->workerCount; $i++) {
            $runtime = new Runtime();
            $this->workers[] = $runtime;

            $runtime->run(function (Channel $tasks, Channel $results, callable $worker) {
                while (true) {
                    $task = $tasks->recv();

                    if ($task === null) {
                        break;  // Shutdown signal
                    }

                    try {
                        $result = $worker($task);
                        $results->send([
                            'success' => true,
                            'data' => $result,
                            'task' => $task
                        ]);
                    } catch (Throwable $e) {
                        $results->send([
                            'success' => false,
                            'error' => $e->getMessage(),
                            'task' => $task
                        ]);
                    }
                }
            }, [$this->tasks, $this->results, $worker]);
        }
    }

    public function submit($task): void {
        $this->tasks->send($task);
    }

    public function getResult(): ?array {
        return $this->results->recv();
    }

    public function shutdown(): void {
        // Send shutdown signals
        for ($i = 0; $i < $this->workerCount; $i++) {
            $this->tasks->send(null);
        }

        // Close workers
        foreach ($this->workers as $worker) {
            $worker->close();
        }

        $this->tasks->close();
        $this->results->close();
    }
}

// Usage: Image processing
$pool = new WorkerPool(4);

$pool->start(function ($imageFile) {
    // Simulate image processing
    $img = imagecreatefromjpeg($imageFile);
    $resized = imagescale($img, 800, 600);

    $outputFile = str_replace('.jpg', '_thumb.jpg', $imageFile);
    imagejpeg($resized, $outputFile, 85);

    imagedestroy($img);
    imagedestroy($resized);

    return $outputFile;
});

// Submit tasks
$images = glob('/path/to/images/*.jpg');
foreach ($images as $image) {
    $pool->submit($image);
}

// Collect results
$processed = 0;
while ($processed < count($images)) {
    $result = $pool->getResult();

    if ($result['success']) {
        echo "Processed: {$result['data']}\n";
    } else {
        echo "Error: {$result['error']}\n";
    }

    $processed++;
}

$pool->shutdown();
```

## Swoole Coroutines (~10 min)

### Setting Up Swoole

```bash
pecl install swoole
```

### Coroutine-Based Concurrency

```php
# filename: swoole-async-client.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;

class SwooleAsyncClient {
    public function fetchMultiple(array $urls): array {
        $results = [];

        // Create a coroutine for each URL
        foreach ($urls as $url) {
            Coroutine::create(function () use ($url, &$results) {
                $parsed = parse_url($url);
                $client = new Client($parsed['host'], $parsed['scheme'] === 'https' ? 443 : 80, $parsed['scheme'] === 'https');

                $client->setHeaders([
                    'User-Agent' => 'Swoole HTTP Client',
                ]);

                $client->get($parsed['path'] ?? '/');

                $results[$url] = $client->body;
                $client->close();
            });
        }

        return $results;
    }

    public function parallelDatabaseQueries(array $queries): array {
        $results = [];

        foreach ($queries as $key => $sql) {
            Coroutine::create(function () use ($sql, $key, &$results) {
                $db = new Swoole\Coroutine\MySQL();
                $db->connect([
                    'host' => '127.0.0.1',
                    'user' => 'root',
                    'password' => '',
                    'database' => 'test',
                ]);

                $results[$key] = $db->query($sql);
                $db->close();
            });
        }

        return $results;
    }
}

// Usage
Coroutine::run(function () {
    $client = new SwooleAsyncClient();

    $urls = [
        'https://api.example.com/users',
        'https://api.example.com/posts',
        'https://api.example.com/comments',
    ];

    $start = microtime(true);
    $results = $client->fetchMultiple($urls);
    $elapsed = microtime(true) - $start;

    echo "Fetched " . count($results) . " URLs in {$elapsed}s\n";
});
```

### Swoole Channel for Communication

```php
# filename: producer-consumer.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class ProducerConsumer {
    private Channel $channel;
    private int $consumers;

    public function __construct(int $capacity = 100, int $consumers = 4) {
        $this->channel = new Channel($capacity);
        $this->consumers = $consumers;
    }

    public function start(array $items, callable $processor): array {
        $results = [];

        // Producer coroutine
        Coroutine::create(function () use ($items) {
            foreach ($items as $item) {
                $this->channel->push($item);
            }

            // Send stop signals
            for ($i = 0; $i < $this->consumers; $i++) {
                $this->channel->push(null);
            }
        });

        // Consumer coroutines
        for ($i = 0; $i < $this->consumers; $i++) {
            Coroutine::create(function () use ($processor, &$results) {
                while (true) {
                    $item = $this->channel->pop();

                    if ($item === null) {
                        break;  // Stop signal
                    }

                    $result = $processor($item);
                    $results[] = $result;
                }
            });
        }

        return $results;
    }
}

// Usage
Coroutine::run(function () {
    $pc = new ProducerConsumer(100, 4);

    $items = range(1, 1000);
    $results = $pc->start($items, function ($n) {
        Coroutine::sleep(0.1);  // Simulate work
        return $n * 2;
    });

    echo "Processed " . count($results) . " items\n";
});
```

## Concurrent Data Structures (~5 min)

### Thread-Safe Queue

```php
# filename: concurrent-queue.php
<?php

declare(strict_types=1);

use Swoole\Coroutine\Channel;

class ConcurrentQueue {
    private Channel $channel;

    public function __construct(int $capacity = Channel::Infinite) {
        $this->channel = new Channel($capacity);
    }

    public function enqueue($item): bool {
        return $this->channel->push($item);
    }

    public function dequeue() {
        return $this->channel->pop();
    }

    public function isEmpty(): bool {
        return $this->channel->isEmpty();
    }

    public function length(): int {
        return $this->channel->length();
    }
}
```

### Lock-Free Counter

```php
# filename: concurrent-counter.php
<?php

declare(strict_types=1);

use Swoole\Atomic;

class ConcurrentCounter {
    private Atomic $counter;

    public function __construct(int $initial = 0) {
        $this->counter = new Atomic($initial);
    }

    public function increment(): int {
        return $this->counter->add(1);
    }

    public function decrement(): int {
        return $this->counter->sub(1);
    }

    public function get(): int {
        return $this->counter->get();
    }

    public function compareAndSwap(int $expected, int $new): bool {
        return $this->counter->cmpset($expected, $new);
    }
}

// Usage
Coroutine::run(function () {
    $counter = new ConcurrentCounter();

    // Spawn 100 coroutines
    for ($i = 0; $i < 100; $i++) {
        Coroutine::create(function () use ($counter) {
            for ($j = 0; $j < 100; $j++) {
                $counter->increment();
            }
        });
    }

    Coroutine::sleep(1);  // Wait for completion
    echo "Final count: " . $counter->get() . "\n";  // 10000
});
```

### Concurrent HashMap

```php
# filename: concurrent-hashmap.php
<?php

declare(strict_types=1);

use Swoole\Table;

class ConcurrentHashMap {
    private Table $table;

    public function __construct(int $size = 1024) {
        $this->table = new Table($size);
        $this->table->column('value', Table::TYPE_STRING, 1024);
        $this->table->create();
    }

    public function put(string $key, string $value): void {
        $this->table->set($key, ['value' => $value]);
    }

    public function get(string $key): ?string {
        $row = $this->table->get($key);
        return $row ? $row['value'] : null;
    }

    public function remove(string $key): bool {
        return $this->table->del($key);
    }

    public function containsKey(string $key): bool {
        return $this->table->exist($key);
    }

    public function size(): int {
        return $this->table->count();
    }
}
```

## Real-World Examples (~10 min)

### 1. Concurrent Web Scraper

```php
# filename: concurrent-scraper.php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use React\Http\Browser;
use function React\Promise\all;

class ConcurrentScraper {
    private Browser $browser;
    private int $maxConcurrent;

    public function __construct(int $maxConcurrent = 10) {
        $this->browser = new Browser();
        $this->maxConcurrent = $maxConcurrent;
    }

    public function scrapeUrls(array $urls): Promise {
        $chunks = array_chunk($urls, $this->maxConcurrent);
        $allResults = [];

        $promise = Promise::resolve();

        foreach ($chunks as $chunk) {
            $promise = $promise->then(function () use ($chunk, &$allResults) {
                $promises = [];

                foreach ($chunk as $url) {
                    $promises[] = $this->scrapeUrl($url);
                }

                return all($promises)->then(function ($results) use (&$allResults) {
                    $allResults = array_merge($allResults, $results);
                });
            });
        }

        return $promise->then(function () use (&$allResults) {
            return $allResults;
        });
    }

    private function scrapeUrl(string $url): Promise {
        return $this->browser->get($url)
            ->then(function ($response) use ($url) {
                $html = (string) $response->getBody();

                // Extract data (simplified)
                preg_match_all('/<title>(.*?)<\/title>/', $html, $matches);
                $title = $matches[1][0] ?? 'No title';

                preg_match_all('/<a href="(.*?)"/', $html, $matches);
                $links = $matches[1] ?? [];

                return [
                    'url' => $url,
                    'title' => $title,
                    'links' => $links,
                    'size' => strlen($html)
                ];
            })
            ->otherwise(function (Exception $e) use ($url) {
                return [
                    'url' => $url,
                    'error' => $e->getMessage()
                ];
            });
    }
}

// Usage
$scraper = new ConcurrentScraper(10);

$urls = [
    'https://example.com/page1',
    'https://example.com/page2',
    // ... 100 more URLs
];

$scraper->scrapeUrls($urls)
    ->then(function ($results) {
        $successful = array_filter($results, fn($r) => !isset($r['error']));
        echo "Scraped " . count($successful) . " pages successfully\n";

        foreach ($results as $result) {
            if (isset($result['error'])) {
                echo "Error on {$result['url']}: {$result['error']}\n";
            } else {
                echo "{$result['url']}: {$result['title']} ({$result['size']} bytes)\n";
            }
        }
    });

Loop::run();
```

### 2. Parallel Data Processing Pipeline

```php
# filename: parallel-pipeline.php
<?php

declare(strict_types=1);

class ParallelPipeline {
    private WorkerPool $pool;

    public function __construct(int $workers = 4) {
        $this->pool = new WorkerPool($workers);
    }

    public function process(string $inputFile, string $outputFile): void {
        // Stage 1: Read and parse CSV
        $data = $this->readCsv($inputFile);

        // Stage 2: Process in parallel
        $this->pool->start(function ($row) {
            // Transform data
            return [
                'id' => $row[0],
                'name' => strtoupper($row[1]),
                'email' => strtolower($row[2]),
                'score' => (int) $row[3] * 1.1,  // Apply 10% bonus
                'processed_at' => date('Y-m-d H:i:s')
            ];
        });

        // Submit all rows
        foreach ($data as $row) {
            $this->pool->submit($row);
        }

        // Stage 3: Collect and write results
        $results = [];
        for ($i = 0; $i < count($data); $i++) {
            $result = $this->pool->getResult();
            if ($result['success']) {
                $results[] = $result['data'];
            }
        }

        $this->writeCsv($outputFile, $results);
        $this->pool->shutdown();
    }

    private function readCsv(string $file): array {
        $data = [];
        $handle = fopen($file, 'r');

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = $row;
        }

        fclose($handle);
        return $data;
    }

    private function writeCsv(string $file, array $data): void {
        $handle = fopen($file, 'w');

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }
}
```

### 3. Concurrent Cache Warmer

```php
# filename: concurrent-cache-warmer.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;

class ConcurrentCacheWarmer {
    private array $urls;
    private int $concurrency;

    public function __construct(array $urls, int $concurrency = 20) {
        $this->urls = $urls;
        $this->concurrency = $concurrency;
    }

    public function warm(): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $chunks = array_chunk($this->urls, $this->concurrency);

        foreach ($chunks as $chunk) {
            $coroutines = [];

            foreach ($chunk as $url) {
                $coroutines[] = Coroutine::create(function () use ($url, &$results) {
                    try {
                        $parsed = parse_url($url);
                        $client = new Client(
                            $parsed['host'],
                            $parsed['scheme'] === 'https' ? 443 : 80,
                            $parsed['scheme'] === 'https'
                        );

                        $client->set(['timeout' => 10]);
                        $client->get($parsed['path'] ?? '/');

                        if ($client->statusCode === 200) {
                            $results['success']++;
                            echo "✓ Warmed: $url\n";
                        } else {
                            $results['failed']++;
                            $results['errors'][] = "$url (HTTP {$client->statusCode})";
                            echo "✗ Failed: $url\n";
                        }

                        $client->close();
                    } catch (Throwable $e) {
                        $results['failed']++;
                        $results['errors'][] = "$url ({$e->getMessage()})";
                        echo "✗ Error: $url\n";
                    }
                });
            }

            // Wait for this batch to complete
            Coroutine::sleep(0.1);
        }

        return $results;
    }
}

// Usage
Coroutine::run(function () {
    $urls = [
        'https://example.com/',
        'https://example.com/about',
        'https://example.com/products',
        // ... 1000 more URLs
    ];

    $warmer = new ConcurrentCacheWarmer($urls, 50);
    $results = $warmer->warm();

    echo "\n";
    echo "Total: " . count($urls) . "\n";
    echo "Success: {$results['success']}\n";
    echo "Failed: {$results['failed']}\n";
});
```

## Performance Comparison

| Approach | Use Case | Performance | Complexity |
|----------|----------|-------------|------------|
| ReactPHP | I/O-bound, event-driven apps | Excellent for I/O | Medium |
| ext-parallel | CPU-intensive tasks | Excellent for CPU | High |
| Swoole | High-performance web apps | Excellent overall | Medium |
| Traditional | Simple scripts | Poor for concurrency | Low |

### Benchmarks

```php
// Benchmark: Fetch 100 URLs

// Sequential (traditional)
$start = microtime(true);
foreach ($urls as $url) {
    file_get_contents($url);
}
$sequential = microtime(true) - $start;
// Result: ~200 seconds (100 URLs × 2s each)

// Concurrent (ReactPHP)
$start = microtime(true);
$client->fetchMultiple($urls)->then(function() use (&$concurrent) {
    $concurrent = microtime(true) - $start;
});
Loop::run();
// Result: ~2 seconds (parallel execution)

echo "Speedup: " . ($sequential / $concurrent) . "x\n";
// Speedup: 100x
```

## Best Practices (~3 min)

### 1. Choose the Right Tool

```php
// For I/O-bound tasks (API calls, database queries)
use ReactPHP or Swoole coroutines

// For CPU-intensive tasks (image processing, encryption)
use ext-parallel or multi-processing

// For mixed workloads
use Swoole (supports both)
```

### 2. Handle Errors Gracefully

```php
$promise->then(
    function ($result) {
        // Success
    },
    function (Exception $e) {
        // Error handling
        error_log("Task failed: " . $e->getMessage());
        return $defaultValue;  // Fallback
    }
);
```

### 3. Limit Concurrency

```php
// BAD: Unlimited concurrency
foreach ($urls as $url) {
    Coroutine::create(fn() => fetchUrl($url));
}

// GOOD: Limited concurrency
$semaphore = new Swoole\Coroutine\Semaphore(10);
foreach ($urls as $url) {
    Coroutine::create(function () use ($url, $semaphore) {
        $semaphore->acquire();
        try {
            fetchUrl($url);
        } finally {
            $semaphore->release();
        }
    });
}
```

### 4. Avoid Race Conditions

```php
// BAD: Race condition
$counter = 0;
foreach (range(1, 100) as $i) {
    Coroutine::create(function () use (&$counter) {
        $counter++;  // Not atomic!
    });
}

// GOOD: Atomic operations
$counter = new Swoole\Atomic(0);
foreach (range(1, 100) as $i) {
    Coroutine::create(function () use ($counter) {
        $counter->add(1);  // Atomic
    });
}
```

## Common Pitfalls (~2 min)

### 1. Shared State

```php
// DANGER: Shared mutable state
class SharedCounter {
    public int $count = 0;  // Not thread-safe!
}

// SOLUTION: Use atomic types or channels
class SafeCounter {
    private Atomic $count;

    public function __construct() {
        $this->count = new Atomic(0);
    }
}
```

### 2. Blocking Operations

```php
// BAD: Blocks the event loop
Coroutine::create(function () {
    sleep(10);  // Blocks entire process!
});

// GOOD: Non-blocking sleep
Coroutine::create(function () {
    Coroutine::sleep(10);  // Only blocks this coroutine
});
```

### 3. Resource Leaks

```php
// BAD: May leak connections
Coroutine::create(function () {
    $db = new PDO(...);
    // Exception thrown, connection not closed
});

// GOOD: Always clean up
Coroutine::create(function () {
    $db = new PDO(...);
    try {
        // Work
    } finally {
        $db = null;  // Ensure cleanup
    }
});
```

## Deadlocks: Detection and Prevention (~5 min)

Deadlocks occur when two or more processes are blocked forever, waiting for each other to release resources. Understanding deadlocks is crucial for building reliable concurrent systems.

### Understanding Deadlocks

A deadlock requires four conditions (Coffman conditions):
1. **Mutual Exclusion** - Resources cannot be shared
2. **Hold and Wait** - Process holds resources while waiting for others
3. **No Preemption** - Resources cannot be forcibly taken
4. **Circular Wait** - Circular chain of processes waiting for resources

### Deadlock Example

```php
# filename: deadlock-example.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class DeadlockExample {
    private Channel $resourceA;
    private Channel $resourceB;

    public function __construct() {
        $this->resourceA = new Channel(1);
        $this->resourceB = new Channel(1);
        
        // Initialize resources
        $this->resourceA->push('Resource A');
        $this->resourceB->push('Resource B');
    }

    public function process1(): void {
        Coroutine::create(function () {
            echo "Process 1: Acquiring Resource A\n";
            $this->resourceA->pop();  // Acquire A
            
            Coroutine::sleep(0.1);  // Simulate work
            
            echo "Process 1: Waiting for Resource B\n";
            $this->resourceB->pop();  // Try to acquire B (DEADLOCK!)
            
            echo "Process 1: Got both resources\n";
            
            // Release resources
            $this->resourceB->push('Resource B');
            $this->resourceA->push('Resource A');
        });
    }

    public function process2(): void {
        Coroutine::create(function () {
            echo "Process 2: Acquiring Resource B\n";
            $this->resourceB->pop();  // Acquire B
            
            Coroutine::sleep(0.1);  // Simulate work
            
            echo "Process 2: Waiting for Resource A\n";
            $this->resourceA->pop();  // Try to acquire A (DEADLOCK!)
            
            echo "Process 2: Got both resources\n";
            
            // Release resources
            $this->resourceA->push('Resource A');
            $this->resourceB->push('Resource B');
        });
    }
}

// This will deadlock!
Coroutine::run(function () {
    $example = new DeadlockExample();
    $example->process1();
    $example->process2();
    
    Coroutine::sleep(1);  // Wait - but processes are deadlocked
});
```

### Deadlock Prevention Strategies

#### 1. Lock Ordering

Always acquire locks in the same order:

```php
# filename: deadlock-prevention-lock-ordering.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class SafeResourceManager {
    private Channel $resourceA;
    private Channel $resourceB;

    public function __construct() {
        $this->resourceA = new Channel(1);
        $this->resourceB = new Channel(1);
        $this->resourceA->push('Resource A');
        $this->resourceB->push('Resource B');
    }

    // Always acquire A before B
    public function acquireResources(): array {
        $a = $this->resourceA->pop();
        $b = $this->resourceB->pop();
        return [$a, $b];
    }

    public function releaseResources(string $a, string $b): void {
        $this->resourceB->push($b);
        $this->resourceA->push($a);
    }
}

Coroutine::run(function () {
    $manager = new SafeResourceManager();
    
    // Process 1: Acquires A then B
    Coroutine::create(function () use ($manager) {
        [$a, $b] = $manager->acquireResources();
        echo "Process 1: Got both resources\n";
        Coroutine::sleep(0.1);
        $manager->releaseResources($a, $b);
    });
    
    // Process 2: Also acquires A then B (same order - no deadlock!)
    Coroutine::create(function () use ($manager) {
        Coroutine::sleep(0.05);  // Start slightly later
        [$a, $b] = $manager->acquireResources();
        echo "Process 2: Got both resources\n";
        Coroutine::sleep(0.1);
        $manager->releaseResources($a, $b);
    });
    
    Coroutine::sleep(0.5);
});
```

#### 2. Timeout-Based Acquisition

Use timeouts to detect and break potential deadlocks:

```php
# filename: deadlock-prevention-timeout.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class TimeoutResourceManager {
    private Channel $resourceA;
    private Channel $resourceB;

    public function __construct() {
        $this->resourceA = new Channel(1);
        $this->resourceB = new Channel(1);
        $this->resourceA->push('Resource A');
        $this->resourceB->push('Resource B');
    }

    public function acquireWithTimeout(Channel $resource, float $timeout): ?string {
        $start = microtime(true);
        
        while (microtime(true) - $start < $timeout) {
            $result = $resource->pop(0.1);  // Try with short timeout
            if ($result !== false) {
                return $result;
            }
        }
        
        return null;  // Timeout - potential deadlock detected
    }

    public function safeAcquire(float $timeout = 1.0): ?array {
        $a = $this->acquireWithTimeout($this->resourceA, $timeout);
        if ($a === null) {
            return null;  // Failed to acquire A
        }
        
        $b = $this->acquireWithTimeout($this->resourceB, $timeout);
        if ($b === null) {
            // Release A if we can't get B
            $this->resourceA->push($a);
            return null;
        }
        
        return [$a, $b];
    }
}
```

#### 3. Single Resource Acquisition

Acquire all resources atomically:

```php
# filename: deadlock-prevention-atomic.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class AtomicResourceManager {
    private Channel $bothResources;  // Single channel for both resources

    public function __construct() {
        $this->bothResources = new Channel(1);
        $this->bothResources->push(['Resource A', 'Resource B']);
    }

    public function acquireBoth(): ?array {
        return $this->bothResources->pop(1.0);  // Timeout prevents infinite wait
    }

    public function releaseBoth(array $resources): void {
        $this->bothResources->push($resources);
    }
}
```

### Deadlock Detection

For complex systems, implement deadlock detection:

```php
# filename: deadlock-detection.php
<?php

declare(strict_types=1);

class DeadlockDetector {
    private array $waitGraph = [];  // Process -> [Resources waiting for]
    private array $holdGraph = [];  // Process -> [Resources holding]

    public function addWait(string $process, string $resource): void {
        if (!isset($this->waitGraph[$process])) {
            $this->waitGraph[$process] = [];
        }
        $this->waitGraph[$process][] = $resource;
    }

    public function addHold(string $process, string $resource): void {
        if (!isset($this->holdGraph[$process])) {
            $this->holdGraph[$process] = [];
        }
        $this->holdGraph[$process][] = $resource;
    }

    public function detectDeadlock(): ?array {
        // Build wait-for graph
        $waitFor = [];
        foreach ($this->waitGraph as $process => $waitingFor) {
            foreach ($waitingFor as $resource) {
                // Find who holds this resource
                foreach ($this->holdGraph as $holder => $held) {
                    if (in_array($resource, $held)) {
                        if (!isset($waitFor[$process])) {
                            $waitFor[$process] = [];
                        }
                        $waitFor[$process][] = $holder;
                    }
                }
            }
        }

        // Detect cycles using DFS
        return $this->findCycle($waitFor);
    }

    private function findCycle(array $graph): ?array {
        $visited = [];
        $recStack = [];

        foreach (array_keys($graph) as $node) {
            if (!isset($visited[$node])) {
                $cycle = $this->dfs($graph, $node, $visited, $recStack, []);
                if ($cycle !== null) {
                    return $cycle;
                }
            }
        }

        return null;
    }

    private function dfs(array $graph, string $node, array &$visited, array &$recStack, array $path): ?array {
        $visited[$node] = true;
        $recStack[$node] = true;
        $path[] = $node;

        if (isset($graph[$node])) {
            foreach ($graph[$node] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $cycle = $this->dfs($graph, $neighbor, $visited, $recStack, $path);
                    if ($cycle !== null) {
                        return $cycle;
                    }
                } elseif (isset($recStack[$neighbor]) && $recStack[$neighbor]) {
                    // Found cycle
                    $cycleStart = array_search($neighbor, $path);
                    return array_slice($path, $cycleStart);
                }
            }
        }

        $recStack[$node] = false;
        return null;
    }
}

// Usage
$detector = new DeadlockDetector();
$detector->addHold('P1', 'R1');
$detector->addWait('P1', 'R2');
$detector->addHold('P2', 'R2');
$detector->addWait('P2', 'R1');

$cycle = $detector->detectDeadlock();
if ($cycle !== null) {
    echo "Deadlock detected! Cycle: " . implode(' -> ', $cycle) . "\n";
}
```

## Testing Concurrent Code (~5 min)

Testing concurrent code is challenging because bugs are non-deterministic. Race conditions may only appear under specific timing conditions, making them hard to reproduce and fix.

### Challenges in Testing Concurrent Code

1. **Non-deterministic behavior** - Same test may pass or fail randomly
2. **Timing-dependent bugs** - Bugs only appear with specific timing
3. **Heisenbugs** - Bugs disappear when you try to observe them
4. **Difficult reproduction** - Hard to recreate failure conditions

### Stress Testing

Run concurrent operations many times to increase chance of detecting race conditions:

```php
# filename: stress-test-concurrent.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Atomic;

class ConcurrentCounterStressTest {
    private Atomic $counter;
    private int $iterations;
    private int $coroutines;

    public function __construct(int $iterations = 10000, int $coroutines = 100) {
        $this->counter = new Atomic(0);
        $this->iterations = $iterations;
        $this->coroutines = $coroutines;
    }

    public function run(): array {
        $start = microtime(true);
        $errors = [];

        Coroutine::run(function () use (&$errors) {
            for ($i = 0; $i < $this->coroutines; $i++) {
                Coroutine::create(function () use (&$errors) {
                    for ($j = 0; $j < $this->iterations / $this->coroutines; $j++) {
                        $old = $this->counter->get();
                        $this->counter->add(1);
                        $new = $this->counter->get();
                        
                        // Verify atomicity
                        if ($new !== $old + 1) {
                            $errors[] = "Race condition detected: old=$old, new=$new";
                        }
                    }
                });
            }
        });

        $elapsed = microtime(true) - $start;
        $final = $this->counter->get();
        $expected = $this->iterations;

        return [
            'final_count' => $final,
            'expected_count' => $expected,
            'errors' => count($errors),
            'error_messages' => array_slice($errors, 0, 10),  // First 10 errors
            'elapsed_time' => $elapsed,
            'success' => $final === $expected && count($errors) === 0
        ];
    }
}

// Run stress test
$test = new ConcurrentCounterStressTest(100000, 100);
$result = $test->run();

echo "Stress Test Results:\n";
echo "Final count: {$result['final_count']}\n";
echo "Expected: {$result['expected_count']}\n";
echo "Errors: {$result['errors']}\n";
echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
```

### Property-Based Testing

Test invariants that should always hold:

```php
# filename: property-based-test.php
<?php

declare(strict_types=1);

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class ConcurrentQueuePropertyTest {
    private Channel $queue;
    private array $enqueued = [];
    private array $dequeued = [];

    public function __construct() {
        $this->queue = new Channel(1000);
    }

    public function testOrdering(int $operations = 1000): bool {
        // Property: Items dequeued should match order enqueued (FIFO)
        
        Coroutine::run(function () use ($operations) {
            // Producer
            Coroutine::create(function () use ($operations) {
                for ($i = 0; $i < $operations; $i++) {
                    $this->queue->push($i);
                    $this->enqueued[] = $i;
                }
            });

            // Consumer
            Coroutine::create(function () use ($operations) {
                for ($i = 0; $i < $operations; $i++) {
                    $item = $this->queue->pop();
                    $this->dequeued[] = $item;
                }
            });
        });

        Coroutine::sleep(2);  // Wait for completion

        // Verify property: dequeued items match enqueued order
        return $this->enqueued === $this->dequeued;
    }

    public function testNoLostItems(int $operations = 1000): bool {
        // Property: All enqueued items should be dequeued
        
        $this->enqueued = [];
        $this->dequeued = [];
        
        Coroutine::run(function () use ($operations) {
            $producers = 10;
            $consumers = 10;
            
            // Multiple producers
            for ($p = 0; $p < $producers; $p++) {
                Coroutine::create(function () use ($operations, $p, $producers) {
                    $start = ($operations / $producers) * $p;
                    $end = ($operations / $producers) * ($p + 1);
                    
                    for ($i = $start; $i < $end; $i++) {
                        $this->queue->push($i);
                        $this->enqueued[] = $i;
                    }
                });
            }
            
            // Multiple consumers
            for ($c = 0; $c < $consumers; $c++) {
                Coroutine::create(function () use ($operations) {
                    while (count($this->dequeued) < $operations) {
                        $item = $this->queue->pop(1.0);
                        if ($item !== false) {
                            $this->dequeued[] = $item;
                        }
                    }
                });
            }
        });

        Coroutine::sleep(5);

        // Verify property: all items accounted for
        sort($this->enqueued);
        sort($this->dequeued);
        return count($this->enqueued) === count($this->dequeued) &&
               $this->enqueued === $this->dequeued;
    }
}
```

### Race Condition Detection

Implement checks to detect race conditions:

```php
# filename: race-condition-detector.php
<?php

declare(strict_types=1);

class RaceConditionDetector {
    private array $accessLog = [];
    private array $violations = [];

    public function logAccess(string $thread, string $resource, string $operation): void {
        $this->accessLog[] = [
            'thread' => $thread,
            'resource' => $resource,
            'operation' => $operation,
            'time' => microtime(true)
        ];
    }

    public function detectRaceConditions(): array {
        // Group by resource
        $resourceAccess = [];
        foreach ($this->accessLog as $access) {
            $resource = $access['resource'];
            if (!isset($resourceAccess[$resource])) {
                $resourceAccess[$resource] = [];
            }
            $resourceAccess[$resource][] = $access;
        }

        // Check for concurrent writes
        foreach ($resourceAccess as $resource => $accesses) {
            $writes = array_filter($accesses, fn($a) => $a['operation'] === 'write');
            
            if (count($writes) > 1) {
                // Check if writes overlap in time
                foreach ($writes as $i => $write1) {
                    foreach (array_slice($writes, $i + 1) as $write2) {
                        if ($this->overlaps($write1, $write2)) {
                            $this->violations[] = [
                                'type' => 'concurrent_write',
                                'resource' => $resource,
                                'thread1' => $write1['thread'],
                                'thread2' => $write2['thread'],
                                'time1' => $write1['time'],
                                'time2' => $write2['time']
                            ];
                        }
                    }
                }
            }
        }

        return $this->violations;
    }

    private function overlaps(array $access1, array $access2): bool {
        // Simplified: consider accesses within 1ms as overlapping
        return abs($access1['time'] - $access2['time']) < 0.001;
    }
}

// Usage in test
$detector = new RaceConditionDetector();

use Swoole\Coroutine;

Coroutine::run(function () use ($detector) {
    $shared = 0;
    
    for ($i = 0; $i < 10; $i++) {
        Coroutine::create(function () use (&$shared, $detector, $i) {
            $detector->logAccess("thread-$i", "shared", "read");
            $value = $shared;
            
            Coroutine::sleep(0.001);
            
            $detector->logAccess("thread-$i", "shared", "write");
            $shared = $value + 1;  // Race condition!
        });
    }
    
    Coroutine::sleep(0.1);
    
    $violations = $detector->detectRaceConditions();
    if (count($violations) > 0) {
        echo "Race conditions detected: " . count($violations) . "\n";
    }
});
```

### Deterministic Testing with Controlled Scheduling

For ReactPHP, you can control the event loop timing:

```php
# filename: deterministic-test.php
<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use React\Promise\Promise;

class DeterministicConcurrentTest {
    public function testOrdering(): bool {
        $results = [];
        $promises = [];

        // Create promises in controlled order
        for ($i = 0; $i < 10; $i++) {
            $promises[] = new Promise(function ($resolve) use ($i, &$results) {
                Loop::addTimer(0.001 * $i, function () use ($resolve, $i, &$results) {
                    $results[] = $i;
                    $resolve($i);
                });
            });
        }

        // Wait for all
        \React\Promise\all($promises)->then(function () use (&$results) {
            // Results should be in order due to controlled timing
            $expected = range(0, 9);
            return $results === $expected;
        });

        Loop::run();

        return $results === range(0, 9);
    }
}
```

### Best Practices for Testing Concurrent Code

1. **Run tests multiple times** - Use loops to increase chance of catching bugs
2. **Test with different concurrency levels** - Test with 1, 10, 100, 1000 concurrent operations
3. **Verify invariants** - Check that properties always hold (no lost items, correct counts)
4. **Use timeouts** - Prevent tests from hanging indefinitely
5. **Log operations** - Record all operations to analyze failures
6. **Test edge cases** - Empty queues, single item, maximum capacity
7. **Monitor resource usage** - Check for memory leaks, connection leaks

## Wrap-up

Congratulations! You've completed the concurrent algorithms chapter. Here's what you've accomplished:

- ✓ Mastered async/await patterns with ReactPHP for non-blocking I/O operations
- ✓ Implemented parallel processing with ext-parallel for CPU-intensive tasks
- ✓ Built high-performance concurrent applications with Swoole coroutines
- ✓ Designed thread-safe concurrent data structures (queues, counters, hash maps)
- ✓ Applied concurrency patterns to real-world problems (web scraping, data processing)
- ✓ Implemented production deployment strategies with monitoring and graceful shutdown
- ✓ Learned to detect, prevent, and resolve deadlocks
- ✓ Mastered testing techniques for concurrent code (stress testing, property-based testing)
- ✓ Learned to avoid race conditions and handle errors gracefully in concurrent code

**Key Takeaways**:
- Use ReactPHP for event-driven I/O-bound operations
- Use ext-parallel for CPU-bound parallelism
- Use Swoole for high-performance web applications
- Always limit concurrency to prevent resource exhaustion
- Protect shared state with atomic operations or channels
- Handle errors gracefully with retries, fallbacks, and circuit breakers

Concurrent algorithms dramatically improve performance for I/O-bound operations (API calls, file I/O), CPU-intensive tasks (data processing, encoding), and real-time applications (chat, notifications). The techniques you've learned can provide 10x to 100x performance improvements when applied correctly.

## Further Reading

- [ReactPHP Documentation](https://reactphp.org/) — Official ReactPHP documentation and examples
- [Swoole Documentation](https://www.swoole.co.uk/docs) — Complete Swoole coroutine and server documentation
- [ext-parallel Documentation](https://www.php.net/manual/en/book.parallel.php) — PHP parallel extension reference
- [PHP Parallel Processing Guide](https://www.php.net/manual/en/book.pcntl.php) — Process control functions for PHP
- [Concurrency Patterns](https://en.wikipedia.org/wiki/Concurrency_pattern) — Wikipedia article on common concurrency patterns
- [Chapter 32: Probabilistic Algorithms](/series/php-algorithms/chapters/32-probabilistic-algorithms) — Space-efficient approximate algorithms
- [Chapter 29: Performance Optimization](/series/php-algorithms/chapters/29-performance-optimization) — General optimization techniques

## Production Deployment Tips

### 1. Supervisor Configuration for ReactPHP

```ini
; /etc/supervisor/conf.d/reactphp-worker.conf
[program:reactphp-worker]
command=/usr/bin/php /path/to/worker.php
process_name=%(program_name)s_%(process_num)02d
numprocs=4
autostart=true
autorestart=true
startsecs=10
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/reactphp-worker.log
stopwaitsecs=60
```

### 2. Systemd Service for Swoole

```ini
; /etc/systemd/system/swoole-http.service
[Unit]
Description=Swoole HTTP Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/app
ExecStart=/usr/bin/php /var/www/app/server.php
Restart=on-failure
RestartSec=5s
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl enable swoole-http
sudo systemctl start swoole-http
sudo systemctl status swoole-http
```

### 3. Docker Configuration

```dockerfile
# Dockerfile
FROM php:8.2-cli

# Install extensions
RUN apt-get update && apt-get install -y \
    libev-dev \
    && docker-php-ext-install sockets pcntl \
    && pecl install swoole \
    && docker-php-ext-enable swoole

# Install ReactPHP via composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .

CMD ["php", "server.php"]
```

```yaml
# docker-compose.yml
version: '3.8'

services:
  reactphp-worker:
    build: .
    command: php worker.php
    volumes:
      - ./:/app
    environment:
      - PHP_MEMORY_LIMIT=256M
      - WORKER_CONCURRENCY=10
    restart: unless-stopped
    deploy:
      replicas: 4
      resources:
        limits:
          cpus: '0.5'
          memory: 512M

  swoole-http:
    build: .
    command: php http-server.php
    ports:
      - "9501:9501"
    volumes:
      - ./:/app
    restart: unless-stopped
```

### 4. Monitoring and Health Checks

```php
class HealthCheckServer {
    private $stats = [
        'requests' => 0,
        'errors' => 0,
        'active_workers' => 0,
        'start_time' => null
    ];

    public function __construct() {
        $this->stats['start_time'] = time();
    }

    public function start(int $port = 9090): void {
        $http = new React\Http\HttpServer(function (ServerRequestInterface $request) {
            $path = $request->getUri()->getPath();

            if ($path === '/health') {
                return $this->healthCheck();
            }

            if ($path === '/metrics') {
                return $this->metrics();
            }

            return new React\Http\Message\Response(404, [], 'Not Found');
        });

        $socket = new React\Socket\SocketServer("0.0.0.0:$port");
        $http->listen($socket);

        echo "Health check server running on port $port\n";
    }

    private function healthCheck(): React\Http\Message\Response {
        $uptime = time() - $this->stats['start_time'];
        $errorRate = $this->stats['requests'] > 0
            ? ($this->stats['errors'] / $this->stats['requests']) * 100
            : 0;

        $status = $errorRate < 5 ? 'healthy' : 'degraded';

        $body = json_encode([
            'status' => $status,
            'uptime' => $uptime,
            'error_rate' => round($errorRate, 2)
        ]);

        return new React\Http\Message\Response(200, [
            'Content-Type' => 'application/json'
        ], $body);
    }

    private function metrics(): React\Http\Message\Response {
        $body = json_encode([
            'requests_total' => $this->stats['requests'],
            'errors_total' => $this->stats['errors'],
            'active_workers' => $this->stats['active_workers'],
            'memory_usage' => memory_get_usage(true),
            'uptime_seconds' => time() - $this->stats['start_time']
        ]);

        return new React\Http\Message\Response(200, [
            'Content-Type' => 'application/json'
        ], $body);
    }

    public function incrementRequests(): void {
        $this->stats['requests']++;
    }

    public function incrementErrors(): void {
        $this->stats['errors']++;
    }

    public function setActiveWorkers(int $count): void {
        $this->stats['active_workers'] = $count;
    }
}

// Usage
$healthCheck = new HealthCheckServer();
$healthCheck->start(9090);

Loop::run();
```

### 5. Graceful Shutdown

```php
class GracefulShutdownHandler {
    private array $workers = [];
    private bool $shuttingDown = false;

    public function __construct() {
        // Handle shutdown signals
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }
    }

    public function registerWorker(WorkerInterface $worker): void {
        $this->workers[] = $worker;
    }

    public function shutdown(): void {
        if ($this->shuttingDown) {
            return;
        }

        $this->shuttingDown = true;
        echo "Graceful shutdown initiated...\n";

        // Stop accepting new work
        foreach ($this->workers as $worker) {
            $worker->stopAcceptingWork();
        }

        // Wait for current work to complete (with timeout)
        $timeout = 30;
        $start = time();

        while (time() - $start < $timeout) {
            $allIdle = true;

            foreach ($this->workers as $worker) {
                if ($worker->hasActiveWork()) {
                    $allIdle = false;
                    break;
                }
            }

            if ($allIdle) {
                break;
            }

            sleep(1);
        }

        // Cleanup
        foreach ($this->workers as $worker) {
            $worker->cleanup();
        }

        echo "Shutdown complete\n";
        exit(0);
    }
}
```

### 6. Performance Tuning

```php
// ReactPHP: Optimize event loop
Loop::addPeriodicTimer(0.001, function () {
    // Process events frequently for low latency
});

// Swoole: Configuration for production
$server = new Swoole\Http\Server("0.0.0.0", 9501);

$server->set([
    'worker_num' => swoole_cpu_num() * 2,
    'task_worker_num' => swoole_cpu_num(),
    'max_request' => 10000,  // Restart workers after N requests
    'max_conn' => 10000,     // Max concurrent connections
    'dispatch_mode' => 2,     // Fixed dispatch mode
    'open_tcp_nodelay' => true,
    'heartbeat_check_interval' => 60,
    'heartbeat_idle_time' => 600,
    'buffer_output_size' => 32 * 1024 * 1024,  // 32MB
    'socket_buffer_size' => 128 * 1024 * 1024,  // 128MB
]);

// PHP configuration for concurrency
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 0);
ini_set('default_socket_timeout', 60);
```

### 7. Load Balancing

```nginx
# Nginx configuration for Swoole/ReactPHP
upstream backend {
    least_conn;  # Use least connections algorithm
    server 127.0.0.1:9501 weight=1 max_fails=3 fail_timeout=30s;
    server 127.0.0.1:9502 weight=1 max_fails=3 fail_timeout=30s;
    server 127.0.0.1:9503 weight=1 max_fails=3 fail_timeout=30s;
    server 127.0.0.1:9504 weight=1 max_fails=3 fail_timeout=30s;
    keepalive 32;  # Connection pooling
}

server {
    listen 80;
    server_name api.example.com;

    location / {
        proxy_pass http://backend;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

## Framework Integration

### Laravel with ReactPHP

```php
// app/Services/ReactPhpService.php
namespace App\Services;

use React\EventLoop\Loop;
use React\Http\Browser;

class ReactPhpService {
    private Browser $browser;

    public function __construct() {
        $this->browser = new Browser();
    }

    public function fetchMultipleApis(array $urls): array {
        $results = [];
        $promises = [];

        foreach ($urls as $key => $url) {
            $promises[$key] = $this->browser->get($url)
                ->then(function ($response) {
                    return json_decode((string) $response->getBody(), true);
                });
        }

        \React\Promise\all($promises)->then(function ($data) use (&$results) {
            $results = $data;
        });

        Loop::run();

        return $results;
    }
}

// Usage in controller
public function dashboard(ReactPhpService $reactService) {
    $data = $reactService->fetchMultipleApis([
        'users' => 'https://api.example.com/users',
        'stats' => 'https://api.example.com/stats',
        'alerts' => 'https://api.example.com/alerts'
    ]);

    return view('dashboard', $data);
}
```

### Symfony with Swoole

```php
// config/packages/swoole.yaml
swoole:
    http_server:
        host: 0.0.0.0
        port: 9501
        settings:
            worker_num: 4
            task_worker_num: 2

// src/Command/SwooleServerCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SwooleServerCommand extends Command {
    protected static $defaultName = 'app:swoole:start';

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $server = new Server("0.0.0.0", 9501);

        $server->on("request", function (Request $request, Response $response) {
            // Handle Symfony request
            $kernel = new \App\Kernel('prod', false);
            $sfRequest = \Symfony\Component\HttpFoundation\Request::create(
                $request->server['request_uri'],
                $request->server['request_method']
            );

            $sfResponse = $kernel->handle($sfRequest);

            $response->status($sfResponse->getStatusCode());
            $response->end($sfResponse->getContent());
        });

        $server->start();

        return Command::SUCCESS;
    }
}
```

## Security Considerations

### 1. Input Validation in Concurrent Contexts

```php
class SecureAsyncValidator {
    public function validateAndProcess(array $inputs): Promise {
        $promises = [];

        foreach ($inputs as $key => $input) {
            $promises[$key] = new Promise(function ($resolve, $reject) use ($input) {
                // Validate in separate context
                if ($this->isValid($input)) {
                    $resolve($this->sanitize($input));
                } else {
                    $reject(new Exception("Invalid input: $key"));
                }
            });
        }

        return all($promises);
    }

    private function isValid($input): bool {
        // Implement validation logic
        return !empty($input) && strlen($input) < 1000;
    }

    private function sanitize($input): string {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
```

### 2. Rate Limiting for Concurrent Requests

```php
class ConcurrentRateLimiter {
    private SplObjectStorage $activeRequests;
    private int $maxConcurrent;

    public function __construct(int $maxConcurrent = 10) {
        $this->activeRequests = new SplObjectStorage();
        $this->maxConcurrent = $maxConcurrent;
    }

    public function execute(callable $operation): Promise {
        if ($this->activeRequests->count() >= $this->maxConcurrent) {
            return Promise::reject(new Exception("Rate limit exceeded"));
        }

        $deferred = new React\Promise\Deferred();
        $this->activeRequests->attach($deferred);

        $operation()
            ->then(function ($result) use ($deferred) {
                $this->activeRequests->detach($deferred);
                $deferred->resolve($result);
            })
            ->otherwise(function ($error) use ($deferred) {
                $this->activeRequests->detach($deferred);
                $deferred->reject($error);
            });

        return $deferred->promise();
    }
}
```

### 3. Memory Leak Prevention

```php
class MemoryAwareWorker {
    private int $maxMemory;
    private int $processedCount = 0;

    public function __construct(int $maxMemoryMB = 128) {
        $this->maxMemory = $maxMemoryMB * 1024 * 1024;
    }

    public function process($data): void {
        // Check memory before processing
        if (memory_get_usage(true) > $this->maxMemory) {
            $this->restart();
        }

        // Process data
        $this->processedCount++;

        // Periodic cleanup
        if ($this->processedCount % 1000 === 0) {
            gc_collect_cycles();
        }
    }

    private function restart(): void {
        echo "Memory limit reached, restarting worker...\n";
        exit(0);  // Supervisor will restart
    }
}
```

## Performance Benchmarks

```php
// Benchmark: API aggregation
function benchmarkSequential(array $urls): float {
    $start = microtime(true);

    foreach ($urls as $url) {
        file_get_contents($url);
    }

    return microtime(true) - $start;
}

function benchmarkConcurrent(array $urls): float {
    $start = microtime(true);
    $client = new ConcurrentApiClient();

    $client->fetchMultiple($urls)->then(function () {
        // Done
    });

    Loop::run();

    return microtime(true) - $start;
}

$urls = array_fill(0, 20, 'https://httpbin.org/delay/1');

$sequentialTime = benchmarkSequential($urls);
$concurrentTime = benchmarkConcurrent($urls);

echo "Sequential: {$sequentialTime}s\n";  // ~20 seconds
echo "Concurrent: {$concurrentTime}s\n";   // ~1 second
echo "Speedup: " . ($sequentialTime / $concurrentTime) . "x\n";  // ~20x
```


<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="31"
  label="Concurrent Algorithms complete!"
/>
## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 31 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-31)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-31
php 01-*.php
```

## Practice Exercises

1. **Concurrent Download Manager**: Build a system that downloads files with progress tracking, pause/resume support, and bandwidth limiting
2. **Parallel Image Pipeline**: Create a concurrent image processor that handles thumbnailing, watermarking, and format conversion
3. **Concurrent Web Crawler**: Implement a web crawler with depth limits, robots.txt respect, and URL deduplication
4. **Multi-Queue Producer-Consumer**: Build a system with multiple priority queues and worker pools
5. **Distributed Cache**: Create a concurrent cache with TTL, LRU eviction, and network replication
6. **Real-time Chat Server**: Build a WebSocket-based chat with ReactPHP or Swoole
7. **Job Queue System**: Implement a concurrent job processing system with retries and dead-letter queues
8. **API Gateway**: Create an async API gateway that aggregates multiple microservices
9. **Stream Processor**: Build a real-time log processor with aggregation and alerting
10. **Load Testing Tool**: Implement a concurrent HTTP load generator with metrics collection
