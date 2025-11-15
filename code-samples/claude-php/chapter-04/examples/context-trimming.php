<?php

declare(strict_types=1);

/**
 * Context Trimming Example - Manage conversation context window
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Conversations\ConversationManager;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Context Trimming Demo ===\n\n";

try {
    $conversation = new ConversationManager($apiKey);

    // Add many messages
    for ($i = 1; $i <= 10; $i++) {
        $conversation->addUserMessage("Question {$i}");
        $conversation->addAssistantMessage("Answer {$i}");
    }

    echo "Messages before trimming: " . count($conversation->getMessages()) . "\n";

    // Trim to keep only last 6 messages (3 turns)
    $conversation->trimMessages(6);

    echo "Messages after trimming: " . count($conversation->getMessages()) . "\n";
    echo "\n✓ Context trimming complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
