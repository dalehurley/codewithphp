<?php

declare(strict_types=1);

/**
 * Example 8: Comparing Different ML Algorithms
 * 
 * Compares multiple classification algorithms on the same dataset
 * to understand their strengths, weaknesses, and when to use each.
 */

require __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Classifiers\GaussianNB;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\CrossValidation\Metrics\Accuracy;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         Comparing Machine Learning Algorithms            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Load Iris dataset
$csvPath = __DIR__ . '/data/iris.csv';
$file = fopen($csvPath, 'r');
fgetcsv($file); // Skip header

$samples = [];
$labels = [];

while (($row = fgetcsv($file)) !== false) {
    $samples[] = [(float) $row[0], (float) $row[1], (float) $row[2], (float) $row[3]];
    $labels[] = $row[4];
}
fclose($file);

echo "Dataset: " . count($samples) . " iris flower samples\n";
echo "Features: 4 (sepal/petal measurements)\n";
echo "Classes: 3 (setosa, versicolor, virginica)\n\n";

// Split data
function trainTestSplit(array $samples, array $labels, float $testRatio = 0.3): array
{
    $indices = range(0, count($samples) - 1);
    shuffle($indices);

    $testSize = (int) round(count($samples) * $testRatio);
    $trainSamples = [];
    $trainLabels = [];
    $testSamples = [];
    $testLabels = [];

    foreach ($indices as $i => $idx) {
        if ($i < count($samples) - $testSize) {
            $trainSamples[] = $samples[$idx];
            $trainLabels[] = $labels[$idx];
        } else {
            $testSamples[] = $samples[$idx];
            $testLabels[] = $labels[$idx];
        }
    }

    return [$trainSamples, $trainLabels, $testSamples, $testLabels];
}

[$trainSamples, $trainLabels, $testSamples, $testLabels] =
    trainTestSplit($samples, $labels, testRatio: 0.3);

$trainDataset = new Labeled($trainSamples, $trainLabels);
$testDataset = new Labeled($testSamples, $testLabels);

echo "Training set: " . count($trainSamples) . " samples\n";
echo "Test set: " . count($testSamples) . " samples\n\n";

// ============================================================================
// Define Algorithms to Compare
// ============================================================================

$algorithms = [
    [
        'name' => 'k-Nearest Neighbors (k=3)',
        'estimator' => new KNearestNeighbors(3),
        'description' => 'Classifies based on majority vote of k nearest neighbors',
        'pros' => ['Simple', 'No training phase', 'Works well with small datasets'],
        'cons' => ['Slow inference', 'Sensitive to feature scaling', 'High memory usage'],
    ],
    [
        'name' => 'k-Nearest Neighbors (k=7)',
        'estimator' => new KNearestNeighbors(7),
        'description' => 'Same as k=3 but considers more neighbors',
        'pros' => ['More robust to noise', 'Better for larger datasets'],
        'cons' => ['Can oversmooth decision boundaries'],
    ],
    [
        'name' => 'Gaussian Naive Bayes',
        'estimator' => new GaussianNB(),
        'description' => 'Probabilistic classifier assuming feature independence',
        'pros' => ['Very fast', 'Works well with small data', 'Handles multiple classes'],
        'cons' => ['Assumes feature independence', 'Less accurate on complex data'],
    ],
    [
        'name' => 'Decision Tree',
        'estimator' => new ClassificationTree(maxDepth: 5),
        'description' => 'Creates a tree of decision rules to classify data',
        'pros' => ['Interpretable', 'Handles non-linear relationships', 'No feature scaling needed'],
        'cons' => ['Can overfit easily', 'Unstable (small changes = different tree)'],
    ],
];

// ============================================================================
// Train and Evaluate Each Algorithm
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "TRAINING AND EVALUATING ALGORITHMS\n";
echo str_repeat('=', 60) . "\n\n";

$results = [];

foreach ($algorithms as $algo) {
    echo "Algorithm: {$algo['name']}\n";
    echo str_repeat('-', 60) . "\n";

    $estimator = $algo['estimator'];

    // Measure training time
    $trainStart = microtime(true);
    $estimator->train($trainDataset);
    $trainTime = microtime(true) - $trainStart;

    // Measure inference time
    $inferenceStart = microtime(true);
    $predictions = $estimator->predict($testDataset);
    $inferenceTime = microtime(true) - $inferenceStart;

    // Calculate accuracy
    $metric = new Accuracy();
    $accuracy = $metric->score($predictions, $testLabels);

    // Store results
    $results[] = [
        'name' => $algo['name'],
        'accuracy' => $accuracy,
        'train_time' => $trainTime,
        'inference_time' => $inferenceTime,
        'avg_prediction_time' => $inferenceTime / count($testSamples),
        'description' => $algo['description'],
        'pros' => $algo['pros'],
        'cons' => $algo['cons'],
    ];

    echo "  Description: {$algo['description']}\n";
    echo "  Training time: " . number_format($trainTime * 1000, 2) . " ms\n";
    echo "  Inference time: " . number_format($inferenceTime * 1000, 2) . " ms\n";
    echo "  Avg per sample: " . number_format(($inferenceTime / count($testSamples)) * 1000, 3) . " ms\n";
    echo "  Accuracy: " . number_format($accuracy * 100, 2) . "%\n";
    echo "  Correct: " . round($accuracy * count($testLabels)) . " / " . count($testLabels) . "\n\n";
}

// ============================================================================
// Comparison Summary
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "COMPARISON SUMMARY\n";
echo str_repeat('=', 60) . "\n\n";

// Sort by accuracy
usort($results, fn($a, $b) => $b['accuracy'] <=> $a['accuracy']);

echo "Ranked by Accuracy:\n";
echo str_repeat('-', 60) . "\n";
foreach ($results as $rank => $result) {
    $medal = match ($rank) {
        0 => '🥇',
        1 => '🥈',
        2 => '🥉',
        default => '  ',
    };
    echo "{$medal} " . ($rank + 1) . ". {$result['name']}: " .
        number_format($result['accuracy'] * 100, 2) . "%\n";
}

echo "\n";

// Sort by training speed
usort($results, fn($a, $b) => $a['train_time'] <=> $b['train_time']);

echo "Ranked by Training Speed:\n";
echo str_repeat('-', 60) . "\n";
foreach ($results as $rank => $result) {
    echo "  " . ($rank + 1) . ". {$result['name']}: " .
        number_format($result['train_time'] * 1000, 2) . " ms\n";
}

echo "\n";

// Sort by inference speed
usort($results, fn($a, $b) => $a['avg_prediction_time'] <=> $b['avg_prediction_time']);

echo "Ranked by Inference Speed (per sample):\n";
echo str_repeat('-', 60) . "\n";
foreach ($results as $rank => $result) {
    echo "  " . ($rank + 1) . ". {$result['name']}: " .
        number_format($result['avg_prediction_time'] * 1000, 3) . " ms\n";
}

echo "\n";

// ============================================================================
// Detailed Algorithm Characteristics
// ============================================================================

// Re-sort alphabetically for detailed view
usort($results, fn($a, $b) => $a['name'] <=> $b['name']);

echo str_repeat('=', 60) . "\n";
echo "DETAILED ALGORITHM CHARACTERISTICS\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($results as $result) {
    echo "{$result['name']}\n";
    echo str_repeat('-', 60) . "\n";
    echo "Description: {$result['description']}\n\n";

    echo "Pros:\n";
    foreach ($result['pros'] as $pro) {
        echo "  ✓ {$pro}\n";
    }

    echo "\nCons:\n";
    foreach ($result['cons'] as $con) {
        echo "  ✗ {$con}\n";
    }

    echo "\nPerformance:\n";
    echo "  Accuracy: " . number_format($result['accuracy'] * 100, 2) . "%\n";
    echo "  Training: " . number_format($result['train_time'] * 1000, 2) . " ms\n";
    echo "  Inference: " . number_format($result['avg_prediction_time'] * 1000, 3) . " ms/sample\n\n";
}

// ============================================================================
// Recommendations
// ============================================================================

echo str_repeat('=', 60) . "\n";
echo "WHEN TO USE EACH ALGORITHM\n";
echo str_repeat('=', 60) . "\n\n";

echo "k-Nearest Neighbors:\n";
echo "  Use when:\n";
echo "    • You have small to medium datasets (< 10,000 samples)\n";
echo "    • Decision boundaries are complex or non-linear\n";
echo "    • You need a simple baseline algorithm\n";
echo "    • Training time doesn't matter\n";
echo "  Avoid when:\n";
echo "    • You need fast predictions (real-time systems)\n";
echo "    • You have high-dimensional data (>20 features)\n";
echo "    • Features have very different scales\n\n";

echo "Naive Bayes:\n";
echo "  Use when:\n";
echo "    • You need very fast training and inference\n";
echo "    • Features are roughly independent\n";
echo "    • You have limited computational resources\n";
echo "    • Text classification (spam detection, sentiment analysis)\n";
echo "  Avoid when:\n";
echo "    • Features are highly correlated\n";
echo "    • Maximum accuracy is critical\n";
echo "    • Relationships between features matter\n\n";

echo "Decision Trees:\n";
echo "  Use when:\n";
echo "    • Interpretability is important\n";
echo "    • You need to explain predictions to non-technical users\n";
echo "    • Features have different types (numeric, categorical)\n";
echo "    • You want to understand feature importance\n";
echo "  Avoid when:\n";
echo "    • Dataset is very small (prone to overfitting)\n";
echo "    • You need the most accurate model\n";
echo "    • Data has high variance or noise\n\n";

echo str_repeat('=', 60) . "\n";
echo "Key Takeaway:\n";
echo str_repeat('=', 60) . "\n";
echo "There's no single 'best' algorithm. Choice depends on:\n";
echo "  • Dataset characteristics (size, features, noise)\n";
echo "  • Performance requirements (speed vs accuracy)\n";
echo "  • Interpretability needs\n";
echo "  • Computational constraints\n\n";
echo "Always test multiple algorithms and pick what works best!\n";
