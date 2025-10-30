<?php

declare(strict_types=1);

/**
 * Handling cold start problems in recommendation systems.
 *
 * This script demonstrates:
 * - Detecting cold start scenarios (new users/items)
 * - Popularity-based fallback recommendations
 * - Content-based filtering for new items
 * - Hybrid approaches combining multiple strategies
 */

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

/**
 * Cold start handler with multiple fallback strategies.
 */
class ColdStartHandler
{
    private array $ratingsMatrix;
    private array $movies;
    private ?UserBasedCollaborativeFilter $cfRecommender = null;
    private array $popularityCache = [];

    public function __construct(array $ratingsMatrix, array $movies)
    {
        $this->ratingsMatrix = $ratingsMatrix;
        $this->movies = $movies;
        $this->cfRecommender = new UserBasedCollaborativeFilter($ratingsMatrix);
        $this->calculatePopularity();
    }

    /**
     * Calculate movie popularity (rating count and average).
     */
    private function calculatePopularity(): void
    {
        $movieStats = [];

        foreach ($this->ratingsMatrix as $userRatings) {
            foreach ($userRatings as $movieId => $rating) {
                if (!isset($movieStats[$movieId])) {
                    $movieStats[$movieId] = ['sum' => 0, 'count' => 0];
                }
                $movieStats[$movieId]['sum'] += $rating;
                $movieStats[$movieId]['count']++;
            }
        }

        foreach ($movieStats as $movieId => $stats) {
            $avgRating = $stats['sum'] / $stats['count'];
            // Popularity score: combine rating and count (weighted)
            $popularity = $avgRating * log($stats['count'] + 1);

            $this->popularityCache[$movieId] = [
                'avg_rating' => $avgRating,
                'rating_count' => $stats['count'],
                'popularity' => $popularity,
            ];
        }

        uasort($this->popularityCache, fn($a, $b) => $b['popularity'] <=> $a['popularity']);
    }

    /**
     * Detect if user has cold start problem.
     */
    public function isUserColdStart(int $userId, int $threshold = 5): bool
    {
        if (!isset($this->ratingsMatrix[$userId])) {
            return true;
        }

        return count($this->ratingsMatrix[$userId]) < $threshold;
    }

    /**
     * Get popular movies (fallback for new users).
     */
    public function getPopularMovies(int $n = 10, ?array $excludeMovies = null): array
    {
        $recommendations = [];

        foreach ($this->popularityCache as $movieId => $stats) {
            if ($excludeMovies && in_array($movieId, $excludeMovies)) {
                continue;
            }

            $recommendations[$movieId] = $stats['avg_rating'];

            if (count($recommendations) >= $n) {
                break;
            }
        }

        return $recommendations;
    }

    /**
     * Get genre-based recommendations (content-based filtering).
     */
    public function getGenreBasedRecommendations(int $userId, int $n = 10): array
    {
        if (!isset($this->ratingsMatrix[$userId]) || empty($this->ratingsMatrix[$userId])) {
            return [];
        }

        // Find user's preferred genres
        $genrePreferences = [];

        foreach ($this->ratingsMatrix[$userId] as $movieId => $rating) {
            if (!isset($this->movies[$movieId])) {
                continue;
            }

            $genre = $this->movies[$movieId]['genre'];

            if (!isset($genrePreferences[$genre])) {
                $genrePreferences[$genre] = ['sum' => 0, 'count' => 0];
            }

            $genrePreferences[$genre]['sum'] += $rating;
            $genrePreferences[$genre]['count']++;
        }

        // Calculate average rating per genre
        foreach ($genrePreferences as $genre => &$stats) {
            $stats['avg'] = $stats['sum'] / $stats['count'];
        }

        // Sort by preference
        uasort($genrePreferences, fn($a, $b) => $b['avg'] <=> $a['avg']);
        $topGenres = array_slice(array_keys($genrePreferences), 0, 2);

        // Find highly-rated movies in preferred genres
        $recommendations = [];
        $ratedMovies = array_keys($this->ratingsMatrix[$userId]);

        foreach ($this->popularityCache as $movieId => $stats) {
            if (in_array($movieId, $ratedMovies)) {
                continue;
            }

            if (!isset($this->movies[$movieId])) {
                continue;
            }

            $genre = $this->movies[$movieId]['genre'];

            if (in_array($genre, $topGenres)) {
                $recommendations[$movieId] = $stats['avg_rating'];

                if (count($recommendations) >= $n) {
                    break;
                }
            }
        }

        return $recommendations;
    }

    /**
     * Smart recommendation with cold start handling.
     */
    public function recommend(int $userId, int $n = 10): array
    {
        // Check for cold start
        $ratingCount = isset($this->ratingsMatrix[$userId])
            ? count($this->ratingsMatrix[$userId])
            : 0;

        if ($ratingCount === 0) {
            // Complete cold start: use popularity
            return $this->getPopularMovies($n);
        } elseif ($ratingCount < 5) {
            // Partial cold start: blend genre-based and popular
            $genreRecs = $this->getGenreBasedRecommendations($userId, $n);

            if (count($genreRecs) < $n) {
                $excludeMovies = array_merge(
                    array_keys($this->ratingsMatrix[$userId]),
                    array_keys($genreRecs)
                );
                $popularRecs = $this->getPopularMovies($n - count($genreRecs), $excludeMovies);
                $genreRecs = array_merge($genreRecs, $popularRecs);
            }

            return $genreRecs;
        } else {
            // Enough data: use collaborative filtering
            return $this->cfRecommender->recommend($userId, $n, 10);
        }
    }
}

echo "=== Cold Start Problem Handling ===\n\n";

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

// Create cold start handler
$handler = new ColdStartHandler($ratings, $movies);

// Scenario 1: Complete cold start (new user with no ratings)
echo "=== Scenario 1: New User (No Ratings) ===\n\n";

$newUserId = 9999;
echo "Recommendations for completely new user:\n";
echo "(Using popularity-based approach)\n\n";

$popularRecs = $handler->recommend($newUserId, 10);

$rank = 1;
foreach ($popularRecs as $movieId => $score) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s, %d)\n",
        $rank++,
        $score,
        $movie['title'],
        $movie['genre'],
        $movie['year']
    );
}

// Scenario 2: Partial cold start (user with few ratings)
echo "\n\n=== Scenario 2: New User with 3 Ratings ===\n\n";

$partialColdUserId = 9998;
$ratings[$partialColdUserId] = [
    1 => 5.0,   // The Matrix Revolution (sci-fi)
    4 => 4.5,   // Inception Dreams (sci-fi)
    21 => 5.0,  // The Shawshank Redemption (drama)
];

echo "User's initial ratings:\n";
foreach ($ratings[$partialColdUserId] as $movieId => $rating) {
    $movie = $movies[$movieId];
    echo sprintf("  ⭐ %.1f - %s (%s)\n", $rating, $movie['title'], $movie['genre']);
}

echo "\nRecommendations (Using hybrid genre + popularity approach):\n\n";

$handler = new ColdStartHandler($ratings, $movies);
$hybridRecs = $handler->recommend($partialColdUserId, 10);

$rank = 1;
foreach ($hybridRecs as $movieId => $score) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s, %d)\n",
        $rank++,
        $score,
        $movie['title'],
        $movie['genre'],
        $movie['year']
    );
}

// Scenario 3: Warm user (enough ratings for CF)
echo "\n\n=== Scenario 3: Established User (10+ Ratings) ===\n\n";

$warmUserId = 5;
$ratingCount = count($ratings[$warmUserId]);

echo "User #{$warmUserId} with {$ratingCount} ratings\n";
echo "(Using collaborative filtering approach)\n\n";

$cfRecs = $handler->recommend($warmUserId, 10);

$rank = 1;
foreach ($cfRecs as $movieId => $score) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s, %d)\n",
        $rank++,
        $score,
        $movie['title'],
        $movie['genre'],
        $movie['year']
    );
}

// Show popular movies
echo "\n\n=== Most Popular Movies Overall ===\n\n";

$popular = $handler->getPopularMovies(10);

$rank = 1;
foreach ($popular as $movieId => $avgRating) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s, %d)\n",
        $rank++,
        $avgRating,
        $movie['title'],
        $movie['genre'],
        $movie['year']
    );
}

echo "\n\n=== Cold Start Strategies Summary ===\n\n";
echo "1. Complete Cold Start (0 ratings):\n";
echo "   → Recommend globally popular items\n\n";

echo "2. Partial Cold Start (1-4 ratings):\n";
echo "   → Use content-based filtering (genres)\n";
echo "   → Blend with popular items\n\n";

echo "3. Warm Users (5+ ratings):\n";
echo "   → Use full collaborative filtering\n\n";

echo "4. Item Cold Start (new movies):\n";
echo "   → Use content-based features (genre, director, actors)\n";
echo "   → Show to diverse users to gather initial ratings\n";

echo "\n✅ Cold start handling complete!\n";

