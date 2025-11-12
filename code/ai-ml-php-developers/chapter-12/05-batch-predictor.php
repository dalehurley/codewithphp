<?php

declare(strict_types=1);

require_once '02-tensorflow-client.php';
require_once '03-image-preprocessor.php';
require_once '04-image-classifier.php';

/**
 * Batch image classification example.
 * 
 * Demonstrates efficient batch processing of multiple images
 * with performance comparison against sequential processing.
 */

echo "Batch Image Classification\n";
echo "===========================\n\n";

// Initialize classifier
$client = new TensorFlowClient();
$preprocessor = new ImagePreprocessor();
$labelsPath = __DIR__ . '/data/imagenet_labels.json';

$classifier = new ImageClassifier(
    client: $client,
    preprocessor: $preprocessor,
    labelsPath: $labelsPath
);

// Get test images
$sampleDir = __DIR__ . '/data/sample_images';
$imagePaths = [];

if (is_dir($sampleDir)) {
    $imagePaths = glob($sampleDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
}

// If no sample images, create some test images
if (empty($imagePaths)) {
    echo "Creating test images...\n";
    $colors = [
        ['Red', 255, 0, 0],
        ['Green', 0, 255, 0],
        ['Blue', 0, 0, 255],
        ['Yellow', 255, 255, 0],
        ['Purple', 128, 0, 128],
    ];

    foreach ($colors as [$name, $r, $g, $b]) {
        $path = "/tmp/test_{$name}.jpg";
        $img = imagecreatetruecolor(300, 300);
        $color = imagecolorallocate($img, $r, $g, $b);
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $path, 90);
        imagedestroy($img);
        $imagePaths[] = $path;
    }
    echo "✓ Created " . count($imagePaths) . " test images\n\n";
}

$numImages = count($imagePaths);
echo "Processing $numImages images...\n\n";

try {
    // Method 1: Batch processing (recommended)
    echo "Method 1: Batch Processing\n";
    echo "---------------------------\n";

    $batchStart = microtime(true);
    $batchResults = $classifier->classifyBatch($imagePaths, topK: 3);
    $batchDuration = microtime(true) - $batchStart;

    echo "✓ Batch completed in " . round($batchDuration * 1000, 2) . " ms\n";
    echo "  Average per image: " . round($batchDuration * 1000 / $numImages, 2) . " ms\n\n";

    // Method 2: Sequential processing (for comparison)
    echo "Method 2: Sequential Processing\n";
    echo "-------------------------------\n";

    $sequentialStart = microtime(true);
    $sequentialResults = [];
    foreach ($imagePaths as $path) {
        $sequentialResults[] = $classifier->classify($path, topK: 3);
    }
    $sequentialDuration = microtime(true) - $sequentialStart;

    echo "✓ Sequential completed in " . round($sequentialDuration * 1000, 2) . " ms\n";
    echo "  Average per image: " . round($sequentialDuration * 1000 / $numImages, 2) . " ms\n\n";

    // Performance comparison
    echo "Performance Comparison\n";
    echo "======================\n\n";

    $speedup = $sequentialDuration / $batchDuration;
    echo "Batch processing: " . round($batchDuration, 3) . "s\n";
    echo "Sequential processing: " . round($sequentialDuration, 3) . "s\n";
    echo "Speedup: " . round($speedup, 2) . "x faster with batching\n\n";

    // Display results
    echo "Classification Results\n";
    echo "======================\n\n";

    foreach ($imagePaths as $i => $path) {
        $predictions = $batchResults[$i];
        $filename = basename($path);

        echo ($i + 1) . ". $filename\n";
        echo "   Top prediction: {$predictions[0]['label']} ";
        echo "(" . round($predictions[0]['confidence'] * 100, 1) . "%)\n";

        // Show all top 3
        echo "   All predictions:\n";
        foreach ($predictions as $j => $pred) {
            $conf = round($pred['confidence'] * 100, 1);
            echo "     " . ($j + 1) . ". {$pred['label']} ($conf%)\n";
        }
        echo "\n";
    }

    // Summary statistics
    echo "Summary Statistics\n";
    echo "==================\n\n";

    $allConfidences = [];
    $topPredictions = [];

    foreach ($batchResults as $results) {
        $allConfidences[] = $results[0]['confidence'];
        $topPredictions[] = $results[0]['label'];
    }

    $avgConfidence = array_sum($allConfidences) / count($allConfidences);
    $maxConfidence = max($allConfidences);
    $minConfidence = min($allConfidences);

    echo "Total images processed: $numImages\n";
    echo "Average confidence: " . round($avgConfidence * 100, 2) . "%\n";
    echo "Highest confidence: " . round($maxConfidence * 100, 2) . "%\n";
    echo "Lowest confidence: " . round($minConfidence * 100, 2) . "%\n\n";

    // Count prediction frequency
    $predictionCounts = array_count_values($topPredictions);
    arsort($predictionCounts);

    echo "Most common predictions:\n";
    $rank = 1;
    foreach (array_slice($predictionCounts, 0, 5, true) as $label => $count) {
        echo "  $rank. $label ($count images)\n";
        $rank++;
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    echo "Make sure TensorFlow Serving is running:\n";
    echo "  ./start_tensorflow_serving.sh\n\n";
    exit(1);
}
