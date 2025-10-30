<?php

declare(strict_types=1);

/**
 * Item-based collaborative filtering implementation.
 *
 * This script demonstrates:
 * - Item-to-item similarity instead of user-to-user
 * - Pre-computing item similarities for efficiency
 * - Generating recommendations from similar items
 * - Comparing with user-based approach
 */

/**
 * Item-based collaborative filtering recommender.
 */
class ItemBasedCollaborativeFilter
{
    private array $ratingsMatrix;
    private array $itemSimilarities = [];

    public function __construct(array $ratingsMatrix)
    {
        $this->ratingsMatrix = $ratingsMatrix;
    }

    /**
     * Calculate cosine similarity between two items.
     */
    private function cosineSimilarity(array $itemA, array $itemB): float
    {
        $commonUsers = array_intersect_key($itemA, $itemB);

        if (count($commonUsers) < 2) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        foreach ($commonUsers as $userId => $ratingA) {
            $ratingB = $itemB[$userId];
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
     * Build item-item similarity matrix.
     *
     * This can be pre-computed and cached for better performance.
     */
    public function buildItemSimilarities(): void
    {
        echo "Building item-item similarity matrix...\n";

        // Convert user-item matrix to item-user matrix
        $itemRatings = [];
        foreach ($this->ratingsMatrix as $userId => $userRatings) {
            foreach ($userRatings as $movieId => $rating) {
                $itemRatings[$movieId][$userId] = $rating;
            }
        }

        $movieIds = array_keys($itemRatings);
        $total = count($movieIds);
        $progress = 0;

        foreach ($movieIds as $i => $movieA) {
            foreach ($movieIds as $j => $movieB) {
                if ($i >= $j) {
                    continue; // Skip self and duplicate pairs
                }

                $similarity = $this->cosineSimilarity(
                    $itemRatings[$movieA],
                    $itemRatings[$movieB]
                );

                if ($similarity > 0) {
                    $this->itemSimilarities[$movieA][$movieB] = $similarity;
                    $this->itemSimilarities[$movieB][$movieA] = $similarity;
                }
            }

            // Progress indicator
            if (++$progress % 10 === 0) {
                $percentage = ($progress / $total) * 100;
                echo "\r  Progress: " . round($percentage, 1) . "%";
            }
        }

        echo "\r  Progress: 100.0%\n";
        echo "Item similarities computed: " . count($this->itemSimilarities) . " items\n\n";
    }

    /**
     * Find k most similar items to the target item.
     */
    public function findSimilarItems(int $movieId, int $k = 10): array
    {
        if (!isset($this->itemSimilarities[$movieId])) {
            return [];
        }

        $similarities = $this->itemSimilarities[$movieId];
        arsort($similarities);

        return array_slice($similarities, 0, $k, true);
    }

    /**
     * Predict rating based on similar items the user has rated.
     */
    public function predictRating(int $userId, int $movieId, int $k = 10): ?float
    {
        if (isset($this->ratingsMatrix[$userId][$movieId])) {
            return $this->ratingsMatrix[$userId][$movieId];
        }

        if (!isset($this->ratingsMatrix[$userId])) {
            return null;
        }

        $similarItems = $this->findSimilarItems($movieId, $k * 2);

        $weightedSum = 0.0;
        $similaritySum = 0.0;
        $count = 0;

        foreach ($similarItems as $similarMovieId => $similarity) {
            if (isset($this->ratingsMatrix[$userId][$similarMovieId])) {
                $weightedSum += $similarity * $this->ratingsMatrix[$userId][$similarMovieId];
                $similaritySum += $similarity;
                $count++;

                if ($count >= $k) {
                    break;
                }
            }
        }

        return $similaritySum > 0 ? $weightedSum / $similaritySum : null;
    }

    /**
     * Get top N recommendations for a user.
     */
    public function recommend(int $userId, int $n = 10, int $k = 10): array
    {
        if (!isset($this->ratingsMatrix[$userId])) {
            return [];
        }

        // Get all movies
        $allMovies = array_keys($this->itemSimilarities);
        $unratedMovies = array_diff($allMovies, array_keys($this->ratingsMatrix[$userId]));

        $predictions = [];

        foreach ($unratedMovies as $movieId) {
            $prediction = $this->predictRating($userId, $movieId, $k);

            if ($prediction !== null) {
                $predictions[$movieId] = $prediction;
            }
        }

        arsort($predictions);

        return array_slice($predictions, 0, $n, true);
    }
}

echo "=== Item-Based Collaborative Filtering ===\n\n";

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

// Create item-based recommender
$itemBasedRecommender = new ItemBasedCollaborativeFilter($ratings);

// Build similarity matrix (this can be pre-computed and cached)
$itemBasedRecommender->buildItemSimilarities();

// Show similar items for a sample movie
$sampleMovieId = 1; // The Matrix Revolution
echo "Similar Movies to \"{$movies[$sampleMovieId]['title']}\" (Sci-Fi):\n\n";

$similarItems = $itemBasedRecommender->findSimilarItems($sampleMovieId, 10);

foreach ($similarItems as $movieId => $similarity) {
    $movie = $movies[$movieId];
    $bar = str_repeat('█', (int) ($similarity * 20));

    echo sprintf(
        "  %.3f %s - %s (%s)\n",
        $similarity,
        $bar,
        $movie['title'],
        $movie['genre']
    );
}

// Generate recommendations for a user
$targetUserId = 5;

echo "\n\n=== Recommendations for User #{$targetUserId} ===\n\n";

echo "User's Rated Movies (Top 5):\n";
$userRatings = $ratings[$targetUserId];
arsort($userRatings);

$count = 0;
foreach ($userRatings as $movieId => $rating) {
    if ($count++ >= 5) break;

    $movie = $movies[$movieId];
    echo sprintf("  ⭐ %.1f - %s (%s)\n", $rating, $movie['title'], $movie['genre']);
}

echo "\n\nItem-Based Recommendations:\n";
$recommendations = $itemBasedRecommender->recommend($targetUserId, 10, 10);

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

// Compare with user-based approach
require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

echo "\n\nComparing with User-Based Approach:\n\n";

$userBasedRecommender = new UserBasedCollaborativeFilter($ratings);
$userBasedRecs = $userBasedRecommender->recommend($targetUserId, 10, 10);

echo "User-Based Recommendations:\n";
$rank = 1;
foreach ($userBasedRecs as $movieId => $predictedRating) {
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
$itemBasedMovies = array_keys($recommendations);
$userBasedMovies = array_keys($userBasedRecs);
$overlap = array_intersect($itemBasedMovies, $userBasedMovies);

echo "\n\nComparison:\n";
echo "  Item-based recommendations: " . count($recommendations) . "\n";
echo "  User-based recommendations: " . count($userBasedRecs) . "\n";
echo "  Overlap: " . count($overlap) . " movies\n";
echo "  Jaccard similarity: " . number_format(
    count($overlap) / count(array_unique(array_merge($itemBasedMovies, $userBasedMovies))),
    3
) . "\n";

echo "\n✅ Item-based collaborative filtering complete!\n";

