<?php

declare(strict_types=1);

/**
 * Batch recommendation processing for efficiency.
 *
 * This script demonstrates:
 * - Processing multiple users at once
 * - Optimizing for throughput
 * - Progress tracking
 * - Exporting recommendations to CSV/JSON
 */

require_once __DIR__ . '/12-production-recommender.php';

echo "=== Batch Recommendation Processing ===\n\n";

// Load data
$ratings = [];
$file = fopen(__DIR__ . '/data/movie_ratings.csv', 'r');
fgetcsv($file);

while ($row = fgetcsv($file)) {
    $ratings[(int) $row[0]][(int) $row[1]] = (float) $row[2];
}
fclose($file);

$movies = [];
$file = fopen(__DIR__ . '/data/movies.csv', 'r');
fgetcsv($file);

while ($row = fgetcsv($file)) {
    $movies[(int) $row[0]] = [
        'title' => $row[1],
        'genre' => $row[2],
        'year' => (int) $row[3],
    ];
}
fclose($file);

// Create recommender
$recommender = new ProductionRecommender($ratings, $movies, [
    'cache_similarities' => true,
    'k_neighbors' => 10,
]);

// Batch process recommendations for all users
$userIds = array_keys($ratings);
$batchSize = 20;
$numBatches = ceil(count($userIds) / $batchSize);

echo "Batch Configuration:\n";
echo "  Total users: " . count($userIds) . "\n";
echo "  Batch size: {$batchSize}\n";
echo "  Number of batches: {$numBatches}\n\n";

echo "Processing recommendations...\n\n";

$allRecommendations = [];
$startTime = microtime(true);

for ($batch = 0; $batch < $numBatches; $batch++) {
    $batchStart = $batch * $batchSize;
    $batchUsers = array_slice($userIds, $batchStart, $batchSize);

    foreach ($batchUsers as $userId) {
        $recommendations = $recommender->getRecommendations($userId, 10);
        $allRecommendations[$userId] = $recommendations;
    }

    $progress = (($batch + 1) / $numBatches) * 100;
    echo "\r  Progress: " . round($progress, 1) . "%";
}

$totalTime = microtime(true) - $startTime;

echo "\r  Progress: 100.0%\n\n";
echo "Processing complete!\n";
echo "  Time: " . number_format($totalTime, 2) . " seconds\n";
echo "  Average per user: " . number_format(($totalTime / count($userIds)) * 1000, 2) . " ms\n";
echo "  Throughput: " . number_format(count($userIds) / $totalTime, 1) . " users/second\n\n";

// Show statistics
$stats = $recommender->getStats();
echo "Statistics:\n";
echo "  Total predictions: {$stats['predictions']}\n";
echo "  Cache hit rate: {$stats['cache_hit_rate']}%\n\n";

// Export to CSV
$outputCsv = __DIR__ . '/batch-recommendations.csv';
$csvFile = fopen($outputCsv, 'w');
fputcsv($csvFile, ['user_id', 'rank', 'movie_id', 'predicted_rating', 'title', 'genre']);

foreach ($allRecommendations as $userId => $recommendations) {
    $rank = 1;
    foreach ($recommendations as $movieId => $rating) {
        fputcsv($csvFile, [
            $userId,
            $rank++,
            $movieId,
            number_format($rating, 2),
            $movies[$movieId]['title'],
            $movies[$movieId]['genre'],
        ]);
    }
}

fclose($csvFile);
echo "Exported to CSV: {$outputCsv}\n";

// Export to JSON
$outputJson = __DIR__ . '/batch-recommendations.json';
$jsonData = [];

foreach ($allRecommendations as $userId => $recommendations) {
    $userRecs = [];
    foreach ($recommendations as $movieId => $rating) {
        $userRecs[] = [
            'movie_id' => $movieId,
            'predicted_rating' => round($rating, 2),
            'title' => $movies[$movieId]['title'],
            'genre' => $movies[$movieId]['genre'],
        ];
    }
    $jsonData[$userId] = $userRecs;
}

file_put_contents($outputJson, json_encode($jsonData, JSON_PRETTY_PRINT));
echo "Exported to JSON: {$outputJson}\n\n";

// Sample results
echo "Sample Recommendations:\n\n";

foreach (array_slice($userIds, 0, 3) as $userId) {
    echo "User #{$userId}:\n";

    $rank = 1;
    foreach (array_slice($allRecommendations[$userId], 0, 5, true) as $movieId => $rating) {
        echo sprintf(
            "  %d. ⭐ %.2f - %s\n",
            $rank++,
            $rating,
            $movies[$movieId]['title']
        );
    }

    echo "\n";
}

echo "✅ Batch processing complete!\n";

