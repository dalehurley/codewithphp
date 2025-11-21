<?php

declare(strict_types=1);

namespace App\Optimization;

class ClaudeCache
{
    public function __construct(
        private readonly object $redis,
        private readonly int $defaultTtl = 3600
    ) {}

    /**
     * Get cached response or execute Claude request
     */
    public function remember(
        string $cacheKey,
        callable $callback,
        ?int $ttl = null
    ): mixed {
        // Try to get from cache
        $cached = $this->get($cacheKey);

        if ($cached !== null) {
            // Cache hit - update stats
            $this->incrementStat('hits');
            return $cached;
        }

        // Cache miss - execute callback
        $this->incrementStat('misses');
        $result = $callback();

        // Store in cache
        $this->set($cacheKey, $result, $ttl ?? $this->defaultTtl);

        return $result;
    }

    /**
     * Generate cache key from prompt and parameters
     */
    public function generateKey(string $prompt, array $params = []): string
    {
        // Include model and relevant params in key
        $keyData = [
            'prompt' => $prompt,
            'model' => $params['model'] ?? 'default',
            'system' => $params['system'] ?? '',
            'temperature' => $params['temperature'] ?? 1.0,
        ];

        return 'claude:cache:' . hash('sha256', json_encode($keyData));
    }

    /**
     * Semantic caching - find similar cached prompts
     */
    public function findSimilar(string $prompt, float $threshold = 0.9): ?string
    {
        // Get all cache keys (expensive - use sparingly)
        $keys = $this->redis->keys('claude:cache:*');

        foreach ($keys as $key) {
            $cached = $this->redis->get($key);
            if (!$cached) continue;

            $data = json_decode($cached, true);
            $cachedPrompt = $data['prompt'] ?? '';

            // Calculate similarity (simple version - use proper algorithm in production)
            $similarity = $this->calculateSimilarity($prompt, $cachedPrompt);

            if ($similarity >= $threshold) {
                return $key;
            }
        }

        return null;
    }

    private function get(string $key): mixed
    {
        $value = $this->redis->get($key);

        if ($value === false) {
            return null;
        }

        $data = json_decode($value, true);

        // Check if expired (additional layer beyond Redis TTL)
        if (isset($data['expires_at']) && $data['expires_at'] < time()) {
            $this->redis->del($key);
            return null;
        }

        return $data['value'];
    }

    private function set(string $key, mixed $value, int $ttl): void
    {
        $data = [
            'value' => $value,
            'cached_at' => time(),
            'expires_at' => time() + $ttl,
        ];

        $this->redis->setex($key, $ttl, json_encode($data));
    }

    private function incrementStat(string $stat): void
    {
        $this->redis->incr("claude:cache:stats:$stat");
    }

    private function calculateSimilarity(string $a, string $b): float
    {
        // Simple similarity - use levenshtein or cosine similarity in production
        similar_text($a, $b, $percent);
        return $percent / 100;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $hits = (int) $this->redis->get('claude:cache:stats:hits') ?: 0;
        $misses = (int) $this->redis->get('claude:cache:stats:misses') ?: 0;
        $total = $hits + $misses;

        return [
            'hits' => $hits,
            'misses' => $misses,
            'total' => $total,
            'hit_rate' => $total > 0 ? round($hits / $total * 100, 2) : 0,
        ];
    }

    /**
     * Warm cache with common queries
     */
    public function warmCache(array $commonQueries): void
    {
        foreach ($commonQueries as $query) {
            $key = $this->generateKey($query['prompt'], $query['params'] ?? []);

            // Check if already cached
            if ($this->get($key) !== null) {
                continue;
            }

            // Execute and cache
            $result = $query['callback']();
            $this->set($key, $result, $query['ttl'] ?? $this->defaultTtl);
        }
    }
}
