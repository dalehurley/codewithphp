<?php

declare(strict_types=1);

/**
 * Example 6: Proper Train/Test Split
 * 
 * Demonstrates how to correctly split data into training and testing sets
 * to get reliable performance estimates and avoid overfitting.
 */

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "=== Train/Test Split ===\n\n";

// Load sample data
$allData = [
    // Setosa examples
    [5.1, 3.5],
    [4.9, 3.0],
    [4.7, 3.2],
    [4.6, 3.1],
    [5.0, 3.6],
    [5.4, 3.9],
    [4.6, 3.4],
    [5.0, 3.4],
    [4.4, 2.9],
    [4.9, 3.1],
    // Versicolor examples
    [7.0, 3.2],
    [6.4, 3.2],
    [6.9, 3.1],
    [5.5, 2.3],
    [6.5, 2.8],
    [5.7, 2.8],
    [6.3, 3.3],
    [4.9, 2.4],
    [6.6, 2.9],
    [5.2, 2.7],
    // Virginica examples
    [6.3, 3.3],
    [5.8, 2.7],
    [7.1, 3.0],
    [6.3, 2.9],
    [6.5, 3.0],
    [7.6, 3.0],
    [4.9, 2.5],
    [7.3, 2.9],
    [6.7, 2.5],
    [7.2, 3.6],
];

$allLabels = [
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
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
];

echo "Full dataset: " . count($allData) . " samples\n";
echo "Classes: " . implode(', ', array_unique($allLabels)) . "\n\n";

/**
 * Split data into training and testing sets
 * 
 * @param array $data Feature arrays
 * @param array $labels Corresponding labels
 * @param float $testRatio Proportion for test set (e.g., 0.2 = 20%)
 * @param bool $shuffle Whether to shuffle before splitting
 * @return array ['train_data', 'train_labels', 'test_data', 'test_labels']
 */
function trainTestSplit(
    array $data,
    array $labels,
    float $testRatio = 0.2,
    bool $shuffle = true
): array {
    $totalSamples = count($data);
    $testSize = (int) round($totalSamples * $testRatio);
    $trainSize = $totalSamples - $testSize;

    // Create indices array
    $indices = range(0, $totalSamples - 1);

    // Shuffle if requested
    if ($shuffle) {
        shuffle($indices);
    }

    // Split indices
    $trainIndices = array_slice($indices, 0, $trainSize);
    $testIndices = array_slice($indices, $trainSize);

    // Split data and labels
    $trainData = [];
    $trainLabels = [];
    $testData = [];
    $testLabels = [];

    foreach ($trainIndices as $idx) {
        $trainData[] = $data[$idx];
        $trainLabels[] = $labels[$idx];
    }

    foreach ($testIndices as $idx) {
        $testData[] = $data[$idx];
        $testLabels[] = $labels[$idx];
    }

    return [
        'train_data' => $trainData,
        'train_labels' => $trainLabels,
        'test_data' => $testData,
        'test_labels' => $testLabels,
    ];
}

/**
 * Calculate classification accuracy
 */
function calculateAccuracy(array $predictions, array $actual): float
{
    $correct = 0;
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] === $actual[$i]) {
            $correct++;
        }
    }
    return ($correct / count($actual)) * 100;
}

// ============================================================================
// SPLIT 1: 80/20 Split (Standard)
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "SPLIT 1: 80% Training / 20% Testing (Standard)\n";
echo str_repeat('=', 60) . "\n\n";

$split = trainTestSplit($allData, $allLabels, testRatio: 0.2, shuffle: true);

echo "Training set: " . count($split['train_data']) . " samples (" .
    round(count($split['train_data']) / count($allData) * 100) . "%)\n";
echo "Test set: " . count($split['test_data']) . " samples (" .
    round(count($split['test_data']) / count($allData) * 100) . "%)\n\n";

// Train model
echo "Training k-NN classifier (k=3)...\n";
$classifier = new KNearestNeighbors(k: 3);
$classifier->train($split['train_data'], $split['train_labels']);
echo "✓ Training complete\n\n";

// Evaluate on training set
echo "Evaluating on TRAINING set:\n";
$trainPredictions = [];
foreach ($split['train_data'] as $sample) {
    $trainPredictions[] = $classifier->predict($sample);
}
$trainAccuracy = calculateAccuracy($trainPredictions, $split['train_labels']);
echo "  Training Accuracy: " . round($trainAccuracy, 2) . "%\n\n";

// Evaluate on test set
echo "Evaluating on TEST set:\n";
$testPredictions = [];
foreach ($split['test_data'] as $sample) {
    $testPredictions[] = $classifier->predict($sample);
}
$testAccuracy = calculateAccuracy($testPredictions, $split['test_labels']);
echo "  Test Accuracy: " . round($testAccuracy, 2) . "%\n\n";

$gap = abs($trainAccuracy - $testAccuracy);
echo "Accuracy Gap: " . round($gap, 2) . "% ";
if ($gap < 10) {
    echo "(Good generalization! ✓)\n";
} else {
    echo "(Possible overfitting! ⚠️)\n";
}

// ============================================================================
// SPLIT 2: Different Ratios Comparison
// ============================================================================

echo "\n" . str_repeat('=', 60) . "\n";
echo "COMPARING DIFFERENT SPLIT RATIOS\n";
echo str_repeat('=', 60) . "\n\n";

$ratios = [0.1, 0.2, 0.3, 0.4];

foreach ($ratios as $ratio) {
    $trainPercent = (1 - $ratio) * 100;
    $testPercent = $ratio * 100;

    echo "Split: {$trainPercent}% train / {$testPercent}% test\n";

    $split = trainTestSplit($allData, $allLabels, testRatio: $ratio, shuffle: true);

    $clf = new KNearestNeighbors(k: 3);
    $clf->train($split['train_data'], $split['train_labels']);

    // Test accuracy only
    $predictions = [];
    foreach ($split['test_data'] as $sample) {
        $predictions[] = $clf->predict($sample);
    }
    $accuracy = calculateAccuracy($predictions, $split['test_labels']);

    echo "  Test Accuracy: " . round($accuracy, 2) . "% ";
    echo "(" . count($split['train_data']) . " train, " . count($split['test_data']) . " test)\n\n";
}

// ============================================================================
// BEST PRACTICES
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "TRAIN/TEST SPLIT BEST PRACTICES\n";
echo str_repeat('=', 60) . "\n\n";

echo "1. TYPICAL SPLIT RATIOS:\n";
echo "   • 80/20 (80% train, 20% test) - Most common\n";
echo "   • 70/30 - When you have less data\n";
echo "   • 90/10 - When you have lots of data\n";
echo "   • Never less than 60/40 for training\n\n";

echo "2. ALWAYS SHUFFLE:\n";
echo "   • Randomize order before splitting\n";
echo "   • Avoids bias from ordered data\n";
echo "   • Example: All class A first, then class B = bad!\n\n";

echo "3. SPLIT BEFORE ANY PROCESSING:\n";
echo "   • Split first, then normalize/scale\n";
echo "   • Calculate stats (mean, std) only on training data\n";
echo "   • Apply same transformation to test data\n";
echo "   • Prevents 'data leakage' from test into training\n\n";

echo "4. NEVER TOUCH TEST DATA DURING TRAINING:\n";
echo "   • Pretend test data doesn't exist until evaluation\n";
echo "   • No peeking at test labels!\n";
echo "   • No tuning hyperparameters based on test performance\n\n";

echo "5. USE TEST SET ONLY ONCE:\n";
echo "   • Final evaluation after all development is done\n";
echo "   • If you tune based on test results, it's no longer 'unseen'\n";
echo "   • For tuning, use cross-validation on training data\n\n";

echo "6. STRATIFIED SPLITTING (Advanced):\n";
echo "   • Maintain class balance in both sets\n";
echo "   • Important for imbalanced datasets\n";
echo "   • Example: If 70% class A, both sets should be 70% class A\n\n";

// ============================================================================
// COMMON MISTAKES
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "COMMON MISTAKES TO AVOID\n";
echo str_repeat('=', 60) . "\n\n";

echo "❌ Training and testing on the same data\n";
echo "   → Result: Overly optimistic accuracy, can't detect overfitting\n\n";

echo "❌ Normalizing before splitting\n";
echo "   → Result: Test data influences training (data leakage)\n\n";

echo "❌ Not shuffling ordered data\n";
echo "   → Result: Biased splits (e.g., all of one class in test set)\n\n";

echo "❌ Test set too small (< 10% of data)\n";
echo "   → Result: Unreliable performance estimates, high variance\n\n";

echo "❌ Using test set to tune hyperparameters\n";
echo "   → Result: Overfitting to test set, need a validation set\n\n";

echo "✓ Correct approach:\n";
echo "  1. Shuffle data\n";
echo "  2. Split into train/test\n";
echo "  3. Process training data and save parameters\n";
echo "  4. Apply same processing to test data\n";
echo "  5. Train model on training data only\n";
echo "  6. Evaluate on test data once at the end\n";
