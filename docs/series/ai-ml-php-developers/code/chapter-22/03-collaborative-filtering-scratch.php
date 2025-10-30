<?php

declare(strict_types=1);

/**
 * User-based collaborative filtering from scratch.
 *
 * This script demonstrates:
 * - Building a complete CF system without libraries
 * - Finding similar users with k-nearest neighbors
 * - Predicting ratings based on similar users
 * - Understanding the core CF algorithm
 */

/**
 * User-based collaborative filtering recommender.
 */
class UserBasedCollaborativeFilter
{
    private array $ratingsMatrix;
    private array $similarityCache = [];

    public function __construct(array $ratingsMatrix)
    {
        $this->ratingsMatrix = $ratingsMatrix;
    }

    /**
     * Calculate cosine similarity between two users.
     */
    private function cosineSimilarity(array $userA, array $userB): float
    {
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

        return ($magnitudeA > 0 && $magnitudeB > 0)
            ? $dotProduct / ($magnitudeA * $magnitudeB)
            : 0.0;
    }

    /**
     * Find k most similar users to the target user.
     *
     * @return array Array of [userId => similarity] sorted by similarity
     */
    public function findSimilarUsers(int $userId, int $k = 5): array
    {
        if (!isset($this->ratingsMatrix[$userId])) {
            return [];
        }

        $similarities = [];

        foreach ($this->ratingsMatrix as $otherUserId => $otherRatings) {
            if ($otherUserId === $userId) {
                continue;
            }

            $similarity = $this->cosineSimilarity(
                $this->ratingsMatrix[$userId],
                $otherRatings
            );

            if ($similarity > 0) {
                $similarities[$otherUserId] = $similarity;
            }
        }

        // Sort by similarity (descending) and return top k
        arsort($similarities);

        return array_slice($similarities, 0, $k, true);
    }

    /**
     * Predict rating for a movie based on similar users.
     *
     * Uses weighted average of similar users' ratings.
     *
     * @return float|null Predicted rating or null if cannot predict
     */
    public function predictRating(int $userId, int $movieId, int $k = 5): ?float
    {
        // If user has already rated this movie, return actual rating
        if (isset($this->ratingsMatrix[$userId][$movieId])) {
            return $this->ratingsMatrix[$userId][$movieId];
        }

        // Find similar users who have rated this movie
        $similarUsers = $this->findSimilarUsers($userId, $k * 2); // Get more candidates

        $weightedSum = 0.0;
        $similaritySum = 0.0;
        $count = 0;

        foreach ($similarUsers as $similarUserId => $similarity) {
            if (isset($this->ratingsMatrix[$similarUserId][$movieId])) {
                $weightedSum += $similarity * $this->ratingsMatrix[$similarUserId][$movieId];
                $similaritySum += $similarity;
                $count++;

                if ($count >= $k) {
                    break;
                }
            }
        }

        if ($similaritySum == 0) {
            return null;
        }

        return $weightedSum / $similaritySum;
    }

    /**
     * Get top N movie recommendations for a user.
     *
     * @return array Array of [movieId => predictedRating]
     */
    public function recommend(int $userId, int $n = 10, int $k = 10): array
    {
        if (!isset($this->ratingsMatrix[$userId])) {
            return [];
        }

        // Get all movies the user hasn't rated
        $allMovies = [];
        foreach ($this->ratingsMatrix as $userRatings) {
            $allMovies = array_merge($allMovies, array_keys($userRatings));
        }
        $allMovies = array_unique($allMovies);

        $unratedMovies = array_diff($allMovies, array_keys($this->ratingsMatrix[$userId]));

        // Predict ratings for unrated movies
        $predictions = [];

        foreach ($unratedMovies as $movieId) {
            $prediction = $this->predictRating($userId, $movieId, $k);

            if ($prediction !== null) {
                $predictions[$movieId] = $prediction;
            }
        }

        // Sort by predicted rating (descending) and return top N
        arsort($predictions);

        return array_slice($predictions, 0, $n, true);
    }
}

echo "=== User-Based Collaborative Filtering (From Scratch) ===\n\n";

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

// Load movie metadata
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

// Test with a sample user
$targetUserId = 5;

echo "Target User: #{$targetUserId}\n\n";

// Show user's existing ratings
echo "User's Existing Ratings (Top 5):\n";
$userRatings = $ratings[$targetUserId];
arsort($userRatings);

$count = 0;
foreach ($userRatings as $movieId => $rating) {
    if ($count++ >= 5) break;

    $movie = $movies[$movieId];
    echo sprintf("  ⭐ %.1f - %s (%s)\n", $rating, $movie['title'], $movie['genre']);
}

// Find similar users
echo "\n\nMost Similar Users:\n";
$similarUsers = $recommender->findSimilarUsers($targetUserId, 5);

foreach ($similarUsers as $userId => $similarity) {
    echo sprintf("  User #%d: %.3f similarity\n", $userId, $similarity);
}

// Get recommendations
echo "\n\nTop 10 Recommended Movies:\n";
$recommendations = $recommender->recommend($targetUserId, 10, 10);

$rank = 1;
foreach ($recommendations as $movieId => $predictedRating) {
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

// Test prediction for a specific movie
$testMovieId = array_key_first($recommendations);
$prediction = $recommender->predictRating($targetUserId, $testMovieId, 10);

echo "\n\nPrediction Details:\n";
echo "  Movie: {$movies[$testMovieId]['title']}\n";
echo "  Predicted Rating: " . number_format($prediction ?? 0, 2) . "\n";

echo "\n✅ Collaborative filtering complete!\n";

