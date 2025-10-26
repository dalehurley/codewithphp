<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 3: Creating Sample JSON Data
 * 
 * Demonstrates: Generating synthetic data, JSON encoding
 */

// Generate sample user activity data
$activities = [];
$actions = ['login', 'view_product', 'add_to_cart', 'purchase', 'review'];

for ($i = 1; $i <= 50; $i++) {
    $activities[] = [
        'user_id' => rand(1, 20),
        'action' => $actions[array_rand($actions)],
        'product_id' => rand(1, 20),
        'timestamp' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
        'duration_seconds' => rand(10, 600),
        'device' => ['mobile', 'desktop', 'tablet'][rand(0, 2)]
    ];
}

// Save to JSON file
file_put_contents(
    __DIR__ . '/data/user_activities.json',
    json_encode($activities, JSON_PRETTY_PRINT)
);

echo "Generated " . count($activities) . " activity records\n";
echo "Saved to data/user_activities.json\n";
