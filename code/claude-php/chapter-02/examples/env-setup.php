<?php

declare(strict_types=1);

/**
 * Environment Setup Example
 *
 * Demonstrates proper environment variable configuration for API keys.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Auth\ApiKeyManager;
use Dotenv\Dotenv;

echo "=== Environment Setup and Validation ===\n\n";

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    // Example 1: Validate Environment
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Environment Validation\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $requiredVars = ['ANTHROPIC_API_KEY', 'APP_ENV'];
    $missingVars = [];

    foreach ($requiredVars as $var) {
        $value = getenv($var);
        if ($value === false) {
            $missingVars[] = $var;
            echo "✗ {$var}: Missing\n";
        } else {
            echo "✓ {$var}: Set\n";
        }
    }

    if (!empty($missingVars)) {
        throw new \RuntimeException("Missing required environment variables: " . implode(', ', $missingVars));
    }
    echo "\n";

    // Example 2: Validate API Key Format
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. API Key Format Validation\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $apiKey = getenv('ANTHROPIC_API_KEY');
    $manager = new ApiKeyManager($apiKey);

    echo "API Key: " . ApiKeyManager::maskKey($apiKey) . "\n";
    echo "Format valid: " . ($manager->isValidFormat($apiKey) ? "✓ Yes" : "✗ No") . "\n\n";

    // Example 3: Test API Key
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "3. API Key Connectivity Test\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Testing API key...\n";
    if ($manager->testKey($apiKey)) {
        echo "✓ API key is valid and working\n\n";
    } else {
        echo "✗ API key is invalid or not working\n\n";
        throw new \RuntimeException('Invalid API key');
    }

    echo "✓ Environment setup complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
