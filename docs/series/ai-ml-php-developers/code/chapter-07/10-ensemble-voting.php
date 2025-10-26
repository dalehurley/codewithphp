<?php

declare(strict_types=1);

/**
 * Ensemble Voting Classifier Demo
 * 
 * Demonstrates combining multiple diverse classifiers using hard and soft voting
 * to achieve better accuracy than any single model.
 * 
 * PHP version 8.4
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Classifiers\GaussianNB;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Kernels\Distance\Euclidean;

/**
 * Voting classifier combining multiple models
 *
 * @param array $models Array of trained classifier objects
 * @param array $testSamples Test feature data
 * @param string $method 'hard' for majority vote, 'soft' for probability averaging
 * @return array Predicted labels
 */
function votingClassifier(
    array $models,
    array $testSamples,
    string $method = 'hard'
): array {
    $numSamples = count($testSamples);
    $predictions = [];

    if ($method === 'hard') {
        // Each model makes predictions
        $allPredictions = [];
        foreach ($models as $model) {
            $dataset = new Labeled($testSamples, array_fill(0, $numSamples, 'placeholder'));
            $allPredictions[] = $model->predict($dataset);
        }

        // Majority vote for each sample
        for ($i = 0; $i < $numSamples; $i++) {
            $votes = [];
            foreach ($allPredictions as $modelPredictions) {
                $vote = $modelPredictions[$i];
                $votes[$vote] = ($votes[$vote] ?? 0) + 1;
            }

            // Get class with most votes
            arsort($votes);
            $predictions[] = array_key_first($votes);
        }
    } else {
        // Soft voting: average probabilities
        $allProbabilities = [];
        foreach ($models as $model) {
            $dataset = new Labeled($testSamples, array_fill(0, $numSamples, 'placeholder'));
            $allProbabilities[] = $model->proba($dataset);
        }

        for ($i = 0; $i < $numSamples; $i++) {
            $avgProbabilities = [];

            // Average probabilities across models
            foreach ($allProbabilities as $modelProbas) {
                foreach ($modelProbas[$i] as $class => $proba) {
                    $avgProbabilities[$class] = ($avgProbabilities[$class] ?? 0) + $proba;
                }
            }

            // Divide by number of models
            foreach ($avgProbabilities as $class => $sum) {
                $avgProbabilities[$class] = $sum / count($models);
            }

            // Predict class with highest average probability
            arsort($avgProbabilities);
            $predictions[] = array_key_first($avgProbabilities);
        }
    }

    return $predictions;
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

// Generate synthetic spam/ham dataset
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║              Ensemble Methods Comparison                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "Generating synthetic email dataset...\n";

// Create synthetic features (word counts, special chars, etc.)
$trainSamples = [];
$trainLabels = [];
$testSamples = [];
$testLabels = [];

// Generate spam samples (high special chars, certain keywords)
for ($i = 0; $i < 13; $i++) {
    $trainSamples[] = [
        rand(5, 20),        // special_char_count
        rand(20, 50),       // exclamation_count
        rand(10, 30),       // caps_ratio
        rand(80, 100),      // word_count
        rand(0, 5)          // link_count
    ];
    $trainLabels[] = 'spam';
}

// Generate ham samples (lower special chars, normal text)
for ($i = 0; $i < 87; $i++) {
    $trainSamples[] = [
        rand(0, 5),         // special_char_count
        rand(0, 2),         // exclamation_count
        rand(0, 10),        // caps_ratio
        rand(50, 150),      // word_count
        rand(0, 3)          // link_count
    ];
    $trainLabels[] = 'ham';
}

// Test set
for ($i = 0; $i < 3; $i++) {
    $testSamples[] = [rand(5, 20), rand(20, 50), rand(10, 30), rand(80, 100), rand(0, 5)];
    $testLabels[] = 'spam';
}

for ($i = 0; $i < 17; $i++) {
    $testSamples[] = [rand(0, 5), rand(0, 2), rand(0, 10), rand(50, 150), rand(0, 3)];
    $testLabels[] = 'ham';
}

echo "✓ Dataset created: 100 training samples (13 spam, 87 ham)\n";
echo "✓ Test set: 20 samples (3 spam, 17 ham)\n\n";

// Train individual models
echo "Training individual models...\n";
echo "─────────────────────────────────────────────────\n";

$knn = new KNearestNeighbors(5, weighted: false, kernel: new Euclidean());
$knn->train(new Labeled($trainSamples, $trainLabels));
echo "✓ k-NN trained (k=5)\n";

$nb = new GaussianNB();
$nb->train(new Labeled($trainSamples, $trainLabels));
echo "✓ Naive Bayes trained\n";

$tree = new ClassificationTree(10); // max depth
$tree->train(new Labeled($trainSamples, $trainLabels));
echo "✓ Decision Tree trained (max_depth=10)\n";

$models = [$knn, $nb, $tree];

// Test individual models
echo "\nIndividual Model Performance:\n";
echo "─────────────────────────────────────────────────\n";

$testDataset = new Labeled($testSamples, $testLabels);

$knnPredictions = $knn->predict($testDataset);
$knnAccuracy = calculateAccuracy($knnPredictions, $testLabels);
echo sprintf("k-NN            : %5.2f%%\n", $knnAccuracy * 100);

$nbPredictions = $nb->predict($testDataset);
$nbAccuracy = calculateAccuracy($nbPredictions, $testLabels);
echo sprintf("Naive Bayes     : %5.2f%%\n", $nbAccuracy * 100);

$treePredictions = $tree->predict($testDataset);
$treeAccuracy = calculateAccuracy($treePredictions, $testLabels);
echo sprintf("Decision Tree   : %5.2f%%\n", $treeAccuracy * 100);

// Test ensemble
echo "\nEnsemble Performance:\n";
echo "─────────────────────────────────────────────────\n";

$hardVotePreds = votingClassifier($models, $testSamples, 'hard');
$hardVoteAccuracy = calculateAccuracy($hardVotePreds, $testLabels);
$hardImprovement = ($hardVoteAccuracy - max($knnAccuracy, $nbAccuracy, $treeAccuracy)) * 100;
echo sprintf("Hard Voting     : %5.2f%% (%+.1f%% improvement)\n", $hardVoteAccuracy * 100, $hardImprovement);

$softVotePreds = votingClassifier($models, $testSamples, 'soft');
$softVoteAccuracy = calculateAccuracy($softVotePreds, $testLabels);
$softImprovement = ($softVoteAccuracy - max($knnAccuracy, $nbAccuracy, $treeAccuracy)) * 100;
echo sprintf("Soft Voting     : %5.2f%% (%+.1f%% improvement)\n", $softVoteAccuracy * 100, $softImprovement);

echo "\n";
if ($softVoteAccuracy > max($knnAccuracy, $nbAccuracy, $treeAccuracy)) {
    echo "✓ Ensemble outperforms all individual models!\n";
} else {
    echo "⚠️  Ensemble performance similar to best individual model\n";
    echo "   (This can happen with small datasets or correlated errors)\n";
}

echo "\n════════════════════════════════════════════════════════════\n";
echo "KEY INSIGHTS\n";
echo "════════════════════════════════════════════════════════════\n\n";

echo "1. Ensemble Voting:\n";
echo "   • Combines diverse algorithms (k-NN, Naive Bayes, Tree)\n";
echo "   • Soft voting > Hard voting (uses probability info)\n";
echo "   • Works best when base models disagree on errors\n\n";

echo "2. Why It Works:\n";
echo "   • k-NN: Good with local patterns\n";
echo "   • Naive Bayes: Fast, works well with probabilities\n";
echo "   • Decision Tree: Captures non-linear boundaries\n";
echo "   • Ensemble: Leverages strengths of all three!\n\n";

echo "3. Trade-offs:\n";
echo "   ✓ Better accuracy (typically 2-5% improvement)\n";
echo "   ✗ Slower inference (3× slower in this case)\n";
echo "   ✗ Less interpretable (which model decided?)\n\n";

echo "💡 Use ensembles when accuracy gain justifies added complexity\n";
