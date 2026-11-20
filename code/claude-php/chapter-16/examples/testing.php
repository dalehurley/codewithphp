<?php

declare(strict_types=1);

use ClaudePHP\SDK\SDKWrapper;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$mock = new MockHandler([
    new Response(200, ['Content-Type' => 'application/json'], json_encode([
        'id' => 'msg_example',
        'content' => [['type' => 'text', 'text' => 'Hello from the fake server.']],
        'usage' => ['input_tokens' => 5, 'output_tokens' => 7],
    ], JSON_THROW_ON_ERROR)),
]);
$history = [];
$stack = HandlerStack::create($mock);
$stack->push(Middleware::history($history));
$wrapper = new SDKWrapper('test-key', new Client(['handler' => $stack]));

$phases = [];
$wrapper->addMiddleware(static function (string $phase, array $data) use (&$phases): array {
    $phases[] = $phase;
    if ($phase === 'request') {
        $data['max_tokens'] = 64;
    }

    return $data;
});

$message = $wrapper->sendMessage([
    'model' => 'claude-sonnet-5-5',
    'max_tokens' => 256,
    'messages' => [['role' => 'user', 'content' => 'Say hello']],
]);

$request = $history[0]['request'];
$payload = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
if ($request->getMethod() !== 'POST'
    || $request->getUri()->getPath() !== '/v1/messages'
    || $request->getHeaderLine('anthropic-version') !== '2023-06-01'
    || $request->getHeaderLine('x-api-key') !== 'test-key'
    || $payload['max_tokens'] !== 64
    || $phases !== ['request', 'response']
    || $message['content'][0]['text'] !== 'Hello from the fake server.'
    || $message['usage']['output_tokens'] !== 7
) {
    throw new RuntimeException('Unexpected request, middleware calls, or response.');
}

echo "Request, middleware, and response checks passed.\n";
