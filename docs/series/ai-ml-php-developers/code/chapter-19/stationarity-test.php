<?php

declare(strict_types=1);

/**
 * Test time series for stationarity using rolling statistics.
 *
 * A stationary series should have constant mean and variance over time.
 */

function calculateRollingStats(array $data, int $windowSize): array
{
    $rollingMeans = [];
    $rollingStds = [];

    for ($i = $windowSize - 1; $i < count($data); $i++) {
        $window = array_slice($data, $i - $windowSize + 1, $windowSize);

        $mean = array_sum($window) / $windowSize;
        $variance = 0;
        foreach ($window as $value) {
            $variance += ($value - $mean) ** 2;
        }
        $std = sqrt($variance / $windowSize);

        $rollingMeans[] = $mean;
        $rollingStds[] = $std;
    }

    return [$rollingMeans, $rollingStds];
}

function testStationarity(array $data, int $windowSize = 50): array
{
    [$rollingMeans, $rollingStds] = calculateRollingStats($data, $windowSize);

    // Calculate coefficient of variation for mean and std
    $meanOfMeans = array_sum($rollingMeans) / count($rollingMeans);
    $meanOfStds = array_sum($rollingStds) / count($rollingStds);

    // Calculate variance of the rolling statistics
    $varianceOfMeans = 0;
    foreach ($rollingMeans as $mean) {
        $varianceOfMeans += ($mean - $meanOfMeans) ** 2;
    }
    $varianceOfMeans /= count($rollingMeans);
    $stdOfMeans = sqrt($varianceOfMeans);

    $varianceOfStds = 0;
    foreach ($rollingStds as $std) {
        $varianceOfStds += ($std - $meanOfStds) ** 2;
    }
    $varianceOfStds /= count($rollingStds);
    $stdOfStds = sqrt($varianceOfStds);

    // Coefficient of variation: lower is more stationary
    $cvMean = $meanOfMeans != 0 ? ($stdOfMeans / $meanOfMeans) * 100 : 0;
    $cvStd = $meanOfStds != 0 ? ($stdOfStds / $meanOfStds) * 100 : 0;

    // Simple heuristic: if CV < 5%, likely stationary
    $isStationary = ($cvMean < 5.0 && $cvStd < 10.0);

    return [
        'is_stationary' => $isStationary,
        'cv_mean' => $cvMean,
        'cv_std' => $cvStd,
        'rolling_means' => $rollingMeans,
        'rolling_stds' => $rollingStds,
    ];
}

function loadStockPrices(string $filepath): array
{
    $prices = [];
    $file = fopen($filepath, 'r');

    if ($file === false) {
        throw new RuntimeException("Cannot open file: $filepath");
    }

    // Skip header
    fgets($file);

    while (($line = fgets($file)) !== false) {
        $parts = str_getcsv($line);
        if (count($parts) >= 2) {
            $prices[] = (float)$parts[1];  // close price
        }
    }

    fclose($file);
    return $prices;
}

// Load stock prices
$prices = loadStockPrices(__DIR__ . '/data/sample_stock_prices.csv');

echo "=== Stationarity Test ===\n\n";

// Test original prices
echo "Testing: Stock Prices (levels)\n";
echo str_repeat('-', 60) . "\n";

$result = testStationarity($prices, windowSize: 50);

printf("Coefficient of Variation (Mean): %.2f%%\n", $result['cv_mean']);
printf("Coefficient of Variation (Std):  %.2f%%\n", $result['cv_std']);

if ($result['is_stationary']) {
    echo "✓ Series appears STATIONARY\n";
    echo "  → Mean and variance are relatively constant\n";
    echo "  → Can apply ARMA models directly\n";
} else {
    echo "✗ Series appears NON-STATIONARY\n";
    echo "  → Mean and/or variance change over time\n";
    echo "  → Consider differencing or transformation\n";
}

// Test differenced series (returns)
echo "\n\nTesting: Daily Returns (differenced)\n";
echo str_repeat('-', 60) . "\n";

$returns = [];
for ($i = 1; $i < count($prices); $i++) {
    $returns[] = ($prices[$i] - $prices[$i - 1]) / $prices[$i - 1] * 100;
}

$resultReturns = testStationarity($returns, windowSize: 50);

printf("Coefficient of Variation (Mean): %.2f%%\n", $resultReturns['cv_mean']);
printf("Coefficient of Variation (Std):  %.2f%%\n", $resultReturns['cv_std']);

if ($resultReturns['is_stationary']) {
    echo "✓ Series appears STATIONARY\n";
    echo "  → Returns are more stable than prices\n";
    echo "  → First-order differencing achieved stationarity\n";
} else {
    echo "✗ Series still NON-STATIONARY\n";
    echo "  → May need additional transformation\n";
}

// Recommendation
echo "\n\nRecommendation:\n";
if (!$result['is_stationary'] && $resultReturns['is_stationary']) {
    echo "✓ Use differencing (convert prices to returns)\n";
    echo "  → Train models on returns\n";
    echo "  → Convert predictions back to price levels\n";
} elseif ($result['is_stationary']) {
    echo "✓ Original series is stationary\n";
    echo "  → Can model prices directly\n";
} else {
    echo "⚠ Consider additional transformations:\n";
    echo "  → Log transformation for stabilizing variance\n";
    echo "  → Second-order differencing\n";
    echo "  → Seasonal differencing if patterns exist\n";
}
