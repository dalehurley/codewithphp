<?php

declare(strict_types=1);

/**
 * Example 11: Regression vs. Classification
 * 
 * Demonstrates regression (predicting continuous values) contrasted with
 * classification (predicting discrete categories).
 * 
 * Uses house price prediction as a practical regression example.
 */

require __DIR__ . '/../../chapter-02/vendor/autoload.php';

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Regressors\Ridge;
use Rubix\ML\CrossValidation\Metrics\MeanSquaredError;
use Rubix\ML\CrossValidation\Metrics\RSquared;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║       Regression vs. Classification Demonstration        ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ============================================================
// UNDERSTANDING THE DIFFERENCE
// ============================================================

echo "============================================================\n";
echo "REGRESSION vs. CLASSIFICATION\n";
echo "============================================================\n\n";

echo "CLASSIFICATION (discrete outputs):\n";
echo "  - Predicts categories/labels\n";
echo "  - Examples: spam/ham, setosa/versicolor/virginica\n";
echo "  - Output: One of N fixed classes\n";
echo "  - Evaluation: Accuracy, precision, recall\n\n";

echo "REGRESSION (continuous outputs):\n";
echo "  - Predicts numeric values\n";
echo "  - Examples: house prices, temperature, age\n";
echo "  - Output: Any number in a range\n";
echo "  - Evaluation: Mean Squared Error (MSE), R-squared\n\n";

// ============================================================
// REGRESSION EXAMPLE: House Price Prediction
// ============================================================

echo "============================================================\n";
echo "PROJECT: House Price Prediction (Regression)\n";
echo "============================================================\n\n";

echo "Goal: Predict house prices based on features\n";
echo "  Input: [square_feet, num_bedrooms, num_bathrooms, age_years]\n";
echo "  Output: Price in dollars (continuous value)\n\n";

// Generate synthetic house data
// In reality, you'd load this from a database or CSV
$houseSamples = [
    // [sqft, bedrooms, bathrooms, age]
    [1200, 2, 1, 15],
    [1500, 3, 2, 10],
    [1800, 3, 2, 5],
    [2000, 4, 2, 8],
    [2200, 4, 3, 3],
    [1100, 2, 1, 20],
    [1400, 3, 1, 12],
    [1600, 3, 2, 7],
    [1900, 4, 2, 4],
    [2100, 4, 3, 2],
    [1300, 2, 1, 18],
    [1700, 3, 2, 9],
    [2300, 4, 3, 1],
    [1250, 2, 1, 16],
    [1550, 3, 2, 11],
    [1850, 3, 2, 6],
    [2050, 4, 3, 5],
    [1450, 3, 2, 13],
    [1950, 4, 2, 7],
    [2250, 4, 3, 2],
];

// Prices in thousands (for readability)
$housePrices = [
    180,
    235,
    290,
    320,
    380,
    165,
    215,
    260,
    310,
    365,
    190,
    275,
    410,
    185,
    245,
    295,
    350,
    225,
    315,
    395,
];

echo "Dataset: " . count($houseSamples) . " houses\n";
echo "Features: square feet, bedrooms, bathrooms, age\n";
echo "Target: price (in thousands)\n\n";

echo "Sample data:\n";
for ($i = 0; $i < 3; $i++) {
    echo "  House " . ($i + 1) . ": " . $houseSamples[$i][0] . " sqft, ";
    echo $houseSamples[$i][1] . " bed, " . $houseSamples[$i][2] . " bath, ";
    echo $houseSamples[$i][3] . " yrs → \$" . $housePrices[$i] . "k\n";
}
echo "  ...\n\n";

// ============================================================
// STEP 1: Split Data
// ============================================================

echo "============================================================\n";
echo "STEP 1: Split Data (80% train, 20% test)\n";
echo "============================================================\n\n";

$indices = range(0, count($houseSamples) - 1);
shuffle($indices);

$trainSize = (int) round(count($houseSamples) * 0.8);

$trainSamples = [];
$trainPrices = [];
$testSamples = [];
$testPrices = [];

for ($i = 0; $i < count($indices); $i++) {
    $idx = $indices[$i];

    if ($i < $trainSize) {
        $trainSamples[] = $houseSamples[$idx];
        $trainPrices[] = $housePrices[$idx];
    } else {
        $testSamples[] = $houseSamples[$idx];
        $testPrices[] = $housePrices[$idx];
    }
}

echo "Training set: " . count($trainSamples) . " houses\n";
echo "Test set: " . count($testSamples) . " houses\n\n";

// ============================================================
// STEP 2: Train Regression Model
// ============================================================

echo "============================================================\n";
echo "STEP 2: Train Regression Model (Ridge Regression)\n";
echo "============================================================\n\n";

echo "Ridge Regression: Linear model with L2 regularization\n";
echo "  Finds relationship: price = w1*sqft + w2*beds + w3*baths + w4*age + b\n\n";

$trainingDataset = new Labeled($trainSamples, $trainPrices);
$regressor = new Ridge(alpha: 1.0);

$trainStart = microtime(true);
$regressor->train($trainingDataset);
$trainTime = microtime(true) - $trainStart;

echo "✓ Model trained in " . number_format($trainTime * 1000, 2) . " ms\n\n";

// ============================================================
// STEP 3: Make Predictions
// ============================================================

echo "============================================================\n";
echo "STEP 3: Make Predictions on Test Set\n";
echo "============================================================\n\n";

$testDataset = new Labeled($testSamples, $testPrices);
$predictions = $regressor->predict($testDataset);

echo "Sample predictions (prices in \$1000s):\n";
echo str_repeat('-', 60) . "\n";
echo "House  | Features                    | Actual | Predicted | Error\n";
echo str_repeat('-', 60) . "\n";

$errors = [];

for ($i = 0; $i < count($testSamples); $i++) {
    $actual = $testPrices[$i];
    $predicted = $predictions[$i];
    $error = abs($actual - $predicted);
    $errors[] = $error;

    $features = $testSamples[$i];
    echo str_pad((string) ($i + 1), 6) . " | ";
    echo $features[0] . " sqft, " . $features[1] . " bed, " . $features[2] . " bath, " . $features[3] . " yrs";
    echo str_pad("", 2) . " | ";
    echo str_pad("\$" . number_format($actual, 0) . "k", 6) . " | ";
    echo str_pad("\$" . number_format($predicted, 0) . "k", 9) . " | ";
    echo "\$" . number_format($error, 0) . "k\n";
}

echo str_repeat('-', 60) . "\n\n";

// ============================================================
// STEP 4: Evaluate Performance
// ============================================================

echo "============================================================\n";
echo "STEP 4: Evaluate Regression Performance\n";
echo "============================================================\n\n";

// Mean Squared Error
$mseMetric = new MeanSquaredError();
$mse = $mseMetric->score($predictions, $testPrices);

// Root Mean Squared Error (more interpretable - same units as target)
$rmse = sqrt($mse);

// R-squared (coefficient of determination)
$r2Metric = new RSquared();
$r2 = $r2Metric->score($predictions, $testPrices);

// Mean Absolute Error (average prediction error)
$mae = array_sum($errors) / count($errors);

echo "Performance Metrics:\n\n";

echo "1. Mean Absolute Error (MAE): \$" . number_format($mae, 2) . "k\n";
echo "   → Average prediction is off by \$" . number_format($mae * 1000, 0) . "\n";
echo "   → Lower is better (0 = perfect predictions)\n\n";

echo "2. Root Mean Squared Error (RMSE): \$" . number_format($rmse, 2) . "k\n";
echo "   → Like MAE but penalizes large errors more heavily\n";
echo "   → Lower is better (0 = perfect predictions)\n\n";

echo "3. R-squared (R²): " . number_format($r2, 4) . "\n";
echo "   → Proportion of variance explained by the model\n";
echo "   → Range: 0 to 1, where 1 = perfect fit\n";
echo "   → Interpretation: Model explains " . number_format($r2 * 100, 1) . "% of price variance\n\n";

if ($r2 > 0.8) {
    echo "✓ Excellent model! R² > 0.8\n";
} elseif ($r2 > 0.6) {
    echo "✓ Good model! R² > 0.6\n";
} elseif ($r2 > 0.4) {
    echo "⚠️  Moderate model. R² > 0.4 but could be better.\n";
} else {
    echo "❌ Poor model. R² < 0.4, needs improvement.\n";
}

echo "\n";

// ============================================================
// STEP 5: Make a New Prediction
// ============================================================

echo "============================================================\n";
echo "STEP 5: Predict Price for a New House\n";
echo "============================================================\n\n";

$newHouse = [[1750, 3, 2, 8]];  // 1750 sqft, 3 bed, 2 bath, 8 years old

echo "New house features:\n";
echo "  Square feet: 1750\n";
echo "  Bedrooms: 3\n";
echo "  Bathrooms: 2\n";
echo "  Age: 8 years\n\n";

$predictedPrice = $regressor->predictSample($newHouse[0]);

echo "→ Predicted price: \$" . number_format($predictedPrice, 2) . "k";
echo " (\$" . number_format($predictedPrice * 1000, 0) . ")\n\n";

echo "Confidence interval (±RMSE): \$" . number_format($predictedPrice - $rmse, 0) . "k to \$" . number_format($predictedPrice + $rmse, 0) . "k\n\n";

// ============================================================
// COMPARISON: Regression vs. Classification
// ============================================================

echo "============================================================\n";
echo "KEY DIFFERENCES SUMMARY\n";
echo "============================================================\n\n";

echo "┌─────────────────┬──────────────────────────┬──────────────────────────┐\n";
echo "│ Aspect          │ CLASSIFICATION           │ REGRESSION               │\n";
echo "├─────────────────┼──────────────────────────┼──────────────────────────┤\n";
echo "│ Output Type     │ Discrete categories      │ Continuous numbers       │\n";
echo "│ Example Output  │ 'spam' or 'setosa'       │ 287.5 or 42.3           │\n";
echo "│ Algorithms      │ k-NN, Naive Bayes, SVM   │ Linear, Ridge, Lasso    │\n";
echo "│ Evaluation      │ Accuracy, F1-score       │ MSE, RMSE, R-squared    │\n";
echo "│ Use Cases       │ Spam detection, species  │ Price prediction, temps │\n";
echo "└─────────────────┴──────────────────────────┴──────────────────────────┘\n\n";

echo "Both are supervised learning (require labeled training data)!\n\n";

echo "When to use each:\n";
echo "  CLASSIFICATION: \"What category does this belong to?\"\n";
echo "    - Email spam detection\n";
echo "    - Customer churn prediction (yes/no)\n";
echo "    - Image recognition (cat/dog/bird)\n\n";

echo "  REGRESSION: \"What numeric value should this have?\"\n";
echo "    - House price prediction\n";
echo "    - Temperature forecasting\n";
echo "    - Sales projections\n\n";

echo "🎉 Regression example complete!\n";
echo "   You now understand both major types of supervised learning.\n";
