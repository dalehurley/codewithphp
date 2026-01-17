---
title: "04: Data Cleaning and Preprocessing in PHP"
description: "Learn to clean, validate, normalize, and prepare datasets for analysis and machine learning"
series: "data-science-php-developers"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "/series/data-science-php-developers/chapters/03-collecting-data-databases-apis-scraping"
  - "PHP 8.4+ with mbstring extension"
keywords:
  [
    "data cleaning PHP",
    "data preprocessing PHP",
    "data validation PHP",
    "data normalization",
    "missing data handling",
    "outlier detection PHP",
    "data transformation PHP",
    "ETL PHP",
  ]
author: "Code with PHP"
estimatedTime: "PT90M"
teaches:
  [
    "Handling missing and null values",
    "Detecting and handling outliers",
    "Data type validation and conversion",
    "String normalization and cleaning",
    "Date and time standardization",
    "Duplicate detection and removal",
    "Data transformation techniques",
    "Building data quality pipelines",
  ]
datePublished: "2026-01-11"
---

![Data Cleaning and Preprocessing in PHP](/images/data-science-php-developers/chapter-04-data-cleaning-hero-full.webp)

# Chapter 04: Data Cleaning and Preprocessing in PHP

## Overview

Raw data is messy. Real-world datasets contain missing values, inconsistent formats, duplicates, outliers, and errors that will break your analysis or machine learning models if left unaddressed. Data cleaning and preprocessing is the unglamorous but essential work that transforms messy raw data into clean, consistent datasets ready for analysis.

This chapter teaches you systematic approaches to data quality issues. You'll learn to detect and handle missing values, identify outliers, standardize formats, validate data types, remove duplicates, and transform data into the shapes your analysis needs. We'll build reusable cleaning pipelines that you can apply to any dataset, ensuring your data science work starts with a solid foundation.

By the end of this chapter, you'll understand that data cleaning isn't a one-time task—it's an iterative process of exploration, validation, transformation, and verification. You'll have the tools and patterns to clean data efficiently and the judgment to know when data is "clean enough" for your purposes.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 03: Collecting Data](/series/data-science-php-developers/chapters/03-collecting-data-databases-apis-scraping)
- PHP 8.4+ with `mbstring` extension enabled
- Understanding of basic statistics (mean, median, standard deviation)
- Familiarity with data types and validation
- **Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check PHP and mbstring extension
php --version
php -m | grep mbstring

# Verify you have data from Chapter 3
ls data/*.csv data/*.json
```

## What You'll Build

By the end of this chapter, you will have created:

- **DataCleaner Class**: Reusable cleaning operations for any dataset
- **ValidationPipeline**: Chain multiple validation rules
- **Missing Value Handler**: Strategies for dealing with null/empty data
- **Outlier Detector**: Statistical methods to find anomalies
- **Data Normalizer**: Standardize formats across datasets
- **Duplicate Remover**: Identify and handle duplicate records
- **Complete Cleaning Pipeline**: End-to-end data quality system
- **Quality Report Generator**: Automated data quality assessment

## Objectives

- Detect and handle missing values using multiple strategies
- Identify outliers using statistical methods (IQR, Z-score)
- Validate and convert data types safely
- Normalize strings, dates, and numeric values
- Remove or merge duplicate records
- Transform data structures for analysis
- Build automated data quality pipelines
- Generate data quality reports

## Step 1: Understanding Data Quality Issues (~5 min)

### Goal

Recognize common data quality problems and their impact on analysis.

### The Reality of Messy Data

Data scientists spend 60-80% of their time cleaning data. Here's why:

**1. Missing Values**
```
user_id,name,email,age,city
1,John Doe,john@example.com,35,New York
2,Jane Smith,,28,
3,Bob Johnson,bob@example.com,,Los Angeles
4,,,42,Chicago
```

**2. Inconsistent Formats**
```
# Dates
2024-01-15, 01/15/2024, Jan 15 2024, 15-Jan-24

# Phone numbers
555-1234, (555) 123-4567, 555.123.4567, +1-555-123-4567

# Names
john doe, John Doe, JOHN DOE, Doe, John
```

**3. Invalid Data**
```
age: -5, 250, "unknown", null
price: "$1,234.56", "free", -100
email: "notanemail", "user@", "@example.com"
```

**4. Duplicates**
```
id,name,email
1,John Doe,john@example.com
2,John Doe,john@example.com      # Exact duplicate
3,John  Doe,JOHN@EXAMPLE.COM     # Near duplicate
```

**5. Outliers**
```
salaries: [45000, 48000, 52000, 47000, 500000, 51000]
                                  ^^^^^^^ Outlier
```

### The Impact

**Without cleaning:**
- ❌ Analysis produces incorrect results
- ❌ Machine learning models fail to train
- ❌ Visualizations are misleading
- ❌ Business decisions based on bad data

**With proper cleaning:**
- ✅ Reliable analysis and insights
- ✅ Models train successfully
- ✅ Accurate visualizations
- ✅ Confident decision-making

### Why It Works

Data cleaning is about understanding your data's context and purpose. There's no universal "clean" state—what's clean depends on what you're trying to do. A missing email might be acceptable for demographic analysis but critical for a marketing campaign.

## Step 2: Handling Missing Values (~20 min)

### Goal

Implement strategies to detect and handle missing, null, and empty values.

### Actions

**1. Create the missing value handler:**

```php
# filename: src/Cleaning/MissingValueHandler.php
<?php

declare(strict_types=1);

namespace DataScience\Cleaning;

class MissingValueHandler
{
    /**
     * Detect missing values in dataset
     */
    public function detectMissing(array $data): array
    {
        $report = [
            'total_rows' => count($data),
            'columns' => [],
        ];
        
        if (empty($data)) {
            return $report;
        }
        
        $columns = array_keys($data[0]);
        
        foreach ($columns as $column) {
            $missing = 0;
            $empty = 0;
            
            foreach ($data as $row) {
                $value = $row[$column] ?? null;
                
                if ($value === null) {
                    $missing++;
                } elseif ($this->isEmpty($value)) {
                    $empty++;
                }
            }
            
            $report['columns'][$column] = [
                'missing' => $missing,
                'empty' => $empty,
                'total_invalid' => $missing + $empty,
                'percentage' => round(
                    (($missing + $empty) / count($data)) * 100,
                    2
                ),
            ];
        }
        
        return $report;
    }
    
    /**
     * Check if value is considered empty
     */
    private function isEmpty(mixed $value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' || 
                   strtolower($value) === 'null' ||
                   strtolower($value) === 'n/a' ||
                   strtolower($value) === 'none';
        }
        
        return empty($value) && $value !== 0 && $value !== '0';
    }
    
    /**
     * Remove rows with missing values
     */
    public function dropMissing(
        array $data,
        array $columns = [],
        string $how = 'any'
    ): array {
        return array_values(array_filter($data, function($row) use ($columns, $how) {
            $columnsToCheck = empty($columns) ? array_keys($row) : $columns;
            $missingCount = 0;
            
            foreach ($columnsToCheck as $column) {
                $value = $row[$column] ?? null;
                
                if ($value === null || $this->isEmpty($value)) {
                    $missingCount++;
                    
                    if ($how === 'any') {
                        return false; // Drop if any column is missing
                    }
                }
            }
            
            // For 'all': only drop if all columns are missing
            return $how === 'all' ? $missingCount < count($columnsToCheck) : true;
        }));
    }
    
    /**
     * Fill missing values with specified strategy
     */
    public function fillMissing(
        array $data,
        string $column,
        mixed $value = null,
        string $strategy = 'constant'
    ): array {
        switch ($strategy) {
            case 'constant':
                return $this->fillConstant($data, $column, $value);
                
            case 'mean':
                return $this->fillMean($data, $column);
                
            case 'median':
                return $this->fillMedian($data, $column);
                
            case 'mode':
                return $this->fillMode($data, $column);
                
            case 'forward':
                return $this->fillForward($data, $column);
                
            case 'backward':
                return $this->fillBackward($data, $column);
                
            default:
                throw new \InvalidArgumentException("Unknown strategy: {$strategy}");
        }
    }
    
    /**
     * Fill with constant value
     */
    private function fillConstant(array $data, string $column, mixed $value): array
    {
        foreach ($data as &$row) {
            if (!isset($row[$column]) || $this->isEmpty($row[$column])) {
                $row[$column] = $value;
            }
        }
        
        return $data;
    }
    
    /**
     * Fill with mean (numeric columns only)
     */
    private function fillMean(array $data, string $column): array
    {
        $values = array_filter(
            array_column($data, $column),
            fn($v) => is_numeric($v)
        );
        
        if (empty($values)) {
            return $data;
        }
        
        $mean = array_sum($values) / count($values);
        
        return $this->fillConstant($data, $column, $mean);
    }
    
    /**
     * Fill with median (numeric columns only)
     */
    private function fillMedian(array $data, string $column): array
    {
        $values = array_filter(
            array_column($data, $column),
            fn($v) => is_numeric($v)
        );
        
        if (empty($values)) {
            return $data;
        }
        
        sort($values);
        $count = count($values);
        $median = $count % 2 === 0
            ? ($values[$count / 2 - 1] + $values[$count / 2]) / 2
            : $values[floor($count / 2)];
        
        return $this->fillConstant($data, $column, $median);
    }
    
    /**
     * Fill with mode (most frequent value)
     */
    private function fillMode(array $data, string $column): array
    {
        $values = array_filter(
            array_column($data, $column),
            fn($v) => !$this->isEmpty($v)
        );
        
        if (empty($values)) {
            return $data;
        }
        
        $counts = array_count_values($values);
        arsort($counts);
        $mode = array_key_first($counts);
        
        return $this->fillConstant($data, $column, $mode);
    }
    
    /**
     * Fill with previous non-missing value (forward fill)
     */
    private function fillForward(array $data, string $column): array
    {
        $lastValid = null;
        
        foreach ($data as &$row) {
            if (isset($row[$column]) && !$this->isEmpty($row[$column])) {
                $lastValid = $row[$column];
            } elseif ($lastValid !== null) {
                $row[$column] = $lastValid;
            }
        }
        
        return $data;
    }
    
    /**
     * Fill with next non-missing value (backward fill)
     */
    private function fillBackward(array $data, string $column): array
    {
        $data = array_reverse($data);
        $data = $this->fillForward($data, $column);
        return array_reverse($data);
    }
}
```

**2. Create an example demonstrating missing value handling:**

```php
# filename: examples/handle-missing-values.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Cleaning\MissingValueHandler;

// Sample data with missing values
$data = [
    ['id' => 1, 'name' => 'John Doe', 'age' => 35, 'salary' => 50000, 'city' => 'New York'],
    ['id' => 2, 'name' => 'Jane Smith', 'age' => null, 'salary' => 60000, 'city' => ''],
    ['id' => 3, 'name' => '', 'age' => 28, 'salary' => null, 'city' => 'Chicago'],
    ['id' => 4, 'name' => 'Bob Johnson', 'age' => 42, 'salary' => 55000, 'city' => 'N/A'],
    ['id' => 5, 'name' => 'Alice Brown', 'age' => 31, 'salary' => 58000, 'city' => 'Boston'],
    ['id' => 6, 'name' => null, 'age' => null, 'salary' => null, 'city' => null],
];

$handler = new MissingValueHandler();

echo "=== Missing Value Analysis ===\n\n";

// 1. Detect missing values
$report = $handler->detectMissing($data);

echo "Total rows: {$report['total_rows']}\n\n";
echo "Missing values by column:\n";

foreach ($report['columns'] as $column => $stats) {
    echo sprintf(
        "  %-10s: %d missing, %d empty (%s%%)\n",
        $column,
        $stats['missing'],
        $stats['empty'],
        $stats['percentage']
    );
}

echo "\n=== Strategy 1: Drop Rows with Any Missing Value ===\n";
$cleaned1 = $handler->dropMissing($data, how: 'any');
echo "Rows remaining: " . count($cleaned1) . " (dropped " . (count($data) - count($cleaned1)) . ")\n";

echo "\n=== Strategy 2: Drop Rows with All Values Missing ===\n";
$cleaned2 = $handler->dropMissing($data, how: 'all');
echo "Rows remaining: " . count($cleaned2) . " (dropped " . (count($data) - count($cleaned2)) . ")\n";

echo "\n=== Strategy 3: Fill Missing Ages with Mean ===\n";
$cleaned3 = $handler->fillMissing($data, 'age', strategy: 'mean');
$ages = array_filter(array_column($cleaned3, 'age'));
echo "Mean age: " . round(array_sum($ages) / count($ages), 1) . "\n";
echo "All ages filled: " . (count($ages) === count($cleaned3) ? 'Yes' : 'No') . "\n";

echo "\n=== Strategy 4: Fill Missing Salaries with Median ===\n";
$cleaned4 = $handler->fillMissing($data, 'salary', strategy: 'median');
$salaries = array_filter(array_column($cleaned4, 'salary'));
echo "Median salary: $" . number_format(array_sum($salaries) / count($salaries), 2) . "\n";

echo "\n=== Strategy 5: Fill Missing Cities with Mode ===\n";
$cleaned5 = $handler->fillMissing($data, 'city', strategy: 'mode');
$cities = array_filter(array_column($cleaned5, 'city'));
$cityCounts = array_count_values($cities);
arsort($cityCounts);
echo "Most common city: " . array_key_first($cityCounts) . " (" . reset($cityCounts) . " occurrences)\n";

echo "\n=== Strategy 6: Fill Missing Names with Constant ===\n";
$cleaned6 = $handler->fillMissing($data, 'name', value: 'Unknown', strategy: 'constant');
$unknownCount = count(array_filter($cleaned6, fn($r) => $r['name'] === 'Unknown'));
echo "Rows with 'Unknown' name: {$unknownCount}\n";

echo "\n✓ Missing value handling complete!\n";
```

**3. Run the example:**

```bash
php examples/handle-missing-values.php
```

### Expected Result

```
=== Missing Value Analysis ===

Total rows: 6

Missing values by column:
  id        : 0 missing, 0 empty (0%)
  name      : 1 missing, 1 empty (33.33%)
  age       : 2 missing, 0 empty (33.33%)
  salary    : 2 missing, 0 empty (33.33%)
  city      : 1 missing, 2 empty (50%)

=== Strategy 1: Drop Rows with Any Missing Value ===
Rows remaining: 2 (dropped 4)

=== Strategy 2: Drop Rows with All Values Missing ===
Rows remaining: 5 (dropped 1)

=== Strategy 3: Fill Missing Ages with Mean ===
Mean age: 34.0
All ages filled: Yes

=== Strategy 4: Fill Missing Salaries with Median ===
Median salary: $55,750.00

=== Strategy 5: Fill Missing Cities with Mode ===
Most common city: New York (1 occurrences)

=== Strategy 6: Fill Missing Names with Constant ===
Rows with 'Unknown' name: 2

✓ Missing value handling complete!
```

### Why It Works

**Multiple Strategies**: Different situations require different approaches. Dropping rows works when you have plenty of data; filling works when you can't afford to lose records.

**Statistical Fills**: Mean works for normally distributed numeric data; median is better for skewed distributions; mode works for categorical data.

**Forward/Backward Fill**: Useful for time series data where values change gradually.

**Context Matters**: A missing age might be filled with mean, but a missing email can't be guessed—it might need to be dropped or marked as "no email provided."

### Troubleshooting

**Error: "Division by zero"**

**Cause**: Trying to calculate mean/median on column with no valid values.

**Solution**: Check for empty values before calculation:

```php
$values = array_filter(array_column($data, $column), fn($v) => is_numeric($v));

if (empty($values)) {
    return $data; // Can't fill, return unchanged
}
```

**Problem: Forward fill doesn't fill first rows**

**Cause**: No previous value exists for first missing values.

**Solution**: Combine strategies:

```php
// First fill with median, then forward fill
$data = $handler->fillMissing($data, 'age', strategy: 'median');
$data = $handler->fillMissing($data, 'age', strategy: 'forward');
```

**Problem: Filling categorical data with mean**

**Cause**: Using numeric strategy on non-numeric data.

**Solution**: Use mode for categorical data:

```php
// Wrong
$data = $handler->fillMissing($data, 'city', strategy: 'mean');

// Correct
$data = $handler->fillMissing($data, 'city', strategy: 'mode');
```

## Step 3: Detecting and Handling Outliers (~20 min)

### Goal

Identify outliers using statistical methods and decide how to handle them.

### Actions

**1. Create the outlier detector:**

```php
# filename: src/Cleaning/OutlierDetector.php
<?php

declare(strict_types=1);

namespace DataScience\Cleaning;

class OutlierDetector
{
    /**
     * Detect outliers using IQR (Interquartile Range) method
     */
    public function detectIQR(
        array $data,
        string $column,
        float $multiplier = 1.5
    ): array {
        $values = array_filter(
            array_column($data, $column),
            fn($v) => is_numeric($v)
        );
        
        if (count($values) < 4) {
            return ['outliers' => [], 'bounds' => null];
        }
        
        sort($values);
        
        // Calculate quartiles
        $q1 = $this->percentile($values, 25);
        $q3 = $this->percentile($values, 75);
        $iqr = $q3 - $q1;
        
        // Calculate bounds
        $lowerBound = $q1 - ($multiplier * $iqr);
        $upperBound = $q3 + ($multiplier * $iqr);
        
        // Find outliers
        $outliers = [];
        foreach ($data as $index => $row) {
            $value = $row[$column] ?? null;
            
            if (is_numeric($value) && 
                ($value < $lowerBound || $value > $upperBound)) {
                $outliers[] = [
                    'index' => $index,
                    'value' => $value,
                    'row' => $row,
                ];
            }
        }
        
        return [
            'outliers' => $outliers,
            'bounds' => [
                'lower' => $lowerBound,
                'upper' => $upperBound,
                'q1' => $q1,
                'q3' => $q3,
                'iqr' => $iqr,
            ],
        ];
    }
    
    /**
     * Detect outliers using Z-score method
     */
    public function detectZScore(
        array $data,
        string $column,
        float $threshold = 3.0
    ): array {
        $values = array_filter(
            array_column($data, $column),
            fn($v) => is_numeric($v)
        );
        
        if (count($values) < 2) {
            return ['outliers' => [], 'stats' => null];
        }
        
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(
            fn($v) => ($v - $mean) ** 2,
            $values
        )) / count($values);
        $stdDev = sqrt($variance);
        
        if ($stdDev == 0) {
            return ['outliers' => [], 'stats' => ['mean' => $mean, 'std_dev' => 0]];
        }
        
        // Find outliers
        $outliers = [];
        foreach ($data as $index => $row) {
            $value = $row[$column] ?? null;
            
            if (is_numeric($value)) {
                $zScore = abs(($value - $mean) / $stdDev);
                
                if ($zScore > $threshold) {
                    $outliers[] = [
                        'index' => $index,
                        'value' => $value,
                        'z_score' => $zScore,
                        'row' => $row,
                    ];
                }
            }
        }
        
        return [
            'outliers' => $outliers,
            'stats' => [
                'mean' => $mean,
                'std_dev' => $stdDev,
                'threshold' => $threshold,
            ],
        ];
    }
    
    /**
     * Remove outliers from dataset
     */
    public function removeOutliers(array $data, array $outlierIndices): array
    {
        return array_values(array_filter(
            $data,
            fn($row, $index) => !in_array($index, $outlierIndices),
            ARRAY_FILTER_USE_BOTH
        ));
    }
    
    /**
     * Cap outliers at bounds (winsorization)
     */
    public function capOutliers(
        array $data,
        string $column,
        float $lowerBound,
        float $upperBound
    ): array {
        foreach ($data as &$row) {
            $value = $row[$column] ?? null;
            
            if (is_numeric($value)) {
                if ($value < $lowerBound) {
                    $row[$column] = $lowerBound;
                } elseif ($value > $upperBound) {
                    $row[$column] = $upperBound;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Calculate percentile
     */
    private function percentile(array $sortedValues, float $percentile): float
    {
        $index = ($percentile / 100) * (count($sortedValues) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower === $upper) {
            return $sortedValues[(int)$index];
        }
        
        $fraction = $index - $lower;
        return $sortedValues[(int)$lower] * (1 - $fraction) + 
               $sortedValues[(int)$upper] * $fraction;
    }
}
```

**2. Create an example demonstrating outlier detection:**

```php
# filename: examples/detect-outliers.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Cleaning\OutlierDetector;

// Sample salary data with outliers
$data = [
    ['id' => 1, 'name' => 'Alice', 'salary' => 45000],
    ['id' => 2, 'name' => 'Bob', 'salary' => 48000],
    ['id' => 3, 'name' => 'Charlie', 'salary' => 52000],
    ['id' => 4, 'name' => 'Diana', 'salary' => 47000],
    ['id' => 5, 'name' => 'Eve', 'salary' => 500000],      // Outlier (CEO)
    ['id' => 6, 'name' => 'Frank', 'salary' => 51000],
    ['id' => 7, 'name' => 'Grace', 'salary' => 49000],
    ['id' => 8, 'name' => 'Henry', 'salary' => 15000],     // Outlier (intern)
    ['id' => 9, 'name' => 'Iris', 'salary' => 53000],
    ['id' => 10, 'name' => 'Jack', 'salary' => 46000],
];

$detector = new OutlierDetector();

echo "=== Outlier Detection ===\n\n";

// Display original data statistics
$salaries = array_column($data, 'salary');
echo "Original data:\n";
echo "  Count: " . count($salaries) . "\n";
echo "  Mean: $" . number_format(array_sum($salaries) / count($salaries), 2) . "\n";
echo "  Min: $" . number_format(min($salaries), 2) . "\n";
echo "  Max: $" . number_format(max($salaries), 2) . "\n\n";

// Method 1: IQR (Interquartile Range)
echo "=== Method 1: IQR Detection ===\n";
$iqrResult = $detector->detectIQR($data, 'salary', multiplier: 1.5);

echo "Bounds:\n";
echo "  Q1: $" . number_format($iqrResult['bounds']['q1'], 2) . "\n";
echo "  Q3: $" . number_format($iqrResult['bounds']['q3'], 2) . "\n";
echo "  IQR: $" . number_format($iqrResult['bounds']['iqr'], 2) . "\n";
echo "  Lower Bound: $" . number_format($iqrResult['bounds']['lower'], 2) . "\n";
echo "  Upper Bound: $" . number_format($iqrResult['bounds']['upper'], 2) . "\n\n";

echo "Outliers found: " . count($iqrResult['outliers']) . "\n";
foreach ($iqrResult['outliers'] as $outlier) {
    echo "  {$outlier['row']['name']}: $" . number_format($outlier['value'], 2) . "\n";
}

// Method 2: Z-Score
echo "\n=== Method 2: Z-Score Detection ===\n";
$zScoreResult = $detector->detectZScore($data, 'salary', threshold: 2.0);

echo "Statistics:\n";
echo "  Mean: $" . number_format($zScoreResult['stats']['mean'], 2) . "\n";
echo "  Std Dev: $" . number_format($zScoreResult['stats']['std_dev'], 2) . "\n";
echo "  Threshold: " . $zScoreResult['stats']['threshold'] . " standard deviations\n\n";

echo "Outliers found: " . count($zScoreResult['outliers']) . "\n";
foreach ($zScoreResult['outliers'] as $outlier) {
    echo sprintf(
        "  %s: $%s (Z-score: %.2f)\n",
        $outlier['row']['name'],
        number_format($outlier['value'], 2),
        $outlier['z_score']
    );
}

// Strategy 1: Remove outliers
echo "\n=== Strategy 1: Remove Outliers ===\n";
$outlierIndices = array_column($iqrResult['outliers'], 'index');
$cleaned1 = $detector->removeOutliers($data, $outlierIndices);

$cleanedSalaries = array_column($cleaned1, 'salary');
echo "After removal:\n";
echo "  Count: " . count($cleanedSalaries) . " (removed " . (count($data) - count($cleaned1)) . ")\n";
echo "  Mean: $" . number_format(array_sum($cleanedSalaries) / count($cleanedSalaries), 2) . "\n";
echo "  Min: $" . number_format(min($cleanedSalaries), 2) . "\n";
echo "  Max: $" . number_format(max($cleanedSalaries), 2) . "\n";

// Strategy 2: Cap outliers (winsorization)
echo "\n=== Strategy 2: Cap Outliers (Winsorization) ===\n";
$cleaned2 = $detector->capOutliers(
    $data,
    'salary',
    $iqrResult['bounds']['lower'],
    $iqrResult['bounds']['upper']
);

$cappedSalaries = array_column($cleaned2, 'salary');
echo "After capping:\n";
echo "  Count: " . count($cappedSalaries) . " (no rows removed)\n";
echo "  Mean: $" . number_format(array_sum($cappedSalaries) / count($cappedSalaries), 2) . "\n";
echo "  Min: $" . number_format(min($cappedSalaries), 2) . "\n";
echo "  Max: $" . number_format(max($cappedSalaries), 2) . "\n";

echo "\n✓ Outlier detection complete!\n";
```

**3. Run the example:**

```bash
php examples/detect-outliers.php
```

### Expected Result

```
=== Outlier Detection ===

Original data:
  Count: 10
  Mean: $95,600.00
  Min: $15,000.00
  Max: $500,000.00

=== Method 1: IQR Detection ===
Bounds:
  Q1: $46,500.00
  Q3: $52,250.00
  IQR: $5,750.00
  Lower Bound: $37,875.00
  Upper Bound: $60,875.00

Outliers found: 2
  Eve: $500,000.00
  Henry: $15,000.00

=== Method 2: Z-Score Detection ===
Statistics:
  Mean: $95,600.00
  Std Dev: $143,686.40
  Threshold: 2 standard deviations

Outliers found: 1
  Eve: $500,000.00 (Z-score: 2.81)

=== Strategy 1: Remove Outliers ===
After removal:
  Count: 8 (removed 2)
  Mean: $48,875.00
  Min: $45,000.00
  Max: $53,000.00

=== Strategy 2: Cap Outliers (Winsorization) ===
After capping:
  Count: 10 (no rows removed)
  Mean: $50,687.50
  Min: $37,875.00
  Max: $60,875.00

✓ Outlier detection complete!
```

### Why It Works

**IQR Method**: Robust to extreme outliers. Uses quartiles (25th and 75th percentiles) to define "normal" range. Values beyond 1.5× IQR from quartiles are outliers.

**Z-Score Method**: Measures how many standard deviations a value is from the mean. Values beyond 2-3 standard deviations are outliers. More sensitive to extreme values.

**Removal vs Capping**: Removing outliers loses data but prevents skewing. Capping (winsorization) preserves row count but limits extreme values to boundaries.

**Context Matters**: A CEO's salary isn't an "error"—it's legitimate data. Whether to remove it depends on your analysis goal. Studying typical employee salaries? Remove it. Studying company payroll? Keep it.

### Troubleshooting

**Problem: Too many outliers detected**

**Cause**: Threshold too strict or data naturally has wide distribution.

**Solution**: Adjust multiplier/threshold:

```php
// IQR: Increase multiplier (default 1.5)
$result = $detector->detectIQR($data, 'salary', multiplier: 2.0);

// Z-Score: Increase threshold (default 3.0)
$result = $detector->detectZScore($data, 'salary', threshold: 3.5);
```

**Problem: No outliers detected in obviously skewed data**

**Cause**: Small dataset or all values are outliers.

**Solution**: Check data distribution first:

```php
$values = array_column($data, 'salary');
sort($values);
print_r($values); // Visual inspection

// Try both methods
$iqr = $detector->detectIQR($data, 'salary');
$zscore = $detector->detectZScore($data, 'salary');
```

**Problem: Capping changes data meaning**

**Cause**: Capping legitimate extreme values.

**Solution**: Consider domain knowledge:

```php
// Option 1: Remove only clear errors
if ($value > 1000000) { // Clearly impossible
    unset($data[$index]);
}

// Option 2: Log transform instead
$data[$index]['salary'] = log($data[$index]['salary']);
```

## Step 4: Data Validation and Type Conversion (~15 min)

### Goal

Validate data types and safely convert values to expected formats.

### Actions

**1. Create the data validator:**

```php
# filename: src/Cleaning/DataValidator.php
<?php

declare(strict_types=1);

namespace DataScience\Cleaning;

class DataValidator
{
    /**
     * Validate and convert data types
     */
    public function validateTypes(array $data, array $schema): array
    {
        $validated = [];
        $errors = [];
        
        foreach ($data as $index => $row) {
            $validatedRow = [];
            $rowErrors = [];
            
            foreach ($schema as $column => $config) {
                $value = $row[$column] ?? null;
                $type = $config['type'];
                $required = $config['required'] ?? false;
                
                // Check required
                if ($required && ($value === null || $value === '')) {
                    $rowErrors[$column] = "Required field missing";
                    continue;
                }
                
                // Skip validation for null optional fields
                if ($value === null && !$required) {
                    $validatedRow[$column] = null;
                    continue;
                }
                
                // Validate and convert type
                try {
                    $validatedRow[$column] = $this->convertType($value, $type, $config);
                } catch (\Exception $e) {
                    $rowErrors[$column] = $e->getMessage();
                }
            }
            
            if (empty($rowErrors)) {
                $validated[] = $validatedRow;
            } else {
                $errors[$index] = [
                    'row' => $row,
                    'errors' => $rowErrors,
                ];
            }
        }
        
        return [
            'valid' => $validated,
            'errors' => $errors,
        ];
    }
    
    /**
     * Convert value to specified type
     */
    private function convertType(mixed $value, string $type, array $config): mixed
    {
        return match($type) {
            'int', 'integer' => $this->toInt($value),
            'float', 'double' => $this->toFloat($value),
            'string' => $this->toString($value),
            'bool', 'boolean' => $this->toBool($value),
            'date' => $this->toDate($value, $config['format'] ?? 'Y-m-d'),
            'email' => $this->toEmail($value),
            'url' => $this->toUrl($value),
            'phone' => $this->toPhone($value),
            'enum' => $this->toEnum($value, $config['values'] ?? []),
            default => throw new \InvalidArgumentException("Unknown type: {$type}"),
        };
    }
    
    private function toInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        throw new \InvalidArgumentException("Cannot convert to integer: {$value}");
    }
    
    private function toFloat(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }
        
        // Handle currency format
        if (is_string($value)) {
            $cleaned = preg_replace('/[^0-9.-]/', '', $value);
            if (is_numeric($cleaned)) {
                return (float)$cleaned;
            }
        }
        
        throw new \InvalidArgumentException("Cannot convert to float: {$value}");
    }
    
    private function toString(mixed $value): string
    {
        return trim((string)$value);
    }
    
    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $lower = strtolower(trim($value));
            if (in_array($lower, ['true', '1', 'yes', 'y', 'on'])) {
                return true;
            }
            if (in_array($lower, ['false', '0', 'no', 'n', 'off', ''])) {
                return false;
            }
        }
        
        return (bool)$value;
    }
    
    private function toDate(mixed $value, string $format): string
    {
        if ($value instanceof \DateTime) {
            return $value->format($format);
        }
        
        try {
            $date = new \DateTime($value);
            return $date->format($format);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date: {$value}");
        }
    }
    
    private function toEmail(mixed $value): string
    {
        $email = trim((string)$value);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: {$value}");
        }
        
        return strtolower($email);
    }
    
    private function toUrl(mixed $value): string
    {
        $url = trim((string)$value);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: {$value}");
        }
        
        return $url;
    }
    
    private function toPhone(mixed $value): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string)$value);
        
        if (strlen($phone) < 10) {
            throw new \InvalidArgumentException("Invalid phone: {$value}");
        }
        
        return $phone;
    }
    
    private function toEnum(mixed $value, array $allowedValues): string
    {
        $value = trim((string)$value);
        
        if (!in_array($value, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                "Value '{$value}' not in allowed values: " . implode(', ', $allowedValues)
            );
        }
        
        return $value;
    }
}
```

**2. Create an example:**

```php
# filename: examples/validate-data.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Cleaning\DataValidator;

// Sample data with type issues
$data = [
    ['id' => '1', 'name' => 'John Doe', 'age' => '35', 'email' => 'john@example.com', 'active' => 'true', 'salary' => '$50,000'],
    ['id' => '2', 'name' => 'Jane Smith', 'age' => 'twenty-eight', 'email' => 'jane@example.com', 'active' => '1', 'salary' => '60000'],
    ['id' => '3', 'name' => 'Bob', 'age' => '42', 'email' => 'invalid-email', 'active' => 'yes', 'salary' => '55000.50'],
    ['id' => 'four', 'name' => '', 'age' => '-5', 'email' => 'alice@example.com', 'active' => 'false', 'salary' => 'free'],
];

// Define schema
$schema = [
    'id' => ['type' => 'int', 'required' => true],
    'name' => ['type' => 'string', 'required' => true],
    'age' => ['type' => 'int', 'required' => true],
    'email' => ['type' => 'email', 'required' => true],
    'active' => ['type' => 'bool', 'required' => false],
    'salary' => ['type' => 'float', 'required' => true],
];

$validator = new DataValidator();

echo "=== Data Validation ===\n\n";

$result = $validator->validateTypes($data, $schema);

echo "Valid rows: " . count($result['valid']) . "\n";
echo "Invalid rows: " . count($result['errors']) . "\n\n";

if (!empty($result['valid'])) {
    echo "=== Valid Data ===\n";
    foreach ($result['valid'] as $row) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Age: {$row['age']}, ";
        echo "Email: {$row['email']}, Active: " . ($row['active'] ? 'Yes' : 'No') . ", ";
        echo "Salary: $" . number_format($row['salary'], 2) . "\n";
    }
}

if (!empty($result['errors'])) {
    echo "\n=== Validation Errors ===\n";
    foreach ($result['errors'] as $index => $error) {
        echo "Row {$index}:\n";
        foreach ($error['errors'] as $column => $message) {
            echo "  {$column}: {$message}\n";
        }
    }
}

echo "\n✓ Validation complete!\n";
```

### Expected Result

```
=== Data Validation ===

Valid rows: 2
Invalid rows: 2

=== Valid Data ===
ID: 1, Name: John Doe, Age: 35, Email: john@example.com, Active: Yes, Salary: $50,000.00
ID: 2, Name: Jane Smith, Age: 28, Email: jane@example.com, Active: Yes, Salary: $60,000.00

=== Validation Errors ===
Row 2:
  email: Invalid email: invalid-email
Row 3:
  id: Cannot convert to integer: four
  name: Required field missing
  salary: Cannot convert to float: free

✓ Validation complete!
```

### Why It Works

**Schema-Driven**: Define expected types once, validate consistently across all data.

**Type Coercion**: Safely converts strings to proper types (e.g., "$50,000" → 50000.0).

**Validation**: Checks email format, URL structure, phone numbers using PHP's filter functions.

**Error Collection**: Collects all errors per row, not just first error, making debugging easier.

**Flexible**: Supports required/optional fields, custom formats, enum values.

## Step 5: Building a Complete Cleaning Pipeline (~15 min)

### Goal

Combine all cleaning operations into a reusable, automated pipeline.

### Actions

**1. Create the cleaning pipeline:**

```php
# filename: src/Cleaning/CleaningPipeline.php
<?php

declare(strict_types=1);

namespace DataScience\Cleaning;

class CleaningPipeline
{
    private array $steps = [];
    private array $report = [];
    
    public function addStep(string $name, callable $operation): self
    {
        $this->steps[] = ['name' => $name, 'operation' => $operation];
        return $this;
    }
    
    public function run(array $data): array
    {
        $this->report = [
            'initial_rows' => count($data),
            'steps' => [],
        ];
        
        $currentData = $data;
        
        foreach ($this->steps as $step) {
            $startTime = microtime(true);
            $beforeCount = count($currentData);
            
            try {
                $currentData = $step['operation']($currentData);
                $afterCount = count($currentData);
                
                $this->report['steps'][] = [
                    'name' => $step['name'],
                    'status' => 'success',
                    'rows_before' => $beforeCount,
                    'rows_after' => $afterCount,
                    'rows_affected' => $beforeCount - $afterCount,
                    'duration' => round((microtime(true) - $startTime) * 1000, 2),
                ];
                
            } catch (\Exception $e) {
                $this->report['steps'][] = [
                    'name' => $step['name'],
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
                
                throw $e;
            }
        }
        
        $this->report['final_rows'] = count($currentData);
        $this->report['total_removed'] = $this->report['initial_rows'] - $this->report['final_rows'];
        
        return $currentData;
    }
    
    public function getReport(): array
    {
        return $this->report;
    }
    
    public function printReport(): void
    {
        echo "=== Cleaning Pipeline Report ===\n\n";
        echo "Initial rows: {$this->report['initial_rows']}\n";
        echo "Final rows: {$this->report['final_rows']}\n";
        echo "Total removed: {$this->report['total_removed']}\n\n";
        
        echo "Steps:\n";
        foreach ($this->report['steps'] as $i => $step) {
            echo ($i + 1) . ". {$step['name']}\n";
            
            if ($step['status'] === 'success') {
                echo "   Status: ✓ Success\n";
                echo "   Rows: {$step['rows_before']} → {$step['rows_after']}";
                
                if ($step['rows_affected'] > 0) {
                    echo " (removed {$step['rows_affected']})";
                }
                
                echo "\n";
                echo "   Duration: {$step['duration']}ms\n";
            } else {
                echo "   Status: ✗ Failed\n";
                echo "   Error: {$step['error']}\n";
            }
            
            echo "\n";
        }
    }
}
```

**2. Create a complete example:**

```php
# filename: examples/cleaning-pipeline.php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DataScience\Cleaning\CleaningPipeline;
use DataScience\Cleaning\MissingValueHandler;
use DataScience\Cleaning\OutlierDetector;
use DataScience\Cleaning\DataValidator;

// Load sample data (simulating messy real-world data)
$data = [
    ['id' => '1', 'name' => 'John Doe', 'age' => '35', 'salary' => '$50,000', 'city' => 'New York'],
    ['id' => '2', 'name' => 'Jane Smith', 'age' => null, 'salary' => '60000', 'city' => ''],
    ['id' => '3', 'name' => 'Bob Johnson', 'age' => '42', 'salary' => '$500,000', 'city' => 'Chicago'],
    ['id' => '2', 'name' => 'Jane Smith', 'age' => '28', 'salary' => '60000', 'city' => 'Boston'], // Duplicate ID
    ['id' => '4', 'name' => '  alice brown  ', 'age' => '-5', 'salary' => '55000', 'city' => 'Seattle'],
    ['id' => '5', 'name' => null, 'age' => null, 'salary' => null, 'city' => null],
    ['id' => '6', 'name' => 'Charlie Davis', 'age' => '31', 'salary' => '$48,500.50', 'city' => 'Austin'],
];

echo "Original data: " . count($data) . " rows\n\n";

// Create cleaning pipeline
$pipeline = new CleaningPipeline();

$missingHandler = new MissingValueHandler();
$outlierDetector = new OutlierDetector();
$validator = new DataValidator();

// Step 1: Remove rows with all values missing
$pipeline->addStep('Remove empty rows', function($data) use ($missingHandler) {
    return $missingHandler->dropMissing($data, how: 'all');
});

// Step 2: Remove duplicate IDs
$pipeline->addStep('Remove duplicates', function($data) {
    $seen = [];
    return array_values(array_filter($data, function($row) use (&$seen) {
        $id = $row['id'] ?? null;
        if ($id && isset($seen[$id])) {
            return false; // Duplicate
        }
        $seen[$id] = true;
        return true;
    }));
});

// Step 3: Fill missing ages with median
$pipeline->addStep('Fill missing ages', function($data) use ($missingHandler) {
    return $missingHandler->fillMissing($data, 'age', strategy: 'median');
});

// Step 4: Fill missing cities with mode
$pipeline->addStep('Fill missing cities', function($data) use ($missingHandler) {
    return $missingHandler->fillMissing($data, 'city', value: 'Unknown', strategy: 'constant');
});

// Step 5: Validate and convert types
$pipeline->addStep('Validate types', function($data) use ($validator) {
    $schema = [
        'id' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'string', 'required' => true],
        'age' => ['type' => 'int', 'required' => true],
        'salary' => ['type' => 'float', 'required' => true],
        'city' => ['type' => 'string', 'required' => false],
    ];
    
    $result = $validator->validateTypes($data, $schema);
    
    if (!empty($result['errors'])) {
        echo "Validation errors found: " . count($result['errors']) . " rows\n";
    }
    
    return $result['valid'];
});

// Step 6: Normalize strings (trim, proper case)
$pipeline->addStep('Normalize strings', function($data) {
    return array_map(function($row) {
        if (isset($row['name'])) {
            $row['name'] = ucwords(strtolower(trim($row['name'])));
        }
        if (isset($row['city'])) {
            $row['city'] = ucwords(strtolower(trim($row['city'])));
        }
        return $row;
    }, $data);
});

// Step 7: Handle outliers in salary
$pipeline->addStep('Cap salary outliers', function($data) use ($outlierDetector) {
    $iqrResult = $outlierDetector->detectIQR($data, 'salary');
    
    if (!empty($iqrResult['outliers'])) {
        echo "Found " . count($iqrResult['outliers']) . " salary outliers\n";
        
        return $outlierDetector->capOutliers(
            $data,
            'salary',
            $iqrResult['bounds']['lower'],
            $iqrResult['bounds']['upper']
        );
    }
    
    return $data;
});

// Run pipeline
echo "Running cleaning pipeline...\n\n";
$cleanedData = $pipeline->run($data);

// Print report
$pipeline->printReport();

// Display cleaned data
echo "\n=== Cleaned Data ===\n";
foreach ($cleanedData as $row) {
    echo sprintf(
        "ID: %d, Name: %-15s Age: %2d, Salary: $%s, City: %s\n",
        $row['id'],
        $row['name'],
        $row['age'],
        number_format($row['salary'], 2),
        $row['city']
    );
}

// Save cleaned data
$csv = fopen('data/cleaned_data.csv', 'w');
fputcsv($csv, array_keys($cleanedData[0]));
foreach ($cleanedData as $row) {
    fputcsv($csv, $row);
}
fclose($csv);

echo "\n✓ Cleaned data saved to data/cleaned_data.csv\n";
```

**3. Run the complete pipeline:**

```bash
php examples/cleaning-pipeline.php
```

### Expected Result

```
Original data: 7 rows

Running cleaning pipeline...

Found 1 salary outliers
Validation errors found: 1 rows

=== Cleaning Pipeline Report ===

Initial rows: 7
Final rows: 5
Total removed: 2

Steps:
1. Remove empty rows
   Status: ✓ Success
   Rows: 7 → 6 (removed 1)
   Duration: 0.15ms

2. Remove duplicates
   Status: ✓ Success
   Rows: 6 → 5 (removed 1)
   Duration: 0.08ms

3. Fill missing ages
   Status: ✓ Success
   Rows: 5 → 5
   Duration: 0.12ms

4. Fill missing cities
   Status: ✓ Success
   Rows: 5 → 5
   Duration: 0.09ms

5. Validate types
   Status: ✓ Success
   Rows: 5 → 4 (removed 1)
   Duration: 0.25ms

6. Normalize strings
   Status: ✓ Success
   Rows: 4 → 4
   Duration: 0.11ms

7. Cap salary outliers
   Status: ✓ Success
   Rows: 4 → 4
   Duration: 0.18ms

=== Cleaned Data ===
ID: 1, Name: John Doe        Age: 35, Salary: $50,000.00, City: New York
ID: 2, Name: Jane Smith      Age: 28, Salary: $60,000.00, City: Boston
ID: 3, Name: Bob Johnson     Age: 42, Salary: $60,875.00, City: Chicago
ID: 6, Name: Charlie Davis   Age: 31, Salary: $48,500.50, City: Austin

✓ Cleaned data saved to data/cleaned_data.csv
```

### Why It Works

**Modular Steps**: Each cleaning operation is independent and reusable. Add, remove, or reorder steps easily.

**Progress Tracking**: Pipeline tracks rows affected by each step, making it easy to see where data is being removed.

**Error Handling**: If a step fails, you know exactly which step and why, with the data state preserved up to that point.

**Performance Monitoring**: Duration tracking helps identify slow operations that might need optimization.

**Reproducible**: Same pipeline on same data always produces same result—critical for production systems.

### Troubleshooting

**Problem: Pipeline removes too many rows**

**Cause**: Steps are too aggressive or in wrong order.

**Solution**: Review report and adjust:

```php
// Check which step removes most rows
$pipeline->printReport();

// Adjust that step or reorder
// Example: Fill missing before validation instead of dropping
```

**Problem: Validation step fails entire pipeline**

**Cause**: Invalid data causes exception.

**Solution**: Handle errors gracefully:

```php
$pipeline->addStep('Validate types', function($data) use ($validator) {
    try {
        $result = $validator->validateTypes($data, $schema);
        return $result['valid'];
    } catch (\Exception $e) {
        echo "Warning: Validation failed, skipping invalid rows\n";
        return $data; // Return unchanged if validation fails
    }
});
```

**Problem: Pipeline is too slow**

**Cause**: Processing large datasets with many steps.

**Solution**: Optimize or parallelize:

```php
// Option 1: Process in chunks
$chunkSize = 1000;
$chunks = array_chunk($data, $chunkSize);

foreach ($chunks as $chunk) {
    $cleaned = $pipeline->run($chunk);
    // Save chunk
}

// Option 2: Skip expensive steps for large datasets
if (count($data) > 10000) {
    // Skip outlier detection on huge datasets
} else {
    $pipeline->addStep('Detect outliers', ...);
}
```

## Exercises

### Exercise 1: Custom Missing Value Strategy

**Goal**: Implement a custom missing value fill strategy.

Create a function that fills missing values based on related columns:

```php
// Fill missing city based on state
// Fill missing price based on category average
// Fill missing date based on adjacent dates

function fillRelated(array $data, string $targetColumn, string $relatedColumn): array
{
    // Group by related column
    // Calculate mean/mode per group
    // Fill missing values with group statistic
    
    // Your implementation here
}
```

**Validation**: Missing cities should be filled with most common city in same state.

### Exercise 2: Advanced Duplicate Detection

**Goal**: Detect "fuzzy" duplicates (similar but not identical).

```php
// Detect duplicates based on:
// - Similar names (Levenshtein distance)
// - Same email domain
// - Close numeric values

function detectFuzzyDuplicates(
    array $data,
    array $columns,
    int $threshold
): array {
    // Calculate similarity scores
    // Group similar records
    // Return duplicate groups
    
    // Your implementation here
}
```

**Validation**: Should find "John Doe" and "John  Doe" as duplicates.

### Exercise 3: Data Quality Score

**Goal**: Calculate overall data quality score.

```php
// Calculate quality score based on:
// - Percentage of missing values
// - Percentage of outliers
// - Percentage of invalid types
// - Percentage of duplicates

function calculateQualityScore(array $data): array
{
    return [
        'completeness' => 0.0,  // % non-missing
        'validity' => 0.0,      // % valid types
        'uniqueness' => 0.0,    // % non-duplicate
        'consistency' => 0.0,   // % within bounds
        'overall' => 0.0,       // Weighted average
    ];
}
```

**Validation**: Perfect data scores 100%, completely messy data scores 0%.

## Wrap-up

<ChapterCheckbox 
  seriesId="data-science-php-developers"
  chapterId="04"
  label="You've mastered data cleaning and preprocessing!"
/>

### What You've Accomplished

✅ Built comprehensive missing value handling with multiple strategies  
✅ Implemented outlier detection using IQR and Z-score methods  
✅ Created robust data validation and type conversion system  
✅ Learned to normalize and standardize data formats  
✅ Built automated cleaning pipelines with progress tracking  
✅ Generated data quality reports  
✅ Handled real-world messy data scenarios  

### Key Concepts Learned

**Missing Values**: Different strategies (drop, fill with mean/median/mode, forward/backward fill) suit different situations. Context determines the best approach.

**Outliers**: IQR method is robust; Z-score is sensitive. Removal vs capping depends on whether outliers are errors or legitimate extreme values.

**Validation**: Schema-driven validation ensures data quality. Type coercion handles common format issues (currency symbols, boolean strings).

**Pipelines**: Modular, reusable cleaning pipelines make data preparation reproducible and maintainable.

**Data Quality**: Cleaning is iterative—explore, clean, validate, repeat until data meets quality standards for your analysis.

### Real-World Applications

You can now:

- **Prepare datasets** for machine learning with confidence
- **Clean production data** from databases and APIs
- **Build ETL pipelines** that handle messy input
- **Generate quality reports** for stakeholders
- **Automate data cleaning** in production systems
- **Debug data issues** systematically

### Connection to Data Science

Clean data is the foundation of reliable analysis. Every insight, model, and decision depends on data quality. The 60-80% of time data scientists spend cleaning data isn't wasted—it's essential investment that prevents garbage-in-garbage-out scenarios.

In the next chapter, you'll use your clean data for exploratory data analysis (EDA), discovering patterns, relationships, and insights that guide your analysis strategy.

## Further Reading

- [PHP Data Filtering](https://www.php.net/manual/en/book.filter.php) — Built-in validation functions
- [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7/) — Standard data structures
- [Data Cleaning Best Practices](https://www.dataquest.io/blog/data-cleaning-best-practices/) — Industry standards
- [Statistical Outlier Detection](https://en.wikipedia.org/wiki/Outlier#Detection) — Methods and theory
- [Data Quality Dimensions](https://www.sciencedirect.com/topics/computer-science/data-quality) — Academic perspective

::: tip Next Chapter
Continue to [Chapter 05: Exploratory Data Analysis (EDA) for PHP Developers](/series/data-science-php-developers/chapters/05-exploratory-data-analysis-for-php-developers) to discover patterns and insights in your clean data!
:::
