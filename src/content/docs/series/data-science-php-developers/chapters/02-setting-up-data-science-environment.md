---
title: "02: Setting Up a Data Science Environment as a PHP Developer"
description: "Set up a lean, practical PHP data science environment with optional Python integration"
sidebar:
  label: "02: Setting Up a Data Science Environment as a PHP Developer"
  order: 2
  badge:
    text: Beginner
    variant: success
---

![Setting Up a Data Science Environment as a PHP Developer](/images/data-science-php-developers/chapter-02-setting-up-environment-hero-full.webp)

# Chapter 02: Setting Up a Data Science Environment as a PHP Developer

## Overview

Before you analyze data or integrate machine learning into your PHP applications, you need the right environment. Not a bloated setup with dozens of tools—but a lean, practical workflow that respects how PHP developers actually work.

This chapter shows you how to set up a PHP-centric data science environment, introduce Python only where it adds real value, and structure projects so your stack stays maintainable. You'll learn what tools you actually need (and what you don't), how to configure PHP for data work, set up minimal Python integration, and establish communication patterns between the two languages.

By the end of this chapter, you'll have a working environment ready for data collection, analysis, and machine learning integration—all orchestrated from PHP.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Composer installed globally (`composer --version`)
- Basic terminal/command line skills
- Completion of [Chapter 01](/series/data-science-php-developers/chapters/01-what-data-science-is-and-why-it-matters)
- **Estimated Time**: ~60 minutes

## What You'll Build

By the end of this chapter, you will have:

- PHP configured with necessary extensions for data work
- A Composer-based project structure for data science
- Essential PHP libraries installed (MathPHP, League CSV, Guzzle)
- Optional Python 3.10+ environment with minimal packages
- Three working communication patterns between PHP and Python
- Verification scripts confirming your setup works
- A reusable project template you can use throughout the series

## Objectives

- Verify PHP 8.4+ installation and required extensions
- Install essential PHP libraries for data science work
- Configure optional Python integration (minimal setup)
- Understand three production-safe PHP-Python communication patterns
- Create and test verification scripts
- Establish best practices for environment management
- Set up a reusable project template

## Step 1: The Right Mindset - PHP First, Not PHP Only (~5 min)

### Goal

Establish the balanced approach philosophy before installing anything.

### The Philosophy

A common mistake is trying to do everything in one language.

Another mistake is abandoning PHP entirely.

**The goal of this series is balance:**

- **PHP handles**: orchestration, ingestion, validation, and delivery
- **Python handles**: heavy numerical analysis and machine learning
- **APIs connect the two cleanly**

Think of **PHP as the control plane** of your data science system. It's the conductor of the orchestra, not necessarily the musician playing every instrument.

### Why This Matters

This approach means:

- You don't need to become a Python expert
- You keep your existing PHP infrastructure
- You add capabilities incrementally
- Your team can maintain the codebase
- Deployment stays familiar

**The Architecture**: In this hybrid approach, PHP serves as the **control plane** handling orchestration, data ingestion, validation, and result delivery. Python serves as the **computation layer** for numerical analysis, machine learning, and statistical operations. The two layers communicate via APIs, files, or CLI commands.

**Key takeaway**: PHP controls the workflow, Python provides specialized computation when needed.

## Step 2: What You Actually Need (And What You Don't) (~5 min)

### Goal

Understand what NOT to install before cluttering your system.

### You Do NOT Need

Let's be clear about what to avoid:

- ❌ **TensorFlow running inside PHP** — Use Python for deep learning
- ❌ **Custom C extensions for PHP** — Adds complexity, breaks portability
- ❌ **Distributed computing frameworks** — Not needed for 99% of PHP use cases
- ❌ **GPU support** — Heavy ML stays in Python microservices
- ❌ **Complex notebooks everywhere** — Notebooks are for exploration only
- ❌ **Every Python ML library** — Start minimal, add as needed
- ❌ **Docker/Kubernetes initially** — Start simple, containerize later

### You DO Need

Focus on essentials:

- ✅ **Reliable data handling** — Read/write CSV, JSON, databases
- ✅ **Repeatable scripts** — Automation via command line
- ✅ **Clear boundaries** — Know what runs where
- ✅ **Simple tooling** — You can debug and understand
- ✅ **Version control** — Git for scripts and configs
- ✅ **Environment isolation** — .env files, virtual environments

**Philosophy**: If you can't explain why you need a tool, don't install it yet.

### Tool Requirements Summary

Here's what you need for each chapter in this series:

| Tool | Purpose | Required? | Used In Chapters |
|------|---------|-----------|------------------|
| **PHP 8.4+** | Core language | Yes | All |
| **Composer** | Dependency management | Yes | All |
| **MySQL/PostgreSQL** | Data storage | Yes (either) | 3-12 |
| **MathPHP** | Statistical functions | Yes | 4-12 |
| **League CSV** | CSV file handling | Yes | 3-12 |
| **Guzzle** | HTTP API client | Yes | 3, 9-12 |
| **Python 3.10+** | Advanced ML (optional) | Optional | 13-20 (bonus) |
| **pandas/numpy** | Python data manipulation | Optional | 13-20 (bonus) |
| **scikit-learn** | Python ML library | Optional | 13-20 (bonus) |
| **Jupyter** | Interactive exploration | Optional | 13-20 (bonus) |
| **Docker** | Containerization | Optional | 12 (deployment) |

**Key takeaway**: You can complete the entire core series (Chapters 1-12) with just PHP, Composer, and a database. Python is only needed for the bonus advanced chapters (13-20).

## Step 3: PHP Setup for Data Science Work (~12 min)

### Goal

Configure PHP with the extensions and libraries needed for data science projects.

### 3a. PHP Version & Extensions

**Verify your PHP version:**

```bash
# Check PHP version
php --version

# Expected output:
# PHP 8.4.x (cli) ...
```

**Required PHP extensions** (most are already installed):

- `pdo` — Database access
- `pdo_mysql` or `pdo_pgsql` — Database drivers
- `json` — JSON parsing (built-in)
- `mbstring` — Multi-byte string handling
- `curl` — HTTP requests
- `intl` — Internationalization support

**Verify extensions:**

```bash
# Check all loaded extensions
php -m

# Check specific extension
php -m | grep pdo
php -m | grep json
php -m | grep curl
```

If you're missing extensions, install them:

```bash
# macOS (Homebrew)
brew install php

# Ubuntu/Debian
sudo apt-get install php8.4-pdo php8.4-mysql php8.4-curl php8.4-mbstring php8.4-intl

# Fedora/RHEL
sudo dnf install php-pdo php-mysqlnd php-mbstring php-intl
```

### 3b. Composer-Based Project Structure

Create a dedicated project structure for data science work:

```bash
# Create project directory
mkdir data-science-project
cd data-science-project

# Initialize Composer
composer init --name="your-name/data-science" --type=project --no-interaction
```

**Recommended directory structure:**

```
data-science-project/
├── composer.json
├── composer.lock
├── .env
├── .env.example
├── .gitignore
├── README.md
├── src/
│   ├── Ingestion/      # Data collection classes
│   ├── Cleaning/       # Data preprocessing
│   ├── Analysis/       # Statistical analysis
│   └── Reporting/      # Output generation
├── scripts/
│   ├── import.php      # Import data from sources
│   ├── clean.php       # Clean and validate data
│   ├── analyze.php     # Run analysis
│   └── export.php      # Export results
├── data/
│   ├── raw/            # Original data (not committed)
│   └── processed/      # Cleaned data (not committed)
├── output/
│   ├── reports/        # Generated reports
│   └── visualizations/ # Charts and graphs
└── tests/              # PHPUnit tests
```

**Why this structure matters:**

- **Separation of concerns**: Each directory has a clear purpose
- **Automation-friendly**: Scripts can be run via cron or CI/CD
- **Version control**: Easy to .gitignore data while committing code
- **Team collaboration**: Clear where to add new functionality

### 3c. Essential PHP Libraries

Install proven, minimal libraries via Composer:

```bash
# Mathematical operations and statistics
composer require markrogoyski/math-php

# CSV file handling
composer require league/csv

# HTTP client for APIs
composer require guzzlehttp/guzzle

# Environment variable management
composer require vlucas/phpdotenv

# Date/time handling (if needed)
composer require nesbot/carbon
```

**What each provides:**

| Library                   | Purpose                                 | When to Use                                |
| ------------------------- | --------------------------------------- | ------------------------------------------ |
| **markrogoyski/math-php** | Statistics, linear algebra, probability | Descriptive stats, distributions, basic ML |
| **league/csv**            | Read/write CSV files efficiently        | Data import/export, ETL pipelines          |
| **guzzlehttp/guzzle**     | HTTP requests with retries              | API integration, web scraping              |
| **vlucas/phpdotenv**      | Environment variable management         | Configuration, credentials                 |
| **nesbot/carbon**         | Date/time manipulation                  | Time series, date parsing                  |

**Libraries to AVOID:**

- ❌ Experimental PHP ML libraries with no maintenance
- ❌ Poorly documented math packages
- ❌ Heavy frameworks when you need simple scripts

**Verification:**

```bash
# List installed packages
composer show

# Verify MathPHP works
php -r "require 'vendor/autoload.php'; use MathPHP\Statistics\Average; echo Average::mean([1, 2, 3, 4, 5]);"

# Expected output: 3
```

### Why It Works

These libraries provide battle-tested solutions for common data tasks. MathPHP gives you statistical functions without writing formulas from scratch. League CSV handles edge cases in CSV parsing that you'd otherwise spend hours debugging. Guzzle provides retry logic and error handling for API calls.

By keeping the dependency list small, you reduce maintenance burden and keep your environment lean.

## Step 4: Python - The Smallest Possible Setup (~10 min)

### Goal

Install Python with minimal packages for when PHP isn't the right tool.

### 4a. Installing Python

You don't need to become a Python developer—but you do need Python for certain tasks.

**Install Python 3.10+:**

```bash
# macOS
brew install python@3.10

# Ubuntu/Debian
sudo apt-get install python3.10 python3.10-venv python3-pip

# Fedora/RHEL
sudo dnf install python3.10 python3-pip

# Verify installation
python3 --version

# Expected output: Python 3.10.x or newer
```

**Create a virtual environment** (isolates dependencies):

```bash
# Inside your project directory
python3 -m venv venv

# Activate it
source venv/bin/activate  # macOS/Linux
# or
venv\Scripts\activate     # Windows

# Your prompt should now show (venv)
```

**Why virtual environments matter:**

- Isolates project dependencies from system Python
- Prevents version conflicts
- Makes projects reproducible
- Allows different Python versions per project

**To deactivate when done:**

```bash
deactivate
```

### 4b. Essential Python Packages Only

With the virtual environment activated, install minimal packages:

```bash
# Install core data science packages
pip install pandas numpy scikit-learn jupyter

# Verify installation
pip list
```

**What these give you:**

| Package          | Purpose                           | Size        | When to Use                   |
| ---------------- | --------------------------------- | ----------- | ----------------------------- |
| **pandas**       | Data manipulation, DataFrames     | Essential   | Reading/transforming datasets |
| **numpy**        | Numerical arrays, math operations | Essential   | Matrix operations, fast math  |
| **scikit-learn** | Machine learning algorithms       | When needed | Training ML models            |
| **jupyter**      | Interactive notebooks             | Exploration | Data exploration, prototyping |

**Total installation size:** ~200-300 MB

**What we're NOT installing (yet):**

- ❌ TensorFlow / PyTorch (deep learning) — 2+ GB, not needed initially
- ❌ Matplotlib / Seaborn (visualization) — PHP will handle output
- ❌ SQL Alchemy (databases) — PHP handles databases
- ❌ Flask / Django (web frameworks) — Wait until Chapter 9

**Looking ahead**: These minimal packages cover chapters 1-12. **Bonus chapters 13-20** introduce specialized libraries (TensorFlow, Dask, MLflow) for advanced Python data science—but only after you've mastered the PHP-first fundamentals in the core series.

**Verification:**

```bash
# Test pandas
python3 -c "import pandas as pd; print(pd.__version__)"

# Test numpy
python3 -c "import numpy as np; print(np.__version__)"

# Test scikit-learn
python3 -c "import sklearn; print(sklearn.__version__)"
```

### Why It Works

This minimal setup covers 90% of data science tasks you'll encounter as a PHP developer. Pandas and numpy handle data transformation and numerical computation. Scikit-learn provides ML algorithms. Jupyter lets you explore data interactively before productionizing in PHP.

By avoiding deep learning frameworks initially, you keep the setup fast and maintainable. You can always add TensorFlow later if needed—but most PHP applications don't require it.

## Step 5: How PHP and Python Communicate (~12 min)

### Goal

Understand and implement three production-safe approaches for PHP-Python integration.

### The Communication Challenge

PHP and Python don't naturally talk to each other. You need a bridge. There are three production-safe options, each with different trade-offs.

### Option 1: File-Based Exchange (Best for Batch Jobs)

**How it works:**

1. PHP exports data to CSV or JSON
2. Python script processes the file
3. Python writes results to output file
4. PHP reads and uses the results

The workflow is straightforward: PHP Application → Exports CSV → File System → Python Script → Writes Results → File System → PHP Application reads results.

**Example - PHP exports data:**

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use League\Csv\Writer;

// Export data for Python to process
$csv = Writer::createFromPath('data/export.csv', 'w+');
$csv->insertOne(['id', 'value', 'category']);

$data = [
    [1, 100, 'A'],
    [2, 200, 'B'],
    [3, 150, 'A'],
];

$csv->insertAll($data);

echo "Data exported to data/export.csv\n";
```

**Example - Python processes:**

```python
# process.py
import pandas as pd

# Read CSV from PHP
df = pd.read_csv('data/export.csv')

# Process data
result = df.groupby('category')['value'].mean()

# Write results
result.to_csv('data/results.csv')
print("Processing complete")
```

**Example - PHP reads results:**

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use League\Csv\Reader;

// Read results from Python
$csv = Reader::createFromPath('data/results.csv', 'r');
$csv->setHeaderOffset(0);

foreach ($csv as $record) {
    echo "{$record['category']}: {$record['value']}\n";
}
```

**Pros:**

- ✅ Simple to implement
- ✅ Easy to debug (inspect files)
- ✅ No server infrastructure needed
- ✅ Works with cron jobs

**Cons:**

- ❌ Not real-time
- ❌ File I/O overhead
- ❌ Requires disk space

**When to use:** Batch ETL pipelines, scheduled reports, overnight processing

### Option 2: CLI Invocation (Best for Quick Tasks)

**How it works:**

PHP runs Python scripts directly via `exec()` or `shell_exec()`, passing arguments and capturing output.

**Example - PHP invokes Python:**

```php
<?php

declare(strict_types=1);

function runPythonModel(array $features): array
{
    // Encode features as JSON for Python
    $input = json_encode($features);
    $escapedInput = escapeshellarg($input);

    // Run Python script, capture output
    $command = "python3 scripts/model.py {$escapedInput} 2>&1";
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new RuntimeException("Python script failed: " . implode("\n", $output));
    }

    // Parse JSON output from Python
    $result = json_decode(implode('', $output), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON from Python");
    }

    return $result;
}

// Usage
try {
    $prediction = runPythonModel(['age' => 35, 'income' => 50000]);
    echo "Prediction: {$prediction['score']}\n";
} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

**Example - Python script:**

```python
# scripts/model.py
import sys
import json

def predict(features):
    # Simple model (replace with real ML)
    score = features['age'] * 0.5 + features['income'] / 1000
    return {'score': round(score, 2)}

if __name__ == '__main__':
    try:
        # Read input from PHP
        input_json = sys.argv[1]
        features = json.loads(input_json)

        # Make prediction
        result = predict(features)

        # Output JSON for PHP
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({'error': str(e)}), file=sys.stderr)
        sys.exit(1)
```

**Pros:**

- ✅ Fast to implement
- ✅ No servers needed
- ✅ Synchronous (get results immediately)
- ✅ Good for small tasks

**Cons:**

- ❌ Process startup overhead
- ❌ Limited error handling
- ❌ Not suitable for long-running tasks
- ❌ Security concerns with user input

**When to use:** Quick predictions, data transformations, one-off calculations

### Option 3: API / Microservice (Best for Real-Time)

**How it works:**

Python runs as a web service (Flask/FastAPI), PHP sends HTTP requests, Python returns JSON responses.

**Example - Python API server:**

```python
# api_server.py
from flask import Flask, request, jsonify
import numpy as np

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        features = np.array([data['age'], data['income']])

        # Simple model
        score = features[0] * 0.5 + features[1] / 1000

        return jsonify({'score': float(score)})
    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
```

**Example - PHP HTTP client:**

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

function predictViaAPI(array $features): array
{
    $client = new Client([
        'base_uri' => 'http://localhost:5000',
        'timeout' => 5.0,
    ]);

    try {
        $response = $client->post('/predict', [
            'json' => $features,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    } catch (GuzzleException $e) {
        throw new RuntimeException("API request failed: " . $e->getMessage());
    }
}

// Usage
try {
    $result = predictViaAPI(['age' => 35, 'income' => 50000]);
    echo "Prediction: {$result['score']}\n";
} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

**Pros:**

- ✅ Real-time responses
- ✅ Scalable (horizontal scaling)
- ✅ Language-agnostic (could be any backend)
- ✅ Clean separation of concerns
- ✅ Can cache predictions

**Cons:**

- ❌ More setup (server, deployment)
- ❌ Network latency
- ❌ Requires monitoring
- ❌ Infrastructure cost

**When to use:** Production predictions, high-frequency requests, multiple consumers

### Comparison Matrix

| Approach           | Setup   | Speed  | Scalability | Use Case           |
| ------------------ | ------- | ------ | ----------- | ------------------ |
| **File Exchange**  | Easy    | Slow   | Low         | Batch ETL, reports |
| **CLI Invocation** | Easy    | Medium | Low         | Quick predictions  |
| **API Service**    | Complex | Fast   | High        | Production ML      |

### Why It Works

Each approach has its place:

- **File exchange** is perfect for overnight batch jobs where you process thousands of records
- **CLI invocation** works well for synchronous tasks where you need an answer immediately
- **API services** shine in production when you need scalability and real-time predictions

Throughout this series, we'll use all three approaches depending on the problem at hand. You don't need to pick one—use the right tool for each job.

## Step 6: Jupyter Notebooks - When (and When Not) to Use Them (~4 min)

### Goal

Understand the proper role of notebooks in a production workflow.

### What Jupyter Notebooks Are Good For

Jupyter excels at:

- **Exploring datasets**: See data structure, distributions, outliers
- **Testing ideas**: Try different transformations quickly
- **Visualizing patterns**: Create charts inline
- **Documenting analysis**: Mix code with explanations
- **Learning**: Experiment with libraries and techniques

**Start Jupyter:**

```bash
# With venv activated
jupyter notebook

# Opens browser at http://localhost:8888
```

### What Notebooks Are Bad For

Jupyter fails at:

- ❌ **Production logic** — No version control, hard to test
- ❌ **Versioned pipelines** — Difficult to diff changes
- ❌ **Automation** — Can't run via cron easily
- ❌ **Team collaboration** — Merge conflicts are painful
- ❌ **Error handling** — Notebooks hide failures

### The Rule of Thumb

**Explore in notebooks, ship code as scripts.**

**Typical workflow:**

1. **Explore** data in Jupyter notebook
2. **Prototype** analysis in notebook
3. **Extract** working code into `.py` scripts
4. **Call** Python scripts from PHP (via CLI or API)
5. **Version** the Python scripts, not notebooks

**Example workflow:**

```
exploration.ipynb     → Try ideas, visualize
      ↓
model.py             → Production Python script
      ↓
PHP calls model.py   → Integration layer
```

### Why It Works

Notebooks are scratchpads, not applications. They're invaluable for understanding your data and trying approaches quickly. But once you know what works, you need to extract that code into proper scripts that PHP can call reliably.

Think of notebooks like prototyping in a design tool—you don't ship the prototype, you ship the refined product.

## Step 7: Environment Variables & Configuration (~5 min)

### Goal

Manage credentials, API keys, and paths securely across environments.

### Never Hardcode These

Avoid hardcoding:

- ❌ Database credentials
- ❌ API keys
- ❌ File paths
- ❌ Service URLs
- ❌ Secret tokens

### Use .env Files

**Create `.env` file:**

```bash
# .env (never commit this file)
DB_HOST=localhost
DB_NAME=analytics
DB_USER=your_username
DB_PASSWORD=your_password

API_KEY=sk_live_xxx
API_URL=https://api.example.com

DATA_PATH=/var/data
OUTPUT_PATH=/var/output
```

**Create `.env.example` (commit this):**

```bash
# .env.example (template for team)
DB_HOST=
DB_NAME=
DB_USER=
DB_PASSWORD=

API_KEY=
API_URL=

DATA_PATH=
OUTPUT_PATH=
```

**Load in PHP:**

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Access variables
$dbHost = $_ENV['DB_HOST'];
$apiKey = $_ENV['API_KEY'];
$dataPath = $_ENV['DATA_PATH'];

echo "Connected to: {$dbHost}\n";
```

**Load in Python:**

```python
# pip install python-dotenv
from dotenv import load_dotenv
import os

load_dotenv()

db_host = os.getenv('DB_HOST')
api_key = os.getenv('API_KEY')
data_path = os.getenv('DATA_PATH')

print(f"Connected to: {db_host}")
```

### Why This Matters for Data Science

Data science projects run in multiple contexts:

- **Local development** — Your laptop
- **Scheduled jobs** — Cron on server
- **Production** — Different credentials
- **CI/CD** — Test databases

Environment variables let you:

- Run the same script in different environments
- Keep secrets out of version control
- Change configuration without code changes
- Share projects safely with team

**Add to `.gitignore`:**

```
.env
.env.local
.env.*.local
```

### Why It Works

By externalizing configuration, you make scripts portable. You can run the same cron job locally for testing, then deploy to production by simply changing `.env` files. This prevents silent failures from hardcoded paths and keeps credentials secure.

## Step 8: Version Control for Data Science Projects (~4 min)

### Goal

Understand what to commit and what to ignore in data science projects.

### Do NOT Commit

Add these to `.gitignore`:

```
# .gitignore for data science projects

# Dependencies
vendor/
node_modules/
venv/
__pycache__/

# Environment
.env
.env.local

# Data files (can be large)
data/raw/**
data/processed/**
*.csv
*.xlsx
*.parquet

# Generated outputs
output/**
*.png
*.jpg
*.pdf

# Model artifacts
*.pkl
*.h5
*.joblib
*.model

# Jupyter
.ipynb_checkpoints/
*.ipynb  # Optional: some teams commit notebooks

# OS
.DS_Store
Thumbs.db
```

**Why not commit data and models:**

- Large files bloat repository
- Binary files don't diff well
- Data may contain sensitive information
- Models can be regenerated from code

### DO Commit

Always commit:

```
✅ Source code (PHP, Python)
✅ Configuration templates (.env.example)
✅ Documentation (README.md)
✅ Requirements (composer.json, requirements.txt)
✅ Scripts (import.php, model.py)
✅ Tests (PHPUnit, pytest)
✅ Schema definitions
✅ Data samples (small, anonymized)
```

### The Philosophy

**Treat data science like software—not experiments.**

Your repository should contain:

- Instructions to recreate the environment
- Code to fetch/generate data
- Scripts to train models
- Documentation explaining the workflow

Anyone with access should be able to:

1. Clone the repo
2. Install dependencies
3. Run scripts
4. Reproduce results

### Example README.md Structure

```markdown
# Data Science Project

## Setup

1. Install dependencies: `composer install`
2. Copy `.env.example` to `.env` and configure
3. Create data directories: `mkdir -p data/{raw,processed}`

## Data Pipeline

1. `php scripts/import.php` - Fetch data from API
2. `php scripts/clean.php` - Clean and validate
3. `python scripts/analyze.py` - Run analysis
4. `php scripts/report.php` - Generate report

## Requirements

- PHP 8.4+
- Python 3.10+
- MySQL 8.0+
```

### Why It Works

By committing code but not data, you keep repositories small and fast. Your team can reproduce your work without downloading gigabytes of CSVs. Version control focuses on logic changes, not data snapshots.

This approach also forces you to write scripts that fetch or generate data, making your pipeline reproducible and documented.

## Step 9: Common Setup Mistakes PHP Developers Make (~3 min)

### Goal

Avoid pitfalls that slow down data science projects.

### Mistake 1: Trying to Replicate Python ML in PHP

**The Problem:**

Developers try to implement scikit-learn algorithms in pure PHP because they don't want to "depend on Python."

**The Reality:**

Python ML libraries have:

- 10+ years of optimization
- Thousands of contributors
- Extensive testing
- GPU support

You can't replicate that in PHP—and you shouldn't try.

**The Solution:**

Use Python for ML, PHP for orchestration. Don't reinvent the wheel.

### Mistake 2: Mixing Data Logic Into Controllers

**The Problem:**

```php
// ❌ Bad: Data analysis in controller
class DashboardController
{
    public function analytics(Request $request)
    {
        $data = DB::table('orders')->get();
        $mean = array_sum($data) / count($data);
        // 100 lines of analysis...
        return view('dashboard', ['mean' => $mean]);
    }
}
```

**The Solution:**

```php
// ✅ Good: Separate data analysis
class DashboardController
{
    public function analytics(Request $request)
    {
        $analytics = new AnalyticsService();
        $report = $analytics->generateReport();
        return view('dashboard', $report);
    }
}

// src/Analysis/AnalyticsService.php
class AnalyticsService
{
    public function generateReport(): array
    {
        // Analysis logic here
    }
}
```

**Why:** Data pipelines deserve their own structure, separate from web controllers.

### Mistake 3: Overengineering Too Early

**The Problem:**

Developers build complex abstractions before understanding the problem:

- Custom ORM for data loading
- Abstract factory for models
- Complex queue systems

**The Solution:**

Start simple:

1. Single script that works
2. Refactor when you see patterns
3. Add abstraction only when you repeat yourself 3+ times

**Why:** Data science is exploratory—you'll change approaches frequently. Heavy abstractions slow you down.

### Mistake 4: Ignoring Reproducibility

**The Problem:**

Scripts that only run on your machine:

- Hardcoded paths: `/Users/you/Desktop/data.csv`
- Missing dependencies
- No documentation
- "Works on my machine" syndrome

**The Solution:**

Make everything reproducible:

- Use relative paths or environment variables
- Document all dependencies
- Provide setup instructions
- Test on a fresh machine

**Why:** If you can't rerun it, it's not data science—it's guesswork.

## Alternative: Docker Setup (Optional)

If you prefer containerized development or need consistent environments across teams, Docker is an excellent alternative to local installation.

### When to Use Docker

**Use Docker if:**
- You work on multiple projects with different PHP versions
- Your team needs identical development environments
- You're deploying to containerized production
- You want to avoid "works on my machine" issues
- You need to test on different operating systems

**Skip Docker if:**
- You're just learning and want simplicity
- You already have PHP 8.4 working locally
- You prefer native performance
- You're working solo on a single project

### Basic Docker Setup

**Create `docker-compose.yml`:**

```yaml
version: '3.8'

services:
  php:
    image: php:8.4-cli
    volumes:
      - ./:/app
    working_dir: /app
    command: tail -f /dev/null  # Keep container running
    
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: data_science
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

  python:
    image: python:3.10
    volumes:
      - ./:/app
    working_dir: /app
    command: tail -f /dev/null

volumes:
  mysql_data:
```

**Usage:**

```bash
# Start all services
docker-compose up -d

# Run PHP scripts
docker-compose exec php php hello-data-science.php

# Run Python scripts
docker-compose exec python python3 python-test.py

# Install Composer dependencies
docker-compose exec php composer install

# Install Python packages
docker-compose exec python pip install pandas numpy scikit-learn

# Stop services
docker-compose down
```

### Docker with Development Tools

**Enhanced `Dockerfile`:**

```dockerfile
FROM php:8.4-cli

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Install Python for hybrid workflows
RUN apt-get install -y python3 python3-pip python3-venv

CMD ["php", "-a"]
```

**Build and run:**

```bash
# Build custom image
docker build -t php-data-science .

# Run container
docker run -it --rm -v $(pwd):/app php-data-science bash

# Inside container, you have both PHP and Python
php --version
python3 --version
composer --version
```

### Docker Best Practices

1. **Use volumes for persistence**:
   ```yaml
   volumes:
     - ./data:/app/data  # Data persists on host
   ```

2. **Don't commit vendor/ or node_modules/**:
   ```bash
   # Install inside container
   docker-compose exec php composer install
   ```

3. **Use .dockerignore**:
   ```
   vendor/
   node_modules/
   .env
   .git/
   *.log
   ```

4. **Keep images small**:
   ```dockerfile
   # Use alpine for smaller images
   FROM php:8.4-cli-alpine
   ```

5. **Cache Composer dependencies**:
   ```dockerfile
   # Copy composer files first (better caching)
   COPY composer.json composer.lock ./
   RUN composer install --no-scripts --no-autoloader
   
   # Then copy application code
   COPY . .
   RUN composer dump-autoload --optimize
   ```

### Docker vs Local Development

| Aspect | Local | Docker |
|--------|-------|--------|
| **Setup Time** | Fast (if PHP installed) | Slower (download images) |
| **Performance** | Native speed | Slight overhead |
| **Consistency** | Varies by machine | Identical everywhere |
| **Isolation** | System-wide PHP | Per-project containers |
| **Learning Curve** | Minimal | Moderate |
| **Team Onboarding** | Requires setup docs | `docker-compose up` |
| **CI/CD** | Requires configuration | Easy integration |

### Hybrid Approach

Many developers use both:

```bash
# Local development (fast iteration)
php artisan serve

# Docker for testing (consistency check)
docker-compose up -d
docker-compose exec php php artisan test

# Docker for deployment (production match)
docker build -t app:latest .
```

**Recommendation for this series**: Start with local installation for simplicity. Add Docker later when you need it (Chapter 12: Deployment covers this in detail).

## Exercises

Practice setting up your environment with these hands-on challenges.

### Exercise 1: Verify Your PHP Setup

**Goal**: Confirm PHP and extensions are properly configured

Create `verify-php.php`:

```php
<?php

declare(strict_types=1);

echo "PHP Version: " . PHP_VERSION . "\n\n";

$required = ['pdo', 'json', 'mbstring', 'curl', 'intl'];

echo "Extension Check:\n";
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '✗';
    echo "  {$status} {$ext}\n";
}

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
    echo "\n✓ Composer dependencies installed\n";
} else {
    echo "\n✗ Run: composer install\n";
}
```

Run it:

```bash
php verify-php.php
```

**Expected output:**

```
PHP Version: 8.4.x

Extension Check:
  ✓ pdo
  ✓ json
  ✓ mbstring
  ✓ curl
  ✓ intl

✓ Composer dependencies installed
```

### Exercise 2: Test File Exchange Pattern

**Goal**: Implement PHP → Python → PHP data flow

**Part 1 - PHP exports:**

```php
<?php
# export.php

declare(strict_types=1);

require 'vendor/autoload.php';

use League\Csv\Writer;

$csv = Writer::createFromPath('data/numbers.csv', 'w+');
$csv->insertOne(['value']);

for ($i = 1; $i <= 10; $i++) {
    $csv->insertOne([$i * 10]);
}

echo "Exported data/numbers.csv\n";
```

**Part 2 - Python processes:**

```python
# process.py
import pandas as pd

df = pd.read_csv('data/numbers.csv')
df['squared'] = df['value'] ** 2
df.to_csv('data/results.csv', index=False)
print("Processed data/results.csv")
```

**Part 3 - PHP imports:**

```php
<?php
# import.php

declare(strict_types=1);

require 'vendor/autoload.php';

use League\Csv\Reader;

$csv = Reader::createFromPath('data/results.csv', 'r');
$csv->setHeaderOffset(0);

foreach ($csv as $record) {
    echo "{$record['value']} squared = {$record['squared']}\n";
}
```

**Run the pipeline:**

```bash
php export.php
python3 process.py
php import.php
```

**Expected output:**

```
10 squared = 100
20 squared = 400
30 squared = 900
...
```

### Exercise 3: Environment Variables

**Goal**: Practice secure configuration management

Create `.env`:

```
APP_NAME="Data Science Project"
DATA_PATH="./data"
API_URL="https://api.example.com"
API_KEY="test_key_123"
```

Create `test-env.php`:

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "App: " . $_ENV['APP_NAME'] . "\n";
echo "Data Path: " . $_ENV['DATA_PATH'] . "\n";
echo "API URL: " . $_ENV['API_URL'] . "\n";
echo "API Key: " . (str_repeat('*', strlen($_ENV['API_KEY']) - 4) . substr($_ENV['API_KEY'], -4)) . "\n";
```

**Validation:**

```bash
php test-env.php
```

**Expected:**

```
App: Data Science Project
Data Path: ./data
API URL: https://api.example.com
API Key: *********123
```

## Troubleshooting

Common issues you might encounter during environment setup and their solutions.

### PHP Installation Issues

#### Problem: `command not found: php`

**Symptom**: Running `php --version` returns "command not found"

**Cause**: PHP is not installed or not in your system PATH

**Solution**:

```bash
# macOS with Homebrew
brew install php@8.4

# Ubuntu/Debian
sudo apt-get update
sudo apt-get install php8.4-cli

# Fedora/RHEL
sudo dnf install php84

# Verify installation
php --version
```

#### Problem: Wrong PHP version installed

**Symptom**: `php --version` shows PHP 7.x or 8.0-8.3

**Cause**: System has older PHP version

**Solution**:

```bash
# macOS - Switch to PHP 8.4
brew unlink php@8.3
brew link php@8.4

# Ubuntu - Use PPA for newer versions
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php8.4-cli

# Verify
php --version  # Should show 8.4.x
```

#### Problem: Missing PHP extensions

**Symptom**: Error like "Call to undefined function mb_strlen()"

**Cause**: Required PHP extension not installed

**Solution**:

```bash
# macOS (extensions usually included with Homebrew PHP)
brew reinstall php@8.4

# Ubuntu/Debian - Install specific extensions
sudo apt-get install php8.4-mbstring php8.4-curl php8.4-xml php8.4-pdo php8.4-mysql

# Verify extensions
php -m | grep -E 'mbstring|curl|pdo|json'
```

### Composer Issues

#### Problem: `command not found: composer`

**Symptom**: Running `composer --version` fails

**Cause**: Composer not installed globally

**Solution**:

```bash
# Download and install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# Verify
composer --version
```

#### Problem: Composer memory limit errors

**Symptom**: "Fatal error: Allowed memory size exhausted"

**Cause**: Composer requires more memory for dependency resolution

**Solution**:

```bash
# Temporary fix (one command)
php -d memory_limit=-1 $(which composer) install

# Permanent fix - Edit php.ini
# Find php.ini location:
php --ini

# Edit and change:
memory_limit = 512M  # or -1 for unlimited
```

#### Problem: Composer packages fail to install

**Symptom**: "Your requirements could not be resolved"

**Cause**: Version conflicts or missing PHP extensions

**Solution**:

```bash
# 1. Clear Composer cache
composer clear-cache

# 2. Update Composer itself
composer self-update

# 3. Try installing with verbose output
composer install -vvv

# 4. Check PHP version requirements
composer show --platform

# 5. Install missing extensions if needed
```

### Database Connection Issues

#### Problem: PDO extension not found

**Symptom**: "Fatal error: Class 'PDO' not found"

**Cause**: PDO extension not enabled

**Solution**:

```bash
# Ubuntu/Debian
sudo apt-get install php8.4-pdo php8.4-mysql

# macOS (usually included)
brew reinstall php@8.4

# Verify
php -m | grep -i pdo
```

#### Problem: Database connection refused

**Symptom**: "SQLSTATE[HY000] [2002] Connection refused"

**Cause**: Database server not running or wrong host/port

**Solution**:

```bash
# Check if MySQL is running
# macOS
brew services list | grep mysql

# Ubuntu
sudo systemctl status mysql

# Start MySQL if stopped
# macOS
brew services start mysql

# Ubuntu
sudo systemctl start mysql

# Test connection manually
mysql -u root -p

# Check if using correct host (localhost vs 127.0.0.1)
# Try both in your .env file
```

#### Problem: Access denied for user

**Symptom**: "SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'"

**Cause**: Wrong credentials or user lacks permissions

**Solution**:

```bash
# Reset MySQL root password (if needed)
# macOS
mysql.server stop
mysqld_safe --skip-grant-tables &
mysql -u root

# In MySQL console:
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
EXIT;

# Restart MySQL normally
brew services restart mysql

# Or create new user with proper permissions
mysql -u root -p
CREATE USER 'datauser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON data_science.* TO 'datauser'@'localhost';
FLUSH PRIVILEGES;
```

### Python Installation Issues

#### Problem: `python3: command not found`

**Symptom**: Running `python3 --version` fails

**Cause**: Python 3 not installed

**Solution**:

```bash
# macOS
brew install python@3.10

# Ubuntu/Debian
sudo apt-get install python3.10 python3.10-venv python3-pip

# Verify
python3 --version
```

#### Problem: pip not found

**Symptom**: `pip: command not found`

**Cause**: pip not installed or not in PATH

**Solution**:

```bash
# macOS
python3 -m ensurepip --upgrade

# Ubuntu/Debian
sudo apt-get install python3-pip

# Use python3 -m pip instead of pip
python3 -m pip --version
```

#### Problem: Virtual environment activation fails

**Symptom**: `source venv/bin/activate` does nothing or errors

**Cause**: Virtual environment not created properly or wrong shell

**Solution**:

```bash
# Recreate virtual environment
rm -rf venv
python3 -m venv venv

# Activate (bash/zsh)
source venv/bin/activate

# Activate (fish)
source venv/bin/activate.fish

# Activate (Windows)
venv\Scripts\activate

# Verify activation (prompt should show (venv))
which python  # Should point to venv/bin/python
```

#### Problem: Package installation fails in venv

**Symptom**: "error: externally-managed-environment"

**Cause**: Python 3.11+ on some systems prevents system-wide pip installs

**Solution**:

```bash
# Always use virtual environment (recommended)
python3 -m venv venv
source venv/bin/activate
pip install pandas numpy scikit-learn

# Or use --break-system-packages flag (not recommended)
pip install --break-system-packages pandas
```

### PHP-Python Communication Issues

#### Problem: Python script not found

**Symptom**: "sh: python3: command not found" when running from PHP

**Cause**: PHP doesn't have Python in its PATH

**Solution**:

```php
<?php
// Use full path to Python
$pythonPath = '/usr/local/bin/python3';  // macOS Homebrew
// or
$pythonPath = '/usr/bin/python3';  // Ubuntu

// Find Python path with:
// which python3

$command = escapeshellcmd("{$pythonPath} script.py");
$output = shell_exec($command);
```

#### Problem: Python script returns empty output

**Symptom**: PHP receives no output from Python script

**Cause**: Python buffering stdout or script has errors

**Solution**:

```python
# In Python script, flush output explicitly
import sys

print("Output", flush=True)
sys.stdout.flush()

# Or run Python with unbuffered flag
# In PHP:
$command = "python3 -u script.py";
```

#### Problem: JSON decode error in PHP

**Symptom**: "Syntax error, malformed JSON"

**Cause**: Python output contains non-JSON text (warnings, errors)

**Solution**:

```python
# Python - Only output JSON, send errors to stderr
import json
import sys

try:
    result = {"status": "success", "data": [1, 2, 3]}
    print(json.dumps(result))
except Exception as e:
    # Send errors to stderr, not stdout
    print(json.dumps({"status": "error", "message": str(e)}), file=sys.stderr)
    sys.exit(1)
```

```php
<?php
// PHP - Capture stderr separately
$descriptors = [
    0 => ["pipe", "r"],  // stdin
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"],  // stderr
];

$process = proc_open("python3 script.py", $descriptors, $pipes);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);
proc_close($process);

$data = json_decode($stdout, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    echo "Python stderr: {$stderr}\n";
}
```

### File Permission Issues

#### Problem: Permission denied when creating files

**Symptom**: "failed to open stream: Permission denied"

**Cause**: PHP doesn't have write permissions to directory

**Solution**:

```bash
# Give write permissions to data directory
chmod 755 data/
chmod 755 data/raw/
chmod 755 data/processed/

# Or make PHP user (www-data) the owner
sudo chown -R $USER:www-data data/
sudo chmod -R 775 data/
```

### Environment Variable Issues

#### Problem: $_ENV variables not loading

**Symptom**: "Undefined array key 'DB_HOST'"

**Cause**: .env file not loaded or variables not exported

**Solution**:

```php
<?php
// Make sure you're loading .env file
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Use $_ENV, not $_SERVER
echo $_ENV['DB_HOST'];  // ✓ Correct

// Or use getenv()
echo getenv('DB_HOST');  // ✓ Also works

// Not $_SERVER (unless explicitly set)
echo $_SERVER['DB_HOST'];  // ✗ Won't work
```

### Testing Issues

#### Problem: Code works in terminal but fails in test script

**Symptom**: Manual execution works, automated tests fail

**Cause**: Different PHP versions or missing extensions in test environment

**Solution**:

```bash
# Check which PHP is being used
which php
php --version

# Check PHP configuration
php --ini

# Ensure test script uses same PHP
/usr/bin/php test.php  # Use full path

# Check loaded extensions
php -m
```

### Still Having Issues?

If you're still stuck after trying these solutions:

1. **Check PHP error logs**:
   ```bash
   # Find error log location
   php -i | grep error_log
   
   # View recent errors
   tail -f /var/log/php_errors.log
   ```

2. **Enable verbose error reporting**:
   ```php
   <?php
   error_reporting(E_ALL);
   ini_set('display_errors', '1');
   ```

3. **Test with minimal example**:
   ```bash
   # Create test.php
   echo '<?php phpinfo();' > test.php
   php test.php | grep -i "version\|extension"
   ```

4. **Check system requirements**:
   ```bash
   # Verify all requirements
   php -v  # PHP 8.4+
   composer --version  # Composer 2.x
   python3 --version  # Python 3.10+
   mysql --version  # MySQL 8.0+ or PostgreSQL 14+
   ```

5. **Review the code samples**:
   - All working examples are in [`testing/data-science-php-developers/chapter-02/`](https://github.com/dalehurley/codewithphp/tree/main/testing/data-science-php-developers/chapter-02)
   - Start with `hello-data-science.php` (no dependencies)
   - Then try `database-test.php` (requires database)
   - Finally test `python-test.py` (optional)

## Wrap-up


Congratulations! You now have a complete, production-ready environment for PHP data science work.

### What You've Learned

You've learned:

- ✓ The PHP-first philosophy: PHP as control plane, Python for computation
- ✓ What tools you need (and what you don't)
- ✓ How to configure PHP with necessary extensions and libraries
- ✓ How to set up minimal Python with virtual environments
- ✓ Three production-safe PHP-Python communication patterns
- ✓ When to use Jupyter notebooks (exploration only)
- ✓ How to manage environment variables securely
- ✓ What to commit and what to ignore in version control
- ✓ Common setup mistakes and how to avoid them

### What You've Achieved

You've built:

- A lean PHP environment with MathPHP, League CSV, and Guzzle
- Optional Python 3.10+ with pandas, numpy, scikit-learn
- Working examples of file exchange, CLI invocation, and API communication
- A project template you can reuse throughout the series
- Verification scripts confirming everything works

**Most importantly**: You have a **reproducible environment** that balances simplicity with capability—ready for real data science work.

### Next Steps

In **Chapter 03: Collecting Data in PHP**, you'll put this environment to work:

- Query databases efficiently with PDO and ORMs
- Consume REST APIs with authentication and rate limiting
- Scrape websites ethically with best practices
- Build reusable data ingestion pipelines
- Handle errors and retries gracefully

Your environment is ready—now let's collect some data.

## Further Reading

To deepen your understanding of development environments:

- [PHP Extensions List](https://www.php.net/manual/en/extensions.php) — Official PHP extension documentation
- [Composer Best Practices](https://blog.martinhujer.cz/17-tips-for-using-composer-efficiently/) — Optimize your dependency management
- [Python Virtual Environments](https://realpython.com/python-virtual-environments-a-primer/) — Understanding venv and virtualenv
- [The Twelve-Factor App](https://12factor.net/) — Best practices for modern applications
- [Environment Variables in PHP](https://www.php.net/manual/en/function.getenv.php) — Official documentation
- [.gitignore Templates](https://github.com/github/gitignore) — Community .gitignore patterns

---

::: tip Ready to Collect Data?
Head to [Chapter 03: Collecting Data in PHP: Databases, APIs, and Web Scraping](/series/data-science-php-developers/chapters/03-collecting-data-databases-apis-scraping) to start gathering real-world data from multiple sources!
:::
