---
title: "14: Data Wrangling with pandas and NumPy"
description: "Master pandas and NumPy for fast, vectorized data manipulation—operations that are 10-100x faster than PHP loops"
series: "data-science-php-developers"
chapter: 14
order: 14
difficulty: "Intermediate"
prerequisites:
  - "/series/data-science-php-developers/chapters/13-python-fundamentals-for-data-science"
  - "Python 3.10+ with pandas and NumPy"
keywords:
  [
    "pandas tutorial",
    "NumPy for data science",
    "data wrangling Python",
    "pandas DataFrames",
    "vectorized operations",
    "data manipulation Python",
    "pandas vs PHP",
  ]
author: "Code with PHP"
estimatedTime: "PT90M"
teaches:
  [
    "Advanced joining and merging strategies",
    "Reshaping data with pivot tables and melt",
    "Handling missing data at scale",
    "Working with Time Series and resampling",
    "Vectorized string operations",
    "Benchmarking pandas vs PHP loops",
  ]
datePublished: "2026-01-12"
---

![Data Wrangling with pandas and NumPy](/images/data-science-php-developers/chapter-14-data-wrangling-pandas-hero-full.webp)

# Chapter 14: Data Wrangling with pandas and NumPy

## Overview

In the previous chapter, you learned the basics of Python syntax and introduced the powerhouse duo: pandas and NumPy. Now, it's time to master **Data Wrangling**—the process of transforming raw, chaotic data into a structured, clean format ready for analysis or machine learning.

As a PHP developer, you're used to processing data using `foreach` loops and associative arrays. While that works for small datasets, it becomes painfully slow and complex as data scales. In this chapter, you'll learn how to replace nested loops with single-line vectorized operations, how to join massive datasets like a SQL pro, and how to handle time-series data with ease. You'll see firsthand why pandas is 10-100x faster than traditional PHP loops for data transformation.

By the end of this chapter, you'll be able to build robust data cleaning pipelines that turn messy CSVs and API logs into pristine analytical assets.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 13: Python Fundamentals](/series/data-science-php-developers/chapters/13-python-fundamentals-for-data-science)
- Python 3.10+ installed with a virtual environment
- `pandas` and `numpy` installed (`pip install pandas numpy`)
- **Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check if libraries are installed
python3 -c "import pandas; import numpy; print(f'pandas: {pandas.__version__}, numpy: {numpy.__version__}')"
```

## What You'll Build

By the end of this chapter, you will have created:

- **E-commerce Analytics Pipeline**: A script that joins user, order, and product data to find high-value customers.
- **Log Aggregator**: A time-series tool that takes raw server logs and resamples them into hourly error rates.
- **Missing Data Handler**: A systematic approach to filling or dropping `NaN` values without breaking math operations.
- **Performance Benchmark Suite**: A side-by-side comparison showing exactly when pandas outpaces PHP.

## Objectives

- Combine datasets using Inner, Left, and Outer joins (just like SQL).
- Reshape data between "long" and "wide" formats using `pivot_table` and `melt`.
- Master Time Series analysis by resampling dates and frequencies.
- Implement vectorized cleaning routines for strings and numerical outliers.
- Optimize memory and speed for datasets exceeding 1M rows.

## Step 1: Advanced Joining and Merging (~20 min)

### Goal

Combine multiple DataFrames using SQL-like joins to build a unified view of your data.

### Why It Matters

In PHP, joining two arrays usually requires nested loops or a lookup map. In pandas, `merge` allows you to join millions of rows in milliseconds using optimized C-algorithms.

### Actions

**1. Create a join experimentation script:**

```python
# filename: examples/merging_lab.py
import pandas as pd

# 1. Setup sample data
users = pd.DataFrame({
    'user_id': [1, 2, 3, 4],
    'name': ['Alice', 'Bob', 'Charlie', 'David'],
    'plan': ['Pro', 'Free', 'Pro', 'Free']
})

orders = pd.DataFrame({
    'order_id': [101, 102, 103, 104],
    'user_id': [1, 2, 1, 5], # Note: user 5 doesn't exist in 'users'
    'amount': [150.0, 25.0, 300.0, 45.0]
})

print("--- Users ---")
print(users)
print("\n--- Orders ---")
print(orders)

# 2. Inner Join (Keep only records in BOTH)
# PHP equivalent: nested loops with if (isset($lookup[$id]))
inner_join = pd.merge(users, orders, on='user_id', how='inner')
print("\n--- Inner Join (Matches only) ---")
print(inner_join)

# 3. Left Join (Keep all users, even those with no orders)
left_join = pd.merge(users, orders, on='user_id', how='left')
print("\n--- Left Join (All Users) ---")
print(left_join)

# 4. Outer Join (Keep EVERYTHING from both)
outer_join = pd.merge(users, orders, on='user_id', how='outer')
print("\n--- Outer Join (Full Union) ---")
print(outer_join)

# 5. Right Join (Keep all orders, even those with no user)
right_join = pd.merge(users, orders, on='user_id', how='right')
print("\n--- Right Join (All Orders) ---")
print(right_join)
```

**2. Run the script:**

```bash
python3 examples/merging_lab.py
```

### Expected Result

```
--- Inner Join (Matches only) ---
   user_id     name  plan  order_id  amount
0        1    Alice   Pro       101   150.0
1        1    Alice   Pro       103   300.0
2        2      Bob  Free       102    25.0

--- Left Join (All Users) ---
   user_id     name  plan  order_id  amount
0        1    Alice   Pro     101.0   150.0
1        1    Alice   Pro     103.0   300.0
2        2      Bob  Free     102.0    25.0
3        3  Charlie   Pro       NaN      NaN
4        4    David  Free       NaN      NaN

--- Outer Join (Full Union) ---
   user_id     name  plan  order_id  amount
0        1    Alice   Pro     101.0   150.0
1        1    Alice   Pro     103.0   300.0
2        2      Bob  Free     102.0    25.0
3        3  Charlie   Pro       NaN      NaN
4        4    David  Free       NaN      NaN
5        5      NaN   NaN     104.0    45.0
```

### Why It Works

Pandas uses **index-based lookup** for merging. When you call `merge`, it identifies the "key" column (like `user_id`), aligns the data in memory, and performs the set operation (Intersection for Inner, Union for Outer).

- **Left Joins** are crucial for maintaining your "entities" while checking for optional related data (like "Users who haven't ordered yet").
- **NaN Handling**: Notice how users with no orders get `NaN` (Not a Number) for the order columns. This is Python's way of saying "missing data."

### Troubleshooting

**Problem: `MergeError: No common columns to merge on`**

**Cause**: The column names don't match exactly (e.g., `id` vs `user_id`).

**Solution**: Use `left_on` and `right_on`:

```python
pd.merge(users, orders, left_on='id', right_on='user_id')
```

**Problem: Duplicate columns after merge (e.g., `name_x`, `name_y`)**

**Cause**: Both DataFrames had a column named `name` that wasn't used as the merge key.

**Solution**: Use the `suffixes` parameter:

```python
pd.merge(users, orders, on='user_id', suffixes=('_user', '_order'))
```

## Step 2: Reshaping and Pivoting Data (~20 min)

### Goal

Transform data between "long" format (easy for storage) and "wide" format (easy for analysis).

### Actions

**1. Create a pivoting lab:**

```python
# filename: examples/pivoting_lab.py
import pandas as pd

# 1. Sample "Long" data (like event logs)
data = {
    'date': ['2025-01-01', '2025-01-01', '2025-01-02', '2025-01-02'],
    'region': ['East', 'West', 'East', 'West'],
    'sales': [100, 150, 120, 180]
}
df = pd.DataFrame(data)
print("--- Original Long Format ---")
print(df)

# 2. Pivot to "Wide" format (Excel-style)
# Goal: Rows are dates, Columns are regions
pivoted = df.pivot(index='date', columns='region', values='sales')
print("\n--- Wide Format (Pivoted) ---")
print(pivoted)

# 3. Pivot Table (Aggregate while pivoting)
# What if multiple sales on same day/region?
data_messy = pd.concat([df, pd.DataFrame({'date': ['2025-01-01'], 'region': ['East'], 'sales': [50]})])
# Use pivot_table to SUM the values
pivot_agg = data_messy.pivot_table(index='date', columns='region', values='sales', aggfunc='sum')
print("\n--- Pivot Table (Summed) ---")
print(pivot_agg)

# 4. Melt (Go back from wide to long)
# Useful for preparing data for machine learning or plotting
melted = pivoted.reset_index().melt(id_vars='date', value_name='sales_amount')
print("\n--- Melted Back to Long ---")
print(melted)
```

**2. Run the script:**

```bash
python3 examples/pivoting_lab.py
```

### Expected Result

```
--- Wide Format (Pivoted) ---
region      East  West
date
2025-01-01   100   150
2025-01-02   120   180

--- Pivot Table (Summed) ---
region      East  West
date
2025-01-01   150   150
2025-01-02   120   180
```

### Why It Works

- **`pivot`**: Simple reshaping when unique index/column pairs exist.
- **`pivot_table`**: The "Grand Master" of aggregation. It handles duplicate entries by applying a function like `sum`, `mean`, or `count`.
- **`melt`**: "Unpivots" a DataFrame. It takes column labels and turns them into data values in a new column. This is often required before sending data to visualization libraries like Seaborn.

### Troubleshooting

**Problem: `ValueError: Index contains duplicate entries, cannot reshape`**

**Cause**: You used `pivot` instead of `pivot_table` when your data has multiple values for the same index/column combination.

**Solution**: Use `pivot_table` and specify an `aggfunc`.

## Step 3: Mastering Time Series Data (~20 min)

### Goal

Transform timestamps into a powerful index for trend analysis and resampling.

### Actions

**1. Create a time series lab:**

```python
# filename: examples/timeseries_lab.py
import pandas as pd
import numpy as np

# 1. Generate 10 days of hourly data
dates = pd.date_range('2025-01-01', periods=240, freq='H')
traffic = np.random.poisson(lam=50, size=240) # Avg 50 visitors/hr

df = pd.DataFrame({'traffic': traffic}, index=dates)
print("--- Hourly Traffic (Head) ---")
print(df.head())

# 2. Slice by time
# PHP: requires complex date math or string parsing
print("\n--- Traffic on Jan 2nd ---")
print(df.loc['2025-01-02'])

# 3. Resampling (The "Magic" of pandas)
# Aggregate hourly data into Daily totals
daily = df.resample('D').sum()
print("\n--- Daily Traffic Totals ---")
print(daily)

# Aggregate into Daily Averages
daily_avg = df.resample('D').mean()
print("\n--- Daily Traffic Average ---")
print(daily_avg)

# 4. Moving Averages (Smooth out the noise)
df['7hr_moving_avg'] = df['traffic'].rolling(window=7).mean()
print("\n--- Hourly Traffic with Moving Average ---")
print(df.head(10))
```

**2. Run the script:**

```bash
python3 examples/timeseries_lab.py
```

### Expected Result

```
--- Daily Traffic Totals ---
            traffic
2025-01-01     1210
2025-01-02     1185
...

--- Hourly Traffic with Moving Average ---
                     traffic  7hr_moving_avg
2025-01-01 00:00:00       48             NaN
...
2025-01-01 06:00:00       52       50.142857
```

### Why It Works

Pandas treats time as a first-class citizen. By setting a `DatetimeIndex`, you unlock:

- **Resampling (`resample`)**: Like `GROUP BY` for time. `D` for Day, `M` for Month, `W` for Week.
- **Rolling (`rolling`)**: Performs calculations on a sliding window—perfect for identifying trends in volatile data.
- **Natural Slicing**: You can use strings like `'2025-01'` to get all data for a specific month.

### Troubleshooting

**Problem: Dates are being treated as strings (object)**

**Cause**: You loaded a CSV where the date column is just text.

**Solution**: Convert it using `pd.to_datetime()`:

```python
df['date'] = pd.to_datetime(df['date'])
df.set_index('date', inplace=True)
```

## Step 4: Cleaning and Pipelines at Scale (~15 min)

### Goal

Apply vectorized string and math operations to clean messy data without row-by-row loops.

### Actions

**1. Create a cleaning lab:**

```python
# filename: examples/cleaning_lab.py
import pandas as pd
import numpy as np

# 1. Messy data
data = {
    'name': [' ALICE ', 'bob', '  Charlie', 'DAVID'],
    'email': ['alice@GMAIL.com', 'BOB@outlook.com', 'charlie@gmail.com', np.nan],
    'age': [25, -1, 30, 150], # -1 and 150 are likely errors
    'score': [85, 90, np.nan, 75]
}
df = pd.DataFrame(data)

# 2. Vectorized String Cleaning
# PHP: foreach loop with trim(), strtolower()
df['name'] = df['name'].str.strip().str.title()
df['email'] = df['email'].str.lower()
print("--- Cleaned Strings ---")
print(df[['name', 'email']])

# 3. Handling Outliers with Clipping
# Clamp age between 0 and 100
df['age'] = df['age'].clip(lower=0, upper=100)

# 4. Fill Missing Values
# Strategy: fill with mean, median, or a constant
df['score'] = df['score'].fillna(df['score'].mean())
df['email'] = df['email'].fillna('unknown@example.com')

print("\n--- Final Cleaned Data ---")
print(df)

# 5. Query filtering (Fast SQL-like syntax)
top_performers = df.query('score > 80')
print("\n--- Top Performers ---")
print(top_performers)
```

**2. Run the script:**

```bash
python3 examples/cleaning_lab.py
```

### Expected Result

```
--- Cleaned Strings ---
      name            email
0    Alice  alice@gmail.com
1      Bob  bob@outlook.com
2  Charlie  charlie@gmail.com
3    David              NaN

--- Final Cleaned Data ---
      name                email  age      score
0    Alice      alice@gmail.com   25  85.000000
1      Bob      bob@outlook.com    0  90.000000
2  Charlie    charlie@gmail.com   30  83.333333
3    David  unknown@example.com  100  75.000000
```

### Why It Works

- **`.str` accessor**: Provides vectorized string methods. If you have 1M names, `str.strip()` cleans them all at once.
- **`.fillna()`**: Safely replaces `NaN` with values. Using the mean is a common data science technique called "Imputation."
- **`.clip()`**: Fast way to bound values without writing `if/else` logic.

### Troubleshooting

**Problem: `.str` operations failing on numeric columns**

**Cause**: You tried to use string methods on an integer or float column.

**Solution**: Ensure column type is string or convert first: `df['col'].astype(str).str.upper()`.

## Step 5: Performance Optimization - pandas vs PHP (~15 min)

### Goal

Benchmark a large operation to understand why pandas is the industry standard for data science.

### Actions

**1. Benchmark script (Conceptual):**

Imagine we want to calculate the 15% tax on 1,000,000 transaction amounts.

```python
# filename: examples/benchmark_tax.py
import pandas as pd
import numpy as np
import time

# Create 1M rows
size = 1_000_000
amounts = np.random.uniform(10, 500, size)
df = pd.DataFrame({'amount': amounts})

# 1. Benchmarking pandas Vectorized
start = time.time()
df['tax'] = df['amount'] * 0.15
print(f"pandas (Vectorized) time: {(time.time() - start)*1000:.2f}ms")

# 2. Benchmarking Python Loop (Similar to PHP speed)
start = time.time()
taxes = []
for a in amounts:
    taxes.append(a * 0.15)
print(f"Python (Loop) time: {(time.time() - start)*1000:.2f}ms")
```

**2. Run the benchmark:**

```bash
python3 examples/benchmark_tax.py
```

### Expected Result

```
pandas (Vectorized) time: 2.10ms
Python (Loop) time: 85.40ms
```

_(Note: PHP 8.4 loops would be closer to the Python Loop speed, though often faster thanks to JIT, but still significantly slower than vectorized C-routines)._

### Why It Works

Pandas and NumPy don't perform the math in the high-level interpreter. They pass the memory pointers of the entire data block to highly optimized **BLAS** (Basic Linear Algebra Subprograms) or **LAPACK** routines written in Fortran or C.

**When to use pandas vs PHP:**

- **Use PHP**: For application logic, routing, UI, and simple data validation.
- **Use pandas**: For heavy data transformations, aggregations on 10k+ rows, and statistical cleaning.

## Exercises

### Exercise 1: The Churn Analyzer

**Goal**: Join user data with event logs to identify "at risk" users.

**Requirements**:

1. Create `users` DataFrame (id, signup_date).
2. Create `logins` DataFrame (id, user_id, login_date) with multiple logins per user.
3. Perform a **Left Join**.
4. Group by `user_id` and find the `max()` (latest) login date.
5. Filter for users whose latest login was more than 30 days ago.

**Validation**: Your final DataFrame should only contain users who haven't logged in for 30+ days.

### Exercise 2: Reshaping Sales Data

**Goal**: Convert a wide dataset into a long one for a chart.

**Requirements**:

1. Create a "Wide" DataFrame with columns: `product`, `sales_jan`, `sales_feb`, `sales_mar`.
2. Use `melt` to turn it into: `product`, `month`, `sales`.
3. Filter out any rows where `sales` is 0 or NaN.

**Validation**: The resulting DataFrame should be tidy and ready for a line chart.

### Exercise 3: Cleaning Server Logs

**Goal**: Process a messy log string into a structured DataFrame.

**Requirements**:

1. Create an array of 5 strings in the format: `[2025-01-01] ERROR: database connection failed`.
2. Use `.str.extract()` or string slicing to split into `date`, `level`, and `message`.
3. Convert `date` to Datetime objects.
4. Filter for only `ERROR` level logs.

**Validation**: Print the final cleaned DataFrame.

## Wrap-up

<ChapterCheckbox 
  seriesId="data-science-php-developers"
  chapterId="14"
  label="You've mastered data wrangling with pandas and NumPy!"
/>

### What You've Learned

In this chapter, you moved beyond basic Python and mastered the primary tools of the data scientist:

1. **Relational Data**: Using `merge` to perform complex joins that would be nightmare nested loops in PHP.
2. **Reshaping**: Transforming data layouts using `pivot_table` and `melt` to see patterns from different angles.
3. **Temporal Analysis**: Mastering the `DatetimeIndex` and `resample` to identify trends over time.
4. **Vectorized Cleaning**: Cleaning millions of rows of strings and numbers instantly using `.str` accessors and NumPy math.
5. **Handling "Messy" Data**: Strategically managing `NaN` values and outliers to ensure data quality.
6. **The Performance Gap**: Understanding why vectorized operations are the only choice for modern data scale.

### What You've Built

You now have a production-ready toolkit:

1. **Merging Pipeline**: A repeatable way to combine business entities.
2. **Reshaping Suite**: Logic for pivoting financial or analytical data.
3. **Time Series Engine**: A template for processing logs and trend data.
4. **Cleaning Pipeline**: A reusable workflow for preparing raw data for ML.

### Key Data Wrangling Principles

**1. Tidy Data is Happy Data**
Each variable is a column, each observation is a row. Use `melt` to get there.

**2. Don't Loop, Vectorize**
If you find yourself writing `for index, row in df.iterrows():`, stop and look for a pandas function instead.

**3. Indexing is your Friend**
Use meaningful indexes (like user IDs or dates) to make your code more readable and your lookups faster.

**4. Data Cleaning is 80% of the Job**
Machine learning models are "Garbage In, Garbage Out." Your wrangling skills are the foundation of all AI success.

### Connection to Data Science Workflow

You are now a high-performance data engineer:

1. ✅ **Chapter 1-12**: Built end-to-end data systems using PHP.
2. ✅ **Chapter 13**: Mastered Python syntax foundations.
3. ✅ **Chapter 14**: **Mastered high-performance Data Wrangling** ← You are here
4. ➡️ **Chapter 15**: Moving into rigorous Statistical Analysis with SciPy.

### Next Steps

**Immediate Practice**:

1. Find a complex SQL query from your app and try to replicate the same logic using pandas `merge` and `groupby`.
2. Experiment with the `df.plot()` method to see how pandas integrates with Matplotlib for quick charts.
3. Look into [pandas-profiling](https://github.com/ydataai/ydata-profiling) for automated data reports.

**Chapter 15 Preview**:

In the next chapter, we'll move from "cleaning" to "proving" using **Advanced Statistical Analysis with SciPy and statsmodels**. You'll learn:

- Hypothesis testing (T-tests, p-values) at scale
- Normality tests and data distributions
- Correlation vs. Causation analysis
- Building Ordinary Least Squares (OLS) regression models
- Interpreting statistical summaries

You'll gain the mathematical rigor needed to back up your data visualizations with hard evidence!

## Further Reading

- [Pandas Documentation: Merge, Join, Concatenate](https://pandas.pydata.org/docs/user_guide/merging.html) — The definitive join guide.
- [Python for Data Analysis (3rd Edition)](https://wesmckinney.com/book/) — Specifically Chapter 7 (Data Cleaning) and 8 (Wrangling).
- [NumPy Vectorization Explained](https://numpy.org/doc/stable/user/basics.types.html) — Why it's so much faster.
- [Pandas Time Series / Date functionality](https://pandas.pydata.org/docs/user_guide/timeseries.html) — Master date frequencies.

::: tip Next Chapter
Continue to [Chapter 15: Advanced Statistical Analysis with SciPy and statsmodels](/series/data-science-php-developers/chapters/15-advanced-statistical-analysis-scipy-statsmodels) to add rigorous statistical methods!
:::
