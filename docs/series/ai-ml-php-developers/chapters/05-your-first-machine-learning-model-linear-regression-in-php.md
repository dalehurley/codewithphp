---
title: "Your First Machine Learning Model: Linear Regression in PHP"
description: Introduces predictive modeling with a simple project. Explains linear regression and walks through implementing it from scratch in PHP, including calculating a best-fit line and testing the model on sample data.
series: ai-ml-php-developers
chapter: 05-your-first-machine-learning-model-linear-regression-in-php
order: 5
difficulty: beginner
prerequisites: [04-data-collection-and-preprocessing-in-php]
---

# Your First Machine Learning Model: Linear Regression in PHP

::: warning Chapter Under Construction
This chapter is currently being developed. Content, code examples, and exercises are being actively written and will be available soon. Check back for updates!
:::

Let's build a simple predictive model using linear regression to predict numeric outcomes.

## What is Linear Regression?

Linear regression finds the best-fit line through data points to predict a numeric value (e.g. house price).

## The Math (Simplified)

- **Equation:** $y = mx + b$
- $m$ is the slope, $b$ is the intercept

## Implementing Linear Regression in PHP

Walk through loading data, calculating $m$ and $b$, and making predictions.

```php
<?php
// Example: Fit a line to (x, y) data
$x = [1, 2, 3, 4, 5];
$y = [2, 4, 5, 4, 5];
// ...calculate slope and intercept...
?>
```

## Testing and Error Measurement

- Test your model on sample data
- Calculate mean squared error (MSE)

By the end, you’ll have built your first ML model in PHP!
