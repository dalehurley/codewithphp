<?php

declare(strict_types=1);

/**
 * Example 1: Supervised Learning - Email Spam Classification
 * 
 * This demonstrates supervised learning where we train a classifier
 * on labeled data (spam vs. ham emails) and then use it to predict
 * whether new emails are spam.
 */

require __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "=== Supervised Learning: Spam Classification ===\n\n";

// Training data: email features (word counts) and labels
// Features: [contains_free, contains_money, contains_urgent, num_exclamations]
$trainingFeatures = [
    [1, 1, 1, 3],  // spam: "FREE MONEY!!! Click now!"
    [1, 1, 0, 2],  // spam: "Get FREE cash!!"
    [0, 0, 0, 0],  // ham: "Meeting at 3pm tomorrow"
    [0, 0, 0, 1],  // ham: "Thanks for your email!"
    [1, 1, 1, 5],  // spam: "URGENT! FREE money NOW!!!!!"
    [0, 1, 0, 0],  // ham: "Budget report attached"
    [1, 0, 1, 2],  // spam: "URGENT FREE offer!!"
    [0, 0, 0, 0],  // ham: "Looking forward to our call"
];

// Labels: what we know these emails actually are
$trainingLabels = ['spam', 'spam', 'ham', 'ham', 'spam', 'ham', 'spam', 'ham'];

echo "Training data: " . count($trainingFeatures) . " labeled emails\n";
echo "- Spam examples: " . count(array_filter($trainingLabels, fn($l) => $l === 'spam')) . "\n";
echo "- Ham examples: " . count(array_filter($trainingLabels, fn($l) => $l === 'ham')) . "\n\n";

// Step 1: Create and train the classifier
echo "Training classifier...\n";
$classifier = new KNearestNeighbors(k: 3);
$classifier->train($trainingFeatures, $trainingLabels);
echo "✓ Classifier trained\n\n";

// Step 2: Predict on new, unseen emails
$newEmails = [
    [1, 1, 1, 4],  // "FREE URGENT money!!!! Act now"
    [0, 0, 0, 0],  // "See you at the meeting"
    [1, 0, 0, 1],  // "FREE coffee at the office!"
    [0, 1, 0, 0],  // "Quarterly financial report"
];

$newEmailDescriptions = [
    'Email 1: "FREE URGENT money!!!! Act now"',
    'Email 2: "See you at the meeting"',
    'Email 3: "FREE coffee at the office!"',
    'Email 4: "Quarterly financial report"',
];

echo "Making predictions on new emails:\n";
echo str_repeat('-', 50) . "\n";

foreach ($newEmails as $index => $emailFeatures) {
    $prediction = $classifier->predict($emailFeatures);
    $emoji = $prediction === 'spam' ? '🚫' : '✓';

    echo "{$emoji} {$newEmailDescriptions[$index]}\n";
    echo "   Prediction: " . strtoupper($prediction) . "\n\n";
}

// Step 3: Explain what happened
echo str_repeat('=', 50) . "\n";
echo "How It Works:\n";
echo str_repeat('=', 50) . "\n";
echo "1. We extracted features from emails (word presence, punctuation)\n";
echo "2. We provided labeled training data (spam vs. ham)\n";
echo "3. The classifier learned patterns from this data\n";
echo "4. It can now predict labels for new, unseen emails\n\n";

echo "This is supervised learning because:\n";
echo "- We provided the 'correct answers' (labels) during training\n";
echo "- The model learned the relationship between features and labels\n";
echo "- It generalizes this knowledge to make predictions\n\n";

// Calculate training accuracy
$trainingPredictions = array_map(
    fn($features) => $classifier->predict($features),
    $trainingFeatures
);

$correct = array_sum(array_map(
    fn($pred, $actual) => $pred === $actual ? 1 : 0,
    $trainingPredictions,
    $trainingLabels
));

$accuracy = ($correct / count($trainingLabels)) * 100;
echo "Training accuracy: " . round($accuracy, 2) . "%\n";
