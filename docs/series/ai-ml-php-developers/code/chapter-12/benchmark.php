<?php

declare(strict_types=1);

require_once '02-tensorflow-client.php';
require_once '03-image-preprocessor.php';
require_once '04-image-classifier.php';

/**
 * Performance benchmark for TensorFlow Serving integration.
 * 
 * Tests and compares:
 * - Single vs batch prediction performance
 * - Cold start vs warm cache
 * - Different image sizes
 * - Various batch sizes
 */

echo "Chapter 12 Performance Benchmark\n";
echo "=================================\n\n";

// Initialize components
try {
    $client = new TensorFlowClient();
    $preprocessor = new ImagePreprocessor();
    $classifier = new ImageClassifier(
        client: $client,
        preprocessor: $preprocessor,
        labelsPath: __DIR__ . '/data/imagenet_labels.json'
    );
} catch (Exception $e) {
    echo "✗ Error initializing: " . $e->getMessage() . "\n";
    echo "Make sure TensorFlow Serving is running.\n";
    exit(1);
}

// Create test images
echo "Setting up test images...\n";
$testImages = [];
$imageSizes = [100, 224, 300, 500];

foreach ($imageSizes as $size) {
    $path = "/tmp/benchmark_{$size}x{$size}.jpg";
    if (!file_exists($path)) {
        $img = imagecreatetruecolor($size, $size);
        $color = imagecolorallocate($img, rand(100, 200), rand(100, 200), rand(100, 200));
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $path, 90);
        imagedestroy($img);
    }
    $testImages[$size] = $path;
}

// Standard test image (224x224)
$standardImage = $testImages[224];
echo "✓ Test images ready\n\n";

// Benchmark 1: Cold Start vs Warm
echo "Benchmark 1: Cold Start vs Warm Cache\n";
echo "======================================\n";

// Cold start (first request)
$coldStart = microtime(true);
$classifier->classify($standardImage, topK: 1);
$coldDuration = microtime(true) - $coldStart;

// Warm requests (subsequent requests)
$warmTimes = [];
for ($i = 0; $i < 5; $i++) {
    $warmStart = microtime(true);
    $classifier->classify($standardImage, topK: 1);
    $warmTimes[] = microtime(true) - $warmStart;
}

$avgWarm = array_sum($warmTimes) / count($warmTimes);

echo "Cold start:      " . round($coldDuration * 1000, 2) . " ms\n";
echo "Warm average:    " . round($avgWarm * 1000, 2) . " ms (5 requests)\n";
echo "Speedup:         " . round($coldDuration / $avgWarm, 2) . "x faster when warm\n\n";

// Benchmark 2: Single vs Batch Predictions
echo "Benchmark 2: Single vs Batch Predictions\n";
echo "=========================================\n";

$batchSizes = [1, 5, 10, 20];
$batchImages = array_fill(0, 20, $standardImage);

foreach ($batchSizes as $batchSize) {
    $imagesToProcess = array_slice($batchImages, 0, $batchSize);

    // Batch processing
    $batchStart = microtime(true);
    $classifier->classifyBatch($imagesToProcess, topK: 1);
    $batchDuration = microtime(true) - $batchStart;

    // Sequential processing
    $sequentialStart = microtime(true);
    foreach ($imagesToProcess as $img) {
        $classifier->classify($img, topK: 1);
    }
    $sequentialDuration = microtime(true) - $sequentialStart;

    $batchAvg = ($batchDuration / $batchSize) * 1000;
    $seqAvg = ($sequentialDuration / $batchSize) * 1000;
    $speedup = $sequentialDuration / $batchDuration;

    echo "Batch size $batchSize:\n";
    echo "  Batch:      " . round($batchDuration * 1000, 2) . " ms total, ";
    echo round($batchAvg, 2) . " ms/image\n";
    echo "  Sequential: " . round($sequentialDuration * 1000, 2) . " ms total, ";
    echo round($seqAvg, 2) . " ms/image\n";
    echo "  Speedup:    " . round($speedup, 2) . "x faster\n\n";
}

// Benchmark 3: Different Image Sizes
echo "Benchmark 3: Image Size Impact (Preprocessing)\n";
echo "==============================================\n";

foreach ($imageSizes as $size) {
    $imagePath = $testImages[$size];

    // Measure preprocessing time only
    $preprocessStart = microtime(true);
    $preprocessor->preprocessImage($imagePath);
    $preprocessDuration = microtime(true) - $preprocessStart;

    // Measure full classification time
    $fullStart = microtime(true);
    $classifier->classify($imagePath, topK: 1);
    $fullDuration = microtime(true) - $fullStart;

    $preprocessPercent = ($preprocessDuration / $fullDuration) * 100;

    echo "{$size}x{$size}:\n";
    echo "  Preprocessing: " . round($preprocessDuration * 1000, 2) . " ms ";
    echo "(" . round($preprocessPercent, 1) . "% of total)\n";
    echo "  Full pipeline: " . round($fullDuration * 1000, 2) . " ms\n\n";
}

// Benchmark 4: Top-K Parameter Impact
echo "Benchmark 4: Top-K Parameter Impact\n";
echo "====================================\n";

$topKValues = [1, 3, 5, 10, 20];

foreach ($topKValues as $topK) {
    $times = [];

    for ($i = 0; $i < 3; $i++) {
        $start = microtime(true);
        $classifier->classify($standardImage, topK: $topK);
        $times[] = microtime(true) - $start;
    }

    $avgTime = array_sum($times) / count($times);
    echo "Top-$topK: " . round($avgTime * 1000, 2) . " ms (avg of 3 runs)\n";
}
echo "\n";

// Benchmark 5: Throughput Test
echo "Benchmark 5: Maximum Throughput\n";
echo "================================\n";

$duration = 10; // seconds
$requestCount = 0;
$startTime = time();

echo "Running throughput test for {$duration} seconds...\n";

while ((time() - $startTime) < $duration) {
    try {
        $classifier->classify($standardImage, topK: 1);
        $requestCount++;
    } catch (Exception $e) {
        echo "Error during throughput test: " . $e->getMessage() . "\n";
        break;
    }
}

$throughput = $requestCount / $duration;
$avgLatency = ($duration * 1000) / $requestCount;

echo "\nResults:\n";
echo "  Total requests:   $requestCount\n";
echo "  Duration:         {$duration}s\n";
echo "  Throughput:       " . round($throughput, 2) . " requests/second\n";
echo "  Average latency:  " . round($avgLatency, 2) . " ms\n\n";

// Summary
echo "====================================\n";
echo "Benchmark Summary\n";
echo "====================================\n\n";

echo "Key Findings:\n";
echo "  • Cold start is " . round($coldDuration / $avgWarm, 1) . "x slower than warm requests\n";
echo "  • Batch processing shows best speedup at larger batch sizes\n";
echo "  • Preprocessing time is significant for large images\n";
echo "  • Top-K parameter has minimal impact on performance\n";
echo "  • Maximum throughput: " . round($throughput, 1) . " req/s\n\n";

echo "Recommendations:\n";
echo "  • Use batch processing for multiple images\n";
echo "  • Resize images before uploading if possible\n";
echo "  • Implement caching for repeated predictions\n";
echo "  • Consider async processing for high-traffic scenarios\n\n";

echo "✓ Benchmark complete!\n";
