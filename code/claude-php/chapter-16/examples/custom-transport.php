<?php

declare(strict_types=1);

use ClaudePHP\SDK\SDKWrapper;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$mock = new MockHandler([
    new Response(200, ['Content-Type' => 'application/json'], json_encode([
        'id' => 'msg_example',
        'content' => [['type' => 'text', 'text' => 'Mocked response']],
        'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
        'stop_reason' => 'end_turn',
    ], JSON_THROW_ON_ERROR)),
]);

$transport = new Client(['handler' => HandlerStack::create($mock)]);
$client = new SDKWrapper('test-key', $transport);
$message = $client->sendMessage([
    'model' => 'claude-sonnet-5-5',
    'max_tokens' => 256,
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);

echo $message['content'][0]['text'] . PHP_EOL;
