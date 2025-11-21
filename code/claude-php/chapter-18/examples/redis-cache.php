<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Caching\CacheManager;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;
use Predis\ClaudePhp as RedisClient;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Caching - Redis Cache\n\n";

// Setup Redis
try {
    $redis = new RedisClient([
        'scheme' => 'tcp',
        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    ]);
    $redis->ping();
    echo "✓ Connected to Redis\n\n";
} catch (\Exception $e) {
    die("✗ Redis not available: {$e->getMessage()}\n");
}

$cache = new CacheManager($redis, ttl: 3600);
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

function getCachedResponse($prompt, $cache, $client)
{
    $cacheKey = $cache->generateKey($prompt);

    // Check cache
    if ($cached = $cache->get($cacheKey)) {
        echo "✓ Cache hit!\n";
        return $cached;
    }

    echo "✗ Cache miss, calling API...\n";

    // Call API
    $response = $client->messages()->create(
        model: 'claude-sonnet-4-5',
        maxTokens: 1024,
        messages: [['role' => 'user', 'content' => $prompt]]
    );

    // Extract response data to cache
    $result = [
        'content' => $response->content,
        'usage' => (array) $response->usage,
        'model' => $response->model,
        'id' => $response->id
    ];

    // Cache response
    $cache->set($cacheKey, $result);

    return $result;
}

// First request - cache miss
echo "Request 1: What is PHP?\n";
$result1 = getCachedResponse('What is PHP?', $cache, $client);
$text1 = $result1['content'][0]['text'] ?? '';
echo "Response: " . substr($text1, 0, 100) . "...\n\n";

// Second request - cache hit
echo "Request 2: What is PHP? (same question)\n";
$result2 = getCachedResponse('What is PHP?', $cache, $client);
$text2 = $result2['content'][0]['text'] ?? '';
echo "Response: " . substr($text2, 0, 100) . "...\n\n";

// Stats
$stats = $cache->getStats();
echo "Cache stats:\n";
print_r($stats);
