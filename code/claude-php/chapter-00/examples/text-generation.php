<?php

declare(strict_types=1);

/**
 * Text Generation Example
 *
 * This example demonstrates using Claude for various text generation tasks:
 * - Creative writing
 * - Business content
 * - Technical documentation
 * - Email drafting
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

echo "=== Claude Text Generation Examples ===\n\n";

// Initialize client and cost calculator
$client = new ClaudeClient($apiKey);
$costCalc = new CostCalculator();

/**
 * Helper function to display a generation result
 *
 * @param string $title Title of the example
 * @param string $prompt The prompt sent to Claude
 * @param object $response The API response
 * @param ClaudeClient $client The Claude client instance
 * @param CostCalculator $costCalc The cost calculator instance
 */
function displayResult(
    string $title,
    string $prompt,
    object $response,
    ClaudeClient $client,
    CostCalculator $costCalc
): void {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📝 {$title}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "Prompt: {$prompt}\n\n";
    echo "Response:\n";
    echo $client->extractText($response) . "\n\n";

    $usage = $client->getUsage($response);
    echo "Tokens: {$usage['inputTokens']} in, {$usage['outputTokens']} out\n\n";

    $costCalc->addUsageFromResponse($response);
}

try {
    // Example 1: Creative Writing
    echo "Example 1: Creative Story Opening\n";
    $response = $client->sendWithSystem(
        "You are a creative fiction writer with a talent for engaging openings.",
        "Write the opening paragraph of a science fiction story about a programmer who discovers AI consciousness. Make it compelling and atmospheric."
    );
    displayResult(
        "Creative Story Opening",
        "Write the opening paragraph of a sci-fi story...",
        $response,
        $client,
        $costCalc
    );

    // Example 2: Business Email
    echo "Example 2: Professional Email\n";
    $response = $client->sendWithSystem(
        "You are a professional business communication expert.",
        "Draft a polite email declining a meeting request due to scheduling conflicts. Suggest alternative times next week."
    );
    displayResult(
        "Professional Email",
        "Draft a polite email declining a meeting...",
        $response,
        $client,
        $costCalc
    );

    // Example 3: Technical Documentation
    echo "Example 3: Technical Documentation\n";
    $response = $client->sendWithSystem(
        "You are a technical writer specializing in clear, concise documentation.",
        "Write a README section explaining how to install and configure a PHP package using Composer. Keep it beginner-friendly."
    );
    displayResult(
        "Technical Documentation",
        "Write a README section for PHP package installation...",
        $response,
        $client,
        $costCalc
    );

    // Example 4: Product Description
    echo "Example 4: Marketing Copy\n";
    $response = $client->sendMessage(
        "Write a compelling 2-paragraph product description for a new smart home device that learns your daily routines and automates tasks. Focus on benefits, not just features.",
        ['max_tokens' => 500]
    );
    displayResult(
        "Product Description",
        "Write a product description for smart home device...",
        $response,
        $client,
        $costCalc
    );

    // Example 5: Code Comments
    echo "Example 5: Code Documentation\n";
    $response = $client->sendMessage(
        "Add clear PHPDoc comments to this function:\n\n" .
        "function calculateDiscount(\$price, \$percentage, \$minOrder = 0) {\n" .
        "    if (\$price < \$minOrder) return \$price;\n" .
        "    return \$price * (1 - \$percentage / 100);\n" .
        "}"
    );
    displayResult(
        "Code Documentation",
        "Add PHPDoc comments to a function...",
        $response,
        $client,
        $costCalc
    );

    // Display summary
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Session Summary\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo $costCalc->getSummary() . "\n\n";

    echo "✓ All text generation examples completed successfully!\n";

} catch (\ClaudePhp\Exceptions\ErrorException $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
