<?php

declare(strict_types=1);

/**
 * ML Lifecycle: The 6-Step Workflow
 * 
 * This example walks through all 6 steps of the machine learning lifecycle
 * using a simple sentiment analysis classifier.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "\n🔄 The Machine Learning Lifecycle: 6 Steps\n";
echo "==========================================\n\n";

// STEP 1: Define the Problem
echo "┌─────────────────────────────────────────┐\n";
echo "│ STEP 1: Define the Problem              │\n";
echo "└─────────────────────────────────────────┘\n\n";

echo "Problem: Classify customer reviews as positive or negative\n";
echo "Input: Review text\n";
echo "Output: Sentiment (positive/negative)\n";
echo "Why: Automatically analyze thousands of reviews\n\n";

sleep(1);

// STEP 2: Collect & Prepare Data
echo "┌─────────────────────────────────────────┐\n";
echo "│ STEP 2: Collect & Prepare Data          │\n";
echo "└─────────────────────────────────────────┘\n\n";

$reviews = [
    // Positive reviews
    "Great product! Love it! Highly recommend!",
    "Excellent quality and fast shipping!",
    "Amazing experience! Will buy again!",
    "Best purchase ever! Very happy!",
    "Love this! Fantastic quality!",

    // Negative reviews
    "Terrible product. Very disappointed.",
    "Poor quality. Would not recommend.",
    "Awful experience. Refund requested.",
    "Waste of money. Very unhappy.",
    "Bad quality. Do not buy.",
];

$sentiments = [
    'positive',
    'positive',
    'positive',
    'positive',
    'positive',
    'negative',
    'negative',
    'negative',
    'negative',
    'negative',
];

echo "✓ Collected " . count($reviews) . " labeled reviews\n";
echo "  - Positive: " . count(array_filter($sentiments, fn($s) => $s === 'positive')) . "\n";
echo "  - Negative: " . count(array_filter($sentiments, fn($s) => $s === 'negative')) . "\n\n";

// Feature extraction
function extractSentimentFeatures(string $review): array
{
    $lower = strtolower($review);

    // Positive indicators
    $positiveWords = ['great', 'love', 'excellent', 'amazing', 'best', 'fantastic', 'happy'];
    $positiveCount = 0;
    foreach ($positiveWords as $word) {
        if (str_contains($lower, $word)) {
            $positiveCount++;
        }
    }

    // Negative indicators
    $negativeWords = ['terrible', 'poor', 'awful', 'waste', 'bad', 'disappointed', 'unhappy'];
    $negativeCount = 0;
    foreach ($negativeWords as $word) {
        if (str_contains($lower, $word)) {
            $negativeCount++;
        }
    }

    return [
        substr_count($review, '!'),  // Exclamation marks
        $positiveCount,              // Positive word count
        $negativeCount,              // Negative word count
        str_word_count($review),     // Total words
    ];
}

$features = array_map('extractSentimentFeatures', $reviews);
echo "✓ Extracted features: [exclamations, positive_words, negative_words, word_count]\n\n";

sleep(1);

// STEP 3: Choose & Train Model
echo "┌─────────────────────────────────────────┐\n";
echo "│ STEP 3: Choose & Train Model            │\n";
echo "└─────────────────────────────────────────┘\n\n";

echo "Algorithm: k-Nearest Neighbors (k=3)\n";
echo "Why: Simple, interpretable, works well for small datasets\n\n";

$model = new KNearestNeighbors(k: 3);
echo "Training model...\n";
$model->train($features, $sentiments);
echo "✓ Model trained successfully!\n\n";

sleep(1);

// STEP 4: Evaluate Performance
echo "┌─────────────────────────────────────────┐\n";
echo "│ STEP 4: Evaluate Performance             │\n";
echo "└─────────────────────────────────────────┘\n\n";

// Test on training data (normally you'd use separate test data)
$predictions = [];
$correct = 0;

foreach ($features as $i => $feature) {
    $prediction = $model->predict($feature);
    $predictions[] = $prediction;
    if ($prediction === $sentiments[$i]) {
        $correct++;
    }
}

$accuracy = ($correct / count($sentiments)) * 100;
echo "Training Accuracy: " . number_format($accuracy, 1) . "%\n";
echo "Correct: {$correct} / " . count($sentiments) . "\n\n";

if ($accuracy >= 90) {
    echo "✓ Good enough! Proceeding to deployment.\n\n";
} else {
    echo "⚠ Performance below target. Would iterate back to Step 2 or 3.\n\n";
}

sleep(1);

// STEP 5: Deploy to Production
echo "┌─────────────────────────────────────────┐\n";
echo "│ STEP 5: Deploy to Production            │\n";
echo "└─────────────────────────────────────────┘\n\n";

echo "✓ Model ready for production use\n";
echo "Integration: REST API endpoint for real-time classification\n\n";

sleep(1);

// STEP 6: Monitor & Maintain
echo "┌─────────────────────────────────────────┐\n";
echo "│ STEP 6: Monitor & Maintain               │\n";
echo "└─────────────────────────────────────────┘\n\n";

// Simulate production usage
$newReviews = [
    "This product is amazing! Love it!",
    "Terrible quality. Very disappointed.",
    "Excellent service and great product!",
];

echo "Production usage (new reviews):\n\n";

foreach ($newReviews as $review) {
    $reviewFeatures = extractSentimentFeatures($review);
    $sentiment = $model->predict($reviewFeatures);

    $icon = $sentiment === 'positive' ? '😊' : '😞';
    echo "{$icon} \"{$review}\"\n";
    echo "   Sentiment: " . strtoupper($sentiment) . "\n\n";
}

echo "Monitoring:\n";
echo "- Track prediction accuracy over time\n";
echo "- Watch for data drift (changing review patterns)\n";
echo "- Retrain periodically with new labeled data\n";
echo "- A/B test model improvements\n\n";

// Summary
echo "==========================================\n";
echo "Key Insights About the ML Lifecycle\n";
echo "==========================================\n\n";

echo "1. It's Iterative, Not Linear\n";
echo "   If evaluation fails, loop back to Steps 2-3\n\n";

echo "2. Data Quality Matters Most\n";
echo "   Better data > Fancier algorithm\n\n";

echo "3. Deployment Isn't the End\n";
echo "   Ongoing monitoring and retraining are essential\n\n";

echo "4. Each Step Has Challenges\n";
echo "   - Data: Collecting enough quality examples\n";
echo "   - Training: Choosing the right algorithm\n";
echo "   - Evaluation: Avoiding overfitting\n";
echo "   - Deployment: Integrating with existing systems\n";
echo "   - Monitoring: Detecting when performance degrades\n\n";

echo "5. Real Projects Take Time\n";
echo "   What looks simple in demos is complex in production\n\n";

echo "The cycle continues: monitor → detect issues → improve → retrain → deploy → monitor...\n\n";
