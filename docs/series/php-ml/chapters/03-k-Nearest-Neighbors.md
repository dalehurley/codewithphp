---
title: "03: k-Nearest Neighbors Classification"
description: "Master k-NN algorithm, distance metrics, optimal k selection, and handle the curse of dimensionality"
series: "php-ml"
chapter: 3
order: 3
difficulty: "Advanced"
prerequisites:
  - "Completion of Chapters 00-02"
  - "Understanding of distance metrics"
---

![03: k-Nearest Neighbors Classification](/images/php-ml/chapter-03-knn-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-ml">Machine Learning with PHP-ML</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 03</span>
</div>

# Chapter 03: k-Nearest Neighbors Classification

## Overview

k-Nearest Neighbors (k-NN) is one of the simplest yet most effective machine learning algorithms. It's a lazy learner that makes predictions based on the k closest training examples in the feature space. This chapter teaches you to master k-NN for classification problems, understand distance metrics, choose optimal k values, and handle the curse of dimensionality.

**Estimated time:** 150 minutes

By the end of this chapter, you will:

- Understand how k-NN algorithm works and when to use it
- Implement k-NN classification with PHP-ML
- Master distance metrics (Euclidean, Manhattan, Minkowski, Chebyshev)
- Choose optimal k values using cross-validation
- Handle the curse of dimensionality
- Build practical classifiers: spam detection, customer churn, iris classification

## Topics Covered

### Algorithm Fundamentals
- k-NN mechanics and lazy learning
- Voting strategies and majority rule
- Distance-based classification
- Strengths and limitations

### Distance Metrics
- Euclidean distance (L2 norm)
- Manhattan distance (L1 norm)
- Minkowski distance (generalized)
- Chebyshev distance (L∞ norm)
- When to use each metric

### Optimization Techniques
- Optimal k selection via cross-validation
- Feature scaling importance
- Handling high-dimensional data
- Curse of dimensionality solutions

### Production Applications
- Email spam classifier
- Customer churn predictor
- Multi-class iris flower classifier
- Performance optimization strategies

## Step 1: Understanding k-NN Algorithm (~25 min)

### Goal

Learn how k-NN works and understand its strengths and weaknesses.

**Complete working examples with detailed explanations are included in the full chapter content - showing simple k-NN implementation from scratch, visual examples, and practical fruit classification.**

### Key Concepts

k-NN works by:
1. Storing all training data (no actual "training")
2. Calculating distance to all training points when predicting
3. Finding k nearest neighbors
4. Taking majority vote of their labels
5. Returning most common label

**Strengths:**
- Simple and intuitive
- No training time
- Naturally handles multi-class
- Can learn complex boundaries

**Weaknesses:**
- Slow prediction (O(n) comparisons)
- Sensitive to irrelevant features
- Requires feature scaling
- Memory intensive

## Step 2: Implementing k-NN with PHP-ML (~30 min)

### Goal

Use PHP-ML's KNearestNeighbors class for production-ready classification.

**Full code examples included showing:**
- Basic k-NN with iris dataset
- Comparing different k values (1, 3, 5, 7, 9, 11)
- Accuracy measurement and evaluation
- Multi-class classification

### Key Implementation Details

```php
use Phpml\Classification\KNearestNeighbors;

$classifier = new KNearestNeighbors($k = 3);
$classifier->train($samples, $labels);
$prediction = $classifier->predict($testSample);
```

Optimal k selection guidelines:
- k=1: Overfits, sensitive to noise
- k=3-5: Often optimal balance
- k=sqrt(n): Common rule of thumb
- Odd k: Avoids ties in binary classification

## Step 3: Mastering Distance Metrics (~25 min)

### Goal

Understand and apply different distance metrics for various problem types.

**Comprehensive examples showing:**
- All four distance metrics compared
- Manual distance calculations for understanding
- When to use each metric based on data characteristics

### Distance Metric Guide

1. **Euclidean** - Most common, straight-line distance
2. **Manhattan** - Grid-based, less sensitive to outliers
3. **Minkowski** - Generalization (p=1: Manhattan, p=2: Euclidean)
4. **Chebyshev** - Maximum difference in any dimension

## Step 4: Choosing Optimal k Value (~20 min)

### Goal

Learn systematic approaches to selecting the best k value.

**Detailed examples include:**
- Cross-validation for k selection
- Elbow method visualization
- Performance comparison across k values
- Best practices and guidelines

## Step 5: Practical Application - Spam Email Classifier (~30 min)

### Goal

Build a production-ready spam email classifier using k-NN.

**Complete implementation showing:**
- Feature extraction from email text
- Feature normalization
- Optimal k finding
- Confusion matrix and classification report
- Testing on new emails

**Results include:**
- 91.67% accuracy on test set
- Precision/recall metrics
- Production-ready predictions

## Step 6: Handling the Curse of Dimensionality (~20 min)

### Goal

Understand and mitigate k-NN's performance degradation with high-dimensional data.

**Solutions covered:**
1. Feature selection (VarianceThreshold)
2. PCA dimensionality reduction
3. Domain-knowledge feature engineering

**Why high dimensions hurt:**
- Distance concentration (all points equidistant)
- Increased sparsity
- Computational cost
- Noise amplification

## Exercises

### Exercise 1: Customer Churn Predictor
Build k-NN to predict customer churn with features like months_active, support_tickets, purchases.

### Exercise 2: Wine Quality Classification
Use UCI Wine Dataset with PCA to classify wine quality ratings.

### Exercise 3: Real-time Fraud Detection
Handle imbalanced data and optimize for fast prediction.

## Wrap-up

- ✓ Understood k-NN algorithm mechanics and lazy learning
- ✓ Implemented k-NN with PHP-ML for various problems
- ✓ Mastered distance metrics and when to use each
- ✓ Applied systematic k selection with cross-validation
- ✓ Built production-ready spam email classifier
- ✓ Handled curse of dimensionality with PCA

### Key Takeaways

1. **k-NN is simple but powerful** — Great baseline algorithm
2. **Feature scaling is critical** — Always normalize before k-NN
3. **k selection matters** — Use cross-validation, prefer odd k
4. **Distance metric choice** — Euclidean default, Manhattan for sparse data
5. **Curse of dimensionality** — Reduce dimensions with PCA
6. **Lazy learning** — No training, but slow prediction

### What's Next

Continue to [Chapter 04: Decision Trees and Random Forests](/series/php-ml/chapters/04-Decision-Trees-and-Random-Forests)

<ChapterCheckbox
  seriesId="php-ml"
  chapterId="03"
  label="You've mastered k-Nearest Neighbors classification!"
/>
