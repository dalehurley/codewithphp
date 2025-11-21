<?php

declare(strict_types=1);

/**
 * Budget Manager Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\TokenManagement\BudgetManager;
use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Budget Manager ===\n\n";

try {
    $client = new ClaudePhp(    apiKey: $apiKey);

    // Set a budget: 5000 tokens
    $budget = new BudgetManager(5000);

    echo "Initial budget: {$budget->getSummary()}\n\n";

    $prompts = [
        "What is PHP?",
        "Explain Laravel framework",
        "What are the benefits of using Composer?",
        "How does dependency injection work?",
        "What is PSR-12 coding standard?"
    ];

    foreach ($prompts as $i => $prompt) {
        if ($budget->isExhausted()) {
            echo "Budget exhausted! Stopping at prompt " . ($i + 1) . "\n";
            break;
        }

        echo "Prompt " . ($i + 1) . ": {$prompt}\n";

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 200,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);

        $totalTokens = $response->usage->inputTokens + $response->usage->outputTokens;
        $budget->addUsage($totalTokens);

        echo "Tokens used: {$totalTokens}\n";
        echo "Budget: {$budget->getSummary()}\n\n";
    }

    echo "Final budget status: {$budget->getSummary()}\n";
    echo "\n✓ Budget management complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
