<?php

declare(strict_types=1);

/**
 * Constants Demonstration
 * 
 * Constants are values that cannot be changed once defined.
 * This demonstrates:
 * - Defining constants with define()
 * - Defining constants with const
 * - Magic constants
 * - Class constants
 */

echo "=== Constants in PHP ===" . PHP_EOL . PHP_EOL;

// Method 1: Using define() function
define('SITE_NAME', 'PHP From Scratch');
define('MAX_LOGIN_ATTEMPTS', 5);
define('PI', 3.14159);

echo "1. Constants defined with define():" . PHP_EOL;
echo "SITE_NAME: " . SITE_NAME . PHP_EOL;
echo "MAX_LOGIN_ATTEMPTS: " . MAX_LOGIN_ATTEMPTS . PHP_EOL;
echo "PI: " . PI . PHP_EOL;
echo PHP_EOL;

// Method 2: Using const keyword (works at compile time)
const APP_VERSION = '1.0.0';
const DEBUG_MODE = true;
const ALLOWED_EXTENSIONS = ['jpg', 'png', 'gif'];

echo "2. Constants defined with const:" . PHP_EOL;
echo "APP_VERSION: " . APP_VERSION . PHP_EOL;
echo "DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . PHP_EOL;
echo "ALLOWED_EXTENSIONS: " . implode(', ', ALLOWED_EXTENSIONS) . PHP_EOL;
echo PHP_EOL;

// Checking if a constant exists
echo "3. Checking if constants exist:" . PHP_EOL;
echo "SITE_NAME defined? " . (defined('SITE_NAME') ? 'Yes' : 'No') . PHP_EOL;
echo "UNKNOWN defined? " . (defined('UNKNOWN') ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Magic constants (change based on where they're used)
echo "4. Magic Constants:" . PHP_EOL;
echo "__FILE__: " . __FILE__ . PHP_EOL;
echo "__DIR__: " . __DIR__ . PHP_EOL;
echo "__LINE__: " . __LINE__ . PHP_EOL;
echo "__FUNCTION__: " . __FUNCTION__ . PHP_EOL;
echo PHP_EOL;

// Trying to change a constant (this will cause an error if uncommented)
// define('SITE_NAME', 'New Name'); // Fatal error!
// SITE_NAME = 'New Name'; // Fatal error!

echo "5. Naming Conventions:" . PHP_EOL;
echo "✓ Constants are typically UPPERCASE_WITH_UNDERSCORES" . PHP_EOL;
echo "✓ Makes them easily distinguishable from variables" . PHP_EOL;
echo "✓ Use const for class constants and top-level constants" . PHP_EOL;
echo "✓ Use define() when you need runtime definition" . PHP_EOL;
echo PHP_EOL;

// Practical example: Configuration
echo "6. Practical Example - Configuration:" . PHP_EOL;
const DB_HOST = 'localhost';
const DB_PORT = 3306;
const DB_NAME = 'my_database';

echo "Database Configuration:" . PHP_EOL;
echo "  Host: " . DB_HOST . PHP_EOL;
echo "  Port: " . DB_PORT . PHP_EOL;
echo "  Database: " . DB_NAME . PHP_EOL;
