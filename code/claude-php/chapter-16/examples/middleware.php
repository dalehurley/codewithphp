<?php

declare(strict_types=1);

use ClaudePHP\SDK\SDKWrapper;

$config = require __DIR__ . '/../bootstrap.php';
$client = new SDKWrapper($config['apiKey']);
$inputTokens = 0;
$outputTokens = 0;

$client->addMiddleware(static function (string $phase, array $data) use (&$inputTokens, &$outputTokens): array {
    if ($phase === 'response') {
        $inputTokens += $data['usage']['input_tokens'];
        $outputTokens += $data['usage']['output_tokens'];
    }

    return $data;
});

foreach (['What is middleware?', 'What is a mock HTTP response?'] as $prompt) {
    $client->sendMessage([
        'model' => $config['model'],
        'max_tokens' => 256,
        'messages' => [['role' => 'user', 'content' => $prompt]],
    ]);
}

printf("Total tokens: %d input, %d output\n", $inputTokens, $outputTokens);
