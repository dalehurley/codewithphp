<?php

declare(strict_types=1);

use ClaudePHP\SDK\SDKWrapper;

$config = require __DIR__ . '/../bootstrap.php';
$client = new SDKWrapper($config['apiKey']);

$client->addMiddleware(static function (string $phase, array $data): array {
    if ($phase === 'request') {
        printf("Request model: %s\n", $data['model']);
    } else {
        printf("Response ID: %s\n", $data['id']);
    }

    return $data;
});

$result = $client->sendMessage([
    'model' => $config['model'],
    'max_tokens' => 256,
    'messages' => [
        ['role' => 'user', 'content' => 'What is the capital of France?'],
    ],
]);

foreach ($result['content'] as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . PHP_EOL;
    }
}
