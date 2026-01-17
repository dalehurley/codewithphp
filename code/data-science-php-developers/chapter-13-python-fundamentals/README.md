# Chapter 13: Python Fundamentals for Data Science

This directory contains all code examples from Chapter 13, extracted from the tutorial for easy testing and modification.

## Prerequisites

- Python 3.10, 3.11, or 3.12
- PHP 8.4+
- Virtual environment recommended

## Setup

### 1. Create Virtual Environment

**macOS/Linux:**
```bash
python3 -m venv venv
source venv/bin/activate
```

**Windows (Command Prompt):**
```cmd
python -m venv venv
venv\Scripts\activate.bat
```

**Windows (PowerShell):**
```powershell
python -m venv venv
venv\Scripts\Activate.ps1
```

### 2. Install Dependencies

```bash
pip install -r requirements.txt
```

### 3. Verify Installation

```bash
python3 -c "import numpy; import pandas; print('Libraries ready!')"
```

## Examples

### Python Syntax Comparisons

```bash
python3 examples/python_vs_php.py
```

Demonstrates:
- Variables and data types
- Lists and dictionaries
- Control structures
- List comprehensions
- Functions and classes

### NumPy Fundamentals

```bash
python3 examples/numpy_lab.py
```

Demonstrates:
- Array creation and manipulation
- Vectorized operations
- Indexing and slicing
- Multi-dimensional arrays
- Performance comparison vs loops

### Pandas DataFrames

```bash
python3 examples/pandas_lab.py
```

Demonstrates:
- DataFrame creation
- Handling missing data
- Filtering and selection
- Grouping and aggregation
- Merging/joining data

### PHP-Python Integration

```bash
php examples/php_orchestrator.php
```

Demonstrates:
- Calling Python from PHP
- Passing data via JSON
- Processing with NumPy
- Returning results to PHP

## Troubleshooting

### Python not found in venv

**Problem:** PHP can't find the Python executable in your virtual environment.

**Solution:** Use the full path:

```php
$venv_python = __DIR__ . '/venv/bin/python';
$command = "{$venv_python} my_script.py";
```

### IndentationError

**Problem:** Mixed tabs and spaces or inconsistent indentation.

**Solution:** Configure your editor to use 4 spaces for indentation.

### ImportError: No module named 'numpy'

**Problem:** Libraries not installed or venv not activated.

**Solution:**
1. Ensure venv is activated (check for `(venv)` in prompt)
2. Run `pip install -r requirements.txt`

### Windows-Specific Issues

**PowerShell Execution Policy:**

If you can't activate the venv in PowerShell:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Path Separators:**

Use forward slashes in paths even on Windows (Python handles this automatically).

## PHP-Python Integration Patterns

### Method 1: Command Line (Simple)

```php
$result = shell_exec("python3 script.py --input data.json");
```

**Pros:** Simple
**Cons:** No error handling, no stderr capture

### Method 2: Process Communication (Better)

```php
$process = proc_open('python3 script.py', $descriptors, $pipes);
fwrite($pipes[0], json_encode($data));
$output = stream_get_contents($pipes[1]);
```

**Pros:** Full control, error handling
**Cons:** More verbose

### Method 3: HTTP API (Production)

```php
$response = $httpClient->post('http://ml-service:5000/predict', [
    'json' => $data
]);
```

**Pros:** Scalable, language-agnostic
**Cons:** Requires separate service

### Method 4: Message Queue (Async)

```php
$queue->publish('ml.predict', json_encode($data));
```

**Pros:** Async, scalable, fault-tolerant
**Cons:** Complex infrastructure

## Performance Notes

- **NumPy operations:** 10-100x faster than Python loops
- **pandas operations:** 5-50x faster than manual iteration
- **PHP-Python overhead:** ~50-100ms per proc_open call

## Further Reading

- [Python Tutorial](https://docs.python.org/3/tutorial/)
- [NumPy Quickstart](https://numpy.org/doc/stable/user/quickstart.html)
- [Pandas Getting Started](https://pandas.pydata.org/docs/getting_started/index.html)
- [JupyterLab Documentation](https://jupyterlab.readthedocs.io/en/stable/)

## Next Steps

- Complete the exercises in Chapter 13
- Experiment with Jupyter notebooks
- Try converting PHP data processing code to pandas
- Move on to Chapter 14: Data Wrangling with pandas and NumPy
