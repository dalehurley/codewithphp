<?php

declare(strict_types=1);

/**
 * Example 4: Training vs. Inference
 * 
 * Demonstrates the two distinct phases of the ML lifecycle:
 * - Training: Learning patterns from data (slow, done once)
 * - Inference: Making predictions with trained model (fast, done many times)
 */

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "=== Training vs. Inference ===\n\n";

// ============================================================================
// PHASE 1: TRAINING (Done Once, Offline)
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "PHASE 1: TRAINING\n";
echo str_repeat('=', 60) . "\n\n";

// Training data: flower measurements [sepal_length, sepal_width]
$trainingData = [
    [5.1, 3.5],
    [4.9, 3.0],
    [4.7, 3.2],
    [4.6, 3.1],  // Setosa samples
    [5.0, 3.6],
    [5.4, 3.9],
    [4.6, 3.4],
    [5.0, 3.4],
    [7.0, 3.2],
    [6.4, 3.2],
    [6.9, 3.1],
    [5.5, 2.3],  // Versicolor samples
    [6.5, 2.8],
    [5.7, 2.8],
    [6.3, 3.3],
    [4.9, 2.4],
    [6.3, 3.3],
    [5.8, 2.7],
    [7.1, 3.0],
    [6.3, 2.9],  // Virginica samples
    [6.5, 3.0],
    [7.6, 3.0],
    [4.9, 2.5],
    [7.3, 2.9],
];

$trainingLabels = [
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
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
    'virginica',
];

echo "Training dataset:\n";
echo "  - " . count($trainingData) . " labeled examples\n";
echo "  - 2 features per sample\n";
echo "  - 3 classes: setosa, versicolor, virginica\n\n";

echo "Creating k-NN classifier (k=3)...\n";
$classifier = new KNearestNeighbors(k: 3);

echo "Starting training...\n";
$trainingStart = microtime(true);

// This is where the learning happens!
$classifier->train($trainingData, $trainingLabels);

$trainingTime = microtime(true) - $trainingStart;
echo "✓ Training complete in " . number_format($trainingTime * 1000, 2) . " ms\n\n";

echo "What happened during training:\n";
echo "  1. Algorithm stored training examples in memory\n";
echo "  2. Learned the relationship between features and labels\n";
echo "  3. For k-NN: Stored all examples for distance calculations\n\n";

// ============================================================================
// PHASE 2: INFERENCE (Done Many Times, Online/Real-time)
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "PHASE 2: INFERENCE\n";
echo str_repeat('=', 60) . "\n\n";

// New flowers we want to classify (no labels!)
$newFlowers = [
    [5.0, 3.5],  // Looks like setosa
    [6.7, 3.1],  // Looks like versicolor
    [7.2, 3.0],  // Looks like virginica
    [4.8, 3.0],  // Looks like setosa
    [6.1, 2.9],  // Looks like versicolor
];

echo "Making predictions on " . count($newFlowers) . " new flowers:\n\n";

$totalInferenceTime = 0;

foreach ($newFlowers as $index => $flower) {
    $inferenceStart = microtime(true);

    // This is inference - using the trained model to predict
    $prediction = $classifier->predict($flower);

    $inferenceTime = microtime(true) - $inferenceStart;
    $totalInferenceTime += $inferenceTime;

    echo "Flower " . ($index + 1) . ": [" . implode(', ', $flower) . "]\n";
    echo "  → Prediction: {$prediction}\n";
    echo "  → Time: " . number_format($inferenceTime * 1000, 3) . " ms\n\n";
}

$avgInferenceTime = $totalInferenceTime / count($newFlowers);

// ============================================================================
// COMPARISON
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "TRAINING vs. INFERENCE COMPARISON\n";
echo str_repeat('=', 60) . "\n\n";

echo "Training Phase:\n";
echo "  - Time: " . number_format($trainingTime * 1000, 2) . " ms (one-time cost)\n";
echo "  - Frequency: Done once (or periodically for retraining)\n";
echo "  - Process: Learning patterns from labeled data\n";
echo "  - Resource intensive: Can take minutes/hours for large datasets\n";
echo "  - Happens: Offline, during development or scheduled jobs\n\n";

echo "Inference Phase:\n";
echo "  - Time: " . number_format($avgInferenceTime * 1000, 3) . " ms per prediction (fast!)\n";
echo "  - Frequency: Done many times (every user request)\n";
echo "  - Process: Applying learned patterns to new data\n";
echo "  - Lightweight: Must be fast for real-time use\n";
echo "  - Happens: Online, in production serving users\n\n";

$speedup = $trainingTime / $avgInferenceTime;
echo "Inference is " . number_format($speedup, 0) . "x faster than training!\n\n";

// ============================================================================
// DISTANCE CALCULATION (How k-NN Works)
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "HOW k-NN CALCULATES PREDICTIONS\n";
echo str_repeat('=', 60) . "\n\n";

/**
 * Calculate Euclidean distance between two points
 * Formula: √((x₁-x₂)² + (y₁-y₂)²)
 */
function euclideanDistance(array $point1, array $point2): float
{
    $sumSquaredDiffs = 0;
    for ($i = 0; $i < count($point1); $i++) {
        $diff = $point1[$i] - $point2[$i];
        $sumSquaredDiffs += $diff * $diff;
    }
    return sqrt($sumSquaredDiffs);
}

$testFlower = [5.0, 3.5];
echo "Example: Classifying flower [" . implode(', ', $testFlower) . "]\n\n";

// Calculate distances to first few training samples
$distances = [];
for ($i = 0; $i < 6; $i++) {
    $distance = euclideanDistance($testFlower, $trainingData[$i]);
    $distances[] = [
        'sample' => $trainingData[$i],
        'label' => $trainingLabels[$i],
        'distance' => $distance,
    ];
}

// Sort by distance
usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

echo "Distances to nearest neighbors:\n";
foreach ($distances as $index => $neighbor) {
    $mark = $index < 3 ? '→' : ' ';
    echo "  {$mark} [" . implode(', ', $neighbor['sample']) . "] ";
    echo "({$neighbor['label']}): " . number_format($neighbor['distance'], 3) . "\n";
}

echo "\nThe 3 nearest neighbors are all 'setosa', so prediction is: setosa\n\n";

echo str_repeat('=', 60) . "\n";
echo "Key Takeaways:\n";
echo str_repeat('=', 60) . "\n";
echo "✓ Training learns patterns (slow, done once)\n";
echo "✓ Inference applies patterns (fast, done many times)\n";
echo "✓ Separate these phases for performance\n";
echo "✓ Save trained models to avoid retraining\n";
echo "✓ k-NN uses distance to find similar examples\n";
