<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

$config = require __DIR__ . '/../bootstrap.php';
$client = new Client([
    'base_uri' => 'https://api.anthropic.com',
    'connect_timeout' => 5.0,
    'timeout' => 30.0,
    'headers' => [
        'x-api-key' => $config['apiKey'],
        'anthropic-version' => '2023-06-01',
    ],
]);

try {
    $response = $client->post('/v1/messages', [
        'json' => [
            'model' => $config['model'],
            'max_tokens' => 256,
            'messages' => [
                ['role' => 'user', 'content' => 'Explain Guzzle in one sentence.'],
            ],
        ],
    ]);

    $message = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    foreach ($message['content'] as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . PHP_EOL;
        }
    }
} catch (RequestException $error) {
    $status = $error->getResponse()?->getStatusCode();
    fwrite(STDERR, $status === null ? "No HTTP response received.\n" : "Claude returned HTTP {$status}.\n");
    exit(1);
} catch (GuzzleException | JsonException $error) {
    fwrite(STDERR, "Unable to read the Claude response.\n");
    exit(1);
}
