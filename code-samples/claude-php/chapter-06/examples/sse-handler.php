<?php

declare(strict_types=1);

/**
 * Server-Sent Events Handler Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== SSE Handler Demo ===\n\n";

try {
    $client = Anthropic::factory()->withApiKey($apiKey)->make();

    echo "Streaming with event handling:\n\n";

    $stream = $client->messages()->createStreamed([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 300,
        'messages' => [
            ['role' => 'user', 'content' => 'Explain streaming in 2 sentences.']
        ]
    ]);

    $fullText = '';

    foreach ($stream as $event) {
        switch ($event->type) {
            case 'message_start':
                echo "[Message started]\n";
                break;

            case 'content_block_start':
                echo "[Content block started]\n";
                break;

            case 'content_block_delta':
                if (isset($event->delta->text)) {
                    $fullText .= $event->delta->text;
                    echo $event->delta->text;
                    flush();
                }
                break;

            case 'content_block_stop':
                echo "\n[Content block stopped]\n";
                break;

            case 'message_stop':
                echo "[Message stopped]\n";
                break;
        }
    }

    echo "\nFull text: {$fullText}\n";
    echo "\n✓ SSE handling complete!\n";

} catch (\Exception $e) {
    echo "\n✗ Error: {$e->getMessage()}\n";
    exit(1);
}
