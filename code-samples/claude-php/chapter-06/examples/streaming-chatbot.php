<?php

declare(strict_types=1);

/**
 * Streaming Chatbot Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Streaming Chatbot ===\n\n";

try {
    $client = new ClaudePhp(apiKey: $apiKey);

    $questions = [
        "What is PHP?",
        "Name 3 popular PHP frameworks",
        "What's new in PHP 8.2?"
    ];

    foreach ($questions as $i => $question) {
        echo "Question " . ($i + 1) . ": {$question}\n";
        echo "Response: ";

        $stream = $client->messages()->createStreamed([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 300,
            'messages' => [['role' => 'user', 'content' => $question]]
        ]);

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta' && isset($event->delta->text)) {
                echo $event->delta->text;
                flush();
            }
        }

        echo "\n\n";
    }

    echo "✓ Chatbot session complete!\n";

} catch (\Exception $e) {
    echo "\n✗ Error: {$e->getMessage()}\n";
    exit(1);
}
