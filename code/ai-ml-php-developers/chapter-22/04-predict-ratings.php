<?php

declare(strict_types=1);

/**
 * Rating prediction demonstration and accuracy testing.
 *
 * This script demonstrates:
 * - Predicting ratings for test data
 * - Comparing predictions vs actual ratings
 * - Understanding prediction confidence
 * - Handling edge cases
 */

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

echo "=== Rating Prediction Testing ===\n\n";

// Load training ratings
$trainingRatings = [];
$file = fopen(__DIR__ . '/data/movie_ratings.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $userId = (int) $row[0];
    $movieId = (int) $row[1];
    $rating = (float) $row[2];

    $trainingRatings[$userId][$movieId] = $rating;
}
fclose($file);

// Load test ratings
$testRatings = [];
$file = fopen(__DIR__ . '/data/test_ratings.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $userId = (int) $row[0];
    $movieId = (int) $row[1];
    $rating = (float) $row[2];

    $testRatings[] = [
        'user_id' => $userId,
        'movie_id' => $movieId,
        'actual' => $rating,
    ];
}
fclose($file);

// Load movie metadata
$movies = [];
$file = fopen(__DIR__ . '/data/movies.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $movies[(int) $row[0]] = [
        'title' => $row[1],
        'genre' => $row[2],
    ];
}
fclose($file);

echo "Dataset Split:\n";
echo "  Training ratings: " . array_sum(array_map('count', $trainingRatings)) . "\n";
echo "  Test ratings: " . count($testRatings) . "\n\n";

// Create recommender with training data
$recommender = new UserBasedCollaborativeFilter($trainingRatings);

// Make predictions on test set
echo "Making Predictions on Test Set...\n";
$predictions = [];
$errors = [];

foreach ($testRatings as $testCase) {
    $userId = $testCase['user_id'];
    $movieId = $testCase['movie_id'];
    $actual = $testCase['actual'];

    $predicted = $recommender->predictRating($userId, $movieId, 10);

    if ($predicted !== null) {
        $error = abs($predicted - $actual);

        $predictions[] = [
            'user_id' => $userId,
            'movie_id' => $movieId,
            'actual' => $actual,
            'predicted' => $predicted,
            'error' => $error,
        ];

        $errors[] = $error;
    }
}

echo "  Predictions made: " . count($predictions) . "\n";
echo "  Coverage: " . round((count($predictions) / count($testRatings)) * 100, 1) . "%\n\n";

// Calculate metrics
$mae = array_sum($errors) / count($errors);
$rmse = sqrt(array_sum(array_map(fn($e) => $e * $e, $errors)) / count($errors));

echo "Prediction Accuracy:\n";
echo "  MAE (Mean Absolute Error): " . number_format($mae, 3) . "\n";
echo "  RMSE (Root Mean Squared Error): " . number_format($rmse, 3) . "\n\n";

// Show sample predictions
echo "Sample Predictions:\n\n";
echo "Movie                               | Actual | Predicted | Error\n";
echo "------------------------------------|--------|-----------|-------\n";

$samples = array_slice($predictions, 0, 10);

foreach ($samples as $pred) {
    $movie = $movies[$pred['movie_id']];
    $title = substr($movie['title'], 0, 35);

    echo sprintf(
        "%-35s | %6.2f | %9.2f | %5.2f\n",
        $title,
        $pred['actual'],
        $pred['predicted'],
        $pred['error']
    );
}

// Error distribution
echo "\n\nError Distribution:\n";
$errorBuckets = [
    '0.0-0.5' => 0,
    '0.5-1.0' => 0,
    '1.0-1.5' => 0,
    '1.5-2.0' => 0,
    '2.0+' => 0,
];

foreach ($errors as $error) {
    if ($error < 0.5) {
        $errorBuckets['0.0-0.5']++;
    } elseif ($error < 1.0) {
        $errorBuckets['0.5-1.0']++;
    } elseif ($error < 1.5) {
        $errorBuckets['1.0-1.5']++;
    } elseif ($error < 2.0) {
        $errorBuckets['1.5-2.0']++;
    } else {
        $errorBuckets['2.0+']++;
    }
}

foreach ($errorBuckets as $range => $count) {
    $percentage = ($count / count($errors)) * 100;
    $bar = str_repeat('█', (int) ($percentage / 2));
    echo sprintf("  %s: %3d (%5.1f%%) %s\n", $range, $count, $percentage, $bar);
}

// Best and worst predictions
usort($predictions, fn($a, $b) => $a['error'] <=> $b['error']);

echo "\n\nBest Predictions (Lowest Error):\n";
foreach (array_slice($predictions, 0, 3) as $pred) {
    $movie = $movies[$pred['movie_id']];
    echo sprintf(
        "  %s\n    Actual: %.2f, Predicted: %.2f, Error: %.3f\n",
        $movie['title'],
        $pred['actual'],
        $pred['predicted'],
        $pred['error']
    );
}

echo "\n\nWorst Predictions (Highest Error):\n";
foreach (array_slice($predictions, -3) as $pred) {
    $movie = $movies[$pred['movie_id']];
    echo sprintf(
        "  %s\n    Actual: %.2f, Predicted: %.2f, Error: %.3f\n",
        $movie['title'],
        $pred['actual'],
        $pred['predicted'],
        $pred['error']
    );
}

echo "\n✅ Rating prediction testing complete!\n";

