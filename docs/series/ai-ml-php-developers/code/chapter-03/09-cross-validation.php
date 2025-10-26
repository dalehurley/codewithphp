<?php

declare(strict_types=1);

/**
 * Example 9: k-Fold Cross-Validation
 * 
 * Demonstrates why cross-validation is more reliable than a single train/test split.
 * Uses k-fold CV to get robust performance estimates that don't depend on lucky/unlucky splits.
 */

require __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         k-Fold Cross-Validation Demonstration            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Load iris dataset
$csvPath = __DIR__ . '/data/iris.csv';
$file = fopen($csvPath, 'r');
$header = fgetcsv($file);

$samples = [];
$labels = [];

while (($row = fgetcsv($file)) !== false) {
    $samples[] = [(float) $row[0], (float) $row[1], (float) $row[2], (float) $row[3]];
    $labels[] = $row[4];
}

fclose($file);

echo "Dataset: " . count($samples) . " iris flower samples\n";
echo "Classes: " . implode(', ', array_unique($labels)) . "\n\n";

/**
 * k-Fold Cross-Validation Implementation
 * 
 * Splits data into k folds, trains on k-1 folds, tests on 1 fold.
 * Repeats k times so each fold serves as test set once.
 * 
 * @param array $samples Feature data
 * @param array $labels Target labels
 * @param int $k Number of folds
 * @param callable $modelFactory Function that returns a new model instance
 * @return array ['scores' => [...], 'mean' => float, 'std' => float]
 */
function kFoldCrossValidation(array $samples, array $labels, int $k, callable $modelFactory): array
{
    $n = count($samples);
    $foldSize = (int) floor($n / $k);

    // Shuffle data to randomize folds
    $indices = range(0, $n - 1);
    shuffle($indices);

    $scores = [];

    echo "Performing {$k}-fold cross-validation...\n";
    echo str_repeat('-', 60) . "\n";

    for ($fold = 0; $fold < $k; $fold++) {
        // Determine test fold indices
        $testStart = $fold * $foldSize;
        $testEnd = ($fold === $k - 1) ? $n : ($fold + 1) * $foldSize;

        // Split into train and test
        $trainSamples = [];
        $trainLabels = [];
        $testSamples = [];
        $testLabels = [];

        for ($i = 0; $i < $n; $i++) {
            $idx = $indices[$i];

            if ($i >= $testStart && $i < $testEnd) {
                $testSamples[] = $samples[$idx];
                $testLabels[] = $labels[$idx];
            } else {
                $trainSamples[] = $samples[$idx];
                $trainLabels[] = $labels[$idx];
            }
        }

        // Train model
        $model = $modelFactory();
        $model->train($trainSamples, $trainLabels);

        // Evaluate on test fold
        $predictions = $model->predict($testSamples);
        $correct = 0;

        for ($i = 0; $i < count($testSamples); $i++) {
            if ($predictions[$i] === $testLabels[$i]) {
                $correct++;
            }
        }

        $accuracy = ($correct / count($testSamples)) * 100;
        $scores[] = $accuracy;

        echo "Fold " . ($fold + 1) . ": ";
        echo "Train=" . count($trainSamples) . ", Test=" . count($testSamples);
        echo " → Accuracy: " . number_format($accuracy, 2) . "%\n";
    }

    echo str_repeat('-', 60) . "\n";

    // Calculate statistics
    $mean = array_sum($scores) / count($scores);
    $variance = 0;
    foreach ($scores as $score) {
        $variance += pow($score - $mean, 2);
    }
    $std = sqrt($variance / count($scores));

    return [
        'scores' => $scores,
        'mean' => $mean,
        'std' => $std,
    ];
}

/**
 * Simple train/test split for comparison
 */
function simpleTrainTestSplit(array $samples, array $labels, float $testRatio = 0.2): array
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

// ============================================================
// COMPARISON: Single Split vs. Cross-Validation
// ============================================================

echo "============================================================\n";
echo "APPROACH 1: Single Train/Test Split (80/20)\n";
echo "============================================================\n\n";

// Run multiple times to show variance
$singleSplitAccuracies = [];

for ($run = 0; $run < 5; $run++) {
    [$trainSamples, $trainLabels, $testSamples, $testLabels] =
        simpleTrainTestSplit($samples, $labels, 0.2);

    $classifier = new KNearestNeighbors(k: 5);
    $classifier->train($trainSamples, $trainLabels);

    $predictions = $classifier->predict($testSamples);
    $correct = 0;

    for ($i = 0; $i < count($testSamples); $i++) {
        if ($predictions[$i] === $testLabels[$i]) {
            $correct++;
        }
    }

    $accuracy = ($correct / count($testSamples)) * 100;
    $singleSplitAccuracies[] = $accuracy;

    echo "Run " . ($run + 1) . ": Accuracy = " . number_format($accuracy, 2) . "%\n";
}

$singleMean = array_sum($singleSplitAccuracies) / count($singleSplitAccuracies);
$singleVariance = 0;
foreach ($singleSplitAccuracies as $acc) {
    $singleVariance += pow($acc - $singleMean, 2);
}
$singleStd = sqrt($singleVariance / count($singleSplitAccuracies));

echo "\nResults from 5 runs:\n";
echo "  Mean Accuracy: " . number_format($singleMean, 2) . "%\n";
echo "  Std Deviation: " . number_format($singleStd, 2) . "% (±" . number_format($singleStd * 2, 2) . "% range)\n";
echo "  Range: " . number_format(min($singleSplitAccuracies), 2) . "% to " . number_format(max($singleSplitAccuracies), 2) . "%\n\n";

echo "⚠️  Notice: Accuracy varies significantly between runs!\n";
echo "   This is because each split is different (random luck).\n\n";

echo "============================================================\n";
echo "APPROACH 2: 5-Fold Cross-Validation\n";
echo "============================================================\n\n";

$cvResults = kFoldCrossValidation(
    $samples,
    $labels,
    k: 5,
    modelFactory: fn() => new KNearestNeighbors(k: 5)
);

echo "\nCross-Validation Results:\n";
echo "  Mean Accuracy: " . number_format($cvResults['mean'], 2) . "%\n";
echo "  Std Deviation: " . number_format($cvResults['std'], 2) . "% (±" . number_format($cvResults['std'] * 2, 2) . "% range)\n";
echo "  Min: " . number_format(min($cvResults['scores']), 2) . "%\n";
echo "  Max: " . number_format(max($cvResults['scores']), 2) . "%\n\n";

echo "✓ Cross-validation uses ALL data for both training and testing.\n";
echo "  Each sample is tested exactly once, making estimates more reliable.\n\n";

// ============================================================
// COMPARING DIFFERENT K VALUES USING CV
// ============================================================

echo "============================================================\n";
echo "BONUS: Using CV to Compare Different k Values\n";
echo "============================================================\n\n";

echo "Testing k-NN with different k values...\n\n";

$kValues = [1, 3, 5, 7, 9];
$results = [];

foreach ($kValues as $k) {
    echo "k = {$k}:\n";

    $cvResults = kFoldCrossValidation(
        $samples,
        $labels,
        k: 5,
        modelFactory: fn() => new KNearestNeighbors(k: $k)
    );

    $results[$k] = $cvResults;

    echo "  → Mean Accuracy: " . number_format($cvResults['mean'], 2) . "% ";
    echo "(±" . number_format($cvResults['std'], 2) . "%)\n\n";
}

// Find best k
$bestK = null;
$bestAccuracy = 0;

foreach ($results as $k => $result) {
    if ($result['mean'] > $bestAccuracy) {
        $bestAccuracy = $result['mean'];
        $bestK = $k;
    }
}

echo "============================================================\n";
echo "CONCLUSION\n";
echo "============================================================\n\n";

echo "✓ Best k value: {$bestK} (Accuracy: " . number_format($bestAccuracy, 2) . "%)\n\n";

echo "Key Takeaways:\n";
echo "  1. Single train/test splits are subject to random variance\n";
echo "  2. Cross-validation provides more stable, reliable estimates\n";
echo "  3. CV uses all data efficiently (no 'wasted' test set)\n";
echo "  4. Perfect for comparing models or hyperparameters\n";
echo "  5. Trade-off: k-fold CV takes k times longer than single split\n\n";

echo "When to use each approach:\n";
echo "  - Single split: Quick experiments, very large datasets\n";
echo "  - Cross-validation: Final evaluation, hyperparameter tuning, small datasets\n\n";

echo "🎉 Cross-validation complete!\n";
