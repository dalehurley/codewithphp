<?php

declare(strict_types=1);

/**
 * Basic Streaming Example
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');

echo "=== Basic Streaming ===\n\n";

try {
    $client = Anthropic::factory()->withApiKey($apiKey)->make();

    echo "Streaming response:\n";

    $stream = $client->messages()->createStreamed([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 500,
        'messages' => [
            ['role' => 'user', 'content' => 'Write a haiku about PHP programming.']
        ]
    ]);

    foreach ($stream as $event) {
        if ($event->type === 'content_block_delta' && isset($event->delta->text)) {
            echo $event->delta->text;
            flush();
        }
    }

    echo "\n\n✓ Streaming complete!\n";

} catch (\Exception $e) {
    echo "\n✗ Error: {$e->getMessage()}\n";
    exit(1);
}
