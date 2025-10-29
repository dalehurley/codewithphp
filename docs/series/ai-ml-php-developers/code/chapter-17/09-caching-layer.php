<?php

declare(strict_types=1);

/**
 * Classification with Result Caching
 * 
 * Demonstrates caching classification results to avoid redundant processing
 * Uses file-based cache with MD5 hash of image content as key
 */

require_once __DIR__ . '/07-unified-service.php';
require_once __DIR__ . '/.env.php';

/**
 * Cached classifier wrapper
 */
final class CachedImageClassifier implements ImageClassifier
{
    private const CACHE_VERSION = 'v1';

    public function __construct(
        private readonly ImageClassifier $classifier,
        private readonly string $cacheDir,
        private readonly int $cacheTtlSeconds = 86400, // 24 hours
    ) {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function classifyImage(string $imagePath): array
    {
        // Generate cache key from image content hash
        $imageContent = file_get_contents($imagePath);
        if ($imageContent === false) {
            throw new RuntimeException("Failed to read image: {$imagePath}");
        }

        $cacheKey = md5($imageContent . $this->classifier->getType() . self::CACHE_VERSION);
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.json';

        // Check cache
        if (file_exists($cacheFile)) {
            $cacheAge = time() - filemtime($cacheFile);

            if ($cacheAge < $this->cacheTtlSeconds) {
                $cached = json_decode(file_get_contents($cacheFile), true);

                if (is_array($cached)) {
                    echo "[CACHE HIT] Age: {$cacheAge}s\n";
                    return $cached;
                }
            } else {
                // Cache expired
                unlink($cacheFile);
            }
        }

        // Cache miss - classify and store
        echo "[CACHE MISS] Classifying...\n";
        $results = $this->classifier->classifyImage($imagePath);

        file_put_contents($cacheFile, json_encode($results));

        return $results;
    }

    public function getType(): string
    {
        return $this->classifier->getType();
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $files = glob($this->cacheDir . '/*.json');
        $totalSize = 0;
        $oldestTime = PHP_INT_MAX;
        $newestTime = 0;

        foreach ($files as $file) {
            $totalSize += filesize($file);
            $mtime = filemtime($file);
            $oldestTime = min($oldestTime, $mtime);
            $newestTime = max($newestTime, $mtime);
        }

        return [
            'entries' => count($files),
            'total_size_kb' => round($totalSize / 1024, 2),
            'oldest_age_hours' => $oldestTime < PHP_INT_MAX ? round((time() - $oldestTime) / 3600, 1) : 0,
            'newest_age_seconds' => $newestTime > 0 ? time() - $newestTime : 0,
        ];
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    // Setup base classifier
    $baseClassifier = null;

    if (file_exists(__DIR__ . '/models/mobilenetv2-7.onnx')) {
        $baseClassifier = new LocalImageClassifier(
            new ONNXClassifier(
                modelPath: __DIR__ . '/models/mobilenetv2-7.onnx',
                labelsPath: __DIR__ . '/data/imagenet_labels.json',
                pythonScript: __DIR__ . '/onnx_inference.py',
                maxResults: 5
            )
        );
    } elseif (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
        $baseClassifier = new CloudImageClassifier(
            new CloudVisionClient(
                apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
                maxResults: 5
            )
        );
    } else {
        die("Error: No classifier available\n");
    }

    // Wrap with caching
    $cachedClassifier = new CachedImageClassifier(
        classifier: $baseClassifier,
        cacheDir: __DIR__ . '/cache',
        cacheTtlSeconds: 3600 // 1 hour
    );

    $service = new ImageClassificationService($cachedClassifier);

    echo "Image Classification with Caching\n";
    echo str_repeat('=', 60) . "\n\n";

    $testImage = __DIR__ . '/data/sample_images/cat.jpg';

    if (!file_exists($testImage)) {
        die("Test image not found: {$testImage}\n");
    }

    // First classification (cache miss)
    echo "First classification:\n";
    $start = microtime(true);
    $result1 = $service->classify($testImage);
    $duration1 = microtime(true) - $start;

    foreach (array_slice($result1['results'], 0, 3) as $r) {
        printf("  %s: %.1f%%\n", $r['label'], $r['confidence'] * 100);
    }
    echo "Time: " . round($duration1 * 1000) . "ms\n\n";

    // Second classification (cache hit)
    echo "Second classification (should be cached):\n";
    $start = microtime(true);
    $result2 = $service->classify($testImage);
    $duration2 = microtime(true) - $start;

    foreach (array_slice($result2['results'], 0, 3) as $r) {
        printf("  %s: %.1f%%\n", $r['label'], $r['confidence'] * 100);
    }
    echo "Time: " . round($duration2 * 1000) . "ms\n\n";

    // Show speedup
    $speedup = round($duration1 / $duration2, 1);
    echo "Speedup from caching: {$speedup}x faster\n\n";

    // Cache statistics
    $stats = $cachedClassifier->getCacheStats();
    echo "Cache Statistics:\n";
    echo "  Entries: {$stats['entries']}\n";
    echo "  Size: {$stats['total_size_kb']} KB\n";
    echo "  Oldest entry: {$stats['oldest_age_hours']} hours old\n";
}
