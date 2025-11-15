<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Caching - Prompt Caching (Claude Feature)\n\n";

$client = new Client(['base_uri' => 'https://api.anthropic.com']);

// Prompt caching allows caching of system prompts and long contexts
// This reduces costs and improves latency for repeated content

$longContext = str_repeat("This is context information. ", 100);

$response = $client->post('/v1/messages', [
    'headers' => [
        'x-api-key' => $_ENV['ANTHROPIC_API_KEY'],
        'anthropic-version' => '2023-06-01',
        'content-type' => 'application/json',
    ],
    'json' => [
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'system' => [
            [
                'type' => 'text',
                'text' => $longContext,
                'cache_control' => ['type' => 'ephemeral'], // Enable caching
            ],
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Summarize the context'],
        ],
    ],
]);

$result = json_decode($response->getBody()->getContents(), true);

echo "Response: " . $result['content'][0]['text'] . "\n\n";

if (isset($result['usage']['cache_creation_input_tokens'])) {
    echo "Cache creation tokens: {$result['usage']['cache_creation_input_tokens']}\n";
}
if (isset($result['usage']['cache_read_input_tokens'])) {
    echo "Cache read tokens: {$result['usage']['cache_read_input_tokens']}\n";
}

echo "\nPrompt caching is useful for:\n";
echo "- Long system prompts\n";
echo "- Large context documents\n";
echo "- Repeated conversations\n";
