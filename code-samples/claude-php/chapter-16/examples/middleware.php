<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Messages\MessageParam;
use ClaudePHP\SDK\SDKWrapper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "SDK - Middleware Example\n\n";

$sdk = new SDKWrapper($_ENV['ANTHROPIC_API_KEY']);

// Token counter middleware
$tokenCount = ['input' => 0, 'output' => 0];
$sdk->addMiddleware(function (string $phase, mixed $payload) use (&$tokenCount) {
    if ($phase === 'response') {
        if (is_object($payload) && property_exists($payload, 'usage')) {
            $tokenCount['input'] += $payload->usage->inputTokens ?? 0;
            $tokenCount['output'] += $payload->usage->outputTokens ?? 0;
        }

        if (is_array($payload) && isset($payload['usage'])) {
            $tokenCount['input'] += $payload['usage']['input_tokens'] ?? 0;
            $tokenCount['output'] += $payload['usage']['output_tokens'] ?? 0;
        }
    }

    return $payload;
});

// Rate limit middleware
$sdk->addMiddleware(function (string $phase, mixed $payload) {
    if ($phase === 'request') {
        static $lastRequest = 0.0;
        $now = microtime(true);
        $elapsed = $now - $lastRequest;
        if ($elapsed < 1) {
            usleep((int) ((1 - $elapsed) * 1_000_000));
        }
        $lastRequest = microtime(true);
    }

    return $payload;
});

// Make requests
for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}...\n";
    $sdk->sendMessage([
        'model' => 'claude-3-5-sonnet-20240620',
        'maxTokens' => 100,
        'messages' => [MessageParam::fromUser("Count to {$i}")],
    ]);
}

echo "\nTotal tokens: {$tokenCount['input']} input, {$tokenCount['output']} output\n";
