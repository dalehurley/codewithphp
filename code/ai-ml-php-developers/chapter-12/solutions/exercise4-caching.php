<?php

declare(strict_types=1);

require_once __DIR__ . '/../02-tensorflow-client.php';
require_once __DIR__ . '/../03-image-preprocessor.php';
require_once __DIR__ . '/../04-image-classifier.php';

/**
 * Exercise 4 Solution: Production Caching System
 * 
 * Implements file-based caching with content hashing,
 * expiration, and performance tracking.
 */
final class CachedImageClassifier
{
    private int $hits = 0;
    private int $misses = 0;

    public function __construct(
        private ImageClassifier $classifier,
        private string $cacheDir = '/tmp/predictions_cache',
        private int $cacheTtl = 86400, // 24 hours
    ) {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Classify with caching by content hash.
     */
    public function classify(string $imagePath, int $topK = 5): array
    {
        // Generate cache key from file content
        $cacheKey = md5_file($imagePath);
        $cacheFile = $this->cacheDir . '/pred_' . $cacheKey . '.json';

        // Check cache
        if (file_exists($cacheFile)) {
            $cacheAge = time() - filemtime($cacheFile);

            if ($cacheAge < $this->cacheTtl) {
                $this->hits++;
                return json_decode(file_get_contents($cacheFile), true);
            }

            // Expired, delete it
            unlink($cacheFile);
        }

        // Cache miss - classify
        $this->misses++;
        $predictions = $this->classifier->classify($imagePath, $topK);

        // Store in cache
        file_put_contents($cacheFile, json_encode($predictions));

        return $predictions;
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        $total = $this->hits + $this->misses;
        $hitRate = $total > 0 ? ($this->hits / $total) * 100 : 0;

        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'total' => $total,
            'hit_rate' => $hitRate,
        ];
    }

    /**
     * Clear expired cache entries.
     */
    public function clearExpired(): int
    {
        $cleared = 0;
        $files = glob($this->cacheDir . '/pred_*.json');

        foreach ($files as $file) {
            $age = time() - filemtime($file);
            if ($age >= $this->cacheTtl) {
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
        $cleared = 0;
        $files = glob($this->cacheDir . '/pred_*.json');

        foreach ($files as $file) {
            unlink($file);
            $cleared++;
        }

        return $cleared;
    }
}

// Test caching system
if (PHP_SAPI === 'cli') {
    echo "Exercise 4: Production Caching System\n";
    echo "======================================\n\n";

    // Initialize
    $client = new TensorFlowClient();
    $preprocessor = new ImagePreprocessor();
    $baseClassifier = new ImageClassifier(
        client: $client,
        preprocessor: $preprocessor,
        labelsPath: __DIR__ . '/../data/imagenet_labels.json'
    );

    $cachedClassifier = new CachedImageClassifier(
        classifier: $baseClassifier,
        cacheDir: '/tmp/ch12_cache_test'
    );

    // Create test images (5 unique, 15 duplicates)
    echo "Creating test dataset (5 unique images, 15 duplicates)...\n";
    $testDir = '/tmp/ch12_cache_images';
    if (!is_dir($testDir)) {
        mkdir($testDir, 0755, true);
    }

    $uniquePaths = [];
    for ($i = 1; $i <= 5; $i++) {
        $path = "$testDir/unique_$i.jpg";
        $img = imagecreatetruecolor(300, 300);
        $color = imagecolorallocate($img, $i * 50, 100, 200);
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $path, 90);
        imagedestroy($img);
        $uniquePaths[] = $path;
    }

    // Create duplicates with different names
    $allPaths = [];
    foreach ($uniquePaths as $i => $path) {
        $allPaths[] = $path;
        for ($j = 1; $j <= 2; $j++) {
            $dupPath = "$testDir/dup_{$i}_$j.jpg";
            copy($path, $dupPath);
            $allPaths[] = $dupPath;
        }
    }

    shuffle($allPaths);
    $totalImages = count($allPaths);

    echo "✓ Created $totalImages images (5 unique content)\n\n";

    // Clear cache
    $cachedClassifier->clearAll();

    // First pass (cold cache)
    echo "First pass (cold cache):\n";
    echo "------------------------\n";
    $coldStart = microtime(true);

    foreach ($allPaths as $path) {
        $cachedClassifier->classify($path, topK: 3);
    }

    $coldDuration = microtime(true) - $coldStart;
    $coldStats = $cachedClassifier->getCacheStats();

    echo "Total time: " . round($coldDuration, 2) . "s\n";
    echo "Cache hits: {$coldStats['hits']}/{$coldStats['total']} ";
    echo "(" . round($coldStats['hit_rate']) . "%)\n\n";

    // Second pass (warm cache)
    echo "Second pass (warm cache):\n";
    echo "-------------------------\n";
    $warmStart = microtime(true);

    foreach ($allPaths as $path) {
        $cachedClassifier->classify($path, topK: 3);
    }

    $warmDuration = microtime(true) - $warmStart;
    $warmStats = $cachedClassifier->getCacheStats();

    echo "Total time: " . round($warmDuration, 2) . "s\n";
    echo "Cache hits: {$warmStats['hits']}/{$warmStats['total']} ";
    echo "(" . round($warmStats['hit_rate']) . "%)\n\n";

    // Performance improvement
    echo "Performance Analysis\n";
    echo "====================\n\n";

    $speedup = $coldDuration / $warmDuration;
    $timeSaved = $coldDuration - $warmDuration;

    echo "Speed improvement: " . round($speedup, 1) . "x faster with cache\n";
    echo "Time saved: " . round($timeSaved, 2) . "s\n";
    echo "Overall cache hit rate: " . round($warmStats['hit_rate']) . "%\n\n";

    echo "✓ Caching system test complete!\n";
    echo "\nNote: In production, use Redis instead of file-based cache\n";
    echo "      for better performance and distributed caching support.\n";
}
