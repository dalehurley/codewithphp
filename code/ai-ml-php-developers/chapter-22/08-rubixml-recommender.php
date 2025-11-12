<?php

declare(strict_types=1);

/**
 * Using Rubix ML for collaborative filtering recommendations.
 *
 * This script demonstrates:
 * - Using KNNRegressor for rating prediction
 * - Matrix factorization approaches
 * - Comparing library vs from-scratch implementations
 * - Production-ready ML library integration
 */

require_once __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Regressors\KNNRegressor;
use Rubix\ML\Kernels\Distance\Euclidean;

echo "=== Rubix ML Recommendation System ===\n\n";

// Load ratings
$ratings = [];
$file = fopen(__DIR__ . '/data/movie_ratings.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $userId = (int) $row[0];
    $movieId = (int) $row[1];
    $rating = (float) $row[2];

    $ratings[] = [
        'user_id' => $userId,
        'movie_id' => $movieId,
        'rating' => $rating,
    ];
}
fclose($file);

// Load movies
$movies = [];
$file = fopen(__DIR__ . '/data/movies.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $movies[(int) $row[0]] = [
        'title' => $row[1],
        'genre' => $row[2],
        'year' => (int) $row[3],
    ];
}
fclose($file);

echo "Dataset: " . count($ratings) . " ratings\n\n";

// Prepare training data
// Features: [user_id, movie_id]
// Labels: rating
$samples = [];
$labels = [];

foreach ($ratings as $rating) {
    $samples[] = [
        (float) $rating['user_id'],
        (float) $rating['movie_id'],
    ];
    $labels[] = $rating['rating'];
}

$dataset = new Labeled($samples, $labels);

echo "Training KNN Regressor...\n";

// Use KNN with k=10 neighbors
$estimator = new KNNRegressor(10, new Euclidean());
$estimator->train($dataset);

echo "Training complete!\n\n";

// Make predictions for a sample user
$targetUserId = 5;

echo "Generating recommendations for User #{$targetUserId}...\n\n";

// Get movies the user hasn't rated
$allMovies = array_keys($movies);

$userMatrix = [];
foreach ($ratings as $rating) {
    if ($rating['user_id'] === $targetUserId) {
        $userMatrix[$rating['movie_id']] = true;
    }
}

$unratedMovies = array_diff($allMovies, array_keys($userMatrix));

// Predict ratings for unrated movies
$predictions = [];

foreach ($unratedMovies as $movieId) {
    $sample = [[
        (float) $targetUserId,
        (float) $movieId,
    ]];

    $prediction = $estimator->predict($sample)[0];
    $predictions[$movieId] = max(1.0, min(5.0, $prediction)); // Clamp to 1-5
}

// Sort by predicted rating
arsort($predictions);

echo "Top 10 Recommendations (Using Rubix ML):\n\n";

$rank = 1;
foreach (array_slice($predictions, 0, 10, true) as $movieId => $predictedRating) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s, %d)\n",
        $rank++,
        $predictedRating,
        $movie['title'],
        $movie['genre'],
        $movie['year']
    );
}

// Compare with from-scratch implementation
echo "\n\nComparing with From-Scratch Implementation:\n\n";

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

$ratingsMatrix = [];
foreach ($ratings as $rating) {
    $ratingsMatrix[$rating['user_id']][$rating['movie_id']] = $rating['rating'];
}

$scratchRecommender = new UserBasedCollaborativeFilter($ratingsMatrix);
$scratchRecs = $scratchRecommender->recommend($targetUserId, 10, 10);

echo "From-Scratch Recommendations:\n\n";
$rank = 1;
foreach ($scratchRecs as $movieId => $predictedRating) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s)\n",
        $rank++,
        $predictedRating,
        $movie['title'],
        $movie['genre']
    );
}

// Calculate overlap
$rubixml_movies = array_slice(array_keys($predictions), 0, 10);
$scratch_movies = array_keys($scratchRecs);
$overlap = array_intersect($rubixml_movies, $scratch_movies);

echo "\n\nComparison:\n";
echo "  Rubix ML top 10: " . count($rubixml_movies) . " movies\n";
echo "  From-scratch top 10: " . count($scratch_movies) . " movies\n";
echo "  Overlap: " . count($overlap) . " movies\n";

if (count($overlap) > 0) {
    echo "\n  Common recommendations:\n";
    foreach ($overlap as $movieId) {
        $movie = $movies[$movieId];
        echo "    - {$movie['title']} ({$movie['genre']})\n";
    }
}

echo "\n\n=== Advantages of Using ML Libraries ===\n\n";
echo "✅ Optimized algorithms (C extensions, BLAS/LAPACK)\n";
echo "✅ Battle-tested implementations\n";
echo "✅ More algorithm choices (SVD, Matrix Factorization, etc.)\n";
echo "✅ Built-in validation and metrics\n";
echo "✅ Easy to swap algorithms\n\n";

echo "=== When to Build From Scratch ===\n\n";
echo "✅ Learning and understanding algorithms\n";
echo "✅ Very specific custom requirements\n";
echo "✅ Integration with existing systems\n";
echo "✅ Performance optimization for specific use case\n";

echo "\n✅ Rubix ML integration complete!\n";

