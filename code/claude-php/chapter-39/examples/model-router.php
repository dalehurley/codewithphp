<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Optimization\ModelRouter;

echo "=== Model Router Example ===\n";

// Initialize Claude client
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Initialize model router
$router = new ModelRouter($client);

// Automatic model selection
$prompt = "Classify this email as spam or not: ...";
$recommendation = $router->recommendModel($prompt, [
    'budget_priority' => true,
    'expected_output_tokens' => 50
]);

echo "Recommended: {$recommendation['recommended_model']}\n";
echo "Reasoning: {$recommendation['reasoning']}\n";
echo "Estimated cost: $" . number_format($recommendation['estimated_costs']['sonnet'], 6) . "\n";

// Test different complexities
$prompts = [
    "What is 2+2?" => ['complexity' => 'simple'],
    "Explain quantum computing" => ['complexity' => 'medium'],
    "Design a scalable microservices architecture" => ['complexity' => 'complex'],
];

echo "\nModel recommendations by complexity:\n";
foreach ($prompts as $prompt => $requirements) {
    $rec = $router->recommendModel($prompt, $requirements);
    echo "- \"$prompt\": {$rec['recommended_model']} ({$rec['complexity']})\n";
}

echo "\n✓ Model router working correctly\n";
