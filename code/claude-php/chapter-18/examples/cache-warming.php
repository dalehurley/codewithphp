<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Caching\CacheManager;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;
use Predis\ClaudePhp as RedisClient;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Caching - Cache Warming\n\n";

try {
    $redis = new RedisClient([
        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    ]);
    $redis->ping();
} catch (\Exception $e) {
    die("✗ Redis not available\n");
}

$cache = new CacheManager($redis);
// ClaudePhp setup for reference
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY'] ?? 'sk-dummy'
);

// Common questions to pre-cache
$commonQuestions = [
    'What are your business hours?',
    'How do I reset my password?',
    'What payment methods do you accept?',
    'How can I contact support?',
    'What is your return policy?',
];

echo "Warming cache with common questions...\n\n";

foreach ($commonQuestions as $i => $question) {
    $cacheKey = $cache->generateKey($question);

    if ($cache->has($cacheKey)) {
        echo ($i + 1) . ". ✓ Already cached: {$question}\n";
        continue;
    }

    echo ($i + 1) . ". ○ Caching: {$question}\n";

    // Simulate API response (in production, call actual API)
    $mockResponse = [
        'content' => [['type' => 'text', 'text' => "Answer to: {$question}"]],
        'usage' => ['input_tokens' => 10, 'output_tokens' => 20],
        'model' => 'claude-mock',
        'id' => 'msg_mock_' . uniqid()
    ];

    $cache->set($cacheKey, $mockResponse, 86400); // Cache for 24 hours

    usleep(100000); // Rate limiting delay
}

echo "\nCache warming complete!\n";
$stats = $cache->getStats();
echo "Total cached entries: {$stats['total_entries']}\n";

echo "\nBenefits of cache warming:\n";
echo "- Faster response times for common queries\n";
echo "- Reduced API costs\n";
echo "- Better user experience\n";
echo "- Lower latency during peak hours\n";
