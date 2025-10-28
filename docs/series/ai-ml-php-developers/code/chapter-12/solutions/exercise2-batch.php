<?php

declare(strict_types=1);

require_once __DIR__ . '/../02-tensorflow-client.php';
require_once __DIR__ . '/../03-image-preprocessor.php';
require_once __DIR__ . '/../04-image-classifier.php';

/**
 * Exercise 2 Solution: Optimized Batch Processing
 * 
 * Processes directories of images with progress tracking, error handling,
 * and performance comparison.
 */

echo "Exercise 2: Optimized Batch Processing\n";
echo "=======================================\n\n";

// Initialize classifier
$client = new TensorFlowClient();
$preprocessor = new ImagePreprocessor();
$classifier = new ImageClassifier(
    client: $client,
    preprocessor: $preprocessor,
    labelsPath: __DIR__ . '/../data/imagenet_labels.json'
);

// Get images from directory
$imageDir = $argv[1] ?? __DIR__ . '/../data/sample_images';

if (!is_dir($imageDir)) {
    echo "Creating test images directory...\n";
    mkdir($imageDir, 0755, true);

    // Create 15 test images
    for ($i = 1; $i <= 15; $i++) {
        $path = "$imageDir/test_$i.jpg";
        $img = imagecreatetruecolor(300, 300);
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);
        $color = imagecolorallocate($img, $r, $g, $b);
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $path, 90);
        imagedestroy($img);
    }
    echo "✓ Created 15 test images\n\n";
}

$imagePaths = glob("$imageDir/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
$totalImages = count($imagePaths);

if ($totalImages === 0) {
    echo "No images found in $imageDir\n";
    exit(1);
}

echo "Found $totalImages images in $imageDir\n\n";

// Batch processing with progress
echo "Processing $totalImages images...\n";

$batchStart = microtime(true);
$successful = 0;
$failed = 0;
$results = [];
$errors = [];

$barWidth = 40;

foreach ($imagePaths as $i => $path) {
    try {
        $result = $classifier->classify($path, topK: 3);
        $results[] = $result;
        $successful++;
    } catch (Exception $e) {
        $failed++;
        $errors[] = basename($path) . ': ' . $e->getMessage();
    }

    // Progress bar
    $progress = ($i + 1) / $totalImages;
    $filledBars = (int)($progress * $barWidth);
    $emptyBars = $barWidth - $filledBars;
    $percentage = round($progress * 100);

    echo "\rProgress: [" . str_repeat('█', $filledBars) . str_repeat('░', $emptyBars) . "] ";
    echo "$percentage% ($i / $totalImages)";
}

$batchDuration = microtime(true) - $batchStart;
echo "\n\n";

// Sequential processing for comparison (sample)
echo "Running sequential comparison (first 5 images)...\n";
$samplePaths = array_slice($imagePaths, 0, min(5, $totalImages));

$sequentialStart = microtime(true);
foreach ($samplePaths as $path) {
    try {
        $classifier->classify($path, topK: 3);
    } catch (Exception $e) {
        // Ignore for comparison
    }
}
$sequentialDuration = microtime(true) - $sequentialStart;

// Estimate full sequential time
$avgSequential = $sequentialDuration / count($samplePaths);
$estimatedSequential = $avgSequential * $totalImages;

echo "\n";

// Summary report
echo "Summary Report\n";
echo "==============\n\n";

echo "✓ $successful successful, $failed failed\n";

if ($successful > 0) {
    // Calculate statistics
    $allConfidences = array_map(fn($r) => $r[0]['confidence'], $results);
    $avgConfidence = array_sum($allConfidences) / count($allConfidences);

    $topPredictions = array_map(fn($r) => $r[0]['label'], $results);
    $predictionCounts = array_count_values($topPredictions);
    arsort($predictionCounts);
    $mostCommon = array_key_first($predictionCounts);
    $mostCommonCount = $predictionCounts[$mostCommon];

    echo "Average confidence: " . round($avgConfidence * 100, 1) . "%\n";
    echo "Most predicted: $mostCommon ($mostCommonCount images)\n\n";
}

// Performance comparison
echo "Performance Comparison\n";
echo "======================\n\n";

echo "Batch time: " . round($batchDuration, 2) . "s\n";
echo "Estimated sequential: " . round($estimatedSequential, 2) . "s\n";

$speedup = $estimatedSequential / $batchDuration;
echo "Speed improvement: " . round($speedup, 1) . "x faster\n\n";

// Show errors if any
if ($failed > 0) {
    echo "Errors:\n";
    foreach (array_slice($errors, 0, 5) as $error) {
        echo "  - $error\n";
    }
    if ($failed > 5) {
        echo "  ... and " . ($failed - 5) . " more\n";
    }
}

echo "\n✓ Batch processing completed!\n";
