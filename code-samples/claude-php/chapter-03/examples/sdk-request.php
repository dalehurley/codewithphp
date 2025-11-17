<?php

declare(strict_types=1);

/**
 * SDK Request Example
 *
 * Make requests using the official Anthropic PHP SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Client;
use Anthropic\Messages\MessageParam;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found\n");
}

echo "=== Making Claude Request with SDK ===\n\n";

try {
    $client = new Client(apiKey: $apiKey);

    echo "Sending request...\n";
    $response = $client->messages->create(
        model: 'claude-3-5-sonnet-20240620',
        maxTokens: 1024,
        messages: [
            MessageParam::fromUser('What is the Anthropic PHP SDK?'),
        ],
    );

    echo "Response: {$response->content[0]->text}\n";
    echo "Tokens: {$response->usage->inputTokens} in, {$response->usage->outputTokens} out\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
