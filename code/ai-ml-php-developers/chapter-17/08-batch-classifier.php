<?php

declare(strict_types=1);

/**
 * Batch Image Classification
 * 
 * Demonstrates efficient batch processing of multiple images
 * with progress tracking and error handling
 */

require_once __DIR__ . '/07-unified-service.php';
require_once __DIR__ . '/.env.php';

// Setup classifier
$localClassifier = null;
if (file_exists(__DIR__ . '/models/mobilenetv2-7.onnx')) {
    $localClassifier = new LocalImageClassifier(
        new ONNXClassifier(
            modelPath: __DIR__ . '/models/mobilenetv2-7.onnx',
            labelsPath: __DIR__ . '/data/imagenet_labels.json',
            pythonScript: __DIR__ . '/onnx_inference.py',
            maxResults: 5
        )
    );
}

if ($localClassifier === null) {
    die("Error: Local classifier not available. Run ./download_model.sh\n");
}

$service = new ImageClassificationService($localClassifier);

// Find all images
$imageDir = __DIR__ . '/data/sample_images';
$images = glob($imageDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);

if (empty($images)) {
    die("No images found in {$imageDir}\n");
}

echo "Batch Image Classification\n";
echo str_repeat('=', 60) . "\n";
echo "Processing " . count($images) . " images...\n\n";

$results = [];
$totalTime = 0;
$successCount = 0;
$errorCount = 0;

foreach ($images as $i => $imagePath) {
    $filename = basename($imagePath);
    $progress = $i + 1;
    $total = count($images);

    printf("[%d/%d] %s ", $progress, $total, $filename);

    try {
        $startTime = microtime(true);
        $result = $service->classify($imagePath);
        $duration = microtime(true) - $startTime;

        $totalTime += $duration;
        $successCount++;

        $topLabel = $result['results'][0]['label'] ?? 'Unknown';
        $confidence = $result['results'][0]['confidence'] ?? 0.0;

        printf(
            "✓ %s (%.1f%%) - %dms\n",
            $topLabel,
            $confidence * 100,
            round($duration * 1000)
        );

        $results[] = [
            'file' => $filename,
            'success' => true,
            'label' => $topLabel,
            'confidence' => $confidence,
            'duration_ms' => round($duration * 1000),
        ];
    } catch (Exception $e) {
        $errorCount++;
        printf("✗ Error: %s\n", $e->getMessage());

        $results[] = [
            'file' => $filename,
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

// Summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "Batch Processing Summary\n";
echo str_repeat('=', 60) . "\n\n";

echo "Results:\n";
echo "  Total images: " . count($images) . "\n";
echo "  Successful: {$successCount}\n";
echo "  Failed: {$errorCount}\n\n";

if ($successCount > 0) {
    echo "Performance:\n";
    echo "  Total time: " . round($totalTime, 2) . "s\n";
    echo "  Average per image: " . round(($totalTime / $successCount) * 1000) . "ms\n";
    echo "  Throughput: " . round($successCount / $totalTime, 1) . " images/second\n\n";
}

// Export results to JSON
$outputFile = __DIR__ . '/batch-results.json';
file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT));
echo "Results exported to: batch-results.json\n";
