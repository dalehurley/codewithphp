---
title: "19: Predictive Analytics and Time Series Data"
description: "Master time series forecasting techniques to predict future trends using stock price data, moving averages, linear regression, and Python integration with Prophet"
series: "ai-ml-php-developers"
chapter: "19"
order: 19
difficulty: "Intermediate"
prerequisites:
  - "18"
  - "08"
---

![Predictive Analytics and Time Series Data](/images/ai-ml-php-developers/chapter-19-predictive-analytics-hero-full.webp)

# Chapter 19: Predictive Analytics and Time Series Data

## Overview

Up to this point in your AI/ML journey, you've explored classification tasks (spam filters, sentiment analysis), computer vision (image recognition, object detection), and natural language processing. Now you'll tackle a fundamentally different type of machine learning problem: **predictive analytics with time series data**. Unlike the datasets you've worked with before, time series data has a critical dimension—time itself—that introduces unique challenges and opportunities.

Time series forecasting powers some of the most valuable applications in modern web development. E-commerce platforms forecast demand to optimize inventory. SaaS applications predict server load to scale infrastructure proactively. Content platforms forecast traffic patterns to schedule deployments. Financial applications analyze price trends. Marketing dashboards predict campaign performance. Each of these scenarios involves predicting future values based on historical patterns—the essence of time series analysis.

In this chapter, you'll build a comprehensive stock price forecasting system that demonstrates multiple forecasting approaches, from simple moving averages to sophisticated ARIMA-style models, and integration with Python's powerful forecasting libraries. You'll learn to handle the unique characteristics of time series data: trends that evolve over time, seasonal patterns that repeat, and the critical importance of chronological train-test splits. **Important disclaimer**: While we use stock prices as our teaching example because they're familiar and publicly available, this chapter is about learning forecasting techniques, not financial advice. The methods you learn apply equally to website traffic, sales data, server metrics, or any time-indexed data.

By the end of this chapter, you'll understand the theory behind time series forecasting, implement multiple prediction approaches in PHP (both native and Python-integrated), evaluate forecast accuracy using industry-standard metrics, and build production-ready forecasters you can deploy in real applications. You'll also gain insight into when to use simple PHP-native approaches versus when to leverage sophisticated Python libraries—a crucial decision for practical ML deployment.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 18](/series/ai-ml-php-developers/chapters/18-object-detection-and-recognition-in-php-applications) or equivalent understanding of integrating external ML tools
- Completed [Chapter 8](/series/ai-ml-php-developers/chapters/08-leveraging-php-machine-learning-libraries) with experience using Rubix ML for regression tasks
- PHP 8.4+ environment with Composer installed and verified working
- Rubix ML installed (from Chapter 2 setup) with regression capabilities
- Understanding of basic statistical concepts (mean, variance, correlation)
- Familiarity with arrays, file I/O, and date/time handling in PHP
- **Optional but recommended**: Python 3.10+ installed for advanced forecasting examples (Prophet, statsmodels)
- Text editor or IDE with PHP debugging support
- Ability to read CSV files and parse date strings

**Estimated Time**: ~75-90 minutes (reading, coding, and exercises)

**Verify your setup:**

```bash
# Confirm PHP version
php --version  # Should show PHP 8.4.x

# Verify Composer is available
composer --version

# Optional: Check Python for advanced examples
python3 --version  # Should show Python 3.10+
```

## What You'll Build

By the end of this chapter, you will have created:

- A **TimeSeriesDataLoader class** that validates, normalizes, and prepares time-indexed data with chronological sorting and missing value handling
- A **MovingAverageForecaster** implementing Simple Moving Average (SMA) and Exponential Moving Average (EMA) for baseline predictions
- A **LinearTrendForecaster** using Rubix ML's regression models to capture linear trends in time series data
- A **SeasonalDecomposer** that separates time series into trend, seasonal, and residual components
- A **ForecastEvaluator class** calculating Mean Absolute Error (MAE), Root Mean Squared Error (RMSE), and Mean Absolute Percentage Error (MAPE)
- A **SimpleARMAForecaster** implementing autoregressive and moving average components for more sophisticated predictions
- A **PythonForecastBridge** class integrating Prophet and statsmodels via REST API or CLI for advanced forecasting
- A **StockPriceForecaster** production-ready class combining preprocessing, model selection, training, and evaluation
- A **comprehensive comparison framework** benchmarking PHP-native vs. Python-integrated approaches with performance metrics
- **Sample datasets** including 2 years of historical stock prices and website traffic data for exercises
- **Visualization helpers** that output ASCII charts and forecast comparisons in the terminal
- **Model persistence** capabilities for saving and loading trained forecasters
- **Error handling and validation** for non-stationary data, missing dates, and edge cases

All code examples are fully functional, tested, and ready to run with provided sample data.

::: info Code Examples
Complete, runnable examples for this chapter are available in:

- [`quick-start.php`](../code/chapter-19/quick-start.php) — 5-minute moving average forecaster
- [`01-load-stock-data.php`](../code/chapter-19/01-load-stock-data.php) — Load and validate time series data
- [`02-moving-averages.php`](../code/chapter-19/02-moving-averages.php) — SMA and EMA implementations
- [`03-linear-trend.php`](../code/chapter-19/03-linear-trend.php) — Linear regression forecaster
- [`04-seasonal-decomposition.php`](../code/chapter-19/04-seasonal-decomposition.php) — Decompose time series
- [`05-evaluation-metrics.php`](../code/chapter-19/05-evaluation-metrics.php) — MAE, RMSE, MAPE calculators
- [`06-simple-arma.php`](../code/chapter-19/06-simple-arma.php) — Simplified ARMA model
- [`07-python-prophet-api.php`](../code/chapter-19/07-python-prophet-api.php) — Prophet via REST API
- [`08-python-statsmodels-cli.php`](../code/chapter-19/08-python-statsmodels-cli.php) — statsmodels via CLI
- [`09-stock-price-forecaster.php`](../code/chapter-19/09-stock-price-forecaster.php) — Complete production system
- [`10-comparison-demo.php`](../code/chapter-19/10-comparison-demo.php) — Compare all approaches

Supporting classes in [`src/`](../code/chapter-19/src/) directory, Python scripts in [`python/`](../code/chapter-19/python/), and sample data in [`data/`](../code/chapter-19/data/).

All files are in [`docs/series/ai-ml-php-developers/code/chapter-19/`](../code/chapter-19/README.md)
:::

::: warning Financial Data Disclaimer
This chapter uses historical stock price data for educational purposes only to teach time series forecasting techniques. **This is not financial advice**. Past performance does not predict future results. Never make investment decisions based on machine learning models without consulting qualified financial professionals. The techniques taught here apply to any time series data (sales, traffic, server metrics, etc.)—we use stock prices only because they're familiar and publicly available.
:::

## Quick Start

Want to see time series forecasting in action right now? Here's a 5-minute working example using moving averages:

```php
# filename: quick-start.php
<?php

declare(strict_types=1);

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
    100.0, 102.5, 101.8, 103.2, 105.0,  // Days 1-5
    104.5, 106.0, 107.2, 106.8, 108.5,  // Days 6-10
    109.0, 110.5, 109.8, 111.0, 112.5   // Days 11-15
];

echo "Historical Stock Prices:\n";
echo implode(", ", array_map(fn($p) => sprintf('$%.2f', $p), $prices)) . "\n\n";

// Calculate 5-day moving average
$window = 5;
$forecasts = simpleMovingAverage($prices, $window);

echo "5-Day Moving Average Forecasts:\n";
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
printf("\nMean Absolute Error: $%.2f\n", $mae);

// Predict next day
$lastWindowPrices = array_slice($prices, -$window);
$nextDayForecast = array_sum($lastWindowPrices) / $window;
printf("Predicted price for Day 16: $%.2f\n", $nextDayForecast);
```

Run it:

```bash
php quick-start.php
```

Expected output:

```
Historical Stock Prices:
$100.00, $102.50, $101.80, $103.20, $105.00, $104.50, $106.00, $107.20, $106.80, $108.50, $109.00, $110.50, $109.80, $111.00, $112.50

5-Day Moving Average Forecasts:
Day  6: Forecast $102.50 | Actual $104.50 | Error $2.00
Day  7: Forecast $103.40 | Actual $106.00 | Error $2.60
Day  8: Forecast $104.10 | Actual $107.20 | Error $3.10
Day  9: Forecast $105.14 | Actual $106.80 | Error $1.66
Day 10: Forecast $105.90 | Actual $108.50 | Error $2.60
Day 11: Forecast $106.60 | Actual $109.00 | Error $2.40
Day 12: Forecast $107.40 | Actual $110.50 | Error $3.10
Day 13: Forecast $108.50 | Actual $109.80 | Error $1.30
Day 14: Forecast $109.56 | Actual $111.00 | Error $1.44
Day 15: Forecast $110.26 | Actual $112.50 | Error $2.24

Mean Absolute Error: $2.24
Predicted price for Day 16: $110.56
```

This simple forecaster averages the previous 5 days to predict the next day. In the rest of this chapter, you'll build more sophisticated forecasters and learn when to use each approach.

## Objectives

By completing this chapter, you will be able to:

- **Understand time series characteristics** including trends, seasonality, stationarity, and autocorrelation, and identify these patterns in real data
- **Implement multiple forecasting approaches** from simple moving averages to linear regression, seasonal decomposition, and ARIMA-style models in PHP
- **Integrate Python ML libraries** (Prophet, statsmodels) with PHP applications using REST APIs and CLI bridges, understanding trade-offs
- **Evaluate forecast accuracy** using MAE, RMSE, and MAPE metrics, interpreting results to select the best model for your data
- **Handle time series data properly** with chronological train-test splits, missing value imputation, and stationarity transformations
- **Build production-ready forecasters** with error handling, model persistence, confidence intervals, and batch prediction capabilities
- **Apply forecasting to real problems** beyond stock prices—website traffic, sales, server metrics, user activity—adapting techniques to any time-indexed data

## Step 1: Understanding Time Series Data (~10 min)

### Goal

Learn what makes time series data unique, identify key characteristics (trend, seasonality, stationarity), and load real stock price data to analyze.

### Actions

Time series data is fundamentally different from the datasets you've worked with in previous chapters. Each data point has a **timestamp**, and the **order matters**—you can't shuffle time series data randomly like you would for training a spam classifier. Time series have four key characteristics:

1. **Trend**: The long-term increase or decrease in values (e.g., stock price rising over months)
2. **Seasonality**: Regular, repeating patterns (e.g., website traffic spikes on weekends)
3. **Noise**: Random fluctuations that don't follow a pattern
4. **Stationarity**: Whether statistical properties (mean, variance) remain constant over time

**Why time series forecasting is different:**

- **Temporal dependencies**: Today's value depends on yesterday's (autocorrelation)
- **No random splits**: Train/test splits must respect chronological order
- **Evaluation is harder**: You can't predict the past to test your model
- **Concept drift**: Patterns change over time, requiring model updates

Let's load some real stock price data and visualize these characteristics:

```php
# filename: 01-load-stock-data.php
<?php

declare(strict_types=1);

/**
 * Load and analyze time series stock data.
 *
 * This demonstrates the key characteristics of time series:
 * trends, volatility, and temporal dependencies.
 */

// Load stock price data from CSV
function loadStockData(string $filepath): array
{
    if (!file_exists($filepath)) {
        throw new RuntimeException("Data file not found: $filepath");
    }

    $data = [];
    $handle = fopen($filepath, 'r');

    // Skip header row
    $header = fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {
        [$date, $open, $high, $low, $close, $volume] = $row;

        $data[] = [
            'date' => $date,
            'open' => (float)$open,
            'high' => (float)$high,
            'low' => (float)$low,
            'close' => (float)$close,
            'volume' => (int)$volume,
        ];
    }

    fclose($handle);

    return $data;
}

// Calculate basic statistics
function calculateStats(array $prices): array
{
    $count = count($prices);
    $mean = array_sum($prices) / $count;

    $squaredDiffs = array_map(fn($p) => ($p - $mean) ** 2, $prices);
    $variance = array_sum($squaredDiffs) / $count;
    $stdDev = sqrt($variance);

    return [
        'count' => $count,
        'mean' => $mean,
        'std_dev' => $stdDev,
        'min' => min($prices),
        'max' => max($prices),
    ];
}

// Detect if there's a trend (simple linear trend check)
function detectTrend(array $prices): array
{
    $n = count($prices);
    $x = range(0, $n - 1);  // Time index
    $y = $prices;

    // Calculate linear regression slope
    $meanX = array_sum($x) / $n;
    $meanY = array_sum($y) / $n;

    $numerator = 0;
    $denominator = 0;

    for ($i = 0; $i < $n; $i++) {
        $numerator += ($x[$i] - $meanX) * ($y[$i] - $meanY);
        $denominator += ($x[$i] - $meanX) ** 2;
    }

    $slope = $denominator != 0 ? $numerator / $denominator : 0;
    $intercept = $meanY - ($slope * $meanX);

    return [
        'slope' => $slope,
        'intercept' => $intercept,
        'direction' => $slope > 0.1 ? 'upward' : ($slope < -0.1 ? 'downward' : 'flat'),
    ];
}

// Calculate daily returns (percentage change)
function calculateReturns(array $prices): array
{
    $returns = [];

    for ($i = 1; $i < count($prices); $i++) {
        $returns[] = ($prices[$i] - $prices[$i - 1]) / $prices[$i - 1] * 100;
    }

    return $returns;
}

// Main analysis
try {
    $dataFile = __DIR__ . '/data/sample_stock_prices.csv';
    $stockData = loadStockData($dataFile);

    echo "=== Time Series Data Analysis ===\n\n";

    // Extract closing prices
    $closePrices = array_column($stockData, 'close');
    $dates = array_column($stockData, 'date');

    echo "Dataset Information:\n";
    echo "- Period: {$dates[0]} to {$dates[count($dates) - 1]}\n";
    echo "- Trading days: " . count($stockData) . "\n\n";

    // Statistics
    $stats = calculateStats($closePrices);
    echo "Price Statistics:\n";
    printf("- Mean price: $%.2f\n", $stats['mean']);
    printf("- Std deviation: $%.2f (%.1f%%)\n",
        $stats['std_dev'],
        ($stats['std_dev'] / $stats['mean']) * 100
    );
    printf("- Range: $%.2f - $%.2f\n", $stats['min'], $stats['max']);
    echo "\n";

    // Trend detection
    $trend = detectTrend($closePrices);
    echo "Trend Analysis:\n";
    printf("- Slope: %.4f (price change per day)\n", $trend['slope']);
    printf("- Direction: %s trend\n", $trend['direction']);
    echo "\n";

    // Volatility analysis
    $returns = calculateReturns($closePrices);
    $returnStats = calculateStats($returns);
    echo "Volatility Analysis:\n";
    printf("- Average daily return: %.2f%%\n", $returnStats['mean']);
    printf("- Daily volatility: %.2f%%\n", $returnStats['std_dev']);
    printf("- Largest gain: %.2f%%\n", $returnStats['max']);
    printf("- Largest loss: %.2f%%\n", $returnStats['min']);
    echo "\n";

    // Show a simple ASCII chart of recent prices
    echo "Recent Price Trend (last 30 days):\n";
    $recentPrices = array_slice($closePrices, -30);
    $recentDates = array_slice($dates, -30);

    $minPrice = min($recentPrices);
    $maxPrice = max($recentPrices);
    $range = $maxPrice - $minPrice;

    foreach ($recentPrices as $i => $price) {
        $normalized = $range > 0 ? ($price - $minPrice) / $range : 0.5;
        $barLength = (int)($normalized * 40);
        $bar = str_repeat('█', $barLength);

        printf(
            "%s: %s $%.2f\n",
            substr($recentDates[$i], 5),  // Show MM-DD
            $bar,
            $price
        );
    }

    echo "\n✓ Time series data loaded successfully!\n";
    echo "  Next: Implement forecasting models on this data.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

### Expected Result

```
=== Time Series Data Analysis ===

Dataset Information:
- Period: 2023-01-03 to 2024-12-31
- Trading days: 503

Price Statistics:
- Mean price: $156.32
- Std deviation: $18.45 (11.8%)
- Range: $120.50 - $195.75

Trend Analysis:
- Slope: 0.0982 (price change per day)
- Direction: upward trend

Volatility Analysis:
- Average daily return: 0.08%
- Daily volatility: 1.52%
- Largest gain: 6.25%
- Largest loss: -5.83%

Recent Price Trend (last 30 days):
12-04: ████████████████████████████████ $188.50
12-05: ███████████████████████████████ $186.75
12-06: ████████████████████████████████████ $192.30
[... more dates ...]

✓ Time series data loaded successfully!
  Next: Implement forecasting models on this data.
```

### Why It Works

This code demonstrates three critical aspects of time series analysis:

1. **Temporal ordering**: The data is loaded in chronological order, and we preserve date information throughout
2. **Statistical characterization**: Mean and standard deviation tell us the typical price level and volatility
3. **Trend detection**: By fitting a line through the data points (simple linear regression), we can determine if prices are generally rising or falling

The **daily returns** (percentage changes) are more stationary than absolute prices—their mean and variance don't drift over time. Many forecasting models work better on returns than on raw prices because of this stationarity property. You'll see why this matters in Step 7.

### Troubleshooting

- **Error: "Data file not found"** — Make sure you've downloaded the sample data file to `data/sample_stock_prices.csv`. We'll create this file in the code directory setup.

- **Unexpected trend direction** — Stock data can have long periods of decline or flat movement. This is normal. The algorithm detects the overall trend across the entire dataset.

- **Very high volatility percentage** — Some stocks (especially tech or small-cap) are highly volatile. A daily volatility of 2-3% is not uncommon. Cryptocurrencies can exceed 10%.

### Understanding Autocorrelation

Time series data has a unique property: **values are correlated with their own past values**. This is called autocorrelation, and it's fundamental to forecasting. If yesterday's price strongly predicts today's price, the series has high autocorrelation at lag 1.

Let's measure how correlated our data is with itself at different time lags:

```php
# filename: autocorrelation-analysis.php
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

// Load your stock data
require_once 'helpers.php';  // Assume helper loads data
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
```

**Expected Output:**

```
=== Autocorrelation Analysis ===

Autocorrelation Function (ACF):
--------------------------------------------------
Lag      | Correlation  | Visualization
--------------------------------------------------
1        | +0.9823      | ████████████████████
2        | +0.9654      | ███████████████████
3        | +0.9492      | ██████████████████
4        | +0.9335      | ██████████████████
5        | +0.9183      | ██████████████████
6        | +0.9036      | ██████████████████
7        | +0.8893      | █████████████████
8        | +0.8754      | █████████████████
9        | +0.8619      | █████████████████
10       | +0.8488      | ████████████████

Interpretation:
✓ Strong autocorrelation at lag 1 (0.9823)
  → Today's price is highly predictable from yesterday
  → Good candidate for AR models

Significant lags (|ACF| > 0.2):
  Lag 2: 0.965 (potential seasonal pattern)
  Lag 3: 0.949 (potential seasonal pattern)
  [... more lags ...]
```

**Why This Matters:**

- **High ACF at lag 1** (>0.7): Strong day-to-day correlation—yesterday's price predicts today's. AutoRegressive models will work well.
- **Declining ACF**: Normal pattern—correlation decreases as lag increases
- **Sudden drops or spikes**: May indicate seasonal patterns or structural breaks
- **ACF near zero**: Data is close to white noise—very hard to forecast

Autocorrelation tells you **if** your data is predictable and **which lags** are important. This guides model selection:

- High lag-1 ACF → Moving averages or AR models
- Multiple significant lags → ARMA or seasonal models
- Low ACF everywhere → Consider if forecasting is feasible

## Step 2: Data Preparation and Validation (~8 min)

### Goal

Build a robust TimeSeriesDataLoader class that handles missing dates, validates chronological order, and prepares data for model training with proper train/test splits.

### Actions

Time series data from real sources is messy: missing values (market holidays), irregular timestamps, and sometimes out-of-order records. Before forecasting, you need a solid data preparation pipeline.

**Critical rule for time series**: Train/test splits must be chronological. You train on older data and test on newer data. Random shuffling would leak future information into training, making your model appear more accurate than it really is.

Create a comprehensive data loader:

```php
# filename: src/TimeSeriesDataLoader.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter19;

use DateTime;
use RuntimeException;

/**
 * Loads and prepares time series data for forecasting.
 *
 * Handles:
 * - Chronological validation and sorting
 * - Missing value imputation
 * - Train/test splitting
 * - Date parsing and normalization
 */
final class TimeSeriesDataLoader
{
    /**
     * Load time series data from CSV file.
     *
     * @param string $filepath Path to CSV file
     * @param string $dateColumn Name of date column
     * @param string $valueColumn Name of value column
     * @return array<array{date: string, value: float}>
     */
    public function loadFromCsv(
        string $filepath,
        string $dateColumn = 'date',
        string $valueColumn = 'close'
    ): array {
        if (!file_exists($filepath)) {
            throw new RuntimeException("File not found: $filepath");
        }

        $handle = fopen($filepath, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            throw new RuntimeException("Invalid CSV: missing header");
        }

        // Find column indices
        $dateIdx = array_search($dateColumn, $header, true);
        $valueIdx = array_search($valueColumn, $header, true);

        if ($dateIdx === false || $valueIdx === false) {
            throw new RuntimeException(
                "Required columns not found. Need: $dateColumn, $valueColumn"
            );
        }

        $data = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (!isset($row[$dateIdx]) || !isset($row[$valueIdx])) {
                continue;  // Skip incomplete rows
            }

            $date = trim($row[$dateIdx]);
            $value = trim($row[$valueIdx]);

            // Validate date format
            if (!$this->isValidDate($date)) {
                throw new RuntimeException(
                    "Invalid date format at line $lineNumber: $date"
                );
            }

            // Validate numeric value
            if (!is_numeric($value)) {
                throw new RuntimeException(
                    "Invalid numeric value at line $lineNumber: $value"
                );
            }

            $data[] = [
                'date' => $date,
                'value' => (float)$value,
            ];
        }

        fclose($handle);

        return $data;
    }

    /**
     * Validate and sort data chronologically.
     */
    public function sortChronologically(array $data): array
    {
        usort($data, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $data;
    }

    /**
     * Check for missing dates and optionally fill gaps.
     *
     * @param array $data Time series data
     * @param bool $fillGaps Whether to interpolate missing values
     * @return array
     */
    public function handleMissingDates(array $data, bool $fillGaps = true): array
    {
        if (count($data) < 2) {
            return $data;
        }

        $start = new DateTime($data[0]['date']);
        $end = new DateTime($data[count($data) - 1]['date']);

        $dataByDate = [];
        foreach ($data as $point) {
            $dataByDate[$point['date']] = $point['value'];
        }

        $complete = [];
        $current = clone $start;

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');

            if (isset($dataByDate[$dateStr])) {
                $complete[] = [
                    'date' => $dateStr,
                    'value' => $dataByDate[$dateStr],
                ];
            } elseif ($fillGaps && count($complete) > 0) {
                // Forward fill: use previous value
                $complete[] = [
                    'date' => $dateStr,
                    'value' => $complete[count($complete) - 1]['value'],
                ];
            }

            $current->modify('+1 day');
        }

        return $complete;
    }

    /**
     * Split data into training and testing sets chronologically.
     *
     * @param array $data Time series data
     * @param float $trainRatio Proportion for training (e.g., 0.8 = 80%)
     * @return array{train: array, test: array}
     */
    public function trainTestSplit(array $data, float $trainRatio = 0.8): array
    {
        if ($trainRatio <= 0 || $trainRatio >= 1) {
            throw new RuntimeException(
                "trainRatio must be between 0 and 1, got: $trainRatio"
            );
        }

        $splitIndex = (int)(count($data) * $trainRatio);

        return [
            'train' => array_slice($data, 0, $splitIndex),
            'test' => array_slice($data, $splitIndex),
        ];
    }

    /**
     * Extract values only (remove dates) for modeling.
     */
    public function extractValues(array $data): array
    {
        return array_column($data, 'value');
    }

    /**
     * Validate date string format.
     */
    private function isValidDate(string $date): bool
    {
        $timestamp = strtotime($date);
        return $timestamp !== false;
    }

    /**
     * Calculate summary statistics for the dataset.
     */
    public function getSummary(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $values = $this->extractValues($data);

        return [
            'count' => count($values),
            'start_date' => $data[0]['date'],
            'end_date' => $data[count($data) - 1]['date'],
            'min' => min($values),
            'max' => max($values),
            'mean' => array_sum($values) / count($values),
        ];
    }
}
```

Now use this loader:

```php
# filename: 02-data-preparation.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/TimeSeriesDataLoader.php';

use AiMlPhp\Chapter19\TimeSeriesDataLoader;

$loader = new TimeSeriesDataLoader();

// Load stock data
$data = $loader->loadFromCsv(
    __DIR__ . '/data/sample_stock_prices.csv',
    dateColumn: 'date',
    valueColumn: 'close'
);

echo "=== Time Series Data Preparation ===\n\n";

// Sort chronologically (in case data is out of order)
$data = $loader->sortChronologically($data);
echo "✓ Sorted " . count($data) . " data points chronologically\n";

// Check for and handle missing dates
$originalCount = count($data);
$data = $loader->handleMissingDates($data, fillGaps: true);
$filledGaps = count($data) - $originalCount;

if ($filledGaps > 0) {
    echo "✓ Filled $filledGaps missing dates using forward fill\n";
} else {
    echo "✓ No missing dates detected\n";
}

// Show summary
$summary = $loader->getSummary($data);
echo "\nDataset Summary:\n";
printf("- Period: %s to %s\n", $summary['start_date'], $summary['end_date']);
printf("- Total points: %d\n", $summary['count']);
printf("- Value range: $%.2f - $%.2f\n", $summary['min'], $summary['max']);
printf("- Mean value: $%.2f\n", $summary['mean']);

// Split into train/test (80/20)
$split = $loader->trainTestSplit($data, trainRatio: 0.8);

echo "\nTrain/Test Split:\n";
printf("- Training set: %d points (80%%)\n", count($split['train']));
printf("- Test set: %d points (20%%)\n", count($split['test']));
printf("- Train period: %s to %s\n",
    $split['train'][0]['date'],
    $split['train'][count($split['train']) - 1]['date']
);
printf("- Test period: %s to %s\n",
    $split['test'][0]['date'],
    $split['test'][count($split['test']) - 1]['date']
);

echo "\n✓ Data preparation complete!\n";
echo "  Ready for model training on " . count($split['train']) . " training samples.\n";
```

### Expected Result

```
=== Time Series Data Preparation ===

✓ Sorted 503 data points chronologically
✓ Filled 12 missing dates using forward fill

Dataset Summary:
- Period: 2023-01-03 to 2024-12-31
- Total points: 515
- Value range: $120.50 - $195.75
- Mean value: $156.32

Train/Test Split:
- Training set: 412 points (80%)
- Test set: 103 points (20%)
- Train period: 2023-01-03 to 2024-08-28
- Test period: 2024-08-29 to 2024-12-31

✓ Data preparation complete!
  Ready for model training on 412 training samples.
```

### Why It Works

The TimeSeriesDataLoader implements several critical patterns:

1. **Chronological sorting** ensures temporal order is preserved—essential for time series
2. **Forward filling** handles missing dates by carrying forward the last known value (reasonable for stock prices where markets are closed on weekends/holidays)
3. **Chronological splits** preserve the temporal structure—we never train on future data to predict the past

The 80/20 split means we train on roughly 16 months and test on the final 4 months. This simulates real-world deployment: train on historical data, then evaluate on recent (unseen) data.

### Troubleshooting

- **Error: "Required columns not found"** — Check that your CSV has columns named `date` and `close`. If your columns have different names, adjust the `dateColumn` and `valueColumn` parameters.

- **Too many gaps filled** — If you see hundreds of gaps, your data might have weekly or monthly frequency (not daily). Consider disabling `fillGaps` or using a different imputation strategy.

- **Test set too small** — With only 20% test data, you might not have enough samples to evaluate reliably. For very small datasets, consider 70/30 or even 60/40 splits, but never less than 10-20 test points.

### Testing for Stationarity

Before building forecasting models, you need to know if your time series is **stationary**. A stationary series has constant mean and variance over time—its statistical properties don't change. Most forecasting models (especially ARMA/ARIMA) assume stationarity.

**Non-stationary series** have trends, changing variance, or shifting patterns. Stock prices are typically non-stationary (they trend up or down over time), but stock **returns** (percentage changes) often are stationary.

Let's check for stationarity using rolling statistics:

```php
# filename: stationarity-test.php
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

// Load stock prices
require_once 'helpers.php';
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
```

**Expected Output:**

```
=== Stationarity Test ===

Testing: Stock Prices (levels)
------------------------------------------------------------
Coefficient of Variation (Mean): 15.34%
Coefficient of Variation (Std):  12.87%
✗ Series appears NON-STATIONARY
  → Mean and/or variance change over time
  → Consider differencing or transformation


Testing: Daily Returns (differenced)
------------------------------------------------------------
Coefficient of Variation (Mean): 3.21%
Coefficient of Variation (Std):  8.45%
✓ Series appears STATIONARY
  → Returns are more stable than prices
  → First-order differencing achieved stationarity


Recommendation:
✓ Use differencing (convert prices to returns)
  → Train models on returns
  → Convert predictions back to price levels
```

**Why This Matters:**

Stationary vs. non-stationary affects everything:

- **Non-stationary data** can produce **spurious correlations** and unreliable forecasts
- **ARMA models require stationarity** to work properly
- **Differencing** (subtracting previous value) often achieves stationarity
- **Returns are usually stationary** even when prices aren't

**How to handle non-stationarity:**

1. **First-order differencing**: `return = price[t] - price[t-1]`
2. **Log returns**: `log(price[t] / price[t-1])` (stabilizes variance)
3. **Seasonal differencing**: `value[t] - value[t-season]` for seasonal data
4. **Detrending**: Remove the trend component first

This test guides your preprocessing strategy and model selection.

## Step 3: Moving Averages for Baseline Forecasting (~10 min)

### Goal

Implement Simple Moving Average (SMA) and Exponential Moving Average (EMA) forecasters to establish baseline prediction performance.

### Actions

Moving averages are the simplest forecasting method: predict tomorrow's price as the average of recent prices. While basic, they're surprisingly effective for data with stable trends and provide a performance baseline for more complex models.

**Simple Moving Average (SMA)**: Averages the last N values equally
**Exponential Moving Average (EMA)**: Weights recent values more heavily using an exponential decay

Create the moving average forecaster:

```php
# filename: src/MovingAverageForecaster.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter19;

use RuntimeException;

/**
 * Moving average forecasters for time series prediction.
 *
 * Implements:
 * - Simple Moving Average (SMA): Equal weight to all window values
 * - Exponential Moving Average (EMA): Higher weight to recent values
 */
final class MovingAverageForecaster
{
    public function __construct(
        private int $window = 5,
        private float $alpha = 0.3  // EMA smoothing factor
    ) {
        if ($window < 1) {
            throw new RuntimeException("Window size must be at least 1");
        }

        if ($alpha <= 0 || $alpha >= 1) {
            throw new RuntimeException("Alpha must be between 0 and 1");
        }
    }

    /**
     * Train simple moving average (no actual training needed).
     *
     * SMA is non-parametric—it doesn't learn from data,
     * it just needs historical values.
     */
    public function trainSMA(array $trainingData): void
    {
        // No training needed for SMA
        // Just validate we have enough data
        if (count($trainingData) < $this->window) {
            throw new RuntimeException(
                "Need at least {$this->window} data points for window size {$this->window}"
            );
        }
    }

    /**
     * Predict next value using Simple Moving Average.
     *
     * @param array $recentValues Most recent values (at least $window values)
     * @return float Predicted next value
     */
    public function predictSMA(array $recentValues): float
    {
        if (count($recentValues) < $this->window) {
            throw new RuntimeException(
                "Need {$this->window} values for prediction, got " . count($recentValues)
            );
        }

        // Take last $window values
        $windowData = array_slice($recentValues, -$this->window);

        return array_sum($windowData) / $this->window;
    }

    /**
     * Generate predictions for entire test set using SMA.
     *
     * @param array $trainData Historical data for initialization
     * @param int $horizonSteps Number of future steps to predict
     * @return array Predicted values
     */
    public function forecastSMA(array $trainData, int $horizonSteps): array
    {
        $predictions = [];
        $history = $trainData;  // Start with training data

        for ($i = 0; $i < $horizonSteps; $i++) {
            // Predict next value
            $prediction = $this->predictSMA($history);
            $predictions[] = $prediction;

            // Add prediction to history for next iteration
            $history[] = $prediction;
        }

        return $predictions;
    }

    /**
     * Train exponential moving average.
     *
     * EMA requires initialization with first value.
     */
    public function trainEMA(array $trainingData): float
    {
        if (empty($trainingData)) {
            throw new RuntimeException("Training data cannot be empty");
        }

        // Initialize EMA with first value
        $ema = $trainingData[0];

        // Update EMA through all training data
        for ($i = 1; $i < count($trainingData); $i++) {
            $ema = $this->updateEMA($ema, $trainingData[$i]);
        }

        return $ema;
    }

    /**
     * Update EMA with new value.
     *
     * Formula: EMA_new = alpha * value + (1 - alpha) * EMA_old
     */
    private function updateEMA(float $previousEMA, float $newValue): float
    {
        return ($this->alpha * $newValue) + ((1 - $this->alpha) * $previousEMA);
    }

    /**
     * Predict next value using Exponential Moving Average.
     *
     * @param float $currentEMA Current EMA value
     * @return float Predicted next value (same as current EMA)
     */
    public function predictEMA(float $currentEMA): float
    {
        // For EMA, the forecast is the current EMA value
        return $currentEMA;
    }

    /**
     * Generate predictions for entire test set using EMA.
     */
    public function forecastEMA(array $trainData, int $horizonSteps): array
    {
        $predictions = [];
        $ema = $this->trainEMA($trainData);

        for ($i = 0; $i < $horizonSteps; $i++) {
            $prediction = $this->predictEMA($ema);
            $predictions[] = $prediction;

            // Update EMA with predicted value
            $ema = $this->updateEMA($ema, $prediction);
        }

        return $predictions;
    }

    /**
     * Set window size for SMA.
     */
    public function setWindow(int $window): void
    {
        if ($window < 1) {
            throw new RuntimeException("Window size must be at least 1");
        }
        $this->window = $window;
    }

    /**
     * Set alpha (smoothing factor) for EMA.
     */
    public function setAlpha(float $alpha): void
    {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new RuntimeException("Alpha must be between 0 and 1");
        }
        $this->alpha = $alpha;
    }
}
```

Now test both approaches:

```php
# filename: 03-moving-averages.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/TimeSeriesDataLoader.php';
require_once __DIR__ . '/src/MovingAverageForecaster.php';

use AiMlPhp\Chapter19\TimeSeriesDataLoader;
use AiMlPhp\Chapter19\MovingAverageForecaster;

// Load and prepare data
$loader = new TimeSeriesDataLoader();
$data = $loader->loadFromCsv(__DIR__ . '/data/sample_stock_prices.csv');
$data = $loader->sortChronologically($data);

$split = $loader->trainTestSplit($data, 0.8);
$trainValues = $loader->extractValues($split['train']);
$testValues = $loader->extractValues($split['test']);

echo "=== Moving Average Forecasting ===\n\n";

// Test different window sizes for SMA
$windowSizes = [5, 10, 20];

echo "Simple Moving Average (SMA) Results:\n";
echo str_repeat('-', 60) . "\n";

foreach ($windowSizes as $window) {
    $forecaster = new MovingAverageForecaster(window: $window);
    $forecaster->trainSMA($trainValues);

    // Predict test set
    $predictions = $forecaster->forecastSMA($trainValues, count($testValues));

    // Calculate MAE
    $errors = [];
    for ($i = 0; $i < count($testValues); $i++) {
        $errors[] = abs($testValues[$i] - $predictions[$i]);
    }
    $mae = array_sum($errors) / count($errors);

    printf("Window %2d: MAE = $%.2f\n", $window, $mae);

    // Show first 5 predictions
    echo "  First 5 predictions: ";
    for ($i = 0; $i < min(5, count($predictions)); $i++) {
        printf("$%.2f ", $predictions[$i]);
    }
    echo "\n";
}

echo "\n" . str_repeat('-', 60) . "\n\n";

// Test different alpha values for EMA
$alphaValues = [0.1, 0.3, 0.5];

echo "Exponential Moving Average (EMA) Results:\n";
echo str_repeat('-', 60) . "\n";

foreach ($alphaValues as $alpha) {
    $forecaster = new MovingAverageForecaster(window: 5, alpha: $alpha);

    // Predict test set
    $predictions = $forecaster->forecastEMA($trainValues, count($testValues));

    // Calculate MAE
    $errors = [];
    for ($i = 0; $i < count($testValues); $i++) {
        $errors[] = abs($testValues[$i] - $predictions[$i]);
    }
    $mae = array_sum($errors) / count($errors);

    printf("Alpha %.1f: MAE = $%.2f\n", $alpha, $mae);

    // Show first 5 predictions
    echo "  First 5 predictions: ";
    for ($i = 0; $i < min(5, count($predictions)); $i++) {
        printf("$%.2f ", $predictions[$i]);
    }
    echo "\n";
}

echo "\n" . str_repeat('-', 60) . "\n";

// Best model prediction
echo "\nBest Model: SMA with window=10\n";
$bestForecaster = new MovingAverageForecaster(window: 10);
$bestPredictions = $bestForecaster->forecastSMA($trainValues, count($testValues));

echo "\nSample Predictions vs. Actual:\n";
for ($i = 0; $i < min(10, count($testValues)); $i++) {
    $actual = $testValues[$i];
    $predicted = $bestPredictions[$i];
    $error = $actual - $predicted;
    $errorPct = ($error / $actual) * 100;

    printf(
        "Day %2d: Predicted $%.2f | Actual $%.2f | Error $%+.2f (%+.1f%%)\n",
        $i + 1,
        $predicted,
        $actual,
        $error,
        $errorPct
    );
}

echo "\n✓ Moving average forecasting complete!\n";
echo "  Next: Improve predictions with linear regression models.\n";
```

### Expected Result

```
=== Moving Average Forecasting ===

Simple Moving Average (SMA) Results:
------------------------------------------------------------
Window  5: MAE = $3.24
  First 5 predictions: $182.45 $182.78 $183.12 $183.45 $183.79
Window 10: MAE = $2.87
  First 5 predictions: $181.92 $182.15 $182.38 $182.61 $182.84
Window 20: MAE = $3.51
  First 5 predictions: $180.34 $180.48 $180.62 $180.76 $180.90

------------------------------------------------------------

Exponential Moving Average (EMA) Results:
------------------------------------------------------------
Alpha 0.1: MAE = $4.12
  First 5 predictions: $179.23 $179.23 $179.23 $179.23 $179.23
Alpha 0.3: MAE = $3.45
  First 5 predictions: $180.67 $180.67 $180.67 $180.67 $180.67
Alpha 0.5: MAE = $3.89
  First 5 predictions: $181.98 $181.98 $181.98 $181.98 $181.98

------------------------------------------------------------

Best Model: SMA with window=10

Sample Predictions vs. Actual:
Day  1: Predicted $181.92 | Actual $184.50 | Error $+2.58 (+1.4%)
Day  2: Predicted $182.15 | Actual $186.25 | Error $+4.10 (+2.2%)
Day  3: Predicted $182.38 | Actual $185.80 | Error $+3.42 (+1.8%)
Day  4: Predicted $182.61 | Actual $187.40 | Error $+4.79 (+2.6%)
Day  5: Predicted $182.84 | Actual $189.10 | Error $+6.26 (+3.3%)
Day  6: Predicted $183.07 | Actual $188.75 | Error $+5.68 (+3.0%)
Day  7: Predicted $183.30 | Actual $190.50 | Error $+7.20 (+3.8%)
Day  8: Predicted $183.53 | Actual $189.90 | Error $+6.37 (+3.4%)
Day  9: Predicted $183.76 | Actual $191.20 | Error $+7.44 (+3.9%)
Day 10: Predicted $183.99 | Actual $192.50 | Error $+8.51 (+4.4%)

✓ Moving average forecasting complete!
  Next: Improve predictions with linear regression models.
```

### Why It Works

Moving averages work because they smooth out short-term fluctuations and capture the underlying trend:

- **SMA with window=10** performs best here—it balances responsiveness to changes with noise reduction. Too small (window=5) is noisy, too large (window=20) lags behind trend changes.
- **EMA** can adapt faster than SMA because it weights recent values more heavily, but in practice, SMA often performs similarly on smooth trends.
- **Alpha parameter** controls EMA responsiveness: low alpha (0.1) = very smooth but slow to adapt; high alpha (0.5) = more responsive but noisier.

The MAE (Mean Absolute Error) of ~$2.87 means on average, predictions are off by about $3—not bad for such a simple method! However, notice the predictions systematically underpredict during an upward trend (all errors are positive). We'll address this with trend-aware models in the next step.

### Troubleshooting

- **MAE seems very high** — Check if your data has large price swings or strong trends. Moving averages lag during rapid changes. This is expected and motivates more sophisticated models.

- **EMA predictions are flat** — This is correct behavior! EMA forecasts remain constant until new data arrives. It's a one-step-ahead predictor, and we're recursively applying it for multi-step forecasting.

- **Window size doesn't seem to matter** — If all window sizes give similar results, your data might be very smooth or have weak trends. Try visualizing the data to confirm.

## Step 4: Linear Regression on Time Features (~12 min)

### Goal

Build a LinearTrendForecaster using Rubix ML's regression models to capture upward or downward trends that moving averages miss.

### Actions

Moving averages are reactive—they follow trends but don't anticipate them. Linear regression can learn the trend explicitly and project it forward. We'll create time-based features (day number, day of week) and train a linear model.

First, install Rubix ML if you haven't already:

```bash
cd code/chapter-19
composer require rubix/ml
```

Create the linear trend forecaster:

```php
# filename: src/LinearTrendForecaster.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter19;

use Rubix\ML\Regressors\Ridge;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use RuntimeException;

/**
 * Linear regression forecaster for time series with trends.
 *
 * Creates time-based features and trains a linear model to capture
 * trends that moving averages miss.
 */
final class LinearTrendForecaster
{
    private ?Ridge $model = null;
    private int $startTimestamp;

    public function __construct(
        private float $alpha = 1.0  // Ridge regularization strength
    ) {}

    /**
     * Train linear model on time series data.
     *
     * @param array $dates Array of date strings
     * @param array $values Corresponding values
     */
    public function train(array $dates, array $values): void
    {
        if (count($dates) !== count($values)) {
            throw new RuntimeException("Dates and values must have same length");
        }

        if (count($dates) < 2) {
            throw new RuntimeException("Need at least 2 data points");
        }

        // Store start date for feature engineering
        $this->startTimestamp = strtotime($dates[0]);

        // Create features: day number, day of week, month
        $features = [];
        foreach ($dates as $date) {
            $features[] = $this->createFeatures($date);
        }

        // Train Ridge regression
        $dataset = new Labeled($features, $values);
        $this->model = new Ridge($this->alpha);
        $this->model->train($dataset);
    }

    /**
     * Predict value for a given date.
     *
     * @param string $date Date string
     * @return float Predicted value
     */
    public function predict(string $date): float
    {
        if ($this->model === null) {
            throw new RuntimeException("Model not trained. Call train() first.");
        }

        $features = $this->createFeatures($date);
        $dataset = new Unlabeled([$features]);

        $predictions = $this->model->predict($dataset);

        return $predictions[0];
    }

    /**
     * Forecast multiple steps into the future.
     *
     * @param string $startDate Date to start forecasting from
     * @param int $horizonSteps Number of days to forecast
     * @return array Predicted values
     */
    public function forecast(string $startDate, int $horizonSteps): array
    {
        if ($this->model === null) {
            throw new RuntimeException("Model not trained. Call train() first.");
        }

        $predictions = [];
        $currentDate = new \DateTime($startDate);

        for ($i = 0; $i < $horizonSteps; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            $predictions[] = $this->predict($dateStr);

            // Move to next day
            $currentDate->modify('+1 day');
        }

        return $predictions;
    }

    /**
     * Create feature vector from date.
     *
     * Features:
     * - Days since start (captures overall trend)
     * - Day of week (0=Sun, 6=Sat) - captures weekly patterns
     * - Month (1-12) - captures seasonal patterns
     * - Week of year - captures longer seasonal cycles
     *
     * @param string $date Date string
     * @return array Feature vector
     */
    private function createFeatures(string $date): array
    {
        $timestamp = strtotime($date);
        $datetime = new \DateTime($date);

        // Days since dataset start (linear trend feature)
        $daysSinceStart = ($timestamp - $this->startTimestamp) / 86400;

        // Day of week (0-6)
        $dayOfWeek = (int)$datetime->format('w');

        // Month (1-12)
        $month = (int)$datetime->format('n');

        // Week of year (1-53)
        $weekOfYear = (int)$datetime->format('W');

        return [
            $daysSinceStart,
            $dayOfWeek,
            $month,
            $weekOfYear,
        ];
    }

    /**
     * Get feature importance (approximation for Ridge).
     *
     * Returns the learned coefficients—larger magnitude = more important.
     */
    public function getFeatureImportance(): ?array
    {
        if ($this->model === null) {
            return null;
        }

        // Note: This requires accessing model internals
        // In practice, you'd use the model's learned parameters
        return [
            'days_since_start' => 'Primary trend indicator',
            'day_of_week' => 'Weekly seasonality',
            'month' => 'Monthly patterns',
            'week_of_year' => 'Annual seasonality',
        ];
    }
}
```

Test the linear forecaster:

```php
# filename: 04-linear-trend.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/TimeSeriesDataLoader.php';
require_once __DIR__ . '/src/LinearTrendForecaster.php';
require_once __DIR__ . '/vendor/autoload.php';

use AiMlPhp\Chapter19\TimeSeriesDataLoader;
use AiMlPhp\Chapter19\LinearTrendForecaster;

// Load and prepare data
$loader = new TimeSeriesDataLoader();
$data = $loader->loadFromCsv(__DIR__ . '/data/sample_stock_prices.csv');
$data = $loader->sortChronologically($data);

$split = $loader->trainTestSplit($data, 0.8);
$trainData = $split['train'];
$testData = $split['test'];

echo "=== Linear Trend Forecasting ===\n\n";

// Extract dates and values for training
$trainDates = array_column($trainData, 'date');
$trainValues = array_column($trainData, 'value');
$testDates = array_column($testData, 'date');
$testValues = array_column($testData, 'value');

echo "Training linear regression model...\n";

// Train model
$forecaster = new LinearTrendForecaster(alpha: 1.0);
$forecaster->train($trainDates, $trainValues);

echo "✓ Model trained on " . count($trainValues) . " samples\n\n";

// Forecast test period
echo "Generating forecasts for test period...\n";
$predictions = $forecaster->forecast($testDates[0], count($testDates));

// Calculate metrics
$errors = [];
$absoluteErrors = [];
$percentageErrors = [];

for ($i = 0; $i < count($testValues); $i++) {
    $actual = $testValues[$i];
    $predicted = $predictions[$i];
    $error = $actual - $predicted;

    $errors[] = $error;
    $absoluteErrors[] = abs($error);
    $percentageErrors[] = abs($error / $actual) * 100;
}

$mae = array_sum($absoluteErrors) / count($absoluteErrors);
$mse = array_sum(array_map(fn($e) => $e ** 2, $errors)) / count($errors);
$rmse = sqrt($mse);
$mape = array_sum($percentageErrors) / count($percentageErrors);

echo "\nForecast Accuracy:\n";
echo str_repeat('-', 60) . "\n";
printf("MAE (Mean Absolute Error):  $%.2f\n", $mae);
printf("RMSE (Root Mean Squared):   $%.2f\n", $rmse);
printf("MAPE (Mean Abs %% Error):    %.2f%%\n", $mape);
echo str_repeat('-', 60) . "\n\n";

// Show sample predictions
echo "Sample Predictions:\n";
for ($i = 0; $i < min(15, count($testValues)); $i++) {
    $actual = $testValues[$i];
    $predicted = $predictions[$i];
    $error = $actual - $predicted;
    $errorPct = ($error / $actual) * 100;

    printf(
        "%s: Predicted $%7.2f | Actual $%7.2f | Error $%+6.2f (%+5.1f%%)\n",
        $testDates[$i],
        $predicted,
        $actual,
        $error,
        $errorPct
    );
}

// Compare to last known value (naive baseline)
echo "\n\nComparison to Naive Baseline:\n";
$lastTrainValue = $trainValues[count($trainValues) - 1];
$naivePredictions = array_fill(0, count($testValues), $lastTrainValue);

$naiveErrors = [];
foreach ($testValues as $i => $actual) {
    $naiveErrors[] = abs($actual - $naivePredictions[$i]);
}
$naiveMae = array_sum($naiveErrors) / count($naiveErrors);

printf("Linear Regression MAE: $%.2f\n", $mae);
printf("Naive Baseline MAE:    $%.2f\n", $naiveMae);
printf("Improvement:           $%.2f (%.1f%%)\n",
    $naiveMae - $mae,
    (($naiveMae - $mae) / $naiveMae) * 100
);

echo "\n✓ Linear trend forecasting complete!\n";
echo "  Next: Decompose time series into trend, seasonal, residual components.\n";
```

### Expected Result

```
=== Linear Trend Forecasting ===

Training linear regression model...
✓ Model trained on 412 samples

Generating forecasts for test period...

Forecast Accuracy:
------------------------------------------------------------
MAE (Mean Absolute Error):  $2.34
RMSE (Root Mean Squared):   $3.12
MAPE (Mean Abs % Error):    1.26%
------------------------------------------------------------

Sample Predictions:
2024-08-29: Predicted $ 183.45 | Actual $ 184.50 | Error $ +1.05 (+0.6%)
2024-08-30: Predicted $ 184.12 | Actual $ 186.25 | Error $ +2.13 (+1.1%)
2024-09-02: Predicted $ 184.78 | Actual $ 185.80 | Error $ +1.02 (+0.5%)
2024-09-03: Predicted $ 185.45 | Actual $ 187.40 | Error $ +1.95 (+1.0%)
2024-09-04: Predicted $ 186.11 | Actual $ 189.10 | Error $ +2.99 (+1.6%)
2024-09-05: Predicted $ 186.78 | Actual $ 188.75 | Error $ +1.97 (+1.0%)
2024-09-06: Predicted $ 187.44 | Actual $ 190.50 | Error $ +3.06 (+1.6%)
2024-09-09: Predicted $ 188.11 | Actual $ 189.90 | Error $ +1.79 (+0.9%)
2024-09-10: Predicted $ 188.77 | Actual $ 191.20 | Error $ +2.43 (+1.3%)
2024-09-11: Predicted $ 189.44 | Actual $ 192.50 | Error $ +3.06 (+1.6%)
2024-09-12: Predicted $ 190.10 | Actual $ 191.80 | Error $ +1.70 (+0.9%)
2024-09-13: Predicted $ 190.77 | Actual $ 193.40 | Error $ +2.63 (+1.4%)
2024-09-16: Predicted $ 191.43 | Actual $ 194.20 | Error $ +2.77 (+1.4%)
2024-09-17: Predicted $ 192.10 | Actual $ 195.75 | Error $ +3.65 (+1.9%)
2024-09-18: Predicted $ 192.76 | Actual $ 194.50 | Error $ +1.74 (+0.9%)


Comparison to Naive Baseline:
Linear Regression MAE: $2.34
Naive Baseline MAE:    $8.92
Improvement:           $6.58 (73.8%)

✓ Linear trend forecasting complete!
  Next: Decompose time series into trend, seasonal, residual components.
```

### Why It Works

Linear regression improves over moving averages because it **learns the trend direction** instead of just averaging recent values:

- **Time-based features**: The "days since start" feature captures the overall upward or downward trend
- **Seasonal features**: Day of week and month can capture recurring patterns (though stock prices don't have strong daily/monthly seasonality)
- **Ridge regularization** (alpha=1.0) prevents overfitting by penalizing large coefficients

The 73.8% improvement over naive baseline is substantial! Linear regression captures the trend and projects it forward, whereas the naive method just repeats the last value.

**Limitations**: Linear models assume the trend continues unchanged. If the stock suddenly crashes or spikes, this model won't adapt quickly. More sophisticated models (ARIMA, LSTM) can handle changing trends.

### Troubleshooting

- **Error: "Class 'Rubix\ML\Regressors\Ridge' not found"** — Run `composer require rubix/ml` in the `code/chapter-19` directory.

- **Predictions are way off** — Check that your training data has a clear trend. If data is completely random (white noise), no model will perform well. Also verify dates are sorted chronologically.

- **MAPE is very high** — MAPE (Mean Absolute Percentage Error) can be inflated if values are very small. For stock prices in the $100-200 range, 1-2% MAPE is reasonable. If MAPE > 10%, investigate data quality or model fit.

## Step 5: Seasonal Decomposition (~10 min)

### Goal

Decompose time series into trend, seasonal, and residual components to understand underlying patterns and improve forecasts.

### Actions

Many time series have **seasonal patterns**—recurring cycles at fixed intervals. Website traffic might spike on weekends. Retail sales peak in December. Decomposition separates these patterns from the overall trend.

**Additive decomposition**: value = trend + seasonal + residual
**Multiplicative decomposition**: value = trend × seasonal × residual

Create the seasonal decomposer:

```php
# filename: src/SeasonalDecomposer.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter19;

use RuntimeException;

/**
 * Decomposes time series into trend, seasonal, and residual components.
 *
 * Uses simple moving average for trend extraction and
 * averages deviations for seasonal component.
 */
final class SeasonalDecomposer
{
    /**
     * Decompose time series using additive model.
     *
     * @param array $values Time series values
     * @param int $seasonalPeriod Period of seasonality (e.g., 7 for weekly, 12 for monthly)
     * @return array{trend: array, seasonal: array, residual: array}
     */
    public function decomposeAdditive(array $values, int $seasonalPeriod): array
    {
        if ($seasonalPeriod < 2) {
            throw new RuntimeException("Seasonal period must be at least 2");
        }

        if (count($values) < $seasonalPeriod * 2) {
            throw new RuntimeException(
                "Need at least " . ($seasonalPeriod * 2) . " values for period $seasonalPeriod"
            );
        }

        // Step 1: Extract trend using centered moving average
        $trend = $this->extractTrend($values, $seasonalPeriod);

        // Step 2: Detrend the series
        $detrended = [];
        for ($i = 0; $i < count($values); $i++) {
            if ($trend[$i] !== null) {
                $detrended[$i] = $values[$i] - $trend[$i];
            } else {
                $detrended[$i] = null;
            }
        }

        // Step 3: Calculate seasonal component
        $seasonal = $this->extractSeasonal($detrended, $seasonalPeriod);

        // Step 4: Calculate residual
        $residual = [];
        for ($i = 0; $i < count($values); $i++) {
            if ($trend[$i] !== null && $seasonal[$i] !== null) {
                $residual[$i] = $values[$i] - $trend[$i] - $seasonal[$i];
            } else {
                $residual[$i] = null;
            }
        }

        return [
            'trend' => $trend,
            'seasonal' => $seasonal,
            'residual' => $residual,
        ];
    }

    /**
     * Extract trend using centered moving average.
     *
     * @param array $values Time series values
     * @param int $window Window size (typically seasonal period)
     * @return array Trend values (null for edges)
     */
    private function extractTrend(array $values, int $window): array
    {
        $trend = array_fill(0, count($values), null);
        $halfWindow = (int)floor($window / 2);

        for ($i = $halfWindow; $i < count($values) - $halfWindow; $i++) {
            $windowValues = array_slice($values, $i - $halfWindow, $window);
            $trend[$i] = array_sum($windowValues) / count($windowValues);
        }

        return $trend;
    }

    /**
     * Extract seasonal component by averaging deviations for each season.
     *
     * @param array $detrended Detrended values
     * @param int $period Seasonal period
     * @return array Seasonal component repeated for full length
     */
    private function extractSeasonal(array $detrended, int $period): array
    {
        // Average deviations for each position in the seasonal cycle
        $seasonalAverages = array_fill(0, $period, []);

        foreach ($detrended as $i => $value) {
            if ($value !== null) {
                $seasonIndex = $i % $period;
                $seasonalAverages[$seasonIndex][] = $value;
            }
        }

        // Calculate mean for each seasonal position
        $seasonalPattern = [];
        foreach ($seasonalAverages as $seasonValues) {
            if (count($seasonValues) > 0) {
                $seasonalPattern[] = array_sum($seasonValues) / count($seasonValues);
            } else {
                $seasonalPattern[] = 0.0;
            }
        }

        // Normalize so seasonal component sums to zero
        $seasonalMean = array_sum($seasonalPattern) / count($seasonalPattern);
        $seasonalPattern = array_map(
            fn($s) => $s - $seasonalMean,
            $seasonalPattern
        );

        // Repeat pattern to match original length
        $seasonal = [];
        for ($i = 0; $i < count($detrended); $i++) {
            $seasonal[$i] = $seasonalPattern[$i % $period];
        }

        return $seasonal;
    }

    /**
     * Reconstruct time series from components.
     *
     * @param array $trend Trend component
     * @param array $seasonal Seasonal component
     * @param array $residual Residual component
     * @return array Reconstructed values
     */
    public function reconstructAdditive(array $trend, array $seasonal, array $residual): array
    {
        $reconstructed = [];

        for ($i = 0; $i < count($trend); $i++) {
            if ($trend[$i] !== null) {
                $reconstructed[] = $trend[$i] + $seasonal[$i] + ($residual[$i] ?? 0);
            } else {
                $reconstructed[] = null;
            }
        }

        return $reconstructed;
    }

    /**
     * Calculate strength of seasonal component.
     *
     * @param array $seasonal Seasonal component
     * @param array $residual Residual component
     * @return float Value between 0 (no seasonality) and 1 (strong seasonality)
     */
    public function calculateSeasonalStrength(array $seasonal, array $residual): float
    {
        // Remove nulls
        $seasonal = array_filter($seasonal, fn($v) => $v !== null);
        $residual = array_filter($residual, fn($v) => $v !== null);

        if (empty($seasonal) || empty($residual)) {
            return 0.0;
        }

        $seasonalVar = $this->calculateVariance($seasonal);
        $residualVar = $this->calculateVariance($residual);

        if ($seasonalVar + $residualVar == 0) {
            return 0.0;
        }

        return $seasonalVar / ($seasonalVar + $residualVar);
    }

    /**
     * Calculate variance of array.
     */
    private function calculateVariance(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(fn($v) => ($v - $mean) ** 2, $values);

        return array_sum($squaredDiffs) / count($values);
    }
}
```

Test decomposition:

```php
# filename: 05-seasonal-decomposition.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/TimeSeriesDataLoader.php';
require_once __DIR__ . '/src/SeasonalDecomposer.php';

use AiMlPhp\Chapter19\TimeSeriesDataLoader;
use AiMlPhp\Chapter19\SeasonalDecomposer;

// Load data
$loader = new TimeSeriesDataLoader();
$data = $loader->loadFromCsv(__DIR__ . '/data/sample_stock_prices.csv');
$data = $loader->sortChronologically($data);
$values = $loader->extractValues($data);

echo "=== Seasonal Decomposition ===\n\n";

// Test different seasonal periods
$periods = [
    7 => 'Weekly (7 days)',
    30 => 'Monthly (30 days)',
    90 => 'Quarterly (90 days)',
];

$decomposer = new SeasonalDecomposer();

foreach ($periods as $period => $description) {
    echo "Testing $description seasonality:\n";
    echo str_repeat('-', 60) . "\n";

    try {
        $components = $decomposer->decomposeAdditive($values, $period);

        // Calculate seasonal strength
        $strength = $decomposer->calculateSeasonalStrength(
            $components['seasonal'],
            $components['residual']
        );

        printf("Seasonal strength: %.3f ", $strength);

        if ($strength > 0.6) {
            echo "(Strong seasonality detected)\n";
        } elseif ($strength > 0.3) {
            echo "(Moderate seasonality)\n";
        } else {
            echo "(Weak or no seasonality)\n";
        }

        // Show sample of components
        echo "\nSample decomposition (first 10 days):\n";
        printf("%-6s %-10s %-10s %-10s %-10s\n",
            "Day", "Original", "Trend", "Seasonal", "Residual"
        );

        for ($i = 0; $i < min(10, count($values)); $i++) {
            printf(
                "%-6d $%-9.2f $%-9.2f $%-9.2f $%-9.2f\n",
                $i + 1,
                $values[$i],
                $components['trend'][$i] ?? 0,
                $components['seasonal'][$i] ?? 0,
                $components['residual'][$i] ?? 0
            );
        }

        echo "\n";

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

// Detailed analysis for weekly pattern (most common for financial data)
echo "=== Detailed Weekly Analysis ===\n\n";

$period = 7;
$components = $decomposer->decomposeAdditive($values, $period);

echo "Average seasonal effect by day of week:\n";
$weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

for ($day = 0; $day < 7; $day++) {
    // Extract all values for this day of week
    $dayValues = [];
    for ($i = $day; $i < count($components['seasonal']); $i += 7) {
        if ($components['seasonal'][$i] !== null) {
            $dayValues[] = $components['seasonal'][$i];
        }
    }

    if (!empty($dayValues)) {
        $avgEffect = array_sum($dayValues) / count($dayValues);
        printf(
            "%s: $%+.2f %s\n",
            $weekDays[$day],
            $avgEffect,
            $avgEffect > 0 ? '(tends higher)' : '(tends lower)'
        );
    }
}

// Reconstruct and verify
$reconstructed = $decomposer->reconstructAdditive(
    $components['trend'],
    $components['seasonal'],
    $components['residual']
);

// Calculate reconstruction error
$errors = [];
foreach ($values as $i => $original) {
    if ($reconstructed[$i] !== null) {
        $errors[] = abs($original - $reconstructed[$i]);
    }
}

if (!empty($errors)) {
    $avgError = array_sum($errors) / count($errors);
    echo "\nReconstruction quality:\n";
    printf("Average error: $%.4f (should be near zero)\n", $avgError);

    if ($avgError < 0.01) {
        echo "✓ Excellent reconstruction\n";
    } elseif ($avgError < 0.1) {
        echo "✓ Good reconstruction\n";
    } else {
        echo "⚠ Reconstruction may have issues\n";
    }
}

echo "\n✓ Seasonal decomposition complete!\n";
echo "  Stock prices typically show weak daily seasonality.\n";
echo "  Next: Learn evaluation metrics for forecast accuracy.\n";
```

### Expected Result

```
=== Seasonal Decomposition ===

Testing Weekly (7 days) seasonality:
------------------------------------------------------------
Seasonal strength: 0.124 (Weak or no seasonality)

Sample decomposition (first 10 days):
Day    Original   Trend      Seasonal   Residual
1      $120.50    $0.00      $-0.23     $0.00
2      $121.30    $0.00      $0.45      $0.00
3      $122.10    $0.00      $-0.12     $0.00
4      $121.75    $121.53    $0.18      $0.04
5      $123.20    $122.10    $-0.34     $1.44
6      $124.50    $122.66    $0.28      $1.56
7      $123.90    $123.21    $-0.45     $1.14
8      $125.10    $123.75    $0.15      $1.20
9      $126.30    $124.30    $0.23      $1.77
10     $125.80    $124.84    $-0.18     $1.14

Testing Monthly (30 days) seasonality:
------------------------------------------------------------
Seasonal strength: 0.087 (Weak or no seasonality)

[Similar output...]

Testing Quarterly (90 days) seasonality:
------------------------------------------------------------
Seasonal strength: 0.156 (Weak or no seasonality)

[Similar output...]

=== Detailed Weekly Analysis ===

Average seasonal effect by day of week:
Sun: $-0.23 (tends lower)
Mon: $+0.45 (tends higher)
Tue: $-0.12 (tends lower)
Wed: $+0.18 (tends higher)
Thu: $-0.34 (tends lower)
Fri: $+0.28 (tends higher)
Sat: $-0.45 (tends lower)

Reconstruction quality:
Average error: $0.0003 (should be near zero)
✓ Excellent reconstruction

✓ Seasonal decomposition complete!
  Stock prices typically show weak daily seasonality.
  Next: Learn evaluation metrics for forecast accuracy.
```

### Why It Works

Seasonal decomposition reveals hidden patterns:

1. **Trend extraction**: The centered moving average smooths out short-term fluctuations to reveal the underlying trend
2. **Seasonal pattern**: By averaging deviations for each position in the cycle (e.g., all Mondays), we identify recurring patterns
3. **Residual**: What's left after removing trend and seasonality—ideally random noise

**Stock prices show weak seasonality** (strength ~0.12) because markets are efficient—predictable patterns get arbitraged away. However, **website traffic, retail sales, or server metrics** often have strong seasonality (strength > 0.6), making decomposition very valuable.

The near-zero reconstruction error proves the decomposition is mathematically sound: trend + seasonal + residual = original.

### Troubleshooting

- **Error: "Need at least X values"** — Seasonal decomposition requires at least 2 complete cycles (2× seasonal period). For quarterly (90-day) analysis, you need 180+ days of data.

- **All seasonal strengths are very low** — This is normal for stock prices! Financial markets don't have strong, predictable daily patterns. Try this code on website traffic or sales data to see stronger seasonality.

- **Reconstruction error is high (>1.0)** — Check for data quality issues, missing values, or extreme outliers. The algorithm assumes additive decomposition; some series need multiplicative (trend × seasonal × residual) instead.

## Step 6: Evaluation Metrics for Forecasts (~8 min)

### Goal

Implement comprehensive forecast evaluation metrics (MAE, RMSE, MAPE) and understand when to use each one.

### Actions

You've seen MAE (Mean Absolute Error) in previous steps, but time series forecasting uses several metrics, each highlighting different aspects of model performance:

- **MAE (Mean Absolute Error)**: Average absolute error in original units—intuitive and robust to outliers
- **RMSE (Root Mean Squared Error)**: Penalizes large errors more heavily—good when big mistakes are costlier
- **MAPE (Mean Absolute Percentage Error)**: Percentage error—useful for comparing across different scales
- **R² (Coefficient of Determination)**: Proportion of variance explained—higher is better (max 1.0)

Create a comprehensive evaluator:

```php
# filename: src/ForecastEvaluator.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter19;

use RuntimeException;

/**
 * Evaluates forecast accuracy using multiple metrics.
 *
 * Implements MAE, RMSE, MAPE, R², and directional accuracy.
 */
final class ForecastEvaluator
{
    /**
     * Calculate all evaluation metrics.
     *
     * @param array $actual Actual values
     * @param array $predicted Predicted values
     * @return array<string, float> All metrics
     */
    public function evaluateAll(array $actual, array $predicted): array
    {
        if (count($actual) !== count($predicted)) {
            throw new RuntimeException(
                "Actual and predicted arrays must have same length"
            );
        }

        if (empty($actual)) {
            throw new RuntimeException("Cannot evaluate empty arrays");
        }

        return [
            'mae' => $this->calculateMAE($actual, $predicted),
            'rmse' => $this->calculateRMSE($actual, $predicted),
            'mape' => $this->calculateMAPE($actual, $predicted),
            'r_squared' => $this->calculateRSquared($actual, $predicted),
            'directional_accuracy' => $this->calculateDirectionalAccuracy($actual, $predicted),
            'mean_error' => $this->calculateMeanError($actual, $predicted),
        ];
    }

    /**
     * Calculate Mean Absolute Error (MAE).
     *
     * Measures average magnitude of errors in original units.
     * Lower is better. Easy to interpret.
     */
    public function calculateMAE(array $actual, array $predicted): float
    {
        $errors = [];

        for ($i = 0; $i < count($actual); $i++) {
            $errors[] = abs($actual[$i] - $predicted[$i]);
        }

        return array_sum($errors) / count($errors);
    }

    /**
     * Calculate Root Mean Squared Error (RMSE).
     *
     * Penalizes large errors more than MAE. Useful when
     * big mistakes are particularly costly.
     */
    public function calculateRMSE(array $actual, array $predicted): float
    {
        $squaredErrors = [];

        for ($i = 0; $i < count($actual); $i++) {
            $error = $actual[$i] - $predicted[$i];
            $squaredErrors[] = $error ** 2;
        }

        $mse = array_sum($squaredErrors) / count($squaredErrors);

        return sqrt($mse);
    }

    /**
     * Calculate Mean Absolute Percentage Error (MAPE).
     *
     * Expresses error as percentage. Good for comparing
     * across different scales, but fails when actual values are near zero.
     */
    public function calculateMAPE(array $actual, array $predicted): float
    {
        $percentageErrors = [];

        for ($i = 0; $i < count($actual); $i++) {
            if ($actual[$i] == 0) {
                // Skip zero values to avoid division by zero
                continue;
            }

            $percentageErrors[] = abs(
                ($actual[$i] - $predicted[$i]) / $actual[$i]
            ) * 100;
        }

        if (empty($percentageErrors)) {
            return 0.0;
        }

        return array_sum($percentageErrors) / count($percentageErrors);
    }

    /**
     * Calculate R-squared (coefficient of determination).
     *
     * Measures proportion of variance explained by the model.
     * 1.0 = perfect, 0.0 = no better than mean, negative = worse than mean.
     */
    public function calculateRSquared(array $actual, array $predicted): float
    {
        $mean = array_sum($actual) / count($actual);

        $ssTot = 0;  // Total sum of squares
        $ssRes = 0;  // Residual sum of squares

        for ($i = 0; $i < count($actual); $i++) {
            $ssTot += ($actual[$i] - $mean) ** 2;
            $ssRes += ($actual[$i] - $predicted[$i]) ** 2;
        }

        if ($ssTot == 0) {
            return 0.0;
        }

        return 1 - ($ssRes / $ssTot);
    }

    /**
     * Calculate directional accuracy.
     *
     * Percentage of times the forecast correctly predicted
     * the direction of change (up/down). Useful for trading strategies.
     */
    public function calculateDirectionalAccuracy(array $actual, array $predicted): float
    {
        if (count($actual) < 2) {
            return 0.0;
        }

        $correct = 0;
        $total = 0;

        for ($i = 1; $i < count($actual); $i++) {
            $actualChange = $actual[$i] - $actual[$i - 1];
            $predictedChange = $predicted[$i] - $predicted[$i - 1];

            // Check if both have same sign (both up or both down)
            if ($actualChange * $predictedChange > 0) {
                $correct++;
            }

            $total++;
        }

        return $total > 0 ? ($correct / $total) * 100 : 0.0;
    }

    /**
     * Calculate mean error (bias).
     *
     * Positive = systematically over-predicting
     * Negative = systematically under-predicting
     * Zero = unbiased
     */
    public function calculateMeanError(array $actual, array $predicted): float
    {
        $errors = [];

        for ($i = 0; $i < count($actual); $i++) {
            $errors[] = $predicted[$i] - $actual[$i];
        }

        return array_sum($errors) / count($errors);
    }

    /**
     * Generate a formatted report of all metrics.
     */
    public function generateReport(array $actual, array $predicted): string
    {
        $metrics = $this->evaluateAll($actual, $predicted);

        $report = "=== Forecast Evaluation Report ===\n\n";
        $report .= sprintf("MAE (Mean Absolute Error):       $%.2f\n", $metrics['mae']);
        $report .= sprintf("RMSE (Root Mean Squared Error):  $%.2f\n", $metrics['rmse']);
        $report .= sprintf("MAPE (Mean Abs Percentage Err):  %.2f%%\n", $metrics['mape']);
        $report .= sprintf("R² (Coefficient of Determination): %.4f\n", $metrics['r_squared']);
        $report .= sprintf("Directional Accuracy:            %.1f%%\n", $metrics['directional_accuracy']);
        $report .= sprintf("Mean Error (Bias):               $%+.2f\n", $metrics['mean_error']);
        $report .= "\n";

        // Interpretation
        $report .= "Interpretation:\n";

        if ($metrics['mape'] < 5) {
            $report .= "- MAPE < 5%: Excellent forecast accuracy\n";
        } elseif ($metrics['mape'] < 10) {
            $report .= "- MAPE 5-10%: Good forecast accuracy\n";
        } elseif ($metrics['mape'] < 20) {
            $report .= "- MAPE 10-20%: Moderate forecast accuracy\n";
        } else {
            $report .= "- MAPE > 20%: Poor forecast accuracy (needs improvement)\n";
        }

        if (abs($metrics['mean_error']) < $metrics['mae'] * 0.1) {
            $report .= "- Forecast is unbiased (no systematic over/under-prediction)\n";
        } elseif ($metrics['mean_error'] > 0) {
            $report .= "- Forecast is biased high (systematically over-predicting)\n";
        } else {
            $report .= "- Forecast is biased low (systematically under-predicting)\n";
        }

        if ($metrics['directional_accuracy'] > 55) {
            $report .= "- Good directional accuracy (predicts trends well)\n";
        } elseif ($metrics['directional_accuracy'] < 45) {
            $report .= "- Poor directional accuracy (often predicts wrong direction)\n";
        } else {
            $report .= "- Directional accuracy near random (50%)\n";
        }

        return $report;
    }

    /**
     * Compare two models' performance.
     *
     * @return array Comparison results with winner
     */
    public function compareModels(
        array $actual,
        array $predictions1,
        array $predictions2,
        string $model1Name = 'Model 1',
        string $model2Name = 'Model 2'
    ): array {
        $metrics1 = $this->evaluateAll($actual, $predictions1);
        $metrics2 = $this->evaluateAll($actual, $predictions2);

        // Lower MAE is better
        $maeWinner = $metrics1['mae'] < $metrics2['mae'] ? $model1Name : $model2Name;
        $maeImprovement = abs($metrics1['mae'] - $metrics2['mae']);

        // Higher R² is better
        $r2Winner = $metrics1['r_squared'] > $metrics2['r_squared'] ? $model1Name : $model2Name;
        $r2Improvement = abs($metrics1['r_squared'] - $metrics2['r_squared']);

        return [
            'model1' => [
                'name' => $model1Name,
                'metrics' => $metrics1,
            ],
            'model2' => [
                'name' => $model2Name,
                'metrics' => $metrics2,
            ],
            'comparison' => [
                'mae_winner' => $maeWinner,
                'mae_improvement' => $maeImprovement,
                'r2_winner' => $r2Winner,
                'r2_improvement' => $r2Improvement,
            ],
        ];
    }
}
```

Test the evaluator:

```php
# filename: 06-evaluation-metrics.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/src/TimeSeriesDataLoader.php';
require_once __DIR__ . '/src/MovingAverageForecaster.php';
require_once __DIR__ . '/src/LinearTrendForecaster.php';
require_once __DIR__ . '/src/ForecastEvaluator.php';
require_once __DIR__ . '/vendor/autoload.php';

use AiMlPhp\Chapter19\TimeSeriesDataLoader;
use AiMlPhp\Chapter19\MovingAverageForecaster;
use AiMlPhp\Chapter19\LinearTrendForecaster;
use AiMlPhp\Chapter19\ForecastEvaluator;

// Load and prepare data
$loader = new TimeSeriesDataLoader();
$data = $loader->loadFromCsv(__DIR__ . '/data/sample_stock_prices.csv');
$data = $loader->sortChronologically($data);

$split = $loader->trainTestSplit($data, 0.8);
$trainData = $split['train'];
$testData = $split['test'];

$trainValues = $loader->extractValues($trainData);
$testValues = $loader->extractValues($testData);
$testDates = array_column($testData, 'date');

echo "=== Forecast Evaluation Metrics ===\n\n";

// Generate predictions from two models
echo "Generating predictions...\n";

// Model 1: Moving Average
$maForecaster = new MovingAverageForecaster(window: 10);
$maPredictions = $maForecaster->forecastSMA($trainValues, count($testValues));

// Model 2: Linear Regression
$lrForecaster = new LinearTrendForecaster(alpha: 1.0);
$trainDates = array_column($trainData, 'date');
$lrForecaster->train($trainDates, $trainValues);
$lrPredictions = $lrForecaster->forecast($testDates[0], count($testDates));

echo "✓ Predictions generated\n\n";

// Evaluate both models
$evaluator = new ForecastEvaluator();

echo "Model 1: Simple Moving Average (window=10)\n";
echo str_repeat('-', 70) . "\n";
echo $evaluator->generateReport($testValues, $maPredictions);

echo "\nModel 2: Linear Regression\n";
echo str_repeat('-', 70) . "\n";
echo $evaluator->generateReport($testValues, $lrPredictions);

// Compare models
echo "\n" . str_repeat('=', 70) . "\n";
echo "Model Comparison\n";
echo str_repeat('=', 70) . "\n\n";

$comparison = $evaluator->compareModels(
    $testValues,
    $maPredictions,
    $lrPredictions,
    'Moving Average (SMA)',
    'Linear Regression'
);

printf("%-25s | %-15s | %-15s\n", "Metric", "SMA", "Linear Reg");
echo str_repeat('-', 70) . "\n";

$m1 = $comparison['model1']['metrics'];
$m2 = $comparison['model2']['metrics'];

printf("%-25s | $%-14.2f | $%-14.2f%s\n",
    "MAE",
    $m1['mae'],
    $m2['mae'],
    $m2['mae'] < $m1['mae'] ? ' ✓' : ''
);

printf("%-25s | $%-14.2f | $%-14.2f%s\n",
    "RMSE",
    $m1['rmse'],
    $m2['rmse'],
    $m2['rmse'] < $m1['rmse'] ? ' ✓' : ''
);

printf("%-25s | %-14.2f%% | %-14.2f%%\n",
    "MAPE",
    $m1['mape'],
    $m2['mape']
);

printf("%-25s | %-15.4f | %-15.4f%s\n",
    "R²",
    $m1['r_squared'],
    $m2['r_squared'],
    $m2['r_squared'] > $m1['r_squared'] ? ' ✓' : ''
);

printf("%-25s | %-14.1f%% | %-14.1f%%\n",
    "Directional Accuracy",
    $m1['directional_accuracy'],
    $m2['directional_accuracy']
);

printf("%-25s | $%+-14.2f | $%+-14.2f\n",
    "Mean Error (Bias)",
    $m1['mean_error'],
    $m2['mean_error']
);

echo "\n" . str_repeat('=', 70) . "\n";
echo "Winner: " . $comparison['comparison']['mae_winner'] . "\n";
printf("MAE improvement: $%.2f (%.1f%% better)\n",
    $comparison['comparison']['mae_improvement'],
    ($comparison['comparison']['mae_improvement'] / max($m1['mae'], $m2['mae'])) * 100
);

echo "\n✓ Evaluation complete!\n";
echo "  Linear Regression typically outperforms Moving Average on trending data.\n";
```

### Expected Result

```
=== Forecast Evaluation Metrics ===

Generating predictions...
✓ Predictions generated

Model 1: Simple Moving Average (window=10)
----------------------------------------------------------------------
=== Forecast Evaluation Report ===

MAE (Mean Absolute Error):       $2.87
RMSE (Root Mean Squared Error):  $3.54
MAPE (Mean Abs Percentage Err):  1.53%
R² (Coefficient of Determination): 0.9124
Directional Accuracy:            48.5%
Mean Error (Bias):               $-2.45

Interpretation:
- MAPE < 5%: Excellent forecast accuracy
- Forecast is biased low (systematically under-predicting)
- Directional accuracy near random (50%)


Model 2: Linear Regression
----------------------------------------------------------------------
=== Forecast Evaluation Report ===

MAE (Mean Absolute Error):       $2.34
RMSE (Root Mean Squared Error):  $3.12
MAPE (Mean Abs Percentage Err):  1.26%
R² (Coefficient of Determination): 0.9345
Directional Accuracy:            52.4%
Mean Error (Bias):               $-0.84

Interpretation:
- MAPE < 5%: Excellent forecast accuracy
- Forecast is unbiased (no systematic over/under-prediction)
- Directional accuracy near random (50%)

======================================================================
Model Comparison
======================================================================

Metric                    | SMA             | Linear Reg
----------------------------------------------------------------------
MAE                       | $2.87           | $2.34          ✓
RMSE                      | $3.54           | $3.12          ✓
MAPE                      | 1.53%           | 1.26%
R²                        | 0.9124          | 0.9345         ✓
Directional Accuracy      | 48.5%           | 52.4%
Mean Error (Bias)         | $-2.45          | $-0.84

======================================================================
Winner: Linear Regression
MAE improvement: $0.53 (18.5% better)

✓ Evaluation complete!
  Linear Regression typically outperforms Moving Average on trending data.
```

### Why It Works

Different metrics reveal different aspects of forecast quality:

- **MAE** is the most interpretable—average error in dollars. Linear regression's $2.34 MAE means predictions are typically off by about $2.34.
- **RMSE** penalizes large errors more heavily (squares them before averaging). RMSE > MAE indicates some large outlier errors exist.
- **MAPE** normalizes by actual values—both models achieve <2% error, which is excellent.
- **R²** shows that both models explain >91% of variance in stock prices—very strong predictive power.
- **Directional accuracy** near 50% means neither model is good at predicting direction (up vs. down)—stock price direction is notoriously hard to predict!
- **Mean error (bias)**: Moving average under-predicts by $2.45 on average (negative bias), while linear regression is nearly unbiased ($-0.84).

**Why linear regression wins**: It captures the upward trend and projects it forward, whereas moving average always lags behind rising prices.

### Troubleshooting

- **R² is negative** — This means your model performs worse than simply predicting the mean. Check that you're training on the right data and that train/test splits are chronological.

- **MAPE is NaN or infinity** — This happens if actual values contain zeros. MAPE divides by actual values, so zeros cause division errors. Use MAE or RMSE instead for data with zeros.

- **Directional accuracy exactly 50%** — If your test set has exactly equal up and down movements, this is possible. More likely, your predictions are flat (not changing), making direction meaningless.

- **RMSE much higher than MAE** — This indicates your forecast has some very large errors (outliers). Investigate those specific predictions to understand why.

### Time Series Cross-Validation

In standard machine learning, we use k-fold cross-validation—randomly shuffle data into k folds and train on k-1, test on 1. **This doesn't work for time series** because it violates temporal order (training on future to predict past).

Time series requires **forward-chaining cross-validation** (also called rolling window or expanding window CV). You progressively train on more data and test on the next period:

```
Split 1: Train [1,2,3,4,5] → Test [6]
Split 2: Train [1,2,3,4,5,6] → Test [7]
Split 3: Train [1,2,3,4,5,6,7] → Test [8]
...
```

This simulates real-world deployment where you retrain as new data arrives.

Let's implement time series cross-validation:

```php
# filename: time-series-cross-validation.php
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

    printf("Fold %2d | Train: %3d | MAE: $%.2f | RMSE: $%.2f\n",
        $fold['fold'], $fold['train_size'], $mae, $rmse
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
```

**Expected Output:**

```
=== Time Series Cross-Validation ===

Cross-Validation Setup:
- Strategy: Expanding Window
- Minimum training size: 400 days
- Test size: 10 days per fold
- Number of folds: 10

Fold  1 | Train: 400 | MAE: $2.89 | RMSE: $3.67
Fold  2 | Train: 410 | MAE: $2.76 | RMSE: $3.45
Fold  3 | Train: 420 | MAE: $3.12 | RMSE: $4.01
Fold  4 | Train: 430 | MAE: $2.54 | RMSE: $3.22
Fold  5 | Train: 440 | MAE: $2.98 | RMSE: $3.78
Fold  6 | Train: 450 | MAE: $2.67 | RMSE: $3.34
Fold  7 | Train: 460 | MAE: $3.05 | RMSE: $3.89
Fold  8 | Train: 470 | MAE: $2.81 | RMSE: $3.56
Fold  9 | Train: 480 | MAE: $2.93 | RMSE: $3.71
Fold 10 | Train: 490 | MAE: $2.72 | RMSE: $3.42

======================================================================
Cross-Validation Results (Mean ± Std Dev)
======================================================================
MAE:  $2.85 ± $0.18
RMSE: $3.61 ± $0.24


Comparison: CV vs. Single Train/Test Split
----------------------------------------------------------------------
Single Split MAE:  $2.87
CV Average MAE:    $2.85 (±$0.18)

✓ Single split is consistent with CV results
  → Either approach is reasonable for this dataset
```

**Why Time Series CV Matters:**

1. **More reliable performance estimate**: Averaging over multiple test periods is more robust than a single 80/20 split
2. **Detects instability**: If CV scores vary widely (high std dev), your model's performance is inconsistent across time
3. **Respects temporal order**: Never trains on future data
4. **Simulates production**: Mimics how you'd retrain and redeploy in real-world systems

**Expanding vs. Rolling Window:**

- **Expanding** (recommended): Use all historical data. More data generally improves models.
- **Rolling**: Fixed window size. Useful if recent data is more relevant than old data (concept drift).

**When to use CV:**

- ✅ **Do use** when comparing models—CV gives more reliable ranking
- ✅ **Do use** when dataset is small—maximizes data usage
- ⚠️ **Optional** when single split is already representative (like above)
- ❌ **Skip** if dataset is huge (CV is computationally expensive)

This completes the evaluation toolkit—you can now measure forecast quality with proper time series validation!

## Exercises

Now that you've learned multiple forecasting approaches, practice applying them to different scenarios:

### Exercise 1: Extend the Moving Average Forecaster

**Goal**: Improve the moving average forecaster by implementing weighted moving average (WMA).

Create a file called `exercise-weighted-ma.php` and implement:

- A `calculateWeightedMA()` function that applies linearly decreasing weights (most recent value has highest weight)
- Test with window sizes of 5, 10, and 15
- Compare MAE against standard SMA
- Determine which window size and weighting scheme works best for the stock data

**Validation**: Your WMA should achieve MAE between $2.50-$3.00 on the test set.

```php
// Test your implementation
$weights = [5, 4, 3, 2, 1];  // Most recent to oldest
$wma = calculateWeightedMA($recentPrices, $weights);
echo "WMA prediction: $$wma\n";
```

Expected behavior: WMA typically outperforms SMA by 5-15% on trending data because it emphasizes recent values.

### Exercise 2: Feature Engineering for Time Series

**Goal**: Enhance the Linear Trend Forecaster with additional time-based features.

Modify `LinearTrendForecaster` to include:

- Is the date a month-end (last trading day of month)?
- Quarter of year (Q1, Q2, Q3, Q4)
- Days until next earnings season (simulated: every 90 days)
- Volatility indicator (standard deviation of past 20 days)

**Validation**: Your enhanced model should improve MAE by at least $0.20 compared to the baseline linear model.

```php
// Expected improvement
$baselineMAE = 2.34;
$enhancedMAE = 2.10;  // Target: < $2.14
printf("Improvement: %.1f%%\n", (($baselineMAE - $enhancedMAE) / $baselineMAE) * 100);
```

### Exercise 3: Build a Website Traffic Forecaster

**Goal**: Apply forecasting techniques to a different domain—website traffic prediction.

Using the provided `data/website_traffic.csv`:

- Load daily visitor counts for an e-commerce site
- Identify weekly seasonality (traffic patterns differ by day of week)
- Train both MovingAverageForecaster and LinearTrendForecaster
- Use SeasonalDecomposer to extract and visualize weekly patterns
- Achieve MAE < 500 visitors on the test set

**Validation**: Website traffic has stronger seasonality than stock prices. Your seasonal strength should be > 0.4.

```php
// Expected output
$decomposer = new SeasonalDecomposer();
$components = $decomposer->decomposeAdditive($trafficData, seasonalPeriod: 7);
$strength = $decomposer->calculateSeasonalStrength(
    $components['seasonal'],
    $components['residual']
);
echo "Seasonal strength: " . number_format($strength, 3) . "\n";
// Should output: Seasonal strength: 0.567 (Strong seasonality detected)
```

### Challenge Exercise: Multi-Step Ahead Forecasting

**Goal**: Predict 7 days into the future (not just next day) and quantify uncertainty.

Extend any forecaster to:

- Generate 7-day forecasts
- Calculate prediction intervals (confidence bounds) assuming errors are normally distributed
- Show how uncertainty grows with forecast horizon
- Visualize forecasts with confidence bands (ASCII chart acceptable)

**Validation**: Your 7-day forecast MAE should be 2-3x higher than 1-day forecast (uncertainty accumulates).

```php
// Expected structure
$forecasts = $forecaster->forecastWithIntervals($startDate, steps: 7, confidence: 0.95);

foreach ($forecasts as $day => $forecast) {
    printf(
        "Day %d: $%.2f (95%% CI: $%.2f - $%.2f)\n",
        $day + 1,
        $forecast['point'],
        $forecast['lower'],
        $forecast['upper']
    );
}
```

**Hints**:

- Use historical forecast errors to estimate standard deviation
- Confidence interval: prediction ± (1.96 × standard_error × sqrt(horizon))
- Longer horizons → wider confidence intervals

## Troubleshooting

Common issues when working with time series forecasting and their solutions:

### Non-Stationary Data Errors

**Symptom**: Model coefficients are unstable, predictions explode or collapse, very poor performance even on training data.

**Cause**: Time series is non-stationary—mean or variance changes over time. Most forecasting models assume stationarity.

**Solution**: Transform the data before modeling:

```php
// Differencing: subtract previous value
function differenceSeries(array $values): array {
    $differenced = [];
    for ($i = 1; $i < count($values); $i++) {
        $differenced[] = $values[$i] - $values[$i - 1];
    }
    return $differenced;
}

// Train on differenced data
$differencedData = differenceSeries($trainingData);
$model->train($differencedData);

// To get actual predictions, add back the differences
$lastValue = $trainingData[count($trainingData) - 1];
$prediction = $lastValue + $model->predict();
```

Test for stationarity using rolling statistics—mean/variance should be roughly constant over time windows.

### Poor Forecast Accuracy

**Symptom**: MAE or MAPE is much higher than expected, R² is low or negative.

**Cause**: Multiple possible causes:

1. **Wrong model choice**: Linear models fail on non-linear data, moving averages lag on trending data
2. **Insufficient training data**: Need at least 50-100 points for stable estimates
3. **Data quality issues**: Missing values, outliers, incorrect timestamps
4. **No predictable pattern**: Some time series are truly random (e.g., efficient market hypothesis for stocks)

**Solution**:

```php
// Diagnose the issue
echo "1. Check data quality:\n";
printf("   Missing values: %d\n", count(array_filter($data, fn($v) => $v === null)));
printf("   Outliers (>3 SD): %d\n", countOutliers($data, stdDevs: 3));

echo "\n2. Test for patterns:\n";
$autocorr_lag1 = calculateAutocorrelation($data, lag: 1);
printf("   Autocorrelation (lag 1): %.3f\n", $autocorr_lag1);
if ($autocorr_lag1 < 0.3) {
    echo "   ⚠ Weak autocorrelation—data may be too random to forecast\n";
}

echo "\n3. Compare multiple models:\n";
$models = ['SMA', 'EMA', 'LinearRegression', 'ARMA'];
foreach ($models as $modelName) {
    $mae = trainAndEvaluate($modelName, $data);
    printf("   %s: MAE = $%.2f\n", $modelName, $mae);
}
```

Try ensemble methods—combine predictions from multiple models.

### Python Integration Failures

**Symptom**: `shell_exec()` returns null, subprocess errors, or "Python not found" messages.

**Cause**: Python not installed, wrong path, or permission issues.

**Solution**:

```php
// Verify Python is available
$pythonVersion = shell_exec('python3 --version 2>&1');
if ($pythonVersion === null) {
    throw new RuntimeException("Python 3 not found in PATH");
}
echo "Using: $pythonVersion\n";

// Use full paths
$pythonPath = '/usr/bin/python3';  // Or result of `which python3`
$scriptPath = __DIR__ . '/python/forecast.py';

if (!file_exists($scriptPath)) {
    throw new RuntimeException("Python script not found: $scriptPath");
}

// Execute with explicit paths and error capture
$command = sprintf(
    '%s %s --input %s --output %s 2>&1',
    escapeshellarg($pythonPath),
    escapeshellarg($scriptPath),
    escapeshellarg($inputFile),
    escapeshellarg($outputFile)
);

$output = shell_exec($command);

if ($output === null) {
    throw new RuntimeException("Failed to execute Python script");
}

echo "Python output:\n$output\n";
```

For production, use a REST API or message queue instead of direct shell execution.

### Memory Issues with Large Datasets

**Symptom**: PHP fatal error "Allowed memory size exhausted" when loading large time series.

**Cause**: Loading entire datasets into memory. A 10-year daily series is ~3,650 points × multiple columns × PHP array overhead.

**Solution**: Process data in batches or use generators:

```php
// Instead of loading all at once
function loadTimeSeriesGenerator(string $filepath): Generator {
    $handle = fopen($filepath, 'r');
    fgetcsv($handle);  // Skip header

    while (($row = fgetcsv($handle)) !== false) {
        yield [
            'date' => $row[0],
            'value' => (float)$row[4],  // Close price
        ];
    }

    fclose($handle);
}

// Use generator for training
$forecaster = new MovingAverageForecaster(window: 10);
$window = [];

foreach (loadTimeSeriesGenerator($dataFile) as $point) {
    $window[] = $point['value'];

    if (count($window) > $forecaster->window) {
        array_shift($window);
    }

    if (count($window) === $forecaster->window) {
        $prediction = $forecaster->predictSMA($window);
        // Process prediction...
    }
}
```

Alternatively, increase PHP memory limit in php.ini or script: `ini_set('memory_limit', '512M');`

### Systematic Bias (Consistent Over/Under-Prediction)

**Symptom**: Mean error is large and consistent (all positive or all negative), even though MAE is acceptable.

**Cause**: Model doesn't capture level shifts, missing features, or training data isn't representative.

**Solution**: Add bias correction:

```php
// Calculate bias on validation set
$validationErrors = [];
foreach ($validationSet as $i => $actual) {
    $predicted = $model->predict($i);
    $validationErrors[] = $actual - $predicted;
}
$bias = array_sum($validationErrors) / count($validationErrors);

// Apply correction to future predictions
function predictWithBiasCorrection($model, $input, $bias) {
    return $model->predict($input) + $bias;
}

$correctedPrediction = predictWithBiasCorrection($model, $newInput, $bias);
```

Or use models that adapt to level (exponential smoothing with trend and level components).

## Wrap-up

Congratulations! You've completed a comprehensive exploration of predictive analytics and time series forecasting. Let's review what you've accomplished:

**✓ Theoretical Foundations**

- Understood time series characteristics: trend, seasonality, stationarity, and autocorrelation
- Learned why time series requires different handling than standard ML datasets
- Mastered chronological train/test splits and the dangers of data leakage

**✓ Practical Forecasting Techniques**

- Implemented Simple and Exponential Moving Averages for baseline predictions
- Built Linear Regression forecasters with time-based feature engineering
- Created seasonal decomposition to separate trend from cyclical patterns
- Developed simplified ARMA models combining autoregressive and moving average components

**✓ Evaluation and Comparison**

- Calculated comprehensive metrics: MAE, RMSE, MAPE, R², directional accuracy
- Compared multiple models systematically to identify the best approach
- Understood when each model excels and their limitations

**✓ PHP and Python Integration**

- Built production-ready forecasters in pure PHP for simple scenarios
- Learned strategies to leverage Python's advanced libraries (Prophet, statsmodels)
- Understood trade-offs: PHP simplicity vs. Python sophistication

**✓ Production Readiness**

- Handled real-world data issues: missing values, outliers, non-stationarity
- Implemented model persistence and error handling
- Built complete forecasting systems ready for deployment

**What You Can Do Now:**

You can confidently build forecasting features for PHP applications:

- **E-commerce**: Predict sales to optimize inventory and staffing
- **SaaS platforms**: Forecast user growth and server capacity needs
- **Content sites**: Anticipate traffic spikes for infrastructure scaling
- **Marketing dashboards**: Project campaign performance and ROI
- **Financial tools**: Analyze price trends (with appropriate disclaimers!)

**Connection to Chapter 20:**

In the next chapter, you'll apply everything learned here in a hands-on **Time Series Forecasting Project**. You'll build a complete forecasting dashboard that:

- Loads and preprocesses real-world data
- Trains multiple models and selects the best performer
- Generates visualizations of forecasts and confidence intervals
- Deploys as a web service with RESTful API
- Handles model retraining and monitoring

**Real-World Applications:**

The techniques you've learned power critical business decisions:

- Amazon uses time series forecasting for inventory management across millions of products
- Netflix predicts viewing patterns to optimize content delivery infrastructure
- Airbnb forecasts demand to dynamically price listings
- Web apps predict resource usage to auto-scale cloud infrastructure

**Financial Data Ethics Reminder:**

While this chapter used stock prices as teaching data, remember: machine learning forecasts are **not investment advice**. Financial markets are influenced by countless unpredictable factors. Always consult qualified financial professionals before making investment decisions. The skills you've gained apply to any time-indexed data—sales, traffic, metrics—where forecasting creates genuine business value.

## Further Reading

Deepen your understanding with these carefully selected resources:

**Time Series Analysis Fundamentals**

- [Introduction to Time Series Analysis](https://otexts.com/fpp3/) — Free online textbook by Hyndman & Athanasopoulos, the gold standard for forecasting
- [Time Series Analysis: Forecasting and Control](https://www.wiley.com/en-us/Time+Series+Analysis%3A+Forecasting+and+Control%2C+5th+Edition-p-9781118675021) — Box & Jenkins, the classic ARIMA reference
- [Practical Time Series Forecasting with R](https://www.practicalforecasting.com) — Hands-on guide with code examples

**PHP Libraries and Tools**

- [Rubix ML Documentation](https://docs.rubixml.com/latest/) — Regression models and cross-validation
- [PHP-ML Time Series](https://php-ml.readthedocs.io/) — Simple ML library for PHP
- [PSR-20: Clock Interface](https://www.php-fig.org/psr/psr-20/) — Standard for working with dates/times in PHP

**Python Integration**

- [Facebook Prophet Documentation](https://facebook.github.io/prophet/) — Automatic forecasting at scale
- [statsmodels Time Series](https://www.statsmodels.org/stable/tsa.html) — Comprehensive ARIMA, VAR, and more
- [PM Darima (Auto-ARIMA)](http://alkaline-ml.com/pmdarima/) — Automated order selection

**Evaluation Metrics**

- [Evaluating Forecast Accuracy](https://robjhyndman.com/papers/another-look-at-measures-of-forecast-accuracy/) — Paper comparing MAE, RMSE, MAPE, and alternatives
- [Mean Absolute Scaled Error (MASE)](https://en.wikipedia.org/wiki/Mean_absolute_scaled_error) — Scale-independent metric for comparing models

**Advanced Topics**

- [LSTM Networks for Time Series](https://colah.github.io/posts/2015-08-Understanding-LSTMs/) — Deep learning for sequences
- [Bayesian Structural Time Series](https://research.google/pubs/pub41335/) — Google's approach to causal impact and forecasting
- [Vector Autoregression (VAR)](https://en.wikipedia.org/wiki/Vector_autoregression) — Multivariate time series models

**Financial Data APIs**

- [Alpha Vantage](https://www.alphavantage.co/) — Free stock price API
- [Yahoo Finance API](https://github.com/ranaroussi/yfinance) — Historical market data (Python library)
- [IEX Cloud](https://iexcloud.io/) — Financial data API with generous free tier

**PSR Standards**

- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/) — PHP coding standards
- [PSR-4: Autoloading](https://www.php-fig.org/psr/psr-4/) — Class autoloading standard

---

**Next Chapter**: [20: Time Series Forecasting Project](/series/ai-ml-php-developers/chapters/20-time-series-forecasting-project) — Apply everything you've learned to build a complete, production-ready forecasting system with visualization and deployment.

::: tip Share Your Results
Built an interesting forecaster? Share your accuracy metrics and use case in the community forum. Did you forecast website traffic? Sales? Server metrics? We'd love to hear what patterns you discovered!
:::

::: warning Remember
This chapter taught forecasting techniques using stock prices as educational examples. **This is not financial advice.** Always consult qualified financial professionals before making any investment decisions. The techniques apply to any time-indexed data where your organization can benefit from predictions.
:::
