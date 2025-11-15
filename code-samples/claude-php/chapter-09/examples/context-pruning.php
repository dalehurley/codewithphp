<?php

declare(strict_types=1);

/**
 * Context Pruning Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\TokenManagement\TokenCounter;
use Anthropic\Anthropic;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Context Pruning ===\n\n";

try {
    $client = Anthropic::factory()->withApiKey($apiKey)->make();

    // Build up a conversation
    $messages = [];

    for ($i = 1; $i <= 10; $i++) {
        $messages[] = ['role' => 'user', 'content' => "Question {$i}"];
        $messages[] = ['role' => 'assistant', 'content' => "Answer {$i}"];
    }

    echo "Original conversation:\n";
    echo "Messages: " . count($messages) . "\n";
    echo "Estimated tokens: " . TokenCounter::estimateConversation($messages) . "\n\n";

    // Prune to keep only last 6 messages (3 turns)
    $prunedMessages = array_slice($messages, -6);

    echo "Pruned conversation:\n";
    echo "Messages: " . count($prunedMessages) . "\n";
    echo "Estimated tokens: " . TokenCounter::estimateConversation($prunedMessages) . "\n\n";

    // Add new message to pruned conversation
    $prunedMessages[] = ['role' => 'user', 'content' => 'What did we just discuss?'];

    echo "Testing pruned conversation:\n";
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 200,
        'messages' => $prunedMessages
    ]);

    echo "Response: {$response->content[0]->text}\n";
    echo "Actual tokens: {$response->usage->inputTokens} input, {$response->usage->outputTokens} output\n\n";

    echo "✓ Context pruning complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
