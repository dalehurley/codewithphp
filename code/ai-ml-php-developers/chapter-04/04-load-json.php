<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 4: Loading JSON Data
 * 
 * Demonstrates: JSON parsing, API data loading, error handling
 */

/**
 * Load JSON data from file or URL
 */
function loadJson(string $source): array
{
    // Check if source is URL or file path
    if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
        $content = @file_get_contents($source);
        if ($content === false) {
            throw new RuntimeException("Failed to fetch data from URL: $source");
        }
    } else {
        if (!file_exists($source)) {
            throw new RuntimeException("File not found: $source");
        }
        $content = file_get_contents($source);
    }

    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("JSON decode error: " . json_last_error_msg());
    }

    return $data;
}

// Load local JSON file
$activities = loadJson(__DIR__ . '/data/user_activities.json');

echo "Loaded " . count($activities) . " user activities\n\n";

// Analyze by action type
$actionCounts = [];
foreach ($activities as $activity) {
    $action = $activity['action'];
    $actionCounts[$action] = ($actionCounts[$action] ?? 0) + 1;
}

echo "Activity Breakdown:\n";
arsort($actionCounts);
foreach ($actionCounts as $action => $count) {
    $percentage = round(($count / count($activities)) * 100, 1);
    echo "- $action: $count ({$percentage}%)\n";
}

// Device usage
$deviceCounts = [];
foreach ($activities as $activity) {
    $device = $activity['device'];
    $deviceCounts[$device] = ($deviceCounts[$device] ?? 0) + 1;
}

echo "\nDevice Usage:\n";
arsort($deviceCounts);
foreach ($deviceCounts as $device => $count) {
    echo "- $device: $count\n";
}
