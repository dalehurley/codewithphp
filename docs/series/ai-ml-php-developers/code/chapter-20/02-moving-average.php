<?php

declare(strict_types=1);

/**
 * Simple Moving Average (SMA) Forecasting.
 * Predicts future sales by averaging recent historical values.
 */

require_once '01-load-and-explore.php';

/**
 * Calculate simple moving average forecast.
 * 
 * @param array $data Historical sales data
 * @param int $window Number of periods to average
 * @param int $horizon How many periods ahead to forecast
 * @return array Forecast results
 */
function simpleMovingAverage(array $data, int $window = 3, int $horizon = 6): array
{
    if ($window < 1) {
        throw new InvalidArgumentException("Window must be at least 1");
    }

    if ($window > count($data)) {
        throw new InvalidArgumentException(
            "Window ($window) cannot exceed data size (" . count($data) . ")"
        );
    }

    $forecasts = [];

    // For each forecast period
    for ($h = 1; $h <= $horizon; $h++) {
        // Take the last 'window' actual values
        $recentValues = array_slice(
            array_column($data, 'revenue'),
            -$window
        );

        // Average them
        $forecast = array_sum($recentValues) / count($recentValues);

        // Calculate forecast date
        $lastMonth = $data[count($data) - 1]['month'];
        $forecastDate = date('Y-m', strtotime($lastMonth . '-01 +' . $h . ' month'));

        $forecasts[] = [
            'month' => $forecastDate,
            'forecast' => $forecast,
            'method' => "SMA-$window",
        ];
    }

    return $forecasts;
}

/**
 * Calculate weighted moving average (more recent = higher weight).
 */
function weightedMovingAverage(array $data, int $window = 3, int $horizon = 6): array
{
    if ($window > count($data)) {
        throw new InvalidArgumentException(
            "Window ($window) cannot exceed data size (" . count($data) . ")"
        );
    }

    $forecasts = [];

    // Generate weights: most recent gets highest weight
    // For window=3: weights are [1, 2, 3] (normalized)
    $weights = range(1, $window);
    $weightSum = array_sum($weights);

    for ($h = 1; $h <= $horizon; $h++) {
        $recentValues = array_slice(
            array_column($data, 'revenue'),
            -$window
        );

        // Calculate weighted average
        $forecast = 0;
        foreach ($recentValues as $i => $value) {
            $forecast += $value * $weights[$i];
        }
        $forecast /= $weightSum;

        $lastMonth = $data[count($data) - 1]['month'];
        $forecastDate = date('Y-m', strtotime($lastMonth . '-01 +' . $h . ' month'));

        $forecasts[] = [
            'month' => $forecastDate,
            'forecast' => $forecast,
            'method' => "WMA-$window",
        ];
    }

    return $forecasts;
}

// Main execution
echo "📈 Moving Average Forecasting\n";
echo str_repeat('=', 70) . "\n\n";

try {
    // Load historical data
    $salesData = loadSalesData('sample-sales-data.csv');
    $lastActual = end($salesData);

    echo "Historical Data (Last 6 months):\n";
    foreach (array_slice($salesData, -6) as $record) {
        echo sprintf(
            "  %s: $%s\n",
            $record['month'],
            number_format($record['revenue'])
        );
    }

    // Generate forecasts with different windows
    $sma3 = simpleMovingAverage($salesData, window: 3, horizon: 6);
    $sma6 = simpleMovingAverage($salesData, window: 6, horizon: 6);
    $wma3 = weightedMovingAverage($salesData, window: 3, horizon: 6);

    echo "\n" . str_repeat('-', 70) . "\n";
    echo "Forecasts for Next 6 Months:\n";
    echo str_repeat('-', 70) . "\n";
    printf(
        "%-12s  %-15s  %-15s  %-15s\n",
        "Month",
        "SMA-3",
        "SMA-6",
        "WMA-3"
    );
    echo str_repeat('-', 70) . "\n";

    for ($i = 0; $i < 6; $i++) {
        printf(
            "%-12s  $%-14s  $%-14s  $%-14s\n",
            $sma3[$i]['month'],
            number_format($sma3[$i]['forecast'], 2),
            number_format($sma6[$i]['forecast'], 2),
            number_format($wma3[$i]['forecast'], 2)
        );
    }

    echo "\n" . str_repeat('-', 70) . "\n";
    echo "Method Comparison:\n";
    echo str_repeat('-', 70) . "\n";

    echo "SMA-3 (3-month average):\n";
    echo "  • Uses last 3 months: " .
        implode(', ', array_map(
            fn($r) => '$' . number_format($r['revenue']),
            array_slice($salesData, -3)
        )) . "\n";
    echo "  • Forecast: $" . number_format($sma3[0]['forecast'], 2) . "\n";
    echo "  • Responds quickly to recent changes\n";

    echo "\nSMA-6 (6-month average):\n";
    echo "  • Uses last 6 months\n";
    echo "  • Forecast: $" . number_format($sma6[0]['forecast'], 2) . "\n";
    echo "  • Smoother, less reactive to short-term fluctuations\n";

    echo "\nWMA-3 (weighted 3-month):\n";
    echo "  • Recent months weighted higher (weights: 1, 2, 3)\n";
    echo "  • Forecast: $" . number_format($wma3[0]['forecast'], 2) . "\n";
    echo "  • Balance between responsiveness and stability\n";

    echo "\n✅ Moving average forecasts generated successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
