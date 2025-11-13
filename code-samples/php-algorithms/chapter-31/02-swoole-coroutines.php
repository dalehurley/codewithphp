<?php
/**
 * Swoole Coroutines Example
 *
 * Demonstrates coroutine-based concurrency using Swoole for high-performance
 * concurrent operations. Swoole provides lightweight coroutines that enable
 * writing asynchronous code in a synchronous style.
 *
 * Installation: pecl install swoole
 *
 * @category  Algorithms
 * @package   ConcurrentAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter31;

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use Swoole\Coroutine\Channel;

/**
 * Concurrent HTTP client using Swoole coroutines
 */
class SwooleHttpClient
{
    /**
     * Fetch multiple URLs concurrently
     *
     * @param array<string> $urls URLs to fetch
     * @return array<string, string>
     */
    public function fetchMultiple(array $urls): array
    {
        $results = [];
        $wg = new Coroutine\WaitGroup();

        foreach ($urls as $url) {
            $wg->add();

            Coroutine::create(function () use ($url, &$results, $wg) {
                try {
                    $parsed = parse_url($url);
                    $host = $parsed['host'] ?? '';
                    $port = $parsed['scheme'] === 'https' ? 443 : 80;
                    $ssl = $parsed['scheme'] === 'https';
                    $path = $parsed['path'] ?? '/';

                    $client = new Client($host, $port, $ssl);
                    $client->set(['timeout' => 10]);
                    $client->get($path);

                    if ($client->statusCode === 200) {
                        $results[$url] = [
                            'status' => $client->statusCode,
                            'body' => $client->body,
                            'size' => strlen($client->body)
                        ];
                    } else {
                        $results[$url] = [
                            'status' => $client->statusCode,
                            'error' => 'HTTP ' . $client->statusCode
                        ];
                    }

                    $client->close();
                } catch (\Throwable $e) {
                    $results[$url] = ['error' => $e->getMessage()];
                } finally {
                    $wg->done();
                }
            });
        }

        $wg->wait();
        return $results;
    }

    /**
     * Parallel database queries simulation
     *
     * @param array<string> $queries SQL queries to execute
     * @return array<string, mixed>
     */
    public function parallelQueries(array $queries): array
    {
        $results = [];
        $wg = new Coroutine\WaitGroup();

        foreach ($queries as $key => $query) {
            $wg->add();

            Coroutine::create(function () use ($query, $key, &$results, $wg) {
                // Simulate database query with coroutine sleep
                Coroutine::sleep(mt_rand(1, 3) / 10); // 0.1-0.3 seconds

                $results[$key] = [
                    'query' => $query,
                    'rows' => mt_rand(1, 100),
                    'time' => mt_rand(10, 300) / 1000
                ];

                $wg->done();
            });
        }

        $wg->wait();
        return $results;
    }
}

/**
 * Producer-Consumer pattern using Swoole Channel
 */
class ProducerConsumer
{
    private Channel $channel;
    private int $consumerCount;

    /**
     * @param int $capacity Channel capacity
     * @param int $consumerCount Number of consumer coroutines
     */
    public function __construct(int $capacity = 100, int $consumerCount = 4)
    {
        $this->channel = new Channel($capacity);
        $this->consumerCount = $consumerCount;
    }

    /**
     * Process items using producer-consumer pattern
     *
     * @param array $items Items to process
     * @param callable $processor Processing function
     * @return array Processed results
     */
    public function process(array $items, callable $processor): array
    {
        $results = [];
        $wg = new Coroutine\WaitGroup();

        // Producer coroutine
        Coroutine::create(function () use ($items) {
            foreach ($items as $item) {
                $this->channel->push($item);
            }

            // Send stop signals
            for ($i = 0; $i < $this->consumerCount; $i++) {
                $this->channel->push(null);
            }
        });

        // Consumer coroutines
        for ($i = 0; $i < $this->consumerCount; $i++) {
            $wg->add();

            Coroutine::create(function () use ($processor, &$results, $wg) {
                while (true) {
                    $item = $this->channel->pop();

                    if ($item === null) {
                        break; // Stop signal
                    }

                    try {
                        $result = $processor($item);
                        $results[] = $result;
                    } catch (\Throwable $e) {
                        $results[] = ['error' => $e->getMessage(), 'item' => $item];
                    }
                }

                $wg->done();
            });
        }

        $wg->wait();
        return $results;
    }
}

/**
 * Concurrent counter using Swoole Atomic
 */
class ConcurrentCounter
{
    private \Swoole\Atomic $counter;

    public function __construct(int $initial = 0)
    {
        $this->counter = new \Swoole\Atomic($initial);
    }

    public function increment(): int
    {
        return $this->counter->add(1);
    }

    public function decrement(): int
    {
        return $this->counter->sub(1);
    }

    public function get(): int
    {
        return $this->counter->get();
    }

    public function compareAndSwap(int $expected, int $new): bool
    {
        return $this->counter->cmpset($expected, $new);
    }
}

/**
 * Example usage demonstrating Swoole coroutines
 */
function demonstrateSwooleCoroutines(): void
{
    echo "=== Swoole Coroutines Demo ===\n\n";

    Coroutine::run(function () {
        // Test 1: Concurrent HTTP requests
        echo "1. Concurrent HTTP Requests\n";
        $client = new SwooleHttpClient();

        $urls = [
            'https://httpbin.org/delay/1',
            'https://httpbin.org/get',
            'https://httpbin.org/user-agent',
        ];

        $start = microtime(true);
        $results = $client->fetchMultiple($urls);
        $elapsed = microtime(true) - $start;

        echo "   Fetched " . count($results) . " URLs in " . number_format($elapsed, 2) . " seconds\n";

        foreach ($results as $url => $result) {
            if (isset($result['error'])) {
                echo "   ✗ {$url}: {$result['error']}\n";
            } else {
                echo "   ✓ {$url}: Status {$result['status']}, {$result['size']} bytes\n";
            }
        }

        // Test 2: Producer-Consumer pattern
        echo "\n2. Producer-Consumer Pattern\n";
        $pc = new ProducerConsumer(50, 4);

        $items = range(1, 20);
        $start = microtime(true);

        $results = $pc->process($items, function ($item) {
            Coroutine::sleep(0.1); // Simulate work
            return $item * 2;
        });

        $elapsed = microtime(true) - $start;

        echo "   Processed " . count($results) . " items in " . number_format($elapsed, 2) . " seconds\n";
        echo "   First 10 results: " . implode(', ', array_slice($results, 0, 10)) . "\n";

        // Test 3: Concurrent counter
        echo "\n3. Concurrent Counter (Thread-Safe)\n";
        $counter = new ConcurrentCounter();

        $wg = new Coroutine\WaitGroup();

        // Spawn 100 coroutines
        for ($i = 0; $i < 100; $i++) {
            $wg->add();

            Coroutine::create(function () use ($counter, $wg) {
                for ($j = 0; $j < 100; $j++) {
                    $counter->increment();
                }
                $wg->done();
            });
        }

        $wg->wait();
        echo "   Final count: " . $counter->get() . " (expected: 10000)\n";

        // Test 4: Parallel database queries
        echo "\n4. Parallel Database Queries (Simulated)\n";
        $queries = [
            'users' => 'SELECT * FROM users LIMIT 100',
            'orders' => 'SELECT * FROM orders WHERE status = "pending"',
            'products' => 'SELECT * FROM products WHERE stock > 0',
            'analytics' => 'SELECT COUNT(*) FROM page_views WHERE date = CURDATE()'
        ];

        $start = microtime(true);
        $results = $client->parallelQueries($queries);
        $elapsed = microtime(true) - $start;

        echo "   Executed " . count($queries) . " queries in " . number_format($elapsed, 2) . " seconds\n";

        foreach ($results as $key => $result) {
            echo "   - {$key}: {$result['rows']} rows in " . number_format($result['time'], 3) . "s\n";
        }
    });
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        if (!extension_loaded('swoole')) {
            echo "Error: Swoole extension not installed. Run: pecl install swoole\n";
            exit(1);
        }

        demonstrateSwooleCoroutines();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
