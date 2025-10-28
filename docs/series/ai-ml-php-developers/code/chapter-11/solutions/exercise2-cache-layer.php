<?php

declare(strict_types=1);

/**
 * Exercise 2 Solution: Caching Layer for ML Predictions
 * 
 * This solution demonstrates:
 * - File-based caching with TTL (time-to-live)
 * - Cache key generation from input hash
 * - Cache hit/miss tracking
 * - Cache statistics and hit rate calculation
 */

class PredictionCache
{
    private string $cacheDir;
    private int $ttl;
    private int $hits = 0;
    private int $misses = 0;

    public function __construct(
        string $cacheDir = __DIR__ . '/cache',
        int $ttl = 3600 // 1 hour
    ) {
        $this->cacheDir = $cacheDir;
        $this->ttl = $ttl;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get a cached prediction or null if not found/expired.
     */
    public function get(string $text): ?array
    {
        $key = $this->generateKey($text);
        $cachePath = $this->getCachePath($key);

        if (!file_exists($cachePath)) {
            $this->misses++;
            return null;
        }

        $cacheData = json_decode(file_get_contents($cachePath), true);

        // Check if expired
        if (time() - $cacheData['cached_at'] > $this->ttl) {
            unlink($cachePath);
            $this->misses++;
            return null;
        }

        $this->hits++;
        return $cacheData;
    }

    /**
     * Store a prediction in cache.
     */
    public function set(string $text, array $prediction): void
    {
        $key = $this->generateKey($text);
        $cachePath = $this->getCachePath($key);

        $cacheData = [
            'text' => $text,
            'sentiment' => $prediction['sentiment'],
            'confidence' => $prediction['confidence'],
            'cached_at' => time()
        ];

        file_put_contents($cachePath, json_encode($cacheData));
    }

    /**
     * Generate cache key from text.
     */
    private function generateKey(string $text): string
    {
        return md5(strtolower(trim($text)));
    }

    /**
     * Get full cache file path.
     */
    private function getCachePath(string $key): string
    {
        return $this->cacheDir . '/' . $key . '.json';
    }

    /**
     * Get cache statistics.
     */
    public function getStats(): array
    {
        $total = $this->hits + $this->misses;
        $hitRate = $total > 0 ? ($this->hits / $total) * 100 : 0;

        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'total' => $total,
            'hit_rate' => round($hitRate, 2),
            'cache_size' => $this->getCacheSize()
        ];
    }

    /**
     * Get cache size (number of files).
     */
    private function getCacheSize(): int
    {
        $files = glob($this->cacheDir . '/*.json');
        return count($files);
    }

    /**
     * Clear expired cache entries.
     */
    public function clearExpired(): int
    {
        $cleared = 0;
        $files = glob($this->cacheDir . '/*.json');

        foreach ($files as $file) {
            $cacheData = json_decode(file_get_contents($file), true);
            if (time() - $cacheData['cached_at'] > $this->ttl) {
                unlink($file);
                $cleared++;
            }
        }

        return $cleared;
    }

    /**
     * Clear all cache.
     */
    public function clearAll(): int
    {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
        return count($files);
    }
}

/**
 * Cached Sentiment Analyzer
 */
class CachedSentimentAnalyzer
{
    public function __construct(
        private PredictionCache $cache,
        private string $pythonPath = 'python3',
        private string $scriptDir = __DIR__ . '/../03-sentiment-analysis'
    ) {}

    /**
     * Predict sentiment with caching.
     */
    public function predict(string $text): array
    {
        // Try cache first
        $cached = $this->cache->get($text);
        if ($cached !== null) {
            $cached['from_cache'] = true;
            return $cached;
        }

        // Cache miss - call ML model
        $start = microtime(true);

        $data = json_encode(['text' => $text]);
        $escaped = escapeshellarg($data);
        $command = "{$this->pythonPath} {$this->scriptDir}/predict.py {$escaped}";

        $output = shell_exec($command);
        if ($output === null) {
            throw new RuntimeException('Failed to execute prediction script');
        }

        $result = json_decode($output, true);
        if (isset($result['error'])) {
            throw new RuntimeException("Prediction error: {$result['error']}");
        }

        $duration = microtime(true) - $start;
        $result['prediction_time'] = round($duration * 1000, 2);
        $result['from_cache'] = false;

        // Store in cache
        $this->cache->set($text, $result);

        return $result;
    }
}

// Demonstration
try {
    echo "=== Exercise 2: Caching Layer Solution ===\n\n";

    // Initialize cache and analyzer
    $cache = new PredictionCache(
        cacheDir: __DIR__ . '/cache',
        ttl: 3600 // 1 hour
    );

    $analyzer = new CachedSentimentAnalyzer($cache);

    // Test reviews
    $reviews = [
        "This product is amazing! Love it!",
        "Terrible quality. Very disappointed.",
        "It's okay. Nothing special.",
        "This product is amazing! Love it!", // Duplicate
        "Best purchase ever!",
        "Terrible quality. Very disappointed.", // Duplicate
    ];

    echo "First run (cache misses expected):\n";
    echo str_repeat('-', 70) . "\n";

    foreach ($reviews as $i => $review) {
        $result = $analyzer->predict($review);
        $source = $result['from_cache'] ? '💾 CACHED' : '🔮 ML';
        $time = $result['from_cache'] ? '~1ms' : "{$result['prediction_time']}ms";

        echo ($i + 1) . ". {$source} ({$time}) - {$result['sentiment']}: \"{$review}\"\n";
    }

    $stats1 = $cache->getStats();
    echo "\nCache Stats (Run 1):\n";
    echo "  Hits: {$stats1['hits']}\n";
    echo "  Misses: {$stats1['misses']}\n";
    echo "  Hit Rate: {$stats1['hit_rate']}%\n";
    echo "  Cache Size: {$stats1['cache_size']} entries\n\n";

    // Second run - should hit cache for duplicates
    echo str_repeat('=', 70) . "\n\n";
    echo "Second run (cache hits expected for repeated texts):\n";
    echo str_repeat('-', 70) . "\n";

    // Reset counters for second run demonstration
    $cache2 = new PredictionCache(cacheDir: __DIR__ . '/cache', ttl: 3600);
    $analyzer2 = new CachedSentimentAnalyzer($cache2);

    foreach ($reviews as $i => $review) {
        $result = $analyzer2->predict($review);
        $source = $result['from_cache'] ? '💾 CACHED' : '🔮 ML';
        $time = $result['from_cache'] ? '~1ms' : "{$result['prediction_time']}ms";

        echo ($i + 1) . ". {$source} ({$time}) - {$result['sentiment']}: \"{$review}\"\n";
    }

    $stats2 = $cache2->getStats();
    echo "\nCache Stats (Run 2):\n";
    echo "  Hits: {$stats2['hits']}\n";
    echo "  Misses: {$stats2['misses']}\n";
    echo "  Hit Rate: {$stats2['hit_rate']}%\n";
    echo "  Cache Size: {$stats2['cache_size']} entries\n\n";

    echo "✅ Exercise 2 Complete!\n\n";

    echo "Performance Improvement:\n";
    echo "  Without cache: ~40-50ms per prediction\n";
    echo "  With cache: ~1ms per prediction (40-50x faster!)\n\n";

    echo "What we learned:\n";
    echo "  ✓ Caching dramatically improves response time\n";
    echo "  ✓ MD5 hashing creates consistent cache keys\n";
    echo "  ✓ TTL prevents stale predictions\n";
    echo "  ✓ Hit rate tracking measures cache effectiveness\n";
    echo "  ✓ File-based cache is simple and effective for small-scale\n\n";

    echo "For production:\n";
    echo "  → Use Redis or Memcached for distributed caching\n";
    echo "  → Monitor hit rates and adjust TTL\n";
    echo "  → Implement cache warming for common queries\n";
    echo "  → Add cache invalidation on model updates\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";

    if (strpos($e->getMessage(), 'Model files not found') !== false) {
        echo "\nNote: Train the sentiment model first:\n";
        echo "  cd ../03-sentiment-analysis\n";
        echo "  php analyze.php\n";
    }

    exit(1);
}


