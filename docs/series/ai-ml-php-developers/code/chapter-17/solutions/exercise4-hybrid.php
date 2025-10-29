<?php

declare(strict_types=1);

/**
 * Exercise 4 Solution: Hybrid Classification Strategy
 * 
 * Intelligent system that:
 * - Uses local model first (fast, free)
 * - Falls back to cloud API if confidence is below threshold
 * - Caches results to avoid redundant processing
 * - Tracks cost savings from hybrid approach
 */

require_once __DIR__ . '/../02-cloud-vision-client.php';
require_once __DIR__ . '/../05-onnx-classifier.php';
require_once __DIR__ . '/../09-caching-layer.php';

/**
 * Hybrid classifier with intelligent fallback strategy
 */
final class HybridClassifier
{
    private int $localAttempts = 0;
    private int $cloudFallbacks = 0;
    private int $cacheHits = 0;

    public function __construct(
        private readonly ONNXClassifier $localClassifier,
        private readonly CloudVisionClient $cloudClassifier,
        private readonly float $confidenceThreshold = 0.75,
        private readonly ?string $cacheDir = null,
    ) {}

    /**
     * Classify with hybrid strategy
     */
    public function classifyImage(string $imagePath): array
    {
        // Check cache first
        if ($this->cacheDir !== null) {
            $cached = $this->checkCache($imagePath);
            if ($cached !== null) {
                $this->cacheHits++;
                return [
                    'success' => true,
                    'results' => $cached,
                    'strategy' => 'cache',
                    'cost' => 0.0,
                ];
            }
        }

        // Try local model first
        try {
            $this->localAttempts++;
            $localResults = $this->localClassifier->classifyImage($imagePath);

            // Check if confidence is acceptable
            $maxConfidence = $localResults[0]['confidence'] ?? 0.0;

            if ($maxConfidence >= $this->confidenceThreshold) {
                // Local model is confident enough
                $this->saveCache($imagePath, $localResults);

                return [
                    'success' => true,
                    'results' => $localResults,
                    'strategy' => 'local',
                    'confidence' => $maxConfidence,
                    'cost' => 0.0,
                    'reason' => 'Local model confidence above threshold',
                ];
            }

            // Local confidence too low - fallback to cloud
            $this->cloudFallbacks++;
            $cloudResults = $this->cloudClassifier->classifyImage($imagePath);
            $this->saveCache($imagePath, $cloudResults);

            return [
                'success' => true,
                'results' => $cloudResults,
                'strategy' => 'cloud_fallback',
                'local_confidence' => $maxConfidence,
                'threshold' => $this->confidenceThreshold,
                'cost' => 0.0015, // Approximate cost per image
                'reason' => "Local confidence ({$maxConfidence}) below threshold ({$this->confidenceThreshold})",
            ];
        } catch (Exception $localError) {
            // Local model failed - use cloud
            $this->cloudFallbacks++;
            $cloudResults = $this->cloudClassifier->classifyImage($imagePath);
            $this->saveCache($imagePath, $cloudResults);

            return [
                'success' => true,
                'results' => $cloudResults,
                'strategy' => 'cloud_fallback',
                'cost' => 0.0015,
                'reason' => "Local model error: {$localError->getMessage()}",
            ];
        }
    }

    /**
     * Get strategy statistics
     */
    public function getStatistics(): array
    {
        $totalRequests = $this->localAttempts + $this->cloudFallbacks + $this->cacheHits;

        if ($totalRequests === 0) {
            return [];
        }

        $cloudCost = $this->cloudFallbacks * 0.0015;
        $potentialCostIfAllCloud = $totalRequests * 0.0015;
        $savings = $potentialCostIfAllCloud - $cloudCost;

        return [
            'total_requests' => $totalRequests,
            'cache_hits' => $this->cacheHits,
            'local_success' => $this->localAttempts - $this->cloudFallbacks,
            'cloud_fallbacks' => $this->cloudFallbacks,
            'cache_hit_rate' => round(($this->cacheHits / $totalRequests) * 100, 1),
            'local_success_rate' => round((($this->localAttempts - $this->cloudFallbacks) / $totalRequests) * 100, 1),
            'actual_cost' => round($cloudCost, 4),
            'potential_cost_if_all_cloud' => round($potentialCostIfAllCloud, 4),
            'savings' => round($savings, 4),
            'savings_percent' => round(($savings / $potentialCostIfAllCloud) * 100, 1),
        ];
    }

    private function checkCache(string $imagePath): ?array
    {
        if ($this->cacheDir === null) {
            return null;
        }

        $cacheKey = md5(file_get_contents($imagePath));
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            return is_array($data) ? $data : null;
        }

        return null;
    }

    private function saveCache(string $imagePath, array $results): void
    {
        if ($this->cacheDir === null) {
            return;
        }

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $cacheKey = md5(file_get_contents($imagePath));
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.json';

        file_put_contents($cacheFile, json_encode($results));
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    require_once __DIR__ . '/../.env.php';

    // Setup classifiers
    if (!file_exists(__DIR__ . '/../models/mobilenetv2-7.onnx')) {
        die("Error: Local ONNX model not found. Run ./download_model.sh\n");
    }

    if (empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
        die("Error: Cloud API key not set in .env file\n");
    }

    $localClassifier = new ONNXClassifier(
        modelPath: __DIR__ . '/../models/mobilenetv2-7.onnx',
        labelsPath: __DIR__ . '/../data/imagenet_labels.json',
        pythonScript: __DIR__ . '/../onnx_inference.py',
        maxResults: 5
    );

    $cloudClassifier = new CloudVisionClient(
        apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
        maxResults: 5
    );

    $hybrid = new HybridClassifier(
        localClassifier: $localClassifier,
        cloudClassifier: $cloudClassifier,
        confidenceThreshold: 0.80,
        cacheDir: __DIR__ . '/hybrid-cache'
    );

    echo "Hybrid Classification Strategy Demonstration\n";
    echo str_repeat('=', 60) . "\n\n";

    $images = glob(__DIR__ . '/../data/sample_images/*.jpg');

    if (empty($images)) {
        die("No images found in data/sample_images/\n");
    }

    // Process images
    foreach (array_slice($images, 0, 5) as $imagePath) {
        echo "Processing: " . basename($imagePath) . "\n";

        try {
            $result = $hybrid->classifyImage($imagePath);

            echo "  Strategy: {$result['strategy']}\n";
            echo "  Top result: {$result['results'][0]['label']} ";
            printf("(%.1f%%)\n", $result['results'][0]['confidence'] * 100);

            if (isset($result['reason'])) {
                echo "  Reason: {$result['reason']}\n";
            }
            echo "  Cost: $" . number_format($result['cost'], 4) . "\n";
            echo "\n";
        } catch (Exception $e) {
            echo "  Error: " . $e->getMessage() . "\n\n";
        }
    }

    // Show statistics
    $stats = $hybrid->getStatistics();

    echo str_repeat('=', 60) . "\n";
    echo "Hybrid Strategy Statistics\n";
    echo str_repeat('=', 60) . "\n\n";

    echo "Performance:\n";
    echo "  Total requests: {$stats['total_requests']}\n";
    echo "  Cache hits: {$stats['cache_hits']} ({$stats['cache_hit_rate']}%)\n";
    echo "  Local success: {$stats['local_success']} ({$stats['local_success_rate']}%)\n";
    echo "  Cloud fallbacks: {$stats['cloud_fallbacks']}\n\n";

    echo "Cost Analysis:\n";
    echo "  Actual cost: $" . number_format($stats['actual_cost'], 4) . "\n";
    echo "  Cost if all cloud: $" . number_format($stats['potential_cost_if_all_cloud'], 4) . "\n";
    echo "  Savings: $" . number_format($stats['savings'], 4) . " ({$stats['savings_percent']}%)\n\n";

    echo "Monthly Projection (30K images):\n";
    $monthlyFallbackRate = $stats['cloud_fallbacks'] / $stats['total_requests'];
    $monthlyCost = 30000 * $monthlyFallbackRate * 0.0015;
    $potentialMonthlyCost = CloudVisionClient::estimateMonthlyCost(30000);
    echo "  Hybrid cost: $" . number_format($monthlyCost, 2) . "\n";
    echo "  Cloud-only cost: $" . number_format($potentialMonthlyCost, 2) . "\n";
    echo "  Monthly savings: $" . number_format($potentialMonthlyCost - $monthlyCost, 2) . "\n";
}
