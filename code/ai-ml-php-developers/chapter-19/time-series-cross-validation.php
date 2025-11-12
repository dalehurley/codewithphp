<?php

declare(strict_types=1);

/**
 * Time Series Cross-Validation
 *
 * Demonstrates proper validation for time series models
 * using rolling window approach.
 */

require_once __DIR__ . '/src/TimeSeriesDataLoader.php';
require_once __DIR__ . '/src/MovingAverageForecaster.php';
require_once __DIR__ . '/src/ForecastEvaluator.php';

use AiMlPhp\Chapter19\TimeSeriesDataLoader;
use AiMlPhp\Chapter19\MovingAverageForecaster;
use AiMlPhp\Chapter19\ForecastEvaluator;

function timeSeriesCrossValidate(
    array $data,
    int $minTrainSize,
    int $testSize = 1,
    string $strategy = 'expanding'  // or 'rolling'
): array {
    $results = [];
    $numSplits = count($data) - $minTrainSize - $testSize + 1;

    for ($i = 0; $i < $numSplits; $i++) {
        if ($strategy === 'expanding') {
            // Expanding window: use all data up to test point
            $trainEnd = $minTrainSize + $i;
            $trainData = array_slice($data, 0, $trainEnd);
        } else {
            // Rolling window: fixed-size training window
            $trainStart = max(0, $i);
            $trainEnd = $minTrainSize + $i;
            $trainData = array_slice($data, $trainStart, $trainEnd - $trainStart);
        }

        $testData = array_slice($data, $trainEnd, $testSize);

        $results[] = [
            'fold' => $i + 1,
            'train_size' => count($trainData),
            'test_indices' => [$trainEnd, $trainEnd + $testSize - 1],
            'train' => $trainData,
            'test' => $testData,
        ];
    }

    return $results;
}

// Load data
$loader = new TimeSeriesDataLoader();
$data = $loader->loadFromCsv(
    __DIR__ . '/data/sample_stock_prices.csv',
    dateColumn: 'date',
    valueColumn: 'close'
);

echo "=== Time Series Cross-Validation ===\n\n";

// Perform expanding window CV with 10 splits
$minTrainSize = 400;  // Start with 400 days
$testSize = 10;       // Test on next 10 days
$folds = timeSeriesCrossValidate($data, $minTrainSize, $testSize, 'expanding');

echo "Cross-Validation Setup:\n";
printf("- Strategy: Expanding Window\n");
printf("- Minimum training size: %d days\n", $minTrainSize);
printf("- Test size: %d days per fold\n", $testSize);
printf("- Number of folds: %d\n\n", count($folds));

// Evaluate model on each fold
$forecaster = new MovingAverageForecaster();
$evaluator = new ForecastEvaluator();
$cvScores = [];

foreach ($folds as $fold) {
    // Extract values only
    $trainValues = array_map(fn($row) => $row['value'], $fold['train']);
    $testValues = array_map(fn($row) => $row['value'], $fold['test']);

    // Generate predictions for test set
    $predictions = [];
    foreach ($testValues as $i => $actual) {
        // Use last 10 values from train (+ any test values we've seen)
        $recentValues = array_slice($trainValues, -10);
        if ($i > 0) {
            $recentValues = array_merge($recentValues, array_slice($predictions, max(0, $i - 10), $i));
        }
        $predictions[] = $forecaster->forecastSMA($recentValues, window: 10);
    }

    // Calculate metrics
    $mae = $evaluator->calculateMAE($testValues, $predictions);
    $rmse = $evaluator->calculateRMSE($testValues, $predictions);

    $cvScores[] = [
        'fold' => $fold['fold'],
        'train_size' => $fold['train_size'],
        'mae' => $mae,
        'rmse' => $rmse,
    ];

    printf(
        "Fold %2d | Train: %3d | MAE: $%.2f | RMSE: $%.2f\n",
        $fold['fold'],
        $fold['train_size'],
        $mae,
        $rmse
    );
}

// Aggregate results
$meanMAE = array_sum(array_column($cvScores, 'mae')) / count($cvScores);
$stdMAE = sqrt(
    array_sum(array_map(
        fn($score) => ($score['mae'] - $meanMAE) ** 2,
        $cvScores
    )) / count($cvScores)
);

$meanRMSE = array_sum(array_column($cvScores, 'rmse')) / count($cvScores);
$stdRMSE = sqrt(
    array_sum(array_map(
        fn($score) => ($score['rmse'] - $meanRMSE) ** 2,
        $cvScores
    )) / count($cvScores)
);

echo "\n" . str_repeat('=', 70) . "\n";
echo "Cross-Validation Results (Mean ± Std Dev)\n";
echo str_repeat('=', 70) . "\n";
printf("MAE:  $%.2f ± $%.2f\n", $meanMAE, $stdMAE);
printf("RMSE: $%.2f ± $%.2f\n", $meanRMSE, $stdRMSE);

// Compare to single train/test split
echo "\n\nComparison: CV vs. Single Train/Test Split\n";
echo str_repeat('-', 70) . "\n";

$trainData = array_slice($data, 0, 400);
$testData = array_slice($data, 400, 103);
$trainValues = array_map(fn($row) => $row['value'], $trainData);
$testValues = array_map(fn($row) => $row['value'], $testData);

$predictions = [];
for ($i = 0; $i < count($testValues); $i++) {
    $recentValues = array_slice($trainValues, -10);
    if ($i > 0) {
        $recentValues = array_merge($recentValues, array_slice($predictions, max(0, $i - 10), $i));
    }
    $predictions[] = $forecaster->forecastSMA($recentValues, window: 10);
}

$singleMAE = $evaluator->calculateMAE($testValues, $predictions);
$singleRMSE = $evaluator->calculateRMSE($testValues, $predictions);

printf("Single Split MAE:  $%.2f\n", $singleMAE);
printf("CV Average MAE:    $%.2f (±$%.2f)\n", $meanMAE, $stdMAE);

if (abs($singleMAE - $meanMAE) > $stdMAE * 2) {
    echo "\n⚠ Single split result is more than 2 std devs from CV mean\n";
    echo "  → Single split may not be representative\n";
    echo "  → Use CV for more reliable performance estimate\n";
} else {
    echo "\n✓ Single split is consistent with CV results\n";
    echo "  → Either approach is reasonable for this dataset\n";
}
