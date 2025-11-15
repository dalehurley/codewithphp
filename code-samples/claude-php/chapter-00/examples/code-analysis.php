<?php

declare(strict_types=1);

/**
 * Code Analysis Example
 *
 * This example demonstrates using Claude to:
 * - Explain code functionality
 * - Identify potential bugs
 * - Suggest improvements
 * - Generate unit tests
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

echo "=== Claude Code Analysis Examples ===\n\n";

// Initialize client and cost calculator
$client = new ClaudeClient($apiKey);
$costCalc = new CostCalculator();

// Sample code to analyze
$sampleCode = <<<'PHP'
function processUserData($data) {
    $result = [];
    foreach ($data as $item) {
        if ($item['age'] >= 18) {
            $result[] = [
                'name' => $item['name'],
                'email' => strtolower($item['email']),
                'age' => $item['age']
            ];
        }
    }
    return $result;
}
PHP;

try {
    // Example 1: Explain Code
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Code Explanation\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $response = $client->sendWithSystem(
        "You are an expert PHP developer who explains code clearly and concisely.",
        "Explain what this PHP function does:\n\n{$sampleCode}"
    );

    echo "Code:\n{$sampleCode}\n\n";
    echo "Explanation:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 2: Find Bugs and Issues
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. Bug Detection\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $response = $client->sendWithSystem(
        "You are a senior PHP developer specializing in code review and bug detection.",
        "Analyze this PHP function for potential bugs, security issues, or edge cases:\n\n{$sampleCode}\n\nList any issues you find and explain why they're problematic."
    );

    echo "Issues Found:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 3: Suggest Improvements
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "3. Code Improvements\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $response = $client->sendWithSystem(
        "You are an expert PHP developer following modern best practices.",
        "Improve this PHP function with:\n" .
        "- Type hints\n" .
        "- Error handling\n" .
        "- PHPDoc comments\n" .
        "- Better naming\n\n" .
        "Original code:\n{$sampleCode}"
    );

    echo "Improved Version:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 4: Generate Unit Tests
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "4. Unit Test Generation\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $response = $client->sendWithSystem(
        "You are an expert in PHP testing with PHPUnit.",
        "Generate comprehensive PHPUnit tests for this function. Include tests for:\n" .
        "- Normal cases\n" .
        "- Edge cases\n" .
        "- Error conditions\n\n" .
        "Function:\n{$sampleCode}"
    );

    echo "Generated Tests:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 5: Complexity Analysis
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "5. Complexity Analysis\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $complexCode = <<<'PHP'
function findDuplicates($array) {
    $seen = [];
    $duplicates = [];
    foreach ($array as $item) {
        if (in_array($item, $seen)) {
            if (!in_array($item, $duplicates)) {
                $duplicates[] = $item;
            }
        } else {
            $seen[] = $item;
        }
    }
    return $duplicates;
}
PHP;

    $response = $client->sendWithSystem(
        "You are a computer science expert specializing in algorithm analysis.",
        "Analyze the time and space complexity of this function. " .
        "Explain the Big O notation and suggest a more efficient approach if possible:\n\n{$complexCode}"
    );

    echo "Code:\n{$complexCode}\n\n";
    echo "Analysis:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Display summary
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Session Summary\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo $costCalc->getSummary() . "\n\n";

    echo "✓ All code analysis examples completed successfully!\n";

} catch (\Anthropic\Exceptions\ErrorException $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
