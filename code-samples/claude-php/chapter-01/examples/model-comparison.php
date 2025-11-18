<?php

declare(strict_types=1);

/**
 * Model Comparison Example
 *
 * This example compares Haiku, Sonnet, and Opus models across different tasks
 * to help you understand their strengths and choose the right model.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ApiIntro\ModelBenchmark;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found in .env file\n");
}

echo "=== Claude Model Comparison ===\n\n";

$benchmark = new ModelBenchmark($apiKey);

$models = [
    'claude-haiku-4-20250514',
    'claude-sonnet-4-20250514',
    'claude-opus-4-20250514'
];

try {
    // Test 1: Simple Task (where Haiku excels)
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Test 1: Simple Classification\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $prompt = "Classify this review as positive, negative, or neutral: " .
              "'The product works okay but the delivery was slow.' " .
              "Answer with just one word.";

    echo "Task: {$prompt}\n\n";

    $results = $benchmark->compare($models, $prompt, 10);

    foreach ($results as $result) {
        echo "{$result['model']}:\n";
        echo "  Response: {$result['response']}\n";
        echo "  Time: " . number_format($result['duration'], 3) . "s\n";
        echo "  Tokens: {$result['total_tokens']}\n\n";
    }

    echo ModelBenchmark::formatComparison($results) . "\n";

    // Test 2: Creative Task (where Sonnet balances quality and speed)
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Test 2: Creative Writing\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $prompt = "Write a creative tagline for a new eco-friendly water bottle. " .
              "Make it memorable and emphasize sustainability.";

    echo "Task: {$prompt}\n\n";

    $results = $benchmark->compare($models, $prompt, 100);

    foreach ($results as $result) {
        echo "{$result['model']}:\n";
        echo "  Response: {$result['response']}\n";
        echo "  Time: " . number_format($result['duration'], 3) . "s\n\n";
    }

    echo ModelBenchmark::formatComparison($results) . "\n";

    // Test 3: Complex Reasoning (where Opus shines)
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Test 3: Complex Problem Solving\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $prompt = "A snail is at the bottom of a 30-foot well. Each day it climbs up 3 feet, " .
              "but at night it slips back 2 feet. How many days will it take to reach the top? " .
              "Explain your reasoning step by step.";

    echo "Task: {$prompt}\n\n";

    $results = $benchmark->compare($models, $prompt, 500);

    foreach ($results as $result) {
        echo "{$result['model']}:\n";
        echo "  Response: {$result['response']}\n";
        echo "  Time: " . number_format($result['duration'], 3) . "s\n";
        echo "  Tokens: {$result['total_tokens']}\n\n";
    }

    echo ModelBenchmark::formatComparison($results) . "\n";

    // Test 4: Code Generation
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Test 4: Code Generation\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $prompt = "Write a PHP function that validates an email address using regex. " .
              "Include type hints and PHPDoc comments.";

    echo "Task: {$prompt}\n\n";

    $results = $benchmark->compare($models, $prompt, 300);

    echo "Quality Comparison (Code):\n\n";
    foreach ($results as $i => $result) {
        echo "Model " . ($i + 1) . ": {$result['model']}\n";
        echo $result['response'] . "\n\n";
    }

    echo ModelBenchmark::formatComparison($results) . "\n";

    // Summary and Recommendations
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Summary and Recommendations\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Based on the tests:\n\n";

    echo "Claude Haiku 4:\n";
    echo "  ✓ Fastest response times\n";
    echo "  ✓ Most cost-effective\n";
    echo "  ✓ Great for simple, high-volume tasks\n";
    echo "  → Best for: Classification, data extraction, simple Q&A\n\n";

    echo "Claude Sonnet 4:\n";
    echo "  ✓ Balanced performance and quality\n";
    echo "  ✓ Good for most use cases\n";
    echo "  ✓ Excellent code generation\n";
    echo "  → Best for: General applications, code, creative writing\n\n";

    echo "Claude Opus 4:\n";
    echo "  ✓ Highest quality reasoning\n";
    echo "  ✓ Best for complex problems\n";
    echo "  ✓ Most detailed responses\n";
    echo "  → Best for: Research, analysis, complex reasoning\n\n";

    echo "✓ Model comparison complete!\n";

} catch (\ClaudePhp\Exceptions\APIError $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
