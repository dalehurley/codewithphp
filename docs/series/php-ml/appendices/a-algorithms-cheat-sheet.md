---
title: "Appendix A: ML Algorithms Cheat Sheet"
description: "Quick reference guide for choosing the right machine learning algorithm"
series: "php-ml"
appendix: "A"
---

# Appendix A: ML Algorithms Cheat Sheet

## When to Use Each Algorithm

### Classification Problems

#### k-Nearest Neighbors (k-NN)
- **Use when:** Small to medium datasets, need interpretable results
- **Pros:** Simple, no training phase, works well for non-linear boundaries
- **Cons:** Slow prediction, sensitive to feature scaling, curse of dimensionality
- **Best for:** < 10,000 samples, low dimensionality (< 20 features)

#### Decision Trees
- **Use when:** Need interpretable model, mixed feature types, non-linear relationships
- **Pros:** Easy to understand, handles categorical/numerical, no feature scaling needed
- **Cons:** Prone to overfitting, unstable
- **Best for:** Interpretability required, business rule extraction

#### Random Forest
- **Use when:** Need high accuracy, can sacrifice interpretability
- **Pros:** Reduces overfitting, feature importance, handles missing values
- **Cons:** Slower than single trees, less interpretable, memory intensive
- **Best for:** Production systems, complex patterns, >1,000 samples

#### Support Vector Machines (SVM)
- **Use when:** High-dimensional data, clear margin separation
- **Pros:** Effective in high dimensions, memory efficient, versatile kernels
- **Cons:** Slow on large datasets (>10,000 samples), requires feature scaling
- **Best for:** Text classification, image classification, medium-sized datasets

#### Naive Bayes
- **Use when:** Text classification, need fast training and prediction
- **Pros:** Very fast, works well with high dimensions, probabilistic output
- **Cons:** Assumes feature independence (rarely true), sensitive to input distribution
- **Best for:** Spam detection, document classification, real-time prediction

### Regression Problems

#### Linear Regression
- **Use when:** Linear relationship between features and target
- **Pros:** Fast, interpretable, provides coefficients
- **Cons:** Assumes linearity, sensitive to outliers
- **Best for:** Trend analysis, simple predictions

#### Support Vector Regression (SVR)
- **Use when:** Non-linear relationships, outliers present
- **Pros:** Robust to outliers, works with non-linear patterns
- **Cons:** Slow on large datasets, requires parameter tuning
- **Best for:** Complex non-linear patterns, robust predictions

### Clustering (Unsupervised)

#### k-Means
- **Use when:** Know number of clusters, spherical cluster shapes
- **Pros:** Fast, simple, scalable
- **Cons:** Must specify k, sensitive to initialization, assumes spherical clusters
- **Best for:** Customer segmentation, data compression

#### DBSCAN
- **Use when:** Don't know cluster count, arbitrary cluster shapes, noise handling
- **Pros:** Finds arbitrary shapes, handles noise, no k parameter
- **Cons:** Sensitive to parameters, struggles with varying densities
- **Best for:** Anomaly detection, geographic clustering

#### Fuzzy C-Means
- **Use when:** Soft clustering needed, overlapping clusters
- **Pros:** Membership degrees, handles uncertainty
- **Cons:** Slower than k-Means, requires c parameter
- **Best for:** Pattern recognition, image segmentation

## Algorithm Selection Flowchart

```
Start
  ↓
Labeled data?
  ├─ Yes → Supervised Learning
  │   ↓
  │   Predicting category or number?
  │   ├─ Category → Classification
  │   │   ↓
  │   │   Dataset size?
  │   │   ├─ < 1K → k-NN or Naive Bayes
  │   │   ├─ 1K-10K → Decision Tree or SVM
  │   │   └─ > 10K → Random Forest or Neural Network
  │   │
  │   └─ Number → Regression
  │       ↓
  │       Linear relationship?
  │       ├─ Yes → Linear Regression
  │       └─ No → SVR or Decision Tree Regression
  │
  └─ No → Unsupervised Learning
      ↓
      Know number of groups?
      ├─ Yes → k-Means
      ├─ No → DBSCAN
      └─ Overlapping groups → Fuzzy C-Means
```

## Performance Comparison

| Algorithm | Training Time | Prediction Time | Memory | Accuracy | Interpretability |
|-----------|---------------|-----------------|--------|----------|------------------|
| k-NN      | O(1)          | O(n)            | High   | Medium   | High             |
| Decision Tree | O(n log n) | O(log n)        | Low    | Medium   | Very High        |
| Random Forest | O(n log n) × trees | O(log n) × trees | High | High | Medium |
| SVM       | O(n²) to O(n³)| O(n)            | Medium | High     | Low              |
| Naive Bayes | O(n)        | O(1)            | Low    | Medium   | High             |
| Linear Reg | O(n)         | O(1)            | Low    | Low-Med  | Very High        |
| k-Means   | O(n × k × i) | O(k)            | Low    | N/A      | Medium           |

*n = samples, k = clusters/neighbors, i = iterations*

## Common Pitfalls and Solutions

### Problem: Poor accuracy
- **Check:** Feature scaling (normalize/standardize)
- **Try:** Different algorithm, more features, more data
- **Consider:** Feature engineering, ensemble methods

### Problem: Overfitting (high training, low test accuracy)
- **Solution:** More training data, regularization, simpler model
- **Try:** Cross-validation, reduce features, ensemble methods

### Problem: Slow training
- **Solution:** Reduce dataset size, simpler algorithm, feature selection
- **Try:** Sampling, dimensionality reduction (PCA)

### Problem: Slow prediction
- **Solution:** Use different algorithm (e.g., Decision Tree instead of k-NN)
- **Try:** Model compression, feature reduction

### Problem: High memory usage
- **Solution:** Reduce features, use streaming, batch processing
- **Try:** Feature selection, online learning algorithms

## PHP-ML Quick Reference

```php
<?php

use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;
use Phpml\Classification\DecisionTree;
use Phpml\Ensemble\RandomForest;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\Clustering\KMeans;
use Phpml\Clustering\DBSCAN;

// Classification
$knn = new KNearestNeighbors($k = 3);
$nb = new NaiveBayes();
$svm = new SVC();
$dt = new DecisionTree($maxDepth = 10);
$rf = new RandomForest($numTrees = 100);

// Regression
$lr = new LeastSquares();
$svr = new SVR();

// Clustering
$kmeans = new KMeans($k = 3);
$dbscan = new DBSCAN($epsilon = 0.5, $minSamples = 3);

// Train
$classifier->train($samples, $labels);

// Predict
$prediction = $classifier->predict($testSample);
$predictions = $classifier->predict($testSamples);
```

## Dataset Size Guidelines

| Samples | Recommended Algorithms |
|---------|------------------------|
| < 100 | k-NN, Naive Bayes, Linear Regression |
| 100-1,000 | Decision Tree, k-NN, Naive Bayes, SVM |
| 1,000-10,000 | Decision Tree, SVM, Random Forest |
| 10,000-100,000 | Random Forest, Neural Networks |
| > 100,000 | Consider: data sampling, online learning, distributed systems |

## Feature Count Guidelines

| Features | Recommendations |
|----------|-----------------|
| < 10 | Most algorithms work well |
| 10-100 | SVM, Random Forest, Neural Networks |
| 100-1,000 | SVM, Neural Networks, consider PCA |
| > 1,000 | Feature selection, PCA, specialized algorithms |

---

**Related:**
- [Appendix B: PHP-ML API Reference](/series/php-ml/appendices/b-api-reference)
- [Appendix C: Math Refresher](/series/php-ml/appendices/c-math-refresher)
- [Chapter 28: Algorithm Selection Guide](/series/php-algorithms/chapters/28-algorithm-selection-guide) (from PHP Algorithms series)
