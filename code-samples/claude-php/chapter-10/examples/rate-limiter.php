<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ErrorHandling\RateLimiter;
use Dotenv\Dotenv;
use Predis\Client as RedisClient;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Claude API - Rate Limiting\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Try to connect to Redis (optional)
$redis = null;
try {
    $redis = new RedisClient([
        'scheme' => 'tcp',
        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => (int) ($_ENV['REDIS_DATABASE'] ?? 0),
    ]);
    $redis->ping();
    echo "✓ Connected to Redis for distributed rate limiting\n\n";
} catch (Exception $e) {
    echo "⚠ Redis not available, using local rate limiting\n";
    echo "  ({$e->getMessage()})\n\n";
    $redis = null;
}

// Initialize rate limiter
$limiter = new RateLimiter(
    requestsPerMinute: (int) ($_ENV['RATE_LIMIT_REQUESTS_PER_MINUTE'] ?? 10),
    requestsPerHour: (int) ($_ENV['RATE_LIMIT_REQUESTS_PER_HOUR'] ?? 100),
    redis: $redis
);

// Example 1: Basic rate limiting
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic Rate Limiting\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$userId = 'user-123';

echo "Making 15 rapid requests for user: {$userId}\n";
echo "Rate limit: 10 requests/minute\n\n";

$allowed = 0;
$rejected = 0;

for ($i = 1; $i <= 15; $i++) {
    if ($limiter->allowRequest($userId)) {
        echo "✓ Request {$i}: Allowed\n";
        $allowed++;
    } else {
        echo "✗ Request {$i}: Rate limit exceeded\n";
        $rejected++;
    }

    usleep(50000); // 50ms delay
}

echo "\nResults: {$allowed} allowed, {$rejected} rejected\n";

// Show current usage
$usage = $limiter->getUsage($userId);
echo "\nCurrent Usage:\n";
print_r($usage);

// Example 2: Multiple users
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Per-User Rate Limiting\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$users = ['alice', 'bob', 'charlie'];

echo "Testing 5 requests for each user:\n\n";

foreach ($users as $user) {
    echo "User: {$user}\n";

    for ($i = 1; $i <= 5; $i++) {
        if ($limiter->allowRequest($user)) {
            echo "  ✓ Request {$i}: Allowed\n";
        } else {
            echo "  ✗ Request {$i}: Rejected\n";
        }
    }

    $usage = $limiter->getUsage($user);
    echo "  Usage: {$usage['requests_this_minute']}/{$usage['minute_limit']} per minute, ";
    echo "{$usage['requests_this_hour']}/{$usage['hour_limit']} per hour\n\n";
}

// Example 3: Checking limits before making requests
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Check Limits Before Requesting\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$apiKey = 'api-key-xyz';

function makeAPIRequest(RateLimiter $limiter, string $key, string $prompt): void
{
    // Check rate limit first
    $usage = $limiter->getUsage($key);

    if ($usage['minute_remaining'] <= 0) {
        echo "⚠ Rate limit reached. Wait before making more requests.\n";
        echo "   Minute: {$usage['requests_this_minute']}/{$usage['minute_limit']}\n";
        return;
    }

    if (!$limiter->allowRequest($key)) {
        echo "✗ Request rejected by rate limiter\n";
        return;
    }

    // Simulate API call
    echo "✓ API Request: '{$prompt}'\n";
    echo "   Remaining: {$usage['minute_remaining']} this minute\n";
}

for ($i = 1; $i <= 12; $i++) {
    echo "Request {$i}: ";
    makeAPIRequest($limiter, $apiKey, "Prompt {$i}");
}

// Example 4: Resetting rate limits
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Reset Rate Limits\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testUser = 'test-user';

// Fill up the rate limit
for ($i = 1; $i <= 10; $i++) {
    $limiter->allowRequest($testUser);
}

$usageBefore = $limiter->getUsage($testUser);
echo "Usage before reset:\n";
echo "  Minute: {$usageBefore['requests_this_minute']}/{$usageBefore['minute_limit']}\n";
echo "  Remaining: {$usageBefore['minute_remaining']}\n\n";

// Reset limits
echo "Resetting rate limit for {$testUser}...\n\n";
$limiter->reset($testUser);

$usageAfter = $limiter->getUsage($testUser);
echo "Usage after reset:\n";
echo "  Minute: {$usageAfter['requests_this_minute']}/{$usageAfter['minute_limit']}\n";
echo "  Remaining: {$usageAfter['minute_remaining']}\n";

// Example 5: Distributed rate limiting (Redis)
if ($redis) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Example 5: Distributed Rate Limiting with Redis\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $globalKey = 'global-api';

    echo "Simulating requests from multiple servers:\n\n";

    for ($i = 1; $i <= 15; $i++) {
        $server = "server-" . (($i % 3) + 1);

        if ($limiter->allowRequest($globalKey)) {
            echo "✓ Request {$i} from {$server}: Allowed\n";
        } else {
            echo "✗ Request {$i} from {$server}: Rejected\n";
        }

        usleep(100000); // 100ms
    }

    $usage = $limiter->getUsage($globalKey);
    echo "\nGlobal usage across all servers:\n";
    echo "  Minute: {$usage['requests_this_minute']}/{$usage['minute_limit']}\n";
    echo "  Hour: {$usage['requests_this_hour']}/{$usage['hour_limit']}\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Rate Limiting Best Practices:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Check rate limits BEFORE making API calls\n";
echo "2. Use Redis for distributed rate limiting across servers\n";
echo "3. Implement both per-minute and per-hour limits\n";
echo "4. Show remaining quota to users\n";
echo "5. Queue requests when rate limit is reached\n";
echo "6. Add buffer to avoid hitting exact API limits\n";
echo "7. Monitor rate limit usage patterns\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
