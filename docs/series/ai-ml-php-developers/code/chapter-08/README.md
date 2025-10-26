# Chapter 08 Code Examples

This directory contains all code examples for Chapter 08: Leveraging PHP Machine Learning Libraries.

## Setup

```bash
# Install dependencies
composer require php-ai/php-ml:^0.10
composer require rubix/ml:^3.0

# Create necessary directories (already created if you cloned the repo)
mkdir -p data models solutions logs
```

## Running the Examples

### Step 1: Installation Verification

```bash
php 01-phpml-spam-filter.php
```

### Step 2: Spam Filter with PHP-ML

```bash
php 01-phpml-spam-filter.php
```

### Step 3: Iris Classifier with Rubix ML

```bash
php 02-rubix-iris-classifier.php
```

### Step 4: Customer Segmentation with Clustering

```bash
php 03-phpml-clustering.php
```

### Step 5: Model Persistence

```bash
php 05-model-persistence.php
```

### Step 6: Algorithm Comparison

```bash
php 06-algorithm-comparison.php
```

### Step 7: Loading Real Data with Transformers

```bash
php 07-load-real-data.php
```

### Step 8: Production REST API

First, make sure you have a trained model saved:

```bash
# Train and save a model (from Step 5)
php 05-model-persistence.php
```

Then start the API server:

```bash
php -S localhost:8000 08-production-api.php
```

In another terminal, test the API:

```bash
# Health check
curl http://localhost:8000/health

# Make prediction
curl -X POST http://localhost:8000/predict \
  -H "Content-Type: application/json" \
  -d '{"features": [5.1, 3.5, 1.4, 0.2]}'

# Check statistics
curl http://localhost:8000/stats
```

### Step 9: Regression and Feature Importance

```bash
php 09-regression-feature-importance.php
```

This demonstrates house price prediction using regression algorithms and analyzes which features are most important for predictions.

## Directory Structure

```
code/chapter-08/
├── README.md                       # This file
├── 01-phpml-spam-filter.php       # Step 2: Spam filter with PHP-ML
├── 02-rubix-iris-classifier.php   # Step 3: Iris classifier with pipeline
├── 03-phpml-clustering.php        # Step 4: Customer segmentation
├── 05-model-persistence.php       # Step 5: Saving/loading models
├── 06-algorithm-comparison.php    # Step 6: Comparing algorithms
├── 07-load-real-data.php          # Step 7: Loading CSV/DB data with transformers
├── 08-production-api.php          # Step 8: Production REST API
├── 09-regression-feature-importance.php  # Step 9: Regression and feature importance
├── data/                          # CSV files and datasets
├── models/                        # Saved trained models
├── logs/                          # Prediction logs
└── solutions/                     # Exercise solutions
```

## Troubleshooting

### "Class not found" errors

Make sure you've installed the dependencies:

```bash
composer install
```

### "Model file not found" error (08-production-api.php)

You need to train and save a model first:

```bash
php 05-model-persistence.php
```

### PHP version issues

This code requires PHP 8.4+. Check your version:

```bash
php -v
```

### Memory issues

Increase PHP memory limit:

```bash
php -d memory_limit=256M 06-algorithm-comparison.php
```

## Notes

- All examples use PHP 8.4 syntax (named arguments, property hooks, etc.)
- The API example (08-production-api.php) is designed to be extended with the features described in Exercise 6
- Model files in `models/` are binary and should not be version controlled in production
- Log files in `logs/` should be rotated in production environments

## Further Reading

See the main chapter documentation at:
`docs/series/ai-ml-php-developers/chapters/08-leveraging-php-machine-learning-libraries.md`
