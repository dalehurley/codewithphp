<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Service\ClaudeService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Service Class Example\n\n";

$claude = new ClaudeService(
    apiKey: $_ENV['ANTHROPIC_API_KEY'],
    model: $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-20250514'
);

// Simple chat
echo "Simple Chat:\n";
$response = $claude->chat('What is PHP?');
echo "→ " . substr($response, 0, 200) . "...\n\n";

// With system prompt
echo "With System Prompt:\n";
$response = $claude->chat(
    'Explain dependency injection',
    'You are a senior PHP developer. Be concise and practical.'
);
echo "→ " . substr($response, 0, 200) . "...\n\n";

// Full message API
echo "Full Message API:\n";
$result = $claude->sendMessage([
    ['role' => 'user', 'content' => 'Hello!'],
]);
echo "→ " . $result['content'][0]['text'] . "\n";
echo "Tokens: {$result['usage']['input_tokens']} in, {$result['usage']['output_tokens']} out\n";
