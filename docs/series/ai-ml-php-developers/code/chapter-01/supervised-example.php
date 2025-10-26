<?php

declare(strict_types=1);

/**
 * Supervised Learning: Spam Email Classifier
 * 
 * This example demonstrates supervised learning where we train a model
 * with labeled examples (spam vs ham) and use it to classify new emails.
 */

require_once __DIR__ . '/../chapter-02/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "\n📧 Supervised Learning: Email Spam Classification\n";
echo "==================================================\n\n";

// Training Dataset: Labeled Examples
$trainingEmails = [
    // Spam examples
    "WIN FREE MONEY NOW!!! Click here!!!",
    "URGENT: You've won a prize! Claim now!",
    "FREE viagra and cialis! Best prices!",
    "Make money fast! Work from home!!!",
    "URGENT LOAN APPROVAL! Click immediately!",
    "Get rich quick! FREE trial!!!",

    // Ham (legitimate) examples
    "Meeting scheduled for tomorrow at 3pm",
    "Thanks for sending the quarterly report",
    "Can we discuss the project timeline?",
    "Your order has been shipped successfully",
    "Welcome to our team! Here's your onboarding schedule",
    "Monthly newsletter: Industry updates",
];

$trainingLabels = [
    'spam',
    'spam',
    'spam',
    'spam',
    'spam',
    'spam',
    'ham',
    'ham',
    'ham',
    'ham',
    'ham',
    'ham',
];

echo "Training Dataset:\n";
echo "  Total emails: " . count($trainingEmails) . "\n";
echo "  Spam examples: " . count(array_filter($trainingLabels, fn($l) => $l === 'spam')) . "\n";
echo "  Ham examples: " . count(array_filter($trainingLabels, fn($l) => $l === 'ham')) . "\n\n";

// Feature Extraction Function
function extractEmailFeatures(string $email): array
{
    $lowerEmail = strtolower($email);

    return [
        str_word_count($email),                              // Word count
        substr_count($email, '!'),                          // Exclamation marks
        str_contains($lowerEmail, 'free') ? 1 : 0,         // Contains "free"
        str_contains($lowerEmail, 'urgent') ? 1 : 0,       // Contains "urgent"
        str_contains($lowerEmail, 'money') ? 1 : 0,        // Contains "money"
        str_contains($lowerEmail, 'click') ? 1 : 0,        // Contains "click"
        preg_match('/[A-Z]/', $email) / max(strlen($email), 1), // Capital letter ratio
    ];
}

// Extract features from training emails
echo "Extracting features from training emails...\n";
$trainingFeatures = array_map('extractEmailFeatures', $trainingEmails);
echo "✓ Features extracted: 7 features per email\n";
echo "  Features: [word_count, exclamations, has_free, has_urgent, has_money, has_click, capital_ratio]\n\n";

// Show feature extraction for one example
echo "Example feature extraction:\n";
echo "  Email: \"{$trainingEmails[0]}\"\n";
echo "  Features: [" . implode(', ', array_map(fn($f) => is_float($f) ? number_format($f, 2) : $f, $trainingFeatures[0])) . "]\n";
echo "  Label: {$trainingLabels[0]}\n\n";

// Train the Classifier
echo "Training k-Nearest Neighbors classifier (k=3)...\n";
$classifier = new KNearestNeighbors(k: 3);
$classifier->train($trainingFeatures, $trainingLabels);
echo "✓ Model trained successfully!\n\n";

// Test on New Emails
echo "==================================================\n";
echo "Testing on New Emails\n";
echo "==================================================\n\n";

$testEmails = [
    "FREE money! Click now for URGENT offer!!!",
    "Team meeting moved to Thursday at 2pm",
    "URGENT: Claim your prize money now!",
    "Thanks for the detailed proposal document",
    "Work from home! Make money fast! FREE!!!",
    "Your package delivery is scheduled for Monday",
];

foreach ($testEmails as $email) {
    $features = extractEmailFeatures($email);
    $prediction = $classifier->predict($features);

    $icon = $prediction === 'spam' ? '🚫 SPAM' : '✅ HAM';
    echo "{$icon}: \"{$email}\"\n";
    echo "  Features: [" . implode(', ', array_map(fn($f) => is_float($f) ? number_format($f, 2) : $f, $features)) . "]\n\n";
}

echo "==================================================\n";
echo "Key Concepts Demonstrated:\n";
echo "==================================================\n\n";

echo "1. Supervised Learning: We provided labeled examples (spam/ham)\n";
echo "2. Feature Engineering: Converted text to numeric features\n";
echo "3. Training Phase: Model learned patterns from examples\n";
echo "4. Inference Phase: Model predicted labels for new data\n";
echo "5. k-NN Algorithm: Classified based on 3 nearest neighbors\n\n";

echo "Why This Works:\n";
echo "- Spam emails have patterns: many exclamations, words like\n";
echo "  'free', 'urgent', 'money', lots of capitals\n";
echo "- Ham emails are calmer: normal punctuation, professional tone\n";
echo "- The model learned these patterns from our labeled examples\n";
echo "- For new emails, it finds the 3 most similar training examples\n";
echo "  and predicts the most common label among them\n\n";
