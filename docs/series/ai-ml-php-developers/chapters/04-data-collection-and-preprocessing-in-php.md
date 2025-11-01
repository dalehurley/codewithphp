---
title: "04: Data Collection and Preprocessing in PHP"
description: Learn to acquire and prepare data for machine learning using PHP—from databases, CSV/JSON files, and APIs to cleaning, normalizing, and encoding for quality datasets
series: ai-ml-php-developers
chapter: "04"
order: 4
difficulty: Beginner
prerequisites:
  - "03"
---

![Data Collection and Preprocessing in PHP](/images/ai-ml-php-developers/chapter-04-data-preprocessing-hero-full.webp)

# Chapter 04: Data Collection and Preprocessing in PHP

## Overview

In machine learning, the quality of your data determines the quality of your model. No amount of sophisticated algorithms can compensate for poor, inconsistent, or incomplete data. This chapter focuses on the crucial but often overlooked phase of any ML project: acquiring and preparing data.

You'll learn practical techniques for gathering data from multiple sources—databases, CSV files, JSON files, and web APIs—all using native PHP capabilities. More importantly, you'll master the art of data preprocessing: handling missing values, normalizing numeric features, encoding categorical variables, and transforming raw data into the clean, consistent format that machine learning algorithms require.

By working through real-world examples with customer and product datasets, you'll develop an intuition for spotting data quality issues and the confidence to fix them systematically. These preprocessing skills transfer directly to every ML project you'll build in the coming chapters.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Composer installed for dependency management
- Completion of [Chapter 03](/series/ai-ml-php-developers/chapters/03-core-machine-learning-concepts-and-terminology) or equivalent understanding of ML concepts
- A text editor or IDE
- **Estimated Time**: ~70-90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Check Composer
composer --version
```

## What You'll Build

By the end of this chapter, you will have created:

- A **data loading system** that reads from CSV, JSON, databases, and APIs
- A **data cleaning pipeline** that handles missing values and outliers
- A **normalization toolkit** for scaling numeric features to consistent ranges
- A **categorical encoder** that converts text labels to numeric values
- A **complete preprocessing workflow** combining all techniques
- Working examples with a 100-customer dataset and 20-product database
- An OOP-based reusable data pipeline class

## Quick Start

Want to see data preprocessing in action? Run this 2-minute example:

```bash
cd docs/series/ai-ml-php-developers/code/chapter-04
php 01-load-csv.php
```

This loads a customer dataset and displays basic statistics, giving you immediate feedback on data structure and quality.

## Objectives

- **Understand** why data quality is the foundation of successful machine learning
- **Load data** from multiple sources (CSV, JSON, databases, APIs) using PHP
- **Identify and handle** missing values, outliers, and inconsistencies
- **Normalize** numeric features to standard scales (0-1 or z-score)
- **Encode** categorical variables into numeric representations
- **Split data** properly to prevent data leakage and enable fair evaluation
- **Engineer features** that capture domain knowledge and improve predictions
- **Save preprocessing parameters** for consistent production deployment
- **Detect outliers** using statistical methods and decide on handling strategies
- **Build** a complete, production-ready preprocessing pipeline
- **Validate** data quality through statistical checks and visualizations

## Step 1: Loading Data from CSV Files (~8 min)

### Goal

Load structured data from CSV files and parse it into PHP arrays for processing.

### Actions

1. **Navigate to the code directory**:

```bash
cd /Users/dalehurley/Code/PHP-From-Scratch/docs/series/ai-ml-php-developers/code/chapter-04
```

2. **Install dependencies**:

```bash
composer install
```

3. **Examine the customer dataset**:

```bash
head -5 data/customers.csv
```

You'll see columns like `customer_id`, `first_name`, `age`, `total_orders`, `avg_order_value`, etc.

4. **Create the CSV loader** (`01-load-csv.php`):

```php
# filename: 01-load-csv.php
<?php

declare(strict_types=1);

/**
 * Load and explore CSV data
 */
function loadCsv(string $filepath): array
{
    if (!file_exists($filepath)) {
        throw new RuntimeException("File not found: $filepath");
    }

    $file = fopen($filepath, 'r');
    if ($file === false) {
        throw new RuntimeException("Could not open file: $filepath");
    }

    // First row contains headers
    $headers = fgetcsv($file);
    if ($headers === false) {
        throw new RuntimeException("Invalid CSV format");
    }

    $data = [];
    while (($row = fgetcsv($file)) !== false) {
        // Combine headers with row data for associative array
        $data[] = array_combine($headers, $row);
    }

    fclose($file);

    return $data;
}

// Load customer data
$customers = loadCsv(__DIR__ . '/data/customers.csv');

echo "Loaded " . count($customers) . " customers\n\n";

// Display first 3 records
echo "Sample records:\n";
foreach (array_slice($customers, 0, 3) as $customer) {
    echo "- {$customer['first_name']} {$customer['last_name']} ";
    echo "(Age: {$customer['age']}, Orders: {$customer['total_orders']})\n";
}

// Basic statistics
$ages = array_column($customers, 'age');
$avgAge = array_sum($ages) / count($ages);

echo "\nBasic Statistics:\n";
echo "- Average age: " . round($avgAge, 1) . "\n";
echo "- Age range: " . min($ages) . " to " . max($ages) . "\n";
```

5. **Run the loader**:

```bash
php 01-load-csv.php
```

### Expected Result

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

### Why It Works

The `fgetcsv()` function is PHP's native CSV parser. By reading the first row as headers and then using `array_combine()` for subsequent rows, we create associative arrays that are easier to work with than numeric-indexed arrays. This structure allows us to reference columns by name (`$customer['age']`) rather than position (`$customer[3]`), making code more readable and maintainable.

### Troubleshooting

- **Error: "File not found"** — Verify you're in the correct directory with `pwd`. The path is relative to where you run the script.
- **Warning: "array_combine(): Argument #1 and #2 must have the same number of elements"** — Your CSV has inconsistent columns. Check for extra commas or missing values in the file.
- **Empty output** — Ensure `data/customers.csv` exists and has content with `wc -l data/customers.csv`

## Step 2: Loading Data from Databases (~7 min)

### Goal

Extract data from a SQLite database using PDO for scenarios where data is stored in relational databases.

### Actions

1. **Create the products database**:

```bash
php create-products-db.php
```

This generates `data/products.db` with 20 products and 500 sample orders.

2. **Create the database loader** (`02-load-database.php`):

```php
# filename: 02-load-database.php
<?php

declare(strict_types=1);

/**
 * Load data from SQLite database
 */
function loadFromDatabase(string $dbPath, string $query): array
{
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new RuntimeException("Database error: " . $e->getMessage());
    }
}

// Load products with aggregated order data
$query = "
    SELECT
        p.product_id,
        p.name,
        p.category,
        p.price,
        p.stock_quantity,
        p.rating,
        COUNT(o.order_id) as total_orders,
        COALESCE(SUM(o.quantity), 0) as units_sold,
        COALESCE(SUM(o.total_amount), 0) as revenue
    FROM products p
    LEFT JOIN orders o ON p.product_id = o.product_id
    GROUP BY p.product_id
    ORDER BY revenue DESC
";

$products = loadFromDatabase(__DIR__ . '/data/products.db', $query);

echo "Loaded " . count($products) . " products\n\n";

// Top 5 products by revenue
echo "Top 5 Products by Revenue:\n";
foreach (array_slice($products, 0, 5) as $product) {
    echo sprintf(
        "- %s: $%.2f (%d orders, %d units)\n",
        $product['name'],
        $product['revenue'],
        $product['total_orders'],
        $product['units_sold']
    );
}

// Category analysis
$categories = [];
foreach ($products as $product) {
    $cat = $product['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = 0;
    }
    $categories[$cat] += (float)$product['revenue'];
}

echo "\nRevenue by Category:\n";
arsort($categories);
foreach ($categories as $category => $revenue) {
    echo "- $category: $" . number_format($revenue, 2) . "\n";
}
```

3. **Run the database loader**:

```bash
php 02-load-database.php
```

### Expected Result

```
Loaded 20 products

Top 5 Products by Revenue:
- Smart Watch Series 5: $8699.75 (29 orders, 87 units)
- Laptop Pro 15": $7799.94 (6 orders, 18 units)
- Mechanical Keyboard: $6149.58 (41 orders, 123 units)
- Bluetooth Speaker: $5759.28 (72 orders, 216 units)
- Running Shoes: $5459.58 (42 orders, 126 units)

Revenue by Category:
- Electronics: $34,758.09
- Sports & Outdoors: $7,649.43
- Home & Kitchen: $6,394.22
- Health & Beauty: $3,794.70
- Home & Garden: $2,474.31
- Health & Nutrition: $2,029.71
```

### Why It Works

PDO (PHP Data Objects) provides a consistent interface for accessing databases regardless of the underlying system (MySQL, PostgreSQL, SQLite). The `LEFT JOIN` aggregates order data for each product, and `COALESCE` handles products with no orders by substituting 0. This demonstrates that real-world ML data often requires joining multiple tables to create feature-rich datasets.

### Troubleshooting

- **Error: "database disk image is malformed"** — Delete `data/products.db` and run `php create-products-db.php` again
- **Error: "no such table: products"** — The database wasn't created. Run the setup script first
- **Empty results** — Check your SQL syntax. Try running a simpler query like `SELECT * FROM products LIMIT 5`

## Step 3: Loading Data from JSON Files and APIs (~6 min)

### Goal

Parse JSON data from files and web APIs to demonstrate handling semi-structured data.

### Actions

1. **Create a sample JSON file** (`03-create-json-data.php`):

```php
# filename: 03-create-json-data.php
<?php

declare(strict_types=1);

// Generate sample user activity data
$activities = [];
$actions = ['login', 'view_product', 'add_to_cart', 'purchase', 'review'];

for ($i = 1; $i <= 50; $i++) {
    $activities[] = [
        'user_id' => rand(1, 20),
        'action' => $actions[array_rand($actions)],
        'product_id' => rand(1, 20),
        'timestamp' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')),
        'duration_seconds' => rand(10, 600),
        'device' => ['mobile', 'desktop', 'tablet'][rand(0, 2)]
    ];
}

// Save to JSON file
file_put_contents(
    __DIR__ . '/data/user_activities.json',
    json_encode($activities, JSON_PRETTY_PRINT)
);

echo "Generated " . count($activities) . " activity records\n";
echo "Saved to data/user_activities.json\n";
```

2. **Generate the JSON data**:

```bash
php 03-create-json-data.php
```

3. **Create the JSON loader** (`04-load-json.php`):

```php
# filename: 04-load-json.php
<?php

declare(strict_types=1);

/**
 * Load JSON data from file or URL
 */
function loadJson(string $source): array
{
    // Check if source is URL or file path
    if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
        $content = @file_get_contents($source);
        if ($content === false) {
            throw new RuntimeException("Failed to fetch data from URL: $source");
        }
    } else {
        if (!file_exists($source)) {
            throw new RuntimeException("File not found: $source");
        }
        $content = file_get_contents($source);
    }

    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("JSON decode error: " . json_last_error_msg());
    }

    return $data;
}

// Load local JSON file
$activities = loadJson(__DIR__ . '/data/user_activities.json');

echo "Loaded " . count($activities) . " user activities\n\n";

// Analyze by action type
$actionCounts = [];
foreach ($activities as $activity) {
    $action = $activity['action'];
    $actionCounts[$action] = ($actionCounts[$action] ?? 0) + 1;
}

echo "Activity Breakdown:\n";
arsort($actionCounts);
foreach ($actionCounts as $action => $count) {
    $percentage = round(($count / count($activities)) * 100, 1);
    echo "- $action: $count ({$percentage}%)\n";
}

// Device usage
$deviceCounts = [];
foreach ($activities as $activity) {
    $device = $activity['device'];
    $deviceCounts[$device] = ($deviceCounts[$device] ?? 0) + 1;
}

echo "\nDevice Usage:\n";
arsort($deviceCounts);
foreach ($deviceCounts as $device => $count) {
    echo "- $device: $count\n";
}
```

4. **Run the JSON loader**:

```bash
php 04-load-json.php
```

### Expected Result

```
Loaded 50 user activities

Activity Breakdown:
- view_product: 14 (28.0%)
- add_to_cart: 11 (22.0%)
- login: 10 (20.0%)
- review: 8 (16.0%)
- purchase: 7 (14.0%)

Device Usage:
- mobile: 19
- desktop: 17
- tablet: 14
```

### Why It Works

JSON is ubiquitous in web APIs and NoSQL databases. PHP's `json_decode()` converts JSON strings into PHP arrays. The `json_last_error()` check ensures we catch malformed JSON early. This same function works for both local files and API responses, making it versatile for different data sources.

### Troubleshooting

- **Error: "JSON decode error: Syntax error"** — Your JSON file is malformed. Validate it with `cat data/user_activities.json | python -m json.tool`
- **Warning: "file_get_contents(): failed to open stream"** — Check file permissions with `ls -la data/`
- **For API calls failing** — Add error context: wrap in try-catch and log the response for debugging

## Step 4: Handling Missing Values (~8 min)

### Goal

Detect and handle missing or incomplete data using multiple strategies (removal, imputation, flagging).

### Actions

1. **Create a dataset with missing values** (`05-create-incomplete-data.php`):

```php
# filename: 05-create-incomplete-data.php
<?php

declare(strict_types=1);

// Generate customer data with intentional missing values
$customers = [];
$cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', null];
$subscriptions = [1, 0, null];

for ($i = 1; $i <= 30; $i++) {
    $customers[] = [
        'customer_id' => $i,
        'age' => rand(0, 10) < 8 ? rand(22, 65) : null, // 20% missing
        'city' => $cities[array_rand($cities)],
        'total_orders' => rand(1, 50),
        'avg_order_value' => rand(0, 10) < 9 ? rand(20, 200) : null, // 10% missing
        'has_subscription' => $subscriptions[array_rand($subscriptions)]
    ];
}

file_put_contents(
    __DIR__ . '/data/incomplete_customers.json',
    json_encode($customers, JSON_PRETTY_PRINT)
);

echo "Generated 30 customer records with missing values\n";
```

2. **Generate the incomplete dataset**:

```bash
php 05-create-incomplete-data.php
```

3. **Create the missing value handler** (`06-handle-missing-values.php`):

```php
# filename: 06-handle-missing-values.php
<?php

declare(strict_types=1);

/**
 * Analyze missing values in dataset
 */
function analyzeMissingValues(array $data): array
{
    $missingCount = [];
    $totalRows = count($data);

    foreach ($data as $row) {
        foreach ($row as $column => $value) {
            if (!isset($missingCount[$column])) {
                $missingCount[$column] = 0;
            }
            if ($value === null || $value === '') {
                $missingCount[$column]++;
            }
        }
    }

    $report = [];
    foreach ($missingCount as $column => $count) {
        $report[$column] = [
            'missing_count' => $count,
            'missing_percentage' => round(($count / $totalRows) * 100, 2)
        ];
    }

    return $report;
}

/**
 * Remove rows with any missing values
 */
function dropMissingRows(array $data): array
{
    return array_filter($data, function ($row) {
        foreach ($row as $value) {
            if ($value === null || $value === '') {
                return false;
            }
        }
        return true;
    });
}

/**
 * Fill missing numeric values with mean
 */
function imputeMean(array $data, string $column): array
{
    // Calculate mean of non-null values
    $values = array_filter(
        array_column($data, $column),
        fn($v) => $v !== null && $v !== ''
    );

    if (empty($values)) {
        return $data;
    }

    $mean = array_sum($values) / count($values);

    // Fill missing values
    return array_map(function ($row) use ($column, $mean) {
        if ($row[$column] === null || $row[$column] === '') {
            $row[$column] = round($mean, 2);
        }
        return $row;
    }, $data);
}

/**
 * Fill missing categorical values with mode (most common)
 */
function imputeMode(array $data, string $column): array
{
    // Find mode
    $values = array_filter(
        array_column($data, $column),
        fn($v) => $v !== null && $v !== ''
    );

    if (empty($values)) {
        return $data;
    }

    $frequency = array_count_values($values);
    arsort($frequency);
    $mode = array_key_first($frequency);

    // Fill missing values
    return array_map(function ($row) use ($column, $mode) {
        if ($row[$column] === null || $row[$column] === '') {
            $row[$column] = $mode;
        }
        return $row;
    }, $data);
}

// Load incomplete data
$data = json_decode(
    file_get_contents(__DIR__ . '/data/incomplete_customers.json'),
    true
);

echo "Original dataset: " . count($data) . " rows\n\n";

// Analyze missing values
$missingReport = analyzeMissingValues($data);
echo "Missing Value Analysis:\n";
foreach ($missingReport as $column => $stats) {
    if ($stats['missing_count'] > 0) {
        echo "- $column: {$stats['missing_count']} missing ({$stats['missing_percentage']}%)\n";
    }
}

// Strategy 1: Drop rows with missing values
$cleanData = dropMissingRows($data);
echo "\n✓ After dropping rows: " . count($cleanData) . " rows remain\n";

// Strategy 2: Impute missing values
$imputedData = $data;
$imputedData = imputeMean($imputedData, 'age');
$imputedData = imputeMean($imputedData, 'avg_order_value');
$imputedData = imputeMode($imputedData, 'city');
$imputedData = imputeMode($imputedData, 'has_subscription');

$afterImpute = analyzeMissingValues($imputedData);
$totalMissing = array_sum(array_column($afterImpute, 'missing_count'));
echo "✓ After imputation: $totalMissing missing values remain\n";

// Save cleaned data
file_put_contents(
    __DIR__ . '/processed/clean_customers.json',
    json_encode($imputedData, JSON_PRETTY_PRINT)
);

echo "\n✓ Cleaned data saved to processed/clean_customers.json\n";
```

4. **Create the processed directory**:

```bash
mkdir -p processed
```

5. **Run the missing value handler**:

```bash
php 06-handle-missing-values.php
```

### Expected Result

```
Original dataset: 30 rows

Missing Value Analysis:
- age: 6 missing (20.00%)
- city: 5 missing (16.67%)
- avg_order_value: 3 missing (10.00%)
- has_subscription: 10 missing (33.33%)

✓ After dropping rows: 16 rows remain
✓ After imputation: 0 missing values remain

✓ Cleaned data saved to processed/clean_customers.json
```

### Why It Works

Missing data is inevitable in real-world scenarios. The three common strategies are:

1. **Deletion** — Remove rows or columns with missing values (simple but loses data)
2. **Mean/Median imputation** — Fill numeric gaps with statistical measures (preserves data size)
3. **Mode imputation** — Fill categorical gaps with the most common value

The choice depends on context: if only 5% of data is missing, deletion is fine. If 30% is missing, imputation preserves more information. The key is being systematic and documenting your approach.

### Troubleshooting

- **Error: "Division by zero"** — A column has all null values. Add a check: `if (empty($values)) return $data;`
- **Incorrect mean calculation** — Ensure you're filtering nulls before calculating: `array_filter($values, fn($v) => $v !== null)`
- **Mode not working** — Check that your column contains strings or integers, not mixed types

## Step 5: Normalizing Numeric Features (~8 min)

### Goal

Scale numeric features to standard ranges to prevent features with large values from dominating the model.

### Actions

1. **Create the normalization toolkit** (`07-normalize-features.php`):

```php
# filename: 07-normalize-features.php
<?php

declare(strict_types=1);

/**
 * Min-Max normalization: Scale values to [0, 1]
 * Formula: (x - min) / (max - min)
 */
function minMaxNormalize(array $data, string $column): array
{
    $values = array_column($data, $column);
    $min = min($values);
    $max = max($values);

    if ($max === $min) {
        // All values are the same, set to 0.5
        return array_map(fn($row) => [
            ...$row,
            $column . '_normalized' => 0.5
        ], $data);
    }

    return array_map(function ($row) use ($column, $min, $max) {
        $normalized = ($row[$column] - $min) / ($max - $min);
        return [
            ...$row,
            $column . '_normalized' => round($normalized, 4)
        ];
    }, $data);
}

/**
 * Z-score normalization (standardization)
 * Formula: (x - mean) / standard_deviation
 * Results in mean=0, std=1
 */
function zScoreNormalize(array $data, string $column): array
{
    $values = array_column($data, $column);
    $mean = array_sum($values) / count($values);

    // Calculate standard deviation
    $squaredDiffs = array_map(fn($v) => ($v - $mean) ** 2, $values);
    $variance = array_sum($squaredDiffs) / count($values);
    $stdDev = sqrt($variance);

    if ($stdDev === 0.0) {
        // No variance, set all to 0
        return array_map(fn($row) => [
            ...$row,
            $column . '_standardized' => 0.0
        ], $data);
    }

    return array_map(function ($row) use ($column, $mean, $stdDev) {
        $standardized = ($row[$column] - $mean) / $stdDev;
        return [
            ...$row,
            $column . '_standardized' => round($standardized, 4)
        ];
    }, $data);
}

/**
 * Robust scaling using median and IQR (inter-quartile range)
 * Better for data with outliers
 */
function robustScale(array $data, string $column): array
{
    $values = array_column($data, $column);
    sort($values);

    $count = count($values);
    $q1Index = (int)floor($count * 0.25);
    $q3Index = (int)floor($count * 0.75);
    $medianIndex = (int)floor($count * 0.5);

    $median = $values[$medianIndex];
    $q1 = $values[$q1Index];
    $q3 = $values[$q3Index];
    $iqr = $q3 - $q1;

    if ($iqr === 0) {
        return array_map(fn($row) => [
            ...$row,
            $column . '_robust' => 0.0
        ], $data);
    }

    return array_map(function ($row) use ($column, $median, $iqr) {
        $scaled = ($row[$column] - $median) / $iqr;
        return [
            ...$row,
            $column . '_robust' => round($scaled, 4)
        ];
    }, $data);
}

// Load clean customer data
$data = json_decode(
    file_get_contents(__DIR__ . '/processed/clean_customers.json'),
    true
);

echo "Normalizing features for " . count($data) . " customers\n\n";

// Show original value ranges
$ages = array_column($data, 'age');
$orders = array_column($data, 'total_orders');
$values = array_column($data, 'avg_order_value');

echo "Original Ranges:\n";
echo "- Age: " . min($ages) . " to " . max($ages) . "\n";
echo "- Total Orders: " . min($orders) . " to " . max($orders) . "\n";
echo "- Avg Order Value: $" . min($values) . " to $" . max($values) . "\n\n";

// Apply all normalization techniques
$normalized = $data;
$normalized = minMaxNormalize($normalized, 'age');
$normalized = minMaxNormalize($normalized, 'total_orders');
$normalized = zScoreNormalize($normalized, 'avg_order_value');

// Display sample
echo "Sample Normalized Data (first 3 customers):\n";
foreach (array_slice($normalized, 0, 3) as $customer) {
    echo "\nCustomer {$customer['customer_id']}:\n";
    echo "  Age: {$customer['age']} → {$customer['age_normalized']} (min-max)\n";
    echo "  Orders: {$customer['total_orders']} → {$customer['total_orders_normalized']} (min-max)\n";
    echo "  Avg Value: \${$customer['avg_order_value']} → {$customer['avg_order_value_standardized']} (z-score)\n";
}

// Save normalized data
file_put_contents(
    __DIR__ . '/processed/normalized_customers.json',
    json_encode($normalized, JSON_PRETTY_PRINT)
);

echo "\n✓ Normalized data saved to processed/normalized_customers.json\n";
```

2. **Run the normalizer**:

```bash
php 07-normalize-features.php
```

### Expected Result

```
Normalizing features for 30 customers

Original Ranges:
- Age: 22 to 65
- Total Orders: 1 to 50
- Avg Order Value: $20 to $200

Sample Normalized Data (first 3 customers):

Customer 1:
  Age: 45 → 0.5349 (min-max)
  Orders: 23 → 0.4490 (min-max)
  Avg Value: $127.5 → 0.3542 (z-score)

Customer 2:
  Age: 28 → 0.1395 (min-max)
  Orders: 8 → 0.1429 (min-max)
  Avg Value: $67.0 → -0.8214 (z-score)

Customer 3:
  Age: 52 → 0.6977 (min-max)
  Orders: 45 → 0.8980 (min-max)
  Avg Value: $185.5 → 1.4523 (z-score)

✓ Normalized data saved to processed/normalized_customers.json
```

### Why It Works

**Min-Max Normalization** scales values to [0, 1] range, preserving the original distribution. It's ideal when you need bounded values.

**Z-Score Standardization** centers data around mean=0 with standard deviation=1. It's preferred when your algorithm assumes normally distributed data (like linear regression or neural networks).

**Robust Scaling** uses median and IQR instead of mean/std, making it resistant to outliers.

Without normalization, a feature like "income" (ranging 20,000-200,000) would dominate "age" (ranging 20-70) in distance-based algorithms like k-NN or SVM.

### Troubleshooting

- **All normalized values are 0 or 1** — Your data has no variance. Check if you loaded the correct column.
- **Division by zero error** — Add checks for zero denominator: `if ($max === $min) return $data;`
- **Normalized values outside [0,1] for min-max** — You applied it to negative numbers. Use z-score instead.

## Step 6: Encoding Categorical Variables (~7 min)

### Goal

Convert text categories (like "gender", "city", "category") into numeric representations that machine learning algorithms can process.

### Actions

1. **Create the categorical encoder** (`08-encode-categorical.php`):

```php
# filename: 08-encode-categorical.php
<?php

declare(strict_types=1);

/**
 * Label Encoding: Convert categories to sequential integers
 * Example: ['red', 'blue', 'green'] → [0, 1, 2]
 */
function labelEncode(array $data, string $column): array
{
    // Get unique values and assign numeric labels
    $uniqueValues = array_unique(array_column($data, $column));
    $mapping = array_flip(array_values($uniqueValues));

    return [
        'data' => array_map(function ($row) use ($column, $mapping) {
            return [
                ...$row,
                $column . '_encoded' => $mapping[$row[$column]]
            ];
        }, $data),
        'mapping' => $mapping
    ];
}

/**
 * One-Hot Encoding: Create binary column for each category
 * Example: 'red' → [1, 0, 0], 'blue' → [0, 1, 0], 'green' → [0, 0, 1]
 */
function oneHotEncode(array $data, string $column): array
{
    $uniqueValues = array_unique(array_column($data, $column));
    sort($uniqueValues);

    return array_map(function ($row) use ($column, $uniqueValues) {
        $encoded = $row;
        foreach ($uniqueValues as $value) {
            $colName = $column . '_' . preg_replace('/[^a-z0-9]/i', '_', strtolower($value));
            $encoded[$colName] = ($row[$column] === $value) ? 1 : 0;
        }
        return $encoded;
    }, $data);
}

/**
 * Frequency Encoding: Replace category with its frequency
 * Useful for high-cardinality categorical features
 */
function frequencyEncode(array $data, string $column): array
{
    // Count occurrences of each value
    $values = array_column($data, $column);
    $frequency = array_count_values($values);

    return array_map(function ($row) use ($column, $frequency) {
        return [
            ...$row,
            $column . '_frequency' => $frequency[$row[$column]]
        ];
    }, $data);
}

// Load customer data
$customers = json_decode(
    file_get_contents(__DIR__ . '/data/customers.csv' . '' ?
        __DIR__ . '/processed/clean_customers.json' :
        __DIR__ . '/data/customers.csv'
    ),
    true
);

// For CSV, load it properly
if (file_exists(__DIR__ . '/data/customers.csv')) {
    $file = fopen(__DIR__ . '/data/customers.csv', 'r');
    $headers = fgetcsv($file);
    $customers = [];
    while (($row = fgetcsv($file)) !== false) {
        $customers[] = array_combine($headers, $row);
    }
    fclose($file);
}

echo "Encoding categorical variables for " . count($customers) . " customers\n\n";

// Example 1: Label Encoding for gender
$result = labelEncode(array_slice($customers, 0, 10), 'gender');
echo "Label Encoding (Gender):\n";
echo "Mapping: " . json_encode($result['mapping']) . "\n";
foreach (array_slice($result['data'], 0, 3) as $customer) {
    echo "- {$customer['gender']} → {$customer['gender_encoded']}\n";
}

// Example 2: One-Hot Encoding for country
$oneHotData = oneHotEncode(array_slice($customers, 0, 10), 'country');
echo "\nOne-Hot Encoding (Country):\n";
foreach (array_slice($oneHotData, 0, 3) as $customer) {
    $countryFields = array_filter(
        $customer,
        fn($key) => str_starts_with($key, 'country_'),
        ARRAY_FILTER_USE_KEY
    );
    echo "- {$customer['country']}: " . json_encode($countryFields) . "\n";
}

// Example 3: Frequency Encoding for city
$freqData = frequencyEncode($customers, 'city');
echo "\nFrequency Encoding (City):\n";
$uniqueCities = array_unique(array_column($customers, 'city'));
foreach (array_slice($uniqueCities, 0, 5) as $city) {
    $example = array_filter($freqData, fn($c) => $c['city'] === $city)[0] ?? null;
    if ($example) {
        echo "- $city: appears {$example['city_frequency']} times\n";
    }
}

// Save encoded data
file_put_contents(
    __DIR__ . '/processed/encoded_customers.json',
    json_encode($freqData, JSON_PRETTY_PRINT)
);

echo "\n✓ Encoded data saved to processed/encoded_customers.json\n";
```

2. **Run the encoder**:

```bash
php 08-encode-categorical.php
```

### Expected Result

```
Encoding categorical variables for 100 customers

Label Encoding (Gender):
Mapping: {"Male":0,"Female":1}
- Male → 0
- Female → 1
- Male → 0

One-Hot Encoding (Country):
- USA: {"country_usa":1}
- USA: {"country_usa":1}
- USA: {"country_usa":1}

Frequency Encoding (City):
- New York: appears 5 times
- Los Angeles: appears 4 times
- Chicago: appears 6 times
- Houston: appears 3 times
- Phoenix: appears 4 times

✓ Encoded data saved to processed/encoded_customers.json
```

### Why It Works

Machine learning algorithms require numeric inputs. **Label encoding** works for ordinal data (e.g., "small" < "medium" < "large"). **One-hot encoding** is essential for nominal data where no ordering exists (e.g., colors, countries), preventing the model from assuming "USA" < "Canada" < "Mexico". **Frequency encoding** is useful when you have hundreds of categories and one-hot would create too many columns.

### Troubleshooting

- **Too many columns after one-hot** — You have high cardinality. Use label or frequency encoding instead, or reduce categories by grouping rare ones into "Other"
- **Memory issues** — One-hot encoding 1000+ categories creates massive arrays. Use frequency encoding or target encoding instead
- **Order matters in label encoding** — If your categories are ordinal (t-shirt sizes), manually specify the order instead of auto-generating it

## Step 7: Building a Complete Preprocessing Pipeline (~6 min)

### Goal

Create a reusable, object-oriented preprocessing pipeline that combines all techniques into a single workflow.

### Actions

1. **Create the pipeline class** (`09-preprocessing-pipeline.php`):

```php
# filename: 09-preprocessing-pipeline.php
<?php

declare(strict_types=1);

/**
 * Data Preprocessing Pipeline
 *
 * Combines loading, cleaning, normalization, and encoding
 * into a reusable workflow.
 */
class PreprocessingPipeline
{
    private array $data = [];
    private array $transformations = [];

    public function load(string $source, string $type = 'csv'): self
    {
        $this->data = match($type) {
            'csv' => $this->loadCsv($source),
            'json' => $this->loadJson($source),
            'database' => $this->loadDatabase($source),
            default => throw new InvalidArgumentException("Unsupported type: $type")
        };

        return $this;
    }

    public function handleMissing(string $column, string $strategy = 'drop'): self
    {
        $this->data = match($strategy) {
            'drop' => array_filter($this->data, fn($row) => !empty($row[$column])),
            'mean' => $this->imputeMean($column),
            'mode' => $this->imputeMode($column),
            'zero' => array_map(fn($row) => [
                ...$row,
                $column => $row[$column] ?? 0
            ], $this->data),
            default => $this->data
        };

        $this->transformations[] = "HandleMissing($column, $strategy)";
        return $this;
    }

    public function normalize(string $column, string $method = 'minmax'): self
    {
        $this->data = match($method) {
            'minmax' => $this->minMaxNormalize($column),
            'zscore' => $this->zScoreNormalize($column),
            default => $this->data
        };

        $this->transformations[] = "Normalize($column, $method)";
        return $this;
    }

    public function encode(string $column, string $method = 'label'): self
    {
        $this->data = match($method) {
            'label' => $this->labelEncode($column),
            'onehot' => $this->oneHotEncode($column),
            'frequency' => $this->frequencyEncode($column),
            default => $this->data
        };

        $this->transformations[] = "Encode($column, $method)";
        return $this;
    }

    public function get(): array
    {
        return $this->data;
    }

    public function save(string $path): void
    {
        file_put_contents($path, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    public function summary(): string
    {
        $output = "Pipeline Summary:\n";
        $output .= "- Records: " . count($this->data) . "\n";
        $output .= "- Transformations applied:\n";
        foreach ($this->transformations as $t) {
            $output .= "  • $t\n";
        }
        return $output;
    }

    // Private helper methods
    private function loadCsv(string $path): array
    {
        $file = fopen($path, 'r');
        $headers = fgetcsv($file);
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            $data[] = array_combine($headers, $row);
        }
        fclose($file);
        return $data;
    }

    private function loadJson(string $path): array
    {
        return json_decode(file_get_contents($path), true);
    }

    private function loadDatabase(string $query): array
    {
        // Simplified - would need connection details in real implementation
        return [];
    }

    private function imputeMean(string $column): array
    {
        $values = array_filter(array_column($this->data, $column));
        $mean = array_sum($values) / count($values);

        return array_map(fn($row) => [
            ...$row,
            $column => $row[$column] ?? $mean
        ], $this->data);
    }

    private function imputeMode(string $column): array
    {
        $values = array_filter(array_column($this->data, $column));
        $frequency = array_count_values($values);
        arsort($frequency);
        $mode = array_key_first($frequency);

        return array_map(fn($row) => [
            ...$row,
            $column => $row[$column] ?? $mode
        ], $this->data);
    }

    private function minMaxNormalize(string $column): array
    {
        $values = array_column($this->data, $column);
        $min = min($values);
        $max = max($values);

        if ($max === $min) return $this->data;

        return array_map(fn($row) => [
            ...$row,
            $column . '_normalized' => ($row[$column] - $min) / ($max - $min)
        ], $this->data);
    }

    private function zScoreNormalize(string $column): array
    {
        $values = array_column($this->data, $column);
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
        $stdDev = sqrt($variance);

        if ($stdDev === 0) return $this->data;

        return array_map(fn($row) => [
            ...$row,
            $column . '_standardized' => ($row[$column] - $mean) / $stdDev
        ], $this->data);
    }

    private function labelEncode(string $column): array
    {
        $unique = array_unique(array_column($this->data, $column));
        $mapping = array_flip(array_values($unique));

        return array_map(fn($row) => [
            ...$row,
            $column . '_encoded' => $mapping[$row[$column]]
        ], $this->data);
    }

    private function oneHotEncode(string $column): array
    {
        $unique = array_unique(array_column($this->data, $column));

        return array_map(function($row) use ($column, $unique) {
            $encoded = $row;
            foreach ($unique as $value) {
                $encoded[$column . '_' . $value] = ($row[$column] === $value) ? 1 : 0;
            }
            return $encoded;
        }, $this->data);
    }

    private function frequencyEncode(string $column): array
    {
        $frequency = array_count_values(array_column($this->data, $column));

        return array_map(fn($row) => [
            ...$row,
            $column . '_frequency' => $frequency[$row[$column]]
        ], $this->data);
    }
}

// Example: Complete preprocessing workflow
$pipeline = new PreprocessingPipeline();

$processed = $pipeline
    ->load(__DIR__ . '/data/customers.csv', 'csv')
    ->handleMissing('age', 'mean')
    ->normalize('age', 'minmax')
    ->normalize('total_orders', 'minmax')
    ->encode('gender', 'label')
    ->encode('country', 'frequency')
    ->get();

echo $pipeline->summary();
echo "\nFirst 2 processed records:\n";
print_r(array_slice($processed, 0, 2));

// Save for use in future ML chapters
$pipeline->save(__DIR__ . '/processed/final_preprocessed.json');
echo "\n✓ Final preprocessed data saved\n";
```

2. **Run the pipeline**:

```bash
php 09-preprocessing-pipeline.php
```

### Expected Result

```
Pipeline Summary:
- Records: 100
- Transformations applied:
  • HandleMissing(age, mean)
  • Normalize(age, minmax)
  • Normalize(total_orders, minmax)
  • Encode(gender, label)
  • Encode(country, frequency)

First 2 processed records:
Array
(
    [0] => Array
        (
            [customer_id] => 1
            [first_name] => John
            [age] => 28
            [age_normalized] => 0.13953488372093
            [total_orders] => 12
            [total_orders_normalized] => 0.35483870967742
            [gender] => Male
            [gender_encoded] => 0
            [country] => USA
            [country_frequency] => 100
        )
    ...
)

✓ Final preprocessed data saved
```

### Why It Works

The pipeline pattern chains transformations in a readable, maintainable way. Each method returns `$this`, enabling method chaining. The `$transformations` array tracks what was done, which is crucial for reproducibility—you can later apply the same pipeline to new data (e.g., in production). This OOP approach encapsulates complexity and provides a clean API for data preprocessing.

### Troubleshooting

- **Method chaining breaks** — Ensure every public method returns `$this`
- **Transformation order matters** — Always handle missing values first, then normalize, then encode
- **Pipeline fails on new data** — Save the transformation parameters (mean, min, max, mappings) and reapply them

## Step 8: Splitting Data for Training and Testing (~5 min)

### Goal

Learn how to properly split data to prevent data leakage and evaluate models fairly.

### Why It Matters

**Data leakage** is one of the most insidious problems in machine learning—it happens when information from your test set influences your training process, leading to overly optimistic performance estimates that collapse in production.

The solution? Split your data FIRST, then calculate all preprocessing parameters (mean, min, max, encodings) from the training set only. Apply those same parameters to the test set. This ensures your test set remains truly unseen.

### Actions

**Run the train/test split example:**

```bash
php 10-train-test-split.php
```

**Expected results:**

```
======================================================================
Example 1: Simple 80/20 Train/Test Split
======================================================================
Split sizes:
  Training set:   80 samples (80%)
  Test set:       20 samples (20%)

======================================================================
Example 2: Three-Way Split (Train/Validation/Test)
======================================================================
Split sizes:
  Training set:   70 samples (70%)
  Validation set: 15 samples (15%)
  Test set:       15 samples (15%)

Why use validation set?
  - Training: Learn model parameters
  - Validation: Tune hyperparameters, select best model
  - Test: Final evaluation on completely unseen data

======================================================================
Example 3: Stratified Split (Maintains Class Distribution)
======================================================================
Class distribution (has_subscription = 1):
  Training:  45 / 79 = 57%
  Test:      12 / 21 = 57.1%
  → Distributions are similar (stratification working!)

======================================================================
Preventing Data Leakage
======================================================================

❌ WRONG: Don't do this
  1. Normalize entire dataset
  2. Then split into train/test
  → Test data "leaks" into training via normalization parameters!

✓ CORRECT: Do this
  1. Split data first
  2. Calculate normalization parameters from TRAINING data only
  3. Apply those same parameters to test data
  → Test data remains truly unseen
```

### Why It Works

The code demonstrates three splitting strategies:

1. **Simple split (80/20)** - Most common for large datasets
2. **Three-way split (70/15/15)** - When you need a validation set for hyperparameter tuning
3. **Stratified split** - Maintains class proportions, crucial for imbalanced classification

By shuffling data before splitting and setting a random seed, you ensure reproducibility while avoiding sequential bias (e.g., if your data is sorted by date).

### Key Concepts

- **Training set**: Data used to learn model parameters
- **Validation set**: Data used to tune hyperparameters and select models
- **Test set**: Final evaluation on completely unseen data
- **Stratification**: Maintaining class distribution across splits
- **Data leakage**: When test information influences training

### Troubleshooting

- **Imbalanced classes** — Use stratified split to maintain class proportions
- **Time-series data** — Don't shuffle; split chronologically (later chapter)
- **Small datasets** — Consider cross-validation instead of single split (Chapter 06)

## Step 9: Feature Engineering Basics (~7 min)

### Goal

Create derived features that capture domain knowledge and improve model performance.

### Why It Matters

Raw features often don't directly represent the patterns you want your model to learn. Feature engineering transforms existing features into new representations that make patterns more obvious. For example, age alone might not predict behavior well, but age groups (Young/Middle/Senior) often reveal distinct patterns.

### Actions

**Run the feature engineering example:**

```bash
php 12-feature-engineering.php
```

**Expected results:**

```
======================================================================
Feature Engineering Examples
======================================================================

1. Age Binning: Group continuous ages into categories
----------------------------------------------------------------------
Age groups created:
  - Young (0-29): 16 customers (16%)
  - Middle (30-44): 58 customers (58%)
  - Senior (45-59): 26 customers (26%)

2. Interaction Feature: Subscription × Age
----------------------------------------------------------------------
Sample interactions:
  John: Subscription(1) × Age(28) = 28
  Jane: Subscription(0) × Age(34) = 0

3. Ratio Feature: Average Order Value per Order
----------------------------------------------------------------------
Sample spending patterns:
  John: $1,026.00 total / 12 orders = $85.50 per order

4. Polynomial Features: Age Squared
----------------------------------------------------------------------
Sample polynomial features:
  John: Age=28, Age²=784

5. Time-based Features: Account Age
----------------------------------------------------------------------
Sample time features:
  John: Created 2022-03-15
    → Year: 2022, Month: 3
    → Days ago: 1321 days

======================================================================
Feature Engineering Summary
======================================================================

Original features:  13
Engineered features: 10
Total features:      23
```

### Why It Works

Each technique serves a different purpose:

- **Binning** converts continuous features into categories when relationships are non-linear
- **Interactions** capture when two features combined create new meaning
- **Ratios** create relative measures that often matter more than absolutes
- **Polynomials** capture curved relationships (e.g., returns diminishing at high values)
- **Time features** extract seasonal patterns and trends from dates

### Key Concepts

- **Feature engineering**: Creating new features from existing ones
- **Domain knowledge**: Using business understanding to guide feature creation
- **Interaction effects**: When features combined are more predictive than alone
- **Non-linear transformations**: Capturing curved relationships

### Troubleshooting

- **Too many features** — More isn't always better; irrelevant features add noise
- **Data leakage** — Never use target variable or future information in features
- **Complexity** — Start simple; test if each new feature improves performance

## Step 10: Saving Preprocessing Parameters for Production (~6 min)

### Goal

Save preprocessing parameters so you can apply identical transformations to new data in production.

### Why It Matters

In production, you need to preprocess incoming data exactly as you preprocessed training data. This means using the same min/max values, same mean/std, same category encodings. If you recalculate these from new data, you'll get different transformations and your model will fail.

### Actions

**Run the parameter persistence example:**

```bash
php 11-save-load-pipeline.php
```

**Expected results:**

```
======================================================================
Phase 1: Training Pipeline on Dataset
======================================================================
✓ Training pipeline complete
✓ Parameters saved to: pipeline_parameters.json

Saved Parameters:
  - minmax_age: {"min":25,"max":58,"column":"age"}
  - zscore_total_orders: {"mean":13.51,"std":5.83,"column":"total_orders"}
  - label_gender: {"mapping":{"Male":0,"Female":1},"column":"gender"}

======================================================================
Phase 2: New Data Arrives (Production)
======================================================================
→ New customers arrived:
  - New Customer (Age: 35, Gender: Female)
  - Another User (Age: 42, Gender: Male)

======================================================================
Phase 3: Applying Saved Parameters to New Data
======================================================================
✓ New data preprocessed with consistent parameters!

Processed New Customers:

New Customer:
  Original Age: 35 → Normalized: 0.303
  Original Orders: 15 → Standardized: 0.2556
  Original Gender: Female → Encoded: 1
```

### Why It Works

The pipeline saves three types of parameters:

1. **Normalization bounds** (min/max for each feature)
2. **Statistical moments** (mean/std for z-score)
3. **Category mappings** (label encodings, one-hot columns)

When new data arrives, you load these parameters and apply the exact same transformations. This ensures consistency between training and production.

### Key Concepts

- **Parameter persistence**: Saving preprocessing parameters for reuse
- **Consistency**: Applying identical transforms to training and new data
- **Versioning**: Tracking which parameter version preprocessed which model
- **Data drift**: When new data distributions differ from training

### Troubleshooting

- **Unknown categories** — New categorical values not seen in training need default handling
- **Out-of-range values** — Decide whether to clip or allow extrapolation
- **Parameter version mismatch** — Always pair parameter files with model versions

## Step 11: Outlier Detection (~5 min)

### Goal

Identify extreme values that may be errors or require special handling.

### Why It Matters

Outliers can dramatically affect model training—a single extreme value can pull regression lines, distort normalizations, and reduce accuracy. But not all outliers are bad! Sometimes they represent important behavior (VIP customers, fraud) that you want to detect. The key is identifying them systematically.

### Actions

**Run the outlier detection example:**

```bash
php 13-outlier-detection.php
```

**Expected results:**

```txt
======================================================================
Outlier Detection Examples
======================================================================

Basic Statistics:
  Count     : 100
  Mean      : 100.11
  Min       : 22.4
  Max       : 221.3

Box Plot Visualization:
[         #######-##########                      ]
22.4      63.85   93.8      132.45                 221.3

======================================================================
Method 1: Z-Score Outlier Detection
======================================================================
Found 1 outliers using Z-score method (threshold=2.5)

Outliers detected:
  Row 15: Patricia Clark - $221.30 (Z-score: 2.6156)

======================================================================
Method 2: IQR Outlier Detection
======================================================================
Found 0 outliers using IQR method (multiplier=1.5)

======================================================================
Outlier Handling Strategies
======================================================================

Strategy 1: Remove Outliers
  Original size: 100 rows
  After removal:  99 rows

Strategy 2: Cap Outliers (Winsorization)
  Capping values beyond bounds
```

### Why It Works

The code demonstrates two methods:

1. **Z-score method**: Identifies values beyond N standard deviations from mean (good for normal distributions)
2. **IQR method**: Uses quartile ranges (more robust to extreme values, good for skewed data)

Then shows three handling strategies:

- **Remove**: Delete outlier rows (when they're errors)
- **Cap**: Limit values to threshold (when outliers are real but extreme)
- **Keep**: Leave them (when they're important patterns)

### Key Concepts

- **Outliers**: Extreme values far from the typical distribution
- **Z-score**: Measures how many standard deviations from mean
- **IQR**: Interquartile range (difference between 75th and 25th percentiles)
- **Robustness**: Method's sensitivity to extreme values

### Troubleshooting

- **Too many outliers detected** — Adjust threshold or use more robust method
- **Skewed data** — Use IQR instead of Z-score
- **Domain knowledge** — Always check if outliers are errors or real patterns

## Exercises

### Exercise 1: Load and Clean E-commerce Data

**Goal**: Practice the complete data loading and cleaning workflow

Create a script that:

1. Loads `data/customers.csv`
2. Identifies columns with missing values
3. Fills missing numeric values with the median (not mean)
4. Fills missing categorical values with "Unknown"
5. Saves the cleaned data to `processed/exercise1_clean.csv`

**Validation**: Your output should have 100 rows with zero null values

```php
// Test: Load and check
$data = json_decode(file_get_contents('processed/exercise1_clean.json'), true);
echo "Rows: " . count($data) . "\n";
// Expected: Rows: 100
```

### Exercise 2: Normalize Product Prices

**Goal**: Apply multiple normalization techniques and compare results

Load products from the database and:

1. Extract `price` and `rating` columns
2. Apply min-max normalization to `price`
3. Apply z-score normalization to `price`
4. Apply robust scaling to `price`
5. Display the first 5 products with all three normalized values side-by-side

**Validation**: Min-max values should be in [0, 1], z-scores centered around 0

### Exercise 3: One-Hot Encode Product Categories

**Goal**: Handle multi-class categorical encoding

Create a script that:

1. Loads products from `data/products.db`
2. Extracts the `category` column
3. Applies one-hot encoding
4. Counts how many binary columns were created
5. Saves the result with column names like `category_Electronics`, `category_Sports_Outdoors`

**Validation**: Number of new columns should equal number of unique categories (6)

### Exercise 4: Build a Custom Pipeline with Train/Test Split

**Goal**: Create an end-to-end preprocessing pipeline for a specific ML task

Task: Prepare customer data for predicting `total_orders` (a regression task)

Your pipeline should:

1. Load `data/customers.csv`
2. Remove the target variable (`total_orders`) and ID column
3. Handle missing values in `age` and `avg_order_value` with mean imputation
4. Normalize `age` and `avg_order_value` with min-max
5. One-hot encode `gender` and `country`
6. **Split into train (80%) and test (20%) sets**
7. Save features (X) and target (y) separately for both train and test

**Validation**:

- Features should have ~10-15 columns (after one-hot), all numeric, no missing values
- Training set: ~80 samples, Test set: ~20 samples
- No data leakage: preprocessing parameters calculated from training data only

### Exercise 5: Outlier Detection and Handling

**Goal**: Detect and handle outliers in product pricing data

Create a script that:

1. Loads products from `data/products.db`
2. Analyzes the `price` column for outliers
3. Uses both Z-score (threshold=2.5) and IQR (multiplier=1.5) methods
4. Compares results: which method found more outliers?
5. Creates two cleaned datasets:
   - One with outliers removed
   - One with outliers capped (winsorization)
6. Reports the impact on mean and standard deviation

**Validation**:

- Both methods should identify outliers (if any exist)
- Capped dataset should have same size as original
- Removed dataset should have fewer rows
- Mean should change after outlier handling

## Troubleshooting

### Error: "Array to string conversion"

**Symptom**: `Warning: Array to string conversion in line XX`

**Cause**: You're trying to use `echo` or string concatenation on an array

**Solution**: Use `print_r()`, `var_dump()`, or `json_encode()` for arrays:

```php
// Wrong
echo $customer;

// Correct
echo json_encode($customer);
// or
print_r($customer);
```

### Error: "Undefined array key"

**Symptom**: `Warning: Undefined array key "column_name" in line XX`

**Cause**: Your CSV/JSON has inconsistent column names or the row is missing that key

**Solution**: Use null coalescing operator:

```php
// Wrong
$value = $row['age'];

// Correct
$value = $row['age'] ?? null;

// Or filter first
if (isset($row['age'])) {
    $value = $row['age'];
}
```

### Division by Zero in Normalization

**Symptom**: `Warning: Division by zero`

**Cause**: All values in a column are identical (max === min or stdDev === 0)

**Solution**: Add a guard clause:

```php
if ($max === $min) {
    // All values same, skip normalization
    return $data;
}
```

### Memory Exhausted on Large Files

**Symptom**: `Fatal error: Allowed memory size exhausted`

**Cause**: Loading entire CSV into memory with large files (100k+ rows)

**Solution**: Process in chunks:

```php
$file = fopen('large.csv', 'r');
$headers = fgetcsv($file);
$batchSize = 1000;
$batch = [];

while (($row = fgetcsv($file)) !== false) {
    $batch[] = array_combine($headers, $row);

    if (count($batch) >= $batchSize) {
        processData($batch); // Your processing function
        $batch = []; // Clear batch
    }
}

// Process remaining rows
if (!empty($batch)) {
    processData($batch);
}

fclose($file);
```

### JSON Decode Returns Null

**Symptom**: `json_decode()` returns null but no error

**Cause**: Invalid JSON syntax or encoding issues

**Solution**: Check the JSON error:

```php
$data = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    // Common: "Syntax error" or "Malformed UTF-8 characters"
}

// Or validate with external tool first:
// cat data.json | python -m json.tool
```

## Wrap-up

Congratulations! You've built a comprehensive data preprocessing toolkit in PHP. Let's review what you've accomplished:

✅ **Loaded data** from multiple sources: CSV files, SQLite databases, JSON files, and (conceptually) web APIs  
✅ **Identified and handled missing values** using deletion, mean imputation, and mode imputation strategies  
✅ **Normalized numeric features** with three techniques: min-max scaling, z-score standardization, and robust scaling  
✅ **Encoded categorical variables** using label encoding, one-hot encoding, and frequency encoding  
✅ **Built a reusable OOP pipeline** that chains transformations for clean, reproducible workflows  
✅ **Processed real datasets** with 100 customers and 20 products, preparing them for machine learning

These preprocessing skills are foundational—they apply to every ML project regardless of algorithm or domain. Clean, well-prepared data is the difference between a model that performs at 60% accuracy and one that reaches 90%.

In the next chapter, you'll put this preprocessed data to work by building your first machine learning model: a linear regression predictor. You'll see firsthand how the quality of your preprocessing directly impacts model performance.

**Want more practice?** Check out the exercise solutions in the `solutions/` directory to see complete implementations of all four exercises.

## Knowledge Check

Test your understanding of data preprocessing concepts:

<!-- <Quiz
<!-- title="Chapter 04 Quiz: Data Preprocessing"
<!-- :questions="[
<!-- {
<!-- question: 'When should you use mean imputation vs. mode imputation for missing values?',
<!-- options: [
<!-- { text: 'Use mean for numeric data, mode for categorical data', correct: true, explanation: 'Mean (average) is appropriate for numeric data as it maintains the distribution. Mode (most common value) is used for categorical data since averaging text values does not make sense.' },
<!-- { text: 'Always use mean imputation for better accuracy', correct: false, explanation: 'Mean only works for numeric data. Using it on categorical data (like \"red\", \"blue\") is impossible.' },
<!-- { text: 'Use mode for all types of data', correct: false, explanation: 'While mode can technically work for both, mean is more statistically appropriate for numeric data as it better preserves the data distribution.' },
<!-- { text: 'They are interchangeable', correct: false, explanation: 'Mean and mode serve different purposes based on data type. Mean for numbers, mode for categories.' }
<!-- ]
<!-- },
<!-- {
<!-- question: 'What is the main difference between min-max normalization and z-score standardization?',
<!-- options: [
<!-- { text: 'Min-max scales to [0,1], z-score centers around mean=0 with std=1', correct: true, explanation: 'Min-max normalization bounds values to [0, 1] range. Z-score (standardization) transforms data to have mean=0 and standard deviation=1, which can produce values outside [0, 1].' },
<!-- { text: 'Min-max is faster to compute', correct: false, explanation: 'Both are similarly fast. The key difference is in their output range and use cases.' },
<!-- { text: 'Z-score only works with integers', correct: false, explanation: 'Z-score works with any numeric data, including floats.' },
<!-- { text: 'They produce identical results', correct: false, explanation: 'They produce different results. Min-max gives [0, 1], z-score can be negative and exceed 1.' }
<!-- ]
<!-- },
<!-- {
<!-- question: 'When should you use one-hot encoding instead of label encoding?',
<!-- options: [
<!-- { text: 'For nominal categorical data with no inherent order (colors, countries)', correct: true, explanation: 'One-hot encoding prevents the model from assuming false ordinal relationships. For example, encoding countries as 1, 2, 3 implies USA < Canada < Mexico, which is meaningless.' },
<!-- { text: 'Always use one-hot encoding for better accuracy', correct: false, explanation: 'One-hot creates many columns with high-cardinality data. For ordinal data (small < medium < large), label encoding is more appropriate.' },
<!-- { text: 'Only when you have less than 5 categories', correct: false, explanation: 'The number of categories affects practicality (memory/performance), but the key criterion is whether the data is nominal (no order) or ordinal (has order).' },
<!-- { text: 'One-hot and label encoding are the same', correct: false, explanation: 'Label encoding assigns integers (0, 1, 2...) to categories. One-hot creates separate binary columns for each category.' }
<!-- ]
<!-- },
<!-- {
<!-- question: 'Why is it important to normalize numeric features before machine learning?',
<!-- options: [
<!-- { text: 'Prevents features with large values from dominating distance-based algorithms', correct: true, explanation: 'Without normalization, a feature ranging 0-100,000 (like income) will dominate a feature ranging 0-100 (like age) in distance calculations, making the smaller-scale feature effectively invisible to algorithms like k-NN or SVM.' },
<!-- { text: 'It makes the code run faster', correct: false, explanation: 'While there can be computational benefits, the main reason is to ensure fair feature importance in the model.' },
<!-- { text: 'Required by PHP syntax', correct: false, explanation: 'PHP does not require normalization - it is a machine learning best practice regardless of programming language.' },
<!-- { text: 'To make data visualization prettier', correct: false, explanation: 'While normalized data may be easier to visualize, the primary goal is to improve model performance by giving all features equal importance.' }
<!-- ]
<!-- },
<!-- {
<!-- question: 'What is the best strategy for handling missing values when 50% of your data has nulls in a column?',
<!-- options: [
<!-- { text: 'Consider dropping the entire column or investigating why so much data is missing', correct: true, explanation: 'When over half the data is missing, imputation may introduce more bias than removing the column. The high missing rate might indicate a data collection problem that needs investigation.' },
<!-- { text: 'Always impute with mean values', correct: false, explanation: 'Imputing 50% of values with the mean of the other 50% creates a highly artificial dataset that may not represent reality.' },
<!-- { text: 'Delete all rows with missing values', correct: false, explanation: 'This would delete 50% of your dataset, potentially leaving insufficient data for training.' },
<!-- { text: 'Fill with zeros', correct: false, explanation: 'Filling with zeros assumes zero is a meaningful value, which is rarely true and can severely skew your data distribution.' }
<!-- ]
<!-- }
<!-- ]"
/> -->

## Production Considerations

When deploying preprocessing pipelines to production environments, consider these critical factors:

### 1. Parameter Versioning

**Save all transformation parameters** after training:

```php
// Save parameters with version info
$params = [
    'version' => '1.0.0',
    'created_at' => date('Y-m-d H:i:s'),
    'parameters' => $pipeline->getParameters(),
    'model_id' => 'customer_churn_v1'
];
file_put_contents('pipeline_v1.0.0.json', json_encode($params));
```

**Always pair parameter files with model versions**—if you update your model, save a new parameter file. This enables rollbacks and A/B testing.

### 2. Handling New Data Issues

**Unknown categories**: New categorical values not seen during training:

```php
// Handle unknown categories gracefully
$encoding = $savedParams['label_country']['mapping'];
$countryCode = $encoding[$customer['country']] ?? -1; // -1 for unknown
```

**Out-of-range values**: Values beyond training min/max:

```php
// Option 1: Clip to boundaries
$normalized = min(max($value, 0), 1);

// Option 2: Allow extrapolation (may cause issues)
$normalized = ($value - $min) / ($max - $min);
```

### 3. Data Drift Monitoring

Track how new data distributions differ from training data:

```php
// Compare distributions monthly
$trainingMean = $savedParams['zscore_age']['mean'];
$productionMean = array_sum($newAges) / count($newAges);

if (abs($productionMean - $trainingMean) > 5) {
    log_warning("Age distribution has drifted significantly");
    trigger_retraining();
}
```

**When to retrain:**

- Feature distributions change by > 10%
- New categories appear frequently
- Model performance degrades

### 4. Documentation and Audit Trails

Keep detailed records of all preprocessing decisions:

```php
$auditLog = [
    'date' => date('Y-m-d H:i:s'),
    'pipeline_version' => '1.0.0',
    'transformations' => [
        'age' => ['type' => 'minmax', 'min' => 25, 'max' => 58],
        'gender' => ['type' => 'label', 'mapping' => ['M' => 0, 'F' => 1]],
    ],
    'outliers_removed' => 3,
    'missing_values_imputed' => 12,
    'rationale' => 'Z-score outliers removed; age < 18 considered data errors'
];
```

This documentation is crucial for:

- Regulatory compliance (GDPR, financial regulations)
- Debugging production issues
- Knowledge transfer to new team members
- Reproducing results

### 5. Performance Optimization

For high-throughput production systems:

- **Batch processing**: Process multiple records together
- **Caching**: Store parameters in memory (Redis, Memcached)
- **Parallel processing**: Use async workers for independent transformations
- **Pre-compute**: If features are expensive, cache engineered features

```php
// Example: Batch normalization
function batchNormalize(array $records, array $params): array {
    $min = $params['min'];
    $max = $params['max'];
    $range = $max - $min;

    return array_map(
        fn($record) => ($record - $min) / $range,
        $records
    );
}
```

## Further Reading

### Core Concepts

- [Data Preprocessing in Machine Learning](https://en.wikipedia.org/wiki/Data_pre-processing) — Theoretical background
- [Feature Scaling Techniques](https://scikit-learn.org/stable/modules/preprocessing.html) — Comprehensive guide (Python, but concepts apply)
- [Handling Missing Data](https://www.kaggle.com/code/alexisbcook/missing-values) — Kaggle tutorial with practical examples

### Feature Engineering

- [Feature Engineering for Machine Learning](https://www.oreilly.com/library/view/feature-engineering-for/9781491953235/) — O'Reilly book (comprehensive)
- [Feature Engineering Techniques](https://www.kaggle.com/learn/feature-engineering) — Kaggle course
- [Automated Feature Engineering](https://towardsdatascience.com/automated-feature-engineering-in-python-99baf11cc219) — Advanced techniques

### Train/Test Splitting

- [Cross-Validation](https://scikit-learn.org/stable/modules/cross_validation.html) — Beyond simple splits
- [Stratified Sampling](https://en.wikipedia.org/wiki/Stratified_sampling) — Maintaining class distributions
- [Time Series Splits](https://scikit-learn.org/stable/modules/generated/sklearn.model_selection.TimeSeriesSplit.html) — For temporal data

### Production ML

- [Rules of Machine Learning](https://developers.google.com/machine-learning/guides/rules-of-ml) — Google's best practices
- [ML System Design](https://github.com/chiphuyen/machine-learning-systems-design) — Production considerations
- [Data Versioning](https://dvc.org/) — Version control for data and models

### PHP Resources

- [PHP CSV Functions](https://www.php.net/manual/en/function.fgetcsv.php) — Official documentation for CSV handling
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php) — PHP Data Objects for database access
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/) — PHP coding standards used in this chapter
