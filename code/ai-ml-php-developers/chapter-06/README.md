# Chapter 06: Classification Basics and Building a Spam Filter

Complete code examples for building and evaluating spam classification models in PHP.

## Setup

```bash
# From the chapter-06 directory
composer install
```

This will install the required dependencies (PHP-ML or Rubix ML) from the chapter-02 setup.

## Files

### Core Concepts

- **01-binary-classification-intro.php** — Binary classification fundamentals with decision boundaries
- **02-feature-extraction-basic.php** — Basic text feature extraction using PHP 8.4 property hooks
- **03-bag-of-words.php** — Bag-of-words implementation from scratch
- **04-tfidf-features.php** — TF-IDF feature weighting for text classification

### Spam Filter Implementations

- **05-naive-bayes-spam-filter.php** — Complete Naive Bayes spam classifier
- **06-knn-spam-filter.php** — k-Nearest Neighbors classifier for comparison
- **10-complete-spam-filter.php** — Production-ready spam filter with confidence scores

### Evaluation and Analysis

- **07-evaluation-metrics.php** — Precision, recall, F1-score calculations
- **08-confusion-matrix-analysis.php** — Detailed error analysis
- **09-feature-importance.php** — Which words most indicate spam?

### Data

- **data/emails.csv** — Sample email dataset with spam/ham labels
- **data/test-emails.txt** — Additional test cases

## Running Examples

### Basic Classification

```bash
php 01-binary-classification-intro.php
```

### Feature Extraction

```bash
php 02-feature-extraction-basic.php
php 03-bag-of-words.php
php 04-tfidf-features.php
```

### Build Spam Filters

```bash
php 05-naive-bayes-spam-filter.php  # Naive Bayes approach
php 06-knn-spam-filter.php          # k-NN approach
```

### Evaluate Models

```bash
php 07-evaluation-metrics.php       # Calculate precision/recall
php 08-confusion-matrix-analysis.php # Analyze errors
php 09-feature-importance.php       # Feature analysis
```

### Production Filter

```bash
php 10-complete-spam-filter.php     # Complete system
```

## Key Concepts Demonstrated

1. **Binary Classification** — Two-class prediction (spam vs ham)
2. **Text Feature Extraction** — Converting text to numeric vectors
3. **Naive Bayes** — Probabilistic classification ideal for text
4. **Model Evaluation** — Beyond accuracy: precision, recall, F1
5. **Confusion Matrix** — Understanding classification errors
6. **Feature Engineering** — Domain-specific text features

## Dataset

The `data/emails.csv` file contains realistic spam and ham emails for training and testing. Format:

```csv
label,subject,body
spam,"WIN FREE MONEY!!!","Congratulations! You've won..."
ham,"Meeting tomorrow","Hi, can we schedule..."
```

## Expected Results

- **Naive Bayes**: 90-95% accuracy, very fast training
- **k-NN**: 85-90% accuracy, slower but no training phase
- **Precision**: > 90% (minimize false positives)
- **Recall**: > 85% (catch most spam)

## Next Steps

After completing this chapter, you'll be ready for:

- **Chapter 07**: Model Evaluation and Improvement
- **Chapter 08**: Advanced ML Libraries
- **Chapter 13**: Advanced NLP Projects

## Notes

- All code uses PHP 8.4 features where beneficial (property hooks, typed properties)
- Examples are self-contained and can run independently
- Production code should include additional validation and error handling
- Real spam filters use much larger training datasets (thousands of emails)
