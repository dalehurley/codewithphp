# Chapter 11: Integrating PHP with Python for Advanced ML - Code Examples

This directory contains all working code examples for Chapter 11, demonstrating three integration strategies for combining PHP web applications with Python machine learning capabilities.

## Directory Structure

```
chapter-11/
├── 01-simple-shell/          # Basic shell execution examples
├── 02-data-passing/          # Complex data exchange patterns
├── 03-sentiment-analysis/    # Complete sentiment analyzer project
├── 04-rest-api-example/      # Flask REST API with PHP client
├── 05-production-patterns/   # Async message queue patterns
├── solutions/                # Exercise solutions
└── README.md                 # This file
```

## Prerequisites

### Required Software

- **PHP 8.4+** with `curl` extension
- **Python 3.10+**
- **pip** (Python package manager)

### Python Packages

Install required Python packages:

```bash
# For sentiment analysis examples (03-sentiment-analysis, 04-rest-api-example)
pip install pandas scikit-learn joblib

# For Flask REST API (04-rest-api-example)
pip install flask

# For message queue worker (05-production-patterns)
pip install redis
```

Or install all at once:

```bash
cd docs/series/ai-ml-php-developers/code/chapter-11
pip install -r requirements.txt
```

### Verification

```bash
# Check PHP version
php --version  # Should be 8.4+

# Check Python version
python3 --version  # Should be 3.10+

# Check Python packages
python3 -c "import sklearn, pandas, joblib; print('✅ ML packages installed')"
```

## Quick Start

### 1. Basic Integration (5 minutes)

The simplest PHP-Python communication:

```bash
cd 01-simple-shell
php hello.php
```

Expected output:

```
=== Basic PHP-Python Integration ===

Test 1 - Simple greeting:
  Hello, PHP Developer!
  Processed by: Python 3

✅ Integration working successfully!
```

### 2. Data Exchange (5 minutes)

Passing complex data structures:

```bash
cd 02-data-passing
php exchange.php
```

### 3. Sentiment Analyzer (15 minutes)

Complete ML system with training and prediction:

```bash
cd 03-sentiment-analysis

# Install Python ML packages if not already installed
pip install pandas scikit-learn joblib

# Run the complete example (trains model, makes predictions)
php analyze.php
```

**First run:** Trains a sentiment model on 30 product reviews, then makes predictions.

**Subsequent runs:** Uses the trained model (stored in `models/` directory).

### 4. REST API (10 minutes)

Scalable HTTP-based integration:

```bash
cd 04-rest-api-example

# Terminal 1 - Start Flask server
pip install flask
python3 flask_server.py

# Terminal 2 - Run PHP client
php php_client.php
```

### 5. Async Queue (Optional - requires Redis)

Production async processing pattern:

```bash
# Start Redis server
redis-server  # or: brew services start redis

# Terminal 1 - Start worker
cd 05-production-patterns
pip install redis
python3 worker.py

# Terminal 2 - Submit tasks
php async_queue.php
```

## Example Walkthrough

### Example 1: Simple Shell Execution

**Files:**

- `01-simple-shell/hello.php` - PHP script that calls Python
- `01-simple-shell/hello.py` - Python script that processes data

**Pattern:**

1. PHP prepares data as JSON
2. PHP calls Python with `shell_exec()`
3. Python processes and returns JSON
4. PHP parses result

**Key concepts:**

- JSON as data interchange format
- `escapeshellarg()` for security
- Error handling on both sides

### Example 2: Data Exchange

**Files:**

- `02-data-passing/exchange.php` - PHP sends complex data
- `02-data-passing/process.py` - Python processes structured data

**Demonstrates:**

- Nested arrays and objects
- Batch processing
- Feature extraction patterns
- User segmentation logic

### Example 3: Sentiment Analysis (Main Project)

**Files:**

- `analyze.php` - PHP orchestration layer
- `train_model.py` - Trains scikit-learn classifier
- `predict.py` - Makes predictions with trained model
- `data/reviews.csv` - Training data (30 product reviews)

**Workflow:**

```
1. Training (one-time):
   PHP → train_model.py → [loads CSV] → [TF-IDF features] → [train Naive Bayes]
   → [cross-validation] → [save model.pkl] → results back to PHP

2. Prediction (real-time):
   PHP → predict.py → [load model.pkl] → [transform text] → [predict sentiment]
   → [confidence scores] → result back to PHP
```

**Model Details:**

- Algorithm: Multinomial Naive Bayes
- Features: TF-IDF (1000 features, unigrams + bigrams)
- Classes: positive, negative, neutral
- Accuracy: ~95-100% on test set
- Prediction latency: ~40-50ms

**Try it yourself:**

```bash
cd 03-sentiment-analysis
php analyze.php
```

Then modify `data/reviews.csv` to add your own training data!

### Example 4: REST API

**Architecture:**

```
PHP Web App → HTTP → Flask API (Python) → ML Model → HTTP Response → PHP
```

**Advantages over shell execution:**

- 3-5x faster (no process spawning)
- Handles concurrent requests
- Scalable (multiple workers, load balancing)
- Standard HTTP protocol

**Flask Server (`flask_server.py`):**

- Loads models at startup (not per request)
- `/health` - Health check endpoint
- `/predict` - Single prediction
- `/predict/batch` - Batch predictions

**PHP Client (`php_client.php`):**

- Checks API availability
- Makes HTTP POST requests with cURL
- Handles errors and timeouts
- Measures latency

**Production Deployment:**

```bash
# Development (single-threaded)
python3 flask_server.py

# Production (4 workers)
pip install gunicorn
gunicorn -w 4 -b 127.0.0.1:5000 flask_server:app
```

### Example 5: Async Queue Pattern

**Architecture:**

```
PHP → Redis Queue → Python Worker → Redis Results → PHP
```

**Use cases:**

- Model training (minutes to hours)
- Batch predictions (thousands of items)
- Video/audio processing
- Long-running computations

**Workflow:**

1. PHP submits task to Redis queue
2. Task waits in queue
3. Python worker picks up task
4. Worker processes and stores result
5. PHP polls for result or receives callback

**Scaling:**
Run multiple workers in parallel:

```bash
python3 worker.py &
python3 worker.py &
python3 worker.py &
```

## Integration Strategy Comparison

| Feature            | Shell Execution           | REST API        | Message Queue           |
| ------------------ | ------------------------- | --------------- | ----------------------- |
| **Complexity**     | Low                       | Medium          | High                    |
| **Latency**        | ~40-50ms                  | ~10-15ms        | Async (seconds-minutes) |
| **Scalability**    | Limited                   | High            | Very High               |
| **Infrastructure** | None                      | Web server      | Queue system            |
| **Best for**       | Development, simple tasks | Production APIs | Background jobs         |

## Common Tasks

### Train a new sentiment model

```bash
cd 03-sentiment-analysis
python3 train_model.py data/reviews.csv
```

### Test a single prediction

```bash
cd 03-sentiment-analysis
python3 -c "
import json
print(json.dumps({'text': 'This is amazing!'}))
" | xargs -I {} python3 predict.py '{}'
```

### Run REST API in production mode

```bash
cd 04-rest-api-example
pip install gunicorn
gunicorn -w 4 -b 0.0.0.0:5000 --timeout 60 flask_server:app
```

### Clear Redis queue

```bash
redis-cli DEL ml_tasks
redis-cli KEYS "task:*" | xargs redis-cli DEL
redis-cli KEYS "result:*" | xargs redis-cli DEL
```

## Troubleshooting

### "python3: command not found"

Python 3 isn't in PATH. Find the correct path:

```bash
which python3  # macOS/Linux
where python   # Windows
```

Use full path in PHP:

```php
$command = "/usr/local/bin/python3 script.py";
```

### "ModuleNotFoundError: No module named 'sklearn'"

Install scikit-learn:

```bash
pip install scikit-learn pandas joblib
```

Or ensure you're using the same Python interpreter:

```bash
# Check which Python PHP is calling
php -r "echo shell_exec('which python3');"

# Install to that Python
/usr/local/bin/python3 -m pip install scikit-learn
```

### "Model files not found"

Run training first:

```bash
cd 03-sentiment-analysis
php analyze.php  # This trains the model
```

Or train manually:

```bash
python3 train_model.py data/reviews.csv
```

### Predictions are slow (>100ms)

**For shell execution:**

- Each call spawns new Python process (~30ms overhead)
- Consider caching predictions
- Switch to REST API for high-traffic use

**For REST API:**

- Ensure model is loaded at startup, not per request
- Use batch predictions for multiple texts
- Run with multiple workers (gunicorn -w 4)

### Flask API connection refused

Ensure server is running:

```bash
curl http://127.0.0.1:5000/health
```

If not running, start it:

```bash
cd 04-rest-api-example
python3 flask_server.py
```

### Redis connection error

Install and start Redis:

```bash
# macOS
brew install redis
brew services start redis

# Ubuntu
sudo apt install redis-server
sudo systemctl start redis

# Verify
redis-cli ping  # Should return "PONG"
```

### PHP "Class 'Redis' not found"

Install PHP Redis extension:

```bash
pecl install redis
echo "extension=redis.so" >> $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
```

## Performance Benchmarks

Typical latencies on modern hardware (MacBook Pro M1):

| Operation         | Shell Execution | REST API  | Notes                     |
| ----------------- | --------------- | --------- | ------------------------- |
| Python startup    | ~30ms           | ~0ms      | API keeps process alive   |
| Model loading     | ~10ms           | ~0ms      | API loads once at startup |
| Feature transform | ~5ms            | ~5ms      | Same                      |
| Prediction        | ~5ms            | ~5ms      | Same                      |
| **Total**         | **~50ms**       | **~15ms** | API is 3x faster          |

Batch operations (5 texts):

| Method             | Latency | Per-text |
| ------------------ | ------- | -------- |
| Shell (sequential) | ~250ms  | ~50ms    |
| Shell (batch)      | ~60ms   | ~12ms    |
| API (sequential)   | ~75ms   | ~15ms    |
| API (batch)        | ~20ms   | ~4ms     |

## Security Considerations

**Always escape shell arguments:**

```php
// ❌ NEVER DO THIS (shell injection vulnerability)
$output = shell_exec("python3 script.py {$userInput}");

// ✅ ALWAYS DO THIS
$escaped = escapeshellarg($userInput);
$output = shell_exec("python3 script.py {$escaped}");
```

**Validate input data:**

```php
// Validate text length
if (strlen($text) > 10000) {
    throw new InvalidArgumentException('Text too long');
}

// Sanitize special characters
$text = filter_var($text, FILTER_SANITIZE_STRING);
```

**Handle Python errors:**

```python
# Python: Always catch exceptions
try:
    result = do_ml_work(data)
    print(json.dumps(result))
except Exception as e:
    print(json.dumps({'error': str(e)}))
    sys.exit(1)
```

## Next Steps

After completing these examples:

1. **Customize the sentiment model:**

   - Add your own training data to `data/reviews.csv`
   - Experiment with different algorithms (Logistic Regression, SVM)
   - Tune TF-IDF parameters

2. **Deploy to production:**

   - Containerize with Docker
   - Set up Nginx reverse proxy
   - Add monitoring and logging
   - Implement rate limiting

3. **Extend functionality:**

   - Multi-language support
   - Aspect-based sentiment (identify specific features)
   - Emoji detection and handling
   - Confidence thresholds

4. **Try exercises:**
   - See exercises in Chapter 11
   - Solutions in `solutions/` directory

## Additional Resources

- [scikit-learn Documentation](https://scikit-learn.org/stable/)
- [Flask Documentation](https://flask.palletsprojects.com/)
- [Redis Documentation](https://redis.io/docs/)
- [PHP exec Functions](https://www.php.net/manual/en/ref.exec.php)
- [PHP cURL](https://www.php.net/manual/en/book.curl.php)

## Support

If you encounter issues:

1. Check this README's Troubleshooting section
2. Verify prerequisites (PHP 8.4+, Python 3.10+)
3. Check Python package installation: `pip list`
4. Test Python scripts standalone: `python3 script.py`
5. Review Chapter 11 Troubleshooting sections

All code has been tested and should work on PHP 8.4+ and Python 3.10+.


