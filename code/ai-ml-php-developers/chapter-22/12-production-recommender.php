<?php

declare(strict_types=1);

/**
 * Production-ready recommendation engine with all features.
 *
 * This script demonstrates:
 * - Complete recommender class with error handling
 * - Caching for performance
 * - Configuration options
 * - Logging and monitoring
 * - API-ready interface
 */

/**
 * Production-grade recommendation engine.
 */
final class ProductionRecommender
{
    private array $ratingsMatrix;
    private array $movies;
    private array $similarityCache = [];
    private array $config;
    private array $stats = [];

    public function __construct(
        array $ratingsMatrix,
        array $movies,
        array $config = []
    ) {
        $this->ratingsMatrix = $ratingsMatrix;
        $this->movies = $movies;
        $this->config = array_merge([
            'similarity_metric' => 'cosine',
            'k_neighbors' => 10,
            'min_common_items' => 2,
            'cold_start_threshold' => 5,
            'cache_similarities' => true,
            'normalize_ratings' => false,
        ], $config);

        $this->stats = [
            'predictions' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
        ];
    }

    /**
     * Calculate similarity between two users.
     */
    private function calculateSimilarity(array $userA, array $userB): float
    {
        $commonMovies = array_intersect_key($userA, $userB);

        if (count($commonMovies) < $this->config['min_common_items']) {
            return 0.0;
        }

        if ($this->config['similarity_metric'] === 'cosine') {
            return $this->cosineSimilarity($userA, $userB, $commonMovies);
        } elseif ($this->config['similarity_metric'] === 'pearson') {
            return $this->pearsonCorrelation($userA, $userB, $commonMovies);
        }

        throw new InvalidArgumentException(
            "Unknown similarity metric: {$this->config['similarity_metric']}"
        );
    }

    /**
     * Cosine similarity.
     */
    private function cosineSimilarity(array $userA, array $userB, array $commonMovies): float
    {
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        foreach ($commonMovies as $movieId => $ratingA) {
            $ratingB = $userB[$movieId];
            $dotProduct += $ratingA * $ratingB;
            $magnitudeA += $ratingA * $ratingA;
            $magnitudeB += $ratingB * $ratingB;
        }

        $denominator = sqrt($magnitudeA) * sqrt($magnitudeB);
        return $denominator > 0 ? $dotProduct / $denominator : 0.0;
    }

    /**
     * Pearson correlation.
     */
    private function pearsonCorrelation(array $userA, array $userB, array $commonMovies): float
    {
        $n = count($commonMovies);

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
        return $denominator > 0 ? $numerator / $denominator : 0.0;
    }

    /**
     * Find k most similar users with caching.
     */
    private function findSimilarUsers(int $userId, int $k): array
    {
        $cacheKey = "{$userId}_{$k}";

        if ($this->config['cache_similarities'] && isset($this->similarityCache[$cacheKey])) {
            $this->stats['cache_hits']++;
            return $this->similarityCache[$cacheKey];
        }

        $this->stats['cache_misses']++;

        if (!isset($this->ratingsMatrix[$userId])) {
            return [];
        }

        $similarities = [];

        foreach ($this->ratingsMatrix as $otherUserId => $otherRatings) {
            if ($otherUserId === $userId) {
                continue;
            }

            $similarity = $this->calculateSimilarity(
                $this->ratingsMatrix[$userId],
                $otherRatings
            );

            if ($similarity > 0) {
                $similarities[$otherUserId] = $similarity;
            }
        }

        arsort($similarities);
        $result = array_slice($similarities, 0, $k, true);

        if ($this->config['cache_similarities']) {
            $this->similarityCache[$cacheKey] = $result;
        }

        return $result;
    }

    /**
     * Check if user has cold start problem.
     */
    public function isUserColdStart(int $userId): bool
    {
        if (!isset($this->ratingsMatrix[$userId])) {
            return true;
        }

        return count($this->ratingsMatrix[$userId]) < $this->config['cold_start_threshold'];
    }

    /**
     * Get popular movies (for cold start).
     */
    private function getPopularMovies(int $n, array $excludeMovies = []): array
    {
        $movieStats = [];

        foreach ($this->ratingsMatrix as $userRatings) {
            foreach ($userRatings as $movieId => $rating) {
                if (in_array($movieId, $excludeMovies)) {
                    continue;
                }

                if (!isset($movieStats[$movieId])) {
                    $movieStats[$movieId] = ['sum' => 0, 'count' => 0];
                }

                $movieStats[$movieId]['sum'] += $rating;
                $movieStats[$movieId]['count']++;
            }
        }

        $popularity = [];
        foreach ($movieStats as $movieId => $stats) {
            $avgRating = $stats['sum'] / $stats['count'];
            $popularity[$movieId] = $avgRating * log($stats['count'] + 1);
        }

        arsort($popularity);

        return array_slice($popularity, 0, $n, true);
    }

    /**
     * Predict rating for a specific movie.
     */
    public function predictRating(int $userId, int $movieId): ?float
    {
        $this->stats['predictions']++;

        if (isset($this->ratingsMatrix[$userId][$movieId])) {
            return $this->ratingsMatrix[$userId][$movieId];
        }

        if (!isset($this->ratingsMatrix[$userId])) {
            return null;
        }

        $similarUsers = $this->findSimilarUsers($userId, $this->config['k_neighbors'] * 2);

        $weightedSum = 0.0;
        $similaritySum = 0.0;
        $count = 0;

        foreach ($similarUsers as $similarUserId => $similarity) {
            if (isset($this->ratingsMatrix[$similarUserId][$movieId])) {
                $weightedSum += $similarity * $this->ratingsMatrix[$similarUserId][$movieId];
                $similaritySum += $similarity;
                $count++;

                if ($count >= $this->config['k_neighbors']) {
                    break;
                }
            }
        }

        return $similaritySum > 0 ? $weightedSum / $similaritySum : null;
    }

    /**
     * Get recommendations for a user.
     *
     * @return array Array of [movieId => score]
     */
    public function getRecommendations(int $userId, int $n = 10): array
    {
        // Handle cold start
        if ($this->isUserColdStart($userId)) {
            $excludeMovies = isset($this->ratingsMatrix[$userId])
                ? array_keys($this->ratingsMatrix[$userId])
                : [];

            return $this->getPopularMovies($n, $excludeMovies);
        }

        // Get all movies
        $allMovies = array_keys($this->movies);
        $ratedMovies = array_keys($this->ratingsMatrix[$userId]);
        $unratedMovies = array_diff($allMovies, $ratedMovies);

        // Predict ratings
        $predictions = [];

        foreach ($unratedMovies as $movieId) {
            $prediction = $this->predictRating($userId, $movieId);

            if ($prediction !== null) {
                $predictions[$movieId] = $prediction;
            }
        }

        // Sort by predicted rating
        arsort($predictions);

        return array_slice($predictions, 0, $n, true);
    }

    /**
     * Get recommendations with metadata.
     */
    public function getRecommendationsWithMetadata(int $userId, int $n = 10): array
    {
        $recommendations = $this->getRecommendations($userId, $n);

        $results = [];

        foreach ($recommendations as $movieId => $score) {
            $results[] = [
                'movie_id' => $movieId,
                'title' => $this->movies[$movieId]['title'] ?? 'Unknown',
                'genre' => $this->movies[$movieId]['genre'] ?? 'Unknown',
                'year' => $this->movies[$movieId]['year'] ?? null,
                'predicted_rating' => round($score, 2),
            ];
        }

        return $results;
    }

    /**
     * Get performance statistics.
     */
    public function getStats(): array
    {
        $cacheHitRate = $this->stats['cache_hits'] + $this->stats['cache_misses'] > 0
            ? ($this->stats['cache_hits'] / ($this->stats['cache_hits'] + $this->stats['cache_misses'])) * 100
            : 0;

        return [
            'predictions' => $this->stats['predictions'],
            'cache_hits' => $this->stats['cache_hits'],
            'cache_misses' => $this->stats['cache_misses'],
            'cache_hit_rate' => round($cacheHitRate, 2),
            'cache_size' => count($this->similarityCache),
        ];
    }

    /**
     * Clear caches.
     */
    public function clearCache(): void
    {
        $this->similarityCache = [];
        $this->stats = [
            'predictions' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
        ];
    }
}

echo "=== Production Recommender System ===\n\n";

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

// Create production recommender with configuration
$recommender = new ProductionRecommender($ratings, $movies, [
    'similarity_metric' => 'cosine',
    'k_neighbors' => 10,
    'cache_similarities' => true,
    'cold_start_threshold' => 5,
]);

echo "Configuration:\n";
echo "  Similarity metric: cosine\n";
echo "  K neighbors: 10\n";
echo "  Caching enabled: Yes\n";
echo "  Cold start threshold: 5 ratings\n\n";

// Test with multiple users
$testUsers = [5, 10, 15];

foreach ($testUsers as $userId) {
    echo "=== User #{$userId} ===\n\n";

    if ($recommender->isUserColdStart($userId)) {
        echo "⚠️  Cold start detected (using popularity-based recommendations)\n\n";
    }

    $startTime = microtime(true);
    $recommendations = $recommender->getRecommendationsWithMetadata($userId, 5);
    $elapsed = (microtime(true) - $startTime) * 1000;

    echo "Top 5 Recommendations:\n";
    foreach ($recommendations as $i => $rec) {
        echo sprintf(
            "  %d. ⭐ %.2f - %s (%s, %d)\n",
            $i + 1,
            $rec['predicted_rating'],
            $rec['title'],
            $rec['genre'],
            $rec['year']
        );
    }

    echo "\nTime: " . number_format($elapsed, 2) . " ms\n\n";
    echo str_repeat('-', 50) . "\n\n";
}

// Show performance stats
$stats = $recommender->getStats();

echo "=== Performance Statistics ===\n\n";
echo "Total predictions:  {$stats['predictions']}\n";
echo "Cache hits:         {$stats['cache_hits']}\n";
echo "Cache misses:       {$stats['cache_misses']}\n";
echo "Cache hit rate:     {$stats['cache_hit_rate']}%\n";
echo "Cache size:         {$stats['cache_size']} entries\n";

echo "\n✅ Production recommender ready for deployment!\n";

