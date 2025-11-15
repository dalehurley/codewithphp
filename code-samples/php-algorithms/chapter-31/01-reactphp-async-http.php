<?php
/**
 * ReactPHP Async HTTP Client
 *
 * Demonstrates concurrent HTTP requests using ReactPHP promises and event loop.
 * This example shows how to fetch multiple URLs in parallel, dramatically improving
 * performance compared to sequential requests.
 *
 * Installation: composer require react/http react/promise
 *
 * @category  Algorithms
 * @package   ConcurrentAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter31;

use React\EventLoop\Loop;
use React\Http\Browser;
use React\Promise\Promise;
use function React\Promise\all;

/**
 * Concurrent HTTP client using ReactPHP
 */
class ConcurrentApiClient
{
    private Browser $browser;
    private int $maxConcurrent;

    /**
     * @param int $maxConcurrent Maximum concurrent requests
     */
    public function __construct(int $maxConcurrent = 10)
    {
        $this->browser = new Browser();
        $this->maxConcurrent = $maxConcurrent;
    }

    /**
     * Fetch multiple URLs concurrently
     *
     * @param array<string> $urls List of URLs to fetch
     * @return Promise<array<string>>
     */
    public function fetchMultiple(array $urls): Promise
    {
        $promises = [];

        foreach ($urls as $key => $url) {
            $promises[$key] = $this->browser->get($url)
                ->then(function ($response) use ($url) {
                    return [
                        'url' => $url,
                        'status' => $response->getStatusCode(),
                        'body' => (string) $response->getBody(),
                        'size' => strlen($response->getBody())
                    ];
                })
                ->otherwise(function (\Exception $e) use ($url) {
                    return [
                        'url' => $url,
                        'error' => $e->getMessage()
                    ];
                });
        }

        return all($promises);
    }

    /**
     * Fetch URLs with rate limiting
     *
     * @param array<string> $urls URLs to fetch
     * @param int $concurrency Maximum concurrent requests
     * @return Promise<array>
     */
    public function fetchWithRateLimit(array $urls, int $concurrency = 5): Promise
    {
        return new Promise(function ($resolve) use ($urls, $concurrency) {
            $results = [];
            $queue = $urls;
            $active = 0;

            $process = function () use (&$queue, &$active, &$results, &$process, $concurrency, $resolve, $urls) {
                while ($active < $concurrency && count($queue) > 0) {
                    $url = array_shift($queue);
                    $active++;

                    $this->browser->get($url)
                        ->then(function ($response) use (&$active, &$results, &$process, $url, $resolve, $urls) {
                            $results[$url] = [
                                'status' => $response->getStatusCode(),
                                'size' => strlen($response->getBody())
                            ];
                            $active--;

                            if (count($results) === count($urls)) {
                                $resolve($results);
                            } else {
                                $process();
                            }
                        })
                        ->otherwise(function (\Exception $e) use (&$active, &$results, &$process, $url, $resolve, $urls) {
                            $results[$url] = ['error' => $e->getMessage()];
                            $active--;

                            if (count($results) === count($urls)) {
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

    /**
     * Fetch with retry logic
     *
     * @param string $url URL to fetch
     * @param int $maxRetries Maximum retry attempts
     * @param int $attempt Current attempt number
     * @return Promise
     */
    public function fetchWithRetry(string $url, int $maxRetries = 3, int $attempt = 1): Promise
    {
        return $this->browser->get($url)
            ->then(function ($response) {
                if ($response->getStatusCode() >= 500) {
                    throw new \Exception("Server error: " . $response->getStatusCode());
                }
                return (string) $response->getBody();
            })
            ->otherwise(function (\Exception $e) use ($url, $maxRetries, $attempt) {
                if ($attempt < $maxRetries) {
                    echo "Attempt {$attempt} failed for {$url}, retrying...\n";

                    return new Promise(function ($resolve, $reject) use ($url, $maxRetries, $attempt) {
                        Loop::addTimer(1, function () use ($url, $maxRetries, $attempt, $resolve, $reject) {
                            $this->fetchWithRetry($url, $maxRetries, $attempt + 1)
                                ->then($resolve, $reject);
                        });
                    });
                }

                throw $e;
            });
    }
}

/**
 * Example usage demonstrating concurrent HTTP operations
 */
function demonstrateConcurrentHttp(): void
{
    echo "=== ReactPHP Concurrent HTTP Client Demo ===\n\n";

    $client = new ConcurrentApiClient();

    // Test URLs (using httpbin.org for testing)
    $urls = [
        'https://httpbin.org/delay/1',
        'https://httpbin.org/delay/1',
        'https://httpbin.org/delay/1',
        'https://httpbin.org/get',
        'https://httpbin.org/user-agent',
    ];

    echo "Fetching " . count($urls) . " URLs concurrently...\n";
    $start = microtime(true);

    $client->fetchMultiple($urls)
        ->then(function ($results) use ($start) {
            $elapsed = microtime(true) - $start;

            echo "\n✓ All requests completed in " . number_format($elapsed, 2) . " seconds\n\n";
            echo "Results:\n";

            foreach ($results as $index => $result) {
                if (isset($result['error'])) {
                    echo "  [{$index}] Error: {$result['error']}\n";
                } else {
                    echo "  [{$index}] {$result['url']}\n";
                    echo "        Status: {$result['status']}, Size: {$result['size']} bytes\n";
                }
            }
        })
        ->otherwise(function (\Exception $e) {
            echo "✗ Batch failed: " . $e->getMessage() . "\n";
        });

    Loop::run();

    echo "\n=== Rate Limited Fetching ===\n";
    echo "Fetching with max 2 concurrent requests...\n";

    $start = microtime(true);

    $client->fetchWithRateLimit($urls, 2)
        ->then(function ($results) use ($start) {
            $elapsed = microtime(true) - $start;

            echo "✓ Rate-limited fetch completed in " . number_format($elapsed, 2) . " seconds\n";
            echo "  (Should be slower than unlimited concurrent)\n";
        });

    Loop::run();
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        if (!class_exists('React\\EventLoop\\Loop')) {
            echo "Error: ReactPHP not installed. Run: composer require react/http react/promise\n";
            exit(1);
        }

        demonstrateConcurrentHttp();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
