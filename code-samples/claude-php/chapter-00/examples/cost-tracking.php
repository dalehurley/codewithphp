<?php

declare(strict_types=1);

/**
 * Cost Tracking Example
 *
 * This example demonstrates how to monitor and calculate API costs:
 * - Track token usage across multiple requests
 * - Calculate costs for different models
 * - Compare pricing between models
 * - Budget management
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\QuickStart\ClaudeClient;
use ClaudePhp\QuickStart\CostCalculator;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found in .env file\n");
}

echo "=== Claude Cost Tracking Examples ===\n\n";

try {
    // Example 1: Basic Cost Tracking
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Basic Cost Tracking\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $client = new ClaudeClient($apiKey, 'claude-sonnet-4-20250514');
    $costCalc = new CostCalculator('claude-sonnet-4-20250514');

    // Make a series of requests
    $prompts = [
        "Explain what PHP is in one sentence.",
        "What are the benefits of using Composer?",
        "How does dependency injection work in PHP?"
    ];

    foreach ($prompts as $i => $prompt) {
        echo "Request " . ($i + 1) . ": {$prompt}\n";
        $response = $client->sendMessage($prompt, ['max_tokens' => 200]);
        $costCalc->addUsageFromResponse($response);

        $usage = $client->getUsage($response);
        echo "  Tokens: {$usage['inputTokens']} in, {$usage['outputTokens']} out\n";
        echo "  Cost: $" . number_format(
            $costCalc->calculateCost($usage['inputTokens'], $usage['outputTokens']),
            6
        ) . "\n\n";
    }

    echo "Total for all requests:\n";
    echo $costCalc->getSummary() . "\n\n";

    // Example 2: Model Comparison
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. Cost Comparison Across Models\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $testPrompt = "Write a haiku about programming in PHP.";
    $models = [
        'claude-haiku-4-20250514',
        'claude-sonnet-4-20250514',
        'claude-opus-4-20250514'
    ];

    echo "Comparing cost for prompt: \"{$testPrompt}\"\n\n";
    echo str_pad("Model", 35) . str_pad("Input", 10) . str_pad("Output", 10) . str_pad("Total", 10) . "Cost\n";
    echo str_repeat("─", 75) . "\n";

    foreach ($models as $model) {
        $modelClient = new ClaudeClient($apiKey, $model, 150);
        $response = $modelClient->sendMessage($testPrompt);

        $usage = $modelClient->getUsage($response);
        $modelCostCalc = new CostCalculator($model);
        $cost = $modelCostCalc->calculateCost($usage['inputTokens'], $usage['outputTokens']);

        echo str_pad($model, 35);
        echo str_pad((string)$usage['inputTokens'], 10);
        echo str_pad((string)$usage['outputTokens'], 10);
        echo str_pad((string)$usage['totalTokens'], 10);
        echo "$" . number_format($cost, 6) . "\n";
    }
    echo "\n";

    // Example 3: Budget Monitoring
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "3. Budget Monitoring\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $budget = 0.10; // $0.10 budget
    $budgetCalc = new CostCalculator('claude-sonnet-4-20250514');
    $client = new ClaudeClient($apiKey, 'claude-sonnet-4-20250514');

    echo "Budget: $" . number_format($budget, 2) . "\n\n";

    $tasks = [
        "Explain closures in PHP",
        "What are PHP generators?",
        "Describe the difference between == and ===",
        "What is a trait in PHP?",
        "Explain PSR-12 coding standard"
    ];

    foreach ($tasks as $i => $task) {
        // Check if we're approaching budget
        $currentCost = $budgetCalc->getTotalCost();
        $remaining = $budget - $currentCost;

        if ($remaining <= 0) {
            echo "⚠️  Budget exceeded! Stopping execution.\n";
            echo "Completed " . $i . " out of " . count($tasks) . " tasks.\n";
            break;
        }

        if ($remaining < 0.02) {
            echo "⚠️  Warning: Less than $0.02 remaining in budget.\n";
        }

        echo "Task " . ($i + 1) . ": {$task}\n";
        $response = $client->sendMessage($task, ['max_tokens' => 150]);
        $budgetCalc->addUsageFromResponse($response);

        $usage = $client->getUsage($response);
        $taskCost = $budgetCalc->calculateCost($usage['inputTokens'], $usage['outputTokens']);

        echo "  Cost: $" . number_format($taskCost, 6);
        echo " | Remaining: $" . number_format($budget - $budgetCalc->getTotalCost(), 6) . "\n\n";
    }

    echo "Final Budget Summary:\n";
    echo "  Spent: $" . number_format($budgetCalc->getTotalCost(), 6) . "\n";
    echo "  Remaining: $" . number_format($budget - $budgetCalc->getTotalCost(), 6) . "\n\n";

    // Example 4: Pricing Reference
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "4. Current Pricing Reference\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Pricing per Million Tokens (as of 2025):\n\n";
    echo str_pad("Model", 35) . str_pad("Input", 12) . "Output\n";
    echo str_repeat("─", 60) . "\n";

    $allPricing = CostCalculator::getAllPricing();
    foreach ($allPricing as $model => $pricing) {
        echo str_pad($model, 35);
        echo "$" . str_pad(number_format($pricing['input'], 2), 11);
        echo "$" . number_format($pricing['output'], 2) . "\n";
    }
    echo "\n";

    // Example 5: Estimated Cost for Large Operations
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "5. Estimated Costs for Common Operations\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $operations = [
        ['name' => 'Analyze 1,000 customer reviews', 'input' => 500000, 'output' => 50000],
        ['name' => 'Generate 100 product descriptions', 'input' => 10000, 'output' => 50000],
        ['name' => 'Process 500 support tickets', 'input' => 250000, 'output' => 100000],
        ['name' => 'Summarize 50 long documents', 'input' => 1000000, 'output' => 25000],
    ];

    foreach ($models as $model) {
        echo "Model: {$model}\n";
        echo str_repeat("─", 75) . "\n";

        $calc = new CostCalculator($model);

        foreach ($operations as $op) {
            $cost = $calc->calculateCost($op['input'], $op['output']);
            echo "  " . str_pad($op['name'], 45);
            echo "$" . number_format($cost, 4) . "\n";
        }
        echo "\n";
    }

    echo "✓ All cost tracking examples completed successfully!\n";
    echo "\n💡 Tip: Use CostCalculator in your production code to monitor API usage!\n";

} catch (\ClaudePhp\Exceptions\APIError $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
