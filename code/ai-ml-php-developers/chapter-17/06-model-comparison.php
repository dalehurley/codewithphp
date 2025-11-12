<?php

declare(strict_types=1);

/**
 * Cloud vs Local Classification Comparison
 * 
 * Compares Google Cloud Vision API with local ONNX model across
 * performance, cost, and accuracy metrics
 */

require_once __DIR__ . '/02-cloud-vision-client.php';
require_once __DIR__ . '/05-onnx-classifier.php';
require_once __DIR__ . '/.env.php';

/**
 * Compare cloud and local image classification
 */
final class ClassifierComparison
{
    public function __construct(
        private readonly ?CloudVisionClient $cloudClient,
        private readonly ?ONNXClassifier $localClassifier,
    ) {}

    /**
     * Compare both classifiers on the same image
     */
    public function compareImage(string $imagePath): array
    {
        $comparison = [
            'image' => basename($imagePath),
            'cloud' => null,
            'local' => null,
        ];

        // Test cloud
        if ($this->cloudClient !== null) {
            try {
                $startTime = microtime(true);
                $results = $this->cloudClient->classifyImage($imagePath);
                $duration = microtime(true) - $startTime;

                $comparison['cloud'] = [
                    'success' => true,
                    'duration_ms' => round($duration * 1000),
                    'top_label' => $results[0]['label'] ?? 'N/A',
                    'confidence' => $results[0]['confidence'] ?? 0.0,
                    'all_results' => array_slice($results, 0, 3),
                ];
            } catch (Exception $e) {
                $comparison['cloud'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Test local
        if ($this->localClassifier !== null) {
            try {
                $startTime = microtime(true);
                $results = $this->localClassifier->classifyImage($imagePath);
                $duration = microtime(true) - $startTime;

                $comparison['local'] = [
                    'success' => true,
                    'duration_ms' => round($duration * 1000),
                    'top_label' => $results[0]['label'] ?? 'N/A',
                    'confidence' => $results[0]['confidence'] ?? 0.0,
                    'all_results' => array_slice($results, 0, 3),
                ];
            } catch (Exception $e) {
                $comparison['local'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $comparison;
    }

    /**
     * Generate summary statistics
     */
    public function summarize(array $comparisons): void
    {
        $cloudTimes = [];
        $localTimes = [];
        $agreements = 0;
        $total = 0;

        foreach ($comparisons as $comp) {
            if ($comp['cloud']['success'] ?? false) {
                $cloudTimes[] = $comp['cloud']['duration_ms'];
            }
            if ($comp['local']['success'] ?? false) {
                $localTimes[] = $comp['local']['duration_ms'];
            }

            if (
                ($comp['cloud']['success'] ?? false) &&
                ($comp['local']['success'] ?? false)
            ) {
                $total++;
                // Check if top labels match (approximately)
                $cloudLabel = strtolower($comp['cloud']['top_label']);
                $localLabel = strtolower($comp['local']['top_label']);

                if (str_contains($cloudLabel, $localLabel) || str_contains($localLabel, $cloudLabel)) {
                    $agreements++;
                }
            }
        }

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "Performance Summary\n";
        echo str_repeat('=', 60) . "\n\n";

        if (!empty($cloudTimes)) {
            echo "Cloud API (Google Vision):\n";
            echo "  Avg latency: " . round(array_sum($cloudTimes) / count($cloudTimes)) . "ms\n";
            echo "  Min latency: " . min($cloudTimes) . "ms\n";
            echo "  Max latency: " . max($cloudTimes) . "ms\n";
            echo "  Cost (1000 images): $" . CloudVisionClient::estimateMonthlyCost(2000) . "\n\n";
        }

        if (!empty($localTimes)) {
            echo "Local ONNX (MobileNetV2):\n";
            echo "  Avg latency: " . round(array_sum($localTimes) / count($localTimes)) . "ms\n";
            echo "  Min latency: " . min($localTimes) . "ms\n";
            echo "  Max latency: " . max($localTimes) . "ms\n";
            echo "  Cost (1000 images): $0.00\n\n";
        }

        if ($total > 0) {
            $agreementPercent = round(($agreements / $total) * 100, 1);
            echo "Top Label Agreement: {$agreements}/{$total} ({$agreementPercent}%)\n\n";
        }

        if (!empty($cloudTimes) && !empty($localTimes)) {
            $speedup = round(array_sum($cloudTimes) / array_sum($localTimes), 1);
            echo "Speed Comparison: Local is {$speedup}x faster\n";

            $monthlyCostBreakEven = 1000 + (50 / 1.50 * 1000);
            echo "Cost Break-even: ~" . round($monthlyCostBreakEven) . " images/month\n";
        }
    }
}

// Run comparison
$testImages = [
    __DIR__ . '/data/sample_images/cat.jpg',
    __DIR__ . '/data/sample_images/dog.jpg',
    __DIR__ . '/data/sample_images/car.jpg',
];

$cloudClient = null;
$localClassifier = null;

// Initialize cloud client if API key is available
if (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
    $cloudClient = new CloudVisionClient(
        apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
        maxResults: 5
    );
}

// Initialize local classifier if model exists
if (file_exists(__DIR__ . '/models/mobilenetv2-7.onnx')) {
    $localClassifier = new ONNXClassifier(
        modelPath: __DIR__ . '/models/mobilenetv2-7.onnx',
        labelsPath: __DIR__ . '/data/imagenet_labels.json',
        pythonScript: __DIR__ . '/onnx_inference.py',
        maxResults: 5
    );
}

$comparison = new ClassifierComparison($cloudClient, $localClassifier);

echo "Cloud vs Local Classification Comparison\n";
echo str_repeat('=', 60) . "\n\n";

$results = [];

foreach ($testImages as $imagePath) {
    if (!file_exists($imagePath)) {
        echo "⚠️  Skipping missing image: " . basename($imagePath) . "\n";
        continue;
    }

    echo "Comparing: " . basename($imagePath) . "\n";
    echo str_repeat('-', 60) . "\n";

    $result = $comparison->compareImage($imagePath);
    $results[] = $result;

    // Display cloud results
    if ($result['cloud'] !== null) {
        if ($result['cloud']['success']) {
            printf(
                "☁️  Cloud: %s (%.1f%%) in %dms\n",
                $result['cloud']['top_label'],
                $result['cloud']['confidence'] * 100,
                $result['cloud']['duration_ms']
            );
        } else {
            echo "☁️  Cloud: Error - " . $result['cloud']['error'] . "\n";
        }
    }

    // Display local results
    if ($result['local'] !== null) {
        if ($result['local']['success']) {
            printf(
                "💻 Local: %s (%.1f%%) in %dms\n",
                $result['local']['top_label'],
                $result['local']['confidence'] * 100,
                $result['local']['duration_ms']
            );
        } else {
            echo "💻 Local: Error - " . $result['local']['error'] . "\n";
        }
    }

    echo "\n";
}

$comparison->summarize($results);
