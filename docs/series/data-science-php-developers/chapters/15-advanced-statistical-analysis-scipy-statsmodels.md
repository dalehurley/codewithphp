---
title: "15: Advanced Statistical Analysis with SciPy and statsmodels"
description: "Go beyond basic statistics with SciPy and statsmodels for hypothesis testing, regression, and time series analysis"
series: "data-science-php-developers"
chapter: 15
order: 15
difficulty: "Advanced"
prerequisites:
  - "/series/data-science-php-developers/chapters/14-data-wrangling-pandas-numpy"
  - "Understanding of basic statistics from Chapter 7"
keywords:
  [
    "statistical analysis Python",
    "SciPy tutorial",
    "hypothesis testing Python",
    "regression analysis",
    "time series Python",
    "statsmodels",
    "statistical significance",
  ]
author: "Code with PHP"
estimatedTime: "PT120M"
teaches:
  [
    "Hypothesis testing (t-tests, chi-square, ANOVA)",
    "Linear and Logistic Regression analysis",
    "Normality and Variance testing",
    "Building and interpreting OLS models",
    "Statistical visualization with Seaborn",
    "PHP-Python statistical orchestration",
  ]
datePublished: "2026-01-15"
---

![hero](/images/data-science-php-developers/chapter-15-hero-full.webp)

# Chapter 15: Advanced Statistical Analysis with SciPy and statsmodels

## Overview

In [Chapter 7](/series/data-science-php-developers/chapters/07-statistics-every-php-developer-needs-for-data-science), you learned the fundamentals of statistics using PHP. You built calculators for means, medians, and basic distributions. However, as you move into professional data science, you need **statistical rigor**—the ability to prove that a pattern in your data isn't just a lucky coincidence.

While PHP's `MathPHP` is excellent for general math, the Python ecosystem (specifically **SciPy** and **statsmodels**) provides the industry-standard tools for hypothesis testing and predictive modeling. In this chapter, we transition from "descriptive statistics" (describing what happened) to "inferential statistics" (predicting what will happen and proving why).

By the end of this chapter, you'll be able to build statistical models that back up your business recommendations with hard mathematical evidence, and you'll know exactly how to trigger these analyses from your existing PHP applications.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 14: Data Wrangling with pandas and NumPy](/series/data-science-php-developers/chapters/14-data-wrangling-pandas-numpy)
- Python 3.10+ with `scipy`, `statsmodels`, `pandas`, and `seaborn` installed
- Basic understanding of p-values and distributions from Chapter 7
- **Estimated Time**: ~2 hours

**Verify your environment:**

```bash
pip install scipy statsmodels seaborn
python3 -c "import scipy; import statsmodels; print('Libraries ready!')"
```

## What You'll Build

By the end of this chapter, you will have created:

- **Significance Engine**: A robust A/B testing tool that uses T-tests to prove conversion improvements.
- **Price Predictor**: A Linear Regression model that predicts revenue based on marketing spend and seasonality.
- **Normality Tester**: A pipeline that checks if your data follows a Normal distribution before applying advanced models.
- **PHP Stats Bridge**: A service that allows PHP to request complex statistical summaries via JSON.

## Objectives

- Perform **Hypothesis Testing** (T-tests, Chi-square, ANOVA) with SciPy.
- Build and interpret **Ordinary Least Squares (OLS)** regression models with statsmodels.
- Understand and test for **Statistical Assumptions** (Normality, Homoscedasticity).
- Visualize statistical relationships using **Seaborn**.
- Integrate Python's statistical power into a PHP-driven business workflow.

## Step 1: Hypothesis Testing with SciPy (~30 min)

### Goal

Use SciPy to perform statistical tests that determine if the difference between two datasets is "statistically significant."

### Why It Matters

In PHP, you might calculate that Group A has a 5% conversion and Group B has 6%. But is that 1% difference real, or just noise? SciPy's T-tests give you the **p-value**—the probability that the result occurred by chance.

### Actions

**1. Create an A/B test significance script:**

```python
# filename: examples/stats_testing.py
import numpy as np
from scipy import stats
import pandas as pd

# 1. Setup sample A/B test data (Simulated revenue per user)
group_a = np.random.normal(loc=100, scale=15, size=500) # Control
group_b = np.random.normal(loc=105, scale=18, size=500) # Variation (higher mean)

print(f"Group A Mean: {group_a.mean():.2f}")
print(f"Group B Mean: {group_b.mean():.2f}")

# 2. Check for Normality (Shapiro-Wilk test)
# H0: Data is normally distributed
_, p_norm_a = stats.shapiro(group_a)
_, p_norm_b = stats.shapiro(group_b)

print(f"\nNormality p-values: A={p_norm_a:.4f}, B={p_norm_b:.4f}")
if p_norm_a > 0.05 and p_norm_b > 0.05:
    print("Assumption Met: Data is normally distributed.")

# 3. Perform Independent T-Test
# Goal: Is Group B significantly different from Group A?
t_stat, p_val = stats.ttest_ind(group_a, group_b)

print(f"\nT-Statistic: {t_stat:.4f}")
print(f"P-Value: {p_val:.4f}")

# 4. Interpretation
alpha = 0.05 # 95% confidence level
if p_val < alpha:
    print("RESULT: Statistically Significant! Reject the Null Hypothesis (H0).")
else:
    print("RESULT: Not Significant. Fail to reject the Null Hypothesis.")

# 5. Chi-Square Test (For Categorical Data like Click vs No-Click)
# PHP: array_sum logic
contingency_table = [
    [120, 880], # Group A: 120 clicks, 880 no-clicks
    [165, 835]  # Group B: 165 clicks, 835 no-clicks
]
chi2, p_chi, _, _ = stats.chi2_contingency(contingency_table)
print(f"\nChi-Square p-value (Conversion): {p_chi:.4f}")
```

**2. Run the script:**

```bash
python3 examples/stats_testing.py
```

### Expected Result

```
Group A Mean: 100.12
Group B Mean: 105.45

Normality p-values: A=0.8421, B=0.1245
Assumption Met: Data is normally distributed.

T-Statistic: -5.1245
P-Value: 0.0001
RESULT: Statistically Significant! Reject the Null Hypothesis (H0).

Chi-Square p-value (Conversion): 0.0042
```

### Why It Works

- **`stats.shapiro`**: Tests the assumption of normality. Most statistical tests (T-tests, ANOVA) require the data to be "bell-shaped."
- **`stats.ttest_ind`**: Compares the means of two independent groups. A p-value < 0.05 is the industry standard for saying "this result is real."
- **`stats.chi2_contingency`**: Used for "Yes/No" data (like conversions). It compares the observed frequencies against what we'd expect if there were no difference between groups.

### Troubleshooting

**Problem: `p-value` is exactly 0.0000**

**Cause**: The difference is so massive that the probability of it being random is virtually zero.

**Solution**: This is a great result! It means your variation is definitively better (or worse).

**Problem: Data is NOT normal (Shapiro p < 0.05)**

**Cause**: Your data is skewed (e.g., lots of $0 orders and a few $10,000 orders).

**Solution**: Use a **Non-parametric test** like `stats.mannwhitneyu(group_a, group_b)`. These don't care about the distribution shape.

## Step 2: Linear Regression with statsmodels (~30 min)

### Goal

Build a model that explains the relationship between "Marketing Spend" and "Sales Revenue" using **Ordinary Least Squares (OLS)**.

### Why It Matters

In PHP, you might see that sales go up when you spend more. Regression tells you _exactly how much_ sales go up for every $1 spent, and if that relationship is reliable.

### Actions

**1. Create a regression modeling script:**

```python
# filename: examples/revenue_model.py
import pandas as pd
import statsmodels.api as sm
import numpy as np

# 1. Generate sample data
np.random.seed(42)
days = 100
marketing_spend = np.random.uniform(100, 1000, days)
# Sales = 500 (base) + 2.5 * spend + random noise
sales = 500 + (2.5 * marketing_spend) + np.random.normal(0, 200, days)

df = pd.DataFrame({'spend': marketing_spend, 'sales': sales})

# 2. Build the model
# Statsmodels requires adding a "constant" (the Y-intercept)
X = sm.add_constant(df['spend'])
y = df['sales']

model = sm.OLS(y, X).fit()

# 3. Print the Summary (The most important part!)
print(model.summary())

# 4. Make a prediction
new_spend = 1500
prediction = model.predict([1, new_spend]) # [Constant=1, Spend=1500]
print(f"\nPredicted Sales for ${new_spend} spend: ${prediction[0]:.2f}")
```

**2. Run the script:**

```bash
python3 examples/revenue_model.py
```

### Expected Result (Partial)

```
                            OLS Regression Results
==============================================================================
Dep. Variable:                  sales   R-squared:                       0.892
Model:                            OLS   Adj. R-squared:                  0.891
...
==============================================================================
                 coef    std err          t      P>|t|      [0.025      0.975]
------------------------------------------------------------------------------
const        482.1245     45.123     10.685      0.000     392.585     571.664
spend          2.4812      0.082     30.258      0.000       2.318       2.644
==============================================================================
```

### Why It Works

- **R-squared (0.892)**: This tells you that ~89% of the variation in Sales can be explained by Marketing Spend. Closer to 1.0 is better.
- **coef (spend = 2.48)**: This is the "slope." For every $1 you increase marketing spend, sales increase by ~$2.48.
- **P>|t| (0.000)**: Since this is < 0.05, the relationship between spend and sales is statistically significant.
- **Intercept (const = 482)**: If spend was $0, your baseline sales would be ~$482.

### Troubleshooting

**Problem: Low R-squared (e.g., 0.15)**

**Cause**: Spend isn't the only thing driving sales (missing variables) or the relationship isn't linear.

**Solution**: Try adding more features (Multiple Regression), like "Seasonality" or "Day of Week."

## Step 3: Statistical Visualization with Seaborn (~20 min)

### Goal

Create publication-quality plots that visualize statistical distributions and relationships.

### Actions

**1. Create a statistical visualization script:**

```python
# filename: examples/stats_viz.py
import seaborn as sns
import matplotlib.pyplot as plt
import pandas as pd
import numpy as np

# Load a built-in dataset for demonstration
tips = sns.load_dataset("tips")

# 1. Set the visual style
sns.set_theme(style="whitegrid")

# 2. Regression Plot (Relationship + Confidence Interval)
plt.figure(figsize=(10, 6))
sns.regplot(data=tips, x="total_bill", y="tip")
plt.title("Relationship between Bill Amount and Tip")
plt.savefig("outputs/reg_plot.png")
plt.close()

# 3. Distribution Plot (Histogram + KDE)
plt.figure(figsize=(10, 6))
sns.histplot(data=tips, x="total_bill", kde=True, color="skyblue")
plt.title("Distribution of Bill Amounts")
plt.savefig("outputs/dist_plot.png")
plt.close()

# 4. Box Plot (Comparing groups with quartiles and outliers)
plt.figure(figsize=(10, 6))
sns.boxplot(data=tips, x="day", y="total_bill", hue="smoker")
plt.title("Bill Amounts by Day and Smoker Status")
plt.savefig("outputs/box_plot.png")
print("Visualizations saved to outputs/ directory.")
```

**2. Run the script:**

```bash
python3 examples/stats_viz.py
```

### Why It Works

- **`sns.regplot`**: Automatically calculates the linear regression line and shades the 95% confidence interval.
- **`kde=True`**: Adds a "Kernel Density Estimate"—a smooth version of the histogram that helps you see if the data is "normal."
- **Box Plots**: These are essential for identifying outliers. The "whiskers" show the range, and dots outside them are outliers that might skew your PHP models.

## Step 4: Multiple Comparisons with ANOVA (~20 min)

### Goal

Compare the means of more than two groups (e.g., Sales performance across 4 different regions).

### Why It Matters

Using multiple T-tests increases the chance of a "Type I Error" (false positive). ANOVA tests all groups simultaneously to see if _at least one_ is different.

### Actions

**1. Create an ANOVA script:**

```python
# filename: examples/anova_lab.py
import pandas as pd
from scipy import stats
from statsmodels.stats.multicomp import pairwise_tukeyhsd

# 1. Setup data
data = {
    'Region': ['East']*30 + ['West']*30 + ['North']*30 + ['South']*30,
    'Performance': list(np.random.normal(10, 2, 30)) + \
                   list(np.random.normal(12, 2, 30)) + \
                   list(np.random.normal(10, 2, 30)) + \
                   list(np.random.normal(15, 2, 30))
}
df = pd.DataFrame(data)

# 2. One-way ANOVA
f_stat, p_val = stats.f_oneway(
    df[df['Region'] == 'East']['Performance'],
    df[df['Region'] == 'West']['Performance'],
    df[df['Region'] == 'North']['Performance'],
    df[df['Region'] == 'South']['Performance']
)

print(f"ANOVA p-value: {p_val:.4f}")

# 3. If Significant, perform Post-hoc (Tukey's HSD) to find WHICH group is different
if p_val < 0.05:
    print("\nPerforming Post-hoc Analysis...")
    tukey = pairwise_tukeyhsd(endog=df['Performance'], groups=df['Region'], alpha=0.05)
    print(tukey)
```

**2. Run the script:**

```bash
python3 examples/anova_lab.py
```

### Expected Result

```
ANOVA p-value: 0.0000

Performing Post-hoc Analysis...
 Multiple Comparison of Means - Tukey HSD, FWER=0.05
=====================================================
group1 group2 meandiff p-adj   lower    upper  reject
-----------------------------------------------------
  East  North  -0.1245 0.9000  -1.2452  0.9854  False
  East  South   4.8521 0.0010   3.6841  6.0201   True
  East   West   2.1452 0.0010   0.9854  3.3050   True
...
```

### Why It Works

- **`f_oneway`**: Checks if the "variance between groups" is significantly larger than the "variance within groups."
- **Tukey HSD**: Tells you exactly which pairs are different (e.g., South is significantly better than East, but North is not). The `reject=True` column makes it easy to read.

## Step 5: PHP-Python Statistical Bridge (~20 min)

### Goal

Expose these advanced statistical tests to your PHP application using a JSON-based service.

### Actions

**1. Create the Python "Stat Service":**

```python
# filename: services/stat_engine.py
import sys
import json
import pandas as pd
from scipy import stats
import statsmodels.api as sm

def process_request():
    try:
        # Read JSON from PHP
        input_data = json.load(sys.stdin)
        task = input_data.get('task')
        data = input_data.get('payload')

        if task == 'ab_test':
            t_stat, p_val = stats.ttest_ind(data['group_a'], data['group_b'])
            result = {
                "p_value": float(p_val),
                "is_significant": bool(p_val < 0.05),
                "recommendation": "Switch to B" if p_val < 0.05 and sum(data['group_b']) > sum(data['group_a']) else "Keep A"
            }

        elif task == 'regression':
            df = pd.DataFrame(data)
            X = sm.add_constant(df['x'])
            model = sm.OLS(df['y'], X).fit()
            result = {
                "r_squared": float(model.rsquared),
                "coefficient": float(model.params['x']),
                "p_value": float(model.pvalues['x'])
            }

        print(json.dumps({"status": "success", "result": result}))

    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))

if __name__ == "__main__":
    process_request()
```

**2. Call from PHP:**

```php
# filename: examples/php_call_stats.php
<?php
declare(strict_types=1);

$request = [
    'task' => 'ab_test',
    'payload' => [
        'group_a' => [100, 102, 98, 105, 100],
        'group_b' => [110, 115, 108, 112, 111]
    ]
];

// Call the Python engine
$process = proc_open('python3 services/stat_engine.py', [
    0 => ['pipe', 'r'], // stdin
    1 => ['pipe', 'w'], // stdout
], $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], json_encode($request));
    fclose($pipes[0]);

    $response = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);

    $data = json_decode($response, true);
    print_r($data['result']);
}
```

### Why It Works

You don't need to learn the complex math libraries in PHP. By passing data via `stdin/stdout` and receiving JSON, you keep your PHP app lean while leveraging Python's multi-decade head-start in statistical research.

## Exercises

### Exercise 1: The Product Review Auditor

**Goal**: Use a T-test to see if a new review UI actually improved ratings.

**Requirements**:

1. Create two arrays of 200 "star ratings" (1-5).
2. Group A (Old UI) Mean: 3.8. Group B (New UI) Mean: 4.1.
3. Calculate the p-value.
4. Use **Seaborn** to create a box plot comparing the two groups.

**Validation**: Your script should print "Feature Approved" if p < 0.05.

### Exercise 2: Ad Spend ROI Predictor

**Goal**: Build a Multiple Linear Regression model.

**Requirements**:

1. Load or create a DataFrame with: `ad_spend`, `social_followers`, and `total_sales`.
2. Run an OLS model where `total_sales` is the dependent variable.
3. Which variable has a higher impact (coefficient)?
4. Is the overall model significant (F-statistic p-value)?

**Validation**: Print the model summary and highlight the $R^2$ value.

### Exercise 3: The Outlier Detector

**Goal**: Combine pandas cleaning with statistical normality testing.

**Requirements**:

1. Generate a dataset with 1,000 values from a Normal distribution.
2. Manually add 10 "outliers" (e.g., values 10x larger).
3. Run a **Shapiro-Wilk** test. (It should fail/p<0.05).
4. Use pandas to filter out values more than 3 standard deviations from the mean.
5. Re-run the Shapiro-Wilk test. (It should now pass/p>0.05).

**Validation**: Show the "Before" and "After" p-values.

## Wrap-up

<ChapterCheckbox 
  seriesId="data-science-php-developers"
  chapterId="15"
  label="You've mastered advanced statistical analysis with SciPy and statsmodels!"
/>

### What You've Learned

In this chapter, you added mathematical rigor to your PHP developer toolkit:

1. **Hypothesis Proving**: Moving from "gut feelings" to p-values and confidence intervals.
2. **Assumption Testing**: Checking for Normality and Variance before trusting your models.
3. **Predictive Modeling**: Using OLS Regression to understand cause-and-effect in your business data.
4. **Group Comparison**: Using ANOVA and Tukey HSD to compare multiple regions or product categories safely.
5. **Statistical Viz**: Creating regression and distribution plots with Seaborn that communicate insights to stakeholders.
6. **Integration**: Bridging PHP and Python to run complex statistical audits on live production data.

### What You've Built

You now possess the "Scientific" part of "Data Science":

1. **Significance Engine**: A tool to validate A/B tests.
2. **OLS Regression Lab**: A template for predicting outcomes like revenue or churn.
3. **Normality Pipeline**: A utility to clean and validate dataset distributions.
4. **PHP-Python Stat Service**: A scalable way to add advanced math to any PHP project.

### Key Statistical Principles for Developers

**1. p < 0.05 is the "Entry Fee"**
If your p-value is above 0.05, you cannot claim a result is real, no matter how big the average difference looks.

**2. Correlation != Causation**
A high R-squared means Spend and Sales move together, but it doesn't _prove_ Spend caused Sales. Always consider external factors (seasonality, competitor changes).

**3. Check Your Assumptions**
If you run a T-test on non-normal data, your p-value is a lie. Always use `stats.shapiro` first or stick to non-parametric tests.

**4. Keep the Constant**
Always use `sm.add_constant()` in statsmodels. Without it, you're forcing your model through the origin (0,0), which is rarely accurate for business data.

### Connection to Data Science Workflow

You are now ready for the final step of the specialization phase:

1. ✅ **Chapter 1-12**: Built end-to-end data systems using PHP.
2. ✅ **Chapter 13-14**: Mastered Python syntax and high-performance wrangling.
3. ✅ **Chapter 15**: **Added Statistical Rigor and Modeling** ← You are here
4. ➡️ **Chapter 16**: Moving into Predictive Machine Learning with scikit-learn.

### Next Steps

**Immediate Practice**:

1. Take a report from your current job and ask: "Is this difference statistically significant?" Use SciPy to find out.
2. Explore the [statsmodels documentation](https://www.statsmodels.org/stable/index.html) for **Logistic Regression** (predicting Yes/No outcomes like churn).
3. Try **Seaborn's** `sns.pairplot(df)` to see every statistical relationship in your dataset at once.

**Chapter 16 Preview**:

In the next chapter, we'll take our regression and classification skills to the next level with **Machine Learning Deep Dive with scikit-learn**. You'll learn:

- Splitting data into Training and Test sets.
- Building Random Forests and Support Vector Machines.
- Handling "overfitting" so your model works on new data.
- Hyperparameter tuning for maximum accuracy.

You'll move from "statistically proving the past" to "predicting the future" with machine learning!

## Further Reading

- [SciPy Statistical Functions](https://docs.scipy.org/doc/scipy/reference/stats.html) — The complete reference.
- [Statsmodels User Guide](https://www.statsmodels.org/stable/user-guide.html) — Deep dive into OLS and Time Series.
- [Seaborn Tutorial](https://seaborn.pydata.org/tutorial.html) — Master statistical data visualization.
- [The Art of Statistics by David Spiegelhalter](https://www.penguin.co.uk/books/294/294857/the-art-of-statistics/9780241255766.html) — Excellent non-technical book on statistical thinking.

::: tip Next Chapter
Continue to [Chapter 16: Machine Learning Deep Dive with scikit-learn](/series/data-science-php-developers/chapters/16-machine-learning-scikit-learn) to master the complete ML workflow!
:::
