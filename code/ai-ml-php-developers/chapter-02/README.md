# Chapter 02: Setting Up Your AI Development Environment - Code Samples

This directory contains working code examples and verification scripts for Chapter 02.

## Contents

### Verification Scripts

- `quick-start-verify.php` - **2-minute quick environment check** (run this first!)
- `verify-installation.php` - Comprehensive environment check (10+ detailed checks)
- `composer-setup-check.php` - Composer installation and configuration verification
- `extension-checker.php` - Detailed PHP extension checker with installation instructions

### Test Scripts

- `test-phpml.php` - PHP-ML library test with k-nearest neighbors example
- `test-rubixml.php` - Rubix ML library test with Iris classification

### Configuration

- `composer.json` - Project dependencies
- `env.example` - Environment variable template

## Quick Start

### 1. Install Dependencies

```bash
# Make sure you're in the chapter-02 directory
cd docs/series/ai-ml-php-developers/code/chapter-02/

# Install PHP dependencies via Composer
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### 2. Verify Your Environment

```bash
# Run the verification script (10 comprehensive checks)
php verify-installation.php
```

You should see all critical checks pass (✅). Optional components (Python, Tensor) may show warnings (⚠️) but won't prevent you from proceeding.

### 3. Test PHP-ML

```bash
# Run the PHP-ML test
php test-phpml.php
```

Expected output:

```
🧪 Testing PHP-ML Library
==========================

Training data:
  Tall people: 175-182 cm, 70-80 kg
  Short people: 155-162 cm, 50-57 kg

Training K-Nearest Neighbors classifier (k=3)...
✅ Training complete

Making predictions:
-------------------
  Medium height person (170 cm, 65 kg) → Predicted: tall
  Very tall person (185 cm, 85 kg) → Predicted: tall
  Very short person (150 cm, 48 kg) → Predicted: short
  Tall person (177 cm, 72 kg) → Predicted: tall

✅ PHP-ML is working correctly!
```

### 4. Test Rubix ML

```bash
# Run the Rubix ML test
php test-rubixml.php
```

Expected output:

```
🧪 Testing Rubix ML Library
============================

Training data: Iris flower dataset
  Features: sepal length, sepal width, petal length, petal width
  Species: setosa, versicolor, virginica
  Samples: 9

Training K-Nearest Neighbors classifier (k=3, Euclidean distance)...
✅ Training complete

Making predictions:
-------------------
  Sample [5.0, 3.4, 1.5, 0.2] (small petals, likely setosa)
  → Predicted species: setosa

  Sample [6.5, 3.0, 4.8, 1.5] (medium petals, likely versicolor)
  → Predicted species: versicolor

  Sample [6.5, 3.0, 5.8, 2.2] (large petals, likely virginica)
  → Predicted species: virginica

Performance check:
------------------
  ✅ Rubix Tensor extension is loaded
     (Mathematical operations are optimized)

✅ Rubix ML is working correctly!
```

## What Each Script Does

### quick-start-verify.php ⚡

**Use this first!** A minimal 2-minute environment check that verifies:

- PHP version (8.4+)
- Critical extensions (json, mbstring, curl)
- Composer availability

This gives you immediate feedback on whether your basic environment is ready. If all checks pass, proceed to the comprehensive verification.

**Run it:**

```bash
php quick-start-verify.php
```

### verify-installation.php

Comprehensive environment check (10+ detailed checks):

- PHP version (8.4+)
- Required extensions (json, mbstring, curl, dom, zip)
- Composer installation and memory configuration
- Autoloader presence and functionality
- PHP-ML library installation and classes
- Rubix ML library installation and classes
- Rubix Tensor extension (optional performance boost)
- Python 3 installation (optional for advanced chapters)
- Available disk space (1GB+ recommended)
- Project directories (src/, tests/, data/, models/) with proper permissions

**Use this first** to confirm everything is set up correctly. The script provides detailed feedback and suggests fixes for any issues found.

### composer-setup-check.php

Dedicated Composer verification that checks:

- Composer installation and version
- Composer 2.x vs 1.x detection
- Memory limit configuration
- Project setup (composer.json, vendor/, autoloader)
- Autoloader functionality test

**Run it:**

```bash
php composer-setup-check.php
```

Useful when you suspect Composer issues or need to debug package installation problems.

### extension-checker.php

Detailed PHP extension analysis:

- Lists all required extensions with descriptions
- Lists all recommended extensions with usage notes
- Provides platform-specific installation instructions
- Explains what each extension is used for in ML work
- Clear status for each extension (installed/missing)

**Run it:**

```bash
php extension-checker.php
```

Useful when troubleshooting specific extension issues or understanding what each extension does.

### test-phpml.php

Demonstrates PHP-ML basics with modern PHP 8.4 features:

- Training a k-nearest neighbors classifier with named arguments
- Using typed arrays and data validation
- Making predictions with enhanced output
- Understanding the PHP-ML API
- PHP 8.4 features: named arguments, array destructuring, match expressions
- Proper error handling and data validation

This is your first working ML code in pure PHP!

### test-rubixml.php

Demonstrates Rubix ML capabilities with enhanced features:

- Creating labeled datasets with data validation
- Using distance kernels (Euclidean) with performance timing
- Training sophisticated classifiers with timing measurements
- Checking for performance optimizations (Tensor extension)
- Working with multi-feature data and statistical summaries
- PHP 8.4 features: array destructuring, microtime precision, enhanced error handling

Rubix ML is more powerful than PHP-ML and will be used throughout the series.

## Troubleshooting

### "vendor/autoload.php not found"

You need to run `composer install` first:

```bash
composer install
```

### "Class not found" errors

Regenerate the autoloader:

```bash
composer dump-autoload
```

### Verification script reports failures

Review the output and refer to the Troubleshooting section in Chapter 02 for specific fixes based on what failed.

### Memory errors

Increase PHP memory limit in `php.ini`:

```ini
memory_limit = 512M
```

Or set it for a single script:

```bash
php -d memory_limit=512M verify-installation.php
```

## Directory Structure

After running `composer install`, your structure should look like:

```
chapter-02/
├── composer.json          # Dependencies definition
├── composer.lock          # Locked versions (auto-generated)
├── vendor/                # Installed libraries (auto-generated)
│   ├── autoload.php      # Autoloader entry point
│   ├── php-ai/           # PHP-ML library
│   ├── rubix/            # Rubix ML library
│   └── ...
├── verify-installation.php
├── test-phpml.php
├── test-rubixml.php
├── env.example
└── README.md (this file)
```

## Next Steps

Once all scripts run successfully:

1. Review the code to understand how ML libraries work
2. Experiment by changing the training data
3. Try different k values in the classifiers
4. Proceed to Chapter 03 to learn core ML concepts

## Additional Resources

- [PHP-ML Documentation](https://php-ml.readthedocs.io/)
- [Rubix ML Documentation](https://docs.rubixml.com/)
- [Composer Documentation](https://getcomposer.org/doc/)
- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/)

## Support

If you encounter issues:

1. Check the Troubleshooting section in this README
2. Review the Troubleshooting section in Chapter 02
3. Verify your PHP version: `php --version`
4. Verify Composer works: `composer --version`
5. Check extension installation: `php -m`

All code in this directory has been tested and should work on PHP 8.4+ with properly installed dependencies.
