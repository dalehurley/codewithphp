<?php

declare(strict_types=1);

/**
 * Bootstrap file for Chapter 20 examples
 * 
 * This file sets up autoloading for both the claude-php-agent framework
 * and provides basic utilities for the examples.
 */

// Try to find the claude-php-agent autoloader
$autoloadPaths = [
    // In case it's installed via Composer in this project
    __DIR__ . '/../../../vendor/autoload.php',
    // In case we're using the adjacent claude-php-agent repo
    __DIR__ . '/../../../../../claude-php-agent/vendor/autoload.php',
];

$autoloaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloaded = true;
        break;
    }
}

if (!$autoloaded) {
    echo "❌ Error: Could not find vendor/autoload.php\n";
    echo "Please ensure claude-php-agent is installed:\n";
    echo "  composer require claude-php/agent\n\n";
    exit(1);
}

// Verify API key is set
if (!getenv('ANTHROPIC_API_KEY')) {
    echo "❌ Error: ANTHROPIC_API_KEY environment variable not set\n";
    echo "Please set your Anthropic API key:\n";
    echo "  export ANTHROPIC_API_KEY=your_key_here\n\n";
    exit(1);
}

return true;
