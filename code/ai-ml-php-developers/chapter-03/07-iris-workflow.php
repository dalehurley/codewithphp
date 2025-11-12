<?php

declare(strict_types=1);

/**
 * Example 7: Complete ML Workflow - Iris Flower Classification
 * 
 * This script demonstrates the entire machine learning workflow from start
 * to finish: load data, explore, preprocess, split, train, evaluate, and deploy.
 * 
 * Dataset: Iris flowers (150 samples, 4 features, 3 classes)
 */

require __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\Native;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   Complete ML Workflow: Iris Flower Classification       ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// STEP 1: Define the Problem
// ============================================================================

echo "STEP 1: Define the Problem\n";
echo str_repeat('-', 60) . "\n";
echo "Goal: Classify iris flowers into 3 species based on measurements\n";
echo "  - Input: 4 numeric features (sepal/petal length & width)\n";
echo "  - Output: Species (Iris-setosa, Iris-versicolor, Iris-virginica)\n";
echo "  - Algorithm: k-Nearest Neighbors (k=5)\n";
echo "  - Success metric: Classification accuracy > 90%\n\n";

// ============================================================================
// STEP 2: Load and Explore Data
// ============================================================================

echo "STEP 2: Load and Explore Data\n";
echo str_repeat('-', 60) . "\n";

$csvPath = __DIR__ . '/data/iris.csv';

if (!file_exists($csvPath)) {
    die("Error: Iris dataset not found at: $csvPath\n");
}

// Load CSV file
$file = fopen($csvPath, 'r');
$header = fgetcsv($file, 0, ",", "\"", "\\"); // Skip header row
$samples = [];
$labels = [];

while (($row = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {
    // Skip empty rows or rows with insufficient data
    if (count($row) < 5 || empty($row[4])) {
        continue;
    }
    // Features: sepal_length, sepal_width, petal_length, petal_width
    $samples[] = [(float) $row[0], (float) $row[1], (float) $row[2], (float) $row[3]];
    // Label: species
    $labels[] = $row[4];
}

fclose($file);

echo "✓ Dataset loaded: " . count($samples) . " samples\n";
echo "✓ Features per sample: " . count($samples[0]) . "\n";
echo "✓ Feature names: " . implode(', ', array_slice($header, 0, 4)) . "\n";
echo "✓ Classes: " . implode(', ', array_unique($labels)) . "\n";

// Calculate class distribution
$classCounts = array_count_values($labels);
echo "\nClass distribution:\n";
foreach ($classCounts as $class => $count) {
    echo "  - {$class}: {$count} samples (" . round($count / count($labels) * 100, 1) . "%)\n";
}

// Display sample data
echo "\nFirst 3 samples:\n";
for ($i = 0; $i < 3; $i++) {
    echo "  " . ($i + 1) . ". Features: [" . implode(', ', array_map(fn($v) => number_format($v, 1), $samples[$i])) .
        "] → Label: {$labels[$i]}\n";
}

echo "\n";

// ============================================================================
// STEP 3: Preprocess Data (Normalize Features)
// ============================================================================

echo "STEP 3: Preprocess Data\n";
echo str_repeat('-', 60) . "\n";

/**
 * Normalize features using min-max scaling to [0, 1]
 */
function normalizeFeatures(array $data): array
{
    $numFeatures = count($data[0]);
    $normalized = [];

    // Calculate min and max for each feature
    $stats = [];
    for ($featureIdx = 0; $featureIdx < $numFeatures; $featureIdx++) {
        $column = array_column($data, $featureIdx);
        $stats[$featureIdx] = [
            'min' => min($column),
            'max' => max($column),
            'range' => max($column) - min($column),
        ];
    }

    // Normalize each sample
    foreach ($data as $sample) {
        $normalizedSample = [];
        for ($featureIdx = 0; $featureIdx < $numFeatures; $featureIdx++) {
            $value = $sample[$featureIdx];
            $min = $stats[$featureIdx]['min'];
            $range = $stats[$featureIdx]['range'];

            $normalizedSample[] = $range > 0 ? ($value - $min) / $range : 0.5;
        }
        $normalized[] = $normalizedSample;
    }

    return $normalized;
}

echo "Original feature ranges:\n";
for ($i = 0; $i < count($samples[0]); $i++) {
    $column = array_column($samples, $i);
    echo "  Feature " . ($i + 1) . ": " . number_format(min($column), 2) .
        " to " . number_format(max($column), 2) . "\n";
}

$normalizedSamples = normalizeFeatures($samples);

echo "\nNormalized feature ranges (should be 0.0 to 1.0):\n";
for ($i = 0; $i < count($normalizedSamples[0]); $i++) {
    $column = array_column($normalizedSamples, $i);
    echo "  Feature " . ($i + 1) . ": " . number_format(min($column), 2) .
        " to " . number_format(max($column), 2) . "\n";
}

echo "✓ Features normalized to [0, 1] range\n\n";

// ============================================================================
// STEP 4: Split Data into Training and Testing Sets
// ============================================================================

echo "STEP 4: Split Data\n";
echo str_repeat('-', 60) . "\n";

/**
 * Shuffle and split data into train/test sets
 */
function trainTestSplit(array $samples, array $labels, float $testRatio = 0.2): array
{
    $indices = range(0, count($samples) - 1);
    shuffle($indices);

    $testSize = (int) round(count($samples) * $testRatio);
    $trainSize = count($samples) - $testSize;

    $trainSamples = [];
    $trainLabels = [];
    $testSamples = [];
    $testLabels = [];

    for ($i = 0; $i < $trainSize; $i++) {
        $idx = $indices[$i];
        $trainSamples[] = $samples[$idx];
        $trainLabels[] = $labels[$idx];
    }

    for ($i = $trainSize; $i < count($indices); $i++) {
        $idx = $indices[$i];
        $testSamples[] = $samples[$idx];
        $testLabels[] = $labels[$idx];
    }

    return [$trainSamples, $trainLabels, $testSamples, $testLabels];
}

[$trainSamples, $trainLabels, $testSamples, $testLabels] =
    trainTestSplit($normalizedSamples, $labels, testRatio: 0.2);

echo "✓ Data split complete\n";
echo "  Training set: " . count($trainSamples) . " samples (" .
    round(count($trainSamples) / count($samples) * 100) . "%)\n";
echo "  Test set: " . count($testSamples) . " samples (" .
    round(count($testSamples) / count($samples) * 100) . "%)\n\n";

// ============================================================================
// STEP 5: Train the Model
// ============================================================================

echo "STEP 5: Train the Model\n";
echo str_repeat('-', 60) . "\n";

$trainingDataset = new Labeled($trainSamples, $trainLabels);

echo "Creating k-NN classifier (k=5)...\n";
$estimator = new KNearestNeighbors(5);

echo "Training model...\n";
$startTime = microtime(true);

$estimator->train($trainingDataset);

$trainingTime = microtime(true) - $startTime;
echo "✓ Model trained in " . number_format($trainingTime, 3) . " seconds\n\n";

// ============================================================================
// STEP 6: Evaluate the Model
// ============================================================================

echo "STEP 6: Evaluate the Model\n";
echo str_repeat('-', 60) . "\n";

// Make predictions on test set
echo "Making predictions on test set...\n";
$predictions = $estimator->predict(new Labeled($testSamples, $testLabels));

// Calculate accuracy
$metric = new Accuracy();
$accuracy = $metric->score($predictions, $testLabels);

echo "✓ Predictions complete\n\n";

echo "Performance Metrics:\n";
echo "  Test Accuracy: " . number_format($accuracy * 100, 2) . "%\n";
echo "  Correct predictions: " . round($accuracy * count($testLabels)) . " / " . count($testLabels) . "\n\n";

// Show some example predictions
echo "Sample predictions:\n";
for ($i = 0; $i < min(5, count($testSamples)); $i++) {
    $match = $predictions[$i] === $testLabels[$i] ? '✓' : '✗';
    echo "  {$match} Sample " . ($i + 1) . ": Predicted '{$predictions[$i]}', Actual '{$testLabels[$i]}'\n";
}

echo "\n";

// ============================================================================
// STEP 7: Save the Model
// ============================================================================

echo "STEP 7: Save the Model\n";
echo str_repeat('-', 60) . "\n";

$modelDir = __DIR__ . '/models';
if (!is_dir($modelDir)) {
    mkdir($modelDir, 0755, true);
}

$modelPath = $modelDir . '/iris-knn.rbx';
$persister = new Filesystem($modelPath);
$encoding = new Native();
$persister->save($encoding->serialize($estimator));

echo "✓ Model saved to: " . basename($modelPath) . "\n";
echo "  File size: " . number_format(filesize($modelPath) / 1024, 2) . " KB\n\n";

// ============================================================================
// STEP 8: Load Model and Make New Prediction
// ============================================================================

echo "STEP 8: Load Model and Predict New Sample\n";
echo str_repeat('-', 60) . "\n";

// Load the saved model
$loadedData = $persister->load();
$loadedEstimator = $encoding->deserialize($loadedData);
echo "✓ Model loaded from disk\n\n";

// Predict on a new, unseen flower
$newFlower = [
    [0.25, 0.58, 0.12, 0.08],  // Normalized features for a setosa-like flower
];

echo "New flower features (normalized): [" . implode(', ', array_map(fn($v) => number_format($v, 2), $newFlower[0])) . "]\n";

$prediction = $loadedEstimator->predictSample($newFlower[0]);

echo "→ Prediction: {$prediction}\n\n";

// ============================================================================
// SUMMARY
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "WORKFLOW COMPLETE!\n";
echo str_repeat('=', 60) . "\n\n";

echo "✓ Problem defined: Multi-class classification\n";
echo "✓ Data loaded: 150 iris flower samples\n";
echo "✓ Data preprocessed: Features normalized\n";
echo "✓ Data split: 80% train, 20% test\n";
echo "✓ Model trained: k-NN (k=5) in " . number_format($trainingTime, 3) . "s\n";
echo "✓ Model evaluated: " . number_format($accuracy * 100, 2) . "% accuracy\n";
echo "✓ Model saved: Ready for production use\n";
echo "✓ Model reloaded: Successfully made new predictions\n\n";

if ($accuracy >= 0.9) {
    echo "🎉 SUCCESS! Model achieves target accuracy (>90%)\n";
    echo "   Ready for deployment!\n";
} else {
    echo "⚠️ Model accuracy below target (90%)\n";
    echo "   Consider: more data, different algorithm, or feature engineering\n";
}

echo "\n";
echo str_repeat('=', 60) . "\n";
echo "Next Steps:\n";
echo str_repeat('=', 60) . "\n";
echo "1. Integrate model into your PHP application\n";
echo "2. Monitor prediction accuracy in production\n";
echo "3. Retrain periodically with new data\n";
echo "4. Experiment with other algorithms\n";
echo "5. Try feature engineering for better results\n";
