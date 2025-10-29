# Chapter 20: Time Series Forecasting Project

Complete code examples for building a sales forecasting system with multiple methods.

## Files

- `sample-sales-data.csv` — 36 months of e-commerce sales data
- `01-load-and-explore.php` — Load and analyze sales data
- `01b-seasonal-decomposition.php` — ✨ **NEW**: Decompose into trend, seasonal, residual
- `02-moving-average.php` — Simple and weighted moving average forecasting
- `03-linear-regression.php` — Trend-based forecasting with Rubix ML
- `04-prophet-integration.php` — PHP-Python integration for Facebook Prophet
- `05-visualize-all.php` — Compare all forecasting methods
- `06-evaluate-accuracy.php` — Calculate MAE, RMSE, MAPE metrics (+ cross-validation)
- `train_prophet.py` — Python script for Prophet forecasting
- `composer.json` — PHP dependencies
- `requirements.txt` — Python dependencies

## Setup

### PHP Dependencies

```bash
composer install
```

### Optional: Python Dependencies (for Prophet)

```bash
# Option 1: pip
pip3 install -r requirements.txt

# Option 2: conda (recommended)
conda install -c conda-forge prophet
```

## Running Examples

Execute the examples in order:

```bash
# Step 1: Load and explore data
php 01-load-and-explore.php

# Step 2: Moving average forecasting
php 02-moving-average.php

# Step 3: Linear regression forecasting
php 03-linear-regression.php

# Step 4: Prophet integration (requires Python)
php 04-prophet-integration.php

# Step 5: Visualize comparisons
php 05-visualize-all.php

# Step 6: Evaluate accuracy
php 06-evaluate-accuracy.php
```

## Exercises

Solutions are available in the `solutions/` directory:

- `exercise-01-exponential-smoothing.php` — Implement exponential smoothing
- `exercise-02-weekly-forecast.php` — Adapt for weekly data
- `exercise-03-confidence-intervals.php` — Add prediction intervals

## Requirements

- PHP 8.4+
- Composer
- Optional: Python 3.10+ with Prophet library

## Notes

- Prophet integration is optional; examples will work without it using mock data
- All code examples are fully functional and tested
- Dataset shows realistic e-commerce revenue with trend and seasonality
