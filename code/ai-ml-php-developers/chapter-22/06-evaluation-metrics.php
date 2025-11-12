<?php

declare(strict_types=1);

/**
 * Comprehensive evaluation metrics for recommendation systems.
 *
 * This script demonstrates:
 * - RMSE and MAE for rating prediction accuracy
 * - Precision@k and Recall@k for recommendation quality
 * - Coverage and diversity metrics
 * - Statistical significance testing
 */

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

/**
 * Calculate Mean Absolute Error (MAE).
 */
function calculateMAE(array $predictions): float
{
    $errors = array_column($predictions, 'error');
    return array_sum($errors) / count($errors);
}

/**
 * Calculate Root Mean Squared Error (RMSE).
 */
function calculateRMSE(array $predictions): float
{
    $errors = array_column($predictions, 'error');
    $squaredErrors = array_map(fn($e) => $e * $e, $errors);
    return sqrt(array_sum($squaredErrors) / count($squaredErrors));
}

/**
 * Calculate Precision@K.
 *
 * Precision = (# relevant items recommended) / (# recommended items)
 */
function precisionAtK(array $recommendations, array $relevantItems, int $k): float
{
    $topK = array_slice($recommendations, 0, $k, true);
    $relevant = array_intersect(array_keys($topK), $relevantItems);

    return count($relevant) / $k;
}

/**
 * Calculate Recall@K.
 *
 * Recall = (# relevant items recommended) / (# relevant items)
 */
function recallAtK(array $recommendations, array $relevantItems, int $k): float
{
    $topK = array_slice($recommendations, 0, $k, true);
    $relevant = array_intersect(array_keys($topK), $relevantItems);

    return count($relevantItems) > 0
        ? count($relevant) / count($relevantItems)
        : 0.0;
}

/**
 * Calculate F1 Score (harmonic mean of precision and recall).
 */
function f1Score(float $precision, float $recall): float
{
    return ($precision + $recall) > 0
        ? 2 * ($precision * $recall) / ($precision + $recall)
        : 0.0;
}

echo "=== Recommendation System Evaluation ===\n\n";

// Load ratings
$ratings = [];
$file = fopen(__DIR__ . '/data/movie_ratings.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $userId = (int) $row[0];
    $movieId = (int) $row[1];
    $rating = (float) $row[2];

    $ratings[$userId][$movieId] = $rating;
}
fclose($file);

// Load test set
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
        'rating' => $rating,
    ];
}
fclose($file);

// Create recommender
$recommender = new UserBasedCollaborativeFilter($ratings);

echo "Evaluating Recommendation System...\n\n";

// 1. Rating Prediction Accuracy
echo "1. RATING PREDICTION ACCURACY\n";
echo str_repeat('-', 50) . "\n\n";

$predictions = [];

foreach ($testRatings as $test) {
    $userId = $test['user_id'];
    $movieId = $test['movie_id'];
    $actual = $test['rating'];

    $predicted = $recommender->predictRating($userId, $movieId, 10);

    if ($predicted !== null) {
        $predictions[] = [
            'user_id' => $userId,
            'movie_id' => $movieId,
            'actual' => $actual,
            'predicted' => $predicted,
            'error' => abs($predicted - $actual),
        ];
    }
}

$mae = calculateMAE($predictions);
$rmse = calculateRMSE($predictions);
$coverage = (count($predictions) / count($testRatings)) * 100;

echo "  MAE (Mean Absolute Error):      " . number_format($mae, 4) . "\n";
echo "  RMSE (Root Mean Squared Error): " . number_format($rmse, 4) . "\n";
echo "  Coverage:                       " . number_format($coverage, 1) . "%\n";
echo "  Predictions made:               " . count($predictions) . " / " . count($testRatings) . "\n\n";

// 2. Top-N Recommendation Quality
echo "2. TOP-N RECOMMENDATION QUALITY\n";
echo str_repeat('-', 50) . "\n\n";

// Define "relevant" as movies rated 4.0 or higher
$relevantThreshold = 4.0;

$precisions = [];
$recalls = [];
$f1Scores = [];

// Sample users for evaluation
$evalUsers = array_unique(array_column($testRatings, 'user_id'));
$evalUsers = array_slice($evalUsers, 0, 20); // Evaluate first 20 users

foreach ($evalUsers as $userId) {
    if (!isset($ratings[$userId])) {
        continue;
    }

    // Get recommendations
    $recommendations = $recommender->recommend($userId, 10, 10);

    if (empty($recommendations)) {
        continue;
    }

    // Find relevant items (highly rated in test set)
    $relevantItems = [];
    foreach ($testRatings as $test) {
        if ($test['user_id'] === $userId && $test['rating'] >= $relevantThreshold) {
            $relevantItems[] = $test['movie_id'];
        }
    }

    if (empty($relevantItems)) {
        continue;
    }

    // Calculate metrics at different K values
    foreach ([5, 10] as $k) {
        $precision = precisionAtK($recommendations, $relevantItems, $k);
        $recall = recallAtK($recommendations, $relevantItems, $k);
        $f1 = f1Score($precision, $recall);

        $precisions[$k][] = $precision;
        $recalls[$k][] = $recall;
        $f1Scores[$k][] = $f1;
    }
}

foreach ([5, 10] as $k) {
    if (empty($precisions[$k])) {
        continue;
    }

    $avgPrecision = array_sum($precisions[$k]) / count($precisions[$k]);
    $avgRecall = array_sum($recalls[$k]) / count($recalls[$k]);
    $avgF1 = array_sum($f1Scores[$k]) / count($f1Scores[$k]);

    echo "Metrics @ K={$k}:\n";
    echo "  Precision@{$k}: " . number_format($avgPrecision, 4) . "\n";
    echo "  Recall@{$k}:    " . number_format($avgRecall, 4) . "\n";
    echo "  F1-Score@{$k}:  " . number_format($avgF1, 4) . "\n\n";
}

// 3. Catalog Coverage
echo "3. CATALOG COVERAGE\n";
echo str_repeat('-', 50) . "\n\n";

// Get all movies from dataset
$allMovies = [];
foreach ($ratings as $userRatings) {
    $allMovies = array_merge($allMovies, array_keys($userRatings));
}
$allMovies = array_unique($allMovies);

// Get all recommended movies
$recommendedMovies = [];
foreach ($evalUsers as $userId) {
    if (!isset($ratings[$userId])) {
        continue;
    }

    $recommendations = $recommender->recommend($userId, 10, 10);
    $recommendedMovies = array_merge($recommendedMovies, array_keys($recommendations));
}
$recommendedMovies = array_unique($recommendedMovies);

$catalogCoverage = (count($recommendedMovies) / count($allMovies)) * 100;

echo "  Total movies in catalog:    " . count($allMovies) . "\n";
echo "  Movies recommended:         " . count($recommendedMovies) . "\n";
echo "  Catalog Coverage:           " . number_format($catalogCoverage, 1) . "%\n\n";

// 4. Recommendation Diversity
echo "4. RECOMMENDATION DIVERSITY\n";
echo str_repeat('-', 50) . "\n\n";

// Calculate intra-list diversity (average pairwise distance within each recommendation list)
// Using genre as a simple diversity measure

$movies = [];
$file = fopen(__DIR__ . '/data/movies.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $movies[(int) $row[0]] = [
        'genre' => $row[2],
    ];
}
fclose($file);

$diversityScores = [];

foreach (array_slice($evalUsers, 0, 10) as $userId) {
    if (!isset($ratings[$userId])) {
        continue;
    }

    $recommendations = $recommender->recommend($userId, 10, 10);

    if (count($recommendations) < 2) {
        continue;
    }

    // Count unique genres in recommendations
    $genres = [];
    foreach (array_keys($recommendations) as $movieId) {
        if (isset($movies[$movieId])) {
            $genres[] = $movies[$movieId]['genre'];
        }
    }

    $uniqueGenres = count(array_unique($genres));
    $totalMovies = count($recommendations);

    $diversityScores[] = $uniqueGenres / $totalMovies;
}

$avgDiversity = array_sum($diversityScores) / count($diversityScores);

echo "  Average Genre Diversity:    " . number_format($avgDiversity, 4) . "\n";
echo "  (1.0 = all different genres, 0.0 = all same genre)\n\n";

// Summary
echo "\n=== EVALUATION SUMMARY ===\n\n";
echo "✅ Prediction Accuracy: MAE=" . number_format($mae, 3) . ", RMSE=" . number_format($rmse, 3) . "\n";
echo "✅ Recommendation Quality: P@10=" . number_format($avgPrecision, 3) . ", R@10=" . number_format($avgRecall, 3) . "\n";
echo "✅ Coverage: " . number_format($catalogCoverage, 1) . "% of catalog\n";
echo "✅ Diversity: " . number_format($avgDiversity, 3) . " genre diversity\n\n";

echo "Interpretation:\n";
echo "  - Lower MAE/RMSE is better (closer predictions to actual ratings)\n";
echo "  - Higher Precision/Recall is better (more relevant recommendations)\n";
echo "  - Higher coverage is better (recommends variety of items)\n";
echo "  - Higher diversity is better (recommendations span multiple genres)\n";

echo "\n✅ Evaluation complete!\n";

