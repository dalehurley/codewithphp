# Chapter 07: Statistics Every PHP Developer Needs for Data Science

Complete statistical analysis toolkit for PHP data science applications.

## Overview

This chapter provides essential statistical tools including:

- **Distribution Analysis**: Normal, binomial, Poisson distributions
- **Confidence Intervals**: For means, proportions, and differences
- **Hypothesis Testing**: T-tests, z-tests, chi-square tests, ANOVA
- **Effect Size Calculations**: Cohen's d, eta-squared
- **A/B Testing**: Complete framework with sample size calculation

## Installation

```bash
composer install
```

## Requirements

- PHP 8.4+
- MathPHP library (^2.8)

## Usage

### Distribution Analysis

```php
use DataScience\Statistics\DistributionAnalyzer;

$analyzer = new DistributionAnalyzer();

// Test for normal distribution
$data = [165, 170, 168, 172, 169, 171, 167, 173, 166, 170];
$result = $analyzer->isNormallyDistributed($data);

// Calculate z-score
$z = $analyzer->zScore(180, 170, 10);
```

### Confidence Intervals

```php
use DataScience\Statistics\ConfidenceIntervalCalculator;

$calculator = new ConfidenceIntervalCalculator();

// CI for mean
$ci = $calculator->forMean($data, 0.95);

// CI for proportion
$ci = $calculator->forProportion($successes, $total, 0.95);
```

### Hypothesis Testing

```php
use DataScience\Statistics\HypothesisTester;

$tester = new HypothesisTester();

// One-sample t-test
$result = $tester->oneSampleTTest($data, $populationMean);

// Two-sample t-test with effect size
$result = $tester->twoSampleTTest($group1, $group2);
$effectSize = $tester->cohensD($group1, $group2);
```

### ANOVA

```php
use DataScience\Statistics\ANOVAAnalyzer;

$anova = new ANOVAAnalyzer();

// One-way ANOVA
$groups = [
    [5.1, 5.3, 5.2, 5.4],
    [6.2, 6.1, 6.3, 6.4],
    [7.1, 7.3, 7.2, 7.0]
];

$result = $anova->oneWayANOVA($groups);
```

### A/B Testing

```php
use DataScience\Statistics\ABTestAnalyzer;

$abTest = new ABTestAnalyzer();

// Analyze conversion test
$result = $abTest->analyzeConversionTest(
    controlConversions: 145,
    controlTotal: 2000,
    variantConversions: 178,
    variantTotal: 2000
);

// Calculate required sample size
$sampleSize = $abTest->calculateSampleSize(
    baselineRate: 0.10,
    minimumDetectableEffect: 0.10,
    alpha: 0.05,
    power: 0.80
);
```

## Examples

Run the example scripts:

```bash
# Distribution analysis
php examples/distributions.php

# Confidence intervals
php examples/confidence-intervals.php

# Hypothesis testing
php examples/hypothesis-testing.php

# ANOVA
php examples/anova.php

# A/B testing
php examples/ab-testing.php
```

## Running Tests

```bash
composer test
```

## Code Quality

All code follows:
- PHP 8.4 syntax and features
- PSR-12 coding standards
- Comprehensive type hints
- Full input validation
- Extensive error handling

## Statistical Accuracy

All calculations have been validated against:
- R statistical computing
- Python scipy.stats
- Published statistical textbooks

## Features

### Input Validation
- All methods validate parameters before processing
- Clear error messages for invalid inputs
- Type safety throughout

### Performance Optimization
- Generator pattern for large datasets (>100k samples)
- Memory-efficient streaming operations
- Optimized calculations

### Effect Sizes
- Cohen's d for t-tests
- Eta-squared for ANOVA
- Proper interpretation guidelines

### Error Handling
- Prevents division by zero
- Handles edge cases (empty arrays, zero variance)
- Validates statistical assumptions

## Best Practices

1. **Check Assumptions**: Verify normality before parametric tests
2. **Report Effect Sizes**: Don't rely solely on p-values
3. **Use Confidence Intervals**: They provide more information than p-values
4. **Correct for Multiple Comparisons**: Use Bonferroni or FDR
5. **Calculate Sample Sizes**: Before running experiments

## Common Pitfalls to Avoid

- ❌ Using t-tests on non-normal small samples
- ❌ P-hacking (testing multiple ways until significant)
- ❌ Stopping A/B tests early when "significant"
- ❌ Ignoring effect size (statistical vs practical significance)
- ❌ Not checking for sufficient sample size

## Further Reading

- [Khan Academy Statistics](https://www.khanacademy.org/math/statistics-probability)
- [Statistics Done Wrong](https://www.statisticsdonewrong.com/)
- [Think Stats](https://greenteapress.com/thinkstats2/)

## License

Educational use - Part of "Data Science for PHP Developers" tutorial series
