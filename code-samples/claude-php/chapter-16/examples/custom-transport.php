<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Messages\MessageParam;
use ClaudePHP\SDK\SDKWrapper;

echo "SDK - Custom Transport\n\n";

$fakeTransport = function (array $payload) {
    return (object) [
        'id' => 'msg_mocked',
        'content' => [(object) ['type' => 'text', 'text' => 'Mocked response']],
        'usage' => (object) ['inputTokens' => 10, 'outputTokens' => 5],
        'stopReason' => 'end_turn',
        'model' => $payload['model'] ?? 'claude-3-5-sonnet-20240620',
    ];
};

$sdk = new SDKWrapper(apiKey: 'test-api-key', transport: $fakeTransport);

echo "Using injected mock transport...\n";
$response = $sdk->sendMessage([
    'model' => 'claude-3-5-sonnet-20240620',
    'maxTokens' => 100,
    'messages' => [MessageParam::fromUser('Test')],
]);

if (is_object($response) && isset($response->content[0]->text)) {
    echo "Response: {$response->content[0]->text}\n";
}

echo "✓ Mock transport working correctly\n";
