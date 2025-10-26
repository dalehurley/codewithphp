# Chapter 01: Introduction to AI and Machine Learning - Code Examples

This directory contains runnable PHP examples demonstrating core AI and ML concepts from Chapter 1.

## Quick Setup

Before running these examples, you need to install dependencies:

```bash
# Navigate to this directory
cd docs/series/ai-ml-php-developers/code/chapter-01

# Install PHP-ML library via Composer
composer install
```

That's it! You're ready to run the examples.

## Prerequisites

- **PHP 8.4+** installed and working
- **Composer** installed

Verify your setup:

```bash
php --version    # Should show 8.4 or higher
composer --version
```

## Running the Examples

All examples are standalone and can be run directly with PHP:

```bash
php quick-start-demo.php
php supervised-example.php
php unsupervised-example.php
php ml-lifecycle-demo.php
php recommendation-example.php
```

## File Descriptions

### `quick-start-demo.php`

**Purpose**: Your first machine learning model in 5 minutes

**What it demonstrates**:

- Simple spam email classifier
- Supervised learning with labeled examples
- Feature extraction from text
- Training and prediction phases

**Concepts**: Supervised learning, features, labels, classification

**Run time**: ~5 seconds

---

### `supervised-example.php`

**Purpose**: Detailed spam classification with comprehensive output

**What it demonstrates**:

- More training examples (12 emails)
- Feature engineering (7 features per email)
- Step-by-step feature extraction visualization
- Testing on new, unseen emails

**Concepts**: Supervised learning, feature engineering, k-NN algorithm

**Run time**: ~5 seconds

---

### `unsupervised-example.php`

**Purpose**: Customer segmentation without predefined labels

**What it demonstrates**:

- K-Means clustering algorithm
- Discovering natural groupings in data
- Interpreting discovered clusters
- Business applications of unsupervised learning

**Concepts**: Unsupervised learning, clustering, pattern discovery

**Run time**: ~5 seconds

---

### `ml-lifecycle-demo.php`

**Purpose**: Complete walkthrough of the 6-step ML workflow

**What it demonstrates**:

- Problem definition
- Data collection and preparation
- Model training
- Performance evaluation
- Deployment considerations
- Monitoring and maintenance

**Concepts**: ML lifecycle, iterative process, production considerations

**Run time**: ~10 seconds (includes sleep() for pacing)

---

### `recommendation-example.php`

**Purpose**: Movie recommendation system

**What it demonstrates**:

- Collaborative filtering concept
- User behavior patterns as features
- Personalized recommendations
- Real-world application structure

**Concepts**: Recommendation systems, user profiling, PHP's role in ML

**Run time**: ~5 seconds

---

## Understanding the Output

Each script produces formatted output showing:

1. **Input data**: What the model is learning from
2. **Training process**: How the model learns
3. **Predictions**: What the model predicts for new data
4. **Explanations**: Why it works and key concepts

## Troubleshooting

### Error: "Class not found" or "vendor/autoload.php not found"

**Cause**: Dependencies not installed

**Solution**:

```bash
# Make sure you're in the chapter-01 directory
pwd
# Should show: .../code/chapter-01

# Install dependencies
composer install
```

### Composer not found

**Cause**: Composer not installed on your system

**Solution**:

Install Composer from [getcomposer.org](https://getcomposer.org/):

```bash
# macOS/Linux
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Or use your package manager:
# macOS: brew install composer
# Ubuntu: apt install composer
```

### PHP version too old

**Cause**: PHP 8.4+ required

**Solution**:

Check your PHP version:

```bash
php --version
```

If it's below 8.4, upgrade PHP using your system's package manager or download from [php.net](https://www.php.net/downloads).

### Predictions seem wrong

**Note**: These are toy examples with small training sets (6-20 samples). Real ML models need hundreds or thousands of examples. The purpose is to demonstrate concepts, not achieve production-level accuracy.

## Next Steps

After running these examples:

1. **Experiment**: Modify training data and see how predictions change
2. **Chapter 2**: Set up your complete ML development environment
3. **Chapter 3**: Learn core ML concepts and terminology in depth
4. **Build**: Create your own classifier with your own data

## Learning Path

These examples are designed to be run in order:

1. **quick-start-demo.php** — Get immediate gratification
2. **supervised-example.php** — See supervised learning in detail
3. **unsupervised-example.php** — Contrast with unsupervised learning
4. **ml-lifecycle-demo.php** — Understand the full workflow
5. **recommendation-example.php** — See a real-world application

## Additional Resources

- [PHP-ML Documentation](https://php-ml.readthedocs.io/)
- [Chapter 1 Tutorial](../../chapters/01-introduction-to-ai-and-machine-learning-for-php-developers.md)
- [AI/ML Series Overview](/series/ai-ml-php-developers/)

## What's Installed?

The `composer.json` file installs:

- **php-ai/php-ml** (^0.10) - Machine learning library for PHP with classification, regression, clustering, and more

This is all you need for Chapter 01 examples. Chapter 02 will introduce additional tools and libraries.

Happy learning! 🚀
