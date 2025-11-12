<?php

declare(strict_types=1);

/**
 * Calculate autocorrelation function (ACF) to understand
 * how today's values relate to past values.
 */

function calculateAutocorrelation(array $data, int $lag): float
{
    if ($lag >= count($data) || $lag < 1) {
        return 0.0;
    }

    $n = count($data) - $lag;
    $mean = array_sum($data) / count($data);

    $numerator = 0;
    $denominator = 0;

    // Calculate covariance at lag
    for ($i = 0; $i < $n; $i++) {
        $numerator += ($data[$i] - $mean) * ($data[$i + $lag] - $mean);
    }

    // Calculate variance
    for ($i = 0; $i < count($data); $i++) {
        $denominator += ($data[$i] - $mean) ** 2;
    }

    return $denominator > 0 ? $numerator / $denominator : 0;
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

// Load stock data
$prices = loadStockPrices(__DIR__ . '/data/sample_stock_prices.csv');

echo "=== Autocorrelation Analysis ===\n\n";

// Calculate ACF for lags 1-10
echo "Autocorrelation Function (ACF):\n";
echo str_repeat('-', 50) . "\n";
printf("%-8s | %-12s | %s\n", "Lag", "Correlation", "Visualization");
echo str_repeat('-', 50) . "\n";

$maxLag = 10;
for ($lag = 1; $lag <= $maxLag; $lag++) {
    $acf = calculateAutocorrelation($prices, $lag);

    // Create simple bar visualization
    $barLength = (int)(abs($acf) * 20);
    $bar = str_repeat('█', $barLength);

    printf("%-8d | %+.4f       | %s\n", $lag, $acf, $bar);
}

echo "\nInterpretation:\n";

$lag1 = calculateAutocorrelation($prices, 1);
if ($lag1 > 0.7) {
    echo "✓ Strong autocorrelation at lag 1 ($lag1)\n";
    echo "  → Today's price is highly predictable from yesterday\n";
    echo "  → Good candidate for AR models\n";
} elseif ($lag1 > 0.3) {
    echo "✓ Moderate autocorrelation at lag 1 ($lag1)\n";
    echo "  → Some predictability exists\n";
    echo "  → Moving averages or simple models may work well\n";
} else {
    echo "⚠ Weak autocorrelation at lag 1 ($lag1)\n";
    echo "  → Data may be close to random walk\n";
    echo "  → Forecasting will be challenging\n";
}

// Check for significant lags beyond 1
echo "\nSignificant lags (|ACF| > 0.2):\n";
for ($lag = 2; $lag <= $maxLag; $lag++) {
    $acf = calculateAutocorrelation($prices, $lag);
    if (abs($acf) > 0.2) {
        printf("  Lag %d: %.3f (potential seasonal pattern)\n", $lag, $acf);
    }
}
