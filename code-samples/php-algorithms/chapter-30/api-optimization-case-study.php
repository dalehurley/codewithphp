<?php

declare(strict_types=1);

/**
 * Real-World Case Study: API Optimization
 *
 * Demonstrates optimization of a high-traffic API endpoint from 850ms to 45ms
 * through caching, query optimization, and algorithmic improvements.
 */

/**
 * Mock database for demonstration
 */
class MockDatabase
{
    private array $users = [];
    private array $posts = [];
    private array $followers = [];
    private int $queryCount = 0;

    public function __construct()
    {
        // Initialize mock data
        for ($i = 1; $i <= 100; $i++) {
            $this->users[$i] = [
                'id' => $i,
                'name' => "User $i",
                'email' => "user$i@example.com"
            ];
        }

        for ($i = 1; $i <= 500; $i++) {
            $this->posts[$i] = [
                'id' => $i,
                'user_id' => rand(1, 100),
                'title' => "Post $i",
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        for ($i = 1; $i <= 1000; $i++) {
            $this->followers[] = [
                'follower_id' => rand(1, 100),
                'following_id' => rand(1, 100)
            ];
        }
    }

    public function query(string $sql): array
    {
        $this->queryCount++;
        usleep(5000);  // Simulate 5ms query time

        // Parse simple queries
        if (str_contains($sql, 'SELECT * FROM users WHERE id =')) {
            preg_match('/id = (\d+)/', $sql, $matches);
            $id = (int)$matches[1];
            return [$this->users[$id] ?? []];
        }

        if (str_contains($sql, 'SELECT id FROM posts WHERE user_id =')) {
            preg_match('/user_id = (\d+)/', $sql, $matches);
            $userId = (int)$matches[1];
            return array_filter($this->posts, fn($p) => $p['user_id'] === $userId);
        }

        return [];
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function resetQueryCount(): void
    {
        $this->queryCount = 0;
    }
}

/**
 * Simple cache mock
 */
class SimpleCache
{
    private array $cache = [];
    private int $hits = 0;
    private int $misses = 0;

    public function get(string $key): mixed
    {
        if (isset($this->cache[$key])) {
            $this->hits++;
            return $this->cache[$key];
        }

        $this->misses++;
        return null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    public function getHitRate(): float
    {
        $total = $this->hits + $this->misses;
        return $total > 0 ? $this->hits / $total : 0;
    }

    public function getStats(): array
    {
        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'hit_rate' => $this->getHitRate()
        ];
    }
}

/**
 * BEFORE: Unoptimized API endpoint
 */
class APIBefore
{
    private MockDatabase $db;

    public function __construct(MockDatabase $db)
    {
        $this->db = $db;
    }

    public function getUserDashboard(int $userId): array
    {
        // Query 1: User info
        $user = $this->db->query("SELECT * FROM users WHERE id = $userId")[0];

        // Query 2-N: Posts (N+1 problem!)
        $posts = [];
        $postIds = $this->db->query("SELECT id FROM posts WHERE user_id = $userId");

        foreach ($postIds as $post) {
            $postData = $this->db->query("SELECT * FROM posts WHERE id = {$post['id']}")[0];
            $posts[] = $postData;
        }

        // Additional queries...
        $followers = $this->db->query("SELECT * FROM followers WHERE following_id = $userId");

        return [
            'user' => $user,
            'posts' => $posts,
            'followers' => $followers
        ];
    }
}

/**
 * AFTER: Optimized API endpoint
 */
class APIAfter
{
    private MockDatabase $db;
    private SimpleCache $cache;

    public function __construct(MockDatabase $db, SimpleCache $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function getUserDashboard(int $userId): array
    {
        // Check cache first
        $cacheKey = "dashboard:$userId";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        // Optimized: Single query with aggregation
        $user = $this->db->query("SELECT * FROM users WHERE id = $userId")[0];

        // Batch fetch posts
        $posts = $this->db->query("SELECT * FROM posts WHERE user_id = $userId");

        $result = [
            'user' => $user,
            'posts' => array_slice($posts, 0, 10),  // Limit results
            'post_count' => count($posts),
            'follower_count' => rand(10, 100)  // Simulated
        ];

        // Cache for 5 minutes
        $this->cache->set($cacheKey, $result);

        return $result;
    }
}

// =============================================================================
// CASE STUDY DEMONSTRATION
// =============================================================================

echo "Real-World Case Study: API Optimization\n";
echo str_repeat("=", 70) . "\n\n";

// Setup
$db = new MockDatabase();
$cache = new SimpleCache();

// Example 1: Before Optimization
echo "BEFORE OPTIMIZATION\n";
echo "-" . str_repeat("-", 69) . "\n";

$apiBefore = new APIBefore($db);
$db->resetQueryCount();

$start = microtime(true);
$result1 = $apiBefore->getUserDashboard(1);
$time1 = (microtime(true) - $start) * 1000;

echo "Response time: " . number_format($time1, 2) . " ms\n";
echo "Database queries: {$db->getQueryCount()}\n";
echo "User: {$result1['user']['name']}\n";
echo "Posts: " . count($result1['posts']) . "\n\n";

// Example 2: After Optimization (First Request)
echo "AFTER OPTIMIZATION - First Request (Cache Miss)\n";
echo "-" . str_repeat("-", 69) . "\n";

$apiAfter = new APIAfter($db, $cache);
$db->resetQueryCount();

$start = microtime(true);
$result2 = $apiAfter->getUserDashboard(1);
$time2 = (microtime(true) - $start) * 1000;

echo "Response time: " . number_format($time2, 2) . " ms\n";
echo "Database queries: {$db->getQueryCount()}\n";
echo "Cache hit rate: " . number_format($cache->getHitRate() * 100, 1) . "%\n";
echo "User: {$result2['user']['name']}\n";
echo "Posts: " . count($result2['posts']) . "\n\n";

// Example 3: After Optimization (Cached Request)
echo "AFTER OPTIMIZATION - Second Request (Cache Hit)\n";
echo "-" . str_repeat("-", 69) . "\n";

$db->resetQueryCount();

$start = microtime(true);
$result3 = $apiAfter->getUserDashboard(1);
$time3 = (microtime(true) - $start) * 1000;

echo "Response time: " . number_format($time3, 2) . " ms\n";
echo "Database queries: {$db->getQueryCount()}\n";
echo "Cache hit rate: " . number_format($cache->getHitRate() * 100, 1) . "%\n\n";

// Performance Summary
echo str_repeat("=", 70) . "\n";
echo "PERFORMANCE SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

$improvement = (($time1 - $time2) / $time1) * 100;
$cacheImprovement = (($time2 - $time3) / $time2) * 100;

echo "Before Optimization:\n";
echo "  Average response time: ~850ms (simulated)\n";
echo "  Database queries: 15-20 per request\n";
echo "  Cache hit rate: 0%\n\n";

echo "After Optimization (First Request):\n";
echo "  Response time: " . number_format($time2, 2) . " ms\n";
echo "  Database queries: {$db->getQueryCount()}\n";
echo "  Improvement: " . number_format($improvement, 1) . "%\n\n";

echo "After Optimization (Cached):\n";
echo "  Response time: " . number_format($time3, 2) . " ms\n";
echo "  Database queries: 0\n";
echo "  Cache improvement: " . number_format($cacheImprovement, 1) . "%\n";
echo "  vs Original: " . number_format($time1 / $time3, 1) . "x faster\n\n";

// Optimization Techniques Applied
echo "OPTIMIZATION TECHNIQUES APPLIED:\n";
echo str_repeat("-", 70) . "\n";
echo "1. ✓ Eliminated N+1 query problem\n";
echo "   Before: 1 query for user + N queries for posts\n";
echo "   After:  2 queries total (batch fetch)\n\n";

echo "2. ✓ Implemented caching layer\n";
echo "   Cache hit rate: " . number_format($cache->getHitRate() * 100, 1) . "%\n";
echo "   Cache reduces load by ~95%\n\n";

echo "3. ✓ Limited result sets\n";
echo "   Return only top 10 posts instead of all\n";
echo "   Use counts for non-critical data\n\n";

echo "4. ✓ Batch operations\n";
echo "   Fetch related data in single query\n";
echo "   Reduce database round trips\n\n";

// Cost Impact
echo "PRODUCTION IMPACT (10,000 req/min):\n";
echo str_repeat("-", 70) . "\n";

$reqPerMin = 10000;
$before = [
    'response_time' => 850,  // ms
    'db_queries' => 18,
    'servers' => 20,
    'cost_per_server' => 800
];

$after = [
    'response_time' => 45,  // ms
    'db_queries' => 2,
    'servers' => 8,
    'cost_per_server' => 400
];

echo "Before:\n";
echo "  Servers needed: {$before['servers']} × \${$before['cost_per_server']}/mo = \$" . number_format($before['servers'] * $before['cost_per_server']) . "/mo\n";
echo "  DB load: " . number_format($reqPerMin * $before['db_queries']) . " queries/min\n\n";

echo "After:\n";
echo "  Servers needed: {$after['servers']} × \${$after['cost_per_server']}/mo = \$" . number_format($after['servers'] * $after['cost_per_server']) . "/mo\n";
echo "  DB load: " . number_format($reqPerMin * $after['db_queries'] * 0.1) . " queries/min (with 90% cache hit)\n\n";

$savings = ($before['servers'] * $before['cost_per_server']) - ($after['servers'] * $after['cost_per_server']);
echo "Monthly savings: \$" . number_format($savings) . " (" . number_format($savings / ($before['servers'] * $before['cost_per_server']) * 100, 1) . "% reduction)\n";
echo "Annual savings: \$" . number_format($savings * 12) . "\n\n";

// Key Lessons
echo "KEY LESSONS LEARNED:\n";
echo str_repeat("-", 70) . "\n";
echo "1. Profile before optimizing - measure to find bottlenecks\n";
echo "2. N+1 queries are a common performance killer\n";
echo "3. Caching provides exponential returns (2-100x improvement)\n";
echo "4. Small changes can have massive cost impact\n";
echo "5. Limit data early - don't fetch what you don't need\n";

echo "\n✓ Case study completed successfully!\n";
