<?php

declare(strict_types=1);

/**
 * Model persistence - saving and loading trained recommenders.
 *
 * This script demonstrates:
 * - Serializing recommendation models
 * - Pre-computing and caching similarities
 * - Versioning saved models
 * - Loading models for fast predictions
 */

require_once __DIR__ . '/03-collaborative-filtering-scratch.php';

/**
 * Persistent recommender with similarity caching.
 */
class PersistentRecommender
{
    private array $ratingsMatrix;
    private array $similarityMatrix = [];
    private string $modelVersion = '1.0';
    private int $lastTrainedTimestamp = 0;

    public function __construct(array $ratingsMatrix)
    {
        $this->ratingsMatrix = $ratingsMatrix;
    }

    /**
     * Pre-compute all user-user similarities.
     */
    public function train(): void
    {
        echo "Training recommender (computing similarities)...\n";

        $userIds = array_keys($this->ratingsMatrix);
        $total = count($userIds);
        $progress = 0;

        foreach ($userIds as $i => $userA) {
            foreach ($userIds as $j => $userB) {
                if ($i >= $j) {
                    continue;
                }

                $similarity = $this->cosineSimilarity(
                    $this->ratingsMatrix[$userA],
                    $this->ratingsMatrix[$userB]
                );

                if ($similarity > 0) {
                    $this->similarityMatrix[$userA][$userB] = $similarity;
                    $this->similarityMatrix[$userB][$userA] = $similarity;
                }
            }

            if (++$progress % 10 === 0) {
                $percentage = ($progress / $total) * 100;
                echo "\r  Progress: " . round($percentage, 1) . "%";
            }
        }

        echo "\r  Progress: 100.0%\n";

        $this->lastTrainedTimestamp = time();
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
     * Get similar users (using pre-computed similarities).
     */
    public function getSimilarUsers(int $userId, int $k = 10): array
    {
        if (!isset($this->similarityMatrix[$userId])) {
            return [];
        }

        $similarities = $this->similarityMatrix[$userId];
        arsort($similarities);

        return array_slice($similarities, 0, $k, true);
    }

    /**
     * Predict rating using cached similarities.
     */
    public function predictRating(int $userId, int $movieId, int $k = 10): ?float
    {
        if (isset($this->ratingsMatrix[$userId][$movieId])) {
            return $this->ratingsMatrix[$userId][$movieId];
        }

        $similarUsers = $this->getSimilarUsers($userId, $k * 2);

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

        return $similaritySum > 0 ? $weightedSum / $similaritySum : null;
    }

    /**
     * Save model to disk.
     */
    public function save(string $filepath): void
    {
        $modelData = [
            'version' => $this->modelVersion,
            'trained_at' => $this->lastTrainedTimestamp,
            'similarity_matrix' => $this->similarityMatrix,
            'num_users' => count($this->ratingsMatrix),
            'num_ratings' => array_sum(array_map('count', $this->ratingsMatrix)),
        ];

        $serialized = serialize($modelData);
        $compressed = gzcompress($serialized, 9);

        file_put_contents($filepath, $compressed);

        $sizeKB = strlen($compressed) / 1024;
        echo "Model saved to: {$filepath} (" . number_format($sizeKB, 2) . " KB)\n";
    }

    /**
     * Load model from disk.
     */
    public static function load(string $filepath, array $ratingsMatrix): self
    {
        if (!file_exists($filepath)) {
            throw new RuntimeException("Model file not found: {$filepath}");
        }

        $compressed = file_get_contents($filepath);
        $serialized = gzuncompress($compressed);
        $modelData = unserialize($serialized);

        $instance = new self($ratingsMatrix);
        $instance->similarityMatrix = $modelData['similarity_matrix'];
        $instance->modelVersion = $modelData['version'];
        $instance->lastTrainedTimestamp = $modelData['trained_at'];

        echo "Model loaded from: {$filepath}\n";
        echo "  Version: {$modelData['version']}\n";
        echo "  Trained: " . date('Y-m-d H:i:s', $modelData['trained_at']) . "\n";
        echo "  Users: {$modelData['num_users']}\n";
        echo "  Ratings: {$modelData['num_ratings']}\n";

        return $instance;
    }
}

echo "=== Model Persistence & Caching ===\n\n";

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
    ];
}
fclose($file);

$modelPath = __DIR__ . '/recommender-model.dat';

// Check if model exists
if (file_exists($modelPath)) {
    echo "Existing model found.\n\n";
    echo "Options:\n";
    echo "  1. Load existing model (fast)\n";
    echo "  2. Retrain and save new model\n\n";

    $choice = 1; // For demo, always load

    if ($choice == 1) {
        echo "Loading existing model...\n\n";
        $startTime = microtime(true);
        $recommender = PersistentRecommender::load($modelPath, $ratings);
        $loadTime = (microtime(true) - $startTime) * 1000;
        echo "Load time: " . number_format($loadTime, 2) . " ms\n\n";
    } else {
        echo "Training new model...\n\n";
        $startTime = microtime(true);
        $recommender = new PersistentRecommender($ratings);
        $recommender->train();
        $trainTime = (microtime(true) - $startTime);
        echo "Training time: " . number_format($trainTime, 2) . " seconds\n\n";

        $recommender->save($modelPath);
    }
} else {
    echo "No existing model found. Training new model...\n\n";

    $startTime = microtime(true);
    $recommender = new PersistentRecommender($ratings);
    $recommender->train();
    $trainTime = (microtime(true) - $startTime);

    echo "Training time: " . number_format($trainTime, 2) . " seconds\n\n";
    $recommender->save($modelPath);
}

// Make predictions (very fast with cached similarities)
$targetUserId = 5;

echo "\nGenerating recommendations for User #{$targetUserId}...\n\n";

$startTime = microtime(true);

// Get similar users
$similarUsers = $recommender->getSimilarUsers($targetUserId, 10);

// Get unrated movies
$allMovies = array_keys($movies);
$ratedMovies = array_keys($ratings[$targetUserId]);
$unratedMovies = array_diff($allMovies, $ratedMovies);

// Predict ratings
$predictions = [];
foreach ($unratedMovies as $movieId) {
    $prediction = $recommender->predictRating($targetUserId, $movieId, 10);
    if ($prediction !== null) {
        $predictions[$movieId] = $prediction;
    }
}

arsort($predictions);
$predictionTime = (microtime(true) - $startTime) * 1000;

echo "Top 10 Recommendations:\n";
$rank = 1;
foreach (array_slice($predictions, 0, 10, true) as $movieId => $predictedRating) {
    $movie = $movies[$movieId];
    echo sprintf(
        "  %2d. ⭐ %.2f - %s (%s)\n",
        $rank++,
        $predictedRating,
        $movie['title'],
        $movie['genre']
    );
}

echo "\nPrediction time: " . number_format($predictionTime, 2) . " ms (using cached similarities)\n";

// Compare with non-cached version
echo "\n\nComparing with non-cached version...\n\n";

$startTime = microtime(true);
$nonCachedRecommender = new UserBasedCollaborativeFilter($ratings);
$nonCachedRecs = $nonCachedRecommender->recommend($targetUserId, 10, 10);
$nonCachedTime = (microtime(true) - $startTime) * 1000;

echo "Non-cached prediction time: " . number_format($nonCachedTime, 2) . " ms\n";
echo "Speedup with caching: " . number_format($nonCachedTime / $predictionTime, 1) . "x faster\n";

echo "\n\n=== Model Persistence Benefits ===\n\n";
echo "✅ Fast startup: Load pre-computed similarities instead of recomputing\n";
echo "✅ Consistent predictions: Same model version across deployments\n";
echo "✅ Efficient updates: Retrain periodically (daily/weekly) as needed\n";
echo "✅ Reduced latency: Predictions in milliseconds, not seconds\n";
echo "✅ Version control: Track model versions and performance over time\n";

echo "\n✅ Model persistence complete!\n";

