# Chapter 02: Environment Setup - Code Examples

This directory contains all code examples from Chapter 02: Setting Up a Data Science Environment as a PHP Developer.

## Directory Structure

```
chapter-02/
├── project-structure/     # Complete project template
├── file-exchange/         # File-based PHP-Python communication
├── cli-invocation/        # CLI-based PHP-Python communication
├── api-service/           # HTTP API PHP-Python communication
├── environment-setup/     # Verification scripts
└── README.md             # This file
```

## Examples Overview

### 1. Project Structure Template

**Directory**: `project-structure/`

A complete, production-ready project template for PHP data science work.

**Contents**:
- `composer.json` - Dependencies (MathPHP, League CSV, Guzzle, etc.)
- `scripts/import.php` - Data collection example
- `scripts/clean.php` - Data preprocessing example
- `scripts/export.php` - Report generation example
- `src/` - Organized source code directories
- `data/` - Raw and processed data storage
- `output/` - Reports and visualizations

**Setup**:
```bash
cd project-structure
composer install
php scripts/import.php
php scripts/clean.php
php scripts/export.php
```

**Expected behavior**:
1. `import.php` generates 100 sample records
2. `clean.php` validates and cleans the data
3. `export.php` generates a summary report

---

### 2. File Exchange Pattern

**Directory**: `file-exchange/`

Demonstrates batch data exchange between PHP and Python via CSV files.

**Flow**: PHP → CSV → Python → CSV → PHP

**Files**:
- `export-data.php` - PHP exports data to CSV
- `process-data.py` - Python processes CSV (requires pandas)
- `import-results.php` - PHP reads Python results

**Usage**:
```bash
cd file-exchange

# Step 1: PHP exports
php export-data.php

# Step 2: Python processes
python3 process-data.py

# Step 3: PHP imports results
php import-results.php
```

**Requirements**:
- Python 3.10+ with pandas (`pip install pandas`)

**When to use**:
- Batch ETL pipelines
- Overnight processing
- Large dataset transformations
- Scheduled reports

---

### 3. CLI Invocation Pattern

**Directory**: `cli-invocation/`

Demonstrates PHP executing Python scripts directly via command line.

**Flow**: PHP calls Python script → Python returns JSON

**Files**:
- `run-model.php` - PHP invokes Python script
- `model.py` - Python prediction script

**Usage**:
```bash
cd cli-invocation
php run-model.php
```

**How it works**:
1. PHP passes JSON input as command-line argument
2. Python processes and returns JSON via stdout
3. PHP captures output and parses JSON
4. Error handling via exit codes

**Requirements**:
- Python 3.10+ (no extra packages needed)

**When to use**:
- Quick predictions
- Synchronous operations
- Small data transformations
- One-off calculations

---

### 4. API Service Pattern

**Directory**: `api-service/`

Demonstrates Python microservice with PHP HTTP client.

**Flow**: Python runs as web service ← PHP sends HTTP requests

**Files**:
- `api-server.py` - Flask prediction API
- `api-client.php` - PHP HTTP client

**Usage**:

**Terminal 1 - Start Python server:**
```bash
cd api-service
pip install flask  # One-time setup
python3 api-server.py
```

**Terminal 2 - Run PHP client:**
```bash
cd api-service
php api-client.php
```

**Endpoints**:
- `GET /health` - Health check
- `POST /predict` - Single prediction
- `POST /batch` - Batch predictions

**Requirements**:
- Python 3.10+ with Flask (`pip install flask`)

**When to use**:
- Production predictions
- Real-time responses
- High-frequency requests
- Scalable architecture
- Multiple consumers

---

### 5. Environment Verification

**Directory**: `environment-setup/`

Scripts to verify your PHP and Python environments are configured correctly.

**Files**:
- `verify-php.php` - Check PHP version, extensions, packages
- `verify-python.sh` - Check Python version, venv, packages
- `env.example` - Environment variable template

**Usage**:

**Verify PHP:**
```bash
cd environment-setup
php verify-php.php
```

**Verify Python:**
```bash
cd environment-setup
bash verify-python.sh
```

**Expected output**: ✓ for each requirement, or ✗ with instructions if missing

---

## Quick Start

### Option 1: Test Everything

```bash
# 1. Verify environments
cd environment-setup
php verify-php.php
bash verify-python.sh

# 2. Test file exchange
cd ../file-exchange
php export-data.php
python3 process-data.py
php import-results.php

# 3. Test CLI invocation
cd ../cli-invocation
php run-model.php

# 4. Test API service (requires 2 terminals)
cd ../api-service
python3 api-server.py &  # Background
sleep 2
php api-client.php
```

### Option 2: Start from Template

```bash
# Copy template to your project
cp -r project-structure/ ~/my-data-project/
cd ~/my-data-project/
composer install

# Run example pipeline
php scripts/import.php
php scripts/clean.php
php scripts/export.php
```

---

## Requirements

### PHP Requirements
- PHP 8.4+
- Extensions: pdo, json, mbstring, curl, intl
- Composer packages (in project-structure):
  - markrogoyski/math-php
  - league/csv
  - guzzlehttp/guzzle
  - vlucas/phpdotenv
  - nesbot/carbon

### Python Requirements (Optional)
- Python 3.10+
- pandas (for file-exchange)
- Flask (for api-service)

**Install Python packages:**
```bash
pip install pandas flask
```

---

## Communication Pattern Comparison

| Pattern | Setup | Speed | Scalability | Best For |
|---------|-------|-------|-------------|----------|
| **File Exchange** | Easy | Slow | Low | Batch jobs, ETL |
| **CLI Invocation** | Easy | Medium | Low | Quick tasks, sync calls |
| **API Service** | Complex | Fast | High | Production, real-time |

---

## Troubleshooting

### PHP Issues

**"Extension not found"**:
```bash
# macOS
brew install php

# Ubuntu
sudo apt-get install php8.4-[extension]
```

**"Composer not found"**:
```bash
# Install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Python Issues

**"Python not found"**:
```bash
# macOS
brew install python@3.10

# Ubuntu
sudo apt-get install python3.10
```

**"Module not found"**:
```bash
pip install [module-name]
# or with venv activated:
source venv/bin/activate
pip install [module-name]
```

**"Permission denied" for .sh files**:
```bash
chmod +x verify-python.sh
```

### API Service Issues

**"Connection refused"**:
- Make sure Python server is running: `python3 api-server.py`
- Check port 5000 is not in use: `lsof -i :5000`
- Wait a few seconds for server to start

**"Import error: flask"**:
```bash
pip install flask
```

---

## Testing

All examples are designed to be runnable without external dependencies (except where noted).

**Test checklist**:
- [ ] PHP verification script passes
- [ ] Python verification script passes
- [ ] File exchange completes full cycle
- [ ] CLI invocation returns JSON
- [ ] API server starts and responds
- [ ] Project structure scripts run successfully

---

## Production Considerations

### Security
- Never commit `.env` files with real credentials
- Validate and sanitize all inputs from Python
- Use HTTPS for production APIs
- Implement rate limiting on API endpoints
- Escape shell arguments in CLI invocation

### Performance
- Use file exchange for large batches (1000+ records)
- Use CLI for medium batches (100-1000 records)
- Use API for real-time (<100 records)
- Cache API responses when possible
- Monitor Python process memory usage

### Reliability
- Add retry logic for API calls
- Log all PHP-Python communication
- Validate JSON schemas
- Handle Python errors gracefully
- Monitor API health endpoints

---

## Next Steps

After completing these examples:

1. ✓ Choose the communication pattern that fits your use case
2. ✓ Set up your project using the template structure
3. ✓ Configure environment variables
4. ✓ Proceed to Chapter 03: Collecting Data

---

## Related Documentation

- [Chapter 02 Full Tutorial](../../docs/series/data-science-php-developers/chapters/02-setting-up-data-science-environment.md)
- [MathPHP Documentation](https://github.com/markrogoyski/math-php)
- [League CSV Documentation](https://csv.thephpleague.com/)
- [Flask Quickstart](https://flask.palletsprojects.com/quickstart/)

---

## License

Code examples are provided for educational purposes as part of the Code with PHP tutorial series.


