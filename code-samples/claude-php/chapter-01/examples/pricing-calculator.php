<?php

declare(strict_types=1);

/**
 * Pricing Calculator Example
 *
 * This example helps you understand and calculate Claude API costs
 * for different models and usage patterns.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Claude API Pricing Calculator ===\n\n";

/**
 * Pricing per million tokens (as of 2025)
 */
const PRICING = [
    'claude-haiku-4-20250514' => [
        'input' => 0.25,
        'output' => 1.25,
        'name' => 'Claude Haiku 4'
    ],
    'claude-sonnet-4-20250514' => [
        'input' => 3.00,
        'output' => 15.00,
        'name' => 'Claude Sonnet 4'
    ],
    'claude-opus-4-20250514' => [
        'input' => 15.00,
        'output' => 75.00,
        'name' => 'Claude Opus 4'
    ]
];

/**
 * Calculate cost for a given token usage
 *
 * @param string $model Model identifier
 * @param int $inputTokens Input token count
 * @param int $outputTokens Output token count
 * @return float Cost in USD
 */
function calculateCost(string $model, int $inputTokens, int $outputTokens): float
{
    if (!isset(PRICING[$model])) {
        throw new InvalidArgumentException("Unknown model: {$model}");
    }

    $pricing = PRICING[$model];
    $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
    $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

    return $inputCost + $outputCost;
}

/**
 * Format cost comparison table
 *
 * @param array<string, array<string, mixed>> $scenarios Scenarios to compare
 */
function displayCostComparison(array $scenarios): void
{
    $header = str_pad("Scenario", 40) . str_pad("Haiku", 15) . str_pad("Sonnet", 15) . "Opus\n";
    echo $header;
    echo str_repeat("─", 85) . "\n";

    foreach ($scenarios as $name => $tokens) {
        echo str_pad($name, 40);

        foreach (['claude-haiku-4-20250514', 'claude-sonnet-4-20250514', 'claude-opus-4-20250514'] as $model) {
            $cost = calculateCost($model, $tokens['input'], $tokens['output']);
            echo str_pad('$' . number_format($cost, 4), 15);
        }
        echo "\n";
    }
    echo "\n";
}

try {
    // Example 1: Current Pricing Overview
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Current Pricing (per million tokens)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $header = str_pad("Model", 35) . str_pad("Input", 15) . "Output\n";
    echo $header;
    echo str_repeat("─", 65) . "\n";

    foreach (PRICING as $model => $pricing) {
        echo str_pad($pricing['name'], 35);
        echo str_pad('$' . number_format($pricing['input'], 2), 15);
        echo '$' . number_format($pricing['output'], 2) . "\n";
    }
    echo "\n";

    // Example 2: Common Use Cases
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. Cost for Common Use Cases\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $useCases = [
        'Single short message' => ['input' => 50, 'output' => 100],
        'Chatbot conversation (10 turns)' => ['input' => 1000, 'output' => 2000],
        'Analyze one document' => ['input' => 5000, 'output' => 1000],
        'Generate product description' => ['input' => 200, 'output' => 500],
        'Code review (medium file)' => ['input' => 2000, 'output' => 3000]
    ];

    displayCostComparison($useCases);

    // Example 3: High-Volume Scenarios
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "3. High-Volume Processing Costs\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $highVolume = [
        'Process 100 support tickets' => ['input' => 50000, 'output' => 100000],
        'Analyze 1,000 customer reviews' => ['input' => 500000, 'output' => 100000],
        'Generate 500 emails' => ['input' => 25000, 'output' => 250000],
        'Daily content moderation (10k items)' => ['input' => 2000000, 'output' => 500000]
    ];

    displayCostComparison($highVolume);

    // Example 4: Monthly Budget Planning
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "4. Monthly Budget Examples\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $budgets = [50, 100, 500, 1000];

    foreach ($budgets as $budget) {
        echo "With a ${budget}/month budget:\n\n";

        foreach (PRICING as $model => $pricing) {
            // Calculate how many tokens you can process
            // Assuming 40% input, 60% output (typical ratio)
            $avgCostPerMillion = ($pricing['input'] * 0.4) + ($pricing['output'] * 0.6);
            $millionTokens = $budget / $avgCostPerMillion;
            $totalTokens = $millionTokens * 1_000_000;

            echo "  {$pricing['name']}:\n";
            echo "    → ~" . number_format($totalTokens) . " total tokens\n";
            echo "    → ~" . number_format($totalTokens / 1000) . " average conversations (1k tokens each)\n";
            echo "    → ~" . number_format($totalTokens / 10000) . " document analyses (10k tokens each)\n\n";
        }
    }

    // Example 5: Cost Optimization Tips
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "5. Cost Optimization Comparison\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $task = "Process 10,000 support tickets per month";
    $tokensPerTicket = ['input' => 500, 'output' => 1000];

    echo "Task: {$task}\n";
    echo "Tokens per ticket: {$tokensPerTicket['input']} in, {$tokensPerTicket['output']} out\n\n";

    foreach (PRICING as $model => $pricing) {
        $costPerTicket = calculateCost($model, $tokensPerTicket['input'], $tokensPerTicket['output']);
        $monthlyCost = $costPerTicket * 10000;

        echo "{$pricing['name']}:\n";
        echo "  Cost per ticket: $" . number_format($costPerTicket, 6) . "\n";
        echo "  Monthly cost: $" . number_format($monthlyCost, 2) . "\n";
        echo "  Annual cost: $" . number_format($monthlyCost * 12, 2) . "\n\n";
    }

    // Example 6: Interactive Calculator
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "6. Cost Savings Analysis\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Switching from Opus to Sonnet for suitable tasks:\n\n";

    $scenarios = [
        ['name' => 'Customer support (simple)', 'input' => 300, 'output' => 800, 'volume' => 1000],
        ['name' => 'Content moderation', 'input' => 200, 'output' => 50, 'volume' => 5000],
        ['name' => 'Email classification', 'input' => 150, 'output' => 20, 'volume' => 2000]
    ];

    foreach ($scenarios as $scenario) {
        $opusCost = calculateCost('claude-opus-4-20250514', $scenario['input'], $scenario['output']) * $scenario['volume'];
        $sonnetCost = calculateCost('claude-sonnet-4-20250514', $scenario['input'], $scenario['output']) * $scenario['volume'];
        $savings = $opusCost - $sonnetCost;
        $savingsPercent = ($savings / $opusCost) * 100;

        echo "{$scenario['name']} ({$scenario['volume']} requests/month):\n";
        echo "  Opus cost: $" . number_format($opusCost, 2) . "\n";
        echo "  Sonnet cost: $" . number_format($sonnetCost, 2) . "\n";
        echo "  Savings: $" . number_format($savings, 2) . " (" . number_format($savingsPercent, 1) . "%)\n\n";
    }

    // Summary
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "💡 Cost Optimization Tips\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "1. Choose the right model for the task\n";
    echo "   → Use Haiku for simple, high-volume tasks\n";
    echo "   → Use Sonnet for general-purpose applications\n";
    echo "   → Reserve Opus for complex reasoning\n\n";

    echo "2. Optimize prompts to reduce tokens\n";
    echo "   → Be concise but clear\n";
    echo "   → Use max_tokens to limit output\n\n";

    echo "3. Implement caching for repeated queries\n";
    echo "   → Cache common responses\n";
    echo "   → Reuse context when possible\n\n";

    echo "4. Monitor and track usage\n";
    echo "   → Set up alerts for budget thresholds\n";
    echo "   → Regularly review token usage patterns\n\n";

    echo "✓ Pricing analysis complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
