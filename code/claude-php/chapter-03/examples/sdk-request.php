<?php

declare(strict_types=1);

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\APIStatusError;
use ClaudePhp\Exceptions\AnthropicException;

$config = require __DIR__ . '/../bootstrap.php';
$client = new ClaudePhp(apiKey: $config['apiKey']);

try {
    $response = $client->messages()->create([
        'model' => $config['model'],
        'max_tokens' => 256,
        'system' => 'Answer in one concise sentence.',
        'messages' => [
            ['role' => 'user', 'content' => 'What is Composer used for in PHP?'],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . PHP_EOL;
        }
    }

    printf("Tokens: %d in, %d out\n", $response->usage->input_tokens, $response->usage->output_tokens);
} catch (APIStatusError $error) {
    fprintf(STDERR, "Claude returned HTTP %d. Request ID: %s\n", $error->status_code, $error->request_id ?? 'unavailable');
    exit(1);
} catch (AnthropicException $error) {
    fwrite(STDERR, "The Claude request failed. Check the network connection and try again.\n");
    exit(1);
}
