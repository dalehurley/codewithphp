<?php

declare(strict_types=1);

/**
 * Efficient matrix operations for recommendation systems.
 *
 * This script demonstrates:
 * - User-item matrix representation
 * - Sparse matrix handling
 * - Matrix operations for collaborative filtering
 * - Memory-efficient data structures
 */

echo "=== Matrix Operations for Recommender Systems ===\n\n";

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

echo "1. USER-ITEM MATRIX\n";
echo str_repeat('-', 50) . "\n\n";

// Calculate matrix dimensions
$userIds = array_keys($ratings);
$movieIds = [];

foreach ($ratings as $userRatings) {
    $movieIds = array_merge($movieIds, array_keys($userRatings));
}
$movieIds = array_unique($movieIds);

$numUsers = count($userIds);
$numMovies = count($movieIds);
$numRatings = array_sum(array_map('count', $ratings));

echo "Matrix Dimensions:\n";
echo "  Rows (users):    {$numUsers}\n";
echo "  Columns (movies): {$numMovies}\n";
echo "  Total cells:     " . ($numUsers * $numMovies) . "\n";
echo "  Filled cells:    {$numRatings}\n";
echo "  Sparsity:        " . number_format(($numRatings / ($numUsers * $numMovies)) * 100, 2) . "%\n\n";

// Visualize a small section of the matrix
echo "Sample Matrix (First 10 users × 10 movies):\n\n";
echo "    ";
foreach (array_slice($movieIds, 0, 10) as $movieId) {
    echo sprintf("%4d ", $movieId);
}
echo "\n";

foreach (array_slice($userIds, 0, 10) as $userId) {
    echo sprintf("%3d ", $userId);

    foreach (array_slice($movieIds, 0, 10) as $movieId) {
        if (isset($ratings[$userId][$movieId])) {
            echo sprintf("%4.1f ", $ratings[$userId][$movieId]);
        } else {
            echo "   - ";
        }
    }
    echo "\n";
}

echo "\n\n2. SPARSE MATRIX REPRESENTATION\n";
echo str_repeat('-', 50) . "\n\n";

// Compare storage formats
$denseSize = $numUsers * $numMovies * 8; // 8 bytes per float
$sparseSize = $numRatings * (4 + 4 + 8); // user_id(4) + movie_id(4) + rating(8)

echo "Storage Requirements:\n";
echo "  Dense matrix:  " . number_format($denseSize / 1024 / 1024, 2) . " MB\n";
echo "  Sparse matrix: " . number_format($sparseSize / 1024, 2) . " KB\n";
echo "  Space savings: " . number_format((1 - $sparseSize / $denseSize) * 100, 1) . "%\n\n";

echo "3. MATRIX STATISTICS\n";
echo str_repeat('-', 50) . "\n\n";

// User statistics
$userRatingCounts = array_map('count', $ratings);
$avgRatingsPerUser = array_sum($userRatingCounts) / count($userRatingCounts);
$minRatingsPerUser = min($userRatingCounts);
$maxRatingsPerUser = max($userRatingCounts);

echo "User Statistics:\n";
echo "  Average ratings per user: " . number_format($avgRatingsPerUser, 1) . "\n";
echo "  Min ratings per user:     {$minRatingsPerUser}\n";
echo "  Max ratings per user:     {$maxRatingsPerUser}\n\n";

// Movie statistics
$movieRatingCounts = [];
foreach ($ratings as $userRatings) {
    foreach ($userRatings as $movieId => $rating) {
        $movieRatingCounts[$movieId] = ($movieRatingCounts[$movieId] ?? 0) + 1;
    }
}

$avgRatingsPerMovie = array_sum($movieRatingCounts) / count($movieRatingCounts);
$minRatingsPerMovie = min($movieRatingCounts);
$maxRatingsPerMovie = max($movieRatingCounts);

echo "Movie Statistics:\n";
echo "  Average ratings per movie: " . number_format($avgRatingsPerMovie, 1) . "\n";
echo "  Min ratings per movie:     {$minRatingsPerMovie}\n";
echo "  Max ratings per movie:     {$maxRatingsPerMovie}\n\n";

// Rating distribution
$globalRatings = [];
foreach ($ratings as $userRatings) {
    $globalRatings = array_merge($globalRatings, array_values($userRatings));
}

$avgRating = array_sum($globalRatings) / count($globalRatings);
$variance = array_sum(array_map(
    fn($r) => ($r - $avgRating) ** 2,
    $globalRatings
)) / count($globalRatings);
$stdDev = sqrt($variance);

echo "Global Rating Statistics:\n";
echo "  Mean:     " . number_format($avgRating, 3) . "\n";
echo "  Std Dev:  " . number_format($stdDev, 3) . "\n";
echo "  Min:      " . number_format(min($globalRatings), 1) . "\n";
echo "  Max:      " . number_format(max($globalRatings), 1) . "\n\n";

echo "4. MATRIX TRANSFORMATION\n";
echo str_repeat('-', 50) . "\n\n";

// Convert to item-user matrix (transpose)
$itemUserMatrix = [];
foreach ($ratings as $userId => $userRatings) {
    foreach ($userRatings as $movieId => $rating) {
        $itemUserMatrix[$movieId][$userId] = $rating;
    }
}

echo "Transposed Matrix (Item-User):\n";
echo "  Rows (movies): " . count($itemUserMatrix) . "\n";
echo "  Columns (users): {$numUsers}\n";
echo "  Filled cells: " . array_sum(array_map('count', $itemUserMatrix)) . "\n\n";

// Mean-center the ratings (normalize)
$userMeans = [];
foreach ($ratings as $userId => $userRatings) {
    $userMeans[$userId] = array_sum($userRatings) / count($userRatings);
}

$centeredRatings = [];
foreach ($ratings as $userId => $userRatings) {
    foreach ($userRatings as $movieId => $rating) {
        $centeredRatings[$userId][$movieId] = $rating - $userMeans[$userId];
    }
}

echo "Mean-Centered Ratings:\n";
echo "  Original range: [" . number_format(min($globalRatings), 1) . ", " .
    number_format(max($globalRatings), 1) . "]\n";

$centeredValues = [];
foreach ($centeredRatings as $userRatings) {
    $centeredValues = array_merge($centeredValues, array_values($userRatings));
}

echo "  Centered range: [" . number_format(min($centeredValues), 1) . ", " .
    number_format(max($centeredValues), 1) . "]\n";
echo "  Centered mean: " . number_format(array_sum($centeredValues) / count($centeredValues), 3) . "\n\n";

echo "5. SIMILARITY MATRIX\n";
echo str_repeat('-', 50) . "\n\n";

// Calculate user-user similarity for a subset
$sampleUserIds = array_slice($userIds, 0, 5);
$similarityMatrix = [];

echo "Computing user-user similarity matrix...\n\n";

foreach ($sampleUserIds as $userA) {
    foreach ($sampleUserIds as $userB) {
        if ($userA === $userB) {
            $similarityMatrix[$userA][$userB] = 1.0;
            continue;
        }

        if (isset($similarityMatrix[$userB][$userA])) {
            $similarityMatrix[$userA][$userB] = $similarityMatrix[$userB][$userA];
            continue;
        }

        // Compute cosine similarity
        $commonMovies = array_intersect_key($ratings[$userA], $ratings[$userB]);

        if (count($commonMovies) === 0) {
            $similarityMatrix[$userA][$userB] = 0.0;
            continue;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        foreach ($commonMovies as $movieId => $ratingA) {
            $ratingB = $ratings[$userB][$movieId];
            $dotProduct += $ratingA * $ratingB;
            $magnitudeA += $ratingA * $ratingA;
            $magnitudeB += $ratingB * $ratingB;
        }

        $similarityMatrix[$userA][$userB] = ($magnitudeA > 0 && $magnitudeB > 0)
            ? $dotProduct / (sqrt($magnitudeA) * sqrt($magnitudeB))
            : 0.0;
    }
}

echo "User-User Similarity Matrix (First 5 users):\n\n";
echo "     ";
foreach ($sampleUserIds as $userId) {
    echo sprintf("%6d ", $userId);
}
echo "\n";

foreach ($sampleUserIds as $userA) {
    echo sprintf("%4d ", $userA);
    foreach ($sampleUserIds as $userB) {
        echo sprintf("%6.3f ", $similarityMatrix[$userA][$userB]);
    }
    echo "\n";
}

echo "\n\n=== Matrix Operation Best Practices ===\n\n";
echo "✅ Use sparse matrices for efficiency (< 10% density)\n";
echo "✅ Mean-center ratings to remove user bias\n";
echo "✅ Cache computed similarities to avoid re-computation\n";
echo "✅ Use batch operations for multiple predictions\n";
echo "✅ Consider using NumPy/BLAS via Python for large matrices\n";

echo "\n✅ Matrix operations complete!\n";

