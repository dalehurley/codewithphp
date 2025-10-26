<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Regressors\RandomForestRegressor;
use Rubix\ML\CrossValidation\Metrics\RSquared;
use Rubix\ML\CrossValidation\Metrics\MeanAbsoluteError;
use Rubix\ML\CrossValidation\Metrics\MeanSquaredError;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║       House Price Prediction with Regression            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ============================================================
// STEP 1: Prepare House Price Dataset
// ============================================================

echo "STEP 1: Preparing House Price Dataset\n";
echo "------------------------------------------------------------\n";

// Features: [sqft, bedrooms, age_years, location_score (1-10)]
$samples = [
    [1200, 3, 10, 7],
    [1800, 4, 5, 8],
    [900, 2, 20, 5],
    [2500, 5, 2, 9],
    [1100, 2, 15, 6],
    [2200, 4, 8, 8],
    [1500, 3, 12, 7],
    [1900, 4, 6, 7],
    [850, 2, 25, 4],
    [2800, 5, 1, 10],
    [1400, 3, 10, 6],
    [2000, 4, 7, 8],
    [1300, 3, 14, 6],
    [2400, 4, 3, 9],
    [1600, 3, 9, 7],
    [1750, 4, 11, 7],
    [2100, 4, 5, 8],
    [950, 2, 18, 5],
    [2300, 5, 4, 9],
    [1450, 3, 13, 6],
];

// Target: House prices in dollars
$prices = [
    250000,
    380000,
    180000,
    520000,
    220000,
    440000,
    285000,
    360000,
    165000,
    580000,
    265000,
    395000,
    255000,
    480000,
    305000,
    340000,
    410000,
    195000,
    490000,
    275000,
];

echo "Dataset prepared:\n";
echo "  Samples: " . count($samples) . " houses\n";
echo "  Features: 4 (sqft, bedrooms, age, location_score)\n";
echo "  Target: Price (\$)\n\n";

echo "Sample data:\n";
for ($i = 0; $i < 3; $i++) {
    echo sprintf(
        "  House %d: %d sqft, %d bed, %d yrs, loc:%d → \$%s\n",
        $i + 1,
        $samples[$i][0],
        $samples[$i][1],
        $samples[$i][2],
        $samples[$i][3],
        number_format($prices[$i], 0)
    );
}
echo "\n";

// ============================================================
// STEP 2: Train Random Forest Regressor
// ============================================================

echo "STEP 2: Training Random Forest Regressor\n";
echo "------------------------------------------------------------\n";

// Create dataset
$dataset = new Labeled($samples, $prices);

// Split into training and testing sets (80/20)
[$training, $testing] = $dataset->randomize()->split(0.8);

echo "Data split:\n";
echo "  Training: " . $training->numSamples() . " samples\n";
echo "  Testing: " . $testing->numSamples() . " samples\n\n";

// Create and train regressor
$regressor = new RandomForestRegressor(
    estimators: 100,        // 100 decision trees
    minLeafSize: 2,
);

echo "Training Random Forest Regressor (100 trees)...\n";
$trainStart = microtime(true);
$regressor->train($training);
$trainTime = (microtime(true) - $trainStart) * 1000;

echo "✓ Model trained in " . number_format($trainTime, 2) . " ms\n\n";

// ============================================================
// STEP 3: Evaluate Model Performance
// ============================================================

echo "STEP 3: Evaluating Model Performance\n";
echo "------------------------------------------------------------\n";

// Make predictions on test set
$predictions = $regressor->predict($testing);
$actuals = $testing->labels();

// Calculate evaluation metrics
$r2Metric = new RSquared();
$maeMetric = new MeanAbsoluteError();
$mseMetric = new MeanSquaredError();

$r2 = $r2Metric->score($predictions, $actuals);
$mae = $maeMetric->score($predictions, $actuals);
$rmse = sqrt($mseMetric->score($predictions, $actuals));

echo "Test Set Performance:\n";
echo "  R² Score: " . number_format($r2, 3) . " (explains " . number_format($r2 * 100, 1) . "% of variance)\n";
echo "  RMSE: \$" . number_format($rmse, 0) . " (avg prediction error)\n";
echo "  MAE: \$" . number_format($mae, 0) . " (avg absolute error)\n\n";

// Show individual predictions
echo "Sample Predictions vs Actuals:\n";
for ($i = 0; $i < min(5, count($predictions)); $i++) {
    $error = abs($predictions[$i] - $actuals[$i]);
    $errorPct = ($error / $actuals[$i]) * 100;

    echo sprintf(
        "  Predicted: \$%s | Actual: \$%s | Error: \$%s (%.1f%%)\n",
        number_format($predictions[$i], 0),
        number_format($actuals[$i], 0),
        number_format($error, 0),
        $errorPct
    );
}
echo "\n";

// ============================================================
// STEP 4: Analyze Feature Importance
// ============================================================

echo "STEP 4: Feature Importance Analysis\n";
echo "------------------------------------------------------------\n";

// Get feature importances from Random Forest
$importances = $regressor->featureImportances();

$featureNames = [
    'Square Feet',
    'Bedrooms',
    'Age (years)',
    'Location Score',
];

// Sort features by importance
$featureData = array_map(
    fn($name, $importance) => ['name' => $name, 'importance' => $importance],
    $featureNames,
    $importances
);

usort($featureData, fn($a, $b) => $b['importance'] <=> $a['importance']);

echo "Feature Importance (how much each feature drives predictions):\n\n";

foreach ($featureData as $i => $feature) {
    $percentage = $feature['importance'] * 100;
    $barLength = (int)($percentage / 2.5); // Scale to reasonable bar length
    $bar = str_repeat('█', $barLength);

    echo sprintf(
        "  %d. %-15s: %5.1f%%  %s\n",
        $i + 1,
        $feature['name'],
        $percentage,
        $bar
    );
}

echo "\n";

echo "Key Insights:\n";
echo "  • " . $featureData[0]['name'] . " is the strongest predictor\n";
echo "  • " . $featureData[1]['name'] . " also significantly impacts price\n";
echo "  • " . $featureData[3]['name'] . " has minimal influence\n\n";

// ============================================================
// STEP 5: Make Predictions on New Houses
// ============================================================

echo "STEP 5: Predicting Prices for New Houses\n";
echo "------------------------------------------------------------\n";

$newHouses = [
    [1500, 3, 8, 7],     // Mid-size, 3 bed, 8 years old, good location
    [2200, 4, 3, 9],     // Large, 4 bed, newer, excellent location
    [1000, 2, 15, 5],    // Small, 2 bed, older, average location
];

$newHouseDescriptions = [
    '1500 sqft, 3 bed, 8 years, location: 7/10',
    '2200 sqft, 4 bed, 3 years, location: 9/10',
    '1000 sqft, 2 bed, 15 years, location: 5/10',
];

$newDataset = new Unlabeled($newHouses);
$newPredictions = $regressor->predict($newDataset);

echo "Predicted prices for new houses:\n\n";

foreach ($newPredictions as $i => $price) {
    echo sprintf(
        "  House %d (%s)\n",
        $i + 1,
        $newHouseDescriptions[$i]
    );
    echo sprintf("  Predicted Price: \$%s\n\n", number_format($price, 0));
}

// ============================================================
// STEP 6: Compare Multiple Regression Algorithms (Optional)
// ============================================================

echo "STEP 6: Comparing Regression Algorithms\n";
echo "------------------------------------------------------------\n";

use Rubix\ML\Regressors\KNNRegressor;
use Rubix\ML\Regressors\Ridge;

$algorithms = [
    'Random Forest' => new RandomForestRegressor(estimators: 50),
    'k-NN Regressor' => new KNNRegressor(k: 3),
    'Ridge Regression' => new Ridge(alpha: 1.0),
];

echo "Training and evaluating 3 regression algorithms...\n\n";

$results = [];

foreach ($algorithms as $name => $algo) {
    $startTime = microtime(true);
    $algo->train($training);
    $trainDuration = (microtime(true) - $startTime) * 1000;

    $preds = $algo->predict($testing);
    $r2Score = $r2Metric->score($preds, $actuals);
    $maeScore = $maeMetric->score($preds, $actuals);

    $results[] = [
        'name' => $name,
        'r2' => $r2Score,
        'mae' => $maeScore,
        'train_time' => $trainDuration,
    ];
}

// Sort by R² (descending)
usort($results, fn($a, $b) => $b['r2'] <=> $a['r2']);

echo "Algorithm Comparison (sorted by R² score):\n";
echo "┌─────────────────────┬──────────┬────────────┬─────────────┐\n";
echo "│ Algorithm           │ R² Score │ MAE (\$)    │ Train (ms)  │\n";
echo "├─────────────────────┼──────────┼────────────┼─────────────┤\n";

foreach ($results as $result) {
    echo sprintf(
        "│ %-19s │ %8.3f │ %10s │ %11s │\n",
        $result['name'],
        $result['r2'],
        number_format($result['mae'], 0),
        number_format($result['train_time'], 2)
    );
}

echo "└─────────────────────┴──────────┴────────────┴─────────────┘\n\n";

echo "Best Algorithm: " . $results[0]['name'] . " (R² = " . number_format($results[0]['r2'], 3) . ")\n\n";

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║      ✓ Regression & Feature Importance Complete!       ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
