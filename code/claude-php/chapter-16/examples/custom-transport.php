<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\ClaudePhp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

echo "SDK - Custom Transport\n\n";

// Create mock handler for testing
$mock = new MockHandler([
    new Response(200, [], json_encode([
        'id' => 'msg_123',
        'content' => [['type' => 'text', 'text' => 'Mocked response']],
        'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
        'stop_reason' => 'end_turn',
    ])),
]);

$handlerStack = HandlerStack::create($mock);
$client = new ClaudePhp(['handler' => $handlerStack]);

echo "Using mock transport for testing...\n";
$response = $client->post('/v1/messages', [
    'json' => [
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 100,
        'messages' => [['role' => 'user', 'content' => 'Test']],
    ],
]);

$data = json_decode($response->getBody()->getContents(), true);
echo "Response: {$data['content'][0]['text']}\n";
echo "✓ Mock transport working correctly\n";
