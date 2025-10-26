<?php

declare(strict_types=1);

/**
 * Example 10: Three-Way Split (Train/Validation/Test)
 * 
 * Demonstrates the proper workflow for hyperparameter tuning:
 * - Train set: Learn model parameters
 * - Validation set: Tune hyperparameters (like k in k-NN)
 * - Test set: Final evaluation (touch ONCE)
 * 
 * This prevents "overfitting to the test set" by keeping test data truly unseen.
 */

require __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   Three-Way Split: Train/Validation/Test Workflow       ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Load iris dataset
$csvPath = __DIR__ . '/data/iris.csv';
$file = fopen($csvPath, 'r');
$header = fgetcsv($file, 0, ",", "\"", "\\");

$samples = [];
$labels = [];

while (($row = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {
    $samples[] = [(float) $row[0], (float) $row[1], (float) $row[2], (float) $row[3]];
    $labels[] = $row[4];
}

fclose($file);

echo "Dataset: " . count($samples) . " iris samples\n\n";

/**
 * Split data into three sets: Train (60%), Validation (20%), Test (20%)
 * 
 * @param array $samples Feature data
 * @param array $labels Target labels
 * @return array ['train' => [...], 'validation' => [...], 'test' => [...]]
 */
function trainValTestSplit(array $samples, array $labels): array
{
    $n = count($samples);

    // Shuffle to randomize
    $indices = range(0, $n - 1);
    shuffle($indices);

    // Calculate split points
    $trainSize = (int) round($n * 0.6);
    $valSize = (int) round($n * 0.2);
    // Test gets remainder to ensure we use all samples

    $trainSamples = [];
    $trainLabels = [];
    $valSamples = [];
    $valLabels = [];
    $testSamples = [];
    $testLabels = [];

    for ($i = 0; $i < $n; $i++) {
        $idx = $indices[$i];

        if ($i < $trainSize) {
            $trainSamples[] = $samples[$idx];
            $trainLabels[] = $labels[$idx];
        } elseif ($i < $trainSize + $valSize) {
            $valSamples[] = $samples[$idx];
            $valLabels[] = $labels[$idx];
        } else {
            $testSamples[] = $samples[$idx];
            $testLabels[] = $labels[$idx];
        }
    }

    return [
        'train' => ['samples' => $trainSamples, 'labels' => $trainLabels],
        'validation' => ['samples' => $valSamples, 'labels' => $valLabels],
        'test' => ['samples' => $testSamples, 'labels' => $testLabels],
    ];
}

/**
 * Evaluate model accuracy
 */
function evaluateAccuracy(KNearestNeighbors $model, array $samples, array $labels): float
{
    $predictions = $model->predict($samples);
    $correct = 0;

    for ($i = 0; $i < count($samples); $i++) {
        if ($predictions[$i] === $labels[$i]) {
            $correct++;
        }
    }

    return ($correct / count($samples)) * 100;
}

// ============================================================
// STEP 1: Split Data into Three Sets
// ============================================================

echo "============================================================\n";
echo "STEP 1: Split Data into Three Sets\n";
echo "============================================================\n\n";

$splits = trainValTestSplit($samples, $labels);

$trainSamples = $splits['train']['samples'];
$trainLabels = $splits['train']['labels'];
$valSamples = $splits['validation']['samples'];
$valLabels = $splits['validation']['labels'];
$testSamples = $splits['test']['samples'];
$testLabels = $splits['test']['labels'];

echo "Data split complete:\n";
echo "  Training set:   " . count($trainSamples) . " samples (" . number_format(count($trainSamples) / count($samples) * 100, 1) . "%)\n";
echo "  Validation set: " . count($valSamples) . " samples (" . number_format(count($valSamples) / count($samples) * 100, 1) . "%)\n";
echo "  Test set:       " . count($testSamples) . " samples (" . number_format(count($testSamples) / count($samples) * 100, 1) . "%)\n\n";

echo "Purpose of each set:\n";
echo "  📚 Training:   Learn model parameters (patterns from data)\n";
echo "  🎯 Validation: Tune hyperparameters (choose best k value)\n";
echo "  🔒 Test:       Final evaluation (estimate real-world performance)\n\n";

echo "⚠️  CRITICAL RULE: Never tune hyperparameters using the test set!\n";
echo "   Test set should be touched ONCE at the very end.\n\n";

// ============================================================
// STEP 2: Hyperparameter Tuning Using Validation Set
// ============================================================

echo "============================================================\n";
echo "STEP 2: Tune Hyperparameter (k) Using Validation Set\n";
echo "============================================================\n\n";

echo "Testing different k values for k-NN classifier...\n\n";

$kValues = [1, 3, 5, 7, 9, 11];
$validationResults = [];

foreach ($kValues as $k) {
    // Train on training set
    $model = new KNearestNeighbors(k: $k);
    $model->train($trainSamples, $trainLabels);

    // Evaluate on VALIDATION set (not test!)
    $valAccuracy = evaluateAccuracy($model, $valSamples, $valLabels);
    $validationResults[$k] = $valAccuracy;

    echo "k = " . str_pad((string) $k, 2, ' ', STR_PAD_LEFT) . ": ";
    echo "Validation Accuracy = " . number_format($valAccuracy, 2) . "%";

    // Also show training accuracy to detect overfitting
    $trainAccuracy = evaluateAccuracy($model, $trainSamples, $trainLabels);
    echo " (Train = " . number_format($trainAccuracy, 2) . "%)";

    $gap = $trainAccuracy - $valAccuracy;
    if ($gap > 10) {
        echo " ⚠️  Large gap!";
    }

    echo "\n";
}

echo "\n";

// Find best k based on validation performance
$bestK = null;
$bestValAccuracy = 0;

foreach ($validationResults as $k => $accuracy) {
    if ($accuracy > $bestValAccuracy) {
        $bestValAccuracy = $accuracy;
        $bestK = $k;
    }
}

echo "Best hyperparameter: k = {$bestK}\n";
echo "  → Validation Accuracy: " . number_format($bestValAccuracy, 2) . "%\n\n";

echo "This is the model we'll use for final evaluation.\n\n";

// ============================================================
// STEP 3: Final Evaluation on Test Set
// ============================================================

echo "============================================================\n";
echo "STEP 3: Final Evaluation on Test Set (Used ONCE)\n";
echo "============================================================\n\n";

// Train final model with best k on training data
$finalModel = new KNearestNeighbors(k: $bestK);
$finalModel->train($trainSamples, $trainLabels);

// Evaluate on test set (first and only time!)
$testAccuracy = evaluateAccuracy($finalModel, $testSamples, $testLabels);

echo "Final model (k = {$bestK}) performance:\n\n";
echo "  Training Accuracy:   " . number_format(evaluateAccuracy($finalModel, $trainSamples, $trainLabels), 2) . "%\n";
echo "  Validation Accuracy: " . number_format($bestValAccuracy, 2) . "%\n";
echo "  Test Accuracy:       " . number_format($testAccuracy, 2) . "%\n\n";

$trainValGap = evaluateAccuracy($finalModel, $trainSamples, $trainLabels) - $bestValAccuracy;
$valTestGap = $bestValAccuracy - $testAccuracy;

echo "Analysis:\n";
echo "  Train-Validation gap: " . number_format(abs($trainValGap), 2) . "%";
if (abs($trainValGap) < 10) {
    echo " ✓ Good\n";
} else {
    echo " ⚠️  May be overfitting\n";
}

echo "  Validation-Test gap:  " . number_format(abs($valTestGap), 2) . "%";
if (abs($valTestGap) < 10) {
    echo " ✓ Good\n";
} else {
    echo " ⚠️  Validation may not represent test well\n";
}

echo "\n";

// ============================================================
// COMPARISON: What NOT to Do
// ============================================================

echo "============================================================\n";
echo "WRONG APPROACH: Tuning on Test Set (What NOT to Do)\n";
echo "============================================================\n\n";

echo "Let's see what happens if we tune on the test set...\n\n";

$testTuningResults = [];

foreach ($kValues as $k) {
    $model = new KNearestNeighbors(k: $k);
    $model->train($trainSamples, $trainLabels);

    // WRONG: Tuning based on test set performance
    $testAcc = evaluateAccuracy($model, $testSamples, $testLabels);
    $testTuningResults[$k] = $testAcc;

    echo "k = {$k}: Test Accuracy = " . number_format($testAcc, 2) . "%\n";
}

$bestKWrong = array_search(max($testTuningResults), $testTuningResults);

echo "\n❌ If we chose k based on test set, we'd pick k = {$bestKWrong}\n";
echo "   Test Accuracy: " . number_format($testTuningResults[$bestKWrong], 2) . "%\n\n";

echo "⚠️  THE PROBLEM:\n";
echo "   Now the test accuracy is NOT an unbiased estimate!\n";
echo "   We've 'overfit' to the test set by choosing k that works best on it.\n";
echo "   Our reported performance will be overly optimistic.\n\n";

echo "✓ CORRECT APPROACH:\n";
echo "   1. Use validation set to choose k = {$bestK}\n";
echo "   2. Report test accuracy = " . number_format($testAccuracy, 2) . "% (unbiased estimate)\n";
echo "   3. Test set was used ONCE, so we didn't overfit to it\n\n";

// ============================================================
// SUMMARY
// ============================================================

echo "============================================================\n";
echo "SUMMARY: Three-Way Split Best Practices\n";
echo "============================================================\n\n";

echo "1. TRAINING SET (60%):\n";
echo "   - Used to train model parameters\n";
echo "   - Larger is better (more data = better learning)\n";
echo "   - Used repeatedly during development\n\n";

echo "2. VALIDATION SET (20%):\n";
echo "   - Used to tune hyperparameters\n";
echo "   - Select best model architecture\n";
echo "   - Can be used many times without 'cheating'\n\n";

echo "3. TEST SET (20%):\n";
echo "   - Final, unbiased performance estimate\n";
echo "   - Use ONCE at the very end\n";
echo "   - Simulates real-world performance\n\n";

echo "Common Split Ratios:\n";
echo "  60/20/20: Standard for medium datasets (100-10,000 samples)\n";
echo "  70/15/15: When data is limited (< 1,000 samples)\n";
echo "  80/10/10: Large datasets (> 10,000 samples)\n\n";

echo "Alternative: Cross-Validation\n";
echo "  Instead of fixed validation set, use k-fold CV on training data.\n";
echo "  Still keep test set separate for final evaluation!\n\n";

echo "🎉 Three-way split complete!\n";
echo "   Your model is ready for production with confidence in its performance.\n";
