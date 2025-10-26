<?php

declare(strict_types=1);

/**
 * Application Entry Point
 * 
 * This demonstrates a complete Composer-based project structure
 */

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Now we can use any class in our src/ directory
use App\User;

echo "=== Composer Autoloading Demo ===" . PHP_EOL . PHP_EOL;

// Create user instance - autoloaded by Composer
$user = new User("John Doe", "john@example.com");

echo "User Name: {$user->getName()}" . PHP_EOL;
echo "User Email: {$user->getEmail()}" . PHP_EOL;
echo "Initials: {$user->getInitials()}" . PHP_EOL;
echo PHP_EOL;

// Use global helper functions (also autoloaded)
echo "Environment: " . env('APP_ENV', 'development') . PHP_EOL;

// Test if running via PHP built-in server
if (php_sapi_name() === 'cli-server') {
    echo "✓ Running via PHP built-in server" . PHP_EOL;
    echo "Visit: http://localhost:8000" . PHP_EOL;
} else {
    echo "✓ Running via CLI" . PHP_EOL;
}
