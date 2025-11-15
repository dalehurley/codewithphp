<?php
declare(strict_types=1);

/**
 * Concurrent HTTP Requests with Guzzle
 *
 * Make multiple HTTP requests in parallel using Guzzle's async features.
 * Similar to Promise.all() in JavaScript.
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\RequestException;

echo "=== Concurrent HTTP Requests with Guzzle ===\n\n";

$client = new Client([
    'timeout' => 10,
    'headers' => [
        'User-Agent' => 'PHP-Guzzle-Example/1.0',
    ]
]);

// Example 1: Sequential vs Concurrent timing
echo "1. Performance Comparison\n";
echo "   (Using JSONPlaceholder API for demo)\n\n";

// Sequential requests (slow)
echo "   Sequential requests:\n";
$startSeq = microtime(true);

try {
    $response1 = $client->get('https://jsonplaceholder.typicode.com/users/1');
    echo "   - Request 1 completed\n";

    $response2 = $client->get('https://jsonplaceholder.typicode.com/users/2');
    echo "   - Request 2 completed\n";

    $response3 = $client->get('https://jsonplaceholder.typicode.com/users/3');
    echo "   - Request 3 completed\n";

    $seqTime = microtime(true) - $startSeq;
    echo "   Sequential time: " . round($seqTime, 2) . "s\n\n";
} catch (RequestException $e) {
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Concurrent requests (fast)
echo "   Concurrent requests:\n";
$startAsync = microtime(true);

$promises = [
    'user1' => $client->getAsync('https://jsonplaceholder.typicode.com/users/1'),
    'user2' => $client->getAsync('https://jsonplaceholder.typicode.com/users/2'),
    'user3' => $client->getAsync('https://jsonplaceholder.typicode.com/users/3'),
];

try {
    $results = Promise\Utils::unwrap($promises);
    echo "   - All requests completed\n";

    $asyncTime = microtime(true) - $startAsync;
    echo "   Concurrent time: " . round($asyncTime, 2) . "s\n";
    echo "   ⚡ Speed improvement: " . round($seqTime / $asyncTime, 1) . "x faster\n\n";

    // Process results
    foreach ($results as $key => $response) {
        $data = json_decode($response->getBody()->getContents(), true);
        echo "   {$key}: {$data['name']}\n";
    }
} catch (RequestException $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Example 2: Handle mixed success/failure
echo "\n2. Error Handling with settle()\n";

$mixedPromises = [
    'valid' => $client->getAsync('https://jsonplaceholder.typicode.com/users/1'),
    'invalid' => $client->getAsync('https://jsonplaceholder.typicode.com/invalid-endpoint'),
];

$results = Promise\Utils::settle($mixedPromises)->wait();

foreach ($results as $key => $result) {
    if ($result['state'] === 'fulfilled') {
        $data = json_decode($result['value']->getBody()->getContents(), true);
        echo "   ✅ {$key}: Success - {$data['name']}\n";
    } else {
        echo "   ❌ {$key}: Failed - {$result['reason']->getMessage()}\n";
    }
}

// Example 3: Practical use case - aggregate data from multiple APIs
echo "\n3. Aggregate Data from Multiple Sources\n";

function fetchMultipleAPIs(): array {
    $client = new Client(['timeout' => 10]);

    $promises = [
        'users' => $client->getAsync('https://jsonplaceholder.typicode.com/users?_limit=3'),
        'posts' => $client->getAsync('https://jsonplaceholder.typicode.com/posts?_limit=3'),
        'todos' => $client->getAsync('https://jsonplaceholder.typicode.com/todos?_limit=3'),
    ];

    $results = Promise\Utils::settle($promises)->wait();

    $data = [];
    foreach ($results as $key => $result) {
        if ($result['state'] === 'fulfilled') {
            $data[$key] = json_decode($result['value']->getBody()->getContents(), true);
        } else {
            $data[$key] = null;
        }
    }

    return $data;
}

$aggregatedData = fetchMultipleAPIs();

foreach ($aggregatedData as $endpoint => $data) {
    if ($data) {
        echo "   {$endpoint}: " . count($data) . " items\n";
    } else {
        echo "   {$endpoint}: Failed to fetch\n";
    }
}

echo "\n✅ All examples completed!\n";
echo "\n💡 Key Takeaway: Concurrent requests are much faster than sequential!\n";
