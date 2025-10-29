# Chapter 19: Predictive Analytics and Time Series Data

Code examples for time series forecasting in PHP.

## Setup

1. Install dependencies:

```bash
composer install
```

2. Verify PHP version:

```bash
php --version  # Should be PHP 8.4+
```

## Quick Start

Run the 5-minute example:

```bash
php quick-start.php
```

## Examples

### Basic Forecasting

- `quick-start.php` — 5-minute moving average demo
- `01-load-and-analyze-data.php` — Load and analyze time series data
- `autocorrelation-analysis.php` — ⭐ Measure temporal dependencies (ACF)
- `02-data-preparation.php` — Data validation and train/test splits
- `stationarity-test.php` — ⭐ Test for stationarity using rolling statistics
- `03-moving-averages.php` — Simple and exponential moving averages
- `04-linear-regression-trend.php` — Linear regression forecasting
- `05-seasonal-decomposition.php` — Trend/seasonal/residual decomposition
- `06-evaluation-metrics.php` — MAE, RMSE, MAPE, R², directional accuracy
- `time-series-cross-validation.php` — ⭐ Proper CV for time series data

### Advanced Topics (Covered in Chapter but code not fully implemented)

These are outlined in the chapter with theory and patterns:

- Python Prophet integration (via REST API)
- Python statsmodels integration (via CLI)
- ARIMA-style forecasting
- Production-ready complete forecaster system

## Data Files

- `data/sample_stock_prices.csv` — 2 years of historical stock prices
- `data/website_traffic.csv` — Daily visitor counts for exercises

## Supporting Classes

All classes are in the `src/` directory and use the `AiMlPhp\Chapter19` namespace:

- `TimeSeriesDataLoader` — Load and prepare time series data
- `MovingAverageForecaster` — SMA and EMA implementations
- `LinearTrendForecaster` — Linear regression with time features
- `SeasonalDecomposer` — Decompose into trend/seasonal/residual
- `ForecastEvaluator` — Comprehensive evaluation metrics
- `SimpleARMAForecaster` — ARMA modeling

## Exercises

Solutions are in the `solutions/` directory:

1. **Weighted Moving Average** — `solutions/exercise-weighted-ma.php`
2. **Feature Engineering** — `solutions/exercise-features.php`
3. **Website Traffic** — `solutions/exercise-traffic.php`
4. **Multi-Step Forecasting** — `solutions/exercise-multistep.php`

## Python Integration (Optional)

If you want to run the Python integration examples (07, 08):

1. Install Python 3.10+
2. Install dependencies:

```bash
cd python
pip install -r requirements.txt
```

3. Run Python examples:

```bash
php 07-python-prophet-api.php
php 08-python-statsmodels-cli.php
```

## Important Disclaimers

**This chapter uses stock price data for educational purposes only.**

- This is **not financial advice**
- Past performance does not predict future results
- Never make investment decisions based solely on ML models
- Consult qualified financial professionals for investment advice

The techniques taught apply to any time series data: sales forecasts, traffic predictions, server metrics, etc.

## Troubleshooting

### Composer install fails

Make sure you have PHP 8.4+ installed:

```bash
php --version
composer --version
```

### Memory errors

Increase PHP memory limit:

```bash
php -d memory_limit=512M script.php
```

### Data files missing

Generate sample data:

```bash
php generate-sample-data.php
```

## Further Learning

- See [Chapter 19 documentation](/series/ai-ml-php-developers/chapters/19-predictive-analytics-and-time-series-data) for full explanations
- Progress to [Chapter 20](/series/ai-ml-php-developers/chapters/20-time-series-forecasting-project) for the complete forecasting project

## License

MIT License - Educational use encouraged
