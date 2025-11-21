<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\ClaudePhp;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Structured Outputs - Streaming JSON\n\n";

$client = new ClaudePhp(['base_uri' => 'https://api.anthropic.com']);

$schema = [
    'type' => 'object',
    'properties' => [
        'summary' => ['type' => 'string'],
        'keywords' => ['type' => 'array', 'items' => ['type' => 'string']],
        'sentiment' => ['type' => 'string', 'enum' => ['positive', 'negative', 'neutral']],
    ],
];

$systemPrompt = "Respond with JSON matching this schema: " . json_encode($schema);

echo "Streaming structured response...\n\n";

$response = $client->post('/v1/messages', [
    'headers' => [
        'x-api-key' => $_ENV['ANTHROPIC_API_KEY'],
        'anthropic-version' => '2023-06-01',
        'content-type' => 'application/json',
    ],
    'json' => [
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'stream' => false,
        'system' => $systemPrompt,
        'messages' => [
            ['role' => 'user', 'content' => 'Analyze: This product is amazing! Great quality and fast shipping. Highly recommend.'],
        ],
    ],
]);

$result = json_decode($response->getBody()->getContents(), true);
$jsonText = $result['content'][0]['text'];

if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
    $data = json_decode($matches[0], true);
    echo "Structured response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
}
