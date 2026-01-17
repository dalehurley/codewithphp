# Chapter 5: Exploratory Data Analysis (EDA) for PHP Developers

This directory contains all code examples from Chapter 5 of the **Data Science for PHP Developers** series.

## 📚 What's Included

### Core Classes

- **`src/Analysis/StatisticalAnalyzer.php`**  
  Calculate comprehensive descriptive statistics (mean, median, mode, standard deviation, skewness, kurtosis, quartiles, IQR, frequency distributions)

- **`src/Analysis/CorrelationAnalyzer.php`**  
  Find relationships between variables using Pearson correlation, correlation matrices, and covariance

- **`src/Analysis/DataProfiler.php`**  
  Generate automated dataset profiles with overview, column analysis, quality assessment, and insights

### Example Scripts

- **`examples/descriptive-statistics.php`**  
  Analyze a sales dataset with central tendency, spread, shape, five-number summary, and frequency distributions

- **`examples/correlation-analysis.php`**  
  Find correlations in advertising data to determine which channels drive sales

- **`examples/complete-eda.php`**  
  Run a complete exploratory data analysis pipeline on customer data

## 🚀 Getting Started

### Prerequisites

- PHP 8.4 or higher
- Composer

### Installation

```bash
# Navigate to this directory
cd code/data-science-php-developers/chapter-05

# Install dependencies
composer install
```

### Running Examples

**1. Descriptive Statistics Analysis**

```bash
php examples/descriptive-statistics.php
```

**Expected Output:**
- Dataset overview (rows, columns, categories)
- Price analysis (mean, median, mode, std dev, IQR)
- Five-number summary
- Frequency distribution with visual bars
- Category distribution
- Complete dataset statistics for all numeric columns

**2. Correlation Analysis**

```bash
php examples/correlation-analysis.php
```

**Expected Output:**
- Individual correlations with sales
- Complete correlation matrix
- Strongest correlations identified
- Interpretation and business recommendations

**3. Complete EDA**

```bash
php examples/complete-eda.php
```

**Expected Output:**
- Dataset profile (rows, columns, memory usage)
- Data quality assessment
- Column-by-column analysis
- Correlation findings
- Key insights automatically extracted

## 📖 Key Concepts

### Descriptive Statistics

**Central Tendency:**
- **Mean**: Average value (sensitive to outliers)
- **Median**: Middle value (robust to outliers)
- **Mode**: Most frequent value

**Spread:**
- **Standard Deviation**: Average distance from mean
- **IQR (Interquartile Range)**: Range of middle 50% of data
- **Range**: Difference between max and min

**Shape:**
- **Skewness**: Measure of asymmetry
  - Positive (right-skewed): tail extends right
  - Negative (left-skewed): tail extends left
  - Near zero: symmetric
- **Kurtosis**: Measure of "tailedness"
  - High: more outliers
  - Low: fewer extreme values

### Correlation Analysis

**Pearson Correlation (r):**
- **r = +1**: Perfect positive correlation
- **r = 0**: No linear relationship
- **r = -1**: Perfect negative correlation

**Interpretation:**
- **|r| >= 0.9**: Very strong
- **|r| >= 0.7**: Strong
- **|r| >= 0.5**: Moderate
- **|r| >= 0.3**: Weak
- **|r| < 0.3**: Very weak/none

**Important:** Correlation ≠ Causation

### Data Profiling

Automated analysis that reveals:
- Dataset structure (rows, columns, types)
- Data quality (completeness, missing values)
- Column characteristics (unique values, ranges)
- Relationships (correlations)
- Insights (patterns, issues)

## 🔧 Usage in Your Projects

### Analyze Any Dataset

```php
use DataScience\Analysis\StatisticalAnalyzer;

$analyzer = new StatisticalAnalyzer();

// Your data
$data = [
    ['metric1' => 100, 'metric2' => 50],
    ['metric1' => 120, 'metric2' => 55],
    // ...
];

// Get statistics
$stats = $analyzer->analyzeColumn($data, 'metric1');

echo "Mean: {$stats['mean']}\n";
echo "Std Dev: {$stats['std_dev']}\n";
echo "Skewness: {$stats['skewness']}\n";
```

### Find Correlations

```php
use DataScience\Analysis\CorrelationAnalyzer;

$analyzer = new CorrelationAnalyzer();

// Find correlation between two variables
$correlation = $analyzer->pearsonCorrelation($data, 'price', 'sales');

// Get correlation matrix for all numeric columns
$matrix = $analyzer->correlationMatrix($data);

// Find strongest correlations
$strong = $analyzer->strongestCorrelations($data, threshold: 0.7);
```

### Profile a Dataset

```php
use DataScience\Analysis\DataProfiler;

$profiler = new DataProfiler();

// Generate complete profile
$profile = $profiler->profileDataset($data);

// Print formatted report
$profiler->printProfile($profile);

// Access specific insights
foreach ($profile['insights'] as $insight) {
    echo $insight['message'] . "\n";
}
```

## 🎯 Real-World Applications

### Business Intelligence
- Analyze sales trends
- Understand customer behavior
- Identify key performance drivers

### Data Quality
- Detect missing values
- Find outliers
- Assess data completeness

### Feature Engineering
- Identify correlated features
- Understand distributions
- Guide transformation decisions

### Reporting
- Generate automated insights
- Create executive summaries
- Communicate findings clearly

## 📊 Sample Data Included

All examples use embedded sample data:

- **Sales data**: Products, prices, quantities, revenue, categories
- **Advertising data**: TV, radio, newspaper spend vs sales
- **Customer data**: Age, income, purchases, satisfaction, segments

## 🐛 Troubleshooting

### Error: "Class not found"

**Solution:** Run `composer install` to install dependencies.

### Error: "Division by zero"

**Cause:** All values in a column are identical (std dev = 0).

**Solution:** Check for constant columns before analysis.

### Warning: "Array to string conversion"

**Cause:** Non-numeric values in numeric analysis.

**Solution:** Use `extractNumericValues()` to filter data.

### Unexpected correlations

**Cause:** Outliers or non-linear relationships.

**Solution:** Clean data first (Chapter 4), or try transformations.

## 📚 Related Chapters

- **Chapter 3**: Data Collection (get the data)
- **Chapter 4**: Data Cleaning (prepare the data)
- **Chapter 5**: EDA (understand the data) ← You are here
- **Chapter 6**: Large Datasets (scale the analysis)
- **Chapter 7**: Statistics (deepen the analysis)

## 🔗 Additional Resources

- [MathPHP Documentation](https://github.com/markrogoyski/math-php)
- [Exploratory Data Analysis (Wikipedia)](https://en.wikipedia.org/wiki/Exploratory_data_analysis)
- [Statistics Basics](https://www.statisticshowto.com/)
- [Correlation vs Causation](https://www.tylervigen.com/spurious-correlations)

## 📝 License

This code is part of the **Code with PHP** tutorial series and is provided for educational purposes.

---

**Need help?** Refer to the full chapter at [codewithphp.com/series/data-science-php-developers/chapters/05-exploratory-data-analysis-for-php-developers](https://codewithphp.com/series/data-science-php-developers/chapters/05-exploratory-data-analysis-for-php-developers)


