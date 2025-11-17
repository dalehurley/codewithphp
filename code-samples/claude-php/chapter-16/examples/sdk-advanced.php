<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Messages\MessageParam;
use ClaudePHP\SDK\SDKWrapper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Official SDK - Advanced Usage\n\n";

$sdk = new SDKWrapper($_ENV['ANTHROPIC_API_KEY']);

$sdk->addMiddleware(function (string $phase, mixed $payload) {
    if ($phase === 'request') {
        echo "→ Requesting: " . json_encode($payload['messages'] ?? []) . "\n";
    }

    if ($phase === 'response' && is_object($payload) && property_exists($payload, 'usage')) {
        echo "← Tokens: input={$payload->usage->inputTokens}, output={$payload->usage->outputTokens}\n";
    }

    return $payload;
});

$result = $sdk->sendMessage([
    'model' => 'claude-3-5-sonnet-20240620',
    'maxTokens' => 512,
    'messages' => [MessageParam::fromUser('What is the capital of France?')],
]);

echo "\nResponse object:\n";
print_r($result);

echo "\nStreaming excerpt:\n";
foreach ($sdk->streamMessage([
    'model' => 'claude-3-5-sonnet-20240620',
    'maxTokens' => 128,
    'messages' => [MessageParam::fromUser('Stream two short tips for cache invalidation.')],
]) as $event) {
    $text = $event->delta->text ?? ($event['delta']['text'] ?? null);
    if ($text) {
        echo $text;
    }
}
echo "\n";
