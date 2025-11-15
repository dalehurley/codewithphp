<?php

declare(strict_types=1);

/**
 * API Basics Example
 *
 * This example demonstrates fundamental Claude API concepts:
 * - Request structure
 * - Response handling
 * - HTTP headers
 * - Error handling
 * - Rate limiting
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Exceptions\ErrorException;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found in .env file\n");
}

echo "=== Claude API Basics ===\n\n";

$client = Anthropic::factory()
    ->withApiKey($apiKey)
    ->make();

try {
    // Example 1: Basic Request Structure
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Basic Request Structure\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Making a basic request with required parameters...\n\n";

    $requestParams = [
        'model' => 'claude-sonnet-4-20250514',  // Required: Which model to use
        'max_tokens' => 1024,                     // Required: Max tokens to generate
        'messages' => [                           // Required: Conversation messages
            [
                'role' => 'user',
                'content' => 'Explain what an API is in one sentence.'
            ]
        ]
    ];

    echo "Request Parameters:\n";
    echo json_encode($requestParams, JSON_PRETTY_PRINT) . "\n\n";

    $response = $client->messages()->create($requestParams);

    echo "Response received!\n";
    echo "Answer: {$response->content[0]->text}\n\n";

    // Example 2: Understanding Response Structure
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. Response Structure\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Complete response structure:\n\n";

    echo "ID: {$response->id}\n";
    echo "Type: {$response->type}\n";
    echo "Role: {$response->role}\n";
    echo "Model: {$response->model}\n";
    echo "Stop Reason: {$response->stopReason}\n";
    echo "Stop Sequence: " . ($response->stopSequence ?? 'null') . "\n\n";

    echo "Content:\n";
    foreach ($response->content as $i => $content) {
        echo "  Block {$i}:\n";
        echo "    Type: {$content->type}\n";
        echo "    Text: {$content->text}\n";
    }
    echo "\n";

    echo "Usage:\n";
    echo "  Input Tokens: {$response->usage->inputTokens}\n";
    echo "  Output Tokens: {$response->usage->outputTokens}\n";
    echo "  Total Tokens: " . ($response->usage->inputTokens + $response->usage->outputTokens) . "\n\n";

    // Example 3: Optional Parameters
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "3. Using Optional Parameters\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Adding system prompt and temperature...\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'system' => 'You are a helpful assistant that explains technical concepts simply.',
        'temperature' => 0.7,
        'messages' => [
            ['role' => 'user', 'content' => 'What is REST API?']
        ]
    ]);

    echo "Response: {$response->content[0]->text}\n\n";

    // Example 4: Stop Sequences
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "4. Using Stop Sequences\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Requesting a numbered list and stopping after 3 items...\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'stop_sequences' => ["\n4."],  // Stop at the 4th item
        'messages' => [
            ['role' => 'user', 'content' => 'List the benefits of using PHP. Number each point.']
        ]
    ]);

    echo "Response (should stop at 3 items):\n";
    echo $response->content[0]->text . "\n";
    echo "\nStop Reason: {$response->stopReason}\n";
    echo "Stop Sequence: " . ($response->stopSequence ?? 'null') . "\n\n";

    // Example 5: Metadata
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "5. Adding Metadata\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Adding user_id metadata for tracking...\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 100,
        'metadata' => [
            'user_id' => 'user_123'
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ]);

    echo "Response: {$response->content[0]->text}\n";
    echo "Request processed with metadata tracking.\n\n";

    // Example 6: Error Handling
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "6. Error Handling\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Testing different error scenarios...\n\n";

    // Test 1: Invalid model
    try {
        echo "Test: Invalid model name\n";
        $client->messages()->create([
            'model' => 'invalid-model',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ]
        ]);
    } catch (ErrorException $e) {
        echo "  ✓ Caught error: {$e->getMessage()}\n";
        echo "  HTTP Status: {$e->getCode()}\n\n";
    }

    // Test 2: Missing max_tokens
    try {
        echo "Test: Missing max_tokens parameter\n";
        $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ]
        ]);
    } catch (\TypeError $e) {
        echo "  ✓ Caught error: Missing required parameter\n\n";
    }

    // Example 7: Rate Limit Headers (simulated)
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "7. Understanding Rate Limits\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Rate limits are enforced via HTTP headers:\n\n";
    echo "  anthropic-ratelimit-requests-limit: Max requests per minute\n";
    echo "  anthropic-ratelimit-requests-remaining: Requests remaining\n";
    echo "  anthropic-ratelimit-requests-reset: When limit resets\n";
    echo "  anthropic-ratelimit-tokens-limit: Max tokens per minute\n";
    echo "  anthropic-ratelimit-tokens-remaining: Tokens remaining\n";
    echo "  anthropic-ratelimit-tokens-reset: When token limit resets\n\n";

    echo "Note: The PHP SDK handles these automatically, but you should\n";
    echo "implement exponential backoff for production applications.\n\n";

    // Summary
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📚 API Basics Summary\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Required Parameters:\n";
    echo "  • model - Which Claude model to use\n";
    echo "  • max_tokens - Maximum tokens to generate\n";
    echo "  • messages - Array of conversation messages\n\n";

    echo "Optional Parameters:\n";
    echo "  • system - System prompt for context\n";
    echo "  • temperature - Randomness (0-1)\n";
    echo "  • stop_sequences - Where to stop generation\n";
    echo "  • metadata - Tracking information\n";
    echo "  • top_p - Nucleus sampling parameter\n";
    echo "  • top_k - Top-k sampling parameter\n\n";

    echo "Response Fields:\n";
    echo "  • id - Unique message identifier\n";
    echo "  • content - Generated text (array of blocks)\n";
    echo "  • model - Model that generated response\n";
    echo "  • usage - Token usage information\n";
    echo "  • stopReason - Why generation stopped\n\n";

    echo "✓ API basics exploration complete!\n";

} catch (ErrorException $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    echo "Error Type: " . get_class($e) . "\n";
    echo "HTTP Code: {$e->getCode()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
