<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\ML\SimpleClassifier;
use DataScience\ML\SimpleRegressor;

echo "=== Supervised Learning Examples ===\n\n";

// 1. Classification: Iris flowers
echo "1. Classification (K-Nearest Neighbors):\n";
echo "   Classifying iris flowers based on petal measurements\n\n";

$classifier = new SimpleClassifier();

// Training data: [petal_length, petal_width] => species
$trainingData = [
    [1.4, 0.2], [1.4, 0.2], [1.3, 0.2], [1.5, 0.2], // setosa
    [4.7, 1.4], [4.5, 1.5], [4.9, 1.5], [4.0, 1.3], // versicolor
    [6.0, 2.5], [5.1, 1.9], [5.9, 2.1], [5.6, 1.8], // virginica
];

$trainingLabels = [
    'setosa', 'setosa', 'setosa', 'setosa',
    'versicolor', 'versicolor', 'versicolor', 'versicolor',
    'virginica', 'virginica', 'virginica', 'virginica',
];

$classifier->train($trainingData, $trainingLabels);

// Test predictions
$testCases = [
    [1.5, 0.3],  // Should be setosa
    [4.5, 1.4],  // Should be versicolor
    [5.8, 2.2],  // Should be virginica
];

foreach ($testCases as $i => $testPoint) {
    $result = $classifier->predictWithProbability($testPoint, k: 3);

    echo "   Test " . ($i + 1) . ": [" . implode(', ', $testPoint) . "]\n";
    echo "     Prediction: {$result['prediction']}\n";
    echo "     Confidence:\n";

    foreach ($result['probabilities'] as $species => $prob) {
        $percentage = round($prob * 100, 1);
        $bar = str_repeat('█', (int)($percentage / 5));
        echo "       {$species}: {$bar} {$percentage}%\n";
    }
    echo "\n";
}

// Evaluate on test set
$testData = [
    [1.3, 0.2], [4.6, 1.3], [5.7, 2.3],
];
$testLabels = ['setosa', 'versicolor', 'virginica'];

$evaluation = $classifier->evaluate($testData, $testLabels);
echo "   Model Accuracy: " . round($evaluation['accuracy'] * 100, 1) . "%\n";
echo "   Correct: {$evaluation['correct']} / {$evaluation['total']}\n\n";

// 2. Regression: House prices
echo "2. Regression (Linear Regression):\n";
echo "   Predicting house prices based on size\n\n";

$regressor = new SimpleRegressor();

// Training data: house size (sqft) => price ($1000s)
$sizes = [1000, 1500, 2000, 2500, 3000, 3500, 4000];
$prices = [150, 200, 250, 300, 350, 400, 450];

$regressor->train($sizes, $prices);

$params = $regressor->getParameters();
echo "   Model: {$params['equation']}\n";
echo "   Slope: $" . number_format($params['slope'] * 1000, 2) . " per sqft\n";
echo "   Intercept: $" . number_format($params['intercept'] * 1000, 2) . "\n\n";

// Make predictions
$testSizes = [1200, 2200, 3800];

echo "   Predictions:\n";
foreach ($testSizes as $size) {
    $predicted = $regressor->predict($size);
    echo "     {$size} sqft → $" . number_format($predicted, 1) . "k\n";
}

echo "\n";

// Model quality
$rSquared = $regressor->rSquared($sizes, $prices);
$mae = $regressor->meanAbsoluteError($sizes, $prices);

echo "   Model Quality:\n";
echo "     R² Score: " . round($rSquared, 4) . " (1.0 = perfect fit)\n";
echo "     Mean Absolute Error: $" . number_format($mae, 2) . "k\n\n";

echo "✓ Supervised learning examples complete!\n";
