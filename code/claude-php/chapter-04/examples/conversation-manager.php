<?php

declare(strict_types=1);

/**
 * Conversation Manager Example - Interactive conversation management
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Conversations\ConversationManager;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Conversation Manager Demo ===\n\n";

try {
    $conversation = new ConversationManager($apiKey);

    echo "Starting a conversation about programming...\n\n";

    $prompts = [
        "I'm learning PHP. Where should I start?",
        "What about frameworks?",
        "Which framework would you recommend for beginners?"
    ];

    foreach ($prompts as $i => $prompt) {
        echo "Turn " . ($i + 1) . "\n";
        echo "User: {$prompt}\n";
        $response = $conversation->send($prompt);
        echo "Claude: {$response->content[0]->text}\n";
        echo "Tokens: {$response->usage->inputTokens} + {$response->usage->outputTokens}\n\n";
    }

    echo "Message history count: " . count($conversation->getMessages()) . "\n";
    echo "✓ Conversation demo complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
