<?php

declare(strict_types=1);

/**
 * Calculate user similarity using different metrics.
 *
 * This script demonstrates:
 * - Cosine similarity for user vectors
 * - Pearson correlation coefficient
 * - Finding similar users
 * - Comparing similarity metrics
 */

/**
 * Calculate cosine similarity between two users.
 *
 * Measures the cosine of the angle between two rating vectors.
 * Range: -1 (opposite) to 1 (identical), 0 (orthogonal/no similarity)
 */
function cosineSimilarity(array $userA, array $userB): float
{
    // Find common movies
    $commonMovies = array_intersect_key($userA, $userB);

    if (count($commonMovies) === 0) {
        return 0.0;
    }

    $dotProduct = 0.0;
    $magnitudeA = 0.0;
    $magnitudeB = 0.0;

    foreach ($commonMovies as $movieId => $ratingA) {
        $ratingB = $userB[$movieId];

        $dotProduct += $ratingA * $ratingB;
        $magnitudeA += $ratingA * $ratingA;
        $magnitudeB += $ratingB * $ratingB;
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    if ($magnitudeA == 0 || $magnitudeB == 0) {
        return 0.0;
    }

    return $dotProduct / ($magnitudeA * $magnitudeB);
}

/**
 * Calculate Pearson correlation coefficient between two users.
 *
 * Measures linear correlation, accounting for rating scale differences.
 * Range: -1 (negative correlation) to 1 (positive correlation)
 */
function pearsonCorrelation(array $userA, array $userB): float
{
    // Find common movies
    $commonMovies = array_intersect_key($userA, $userB);

    if (count($commonMovies) < 2) {
        return 0.0;
    }

    $n = count($commonMovies);

    // Calculate means
    $meanA = array_sum($commonMovies) / $n;
    $meanB = array_sum(array_intersect_key($userB, $commonMovies)) / $n;

    $numerator = 0.0;
    $sumSquaresA = 0.0;
    $sumSquaresB = 0.0;

    foreach ($commonMovies as $movieId => $ratingA) {
        $ratingB = $userB[$movieId];

        $diffA = $ratingA - $meanA;
        $diffB = $ratingB - $meanB;

        $numerator += $diffA * $diffB;
        $sumSquaresA += $diffA * $diffA;
        $sumSquaresB += $diffB * $diffB;
    }

    $denominator = sqrt($sumSquaresA * $sumSquaresB);

    if ($denominator == 0) {
        return 0.0;
    }

    return $numerator / $denominator;
}

echo "=== User Similarity Calculation ===\n\n";

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

// Calculate similarity between first few users
$userIds = array_slice(array_keys($ratings), 0, 5);

echo "Comparing Similarity Metrics:\n\n";
echo "User Pair                | Cosine | Pearson | Common Movies\n";
echo "-------------------------|--------|---------|---------------\n";

for ($i = 0; $i < count($userIds) - 1; $i++) {
    for ($j = $i + 1; $j < count($userIds); $j++) {
        $userA = $userIds[$i];
        $userB = $userIds[$j];

        $cosine = cosineSimilarity($ratings[$userA], $ratings[$userB]);
        $pearson = pearsonCorrelation($ratings[$userA], $ratings[$userB]);
        $commonMovies = count(array_intersect_key($ratings[$userA], $ratings[$userB]));

        echo sprintf(
            "User %2d <-> User %2d    | %6.3f | %7.3f | %13d\n",
            $userA,
            $userB,
            $cosine,
            $pearson,
            $commonMovies
        );
    }
}

// Find most similar users for a target user
$targetUserId = 1;
$similarities = [];

echo "\n\nFinding Similar Users for User #{$targetUserId}:\n\n";

foreach ($ratings as $userId => $userRatings) {
    if ($userId === $targetUserId) {
        continue;
    }

    $similarity = cosineSimilarity($ratings[$targetUserId], $userRatings);

    if ($similarity > 0) {
        $similarities[$userId] = $similarity;
    }
}

arsort($similarities);
$topSimilar = array_slice($similarities, 0, 10, true);

echo "Top 10 Most Similar Users (Cosine Similarity):\n";
foreach ($topSimilar as $userId => $similarity) {
    $commonMovies = count(array_intersect_key($ratings[$targetUserId], $ratings[$userId]));
    $bar = str_repeat('█', (int) ($similarity * 20));

    echo sprintf(
        "  User %3d: %.3f %s (%d common movies)\n",
        $userId,
        $similarity,
        $bar,
        $commonMovies
    );
}

echo "\n✅ User similarity calculation complete!\n";

