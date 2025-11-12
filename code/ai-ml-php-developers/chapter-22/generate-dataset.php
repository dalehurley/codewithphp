<?php

declare(strict_types=1);

/**
 * Generate realistic movie ratings dataset with patterns.
 * 
 * This creates a sparse ratings matrix with genre preferences:
 * - 100 users with different genre preferences
 * - 50 movies across 5 genres
 * - ~2000 ratings (~40% density)
 * - Patterns: users who like sci-fi tend to rate other sci-fi movies higher
 */

// Load movies
$movies = [];
$moviesByGenre = [];

$file = fopen(__DIR__ . '/data/movies.csv', 'r');
fgetcsv($file); // Skip header

while ($row = fgetcsv($file)) {
    $movieId = (int) $row[0];
    $movies[$movieId] = [
        'id' => $movieId,
        'title' => $row[1],
        'genre' => $row[2],
        'year' => (int) $row[3],
    ];
    $moviesByGenre[$row[2]][] = $movieId;
}
fclose($file);

// Define user profiles with genre preferences
$genres = ['sci-fi', 'comedy', 'drama', 'action', 'horror'];
$userProfiles = [];

for ($userId = 1; $userId <= 100; $userId++) {
    // Each user has primary and secondary genre preferences
    $primaryGenre = $genres[array_rand($genres)];
    $secondaryGenre = $genres[array_rand($genres)];

    // Assign preference weights (how much they like each genre)
    $preferences = [
        $primaryGenre => mt_rand(80, 100) / 100,
        $secondaryGenre => mt_rand(60, 80) / 100,
    ];

    // Add some randomness for other genres
    foreach ($genres as $genre) {
        if (!isset($preferences[$genre])) {
            $preferences[$genre] = mt_rand(20, 50) / 100;
        }
    }

    $userProfiles[$userId] = $preferences;
}

// Generate ratings
$ratings = [];
$testRatings = [];

foreach ($userProfiles as $userId => $preferences) {
    // Each user rates 15-25 movies (random)
    $numRatings = mt_rand(15, 25);
    $ratedMovies = array_rand($movies, $numRatings);

    if (!is_array($ratedMovies)) {
        $ratedMovies = [$ratedMovies];
    }

    foreach ($ratedMovies as $movieId) {
        $movie = $movies[$movieId];
        $genre = $movie['genre'];

        // Base rating influenced by genre preference
        $baseRating = $preferences[$genre] * 5;

        // Add randomness (-1 to +1)
        $rating = $baseRating + (mt_rand(-100, 100) / 100);

        // Clamp to 1-5 range
        $rating = max(1, min(5, $rating));

        // Round to nearest 0.5
        $rating = round($rating * 2) / 2;

        // 80% training, 20% test split
        if (mt_rand(1, 100) <= 80) {
            $ratings[] = [
                'user_id' => $userId,
                'movie_id' => $movieId,
                'rating' => $rating,
            ];
        } else {
            $testRatings[] = [
                'user_id' => $userId,
                'movie_id' => $movieId,
                'rating' => $rating,
            ];
        }
    }
}

// Shuffle for randomness
shuffle($ratings);
shuffle($testRatings);

// Write training ratings
$file = fopen(__DIR__ . '/data/movie_ratings.csv', 'w');
fputcsv($file, ['user_id', 'movie_id', 'rating']);

foreach ($ratings as $rating) {
    fputcsv($file, $rating);
}
fclose($file);

// Write test ratings
$file = fopen(__DIR__ . '/data/test_ratings.csv', 'w');
fputcsv($file, ['user_id', 'movie_id', 'rating']);

foreach ($testRatings as $rating) {
    fputcsv($file, $rating);
}
fclose($file);

echo "Dataset generated successfully!\n";
echo "Training ratings: " . count($ratings) . "\n";
echo "Test ratings: " . count($testRatings) . "\n";
echo "Total ratings: " . (count($ratings) + count($testRatings)) . "\n";
echo "Movies: " . count($movies) . "\n";
echo "Users: " . count($userProfiles) . "\n";
echo "\nSparsity: " . round((count($ratings) + count($testRatings)) / (100 * 50) * 100, 1) . "%\n";

