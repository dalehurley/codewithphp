<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\SDK\SDKWrapper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Official SDK - Advanced Usage\n\n";

$sdk = new SDKWrapper($_ENV['ANTHROPIC_API_KEY']);

// Add logging middleware
$sdk->addMiddleware(function($type, $data) {
    if ($type === 'request') {
        echo "→ Request: " . $data['messages'][0]['content'] . "\n";
    } elseif ($type === 'response') {
        echo "← Response: " . substr($data['content'][0]['text'], 0, 100) . "...\n";
    }
    return $data;
});

$result = $sdk->sendMessage([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is the capital of France?'],
    ],
]);

echo "\nFull response:\n";
echo $result['content'][0]['text'] . "\n";
