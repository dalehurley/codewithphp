<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Caching\CacheManager;
use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Predis\Client as RedisClient;

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
$client = new Client(['base_uri' => 'https://api.anthropic.com']);

function getCachedResponse($prompt, $cache, $client, $apiKey)
{
    $cacheKey = $cache->generateKey($prompt);

    // Check cache
    if ($cached = $cache->get($cacheKey)) {
        echo "✓ Cache hit!\n";
        return $cached;
    }

    echo "✗ Cache miss, calling API...\n";

    // Call API
    $response = $client->post('/v1/messages', [
        'headers' => [
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ],
        'json' => [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ],
    ]);

    $result = json_decode($response->getBody()->getContents(), true);

    // Cache response
    $cache->set($cacheKey, $result);

    return $result;
}

// First request - cache miss
echo "Request 1: What is PHP?\n";
$result1 = getCachedResponse('What is PHP?', $cache, $client, $_ENV['ANTHROPIC_API_KEY']);
echo "Response: " . substr($result1['content'][0]['text'], 0, 100) . "...\n\n";

// Second request - cache hit
echo "Request 2: What is PHP? (same question)\n";
$result2 = getCachedResponse('What is PHP?', $cache, $client, $_ENV['ANTHROPIC_API_KEY']);
echo "Response: " . substr($result2['content'][0]['text'], 0, 100) . "...\n\n";

// Stats
$stats = $cache->getStats();
echo "Cache stats:\n";
print_r($stats);
