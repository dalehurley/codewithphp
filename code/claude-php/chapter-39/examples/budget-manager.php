<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\BudgetManager;

echo "=== Budget Tracker Example ===\n";

// Initialize Redis (assuming it's running)
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);

// Initialize Claude client
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Initialize budget tracker
$budgetTracker = new BudgetManager($redis, [
    'hourly' => 10.00,
    'daily' => 200.00,
    'monthly' => 5000.00,
]);

// Before making request
$estimatedCost = 0.15;

if ($budgetTracker->wouldExceedBudget($estimatedCost)) {
    throw new \Exception("Request would exceed budget limits");
}

// Make request
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Hello Claude!']]
]);

// Track actual cost
$actualCost = 0.00495; // Would normally calculate from response
$budgetTracker->trackSpending($actualCost, 'user123');

// Check budget status
$status = $budgetTracker->getBudgetStatus();
echo "Budget Status:\n";
foreach ($status as $period => $data) {
    echo "  $period: $" . number_format($data['spent'], 2) . " / $" .
         number_format($data['limit'], 2) . " ({$data['used_pct']}%)\n";
}

echo "\n✓ Budget tracker working correctly\n";
