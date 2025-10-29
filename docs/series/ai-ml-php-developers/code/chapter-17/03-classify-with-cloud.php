<?php

declare(strict_types=1);

/**
 * Batch classification using Google Cloud Vision API
 * 
 * Classifies multiple images and compares results
 */

require_once __DIR__ . '/02-cloud-vision-client.php';
require_once __DIR__ . '/.env.php';

$apiKey = $_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '';

if (empty($apiKey)) {
    die("Error: GOOGLE_CLOUD_VISION_API_KEY not set in .env file\n");
}

$client = new CloudVisionClient(
    apiKey: $apiKey,
    maxResults: 5
);

$imagesToClassify = [
    'Cat' => __DIR__ . '/data/sample_images/cat.jpg',
    'Dog' => __DIR__ . '/data/sample_images/dog.jpg',
    'Car' => __DIR__ . '/data/sample_images/car.jpg',
    'Bicycle' => __DIR__ . '/data/sample_images/bicycle.jpg',
    'Coffee' => __DIR__ . '/data/sample_images/coffee.jpg',
];

echo "Cloud Vision API - Batch Classification\n";
echo str_repeat('=', 60) . "\n\n";

$totalTime = 0;
$successCount = 0;
$results = [];

foreach ($imagesToClassify as $expectedLabel => $imagePath) {
    if (!file_exists($imagePath)) {
        echo "⚠️  {$expectedLabel}: File not found - {$imagePath}\n\n";
        continue;
    }

    try {
        $startTime = microtime(true);
        $classifications = $client->classifyImage($imagePath);
        $duration = microtime(true) - $startTime;
        $totalTime += $duration;

        echo "📷 {$expectedLabel} (" . round($duration * 1000) . "ms)\n";
        echo str_repeat('-', 60) . "\n";

        foreach (array_slice($classifications, 0, 3) as $i => $result) {
            $icon = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉');
            printf(
                "%s %-20s %5.1f%%\n",
                $icon,
                $result['label'],
                $result['confidence'] * 100
            );
        }

        echo "\n";
        $successCount++;

        $results[$expectedLabel] = $classifications;
    } catch (Exception $e) {
        echo "❌ {$expectedLabel}: " . $e->getMessage() . "\n\n";
    }
}

// Summary
echo str_repeat('=', 60) . "\n";
echo "Summary:\n";
echo "  Processed: {$successCount}/" . count($imagesToClassify) . " images\n";
echo "  Total time: " . round($totalTime, 2) . "s\n";

if ($successCount > 0) {
    echo "  Avg per image: " . round(($totalTime / $successCount) * 1000) . "ms\n";

    $costPer1000 = CloudVisionClient::estimateMonthlyCost(1000 + $successCount) * (1000 / $successCount);
    echo "  Est. cost per 1000: $" . round($costPer1000, 2) . "\n";
}
