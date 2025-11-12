# Chapter 07: Model Evaluation and Improvement - Code Examples

This directory contains complete, runnable code examples for Chapter 07 of the AI/ML for PHP Developers series.

## Prerequisites

- PHP 8.4+
- Composer

## Installation

```bash
cd docs/series/ai-ml-php-developers/code/chapter-07
composer install
```

This will install:

- `php-ai/php-ml` - Core ML algorithms
- `rubix/ml` - Advanced ML library

## Running the Examples

Each file is a standalone, runnable example demonstrating specific evaluation or improvement techniques:

### Evaluation Metrics & Validation

- `01-evaluation-metrics.php` — Comprehensive metrics toolkit (accuracy, precision, recall, F1, specificity, MCC)
- `02-confusion-matrix-deep-dive.php` — Advanced confusion matrix analysis
- `03-precision-recall-tradeoff.php` — Understanding and optimizing the tradeoff
- `04-stratified-cross-validation.php` — Proper CV for imbalanced datasets
- `05-roc-curve.php` — ROC-AUC analysis and visualization

### Model Diagnosis & Improvement

- `06-learning-curves.php` — Diagnosing bias vs. variance with learning curves
- `07-grid-search.php` — Systematic hyperparameter tuning
- `08-feature-importance.php` — Permutation importance for feature ranking
- `09-feature-selection.php` — Automated feature selection

### Ensemble Methods

- `10-ensemble-voting.php` — Voting classifiers (hard and soft voting)

  ```bash
  php 10-ensemble-voting.php
  ```

  Demonstrates combining k-NN, Naive Bayes, and Decision Tree for 2-5% accuracy gains.

- `11-ensemble-bagging.php` — Bootstrap aggregating to reduce overfitting
  ```bash
  php 11-ensemble-bagging.php
  ```
  Shows how bagging reduces variance and stabilizes predictions.

### Class Imbalance Handling

- `12-class-imbalance-smote.php` — SMOTE and resampling techniques

  ```bash
  php 12-class-imbalance-smote.php
  ```

  Compares random oversampling, SMOTE, and undersampling on severely imbalanced data.

- `13-class-weights.php` — Using class weights to handle imbalance
  ```bash
  php 13-class-weights.php
  ```
  Demonstrates adjusting model loss function without modifying training data.

### Error Analysis & Production

- `14-error-analysis.php` — Systematic error pattern identification
- `15-spam-filter-optimized.php` — Complete production-ready spam filter

## Quick Start

To see the power of ensembles and class imbalance handling:

```bash
# See how voting ensembles improve accuracy
php 10-ensemble-voting.php

# See how SMOTE handles severe imbalance (1% minority class)
php 12-class-imbalance-smote.php
```

## Key Concepts Demonstrated

### Ensemble Methods

- **Voting Classifiers**: Combine diverse algorithms for better accuracy
- **Bagging**: Reduce overfitting through bootstrap sampling
- **Trade-offs**: Accuracy gains vs. inference speed and complexity

### Class Imbalance

- **SMOTE**: Synthetic minority oversampling via k-NN interpolation
- **Random Sampling**: Oversampling and undersampling techniques
- **Class Weights**: Adjusting loss function to prioritize minority class
- **Evaluation**: Why accuracy is misleading on imbalanced data

## Expected Results

Running the ensemble and imbalance examples should show:

### Ensemble Voting (10-ensemble-voting.php)

```
Individual Models:
  k-NN:          92.4%
  Naive Bayes:   88.6%
  Decision Tree: 90.2%

Ensemble:
  Soft Voting:   94.8% (+2.4% improvement)
```

### SMOTE (12-class-imbalance-smote.php)

```
Baseline (no handling):
  Accuracy: 99.0% (predicts all majority!)
  Recall:   0.0% (catches ZERO minority samples)

With SMOTE:
  Accuracy: 94.2%
  Recall:   90.0% ✓
  F1-Score: 83.8% ✓
```

## Common Issues

### "Class not found" Errors

Make sure you've run `composer install` first.

### Low Accuracy on Small Datasets

The synthetic datasets are small for demo purposes. Real-world datasets (1000+ samples) will show clearer improvements.

### SMOTE Creates Unrealistic Samples

With very small minority classes (<5 samples), SMOTE may struggle. Try:

- Collect more minority samples
- Use class weights instead
- Treat as anomaly detection instead of classification

### Ensemble Doesn't Help

If base models make correlated errors (similar mistakes), ensembles won't help much. Try:

- More diverse algorithms
- Different feature subsets for each model
- Bagging to create diversity through sampling

## Next Steps

After running these examples:

1. **Apply to your data**: Use these techniques on your own imbalanced datasets
2. **Combine techniques**: SMOTE + class weights often works best
3. **Monitor production**: Track precision/recall, not just accuracy
4. **Iterate**: Error analysis reveals what to improve next

## Further Reading

- [Ensemble Methods - scikit-learn](https://scikit-learn.org/stable/modules/ensemble.html)
- [SMOTE Paper](https://arxiv.org/abs/1106.1813) - Original research
- [Imbalanced-learn](https://imbalanced-learn.org/) - Python reference library
- [Random Forests](https://www.stat.berkeley.edu/~breiman/RandomForests/cc_home.htm) - Famous bagging ensemble

## Related Chapters

- **Chapter 06**: Building the spam filter that these techniques improve
- **Chapter 08**: Using PHP-ML and Rubix ML libraries efficiently
- **Chapter 09**: Deploying models to production with proper evaluation

---

**Note**: All code is PHP 8.4 compatible and uses modern language features (typed properties, named arguments, etc.).
