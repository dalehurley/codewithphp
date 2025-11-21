<?php

declare(strict_types=1);

/**
 * Multi-Turn Conversation Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Conversations\ConversationManager;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Multi-Turn Conversation ===\n\n";

try {
    $conversation = new ConversationManager($apiKey);

    // Turn 1
    echo "User: What is PHP?\n";
    $response = $conversation->send("What is PHP?");
    echo "Claude: {$response->content[0]->text}\n\n";

    // Turn 2
    echo "User: What version is current?\n";
    $response = $conversation->send("What version is current?");
    echo "Claude: {$response->content[0]->text}\n\n";

    // Turn 3
    echo "User: What are the major features?\n";
    $response = $conversation->send("What are the major features?");
    echo "Claude: {$response->content[0]->text}\n\n";

    echo "✓ Conversation complete!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
