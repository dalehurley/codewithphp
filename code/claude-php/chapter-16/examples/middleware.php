<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\SDK\SDKWrapper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "SDK - Middleware Example\n\n";

$sdk = new SDKWrapper($_ENV['ANTHROPIC_API_KEY']);

// Token counter middleware
$tokenCount = ['input' => 0, 'output' => 0];
$sdk->addMiddleware(function($type, $data) use (&$tokenCount) {
    if ($type === 'response' && isset($data['usage'])) {
        $tokenCount['input'] += $data['usage']['input_tokens'];
        $tokenCount['output'] += $data['usage']['output_tokens'];
    }
    return $data;
});

// Rate limit middleware
$sdk->addMiddleware(function($type, $data) {
    if ($type === 'request') {
        static $lastRequest = 0;
        $now = microtime(true);
        $elapsed = $now - $lastRequest;
        if ($elapsed < 1) {
            usleep((int) ((1 - $elapsed) * 1000000));
        }
        $lastRequest = microtime(true);
    }
    return $data;
});

// Make requests
for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}...\n";
    $sdk->sendMessage([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 100,
        'messages' => [['role' => 'user', 'content' => "Count to {$i}"]],
    ]);
}

echo "\nTotal tokens: {$tokenCount['input']} input, {$tokenCount['output']} output\n";
