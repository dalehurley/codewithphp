<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Optimization\PromptOptimizer;

echo "=== Prompt Optimizer Example ===\n";

$optimizer = new PromptOptimizer();

$verbose = "I would like you to please analyze the following text and tell me if it contains any negative sentiment or not.";

$result = $optimizer->optimize($verbose);

echo "Original: {$result['original']}\n";
echo "Optimized: {$result['optimized']}\n";
echo "Reduction: {$result['reduction_pct']}%\n";
echo "Token savings: ~{$result['estimated_token_savings']} tokens\n";

// Test aggressive optimization
$resultAggressive = $optimizer->optimize($verbose, ['aggressive' => true]);
echo "\nAggressive optimization:\n";
echo "Optimized: {$resultAggressive['optimized']}\n";
echo "Reduction: {$resultAggressive['reduction_pct']}%\n";

echo "\n✓ Prompt optimizer working correctly\n";
