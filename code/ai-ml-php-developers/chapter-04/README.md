# Chapter 04: Data Collection and Preprocessing - Code Examples

This directory contains all the code examples and data files for Chapter 04 of the AI/ML for PHP Developers series.

## Overview

These examples demonstrate:

- Loading data from multiple sources (CSV, JSON, databases)
- Handling missing values
- Normalizing numeric features
- Encoding categorical variables
- Building reusable preprocessing pipelines
- Splitting data for training and testing
- Saving and loading preprocessing parameters
- Creating engineered features
- Detecting and handling outliers

## Prerequisites

- PHP 8.4+
- Composer
- SQLite extension (usually included with PHP)

**Note:** The `processed/` directory will be created automatically by the scripts when needed.

## Setup

1. Install dependencies:

```bash
composer install
```

2. Create the products database:

```bash
php create-products-db.php
```

This creates `data/products.db` with 20 products and 500 orders.

## Directory Structure

```
chapter-04/
├── data/                          # Sample datasets
│   ├── customers.csv             # 100 customer records
│   ├── products.db              # SQLite database (generated)
│   ├── user_activities.json     # Generated activity data
│   └── incomplete_customers.json # Generated data with missing values
├── processed/                     # Output directory for cleaned data
├── solutions/                     # Exercise solutions (5 exercises)
├── 01-load-csv.php               # CSV loading example
├── 02-load-database.php          # Database loading example
├── 03-create-json-data.php       # Generate sample JSON data
├── 04-load-json.php              # JSON loading example
├── 05-create-incomplete-data.php # Generate data with missing values
├── 06-handle-missing-values.php  # Missing value handling
├── 07-normalize-features.php     # Feature normalization
├── 08-encode-categorical.php     # Categorical encoding
├── 09-preprocessing-pipeline.php # Complete OOP pipeline
├── 10-train-test-split.php       # Train/test splitting
├── 11-save-load-pipeline.php     # Parameter persistence
├── 12-feature-engineering.php    # Feature engineering techniques
├── 13-outlier-detection.php      # Outlier detection and handling
└── composer.json                  # Dependencies
```

## Running the Examples

### Step 1: Load CSV Data

```bash
php 01-load-csv.php
```

Loads the customer dataset and displays basic statistics.

**Expected output:**

```
Loaded 100 customers

Sample records:
- John Doe (Age: 28, Orders: 12)
- Jane Smith (Age: 34, Orders: 8)
- Michael Johnson (Age: 45, Orders: 25)

Basic Statistics:
- Average age: 37.8
- Age range: 22 to 65
```

### Step 2: Load Database Data

```bash
php 02-load-database.php
```

Loads products from SQLite database with aggregated order data.

### Step 3 & 4: JSON Data

```bash
# Generate sample JSON data
php 03-create-json-data.php

# Load and analyze JSON data
php 04-load-json.php
```

### Step 5 & 6: Missing Values

```bash
# Generate incomplete data
php 05-create-incomplete-data.php

# Handle missing values
php 06-handle-missing-values.php
```

Demonstrates deletion, mean imputation, and mode imputation strategies.

### Step 7: Normalization

```bash
php 07-normalize-features.php
```

Applies min-max, z-score, and robust scaling to features.

### Step 8: Encoding

```bash
php 08-encode-categorical.php
```

Shows label encoding, one-hot encoding, and frequency encoding.

### Step 9: Complete Pipeline

```bash
php 09-preprocessing-pipeline.php
```

Demonstrates the full OOP preprocessing pipeline with method chaining.

### Step 10: Train/Test Split

```bash
php 10-train-test-split.php
```

Shows how to properly split data to prevent data leakage. Demonstrates simple, three-way, and stratified splits.

### Step 11: Save/Load Pipeline Parameters

```bash
php 11-save-load-pipeline.php
```

Demonstrates saving preprocessing parameters for production use and applying them to new data.

### Step 12: Feature Engineering

```bash
php 12-feature-engineering.php
```

Creates derived features using binning, interactions, ratios, polynomials, and time-based extractions.

### Step 13: Outlier Detection

```bash
php 13-outlier-detection.php
```

Detects outliers using Z-score and IQR methods, with strategies for removal or capping.

## Data Files

### customers.csv

- **Rows:** 100
- **Columns:** 13
- **Fields:** customer_id, first_name, last_name, email, age, gender, city, country, total_orders, avg_order_value, account_created, has_subscription, is_active

### products.db (Generated)

- **Products table:** 20 products across 6 categories
- **Orders table:** 500 sample orders
- **Fields:** product_id, name, category, price, stock_quantity, rating, created_date, is_active

## Common Issues

### "File not found" error

Make sure you're running scripts from the `chapter-04/` directory:

```bash
cd /path/to/docs/series/ai-ml-php-developers/code/chapter-04
```

### "No such table: products"

Run the database setup script first:

```bash
php create-products-db.php
```

### "processed directory not found"

The scripts now create this directory automatically. If you encounter this error, verify write permissions:

```bash
chmod 755 .
```

## Exercises

The chapter includes 5 exercises with complete solutions:

### Exercise 1: Load and Clean E-commerce Data

Practice complete data loading and cleaning workflow with median imputation.

```bash
php solutions/exercise1-clean-data.php
```

### Exercise 2: Normalize Product Prices

Apply multiple normalization techniques and compare results.

```bash
php solutions/exercise2-normalize-comparison.php
```

### Exercise 3: One-Hot Encode Product Categories

Handle multi-class categorical encoding with product categories.

```bash
php solutions/exercise3-onehot-categories.php
```

### Exercise 4: Build a Custom Preprocessing Pipeline with Train/Test Split

Create an end-to-end preprocessing pipeline for predicting `total_orders`, including proper train/test splitting.

```bash
php solutions/exercise4-custom-pipeline.php
```

**Outputs:**

- Training features and target (80% of data)
- Test features and target (20% of data)
- No data leakage: preprocessing done before split

### Exercise 5: Outlier Detection and Handling

Detect outliers in product prices using Z-score and IQR methods, compare results, and create cleaned datasets.

```bash
php solutions/exercise5-outlier-handling.php
```

**Outputs:**

- Comparison of detection methods
- Removed outliers dataset
- Capped outliers dataset
- Impact analysis on mean and standard deviation

All solutions are fully working and include detailed output.

## Next Steps

After completing this chapter, you'll use the preprocessed data in Chapter 05 to build your first machine learning model: a linear regression predictor.

The `processed/final_preprocessed.json` file created by the pipeline will be used in future chapters.

## Additional Resources

- [PHP CSV Functions](https://www.php.net/manual/en/function.fgetcsv.php)
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [JSON Functions](https://www.php.net/manual/en/book.json.php)

## Code Quality Features

The examples in this chapter include:

- **Type Safety:** Automatic type coercion for CSV numeric fields
- **Error Handling:** Comprehensive checks with descriptive error messages
- **Auto-creation:** Directories are created automatically when needed
- **Validation:** Empty dataset checks prevent silent failures
- **Documentation:** Each file has a header explaining its purpose

## Recent Improvements

### Latest Update (2025-01-26)

- **New Examples Added:**
  - 10-train-test-split.php: Simple, three-way, and stratified splits
  - 11-save-load-pipeline.php: Parameter persistence for production
  - 12-feature-engineering.php: Binning, interactions, ratios, polynomials, time features
  - 13-outlier-detection.php: Z-score and IQR methods with handling strategies
- **Exercise 4:** Updated to include train/test split
- **Exercise 5:** New outlier detection and handling exercise
- **Chapter Content:** Added 4 new steps (8-11) covering production-ready preprocessing
- **Documentation:** Production Considerations section added to chapter

### Previous Improvements

- Added type coercion for CSV data (numeric strings → numbers)
- Implemented automatic directory creation for `processed/`
- Enhanced error messages with contextual help
- Added headers to all code files
- Demonstrated robust scaling in normalization examples
- Created complete solutions for all exercises
- Added Knowledge Check quiz to chapter

## License

All code examples are part of the Code with PHP tutorial series and are provided for educational purposes.
