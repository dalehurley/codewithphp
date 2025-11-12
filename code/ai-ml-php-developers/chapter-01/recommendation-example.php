<?php

declare(strict_types=1);

/**
 * Recommendation System Example
 * 
 * This example shows how PHP developers can build a simple movie
 * recommendation system using machine learning.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "\n🎬 Movie Recommendation System\n";
echo "==============================\n\n";

echo "Scenario: Recommend movies to users based on viewing patterns\n\n";

// User viewing patterns: [action, comedy, drama, romance]
// 1 = watched, 0 = not watched
$trainingData = [
    [1, 0, 1, 0],  // User 1: Watches action and drama
    [1, 0, 1, 0],  // User 2: Similar pattern - action lover
    [0, 1, 0, 1],  // User 3: Watches comedy and romance
    [0, 1, 0, 1],  // User 4: Similar pattern - comedy fan
    [1, 1, 0, 0],  // User 5: Action and comedy
    [0, 0, 1, 1],  // User 6: Drama and romance
];

// User preferences (labels)
$labels = [
    'action_lover',
    'action_lover',
    'romance_fan',
    'romance_fan',
    'varied_taste',
    'drama_fan',
];

echo "Training Data:\n";
echo "  Users: " . count($trainingData) . "\n";
echo "  Patterns tracked: [action, comedy, drama, romance]\n\n";

// Show training examples
echo "Sample Training Examples:\n";
$patterns = ['Action', 'Comedy', 'Drama', 'Romance'];
for ($i = 0; $i < 3; $i++) {
    echo "  User " . ($i + 1) . ": ";
    $watched = [];
    foreach ($trainingData[$i] as $j => $value) {
        if ($value === 1) {
            $watched[] = $patterns[$j];
        }
    }
    echo implode(', ', $watched);
    echo " → {$labels[$i]}\n";
}
echo "\n";

// Train the classifier
echo "Training recommendation model...\n";
$classifier = new KNearestNeighbors(k: 2);
$classifier->train($trainingData, $labels);
echo "✓ Model trained!\n\n";

// Predict for new users
echo "==============================\n";
echo "Predicting for New Users\n";
echo "==============================\n\n";

$newUsers = [
    [1, 0, 1, 0],  // Watches action and drama
    [0, 1, 1, 0],  // Watches comedy and drama
    [1, 1, 1, 1],  // Watches everything
    [0, 0, 0, 1],  // Only watches romance
];

$userNames = ['Alice', 'Bob', 'Charlie', 'Diana'];

foreach ($newUsers as $i => $userData) {
    $prediction = $classifier->predict($userData);

    echo "User: {$userNames[$i]}\n";
    echo "  Viewing pattern: ";
    $watched = [];
    foreach ($userData as $j => $value) {
        if ($value === 1) {
            $watched[] = $patterns[$j];
        }
    }
    echo implode(', ', $watched) . "\n";
    echo "  Predicted type: {$prediction}\n";

    // Make recommendations based on prediction
    echo "  📺 Recommendations: ";
    switch ($prediction) {
        case 'action_lover':
            echo "Mad Max, John Wick, Die Hard\n";
            break;
        case 'romance_fan':
            echo "The Notebook, Pride & Prejudice, La La Land\n";
            break;
        case 'drama_fan':
            echo "The Shawshank Redemption, Forrest Gump\n";
            break;
        case 'varied_taste':
            echo "Inception, Guardians of the Galaxy, Interstellar\n";
            break;
    }
    echo "\n";
}

echo "==============================\n";
echo "How This Works\n";
echo "==============================\n\n";

echo "1. Data Collection (PHP's Role)\n";
echo "   - Your PHP app tracks what users watch\n";
echo "   - Store viewing patterns in database\n";
echo "   - Extract features: genre preferences\n\n";

echo "2. Data Preparation (PHP's Role)\n";
echo "   - Query database for user behavior\n";
echo "   - Transform to numeric features [1, 0, 1, 0]\n";
echo "   - Clean and validate data\n\n";

echo "3. Model Training (PHP or Python)\n";
echo "   - Use PHP-ML for simple models (like this)\n";
echo "   - Or call Python for complex collaborative filtering\n";
echo "   - Train once, use many times\n\n";

echo "4. Serving Predictions (PHP's Role)\n";
echo "   - Load trained model\n";
echo "   - Get user's viewing pattern\n";
echo "   - Generate recommendations\n";
echo "   - Display in your web app\n\n";

echo "5. Monitoring (PHP's Role)\n";
echo "   - Track recommendation click-through rate\n";
echo "   - Collect feedback\n";
echo "   - Retrain periodically with new data\n\n";

echo "Real-World Considerations:\n";
echo "- Cold start problem: New users with no history\n";
echo "- Data sparsity: Users haven't rated most movies\n";
echo "- Scalability: Millions of users and items\n";
echo "- Real-time updates: Recommendations change as users watch\n";
echo "- Privacy: Anonymize and protect user data\n\n";

echo "For production systems:\n";
echo "- Use specialized recommendation libraries\n";
echo "- Consider collaborative filtering algorithms\n";
echo "- Implement A/B testing for recommendation quality\n";
echo "- Cache recommendations for performance\n\n";
