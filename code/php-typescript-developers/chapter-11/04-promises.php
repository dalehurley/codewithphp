<?php
declare(strict_types=1);

/**
 * ReactPHP Promises
 *
 * Promise patterns similar to JavaScript Promises.
 */

require 'vendor/autoload.php';

use React\Promise\Promise;
use React\Promise\Deferred;
use function React\Promise\all;
use function React\Promise\resolve;
use function React\Promise\reject;

echo "=== ReactPHP Promises ===\n\n";

// Basic promise
echo "1. Basic Promise\n";
$promise = new Promise(function ($resolve, $reject) {
    echo "  - Executing promise...\n";
    $resolve('Success!');
});

$promise->then(function ($value) {
    echo "  - Resolved: {$value}\n";
});

// Deferred (more common pattern)
echo "\n2. Deferred Promise\n";
$deferred = new Deferred();

$deferred->promise()->then(function ($value) {
    echo "  - User data: " . json_encode($value) . "\n";
});

// Simulate async operation
$deferred->resolve(['id' => 1, 'name' => 'Alice']);

// Promise chaining
echo "\n3. Promise Chaining\n";
function fetchUser(int $id): Promise {
    return resolve(['id' => $id, 'name' => 'User ' . $id]);
}

function fetchPosts(int $userId): Promise {
    return resolve([
        ['id' => 1, 'title' => 'Post 1', 'userId' => $userId],
        ['id' => 2, 'title' => 'Post 2', 'userId' => $userId],
    ]);
}

fetchUser(1)
    ->then(function ($user) {
        echo "  - Fetched user: {$user['name']}\n";
        return fetchPosts($user['id']);
    })
    ->then(function ($posts) {
        echo "  - Fetched " . count($posts) . " posts\n";
    });

// Promise.all equivalent
echo "\n4. Promise::all (parallel execution)\n";
$promises = [
    resolve('Task 1 complete'),
    resolve('Task 2 complete'),
    resolve('Task 3 complete'),
];

all($promises)->then(function ($results) {
    echo "  - All tasks completed:\n";
    foreach ($results as $i => $result) {
        echo "    " . ($i + 1) . ". {$result}\n";
    }
});

// Error handling
echo "\n5. Error Handling\n";
reject(new Exception('Something went wrong'))
    ->then(function ($value) {
        echo "  - Success: {$value}\n";
    })
    ->catch(function ($error) {
        echo "  - Error caught: {$error->getMessage()}\n";
    });

// Promise with timeout simulation
echo "\n6. Async Simulation\n";
function asyncOperation(string $name, int $delay): Promise {
    $deferred = new Deferred();

    echo "  - [{$name}] Started\n";

    // In real app, this would be actual async I/O
    // For demo, we resolve immediately
    $deferred->resolve("Result from {$name}");

    return $deferred->promise();
}

all([
    asyncOperation('API Call 1', 1),
    asyncOperation('API Call 2', 2),
    asyncOperation('API Call 3', 3),
])->then(function ($results) {
    echo "\n  - All operations completed:\n";
    foreach ($results as $result) {
        echo "    • {$result}\n";
    }
});

echo "\n✅ All promise examples completed!\n";
