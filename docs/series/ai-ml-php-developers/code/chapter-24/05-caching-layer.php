<?php

declare(strict_types=1);

/**
 * Caching layer for ML prediction results
 */
final class PredictionCache
{
    private const DEFAULT_TTL = 3600; // 1 hour
    private const CACHE_VERSION = 'v1';

    public function __construct(
        private readonly Redis $redis,
        private readonly string $prefix = 'cache:prediction',
    ) {}

    /**
     * Generate cache key from input features
     */
    private function generateKey(array $features, string $modelName): string
    {
        // Sort keys for consistent hashing
        ksort($features);

        $hash = md5(json_encode($features, JSON_THROW_ON_ERROR));

        return "{$this->prefix}:{self::CACHE_VERSION}:{$modelName}:{$hash}";
    }

    /**
     * Get cached prediction if available
     */
    public function get(array $features, string $modelName): ?array
    {
        $key = $this->generateKey($features, $modelName);

        $cached = $this->redis->get($key);

        if ($cached === false) {
            return null;
        }

        $data = json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

        // Track cache hit
        $this->redis->incr('metrics:cache:hits');

        return $data;
    }

    /**
     * Store prediction result in cache
     */
    public function set(array $features, string $modelName, array $result, int $ttl = self::DEFAULT_TTL): bool
    {
        $key = $this->generateKey($features, $modelName);

        $data = array_merge($result, [
            'cached_at' => time(),
            'features' => $features,
        ]);

        $success = $this->redis->setEx(
            $key,
            $ttl,
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        if ($success) {
            $this->redis->incr('metrics:cache:writes');
        }

        return $success;
    }

    /**
     * Check if prediction is cached
     */
    public function has(array $features, string $modelName): bool
    {
        $key = $this->generateKey($features, $modelName);

        return $this->redis->exists($key) > 0;
    }

    /**
     * Invalidate cache for specific features or entire model
     */
    public function invalidate(?array $features = null, ?string $modelName = null): int
    {
        if ($features !== null && $modelName !== null) {
            // Invalidate specific prediction
            $key = $this->generateKey($features, $modelName);
            return $this->redis->del($key);
        }

        if ($modelName !== null) {
            // Invalidate all predictions for a model
            $pattern = "{$this->prefix}:{self::CACHE_VERSION}:{$modelName}:*";
            return $this->deleteByPattern($pattern);
        }

        // Invalidate all cache
        $pattern = "{$this->prefix}:*";
        return $this->deleteByPattern($pattern);
    }

    private function deleteByPattern(string $pattern): int
    {
        $deleted = 0;
        $iterator = null;

        while ($keys = $this->redis->scan($iterator, $pattern, 100)) {
            foreach ($keys as $key) {
                $this->redis->del($key);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $hits = (int) $this->redis->get('metrics:cache:hits') ?: 0;
        $writes = (int) $this->redis->get('metrics:cache:writes') ?: 0;
        $misses = $writes; // Approximate misses as cache writes

        $hitRate = $hits + $misses > 0
            ? round(($hits / ($hits + $misses)) * 100, 2)
            : 0;

        return [
            'hits' => $hits,
            'misses' => $misses,
            'writes' => $writes,
            'hit_rate' => $hitRate,
        ];
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    $cache = new PredictionCache($redis);

    $features = ['feature1' => 1.5, 'feature2' => 2.3];
    $modelName = 'classifier-v1';

    // Check cache
    $cached = $cache->get($features, $modelName);

    if ($cached === null) {
        echo "Cache miss - running inference...\n";

        // Simulate ML inference
        $result = [
            'prediction' => 1,
            'confidence' => 0.87,
            'inference_time' => 0.245,
        ];

        // Cache result
        $cache->set($features, $modelName, $result);
        echo "Result cached\n";
    } else {
        echo "Cache hit!\n";
        echo "Cached result: " . json_encode($cached, JSON_PRETTY_PRINT) . "\n";
    }

    // Show stats
    echo "\nCache stats: " . json_encode($cache->getStats(), JSON_PRETTY_PRINT) . "\n";
}
