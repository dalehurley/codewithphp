<?php

declare(strict_types=1);

/**
 * SMOTE and Class Imbalance Handling Demo
 * 
 * Demonstrates techniques for handling severely imbalanced datasets:
 * - Random Oversampling
 * - SMOTE (Synthetic Minority Over-sampling Technique)
 * - Random Undersampling
 * - Class Weights
 * 
 * PHP version 8.4
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Kernels\Distance\Euclidean;

/**
 * Random oversampling of minority class
 *
 * @param array $samples Feature data
 * @param array $labels Target labels
 * @param string $minorityClass Class to oversample
 * @return array ['samples' => array, 'labels' => array]
 */
function randomOversample(
    array $samples,
    array $labels,
    string $minorityClass
): array {
    // Separate minority and majority
    $minorityIndices = [];
    $majorityIndices = [];

    foreach ($labels as $idx => $label) {
        if ($label === $minorityClass) {
            $minorityIndices[] = $idx;
        } else {
            $majorityIndices[] = $idx;
        }
    }

    $minorityCount = count($minorityIndices);
    $majorityCount = count($majorityIndices);

    echo "Original distribution:\n";
    echo "  Minority ({$minorityClass}): {$minorityCount}\n";
    echo "  Majority: {$majorityCount}\n";

    // Duplicate minority samples to match majority
    $oversampledIndices = $minorityIndices;
    while (count($oversampledIndices) < $majorityCount) {
        // Randomly pick a minority sample to duplicate
        $oversampledIndices[] = $minorityIndices[array_rand($minorityIndices)];
    }

    // Combine
    $allIndices = array_merge($majorityIndices, $oversampledIndices);
    shuffle($allIndices);

    $newSamples = [];
    $newLabels = [];
    foreach ($allIndices as $idx) {
        $newSamples[] = $samples[$idx];
        $newLabels[] = $labels[$idx];
    }

    echo "After oversampling:\n";
    echo "  Minority ({$minorityClass}): " . count($oversampledIndices) . "\n";
    echo "  Majority: {$majorityCount}\n\n";

    return ['samples' => $newSamples, 'labels' => $newLabels];
}

/**
 * SMOTE: Synthetic Minority Over-sampling Technique
 *
 * @param array $samples Feature data
 * @param array $labels Target labels
 * @param string $minorityClass Class to oversample
 * @param int $k Number of nearest neighbors to consider
 * @param float $ratio Target minority ratio (1.0 = fully balanced)
 * @return array ['samples' => array, 'labels' => array]
 */
function smote(
    array $samples,
    array $labels,
    string $minorityClass,
    int $k = 5,
    float $ratio = 1.0
): array {
    // Extract minority samples
    $minoritySamples = [];
    $minorityIndices = [];
    $majorityCount = 0;

    foreach ($labels as $idx => $label) {
        if ($label === $minorityClass) {
            $minoritySamples[] = $samples[$idx];
            $minorityIndices[] = $idx;
        } else {
            $majorityCount++;
        }
    }

    $minorityCount = count($minoritySamples);
    $targetMinorityCount = (int)($majorityCount * $ratio);
    $numSynthetic = $targetMinorityCount - $minorityCount;

    if ($numSynthetic <= 0) {
        echo "⚠️  Already balanced or minority is larger\n";
        return ['samples' => $samples, 'labels' => $labels];
    }

    echo "SMOTE: Creating {$numSynthetic} synthetic {$minorityClass} samples\n";

    $syntheticSamples = [];
    $syntheticLabels = [];

    for ($i = 0; $i < $numSynthetic; $i++) {
        // Pick random minority sample
        $sampleIdx = array_rand($minoritySamples);
        $sample = $minoritySamples[$sampleIdx];

        // Find k nearest neighbors (among minority class)
        $distances = [];
        foreach ($minoritySamples as $neighborIdx => $neighbor) {
            if ($neighborIdx === $sampleIdx) continue;
            $distances[$neighborIdx] = euclideanDistance($sample, $neighbor);
        }
        asort($distances);
        $nearestNeighbors = array_slice(array_keys($distances), 0, min($k, count($distances)), true);

        if (empty($nearestNeighbors)) {
            // Not enough neighbors, just duplicate
            $syntheticSamples[] = $sample;
        } else {
            // Pick random neighbor
            $neighborIdx = $nearestNeighbors[array_rand($nearestNeighbors)];
            $neighbor = $minoritySamples[$neighborIdx];

            // Create synthetic sample via interpolation
            // synthetic = sample + λ × (neighbor - sample), where λ ∈ [0, 1]
            $lambda = (float)rand(0, 100) / 100;
            $synthetic = [];

            for ($featureIdx = 0; $featureIdx < count($sample); $featureIdx++) {
                $value = $sample[$featureIdx] + $lambda * ($neighbor[$featureIdx] - $sample[$featureIdx]);
                $synthetic[] = $value;
            }

            $syntheticSamples[] = $synthetic;
        }

        $syntheticLabels[] = $minorityClass;
    }

    // Combine original and synthetic
    $newSamples = array_merge($samples, $syntheticSamples);
    $newLabels = array_merge($labels, $syntheticLabels);

    // Shuffle
    $indices = range(0, count($newSamples) - 1);
    shuffle($indices);

    $shuffledSamples = [];
    $shuffledLabels = [];
    foreach ($indices as $idx) {
        $shuffledSamples[] = $newSamples[$idx];
        $shuffledLabels[] = $newLabels[$idx];
    }

    echo "✓ Synthetic samples generated via k-NN interpolation\n\n";

    return ['samples' => $shuffledSamples, 'labels' => $shuffledLabels];
}

/**
 * Random undersampling of majority class
 */
function randomUndersample(
    array $samples,
    array $labels,
    string $majorityClass,
    float $ratio = 1.0
): array {
    $minorityIndices = [];
    $majorityIndices = [];

    foreach ($labels as $idx => $label) {
        if ($label === $majorityClass) {
            $majorityIndices[] = $idx;
        } else {
            $minorityIndices[] = $idx;
        }
    }

    $minorityCount = count($minorityIndices);
    $majorityCount = count($majorityIndices);
    $targetMajorityCount = (int)($minorityCount * $ratio);

    echo "Original distribution:\n";
    echo "  Minority: {$minorityCount}\n";
    echo "  Majority ({$majorityClass}): {$majorityCount}\n";

    // Randomly sample majority class
    shuffle($majorityIndices);
    $sampledMajorityIndices = array_slice($majorityIndices, 0, $targetMajorityCount);

    echo "After undersampling:\n";
    echo "  Minority: {$minorityCount}\n";
    echo "  Majority ({$majorityClass}): " . count($sampledMajorityIndices) . "\n";
    echo "⚠️  Dataset reduced from " . count($samples) . " to " . (count($minorityIndices) + count($sampledMajorityIndices)) . " samples!\n\n";

    // Combine
    $allIndices = array_merge($minorityIndices, $sampledMajorityIndices);
    shuffle($allIndices);

    $newSamples = [];
    $newLabels = [];
    foreach ($allIndices as $idx) {
        $newSamples[] = $samples[$idx];
        $newLabels[] = $labels[$idx];
    }

    return ['samples' => $newSamples, 'labels' => $newLabels];
}

/**
 * Calculate Euclidean distance between two vectors
 */
function euclideanDistance(array $a, array $b): float
{
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $diff = $a[$i] - $b[$i];
        $sum += $diff * $diff;
    }
    return sqrt($sum);
}

/**
 * Calculate confusion matrix components
 */
function calculateConfusionComponents(array $predictions, array $actuals, string $positiveClass): array
{
    $tp = $fp = $tn = $fn = 0;

    for ($i = 0; $i < count($predictions); $i++) {
        $predicted = $predictions[$i];
        $actual = $actuals[$i];

        if ($predicted === $positiveClass && $actual === $positiveClass) {
            $tp++;
        } elseif ($predicted === $positiveClass && $actual !== $positiveClass) {
            $fp++;
        } elseif ($predicted !== $positiveClass && $actual === $positiveClass) {
            $fn++;
        } else {
            $tn++;
        }
    }

    return ['tp' => $tp, 'fp' => $fp, 'tn' => $tn, 'fn' => $fn];
}

/**
 * Calculate precision
 */
function calculatePrecision(array $components): float
{
    $tp = $components['tp'];
    $fp = $components['fp'];

    if ($tp + $fp === 0) {
        return 0.0;
    }

    return $tp / ($tp + $fp);
}

/**
 * Calculate recall
 */
function calculateRecall(array $components): float
{
    $tp = $components['tp'];
    $fn = $components['fn'];

    if ($tp + $fn === 0) {
        return 0.0;
    }

    return $tp / ($tp + $fn);
}

/**
 * Calculate F1 score
 */
function calculateF1Score(float $precision, float $recall): float
{
    if ($precision + $recall === 0) {
        return 0.0;
    }

    return 2 * ($precision * $recall) / ($precision + $recall);
}

/**
 * Evaluate model on imbalanced data
 */
function evaluateImbalanced(array $predictions, array $actuals, string $positiveClass): void
{
    $components = calculateConfusionComponents($predictions, $actuals, $positiveClass);
    $precision = calculatePrecision($components);
    $recall = calculateRecall($components);
    $f1 = calculateF1Score($precision, $recall);
    $accuracy = ($components['tp'] + $components['tn']) / count($actuals);

    echo sprintf("Accuracy:  %5.1f%% (misleading for imbalanced data)\n", $accuracy * 100);
    echo sprintf("Precision: %5.1f%% (of predicted %s, how many are real)\n", $precision * 100, $positiveClass);
    echo sprintf("Recall:    %5.1f%% (of actual %s, how many we caught)\n", $recall * 100, $positiveClass);
    echo sprintf("F1-Score:  %5.1f%% (harmonic mean)\n", $f1 * 100);
}

// Generate severely imbalanced dataset
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║        Handling Severe Class Imbalance (1% spam)         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$samples = [];
$labels = [];

// Generate ham samples (99%)
for ($i = 0; $i < 99; $i++) {
    $samples[] = [
        rand(0, 3),         // special_chars
        rand(0, 1),         // exclamations
        rand(0, 5),         // caps_ratio
        rand(50, 150),      // word_count
        rand(0, 2)          // links
    ];
    $labels[] = 'ham';
}

// Generate spam samples (1%)
for ($i = 0; $i < 1; $i++) {
    $samples[] = [
        rand(10, 20),       // special_chars
        rand(5, 15),        // exclamations
        rand(20, 40),       // caps_ratio
        rand(80, 120),      // word_count
        rand(3, 8)          // links
    ];
    $labels[] = 'spam';
}

echo "Original Dataset: 99 ham, 1 spam (1% minority class)\n\n";

// Test set (also imbalanced)
$testSamples = [];
$testLabels = [];

for ($i = 0; $i < 18; $i++) {
    $testSamples[] = [rand(0, 3), rand(0, 1), rand(0, 5), rand(50, 150), rand(0, 2)];
    $testLabels[] = 'ham';
}

for ($i = 0; $i < 2; $i++) {
    $testSamples[] = [rand(10, 20), rand(5, 15), rand(20, 40), rand(80, 120), rand(3, 8)];
    $testLabels[] = 'spam';
}

// Baseline: No handling
echo "═══════════════════════════════════════════════════════════\n";
echo "BASELINE: No Imbalance Handling\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Training on imbalanced data...\n";

$baselineClassifier = new KNearestNeighbors(5, weighted: false, kernel: new Euclidean());
$baselineClassifier->train(new Labeled($samples, $labels));
echo "✓ Model trained\n\n";

$baselinePredictions = $baselineClassifier->predict(new Labeled($testSamples, $testLabels));
evaluateImbalanced($baselinePredictions, $testLabels, 'spam');

$baselineComponents = calculateConfusionComponents($baselinePredictions, $testLabels, 'spam');
if ($baselineComponents['tp'] === 0 && $baselineComponents['fn'] > 0) {
    echo "\n⚠️  Model predicts ALL samples as ham — completely useless!\n";
}

// Technique 1: Random Oversampling
echo "\n═══════════════════════════════════════════════════════════\n";
echo "TECHNIQUE 1: Random Oversampling\n";
echo "═══════════════════════════════════════════════════════════\n";

$oversampled = randomOversample($samples, $labels, 'spam');

$osClassifier = new KNearestNeighbors(5, weighted: false, kernel: new Euclidean());
$osClassifier->train(new Labeled($oversampled['samples'], $oversampled['labels']));

$osPredictions = $osClassifier->predict(new Labeled($testSamples, $testLabels));
evaluateImbalanced($osPredictions, $testLabels, 'spam');

echo "\n💡 Improved recall but may have overfitting (exact duplicates)\n";

// Technique 2: SMOTE
echo "\n═══════════════════════════════════════════════════════════\n";
echo "TECHNIQUE 2: SMOTE (Synthetic Oversampling)\n";
echo "═══════════════════════════════════════════════════════════\n";

$smoted = smote($samples, $labels, 'spam', k: 3, ratio: 1.0);

$smoteClassifier = new KNearestNeighbors(5, weighted: false, kernel: new Euclidean());
$smoteClassifier->train(new Labeled($smoted['samples'], $smoted['labels']));

$smotePredictions = $smoteClassifier->predict(new Labeled($testSamples, $testLabels));
evaluateImbalanced($smotePredictions, $testLabels, 'spam');

echo "\n💡 SMOTE provides better generalization than simple duplication\n";

// Technique 3: Undersampling
echo "\n═══════════════════════════════════════════════════════════\n";
echo "TECHNIQUE 3: Random Undersampling\n";
echo "═══════════════════════════════════════════════════════════\n";

$undersampled = randomUndersample($samples, $labels, 'ham', ratio: 1.0);

$usClassifier = new KNearestNeighbors(5, weighted: false, kernel: new Euclidean());
$usClassifier->train(new Labeled($undersampled['samples'], $undersampled['labels']));

$usPredictions = $usClassifier->predict(new Labeled($testSamples, $testLabels));
evaluateImbalanced($usPredictions, $testLabels, 'spam');

echo "\n💡 Works but loses information — only use if dataset is huge\n";

// Summary
echo "\n════════════════════════════════════════════════════════════\n";
echo "RECOMMENDATIONS\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "1. **First choice: SMOTE**\n";
echo "   • Creates realistic synthetic samples\n";
echo "   • Reduces overfitting vs. simple duplication\n";
echo "   • Works well for most imbalanced problems\n\n";

echo "2. **Second choice: Class Weights** (see 13-class-weights.php)\n";
echo "   • No dataset modification\n";
echo "   • Fast (no data augmentation)\n";
echo "   • Requires algorithm support\n\n";

echo "3. **When to use undersampling:**\n";
echo "   • Dataset is very large (>100k samples)\n";
echo "   • Training time is critical\n";
echo "   • Acceptable to discard data\n\n";

echo "4. **Avoid:**\n";
echo "   • Doing nothing (baseline)!\n";
echo "   • Trusting accuracy alone on imbalanced data\n\n";

echo "💡 For production: Try SMOTE + class weights together for best results!\n";
