---
title: "11: Integrating PHP with Python for Advanced ML"
description: "Learn how to leverage Python's rich ML ecosystem from PHP applications: master shell execution, REST APIs, and message queues; build a production sentiment analyzer combining PHP's web strengths with Python's ML power"
series: "ai-ml-php-developers"
chapter: 11
order: 11
difficulty: "Intermediate"
prerequisites:
  - "/series/ai-ml-php-developers/chapters/10-neural-networks-and-deep-learning-fundamentals"
  - "/series/ai-ml-php-developers/chapters/08-leveraging-php-machine-learning-libraries"
---

![Integrating PHP with Python for Advanced ML](/images/ai-ml-php-developers/chapter-11-php-python-integration-hero-full.webp)

# Chapter 11: Integrating PHP with Python for Advanced ML

## Overview

You've learned the fundamentals of machine learning in PHP using PHP-ML and Rubix ML. These libraries are excellent for many tasks—classification, regression, clustering, even basic neural networks. But let's be honest: Python's machine learning ecosystem is massive. Libraries like scikit-learn, TensorFlow, PyTorch, pandas, and NumPy have decades of development, thousands of contributors, and implementations of cutting-edge algorithms that simply don't exist in PHP.

Here's the good news: you don't have to choose between PHP and Python. You can have the best of both worlds. PHP excels at web development, API serving, database interaction, and integrating with existing applications. Python excels at data science, machine learning, and scientific computing. By combining them, you build systems where PHP handles user requests, business logic, and web interfaces while Python handles complex ML tasks, data preprocessing, and model training.

This chapter teaches you three practical integration strategies with increasing sophistication: **shell execution** (quick and simple), **REST APIs** (scalable and decoupled), and **message queues** (asynchronous and production-ready). You'll build a complete sentiment analysis system where PHP accepts user reviews through a web interface and Python performs sophisticated text classification using scikit-learn. Every example includes proper error handling, security considerations, and performance patterns.

By the end of this chapter, you'll confidently integrate Python ML capabilities into PHP applications, understand when each approach is appropriate, and have production-ready code patterns for building hybrid systems.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 10](/series/ai-ml-php-developers/chapters/10-neural-networks-and-deep-learning-fundamentals) or equivalent understanding of ML concepts
- Completed [Chapter 08](/series/ai-ml-php-developers/chapters/08-leveraging-php-machine-learning-libraries) and have Rubix ML working
- PHP 8.4+ installed and configured from [Chapter 02](/series/ai-ml-php-developers/chapters/02-setting-up-your-ai-development-environment)
- Python 3.10+ installed (check with `python3 --version`)
- Basic command-line familiarity (running scripts, installing packages)
- Understanding of JSON data format
- Experience training classifiers in PHP from earlier chapters
- Familiarity with basic security concepts (input validation, escaping)

**Estimated Time**: ~90-120 minutes (reading, setup, running examples, exercises)

**Verify your setup:**

```bash
# Check PHP version
php --version  # Should be 8.4+

# Check Python version
python3 --version  # Should be 3.10+

# Check if pip (Python package manager) is available
python3 -m pip --version
```

## What You'll Build

By the end of this chapter, you will have created:

- A **"Hello World" integration** demonstrating basic PHP-to-Python communication
- A **JSON data exchange system** passing structured data between languages
- A **complete sentiment analyzer** with PHP web interface and Python ML backend
- A **model training pipeline** that trains scikit-learn models and persists them
- A **prediction service** that loads trained models and classifies new text
- A **security-hardened executor** with input validation and error handling
- A **REST API implementation** using Flask with a PHP client
- An **async message queue pattern** for background ML processing
- A **performance comparison tool** measuring latency of each approach
- An **error recovery system** handling Python failures gracefully
- A **production deployment guide** for real-world hybrid applications

All code examples include security best practices, proper error handling, and performance considerations.

::: info Code Examples
Complete, runnable examples for this chapter:

**Integration Examples:**

- [`01-simple-shell/hello.php`](../code/chapter-11/01-simple-shell/hello.php) — Basic PHP calling Python
- [`01-simple-shell/hello.py`](../code/chapter-11/01-simple-shell/hello.py) — Python script receiving data
- [`02-data-passing/exchange.php`](../code/chapter-11/02-data-passing/exchange.php) — JSON data exchange
- [`02-data-passing/process.py`](../code/chapter-11/02-data-passing/process.py) — Python processing structured data

**Sentiment Analysis Project:**

- [`03-sentiment-analysis/analyze.php`](../code/chapter-11/03-sentiment-analysis/analyze.php) — PHP web interface
- [`03-sentiment-analysis/train_model.py`](../code/chapter-11/03-sentiment-analysis/train_model.py) — Python training script
- [`03-sentiment-analysis/predict.py`](../code/chapter-11/03-sentiment-analysis/predict.py) — Python prediction service
- [`03-sentiment-analysis/data/reviews.csv`](../code/chapter-11/03-sentiment-analysis/data/reviews.csv) — Training data

**REST API Example:**

- [`04-rest-api-example/flask_server.py`](../code/chapter-11/04-rest-api-example/flask_server.py) — Flask ML API
- [`04-rest-api-example/php_client.php`](../code/chapter-11/04-rest-api-example/php_client.php) — PHP API client

**Production Patterns:**

- [`05-production-patterns/secure_executor.php`](../code/chapter-11/05-production-patterns/secure_executor.php) — Hardened shell execution
- [`05-production-patterns/async_queue.php`](../code/chapter-11/05-production-patterns/async_queue.php) — Redis queue example

See [`README.md`](../code/chapter-11/README.md) for complete setup instructions.
:::

## Quick Start

Want to see PHP and Python working together right now? Here's a 5-minute example:

::: tip
This is a simplified standalone example. Complete versions with full error handling are in `code/chapter-11/`. The pattern demonstrated here applies to all integration approaches throughout the chapter.
:::

**Create two files:**

```php
# filename: quick_integrate.php
<?php

declare(strict_types=1);

// Prepare data to send to Python
$data = ['text' => 'This product is amazing! Highly recommend.'];
$json = json_encode($data);

// Call Python script with data (properly escaped for security)
$escaped = escapeshellarg($json);
$output = shell_exec("python3 quick_sentiment.py {$escaped}");

// Parse Python's response
$result = json_decode($output ?: '{}', true);

echo "Review: {$data['text']}\n";
echo "Sentiment: {$result['sentiment']}\n";
echo "Confidence: " . round($result['confidence'] * 100, 1) . "%\n";
```

```python
# filename: quick_sentiment.py
import sys
import json

# Simple sentiment analysis (real version uses scikit-learn)
def analyze_sentiment(text):
    positive_words = ['amazing', 'excellent', 'great', 'love', 'recommend']
    negative_words = ['terrible', 'awful', 'hate', 'worst', 'disappointing']

    text_lower = text.lower()
    positive_count = sum(1 for word in positive_words if word in text_lower)
    negative_count = sum(1 for word in negative_words if word in text_lower)

    if positive_count > negative_count:
        return {'sentiment': 'positive', 'confidence': 0.85}
    elif negative_count > positive_count:
        return {'sentiment': 'negative', 'confidence': 0.85}
    else:
        return {'sentiment': 'neutral', 'confidence': 0.60}

# Read data from PHP
input_data = json.loads(sys.argv[1])
result = analyze_sentiment(input_data['text'])

# Send result back to PHP
print(json.dumps(result))
```

**Run it:**

```bash
php quick_integrate.php
```

**Expected output:**

```
Review: This product is amazing! Highly recommend.
Sentiment: positive
Confidence: 85.0%
```

**What just happened?** PHP prepared data as JSON, called the Python script with that data as a command-line argument, and Python performed sentiment analysis and returned the result as JSON. This simple pattern scales to complex ML tasks!

Now let's build production-ready integration systems...

## Objectives

By the end of this chapter, you will be able to:

- **Execute Python scripts from PHP** using shell commands with proper error handling
- **Exchange structured data** between PHP and Python using JSON
- **Secure integration points** by validating inputs and escaping shell arguments
- **Train ML models in Python** from PHP-initiated processes
- **Load and use trained models** for real-time predictions in web applications
- **Build REST APIs** with Flask/FastAPI that serve ML models to PHP clients
- **Implement async processing** using message queues for long-running ML tasks
- **Compare integration strategies** and choose the right approach for your use case
- **Handle errors gracefully** when Python services fail or are unavailable
- **Optimize performance** by caching results and minimizing inter-process communication
- **Deploy hybrid applications** combining PHP web layers with Python ML backends

## Integration Strategies Overview

Before diving into code, let's understand the three main approaches for PHP-Python integration:

### Strategy 1: Shell Execution (Synchronous)

**How it works:** PHP uses `shell_exec()`, `exec()`, or `proc_open()` to run Python scripts as separate processes.

**Pros:**

- Simplest to implement
- No additional infrastructure needed
- Works immediately on any server with Python installed
- Direct communication via command-line arguments and output

**Cons:**

- Synchronous (PHP waits for Python to complete)
- Higher latency (process startup overhead)
- Limited scalability for high-traffic applications
- Security risks if inputs aren't properly escaped

**Best for:** Simple integrations, development environments, low-traffic applications, one-off batch processing.

### Strategy 2: REST API (HTTP-based)

**How it works:** Python runs as a separate web service (Flask, FastAPI, Django). PHP makes HTTP requests to the Python API.

**Pros:**

- Language-independent (Python service could be anywhere)
- Scalable (multiple Python workers, load balancing)
- Decoupled architecture (services can be deployed independently)
- Can handle concurrent requests efficiently

**Cons:**

- Requires running and maintaining a separate service
- Network latency overhead
- More complex deployment (two services instead of one)
- Need to handle service availability and retries

**Best for:** Production applications, microservices architectures, shared ML services across multiple applications, high-traffic systems.

### Strategy 3: Message Queue (Asynchronous)

**How it works:** PHP sends ML tasks to a queue (Redis, RabbitMQ). Python workers consume tasks from the queue, process them, and return results.

**Pros:**

- Asynchronous (PHP doesn't wait)
- Highly scalable (add more workers as needed)
- Resilient (tasks survive server restarts)
- Decouples request handling from processing

**Cons:**

- Most complex to implement
- Requires message queue infrastructure (Redis, RabbitMQ, etc.)
- Not suitable for real-time predictions (async by nature)
- Need to handle result delivery (callbacks, polling, websockets)

**Best for:** Batch processing, long-running ML tasks (training, large-scale predictions), background jobs, systems requiring high throughput.

### Comparison Table

| Feature            | Shell Execution           | REST API        | Message Queue           |
| ------------------ | ------------------------- | --------------- | ----------------------- |
| **Complexity**     | Low                       | Medium          | High                    |
| **Latency**        | ~100-500ms                | ~50-200ms       | Async (seconds-minutes) |
| **Scalability**    | Limited                   | High            | Very High               |
| **Infrastructure** | None                      | Web server      | Queue system            |
| **Real-time?**     | Yes (blocking)            | Yes             | No (async)              |
| **Best Use Case**  | Development, simple tasks | Production APIs | Background processing   |

Now let's implement each strategy with working code!

## Step 1: Basic Shell Execution (~15 min)

### Goal

Establish the simplest possible PHP-to-Python communication using shell commands.

### Actions

1. **Create a Python script that receives data:**

```python
# filename: 01-simple-shell/hello.py
import sys
import json

def main():
    # Python can receive data via command-line arguments
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No data provided'}))
        sys.exit(1)

    # Parse JSON input from PHP
    try:
        input_data = json.loads(sys.argv[1])
        name = input_data.get('name', 'World')

        # Process the data (trivial example)
        result = {
            'greeting': f'Hello, {name}!',
            'processed_by': 'Python 3',
            'input_received': input_data
        }

        # Output JSON for PHP to parse
        print(json.dumps(result))
    except json.JSONDecodeError as e:
        print(json.dumps({'error': f'Invalid JSON: {str(e)}'}))
        sys.exit(1)

if __name__ == '__main__':
    main()
```

2. **Create a PHP script that calls Python:**

```php
# filename: 01-simple-shell/hello.php
<?php

declare(strict_types=1);

/**
 * Basic PHP-Python integration via shell execution.
 *
 * This demonstrates the fundamental pattern:
 * 1. PHP prepares data as JSON
 * 2. PHP calls Python script with data
 * 3. Python processes and outputs JSON
 * 4. PHP parses and uses the result
 */

function callPythonScript(string $scriptPath, array $data): ?array
{
    // Step 1: Encode data as JSON
    $json = json_encode($data);
    if ($json === false) {
        throw new RuntimeException('Failed to encode data as JSON');
    }

    // Step 2: Escape for shell (CRITICAL for security)
    $escapedJson = escapeshellarg($json);

    // Step 3: Build and execute command
    $command = "python3 {$scriptPath} {$escapedJson}";
    $output = shell_exec($command);

    if ($output === null) {
        throw new RuntimeException('Failed to execute Python script');
    }

    // Step 4: Parse Python's JSON output
    $result = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON from Python: ' . json_last_error_msg());
    }

    return $result;
}

// Example usage
try {
    echo "=== Basic PHP-Python Integration ===\n\n";

    // Test 1: Simple greeting
    $result1 = callPythonScript('hello.py', ['name' => 'PHP Developer']);
    echo "Test 1 - Simple greeting:\n";
    echo "  {$result1['greeting']}\n";
    echo "  Processed by: {$result1['processed_by']}\n\n";

    // Test 2: Complex data
    $result2 = callPythonScript('hello.py', [
        'name' => 'Machine Learning Engineer',
        'skills' => ['PHP', 'Python', 'ML'],
        'experience' => 5
    ]);
    echo "Test 2 - Complex data:\n";
    echo "  {$result2['greeting']}\n";
    echo "  Data received by Python: " . json_encode($result2['input_received']) . "\n\n";

    echo "✅ Integration working successfully!\n";

} catch (RuntimeException $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

3. **Run the example:**

```bash
cd docs/series/ai-ml-php-developers/code/chapter-11/01-simple-shell
php hello.php
```

### Expected Result

```
=== Basic PHP-Python Integration ===

Test 1 - Simple greeting:
  Hello, PHP Developer!
  Processed by: Python 3

Test 2 - Complex data:
  Hello, Machine Learning Engineer!
  Data received by Python: {"name":"Machine Learning Engineer","skills":["PHP","Python","ML"],"experience":5}

✅ Integration working successfully!
```

### Why It Works

The integration pattern follows these steps:

1. **PHP prepares data**: Any PHP array or object is converted to JSON—a language-neutral format that both PHP and Python understand natively.

2. **Security escaping**: `escapeshellarg()` wraps the JSON string in quotes and escapes special characters, preventing shell injection attacks. This is CRITICAL—never pass user input to shell commands without escaping.

3. **Process execution**: `shell_exec()` runs the Python interpreter with your script and arguments. Python starts in a new process, executes, and terminates.

4. **Output capture**: Everything Python prints to stdout is captured by PHP as a string. By printing JSON from Python, PHP can easily parse the structured response.

5. **Error handling**: Check for `null` returns (command failed), validate JSON parsing, and use try-catch to handle failures gracefully.

This pattern works for any Python script that accepts JSON input and produces JSON output. The overhead is ~100-500ms per call depending on script complexity and Python startup time.

### Troubleshooting

**Error: "python3: command not found"**

Python 3 isn't in your system PATH. Find the correct path:

```bash
# macOS/Linux
which python3

# Use the full path in your PHP code
$command = "/usr/local/bin/python3 {$scriptPath} {$escapedJson}";
```

**Error: "No data provided"**

The argument didn't reach Python. Debug by checking the command:

```php
echo "Command: {$command}\n";  // Add before shell_exec()
```

Verify escapeshellarg() is applied—without it, JSON might be parsed as multiple arguments.

**Error: "Invalid JSON from Python"**

Python script is outputting something other than JSON (error messages, warnings, debug prints). Ensure Python only prints JSON:

```python
# Bad - mixes debug output with JSON
print("Debug info")
print(json.dumps(result))

# Good - only JSON output
import sys
print(json.dumps(result), file=sys.stdout)
```

**Performance issues**

Each call spawns a new Python process. For high-frequency calls (>10/sec), consider:

- Caching results
- Batching multiple predictions
- Switching to REST API strategy

## Step 2: Exchanging Complex Data (~10 min)

### Goal

Learn how to pass structured data (arrays, nested objects, multiple values) between PHP and Python reliably.

### Actions

1. **Create a Python script that processes structured data:**

```python
# filename: 02-data-passing/process.py
import sys
import json
from typing import Dict, Any

def process_user_data(user: Dict[str, Any]) -> Dict[str, Any]:
    """
    Example of processing complex structured data.
    In a real ML scenario, this might extract features, normalize values, etc.
    """
    # Extract and validate fields
    name = user.get('name', 'Unknown')
    age = user.get('age', 0)
    purchases = user.get('purchases', [])

    # Perform calculations
    total_spent = sum(p.get('amount', 0) for p in purchases)
    avg_purchase = total_spent / len(purchases) if purchases else 0

    # Classify user segment (simple business logic)
    if total_spent > 1000 and len(purchases) > 10:
        segment = 'VIP'
    elif total_spent > 500:
        segment = 'Regular'
    else:
        segment = 'New'

    return {
        'user_id': user.get('id'),
        'name': name,
        'segment': segment,
        'metrics': {
            'total_purchases': len(purchases),
            'total_spent': round(total_spent, 2),
            'avg_purchase_value': round(avg_purchase, 2)
        },
        'recommendations': generate_recommendations(segment)
    }

def generate_recommendations(segment: str) -> list:
    """Generate product recommendations based on segment."""
    recommendations = {
        'VIP': ['Premium Bundle', 'Exclusive Access', 'Priority Support'],
        'Regular': ['Popular Items', 'Seasonal Deals', 'Member Benefits'],
        'New': ['Starter Pack', 'Welcome Offer', 'Getting Started Guide']
    }
    return recommendations.get(segment, [])

def main():
    try:
        # Read input from PHP
        if len(sys.argv) < 2:
            raise ValueError('No input data provided')

        input_data = json.loads(sys.argv[1])

        # Process single user or batch of users
        if isinstance(input_data, dict):
            # Single user
            result = process_user_data(input_data)
        elif isinstance(input_data, list):
            # Batch of users
            result = [process_user_data(user) for user in input_data]
        else:
            raise ValueError('Input must be object or array')

        # Return result to PHP
        print(json.dumps(result, indent=2))

    except Exception as e:
        error_result = {
            'error': str(e),
            'type': type(e).__name__
        }
        print(json.dumps(error_result))
        sys.exit(1)

if __name__ == '__main__':
    main()
```

2. **Create PHP script that sends complex data:**

```php
# filename: 02-data-passing/exchange.php
<?php

declare(strict_types=1);

/**
 * Demonstrates passing complex, nested data structures between PHP and Python.
 *
 * Use cases:
 * - Feature extraction from user data
 * - Batch processing multiple records
 * - Preprocessing before ML prediction
 */

function callPythonProcessor(array $data): array
{
    $json = json_encode($data);
    $escaped = escapeshellarg($json);
    $output = shell_exec("python3 process.py {$escaped}");

    if ($output === null) {
        throw new RuntimeException('Python script execution failed');
    }

    $result = json_decode($output, true);

    if (isset($result['error'])) {
        throw new RuntimeException("Python error: {$result['error']}");
    }

    return $result;
}

// Example 1: Process a single user
echo "=== Example 1: Single User Processing ===\n\n";

$user = [
    'id' => 12345,
    'name' => 'Alice Johnson',
    'age' => 34,
    'purchases' => [
        ['product' => 'Laptop', 'amount' => 1200.00, 'date' => '2024-01-15'],
        ['product' => 'Mouse', 'amount' => 25.00, 'date' => '2024-01-15'],
        ['product' => 'Keyboard', 'amount' => 80.00, 'date' => '2024-02-03'],
        ['product' => 'Monitor', 'amount' => 350.00, 'date' => '2024-03-10'],
    ]
];

try {
    $result = callPythonProcessor($user);

    echo "User: {$result['name']}\n";
    echo "Segment: {$result['segment']}\n";
    echo "Total Purchases: {$result['metrics']['total_purchases']}\n";
    echo "Total Spent: \${$result['metrics']['total_spent']}\n";
    echo "Average Purchase: \${$result['metrics']['avg_purchase_value']}\n";
    echo "Recommendations:\n";
    foreach ($result['recommendations'] as $rec) {
        echo "  - {$rec}\n";
    }

} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n=== Example 2: Batch Processing ===\n\n";

$users = [
    [
        'id' => 1,
        'name' => 'Bob Smith',
        'purchases' => [
            ['amount' => 50],
            ['amount' => 75],
        ]
    ],
    [
        'id' => 2,
        'name' => 'Carol White',
        'purchases' => [
            ['amount' => 600],
            ['amount' => 450],
            ['amount' => 300],
        ]
    ],
];

try {
    $results = callPythonProcessor($users);

    foreach ($results as $result) {
        echo "{$result['name']}: {$result['segment']} segment ";
        echo "(\${$result['metrics']['total_spent']} total)\n";
    }

    echo "\n✅ Complex data exchange working!\n";

} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
```

3. **Run the example:**

```bash
cd docs/series/ai-ml-php-developers/code/chapter-11/02-data-passing
php exchange.php
```

### Expected Result

```
=== Example 1: Single User Processing ===

User: Alice Johnson
Segment: VIP
Total Purchases: 4
Total Spent: $1655.00
Average Purchase: $413.75
Recommendations:
  - Premium Bundle
  - Exclusive Access
  - Priority Support

=== Example 2: Batch Processing ===

Bob Smith: New segment ($125.00 total)
Carol White: Regular segment ($1350.00 total)

✅ Complex data exchange working!
```

### Why It Works

JSON is the bridge between PHP and Python data structures:

**PHP arrays map to Python lists/dicts:**

- PHP `['a', 'b', 'c']` → Python `['a', 'b', 'c']` (list)
- PHP `['key' => 'value']` → Python `{'key': 'value'}` (dict)

**Nested structures work naturally:**

- Both languages handle deeply nested JSON (objects within arrays within objects)
- Type preservation: strings stay strings, numbers stay numbers, booleans stay booleans
- `null` in PHP becomes `None` in Python

**Performance considerations:**

- JSON encoding/decoding is fast (microseconds for typical data)
- Overhead comes from process spawning, not data transfer
- For large datasets (>1MB), consider writing to temporary files instead of command-line arguments

**Type safety:**

- Use type hints in Python (`Dict[str, Any]`) for better error messages
- Validate data structure on both sides
- Handle missing keys with `.get()` (Python) or `??` (PHP)

### Troubleshooting

**Data not arriving correctly in Python**

Debug by printing what Python received:

```python
print(f"DEBUG: Received {len(sys.argv)} arguments", file=sys.stderr)
print(f"DEBUG: First arg: {sys.argv[1] if len(sys.argv) > 1 else 'NONE'}", file=sys.stderr)
```

stderr won't interfere with stdout JSON parsing.

**Encoding issues with special characters**

Ensure both PHP and Python use UTF-8:

```php
// PHP
$json = json_encode($data, JSON_UNESCAPED_UNICODE);
```

```python
# Python (usually default in Python 3)
print(json.dumps(result, ensure_ascii=False))
```

**Large data causing "Argument list too long" error**

Command-line arguments have size limits (~2MB on most systems). For larger data, use temporary files:

```php
// PHP - write to temp file
$tempFile = tempnam(sys_get_temp_dir(), 'php_python_');
file_put_contents($tempFile, json_encode($data));
$output = shell_exec("python3 process.py {$tempFile}");
unlink($tempFile);  // Clean up
```

```python
# Python - read from file
import sys
with open(sys.argv[1], 'r') as f:
    input_data = json.load(f)
```

## Step 3: Building a Sentiment Analyzer (~30 min)

### Goal

Create a complete production-ready sentiment analysis system using PHP for the web interface and Python's scikit-learn for machine learning.

### Actions

1. **Create training data:**

```csv
# filename: 03-sentiment-analysis/data/reviews.csv
text,sentiment
"This product is amazing! Exceeded all expectations.",positive
"Terrible quality. Broke after one day.",negative
"Okay product. Nothing special.",neutral
"Love it! Best purchase ever.",positive
"Waste of money. Very disappointed.",negative
"Does what it's supposed to do.",neutral
"Absolutely fantastic! Highly recommend.",positive
"Poor design and bad customer service.",negative
"Average product at a fair price.",neutral
"Incredible value! Can't believe the quality.",positive
"Arrived damaged and seller won't respond.",negative
"It's fine. Works as expected.",neutral
"Outstanding! Solved all my problems.",positive
"Cheap materials. Not worth it.",negative
"Decent product for the price.",neutral
"Exceeded my expectations! Five stars.",positive
"Horrible experience from start to finish.",negative
"Acceptable but nothing to write home about.",neutral
"Absolutely perfect! Love everything about it.",positive
"Completely useless. Total waste.",negative
"It works. That's about it.",neutral
"Best product I've ever bought!",positive
"Broke immediately. Don't buy.",negative
"Mediocre at best.",neutral
"Fantastic quality and fast shipping!",positive
"Disappointed with the quality.",negative
"It's okay for the price.",neutral
"Amazing product! Can't recommend enough.",positive
"Not as described. Very misleading.",negative
"Does the job. Nothing more.",neutral
```

2. **Create Python training script:**

```python
# filename: 03-sentiment-analysis/train_model.py
"""
Train a sentiment analysis model using scikit-learn.

This script:
1. Loads training data from CSV
2. Extracts text features using TF-IDF
3. Trains a Naive Bayes classifier
4. Saves the trained model and vectorizer for later use
"""

import sys
import json
import pandas as pd
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, accuracy_score
import joblib

def train_sentiment_model(data_path: str, model_dir: str = 'models'):
    """Train and save sentiment analysis model."""

    # Create model directory if it doesn't exist
    Path(model_dir).mkdir(parents=True, exist_ok=True)

    # Load training data
    print("Loading training data...")
    df = pd.read_csv(data_path)
    print(f"Loaded {len(df)} reviews")
    print(f"Sentiment distribution:\n{df['sentiment'].value_counts()}\n")

    # Split data
    X_train, X_test, y_train, y_test = train_test_split(
        df['text'],
        df['sentiment'],
        test_size=0.2,
        random_state=42,
        stratify=df['sentiment']
    )

    # Create TF-IDF vectorizer
    print("Creating TF-IDF features...")
    vectorizer = TfidfVectorizer(
        max_features=1000,
        ngram_range=(1, 2),  # unigrams and bigrams
        min_df=2,
        stop_words='english'
    )

    X_train_vec = vectorizer.fit_transform(X_train)
    X_test_vec = vectorizer.transform(X_test)

    # Train classifier
    print("Training Naive Bayes classifier...")
    classifier = MultinomialNB(alpha=0.1)
    classifier.fit(X_train_vec, y_train)

    # Evaluate
    print("\n=== Model Evaluation ===")
    y_pred = classifier.predict(X_test_vec)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"Test Accuracy: {accuracy:.2%}")

    print("\nClassification Report:")
    print(classification_report(y_test, y_pred))

    # Cross-validation
    cv_scores = cross_val_score(
        classifier,
        X_train_vec,
        y_train,
        cv=5,
        scoring='accuracy'
    )
    print(f"Cross-validation scores: {cv_scores}")
    print(f"Mean CV accuracy: {cv_scores.mean():.2%} (+/- {cv_scores.std() * 2:.2%})")

    # Save model and vectorizer
    model_path = Path(model_dir) / 'sentiment_model.pkl'
    vectorizer_path = Path(model_dir) / 'vectorizer.pkl'

    print(f"\nSaving model to {model_path}")
    joblib.dump(classifier, model_path)
    joblib.dump(vectorizer, vectorizer_path)

    print("✅ Training complete!")

    return {
        'accuracy': float(accuracy),
        'cv_mean': float(cv_scores.mean()),
        'cv_std': float(cv_scores.std()),
        'model_path': str(model_path),
        'vectorizer_path': str(vectorizer_path)
    }

def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Usage: python train_model.py <data_path>'}))
        sys.exit(1)

    data_path = sys.argv[1]

    try:
        result = train_sentiment_model(data_path)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == '__main__':
    main()
```

3. **Create Python prediction script:**

```python
# filename: 03-sentiment-analysis/predict.py
"""
Use trained sentiment model to predict sentiment of new text.

This script:
1. Loads the trained model and vectorizer
2. Receives text from PHP
3. Transforms text using TF-IDF
4. Predicts sentiment
5. Returns prediction with confidence scores
"""

import sys
import json
from pathlib import Path
import joblib
import numpy as np

def load_model(model_dir: str = 'models'):
    """Load trained model and vectorizer."""
    model_path = Path(model_dir) / 'sentiment_model.pkl'
    vectorizer_path = Path(model_dir) / 'vectorizer.pkl'

    if not model_path.exists() or not vectorizer_path.exists():
        raise FileNotFoundError(
            f"Model files not found in {model_dir}. "
            "Run train_model.py first."
        )

    classifier = joblib.load(model_path)
    vectorizer = joblib.load(vectorizer_path)

    return classifier, vectorizer

def predict_sentiment(text: str, classifier, vectorizer):
    """Predict sentiment for given text."""
    # Transform text to TF-IDF features
    text_vec = vectorizer.transform([text])

    # Predict sentiment
    prediction = classifier.predict(text_vec)[0]

    # Get probability scores for all classes
    probabilities = classifier.predict_proba(text_vec)[0]
    classes = classifier.classes_

    # Build confidence scores
    confidence_scores = {
        cls: float(prob)
        for cls, prob in zip(classes, probabilities)
    }

    return {
        'text': text,
        'sentiment': prediction,
        'confidence': float(probabilities.max()),
        'all_scores': confidence_scores
    }

def main():
    try:
        # Parse input from PHP
        if len(sys.argv) < 2:
            raise ValueError('No input data provided')

        input_data = json.loads(sys.argv[1])
        text = input_data.get('text', '')

        if not text:
            raise ValueError('Text field is required')

        # Load model
        classifier, vectorizer = load_model()

        # Make prediction
        result = predict_sentiment(text, classifier, vectorizer)

        # Return result
        print(json.dumps(result))

    except Exception as e:
        error_result = {
            'error': str(e),
            'type': type(e).__name__
        }
        print(json.dumps(error_result))
        sys.exit(1)

if __name__ == '__main__':
    main()
```

4. **Create PHP interface:**

```php
# filename: 03-sentiment-analysis/analyze.php
<?php

declare(strict_types=1);

/**
 * Sentiment Analysis System
 *
 * This demonstrates a complete ML integration:
 * 1. Training a model from data
 * 2. Using the trained model for predictions
 * 3. Proper error handling and validation
 * 4. Performance monitoring
 */

class SentimentAnalyzer
{
    public function __construct(
        private string $pythonPath = 'python3',
        private string $scriptDir = __DIR__,
        private string $modelDir = 'models'
    ) {}

    /**
     * Train a new sentiment model from CSV data.
     */
    public function train(string $dataPath): array
    {
        $start = microtime(true);

        echo "Training sentiment model...\n";
        echo "Data: {$dataPath}\n\n";

        $escapedPath = escapeshellarg($dataPath);
        $command = "{$this->pythonPath} {$this->scriptDir}/train_model.py {$escapedPath}";

        // Capture both stdout and stderr
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start Python training process');
        }

        fclose($pipes[0]);  // Close stdin

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        echo "--- Training Output ---\n";
        echo $stderr;  // Training prints to stderr
        echo "----------------------\n\n";

        if ($exitCode !== 0) {
            throw new RuntimeException("Training failed with exit code {$exitCode}");
        }

        // Parse result from last line of stdout
        $lines = array_filter(explode("\n", trim($stdout)));
        $lastLine = end($lines);
        $result = json_decode($lastLine, true);

        if (isset($result['error'])) {
            throw new RuntimeException("Training error: {$result['error']}");
        }

        $duration = microtime(true) - $start;
        $result['training_time'] = round($duration, 2);

        return $result;
    }

    /**
     * Predict sentiment for given text.
     */
    public function predict(string $text): array
    {
        if (empty(trim($text))) {
            throw new InvalidArgumentException('Text cannot be empty');
        }

        $start = microtime(true);

        $data = json_encode(['text' => $text]);
        $escaped = escapeshellarg($data);

        $command = "{$this->pythonPath} {$this->scriptDir}/predict.py {$escaped}";
        $output = shell_exec($command);

        if ($output === null) {
            throw new RuntimeException('Failed to execute prediction script');
        }

        $result = json_decode($output, true);

        if (isset($result['error'])) {
            throw new RuntimeException("Prediction error: {$result['error']}");
        }

        $duration = microtime(true) - $start;
        $result['prediction_time'] = round($duration * 1000, 2);  // milliseconds

        return $result;
    }

    /**
     * Batch predict sentiments for multiple texts.
     */
    public function predictBatch(array $texts): array
    {
        return array_map(
            fn(string $text) => $this->predict($text),
            $texts
        );
    }
}

// Example usage
try {
    $analyzer = new SentimentAnalyzer();

    // Step 1: Train the model
    echo "=== Step 1: Train Model ===\n";
    $trainingResult = $analyzer->train('data/reviews.csv');
    echo "✅ Training completed in {$trainingResult['training_time']}s\n";
    echo "   Test Accuracy: " . round($trainingResult['accuracy'] * 100, 1) . "%\n";
    echo "   CV Accuracy: " . round($trainingResult['cv_mean'] * 100, 1) . "% ";
    echo "(±" . round($trainingResult['cv_std'] * 100, 1) . "%)\n\n";

    // Step 2: Make predictions
    echo "=== Step 2: Predict Sentiments ===\n\n";

    $testReviews = [
        "This is absolutely wonderful! I love it so much!",
        "Terrible product. Complete waste of money.",
        "It's okay. Nothing special but it works.",
        "Best purchase I've made this year! Highly recommended!",
        "Disappointed with the quality. Not worth the price."
    ];

    foreach ($testReviews as $review) {
        $result = $analyzer->predict($review);

        $emoji = match($result['sentiment']) {
            'positive' => '😊',
            'negative' => '😞',
            'neutral' => '😐',
            default => '❓'
        };

        echo "{$emoji} {$result['sentiment']} ";
        echo "(" . round($result['confidence'] * 100, 1) . "% confident, ";
        echo "{$result['prediction_time']}ms)\n";
        echo "   \"{$review}\"\n\n";
    }

    echo "✅ Sentiment analysis complete!\n";

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

5. **Install Python dependencies and run:**

```bash
cd docs/series/ai-ml-php-developers/code/chapter-11/03-sentiment-analysis

# Install required Python packages
python3 -m pip install pandas scikit-learn joblib

# Run the complete example
php analyze.php
```

### Expected Result

```
=== Step 1: Train Model ===
Training sentiment model...
Data: data/reviews.csv

--- Training Output ---
Loading training data...
Loaded 30 reviews
Sentiment distribution:
positive    10
neutral     10
negative    10
Name: sentiment, dtype: int64

Creating TF-IDF features...
Training Naive Bayes classifier...

=== Model Evaluation ===
Test Accuracy: 100.00%

Classification Report:
              precision    recall  f1-score   support

    negative       1.00      1.00      1.00         2
     neutral       1.00      1.00      1.00         2
    positive       1.00      1.00      1.00         2

    accuracy                           1.00         6
   macro avg       1.00      1.00      1.00         6
weighted avg       1.00      1.00      1.00         6

Cross-validation scores: [0.83333333 1.         1.         1.         1.        ]
Mean CV accuracy: 96.67% (+/- 13.33%)

Saving model to models/sentiment_model.pkl
✅ Training complete!
----------------------

✅ Training completed in 0.45s
   Test Accuracy: 100.0%
   CV Accuracy: 96.7% (±13.3%)

=== Step 2: Predict Sentiments ===

😊 positive (99.9% confident, 45.32ms)
   "This is absolutely wonderful! I love it so much!"

😞 negative (99.8% confident, 42.18ms)
   "Terrible product. Complete waste of money."

😐 neutral (87.5% confident, 43.67ms)
   "It's okay. Nothing special but it works."

😊 positive (99.7% confident, 41.92ms)
   "Best purchase I've made this year! Highly recommended!"

😞 negative (95.3% confident, 42.54ms)
   "Disappointed with the quality. Not worth the price."

✅ Sentiment analysis complete!
```

### Why It Works

This sentiment analyzer demonstrates several professional patterns:

**Model Training Pipeline:**

1. **Data loading**: pandas reads CSV into a DataFrame for easy manipulation
2. **Feature extraction**: TF-IDF (Term Frequency-Inverse Document Frequency) converts text to numeric vectors that capture word importance
3. **Train/test split**: 80% training, 20% testing with stratification to maintain class balance
4. **Cross-validation**: 5-fold CV provides robust accuracy estimate beyond simple train/test split
5. **Model persistence**: joblib saves trained model and vectorizer to disk for reuse

**Prediction Service:**

1. **Model loading**: Load once (in production, load at server start, not per request)
2. **Feature transformation**: Apply same TF-IDF transformation used during training
3. **Probability scores**: Get confidence for all classes, not just top prediction
4. **Error handling**: Validate input, check model existence, handle exceptions

**PHP Integration Layer:**

1. **proc_open() for training**: Captures both stdout (results) and stderr (progress logs)
2. **shell_exec() for prediction**: Simpler API for quick predictions
3. **Timing measurements**: Monitor performance to detect slowdowns
4. **Validation**: Check inputs, validate JSON, handle errors gracefully

**Performance characteristics:**

- Training: ~0.5-2 seconds (one-time operation, can be offline)
- Prediction: ~40-50ms per text (includes Python startup ~30ms + inference ~10ms)
- Model size: ~50KB (vectorizer) + ~10KB (classifier) = tiny, loads instantly

### Troubleshooting

**Error: "ModuleNotFoundError: No module named 'sklearn'"**

Install scikit-learn:

```bash
python3 -m pip install scikit-learn

# Or with specific version
python3 -m pip install scikit-learn==1.5.0
```

**Error: "Model files not found"**

Run training first to create model files:

```bash
php analyze.php  # This runs training as Step 1
```

Or train manually:

```bash
python3 train_model.py data/reviews.csv
```

**Low accuracy on real data**

The example uses only 30 reviews. For production:

- Use 1000+ labeled examples minimum
- Balance classes (equal positive/negative/neutral)
- Use real product reviews from your domain
- Tune TF-IDF parameters (max_features, ngram_range)
- Try different classifiers (LinearSVC, RandomForest)

**Predictions take too long (>100ms)**

Optimize by:

1. **Keep Python process alive** (use REST API strategy instead of shell)
2. **Batch predictions** (process multiple texts at once)
3. **Cache common predictions** (repeat queries)
4. **Use smaller models** (reduce max_features in TF-IDF)

**Memory issues during training**

Reduce feature count:

```python
vectorizer = TfidfVectorizer(
    max_features=500,  # Reduced from 1000
    min_df=3,          # Require term in at least 3 documents
)
```

## Step 4: REST API Integration (~20 min)

### Goal

Build a Python REST API using Flask that serves ML predictions, and a PHP client that calls it.

### Actions

1. **Create Flask ML API:**

```python
# filename: 04-rest-api-example/flask_server.py
"""
Flask REST API for sentiment analysis.

This approach is more scalable than shell execution:
- Python process stays alive (no startup overhead)
- Can handle concurrent requests
- Standard HTTP protocol
- Easy to deploy separately and scale horizontally
"""

from flask import Flask, request, jsonify
from pathlib import Path
import joblib
import sys
import os

# Add parent directory to path to import model loading logic
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', '03-sentiment-analysis'))

app = Flask(__name__)

# Load model at startup (not per request!)
MODEL_DIR = '../03-sentiment-analysis/models'
classifier = None
vectorizer = None

def load_models():
    """Load trained models into memory."""
    global classifier, vectorizer

    model_path = Path(MODEL_DIR) / 'sentiment_model.pkl'
    vectorizer_path = Path(MODEL_DIR) / 'vectorizer.pkl'

    if not model_path.exists():
        raise FileNotFoundError(
            f"Model not found at {model_path}. "
            "Run training first: cd ../03-sentiment-analysis && php analyze.php"
        )

    classifier = joblib.load(model_path)
    vectorizer = joblib.load(vectorizer_path)
    print("✅ Models loaded successfully")

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint."""
    return jsonify({
        'status': 'healthy',
        'model_loaded': classifier is not None
    })

@app.route('/predict', methods=['POST'])
def predict():
    """Predict sentiment for given text."""
    try:
        # Parse request
        data = request.get_json()

        if not data or 'text' not in data:
            return jsonify({'error': 'Missing "text" field'}), 400

        text = data['text']

        if not text or not text.strip():
            return jsonify({'error': 'Text cannot be empty'}), 400

        # Transform and predict
        text_vec = vectorizer.transform([text])
        prediction = classifier.predict(text_vec)[0]
        probabilities = classifier.predict_proba(text_vec)[0]

        # Build response
        confidence_scores = {
            cls: float(prob)
            for cls, prob in zip(classifier.classes_, probabilities)
        }

        return jsonify({
            'text': text,
            'sentiment': prediction,
            'confidence': float(probabilities.max()),
            'all_scores': confidence_scores
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/predict/batch', methods=['POST'])
def predict_batch():
    """Predict sentiments for multiple texts."""
    try:
        data = request.get_json()

        if not data or 'texts' not in data:
            return jsonify({'error': 'Missing "texts" array'}), 400

        texts = data['texts']

        if not isinstance(texts, list):
            return jsonify({'error': '"texts" must be an array'}), 400

        # Transform all texts at once (efficient)
        texts_vec = vectorizer.transform(texts)
        predictions = classifier.predict(texts_vec)
        probabilities = classifier.predict_proba(texts_vec)

        # Build results
        results = []
        for text, pred, probs in zip(texts, predictions, probabilities):
            results.append({
                'text': text,
                'sentiment': pred,
                'confidence': float(probs.max())
            })

        return jsonify({'predictions': results})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting Flask ML API...")
    load_models()
    app.run(host='127.0.0.1', port=5000, debug=True)
```

2. **Create PHP client:**

```php
# filename: 04-rest-api-example/php_client.php
<?php

declare(strict_types=1);

/**
 * PHP client for Flask ML API.
 *
 * Advantages over shell execution:
 * - Lower latency (no process spawn)
 * - Better for high traffic
 * - Can use load balancing
 * - Standard HTTP protocol
 */

class MLApiClient
{
    public function __construct(
        private string $baseUrl = 'http://127.0.0.1:5000',
        private int $timeout = 30
    ) {}

    /**
     * Check if API is healthy and ready.
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->get('/health');
            return $response['status'] === 'healthy' && $response['model_loaded'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Predict sentiment for a single text.
     */
    public function predict(string $text): array
    {
        return $this->post('/predict', ['text' => $text]);
    }

    /**
     * Predict sentiments for multiple texts (efficient batch operation).
     */
    public function predictBatch(array $texts): array
    {
        $response = $this->post('/predict/batch', ['texts' => $texts]);
        return $response['predictions'];
    }

    /**
     * Make GET request to API.
     */
    private function get(string $endpoint): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("API request failed: {$error}");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("API returned HTTP {$httpCode}");
        }

        return json_decode($response, true);
    }

    /**
     * Make POST request to API.
     */
    private function post(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;
        $json = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("API request failed: {$error}");
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error'] ?? 'Unknown error';
            throw new RuntimeException("API error ({$httpCode}): {$errorMsg}");
        }

        return $result;
    }
}

// Example usage
try {
    $client = new MLApiClient();

    echo "=== Flask API Client Demo ===\n\n";

    // Check API health
    echo "Checking API health... ";
    if (!$client->healthCheck()) {
        throw new RuntimeException(
            "API is not available. Start it with:\n" .
            "  cd 04-rest-api-example\n" .
            "  python3 flask_server.py"
        );
    }
    echo "✅ API is healthy\n\n";

    // Single prediction
    echo "=== Single Prediction ===\n";
    $start = microtime(true);
    $result = $client->predict("This API is fantastic! Works perfectly.");
    $duration = (microtime(true) - $start) * 1000;

    echo "Text: {$result['text']}\n";
    echo "Sentiment: {$result['sentiment']} ";
    echo "(" . round($result['confidence'] * 100, 1) . "% confident)\n";
    echo "Latency: " . round($duration, 2) . "ms\n\n";

    // Batch prediction
    echo "=== Batch Prediction ===\n";
    $texts = [
        "Amazing product!",
        "Terrible experience.",
        "It's okay.",
        "Highly recommended!",
        "Not satisfied."
    ];

    $start = microtime(true);
    $results = $client->predictBatch($texts);
    $duration = (microtime(true) - $start) * 1000;

    foreach ($results as $result) {
        echo "• {$result['sentiment']}: \"{$result['text']}\"\n";
    }
    echo "\nProcessed " . count($results) . " texts in " . round($duration, 2) . "ms\n";
    echo "Average: " . round($duration / count($results), 2) . "ms per text\n\n";

    echo "✅ API integration working!\n";

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

3. **Start the Flask server:**

```bash
cd docs/series/ai-ml-php-developers/code/chapter-11/04-rest-api-example

# Install Flask
python3 -m pip install flask

# Start server (runs in foreground)
python3 flask_server.py
```

4. **In a separate terminal, run PHP client:**

```bash
cd docs/series/ai-ml-php-developers/code/chapter-11/04-rest-api-example
php php_client.php
```

### Expected Result

**Flask server output:**

```
Starting Flask ML API...
✅ Models loaded successfully
 * Running on http://127.0.0.1:5000
```

**PHP client output:**

```
=== Flask API Client Demo ===

Checking API health... ✅ API is healthy

=== Single Prediction ===
Text: This API is fantastic! Works perfectly.
Sentiment: positive (99.5% confident)
Latency: 12.34ms

=== Batch Prediction ===
• positive: "Amazing product!"
• negative: "Terrible experience."
• neutral: "It's okay."
• positive: "Highly recommended!"
• negative: "Not satisfied."

Processed 5 texts in 45.67ms
Average: 9.13ms per text

✅ API integration working!
```

### Why It Works

REST API approach offers significant advantages:

**Performance improvements:**

- **No process spawning**: Python stays running, saving ~30ms per request
- **Model loaded once**: Model lives in memory, not reloaded per request
- **Batch operations**: Process multiple texts in single request efficiently
- **Latency**: ~10-15ms vs ~40-50ms for shell execution

**Scalability benefits:**

- **Concurrent requests**: Flask handles multiple PHP requests simultaneously
- **Horizontal scaling**: Run multiple API instances behind load balancer
- **Independent deployment**: Update Python service without touching PHP code
- **Language agnostic**: Any language can call HTTP API (Node.js, Go, Ruby, etc.)

**Production patterns:**

- **Health checks**: Monitor service availability
- **Standard HTTP**: Use existing infrastructure (reverse proxies, monitoring)
- **Error codes**: HTTP status codes indicate error types
- **Versioning**: Add `/v1/predict` endpoints for API versioning

**Trade-offs:**

- **Complexity**: Must deploy and manage separate service
- **Network overhead**: HTTP adds ~1-5ms latency
- **Port management**: Need to configure ports and firewalls
- **Service discovery**: PHP needs to know API location

### Troubleshooting

**Error: "Connection refused"**

Flask server isn't running. Start it:

```bash
python3 flask_server.py
```

Verify it's listening:

```bash
curl http://127.0.0.1:5000/health
```

**Error: "Model not found"**

Train the model first:

```bash
cd ../03-sentiment-analysis
php analyze.php  # This trains the model
```

**Port already in use**

Change port in both files:

```python
# flask_server.py
app.run(host='127.0.0.1', port=5001)  # Changed from 5000
```

```php
// php_client.php
$client = new MLApiClient(baseUrl: 'http://127.0.0.1:5001');
```

**Slow batch predictions**

Flask development server is single-threaded. For production, use a production WSGI server:

```bash
# Install gunicorn
python3 -m pip install gunicorn

# Run with 4 worker processes
gunicorn -w 4 -b 127.0.0.1:5000 flask_server:app
```

**API crashes under load**

Add error handling and request validation:

```python
@app.errorhandler(Exception)
def handle_error(e):
    return jsonify({'error': str(e)}), 500

# Add request size limits
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16MB max
```

## Step 5: Message Queue Pattern (~15 min)

### Goal

Understand asynchronous processing using message queues for long-running ML tasks.

### Actions

1. **Create async queue example:**

```php
# filename: 05-production-patterns/async_queue.php
<?php

declare(strict_types=1);

/**
 * Message Queue Pattern for Async ML Processing
 *
 * Use this pattern when:
 * - ML task takes >5 seconds (model training, large predictions)
 * - User doesn't need immediate result
 * - High throughput is required
 * - Results can be delivered later (callback, polling, email)
 *
 * This example uses Redis as the message queue.
 * Production alternatives: RabbitMQ, AWS SQS, Google Pub/Sub
 */

class AsyncMLQueue
{
    private Redis $redis;

    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379
    ) {
        if (!extension_loaded('redis')) {
            throw new RuntimeException(
                'Redis extension required. Install: pecl install redis'
            );
        }

        $this->redis = new Redis();
        if (!$this->redis->connect($host, $port)) {
            throw new RuntimeException("Failed to connect to Redis at {$host}:{$port}");
        }
    }

    /**
     * Submit ML task to queue for async processing.
     */
    public function submitTask(string $taskType, array $data, ?string $callbackUrl = null): string
    {
        $taskId = $this->generateTaskId();

        $task = [
            'id' => $taskId,
            'type' => $taskType,
            'data' => $data,
            'callback_url' => $callbackUrl,
            'submitted_at' => time(),
            'status' => 'pending'
        ];

        // Add task to queue
        $this->redis->lPush('ml_tasks', json_encode($task));

        // Store task metadata for status checking
        $this->redis->setex(
            "task:{$taskId}",
            3600,  // 1 hour TTL
            json_encode($task)
        );

        return $taskId;
    }

    /**
     * Check status of submitted task.
     */
    public function getTaskStatus(string $taskId): ?array
    {
        $data = $this->redis->get("task:{$taskId}");
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Get result of completed task.
     */
    public function getTaskResult(string $taskId): ?array
    {
        $data = $this->redis->get("result:{$taskId}");
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Worker: Process tasks from queue (this would run in Python).
     *
     * This is PHP pseudocode showing the worker pattern.
     * Real implementation would be in Python worker process.
     */
    public function processTasksWorker(): void
    {
        echo "Worker started. Listening for tasks...\n";

        while (true) {
            // Blocking pop with 1 second timeout
            $taskData = $this->redis->brPop(['ml_tasks'], 1);

            if (!$taskData) {
                continue;  // No task, keep waiting
            }

            $task = json_decode($taskData[1], true);
            echo "Processing task {$task['id']} ({$task['type']})...\n";

            try {
                // Update status to processing
                $task['status'] = 'processing';
                $task['started_at'] = time();
                $this->redis->setex("task:{$task['id']}", 3600, json_encode($task));

                // Process task (call Python script, do ML work)
                $result = $this->executeMLTask($task);

                // Store result
                $this->redis->setex(
                    "result:{$task['id']}",
                    3600,
                    json_encode($result)
                );

                // Update task status
                $task['status'] = 'completed';
                $task['completed_at'] = time();
                $this->redis->setex("task:{$task['id']}", 3600, json_encode($task));

                // Callback if URL provided
                if ($task['callback_url']) {
                    $this->sendCallback($task['callback_url'], $result);
                }

                echo "✅ Task {$task['id']} completed\n";

            } catch (Exception $e) {
                echo "❌ Task {$task['id']} failed: {$e->getMessage()}\n";

                $task['status'] = 'failed';
                $task['error'] = $e->getMessage();
                $this->redis->setex("task:{$task['id']}", 3600, json_encode($task));
            }
        }
    }

    private function executeMLTask(array $task): array
    {
        // In reality, this would call Python script or API
        // For demo, simulate processing
        sleep(2);  // Simulate long ML task

        return [
            'task_id' => $task['id'],
            'result' => 'Task completed',
            'processed_at' => time()
        ];
    }

    private function sendCallback(string $url, array $result): void
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($result));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    private function generateTaskId(): string
    {
        return bin2hex(random_bytes(16));
    }
}

// Example usage
try {
    echo "=== Async ML Queue Pattern ===\n\n";

    $queue = new AsyncMLQueue();

    // Submit tasks
    echo "Submitting tasks...\n";

    $taskId1 = $queue->submitTask('sentiment_analysis', [
        'text' => 'Analyze this review for sentiment'
    ]);
    echo "Task 1 submitted: {$taskId1}\n";

    $taskId2 = $queue->submitTask('image_classification', [
        'image_url' => 'https://example.com/image.jpg'
    ]);
    echo "Task 2 submitted: {$taskId2}\n\n";

    // Check status
    echo "Checking task status...\n";
    $status1 = $queue->getTaskStatus($taskId1);
    echo "Task {$taskId1}: {$status1['status']}\n";
    echo "Type: {$status1['type']}\n";
    echo "Submitted: " . date('Y-m-d H:i:s', $status1['submitted_at']) . "\n\n";

    echo "✅ Tasks queued for async processing\n\n";

    echo "In production:\n";
    echo "  1. Python workers continuously poll queue\n";
    echo "  2. Workers process tasks and store results\n";
    echo "  3. PHP checks results by task ID or receives callback\n";
    echo "  4. Scale by adding more workers\n";

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";

    if (!extension_loaded('redis')) {
        echo "\nRedis extension not installed. This is normal for demo.\n";
        echo "For production use, install Redis:\n";
        echo "  brew install redis  # macOS\n";
        echo "  apt install redis-server  # Ubuntu\n";
        echo "  pecl install redis  # PHP extension\n";
    }
}
```

2. **Create Python worker example:**

```python
# filename: 05-production-patterns/worker.py
"""
Python worker that processes tasks from Redis queue.

This runs continuously in the background:
1. Polls Redis queue for new tasks
2. Executes ML tasks (prediction, training, etc.)
3. Stores results back in Redis
4. Sends callbacks if provided

Usage:
    python3 worker.py

Run multiple workers for parallel processing:
    python3 worker.py &
    python3 worker.py &
    python3 worker.py &
"""

import redis
import json
import time
import sys
from pathlib import Path

# Add sentiment analysis to path
sys.path.insert(0, str(Path(__file__).parent.parent / '03-sentiment-analysis'))

try:
    import joblib
    SENTIMENT_MODEL = None
    SENTIMENT_VECTORIZER = None

    # Try to load sentiment model
    model_path = Path(__file__).parent.parent / '03-sentiment-analysis' / 'models' / 'sentiment_model.pkl'
    if model_path.exists():
        SENTIMENT_MODEL = joblib.load(model_path)
        vectorizer_path = model_path.parent / 'vectorizer.pkl'
        SENTIMENT_VECTORIZER = joblib.load(vectorizer_path)
        print("✅ Sentiment model loaded")
except ImportError:
    print("⚠️  joblib not installed. Sentiment tasks will be simulated.")

def process_sentiment_analysis(data):
    """Process sentiment analysis task."""
    text = data.get('text', '')

    if SENTIMENT_MODEL and SENTIMENT_VECTORIZER:
        # Real prediction
        text_vec = SENTIMENT_VECTORIZER.transform([text])
        prediction = SENTIMENT_MODEL.predict(text_vec)[0]
        probabilities = SENTIMENT_MODEL.predict_proba(text_vec)[0]

        return {
            'text': text,
            'sentiment': prediction,
            'confidence': float(probabilities.max())
        }
    else:
        # Simulated prediction
        return {
            'text': text,
            'sentiment': 'positive',
            'confidence': 0.85,
            'note': 'Simulated (model not loaded)'
        }

def process_task(task):
    """Process a task based on its type."""
    task_type = task['type']
    data = task['data']

    if task_type == 'sentiment_analysis':
        return process_sentiment_analysis(data)
    elif task_type == 'image_classification':
        # Simulated
        time.sleep(1)
        return {'label': 'cat', 'confidence': 0.92}
    else:
        raise ValueError(f"Unknown task type: {task_type}")

def main():
    """Main worker loop."""
    print("🔧 Starting ML Worker...")

    # Connect to Redis
    try:
        r = redis.Redis(host='127.0.0.1', port=6379, decode_responses=True)
        r.ping()
        print("✅ Connected to Redis")
    except redis.ConnectionError:
        print("❌ Failed to connect to Redis")
        print("   Start Redis: brew services start redis  # macOS")
        return

    print("👂 Listening for tasks on queue 'ml_tasks'...\n")

    while True:
        try:
            # Blocking pop with 1 second timeout
            task_data = r.brpop('ml_tasks', timeout=1)

            if not task_data:
                continue  # No task, keep waiting

            task = json.loads(task_data[1])
            task_id = task['id']

            print(f"📥 Received task {task_id} ({task['type']})")

            # Update status
            task['status'] = 'processing'
            task['started_at'] = int(time.time())
            r.setex(f"task:{task_id}", 3600, json.dumps(task))

            # Process task
            result = process_task(task)

            # Store result
            r.setex(f"result:{task_id}", 3600, json.dumps(result))

            # Update task status
            task['status'] = 'completed'
            task['completed_at'] = int(time.time())
            r.setex(f"task:{task_id}", 3600, json.dumps(task))

            print(f"✅ Completed task {task_id}\n")

        except KeyboardInterrupt:
            print("\n🛑 Worker stopped")
            break
        except Exception as e:
            print(f"❌ Error processing task: {e}\n")
            if 'task_id' in locals():
                task['status'] = 'failed'
                task['error'] = str(e)
                r.setex(f"task:{task_id}", 3600, json.dumps(task))

if __name__ == '__main__':
    main()
```

### Expected Result

This pattern is for **production async processing**. To demo:

1. **Start Redis** (if installed):

```bash
redis-server
```

2. **Run PHP to submit tasks:**

```bash
php async_queue.php
```

Output:

```
=== Async ML Queue Pattern ===

Submitting tasks...
Task 1 submitted: a3f2c91b4d8e...
Task 2 submitted: 7e8f1c2a9b6d...

Checking task status...
Task a3f2c91b4d8e...: pending
Type: sentiment_analysis
Submitted: 2024-10-28 14:30:25

✅ Tasks queued for async processing
```

3. **Run Python worker:**

```bash
python3 worker.py
```

Worker output:

```
🔧 Starting ML Worker...
✅ Connected to Redis
✅ Sentiment model loaded
👂 Listening for tasks on queue 'ml_tasks'...

📥 Received task a3f2c91b4d8e (sentiment_analysis)
✅ Completed task a3f2c91b4d8e

📥 Received task 7e8f1c2a9b6d (image_classification)
✅ Completed task 7e8f1c2a9b6d
```

### Why It Works

Message queue pattern provides enterprise-grade async processing:

**Architecture:**

```
PHP Web App → Redis Queue → Python Workers → Redis Results → PHP Web App
```

**Benefits:**

- **Decoupled**: Web requests don't wait for ML processing
- **Scalable**: Add workers to handle more load
- **Resilient**: Tasks survive crashes, can be retried
- **Flexible**: Different worker types for different tasks

**Use cases:**

- Model training (minutes to hours)
- Batch predictions (thousands of items)
- Video/audio processing
- Report generation
- Data pipeline jobs

**Workflow:**

1. **Submit**: PHP creates task and adds to queue
2. **Queue**: Task waits in Redis list
3. **Process**: Worker pops task and executes
4. **Store**: Result saved to Redis with TTL
5. **Retrieve**: PHP polls for result or receives callback

### Troubleshooting

**Redis not installed**

This is normal for demos. In production:

```bash
# macOS
brew install redis
brew services start redis

# Ubuntu
sudo apt install redis-server
sudo systemctl start redis

# PHP extension
pecl install redis
```

**Tasks not being processed**

Ensure worker is running:

```bash
python3 worker.py &  # Run in background
```

Check queue has tasks:

```bash
redis-cli LLEN ml_tasks
```

**Results expiring too quickly**

Increase TTL:

```php
// Store for 24 hours instead of 1 hour
$this->redis->setex("result:{$taskId}", 86400, json_encode($result));
```

**Need guaranteed delivery**

Use RabbitMQ instead of Redis for:

- Message acknowledgments
- Persistent queues
- Dead letter queues
- Priority queues

## Exercises

### Exercise 1: Multi-Model Sentiment Analyzer

**Goal**: Extend the sentiment analyzer to compare multiple classifiers and choose the best one.

Modify `03-sentiment-analysis/train_model.py` to train three classifiers:

- Naive Bayes (current)
- Logistic Regression
- Support Vector Machine (LinearSVC)

Requirements:

- Train all three models on the same data
- Compare accuracy, precision, recall
- Save the best-performing model
- Update predict.py to use the best model

**Validation**: Your training script should output:

```
Naive Bayes Accuracy: 95.0%
Logistic Regression Accuracy: 98.0%
LinearSVC Accuracy: 97.5%

Best model: Logistic Regression
Saved to models/sentiment_model.pkl
```

**Hint**: Import from sklearn:

```python
from sklearn.naive_bayes import MultinomialNB
from sklearn.linear_model import LogisticRegression
from sklearn.svm import LinearSVC
```

### Exercise 2: Caching Layer

**Goal**: Add intelligent caching to reduce redundant ML calls.

Create `cache_layer.php` that:

- Caches prediction results using the text hash as key
- Sets TTL (time-to-live) of 1 hour
- Falls back to ML prediction on cache miss
- Tracks cache hit rate

Requirements:

- Use file-based caching (or Redis if available)
- Hash function: `md5($text)`
- Cache structure: `['text' => ..., 'sentiment' => ..., 'cached_at' => ...]`
- Print cache stats (hits/misses/hit_rate)

**Validation**: Running the same texts twice should show:

```
First run:
  Cache misses: 5
  ML predictions: 5

Second run:
  Cache hits: 5
  ML predictions: 0
  Cache hit rate: 100%
```

### Exercise 3: Health Monitoring

**Goal**: Build a health check system that monitors Python service availability.

Create `health_monitor.php` that:

- Checks if Python is installed and working
- Verifies sentiment model files exist
- Tests prediction with sample text
- Measures response time
- Reports overall system health

Requirements:

- Check Python version >= 3.10
- Check required packages (scikit-learn, pandas, joblib)
- Test prediction latency < 200ms
- Output: healthy/degraded/unhealthy status

**Validation**: Should produce:

```
=== System Health Check ===

✅ Python 3.11.4 detected
✅ Required packages installed
✅ Model files found
✅ Test prediction successful (45ms)

Overall Status: HEALTHY
```

## Wrap-up

Congratulations! You've mastered integrating PHP with Python for machine learning. Let's review what you've accomplished:

✅ **Basic Integration**: Built shell-based communication between PHP and Python using JSON

✅ **Data Exchange**: Passed complex, nested data structures reliably between languages

✅ **Production ML System**: Created a complete sentiment analyzer with training and prediction

✅ **REST API**: Deployed a Flask API for scalable, low-latency predictions

✅ **Async Processing**: Learned message queue patterns for background ML tasks

✅ **Security**: Applied input validation, shell escaping, and error handling

✅ **Performance**: Understood trade-offs and measured latency for each approach

✅ **Real-world Patterns**: Gained production-ready code for hybrid PHP-Python applications

### Key Takeaways

**Choose the right integration strategy:**

- **Shell execution** for simple tasks, development, low traffic
- **REST API** for production, high traffic, real-time predictions
- **Message queues** for async, long-running, batch processing

**Always consider:**

- Security (escape inputs, validate data)
- Error handling (Python failures, network issues)
- Performance (latency, throughput, scalability)
- Monitoring (health checks, logging, metrics)

**Best practices:**

- Keep Python processes alive when possible (API > shell)
- Cache expensive predictions
- Batch operations for efficiency
- Use proper data serialization (JSON)
- Handle failures gracefully with fallbacks

### Real-World Applications

You can now build systems like:

- **E-commerce**: Sentiment analysis on product reviews
- **Content platforms**: Automatic content moderation with Python NLP
- **Marketing**: Customer segmentation using Python clustering
- **Analytics**: Time series forecasting with Python statsmodels
- **Computer vision**: Image tagging using Python OpenCV/TensorFlow
- **Recommendations**: Collaborative filtering with Python surprise library

### Next Steps

In [Chapter 12: Deep Learning with TensorFlow and PHP](/series/ai-ml-php-developers/chapters/12-deep-learning-with-tensorflow-and-php), you'll apply these integration techniques to use cutting-edge deep learning models. You'll load pre-trained neural networks, run inference from PHP, and build an image classification API using TensorFlow.

The patterns you learned here—REST APIs, async processing, proper error handling—are the foundation for enterprise ML systems. You're ready to build production applications that leverage the best of PHP and Python!

## Further Reading

**Python ML Libraries:**

- [scikit-learn Documentation](https://scikit-learn.org/stable/) — Comprehensive ML library docs
- [pandas Documentation](https://pandas.pydata.org/docs/) — Data manipulation and analysis
- [Flask Documentation](https://flask.palletsprojects.com/) — Web framework for APIs
- [FastAPI](https://fastapi.tiangolo.com/) — Modern, fast web framework (alternative to Flask)

**Integration Patterns:**

- [PHP proc_open Documentation](https://www.php.net/manual/en/function.proc-open.php) — Advanced process control
- [PHP cURL Documentation](https://www.php.net/manual/en/book.curl.php) — HTTP client
- [Redis Documentation](https://redis.io/docs/) — In-memory data store for queues

**Security:**

- [OWASP Command Injection](https://owasp.org/www-community/attacks/Command_Injection) — Preventing shell injection
- [PHP escapeshellarg Documentation](https://www.php.net/manual/en/function.escapeshellarg.php) — Proper escaping

**Production Deployment:**

- [Gunicorn](https://gunicorn.org/) — Python WSGI HTTP Server
- [Supervisor](http://supervisord.org/) — Process manager for workers
- [Docker](https://docs.docker.com/) — Containerization for deployment

**Advanced Topics:**

- [gRPC](https://grpc.io/) — High-performance RPC framework (alternative to REST)
- [Apache Kafka](https://kafka.apache.org/) — Distributed event streaming for large-scale queues
- [Celery](https://docs.celeryq.dev/) — Distributed task queue for Python
