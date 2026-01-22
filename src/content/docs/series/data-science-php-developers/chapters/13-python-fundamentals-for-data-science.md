---
title: "13: Python Fundamentals for Data Science"
description: "Learn Python basics for data science work, coming from PHP."
sidebar:
  label: "13: Python Fundamentals"
  order: 13
---

### Syntax Comparison

Python and PHP share many concepts but have different "flavors." The biggest difference is Python's use of **indentation** instead of curly braces `{}`.

| Feature           | PHP                       | Python                             |
| ----------------- | ------------------------- | ---------------------------------- |
| **Variables**     | `$x = 10;`                | `x = 10`                           |
| **Constants**     | `define('PI', 3.14);`     | `PI = 3.14` (convention)           |
| **Arrays**        | `$arr = [1, 2, 3];`       | `arr = [1, 2, 3]` (List)           |
| **Assoc Arrays**  | `['a' => 1]`              | `{'a': 1}` (Dictionary)            |
| **Comments**      | `//` or `/* */`           | `#` or `""" """`                   |
| **Concatenation** | `"Hi " . $name`           | `"Hi " + name` or `f"Hi {name}"`   |
| **Loops**         | `foreach ($arr as $v) {}` | `for v in arr:`                    |
| **Functions**     | `function name() {}`      | `def name():`                      |
| **Class Prop**    | `public string $name;`    | `self.name = name` (in `__init__`) |
| **Null/None**     | `null`                    | `None`                             |
| **Boolean**       | `true / false`            | `True / False` (Capitalized!)      |

### Actions

**1. Create a Python/PHP comparison script:**

```python
# filename: examples/python_vs_php.py

# 1. Variables and Data Types
name = "PHP Developer"
age = 30
is_ready = True
price = 19.99

# 2. Lists (PHP Indexed Arrays)
fruits = ["apple", "banana", "cherry"]
fruits.append("date")  # $fruits[] = 'date';
print(f"Second fruit: {fruits[1]}") # Indices start at 0

# 3. Dictionaries (PHP Associative Arrays)
user = {
    "name": "Dale",
    "role": "Developer",
    "skills": ["PHP", "SQL", "Python"]
}
print(f"User Name: {user['name']}")

# 4. Control Structures (Note the indentation!)
if age >= 18:
    print(f"{name} is an adult")  # f-string like "{$name}"
elif age > 13:
    print(f"{name} is a teen")    # PHP: elseif
else:
    print(f"{name} is a minor")

# 5. Loops
print("\nLooping through fruits with index:")
for i, fruit in enumerate(fruits): # PHP: foreach($fruits as $i => $fruit)
    print(f"  {i}: {fruit}")

# 6. List Comprehensions (Powerful Python idiom!)
# PHP: $squared = array_map(fn($x) => $x * $x, [1, 2, 3, 4, 5]);
numbers = [1, 2, 3, 4, 5]
squared = [x * x for x in numbers]
print(f"\nSquared numbers: {squared}")

# Filtering with list comprehensions
# PHP: $evens = array_filter($numbers, fn($x) => $x % 2 == 0);
evens = [x for x in numbers if x % 2 == 0]
print(f"Even numbers: {evens}")

# 7. Functions and Type Hinting (Modern Python)
def calculate_total(price: float, tax_rate: float = 0.05) -> float:
    """Calculates total with tax (Python uses -> for return types)"""
    return price * (1 + tax_rate)

final_price = calculate_total(100.0, 0.2)
print(f"Final price: ${final_price:.2f}")

# 8. Classes (Briefly)
class DataAnalyzer:
    def __init__(self, data_source): # Constructor (__construct)
        self.data_source = data_source
        self.is_processed = False

    def process(self):
        print(f"Processing data from {self.data_source}...")
        self.is_processed = True

analyzer = DataAnalyzer("sales.csv")
analyzer.process()
```

**2. Run the script:**

```bash
python3 examples/python_vs_php.py
```

### Expected Result

```
Second fruit: banana
User Name: Dale
PHP Developer is an adult

Looping through fruits with index:
  0: apple
  1: banana
  2: cherry
  3: date

Squared numbers: [1, 4, 9, 16, 25]
Even numbers: [2, 4]
Final price: $120.00
Processing data from sales.csv...
```

### Why It Works

Python is **dynamically typed** like PHP but more strictly enforced.

- **Self instead of $this**: In class methods, you must explicitly pass `self` as the first parameter. It functions exactly like PHP's `$this`.
- **Dunder Methods**: Magic methods like `__init__` (constructor) or `__str__` (tostring) are prefixed/suffixed with double underscores ("Double UNDERscore").
- **Enumerate**: Python's `for` loop doesn't naturally give you the index like PHP's `foreach($arr as $k => $v)`. You use `enumerate()` to get both.
- **List Comprehensions**: These are the "soul" of idiomatic Python. They are faster than `for` loops and more readable than `array_map/filter` once you learn the syntax.

### Troubleshooting

**Problem: `IndentationError: expected an indented block`**

**Cause**: Python is missing the 4-space indentation after a colon `:`, or you mixed tabs and spaces.

**Solution**: Ensure consistent 4-space indentation. Most editors (including Cursor) do this automatically.

**Problem: `TypeError: can only concatenate str (not "int") to str`**

**Cause**: PHP converts types automatically (`"Age: " . 30` works). Python does not.

**Solution**: Use f-strings `f"Age: {age}"` or explicit conversion `str(age)`.

**Problem: `NameError: name 'x' is not defined`**

**Cause**: Using a variable before assigning it. Python doesn't have "undefined" variables that default to null/warning.

**Solution**: Initialize your variables.

## Step 2: Mastering NumPy Arrays (~20 min)

### Goal

Use NumPy for numerical operations that are significantly faster and more concise than PHP array loops.

### What is NumPy?

**NumPy** (Numerical Python) is the foundation of data science in Python. While PHP arrays are flexible (allowing mixed types) but slow for large math operations, NumPy arrays are:

- **Vectorized**: Perform math on entire arrays at once (no loops!)
- **Fixed-Type**: Highly optimized memory usage (C-backend)
- **Multi-Dimensional**: Easy matrix operations

### Actions

**1. Create a NumPy experimentation script:**

```python
# filename: examples/numpy_lab.py
import numpy as np
import time

# 1. Creating Arrays
# PHP: $arr = range(1, 5);
arr = np.array([1, 2, 3, 4, 5])
print(f"NumPy Array: {arr}")
print(f"Data type: {arr.dtype}") # Int64 or similar

# 2. Vectorized Operations (The "Magic")
# PHP: $multiplied = array_map(fn($x) => $x * 10, $arr);
multiplied = arr * 10
print(f"Multiplied (* 10): {multiplied}")

added = arr + 5
print(f"Added (+ 5): {added}")

# 3. Indexing and Slicing (Powerful!)
# PHP: array_slice($arr, 1, 2);
print(f"\nFirst 3 elements: {arr[:3]}") # [1 2 3]
print(f"Last 2 elements: {arr[-2:]}") # [4 5]
print(f"Elements from index 1 to 3: {arr[1:4]}") # [2 3 4]

# 4. Multi-Dimensional Arrays (Matrices)
matrix = np.array([
    [1, 2, 3],
    [4, 5, 6]
])
print(f"\nMatrix:\n{matrix}")
print(f"Matrix Shape: {matrix.shape}") # (2, 3)
print(f"Sum of columns: {matrix.sum(axis=0)}") # [5 7 9]
print(f"Mean of matrix: {matrix.mean()}")

# 5. Masking (Conditional selection)
# PHP: array_filter logic
mask = arr > 3
print(f"\nMask: {mask}") # [False False False  True  True]
print(f"Values > 3: {arr[mask]}") # [4 5]

# 6. Performance Comparison: PHP-style Loop vs NumPy
size = 1_000_000
data = list(range(size)) # Standard Python list (like PHP array)
np_data = np.array(data) # NumPy array

# Standard loop speed
start = time.time()
loop_result = [x * 2 for x in data]
print(f"\nStandard Loop time: {(time.time() - start)*1000:.2f}ms")

# NumPy vectorized speed
start = time.time()
np_result = np_data * 2
print(f"NumPy Vectorized time: {(time.time() - start)*1000:.2f}ms")
```

**2. Run the script:**

```bash
python3 examples/numpy_lab.py
```

### Expected Result

```
NumPy Array: [1 2 3 4 5]
Multiplied (* 10): [10 20 30 40 50]
Added (+ 5): [6 7 8 9 10]

First 3 elements: [1 2 3]
Last 2 elements: [4 5]
Elements from index 1 to 3: [2 3 4]

Matrix:
[[1 2 3]
 [4 5 6]]
Matrix Shape: (2, 3)
Sum of columns: [5 7 9]
Mean of matrix: 3.5

Mask: [False False False  True  True]
Values > 3: [4 5]

Standard Loop time: 45.20ms
NumPy Vectorized time: 1.15ms
```

### Why It Works

NumPy uses **vectorization**. Instead of iterating over elements one by one in the Python interpreter (slow), it passes the entire operation to optimized C/C++ routines (fast).

**Key Concepts:**

- **Slicing**: `arr[start:stop:step]` syntax allows complex subsetting without `array_slice`.
- **Masking**: You can use a boolean array to index another array. This is the basis for advanced data filtering in Python.
- **Axes**: In multi-dimensional arrays, `axis=0` refers to columns and `axis=1` refers to rows (usually).

### Troubleshooting

**Problem: `ValueError: operands could not be broadcast together`**

**Cause**: You're trying to add or multiply two arrays with incompatible shapes (e.g., a 2x2 matrix and a 3x3 matrix).

**Solution**: Check shapes with `arr.shape`. NumPy can "broadcast" a smaller array across a larger one if certain dimensions match.

**Problem: My math results are integers instead of floats**

**Cause**: NumPy infers the type from your input. If you provide all integers, it uses an integer array.

**Solution**: Specify the type with `dtype=float` when creating the array, or use a decimal point (e.g., `np.array([1., 2.])`).

## Step 3: Tabular Data with pandas (~20 min)

### Goal

Use pandas DataFrames to manipulate tabular data—think associative arrays on steroids with SQL-like powers.

### What is pandas?

**pandas** is the "Excel for Python." It introduces the **DataFrame**, a two-dimensional labeled data structure with columns of potentially different types.

- **PHP Equivalent**: An array of associative arrays (like PDO results).
- **Pandas Strength**: Built-in filtering, grouping, merging, and time-series handling.

### Actions

**1. Create a pandas data processing script:**

```python
# filename: examples/pandas_lab.py
import pandas as pd
import numpy as np

# 1. Creating a DataFrame from scratch
# PHP: $data = [['name' => 'A', 'sales' => 100], ...]
data = {
    'product': ['Laptop', 'Mouse', 'Monitor', 'Keyboard', 'Webcam', 'Microphone'],
    'category': ['Electronics', 'Peripherals', 'Electronics', 'Peripherals', 'Peripherals', 'Audio'],
    'sales': [1200, 25, 300, 75, 150, np.nan], # Missing value!
    'stock': [15, 100, 8, 40, 25, 10]
}

df = pd.DataFrame(data)

# 2. Handling Missing Data (Crucial!)
# PHP devs often forget to check for nulls in math
print("--- Check for Nulls ---")
print(df.isnull().sum())

# Fill missing sales with the mean
df['sales'] = df['sales'].fillna(df['sales'].mean())
print("\n--- DataFrame after filling NaNs ---")
print(df)

# 3. Advanced Filtering (Like SQL WHERE)
# PHP: complex array_filter
high_value_peripherals = df[(df['category'] == 'Peripherals') & (df['sales'] > 50)]
print("\n--- High Value Peripherals ---")
print(high_value_peripherals)

# 4. Feature Engineering
df['revenue'] = df['sales'] * 0.8
df['status'] = np.where(df['stock'] < 20, 'LOW', 'OK')
print("\n--- Feature Engineering Result ---")
print(df[['product', 'stock', 'status']])

# 5. Grouping and Aggregation (Powerful!)
# PHP: usually requires nested loops and array_reduce
summary = df.groupby('category').agg({
    'sales': ['sum', 'mean'],
    'stock': 'min'
})
print("\n--- Category Summary ---")
print(summary)

# 6. Merging DataFrames (Like SQL JOIN)
inventory_data = pd.DataFrame({
    'product': ['Laptop', 'Mouse', 'Monitor'],
    'supplier': ['Dell', 'Logitech', 'Samsung']
})

# Left Join
merged_df = df.merge(inventory_data, on='product', how='left')
print("\n--- Merged (Joined) Data ---")
print(merged_df[['product', 'category', 'supplier']])
```

**2. Run the script:**

```bash
python3 examples/pandas_lab.py
```

### Expected Result

```
--- Check for Nulls ---
product     0
category    0
sales       1
stock       0

--- DataFrame after filling NaNs ---
      product     category   sales  stock
0      Laptop  Electronics  1200.0     15
...
5  Microphone        Audio   350.0     10

--- High Value Peripherals ---
    product     category  sales  stock
3  Keyboard  Peripherals   75.0     40
4    Webcam  Peripherals  150.0     25

--- Category Summary ---
              sales         stock
                sum   mean    min
category
Audio         350.0  350.0     10
Electronics  1500.0  750.0      8
Peripherals   250.0   83.3     25
```

### Why It Works

Pandas DataFrames are built on NumPy but optimized for heterogeneous data (different types per column).

**Key Skills for PHP Developers:**

- **NaN (Not a Number)**: Python uses `np.nan` for missing numerical data. Pandas has built-in tools (`fillna`, `dropna`) to handle this, which is much safer than PHP's `null` behavior in math.
- **Aggregation**: The `.groupby().agg()` pattern replaces dozens of lines of PHP loops. You can calculate sums, counts, and averages for multiple columns in one go.
- **Merging**: `df.merge()` is exactly like a SQL JOIN. You don't need to manually map IDs between two arrays.

### Troubleshooting

**Problem: `SettingWithCopyWarning`**

**Cause**: You're trying to modify a slice of a DataFrame (a "view") instead of the original.

**Solution**: Use `.copy()` when creating a subset, or use `.loc[row_indexer, col_indexer]` to modify data explicitly.

**Problem: Performance is slow on a loop**

**Cause**: You used `.iterrows()`.

**Solution**: Use **Vectorized Methods**. Almost any row-wise operation can be done with pandas/NumPy functions (e.g., `df['a'] + df['b']`).

## Step 4: Interactive Data Science with Jupyter (~15 min)

### Goal

Set up and use Jupyter Notebooks for interactive data exploration and visualization.

### Actions

**1. Start Jupyter Lab:**

```bash
jupyter lab
```

**2. Create a Visual Analysis Notebook:**
In a new cell, perform basic visualization using **Matplotlib**:

```python
import matplotlib.pyplot as plt
import pandas as pd
import numpy as np

# Create data
categories = ['Organic', 'Paid', 'Social', 'Referral']
visitors = [4500, 2300, 1800, 1200]

# 1. Bar Chart
plt.figure(figsize=(10, 6))
plt.bar(categories, visitors, color=['skyblue', 'orange', 'green', 'red'])
plt.title('Website Visitors by Source')
plt.ylabel('Number of Visitors')
plt.grid(axis='y', linestyle='--', alpha=0.7)
plt.show()

# 2. Scatter Plot with NumPy
x = np.random.randn(100)
y = x + np.random.randn(100) * 0.5

plt.scatter(x, y, alpha=0.5)
plt.title('Correlation Analysis')
plt.show()
```

**3. Interactive Dataframes:**
Type `df` (the name of your dataframe) in a cell and run it. Jupyter renders it as a clean, sortable HTML table automatically.

### Why It Works

Jupyter is the "IDE" for data science. It allows you to:

- **Visualize Instantly**: No need to refresh a browser or generate image files.
- **Document as you go**: Use Markdown cells to explain your methodology to stakeholders.
- **Save State**: Unlike a PHP script that restarts from zero every run, Jupyter keeps your variables in memory. You can spend 10 minutes loading a massive CSV, then spend hours analyzing it without reloading.

### Troubleshooting

**Problem: Plot doesn't show up**

**Cause**: Missing `%matplotlib inline` (for older versions) or `plt.show()`.

**Solution**: Use `plt.show()` at the end of your plotting code.

**Problem: Jupyter can't find my virtual environment**

**Cause**: Jupyter is running from your global Python install instead of your venv.

**Solution**: Install the "IPyKernel" in your venv:

```bash
pip install ipykernel
python -m ipykernel install --user --name=my-data-env
```

Then select "my-data-env" in the Jupyter "Kernel" menu.

## Step 5: Hybrid Orchestration — Connecting PHP and Python (~15 min)

### Goal

Learn the two primary ways to call your Python data analysis code from a PHP application.

### Why Orchestrate?

You shouldn't rewrite your entire PHP app in Python. Instead, keep your **Business Logic in PHP** and your **Heavy Lifting in Python**.

### Actions

**1. Strategy A: Command Line Integration (Simple)**
Use PHP's `proc_open` to pass data to a Python script and receive JSON back.

```php
# filename: examples/php_orchestrator.php
<?php
declare(strict_types=1);

$data = [10, 20, 30, 40, 50];
$json_input = json_encode($data);

// Call Python script
$process = proc_open('python3 examples/process_data.py', [
    0 => ['pipe', 'r'], // stdin
    1 => ['pipe', 'w'], // stdout
], $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $json_input);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);

    $result = json_decode($output, true);
    echo "PHP received from Python: " . $result['mean'] . "\n";
}
```

```python
# filename: examples/process_data.py
import sys
import json
import numpy as np

# Read from PHP's stdin
input_data = json.load(sys.stdin)
arr = np.array(input_data)

# Perform heavy math
result = {
    "mean": float(arr.mean()),
    "sum": int(arr.sum())
}

# Output to PHP's stdout
print(json.dumps(result))
```

**2. Strategy B: Microservice Integration (Robust)**
Expose your pandas analysis via a small **Flask** or **FastAPI** service (as seen in Chapter 9).

### Why It Works

- **Loose Coupling**: Your PHP app doesn't care how the Python code works, as long as the JSON matches the schema.
- **Performance**: You only pay the "Python tax" when doing complex analysis.
- **Scalability**: You can eventually move the Python microservice to its own server/container if it becomes a bottleneck.

### Troubleshooting

**Problem: Python script can't find libraries when called from PHP**

**Cause**: PHP is using the global Python path instead of your virtual environment.

**Solution**: Use the full path to the `python` executable in your venv:

```php
$venv_python = __DIR__ . '/venv/bin/python';
$command = "{$venv_python} my_script.py";
```

## Exercises

### Exercise 1: The Idiomatic Transformer

**Goal**: Convert complex PHP logic into a concise Python list comprehension.

**Requirement**:
Given a list of transactions, calculate the tax (15%) for all "Approved" transactions over $100.

```php
$transactions = [
    ['id' => 1, 'amount' => 150, 'status' => 'Approved'],
    ['id' => 2, 'amount' => 50, 'status' => 'Approved'],
    ['id' => 3, 'amount' => 200, 'status' => 'Pending'],
    ['id' => 4, 'amount' => 300, 'status' => 'Approved']
];

$taxes = [];
foreach ($transactions as $t) {
    if ($t['status'] === 'Approved' && $t['amount'] > 100) {
        $taxes[] = $t['amount'] * 0.15;
    }
}
```

**Validation**: Your Python code should be a single line (excluding the list creation).

### Exercise 2: Anomaly Detection with NumPy

**Goal**: Use NumPy's speed to find outliers in a dataset.

**Requirement**:

1. Generate an array of 5,000 random numbers from a normal distribution (`np.random.normal`).
2. Calculate the Mean and Standard Deviation.
3. Use **Boolean Masking** to find all values that are more than 3 standard deviations away from the mean (these are the "outliers").
4. Print the percentage of the data that are outliers.

**Validation**: You should use zero `for` loops.

### Exercise 3: The Customer Segmenter

**Goal**: Use pandas to perform business segmentation.

**Requirement**:

1. Create a DataFrame from a dictionary of 10 customers with columns: `name`, `total_spend`, `last_purchase_days_ago`.
2. Create a new column `segment` using `np.where` or `.apply()`:
   - "VIP" if spend > 500 AND last purchase < 30 days ago.
   - "At Risk" if last purchase > 90 days ago.
   - "Standard" otherwise.
3. Group by `segment` and calculate the average `total_spend`.

**Validation**: Print the resulting summary table.

## Wrap-up


### What You've Learned

In this chapter, you bridged the gap between PHP and the Python data science ecosystem:

1. **Python Syntax**: Mastery of indentation, lists, dictionaries, and f-strings from a PHP perspective.
2. **Environment Management**: Using `venv` and `pip` to isolate and manage project dependencies safely.
3. **NumPy Foundations**: Understanding vectorized operations and why they outperform standard loops for math.
4. **Pandas Mastery**: Using DataFrames to clean, filter, aggregate, and join tabular data with SQL-like efficiency.
5. **Functional Idioms**: Replacing verbose `foreach` loops with powerful list comprehensions and lambda functions.
6. **Interactive Exploration**: Leveraging Jupyter Notebooks for rapid experimentation, visualization, and documentation.
7. **Hybrid Thinking**: Knowing when to use PHP for application logic and Python for heavy analytical lifting.

### What You've Built

You now have a solid foundation in Python data science:

1. **Comparison Cheat Sheet**: A reference library for translating PHP patterns to Python.
2. **NumPy Matrix Lab**: High-performance numerical code for statistical analysis.
3. **Pandas Analyzer**: A robust toolkit for processing structured business datasets.
4. **Jupyter Portfolio**: An interactive document showcasing your visualization and analysis work.
5. **Environment Template**: A professional workflow for managing Python data projects.

### PHP vs Python: The Data Science View

| Task                    | PHP (Chapter 1-12)          | Python (Chapter 13+)                 |
| ----------------------- | --------------------------- | ------------------------------------ |
| **Data Scrapping/APIs** | Excellent (Guzzle, Symfony) | Good, but often more complex         |
| **Data Cleaning**       | Great for strings/regex     | Superior for numerical/missing data  |
| **Math/Stats**          | Good (MathPHP)              | Industry standard (NumPy/SciPy)      |
| **Tabular Analysis**    | Verbose associative arrays  | Intuitive DataFrames (pandas)        |
| **Machine Learning**    | Emerging (PHP-ML)           | Cutting edge (scikit-learn, PyTorch) |
| **Visualization**       | Web-based (Chart.js)        | Research-grade (Matplotlib, Seaborn) |

### Key Python Data Science Principles

**1. Avoid Loops Like the Plague**
If you're writing a `for` loop to do math on an array, there's probably a NumPy or pandas function that does it 100x faster in a single line.

**2. Handle Missing Data Explicitly**
Don't let `null` values break your models. Use `.fillna()` or `.dropna()` to be intentional about your data quality.

**3. Type Hinting Matters**
As a PHP developer used to strict typing in 8.4, use Python's type hints to make your data pipelines robust and self-documenting.

**4. Notebooks for Research, Scripts for Production**
Use Jupyter to find the right insight, then refactor that code into clean Python classes for your PHP application to orchestrate.

### Connection to Data Science Workflow

You are now entering the **Advanced Specialization** phase of your data science journey:

1. ✅ **Chapter 1-12**: Built end-to-end data systems using PHP.
2. ✅ **Chapter 13**: **Deepened Python skills for specialized tasks** ← You are here
3. ➡️ **Chapter 14**: Mastering advanced Data Wrangling with Python.

### Next Steps

**Immediate Practice**:

1. Take a complex data transformation from your current PHP project and try to implement it in pandas.
2. Visit [Kaggle](https://www.kaggle.com/) and download a small dataset to explore in Jupyter.
3. Explore the [scikit-learn](https://scikit-learn.org/) gallery to see what's possible in the next few chapters.

**Chapter 14 Preview**:

In the next chapter, we'll dive deep into **Data Wrangling with pandas and NumPy**. You'll learn:

- Advanced joining and merging strategies (Inner, Outer, Right joins)
- Reshaping data with `pivot_table` and `melt`
- Working with Time Series data (resampling by day/month/year)
- Cleaning messy strings and handling outliers at scale
- Optimized cleaning pipelines for machine learning

You'll transform raw, chaotic data into high-quality datasets ready for predictive modeling!

## Further Reading

- [The Python Tutorial](https://docs.python.org/3/tutorial/) — Official Python documentation
- [NumPy Quickstart](https://numpy.org/doc/stable/user/quickstart.html) — Essential NumPy guide
- [Pandas Getting Started](https://pandas.pydata.org/docs/getting_started/index.html) — Core pandas concepts
- [JupyterLab Documentation](https://jupyterlab.readthedocs.io/en/stable/) — Master your interactive environment
- [Python for Data Analysis](https://wesmckinney.com/book/) — The definitive guide by the creator of pandas

::: tip Next Chapter
Continue to [Chapter 14: Data Wrangling with pandas and NumPy](/series/data-science-php-developers/chapters/14-data-wrangling-pandas-numpy) to master advanced data manipulation!
:::
