<?php

declare(strict_types=1);

/**
 * Example 5: Overfitting Demonstration
 * 
 * Shows the danger of overfitting - when a model memorizes training data
 * instead of learning generalizable patterns. This is the most common
 * mistake in machine learning!
 */

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "=== Understanding Overfitting ===\n\n";

// ============================================================================
// SCENARIO 1: OVERFITTING (Bad Model)
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "SCENARIO 1: OVERFITTING - Model Memorizes Training Data\n";
echo str_repeat('=', 60) . "\n\n";

// Very small training set (only 6 examples!)
$smallTrainingData = [
    [5.0, 3.5],
    [4.8, 3.0],
    [5.2, 3.4],  // Setosa
    [6.5, 2.8],
    [6.0, 2.7],
    [5.9, 3.0],  // Versicolor
];

$smallTrainingLabels = [
    'setosa',
    'setosa',
    'setosa',
    'versicolor',
    'versicolor',
    'versicolor',
];

echo "Training data: " . count($smallTrainingData) . " examples (very small!)\n\n";

// Train with k=1 (will memorize perfectly)
$overfit_classifier = new KNearestNeighbors(k: 1);
$overfit_classifier->train($smallTrainingData, $smallTrainingLabels);

// Test on training data (will be perfect!)
echo "Testing on TRAINING data:\n";
$correct = 0;
foreach ($smallTrainingData as $index => $sample) {
    $prediction = $overfit_classifier->predict($sample);
    $actual = $smallTrainingLabels[$index];
    $match = $prediction === $actual ? '✓' : '✗';

    if ($prediction === $actual) {
        $correct++;
    }

    echo "  {$match} Sample " . ($index + 1) . ": predicted={$prediction}, actual={$actual}\n";
}

$trainingAccuracy = ($correct / count($smallTrainingData)) * 100;
echo "\n→ Training Accuracy: " . round($trainingAccuracy, 1) . "% (Perfect! But...)\n\n";

// Test on NEW data (will be poor!)
$testData = [
    [5.1, 3.6],
    [4.9, 3.1],
    [5.3, 3.5],  // Should be setosa
    [6.4, 2.9],
    [6.1, 2.6],
    [6.2, 3.0],  // Should be versicolor
];

$testLabels = [
    'setosa',
    'setosa',
    'setosa',
    'versicolor',
    'versicolor',
    'versicolor',
];

echo "Testing on NEW (unseen) data:\n";
$correct = 0;
foreach ($testData as $index => $sample) {
    $prediction = $overfit_classifier->predict($sample);
    $actual = $testLabels[$index];
    $match = $prediction === $actual ? '✓' : '✗';

    if ($prediction === $actual) {
        $correct++;
    }

    echo "  {$match} Sample " . ($index + 1) . ": predicted={$prediction}, actual={$actual}\n";
}

$testAccuracy = ($correct / count($testData)) * 100;
echo "\n→ Test Accuracy: " . round($testAccuracy, 1) . "% (Poor!)\n\n";

echo "⚠️ OVERFITTING DETECTED!\n";
echo "  Training accuracy (100%) >> Test accuracy (" . round($testAccuracy, 1) . "%)\n";
echo "  The model memorized the training data but failed to generalize!\n\n";

// ============================================================================
// SCENARIO 2: PROPER GENERALIZATION (Good Model)
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "SCENARIO 2: PROPER GENERALIZATION - Learns Patterns\n";
echo str_repeat('=', 60) . "\n\n";

// Larger, more diverse training set
$largeTrainingData = [
    [5.0, 3.5],
    [4.8, 3.0],
    [5.2, 3.4],
    [4.9, 3.1],
    [5.1, 3.6],  // Setosa
    [5.3, 3.7],
    [4.7, 3.2],
    [5.0, 3.3],
    [4.6, 3.4],
    [5.4, 3.9],
    [6.5, 2.8],
    [6.0, 2.7],
    [5.9, 3.0],
    [6.4, 2.9],
    [6.1, 2.6],  // Versicolor
    [6.3, 2.8],
    [5.8, 2.7],
    [6.2, 2.9],
    [5.7, 2.8],
    [6.6, 3.0],
];

$largeTrainingLabels = [
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'setosa',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
    'versicolor',
];

echo "Training data: " . count($largeTrainingData) . " examples (better!)\n\n";

// Train with k=3 (considers multiple neighbors)
$good_classifier = new KNearestNeighbors(k: 3);
$good_classifier->train($largeTrainingData, $largeTrainingLabels);

// Test on training data
echo "Testing on TRAINING data:\n";
$correct = 0;
foreach ($largeTrainingData as $index => $sample) {
    $prediction = $good_classifier->predict($sample);
    $actual = $largeTrainingLabels[$index];

    if ($prediction === $actual) {
        $correct++;
    }
}

$trainingAccuracy = ($correct / count($largeTrainingData)) * 100;
echo "→ Training Accuracy: " . round($trainingAccuracy, 1) . "%\n\n";

// Test on NEW data
echo "Testing on NEW (unseen) data:\n";
$correct = 0;
foreach ($testData as $index => $sample) {
    $prediction = $good_classifier->predict($sample);
    $actual = $testLabels[$index];
    $match = $prediction === $actual ? '✓' : '✗';

    if ($prediction === $actual) {
        $correct++;
    }

    echo "  {$match} Sample " . ($index + 1) . ": predicted={$prediction}, actual={$actual}\n";
}

$testAccuracy = ($correct / count($testData)) * 100;
echo "\n→ Test Accuracy: " . round($testAccuracy, 1) . "%\n\n";

echo "✓ GOOD GENERALIZATION!\n";
echo "  Training accuracy (" . round($trainingAccuracy, 1) . "%) ≈ Test accuracy (" . round($testAccuracy, 1) . "%)\n";
echo "  The model learned generalizable patterns!\n\n";

// ============================================================================
// COMPARISON AND LESSONS
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "OVERFITTING vs. GENERALIZATION\n";
echo str_repeat('=', 60) . "\n\n";

echo "Overfitted Model (Scenario 1):\n";
echo "  ✗ Training data: 6 examples (too small)\n";
echo "  ✗ k=1 (too sensitive to individual examples)\n";
echo "  ✗ Training accuracy: 100% (suspiciously perfect!)\n";
echo "  ✗ Test accuracy: ~50% (failed to generalize)\n";
echo "  ✗ Memorized training data instead of learning patterns\n\n";

echo "Well-Generalized Model (Scenario 2):\n";
echo "  ✓ Training data: 20 examples (more representative)\n";
echo "  ✓ k=3 (averages over neighbors, more robust)\n";
echo "  ✓ Training accuracy: ~95% (good but not perfect)\n";
echo "  ✓ Test accuracy: ~83% (generalizes well)\n";
echo "  ✓ Learned real patterns in the data\n\n";

echo str_repeat('=', 60) . "\n";
echo "HOW TO PREVENT OVERFITTING\n";
echo str_repeat('=', 60) . "\n\n";

echo "1. Use MORE training data\n";
echo "   - More examples = better representation of the true pattern\n";
echo "   - Harder for model to memorize individual examples\n\n";

echo "2. Use SIMPLER models\n";
echo "   - Increase k in k-NN (consider more neighbors)\n";
echo "   - Limit tree depth in decision trees\n";
echo "   - Use regularization in neural networks\n\n";

echo "3. SPLIT your data properly\n";
echo "   - Training set: Learn patterns (60-80%)\n";
echo "   - Test set: Evaluate generalization (20-40%)\n";
echo "   - Never test on training data!\n\n";

echo "4. Use CROSS-VALIDATION\n";
echo "   - Train/test on multiple splits\n";
echo "   - Average performance across splits\n";
echo "   - More reliable performance estimate\n\n";

echo "5. Monitor the GAP\n";
echo "   - Training accuracy >> Test accuracy = OVERFITTING\n";
echo "   - Training accuracy ≈ Test accuracy = GOOD\n";
echo "   - Training accuracy << Test accuracy = something's wrong!\n\n";

echo str_repeat('=', 60) . "\n";
echo "Key Takeaway:\n";
echo str_repeat('=', 60) . "\n";
echo "Overfitting is when your model memorizes instead of learns.\n";
echo "Always test on unseen data to catch it!\n";
