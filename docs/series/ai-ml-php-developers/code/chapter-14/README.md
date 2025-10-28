# Chapter 14: NLP Project - Text Classification in PHP

Complete, working code examples for building a sentiment analysis system.

## Prerequisites

- PHP 8.4+
- Composer
- PHP-ML library (installed via composer)

## Setup

```bash
# Navigate to this directory
cd docs/series/ai-ml-php-developers/code/chapter-14

# Install dependencies
composer install

# Verify PHP version
php --version  # Should be 8.4+
```

## Files Overview

### Core Examples

- `01-load-dataset.php` — Load and inspect the movie review dataset
- `02-text-preprocessing.php` — Text preprocessing pipeline (tokenization, stopwords, stemming)
- `03-bag-of-words.php` — Bag-of-words vectorization
- `04-tfidf-vectorizer.php` — TF-IDF feature extraction
- `05-naive-bayes-sentiment.php` — Train Naive Bayes classifier
- `06-svm-sentiment.php` — Train SVM classifier
- `07-logistic-regression-sentiment.php` — Train Logistic Regression classifier
- `08-evaluation-metrics.php` — Comprehensive model evaluation
- `09-model-comparison.php` — Compare all three algorithms
- `10-advanced-features.php` — N-grams and feature selection
- `11-model-persistence.php` — Save and load trained models
- `12-production-sentiment-analyzer.php` — Complete production-ready class

### Data Files

- `data/movie_reviews.csv` — 1,000 labeled movie reviews (500 positive, 500 negative)
- `data/stopwords.txt` — English stopwords list for preprocessing

## Running the Examples

### Quick Start

```bash
# Load and explore the dataset
php 01-load-dataset.php

# Test text preprocessing
php 02-text-preprocessing.php

# Try bag-of-words vectorization
php 03-bag-of-words.php

# Compare with TF-IDF
php 04-tfidf-vectorizer.php
```

### Training Classifiers

```bash
# Train Naive Bayes (fast, ~0.1s)
php 05-naive-bayes-sentiment.php

# Train SVM (slower, ~2-3s, higher accuracy)
php 06-svm-sentiment.php

# Compare all algorithms
php 09-model-comparison.php
```

### Advanced Examples

```bash
# Evaluate with comprehensive metrics
php 08-evaluation-metrics.php

# Try n-gram features
php 10-advanced-features.php

# Save and load models
php 11-model-persistence.php

# Use production-ready analyzer
php 12-production-sentiment-analyzer.php
```

## Expected Performance

With the provided dataset (1,000 reviews):

| Algorithm           | Training Time | Accuracy | Best For                |
| ------------------- | ------------- | -------- | ----------------------- |
| Naive Bayes         | ~0.1-0.2s     | ~87%     | Speed, small datasets   |
| SVM (Linear)        | ~2-3s         | ~89%     | Maximum accuracy        |
| Logistic Regression | ~1-2s         | ~88%     | Balanced speed/accuracy |

## Dataset Format

`data/movie_reviews.csv`:

```csv
review,sentiment
"This movie was fantastic!",positive
"Terrible waste of time.",negative
```

- **Size**: 1,000 reviews (500 positive, 500 negative)
- **Format**: CSV with header row
- **Balance**: 50/50 class distribution
- **Source**: Synthetic movie reviews for educational purposes

## Common Issues

### "Fatal error: Class 'Phpml\...' not found"

**Solution**: Install dependencies

```bash
composer install
```

### "Dataset file not found"

**Solution**: Run from the correct directory

```bash
cd docs/series/ai-ml-php-developers/code/chapter-14
php 01-load-dataset.php
```

### "Memory exhausted"

**Solution**: Increase PHP memory limit

```php
ini_set('memory_limit', '512M');
```

Or in php.ini:

```ini
memory_limit = 512M
```

### Training is very slow

**Solutions**:

- Use smaller training set for experimentation
- Limit vocabulary size: `$vectorizer->fit($docs, maxFeatures: 1000)`
- Start with Naive Bayes (fastest algorithm)

## Exercises

Try these extensions to deepen your understanding:

1. **Improve Preprocessing**: Add HTML removal, URL removal, contraction expansion
2. **Multi-class Classification**: Extend to 5-star ratings (1-5)
3. **Domain Adaptation**: Try with product reviews or other text domains
4. **N-gram Features**: Add bigrams/trigrams to capture phrases like "not good"
5. **Ensemble Methods**: Combine multiple classifiers for better accuracy
6. **Cross-validation**: Implement k-fold cross-validation for robust evaluation
7. **Feature Selection**: Identify which words are most predictive
8. **Real-time API**: Build a REST API endpoint for sentiment analysis

## Learning Path

Recommended order for working through the examples:

1. **Understand the data** (01-load-dataset.php)
2. **Learn preprocessing** (02-text-preprocessing.php)
3. **Feature extraction** (03-bag-of-words.php, 04-tfidf-vectorizer.php)
4. **Train your first model** (05-naive-bayes-sentiment.php)
5. **Compare algorithms** (06-svm-sentiment.php, 09-model-comparison.php)
6. **Evaluate properly** (08-evaluation-metrics.php)
7. **Production deployment** (11-model-persistence.php, 12-production-sentiment-analyzer.php)
8. **Advanced techniques** (10-advanced-features.php)

## Further Resources

- [Chapter 14 Tutorial](../../chapters/14-nlp-project-text-classification-in-php.md)
- [PHP-ML Documentation](https://php-ml.readthedocs.io/)
- [Rubix ML Documentation](https://docs.rubixml.com/)

## License

Educational code samples for Code with PHP tutorial series.

## Contributing

Found a bug or want to improve an example? See the main repository for contribution guidelines.
