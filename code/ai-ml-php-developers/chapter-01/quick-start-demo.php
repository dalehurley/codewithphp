<?php

declare(strict_types=1);

/**
 * Quick Start Demo: Your First Machine Learning Model
 * 
 * This simple spam classifier demonstrates supervised learning in action.
 * You'll see how a model learns from labeled examples and predicts new data.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "\n🚀 Quick Start: Your First ML Model\n";
echo "=====================================\n\n";

// Step 1: Prepare training data with labels
echo "📚 Training data: 6 emails with known labels\n\n";

$trainingEmails = [
    "WIN FREE MONEY NOW!!!",
    "Meeting scheduled for 2pm",
    "URGENT: Claim your prize",
    "Thanks for the project update",
    "FREE discount! Act fast!",
    "Can we discuss the proposal?",
];

// Extract features: [word_count, exclamation_count, has_free, has_urgent]
$trainingFeatures = [
    [4, 3, 1, 0],  // spam: "WIN FREE MONEY NOW!!!"
    [4, 0, 0, 0],  // ham: "Meeting scheduled for 2pm"
    [4, 1, 0, 1],  // spam: "URGENT: Claim your prize"
    [5, 0, 0, 0],  // ham: "Thanks for the project update"
    [4, 1, 1, 0],  // spam: "FREE discount! Act fast!"
    [6, 1, 0, 0],  // ham: "Can we discuss the proposal?"
];

$trainingLabels = ['spam', 'ham', 'spam', 'ham', 'spam', 'ham'];

foreach ($trainingEmails as $i => $email) {
    $label = $trainingLabels[$i];
    $icon = $label === 'spam' ? '🚫' : '✅';
    echo "  {$icon} \"{$email}\" → {$label}\n";
}

// Step 2: Train the classifier
echo "\n🎓 Training the classifier...\n";
$classifier = new KNearestNeighbors(k: 3);
$classifier->train($trainingFeatures, $trainingLabels);
echo "✓ Training complete!\n";

// Step 3: Make predictions on new emails
echo "\n🔮 Predicting new emails:\n\n";

$newEmails = [
    "FREE gift! Click NOW!!!",
    "See you at the meeting",
    "URGENT money offer!!!",
    "Let's schedule a call",
];

$newFeatures = [
    [4, 3, 1, 0],  // [word_count, exclamations, has_free, has_urgent]
    [5, 0, 0, 0],
    [4, 3, 0, 1],
    [4, 0, 0, 0],
];

foreach ($newEmails as $index => $email) {
    $prediction = $classifier->predict($newFeatures[$index]);
    $icon = $prediction === 'spam' ? '🚫' : '✅';
    $confidence = $prediction === 'spam' ? 'SPAM' : 'HAM';
    echo "  {$icon} \"{$email}\"\n";
    echo "     → Prediction: {$confidence}\n\n";
}

echo "=====================================\n";
echo "🎉 That's machine learning in action!\n\n";
echo "What just happened?\n";
echo "- The model learned patterns from 6 labeled examples\n";
echo "- It extracted numeric features from text\n";
echo "- It predicted labels for new, unseen emails\n";
echo "- All in pure PHP, no external services needed!\n\n";
