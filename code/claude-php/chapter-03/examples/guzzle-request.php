<?php

declare(strict_types=1);

/**
 * Guzzle Request Example
 *
 * Make Claude API requests using Guzzle HTTP client directly.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\ClaudePhp;
use GuzzleHttp\Exception\GuzzleException;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found\n");
}

echo "=== Making Claude Request with Guzzle ===\n\n";

try {
    // Create Guzzle HTTP client
    $client = new ClaudePhp([
        'base_uri' => 'https://api.anthropic.com',
        'timeout' => 30.0,
        'headers' => [
            'anthropic-version' => '2023-06-01',
            'x-api-key' => $apiKey,
            'content-type' => 'application/json'
        ]
    ]);

    // Prepare request payload
    $payload = [
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Explain what Guzzle is in PHP in one sentence.'
            ]
        ]
    ];

    echo "Sending request...\n";
    $response = $client->post('/v1/messages', [
        'json' => $payload
    ]);

    $statusCode = $response->getStatusCode();
    $body = json_decode($response->getBody()->getContents(), true);

    echo "Status Code: {$statusCode}\n";
    echo "Response: {$body['content'][0]['text']}\n";
    echo "Tokens: {$body['usage']['input_tokens']} in, {$body['usage']['output_tokens']} out\n";

} catch (GuzzleException $e) {
    echo "✗ HTTP Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
