---
title: "16: Machine Learning Deep Dive with scikit-learn"
description: "Master scikit-learn's ML toolkit for classification, regression, clustering, and the complete model training workflow"
sidebar:
  label: "16: Machine Learning Deep Dive with scikit-learn"
  order: 16
  badge:
    text: Advanced
    variant: danger
---

![hero](/images/data-science-php-developers/chapter-16-hero-full.webp)

# Chapter 16: Machine Learning Deep Dive with scikit-learn

## Overview

In [Chapter 8](/series/data-science-php-developers/chapters/08-machine-learning-explained-for-php-developers), you learned the core concepts of Machine Learning (ML). You built basic logic for classification and clustering using PHP-ML. However, for large-scale production models, the industry standard is **scikit-learn** (sklearn).

Scikit-learn is a massive, highly optimized Python library built on top of NumPy and SciPy. It provides a consistent interface for hundreds of algorithms, making it easy to switch from a simple Logistic Regression to a complex Random Forest with just one line of code. For a PHP developer, scikit-learn is the "framework" (like Laravel or Symfony) that brings structure and professional tools to the experimental world of data science.

In this chapter, we will master the complete ML workflow: preparing data, selecting features, training models, tuning hyperparameters, and—most importantly—exporting those models for use in your PHP applications.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 15: Statistical Analysis](/series/data-science-php-developers/chapters/15-advanced-statistical-analysis-scipy-statsmodels)
- Python 3.10+ with `scikit-learn`, `pandas`, and `joblib` installed
- Basic understanding of classification vs. regression
- **Estimated Time**: ~2 hours

**Verify your setup:**

```bash
pip install scikit-learn pandas joblib
python3 -c "import sklearn; print(f'scikit-learn version: {sklearn.__version__}')"
```

## What You'll Build

By the end of this chapter, you will have created:

- **Customer Churn Predictor**: A classification model that identifies which subscribers are likely to cancel.
- **Housing Price Regressor**: A regression model that predicts property values based on features like location and size.
- **Model Evaluation Suite**: A script that calculates accuracy, precision, recall, and F1-score.
- **PHP-ML Bridge**: A production-ready pattern for serving Python models from a PHP environment.

## Objectives

- Master scikit-learn's **Estimator API** (`fit`, `predict`, `score`).
- Implement **Supervised Learning** for both classification and regression.
- Use **Train/Test Splitting** and **Cross-Validation** to prevent overfitting.
- Perform **Feature Scaling** and **One-Hot Encoding** using scikit-learn transformers.
- Serialize models using `joblib` for high-performance loading in PHP.

## Step 1: The Estimator API and Classification (~25 min)

### Goal

Understand scikit-learn's consistent API by building a "Churn Prediction" model.

### Why It Matters

In PHP-ML, every class has different method names. Scikit-learn uses the **Estimator API**—every model uses `fit()` to train and `predict()` to use. This consistency allows you to swap algorithms without rewriting your entire pipeline.

### Actions

**1. Create a Churn Prediction script:**

```python
# filename: examples/churn_classifier.py
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
import joblib

# 1. Load sample data (Synthetic Churn Data)
data = {
    'monthly_spend': [50, 100, 20, 80, 120, 30, 90, 45, 110, 60],
    'tenure_months': [12, 2, 24, 5, 1, 36, 8, 18, 3, 15],
    'support_calls': [1, 5, 0, 4, 6, 0, 3, 1, 5, 2],
    'churned': [0, 1, 0, 1, 1, 0, 1, 0, 1, 0] # 1 = Cancelled, 0 = Stayed
}
df = pd.DataFrame(data)

# 2. Features (X) and Target (y)
X = df.drop('churned', axis=1)
y = df['churned']

# 3. Train/Test Split (The "Golden Rule")
# PHP devs often test on the same data they train on. Don't do that!
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# 4. Initialize and Train the Model
model = RandomForestClassifier(n_estimators=100)
model.fit(X_train, y_train)

# 5. Predict and Evaluate
predictions = model.predict(X_test)
print(f"Model Accuracy: {accuracy_score(y_test, predictions):.2f}")
print("\nClassification Report:")
print(classification_report(y_test, predictions))

# 6. Save for PHP integration
joblib.dump(model, 'models/churn_model.pkl')
print("\nModel saved to models/churn_model.pkl")
```

**2. Run the script:**

```bash
python3 examples/churn_classifier.py
```

### Expected Result

```
Model Accuracy: 1.00

Classification Report:
              precision    recall  f1-score   support
           0       1.00      1.00      1.00         1
           1       1.00      1.00      1.00         1
```

### Why It Works

- **`train_test_split`**: Reserves 20% of the data to see how the model performs on "unseen" data. This is crucial for real-world reliability.
- **`RandomForestClassifier`**: An ensemble of Decision Trees. It's robust, handles non-linear relationships, and is a great starting point for most classification tasks.
- **`joblib`**: A faster alternative to Python's `pickle` for saving large NumPy-based models.

### Troubleshooting

**Problem: `ValueError: Found input variables with inconsistent numbers of samples`**

**Cause**: Your `X` and `y` have different row counts.

**Solution**: Ensure you didn't accidentally filter one but not the other. Check `X.shape` and `y.shape`.

## Step 2: Regression and Feature Scaling (~25 min)

### Goal

Build a model to predict a continuous value (Price) and learn why **Feature Scaling** is essential.

### Why It Matters

Some models (like SVR or KNN) are sensitive to the "scale" of data. If `price` is in thousands and `bedrooms` is a single digit, the model might ignore bedrooms. Scaling puts all features on the same footing (usually 0 to 1).

### Actions

**1. Create a Price Prediction script:**

```python
# filename: examples/price_regressor.py
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, r2_score

# 1. Setup data
data = {
    'sqft': [1500, 2000, 1200, 2500, 1800, 3000, 1400],
    'bedrooms': [3, 4, 2, 4, 3, 5, 2],
    'price': [350000, 450000, 280000, 550000, 400000, 650000, 310000]
}
df = pd.DataFrame(data)

X = df[['sqft', 'bedrooms']]
y = df['price']

# 2. Scaling (The PHP developer's forgotten step)
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

X_train, X_test, y_train, y_test = train_test_split(X_scaled, y, test_size=0.2, random_state=42)

# 3. Train
model = LinearRegression()
model.fit(X_train, y_train)

# 4. Evaluate
preds = model.predict(X_test)
print(f"Mean Absolute Error: ${mean_absolute_error(y_test, preds):,.2f}")
print(f"R-squared Score: {r2_score(y_test, preds):.4f}")
```

**2. Run the script:**

```bash
python3 examples/price_regressor.py
```

### Why It Works

- **`StandardScaler`**: Subtracts the mean and divides by the standard deviation. It ensures that "sqft" (large numbers) and "bedrooms" (small numbers) are treated with equal importance by the math.
- **Mean Absolute Error (MAE)**: Tells you how much, on average, your price predictions are off by. In our small example, it should be very low.

### Troubleshooting

**Problem: R-squared is negative**

**Cause**: Your model is performing worse than just guessing the average. This usually means the features have no relationship to the target or the model is too simple.

**Solution**: Try a non-linear model like `sklearn.svm.SVR` or `RandomForestRegressor`.

## Step 3: Pipelines and Hyperparameter Tuning (~25 min)

### Goal

Automate the workflow using **Pipelines** and find the best settings using **GridSearchCV**.

### Why It Matters

Manually scaling and then training is error-prone (you might forget to scale your test data). Pipelines bundle them together. Hyperparameter tuning finds the "magic numbers" (like the number of trees) that maximize accuracy.

### Actions

**1. Create an automated tuning script:**

```python
# filename: examples/model_tuning.py
from sklearn.pipeline import Pipeline
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import GridSearchCV
import pandas as pd

# Load data
df = pd.read_csv('https://raw.githubusercontent.com/datasciencedojo/datasets/master/titanic.csv')
# Simple cleaning
df = df[['Survived', 'Pclass', 'Age', 'Fare']].dropna()
X = df.drop('Survived', axis=1)
y = df['Survived']

# 1. Create a Pipeline
pipeline = Pipeline([
    ('scaler', StandardScaler()),
    ('clf', RandomForestClassifier(random_state=42))
])

# 2. Define Hyperparameters to test
param_grid = {
    'clf__n_estimators': [10, 50, 100],
    'clf__max_depth': [None, 5, 10],
    'clf__min_samples_split': [2, 5]
}

# 3. Grid Search (Exhaustive search over specified values)
grid = GridSearchCV(pipeline, param_grid, cv=5, scoring='accuracy')
grid.fit(X, y)

print(f"Best Parameters: {grid.best_params_}")
print(f"Best Cross-Validation Score: {grid.best_score_:.4f}")
```

### Why It Works

- **`Pipeline`**: Ensures that when you call `predict()`, the new data is automatically scaled using the exact parameters from the training set. This prevents "Data Leakage."
- **`cv=5` (Cross-Validation)**: Splits the data into 5 parts, training on 4 and testing on 1, five different times. This gives a much more honest assessment of accuracy than a single split.

## Step 4: Serving scikit-learn Models from PHP (~25 min)

### Goal

Learn the industry pattern for calling your complex Python models from a standard PHP/Laravel application.

### Actions

**1. Create the Python Prediction Script:**

```python
# filename: services/predict_churn.py
import sys
import json
import joblib
import pandas as pd

# 1. Load the pre-trained model
model = joblib.load('models/churn_model.pkl')

def main():
    # 2. Read input from PHP stdin
    input_json = sys.stdin.read()
    data = json.loads(input_json)

    # 3. Prepare for scikit-learn
    # Note: Must match the columns/order of training!
    df = pd.DataFrame([data])

    # 4. Predict
    prediction = int(model.predict(df)[0])
    probability = float(model.predict_proba(df)[0][1]) # Prob of class 1 (churn)

    # 5. Return JSON to PHP stdout
    result = {
        "churn_prediction": prediction,
        "probability": round(probability, 4),
        "status": "Will Churn" if prediction == 1 else "Likely to Stay"
    }
    print(json.dumps(result))

if __name__ == "__main__":
    main()
```

**2. Call it from PHP:**

```php
# filename: examples/php_ml_production.php
<?php
declare(strict_types=1);

// Data from a form or database
$userData = [
    'monthly_spend' => 120,
    'tenure_months' => 2,
    'support_calls' => 6
];

$process = proc_open('python3 services/predict_churn.py', [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
], $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], json_encode($userData));
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);

    $result = json_decode($output, true);

    echo "Churn Prediction Result:\n";
    echo "------------------------\n";
    echo "Status: " . $result['status'] . "\n";
    echo "Probability: " . ($result['probability'] * 100) . "%\n";
}
```

### Why It Works

This pattern is the **"De-facto Standard"** for smaller teams. It allows you to:

1. Use PHP for your web interface, Auth, and DB management.
2. Use Python for the heavy ML lifting.
3. Decouple your training (done once) from your inference (done on every request).

## Exercises

### Exercise 1: The Classifier Shootout

**Goal**: Compare two different algorithms on the same dataset.

**Requirements**:

1. Load the `iris` dataset (`from sklearn.datasets import load_iris`).
2. Train a `LogisticRegression` and a `DecisionTreeClassifier`.
3. Compare their accuracy on a 30% test split.
4. Which one performs better?

**Validation**: Print the accuracy of both models side-by-side.

### Exercise 2: Feature Engineering Challenge

**Goal**: Transform categorical data for scikit-learn.

**Requirements**:

1. Create a DataFrame with a "City" column (Berlin, London, Paris).
2. Scikit-learn cannot handle strings directly. Use `pd.get_dummies()` or `sklearn.preprocessing.OneHotEncoder` to turn these into numeric columns.
3. Train a `LinearRegression` model using these new "city" columns to predict `cost_of_living`.

**Validation**: Print the coefficients for each city.

### Exercise 3: Hyperparameter Search

**Goal**: Tune a `Support Vector Machine (SVM)`.

**Requirements**:

1. Use `sklearn.svm.SVC`.
2. Perform a `GridSearchCV` testing two different kernels: `'linear'` and `'rbf'`.
3. Test three values for `C`: `[0.1, 1, 10]`.
4. Report the best parameters.

## Wrap-up


### What You've Learned

In this chapter, you graduated to professional machine learning tools:

1. **Estimator API**: Understanding the universal `fit`/`predict` interface.
2. **Train/Test Splitting**: Protecting yourself from "overfitting" (models that memorize rather than learn).
3. **Preprocessing**: Using `StandardScaler` and `OneHotEncoder` to prepare raw data for mathematical models.
4. **Ensemble Methods**: Why `RandomForest` is often the "Swiss Army Knife" of classification.
5. **Model Evaluation**: Moving beyond accuracy to Precision, Recall, and F1-score for imbalanced datasets.
6. **Production Integration**: Mastering the `joblib` + `proc_open` bridge for PHP applications.

### What You've Built

1. **Churn Predictor**: A model that can save real business revenue.
2. **Price Estimator**: A regression tool for financial forecasting.
3. **ML Pipeline**: A repeatable workflow for cleaning and training.
4. **PHP-Python Bridge**: A scalable architecture for ML-powered web apps.

### Key ML Principles for PHP Developers

**1. Data Preparation is 80% of the Work**
The best algorithm cannot save a model built on unscaled, uncleaned, or leaky data.

**2. Accuracy is a Lie (Sometimes)**
If 99% of your users don't churn, a model that simply predicts "No Churn" every time will be 99% accurate but 0% useful. Look at the **Classification Report**!

**3. Simple Models First**
Start with `LogisticRegression` or `DecisionTree`. Only move to complex ensembles or Neural Networks if you have a significant performance gap.

**4. Version Your Models**
When you save a `.pkl` file, include a version or timestamp. Your PHP app needs to know exactly which version of the "brain" it is calling.

### Connection to Data Science Workflow

You have now completed the **Machine Learning** core of the series:

1. ✅ **Chapter 1-12**: Built data systems in PHP.
2. ✅ **Chapter 13-15**: Mastered Python and Statistics.
3. ✅ **Chapter 16**: **Mastered Predictive Modeling with scikit-learn** ← You are here
4. ➡️ **Chapter 17**: Moving into the world of **Deep Learning** with Neural Networks.

### Next Steps

**Immediate Practice**:

1. Take a dataset from your production PHP database (anonymized!) and try to predict a user behavior.
2. Explore [Kaggle Kernels](https://www.kaggle.com/code) to see how pros structure their scikit-learn pipelines.
3. Read the [scikit-learn algorithm cheat sheet](https://scikit-learn.org/stable/tutorial/machine_learning_map/index.html) to choose the right model for your next project.

**Chapter 17 Preview**:

In the next chapter, we enter the world of **Deep Learning with TensorFlow and Keras**. You'll learn:

- How Artificial Neural Networks mimic the human brain.
- Building Multi-layer Perceptrons for complex patterns.
- Working with unstructured data like Images and Text.
- Using **Transfer Learning** to leverage models built by Google and Facebook.

You're about to build models that can "see" and "read"!

## Further Reading

- [Scikit-learn Official Documentation](https://scikit-learn.org/stable/) — Impeccable reference and tutorials.
- [Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow](https://www.oreilly.com/library/view/hands-on-machine-learning/9781492032632/) — The industry-standard textbook.
- [Machine Learning Mastery](https://machinelearningmastery.com/) — Excellent practical tutorials for beginners.
- [Joblib Documentation](https://joblib.readthedocs.io/en/latest/) — High-performance model serialization.

::: tip Next Chapter
Continue to [Chapter 17: Deep Learning with TensorFlow and Keras](/series/data-science-php-developers/chapters/17-deep-learning-tensorflow-keras) to enter the world of neural networks!
:::
