<?php

declare(strict_types=1);

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AnthropicException;

$config = require __DIR__ . '/../bootstrap.php';
$client = new ClaudePhp(apiKey: $config['apiKey']);
$completed = false;

try {
    $stream = $client->messages()->stream([
        'model' => $config['model'],
        'max_tokens' => 256,
        'messages' => [['role' => 'user', 'content' => 'Explain dependency injection in one sentence.']],
    ]);

    try {
        foreach ($stream as $event) {
            if ($event['type'] === 'error') {
                throw new RuntimeException('Claude sent a stream error.');
            }

            if ($event['type'] === 'message_stop') {
                $completed = true;
                break;
            }

            if ($event['type'] === 'content_block_delta' && $event['delta']['type'] === 'text_delta') {
                echo $event['delta']['text'];
                flush();
            }
        }
    } finally {
        $stream->close();
    }
} catch (AnthropicException | RuntimeException $error) {
    fwrite(STDERR, "\nThe Claude stream failed.\n");
    exit(1);
}

if (!$completed) {
    fwrite(STDERR, "\nThe stream ended before Claude finished the message.\n");
    exit(1);
}

echo PHP_EOL;
