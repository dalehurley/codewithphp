# Chapter 31: Concurrent Algorithms

## Introduction

Concurrency allows multiple tasks to make progress simultaneously, dramatically improving performance for I/O-bound operations and CPU-intensive tasks. This chapter explores concurrent algorithms in PHP, including async/await patterns, parallel processing, and concurrent data structures.

## Understanding Concurrency in PHP

### Traditional PHP vs. Concurrent PHP

```php
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

## Async/Await with ReactPHP

### Setting Up ReactPHP

```bash
composer require react/http react/promise
```

### Basic Promise Pattern

```php
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

## Parallel Processing with ext-parallel

### Installing Parallel Extension

```bash
pecl install parallel
```

### Basic Parallel Execution

```php
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

## Swoole Coroutines

### Setting Up Swoole

```bash
pecl install swoole
```

### Coroutine-Based Concurrency

```php
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

## Concurrent Data Structures

### Thread-Safe Queue

```php
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

## Real-World Examples

### 1. Concurrent Web Scraper

```php
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

## Best Practices

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

## Common Pitfalls

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

## Summary

Concurrent algorithms dramatically improve performance for:
- I/O-bound operations (API calls, file I/O)
- CPU-intensive tasks (data processing, encoding)
- Real-time applications (chat, notifications)

**Key Takeaways**:
- Use ReactPHP for event-driven I/O
- Use ext-parallel for CPU-bound parallelism
- Use Swoole for high-performance web apps
- Always limit concurrency
- Protect shared state
- Handle errors gracefully

## Next Steps

- **Chapter 32: Probabilistic Algorithms** - Space-efficient approximate algorithms
- **Chapter 27: Caching & Memoization** - Speed up repeated computations
- **Chapter 29: Performance Optimization** - General optimization techniques

## Practice Exercises

1. Implement a concurrent download manager with progress tracking
2. Build a parallel image processing pipeline
3. Create a concurrent web crawler with depth limits
4. Implement a producer-consumer pattern with multiple queues
5. Build a concurrent cache with TTL and eviction policies
