<?php

declare(strict_types=1);

/**
 * Load and inspect the movie ratings dataset.
 *
 * This script demonstrates:
 * - Reading CSV data with ratings
 * - Building a user-item ratings matrix
 * - Analyzing data sparsity and distribution
 * - Understanding the dataset structure
 */

echo "=== Movie Ratings Dataset Loader ===\n\n";

// Load movie ratings
$ratings = [];
$file = fopen(__DIR__ . '/data/movie_ratings.csv', 'r');
fgetcsv($file, 0, ',', '"', '\\'); // Skip header

while ($row = fgetcsv($file, 0, ',', '"', '\\')) {
    $userId = (int) $row[0];
    $movieId = (int) $row[1];
    $rating = (float) $row[2];

    if (!isset($ratings[$userId])) {
        $ratings[$userId] = [];
    }

    $ratings[$userId][$movieId] = $rating;
}
fclose($file);

// Load movie metadata
$movies = [];
$file = fopen(__DIR__ . '/data/movies.csv', 'r');
fgetcsv($file, 0, ',', '"', '\\'); // Skip header

while ($row = fgetcsv($file, 0, ',', '"', '\\')) {
    // Skip empty rows or rows with insufficient columns
    if (empty($row) || count($row) < 4) {
        continue;
    }

    $movies[(int) $row[0]] = [
        'id' => (int) $row[0],
        'title' => $row[1] ?? 'Unknown',
        'genre' => $row[2] ?? 'Unknown',
        'year' => isset($row[3]) ? (int) $row[3] : 0,
    ];
}
fclose($file);

// Calculate statistics
$numUsers = count($ratings);
$numMovies = count($movies);
$numRatings = array_sum(array_map('count', $ratings));
$possibleRatings = $numUsers * $numMovies;
$sparsity = ($numRatings / $possibleRatings) * 100;

echo "Dataset Statistics:\n";
echo "  Users: {$numUsers}\n";
echo "  Movies: {$numMovies}\n";
echo "  Ratings: {$numRatings}\n";
echo "  Sparsity: " . round($sparsity, 1) . "%\n\n";

// Rating distribution
$ratingCounts = [];
foreach ($ratings as $userRatings) {
    foreach ($userRatings as $rating) {
        // Use string key to avoid float-to-int conversion warning
        $ratingKey = (string) $rating;
        $ratingCounts[$ratingKey] = ($ratingCounts[$ratingKey] ?? 0) + 1;
    }
}

ksort($ratingCounts);

echo "Rating Distribution:\n";
foreach ($ratingCounts as $ratingKey => $count) {
    $rating = (float) $ratingKey;
    $percentage = ($count / $numRatings) * 100;
    $bar = str_repeat('█', (int) ($percentage / 2));
    echo sprintf("  %.1f stars: %4d (%5.1f%%) %s\n", $rating, $count, $percentage, $bar);
}
echo "\n";

// Show sample user ratings
$sampleUserId = array_key_first($ratings);
echo "Sample User Ratings (User #{$sampleUserId}):\n";

$userRatings = $ratings[$sampleUserId];
arsort($userRatings);
$count = 0;

foreach ($userRatings as $movieId => $rating) {
    if ($count++ >= 5) break;

    // Skip if movie metadata not found
    if (!isset($movies[$movieId])) {
        continue;
    }

    $movie = $movies[$movieId];
    echo sprintf(
        "  ⭐ %.1f - %s (%s, %d)\n",
        $rating,
        $movie['title'],
        $movie['genre'],
        $movie['year']
    );
}
echo "\n";

// Genre distribution
$genreRatings = [];
foreach ($ratings as $userRatings) {
    foreach ($userRatings as $movieId => $rating) {
        // Skip if movie metadata not found
        if (!isset($movies[$movieId])) {
            continue;
        }

        $genre = $movies[$movieId]['genre'];
        if (!isset($genreRatings[$genre])) {
            $genreRatings[$genre] = ['sum' => 0, 'count' => 0];
        }
        $genreRatings[$genre]['sum'] += $rating;
        $genreRatings[$genre]['count']++;
    }
}

echo "Average Rating by Genre:\n";
foreach ($genreRatings as $genre => $data) {
    $average = $data['sum'] / $data['count'];
    echo sprintf(
        "  %s: %.2f (%d ratings)\n",
        ucfirst($genre),
        $average,
        $data['count']
    );
}

echo "\n✅ Dataset loaded successfully!\n";
