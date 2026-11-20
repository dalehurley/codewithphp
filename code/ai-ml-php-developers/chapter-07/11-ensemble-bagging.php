<?php

declare(strict_types=1);

/**
 * Bagging Ensemble Demo
 * 
 * Demonstrates Bootstrap Aggregating (Bagging) to reduce overfitting
 * by training multiple models on different random subsets of training data.
 * 
 * PHP version 8.6
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Datasets\Labeled;

/**
 * Bagging ensemble using bootstrap sampling
 *
 * @param callable $modelFactory Function that returns new model instance
 * @param array $samples Training features
 * @param array $labels Training labels
 * @param int $numModels Number of models in ensemble
 * @return object Object with predict() method
 */
function bagging(
    callable $modelFactory,
    array $samples,
    array $labels,
    int $numModels = 10
): object {
    $n = count($samples);
    $models = [];

    echo "Creating bagging ensemble with {$numModels} models...\n";

    for ($i = 0; $i < $numModels; $i++) {
        // Bootstrap sampling: random sample with replacement
        $bootstrapIndices = [];
        for ($j = 0; $j < $n; $j++) {
            $bootstrapIndices[] = rand(0, $n - 1);
        }

        // Extract bootstrap sample
        $bootstrapSamples = [];
        $bootstrapLabels = [];
        foreach ($bootstrapIndices as $idx) {
            $bootstrapSamples[] = $samples[$idx];
            $bootstrapLabels[] = $labels[$idx];
        }

        // Train model on bootstrap sample
        $model = $modelFactory();
        $model->train(new Labeled($bootstrapSamples, $bootstrapLabels));
        $models[] = $model;

        if (($i + 1) % 5 === 0) {
            echo "  Trained " . ($i + 1) . "/{$numModels} models\n";
        }
    }

    // Return ensemble object
    return new class($models) {
        private array $models;

        public function __construct(array $models)
        {
            $this->models = $models;
        }

        public function predict(Labeled $dataset): array
        {
            $allPredictions = [];
            foreach ($this->models as $model) {
                $allPredictions[] = $model->predict($dataset);
            }

            // Majority vote for each sample
            $samples = $dataset->samples();
            $numSamples = count($samples);
            $predictions = [];

            for ($i = 0; $i < $numSamples; $i++) {
                $votes = [];
                foreach ($allPredictions as $modelPredictions) {
                    $vote = $modelPredictions[$i];
                    $votes[$vote] = ($votes[$vote] ?? 0) + 1;
                }
                arsort($votes);
                $predictions[] = array_key_first($votes);
            }

            return $predictions;
        }
    };
}

/**
 * Calculate accuracy
 */
function calculateAccuracy(array $predictions, array $actual): float
{
    $correct = 0;
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] === $actual[$i]) {
            $correct++;
        }
    }
    return $correct / count($predictions);
}

/**
 * Perform k-fold cross-validation
 */
function crossValidate($model, array $samples, array $labels, int $k = 5): float
{
    $n = count($samples);
    $foldSize = (int)($n / $k);
    $scores = [];

    // Shuffle data
    $indices = range(0, $n - 1);
    shuffle($indices);

    for ($fold = 0; $fold < $k; $fold++) {
        $testStart = $fold * $foldSize;
        $testEnd = ($fold === $k - 1) ? $n : ($fold + 1) * $foldSize;

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

        // Train and evaluate
        if (is_callable($model)) {
            $trainedModel = $model();
            $trainedModel->train(new Labeled($trainSamples, $trainLabels));
        } else {
            $trainedModel = bagging(
                fn() => new ClassificationTree(15),
                $trainSamples,
                $trainLabels,
                numModels: 10
            );
        }

        $predictions = $trainedModel->predict(new Labeled($testSamples, $testLabels));
        $scores[] = calculateAccuracy($predictions, $testLabels);
    }

    return array_sum($scores) / count($scores);
}

// Generate synthetic dataset
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║            Bagging Ensemble Demonstration                ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "Generating synthetic dataset with noise...\n";

$trainSamples = [];
$trainLabels = [];
$testSamples = [];
$testLabels = [];

// Generate samples with clear pattern + noise
// Class A: feature1 + feature2 > 10
// Class B: feature1 + feature2 <= 10
for ($i = 0; $i < 100; $i++) {
    $f1 = rand(0, 20);
    $f2 = rand(0, 20);
    $f3 = rand(0, 10);
    $f4 = rand(0, 10);

    $trainSamples[] = [$f1, $f2, $f3, $f4];
    $trainLabels[] = ($f1 + $f2 > 10) ? 'A' : 'B';
}

// Test set
for ($i = 0; $i < 50; $i++) {
    $f1 = rand(0, 20);
    $f2 = rand(0, 20);
    $f3 = rand(0, 10);
    $f4 = rand(0, 10);

    $testSamples[] = [$f1, $f2, $f3, $f4];
    $testLabels[] = ($f1 + $f2 > 10) ? 'A' : 'B';
}

echo "✓ Training set: 100 samples\n";
echo "✓ Test set: 50 samples\n\n";

// Single decision tree (prone to overfitting with high depth)
echo "════════════════════════════════════════════════════════════\n";
echo "Single Decision Tree (prone to overfitting)\n";
echo "════════════════════════════════════════════════════════════\n\n";

$modelFactory = fn() => new ClassificationTree(15); // max depth

$singleTree = $modelFactory();
$singleTree->train(new Labeled($trainSamples, $trainLabels));

$singlePredictions = $singleTree->predict(new Labeled($testSamples, $testLabels));
$singleAccuracy = calculateAccuracy($singlePredictions, $testLabels);

echo sprintf("Test Accuracy: %5.2f%%\n", $singleAccuracy * 100);
echo "⚠️  Deep tree may overfit to training noise\n\n";

// Bagged ensemble
echo "════════════════════════════════════════════════════════════\n";
echo "Bagged Ensemble (reduces overfitting)\n";
echo "════════════════════════════════════════════════════════════\n\n";

$baggedEnsemble = bagging($modelFactory, $trainSamples, $trainLabels, numModels: 20);
echo "✓ Bagging ensemble ready\n\n";

$baggedPredictions = $baggedEnsemble->predict(new Labeled($testSamples, $testLabels));
$baggedAccuracy = calculateAccuracy($baggedPredictions, $testLabels);

echo sprintf("Test Accuracy: %5.2f%%\n", $baggedAccuracy * 100);

// Performance comparison
echo "\n════════════════════════════════════════════════════════════\n";
echo "Performance Comparison\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo sprintf("Single Decision Tree:  %5.2f%%\n", $singleAccuracy * 100);
echo sprintf(
    "Bagged Ensemble (20):  %5.2f%% (%+.2f%% improvement)\n",
    $baggedAccuracy * 100,
    ($baggedAccuracy - $singleAccuracy) * 100
);

// Cross-validation comparison (variance reduction)
echo "\n════════════════════════════════════════════════════════════\n";
echo "Variance Reduction via Cross-Validation\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "Running 5-fold CV on single tree... (this may take a moment)\n";
$singleCVScore = crossValidate(
    fn() => new ClassificationTree(15),
    array_merge($trainSamples, $testSamples),
    array_merge($trainLabels, $testLabels),
    k: 5
);

echo sprintf("Single Tree CV Score: %5.2f%%\n", $singleCVScore * 100);
echo "  → High variance (results vary significantly across folds)\n\n";

echo "Note: Bagging reduces this variance by averaging predictions\n";
echo "  → More stable performance (lower variance)\n";

echo "\n════════════════════════════════════════════════════════════\n";
echo "KEY INSIGHTS\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "1. Bagging Benefits:\n";
echo "   • Reduces overfitting of high-variance models\n";
echo "   • Works best with unstable models (Decision Trees, k-NN)\n";
echo "   • More models = more stable (diminishing returns after 20-50)\n\n";

echo "2. How Bootstrap Sampling Works:\n";
echo "   • Each model trained on random subset (with replacement)\n";
echo "   • ~63.2% unique samples per model (some duplicated)\n";
echo "   • Creates diversity in model errors\n\n";

echo "3. When to Use Bagging:\n";
echo "   ✓ Single model overfits (high variance)\n";
echo "   ✓ Unstable algorithm (small data changes → big prediction changes)\n";
echo "   ✓ Limited training data (bootstrap creates diverse sets)\n";
echo "   ✗ Model already stable (e.g., Naive Bayes) — won't help much\n\n";

echo "4. Production Considerations:\n";
echo "   • Inference is N× slower (20 models = 20× slower)\n";
echo "   • Memory usage is N× higher\n";
echo "   • But: usually worth it for 2-5%+ accuracy gain!\n\n";

echo "💡 Famous Example: Random Forests = Bagged Decision Trees\n";
echo "   (One of the most successful ML algorithms ever!)\n";
