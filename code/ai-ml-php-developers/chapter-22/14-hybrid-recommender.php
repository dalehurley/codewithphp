<?php

declare(strict_types=1);

/**
 * Hybrid recommendation system combining multiple approaches.
 *
 * This script demonstrates:
 * - Combining collaborative + content-based filtering
 * - Weighting different recommendation strategies
 * - Improving coverage and diversity
 * - Handling various edge cases
 */

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

/**
 * Hybrid recommender combining collaborative and content-based filtering.
 */
class HybridRecommender
{
    private UserBasedCollaborativeFilter $cfRecommender;
    private array $ratingsMatrix;
    private array $movies;
    private array $config;

    public function __construct(
        array $ratingsMatrix,
        array $movies,
        array $config = []
    ) {
        $this->ratingsMatrix = $ratingsMatrix;
        $this->movies = $movies;
        $this->cfRecommender = new UserBasedCollaborativeFilter($ratingsMatrix);

        $this->config = array_merge([
            'cf_weight' => 0.7,         // Weight for collaborative filtering
            'content_weight' => 0.3,     // Weight for content-based
            'popularity_weight' => 0.1,  // Weight for popularity
            'diversity_boost' => true,   // Boost diverse genres
        ], $config);
    }

    /**
     * Get content-based recommendations (genre similarity).
     */
    private function getContentBasedRecommendations(int $userId, int $n = 10): array
    {
        if (!isset($this->ratingsMatrix[$userId])) {
            return [];
        }

        // Find user's genre preferences
        $genreRatings = [];

        foreach ($this->ratingsMatrix[$userId] as $movieId => $rating) {
            if (!isset($this->movies[$movieId])) {
                continue;
            }

            $genre = $this->movies[$movieId]['genre'];

            if (!isset($genreRatings[$genre])) {
                $genreRatings[$genre] = ['sum' => 0, 'count' => 0];
            }

            $genreRatings[$genre]['sum'] += $rating;
            $genreRatings[$genre]['count']++;
        }

        // Calculate genre preferences
        $genrePreferences = [];
        foreach ($genreRatings as $genre => $data) {
            $genrePreferences[$genre] = $data['sum'] / $data['count'];
        }

        // Score movies based on genre preference
        $scores = [];
        $ratedMovies = array_keys($this->ratingsMatrix[$userId]);

        foreach ($this->movies as $movieId => $movie) {
            if (in_array($movieId, $ratedMovies)) {
                continue;
            }

            $genre = $movie['genre'];

            if (isset($genrePreferences[$genre])) {
                $scores[$movieId] = $genrePreferences[$genre];
            }
        }

        arsort($scores);

        return array_slice($scores, 0, $n, true);
    }

    /**
     * Get popularity-based recommendations.
     */
    private function getPopularityRecommendations(int $userId, int $n = 10): array
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

        $popularity = [];
        $ratedMovies = isset($this->ratingsMatrix[$userId])
            ? array_keys($this->ratingsMatrix[$userId])
            : [];

        foreach ($movieStats as $movieId => $stats) {
            if (in_array($movieId, $ratedMovies)) {
                continue;
            }

            $avgRating = $stats['sum'] / $stats['count'];
            $popularity[$movieId] = $avgRating * log($stats['count'] + 1);
        }

        arsort($popularity);

        return array_slice($popularity, 0, $n, true);
    }

    /**
     * Apply diversity boost to promote genre variety.
     */
    private function applyDiversityBoost(array $recommendations): array
    {
        $genreCounts = [];
        $boosted = [];

        foreach ($recommendations as $movieId => $score) {
            if (!isset($this->movies[$movieId])) {
                $boosted[$movieId] = $score;
                continue;
            }

            $genre = $this->movies[$movieId]['genre'];
            $genreCounts[$genre] = ($genreCounts[$genre] ?? 0) + 1;

            // Penalize overrepresented genres
            $diversityPenalty = 1 - (($genreCounts[$genre] - 1) * 0.1);
            $diversityPenalty = max(0.7, $diversityPenalty);

            $boosted[$movieId] = $score * $diversityPenalty;
        }

        arsort($boosted);

        return $boosted;
    }

    /**
     * Get hybrid recommendations combining all strategies.
     */
    public function recommend(int $userId, int $n = 10): array
    {
        // Get recommendations from each approach
        $cfRecs = $this->cfRecommender->recommend($userId, $n * 2, 10);
        $contentRecs = $this->getContentBasedRecommendations($userId, $n * 2);
        $popularRecs = $this->getPopularityRecommendations($userId, $n * 2);

        // Combine scores with weights
        $combinedScores = [];

        // Normalize scores to 0-1 range for each approach
        $cfMax = !empty($cfRecs) ? max($cfRecs) : 1;
        $contentMax = !empty($contentRecs) ? max($contentRecs) : 1;
        $popularMax = !empty($popularRecs) ? max($popularRecs) : 1;

        // Add weighted collaborative filtering scores
        foreach ($cfRecs as $movieId => $score) {
            $normalized = $score / $cfMax;
            $combinedScores[$movieId] = ($combinedScores[$movieId] ?? 0)
                + ($normalized * $this->config['cf_weight']);
        }

        // Add weighted content-based scores
        foreach ($contentRecs as $movieId => $score) {
            $normalized = $score / $contentMax;
            $combinedScores[$movieId] = ($combinedScores[$movieId] ?? 0)
                + ($normalized * $this->config['content_weight']);
        }

        // Add weighted popularity scores
        foreach ($popularRecs as $movieId => $score) {
            $normalized = $score / $popularMax;
            $combinedScores[$movieId] = ($combinedScores[$movieId] ?? 0)
                + ($normalized * $this->config['popularity_weight']);
        }

        // Apply diversity boost if enabled
        if ($this->config['diversity_boost']) {
            $combinedScores = $this->applyDiversityBoost($combinedScores);
        }

        arsort($combinedScores);

        return array_slice($combinedScores, 0, $n, true);
    }

    /**
     * Get recommendations with breakdown.
     */
    public function recommendWithBreakdown(int $userId, int $n = 10): array
    {
        $cfRecs = $this->cfRecommender->recommend($userId, $n, 10);
        $contentRecs = $this->getContentBasedRecommendations($userId, $n);
        $hybridRecs = $this->recommend($userId, $n);

        return [
            'collaborative' => $cfRecs,
            'content_based' => $contentRecs,
            'hybrid' => $hybridRecs,
        ];
    }
}

echo "=== Hybrid Recommendation System ===\n\n";

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

// Create hybrid recommender
$hybridRecommender = new HybridRecommender($ratings, $movies, [
    'cf_weight' => 0.6,
    'content_weight' => 0.3,
    'popularity_weight' => 0.1,
    'diversity_boost' => true,
]);

$targetUserId = 5;

echo "Comparing Recommendation Approaches for User #{$targetUserId}\n\n";

// Get breakdown
$breakdown = $hybridRecommender->recommendWithBreakdown($targetUserId, 10);

// Show user's preferences
echo "User's Top-Rated Movies:\n";
$userRatings = $ratings[$targetUserId];
arsort($userRatings);

$count = 0;
foreach ($userRatings as $movieId => $rating) {
    if ($count++ >= 5) break;

    $movie = $movies[$movieId];
    echo sprintf("  ⭐ %.1f - %s (%s)\n", $rating, $movie['title'], $movie['genre']);
}

// Collaborative filtering recommendations
echo "\n\n1. COLLABORATIVE FILTERING\n";
echo str_repeat('-', 50) . "\n\n";

$rank = 1;
foreach ($breakdown['collaborative'] as $movieId => $score) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s)\n",
        $rank++,
        $score,
        $movie['title'],
        $movie['genre']
    );
}

// Content-based recommendations
echo "\n\n2. CONTENT-BASED FILTERING\n";
echo str_repeat('-', 50) . "\n\n";

$rank = 1;
foreach ($breakdown['content_based'] as $movieId => $score) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s)\n",
        $rank++,
        $score,
        $movie['title'],
        $movie['genre']
    );
}

// Hybrid recommendations
echo "\n\n3. HYBRID (COMBINED)\n";
echo str_repeat('-', 50) . "\n\n";

$rank = 1;
foreach ($breakdown['hybrid'] as $movieId => $score) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s)\n",
        $rank++,
        $score,
        $movie['title'],
        $movie['genre']
    );
}

// Analyze genre diversity
echo "\n\n=== Genre Diversity Analysis ===\n\n";

$analyzeGenres = function ($recommendations) use ($movies) {
    $genres = [];
    foreach (array_keys($recommendations) as $movieId) {
        if (isset($movies[$movieId])) {
            $genres[] = $movies[$movieId]['genre'];
        }
    }
    return $genres;
};

$cfGenres = $analyzeGenres($breakdown['collaborative']);
$contentGenres = $analyzeGenres($breakdown['content_based']);
$hybridGenres = $analyzeGenres($breakdown['hybrid']);

echo "Collaborative Filtering: " . count(array_unique($cfGenres)) . " unique genres\n";
echo "  " . implode(', ', array_unique($cfGenres)) . "\n\n";

echo "Content-Based: " . count(array_unique($contentGenres)) . " unique genres\n";
echo "  " . implode(', ', array_unique($contentGenres)) . "\n\n";

echo "Hybrid: " . count(array_unique($hybridGenres)) . " unique genres\n";
echo "  " . implode(', ', array_unique($hybridGenres)) . "\n\n";

echo "\n=== Hybrid Approach Benefits ===\n\n";
echo "✅ Improved coverage: Combines strengths of multiple approaches\n";
echo "✅ Better cold start: Content-based helps when CF data is sparse\n";
echo "✅ Increased diversity: Promotes variety across genres\n";
echo "✅ Reduced filter bubbles: Content-based introduces new genres\n";
echo "✅ More robust: Less sensitive to data sparsity issues\n";

echo "\n✅ Hybrid recommendation system complete!\n";

