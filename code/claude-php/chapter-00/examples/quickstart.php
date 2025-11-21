<?php

declare(strict_types=1);

/**
 * Quick Start Example - Your First Claude API Call
 *
 * This example demonstrates the absolute basics of making a Claude API request.
 * It's the simplest possible implementation to get you started.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\QuickStart\ClaudeClient;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Ensure API key is set
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found in .env file\n");
}

echo "=== Claude Quick Start ===\n\n";

try {
    // Create a Claude client
    $client = new ClaudeClient($apiKey);

    // Send a simple message
    echo "Sending message to Claude...\n";
    $response = $client->sendMessage("Hello! Please introduce yourself in one sentence.");

    // Extract and display the response
    $text = $client->extractText($response);
    echo "\nClaude's Response:\n";
    echo "─────────────────────────────────────\n";
    echo $text . "\n";
    echo "─────────────────────────────────────\n\n";

    // Display usage information
    $usage = $client->getUsage($response);
    echo "Token Usage:\n";
    echo "  Input:  {$usage['inputTokens']} tokens\n";
    echo "  Output: {$usage['outputTokens']} tokens\n";
    echo "  Total:  {$usage['totalTokens']} tokens\n\n";

    echo "✓ Success! You've made your first Claude API call.\n";

} catch (\ClaudePhp\Exceptions\ErrorException $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
