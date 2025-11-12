<?php

declare(strict_types=1);

/**
 * Quick Start: 5-Minute Moving Average Forecaster
 *
 * This demonstrates time series forecasting in the simplest way possible.
 * See Chapter 19 for detailed explanations and advanced techniques.
 */

// Simple Moving Average Forecaster
function simpleMovingAverage(array $data, int $window = 5): array
{
    $forecasts = [];

    for ($i = $window; $i < count($data); $i++) {
        // Average of previous $window values
        $windowData = array_slice($data, $i - $window, $window);
        $forecasts[] = array_sum($windowData) / $window;
    }

    return $forecasts;
}

// Sample stock prices (closing prices for 15 days)
$prices = [
    100.0,
    102.5,
    101.8,
    103.2,
    105.0,  // Days 1-5
    104.5,
    106.0,
    107.2,
    106.8,
    108.5,  // Days 6-10
    109.0,
    110.5,
    109.8,
    111.0,
    112.5   // Days 11-15
];

echo "=== Quick Start: Time Series Forecasting ===\n\n";

echo "Historical Stock Prices:\n";
echo implode(", ", array_map(fn($p) => sprintf('$%.2f', $p), $prices)) . "\n\n";

// Calculate 5-day moving average
$window = 5;
$forecasts = simpleMovingAverage($prices, $window);

echo "5-Day Moving Average Forecasts:\n";
echo str_repeat('-', 60) . "\n";
foreach ($forecasts as $day => $forecast) {
    $actualDay = $day + $window;
    $actual = $prices[$actualDay] ?? null;

    if ($actual !== null) {
        $error = abs($actual - $forecast);
        printf(
            "Day %2d: Forecast $%.2f | Actual $%.2f | Error $%.2f\n",
            $actualDay + 1,
            $forecast,
            $actual,
            $error
        );
    }
}

// Calculate Mean Absolute Error
$errors = [];
foreach ($forecasts as $i => $forecast) {
    $actual = $prices[$i + $window];
    $errors[] = abs($actual - $forecast);
}

$mae = array_sum($errors) / count($errors);

echo "\n" . str_repeat('-', 60) . "\n";
printf("Mean Absolute Error: $%.2f\n", $mae);

// Predict next day
$lastWindowPrices = array_slice($prices, -$window);
$nextDayForecast = array_sum($lastWindowPrices) / $window;
printf("Predicted price for Day 16: $%.2f\n", $nextDayForecast);

echo "\n✓ Quick start complete!\n";
echo "  See Chapter 19 for comprehensive forecasting techniques.\n";
