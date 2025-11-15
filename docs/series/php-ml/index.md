---
title: Machine Learning with PHP-ML
description: Master machine learning in PHP—from classification and regression to neural networks and real-world applications using the PHP-ML library.
series: php-ml
order: 0
difficulty: Advanced
prerequisites:
  [
    "Solid understanding of PHP 8.4+",
    "Familiarity with object-oriented programming",
    "Basic understanding of mathematics (algebra, statistics)",
    "Completion of PHP Algorithms series recommended",
  ]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Machine Learning with PHP-ML</span>
</div>

![Machine Learning with PHP-ML](/images/php-ml/php-ml-series-hero-full.webp)

# Machine Learning with PHP-ML <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Welcome to **Machine Learning with PHP-ML** — a comprehensive, hands-on course that teaches you machine learning fundamentals through practical PHP implementations using the PHP-ML library. Whether you're building recommendation systems, predictive models, or intelligent data analysis tools, this series will give you the knowledge and skills to implement machine learning solutions in PHP.

Machine learning transforms raw data into actionable insights and intelligent applications. Yet many PHP developers assume ML is only possible with Python or R. This series proves otherwise. You'll learn to implement classification, regression, clustering, and neural networks—all in modern PHP using the powerful PHP-ML library.

This series bridges the gap between traditional PHP development and modern machine learning. You'll learn ML algorithms explained in developer-friendly terms, implemented in PHP 8.4, and applied to real-world web application scenarios. From spam detection to product recommendations, from customer segmentation to demand forecasting—you'll understand when and how to apply each ML technique effectively.

By the end of this series, you'll have mastered core ML concepts, implemented dozens of algorithms using PHP-ML, explored data preprocessing and feature engineering, and built practical projects that demonstrate real-world applications. More importantly, you'll have developed ML thinking—the ability to recognize problems that ML can solve and design effective solutions.

## Who This Is For

This series is designed for:

- **Expert PHP developers** who want to add machine learning to their toolkit
- **Backend engineers** looking to implement ML-powered features without leaving PHP
- **Data-driven developers** who want to build intelligent applications
- **Full-stack developers** wanting to understand ML for modern web applications
- **Technical leads** evaluating ML solutions for PHP-based systems

You should be comfortable with PHP syntax, OOP concepts, arrays, and mathematical operations. While prior ML experience isn't required, basic understanding of statistics and algebra will help you grasp the concepts faster.

## Prerequisites

**Software Requirements:**

- **PHP 8.4+** (we'll use modern PHP features throughout)
- **Composer** (for installing PHP-ML and dependencies)
- **PHP-ML library** (we'll install this together)
- **Text editor or IDE** (VS Code, PhpStorm, or your preferred editor)
- **Terminal/Command line** access

**Time Commitment:**

- **Estimated total**: 30–40 hours to complete all chapters
- **Per chapter**: 90 minutes to 3 hours
- **Quick start path**: 8 hours
- **Complete mastery path**: 40+ hours

**Skill Assumptions:**

- You can write PHP classes and use namespaces confidently
- You understand arrays, loops, and object-oriented programming
- You're familiar with basic mathematical concepts (mean, variance, distance)
- You can use Composer to manage dependencies
- No prior machine learning knowledge required (we'll teach you!)

## What You'll Build

<ProgressTracker seriesId="php-ml" :totalChapters="16" title="Your Progress" />

By working through this series, you will:

1. **Master machine learning fundamentals** in PHP 8.4:
   - Classification algorithms (k-NN, Decision Trees, SVM, Naive Bayes)
   - Regression techniques (Linear, SVR, Decision Tree Regression)
   - Clustering algorithms (k-Means, DBSCAN, Fuzzy C-Means)
   - Neural networks (Perceptron, Multilayer Perceptron)
   - Ensemble methods (Random Forest, Bagging, AdaBoost)

2. **Build practical ML-powered projects**:
   - Spam email classifier
   - House price prediction system
   - Customer segmentation engine
   - Product recommendation system
   - Sentiment analysis tool
   - Demand forecasting application

3. **Master data preprocessing and feature engineering**:
   - Data cleaning and normalization
   - Feature extraction and selection
   - Dimensionality reduction (PCA)
   - Handling missing data and outliers
   - Label encoding and one-hot encoding

4. **Gain production-ready ML skills**:
   - Model evaluation and metrics
   - Cross-validation techniques
   - Hyperparameter tuning
   - Model persistence and deployment
   - Performance optimization

Every code example is production-ready, following PHP 8.4 and ML best practices, with comprehensive explanations of how and why algorithms work.

## Learning Objectives

By the end of this series, you will be able to:

- **Understand ML fundamentals** including supervised and unsupervised learning
- **Implement classification algorithms** for categorization problems
- **Build regression models** for prediction and forecasting
- **Apply clustering techniques** for data segmentation and pattern discovery
- **Create neural networks** for complex pattern recognition
- **Preprocess data effectively** using normalization, encoding, and feature engineering
- **Evaluate model performance** using appropriate metrics and validation techniques
- **Choose the right algorithm** for any given ML problem
- **Deploy ML models** in production PHP applications
- **Optimize ML pipelines** for performance and accuracy

## How This Series Works

This series follows a **progressive, hands-on approach**: you'll learn each ML concept by understanding the theory, implementing it with PHP-ML, evaluating its performance, and seeing real-world applications.

Each chapter includes:

- **Clear explanations** of ML concepts using developer-friendly language
- **Step-by-step implementations** in modern PHP 8.4 with PHP-ML
- **Algorithm analysis** for understanding complexity and performance
- **Practical examples** showing when and why to use each technique
- **Model evaluation** with metrics and visualization
- **Hands-on exercises** to reinforce learning
- **Troubleshooting tips** for common implementation challenges
- **Further reading** for deeper exploration

We'll start with ML fundamentals and PHP-ML setup, progress through classification and regression, explore clustering and neural networks, and finish with ensemble methods and real-world applications.

::: tip
Type the code yourself instead of copy-pasting. Understanding ML requires hands-on practice—implementing, testing, evaluating, and iterating. Build muscle memory and debugging skills by typing every example.
:::

## Quick Start

Want to see machine learning in action right now? Here's a 2-minute example:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;

// Training data: [hours_studied, practice_tests] => pass/fail
$samples = [
    [2, 1], [3, 1], [4, 2], [5, 3], [6, 4], // Failed students
    [7, 5], [8, 6], [9, 7], [10, 8]         // Passed students
];

$labels = ['fail', 'fail', 'fail', 'fail', 'fail', 'pass', 'pass', 'pass', 'pass'];

// Train the classifier
$classifier = new KNearestNeighbors();
$classifier->train($samples, $labels);

// Predict for a new student: [6 hours studied, 3 practice tests]
$prediction = $classifier->predict([6, 3]);
echo "Prediction: {$prediction}\n"; // Output: pass or fail

// The algorithm learned patterns from past data to predict future outcomes!
```

**What's Next?**
That's machine learning: finding patterns in data to make predictions. Head to [Chapter 00: Introduction to Machine Learning](/series/php-ml/chapters/00-introduction-to-machine-learning/) for a comprehensive introduction.

---

## Learning Paths & Chapters

Choose your learning path based on your goals and experience level, or explore all chapters below.

::: tip Recommended Learning Paths
- **Quick Start** (~8 hours): Chapters 00, 01, 02, 03, 07, 10, 15
- **Classification Focus** (~15 hours): Chapters 00-06, 10-11, 15
- **Complete ML Mastery** (~40 hours): All chapters 00-15
- **Production Deployment** (~12 hours): Chapters 00-02, 10, 13-15
:::

### Part 0: Getting Started (Chapter 00)

Introduction to machine learning concepts and PHP-ML library.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-00-introduction-hero-thumbnail.webp" alt="Chapter 00 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/00-introduction-to-machine-learning">00 — Introduction to Machine Learning</a></h4>
    <p style="margin-bottom: 0;">Understand what machine learning is, explore supervised vs unsupervised learning, and see practical examples of ML in web applications. Learn the difference between classification, regression, and clustering. Build your first ML model in PHP.</p>
  </div>
</div>

### Part 1: Foundation (Chapters 01–02)

Build essential ML knowledge and set up your development environment.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-01-setup-hero-thumbnail.webp" alt="Chapter 01 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/01-setting-up-php-ml">01 — Setting Up PHP-ML and Your First Model</a></h4>
    <p style="margin-bottom: 0;">Install and configure PHP-ML using Composer. Understand the library structure, core concepts, and build your first complete ML pipeline. Learn dataset loading, model training, prediction, and evaluation.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-02-preprocessing-hero-thumbnail.webp" alt="Chapter 02 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/02-data-preprocessing">02 — Data Preprocessing and Feature Engineering</a></h4>
    <p style="margin-bottom: 0;">Master data cleaning, normalization, and standardization. Handle missing values with imputation strategies. Encode categorical variables using label encoding and one-hot encoding. Extract meaningful features from raw data.</p>
  </div>
</div>

### Part 2: Classification Algorithms (Chapters 03–06)

Master supervised learning algorithms for categorization problems.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-03-knn-hero-thumbnail.webp" alt="Chapter 03 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/03-k-nearest-neighbors">03 — Classification Fundamentals: k-Nearest Neighbors</a></h4>
    <p style="margin-bottom: 0;">Understand k-NN algorithm and distance metrics (Euclidean, Manhattan, Minkowski). Choose optimal k value and handle the curse of dimensionality. Build a spam email classifier and customer churn predictor.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-04-decision-trees-hero-thumbnail.webp" alt="Chapter 04 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/04-decision-trees-random-forests">04 — Decision Trees and Random Forests</a></h4>
    <p style="margin-bottom: 0;">Learn decision tree algorithms, information gain, and Gini impurity. Understand overfitting and pruning strategies. Build Random Forest ensembles for improved accuracy. Implement feature importance analysis.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-05-svm-hero-thumbnail.webp" alt="Chapter 05 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/05-support-vector-machines">05 — Support Vector Machines (SVM)</a></h4>
    <p style="margin-bottom: 0;">Master SVM for classification with linear and non-linear kernels. Understand the margin concept, support vectors, and kernel trick. Apply SVM to image classification and text categorization problems.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-06-naive-bayes-hero-thumbnail.webp" alt="Chapter 06 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/06-naive-bayes">06 — Naive Bayes Classifier</a></h4>
    <p style="margin-bottom: 0;">Learn Bayesian probability and conditional independence assumptions. Implement Gaussian and Multinomial Naive Bayes. Build spam filters, sentiment analyzers, and document classifiers with probabilistic models.</p>
  </div>
</div>

### Part 3: Regression and Clustering (Chapters 07–08)

Explore prediction and unsupervised learning techniques.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-07-regression-hero-thumbnail.webp" alt="Chapter 07 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/07-regression-algorithms">07 — Regression Algorithms: Linear and Beyond</a></h4>
    <p style="margin-bottom: 0;">Master linear regression, polynomial regression, and SVR (Support Vector Regression). Understand least squares method, gradient descent, and regularization. Build house price predictors and demand forecasting systems.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-08-clustering-hero-thumbnail.webp" alt="Chapter 08 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/08-clustering-algorithms">08 — Clustering Algorithms: k-Means and DBSCAN</a></h4>
    <p style="margin-bottom: 0;">Learn unsupervised learning with k-Means clustering, DBSCAN for density-based clustering, and Fuzzy C-Means for soft clustering. Implement customer segmentation, anomaly detection, and pattern discovery.</p>
  </div>
</div>

### Part 4: Neural Networks (Chapter 09)

Build intelligent systems with neural networks.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-09-neural-networks-hero-thumbnail.webp" alt="Chapter 09 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/09-neural-networks">09 — Neural Networks and Perceptron</a></h4>
    <p style="margin-bottom: 0;">Understand neurons, activation functions, and forward propagation. Learn backpropagation and gradient descent. Build Multilayer Perceptron (MLP) classifiers for complex pattern recognition and non-linear decision boundaries.</p>
  </div>
</div>

### Part 5: Model Evaluation (Chapter 10)

Ensure your models perform well and generalize effectively.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-10-evaluation-hero-thumbnail.webp" alt="Chapter 10 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/10-model-evaluation">10 — Model Evaluation and Cross-Validation</a></h4>
    <p style="margin-bottom: 0;">Master evaluation metrics: accuracy, precision, recall, F1-score, ROC-AUC. Understand confusion matrices and classification reports. Implement k-fold cross-validation, stratified sampling, and train/test splits for robust model assessment.</p>
  </div>
</div>

### Part 6: Advanced Techniques (Chapters 11–14)

Advanced ML methods for improved performance and insights.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-11-ensemble-hero-thumbnail.webp" alt="Chapter 11 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/11-ensemble-methods">11 — Ensemble Methods: Bagging and Boosting</a></h4>
    <p style="margin-bottom: 0;">Learn ensemble learning with Bootstrap Aggregating (Bagging), AdaBoost, and voting classifiers. Understand bias-variance tradeoff and model combination strategies. Build robust predictors that outperform single models.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-12-pca-hero-thumbnail.webp" alt="Chapter 12 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/12-dimensionality-reduction">12 — Dimensionality Reduction: PCA</a></h4>
    <p style="margin-bottom: 0;">Master Principal Component Analysis (PCA) for reducing feature dimensions while preserving variance. Understand eigenvalues, eigenvectors, and explained variance. Visualize high-dimensional data and improve model performance.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-13-feature-selection-hero-thumbnail.webp" alt="Chapter 13 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/13-feature-selection">13 — Feature Selection and Optimization</a></h4>
    <p style="margin-bottom: 0;">Learn feature selection with Variance Threshold, SelectKBest, and correlation analysis. Implement hyperparameter tuning with grid search and random search. Optimize model performance systematically.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-14-association-rules-hero-thumbnail.webp" alt="Chapter 14 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/14-association-rule-learning">14 — Association Rule Learning with Apriori</a></h4>
    <p style="margin-bottom: 0;">Discover patterns in transactional data with Apriori algorithm. Learn support, confidence, and lift metrics. Build market basket analysis, product recommendation systems, and cross-sell engines.</p>
  </div>
</div>

### Part 7: Real-World Applications (Chapter 15)

Apply ML to production PHP applications.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-ml/chapter-15-real-world-hero-thumbnail.webp" alt="Chapter 15 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-ml/chapters/15-real-world-applications">15 — Real-World Applications and Case Studies</a></h4>
    <p style="margin-bottom: 0;">Build complete ML-powered applications: e-commerce recommendation engine, fraud detection system, customer lifetime value predictor, content categorization tool. Learn model persistence, deployment strategies, and production optimization.</p>
  </div>
</div>

---

## Appendices

Quick reference materials to support your ML journey.

- **[Appendix A: ML Algorithms Cheat Sheet](/series/php-ml/appendices/a-algorithms-cheat-sheet/)** — Quick reference for when to use each algorithm
- **[Appendix B: PHP-ML API Reference](/series/php-ml/appendices/b-api-reference/)** — Complete guide to PHP-ML classes and methods
- **[Appendix C: Math Refresher](/series/php-ml/appendices/c-math-refresher/)** — Essential mathematics for machine learning
- **[Appendix D: Further Reading](/series/php-ml/appendices/d-further-reading/)** — Curated resources for continued learning

---

## Frequently Asked Questions

**Can I really do machine learning in PHP?**
Absolutely! While Python dominates the ML space, PHP-ML provides a robust library for implementing ML algorithms. It's perfect for PHP developers who want to add ML capabilities to existing applications without switching languages.

**Do I need advanced math skills?**
Basic understanding of algebra and statistics helps, but we'll explain mathematical concepts as needed. You don't need calculus or linear algebra expertise—we focus on practical implementation over theoretical proofs.

**How does PHP-ML compare to Python's scikit-learn?**
PHP-ML is inspired by scikit-learn and provides similar functionality for classification, regression, and clustering. While Python has more libraries and research tools, PHP-ML is production-ready for web applications.

**Will this help with deep learning?**
This series focuses on classical ML algorithms. For deep learning, you'd need specialized libraries (like TensorFlow or PyTorch), which are primarily Python-based. However, the foundational concepts you learn here apply to deep learning too.

**Can I deploy PHP-ML models in production?**
Yes! PHP-ML models can be serialized and deployed in production PHP applications. We cover deployment strategies, performance optimization, and integration with web frameworks in Chapter 15.

**What about large datasets?**
PHP-ML works well with small to medium datasets (thousands to hundreds of thousands of records). For massive datasets (millions+), consider using Python or specialized big data tools, then deploying predictions via API.

**Do I need the PHP Algorithms series first?**
Not required, but recommended. Understanding algorithm complexity and data structures helps you grasp ML concepts faster. If you're comfortable with arrays, loops, and basic algorithms, you're ready to start.

## Getting Help

**Stuck on something?** Here's where to get help:

- **Check the appendices first**:
  - [Appendix A: ML Algorithms Cheat Sheet](/series/php-ml/appendices/a-algorithms-cheat-sheet/)
  - [Appendix B: PHP-ML API Reference](/series/php-ml/appendices/b-api-reference/)
  - [Appendix C: Math Refresher](/series/php-ml/appendices/c-math-refresher/)
- **Review chapter troubleshooting sections** for common issues
- **PHP-ML Documentation**: [php-ml.readthedocs.io](https://php-ml.readthedocs.io/)
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)
- **Report issues**: [Open an issue](https://github.com/dalehurley/codewithphp/issues)

## Related Resources

### Machine Learning Resources

- **[Scikit-learn Documentation](https://scikit-learn.org/)**: Python ML library (inspiration for PHP-ML)
- **[Machine Learning Crash Course](https://developers.google.com/machine-learning/crash-course)**: Google's free ML course
- **[StatQuest YouTube](https://www.youtube.com/user/joshstarmer)**: Visual explanations of ML concepts
- **[Kaggle Learn](https://www.kaggle.com/learn)**: Free ML courses and datasets

### PHP Resources

- **[PHP-ML Documentation](https://php-ml.readthedocs.io/)**: Official library documentation
- **[PHP Manual](https://www.php.net/manual/en/)**: Official language reference
- **[Composer](https://getcomposer.org/)**: Dependency manager for PHP

### Books (Recommended Reading)

- **"Hands-On Machine Learning"** by Aurélien Géron — Practical ML with scikit-learn
- **"An Introduction to Statistical Learning"** by James et al. — Theory with R examples
- **"The Hundred-Page Machine Learning Book"** by Andriy Burkov — Concise ML overview

### Related Code with PHP Series

- **[PHP Algorithms](/series/php-algorithms/)** — Foundation for understanding algorithm complexity
- **[PHP Basics](/series/php-basics/)** — Master PHP fundamentals
- **[Build a CRM with Laravel 12](/series/build-crm-laravel-12/)** — Apply ML in Laravel applications

---

::: tip Ready to Start?
Begin your ML journey with [Chapter 00: Introduction to Machine Learning](/series/php-ml/chapters/00-introduction-to-machine-learning)!
:::

---

## Continue Your Learning

Master other aspects of modern PHP development:

**→ [PHP Algorithms](/series/php-algorithms/)** — Master algorithmic thinking and data structures
**→ [PHP Basics](/series/php-basics/)** — Master PHP fundamentals from scratch
**→ [Build a CRM with Laravel 12](/series/build-crm-laravel-12/)** — Production PHP applications

<style>
:root {
  --primary-teal: #0d9488;
  --primary-teal-dark: #0f766e;
  --ml-purple: #7c3aed;
  --ml-indigo: #4f46e5;
  --php-amber: #f59e0b;
  --php-orange: #ea580c;
  --neutral-gray: #64748b;
  --bg-light: #f8fafc;
}

/* Chapter card enhancements */
div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--ml-purple);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(124, 58, 237, 0.15);
  border-left-color: var(--ml-indigo);
}

/* Image styling */
div[style*="display: flex"] img[style*="width: 180px"] {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

div[style*="display: flex"]:hover img[style*="width: 180px"] {
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

/* Link styling */
div[style*="display: flex"] h4 a {
  color: var(--ml-purple);
  transition: color 0.2s ease;
}

div[style*="display: flex"] h4 a:hover {
  color: var(--ml-indigo);
}
</style>
