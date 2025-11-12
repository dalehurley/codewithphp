<?php

declare(strict_types=1);

/**
 * Quick start example - basic collaborative filtering in 5 minutes.
 */

echo "=== Quick Start: Movie Recommender ===\n\n";

// Simple ratings data: user_id => [movie_id => rating]
$ratings = [
    1 => [1 => 5.0, 2 => 4.0, 3 => 1.0],
    2 => [1 => 4.5, 2 => 4.5, 4 => 2.0],
    3 => [3 => 5.0, 4 => 4.0, 5 => 3.0],
    4 => [2 => 5.0, 3 => 1.5, 5 => 4.5],
];

$movies = [
    1 => 'The Matrix',
    2 => 'Inception',
    3 => 'The Hangover',
    4 => 'Superbad',
    5 => 'Interstellar',
];

// Find similar users using cosine similarity
function findSimilarUser(int $targetUser, array $ratings): int
{
    $bestSimilarity = -1;
    $mostSimilarUser = null;

    foreach ($ratings as $userId => $userRatings) {
        if ($userId === $targetUser) continue;

        $commonMovies = array_intersect_key($ratings[$targetUser], $userRatings);
        if (empty($commonMovies)) continue;

        $dotProduct = 0;
        $magA = 0;
        $magB = 0;

        foreach ($commonMovies as $movieId => $ratingA) {
            $ratingB = $userRatings[$movieId];
            $dotProduct += $ratingA * $ratingB;
            $magA += $ratingA * $ratingA;
            $magB += $ratingB * $ratingB;
        }

        $similarity = $dotProduct / (sqrt($magA) * sqrt($magB));

        if ($similarity > $bestSimilarity) {
            $bestSimilarity = $similarity;
            $mostSimilarUser = $userId;
        }
    }

    return $mostSimilarUser;
}

// Recommend movies for user 1
$targetUser = 1;
echo "User #{$targetUser} has rated:\n";

foreach ($ratings[$targetUser] as $movieId => $rating) {
    echo "  ⭐ {$rating} - {$movies[$movieId]}\n";
}

$similarUser = findSimilarUser($targetUser, $ratings);

echo "\nMost similar user: #{$similarUser}\n\n";
echo "Recommendations (movies that User #{$similarUser} liked but User #{$targetUser} hasn't seen):\n";

$ratedByTarget = array_keys($ratings[$targetUser]);
$recommendations = [];

foreach ($ratings[$similarUser] as $movieId => $rating) {
    if (!in_array($movieId, $ratedByTarget) && $rating >= 4.0) {
        $recommendations[$movieId] = $rating;
    }
}

arsort($recommendations);

foreach ($recommendations as $movieId => $rating) {
    echo "  ⭐ {$rating} - {$movies[$movieId]}\n";
}

echo "\n✅ That's collaborative filtering in 40 lines of code!\n";
echo "Now let's build a production-ready system...\n";
