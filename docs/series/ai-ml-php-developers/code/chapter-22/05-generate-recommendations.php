<?php

declare(strict_types=1);

/**
 * Generate and explain recommendations for multiple users.
 *
 * This script demonstrates:
 * - Generating personalized recommendations
 * - Explaining why movies are recommended
 * - Comparing recommendations across users
 * - Understanding recommendation diversity
 */

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

echo "=== Recommendation Generation ===\n\n";

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

// Create recommender
$recommender = new UserBasedCollaborativeFilter($ratings);

// Generate recommendations for multiple users
$sampleUsers = [1, 5, 10, 15, 20];

foreach ($sampleUsers as $userId) {
    if (!isset($ratings[$userId])) {
        continue;
    }

    echo "=== User #{$userId} ===\n\n";

    // Show user's top-rated movies
    $userRatings = $ratings[$userId];
    arsort($userRatings);

    echo "User's Favorite Movies:\n";
    $count = 0;
    foreach ($userRatings as $movieId => $rating) {
        if ($count++ >= 3) break;

        $movie = $movies[$movieId];
        echo sprintf("  ⭐ %.1f - %s (%s)\n", $rating, $movie['title'], $movie['genre']);
    }

    // Get recommendations
    echo "\nRecommended for You:\n";
    $recommendations = $recommender->recommend($userId, 5, 10);

    $rank = 1;
    foreach ($recommendations as $movieId => $predictedRating) {
        $movie = $movies[$movieId];
        echo sprintf(
            "  %d. ⭐ %.2f - %s (%s, %d)\n",
            $rank++,
            $predictedRating,
            $movie['title'],
            $movie['genre'],
            $movie['year']
        );
    }

    echo "\n" . str_repeat('-', 60) . "\n\n";
}

// Analyze recommendation diversity
echo "=== Recommendation Diversity Analysis ===\n\n";

$allRecommendations = [];
$genreDistribution = [];

foreach ($sampleUsers as $userId) {
    if (!isset($ratings[$userId])) {
        continue;
    }

    $recommendations = $recommender->recommend($userId, 10, 10);

    foreach ($recommendations as $movieId => $predictedRating) {
        $allRecommendations[] = $movieId;

        $genre = $movies[$movieId]['genre'];
        $genreDistribution[$genre] = ($genreDistribution[$genre] ?? 0) + 1;
    }
}

$uniqueMovies = count(array_unique($allRecommendations));
$totalRecommendations = count($allRecommendations);

echo "Overall Statistics:\n";
echo "  Total recommendations: {$totalRecommendations}\n";
echo "  Unique movies recommended: {$uniqueMovies}\n";
echo "  Diversity: " . round(($uniqueMovies / $totalRecommendations) * 100, 1) . "%\n\n";

echo "Recommended Genre Distribution:\n";
arsort($genreDistribution);

foreach ($genreDistribution as $genre => $count) {
    $percentage = ($count / $totalRecommendations) * 100;
    $bar = str_repeat('█', (int) ($percentage / 2));
    echo sprintf("  %s: %2d (%5.1f%%) %s\n", ucfirst($genre), $count, $percentage, $bar);
}

// Find most frequently recommended movies
$movieCounts = array_count_values($allRecommendations);
arsort($movieCounts);

echo "\n\nMost Frequently Recommended Movies:\n";
$top = array_slice($movieCounts, 0, 5, true);

foreach ($top as $movieId => $count) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %s (%s) - recommended %d times\n",
        $movie['title'],
        $movie['genre'],
        $count
    );
}

// Cold start example - user with few ratings
echo "\n\n=== Cold Start Example ===\n\n";

// Create a user with only 3 ratings
$newUserId = 9999;
$ratings[$newUserId] = [
    1 => 5.0,  // The Matrix Revolution
    11 => 4.5, // Superbad
    21 => 5.0, // The Shawshank Redemption
];

echo "New User with only 3 ratings:\n";
foreach ($ratings[$newUserId] as $movieId => $rating) {
    $movie = $movies[$movieId];
    echo sprintf("  ⭐ %.1f - %s (%s)\n", $rating, $movie['title'], $movie['genre']);
}

echo "\nRecommendations for New User:\n";
$newRecommender = new UserBasedCollaborativeFilter($ratings);
$newRecommendations = $newRecommender->recommend($newUserId, 10, 10);

if (empty($newRecommendations)) {
    echo "  ⚠️  Not enough data to generate recommendations (cold start problem)\n";
} else {
    $rank = 1;
    foreach ($newRecommendations as $movieId => $predictedRating) {
        $movie = $movies[$movieId];
        echo sprintf(
            "  %d. ⭐ %.2f - %s (%s)\n",
            $rank++,
            $predictedRating,
            $movie['title'],
            $movie['genre']
        );
    }
}

echo "\n✅ Recommendation generation complete!\n";

